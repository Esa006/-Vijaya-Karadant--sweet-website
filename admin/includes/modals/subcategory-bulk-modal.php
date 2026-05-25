<?php
/**
 * Sweets Website
 * =============================================================
 * File: admin/includes/modals/subcategory-bulk-modal.php
 * Description: High-fidelity Bulk Update Modal for Subcategories
 * =============================================================
 */
?>
<div class="modal fade" id="subcategoryBulkModal" tabindex="-1" aria-labelledby="bulkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-0 bg-light p-4">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                        <i class="bi bi-layers-fill text-primary fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="bulkModalLabel">Bulk Update</h5>
                        <p class="text-muted small mb-0" id="selectedCountText">0 items selected</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <form id="subcategoryBulkForm">
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-muted mb-2">Select Action</label>
                        <select class="form-select shadow-none border-light-subtle p-3" id="bulkActionType" required style="border-radius: 12px; background-color: #f8f9fa;">
                            <option value="">-- Choose an action --</option>
                            <option value="status">Change Status</option>
                            <option value="category">Assign to Category</option>
                            <option value="delete">Soft Delete Items</option>
                        </select>
                    </div>

                    <!-- Dynamic Value Input based on Action -->
                    <div id="bulkValueContainer" class="d-none">
                        <div id="statusValueWrapper" class="d-none">
                            <label class="form-label fw-bold small text-uppercase text-muted mb-2">New Status</label>
                            <select class="form-select shadow-none border-light-subtle p-3" id="bulkStatusValue" style="border-radius: 12px;">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div id="categoryValueWrapper" class="d-none">
                            <label class="form-label fw-bold small text-uppercase text-muted mb-2">Assign to Category</label>
                            <select class="form-select shadow-none border-light-subtle p-3" id="bulkCategoryValue" style="border-radius: 12px;">
                                <?php 
                                $allCats = (new CategoryRepository())->getRootCategories();
                                foreach($allCats as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="deleteValueWrapper" class="d-none">
                            <div class="alert alert-danger border-0 rounded-4 d-flex align-items-center mb-0">
                                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Confirm Deletion</h6>
                                    <p class="small mb-0">Selected items will be moved to trash (is_deleted = 1). This action can be undone by admin.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-0 p-4 bg-light">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" id="btnSubmitBulk" disabled>
                    Apply Changes
                </button>
            </div>
        </div>
    </div>
</div>

<style>
#subcategoryBulkModal .form-select:focus {
    border-color: #8c3333;
    box-shadow: 0 0 0 4px rgba(140, 51, 51, 0.1);
}
</style>
