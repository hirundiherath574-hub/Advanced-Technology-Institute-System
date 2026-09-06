<?php
// public/api/students/resubmit.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Models\Student;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$token = $_GET['token'] ?? '';
if (empty($token)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Resubmission token is required']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

try {
    $student = new Student();

    // Validate token and get student
    $studentRecord = $student->getByResubmitToken($token);
    if (!$studentRecord) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid or expired resubmission token']);
        exit;
    }

    // Validate required fields
    $requiredFields = [
        'name_with_initials', 'full_name', 'nic', 'gender', 'birthday',
        'contact_no', 'address', 'email', 'dependant_name', 'dependant_prof',
        'dependant_phone', 'dependant_addr', 'ol_year', 'ol_index_no',
        'al_year', 'al_index_no', 'al_stream', 'al_zscore',
        'al_general_test', 'al_general_english'
    ];

    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field {$field} is required"]);
            exit;
        }
    }

    // Validate subjects
    if (!isset($input['ol_subjects']) || !is_array($input['ol_subjects']) || empty($input['ol_subjects'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'O/L subjects are required']);
        exit;
    }

    if (!isset($input['al_subjects']) || !is_array($input['al_subjects']) || empty($input['al_subjects'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'A/L subjects are required']);
        exit;
    }

    // Validate subject data
    foreach ($input['ol_subjects'] as $subject) {
        if (!isset($subject['subject']) || !isset($subject['grade']) ||
            empty(trim($subject['subject'])) || empty(trim($subject['grade']))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'All O/L subjects must have subject name and grade']);
            exit;
        }
    }

    foreach ($input['al_subjects'] as $subject) {
        if (!isset($subject['subject']) || !isset($subject['grade']) ||
            empty(trim($subject['subject'])) || empty(trim($subject['grade']))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'All A/L subjects must have subject name and grade']);
            exit;
        }
    }

    // Validate NIC format
    $nic = trim($input['nic']);
    if (!preg_match('/^[0-9]{9}[vVxX]$|^[0-9]{12}$/', $nic)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid NIC format']);
        exit;
    }

    // Validate email format
    $email = trim($input['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    // Check if email is already taken by another student
    if ($student->isEmailTaken($email, $studentRecord['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email address is already registered by another student']);
        exit;
    }

    // Check if NIC is already taken by another student
    if ($student->isNICTaken($nic, $studentRecord['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'NIC is already registered by another student']);
        exit;
    }

    // Validate numeric fields
    $al_zscore = floatval($input['al_zscore']);
    if ($al_zscore < 0 || $al_zscore > 3) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Z-Score must be between 0 and 3']);
        exit;
    }

    $al_general_test = intval($input['al_general_test']);
    if ($al_general_test < 0 || $al_general_test > 100) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'General Test score must be between 0 and 100']);
        exit;
    }

    // Validate years
    $ol_year = intval($input['ol_year']);
    $al_year = intval($input['al_year']);
    $current_year = date('Y');

    if ($ol_year < 2000 || $ol_year > $current_year) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid O/L year']);
        exit;
    }

    if ($al_year < 2000 || $al_year > $current_year) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid A/L year']);
        exit;
    }

    if ($al_year < $ol_year) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'A/L year cannot be earlier than O/L year']);
        exit;
    }

    // Validate date of birth
    $birthday = $input['birthday'];
    $birthDate = DateTime::createFromFormat('Y-m-d', $birthday);
    if (!$birthDate) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid date of birth format']);
        exit;
    }

    $age = $birthDate->diff(new DateTime())->y;
    if ($age < 16 || $age > 50) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Age must be between 16 and 50 years']);
        exit;
    }

    // Validate grade values
    $validGrades = ['A', 'B', 'C', 'S', 'F'];

    foreach ($input['ol_subjects'] as $subject) {
        if (!in_array($subject['grade'], $validGrades)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid O/L grade: ' . $subject['grade']]);
            exit;
        }
    }

    foreach ($input['al_subjects'] as $subject) {
        if (!in_array($subject['grade'], $validGrades)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid A/L grade: ' . $subject['grade']]);
            exit;
        }
    }

    if (!in_array($input['al_general_english'], $validGrades)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid General English grade']);
        exit;
    }

    // Prepare data for update
    $updateData = [
        'name_with_initials' => trim($input['name_with_initials']),
        'full_name' => trim($input['full_name']),
        'nic' => $nic,
        'gender' => trim($input['gender']),
        'birthday' => $birthday,
        'contact_no' => trim($input['contact_no']),
        'address' => trim($input['address']),
        'email' => $email,
        'dependant_name' => trim($input['dependant_name']),
        'dependant_prof' => trim($input['dependant_prof']),
        'dependant_phone' => trim($input['dependant_phone']),
        'dependant_addr' => trim($input['dependant_addr']),
        'ol_year' => $ol_year,
        'ol_index_no' => trim($input['ol_index_no']),
        'al_year' => $al_year,
        'al_index_no' => trim($input['al_index_no']),
        'al_stream' => trim($input['al_stream']),
        'al_zscore' => $al_zscore,
        'al_general_test' => $al_general_test,
        'al_general_english' => trim($input['al_general_english']),
        'ol_subjects' => $input['ol_subjects'],
        'al_subjects' => $input['al_subjects']
    ];

    // Update student record and reset to pending status
    $success = $student->updateForResubmission($studentRecord['id'], $updateData);

    if ($success) {
        // Send confirmation email
        $emailSent = $student->sendResubmissionConfirmationEmail($studentRecord['id']);

        echo json_encode([
            'success' => true,
            'message' => 'Application resubmitted successfully',
            'data' => [
                'student_id' => $studentRecord['id'],
                'email_sent' => $emailSent
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update application'
        ]);
    }

} catch (Exception $e) {
    error_log('Resubmission error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'System error. Please try again later.'
    ]);
}
?>