<?php
require_once 'config/config.php';
require_once SERVICES_PATH . '/CustomerService.php';
require_once SERVICES_PATH . '/AddressService.php';
require_once __DIR__ . '/src/Validator/ProfileValidator.php';

use App\Validator\ProfileValidator;

$customerService = new CustomerService();
$addressService = new AddressService();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=profile.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$profileData = $customerService->getProfileData($userId);

if (!$profileData) {
    // If somehow session user_id doesn't exist in DB, logout or error
    header('Location: api/logout.php');
    exit;
}

$user = $profileData['profile'];
$addresses = $profileData['addresses'];

$profile_errors = [];
$success_msg = $_SESSION['profile_success'] ?? null;
unset($_SESSION['profile_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['full_name'])) {
    try {
        // CSRF Check
        if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid security token.');
        }

        $validator = new ProfileValidator();
        $validatedData = $validator->validate($_POST);
        
        // Call Service to update
        $updateSuccess = $customerService->updateProfile($userId, $validatedData);
        
        if ($updateSuccess) {
            $_SESSION['profile_success'] = 'Profile updated successfully!';
            header('Location: profile.php');
            exit;
        } else {
            throw new Exception('Failed to update profile. Please try again.');
        }
        
    } catch (InvalidArgumentException $e) {
        $profile_errors = json_decode($e->getMessage(), true);
    } catch (Exception $e) {
        $profile_errors['general'] = $e->getMessage();
    }
}

require_once 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/profile.css?v=<?php echo SITE_VERSION; ?>">

