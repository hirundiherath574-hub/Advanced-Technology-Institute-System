<?php
// api/semesters/enrolled-students.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Auth\Middleware;
use App\Models\SemesterManagement;

header('Content-Type: application/json');
Middleware::check('DEPT_FULL');

try {
    if (!isset($_GET['semester_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Semester ID is required']);
        exit;
    }

    $semesterManagement = new SemesterManagement();
    $currentOnly = isset($_GET['current_only']) ? (bool)$_GET['current_only'] : true;

    $students = $semesterManagement->getStudentsBySemester($_GET['semester_id'], $currentOnly);

    echo json_encode(['success' => true, 'data' => $students]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>