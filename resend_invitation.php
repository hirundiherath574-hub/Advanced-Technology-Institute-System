<?php
// public/api/students/resend_invitation.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Models\Student;
use App\Auth\Middleware;
use App\Email\Sender;

Middleware::check('STUDENT_WRITE');

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$studentId = (int)($input['student_id'] ?? 0);
if (!$studentId) {
    http_response_code(422);
    echo json_encode(['error' => 'Student ID is required']);
    exit;
}

try {
    $student = new Student();
    $studentData = $student->findById($studentId);

    if (!$studentData) {
        http_response_code(404);
        echo json_encode(['error' => 'Student not found']);
        exit;
    }

    if ($studentData['completed']) {
        http_response_code(422);
        echo json_encode(['error' => 'Student has already completed registration']);
        exit;
    }

    // Send email
    $sent = $student->sendPreEnrollmentEmail($studentId);

    if (!$sent) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to send email']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Email sent successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to resend email'
    ]);
}
