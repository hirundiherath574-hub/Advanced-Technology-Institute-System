<?php
// public/admin/pages/semester_students_overview.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Auth\Middleware;

// Check if user has permission to manage semesters
Middleware::check('DEPT_FULL');

if (!isset($_SERVER['HTTP_HX_REQUEST'])) {
    // Push the user back to the dashboard, preserving the target
    header('Location: ../dashboard.php?page=' . basename(__FILE__, '.php'));
    exit;
}
?>

<div x-data="semesterStudentsOverview()" x-init="loadInitialData()" class="min-h-screen py-6 content-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Semester Students Overview</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">View enrolled students and semester statistics</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="exportStudentData" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export Data
                    </button>
                    <button @click="refreshData" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <div x-show="alert.show" x-cloak class="mb-6">
            <div :class="alert.type === 'success' ? 'bg-green-50 border-green-200' : alert.type === 'info' ? 'bg-blue-50 border-blue-200' : 'bg-red-50 border-red-200'" class="rounded-md border p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg x-show="alert.type === 'success'" class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <svg x-show="alert.type === 'info'" class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <svg x-show="alert.type === 'error'" class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p :class="alert.type === 'success' ? 'text-green-800' : alert.type === 'info' ? 'text-blue-800' : 'text-red-800'" class="text-sm font-medium" x-text="alert.message"></p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button @click="alert.show = false" :class="alert.type === 'success' ? 'text-green-400 hover:text-green-600' : alert.type === 'info' ? 'text-blue-400 hover:text-blue-600' : 'text-red-400 hover:text-red-600'" class="inline-flex rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Filter -->
        <div class="bg-white shadow rounded-lg mb-6 p-4">
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Department:</label>
                <select x-model="selectedDepartment" @change="loadSemesters()" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Departments</option>
                    <template x-for="dept in departments" :key="dept.id">
                        <option :value="dept.id" x-text="`${dept.name} (${dept.code})`"></option>
                    </template>
                </select>
                <div class="flex-1"></div>
                <div class="text-sm text-gray-500">
                    <span x-text="semesters.length"></span> semesters available
                </div>
            </div>
        </div>

        <!-- Semester Selection Cards -->
        <div class="mb-6">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Select Semester</h4>
            <div x-show="loading" x-cloak class="flex items-center justify-center py-12">
                <div class="flex items-center space-x-2">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
                    <span class="text-gray-500">Loading semesters...</span>
                </div>
            </div>

            <div x-show="!loading" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <template x-for="semester in semesters" :key="semester.id">
                    <div @click="selectSemester(semester)"
                         :class="selectedSemester?.id === semester.id ? 'selected bg-indigo-50 border-indigo-200' : 'bg-white border-gray-200 hover:border-indigo-300'"
                         class="semester-card border-2 rounded-lg p-4 cursor-pointer transition-all duration-200">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h5 class="font-medium text-gray-900" x-text="`${semester.academic_year} - Year ${semester.year}`"></h5>
                                <p class="text-sm text-gray-600" x-text="`Semester ${semester.semester_number}`"></p>
                                <p class="text-xs text-gray-500 mt-1" x-text="semester.department_name"></p>
                                <div class="flex items-center mt-2 space-x-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3 mr-1">
                                          <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z" clip-rule="evenodd" />
                                          <path d="M5.082 14.254a8.287 8.287 0 0 0-1.308 5.135 9.687 9.687 0 0 1-1.764-.44l-.115-.04a.563.563 0 0 1-.373-.487l-.01-.121a3.75 3.75 0 0 1 3.57-4.047ZM20.226 19.389a8.287 8.287 0 0 0-1.308-5.135 3.75 3.75 0 0 1 3.57 4.047l-.01.121a.563.563 0 0 1-.373.486l-.115.04c-.567.2-1.156.349-1.764.441Z" />
                                        </svg>
                                        <span class="mr-1" x-text="semester.student_count || 0"></span> students
                                    </span>
                                    <span :class="getSemesterStatusClass(semester)" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                                        <span x-text="getSemesterStatus(semester)"></span>
                                    </span>
                                </div>
                                <div class="mt-2 text-xs text-gray-500">
                                    <span x-text="formatDateRange(semester.start_date, semester.end_date)"></span>
                                </div>
                            </div>
                            <div class="ml-2">
                                <div :class="selectedSemester?.id === semester.id ? 'bg-indigo-600 border-indigo-600' : 'bg-white border-gray-300'" class="w-4 h-4 rounded-full border-2 flex items-center justify-center">
                                    <div x-show="selectedSemester?.id === semester.id" class="w-2 h-2 bg-white rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Empty State -->
                <div x-show="semesters.length === 0 && !loading" x-cloak class="col-span-full">
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto mb-4 text-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 2.994v2.25m10.5-2.25v2.25m-14.252 13.5V7.491a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.251m-18 0a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5m-6.75-6h2.25m-9 2.25h4.5m.002-2.25h.005v.006H12v-.006Zm-.001 4.5h.006v.006h-.006v-.005Zm-2.25.001h.005v.006H9.75v-.006Zm-2.25 0h.005v.005h-.006v-.005Zm6.75-2.247h.005v.005h-.005v-.005Zm0 2.247h.006v.006h-.006v-.006Zm2.25-2.248h.006V15H16.5v-.005Z" />
                        </svg>
                        <p class="text-lg font-medium mb-2">No semesters found</p>
                        <p class="text-sm text-gray-500">Try selecting a different department or contact your administrator</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards (shown when semester is selected) -->
        <div x-show="selectedSemester" x-cloak class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
            <div class="stats-card text-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-white/80 truncate">Total Students</dt>
                                <dd class="text-lg font-medium text-white" x-text="stats.total_students"></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stats-card-2 text-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-white/80 truncate">Active Students</dt>
                                <dd class="text-lg font-medium text-white" x-text="stats.current_students"></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stats-card-3 text-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-white/80 truncate">Activity Rate</dt>
                                <dd class="text-lg font-medium text-white" x-text="getActivityRate() + '%'"></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stats-card-4 text-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-white/80 truncate">Days Remaining</dt>
                                <dd class="text-lg font-medium text-white" x-text="getDaysRemaining()"></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student List Table -->
        <div x-show="selectedSemester" x-cloak class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Students in Semester</h3>
                        <p class="mt-1 text-sm text-gray-500" x-text="`${selectedSemester?.academic_year} - Year ${selectedSemester?.year}, Semester ${selectedSemester?.semester_number}`"></p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <!-- Search -->
                        <div class="relative">
                            <input x-model="searchTerm" @input="filterStudents" type="text" placeholder="Search students..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <select x-model="statusFilter" @change="filterStudents" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Status</option>
                            <option value="active">Active Only</option>
                            <option value="inactive">Inactive Only</option>
                        </select>

                        <button @click="loadStudentList" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': studentsLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div x-show="studentsLoading" x-cloak class="flex items-center justify-center py-12">
                <div class="flex items-center space-x-2">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
                    <span class="text-gray-500">Loading students...</span>
                </div>
            </div>

            <!-- Table -->
            <div x-show="!studentsLoading" x-cloak class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrolled Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="student in filteredStudents" :key="student.id">
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <!-- Replace the initials avatar with image -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <!-- Profile Image -->
                                    <div class="flex-shrink-0">
                                        <div x-show="!student?.photo" class="w-12 h-12 rounded-full bg-gray-400 bg-opacity-20 flex items-center justify-center">
                                            <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                            </svg>
                                        </div>
                                        <img x-show="student?.photo"
                                             class="w-12 h-12 rounded-full object-cover border-4 border-white border-opacity-30"
                                             :src="`${window.APP_CONFIG.API_BASE_URL}/students/photo.php?index=${encodeURIComponent(student?.id ?? '')}&w=256&h=256&fit=crop`"
                                             :alt="student?.name_with_initials">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900" x-text="student.name_with_initials"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900" x-text="student.student_number"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900" x-text="student.email"></div>
                                <div class="text-sm text-gray-500" x-text="student.phone"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="student.is_current ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                    <span x-text="student.is_current ? 'Active' : 'Inactive'"></span>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(student.assigned_at)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <button @click="viewStudentDetails(student)" class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150" title="View Details">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button @click="viewStudentHistory(student)" class="text-blue-600 hover:text-blue-900 transition-colors duration-150" title="View History">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty State -->
                    <tr x-show="filteredStudents.length === 0 && !studentsLoading" x-cloak>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="text-lg font-medium mb-2" x-text="searchTerm ? 'No matching students found' : 'No students in this semester'"></p>
                                <p class="text-sm" x-text="searchTerm ? 'Try adjusting your search terms' : 'Students will appear here when enrolled in this semester'"></p>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Assignment History Section -->
        <div x-show="selectedSemester && showHistory" x-cloak class="mt-6 bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Assignment History</h3>
                        <p class="mt-1 text-sm text-gray-500">Recent enrollment activities for this semester</p>
                    </div>
                    <button @click="showHistory = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <div class="flow-root">
                    <ul class="-mb-8">
                        <template x-for="(activity, index) in assignmentHistory" :key="activity.id">
                            <li>
                                <div class="relative pb-8" :class="{ 'pb-0': index === assignmentHistory.length - 1 }">
                                    <span x-show="index !== assignmentHistory.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span :class="getActivityIconClass(activity.action)" class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white">
                                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path x-show="activity.action === 'bulk_assign'" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    <path x-show="activity.action === 'individual_assign'" d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6z" />
                                                    <path x-show="activity.action === 'remove'" fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500" x-text="getActivityDescription(activity)"></p>
                                                <p class="text-xs text-gray-400 mt-1">By: <span x-text="activity.assigned_by_username"></span></p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                <time x-text="formatDate(activity.assigned_at)"></time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Details Modal -->
    <div x-show="modals.studentDetails" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modals.studentDetails" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity">
                <div class="absolute inset-0 bg-gray-500 bg-opacity-75" @click="modals.studentDetails = false"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="modals.studentDetails" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Student Details</h3>
                            <div class="mt-4" x-show="selectedStudent">
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900" x-text="selectedStudent ? `${selectedStudent.name_with_initials}` : ''"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Student ID</dt>
                                        <dd class="mt-1 text-sm text-gray-900" x-text="selectedStudent?.student_number"></dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                                        <dd class="mt-1 text-sm text-gray-900" x-text="selectedStudent?.email"></dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                        <dd class="mt-1 text-sm text-gray-900" x-text="selectedStudent?.phone"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                                        <dd class="mt-1">
                                            <span :class="selectedStudent?.is_current ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                                <span x-text="selectedStudent?.is_current ? 'Active' : 'Inactive'"></span>
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Enrolled Date</dt>
                                        <dd class="mt-1 text-sm text-gray-900" x-text="formatDate(selectedStudent?.assigned_at)"></dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Enrolled By</dt>
                                        <dd class="mt-1 text-sm text-gray-900" x-text="selectedStudent?.assigned_by_username || 'System'"></dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="modals.studentDetails = false" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors duration-200">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Redesigned PDF Export Modal -->
    <div x-show="pdfExportModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="pdfExportModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-50 backdrop-blur-sm">
                <div class="absolute inset-0" @click="closePDFModal()"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <!-- Modal content -->
            <div x-show="pdfExportModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                <!-- Header -->
                <div class="bg-gradient-to-r bg-indigo-500 px-6 py-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-white">Export Attendance Sheet</h3>
                                <p class="text-indigo-100 text-sm">Generate PDF attendance list</p>
                            </div>
                        </div>
                        <button @click="closePDFModal()"
                                :disabled="loading"
                                class="text-white hover:text-indigo-200 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="px-6 py-6 space-y-6">
                    <!-- Semester Info Card -->
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900" x-show="selectedSemester">
                                    <span x-text="selectedSemester?.academic_year + ' - Year ' + selectedSemester?.year + ', Semester ' + selectedSemester?.semester_number"></span>
                                </p>
                                <p class="text-xs text-gray-500" x-show="selectedSemester" x-text="selectedSemester?.department_name"></p>
                            </div>
                            <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <span class="mr-1" x-text="filteredStudents.length"></span> students
                            </span>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="generatePDF()" class="space-y-5">
                        <!-- Subject Selection - Updated to be optional -->
                        <div class="space-y-2">
                            <label for="subject_select" class="flex items-center text-sm font-medium text-gray-700">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                Subject/Course
                                <span class="ml-1 text-xs text-gray-500 font-normal">(optional)</span>
                            </label>
                            <div class="relative">
                                <select
                                        id="subject_select"
                                        x-model="exportForm.subject_id"
                                        class="w-full pl-4 pr-10 py-3 text-sm border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white"
                                >
                                    <option value="">Leave blank to fill manually</option>
                                    <template x-for="subject in semesterCurriculum" :key="subject.id">
                                        <option :value="subject.subject_id" x-text="`${subject.subject_name} (${subject.subject_code})`"></option>
                                    </template>
                                </select>
                            </div>
                            <p class="text-xs text-gray-500 flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                Select a subject or leave blank to write manually on the printed sheet
                            </p>
                        </div>

                        <!-- Date Selection -->
                        <div class="space-y-2">
                            <label for="export_date" class="flex items-center text-sm font-medium text-gray-700">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Date
                            </label>
                            <input
                                    type="date"
                                    id="export_date"
                                    x-model="exportForm.date"
                                    required
                                    class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                            >
                        </div>
                    </form>

                    <!-- Preview Info - Updated to handle optional subject -->
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-blue-900 mb-1">Ready to generate</h4>
                                <p class="text-sm text-blue-700">
                                    Attendance sheet will include <span class="font-medium" x-text="filteredStudents.length"></span> students
                                    <span x-show="exportForm.subject_id">
                                        for <span class="font-medium" x-text="semesterCurriculum.find(s => s.subject_id == exportForm.subject_id)?.subject_name"></span>
                                    </span>
                                    <span x-show="!exportForm.subject_id">with blank subject field to fill manually</span>.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions - Simplified to single generate button -->
                <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 space-y-3 space-y-reverse sm:space-y-0">
                    <button
                            type="button"
                            @click="closePDFModal()"
                            :disabled="loading"
                            class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Cancel
                    </button>

                    <button
                            type="button"
                            @click="generatePDF()"
                            :disabled="loading"
                            class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r bg-indigo-600 rounded-xl shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg x-show="!loading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span x-text="loading ? 'Generating PDF...' : 'Generate PDF'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function semesterStudentsOverview() {
        return {
            // Data
            semesters: [],
            selectedSemester: null,
            studentList: [],
            filteredStudents: [],
            departments: [],
            selectedDepartment: '',
            searchTerm: '',
            statusFilter: '',
            assignmentHistory: [],
            showHistory: false,
            selectedStudent: null,
            semesterCurriculum: [],

            // Loading states
            loading: false,
            studentsLoading: false,

            // Statistics
            stats: {
                total_students: 0,
                current_students: 0,
                first_assignment: null,
                last_assignment: null
            },

            // Alert system
            alert: {
                show: false,
                type: 'success',
                message: ''
            },

            // Modals
            modals: {
                studentDetails: false
            },

            pdfExportModal: false,
            exportForm: {
                subject_name: '',
                date: new Date().toISOString().split('T')[0]
            },

            async loadSemesterCurriculum() {
                if (!this.selectedSemester) return;

                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/semesters/curriculum.php?semester_id=${this.selectedSemester.id}`);
                    const data = await response.json();

                    if (data.success) {
                        this.semesterCurriculum = data.data || [];
                    } else {
                        console.error('Failed to load curriculum:', data.message);
                    }
                } catch (error) {
                    console.error('Error loading curriculum:', error);
                }
            },

            // Initialize
            async loadInitialData() {
                await this.loadDepartments();
                await this.loadSemesters();
            },

            // Load departments
            async loadDepartments() {
                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/departments.php`);
                    const data = await response.json();

                    if (data.success) {
                        this.departments = data.data || [];
                    } else {
                        this.showAlert('Error loading departments: ' + data.message, 'error');
                    }
                } catch (error) {
                    console.error('Error loading departments:', error);
                    this.showAlert('Error loading departments: ' + error.message, 'error');
                }
            },

            // Load semesters
            // Load semesters - Updated to reset data when department changes
            async loadSemesters() {
                this.loading = true;

                // Reset semester-related data when department filter changes
                this.selectedSemester = null;
                this.studentList = [];
                this.filteredStudents = [];
                this.assignmentHistory = [];
                this.showHistory = false;
                this.selectedStudent = null;
                this.semesterCurriculum = [];
                this.stats = {
                    total_students: 0,
                    current_students: 0,
                    first_assignment: null,
                    last_assignment: null
                };

                try {
                    const params = new URLSearchParams({ with_student_count: '1' });
                    if (this.selectedDepartment) params.append('department_id', this.selectedDepartment);

                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/semesters/get.php?${params}`);
                    const data = await response.json();

                    if (data.success) {
                        this.semesters = data.data || [];
                    } else {
                        this.showAlert('error', 'Error loading semesters: ' + data.message);
                    }
                } catch (error) {
                    this.showAlert('error', 'Failed to load semesters');
                } finally {
                    this.loading = false;
                }
            },

            // Select semester
            async selectSemester(semester) {
                this.selectedSemester = semester;
                this.showHistory = false;
                await this.loadSemesterStats();
                await this.loadStudentList();
                await this.loadAssignmentHistory();
                await this.loadSemesterCurriculum();
            },

            // Load semester statistics
            async loadSemesterStats() {
                if (!this.selectedSemester) return;

                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/semesters/semester-stats.php?semester_id=${this.selectedSemester.id}`);
                    const data = await response.json();

                    if (data.success) {
                        this.stats = data.data;
                    } else {
                        this.showAlert('error', 'Failed to load semester statistics');
                    }
                } catch (error) {
                    this.showAlert('error', 'Failed to load semester statistics');
                }
            },

            // Load student list
            async loadStudentList() {
                if (!this.selectedSemester) return;

                this.studentsLoading = true;
                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/semesters/enrolled-students.php?semester_id=${this.selectedSemester.id}`);
                    const data = await response.json();

                    if (data.success) {
                        this.studentList = data.data;
                        this.filterStudents();
                    } else {
                        this.showAlert('error', 'Failed to load student list');
                    }
                } catch (error) {
                    this.showAlert('error', 'Failed to load student list');
                } finally {
                    this.studentsLoading = false;
                }
            },

            // Load assignment history
            async loadAssignmentHistory() {
                if (!this.selectedSemester) return;

                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/semesters/assignment-history.php?semester_id=${this.selectedSemester.id}&limit=20`);
                    const data = await response.json();

                    if (data.success) {
                        this.assignmentHistory = data.data;
                    } else {
                        this.showAlert('error', 'Failed to load assignment history');
                    }
                } catch (error) {
                    this.showAlert('error', 'Failed to load assignment history');
                }
            },

            // Filter students based on search and status
            filterStudents() {
                let filtered = this.studentList;

                // Apply search filter
                if (this.searchTerm.trim()) {
                    const term = this.searchTerm.toLowerCase();
                    filtered = filtered.filter(student =>
                        (student.full_name || '').toLowerCase().includes(term) ||
                        (student.student_number || student.index_no || '').toLowerCase().includes(term) ||
                        (student.email || '').toLowerCase().includes(term)
                    );
                }

                // Apply status filter
                if (this.statusFilter) {
                    if (this.statusFilter === 'active') {
                        filtered = filtered.filter(student => student.is_current);
                    } else if (this.statusFilter === 'inactive') {
                        filtered = filtered.filter(student => !student.is_current);
                    }
                }

                this.filteredStudents = filtered;
            },

            // Refresh all data
            async refreshData() {
                await this.loadSemesters();
                if (this.selectedSemester) {
                    await this.loadSemesterStats();
                    await this.loadStudentList();
                    await this.loadAssignmentHistory();
                }
                this.showAlert('success', 'Data refreshed successfully');
            },

            // Utility functions
            getInitials(firstName, lastName) {
                return `${firstName?.charAt(0) || ''}${lastName?.charAt(0) || ''}`.toUpperCase();
            },

            formatDate(dateString) {
                if (!dateString) return '-';
                return new Date(dateString).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            },

            formatDateRange(startDate, endDate) {
                if (!startDate || !endDate) return 'No dates set';
                const start = new Date(startDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                const end = new Date(endDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                return `${start} - ${end}`;
            },

            getSemesterStatus(semester) {
                const now = new Date();
                const startDate = new Date(semester.start_date);
                const endDate = new Date(semester.end_date);

                if (now < startDate) return 'Upcoming';
                if (now > endDate) return 'Completed';
                return 'Active';
            },

            getSemesterStatusClass(semester) {
                const status = this.getSemesterStatus(semester);
                switch(status) {
                    case 'Active': return 'bg-green-100 text-green-800';
                    case 'Upcoming': return 'bg-blue-100 text-blue-800';
                    case 'Completed': return 'bg-gray-100 text-gray-800';
                    default: return 'bg-gray-100 text-gray-800';
                }
            },

            getActivityRate() {
                if (!this.stats.total_students) return 0;
                return Math.round((this.stats.current_students / this.stats.total_students) * 100);
            },

            getDaysRemaining() {
                if (!this.selectedSemester) return 0;
                const now = new Date();
                const endDate = new Date(this.selectedSemester.end_date);
                const diffTime = endDate - now;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                return diffDays > 0 ? diffDays : 0;
            },

            getActivityIconClass(action) {
                switch(action) {
                    case 'bulk_assign': return 'bg-green-500';
                    case 'individual_assign': return 'bg-blue-500';
                    case 'remove': return 'bg-red-500';
                    default: return 'bg-gray-500';
                }
            },

            getActivityDescription(activity) {
                switch(activity.action) {
                    case 'bulk_assign': return `Bulk assigned ${activity.student_count} students`;
                    case 'individual_assign': return `Assigned ${activity.student_count} student`;
                    case 'remove': return `Removed ${activity.student_count} students`;
                    default: return 'Unknown activity';
                }
            },

            // Actions
            async viewStudentDetails(student) {
                this.selectedStudent = student;
                this.modals.studentDetails = true;
            },

            async viewStudentHistory(student) {
                this.showHistory = true;
                this.showAlert('info', `Viewing enrollment history for ${student.first_name} ${student.last_name}`);
            },

            async exportStudentData() {
                if (!this.selectedSemester) {
                    this.showAlert('error', 'Please select a semester first');
                    return;
                }

                // Show export modal
                this.pdfExportModal = true;
            },

            // Generate PDF with form data
            async generatePDF() {
                if (!this.selectedSemester) {
                    this.showAlert('error', 'Please select a semester first');
                    return;
                }

                try {
                    this.loading = true;

                    // Get selected subject details
                    const selectedSubject = this.semesterCurriculum.find(s => s.subject_id == this.exportForm.subject_id);
                    const subjectName = selectedSubject ? `${selectedSubject.subject_name} (${selectedSubject.subject_code})` : '';

                    const exportData = {
                        semester_id: this.selectedSemester.id,
                        subject_name: subjectName,
                        date: this.exportForm.date
                    };

                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/semesters/export-attendance-pdf.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(exportData)
                    });

                    if (response.ok) {
                        // Handle PDF download
                        const blob = await response.blob();
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `Attendance_${this.selectedSemester.academic_year}_Year${this.selectedSemester.year}_Sem${this.selectedSemester.semester_number}_${this.exportForm.date}.pdf`;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);

                        this.showAlert('success', 'PDF downloaded successfully');
                        this.closePDFModal();
                    } else {
                        const errorData = await response.json();
                        throw new Error(errorData.message || 'Failed to generate PDF');
                    }

                } catch (error) {
                    console.error('PDF export error:', error);
                    this.showAlert('error', `Failed to export PDF: ${error.message}`);
                } finally {
                    this.loading = false;
                }
            },

            // Close PDF export modal
            closePDFModal() {
                this.pdfExportModal = false;
                this.exportForm = {
                    subject_id: '',
                    date: new Date().toISOString().split('T')[0]
                };
            },

            // Alert system
            showAlert(type, message) {
                this.alert = {
                    show: true,
                    type: type,
                    message: message
                };

                setTimeout(() => {
                    this.alert.show = false;
                }, 5000);
            }
        };
    }
</script>

<style>
    .semester-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .semester-card.selected {
        ring-width: 2px;
        ring-color: rgb(79 70 229);
    }
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .stats-card-2 {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .stats-card-3 {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    .stats-card-4 {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }
</style>