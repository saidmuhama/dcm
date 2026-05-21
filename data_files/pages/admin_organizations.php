<?php
$user_role = $user_role ?? 0;
if ($user_role != 5) { include('403.php'); return; }

require_once __DIR__ . '/../config/url_crypt_config.php';
?>
<style>
.org-stat-card{border-radius:16px;padding:1.25rem 1.5rem;color:#fff;position:relative;overflow:hidden;min-height:90px;}
.org-stat-card .stat-val{font-size:2rem;font-weight:800;line-height:1;}
.org-stat-card .stat-lbl{font-size:.75rem;opacity:.85;margin-top:.25rem;}
.org-stat-card .stat-icon{position:absolute;right:1rem;top:50%;transform:translateY(-50%);font-size:3rem;opacity:.18;}
.org-type-badge{font-size:.65rem;font-weight:700;padding:.2rem .55rem;border-radius:20px;text-transform:uppercase;letter-spacing:.04em;}
.org-status-badge{font-size:.68rem;font-weight:700;padding:.22rem .6rem;border-radius:20px;}
.table-org th{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;white-space:nowrap;}
.table-org td{vertical-align:middle;font-size:.84rem;}
.org-logo-sm{width:34px;height:34px;border-radius:8px;object-fit:cover;background:#f1f5f9;display:flex;align-items:center;justify-content:center;overflow:hidden;}
</style>

<div class="container-fluid px-3 py-3">

  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0 small">
      <li class="breadcrumb-item"><a href="?view=admin_dashboard">Admin</a></li>
      <li class="breadcrumb-item active">Organizations</li>
    </ol>
  </nav>

  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <h5 class="fw-bold mb-0"><i class="bi bi-building me-2 text-primary"></i>Organization Management</h5>
      <small class="text-muted">Manage schools, colleges, companies &amp; institutions</small>
    </div>
    <button class="btn btn-primary px-4" style="border-radius:10px" onclick="openCreate()">
      <i class="bi bi-plus-circle me-2"></i>New Organization
    </button>
  </div>

  <!-- Stats -->
  <div class="row g-3 mb-4" id="statsRow">
    <div class="col-6 col-md-3">
      <div class="org-stat-card" style="background:linear-gradient(135deg,#6366f1,#4f46e5)">
        <div class="stat-val" id="sTotal">—</div>
        <div class="stat-lbl">Total Organizations</div>
        <i class="bi bi-building stat-icon"></i>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="org-stat-card" style="background:linear-gradient(135deg,#059669,#047857)">
        <div class="stat-val" id="sActive">—</div>
        <div class="stat-lbl">Active</div>
        <i class="bi bi-check-circle stat-icon"></i>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="org-stat-card" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
        <div class="stat-val" id="sSuspended">—</div>
        <div class="stat-lbl">Suspended</div>
        <i class="bi bi-pause-circle stat-icon"></i>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="org-stat-card" style="background:linear-gradient(135deg,#3b82f6,#2563eb)">
        <div class="stat-val" id="sMembers">—</div>
        <div class="stat-lbl">Total Active Members</div>
        <i class="bi bi-people stat-icon"></i>
      </div>
    </div>
  </div>

  <!-- Filters -->
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
      <div class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Search name, email, code…">
          </div>
        </div>
        <div class="col-6 col-md-2">
          <select id="typeFilter" class="form-select form-select-sm">
            <option value="">All Types</option>
            <option value="school">School</option>
            <option value="college">College</option>
            <option value="company">Company</option>
            <option value="institution">Institution</option>
            <option value="training_center">Training Center</option>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <select id="statusFilter" class="form-select form-select-sm">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
            <option value="expired">Expired</option>
            <option value="pending">Pending</option>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <select id="planFilter" class="form-select form-select-sm">
            <option value="">All Plans</option>
          </select>
        </div>
        <div class="col-6 col-md-2 text-end">
          <button class="btn btn-sm btn-outline-secondary" onclick="loadOrgs(1)"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Table -->
  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-org mb-0">
          <thead class="border-bottom">
            <tr>
              <th class="ps-3">Organization</th>
              <th>Type</th>
              <th>Admin</th>
              <th class="text-center">Members</th>
              <th class="text-center">Courses</th>
              <th>Plan</th>
              <th>Status</th>
              <th>License Expiry</th>
              <th class="text-end pe-3">Actions</th>
            </tr>
          </thead>
          <tbody id="orgBody">
            <tr><td colspan="9" class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm"></div></td></tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent border-top d-flex align-items-center justify-content-between py-2 flex-wrap gap-2">
      <small id="pageInfo" class="text-muted"></small>
      <div id="pageBtns" class="d-flex gap-1"></div>
    </div>
  </div>
</div>

<!-- ── Create Modal ──────────────────────────────────────────────── -->
<div class="modal fade" id="createOrgModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0" style="background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff">
        <h6 class="modal-title fw-bold"><i class="bi bi-building-add me-2"></i>New Organization</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12"><h6 class="fw-semibold text-muted mb-0" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.06em">Organization Details</h6><hr class="mt-1 mb-2"></div>
          <div class="col-md-8">
            <label class="form-label small fw-semibold">Organization Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="c_name" placeholder="e.g. Springfield University">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold">Type <span class="text-danger">*</span></label>
            <select class="form-select" id="c_type">
              <option value="school">School</option>
              <option value="college">College</option>
              <option value="company">Company</option>
              <option value="institution">Institution</option>
              <option value="training_center">Training Center</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Organization Email</label>
            <input type="email" class="form-control" id="c_email" placeholder="org@example.com">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Phone</label>
            <input type="text" class="form-control" id="c_phone" placeholder="+255 700 000 000">
          </div>
          <div class="col-md-8">
            <label class="form-label small fw-semibold">Address</label>
            <input type="text" class="form-control" id="c_address" placeholder="Physical address">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold">Country</label>
            <input type="text" class="form-control" id="c_country" placeholder="Tanzania">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Subscription Plan</label>
            <select class="form-select" id="c_plan"></select>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">License Expiry Date</label>
            <input type="date" class="form-control" id="c_license">
          </div>
          <div class="col-12">
            <label class="form-label small fw-semibold">Notes</label>
            <textarea class="form-control" id="c_notes" rows="2" placeholder="Internal notes…"></textarea>
          </div>

          <div class="col-12 mt-2"><h6 class="fw-semibold text-muted mb-0" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.06em">Organization Admin Account</h6><hr class="mt-1 mb-2"></div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">First Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="c_ad_fname" placeholder="Admin first name">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Last Name</label>
            <input type="text" class="form-control" id="c_ad_lname" placeholder="Admin last name">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Admin Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="c_ad_email" placeholder="admin@org.com">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Admin Phone</label>
            <input type="text" class="form-control" id="c_ad_phone" placeholder="+255 700 000 000">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="c_ad_pass" placeholder="Min 8 characters">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Confirm Password</label>
            <input type="password" class="form-control" id="c_ad_pass2" placeholder="Repeat password">
          </div>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary px-4" id="createBtn" onclick="submitCreate()"><i class="bi bi-check2 me-1"></i>Create Organization</button>
      </div>
    </div>
  </div>
</div>

<!-- ── Edit Modal ────────────────────────────────────────────────── -->
<div class="modal fade" id="editOrgModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 bg-light">
        <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-2"></i>Edit Organization</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="e_oid">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label small fw-semibold">Organization Name</label>
            <input type="text" class="form-control" id="e_name">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold">Type</label>
            <select class="form-select" id="e_type">
              <option value="school">School</option>
              <option value="college">College</option>
              <option value="company">Company</option>
              <option value="institution">Institution</option>
              <option value="training_center">Training Center</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Email</label>
            <input type="email" class="form-control" id="e_email">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Phone</label>
            <input type="text" class="form-control" id="e_phone">
          </div>
          <div class="col-md-8">
            <label class="form-label small fw-semibold">Address</label>
            <input type="text" class="form-control" id="e_address">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold">Country</label>
            <input type="text" class="form-control" id="e_country">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Domain</label>
            <input type="text" class="form-control" id="e_domain" placeholder="org.example.com">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Status</label>
            <select class="form-select" id="e_status">
              <option value="active">Active</option>
              <option value="suspended">Suspended</option>
              <option value="expired">Expired</option>
              <option value="pending">Pending</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold">Plan</label>
            <select class="form-select" id="e_plan"></select>
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold">License Expiry</label>
            <input type="date" class="form-control" id="e_license">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold">Max Users</label>
            <input type="number" class="form-control" id="e_max_users" min="-1">
          </div>
          <div class="col-12">
            <label class="form-label small fw-semibold">Notes</label>
            <textarea class="form-control" id="e_notes" rows="2"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary px-4" onclick="submitEdit()"><i class="bi bi-check2 me-1"></i>Save Changes</button>
      </div>
    </div>
  </div>
</div>

<script>
const AJAX = '../data_files/ajax/ajax_organizations.php';
let currentPage = 1, plans = [], searchTimer;

// ── Load plans for dropdowns ────────────────────────────────────
(function loadPlans(){
  $.post(AJAX, {action:'plans'}, function(r){
    if(r.status !== 'success') return;
    plans = r.data;
    let opts = '<option value="">No Plan</option>';
    r.data.forEach(p => opts += `<option value="${p.id}">${p.plan_name} (${p.max_users < 0 ? 'Unlimited' : p.max_users} users)</option>`);
    $('#c_plan, #e_plan').html(opts);
    let fo = '<option value="">All Plans</option>';
    r.data.forEach(p => fo += `<option value="${p.id}">${p.plan_name}</option>`);
    $('#planFilter').html(fo);
  }, 'json');
})();

// ── Load stats ──────────────────────────────────────────────────
(function loadStats(){
  $.post(AJAX, {action:'stats'}, function(r){
    if(r.status !== 'success') return;
    const d = r.data;
    $('#sTotal').text(d.total); $('#sActive').text(d.active);
    $('#sSuspended').text(d.suspended); $('#sMembers').text(Number(d.members).toLocaleString());
  }, 'json');
})();

// ── Load orgs ───────────────────────────────────────────────────
function loadOrgs(page = 1) {
  currentPage = page;
  $('#orgBody').html('<tr><td colspan="9" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>');
  $.post(AJAX, {
    action:'list', page,
    search: $('#searchInput').val(),
    type:   $('#typeFilter').val(),
    status: $('#statusFilter').val(),
    plan:   $('#planFilter').val(),
  }, function(r) {
    if(r.status !== 'success'){ $('#orgBody').html('<tr><td colspan="9" class="text-center text-danger py-4">Failed to load</td></tr>'); return; }
    let html = '';
    if(!r.data.length){ html = '<tr><td colspan="9" class="text-center text-muted py-5"><i class="bi bi-building opacity-30" style="font-size:2rem"></i><div class="mt-2">No organizations found</div></td></tr>'; }
    r.data.forEach(o => {
      const typeColors = {school:'#6366f1',college:'#8b5cf6',company:'#3b82f6',institution:'#0891b2',training_center:'#059669'};
      const tc = typeColors[o.org_type] || '#64748b';
      const typeLabel = {school:'School',college:'College',company:'Company',institution:'Institution',training_center:'Training Ctr'}[o.org_type] || o.org_type;
      const stBg = {active:'#dcfce7',suspended:'#fef3c7',expired:'#fee2e2',pending:'#dbeafe'}[o.status] || '#f1f5f9';
      const stCl = {active:'#166534',suspended:'#92400e',expired:'#991b1b',pending:'#1e40af'}[o.status] || '#475569';
      const exp  = o.license_expires_at ? new Date(o.license_expires_at).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}) : '—';
      const logoHtml = o.logo
        ? `<img src="../${o.logo}" class="org-logo-sm me-2" style="width:34px;height:34px;border-radius:8px;object-fit:cover">`
        : `<div class="org-logo-sm me-2 d-inline-flex" style="background:${tc}20;color:${tc};font-weight:700;font-size:.75rem">${o.org_name.charAt(0).toUpperCase()}</div>`;
      html += `<tr>
        <td class="ps-3">
          <div class="d-flex align-items-center">
            ${logoHtml}
            <div>
              <div class="fw-semibold" style="font-size:.84rem">${o.org_name}</div>
              <small class="text-muted">${o.org_code}</small>
            </div>
          </div>
        </td>
        <td><span class="org-type-badge" style="background:${tc}18;color:${tc}">${typeLabel}</span></td>
        <td><div style="font-size:.82rem">${o.admin_name||'—'}</div><small class="text-muted">${o.email||''}</small></td>
        <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary fw-semibold">${o.member_count||0}</span></td>
        <td class="text-center"><span class="badge bg-info bg-opacity-10 text-info fw-semibold">${o.course_count||0}</span></td>
        <td><small>${o.plan_name||'—'}</small></td>
        <td><span class="org-status-badge" style="background:${stBg};color:${stCl}">${o.status}</span></td>
        <td><small class="${o.license_expires_at && new Date(o.license_expires_at)<new Date()?'text-danger fw-semibold':''}">${exp}</small></td>
        <td class="text-end pe-3">
          <div class="d-flex gap-1 justify-content-end">
            <a href="?view=admin_org_detail&oid=${encodeURIComponent(o.oid_token)}" class="btn btn-sm btn-primary" title="Manage"><i class="bi bi-folder2-open"></i></a>
            <button class="btn btn-sm btn-outline-secondary" onclick='openEdit(${JSON.stringify(o)})' title="Edit"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-outline-${o.status==='active'?'warning':'success'}" onclick="toggleStatus('${o.oid_token}','${o.status==='active'?'suspended':'active'}','${o.org_name}')" title="${o.status==='active'?'Suspend':'Activate'}">
              <i class="bi bi-${o.status==='active'?'pause-circle':'play-circle'}"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="deleteOrg('${o.oid_token}','${o.org_name}')" title="Delete"><i class="bi bi-trash"></i></button>
          </div>
        </td>
      </tr>`;
    });
    $('#orgBody').html(html);

    const total = r.total, per = r.per, pages = Math.ceil(total/per);
    const from = Math.min((page-1)*per+1, total), to = Math.min(page*per, total);
    $('#pageInfo').text(total > 0 ? `Showing ${from}–${to} of ${total}` : 'No results');
    let btns = '';
    for(let p=1;p<=pages;p++) btns += `<button class="btn btn-sm ${p===page?'btn-primary':'btn-outline-secondary'} page-btn" data-p="${p}">${p}</button>`;
    $('#pageBtns').html(btns);
  }, 'json');
}

