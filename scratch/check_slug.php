<?php
require_once 'config/config.php';
require_once REPOS_PATH . '/CategoryRepository.php';

$repo = new CategoryRepository();
$cat = $repo->getBySlug('gift-box');
echo json_encode($cat, JSON_PRETTY_PRINT);