<main class="p-profile">

    <!-- ── HERO TITLE STRIP ── -->
    <section class="p-profile__hero-strip">
        <div class="container">
            <h1 class="p-profile__strip-title">My Profile</h1>
            <p class="p-profile__strip-sub">Manage your personal information, addresses and track orders</p>
        </div>
    </section>

    <!-- ── PROFILE IDENTITY CARD ── -->
    <section class="py-3">
        <div class="container">
            <div class="p-profile__identity-card">
                <div class="p-profile__avatar-wrap">
                    <img src="<?php echo htmlspecialchars($user['avatar_url'] ?: 'assets/images/profile/avatar.png'); ?>"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($user['name']); ?>&background=7b1d1d&color=fff&size=96'"
                         alt="<?php echo htmlspecialchars($user['name']); ?>">
                </div>
                <div class="p-profile__identity-info">
                    <h2 class="p-profile__name"><?php echo htmlspecialchars($user['name']); ?></h2>
                    <div class="p-profile__meta-row">
                        <span><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></span>
                        <span><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($user['phone'] ?: 'No phone provided'); ?></span>
                    </div>
                </div>
                <div class="p-profile__identity-action">
                    <button type="button" class="p-profile__btn-edit" onclick="document.querySelector('form.p-profile__form input[name=full_name]').focus();">
                        <i class="bi bi-pencil-square"></i> Edit Profile
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- ── MAIN CONTENT: LEFT FORMS | RIGHT QUICK LINKS ── -->
    <section class="py-4 pb-5">
        <div class="container">
            <div class="row g-4">

                <!-- LEFT COLUMN -->
                <div class="col-lg-7">

                    <!-- Account Information (editable inputs) -->
                    <form method="POST" action="profile.php" class="p-profile__form">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="p-profile__panel mb-4">
                            <h3 class="p-profile__panel-title">Account Information</h3>
                            
                            <?php if ($success_msg): ?>
                                <div class="alert alert-success"><?php echo $success_msg; ?></div>
                            <?php endif; ?>
                            <?php if (isset($profile_errors['general'])): ?>
                                <div class="alert alert-danger"><?php echo $profile_errors['general']; ?></div>
                            <?php endif; ?>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="p-profile__form-label">Full Name</label>
                                    <input type="text" name="full_name" class="p-profile__input <?php echo isset($profile_errors['full_name']) ? 'is-invalid' : ''; ?>" 
                                           placeholder="Full Name" value="<?php echo htmlspecialchars($user['name']); ?>">
                                    <?php if (isset($profile_errors['full_name'])): ?>
                                        <div class="invalid-feedback"><?php echo $profile_errors['full_name']; ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="p-profile__form-label">Email Address</label>
                                    <input type="email" name="email" class="p-profile__input <?php echo isset($profile_errors['email']) ? 'is-invalid' : ''; ?>" 
                                           placeholder="Email Address" value="<?php echo htmlspecialchars($user['email']); ?>">
                                    <?php if (isset($profile_errors['email'])): ?>
                                        <div class="invalid-feedback"><?php echo $profile_errors['email']; ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="p-profile__form-label">Phone Number</label>
                                    <input type="tel" name="phone" class="p-profile__input <?php echo isset($profile_errors['phone']) ? 'is-invalid' : ''; ?>" 
                                           placeholder="Phone Number" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                    <?php if (isset($profile_errors['phone'])): ?>
                                        <div class="invalid-feedback"><?php echo $profile_errors['phone']; ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="p-profile__form-label">Alternate Phone (Optional)</label>
                                    <input type="tel" name="alternate_phone" class="p-profile__input" placeholder="Alternate Phone" value="<?php echo htmlspecialchars($user['alternate_phone'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="p-profile__form-label">Date of Birth</label>
                                    <input type="date" name="dob" class="p-profile__input" value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="p-profile__form-label">Gender</label>
                                    <select name="gender" class="p-profile__input">
                                        <option value="unspecified" <?php echo ($user['gender'] == 'unspecified') ? 'selected' : ''; ?>>Unspecified</option>
                                        <option value="male" <?php echo ($user['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="female" <?php echo ($user['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                                        <option value="other" <?php echo ($user['gender'] == 'other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                
                                <div class="col-12 mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="marketing_opt_in" id="marketing_opt_in" <?php echo ($user['marketing_opt_in']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label small text-muted" for="marketing_opt_in">
                                            Text me with news and exclusive offers
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-12 mt-4 pt-2 border-top">
                                    <h4 class="fs-6 fw-bold mb-3">Security & Password</h4>
                                </div>

                                <div class="col-md-6">
                                    <label class="p-profile__form-label">New Password</label>
                                    <input type="password" name="new_password" id="newPassword" class="p-profile__input <?php echo isset($profile_errors['new_password']) ? 'is-invalid' : ''; ?>" 
                                           placeholder="Leave blank to keep current">
                                    <div class="password-strength-meter mt-2" id="strengthMeter">
                                        <div class="meter-bar"></div>
                                        <span class="meter-text small text-muted">Enter a password</span>
                                    </div>
                                    <?php if (isset($profile_errors['new_password'])): ?>
                                        <div class="invalid-feedback"><?php echo $profile_errors['new_password']; ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="p-profile__form-label">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="p-profile__input <?php echo isset($profile_errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                           placeholder="Repeat new password">
                                    <?php if (isset($profile_errors['confirm_password'])): ?>
                                        <div class="invalid-feedback"><?php echo $profile_errors['confirm_password']; ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12">
                                    <label class="p-profile__form-label">Current Password (to save changes)</label>
                                    <input type="password" name="current_password" class="p-profile__input" placeholder="Enter current password to verify" required>
                                </div>
                                <div class="col-12 mt-3 text-end">
                                    <button class="p-profile__btn-save" type="submit">Save All Changes</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Shipping Addresses -->
                    <div class="p-profile__panel">
                        <div class="p-profile__panel-header">
                            <h3 class="p-profile__panel-title mb-0">Shipping Addresses</h3>
                            <button class="p-profile__btn-add" type="button" data-bs-toggle="modal" data-bs-target="#profileAddressModal" id="profileAddNewAddressBtn">
                                <i class="bi bi-plus"></i> Add New
                            </button>
                        </div>

                        <?php if (isset($_SESSION['address_success'])): ?>
                            <div class="alert alert-success mt-3 mb-0"><?php echo htmlspecialchars($_SESSION['address_success']); unset($_SESSION['address_success']); ?></div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['address_error'])): ?>
                            <div class="alert alert-danger mt-3 mb-0"><?php echo htmlspecialchars($_SESSION['address_error']); unset($_SESSION['address_error']); ?></div>
                        <?php endif; ?>

                        <div class="row g-3 mt-1">
                            <?php if (!empty($addresses)): ?>
                                <?php foreach ($addresses as $addr): ?>
                                    <div class="col-md-6">
                                        <div class="p-profile__addr-card">
                                            <div class="p-profile__addr-head">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="p-profile__addr-label"><?php echo htmlspecialchars(ucfirst((string)$addr['type'])); ?></span>
                                                    <?php if (!empty($addr['is_default'])): ?>
                                                        <span class="p-profile__badge">Default</span>
                                                    <?php else: ?>
                                                        <a href="api/address-handler.php?action=setDefault&id=<?php echo (int)$addr['id']; ?>&redirect=profile.php" class="small text-decoration-none">Set Default</a>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <button
                                                        class="p-profile__icon-btn js-profile-edit-address"
                                                        type="button"
                                                        aria-label="Edit"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#profileAddressModal"
                                                        data-id="<?php echo (int)$addr['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars((string)$addr['recipient_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-phone="<?php echo htmlspecialchars((string)$addr['phone'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-line1="<?php echo htmlspecialchars((string)$addr['address_line1'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-line2="<?php echo htmlspecialchars((string)$addr['address_line2'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-city="<?php echo htmlspecialchars((string)$addr['city'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-state="<?php echo htmlspecialchars((string)$addr['state'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-zip="<?php echo htmlspecialchars((string)$addr['zip_code'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-type="<?php echo htmlspecialchars((string)$addr['type'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-default="<?php echo (int)$addr['is_default']; ?>"
                                                    ><i class="bi bi-pencil"></i></button>
                                                    <a
                                                        class="p-profile__icon-btn"
                                                        href="api/address-handler.php?action=delete&id=<?php echo (int)$addr['id']; ?>&redirect=profile.php"
                                                        aria-label="Delete"
                                                        onclick="return confirm('Are you sure you want to delete this address?')"
                                                    ><i class="bi bi-trash"></i></a>
                                                </div>
                                            </div>
                                            <p class="p-profile__addr-text">
                                                <?php echo htmlspecialchars((string)$addr['address_line1']); ?><br>
                                                <?php if (!empty($addr['address_line2'])): ?>
                                                    <?php echo htmlspecialchars((string)$addr['address_line2']); ?><br>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars((string)$addr['city']); ?>, <?php echo htmlspecialchars((string)$addr['state']); ?> - <?php echo htmlspecialchars((string)$addr['zip_code']); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="p-profile__addr-card text-center">
                                        <p class="p-profile__addr-text mb-0">No shipping addresses yet. Add your first address.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action Buttons -->
                        <div class="p-profile__panel-actions">
                            <a href="api/logout.php" class="p-profile__btn-logout text-decoration-none text-center">Logout Account</a>
                            <button class="p-profile__btn-save" type="button">Save All Changes</button>
                        </div>
                    </div>

                </div><!-- /col-lg-7 -->

                <!-- RIGHT COLUMN: Quick Links -->
                <div class="col-lg-5">
                    <h3 class="p-profile__panel-title mb-3">Account Information</h3>

                    <a class="p-profile__ql-card" href="my-orders.php">
                        <div class="p-profile__ql-icon">
                            <i class="bi bi-bag-check"></i>
                        </div>
                        <div class="p-profile__ql-text">
                            <h5>My Orders</h5>
                            <p>Track, return or buy things again</p>
                        </div>
                    </a>

                    <a class="p-profile__ql-card" href="wishlist.php">
                        <div class="p-profile__ql-icon">
                            <i class="bi bi-heart"></i>
                        </div>
                        <div class="p-profile__ql-text">
                            <h5>Wishlist</h5>
                            <p>Save your favorites for later</p>
                        </div>
                    </a>

                    <a class="p-profile__ql-card" href="saved-addresses.php">
                        <div class="p-profile__ql-icon">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <div class="p-profile__ql-text">
                            <h5>Saved Addresses</h5>
                            <p>Manage delivery locations</p>
                        </div>
                    </a>

                    <a class="p-profile__ql-card" href="payment-methods.php">
                        <div class="p-profile__ql-icon">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <div class="p-profile__ql-text">
                            <h5>Payment Methods</h5>
                            <p>Edit or remove card details</p>
                        </div>
                    </a>

                    <!-- Need Help card (with Contact Support link at bottom) -->
                    <div class="p-profile__ql-card p-profile__ql-card--help">
                        <div class="d-flex align-items-start gap-3">
                            <div class="p-profile__ql-icon">
                                <i class="bi bi-question-circle"></i>
                            </div>
                            <div class="p-profile__ql-text">
                                <h5>Need Help?</h5>
                                <p>Our support is here 24/7</p>
                            </div>
                        </div>
                        <a href="help.php" class="p-profile__ql-support-link">Contact Support <i class="bi bi-arrow-right"></i></a>
                    </div>

                </div><!-- /col-lg-5 -->

            </div><!-- /row -->
        </div><!-- /container -->
    </section>

</main>

<div class="modal fade" id="profileAddressModal" tabindex="-1" aria-labelledby="profileAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="profileAddressModalLabel">Add New Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="api/address-handler.php" method="POST" id="profileAddressForm">
                <div class="modal-body py-4">
                    <input type="hidden" name="action" id="profileFormAction" value="add">
                    <input type="hidden" name="id" id="profileAddressId" value="">
                    <input type="hidden" name="redirect" value="profile.php">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold">Recipient Name</label>
                            <input type="text" name="recipient_name" id="profileRecipientName" class="form-control rounded-3" placeholder="Full Name" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Phone Number</label>
                            <input type="tel" name="phone" id="profilePhone" class="form-control rounded-3" placeholder="+91" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Address Line 1</label>
                            <input type="text" name="address_line1" id="profileAddressLine1" class="form-control rounded-3" placeholder="House No, Street" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Address Line 2 (Optional)</label>
                            <input type="text" name="address_line2" id="profileAddressLine2" class="form-control rounded-3" placeholder="Landmark/Area">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">City</label>
                            <input type="text" name="city" id="profileCity" class="form-control rounded-3" placeholder="City" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">State</label>
                            <input type="text" name="state" id="profileState" class="form-control rounded-3" placeholder="State" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ZIP Code</label>
                            <input type="text" name="zip_code" id="profileZipCode" class="form-control rounded-3" placeholder="Pincode" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Address Type</label>
                            <select name="type" id="profileType" class="form-select rounded-3">
                                <option value="home">Home</option>
                                <option value="office">Office</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_default" id="profileIsDefault" value="1">
                                <label class="form-check-label small text-muted" for="profileIsDefault">
                                    Set as default shipping address
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" style="background-color: var(--clr-secondary); border: none;">Save Address</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.password-strength-meter .meter-bar { height: 4px; background: #eee; border-radius: 2px; transition: all 0.3s; }
.password-strength-meter .meter-bar.weak { background: #ff4d4d; }
.password-strength-meter .meter-bar.medium { background: #ffa500; }
.password-strength-meter .meter-bar.strong { background: #2ecc71; }
.password-strength-meter .meter-bar.very-strong { background: #27ae60; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password Strength Logic
    const passwordInput = document.getElementById('newPassword');
    if (passwordInput) {
        const strengthMeter = document.getElementById('strengthMeter');
        const meterBar = strengthMeter.querySelector('.meter-bar');
        const meterText = strengthMeter.querySelector('.meter-text');

        passwordInput.addEventListener('input', function() {
            const val = passwordInput.value;
            let strength = 0;
            let msg = '';

            if (val.length >= 8) strength++;
            if (/[A-Z]/.test(val)) strength++;
            if (/[0-9]/.test(val)) strength++;
            if (/[^A-Za-z0-9]/.test(val)) strength++;

            meterBar.className = 'meter-bar';
            if (val.length === 0) {
                meterBar.style.width = '0';
                msg = 'Enter a password';
            } else if (strength < 2) {
                meterBar.classList.add('weak');
                meterBar.style.width = '25%';
                msg = 'Weak';
            } else if (strength < 3) {
                meterBar.classList.add('medium');
                meterBar.style.width = '50%';
                msg = 'Medium';
            } else if (strength < 4) {
                meterBar.classList.add('strong');
                meterBar.style.width = '75%';
                msg = 'Strong';
            } else {
                meterBar.classList.add('very-strong');
                meterBar.style.width = '100%';
                msg = 'Very Strong';
            }
            meterText.textContent = msg;
        });
    }

    // Address Modal Logic
    const form = document.getElementById('profileAddressForm');
    const modalTitle = document.getElementById('profileAddressModalLabel');
    const actionInput = document.getElementById('profileFormAction');
    const idInput = document.getElementById('profileAddressId');
    const addButton = document.getElementById('profileAddNewAddressBtn');

    if (!form || !modalTitle || !actionInput || !idInput || !addButton) {
        return;
    }

    document.querySelectorAll('.js-profile-edit-address').forEach(function(button) {
        button.addEventListener('click', function() {
            modalTitle.textContent = 'Edit Address';
            actionInput.value = 'edit';
            idInput.value = this.dataset.id || '';

            document.getElementById('profileRecipientName').value = this.dataset.name || '';
            document.getElementById('profilePhone').value = this.dataset.phone || '';
            document.getElementById('profileAddressLine1').value = this.dataset.line1 || '';
            document.getElementById('profileAddressLine2').value = this.dataset.line2 || '';
            document.getElementById('profileCity').value = this.dataset.city || '';
            document.getElementById('profileState').value = this.dataset.state || '';
            document.getElementById('profileZipCode').value = this.dataset.zip || '';
            document.getElementById('profileType').value = this.dataset.type || 'home';
            document.getElementById('profileIsDefault').checked = this.dataset.default === '1';
        });
    });

    addButton.addEventListener('click', function() {
        modalTitle.textContent = 'Add New Address';
        actionInput.value = 'add';
        idInput.value = '';
        form.reset();
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
