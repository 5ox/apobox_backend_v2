/**
 * Reports Module — APO Box Admin
 *
 * AJAX-driven charts, KPI cards, and server-side paginated orders table.
 * Uses Chart.js 4 via Vite bundle.
 */
import Chart from 'chart.js/auto';

// ---------------------------------------------------------------
// Design tokens (match global.scss)
// ---------------------------------------------------------------
const COLORS = {
    navy:    '#0f2b5b',
    blue:    '#1a5fb4',
    green:   '#26a269',
    teal:    '#0d9488',
    amber:   '#e5a50a',
    red:     '#c01c28',
    slate:   '#64748b',
    sky:     '#99c1f1',
};

const CHART_COLORS = [
    COLORS.blue, COLORS.green, COLORS.amber, COLORS.teal,
    COLORS.red, COLORS.navy, COLORS.slate, COLORS.sky,
];

const STATUS_NAMES = {};  // Populated from server data

// ---------------------------------------------------------------
// Fetch helper with simple in-memory cache
// ---------------------------------------------------------------
const cache = {};

async function fetchJSON(url, ttlMs = 60000) {
    const now = Date.now();
    if (cache[url] && now - cache[url].ts < ttlMs) {
        return cache[url].data;
    }
    const res = await fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    if (!res.ok) throw new Error(`Fetch failed: ${res.status}`);
    const data = await res.json();
    cache[url] = { data, ts: now };
    return data;
}

function getBaseUrl() {
    // Detect manager vs employee prefix from current URL
    const match = window.location.pathname.match(/^\/(manager|employee)/);
    return match ? `/${match[1]}` : '/manager';
}

// ---------------------------------------------------------------
// KPI Cards
// ---------------------------------------------------------------
async function loadSummary(range = '30d') {
    const base = getBaseUrl();
    showSkeletons('.kpi-card');
    try {
        const data = await fetchJSON(`${base}/reports/api/summary?range=${range}`);

        setText('#kpi-total-orders', data.totalOrders.toLocaleString());
        setText('#kpi-active-customers', data.activeCustomers.toLocaleString());
        setText('#kpi-avg-per-customer', data.avgPerCustomer);
        setText('#kpi-total-customers', data.totalCustomers.toLocaleString());
        setText('#kpi-lifetime-shipped', (data.lifetimeShipped || 0).toLocaleString());

        // Percent change badge
        const changeBadge = document.getElementById('kpi-percent-change');
        if (changeBadge) {
            const pct = data.percentChange;
            const isUp = pct >= 0;
            changeBadge.textContent = `${isUp ? '+' : ''}${pct}%`;
            changeBadge.className = `badge bg-${isUp ? 'success' : 'danger'}-subtle text-${isUp ? 'success' : 'danger'}`;
        }

        // Size breakdown donut
        renderSizeDonut(data.sizeBreakdown || []);

        // Top customers bar
        renderTopCustomers(data.topCustomers || []);

        // Status counts
        renderStatusCounts(data.statusCounts || {});

        hideSkeletons('.kpi-card');
    } catch (err) {
        console.error('Failed to load summary:', err);
        hideSkeletons('.kpi-card');
    }
}

function setText(selector, value) {
    const el = document.querySelector(selector);
    if (el) el.textContent = value;
}

// ---------------------------------------------------------------
// Charts
// ---------------------------------------------------------------

let trendsChart = null;
let sizeDonutChart = null;
let topCustomersChart = null;
let customerGrowthChart = null;
let avgWeightChart = null;
let destinationsChart = null;

