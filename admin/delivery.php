<?php
/**
 * Sweets Website
 * =============================================================
 * File: delivery.php
 * Description: Admin shipment tracking dashboard
 * =============================================================
 */

$pageStyles = [];

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div class="main-content products-page">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-white p-2 rounded-3 shadow-sm" style="width: 60px; height: 60px;">
                    <img src="<?php echo BASE_URL; ?>assets/images/admin/shipment-tracking.png" alt="Shipment Tracking" class="img-fluid">
                </div>
                <div>
                    <h1 class="mb-0" style="font-size:1.75rem; color:#7B1F1F;">Shipment Tracking</h1>
                    <p class="text-muted mb-0">Manage delivery status with real-time updates.</p>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4" id="countCards">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Shipments</p>
                        <h3 class="mb-0" id="countTotal">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Pending</p>
                        <h3 class="mb-0 text-warning" id="countPending">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">In Transit</p>
                        <h3 class="mb-0 text-primary" id="countTransit">0</h3>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Delivered</p>
                        <h3 class="mb-0 text-success" id="countDelivered">0</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3 align-items-center mb-3">
                    <div class="col-12 col-md-6 col-lg-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                </svg>
                            </span>
                            <input
                                type="text"
                                id="searchInput"
                                class="form-control"
                                placeholder="Search by order reference or customer"
                            >
                        </div>
                    </div>
                </div>

                <div id="loadingState" class="text-center py-5">
                    <div class="spinner-border" role="status" aria-hidden="true"></div>
                    <p class="text-muted mt-3 mb-0">Loading shipments...</p>
                </div>

                <div id="tableWrap" class="table-responsive d-none">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Order Reference</th>
                                <th>Customer</th>
                                <th>Destination</th>
                                <th>Value</th>
                                <th>Status</th>
                                <th>Updated time</th>
                            </tr>
                        </thead>
                        <tbody id="shipmentsTableBody"></tbody>
                    </table>
                </div>

                <div id="errorState" class="alert alert-danger d-none mt-3 mb-0" role="alert"></div>
            </div>
        </div>
    </div>
</div>

