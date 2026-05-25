/**
 * Sweets Website
 * =============================================================
 * File: dashboard.js
 * Description: Main dashboard interactivity and Sales Analytics chart
 * =============================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    initSalesChart();
    initRevenueChart();
    initCategoryChart();
});

function initSalesChart() {
    const ctx = document.getElementById('salesChart');
    if (!ctx) return;

    new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: window.dashboardChartData?.sales?.labels || ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [
                {
                    label: 'Revenue',
                    data: window.dashboardChartData?.sales?.revenue || [12000, 3000, 17000, 11000, 21000, 11000, 15000],
                    borderColor: '#7a1d1d',
                    backgroundColor: 'rgba(122,29,29,0.08)',
                    tension: 0.45,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#7a1d1d',
                    fill: true
                },
                {
                    label: 'Volume',
                    data: window.dashboardChartData?.sales?.volume || [10000, 9000, 13000, 13000, 15000, 12000, 24000],
                    borderColor: '#f59047',
                    backgroundColor: 'rgba(245,144,71,0.08)',
                    tension: 0.45,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#f59047',
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 5000,
                        callback: v => (v / 1000) + 'K'
                    },
                    grid: { color: '#f0f0f0' }
                },
                x: { grid: { display: false } }
            }
        }
    });
}

function initRevenueChart() {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;

    new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: window.dashboardChartData?.revenue?.labels || ['Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr'],
            datasets: [{
                data: window.dashboardChartData?.revenue?.data || [6000, 9000, 12000, 11000, 20000, 17000, 24000],
                borderColor: '#7a1d1d',
                backgroundColor: (context) => {
                    const c = context.chart.ctx;
                    const g = c.createLinearGradient(0, 0, 0, 300);
                    g.addColorStop(0, 'rgba(245,144,71,0.35)');
                    g.addColorStop(1, 'rgba(245,144,71,0)');
                    return g;
                },
                borderWidth: 3,
                tension: 0.45,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#f59047'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: true,
                    backgroundColor: '#222',
                    callbacks: { label: c => '₹' + (c.parsed.y / 1000 * 10).toFixed(0) + 'K' }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 5000, callback: v => (v / 1000) + 'K' },
                    grid: { color: '#f0f0f0', borderDash: [4, 4] }
                },
                x: { grid: { display: false } }
            }
        }
    });
}

function initCategoryChart() {
    const ctx = document.getElementById('categoryChart');
    if (!ctx) return;

    new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: window.dashboardChartData?.category?.labels || ['Karadant', 'Laddu', 'Namkeen', 'Gift Box'],
            datasets: [{
                data: window.dashboardChartData?.category?.data || [40, 25, 20, 15],
                backgroundColor: ['#7a1d1d', '#a23a1d', '#f59047', '#fde4cf'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: { legend: { display: false } }
        }
    });
}

// ============================================================
//  ORDERS UI CRUD FUNCTIONS
// ============================================================

const initials    = n => n.trim().split(/\s+/).map(s=>s[0]).join('').slice(0,2).toUpperCase();
const fmtAmount   = a => '₹ ' + Number(a).toLocaleString('en-IN');
const statusClass = {Available:'status-available',Pending:'status-pending',Cancelled:'status-cancelled',Delivered:'status-available',Completed:'status-available'};

function showToast(msg){
  document.getElementById('toastMsg').textContent = msg;
  const t = bootstrap.Toast.getOrCreateInstance(document.getElementById('appToast'),{delay:2200});
  t.show();
}

function openAddOrderModal(){
  document.getElementById('orderModalTitle').textContent = 'Add Order';
  document.getElementById('orderForm').reset();
  document.getElementById('orderIndex').value = -1;
  document.getElementById('custDate').value = new Date().toISOString().slice(0,10);
  new bootstrap.Modal('#orderModal').show();
}

function editOrder(orderJson){
  const o = JSON.parse(orderJson);
  document.getElementById('orderModalTitle').textContent = 'Edit Order #' + (o.order_number || o.id);
  document.getElementById('orderIndex').value = o.id;
  document.getElementById('custName').value   = o.customer_name || '';
  document.getElementById('custAmount').value = o.total_amount || 0;
  document.getElementById('custDate').value   = o.created_at ? o.created_at.substring(0,10) : '';
  document.getElementById('custStatus').value = (o.status || '').charAt(0).toUpperCase() + (o.status || '').slice(1);
  new bootstrap.Modal('#orderModal').show();
}

function viewOrder(orderJson){
  const o = JSON.parse(orderJson);
  const name = o.customer_name || 'Unknown';
  const idStr = '#' + (o.order_number || o.id);
  const st = (o.status || '').charAt(0).toUpperCase() + (o.status || '').slice(1);
  
  document.getElementById('viewBody').innerHTML = `
    <div class="d-flex align-items-center mb-3">
      <span class="avatar" style="width:48px;height:48px;font-size:1.1rem;">${initials(name)}</span>
      <div class="ms-3">
        <div class="fw-bold fs-5">${name}</div>
        <div class="text-muted small">${idStr}</div>
      </div>
    </div>
    <hr>
    <div class="row g-3">
      <div class="col-6"><div class="text-muted small">Amount</div><div class="fw-semibold">${fmtAmount(o.total_amount)}</div></div>
      <div class="col-6"><div class="text-muted small">Date</div><div class="fw-semibold">${o.created_at ? o.created_at.substring(0,10) : ''}</div></div>
      <div class="col-6"><div class="text-muted small">Status</div>
        <span class="status-pill ${statusClass[st] || 'status-pending'}">${st}</span>
      </div>
      <div class="col-6"><div class="text-muted small">Order ID</div><div class="fw-semibold">${idStr}</div></div>
    </div>`;
  new bootstrap.Modal('#viewModal').show();
}

function deleteOrder(id){
  if(!confirm(`Delete order #${id}?`)) return;
  // This would typically make an AJAX request
  showToast('Order deleted (Simulated)');
}

function saveOrder(e){
  e.preventDefault();
  // This would typically make an AJAX request
  const idx = parseInt(document.getElementById('orderIndex').value,10);
  if(idx === -1){
    showToast('Order added (Simulated)');
  } else {
    showToast('Order updated (Simulated)');
  }
  bootstrap.Modal.getInstance(document.getElementById('orderModal')).hide();
  return false;
}

function changeDateRange(label){
  document.getElementById('dateLabel').textContent = label;
  showToast('Filter: ' + label);
}

function exportReport(){
  showToast('Report export initiated');
}

function manageCollection(){
  showToast('Opening Collection Manager…');
}
