<?php
// api/get_departments.php
require_once __DIR__ . '/../../bootstrap.php';
use App\Auth\Middleware;
use App\Database\Connection;
use App\Models\DepartmentService;

// Check authentication
Middleware::check('DEPT_FULL');

// Set JSON header
header('Content-Type: application/json');

try {
    // Get database connection
    $db = Connection::pdo();

    // Create department service
    $departmentService = new DepartmentService($db);

    // Get all departments with specializations
    $result = $departmentService->getAllDepartmentsWithSpecializations();

    // Return response
    echo json_encode($result);

} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve departments: ' . $e->getMessage()
    ]);
}
?>