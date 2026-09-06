<?php
// api/semesters/curriculum.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Models\SemesterCurriculum;
use App\Auth\Middleware;

header('Content-Type: application/json');
//Middleware::check('DEPT_VIEW');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$semester_id = $_GET['semester_id'] ?? null;

if (!$semester_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Semester ID is required']);
    exit;
}

try {
    $semesterCurriculum = new SemesterCurriculum(null);
    $result = $semesterCurriculum->readWithDetails($semester_id);

    echo json_encode([
        'success' => true,
        'data' => $result
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load curriculum: ' . $e->getMessage()
    ]);
}
?>