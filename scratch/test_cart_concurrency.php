<?php
// Scratch script to test Session Lock Concurrency using curl_multi
$url = "http://localhost/sweet-website/api/v1/cart-add.php";

$numRequests = 20; // 20 simultaneous add-to-cart requests
$multi = curl_multi_init();
$channels = [];

$startTime = microtime(true);

for ($i = 0; $i < $numRequests; $i++) {
    $ch = curl_init();
    
    // Fake payload
    $postData = http_build_query([
        'product_id' => 2029,
        'quantity' => 1
    ]);
    
    // Force the SAME session cookie to trigger session locking!
    $cookie = "PHPSESSID=test_concurrency_lock_session_12345;";

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    
    // Optional timeout
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    curl_multi_add_handle($multi, $ch);
    $channels[$i] = $ch;
}

echo "Initiated $numRequests concurrent add-to-cart requests for the SAME session...\n";

$active = null;
do {
    $mrc = curl_multi_exec($multi, $active);
} while ($mrc == CURLM_CALL_MULTI_PERFORM);

while ($active && $mrc == CURLM_OK) {
    if (curl_multi_select($multi) != -1) {
        do {
            $mrc = curl_multi_exec($multi, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    }
}

$endTime = microtime(true);
$duration = round($endTime - $startTime, 3);

$successCount = 0;
foreach ($channels as $i => $ch) {
    $content = curl_multi_getcontent($ch);
    $info = curl_getinfo($ch);
    if ($info['http_code'] === 200) {
        $successCount++;
    }
    curl_multi_remove_handle($multi, $ch);
    curl_close($ch);
}
curl_multi_close($multi);

echo "Completed in $duration seconds.\n";
echo "Successful HTTP 200 Responses: $successCount / $numRequests\n";

if ($duration > 5) {
    echo "🚨 WARNING: High response time indicates PHP Session File Locking is active (Requests were serialized).\n";
} else {
    echo "✅ SUCCESS: Low response time indicates Redis/Asynchronous sessions are working perfectly!\n";
}
