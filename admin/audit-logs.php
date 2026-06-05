<?php
/**
 * Sweets Website
 * =============================================================
 * File: audit-logs.php
 * Description: Dynamic Admin Audit Logs Viewer
 * =============================================================
 */

// ── MUST be before ANY output (HTML headers already sent check) ──
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/src/Service/AuditLogService.php';
use App\Services\AuditLogService;

// ── CSV Export — handle before HTML output ────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    if (session_status() === PHP_SESSION_NONE) session_start();
    require_once 'includes/auth.php';

    $filterAction = trim($_GET['action']      ?? '');
    $filterEntity = trim($_GET['entity_type'] ?? '');
    $filterSearch = trim($_GET['search']      ?? '');
    $filterFrom   = trim($_GET['date_from']   ?? '');
    $filterTo     = trim($_GET['date_to']     ?? '');
    $exportFilters = array_filter([
        'action'      => $filterAction,
        'entity_type' => $filterEntity,
        'search'      => $filterSearch,
        'date_from'   => $filterFrom,
        'date_to'     => $filterTo,
    ]);

    $actionLabels = [
        'create'               => 'Create',
        'update'               => 'Update',
        'delete'               => 'Delete',
        'delete_image'         => 'Delete Image',
        'toggle_status'        => 'Toggle Status',
        'set_primary_image'    => 'Set Primary Image',
        'cancelled_by_customer'=> 'Cancelled by Customer',
        'admin_created'        => 'Admin Created',
        'admin_suspended'      => 'Admin Suspended',
        'admin_reactivated'    => 'Admin Reactivated',
        'admin_removed'        => 'Admin Removed',
        'invite_sent'          => 'Invite Sent',
        'login'                => 'Login',
        'logout'               => 'Logout',
        'login_failed'         => 'Failed Login',
        'settings_updated'     => 'Settings Updated',
        'password_setup'       => 'Password Setup',
    ];

    $allResult = AuditLogService::getLogsPage(1, 9999, $exportFilters);
    $allLogs   = $allResult['logs'];

    // Disable output buffering so CSV streams cleanly
    while (ob_get_level()) ob_end_clean();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="audit-logs-' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');
    // UTF-8 BOM so Excel opens correctly
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['#', 'Action', 'Action Label', 'Module', 'Entity ID', 'Actor', 'Actor Email', 'IP Address', 'Date', 'Time', 'Payload']);
    $i = 1;
    foreach ($allLogs as $row) {
        fputcsv($out, [
            $i++,
            $row['action'],
            $actionLabels[$row['action']] ?? ucwords(str_replace('_', ' ', $row['action'])),
            ucwords(str_replace('_', ' ', $row['entity_type'])),
            $row['entity_id'],
            $row['actor_name'] ?? 'System',
            $row['actor_email'] ?? '',
            $row['ip_address'] ?? 'unknown',
            date('d-m-Y', strtotime($row['created_at'])),
            date('h:i:s A', strtotime($row['created_at'])),
            $row['payload'] ?? '',
        ]);
    }
    fclose($out);
    exit;
}

$pageStyles  = ['assets/css/pages/admin-audit-logs.css'];
$pageScripts = ['assets/js/admin/audit-logs.js'];

require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';

if (!hasPermission('permissions:manage')) {
    header("Location: dashboard.php");
    exit;
}

// AuditLogService already loaded above for CSV export path

// ── Filters ──────────────────────────────────────────────────
$page        = max(1, (int)($_GET['page']       ?? 1));
$perPage     = 20;
$filterAction = trim($_GET['action']      ?? '');
$filterEntity = trim($_GET['entity_type'] ?? '');
$filterSearch = trim($_GET['search']      ?? '');
$filterFrom   = trim($_GET['date_from']   ?? '');
$filterTo     = trim($_GET['date_to']     ?? '');

$filters = array_filter([
    'action'      => $filterAction,
    'entity_type' => $filterEntity,
    'search'      => $filterSearch,
    'date_from'   => $filterFrom,
    'date_to'     => $filterTo,
]);

$result       = AuditLogService::getLogsPage($page, $perPage, $filters);
$logs         = $result['logs'];
$totalLogs    = $result['total'];
$totalPages   = $result['pages'];
$stats        = AuditLogService::getSummaryStats();
$actionTypes  = AuditLogService::getDistinctActions();
$entityTypes  = AuditLogService::getDistinctEntityTypes();

