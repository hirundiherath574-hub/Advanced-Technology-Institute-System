<?php
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
    $stats = $semesterManagement->getSemesterStats($_GET['semester_id']);

    echo json_encode(['success' => true, 'data' => $stats]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>