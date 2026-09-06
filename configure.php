<?php
// public/api/semester_curriculum/configure.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Auth\Middleware;
use App\Database\Connection;
use App\Models\SemesterCurriculumService;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

Middleware::check('DEPT_FULL');

try {
    $db = Connection::pdo();
    $service = new SemesterCurriculumService($db);

    $requestMethod = $_SERVER['REQUEST_METHOD'];

    switch ($requestMethod) {
        case 'POST':
            // Configure curriculum for a semester
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['semester_id']) || !isset($input['subjects'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Semester ID and subjects are required'
                ]);
                break;
            }

            // Validate subjects array
            if (!is_array($input['subjects'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Subjects must be an array'
                ]);
                break;
            }

            $replace_existing = $input['replace_existing'] ?? false;
            $result = $service->configureCurriculum(
                $input['semester_id'],
                $input['subjects'],
                $replace_existing
            );

            http_response_code($result['success'] ? 201 : 400);
            echo json_encode($result);
            break;

        case 'PUT':
            // Add single subject to curriculum
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['semester_id']) || !isset($input['subject_data'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Semester ID and subject data are required'
                ]);
                break;
            }

            $result = $service->addSubjectToCurriculum(
                $input['semester_id'],
                $input['subject_data']
            );

            http_response_code($result['success'] ? 201 : 400);
            echo json_encode($result);
            break;

        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed'
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Semester Curriculum Get API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}