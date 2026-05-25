/**
 * Sweets Website - Reports Engine
 * =============================================================
 * File: assets/js/admin/pages/reports.js
 * Handles KPI fetch, chart rendering, period switching
 * =============================================================
 */

const ReportsEngine = {
    charts: {},
    currentRange: 7,

    init() {
        this.bindEvents();
        this.load(7); // default: weekly
    },

    bindEvents() {
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                const rangeMap = { daily: 1, weekly: 7, monthly: 30 };
                this.currentRange = rangeMap[e.target.dataset.range] ?? 7;
                this.load(this.currentRange);
            });
        });
    },

    load(range) {
        this.fetchOverview(range);
        this.fetchRevenueChart(range);
        this.fetchUnitsChart(range);
        this.fetchAovChart(range);
        this.fetchCategoryChart(range);
        this.fetchTopProducts(range);
    },

    // ── KPI Cards ──────────────────────────────────────────────
    async fetchOverview(range = 7) {
        try {
            const res    = await fetch(`../api/analytics.php?type=overview&range=${range}`);
            const result = await res.json();
            if (result.status === 'success') this.updateKPIs(result.data);
        } catch (e) { console.error('Overview fetch failed:', e); }
    },

    updateKPIs(data) {
        const fmt = (n) => Number(n ?? 0).toLocaleString('en-IN', { maximumFractionDigits: 2 });
        const set = (id, val) => { const el = document.getElementById(id); if (el) el.innerText = val; };

        set('kpi-revenue', `₹ ${fmt(data.revenue)}`);
        set('kpi-orders',  fmt(data.orders));
        set('kpi-units',   fmt(data.units));
        set('kpi-aov',     `₹ ${fmt(data.aov)}`);

        this.setGrowth('trend-revenue', data.growth ?? 0);
        this.setGrowth('trend-orders',  data.growth ?? 0);
        this.setGrowth('trend-units',   data.growth ?? 0);
        this.setGrowth('trend-aov',     data.growth ?? 0);
    },

    setGrowth(id, pct) {
        const el = document.getElementById(id);
        if (!el) return;
        const isPos = pct >= 0;
        el.className = `card-change ${isPos ? 'positive' : 'negative'}`;
        el.innerHTML = `<i class="bi bi-arrow-${isPos ? 'up' : 'down'}-right"></i><span>${isPos ? '+' : ''}${Number(pct).toFixed(1)}% vs prev period</span>`;
    },

    // ── Revenue Line Chart ─────────────────────────────────────
    async fetchRevenueChart(range = 30) {
        try {
            const res    = await fetch(`../api/analytics.php?type=revenue_chart&range=${range}`);
            const result = await res.json();
            if (result.status !== 'success') return;

            const dates   = result.data.map(r => r.date);
            const revenue = result.data.map(r => parseFloat(r.revenue));

            this.renderAreaChart('revenue', 'revenueChart', 'Revenue (₹)', dates, revenue, '#A02040');
        } catch(e) { console.error('Revenue chart failed:', e); }
    },

    // ── Units Sold Chart ─────────────────────────────────────
    async fetchUnitsChart(range = 30) {
        try {
            const res    = await fetch(`../api/analytics.php?type=units_chart&range=${range}`);
            const result = await res.json();
            if (result.status !== 'success') return;

            const dates = result.data.map(r => r.date);
            const units = result.data.map(r => parseInt(r.units));

            this.renderBarChart('units', 'unitsChart', 'Units Sold', dates, units, '#2E7D32');
        } catch(e) { console.error('Units chart failed:', e); }
    },

    // ── AOV Trend Chart ─────────────────────────────────────
    async fetchAovChart(range = 30) {
        try {
            const res    = await fetch(`../api/analytics.php?type=aov_chart&range=${range}`);
            const result = await res.json();
            if (result.status !== 'success') return;

            const dates = result.data.map(r => r.date);
            const aov   = result.data.map(r => parseFloat(r.aov));

            this.renderAreaChart('aov', 'aovChart', 'AOV (₹)', dates, aov, '#EF6C00');
        } catch(e) { console.error('AOV chart failed:', e); }
    },

    // ── Category Distribution Chart ──────────────────────────
    async fetchCategoryChart(range = 30) {
        try {
            const res    = await fetch(`../api/analytics.php?type=category_chart&range=${range}`);
            const result = await res.json();
            if (result.status !== 'success') return;

            const labels = result.data.map(r => r.category);
            const values = result.data.map(r => parseFloat(r.revenue));

            if (this.charts.category) {
                this.charts.category.updateSeries(values);
                this.charts.category.updateOptions({ labels: labels });
                return;
            }

            this.charts.category = new ApexCharts(document.getElementById('categoryChart'), {
                series: values,
                chart: { type: 'donut', height: 320 },
                labels: labels,
                colors: ['#A02040', '#EF6C00', '#2E7D32', '#1565C0', '#4527A0'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: true, formatter: val => `${val.toFixed(1)}%` },
                tooltip: { y: { formatter: v => `₹ ${Number(v).toLocaleString('en-IN')}` } }
            });
            this.charts.category.render();
        } catch(e) { console.error('Category chart failed:', e); }
    },

    // ── Generic Chart Helpers ────────────────────────────────
    renderAreaChart(key, elementId, name, dates, data, color) {
        if (this.charts[key]) {
            this.charts[key].updateSeries([{ name, data }]);
            this.charts[key].updateOptions({ xaxis: { categories: dates } });
            return;
        }

        this.charts[key] = new ApexCharts(document.getElementById(elementId), {
            series: [{ name, data }],
            chart: { type: 'area', height: 260, toolbar: { show: false }, zoom: { enabled: false } },
            colors: [color],
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
            dataLabels: { enabled: false },
            xaxis: { categories: dates, labels: { style: { fontSize: '10px' } } },
            yaxis: { labels: { style: { fontSize: '10px' } } },
            grid: { borderColor: '#f1f1f1' }
        });
        this.charts[key].render();
    },

    renderBarChart(key, elementId, name, dates, data, color) {
        if (this.charts[key]) {
            this.charts[key].updateSeries([{ name, data }]);
            this.charts[key].updateOptions({ xaxis: { categories: dates } });
            return;
        }

        this.charts[key] = new ApexCharts(document.getElementById(elementId), {
            series: [{ name, data }],
            chart: { type: 'bar', height: 260, toolbar: { show: false } },
            colors: [color],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
            dataLabels: { enabled: false },
            xaxis: { categories: dates, labels: { style: { fontSize: '10px' } } },
            yaxis: { labels: { style: { fontSize: '10px' } } },
            grid: { borderColor: '#f1f1f1' }
        });
        this.charts[key].render();
    },

    // ── Top Products Table ─────────────────────────────────────
    async fetchTopProducts(range = 30) {
        const tbody  = document.getElementById('productsTableBody');
        const mobile = document.getElementById('productsMobile');
        if (!tbody) return;

        try {
            const res    = await fetch(`../api/analytics.php?type=top_products&range=${range}`);
            const result = await res.json();

            if (result.status !== 'success' || !result.data.length) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">No sales data.</td></tr>`;
                return;
            }

            tbody.innerHTML = result.data.map((p, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td>${this.escHtml(p.name)}</td>
                    <td class="text-end">${Number(p.total_sold ?? 0).toLocaleString()}</td>
                    <td class="text-end">₹ ${Number(p.product_revenue ?? 0).toLocaleString('en-IN', { maximumFractionDigits: 0 })}</td>
                    <td class="text-end"><span class="badge bg-success-subtle text-success">↑</span></td>
                </tr>`).join('');
        } catch(e) { console.error('Top products failed:', e); }
    },

    escHtml(str) { return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); },
    
    exportCSV() {
        const range = this.currentRange || 7;
        window.location.href = `../api/export-reports.php?range=${range}`;
    }
};

document.addEventListener('DOMContentLoaded', () => ReportsEngine.init());
