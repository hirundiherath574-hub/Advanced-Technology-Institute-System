<?php
// api/students/academic_status.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Models\Student;
use App\Auth\Middleware;

header('Content-Type: application/json');

// Check authentication and permissions
try {
    Middleware::check('STUDENT_WRITE');
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$student = new Student();

try {
    switch ($method) {
        case 'POST':
            // Update student academic status
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['student_id'], $input['new_status'], $input['reason'])) {
                throw new Exception('Missing required fields: student_id, new_status, reason');
            }

            $validStatuses = ['Active', 'Inactive', 'OnLeave', 'Suspended'];
            if (!in_array($input['new_status'], $validStatuses)) {
                throw new Exception('Invalid status. Must be one of: ' . implode(', ', $validStatuses));
            }

            // [Inference] Assuming session management exists for changed_by
            $changedBy = $_SESSION['user_id'] ?? null;

            $result = $student->updateAcademicStatus(
                $input['student_id'],
                $input['new_status'],
                $input['reason'],
                $changedBy
            );

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Academic status updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update academic status');
            }
            break;

        case 'GET':
            if (isset($_GET['student_id'])) {
                // Get academic status history for a specific student
                $history = $student->getAcademicStatusHistory($_GET['student_id']);
                echo json_encode([
                    'success' => true,
                    'data' => $history
                ]);
            } elseif (isset($_GET['stats'])) {
                // Get academic status statistics
                $stats = $student->getAcademicStatusStats();
                echo json_encode([
                    'success' => true,
                    'data' => $stats
                ]);
            } elseif (isset($_GET['status'])) {
                // Get students by academic status
                $students = $student->getByAcademicStatus($_GET['status']);
                echo json_encode([
                    'success' => true,
                    'data' => $students
                ]);
            } else {
                throw new Exception('Missing required parameters');
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}