<?php
// public/api/auth/complete_reset.php
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

    if (!isset($input['password']) || empty($input['password'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'New password is required'
        ]);
        exit;
    }

    $token = $input['token'];
    $password = $input['password'];

    // Validate password strength
    if (strlen($password) < 8) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 8 characters long'
        ]);
        exit;
    }

    // [Unverified] Additional password strength checks could be added here
    // Such as requiring uppercase, lowercase, numbers, special characters

    // Initialize UserService
    $userService = new UserService();

    // Complete the password reset
    $result = $userService->completePasswordReset($token, $password);

    if ($result['success']) {
        // Log the successful password reset
        error_log("Password reset completed successfully for user ID: " . $result['user_id']);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => $result['message']
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
    error_log("Error in complete_reset.php: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error occurred'
    ]);
}
?>