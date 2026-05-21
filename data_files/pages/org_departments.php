<?php
// Org Admin Departments — role 4 only
if (($user_role ?? 0) != 4) { include('403.php'); return; }

$me = $_SESSION['usr_code'];
$orgRow = $db->query("
    SELECT o.org_code, o.org_name
    FROM tbl_organizations o
    INNER JOIN tbl_org_members m ON m.org_code = o.org_code
    WHERE m.usr_code = '$me' AND m.org_role = 'admin' AND m.status = 'active'
      AND o.deleted_at IS NULL
    LIMIT 1
")->fetch_assoc();

if (!$orgRow) {
    echo '<div class="alert alert-warning m-4">No organization linked to your account.</div>';
    return;
}
?>
<style>
/* ── Org Departments ─────────────────────────────────────── od-* ── */
.od-hero{background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 55%,#312e81 100%);padding:2rem 1.5rem 3.5rem;position:relative;overflow:hidden}
.od-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")}
.od-breadcrumb{display:flex;align-items:center;gap:.4rem;font-size:.78rem;color:rgba(255,255,255,.55);margin-bottom:.9rem}
.od-breadcrumb a{color:rgba(255,255,255,.55);text-decoration:none}.od-breadcrumb a:hover{color:#fff}
.od-breadcrumb .sep{opacity:.4}
.od-hero-title{font-size:1.5rem;font-weight:700;color:#fff;margin-bottom:.25rem}
.od-hero-sub{font-size:.85rem;color:rgba(255,255,255,.6);margin-bottom:1.2rem}
.od-stat-pills{display:flex;flex-wrap:wrap;gap:.6rem}
.od-stat-pill{background:rgba(255,255,255,.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.15);border-radius:2rem;padding:.35rem .85rem;color:#fff;font-size:.8rem;display:flex;align-items:center;gap:.45rem}
.od-stat-pill i{opacity:.7}
.od-hero-actions{display:flex;gap:.6rem;flex-wrap:wrap;margin-top:1.4rem}
.od-btn-add{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:.6rem;padding:.5rem 1.1rem;font-size:.85rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;transition:opacity .15s}
.od-btn-add:hover{opacity:.88}
.od-body{background:#f8fafc;margin-top:-1.8rem;border-radius:1.2rem 1.2rem 0 0;padding:1.5rem;min-height:60vh;position:relative;z-index:1}
.od-filter-bar{display:flex;flex-wrap:wrap;gap:.6rem;align-items:center;margin-bottom:1.4rem}
.od-filter-bar input,.od-filter-bar select{font-size:.82rem;border-radius:.5rem;border:1px solid #e2e8f0;padding:.38rem .7rem;background:#fff;outline:none}
.od-filter-bar input:focus,.od-filter-bar select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12)}
.od-filter-bar input{flex:1;min-width:180px}
.od-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:1rem}
.od-card{background:#fff;border-radius:1rem;border:1px solid #e8ecf3;transition:transform .15s,box-shadow .15s;cursor:pointer;overflow:hidden}
.od-card:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(99,102,241,.13)}
.od-card-head{padding:1.1rem 1.1rem .7rem;display:flex;align-items:flex-start;justify-content:space-between;gap:.7rem}
.od-icon{width:44px;height:44px;border-radius:.7rem;background:linear-gradient(135deg,rgba(99,102,241,.12),rgba(139,92,246,.12));display:flex;align-items:center;justify-content:center;font-size:1.15rem;color:#6366f1;flex-shrink:0}
.od-card-name{font-weight:700;font-size:.95rem;color:#1e293b;line-height:1.3}
.od-card-code{font-size:.72rem;color:#94a3b8;margin-top:.1rem}
.od-status-chip{font-size:.7rem;font-weight:600;padding:.22rem .6rem;border-radius:2rem;flex-shrink:0}
.od-status-chip.active{background:#dcfce7;color:#16a34a}
.od-status-chip.inactive{background:#f1f5f9;color:#64748b}
.od-card-desc{padding:0 1.1rem .7rem;font-size:.8rem;color:#64748b;line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.od-card-foot{padding:.65rem 1.1rem;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between}
.od-meta{display:flex;align-items:center;gap:1rem}
.od-meta-item{display:flex;align-items:center;gap:.3rem;font-size:.78rem;color:#64748b}
.od-edit-btn{border:1px solid #e2e8f0;background:#fff;border-radius:.45rem;padding:.25rem .65rem;font-size:.78rem;color:#475569;cursor:pointer;display:flex;align-items:center;gap:.3rem;transition:all .15s}
.od-edit-btn:hover{border-color:#6366f1;color:#6366f1}
/* skeleton */
.od-skel{border-radius:.7rem;background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:odSkel 1.4s infinite}
@keyframes odSkel{0%{background-position:200% 0}100%{background-position:-200% 0}}
/* empty */
.od-empty{text-align:center;padding:4rem 1rem;color:#94a3b8}
.od-empty i{font-size:3.5rem;display:block;margin-bottom:1rem;opacity:.4}
/* modal tweaks */
.od-modal-label{font-size:.82rem;font-weight:600;color:#374151;margin-bottom:.35rem}
.od-form-ctrl{font-size:.85rem;border-radius:.5rem;border:1px solid #e2e8f0;padding:.42rem .7rem;width:100%;outline:none;transition:border .15s}
.od-form-ctrl:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
/* dark mode */
@media(prefers-color-scheme:dark){
  .od-body{background:#0f172a}
  .od-card{background:#1e293b;border-color:#334155}
  .od-card-name{color:#f1f5f9}
  .od-card-foot{border-color:#334155}
  .od-filter-bar input,.od-filter-bar select{background:#1e293b;border-color:#334155;color:#f1f5f9}
  .od-edit-btn{background:#1e293b;border-color:#334155;color:#94a3b8}
}
</style>

<!-- Hero -->
<div class="od-hero">
    <div class="od-breadcrumb">
        <a href="?view=org_dashboard"><i class="bi bi-house-fill"></i></a>
        <span class="sep">/</span>
        <span><?= htmlspecialchars($orgRow['org_name']) ?></span>
        <span class="sep">/</span>
        <span style="color:#fff">Departments</span>
    </div>
    <div class="od-hero-title"><i class="bi bi-diagram-3-fill me-2" style="color:#a5b4fc"></i>Departments</div>
    <div class="od-hero-sub">Manage departments and their members</div>
    <div class="od-stat-pills" id="odStatPills">
        <div class="od-stat-pill"><i class="bi bi-building"></i><span id="odStatTotal">—</span> Departments</div>
        <div class="od-stat-pill"><i class="bi bi-check-circle"></i><span id="odStatActive">—</span> Active</div>
        <div class="od-stat-pill"><i class="bi bi-people-fill"></i><span id="odStatMembers">—</span> Members Assigned</div>
    </div>
    <div class="od-hero-actions">
        <button class="od-btn-add" onclick="odOpenCreate()">
            <i class="bi bi-plus-lg"></i>Add Department
        </button>
    </div>
</div>

<!-- Body -->
<div class="od-body">

    <!-- Filter bar -->
    <div class="od-filter-bar">
        <input type="text" id="odSearch" placeholder="Search departments…" oninput="odFilter()">
        <select id="odStatusFilter" onchange="odFilter()" style="min-width:130px">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>

    <!-- Grid -->
    <div class="od-grid" id="odGrid">
        <!-- skeleton -->
        <?php for($i=0;$i<6;$i++): ?>
        <div class="od-card" style="pointer-events:none">
            <div class="od-card-head">
                <div style="display:flex;gap:.7rem;align-items:center">
                    <div class="od-skel" style="width:44px;height:44px;border-radius:.7rem"></div>
                    <div>
                        <div class="od-skel" style="width:120px;height:.85rem;border-radius:.3rem"></div>
                        <div class="od-skel" style="width:60px;height:.65rem;border-radius:.3rem;margin-top:.35rem"></div>
                    </div>
                </div>
                <div class="od-skel" style="width:50px;height:1.2rem;border-radius:2rem"></div>
            </div>
            <div class="od-card-desc"><div class="od-skel" style="width:90%;height:.7rem;border-radius:.3rem"></div></div>
            <div class="od-card-foot">
                <div class="od-skel" style="width:80px;height:.7rem;border-radius:.3rem"></div>
                <div class="od-skel" style="width:48px;height:1.6rem;border-radius:.45rem"></div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal fade" id="odModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:1rem;overflow:hidden">
            <div class="modal-header" style="background:linear-gradient(135deg,#1e1b4b,#312e81);border:none;padding:1.1rem 1.4rem">
                <div>
                    <h6 class="mb-0 fw-bold text-white" id="odModalTitle">Add Department</h6>
                    <div class="text-white-50" style="font-size:.78rem" id="odModalSub">Fill in department details</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="odId">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="od-modal-label">Department Name <span class="text-danger">*</span></div>
                        <input type="text" class="od-form-ctrl" id="odName" placeholder="e.g. Science Department">
                    </div>
                    <div class="col-md-6">
                        <div class="od-modal-label">Department Code</div>
                        <input type="text" class="od-form-ctrl" id="odCode" placeholder="e.g. SCI-01">
                    </div>
                    <div class="col-md-6">
                        <div class="od-modal-label">Status</div>
                        <select class="od-form-ctrl" id="odStatus">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="od-modal-label">Description</div>
                        <textarea class="od-form-ctrl" id="odDesc" rows="3" placeholder="Optional description…" style="resize:vertical"></textarea>
                    </div>
                    <div class="col-12">
                        <div class="od-modal-label">Head / Coordinator</div>
                        <select class="od-form-ctrl" id="odHead">
                            <option value="">— None —</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                <button class="btn btn-sm btn-outline-danger me-auto d-none" id="odDeleteBtn" onclick="odDelete()">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
                <button class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-sm text-white fw-semibold" id="odSaveBtn" onclick="odSave()"
                        style="background:linear-gradient(135deg,#6366f1,#8b5cf6);border:none;padding:.42rem 1.2rem;border-radius:.5rem">
                    Save Department
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const OD_AJAX = '../data_files/ajax/ajax_org_admin.php';
let odAllDepts = [];
let odInstructors = [];

async function odInit() {
    const [dr, ir] = await Promise.all([
        fetch(`${OD_AJAX}?action=list_departments`).then(x=>x.json()).catch(()=>({})),
        fetch(`${OD_AJAX}?action=list_instructors`).then(x=>x.json()).catch(()=>({}))
    ]);
    odAllDepts    = dr.departments ?? [];
    odInstructors = ir.instructors ?? [];

    const sel = document.getElementById('odHead');
    odInstructors.forEach(u => {
        sel.appendChild(new Option(`${u.first_name} ${u.last_name}`, u.usr_code));
    });

    odUpdateStats();
    odRender(odAllDepts);
}

function odUpdateStats() {
    const active   = odAllDepts.filter(d => d.status === 'active').length;
    const assigned = odAllDepts.reduce((s, d) => s + (parseInt(d.member_count) || 0), 0);
    document.getElementById('odStatTotal').textContent   = odAllDepts.length;
    document.getElementById('odStatActive').textContent  = active;
    document.getElementById('odStatMembers').textContent = assigned;
}

function odFilter() {
    const q    = document.getElementById('odSearch').value.toLowerCase();
    const stat = document.getElementById('odStatusFilter').value;
    const filtered = odAllDepts.filter(d => {
        const matchQ   = !q || d.dept_name.toLowerCase().includes(q) || (d.dept_code||'').toLowerCase().includes(q);
        const matchS   = !stat || d.status === stat;
        return matchQ && matchS;
    });
    odRender(filtered);
}

function odRender(depts) {
    const grid = document.getElementById('odGrid');
    if (!depts.length) {
        grid.innerHTML = `<div class="od-empty" style="grid-column:1/-1">
            <i class="bi bi-diagram-3"></i>
            <div style="font-size:1rem;font-weight:600;color:#475569;margin-bottom:.4rem">No departments found</div>
            <div style="font-size:.85rem">Try a different search or add a new department.</div>
            <button class="od-btn-add mt-3" onclick="odOpenCreate()"><i class="bi bi-plus-lg"></i>Add Department</button>
        </div>`;
        return;
    }
    grid.innerHTML = depts.map(d => `
        <div class="od-card" onclick="odOpenEdit(${JSON.stringify(d).replace(/"/g,'&quot;')})">
            <div class="od-card-head">
                <div style="display:flex;align-items:flex-start;gap:.7rem;flex:1;min-width:0">
                    <div class="od-icon"><i class="bi bi-building-fill"></i></div>
                    <div style="min-width:0">
                        <div class="od-card-name">${odEsc(d.dept_name)}</div>
                        ${d.dept_code ? `<div class="od-card-code">${odEsc(d.dept_code)}</div>` : ''}
                    </div>
                </div>
                <span class="od-status-chip ${d.status}">${d.status}</span>
            </div>
            ${d.description ? `<div class="od-card-desc">${odEsc(d.description)}</div>` : '<div style="height:.4rem"></div>'}
            <div class="od-card-foot">
                <div class="od-meta">
                    <div class="od-meta-item"><i class="bi bi-people-fill"></i>${d.member_count||0} member${d.member_count!=1?'s':''}</div>
                    ${d.head_name ? `<div class="od-meta-item"><i class="bi bi-person-badge-fill"></i>${odEsc(d.head_name)}</div>` : ''}
                </div>
                <button class="od-edit-btn" onclick="event.stopPropagation();odOpenEdit(${JSON.stringify(d).replace(/"/g,'&quot;')})">
                    <i class="bi bi-pencil-fill"></i>Edit
                </button>
            </div>
        </div>`).join('');
}

function odOpenCreate() {
    document.getElementById('odModalTitle').textContent = 'Add Department';
    document.getElementById('odModalSub').textContent   = 'Fill in department details';
    document.getElementById('odId').value     = '';
    document.getElementById('odName').value   = '';
    document.getElementById('odCode').value   = '';
    document.getElementById('odDesc').value   = '';
    document.getElementById('odHead').value   = '';
    document.getElementById('odStatus').value = 'active';
    document.getElementById('odDeleteBtn').classList.add('d-none');
    document.getElementById('odSaveBtn').textContent = 'Save Department';
    new bootstrap.Modal(document.getElementById('odModal')).show();
    setTimeout(()=>document.getElementById('odName').focus(), 350);
}

function odOpenEdit(d) {
    document.getElementById('odModalTitle').textContent = 'Edit Department';
    document.getElementById('odModalSub').textContent   = `Editing: ${d.dept_name}`;
    document.getElementById('odId').value     = d.id;
    document.getElementById('odName').value   = d.dept_name;
    document.getElementById('odCode').value   = d.dept_code   || '';
    document.getElementById('odDesc').value   = d.description || '';
    document.getElementById('odHead').value   = d.head_usr_code || '';
    document.getElementById('odStatus').value = d.status;
    document.getElementById('odDeleteBtn').classList.remove('d-none');
    document.getElementById('odSaveBtn').textContent = 'Update Department';
    new bootstrap.Modal(document.getElementById('odModal')).show();
}

async function odSave() {
    const id = odV('odId');
    const name = odV('odName').trim();
    if (!name) { odToast('Department name is required', 'warning'); return; }

    const btn = document.getElementById('odSaveBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

    const r = await odPost({
        action: id ? 'update_dept' : 'create_dept',
        dept_id: id, dept_name: name, dept_code: odV('odCode'),
        description: odV('odDesc'), head_usr_code: odV('odHead'), status: odV('odStatus'),
    });
    btn.disabled = false;
    btn.textContent = id ? 'Update Department' : 'Save Department';

    if (r.status === 'success') {
        odToast(id ? 'Department updated successfully' : 'Department created successfully');
        bootstrap.Modal.getInstance(document.getElementById('odModal'))?.hide();
        await odRefresh();
    } else {
        odToast(r.message || 'Something went wrong', 'danger');
    }
}

async function odDelete() {
    const id   = odV('odId');
    const name = odV('odName');
    if (!id) return;

    const result = await Swal.fire({
        title: 'Delete Department?',
        html: `<p class="mb-1">You're about to delete <strong>${odEsc(name)}</strong>.</p><p class="text-muted small">Members in this department will be unassigned.</p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: { popup: 'rounded-4' }
    });
    if (!result.isConfirmed) return;

    const r = await odPost({ action: 'delete_dept', dept_id: id });
    if (r.status === 'success') {
        odToast('Department deleted');
        bootstrap.Modal.getInstance(document.getElementById('odModal'))?.hide();
        await odRefresh();
    } else {
        odToast(r.message || 'Error deleting department', 'danger');
    }
}

async function odRefresh() {
    const r = await fetch(`${OD_AJAX}?action=list_departments`).then(x=>x.json()).catch(()=>({}));
    odAllDepts = r.departments ?? [];
    odUpdateStats();
    odFilter();
}

/* ── helpers ── */
const odV   = id => document.getElementById(id)?.value ?? '';
const odEsc = s  => (s+'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

async function odPost(data) {
    const fd = new FormData();
    Object.entries(data).forEach(([k, v]) => fd.append(k, v ?? ''));
    return fetch(OD_AJAX, { method: 'POST', body: fd })
        .then(x => x.json())
        .catch(() => ({ status: 'error', message: 'Network error' }));
}

function odToast(msg, type = 'success') {
    const colors = { success: '#16a34a', danger: '#dc2626', warning: '#d97706', info: '#0891b2' };
    const icons  = { success: 'bi-check-circle-fill', danger: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
    let c = document.getElementById('odToastWrap');
    if (!c) {
        c = Object.assign(document.createElement('div'), { id: 'odToastWrap' });
        c.style.cssText = 'position:fixed;bottom:1.2rem;right:1.2rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem';
        document.body.appendChild(c);
    }
    const t = document.createElement('div');
    t.style.cssText = `background:${colors[type]||colors.success};color:#fff;padding:.65rem 1rem;border-radius:.65rem;font-size:.84rem;box-shadow:0 4px 16px rgba(0,0,0,.18);display:flex;align-items:center;gap:.5rem;max-width:320px;animation:fadeInUp .2s ease`;
    t.innerHTML = `<i class="bi ${icons[type]||icons.success}" style="flex-shrink:0"></i><span>${msg}</span>`;
    c.appendChild(t);
    setTimeout(() => t.remove(), 3800);
}

if (!document.getElementById('odFadeKf')) {
    const s = document.createElement('style');
    s.id = 'odFadeKf';
    s.textContent = '@keyframes fadeInUp{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}';
    document.head.appendChild(s);
}

odInit();
</script>