// ── Action icon/color map ─────────────────────────────────────
$actionMeta = [
    // Generic CRUD (matches DB values)
    'create'               => ['icon' => 'plus-circle',       'color' => '#198754', 'label' => 'Create'],
    'update'               => ['icon' => 'pencil-square',      'color' => '#0d6efd', 'label' => 'Update'],
    'delete'               => ['icon' => 'trash',              'color' => '#dc3545', 'label' => 'Delete'],
    'delete_image'         => ['icon' => 'image',              'color' => '#dc3545', 'label' => 'Delete Image'],
    'toggle_status'        => ['icon' => 'toggle-on',          'color' => '#fd7e14', 'label' => 'Toggle Status'],
    'set_primary_image'    => ['icon' => 'star',               'color' => '#f59e0b', 'label' => 'Set Primary Image'],
    'cancelled_by_customer'=> ['icon' => 'x-circle',           'color' => '#dc3545', 'label' => 'Cancelled by Customer'],
    // Admin-specific
    'admin_created'        => ['icon' => 'person-plus',        'color' => '#0d6efd', 'label' => 'Admin Created'],
    'admin_suspended'      => ['icon' => 'pause-circle',       'color' => '#dc3545', 'label' => 'Admin Suspended'],
    'admin_reactivated'    => ['icon' => 'check-circle',       'color' => '#198754', 'label' => 'Admin Reactivated'],
    'admin_removed'        => ['icon' => 'trash',              'color' => '#dc3545', 'label' => 'Admin Removed'],
    'invite_sent'          => ['icon' => 'envelope',           'color' => '#fd7e14', 'label' => 'Invite Sent'],
    'invite_resent'        => ['icon' => 'envelope-arrow-up',  'color' => '#fd7e14', 'label' => 'Invite Resent'],
    'role_assigned'        => ['icon' => 'person-badge',       'color' => '#6610f2', 'label' => 'Role Assigned'],
    'permissions_synced'   => ['icon' => 'shield-check',       'color' => '#0dcaf0', 'label' => 'Permissions Updated'],
    'login'                => ['icon' => 'box-arrow-in-right', 'color' => '#198754', 'label' => 'Login'],
    'logout'               => ['icon' => 'box-arrow-right',    'color' => '#6c757d', 'label' => 'Logout'],
    'login_failed'         => ['icon' => 'x-circle',           'color' => '#dc3545', 'label' => 'Failed Login'],
    'order_updated'        => ['icon' => 'grid-3x3-gap',       'color' => '#0d6efd', 'label' => 'Order Updated'],
    'refund_processed'     => ['icon' => 'arrow-counterclockwise', 'color' => '#fd7e14', 'label' => 'Refund Processed'],
    'settings_updated'     => ['icon' => 'gear',               'color' => '#6c757d', 'label' => 'Settings Updated'],
    'password_setup'       => ['icon' => 'key',                'color' => '#198754', 'label' => 'Password Setup'],
];
?>

