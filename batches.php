<?php
// api/students/batches.php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Database\Connection;
use App\Auth\Middleware;
use App\Models\Student;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Check authentication
    Middleware::check('DEPT_FULL');

    // Get parameters
    $department_id = $_GET['department_id'] ?? '';
    $mode = $_GET['mode'] ?? '';

    if (empty($department_id) || empty($mode)) {
        throw new Exception('Department ID and mode are required');
    }

    // Create Student model instance
    $studentModel = new Student();

    // Get batches using the model method
    $batches = $studentModel->getBatches((int)$department_id, $mode);

    echo json_encode([
        'success' => true,
        'batches' => $batches,
        'total' => count($batches)
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>