$(document).on('click','.page-btn', function(){ loadOrgs($(this).data('p')); });
$('#searchInput').on('input',function(){ clearTimeout(searchTimer); searchTimer=setTimeout(()=>loadOrgs(1),350); });
$('#typeFilter,#statusFilter,#planFilter').on('change',()=>loadOrgs(1));

// ── Create ──────────────────────────────────────────────────────
function openCreate(){ new bootstrap.Modal('#createOrgModal').show(); }

function submitCreate(){
  const pass = $('#c_ad_pass').val(), pass2 = $('#c_ad_pass2').val();
  if(pass !== pass2){ Swal.fire('Error','Passwords do not match','error'); return; }
  const btn = $('#createBtn').prop('disabled',true).text('Creating…');
  $.ajax({url:AJAX, method:'POST', contentType:'application/json',
    data: JSON.stringify({
      action:'create',
      org_name: $('#c_name').val(), org_type: $('#c_type').val(),
      email: $('#c_email').val(), phone: $('#c_phone').val(),
      address: $('#c_address').val(), country: $('#c_country').val(),
      plan_id: $('#c_plan').val(), license_expires_at: $('#c_license').val(),
      notes: $('#c_notes').val(),
      admin_first_name: $('#c_ad_fname').val(), admin_last_name: $('#c_ad_lname').val(),
      admin_email: $('#c_ad_email').val(), admin_phone: $('#c_ad_phone').val(),
      admin_password: pass,
    }),
    success(r){
      btn.prop('disabled',false).html('<i class="bi bi-check2 me-1"></i>Create Organization');
      if(r.status==='success'){
        bootstrap.Modal.getInstance('#createOrgModal').hide();
        Swal.fire({icon:'success',title:'Organization Created',text:`Code: ${r.org_code}`,timer:3000,showConfirmButton:false});
        loadOrgs(1); loadStats();
      } else {
        Swal.fire('Error', r.message || 'Failed', 'error');
      }
    }, dataType:'json'
  });
}

