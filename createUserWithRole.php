// cli/createUserWithRole.php
#!/usr/bin/env php
<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Database\Connection;
use Delight\Auth\Auth;

if ($argc !== 4) {
    die("Usage: php createUserWithRole.php email@domain password role_name\n");
}

$email = $argv[1];
$pw    = $argv[2];
$role  = $argv[3];

$auth = new Auth(Connection::pdo());
$db   = Connection::pdo();

// 1. Create user
$userId = $auth->admin()->createUser($email, $pw);

// 2. Map role name → id
$stmt = $db->prepare("SELECT id FROM roles WHERE name = ?");
$stmt->execute([$role]);
$roleId = $stmt->fetchColumn();

if (!$roleId) {
    die("Role '$role' not found in roles table.\n");
}

// 3. Link user → role
$db->prepare("REPLACE INTO user_roles (user_id, role_id) VALUES (?, ?)")
    ->execute([$userId, $roleId]);

echo "User $email created with role '$role'.\n";
