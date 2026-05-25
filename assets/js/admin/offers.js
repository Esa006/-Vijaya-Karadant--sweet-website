/**
 * Sweets Website
 * =============================================================
 * File: offers.js
 * Description: Business logic for Offers & Coupons.
 * Handling table rendering, filtering, pagination, and simulation CRUD.
 * =============================================================
 */

document.addEventListener('DOMContentLoaded', function() {
    // --- State Variables ---
    let offersData = [];

    const ITEMS_PER_PAGE = 8;
    let currentPage = 1;
    let totalItems = 0;
    let currentFilter = 'all';
    let deleteTargetId = null;

    init();

    async function init() {
        await fetchOffers();
        setupEventListeners();
    }

    async function fetchOffers() {
        try {
            const res = await fetch('api/v1/coupons.php');
            const result = await res.json();
            if (result.success) {
                offersData = result.data.map(mapCouponToOffer);
                totalItems = offersData.length;
                renderTable(offersData);
                renderPagination();
            } else {
                showOfferToast(result.error || 'Failed to load offers', true);
            }
        } catch (err) {
            console.error('Fetch error:', err);
            showOfferToast('System error loading offers', true);
        }
    }

    function mapCouponToOffer(c) {
        const now = new Date();
        const expires = c.expires_at ? new Date(c.expires_at) : null;
        
        let status = c.is_active ? 'Active' : 'Expired';
        if (expires && expires < now) {
            status = 'Expired';
        }

        const discountLabel = c.type === 'percentage' ? `${parseFloat(c.value)}% Off` : `Flat ₹${parseFloat(c.value)}`;
        const typeLabel = c.type === 'percentage' ? 'Percentage' : 'Fixed Amount';
        
        const colors = ['type-fill-green', 'type-fill-maroon', 'type-fill-orange', 'type-fill-red', 'type-fill-brown'];
        const progressColor = colors[c.id % colors.length];

        return {
            id: c.id,
            name: c.code,
            type: typeLabel,
            discount: discountLabel,
            minOrder: parseFloat(c.min_cart_total) || 0,
            usageUsed: parseInt(c.usage_used) || 0,
            usageLimit: parseInt(c.usage_limit) || 0,
            startDate: new Date(c.created_at).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }),
            endDate: expires ? expires.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : 'No expiry',
            status: status,
            progressColor: progressColor,
            progressWidth: Math.floor(Math.random() * 40) + 40 // Dummy for visual flair
        };
    }

    function setupEventListeners() {
        // Search
        const searchInput = document.getElementById('offerSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                filterAndRender();
            });
        }

        // Select All
        const selectAll = document.getElementById('selectAllOffers');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
            });
        }

        // Save Offer
        const saveBtn = document.getElementById('saveOfferBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', handleSaveOffer);
        }

        // Delete Confirm
        const confirmDelete = document.getElementById('confirmDeleteBtn');
        if (confirmDelete) {
            confirmDelete.addEventListener('click', handleDeleteConfirm);
        }
    }

    function renderTable(data) {
        const tbody = document.getElementById('offersTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';

        data.forEach(offer => {
            const usagePercent = offer.usageLimit > 0 ? Math.min((offer.usageUsed / offer.usageLimit) * 100, 100) : 0;
            let usageFillClass = 'fill-green';
            if (usagePercent >= 100) usageFillClass = 'fill-full';
            else if (usagePercent >= 70) usageFillClass = 'fill-orange';
            else if (usagePercent >= 90) usageFillClass = 'fill-red';

            let statusClass = 'status-pill-active';
            if (offer.status === 'Scheduled') statusClass = 'status-pill-scheduled';
            else if (offer.status === 'Expired') statusClass = 'status-pill-expired';

            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="checkbox" class="offers-custom-checkbox row-checkbox" data-id="${offer.id}"></td>
                <td>
                    <a href="offer-details.php?id=${offer.id}" class="fw-bold text-dark text-decoration-underline" style="font-size:13px;">${offer.name}</a>
                    <div class="text-muted" style="font-size:10px;">${offer.type}</div>
                    <div class="offer-type-progress-track">
                        <div class="offer-type-progress-fill ${offer.progressColor}" style="width:${offer.progressWidth}%"></div>
                    </div>
                </td>
                <td>
                    <div class="fw-bold text-dark">${offer.discount}</div>
                    <div class="text-muted" style="font-size:10px;">${offer.type}</div>
                </td>
                <td>
                    <div class="fw-bold">₹ ${offer.minOrder.toLocaleString('en-IN')}</div>
                </td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted">${offer.usageUsed}</span>
                        <div class="offer-usage-bar-track">
                            <div class="offer-usage-bar-fill ${usageFillClass}" style="width:${usagePercent}%"></div>
                        </div>
                        <span class="small text-muted">${offer.usageLimit}</span>
                    </div>
                </td>
                <td>
                    <div style="font-size:12px;">${offer.startDate}</div>
                    <div class="text-muted" style="font-size:10px;">to ${offer.endDate}</div>
                </td>
                <td>
                    <span class="status-pill-badge ${statusClass}">${offer.status}</span>
                </td>
                <td>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-light p-1" title="Edit" onclick="editOffer(${offer.id})"><i class="bi bi-pencil-fill"></i></button>
                        <button class="btn btn-sm btn-light p-1" title="Duplicate" onclick="duplicateOffer(${offer.id})"><i class="bi bi-copy"></i></button>
                        <button type="button" class="btn btn-sm btn-light p-1" title="View" onclick='viewOffer(${offer.id})'><i class="bi bi-eye"></i></button>
                        <button type="button" class="btn btn-sm btn-light p-1 text-danger" title="Delete" onclick='openOfferDelete(${offer.id})'><i class="bi bi-trash3"></i></button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    function renderPagination() {
        const controls = document.getElementById('paginationControls');
        const info = document.getElementById('paginationInfo');
        if (!controls || !info) return;

        const totalPages = Math.ceil(totalItems / ITEMS_PER_PAGE);
        const startItem = (currentPage - 1) * ITEMS_PER_PAGE + 1;
        const endItem = Math.min(currentPage * ITEMS_PER_PAGE, totalItems);
        info.textContent = `Page ${currentPage} of ${totalPages} · ${startItem}–${endItem} of ${totalItems} offers`;

        let html = '';
        html += `<button class="btn btn-sm btn-light border mx-1 ${currentPage === 1 ? 'disabled' : ''}" onclick="goToOfferPage(${currentPage - 1})">Back</button>`;
        
        for (let i = 1; i <= Math.min(totalPages, 5); i++) {
            html += `<button class="btn btn-sm ${i === currentPage ? 'btn-dark' : 'btn-light border'} mx-1" onclick="goToOfferPage(${i})">${i}</button>`;
        }
        
        html += `<button class="btn btn-sm btn-light border mx-1 ${currentPage === totalPages ? 'disabled' : ''}" onclick="goToOfferPage(${currentPage + 1})">Next</button>`;
        controls.innerHTML = html;
    }

    window.goToOfferPage = function(page) {
        const totalPages = Math.ceil(totalItems / ITEMS_PER_PAGE);
        if (page < 1 || page > totalPages) return;
        currentPage = page;
        renderPagination();
        showOfferToast(`Loaded page ${page}`);
    };

    function filterAndRender() {
        const query = document.getElementById('offerSearchInput').value.toLowerCase().trim();
        let filtered = offersData;
        
        if (query !== '') {
            filtered = offersData.filter(o => 
                o.name.toLowerCase().includes(query) || 
                o.discount.toLowerCase().includes(query)
            );
        }
        
        if (currentFilter !== 'all') {
            filtered = filtered.filter(o => o.status === currentFilter);
        }
        
        renderTable(filtered);
    }

    window.setOfferStatusFilter = function(status, label) {
        currentFilter = status;
        const btn = document.getElementById('statusFilterBtn');
        if (btn) btn.innerHTML = `${label} <i class="bi bi-chevron-down ms-1" style="font-size:10px;"></i>`;
        filterAndRender();
    };

    function handleSaveOffer() {
        const name = document.getElementById('offerName').value.trim();
        const type = document.getElementById('discountType').value;
        const val  = document.getElementById('discountValue').value.trim();
        const start = document.getElementById('startDate').value;

        if (!name || !type || !val || !start) {
            showOfferToast('Please fill all required fields', true);
            return;
        }

        const newOffer = {
            id: Date.now(),
            name: name,
            type: type,
            discount: val,
            minOrder: parseInt(document.getElementById('minOrder').value) || 0,
            usageUsed: 0,
            usageLimit: parseInt(document.getElementById('usageLimit').value) || 500,
            startDate: new Date(start).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }),
            endDate: document.getElementById('endDate').value || 'No expiry',
            status: document.getElementById('offerStatus').value,
            progressColor: 'type-fill-brown',
            progressWidth: 40
        };

        offersData.unshift(newOffer);
        totalItems++;
        renderTable(offersData);
        renderPagination();
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('createOfferModal'));
        if (modal) modal.hide();
        document.getElementById('createOfferForm').reset();
        showOfferToast('Offer created successfully!');
    }

    window.openOfferDelete = function(id) {
        const offer = offersData.find(o => o.id === id);
        if (offer) {
            if (typeof AdminModals !== 'undefined' && typeof AdminModals.openDeleteModal === 'function') {
                AdminModals.openDeleteModal(offer, "offer", window.handleDeleteConfirm);
            } else {
                if(confirm('Are you sure you want to delete this offer?')) {
                    window.handleDeleteConfirm(offer);
                }
            }
        }
    };

    window.handleDeleteConfirm = async function(target) {
        const id = target.id;
        if (id !== null) {
            try {
                const res = await fetch('api/v1/coupons.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const result = await res.json();
                if (result.success) {
                    offersData = offersData.filter(o => o.id !== id);
                    totalItems--;
                    renderTable(offersData);
                    renderPagination();
                    showOfferToast('Offer deleted permanently');
                } else {
                    showOfferToast(result.error || 'Delete failed', true);
                }
            } catch (err) {
                showOfferToast('System error deleting offer', true);
            }
        }
    };

    window.editOffer = function(id) {
        window.location.href = 'edit-offer.php?id=' + id;
    };

    window.viewOffer = function(id) {
        window.location.href = 'offer-details.php?id=' + id;
    };

    window.duplicateOffer = function(id) {
        const offer = offersData.find(o => o.id === id);
        if (offer) {
            const dup = { ...offer, id: Date.now(), name: offer.name + " (Copy)" };
            offersData.push(dup);
            totalItems++;
            renderTable(offersData);
            renderPagination();
            showOfferToast('Offer duplicated');
        }
    };

    function showOfferToast(message, isError = false) {
        const toast = document.createElement('div');
        toast.style.cssText = `position: fixed; top: 100px; right: 20px; background: ${isError ? '#C62828' : '#5C2D0E'}; 
                               color: white; padding: 12px 24px; border-radius: 8px; z-index: 10000; box-shadow: 0 4px 12px rgba(0,0,0,0.15);`;
        toast.innerHTML = `<i class="bi bi-${isError ? 'exclamation-triangle' : 'check-circle'} me-2"></i> ${message}`;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.4s ease';
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    }
});
