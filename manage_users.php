// cli/manage_users.php
#!/usr/bin/env php
<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Database\Connection;
use App\Models\UserService;
use Delight\Auth\Auth;

// Check command line arguments
if ($argc < 2) {
    showUsage();
    exit(1);
}

$command = $argv[1];
$userService = new UserService();

switch ($command) {
    case 'list':
        listUsers();
        break;
    case 'create':
        createUser($argv);
        break;
    case 'update-role':
        updateUserRole($argv);
        break;
    case 'activate':
        toggleUserStatus($argv, 'activate');
        break;
    case 'deactivate':
        toggleUserStatus($argv, 'deactivate');
        break;
    case 'delete':
        deleteUser($argv);
        break;
    case 'reset-password':
        resetPassword($argv);
        break;
    case 'show-roles':
        showRoles();
        break;
    default:
        echo "Unknown command: $command\n";
        showUsage();
        exit(1);
}

function showUsage() {
    echo "Usage: php manage_users.php <command> [options]\n\n";
    echo "Commands:\n";
    echo "  list                                List all admin users\n";
    echo "  create <email> <password> <role>    Create new admin user\n";
    echo "  update-role <email> <role>          Update user role\n";
    echo "  activate <email>                    Activate user account\n";
    echo "  deactivate <email>                  Deactivate user account\n";
    echo "  delete <email>                      Delete user account\n";
    echo "  reset-password <email> <password>   Reset user password\n";
    echo "  show-roles                          Show available roles\n\n";
    echo "Available roles: registrar, staff, super_admin\n";
}

function listUsers() {
    global $userService;
    $users = $userService->getAllAdminUsers();

    echo "System Administrator Users:\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-5s %-30s %-15s %-10s %-20s\n", "ID", "Email", "Roles", "Status", "Registered");
    echo str_repeat("-", 80) . "\n";

    foreach ($users as $user) {
        $roles = implode(', ', $user['roles']);
        $status = $user['status'] == 1 ? 'Active' : 'Inactive';
        $registered = date('Y-m-d H:i', strtotime($user['registered']));

        printf("%-5s %-30s %-15s %-10s %-20s\n",
            $user['id'],
            $user['email'],
            $roles ?: 'No role',
            $status,
            $registered
        );
    }
}

function createUser($argv) {
    global $userService;

    if (count($argv) < 5) {
        echo "Usage: php manage_users.php create <email> <password> <role>\n";
        exit(1);
    }

    $email = $argv[2];
    $password = $argv[3];
    $roleName = $argv[4];

    // Get role ID
    $roles = $userService->getAllRoles();
    $roleId = null;
    foreach ($roles as $role) {
        if ($role['name'] === $roleName) {
            $roleId = $role['id'];
            break;
        }
    }

    if (!$roleId) {
        echo "Error: Role '$roleName' not found.\n";
        echo "Available roles: " . implode(', ', array_column($roles, 'name')) . "\n";
        exit(1);
    }

    $result = $userService->createAdminUser($email, $password, $roleId);

    if ($result['success']) {
        echo "User '$email' created successfully with role '$roleName'.\n";
    } else {
        echo "Error creating user: " . $result['message'] . "\n";
        exit(1);
    }
}

function updateUserRole($argv) {
    global $userService;

    if (count($argv) < 4) {
        echo "Usage: php manage_users.php update-role <email> <role>\n";
        exit(1);
    }

    $email = $argv[2];
    $roleName = $argv[3];

    // Get user ID
    $userId = getUserIdByEmail($email);
    if (!$userId) {
        echo "Error: User '$email' not found.\n";
        exit(1);
    }

    // Get role ID
    $roles = $userService->getAllRoles();
    $roleId = null;
    foreach ($roles as $role) {
        if ($role['name'] === $roleName) {
            $roleId = $role['id'];
            break;
        }
    }

    if (!$roleId) {
        echo "Error: Role '$roleName' not found.\n";
        exit(1);
    }

    $result = $userService->updateUserRole($userId, $roleId);

    if ($result['success']) {
        echo "User '$email' role updated to '$roleName'.\n";
    } else {
        echo "Error updating user role: " . $result['message'] . "\n";
        exit(1);
    }
}

function toggleUserStatus($argv, $action) {
    global $userService;

    if (count($argv) < 3) {
        echo "Usage: php manage_users.php $action <email>\n";
        exit(1);
    }

    $email = $argv[2];
    $userId = getUserIdByEmail($email);

    if (!$userId) {
        echo "Error: User '$email' not found.\n";
        exit(1);
    }

    if ($action === 'activate') {
        $result = $userService->activateUser($userId);
    } else {
        $result = $userService->deactivateUser($userId);
    }

    if ($result['success']) {
        echo "User '$email' has been {$action}d.\n";
    } else {
        echo "Error {$action}ing user: " . $result['message'] . "\n";
        exit(1);
    }
}

function deleteUser($argv) {
    global $userService;

    if (count($argv) < 3) {
        echo "Usage: php manage_users.php delete <email>\n";
        exit(1);
    }

    $email = $argv[2];
    $userId = getUserIdByEmail($email);

    if (!$userId) {
        echo "Error: User '$email' not found.\n";
        exit(1);
    }

    echo "Are you sure you want to delete user '$email'? (yes/no): ";
    $confirmation = trim(fgets(STDIN));

    if (strtolower($confirmation) !== 'yes') {
        echo "Operation cancelled.\n";
        exit(0);
    }

    $result = $userService->deleteUser($userId);

    if ($result['success']) {
        echo "User '$email' has been deleted.\n";
    } else {
        echo "Error deleting user: " . $result['message'] . "\n";
        exit(1);
    }
}

function resetPassword($argv) {
    global $userService;

    if (count($argv) < 4) {
        echo "Usage: php manage_users.php reset-password <email> <new_password>\n";
        exit(1);
    }

    $email = $argv[2];
    $newPassword = $argv[3];

    $userId = getUserIdByEmail($email);
    if (!$userId) {
        echo "Error: User '$email' not found.\n";
        exit(1);
    }

    if (strlen($newPassword) < 8) {
        echo "Error: Password must be at least 8 characters long.\n";
        exit(1);
    }

    $result = $userService->updateUserPassword($userId, $newPassword);

    if ($result['success']) {
        echo "Password for user '$email' has been reset.\n";
    } else {
        echo "Error resetting password: " . $result['message'] . "\n";
        exit(1);
    }
}

function showRoles() {
    global $userService;
    $roles = $userService->getAllRoles();

    echo "Available Roles:\n";
    echo str_repeat("-", 60) . "\n";
    printf("%-15s %s\n", "Role Name", "Permissions");
    echo str_repeat("-", 60) . "\n";

    foreach ($roles as $role) {
        $permissions = implode(', ', $role['permissions']);
        printf("%-15s %s\n", $role['name'], $permissions ?: 'No permissions');
    }
}

function getUserIdByEmail($email) {
    $db = Connection::pdo();
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetchColumn();
}
?>