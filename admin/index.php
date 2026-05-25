<?php
/**
 * Sweets Website
 * =============================================================
 * File: index.php
 * Description: Admin Dashboard Home (Inline CSS & JS Version)
 * =============================================================
 */

require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';
require_once SERVICES_PATH . '/DashboardService.php';

$dashboardService = new DashboardService();
$dash = $dashboardService->getStats();
$chartData = $dashboardService->getChartData();

// Serialize Recent Orders for JS
$recentOrdersJson = [];
foreach ($dash['recent_orders'] as $order) {
    $recentOrdersJson[] = [
        'id' => '#' . ($order['order_number'] ?? $order['id']),
        'customer' => $order['customer_name'],
        'amount' => '₹ ' . number_format($order['total_amount'], 2),
        'status' => ucfirst($order['status']),
        'date' => date('Y-m-d', strtotime($order['created_at'])),
        'raw_id' => $order['id'],
        'raw_amount' => (float)$order['total_amount']
    ];
}

// Prepare Stats for JS
$statsJson = [
    [
        'label' => 'Total Orders',
        'value' => number_format($dash['total_orders']),
        'icon'  => 'fa-cube',
        'cls'   => 'icon-yellow',
        'trend' => $dash['trends']['orders'],
        'up'    => strpos($dash['trends']['orders'], '+') !== false || $dash['trends']['orders'] === '0%',
        'url'   => 'orders.php'
    ],
    [
        'label' => 'Total Revenue',
        'value' => $dash['revenue_formatted'],
        'icon'  => 'fa-chart-line',
        'cls'   => 'icon-green',
        'trend' => $dash['trends']['revenue'],
        'up'    => strpos($dash['trends']['revenue'], '+') !== false || $dash['trends']['revenue'] === '0%',
        'url'   => 'reports.php'
    ],
    [
        'label' => 'Total Customers',
        'value' => number_format($dash['total_customers']),
        'icon'  => 'fa-users',
        'cls'   => 'icon-purple',
        'trend' => $dash['trends']['customers'],
        'up'    => strpos($dash['trends']['customers'], '+') !== false || $dash['trends']['customers'] === '0%',
        'url'   => 'customers.php'
    ],
    [
        'label' => 'Pending Orders',
        'value' => $dash['pending_formatted'],
        'icon'  => 'fa-clock-rotate-left',
        'cls'   => 'icon-peach',
        'trend' => '+0%', // Placeholder trend
        'up'    => false,
        'url'   => 'orders.php'
    ]
];

