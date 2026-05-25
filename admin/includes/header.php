<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once __DIR__ . '/auth.php'; // Enforces requireAdmin()
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?php echo SITE_NAME; ?> Admin</title>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL . SITE_FAVICON; ?>">
    
    <!-- Bootstrap 5.3.0 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Google Fonts: Inter & Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Flatpickr Date Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/confetti.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js"></script>
    
    <!-- Security: CSRF Token for Admin API -->
    <?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
    <?php if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); ?>
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    
    <!-- Custom Admin Styles -->
    <script src="<?php echo BASE_URL; ?>assets/js/admin/modals.js"></script>
    <script type="module" src="<?php echo BASE_URL; ?>assets/js/admin/preview-engine/main.js"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin/admin.css?v=<?php echo SITE_VERSION; ?>">
    <?php if (!empty($pageStyles) && is_array($pageStyles)): ?>
        <?php foreach ($pageStyles as $stylePath): ?>
            <link rel="stylesheet" href="<?php echo BASE_URL . ltrim($stylePath, '/'); ?>?v=<?php echo SITE_VERSION; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin/components/preview-modal.css?v=<?php echo SITE_VERSION; ?>">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Global JS Config -->
    <script>
        window.BASE_URL = "<?php echo BASE_URL; ?>";
    </script>
    
    <!-- PDF Generation Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
</head>
<body class="admin-body">
    <div class="admin-wrapper">