async function loadTrends(interval = 'week') {
    const base = getBaseUrl();
    const from = document.getElementById('trends-from')?.value || defaultFrom();
    const to = document.getElementById('trends-to')?.value || defaultTo();

    try {
        const data = await fetchJSON(
            `${base}/reports/api/trends?metric=orders&interval=${interval}&from=${from}&to=${to}`,
            120000
        );

        const ctx = document.getElementById('trendsChart');
        if (!ctx) return;

        if (trendsChart) trendsChart.destroy();

        trendsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => d.period),
                datasets: [
                    {
                        label: 'Total Orders',
                        data: data.map(d => d.total_orders),
                        borderColor: COLORS.blue,
                        backgroundColor: hexToRgba(COLORS.blue, 0.08),
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                    },
                    {
                        label: 'Paid',
                        data: data.map(d => d.paid_orders),
                        borderColor: COLORS.green,
                        backgroundColor: 'transparent',
                        borderDash: [4, 4],
                        tension: 0.3,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                    },
                    {
                        label: 'Shipped',
                        data: data.map(d => d.shipped_orders),
                        borderColor: COLORS.teal,
                        backgroundColor: 'transparent',
                        borderDash: [2, 2],
                        tension: 0.3,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, padding: 16 } },
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                    x: { grid: { display: false } },
                },
            },
        });

        // Render average weight chart from same data
        renderAvgWeight(data);
    } catch (err) {
        console.error('Failed to load trends:', err);
    }
}

function renderAvgWeight(data) {
    const ctx = document.getElementById('avgWeightChart');
    if (!ctx) return;

    if (avgWeightChart) avgWeightChart.destroy();

    avgWeightChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.period),
            datasets: [{
                label: 'Avg Weight (oz)',
                data: data.map(d => d.avg_weight_oz),
                borderColor: COLORS.amber,
                backgroundColor: hexToRgba(COLORS.amber, 0.08),
                fill: true,
                tension: 0.3,
                pointRadius: 2,
                pointHoverRadius: 5,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ` ${ctx.raw} oz (${(ctx.raw / 16).toFixed(1)} lb)`,
                    },
                },
            },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Ounces' } },
                x: { grid: { display: false } },
            },
        },
    });
}

async function loadDestinations() {
    const base = getBaseUrl();
    const from = document.getElementById('trends-from')?.value || defaultFrom();
    const to = document.getElementById('trends-to')?.value || defaultTo();

    try {
        const data = await fetchJSON(
            `${base}/reports/api/destinations?from=${from}&to=${to}&limit=15`,
            120000
        );

        const ctx = document.getElementById('destinationsChart');
        if (!ctx) return;

        if (destinationsChart) destinationsChart.destroy();

        destinationsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => `${d.zip} (${d.state})`),
                datasets: [{
                    label: 'Packages',
                    data: data.map(d => d.count),
                    backgroundColor: hexToRgba(COLORS.navy, 0.7),
                    borderRadius: 4,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 }, grid: { display: false } },
                    y: { grid: { display: false } },
                },
            },
        });
    } catch (err) {
        console.error('Failed to load destinations:', err);
    }
}

function renderSizeDonut(breakdown) {
    const ctx = document.getElementById('sizeDonutChart');
    if (!ctx) return;

    if (sizeDonutChart) sizeDonutChart.destroy();

    sizeDonutChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: breakdown.map(b => b.type),
            datasets: [{
                data: breakdown.map(b => b.count),
                backgroundColor: CHART_COLORS.slice(0, breakdown.length),
                borderWidth: 2,
                borderColor: '#fff',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 12 } },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const pct = breakdown[ctx.dataIndex]?.percent || 0;
                            return ` ${ctx.label}: ${ctx.raw.toLocaleString()} (${pct}%)`;
                        },
                    },
                },
            },
            onClick: (_event, elements) => {
                if (elements.length > 0) {
                    const idx = elements[0].index;
                    const type = breakdown[idx]?.type;
                    if (type && type !== 'Unknown') {
                        applyTableFilter('package_type', type);
                    }
                }
            },
        },
    });
}

