<?php
// api/students/assign_semester.php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Auth\Middleware;
use App\Models\SemesterManagement;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Check authentication
    Middleware::check('DEPT_FULL');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }

    // Get JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('Invalid JSON data');
    }

    // Validate input
    if (empty($data['student_ids']) || !is_array($data['student_ids'])) {
        throw new Exception('Student IDs array is required');
    }

    if (empty($data['semester_id'])) {
        throw new Exception('Semester ID is required');
    }

    // Use the SemesterManagement model
    $semesterManagement = new SemesterManagement();
    $result = $semesterManagement->assignStudentsToSemester(
        $data['student_ids'],
        $data['semester_id']
    );

    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>