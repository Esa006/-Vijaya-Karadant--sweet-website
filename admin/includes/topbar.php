<?php
/**
 * Sweets Website
 * =============================================================
 * File: topbar.php
 * Description: Admin top navigation bar with search and profile
 * =============================================================
 */

require_once ROOT_PATH . '/services/AdminNotificationService.php';
$notifService = new AdminNotificationService();
$topbarNotifs = $notifService->getTopbarData();
$unreadCount = $topbarNotifs['count'];
$recentNotifs = $topbarNotifs['recent'];
?>
<header class="admin-topbar border-bottom bg-white" style="height: 80px;">
    <div class="topbar-left d-flex align-items-center gap-3">
        <button class="sidebar-toggle btn p-0 text-dark" id="sidebarToggle">
            <i class="bi bi-list fs-3"></i>
        </button>
        <div class="topbar-search d-none d-md-block" style="flex: 1; max-width: 320px;">
            <div class="input-group align-items-center" style="background-color: #FCF8F5; border: 1px solid #ECCCBC; border-radius: 6px; width: 100%; height: 42px;">
                <span class="input-group-text border-0 bg-transparent ps-3 pe-2" style="color: #AE4B3A;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                    </svg>
                </span>
                <input type="text" id="globalAdminSearch" class="form-control border-0 bg-transparent shadow-none px-0" placeholder="Search here..." style="font-size: 14px; color: #8C8C8C; font-weight: 500;">
            </div>
        </div>
    </div>

    <div class="topbar-right d-flex align-items-center justify-content-end gap-2 gap-md-3">
        <!-- Bell Icon with dynamic notification dropdown -->
        <div class="topbar-item dropdown" id="notifDropdown">
            <a href="#" class="d-flex align-items-center justify-content-center position-relative text-decoration-none"
               data-bs-toggle="dropdown" id="notifBell"
               style="width: 40px; height: 40px; border: 1px solid #ECCCBC; border-radius: 6px; color: #AE4B3A;">
                <i class="bi bi-bell"></i>
                <?php if ($unreadCount > 0): ?>
                <span class="notif-live-badge" id="notifBadge"><?= $unreadCount ?></span>
                <?php else: ?>
                <span class="notif-live-badge d-none" id="notifBadge">0</span>
                <?php endif; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3 rounded-4 p-3" style="min-width: 300px;">
                <li class="dropdown-header px-0 d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold" style="color:#1a1a1a;font-size:.9rem;">Notifications</span>
                    <div class="d-flex align-items-center gap-2">
                        <?php if ($unreadCount > 0): ?>
                        <button type="button" class="btn p-0 border-0 bg-transparent" id="markAllReadBtn"
                                style="font-size:.72rem;color:#AE4B3A;font-weight:600;white-space:nowrap;">
                            Mark all read
                        </button>
                        <?php endif; ?>
                        <span class="badge bg-danger rounded-pill" id="notifCountBadge"
                              style="<?= $unreadCount > 0 ? '' : 'display:none' ?>"><?= $unreadCount ?></span>
                    </div>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <?php if (empty($recentNotifs)): ?>
                    <li class="py-3 text-center text-muted small">No new notifications</li>
                <?php else: ?>
                    <?php foreach ($recentNotifs as $rn): ?>
                        <li>
                            <a class="dropdown-item rounded-3 notif-item-link d-flex align-items-start gap-2 py-2"
                               href="notifications.php"
                               data-id="<?= (int)$rn['id'] ?>"
                               data-read="<?= $rn['is_read'] ? '1' : '0' ?>"
                               style="max-width:270px; white-space:normal;">
                                <span class="notif-dot-indicator <?= $rn['is_read'] ? 'd-none' : '' ?>"
                                      style="width:7px;height:7px;background:#dc3545;border-radius:50%;flex-shrink:0;margin-top:6px;"></span>
                                <span class="text-truncate" style="max-width:230px;">
                                    <?= htmlspecialchars($rn['title']) ?>
                                </span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
                <li><hr class="dropdown-divider my-1"></li>
                <li><a class="dropdown-item text-center fw-medium" href="notifications.php"
                       style="color:#AE4B3A;font-size:.85rem;">View all</a></li>
            </ul>
        </div>


        <!-- Gear Icon -->
        <div class="topbar-item">
            <a href="settings.php" class="d-flex align-items-center justify-content-center text-decoration-none" style="width: 40px; height: 40px; border: 1px solid #ECCCBC; border-radius: 6px; color: #AE4B3A;">
                <i class="bi bi-gear"></i>
            </a>
        </div>

        <!-- Profile Section -->
        <div class="topbar-profile dropdown d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <div class="profile-avatar">
                   <?php
                   $topbarAvatarStr = $_SESSION['user_avatar'] ?? '';
                   if (!empty($topbarAvatarStr)) {
                       $topbarImgSrc = (strpos($topbarAvatarStr, 'http') === 0) ? $topbarAvatarStr : BASE_URL . $topbarAvatarStr;
                   } else {
                       $topbarImgSrc = 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user_name'] ?? 'Admin') . '&background=AE4B3A&color=fff';
                   }
                   ?>
                   <img src="<?php echo htmlspecialchars($topbarImgSrc); ?>" alt="<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>" style="width: 42px; height: 42px; border-radius: 6px; object-fit: cover;">
                </div>
                <div class="profile-text text-start d-none d-lg-flex flex-column justify-content-center">
                    <span class="profile-name fw-bolder text-dark" style="font-size: 13px; line-height: 1.2;"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span>
                    <span class="profile-role text-muted" style="font-size: 11px;"><?php echo ucfirst($_SESSION['user_role'] ?? 'Admin'); ?></span>
                </div>
            </div>
            
            <button class="btn p-0 d-flex align-items-center justify-content-center rounded-circle" data-bs-toggle="dropdown" style="width: 28px; height: 28px; border: 1px solid #ECCCBC; color: #AE4B3A;">
                <i class="bi bi-chevron-down fs-7" style="font-size: 11px; stroke-width: 1px;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3 rounded-4 p-2">
                <li><a class="dropdown-item rounded-3" href="profile.php"><i class="bi bi-person me-2"></i> My Profile</a></li>
                <li><a class="dropdown-item rounded-3" href="settings.php"><i class="bi bi-gear me-2"></i> Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item rounded-3 text-danger" href="<?php echo BASE_URL; ?>api/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</header>

