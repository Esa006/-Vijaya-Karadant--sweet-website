<?php
require_once __DIR__ . '/../config/config.php';
require_once SERVICES_PATH . '/SettingService.php';

$settingService = new SettingService();
$settings = $settingService->getAllSettings();
print_r($settings);
