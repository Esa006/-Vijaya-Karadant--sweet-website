<?php
/**
 * Sweets Website
 * =============================================================
 * File: admin/stock-requests.php
 * Description: View customer restock requests for Out-of-Stock items
 * =============================================================
 */

require_once '../config/config.php';
require_once 'includes/auth.php';

$db = Database::getInstance();

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_notified') {
    $id = (int)$_POST['id'];
    
    // Fetch request details
    $stmt = $db->prepare("SELECT sn.*, IF(sn.product_type = 'combo', c.name, p.name) as item_name FROM stock_notifications sn LEFT JOIN products p ON sn.product_id = p.id AND sn.product_type = 'product' LEFT JOIN combos c ON sn.product_id = c.id AND sn.product_type = 'combo' WHERE sn.id = :id");
    $stmt->execute([':id' => $id]);
    $req = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($req) {
        require_once '../src/Service/EmailService.php';
        $loginUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost/sweet-website/';
        $productName = $req['item_name'] ?? 'The item you requested';
        $subject = "Good News! {$productName} is Back in Stock";
        
        $html = "
            <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                <h2 style='color: #8C3333; border-bottom: 2px solid #8C3333; padding-bottom: 10px;'>Back in Stock Alert!</h2>
                <p>Hello,</p>
                <p>You recently asked us to notify you when <strong>{$productName}</strong> is back in stock.</p>
                <p>Good news! It's available right now. Hurry before it sells out again!</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$loginUrl}' style='background-color: #8C3333; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block;'>Shop Now</a>
                </div>
            </div>
        ";
        
        $emailService = new \App\Service\EmailService();
        $emailSent = $emailService->sendHtmlEmail($req['email'], $subject, $html);
        
        if ($emailSent) {
            $updateStmt = $db->prepare("UPDATE stock_notifications SET status = 'notified', notified_at = NOW() WHERE id = :id");
            $updateStmt->execute([':id' => $id]);
            $_SESSION['success_msg'] = "Email sent automatically and request marked as notified.";
        } else {
            // Still mark as notified or not? It's better to show an error and not mark it.
            $_SESSION['success_msg'] = "Failed to send email. Check API key.";
        }
    }
    
    header("Location: stock-requests.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_demo') {
    try {
        $productRows = $db->query("SELECT id FROM products ORDER BY id ASC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);

        if (empty($productRows)) {
            $_SESSION['success_msg'] = "No products available to create demo requests.";
            header("Location: stock-requests.php");
            exit;
        }

        $insert = $db->prepare(
            "INSERT INTO stock_notifications (product_id, product_type, email, status, created_at)
             VALUES (:product_id, 'product', :email, 'pending', NOW())"
        );

        $emails = ['demo.customer1@example.com', 'demo.customer2@example.com', 'demo.customer3@example.com'];
        $created = 0;

        foreach ($productRows as $index => $row) {
            $productId = (int)($row['id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }

            $insert->execute([
                ':product_id' => $productId,
                ':email'      => $emails[$index] ?? ('demo.customer' . ($index + 1) . '@example.com')
            ]);
            $created++;
        }

        $_SESSION['success_msg'] = $created > 0
            ? "Added {$created} demo stock request(s)."
            : "Could not add demo stock requests.";
    } catch (Exception $e) {
        error_log('[stock-requests] add_demo failed: ' . $e->getMessage());
        $_SESSION['success_msg'] = 'Failed to add demo requests.';
    }

    header("Location: stock-requests.php");
    exit;
}

// Fetch requests
$sql = "
    SELECT sn.*, 
           IF(sn.product_type = 'combo', c.name, p.name) as item_name,
           IF(sn.product_type = 'combo', c.image, (SELECT image_path FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1)) as item_image
    FROM stock_notifications sn
    LEFT JOIN products p ON sn.product_id = p.id AND sn.product_type = 'product'
    LEFT JOIN combos c ON sn.product_id = c.id AND sn.product_type = 'combo'
    ORDER BY sn.created_at DESC
";
$requests = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$pendingCount = 0;
$notifiedCount = 0;
foreach($requests as $req) {
    if ($req['status'] === 'pending') $pendingCount++;
    else $notifiedCount++;
}

$totalRequests = count($requests);
$pendingPercent = $totalRequests > 0 ? (int)round(($pendingCount / $totalRequests) * 100) : 0;

$summaryCards = [
    [
        'title' => 'Total Requests',
        'value' => $totalRequests,
        'borderClass' => 'border-primary',
        'borderStyle' => '#0d6efd'
    ],
    [
        'title' => 'Pending Requests',
        'value' => $pendingCount,
        'borderClass' => 'border-warning',
        'borderStyle' => '#ffc107'
    ],
    [
        'title' => 'Notified Users',
        'value' => $notifiedCount,
        'borderClass' => 'border-success',
        'borderStyle' => '#198754'
    ],
    [
        'title' => 'Pending Rate',
        'value' => $pendingPercent . '%',
        'borderClass' => 'border-info',
        'borderStyle' => '#0dcaf0'
    ]
];

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div class="main-content">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body pt-0 px-4">
        
        <div class="d-flex justify-content-between align-items-center py-4 mb-3 border-bottom">
            <h2 class="fw-bold mb-0" style="color: #4a3728;">Stock Requests (Notify Me)</h2>
            <form method="POST" class="m-0">
                <input type="hidden" name="action" value="add_demo">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn btn-outline-primary btn-sm fw-bold">
                    <i class="bi bi-plus-circle me-1"></i> Add Demo
                </button>
            </form>
        </div>

        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i> <?php echo htmlspecialchars($_SESSION['success_msg']); unset($_SESSION['success_msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4 mb-4">
            <?php foreach ($summaryCards as $cardIndex => $card): ?>
                <?php
                    $filter = 'all';
                    if ($card['title'] === 'Pending Requests' || $card['title'] === 'Pending Rate') {
                        $filter = 'pending';
                    } elseif ($card['title'] === 'Notified Users') {
                        $filter = 'notified';
                    }
                ?>
                <div class="col-xl-3 col-md-6">
                    <button
                        type="button"
                        class="p-4 rounded-3 shadow-sm bg-white border <?php echo $card['borderClass']; ?> w-100 text-start stock-filter-card <?php echo $cardIndex === 0 ? 'is-active' : ''; ?>"
                        style="border-left: 5px solid <?php echo $card['borderStyle']; ?> !important;"
                        data-filter="<?php echo htmlspecialchars($filter); ?>">
                        <h5 class="text-muted fw-bold mb-2"><?php echo htmlspecialchars($card['title']); ?></h5>
                        <h2 class="fw-bold mb-0"><?php echo htmlspecialchars((string)$card['value']); ?></h2>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="px-4 pt-3 pb-2 border-bottom">
                    <div class="d-flex flex-column flex-md-row gap-2 align-items-md-center justify-content-between">
                        <div>
                            <span class="small text-muted">Current filter:</span>
                            <span id="currentFilterLabel" class="badge bg-light text-dark border ms-2">All Requests</span>
                        </div>
                        <div class="input-group input-group-sm" style="max-width: 320px;">
                            <span class="input-group-text bg-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                </svg>
                            </span>
                            <input type="text" id="stockRequestSearch" class="form-control" placeholder="Search email or item...">
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background-color: #fdf8f2;">
                            <tr>
                                <th class="py-3 px-4 fw-bold text-dark border-0">Customer Email</th>
                                <th class="py-3 px-4 fw-bold text-dark border-0">Item Name</th>
                                <th class="py-3 px-4 fw-bold text-dark border-0 d-none d-lg-table-cell">Type</th>
                                <th class="py-3 px-4 fw-bold text-dark border-0 d-none d-lg-table-cell">Requested On</th>
                                <th class="py-3 px-4 fw-bold text-dark border-0">Status</th>
                                <th class="py-3 px-4 fw-bold text-dark border-0 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($requests)): ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">No stock requests found.</td></tr>
                            <?php else: ?>
                                <?php foreach($requests as $req): ?>
                                    <tr
                                        data-status="<?php echo htmlspecialchars($req['status']); ?>"
                                        data-email="<?php echo htmlspecialchars(strtolower((string)($req['email'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>"
                                        data-item="<?php echo htmlspecialchars(strtolower((string)($req['item_name'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>">
                                        <td class="py-3 px-4 fw-bold td-email" data-label="Email"><?php echo htmlspecialchars((string)($req['email'] ?? '')); ?></td>
                                        <td class="py-3 px-4 td-item" data-label="Item">
                                            <div class="d-flex align-items-center gap-2">
                                                <?php $img = $req['item_image'] ? '../' . $req['item_image'] : '../assets/images/placeholders/product-placeholder.png'; ?>
                                                <img src="<?php echo htmlspecialchars($img); ?>" class="sr-item-img" style="width:40px;height:40px;object-fit:cover;border-radius:6px;flex-shrink:0;">
                                                <span class="text-dark fw-medium sr-item-name"><?php echo htmlspecialchars($req['item_name'] ?? 'Unknown Item'); ?></span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 d-none d-lg-table-cell td-type" data-label="Type"><span class="badge bg-secondary text-capitalize"><?php echo $req['product_type']; ?></span></td>
                                        <td class="py-3 px-4 text-muted d-none d-lg-table-cell td-date" data-label="Date"><?php echo date('M d, Y h:i A', strtotime($req['created_at'])); ?></td>
                                        <td class="py-3 px-4 td-status" data-label="Status">
                                            <?php if($req['status'] === 'pending'): ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-clock"></i> Pending</span>
                                            <?php else: ?>
                                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Notified</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-4 text-end td-action" data-label="Action">
                                            <?php if($req['status'] === 'pending'): ?>
                                                <form method="POST" style="display:inline;width:100%;">
                                                    <input type="hidden" name="action" value="mark_notified">
                                                    <input type="hidden" name="id" value="<?php echo $req['id']; ?>">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-success fw-bold sr-notify-btn" onclick="return confirm('Send an automated email to this customer and mark as notified?')">Send Email &amp; Mark Notified</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted small"><i class="bi bi-check"></i> Done</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (!empty($requests)): ?>
                    <div class="px-4 py-3 border-top d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div class="small text-muted" id="stockRequestsMeta">Showing 0 of 0</div>
                        <div class="d-flex align-items-center gap-2">
                            <label for="stockRequestsPerPage" class="small text-muted mb-0">Rows:</label>
                            <select id="stockRequestsPerPage" class="form-select form-select-sm" style="width: 82px;">
                                <option value="10" selected>10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                            </select>
                            <div class="btn-group btn-group-sm" role="group" aria-label="Pagination">
                                <button type="button" class="btn btn-outline-secondary" id="stockRequestsPrev">Prev</button>
                                <button type="button" class="btn btn-outline-secondary" id="stockRequestsNext">Next</button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<style>
    .stock-filter-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        cursor: pointer;
    }

    .stock-filter-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08) !important;
    }

    .stock-filter-card.is-active {
        box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.2), 0 10px 20px rgba(0, 0, 0, 0.08) !important;
    }

    /* ================================================
       AMAZON-STYLE MOBILE CARD TABLE — ≤767px
       ================================================ */
    @media (max-width: 767.98px) {

        /* Page header: stack on mobile */
        .content-body > .d-flex.justify-content-between {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 10px;
        }

        .content-body > .d-flex.justify-content-between form .btn {
            width: 100%;
        }

        /* Table → card transform */
        .table-responsive table {
            display: block;
        }
        .table-responsive thead {
            display: none !important;
        }
        .table-responsive tbody {
            display: block;
        }
        .table-responsive tr {
            display: block;
            background: #fff;
            border: 1px solid #fee7d6 !important;
            border-radius: 14px;
            margin-bottom: 14px;
            padding: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            position: relative;
        }
        .table-responsive td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0 !important;
            border-bottom: 1px solid #f8f4f0 !important;
            border-top: none !important;
            text-align: right;
            font-size: 13px;
        }
        .table-responsive td:last-child {
            border-bottom: none !important;
        }

        /* data-label pseudo-elements */
        .table-responsive td::before {
            content: attr(data-label);
            font-weight: 700;
            color: #8B2E2E;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
            flex-shrink: 0;
            margin-right: 10px;
            opacity: 0.85;
        }

        /* Email: word-break so long addresses wrap */
        .table-responsive td.td-email {
            word-break: break-all;
            overflow-wrap: anywhere;
            align-items: flex-start;
        }

        .table-responsive td.td-email::before {
            padding-top: 1px;
            flex-shrink: 0;
        }

        /* Item: image fixed size, name truncates */
        .table-responsive td.td-item {
            align-items: center;
        }

        .table-responsive td.td-item .sr-item-img {
            width: 36px !important;
            height: 36px !important;
            flex-shrink: 0;
        }

        .table-responsive td.td-item .sr-item-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 130px;
            display: block;
        }

        /* Hidden columns (Type, Date): show as labeled rows */
        .table-responsive td.d-none.d-lg-table-cell {
            display: flex !important;
        }

        /* Status: badge aligns right */
        .table-responsive td.td-status {
            align-items: center;
        }

        /* Action: full-width centered button */
        .table-responsive td.td-action {
            flex-direction: column;
            align-items: stretch;
            border-bottom: none !important;
            border-top: 1px solid #f3ede7 !important;
            margin-top: 4px;
            padding-top: 10px !important;
        }

        .table-responsive td.td-action::before {
            display: none;
        }

        .table-responsive td.td-action form {
            display: block !important;
            width: 100%;
        }

        .table-responsive td.td-action .sr-notify-btn {
            display: block;
            width: 100%;
            text-align: center;
            padding: 10px 0;
            border-radius: 8px;
            font-size: 13px;
        }

        .table-responsive td.td-action .text-muted.small {
            display: block;
            text-align: center;
            padding: 8px 0;
        }
    }

    /* ================================================
       320px MICRO-ADJUSTMENTS
       ================================================ */
    @media (max-width: 380px) {

        /* Stat cards: single column */
        .row.g-4.mb-4 > [class*="col-"] {
            flex: 0 0 100% !important;
            max-width: 100% !important;
        }

        .stock-filter-card {
            padding: 14px 12px !important;
        }

        .stock-filter-card h5 {
            font-size: 0.85rem;
        }

        .stock-filter-card h2 {
            font-size: 1.5rem;
        }

        /* Page title: smaller */
        .content-body h2 {
            font-size: 1.2rem !important;
        }

        /* Card table rows: tighter padding */
        .table-responsive tr {
            padding: 12px 10px;
            border-radius: 12px;
        }

        /* Item name: narrower max-width */
        .table-responsive td.td-item .sr-item-name {
            max-width: 100px;
        }

        /* Email text: smaller */
        .table-responsive td.td-email {
            font-size: 12px;
        }

        /* Content padding */
        .content-body.px-4 {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterCards = document.querySelectorAll('.stock-filter-card');
        const rows = document.querySelectorAll('table tbody tr[data-status]');
        const currentFilterLabel = document.getElementById('currentFilterLabel');
        const searchInput = document.getElementById('stockRequestSearch');
        const perPageSelect = document.getElementById('stockRequestsPerPage');
        const prevBtn = document.getElementById('stockRequestsPrev');
        const nextBtn = document.getElementById('stockRequestsNext');
        const metaLabel = document.getElementById('stockRequestsMeta');
        let activeFilter = 'all';
        let currentPage = 1;

        const filterLabelMap = {
            all: 'All Requests',
            pending: 'Pending Requests',
            notified: 'Notified Users'
        };

        function applyFilterAndSearch() {
            const searchTerm = (searchInput && searchInput.value ? searchInput.value : '').toLowerCase().trim();
            const perPage = perPageSelect ? Math.max(1, parseInt(perPageSelect.value, 10) || 10) : 10;
            const filteredRows = [];

            rows.forEach(function (row) {
                const rowStatus = (row.getAttribute('data-status') || '').toLowerCase();
                const rowEmail = (row.getAttribute('data-email') || '').toLowerCase();
                const rowItem = (row.getAttribute('data-item') || '').toLowerCase();

                const matchesFilter = (activeFilter === 'all' || rowStatus === activeFilter);
                const matchesSearch = searchTerm === '' || rowEmail.includes(searchTerm) || rowItem.includes(searchTerm);

                if (matchesFilter && matchesSearch) {
                    filteredRows.push(row);
                }

                row.style.display = 'none';
            });

            const total = filteredRows.length;
            const totalPages = Math.max(1, Math.ceil(total / perPage));
            if (currentPage > totalPages) {
                currentPage = totalPages;
            }

            const startIndex = (currentPage - 1) * perPage;
            const endIndex = Math.min(startIndex + perPage, total);

            for (let i = startIndex; i < endIndex; i++) {
                filteredRows[i].style.display = '';
            }

            if (currentFilterLabel) {
                currentFilterLabel.textContent = filterLabelMap[activeFilter] || 'All Requests';
            }

            if (metaLabel) {
                if (total === 0) {
                    metaLabel.textContent = 'Showing 0 of 0';
                } else {
                    metaLabel.textContent = 'Showing ' + (startIndex + 1) + '-' + endIndex + ' of ' + total;
                }
            }

            if (prevBtn) {
                prevBtn.disabled = currentPage <= 1;
            }
            if (nextBtn) {
                nextBtn.disabled = currentPage >= totalPages;
            }
        }

        filterCards.forEach(function (card) {
            card.addEventListener('click', function () {
                const filter = (this.getAttribute('data-filter') || 'all').toLowerCase();

                filterCards.forEach(function (el) {
                    el.classList.remove('is-active');
                });
                this.classList.add('is-active');

                activeFilter = filter;
                currentPage = 1;
                applyFilterAndSearch();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                currentPage = 1;
                applyFilterAndSearch();
            });
        }

        if (perPageSelect) {
            perPageSelect.addEventListener('change', function () {
                currentPage = 1;
                applyFilterAndSearch();
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                if (currentPage > 1) {
                    currentPage--;
                    applyFilterAndSearch();
                }
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                currentPage++;
                applyFilterAndSearch();
            });
        }

        applyFilterAndSearch();
    });
</script>
