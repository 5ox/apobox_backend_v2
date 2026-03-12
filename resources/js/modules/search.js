/**
 * Search Module — debounced AJAX live-search for customers & orders.
 *
 * Works by reading data attributes from #search-input:
 *   data-search-url    → the endpoint URL (e.g. /manager/customers)
 *   data-search-type   → "customers" or "orders"
 *
 * Sends Accept: application/json so the controller returns JSON.
 * Falls back gracefully to form submit if JS fails.
 */

const DEBOUNCE_MS = 300;
const MIN_CHARS   = 2;

let debounceTimer  = null;
let abortCtrl      = null;

const input          = document.getElementById('search-input');
const liveResults    = document.getElementById('live-results');
const liveBody       = document.getElementById('live-results-body');
const liveInfo       = document.getElementById('live-results-info');
const staticResults  = document.getElementById('static-results');
const statusFilter   = document.getElementById('status-filter');

if (input) {
    const searchUrl  = input.dataset.searchUrl;
    const searchType = input.dataset.searchType; // "customers" or "orders"
    const prefix     = searchUrl.split('/')[1];   // "manager" or "employee"

    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => runSearch(searchUrl, searchType, prefix), DEBOUNCE_MS);
    });

    // Also trigger live-search when status filter changes (orders page)
    if (statusFilter) {
        statusFilter.addEventListener('change', () => {
            runSearch(searchUrl, searchType, prefix);
        });
    }

    // On Escape, clear live results and restore static
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            hideLive();
            input.value = '';
        }
    });
}

function runSearch(url, type, prefix) {
    const q = input.value.trim();
    const status = statusFilter?.value || '';

    // Need at least MIN_CHARS, or a status filter
    if (q.length < MIN_CHARS && !status) {
        hideLive();
        return;
    }

    // Abort any in-flight request
    if (abortCtrl) abortCtrl.abort();
    abortCtrl = new AbortController();

    const params = new URLSearchParams();
    if (q) params.set('q', q);
    if (status) params.set('showStatus', status);

    showLoading();

    fetch(`${url}?${params}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        signal: abortCtrl.signal,
    })
    .then(r => r.json())
    .then(data => {
        // If server says redirect (exact match), navigate there
        if (data.redirect) {
            window.location.href = data.redirect;
            return;
        }

        showLive();
        renderResults(data, type, prefix);
    })
    .catch(err => {
        if (err.name !== 'AbortError') {
            console.error('Search failed:', err);
            hideLive();
        }
    });
}

function renderResults(data, type, prefix) {
    if (!data.results || data.results.length === 0) {
        liveBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">No results found</td></tr>`;
        liveInfo.textContent = '';
        return;
    }

    if (type === 'customers') {
        renderCustomers(data.results, prefix);
    } else {
        renderOrders(data.results, prefix);
    }

    liveInfo.textContent = `Showing ${data.results.length} of ${data.total} result${data.total !== 1 ? 's' : ''}`;
}

function renderCustomers(items, prefix) {
    liveBody.innerHTML = items.map(c => `
        <tr>
            <td><span class="fw-semibold font-monospace">${esc(c.billing_id || '')}</span></td>
            <td>${esc(c.full_name || '')}</td>
            <td class="text-muted">${esc(c.email || '')}</td>
            <td><a href="/${prefix}/customers/view/${c.customers_id}" class="btn btn-sm btn-outline-primary">
                <i data-lucide="eye" class="icon--sm me-1"></i>View</a></td>
        </tr>
    `).join('');

    // Re-render Lucide icons in the new DOM
    if (window.lucide) window.lucide.createIcons();
}

function renderOrders(items, prefix) {
    liveBody.innerHTML = items.map(o => `
        <tr>
            <td><a href="/${prefix}/orders/${o.orders_id}" class="fw-semibold">${o.orders_id}</a></td>
            <td>${o.customer_id
                ? `<a href="/${prefix}/customers/view/${o.customer_id}">${esc(o.customer_name || '')}</a>`
                : ''}</td>
            <td>${statusBadge(o.status)}</td>
            <td>$${Number(o.total || 0).toFixed(2)}</td>
            <td>${esc(o.date_purchased || '')}</td>
        </tr>
    `).join('');
}

function statusBadge(status) {
    if (!status) return '';
    const slugMap = {
        'Warehouse': 'warehouse',
        'Awaiting Payment': 'awaiting-payment',
        'Shipped': 'shipped',
        'Paid': 'paid',
        'Returned': 'returned',
        'Awaiting Package': 'awaiting-package',
    };
    const slug = slugMap[status] || status.toLowerCase().replace(/\s+/g, '-');
    return `<span class="status-badge status-badge--${slug}">${esc(status)}</span>`;
}

function showLive() {
    liveResults?.classList.remove('d-none');
    staticResults?.classList.add('d-none');
}

function hideLive() {
    liveResults?.classList.add('d-none');
    staticResults?.classList.remove('d-none');
}

function showLoading() {
    showLive();
    liveBody.innerHTML = Array.from({ length: 3 }, () =>
        `<tr>${Array.from({ length: 5 }, () =>
            `<td><div class="skeleton-loading" style="height:1rem;width:80%;border-radius:4px"></div></td>`
        ).join('')}</tr>`
    ).join('');
    liveInfo.textContent = 'Searching...';
}

function esc(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}