function renderTopCustomers(topCustomers) {
    const ctx = document.getElementById('topCustomersChart');
    if (!ctx) return;

    if (topCustomersChart) topCustomersChart.destroy();

    topCustomersChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: topCustomers.map(c => truncate(c.customers_name, 20)),
            datasets: [{
                label: 'Packages',
                data: topCustomers.map(c => c.order_count),
                backgroundColor: hexToRgba(COLORS.blue, 0.7),
                borderRadius: 4,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { precision: 0 }, grid: { display: false } },
                y: { grid: { display: false } },
            },
        },
    });
}

async function loadCustomerGrowth(interval = 'month') {
    const base = getBaseUrl();
    const from = document.getElementById('trends-from')?.value || defaultFrom();
    const to = document.getElementById('trends-to')?.value || defaultTo();

    try {
        const data = await fetchJSON(
            `${base}/reports/api/customers?metric=signups&interval=${interval}&from=${from}&to=${to}`,
            120000
        );

        const ctx = document.getElementById('customerGrowthChart');
        if (!ctx) return;

        if (customerGrowthChart) customerGrowthChart.destroy();

        customerGrowthChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => d.period),
                datasets: [
                    {
                        label: 'New Signups',
                        data: data.map(d => d.count),
                        borderColor: COLORS.teal,
                        backgroundColor: hexToRgba(COLORS.teal, 0.08),
                        fill: true,
                        tension: 0.3,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Cumulative',
                        data: data.map(d => d.cumulative),
                        borderColor: COLORS.navy,
                        backgroundColor: 'transparent',
                        borderDash: [6, 3],
                        tension: 0.3,
                        yAxisID: 'y1',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, padding: 16 } },
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, position: 'left' },
                    y1: { ticks: { precision: 0 }, position: 'right', grid: { drawOnChartArea: false } },
                    x: { grid: { display: false } },
                },
            },
        });
    } catch (err) {
        console.error('Failed to load customer growth:', err);
    }
}

function renderStatusCounts(statusCounts) {
    const container = document.getElementById('status-counts');
    if (!container) return;

    const statuses = JSON.parse(container.dataset.statuses || '{}');
    const statusColors = {
        1: 'teal',
        2: 'amber',
        3: 'green',
        4: 'blue',
        5: 'red',
    };

    container.innerHTML = Object.entries(statusCounts)
        .map(([id, count]) => {
            const name = statuses[id] || `Status ${id}`;
            const color = statusColors[id] || 'slate';
            return `<div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <span class="status-badge status-badge--${name.toLowerCase().replace(/\s+/g, '-')}">${name}</span>
                <span class="fw-semibold">${count.toLocaleString()}</span>
            </div>`;
        })
        .join('');
}

// ---------------------------------------------------------------
// Orders Table (server-side paginated)
// ---------------------------------------------------------------

let currentPage = 1;
let currentSort = 'date_purchased';
let currentDir = 'desc';

