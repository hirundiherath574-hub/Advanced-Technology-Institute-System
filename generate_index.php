<?php
// public/api/students/generate_index.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Models\Student;
use App\Auth\Middleware;

Middleware::check('STUDENT_WRITE');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$studentId = (int)($input['student_id'] ?? 0);
$enrollmentYear = $input['enrollment_year'] ?? null;

if (!$studentId) {
    http_response_code(422);
    echo json_encode(['error' => 'Missing student_id parameter']);
    exit;
}

try {
    $student = new Student();

    // Force close any hanging transactions with retry logic
    $maxRetries = 3;
    $retryCount = 0;

    while ($student->db->inTransaction() && $retryCount < $maxRetries) {
        try {
            $student->db->rollback();
            $retryCount++;
        } catch (Exception $e) {
            // If rollback fails, create new connection
            $student = new Student();
            $retryCount++;
        }
    }

    if ($student->db->inTransaction()) {
        throw new Exception('Unable to clear existing transaction state');
    }

    $studentData = $student->findById($studentId);

    if (!$studentData) {
        http_response_code(404);
        echo json_encode(['error' => 'Student not found']);
        exit;
    }

    // Check if student already has an index number
    if (!empty($studentData['index_no'])) {
        http_response_code(422);
        echo json_encode([
            'error' => 'Student already has an index number: ' . $studentData['index_no']
        ]);
        exit;
    }

    // Use enrollment year from request, fallback to database value
    $finalEnrollmentYear = $enrollmentYear ?? $studentData['enrollment_year'];

    if (!$finalEnrollmentYear) {
        http_response_code(422);
        echo json_encode(['error' => 'Enrollment year is required']);
        exit;
    }

    // Handle the entire operation in a single transaction
    $student->db->beginTransaction();

    try {
        // Update enrollment year first if needed
        if ($enrollmentYear && $enrollmentYear !== $studentData['enrollment_year']) {
            $stmt = $student->db->prepare("UPDATE students SET enrollment_year = ? WHERE id = ?");
            $updateYearResult = $stmt->execute([$enrollmentYear, $studentId]);

            if (!$updateYearResult) {
                throw new Exception('Failed to update enrollment year');
            }
        }

        // Generate index number manually to avoid nested transactions
        // Get department code
        $stmt = $student->db->prepare("SELECT code FROM departments WHERE id = ?");
        $stmt->execute([$studentData['department_id']]);
        $dept = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dept) {
            throw new Exception("Department not found.");
        }

        $department_code = $dept['code'];
        $enrollment_type = strtoupper(substr($studentData['mode'], 0, 1));
        $pattern = "KUR/{$department_code}/{$finalEnrollmentYear}/{$enrollment_type}";

        // Get and increment sequence number
        $stmt = $student->db->prepare("
            INSERT INTO index_sequences (pattern, next_number) 
            VALUES (?, 1) 
            ON DUPLICATE KEY UPDATE 
                next_number = next_number + 1,
                updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$pattern]);

        // Get the updated number
        $stmt = $student->db->prepare("SELECT next_number FROM index_sequences WHERE pattern = ?");
        $stmt->execute([$pattern]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $nextNumber = $result['next_number'];
        $seq_num = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        $indexNumber = "{$pattern}/{$seq_num}";

        // Verify this index number doesn't exist
        $checkStmt = $student->db->prepare("SELECT id FROM students WHERE index_no = ?");
        $checkStmt->execute([$indexNumber]);
        if ($checkStmt->fetch()) {
            throw new Exception("Generated index number already exists: {$indexNumber}");
        }

        // Update student with index number
        $stmt = $student->db->prepare("UPDATE students SET index_no = ? WHERE id = ?");
        $updateResult = $stmt->execute([$indexNumber, $studentId]);

        if (!$updateResult) {
            throw new Exception('Failed to update student with generated index number');
        }

        // Log the generation
        if (isset($_SESSION['admin_id'])) {
            $stmt = $student->db->prepare("
                INSERT INTO index_generation_log (index_number, department_id, mode, enrollment_year, generated_by)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $indexNumber,
                $studentData['department_id'],
                $studentData['mode'],
                $finalEnrollmentYear,
                $_SESSION['admin_id']
            ]);
        }

        // Log academic status change if needed
        $stmt = $student->db->prepare("
            INSERT INTO academic_status_history (student_id, old_status, new_status, reason, changed_by)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $studentId,
            $studentData['academic_status'],
            $studentData['academic_status'],
            'Index number generated',
            $_SESSION['admin_id'] ?? null
        ]);

        $student->db->commit();

        echo json_encode([
            'success' => true,
            'index_number' => $indexNumber,
            'enrollment_year' => $finalEnrollmentYear,
            'message' => 'Index number generated successfully'
        ]);

    } catch (Exception $e) {
        $student->db->rollback();
        throw $e;
    }

} catch (Exception $e) {
    // Final cleanup
    try {
        if (isset($student) && $student->db->inTransaction()) {
            $student->db->rollback();
        }
    } catch (Exception $rollbackError) {
        error_log("Rollback error in generate_index.php: " . $rollbackError->getMessage());
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error generating index number: ' . $e->getMessage()
    ]);
}