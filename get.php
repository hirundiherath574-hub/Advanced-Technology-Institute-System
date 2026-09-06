<?php
// public/api/subjects/get.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Auth\Middleware;
use App\Database\Connection;
use App\Models\Subject;

header('Content-Type: application/json');
Middleware::check('DEPT_FULL');

try {
    $db = Connection::pdo();
    $subject = new Subject($db);

    if (isset($_GET['id'])) {
        // Get single subject
        $id = intval($_GET['id']);
        if ($subject->readOne($id)) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'id' => $subject->id,
                    'department_id' => $subject->department_id,
                    'name' => $subject->name,
                    'code' => $subject->code,
                    'credits' => $subject->credits,
                    'status' => $subject->status,
                    'has_written_exam' => (bool)$subject->has_written_exam
                ]
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Subject not found']);
        }

    } elseif (isset($_GET['department_id'])) {
        // Get subjects by department
        $departmentId = intval($_GET['department_id']);
        $stmt = $subject->getByDepartment($departmentId);
        $subjects = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $subjects[] = [
                'id' => $row['id'],
                'department_id' => $row['department_id'],
                'name' => $row['name'],
                'code' => $row['code'],
                'credits' => $row['credits'],
                'status' => $row['status'],
                'has_written_exam' => (bool)$row['has_written_exam']
            ];
        }

        echo json_encode(['success' => true, 'data' => $subjects]);

    } elseif (isset($_GET['search'])) {
        // Search subjects
        $searchTerm = $_GET['search'];
        $departmentId = isset($_GET['dept_filter']) ? intval($_GET['dept_filter']) : null;
        $stmt = $subject->search($searchTerm, $departmentId);
        $subjects = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $subjects[] = [
                'id' => $row['id'],
                'department_id' => $row['department_id'],
                'name' => $row['name'],
                'code' => $row['code'],
                'credits' => $row['credits'],
                'status' => $row['status'],
                'has_written_exam' => (bool)$row['has_written_exam'],
                'department_name' => $row['department_name'],
                'department_code' => $row['department_code']
            ];
        }

        echo json_encode(['success' => true, 'data' => $subjects]);

    } else {
        // Get all subjects
        $departmentFilter = isset($_GET['dept_filter']) ? intval($_GET['dept_filter']) : null;
        $stmt = $subject->readAll($departmentFilter);
        $subjects = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $subjects[] = [
                'id' => $row['id'],
                'department_id' => $row['department_id'],
                'name' => $row['name'],
                'code' => $row['code'],
                'credits' => $row['credits'],
                'status' => $row['status'],
                'has_written_exam' => (bool)$row['has_written_exam'],
                'department_name' => $row['department_name'],
                'department_code' => $row['department_code']
            ];
        }

        echo json_encode(['success' => true, 'data' => $subjects]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>