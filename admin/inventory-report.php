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

<div class="main-content inventory-report-page">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="container-fluid px-4">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
            <h2 class="dashboard-title mb-0">Inventory Status</h2>
            <div class="header-actions d-flex gap-2">
                <button class="btn btn-outline-brown d-flex align-items-center" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i>
                    <span>Print Report</span>
                </button>
            </div>
        </div>

        <!-- KPI Summary Cards (populated dynamically) -->
        <div class="row g-3 mb-4" id="summaryCards">
            <div class="col-6 col-md-3">
                <div class="kpi-card">
                    <div class="kpi-label">Total SKUs</div>
                    <div class="kpi-value" id="kpi-total">—</div>
                    <div class="kpi-sub">across all categories</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card">
                    <div class="kpi-label">Healthy Stock</div>
                    <div class="kpi-value text-success" id="kpi-healthy">—</div>
                    <div class="kpi-sub">of catalog</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card">
                    <div class="kpi-label">Low / Critical</div>
                    <div class="kpi-value text-warning" id="kpi-low">—</div>
                    <div class="kpi-sub">need reordering</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="kpi-card">
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
        <div class="table-card mb-0 p-3">
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
        .admin-sidebar, .topbar, .header-actions { display: none !important; }
        .main-content { margin-left: 0 !important; padding: 0 !important; }
        .table-card { box-shadow: none; border: none; }
        .scroll-container { max-height: none; overflow: visible; }
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
    document.getElementById('statusFilter').addEventListener('change', loadInventory);

    // Initial Load
    loadInventory();
});
</script>

<?php require_once 'includes/footer.php'; ?>
