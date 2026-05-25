<?php
/**
 * Sweets Website - Admin
 * =============================================================
 * File: product-preview.php
 * Description: Premium State-Driven Product & Subcategory Preview Modal
 * =============================================================
 */
?>

<!-- Premium Product Preview Modal -->
<div class="modal fade" id="productPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content border-0 preview-container-wrapper">
            
            <!-- Skeleton Loader (Shimmer) -->
            <div id="preview_skeleton" class="p-4">
                <div class="skeleton skeleton-title mb-4"></div>
                <div class="skeleton skeleton-img mb-4"></div>
                <div class="row g-2 mb-4">
                    <div class="col-3"><div class="skeleton skeleton-stat"></div></div>
                    <div class="col-3"><div class="skeleton skeleton-stat"></div></div>
                    <div class="col-3"><div class="skeleton skeleton-stat"></div></div>
                    <div class="col-3"><div class="skeleton skeleton-stat"></div></div>
                </div>
                <div class="skeleton skeleton-text mb-2"></div>
                <div class="skeleton skeleton-text mb-2"></div>
                <div class="skeleton skeleton-text w-50"></div>
            </div>

            <!-- Main Dynamic Content -->
            <div id="preview_main_content" class="d-none">
                <div class="preview-container-inner">
                    <!-- Header -->
                    <div class="preview-header-custom">
                        <h1 class="preview-title-custom">Preview Mode</h1>
                        <button type="button" class="btn-close-custom" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <!-- Image Section -->
                    <div class="product-image-wrapper-custom">
                        <img id="preview_image" src="" alt="Product Preview" class="product-img-custom">
                        
                        <div class="img-badge-custom" id="preview_best_seller_badge">
                            Best Seller
                        </div>
                        
                        <button class="img-heart-btn-custom" onclick="this.classList.toggle('active')">
                            <i class="bi bi-heart-fill"></i>
                        </button>
                    </div>

                    <!-- Product Details -->
                    <div class="product-details-custom">
                        
                        <!-- Title & Status -->
                        <div class="title-row-custom">
                            <h2 class="product-title-custom" id="preview_name">Product Name</h2>
                            <span class="status-badge-custom" id="preview_status_badge">Active</span>
                        </div>

                        <!-- Stats Grid -->
                        <div class="stats-grid-custom">
                            <div class="stat-card-custom">
                                <i class="bi bi-tag-fill stat-icon-custom"></i>
                                <div class="stat-value-custom" id="preview_stat_price">₹ 0</div>
                                <div class="stat-label-custom">Price</div>
                            </div>
                            <div class="stat-card-custom">
                                <i class="bi bi-box-seam-fill stat-icon-custom"></i>
                                <div class="stat-value-custom" id="preview_stat_stock">0</div>
                                <div class="stat-label-custom">In Stock</div>
                            </div>
                            <div class="stat-card-custom">
                                <i class="bi bi-cart-check-fill stat-icon-custom"></i>
                                <div class="stat-value-custom" id="preview_stat_sold">0</div>
                                <div class="stat-label-custom">Sold</div>
                            </div>
                            <div class="stat-card-custom">
                                <i class="bi bi-star-fill stat-icon-custom"></i>
                                <div class="stat-value-custom" id="preview_stat_rating">0.0</div>
                                <div class="stat-label-custom">Rating</div>
                            </div>
                        </div>

                        <!-- Weight Selector -->
                        <div class="variant-section-custom">
                            <div class="weight-selector-custom" id="preview_variants_list">
                                <!-- Dynamic buttons -->
                            </div>
                        </div>

                        <!-- Stock Status -->
                        <div class="stock-status-group-custom">
                            <span class="stock-label-custom">Stock Status</span>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="stockStatusPreview" id="inStockPreview" disabled>
                                <label class="form-check-label" for="inStockPreview">In Stock</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="stockStatusPreview" id="outStockPreview" disabled>
                                <label class="form-check-label" for="outStockPreview">Out of Stock</label>
                            </div>
                        </div>

                        <!-- SKU -->
                        <div class="sku-row-custom">
                            SKU <span class="sku-code-custom" id="preview_sku_value">N/A</span>
                        </div>

                        <!-- Description -->
                        <div class="desc-section-custom">
                            <label class="desc-label-custom">Description</label>
                            <div class="desc-box-custom" id="preview_desc_text">
                                Product description goes here.
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="action-row-custom">
                            <button class="btn-action-custom btn-edit-custom" id="preview_main_action_btn" onclick="editFromPreview()">Edit Product</button>
                            <button class="btn-action-custom btn-close-action-custom" data-bs-dismiss="modal">Close</button>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Error State UI -->
            <div id="preview_error_state" class="p-5 text-center d-none">
                <div class="text-danger mb-3"><i class="bi bi-exclamation-triangle fs-1"></i></div>
                <h6>Unable to load product data</h6>
                <p class="text-muted small">Please check your connection or try again.</p>
                <button class="btn btn-sm btn-outline-danger mt-2" onclick="location.reload()">Retry</button>
            </div>

        </div>
    </div>
</div>
