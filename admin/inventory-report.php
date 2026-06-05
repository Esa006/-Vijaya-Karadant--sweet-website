<?php
/**
 * Sweets Website
 * =============================================================
 * File: inventory-report.php
 * Description: Reports & Analytics - Inventory Status (Dynamic)
 * Author: Antigravity - Senior Full-Stack Architect
 * Version: 2.0.0
 * =============================================================
 */

require_once '../config/config.php';
$pageStyles = ['assets/css/admin/pages/inventory-report.css'];
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';
?>

<!-- Load Google Fonts for the premium look -->
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

<style>
    /* Report Tabs Styling (inline to bypass browser caching) */
    .report-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .tab-btn {
        flex: 0 0 auto;
        min-width: 100px;
        border: 1px solid #E5E5E5;
        background: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        color: #2D3436;
        font-family: inherit;
        font-size: 0.9rem;
    }

    .tab-btn:hover {
        background-color: #fcfcfc;
    }

    .tab-btn.active {
        background: #822D1D !important;
        color: white !important;
        border-color: #822D1D !important;
    }

    /* Mobile Header Layout Stack */
    @media (max-width: 575.98px) {
        .page-header-flex {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 12px !important;
            margin-top: 12px !important;
            margin-bottom: 16px !important;
        }
        .dashboard-title {
            font-size: 1.4rem !important;
            margin-bottom: 0 !important;
            text-align: left !important;
        }
        .inventory-report-page .header-actions {
            width: 100% !important;
        }
        .inventory-report-page .header-actions button {
            width: 100% !important;
            justify-content: center !important;
            padding: 10px 16px !important;
            font-size: 0.85rem !important;
            height: auto !important;
        }
        .report-tabs {
            display: flex !important;
            flex-wrap: nowrap !important;
            gap: 6px !important;
        }
        .tab-btn {
            flex: 1 !important;
            min-width: 0 !important;
            padding: 8px 4px !important;
            font-size: 0.82rem !important;
            text-align: center !important;
        }
        .inventory-report-page .container-fluid {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }
    }

    @media (max-width: 380px) {
        .dashboard-title {
            font-size: 1.2rem !important;
        }
        .tab-btn {
            font-size: 0.78rem !important;
        }
        .kpi-card {
            padding: 10px 12px !important;
        }
        .kpi-label {
            font-size: 10px !important;
        }
        .kpi-value {
            font-size: 1.25rem !important;
        }
        .kpi-sub {
            font-size: 10px !important;
        }
    }

    @media (max-width: 320px) {
        .dashboard-title {
            font-size: 1.1rem !important;
        }
        .tab-btn {
            font-size: 0.72rem !important;
            padding: 6px 2px !important;
        }
        #summaryCards {
            margin-bottom: 12px !important;
            --bs-gutter-x: 8px !important;
            --bs-gutter-y: 8px !important;
        }
        .kpi-card {
            padding: 8px !important;
            border-radius: 8px !important;
        }
        .kpi-label {
            font-size: 9px !important;
            letter-spacing: 0px !important;
        }
        .kpi-value {
            font-size: 1.15rem !important;
            margin: 2px 0 !important;
        }
        .kpi-sub {
            font-size: 9px !important;
        }
        .filter-row {
            padding: 10px !important;
        }
        .filter-row .form-control,
        .filter-row .form-select,
        .filter-row .input-group-text {
            font-size: 12px !important;
            padding: 6px 10px !important;
            height: 36px !important;
        }
        .table-report tr {
            padding: 10px !important;
            margin-bottom: 12px !important;
            border-radius: 8px !important;
        }
        .table-report td {
            padding: 4px 0 !important;
            font-size: 11px !important;
        }
        .table-report td::before {
            font-size: 10px !important;
        }
        .table-report td[data-label="Product"] {
            padding-top: 8px !important;
        }
        .product-bold {
            font-size: 12px !important;
        }
        .sku-sub {
            font-size: 10px !important;
        }
        .status-pill {
            font-size: 10px !important;
            padding: 4px 8px !important;
            min-width: 80px !important;
        }
        .progress-custom {
            width: 90px !important;
        }
        /* Topbar extra compact */
        .topbar-right {
            gap: 6px !important;
        }
        .profile-avatar img {
            width: 36px !important;
            height: 36px !important;
        }
        .topbar-profile button {
            width: 24px !important;
            height: 24px !important;
        }
    }
</style>

