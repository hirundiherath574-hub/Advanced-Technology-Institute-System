<?php
// public/api/students/pending.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Models\Student;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $student = new Student();
    $pendingRegistrations = $student->getPendingRegistrations();

    echo json_encode([
        'success' => true,
        'data' => $pendingRegistrations
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load pending registrations: ' . $e->getMessage()
    ]);
}
?>