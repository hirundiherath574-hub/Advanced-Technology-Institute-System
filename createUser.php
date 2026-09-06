// cli/createUser.php
<?php
require __DIR__ . '/../bootstrap.php';

use App\Database\Connection;
use Delight\Auth\Auth;

$auth = new Auth(Connection::pdo());

$email = $argv[1];
$pw    = $argv[2];

$userId = $auth->admin()->createUser($email, $pw);
$auth->admin()->addRoleForUserById($userId, \Delight\Auth\Role::SUPER_ADMIN);

echo "Admin created: $email\n";