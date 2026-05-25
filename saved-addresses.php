<?php
/**
 * Sweets Website
 * =============================================================
 * File: saved-addresses.php
 * Description: Page to manage delivery locations
 * =============================================================
 */

require_once 'config/config.php';
require_once SERVICES_PATH . '/AddressService.php';

$addressService = new AddressService();
$userId = $_SESSION['user_id'] ?? 1; // Default for prototype
$addresses = $addressService->getAddressesByUser($userId);

require_once 'includes/header.php';
?>

<!-- Saved Addresses Styles -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/saved-addresses.css?v=<?php echo SITE_VERSION; ?>">

<main class="sa-page py-5">
    <div class="container">
        
        <!-- Page Header -->
        <header class="sa-header mb-5 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="sa-title">Saved Addresses</h1>
                <p class="sa-subtitle">Manage your delivery locations</p>
            </div>
            <?php if (isset($_SESSION['address_success'])): ?>
                <div class="alert alert-success py-2 px-3 mb-0"><?php echo $_SESSION['address_success']; unset($_SESSION['address_success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['address_error'])): ?>
                <div class="alert alert-danger py-2 px-3 mb-0"><?php echo $_SESSION['address_error']; unset($_SESSION['address_error']); ?></div>
            <?php endif; ?>
        </header>

        <!-- Addresses Grid -->
        <div class="row g-4">
            
            <?php foreach ($addresses as $addr): ?>
            <!-- Address Card -->
            <div class="col-md-6 col-lg-4">
                <div class="sa-card <?php echo $addr['is_default'] ? 'sa-card--default' : ''; ?>">
                    <div class="sa-card__head">
                        <div class="sa-icon sa-icon--<?php echo $addr['type'] === 'home' ? 'home' : 'office'; ?>">
                            <i class="bi bi-<?php echo $addr['type'] === 'home' ? 'house-door-fill' : 'briefcase-fill'; ?>"></i>
                        </div>
                        <?php if ($addr['is_default']): ?>
                            <span class="sa-badge-default">Default</span>
                        <?php else: ?>
                            <a href="api/address-handler.php?action=setDefault&id=<?php echo $addr['id']; ?>" class="text-decoration-none small text-muted">Set Default</a>
                        <?php endif; ?>
                    </div>
                    <div class="sa-card__body">
                        <h3 class="sa-name"><?php echo htmlspecialchars($addr['recipient_name']); ?></h3>
                        <p class="sa-address">
                            <?php echo htmlspecialchars($addr['address_line1']); ?>,<br>
                            <?php echo $addr['address_line2'] ? htmlspecialchars($addr['address_line2']) . ',<br>' : ''; ?>
                            <?php echo htmlspecialchars($addr['city']); ?>,<br>
                            <?php echo htmlspecialchars($addr['state']); ?> - <?php echo htmlspecialchars($addr['zip_code']); ?>
                        </p>
                        <div class="sa-contact">
                            <i class="bi bi-telephone-fill"></i>
                            <span><?php echo htmlspecialchars($addr['phone']); ?></span>
                        </div>
                    </div>
                    <div class="sa-card__actions">
                        <button class="sa-action-btn js-edit-address" 
                                data-bs-toggle="modal" 
                                data-bs-target="#addressModal"
                                data-id="<?php echo $addr['id']; ?>"
                                data-name="<?php echo htmlspecialchars($addr['recipient_name']); ?>"
                                data-phone="<?php echo htmlspecialchars($addr['phone']); ?>"
                                data-line1="<?php echo htmlspecialchars($addr['address_line1']); ?>"
                                data-line2="<?php echo htmlspecialchars($addr['address_line2']); ?>"
                                data-city="<?php echo htmlspecialchars($addr['city']); ?>"
                                data-state="<?php echo htmlspecialchars($addr['state']); ?>"
                                data-zip="<?php echo htmlspecialchars($addr['zip_code']); ?>"
                                data-type="<?php echo $addr['type']; ?>"
                                data-default="<?php echo $addr['is_default']; ?>"
                                title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="api/address-handler.php?action=delete&id=<?php echo $addr['id']; ?>" 
                           class="sa-action-btn" title="Delete" 
                           onclick="return confirm('Are you sure you want to delete this address?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Add New Address Card -->
            <div class="col-md-6 col-lg-4">
                <div class="sa-card sa-card--add-new" data-bs-toggle="modal" data-bs-target="#addressModal" id="addNewAddressBtn">
                    <div class="sa-add-content">
                        <div class="sa-add-icon">
                            <i class="bi bi-plus-lg"></i>
                        </div>
                        <h4 class="sa-add-title">Add Address</h4>
                        <p class="sa-add-sub">Save a Delivery Spot</p>
                    </div>
                </div>
            </div>

        </div><!-- /row -->
    </div><!-- /container -->
</main>

<!-- Address Modal (Add/Edit) -->
<div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="addressModalLabel">Add New Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="api/address-handler.php" method="POST" id="addressForm">
                <div class="modal-body py-4">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="addressId" value="">
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold">Recipient Name</label>
                            <input type="text" name="recipient_name" id="recipientName" class="form-control rounded-3" placeholder="Full Name" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Phone Number</label>
                            <input type="tel" name="phone" id="phone" class="form-control rounded-3" placeholder="+91" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Address Line 1</label>
                            <input type="text" name="address_line1" id="addressLine1" class="form-control rounded-3" placeholder="House No, Street" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Address Line 2 (Optional)</label>
                            <input type="text" name="address_line2" id="addressLine2" class="form-control rounded-3" placeholder="Landmark/Area">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">City</label>
                            <input type="text" name="city" id="city" class="form-control rounded-3" placeholder="City" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">State</label>
                            <input type="text" name="state" id="state" class="form-control rounded-3" placeholder="State" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ZIP Code</label>
                            <input type="text" name="zip_code" id="zipCode" class="form-control rounded-3" placeholder="Pincode" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Address Type</label>
                            <select name="type" id="type" class="form-select rounded-3">
                                <option value="home">Home</option>
                                <option value="office">Office</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_default" id="isDefault" value="1">
                                <label class="form-check-label small text-muted" for="isDefault">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addressModal = document.getElementById('addressModal');
    const form = document.getElementById('addressForm');
    const label = document.getElementById('addressModalLabel');
    const actionInput = document.getElementById('formAction');
    const idInput = document.getElementById('addressId');

    // Handle Edit button clicks
    document.querySelectorAll('.js-edit-address').forEach(btn => {
        btn.addEventListener('click', function() {
            label.textContent = 'Edit Address';
            actionInput.value = 'edit';
            idInput.value = this.dataset.id;
            
            document.getElementById('recipientName').value = this.dataset.name;
            document.getElementById('phone').value = this.dataset.phone;
            document.getElementById('addressLine1').value = this.dataset.line1;
            document.getElementById('addressLine2').value = this.dataset.line2;
            document.getElementById('city').value = this.dataset.city;
            document.getElementById('state').value = this.dataset.state;
            document.getElementById('zipCode').value = this.dataset.zip;
            document.getElementById('type').value = this.dataset.type;
            document.getElementById('isDefault').checked = this.dataset.default == '1';
        });
    });

    // Reset form for "Add New"
    document.getElementById('addNewAddressBtn').addEventListener('click', function() {
        label.textContent = 'Add New Address';
        actionInput.value = 'add';
        idInput.value = '';
        form.reset();
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
