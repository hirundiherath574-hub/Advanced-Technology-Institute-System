<?php
// public/admin/verify-account.php
require_once __DIR__ . '/../../bootstrap.php';
use App\Models\UserService;

// Get token from URL
$token = $_GET['token'] ?? null;

if (!$token) {
    $error = "No verification token provided.";
} else {
    $userService = new UserService();
    $verifyResult = $userService->verifyAccountWithToken($token);

    if (!$verifyResult['success']) {
        $error = $verifyResult['message'];
    } else {
        $user = $verifyResult['user'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($user)) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirmPassword)) {
        $formError = "Please fill in all fields.";
    } elseif (strlen($password) < 8) {
        $formError = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirmPassword) {
        $formError = "Passwords do not match.";
    } else {
        $completeResult = $userService->completeVerification($token, $password);
        if ($completeResult['success']) {
            $success = true;
            $successMessage = $completeResult['message'];
        } else {
            $formError = $completeResult['message'];
        }
    }
}

function url($path) {
    return rtrim($_ENV['APP_URL'], '/') . $path;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account - University Management System</title>
    <!-- Alpine -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <!-- Styles & fonts -->
    <link rel="stylesheet" href="<?= url('/assets/css/dist/styles.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-indigo-100">
                <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Account Verification
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Complete your account setup
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-xl p-8">
            <?php if (isset($error)): ?>
                <!-- Error State -->
                <div class="text-center">
                    <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-full bg-red-100 mb-4">
                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Verification Failed</h3>
                    <p class="text-red-600 mb-6"><?php echo htmlspecialchars($error); ?></p>
                    <a href="/admin/login.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        Return to Login
                    </a>
                </div>

            <?php elseif (isset($success) && $success): ?>
                <!-- Success State -->
                <div class="text-center">
                    <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-full bg-green-100 mb-4">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Account Verified Successfully!</h3>
                    <p class="text-green-600 mb-6"><?php echo htmlspecialchars($successMessage); ?></p>
                    <a href="/admin/login.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                        Login to Your Account
                    </a>
                </div>

            <?php else: ?>
                <!-- Password Setup Form -->
                <div x-data="passwordSetup()">
                    <!-- User Info -->
                    <div class="mb-6 p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                        <h3 class="text-sm font-medium text-indigo-800 mb-1">Account Details</h3>
                        <p class="text-sm text-indigo-600"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="text-sm text-indigo-600"><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
                    </div>

                    <!-- Form Errors -->
                    <?php if (isset($formError)): ?>
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-red-800"><?php echo htmlspecialchars($formError); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Password Setup Form -->
                    <form method="POST" @submit="handleSubmit" class="space-y-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                Create Password *
                            </label>
                            <div class="relative">
                                <input
                                    id="password"
                                    name="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    x-model="password"
                                    @input="validatePassword"
                                    required
                                    minlength="8"
                                    class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Enter your new password"
                                >
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                >
                                    <svg x-show="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Password Strength Indicator -->
                            <div class="mt-2">
                                <div class="flex items-center space-x-2 text-xs">
                                    <div class="flex space-x-1">
                                        <div :class="passwordStrength >= 1 ? 'bg-red-500' : 'bg-gray-200'" class="w-2 h-2 rounded"></div>
                                        <div :class="passwordStrength >= 2 ? 'bg-yellow-500' : 'bg-gray-200'" class="w-2 h-2 rounded"></div>
                                        <div :class="passwordStrength >= 3 ? 'bg-green-500' : 'bg-gray-200'" class="w-2 h-2 rounded"></div>
                                        <div :class="passwordStrength >= 4 ? 'bg-green-600' : 'bg-gray-200'" class="w-2 h-2 rounded"></div>
                                    </div>
                                    <span :class="getPasswordStrengthColor()" x-text="getPasswordStrengthText()"></span>
                                </div>
                                <div class="mt-1 text-xs text-gray-600">
                                    <p>Password must contain at least:</p>
                                    <ul class="list-disc list-inside mt-1 space-y-1">
                                        <li :class="password.length >= 8 ? 'text-green-600' : 'text-gray-600'">8 characters</li>
                                        <li :class="/[A-Z]/.test(password) ? 'text-green-600' : 'text-gray-600'">One uppercase letter</li>
                                        <li :class="/[a-z]/.test(password) ? 'text-green-600' : 'text-gray-600'">One lowercase letter</li>
                                        <li :class="/[0-9]/.test(password) ? 'text-green-600' : 'text-gray-600'">One number</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">
                                Confirm Password *
                            </label>
                            <div class="relative">
                                <input
                                    id="confirm_password"
                                    name="confirm_password"
                                    :type="showConfirmPassword ? 'text' : 'password'"
                                    x-model="confirmPassword"
                                    @input="validateConfirmPassword"
                                    required
                                    minlength="8"
                                    class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Confirm your new password"
                                >
                                <button
                                    type="button"
                                    @click="showConfirmPassword = !showConfirmPassword"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                >
                                    <svg x-show="!showConfirmPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="showConfirmPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                                    </svg>
                                </button>
                            </div>
                            <div x-show="confirmPassword && !passwordsMatch" class="mt-1 text-xs text-red-600">
                                Passwords do not match
                            </div>
                            <div x-show="confirmPassword && passwordsMatch" class="mt-1 text-xs text-green-600">
                                Passwords match ✓
                            </div>
                        </div>

                        <div class="pt-4">
                            <button
                                type="submit"
                                :disabled="!canSubmit"
                                :class="canSubmit ? 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500' : 'bg-gray-400 cursor-not-allowed'"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200"
                            >
                                <span x-show="!submitting">Complete Account Setup</span>
                                <span x-show="submitting" class="flex items-center">
                                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                        Setting up account...
                                    </span>
                            </button>
                        </div>
                    </form>

                    <!-- Security Notice -->
                    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Security Notice</h3>
                                <p class="mt-1 text-sm text-yellow-700">
                                    Please create a strong password that you haven't used elsewhere. This link will expire after use for security purposes.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function passwordSetup() {
        return {
            password: '',
            confirmPassword: '',
            showPassword: false,
            showConfirmPassword: false,
            submitting: false,
            passwordStrength: 0,

            get passwordsMatch() {
                return this.password && this.confirmPassword && this.password === this.confirmPassword;
            },

            get canSubmit() {
                return this.password.length >= 8 &&
                    this.passwordsMatch &&
                    this.passwordStrength >= 2 &&
                    !this.submitting;
            },

            validatePassword() {
                let strength = 0;

                // Length check
                if (this.password.length >= 8) strength++;

                // Uppercase check
                if (/[A-Z]/.test(this.password)) strength++;

                // Lowercase check
                if (/[a-z]/.test(this.password)) strength++;

                // Number check
                if (/[0-9]/.test(this.password)) strength++;

                this.passwordStrength = strength;
            },

            validateConfirmPassword() {
                // This will trigger the passwordsMatch computed property
            },

            getPasswordStrengthText() {
                const texts = ['Very Weak', 'Weak', 'Fair', 'Strong'];
                return texts[this.passwordStrength - 1] || 'Very Weak';
            },

            getPasswordStrengthColor() {
                const colors = ['text-red-600', 'text-yellow-600', 'text-blue-600', 'text-green-600'];
                return colors[this.passwordStrength - 1] || 'text-red-600';
            },

            handleSubmit() {
                if (!this.canSubmit) {
                    return false;
                }

                this.submitting = true;
                return true;
            }
        }
    }
</script>
</body>
</html>