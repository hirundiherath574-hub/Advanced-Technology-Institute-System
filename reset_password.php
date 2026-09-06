<?php
// public/api/admin_users/reset_password.php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Auth\Middleware;
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
    // Check authentication and permissions
    Middleware::check('DEPT_FULL');

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
    if (!isset($input['user_id']) || !is_numeric($input['user_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'User ID is required and must be numeric'
        ]);
        exit;
    }

    $userId = (int)$input['user_id'];

    // Initialize UserService
    $userService = new UserService();

    // Get user details to verify they exist and get their current status
    $user = $userService->getUserById($userId);

    if (!$user) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }

    // Check if user has admin roles
    $adminRoles = ['super_admin', 'registrar', 'staff'];
    $hasAdminRole = false;
    foreach ($user['roles'] as $role) {
        if (in_array($role, $adminRoles)) {
            $hasAdminRole = true;
            break;
        }
    }

    if (!$hasAdminRole) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'User does not have administrator privileges'
        ]);
        exit;
    }

    // Check if user account is active
    if ($user['status'] != 1) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Cannot reset password for inactive user. Please activate the user first.'
        ]);
        exit;
    }

    // Send password reset
    $result = $userService->sendPasswordReset($userId);

    if ($result['success']) {
        // Log the action for audit trail
        error_log("Password reset initiated by admin for user ID: {$userId} ({$user['email']})");

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => $result['message'],
            'email_sent' => $result['email_sent'] ?? true
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
    error_log("Error in reset_password.php: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error occurred'
    ]);
}
?>