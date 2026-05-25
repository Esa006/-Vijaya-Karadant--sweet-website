<?php
/**
 * Sweets Website
 * =============================================================
 * File: queries.php
 * Description: Customer Contact Messages & Queries Management
 * Author: Antigravity - Senior Backend Engineer
 * =============================================================
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once REPOS_PATH . '/ContactRepository.php';

$contactRepo = new ContactRepository();

/**
 * Handle AJAX Actions (Must be before any HTML output)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    // Auth Check
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        header('HTTP/1.1 403 Forbidden');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    header('Content-Type: application/json');
    $id = (int)($_POST['id'] ?? 0);
    
    if ($_POST['action'] === 'mark_read') {
        $success = $contactRepo->markAsRead($id);
        echo json_encode(['success' => $success]);
    } elseif ($_POST['action'] === 'delete') {
        $success = $contactRepo->delete($id);
        echo json_encode(['success' => $success]);
    }
    exit;
}

require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';

$messages = $contactRepo->getAllMessages(100);
$unreadCount = $contactRepo->getUnreadCount();
?>

<style>
    :root {
        --brand-maroon: #4a1a04;
        --brand-gold: #8e4422;
        --bg-warm: #faf8f5;
        --border-color: #e8e0d8;
    }

    .queries-page {
        background-color: var(--bg-warm);
        min-height: 100vh;
    }

    .query-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        margin-bottom: 20px;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .query-card.unread {
        border-left: 5px solid var(--brand-gold);
    }

    .query-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    }

    .query-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f0ebe5;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .query-body {
        padding: 20px 24px;
    }

    .query-footer {
        padding: 15px 24px;
        background: #fdfaf8;
        border-top: 1px solid #f0ebe5;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .customer-name {
        font-weight: 700;
        color: var(--brand-maroon);
        font-size: 1.1rem;
        margin-bottom: 2px;
    }

    .query-meta {
        font-size: 0.85rem;
        color: #6b7280;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .query-text {
        font-size: 0.95rem;
        line-height: 1.6;
        color: #374151;
        white-space: pre-wrap;
    }

    .status-badge {
        padding: 4px 10px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .badge-unread { background: #fee2e2; color: #dc2626; }
    .badge-read { background: #dcfce7; color: #16a34a; }

    .btn-action {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }

    .btn-mark-read {
        background: #fff;
        border: 1px solid var(--brand-gold);
        color: var(--brand-gold);
    }

    .btn-mark-read:hover {
        background: var(--brand-gold);
        color: #fff;
    }

    .btn-delete {
        background: #fff;
        border: 1px solid #dc2626;
        color: #dc2626;
    }

    .btn-delete:hover {
        background: #dc2626;
        color: #fff;
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
    }

    .empty-icon {
        font-size: 4rem;
        color: var(--border-color);
        margin-bottom: 20px;
    }
</style>

<div class="main-content queries-page">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body px-4 pb-5">
        <div class="d-flex justify-content-between align-items-center py-4 mb-3">
            <div>
                <h1 class="fw-bold h2 mb-1" style="color:var(--brand-maroon);">Customer Queries</h1>
                <p class="text-muted small mb-0">Manage and respond to customer messages</p>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-danger rounded-pill px-3 py-2">
                    <?php echo $unreadCount; ?> Unread
                </span>
            </div>
        </div>

        <div class="queries-list" id="queriesList">
            <?php if (empty($messages)): ?>
                <div class="empty-state">
                    <i class="bi bi-chat-dots empty-icon"></i>
                    <h3 class="text-muted">No messages yet</h3>
                    <p class="text-muted small">When customers contact you, their messages will appear here.</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): 
                    $isUnread = $msg['status'] === 'unread';
                ?>
                <div class="query-card <?php echo $isUnread ? 'unread' : ''; ?>" id="query-<?php echo $msg['id']; ?>">
                    <div class="query-header">
                        <div>
                            <div class="customer-name"><?php echo htmlspecialchars($msg['full_name']); ?></div>
                            <div class="query-meta">
                                <span><i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($msg['email']); ?></span>
                                <?php if ($msg['phone']): ?>
                                    <span><i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($msg['phone']); ?></span>
                                <?php endif; ?>
                                <span><i class="bi bi-calendar3 me-1"></i><?php echo date('d M Y, h:i A', strtotime($msg['created_at'])); ?></span>
                            </div>
                        </div>
                        <div>
                            <span class="status-badge <?php echo $isUnread ? 'badge-unread' : 'badge-read'; ?>">
                                <?php echo ucfirst($msg['status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="query-body">
                        <div class="query-text"><?php echo htmlspecialchars($msg['message']); ?></div>
                    </div>
                    <div class="query-footer">
                        <?php if ($isUnread): ?>
                        <button class="btn btn-action btn-mark-read" onclick="markAsRead(<?php echo $msg['id']; ?>)">
                            <i class="bi bi-check2-circle"></i> Mark as Read
                        </button>
                        <?php endif; ?>
                        <a href="mailto:<?php echo $msg['email']; ?>" class="btn btn-action btn-accent" style="background:var(--brand-maroon); color:#fff; border:none;">
                            <i class="bi bi-reply"></i> Reply
                        </a>
                        <button class="btn btn-action btn-delete" onclick="deleteQuery(<?php echo $msg['id']; ?>)">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
async function markAsRead(id) {
    if (!confirm('Mark this message as read?')) return;
    
    try {
        const fd = new FormData();
        fd.append('action', 'mark_read');
        fd.append('id', id);
        
        const res = await fetch('queries.php', { method: 'POST', body: fd });
        const data = await res.json();
        
        if (data.success) {
            const card = document.getElementById(`query-${id}`);
            card.classList.remove('unread');
            const badge = card.querySelector('.status-badge');
            badge.classList.remove('badge-unread');
            badge.classList.add('badge-read');
            badge.textContent = 'Read';
            card.querySelector('.btn-mark-read').remove();
        }
    } catch (err) {
        alert('Error updating status');
    }
}

async function deleteQuery(id) {
    if (!confirm('Are you sure you want to delete this message?')) return;
    
    try {
        const fd = new FormData();
        fd.append('action', 'delete');
        fd.append('id', id);
        
        const res = await fetch('queries.php', { method: 'POST', body: fd });
        const data = await res.json();
        
        if (data.success) {
            const card = document.getElementById(`query-${id}`);
            card.style.opacity = '0';
            card.style.transform = 'translateX(20px)';
            setTimeout(() => card.remove(), 300);
        }
    } catch (err) {
        alert('Error deleting message');
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
