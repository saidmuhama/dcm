<?php
if (($user_role ?? 0) != 4) { include('403.php'); return; }
$me = $_SESSION['usr_code'];
$orgRow = $db->query("
    SELECT o.org_code, o.org_name, o.max_users
    FROM tbl_organizations o
    INNER JOIN tbl_org_members m ON m.org_code = o.org_code
    WHERE m.usr_code = '$me' AND m.org_role = 'admin' AND m.status = 'active' AND o.deleted_at IS NULL
    LIMIT 1
")->fetch_assoc();
if (!$orgRow) { echo '<div class="alert alert-warning m-4">No organization linked to your account.</div>'; return; }
$maxUsers = $orgRow['max_users'] == -1 ? '∞' : number_format($orgRow['max_users']);
?>
<style>
/* ── Org Members (om-*) ── */
.om-hero { background: linear-gradient(135deg,#0f172a 0%,#1e1b4b 50%,#312e81 100%);
    padding: 2rem 0 3.5rem; position: relative; overflow: hidden; }
.om-hero::before { content:''; position:absolute; inset:0;
    background: radial-gradient(circle at 15% 60%,rgba(99,102,241,.2) 0%,transparent 55%),
                radial-gradient(circle at 85% 20%,rgba(139,92,246,.15) 0%,transparent 48%);
    pointer-events:none; }
.om-canvas { max-width:1280px; margin:-2rem auto 0; padding:0 1.25rem 3rem; position:relative; z-index:10; }
.om-stat-pill { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12);
    border-radius:12px; padding:.5rem 1rem; text-align:center; backdrop-filter:blur(4px); min-width:90px; }
.om-stat-pill .val { font-size:1.1rem; font-weight:800; color:#fff; line-height:1; }
.om-stat-pill .lbl { font-size:.65rem; color:rgba(255,255,255,.5); text-transform:uppercase; letter-spacing:.04em; margin-top:.15rem; }

/* cards */
.om-card { background:#fff; border-radius:18px; box-shadow:0 2px 14px rgba(0,0,0,.07);
    border:1px solid rgba(0,0,0,.05); overflow:hidden; }
.om-card-header { padding:.9rem 1.25rem; border-bottom:1px solid #f1f5f9;
    display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; }

/* member card */
.om-member-tile { background:#fff; border-radius:16px; border:1px solid rgba(0,0,0,.06);
    box-shadow:0 2px 10px rgba(0,0,0,.06); overflow:hidden;
    transition:transform .2s, box-shadow .2s; height:100%; display:flex; flex-direction:column; }
.om-member-tile:hover { transform:translateY(-3px); box-shadow:0 8px 28px rgba(0,0,0,.1); }
.om-avatar-lg { width:52px; height:52px; border-radius:50%; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:1.1rem; font-weight:800; color:#fff; }
.om-role-badge { font-size:.68rem; font-weight:700; padding:.22rem .65rem;
    border-radius:100px; text-transform:capitalize; }

/* list table */
.om-table th { font-size:.72rem; text-transform:uppercase; letter-spacing:.05em;
    color:#64748b; font-weight:700; padding:.75rem 1rem; white-space:nowrap;
    border-bottom:2px solid #f1f5f9; }
.om-table td { padding:.8rem 1rem; vertical-align:middle; font-size:.84rem;
    border-bottom:1px solid #f8fafc; }
.om-table tr:last-child td { border-bottom:none; }
.om-table tr:hover td { background:#f8f9ff; }
.om-avatar-sm { width:36px; height:36px; border-radius:50%; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:.75rem; font-weight:700; color:#fff; }

/* filter bar */
.om-filter-bar { background:#fff; border-radius:14px; box-shadow:0 2px 10px rgba(0,0,0,.06);
    border:1px solid rgba(0,0,0,.05); padding:.85rem 1.1rem; margin-bottom:1.25rem; }

/* skeleton */
.om-skel { background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);
    background-size:200% 100%; animation:om-skel 1.5s infinite; border-radius:8px; }
@keyframes om-skel { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* modal */
.om-modal-header { background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; }

/* action btns */
.om-act-btn { width:30px; height:30px; border-radius:8px; border:none; display:inline-flex;
    align-items:center; justify-content:center; font-size:.8rem; cursor:pointer;
    transition:all .15s; flex-shrink:0; }

/* tabs in modal */
.om-tab-btn { padding:.45rem 1rem; border-radius:8px; border:none; background:transparent;
    font-size:.82rem; font-weight:600; color:#64748b; cursor:pointer; transition:all .15s; }
.om-tab-btn.active { background:#eef2ff; color:#4f46e5; }

@media (prefers-color-scheme:dark) {
    .om-card, .om-member-tile, .om-filter-bar { background:#1e293b; border-color:rgba(255,255,255,.06); }
    .om-card-header, .om-table th { border-color:rgba(255,255,255,.06); }
    .om-table td { border-color:rgba(255,255,255,.04); }
    .om-table tr:hover td { background:rgba(99,102,241,.05); }
    .om-skel { background:linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%); background-size:200% 100%; }
}
</style>

<!-- HERO -->
<div class="om-hero">
  <div class="container-xl position-relative" style="z-index:2">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb mb-0" style="font-size:.75rem">
        <li class="breadcrumb-item"><a href="?view=3002" class="text-white-50 text-decoration-none">Dashboard</a></li>
        <li class="breadcrumb-item active" style="color:rgba(255,255,255,.5)">Members</li>
      </ol>
    </nav>
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3">
      <div class="flex-grow-1">
        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
          <h4 class="text-white fw-bold mb-0"><i class="bi bi-people-fill me-2"></i>Members</h4>
        </div>
        <div style="font-size:.8rem;color:rgba(255,255,255,.5)"><?= htmlspecialchars($orgRow['org_name']) ?></div>
      </div>
      <div class="d-flex gap-2 flex-wrap mt-2 mt-md-0">
        <div class="om-stat-pill"><div class="val" id="omPillTotal">—</div><div class="lbl">Total / <?= $maxUsers ?></div></div>
        <div class="om-stat-pill"><div class="val" id="omPillActive">—</div><div class="lbl">Active</div></div>
        <div class="om-stat-pill"><div class="val" id="omPillInst">—</div><div class="lbl">Instructors</div></div>
        <div class="om-stat-pill"><div class="val" id="omPillStu">—</div><div class="lbl">Students</div></div>
      </div>
      <div class="d-flex gap-2 flex-shrink-0">
        <button class="btn btn-sm fw-semibold px-3" style="background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:10px" onclick="omOpenImport()">
          <i class="bi bi-upload me-1"></i>Import CSV
        </button>
        <button class="btn btn-sm fw-semibold px-3" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:10px" onclick="omOpenAdd()">
          <i class="bi bi-person-plus-fill me-1"></i>Add Member
        </button>
      </div>
    </div>
  </div>
</div>

<!-- CANVAS -->
<div class="om-canvas">

  <!-- Filter bar -->
  <div class="om-filter-bar d-flex flex-wrap gap-2 align-items-center">
    <div class="input-group input-group-sm flex-grow-1" style="min-width:200px;max-width:300px">
      <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
      <input type="search" id="omSearch" class="form-control border-start-0" placeholder="Search name, email, ID…">
    </div>
    <select id="omRoleFilter" class="form-select form-select-sm" style="width:auto">
      <option value="">All Roles</option>
      <option value="admin">Admin</option>
      <option value="coordinator">Coordinator</option>
      <option value="instructor">Instructor</option>
      <option value="student">Student</option>
      <option value="staff">Staff</option>
    </select>
    <select id="omStatusFilter" class="form-select form-select-sm" style="width:auto">
      <option value="">All Status</option>
      <option value="active">Active</option>
      <option value="suspended">Suspended</option>
    </select>
    <select id="omDeptFilter" class="form-select form-select-sm" style="width:auto">
      <option value="">All Departments</option>
    </select>
    <div class="ms-auto d-flex gap-1">
      <button class="btn btn-sm btn-outline-secondary active" id="viewGrid" onclick="setView('grid')" title="Grid view"><i class="bi bi-grid-3x3-gap"></i></button>
      <button class="btn btn-sm btn-outline-secondary" id="viewList" onclick="setView('list')" title="List view"><i class="bi bi-list-ul"></i></button>
    </div>
  </div>

  <!-- Grid view -->
  <div id="omGridWrap">
    <div class="row g-3" id="omGridView">
      <?php for($i=0;$i<8;$i++): ?>
      <div class="col-sm-6 col-md-4 col-lg-3">
        <div class="om-member-tile p-3">
          <div class="d-flex align-items-center gap-2 mb-3">
            <div class="om-skel" style="width:52px;height:52px;border-radius:50%;flex-shrink:0"></div>
            <div style="flex:1"><div class="om-skel mb-1" style="height:13px;width:80%"></div><div class="om-skel" style="height:10px;width:55%"></div></div>
          </div>
          <div class="om-skel mb-2" style="height:10px;width:60%"></div>
          <div class="om-skel" style="height:10px;width:40%"></div>
        </div>
      </div>
      <?php endfor; ?>
    </div>
  </div>

  <!-- List view -->
  <div id="omListWrap" class="d-none">
    <div class="om-card">
      <div class="table-responsive">
        <table class="table om-table mb-0">
          <thead><tr>
            <th class="ps-4">Member</th><th>Role</th><th>Department</th>
            <th>Employee ID</th><th>Status</th><th>Joined</th><th class="text-end pe-4">Actions</th>
          </tr></thead>
          <tbody id="omListTbody"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Empty -->
  <div id="omEmpty" class="d-none text-center py-5">
    <div style="width:72px;height:72px;background:#f1f5f9;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem">
      <i class="bi bi-people" style="font-size:1.8rem;color:#94a3b8"></i>
    </div>
    <div class="fw-semibold" style="color:#1e293b">No members found</div>
    <div class="text-muted small">Try adjusting your filters or add a new member</div>
  </div>

</div><!-- /canvas -->

<!-- ── Add Member Modal ── -->
<div class="modal fade" id="addMemberModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header om-modal-header border-0">
        <h6 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2"></i>Add Member</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Tab switcher -->
        <div class="d-flex gap-1 p-1 mb-4" style="background:#f8fafc;border-radius:10px;width:fit-content">
          <button class="om-tab-btn active" id="tabExistingBtn" onclick="omSwitchTab('existing')">Add Existing User</button>
          <button class="om-tab-btn" id="tabCreateBtn" onclick="omSwitchTab('create')">Create New Account</button>
        </div>

        <!-- Existing user tab -->
        <div id="tabExisting">
          <p class="text-muted small mb-3">Add an existing platform user by their email address.</p>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="amEmail" placeholder="user@example.com">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Role in Organization</label>
              <select class="form-select" id="amRole">
                <option value="student">Student</option>
                <option value="instructor">Instructor</option>
                <option value="coordinator">Coordinator</option>
                <option value="staff">Staff</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Department</label>
              <select class="form-select" id="amDept"><option value="">— None —</option></select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Employee / Student ID</label>
              <input type="text" class="form-control" id="amEmpId" placeholder="Optional">
            </div>
          </div>
        </div>

        <!-- Create new tab -->
        <div id="tabCreate" class="d-none">
          <p class="text-muted small mb-3">Create a new account and add them to your organization. Default password: <code>DigitalClass@123</code></p>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">First Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="amFirstName" placeholder="First Name">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Last Name</label>
              <input type="text" class="form-control" id="amLastName" placeholder="Last Name">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="amNewEmail" placeholder="email@example.com">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Phone</label>
              <input type="text" class="form-control" id="amPhone" placeholder="+255 700 000 000">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Role in Organization</label>
              <select class="form-select" id="amRoleCreate">
                <option value="student">Student</option>
                <option value="instructor">Instructor</option>
                <option value="coordinator">Coordinator</option>
                <option value="staff">Staff</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Department</label>
              <select class="form-select" id="amDeptCreate"><option value="">— None —</option></select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Employee / Student ID</label>
              <input type="text" class="form-control" id="amEmpIdCreate" placeholder="Optional">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Custom Password <span class="text-muted fw-normal">(optional)</span></label>
              <input type="password" class="form-control" id="amPassword" placeholder="Leave blank for default">
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 bg-light">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary px-4 fw-semibold" id="amSubmitBtn" onclick="omSubmitAdd()">
          <i class="bi bi-check2 me-1"></i>Add Member
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ── Import CSV Modal ── -->
<div class="modal fade" id="importModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header om-modal-header border-0">
        <h6 class="modal-title fw-bold"><i class="bi bi-file-earmark-arrow-up-fill me-2"></i>Import Members via CSV</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-start gap-3 p-3 mb-4" style="background:#eef2ff;border-radius:12px;border:1px solid #c7d2fe">
          <i class="bi bi-info-circle-fill text-primary mt-1"></i>
          <div class="small">
            <strong>CSV Format:</strong> <code>first_name, last_name, email, org_role, dept_code, employee_id</code><br>
            <code>org_role</code> values: admin, coordinator, instructor, student, staff<br>
            New accounts get default password <code>DigitalClass@123</code>. Existing users are added automatically.
          </div>
        </div>
        <div class="mb-3">
          <button class="btn btn-sm btn-outline-secondary" onclick="omDownloadTemplate(event)">
            <i class="bi bi-download me-1"></i>Download Template
          </button>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Select CSV File</label>
          <input type="file" class="form-control" id="csvFile" accept=".csv">
        </div>
        <div id="importPreview"></div>
      </div>
      <div class="modal-footer border-0 bg-light">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary px-4 fw-semibold" id="btnImport" onclick="omSubmitImport()" disabled>
          <i class="bi bi-upload me-1"></i>Import
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ── Edit Member Modal ── -->
<div class="modal fade" id="editMemberModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 bg-light">
        <h6 class="modal-title fw-bold"><i class="bi bi-pencil-fill me-2 text-primary"></i>Edit Member</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="emUsrCode">
        <div id="emMemberInfo" class="d-flex align-items-center gap-3 p-3 mb-4 rounded-3" style="background:#f8fafc;border:1px solid #f1f5f9"></div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Role</label>
            <select class="form-select" id="emRole">
              <option value="student">Student</option>
              <option value="instructor">Instructor</option>
              <option value="coordinator">Coordinator</option>
              <option value="staff">Staff</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Status</label>
            <select class="form-select" id="emStatus">
              <option value="active">Active</option>
              <option value="suspended">Suspended</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Department</label>
            <select class="form-select" id="emDept"><option value="">— None —</option></select>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Employee / Student ID</label>
            <input type="text" class="form-control" id="emEmpId">
          </div>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button class="btn btn-outline-warning btn-sm me-auto" onclick="omResetPassword()">
          <i class="bi bi-key me-1"></i>Reset Password
        </button>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary px-4" onclick="omSubmitEdit()">Save Changes</button>
      </div>
    </div>
  </div>
</div>

<script>
const OM_AJAX = '../data_files/ajax/ajax_org_admin.php';
let omView = 'grid', omDepts = [], omSearchTimer;

const roleColors = {admin:'#ef4444',coordinator:'#f59e0b',instructor:'#06b6d4',student:'#6366f1',staff:'#64748b'};
const roleBg     = {admin:'#fee2e2',coordinator:'#fef3c7',instructor:'#cffafe',student:'#eef2ff',staff:'#f1f5f9'};
const esc = s => (s+'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
const fmtDate = s => s ? new Date(s).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : '—';

async function omInit() {
    const dr = await fetch(`${OM_AJAX}?action=list_departments`).then(x=>x.json()).catch(()=>({}));
    omDepts = dr.departments ?? [];
    ['omDeptFilter','amDept','amDeptCreate','emDept'].forEach(id => {
        const el = document.getElementById(id); if(!el) return;
        omDepts.forEach(d => el.appendChild(new Option(d.dept_name, d.id)));
    });
    document.getElementById('omSearch').addEventListener('input', () => {
        clearTimeout(omSearchTimer); omSearchTimer = setTimeout(omLoadMembers, 300);
    });
    ['omRoleFilter','omStatusFilter','omDeptFilter'].forEach(id =>
        document.getElementById(id).addEventListener('change', omLoadMembers)
    );
    omLoadMembers();
}

async function omLoadMembers() {
    const params = new URLSearchParams({
        action:'list_members',
        q: document.getElementById('omSearch').value,
        role: document.getElementById('omRoleFilter').value,
        status: document.getElementById('omStatusFilter').value,
        dept: document.getElementById('omDeptFilter').value,
    });
    const r = await fetch(`${OM_AJAX}?${params}`).then(x=>x.json()).catch(()=>({}));
    const members = r.members ?? [];
    const counts  = r.counts  ?? {};

    document.getElementById('omPillTotal').textContent = counts.total ?? members.length;
    document.getElementById('omPillActive').textContent = counts.active ?? '—';
    document.getElementById('omPillInst').textContent = counts.instructors ?? '—';
    document.getElementById('omPillStu').textContent = counts.students ?? '—';

    document.getElementById('omEmpty').classList.toggle('d-none', members.length > 0);
    if (omView === 'grid') renderGrid(members);
    else renderList(members);
}

function renderGrid(members) {
    document.getElementById('omGridWrap').classList.remove('d-none');
    document.getElementById('omListWrap').classList.add('d-none');
    if (!members.length) { document.getElementById('omGridView').innerHTML = ''; return; }
    document.getElementById('omGridView').innerHTML = members.map(m => {
        const initials = ((m.first_name||'')[0]+(m.last_name||'')[0]).toUpperCase();
        const col = roleColors[m.org_role] || '#94a3b8';
        const bg  = roleBg[m.org_role]    || '#f1f5f9';
        const statusDot = m.status === 'active'
            ? `<span style="width:8px;height:8px;background:#22c55e;border-radius:50%;display:inline-block;margin-right:.3rem"></span>`
            : `<span style="width:8px;height:8px;background:#f59e0b;border-radius:50%;display:inline-block;margin-right:.3rem"></span>`;
        return `
        <div class="col-sm-6 col-md-4 col-lg-3">
          <div class="om-member-tile">
            <div class="p-3 flex-grow-1">
              <div class="d-flex align-items-center gap-2 mb-3">
                <div class="om-avatar-lg" style="background:${col}">${initials}</div>
                <div style="min-width:0;flex:1">
                  <div class="fw-semibold" style="font-size:.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${esc(m.first_name)} ${esc(m.last_name)}</div>
                  <div class="text-muted" style="font-size:.7rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${esc(m.email)}</div>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <span class="om-role-badge" style="background:${bg};color:${col}">${esc(m.org_role)}</span>
                <span style="font-size:.7rem;color:#64748b">${statusDot}${m.status}</span>
              </div>
              ${m.dept_name ? `<div style="font-size:.72rem;color:#64748b"><i class="bi bi-building me-1"></i>${esc(m.dept_name)}</div>` : ''}
              ${m.employee_id ? `<div style="font-size:.7rem;color:#94a3b8;margin-top:.2rem"><i class="bi bi-tag me-1"></i>${esc(m.employee_id)}</div>` : ''}
            </div>
            <div class="d-flex border-top" style="border-color:#f1f5f9!important">
              <button class="om-act-btn flex-fill justify-content-center" style="background:none;border-right:1px solid #f1f5f9;border-radius:0;color:#6366f1" onclick='omOpenEdit(${JSON.stringify(m)})' title="Edit"><i class="bi bi-pencil-fill"></i></button>
              <button class="om-act-btn flex-fill justify-content-center" style="background:none;border-right:1px solid #f1f5f9;border-radius:0;color:#d97706" onclick="omResetPasswordFor('${esc(m.usr_code)}')" title="Reset Password"><i class="bi bi-key-fill"></i></button>
              <button class="om-act-btn flex-fill justify-content-center" style="background:none;border-radius:0;color:#dc2626" onclick="omRemove('${esc(m.usr_code)}','${esc(m.first_name+' '+m.last_name)}')" title="Remove"><i class="bi bi-person-dash-fill"></i></button>
            </div>
          </div>
        </div>`;
    }).join('');
}

function renderList(members) {
    document.getElementById('omGridWrap').classList.add('d-none');
    document.getElementById('omListWrap').classList.remove('d-none');
    if (!members.length) { document.getElementById('omListTbody').innerHTML = ''; return; }
    document.getElementById('omListTbody').innerHTML = members.map(m => {
        const initials = ((m.first_name||'')[0]+(m.last_name||'')[0]).toUpperCase();
        const col = roleColors[m.org_role] || '#94a3b8';
        const bg  = roleBg[m.org_role]    || '#f1f5f9';
        const stBg = m.status==='active' ? '#dcfce7' : '#fef3c7';
        const stCl = m.status==='active' ? '#166534' : '#92400e';
        return `<tr>
          <td class="ps-4">
            <div class="d-flex align-items-center gap-2">
              <div class="om-avatar-sm" style="background:${col}">${initials}</div>
              <div>
                <div class="fw-semibold" style="color:#1e293b">${esc(m.first_name)} ${esc(m.last_name)}</div>
                <div style="font-size:.72rem;color:#94a3b8">${esc(m.email)}</div>
              </div>
            </div>
          </td>
          <td><span class="om-role-badge" style="background:${bg};color:${col}">${esc(m.org_role)}</span></td>
          <td style="color:#64748b">${esc(m.dept_name||'—')}</td>
          <td style="color:#64748b;font-size:.8rem">${esc(m.employee_id||'—')}</td>
          <td><span class="om-role-badge" style="background:${stBg};color:${stCl}">${m.status}</span></td>
          <td style="color:#94a3b8;font-size:.78rem">${fmtDate(m.joined_at)}</td>
          <td class="text-end pe-4">
            <div class="d-flex gap-1 justify-content-end">
              <button class="om-act-btn" style="background:#eef2ff;color:#6366f1" onclick='omOpenEdit(${JSON.stringify(m)})' title="Edit"><i class="bi bi-pencil-fill"></i></button>
              <button class="om-act-btn" style="background:#fef3c7;color:#d97706" onclick="omResetPasswordFor('${esc(m.usr_code)}')" title="Reset Password"><i class="bi bi-key-fill"></i></button>
              <button class="om-act-btn" style="background:#fee2e2;color:#dc2626" onclick="omRemove('${esc(m.usr_code)}','${esc(m.first_name+' '+m.last_name)}')" title="Remove"><i class="bi bi-person-dash-fill"></i></button>
            </div>
          </td>
        </tr>`;
    }).join('');
}

function setView(v) {
    omView = v;
    document.getElementById('viewGrid').classList.toggle('active', v==='grid');
    document.getElementById('viewList').classList.toggle('active', v==='list');
    omLoadMembers();
}

// ── Tab switching in Add modal ──
let omAddTab = 'existing';
function omSwitchTab(tab) {
    omAddTab = tab;
    document.getElementById('tabExisting').classList.toggle('d-none', tab!=='existing');
    document.getElementById('tabCreate').classList.toggle('d-none',   tab!=='create');
    document.getElementById('tabExistingBtn').classList.toggle('active', tab==='existing');
    document.getElementById('tabCreateBtn').classList.toggle('active',   tab==='create');
    document.getElementById('amSubmitBtn').innerHTML = tab==='existing'
        ? '<i class="bi bi-check2 me-1"></i>Add Member'
        : '<i class="bi bi-person-plus-fill me-1"></i>Create & Add';
}

function omOpenAdd() {
    omSwitchTab('existing');
    new bootstrap.Modal(document.getElementById('addMemberModal')).show();
}

async function omSubmitAdd() {
    const btn = document.getElementById('amSubmitBtn');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';
    let r;
    if (omAddTab === 'existing') {
        r = await post({ action:'add_member', email:v('amEmail'), org_role:v('amRole'), dept_id:v('amDept'), employee_id:v('amEmpId') });
    } else {
        r = await post({ action:'create_member', first_name:v('amFirstName'), last_name:v('amLastName'),
            email:v('amNewEmail'), phone:v('amPhone'), password:v('amPassword'),
            org_role:v('amRoleCreate'), dept_id:v('amDeptCreate'), employee_id:v('amEmpIdCreate') });
    }
    btn.disabled = false;
    btn.innerHTML = omAddTab==='existing' ? '<i class="bi bi-check2 me-1"></i>Add Member' : '<i class="bi bi-person-plus-fill me-1"></i>Create & Add';
    if (r.status==='success') {
        bootstrap.Modal.getInstance(document.getElementById('addMemberModal'))?.hide();
        omToast(r.message || 'Member added successfully');
        omLoadMembers();
    } else {
        omToast(r.message || 'Failed to add member', 'danger');
    }
}

// ── Edit ──
function omOpenEdit(m) {
    document.getElementById('emUsrCode').value = m.usr_code;
    document.getElementById('emRole').value   = m.org_role;
    document.getElementById('emEmpId').value  = m.employee_id || '';
    document.getElementById('emStatus').value = m.status;
    document.getElementById('emDept').value   = m.dept_id || '';
    const col = roleColors[m.org_role] || '#94a3b8';
    const initials = ((m.first_name||'')[0]+(m.last_name||'')[0]).toUpperCase();
    document.getElementById('emMemberInfo').innerHTML = `
        <div style="width:44px;height:44px;border-radius:50%;background:${col};display:flex;align-items:center;justify-content:center;font-weight:800;color:#fff;font-size:1rem;flex-shrink:0">${initials}</div>
        <div>
          <div class="fw-semibold">${esc(m.first_name)} ${esc(m.last_name)}</div>
          <div style="font-size:.75rem;color:#64748b">${esc(m.email)}</div>
        </div>`;
    new bootstrap.Modal(document.getElementById('editMemberModal')).show();
}

async function omSubmitEdit() {
    const r = await post({ action:'update_member', usr_code:v('emUsrCode'), org_role:v('emRole'), dept_id:v('emDept'), employee_id:v('emEmpId'), status:v('emStatus') });
    if (r.status==='success') {
        bootstrap.Modal.getInstance(document.getElementById('editMemberModal'))?.hide();
        omToast('Member updated');
        omLoadMembers();
    } else omToast(r.message||'Error', 'danger');
}

// ── Remove ──
async function omRemove(usrCode, name) {
    const res = await Swal.fire({ title:`Remove ${name}?`, text:'They will lose access to the organization.',
        icon:'warning', showCancelButton:true, confirmButtonText:'Remove', confirmButtonColor:'#dc2626', reverseButtons:true });
    if (!res.isConfirmed) return;
    const r = await post({ action:'remove_member', usr_code:usrCode });
    if (r.status==='success') { omToast('Member removed'); omLoadMembers(); }
    else omToast(r.message||'Error', 'danger');
}

// ── Reset password ──
async function omResetPassword() { omResetPasswordFor(v('emUsrCode')); }
async function omResetPasswordFor(usrCode) {
    const res = await Swal.fire({ title:'Reset Password?', text:'Password will be reset to DigitalClass@123',
        icon:'question', showCancelButton:true, confirmButtonText:'Reset', confirmButtonColor:'#d97706', reverseButtons:true });
    if (!res.isConfirmed) return;
    const r = await post({ action:'reset_password', usr_code:usrCode });
    if (r.status==='success') omToast('Password reset to default');
    else omToast(r.message||'Error', 'danger');
}

// ── CSV Import ──
function omOpenImport() {
    document.getElementById('csvFile').value = '';
    document.getElementById('importPreview').innerHTML = '';
    document.getElementById('btnImport').disabled = true;
    new bootstrap.Modal(document.getElementById('importModal')).show();
}
document.getElementById('csvFile').addEventListener('change', function() {
    if (!this.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const lines = e.target.result.split('\n').filter(l=>l.trim());
        if (lines.length < 2) { document.getElementById('importPreview').innerHTML='<div class="text-danger small">File appears empty.</div>'; return; }
        const preview = lines.slice(0,6).map(l => `<div class="font-monospace border-bottom py-1" style="font-size:.72rem">${esc(l)}</div>`).join('');
        document.getElementById('importPreview').innerHTML = `
            <div class="small text-muted mb-2"><strong>${lines.length-1}</strong> data rows detected (showing up to 5):</div>
            <div class="border rounded p-2" style="background:#f8fafc">${preview}</div>`;
        document.getElementById('btnImport').disabled = false;
    };
    reader.readAsText(this.files[0]);
});
async function omSubmitImport() {
    const file = document.getElementById('csvFile').files[0];
    if (!file) return;
    const fd = new FormData(); fd.append('action','import_members'); fd.append('csv_file', file);
    const btn = document.getElementById('btnImport');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Importing…';
    const r = await fetch(OM_AJAX,{method:'POST',body:fd}).then(x=>x.json()).catch(()=>({status:'error'}));
    btn.disabled = false; btn.innerHTML = '<i class="bi bi-upload me-1"></i>Import';
    if (r.status==='success') {
        bootstrap.Modal.getInstance(document.getElementById('importModal'))?.hide();
        omToast(`Imported: ${r.imported} · Skipped: ${r.skipped}`);
        omLoadMembers();
    } else omToast(r.message||'Import failed', 'danger');
}
function omDownloadTemplate(e) {
    e.preventDefault();
    const csv = 'first_name,last_name,email,org_role,dept_code,employee_id\nJohn,Doe,john@example.com,student,,STU001\nJane,Smith,jane@example.com,instructor,,INS001';
    const a = document.createElement('a'); a.href = URL.createObjectURL(new Blob([csv],{type:'text/csv'}));
    a.download = 'member_import_template.csv'; a.click();
}

// ── Helpers ──
const v = id => document.getElementById(id)?.value ?? '';
async function post(data) {
    const fd = new FormData();
    Object.entries(data).forEach(([k,val]) => fd.append(k, val??''));
    return fetch(OM_AJAX,{method:'POST',body:fd}).then(x=>x.json()).catch(()=>({status:'error',message:'Network error'}));
}
function omToast(msg, type='success') {
    const icons = {success:'bi-check-circle-fill', danger:'bi-exclamation-circle-fill', warning:'bi-exclamation-triangle-fill'};
    const colors = {success:'#16a34a', danger:'#dc2626', warning:'#d97706'};
    const c = document.getElementById('omToastCon') || (() => {
        const el = Object.assign(document.createElement('div'),{id:'omToastCon'});
        el.style.cssText = 'position:fixed;bottom:1.25rem;right:1.25rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem';
        document.body.appendChild(el); return el;
    })();
    const t = document.createElement('div');
    t.style.cssText = `background:${colors[type]||colors.success};color:#fff;padding:.65rem 1rem;border-radius:12px;font-size:.84rem;box-shadow:0 6px 20px rgba(0,0,0,.15);max-width:340px;display:flex;align-items:center;gap:.5rem`;
    t.innerHTML = `<i class="bi ${icons[type]||icons.success}"></i><span>${esc(msg)}</span>`;
    c.appendChild(t);
    setTimeout(()=>{ t.style.opacity='0'; t.style.transition='opacity .3s'; setTimeout(()=>t.remove(),300); }, 3500);
}

omInit();
</script>