// Prepare Category Legend for JS
$categoriesJson = [];
$catColors = ['#7a1f1f', '#a13a1d', '#e8782c', '#f5d9c0'];
$idx = 0;
foreach ($chartData['category_percentages'] as $name => $pctStr) {
    $pctVal = (float)str_replace('% Sales', '', $pctStr);
    $categoriesJson[] = [
        'name' => $name,
        'pct' => $pctVal,
        'color' => $catColors[$idx % count($catColors)]
    ];
    $idx++;
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
  :root{
    --brand:#7a1f1f;
    --brand-dark:#5a1414;
    --orange:#e8782c;
    --cream:#fff5e6;
    --soft-cream:#fef3e2;
    --light-bg:#fafafa;
    --border:#f1e6d6;
  }

  /* ── Date Range Picker ───────────────────────────────────────── */
  .drp-wrap { position: relative; }

  .drp-popup {
    display: none;
    position: absolute;
    top: calc(100% + 8px);
    right: 0;          /* aligns to right edge of button — no overflow */
    left: auto;
    z-index: 1050;
    background: #fff;
    border: 1px solid #e8d9c8;
    border-radius: 16px;
    box-shadow: 0 16px 48px rgba(122,31,31,0.14);
    padding: 16px 18px;
    width: 560px;
    animation: drpFadeIn 0.18s ease;
  }
  .drp-popup.open { display: block; }

  @keyframes drpFadeIn {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* Presets */
  .drp-presets {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding-bottom: 12px;
    border-bottom: 1px solid #f1e6d6;
    margin-bottom: 14px;
  }
  .drp-preset {
    background: #fef3e2;
    border: 1px solid #f1e6d6;
    border-radius: 999px;
    padding: 5px 14px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #555;
    cursor: pointer;
    transition: all 0.15s;
  }
  .drp-preset:hover, .drp-preset.active {
    background: var(--brand);
    color: #fff;
    border-color: var(--brand);
  }

  /* Two-month grid */
  .drp-calendars {
    display: flex;
    gap: 16px;
  }
  .drp-cal { flex: 1; min-width: 220px; }

  .drp-cal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
  }
  .drp-cal-header span {
    font-weight: 700;
    font-size: 0.9rem;
    color: var(--brand);
  }
  .drp-cal-nav {
    background: none;
    border: none;
    width: 28px; height: 28px;
    border-radius: 50%;
    cursor: pointer;
    color: var(--brand);
    display: flex; align-items: center; justify-content: center;
    transition: background 0.15s;
  }
  .drp-cal-nav:hover { background: #fef3e2; }

  .drp-day-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    text-align: center;
  }
  .drp-dow {
    font-size: 0.7rem;
    font-weight: 700;
    color: #aaa;
    padding: 4px 0;
  }
  .drp-day {
    width: 30px; height: 30px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.82rem;
    cursor: pointer;
    margin: 0 auto;
    transition: background 0.12s, color 0.12s;
    position: relative;
    z-index: 1;
  }
  .drp-day:hover:not(.drp-other):not(.drp-disabled) {
    background: #fef3e2;
    color: var(--brand);
  }
  .drp-day.drp-other { color: #ccc; cursor: default; }
  .drp-day.drp-disabled { color: #ddd; cursor: not-allowed; pointer-events: none; }
  .drp-day.drp-start, .drp-day.drp-end {
    background: var(--brand);
    color: #fff;
    border-radius: 50%;
    font-weight: 700;
  }
  .drp-day.drp-in-range {
    background: #fde9d4;
    color: var(--brand);
    border-radius: 0;
  }
  .drp-day.drp-start.drp-in-range { border-radius: 50% 0 0 50%; }
  .drp-day.drp-end.drp-in-range   { border-radius: 0 50% 50% 0; }
  .drp-day.drp-today:not(.drp-start):not(.drp-end) {
    border: 2px solid var(--orange);
    color: var(--orange);
    font-weight: 700;
  }

  /* Footer */
  .drp-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 14px;
    padding-top: 12px;
    border-top: 1px solid #f1e6d6;
  }
  .drp-selected {
    font-size: 0.82rem;
    color: #777;
    font-weight: 600;
  }
  .drp-actions { display: flex; gap: 8px; }
  .drp-btn-cancel {
    background: #f5f0eb;
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #555;
    cursor: pointer;
  }
  .drp-btn-apply {
    background: var(--brand);
    border: none;
    border-radius: 8px;
    padding: 8px 18px;
    font-size: 0.85rem;
    font-weight: 700;
    color: #fff;
    cursor: pointer;
    transition: background 0.15s;
  }
  .drp-btn-apply:hover { background: var(--brand-dark); }

  @media (max-width: 640px) {
    .drp-popup { width: calc(100vw - 20px); right: 0; left: auto; }
    .drp-calendars { flex-direction: column; }
    .drp-cal:last-child { display: none; }
  }
  
  .dashboard-title{
    color:var(--brand);
    font-weight:800;
    font-size:1.8rem;
    margin:0;
  }
  .top-btn{
    border:1px solid var(--border);
    background:#fff;
    border-radius:10px;
    padding:10px 16px;
    font-weight:600;
    color:#333;
    display:inline-flex;
    align-items:center;
    gap:8px;
  }
  .top-btn i{color:var(--brand);}
  .btn-explore{
    background:var(--brand);
    color:#fff;
    border-radius:10px;
    padding:10px 18px;
    font-weight:700;
    border:none;
    display:inline-flex;
    align-items:center;
    gap:8px;
  }
  .btn-explore:hover{background:var(--brand-dark);color:#fff;}

  /* Stat Cards */
  .stat-card{
    background:#fff;
    border:1px solid var(--border);
    border-radius:14px;
    padding:18px;
    height:100%;
    display:block;
    color:inherit;
    transition:transform 0.2s ease, box-shadow 0.2s ease;
  }
  .stat-card:hover{
    transform:translateY(-4px);
    box-shadow:0 8px 24px rgba(122,31,31,0.08);
    color:inherit;
  }
  .stat-card .label{color:#555;font-size:.95rem;margin-bottom:8px;}
  .stat-card .value{font-size:1.9rem;font-weight:800;margin:0;}
  .stat-icon{
    width:46px;height:46px;border-radius:12px;
    display:flex;align-items:center;justify-content:center;
    font-size:1.2rem;
  }
  .icon-yellow{background:#fde9a8;color:#b8860b;}
  .icon-green{background:#cdebd6;color:#1f8a4c;}
  .icon-purple{background:#d9d6f7;color:#5a4ad1;}
  .icon-peach{background:#fcd9c8;color:#d2552a;}

  .badge-pill{
    display:inline-flex;align-items:center;gap:6px;
    padding:5px 12px;border-radius:999px;
    font-size:.85rem;font-weight:700;
  }
  .badge-up{background:#cfeee0;color:#0e7a45;}
  .badge-down{background:#fad7d3;color:#b8392c;}

  /* Cards general */
  .panel{
    background:#fff;
    border:1px solid var(--border);
    border-radius:14px;
    padding:20px;
   
  }
  .panel h5{font-weight:800;margin-bottom:14px;}

  .legend-dot{width:10px;height:10px;border-radius:50%;display:inline-block;margin-right:6px;}

  /* Promo banner */
  .promo{
    background:linear-gradient(135deg,#a13a1d 0%, #7a1f1f 60%, #5a1414 100%);
    border-radius:14px;
    padding:24px;
    color:#fff;
    height:100%;
    position:relative;
    overflow:hidden;
  }
  .promo::before{
    content:"";position:absolute;top:-40px;right:-40px;
    width:160px;height:160px;border-radius:50%;
    background:rgba(255,255,255,.06);
  }
  .promo::after{
    content:"";position:absolute;bottom:-60px;right:-20px;
    width:200px;height:200px;border-radius:50%;
    background:rgba(255,255,255,.05);
  }
  .promo .tag{
    display:inline-block;background:rgba(255,255,255,.18);
    padding:6px 14px;border-radius:999px;font-size:.85rem;font-weight:600;
    margin-bottom:14px;
  }
  .promo h4{font-weight:800;line-height:1.2;}
  .promo p{opacity:.9;font-size:.95rem;}
  .btn-manage{
    background:#fff;color:var(--brand);
    border:none;border-radius:10px;padding:12px 22px;
    font-weight:800;font-size:1.05rem;
    width:100%;
  }
  .btn-manage:hover{background:#f8f2e8;color:var(--brand-dark);}

  /* Table */
  .recent-title{font-weight:800;font-size:1.5rem;margin:24px 0 14px;}
  .order-card{
    background:#fff;border:1px solid var(--border);border-radius:14px;overflow:hidden;
  }
  .orders-table{
    width:100%;
    border-collapse:separate;
    border-spacing:0;
  }
  .orders-table thead{
    background:var(--soft-cream);
  }
  .orders-table thead th{
    padding:16px;font-weight:800;color:#222;border:none;white-space:nowrap;
  }
  .orders-table thead th:first-child{border-top-left-radius:10px;}
  .orders-table thead th:last-child{border-top-right-radius:10px;}
  .orders-table tbody td{
    padding:18px 16px;
    border-bottom:1px solid #f5d9c0;
    vertical-align:middle;
  }
  .order-id{color:var(--brand);font-weight:700;text-decoration:none}
  .order-id:hover{text-decoration:underline;color:var(--brand);}
  .avatar{
    width:34px;height:34px;border-radius:50%;
    background:var(--orange);color:#fff;
    display:inline-flex;align-items:center;justify-content:center;
    font-weight:700;font-size:.8rem;margin-right:10px;
  }
  .status-pill{
    display:inline-block;padding:8px 22px;border-radius:8px;
    font-weight:700;font-size:.9rem;min-width:120px;text-align:center;
  }
  .status-available{background:#cdebd6;color:#1f8a4c;}
  .status-pending{background:#fce4c1;color:#b8721c;}
  .status-cancelled{background:#fad7d3;color:#b8392c;}
  .action-btn{
    background:none;border:none;color:#333;margin:0 4px;font-size:1rem;
  }
  .action-btn:hover{color:var(--brand);}

  /* Chart container heights */
  .chart-wrap{position:relative;height:260px;}
  .donut-wrap{position:relative;height:240px;margin:0 auto;}

  @media (max-width: 768px){
    .dashboard-title{font-size:1.4rem;}
    .stat-card .value{font-size:1.5rem;}
    .donut-wrap{height: 200px;}
    .cat-row { font-size: 0.9rem; }
    
    /* Responsive Table as Cards */
    .orders-table thead { display: none; }
    .orders-table, .orders-table tbody, .orders-table tr, .orders-table td {
      display: block;
      width: 100%;
    }
    .orders-table tr {
      margin-bottom: 1rem;
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 0.5rem;
      background: #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }
    .orders-table td {
      text-align: right;
      padding: 0.75rem 0.5rem;
      border-bottom: 1px solid #f9f9f9;
      position: relative;
      padding-left: 45%;
      display: flex;
      justify-content: flex-end;
      align-items: center;
    }
    .orders-table td:last-child { border-bottom: 0; justify-content: flex-end; }
    .orders-table td::before {
      content: attr(data-label);
      position: absolute;
      left: 0.5rem;
      width: 40%;
      text-align: left;
      font-weight: 700;
      color: #666;
      font-size: 0.9rem;
    }
    .status-pill {
      padding: 6px 12px;
      font-size: 0.8rem;
      min-width: auto;
    }
  }
  
  @media (max-width: 320px) {
    .orders-table td {
      padding-left: 0.5rem;
      flex-direction: column;
      align-items: flex-start;
      text-align: left;
    }
    .orders-table td::before {
      position: relative;
      left: 0;
      width: 100%;
      display: block;
      margin-bottom: 0.3rem;
    }
    .orders-table td {
      justify-content: flex-start;
    }
    .orders-table td:last-child{
              display: flex;
        justify-content: center;
        flex-direction: row;
        align-items: center;
    }
  }
  .toast-container{position:fixed;top:18px;right:18px;z-index:9999}
</style>

<div class="main-content">
  <?php require_once 'includes/topbar.php'; ?>
  
  <div class="container-fluid px-3 px-md-4 px-lg-5 py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
      <h1 class="dashboard-title">Dashboard</h1>
      <div class="d-flex gap-2 flex-wrap">
        <!-- Date Range Picker -->
        <div class="drp-wrap" id="drpWrap">
          <button class="top-btn" id="dateBtn" onclick="toggleCalendar(event)" style="border:1px solid var(--border);background:#fff;">
            <i class="far fa-calendar"></i>
            <span id="dateBtnLabel">Last 30 Days</span>
            <i class="fas fa-chevron-down" style="font-size:0.7rem;opacity:0.6;"></i>
          </button>

          <!-- Calendar Popup -->
          <div class="drp-popup" id="drpPopup">
            <!-- Quick Presets -->
            <div class="drp-presets">
              <button class="drp-preset active" data-preset="today">Today</button>
              <button class="drp-preset" data-preset="7">Last 7 Days</button>
              <button class="drp-preset active-default" data-preset="30">Last 30 Days</button>
              <button class="drp-preset" data-preset="90">Last 90 Days</button>
              <button class="drp-preset" data-preset="year">This Year</button>
              <button class="drp-preset" data-preset="custom">Custom Range</button>
            </div>
            <!-- Calendars -->
            <div class="drp-calendars" id="drpCalendars">
              <div class="drp-cal" id="drpCalLeft"></div>
              <div class="drp-cal" id="drpCalRight"></div>
            </div>
            <!-- Footer -->
            <div class="drp-footer">
              <span class="drp-selected" id="drpSelected">Select a date range</span>
              <div class="drp-actions">
                <button class="drp-btn-cancel" onclick="closeCalendar()">Cancel</button>
                <button class="drp-btn-apply" onclick="applyDateRange()">Apply</button>
              </div>
            </div>
          </div>
        </div>
        <div class="dropdown">
          <button class="btn-explore dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fas fa-download"></i> Export Report
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#" onclick="exportReport()"><i class="bi bi-file-earmark-spreadsheet me-2"></i> CSV</a></li>
            <li><a class="dropdown-item" href="#" onclick="exportPDF()"><i class="bi bi-file-earmark-pdf me-2"></i> PDF</a></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4" id="statRow"></div>

    <!-- Sales Analytics + Promo -->
    <div class="row g-3 mb-4">
      <div class="col-12 col-lg-8">
        <div class="panel">
          <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Sales Analytics</h5>
            <div>
              <span class="me-3"><span class="legend-dot" style="background:#7a1f1f"></span>Revenue</span>
              <span><span class="legend-dot" style="background:#e8782c"></span>Volume</span>
            </div>
          </div>
          <div class="chart-wrap"><canvas id="salesChart"></canvas></div>
        </div>
      </div>
      <div class="col-12 col-lg-4">
        <div class="promo">
          <span class="tag">Limited Edition</span>
          <h4>Artisanal Classic Vijaya karadant Gold Collection</h4>
          <p>Boost sales for the upcoming Diwali season by featuring our luxury gift hampers.</p>
          <button class="btn-manage mt-3" onclick="manageCollection()">Manage Collection</button>
        </div>
      </div>
    </div>

    <!-- Revenue Overview + Sales by Category -->
    <div class="row g-3 mb-4">
      <div class="col-12 col-lg-8">
        <div class="panel">
          <h5>Revenue Overview</h5>
          <div class="chart-wrap"><canvas id="revenueChart"></canvas></div>
        </div>
      </div>
      <div class="col-12 col-lg-4">
        <div class="panel">
          <h5>Sales by Category</h5>
          <div class="row align-items-center h-100">
            <div class="col-6">
              <div class="donut-wrap"><canvas id="categoryChart"></canvas>
                <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;">
                  <div style="font-weight:800;font-size:1.1rem;"><?php echo $dash['revenue_formatted']; ?></div>
                  <div style="font-size:.8rem;color:#666;">Total Sales</div>
                </div>
              </div>
            </div>
            <div class="col-6" id="catLegend"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="panel">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="panel-title mb-0">Recent Orders</h5>
        <a href="orders.php" class="btn btn-sm btn-brand-outline">View All Orders</a>
      </div>
      <div class="table-responsive">
      <table class="orders-table">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="ordersBody"></tbody>
      </table>
    </div>

  </div>
</div>

<!-- ============== VIEW MODAL ============== -->
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Order Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="viewBody"></div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- ============== DELETE MODAL ============== -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content text-center p-4" style="border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
      <div class="modal-body p-0">
        <div class="mb-3">
          <div style="width: 60px; height: 60px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
            <i class="fas fa-trash text-danger" style="font-size: 1.5rem; color: #dc2626 !important;"></i>
          </div>
        </div>
        <h5 class="mb-2 fw-bold">Delete Order?</h5>
        <p class="text-muted small mb-4" id="deleteModalText">Are you sure you want to delete this order? This cannot be undone.</p>
        <div class="d-flex justify-content-center gap-2">
          <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
          <button type="button" class="btn btn-danger fw-bold px-4" id="confirmDeleteBtn" style="border-radius: 8px; background: #dc2626; border: none;">Delete</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Toast container -->
<div class="toast-container position-fixed top-0 end-0 p-3" id="toastBox"></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
/* ============================================================
   DATA
   ============================================================ */
const stats = <?php echo json_encode($statsJson); ?>;
const orders = <?php echo json_encode($recentOrdersJson); ?>;
const categories = <?php echo json_encode($categoriesJson); ?>;
const chartData = <?php echo json_encode($chartData); ?>;

/* ============================================================
   HELPERS & RENDERERS
   ============================================================ */
const $q = (s)=>document.querySelector(s);
function initials(name){
  return name.split(/\s+/).map(s=>s[0]).slice(0,2).join('').toUpperCase();
}

function showToast(msg){
  const wrap = document.createElement('div');
  wrap.className = "toast align-items-center text-white border-0 show";
  wrap.style.background = "var(--brand)";
  wrap.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div>
    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
  $q('#toastBox').appendChild(wrap);
  setTimeout(()=>wrap.remove(), 3000);
}

function renderStats(){
  $q('#statRow').innerHTML = stats.map(s=>`
    <div class="col-12 col-sm-6 col-lg-3">
      <a href="${s.url}" class="stat-card text-decoration-none">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="label">${s.label}</div>
            <p class="value">${s.value}</p>
          </div>
          <div class="stat-icon ${s.cls}"><i class="fas ${s.icon}"></i></div>
        </div>
        <span class="badge-pill ${s.up?'badge-up':'badge-down'} mt-2">
          <i class="fas ${s.up?'fa-arrow-trend-up':'fa-arrow-trend-down'}"></i> ${s.trend}
        </span>
      </a>
    </div>
  `).join('');
}

function statusBadge(s){
  let cls = 'status-pending';
  if (['Delivered','Completed','Active','Available','Paid'].includes(s)) cls = 'status-available';
  if (s === 'Cancelled' || s === 'Refunded') cls = 'status-cancelled';
  
  return `<span class="status-pill ${cls}">${s}</span>`;
}

function renderOrders(){
  const tb = $q('#ordersBody');
  if(!orders.length){
    tb.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">No orders yet</td></tr>`;
    return;
  }
  tb.innerHTML = orders.map((o,i)=>`
    <tr>
      <td data-label="Order ID"><a class="order-id" href="#" onclick="viewOrder(${i});return false;">${o.id}</a></td>
      <td data-label="Customer"><span class="avatar">${initials(o.customer)}</span>${o.customer}</td>
      <td data-label="Amount">${o.amount}</td>
      <td data-label="Status">${statusBadge(o.status)}</td>
      <td data-label="Date">${o.date}</td>
      <td data-label="Actions" class="text-end">
        <a href="order-details.php?id=${o.raw_id}" class="action-btn" title="Edit"><i class="fas fa-pen"></i></a>
        <button class="action-btn" title="View" onclick="viewOrder(${i})"><i class="fas fa-eye"></i></button>
        <button class="action-btn" title="Delete" onclick="deleteOrder(${i})"><i class="fas fa-trash"></i></button>
      </td>
    </tr>
  `).join('');
}

function renderCategoryLegend(){
  $q('#catLegend').innerHTML = categories.map(c=>`
    <div class="mb-2 cat-row">
      <strong><span class="legend-dot" style="background:${c.color}"></span>${c.name}</strong>
      <div class="text-muted small">${c.pct}% Sales</div>
    </div>
  `).join('');
}

/* ============================================================
   CHARTS
   ============================================================ */
// Centralized chart instances to prevent "Canvas in use" errors
window.adminCharts = window.adminCharts || {};

function initCharts(){
  // Ensure chartData defaults exist if backend falls back
  const lbls = chartData.sales?.labels || ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
  const rev  = chartData.sales?.revenue || [12,3,17,10,20,11,24];
  const vol  = chartData.sales?.volume || [10,4,12,13,18,12,22];

  // Sales Analytics chart (two lines)
  const salesCtx = document.getElementById('salesChart');
  if (salesCtx) {
    if (window.adminCharts.sales) window.adminCharts.sales.destroy();
    window.adminCharts.sales = new Chart(salesCtx, {
      type:'line',
      data:{
        labels: lbls,
        datasets:[
          {label:'Revenue', data:rev, borderColor:'#7a1f1f', backgroundColor:'rgba(122,31,31,.08)', tension:.4, fill:true, pointBackgroundColor:'#7a1f1f', pointRadius:5},
          {label:'Volume', data:vol, borderColor:'#e8782c', backgroundColor:'rgba(232,120,44,.08)', tension:.4, fill:true, pointBackgroundColor:'#e8782c', pointRadius:5}
        ]
      },
      options:{
        responsive:true, maintainAspectRatio:false,
        plugins:{legend:{display:false}},
        scales:{
          y:{beginAtZero:true, ticks:{callback:v=>v/1000+'K'}, grid:{color:'#f1e6d6'}},
          x:{grid:{display:false}}
        }
      }
    });
  }

  // Revenue chart with tooltip-style point
  const revCtx = document.getElementById('revenueChart');
  if (revCtx) {
    if (window.adminCharts.revenue) window.adminCharts.revenue.destroy();
    
    // Custom gradient for revenue chart
    const grad = revCtx.getContext('2d').createLinearGradient(0,0,0,260);
    grad.addColorStop(0,'rgba(232,120,44,.25)');
    grad.addColorStop(1,'rgba(232,120,44,0)');
    
    window.adminCharts.revenue = new Chart(revCtx,{
      type:'line',
      data:{
        labels: chartData.revenue?.labels || ['Oct','Nov','Dec','Jan','Fab','Mar','Apr'],
        datasets:[{
          data: chartData.revenue?.data || [6,9,11,10,20,17,24],
          borderColor:'#7a1f1f',
          backgroundColor:grad,
          tension:.45, fill:true,
          pointBackgroundColor:'#e8782c', pointBorderColor:'#fff', pointRadius:5, pointHoverRadius:7
        }]
      },
      options:{
        responsive:true, maintainAspectRatio:false,
        plugins:{legend:{display:false}, tooltip:{
          callbacks:{label:ctx=>'₹'+(ctx.parsed.y/1000).toFixed(1)+'K'},
          backgroundColor:'#222', padding:8
        }},
        scales:{
          y:{beginAtZero:true, ticks:{callback:v=>v/1000+'K'}, grid:{color:'#f1e6d6', borderDash:[4,4]}},
          x:{grid:{display:false}}
        }
      }
    });
  }

  // Donut
  const catCtx = document.getElementById('categoryChart');
  if (catCtx) {
    if (window.adminCharts.category) window.adminCharts.category.destroy();
    window.adminCharts.category = new Chart(catCtx,{
      type:'doughnut',
      data:{
        labels:categories.map(c=>c.name),
        datasets:[{
          data:categories.map(c=>Math.max(c.pct, 2)), // Guarantee visible slice
          backgroundColor:categories.map(c=>c.color),
          borderWidth:0,
          hoverOffset: 4
        }]
      },
      options:{
        responsive:true, maintainAspectRatio:false,
        cutout:'70%',
        plugins:{
          legend:{display:false},
          tooltip: {
            callbacks: {
              label: function(context) {
                const realPct = categories[context.dataIndex].pct;
                return ` ${realPct}%`;
              }
            }
          }
        }
      }
    });
  }
}

/* ============================================================
   ACTIONS
   ============================================================ */
/* ============================================================
   DATE RANGE PICKER ENGINE
   ============================================================ */
(function(){
  const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  const DAYS   = ['Su','Mo','Tu','We','Th','Fr','Sa'];

  let leftYear, leftMonth, startDate, endDate, hoverDate, isOpen = false;

  function today() {
    const d = new Date(); d.setHours(0,0,0,0); return d;
  }

  function fmt(d) {
    if (!d) return '';
    return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
  }

  function setPreset(preset) {
    const t = today();
    if (preset === 'today') {
      startDate = new Date(t); endDate = new Date(t);
    } else if (preset === '7') {
      endDate = new Date(t); startDate = new Date(t); startDate.setDate(startDate.getDate() - 6);
    } else if (preset === '30') {
      endDate = new Date(t); startDate = new Date(t); startDate.setDate(startDate.getDate() - 29);
    } else if (preset === '90') {
      endDate = new Date(t); startDate = new Date(t); startDate.setDate(startDate.getDate() - 89);
    } else if (preset === 'year') {
      startDate = new Date(t.getFullYear(), 0, 1); endDate = new Date(t);
    }
    // For custom, don't reset dates — let user pick
    document.querySelectorAll('.drp-preset').forEach(b => b.classList.remove('active'));
    document.querySelector(`.drp-preset[data-preset="${preset}"]`).classList.add('active');
    render();
  }

  function sameDay(a, b) {
    return a && b && a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
  }

  function buildMonth(year, month, side) {
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const t = today();

    let html = `<div class="drp-cal-header">`;
    if (side === 'left') {
      html += `<button class="drp-cal-nav" onclick="window._drp.nav(-1)"><i class="fas fa-chevron-left" style="font-size:0.7rem"></i></button>`;
    } else {
      html += `<span></span>`;
    }
    html += `<span>${MONTHS[month]} ${year}</span>`;
    if (side === 'right') {
      html += `<button class="drp-cal-nav" onclick="window._drp.nav(1)"><i class="fas fa-chevron-right" style="font-size:0.7rem"></i></button>`;
    } else {
      html += `<span></span>`;
    }
    html += `</div><div class="drp-day-grid">`;

    DAYS.forEach(d => { html += `<div class="drp-dow">${d}</div>`; });

    // Empty cells before first day
    for (let i = 0; i < firstDay; i++) {
      const prevDate = new Date(year, month, -(firstDay - i - 1));
      html += `<div class="drp-day drp-other">${prevDate.getDate()}</div>`;
    }

    for (let d = 1; d <= daysInMonth; d++) {
      const cur = new Date(year, month, d);
      let cls = 'drp-day';
      if (sameDay(cur, t)) cls += ' drp-today';
      const effectiveEnd = endDate || hoverDate;
      if (startDate && sameDay(cur, startDate)) cls += ' drp-start';
      if (effectiveEnd && sameDay(cur, effectiveEnd)) cls += ' drp-end';
      const inRange = startDate && effectiveEnd && cur > startDate && cur < effectiveEnd;
      if (inRange) cls += ' drp-in-range';
      if (sameDay(cur, startDate) && effectiveEnd && cur < effectiveEnd) cls += ' drp-in-range';
      if (effectiveEnd && sameDay(cur, effectiveEnd) && startDate && cur > startDate) cls += ' drp-in-range';

      html += `<div class="${cls}" data-date="${cur.toISOString()}" onclick="window._drp.pick('${cur.toISOString()}')" onmouseenter="window._drp.hover('${cur.toISOString()}')">${d}</div>`;
    }

    // Fill remaining
    const totalCells = firstDay + daysInMonth;
    const remainder = totalCells % 7 ? 7 - (totalCells % 7) : 0;
    for (let i = 1; i <= remainder; i++) {
      html += `<div class="drp-day drp-other">${i}</div>`;
    }

    html += '</div>';
    return html;
  }

  function render() {
    const rightYear  = leftMonth === 11 ? leftYear + 1 : leftYear;
    const rightMonth = leftMonth === 11 ? 0 : leftMonth + 1;

    document.getElementById('drpCalLeft').innerHTML  = buildMonth(leftYear, leftMonth, 'left');
    document.getElementById('drpCalRight').innerHTML = buildMonth(rightYear, rightMonth, 'right');

    const sel = document.getElementById('drpSelected');
    if (startDate && endDate) {
      sel.textContent = fmt(startDate) + '  →  ' + fmt(endDate);
    } else if (startDate) {
      sel.textContent = fmt(startDate) + '  →  ...';
    } else {
      sel.textContent = 'Select a date range';
    }
  }

  function init() {
    const t = today();
    leftYear  = t.getFullYear();
    leftMonth = t.getMonth() - 1 < 0 ? 11 : t.getMonth() - 1;
    if (leftMonth === 11) leftYear--;

    // Default: last 30 days
    endDate   = new Date(t);
    startDate = new Date(t); startDate.setDate(startDate.getDate() - 29);

    // Preset click handlers
    document.querySelectorAll('.drp-preset').forEach(btn => {
      btn.addEventListener('click', () => setPreset(btn.dataset.preset));
    });

    // Close on outside click
    document.addEventListener('click', (e) => {
      const wrap = document.getElementById('drpWrap');
      if (isOpen && wrap && !wrap.contains(e.target)) closeCalendar();
    });

    render();
  }

  // Public API
  window._drp = {
    nav(dir) {
      leftMonth += dir;
      if (leftMonth > 11) { leftMonth = 0; leftYear++; }
      if (leftMonth < 0)  { leftMonth = 11; leftYear--; }
      render();
    },
    pick(iso) {
      const d = new Date(iso); d.setHours(0,0,0,0);
      if (!startDate || (startDate && endDate)) {
        startDate = d; endDate = null; hoverDate = null;
        document.querySelectorAll('.drp-preset').forEach(b => b.classList.remove('active'));
        document.querySelector('.drp-preset[data-preset="custom"]').classList.add('active');
      } else {
        if (d < startDate) { endDate = startDate; startDate = d; }
        else               { endDate = d; }
        hoverDate = null;
      }
      render();
    },
    hover(iso) {
      if (startDate && !endDate) {
        hoverDate = new Date(iso); hoverDate.setHours(0,0,0,0);
        render();
      }
    }
  };

  document.addEventListener('DOMContentLoaded', init);

  window.toggleCalendar = function(e) {
    e.stopPropagation();
    const popup = document.getElementById('drpPopup');
    isOpen = !popup.classList.contains('open');
    popup.classList.toggle('open', isOpen);
  };

  window.closeCalendar = function() {
    document.getElementById('drpPopup').classList.remove('open');
    isOpen = false;
  };

  window.applyDateRange = function() {
    if (!startDate || !endDate) { showToast('Please select both start and end dates.'); return; }
    const label = fmt(startDate) + ' – ' + fmt(endDate);
    document.getElementById('dateBtnLabel').textContent = label;
    closeCalendar();
    showToast('Date range applied: ' + label);
    // TODO: re-fetch dashboard data for this date range via AJAX
  };

  window.changeDateRange = function(label) {
    document.getElementById('dateBtnLabel').textContent = label;
    showToast('Date range: ' + label);
  };
})();

function exportReport(){
  const headers = ['Order ID','Customer','Amount','Status','Date'];
  const rows = orders.map(o=>[o.id,o.customer,o.amount,o.status,o.date]);
  const csv = [headers,...rows].map(r=>r.map(v=>`"${v}"`).join(',')).join('\n');
  const blob = new Blob([csv],{type:'text/csv'});
  const url  = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href=url; a.download='dashboard-report-'+new Date().toISOString().slice(0,10)+'.csv';
  a.click(); URL.revokeObjectURL(url);
  showToast('Report exported as CSV');
}

function exportPDF(){
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF('p', 'mm', 'a4');
  
  doc.setFontSize(18);
  doc.setTextColor(122, 31, 31);
  doc.text("Dashboard Summary Report", 14, 20);
  
  doc.setFontSize(10);
  doc.setTextColor(100);
  doc.text(`Generated on: ${new Date().toLocaleString()}`, 14, 28);
  
  const tableData = orders.map(o => [o.id, o.customer, o.amount, o.status, o.date]);
  
  doc.autoTable({
    startY: 35,
    head: [['Order ID', 'Customer', 'Amount', 'Status', 'Date']],
    body: tableData,
    headStyles: { fillColor: [122, 31, 31], fontSize: 10 },
    alternateRowStyles: { fillColor: [250, 243, 226] }
  });
  
  doc.save(`dashboard_report_${new Date().toISOString().slice(0,10)}.pdf`);
  showToast('Report exported as PDF');
}

function manageCollection(){ window.location.href = 'combos.php'; }

function viewOrder(i){
  const o = orders[i];
  $q('#viewBody').innerHTML=`
    <div class="d-flex align-items-center mb-3">
      <span class="avatar" style="width:48px;height:48px;font-size:1rem">${initials(o.customer)}</span>
      <div class="ms-2">
        <div class="fw-bold">${o.customer}</div>
        <div class="text-muted small">${o.id}</div>
      </div>
    </div>
    <p class="mb-2"><strong>Amount:</strong> ${o.amount}</p>
    <p class="mb-2"><strong>Date:</strong> ${o.date}</p>
    <p class="mb-3"><strong>Status:</strong> ${statusBadge(o.status)}</p>
    <hr>
    <div class="d-grid">
      <a href="order-details.php?id=${o.raw_id}" class="btn btn-brand-outline">View Full Order Details</a>
    </div>
  `;
  new bootstrap.Modal('#viewModal').show();
}

let orderToDeleteIndex = null;

function deleteOrder(i){
  orderToDeleteIndex = i;
  const o = orders[i];
  $q('#deleteModalText').innerText = `Are you sure you want to PERMANENTLY delete Order ${o.id}? This cannot be undone.`;
  new bootstrap.Modal('#deleteModal').show();
}

async function executeDeleteOrder() {
  if (orderToDeleteIndex === null) return;
  const i = orderToDeleteIndex;
  const o = orders[i];
  
  const btn = $q('#confirmDeleteBtn');
  const originalText = btn.innerHTML;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
  btn.disabled = true;

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const fd = new FormData();
    fd.append('action', 'delete_order');
    fd.append('order_id', o.raw_id);
    fd.append('csrf_token', csrfToken);

    const res = await fetch('api/v1/orders.php', {
      method: 'POST',
      body: fd
    });
    
    const result = await res.json();
    
    if(result.status === 'success') {
      orders.splice(i, 1);
      renderOrders();
      showToast(result.message);
      bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
    } else {
      showToast(result.message || 'Error deleting order', 'error');
    }
  } catch (err) {
    console.error(err);
    showToast('Infrastructure error during deletion', 'error');
  } finally {
    btn.innerHTML = originalText;
    btn.disabled = false;
    orderToDeleteIndex = null;
  }
}

/* ============================================================
   INIT
   ============================================================ */
document.addEventListener('DOMContentLoaded',()=>{
  renderStats();
  renderOrders();
  renderCategoryLegend();
  initCharts();
  
  $q('#confirmDeleteBtn')?.addEventListener('click', executeDeleteOrder);
});
</script>

<?php require_once 'includes/footer.php'; ?>