<div class="main-content inventory-report-page">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="container-fluid px-4">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 mt-4 page-header-flex">
            <h2 class="dashboard-title mb-0">Inventory Status</h2>
            <div class="header-actions d-flex gap-2">
                <button class="btn btn-outline-brown d-flex align-items-center" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i>
                    <span>Print Report</span>
                </button>
            </div>
        </div>
        <!-- Report Tabs -->
        <div class="report-tabs mb-4">
            <button class="tab-btn" onclick="window.location='reports.php'">Sales Report</button>
            <button class="tab-btn active" onclick="window.location='inventory-report.php'">Stock Report</button>
        </div>
        <!-- KPI Summary Cards (populated dynamically) -->
        <div class="row g-3 mb-4" id="summaryCards">
            <div class="col-6 col-md-3">
                <div class="kpi-card active" data-filter="">
                    <div class="kpi-label">Total SKUs</div>
                    <div class="kpi-value" id="kpi-total">—</div>
                    <div class="kpi-sub">across all categories</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card" data-filter="healthy">
                    <div class="kpi-label">Healthy Stock</div>
                    <div class="kpi-value text-success" id="kpi-healthy">—</div>
                    <div class="kpi-sub">of catalog</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card" data-filter="low">
                    <div class="kpi-label">Low / Critical</div>
                    <div class="kpi-value text-warning" id="kpi-low">—</div>
                    <div class="kpi-sub">need reordering</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card" data-filter="out_of_stock">
                    <div class="kpi-label">Out of Stock</div>
                    <div class="kpi-value text-danger" id="kpi-out">—</div>
                    <div class="kpi-sub">lost sales risk</div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="table-card p-3 mb-0">
                    <h5 class="chart-title-small mb-3">Stock Flow (Last 6 Weeks)</h5>
                    <div id="stockFlowChart"></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="table-card p-3 mb-0">
                    <h5 class="chart-title-small mb-3">Net Stock Change</h5>
                    <div id="netChangeChart"></div>
                </div>
            </div>
        </div>

        <!-- Filters Row -->
        <div class="table-card mb-0 p-3 filter-row">
            <div class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                            </svg>
                        </span>
                        <input type="text" id="inventorySearch" class="form-control" placeholder="Search by name or SKU...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="healthy">Healthy</option>
                        <option value="low">Low</option>
                        <option value="out_of_stock">Out of Stock</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="table-card">
            <div class="scroll-container">
                <table class="table-report" id="inventoryTable">
                    <thead>
                        <tr>
                            <th style="width:50px;">
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input" type="checkbox" id="masterCheckbox">
                                </div>
                            </th>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>In Stock</th>
                            <th>Reorder Point</th>
                            <th>Sold (30d)</th>
                            <th>Days Left</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="inventoryTbody">
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                Loading inventory...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
    .kpi-card {
        background: #fff;
        border-radius: 12px;
        padding: 16px 20px;
        box-shadow: 0 1px 6px rgba(0,0,0,0.06);
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .kpi-card.active {
        border-color: var(--brand-brown, #8B3A3A);
        background-color: #fdf5f2;
    }
    .kpi-label { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: .5px; }
    .kpi-value { font-size: 28px; font-weight: 700; color: #222; margin: 4px 0; }
    .kpi-sub   { font-size: 12px; color: #aaa; }

    .chart-title-small { font-size: 14px; font-weight: 600; color: #444; }

    .btn-outline-brown {
        color: var(--brand-brown, #8B3A3A);
        border-color: var(--brand-brown, #8B3A3A);
        background: transparent;
        transition: all 0.3s ease;
    }
    .btn-outline-brown:hover { background-color: var(--brand-brown, #8B3A3A); color: white; }

    @media print {
        /* 1. Hide interactive/navigation elements */
        .admin-sidebar, 
        .admin-topbar, 
        .header-actions, 
        .report-tabs, 
        .filter-row, 
        .table-report th:first-child, 
        .table-report td:first-child { 
            display: none !important; 
        }

        /* 2. Reset main content container */
        .main-content { 
            margin-left: 0 !important; 
            padding: 0 !important; 
            background: #fff !important; 
            color: #000 !important; 
        }
        
        .container-fluid {
            padding: 0 !important;
        }

        body {
            background: #fff !important;
            color: #000 !important;
        }

        /* 3. Style title */
        .dashboard-title {
            color: #000 !important;
            font-size: 24px !important;
            margin-bottom: 20px !important;
            border-bottom: 2px solid #000 !important;
            padding-bottom: 10px !important;
        }

        /* 4. Format KPI Cards for Print (Flat, grid-like border boxes) */
        #summaryCards {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            gap: 12px !important;
            margin-bottom: 24px !important;
        }
        
        #summaryCards > div {
            flex: 1 !important;
            width: 25% !important;
            max-width: 25% !important;
        }

        .kpi-card {
            border: 1px solid #ccc !important;
            background: #fff !important;
            box-shadow: none !important;
            padding: 12px 10px !important;
            border-radius: 6px !important;
            text-align: center !important;
            height: 100% !important;
        }

        .kpi-label {
            font-size: 10px !important;
            color: #555 !important;
        }

        .kpi-value {
            font-size: 20px !important;
            color: #000 !important;
            font-weight: bold !important;
        }

        .kpi-sub {
            font-size: 9px !important;
            color: #777 !important;
        }

        /* 5. Keep Charts Neatly Displayed Side-by-Side */
        .row {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: wrap !important;
        }
        
        .col-md-6 {
            width: 50% !important;
            flex: 0 0 50% !important;
        }

        .table-card { 
            box-shadow: none !important; 
            border: 1px solid #ddd !important; 
            background: #fff !important;
            padding: 12px !important;
        }

        #stockFlowChart, #netChangeChart {
            page-break-inside: avoid !important;
        }

        /* 6. Clean and Structured Table */
        .scroll-container { 
            max-height: none !important; 
            overflow: visible !important; 
        }

        .table-report {
            width: 100% !important;
            border: 1px solid #ddd !important;
            border-collapse: collapse !important;
        }

        .table-report thead th {
            position: static !important;
            background-color: #f5f5f5 !important;
            color: #000 !important;
            font-weight: bold !important;
            font-size: 11px !important;
            padding: 8px 6px !important;
            border-bottom: 2px solid #000 !important;
            border-right: 1px solid #ddd !important;
        }

        .table-report tbody td {
            padding: 8px 6px !important;
            font-size: 11px !important;
            border-bottom: 1px solid #ddd !important;
            border-right: 1px solid #ddd !important;
            background: transparent !important;
            color: #000 !important;
        }

        .table-report tr {
            page-break-inside: avoid !important;
        }

        /* 7. Status Pill and Custom Progress Bar styling for print */
        .status-pill {
            background: transparent !important;
            border: 1px solid #999 !important;
            color: #000 !important;
            padding: 2px 6px !important;
            font-size: 10px !important;
            min-width: 80px !important;
            text-align: center !important;
        }

        .progress-custom {
            display: none !important;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const BASE = '../api/inventory-report.php';
    let debounceTimer = null;
    let charts = {};

    // ── Fetch & Render ─────────────────────────────────────────
    async function loadInventory() {
        const search = document.getElementById('inventorySearch').value.trim();
        const status = document.getElementById('statusFilter').value;

        const params = new URLSearchParams({ search, status });
        const tbody  = document.getElementById('inventoryTbody');

        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading...</td></tr>`;

        try {
            const res    = await fetch(`${BASE}?${params}`);
            const result = await res.json();

            if (result.status !== 'success') throw new Error(result.message);

            updateKPIs(result.summary);
            renderRows(result.data, tbody);
            renderCharts(result.movement);
            bindCheckboxes();

        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>${err.message}</td></tr>`;
        }
    }

    // ── Charts ─────────────────────────────────────────────────
    function renderCharts(movement) {
        if (!movement || !movement.length) return;

        const labels = movement.map(m => m.week_label);
        const stockIn = movement.map(m => parseInt(m.stock_in));
        const stockOut = movement.map(m => parseInt(m.stock_out));
        const netChange = movement.map(m => parseInt(m.stock_in) - parseInt(m.stock_out));

        // 1. Stock Flow (Grouped Bar)
        if (!charts.flow) {
            charts.flow = new ApexCharts(document.getElementById('stockFlowChart'), {
                series: [
                    { name: 'Stock In', data: stockIn },
                    { name: 'Stock Out', data: stockOut }
                ],
                chart: { type: 'bar', height: 260, toolbar: { show: false } },
                colors: ['#00A389', '#D32F2F'],
                plotOptions: { bar: { columnWidth: '55%', borderRadius: 4 } },
                dataLabels: { enabled: false },
                xaxis: { categories: labels, labels: { style: { fontSize: '10px' } } },
                legend: { position: 'top', horizontalAlign: 'right', fontSize: '12px' }
            });
            charts.flow.render();
        } else {
            charts.flow.updateSeries([{ name: 'Stock In', data: stockIn }, { name: 'Stock Out', data: stockOut }]);
        }

        // 2. Net Change (Line Chart with markers)
        if (!charts.net) {
            charts.net = new ApexCharts(document.getElementById('netChangeChart'), {
                series: [{ name: 'Net Change', data: netChange }],
                chart: { type: 'line', height: 260, toolbar: { show: false } },
                colors: ['#9C27B0'],
                stroke: { curve: 'smooth', width: 3 },
                markers: { size: 5, strokeColors: '#fff', strokeWidth: 2 },
                xaxis: { categories: labels, labels: { style: { fontSize: '10px' } } },
                grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
                yaxis: { labels: { style: { fontSize: '10px' } } }
            });
            charts.net.render();
        } else {
            charts.net.updateSeries([{ name: 'Net Change', data: netChange }]);
        }
    }

    // ── KPI Cards ──────────────────────────────────────────────
    function updateKPIs(s) {
        document.getElementById('kpi-total').textContent   = s.total_skus;
        document.getElementById('kpi-healthy').textContent = s.healthy;
        document.getElementById('kpi-low').textContent     = s.low_critical;
        document.getElementById('kpi-out').textContent     = s.out_of_stock;
    }

    // ── Row Builder ────────────────────────────────────────────
    function renderRows(rows, tbody) {
        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-muted">No products found.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(item => {
            const stock   = parseInt(item.in_stock);
            const maxStock = 500;
            const pct     = Math.min(100, (stock / maxStock) * 100).toFixed(1);

            const statusMap = {
                healthy:     { label: 'Healthy',      cls: 'status-healthy', color: '#2E7D32' },
                low:         { label: 'Low',           cls: 'status-low',     color: '#EF6C00' },
                out_of_stock:{ label: 'Out of Stock',  cls: 'status-out',     color: '#C62828' },
            };
            const st = statusMap[item.status_key] ?? statusMap.healthy;

            const daysLeft = item.days_left !== null ? `${item.days_left}d` : '—';
            const sold30   = parseInt(item.sold_30d) || 0;

            return `
            <tr>
                <td><div class="form-check d-flex justify-content-center">
                    <input class="form-check-input row-checkbox" type="checkbox">
                </div></td>
                <td data-label="Product"><span class="product-bold">${escHtml(item.name)}</span></td>
                <td data-label="SKU"><span class="sku-sub">SKU : ${escHtml(item.sku ?? 'N/A')}</span></td>
                <td data-label="In Stock">
                    <div class="d-flex flex-column" style="width:110px;">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>${stock}</span><span>${maxStock}</span>
                        </div>
                        <div class="progress progress-custom">
                            <div class="progress-bar" style="width:${pct}%;background-color:${st.color};"></div>
                        </div>
                    </div>
                </td>
                <td data-label="Reorder Point">₹ ${parseInt(item.reorder_price ?? 0).toLocaleString('en-IN')}</td>
                <td data-label="Sold (30d)">${sold30}</td>
                <td data-label="Days Left">${daysLeft}</td>
                <td data-label="Status"><span class="status-pill ${st.cls}">${st.label}</span></td>
            </tr>`;
        }).join('');
    }

    // ── Checkbox Logic ─────────────────────────────────────────
    function bindCheckboxes() {
        const master = document.getElementById('masterCheckbox');
        const checkboxes = () => document.querySelectorAll('.row-checkbox');

        if (master) {
            master.onchange = (e) => checkboxes().forEach(cb => {
                cb.checked = e.target.checked;
                cb.closest('tr').classList.toggle('is-selected', e.target.checked);
            });
        }

        checkboxes().forEach(cb => cb.addEventListener('change', () => {
            cb.closest('tr').classList.toggle('is-selected', cb.checked);
            if (master) {
                const all = checkboxes();
                const checked = document.querySelectorAll('.row-checkbox:checked').length;
                master.checked = checked === all.length && all.length > 0;
                master.indeterminate = checked > 0 && checked < all.length;
            }
        }));
    }

    // ── Helpers ────────────────────────────────────────────────
    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // ── Events ─────────────────────────────────────────────────
    document.getElementById('inventorySearch').addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(loadInventory, 350);
    });
    
    const statusSelect = document.getElementById('statusFilter');
    statusSelect.addEventListener('change', (e) => {
        document.querySelectorAll('.kpi-card').forEach(card => {
            card.classList.remove('active');
            if (card.getAttribute('data-filter') === e.target.value) {
                card.classList.add('active');
            }
        });
        loadInventory();
    });

    // ── KPI Card Clicks ─────────────────────────────────────────
    document.querySelectorAll('.kpi-card').forEach(card => {
        card.addEventListener('click', () => {
            let filterValue = card.getAttribute('data-filter');
            
            // Toggle logic: if clicking the currently active filter, reset to 'Total SKUs' (which is '')
            if (statusSelect.value === filterValue && filterValue !== '') {
                filterValue = '';
            }
            
            statusSelect.value = filterValue;
            statusSelect.dispatchEvent(new Event('change'));
        });
    });

    // Initial Load
    loadInventory();
});
</script>

<?php require_once 'includes/footer.php'; ?>
