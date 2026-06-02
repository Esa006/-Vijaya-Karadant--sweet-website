/**
 * Sweets Website
 * =============================================================
 * File: admin.js
 * Description: Admin dashboard logic and chart initialization
 * =============================================================
 */

/* ============================================================
   MOBILE DRAWER CONTROLLER
   Production-grade sidebar/drawer with:
   ✔ GPU-accelerated transform (no layout reflow)
   ✔ Dark overlay backdrop with fade
   ✔ Body scroll lock (iOS-safe: saves/restores scrollY)
   ✔ Close on overlay click
   ✔ Close on Escape key
   ✔ Close on nav-link tap (mobile)
   ✔ Resize guard (prevents state drift between breakpoints)
   ✔ ARIA accessibility (aria-expanded, aria-hidden)
   ============================================================ */
(function () {
    'use strict';

    /* ── Constants ──────────────────────────────────────────── */
    var MOBILE_BREAKPOINT = 992; // Must match CSS @media (max-width: 991px)
    var scrollY = 0;             // Saved scroll position for iOS lock

    /* ── Element references ─────────────────────────────────── */
    var sidebar      = document.getElementById('adminSidebar');
    var toggleBtn    = document.getElementById('sidebarToggle');
    var closeBtn     = document.getElementById('sidebarClose');
    var overlay      = null;    // Created dynamically below

    /* ── Inject overlay backdrop into <body> ────────────────── */
    function createOverlay() {
        var el = document.createElement('div');
        el.id = 'sidebarOverlay';
        el.setAttribute('aria-hidden', 'true');
        el.setAttribute('tabindex', '-1');
        document.body.appendChild(el);
        return el;
    }

    /* ── Is mobile viewport? ────────────────────────────────── */
    function isMobile() {
        return window.innerWidth < MOBILE_BREAKPOINT;
    }

    /* ── Open drawer ────────────────────────────────────────── */
    function openDrawer() {
        if (!sidebar) return;

        /* 1. Save current scroll position (iOS scroll-lock technique) */
        scrollY = window.scrollY || window.pageYOffset;

        /* 2. Lock body scroll */
        document.body.classList.add('sidebar-open');
        document.body.style.top = '-' + scrollY + 'px';
        document.body.style.position = 'fixed';
        document.body.style.width = '100%';

        /* 3. Show sidebar (CSS class triggers transform: translateX(0)) */
        document.body.classList.add('sidebar-open');

        /* 4. Show overlay: make it block first, then fade in via RAF */
        if (overlay) {
            overlay.style.display = 'block';
            /* Force a paint so transition triggers */
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    overlay.classList.add('overlay-visible');
                });
            });
        }

        /* 5. Update ARIA */
        if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
        if (sidebar)   sidebar.removeAttribute('aria-hidden');

        /* 6. Trap focus: move focus into sidebar for accessibility */
        var firstFocusable = sidebar.querySelector('a, button');
        if (firstFocusable) firstFocusable.focus();
    }

    /* ── Close drawer ───────────────────────────────────────── */
    function closeDrawer() {
        if (!sidebar) return;

        /* 1. Hide overlay (fade out, then hide after transition) */
        if (overlay) {
            overlay.classList.remove('overlay-visible');
            /* After the 300ms CSS transition, set display:none */
            setTimeout(function () {
                if (!overlay.classList.contains('overlay-visible')) {
                    overlay.style.display = 'none';
                }
            }, 320);
        }

        /* 2. Slide sidebar off-screen */
        document.body.classList.remove('sidebar-open');

        /* 3. Restore body scroll (iOS technique) */
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.width = '';
        window.scrollTo(0, scrollY);

        /* 4. Update ARIA */
        if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
        if (sidebar)   sidebar.setAttribute('aria-hidden', 'true');

        /* 5. Return focus to toggle button */
        if (toggleBtn) toggleBtn.focus();
    }

    /* ── Toggle drawer ──────────────────────────────────────── */
    function toggleDrawer() {
        if (document.body.classList.contains('sidebar-open')) {
            closeDrawer();
        } else {
            openDrawer();
        }
    }

    /* ── Bind events ────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {

        /* Create and cache the overlay element */
        overlay = createOverlay();

        /* Hamburger / toggle button */
        if (toggleBtn) {
            toggleBtn.setAttribute('aria-expanded', 'false');
            toggleBtn.setAttribute('aria-controls', 'adminSidebar');
            toggleBtn.setAttribute('aria-label', 'Open navigation menu');
            toggleBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (isMobile()) toggleDrawer();
            });
        }

        /* Close (X) button inside the sidebar */
        if (closeBtn) {
            closeBtn.setAttribute('aria-label', 'Close navigation menu');
            closeBtn.addEventListener('click', closeDrawer);
        }

        /* Overlay click → close drawer */
        overlay.addEventListener('click', closeDrawer);

        /* Escape key → close drawer */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && document.body.classList.contains('sidebar-open')) {
                closeDrawer();
            }
        });

        /* Nav links: close drawer when tapped on mobile */
        if (sidebar) {
            sidebar.querySelectorAll('.nav-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (isMobile() && document.body.classList.contains('sidebar-open')) {
                        closeDrawer();
                    }
                });
            });
        }

        /* Resize guard: if user resizes to desktop, clean up mobile state */
        window.addEventListener('resize', function () {
            if (!isMobile() && document.body.classList.contains('sidebar-open')) {
                /* Clean up without scroll restore (desktop doesn't need it) */
                if (overlay) {
                    overlay.classList.remove('overlay-visible');
                    overlay.style.display = 'none';
                }
                document.body.classList.remove('sidebar-open');
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.width = '';
                if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
            }
        }, { passive: true });

        /* Initialize Sales Chart if element exists */
        var salesCtx = document.getElementById('salesChart');
        if (salesCtx) {
            initSalesChart(salesCtx);
        }
    });

}());

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
