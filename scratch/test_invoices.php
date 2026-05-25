<?php
require_once 'c:\xampp\htdocs\sweet-website\config\config.php';
require_once REPOS_PATH . '\InvoiceRepository.php';
$repo = new InvoiceRepository();
$res = $repo->getAllInvoicesWithOrders(2);
print_r($res);
