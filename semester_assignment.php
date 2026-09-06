<?php
// public/admin/pages/semester_assignment.php
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
<div x-data="semesterAssignment()" x-init="loadDepartments()" class="min-h-screen py-6 content-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Semester Assignment Management</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">Assign semesters to students by department, mode, and batch or scan QR codes</p>
                    </div>
                    <div class="flex space-x-3">
                        <button @click="toggleQRMode()" :class="qrMode ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border-gray-300'" class="inline-flex items-center px-4 py-2 border rounded-md shadow-sm text-sm font-medium hover:bg-indigo-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                            </svg>
                            <span x-text="qrMode ? 'Exit QR Mode' : 'QR Code Mode'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced QR Code Scanner Modal with Specialized Context Establishment Layout -->
        <div x-show="showQRScanner" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="background-color: rgba(0, 0, 0, 0.5);">
            <div class="flex items-center justify-center min-h-screen px-4">
                <!-- Context Establishment Layout -->
                <div x-show="qrContextMode === 'establishing'" class="bg-white rounded-lg max-w-2xl w-full p-8" @click.away="closeQRScanner()">
                    <!-- Header with Clear Purpose -->
                    <div class="text-center mb-6">
                        <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-indigo-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Establish Context</h2>
                        <p class="text-gray-600 max-w-md mx-auto">Scan any student's QR code to set the department, mode, and batch for this session. All subsequent scans must match this context.</p>
                    </div>

                    <!-- Single Column Layout for Video -->
                    <div class="space-y-6">
                        <!-- Enhanced Video Feed with Corner Brackets -->
                        <div class="relative mx-auto max-w-md">
                            <div class="relative">
                                <video x-ref="qrVideoContext"
                                       class="w-full h-80 bg-gray-100 rounded-xl border-2 object-cover"
                                       :class="qrScanning ? 'border-indigo-400 shadow-lg shadow-indigo-200/50' : 'border-gray-300'"
                                       autoplay muted playsinline></video>

                                <!-- Corner Brackets for Scanning Area -->
                                <div class="absolute -inset-3 pointer-events-none">
                                    <!-- Top Left -->
                                    <div class="absolute top-0 left-0 w-8 h-8 border-l-4 border-t-4 rounded-tl-2xl"
                                         :class="qrScanning ? 'border-indigo-600' : 'border-white'"></div>
                                    <!-- Top Right -->
                                    <div class="absolute top-0 right-0 w-8 h-8 border-r-4 border-t-4 rounded-tr-2xl"
                                         :class="qrScanning ? 'border-indigo-600' : 'border-white'"></div>
                                    <!-- Bottom Left -->
                                    <div class="absolute bottom-0 left-0 w-8 h-8 border-l-4 border-b-4 rounded-bl-2xl"
                                         :class="qrScanning ? 'border-indigo-600' : 'border-white'"></div>
                                    <!-- Bottom Right -->
                                    <div class="absolute bottom-0 right-0 w-8 h-8 border-r-4 border-b-4 rounded-br-2xl"
                                         :class="qrScanning ? 'border-indigo-600' : 'border-white'"></div>
                                </div>


                                <!-- Enhanced Scanning Indicator -->
                                <div x-show="qrScanning" class="absolute top-4 left-4 right-4">
                                    <div class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center shadow-lg">
                                        <div class="animate-pulse w-2 h-2 bg-white rounded-full mr-3"></div>
                                        <span class="animate-pulse">Scanning for QR code...</span>
                                    </div>
                                </div>
                            </div>

                            <canvas x-ref="qrCanvasContext" class="hidden"></canvas>
                        </div>

                        <!-- Status Display for Recent Scan -->
                        <div x-show="qrScanResults.length > 0" class="bg-gray-50 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <template x-if="qrScanResults[0]?.status === 'success'">
                                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </template>
                                        <template x-if="qrScanResults[0]?.status === 'error'">
                                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                                <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </template>
                                        <template x-if="qrScanResults[0]?.status === 'processing'">
                                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900" x-text="qrScanResults[0]?.nic || 'Processing...'"></div>
                                        <div class="text-xs text-gray-500" x-text="qrScanResults[0]?.message || 'Analyzing QR code...'"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Clear Instructions Card -->
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-blue-900 mb-1">How it works:</h4>
                                    <ul class="text-xs text-blue-800 space-y-1">
                                        <li>• Point camera at any student's QR code</li>
                                        <li>• Their department, mode, and batch become the session context</li>
                                        <li>• Only students matching this context can be added later</li>
                                        <li>• This ensures all students are from the same group</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Simplified Controls -->
                        <div class="flex space-x-4">
                            <button @click="startQRScanner()"
                                    :disabled="qrScanning"
                                    class="flex-1 inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-xl shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                                <svg x-show="!qrScanning" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h2M4 4h4m4 0h2m0 0v3m0 0h.01M12 12h4.01"></path>
                                </svg>
                                <div x-show="qrScanning" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
                                <span x-text="qrScanning ? 'Scanning...' : 'Start Scanner'"></span>
                            </button>

                            <button @click="closeQRScanner()"
                                    class="px-6 py-3 border-2 border-gray-300 rounded-xl shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Context Established Confirmation (shown after successful context establishment) -->
                <div x-show="qrContextMode === 'established' && !showQRScanner" class="bg-white rounded-lg max-w-2xl w-full p-8">
                    <div class="text-center">
                        <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Context Established!</h2>
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
                            <h3 class="text-lg font-medium text-green-900 mb-2">Session Details</h3>
                            <div class="text-sm text-green-800 space-y-1">
                                <p><strong>Department:</strong> <span x-text="qrContext.department_name"></span></p>
                                <p><strong>Mode:</strong> <span x-text="qrContext.mode === 'FULL' ? 'Full-time' : 'Part-time'"></span></p>
                                <p><strong>Batch:</strong> <span x-text="qrContext.batch"></span></p>
                                <p><strong>Students Added:</strong> <span x-text="qrScannedStudents.length"></span></p>
                            </div>
                        </div>
                        <p class="text-gray-600 mb-6">All future scans must match this context. Only students from the same department, mode, and batch will be accepted.</p>

                        <div class="space-y-4">
                            <button @click="openBulkScanner()" class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-xl shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h2M4 4h4m4 0h2m0 0v3m0 0h.01M12 12h4.01"></path>
                                </svg>
                                Scan More Students
                            </button>

                            <button @click="resetQRContext()" class="w-full inline-flex items-center justify-center px-6 py-3 border-2 border-gray-300 rounded-xl shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Reset Context
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Bulk Scanning Layout (existing layout when context is established and scanning) -->
                <div x-show="qrContextMode === 'established' && showQRScanner" class="bg-white rounded-lg max-w-4xl w-full p-6" @click.away="closeQRScanner()">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Add Students - Must Match Context</h3>
                            <p class="text-sm text-gray-500">
                                Only students from <span x-text="qrContext.department_name" class="font-medium"></span> -
                                <span x-text="qrContext.mode === 'FULL' ? 'Full-time' : 'Part-time'" class="font-medium"></span> -
                                Batch <span x-text="qrContext.batch" class="font-medium"></span> will be accepted
                            </p>
                        </div>
                        <button @click="closeQRScanner()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Camera Section -->
                        <div class="space-y-4">
                            <!-- Video Feed -->
                            <div class="relative">
                                <video x-ref="qrVideo" class="w-full h-64 bg-gray-100 rounded-md border-2" :class="qrScanning ? 'border-green-400' : 'border-gray-300'" autoplay muted playsinline></video>
                                <canvas x-ref="qrCanvas" class="hidden"></canvas>

                                <!-- Scanning Indicator -->
                                <div x-show="qrScanning" class="absolute top-2 left-2 bg-green-500 text-white px-2 py-1 rounded-md text-xs font-medium flex items-center">
                                    <div class="animate-pulse w-2 h-2 bg-white rounded-full mr-2"></div>
                                    Scanning...
                                </div>
                            </div>

                            <!-- Stats -->
                            <div class="grid grid-cols-4 gap-2 text-center">
                                <div class="bg-blue-50 p-2 rounded-md">
                                    <div class="text-lg font-bold text-blue-600" x-text="qrStats.totalScanned"></div>
                                    <div class="text-xs text-blue-500">Scanned</div>
                                </div>
                                <div class="bg-green-50 p-2 rounded-md">
                                    <div class="text-lg font-bold text-green-600" x-text="qrStats.successfullyAdded"></div>
                                    <div class="text-xs text-green-500">Added</div>
                                </div>
                                <div class="bg-yellow-50 p-2 rounded-md">
                                    <div class="text-lg font-bold text-yellow-600" x-text="qrStats.duplicates"></div>
                                    <div class="text-xs text-yellow-500">Duplicates</div>
                                </div>
                                <div class="bg-red-50 p-2 rounded-md">
                                    <div class="text-lg font-bold text-red-600" x-text="qrStats.errors"></div>
                                    <div class="text-xs text-red-500">Errors</div>
                                </div>
                            </div>

                            <!-- Camera Controls -->
                            <div class="flex space-x-3">
                                <button @click="startQRScanner()" :disabled="qrScanning" class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                    <span x-show="!qrScanning">Start Scanner</span>
                                    <span x-show="qrScanning" class="flex items-center">
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                Scanning...
                            </span>
                                </button>
                                <button @click="stopQRScanner()" :disabled="!qrScanning" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                    Stop
                                </button>
                            </div>
                        </div>

                        <!-- Results Section -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-md font-medium text-gray-900">Recent Scans</h4>
                                <button @click="clearQRScanResults()" class="text-sm text-gray-500 hover:text-gray-700">Clear Results</button>
                            </div>

                            <!-- Recent Scan Results -->
                            <div class="max-h-64 overflow-y-auto border rounded-md">
                                <div x-show="qrScanResults.length === 0" class="p-4 text-center text-gray-500 text-sm">
                                    No scans yet. Point camera at QR codes to start.
                                </div>

                                <div x-show="qrScanResults.length > 0" class="divide-y divide-gray-200">
                                    <template x-for="result in getRecentScanResults()" :key="result.timestamp">
                                        <div class="p-3 flex items-center justify-between">
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-gray-900" x-text="result.nic"></div>
                                                <div class="text-xs text-gray-500" x-text="result.message || 'Processing...'"></div>
                                            </div>
                                            <div class="ml-3">
                                                <!-- Success -->
                                                <div x-show="result.status === 'success'" class="flex items-center text-green-600">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <!-- Duplicate -->
                                                <div x-show="result.status === 'duplicate'" class="flex items-center text-yellow-600">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <!-- Error -->
                                                <div x-show="result.status === 'error'" class="flex items-center text-red-600">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <!-- Processing -->
                                                <div x-show="result.status === 'processing'" class="flex items-center text-blue-600">
                                                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="space-y-2">
                                <button
                                        x-show="qrScannedStudents.length > 0"
                                        @click="bulkAssignScannedStudents()"
                                        :disabled="!assignment.semester_id"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Assign All Scanned (<span x-text="qrScannedStudents.length"></span>)
                                </button>

                                <button
                                        x-show="qrScannedStudents.length > 0"
                                        @click="clearQRStudents()"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Clear All Scanned
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 flex space-x-3">
                        <button @click="closeQRScanner()" class="flex-1 px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Done Scanning
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manual Student Search Modal -->
        <div x-show="showManualSearch" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="background-color: rgba(0, 0, 0, 0.5);">
            <div class="flex items-center justify-center min-h-screen px-4 sm:px-6 lg:px-8">
                <div class="relative bg-white rounded-xl max-w-2xl w-full p-6 sm:p-8 shadow-xl transform transition-all duration-300" @click.away="closeManualSearch()">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-3">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <h3 class="text-xl font-semibold text-gray-900">Add Student Manually</h3>
                        </div>
                        <button @click="closeManualSearch()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Session Context Display -->
                    <div x-show="qrMode && qrContext.established" class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-5">
                        <div class="flex items-center space-x-3 mb-3">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h4 class="text-sm font-medium text-blue-900">Current Session Context</h4>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="bg-white rounded-md p-3 border border-blue-100">
                                <span class="block text-xs font-medium text-blue-600 uppercase tracking-wider">Department</span>
                                <span class="text-sm font-medium text-gray-900" x-text="qrContext.department_name"></span>
                            </div>
                            <div class="bg-white rounded-md p-3 border border-blue-100">
                                <span class="block text-xs font-medium text-blue-600 uppercase tracking-wider">Mode</span>
                                <span class="text-sm font-medium text-gray-900" x-text="qrContext.mode === 'FULL' ? 'Full-time' : 'Part-time'"></span>
                            </div>
                            <div class="bg-white rounded-md p-3 border border-blue-100">
                                <span class="block text-xs font-medium text-blue-600 uppercase tracking-wider">Batch</span>
                                <span class="text-sm font-medium text-gray-900" x-text="qrContext.batch"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="mb-6">
                        <label for="manual-search" class="block text-sm font-medium text-gray-700 mb-2">Search by NIC, Index Number, or Name</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input
                                    id="manual-search"
                                    type="text"
                                    x-model="manualSearch.query"
                                    @input.debounce.300ms="searchStudents()"
                                    placeholder="Enter NIC, index number, or student name..."
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-sm"
                            >
                        </div>
                    </div>

                    <!-- Search Results -->
                    <div x-show="manualSearch.loading" class="text-center py-6">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                        <p class="text-sm text-gray-500 mt-3">Searching for students...</p>
                    </div>

                    <div x-show="!manualSearch.loading && manualSearch.results.length > 0" class="max-h-80 overflow-y-auto border border-gray-200 rounded-lg">
                        <template x-for="student in manualSearch.results" :key="student.id">
                            <div class="p-4 border-b last:border-b-0 hover:bg-gray-50 cursor-pointer transition-colors duration-150" @click="addManualStudent(student)">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                <!-- Profile Image -->
                                                <div class="flex-shrink-0">
                                                    <div x-show="!student?.photo" class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                                                        <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                        </svg>
                                                    </div>
                                                    <img x-show="student?.photo"
                                                         class="w-12 h-12 rounded-full object-cover border-4 border-white border-opacity-30"
                                                         :src="`${window.APP_CONFIG.API_BASE_URL}/students/photo.php?index=${encodeURIComponent(student?.id ?? '')}&w=256&h=256&fit=crop`"
                                                         :alt="student?.name_with_initials">
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900" x-text="student.full_name"></div>
                                            <div class="text-xs text-gray-500">
                                                <span x-text="student.index_no || 'No index'"></span> •
                                                <span x-text="student.nic"></span>
                                            </div>
                                            <div class="text-xs text-gray-400" x-text="`${student.department_name} - ${student.mode === 'FULL' ? 'Full-time' : 'Part-time'} - Batch ${student.batch}`"></div>
                                        </div>
                                    </div>
                                    <button @click.stop="addManualStudent(student)" class="inline-flex items-center px-3 py-1 border border-blue-300 rounded-md text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Add
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="!manualSearch.loading && manualSearch.query && manualSearch.results.length === 0" class="text-center py-6">
                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <p class="text-sm font-medium text-gray-600">No students found</p>
                        <p class="text-xs text-gray-500 mt-1">Try adjusting your search criteria or ensure the student matches the session context.</p>
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 flex justify-end">
                        <button @click="closeManualSearch()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <div x-show="alert.show" x-cloak class="mb-6">
            <div :class="alert.type === 'success' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'" class="rounded-md border p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg x-show="alert.type === 'success'" class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <svg x-show="alert.type === 'error'" class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <!-- Added whitespace-pre-line class to preserve line breaks -->
                        <p :class="alert.type === 'success' ? 'text-green-800' : 'text-red-800'"
                           class="text-sm font-medium whitespace-pre-line"
                           x-text="alert.message"></p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button @click="alert.show = false" :class="alert.type === 'success' ? 'text-green-400 hover:text-green-600' : 'text-red-400 hover:text-red-600'" class="inline-flex rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <div x-show="assignmentModal.show"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             @keydown.escape.window="assignmentModal.show = false">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                 @click="assignmentModal.show = false"></div>

            <!-- Modal Content -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full transform transition-all overflow-hidden"
                     @click.stop>

                    <!-- Header -->
                    <div class="bg-green-50 px-6 py-4 border-b border-green-100">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg font-semibold text-green-900">
                                    Semester Assignment Complete
                                </h3>
                                <p class="text-sm text-green-700">
                                    Students have been successfully assigned to the semester
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-4">
                        <!-- Semester Info -->
                        <div class="mb-6">
                            <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                                <h4 class="font-medium text-blue-900 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Semester Details
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Semester:</span>
                                        <span class="font-medium text-blue-900" x-text="assignmentModal.semesterDisplay"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Department:</span>
                                        <span class="font-medium text-blue-900" x-text="assignmentModal.department"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Mode:</span>
                                        <span class="font-medium text-blue-900" x-text="assignmentModal.mode"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Batch:</span>
                                        <span class="font-medium text-blue-900" x-text="assignmentModal.batch"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Students Count -->
                        <div class="mb-6">
                            <div class="bg-green-50 rounded-lg p-4 border border-green-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                        </svg>
                                        <span class="text-green-800 font-medium">Students Assigned</span>
                                    </div>
                                    <span class="text-2xl font-bold text-green-700" x-text="assignmentModal.assignedCount"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Student List -->
                        <div class="mb-4">
                            <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6.5a2.5 2.5 0 01-2.5 2.5h-7A2.5 2.5 0 014 11.5V5zM7 7a1 1 0 012 0v2a1 1 0 11-2 0V7zm4 0a1 1 0 112 0v2a1 1 0 11-2 0V7z" clip-rule="evenodd"/>
                                </svg>
                                Assigned Students
                            </h4>

                            <!-- Show all students if 8 or fewer, otherwise show limited list -->
                            <template x-if="assignmentModal.studentNames.length <= 8">
                                <div class="space-y-2 max-h-48 overflow-y-auto">
                                    <template x-for="(student, index) in assignmentModal.studentNames" :key="index">
                                        <div class="flex items-center p-2 bg-gray-50 rounded border">
                                            <span class="w-6 h-6 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center text-xs font-medium mr-3" x-text="index + 1"></span>
                                            <span class="text-gray-800" x-text="student"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="assignmentModal.studentNames.length > 8">
                                <div>
                                    <div class="space-y-2 mb-3">
                                        <template x-for="(student, index) in assignmentModal.studentNames.slice(0, 5)" :key="index">
                                            <div class="flex items-center p-2 bg-gray-50 rounded border">
                                                <span class="w-6 h-6 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center text-xs font-medium mr-3" x-text="index + 1"></span>
                                                <span class="text-gray-800" x-text="student"></span>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="text-center p-3 bg-gray-100 rounded border border-dashed border-gray-300">
                                <span class="text-gray-600 text-sm">
                                    ... and <span class="font-medium" x-text="assignmentModal.studentNames.length - 5"></span> more students
                                </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 rounded-b-lg">
                        <div class="flex justify-end space-x-3">
                            <button @click="assignmentModal.show = false"
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors font-medium">
                                Perfect! Continue
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Mode Panel with Context Display -->
        <div x-show="qrMode" x-cloak class="mb-6">
            <!-- Context Setup State - Waiting -->
            <div x-show="qrContextMode === 'waiting'" class="bg-white shadow rounded-lg mb-6">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">QR Context Setup</h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">Scan any student's QR code to establish the session context</p>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <svg class="w-4 h-4 mr-1.5 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                Setup Required
                            </span>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                        <!-- Instructions -->
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Setup Process:</h4>
                                <div class="space-y-3">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                            <span class="text-xs font-medium text-indigo-600">1</span>
                                        </div>
                                        <p class="text-sm text-gray-600">Point camera at any student's QR code</p>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                            <span class="text-xs font-medium text-indigo-600">2</span>
                                        </div>
                                        <p class="text-sm text-gray-600">Department, mode, and batch are automatically detected</p>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                            <span class="text-xs font-medium text-indigo-600">3</span>
                                        </div>
                                        <p class="text-sm text-gray-600">Continue scanning students from the same group</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-blue-800">Context Restriction</p>
                                        <p class="mt-1 text-sm text-blue-700">Once established, only students from the same department, mode, and batch will be accepted.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action -->
                        <div class="text-center">
                            <div class="mb-6">
                                <div class="mx-auto w-20 h-20 bg-indigo-100 rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-indigo-600">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                                    </svg>
                                </div>
                            </div>
                            <button @click="openContextScanner()" class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                Start QR Setup
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Context Established State -->
            <div x-show="qrContextMode === 'established'" class="bg-white shadow rounded-lg mb-6">
                <!-- Header -->
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">QR Context Active</h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">Session context established - ready to scan matching students</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <!-- Live Count -->
                            <div class="bg-gray-50 rounded-lg px-3 py-2 text-center border">
                                <div class="text-lg font-medium text-gray-900" x-text="qrScannedStudents.length"></div>
                                <div class="text-xs text-gray-500">Students</div>
                            </div>
                            <!-- Status Badge -->
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <div class="w-2 h-2 bg-green-400 rounded-full mr-1.5 animate-pulse"></div>
                                Active
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="px-4 py-5 sm:px-6 space-y-6">
                    <!-- Context Details -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Current Session Context:</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Department Card -->
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <div class="flex items-center mb-2">
                                    <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Department</span>
                                </div>
                                <div class="text-sm font-medium text-gray-900" x-text="qrContext.department_name"></div>
                            </div>

                            <!-- Mode Card -->
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <div class="flex items-center mb-2">
                                    <svg class="w-5 h-5 text-purple-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Study Mode</span>
                                </div>
                                <div class="text-sm font-medium text-gray-900" x-text="qrContext.mode === 'FULL' ? 'Full-time' : 'Part-time'"></div>
                            </div>

                            <!-- Batch Card -->
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <div class="flex items-center mb-2">
                                    <svg class="w-5 h-5 text-orange-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</span>
                                </div>
                                <div class="text-sm font-medium text-gray-900" x-text="qrContext.batch"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Scanning Statistics -->
                    <div x-show="qrStats.totalScanned > 0">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Scanning Activity:</h4>
                        <div class="grid grid-cols-4 gap-3">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
                                <div class="text-lg font-medium text-blue-600" x-text="qrStats.totalScanned"></div>
                                <div class="text-xs text-blue-600 font-medium">Scanned</div>
                            </div>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-center">
                                <div class="text-lg font-medium text-green-600" x-text="qrStats.successfullyAdded"></div>
                                <div class="text-xs text-green-600 font-medium">Added</div>
                            </div>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-center">
                                <div class="text-lg font-medium text-yellow-600" x-text="qrStats.duplicates"></div>
                                <div class="text-xs text-yellow-600 font-medium">Duplicates</div>
                            </div>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-center">
                                <div class="text-lg font-medium text-red-600" x-text="qrStats.errors"></div>
                                <div class="text-xs text-red-600 font-medium">Errors</div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <!-- Primary Action -->
                        <button @click="openBulkScanner()" class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z"></path>
                            </svg>
                            Add More Students
                        </button>

                        <button @click="openManualSearch()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Add Manually
                        </button>

                        <!-- Secondary Actions -->
                        <button @click="scrollToStudentList()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            View List
                        </button>

                        <button @click="resetQRContext()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Reset Context
                        </button>
                    </div>

                    <!-- Context Restriction Notice -->
                    <div class="bg-amber-50 border border-amber-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-amber-800">Context Restriction Active</p>
                                <p class="mt-1 text-sm text-amber-700">Only students matching the established context will be accepted. Students from different departments, modes, or batches will be rejected.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step Navigation -->
        <div x-show="!qrMode" x-cloak class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <nav class="flex items-center justify-center">
                    <ol class="flex items-center space-x-5">
                        <!-- Step 1: Department -->
                        <li class="flex items-center">
                            <div :class="currentStep >= 1 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600'" class="relative w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">
                                1
                            </div>
                            <span :class="currentStep >= 1 ? 'text-indigo-600 font-medium' : 'text-gray-500'" class="ml-2 text-sm">Department</span>
                        </li>

                        <!-- Arrow -->
                        <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>

                        <!-- Step 2: Mode -->
                        <li class="flex items-center">
                            <div :class="currentStep >= 2 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600'" class="relative w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">
                                2
                            </div>
                            <span :class="currentStep >= 2 ? 'text-indigo-600 font-medium' : 'text-gray-500'" class="ml-2 text-sm">Mode</span>
                        </li>

                        <!-- Arrow -->
                        <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>

                        <!-- Step 3: Batch -->
                        <li class="flex items-center">
                            <div :class="currentStep >= 3 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600'" class="relative w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">
                                3
                            </div>
                            <span :class="currentStep >= 3 ? 'text-indigo-600 font-medium' : 'text-gray-500'" class="ml-2 text-sm">Batch</span>
                        </li>

                        <!-- Arrow -->
                        <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>

                        <!-- Step 4: Students -->
                        <li class="flex items-center">
                            <div :class="currentStep >= 4 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600'" class="relative w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">
                                4
                            </div>
                            <span :class="currentStep >= 4 ? 'text-indigo-600 font-medium' : 'text-gray-500'" class="ml-2 text-sm">Students</span>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Manual Steps (Step 1-3) -->
        <div x-show="!qrMode">
            <!-- Step 1: Department Selection -->
            <div x-show="currentStep === 1" x-cloak class="bg-white shadow rounded-lg mb-6">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Step 1: Select Department</h3>
                    <p class="mt-1 text-sm text-gray-500">Choose the department for semester assignment</p>
                </div>
                <div class="px-4 py-5 sm:px-6">
                    <div class="max-w-sm">
                        <select x-model="selection.department_id" @change="onDepartmentChange()" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Department</option>
                            <template x-for="dept in departments" :key="dept.id">
                                <option :value="dept.id" x-text="`${dept.name} (${dept.code})`"></option>
                            </template>
                        </select>
                    </div>
                    <div class="mt-4">
                        <button @click="nextStep()" :disabled="!selection.department_id" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                            Next: Select Mode
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Mode Selection -->
            <div x-show="currentStep === 2" x-cloak class="bg-white shadow rounded-lg mb-6">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Step 2: Select Study Mode</h3>
                    <p class="mt-1 text-sm text-gray-500">Choose between Full-time or Part-time students</p>
                </div>
                <div class="px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 max-w-md">
                        <div>
                            <label class="relative flex cursor-pointer rounded-lg border p-4 shadow-sm focus:outline-none" :class="selection.mode === 'FULL' ? 'bg-indigo-50 border-indigo-200' : 'bg-white border-gray-300'">
                                <input type="radio" x-model="selection.mode" value="FULL" class="sr-only">
                                <div class="flex flex-1">
                                    <div class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900">Full-time</span>
                                        <span class="mt-1 flex items-center text-sm text-gray-500">Full-time students</span>
                                    </div>
                                </div>
                                <svg x-show="selection.mode === 'FULL'" class="h-5 w-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </label>
                        </div>

                        <div>
                            <label class="relative flex cursor-pointer rounded-lg border p-4 shadow-sm focus:outline-none" :class="selection.mode === 'PART' ? 'bg-indigo-50 border-indigo-200' : 'bg-white border-gray-300'">
                                <input type="radio" x-model="selection.mode" value="PART" class="sr-only">
                                <div class="flex flex-1">
                                    <div class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900">Part-time</span>
                                        <span class="mt-1 flex items-center text-sm text-gray-500">Part-time students</span>
                                    </div>
                                </div>
                                <svg x-show="selection.mode === 'PART'" class="h-5 w-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 flex space-x-3">
                        <button @click="previousStep()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Previous
                        </button>
                        <button @click="loadBatches()" :disabled="!selection.mode" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                            Next: Load Batches
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Batch Selection -->
            <div x-show="currentStep === 3" x-cloak class="bg-white shadow rounded-lg mb-6">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Step 3: Select Batch</h3>
                    <p class="mt-1 text-sm text-gray-500">Choose the batch (enrollment year) for students</p>
                </div>
                <div class="px-4 py-5 sm:px-6">
                    <div x-show="loading.batches" class="flex items-center justify-center py-8">
                        <div class="flex items-center space-x-2">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
                            <span class="text-gray-500">Loading batches...</span>
                        </div>
                    </div>

                    <div x-show="!loading.batches" class="max-w-sm">
                        <select x-model="selection.batch" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Batch</option>
                            <template x-for="batch in batches" :key="batch">
                                <option :value="batch" x-text="batch"></option>
                            </template>
                        </select>
                    </div>

                    <div x-show="!loading.batches" class="mt-6 flex space-x-3">
                        <button @click="previousStep()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Previous
                        </button>
                        <button @click="loadStudents()" :disabled="!selection.batch" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                            Next: Load Students
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Students and Semester Assignment (shown for both modes) -->
        <div x-show="currentStep === 4 || qrMode" x-cloak class="space-y-6">
            <!-- Semester Selection -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        <span x-show="qrMode">QR Mode: Select Semester & Assign Students</span>
                        <span x-show="!qrMode">Step 4: Select Semester & Assign Students</span>
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">Choose semester and assign to selected students</p>
                </div>
                <div class="px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select Semester</label>
                            <select x-model="assignment.semester_id" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Choose Semester</option>
                                <template x-for="semester in semesters" :key="semester.id">
                                    <option :value="semester.id" x-text="`Year ${semester.year} - Semester ${semester.semester_number} (${semester.academic_year})`"></option>
                                </template>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button @click="assignBulkSemester()" :disabled="!assignment.semester_id || selectedStudents.length === 0 || assignment.processing" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                                <span x-show="!assignment.processing">Assign to Selected (<span x-text="selectedStudents.length"></span>)</span>
                                <span x-show="assignment.processing" class="flex items-center">
                                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                    Assigning...
                                </span>
                            </button>
                        </div>
                        <div class="flex items-end" x-show="!qrMode">
                            <button @click="previousStep()" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                Previous
                            </button>
                        </div>
                        <div class="flex items-end" x-show="qrMode">
                            <button @click="openQRScanner()" class="w-full inline-flex items-center justify-center px-4 py-2 border border-indigo-600 text-indigo-600 bg-white hover:bg-indigo-50 rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                                </svg>
                                Scan More Students
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QR Scanned Students Summary -->
            <div x-show="qrMode && qrScannedStudents.length > 0" x-cloak class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-blue-800">QR Scanned Students</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>Found <span x-text="qrScannedStudents.length"></span> student(s) from QR scanning across different departments/batches.</p>
                            <div class="mt-2 space-y-1">
                                <template x-for="group in getQRGroupedStudents()" :key="group.key">
                                    <div class="text-xs">
                                        • <span x-text="group.department"></span> - <span x-text="group.mode"></span> - Batch <span x-text="group.batch"></span>: <span x-text="group.students.length"></span> student(s)
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Students List -->
            <div id="student-list-section" class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Students (<span x-text="students.length"></span>)
                            </h3>
                            <p class="mt-1 text-sm text-gray-500" x-show="!qrMode">
                                <span x-text="getSelectedDepartmentName()"></span> •
                                <span x-text="selection.mode === 'FULL' ? 'Full-time' : 'Part-time'"></span> •
                                Batch <span x-text="selection.batch"></span>
                            </p>
                            <p class="mt-1 text-sm text-gray-500" x-show="qrMode">
                                Mixed departments and batches from QR scanning
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            <button @click="selectAllStudents()" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                Select All
                            </button>
                            <button @click="deselectAllStudents()" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                Deselect All
                            </button>
                            <button x-show="qrMode" @click="clearQRStudents()" class="inline-flex items-center px-3 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                Clear All QR Students
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div x-show="loading.students" x-cloak class="flex items-center justify-center py-12">
                    <div class="flex items-center space-x-2">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
                        <span class="text-gray-500">Loading students...</span>
                    </div>
                </div>

                <!-- Students Table -->
                <div x-show="!loading.students && students.length > 0" x-cloak class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" @change="toggleAll($event)" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Index Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mode/Batch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Semester</th>
                            <th x-show="qrMode || hasManualStudents()" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="student in students" :key="student.id">
                            <tr :class="selectedStudents.includes(student.id) ? 'bg-indigo-50' : 'hover:bg-gray-50'" class="transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" :value="student.id" @change="toggleStudent(student.id)" :checked="selectedStudents.includes(student.id)" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full flex items-center justify-center" :class="student.source === 'qr' ? 'bg-green-100' : 'bg-indigo-100'">
                                                <!-- Profile Image -->
                                                <div class="flex-shrink-0">
                                                    <div x-show="!student?.photo" class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                                                        <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                        </svg>
                                                    </div>
                                                    <img x-show="student?.photo"
                                                         class="w-12 h-12 rounded-full object-cover border-4 border-white border-opacity-30"
                                                         :src="`${window.APP_CONFIG.API_BASE_URL}/students/photo.php?index=${encodeURIComponent(student?.id ?? '')}&w=256&h=256&fit=crop`"
                                                         :alt="student?.name_with_initials">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ml-5">
                                            <div class="text-sm font-medium text-gray-900" x-text="student.full_name"></div>
                                            <div class="text-sm text-gray-500" x-text="student.email"></div>
                                            <div x-show="student.nic" class="text-xs text-gray-400">
                                                NIC: <span x-text="student.nic"></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="student.index_no || 'Not assigned'"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="student.department_name || getSelectedDepartmentName()"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div x-text="(student.mode === 'FULL' ? 'Full-time' : 'Part-time')"></div>
                                    <div class="text-xs text-gray-500">Batch: <span x-text="student.batch || selection.batch"></span></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="student.status === 'APPROVED' ? 'bg-green-100 text-green-800' : student.status === 'PENDING_APPROVAL' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" x-text="student.status"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="student.current_semester || 'Not assigned'"></td>
                                <td x-show="qrMode || hasManualStudents()" class="px-6 py-4 whitespace-nowrap">
                                        <span :class="student.source === 'qr' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                                            <span x-text="student.source === 'qr' ? 'QR Scanned' : 'Manual'"></span>
                                        </span>
                                </td>
                            </tr>
                        </template>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div x-show="!loading.students && students.length === 0" x-cloak class="px-6 py-12 text-center">
                    <div class="text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto mb-4 text-gray-400" >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                        <p class="text-lg font-medium mb-2">No students found</p>
                        <p class="text-sm" x-show="!qrMode">No students found for the selected criteria</p>
                        <p class="text-sm" x-show="qrMode">Scan QR codes to add students to the list</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function semesterAssignment() {
        return {
            // Data
            departments: [],
            batches: [],
            students: [],
            semesters: [],
            selectedStudents: [],
            qrScannedStudents: [], // Store QR scanned students

            // Manual search
            showManualSearch: false,
            manualSearch: {
                query: '',
                results: [],
                loading: false
            },

            // Current step
            currentStep: 1,

            // QR Mode
            qrMode: false,
            showQRScanner: false,
            qrScanning: false,
            qrScanResults: [], // Track all scan results
            qrStream: null,
            qrLastScanTime: 0, // Prevent duplicate rapid scans
            qrScanDelay: 2000, // 2 second delay between scans
            qrProcessingQueue: [], // Queue for processing scans
            qrStats: {
                totalScanned: 0,
                successfullyAdded: 0,
                duplicates: 0,
                errors: 0
            },

            // Loading states
            loading: {
                batches: false,
                students: false
            },

            // Selections
            selection: {
                department_id: '',
                mode: '',
                batch: ''
            },

            // Assignment
            assignment: {
                semester_id: '',
                processing: false
            },

            // Alert system
            alert: {
                show: false,
                type: 'success',
                message: ''
            },

            // QR Mode Context (established by first scan)
            qrContext: {
                established: false,
                department_id: null,
                department_name: null,
                mode: null,
                batch: null
            },

            qrContextMode: 'waiting', // 'waiting' | 'establishing' | 'established'

            assignmentModal: {
                show: false,
                semesterDisplay: '',
                department: '',
                mode: '',
                batch: '',
                assignedCount: 0,
                studentNames: []
            },

            // Manual search methods
            openManualSearch() {
                if (!this.qrContext.established) {
                    this.showAlert('Context must be established first', 'error');
                    return;
                }
                this.showManualSearch = true;
                this.manualSearch.query = '';
                this.manualSearch.results = [];
            },

            closeManualSearch() {
                this.showManualSearch = false;
                this.manualSearch.query = '';
                this.manualSearch.results = [];
            },

            hasManualStudents() {
                return this.students.some(s => s.source === 'manual');
            },

            async searchStudents() {
                const query = this.manualSearch.query.trim();
                if (query.length < 2) {
                    this.manualSearch.results = [];
                    return;
                }

                this.manualSearch.loading = true;
                try {
                    const params = new URLSearchParams({
                        q: query,
                        department: this.qrContext.department_id,
                        mode: this.qrContext.mode,
                        batch: this.qrContext.batch
                    });
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/search.php?${params.toString()}`);
                    const data = await response.json();

                    if (data.success) {
                        this.manualSearch.results = data.results || [];
                    } else {
                        this.manualSearch.results = [];
                        console.warn('Search failed:', data.message);
                    }
                } catch (error) {
                    console.error('Search error:', error);
                    this.manualSearch.results = [];
                } finally {
                    this.manualSearch.loading = false;
                }
            },

            async addManualStudent(student) {
                // Check if already exists
                const exists = this.students.find(s => s.id === student.id);
                if (exists) {
                    this.showAlert(`${student.full_name} is already in the list`, 'error');
                    return;
                }

                // Context validation (same as QR)
                const contextMismatch =
                    student.department_id !== this.qrContext.department_id ||
                    student.mode !== this.qrContext.mode ||
                    (student.batch || student.enrollment_year) !== this.qrContext.batch;

                if (contextMismatch) {
                    const expected = `${this.qrContext.department_name} - ${this.qrContext.mode === 'FULL' ? 'Full-time' : 'Part-time'} - Batch ${this.qrContext.batch}`;
                    const actual = `${student.department_name} - ${student.mode === 'FULL' ? 'Full-time' : 'Part-time'} - Batch ${student.batch}`;

                    this.showAlert(`Context mismatch. Expected: ${expected}, Got: ${actual}`, 'error');
                    return;
                }

                // Add student
                const processedStudent = {
                    ...student,
                    source: 'manual',
                    scan_timestamp: Date.now()
                };

                this.students.push(processedStudent);
                this.selectedStudents.push(student.id);

                this.showAlert(`${student.full_name} added successfully`, 'success');
                this.closeManualSearch();
            },

            scrollToStudentList() {
                // Scroll to the student list section smoothly
                const studentSection = document.querySelector('#student-list-section') || document.querySelector('[data-section="students"]');
                if (studentSection) {
                    studentSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            },

            resetQRContext() {
                if (confirm('Reset context and clear all QR students?')) {
                    this.qrContextMode = 'waiting';
                    this.qrContext.established = false;
                    this.qrScannedStudents = [];
                    this.students = [];
                    this.selectedStudents = [];
                    this.clearQRScanResults();
                    this.showAlert('Context reset - scan a student to establish new context', 'success');
                }
            },

            // Initialize
            async loadDepartments() {
                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/departments.php`);
                    const data = await response.json();

                    if (data.success) {
                        this.departments = data.data || [];
                        await this.loadAllSemesters(); // Load all semesters initially
                    } else {
                        this.showAlert('Error loading departments: ' + data.message, 'error');
                    }
                } catch (error) {
                    console.error('Error loading departments:', error);
                    this.showAlert('Error loading departments: ' + error.message, 'error');
                }
            },

            // QR Mode functions
            toggleQRMode() {
                this.qrMode = !this.qrMode;
                if (this.qrMode) {
                    this.currentStep = 4;
                    // Reset everything for fresh context establishment
                    this.qrContextMode = 'waiting';
                    this.qrContext = {
                        established: false,
                        department_id: null,
                        department_name: null,
                        mode: null,
                        batch: null
                    };

                    // Clear any existing QR students and show context scanner immediately
                    this.qrScannedStudents = [];
                    this.students = [];
                    this.selectedStudents = [];
                    this.openContextScanner();
                } else {
                    this.currentStep = 1;
                    this.students = [];
                    this.selectedStudents = [];
                    this.qrContextMode = 'waiting';
                    this.qrContext.established = false;
                }
            },

            // New method for context establishment scanner
            openContextScanner() {
                this.qrContextMode = 'establishing';
                this.showQRScanner = true;
                this.qrScanResults = [];
                this.qrStats = {
                    totalScanned: 0,
                    successfullyAdded: 0,
                    duplicates: 0,
                    errors: 0
                };

                // Use Alpine's $nextTick to ensure DOM is updated
                this.$nextTick(() => {
                    // Small delay to ensure the modal is fully rendered
                    setTimeout(() => {
                        this.startQRScanner();
                    }, 100);
                });
            },

            openBulkScanner() {
                if (!this.qrContext.established) {
                    this.showAlert('Context must be established first', 'error');
                    return;
                }

                this.showQRScanner = true;
                this.qrScanResults = [];
                this.qrStats = {
                    totalScanned: 0,
                    successfullyAdded: 0,
                    duplicates: 0,
                    errors: 0
                };

                // Use Alpine's $nextTick to ensure DOM is updated
                this.$nextTick(() => {
                    setTimeout(() => {
                        this.startQRScanner();
                    }, 100);
                });
            },

            openQRScanner() {
                this.showQRScanner = true;
                this.qrScanResults = [];
                this.qrStats = {
                    totalScanned: 0,
                    successfullyAdded: 0,
                    duplicates: 0,
                    errors: 0
                };
                // Wait for next tick to ensure modal is rendered
                this.$nextTick(() => {
                    this.startQRScanner();
                });
            },

            closeQRScanner() {
                this.stopQRScanner();
                this.showQRScanner = false;
                this.qrScanResults = [];

                // Special handling for context establishment mode
                if (this.qrContextMode === 'establishing') {
                    if (!this.qrContext.established) {
                        // Context was not established, exit QR mode
                        this.qrMode = false;
                        this.qrContextMode = 'waiting';
                        this.currentStep = 1;
                        this.showAlert('QR mode cancelled - no context established', 'info');
                    } else {
                        // Context was established, show success message and prepare for bulk scanning
                        this.showAlert(`Context established successfully. You can now scan more students from ${this.qrContext.department_name}.`, 'success');
                    }
                }
            },

            async startQRScanner() {
                try {
                    this.qrScanning = true;

                    // Request camera permission
                    const stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'environment',
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        }
                    });

                    // Determine which video element to use based on current mode
                    let video;
                    if (this.qrContextMode === 'establishing') {
                        video = this.$refs.qrVideoContext;
                    } else {
                        video = this.$refs.qrVideo;
                    }

                    if (!video) {
                        throw new Error('Video element not found');
                    }

                    video.srcObject = stream;
                    this.qrStream = stream;

                    await video.play();

                    // Use native BarcodeDetector if available, otherwise fall back to manual detection
                    if ('BarcodeDetector' in window) {
                        await this.startNativeBarcodeDetection(video);
                    } else {
                        await this.startManualQRDetection(video);
                    }

                } catch (error) {
                    console.error('Error starting QR scanner:', error);
                    this.showAlert('Error accessing camera: ' + error.message, 'error');
                    this.qrScanning = false;
                }
            },

            // 1. Optimize QR Detection Performance
            async startNativeBarcodeDetection(video) {
                try {
                    const barcodeDetector = new BarcodeDetector({
                        formats: ['qr_code']
                    });

                    let isDetecting = false;
                    const detectLoop = async () => {
                        if (!this.qrScanning || isDetecting) return;

                        isDetecting = true;
                        try {
                            const barcodes = await barcodeDetector.detect(video);
                            if (barcodes.length > 0) {
                                await this.handleQRScanResult(barcodes[0].rawValue);
                            }
                        } catch (e) {
                            // Continue scanning on detection errors
                        } finally {
                            isDetecting = false;
                        }

                        // Reduce detection frequency for better performance
                        setTimeout(detectLoop, 200);
                    };

                    detectLoop();

                } catch (error) {
                    console.error('Native barcode detection failed:', error);
                    await this.startManualQRDetection(video);
                }
            },

            async startManualQRDetection(video) {
                // Load jsQR library for manual QR detection
                if (!window.jsQR) {
                    await this.loadJsQRLibrary();
                }

                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d', { willReadFrequently: true });

                const detectLoop = () => {
                    if (!this.qrScanning) return;

                    if (video.readyState === video.HAVE_ENOUGH_DATA) {
                        canvas.height = video.videoHeight;
                        canvas.width = video.videoWidth;
                        context.drawImage(video, 0, 0, canvas.width, canvas.height);

                        const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                        const code = jsQR(imageData.data, imageData.width, imageData.height, {
                            inversionAttempts: "dontInvert",
                        });

                        if (code) {
                            this.handleQRScanResult(code.data);
                        }
                    }

                    // Continue detection loop - don't stop after finding a code
                    requestAnimationFrame(detectLoop);
                };

                detectLoop();
            },

            async loadJsQRLibrary() {
                return new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js';
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            },

            stopQRScanner() {
                this.qrScanning = false;

                if (this.qrStream) {
                    this.qrStream.getTracks().forEach(track => track.stop());
                    this.qrStream = null;
                }

                // Stop both video elements to be safe
                const videos = [this.$refs.qrVideo, this.$refs.qrVideoContext];
                videos.forEach(video => {
                    if (video) {
                        video.srcObject = null;
                    }
                });
            },

            // 2. Debounce QR Processing with Smart Cache
            async handleQRScanResult(scannedData) {
                const currentTime = Date.now();
                const normalizedNIC = scannedData.trim().toUpperCase();

                // Increase delay to prevent rapid duplicate scans
                if (currentTime - this.qrLastScanTime < 3000) {
                    return;
                }

                // Check recent scans cache (last 5 minutes)
                const fiveMinutesAgo = currentTime - 300000;
                const recentScan = this.qrScanResults.find(result =>
                    result.nic === normalizedNIC &&
                    result.timestamp > fiveMinutesAgo
                );

                if (recentScan) {
                    // Show cached result instead of re-processing
                    this.updateScanResult(normalizedNIC, recentScan.status, `${recentScan.message} (cached)`);
                    return;
                }

                this.qrLastScanTime = currentTime;
                this.qrStats.totalScanned++;

                // Add to scan results immediately
                this.qrScanResults.unshift({
                    nic: normalizedNIC,
                    timestamp: currentTime,
                    status: 'processing',
                    message: 'Validating...'
                });

                // Limit scan results array size for memory management
                if (this.qrScanResults.length > 50) {
                    this.qrScanResults = this.qrScanResults.slice(0, 50);
                }

                await this.processQRScan(normalizedNIC);
            },

            // 4. Optimized Student Processing with Better Error Handling
            async processQRScan(normalizedNIC) {
                const scanResult = this.qrScanResults.find(r => r.nic === normalizedNIC);

                try {
                    // Client-side validation first
                    if (!this.validateNIC(normalizedNIC)) {
                        this.updateScanResult(normalizedNIC, 'error', 'Invalid NIC format');
                        this.qrStats.errors++;
                        this.playErrorSound();
                        return;
                    }

                    // Check if already in current session (faster than API call)
                    const existingStudent = this.students.find(s => s.nic === normalizedNIC);
                    if (existingStudent) {
                        this.updateScanResult(normalizedNIC, 'duplicate', `${existingStudent.full_name} already in list`);
                        this.qrStats.duplicates++;
                        this.playDuplicateSound();
                        return;
                    }

                    // Update status to show API call in progress
                    this.updateScanResult(normalizedNIC, 'processing', 'Checking database...');

                    const student = await this.lookupStudentByNIC(normalizedNIC);

                    if (!student) {
                        this.updateScanResult(normalizedNIC, 'error', 'Student not found or not eligible');
                        this.qrStats.errors++;
                        this.playErrorSound();
                        return;
                    }

                    // Context establishment mode
                    if (this.qrContextMode === 'establishing') {
                        await this.establishContextFromStudent(student, normalizedNIC);
                        return;
                    }

                    // Regular scanning with context validation
                    if (this.qrContextMode === 'established') {
                        await this.validateAndAddStudent(student, normalizedNIC);
                    }

                } catch (error) {
                    console.error('Error processing QR scan:', error);
                    this.updateScanResult(normalizedNIC, 'error', 'Processing failed: ' + error.message);
                    this.qrStats.errors++;
                    this.playErrorSound();
                }
            },

            // 5. Separate Context Establishment Logic
            async establishContextFromStudent(student, nic) {
                this.qrContext = {
                    established: true,
                    department_id: student.department_id,
                    department_name: student.department_name,
                    mode: student.mode,
                    batch: student.batch
                };

                await this.loadSemesters();

                this.qrContextMode = 'established';

                // Add the context-setting student
                const processedStudent = {
                    ...student,
                    source: 'qr',
                    scan_timestamp: Date.now()
                };

                this.qrScannedStudents.push(processedStudent);
                this.students.push(processedStudent);
                this.selectedStudents.push(student.id);

                this.updateScanResult(nic, 'success', `${student.full_name} - Context established`);
                this.qrStats.successfullyAdded++;

                // Load semesters for this department in background
                this.loadSemestersForDepartment(student.department_id).catch(console.error);

                this.playSuccessSound();

                // Smooth transition with better UX
                setTimeout(() => {
                    this.stopQRScanner();
                    this.showQRScanner = false;
                    this.showAlert(
                        `Context established: ${student.department_name} - ${student.mode === 'FULL' ? 'Full-time' : 'Part-time'} - Batch ${student.batch}`,
                        'success'
                    );
                }, 1500);
            },

            // 6. Optimized Student Addition with Better Context Validation
            async validateAndAddStudent(student, nic) {
                // Fast context validation
                const contextMismatch =
                    student.department_id !== this.qrContext.department_id ||
                    student.mode !== this.qrContext.mode ||
                    student.batch !== this.qrContext.batch;

                if (contextMismatch) {
                    const expected = `${this.qrContext.department_name} - ${this.qrContext.mode === 'FULL' ? 'Full-time' : 'Part-time'} - Batch ${this.qrContext.batch}`;
                    const actual = `${student.department_name} - ${student.mode === 'FULL' ? 'Full-time' : 'Part-time'} - Batch ${student.batch}`;

                    this.updateScanResult(nic, 'error', `Context mismatch. Expected: ${expected}, Got: ${actual}`);
                    this.qrStats.errors++;
                    this.playErrorSound();
                    return;
                }

                // Add matching student
                const processedStudent = {
                    ...student,
                    source: 'qr',
                    scan_timestamp: Date.now()
                };

                this.qrScannedStudents.push(processedStudent);
                this.students.push(processedStudent);
                this.selectedStudents.push(student.id);

                this.updateScanResult(nic, 'success', student.full_name);
                this.qrStats.successfullyAdded++;
                this.playSuccessSound();
            },

            updateScanResult(nic, status, message) {
                const result = this.qrScanResults.find(r => r.nic === nic);
                if (result) {
                    result.status = status;
                    result.message = message;
                }
            },

            // Play success sound - ascending double beep
            playSuccessSound() {
                this.playToneSequence([
                    { freq: 700, duration: 0.15 },
                    { freq: 900, duration: 0.15 }
                ]);
            },

            // Play error sound - descending double beep
            playErrorSound() {
                this.playToneSequence([
                    { freq: 600, duration: 0.2 },
                    { freq: 300, duration: 0.25 }
                ]);
            },

            // Play duplicate sound - single mid-tone beep
            playDuplicateSound() {
                this.playToneSequence([
                    { freq: 500, duration: 0.3 }
                ]);
            },

            // 9. Enhanced Audio Feedback with Volume Control
            playToneSequence(tones) {
                try {
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    let currentTime = audioContext.currentTime;

                    tones.forEach(({ freq, duration }) => {
                        const oscillator = audioContext.createOscillator();
                        const gainNode = audioContext.createGain();

                        oscillator.connect(gainNode);
                        gainNode.connect(audioContext.destination);

                        oscillator.frequency.value = freq;
                        oscillator.type = 'sine';

                        // Reduced volume for better UX
                        gainNode.gain.setValueAtTime(0.15, currentTime);
                        gainNode.gain.exponentialRampToValueAtTime(0.01, currentTime + duration);

                        oscillator.start(currentTime);
                        oscillator.stop(currentTime + duration);

                        currentTime += duration + 0.05;
                    });

                    // Clean up audio context after use
                    setTimeout(() => {
                        if (audioContext.state !== 'closed') {
                            audioContext.close().catch(console.warn);
                        }
                    }, 5000);

                } catch (error) {
                    console.warn("Audio feedback failed:", error);
                }
            },

            clearQRScanResults() {
                this.qrScanResults = [];
                this.qrStats = {
                    totalScanned: 0,
                    successfullyAdded: 0,
                    duplicates: 0,
                    errors: 0
                };
            },

            // 8. Improved Memory Management
            getRecentScanResults() {
                // Show last 15 results, most recent first, with better memory management
                return this.qrScanResults
                    .slice(0, 15)
                    .sort((a, b) => b.timestamp - a.timestamp);
            },

            // 3. Enhanced Validation with Client-side Pre-check
            validateNIC(nic) {
                if (!nic || typeof nic !== 'string') return false;

                const normalized = nic.trim().toUpperCase();

                // Enhanced NIC validation
                const oldFormat = /^[0-9]{9}[VX]$/;
                const newFormat = /^[0-9]{12}$/;

                if (!oldFormat.test(normalized) && !newFormat.test(normalized)) {
                    return false;
                }

                // Additional validation for old format
                if (oldFormat.test(normalized)) {
                    const year = parseInt(normalized.substring(0, 2));
                    const dayOfYear = parseInt(normalized.substring(2, 5));

                    // Basic range checks
                    if (dayOfYear < 1 || dayOfYear > 366) return false;
                    if (year < 0 || year > 99) return false;
                }

                return true;
            },

            // 7. Optimized API Call with Timeout and Retry
            async lookupStudentByNIC(nic) {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout

                try {
                    const response = await fetch(
                        `${window.APP_CONFIG.API_BASE_URL}/students/lookup_by_nic.php?nic=${encodeURIComponent(nic)}`,
                        {
                            signal: controller.signal,
                            headers: {
                                'Cache-Control': 'no-cache'
                            }
                        }
                    );

                    clearTimeout(timeoutId);

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }

                    const data = await response.json();

                    if (data.success) {
                        return data.student;
                    } else {
                        console.log('Student lookup failed:', data.message);
                        return null;
                    }
                } catch (error) {
                    clearTimeout(timeoutId);

                    if (error.name === 'AbortError') {
                        throw new Error('Request timeout - please try again');
                    }

                    throw new Error(`Network error: ${error.message}`);
                }
            },

            async loadSemestersForDepartment(departmentId) {
                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/semesters/list.php?department_id=${departmentId}`);
                    const data = await response.json();

                    if (data.success) {
                        // Merge with existing semesters, avoiding duplicates
                        const existingSemesterIds = this.semesters.map(s => s.id);
                        const newSemesters = data.semesters.filter(s => !existingSemesterIds.includes(s.id));
                        this.semesters = [...this.semesters, ...newSemesters];
                    }
                } catch (error) {
                    console.error('Error loading semesters for department:', error);
                }
            },

            async loadAllSemesters() {
                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/semesters/list.php`);
                    const data = await response.json();

                    if (data.success) {
                        this.semesters = data.semesters || [];
                    }
                } catch (error) {
                    console.error('Error loading all semesters:', error);
                }
            },

            // Enhanced QR students clearing with confirmation
            clearQRStudents() {
                if (this.qrScannedStudents.length === 0) {
                    this.showAlert('No QR scanned students to clear', 'error');
                    return;
                }

                if (confirm(`Are you sure you want to remove all ${this.qrScannedStudents.length} QR scanned students?`)) {
                    // Remove all QR scanned students
                    this.students = this.students.filter(student => student.source !== 'qr');
                    this.selectedStudents = this.selectedStudents.filter(studentId =>
                        !this.qrScannedStudents.find(qrStudent => qrStudent.id === studentId)
                    );

                    const clearedCount = this.qrScannedStudents.length;
                    this.qrScannedStudents = [];
                    this.clearQRScanResults();

                    // Reset QR context
                    this.qrContext.established = false;

                    this.showAlert(`Cleared ${clearedCount} QR scanned students and reset context`, 'success');
                }
            },

            // Batch process all successfully scanned students
            async bulkAssignScannedStudents() {
                if (!this.assignment.semester_id) {
                    this.showAlert('Please select a semester first', 'error');
                    return;
                }

                const qrStudentIds = this.qrScannedStudents.map(student => student.id);

                if (qrStudentIds.length === 0) {
                    this.showAlert('No QR scanned students to assign', 'error');
                    return;
                }

                // Temporarily set selected students to all QR scanned students
                const originalSelection = [...this.selectedStudents];
                this.selectedStudents = qrStudentIds;

                try {
                    await this.assignBulkSemester();

                    // Clear QR students after successful assignment
                    this.qrScannedStudents = [];
                    this.students = this.students.filter(student => student.source !== 'qr');
                    this.clearQRScanResults();

                } catch (error) {
                    // Restore original selection on error
                    this.selectedStudents = originalSelection;
                    throw error;
                }
            },

            getQRGroupedStudents() {
                const groups = {};

                this.qrScannedStudents.forEach(student => {
                    const key = `${student.department_id}-${student.mode}-${student.batch}`;
                    if (!groups[key]) {
                        groups[key] = {
                            key,
                            department: student.department_name,
                            mode: student.mode === 'FULL' ? 'Full-time' : 'Part-time',
                            batch: student.batch,
                            students: []
                        };
                    }
                    groups[key].students.push(student);
                });

                return Object.values(groups);
            },

            // Department change handler
            onDepartmentChange() {
                // Reset subsequent selections
                this.selection.mode = '';
                this.selection.batch = '';
                this.batches = [];
                this.students = [];
                this.selectedStudents = [];

                // Load semesters for selected department
                this.loadSemesters();
            },

            // Load available semesters for department
            // ✅ Updated loadSemesters to respect QR context
            async loadSemesters() {
                let departmentId;

                if (this.qrMode && this.qrContext.established) {
                    departmentId = this.qrContext.department_id;
                } else if (!this.qrMode && this.selection.department_id) {
                    departmentId = this.selection.department_id;
                }

                if (!departmentId) return;

                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/semesters/list.php?department_id=${departmentId}`);
                    const data = await response.json();

                    if (data.success) {
                        this.semesters = data.semesters || [];
                    } else {
                        this.showAlert('Error loading semesters: ' + data.message, 'error');
                    }
                } catch (error) {
                    console.error('Error loading semesters:', error);
                    this.showAlert('Error loading semesters: ' + error.message, 'error');
                }
            },

            // Load batches based on department and mode
            async loadBatches() {
                if (!this.selection.department_id || !this.selection.mode) return;

                this.loading.batches = true;
                this.currentStep = 3;

                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/batches.php?department_id=${this.selection.department_id}&mode=${this.selection.mode}`);
                    const data = await response.json();

                    if (data.success) {
                        this.batches = data.batches;
                    } else {
                        this.showAlert('Error loading batches: ' + data.message, 'error');
                    }
                } catch (error) {
                    console.error('Error loading batches:', error);
                    this.showAlert('Error loading batches: ' + error.message, 'error');
                } finally {
                    this.loading.batches = false;
                }
            },

            // Load students based on selection criteria
            async loadStudents() {
                if (!this.selection.department_id || !this.selection.mode || !this.selection.batch) return;

                this.loading.students = true;
                this.currentStep = 4;
                this.selectedStudents = [];

                try {
                    const params = new URLSearchParams({
                        department_id: this.selection.department_id,
                        mode: this.selection.mode,
                        batch: this.selection.batch
                    });

                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/list_for_assignment.php?${params}`);
                    const data = await response.json();

                    if (data.success) {
                        // Add source identifier for manually loaded students
                        const manualStudents = data.students.map(student => ({
                            ...student,
                            source: 'manual'
                        }));

                        // Combine with QR scanned students if any
                        this.students = [...manualStudents, ...this.qrScannedStudents];
                    } else {
                        this.showAlert('Error loading students: ' + data.message, 'error');
                    }
                } catch (error) {
                    console.error('Error loading students:', error);
                    this.showAlert('Error loading students: ' + error.message, 'error');
                } finally {
                    this.loading.students = false;
                }
            },

            // Step navigation
            nextStep() {
                if (this.currentStep < 4) {
                    this.currentStep++;
                }
            },

            previousStep() {
                if (this.currentStep > 1) {
                    this.currentStep--;
                }
            },

            // Student selection
            toggleStudent(studentId) {
                const index = this.selectedStudents.indexOf(studentId);
                if (index > -1) {
                    this.selectedStudents.splice(index, 1);
                } else {
                    this.selectedStudents.push(studentId);
                }
            },

            toggleAll(event) {
                if (event.target.checked) {
                    this.selectedStudents = this.students.map(student => student.id);
                } else {
                    this.selectedStudents = [];
                }
            },

            selectAllStudents() {
                this.selectedStudents = this.students.map(student => student.id);
            },

            deselectAllStudents() {
                this.selectedStudents = [];
            },

            // Bulk semester assignment
            async assignBulkSemester() {
                if (!this.assignment.semester_id || this.selectedStudents.length === 0) {
                    this.showAlert('Please select a semester and students', 'error');
                    return;
                }

                this.assignment.processing = true;

                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/students/assign_semester.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            student_ids: this.selectedStudents,
                            semester_id: parseInt(this.assignment.semester_id)
                        })
                    });

                    const result = await response.json();

                    // Replace the success message part in assignBulkSemester() method:
                    if (result.success) {
                        // Prepare modal data
                        const assignedSemester = this.semesters.find(s => s.id == this.assignment.semester_id);
                        const semesterDisplay = `Year ${assignedSemester.year} - Semester ${assignedSemester.semester_number} (${assignedSemester.academic_year})`;

                        const assignedStudentNames = this.students
                            .filter(student => this.selectedStudents.includes(student.id))
                            .map(student => student.full_name);

                        // Set modal data
                        this.assignmentModal = {
                            show: true,
                            semesterDisplay: semesterDisplay,
                            department: this.qrMode && this.qrContext.established ?
                                this.qrContext.department_name :
                                this.getSelectedDepartmentName(),
                            mode: this.qrMode && this.qrContext.established ?
                                (this.qrContext.mode === 'FULL' ? 'Full-time' : 'Part-time') :
                                (this.selection.mode === 'FULL' ? 'Full-time' : 'Part-time'),
                            batch: this.qrMode && this.qrContext.established ?
                                this.qrContext.batch :
                                this.selection.batch,
                            assignedCount: result.assigned_count,
                            studentNames: assignedStudentNames
                        };

                        // Update student data
                        this.students.forEach(student => {
                            if (this.selectedStudents.includes(student.id)) {
                                student.current_semester = `Year ${assignedSemester.year} - Semester ${assignedSemester.semester_number}`;
                            }
                        });

                        // Reset selections
                        this.selectedStudents = [];
                        this.assignment.semester_id = '';

                    } else {
                        this.showAlert('Error assigning semester: ' + result.message, 'error');
                    }
                } catch (error) {
                    console.error('Error assigning semester:', error);
                    this.showAlert('Error assigning semester: ' + error.message, 'error');
                } finally {
                    this.assignment.processing = false;
                }
            },

            // Helper methods
            getSelectedDepartmentName() {
                const dept = this.departments.find(d => d.id == this.selection.department_id);
                return dept ? dept.name : '';
            },

            // Show alert message
            showAlert(message, type = 'success') {
                this.alert = {
                    show: true,
                    type: type,
                    message: message
                };

                // Longer timeout for detailed messages
                const timeout = message.length > 200 ? 10000 : 5000;
                setTimeout(() => {
                    this.alert.show = false;
                }, timeout);
            }
        }
    }
</script>

<!-- Add QR Scanner CSS if needed -->
<style>
    [x-cloak] { display: none !important; }

    /* Enhanced animations and transitions */
    @keyframes pulse-ring {
        0% {
            transform: scale(0.8);
            opacity: 1;
        }
        100% {
            transform: scale(1.2);
            opacity: 0;
        }
    }

    .animate-pulse-ring {
        animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    /* Smooth transitions */
    .transition-all {
        transition-property: all;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 200ms;
    }

    /* Hover effects */
    button:hover {
        transform: translateY(-1px);
    }

    /* Focus styles */
    button:focus {
        outline: none;
        ring: 2px;
        ring-opacity: 50;
    }

    .content-fade-in {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes scanLine {
        0% { transform: translateY(-100%); opacity: 0; }
        50% { opacity: 1; }
        100% { transform: translateY(400%); opacity: 0; }
    }

    .scan-line {
        animation: scanLine 2s ease-in-out infinite;
    }

    /* Pulse animation for corner brackets */
    @keyframes bracketPulse {
        0%, 100% { opacity: 0.7; transform: scale(1); }
        50% { opacity: 1; transform: scale(1.1); }
    }

    .bracket-pulse {
        animation: bracketPulse 2s ease-in-out infinite;
    }

    /* Smooth transitions */
    .transition-all {
        transition-property: all;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 200ms;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* QR Scanner specific styles */
    video {
        border-radius: 8px;
        object-fit: cover;
    }

    /* Scanning animation */
    @keyframes scan-line {
        0% { transform: translateY(-100%); }
        100% { transform: translateY(400%); }
    }

    .scanning-line {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, #10B981, transparent);
        animation: scan-line 2s linear infinite;
    }
</style>