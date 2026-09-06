<?php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Models\Department;
use App\Models\Student;
use App\Database\Connection;

if (!isset($_SERVER['HTTP_HX_REQUEST'])) {
    // Push the user back to the dashboard, preserving the target
    header('Location: ../dashboard.php?page=' . basename(__FILE__, '.php'));
    exit;
}

// Get database connection
$database = Connection::pdo();

// Initialize models
$student = new Student();

// Get statistics
try {
    // Total departments
    $deptStmt = $database->query("SELECT COUNT(*) as count FROM departments");
    $totalDepartments = $deptStmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Total students
    $studentStmt = $database->query("SELECT COUNT(*) as count FROM students");
    $totalStudents = $studentStmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Active students
    $activeStmt = $database->query("SELECT COUNT(*) as count FROM students WHERE academic_status = 'Active'");
    $activeStudents = $activeStmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Completed registrations
    $completedStmt = $database->query("SELECT COUNT(*) as count FROM students WHERE completed = 1");
    $completedRegistrations = $completedStmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Pending registrations
    $pendingStmt = $database->query("SELECT COUNT(*) as count FROM students WHERE completed = 0");
    $pendingRegistrations = $pendingStmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get academic status breakdown
    $statusStats = $student->getAcademicStatusStats();

    // Recent registrations (last 7 days)
    $recentStmt = $database->query("SELECT COUNT(*) as count FROM students WHERE registered_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $recentRegistrations = $recentStmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Department-wise student count
    $deptStatsStmt = $database->query("
        SELECT d.name, d.code, COUNT(s.id) as student_count 
        FROM departments d 
        LEFT JOIN students s ON d.id = s.department_id 
        GROUP BY d.id, d.name, d.code 
        ORDER BY student_count DESC 
        LIMIT 5
    ");
    $departmentStats = $deptStatsStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // Fallback values if database query fails
    $totalDepartments = 0;
    $totalStudents = 0;
    $activeStudents = 0;
    $completedRegistrations = 0;
    $pendingRegistrations = 0;
    $recentRegistrations = 0;
    $statusStats = [];
    $departmentStats = [];
}
?>
<div class="content-fade-in">
    <!-- Welcome Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 mb-6">
        <div class="text-center">
            <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Welcome to ATI Admin Panel</h2>
            <p class="text-gray-600 mb-6">System overview and quick access to key management functions.</p>

            <!-- Quick Action Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-8">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 text-left">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-white">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Departments</h3>
                            <p class="text-gray-600 text-sm">Manage academic departments</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 text-left">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-white">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Students</h3>
                            <p class="text-gray-600 text-sm">Manage student records</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-blue-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Departments</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($totalDepartments); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-green-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Students</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($totalStudents); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-emerald-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Students</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($activeStudents); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-orange-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Recent (7 days)</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($recentRegistrations); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Status Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Registration Status</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">Completed</span>
                    </div>
                    <span class="text-sm font-bold text-gray-900"><?php echo number_format($completedRegistrations); ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">Pending</span>
                    </div>
                    <span class="text-sm font-bold text-gray-900"><?php echo number_format($pendingRegistrations); ?></span>
                </div>
                <?php if ($totalStudents > 0): ?>
                    <div class="pt-2 border-t border-gray-200">
                        <div class="text-xs text-gray-500">
                            Completion Rate: <?php echo round(($completedRegistrations / $totalStudents) * 100, 1); ?>%
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Academic Status Breakdown</h3>
            <div class="space-y-3">
                <?php if (empty($statusStats)): ?>
                    <p class="text-sm text-gray-500">No student data available</p>
                <?php else: ?>
                    <?php foreach ($statusStats as $status): ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 <?php
                                echo match($status['academic_status']) {
                                    'Active' => 'bg-green-500',
                                    'Suspended' => 'bg-red-500',
                                    'Graduated' => 'bg-blue-500',
                                    'Dropped' => 'bg-gray-500',
                                    default => 'bg-purple-500'
                                };
                                ?> rounded-full mr-3"></div>
                                <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($status['academic_status']); ?></span>
                            </div>
                            <span class="text-sm font-bold text-gray-900"><?php echo number_format($status['count']); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Department Statistics -->
    <?php if (!empty($departmentStats)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Departments by Student Count</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-2 px-1 text-sm font-medium text-gray-700">Department</th>
                        <th class="text-left py-2 px-1 text-sm font-medium text-gray-700">Code</th>
                        <th class="text-right py-2 px-1 text-sm font-medium text-gray-700">Students</th>
                        <th class="text-right py-2 px-1 text-sm font-medium text-gray-700">Percentage</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    <?php foreach ($departmentStats as $dept): ?>
                        <tr>
                            <td class="py-3 px-1 text-sm text-gray-900"><?php echo htmlspecialchars($dept['name']); ?></td>
                            <td class="py-3 px-1 text-sm font-mono text-gray-600"><?php echo htmlspecialchars($dept['code']); ?></td>
                            <td class="py-3 px-1 text-sm font-semibold text-gray-900 text-right"><?php echo number_format($dept['student_count']); ?></td>
                            <td class="py-3 px-1 text-sm text-gray-600 text-right">
                                <?php echo $totalStudents > 0 ? round(($dept['student_count'] / $totalStudents) * 100, 1) : 0; ?>%
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>