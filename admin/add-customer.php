<?php
/**
 * Sweets Website
 * =============================================================
 * File: add-customer.php
 * Description: Premium Add Customer panel
 * =============================================================
 */

$pageStyles = ['assets/css/admin/pages/add-customer.css'];

require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';
?>

<div class="main-content add-customer-page">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body add-customer-body">
        <button class="add-customer-reopen" id="addCustomerReopen" type="button">
            <i class="bi bi-plus-circle me-2"></i>Add Customer
        </button>

        <div class="add-customer-wrapper" id="addCustomerWrapper">
            <div class="add-customer-card">
                <div class="add-customer-scroll-track" id="addCustomerTrack">
                    <div class="add-customer-track-bg"></div>
                    <div class="add-customer-thumb" id="addCustomerThumb"></div>
                </div>

                <div class="add-customer-scroll" id="addCustomerScroll">
                    <div class="add-customer-header">
                        <h1 class="add-customer-title">Add Customer</h1>
                        <button class="add-customer-close" type="button" id="addCustomerClose" aria-label="Close panel">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="add-customer-field">
                        <label class="add-customer-label" for="customerFullName">Full Name</label>
                        <input type="text" id="customerFullName" class="add-customer-input" placeholder="Enter full name*">
                    </div>

                    <div class="add-customer-field">
                        <div class="add-customer-row-2">
                            <div class="add-customer-col-1-2">
                                <label class="add-customer-label" for="customerEmail">Email Address</label>
                                <input type="email" id="customerEmail" class="add-customer-input" placeholder="Enter email">
                            </div>
                            <div class="add-customer-col-1-2">
                                <label class="add-customer-label" for="customerPhone">Phone Number</label>
                                <input type="text" id="customerPhone" class="add-customer-input" placeholder="Enter phone number">
                            </div>
                        </div>
                    </div>

                    <div class="add-customer-field">
                        <label class="add-customer-label" for="customerAddress">Address Line</label>
                        <select id="customerAddress" class="add-customer-select">
                            <option value="" disabled selected>Enter address*</option>
                            <option value="address1">123 Main Street, Block A</option>
                            <option value="address2">456 Park Avenue, Suite 10</option>
                            <option value="address3">789 Gandhi Nagar, Sector 5</option>
                        </select>
                    </div>

                    <div class="add-customer-field">
                        <div class="add-customer-row-3">
                            <div class="add-customer-col-1-3">
                                <label class="add-customer-label" for="customerCity">City</label>
                                <input type="text" id="customerCity" class="add-customer-input" placeholder="Enter city">
                            </div>
                            <div class="add-customer-col-1-3">
                                <label class="add-customer-label" for="customerState">State</label>
                                <input type="text" id="customerState" class="add-customer-input" placeholder="Enter state">
                            </div>
                            <div class="add-customer-col-1-3">
                                <label class="add-customer-label" for="customerPincode">Pincode</label>
                                <input type="text" id="customerPincode" class="add-customer-input" placeholder="Enter pincode">
                            </div>
                        </div>
                    </div>

                    <div class="add-customer-field">
                        <div class="add-customer-row-2">
                            <div class="add-customer-col-1-2">
                                <label class="add-customer-label" for="customerPassword">Password</label>
                                <input type="password" id="customerPassword" class="add-customer-input" placeholder="Enter password">
                            </div>
                            <div class="add-customer-col-1-2">
                                <label class="add-customer-label" for="customerPasswordConfirm">Confirm Password</label>
                                <input type="password" id="customerPasswordConfirm" class="add-customer-input" placeholder="Re-enter password">
                            </div>
                        </div>
                    </div>

                    <div class="add-customer-toggle-row">
                        <span class="add-customer-toggle-label">Customer Status</span>
                        <label class="add-customer-toggle">
                            <input type="checkbox" id="customerStatus" checked>
                            <span class="add-customer-toggle-slider"></span>
                        </label>
                    </div>

                    <div class="add-customer-field">
                        <div class="add-customer-radio-group">
                            <label class="add-customer-radio">
                                <input type="radio" name="customerType" value="new" checked>
                                <span class="add-customer-radio-dot"></span>
                                <span class="add-customer-radio-text">New</span>
                            </label>
                            <label class="add-customer-radio">
                                <input type="radio" name="customerType" value="vip">
                                <span class="add-customer-radio-dot"></span>
                                <span class="add-customer-radio-text">VIP</span>
                            </label>
                            <label class="add-customer-radio">
                                <input type="radio" name="customerType" value="frequent">
                                <span class="add-customer-radio-dot"></span>
                                <span class="add-customer-radio-text">Frequent Buyer</span>
                            </label>
                        </div>
                    </div>

                    <div class="add-customer-field">
                        <label class="add-customer-label" for="customerNotes">Notes</label>
                        <input type="text" id="customerNotes" class="add-customer-input" placeholder="Add notes">
                    </div>

                    <div class="add-customer-actions">
                        <button class="add-customer-btn add-customer-btn-cancel" type="button" id="addCustomerCancel">Cancel</button>
                        <button class="add-customer-btn add-customer-btn-save" type="button" id="addCustomerSave">Save Customer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scrollContainer = document.getElementById('addCustomerScroll');
        const thumb = document.getElementById('addCustomerThumb');
        const wrapper = document.getElementById('addCustomerWrapper');
        const reopenBtn = document.getElementById('addCustomerReopen');
        const closeBtn = document.getElementById('addCustomerClose');
        const cancelBtn = document.getElementById('addCustomerCancel');
        const saveBtn = document.getElementById('addCustomerSave');

        function updateScrollbar() {
            if (!scrollContainer || !thumb) return;
            const trackHeight = scrollContainer.offsetHeight;
            const contentHeight = scrollContainer.scrollHeight;
            const scrollTop = scrollContainer.scrollTop;

            if (contentHeight <= trackHeight) {
                thumb.style.display = 'none';
                return;
            }

            thumb.style.display = 'block';
            const thumbHeight = Math.max((trackHeight / contentHeight) * trackHeight, 40);
            const maxScroll = contentHeight - trackHeight;
            const maxThumbTop = trackHeight - thumbHeight;
            const thumbTop = (scrollTop / maxScroll) * maxThumbTop;

            thumb.style.height = thumbHeight + 'px';
            thumb.style.top = thumbTop + 'px';
        }

        if (scrollContainer) {
            scrollContainer.addEventListener('scroll', updateScrollbar);
        }
        window.addEventListener('resize', updateScrollbar);
        updateScrollbar();

        function closePanel() {
            if (!wrapper || !reopenBtn) return;
            wrapper.style.transition = 'opacity 0.3s, transform 0.3s';
            wrapper.style.opacity = '0';
            wrapper.style.transform = 'translateY(20px)';
            setTimeout(() => {
                wrapper.style.display = 'none';
                reopenBtn.style.display = 'inline-block';
            }, 300);
        }

        function reopenPanel() {
            if (!wrapper || !reopenBtn) return;
            reopenBtn.style.display = 'none';
            wrapper.style.display = 'block';
            wrapper.style.opacity = '0';
            wrapper.style.transform = 'translateY(20px)';
            requestAnimationFrame(() => {
                wrapper.style.transition = 'opacity 0.4s, transform 0.4s';
                wrapper.style.opacity = '1';
                wrapper.style.transform = 'translateY(0)';
            });
            setTimeout(updateScrollbar, 100);
        }

        function resetForm() {
            const fields = document.querySelectorAll('.add-customer-input, .add-customer-select');
            fields.forEach(field => {
                if (field.tagName === 'SELECT') {
                    field.selectedIndex = 0;
                } else {
                    field.value = '';
                }
            });
            const radios = document.querySelectorAll('input[name="customerType"]');
            if (radios.length) {
                radios[0].checked = true;
            }
            const statusToggle = document.getElementById('customerStatus');
            if (statusToggle) statusToggle.checked = true;
        }

        function showSavedState() {
            if (!saveBtn) return;
            const original = saveBtn.innerHTML;
            saveBtn.classList.add('is-success');
            saveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Saved!';
            setTimeout(() => {
                saveBtn.classList.remove('is-success');
                saveBtn.innerHTML = original;
            }, 1800);
        }

        if (closeBtn) closeBtn.addEventListener('click', closePanel);
        if (reopenBtn) reopenBtn.addEventListener('click', reopenPanel);
        if (cancelBtn) cancelBtn.addEventListener('click', resetForm);
        if (saveBtn) saveBtn.addEventListener('click', showSavedState);
    });
</script>

<?php require_once 'includes/footer.php'; ?>
