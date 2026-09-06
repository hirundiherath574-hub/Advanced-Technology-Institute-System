<?php
// public/api/students/pre_enroll.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Models\Student;
use App\Auth\Middleware;
use App\Database\Connection;
use App\Email\Sender;

Middleware::check('STUDENT_WRITE');

header('Content-Type: application/json');

// Read JSON body
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$data = [
    'department_id' => (int)($input['department_id'] ?? 0),
    'nic'           => trim($input['nic'] ?? ''),
    'email'         => trim($input['email'] ?? ''),
    'mode'          => $input['mode'] === 'PART' ? 'PART' : 'FULL',
    'birthday'      => $input['birthday'] ?? null,
    'gender'        => $input['gender'] ?? null,
    'enrollment_year' => trim($input['enrollment_year'] ?? '')
];

// Validation
$errors = [];

if (!$data['department_id']) {
    $errors[] = 'Department is required';
}

if (!$data['nic']) {
    $errors[] = 'NIC is required';
} else {
    // Validate NIC format
    $nicPattern = '/^(?:\d{9}[VXvx]|\d{12})$/';
    if (!preg_match($nicPattern, $data['nic'])) {
        $errors[] = 'Invalid NIC format';
    }
}

if (!$data['email'] || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required';
}

if (!$data['birthday']) {
    $errors[] = 'Birthday is required (parsed from NIC)';
}

// Update the gender validation
if (!$data['gender'] || !in_array($data['gender'], ['M', 'F'])) {
    $errors[] = 'Valid gender is required (parsed from NIC)';
}

if (!$data['enrollment_year']) {
    $errors[] = 'Enrollment year is required';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['error' => implode(', ', $errors)]);
    exit;
}

try {
    // Check if department exists
    $stmt = Connection::pdo()->prepare('SELECT name FROM departments WHERE id = ?');
    $stmt->execute([$data['department_id']]);
    $department = $stmt->fetch();
    if (!$department) {
        http_response_code(422);
        echo json_encode(['error' => 'Invalid department selected']);
        exit;
    }

    // Check if NIC already exists
    $stmt = Connection::pdo()->prepare('SELECT id FROM students WHERE nic = ?');
    $stmt->execute([$data['nic']]);
    if ($stmt->fetch()) {
        http_response_code(422);
        echo json_encode(['error' => 'A student with this NIC already exists']);
        exit;
    }

    // Check if email already exists
    $stmt = Connection::pdo()->prepare('SELECT id FROM students WHERE email = ?');
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        http_response_code(422);
        echo json_encode(['error' => 'A student with this email already exists']);
        exit;
    }

    $student = new Student();
    $id = $student->insertPartial($data);

    // Get the created student to get the token and index number
    $createdStudent = $student->findById($id);

    if (!$createdStudent) {
        throw new \Exception('Failed to retrieve created student');
    }

    // Send email
    $sent = $student->sendPreEnrollmentEmail($id);

    if (!$sent) {
        // Still return success but mention email issue
        echo json_encode([
            'success' => true,
            'message' => 'Student registered successfully but email notification failed. Index Number: ' . $createdStudent['index_no'],
            'index_no' => $createdStudent['index_no']
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Invitation sent to ' . $data['email'],
            'index_no' => $createdStudent['index_no']
        ]);
    }

} catch (Exception $e) {
    error_log('Pre-enrollment error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Registration failed. Please try again.'
    ]);
}