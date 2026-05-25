<?php
/**
 * Sweets Website
 * =============================================================
 * File: profile.php
 * Description: High-fidelity Profile Management Dashboard
 * =============================================================
 */

$pageStyles = [
    'assets/css/admin/products.css',
    'assets/css/admin/pages/profile.css'
];
$pageScripts = ['assets/js/admin/pages/profile.js'];

require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';
require_once dirname(__DIR__) . '/config/Database.php';

// Fetch actual user data from DB
$pdo = Database::getInstance();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch() ?: [];

$userName     = $user['full_name'] ?? $_SESSION['user_name'] ?? 'Admin';
$userEmail    = $user['email']     ?? $_SESSION['user_email'] ?? '';
$userPhone    = $user['phone']     ?? '';
$userLanguage = $user['language']  ?? 'English (US)';
$userTimezone = $user['timezone']  ?? '(UTC+05:30) Asia/Kolkata';
$userRole     = ucfirst($user['role'] ?? $_SESSION['user_role'] ?? 'Admin');

// Resolve avatar URL
if (!empty($user['avatar'])) {
    // Stored path may be relative (assets/images/avatars/...) or a full URL
    $userAvatar = (strpos($user['avatar'], 'http') === 0)
        ? $user['avatar']
        : BASE_URL . $user['avatar'];
} else {
    $userAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=AE4B3A&color=fff';
}

// Password last-changed label (use updated_at as proxy)
$passwordLabel = 'Never updated';
if (!empty($user['updated_at'])) {
    $diff = time() - strtotime($user['updated_at']);
    if ($diff < 60)           $passwordLabel = 'Just now';
    elseif ($diff < 3600)     $passwordLabel = floor($diff / 60) . ' minutes ago';
    elseif ($diff < 86400)    $passwordLabel = floor($diff / 3600) . ' hours ago';
    elseif ($diff < 2592000)  $passwordLabel = floor($diff / 86400) . ' days ago';
    elseif ($diff < 31536000) $passwordLabel = floor($diff / 2592000) . ' months ago';
    else                      $passwordLabel = floor($diff / 31536000) . ' years ago';
}

