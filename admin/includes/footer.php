<?php
/**
 * Sweets Website
 * =============================================================
 * File: footer.php
 * Description: Admin layout footer with scripts
 * =============================================================
 */
?>
        </div> <!-- end .content-body -->
    </div> <!-- end .main-content -->
</div> <!-- end .admin-wrapper -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom Admin JS -->
<script src="<?php echo BASE_URL; ?>assets/js/admin/admin-api.js?v=<?php echo SITE_VERSION; ?>"></script>
<script src="<?php echo BASE_URL; ?>assets/js/admin/dashboard-stats.js?v=<?php echo SITE_VERSION; ?>"></script>
<script src="<?php echo BASE_URL; ?>assets/js/admin/admin.js?v=<?php echo SITE_VERSION; ?>"></script>

<?php if (!empty($pageScripts) && is_array($pageScripts)): ?>
    <?php foreach ($pageScripts as $scriptPath): ?>
        <script src="<?php echo BASE_URL . ltrim($scriptPath, '/'); ?>?v=<?php echo SITE_VERSION; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
