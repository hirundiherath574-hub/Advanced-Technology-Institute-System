// cli/list_user_permissions.php
#!/usr/bin/env php
<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Database\Connection;

if ($argc < 2) {
    echo "Usage: php list_user_permissions.php <email>\n";
    exit(1);
}

$email = $argv[1];
$db = Connection::pdo();

// Get user with their permissions
$stmt = $db->prepare("
    SELECT 
        u.id, u.email, u.status,
        GROUP_CONCAT(DISTINCT r.name) as roles,
        GROUP_CONCAT(DISTINCT rp.permission) as permissions
    FROM users u
    LEFT JOIN user_roles ur ON u.id = ur.user_id  
    LEFT JOIN roles r ON ur.role_id = r.id
    LEFT JOIN role_permissions rp ON r.id = rp.role_id
    WHERE u.email = ?
    GROUP BY u.id, u.email, u.status
");
$stmt->execute([$email]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User '$email' not found.\n";
    exit(1);
}

echo "User Information:\n";
echo str_repeat("-", 50) . "\n";
echo "ID: " . $user['id'] . "\n";
echo "Email: " . $user['email'] . "\n";
echo "Status: " . ($user['status'] ? 'Active' : 'Inactive') . "\n";
echo "Roles: " . ($user['roles'] ?: 'No roles assigned') . "\n";
echo "Permissions: " . ($user['permissions'] ?: 'No permissions') . "\n";
?>