<?php
require_once __DIR__ . '/../config/url_crypt_config.php';

$oid = decryptURLId($_GET['oid'] ?? '', ctx: 'org');
if (!$oid) { include('404.php'); return; }

$oidSafe = (int)$oid;
$orgRow = $db->query("SELECT o.*, p.plan_name FROM tbl_organizations o LEFT JOIN tbl_org_plans p ON p.id = o.plan_id WHERE o.id = $oidSafe AND o.deleted_at IS NULL")->fetch_assoc();
if (!$orgRow) { include('404.php'); return; }

$oidToken = encryptURLId($oidSafe, ctx: 'org');
?>
<style>
.aod-tab-content { min-height: 400px; }
.aod-stat-card   { border-radius: .75rem; }
.aod-member-row:hover { background: rgba(var(--bs-primary-rgb),.04); }
.aod-dept-card   { transition: box-shadow .15s ease; }
.aod-dept-card:hover { box-shadow: 0 .25rem .75rem rgba(0,0,0,.1) !important; }
.badge-status-active    { background:#d1fae5;color:#065f46; }
.badge-status-suspended { background:#fee2e2;color:#991b1b; }
.badge-status-expired   { background:#fef3c7;color:#92400e; }
.badge-status-pending   { background:#ede9fe;color:#5b21b6; }
</style>

<div class="container-fluid px-3 pt-3 pb-5" id="aodRoot">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="../data_files/?view=admin_organizations">Organizations</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($orgRow['org_name']) ?></li>
        </ol>
    </nav>

    <!-- Org Header Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-start gap-4 flex-wrap">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <?php if ($orgRow['logo']): ?>
                    <img src="uploads/org_logos/<?= htmlspecialchars(basename($orgRow['logo'])) ?>" alt="Logo"
                         style="width:80px;height:80px;object-fit:contain;border-radius:.5rem;border:1px solid #e5e7eb;">
                    <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center rounded text-white fw-bold fs-4"
                         style="width:80px;height:80px;background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                        <?= strtoupper(substr($orgRow['org_name'], 0, 2)) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <!-- Info -->
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h5 class="fw-bold mb-0"><?= htmlspecialchars($orgRow['org_name']) ?></h5>
                        <span class="badge badge-status-<?= $orgRow['status'] ?> rounded-pill px-3">
                            <?= ucfirst($orgRow['status']) ?>
                        </span>
                        <span class="badge bg-secondary bg-opacity-15 text-secondary rounded-pill px-2">
                            <?= ucfirst(str_replace('_',' ',$orgRow['org_type'])) ?>
                        </span>
                    </div>
                    <div class="text-muted small mb-2"><?= htmlspecialchars($orgRow['org_code']) ?></div>
                    <div class="d-flex gap-3 flex-wrap small">
                        <?php if ($orgRow['email']): ?>
                        <span><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($orgRow['email']) ?></span>
                        <?php endif; ?>
                        <?php if ($orgRow['phone']): ?>
                        <span><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($orgRow['phone']) ?></span>
                        <?php endif; ?>
                        <?php if ($orgRow['country']): ?>
                        <span><i class="bi bi-globe me-1"></i><?= htmlspecialchars($orgRow['country']) ?></span>
                        <?php endif; ?>
                        <?php if ($orgRow['plan_name']): ?>
                        <span><i class="bi bi-box me-1"></i><?= htmlspecialchars($orgRow['plan_name']) ?> Plan</span>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Actions -->
                <div class="d-flex gap-2 flex-shrink-0">
                    <button class="btn btn-outline-primary btn-sm" onclick="aodOpenEdit()">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                    <?php if ($orgRow['status'] === 'active'): ?>
                    <button class="btn btn-outline-warning btn-sm" onclick="aodToggle('suspended')">
                        <i class="bi bi-pause-circle me-1"></i>Suspend
                    </button>
                    <?php else: ?>
                    <button class="btn btn-outline-success btn-sm" onclick="aodToggle('active')">
                        <i class="bi bi-play-circle me-1"></i>Activate
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4" id="aodStats">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm aod-stat-card text-center py-3">
                <div class="h4 fw-bold text-primary mb-0" id="statMembers">—</div>
                <div class="small text-muted">Total Members</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm aod-stat-card text-center py-3">
                <div class="h4 fw-bold text-success mb-0" id="statActive">—</div>
                <div class="small text-muted">Active Users</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm aod-stat-card text-center py-3">
                <div class="h4 fw-bold text-info mb-0" id="statCourses">—</div>
                <div class="small text-muted">Courses Granted</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm aod-stat-card text-center py-3">
                <div class="h4 fw-bold text-warning mb-0" id="statDepts">—</div>
                <div class="small text-muted">Departments</div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="aodTabs">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabOverview">Overview</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabMembers" onclick="aodLoadMembers()">Members</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabDepts" onclick="aodLoadDepts()">Departments</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabCourses" onclick="aodLoadCourses()">Course Access</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabActivity" onclick="aodLoadActivity()">Activity Log</button></li>
    </ul>

    <div class="tab-content aod-tab-content">

        <!-- ── Overview Tab ── -->
        <div class="tab-pane fade show active" id="tabOverview">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent fw-semibold small">Organization Details</div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    <tr><td class="text-muted" style="width:40%">Org Code</td><td class="fw-medium font-monospace"><?= htmlspecialchars($orgRow['org_code']) ?></td></tr>
                                    <tr><td class="text-muted">Type</td><td><?= ucfirst(str_replace('_',' ',$orgRow['org_type'])) ?></td></tr>
                                    <tr><td class="text-muted">Status</td><td><span class="badge badge-status-<?= $orgRow['status'] ?>"><?= ucfirst($orgRow['status']) ?></span></td></tr>
                                    <tr><td class="text-muted">Plan</td><td><?= htmlspecialchars($orgRow['plan_name'] ?? '—') ?></td></tr>
                                    <tr><td class="text-muted">Max Users</td><td><?= $orgRow['max_users'] == -1 ? 'Unlimited' : number_format($orgRow['max_users']) ?></td></tr>
                                    <tr><td class="text-muted">Storage Limit</td><td><?= $orgRow['storage_limit_gb'] == -1 ? 'Unlimited' : $orgRow['storage_limit_gb'].' GB' ?></td></tr>
                                    <tr><td class="text-muted">License Expires</td><td><?= $orgRow['license_expires_at'] ? date('M j, Y', strtotime($orgRow['license_expires_at'])) : '—' ?></td></tr>
                                    <tr><td class="text-muted">Domain</td><td><?= $orgRow['domain'] ? htmlspecialchars($orgRow['domain']) : '—' ?></td></tr>
                                    <tr><td class="text-muted">Created</td><td><?= date('M j, Y', strtotime($orgRow['created_at'])) ?></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-transparent fw-semibold small">Contact & Location</div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    <tr><td class="text-muted" style="width:40%">Email</td><td><?= $orgRow['email'] ? htmlspecialchars($orgRow['email']) : '—' ?></td></tr>
                                    <tr><td class="text-muted">Phone</td><td><?= $orgRow['phone'] ? htmlspecialchars($orgRow['phone']) : '—' ?></td></tr>
                                    <tr><td class="text-muted">Country</td><td><?= $orgRow['country'] ? htmlspecialchars($orgRow['country']) : '—' ?></td></tr>
                                    <tr><td class="text-muted">Address</td><td><?= $orgRow['address'] ? htmlspecialchars($orgRow['address']) : '—' ?></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if ($orgRow['notes']): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent fw-semibold small">Admin Notes</div>
                        <div class="card-body small text-muted"><?= nl2br(htmlspecialchars($orgRow['notes'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ── Members Tab ── -->
        <div class="tab-pane fade" id="tabMembers">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <input type="search" id="memberSearch" class="form-control form-control-sm" style="max-width:260px" placeholder="Search members…" oninput="aodFilterMembers()">
                <div class="d-flex gap-2">
                    <select id="memberRoleFilter" class="form-select form-select-sm" style="width:auto" onchange="aodFilterMembers()">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="coordinator">Coordinator</option>
                        <option value="instructor">Instructor</option>
                        <option value="student">Student</option>
                        <option value="staff">Staff</option>
                    </select>
                    <button class="btn btn-sm btn-outline-danger" onclick="aodGrantCourse()" title="Grant course access">
                        <i class="bi bi-plus-lg me-1"></i>Grant Course
                    </button>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light">
                            <tr>
                                <th>Member</th><th>Role</th><th>Department</th><th>Status</th><th>Joined</th><th></th>
                            </tr>
                        </thead>
                        <tbody id="membersTbody">
                            <tr><td colspan="6" class="text-center py-4 text-muted">Loading…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ── Departments Tab ── -->
        <div class="tab-pane fade" id="tabDepts">
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-primary btn-sm" onclick="aodCreateDept()">
                    <i class="bi bi-plus-lg me-1"></i>Add Department
                </button>
            </div>
            <div class="row g-3" id="deptsGrid">
                <div class="col-12 text-center py-5 text-muted">Loading…</div>
            </div>
        </div>

        <!-- ── Course Access Tab ── -->
        <div class="tab-pane fade" id="tabCourses">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <input type="search" id="courseSearch" class="form-control form-control-sm" style="max-width:260px" placeholder="Search courses…" oninput="aodFilterCourses()">
                <button class="btn btn-primary btn-sm" onclick="aodGrantCourse()">
                    <i class="bi bi-plus-lg me-1"></i>Grant Course Access
                </button>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light">
                            <tr><th>Course</th><th>Granted By</th><th>Granted</th><th>Expires</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody id="coursesTbody">
                            <tr><td colspan="6" class="text-center py-4 text-muted">Loading…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ── Activity Log Tab ── -->
        <div class="tab-pane fade" id="tabActivity">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0 small">
                        <thead class="bg-light">
                            <tr><th>Time</th><th>Actor</th><th>Action</th><th>Target</th><th>IP</th></tr>
                        </thead>
                        <tbody id="activityTbody">
                            <tr><td colspan="5" class="text-center py-4 text-muted">Loading…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div><!-- /tab-content -->
</div>

<!-- Grant Course Modal -->
<div class="modal fade" id="grantCourseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-semibold">Grant Course Access</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="search" id="gcSearch" class="form-control form-control-sm mb-3" placeholder="Search courses…" oninput="gcFilter()">
                <div id="gcList" style="max-height:400px;overflow-y:auto"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary btn-sm" onclick="gcSave()">Grant Selected</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Org Modal -->
<div class="modal fade" id="editOrgModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-semibold">Edit Organization</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small">Org Name</label>
                        <input type="text" class="form-control form-control-sm" id="eoOrgName" value="<?= htmlspecialchars($orgRow['org_name']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Type</label>
                        <select class="form-select form-select-sm" id="eoOrgType">
                            <?php foreach(['school','college','company','institution','training_center'] as $t): ?>
                            <option value="<?= $t ?>" <?= $orgRow['org_type'] === $t ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$t)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Email</label>
                        <input type="email" class="form-control form-control-sm" id="eoEmail" value="<?= htmlspecialchars($orgRow['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Phone</label>
                        <input type="text" class="form-control form-control-sm" id="eoPhone" value="<?= htmlspecialchars($orgRow['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Country</label>
                        <input type="text" class="form-control form-control-sm" id="eoCountry" value="<?= htmlspecialchars($orgRow['country'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Domain</label>
                        <input type="text" class="form-control form-control-sm" id="eoDomain" value="<?= htmlspecialchars($orgRow['domain'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Status</label>
                        <select class="form-select form-select-sm" id="eoStatus">
                            <?php foreach(['active','suspended','expired','pending'] as $s): ?>
                            <option value="<?= $s ?>" <?= $orgRow['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">License Expires</label>
                        <input type="date" class="form-control form-control-sm" id="eoLicense" value="<?= $orgRow['license_expires_at'] ?? '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Max Users</label>
                        <input type="number" class="form-control form-control-sm" id="eoMaxUsers" value="<?= $orgRow['max_users'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Storage Limit (GB)</label>
                        <input type="number" class="form-control form-control-sm" id="eoStorage" value="<?= $orgRow['storage_limit_gb'] ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Address</label>
                        <textarea class="form-control form-control-sm" id="eoAddress" rows="2"><?= htmlspecialchars($orgRow['address'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Notes</label>
                        <textarea class="form-control form-control-sm" id="eoNotes" rows="2"><?= htmlspecialchars($orgRow['notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary btn-sm" onclick="aodSubmitEdit()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
const AOD_OID   = '<?= $oidToken ?>';
const AOD_AJAX  = '../data_files/ajax/ajax_organizations.php';
let allCoursesData = [];
let allMembersData = [];
let membersLoaded  = false;
let deptsLoaded    = false;
let coursesLoaded  = false;
let activityLoaded = false;

// ── Stats ──
async function aodLoadStats() {
    const r = await fetch(`${AOD_AJAX}?action=stats&oid=${encodeURIComponent(AOD_OID)}`).then(x=>x.json()).catch(()=>({}));
    if (r.status === 'success') {
        document.getElementById('statMembers').textContent = r.total_members ?? '—';
        document.getElementById('statActive').textContent  = r.active_members ?? '—';
        document.getElementById('statCourses').textContent = r.total_courses ?? '—';
        document.getElementById('statDepts').textContent   = r.total_depts ?? '—';
    }
}

// ── Members ──
async function aodLoadMembers() {
    if (membersLoaded) return;
    membersLoaded = true;
    const r = await fetch(`${AOD_AJAX}?action=list_members&oid=${encodeURIComponent(AOD_OID)}`).then(x=>x.json()).catch(()=>({}));
    allMembersData = r.members ?? [];
    renderMembers(allMembersData);
}
function renderMembers(list) {
    const roleBadge = {admin:'bg-danger',coordinator:'bg-warning text-dark',instructor:'bg-info text-dark',student:'bg-primary',staff:'bg-secondary'};
    const tb = document.getElementById('membersTbody');
    if (!list.length) { tb.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No members found.</td></tr>'; return; }
    tb.innerHTML = list.map(m => `
        <tr class="aod-member-row">
            <td>
                <div class="fw-medium">${esc(m.full_name)}</div>
                <div class="text-muted" style="font-size:.75rem">${esc(m.email)}</div>
            </td>
            <td><span class="badge ${roleBadge[m.org_role]||'bg-secondary'}">${esc(m.org_role)}</span></td>
            <td>${esc(m.dept_name||'—')}</td>
            <td><span class="badge ${m.status==='active'?'bg-success':'bg-secondary'}">${esc(m.status)}</span></td>
            <td>${fmtDate(m.joined_at)}</td>
            <td>
                <button class="btn btn-outline-danger btn-sm py-0 px-2" onclick="aodRemoveMember('${esc(m.usr_code)}', '${esc(m.full_name)}')" title="Remove">
                    <i class="bi bi-person-dash"></i>
                </button>
            </td>
        </tr>`).join('');
}
function aodFilterMembers() {
    const q    = document.getElementById('memberSearch').value.toLowerCase();
    const role = document.getElementById('memberRoleFilter').value;
    renderMembers(allMembersData.filter(m =>
        (!q    || m.full_name.toLowerCase().includes(q) || m.email.toLowerCase().includes(q)) &&
        (!role || m.org_role === role)
    ));
}
async function aodRemoveMember(usrCode, name) {
    if (!confirm(`Remove ${name} from this organization?`)) return;
    const r = await post({action:'remove_member', oid: AOD_OID, usr_code: usrCode});
    if (r.status === 'success') { toast('Member removed'); membersLoaded=false; aodLoadMembers(); }
    else toast(r.message||'Error', 'danger');
}

// ── Departments ──
async function aodLoadDepts() {
    if (deptsLoaded) return;
    deptsLoaded = true;
    renderDepts(null);
    const r = await fetch(`${AOD_AJAX}?action=list_depts&oid=${encodeURIComponent(AOD_OID)}`).then(x=>x.json()).catch(()=>({}));
    renderDepts(r.departments ?? []);
}
function renderDepts(list) {
    const g = document.getElementById('deptsGrid');
    if (!list) { g.innerHTML = '<div class="col-12 text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Loading…</div>'; return; }
    if (!list.length) { g.innerHTML = '<div class="col-12 text-center py-5 text-muted"><i class="bi bi-building display-6 d-block mb-2 opacity-50"></i>No departments yet.</div>'; return; }
    g.innerHTML = list.map(d => `
        <div class="col-md-4">
            <div class="card border-0 shadow-sm aod-dept-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div>
                            <div class="fw-semibold">${esc(d.dept_name)}</div>
                            <div class="text-muted small">${esc(d.dept_code||'')}</div>
                        </div>
                        <span class="badge ${d.status==='active'?'bg-success':'bg-secondary'} ms-2">${esc(d.status)}</span>
                    </div>
                    ${d.description ? `<p class="small text-muted mb-2">${esc(d.description)}</p>` : ''}
                    <div class="small text-muted">${d.member_count||0} member${d.member_count!=1?'s':''}</div>
                </div>
            </div>
        </div>`).join('');
}

// ── Course Access ──
async function aodLoadCourses() {
    if (coursesLoaded) return;
    coursesLoaded = true;
    const r = await fetch(`${AOD_AJAX}?action=list_courses&oid=${encodeURIComponent(AOD_OID)}`).then(x=>x.json()).catch(()=>({}));
    renderCourses(r.courses ?? []);
}
function renderCourses(list) {
    const tb = document.getElementById('coursesTbody');
    if (!list.length) { tb.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No course access granted yet.</td></tr>'; return; }
    tb.innerHTML = list.map(c => `
        <tr>
            <td class="fw-medium">${esc(c.title)}</td>
            <td>${esc(c.granted_by_name||'—')}</td>
            <td>${fmtDate(c.granted_at)}</td>
            <td>${c.expires_at ? fmtDate(c.expires_at) : 'Never'}</td>
            <td><span class="badge ${c.is_active?'bg-success':'bg-secondary'}">${c.is_active?'Active':'Inactive'}</span></td>
            <td>
                <button class="btn btn-outline-danger btn-sm py-0 px-2" onclick="aodRevokeCourse(${c.course_id}, '${esc(c.title)}')" title="Revoke">
                    <i class="bi bi-x-lg"></i>
                </button>
            </td>
        </tr>`).join('');
}
function aodFilterCourses() {
    const q = document.getElementById('courseSearch').value.toLowerCase();
    // re-fetch and filter client side if needed — for now filter DOM
    document.querySelectorAll('#coursesTbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
async function aodRevokeCourse(courseId, title) {
    if (!confirm(`Revoke access to "${title}"?`)) return;
    const r = await post({action:'revoke_course', oid: AOD_OID, course_id: courseId});
    if (r.status === 'success') { toast('Course access revoked'); coursesLoaded=false; aodLoadCourses(); }
    else toast(r.message||'Error', 'danger');
}

// ── Grant Course Modal ──
async function aodGrantCourse() {
    if (!allCoursesData.length) {
        const r = await fetch(`${AOD_AJAX}?action=all_courses`).then(x=>x.json()).catch(()=>({}));
        allCoursesData = r.courses ?? [];
    }
    gcFilter();
    new bootstrap.Modal(document.getElementById('grantCourseModal')).show();
}
function gcFilter() {
    const q = document.getElementById('gcSearch').value.toLowerCase();
    const filtered = allCoursesData.filter(c => c.title.toLowerCase().includes(q));
    const el = document.getElementById('gcList');
    if (!filtered.length) { el.innerHTML = '<div class="text-muted text-center py-3">No courses found.</div>'; return; }
    el.innerHTML = filtered.map(c => `
        <div class="form-check border rounded p-3 mb-2">
            <input class="form-check-input" type="checkbox" value="${c.id}" id="gc_${c.id}">
            <label class="form-check-label w-100" for="gc_${c.id}">
                <div class="fw-medium small">${esc(c.title)}</div>
                <div class="text-muted" style="font-size:.72rem">${esc(c.instructor_name||'')} &middot; ${esc(c.status)}</div>
            </label>
        </div>`).join('');
}
async function gcSave() {
    const ids = [...document.querySelectorAll('#gcList input:checked')].map(i => +i.value);
    if (!ids.length) { toast('Select at least one course', 'warning'); return; }
    const r = await post({action:'grant_course', oid: AOD_OID, course_ids: ids});
    if (r.status === 'success') {
        toast('Course access granted');
        bootstrap.Modal.getInstance(document.getElementById('grantCourseModal'))?.hide();
        coursesLoaded = false;
        aodLoadCourses();
    } else toast(r.message||'Error', 'danger');
}

// ── Activity Log ──
async function aodLoadActivity() {
    if (activityLoaded) return;
    activityLoaded = true;
    const r = await fetch(`${AOD_AJAX}?action=activity_log&oid=${encodeURIComponent(AOD_OID)}`).then(x=>x.json()).catch(()=>({}));
    const logs = r.logs ?? [];
    const tb = document.getElementById('activityTbody');
    if (!logs.length) { tb.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No activity yet.</td></tr>'; return; }
    tb.innerHTML = logs.map(l => `
        <tr>
            <td class="text-muted">${fmtDate(l.created_at)}</td>
            <td>${esc(l.actor_name||l.actor_usr_code)}</td>
            <td><span class="badge bg-secondary bg-opacity-15 text-secondary">${esc(l.action)}</span></td>
            <td>${l.target_type ? esc(l.target_type)+': '+esc(l.target_id||'') : '—'}</td>
            <td class="text-muted">${esc(l.ip_address||'—')}</td>
        </tr>`).join('');
}

// ── Edit Org ──
function aodOpenEdit() { new bootstrap.Modal(document.getElementById('editOrgModal')).show(); }
async function aodSubmitEdit() {
    const r = await post({
        action: 'update', oid: AOD_OID,
        org_name: v('eoOrgName'), org_type: v('eoOrgType'), email: v('eoEmail'),
        phone: v('eoPhone'), country: v('eoCountry'), domain: v('eoDomain'),
        status: v('eoStatus'), license_expires_at: v('eoLicense'),
        max_users: v('eoMaxUsers'), storage_limit_gb: v('eoStorage'),
        address: v('eoAddress'), notes: v('eoNotes'),
    });
    if (r.status === 'success') { toast('Organization updated'); bootstrap.Modal.getInstance(document.getElementById('editOrgModal'))?.hide(); setTimeout(()=>location.reload(),800); }
    else toast(r.message||'Error', 'danger');
}

// ── Toggle Status ──
async function aodToggle(newStatus) {
    if (!confirm(`Set status to "${newStatus}"?`)) return;
    const r = await post({action:'toggle_status', oid: AOD_OID, status: newStatus});
    if (r.status === 'success') { toast('Status updated'); setTimeout(()=>location.reload(),800); }
    else toast(r.message||'Error', 'danger');
}

// ── Create Department (placeholder — redirect to org admin page) ──
function aodCreateDept() { toast('Departments are managed by the Org Admin', 'info'); }

// ── Helpers ──
const v   = id => document.getElementById(id)?.value ?? '';
const esc = s  => (s+'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
const fmtDate = s => s ? new Date(s).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : '—';
async function post(data) {
    const fd = new FormData();
    Object.entries(data).forEach(([k,v]) => {
        if (Array.isArray(v)) v.forEach(i => fd.append(k+'[]', i));
        else fd.append(k, v);
    });
    return fetch(AOD_AJAX, {method:'POST', body:fd}).then(x=>x.json()).catch(()=>({status:'error',message:'Network error'}));
}
function toast(msg, type='success') {
    const c = document.getElementById('toastContainer') || (() => {
        const el = document.createElement('div');
        el.id = 'toastContainer';
        el.style.cssText = 'position:fixed;bottom:1rem;right:1rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem';
        document.body.appendChild(el);
        return el;
    })();
    const colors = {success:'#16a34a',danger:'#dc2626',warning:'#d97706',info:'#0891b2'};
    const t = document.createElement('div');
    t.style.cssText = `background:${colors[type]||colors.success};color:#fff;padding:.6rem 1rem;border-radius:.5rem;font-size:.85rem;box-shadow:0 4px 12px rgba(0,0,0,.15);max-width:320px`;
    t.textContent = msg;
    c.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

aodLoadStats();
</script>
