<?php
// api/students/lookup_by_nic.php - Optimized version
require_once __DIR__ . '/../../../bootstrap.php';

use App\Auth\Middleware;
use App\Models\Student;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Add caching headers for better performance
header('Cache-Control: private, max-age=300'); // 5 minute cache
header('Vary: Authorization');

try {
    // Check authentication
    Middleware::check('DEPT_FULL');

    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        throw new Exception('Method not allowed');
    }

    // Get and normalize NIC from query parameter
    $nic = trim($_GET['nic'] ?? '');
    $nic = strtoupper($nic); // Normalize to uppercase

    if (empty($nic)) {
        http_response_code(400);
        throw new Exception('NIC parameter is required');
    }

    // Enhanced NIC validation
    if (!preg_match('/^(\d{9}[VX]|\d{12})$/', $nic)) {
        http_response_code(400);
        throw new Exception('Invalid NIC format');
    }

    // Additional validation for old format NICs
    if (preg_match('/^\d{9}[VX]$/', $nic)) {
        $dayOfYear = (int)substr($nic, 2, 3);
        if ($dayOfYear < 1 || $dayOfYear > 366) {
            http_response_code(400);
            throw new Exception('Invalid NIC: Invalid day of year');
        }
    }

    // Create Student model instance
    $studentModel = new Student();

    // Find student by NIC with optimized query
    $student = $studentModel->findByNICOptimized($nic);

    if ($student) {
        // Remove sensitive fields
        unset($student['token'], $student['resubmit_token']);

        // Add response metadata for debugging
        $response = [
            'success' => true,
            'student' => $student,
            'meta' => [
                'timestamp' => time(),
                'nic_format' => strlen($nic) === 10 ? 'old' : 'new'
            ]
        ];

        echo json_encode($response);
    } else {
        // More specific error message
        echo json_encode([
            'success' => false,
            'message' => 'Student not found or not eligible for semester assignment',
            'details' => 'Student must be: completed registration, active academic status, and approved',
            'meta' => [
                'timestamp' => time(),
                'nic_searched' => $nic
            ]
        ]);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'meta' => [
            'timestamp' => time(),
            'error_type' => get_class($e)
        ]
    ]);
}
?>