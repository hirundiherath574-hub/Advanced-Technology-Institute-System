<?php
require_once __DIR__ . '/../../bootstrap.php';
use App\Database\Connection;
use Delight\Auth\Auth;

$auth = new Auth(Connection::pdo());
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get user information
$userId = $auth->getUserId();
$userEmail = $auth->getEmail();
$username = $auth->getUsername();

// Get user display name (fallback to email if username not set)
$displayName = $username ?: explode('@', $userEmail)[0];

// Get user role/status - you can customize this based on your user table structure
$userRole = 'Administrator'; // Default role, you can query from database

$allowed = ['departments', 'students', 'admin_users', 'role_config', 'semester_assignment','semester_students_overview'];
$page    = $_GET['page'] ?? '';

function url($path) {
    return rtrim($_ENV['APP_URL'], '/') . $path;
}
?>
<!DOCTYPE  html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard – ATI System</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script src="../js/config.js.php"></script>

    <!-- Alpine & HTMX -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.6/dist/htmx.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>

    <!-- Styles & fonts -->
    <link rel="stylesheet" href="<?= url('/assets/css/dist/styles.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body x-cloak class="bg-gray-50 font-sans antialiased" x-data="{ sidebarCollapsed: $persist(false) }">
<!-- Sidebar -->
<aside id="sidebar"
       class="fixed left-0 top-0 h-full bg-white border-r border-gray-200 shadow-sm z-40 transition-all duration-300"
       :class="{ 'w-64': !sidebarCollapsed, 'w-16': sidebarCollapsed, 'sidebar-collapsed': sidebarCollapsed }">

    <!-- Logo/Brand -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
        <div class="flex items-center space-x-3">
            <div class="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m-2 0h2M7 7h10M7 11h4"></path>
                </svg>
            </div>
            <div class="sidebar-text sidebar-logo-text">
                <h1 class="text-lg font-semibold text-gray-900">ATI Admin</h1>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="px-3 py-4 space-y-1">
        <!-- Overview -->
        <a href="<?= url('/admin/dashboard.php?page=overview') ?>"
           hx-get="<?= url('/admin/pages/overview.php') ?>"
           hx-target="#main-panel"
           hx-push-url="true"
           hx-swap="innerHTML"
           class="nav-link flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors duration-200 cursor-pointer relative group"
           data-page="overview">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
            </svg>
            <span class="sidebar-text ml-3 font-medium">Overview</span>
            <div class="tooltip">Overview</div>
        </a>

        <!-- Departments -->
        <a href="<?= url('/admin/dashboard.php?page=departments') ?>"
           hx-get="<?= url('/admin/pages/departments.php') ?>"
           hx-target="#main-panel"
           hx-push-url="true"
           hx-swap="innerHTML"
           class="nav-link flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors duration-200 cursor-pointer relative group"
           data-page="departments">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
            </svg>
            <span class="sidebar-text ml-3 font-medium">Departments</span>
            <div class="tooltip">Departments</div>
        </a>

        <!-- Students -->
        <a href="<?= url('/admin/dashboard.php?page=students') ?>"
           hx-get="<?= url('/admin/pages/students.php') ?>"
           hx-target="#main-panel"
           hx-push-url="true"
           hx-swap="innerHTML"
           class="nav-link flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors duration-200 cursor-pointer relative group"
           data-page="students">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
            <span class="sidebar-text ml-3 font-medium">Students</span>
            <div class="tooltip">Students</div>
        </a>

        <!-- semester_assignment -->
        <a href="<?= url('/admin/dashboard.php?page=semester_assignment') ?>"
           hx-get="<?= url('/admin/pages/semester_assignment.php') ?>"
           hx-target="#main-panel"
           hx-push-url="true"
           hx-swap="innerHTML"
           class="nav-link flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors duration-200 cursor-pointer relative group"
           data-page="semester_assignment">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 2.994v2.25m10.5-2.25v2.25m-14.252 13.5V7.491a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.251m-18 0a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5m-6.75-6h2.25m-9 2.25h4.5m.002-2.25h.005v.006H12v-.006Zm-.001 4.5h.006v.006h-.006v-.005Zm-2.25.001h.005v.006H9.75v-.006Zm-2.25 0h.005v.005h-.006v-.005Zm6.75-2.247h.005v.005h-.005v-.005Zm0 2.247h.006v.006h-.006v-.006Zm2.25-2.248h.006V15H16.5v-.005Z" />
            </svg>
            <span class="sidebar-text ml-3 font-medium">Semester Assignment</span>
            <div class="tooltip">Semester Assignment</div>
        </a>

        <!-- semester_students_overview -->
        <a href="<?= url('/admin/dashboard.php?page=semester_students_overview') ?>"
           hx-get="<?= url('/admin/pages/semester_students_overview.php') ?>"
           hx-target="#main-panel"
           hx-push-url="true"
           hx-swap="innerHTML"
           class="nav-link flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors duration-200 cursor-pointer relative group"
           data-page="semester_students_overview">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776" />
            </svg>
            <span class="sidebar-text ml-3 font-medium">Semester Overview</span>
            <div class="tooltip">Semester Overview</div>
        </a>

        <a href="<?= url('/admin/dashboard.php?page=admin_users') ?>"
           hx-get="<?= url('/admin/pages/admin_users.php') ?>"
           hx-target="#main-panel"
           hx-push-url="true"
           hx-swap="innerHTML"
           class="nav-link flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors duration-200 cursor-pointer relative group"
           data-page="admin_users">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
            </svg>
            <span class="sidebar-text ml-3 font-medium">Admin Users</span>
            <div class="tooltip">Admin Users</div>
        </a>

        <a href="<?= url('/admin/dashboard.php?page=role_config') ?>"
           hx-get="<?= url('/admin/pages/role_config.php') ?>"
           hx-target="#main-panel"
           hx-push-url="true"
           hx-swap="innerHTML"
           class="nav-link flex items-center px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors duration-200 cursor-pointer relative group"
           data-page="role_config">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
            </svg>
            <span class="sidebar-text ml-3 font-medium">Role Config</span>
            <div class="tooltip">Role Config</div>
        </a>

        <!-- Divider -->
        <div class="py-2">
            <div class="border-t border-gray-200"></div>
        </div>

        <!-- Logout -->
        <a href="<?= url('/admin/logout.php') ?>"
           class="nav-link flex items-center px-3 py-2 rounded-lg text-red-600 hover:bg-red-50 before:bg-red-500 transition-colors duration-200 cursor-pointer relative group">
            <svg class="flex-shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
            <span class="sidebar-text ml-3 font-medium">Logout</span>
            <div class="tooltip">Logout</div>
        </a>
    </nav>

    <!-- User Profile Section -->
    <div class="absolute bottom-0 left-0 right-0 p-3 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center space-x-3">
            <div class="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                    <span class="text-xs font-semibold text-white">
                        <?= strtoupper(substr($displayName, 0, 2)) ?>
                    </span>
            </div>
            <div class="sidebar-text flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate" title="<?= htmlspecialchars($displayName) ?>">
                    <?= htmlspecialchars($displayName) ?>
                </p>
                <p class="text-xs text-gray-500 truncate" title="<?= htmlspecialchars($userRole) ?>">
                    <?= htmlspecialchars($userRole) ?>
                </p>
            </div>
            <div class="sidebar-text">
                <div class="w-2 h-2 bg-green-400 rounded-full" title="Online"></div>
            </div>
        </div>

        <!-- Expanded user info when sidebar is open -->
        <div class="sidebar-text mt-2 pt-2 border-t border-gray-200 text-xs text-gray-500">
            <div class="flex justify-between items-center">
                <span>ID: <?= htmlspecialchars($userId) ?></span>
                <span class="text-green-600">● Online</span>
            </div>
            <?php if (isset($lastLogin) && $lastLogin): ?>
                <div class="mt-1">
                    Last login: <?= date('M j, H:i', strtotime($lastLogin)) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</aside>

