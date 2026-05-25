<?php
/**
 * Sweets Website - Admin
 * =============================================================
 * File: includes/modals/delete-confirm.php
 * Description: Generic luxury delete confirmation modal
 * =============================================================
 */
?>
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="delete-modal-card">
                <button type="button" class="delete-modal-close shadow-none" data-bs-dismiss="modal" aria-label="Close dialog">
                    <svg viewBox="0 0 24 24" style="width: 24px; height: 24px;" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
                        <path d="M6 6l12 12" />
                        <path d="M18 6L6 18" />
                    </svg>
                </button>

                <div class="delete-header">
                    <h1 class="delete-title" id="delete_modal_title">Delete Item?</h1>
                    <p class="delete-subtitle" id="delete_modal_subtitle">
                        This action cannot be undone. The item will be
                        permanently removed from your records.
                    </p>
                </div>

                <div class="delete-product-preview">
                    <div class="delete-image-outer">
                        <div class="delete-image-badge">
                            <i class="bi bi-x-lg" style="font-size: 10px;"></i>
                        </div>
                        <div class="delete-image-inner">
                            <img id="delete_item_image" src="<?php echo BASE_URL; ?>assets/images/placeholders/product-placeholder.png" alt="Preview" onerror="this.src='<?php echo BASE_URL; ?>assets/images/placeholders/product-placeholder.png'">
                        </div>
                    </div>

                    <div class="delete-info">
                        <h2 class="delete-product-name" id="delete_item_name">Item Name</h2>
                        <p class="delete-product-meta" id="delete_item_meta_primary">Details : N/A</p>
                        <p class="delete-product-meta" id="delete_item_meta_secondary"></p>
                    </div>
                </div>

                <div class="delete-actions text-center">
                    <button type="button" id="confirmDeleteBtn" class="btn btn-confirm-del shadow-none">
                        Yes, Delete
                    </button>
                    <button type="button" class="btn btn-cancel-del shadow-none" data-bs-dismiss="modal">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
