<?php
// public/api/subjects/delete.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Auth\Middleware;
use App\Database\Connection;
use App\Models\Subject;

header('Content-Type: application/json');
Middleware::check('DEPT_FULL');

try {
    $db = Connection::pdo();
    $subject = new Subject($db);

    // Check if it's bulk delete from POST body
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (isset($data['bulk']) && $data['bulk'] === true && isset($data['ids'])) {
            // Bulk delete
            $ids = $data['ids'];

            if (empty($ids) || !is_array($ids)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Subject IDs array is required for bulk delete']);
                exit;
            }

            $deleted_count = 0;
            $errors = [];
            $db->beginTransaction();

            try {
                foreach ($ids as $id) {
                    $subjectId = intval($id);

                    // Check if subject exists first
                    if (!$subject->readOne($subjectId)) {
                        $errors[] = "Subject with ID {$subjectId} not found";
                        continue;
                    }

                    $subject->id = $subjectId;
                    if ($subject->delete()) {
                        $deleted_count++;
                    } else {
                        $errors[] = "Failed to delete subject with ID {$subjectId}";
                    }
                }

                if (!empty($errors)) {
                    $db->rollBack();
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Some deletions failed',
                        'errors' => $errors,
                        'deleted_count' => 0
                    ]);
                } else {
                    $db->commit();
                    echo json_encode([
                        'success' => true,
                        'message' => "{$deleted_count} subjects deleted successfully",
                        'deleted_count' => $deleted_count
                    ]);
                }

            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }

        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid bulk delete request']);
        }

    } else {
        // Single delete via GET/DELETE method
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Subject ID is required']);
            exit;
        }

        $id = intval($_GET['id']);

        // Check if subject exists first
        if (!$subject->readOne($id)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Subject not found']);
            exit;
        }

        $subject->id = $id;

        if ($subject->delete()) {
            echo json_encode(['success' => true, 'message' => 'Subject deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete subject']);
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