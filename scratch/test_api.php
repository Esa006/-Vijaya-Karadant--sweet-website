<?php
$_POST['order_id'] = 90;
$_POST['action'] = 'add';
$_POST['status'] = 'SHIPPED';
$_POST['description'] = 'Test description';

// Mock server env
$_SERVER['REQUEST_METHOD'] = 'POST';

// Mock session for auth
session_start();
$_SESSION['user_role'] = 'admin';
$_SESSION['user_id'] = 1;
$_SESSION['user_ip'] = '::1'; // Match local if needed

require_once 'admin/api/v1/tracking.php';
