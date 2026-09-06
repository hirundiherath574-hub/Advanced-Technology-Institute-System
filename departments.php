<?php
require_once __DIR__ . '/../../bootstrap.php';
use App\Auth\Middleware;
use App\Database\Connection;
use App\Models\Department;

header('Content-Type: application/json');
Middleware::check('DEPT_FULL');

try {
    $db = Connection::pdo();
    $department = new Department($db);

    $requestMethod = $_SERVER['REQUEST_METHOD'];

    switch ($requestMethod) {
        case 'GET':
            // Get all departments for dropdown/selection purposes
            $stmt = $department->readAll();
            $departments = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $departments[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'code' => $row['code'],
                    'years' => $row['years'],
                    'semesters_per_year' => $row['semesters_per_year'],
                    'has_specializations' => (bool)$row['has_specializations'],
                    'specialization_year' => $row['specialization_year']
                ];
            }

            echo json_encode(['success' => true, 'data' => $departments]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>