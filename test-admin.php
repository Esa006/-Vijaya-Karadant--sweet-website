<?php
require 'config/config.php';
require 'src/Autoloader.php';
require 'repositories/PermissionRepository.php';

use App\Repositories\PermissionRepository;

try {
    $repo = new PermissionRepository();
    $data = [
        'name' => 'test_user',
        'email' => 'test_random_' . time() . '@gmail.com',
        'password' => '12345',
        'role_id' => 2
    ];
    $repo->createOrUpgradeAdmin($data);
    echo "SUCCESS\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
