<?php
/**
 * Sweets Website
 * =============================================================
 * File: settings.php
 * Description: High-fidelity Settings Dashboard (Sectioned).
 * =============================================================
 */

$pageStyles = [
    'assets/css/admin/products.css', 
    'assets/css/admin/pages/settings.css'
];
$pageScripts = ['assets/js/admin/pages/settings.js'];

require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php'; 
require_once SERVICES_PATH . '/SettingService.php';

$settingService = new SettingService();
$settings = $settingService->getAllSettings();
?>

<div class="main-content settings-content-body">
    <?php require_once 'includes/topbar.php'; ?>

    <!-- Header Area -->
    <div class="settings-header-maroon p-4 px-md-5 mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <h1 class="settings-title">Settings</h1>
            <div class="header-actions">
                <button class="btn btn-outline-cancel px-4 btn-cancel-settings">Cancel</button>
                <button class="btn btn-save-maroon px-4 btn-save-settings">Save Changes</button>
            </div>
        </div>
    </div>

    <div class="settings-container-fluid px-4 px-md-5">
        <div class="settings-layout-main">
            
            <!-- Sidebar Navigation -->
            <aside class="settings-nav-pane">
                <div class="nav-branding mb-4">
                    <h5 class="nav-title">Settings navigation</h5>
                    <p class="nav-subtitle">Switch between store configuration areas. Store Info is selected by default.</p>
                </div>

                <div class="settings-nav-list" id="settingsNavList">
                    <button class="nav-item-box active" data-section="store-info">
                        <div class="icon-wrap"><i class="bi bi-shop"></i></div>
                        <div class="item-text">
                            <h6>Store Info</h6>
                            <p>Order alerts, email digests and push communication preferences.</p>
                        </div>
                        <i class="bi bi-chevron-right ms-auto"></i>
                    </button>

                    <button class="nav-item-box" data-section="notifications">
                        <div class="icon-wrap"><i class="bi bi-bell"></i></div>
                        <div class="item-text">
                            <h6>Notifications</h6>
                            <p>Configure order alerts, email signals and push communication preferences.</p>
                        </div>
                        <i class="bi bi-chevron-right ms-auto"></i>
                    </button>

                    <button class="nav-item-box" data-section="security">
                        <div class="icon-wrap"><i class="bi bi-shield-lock"></i></div>
                        <div class="item-text">
                            <h6>Security</h6>
                            <p>Passwords, login review, authentication, alerts and session controls.</p>
                        </div>
                        <i class="bi bi-chevron-right ms-auto"></i>
                    </button>

                    <button class="nav-item-box" data-section="payments">
                        <div class="icon-wrap"><i class="bi bi-credit-card"></i></div>
                        <div class="item-text">
                            <h6>Payments</h6>
                            <p>UPI, cards, CODs and settlement preferences for checkout.</p>
                        </div>
                        <i class="bi bi-chevron-right ms-auto"></i>
                    </button>

                    <button class="nav-item-box" data-section="shipping">
                        <div class="icon-wrap"><i class="bi bi-truck"></i></div>
                        <div class="item-text">
                            <h6>Shipping</h6>
                            <p>Delivery charges, service areas and free delivery thresholds.</p>
                        </div>
                        <i class="bi bi-chevron-right ms-auto"></i>
                    </button>

                    <button class="nav-item-box" data-section="appearance">
                        <div class="icon-wrap"><i class="bi bi-palette"></i></div>
                        <div class="item-text">
                            <h6>Appearance</h6>
                            <p>Theme colors, panel density and admin interface preferences.</p>
                        </div>
                        <i class="bi bi-chevron-right ms-auto"></i>
                    </button>
                </div>
            </aside>

            <!-- Section Content -->
            <div class="settings-editor-pane">
                
                <!-- STORE INFO SECTION -->
                <section id="store-info" class="settings-section active">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <div class="selection-indicator">
                                <i class="bi bi-shop"></i> Selected section: Store Info
                            </div>
                            <h2 class="section-main-title">Store Info</h2>
                            <p class="section-description">Keep your storefront identity consistent across the website, packaging, invoices, and customer communications. These values are pre-filled and ready to update.</p>
                        </div>
                        <div class="unsaved-status-chip dashboard-status-indicator" style="display: none;">
                            <span class="status-dot"></span>
                            <div class="status-text">
                                <div class="fw-bold">Unsaved changes</div>
                                <div class="small text-muted"><span class="changed-fields-count">0</span> fields updated recently</div>
                            </div>
                        </div>
                    </div>
                    <!-- Reuse existing Store Info inputs but with better containers -->
                    <div class="editor-content-wrap">
                        <!-- Brand Identity -->
                        <div class="settings-card-layout mb-4">
                            <div class="field-group-header d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="fw-bold mb-0">Brand Identity</h5>
                                    <p class="text-muted small mb-0">Update the logo and storefront presentation details that customers see across the experience.</p>
                                </div>
                                <span class="badge-pill-maroon">Primary section</span>
                            </div>

                            <div class="store-mark-card rounded border p-4 mb-4">
                                <div class="store-mark-preview" id="logoPreview">
                                    <?php if (!empty($settings['store_logo'])): ?>
                                        <img src="<?php echo BASE_URL . $settings['store_logo']; ?>" alt="Logo">
                                    <?php else: ?>
                                        <i class="bi bi-shop"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Current storefront mark</h6>
                                    <p class="text-muted small mb-3">Used in the header, invoices, order emails and premium sweet box labels. Recommended ratio: 1:1, transparent background.</p>
                                    <div class="d-flex align-items-center">
                                        <label for="store_logo_file" class="btn btn-upload-maroon cursor-pointer m-0">
                                            Upload New Logo
                                        </label>
                                        <input type="file" id="store_logo_file" name="store_logo_file" hidden accept="image/*">
                                        <button class="btn-remove-link" type="button" onclick="document.getElementById('store_logo_file').value=''; document.getElementById('logoPreview').innerHTML='<i class=\'bi bi-shop\'></i>';">Remove</button>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label-maroon mb-0">Store Name</label>
                                        <span class="label-hint">Required</span>
                                    </div>
                                    <input type="text" class="form-control-maroon" id="store_name" value="<?php echo htmlspecialchars($settings['store_name'] ?? 'Vijaya Karadant'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label-maroon mb-0">Tagline</label>
                                        <span class="label-hint">Visible on homepage</span>
                                    </div>
                                    <input type="text" class="form-control-maroon" id="store_tagline" value="<?php echo htmlspecialchars($settings['store_tagline'] ?? 'Authentic Karnataka sweets, crafted with heart.'); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Contact Details -->
                        <div class="settings-card-layout mb-4">
                            <div class="field-group-header d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="fw-bold mb-0">Contact Details</h5>
                                    <p class="text-muted small mb-0">Store address and support information shown on checkout, order updates and customer service pages.</p>
                                </div>
                                <span class="badge-pill-maroon">Customer-facing</span>
                            </div>

                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label-maroon mb-2">Email Address</label>
                                    <input type="email" class="form-control-maroon" id="store_email" value="<?php echo htmlspecialchars($settings['store_email'] ?? 'hello@vijayakaradant.com'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-maroon mb-2">Phone Number</label>
                                    <input type="tel" class="form-control-maroon" id="store_phone" value="<?php echo htmlspecialchars($settings['store_phone'] ?? '+91 98860 24567'); ?>">
                                </div>
                            </div>

                            <div class="form-group-maroon">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label-maroon">Store Address</label>
                                    <span class="label-hint">Shown on checkout</span>
                                </div>
                                <textarea class="form-control-maroon h-auto py-3" id="store_address" rows="3"><?php echo htmlspecialchars($settings['store_address'] ?? '145, Market Road, Near Gandhi Chowk, Gokak, Belagavi, Karnataka 591307'); ?></textarea>
                            </div>
                        </div>

                        <!-- Business Info -->
                        <div class="settings-card-layout mb-4">
                            <div class="field-group-header d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="fw-bold mb-0">Business Info</h5>
                                    <p class="text-muted small mb-0">Tax and business information used in billing, compliance and B2B order processing.</p>
                                </div>
                                <span class="badge-pill-maroon">Optional</span>
                            </div>

                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label-maroon mb-2">GST Number</label>
                                    <input type="text" class="form-control-maroon" id="store_gst" value="<?php echo htmlspecialchars($settings['store_gst'] ?? '29AAACV2288J1Z2'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-maroon mb-2">Business Type</label>
                                    <input type="text" class="form-control-maroon" id="store_business_type" value="<?php echo htmlspecialchars($settings['store_business_type'] ?? 'Private Limited Company'); ?>">
                                </div>
                            </div>

                            <div class="form-group-maroon mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label-maroon">Store Address</label>
                                    <span class="label-hint">Shown on Invoices</span>
                                </div>
                                <textarea class="form-control-maroon h-auto py-3" id="store_invoice_address" rows="2"><?php echo htmlspecialchars($settings['store_invoice_address'] ?? '145, Market Road, Near Gandhi Chowk, Gokak, Belagavi, Karnataka 591107'); ?></textarea>
                            </div>

                            <div class="sync-status-box mb-0">
                                <h6 class="fw-bold"><i class="bi bi-check-circle-fill text-success me-2"></i> Brand profile synced successfully</h6>
                                <p class="mb-2">Your storefront metadata is consistent across the website, GST invoice template and order confirmation mailer.</p>
                                <a href="#" class="preview-link">Preview storefront</a>
                            </div>
                        </div>

                        <!-- Final confirmation banner -->
                        <div class="save-confirmation-ready">
                            <div class="d-flex align-items-center gap-3">
                                <div class="check-circle-icon"><i class="bi bi-check-lg"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1">Save confirmation ready</h6>
                                    <p class="text-muted small mb-0">Changes will update the store profile, support details and branding assets across the admin and website preview.</p>
                                </div>
                            </div>
                            <span class="badge-live-sync">Live sync enabled</span>
                        </div>
                    </div>
                </section>

                <!-- SECURITY SECTION (AS IN IMAGE) -->
                <section id="security" class="settings-section">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <div class="selection-indicator">
                                <i class="bi bi-braces-asterisk"></i> Selected section: Security
                            </div>
                            <h2 class="section-main-title">Security</h2>
                            <p class="section-description">Manage your account security, passwords, authentication settings, and recent device activity to keep your admin panel protected.</p>
                        </div>
                        <div class="unsaved-status-chip dashboard-status-indicator" style="display: none;">
                            <span class="status-dot"></span>
                            <div class="status-text">
                                <div class="fw-bold">Unsaved changes</div>
                                <div class="small text-muted"><span class="changed-fields-count">0</span> fields updated recently</div>
                            </div>
                        </div>
                    </div>

                    <!-- Password Settings -->
                    <div class="settings-card-layout mb-5">
                        <div class="card-head d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">Password Settings</h5>
                            <span class="badge-pill-maroon">Customer-facing</span>
                        </div>
                        <div class="card-body-maroon mt-4">
                            <p class="text-muted small mb-4">Update the primary admin password regularly and make sure it meets your store security policy before festive traffic increases.</p>
                            
                            <div class="form-group-maroon mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label-maroon">Current Password</label>
                                    <span class="label-hint">Required to change password</span>
                                </div>
                                <input type="password" class="form-control-maroon" placeholder="Enter current password" id="currentPasswordInput" name="currentPasswordInput">
                            </div>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label-maroon mb-2">New Password</label>
                                    <input type="password" class="form-control-maroon" placeholder="Saffron@2025" id="newPasswordInput" name="newPasswordInput">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-maroon mb-2">Confirm New Password</label>
                                    <input type="password" class="form-control-maroon" placeholder="Saffron@2025" id="confirmPasswordInput" name="confirmPasswordInput">
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="strength-container d-flex align-items-center gap-3">
                                    <div class="strength-bar-wrap">
                                        <div class="strength-bar-fill" id="passStrengthBar" style="width: 40%;"></div>
                                    </div>
                                    <span class="strength-label text-warning fw-bold small" id="passStrengthLabel">Strong password</span>
                                </div>
                                <p class="text-muted extra-small mt-2">Use at least 8 characters, one number, one special character, and avoid reusing an older admin password.</p>
                            </div>
                        </div>
                    </div>

                    <!-- 2FA -->
                    <div class="settings-card-layout mb-5">
                        <div class="card-head d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">Two-Factor Authentication</h5>
                            <button class="btn btn-outline-action btn-sm">Setup QR / OTP</button>
                        </div>
                        <div class="card-body-maroon mt-3">
                            <p class="text-muted small mb-4">Add an extra layer of security to your account with OTP verification for every sensitive sign-in.</p>
                            
                            <div class="action-box-maroon d-flex justify-content-between align-items-center p-3 rounded border">
                                <div>
                                    <h6 class="fw-bold mb-1">Enable 2FA</h6>
                                    <p class="text-muted small mb-0">Require a second authentication step when signing in from new devices or locations.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="enable2FA" <?= ($settings['enable2FA'] ?? '1') == '1' ? 'checked' : '' ?>>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Login Activity -->
                    <div class="settings-card-layout mb-5">
                        <div class="card-head d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">Login Activity</h5>
                            <button class="btn btn-outline-action btn-sm">Logout all devices</button>
                        </div>
                        <div class="card-body-maroon mt-3">
                            <p class="text-muted small mb-4">Review the most recent sign-in attempts across browsers and devices. Failed attempts are highlighted for quick investigation.</p>
                            
                            <div class="table-responsive">
                                <table class="activity-table-maroon w-100">
                                    <thead>
                                        <tr>
                                            <th>Device / Browser</th>
                                            <th>Location</th>
                                            <th>Date & Time</th>
                                            <th>Status</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td data-label="Device / Browser">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="device-icon"><i class="bi bi-laptop"></i></div>
                                                    <div class="device-info">
                                                        <div class="fw-bold text-dark">MacBook Pro - Chrome</div>
                                                        <div class="extra-small text-success fw-bold">Current device</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td data-label="Location">Bengaluru, KA</td>
                                            <td data-label="Date & Time">Today, 10:42 AM</td>
                                            <td data-label="Status"><span class="status-badge-maroon success">Success</span></td>
                                            <td data-label="Actions" class="text-end"><span class="action-link-maroon cursor-pointer">Trusted</span></td>
                                        </tr>
                                        <tr>
                                            <td data-label="Device / Browser">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="device-icon"><i class="bi bi-phone"></i></div>
                                                    <div class="device-info">
                                                        <div class="fw-bold text-dark">iPhone 15 - Safari</div>
                                                        <div class="extra-small text-muted">OTP Verified</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td data-label="Location">Hubballi, KA</td>
                                            <td data-label="Date & Time">Yesterday, 8:15 PM</td>
                                            <td data-label="Status"><span class="status-badge-maroon success">Success</span></td>
                                            <td data-label="Actions" class="text-end"><span class="action-link-maroon cursor-pointer">Reviewed</span></td>
                                        </tr>
                                        <tr class="flagged-row">
                                            <td data-label="Device / Browser">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="device-icon flagged"><i class="bi bi-display"></i></div>
                                                    <div class="device-info">
                                                        <div class="fw-bold text-dark text-danger">Windows PC - Edge</div>
                                                        <div class="extra-small text-danger fw-bold">Unrecognized attempt</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td data-label="Location">Pune, MH</td>
                                            <td data-label="Date & Time">Yesterday, 2:08 AM</td>
                                            <td data-label="Status"><span class="status-badge-maroon failed">Failed</span></td>
                                            <td data-label="Actions" class="text-end text-danger fw-bold cursor-pointer">Flagged</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Security Alerts -->
                    <div class="settings-card-layout mb-5">
                        <h5 class="fw-bold mb-4">Security Alerts</h5>
                        <p class="text-muted small mb-4">Choose which risky events should trigger an instant alert for admins and owners.</p>
                        
                        <div class="alert-settings-stack">
                            <div class="alert-toggle-box">
                                <div class="alert-info">
                                    <h6 class="fw-bold text-dark mb-1">Suspicious Login Alerts</h6>
                                    <p class="text-muted small mb-0">Notify the team when a sign-in attempt is detected from an unusual device, IP, or region.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="alertSuspiciousLogin" <?= ($settings['alertSuspiciousLogin'] ?? '1') == '1' ? 'checked' : '' ?>>
                                </div>
                            </div>

                            <div class="alert-toggle-box">
                                <div class="alert-info">
                                    <h6 class="fw-bold text-dark mb-1">Password Change Alerts</h6>
                                    <p class="text-muted small mb-0">Send an alert every time an admin password is updated or reset.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="alertPasswordChange" <?= ($settings['alertPasswordChange'] ?? '0') == '1' ? 'checked' : '' ?>>
                                </div>
                            </div>

                            <div class="alert-toggle-box">
                                <div class="alert-info">
                                    <h6 class="fw-bold text-dark mb-1">New Device Login Alerts</h6>
                                    <p class="text-muted small mb-0">Require an alert and OTP confirmation whenever a fresh device signs in to the account.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="alertNewDeviceLogin" <?= ($settings['alertNewDeviceLogin'] ?? '1') == '1' ? 'checked' : '' ?>>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Placeholder Sections -->
                <section id="notifications" class="settings-section">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <div class="selection-indicator">
                                <i class="bi bi-bell"></i> Selected section: Notifications
                            </div>
                            <h2 class="section-main-title">Notifications</h2>
                            <p class="section-description">Manage how you receive alerts and updates for orders, customers, and system activity across all your preferred channels.</p>
                        </div>
                        <div class="unsaved-status-chip dashboard-status-indicator" style="display: none;">
                            <span class="status-dot"></span>
                            <div class="status-text">
                                <div class="fw-bold">Unsaved changes</div>
                                <div class="small text-muted"><span class="changed-fields-count">0</span> fields updated recently</div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Notifications -->
                    <div class="settings-card-layout mb-5">
                        <div class="card-head d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">Order Notifications</h5>
                            <span class="badge-pill-maroon">Primary section</span>
                        </div>
                        <div class="card-body-maroon mt-4">
                            <p class="text-muted small mb-4">Update the logo and storefront presentation details that customers see across the customer-facing experience.</p>
                            
                            <div class="alert-settings-stack">
                                <div class="alert-toggle-box">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">New Order Alert</h6>
                                        <p class="text-muted small mb-0">Get notified immediately when a customer places a new order on the store.</p>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="notify_new_order" <?= ($settings['notify_new_order'] ?? '1') == '1' ? 'checked' : '' ?>>
                                    </div>
                                </div>

                                <div class="alert-toggle-box">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">Order Status Update</h6>
                                        <p class="text-muted small mb-0">Receive alerts when an order moves from pending to processing or fulfilled.</p>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="notify_order_status" <?= ($settings['notify_order_status'] ?? '1') == '1' ? 'checked' : '' ?>>
                                    </div>
                                </div>

                                <div class="alert-toggle-box">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">Order Cancelled Alert</h6>
                                        <p class="text-muted small mb-0">Alert me when an order is cancelled by the customer or the system.</p>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="notify_order_cancelled" <?= ($settings['notify_order_cancelled'] ?? '1') == '1' ? 'checked' : '' ?>>
                                    </div>
                                </div>

                                <div class="alert-toggle-box opacity-50">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">Payment Received Notification</h6>
                                        <p class="text-muted small mb-0">Notify me upon successful settlement of manual bank transfers or UPI payments.</p>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="notify_payment_received" <?= ($settings['notify_payment_received'] ?? '0') == '1' ? 'checked' : '' ?>>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Notifications -->
                    <div class="settings-card-layout mb-5">
                        <h5 class="fw-bold mb-1">Customer Notifications</h5>
                        <p class="text-muted small mb-4">Update the logo and storefront presentation details that customers see across the customer-facing experience.</p>
                        
                        <div class="alert-settings-stack">
                            <div class="alert-toggle-box">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">New Customer Signup</h6>
                                    <p class="text-muted small mb-0">Receive an alert when a new user creates an account on the storefront.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="notify_customer_signup" <?= ($settings['notify_customer_signup'] ?? '0') == '1' ? 'checked' : '' ?>>
                                </div>
                            </div>

                            <div class="alert-toggle-box">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Customer Feedback / Review Alert</h6>
                                    <p class="text-muted small mb-0">Get notified when a product review or general feedback is submitted.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="notify_feedback_review" <?= ($settings['notify_feedback_review'] ?? '1') == '1' ? 'checked' : '' ?>>
                                </div>
                            </div>

                            <div class="alert-toggle-box">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Abandoned Cart Notification</h6>
                                    <p class="text-muted small mb-0">Alert when high-value carts are abandoned for more than 4 hours.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="notify_abandoned_cart" <?= ($settings['notify_abandoned_cart'] ?? '1') == '1' ? 'checked' : '' ?>>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery & Shipping Notifications -->
                    <div class="settings-card-layout mb-5">
                        <h5 class="fw-bold mb-1">Delivery & Shipping Notifications</h5>
                        <p class="text-muted small mb-4">Track dispatch and logistics statuses seamlessly from the dashboard.</p>
                        
                        <div class="alert-settings-stack">
                            <div class="alert-toggle-box">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Order Shipped</h6>
                                    <p class="text-muted small mb-0">Notify me when the tracking ID is generated and the package leaves the facility.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="notify_shipped" <?= ($settings['notify_shipped'] ?? '1') == '1' ? 'checked' : '' ?>>
                                </div>
                            </div>

                            <div class="alert-toggle-box opacity-50">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Out for Delivery</h6>
                                    <p class="text-muted small mb-0">Get an alert on the day of delivery via logistics partner updates.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="notify_out_for_delivery" <?= ($settings['notify_out_for_delivery'] ?? '0') == '1' ? 'checked' : '' ?>>
                                </div>
                            </div>

                            <div class="alert-toggle-box">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Delivery Completed</h6>
                                    <p class="text-muted small mb-0">Confirm when the customer successfully receives the package.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="notify_delivered" <?= ($settings['notify_delivered'] ?? '1') == '1' ? 'checked' : '' ?>>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notification Frequency -->
                    <div class="settings-card-layout mb-5">
                        <h5 class="fw-bold mb-1">Notification Frequency</h5>
                        <p class="text-muted small mb-4">Control how often you want to receive aggregated summaries to avoid inbox clutter.</p>
                        
                        <div class="form-group-maroon">
                            <select class="form-select form-control-maroon h-auto" id="notify_frequency">
                                <option value="instant" <?= ($settings['notify_frequency'] ?? '') == 'instant' ? 'selected' : '' ?>>Instant Alerts</option>
                                <option value="daily" <?= ($settings['notify_frequency'] ?? '') == 'daily' ? 'selected' : '' ?>>Daily Digest</option>
                                <option value="weekly" <?= ($settings['notify_frequency'] ?? '') == 'weekly' ? 'selected' : '' ?>>Weekly Summary</option>
                            </select>
                        </div>
                    </div>
                </section>
                <section id="payments" class="settings-section">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <div class="selection-indicator">
                                <i class="bi bi-credit-card"></i> Selected section: Payments
                            </div>
                            <h2 class="section-main-title">Payments</h2>
                            <p class="section-description">Manage payment methods, configurations, and transaction settings for your store.</p>
                        </div>
                        <div class="unsaved-status-chip dashboard-status-indicator" style="display: none;">
                            <span class="status-dot"></span>
                            <div class="status-text">
                                <div class="fw-bold">Unsaved changes</div>
                                <div class="small text-muted"><span class="changed-fields-count">0</span> fields updated recently</div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="settings-card-layout mb-5">
                        <div class="card-head">
                            <h5 class="fw-bold mb-1">Payment Methods</h5>
                            <p class="text-muted small">Enable or disable payment options available to customers during checkout.</p>
                        </div>
                        <div class="card-body-maroon mt-4">
                            <div class="alert-settings-stack">
                                <div class="alert-toggle-box">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="icon-wrap bg-light"><i class="bi bi-cash"></i></div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1">Cash on Delivery (COD)</h6>
                                            <p class="text-muted small mb-0">Allow customers to pay in cash upon receiving their order.</p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="payMethodCOD" <?= ($settings['payMethodCOD'] ?? '1') == '1' ? 'checked' : '' ?>>
                                    </div>
                                </div>

                                <div class="alert-toggle-box">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="icon-wrap bg-light"><i class="bi bi-phone"></i></div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1">UPI Payments</h6>
                                            <p class="text-muted small mb-0">Accept payments via GPay, PhonePe, Paytm, and other UPI apps.</p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="payMethodUPI" <?= ($settings['payMethodUPI'] ?? '1') == '1' ? 'checked' : '' ?>>
                                    </div>
                                </div>

                                <div class="alert-toggle-box">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="icon-wrap bg-light"><i class="bi bi-credit-card-2-back"></i></div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1">Credit / Debit Card</h6>
                                            <p class="text-muted small mb-0">Process major credit and debit cards securely.</p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="payMethodCard" <?= ($settings['payMethodCard'] ?? '1') == '1' ? 'checked' : '' ?>>
                                    </div>
                                </div>

                                <div class="alert-toggle-box">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="icon-wrap bg-light"><i class="bi bi-bank"></i></div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1">Net Banking</h6>
                                            <p class="text-muted small mb-0">Enable direct bank transfers during checkout.</p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="payMethodNet" <?= ($settings['payMethodNet'] ?? '0') == '1' ? 'checked' : '' ?>>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- UPI Settings -->
                    <div class="settings-card-layout mb-5">
                        <div class="card-head d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold mb-1">UPI Settings</h5>
                                <p class="text-muted small mb-0">Configure your business UPI account details.</p>
                            </div>
                            <button class="btn btn-outline-action btn-sm">Setup QR / OTP</button>
                        </div>
                        <div class="card-body-maroon mt-4">
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label-maroon mb-2">Upi Id</label>
                                    <input type="text" class="form-control-maroon" id="upiId" value="<?= htmlspecialchars($settings['upiId'] ?? 'vijayakaradant@ybl') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-maroon mb-2">Display Name</label>
                                    <input type="text" class="form-control-maroon" id="upiDisplayName" value="<?= htmlspecialchars($settings['upiDisplayName'] ?? 'Vijaya Karadant Sweets') ?>">
                                </div>
                            </div>

                            <div class="alert-toggle-box mb-4">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Enable 2FA</h6>
                                    <p class="text-muted small mb-0">Require a second authentication step when signing in from new devices or locations.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="upi2FA" <?= ($settings['upi2FA'] ?? '1') == '1' ? 'checked' : '' ?>>
                                </div>
                            </div>

                            <div class="qr-upload-box p-4 border rounded text-center bg-light">
                                <p class="text-muted small mb-3">Static QR code (Optional) <span class="float-end">Shown on Invoices</span></p>
                                <label for="shop_qr_file" class="qr-placeholder d-block text-center py-4 border-dashed rounded bg-white m-0">
                                    <div class="icon-wrap mx-auto mb-2" style="background:var(--nav-icon-bg); color:var(--primary);">
                                        <i class="bi bi-qr-code-scan fs-3"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Click to upload your shop QR code</h6>
                                    <p class="text-muted extra-small">PNG or JPG up to 2MB</p>
                                    <input type="file" id="shop_qr_file" name="shop_qr_file" hidden accept="image/*">
                                </label>
                                <div id="qrPreview" class="mt-3" style="display: <?= !empty($settings['shop_qr']) ? 'block' : 'none' ?>;">
                                    <img src="<?= BASE_URL . ($settings['shop_qr'] ?? '') ?>" alt="QR Preview" style="max-width: 150px; height: auto;" class="rounded border">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Payments Settings -->
                    <div class="settings-card-layout mb-5">
                        <div class="card-head d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold mb-1">Card Payments settings</h5>
                                <p class="text-muted small mb-0">Configure your primary payment gateway integration.</p>
                            </div>
                            <button class="btn btn-outline-action btn-sm">Setup QR / OTP</button>
                        </div>
                        <div class="card-body-maroon mt-4">
                            <div class="form-group-maroon mb-4">
                                <label class="form-label-maroon mb-2">Payment Gateway</label>
                                <select class="form-select form-control-maroon h-auto" id="paymentGateway">
                                    <option value="razorpay" <?= ($settings['paymentGateway'] ?? 'razorpay') === 'razorpay' ? 'selected' : '' ?>>Razorpay</option>
                                    <option value="stripe" <?= ($settings['paymentGateway'] ?? '') === 'stripe' ? 'selected' : '' ?>>Stripe</option>
                                    <option value="paypal" <?= ($settings['paymentGateway'] ?? '') === 'paypal' ? 'selected' : '' ?>>PayPal</option>
                                </select>
                            </div>
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label-maroon mb-2">API Key</label>
                                    <input type="text" class="form-control-maroon" id="payGatewayApiKey" value="<?= htmlspecialchars($settings['payGatewayApiKey'] ?? 'rzp_live_nKy88fjK34MN') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-maroon mb-2">Secret key</label>
                                    <input type="password" class="form-control-maroon" id="payGatewaySecret" value="<?= htmlspecialchars($settings['payGatewaySecret'] ?? 'password123') ?>">
                                </div>
                            </div>
                            <div class="alert-toggle-box">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Test mode</h6>
                                    <p class="text-muted small mb-0">Process dummy transactions without real charges. Ensure this is disabled for live store.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="payGatewayTestMode" <?= ($settings['payGatewayTestMode'] ?? '1') == '1' ? 'checked' : '' ?>>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- COD Settings -->
                    <div class="settings-card-layout mb-5">
                        <div class="card-head d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold mb-1">Cash on delivery (COD) Settings</h5>
                                <p class="text-muted small mb-0">Set rules and extra charges for cash orders.</p>
                            </div>
                            <button class="btn btn-outline-action btn-sm">Setup QR / OTP</button>
                        </div>
                        <div class="card-body-maroon mt-4">
                            <div class="alert-toggle-box mb-4">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Enable COD</h6>
                                    <p class="text-muted small mb-0">Allow customers to select COD on the checkout page.</p>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="enableCODActual" <?= ($settings['enableCODActual'] ?? '1') == '1' ? 'checked' : '' ?>>
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label-maroon mb-2">COD Charges</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">₹</span>
                                        <input type="number" class="form-control-maroon" id="codCharges" value="<?= (int)($settings['codCharges'] ?? 50) ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-maroon mb-2">Minimum order value for COD</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">₹</span>
                                        <input type="number" class="form-control-maroon" id="codMinOrder" value="<?= (int)($settings['codMinOrder'] ?? 500) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Preferences -->
                    <div class="settings-card-layout mb-5">
                        <div class="card-head d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold mb-1">Payment Preferences</h5>
                                <p class="text-muted small mb-0">General settings for order processing.</p>
                            </div>
                            <button class="btn btn-outline-action btn-sm">Setup QR / OTP</button>
                        </div>
                        <div class="card-body-maroon mt-4">
                            <div class="form-group-maroon mb-4">
                                <label class="form-label-maroon mb-2">Default Payment method</label>
                                <select class="form-select form-control-maroon h-auto" id="defaultPayMethod">
                                    <option value="upi" <?= ($settings['defaultPayMethod'] ?? 'upi') === 'upi' ? 'selected' : '' ?>>UPI Payments</option>
                                    <option value="card" <?= ($settings['defaultPayMethod'] ?? '') === 'card' ? 'selected' : '' ?>>Card Payments</option>
                                    <option value="cod" <?= ($settings['defaultPayMethod'] ?? '') === 'cod' ? 'selected' : '' ?>>Cash on Delivery</option>
                                </select>
                            </div>
                            <div class="alert-settings-stack">
                                <div class="alert-toggle-box">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">Auto-capture payments</h6>
                                        <p class="text-muted small mb-0">Automatically capture authorized payments immediately after successful checkout.</p>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="autoCapturePay" <?= ($settings['autoCapturePay'] ?? '1') == '1' ? 'checked' : '' ?>>
                                    </div>
                                </div>
                                <div class="alert-toggle-box">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">Enable Automated Refunds</h6>
                                        <p class="text-muted small mb-0">Process refunds directly to the original payment method upon order cancellation.</p>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="autoRefunds" <?= ($settings['autoRefunds'] ?? '1') == '1' ? 'checked' : '' ?>>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <section id="shipping" class="settings-section">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <div class="selection-indicator">
                                <i class="bi bi-truck"></i> Selected section: Shipping
                            </div>
                            <h2 class="section-main-title">Shipping</h2>
                            <p class="section-description">Configure shipping zones, courier partners, and local delivery rules.</p>
                        </div>
                        <div class="unsaved-status-chip dashboard-status-indicator" style="display: none;">
                            <span class="status-dot"></span>
                            <div class="status-text">
                                <div class="fw-bold">Unsaved changes</div>
                                <div class="small text-muted"><span class="changed-fields-count">0</span> fields updated recently</div>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Rules -->
                    <div class="settings-card-layout mb-5">
                        <div class="card-head">
                            <h5 class="fw-bold mb-1">Global Shipping Rules</h5>
                            <p class="text-muted small">Set baseline thresholds and behavior for all orders.</p>
                        </div>
                        <div class="card-body-maroon mt-4">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label-maroon mb-2">Free Shipping Threshold</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">₹</span>
                                        <input type="number" class="form-control-maroon" id="shipping_free_threshold" value="<?= (int)($settings['shipping_free_threshold'] ?? 1500) ?>">
                                    </div>
                                    <p class="extra-small text-muted mt-2">Orders above this value will not be charged any shipping fees.</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-maroon mb-2">Default Base Rate</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">₹</span>
                                        <input type="number" class="form-control-maroon" id="shipping_base_rate" value="<?= (int)($settings['shipping_base_rate'] ?? 80) ?>">
                                    </div>
                                    <p class="extra-small text-muted mt-2">Standard rate applied if no specific zone rule is matched.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Zones -->
                    <div class="settings-card-layout mb-5">
                        <div class="card-head">
                            <h5 class="fw-bold mb-1">Shipping Zones</h5>
                            <p class="text-muted small">Manage delivery availability and custom rates based on geography.</p>
                        </div>
                        <div class="card-body-maroon mt-4">
                            <div class="alert-settings-stack">
                                <!-- Zone: Local -->
                                <div class="alert-toggle-box">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="icon-wrap bg-light"><i class="bi bi-geo-alt"></i></div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1">Local Delivery (Same City)</h6>
                                            <p class="text-muted small mb-0">Delivery within Hubballi-Dharwad city limits.</p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="input-group input-group-sm" style="width: 100px;">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control" id="shipping_rate_local" value="<?= (int)($settings['shipping_rate_local'] ?? 40) ?>">
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" id="shipping_enable_local" <?= ($settings['shipping_enable_local'] ?? '1') == '1' ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                </div>

                                <!-- Zone: State -->
                                <div class="alert-toggle-box">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="icon-wrap bg-light"><i class="bi bi-map"></i></div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1">Intra-State (Karnataka)</h6>
                                            <p class="text-muted small mb-0">Standard shipping to areas within Karnataka.</p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="input-group input-group-sm" style="width: 100px;">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control" id="shipping_rate_state" value="<?= (int)($settings['shipping_rate_state'] ?? 70) ?>">
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" id="shipping_enable_state" <?= ($settings['shipping_enable_state'] ?? '1') == '1' ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                </div>

                                <!-- Zone: National -->
                                <div class="alert-toggle-box">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="icon-wrap bg-light"><i class="bi bi-globe"></i></div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1">Pan-India (Rest of India)</h6>
                                            <p class="text-muted small mb-0">Fast-tracked courier shipping to other Indian states.</p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="input-group input-group-sm" style="width: 100px;">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control" id="shipping_rate_national" value="<?= (int)($settings['shipping_rate_national'] ?? 120) ?>">
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" id="shipping_enable_national" <?= ($settings['shipping_enable_national'] ?? '1') == '1' ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Partners -->
                    <div class="settings-card-layout mb-5">
                        <div class="card-head">
                            <h5 class="fw-bold mb-1">Carrier Integration</h5>
                            <p class="text-muted small">Toggle automated tracking for supported delivery partners.</p>
                        </div>
                        <div class="card-body-maroon mt-4">
                            <div class="row g-3">
                                <div class="col-6 col-md-3">
                                    <div class="carrier-chip p-3 text-center border rounded" style="cursor: pointer;" onclick="this.classList.toggle('active')">
                                        <img src="<?= BASE_URL ?>assets/images/admin/ship-partner-1.png" alt="BlueDart" class="mb-2 grayscale" style="height: 25px; object-fit: contain;" onerror="this.style.display='none'">
                                        <div class="extra-small fw-bold mt-1">BlueDart</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="carrier-chip p-3 text-center border rounded active" style="cursor: pointer;" onclick="this.classList.toggle('active')">
                                        <img src="<?= BASE_URL ?>assets/images/admin/ship-partner-2.png" alt="Delhivery" class="mb-2" style="height: 25px; object-fit: contain;" onerror="this.style.display='none'">
                                        <div class="extra-small fw-bold mt-1">Delhivery</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="carrier-chip p-3 text-center border rounded" style="cursor: pointer;" onclick="this.classList.toggle('active')">
                                        <img src="<?= BASE_URL ?>assets/images/admin/ship-partner-3.png" alt="DTDC" class="mb-2 grayscale" style="height: 25px; object-fit: contain;" onerror="this.style.display='none'">
                                        <div class="extra-small fw-bold mt-1">DTDC</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="carrier-chip p-3 text-center border rounded" style="cursor: pointer;" onclick="showToast('Feature coming soon: Integration with new delivery partners')">
                                        <div class="placeholder-carrier text-muted extra-small py-2 mt-1 fw-bold">+ Add New</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <section id="appearance" class="settings-section">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <div class="selection-indicator">
                                <i class="bi bi-palette"></i> Selected section: Appearance
                            </div>
                            <h2 class="section-main-title">Appearance</h2>
                            <p class="section-description">Customize your store's brand identity, logos, and visual theme.</p>
                        </div>
                        <div class="unsaved-status-chip dashboard-status-indicator" style="display: none;">
                            <span class="status-dot"></span>
                            <div class="status-text">
                                <div class="fw-bold">Unsaved changes</div>
                                <div class="small text-muted"><span class="changed-fields-count">0</span> fields updated recently</div>
                            </div>
                        </div>
                    </div>

                    <!-- Logo Brand -->
                    <div class="settings-card-layout mb-5">
                        <div class="card-head">
                            <h5 class="fw-bold mb-1">Brand Assets</h5>
                            <p class="text-muted small">Update your store's primary logo and favicon across all platforms.</p>
                        </div>
                        <div class="card-body-maroon mt-4">
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label-maroon mb-2">Main Logo</label>
                                    <div class="settings-logo-uploader">
                                        <div class="logo-display-box" id="logoPreview">
                                            <img src="<?= BASE_URL . ($settings['ui_logo_path'] ?? 'assets/images/logo.png') ?>" alt="Logo">
                                        </div>
                                        <div class="logo-upload-info">
                                            <div class="label mb-1">Upload Store Logo</div>
                                            <div class="meta mb-2">PNG or SVG, Min 2px x 256px</div>
                                            <input type="file" class="d-none" id="ui_logo_file" accept=".png,.svg,.jpg">
                                            <button class="btn btn-outline-action btn-xs" onclick="document.getElementById('ui_logo_file').click()">Select File</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-maroon mb-2">Favicon Icon</label>
                                    <div class="settings-logo-uploader">
                                        <div class="logo-display-box" style="width: 48px; height: 48px;" id="faviconPreview">
                                            <img src="<?= BASE_URL . ($settings['ui_favicon_path'] ?? 'favicon.ico') ?>" alt="Favicon">
                                        </div>
                                        <div class="logo-upload-info">
                                            <div class="label mb-1">Upload Favicon</div>
                                            <div class="meta mb-2">ICO or PNG, 32x32px recommended</div>
                                            <input type="file" class="d-none" id="ui_favicon_file" accept=".ico,.png">
                                            <button class="btn btn-outline-action btn-xs" onclick="document.getElementById('ui_favicon_file').click()">Select File</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Color Palette -->
                    <div class="settings-card-layout mb-5">
                        <div class="card-head">
                            <h5 class="fw-bold mb-1">Brand Palette</h5>
                            <p class="text-muted small">Set default colors for links, buttons, and highlights.</p>
                        </div>
                        <div class="card-body-maroon mt-4">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label-maroon mb-2">Primary Color (Maroon)</label>
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="color" class="form-control form-control-color border-0 p-0" id="ui_primary_color" value="<?= $settings['ui_primary_color'] ?? '#7B1F1F' ?>" style="width: 48px; height: 48px; cursor: pointer;">
                                        <input type="text" class="form-control-maroon flex-grow-1" id="ui_primary_color_text" value="<?= $settings['ui_primary_color'] ?? '#7B1F1F' ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-maroon mb-2">Secondary Color (Gold)</label>
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="color" class="form-control form-control-color border-0 p-0" id="ui_secondary_color" value="<?= $settings['ui_secondary_color'] ?? '#BA9A6E' ?>" style="width: 48px; height: 48px; cursor: pointer;">
                                        <input type="text" class="form-control-maroon flex-grow-1" id="ui_secondary_color_text" value="<?= $settings['ui_secondary_color'] ?? '#BA9A6E' ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Typography -->
                    <div class="settings-card-layout mb-5">
                        <div class="card-head">
                            <h5 class="fw-bold mb-1">Store Typography</h5>
                            <p class="text-muted small">Choose the primary font family for titles and product names.</p>
                        </div>
                        <div class="card-body-maroon mt-4">
                            <div class="form-group-maroon">
                                <label class="form-label-maroon mb-2">Primary Font</label>
                                <select class="form-select form-control-maroon h-auto" id="ui_primary_font">
                                    <option value="Poppins" <?= ($settings['ui_primary_font'] ?? '') == 'Poppins' ? 'selected' : '' ?>>Poppins (Default)</option>
                                    <option value="Inter" <?= ($settings['ui_primary_font'] ?? '') == 'Inter' ? 'selected' : '' ?>>Inter (Modern)</option>
                                    <option value="Montserrat" <?= ($settings['ui_primary_font'] ?? '') == 'Montserrat' ? 'selected' : '' ?>>Montserrat (Classic)</option>
                                    <option value="Playfair Display" <?= ($settings['ui_primary_font'] ?? '') == 'Playfair Display' ? 'selected' : '' ?>>Playfair Display (Luxury)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Floating Action Bar (Bottom Bar) -->
    <div class="unsaved-changes-bar shadow" id="floatingActionBar" style="display: none;">
        <div class="bar-inner d-flex justify-content-between align-items-center">
            <div class="info-side">
                <h6 class="fw-bold text-dark mb-1">Unsaved changes</h6>
                <p class="text-muted small mb-0">Review updated brand details before publishing them to the storefront and admin touchpoints.</p>
            </div>
            <div class="actions-side d-flex gap-3">
                <button class="btn btn-light-maroon btn-cancel-settings px-4">Cancel</button>
                <button class="btn btn-save-maroon px-4 btn-save-settings">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- MOBILE OFFCANVAS NAVIGATION -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSettingsNav">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" style="color:var(--primary);">Settings Navigation</h5>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-3">
        <div class="nav-list-maroon" id="mobileNavList"></div>
    </div>
</div>

<!-- TOAST CONTAINER -->
<div class="toast-container-maroon" id="settingsToastContainer"></div>

<?php require_once 'includes/footer.php'; ?>
