<?php
// public/api/students/validate_index.php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Models\Student;
use App\Auth\Middleware;

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'valid' => false,
        'errors' => ['Method not allowed']
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode([
        'valid' => false,
        'errors' => ['Invalid JSON payload']
    ]);
    exit;
}

$indexNumber = trim($input['index_number'] ?? '');
$studentId = (int)($input['student_id'] ?? 0);

if (!$indexNumber || !$studentId) {
    http_response_code(422);
    echo json_encode([
        'valid' => false,
        'errors' => ['Missing required parameters: index_number and student_id are required']
    ]);
    exit;
}

try {
    $student = new Student();

    // CRITICAL: Force cleanup any hanging transactions FIRST
    while ($student->db->inTransaction()) {
        $student->db->rollback();
    }

    // Use the existing validateManualIndexNumber method
    $validation = $student->validateManualIndexNumber($indexNumber, $studentId);

    // Add additional real-time checks (READ-ONLY operations)
    if (preg_match('/^KUR\/[A-Z]{2,10}\/[0-9]{4}\/[FP]\/[0-9]{4}$/', $indexNumber)) {
        $parts = explode('/', $indexNumber);

        // Verify department exists
        $stmt = $student->db->prepare("SELECT id, name, code FROM departments WHERE code = ?");
        $stmt->execute([$parts[1]]);
        $dept = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dept) {
            $validation['errors'][] = "Department code '{$parts[1]}' does not exist";
            $validation['valid'] = false;
        }

        // Validate year format
        $year = $parts[2];
        $currentYear = date('Y');
        if ($year < ($currentYear - 10) || $year > ($currentYear + 5)) {
            $validation['warnings'][] = "Year '{$year}' seems unusual (too far in past or future)";
        }

        // Check sequence number
        $sequence = intval($parts[4]);
        if ($sequence > 9000) {
            $validation['warnings'][] = "Sequence number '{$parts[4]}' is very high - please verify";
        }

        // Check for sequence gaps (READ-ONLY)
        if ($validation['valid'] && $dept) {
            $pattern = "KUR/{$parts[1]}/{$parts[2]}/{$parts[3]}";

            $stmt = $student->db->prepare("
                SELECT index_no 
                FROM students 
                WHERE index_no LIKE ? 
                AND index_no != ?
                ORDER BY index_no
            ");
            $stmt->execute(["{$pattern}/%", $indexNumber]);
            $existingIndexes = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($existingIndexes)) {
                $sequences = [];
                foreach ($existingIndexes as $idx) {
                    $idxParts = explode('/', $idx);
                    if (count($idxParts) === 5) {
                        $sequences[] = intval($idxParts[4]);
                    }
                }

                sort($sequences);
                $maxSequence = max($sequences);

                if ($sequence < $maxSequence - 50) {
                    $validation['warnings'][] = "This sequence number ({$sequence}) creates a significant gap. Latest sequence is {$maxSequence}";
                }

                $suggestedSequence = $maxSequence + 1;
                if ($sequence < $suggestedSequence - 10) {
                    $validation['suggestions'][] = "Consider using sequence {$suggestedSequence} to maintain order";
                }
            }
        }
    }

    // Add helpful suggestions for common mistakes
    if (!$validation['valid']) {
        $suggestions = [];

        if (!preg_match('/^KUR\//', $indexNumber)) {
            $suggestions[] = "Index numbers should start with 'KUR/'";
        }

        if (!preg_match('/\/[FP]\//', $indexNumber)) {
            $suggestions[] = "Mode should be 'F' for full-time or 'P' for part-time";
        }

        if (!preg_match('/\/\d{4}$/', $indexNumber)) {
            $suggestions[] = "Sequence number should be exactly 4 digits (e.g., 0001)";
        }

        if (!empty($suggestions)) {
            $validation['suggestions'] = $suggestions;
        }

        $validation['example_format'] = "KUR/IT/2024/F/0001";
    }

    // Ensure arrays exist
    if (!isset($validation['warnings'])) {
        $validation['warnings'] = [];
    }
    if (!isset($validation['suggestions'])) {
        $validation['suggestions'] = [];
    }

    echo json_encode($validation);

} catch (Exception $e) {
    // Aggressive cleanup on error
    try {
        if (isset($student) && $student->db) {
            while ($student->db->inTransaction()) {
                $student->db->rollback();
            }
        }
    } catch (Exception $rollbackError) {
        error_log('Validate API rollback error: ' . $rollbackError->getMessage());
    }

    error_log('Index validation error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'valid' => false,
        'errors' => ['Validation error: ' . $e->getMessage()],
        'warnings' => [],
        'suggestions' => []
    ]);
}
?>