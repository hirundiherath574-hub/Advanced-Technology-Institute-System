// cli/setup_permissions.php
#!/usr/bin/env php
<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Database\Connection;

echo "Setting up user management permissions...\n";

$db = Connection::pdo();

try {
    $db->beginTransaction();

    // Add new permission for user management
    $stmt = $db->prepare("INSERT IGNORE INTO permissions (name) VALUES (?)");
    $stmt->execute(['USER_MANAGE']);

    // Grant USER_MANAGE permission to super_admin role
    $stmt = $db->prepare("
        INSERT IGNORE INTO role_permissions (role_id, permission) 
        SELECT r.id, 'USER_MANAGE' 
        FROM roles r 
        WHERE r.name = 'super_admin'
    ");
    $stmt->execute();

    $db->commit();
    echo "Permissions setup completed successfully!\n";
    echo "- Added USER_MANAGE permission\n";
    echo "- Granted USER_MANAGE to super_admin role\n";

} catch (Exception $e) {
    $db->rollBack();
    echo "Error setting up permissions: " . $e->getMessage() . "\n";
    exit(1);
}
?>