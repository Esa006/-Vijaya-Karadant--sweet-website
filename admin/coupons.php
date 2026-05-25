<?php
/**
 * Sweets Website
 * =============================================================
 * File: offers.php
 * Description: Promotional offers and coupons management dashboard.
 * Integrated with the modular admin shell.
 * =============================================================
 */

// Define page-specific assets BEFORE including header.php
$pageStyles = [
    'assets/css/admin/offers.css',
    'assets/css/admin/pages/product-preview.css',
    'assets/css/admin/pages/product-delete.css'
];
$pageScripts = [
    'assets/js/admin/offers.js',
    'assets/js/admin/modals.js'
];

require_once 'includes/header.php';
require_once 'includes/auth.php'; // Security check
require_once 'includes/sidebar.php'; 
?>

<div class="main-content">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body pt-0 px-4 pb-5">
        
        <!-- Breadcrumb & Header -->
        <div class="offers-page-header pt-4 mb-4">
            <div>
                <nav aria-label="breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-0" style="font-size: 12px;">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Admin</a></li>
                        <li class="breadcrumb-item active text-muted" aria-current="page">Promotions</li>
                    </ol>
                </nav>
                <h1 class="offers-page-title mb-0">Offers & Coupons</h1>
            </div>
            <a href="create-offer.php" class="btn-offers-create text-decoration-none">
                <i class="bi bi-plus-lg"></i> Create Offer
            </a>
        </div>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($_SESSION['flash_error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <!-- Filter Bar -->
        <div class="offers-filter-bar bg-white p-3 rounded-3 border shadow-sm border-light">
             <div class="offers-search-input">
                 <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search i-search" viewBox="0 0 16 16" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #8e4422; z-index: 5; pointer-events: none;">
                     <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                 </svg>
                 <input type="text" id="offerSearchInput" placeholder="Search by name..." class="form-control-sm border-0 bg-transparent">
            </div>
            
            <div class="dropdown">
                <button class="filter-offers-btn dropdown-toggle" id="statusFilterBtn" data-bs-toggle="dropdown">
                    Status All <i class="bi bi-chevron-down ms-1" style="font-size:10px;"></i>
                </button>
                <ul class="dropdown-menu shadow-sm">
                    <li><a class="dropdown-item small" href="#" onclick="setOfferStatusFilter('all', 'Status All')">All</a></li>
                    <li><a class="dropdown-item small" href="#" onclick="setOfferStatusFilter('Active', 'Active')">Active</a></li>
                    <li><a class="dropdown-item small" href="#" onclick="setOfferStatusFilter('Scheduled', 'Scheduled')">Scheduled</a></li>
                    <li><a class="dropdown-item small" href="#" onclick="setOfferStatusFilter('Expired', 'Expired')">Expired</a></li>
                </ul>
            </div>
            
            <button class="filter-offers-btn" id="dateFilter">
                Join Date <i class="bi bi-calendar3 ms-1" style="font-size:12px;"></i>
            </button>
        </div>

        <h2 class="section-title fs-6 fw-bold mt-4 mb-3">Active & Scheduled Offers</h2>

        <!-- Table Card -->
        <div class="offers-table-card border shadow-sm">
            <table class="offers-main-table">
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" class="offers-custom-checkbox" id="selectAllOffers"></th>
                        <th>Offer Details</th>
                        <th>Discount</th>
                        <th>Min. Order</th>
                        <th>Usage Limit</th>
                        <th>Validity Period</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="offersTableBody">
                    <!-- Loaded via JS -->
                </tbody>
            </table>

            <!-- Pagination Bar -->
            <div class="offers-pagination-bar">
                <div class="pagination-info small text-muted" id="paginationInfo">Page 1 of 1 · 1–6 of 6 offers</div>
                <div class="pagination-controls d-flex align-items-center" id="paginationControls">
                    <!-- Pagination injected by JS -->
                </div>
            </div>
        </div>
    </div>

<?php 
// Include Global Modals
require_once 'includes/modals/product-preview.php';
require_once 'includes/modals/delete-confirm.php';
?>

</div>

<?php require_once 'includes/footer.php'; ?>
