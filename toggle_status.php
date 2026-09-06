<?php
// api/admin_users/toggle_status.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Auth\Middleware;
use App\Models\UserService;

header('Content-Type: application/json');

try {
    // Check if user has permission to manage users
    Middleware::check('DEPT_FULL');

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new \Exception('Invalid JSON input');
    }

    $userId = $input['user_id'] ?? 0;
    $action = $input['action'] ?? '';

    if (empty($userId)) {
        throw new \Exception('User ID is required');
    }

    if (!in_array($action, ['activate', 'deactivate'])) {
        throw new \Exception('Invalid action specified');
    }

    $userService = new UserService();

    if ($action === 'activate') {
        $result = $userService->toggleUserStatus((int)$userId, 'activate');
    } else {
        $result = $userService->toggleUserStatus((int)$userId, 'deactivate');
    }

    if ($result['success']) {
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }
} catch (\Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>