<?php
// public/admin/pages/students.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Auth\Middleware;

Middleware::check('STUDENT_WRITE');

if (!isset($_SERVER['HTTP_HX_REQUEST'])) {
    header('Location: ../dashboard.php?page=' . basename(__FILE__, '.php'));
    exit;
}

// Get enrollment years (current and previous years)
$currentYear = date('Y');
$enrollmentYears = [];
for ($i = 0; $i < 5; $i++) {
    $year = $currentYear - $i;
    $nextYear = $year + 1;
    $shortYear = substr($year, 2) . substr($nextYear, 2);
    $enrollmentYears[$shortYear] = $year . '-' . $nextYear;
}
?>

<div class="content-fade-in">
    <div class="container mx-auto p-8 mb-6">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6"
             x-data="studentManagement()"
             x-init="init()">
            <div class="px-4 py-5 sm:px-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Student Management</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Manage student registrations, approvals, and academic information.
                        </p>
                    </div>

                    <!-- Action buttons -->
                    <div class="flex space-x-3">
                        <!-- Visit Registration Page Button -->
                        <a target="_blank" :href="registrationUrl"
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            Student Registration
                        </a>

                        <!-- Copy Link with QR Code Button -->
                        <div class="relative">
                            <button @click="toggleQRCode()"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                <span x-text="showQR ? 'Hide QR Code' : 'Share Registration Link'"></span>
                            </button>

                            <!-- QR Code Dropdown -->
                            <div x-show="showQR"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 @click.away="showQR = false"
                                 class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                                <div class="p-4">
                                    <h4 class="text-sm font-medium text-gray-900 mb-3">Student Registration Link</h4>

                                    <!-- QR Code -->
                                    <div class="flex justify-center mb-4">
                                        <div id="qrcode" class="border rounded p-2 bg-white"></div>
                                    </div>

                                    <!-- Download QR Code Button -->
                                    <div class="flex justify-center mb-4">
                                        <button @click="downloadQRCode()"
                                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-md transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Download QR Code
                                        </button>
                                    </div>

                                    <!-- URL Display and Copy -->
                                    <div class="space-y-3">
                                        <div class="flex items-center space-x-2">
                                            <input type="text"
                                                   x-ref="urlInput"
                                                   :value="registrationUrl"
                                                   readonly
                                                   class="flex-1 text-xs border border-gray-300 rounded px-2 py-1 bg-gray-50 text-gray-600">
                                            <button @click="copyToClipboard()"
                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-indigo-600 hover:text-indigo-500">
                                                <svg x-show="!copied" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                                <svg x-show="copied" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                                            </button>
                                        </div>

                                        <p class="text-xs text-gray-500">
                                            Share this link or QR code with students to access the registration form.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-data="studentDashboard()" x-init="init()">
            <!-- Navigation Tabs -->
            <div class="bg-white border border-gray-200 rounded-lg mb-6 overflow-hidden">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex">
                        <button
                                class="px-6 py-3 text-sm font-medium border-b-2 transition-colors duration-200 flex items-center gap-2"
                                :class="activeTab === 'preregister' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                @click="activeTab = 'preregister'">
                            <!-- User Plus Icon -->
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            <span>Pre-Register</span>
                        </button>
                        <button
                                class="px-6 py-3 text-sm font-medium border-b-2 transition-colors duration-200 relative flex items-center gap-2"
                                :class="activeTab === 'pending' ? 'border-orange-600 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                @click="activeTab = 'pending'">
                            <!-- Clock Icon -->
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Pending Approvals</span>
                            <span x-show="pendingCount > 0"
                                  x-text="pendingCount"
                                  class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center ml-1"></span>
                        </button>
                        <button
                                class="px-6 py-3 text-sm font-medium border-b-2 transition-colors duration-200 flex items-center gap-2"
                                :class="activeTab === 'approved' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                @click="switchToApprovedTab()">
                            <!-- Check Circle Icon -->
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Approved Students</span>
                        </button>
                        <button
                                class="px-6 py-3 text-sm font-medium border-b-2 transition-colors duration-200 flex items-center gap-2"
                                :class="activeTab === 'rejected' ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                @click="switchToRejectedTab()">
                            <!-- X Circle Icon -->
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Rejected Applications</span>
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Pre-Registration Tab -->
            <div x-show="activeTab === 'preregister'" class="space-y-6">
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex">
                            <button
                                    class="px-4 py-2 text-sm font-medium border-b-2 transition-colors duration-200"
                                    :class="preRegSubTab === 'form' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    @click="preRegSubTab = 'form'">
                                Send New Invitation
                            </button>
                            <button
                                    class="px-4 py-2 text-sm font-medium border-b-2 transition-colors duration-200 flex items-center gap-2"
                                    :class="preRegSubTab === 'list' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    @click="switchToPreRegList()">
                                Pending Invitations
                                <span x-show="preRegisteredCount > 0"
                                      x-text="preRegisteredCount"
                                      class="bg-blue-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"></span>
                            </button>
                        </nav>
                    </div>

                    <!-- Form Sub-tab -->
                    <div x-show="preRegSubTab === 'form'" class="p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Send Registration Invitation</h3>
                            <p class="text-sm text-gray-600 mt-1">Send an invitation email to a student with a secure registration link</p>
                        </div>

                        <form @submit.prevent="submitPreRegistration()" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Department -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Department *</label>
                                    <select x-model="preRegForm.department_id" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Department</option>
                                        <template x-for="dept in departments" :key="dept.id">
                                            <option :value="dept.id" x-text="dept.name"></option>
                                        </template>
                                    </select>
                                </div>

                                <!-- Enrollment Year -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Enrollment Year *</label>
                                    <input type="number" x-model="preRegForm.enrollment_year" required
                                           min="2020"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <!-- NIC -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">NIC *</label>
                                    <input type="text" x-model="preRegForm.nic" required
                                           placeholder="123456789V or 199912345678"
                                           @input="parseNICData()"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <!-- Email -->
                                <div class="relative">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                    <input type="email"
                                           x-model="preRegForm.email"
                                           @input="checkEmailTypos()"
                                           @blur="checkEmailTypos()"
                                           required
                                           placeholder="Enter student's email address"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                                    <!-- Email Suggestion Banner -->
                                    <div x-show="emailSuggestion"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 transform -translate-y-2"
                                         x-transition:enter-end="opacity-100 transform translate-y-0"
                                         x-transition:leave="transition ease-in duration-150"
                                         x-transition:leave-start="opacity-100"
                                         x-transition:leave-end="opacity-0"
                                         class="absolute top-full left-0 right-0 mt-1 bg-yellow-50 border border-yellow-200 rounded-md p-3 shadow-lg z-20">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-2">
                                                <!-- Warning Icon -->
                                                <svg class="w-4 h-4 text-yellow-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                </svg>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm text-yellow-800">
                                                        Did you mean: <strong class="break-all" x-text="emailSuggestion"></strong>?
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex space-x-2 ml-3 flex-shrink-0">
                                                <button @click="acceptEmailSuggestion()"
                                                        type="button"
                                                        class="px-3 py-1 text-xs bg-yellow-600 text-white rounded hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-1 transition-colors">
                                                    Use this
                                                </button>
                                                <button @click="dismissEmailSuggestion()"
                                                        type="button"
                                                        class="px-3 py-1 text-xs bg-gray-300 text-gray-700 rounded hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-1 transition-colors">
                                                    Dismiss
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mode -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Mode *</label>
                                    <select x-model="preRegForm.mode" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="FULL">Full-time</option>
                                        <option value="PART">Part-time</option>
                                    </select>
                                </div>

                                <!-- Parsed Info Display -->
                                <div x-show="preRegForm.birthday || preRegForm.gender" class="md:col-span-2">
                                    <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                                        <p class="text-sm text-blue-700">
                                            <strong>Parsed from NIC:</strong>
                                            <span x-show="preRegForm.birthday">Birthday: <span x-text="preRegForm.birthday"></span></span>
                                            <span x-show="preRegForm.gender">• Gender: <span x-text="preRegForm.gender === 'M' ? 'Male' : 'Female'"></span></span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-3 pt-4">
                                <button type="button" @click="resetPreRegForm()"
                                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                                    Reset
                                </button>
                                <button type="submit" :disabled="preRegLoading"
                                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50">
                                    <span x-show="!preRegLoading">Send Invitation</span>
                                    <span x-show="preRegLoading">Sending...</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- List Sub-tab -->
                    <div x-show="preRegSubTab === 'list'" class="p-6">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Pending Registration Invitations</h3>
                            <p class="text-sm text-gray-600 mt-1">Students who have been sent invitation emails but haven't completed their registration yet</p>
                        </div>

                        <!-- Info Banner -->
                        <div x-show="preRegisteredCount > 0" class="mb-4 bg-blue-50 border border-blue-200 rounded-md p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1 md:flex md:justify-between">
                                    <p class="text-sm text-blue-700">
                                        <span x-text="preRegisteredCount"></span> invitation<span x-show="preRegisteredCount !== 1">s</span> pending completion.
                                        Students will receive an email with a secure registration link to complete their profile.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                                <select x-model="preRegDepartmentFilter" @change="filterPreRegisteredStudents()"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Departments</option>
                                    <template x-for="dept in departments" :key="dept.id">
                                        <option :value="dept.id" x-text="dept.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mode</label>
                                <select x-model="preRegModeFilter" @change="filterPreRegisteredStudents()"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Modes</option>
                                    <option value="FULL">Full-time</option>
                                    <option value="PART">Part-time</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input type="text" x-model="preRegSearchTerm" @input="filterPreRegisteredStudents()"
                                       placeholder="Search by email, NIC..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <!-- Students table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Info</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mode</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrollment Year</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invitation Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="student in filteredPreRegisteredStudents" :key="student.id">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900" x-text="student.email"></div>
                                                <div class="text-sm text-gray-500" x-text="student.nic"></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="getDepartmentName(student.department_id)"></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                      :class="student.mode === 'FULL' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'"
                                      x-text="student.mode === 'FULL' ? 'Full-time' : 'Part-time'"></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="student.enrollment_year || 'Not set'"></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Awaiting Registration
                                </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button @click="resendInvitation(student)"
                                                    class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                Resend Invitation
                                            </button>
                                            <button @click="deletePreRegistration(student)"
                                                    class="text-red-600 hover:text-red-900">
                                                Cancel
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="filteredPreRegisteredStudents.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2M4 13h2m13-8V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v1M7 8h10M7 12h4m1 8l-1-1h4l-1 1"></path>
                                            </svg>
                                            <p class="text-lg font-medium text-gray-900 mb-1">No pending invitations</p>
                                            <p class="text-sm text-gray-500">All students have completed their registration or no invitations have been sent yet.</p>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Approvals Tab -->
            <div x-show="activeTab === 'pending'" class="space-y-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Pending Today</p>
                                <p class="text-2xl font-semibold text-orange-600" x-text="stats.pending_today || 0"></p>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Pending</p>
                                <p class="text-2xl font-semibold text-red-600" x-text="stats.pending_total || 0"></p>
                            </div>
                            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Approved Today</p>
                                <p class="text-2xl font-semibold text-green-600" x-text="stats.approved_today || 0"></p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">This Week</p>
                                <p class="text-2xl font-semibold text-blue-600" x-text="stats.week_total || 0"></p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters and Search Section -->
                <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <!-- Department Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                            <select x-model="pendingDepartmentFilter"
                                    @change="filterPendingStudents()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Departments</option>
                                <template x-for="dept in departments" :key="dept.id">
                                    <option :value="dept.id" x-text="dept.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Mode Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mode</label>
                            <select x-model="pendingModeFilter"
                                    @change="filterPendingStudents()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Modes</option>
                                <option value="FULL">Full-time</option>
                                <option value="PART">Part-time</option>
                            </select>
                        </div>

                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <div class="relative">
                                <input type="text"
                                       x-model="pendingSearchTerm"
                                       @input="filterPendingStudents()"
                                       placeholder="Search by name, email, NIC..."
                                       class="w-full px-3 py-2 pl-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <button @click="loadPendingRegistrations(); pendingDepartmentFilter = ''; pendingModeFilter = ''; pendingSearchTerm = ''"
                                    class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Refresh
                            </button>
                            <button @click="pendingDepartmentFilter = ''; pendingModeFilter = ''; pendingSearchTerm = ''; filterPendingStudents()"
                                    class="px-3 py-2 text-gray-600 text-sm font-medium rounded-md hover:bg-gray-50 border border-gray-300">
                                Clear
                            </button>
                        </div>
                    </div>

                    <!-- Filter Results Summary -->
                    <div class="mt-4 flex items-center justify-between text-sm text-gray-600">
                        <div>
                            Showing <span class="font-medium" x-text="filteredPendingStudents.length"></span>
                            of <span class="font-medium" x-text="pendingStudents.length"></span> pending applications
                            <span x-show="pendingDepartmentFilter || pendingModeFilter || pendingSearchTerm" class="text-blue-600">
                    (filtered)
                </span>
                        </div>
                        <div x-show="pendingDepartmentFilter || pendingModeFilter || pendingSearchTerm" class="text-blue-600">
                            <button @click="pendingDepartmentFilter = ''; pendingModeFilter = ''; pendingSearchTerm = ''; filterPendingStudents()"
                                    class="underline hover:no-underline">
                                Clear all filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pending Registrations Table -->
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-medium text-gray-900">Pending Registration Approvals</h2>
                        <button @click="loadPendingRegistrations"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Refresh
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Application</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Details</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Academic Info</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applied</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="student in filteredPendingStudents" :key="student.id">
                                <tr class="hover:bg-gray-50">
                                    <!-- Application Info -->
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm">
                                            <div class="font-medium text-gray-900" x-text="'APP-' + String(student.id).padStart(6, '0')"></div>
                                            <div class="text-gray-500">Application ID</div>
                                        </div>
                                    </td>

                                    <!-- Student Details -->
                                    <td class="px-4 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div x-show="!student.photo" class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <svg class="h-6 w-6 text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                    </svg>
                                                </div>
                                                <img
                                                        x-show="student.photo"
                                                        class="h-10 w-10 rounded-full object-cover"
                                                        :src="`${window.APP_CONFIG.API_BASE_URL}/students/photo.php?index=${encodeURIComponent(student.id)}&w=80&h=80&fit=crop`"
                                                        :alt="student.name_with_initials"
                                                />
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900" x-text="student.name_with_initials"></div>
                                                <div class="text-sm text-gray-500" x-text="student.full_name"></div>
                                                <div class="text-xs text-gray-400" x-text="student.nic"></div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Academic Info -->
                                    <td class="px-4 py-4">
                                        <div class="text-sm text-gray-900" x-text="getDepartmentName(student.department_id)"></div>
                                        <div class="text-sm text-gray-500">
                                            <span :class="student.mode === 'FULL' ? 'text-blue-600' : 'text-purple-600'" x-text="student.mode === 'FULL' ? 'Full-time' : 'Part-time'"></span>
                                            • <span x-text="student.enrollment_year"></span>
                                        </div>
                                    </td>

                                    <!-- Contact -->
                                    <td class="px-4 py-4">
                                        <div class="text-sm text-gray-900" x-text="student.email"></div>
                                        <div class="text-sm text-gray-500" x-text="student.contact_no"></div>
                                    </td>

                                    <!-- Applied Date -->
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div x-text="formatDate(student.registered_at)"></div>
                                        <div class="text-xs" x-text="getTimeAgo(student.registered_at)"></div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <button @click="viewPendingStudent(student)"
                                                    class="text-blue-600 hover:text-blue-900 p-2 rounded hover:bg-blue-50"
                                                    title="View Details">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filteredPendingStudents.length === 0 && pendingStudents.length > 0">
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 text-sm">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        <p class="text-gray-500">No pending registrations match your filters</p>
                                        <p class="text-gray-400 text-sm">Try adjusting your search criteria</p>
                                    </div>
                                </td>
                            </tr>
                            <tr x-show="pendingStudents.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 text-sm">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-gray-500">No pending registrations found</p>
                                        <p class="text-gray-400 text-sm">All applications have been processed</p>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Approved Students Tab -->
            <div x-show="activeTab === 'approved'" class="space-y-6">
                <!-- Department Filter -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex flex-wrap items-center gap-4">
                        <label class="text-sm font-medium text-gray-700">Filter by:</label>

                        <!-- Department Filter -->
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-600">Department:</label>
                            <select x-model="selectedDepartmentFilter"
                                    @change="filterApprovedStudents()"
                                    class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Departments</option>
                                <template x-for="dept in departments" :key="dept.id">
                                    <option :value="dept.id" x-text="dept.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Academic Status Filter -->
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-600">Academic Status:</label>
                            <select x-model="selectedAcademicStatusFilter"
                                    @change="filterApprovedStudents()"
                                    class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Status</option>
                                <option value="ACTIVE">Active</option>
                                <option value="SUSPENDED">Suspended</option>
                                <option value="GRADUATED">Graduated</option>
                                <option value="DROPPED_OUT">Dropped Out</option>
                            </select>
                        </div>

                        <!-- Search Filter -->
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-600">Search:</label>
                            <input type="text"
                                   x-model="searchTerm"
                                   @input="filterApprovedStudents()"
                                   placeholder="Name, email, or index..."
                                   class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="text-sm text-gray-600">
                            <span x-text="filteredApprovedStudents.length"></span> student(s) found
                        </div>
                    </div>
                </div>

                <!-- Approved Students Table -->
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-medium text-gray-900">Approved Students</h2>
                        <button @click="loadApprovedStudents"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Refresh
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Index Number</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Details</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approved Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="student in filteredApprovedStudents" :key="student.id">
                                <tr class="hover:bg-gray-50">
                                    <!-- Index Number -->
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-mono text-gray-900" x-text="student.index_no"></div>
                                    </td>

                                    <!-- Student Details -->
                                    <td class="px-4 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div x-show="!student.photo" class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <svg class="h-6 w-6 text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                    </svg>
                                                </div>
                                                <img
                                                        x-show="student.photo"
                                                        class="h-10 w-10 rounded-full object-cover"
                                                        :src="`${window.APP_CONFIG.API_BASE_URL}/students/photo.php?index=${encodeURIComponent(student.id)}&w=80&h=80&fit=crop`"
                                                        :alt="student.name_with_initials"
                                                />
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900" x-text="student.name_with_initials"></div>
                                                <div class="text-sm text-gray-500" x-text="student.full_name"></div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Department -->
                                    <td class="px-4 py-4">
                                        <div class="text-sm text-gray-900" x-text="getDepartmentName(student.department_id)"></div>
                                        <div class="text-sm text-gray-500 flex items-center gap-2">
                                            <span :class="student.mode === 'FULL' ? 'text-blue-600' : 'text-purple-600'" x-text="student.mode === 'FULL' ? 'Full-time' : 'Part-time'"></span>
                                            • <span x-text="student.enrollment_year"></span>
                                            • <span class="px-2 py-1 text-xs rounded-full"
                                                    :class="{
                                                           'bg-green-100 text-green-800': student.academic_status === 'ACTIVE',
                                                           'bg-yellow-100 text-yellow-800': student.academic_status === 'SUSPENDED',
                                                           'bg-blue-100 text-blue-800': student.academic_status === 'GRADUATED',
                                                           'bg-red-100 text-red-800': student.academic_status === 'DROPPED_OUT',
                                                           'bg-gray-100 text-gray-800': !student.academic_status
                                                       }"
                                                    x-text="student.academic_status || 'N/A'"></span>
                                        </div>
                                    </td>

                                    <!-- Contact -->
                                    <td class="px-4 py-4">
                                        <div class="text-sm text-gray-900" x-text="student.email"></div>
                                        <div class="text-sm text-gray-500" x-text="student.contact_no"></div>
                                    </td>

                                    <!-- Approved Date -->
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div x-text="formatDate(student.approved_at)"></div>
                                    </td>

                                    <!-- Updated Actions column in the approved students table -->
                                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <!-- Edit Button -->
                                            <button @click="editApprovedStudent(student)"
                                                    class="text-blue-600 hover:text-blue-900 p-2 rounded hover:bg-blue-50"
                                                    title="Edit Student">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>

                                            <!-- View Details Button -->
                                            <button @click="viewApprovedStudent(student)"
                                                    class="text-gray-600 hover:text-gray-900 p-2 rounded hover:bg-gray-50"
                                                    title="View Details">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </button>

                                            <!-- Academic Status History Button (Optional) -->
                                            <button @click="viewAcademicStatusHistory(student)"
                                                    class="text-green-600 hover:text-green-900 p-2 rounded hover:bg-green-50"
                                                    title="Academic Status History">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filteredApprovedStudents.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 text-sm">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-gray-500">No approved students found</p>
                                        <p class="text-gray-400 text-sm" x-text="selectedDepartmentFilter ? 'Try selecting a different department' : 'Students will appear here once approved'"></p>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Rejected Applications Tab -->
            <div x-show="activeTab === 'rejected'" class="space-y-6">
                <!-- Filters and Search Section -->
                <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <!-- Department Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                            <select x-model="rejectedDepartmentFilter"
                                    @change="filterRejectedStudents()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Departments</option>
                                <template x-for="dept in departments" :key="dept.id">
                                    <option :value="dept.id" x-text="dept.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Mode Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mode</label>
                            <select x-model="rejectedModeFilter"
                                    @change="filterRejectedStudents()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Modes</option>
                                <option value="FULL">Full-time</option>
                                <option value="PART">Part-time</option>
                            </select>
                        </div>

                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <div class="relative">
                                <input type="text"
                                       x-model="rejectedSearchTerm"
                                       @input="filterRejectedStudents()"
                                       placeholder="Search by name, email, NIC..."
                                       class="w-full px-3 py-2 pl-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <button @click="loadRejectedStudents(); rejectedDepartmentFilter = ''; rejectedModeFilter = ''; rejectedSearchTerm = ''"
                                    class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Refresh
                            </button>
                            <button @click="rejectedDepartmentFilter = ''; rejectedModeFilter = ''; rejectedSearchTerm = ''; filterRejectedStudents()"
                                    class="px-3 py-2 text-gray-600 text-sm font-medium rounded-md hover:bg-gray-50 border border-gray-300">
                                Clear
                            </button>
                        </div>
                    </div>

                    <!-- Filter Results Summary -->
                    <div class="mt-4 flex items-center justify-between text-sm text-gray-600">
                        <div>
                            Showing <span class="font-medium" x-text="filteredRejectedStudents.length"></span>
                            of <span class="font-medium" x-text="rejectedStudents.length"></span> rejected applications
                            <span x-show="rejectedDepartmentFilter || rejectedModeFilter || rejectedSearchTerm" class="text-blue-600">
                    (filtered)
                </span>
                        </div>
                    </div>
                </div>

                <!-- Rejected Applications Table -->
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Rejected Applications</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Application</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Details</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Academic Info</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rejection Details</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rejected On</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="student in filteredRejectedStudents" :key="student.id">
                                <tr class="hover:bg-gray-50">
                                    <!-- Application Info -->
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm">
                                            <div class="font-medium text-gray-900" x-text="'APP-' + String(student.id).padStart(6, '0')"></div>
                                            <div class="text-gray-500">Application ID</div>
                                        </div>
                                    </td>

                                    <!-- Student Details -->
                                    <td class="px-4 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div x-show="!student.photo" class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <svg class="h-6 w-6 text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                    </svg>
                                                </div>
                                                <img
                                                        x-show="student.photo"
                                                        class="h-10 w-10 rounded-full object-cover"
                                                        :src="`${window.APP_CONFIG.API_BASE_URL}/students/photo.php?index=${encodeURIComponent(student.id)}&w=80&h=80&fit=crop`"
                                                        :alt="student.name_with_initials"
                                                />
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900" x-text="student.name_with_initials"></div>
                                                <div class="text-sm text-gray-500" x-text="student.full_name"></div>
                                                <div class="text-xs text-gray-400" x-text="student.nic"></div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Academic Info -->
                                    <td class="px-4 py-4">
                                        <div class="text-sm text-gray-900" x-text="getDepartmentName(student.department_id)"></div>
                                        <div class="text-sm text-gray-500">
                                            <span :class="student.mode === 'FULL' ? 'text-blue-600' : 'text-purple-600'" x-text="student.mode === 'FULL' ? 'Full-time' : 'Part-time'"></span>
                                            • <span x-text="student.enrollment_year"></span>
                                        </div>
                                    </td>

                                    <!-- Rejection Details -->
                                    <td class="px-4 py-4 max-w-xs">
                                        <div class="text-sm text-gray-900 truncate" x-text="student.rejection_reason" :title="student.rejection_reason"></div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <span x-show="student.allow_resubmission" class="text-green-600">Resubmission Allowed</span>
                                            <span x-show="!student.allow_resubmission" class="text-red-600">Final Rejection</span>
                                        </div>
                                    </td>

                                    <!-- Rejected Date -->
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div x-text="formatDate(student.rejected_at)"></div>
                                        <div class="text-xs" x-text="getTimeAgo(student.rejected_at)"></div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <button @click="viewRejectedStudent(student)"
                                                    class="text-blue-600 hover:text-blue-900 p-2 rounded hover:bg-blue-50"
                                                    title="View Details">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </button>
                                            <button @click="confirmDeleteApplication(student)"
                                                    class="text-red-600 hover:text-red-900 p-2 rounded hover:bg-red-50"
                                                    title="Delete Application">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filteredRejectedStudents.length === 0 && rejectedStudents.length > 0">
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 text-sm">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        <p class="text-gray-500">No rejected applications match your filters</p>
                                    </div>
                                </td>
                            </tr>
                            <tr x-show="rejectedStudents.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 text-sm">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-gray-500">No rejected applications found</p>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Student Detail Modal -->
            <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Background overlay -->
                    <div class="fixed inset-0 transition-opacity" @click="closeModal()">
                        <div class="absolute inset-0 bg-gray-900 opacity-60"></div>
                    </div>

                    <!-- Modal container -->
                    <div class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-6xl sm:w-full">
                        <div class="max-h-[calc(100vh-4rem)] overflow-y-auto will-change-transform">

                            <!-- Header with gradient background -->
                            <div class="bg-gradient-to-r bg-indigo-600 px-8 py-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <!-- Profile Image -->
                                        <div class="flex-shrink-0">
                                            <div x-show="!selectedStudent?.photo" class="w-16 h-16 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                </svg>
                                            </div>
                                            <img x-show="selectedStudent?.photo"
                                                 class="w-16 h-16 rounded-full object-cover border-4 border-white border-opacity-30"
                                                 :src="`${window.APP_CONFIG.API_BASE_URL}/students/photo.php?index=${encodeURIComponent(selectedStudent?.id ?? '')}&w=256&h=256&fit=crop`"
                                                 :alt="selectedStudent?.name_with_initials">
                                        </div>

                                        <div class="text-white">
                                            <h3 class="text-2xl font-bold" x-text="selectedStudent?.name_with_initials"></h3>
                                            <p class="text-indigo-100 text-sm" x-text="selectedStudent?.full_name"></p>
                                            <div class="flex items-center mt-2 space-x-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white bg-opacity-20 text-white">
                                        <span x-text="'APP-' + String(selectedStudent?.id).padStart(6, '0')"></span>
                                    </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                      :class="getStatusBadgeClass(selectedStudent?.status)">
                                        <span x-text="getStatusText(selectedStudent?.status)"></span>
                                    </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Close button -->
                                    <button @click="closeModal()"
                                            class="text-white hover:text-gray-200 transition-colors p-2 hover:bg-white hover:bg-opacity-10 rounded-full">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Student QR Code Section - Only for Approved Students -->
                            <div x-show="selectedStudent?.status === 'APPROVED'" x-cloak class="px-8 py-4 bg-gray-50 border-b">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-600 mr-2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                                        </svg>
                                        <h4 class="text-sm font-medium text-gray-900">Student QR Code (NIC)</h4>
                                    </div>
                                    <button @click="toggleStudentQR()"
                                            class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                                        <svg x-show="!showStudentQR" class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        <svg x-show="showStudentQR" class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                        </svg>
                                        <span x-text="showStudentQR ? 'Hide QR' : 'Show QR'"></span>
                                    </button>
                                </div>

                                <!-- QR Code Display -->
                                <div x-show="showStudentQR" x-transition class="mt-4 flex flex-col items-center">
                                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                                        <div id="studentQRCode"></div>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <p class="text-xs text-gray-600 mb-2">QR Code contains: <span class="font-mono" x-text="selectedStudent?.nic"></span></p>
                                        <button @click="downloadStudentQRCode()"
                                                class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 transition-colors">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Download QR
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Info Cards -->
                            <div class="px-8 py-6 bg-gray-50 border-b">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                                    <!-- Index Number Card -->
                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-xs text-gray-500 uppercase tracking-wide">Index Number</p>
                                                <div class="mt-1">
                                                    <p x-show="selectedStudent?.index_no" class="text-sm font-semibold text-gray-900" x-text="selectedStudent?.index_no"></p>
                                                    <p x-show="!selectedStudent?.index_no && selectedStudent?.status === 'PENDING_APPROVAL'" class="text-sm text-amber-600 font-medium">Will be generated upon approval</p>
                                                    <p x-show="!selectedStudent?.index_no && selectedStudent?.status !== 'PENDING_APPROVAL'" class="text-sm text-gray-500 italic">Not assigned</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Department Card -->
                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-2 0H7m14 0V9a2 2 0 00-2-2M9 7h6m-6 4h6m-6 4h6"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 uppercase tracking-wide">Department</p>
                                                <p class="text-sm font-semibold text-gray-900" x-text="selectedStudent?.department_name"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Study Mode Card -->
                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 uppercase tracking-wide">Study Mode</p>
                                                <div class="flex items-center mt-1">
                                                    <p class="text-sm font-semibold text-gray-900" x-text="selectedStudent?.mode === 'FULL' ? 'Full-time' : 'Part-time'"></p>
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                                          :class="selectedStudent?.mode === 'FULL' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'">
                                            <span x-text="selectedStudent?.mode"></span>
                                        </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Enrollment Year Card -->
                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 12v-8"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-xs text-gray-500 uppercase tracking-wide">Enrollment Year</p>
                                                <div class="mt-1">
                                                    <p class="text-sm font-semibold text-gray-900" x-text="selectedStudent?.enrollment_year || 'Not set'"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Section for Pending Applications -->
                            <div x-show="selectedStudent?.status === 'PENDING_APPROVAL'" class="px-8 py-6 bg-gradient-to-r from-orange-50 to-yellow-50 border-b">

                                <!-- Status Banner -->
                                <div x-show="!showApprovalForm && !showRejectionForm" x-cloak class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center mr-4">
                                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="text-lg font-semibold text-gray-900">Application Pending Review</h4>
                                            <p class="text-sm text-gray-600">This application requires your decision to proceed.</p>
                                        </div>
                                    </div>
                                    <div class="flex space-x-3">
                                        <button @click="showApprovalForm = true; showRejectionForm = false"
                                                class="inline-flex items-center px-6 py-3 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 shadow-sm hover:shadow-md">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Approve Application
                                        </button>
                                        <button @click="showRejectionForm = true; showApprovalForm = false"
                                                class="inline-flex items-center px-6 py-3 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 shadow-sm hover:shadow-md">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Reject Application
                                        </button>
                                    </div>
                                </div>

                                <!-- Approval Form -->
                                <div x-show="showApprovalForm" x-transition x-cloak class="bg-white rounded-lg border-2 border-green-200 p-6">
                                    <div class="flex items-start mb-6">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <h4 class="text-lg font-semibold text-green-900 mb-2">Approve Student Registration</h4>
                                            <p class="text-sm text-green-800 mb-4">
                                                This will activate the student account and send approval notification. Please review the details below:
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Approval Form Fields -->
                                    <div class="space-y-6">
                                        <!-- Enrollment Year -->
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <label class="block text-sm font-medium text-gray-900 mb-2">
                                                Enrollment Year <span class="text-red-500">*</span>
                                            </label>
                                            <input type="number"
                                                   x-model="selectedStudent.enrollment_year"
                                                   @input="updateAutoPreview()"
                                                   min="2020"
                                                   max="2030"
                                                   class="w-32 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white"
                                                   placeholder="2024"
                                                   required>
                                            <p class="text-xs text-gray-600 mt-1">This will be used in the student index number</p>
                                        </div>

                                        <!-- Index Generation Method -->
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <label class="block text-sm font-medium text-gray-900 mb-3">Index Number Generation</label>
                                            <div class="flex space-x-6 mb-4">
                                                <label class="flex items-center">
                                                    <input type="radio"
                                                           x-model="indexGenerationMode"
                                                           value="auto"
                                                           class="mr-3 text-green-600 focus:ring-green-500">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">Auto Generate</div>
                                                        <div class="text-xs text-gray-600">System will generate automatically</div>
                                                    </div>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="radio"
                                                           x-model="indexGenerationMode"
                                                           value="manual"
                                                           class="mr-3 text-green-600 focus:ring-green-500">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">Manual Entry</div>
                                                        <div class="text-xs text-gray-600">Enter custom index number</div>
                                                    </div>
                                                </label>
                                            </div>

                                            <!-- Manual Index Entry -->
                                            <div x-show="indexGenerationMode === 'manual'" x-transition class="mt-4">
                                                <label class="block text-sm font-medium text-gray-900 mb-2">Custom Index Number</label>
                                                <div class="flex border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-green-500 focus-within:border-green-500 bg-white">
                                                    <input type="text"
                                                           :value="generateIndexPrefix()"
                                                           readonly
                                                           class="px-3 py-2 bg-gray-100 text-gray-700 font-mono text-sm border-0 outline-none flex-shrink-0">
                                                    <input type="text"
                                                           x-model="manualIndexSuffix"
                                                           @input="validateManualIndex()"
                                                           @blur="formatIndexSuffix()"
                                                           placeholder="0001"
                                                           maxlength="4"
                                                           class="px-3 py-2 font-mono text-sm border-0 outline-none flex-1 min-w-0"
                                                           :class="{'text-red-600': indexValidationErrors.length > 0}">
                                                </div>

                                                <!-- Loading indicator for validation -->
                                                <div x-show="manualIndexValidating" class="flex items-center mt-2 text-sm text-blue-600">
                                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    Validating index number...
                                                </div>

                                                <p class="text-xs text-gray-600 mt-1">Format: KUR/DEPT/YEAR/MODE/NNNN</p>

                                                <!-- Validation Messages -->
                                                <div x-show="indexValidationErrors.length > 0" class="mt-2">
                                                    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                                        <div class="text-sm font-medium text-red-900 mb-1">Validation Errors:</div>
                                                        <template x-for="error in indexValidationErrors" :key="error">
                                                            <p class="text-xs text-red-700" x-text="error"></p>
                                                        </template>
                                                    </div>
                                                </div>

                                                <!-- Success state for manual index -->
                                                <div x-show="indexGenerationMode === 'manual' && manualIndexSuffix && indexValidationErrors.length === 0 && !manualIndexValidating" class="mt-2">
                                                    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                                        <div class="text-sm font-medium text-green-900 mb-1">✓ Valid Index Number</div>
                                                        <div class="font-mono text-sm text-green-800" x-text="getManualIndexNumber()"></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Auto Generation Preview with Live Updates -->
                                            <div x-show="indexGenerationMode === 'auto' && selectedStudent.enrollment_year"
                                                 x-transition
                                                 class="mt-4"
                                                 x-data="{ previewIndex: '' }"
                                                 x-init="
                                                        $watch('selectedStudent.enrollment_year', async () => {
                                                            if (indexGenerationMode === 'auto' && selectedStudent.enrollment_year) {
                                                                previewIndex = await getAutoIndexPreview();
                                                            }
                                                        });
                                                        // Initial load
                                                        if (indexGenerationMode === 'auto' && selectedStudent.enrollment_year) {
                                                            previewIndex = await getAutoIndexPreview();
                                                        }
                                                     ">
                                                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                                    <div class="text-sm font-medium text-green-900 mb-1">Preview Index Number</div>
                                                    <div class="flex items-center">
                                                        <div x-show="previewIndexLoading" class="flex items-center text-green-700">
                                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                            Generating preview...
                                                        </div>
                                                        <div x-show="!previewIndexLoading" class="font-mono text-sm text-green-800" x-text="previewIndex || 'Loading...'"></div>
                                                    </div>
                                                    <p class="text-xs text-green-700 mt-1">Actual sequence number will be assigned automatically</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Action Summary -->
                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                            <h5 class="text-sm font-medium text-blue-900 mb-2">Actions that will be performed:</h5>
                                            <ul class="text-sm text-blue-800 space-y-1">
                                                <li class="flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    <span x-text="indexGenerationMode === 'auto' ? 'Generate unique student index number' : 'Assign custom index number'"></span>
                                                </li>
                                                <li class="flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    Activate student account with login credentials
                                                </li>
                                                <li class="flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    Send approval email with account details
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Approval Actions -->
                                    <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                                        <button @click="showApprovalForm = false; resetApprovalForm()"
                                                :disabled="approvalLoading"
                                                class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 disabled:opacity-50 transition-colors">
                                            Cancel
                                        </button>
                                        <button @click="processApproval()"
                                                :disabled="approvalLoading || !selectedStudent.enrollment_year || (indexGenerationMode === 'manual' && (indexValidationErrors.length > 0 || manualIndexValidating)) || previewIndexLoading"
                                                class="inline-flex items-center px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                            <span x-show="approvalLoading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                                            <span x-show="!approvalLoading">Confirm Approval</span>
                                            <span x-show="approvalLoading">Processing...</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Rejection Form -->
                                <div x-show="showRejectionForm" x-transition x-cloak class="bg-white rounded-lg border-2 border-red-200 p-6">
                                    <div class="flex items-start mb-6">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <h4 class="text-lg font-semibold text-red-900 mb-2">Reject Student Application</h4>
                                            <p class="text-sm text-red-800 mb-4">
                                                Please provide a clear reason for rejecting this application. This information will be sent to the student.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Common Rejection Reasons -->
                                    <div class="mb-3">
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="reason in commonRejectionReasons" :key="reason">
                                                <button type="button"
                                                        @click="rejectionReason = reason"
                                                        class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full border transition-colors">
                                                    <span x-text="reason"></span>
                                                </button>
                                            </template>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Click on a common reason to use it, or write your own below</p>
                                    </div>

                                    <div class="mb-6">
                                        <label class="block text-sm font-medium text-red-900 mb-2">Reason for Rejection <span class="text-red-500">*</span></label>
                                        <textarea x-model="rejectionReason"
                                                  rows="4"
                                                  required
                                                  class="w-full px-3 py-2 border border-red-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white"
                                                  placeholder="Please provide a detailed reason for rejection (e.g., incomplete documentation, eligibility requirements not met, etc.)"></textarea>

                                        <div class="mt-4">
                                            <label class="flex items-center">
                                                <input type="checkbox" x-model="allowResubmission"
                                                       class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                                <span class="ml-2 text-sm text-gray-700">
                                                    Allow student to edit and resubmit their application
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                                        <button @click="showRejectionForm = false; rejectionReason = ''"
                                                :disabled="rejectionLoading"
                                                class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 disabled:opacity-50 transition-colors">
                                            Cancel
                                        </button>
                                        <button @click="processRejection()"
                                                :disabled="rejectionLoading || !rejectionReason.trim()"
                                                class="inline-flex items-center px-6 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                            <span x-show="rejectionLoading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                                            <span x-show="!rejectionLoading">Confirm Rejection</span>
                                            <span x-show="rejectionLoading">Processing...</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Approved/Rejected Status Banner -->
                            <div x-show="selectedStudent?.status === 'APPROVED'" x-cloak class="px-8 py-4 bg-green-50 border-b border-green-200">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-green-900">Application Approved</h4>
                                        <p class="text-xs text-green-700">Student has been approved and can access the system</p>
                                    </div>
                                </div>
                            </div>

                            <div x-show="selectedStudent?.status === 'REJECTED'" x-cloak class="px-8 py-4 bg-red-50 border-b border-red-200">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-red-900">Application Rejected</h4>
                                        <p class="text-xs text-red-700">This application has been rejected</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Content Tabs -->
                            <div class="px-8 py-6">
                                <!-- Tab Navigation -->
                                <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg mb-6">
                                    <button @click="modalActiveTab = 'personal'"
                                            :class="modalActiveTab === 'personal' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                                            class="flex-1 py-2 px-4 text-sm font-medium rounded-md transition-colors">
                                        Personal Information
                                    </button>
                                    <button @click="modalActiveTab = 'contact'"
                                            :class="modalActiveTab  === 'contact' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                                            class="flex-1 py-2 px-4 text-sm font-medium rounded-md transition-colors">
                                        Contact Details
                                    </button>
                                    <button @click="modalActiveTab = 'qualifications'"
                                            :class="modalActiveTab === 'qualifications' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                                            class="flex-1 py-2 px-4 text-sm font-medium rounded-md transition-colors">
                                        Qualifications
                                    </button>
                                    <button @click="modalActiveTab  = 'emergency'"
                                            :class="modalActiveTab === 'emergency' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                                            class="flex-1 py-2 px-4 text-sm font-medium rounded-md transition-colors">
                                        Emergency Contact
                                    </button>
                                </div>

                                <!-- Personal Information Tab -->
                                <div x-show="modalActiveTab === 'personal'" x-cloak class="space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- NIC -->
                                        <div class="space-y-2">
                                            <label class="flex items-center text-sm font-medium text-gray-700">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                                </svg>
                                                National Identity Card
                                            </label>
                                            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                                                <p class="text-gray-900 font-mono text-sm" x-text="selectedStudent?.nic"></p>
                                            </div>
                                        </div>

                                        <!-- Gender -->
                                        <div class="space-y-2">
                                            <label class="flex items-center text-sm font-medium text-gray-700">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                Gender
                                            </label>
                                            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                                                <p class="text-gray-900 text-sm" x-text="selectedStudent?.gender === 'M' ? 'Male' : 'Female'"></p>
                                            </div>
                                        </div>

                                        <!-- Birthday -->
                                        <div class="space-y-2">
                                            <label class="flex items-center text-sm font-medium text-gray-700">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 12v-8"></path>
                                                </svg>
                                                Date of Birth
                                            </label>
                                            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                                                <p class="text-gray-900 text-sm" x-text="formatDate(selectedStudent?.birthday)"></p>
                                            </div>
                                        </div>

                                        <!-- Registration Date -->
                                        <div class="space-y-2">
                                            <label class="flex items-center text-sm font-medium text-gray-700">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 12v-8"></path>
                                                </svg>
                                                Application Date
                                            </label>
                                            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                                                <p class="text-gray-900 text-sm" x-text="formatDate(selectedStudent?.registered_at)"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Details Tab -->
                                <div x-show="modalActiveTab === 'contact'" x-cloak class="space-y-6">
                                    <div class="grid grid-cols-1 gap-6">
                                        <!-- Email -->
                                        <div class="space-y-2">
                                            <label class="flex items-center text-sm font-medium text-gray-700">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                </svg>
                                                Email Address
                                            </label>
                                            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                                                <p class="text-gray-900 text-sm" x-text="selectedStudent?.email"></p>
                                            </div>
                                        </div>

                                        <!-- Phone -->
                                        <div class="space-y-2">
                                            <label class="flex items-center text-sm font-medium text-gray-700">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                </svg>
                                                Contact Number
                                            </label>
                                            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                                                <p class="text-gray-900 font-mono text-sm" x-text="selectedStudent?.contact_no"></p>
                                            </div>
                                        </div>

                                        <!-- Address -->
                                        <div class="space-y-2">
                                            <label class="flex items-center text-sm font-medium text-gray-700">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                Address
                                            </label>
                                            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                                                <p class="text-gray-900 text-sm" x-text="selectedStudent?.address"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Qualifications Tab -->
                                <div x-show="modalActiveTab === 'qualifications'" x-cloak class="space-y-8">
                                    <!-- O/L Qualifications -->
                                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 border border-blue-200">
                                        <div class="flex items-center mb-4">
                                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900">G.C.E. Ordinary Level</h3>
                                                <p class="text-sm text-gray-600">O/L Examination Results</p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                            <!-- O/L Year -->
                                            <div class="space-y-2">
                                                <label class="text-sm font-medium text-gray-700">Examination Year</label>
                                                <div class="bg-white border border-gray-200 rounded-lg px-4 py-3">
                                                    <p class="text-gray-900 text-sm" x-text="selectedStudent?.ol_year"></p>
                                                </div>
                                            </div>

                                            <!-- O/L Index -->
                                            <div class="space-y-2">
                                                <label class="text-sm font-medium text-gray-700">Index Number</label>
                                                <div class="bg-white border border-gray-200 rounded-lg px-4 py-3">
                                                    <p class="text-gray-900 font-mono text-sm" x-text="selectedStudent?.ol_index_no"></p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- O/L Subjects -->
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-gray-700">Subject Results</label>
                                            <div class="bg-white border border-gray-200 rounded-lg p-4">
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                    <template x-for="(subject, index) in selectedStudent?.ol_subjects || []" :key="'ol-subject-' + index">
                                                        <div class="flex justify-between items-center bg-gray-50 px-3 py-2 rounded-md">
                                                            <span class="text-sm text-gray-700" x-text="subject.subject"></span>
                                                            <span class="text-sm font-semibold px-2 py-1 rounded-full"
                                                                  :class="{
                                                          'bg-green-100 text-green-800': subject.grade === 'A',
                                                          'bg-blue-100 text-blue-800': subject.grade === 'B',
                                                          'bg-yellow-100 text-yellow-800': subject.grade === 'C',
                                                          'bg-orange-100 text-orange-800': subject.grade === 'S',
                                                          'bg-red-100 text-red-800': subject.grade === 'F',
                                                          'bg-gray-100 text-gray-800': !['A', 'B', 'C', 'S', 'F'].includes(subject.grade)
                                                      }"
                                                                  x-text="subject.grade"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- A/L Qualifications -->
                                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-6 border border-purple-200">
                                        <div class="flex items-center mb-4">
                                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900">G.C.E. Advanced Level</h3>
                                                <p class="text-sm text-gray-600">A/L Examination Results</p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                                            <!-- A/L Year -->
                                            <div class="space-y-2">
                                                <label class="text-sm font-medium text-gray-700">Examination Year</label>
                                                <div class="bg-white border border-gray-200 rounded-lg px-4 py-3">
                                                    <p class="text-gray-900 text-sm" x-text="selectedStudent?.al_year"></p>
                                                </div>
                                            </div>

                                            <!-- A/L Index -->
                                            <div class="space-y-2">
                                                <label class="text-sm font-medium text-gray-700">Index Number</label>
                                                <div class="bg-white border border-gray-200 rounded-lg px-4 py-3">
                                                    <p class="text-gray-900 font-mono text-sm" x-text="selectedStudent?.al_index_no"></p>
                                                </div>
                                            </div>

                                            <!-- A/L Stream -->
                                            <div class="space-y-2">
                                                <label class="text-sm font-medium text-gray-700">Stream</label>
                                                <div class="bg-white border border-gray-200 rounded-lg px-4 py-3">
                                                    <p class="text-gray-900 text-sm" x-text="selectedStudent?.al_stream"></p>
                                                </div>
                                            </div>

                                            <!-- Z-Score -->
                                            <div class="space-y-2">
                                                <label class="text-sm font-medium text-gray-700">Z-Score</label>
                                                <div class="bg-white border border-gray-200 rounded-lg px-4 py-3">
                                                    <p class="text-gray-900 font-mono text-sm" x-text="parseFloat(selectedStudent?.al_zscore || 0).toFixed(4)"></p>
                                                </div>
                                            </div>

                                            <!-- General Test -->
                                            <div class="space-y-2">
                                                <label class="text-sm font-medium text-gray-700">General Test</label>
                                                <div class="bg-white border border-gray-200 rounded-lg px-4 py-3">
                                                    <p class="text-gray-900 text-sm" x-text="selectedStudent?.al_general_test + '/100'"></p>
                                                </div>
                                            </div>

                                            <!-- General English -->
                                            <div class="space-y-2">
                                                <label class="text-sm font-medium text-gray-700">General English</label>
                                                <div class="bg-white border border-gray-200 rounded-lg px-4 py-3">
                                                <span class="text-sm font-semibold px-2 py-1 rounded-full"
                                                      :class="{
                                                          'bg-green-100 text-green-800': selectedStudent?.al_general_english === 'A',
                                                          'bg-blue-100 text-blue-800': selectedStudent?.al_general_english === 'B',
                                                          'bg-yellow-100 text-yellow-800': selectedStudent?.al_general_english === 'C',
                                                          'bg-orange-100 text-orange-800': selectedStudent?.al_general_english === 'S',
                                                          'bg-red-100 text-red-800': selectedStudent?.al_general_english === 'F',
                                                          'bg-gray-100 text-gray-800': !['A', 'B', 'C', 'S', 'F'].includes(selectedStudent?.al_general_english)
                                                      }"
                                                      x-text="selectedStudent?.al_general_english"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- A/L Subjects -->
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-gray-700">Subject Results</label>
                                            <div class="bg-white border border-gray-200 rounded-lg p-4">
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                    <template x-for="(subject, index) in selectedStudent?.al_subjects || []" :key="'al-subject-' + index">
                                                        <div class="flex justify-between items-center bg-gray-50 px-3 py-2 rounded-md">
                                                            <span class="text-sm text-gray-700" x-text="subject.subject"></span>
                                                            <span class="text-sm font-semibold px-2 py-1 rounded-full"
                                                                  :class="{
                                                          'bg-green-100 text-green-800': subject.grade === 'A',
                                                          'bg-blue-100 text-blue-800': subject.grade === 'B',
                                                          'bg-yellow-100 text-yellow-800': subject.grade === 'C',
                                                          'bg-orange-100 text-orange-800': subject.grade === 'S',
                                                          'bg-red-100 text-red-800': subject.grade === 'F',
                                                          'bg-gray-100 text-gray-800': !['A', 'B', 'C', 'S', 'F'].includes(subject.grade)
                                                      }"
                                                                  x-text="subject.grade"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Emergency Contact Tab -->
                                <div x-show="modalActiveTab === 'emergency'" x-cloak class="space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Contact Name -->
                                        <div class="space-y-2">
                                            <label class="flex items-center text-sm font-medium text-gray-700">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                Emergency Contact Name
                                            </label>
                                            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                                                <p class="text-gray-900 text-sm" x-text="selectedStudent?.dependant_name"></p>
                                            </div>
                                        </div>

                                        <!-- Profession -->
                                        <div class="space-y-2">
                                            <label class="flex items-center text-sm font-medium text-gray-700">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0H8m8 0v2a2 2 0 01-2 2H10a2 2 0 01-2-2V6"></path>
                                                </svg>
                                                Profession
                                            </label>
                                            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                                                <p class="text-gray-900 text-sm" x-text="selectedStudent?.dependant_prof"></p>
                                            </div>
                                        </div>

                                        <!-- Contact Number -->
                                        <div class="space-y-2">
                                            <label class="flex items-center text-sm font-medium text-gray-700">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                </svg>
                                                Contact Number
                                            </label>
                                            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                                                <p class="text-gray-900 font-mono text-sm" x-text="selectedStudent?.dependant_phone"></p>
                                            </div>
                                        </div>

                                        <!-- Address -->
                                        <div class="space-y-2 md:col-span-2">
                                            <label class="flex items-center text-sm font-medium text-gray-700">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                Address
                                            </label>
                                            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                                                <p class="text-gray-900 text-sm" x-text="selectedStudent?.dependant_addr"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="bg-gray-50 px-8 py-4 flex justify-between items-center border-t">
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Application submitted: <span x-text="formatDate(selectedStudent?.registered_at)"></span></span>
                                </div>

                                <button @click="closeModal()"
                                        class="px-6 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Student Modal -->
            <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Background overlay -->
                    <div class="fixed inset-0 transition-opacity" @click="closeEditModal()">
                        <div class="absolute inset-0 bg-black opacity-50 backdrop-blur-sm"></div>
                    </div>

                    <!-- Modal container -->
                    <div class="inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-5xl sm:w-full border border-gray-100">
                        <div class="max-h-[calc(100vh-4rem)] overflow-y-auto will-change-transform">

                            <!-- Header -->
                            <div class="bg-white border-b border-gray-100 px-8 py-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg flex items-center justify-center shadow-lg">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-2xl font-semibold text-gray-900">Edit Student Information</h3>
                                            <p class="text-gray-600 text-sm mt-1" x-text="editingStudent?.name_with_initials"></p>
                                            <div class="flex items-center mt-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                        <span x-text="editingStudent?.index_no"></span>
                                    </span>
                                            </div>
                                        </div>
                                    </div>
                                    <button @click="closeEditModal()"
                                            class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-lg">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Warning Message for Index Changes -->
                            <div x-show="showIndexWarning" x-transition class="mx-8 mt-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-amber-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-semibold text-amber-900">Index Number Change Required</h3>
                                        <div class="mt-2 text-sm text-amber-800">
                                            <p>Changing the department or enrollment year will require generating a new index number. The student's current index number will be archived.</p>
                                            <div class="mt-3 bg-white rounded-md p-3 border border-amber-200">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-xs font-medium text-amber-700">Current:</span>
                                                    <span class="font-mono text-sm text-amber-900" x-text="editingStudent?.index_no"></span>
                                                </div>
                                                <div class="flex justify-between items-center mt-1">
                                                    <span class="text-xs font-medium text-amber-700">New Pattern:</span>
                                                    <span class="font-mono text-sm text-amber-900" x-text="getNewIndexPattern()"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Form -->
                            <div class="px-8 py-6">
                                <form @submit.prevent="updateStudent()">

                                    <!-- Personal Information Section -->
                                    <div class="mb-8">
                                        <div class="flex items-center mb-6">
                                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                            <h4 class="text-lg font-semibold text-gray-900">Personal Information</h4>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="space-y-2">
                                                <label class="block text-sm font-semibold text-gray-700">Name with Initials</label>
                                                <input type="text" x-model="editForm.name_with_initials"
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white"
                                                       required>
                                            </div>
                                            <div class="space-y-2">
                                                <label class="block text-sm font-semibold text-gray-700">Full Name</label>
                                                <input type="text" x-model="editForm.full_name"
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white"
                                                       required>
                                            </div>
                                            <div class="space-y-2">
                                                <label class="block text-sm font-semibold text-gray-700">NIC</label>
                                                <input type="text" x-model="editForm.nic"
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white"
                                                       required>
                                            </div>
                                            <div class="space-y-2">
                                                <label class="block text-sm font-semibold text-gray-700">Gender</label>
                                                <select x-model="editForm.gender"
                                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white"
                                                        required>
                                                    <option value="M">Male</option>
                                                    <option value="F">Female</option>
                                                </select>
                                            </div>
                                            <div class="space-y-2">
                                                <label class="block text-sm font-semibold text-gray-700">Date of Birth</label>
                                                <input type="date" x-model="editForm.birthday"
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white"
                                                       required>
                                            </div>
                                            <div class="space-y-2">
                                                <label class="block text-sm font-semibold text-gray-700">Email</label>
                                                <input type="email" x-model="editForm.email"
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white"
                                                       required>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Divider -->
                                    <div class="border-t border-gray-200 my-8"></div>

                                    <!-- Academic Information Section -->
                                    <div class="mb-8">
                                        <div class="flex items-center mb-6">
                                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-2 0H7m14 0V9a2 2 0 00-2-2M9 7h6m-6 4h6m-6 4h6"></path>
                                                </svg>
                                            </div>
                                            <h4 class="text-lg font-semibold text-gray-900">Academic Information</h4>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="space-y-2">
                                                <label class="block text-sm font-semibold text-gray-700">Department</label>
                                                <select x-model="editForm.department_id"
                                                        @change="checkIndexChange()"
                                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white"
                                                        required>
                                                    <template x-for="dept in departments" :key="dept.id">
                                                        <option :value="dept.id" x-text="dept.name"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="space-y-2">
                                                <label class="block text-sm font-semibold text-gray-700">Enrollment Year</label>
                                                <input type="number" x-model="editForm.enrollment_year"
                                                       @input="checkIndexChange()"
                                                       min="2020"
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white"
                                                       required>
                                            </div>
                                            <div class="space-y-2">
                                                <label class="block text-sm font-semibold text-gray-700">Study Mode</label>
                                                <input type="text" :value="editForm.mode === 'FULL' ? 'Full-time' : 'Part-time'"
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600"
                                                       readonly>
                                                <p class="text-xs text-gray-500">Study mode cannot be changed for approved students</p>
                                            </div>
                                            <!-- Replace your Academic Status dropdown with this fixed version -->

                                            <div class="space-y-2">
                                                <label class="block text-sm font-semibold text-gray-700">Academic Status</label>
                                                <select x-model="editForm.academic_status"
                                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white"
                                                        required>
                                                    <option value="">Select Status</option>
                                                    <option value="Active" :selected="editForm.academic_status === 'Active'">Active</option>
                                                    <option value="Inactive" :selected="editForm.academic_status === 'Inactive'">Inactive</option>
                                                    <option value="OnLeave" :selected="editForm.academic_status === 'OnLeave'">On Leave</option>
                                                    <option value="Suspended" :selected="editForm.academic_status === 'Suspended'">Suspended</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Academic Status Change Reason -->
                                        <div x-show="editForm.academic_status !== originalAcademicStatus" x-transition class="mt-6">
                                            <div class="space-y-2">
                                                <label class="block text-sm font-semibold text-gray-700">Reason for Status Change</label>
                                                <textarea x-model="editForm.status_change_reason"
                                                          rows="4"
                                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white resize-none"
                                                          placeholder="Please provide a reason for the academic status change..."
                                                          ></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Divider -->
                                    <div class="border-t border-gray-200 my-8"></div>

                                    <!-- Contact Information Section -->
                                    <div class="mb-8">
                                        <div class="flex items-center mb-6">
                                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                </svg>
                                            </div>
                                            <h4 class="text-lg font-semibold text-gray-900">Contact Information</h4>
                                        </div>
                                        <div class="grid grid-cols-1 gap-6">
                                            <div class="space-y-2">
                                                <label class="block text-sm font-semibold text-gray-700">Contact Number</label>
                                                <input type="tel" x-model="editForm.contact_no"
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white"
                                                       required>
                                            </div>
                                            <div class="space-y-2">
                                                <label class="block text-sm font-semibold text-gray-700">Address</label>
                                                <textarea x-model="editForm.address"
                                                          rows="4"
                                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white resize-none"
                                                          required></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Divider -->
                                    <div class="border-t border-gray-200 my-8"></div>

                                    <!-- Emergency Contact Section -->
                                    <div class="mb-8">
                                        <div class="flex items-center mb-6">
                                            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20a3 3 0 01-3-3v-2a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1M7 20v-2c0-.656.126-1.283.356-1.857M7 20a3 3 0 003-3v-2a3 3 0 003-3V7a3 3 0 00-3-3H7a3 3 0 00-3 3v5a3 3 0 003 3z"></path>
                                                </svg>
                                            </div>
                                            <h4 class="text-lg font-semibold text-gray-900">Emergency Contact</h4>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="space-y-2">
                                                <label class="block text-sm font-semibold text-gray-700">Contact Name</label>
                                                <input type="text" x-model="editForm.dependant_name"
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white"
                                                       required>
                                            </div>
                                            <div class="space-y-2">
                                                <label class="block text-sm font-semibold text-gray-700">Profession</label>
                                                <input type="text" x-model="editForm.dependant_prof"
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white"
                                                       required>
                                            </div>
                                            <div class="space-y-2">
                                                <label class="block text-sm font-semibold text-gray-700">Contact Number</label>
                                                <input type="tel" x-model="editForm.dependant_phone"
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white"
                                                       required>
                                            </div>
                                            <div class="space-y-2">
                                                <label class="block text-sm font-semibold text-gray-700">Address</label>
                                                <textarea x-model="editForm.dependant_addr"
                                                          rows="4"
                                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white resize-none"
                                                          required></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Index Number Generation Section -->
                                    <div x-show="showIndexWarning" x-transition class="mb-8">
                                        <div class="border-t border-gray-200 pt-8">
                                            <div class="flex items-center mb-6">
                                                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center mr-3">
                                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                    </svg>
                                                </div>
                                                <h4 class="text-lg font-semibold text-gray-900">New Index Number Generation</h4>
                                            </div>

                                            <div class="bg-gray-50 rounded-lg p-6 mb-4 border border-gray-200">
                                                <!-- Generation Mode Selection -->
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                                    <label class="flex items-start p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-white transition-colors"
                                                           :class="{'bg-blue-50 border-blue-300': editIndexGenerationMode === 'auto'}">
                                                        <input type="radio" x-model="editIndexGenerationMode" value="auto"
                                                               class="mt-1 mr-4 text-blue-600 focus:ring-blue-500">
                                                        <div>
                                                            <div class="text-sm font-semibold text-gray-900">Auto Generate</div>
                                                            <div class="text-xs text-gray-600 mt-1">System will generate automatically based on sequence</div>
                                                        </div>
                                                    </label>
                                                    <label class="flex items-start p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-white transition-colors"
                                                           :class="{'bg-blue-50 border-blue-300': editIndexGenerationMode === 'manual'}">
                                                        <input type="radio" x-model="editIndexGenerationMode" value="manual"
                                                               class="mt-1 mr-4 text-blue-600 focus:ring-blue-500">
                                                        <div>
                                                            <div class="text-sm font-semibold text-gray-900">Manual Entry</div>
                                                            <div class="text-xs text-gray-600 mt-1">Enter custom index number manually</div>
                                                        </div>
                                                    </label>
                                                </div>

                                                <!-- Auto Generation Preview -->
                                                <div x-show="editIndexGenerationMode === 'auto'" x-transition class="mt-6">
                                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                                        <div class="flex items-center justify-between mb-3">
                                                            <div class="text-sm font-semibold text-blue-900">Preview Next Index Number</div>
                                                            <div x-show="editPreviewIndexLoading" class="flex items-center text-blue-700">
                                                                <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                                </svg>
                                                                <span class="text-xs">Loading...</span>
                                                            </div>
                                                        </div>

                                                        <!-- Preview Display -->
                                                        <div class="font-mono text-lg text-blue-800 bg-white px-4 py-3 rounded border mb-3"
                                                             x-text="getEditAutoIndexPreview()"></div>

                                                        <!-- Preview Details -->
                                                        <div x-show="editIndexPreviewData && Object.keys(editIndexPreviewData).length > 0" class="space-y-2">
                                                            <div class="flex justify-between text-xs text-blue-700">
                                                                <span>Existing students in this category:</span>
                                                                <span x-text="editIndexPreviewData.existing_students || 0"></span>
                                                            </div>
                                                            <div class="flex justify-between text-xs text-blue-700">
                                                                <span>Pattern:</span>
                                                                <span x-text="editIndexPreviewData.pattern || ''"></span>
                                                            </div>
                                                            <div x-show="editIndexPreviewData.note" class="text-xs text-blue-600 italic"
                                                                 x-text="editIndexPreviewData.note"></div>
                                                        </div>

                                                        <p class="text-xs text-blue-700 mt-3">Final sequence number will be assigned when saved</p>
                                                    </div>
                                                </div>

                                                <!-- Manual Index Entry -->
                                                <div x-show="editIndexGenerationMode === 'manual'" x-transition class="mt-6">
                                                    <label class="block text-sm font-semibold text-gray-900 mb-3">Custom Index Number</label>

                                                    <!-- Index Input Field -->
                                                    <div class="flex border rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500 bg-white"
                                                         :class="{'border-red-300 focus-within:ring-red-500 focus-within:border-red-500': editIndexValidationErrors.length > 0}">
                                                        <input type="text" :value="getNewIndexPrefix()" readonly
                                                               class="px-4 py-3 bg-gray-100 text-gray-700 font-mono text-sm border-0 outline-none flex-shrink-0">
                                                        <input type="text"
                                                               x-model="editManualIndexSuffix"
                                                               @blur="formatEditIndexSuffix()"
                                                               placeholder="0001"
                                                               maxlength="4"
                                                               class="px-4 py-3 font-mono text-sm border-0 outline-none flex-1 min-w-0"
                                                               :class="{'text-red-600': editIndexValidationErrors.length > 0}"
                                                               :disabled="editManualIndexValidating">
                                                        <div x-show="editManualIndexValidating" class="px-3 py-3 bg-gray-50 flex items-center">
                                                            <svg class="animate-spin h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                        </div>
                                                    </div>

                                                    <div class="flex justify-between items-center mt-2">
                                                        <p class="text-xs text-gray-600">Format: KUR/DEPT/YEAR/MODE/NNNN</p>
                                                        <div x-show="editManualIndexSuffix && editIndexValidationErrors.length === 0"
                                                             class="text-xs text-green-600 flex items-center">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            Valid format
                                                        </div>
                                                    </div>

                                                    <!-- Validation Messages -->
                                                    <div x-show="editIndexValidationErrors.length > 0" x-transition class="mt-4">
                                                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                                            <div class="flex items-start">
                                                                <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                                </svg>
                                                                <div>
                                                                    <div class="text-sm font-semibold text-red-900 mb-2">Validation Errors:</div>
                                                                    <template x-for="error in editIndexValidationErrors" :key="error">
                                                                        <p class="text-xs text-red-700 mb-1" x-text="error"></p>
                                                                    </template>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Validation Warnings -->
                                                    <div x-show="editIndexValidationWarnings && editIndexValidationWarnings.length > 0" x-transition class="mt-4">
                                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                                            <div class="flex items-start">
                                                                <svg class="w-5 h-5 text-yellow-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                                </svg>
                                                                <div>
                                                                    <div class="text-sm font-semibold text-yellow-900 mb-2">Warnings:</div>
                                                                    <template x-for="warning in editIndexValidationWarnings" :key="warning">
                                                                        <p class="text-xs text-yellow-700 mb-1" x-text="warning"></p>
                                                                    </template>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Helpful Suggestions -->
                                                    <div x-show="editIndexValidationSuggestions && editIndexValidationSuggestions.length > 0" x-transition class="mt-4">
                                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                                            <div class="flex items-start">
                                                                <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                                                </svg>
                                                                <div>
                                                                    <div class="text-sm font-semibold text-blue-900 mb-2">Suggestions:</div>
                                                                    <template x-for="suggestion in editIndexValidationSuggestions" :key="suggestion">
                                                                        <p class="text-xs text-blue-700 mb-1" x-text="suggestion"></p>
                                                                    </template>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Current vs New Comparison -->
                                                    <div class="mt-4 bg-gray-50 rounded-lg p-4 border border-gray-200">
                                                        <div class="text-sm font-semibold text-gray-900 mb-3">Index Number Comparison</div>
                                                        <div class="space-y-2">
                                                            <div class="flex justify-between items-center">
                                                                <span class="text-xs font-medium text-gray-700">Current:</span>
                                                                <span class="font-mono text-sm text-gray-900 bg-white px-3 py-1 rounded border" x-text="editingStudent?.index_no || 'N/A'"></span>
                                                            </div>
                                                            <div class="flex justify-between items-center">
                                                                <span class="text-xs font-medium text-gray-700">New:</span>
                                                                <span class="font-mono text-sm text-blue-900 bg-blue-50 px-3 py-1 rounded border"
                                                                      x-text="getEditManualIndexNumber() || 'Enter suffix above'"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Form Actions -->
                                    <div class="bg-gray-50 -mx-8 px-8 py-6 mt-8 border-t border-gray-200">
                                        <div class="flex justify-end space-x-4">
                                            <button type="button" @click="closeEditModal()"
                                                    :disabled="editLoading"
                                                    class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-white hover:shadow-sm disabled:opacity-50 transition-all duration-200">
                                                Cancel
                                            </button>
                                            <button type="submit"
                                                    :disabled="editLoading || (showIndexWarning && editIndexGenerationMode === 'manual' && (editIndexValidationErrors.length > 0 || editManualIndexValidating))"
                                                    class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl">

                                                <!-- Loading Spinner -->
                                                <span x-show="editLoading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-3"></span>

                                                <!-- Validation Spinner -->
                                                <span x-show="!editLoading && editManualIndexValidating" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-3"></span>

                                                <!-- Button Text -->
                                                <span x-show="!editLoading && !editManualIndexValidating">Update Student</span>
                                                <span x-show="editLoading">Updating...</span>
                                                <span x-show="!editLoading && editManualIndexValidating">Validating...</span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Status History Modal Component -->
            <div x-show="showStatusHistoryModal"
                 class="fixed inset-0 z-50 overflow-y-auto"
                 x-cloak>
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Background overlay -->
                    <div class="fixed inset-0 transition-opacity" @click="closeStatusHistoryModal()">
                        <div class="absolute inset-0 bg-gray-900 opacity-60"></div>
                    </div>

                    <!-- Modal container -->
                    <div class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-6xl sm:w-full">
                        <div class="max-h-[calc(100vh-4rem)] overflow-y-auto will-change-transform">

                            <!-- Header with gradient background matching Student Detail Modal -->
                            <div class="bg-gradient-to-r bg-indigo-600 px-8 py-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <!-- Profile Image -->
                                        <div class="flex-shrink-0">
                                            <div x-show="!statusHistoryStudent?.photo" class="w-16 h-16 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                </svg>
                                            </div>
                                            <img x-show="statusHistoryStudent?.photo"
                                                 class="w-16 h-16 rounded-full object-cover border-4 border-white border-opacity-30"
                                                 :src="`${window.APP_CONFIG.API_BASE_URL}/students/photo.php?index=${encodeURIComponent(statusHistoryStudent?.id ?? '')}&w=256&h=256&fit=crop`"
                                                 :alt="statusHistoryStudent?.name_with_initials">
                                        </div>

                                        <div class="text-white">
                                            <h3 class="text-2xl font-bold" x-text="statusHistoryStudent?.name_with_initials"></h3>
                                            <p class="text-indigo-100 text-sm" x-text="statusHistoryStudent?.full_name"></p>
                                            <div class="flex items-center mt-2 space-x-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white bg-opacity-20 text-white">
                                        <span x-text="'APP-' + String(statusHistoryStudent?.id).padStart(6, '0')"></span>
                                    </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Academic History
                                    </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Close button -->
                                    <button @click="closeStatusHistoryModal()"
                                            class="text-white hover:text-gray-200 transition-colors p-2 hover:bg-white hover:bg-opacity-10 rounded-full">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Quick Info Cards matching Student Detail Modal style -->
                            <div class="px-8 py-6 bg-gray-50 border-b">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <!-- Current Status Card -->
                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 uppercase tracking-wide">Current Status</p>
                                                <p class="text-sm font-semibold text-gray-900" x-text="getStatusText(statusHistoryStudent?.status)"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Total Changes Card -->
                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 uppercase tracking-wide">Total Changes</p>
                                                <p class="text-sm font-semibold text-gray-900" x-text="statusHistory.length || 0"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Index Number Card -->
                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 uppercase tracking-wide">Index Number</p>
                                                <p class="text-sm font-semibold text-gray-900 font-mono" x-text="statusHistoryStudent?.index_no || 'Not assigned'"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Department Card -->
                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-2 0H7m14 0V9a2 2 0 00-2-2M9 7h6m-6 4h6m-6 4h6"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 uppercase tracking-wide">Department</p>
                                                <p class="text-sm font-semibold text-gray-900" x-text="statusHistoryStudent?.department_name"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Content Area -->
                            <div class="px-8 py-6">
                                <!-- Loading State -->
                                <div x-show="statusHistoryLoading" class="flex items-center justify-center py-20">
                                    <div class="text-center">
                                        <div class="relative">
                                            <div class="animate-spin rounded-full h-16 w-16 border-4 border-indigo-200 border-t-indigo-600 mx-auto"></div>
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <div class="w-6 h-6 bg-indigo-600 rounded-full animate-pulse"></div>
                                            </div>
                                        </div>
                                        <p class="text-gray-600 mt-4 text-lg">Loading academic status history...</p>
                                    </div>
                                </div>

                                <!-- Error State -->
                                <div x-show="statusHistoryError && !statusHistoryLoading" class="py-8">
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-6 max-w-2xl mx-auto">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4 flex-1">
                                                <h4 class="text-lg font-semibold text-red-900 mb-2">Unable to Load History</h4>
                                                <p class="text-sm text-red-700 mb-4" x-text="statusHistoryError"></p>
                                                <button @click="loadStatusHistory()"
                                                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 shadow-sm hover:shadow-md">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                    </svg>
                                                    Retry Loading
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Empty State -->
                                <div x-show="!statusHistoryLoading && !statusHistoryError && statusHistory.length === 0" class="py-12">
                                    <div class="text-center">
                                        <div class="bg-gray-50 rounded-lg p-12 max-w-lg mx-auto">
                                            <div class="w-20 h-20 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-6">
                                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                            <h4 class="text-xl font-semibold text-gray-900 mb-3">No Status Changes</h4>
                                            <p class="text-gray-600">This student's academic record shows no status changes have been recorded yet.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Timeline Content -->
                                <div x-show="!statusHistoryLoading && !statusHistoryError && statusHistory.length > 0" class="space-y-6">
                                    <!-- Timeline Header -->
                                    <div class="text-center mb-8">
                                        <h4 class="text-xl font-semibold text-gray-900 mb-2">Academic Status Timeline</h4>
                                        <p class="text-sm text-gray-600">Complete history of status changes and academic progress</p>
                                    </div>

                                    <!-- Timeline Container -->
                                    <div class="relative">
                                        <!-- Timeline Line -->
                                        <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gradient-to-b from-indigo-300 to-indigo-500"></div>

                                        <!-- Timeline Items -->
                                        <div class="space-y-6">
                                            <template x-for="(entry, index) in statusHistory" :key="index">
                                                <div class="relative">
                                                    <!-- Timeline Dot -->
                                                    <div class="absolute left-5 w-7 h-7 bg-indigo-600 rounded-full flex items-center justify-center shadow-lg border-4 border-white z-10">
                                                        <span class="text-white font-bold text-xs" x-text="index + 1"></span>
                                                    </div>

                                                    <!-- Status Change Card -->
                                                    <div class="ml-16 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                                                        <!-- Card Header -->
                                                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                                            <div class="flex items-center justify-between">
                                                                <div class="flex items-center space-x-4">
                                                                    <!-- Status Change Flow -->
                                                                    <div class="flex items-center space-x-3">
                                                                        <!-- From Status -->
                                                                        <div class="flex items-center space-x-2">
                                                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                                                                                 :class="getStatusIconBgClass(entry.old_status || 'Initial')">
                                                                                <span class="text-lg" x-text="getStatusIcon(entry.old_status || 'Initial')"></span>
                                                                            </div>
                                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                                                  :class="getStatusBadgeClass(entry.old_status || 'Initial')"
                                                                                  x-text="entry.old_status || 'Initial'"></span>
                                                                        </div>

                                                                        <!-- Arrow -->
                                                                        <div class="flex items-center">
                                                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                                                            </svg>
                                                                        </div>

                                                                        <!-- To Status -->
                                                                        <div class="flex items-center space-x-2">
                                                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                                                                                 :class="getStatusIconBgClass(entry.new_status)">
                                                                                <span class="text-lg" x-text="getStatusIcon(entry.new_status)"></span>
                                                                            </div>
                                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                                                  :class="getStatusBadgeClass(entry.new_status)"
                                                                                  x-text="entry.new_status"></span>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Date -->
                                                                <div class="flex items-center text-sm text-gray-500">
                                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                                    </svg>
                                                                    <span x-text="formatStatusHistoryDate(entry.changed_at)"></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Card Content - Reason -->
                                                        <div x-show="entry.reason" class="px-6 py-4">
                                                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                                                <div class="flex items-start">
                                                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                        </svg>
                                                                    </div>
                                                                    <div class="flex-1">
                                                                        <p class="text-xs text-blue-600 uppercase tracking-wide font-medium mb-1">Change Reason</p>
                                                                        <p class="text-sm text-blue-900" x-text="entry.reason"></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- No reason message -->
                                                        <div x-show="!entry.reason" class="px-6 py-3">
                                                            <p class="text-xs text-gray-500 italic">No reason provided for this status change</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Summary Statistics -->
                                    <div x-show="statusHistory.length > 0" class="mt-8 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg p-6 border border-indigo-200">
                                        <div class="flex items-center mb-4">
                                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h5 class="text-lg font-semibold text-gray-900">History Summary</h5>
                                                <p class="text-sm text-gray-600">Overview of academic status progression</p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <!-- First Status -->
                                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                <div class="text-center">
                                                    <div class="w-8 h-8 mx-auto mb-2 rounded-lg flex items-center justify-center"
                                                         :class="getStatusIconBgClass(statusHistory[statusHistory.length - 1]?.old_status || 'Initial')">
                                                        <span x-text="getStatusIcon(statusHistory[statusHistory.length - 1]?.old_status || 'Initial')"></span>
                                                    </div>
                                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Initial Status</p>
                                                    <p class="text-sm font-semibold text-gray-900" x-text="statusHistory[statusHistory.length - 1]?.old_status || 'Initial'"></p>
                                                </div>
                                            </div>

                                            <!-- Current Status -->
                                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                <div class="text-center">
                                                    <div class="w-8 h-8 mx-auto mb-2 rounded-lg flex items-center justify-center"
                                                         :class="getStatusIconBgClass(statusHistory[0]?.new_status)">
                                                        <span x-text="getStatusIcon(statusHistory[0]?.new_status)"></span>
                                                    </div>
                                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Current Status</p>
                                                    <p class="text-sm font-semibold text-gray-900" x-text="statusHistory[0]?.new_status"></p>
                                                </div>
                                            </div>

                                            <!-- Last Change -->
                                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                <div class="text-center">
                                                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                    </div>
                                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Last Change</p>
                                                    <p class="text-sm font-semibold text-gray-900" x-text="formatDate(statusHistory[0]?.changed_at)"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer matching Student Detail Modal -->
                            <div class="bg-gray-50 px-8 py-4 flex justify-between items-center border-t">
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span x-show="statusHistory.length > 0">
                            Total changes: <span class="font-medium" x-text="statusHistory.length"></span>
                        </span>
                                    <span x-show="statusHistory.length === 0">No status changes recorded</span>
                                </div>

                                <div class="flex space-x-3">
                                    <button @click="loadStatusHistory()"
                                            class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Refresh
                                    </button>
                                    <button @click="closeStatusHistoryModal()"
                                            class="px-6 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function studentManagement() {
        return {
            showQR: false,
            copied: false,
            registrationUrl: '',
            qrCodeInstance: null,

            init() {
                this.registrationUrl = `${window.APP_CONFIG.BASE_URL}/student/register.php`;
            },

            toggleQRCode() {
                this.showQR = !this.showQR;
                if (this.showQR && this.registrationUrl) {
                    this.generateQRCode();
                }
            },

            generateQRCode() {
                const qrContainer = document.getElementById("qrcode");
                qrContainer.innerHTML = ""; // Clear old code
                this.qrCodeInstance = new QRCode(qrContainer, {
                    text: this.registrationUrl,
                    width: 200,
                    height: 200,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            },

            downloadQRCode() {
                if (!this.qrCodeInstance) return;

                // Get the canvas element from the QR code
                const originalCanvas = document.querySelector("#qrcode canvas");
                if (originalCanvas) {
                    // Create a new canvas with padding
                    const paddedCanvas = document.createElement('canvas');
                    const ctx = paddedCanvas.getContext('2d');

                    // Define padding (40px on all sides)
                    const padding = 40;
                    const newWidth = originalCanvas.width + (padding * 2);
                    const newHeight = originalCanvas.height + (padding * 2);

                    paddedCanvas.width = newWidth;
                    paddedCanvas.height = newHeight;

                    // Fill background with white
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, newWidth, newHeight);

                    // Draw the original QR code centered with padding
                    ctx.drawImage(originalCanvas, padding, padding);

                    // Create download link
                    const link = document.createElement('a');
                    link.download = 'student-registration-qr-code.png';
                    link.href = paddedCanvas.toDataURL('image/png');

                    // Trigger download
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    // Fallback: try to get the image element and add padding
                    const img = document.querySelector("#qrcode img");
                    if (img) {
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');

                        // Define padding
                        const padding = 40;
                        const newWidth = img.width + (padding * 2);
                        const newHeight = img.height + (padding * 2);

                        canvas.width = newWidth;
                        canvas.height = newHeight;

                        // Fill background with white
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, newWidth, newHeight);

                        // Draw the image centered with padding
                        ctx.drawImage(img, padding, padding);

                        const link = document.createElement('a');
                        link.download = 'student-registration-qr-code.png';
                        link.href = canvas.toDataURL('image/png');

                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    }
                }
            },

            async copyToClipboard() {
                try {
                    await navigator.clipboard.writeText(this.registrationUrl);
                    this.copied = true;
                } catch (err) {
                    this.$refs.urlInput.select();
                    document.execCommand('copy');
                    this.copied = true;
                }
                setTimeout(() => {
                    this.copied = false;
                }, 2000);
            }
        }
    }