<style>
    /* Back button styling (inline to bypass browser caching) */
    .btn-back-audit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        border: 1.5px solid var(--maroon, #7a1f1f);
        color: var(--maroon, #7a1f1f);
        background: transparent;
        transition: all 0.25s ease;
        font-size: 1.2rem;
        text-decoration: none;
        flex-shrink: 0;
    }
    .btn-back-audit:hover {
        background: var(--maroon, #7a1f1f);
        color: #fff;
        transform: translateX(-3px);
        box-shadow: 0 4px 12px rgba(122, 31, 31, 0.15);
    }
    @media (max-width: 575.98px) {
        .btn-back-audit {
            width: 36px;
            height: 36px;
            font-size: 1.05rem;
        }
    }
</style>

<div class="main-content">
    <?php require_once 'includes/topbar.php'; ?>

    <!-- Page Header -->
    <div class="audit-page-header">
        <div class="d-flex align-items-center gap-3">
            <a href="audit-logs.php" class="btn-back-audit" title="Back to Audit Logs">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="audit-title"><i class="bi bi-journal-text me-2"></i>Audit Logs</h1>
                <p class="audit-subtitle">Complete activity trail for all admin actions</p>
            </div>
        </div>
        <button onclick="exportLogs()" class="btn-export-audit">
            <i class="bi bi-download me-2"></i>Export CSV
        </button>
    </div>

    <!-- Stats Row -->
    <div class="audit-stats-row">
        <div class="audit-stat-card">
            <div class="audit-stat-icon" style="background:#fff3cd;color:#fd7e14">
                <i class="bi bi-calendar-day"></i>
            </div>
            <div>
                <div class="audit-stat-num"><?= number_format($stats['today']) ?></div>
                <div class="audit-stat-lbl">Actions Today</div>
            </div>
        </div>
        <div class="audit-stat-card">
            <div class="audit-stat-icon" style="background:#d1e7dd;color:#198754">
                <i class="bi bi-calendar-week"></i>
            </div>
            <div>
                <div class="audit-stat-num"><?= number_format($stats['week']) ?></div>
                <div class="audit-stat-lbl">This Week</div>
            </div>
        </div>
        <div class="audit-stat-card">
            <div class="audit-stat-icon" style="background:#f5e6e6;color:#7a1f1f">
                <i class="bi bi-journal-text"></i>
            </div>
            <div>
                <div class="audit-stat-num"><?= number_format($stats['total']) ?></div>
                <div class="audit-stat-lbl">Total Logs</div>
            </div>
        </div>
        <div class="audit-stat-card">
            <div class="audit-stat-icon" style="background:#e0cffc;color:#6610f2">
                <i class="bi bi-people"></i>
            </div>
            <div>
                <div class="audit-stat-num"><?= number_format($stats['actors']) ?></div>
                <div class="audit-stat-lbl">Active Actors (7d)</div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="audit-filter-card">
        <form method="GET" action="" id="filterForm" class="audit-filter-row">
            <div class="audit-filter-item">
                <label class="audit-filter-lbl">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                        </svg>
                    </span>
                    <input type="text" name="search" class="form-control border-0 bg-light" placeholder="Action, entity, actor..." value="<?= htmlspecialchars($filterSearch) ?>">
                </div>
            </div>
            <div class="audit-filter-item">
                <label class="audit-filter-lbl">Action Type</label>
                <select name="action" class="form-select bg-light border-0">
                    <option value="">All Actions</option>
                    <?php foreach ($actionTypes as $a): ?>
                        <option value="<?= htmlspecialchars($a) ?>" <?= $filterAction === $a ? 'selected' : '' ?>>
                            <?= htmlspecialchars($actionMeta[$a]['label'] ?? ucwords(str_replace('_', ' ', $a))) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="audit-filter-item">
                <label class="audit-filter-lbl">Module</label>
                <select name="entity_type" class="form-select bg-light border-0">
                    <option value="">All Modules</option>
                    <?php foreach ($entityTypes as $et): ?>
                        <option value="<?= htmlspecialchars($et) ?>" <?= $filterEntity === $et ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $et))) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="audit-filter-item">
                <label class="audit-filter-lbl">From Date</label>
                <input type="date" name="date_from" class="form-control bg-light border-0" value="<?= htmlspecialchars($filterFrom) ?>">
            </div>
            <div class="audit-filter-item">
                <label class="audit-filter-lbl">To Date</label>
                <input type="date" name="date_to" class="form-control bg-light border-0" value="<?= htmlspecialchars($filterTo) ?>">
            </div>
            <div class="audit-filter-actions">
                <button type="submit" class="btn-apply-filter" id="applyBtn">
                    <i class="bi bi-funnel me-1"></i>Apply
                    <?php
                        $activeCount = count(array_filter([$filterAction, $filterEntity, $filterSearch, $filterFrom, $filterTo]));
                        if ($activeCount > 0):
                    ?>
                        <span class="filter-badge"><?= $activeCount ?></span>
                    <?php endif; ?>
                </button>
                <a href="audit-logs.php" class="btn-clear-filter">Clear</a>
            </div>
        </form>
    </div>

    <!-- Results Info -->
    <div class="audit-results-info">
        <div class="audit-results-left">
            <span>
                Showing <strong><?= count($logs) ?></strong> of <strong><?= number_format($totalLogs) ?></strong> logs
            </span>
            <?php if (!empty($filters)): ?>
                <div class="audit-active-chips" id="activeChips">
                    <?php foreach ($filters as $key => $val):
                        $chipLabels = [
                            'action'      => 'Action',
                            'entity_type' => 'Module',
                            'search'      => 'Search',
                            'date_from'   => 'From',
                            'date_to'     => 'To',
                        ];
                        $chipLabel = ($chipLabels[$key] ?? $key) . ': ' . htmlspecialchars($val);
                    ?>
                        <span class="audit-chip">
                            <?= $chipLabel ?>
                            <a href="<?= htmlspecialchars('audit-logs.php?' . http_build_query(array_filter(array_merge($filters, [$key => null])))) ?>" class="audit-chip-remove" title="Remove filter">&times;</a>
                        </span>
                    <?php endforeach; ?>
                    <a href="audit-logs.php" class="audit-chip audit-chip-clear">Clear all</a>
                </div>
            <?php endif; ?>
        </div>
        <span class="text-muted small">Page <?= $page ?> of <?= $totalPages ?: 1 ?></span>
    </div>

    <!-- Logs Table -->
    <div class="audit-table-card">
        <?php if (empty($logs)): ?>
            <div class="audit-empty">
                <i class="bi bi-journal-x"></i>
                <p>No audit logs found matching your filters.</p>
                <?php if (!empty($filters)): ?>
                    <a href="audit-logs.php" class="btn btn-sm btn-outline-secondary">Clear Filters</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
        <!-- ── DESKTOP TABLE (hidden on mobile) ───────────────────── -->
        <div class="table-responsive audit-desktop-table">
            <table class="table audit-log-table m-0" id="auditTable">
                <thead>
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Actor</th>
                        <th>IP Address</th>
                        <th>Timestamp</th>
                        <th style="width:60px">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $i => $log):
                        $meta = $actionMeta[$log['action']] ?? ['icon' => 'circle', 'color' => '#6c757d', 'label' => ucwords(str_replace('_', ' ', $log['action']))];
                        $rowNum = ($page - 1) * $perPage + $i + 1;
                        $payload = $log['payload'] ? json_decode($log['payload'], true) : null;
                    ?>
                    <tr class="audit-row" id="log-row-<?= $log['id'] ?>">
                        <td class="text-muted small align-middle"><?= $rowNum ?></td>
                        <td class="align-middle">
                            <div class="d-flex align-items-center gap-2">
                                <div class="audit-action-icon" style="background:<?= $meta['color'] ?>22;color:<?= $meta['color'] ?>">
                                    <i class="bi bi-<?= $meta['icon'] ?>"></i>
                                </div>
                                <span class="audit-action-label"><?= htmlspecialchars($meta['label']) ?></span>
                            </div>
                        </td>
                        <td class="align-middle">
                            <span class="badge rounded-pill px-3 py-2" style="background:#f5f5f5;color:#444;font-size:.78rem;">
                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $log['entity_type']))) ?>
                            </span>
                        </td>
                        <td class="align-middle">
                            <?php if ($log['actor_name']): ?>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="audit-avatar"><?= strtoupper(substr($log['actor_name'], 0, 1)) ?></div>
                                    <div>
                                        <div class="fw-bold small"><?= htmlspecialchars($log['actor_name']) ?></div>
                                        <div class="text-muted" style="font-size:.75rem;"><?= htmlspecialchars($log['actor_email'] ?? '') ?></div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="text-muted small"><em>System</em></span>
                            <?php endif; ?>
                        </td>
                        <td class="align-middle text-muted small">
                            <i class="bi bi-geo-alt me-1"></i>
                            <?= htmlspecialchars($log['ip_address'] ?? 'unknown') ?>
                        </td>
                        <td class="align-middle">
                            <div class="fw-bold small"><?= date('d M Y', strtotime($log['created_at'])) ?></div>
                            <div class="text-muted" style="font-size:.75rem;"><?= date('h:i:s A', strtotime($log['created_at'])) ?></div>
                        </td>
                        <td class="align-middle text-center">
                            <?php if ($payload): ?>
                                <button class="btn-view-log" onclick="viewLogDetail(<?= $log['id'] ?>, <?= htmlspecialchars(json_encode($payload), ENT_QUOTES) ?>, '<?= htmlspecialchars($meta['label']) ?>')" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ── MOBILE CARD FEED (hidden on desktop) ─────────────────── -->
        <div class="audit-card-feed">
            <?php foreach ($logs as $i => $log):
                $meta = $actionMeta[$log['action']] ?? ['icon' => 'circle', 'color' => '#6c757d', 'label' => ucwords(str_replace('_', ' ', $log['action']))];
                $payload = $log['payload'] ? json_decode($log['payload'], true) : null;
                $actorInitial = $log['actor_name'] ? strtoupper(substr($log['actor_name'], 0, 1)) : 'S';
                $actorName    = $log['actor_name'] ? htmlspecialchars($log['actor_name']) : 'System';
                $moduleName   = htmlspecialchars(ucwords(str_replace('_', ' ', $log['entity_type'])));
                $dateStr      = date('d M Y', strtotime($log['created_at']));
                $timeStr      = date('h:i A', strtotime($log['created_at']));
            ?>
            <div class="audit-card" id="card-log-<?= $log['id'] ?>">
                <!-- Left: action icon -->
                <div class="ac-icon" style="background:<?= $meta['color'] ?>18;color:<?= $meta['color'] ?>">
                    <i class="bi bi-<?= $meta['icon'] ?>"></i>
                </div>

                <!-- Center: all text content -->
                <div class="ac-body">
                    <!-- Row 1: action title + timestamp -->
                    <div class="ac-row1">
                        <span class="ac-action"><?= htmlspecialchars($meta['label']) ?></span>
                        <span class="ac-time"><?= $dateStr ?> &middot; <?= $timeStr ?></span>
                    </div>
                    <!-- Row 2: module badge + actor -->
                    <div class="ac-row2">
                        <span class="ac-module"><?= $moduleName ?></span>
                        <span class="ac-sep">·</span>
                        <span class="ac-actor"><?= $actorName ?></span>
                    </div>
                </div>

                <!-- Right: kebab menu or dash -->
                <div class="ac-menu">
                    <?php if ($payload): ?>
                        <button class="ac-btn-menu" onclick="viewLogDetail(<?= $log['id'] ?>, <?= htmlspecialchars(json_encode($payload), ENT_QUOTES) ?>, '<?= htmlspecialchars($meta['label']) ?>')" title="View Details">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                    <?php else: ?>
                        <span class="ac-nodot">—</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="audit-pagination">
            <?php
            $qs = http_build_query(array_filter(array_merge($filters, ['page' => null])));
            for ($p = 1; $p <= $totalPages; $p++):
                $isActive = ($p === $page);
                $pQs = http_build_query(array_filter(array_merge($filters, ['page' => $p])));
            ?>
                <a href="?<?= $pQs ?>" class="audit-page-btn <?= $isActive ? 'active' : '' ?>">
                    <?= $p ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Log Detail Modal -->
