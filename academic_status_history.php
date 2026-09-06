<?php
// public/api/students/academic_status_history.php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Models\Student;
use App\Auth\Middleware;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $studentId = $_GET['student_id'] ?? null;

    if (!$studentId || !is_numeric($studentId)) {
        throw new Exception('Valid student ID is required');
    }

    $student = new Student();

    // Verify student exists
    $studentData = $student->findById((int)$studentId);
    if (!$studentData) {
        throw new Exception('Student not found');
    }

    // Get academic status history
    $history = $student->getAcademicStatusHistory((int)$studentId);

    echo json_encode([
        'success' => true,
        'data' => $history,
        'student' => [
            'id' => $studentData['id'],
            'name' => $studentData['name_with_initials'],
            'index_no' => $studentData['index_no'],
            'current_status' => $studentData['academic_status']
        ]
    ]);

} catch (Exception $e) {
    error_log("Error fetching academic status history: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}