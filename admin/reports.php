<?php
/**
 * Sweets Website
 * =============================================================
 * File: reports.php
 * Description: High-fidelity Reports & Analytics Dashboard
 * Author: Antigravity - Senior Full-Stack Architect
 * Version: 3.1.0
 * =============================================================
 */

require_once '../config/config.php';

$pageStyles = ['assets/css/admin/pages/reports.css'];
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';
?>

<style>
    .filters-container {
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.9) !important;
        transition: all 0.3s ease;
    }
    
    .search-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    
    .search-icon {
        position: absolute;
        left: 12px;
        color: #888;
        z-index: 5;
        font-size: 14px;
    }
    
    .search-input {
        padding-left: 38px !important;
        border-radius: 10px !important;
        height: 42px;
        border: 1px solid #eee !important;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .search-input:focus {
        border-color: var(--brand-brown, #8B3A3A) !important;
        box-shadow: 0 0 0 3px rgba(139, 58, 58, 0.1) !important;
    }
    
    .filter-select, .form-control[type="date"] {
        height: 42px;
        border-radius: 10px !important;
        border: 1px solid #eee !important;
        font-size: 14px;
    }

    .input-group-text {
        border-radius: 10px 0 0 10px !important;
        border: 1px solid #eee !important;
        border-right: none !important;
        color: #888;
        cursor: pointer;
    }

    /* Date picker wrapper — positions the transparent overlay correctly */
    .date-picker-wrap {
        position: relative;
        display: flex;
        flex: 1;
    }

    .form-control[type="date"] {
        border-radius: 0 10px 10px 0 !important;
        padding-right: 8px !important;
    }

    /* Completely hide the right-hand browser calendar icon */
    .form-control[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0;
        position: absolute;
        right: 0;
        top: 0;
        width: 0;
        height: 0;
        pointer-events: none;
    }

    /* Invisible full-area overlay so clicking anywhere on the input opens picker */
    .date-picker-overlay {
        position: absolute;
        inset: 0;
        cursor: pointer;
        z-index: 2;
        background: transparent;
    }
</style>

<div class="main-content reports-page">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="dashboard-container mt-4 p-3  report-bg">
        <!-- Header -->
        <header class="page-header">
            <div>
                <h1 class="page-title">Reports & Analytics</h1>
            </div>
            <div class="header-actions">
                <button class="btn-export" onclick="ReportsEngine.exportCSV()">
                    <i class="bi bi-download"></i>
                    <span>Export CSV</span>
                </button>
            </div>
        </header>

        <!-- Report Tabs -->
        <div class="report-tabs">
            <button class="tab-btn active">Sales Report</button>
            <button class="tab-btn">Stock Report</button>
        </div>

        <!-- Period Selector -->
        <div class="period-selector">
            <button class="period-btn" data-range="daily">Daily</button>
            <button class="period-btn active" data-range="weekly">Weekly</button>
            <button class="period-btn" data-range="monthly">Monthly</button>
        </div>

        <div id="analytics-content">
            <!-- Summary Cards -->
            <div class="summary-grid">
                <div class="summary-card revenue">
                    <div class="card-header-row">
                        <span class="card-label">Total Revenue</span>
                        <div class="card-icon revenue">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                    </div>
                    <div class="card-value" id="kpi-revenue">₹ 0</div>
                    <div class="card-change positive" id="trend-revenue">
                        <i class="bi bi-arrow-up-right"></i>
                        <span>+0% vs prev period</span>
                    </div>
                </div>

                <div class="summary-card orders">
                    <div class="card-header-row">
                        <span class="card-label">Total Orders</span>
                        <div class="card-icon orders">
                            <i class="bi bi-bag"></i>
                        </div>
                    </div>
                    <div class="card-value" id="kpi-orders">0</div>
                    <div class="card-change positive" id="trend-orders">
                        <i class="bi bi-arrow-up-right"></i>
                        <span>+0% vs prev period</span>
                    </div>
                </div>

                <div class="summary-card units">
                    <div class="card-header-row">
                        <span class="card-label">Units Sold</span>
                        <div class="card-icon units">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    </div>
                    <div class="card-value" id="kpi-units">0</div>
                    <div class="card-change positive" id="trend-units">
                        <i class="bi bi-arrow-up-right"></i>
                        <span>+0% vs prev period</span>
                    </div>
                </div>

                <div class="summary-card aov">
                    <div class="card-header-row">
                        <span class="card-label">Order Value</span>
                        <div class="card-icon aov">
                            <i class="bi bi-currency-rupee"></i>
                        </div>
                    </div>
                    <div class="card-value" id="kpi-aov">₹ 0.00</div>
                    <div class="card-change negative" id="trend-aov">
                        <i class="bi bi-arrow-down-right"></i>
                        <span>-0% vs prev period</span>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-section mb-4">
                <div class="filters-container bg-white p-3 rounded-4 shadow-sm border border-light">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-5">
                            <div class="search-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search search-icon" viewBox="0 0 16 16" style="position: absolute; left: 12px; color: #888; z-index: 5; top: 50%; transform: translateY(-50%); pointer-events: none;">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                </svg>
                                <input type="text" class="form-control search-input" id="searchInput" placeholder="Search by name or SKU...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select filter-select" id="statusFilter">
                                <option value="">Status All</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"
                                      onclick="document.getElementById('dateFilter').showPicker()">
                                    <i class="bi bi-calendar3"></i>
                                </span>
                                <div class="date-picker-wrap">
                                    <input type="date" class="form-control border-start-0 ps-2 w-100" id="dateFilter">
                                    <span class="date-picker-overlay" onclick="document.getElementById('dateFilter').showPicker()"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-section">
                <!-- Main Line Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h2 class="chart-title">Revenue & Orders Over Time</h2>
                    </div>
                    <div class="chart-container">
                        <div id="revenueChart"></div>
                    </div>
                </div>

                <!-- Secondary Charts Row -->
                <div class="charts-row">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h2 class="chart-title">Units Sold & Revenue</h2>
                        </div>
                        <div class="chart-container">
                            <div id="unitsChart"></div>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="chart-header">
                            <h2 class="chart-title">Average Order Value</h2>
                        </div>
                        <div class="chart-container">
                            <div id="aovChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Grid: Category + Table -->
            <div class="bottom-grid">
                <!-- Category Chart -->
                <div class="category-card">
                    <div class="chart-header">
                        <h2 class="chart-title">Revenue by Category</h2>
                    </div>
                    <div class="category-chart-container">
                        <div id="categoryChart"></div>
                    </div>
                </div>

                <!-- Top Products Table -->
                <div class="table-card">
                    <div class="table-header">
                        <h2 class="table-title">Top Selling Products</h2>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="products-mobile" id="productsMobile">
                        <!-- Populated by JS -->
                    </div>

                    <!-- Desktop Table View -->
                    <div class="table-desktop">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th class="text-end">Sold</th>
                                        <th class="text-end">Revenue</th>
                                        <th class="text-end">Trend</th>
                                    </tr>
                                </thead>
                                <tbody id="productsTableBody">
                                    <!-- Populated by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="<?php echo BASE_URL; ?>assets/js/admin/pages/reports.js"></script>
<?php require_once 'includes/footer.php'; ?>