</script>

<script>
    function studentDashboard() {
        return {
            modalActiveTab: 'personal',
            showApprovalForm: false,
            showRejectionForm: false,
            indexGenerationMode: 'auto',
            manualIndexSuffix: '',
            indexValidationErrors: [],
            approvalLoading: false,
            rejectionLoading: false,
            rejectionReason: '',
            previewIndexLoading: false,
            manualIndexValidating: false,

            approvedStudents: [],
            filteredApprovedStudents: [],
            selectedDepartmentFilter: '',
            selectedAcademicStatusFilter: '',
            searchTerm: '',
            approvedStats: {},

            allowResubmission: true,

            studentQRCode: null,
            showStudentQR: false,

            generateStudentQRCode() {
                if (!this.selectedStudent?.nic) return;

                const qrContainer = document.getElementById("studentQRCode");
                if (!qrContainer) return;

                qrContainer.innerHTML = ""; // Clear old code
                this.studentQRCode = new QRCode(qrContainer, {
                    text: this.selectedStudent.nic,
                    width: 150,
                    height: 150,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
            },

            toggleStudentQR() {
                this.showStudentQR = !this.showStudentQR;
                if (this.showStudentQR && this.selectedStudent?.nic) {
                    this.$nextTick(() => {
                        this.generateStudentQRCode();
                    });
                }
            },

            downloadStudentQRCode() {
                if (!this.studentQRCode) return;

                const canvas = document.querySelector("#studentQRCode canvas");
                if (canvas) {
                    const paddedCanvas = document.createElement('canvas');
                    const ctx = paddedCanvas.getContext('2d');

                    const padding = 40;
                    const newWidth = canvas.width + (padding * 2);
                    const newHeight = canvas.height + (padding * 2);

                    paddedCanvas.width = newWidth;
                    paddedCanvas.height = newHeight;

                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, newWidth, newHeight);
                    ctx.drawImage(canvas, padding, padding);

                    const link = document.createElement('a');
                    link.download = `student-${this.selectedStudent.nic}-qr.png`;
                    link.href = paddedCanvas.toDataURL('image/png');

                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            },

            commonRejectionReasons: [
                'Incomplete documentation provided',
                'A/L Z-Score below department threshold',
                'Required subjects not completed at A/L',
                'Incorrect department selection for qualifications',
                'Missing emergency contact information',
                'Duplicate application detected',
                'Photo not suitable for ID card - unclear headshot required'
            ],

            rejectedStudents: [],
            filteredRejectedStudents: [],
            rejectedDepartmentFilter: '',
            rejectedModeFilter: '',
            rejectedSearchTerm: '',

            async switchToRejectedTab() {
                this.activeTab = 'rejected';
                if (this.rejectedStudents.length === 0) {
                    await this.loadRejectedStudents();
                }
            },

            async loadRejectedStudents() {
                try {
                    const res = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/list.php?status=REJECTED`);
                    const json = await res.json();
                    this.rejectedStudents = json.data || [];
                    this.filterRejectedStudents();
                } catch (e) {
                    console.error('Failed to load rejected students:', e);
                    this.rejectedStudents = [];
                    this.filteredRejectedStudents = [];
                }
            },

            filterRejectedStudents() {
                let filtered = this.rejectedStudents;

                if (this.rejectedDepartmentFilter) {
                    filtered = filtered.filter(student =>
                        student.department_id == this.rejectedDepartmentFilter
                    );
                }

                if (this.rejectedModeFilter) {
                    filtered = filtered.filter(student =>
                        student.mode === this.rejectedModeFilter
                    );
                }

                if (this.rejectedSearchTerm && this.rejectedSearchTerm.trim()) {
                    const searchLower = this.rejectedSearchTerm.toLowerCase().trim();
                    filtered = filtered.filter(student =>
                        student.name_with_initials.toLowerCase().includes(searchLower) ||
                        student.full_name.toLowerCase().includes(searchLower) ||
                        student.email.toLowerCase().includes(searchLower) ||
                        student.nic.toLowerCase().includes(searchLower)
                    );
                }

                this.filteredRejectedStudents = filtered;
            },

            viewRejectedStudent(student) {
                this.selectedStudent = student;
                this.modalActiveTab = 'personal';
                this.showModal = true;

                // Prevent body scroll
                document.body.style.overflow = 'hidden';
                document.documentElement.style.overflow = 'hidden';
            },

            confirmDeleteApplication(student) {
                if (confirm(`Are you sure you want to permanently delete the application for ${student.name_with_initials}?\n\nThis action cannot be undone and will remove all application data including documents.`)) {
                    this.deleteApplication(student);
                }
            },

            async deleteApplication(student) {
                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/delete.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            student_id: student.id
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('Application deleted successfully');
                        await this.loadRejectedStudents();
                    } else {
                        alert(data.message || 'Failed to delete application');
                    }
                } catch (error) {
                    console.error('Error deleting application:', error);
                    alert('Error deleting application: ' + error.message);
                }
            },

            preRegForm: {
                department_id: '',
                nic: '',
                email: '',
                mode: 'FULL',
                enrollment_year: new Date().getFullYear(),
                birthday: '',
                gender: ''
            },
            preRegLoading: false,

            // Pre-registration sub-tab management
            preRegSubTab: 'form',
            preRegisteredStudents: [],
            filteredPreRegisteredStudents: [],
            preRegisteredCount: 0,
            preRegDepartmentFilter: '',
            preRegModeFilter: '',
            preRegSearchTerm: '',

            async switchToPreRegList() {
                this.preRegSubTab = 'list';
                await this.loadPreRegisteredStudents();

            },

            async loadPreRegisteredStudents() {
                try {
                    // Use a different endpoint or add query parameters to identify pre-registered students
                    const res = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/list.php?incomplete=1`);
                    const json = await res.json();
                    this.preRegisteredStudents = json.data || [];
                    this.preRegisteredCount = this.preRegisteredStudents.length;
                    this.filterPreRegisteredStudents();
                } catch (e) {
                    console.error('Failed to load pre-registered students:', e);
                    this.preRegisteredStudents = [];
                    this.filteredPreRegisteredStudents = [];
                }
            },

            filterPreRegisteredStudents() {
                let filtered = this.preRegisteredStudents;

                if (this.preRegDepartmentFilter) {
                    filtered = filtered.filter(student =>
                        student.department_id == this.preRegDepartmentFilter
                    );
                }

                if (this.preRegModeFilter) {
                    filtered = filtered.filter(student =>
                        student.mode === this.preRegModeFilter
                    );
                }

                if (this.preRegSearchTerm && this.preRegSearchTerm.trim()) {
                    const searchLower = this.preRegSearchTerm.toLowerCase().trim();
                    filtered = filtered.filter(student =>
                        student.email.toLowerCase().includes(searchLower) ||
                        student.nic.toLowerCase().includes(searchLower)
                    );
                }

                this.filteredPreRegisteredStudents = filtered;
            },

            async resendInvitation(student) {
                if (!confirm(`Resend invitation email to ${student.email}?`)) {
                    return;
                }

                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/resend_invitation.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ student_id: student.id })
                    });

                    const data = await response.json();
                    if (data.success) {
                        alert('Invitation email sent successfully!');
                    } else {
                        alert(data.message || 'Failed to send invitation');
                    }
                } catch (error) {
                    console.error('Error sending invitation:', error);
                    alert('Error sending invitation');
                }
            },

            async deletePreRegistration(student) {
                if (!confirm(`Delete pre-registration for ${student.email}?\n\nThis will permanently remove this record.`)) {
                    return;
                }

                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/delete_prereg.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ student_id: student.id })
                    });

                    const data = await response.json();
                    if (data.success) {
                        alert('Pre-registration deleted successfully');
                        await this.loadPreRegisteredStudents();
                    } else {
                        alert(data.message || 'Failed to delete pre-registration');
                    }
                } catch (error) {
                    console.error('Error deleting pre-registration:', error);
                    alert('Error deleting pre-registration');
                }
            },

            parseNICData() {
                const nic = this.preRegForm.nic.trim();
                if (!nic) {
                    // Clear parsed data if NIC is empty
                    this.preRegForm.birthday = '';
                    this.preRegForm.gender = '';
                    return;
                }

                try {
                    let year, dayOfYear, gender;

                    if (nic.length === 10 && /^\d{9}[VXvx]$/.test(nic)) { // Old format
                        year = parseInt(nic.substring(0, 2));
                        year += (year > 50) ? 1900 : 2000;
                        dayOfYear = parseInt(nic.substring(2, 5));

                        if (dayOfYear > 500) {
                            gender = 'F';  // Changed from 'Female' to 'F'
                            dayOfYear -= 500;
                        } else {
                            gender = 'M';  // Changed from 'Male' to 'M'
                        }
                    } else if (nic.length === 12 && /^\d{12}$/.test(nic)) { // New format
                        year = parseInt(nic.substring(0, 4));
                        dayOfYear = parseInt(nic.substring(4, 7));

                        if (dayOfYear > 500) {
                            gender = 'F';  // Changed from 'Female' to 'F'
                            dayOfYear -= 500;
                        } else {
                            gender = 'M';  // Changed from 'Male' to 'M'
                        }
                    } else {
                        // Invalid NIC format
                        this.preRegForm.birthday = '';
                        this.preRegForm.gender = '';
                        return;
                    }

                    const birthday = this.calculateBirthdayFromDayOfYear(year, dayOfYear);

                    this.preRegForm.birthday = birthday;
                    this.preRegForm.gender = gender;

                    // Debug log
                    console.log('Parsed NIC data:', {
                        nic: nic,
                        birthday: birthday,
                        gender: gender
                    });

                } catch (error) {
                    console.error('Error parsing NIC:', error);
                    this.preRegForm.birthday = '';
                    this.preRegForm.gender = '';
                }
            },

            calculateBirthdayFromDayOfYear(year, dayOfYear) {
                const date = new Date(year, 0);
                date.setDate(dayOfYear);
                return date.toISOString().split('T')[0];
            },

            async submitPreRegistration() {
                if (!this.preRegForm.department_id || !this.preRegForm.nic || !this.preRegForm.email) {
                    alert('Please fill in all required fields');
                    return;
                }

                this.preRegLoading = true;

                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/pre_enroll.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(this.preRegForm)
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert(data.message);
                        this.resetPreRegForm();
                    } else {
                        alert(data.error || 'Registration failed');
                    }
                } catch (error) {
                    console.error('Pre-registration error:', error);
                    alert('Error during registration. Please try again.');
                } finally {
                    this.preRegLoading = false;
                }
            },

            // NEW: Add email suggestion properties
            emailSuggestion: '',
            dismissedEmailSuggestion: '',

            // NEW: Email domain suggestions mapping
            emailDomainSuggestions: {
                // Gmail variations
                'gmai.com': 'gmail.com',
                'gmial.com': 'gmail.com',
                'gmail.co': 'gmail.com',
                'gmil.com': 'gmail.com',
                'gmaill.com': 'gmail.com',
                'gmail.con': 'gmail.com',
                'gmail.cm': 'gmail.com',
                'gmali.com': 'gmail.com',
                'gmal.com': 'gmail.com',
                'gamil.com': 'gmail.com',
                'gmai;.com': 'gmail.com',
                'gmail.cmo': 'gmail.com',
                'gmailcom': 'gmail.com',

                // Yahoo variations
                'yahoo.co': 'yahoo.com',
                'yaho.com': 'yahoo.com',
                'yahooo.com': 'yahoo.com',
                'yahoo.con': 'yahoo.com',
                'yahoo.cm': 'yahoo.com',
                'yhoo.com': 'yahoo.com',
                'ymail.co': 'ymail.com',

                // Outlook/Hotmail variations
                'hotmai.com': 'hotmail.com',
                'hotmial.com': 'hotmail.com',
                'hotmail.co': 'hotmail.com',
                'outlook.co': 'outlook.com',
                'outlok.com': 'outlook.com',
                'outloo.com': 'outlook.com',

                // Other common domains
                'aol.co': 'aol.com',
                'icloud.co': 'icloud.com',
                'protonmai.com': 'protonmail.com',
                'protonmal.com': 'protonmail.com',
                'live.co': 'live.com',
                'msn.co': 'msn.com'
            },

            // NEW: Add these methods to your existing Alpine.js methods

            checkEmailTypos() {
                // Reset suggestion if this email was previously dismissed
                if (this.preRegForm.email === this.dismissedEmailSuggestion) {
                    this.emailSuggestion = '';
                    return;
                }

                if (!this.preRegForm.email || !this.preRegForm.email.includes('@')) {
                    this.emailSuggestion = '';
                    return;
                }

                const emailParts = this.preRegForm.email.split('@');
                if (emailParts.length !== 2) {
                    this.emailSuggestion = '';
                    return;
                }

                const domain = emailParts[1].toLowerCase().trim();
                const username = emailParts[0].trim();

                // Check if domain has a direct suggestion
                if (this.emailDomainSuggestions[domain]) {
                    const suggestedDomain = this.emailDomainSuggestions[domain];
                    this.emailSuggestion = `${username}@${suggestedDomain}`;
                } else {
                    // Only check for similar domains if the domain looks like a typo of a known domain
                    const suggestion = this.findSimilarEmailDomain(domain);
                    if (suggestion) {
                        this.emailSuggestion = `${username}@${suggestion}`;
                    } else {
                        this.emailSuggestion = '';
                    }
                }
            },

            findSimilarEmailDomain(domain) {
                // List of valid domains that should NEVER trigger suggestions
                const validDomains = [
                    'gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com',
                    'aol.com', 'icloud.com', 'live.com', 'ymail.com',
                    'protonmail.com', 'msn.com', 'googlemail.com', 'rocketmail.com',
                    'mail.com', 'zoho.com', 'fastmail.com', 'tutanota.com',
                    'proton.me', 'hey.com', 'mail.ru', 'yandex.com'
                ];

                // If the domain is already valid, don't suggest anything
                if (validDomains.includes(domain)) {
                    return null;
                }

                const popularDomains = [
                    'gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com',
                    'aol.com', 'icloud.com', 'live.com', 'ymail.com',
                    'protonmail.com', 'msn.com'
                ];

                const threshold = 2; // Maximum allowed character differences
                const minSimilarityRatio = 0.7; // Increased to 70% similarity

                for (let popularDomain of popularDomains) {
                    const distance = this.levenshteinDistance(domain, popularDomain);
                    const maxLength = Math.max(domain.length, popularDomain.length);
                    const similarityRatio = 1 - (distance / maxLength);

                    // Only suggest if:
                    // 1. Distance is within threshold
                    // 2. Similarity ratio is high enough
                    // 3. Domain lengths are reasonably close
                    // 4. The domain is not identical
                    // 5. The domain is not in our valid domains list
                    if (distance <= threshold &&
                        distance > 0 &&
                        domain !== popularDomain &&
                        similarityRatio >= minSimilarityRatio &&
                        !validDomains.includes(domain)) {

                        const lengthDiff = Math.abs(domain.length - popularDomain.length);
                        if (lengthDiff <= 2) { // Made more restrictive
                            return popularDomain;
                        }
                    }
                }
                return null;
            },

            levenshteinDistance(str1, str2) {
                const matrix = [];

                // Initialize matrix
                for (let i = 0; i <= str2.length; i++) {
                    matrix[i] = [i];
                }

                for (let j = 0; j <= str1.length; j++) {
                    matrix[0][j] = j;
                }

                // Fill matrix
                for (let i = 1; i <= str2.length; i++) {
                    for (let j = 1; j <= str1.length; j++) {
                        if (str2.charAt(i - 1) === str1.charAt(j - 1)) {
                            matrix[i][j] = matrix[i - 1][j - 1];
                        } else {
                            matrix[i][j] = Math.min(
                                matrix[i - 1][j - 1] + 1, // substitution
                                matrix[i][j - 1] + 1,     // insertion
                                matrix[i - 1][j] + 1      // deletion
                            );
                        }
                    }
                }

                return matrix[str2.length][str1.length];
            },

            acceptEmailSuggestion() {
                this.preRegForm.email = this.emailSuggestion;
                this.emailSuggestion = '';
                this.dismissedEmailSuggestion = '';
                // Trigger any additional validation if needed
                this.checkEmailTypos();
            },

            dismissEmailSuggestion() {
                this.dismissedEmailSuggestion = this.preRegForm.email;
                this.emailSuggestion = '';
            },

            resetPreRegForm() {
                this.preRegForm = {
                    department_id: '',
                    nic: '',
                    email: '',
                    mode: 'FULL',
                    enrollment_year: new Date().getFullYear(),
                    birthday: '',
                    gender: ''
                };
                // Reset email suggestion states
                this.emailSuggestion = '';
                this.dismissedEmailSuggestion = '';
            },

            // === NEW: Academic Status History Modal Properties ===
            showStatusHistoryModal: false,
            statusHistoryLoading: false,
            statusHistoryError: null,
            statusHistoryStudent: null,
            statusHistory: [],

            // === NEW: Academic Status History Modal Methods ===

            /**
             * Show academic status history modal
             */
            async viewAcademicStatusHistory(student) {
                this.statusHistoryStudent = student;
                this.showStatusHistoryModal = true;
                this.statusHistoryError = null;
                this.statusHistory = [];

                // Prevent body scroll
                document.body.style.overflow = 'hidden';
                document.documentElement.style.overflow = 'hidden';

                await this.loadStatusHistory();
            },

            /**
             * Get background CSS classes for status icon containers
             */
            getStatusIconBgClass(status) {
                const statusIconBgClasses = {
                    'Active': 'bg-green-100',
                    'Inactive': 'bg-red-100',
                    'Suspended': 'bg-yellow-100',
                    'Graduated': 'bg-blue-100',
                    'Transferred': 'bg-purple-100',
                    'Withdrawn': 'bg-gray-100',
                    'Initial': 'bg-gray-100'
                };

                return statusIconBgClasses[status] || 'bg-gray-100';
            },

            /**
             * Close academic status history modal
             */
            closeStatusHistoryModal() {
                this.showStatusHistoryModal = false;
                this.statusHistoryStudent = null;
                this.statusHistory = [];
                this.statusHistoryError = null;

                // Restore body scroll
                document.body.style.overflow = '';
                document.documentElement.style.overflow = '';
            },

            /**
             * Load academic status history from API
             */
            async loadStatusHistory() {
                if (!this.statusHistoryStudent?.id) return;

                this.statusHistoryLoading = true;
                this.statusHistoryError = null;

                try {
                    const response = await fetch(
                        `${window.APP_CONFIG.API_BASE_URL}/students/academic_status_history.php?student_id=${this.statusHistoryStudent.id}`
                    );

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();

                    if (data.success) {
                        this.statusHistory = data.data || [];
                        // Update student data if provided
                        if (data.student) {
                            this.statusHistoryStudent = { ...this.statusHistoryStudent, ...data.student };
                        }
                    } else {
                        throw new Error(data.message || 'Failed to load academic status history');
                    }
                } catch (error) {
                    console.error('Error loading academic status history:', error);
                    this.statusHistoryError = error.message || 'Error loading academic status history';
                } finally {
                    this.statusHistoryLoading = false;
                }
            },

            /**
             * Format date for display
             */
            formatStatusHistoryDate(dateString) {
                if (!dateString) return '';
                try {
                    return new Date(dateString).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                } catch (error) {
                    return dateString;
                }
            },

            /**
             * Get CSS classes for status badges
             */
            getStatusHistoryBadgeClass(status) {
                const statusClasses = {
                    'Active': 'bg-green-100 text-green-800',
                    'Inactive': 'bg-red-100 text-red-800',
                    'Suspended': 'bg-yellow-100 text-yellow-800',
                    'Graduated': 'bg-blue-100 text-blue-800',
                    'Transferred': 'bg-purple-100 text-purple-800',
                    'Withdrawn': 'bg-gray-100 text-gray-800',
                    'Initial': 'bg-gray-100 text-gray-600'
                };

                return statusClasses[status] || 'bg-gray-100 text-gray-800';
            },

            getStatusIcon(status) {
                const icons = {
                    'Initial': '🎓',
                    'Active': '✅',
                    'Inactive': '⚠️',
                    'Suspended': '🚫',
                    'Graduated': '🎉',
                    'Withdrawn': '❌'
                };
                return icons[status] || '📋';
            },

            async loadApprovedStudents() {
                try {
                    const res = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/list.php?status=APPROVED`);
                    const json = await res.json();
                    this.approvedStudents = json.data || [];
                    this.filterApprovedStudents();
                } catch (e) {
                    console.error('Failed to load approved students:', e);
                    this.approvedStudents = [];
                    this.filteredApprovedStudents = [];
                }
            },

            async loadApprovedStudentsWithStats() {
                try {
                    const res = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/list.php?status=APPROVED&include_stats=true`);
                    const json = await res.json();
                    this.approvedStudents = json.data || [];
                    this.approvedStats = json.academic_status_stats || {};
                    this.filterApprovedStudents();
                } catch (e) {
                    console.error('Failed to load approved students:', e);
                    this.approvedStudents = [];
                    this.filteredApprovedStudents = [];
                }
            },

            filterApprovedStudents() {
                let filtered = this.approvedStudents;

                // Filter by department
                if (this.selectedDepartmentFilter) {
                    filtered = filtered.filter(student =>
                        student.department_id == this.selectedDepartmentFilter
                    );
                }

                // Filter by academic status
                if (this.selectedAcademicStatusFilter) {
                    filtered = filtered.filter(student =>
                        student.academic_status === this.selectedAcademicStatusFilter
                    );
                }

                // Filter by search term
                if (this.searchTerm && this.searchTerm.trim()) {
                    const searchLower = this.searchTerm.toLowerCase().trim();
                    filtered = filtered.filter(student =>
                        student.name_with_initials.toLowerCase().includes(searchLower) ||
                        student.full_name.toLowerCase().includes(searchLower) ||
                        student.email.toLowerCase().includes(searchLower) ||
                        student.index_no.toLowerCase().includes(searchLower)
                    );
                }

                this.filteredApprovedStudents = filtered;
            },

            async switchToApprovedTab() {
                this.activeTab = 'approved';
                if (this.approvedStudents.length === 0) {
                    await this.loadApprovedStudentsWithStats();
                }
            },

            viewApprovedStudent(student) {
                this.selectedStudent = student;
                this.modalActiveTab = 'personal';
                this.showModal = true;

                // Prevent body scroll when modal opens
                document.body.style.overflow = 'hidden';
                document.documentElement.style.overflow = 'hidden';
            },

            closeModal() {
                this.showModal = false;
                this.modalActiveTab = 'personal';
                this.resetApprovalForm();

                // Add QR cleanup
                this.showStudentQR = false;
                this.studentQRCode = null;

                // Restore body scroll when modal closes
                document.body.style.overflow = '';
                document.documentElement.style.overflow = '';
            },

            getStatusBadgeClass(status) {
                switch(status) {
                    case 'PENDING_APPROVAL':
                        return 'bg-yellow-100 bg-opacity-90 text-yellow-800';
                    case 'APPROVED':
                        return 'bg-green-100 bg-opacity-90 text-green-800';
                    case 'REJECTED':
                        return 'bg-red-100 bg-opacity-90 text-red-800';
                    default:
                        return 'bg-gray-100 bg-opacity-90 text-gray-800';
                }
            },

            getStatusText(status) {
                switch(status) {
                    case 'PENDING_APPROVAL':
                        return 'Pending Review';
                    case 'APPROVED':
                        return 'Approved';
                    case 'REJECTED':
                        return 'Rejected';
                    default:
                        return 'Unknown';
                }
            },

            generateIndexPrefix() {
                if (!this.selectedStudent) return '';

                const dept = this.departments.find(d => d.id == this.selectedStudent.department_id);
                const deptCode = dept?.code || 'DEPT';
                const year = this.selectedStudent.enrollment_year || new Date().getFullYear();
                const mode = this.selectedStudent.mode === 'FULL' ? 'F' : 'P';

                return `KUR/${deptCode}/${year}/${mode}/`;
            },

            // Enhanced auto preview with live updates
            async getAutoIndexPreview() {
                if (!this.selectedStudent?.enrollment_year) return 'Set enrollment year first';

                this.previewIndexLoading = true;

                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/preview_index.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            department_id: this.selectedStudent.department_id,
                            mode: this.selectedStudent.mode,
                            enrollment_year: this.selectedStudent.enrollment_year
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        return data.preview_index_number;
                    } else {
                        console.error('Preview error:', data.error);
                        return this.generateIndexPrefix() + 'XXXX';
                    }
                } catch (error) {
                    console.error('Preview fetch error:', error);
                    return this.generateIndexPrefix() + 'XXXX';
                } finally {
                    this.previewIndexLoading = false;
                }
            },

            // Watch for enrollment year changes to update preview
            async updateAutoPreview() {
                if (this.indexGenerationMode === 'auto' && this.selectedStudent?.enrollment_year) {
                    // Trigger preview update
                    this.$nextTick(() => {
                        // The preview will automatically update due to Alpine.js reactivity
                    });
                }
            },

            formatIndexSuffix() {
                if (this.manualIndexSuffix && /^\d+$/.test(this.manualIndexSuffix)) {
                    const num = parseInt(this.manualIndexSuffix);
                    if (num >= 0 && num <= 9999) {
                        this.manualIndexSuffix = num.toString().padStart(4, '0');
                    }
                }
                this.validateManualIndex();
            },

            // Enhanced manual index validation with server-side validation
            async validateManualIndex() {
                this.indexValidationErrors = [];

                if (!this.manualIndexSuffix) {
                    this.indexValidationErrors.push('Suffix is required');
                    return;
                }

                if (!/^\d{4}$/.test(this.manualIndexSuffix)) {
                    this.indexValidationErrors.push('Suffix must be exactly 4 digits');
                    return;
                }

                const num = parseInt(this.manualIndexSuffix);
                if (num < 1) {
                    this.indexValidationErrors.push('Suffix cannot be 0000');
                    return;
                }

                // Server-side validation
                const fullIndexNumber = this.getManualIndexNumber();
                if (fullIndexNumber && this.selectedStudent?.id) {
                    await this.validateManualIndexOnServer(fullIndexNumber);
                }
            },

            // Server-side validation for manual index
            async validateManualIndexOnServer(indexNumber) {
                if (this.manualIndexValidating) return; // Prevent multiple concurrent validations

                this.manualIndexValidating = true;

                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/validate_index.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            index_number: indexNumber,
                            student_id: this.selectedStudent.id
                        })
                    });

                    const validation = await response.json();

                    if (validation.valid) {
                        // Clear any previous errors from server validation
                        this.indexValidationErrors = this.indexValidationErrors.filter(error =>
                            !error.includes('already assigned') &&
                            !error.includes('Department code') &&
                            !error.includes('Mode')
                        );

                        // Add any warnings as informational messages (not blocking)
                        if (validation.warnings && validation.warnings.length > 0) {
                            // You could show warnings in a separate section if needed
                            console.log('Manual index warnings:', validation.warnings);
                        }
                    } else {
                        // Add server validation errors
                        if (validation.errors) {
                            validation.errors.forEach(error => {
                                if (!this.indexValidationErrors.includes(error)) {
                                    this.indexValidationErrors.push(error);
                                }
                            });
                        }
                    }
                } catch (error) {
                    console.error('Server validation error:', error);
                    this.indexValidationErrors.push('Unable to validate with server. Please try again.');
                } finally {
                    this.manualIndexValidating = false;
                }
            },

            getManualIndexNumber() {
                if (!this.manualIndexSuffix) return '';
                return this.generateIndexPrefix() + this.manualIndexSuffix;
            },

            resetApprovalForm() {
                this.showApprovalForm = false;
                this.indexGenerationMode = 'auto';
                this.manualIndexSuffix = '';
                this.indexValidationErrors = [];
                this.rejectionReason = '';
                this.allowResubmission = false;
                this.showRejectionForm = false;
            },

            async processApproval() {
                if (!this.selectedStudent.enrollment_year) {
                    alert('Please enter enrollment year');
                    return;
                }

                if (this.indexGenerationMode === 'manual') {
                    if (this.indexValidationErrors.length > 0) {
                        alert('Please fix index number validation errors:\n' + this.indexValidationErrors.join('\n'));
                        return;
                    }
                    if (!this.manualIndexSuffix) {
                        alert('Please enter manual index suffix');
                        return;
                    }
                }

                this.approvalLoading = true;

                try {
                    const requestBody = {
                        student_id: this.selectedStudent.id,
                        enrollment_year: this.selectedStudent.enrollment_year
                    };

                    if (this.indexGenerationMode === 'manual') {
                        requestBody.manual_index_number = this.getManualIndexNumber();
                    }

                    const res = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/approve.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(requestBody)
                    });

                    const data = await res.json();

                    if (data.success) {
                        let message = 'Student approved successfully!';
                        if (data.data.index_number) {
                            message += `\n${this.indexGenerationMode === 'manual' ? 'Assigned' : 'Generated'} Index Number: ${data.data.index_number}`;
                        }
                        if (data.data.email_sent) {
                            message += '\nApproval email sent successfully.';
                        } else {
                            message += '\nNote: Email sending failed, but approval was successful.';
                        }

                        alert(message);
                        this.resetApprovalForm();
                        this.closeModal();
                        await this.loadPendingRegistrations();
                        await this.loadStats();
                    } else {
                        alert(data.message || 'Failed to approve student');
                    }
                } catch (e) {
                    console.error('Error approving student:', e);
                    alert('Error approving student: ' + e.message);
                } finally {
                    this.approvalLoading = false;
                }
            },

            async processRejection() {
                if (!this.rejectionReason.trim()) {
                    alert('Please provide a reason for rejection');
                    return;
                }

                this.rejectionLoading = true;

                try {
                    const res = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/reject.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            student_id: this.selectedStudent.id,
                            reason: this.rejectionReason.trim(),
                            allow_resubmission: this.allowResubmission
                        })
                    });

                    const data = await res.json();

                    if (data.success) {
                        let message = 'Student registration rejected successfully.';
                        if (this.allowResubmission) {
                            message += ' Resubmission link sent to student.';
                        } else {
                            message += ' Notification email sent.';
                        }
                        alert(message);

                        this.resetApprovalForm();
                        this.showModal = false;
                        await this.loadPendingRegistrations();
                        await this.loadStats();
                    } else {
                        alert(data.message || 'Failed to reject student');
                    }
                } catch (e) {
                    console.error('Error rejecting student:', e);
                    alert('Error rejecting student: ' + e.message);
                } finally {
                    this.rejectionLoading = false;
                }
            },

            activeTab: 'preregister',
            departments: [],
            pendingStudents: [],
            pendingCount: 0,
            stats: {},
            showModal: false,
            selectedStudent: { enrollment_year: '' },

            // Pending filters and search
            filteredPendingStudents: [],
            pendingDepartmentFilter: '',
            pendingModeFilter: '',
            pendingSearchTerm: '',

            filterPendingStudents() {
                let filtered = this.pendingStudents;

                // Filter by department
                if (this.pendingDepartmentFilter) {
                    filtered = filtered.filter(student =>
                        student.department_id == this.pendingDepartmentFilter
                    );
                }

                // Filter by mode
                if (this.pendingModeFilter) {
                    filtered = filtered.filter(student =>
                        student.mode === this.pendingModeFilter
                    );
                }

                // Filter by search term (name, email, NIC)
                if (this.pendingSearchTerm && this.pendingSearchTerm.trim()) {
                    const searchLower = this.pendingSearchTerm.toLowerCase().trim();
                    filtered = filtered.filter(student =>
                        student.name_with_initials.toLowerCase().includes(searchLower) ||
                        student.full_name.toLowerCase().includes(searchLower) ||
                        student.email.toLowerCase().includes(searchLower) ||
                        student.nic.toLowerCase().includes(searchLower)
                    );
                }

                this.filteredPendingStudents = filtered;
            },

            async init() {
                await this.loadDepartments();
                await this.loadPendingRegistrations();
                await this.loadStats();
            },

            async loadDepartments() {
                try {
                    const res = await fetch(`${window.APP_CONFIG.API_BASE_URL}/departments.php`);
                    const json = await res.json();
                    this.departments = json.data || [];
                } catch (e) {
                    console.error('Failed to load departments:', e);
                }
            },

            async loadPendingRegistrations() {
                try {
                    const res = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/pending.php`);
                    const json = await res.json();
                    this.pendingStudents = json.data || [];
                    this.pendingCount = this.pendingStudents.length;

                    // Apply filters after loading data
                    this.filterPendingStudents();
                } catch (e) {
                    console.error('Failed to load pending registrations:', e);
                    this.pendingStudents = [];
                    this.filteredPendingStudents = [];
                }
            },

            async loadStats() {
                try {
                    const res = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/stats.php`);
                    const json = await res.json();
                    this.stats = json.data || {};
                } catch (e) {
                    console.error('Failed to load stats:', e);
                }
            },

            viewPendingStudent(student) {
                this.selectedStudent = student;
                this.modalActiveTab = 'personal';
                this.showModal = true;
                this.allowResubmission = true;

                // Prevent body scroll when modal opens
                document.body.style.overflow = 'hidden';
                document.documentElement.style.overflow = 'hidden';
            },

            getDepartmentName(deptId) {
                const dept = this.departments.find(d => d.id == deptId);
                return dept ? dept.name : 'Unknown';
            },

            formatDate(dateStr) {
                if (!dateStr) return '';
                return new Date(dateStr).toLocaleDateString();
            },

            getTimeAgo(dateStr) {
                if (!dateStr) return '';
                const now = new Date();
                const date = new Date(dateStr);
                const diffMs = now - date;
                const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
                const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
                const diffMinutes = Math.floor(diffMs / (1000 * 60));

                if (diffDays > 0) {
                    return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
                } else if (diffHours > 0) {
                    return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
                } else if (diffMinutes > 0) {
                    return `${diffMinutes} minute${diffMinutes > 1 ? 's' : ''} ago`;
                } else {
                    return 'Just now';
                }
            },

            // Edit-related state
            showEditModal: false,
            editingStudent: null,
            editForm: {
                name_with_initials: '',
                full_name: '',
                nic: '',
                gender: '',
                birthday: '',
                email: '',
                department_id: '',
                enrollment_year: '',
                mode: '',
                academic_status: '',
                status_change_reason: '',
                contact_no: '',
                address: '',
                dependant_name: '',
                dependant_prof: '',
                dependant_phone: '',
                dependant_addr: ''
            },
            originalAcademicStatus: '',
            editLoading: false,

            // Index change detection
            showIndexWarning: false,
            originalDepartmentId: '',
            originalEnrollmentYear: '',
            editIndexGenerationMode: 'auto',
            editManualIndexSuffix: '',
            editIndexValidationErrors: [],
            editManualIndexValidating: false,

            editPreviewIndexLoading: false,
            editIndexPreviewData: {},
            editIndexValidationWarnings: [],
            editIndexValidationSuggestions: [],

            /**
             * Get preview of next auto-generated index number for edit
             */
            async getEditAutoIndexPreview() {
                if (!this.editForm.department_id || !this.editForm.mode || !this.editForm.enrollment_year) {
                    return 'Please fill in department, mode, and enrollment year';
                }

                this.editPreviewIndexLoading = true;

                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/preview_index.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            department_id: this.editForm.department_id,
                            mode: this.editForm.mode,
                            enrollment_year: this.editForm.enrollment_year
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.editIndexPreviewData = data.info || {};
                        return data.preview_index_number;
                    } else {
                        console.error('Edit preview error:', data.error);
                        return this.getNewIndexPattern() + 'XXXX';
                    }
                } catch (error) {
                    console.error('Edit preview fetch error:', error);
                    return this.getNewIndexPattern() + 'XXXX';
                } finally {
                    this.editPreviewIndexLoading = false;
                }
            },

            /**
             * Open edit modal for approved student
             */
            editApprovedStudent(student) {
                this.editingStudent = { ...student };

                // Store original values for comparison
                this.originalAcademicStatus = String(student.academic_status || '').trim();
                this.originalDepartmentId = student.department_id;
                this.originalEnrollmentYear = student.enrollment_year;

                // Populate edit form
                this.editForm = {
                    name_with_initials: student.name_with_initials || '',
                    full_name: student.full_name || '',
                    nic: student.nic || '',
                    gender: student.gender || '',
                    birthday: student.birthday || '',
                    email: student.email || '',
                    department_id: student.department_id || '',
                    enrollment_year: student.enrollment_year || '',
                    mode: student.mode || '',
                    academic_status: String(student.academic_status || '').trim(),
                    status_change_reason: '',
                    contact_no: student.contact_no || '',
                    address: student.address || '',
                    dependant_name: student.dependant_name || '',
                    dependant_prof: student.dependant_prof || '',
                    dependant_phone: student.dependant_phone || '',
                    dependant_addr: student.dependant_addr || ''
                };

                this.showEditModal = true;
                this.resetEditIndexState();

                // Prevent body scroll
                document.body.style.overflow = 'hidden';
                document.documentElement.style.overflow = 'hidden';
            },

            /**
             * Close edit modal
             */
            closeEditModal() {
                this.showEditModal = false;
                this.editingStudent = null;
                this.resetEditIndexState();

                // Restore body scroll
                document.body.style.overflow = '';
                document.documentElement.style.overflow = '';
            },

            /**
             * Reset edit index-related state
             */
            resetEditIndexState() {
                this.showIndexWarning = false;
                this.editIndexGenerationMode = 'auto';
                this.editManualIndexSuffix = '';
                this.editIndexValidationErrors = [];
                this.editIndexValidationWarnings = [];
                this.editIndexValidationSuggestions = [];
                this.editManualIndexValidating = false;
                this.editPreviewIndexLoading = false;
                this.editIndexPreviewData = {};
            },

            /**
             * Enhanced index change detection with preview update
             */
            async checkIndexChange() {
                const deptChanged = this.editForm.department_id != this.originalDepartmentId;
                const yearChanged = this.editForm.enrollment_year != this.originalEnrollmentYear;

                this.showIndexWarning = deptChanged || yearChanged;

                if (this.showIndexWarning) {
                    // Reset manual index when pattern changes
                    this.editManualIndexSuffix = '';
                    this.editIndexValidationErrors = [];
                    this.editIndexValidationWarnings = [];
                    this.editIndexValidationSuggestions = [];

                    // Update preview if in auto mode
                    if (this.editIndexGenerationMode === 'auto') {
                        await this.$nextTick(); // Wait for DOM updates
                    }
                }
            },

            /**
             * Get new index pattern for preview
             */
            getNewIndexPattern() {
                if (!this.editForm.department_id || !this.editForm.enrollment_year || !this.editForm.mode) {
                    return '';
                }

                const dept = this.departments.find(d => d.id == this.editForm.department_id);
                const deptCode = dept?.code || 'DEPT';
                const year = this.editForm.enrollment_year;
                const mode = this.editForm.mode === 'FULL' ? 'F' : 'P';

                return `KUR/${deptCode}/${year}/${mode}/`;
            },

            /**
             * Get new index prefix for manual entry
             */
            getNewIndexPrefix() {
                return this.getNewIndexPattern();
            },

            /**
             * Format edit manual index suffix with proper padding
             */
            formatEditIndexSuffix() {
                if (this.editManualIndexSuffix && /^\d+$/.test(this.editManualIndexSuffix)) {
                    const num = parseInt(this.editManualIndexSuffix);
                    if (num >= 1 && num <= 9999) {
                        this.editManualIndexSuffix = num.toString().padStart(4, '0');
                    }
                }
                this.validateEditManualIndex();
            },

            /**
             * Enhanced manual index validation for edit with real-time server validation
             */
            async validateEditManualIndex() {
                this.editIndexValidationErrors = [];

                if (!this.editManualIndexSuffix) {
                    this.editIndexValidationErrors.push('Suffix is required');
                    return;
                }

                if (!/^\d{1,4}$/.test(this.editManualIndexSuffix)) {
                    this.editIndexValidationErrors.push('Suffix must be 1-4 digits');
                    return;
                }

                const num = parseInt(this.editManualIndexSuffix);
                if (num < 1) {
                    this.editIndexValidationErrors.push('Suffix cannot be 0000');
                    return;
                }

                if (num > 9999) {
                    this.editIndexValidationErrors.push('Suffix cannot exceed 9999');
                    return;
                }

                // Server-side validation
                const fullIndexNumber = this.getEditManualIndexNumber();
                if (fullIndexNumber && this.editingStudent?.id) {
                    await this.validateEditIndexOnServer(fullIndexNumber);
                }
            },

            /**
             * Server-side validation for edit manual index
             */
            async validateEditIndexOnServer(indexNumber) {
                if (this.editManualIndexValidating) return; // Prevent concurrent validations

                this.editManualIndexValidating = true;

                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/validate_index.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            index_number: indexNumber,
                            student_id: this.editingStudent.id
                        })
                    });

                    if (!response.ok) {
                        throw new Error(`Server error: ${response.status}`);
                    }

                    const validation = await response.json();

                    // Clear previous server validation errors
                    this.editIndexValidationErrors = this.editIndexValidationErrors.filter(error =>
                        !error.includes('already assigned') &&
                        !error.includes('Department code') &&
                        !error.includes('Mode') &&
                        !error.includes('does not exist') &&
                        !error.includes('Server error')
                    );

                    if (validation.valid) {
                        // Store validation warnings and suggestions
                        this.editIndexValidationWarnings = validation.warnings || [];
                        this.editIndexValidationSuggestions = validation.suggestions || [];
                    } else {
                        // Add server validation errors
                        if (validation.errors && Array.isArray(validation.errors)) {
                            validation.errors.forEach(error => {
                                if (!this.editIndexValidationErrors.includes(error)) {
                                    this.editIndexValidationErrors.push(error);
                                }
                            });
                        }

                        // Store suggestions for user guidance
                        this.editIndexValidationSuggestions = validation.suggestions || [];
                    }

                } catch (error) {
                    console.error('Edit index server validation error:', error);
                    this.editIndexValidationErrors.push(`Server validation error: ${error.message}`);
                    this.editIndexValidationWarnings = [];
                    this.editIndexValidationSuggestions = [];
                } finally {
                    this.editManualIndexValidating = false;
                }
            },

            /**
             * Get complete manual index number for edit
             */
            getEditManualIndexNumber() {
                if (!this.editManualIndexSuffix) return '';
                return this.getNewIndexPrefix() + this.editManualIndexSuffix;
            },

            /**
             * Server-side validation for edit manual index
             */
            async validateEditManualIndexOnServer(indexNumber) {
                if (this.editManualIndexValidating) return;

                this.editManualIndexValidating = true;

                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/validate_index.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            index_number: indexNumber,
                            student_id: this.editingStudent.id
                        })
                    });

                    const validation = await response.json();

                    if (validation.valid) {
                        // Clear server validation errors
                        this.editIndexValidationErrors = this.editIndexValidationErrors.filter(error =>
                            !error.includes('already assigned') &&
                            !error.includes('Department code') &&
                            !error.includes('Mode')
                        );
                    } else {
                        // Add server validation errors
                        if (validation.errors) {
                            validation.errors.forEach(error => {
                                if (!this.editIndexValidationErrors.includes(error)) {
                                    this.editIndexValidationErrors.push(error);
                                }
                            });
                        }
                    }
                } catch (error) {
                    console.error('Server validation error:', error);
                    this.editIndexValidationErrors.push('Unable to validate with server. Please try again.');
                } finally {
                    this.editManualIndexValidating = false;
                }
            },

            /**
             * Update student information
             */
            async updateStudent() {
                if (!this.editingStudent) return;

                // Validate required fields
                if (!this.editForm.name_with_initials || !this.editForm.full_name || !this.editForm.email) {
                    alert('Please fill in all required fields');
                    return;
                }

                // Fix: Convert both values to strings for proper comparison
                const currentStatus = String(this.editForm.academic_status).trim();
                const originalStatus = String(this.originalAcademicStatus).trim();
                const statusChanged = currentStatus !== originalStatus;

                // Debug: Log the comparison (remove this after fixing)
                console.log('Status comparison:', {
                    current: currentStatus,
                    original: originalStatus,
                    changed: statusChanged,
                    reason: this.editForm.status_change_reason
                });

                // Validate academic status change reason - only if status actually changed
                if (statusChanged && (!this.editForm.status_change_reason || !this.editForm.status_change_reason.trim())) {
                    alert('Please provide a reason for the academic status change');
                    return;
                }

                // Rest of your validation logic...
                if (this.showIndexWarning) {
                    if (this.editIndexGenerationMode === 'manual') {
                        if (this.editIndexValidationErrors.length > 0) {
                            alert('Please fix index number validation errors:\n' + this.editIndexValidationErrors.join('\n'));
                            return;
                        }
                        if (!this.editManualIndexSuffix) {
                            alert('Please enter manual index suffix');
                            return;
                        }
                    }
                }

                this.editLoading = true;

                try {
                    const updateData = {
                        student_id: this.editingStudent.id,
                        ...this.editForm
                    };

                    // Add status change flag
                    if (statusChanged) {
                        updateData.status_changed = true;
                    }

                    // Add index generation info if needed
                    if (this.showIndexWarning) {
                        updateData.needs_new_index = true;
                        updateData.index_generation_mode = this.editIndexGenerationMode;

                        if (this.editIndexGenerationMode === 'manual') {
                            updateData.manual_index_number = this.getEditManualIndexNumber();
                        }
                    }

                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/update.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(updateData)
                    });

                    const data = await response.json();

                    if (data.success) {
                        let message = 'Student information updated successfully!';

                        if (data.data?.new_index_number) {
                            message += `\n${this.editIndexGenerationMode === 'manual' ? 'Assigned' : 'Generated'} New Index Number: ${data.data.new_index_number}`;
                        }

                        if (statusChanged) {
                            message += '\nAcademic status updated and logged.';
                        }

                        alert(message);
                        this.closeEditModal();

                        // Refresh the approved students list
                        await this.loadApprovedStudentsWithStats();

                    } else {
                        alert(data.message || 'Failed to update student information');
                    }

                } catch (error) {
                    console.error('Error updating student:', error);
                    alert('Error updating student: ' + error.message);
                } finally {
                    this.editLoading = false;
                }
            },


    };
    }
</script>