<?php
// public/admin/pages/role_config.php
require_once __DIR__ . '/../../../bootstrap.php';
use App\Auth\Middleware;

// Check if user has permission to manage roles
Middleware::check('DEPT_FULL');

if (!isset($_SERVER['HTTP_HX_REQUEST'])) {
    // Push the user back to the dashboard, preserving the target
    header('Location: ../dashboard.php?page=' . basename(__FILE__, '.php'));
    exit;
}
?>
<div x-data="roleConfiguration()" x-init="loadRoles()" class="min-h-screen py-6 content-fade-in">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Role Configuration</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Manage system roles and permissions</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="openCreatePermissionModal" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Permission
                    </button>
                    <button @click="openCreateRoleModal" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Role
                    </button>
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

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 text-gray-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Roles</dt>
                                <dd class="text-lg font-medium text-gray-900" x-text="roles.length"></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 text-gray-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Permissions</dt>
                                <dd class="text-lg font-medium text-gray-900" x-text="permissions.length"></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 text-gray-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Active Users</dt>
                                <dd class="text-lg font-medium text-gray-900" x-text="getTotalActiveUsers()"></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 text-gray-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Most Used Role</dt>
                                <dd class="text-lg font-medium text-gray-900" x-text="getMostUsedRole()"></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Roles Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Roles</h3>
                    <button @click="loadRoles" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>

            <!-- Loading State -->
            <div x-show="loading" x-cloak class="flex items-center justify-center py-12">
                <div class="flex items-center space-x-2">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
                    <span class="text-gray-500">Loading roles...</span>
                </div>
            </div>

            <!-- Table -->
            <div x-show="!loading" x-cloak class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="role in roles" :key="role.id">
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900" x-text="role.name"></div>
                                    <div class="text-sm text-gray-500" x-text="role.description || 'No description'"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="permission in role.permissions" :key="permission">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800" x-text="permission"></span>
                                    </template>
                                    <span x-show="role.permissions.length === 0" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">No permissions</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800" x-text="role.user_count + ' users'"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(role.created_at)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <!-- View Details Button -->
                                    <button @click="viewRoleDetails(role)" class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150" title="View Details">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>

                                    <!-- Edit Button -->
                                    <button @click="editRole(role)" class="text-yellow-600 hover:text-yellow-900 transition-colors duration-150" title="Edit Role">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <!-- Clone Button -->
                                    <button @click="cloneRole(role)" class="text-green-600 hover:text-green-900 transition-colors duration-150" title="Clone Role">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <button @click="deleteRole(role)" :disabled="role.user_count > 0" class="text-red-600 hover:text-red-900 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed" title="Delete Role">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty State -->
                    <tr x-show="roles.length === 0 && !loading" x-cloak>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="text-lg font-medium mb-2">No roles found</p>
                                <p class="text-sm">Create your first role to get started</p>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<!-- Create Role Modal -->
