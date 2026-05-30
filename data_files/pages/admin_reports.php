<?php
// Super Admin Reports Hub — role 5 only
if (($user_role ?? 0) != 5) { include('403.php'); return; }
?>
<style>
/* ── Admin Reports — ar-* ─────────────────────────────────────────── */
.ar-hero{background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 55%,#312e81 100%);padding:2rem 1.5rem 3.5rem;position:relative;overflow:hidden}
.ar-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")}
.ar-breadcrumb{display:flex;align-items:center;gap:.4rem;font-size:.78rem;color:rgba(255,255,255,.55);margin-bottom:.9rem}
.ar-breadcrumb a{color:rgba(255,255,255,.55);text-decoration:none}.ar-breadcrumb a:hover{color:#fff}
.ar-breadcrumb .sep{opacity:.4}
.ar-hero-title{font-size:1.5rem;font-weight:700;color:#fff;margin-bottom:.25rem}
.ar-hero-sub{font-size:.85rem;color:rgba(255,255,255,.6);margin-bottom:1.2rem}
.ar-kpi-row{display:flex;flex-wrap:wrap;gap:.6rem}
.ar-kpi-pill{background:rgba(255,255,255,.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.15);border-radius:2rem;padding:.35rem .85rem;color:#fff;font-size:.8rem;display:flex;align-items:center;gap:.45rem}
.ar-kpi-pill i{opacity:.7}
.ar-body{background:#f8fafc;margin-top:-1.8rem;border-radius:1.2rem 1.2rem 0 0;padding:1.5rem;min-height:70vh;position:relative;z-index:1}
/* tabs */
.ar-tabs{display:flex;gap:0;border-bottom:2px solid #e2e8f0;margin-bottom:1.5rem;overflow-x:auto}
.ar-tab{border:none;background:none;padding:.65rem 1.2rem;font-size:.84rem;font-weight:600;color:#64748b;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;white-space:nowrap;transition:color .15s,border-color .15s}
.ar-tab.active{color:#6366f1;border-bottom-color:#6366f1}
.ar-tab:hover:not(.active){color:#374151}
.ar-tab-pane{display:none}.ar-tab-pane.active{display:block}
/* stat cards */
.ar-stat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem}
.ar-stat-card{background:#fff;border:1px solid #e8ecf3;border-radius:.9rem;padding:1.1rem;transition:box-shadow .15s}
.ar-stat-card:hover{box-shadow:0 4px 16px rgba(99,102,241,.1)}
.ar-stat-val{font-size:1.9rem;font-weight:800;line-height:1;color:#1e293b}
.ar-stat-lbl{font-size:.76rem;color:#94a3b8;margin-top:.25rem;font-weight:500}
.ar-stat-icon{width:40px;height:40px;border-radius:.65rem;display:flex;align-items:center;justify-content:center;font-size:1.1rem;margin-bottom:.75rem}
/* chart wrapper */
.ar-chart-card{background:#fff;border:1px solid #e8ecf3;border-radius:.9rem;padding:1.2rem;margin-bottom:1.3rem}
.ar-chart-title{font-size:.88rem;font-weight:700;color:#374151;margin-bottom:.9rem;display:flex;align-items:center;gap:.4rem}
.ar-chart-wrap{position:relative;height:220px}
/* tables */
.ar-table-wrap{background:#fff;border-radius:.9rem;border:1px solid #e8ecf3;overflow:hidden;margin-bottom:1.3rem}
.ar-table-header{padding:.8rem 1rem;border-bottom:1px solid #e8ecf3;display:flex;align-items:center;justify-content:space-between;gap:.6rem}
.ar-table-header-title{font-size:.88rem;font-weight:700;color:#374151}
.ar-table{width:100%;border-collapse:collapse;font-size:.82rem}
.ar-table thead th{background:#f8fafc;font-weight:700;color:#374151;padding:.7rem .9rem;border-bottom:2px solid #e2e8f0;white-space:nowrap}
.ar-table tbody td{padding:.65rem .9rem;border-bottom:1px solid #f1f5f9;color:#374151;vertical-align:middle}
.ar-table tbody tr:last-child td{border-bottom:none}
.ar-table tbody tr:hover td{background:#fafaff}
/* status badges */
.ar-badge{font-size:.68rem;font-weight:700;padding:.2rem .6rem;border-radius:2rem;display:inline-block}
.ar-badge.pending{background:#fef9c3;color:#a16207}
.ar-badge.awaiting_payment{background:#dbeafe;color:#2563eb}
.ar-badge.paid,.ar-badge.active{background:#dcfce7;color:#16a34a}
.ar-badge.rejected,.ar-badge.cancelled{background:#fee2e2;color:#dc2626}
/* progress bar */
.ar-prog{height:7px;border-radius:4px;background:#f1f5f9;overflow:hidden;min-width:80px}
.ar-prog-bar{height:100%;border-radius:4px;background:linear-gradient(90deg,#6366f1,#8b5cf6)}
/* funnel bars */
.ar-funnel{display:flex;flex-direction:column;gap:.5rem}
.ar-funnel-row{display:flex;align-items:center;gap:.7rem;font-size:.82rem}
.ar-funnel-label{min-width:130px;color:#64748b;font-weight:500}
.ar-funnel-bar-wrap{flex:1;height:18px;background:#f1f5f9;border-radius:4px;overflow:hidden}
.ar-funnel-bar{height:100%;border-radius:4px;background:linear-gradient(90deg,#6366f1,#8b5cf6);transition:width .5s ease}
.ar-funnel-count{min-width:40px;text-align:right;font-weight:700;color:#374151}
/* skeleton */
.ar-skel{border-radius:.4rem;background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:arSkel 1.4s infinite}
@keyframes arSkel{0%{background-position:200% 0}100%{background-position:-200% 0}}
/* empty */
.ar-empty{text-align:center;padding:3rem 1rem;color:#94a3b8}
.ar-empty i{font-size:2.5rem;display:block;margin-bottom:.7rem;opacity:.35}
</style>

<!-- Hero -->
<div class="ar-hero">
    <div class="ar-breadcrumb">
        <a href="?view=admin_dashboard"><i class="bi bi-house-fill"></i></a>
        <span class="sep">/</span>
        <span>Super Admin</span>
        <span class="sep">/</span>
        <span style="color:#fff">Reports</span>
    </div>
    <div class="ar-hero-title"><i class="bi bi-graph-up-arrow me-2" style="color:#a5b4fc"></i>Institutional Reports</div>
    <div class="ar-hero-sub">Revenue, purchase activity, and license utilization across all organizations</div>
    <div class="ar-kpi-row">
        <div class="ar-kpi-pill"><i class="bi bi-buildings"></i><span id="arPillOrgs">—</span> Active Orgs</div>
        <div class="ar-kpi-pill"><i class="bi bi-cash-stack"></i>TZS <span id="arPillRevenue">—</span> This Month</div>
        <div class="ar-kpi-pill"><i class="bi bi-hourglass-split"></i><span id="arPillPending">—</span> Pending Requests</div>
        <div class="ar-kpi-pill"><i class="bi bi-key-fill"></i><span id="arPillLicenses">—</span> Active Licenses</div>
    </div>
</div>

<!-- Body -->
<div class="ar-body">

    <!-- Tabs -->
    <div class="ar-tabs">
        <button class="ar-tab active" onclick="arSwitchTab('revenue', this)">
            <i class="bi bi-currency-dollar me-1"></i>Institutional Revenue
        </button>
        <button class="ar-tab" onclick="arSwitchTab('requests', this)">
            <i class="bi bi-file-earmark-text me-1"></i>Purchase Requests
        </button>
        <button class="ar-tab" onclick="arSwitchTab('bundles', this)">
            <i class="bi bi-collection-fill me-1"></i>Bundle Sales
        </button>
        <button class="ar-tab" onclick="arSwitchTab('licenses', this)">
            <i class="bi bi-key-fill me-1"></i>License Utilization
        </button>
    </div>

    <!-- TAB: Institutional Revenue -->
    <div class="ar-tab-pane active" id="arPane-revenue">
        <div class="ar-chart-card">
            <div class="ar-chart-title"><i class="bi bi-bar-chart-fill" style="color:#6366f1"></i>Monthly Revenue — Last 12 Months</div>
            <div class="ar-chart-wrap"><canvas id="arRevenueChart"></canvas></div>
        </div>
        <div class="ar-table-wrap">
            <div class="ar-table-header">
                <span class="ar-table-header-title"><i class="bi bi-buildings me-1" style="color:#6366f1"></i>Revenue by Organization</span>
            </div>
            <div class="table-responsive">
                <table class="ar-table">
                    <thead>
                        <tr>
                            <th>Organization</th>
                            <th>Total Revenue</th>
                            <th>Paid Requests</th>
                            <th>Pending</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="arRevenueTbody">
                        <tr><td colspan="5" class="text-center py-5">
                            <div class="spinner-border spinner-border-sm text-primary me-2"></div>Loading…
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB: Purchase Requests -->
    <div class="ar-tab-pane" id="arPane-requests">
        <div class="ar-chart-card">
            <div class="ar-chart-title"><i class="bi bi-funnel-fill" style="color:#6366f1"></i>Status Funnel</div>
            <div id="arFunnelWrap" style="padding:.5rem 0">
                <div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Loading…</div>
            </div>
        </div>
        <div class="ar-table-wrap">
            <div class="ar-table-header">
                <span class="ar-table-header-title"><i class="bi bi-list-ul me-1" style="color:#6366f1"></i>Recent Purchase Requests</span>
            </div>
            <div class="table-responsive">
                <table class="ar-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Organization</th>
                            <th>Bundle</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Paid At</th>
                        </tr>
                    </thead>
                    <tbody id="arRequestsTbody">
                        <tr><td colspan="7" class="text-center py-5 text-muted">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB: Bundle Sales -->
    <div class="ar-tab-pane" id="arPane-bundles">
        <div class="ar-table-wrap">
            <div class="ar-table-header">
                <span class="ar-table-header-title"><i class="bi bi-collection-fill me-1" style="color:#6366f1"></i>Bundle Purchase Requests</span>
            </div>
            <div class="table-responsive">
                <table class="ar-table">
                    <thead>
                        <tr>
                            <th>Bundle</th>
                            <th>Organization</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="arBundlesTbody">
                        <tr><td colspan="5" class="text-center py-5 text-muted">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB: License Utilization -->
    <div class="ar-tab-pane" id="arPane-licenses">
        <div class="ar-table-wrap">
            <div class="ar-table-header">
                <span class="ar-table-header-title"><i class="bi bi-key-fill me-1" style="color:#6366f1"></i>License Utilization — All Organizations</span>
            </div>
            <div class="table-responsive">
                <table class="ar-table">
                    <thead>
                        <tr>
                            <th>Organization</th>
                            <th>Course</th>
                            <th>Seats Purchased</th>
                            <th>Seats Used</th>
                            <th style="min-width:130px">Utilization</th>
                            <th>Avg Completion</th>
                        </tr>
                    </thead>
                    <tbody id="arLicensesTbody">
                        <tr><td colspan="6" class="text-center py-5 text-muted">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
(function () {
    const IR = '../data_files/ajax/ajax_institutional_reports.php';
    const SA = '../data_files/ajax/ajax_dashboard_super_admin.php';

    const arEsc = s => (s + '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    const arFmt = n => Number(n).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    const arDate = s => s ? new Date(s).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';

    let arChart = null;
    let arTabsLoaded = { revenue: false, requests: false, bundles: false, licenses: false };

    /* ── Init ── */
    async function arInit() {
        const r = await fetch(`${SA}?action=get_commercial_stats`).then(x => x.json()).catch(() => ({}));
        if (r.status === 'success') {
            document.getElementById('arPillOrgs').textContent     = arFmt(r.total_orgs ?? 0);
            document.getElementById('arPillRevenue').textContent  = arFmt(r.revenue_this_month ?? 0);
            document.getElementById('arPillPending').textContent  = arFmt(r.pending_requests ?? 0);
            document.getElementById('arPillLicenses').textContent = arFmt(r.active_licenses ?? 0);
        }
        arLoadRevenue();
    }

    /* ── Tab switch ── */
    window.arSwitchTab = function (name, el) {
        document.querySelectorAll('.ar-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.ar-tab-pane').forEach(p => p.classList.remove('active'));
        el.classList.add('active');
        document.getElementById('arPane-' + name).classList.add('active');
        if (!arTabsLoaded[name]) {
            arTabsLoaded[name] = true;
            if (name === 'revenue')  arLoadRevenue();
            if (name === 'requests') arLoadRequests();
            if (name === 'bundles')  arLoadBundles();
            if (name === 'licenses') arLoadLicenses();
        }
    };

    /* ── Revenue tab ── */
    async function arLoadRevenue() {
        arTabsLoaded.revenue = true;
        const [chartR, orgR] = await Promise.all([
            fetch(`${SA}?action=get_revenue_chart`).then(x => x.json()).catch(() => ({})),
            fetch(`${IR}?action=revenue_by_org`).then(x => x.json()).catch(() => ({})),
        ]);

        // Chart
        if (chartR.status === 'success') {
            arDrawRevenueChart(chartR.chart ?? []);
        }

        // Table
        const tb = document.getElementById('arRevenueTbody');
        const orgs = orgR.orgs ?? [];
        if (!orgs.length) {
            tb.innerHTML = '<tr><td colspan="5"><div class="ar-empty"><i class="bi bi-buildings"></i><div>No revenue data available.</div></div></td></tr>';
            return;
        }
        tb.innerHTML = orgs.map(o => `
            <tr>
                <td style="font-weight:600">${arEsc(o.org_name)}</td>
                <td><strong style="color:#16a34a">TZS ${arFmt(o.total_revenue)}</strong></td>
                <td>${o.paid_requests}</td>
                <td>${o.pending_requests > 0 ? `<span style="color:#d97706;font-weight:600">${o.pending_requests}</span>` : '<span style="color:#94a3b8">0</span>'}</td>
                <td><span class="ar-badge ${arEsc(o.org_status)}">${arEsc(o.org_status)}</span></td>
            </tr>
        `).join('');
    }

    function arDrawRevenueChart(data) {
        if (!data.length) return;
        if (typeof Chart === 'undefined') {
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
            s.onload = () => arDrawRevenueChart(data);
            document.head.appendChild(s);
            return;
        }
        const ctx = document.getElementById('arRevenueChart').getContext('2d');
        if (arChart) arChart.destroy();
        arChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.label),
                datasets: [{
                    label: 'Revenue (TZS)',
                    data: data.map(d => d.revenue),
                    backgroundColor: 'rgba(99,102,241,.7)',
                    borderColor: '#6366f1',
                    borderWidth: 1,
                    borderRadius: 5,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        ticks: { callback: v => 'TZS ' + arFmt(v), font: { size: 11 } },
                        grid: { color: '#f1f5f9' },
                    },
                    x: { ticks: { font: { size: 11 } }, grid: { display: false } },
                },
            },
        });
    }

    /* ── Requests tab ── */
    async function arLoadRequests() {
        const r = await fetch(`${IR}?action=purchase_requests_funnel`).then(x => x.json()).catch(() => ({}));
        if (r.status !== 'success') return;

        // Funnel
        const funnel  = r.funnel ?? [];
        const maxCount = Math.max(1, ...funnel.map(f => f.count));
        const fw = document.getElementById('arFunnelWrap');
        if (!funnel.length) {
            fw.innerHTML = '<div class="ar-empty"><i class="bi bi-funnel"></i><div>No request data.</div></div>';
        } else {
            const statusColors = {
                pending: '#f59e0b', awaiting_payment: '#3b82f6',
                paid: '#10b981', active: '#10b981',
                rejected: '#ef4444', cancelled: '#94a3b8',
            };
            fw.innerHTML = '<div class="ar-funnel">' + funnel.map(f => `
                <div class="ar-funnel-row">
                    <span class="ar-funnel-label"><span class="ar-badge ${arEsc(f.status)}">${arEsc(f.status)}</span></span>
                    <div class="ar-funnel-bar-wrap">
                        <div class="ar-funnel-bar" style="width:${Math.round((f.count / maxCount) * 100)}%;background:${statusColors[f.status] || '#6366f1'}"></div>
                    </div>
                    <span class="ar-funnel-count">${f.count}</span>
                    <span style="min-width:90px;font-size:.75rem;color:#64748b">TZS ${arFmt(f.total_amount)}</span>
                </div>
            `).join('') + '</div>';
        }

        // Table
        const tb = document.getElementById('arRequestsTbody');
        const reqs = r.requests ?? [];
        if (!reqs.length) {
            tb.innerHTML = '<tr><td colspan="7"><div class="ar-empty"><i class="bi bi-file-earmark-text"></i><div>No requests found.</div></div></td></tr>';
            return;
        }
        tb.innerHTML = reqs.map(req => `
            <tr>
                <td><code style="font-size:.75rem;color:#6366f1">${arEsc(req.request_code || '#' + req.id)}</code></td>
                <td style="font-weight:600">${arEsc(req.org_name)}</td>
                <td>${req.bundle_name ? arEsc(req.bundle_name) : '<span style="color:#94a3b8">—</span>'}</td>
                <td><strong>TZS ${arFmt(req.final_price)}</strong></td>
                <td><span class="ar-badge ${arEsc(req.status)}">${arEsc(req.status)}</span></td>
                <td style="color:#64748b;font-size:.78rem">${arDate(req.created_at)}</td>
                <td style="color:#64748b;font-size:.78rem">${arDate(req.paid_at)}</td>
            </tr>
        `).join('');
    }

    /* ── Bundle sales tab ── */
    async function arLoadBundles() {
        const r = await fetch(`${IR}?action=purchase_requests_funnel`).then(x => x.json()).catch(() => ({}));
        const tb = document.getElementById('arBundlesTbody');
        const reqs = (r.requests ?? []).filter(req => req.bundle_name);
        if (!reqs.length) {
            tb.innerHTML = '<tr><td colspan="5"><div class="ar-empty"><i class="bi bi-collection-fill"></i><div>No bundle sales yet.</div></div></td></tr>';
            return;
        }
        tb.innerHTML = reqs.map(req => `
            <tr>
                <td style="font-weight:600">${arEsc(req.bundle_name)}</td>
                <td>${arEsc(req.org_name)}</td>
                <td><strong>TZS ${arFmt(req.final_price)}</strong></td>
                <td><span class="ar-badge ${arEsc(req.status)}">${arEsc(req.status)}</span></td>
                <td style="color:#64748b;font-size:.78rem">${arDate(req.created_at)}</td>
            </tr>
        `).join('');
    }

    /* ── License utilization tab ── */
    async function arLoadLicenses() {
        const r = await fetch(`${IR}?action=license_utilization_all`).then(x => x.json()).catch(() => ({}));
        const tb = document.getElementById('arLicensesTbody');
        const licenses = r.licenses ?? [];
        if (!licenses.length) {
            tb.innerHTML = '<tr><td colspan="6"><div class="ar-empty"><i class="bi bi-key"></i><div>No license data found.</div></div></td></tr>';
            return;
        }
        tb.innerHTML = licenses.map(l => {
            const pct = l.utilization_pct;
            const barColor = pct >= 90 ? '#ef4444' : (pct >= 70 ? '#f59e0b' : '#6366f1');
            return `<tr>
                <td style="font-weight:600">${arEsc(l.org_name)}</td>
                <td>${arEsc(l.title)}</td>
                <td style="text-align:center">${l.seats_purchased}</td>
                <td style="text-align:center">${l.seats_used}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:.5rem">
                        <div class="ar-prog flex-grow-1">
                            <div class="ar-prog-bar" style="width:${pct}%;background:${barColor}"></div>
                        </div>
                        <span style="font-size:.75rem;min-width:36px;font-weight:600;color:${barColor}">${pct}%</span>
                    </div>
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:.4rem">
                        <div class="ar-prog" style="width:70px">
                            <div class="ar-prog-bar" style="width:${Math.min(100, l.avg_completion)}%;background:linear-gradient(90deg,#10b981,#34d399)"></div>
                        </div>
                        <span style="font-size:.75rem;font-weight:600;color:#10b981">${l.avg_completion}%</span>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    arInit();
})();
</script>
