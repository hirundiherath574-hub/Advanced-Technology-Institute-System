<?php
// public/api/students/assign_manual_index.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Models\Student;
use App\Auth\Middleware;
use App\Database\Connection;
use Delight\Auth\Auth;

//Middleware::check('STUDENT_WRITE');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$studentId = (int)($input['student_id'] ?? 0);
$indexNumber = trim($input['index_number'] ?? '');

if (!$studentId || !$indexNumber) {
    http_response_code(422);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

try {
    $student = new Student();

    // Get admin ID using Delight Auth
    $pdo = Connection::pdo();
    $auth = new Auth($pdo);
    $adminId = $auth->isLoggedIn() ? $auth->getUserId() : null;

    // Validate the manual index number first
    $validation = $student->validateManualIndexNumber($indexNumber, $studentId);

    if (!$validation['valid']) {
        http_response_code(422);
        echo json_encode([
            'error' => 'Validation failed',
            'errors' => $validation['errors']
        ]);
        exit;
    }

    // Assign the manual index number
    $result = $student->setManualIndexNumber($studentId, $indexNumber, $adminId);

    if ($result) {
        echo json_encode([
            'success' => true,
            'index_number' => $indexNumber,
            'message' => 'Manual index number assigned successfully',
            'warnings' => $validation['warnings'] ?? []
        ]);
    } else {
        throw new Exception('Failed to assign manual index number');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error assigning manual index number: ' . $e->getMessage()
    ]);
}