// ── Edit ────────────────────────────────────────────────────────
function openEdit(o){
  $('#e_oid').val(o.oid_token);
  $('#e_name').val(o.org_name); $('#e_type').val(o.org_type);
  $('#e_email').val(o.email||''); $('#e_phone').val(o.phone||'');
  $('#e_address').val(o.address||''); $('#e_country').val(o.country||'');
  $('#e_domain').val(o.domain||''); $('#e_status').val(o.status);
  $('#e_plan').val(o.plan_id||''); $('#e_license').val(o.license_expires_at||'');
  $('#e_max_users').val(o.max_users||50); $('#e_notes').val(o.notes||'');
  new bootstrap.Modal('#editOrgModal').show();
}

function submitEdit(){
  $.ajax({url:AJAX, method:'POST', contentType:'application/json',
    data: JSON.stringify({
      action:'update', oid: $('#e_oid').val(),
      org_name:$('#e_name').val(), org_type:$('#e_type').val(),
      email:$('#e_email').val(), phone:$('#e_phone').val(),
      address:$('#e_address').val(), country:$('#e_country').val(),
      domain:$('#e_domain').val(), status:$('#e_status').val(),
      plan_id:$('#e_plan').val(), license_expires_at:$('#e_license').val(),
      max_users:$('#e_max_users').val(), notes:$('#e_notes').val(),
    }),
    success(r){
      if(r.status==='success'){
        bootstrap.Modal.getInstance('#editOrgModal').hide();
        Swal.fire({icon:'success',title:'Updated',timer:1800,showConfirmButton:false});
        loadOrgs(currentPage);
      } else { Swal.fire('Error',r.message||'Failed','error'); }
    }, dataType:'json'
  });
}

