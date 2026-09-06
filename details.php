<?php
// public/api/students/details.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Models\Student;
use App\Auth\Middleware;

Middleware::check('STUDENT_READ');

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$studentId = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$studentId) {
    http_response_code(400);
    echo json_encode(['error' => 'Student ID is required']);
    exit;
}

$student = new Student();

try {
    switch ($method) {
        case 'GET':
            // Get student details
            $studentData = $student->findById((int)$studentId);
            if (!$studentData) {
                http_response_code(404);
                echo json_encode(['error' => 'Student not found']);
                exit;
            }

            echo json_encode([
                'success' => true,
                'data' => $studentData
            ]);
            break;

        case 'PUT':
            // Update student details
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON']);
                exit;
            }

            // Check if student exists
            $existingStudent = $student->findById((int)$studentId);
            if (!$existingStudent) {
                http_response_code(404);
                echo json_encode(['error' => 'Student not found']);
                exit;
            }

            // Validate required fields if student is being marked as completed
            if (isset($input['completed']) && $input['completed']) {
                $requiredFields = ['full_name', 'name_with_initials', 'address', 'contact_no'];
                foreach ($requiredFields as $field) {
                    if (empty($input[$field])) {
                        http_response_code(422);
                        echo json_encode(['error' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
                        exit;
                    }
                }
            }

            $success = $student->update((int)$studentId, $input);
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Student updated successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update student']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error occurred'
    ]);
}