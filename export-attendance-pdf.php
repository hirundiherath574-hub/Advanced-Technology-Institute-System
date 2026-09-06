<?php
// api/semesters/export-attendance-pdf.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Auth\Middleware;
use App\Models\SemesterManagement;
use TCPDF;

// Clean any previous output
if (ob_get_level()) {
    ob_end_clean();
}

Middleware::check('DEPT_FULL');

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        // Fallback to GET parameters for quick export
        $input = $_GET;
    }

    if (!isset($input['semester_id'])) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Semester ID is required']);
        exit;
    }

    $semesterManagement = new SemesterManagement();

    // Get semester details
    $semester = getSemesterDetails($input['semester_id']);
    if (!$semester) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Semester not found']);
        exit;
    }

    // Get enrolled students ordered by student number (index number)
    $students = $semesterManagement->getStudentsBySemester($input['semester_id'], true);

    // Sort students by student number (index number)
    usort($students, function($a, $b) {
        return strcmp($a['student_number'], $b['student_number']);
    });

    if (empty($students)) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No students found for this semester']);
        exit;
    }

    // Extract form data with defaults
    $subjectName = $input['subject_name'] ?? '';
    $attendanceDate = $input['date'] ?? date('Y-m-d');

    // Generate PDF
    $pdf = generateAttendanceSigningSheet($semester, $students, $subjectName, $attendanceDate);

    // Set filename
    $filename = sprintf(
        'Attendance_SigningSheet_%s_Year%s_Sem%s_%s.pdf',
        $semester['academic_year'],
        $semester['year'],
        $semester['semester_number'],
        $attendanceDate
    );

    // Set proper headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    // Output PDF
    echo $pdf->Output('', 'S'); // 'S' returns PDF as string

} catch (Exception $e) {
    // Clean any previous output
    if (ob_get_level()) {
        ob_end_clean();
    }

    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

/**
 * Get semester details
 */
function getSemesterDetails($semester_id) {
    $conn = \App\Database\Connection::pdo();

    $query = "SELECT s.*, d.name as department_name, d.code as department_code
              FROM semesters s
              JOIN departments d ON s.department_id = d.id
              WHERE s.id = :semester_id";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':semester_id', $semester_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Generate attendance signing sheet matching exact format
 */
function generateAttendanceSigningSheet($semester, $students, $subjectName, $attendanceDate) {
    // Create new PDF document
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('ATI Student Management System');
    $pdf->SetAuthor('ATI Administration');
    $pdf->SetTitle('Attendance Signing Sheet - ' . $semester['academic_year']);
    $pdf->SetSubject('Student Attendance');

    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Set margins - reduced margins
    $pdf->SetMargins(8, 15, 8);
    $pdf->SetAutoPageBreak(TRUE, 12);

    // Set default font to Times New Roman
    $pdf->SetFont('times', '', 10);

    // Add page
    $pdf->AddPage();

    // Generate header
    generateExactHeader($pdf, $semester, $subjectName);

    // Generate student signing table
    generateExactTable($pdf, $students);

    // Add footer information
    generateExactFooter($pdf);

    return $pdf;
}

/**
 * Generate exact header format
 */
function generateExactHeader($pdf, $semester, $subjectName) {
    // Institution name - not bold, slightly increased line height
    $pdf->SetFont('times', '', 12);
    $pdf->Cell(140, 9, 'Advanced Technological Institute – Kurunegala', 0, 0, 'L');

    // Top right box - Registered/Year/Theory (original heights) - NOT BOLD
    $pdf->SetFont('times', '', 11);
    $pdf->Cell(50, 6, 'Registered', 1, 1, 'C');

    // Program name - dynamic department name with prefix
    $pdf->SetFont('times', 'B', 11);
    $programName = 'Higher National Diploma in ' . $semester['department_name'] . ' (Full Time)';
    $pdf->Cell(140, 7, $programName, 0, 0, 'L');

    // Top right box - Year (NOT BOLD)
    $pdf->SetFont('times', '', 11);
    $pdf->Cell(50, 6, $semester['academic_year'], 1, 1, 'C');

    // Year and semester - slightly increased line height
    $pdf->SetFont('times', 'B', 11);
    $yearText = '';
    if ($semester['year'] == 1) {
        $yearText = 'FIRST YEAR';
    } elseif ($semester['year'] == 2) {
        $yearText = 'SECOND YEAR';
    } elseif ($semester['year'] == 3) {
        $yearText = 'THIRD YEAR';
    }

    $semesterText = '';
    if ($semester['semester_number'] == 1) {
        $semesterText = 'FIRST SEMESTER';
    } elseif ($semester['semester_number'] == 2) {
        $semesterText = 'SECOND SEMESTER';
    }

    $pdf->Cell(140, 7, $yearText . ' ' . $semesterText . ' – ' . $semester['academic_year'], 0, 0, 'L');

    // Top right box - Theory (NOT BOLD)
    $pdf->SetFont('times', '', 11);
    $pdf->Cell(50, 6, 'Theory', 1, 1, 'C');

    $pdf->Ln(5);

    // Subject line with dots - bold and same size as top heading, slightly increased line height
    $pdf->SetFont('times', 'B', 12);
    $subjectLine = 'Subject Code Name: - ' . ($subjectName ? $subjectName : str_repeat('.', 80));
    $pdf->Cell(0, 9, $subjectLine, 0, 1, 'L');

    $pdf->Ln(5);
}

/**
 * Generate exact table format with multiple columns
 */
function generateExactTable($pdf, $students) {
    // Calculate column widths for the table - further increased registration number width
    $snoWidth = 12;
    $regWidth = 42; // Increased from 38 to 42
    $nameWidth = 42;
    $signatureColumns = 6; // Number of signature columns
    $signatureWidth = (194 - $snoWidth - $regWidth - $nameWidth) / $signatureColumns; // Width per signature column

    // Table header - increased height
    $pdf->SetFont('times', 'B', 10); // Increased from 9 to 10
    $headerHeight = 12; // Increased from 8 to 12

    // First row - main headers
    $pdf->Cell($snoWidth, $headerHeight, 'S.No', 1, 0, 'C');
    $pdf->Cell($regWidth, $headerHeight, 'Registration Number', 1, 0, 'C');
    $pdf->Cell($nameWidth, $headerHeight, 'Name with Initial', 1, 0, 'C');

    // Multiple signature columns (empty headers for dates)
    for ($i = 0; $i < $signatureColumns; $i++) {
        $pdf->Cell($signatureWidth, $headerHeight, '', 1, 0, 'C');
    }
    $pdf->Ln();

    // Table content
    $pdf->SetFont('times', '', 10); // Increased from 9 to 10

    $rowHeight = 6;
    $studentsPerPage = 27; // Adjusted for slightly increased header line heights
    $currentStudent = 0;

    // Add students from database - only actual students, no extra empty rows
    foreach ($students as $index => $student) {
        // Check if we need a new page
        if ($currentStudent > 0 && $currentStudent % $studentsPerPage === 0) {
            $pdf->AddPage();

            // Re-add table header on new page
            $pdf->SetFont('times', 'B', 10);
            $pdf->Cell($snoWidth, $headerHeight, 'S.No', 1, 0, 'C');
            $pdf->Cell($regWidth, $headerHeight, 'Registration Number', 1, 0, 'C');
            $pdf->Cell($nameWidth, $headerHeight, 'Name with Initial', 1, 0, 'C');
            for ($i = 0; $i < $signatureColumns; $i++) {
                $pdf->Cell($signatureWidth, $headerHeight, '', 1, 0, 'C');
            }
            $pdf->Ln();
            $pdf->SetFont('times', '', 10);
        }

        // Determine title based on gender
        $title = '';
        if (isset($student['gender'])) {
            if ($student['gender'] === 'M') {
                $title = 'Mr. ';
            } elseif ($student['gender'] === 'F') {
                $title = 'Ms. ';
            }
        }

        // Student row
        $pdf->Cell($snoWidth, $rowHeight, ($index + 1), 1, 0, 'C');
        $pdf->Cell($regWidth, $rowHeight, $student['student_number'], 1, 0, 'C');
        $pdf->Cell($nameWidth, $rowHeight, $title . $student['name_with_initials'], 1, 0, 'L');

        // Empty signature columns
        for ($i = 0; $i < $signatureColumns; $i++) {
            $pdf->Cell($signatureWidth, $rowHeight, '', 1, 0, 'C');
        }
        $pdf->Ln();

        $currentStudent++;
    }
}

/**
 * Generate exact footer format
 */
function generateExactFooter($pdf) {
    $pdf->Ln(3);

    // Disclaimer text - exact format
    $pdf->SetFont('times', '', 8);
    $pdf->MultiCell(0, 4, 'No any student is allowed to sign below this and attendance will also not be considered for semester examination.', 0, 'L');
    $pdf->MultiCell(0, 4, 'If you have not yet renewed the semester registration, please complete it by following the instructions given in the Student Handbook.', 0, 'L');

    $pdf->Ln(2);

    $pdf->SetFont('times', 'B', 8);
    $pdf->MultiCell(0, 4, 'Important: 80% attendance is required to appear in the semester examination.', 0, 'L');

    $pdf->Ln(8);

    // Lecturer signature line
    $pdf->SetFont('times', '', 9);
    $pdf->Cell(0, 5, 'Name / Signature of the Lecturer', 0, 1, 'L');
}

?>