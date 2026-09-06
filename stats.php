<?php
// public/api/students/stats.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Models\Student;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $student = new Student();
    $rawStats = $student->getRegistrationStats();

    // Process stats into a more usable format
    $stats = [
        'pending_total' => 0,
        'pending_today' => 0,
        'approved_total' => 0,
        'approved_today' => 0,
        'rejected_total' => 0,
        'rejected_today' => 0,
        'week_total' => 0
    ];

    foreach ($rawStats as $stat) {
        $status = $stat['status'];

        if ($status === 'PENDING_APPROVAL') {
            $stats['pending_total'] = $stat['count'];
            $stats['pending_today'] = $stat['today_count'];
            $stats['week_total'] += $stat['week_count'];
        } elseif ($status === 'APPROVED') {
            $stats['approved_total'] = $stat['count'];
            $stats['approved_today'] = $stat['today_count'];
        } elseif ($status === 'REJECTED') {
            $stats['rejected_total'] = $stat['count'];
            $stats['rejected_today'] = $stat['today_count'];
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load statistics: ' . $e->getMessage()
    ]);
}
?>