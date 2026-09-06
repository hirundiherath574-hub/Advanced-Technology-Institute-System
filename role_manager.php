<?php
// cli/role_manager.php
require_once __DIR__ . '/../bootstrap.php';

use App\Models\RoleConfigurationService;

if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line.' . PHP_EOL);
}

class RoleManager
{
    private RoleConfigurationService $service;

    public function __construct()
    {
        $this->service = new RoleConfigurationService();
    }

    public function run($argv)
    {
        if (count($argv) < 2) {
            $this->showHelp();
            return;
        }

        $command = $argv[1];

        switch ($command) {
            case 'list-roles':
                $this->listRoles();
                break;
            case 'list-permissions':
                $this->listPermissions();
                break;
            case 'create-role':
                $this->createRole($argv);
                break;
            case 'create-permission':
                $this->createPermission($argv);
                break;
            case 'assign-permission':
                $this->assignPermission($argv);
                break;
            case 'remove-permission':
                $this->removePermission($argv);
                break;
            case 'role-details':
                $this->showRoleDetails($argv);
                break;
            case 'setup-defaults':
                $this->setupDefaultRoles();
                break;
            default:
                echo "Unknown command: $command" . PHP_EOL;
                $this->showHelp();
        }
    }

    private function showHelp()
    {
        echo "Role Management CLI" . PHP_EOL;
        echo "==================" . PHP_EOL . PHP_EOL;
        echo "Usage: php role_manager.php <command> [options]" . PHP_EOL . PHP_EOL;
        echo "Commands:" . PHP_EOL;
        echo "  list-roles                    List all roles" . PHP_EOL;
        echo "  list-permissions              List all permissions" . PHP_EOL;
        echo "  create-role <name> [desc]     Create a new role" . PHP_EOL;
        echo "  create-permission <name> <desc> <category>  Create a new permission" . PHP_EOL;
        echo "  assign-permission <role_id> <permission_name>  Assign permission to role" . PHP_EOL;
        echo "  remove-permission <role_id> <permission_name>  Remove permission from role" . PHP_EOL;
        echo "  role-details <role_id>        Show role details" . PHP_EOL;
        echo "  setup-defaults                Setup default roles and permissions" . PHP_EOL;
        echo PHP_EOL;
    }

    private function listRoles()
    {
        echo "Roles:" . PHP_EOL;
        echo "======" . PHP_EOL;

        $roles = $this->service->getAllRolesWithPermissions();

        if (empty($roles)) {
            echo "No roles found." . PHP_EOL;
            return;
        }

        foreach ($roles as $role) {
            echo sprintf("ID: %d | Name: %s | Users: %d | Permissions: %d" . PHP_EOL,
                $role['id'],
                $role['name'],
                $role['user_count'],
                count($role['permissions'])
            );

            if ($role['description']) {
                echo "  Description: " . $role['description'] . PHP_EOL;
            }

            if (!empty($role['permissions'])) {
                echo "  Permissions: " . implode(', ', $role['permissions']) . PHP_EOL;
            }
            echo PHP_EOL;
        }
    }

    private function listPermissions()
    {
        echo "Permissions:" . PHP_EOL;
        echo "============" . PHP_EOL;

        $permissions = $this->service->getAllPermissions();

        if (empty($permissions)) {
            echo "No permissions found." . PHP_EOL;
            return;
        }

        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $groupedPermissions[$permission['category']][] = $permission;
        }