async function loadOrdersTable(page = 1) {
    const base = getBaseUrl();
    currentPage = page;

    const params = new URLSearchParams({
        page,
        per_page: 50,
        sort: currentSort,
        dir: currentDir,
    });

    // Gather filters
    const from = document.getElementById('table-from')?.value;
    const to = document.getElementById('table-to')?.value;
    const status = document.getElementById('table-status')?.value;
    const packageType = document.getElementById('table-package-type')?.value;
    const search = document.getElementById('table-search')?.value;

    if (from) params.set('from', from);
    if (to) params.set('to', to);
    if (status) params.set('status', status);
    if (packageType) params.set('package_type', packageType);
    if (search) params.set('q', search);

    const tableBody = document.getElementById('orders-table-body');
    const paginationEl = document.getElementById('orders-pagination');
    const countEl = document.getElementById('orders-count');

    if (tableBody) tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">Loading...</td></tr>';

    try {
        const data = await fetchJSON(`${base}/reports/api/orders?${params}`, 30000);

        if (countEl) {
            countEl.textContent = `${data.total.toLocaleString()} orders`;
        }

        if (tableBody) {
            if (data.data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No orders found</td></tr>';
            } else {
                const statuses = JSON.parse(document.getElementById('status-counts')?.dataset.statuses || '{}');
                tableBody.innerHTML = data.data.map(order => {
                    const tracking = order.usps_track_num || order.ups_track_num || order.fedex_track_num || order.dhl_track_num || order.amazon_track_num || '';
                    const statusName = statuses[order.orders_status] || `Status ${order.orders_status}`;
                    const statusSlug = statusName.toLowerCase().replace(/\s+/g, '-');
                    const date = order.date_purchased ? new Date(order.date_purchased).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '';
                    const dest = [order.delivery_city, order.delivery_state, order.delivery_country].filter(Boolean).join(', ');

                    return `<tr>
                        <td class="text-muted">${date}</td>
                        <td><a href="${base}/customers/view/${order.customers_id}">${escapeHtml(order.customers_name)}</a></td>
                        <td class="text-monospace small">${escapeHtml(tracking)}</td>
                        <td>${escapeHtml(order.package_type || '')}</td>
                        <td>${order.weight_oz ? order.weight_oz + ' oz' : ''}</td>
                        <td><span class="status-badge status-badge--${statusSlug}">${statusName}</span></td>
                        <td class="small">${escapeHtml(dest)}</td>
                        <td><a href="${base}/orders/${order.orders_id}" class="btn btn-sm btn-outline-primary">View</a></td>
                    </tr>`;
                }).join('');
            }
        }

        if (paginationEl) {
            renderPagination(paginationEl, data);
        }
    } catch (err) {
        console.error('Failed to load orders:', err);
        if (tableBody) {
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-danger">Failed to load orders</td></tr>';
        }
    }
}

function renderPagination(container, data) {
    const totalPages = data.last_page;
    const current = data.current_page;

    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '<nav><ul class="pagination pagination-sm mb-0">';

    // Previous
    html += `<li class="page-item ${current === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" data-page="${current - 1}">&laquo;</a></li>`;

    // Page numbers (show max 7)
    const pages = paginationRange(current, totalPages, 7);
    for (const p of pages) {
        if (p === '...') {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        } else {
            html += `<li class="page-item ${p === current ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${p}">${p}</a></li>`;
        }
    }

    // Next
    html += `<li class="page-item ${current === totalPages ? 'disabled' : ''}">
        <a class="page-link" href="#" data-page="${current + 1}">&raquo;</a></li>`;

    html += '</ul></nav>';
    container.innerHTML = html;

    // Bind click events
    container.querySelectorAll('[data-page]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const page = parseInt(link.dataset.page);
            if (page >= 1 && page <= totalPages) {
                loadOrdersTable(page);
            }
        });
    });
}

function paginationRange(current, total, maxVisible) {
    if (total <= maxVisible) {
        return Array.from({ length: total }, (_, i) => i + 1);
    }

    const pages = [];
    const half = Math.floor(maxVisible / 2);
    let start = Math.max(2, current - half);
    let end = Math.min(total - 1, current + half);

    if (current - half < 2) end = Math.min(total - 1, maxVisible - 1);
    if (current + half > total - 1) start = Math.max(2, total - maxVisible + 2);

    pages.push(1);
    if (start > 2) pages.push('...');
    for (let i = start; i <= end; i++) pages.push(i);
    if (end < total - 1) pages.push('...');
    pages.push(total);

    return pages;
}

function applyTableFilter(key, value) {
    const el = document.getElementById(`table-${key.replace('_', '-')}`);
    if (el) {
        el.value = value;
        loadOrdersTable(1);
        // Scroll to table
        document.getElementById('orders-table-section')?.scrollIntoView({ behavior: 'smooth' });
    }
}

// ---------------------------------------------------------------
// Event bindings
// ---------------------------------------------------------------