<!-- Main Content Area -->
<div x-cloak class="flex-1" :class="{ 'ml-16': sidebarCollapsed, 'ml-64': !sidebarCollapsed }" style="transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);">

    <!-- Top Header -->
    <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between sticky top-0 z-30 h-20">
        <div class="flex items-center space-x-4">
            <!-- Sidebar Toggle -->
            <button @click="sidebarCollapsed = !sidebarCollapsed"
                    class="text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 rounded-lg p-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- Page Title -->
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Dashboard</h1>
                <p class="text-sm text-gray-500">Manage your system efficiently</p>
            </div>
        </div>

        <!-- Header Actions -->
        <div class="flex items-center space-x-3">
            <!-- User info in header -->
            <div class="hidden sm:block text-right">
                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($displayName) ?></p>
                <p class="text-xs text-gray-500"><?= htmlspecialchars($userEmail) ?></p>
            </div>

            <!-- User avatar -->
            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                    <span class="text-xs font-semibold text-white">
                        <?= strtoupper(substr($displayName, 0, 2)) ?>
                    </span>
            </div>

            <!-- Notifications -->
            <button class="relative text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 rounded-lg p-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM11 17H6l5 5v-5zM15 7a6 6 0 11-12 0 6 6 0 0112 0z"></path>
                </svg>
                <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-400 ring-2 ring-white"></span>
            </button>

            <!-- Settings -->
            <button class="text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 rounded-lg p-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </button>
        </div>
    </header>

    <!-- Main Content Panel -->
    <main id="main-panel" class="p-6 min-h-[calc(100vh-80px)] bg-gray-50" >

    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('page') || 'overview';
        const allowedPages = ['overview', 'departments', 'students', 'admin_users', 'role_config','semester_assignment','semester_students_overview'];

        //console.log('Current page:', page); // Debug log

        // Function to update page title
        function updatePageTitle(pageTitle) {
            const headerTitle = document.querySelector('header h1');
            const headerSubtitle = document.querySelector('header p');

            if (headerTitle) {
                headerTitle.textContent = pageTitle;
            }
            if (headerSubtitle) {
                headerSubtitle.textContent = `Manage ${pageTitle.toLowerCase()}`;
            }
        }

        // Function to set active navigation
        function setActiveNav(targetPage) {
            // Remove active class from all links
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });

            // Add active class to current page
            const activeLink = document.querySelector(`[data-page="${targetPage}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
                const pageTitle = activeLink.querySelector('span').textContent.trim();
                updatePageTitle(pageTitle);
                //console.log('Active nav set to:', targetPage); // Debug log
            } else {
                //console.log('No link found for page:', targetPage); // Debug log
            }
        }

        // Handle navigation clicks
        document.querySelectorAll('[data-page]').forEach(link => {
            link.addEventListener('click', function(e) {
                const targetPage = this.getAttribute('data-page');
                //console.log('Navigation clicked:', targetPage); // Debug log

                // Set active state immediately
                setActiveNav(targetPage);

                // Let HTMX handle the request naturally
                // Don't prevent default or do manual HTMX calls
            });
        });

        // Handle HTMX events
        document.addEventListener('htmx:afterRequest', function(event) {
            //console.log('HTMX request completed:', event.detail); // Debug log

            if (event.detail.failed) {
                //console.error('HTMX request failed:', event.detail);
                // Show error message to user
                const mainPanel = document.getElementById('main-panel');
                if (mainPanel) {
                    mainPanel.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 m-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Error loading page</h3>
                                <p class="text-sm text-red-700 mt-1">The requested page could not be loaded. Please try again.</p>
                            </div>
                        </div>
                    </div>
                `;
                }
            }
        });

        // Load initial page
        if (allowedPages.includes(page)) {
            setActiveNav(page);

            // Load the page content
            const targetLink = document.querySelector(`[data-page="${page}"]`);
            if (targetLink) {
                const pageUrl = targetLink.getAttribute('hx-get');
                //console.log('Loading initial page:', pageUrl); // Debug log

                if (typeof htmx !== 'undefined' && pageUrl) {
                    htmx.ajax('GET', pageUrl, {
                        target: '#main-panel',
                        swap: 'innerHTML'
                    }).then(function() {
                        //console.log('Initial page loaded successfully');
                    }).catch(function(error) {
                        //console.error('Error loading initial page:', error);
                    });
                }
            }
        } else {
            console.log('Invalid page, redirecting to overview');
            // Redirect to overview if invalid page
            window.location.href = window.location.pathname + '?page=overview';
        }

        // Handle browser back/forward
        window.addEventListener('popstate', function(event) {
            const urlParams = new URLSearchParams(window.location.search);
            const page = urlParams.get('page') || 'overview';

            //console.log('Popstate event, loading page:', page); // Debug log

            if (allowedPages.includes(page)) {
                setActiveNav(page);
                const targetLink = document.querySelector(`[data-page="${page}"]`);
                if (targetLink && typeof htmx !== 'undefined') {
                    const pageUrl = targetLink.getAttribute('hx-get');
                    if (pageUrl) {
                        htmx.ajax('GET', pageUrl, {
                            target: '#main-panel',
                            swap: 'innerHTML'
                        });
                    }
                }
            }
        });

        /* Add HTMX debugging
        document.addEventListener('htmx:configRequest', function(event) {
            console.log('HTMX request config:', event.detail);
        });

        document.addEventListener('htmx:beforeRequest', function(event) {
            console.log('HTMX before request:', event.detail.xhr.responseURL || event.detail.pathInfo.requestPath);
        });*/
    });

    document.addEventListener('htmx:afterSwap', function (event) {
        if (event.target.id === 'main-panel') {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

</script>
</body>
</html>