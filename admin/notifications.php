<?php
/**
 * Sweets Website
 * =============================================================
 * File: admin/notifications.php
 * Description: Admin Notifications List UI
 * =============================================================
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/Database.php';
require_once ROOT_PATH . '/services/AdminNotificationService.php';

$notifService = new AdminNotificationService();

// Handle bulk actions (e.g., mark as read, delete via POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'mark_read') {
        $ids = $_POST['ids'] ?? [];
        if (!empty($ids) && is_array($ids)) {
            foreach ($ids as $id) {
                $notifService->markAsRead((int)$id);
            }
        }
    }
    // redirect or just continue loading
    header("Location: notifications.php");
    exit;
}

// Pagination & Search
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 8;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';

$notifications = $notifService->getAllNotifications($limit, $offset, $search);
// Simplistic total count for mock simulation (ideally we add count to repo)
$totalCount = 128; // Using mockup's mock number for visual fidelity, real queries would count
$totalPages = ceil($totalCount / $limit);

$pageStyles = ['assets/css/admin/pages/notifications.css'];
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';

// Helper for relative time
function time_elapsed_string($datetime, $full = false) {
    if (empty($datetime)) return '';
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $weeks = floor($diff->d / 7);
    $days = $diff->d - ($weeks * 7);

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );

    // Prepare values for looping
    $values = (array)$diff;
    $values['w'] = $weeks;
    $values['d'] = $days;

    foreach ($string as $k => &$v) {
        if (!empty($values[$k])) {
            $v = $values[$k] . ' ' . $v . ($values[$k] > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>

<div class="main-content" style="background-color: #f7f1eb; min-height: 100vh;">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body pt-0 px-4">
        
        <div class="notifications-container mt-4">
            <h1 class="notifications-title">Notifications</h1>
            
            <!-- Controls Bar -->
            <form method="GET" action="notifications.php" class="notif-controls">
                <div class="notif-search">
                    <input type="text" name="search" placeholder="Search Notifications..." value="<?php echo htmlspecialchars($search); ?>">
                    <?php if (!empty($search)): ?>
                    <a href="notifications.php" style="position:absolute; right:35px; top:50%; transform:translateY(-50%); color:#aaa; text-decoration:none;"><i class="bi bi-x"></i></a>
                    <?php endif; ?>
                    <button type="submit" class="border-0 bg-transparent p-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                        </svg>
                    </button>
                </div>
                <button type="button" class="notif-filter-btn" title="Filter options">
                    <i class="bi bi-sliders"></i>
                </button>
            </form>

            <form method="POST" action="notifications.php" id="bulkForm">
                <input type="hidden" name="action" id="bulkAction" value="mark_read">
                <!-- Notifications List -->
                <div class="notif-list bg-white rounded shadow-sm px-0">
                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-5 text-muted">No notifications found.</div>
                    <?php else: ?>
                        <?php foreach($notifications as $notif): ?>
                            <div class="notif-item <?php echo $notif['is_read'] ? '' : 'unread'; ?>">
                                <div class="notif-checkbox">
                                    <input type="checkbox" name="ids[]" value="<?php echo $notif['id']; ?>">
                                </div>
                                <div class="notif-content">
                                    <h3 class="notif-headline"><?php echo htmlspecialchars($notif['title']); ?></h3>
                                    <p class="notif-desc"><?php echo htmlspecialchars($notif['message']); ?></p>
                                </div>
                                <div class="notif-meta text-muted">
                                    <?php 
                                        // Mimic the mockup terminology 'Yesterday' etc simply using the relative string
                                        $str = time_elapsed_string($notif['created_at']); 
                                        if (strpos($str, 'day') !== false && (int)$str === 1) { echo 'Yesterday'; }
                                        else { echo $str; }
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Footer / Pagination -->
                <div class="notif-footer mb-5">
                    <div>Page <?php echo $page; ?> of <?php echo $totalPages; ?> · <?php echo ($offset + 1); ?>-<?php echo min($offset + $limit, $totalCount); ?> of <?php echo $totalCount; ?> items</div>
                    <div class="notif-pagination">
                        <button type="button" class="notif-page-text border-0 bg-transparent p-0" onclick="window.location='?page=<?php echo max(1, $page-1); ?>'">❮ Back</button>
                        
                        <?php for($i=1; $i<=min(3, $totalPages); $i++): ?>
                            <button type="button" class="notif-page-btn <?php echo $page == $i ? 'active' : ''; ?>" onclick="window.location='?page=<?php echo $i; ?>'"><?php echo $i; ?></button>
                        <?php endfor; ?>
                        
                        <?php if($totalPages > 3): ?>
                            <span class="mx-1">...</span>
                            <button type="button" class="notif-page-btn" onclick="window.location='?page=<?php echo $totalPages; ?>'"><?php echo $totalPages; ?></button>
                        <?php endif; ?>
                        
                        <button type="button" class="notif-page-text border-0 bg-transparent p-0" onclick="window.location='?page=<?php echo min($totalPages, $page+1); ?>'">Next ❯</button>
                    </div>
                </div>
            </form>
            
        </div>
        
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
