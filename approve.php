<?php
// public/api/students/approve.php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Models\Student;
use App\Auth\Middleware;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$studentId = (int)($input['student_id'] ?? 0);
$enrollmentYear = $input['enrollment_year'] ?? '';
$manualIndexNumber = $input['manual_index_number'] ?? null;

if (!$studentId || !$enrollmentYear) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters'
    ]);
    exit;
}

try {
    $student = new Student();

    // CRITICAL: Clean up any hanging transactions BEFORE starting our work
    while ($student->db->inTransaction()) {
        error_log("Found hanging transaction in approve.php - rolling back");
        $student->db->rollback();
    }

    $approvedBy = null;

    // Get student details first
    $studentData = $student->findById($studentId);
    if (!$studentData) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Student not found'
        ]);
        exit;
    }

    if ($studentData['status'] !== 'PENDING_APPROVAL') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Student is not in pending approval status. Current status: ' . ($studentData['status'] ?? 'unknown')
        ]);
        exit;
    }

    // NOW start our transaction for the entire approval process
    $student->db->beginTransaction();

    try {
        $indexNumber = null;

        // Handle index number generation/assignment
        if ($manualIndexNumber) {
            // Manual index number provided
            $validation = $student->validateManualIndexNumber($manualIndexNumber, $studentId);

            if (!$validation['valid']) {
                throw new Exception('Manual index number validation failed: ' . implode(', ', $validation['errors']));
            }

            // For manual assignment, we need to handle it specially to avoid nested transactions
            // Check if index is already used (without starting transaction)
            if ($student->isIndexNumberUsed($manualIndexNumber)) {
                throw new Exception("Index number '{$manualIndexNumber}' is already in use");
            }

            // Update student with manual index number directly
            $stmt = $student->db->prepare("UPDATE students SET index_no = ? WHERE id = ?");
            $success = $stmt->execute([$manualIndexNumber, $studentId]);

            if (!$success) {
                throw new Exception('Failed to assign manual index number');
            }

            // Update sequence table to prevent conflicts
            $parsedIndex = $student->parseIndexNumber($manualIndexNumber);
            if ($parsedIndex) {
                $student->updateSequenceForManualIndex($parsedIndex);
            }

            $indexNumber = $manualIndexNumber;

        } else {
            // Auto-generate index number
            // We need to generate without starting a new transaction since we're already in one
            $indexNumber = $student->generateIndexNumberInTransaction(
                $studentData['department_id'],
                $studentData['mode'],
                $enrollmentYear,
                $approvedBy
            );

            // Update student with generated index number if not already set
            if (empty($studentData['index_no'])) {
                $stmt = $student->db->prepare("UPDATE students SET index_no = ? WHERE id = ?");
                $success = $stmt->execute([$indexNumber, $studentId]);

                if (!$success) {
                    throw new Exception('Failed to update student with generated index number');
                }
            }
        }

        // Update enrollment year if it has changed
        if ($enrollmentYear !== $studentData['enrollment_year']) {
            $stmt = $student->db->prepare("UPDATE students SET enrollment_year = ? WHERE id = ?");
            $updateResult = $stmt->execute([$enrollmentYear, $studentId]);

            if (!$updateResult) {
                throw new Exception('Failed to update enrollment year');
            }
        }

        // Approve the student registration (handle without nested transactions)
        $stmt = $student->db->prepare("
            UPDATE students 
            SET academic_status = 'Active', 
                status = 'APPROVED',
                completed = 1,
                approved_at = NOW()
            WHERE id = ?
        ");
        $approveSuccess = $stmt->execute([$studentId]);

        if (!$approveSuccess) {
            throw new Exception('Failed to approve student registration');
        }

        // Log academic status change
        $stmt = $student->db->prepare("
            INSERT INTO academic_status_history (student_id, old_status, new_status, reason, changed_by)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$studentId, 'PENDING', 'Active', 'Registration approved by admin', $approvedBy]);

        // Commit the transaction
        $student->db->commit();

        // Try to send approval email (non-blocking)
        $emailSent = false;
        try {
            $emailSent = $student->sendApprovalEmail($studentId);
            if (!$emailSent) {
                error_log('Approval email sending returned false for student ' . $studentId);
            }
        } catch (Exception $emailError) {
            error_log('Approval email failed for student ' . $studentId . ': ' . $emailError->getMessage());
        }

        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Student approved successfully',
            'data' => [
                'student_id' => $studentId,
                'index_number' => $indexNumber,
                'enrollment_year' => $enrollmentYear,
                'method' => $manualIndexNumber ? 'manual' : 'auto',
                'approved_by' => $approvedBy,
                'email_sent' => $emailSent
            ]
        ]);

    } catch (Exception $e) {
        // Rollback transaction on any error
        try {
            $student->db->rollback();
        } catch (Exception $rollbackError) {
            error_log('Transaction rollback failed: ' . $rollbackError->getMessage());
        }
        throw $e;
    }

} catch (Exception $e) {
    // Final cleanup - ensure no hanging transactions
    try {
        if (isset($student) && $student->db && $student->db->inTransaction()) {
            $student->db->rollback();
        }
    } catch (Exception $finalCleanupError) {
        error_log('Final cleanup failed: ' . $finalCleanupError->getMessage());
    }

    error_log('Student approval error - Student ID: ' . $studentId . ', Error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());

    $httpCode = 500;
    $message = $e->getMessage();

    if (strpos($message, 'not found') !== false) {
        $httpCode = 404;
    } elseif (strpos($message, 'validation failed') !== false ||
        strpos($message, 'not in pending approval') !== false) {
        $httpCode = 422;
    } elseif (strpos($message, 'session not found') !== false) {
        $httpCode = 401;
    }

    http_response_code($httpCode);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'error_code' => $httpCode
    ]);
}
?>