// Recent login activity from DB
$loginStmt = $pdo->prepare("
    SELECT device_label, location, device_type, is_current, created_at
    FROM admin_login_activity
    WHERE admin_id = :id AND status = 'success'
    ORDER BY created_at DESC
    LIMIT 5
");
$loginStmt->execute([':id' => $_SESSION['user_id']]);
$loginActivity = $loginStmt->fetchAll() ?: [];
?>

<div class="main-content profile-content-body">
    <?php require_once 'includes/topbar.php'; ?>

    <!-- Header Area -->
    <div class="profile-header-maroon p-4 mb-4">
        <div class="container-fluid profile-container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <h1 class="profile-title">Profile</h1>
                <div class="header-actions d-none d-md-flex gap-3">
                    <button class="btn btn-outline-cancel px-4" id="headerCancelBtn">Cancel</button>
                    <button class="btn btn-save-maroon px-4" id="headerSaveBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid profile-container px-4">
        
        <!-- Summary Hero Card -->
        <div class="profile-hero-card mb-5">
            <div class="hero-avatar-wrap">
                <img src="<?php echo $userAvatar; ?>" alt="<?php echo $userName; ?>">
            </div>
            <div class="hero-info-text flex-grow-1">
                <h3 id="displayUserName"><?php echo $userName; ?></h3>
                <p><i class="bi bi-envelope"></i> <?php echo $userEmail; ?></p>
            </div>
            <button class="btn-edit-picture">
                <i class="bi bi-pencil-square"></i> Edit Picture
            </button>
        </div>

        <form id="profileForm" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="row g-4">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="profile-section-card">
                        <span class="section-label-maroon">Basic Information</span>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label-maroon">Full Name</label>
                                <input type="text" name="full_name" class="form-control-maroon w-100" value="<?php echo $userName; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-maroon">Email Address</label>
                                <input type="email" name="email" class="form-control-maroon w-100" value="<?php echo $userEmail; ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label-maroon">Phone Number</label>
                                <input type="text" name="phone" class="form-control-maroon w-100" placeholder="+91 98765 43210" value="<?php echo htmlspecialchars($userPhone); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Profile Picture Upload -->
                    <div class="profile-section-card">
                        <span class="section-label-maroon">Profile Picture</span>
                        <div class="upload-dropzone" id="avatarDropzone">
                            <div class="dropzone-icon-wrap">
                                <i class="bi bi-cloud-arrow-up"></i>
                            </div>
                            <div class="dropzone-text">
                                <h6>Click to upload or drag and drop</h6>
                                <p>Recommended size: 1920x800px (Max 2MB)</p>
                            </div>
                            <input type="file" class="d-none" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif">
                        </div>
                    </div>

                    <!-- Account Settings -->
                    <div class="profile-section-card">
                        <span class="section-label-maroon">Account Settings</span>
                        
                        <div class="setting-action-row">
                            <div class="setting-text">
                                <h6>Password</h6>
                                <p>Last changed <?php echo $passwordLabel; ?></p>
                            </div>
                            <button type="button" class="btn-setting-action">Change Password</button>
                        </div>

                        <div class="setting-action-row">
                            <div class="setting-text">
                                <h6>Two-Factor Authentication</h6>
                                <p>Add an extra layer of security to your account</p>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" checked id="toggle2FA">
                            </div>
                        </div>

                        <div class="mt-4">
                            <h6 class="fw-bold text-dark mb-3">Recent Login Activity</h6>
                            <?php if (empty($loginActivity)): ?>
                                <p class="text-muted small">No recent login activity found.</p>
                            <?php else: ?>
                                <?php foreach ($loginActivity as $session): ?>
                                    <?php
                                        $deviceIcon = match($session['device_type'] ?? 'desktop') {
                                            'mobile'  => 'bi-phone',
                                            'tablet'  => 'bi-tablet',
                                            default   => 'bi-laptop'
                                        };
                                        $isCurrent = (bool)$session['is_current'];
                                        $timeAgo   = '';
                                        $diffSec   = time() - strtotime($session['created_at']);
                                        if ($diffSec < 3600)        $timeAgo = floor($diffSec/60)   . 'm ago';
                                        elseif ($diffSec < 86400)   $timeAgo = floor($diffSec/3600) . 'h ago';
                                        else                        $timeAgo = floor($diffSec/86400) . 'd ago';
                                    ?>
                                    <div class="active-session-box mb-2">
                                        <div class="session-core">
                                            <div class="device-avatar">
                                                <i class="bi <?php echo $deviceIcon; ?>"></i>
                                            </div>
                                            <div class="session-info">
                                                <h6><?php echo htmlspecialchars($session['device_label'] ?: 'Unknown Device'); ?></h6>
                                                <p><?php echo htmlspecialchars($session['location'] ?: 'Unknown Location'); ?> • <?php echo $timeAgo; ?></p>
                                            </div>
                                        </div>
                                        <?php if ($isCurrent): ?>
                                            <span class="badge-active-now">Active Now</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary" style="font-size:0.7rem;">Past</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Preferences) -->
                <div class="col-lg-4">
                    <div class="profile-section-card position-sticky" style="top: 100px;">
                        <span class="section-label-maroon">Preferences</span>
                        
                        <div class="mb-4">
                            <label class="form-label-maroon">Language</label>
                            <select name="language" class="form-select form-control-maroon h-auto">
                                <option value="English (US)" <?php echo $userLanguage === 'English (US)' ? 'selected' : ''; ?>>English (US)</option>
                                <option value="Kannada" <?php echo $userLanguage === 'Kannada' ? 'selected' : ''; ?>>Kannada</option>
                                <option value="Hindi" <?php echo $userLanguage === 'Hindi' ? 'selected' : ''; ?>>Hindi</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label-maroon">Timezone</label>
                            <select name="timezone" class="form-select form-control-maroon h-auto">
                                <option value="(UTC+05:30) Asia/Kolkata" <?php echo $userTimezone === '(UTC+05:30) Asia/Kolkata' ? 'selected' : ''; ?>>(UTC+05:30) Asia/Kolkata</option>
                                <option value="(UTC+00:00) London" <?php echo $userTimezone === '(UTC+00:00) London' ? 'selected' : ''; ?>>(UTC+00:00) London</option>
                                <option value="(UTC-05:00) New York" <?php echo $userTimezone === '(UTC-05:00) New York' ? 'selected' : ''; ?>>(UTC-05:00) New York</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Sticky Footer -->
    <div class="profile-sticky-footer" id="profileStickyFooter">
        <div class="unsaved-badge">
            <div class="status-dot-pulse"></div>
            <span class="unsaved-text">Unsaved changes</span>
        </div>
        <div class="footer-actions d-flex gap-3">
            <button class="btn btn-light px-4 border" id="cancelProfileBtn">Cancel</button>
            <button class="btn btn-save-maroon px-4" id="saveProfileBtn">Save Changes</button>
        </div>
    </div>
</div>

<!-- TOAST CONTAINER -->
<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>

<?php require_once 'includes/footer.php'; ?>
