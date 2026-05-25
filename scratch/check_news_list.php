<?php
require 'config/config.php';
require 'services/NewsService.php';
$s = new NewsService();
$list = $s->getActiveNews();
foreach($list as $n) {
    echo "ID: {$n['id']} | Title: {$n['title']} | Image: {$n['image_path']}\n";
}
