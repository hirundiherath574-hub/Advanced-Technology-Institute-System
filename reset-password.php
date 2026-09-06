<?php
// public/auth/reset-password.php
require_once __DIR__ . '/../../bootstrap.php';

// Get app configuration
$appName = $_ENV['APP_NAME'] ?? 'Admin System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo htmlspecialchars($appName); ?></title>
    <script src="../js/config.js.php"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-gray-50">
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Reset Your Password
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Create a new secure password for your administrator account
            </p>
        </div>

        <div x-data="passwordReset()" class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <!-- Alert Messages -->
            <div x-show="alert.show" x-cloak class="mb-4">
                <div :class="alert.type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'"
                     class="rounded-md border p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm font-medium" x-text="alert.message"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Token Verification Loading -->
            <div x-show="verifying" x-cloak class="text-center py-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
                <p class="mt-2 text-sm text-gray-500">Verifying reset token...</p>
            </div>

            <!-- Password Reset Form -->
            <form x-show="tokenValid && !verifying && !resetComplete" x-cloak @submit.prevent="resetPassword" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Email Address
                    </label>
                    <input :value="userInfo.email" type="email" readonly
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Role
                    </label>
                    <input :value="userInfo.role" type="text" readonly
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        New Password *
                    </label>
                    <input x-model="password" type="password" required minlength="8"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Minimum 8 characters">
                    <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters long</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Confirm Password *
                    </label>
                    <input x-model="confirmPassword" type="password" required minlength="8"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Confirm your new password">
                    <p x-show="password && confirmPassword && password !== confirmPassword"
                       class="mt-1 text-xs text-red-500">Passwords do not match</p>
                </div>

                <div>
                    <button type="submit"
                            :disabled="submitting || password !== confirmPassword || !password || password.length < 8"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!submitting">Reset Password</span>
                        <span x-show="submitting" class="flex items-center">
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                Resetting...
                            </span>
                    </button>
                </div>
            </form>

            <!-- Success Message -->
            <div x-show="resetComplete" x-cloak class="text-center py-4">
                <div class="text-green-600 mb-4">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Password Reset Successful!</h3>
                <p class="text-sm text-gray-600 mb-4">Your password has been updated successfully.</p>
                <a href="/public/auth/login.php"
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    Login Now
                </a>
            </div>

            <!-- Invalid Token Message -->
            <div x-show="!tokenValid && !verifying" x-cloak class="text-center py-4">
                <div class="text-red-600 mb-4">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Invalid or Expired Link</h3>
                <p class="text-sm text-gray-600 mb-4">This password reset link is invalid or has expired.</p>
                <p class="text-sm text-gray-600">Please contact your administrator for a new reset link.</p>
            </div>
        </div>
    </div>
</div>

<script>
    function passwordReset() {
        return {
            verifying: true,
            tokenValid: false,
            resetComplete: false,
            submitting: false,
            password: '',
            confirmPassword: '',
            userInfo: {
                email: '',
                role: ''
            },
            alert: {
                show: false,
                type: 'success',
                message: ''
            },

            async init() {
                await this.verifyToken();
            },

            async verifyToken() {
                const urlParams = new URLSearchParams(window.location.search);
                const token = urlParams.get('token');

                if (!token) {
                    this.verifying = false;
                    return;
                }

                try {
                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/auth/verify_reset_token.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            token: token
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.tokenValid = true;
                        this.userInfo = result.user;
                    } else {
                        this.showAlert(result.message, 'error');
                    }
                } catch (error) {
                    console.error('Error verifying token:', error);
                    this.showAlert('Error verifying reset token', 'error');
                } finally {
                    this.verifying = false;
                }
            },

            async resetPassword() {
                if (this.password !== this.confirmPassword) {
                    this.showAlert('Passwords do not match', 'error');
                    return;
                }

                if (this.password.length < 8) {
                    this.showAlert('Password must be at least 8 characters long', 'error');
                    return;
                }

                this.submitting = true;
                try {
                    const urlParams = new URLSearchParams(window.location.search);
                    const token = urlParams.get('token');

                    const response = await fetch(`${window.APP_CONFIG.API_BASE_URL}/auth/complete_reset.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            token: token,
                            password: this.password
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.resetComplete = true;
                        this.showAlert('Password reset successful!', 'success');
                    } else {
                        this.showAlert(result.message, 'error');
                    }
                } catch (error) {
                    console.error('Error resetting password:', error);
                    this.showAlert('Error resetting password', 'error');
                } finally {
                    this.submitting = false;
                }
            },

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
            }
        }
    }
</script>
</body>
</html>