<?php
// public/api/auth/verify_reset_token.php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Models\UserService;

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Only POST requests are accepted.'
    ]);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON input'
        ]);
        exit;
    }

    // Validate required fields
    if (!isset($input['token']) || empty($input['token'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Reset token is required'
        ]);
        exit;
    }

    $token = $input['token'];

    // Initialize UserService
    $userService = new UserService();

    // Verify the reset token
    $result = $userService->verifyPasswordResetToken($token);

    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'user' => $result['user']
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }

} catch (Exception $e) {
    // Log the error
    error_log("Error in verify_reset_token.php: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error occurred'
    ]);
}
?>