function bindEvents() {
    // Granularity toggles for trends chart
    document.querySelectorAll('[data-interval]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelectorAll('[data-interval]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            loadTrends(btn.dataset.interval);
        });
    });

    // Range selector for KPI cards
    document.querySelectorAll('[data-range]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelectorAll('[data-range]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            loadSummary(btn.dataset.range);
        });
    });

    // Sort headers for orders table
    document.querySelectorAll('[data-sort]').forEach(th => {
        th.addEventListener('click', () => {
            const sort = th.dataset.sort;
            if (currentSort === sort) {
                currentDir = currentDir === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort = sort;
                currentDir = 'desc';
            }
            // Update sort indicators
            document.querySelectorAll('[data-sort]').forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
            });
            th.classList.add(currentDir === 'asc' ? 'sort-asc' : 'sort-desc');
            loadOrdersTable(1);
        });
    });

    // Table filters
    let searchTimeout;
    document.getElementById('table-search')?.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadOrdersTable(1), 400);
    });

    document.getElementById('table-status')?.addEventListener('change', () => loadOrdersTable(1));
    document.getElementById('table-package-type')?.addEventListener('change', () => loadOrdersTable(1));
    document.getElementById('table-from')?.addEventListener('change', () => loadOrdersTable(1));
    document.getElementById('table-to')?.addEventListener('change', () => loadOrdersTable(1));

    // Date range for trends
    document.getElementById('trends-from')?.addEventListener('change', () => {
        const active = document.querySelector('[data-interval].active');
        loadTrends(active?.dataset.interval || 'week');
        loadCustomerGrowth(active?.dataset.interval || 'month');
        loadDestinations();
    });
    document.getElementById('trends-to')?.addEventListener('change', () => {
        const active = document.querySelector('[data-interval].active');
        loadTrends(active?.dataset.interval || 'week');
        loadCustomerGrowth(active?.dataset.interval || 'month');
        loadDestinations();
    });

    // CSV Export
    document.getElementById('btn-export-csv')?.addEventListener('click', (e) => {
        e.preventDefault();
        const base = getBaseUrl();
        const params = new URLSearchParams();
        const from = document.getElementById('table-from')?.value;
        const to = document.getElementById('table-to')?.value;
        const status = document.getElementById('table-status')?.value;
        const packageType = document.getElementById('table-package-type')?.value;

        if (from) params.set('from', from);
        if (to) params.set('to', to);
        if (status) params.set('status', status);
        if (packageType) params.set('package_type', packageType);

        window.location.href = `${base}/reports/api/export?${params}`;
    });

    // Clear filters
    document.getElementById('btn-clear-filters')?.addEventListener('click', () => {
        document.getElementById('table-search').value = '';
        document.getElementById('table-from').value = '';
        document.getElementById('table-to').value = '';
        document.getElementById('table-status').value = '';
        document.getElementById('table-package-type').value = '';
        loadOrdersTable(1);
    });
}

// ---------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------

function hexToRgba(hex, alpha) {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function truncate(str, len) {
    if (!str) return '';
    return str.length > len ? str.slice(0, len) + '...' : str;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function defaultFrom() {
    const d = new Date();
    d.setFullYear(d.getFullYear() - 1);
    return d.toISOString().slice(0, 10);
}

function defaultTo() {
    return new Date().toISOString().slice(0, 10);
}

function showSkeletons(selector) {
    document.querySelectorAll(selector).forEach(el => {
        el.classList.add('skeleton-loading');
    });
}

function hideSkeletons(selector) {
    document.querySelectorAll(selector).forEach(el => {
        el.classList.remove('skeleton-loading');
    });
}

// ---------------------------------------------------------------
// Initialize
// ---------------------------------------------------------------

bindEvents();

// Load all data in parallel
Promise.all([
    loadSummary('30d'),
    loadTrends('week'),
    loadCustomerGrowth('month'),
    loadDestinations(),
    loadOrdersTable(1),
]);
