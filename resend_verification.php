<?php
// public/api/admin_users/resend_verification.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Auth\Middleware;
use App\Models\UserService;

// Check permissions
Middleware::check('DEPT_FULL');

header('Content-Type: application/json');

try {
    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }

    // Validate required fields
    $userId = $input['user_id'] ?? null;

    if (empty($userId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }

    // Validate user ID
    if (!is_numeric($userId) || $userId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        exit;
    }

    $userService = new UserService();

    // Resend verification email
    $result = $userService->resendVerificationEmail((int)$userId);

    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => $result['message'],
            'email_sent' => $result['email_sent']
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }

} catch (\Exception $e) {
    error_log("Error in resend verification API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}