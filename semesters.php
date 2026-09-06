<?php
require_once __DIR__ . '/../../bootstrap.php';
use App\Auth\Middleware;
use App\Database\Connection;
use App\Models\Semester;

header('Content-Type: application/json');
Middleware::check('DEPT_FULL');

try {
    $db = Connection::pdo();
    $semester = new Semester($db);

    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);

    switch ($requestMethod) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Get single semester
                $semesterData = $semester->readOne($_GET['id']);
                if ($semesterData) {
                    echo json_encode(['success' => true, 'data' => $semesterData]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Semester not found']);
                }
            } elseif (isset($_GET['academic_years'])) {
                // Get unique academic years
                $years = $semester->getAcademicYears();
                echo json_encode(['success' => true, 'data' => $years]);
            } else {
                // Get all semesters with optional filters
                $filters = [];
                if (isset($_GET['department_id'])) {
                    $filters['department_id'] = $_GET['department_id'];
                }
                if (isset($_GET['academic_year'])) {
                    $filters['academic_year'] = $_GET['academic_year'];
                }
                if (isset($_GET['year'])) {
                    $filters['year'] = $_GET['year'];
                }

                $stmt = $semester->readAll($filters);
                $semesters = [];

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $semesters[] = [
                        'id' => $row['id'],
                        'department_id' => $row['department_id'],
                        'department_name' => $row['department_name'],
                        'department_code' => $row['department_code'],
                        'academic_year' => $row['academic_year'],
                        'year' => $row['year'],
                        'semester_number' => $row['semester_number'],
                        'start_date' => $row['start_date'],
                        'end_date' => $row['end_date']
                    ];
                }

                echo json_encode(['success' => true, 'data' => $semesters]);
            }
            break;

        case 'POST':
            // Create new semester
            if (!$input) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
                break;
            }

            // Validate required fields
            $required_fields = ['department_id', 'academic_year', 'year', 'semester_number', 'start_date', 'end_date'];
            foreach ($required_fields as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                    exit;
                }
            }

            // Set semester properties
            $semester->department_id = $input['department_id'];
            $semester->academic_year = $input['academic_year'];
            $semester->year = $input['year'];
            $semester->semester_number = $input['semester_number'];
            $semester->start_date = $input['start_date'];
            $semester->end_date = $input['end_date'];

            // Validate date range
            if (!$semester->isValidDateRange()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'End date must be after start date']);
                break;
            }

            // Check for date overlaps
            if ($semester->hasDateOverlap()) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Semester dates overlap with existing semester']);
                break;
            }

            // Validate semester number
            if ($input['semester_number'] < 1 || $input['semester_number'] > 2) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Semester number must be 1 or 2']);
                break;
            }

            if ($semester->create()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Semester created successfully',
                    'data' => ['id' => $semester->id]
                ]);
            } else {
                if ($semester->exists()) {
                    http_response_code(409);
                    echo json_encode(['success' => false, 'message' => 'Semester already exists for this department, year, and semester number']);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Failed to create semester']);
                }
            }
            break;

        case 'PUT':
            // Update semester
            if (!$input || !isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Semester ID is required']);
                break;
            }

            // Check if semester exists
            if (!$semester->readOne($input['id'])) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Semester not found']);
                break;
            }

            // Validate required fields
            $required_fields = ['department_id', 'academic_year', 'year', 'semester_number', 'start_date', 'end_date'];
            foreach ($required_fields as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                    exit;
                }
            }

            // Set updated properties
            $semester->id = $input['id'];
            $semester->department_id = $input['department_id'];
            $semester->academic_year = $input['academic_year'];
            $semester->year = $input['year'];
            $semester->semester_number = $input['semester_number'];
            $semester->start_date = $input['start_date'];
            $semester->end_date = $input['end_date'];

            // Validate date range
            if (!$semester->isValidDateRange()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'End date must be after start date']);
                break;
            }

            // Check for date overlaps (excluding current semester)
            if ($semester->hasDateOverlap($input['id'])) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Semester dates overlap with existing semester']);
                break;
            }

            // Validate semester number
            if ($input['semester_number'] < 1 || $input['semester_number'] > 2) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Semester number must be 1 or 2']);
                break;
            }

            if ($semester->update()) {
                echo json_encode(['success' => true, 'message' => 'Semester updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update semester or duplicate entry exists']);
            }
            break;

        case 'DELETE':
            // Delete semester
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Semester ID is required']);
                break;
            }

            $semester->id = $_GET['id'];

            // Check if semester exists
            if (!$semester->readOne($_GET['id'])) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Semester not found']);
                break;
            }

            if ($semester->delete()) {
                echo json_encode(['success' => true, 'message' => 'Semester deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete semester']);
            }
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