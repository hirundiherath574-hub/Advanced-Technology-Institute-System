<?php
// public/api/students/preview_index.php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Models\Student;
use App\Auth\Middleware;

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

// Validate required parameters
$departmentId = (int)($input['department_id'] ?? 0);
$mode = $input['mode'] ?? '';
$enrollmentYear = $input['enrollment_year'] ?? '';

if (!$departmentId || !in_array($mode, ['FULL', 'PART']) || !$enrollmentYear) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid or missing parameters',
        'details' => [
            'department_id' => $departmentId ?: 'required',
            'mode' => in_array($mode, ['FULL', 'PART']) ? $mode : 'must be FULL or PART',
            'enrollment_year' => $enrollmentYear ?: 'required'
        ]
    ]);
    exit;
}

// Additional validation
if (!preg_match('/^\d{4}$/', $enrollmentYear)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'error' => 'Enrollment year must be a 4-digit year'
    ]);
    exit;
}

try {
    $student = new Student();

    // CRITICAL: Force cleanup any hanging transactions FIRST
    while ($student->db->inTransaction()) {
        $student->db->rollback();
    }

    // Use the corrected method from the Student class
    $previewIndex = $student->getNextAvailableIndexNumber($departmentId, $mode, $enrollmentYear);

    // Parse the index to get pattern info
    $parsed = $student->parseIndexNumber($previewIndex);
    $pattern = "KUR/{$parsed['department']}/{$parsed['year']}/{$parsed['mode']}";

    // Additional context
    $additionalInfo = [];

    // Check if this is the first student in this category
    $stmt = $student->db->prepare("SELECT next_number FROM index_sequences WHERE pattern = ?");
    $stmt->execute([$pattern]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        $additionalInfo['note'] = 'This will be the first student in this category';
    }

    // Get count of existing students
    $stmt = $student->db->prepare("
        SELECT COUNT(*) as count 
        FROM students 
        WHERE index_no LIKE ? 
        AND index_no IS NOT NULL
    ");
    $stmt->execute(["{$pattern}/%"]);
    $existingCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

    $additionalInfo['existing_students'] = $existingCount;
    $additionalInfo['pattern'] = $pattern;
    $additionalInfo['sequence_number'] = $parsed['sequence'];

    echo json_encode([
        'success' => true,
        'preview_index_number' => $previewIndex,
        'info' => $additionalInfo
    ]);

} catch (Exception $e) {
    // Aggressive cleanup on error
    try {
        if (isset($student) && $student->db) {
            while ($student->db->inTransaction()) {
                $student->db->rollback();
            }
        }
    } catch (Exception $rollbackError) {
        error_log('Preview API rollback error: ' . $rollbackError->getMessage());
    }

    error_log('Preview API error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error generating preview: ' . $e->getMessage()
    ]);
}
?>