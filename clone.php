<?php
// public/api/role_config/clone.php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Auth\Middleware;
use App\Models\RoleConfigurationService;

header('Content-Type: application/json');

try {
    // Check if user has permission to manage roles
    Middleware::check('DEPT_FULL');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }

    // Validate required fields
    if (empty($input['source_role_id']) || empty($input['new_name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Source role ID and new name are required']);
        exit;
    }

    $service = new RoleConfigurationService();

    $result = $service->cloneRole(
        (int) $input['source_role_id'],
        trim($input['new_name']),
        trim($input['description'] ?? '')
    );

    if ($result['success']) {
        echo json_encode($result);
    } else {
        http_response_code(400);
        echo json_encode($result);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error cloning role: ' . $e->getMessage()
    ]);
}