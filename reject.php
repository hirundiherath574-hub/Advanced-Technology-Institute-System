<?php
// public/api/students/reject.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Models\Student;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['student_id']) || !is_numeric($input['student_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid student ID']);
    exit;
}

if (!isset($input['reason']) || empty(trim($input['reason']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Rejection reason is required']);
    exit;
}

try {
    $student = new Student();
    $studentId = (int)$input['student_id'];
    $reason = trim($input['reason']);
    $allowResubmission = isset($input['allow_resubmission']) && $input['allow_resubmission'] === true;

    // Reject the registration
    $success = $student->rejectPendingRegistration($studentId, $reason, null, $allowResubmission);

    if ($success) {
        if ($allowResubmission) {
            // Send rejection email with resubmission link
            $emailSent = $student->sendRejectionWithResubmissionEmail($studentId, $reason);

            if ($emailSent) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Student registration rejected and resubmission email sent'
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => 'Student registration rejected but resubmission email failed to send'
                ]);
            }
        } else {
            // Send regular rejection email
            $emailSent = $student->sendRejectionEmail($studentId, $reason);

            if ($emailSent) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Student registration rejected and notification email sent'
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => 'Student registration rejected but email failed to send'
                ]);
            }
        }
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to reject student registration'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error rejecting student: ' . $e->getMessage()
    ]);
}
?>