<?php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Auth\Middleware;
use App\Models\SemesterManagement;

header('Content-Type: application/json');
Middleware::check('DEPT_FULL');

try {
    $semesterManagement = new SemesterManagement();

    $filters = [];
    if (isset($_GET['semester_id'])) {
        $filters['semester_id'] = $_GET['semester_id'];
    }
    if (isset($_GET['assigned_by'])) {
        $filters['assigned_by'] = $_GET['assigned_by'];
    }
    if (isset($_GET['date_from'])) {
        $filters['date_from'] = $_GET['date_from'];
    }
    if (isset($_GET['date_to'])) {
        $filters['date_to'] = $_GET['date_to'];
    }

    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

    $history = $semesterManagement->getAssignmentHistory($filters, $limit, $offset);

    // Add action field for frontend compatibility
    foreach ($history as &$item) {
        if ($item['student_count'] > 1) {
            $item['action'] = 'bulk_assign';
        } else {
            $item['action'] = 'individual_assign';
        }
    }

    echo json_encode(['success' => true, 'data' => $history]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>