<div class="modal fade" id="logDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0" style="background:#f5e6e6;">
                <h5 class="modal-title fw-bold" style="color:#7a1f1f;" id="logDetailTitle">Log Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="logDetailBody">
                <pre class="audit-json-view" id="logDetailJson"></pre>
            </div>
        </div>
    </div>
</div>

<script>
/* ── View Detail Modal ────────────────────────────────────────── */
function viewLogDetail(id, payload, actionLabel) {
    document.getElementById('logDetailTitle').textContent = '🔍 ' + actionLabel + ' — Details';
    document.getElementById('logDetailJson').textContent  = JSON.stringify(payload, null, 2);
    new bootstrap.Modal(document.getElementById('logDetailModal')).show();
}

/* ── Export CSV (keeps current filters) ──────────────────────── */
function exportLogs() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.open('audit-logs.php?' + params.toString(), '_blank');
}

/* ── Dynamic Filter Engine ───────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('filterForm');
    if (!form) return; // safety guard

    const searchInput = form.querySelector('input[name="search"]');
    const selects     = form.querySelectorAll('select');
    const dateInputs  = form.querySelectorAll('input[type="date"]');

    /* Loading overlay */
    const overlay = document.createElement('div');
    overlay.id = 'auditLoadingOverlay';
    overlay.innerHTML = '<div class="audit-spinner"><div class="audit-spinner-ring"></div><span>Filtering&hellip;</span></div>';
    document.body.appendChild(overlay);

    function showLoading() {
        overlay.classList.add('active');
    }

    function submitForm() {
        /* Ensure pagination resets to page 1 on any filter change */
        let pageInput = form.querySelector('input[name="page"]');
        if (!pageInput) {
            pageInput = Object.assign(document.createElement('input'), {
                type: 'hidden', name: 'page', value: '1'
            });
            form.appendChild(pageInput);
        } else {
            pageInput.value = '1';
        }
        showLoading();
        /* Small delay so the overlay renders before navigation */
        setTimeout(function () { form.submit(); }, 30);
    }

    /* Live search — 500 ms debounce */
    if (searchInput) {
        let searchTimer;
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(submitForm, 500);
        });
        /* Also submit on Enter key */
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); clearTimeout(searchTimer); submitForm(); }
        });
    }

    /* Instant submit when a dropdown value changes */
    selects.forEach(function (sel) {
        sel.addEventListener('change', submitForm);
    });

    /* Instant submit when a date is picked */
    dateInputs.forEach(function (d) {
        d.addEventListener('change', submitForm);
    });

    /* Show loading on manual Apply button submit */
    form.addEventListener('submit', function () {
        showLoading();
    });

    /* Show loading when paginating */
    document.querySelectorAll('.audit-page-btn').forEach(function (link) {
        link.addEventListener('click', showLoading);
    });
});
</script>

<?php // CSV export is handled at the top of the file before HTML output ?>

<?php require_once 'includes/footer.php'; ?>
