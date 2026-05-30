<?php
$user_role = $user_role ?? 0;
if ($user_role != 5) { include('403.php'); return; }
?>
<style>
/* ── admin purchase requests (apr-*) ── */
.apr-hero { background: linear-gradient(135deg,#0f172a 0%,#1a1a2e 40%,#16213e 70%,#0f3460 100%);
    padding: 2rem 0 3.5rem; position: relative; overflow: hidden; }
.apr-hero::before { content:''; position:absolute; inset:0;
    background: radial-gradient(circle at 10% 70%, rgba(59,130,246,.2) 0%, transparent 50%),
                radial-gradient(circle at 90% 15%, rgba(139,92,246,.15) 0%, transparent 45%),
                radial-gradient(circle at 55% 90%, rgba(16,185,129,.1) 0%, transparent 40%);
    pointer-events:none; animation: apr-orb 9s ease-in-out infinite alternate; }
@keyframes apr-orb { 0%{opacity:.7;transform:scale(1)} 100%{opacity:1;transform:scale(1.03)} }
.apr-canvas { max-width:1280px; margin:-2rem auto 0; padding:0 1.25rem 3rem; position:relative; z-index:10; }

/* KPI cards */
.apr-kpi { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12);
    border-radius:18px; padding:1.2rem 1.5rem; backdrop-filter:blur(6px);
    transition:transform .2s, box-shadow .2s; }
