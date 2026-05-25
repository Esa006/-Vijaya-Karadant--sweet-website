<?php
$ch = curl_init('http://localhost/sweet-website/api/v1/cart-add.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['product_id'=>2029,'quantity'=>1]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
echo curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n" . $res;
