<?php
// public/api/students/index_history.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Models\Student;
use App\Auth\Middleware;

Middleware::check('STUDENT_READ');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : null;

try {
    $student = new Student();
    $history = $student->getManualIndexHistory($studentId);

    echo json_encode([
        'success' => true,
        'data' => $history
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error fetching index history: ' . $e->getMessage()
    ]);
}