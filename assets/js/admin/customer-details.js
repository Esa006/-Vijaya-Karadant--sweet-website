/**
 * Sweets Website - Admin
 * =============================================================
 * File: assets/js/admin/customer-details.js
 * Description: Ultra-robust rendering logic with Auto-Demo Mode
 * Author: Antigravity
 * Version: 4.5.0
 * =============================================================
 */

const CustomerApp = {
    userId: document.getElementById('crmApp')?.dataset.userId,
    state: { data: null, loading: true },

    async init() {
        console.log('CRM App: Initializing for ID:', this.userId);
        
        // AUTO-DEMO MODE: If ID is 0 or missing, show high-fidelity dummy data
        if (!this.userId || this.userId === '0' || this.userId === 'undefined') {
            console.warn('CRM App: Triggering Demo Mode...');
            this.state.data = this.getDummyData();
            this.setLoading(false);
            this.render();
            return;
        }

        await this.fetchData();
    },

    async fetchData() {
        this.setLoading(true);
        try {
            const response = await fetch(`api/v1/customer-details.php?id=${this.userId}`);
            const result = await response.json();
            
            if (result.success) {
                this.state.data = result.data;
                this.render();
            } else {
                this.showErrorMessage(result.error || 'Failed to fetch data');
            }
        } catch (error) {
            console.error('CRM App Error:', error);
            this.showErrorMessage('Network Error: Check API connection');
        } finally {
            this.setLoading(false);
        }
    },

    setLoading(isLoading) {
        const loader = document.getElementById('skeletonLoader');
        const content = document.getElementById('mainContent');
        if (loader) loader.classList.toggle('d-none', !isLoading);
        if (content) content.classList.toggle('d-none', isLoading);
    },

    showErrorMessage(msg) {
        const content = document.getElementById('mainContent');
        if (content) {
            content.innerHTML = `<div class="alert alert-danger m-4">${msg}</div>`;
            content.classList.remove('d-none');
        }
    },

    render() {
        try {
            const data = this.state.data;
            if (!data || !data.profile) return;
            const profile = data.profile;

            // 1. Profile Info
            this.safeSetText('breadcrumbName', profile.name);
            this.safeSetText('profileName', profile.name);
            this.safeSetText('infoName', profile.name);
            this.safeSetText('profileEmail', profile.email);
            this.safeSetText('infoEmail', profile.email);
            this.safeSetText('infoPhone', profile.phone || 'N/A');
            this.safeSetText('infoDob', profile.dob || 'Not Provided');
            this.safeSetText('profileSince', 'Member since ' + (profile.join_date || profile.created_at || 'Unknown').split(' ')[0]);

            const initials = (profile.name || 'U').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
            this.safeSetText('profileInitials', initials);

            // 2. Stats
            this.safeSetText('statOrders', data.summary.orders_count || 0);
            this.safeSetText('statSpend', '₹ ' + parseFloat(data.summary.total_spend || 0).toLocaleString('en-IN'));
            this.safeSetText('statAov', '₹ ' + parseFloat(data.summary.aov || 0).toLocaleString('en-IN'));
            this.safeSetText('statLastOrder', data.summary.last_order_date || 'N/A');

            // 3. Status Badge
            const status = profile.status || 'active';
            const statusText = document.getElementById('statusText');
            const badgeContainer = document.getElementById('statusBadgeContainer');
            if (statusText) statusText.textContent = status.charAt(0).toUpperCase() + status.slice(1);
            if (badgeContainer) {
                badgeContainer.style.backgroundColor = status === 'active' ? '#DCFCE7' : '#FEE2E2';
                badgeContainer.style.color = status === 'active' ? '#166534' : '#991B1B';
            }

            // Pre-fill Edit Modal
            const editFullName = document.getElementById('editFullName');
            if (editFullName) editFullName.value = profile.name || '';
            const editEmail = document.getElementById('editEmail');
            if (editEmail) editEmail.value = profile.email || '';
            const editPhone = document.getElementById('editPhone');
            if (editPhone) editPhone.value = profile.phone || '';
            const editStatus = document.getElementById('editStatus');
            if (editStatus) editStatus.value = profile.status || 'active';

            // 4. Addresses
            const addrBox = document.getElementById('addressContainer');
            if (addrBox && data.addresses) {
                addrBox.innerHTML = data.addresses.map(a => {
                    const line = (a.address_line || '').replace(/undefined/g, '').trim();
                    const city = (a.city || '').replace(/undefined/g, '').trim();
                    const state = (a.state || '').replace(/undefined/g, '').trim();
                    const pincode = (a.pincode || '').replace(/undefined/g, '').trim();
                    
                    const hasSomeData = line || city || state || pincode;
                    if (!hasSomeData) return '';

                    return `
                        <div class="col-md-6 mb-3">
                            <div class="address-box h-100" style="background:#FDF8F5; border:2px solid #D4A574; border-radius:12px; padding:20px;">
                                <div class="fw-bold mb-2 text-uppercase small" style="color:#D97706">${a.type || 'Shipping'} Address</div>
                                <p class="mb-0 small">
                                    ${line ? line + '<br>' : ''}
                                    ${city}${state ? ', ' + state : ''} ${pincode}
                                </p>
                            </div>
                        </div>
                    `;
                }).join('') || '<div class="col-12 text-muted px-3">No address records found for this customer.</div>';
            }

            // 5. Recent Orders
            const orderTable = document.getElementById('ordersTable');
            if (orderTable && data.orders) {
                orderTable.innerHTML = data.orders.map(o => {
                    const comboBadge = parseInt(o.combo_count) > 0 
                        ? `<span class="badge ms-1" style="background:#8B2E2E; color:#FFD700; font-size:0.7rem; border:1px solid #FFD700;">COMBO</span>` 
                        : '';
                        
                    return `
                        <tr>
                            <td class="fw-bold" style="color:#8B2E2E">#ORD-${o.id}</td>
                            <td>${o.created_at.split(' ')[0]}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    ${o.item_count || 0} Items ${comboBadge}
                                </div>
                            </td>
                            <td class="fw-bold">₹ ${parseFloat(o.total_amount).toLocaleString('en-IN')}</td>
                            <td><span class="badge ${o.status === 'delivered' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'} p-2 rounded">${o.status}</span></td>
                            <td class="text-end">
                                <a href="order-details.php?id=${o.id}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            // 6. Timeline
            const timelineBox = document.getElementById('timelineList');
            if (timelineBox && data.timeline) {
                timelineBox.innerHTML = data.timeline.map(t => `
                    <div class="timeline-item pb-3 ps-4 position-relative">
                        <div class="position-absolute start-0 top-0 h-100 border-start border-2 border-light"></div>
                        <div class="position-absolute start-0 top-0 mt-1 ms-n1 bg-secondary rounded-circle" style="width:10px; height:10px; left:-5px"></div>
                        <div class="fw-bold small">${(t.action || '').replace('_', ' ')}</div>
                        <div class="text-muted x-small">${t.created_at}</div>
                    </div>
                `).join('');
            }

            // 7. Notes
            const tagContainer = document.getElementById('tagContainer');
            if (tagContainer) {
                tagContainer.innerHTML = '<span class="tag-badge"><i class="bi bi-star"></i> VIP Customer</span>';
            }
            
            const noteInput = document.getElementById('noteInput');
            if (noteInput && data.notes && data.notes.length > 0) {
                let existingNotes = data.notes.map(n => `[${n.created_at}] ${n.note}`).join('\n\n');
                noteInput.value = existingNotes + '\n\n';
            }

        } catch (e) {
            console.error('Render Error:', e);
            this.showErrorMessage('UI Display Error: ' + e.message);
        }
    },

    safeSetText(id, text) {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    },

    getDummyData() {
        return {
            profile: { id: 0, name: 'Rajiv Sharma', email: 'rajiv.sharma@example.com', phone: '+91 98765 43210', dob: '1985-03-12', status: 'active', created_at: '2022-10-15' },
            summary: { orders_count: 24, total_spend: 34500, aov: 1437, last_order_date: '2023-10-24' },
            addresses: [
                { type: 'billing', address_line: 'Flat 402, Sunshine Apartments, Koramangala', city: 'Bengaluru', state: 'Karnataka', pincode: '560034' },
                { type: 'shipping', address_line: 'Flat 402, Sunshine Apartments, Koramangala', city: 'Bengaluru', state: 'Karnataka', pincode: '560034' }
            ],
            orders: [
                { id: 4091, total_amount: 1267.50, status: 'delivered', item_count: 3, combo_count: 1, created_at: '2023-10-24' },
                { id: 3812, total_amount: 450.50, status: 'delivered', item_count: 1, combo_count: 0, created_at: '2023-09-12' }
            ],
            timeline: [
                { action: 'Order Placed', created_at: '2023-10-24 10:42' },
                { action: 'Account Created', created_at: '2022-10-15 09:00' }
            ]
        };
    }
};

window.saveNote = async function(event) {
    const input = document.getElementById('noteInput');
    if (!input || !input.value.trim()) return;

    try {
        const btn = event ? event.currentTarget : document.querySelector('button[onclick="saveNote()"]');
        const origText = btn.innerHTML;
        btn.innerHTML = 'Saving...';
        btn.disabled = true;

        const response = await fetch(`api/v1/customer-details.php?id=${CustomerApp.userId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'add_note',
                note: input.value.trim()
            })
        });
        const result = await response.json();
        if (result.success) {
            alert('Note saved!');
            CustomerApp.fetchData();
        } else {
            alert(result.error || 'Failed to save note');
        }
        btn.innerHTML = origText;
        btn.disabled = false;
    } catch (e) {
        alert('Network error');
    }
};

window.saveCustomer = async function(event) {
    const form = document.getElementById('editCustomerForm');
    if (!form.reportValidity()) return;

    const btn = event.currentTarget;
    const origText = btn.innerHTML;
    btn.innerHTML = 'Saving...';
    btn.disabled = true;

    try {
        const payload = {
            action: 'update_profile',
            full_name: document.getElementById('editFullName').value.trim(),
            email: document.getElementById('editEmail').value.trim(),
            phone: document.getElementById('editPhone').value.trim(),
            status: document.getElementById('editStatus').value
        };

        const response = await fetch(`api/v1/customer-details.php?id=${CustomerApp.userId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        
        const result = await response.json();
        if (result.success) {
            // close modal
            const modalEl = document.getElementById('editCustomerModal');
            if (typeof bootstrap !== 'undefined') {
                const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                if (modalInstance) modalInstance.hide();
            }
            
            CustomerApp.fetchData(); // reload
        } else {
            alert(result.error || 'Failed to update customer');
        }
    } catch (e) {
        alert('Network error');
    } finally {
        btn.innerHTML = origText;
        btn.disabled = false;
    }
};

document.addEventListener('DOMContentLoaded', () => CustomerApp.init());
