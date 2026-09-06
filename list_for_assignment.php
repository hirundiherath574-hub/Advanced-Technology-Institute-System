<?php
// api/students/list_for_assignment.php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Models\Student;
use App\Auth\Middleware;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Check authentication
    Middleware::check('DEPT_FULL');

    // Get parameters
    $department_id = $_GET['department_id'] ?? '';
    $mode = $_GET['mode'] ?? '';
    $batch = $_GET['batch'] ?? '';
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 50);
    $use_pagination = isset($_GET['paginate']) && $_GET['paginate'] === 'true';

    if (empty($department_id) || empty($mode) || empty($batch)) {
        throw new Exception('Department ID, mode, and batch are required');
    }

    $studentModel = new Student();

    if ($use_pagination) {
        // Use paginated version with additional filters
        $filters = [];

        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }

        if (!empty($_GET['has_current_semester'])) {
            $filters['has_current_semester'] = $_GET['has_current_semester'];
        }

        if (!empty($_GET['semester_year'])) {
            $filters['semester_year'] = $_GET['semester_year'];
        }

        $result = $studentModel->getStudentsForSemesterAssignmentPaginated(
            (int)$department_id,
            $mode,
            $batch,
            $filters,
            $page,
            $limit
        );

        echo json_encode([
            'success' => true,
            'students' => $result['students'],
            'pagination' => $result['pagination'],
            'total' => $result['pagination']['total_records']
        ]);

    } else {
        // Use simple version (backward compatibility)
        $students = $studentModel->getStudentsForSemesterAssignment(
            (int)$department_id,
            $mode,
            $batch
        );

        echo json_encode([
            'success' => true,
            'students' => $students,
            'total' => count($students)
        ]);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>