<?php
// add_department.php - Backend endpoint for creating departments

// Set headers for JSON response and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Include necessary files
require_once '../../bootstrap.php';
use App\Auth\Middleware;
Middleware::check('DEPT_FULL');

use App\Models\Department;
use App\Models\Specialization;
use App\Models\DepartmentService;
use App\Database\Connection;

try {
    // Get database connection
    $db = Connection::pdo();

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate JSON input
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }

    // Create department service instance
    $departmentService = new DepartmentService($db);

    // Validate the data
    $errors = $departmentService->validateDepartmentData($input);

    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ]);
        exit();
    }

    // Create department with specializations
    $result = $departmentService->createDepartmentWithSpecializations($input);

    // Return response
    echo json_encode($result);

} catch (Exception $e) {
    // Log error (in production, use proper logging)
    error_log('Department creation error: ' . $e->getMessage());

    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}
?>