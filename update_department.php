<?php
// api/update_department.php
require_once __DIR__ . '/../../bootstrap.php';
use App\Auth\Middleware;
use App\Database\Connection;
use App\Models\DepartmentService;

// Check authentication
Middleware::check('DEPT_FULL');

// Set JSON header
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    // Validate required fields
    if (empty($input['id'])) {
        throw new Exception('Department ID is required');
    }

    // Get database connection
    $db = Connection::pdo();

    // Create department service
    $departmentService = new DepartmentService($db);

    // Validate input data
    $errors = $departmentService->validateDepartmentData($input);
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ]);
        exit;
    }

    // Update department
    $result = $departmentService->updateDepartmentWithSpecializations($input['id'], $input);

    // Return response
    echo json_encode($result);

} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update department: ' . $e->getMessage()
    ]);
}
?>