<div x-show="modals.createRole" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="modals.createRole" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 bg-opacity-75" @click="modals.createRole = false"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div x-show="modals.createRole" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <form @submit.prevent="createRole">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Create New Role</h3>
                            <div class="mt-4 space-y-4">
                                <!-- Role Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Role Name *</label>
                                    <input x-model="forms.createRole.name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter role name">
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea x-model="forms.createRole.description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="Optional description"></textarea>
                                </div>

                                <!-- Permissions -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                                    <div class="space-y-2 max-h-40 overflow-y-auto">
                                        <template x-for="permission in permissions" :key="permission.id">
                                            <label class="flex items-center">
                                                <input type="checkbox" :value="permission.name" x-model="forms.createRole.permissions" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <span class="ml-2 text-sm text-gray-900" x-text="permission.name"></span>
                                                <span class="ml-2 text-xs text-gray-500" x-text="permission.description ? '(' + permission.description + ')' : ''"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" :disabled="forms.createRole.submitting" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                        <span x-show="!forms.createRole.submitting">Create Role</span>
                        <span x-show="forms.createRole.submitting" class="flex items-center">
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                Creating...
                            </span>
                    </button>
                    <button type="button" @click="modals.createRole = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div x-show="modals.editRole" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="modals.editRole" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 bg-opacity-75" @click="modals.editRole = false"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div x-show="modals.editRole" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <form @submit.prevent="updateRole">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Edit Role</h3>
                            <div class="mt-4 space-y-4">
                                <!-- Role Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Role Name *</label>
                                    <input x-model="forms.editRole.name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter role name">
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea x-model="forms.editRole.description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="Optional description"></textarea>
                                </div>

                                <!-- Permissions -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                                    <div class="space-y-2 max-h-40 overflow-y-auto">
                                        <template x-for="permission in permissions" :key="permission.id">
                                            <label class="flex items-center">
                                                <input type="checkbox" :value="permission.name" x-model="forms.editRole.permissions" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <span class="ml-2 text-sm text-gray-900" x-text="permission.name"></span>
                                                <span class="ml-2 text-xs text-gray-500" x-text="permission.description ? '(' + permission.description + ')' : ''"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" :disabled="forms.editRole.submitting" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                        <span x-show="!forms.editRole.submitting">Update Role</span>
                        <span x-show="forms.editRole.submitting" class="flex items-center">
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                Updating...
                            </span>
                    </button>
                    <button type="button" @click="modals.editRole = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Permission Modal -->
<div x-show="modals.createPermission" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="modals.createPermission" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 bg-opacity-75" @click="modals.createPermission = false"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div x-show="modals.createPermission" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form @submit.prevent="createPermission">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Create New Permission</h3>
                            <div class="mt-4 space-y-4">
                                <!-- Permission Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Permission Name *</label>
                                    <input x-model="forms.createPermission.name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g., USER_CREATE">
                                    <p class="mt-1 text-xs text-gray-500">Use uppercase letters and underscores (e.g., USER_CREATE, DEPT_READ)</p>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea x-model="forms.createPermission.description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="Describe what this permission allows"></textarea>
                                </div>

                                <!-- Category -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                    <select x-model="forms.createPermission.category" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="General">General</option>
                                        <option value="Department">Department</option>
                                        <option value="Student">Student</option>
                                        <option value="User">User</option>
                                        <option value="System">System</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" :disabled="forms.createPermission.submitting" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                        <span x-show="!forms.createPermission.submitting">Create Permission</span>
                        <span x-show="forms.createPermission.submitting" class="flex items-center">
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                Creating...
                            </span>
                    </button>
                    <button type="button" @click="modals.createPermission = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Role Details Modal -->
