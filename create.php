<?php
// public/api/subjects/create.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Auth\Middleware;
use App\Database\Connection;
use App\Models\Subject;

header('Content-Type: application/json');
Middleware::check('DEPT_FULL');

try {
    $db = Connection::pdo();
    $subject = new Subject($db);

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit;
    }

    // Check if it's bulk creation
    if (isset($data['bulk']) && $data['bulk'] === true && isset($data['subjects'])) {
        // Bulk subject creation
        $subjects = $data['subjects'];
        $department_id = $data['department_id'];

        if (empty($department_id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Department ID is required for bulk creation']);
            exit;
        }

        if (empty($subjects) || !is_array($subjects)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Subjects array is required for bulk creation']);
            exit;
        }

        $created_subjects = [];
        $errors = [];
        $db->beginTransaction();

        try {
            foreach ($subjects as $index => $subjectData) {
                // Validation for each subject
                $subjectErrors = [];

                if (empty($subjectData['name'])) {
                    $subjectErrors[] = "Subject name is required";
                }

                if (empty($subjectData['code'])) {
                    $subjectErrors[] = "Subject code is required";
                } elseif ($subject->codeExists($subjectData['code'])) {
                    $subjectErrors[] = "Subject code '{$subjectData['code']}' already exists";
                }

                if (empty($subjectData['credits']) || !is_numeric($subjectData['credits']) || $subjectData['credits'] < 1) {
                    $subjectErrors[] = "Valid credits number is required (minimum 1)";
                }

                if (empty($subjectData['status']) || !in_array($subjectData['status'], ['GPA', 'Non-GPA'])) {
                    $subjectErrors[] = "Status must be either GPA or Non-GPA";
                }

                if (!empty($subjectErrors)) {
                    $errors["subject_" . ($index + 1)] = $subjectErrors;
                    continue;
                }

                // Create subject
                $subject->department_id = $department_id;
                $subject->name = $subjectData['name'];
                $subject->code = strtoupper($subjectData['code']);
                $subject->credits = $subjectData['credits'];
                $subject->status = $subjectData['status'];
                $subject->has_written_exam = isset($subjectData['has_written_exam']) ? $subjectData['has_written_exam'] : true;

                if ($subject->create()) {
                    $created_subjects[] = [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'code' => $subject->code,
                        'credits' => $subject->credits,
                        'status' => $subject->status,
                        'has_written_exam' => $subject->has_written_exam
                    ];
                } else {
                    $errors["subject_" . ($index + 1)] = ["Failed to create subject"];
                }
            }

            if (!empty($errors)) {
                $db->rollBack();
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Validation errors occurred',
                    'errors' => $errors
                ]);
            } else {
                $db->commit();
                echo json_encode([
                    'success' => true,
                    'message' => count($created_subjects) . ' subjects created successfully',
                    'data' => $created_subjects
                ]);
            }

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

    } else {
        // Single subject creation
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Subject name is required';
        }

        if (empty($data['code'])) {
            $errors[] = 'Subject code is required';
        } elseif ($subject->codeExists($data['code'])) {
            $errors[] = 'Subject code already exists';
        }

        if (empty($data['department_id'])) {
            $errors[] = 'Department is required';
        }

        if (empty($data['credits']) || !is_numeric($data['credits']) || $data['credits'] < 1) {
            $errors[] = 'Valid credits number is required (minimum 1)';
        }

        if (empty($data['status']) || !in_array($data['status'], ['GPA', 'Non-GPA'])) {
            $errors[] = 'Status must be either GPA or Non-GPA';
        }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit;
        }

        // Set subject properties
        $subject->department_id = $data['department_id'];
        $subject->name = $data['name'];
        $subject->code = strtoupper($data['code']);
        $subject->credits = $data['credits'];
        $subject->status = $data['status'];
        $subject->has_written_exam = isset($data['has_written_exam']) ? $data['has_written_exam'] : true;

        if ($subject->create()) {
            echo json_encode([
                'success' => true,
                'message' => 'Subject created successfully',
                'data' => ['id' => $subject->id]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create subject']);
        }
    }

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>