// ── Toggle status ───────────────────────────────────────────────
function toggleStatus(oidToken, newStatus, name){
  const label = newStatus === 'suspended' ? 'Suspend' : 'Activate';
  Swal.fire({
    title:`${label} "${name}"?`,
    text: newStatus==='suspended' ? 'Users will lose access immediately.' : 'Organization will be reactivated.',
    icon: newStatus==='suspended' ? 'warning' : 'question',
    showCancelButton:true, confirmButtonText:`Yes, ${label}`,
    confirmButtonColor: newStatus==='suspended' ? '#f59e0b' : '#059669',
  }).then(r => {
    if(!r.isConfirmed) return;
    $.ajax({url:AJAX, method:'POST', contentType:'application/json',
      data: JSON.stringify({action:'toggle_status', oid:oidToken, status:newStatus}),
      success(res){ if(res.status==='success') loadOrgs(currentPage); else Swal.fire('Error',res.message,'error'); },
      dataType:'json'
    });
  });
}

// ── Delete ──────────────────────────────────────────────────────
function deleteOrg(oidToken, name){
  Swal.fire({
    title:`Delete "${name}"?`,
    text:'All org data will be archived. Members retain their accounts.',
    icon:'warning', showCancelButton:true,
    confirmButtonText:'Yes, Delete', confirmButtonColor:'#dc2626',
  }).then(r => {
    if(!r.isConfirmed) return;
    $.ajax({url:AJAX, method:'POST', contentType:'application/json',
      data: JSON.stringify({action:'delete', oid:oidToken}),
      success(res){
        if(res.status==='success'){ Swal.fire({icon:'success',title:'Deleted',timer:1500,showConfirmButton:false}); loadOrgs(1); loadStats(); }
        else Swal.fire('Error',res.message,'error');
      }, dataType:'json'
    });
  });
}

loadOrgs(1);
</script>
