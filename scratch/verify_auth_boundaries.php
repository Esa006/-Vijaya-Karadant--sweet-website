<?php
/**
 * Security Integration Test: Verify Auth Boundaries
 */
require_once __DIR__ . '/../config/config.php';

function runTest(string $filePath, array $sessionMock): array {
    $targetDir = dirname(ROOT_PATH . '/' . $filePath);
    $tempFile = $targetDir . '/temp_test_session.php';
    
    // Write a temporary file that sets up the mock session before including the target file
    $sessionCode = "<?php\n";
    $sessionCode .= "if (session_status() === PHP_SESSION_NONE) session_start();\n";
    foreach ($sessionMock as $key => $val) {
        $sessionCode .= "\$_SESSION[" . var_export($key, true) . "] = " . var_export($val, true) . ";\n";
    }
    // Mock REQUEST_URI to simulate an API request
    if (strpos($filePath, 'api') !== false) {
        $sessionCode .= "\$_SERVER['REQUEST_URI'] = " . var_export('/' . str_replace('\\', '/', $filePath), true) . ";\n";
        $sessionCode .= "\$_SERVER['REQUEST_METHOD'] = 'POST';\n"; // simulate POST
    }
    
    $sessionCode .= "try {\n";
    $sessionCode .= "    include " . var_export(basename($filePath), true) . ";\n";
    $sessionCode .= "} catch (Throwable \$e) {\n";
    $sessionCode .= "    echo 'EXCEPTION: ' . \$e->getMessage();\n";
    $sessionCode .= "}\n";
    
    file_put_contents($tempFile, $sessionCode);
    
    // Run the PHP process with cwd set to the target file's directory
    $descriptorSpec = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ];
    $process = proc_open('php ' . escapeshellarg(basename($tempFile)), $descriptorSpec, $pipes, $targetDir);
    
    $output = '';
    $error = '';
    if (is_resource($process)) {
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        proc_close($process);
    }
    
    @unlink($tempFile);
    return ['output' => trim($output), 'error' => trim($error)];
}

$filesToTest = [
    'admin/api/v1/analytics.php',
    'admin/api/v1/coupons.php',
    'admin/api/v1/customers.php',
    'admin/api/v1/customer-details.php',
    'admin/api/v1/offer-details.php',
    'admin/api/v1/order-bulk.php',
    'admin/api/v1/subcategory-bulk.php',
    'admin/api/mark-notification-read.php',
    'admin/install-crm.php'
];

$sessions = [
    'Guest (No Session)' => [],
    'Customer (esakiraj)' => ['user_id' => 9008, 'user_role' => 'customer'],
    'Admin (Full Access)' => ['user_id' => 2, 'user_role' => 'admin']
];

echo "=== SECURTY INTEGRATION TEST: AUTH BOUNDARIES ===\n\n";

foreach ($filesToTest as $file) {
    echo "Testing: $file\n";
    foreach ($sessions as $name => $session) {
        $res = runTest($file, $session);
        $out = $res['output'];
        
        // Analyze outcome
        $status = 'UNKNOWN';
        if (strpos($out, 'Session expired or unauthorized') !== false || 
            strpos($out, 'Location:') !== false || 
            strpos($out, 'login.php') !== false || 
            strpos($out, 'Unauthorized') !== false || 
            strpos($out, 'unauthorized') !== false ||
            (empty($out) && $name !== 'Admin (Full Access)')) { // Empty output for guest/customer means auth middleware called exit;
            $status = 'BLOCKED (SECURE)';
        } else if (strpos($out, 'EXCEPTION') !== false || 
                   strpos($out, 'error') !== false || 
                   strpos($out, 'success') !== false || 
                   strpos($out, 'Starting CRM') !== false || 
                   empty($out)) {
            if ($name === 'Admin (Full Access)') {
                $status = 'ALLOWED (EXPECTED)';
            } else {
                $status = 'Bypassed (VULNERABLE!)';
            }
        }
        
        echo sprintf("  %-25s => Status: %s | Result: %s\n", $name, $status, substr(str_replace("\n", " ", $out), 0, 80));
    }
    echo "\n";
}
