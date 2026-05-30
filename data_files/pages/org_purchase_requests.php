<?php
if (($user_role ?? 0) != 4) { include('403.php'); return; }
$me = $_SESSION['usr_code'];
$orgRow = $db->query("
    SELECT o.org_code, o.org_name
    FROM tbl_organizations o
    INNER JOIN tbl_org_members m ON m.org_code = o.org_code
    WHERE m.usr_code = '$me' AND m.org_role = 'admin' AND m.status = 'active' AND o.deleted_at IS NULL
    LIMIT 1
")->fetch_assoc();
?>
<style>
/* ── org purchase requests (opr-*) ── */
.opr-hero { background: linear-gradient(135deg,#0f172a 0%,#1e1b4b 50%,#312e81 100%);
    padding: 2rem 0 3.5rem; position: relative; overflow: hidden; }
.opr-hero::before { content:''; position:absolute; inset:0;
    background: radial-gradient(circle at 15% 60%, rgba(99,102,241,.22) 0%, transparent 55%),
                radial-gradient(circle at 85% 20%, rgba(139,92,246,.18) 0%, transparent 48%),
                radial-gradient(circle at 50% 100%, rgba(59,130,246,.12) 0%, transparent 40%);
    pointer-events:none; animation: opr-orb 8s ease-in-out infinite alternate; }
@keyframes opr-orb { 0%{opacity:.8;transform:scale(1)} 100%{opacity:1;transform:scale(1.04)} }
.opr-canvas { max-width:1280px; margin:-2rem auto 0; padding:0 1.25rem 3rem; position:relative; z-index:10; }

/* KPI cards */
.opr-kpi { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12);
    border-radius:16px; padding:1.1rem 1.4rem; text-align:center; backdrop-filter:blur(6px);
    transition:transform .2s, box-shadow .2s; min-width:120px; }
.opr-kpi:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,.25); }
.opr-kpi .kpi-val { font-size:1.6rem; font-weight:800; color:#fff; line-height:1; }
.opr-kpi .kpi-lbl { font-size:.68rem; color:rgba(255,255,255,.5); text-transform:uppercase;
    letter-spacing:.06em; margin-top:.3rem; }

/* card */
.opr-card { background:#fff; border-radius:18px; box-shadow:0 2px 14px rgba(0,0,0,.07);
    border:1px solid rgba(0,0,0,.05); overflow:hidden; }
.opr-card-header { padding:.9rem 1.25rem; border-bottom:1px solid #f1f5f9;
    display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; }

/* table */
.opr-table th { font-size:.72rem; text-transform:uppercase; letter-spacing:.05em;
    color:#64748b; font-weight:700; padding:.75rem 1rem; white-space:nowrap;
    border-bottom:2px solid #f1f5f9; }
.opr-table td { padding:.8rem 1rem; vertical-align:middle; font-size:.84rem;
    border-bottom:1px solid #f8fafc; }
.opr-table tr:last-child td { border-bottom:none; }
.opr-table tr:hover td { background:#f8f9ff; }

/* status badges */
.opr-badge { font-size:.68rem; font-weight:700; padding:.22rem .65rem;
    border-radius:100px; white-space:nowrap; }

/* tab filter */
.opr-tab { padding:.4rem 1rem; border-radius:8px; border:none; background:transparent;
    font-size:.8rem; font-weight:600; color:#64748b; cursor:pointer; transition:all .15s;
    white-space:nowrap; }
.opr-tab.active { background:#eef2ff; color:#4f46e5; }

/* action button */
.opr-act { width:30px; height:30px; border-radius:8px; border:none; display:inline-flex;
    align-items:center; justify-content:center; font-size:.8rem; cursor:pointer; transition:all .15s; }

/* skeleton */
.opr-skel { background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);
    background-size:200% 100%; animation:opr-sk 1.5s infinite; border-radius:8px; }
@keyframes opr-sk { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* modal header */
.opr-modal-hdr { background:linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff; }

/* Pricing tier hint */
.opr-tier-hint { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px;
    padding:.65rem 1rem; font-size:.82rem; color:#166534; display:none; }

@media (prefers-color-scheme:dark) {
    .opr-card { background:#1e293b; border-color:rgba(255,255,255,.06); }
    .opr-card-header, .opr-table th { border-color:rgba(255,255,255,.06); }
    .opr-table td { border-color:rgba(255,255,255,.04); }
    .opr-table tr:hover td { background:rgba(99,102,241,.06); }
}
</style>

<!-- HERO -->
<div class="opr-hero">
  <div class="container-xl position-relative" style="z-index:2">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb mb-0" style="font-size:.75rem">
        <li class="breadcrumb-item"><a href="?view=3002" class="text-white-50 text-decoration-none">Dashboard</a></li>
        <li class="breadcrumb-item active" style="color:rgba(255,255,255,.5)">Purchases</li>
      </ol>
    </nav>
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3">
      <div class="flex-grow-1">
        <div class="d-flex align-items-center gap-2 mb-1">
          <h4 class="text-white fw-bold mb-0"><i class="bi bi-cart-fill me-2"></i>My Purchase Requests</h4>
        </div>
        <div style="font-size:.8rem;color:rgba(255,255,255,.5)"><?= htmlspecialchars($orgRow['org_name'] ?? '') ?></div>
      </div>
      <div class="d-flex gap-2 flex-wrap mt-2 mt-md-0" id="oprKpis">
        <div class="opr-kpi"><div class="kpi-val" id="kpiTotal">—</div><div class="kpi-lbl">Total</div></div>
        <div class="opr-kpi"><div class="kpi-val" id="kpiPending">—</div><div class="kpi-lbl">Pending</div></div>
        <div class="opr-kpi"><div class="kpi-val" id="kpiActive">—</div><div class="kpi-lbl">Active</div></div>
        <div class="opr-kpi"><div class="kpi-val" id="kpiSpent">—</div><div class="kpi-lbl">Total Spent</div></div>
      </div>
      <button class="btn fw-semibold px-4 ms-md-2" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:10px;white-space:nowrap" onclick="oprOpenNew()">
        <i class="bi bi-plus-circle-fill me-2"></i>New Request
      </button>
    </div>
  </div>
</div>

<!-- CANVAS -->
<div class="opr-canvas">

  <!-- Tab filter -->
  <div class="opr-card mb-3">
    <div class="d-flex flex-wrap gap-1 p-2" id="oprTabBar">
      <?php
      $tabs = [
        ''                 => 'All',
        'pending'          => 'Pending',
        'reviewed'         => 'Reviewed',
        'awaiting_payment' => 'Awaiting Payment',
        'paid'             => 'Paid',
        'active'           => 'Active',
        'rejected'         => 'Rejected',
        'cancelled'        => 'Cancelled',
      ];
      foreach ($tabs as $val => $lbl):
      ?>
      <button class="opr-tab<?= $val === '' ? ' active' : '' ?>" data-status="<?= $val ?>" onclick="oprSetTab(this)"><?= $lbl ?></button>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Table -->
  <div class="opr-card">
    <div class="opr-card-header">
      <div class="fw-semibold" style="font-size:.92rem"><i class="bi bi-list-ul me-2 text-primary"></i>Requests</div>
      <div id="oprCountBadge" class="text-muted" style="font-size:.8rem"></div>
    </div>
    <div class="table-responsive">
      <table class="table opr-table mb-0">
        <thead><tr>
          <th class="ps-4">Request Code</th>
          <th>Item</th>
          <th>Seats</th>
          <th>Original</th>
          <th>Discount</th>
          <th>Final Price</th>
          <th>Status</th>
          <th>Submitted</th>
          <th class="text-end pe-4">Actions</th>
        </tr></thead>
        <tbody id="oprTbody">
          <tr><td colspan="9" class="text-center py-4">
            <span class="spinner-border spinner-border-sm text-primary me-2"></span>Loading…
          </td></tr>
        </tbody>
      </table>
    </div>
    <!-- Pagination -->
    <div id="oprPagination" class="d-flex justify-content-center gap-2 p-3"></div>
  </div>

</div><!-- /canvas -->

<!-- ── New Request Modal ── -->
<div class="modal fade" id="oprNewModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header opr-modal-hdr border-0">
        <h6 class="modal-title fw-bold"><i class="bi bi-cart-plus-fill me-2"></i>New Purchase Request</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Request type tabs -->
        <div class="d-flex gap-1 p-1 mb-4" style="background:#f8fafc;border-radius:10px;width:fit-content">
          <button class="opr-tab active" id="rTypeStandard" onclick="oprSwitchType('standard')">Standard Request</button>
          <button class="opr-tab" id="rTypeCustom" onclick="oprSwitchType('custom')">Custom Quote</button>
        </div>

        <!-- Standard form -->
        <div id="rFormStandard">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Item Type <span class="text-danger">*</span></label>
              <select class="form-select" id="rItemType" onchange="oprOnItemTypeChange()">
                <option value="course">Single Course</option>
                <option value="bundle">Course Bundle</option>
              </select>
            </div>
            <div class="col-md-6" id="rCourseWrap">
              <label class="form-label small fw-semibold">Course <span class="text-danger">*</span></label>
              <select class="form-select" id="rCourseId" onchange="oprUpdatePriceHint()">
                <option value="">Loading…</option>
              </select>
            </div>
            <div class="col-md-6 d-none" id="rBundleWrap">
              <label class="form-label small fw-semibold">Bundle <span class="text-danger">*</span></label>
              <select class="form-select" id="rBundleId" onchange="oprUpdatePriceHint()">
                <option value="">Loading…</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Number of Staff / Seats <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="rSeats" min="1" value="1" oninput="oprUpdatePriceHint()">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Expected Start Date</label>
              <input type="date" class="form-control" id="rStartDate">
            </div>
            <div class="col-12">
              <!-- Pricing estimate hint -->
              <div class="opr-tier-hint" id="oprPriceHint">
                <i class="bi bi-calculator me-1"></i>
                Estimated price: <strong id="oprPriceHintAmt">—</strong>
                <span class="text-muted ms-2" id="oprPriceHintNote"></span>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Notes / Additional Requirements</label>
              <textarea class="form-control" id="rNotes" rows="3" placeholder="Any special requirements or notes for the admin…"></textarea>
            </div>
          </div>
        </div>

        <!-- Custom quote form -->
        <div id="rFormCustom" class="d-none">
          <div class="p-3 mb-4 rounded-3" style="background:#fef3c7;border:1px solid #fde68a">
            <div class="d-flex gap-2">
              <i class="bi bi-lightbulb-fill text-warning mt-1"></i>
              <div class="small">Use this form to request a custom pricing package for your organization. Our team will review and provide a tailored quote.</div>
            </div>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Number of Staff <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="rCustomStaff" min="1" placeholder="e.g. 50">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Budget (TZS) <span class="text-muted fw-normal">(optional)</span></label>
              <input type="text" class="form-control" id="rCustomBudget" placeholder="e.g. 5,000,000">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Expected Start Date</label>
              <input type="date" class="form-control" id="rCustomStart">
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Requirements &amp; Courses of Interest <span class="text-danger">*</span></label>
              <textarea class="form-control" id="rCustomReqs" rows="4" placeholder="Describe the courses needed, learning objectives, and any other details…"></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 bg-light">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary px-4 fw-semibold" id="oprSubmitBtn" onclick="oprSubmitNew()">
          <i class="bi bi-send-fill me-1"></i>Submit Request
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ── Detail Modal ── -->
<div class="modal fade" id="oprDetailModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 bg-light">
        <h6 class="modal-title fw-bold"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Request Detail</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="oprDetailBody">
        <div class="text-center py-4"><span class="spinner-border text-primary"></span></div>
      </div>
    </div>
  </div>
</div>

<script>
const OPR_AJAX = '../data_files/ajax/ajax_purchase_requests.php';
const esc = s => (s+'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
const fmt = (n,d=0) => Number(n||0).toLocaleString('en-US',{minimumFractionDigits:d,maximumFractionDigits:d});
const fmtDate = s => s ? new Date(s).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : '—';

let oprCurrentStatus = '', oprCurrentPage = 1, oprItems = {courses:[], bundles:[]}, oprRequestType = 'standard';
let oprRowData = [];

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
    return `<span class="opr-badge" style="background:${c.bg};color:${c.color}">${esc(c.label)}</span>`;
}

async function oprInit() {
    await oprLoadItems();
    oprLoad();
}

async function oprLoadItems() {
    const r = await fetch(`${OPR_AJAX}?action=list_items`).then(x=>x.json()).catch(()=>({}));
    oprItems.courses = r.courses || [];
    oprItems.bundles = r.bundles || [];
    oprPopulateCourseDropdown();
    oprPopulateBundleDropdown();
}

function oprPopulateCourseDropdown() {
    const sel = document.getElementById('rCourseId');
    sel.innerHTML = '<option value="">— Select Course —</option>';
    oprItems.courses.forEach(c => {
        const o = new Option(`${c.title} (TZS ${fmt(c.price,0)}/seat)`, c.id);
        o.dataset.price = c.price;
        sel.appendChild(o);
    });
}

function oprPopulateBundleDropdown() {
    const sel = document.getElementById('rBundleId');
    sel.innerHTML = '<option value="">— Select Bundle —</option>';
    oprItems.bundles.forEach(b => {
        const o = new Option(`${b.bundle_name} (TZS ${fmt(b.org_price,0)}/seat)`, b.id);
        o.dataset.price = b.org_price;
        sel.appendChild(o);
    });
}

async function oprLoad() {
    document.getElementById('oprTbody').innerHTML =
        '<tr><td colspan="9" class="text-center py-4"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Loading…</td></tr>';

    const params = new URLSearchParams({ action:'list_requests', status: oprCurrentStatus, page: oprCurrentPage });
    const r = await fetch(`${OPR_AJAX}?${params}`).then(x=>x.json()).catch(()=>({status:'error', data:[]}));

    if (r.status !== 'success') {
        document.getElementById('oprTbody').innerHTML = '<tr><td colspan="9" class="text-center text-danger py-4">Failed to load requests.</td></tr>';
        return;
    }

    // Update KPIs
    const st = r.stats || {};
    document.getElementById('kpiTotal').textContent   = fmt(st.total   || 0);
    document.getElementById('kpiPending').textContent = fmt(st.pending  || 0);
    document.getElementById('kpiActive').textContent  = fmt(st.active   || 0);
    document.getElementById('kpiSpent').textContent   = 'TZS ' + fmt(st.total_spent || 0);

    oprRowData = r.data || [];
    document.getElementById('oprCountBadge').textContent = `${r.total || 0} request(s)`;

    if (!oprRowData.length) {
        document.getElementById('oprTbody').innerHTML =
            '<tr><td colspan="9" class="text-center py-5"><div style="color:#94a3b8"><i class="bi bi-inbox" style="font-size:2rem"></i><div class="mt-2">No requests found</div></div></td></tr>';
        document.getElementById('oprPagination').innerHTML = '';
        return;
    }

    document.getElementById('oprTbody').innerHTML = oprRowData.map((row, idx) => {
        const itemName = row.course_title || row.bundle_name || (row.item_type === 'custom' ? 'Custom Quote' : '—');
        const discountTxt = row.discount_percent ? `${row.discount_percent}%` : '—';
        const canCancel   = row.status === 'pending';

        return `<tr>
          <td class="ps-4"><span class="fw-mono fw-semibold" style="font-size:.8rem;color:#4f46e5">${esc(row.request_code)}</span></td>
          <td>
            <div class="fw-semibold" style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${esc(itemName)}</div>
            <div style="font-size:.72rem;color:#94a3b8">${esc(row.item_type)}</div>
          </td>
          <td class="text-center">${fmt(row.seats_requested)}</td>
          <td>TZS ${fmt(row.original_price, 2)}</td>
          <td>${discountTxt}</td>
          <td><strong>TZS ${fmt(row.final_price || row.original_price, 2)}</strong></td>
          <td>${statusBadge(row.status)}</td>
          <td style="color:#64748b;font-size:.78rem">${fmtDate(row.created_at)}</td>
          <td class="text-end pe-4">
            <div class="d-flex gap-1 justify-content-end">
              <button class="opr-act" style="background:#eef2ff;color:#4f46e5" onclick="oprViewDetail(${idx})" title="View Detail"><i class="bi bi-eye-fill"></i></button>
              ${canCancel ? `<button class="opr-act" style="background:#fee2e2;color:#dc2626" onclick="oprCancel(${row.id},'${esc(row.request_code)}')" title="Cancel"><i class="bi bi-x-lg"></i></button>` : ''}
            </div>
          </td>
        </tr>`;
    }).join('');

    // Pagination
    const totalPages = Math.ceil(r.total / r.per);
    if (totalPages > 1) {
        let pg = '';
        for (let i = 1; i <= totalPages; i++) {
            pg += `<button class="btn btn-sm ${i === oprCurrentPage ? 'btn-primary' : 'btn-outline-secondary'}" onclick="oprGoPage(${i})">${i}</button>`;
        }
        document.getElementById('oprPagination').innerHTML = pg;
    } else {
        document.getElementById('oprPagination').innerHTML = '';
    }
}

window.oprSetTab = function(el) {
    document.querySelectorAll('#oprTabBar .opr-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    oprCurrentStatus = el.dataset.status;
    oprCurrentPage   = 1;
    oprLoad();
};

window.oprGoPage = function(p) { oprCurrentPage = p; oprLoad(); };

window.oprViewDetail = async function(idx) {
    const row = oprRowData[idx];
    document.getElementById('oprDetailBody').innerHTML = '<div class="text-center py-4"><span class="spinner-border text-primary"></span></div>';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('oprDetailModal')).show();

    const r = await fetch(`${OPR_AJAX}?action=get_request&id=${row.id}`).then(x=>x.json()).catch(()=>({status:'error'}));
    if (r.status !== 'success') {
        document.getElementById('oprDetailBody').innerHTML = '<div class="text-danger text-center py-4">Failed to load details.</div>';
        return;
    }
    const d = r.data;
    const h = r.history || [];
    const itemName = d.course_title || d.bundle_name || (d.item_type === 'custom' ? 'Custom Quote Request' : '—');

    const histHtml = h.map(e => {
        const actionIcons = { submitted:'bi-send-fill', reviewed:'bi-search', awaiting_payment:'bi-credit-card-fill',
            paid_and_activated:'bi-check-circle-fill', rejected:'bi-x-circle-fill', cancelled:'bi-slash-circle-fill' };
        const ico = actionIcons[e.action] || 'bi-clock-history';
        return `<div class="d-flex gap-3 mb-3">
          <div style="width:32px;height:32px;border-radius:50%;background:#eef2ff;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="bi ${ico} text-primary" style="font-size:.8rem"></i>
          </div>
          <div>
            <div class="fw-semibold" style="font-size:.82rem">${esc(e.actor_name || 'System')}</div>
            <div style="font-size:.75rem;color:#64748b">${esc(e.notes || e.action)}</div>
            <div style="font-size:.7rem;color:#94a3b8">${fmtDate(e.created_at)}</div>
          </div>
        </div>`;
    }).join('') || '<div class="text-muted small">No history available.</div>';

    document.getElementById('oprDetailBody').innerHTML = `
      <div class="row g-3 mb-4">
        <div class="col-sm-6"><div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #f1f5f9">
          <div class="small text-muted mb-1">Request Code</div>
          <div class="fw-bold" style="color:#4f46e5">${esc(d.request_code)}</div>
        </div></div>
        <div class="col-sm-6"><div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #f1f5f9">
          <div class="small text-muted mb-1">Status</div>
          ${statusBadge(d.status)}
        </div></div>
        <div class="col-sm-6"><div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #f1f5f9">
          <div class="small text-muted mb-1">Item</div>
          <div class="fw-semibold">${esc(itemName)}</div>
          <div class="small text-muted">${esc(d.item_type)}</div>
        </div></div>
        <div class="col-sm-6"><div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #f1f5f9">
          <div class="small text-muted mb-1">Seats Requested</div>
          <div class="fw-bold">${fmt(d.seats_requested)}</div>
        </div></div>
        <div class="col-sm-4"><div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #f1f5f9">
          <div class="small text-muted mb-1">Original Price</div>
          <div>TZS ${fmt(d.original_price, 2)}</div>
        </div></div>
        <div class="col-sm-4"><div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #f1f5f9">
          <div class="small text-muted mb-1">Discount</div>
          <div>${d.discount_name ? esc(d.discount_name) + ' (' + d.discount_percent + '%)' : '—'}</div>
        </div></div>
        <div class="col-sm-4"><div class="p-3 rounded-3" style="background:#f0fdf4;border:1px solid #bbf7d0">
          <div class="small text-muted mb-1">Final Price</div>
          <div class="fw-bold text-success">TZS ${fmt(d.final_price || d.original_price, 2)}</div>
        </div></div>
        ${d.expected_start_date ? `<div class="col-sm-6"><div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #f1f5f9"><div class="small text-muted mb-1">Expected Start</div><div>${fmtDate(d.expected_start_date)}</div></div></div>` : ''}
        ${d.admin_remarks ? `<div class="col-12"><div class="p-3 rounded-3" style="background:#fef3c7;border:1px solid #fde68a"><div class="small text-muted mb-1">Admin Remarks</div><div class="small">${esc(d.admin_remarks)}</div></div></div>` : ''}
        ${d.notes ? `<div class="col-12"><div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #f1f5f9"><div class="small text-muted mb-1">Notes</div><div class="small">${esc(d.notes)}</div></div></div>` : ''}
      </div>
      <div class="fw-semibold mb-3" style="font-size:.88rem"><i class="bi bi-clock-history me-2 text-primary"></i>Request History</div>
      ${histHtml}
    `;
};

window.oprCancel = async function(id, code) {
    const res = await Swal.fire({
        title: `Cancel ${code}?`, text: 'This request will be cancelled and cannot be resubmitted.',
        icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, Cancel',
        cancelButtonText: 'Keep It', confirmButtonColor: '#dc2626', reverseButtons: true,
    });
    if (!res.isConfirmed) return;
    const r = await oprPost({ action:'cancel_request', id });
    if (r.status === 'success') { oprToast('Request cancelled'); oprLoad(); }
    else oprToast(r.message || 'Failed', 'danger');
};

// ── New Request Modal ──
window.oprOpenNew = function() {
    ['rItemType','rCourseId','rBundleId','rSeats','rStartDate','rNotes','rCustomStaff','rCustomBudget','rCustomStart','rCustomReqs'].forEach(id => {
        const el = document.getElementById(id); if (!el) return;
        if (el.tagName === 'SELECT') el.selectedIndex = 0;
        else el.value = el.type === 'number' ? 1 : '';
    });
    document.getElementById('oprPriceHint').style.display = 'none';
    oprSwitchType('standard');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('oprNewModal')).show();
};

window.oprSwitchType = function(type) {
    oprRequestType = type;
    document.getElementById('rFormStandard').classList.toggle('d-none', type !== 'standard');
    document.getElementById('rFormCustom').classList.toggle('d-none',   type !== 'custom');
    document.getElementById('rTypeStandard').classList.toggle('active', type === 'standard');
    document.getElementById('rTypeCustom').classList.toggle('active',   type === 'custom');
};

window.oprOnItemTypeChange = function() {
    const t = document.getElementById('rItemType').value;
    document.getElementById('rCourseWrap').classList.toggle('d-none', t !== 'course');
    document.getElementById('rBundleWrap').classList.toggle('d-none', t !== 'bundle');
    oprUpdatePriceHint();
};

window.oprUpdatePriceHint = function() {
    const type  = document.getElementById('rItemType').value;
    const seats = parseInt(document.getElementById('rSeats').value) || 1;
    let pricePerSeat = 0;
    if (type === 'course') {
        const sel = document.getElementById('rCourseId');
        const opt = sel.options[sel.selectedIndex];
        pricePerSeat = parseFloat(opt?.dataset?.price || 0);
    } else {
        const sel = document.getElementById('rBundleId');
        const opt = sel.options[sel.selectedIndex];
        pricePerSeat = parseFloat(opt?.dataset?.price || 0);
    }
    const hint = document.getElementById('oprPriceHint');
    if (pricePerSeat > 0 && seats > 0) {
        const total = pricePerSeat * seats;
        document.getElementById('oprPriceHintAmt').textContent = 'TZS ' + fmt(total, 2);
        document.getElementById('oprPriceHintNote').textContent = `(${fmt(pricePerSeat,2)} x ${seats} seat${seats!==1?'s':''}, before any discount)`;
        hint.style.display = '';
    } else {
        hint.style.display = 'none';
    }
};

window.oprSubmitNew = async function() {
    const btn = document.getElementById('oprSubmitBtn');
    let payload = { action: 'submit_request' };

    if (oprRequestType === 'standard') {
        const type = document.getElementById('rItemType').value;
        const seats = parseInt(document.getElementById('rSeats').value) || 0;
        if (!seats || seats < 1) { oprToast('Number of seats is required', 'danger'); return; }
        if (type === 'course' && !document.getElementById('rCourseId').value) {
            oprToast('Please select a course', 'danger'); return;
        }
        if (type === 'bundle' && !document.getElementById('rBundleId').value) {
            oprToast('Please select a bundle', 'danger'); return;
        }
        payload = { ...payload, item_type: type,
            course_id: document.getElementById('rCourseId').value,
            bundle_id: document.getElementById('rBundleId').value,
            seats_requested: seats,
            expected_start: document.getElementById('rStartDate').value,
            notes: document.getElementById('rNotes').value };
    } else {
        const staff = parseInt(document.getElementById('rCustomStaff').value) || 0;
        const reqs  = document.getElementById('rCustomReqs').value.trim();
        if (!staff)  { oprToast('Number of staff is required', 'danger'); return; }
        if (!reqs)   { oprToast('Requirements are required for a custom quote', 'danger'); return; }
        payload = { ...payload, item_type: 'custom',
            staff_count: staff, seats_requested: staff,
            budget: document.getElementById('rCustomBudget').value,
            expected_start: document.getElementById('rCustomStart').value,
            requirements: reqs };
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Submitting…';

    const r = await oprPost(payload);
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-send-fill me-1"></i>Submit Request';

    if (r.status === 'success') {
        bootstrap.Modal.getInstance(document.getElementById('oprNewModal'))?.hide();
        oprToast(`Request ${r.request_code} submitted successfully`);
        oprLoad();
    } else {
        oprToast(r.message || 'Failed to submit request', 'danger');
    }
};

// ── Helpers ──
async function oprPost(data) {
    const fd = new FormData();
    Object.entries(data).forEach(([k,v]) => fd.append(k, v ?? ''));
    return fetch(OPR_AJAX, { method:'POST', body:fd }).then(x=>x.json()).catch(()=>({status:'error',message:'Network error'}));
}

function oprToast(msg, type='success') {
    const colors = { success:'#16a34a', danger:'#dc2626', warning:'#d97706', info:'#0891b2' };
    const icons  = { success:'bi-check-circle-fill', danger:'bi-exclamation-circle-fill', warning:'bi-exclamation-triangle-fill', info:'bi-info-circle-fill' };
    let c = document.getElementById('oprToastCon');
    if (!c) {
        c = Object.assign(document.createElement('div'), { id:'oprToastCon' });
        c.style.cssText = 'position:fixed;bottom:1.25rem;right:1.25rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem';
        document.body.appendChild(c);
    }
    const t = document.createElement('div');
    t.style.cssText = `background:${colors[type]||colors.success};color:#fff;padding:.65rem 1rem;border-radius:12px;font-size:.84rem;box-shadow:0 6px 20px rgba(0,0,0,.15);max-width:340px;display:flex;align-items:center;gap:.5rem`;
    t.innerHTML = `<i class="bi ${icons[type]||icons.success}"></i><span>${esc(msg)}</span>`;
    c.appendChild(t);
    setTimeout(() => { t.style.opacity='0'; t.style.transition='opacity .3s'; setTimeout(()=>t.remove(),300); }, 3500);
}

oprInit();
</script>