        foreach ($groupedPermissions as $category => $categoryPermissions) {
            echo PHP_EOL . "[$category]" . PHP_EOL;
            foreach ($categoryPermissions as $permission) {
                echo sprintf("  %s - %s" . PHP_EOL,
                    $permission['name'],
                    $permission['description'] ?: 'No description'
                );
            }
        }
        echo PHP_EOL;
    }

    private function createRole($argv)
    {
        if (count($argv) < 3) {
            echo "Usage: php role_manager.php create-role <name> [description]" . PHP_EOL;
            return;
        }

        $name = $argv[2];
        $description = $argv[3] ?? '';

        $result = $this->service->createRole($name, $description);

        if ($result['success']) {
            echo "Role '$name' created successfully with ID: " . $result['role_id'] . PHP_EOL;
        } else {
            echo "Error creating role: " . $result['message'] . PHP_EOL;
        }
    }

    private function createPermission($argv)
    {
        if (count($argv) < 5) {
            echo "Usage: php role_manager.php create-permission <name> <description> <category>" . PHP_EOL;
            return;
        }

        $name = strtoupper($argv[2]);
        $description = $argv[3];
        $category = $argv[4];

        $result = $this->service->createPermission($name, $description, $category);

        if ($result['success']) {
            echo "Permission '$name' created successfully with ID: " . $result['permission_id'] . PHP_EOL;
        } else {
            echo "Error creating permission: " . $result['message'] . PHP_EOL;
        }
    }

    private function assignPermission($argv)
    {
        if (count($argv) < 4) {
            echo "Usage: php role_manager.php assign-permission <role_id> <permission_name>" . PHP_EOL;
            return;
        }

        $roleId = (int) $argv[2];
        $permissionName = strtoupper($argv[3]);

        // Get current role details
        $role = $this->service->getRoleById($roleId);
        if (!$role) {
            echo "Role with ID $roleId not found." . PHP_EOL;
            return;
        }

        // Check if permission already assigned
        if (in_array($permissionName, $role['permissions'])) {
            echo "Permission '$permissionName' is already assigned to role '{$role['name']}'." . PHP_EOL;
            return;
        }

        // Add permission to existing permissions
        $newPermissions = array_merge($role['permissions'], [$permissionName]);

        $result = $this->service->updateRole($roleId, $role['name'], $role['description'], $newPermissions);

        if ($result['success']) {
            echo "Permission '$permissionName' assigned to role '{$role['name']}' successfully." . PHP_EOL;
        } else {
            echo "Error assigning permission: " . $result['message'] . PHP_EOL;
        }
    }

    private function removePermission($argv)
    {
        if (count($argv) < 4) {
            echo "Usage: php role_manager.php remove-permission <role_id> <permission_name>" . PHP_EOL;
            return;
        }

        $roleId = (int) $argv[2];
        $permissionName = strtoupper($argv[3]);

        // Get current role details
        $role = $this->service->getRoleById($roleId);
        if (!$role) {
            echo "Role with ID $roleId not found." . PHP_EOL;
            return;
        }

        // Check if permission is assigned
        if (!in_array($permissionName, $role['permissions'])) {
            echo "Permission '$permissionName' is not assigned to role '{$role['name']}'." . PHP_EOL;
            return;
        }

        // Remove permission from existing permissions
        $newPermissions = array_filter($role['permissions'], function($p) use ($permissionName) {
            return $p !== $permissionName;
        });

        $result = $this->service->updateRole($roleId, $role['name'], $role['description'], array_values($newPermissions));

        if ($result['success']) {
            echo "Permission '$permissionName' removed from role '{$role['name']}' successfully." . PHP_EOL;
        } else {
            echo "Error removing permission: " . $result['message'] . PHP_EOL;
        }
    }

    private function showRoleDetails($argv)
    {
        if (count($argv) < 3) {
            echo "Usage: php role_manager.php role-details <role_id>" . PHP_EOL;
            return;
        }

        $roleId = (int) $argv[2];
        $role = $this->service->getRoleById($roleId);

        if (!$role) {
            echo "Role with ID $roleId not found." . PHP_EOL;
            return;
        }

        echo "Role Details:" . PHP_EOL;
        echo "=============" . PHP_EOL;
        echo "ID: " . $role['id'] . PHP_EOL;
        echo "Name: " . $role['name'] . PHP_EOL;
        echo "Description: " . ($role['description'] ?: 'No description') . PHP_EOL;
        echo "Created: " . $role['created_at'] . PHP_EOL;
        echo "Updated: " . ($role['updated_at'] ?: 'Never') . PHP_EOL;
        echo "User Count: " . $role['user_count'] . PHP_EOL;
        echo "Permissions: " . (empty($role['permissions']) ? 'None' : implode(', ', $role['permissions'])) . PHP_EOL;

        if ($role['user_count'] > 0) {
            echo PHP_EOL . "Assigned Users:" . PHP_EOL;
            $users = $this->service->getUsersByRole($roleId);
            foreach ($users as $user) {
                $status = $user['status'] == 1 ? 'Active' : 'Inactive';
                $verified = $user['verified'] == 1 ? 'Verified' : 'Unverified';
                echo "  - {$user['email']} ({$status}, {$verified})" . PHP_EOL;
            }
        }
    }

    private function setupDefaultRoles()
    {
        echo "Setting up default roles and permissions..." . PHP_EOL;

        // Create common permissions if they don't exist
        $defaultPermissions = [
            ['name' => 'DEPT_FULL', 'description' => 'Full department management access', 'category' => 'Department'],
            ['name' => 'DEPT_WRITE', 'description' => 'Department write access', 'category' => 'Department'],
            ['name' => 'STUDENT_READ', 'description' => 'Student information read access', 'category' => 'Student'],
            ['name' => 'STUDENT_WRITE', 'description' => 'Student information write access', 'category' => 'Student'],
            ['name' => 'USER_MANAGE', 'description' => 'User management access', 'category' => 'User'],
            ['name' => 'ROLE_MANAGE', 'description' => 'Role and permission management', 'category' => 'System'],
            ['name' => 'SYSTEM_CONFIG', 'description' => 'System configuration access', 'category' => 'System'],
        ];

        foreach ($defaultPermissions as $permission) {
            $result = $this->service->createPermission($permission['name'], $permission['description'], $permission['category']);
            if ($result['success']) {
                echo "Created permission: {$permission['name']}" . PHP_EOL;
            } elseif (strpos($result['message'], 'already exists') !== false) {
                echo "Permission already exists: {$permission['name']}" . PHP_EOL;
            } else {
                echo "Error creating permission {$permission['name']}: {$result['message']}" . PHP_EOL;
            }
        }

        // Setup default role configurations
        $defaultRoles = [
            [
                'name' => 'super_admin',
                'description' => 'Super administrator with all system access',
                'permissions' => ['DEPT_FULL', 'STUDENT_WRITE', 'USER_MANAGE', 'ROLE_MANAGE', 'SYSTEM_CONFIG']
            ],
            [
                'name' => 'registrar',
                'description' => 'University registrar with student and academic management',
                'permissions' => ['DEPT_WRITE', 'STUDENT_WRITE']
            ],
            [
                'name' => 'staff',
                'description' => 'Staff member with limited access',
                'permissions' => ['STUDENT_READ']
            ]
        ];

        foreach ($defaultRoles as $roleData) {
            // Try to get existing role first
            $roles = $this->service->getAllRolesWithPermissions();
            $existingRole = null;
            foreach ($roles as $role) {
                if ($role['name'] === $roleData['name']) {
                    $existingRole = $role;
                    break;
                }
            }

            if ($existingRole) {
                // Update existing role
                $result = $this->service->updateRole(
                    $existingRole['id'],
                    $roleData['name'],
                    $roleData['description'],
                    $roleData['permissions']
                );
                if ($result['success']) {
                    echo "Updated role: {$roleData['name']}" . PHP_EOL;
                } else {
                    echo "Error updating role {$roleData['name']}: {$result['message']}" . PHP_EOL;
                }
            } else {
                // Create new role
                $result = $this->service->createRole(
                    $roleData['name'],
                    $roleData['description'],
                    $roleData['permissions']
                );
                if ($result['success']) {
                    echo "Created role: {$roleData['name']}" . PHP_EOL;
                } else {
                    echo "Error creating role {$roleData['name']}: {$result['message']}" . PHP_EOL;
                }
            }
        }

        echo "Default setup completed!" . PHP_EOL;
    }
}

// Run the CLI
$manager = new RoleManager();
$manager->run($argv);