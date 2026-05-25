/**
 * Sweets Website
 * =============================================================
 * File: admin.js
 * Description: Admin dashboard logic and chart initialization
 * =============================================================
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Sidebar toggle for mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');
    const adminBody = document.body;

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            adminBody.classList.toggle('sidebar-active');
        });
    }

    if (sidebarClose) {
        sidebarClose.addEventListener('click', () => {
            adminBody.classList.remove('sidebar-active');
        });
    }

    // Initialize Sales Chart if elements exist
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        initSalesChart(salesCtx);
    }
});

// Centralized chart instances to prevent "Canvas in use" errors
window.adminCharts = window.adminCharts || {};

/**
 * Initialize the Sales Analytics Chart
 */
function initSalesChart(ctx) {
    if (window.adminCharts.sales) {
        window.adminCharts.sales.destroy();
    }

    const data = {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Revenue',
            data: [12, 19, 10, 15, 22, 18, 25],
            borderColor: '#AE4B3A',
            backgroundColor: 'rgba(174, 75, 58, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointRadius: 5,
            pointBackgroundColor: '#AE4B3A',
            pointBorderColor: '#FFF',
            pointBorderWidth: 2
        }, {
            label: 'Volume',
            data: [8, 12, 15, 10, 14, 20, 18],
            borderColor: '#22222E',
            backgroundColor: 'transparent',
            borderWidth: 2,
            borderDash: [5, 5],
            tension: 0.4,
            pointRadius: 0
        }]
    };

    const config = {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#FFF',
                    titleColor: '#333',
                    bodyColor: '#666',
                    borderColor: '#DDD',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        labelColor: function(context) {
                            return {
                                borderColor: context.dataset.borderColor,
                                backgroundColor: context.dataset.borderColor,
                            };
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index',
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#F1F1F1',
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return '₹' + value + 'k';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    };

    window.adminCharts.sales = new Chart(ctx, config);
}

/**
 * Status Toggle Handler
 * Handles quick toggling of Active/Inactive or Draft/Published from table switches
 */
document.addEventListener('change', async function(e) {
    if (e.target.classList.contains('status-toggle-custom')) {
        const toggle = e.target;
        const id = toggle.dataset.id;
        const type = toggle.dataset.type; // product, category, subcategory
        const isChecked = toggle.checked;
        
        // Map status values based on type
        let newStatus = '';
        if (type === 'product') {
            newStatus = isChecked ? 'published' : 'out_of_stock';
        } else {
            newStatus = isChecked ? 'active' : 'inactive';
        }

        const originalState = !isChecked;
        toggle.disabled = true;

        try {
            const formData = new FormData();
            formData.append('action', 'toggle_status');
            formData.append('status', newStatus);
            
            // Map ID field name based on type
            if (type === 'product') {
                formData.append('product_id', id);
            } else {
                formData.append('id', id);
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            // Map endpoint based on type with correct pluralization
            let endpoint = '';
            if (type === 'category') {
                endpoint = 'api/v1/categories.php';
            } else if (type === 'subcategory') {
                endpoint = 'api/v1/subcategories.php';
            } else {
                endpoint = `api/v1/${type}s.php`;
            }
            
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData
            });

            const result = await response.json();

            if (!result.success && result.status !== 'success') {
                throw new Error(result.message || 'Failed to update status');
            }

            // Success feedback (optional: show a small toast)
            console.log(`${type} ${id} status updated to ${newStatus}`);
            
        } catch (error) {
            console.error('Status toggle error:', error);
            alert('Failed to update status: ' + error.message);
            // Revert toggle state on failure
            toggle.checked = originalState;
        } finally {
            toggle.disabled = false;
        }
    }
});