.apr-kpi:hover { transform:translateY(-3px); box-shadow:0 10px 30px rgba(0,0,0,.25); }
.apr-kpi .kv { font-size:1.75rem; font-weight:800; color:#fff; line-height:1; }
.apr-kpi .kl { font-size:.68rem; color:rgba(255,255,255,.5); text-transform:uppercase; letter-spacing:.06em; margin-top:.3rem; }
.apr-kpi .ki { font-size:1.8rem; opacity:.2; }

/* card */
.apr-card { background:#fff; border-radius:18px; box-shadow:0 2px 14px rgba(0,0,0,.07);
    border:1px solid rgba(0,0,0,.05); overflow:hidden; }
.apr-card-header { padding:.9rem 1.25rem; border-bottom:1px solid #f1f5f9;
    display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; }

/* table */
.apr-table th { font-size:.72rem; text-transform:uppercase; letter-spacing:.05em;
    color:#64748b; font-weight:700; padding:.75rem 1rem; white-space:nowrap;
    border-bottom:2px solid #f1f5f9; }
.apr-table td { padding:.8rem 1rem; vertical-align:middle; font-size:.84rem;
    border-bottom:1px solid #f8fafc; }
.apr-table tr:last-child td { border-bottom:none; }
.apr-table tr:hover td { background:#f8f9ff; cursor:pointer; }

/* status badge */
.apr-badge { font-size:.68rem; font-weight:700; padding:.22rem .65rem;
    border-radius:100px; white-space:nowrap; }

/* tab */
.apr-tab { padding:.4rem 1rem; border-radius:8px; border:none; background:transparent;
    font-size:.8rem; font-weight:600; color:#64748b; cursor:pointer; transition:all .15s;
    white-space:nowrap; }
.apr-tab.active { background:#eef2ff; color:#4f46e5; }

/* action button */
.apr-act { width:30px; height:30px; border-radius:8px; border:none; display:inline-flex;
    align-items:center; justify-content:center; font-size:.8rem; cursor:pointer; transition:all .15s; }

/* timeline */
.apr-timeline { border-left:2px solid #e2e8f0; padding-left:1.25rem; margin-left:.5rem; }
.apr-timeline-dot { width:12px; height:12px; border-radius:50%; margin-left:-1.69rem;
    flex-shrink:0; margin-top:.2rem; }

@media (prefers-color-scheme:dark) {
    .apr-card { background:#1e293b; border-color:rgba(255,255,255,.06); }
    .apr-card-header, .apr-table th { border-color:rgba(255,255,255,.06); }
    .apr-table td { border-color:rgba(255,255,255,.04); }
    .apr-table tr:hover td { background:rgba(99,102,241,.06); }
}
</style>

<!-- HERO -->
<div class="apr-hero">
  <div class="container-xl position-relative" style="z-index:2">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb mb-0" style="font-size:.75rem">
        <li class="breadcrumb-item"><a href="?view=admin_dashboard" class="text-white-50 text-decoration-none">Admin</a></li>
        <li class="breadcrumb-item active" style="color:rgba(255,255,255,.5)">Purchase Requests</li>
      </ol>
    </nav>
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 mb-4">
      <div>
        <h4 class="text-white fw-bold mb-1"><i class="bi bi-file-earmark-text-fill me-2"></i>Purchase Request Management</h4>
        <div style="font-size:.8rem;color:rgba(255,255,255,.5)">Review, approve, and track institutional purchase requests</div>
      </div>
    </div>
    <!-- KPI row -->
    <div class="row g-3" id="aprKpiRow">
      <div class="col-6 col-lg-3">
        <div class="apr-kpi d-flex align-items-center justify-content-between">
          <div><div class="kv" id="kpiPending">—</div><div class="kl">Pending Review</div></div>
          <i class="bi bi-hourglass-split ki text-warning"></i>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="apr-kpi d-flex align-items-center justify-content-between">
          <div><div class="kv" id="kpiAwaiting">—</div><div class="kl">Awaiting Payment</div></div>
          <i class="bi bi-credit-card-fill ki text-info"></i>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="apr-kpi d-flex align-items-center justify-content-between">
          <div><div class="kv" id="kpiActiveM">—</div><div class="kl">Active This Month</div></div>
          <i class="bi bi-check-circle-fill ki text-success"></i>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="apr-kpi d-flex align-items-center justify-content-between">
          <div><div class="kv" id="kpiRevenue">—</div><div class="kl">Revenue This Month</div></div>
          <i class="bi bi-cash-stack ki" style="color:rgba(139,92,246,.4)"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- CANVAS -->
<div class="apr-canvas">

  <!-- Filters -->
  <div class="apr-card mb-3">
    <div class="d-flex flex-wrap align-items-center gap-2 p-3">
      <!-- Tab filter -->
      <div class="d-flex flex-wrap gap-1" id="aprTabBar">
        <?php
        $tabs = [
          ''                 => 'All',
          'pending'          => 'Pending',
          'reviewed'         => 'Reviewed',
          'awaiting_payment' => 'Awaiting Payment',
          'active'           => 'Active',
          'rejected'         => 'Rejected',
          'cancelled'        => 'Cancelled',
        ];
        foreach ($tabs as $val => $lbl):
        ?>
        <button class="apr-tab<?= $val === '' ? ' active' : '' ?>" data-status="<?= $val ?>" onclick="aprSetTab(this)"><?= $lbl ?></button>
        <?php endforeach; ?>
      </div>
      <div class="ms-auto d-flex gap-2">
        <select id="aprOrgFilter" class="form-select form-select-sm" style="width:auto" onchange="aprLoad()">
          <option value="">All Organizations</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Table -->
  <div class="apr-card">
    <div class="apr-card-header">
      <div class="fw-semibold" style="font-size:.92rem"><i class="bi bi-list-ul me-2 text-primary"></i>Purchase Requests</div>
      <div id="aprCountBadge" class="text-muted" style="font-size:.8rem"></div>
    </div>
    <div class="table-responsive">
      <table class="table apr-table mb-0">
        <thead><tr>
          <th class="ps-4">Code</th>
          <th>Organization</th>
          <th>Item</th>
          <th>Seats</th>
          <th>Original</th>
          <th>Final Price</th>
          <th>Status</th>
          <th>Submitted</th>
          <th class="text-end pe-4">Actions</th>
        </tr></thead>
        <tbody id="aprTbody">
          <tr><td colspan="9" class="text-center py-4">
            <span class="spinner-border spinner-border-sm text-primary me-2"></span>Loading…
          </td></tr>
        </tbody>
      </table>
    </div>
    <div id="aprPagination" class="d-flex justify-content-center gap-2 p-3"></div>
  </div>

</div><!-- /canvas -->

<!-- ── Review Modal ── -->
<div class="modal fade" id="aprReviewModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff">
        <h6 class="modal-title fw-bold"><i class="bi bi-search me-2"></i>Review Purchase Request</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="rvReqId">
        <div id="rvRequestInfo" class="mb-4"></div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Original Price</label>
            <div class="form-control bg-light fw-bold" id="rvOriginalPrice"></div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Apply Discount</label>
            <select class="form-select" id="rvDiscountId" onchange="aprCalcFinalPrice()">
              <option value="">No Discount</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Final Price <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">TZS</span>
              <input type="number" class="form-control" id="rvFinalPrice" step="0.01" min="0" oninput="aprClearDiscount()">
            </div>
            <div class="small text-muted mt-1">Override the calculated amount if needed.</div>
          </div>
          <div class="col-12">
            <label class="form-label small fw-semibold">Admin Remarks</label>
            <textarea class="form-control" id="rvRemarks" rows="3" placeholder="Notes for the organization admin…"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 bg-light d-flex gap-2">
        <button class="btn btn-secondary me-auto" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-outline-warning px-3 fw-semibold" onclick="aprSetAwaitingPayment()" id="rvAwaitBtn" style="display:none">
          <i class="bi bi-credit-card me-1"></i>Mark Awaiting Payment
        </button>
        <button class="btn btn-primary px-4 fw-semibold" onclick="aprSubmitReview()" id="rvSaveBtn">
          <i class="bi bi-check2 me-1"></i>Save Review
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ── Mark Paid Modal ── -->
<div class="modal fade" id="aprPaidModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0" style="background:linear-gradient(135deg,#059669,#047857);color:#fff">
        <h6 class="modal-title fw-bold"><i class="bi bi-cash-coin me-2"></i>Confirm Payment Received</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="pdReqId">
        <div id="pdInfo" class="p-3 rounded-3 mb-4" style="background:#f0fdf4;border:1px solid #bbf7d0"></div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Payment Reference <span class="text-muted fw-normal">(optional)</span></label>
          <input type="text" class="form-control" id="pdPayRef" placeholder="Bank ref, M-Pesa code, receipt no…">
        </div>
        <div class="p-3 rounded-3" style="background:#fef3c7;border:1px solid #fde68a;font-size:.82rem">
          <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
          This will activate the organization's course access immediately.
        </div>
      </div>
      <div class="modal-footer border-0 bg-light">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn fw-semibold px-4" style="background:linear-gradient(135deg,#059669,#047857);color:#fff;border:none" onclick="aprMarkPaid()" id="pdConfirmBtn">
          <i class="bi bi-check-circle-fill me-1"></i>Confirm &amp; Activate
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ── Reject Modal ── -->
<div class="modal fade" id="aprRejectModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0" style="background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff">
        <h6 class="modal-title fw-bold"><i class="bi bi-x-circle-fill me-2"></i>Reject Request</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="rjReqId">
        <div id="rjInfo" class="p-3 rounded-3 mb-4" style="background:#fff5f5;border:1px solid #fed7d7;font-size:.85rem"></div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
          <textarea class="form-control" id="rjReason" rows="3" placeholder="Provide a clear reason for rejection…"></textarea>
        </div>
      </div>
      <div class="modal-footer border-0 bg-light">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-danger px-4 fw-semibold" onclick="aprSubmitReject()">
          <i class="bi bi-x-circle me-1"></i>Reject Request
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ── Detail / Audit History Modal ── -->
<div class="modal fade" id="aprDetailModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 bg-light">
        <h6 class="modal-title fw-bold"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Request Detail &amp; Audit History</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="aprDetailBody">
        <div class="text-center py-5"><span class="spinner-border text-primary"></span></div>
      </div>
    </div>
  </div>
</div>

<script>
const APR_AJAX = '../data_files/ajax/ajax_purchase_requests.php';
const esc = s => (s+'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
const fmt = (n,d=0) => Number(n||0).toLocaleString('en-US',{minimumFractionDigits:d,maximumFractionDigits:d});
const fmtDate = s => s ? new Date(s).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : '—';
const fmtDT   = s => s ? new Date(s).toLocaleString('en-GB',{day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}) : '—';

let aprCurrentStatus = '', aprCurrentPage = 1;
let aprDiscounts = [], aprCurrentRowIdx = null, aprRowData = [];

const STATUS_CONFIG = {
    pending:          { bg:'#fef3c7', color:'#92400e', label:'Pending' },
    reviewed:         { bg:'#dbeafe', color:'#1e40af', label:'Reviewed' },
    awaiting_payment: { bg:'#fde8d8', color:'#9a3412', label:'Awaiting Payment' },
    paid:             { bg:'#dcfce7', color:'#166534', label:'Paid' },
    active:           { bg:'#d1fae5', color:'#065f46', label:'Active' },
    rejected:         { bg:'#fee2e2', color:'#991b1b', label:'Rejected' },
    cancelled:        { bg:'#f1f5f9', color:'#475569', label:'Cancelled' },
};
function statusBadge(s) {
    const c = STATUS_CONFIG[s] || { bg:'#f1f5f9', color:'#475569', label: s };
    return `<span class="apr-badge" style="background:${c.bg};color:${c.color}">${esc(c.label)}</span>`;
}

async function aprInit() {
    await Promise.all([aprLoadStats(), aprLoadOrgs(), aprLoadDiscounts()]);
    aprLoad();
}

async function aprLoadStats() {
    const r = await fetch(`${APR_AJAX}?action=get_stats`).then(x=>x.json()).catch(()=>({status:'error'}));
    if (r.status !== 'success') return;
    document.getElementById('kpiPending').textContent  = fmt(r.pending || 0);
    document.getElementById('kpiAwaiting').textContent = fmt(r.awaiting_payment || 0);
    document.getElementById('kpiActiveM').textContent  = fmt(r.active_this_month || 0);
    document.getElementById('kpiRevenue').textContent  = 'TZS ' + fmt(r.revenue_this_month || 0);
}

async function aprLoadOrgs() {
    const r = await fetch(`${APR_AJAX}?action=list_orgs`).then(x=>x.json()).catch(()=>({status:'error'}));
    const sel = document.getElementById('aprOrgFilter');
    (r.orgs || []).forEach(o => sel.appendChild(new Option(o.org_name, o.org_code)));
}

async function aprLoadDiscounts() {
    const r = await fetch(`${APR_AJAX}?action=list_discounts`).then(x=>x.json()).catch(()=>({status:'error'}));
    aprDiscounts = r.discounts || [];
    const sel = document.getElementById('rvDiscountId');
    aprDiscounts.forEach(d => sel.appendChild(new Option(`${d.name} (${d.discount_percent}%)`, d.id)));
}

async function aprLoad() {
    document.getElementById('aprTbody').innerHTML =
        '<tr><td colspan="9" class="text-center py-4"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Loading…</td></tr>';

    const org = document.getElementById('aprOrgFilter').value;
    const params = new URLSearchParams({ action:'list_all_requests', status: aprCurrentStatus, org, page: aprCurrentPage });
    const r = await fetch(`${APR_AJAX}?${params}`).then(x=>x.json()).catch(()=>({status:'error', data:[]}));

    if (r.status !== 'success') {
        document.getElementById('aprTbody').innerHTML = '<tr><td colspan="9" class="text-center text-danger py-4">Failed to load.</td></tr>';
        return;
    }

    aprRowData = r.data || [];
    document.getElementById('aprCountBadge').textContent = `${r.total || 0} request(s)`;

    if (!aprRowData.length) {
        document.getElementById('aprTbody').innerHTML =
            '<tr><td colspan="9" class="text-center py-5"><div style="color:#94a3b8"><i class="bi bi-inbox" style="font-size:2rem"></i><div class="mt-2">No requests found</div></div></td></tr>';
        document.getElementById('aprPagination').innerHTML = '';
        return;
    }

    document.getElementById('aprTbody').innerHTML = aprRowData.map((row, idx) => {
        const itemName = row.course_title || row.bundle_name || (row.item_type === 'custom' ? 'Custom Quote' : '—');
        const canReview  = ['pending','reviewed'].includes(row.status);
        const canPay     = ['awaiting_payment','reviewed'].includes(row.status);
        const canReject  = !['active','rejected','cancelled'].includes(row.status);
        const canAwait   = row.status === 'reviewed';

        return `<tr onclick="aprViewDetail(${idx})" title="Click to view details">
          <td class="ps-4">
            <span class="fw-semibold" style="font-size:.8rem;color:#4f46e5">${esc(row.request_code)}</span>
          </td>
          <td>
            <div class="fw-semibold">${esc(row.org_name || '—')}</div>
            <div style="font-size:.72rem;color:#94a3b8">${esc(row.org_code)}</div>
          </td>
          <td>
            <div style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${esc(itemName)}</div>
            <div style="font-size:.72rem;color:#94a3b8">${esc(row.item_type)}</div>
          </td>
          <td class="text-center">${fmt(row.seats_requested)}</td>
          <td>TZS ${fmt(row.original_price, 2)}</td>
          <td><strong>TZS ${fmt(row.final_price || row.original_price, 2)}</strong></td>
          <td>${statusBadge(row.status)}</td>
          <td style="color:#64748b;font-size:.78rem">${fmtDate(row.created_at)}</td>
          <td class="text-end pe-4" onclick="event.stopPropagation()">
            <div class="d-flex gap-1 justify-content-end flex-wrap">
              ${canReview  ? `<button class="apr-act" style="background:#eef2ff;color:#4f46e5" onclick="aprOpenReview(${idx})" title="Review"><i class="bi bi-search"></i></button>` : ''}
              ${canAwait   ? `<button class="apr-act" style="background:#fef3c7;color:#d97706" onclick="aprOpenAwait(${idx})" title="Mark Awaiting Payment"><i class="bi bi-credit-card"></i></button>` : ''}
              ${canPay     ? `<button class="apr-act" style="background:#d1fae5;color:#065f46" onclick="aprOpenPaid(${idx})" title="Confirm Payment"><i class="bi bi-cash-coin"></i></button>` : ''}
              ${canReject  ? `<button class="apr-act" style="background:#fee2e2;color:#dc2626" onclick="aprOpenReject(${idx})" title="Reject"><i class="bi bi-x-lg"></i></button>` : ''}
            </div>
          </td>
        </tr>`;
    }).join('');

    // Pagination
    const totalPages = Math.ceil(r.total / r.per);
    if (totalPages > 1) {
        let pg = '';
        for (let i = 1; i <= totalPages; i++) {
            pg += `<button class="btn btn-sm ${i === aprCurrentPage ? 'btn-primary' : 'btn-outline-secondary'}" onclick="aprGoPage(${i})">${i}</button>`;
        }
        document.getElementById('aprPagination').innerHTML = pg;
    } else {
        document.getElementById('aprPagination').innerHTML = '';
    }
}

window.aprSetTab = function(el) {
    document.querySelectorAll('#aprTabBar .apr-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    aprCurrentStatus = el.dataset.status;
    aprCurrentPage   = 1;
    aprLoad();
};

window.aprGoPage = function(p) { aprCurrentPage = p; aprLoad(); };

// ── Review ──
window.aprOpenReview = function(idx) {
    aprCurrentRowIdx = idx;
    const row = aprRowData[idx];
    const itemName = row.course_title || row.bundle_name || (row.item_type === 'custom' ? 'Custom Quote' : '—');
    document.getElementById('rvReqId').value = row.id;
    document.getElementById('rvOriginalPrice').textContent = 'TZS ' + fmt(row.original_price, 2);
    document.getElementById('rvFinalPrice').value = row.final_price || row.original_price;
    document.getElementById('rvDiscountId').value = row.discount_id || '';
    document.getElementById('rvRemarks').value = row.admin_remarks || '';
    document.getElementById('rvAwaitBtn').style.display = row.status === 'reviewed' ? '' : 'none';
    document.getElementById('rvRequestInfo').innerHTML = `
      <div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #f1f5f9">
        <div class="row g-2">
          <div class="col-sm-4"><div class="small text-muted">Request</div><div class="fw-bold" style="color:#4f46e5">${esc(row.request_code)}</div></div>
          <div class="col-sm-4"><div class="small text-muted">Organization</div><div class="fw-semibold">${esc(row.org_name||'—')}</div></div>
          <div class="col-sm-4"><div class="small text-muted">Item</div><div class="fw-semibold">${esc(itemName)}</div></div>
          <div class="col-sm-4"><div class="small text-muted">Seats</div><div>${fmt(row.seats_requested)}</div></div>
          <div class="col-sm-4"><div class="small text-muted">Status</div>${statusBadge(row.status)}</div>
          <div class="col-sm-4"><div class="small text-muted">Submitted</div><div style="font-size:.8rem">${fmtDate(row.created_at)}</div></div>
        </div>
      </div>`;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('aprReviewModal')).show();
};

window.aprCalcFinalPrice = function() {
    const row = aprCurrentRowIdx !== null ? aprRowData[aprCurrentRowIdx] : null;
    if (!row) return;
    const discId = parseInt(document.getElementById('rvDiscountId').value) || 0;
    let fp = parseFloat(row.original_price) || 0;
    if (discId) {
        const disc = aprDiscounts.find(d => d.id == discId);
        if (disc) fp = fp * (1 - disc.discount_percent / 100);
    }
    document.getElementById('rvFinalPrice').value = fp.toFixed(2);
};

window.aprClearDiscount = function() {
    document.getElementById('rvDiscountId').value = '';
};

window.aprSubmitReview = async function() {
    const reqId      = document.getElementById('rvReqId').value;
    const discountId = document.getElementById('rvDiscountId').value;
    const finalPrice = document.getElementById('rvFinalPrice').value;
    const remarks    = document.getElementById('rvRemarks').value.trim();
    if (!finalPrice || parseFloat(finalPrice) < 0) { aprToast('Final price is required', 'danger'); return; }

    const btn = document.getElementById('rvSaveBtn');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';
    const r = await aprPost({ action:'review_request', id:reqId, discount_id:discountId, final_price:finalPrice, admin_remarks:remarks });
    btn.disabled = false; btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Save Review';

    if (r.status === 'success') {
        bootstrap.Modal.getInstance(document.getElementById('aprReviewModal'))?.hide();
        aprToast('Request reviewed successfully');
        aprLoad(); aprLoadStats();
    } else aprToast(r.message || 'Failed', 'danger');
};

window.aprSetAwaitingPayment = async function() {
    const reqId = document.getElementById('rvReqId').value;
    const r = await aprPost({ action:'set_awaiting_payment', id:reqId });
    if (r.status === 'success') {
        bootstrap.Modal.getInstance(document.getElementById('aprReviewModal'))?.hide();
        aprToast('Moved to awaiting payment — organization notified');
        aprLoad(); aprLoadStats();
    } else aprToast(r.message || 'Failed', 'danger');
};

window.aprOpenAwait = async function(idx) {
    const row = aprRowData[idx];
    const confirmed = await Swal.fire({
        title: 'Mark Awaiting Payment?',
        html: `Notify <strong>${esc(row.org_name || row.org_code)}</strong> to submit payment for <strong>${esc(row.request_code)}</strong>?<br><small class="text-muted">Final amount: TZS ${fmt(row.final_price || row.original_price, 2)}</small>`,
        icon: 'question', showCancelButton: true, confirmButtonText: 'Yes, Notify', confirmButtonColor: '#d97706', reverseButtons: true,
    });
    if (!confirmed.isConfirmed) return;
    const r = await aprPost({ action:'set_awaiting_payment', id:row.id });
    if (r.status === 'success') { aprToast('Organization notified to submit payment'); aprLoad(); aprLoadStats(); }
    else aprToast(r.message || 'Failed', 'danger');
};

// ── Mark Paid ──
window.aprOpenPaid = function(idx) {
    const row = aprRowData[idx];
    document.getElementById('pdReqId').value = row.id;
    document.getElementById('pdPayRef').value = '';
    const itemName = row.course_title || row.bundle_name || 'Custom Quote';
    document.getElementById('pdInfo').innerHTML = `
      <div class="d-flex flex-column gap-1">
        <div><strong>${esc(row.org_name||row.org_code)}</strong> — ${esc(row.request_code)}</div>
        <div class="small text-muted">${esc(itemName)} &bull; ${fmt(row.seats_requested)} seat(s)</div>
        <div class="mt-1"><strong class="text-success">Amount: TZS ${fmt(row.final_price||row.original_price,2)}</strong></div>
      </div>`;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('aprPaidModal')).show();
};

window.aprMarkPaid = async function() {
    const btn = document.getElementById('pdConfirmBtn');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing…';
    const r = await aprPost({ action:'mark_paid', id:document.getElementById('pdReqId').value, payment_ref:document.getElementById('pdPayRef').value });
    btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Confirm & Activate';
    if (r.status === 'success') {
        bootstrap.Modal.getInstance(document.getElementById('aprPaidModal'))?.hide();
        aprToast('Payment confirmed — course access activated');
        aprLoad(); aprLoadStats();
    } else aprToast(r.message || 'Failed', 'danger');
};

// ── Reject ──
window.aprOpenReject = function(idx) {
    const row = aprRowData[idx];
    document.getElementById('rjReqId').value = row.id;
    document.getElementById('rjReason').value = '';
    const itemName = row.course_title || row.bundle_name || 'Custom Quote';
    document.getElementById('rjInfo').innerHTML = `<strong>${esc(row.org_name||row.org_code)}</strong> — ${esc(row.request_code)}<br><small class="text-muted">${esc(itemName)}</small>`;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('aprRejectModal')).show();
};

window.aprSubmitReject = async function() {
    const reason = document.getElementById('rjReason').value.trim();
    if (!reason) { aprToast('Rejection reason is required', 'danger'); return; }
    const r = await aprPost({ action:'reject_request', id:document.getElementById('rjReqId').value, reason });
    if (r.status === 'success') {
        bootstrap.Modal.getInstance(document.getElementById('aprRejectModal'))?.hide();
        aprToast('Request rejected — organization notified');
        aprLoad(); aprLoadStats();
    } else aprToast(r.message || 'Failed', 'danger');
};

// ── Detail / Audit Modal ──
window.aprViewDetail = async function(idx) {
    const row = aprRowData[idx];
    document.getElementById('aprDetailBody').innerHTML = '<div class="text-center py-5"><span class="spinner-border text-primary"></span></div>';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('aprDetailModal')).show();

    const r = await fetch(`${APR_AJAX}?action=get_request&id=${row.id}`).then(x=>x.json()).catch(()=>({status:'error'}));
    if (r.status !== 'success') {
        document.getElementById('aprDetailBody').innerHTML = '<div class="text-danger text-center py-4">Failed to load details.</div>';
        return;
    }
    const d = r.data;
    const h = r.history || [];
    const itemName = d.course_title || d.bundle_name || (d.item_type === 'custom' ? 'Custom Quote Request' : '—');

    const actionColors = {
        submitted:'#6366f1', reviewed:'#3b82f6', awaiting_payment:'#f59e0b',
        paid_and_activated:'#059669', rejected:'#dc2626', cancelled:'#94a3b8'
    };
    const histHtml = h.map(e => `
        <div class="d-flex gap-3 mb-4">
          <div style="flex-shrink:0">
            <div style="width:32px;height:32px;border-radius:50%;background:${actionColors[e.action]||'#94a3b8'};display:flex;align-items:center;justify-content:center">
              <i class="bi bi-clock text-white" style="font-size:.7rem"></i>
            </div>
          </div>
          <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <span class="fw-semibold" style="font-size:.84rem">${esc(e.actor_name || 'System')}</span>
              <span class="apr-badge" style="background:${(actionColors[e.action]||'#94a3b8')}22;color:${actionColors[e.action]||'#475569'}">${esc(e.action.replace(/_/g,' '))}</span>
              <span style="font-size:.72rem;color:#94a3b8;margin-left:auto">${fmtDT(e.created_at)}</span>
            </div>
            ${e.notes ? `<div style="font-size:.8rem;color:#64748b;margin-top:.25rem">${esc(e.notes)}</div>` : ''}
          </div>
        </div>
    `).join('') || '<div class="text-muted small">No history available.</div>';

    document.getElementById('aprDetailBody').innerHTML = `
      <div class="row g-3 mb-4">
        <div class="col-lg-8">
          <div class="row g-3">
            <div class="col-sm-4"><div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #f1f5f9">
              <div class="small text-muted mb-1">Request Code</div><div class="fw-bold" style="color:#4f46e5">${esc(d.request_code)}</div>
            </div></div>
            <div class="col-sm-4"><div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #f1f5f9">
              <div class="small text-muted mb-1">Status</div>${statusBadge(d.status)}
            </div></div>
            <div class="col-sm-4"><div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #f1f5f9">
              <div class="small text-muted mb-1">Organization</div><div class="fw-semibold">${esc(d.org_name||'—')}</div>
            </div></div>
            <div class="col-sm-6"><div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #f1f5f9">
              <div class="small text-muted mb-1">Item</div><div class="fw-semibold">${esc(itemName)}</div><div class="small text-muted">${esc(d.item_type)}</div>
            </div></div>
            <div class="col-sm-6"><div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #f1f5f9">
              <div class="small text-muted mb-1">Seats</div><div class="fw-bold">${fmt(d.seats_requested)}</div>
            </div></div>
            <div class="col-sm-4"><div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #f1f5f9">
              <div class="small text-muted mb-1">Original Price</div><div>TZS ${fmt(d.original_price,2)}</div>
            </div></div>
            <div class="col-sm-4"><div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #f1f5f9">
              <div class="small text-muted mb-1">Discount</div>
              <div>${d.discount_name ? esc(d.discount_name)+' ('+d.discount_percent+'%)' : '—'}</div>
            </div></div>
            <div class="col-sm-4"><div class="p-3 rounded-3" style="background:#f0fdf4;border:1px solid #bbf7d0">
              <div class="small text-muted mb-1">Final Price</div><div class="fw-bold text-success">TZS ${fmt(d.final_price||d.original_price,2)}</div>
            </div></div>
            ${d.expected_start_date ? `<div class="col-sm-6"><div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #f1f5f9"><div class="small text-muted mb-1">Expected Start</div><div>${fmtDate(d.expected_start_date)}</div></div></div>` : ''}
            ${d.payment_ref ? `<div class="col-sm-6"><div class="p-3 rounded-3" style="background:#f0fdf4;border:1px solid #bbf7d0"><div class="small text-muted mb-1">Payment Reference</div><div class="fw-semibold font-monospace">${esc(d.payment_ref)}</div></div></div>` : ''}
            ${d.notes ? `<div class="col-12"><div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #f1f5f9"><div class="small text-muted mb-1">Notes from Org</div><div class="small">${esc(d.notes)}</div></div></div>` : ''}
            ${d.requirements ? `<div class="col-12"><div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #f1f5f9"><div class="small text-muted mb-1">Requirements</div><div class="small">${esc(d.requirements)}</div></div></div>` : ''}
            ${d.admin_remarks ? `<div class="col-12"><div class="p-3 rounded-3" style="background:#fef3c7;border:1px solid #fde68a"><div class="small text-muted mb-1">Admin Remarks</div><div class="small">${esc(d.admin_remarks)}</div></div></div>` : ''}
          </div>
        </div>
        <div class="col-lg-4">
          <div class="p-3 rounded-3 h-100" style="background:#f8fafc;border:1px solid #f1f5f9">
            <div class="fw-semibold mb-3" style="font-size:.88rem"><i class="bi bi-clock-history me-2 text-primary"></i>Audit History</div>
            <div class="apr-timeline">${histHtml}</div>
          </div>
        </div>
      </div>
    `;
};

// ── Helpers ──
async function aprPost(data) {
    const fd = new FormData();
    Object.entries(data).forEach(([k,v]) => fd.append(k, v ?? ''));
    return fetch(APR_AJAX, { method:'POST', body:fd }).then(x=>x.json()).catch(()=>({status:'error',message:'Network error'}));
}

function aprToast(msg, type='success') {
    const colors = { success:'#16a34a', danger:'#dc2626', warning:'#d97706', info:'#0891b2' };
    const icons  = { success:'bi-check-circle-fill', danger:'bi-exclamation-circle-fill', warning:'bi-exclamation-triangle-fill', info:'bi-info-circle-fill' };
    let c = document.getElementById('aprToastCon');
    if (!c) {
        c = Object.assign(document.createElement('div'), { id:'aprToastCon' });
        c.style.cssText = 'position:fixed;bottom:1.25rem;right:1.25rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem';
        document.body.appendChild(c);
    }
    const t = document.createElement('div');
    t.style.cssText = `background:${colors[type]||colors.success};color:#fff;padding:.65rem 1rem;border-radius:12px;font-size:.84rem;box-shadow:0 6px 20px rgba(0,0,0,.15);max-width:340px;display:flex;align-items:center;gap:.5rem`;
    t.innerHTML = `<i class="bi ${icons[type]||icons.success}"></i><span>${esc(msg)}</span>`;
    c.appendChild(t);
    setTimeout(() => { t.style.opacity='0'; t.style.transition='opacity .3s'; setTimeout(()=>t.remove(),300); }, 3500);
}

aprInit();
</script>
