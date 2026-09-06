<?php
// public/admin/pages/admin_users.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Auth\Middleware;

// Check if user has permission to manage users
Middleware::check('DEPT_FULL');

if (!isset($_SERVER['HTTP_HX_REQUEST'])) {
    // Push the user back to the dashboard, preserving the target
    header('Location: ../dashboard.php?page=' . basename(__FILE__, '.php'));
    exit;
}
?>
<div x-data="userManagement()" x-init="loadUsers()" class="min-h-screen py-6 content-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">System Administrator Management</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Manage system administrator accounts and roles</p>
                </div>
                <button @click="openCreateModal" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add New Admin
                </button>
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
                        <p :class="alert.type === 'success' ? 'text-green-800' : 'text-red-800'" class="text-sm font-medium" x-text="alert.message"></p>
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

        <!-- Filters and Search -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <div class="relative">
                            <input x-model="filters.search" @input="filterUsers" type="text" placeholder="Search by email..." class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Role Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select x-model="filters.role" @change="filterUsers" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Roles</option>
                            <option value="super_admin">Super Admin</option>
                            <option value="registrar">Registrar</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select x-model="filters.status" @change="filterUsers" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <!-- Refresh Button -->
                    <div class="flex items-end">
                        <button @click="loadUsers" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Users (<span x-text="filteredUsers.length"></span>)
                </h3>
            </div>

            <!-- Loading State -->
            <div x-show="loading" x-cloak class="flex items-center justify-center py-12">
                <div class="flex items-center space-x-2">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
                    <span class="text-gray-500">Loading users...</span>
                </div>
            </div>

            <!-- Table -->
            <div x-show="!loading" x-cloak class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="user in filteredUsers" :key="user.id">
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900" x-text="user.email"></div>
                                        <div class="text-sm text-gray-500">ID: <span x-text="user.id"></span></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="role in user.roles" :key="role">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800" x-text="role"></span>
                                    </template>
                                    <span x-show="user.roles.length === 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">No role</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    <!-- Account Status -->
                                    <span :class="user.status == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                        <span x-text="user.status == 1 ? 'Active' : 'Inactive'"></span>
                                    </span>

                                    <!-- Verification Status -->
                                    <span x-show="user.status == 1" :class="user.verified == 1 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                        <span x-text="user.verified == 1 ? 'Verified' : 'Pending Setup'"></span>
                                    </span>

                                    <!-- Waiting for activation -->
                                    <span x-show="user.status == 0 && user.roles.length > 0" class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Awaiting Activation
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(user.registered)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="user.last_login ? formatDate(user.last_login) : 'Never'"></td>
                            <!-- Action buttons section - replace in your table -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">

                                    <!-- Resend Verification Button (for activated but unverified users) -->
                                    <button x-show="user.status == 1 && user.verified == 0"
                                            @click="resendVerification(user)"
                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-150"
                                            title="Resend Verification Email">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </button>

                                    <!-- Edit Button -->
                                    <button @click="editUser(user)"
                                            class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150"
                                            title="Edit User">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </button>

                                    <!-- Reset Password Button (sends email link) -->
                                    <button @click="resetPassword(user)"
                                            class="text-orange-600 hover:text-orange-900 transition-colors duration-150"
                                            title="Send Password Reset Link">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                        </svg>
                                    </button>

                                    <!-- Toggle Status Button -->
                                    <button @click="toggleUserStatus(user)"
                                            :class="user.status == 1 ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900'"
                                            class="transition-colors duration-150"
                                            :title="user.status == 1 ? 'Deactivate User' : 'Activate User'">
                                        <svg x-show="user.status == 1" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9v6m-4.5 0V9M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        <svg x-show="user.status == 0" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z" />
                                        </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <button @click="deleteUser(user)"
                                            class="text-red-600 hover:text-red-900 transition-colors duration-150"
                                            title="Delete User">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty State -->
                    <tr x-show="filteredUsers.length === 0 && !loading" x-cloak>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="text-lg font-medium mb-2">No users found</p>
                                <p class="text-sm">Try adjusting your search or filter criteria</p>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <div x-show="modals.create" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modals.create" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity">
                <div class="absolute inset-0 bg-gray-500 bg-opacity-75" @click="modals.create = false"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="modals.create" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form @submit.prevent="createUser">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Create New Admin User</h3>
                                <p class="mt-2 text-sm text-gray-500">The user will receive an email notification but must be manually activated before they can set their password.</p>
                                <div class="mt-4 space-y-4">
                                    <!-- Email -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                                        <input x-model="forms.create.email" type="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="admin@university.edu">
                                    </div>

                                    <!-- Role -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                                        <select x-model="forms.create.role_id" @change="updateRolePermissions('create')" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Select Role</option>
                                            <template x-for="role in roles" :key="role.id">
                                                <option :value="role.id" x-text="role.name"></option>
                                            </template>
                                        </select>
                                        <p x-show="forms.create.permissions" x-text="forms.create.permissions" class="mt-1 text-xs text-gray-500"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" :disabled="forms.create.submitting" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                            <span x-show="!forms.create.submitting">Create User</span>
                            <span x-show="forms.create.submitting" class="flex items-center">
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                Creating...
                            </span>
                        </button>
                        <button type="button" @click="modals.create = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div x-show="modals.edit" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modals.edit" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity">
                <div class="absolute inset-0 bg-gray-500 bg-opacity-75" @click="modals.edit = false"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="modals.edit" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form @submit.prevent="updateUser">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Edit User</h3>
                                <div class="mt-4 space-y-4">
                                    <!-- Email (readonly) -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                        <input x-model="forms.edit.email" type="email" readonly class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" tabindex="-1">
                                    </div>

                                    <!-- Role -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                                        <select x-model="forms.edit.role_id" @change="updateRolePermissions('edit')" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Select Role</option>
                                            <template x-for="role in roles" :key="role.id">
                                                <option :value="role.id" x-text="role.name"></option>
                                            </template>
                                        </select>
                                        <p x-show="forms.edit.permissions" x-text="forms.edit.permissions" class="mt-1 text-xs text-gray-500"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" :disabled="forms.edit.submitting" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                            <span x-show="!forms.edit.submitting">Update User</span>
                            <span x-show="forms.edit.submitting" class="flex items-center">
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                Updating...
                            </span>
                        </button>
                        <button type="button" @click="modals.edit = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div x-show="modals.confirm.show" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modals.confirm.show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity">
                <div class="absolute inset-0 bg-gray-500 bg-opacity-75" @click="modals.confirm.show = false"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="modals.confirm.show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" x-text="modals.confirm.title"></h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" x-text="modals.confirm.message"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="modals.confirm.onConfirm()" :disabled="modals.confirm.loading" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                        <span x-show="!modals.confirm.loading" x-text="modals.confirm.confirmText"></span>
                        <span x-show="modals.confirm.loading" class="flex items-center">
                            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                            Processing...
                        </span>
                    </button>
                    <button @click="modals.confirm.show = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function userManagement() {
        return {
            // Data
            users: [],
            filteredUsers: [],
            roles: [],
            loading: false,

            // Filters
            filters: {
                search: '',
                role: '',
                status: ''
            },

            // Modals state
            modals: {
                create: false,
                edit: false,
                confirm: {
                    show: false,
                    title: '',
                    message: '',
                    confirmText: 'Confirm',
                    loading: false,
                    onConfirm: () => {}
                }
            },

            // Forms data
            forms: {
                create: {
                    email: '',
                    role_id: '',
                    permissions: '',
                    submitting: false
                },
                edit: {
                    user_id: '',
                    email: '',
                    role_id: '',
                    permissions: '',
                    submitting: false
                }
            },

            // Alert system
            alert: {
                show: false,
                type: 'success',
                message: ''
            },

            // Initialize
            async init() {
                await this.loadUsers();
            },

            // Load users from API
            async loadUsers() {
                this.loading = true;
                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/admin_users/list.php`);
                    const data = await response.json();

                    if (data.success) {
                        this.users = data.users;
                        this.roles = data.roles;
                        this.filterUsers();
                    } else {
                        this.showAlert('Error loading users: ' + data.message, 'error');
                    }
                } catch (error) {
                    console.error('Error loading users:', error);
                    this.showAlert('Error loading users: ' + error.message, 'error');
                } finally {
                    this.loading = false;
                }
            },

            // Filter users based on search and filters
            filterUsers() {
                let filtered = this.users;

                // Search filter
                if (this.filters.search) {
                    const searchTerm = this.filters.search.toLowerCase();
                    filtered = filtered.filter(user =>
                        user.email.toLowerCase().includes(searchTerm) ||
                        (user.username && user.username.toLowerCase().includes(searchTerm))
                    );
                }

                // Role filter
                if (this.filters.role) {
                    filtered = filtered.filter(user =>
                        user.roles && user.roles.includes(this.filters.role)
                    );
                }

                // Status filter
                if (this.filters.status !== '') {
                    filtered = filtered.filter(user =>
                        user.status == this.filters.status
                    );
                }

                this.filteredUsers = filtered;
            },

            // Show alert message
            showAlert(message, type = 'success') {
                this.alert = {
                    show: true,
                    type: type,
                    message: message
                };

                // Auto hide after 5 seconds
                setTimeout(() => {
                    this.alert.show = false;
                }, 5000);
            },

            // Format date for display
            formatDate(dateString) {
                if (!dateString) return 'N/A';
                try {
                    const date = new Date(dateString);
                    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                } catch (e) {
                    return 'Invalid Date';
                }
            },

            // Update role permissions display
            updateRolePermissions(formType) {
                const roleId = this.forms[formType].role_id;
                if (!roleId) {
                    this.forms[formType].permissions = '';
                    return;
                }

                const role = this.roles.find(r => r.id == roleId);
                if (role && role.permissions && role.permissions.length > 0) {
                    this.forms[formType].permissions = `Permissions: ${role.permissions.join(', ')}`;
                } else {
                    this.forms[formType].permissions = 'No permissions assigned';
                }
            },

            // Modal handlers
            openCreateModal() {
                this.resetCreateForm();
                this.modals.create = true;
            },

            resetCreateForm() {
                this.forms.create = {
                    email: '',
                    role_id: '',
                    permissions: '',
                    submitting: false
                };
            },

            editUser(user) {
                this.forms.edit = {
                    user_id: user.id,
                    email: user.email,
                    role_id: user.role_ids && user.role_ids.length > 0 ? user.role_ids[0] : '',
                    permissions: '',
                    submitting: false
                };
                this.updateRolePermissions('edit');
                this.modals.edit = true;
            },

            // Password reset via email
            async resetPassword(user) {
                this.showConfirmation(
                    'Reset Password',
                    `Send password reset link to ${user.email}? The user will receive an email with instructions to set a new password.`,
                    'Send Reset Link',
                    async () => {
                        this.modals.confirm.loading = true;
                        try {
                            const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/admin_users/reset_password.php`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    user_id: user.id
                                })
                            });

                            const result = await response.json();

                            if (result.success) {
                                this.showAlert('Password reset link sent successfully', 'success');
                            } else {
                                this.showAlert('Error sending reset link: ' + result.message, 'error');
                            }
                        } catch (error) {
                            console.error('Error sending reset link:', error);
                            this.showAlert('Error sending reset link: ' + error.message, 'error');
                        } finally {
                            this.modals.confirm.loading = false;
                            this.modals.confirm.show = false;
                        }
                    }
                );
            },

            // Resend verification email
            async resendVerification(user) {
                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/admin_users/resend_verification.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            user_id: user.id
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showAlert('Verification email sent successfully', 'success');
                    } else {
                        this.showAlert('Error sending verification email: ' + result.message, 'error');
                    }
                } catch (error) {
                    console.error('Error sending verification:', error);
                    this.showAlert('Error sending verification email: ' + error.message, 'error');
                }
            },

            // API calls
            async createUser() {
                if (!this.forms.create.email || !this.forms.create.role_id) {
                    this.showAlert('Please fill in all required fields', 'error');
                    return;
                }

                // Basic email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(this.forms.create.email)) {
                    this.showAlert('Please enter a valid email address', 'error');
                    return;
                }

                this.forms.create.submitting = true;
                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/admin_users/create.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            email: this.forms.create.email.trim(),
                            role_id: parseInt(this.forms.create.role_id)
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showAlert('User created successfully. They will receive a notification email and must be activated before they can set their password.', 'success');
                        this.modals.create = false;
                        await this.loadUsers();
                    } else {
                        this.showAlert('Error creating user: ' + result.message, 'error');
                    }
                } catch (error) {
                    console.error('Error creating user:', error);
                    this.showAlert('Error creating user: ' + error.message, 'error');
                } finally {
                    this.forms.create.submitting = false;
                }
            },

            async updateUser() {
                if (!this.forms.edit.role_id) {
                    this.showAlert('Please select a role', 'error');
                    return;
                }

                this.forms.edit.submitting = true;
                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/admin_users/update.php`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            user_id: parseInt(this.forms.edit.user_id),
                            role_id: parseInt(this.forms.edit.role_id)
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showAlert('User updated successfully', 'success');
                        this.modals.edit = false;
                        await this.loadUsers();
                    } else {
                        this.showAlert('Error updating user: ' + result.message, 'error');
                    }
                } catch (error) {
                    console.error('Error updating user:', error);
                    this.showAlert('Error updating user: ' + error.message, 'error');
                } finally {
                    this.forms.edit.submitting = false;
                }
            },

            // Confirmation dialog for destructive actions
            showConfirmation(title, message, confirmText, onConfirm) {
                this.modals.confirm = {
                    show: true,
                    title: title,
                    message: message,
                    confirmText: confirmText,
                    loading: false,
                    onConfirm: onConfirm
                };
            },

            // Toggle user status (activate/deactivate)
            toggleUserStatus(user) {
                const action = user.status == 1 ? 'deactivate' : 'activate';
                const actionText = action.charAt(0).toUpperCase() + action.slice(1);

                this.showConfirmation(
                    `${actionText} User`,
                    `Are you sure you want to ${action} ${user.email}?`,
                    actionText,
                    async () => {
                        this.modals.confirm.loading = true;
                        try {
                            const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/admin_users/toggle_status.php`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    user_id: user.id,
                                    action: action
                                })
                            });

                            const result = await response.json();

                            if (result.success) {
                                this.showAlert(`User ${action}d successfully`, 'success');
                                await this.loadUsers();
                            } else {
                                this.showAlert(`Error ${action}ing user: ` + result.message, 'error');
                            }
                        } catch (error) {
                            console.error(`Error ${action}ing user:`, error);
                            this.showAlert(`Error ${action}ing user: ` + error.message, 'error');
                        } finally {
                            this.modals.confirm.loading = false;
                            this.modals.confirm.show = false;
                        }
                    }
                );
            },

            // Delete user
            deleteUser(user) {
                this.showConfirmation(
                    'Delete User',
                    `Are you sure you want to delete ${user.email}? This will remove all roles and deactivate the account. This action cannot be undone.`,
                    'Delete',
                    async () => {
                        this.modals.confirm.loading = true;
                        try {
                            const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/admin_users/delete.php?user_id=${user.id}`, {
                                method: 'DELETE'
                            });

                            const result = await response.json();

                            if (result.success) {
                                this.showAlert('User deleted successfully', 'success');
                                await this.loadUsers();
                            } else {
                                this.showAlert('Error deleting user: ' + result.message, 'error');
                            }
                        } catch (error) {
                            console.error('Error deleting user:', error);
                            this.showAlert('Error deleting user: ' + error.message, 'error');
                        } finally {
                            this.modals.confirm.loading = false;
                            this.modals.confirm.show = false;
                        }
                    }
                );
            }
        }
    }
</script>