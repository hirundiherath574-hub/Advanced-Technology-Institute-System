<?php
require_once __DIR__ . '/../../bootstrap.php';
use App\Database\Connection;
use Delight\Auth\Auth;

$auth = new Auth(Connection::pdo());
$auth->logOut();               // kills local session
$auth->destroySession();       // optional: wipe $_SESSION completely

header('Location: login.php');
exit;