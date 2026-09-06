// cli/role.php
#!/usr/bin/env php
<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Database\Connection;

$email = $argv[1] ?? die("Usage: php role.php email role_name\n");
$role  = $argv[2] ?? die("Usage: php role.php email role_name\n");

$db = Connection::pdo();

// 1. fetch user id
$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$userId = $stmt->fetchColumn();
if (!$userId) die("User not found\n");

// 2. fetch role id
$stmt = $db->prepare("SELECT id FROM roles WHERE name = ?");
$stmt->execute([$role]);
$roleId = $stmt->fetchColumn();
if (!$roleId) die("Role not found\n");

// 3. link them
$db->prepare("REPLACE INTO user_roles (user_id, role_id) VALUES (?,?)")
    ->execute([$userId, $roleId]);

echo "Granted $role to $email\n";