<style>
    .status-pill {
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.6rem;
        display: inline-flex;
        align-items: center;
        text-transform: capitalize;
    }
    .status-pending {
        background-color: #fff3cd !important;
        color: #b36b00 !important;
        border-color: #ffe69c !important;
    }
    .status-in-transit {
        background-color: #e7f1ff !important;
        color: #0d6efd !important;
        border-color: #b6d4fe !important;
    }
    .status-delivered {
        background-color: #e8f7ed !important;
        color: #198754 !important;
        border-color: #badbcc !important;
    }
    .status-select {
        min-width: 132px;
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .status-select:focus {
        box-shadow: 0 0 0 0.25rem rgba(0,0,0,0.1);
    }
</style>

<script>
    const statusMap = {
        pending: 'Pending',
        in_transit: 'In Transit',
        delivered: 'Delivered'
    };

    let shipments = [];
    let filteredShipments = [];

    const tableBodyEl = document.getElementById('shipmentsTableBody');
    const loadingStateEl = document.getElementById('loadingState');
    const tableWrapEl = document.getElementById('tableWrap');
    const errorStateEl = document.getElementById('errorState');
    const searchInputEl = document.getElementById('searchInput');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    document.addEventListener('DOMContentLoaded', () => {
        bindEvents();
        loadDashboard();
    });

    function bindEvents() {
        searchInputEl.addEventListener('input', filterAndRenderRows);
    }

    async function loadDashboard() {
        showLoading(true);
        showError('');

        try {
            const [shipmentsRes, countsRes] = await Promise.all([
                fetch('get_shipments.php', { headers: { 'Accept': 'application/json' } }),
                fetch('dashboard_counts.php', { headers: { 'Accept': 'application/json' } })
            ]);

            const shipmentsJson = await shipmentsRes.json();
            const countsJson = await countsRes.json();

            if (!shipmentsRes.ok || !shipmentsJson.success) {
                throw new Error(shipmentsJson.message || 'Unable to load shipment rows');
            }
            if (!countsRes.ok || !countsJson.success) {
                throw new Error(countsJson.message || 'Unable to load counts');
            }

            shipments = shipmentsJson.data.map((item) => ({
                order_id: Number(item.order_id),
                order_reference: String(item.order_reference || '').trim() || `ORD-${Number(item.order_id || 0)}`,
                customer_name: String(item.customer_name || 'Guest'),
                destination: String(item.destination || '').trim() || 'N/A',
                total_amount: Number(item.total_amount || 0),
                status: String(item.status || 'pending').toLowerCase(),
                updated_at: item.updated_at || item.created_at || null
            }));

            filteredShipments = [...shipments];
            renderRows();
            renderCounts(countsJson.data);
        } catch (error) {
            showError(error.message || 'Unexpected error while loading dashboard');
            renderRows();
            renderCounts({ total: 0, pending: 0, in_transit: 0, delivered: 0 });
        } finally {
            showLoading(false);
        }
    }

    function showLoading(isLoading) {
        loadingStateEl.classList.toggle('d-none', !isLoading);
        tableWrapEl.classList.toggle('d-none', isLoading);
    }

    function showError(message) {
        if (!message) {
            errorStateEl.classList.add('d-none');
            errorStateEl.textContent = '';
            return;
        }
        errorStateEl.classList.remove('d-none');
        errorStateEl.textContent = message;
    }

    function filterAndRenderRows() {
        const searchText = searchInputEl.value.trim().toLowerCase();

        filteredShipments = shipments.filter((item) => {
            if (!searchText) {
                return true;
            }

            const orderReference = item.order_reference.toLowerCase();
            const customer = item.customer_name.toLowerCase();
            return orderReference.includes(searchText) || customer.includes(searchText);
        });

        renderRows();
    }

    function renderRows() {
        if (filteredShipments.length === 0) {
            tableBodyEl.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">No data</td>
                </tr>
            `;
            return;
        }

        tableBodyEl.innerHTML = filteredShipments.map((item) => {
            const statusClass = getStatusClass(item.status);
            const statusLabel = statusMap[item.status] || item.status;
            const orderRef = String(item.order_reference || '').trim() || `ORD-${item.order_id}`;

            return `
                <tr data-order-id="${item.order_id}">
                    <td>
                        <a href="delivery-details.php?id=${item.order_id}" class="text-decoration-none d-flex align-items-center gap-2">
                            <span class="fw-semibold text-primary">${escapeHtml(orderRef)}</span>
                            <i class="bi bi-box-arrow-up-right small"></i>
                        </a>
                    </td>
                    <td>${escapeHtml(item.customer_name)}</td>
                    <td>${escapeHtml(item.destination || 'N/A')}</td>
                    <td>INR ${formatAmount(item.total_amount)}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <select class="form-select form-select-sm status-select ${statusClass}" id="select-${item.order_id}" data-order-id="${item.order_id}" onchange="handleStatusChange(event)">
                                ${renderStatusOptions(item.status)}
                            </select>
                        </div>
                    </td>
                    <td id="updated-${item.order_id}">${formatDateTime(item.updated_at)}</td>
                </tr>
            `;
        }).join('');
    }

    function renderStatusOptions(selectedStatus) {
        return Object.keys(statusMap).map((key) => {
            const selected = key === selectedStatus ? 'selected' : '';
            return `<option value="${key}" ${selected}>${statusMap[key]}</option>`;
        }).join('');
    }

    async function handleStatusChange(event) {
        const selectEl = event.target;
        const orderId = Number(selectEl.dataset.orderId || 0);
        const nextStatus = selectEl.value;

        if (!orderId || !statusMap[nextStatus]) {
            showError('Invalid status selection');
            return;
        }

        const shipment = shipments.find((item) => item.order_id === orderId);
        if (!shipment) {
            showError('Shipment row not found');
            return;
        }

        const previousStatus = shipment.status;
        const previousUpdatedAt = shipment.updated_at;
        if (previousStatus === nextStatus) {
            return;
        }

        shipment.status = nextStatus;
        shipment.updated_at = new Date().toISOString().slice(0, 19).replace('T', ' ');
        updateRowUI(orderId, nextStatus, shipment.updated_at);
        renderCountsFromLocalRows();

        selectEl.disabled = true;
        showError('');

        try {
            const payload = new URLSearchParams();
            payload.append('order_id', String(orderId));
            payload.append('status', nextStatus);

            const response = await fetch('update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                    'X-CSRF-Token': csrfToken,
                    'Accept': 'application/json'
                },
                body: payload.toString()
            });

            const json = await response.json();
            if (!response.ok || !json.success) {
                throw new Error(json.message || 'Failed to update status');
            }

            shipment.updated_at = json.data?.updated_at || shipment.updated_at;
            updateRowUI(orderId, nextStatus, shipment.updated_at);
        } catch (error) {
            shipment.status = previousStatus;
            shipment.updated_at = previousUpdatedAt;
            updateRowUI(orderId, previousStatus, shipment.updated_at);
            renderCountsFromLocalRows();
            selectEl.value = previousStatus;
            showError(error.message || 'Could not update status');
        } finally {
            selectEl.disabled = false;
        }
    }

    function updateRowUI(orderId, status, updatedAt) {
        const selectEl = document.getElementById(`select-${orderId}`);
        const updatedEl = document.getElementById(`updated-${orderId}`);

        if (selectEl) {
            // Remove previous status classes
            selectEl.classList.remove('status-pending', 'status-in-transit', 'status-delivered');
            // Add new status class
            selectEl.classList.add(getStatusClass(status));
            selectEl.value = status;
        }

        if (updatedEl) {
            updatedEl.textContent = formatDateTime(updatedAt);
        }
    }

    function getStatusClass(status) {
        if (status === 'pending') {
            return 'status-pending';
        }
        if (status === 'in_transit') {
            return 'status-in-transit';
        }
        if (status === 'delivered') {
            return 'status-delivered';
        }
        return 'status-pending';
    }

    function renderCounts(counts) {
        document.getElementById('countTotal').textContent = Number(counts.total || 0);
        document.getElementById('countPending').textContent = Number(counts.pending || 0);
        document.getElementById('countTransit').textContent = Number(counts.in_transit || 0);
        document.getElementById('countDelivered').textContent = Number(counts.delivered || 0);
    }

    function renderCountsFromLocalRows() {
        const counts = {
            total: shipments.length,
            pending: 0,
            in_transit: 0,
            delivered: 0
        };

        shipments.forEach((item) => {
            if (counts[item.status] !== undefined) {
                counts[item.status] += 1;
            }
        });

        renderCounts(counts);
    }

    function formatAmount(amount) {
        return Number(amount || 0).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function formatDateTime(dateTimeValue) {
        if (!dateTimeValue) {
            return 'N/A';
        }

        const dt = new Date(dateTimeValue.replace(' ', 'T'));
        if (Number.isNaN(dt.getTime())) {
            return 'N/A';
        }

        return dt.toLocaleString('en-IN', {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>

<?php require_once 'includes/footer.php'; ?>