<style>
/* ── Notification live badge ──────────────────────────────── */
.notif-live-badge {
    position: absolute;
    top: 6px; right: 6px;
    background: #dc3545;
    color: #fff;
    font-size: .6rem;
    font-weight: 800;
    min-width: 16px; height: 16px;
    border-radius: 99px;
    display: flex; align-items: center; justify-content: center;
    padding: 0 3px;
    line-height: 1;
    pointer-events: none;
    transition: opacity .2s;
}
.notif-item-link { transition: background .15s; }
.notif-item-link:hover { background: #fef6f0 !important; }
.notif-item-link.marking { opacity: .5; pointer-events: none; }
</style>

<script>
(function () {
    /* ── Helpers ───────────────────────────────────────────── */
    var MARK_URL = 'api/mark-notification-read.php';

    function postJson(url, data) {
        return fetch(url, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(data),
            credentials: 'same-origin'
        }).then(function (r) { return r.json(); });
    }

    /* ── Badge DOM helpers ─────────────────────────────────── */
    var badge      = document.getElementById('notifBadge');
    var countBadge = document.getElementById('notifCountBadge');
    var unread     = parseInt((badge && badge.textContent) || '0', 10);

    function updateBadge(newCount) {
        unread = Math.max(0, newCount);
        if (badge) {
            badge.textContent = unread;
            badge.classList.toggle('d-none', unread === 0);
        }
        if (countBadge) {
            countBadge.textContent = unread;
            countBadge.style.display = unread > 0 ? '' : 'none';
        }
    }

    /* ── Per-item click → mark as read ────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {

        /* Notification item clicks */
        document.querySelectorAll('.notif-item-link').forEach(function (link) {
            link.addEventListener('click', function (e) {
                var id     = parseInt(this.dataset.id, 10);
                var isRead = this.dataset.read === '1';
                var dot    = this.querySelector('.notif-dot-indicator');

                if (!isRead && id > 0) {
                    e.preventDefault();                       // stop nav temporarily
                    this.classList.add('marking');
                    var href = this.href;
                    var self = this;

                    postJson(MARK_URL, { id: id })
                        .then(function (res) {
                            if (res.success) {
                                self.dataset.read = '1';
                                if (dot) dot.classList.add('d-none');
                                updateBadge(unread - 1);
                            }
                        })
                        .catch(function () { /* silently ignore */ })
                        .finally(function () {
                            self.classList.remove('marking');
                            window.location.href = href;    // navigate after AJAX
                        });
                }
                /* If already read, let the default href navigate normally */
            });
        });

        /* "Mark all read" button */
        var markAllBtn = document.getElementById('markAllReadBtn');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', function (e) {
                e.stopPropagation();   // keep dropdown open
                markAllBtn.textContent = 'Marking…';
                markAllBtn.disabled = true;

                postJson(MARK_URL, { all: true })
                    .then(function (res) {
                        if (res.success) {
                            /* Remove all dots */
                            document.querySelectorAll('.notif-dot-indicator').forEach(function (d) {
                                d.classList.add('d-none');
                            });
                            document.querySelectorAll('.notif-item-link').forEach(function (l) {
                                l.dataset.read = '1';
                            });
                            updateBadge(0);
                            markAllBtn.style.display = 'none';
                        }
                    })
                    .catch(function () {})
                    .finally(function () {
                        if (markAllBtn.style.display !== 'none') {
                            markAllBtn.textContent = 'Mark all read';
                            markAllBtn.disabled = false;
                        }
                    });
            });
        }

        /* ── Global Topbar Search ──────────────────────────── */
        var globalSearchInput = document.getElementById('globalAdminSearch');
        if (globalSearchInput) {
            globalSearchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    var term = this.value.trim();
                    if (!term) return;
                    var path = window.location.pathname;
                    if (path.includes('products.php')) {
                        window.location.href = 'products.php?search=' + encodeURIComponent(term);
                    } else if (path.includes('customers.php')) {
                        window.location.href = 'customers.php?search=' + encodeURIComponent(term);
                    } else if (path.includes('audit-logs.php')) {
                        window.location.href = 'audit-logs.php?search=' + encodeURIComponent(term);
                    } else {
                        window.location.href = 'orders.php?search=' + encodeURIComponent(term);
                    }
                }
            });
        }

    });
})();
</script>

