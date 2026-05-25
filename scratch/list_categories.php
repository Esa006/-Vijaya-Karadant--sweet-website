<?php
require_once 'config/config.php';
require_once REPOS_PATH . '/CategoryRepository.php';

$repo = new CategoryRepository();
$categories = $repo->getAllFlat();
echo json_encode($categories, JSON_PRETTY_PRINT);