<div x-show="modals.roleDetails" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="modals.roleDetails" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 bg-opacity-75" @click="modals.roleDetails = false"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div x-show="modals.roleDetails" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" x-text="'Role Details: ' + (roleDetails.name || '')"></h3>
                        <div class="mt-4 space-y-4">
                            <!-- Role Info -->
                            <div class="bg-gray-50 p-4 rounded-md">
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                                        <dd class="text-sm text-gray-900" x-text="roleDetails.name"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">User Count</dt>
                                        <dd class="text-sm text-gray-900" x-text="roleDetails.user_count + ' users'"></dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                                        <dd class="text-sm text-gray-900" x-text="roleDetails.description || 'No description'"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Created</dt>
                                        <dd class="text-sm text-gray-900" x-text="formatDate(roleDetails.created_at)"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Updated</dt>
                                        <dd class="text-sm text-gray-900" x-text="roleDetails.updated_at ? formatDate(roleDetails.updated_at) : 'Never'"></dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Permissions -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Permissions</h4>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="permission in roleDetails.permissions" :key="permission">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800" x-text="permission"></span>
                                    </template>
                                    <span x-show="roleDetails.permissions && roleDetails.permissions.length === 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">No permissions assigned</span>
                                </div>
                            </div>

                            <!-- Users -->
                            <div x-show="roleUsers && roleUsers.length > 0">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Assigned Users</h4>
                                <div class="bg-gray-50 rounded-md max-h-40 overflow-y-auto">
                                    <ul class="divide-y divide-gray-200">
                                        <template x-for="user in roleUsers" :key="user.id">
                                            <li class="px-3 py-2">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-sm text-gray-900" x-text="user.email"></span>
                                                    <div class="flex space-x-1">
                                                        <span :class="user.status == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" x-text="user.status == 1 ? 'Active' : 'Inactive'"></span>
                                                        <span :class="user.verified == 1 ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" x-text="user.verified == 1 ? 'Verified' : 'Unverified'"></span>
                                                    </div>
                                                </div>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button @click="modals.roleDetails = false" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm transition-colors duration-200">
                    Close
                </button>
            </div>
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
    function roleConfiguration() {
        return {
            // Data
            roles: [],
            permissions: [],
            statistics: [],
            loading: false,
            roleDetails: {},
            roleUsers: [],

            // Modals state
            modals: {
                createRole: false,
                editRole: false,
                createPermission: false,
                roleDetails: false,
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
                createRole: {
                    name: '',
                    description: '',
                    permissions: [],
                    submitting: false
                },
                editRole: {
                    role_id: '',
                    name: '',
                    description: '',
                    permissions: [],
                    submitting: false
                },
                createPermission: {
                    name: '',
                    description: '',
                    category: 'General',
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
                await this.loadRoles();
            },

            // Load roles and permissions from API
            async loadRoles() {
                this.loading = true;
                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/role_config/list.php`);
                    const data = await response.json();

                    if (data.success) {
                        this.roles = data.roles;
                        this.permissions = data.permissions;
                        this.statistics = data.statistics;
                        //console.log(data.statistics);
                    } else {
                        this.showAlert('Error loading roles: ' + data.message, 'error');
                    }
                } catch (error) {
                    this.showAlert('Error loading roles: ' + error.message, 'error');
                } finally {
                    this.loading = false;
                }
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
                return new Date(dateString).toLocaleDateString() + ' ' +
                    new Date(dateString).toLocaleTimeString();
            },

            // Get statistics
            getTotalActiveUsers() {
                return this.statistics.reduce((total, stat) => total + stat.user_count, 0);
            },

            getMostUsedRole() {
                if (this.statistics.length === 0) return 'None';
                const mostUsed = this.statistics.reduce((max, stat) =>
                    stat.user_count > max.user_count ? stat : max, this.statistics[0]);
                return mostUsed.name;
            },

            // Modal handlers
            openCreateRoleModal() {
                this.resetCreateRoleForm();
                this.modals.createRole = true;
            },

            resetCreateRoleForm() {
                this.forms.createRole = {
                    name: '',
                    description: '',
                    permissions: [],
                    submitting: false
                };
            },

            openCreatePermissionModal() {
                this.resetCreatePermissionForm();
                this.modals.createPermission = true;
            },

            resetCreatePermissionForm() {
                this.forms.createPermission = {
                    name: '',
                    description: '',
                    category: 'General',
                    submitting: false
                };
            },

            editRole(role) {
                this.forms.editRole = {
                    role_id: role.id,
                    name: role.name,
                    description: role.description || '',
                    permissions: [...role.permissions],
                    submitting: false
                };
                this.modals.editRole = true;
            },

            async viewRoleDetails(role) {
                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/role_config/details.php?role_id=${role.id}`);
                    const data = await response.json();

                    if (data.success) {
                        this.roleDetails = data.role;
                        this.roleUsers = data.users;
                        this.modals.roleDetails = true;
                    } else {
                        this.showAlert('Error loading role details: ' + data.message, 'error');
                    }
                } catch (error) {
                    this.showAlert('Error loading role details: ' + error.message, 'error');
                }
            },

            // API calls
            async createRole() {
                if (!this.forms.createRole.name.trim()) {
                    this.showAlert('Role name is required', 'error');
                    return;
                }

                this.forms.createRole.submitting = true;
                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/role_config/create.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            name: this.forms.createRole.name.trim(),
                            description: this.forms.createRole.description.trim(),
                            permissions: this.forms.createRole.permissions
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showAlert('Role created successfully', 'success');
                        this.modals.createRole = false;
                        await this.loadRoles();
                    } else {
                        this.showAlert('Error creating role: ' + result.message, 'error');
                    }
                } catch (error) {
                    this.showAlert('Error creating role: ' + error.message, 'error');
                } finally {
                    this.forms.createRole.submitting = false;
                }
            },

            async updateRole() {
                if (!this.forms.editRole.name.trim()) {
                    this.showAlert('Role name is required', 'error');
                    return;
                }

                this.forms.editRole.submitting = true;
                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/role_config/update.php`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            role_id: parseInt(this.forms.editRole.role_id),
                            name: this.forms.editRole.name.trim(),
                            description: this.forms.editRole.description.trim(),
                            permissions: this.forms.editRole.permissions
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showAlert('Role updated successfully', 'success');
                        this.modals.editRole = false;
                        await this.loadRoles();
                    } else {
                        this.showAlert('Error updating role: ' + result.message, 'error');
                    }
                } catch (error) {
                    this.showAlert('Error updating role: ' + error.message, 'error');
                } finally {
                    this.forms.editRole.submitting = false;
                }
            },

            async createPermission() {
                if (!this.forms.createPermission.name.trim()) {
                    this.showAlert('Permission name is required', 'error');
                    return;
                }

                this.forms.createPermission.submitting = true;
                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/role_config/create_permission.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            name: this.forms.createPermission.name.trim().toUpperCase(),
                            description: this.forms.createPermission.description.trim(),
                            category: this.forms.createPermission.category
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showAlert('Permission created successfully', 'success');
                        this.modals.createPermission = false;
                        await this.loadRoles(); // Reload to get updated permissions list
                    } else {
                        this.showAlert('Error creating permission: ' + result.message, 'error');
                    }
                } catch (error) {
                    this.showAlert('Error creating permission: ' + error.message, 'error');
                } finally {
                    this.forms.createPermission.submitting = false;
                }
            },

            // Clone role functionality
            cloneRole(role) {
                const newName = prompt(`Enter name for cloned role (original: ${role.name}):`);
                if (!newName || !newName.trim()) return;

                this.showConfirmation(
                    'Clone Role',
                    `Are you sure you want to clone "${role.name}" as "${newName.trim()}"?`,
                    'Clone',
                    async () => {
                        this.modals.confirm.loading = true;
                        try {
                            const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/role_config/clone.php`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    source_role_id: role.id,
                                    new_name: newName.trim(),
                                    description: `Cloned from ${role.name}`
                                })
                            });

                            const result = await response.json();

                            if (result.success) {
                                this.showAlert('Role cloned successfully', 'success');
                                await this.loadRoles();
                            } else {
                                this.showAlert('Error cloning role: ' + result.message, 'error');
                            }
                        } catch (error) {
                            this.showAlert('Error cloning role: ' + error.message, 'error');
                        } finally {
                            this.modals.confirm.loading = false;
                            this.modals.confirm.show = false;
                        }
                    }
                );
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

            deleteRole(role) {
                if (role.user_count > 0) {
                    this.showAlert(`Cannot delete role: ${role.user_count} user(s) are assigned to this role`, 'error');
                    return;
                }

                this.showConfirmation(
                    'Delete Role',
                    `Are you sure you want to delete the role "${role.name}"? This action cannot be undone.`,
                    'Delete',
                    async () => {
                        this.modals.confirm.loading = true;
                        try {
                            const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/role_config/delete.php?role_id=${role.id}`, {
                                method: 'DELETE'
                            });

                            const result = await response.json();

                            if (result.success) {
                                this.showAlert('Role deleted successfully', 'success');
                                await this.loadRoles();
                            } else {
                                this.showAlert('Error deleting role: ' + result.message, 'error');
                            }
                        } catch (error) {
                            this.showAlert('Error deleting role: ' + error.message, 'error');
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