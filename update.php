<?php
// public/api/subjects/update.php
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

    if (!$data || !isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Subject ID is required']);
        exit;
    }

    $id = intval($data['id']);
    if (!$subject->readOne($id)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Subject not found']);
        exit;
    }

    // Check if it's bulk update
    if (isset($data['bulk']) && $data['bulk'] === true && isset($data['subjects'])) {
        // Bulk subject update
        $subjects_data = $data['subjects'];

        if (empty($subjects_data) || !is_array($subjects_data)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Subjects array is required for bulk update']);
            exit;
        }

        $updated_subjects = [];
        $errors = [];
        $db->beginTransaction();

        try {
            foreach ($subjects_data as $index => $subjectData) {
                if (!isset($subjectData['id'])) {
                    $errors["subject_" . ($index + 1)] = ["Subject ID is required"];
                    continue;
                }

                $subjectId = intval($subjectData['id']);
                if (!$subject->readOne($subjectId)) {
                    $errors["subject_" . ($index + 1)] = ["Subject not found"];
                    continue;
                }

                // Validation for each subject
                $subjectErrors = [];

                if (empty($subjectData['name'])) {
                    $subjectErrors[] = "Subject name is required";
                }

                if (empty($subjectData['code'])) {
                    $subjectErrors[] = "Subject code is required";
                } elseif ($subject->codeExists($subjectData['code'], $subjectId)) {
                    $subjectErrors[] = "Subject code '{$subjectData['code']}' already exists";
                }

                if (empty($subjectData['department_id'])) {
                    $subjectErrors[] = "Department is required";
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

                // Update subject
                $subject->id = $subjectId;
                $subject->department_id = $subjectData['department_id'];
                $subject->name = $subjectData['name'];
                $subject->code = strtoupper($subjectData['code']);
                $subject->credits = $subjectData['credits'];
                $subject->status = $subjectData['status'];
                $subject->has_written_exam = isset($subjectData['has_written_exam']) ? $subjectData['has_written_exam'] : true;

                if ($subject->update()) {
                    $updated_subjects[] = [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'code' => $subject->code,
                        'credits' => $subject->credits,
                        'status' => $subject->status,
                        'has_written_exam' => $subject->has_written_exam
                    ];
                } else {
                    $errors["subject_" . ($index + 1)] = ["Failed to update subject"];
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
                    'message' => count($updated_subjects) . ' subjects updated successfully',
                    'data' => $updated_subjects
                ]);
            }

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

    } else {
        // Single subject update
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Subject name is required';
        }

        if (empty($data['code'])) {
            $errors[] = 'Subject code is required';
        } elseif ($subject->codeExists($data['code'], $id)) {
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

        // Update subject properties
        $subject->id = $id;
        $subject->department_id = $data['department_id'];
        $subject->name = $data['name'];
        $subject->code = strtoupper($data['code']);
        $subject->credits = $data['credits'];
        $subject->status = $data['status'];
        $subject->has_written_exam = isset($data['has_written_exam']) ? $data['has_written_exam'] : true;

        if ($subject->update()) {
            echo json_encode(['success' => true, 'message' => 'Subject updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update subject']);
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