<?php
/**
 * Sweets Website - Admin
 * =============================================================
 * File: admin
 * Description: High-Fidelity Offers & Coupons Management Dashboard
 * Author: Antigravity
 * Version: 2.1.0
 * =============================================================
 */

require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';
require_once ROOT_PATH . '/config/Database.php';
require_once REPOS_PATH . '/CouponRepository.php';

$pageTitle = "Offer & Coupons";
$db = Database::getInstance();
$repo = new CouponRepository($db);
$coupons = $repo->getAllCoupons();
?>

<style>
    :root {
        --primary-maroon: #8B2E2E;
        --secondary-gold: #D97706;
        --bg-light: #FDF8F5;
        --border-maroon: #E2D8CE;
    }

    .btn-maroon {
        background-color: var(--primary-maroon);
        color: white;
        font-weight: 600;
        border-radius: 10px;
        padding: 10px 20px;
    }
    .btn-maroon:hover { background-color: #722424; color: white; }

    /* Table Styling */
    .table-card {
        background: white;
        border-radius: 16px;
        border: 1px solid var(--border-maroon);
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
    .table thead th {
        background: #FDF8F5;
        color: #666;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
        padding: 16px 24px;
        border-bottom: 1px solid var(--border-maroon);
    }
    .table tbody td {
        padding: 18px 24px;
        vertical-align: middle;
        color: #333;
        border-bottom: 1px solid #F5F5F5;
    }
    .coupon-code-tag {
        background: #FFF5F5;
        color: var(--primary-maroon);
        font-weight: 800;
        padding: 6px 12px;
        border-radius: 6px;
        font-family: monospace;
        border: 1px dashed var(--primary-maroon);
    }

    /* Badge Styling */
    .badge-active { background: #DCFCE7; color: #166534; font-weight: 600; padding: 6px 12px; border-radius: 20px; }
    .badge-expired { background: #FEE2E2; color: #991B1B; font-weight: 600; padding: 6px 12px; border-radius: 20px; }

    /* Modal Styling */
    .modal-content { border-radius: 20px; border: none; }
    .modal-header { border-bottom: 1px solid var(--border-maroon); padding: 24px; background: #FDF8F5; border-radius: 20px 20px 0 0; }
    .form-label { font-weight: 600; color: #444; font-size: 0.9rem; }
    .form-control, .form-select { border-radius: 10px; border: 1px solid var(--border-maroon); padding: 12px; }

    /* Responsive Mobile Cards */
    @media (max-width: 767px) {
        .table-responsive table { display: block; }
        .table-responsive thead { display: none; }
        .table-responsive tbody { display: block; }
        .table-responsive tr {
            display: flex;
            flex-direction: column;
            background: #fff;
            border: 1px solid rgba(0,0,0,0.08) !important;
            border-radius: 12px;
            margin-bottom: 1rem;
            padding: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }
        .table-responsive td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0 !important;
            border-bottom: 1px solid rgba(0,0,0,0.04) !important;
            text-align: right;
        }
        .table-responsive td:last-child {
            border-bottom: none !important;
            border-top: 1px solid #eee !important;
            margin-top: 0.5rem;
            padding-top: 1rem !important;
            justify-content: center;
        }
        .table-responsive td::before {
            content: attr(data-label);
            font-weight: 700;
            color: #8C3333;
            font-size: 0.8rem;
            text-transform: uppercase;
            text-align: left;
        }
    }
</style>

<div class="main-content">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body px-4 pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
            <div>
                <h1 class="fw-bold h2" style="color: #4A1D1D;">Offers & Coupons</h1>
                <p class="text-muted mb-0">Manage your promotional campaigns and discount codes</p>
            </div>
            <button class="btn btn-maroon" onclick="openCreateModal()">
                <i class="bi bi-plus-lg me-2"></i>Create New Offer
            </button>
        </div>

        <div class="table-card p-3 p-md-4 mb-4" id="festival-timer-card" style="max-width: 100%; overflow: hidden;">
            <style>
                #timerEndInput::-webkit-calendar-picker-indicator {
                    opacity: 0;
                    position: absolute;
                    inset: 0;
                    cursor: pointer;
                    z-index: 2;
                }
                #timerEndInput { position: relative; }
            </style>
            
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                <div class="d-flex align-items-start align-items-sm-center gap-3 w-100">
                    <div style="width:52px;height:52px;background:rgba(139,46,46,0.08);border-radius:14px;display:flex;align-items:center;justify-content:center;border: 1px solid rgba(139,46,46,0.1);flex-shrink:0;">
                        <i class="bi bi-clock-history" style="font-size:1.5rem;color:#8B2E2E;"></i>
                    </div>
                    <div style="min-width: 0;">
                        <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                            <h5 class="fw-bold mb-0 text-truncate" style="color:#4A1D1D;">Festival Countdown Timer</h5>
                            <span id="timerStatusBadge" class="badge rounded-pill px-3 py-1" style="background:#F3F4F6;color:#6B7280;font-size:0.75rem;">Loading…</span>
                        </div>
                        <p class="text-muted small mb-0" style="word-wrap: break-word; white-space: normal;">Manage the live countdown timer shown on the storefront.</p>
                    </div>
                </div>
            </div>

            <div class="row g-3 align-items-end mb-3">
                <div class="col-md-5">
                    <label class="form-label mb-2" style="font-weight:600;color:#444;font-size:0.85rem;" onclick="document.getElementById('timerEndInput').showPicker()">
                        <i class="bi bi-calendar-check me-1"></i> Set New End Date & Time
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-white" style="border-radius: 10px 0 0 10px; border-color: #E2D8CE; cursor: pointer;" onclick="document.getElementById('timerEndInput').showPicker()">
                            <i class="bi bi-calendar3"></i>
                        </span>
                        <input type="datetime-local" id="timerEndInput" class="form-control"
                               style="border-radius:0 10px 10px 0;border:1px solid #E2D8CE;padding:12px;border-left:none;">
                    </div>
                </div>
                <div class="col-md-auto">
                    <button class="btn btn-maroon px-4 shadow-sm" style="height: 46px;" id="setTimerBtn" onclick="setFestivalTimer()">
                        <i class="bi bi-play-fill me-1"></i> Set Timer
                    </button>
                </div>
                <div class="col-md-auto">
                    <button class="btn btn-outline-danger px-4" style="height: 46px;" id="stopTimerBtn" onclick="stopFestivalTimer()">
                        <i class="bi bi-stop-fill me-1"></i> Stop Timer
                    </button>
                </div>
            </div>

            <div id="timerCurrentDisplay" class="p-3 rounded-3 small"
                 style="background:#FFFBF7;border:1px solid #F0E6DD;color:#555;display:none;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle text-muted"></i>
                    <span>Currently active end date: <strong id="timerCurrentVal" style="color: #4A1D1D;"></strong></span>
                </div>
            </div>
        </div>

        <!-- Coupons Table -->
        <div class="">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Coupon Code</th>
                        <th class="d-none d-lg-table-cell">Offer Type</th>
                        <th>Value</th>
                        <th class="d-none d-lg-table-cell">Usage</th>
                        <th>Expiry</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $c): 
                        $isExpired = $c['expires_at'] && strtotime($c['expires_at']) < time();
                        $statusBadge = ($c['is_active'] && !$isExpired) ? 'badge-active' : 'badge-expired';
                        $statusText = $isExpired ? 'Expired' : ($c['is_active'] ? 'Active' : 'Inactive');
                    ?>
                    <tr>
                        <td data-label="Coupon Code"><span class="coupon-code-tag"><?php echo $c['code']; ?></span></td>
                        <td class="text-capitalize d-none d-lg-table-cell" data-label="Offer Type"><?php echo $c['type']; ?> Discount</td>
                        <td class="fw-bold" data-label="Value"><?php echo $c['type'] === 'percentage' ? $c['value'].'%' : '₹'.$c['value']; ?></td>
                        <td class="d-none d-lg-table-cell" data-label="Usage"><span class="text-muted"><?php echo $c['usage_limit']; ?> Max</span></td>
                        <td data-label="Expiry"><?php echo $c['expires_at'] ? date('M d, Y', strtotime($c['expires_at'])) : 'No Expiry'; ?></td>
                        <td data-label="Status"><span class="<?php echo $statusBadge; ?>"><?php echo $statusText; ?></span></td>
                        <td class="text-end" data-label="">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="offer-details.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick='openEditModal(<?php echo json_encode($c); ?>)'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="deleteCoupon(<?php echo $c['id']; ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        </div><!-- /.table-card -->

    </div><!-- /.content-body -->
</div><!-- /.main-content -->

<!-- Offer Modal (Create/Edit) -->
<div class="modal fade" id="offerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle" style="color: var(--primary-maroon);">Create New Coupon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="offerForm">
                    <input type="hidden" name="id" id="couponId">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Coupon Code*</label>
                            <input type="text" name="code" id="couponCode" class="form-control" placeholder="e.g. DIWALI20" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Offer Type*</label>
                            <select name="type" id="couponType" class="form-select" required>
                                <option value="percentage">Percentage Discount</option>
                                <option value="fixed">Fixed Amount Discount</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Discount Value*</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-tag"></i></span>
                                <input type="number" name="value" id="couponValue" class="form-control border-start-0" placeholder="e.g. 20" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Min. Cart Total (₹)</label>
                            <input type="number" name="min_cart_total" id="couponMinTotal" class="form-control" placeholder="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Usage Limit</label>
                            <input type="number" name="usage_limit" id="couponLimit" class="form-control" placeholder="500">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" name="expires_at" id="couponExpiry" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Offer Description</label>
                            <textarea name="description" id="couponDesc" class="form-control" rows="2" placeholder="Describe this offer..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer p-4 border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-maroon px-5 shadow" id="saveBtn" onclick="saveOffer()">Create Offer</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    /* ── Festival Timer Controls ─────────────────────────────────────────── */
    const TIMER_API = 'api/v1/festival-timer.php';

    async function loadTimerStatus() {
        try {
            const res  = await fetch(TIMER_API);
            const data = await res.json();
            if (!data.success) return;

            const badge   = document.getElementById('timerStatusBadge');
            const display = document.getElementById('timerCurrentDisplay');
            const valEl   = document.getElementById('timerCurrentVal');

            if (data.timer_end) {
                const end = new Date(data.timer_end);
                const now = new Date();
                const active = end > now;

                badge.textContent = active ? '🟢 Active' : '🔴 Expired';
                badge.style.background = active ? '#DCFCE7' : '#FEE2E2';
                badge.style.color      = active ? '#166534' : '#991B1B';

                valEl.textContent = end.toLocaleString('en-IN', { dateStyle: 'medium', timeStyle: 'short' });
                display.style.display = '';

                // Pre-fill input with current end time
                const iso = new Date(end.getTime() - end.getTimezoneOffset() * 60000)
                                .toISOString().slice(0, 16);
                document.getElementById('timerEndInput').value = iso;
            } else {
                badge.textContent      = '⚪ Not Set';
                badge.style.background = '#F3F4F6';
                badge.style.color      = '#6B7280';
                display.style.display  = 'none';
            }
        } catch (e) {
            console.error('Timer load failed', e);
        }
    }

    async function setFestivalTimer() {
        const val = document.getElementById('timerEndInput').value;
        if (!val) { alert('Please select an end date and time.'); return; }

        const btn = document.getElementById('setTimerBtn');
        btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Saving…';

        try {
            const res  = await fetch(TIMER_API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'set', timer_end: val })
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ title: 'Timer Updated!', text: data.message, icon: 'success', confirmButtonColor: '#8B2E2E' });
                loadTimerStatus();
            } else {
                Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#8B2E2E' });
            }
        } catch (e) {
            alert('Network error. Please try again.');
        } finally {
            btn.disabled = false; btn.innerHTML = '<i class="bi bi-play-fill me-1"></i> Set Timer';
        }
    }

    async function stopFestivalTimer() {
        const confirmed = await Swal.fire({
            title: 'Stop Festival Timer?',
            text: 'The countdown on the homepage will show "Offer Expired!" immediately.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#8B2E2E',
            confirmButtonText: 'Yes, Stop It'
        });
        if (!confirmed.isConfirmed) return;

        const btn = document.getElementById('stopTimerBtn');
        btn.disabled = true;

        try {
            const res  = await fetch(TIMER_API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'stop' })
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({ title: 'Timer Stopped!', text: data.message, icon: 'success', confirmButtonColor: '#8B2E2E' });
                loadTimerStatus();
            } else {
                Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonColor: '#8B2E2E' });
            }
        } catch (e) {
            alert('Network error. Please try again.');
        } finally {
            btn.disabled = false;
        }
    }

    document.addEventListener('DOMContentLoaded', loadTimerStatus);

    let offerModal;
    document.addEventListener('DOMContentLoaded', () => {
        offerModal = new bootstrap.Modal(document.getElementById('offerModal'));
    });

    function openCreateModal() {
        document.getElementById('modalTitle').textContent = 'Create New Coupon';
        document.getElementById('saveBtn').textContent = 'Create Offer';
        document.getElementById('offerForm').reset();
        document.getElementById('couponId').value = '';
        document.getElementById('couponCode').disabled = false;
        offerModal.show();
    }

    function openEditModal(coupon) {
        document.getElementById('modalTitle').textContent = 'Edit Coupon: ' + coupon.code;
        document.getElementById('saveBtn').textContent = 'Update Offer';
        
        document.getElementById('couponId').value = coupon.id;
        document.getElementById('couponCode').value = coupon.code;
        document.getElementById('couponCode').disabled = true; // Code usually shouldn't change
        document.getElementById('couponType').value = coupon.type;
        document.getElementById('couponValue').value = coupon.value;
        document.getElementById('couponMinTotal').value = coupon.min_cart_total;
        document.getElementById('couponLimit').value = coupon.usage_limit;
        document.getElementById('couponDesc').value = coupon.description;
        
        if (coupon.expires_at) {
            document.getElementById('couponExpiry').value = coupon.expires_at.split(' ')[0];
        } else {
            document.getElementById('couponExpiry').value = '';
        }
        
        offerModal.show();
    }

    async function saveOffer() {
        const form = document.getElementById('offerForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        const id = document.getElementById('couponId').value;
        
        if (!data.code || !data.value) {
            alert('Please fill in all required fields');
            return;
        }

        const method = id ? 'PUT' : 'POST';
        const action = id ? 'update' : 'create';

        try {
            const response = await fetch('api/v1/coupons.php', {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({...data, id: id, action: action})
            });
            const result = await response.json();
            
            if (result.success) {
                alert(id ? 'Coupon updated successfully!' : 'Coupon created successfully!');
                location.reload();
            } else {
                alert(result.error || 'Operation failed');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Network error occurred');
        }
    }

    async function deleteCoupon(id) {
        if (!confirm('Are you sure you want to delete this coupon?')) return;
        
        try {
            const response = await fetch('api/v1/coupons.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                alert(result.error);
            }
        } catch (e) {
            console.error(e);
        }
    }
</script>

<?php require_once 'includes/footer.php'; ?>
</body>
</html>
