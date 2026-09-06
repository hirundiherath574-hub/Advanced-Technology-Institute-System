<?php
// api/students/list.php - Updated to include academic status filtering
require_once __DIR__ . '/../../../bootstrap.php';
use App\Models\Student;
use App\Auth\Middleware;

header('Content-Type: application/json');

// Check authentication and permissions
try {
    Middleware::check('STUDENT_READ');
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

try {
    $student = new Student();

    // Build filters from query parameters
    $filters = [];

    if (isset($_GET['department']) && !empty($_GET['department'])) {
        $filters['department'] = $_GET['department'];
    }

    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $filters['status'] = $_GET['status'];
    }

    if (isset($_GET['incomplete']) && !empty($_GET['incomplete'])) {
        $filters['incomplete'] = $_GET['incomplete'];
    }

    if (isset($_GET['academic_status']) && !empty($_GET['academic_status'])) {
        $filters['academic_status'] = $_GET['academic_status'];
    }

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $filters['search'] = $_GET['search'];
    }

    // Get students with filters
    $students = $student->getAll($filters);

    // Format the response
    $response = [
        'success' => true,
        'data' => $students,
        'count' => count($students)
    ];

    // Add summary statistics if requested
    if (isset($_GET['include_stats']) && $_GET['include_stats'] === 'true') {
        $stats = $student->getAcademicStatusStats();
        $response['academic_status_stats'] = $stats;
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch students: ' . $e->getMessage()
    ]);
}