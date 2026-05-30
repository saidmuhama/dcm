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
if (!$orgRow) { echo '<div class="alert alert-warning m-4">No organization linked to your account.</div>'; return; }
$orgCode = $orgRow['org_code'];
?>
<style>
/* ── License Management (lm-*) ─────────────────────────────── */
.lm-hero{background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 55%,#312e81 100%);padding:2rem 1.5rem 3.5rem;position:relative;overflow:hidden}
.lm-hero::before{content:'';position:absolute;inset:0;
    background:radial-gradient(circle at 15% 60%,rgba(99,102,241,.2) 0%,transparent 55%),
               radial-gradient(circle at 85% 20%,rgba(139,92,246,.15) 0%,transparent 48%);pointer-events:none}
.lm-hero-title{font-size:1.5rem;font-weight:700;color:#fff;margin-bottom:.25rem}
.lm-hero-sub{font-size:.85rem;color:rgba(255,255,255,.6);margin-bottom:1.2rem}
.lm-breadcrumb{display:flex;align-items:center;gap:.4rem;font-size:.78rem;color:rgba(255,255,255,.55);margin-bottom:.9rem}
.lm-breadcrumb a{color:rgba(255,255,255,.55);text-decoration:none}.lm-breadcrumb a:hover{color:#fff}
.lm-breadcrumb .sep{opacity:.4}
.lm-kpi-bar{display:flex;flex-wrap:wrap;gap:.65rem;margin-top:1rem}
.lm-kpi{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:14px;
    padding:.65rem 1.1rem;backdrop-filter:blur(4px);min-width:110px;text-align:center}
.lm-kpi .val{font-size:1.15rem;font-weight:800;color:#fff;line-height:1}
.lm-kpi .lbl{font-size:.63rem;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.04em;margin-top:.2rem}
.lm-canvas{max-width:1280px;margin:-2rem auto 0;padding:0 1.25rem 3rem;position:relative;z-index:10}

/* License card */
.lm-card{background:#fff;border-radius:18px;box-shadow:0 2px 14px rgba(0,0,0,.07);
    border:1px solid rgba(0,0,0,.05);overflow:hidden;margin-bottom:1.5rem}
.lm-card-header{padding:1rem 1.25rem;display:flex;align-items:flex-start;gap:1rem;flex-wrap:wrap}
.lm-thumb{width:72px;height:52px;border-radius:10px;object-fit:cover;background:#f1f5f9;flex-shrink:0}
.lm-thumb-placeholder{width:72px;height:52px;border-radius:10px;background:linear-gradient(135deg,#6366f1,#8b5cf6);
    display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.2rem;flex-shrink:0}
.lm-course-title{font-size:.95rem;font-weight:700;color:#1e293b;margin-bottom:.2rem;line-height:1.3}
.lm-badge-access{font-size:.62rem;font-weight:700;padding:.18rem .5rem;border-radius:100px;text-transform:uppercase;letter-spacing:.04em}
.lm-badge-unlimited{background:#dcfce7;color:#16a34a}
.lm-badge-seat{background:#dbeafe;color:#1d4ed8}
.lm-badge-expired{background:#fee2e2;color:#dc2626}

/* Seats bar */
.lm-seats-wrap{padding:.75rem 1.25rem;border-top:1px solid #f1f5f9}
.lm-seats-label{display:flex;justify-content:space-between;align-items:center;font-size:.78rem;color:#64748b;margin-bottom:.35rem}
.lm-seats-bar-bg{height:8px;border-radius:100px;background:#f1f5f9;overflow:hidden}
.lm-seats-bar-fill{height:100%;border-radius:100px;background:linear-gradient(90deg,#6366f1,#8b5cf6);transition:width .5s ease}
.lm-seats-bar-fill.warn{background:linear-gradient(90deg,#f59e0b,#ef4444)}
.lm-expiry-warn{font-size:.74rem;color:#ef4444;margin-top:.3rem;display:flex;align-items:center;gap:.3rem}

/* Manage pane */
.lm-manage-pane{border-top:1px solid #f1f5f9;padding:1.25rem;display:none}
.lm-manage-pane.open{display:block}
.lm-assignee-row{display:flex;align-items:center;gap:.75rem;padding:.6rem 0;border-bottom:1px solid #f8fafc}
.lm-assignee-row:last-child{border-bottom:none}
.lm-avatar-sm{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;
    font-size:.72rem;font-weight:800;color:#fff;flex-shrink:0}
.lm-progress-mini{height:5px;border-radius:100px;background:#e2e8f0;overflow:hidden;flex:1;max-width:90px}
.lm-progress-mini-fill{height:100%;border-radius:100px;background:#6366f1;transition:width .4s}

/* Modal overrides */
.lm-modal .modal-content{border-radius:20px;border:0;box-shadow:0 16px 60px rgba(0,0,0,.16)}
.lm-modal .modal-header{border-bottom:1px solid #f1f5f9;padding:1.1rem 1.4rem}
.lm-modal .modal-footer{border-top:1px solid #f1f5f9;padding:.85rem 1.4rem}
.lm-member-item{display:flex;align-items:center;gap:.65rem;padding:.5rem .75rem;border-radius:10px;
    cursor:pointer;transition:background .15s;border:1px solid transparent}
.lm-member-item:hover{background:#f8f9ff;border-color:#e0e7ff}
.lm-member-item.selected{background:#ede9fe;border-color:#6366f1}
.lm-member-item input[type=checkbox]{flex-shrink:0}
</style>

<div class="lm-hero">
    <div class="container-fluid px-4">
        <div class="lm-breadcrumb">
            <a href="?view=3002">Dashboard</a>
            <span class="sep">/</span>
            <span>License Management</span>
        </div>
        <div class="lm-hero-title"><i class="bi bi-key-fill me-2"></i>License Management</div>
        <div class="lm-hero-sub">Manage course seat licenses for <strong style="color:#fff"><?= htmlspecialchars($orgRow['org_name']) ?></strong></div>
        <div class="lm-kpi-bar" id="lmKpiBar">
            <div class="lm-kpi"><div class="val" id="kpiTotal">—</div><div class="lbl">Total Licenses</div></div>
            <div class="lm-kpi"><div class="val" id="kpiPurchased">—</div><div class="lbl">Seats Purchased</div></div>
            <div class="lm-kpi"><div class="val" id="kpiAssigned">—</div><div class="lbl">Seats Assigned</div></div>
            <div class="lm-kpi"><div class="val" id="kpiRemaining">—</div><div class="lbl">Seats Remaining</div></div>
            <div class="lm-kpi"><div class="val" id="kpiAvgCompletion">—</div><div class="lbl">Avg Completion</div></div>
        </div>
    </div>
</div>

<div class="lm-canvas">

    <!-- Empty state -->
    <div id="lmEmpty" style="display:none">
        <div class="text-center py-5 text-muted">
            <i class="bi bi-key" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem"></i>
            <p class="mb-0">No active licenses found for your organization.</p>
            <p class="small">Subscribe to courses from <a href="?view=org_courses">Course Catalog</a> to get started.</p>
        </div>
    </div>

    <!-- License cards container -->
    <div id="lmCards"></div>
</div>

<!-- ── Manage Seats Modal ───────────────────────────────────── -->
<div class="modal fade lm-modal" id="lmManageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h6 class="modal-title mb-0" id="lmManageTitle">Manage Seats</h6>
                    <div class="text-muted small" id="lmManageSub"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:0">

                <!-- Tab nav -->
                <ul class="nav nav-tabs px-3 pt-2" id="lmManageTabs">
                    <li class="nav-item">
                        <button class="nav-link active" onclick="lmTab('assigned')" id="tabAssigned">
                            <i class="bi bi-people me-1"></i>Assigned Users
                            <span class="badge bg-primary ms-1" id="lmAssignedCount">0</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" onclick="lmTab('assign')" id="tabAssign">
                            <i class="bi bi-person-plus me-1"></i>Assign Users
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" onclick="lmTab('bulk')" id="tabBulk">
                            <i class="bi bi-building me-1"></i>Bulk by Department
                        </button>
                    </li>
                </ul>

                <!-- Assigned users tab -->
                <div id="paneAssigned" class="p-3">
                    <div id="lmAssigneeList">
                        <div class="text-center text-muted py-4 small">Loading...</div>
                    </div>
                </div>

                <!-- Assign users tab -->
                <div id="paneAssign" class="p-3" style="display:none">
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control" id="lmMemberSearch" placeholder="Search members by name or email..." oninput="lmSearchMembers()">
                    </div>
                    <div id="lmMemberList" style="max-height:320px;overflow-y:auto">
                        <div class="text-center text-muted py-3 small">Loading members...</div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                        <span class="small text-muted"><span id="lmSelectedCount">0</span> selected</span>
                        <button class="btn btn-primary btn-sm px-4" onclick="lmDoAssign()">
                            <i class="bi bi-person-check me-1"></i>Assign Selected
                        </button>
                    </div>
                </div>

                <!-- Bulk by dept tab -->
                <div id="paneBulk" class="p-3" style="display:none">
                    <p class="text-muted small mb-3">Select a department to assign all its members to this license at once. Members already assigned will be skipped.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Department</label>
                        <select class="form-select" id="lmBulkDept">
                            <option value="">-- Select department --</option>
                        </select>
                    </div>
                    <button class="btn btn-primary px-4" onclick="lmDoBulkAssign()">
                        <i class="bi bi-people-fill me-1"></i>Assign All Department Members
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
/* ── License Management SPA ─────────────────────────────────── */
(function(){
'use strict';
const API = '../data_files/ajax/ajax_license_management.php';
const ORGAPI = '../data_files/ajax/ajax_org_admin.php';
let _licenses = [];
let _activeLicId = null;
let _selectedUsr = new Set();
let _memberCache = null;
let _memberSearchTimer = null;

/* ── helpers ─────────────────────────────────────────────── */
function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function avatarColor(s){ const c=['#6366f1','#8b5cf6','#ec4899','#f59e0b','#10b981','#3b82f6','#ef4444','#06b6d4'];let h=0;for(let i=0;i<(s||'').length;i++)h=(h*31+s.charCodeAt(i))&0xfffff;return c[h%c.length]; }
function initials(n){ return (n||'?').split(' ').map(x=>x[0]||'').join('').substring(0,2).toUpperCase(); }
function pctColor(p){ return p>=80?'#10b981':p>=40?'#f59e0b':'#6366f1'; }
function fmtDate(d){ if(!d)return'No expiry'; const dt=new Date(d); return dt.toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}); }
function daysUntil(d){ if(!d)return null; return Math.ceil((new Date(d)-Date.now())/86400000); }
function post(data){ return fetch(API,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)}).then(r=>r.json()); }
function get(params){ return fetch(API+'?'+new URLSearchParams(params)).then(r=>r.json()); }

/* ── Load all licenses ───────────────────────────────────── */
function loadLicenses(){
    get({action:'list_licenses'}).then(r=>{
        if(r.status!=='success'){document.getElementById('lmEmpty').style.display='block';return;}
        _licenses = r.data || [];
        renderKPIs();
        renderCards();
    });
}

function renderKPIs(){
    let total=_licenses.length, purchased=0, assigned=0, remaining=0, completionSum=0, completionCount=0;
    _licenses.forEach(l=>{
        const sp = parseInt(l.seats_purchased)||0;
        const su = parseInt(l.seats_used)||0;
        purchased += sp;
        assigned  += su;
        remaining += Math.max(0, sp - su);
        (l.assignees||[]).forEach(a=>{
            completionSum += parseFloat(a.progress_pct)||0;
            completionCount++;
        });
    });
    document.getElementById('kpiTotal').textContent     = total;
    document.getElementById('kpiPurchased').textContent = purchased > 0 ? purchased : (total > 0 ? 'Unlimited' : '—');
    document.getElementById('kpiAssigned').textContent  = assigned;
    document.getElementById('kpiRemaining').textContent = remaining;
    document.getElementById('kpiAvgCompletion').textContent = completionCount > 0
        ? Math.round(completionSum/completionCount)+'%' : '—';

    if(_licenses.length===0) document.getElementById('lmEmpty').style.display='block';
}

function renderCards(){
    const wrap = document.getElementById('lmCards');
    if(!_licenses.length){ wrap.innerHTML=''; return; }

    wrap.innerHTML = _licenses.map(l => {
        const sp       = parseInt(l.seats_purchased)||0;
        const su       = parseInt(l.seats_used)||0;
        const rem      = Math.max(0, sp - su);
        const pct      = sp > 0 ? Math.min(100, Math.round(su/sp*100)) : 0;
        const isSeat   = l.access_type === 'seat_limited';
        const days     = daysUntil(l.expires_at);
        const expiring = days !== null && days <= 30 && days >= 0;
        const expired  = days !== null && days < 0;
        const thumb    = l.thumbnail
            ? `<img src="../${esc(l.thumbnail)}" class="lm-thumb" onerror="this.replaceWith(document.getElementById('_lmThumbTpl').content.cloneNode(true))">`
            : `<div class="lm-thumb-placeholder"><i class="bi bi-play-circle-fill"></i></div>`;
        const badgeCls = expired ? 'lm-badge-expired' : (isSeat ? 'lm-badge-seat' : 'lm-badge-unlimited');
        const badgeTxt = expired ? 'Expired' : (isSeat ? 'Seat-Limited' : 'Unlimited');

        // Mini assignee avatars (up to 5)
        const maxShow = 5;
        const shown   = (l.assignees||[]).slice(0, maxShow);
        const extra   = (l.assignees||[]).length - maxShow;
        const avatarHtml = shown.map(a=>`<div class="lm-avatar-sm" style="background:${avatarColor(a.usr_code)};width:26px;height:26px;font-size:.6rem;margin-left:-6px;border:2px solid #fff" title="${esc(a.full_name)}">${initials(a.full_name)}</div>`).join('')
            + (extra>0?`<div class="lm-avatar-sm" style="background:#64748b;width:26px;height:26px;font-size:.6rem;margin-left:-6px;border:2px solid #fff">+${extra}</div>`:'');

        return `
        <div class="lm-card" id="lmCard_${l.id}">
            <div class="lm-card-header">
                ${thumb}
                <div style="flex:1;min-width:0">
                    <div class="lm-course-title">${esc(l.title)}</div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                        <span class="lm-badge-access ${badgeCls}">${badgeTxt}</span>
                        ${l.instructor_name ? `<span class="text-muted small"><i class="bi bi-person me-1"></i>${esc(l.instructor_name)}</span>` : ''}
                    </div>
                    ${isSeat ? `
                    <div class="d-flex align-items-center gap-2 mt-2">
                        <div style="display:flex;margin-left:6px">${avatarHtml}</div>
                        <span class="text-muted small">${su} of ${sp} seats assigned</span>
                    </div>` : ''}
                </div>
                <div class="text-end" style="flex-shrink:0">
                    <button class="btn btn-primary btn-sm px-3" onclick="lmOpenManage(${l.id})">
                        <i class="bi bi-gear-fill me-1"></i>Manage Seats
                    </button>
                </div>
            </div>
            ${isSeat ? `
            <div class="lm-seats-wrap">
                <div class="lm-seats-label">
                    <span><i class="bi bi-person-fill me-1"></i>${su} assigned &nbsp;·&nbsp; <strong>${rem}</strong> remaining</span>
                    <span>${sp} total</span>
                </div>
                <div class="lm-seats-bar-bg">
                    <div class="lm-seats-bar-fill ${pct>=85?'warn':''}" style="width:${pct}%"></div>
                </div>
                ${expiring ? `<div class="lm-expiry-warn"><i class="bi bi-exclamation-triangle-fill"></i>Expiring in ${days} day${days!=1?'s':''} — ${fmtDate(l.expires_at)}</div>` : ''}
                ${expired  ? `<div class="lm-expiry-warn"><i class="bi bi-x-circle-fill"></i>Expired on ${fmtDate(l.expires_at)}</div>` : ''}
                ${!expiring && !expired && l.expires_at ? `<div class="text-muted small mt-1"><i class="bi bi-calendar me-1"></i>Expires: ${fmtDate(l.expires_at)}</div>` : ''}
            </div>` : `
            <div class="lm-seats-wrap">
                <span class="text-success small"><i class="bi bi-infinity me-1"></i>Unlimited access — all org members can enroll</span>
                ${l.expires_at ? `<span class="ms-3 text-muted small">· Expires: ${fmtDate(l.expires_at)}</span>` : ''}
            </div>`}
        </div>`;
    }).join('');
}

/* ── Open manage modal ──────────────────────────────────── */
window.lmOpenManage = function(licId){
    _activeLicId  = licId;
    _selectedUsr  = new Set();
    _memberCache  = null;
    const lic = _licenses.find(l=>l.id==licId);
    if(!lic)return;
    document.getElementById('lmManageTitle').textContent = 'Manage Seats — ' + lic.title;
    document.getElementById('lmManageSub').textContent   = lic.access_type === 'seat_limited'
        ? `${lic.seats_used||0} of ${lic.seats_purchased||0} seats used`
        : 'Unlimited access';
    lmTab('assigned');
    loadAssignees(licId, lic);
    loadDepts();
    const m = bootstrap.Modal.getOrCreateInstance(document.getElementById('lmManageModal'));
    m.show();
};

/* ── Tab switcher ───────────────────────────────────────── */
window.lmTab = function(t){
    ['assigned','assign','bulk'].forEach(id=>{
        document.getElementById('pane'+id.charAt(0).toUpperCase()+id.slice(1)).style.display = id===t?'block':'none';
        document.getElementById('tab'+id.charAt(0).toUpperCase()+id.slice(1))?.classList.toggle('active', id===t);
    });
    if(t==='assign') loadMembersForAssign();
};

/* ── Assigned list ──────────────────────────────────────── */
function loadAssignees(licId, lic){
    get({action:'get_license_detail', access_id:licId}).then(r=>{
        const wrap = document.getElementById('lmAssigneeList');
        if(r.status!=='success'||!r.data){wrap.innerHTML='<div class="text-muted small text-center py-3">Failed to load</div>';return;}
        const assignees = r.data.assignees||[];
        document.getElementById('lmAssignedCount').textContent = assignees.length;
        if(!assignees.length){
            wrap.innerHTML='<div class="text-center text-muted py-4 small"><i class="bi bi-people d-block mb-2" style="font-size:1.5rem;opacity:.3"></i>No users assigned yet</div>';
            return;
        }
        wrap.innerHTML = assignees.map(a=>{
            const pct = parseFloat(a.progress_pct)||0;
            const col = pctColor(pct);
            return `
            <div class="lm-assignee-row" id="assignee_${a.assignment_id}">
                <div class="lm-avatar-sm" style="background:${avatarColor(a.usr_code)}">${initials(a.full_name)}</div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:.83rem;font-weight:600;color:#1e293b">${esc(a.full_name)}</div>
                    <div style="font-size:.72rem;color:#64748b">${esc(a.email)}${a.dept_name?' · '+esc(a.dept_name):''}</div>
                </div>
                <div style="text-align:right;flex-shrink:0">
                    <div style="font-size:.78rem;font-weight:700;color:${col}">${pct}%</div>
                    <div class="lm-progress-mini" style="margin-top:3px">
                        <div class="lm-progress-mini-fill" style="width:${pct}%;background:${col}"></div>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-danger ms-2" style="font-size:.72rem;padding:.2rem .55rem"
                    onclick="lmRevoke(${a.assignment_id},'${esc(a.full_name).replace(/'/g,"\\'")}')">
                    <i class="bi bi-x-circle me-1"></i>Revoke
                </button>
            </div>`;
        }).join('');
    });
}

/* ── Revoke ─────────────────────────────────────────────── */
window.lmRevoke = function(assignmentId, name){
    Swal.fire({
        title:'Revoke Access?',
        text:`Remove "${name}" from this course?`,
        icon:'warning',
        showCancelButton:true,
        confirmButtonColor:'#ef4444',
        confirmButtonText:'Yes, Revoke',
        cancelButtonText:'Cancel',
    }).then(r=>{
        if(!r.isConfirmed)return;
        post({action:'revoke_seat',assignment_id:assignmentId}).then(res=>{
            if(res.status==='success'){
                document.getElementById('assignee_'+assignmentId)?.remove();
                const cnt = document.getElementById('lmAssignedCount');
                if(cnt)cnt.textContent = Math.max(0,(parseInt(cnt.textContent)||0)-1);
                loadLicenses();
                Swal.fire({toast:true,position:'bottom-end',icon:'success',title:res.message,showConfirmButton:false,timer:2500,timerProgressBar:true});
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        });
    });
};

/* ── Members for assignment ─────────────────────────────── */
function loadMembersForAssign(q=''){
    const wrap = document.getElementById('lmMemberList');
    wrap.innerHTML = '<div class="text-center text-muted py-3 small"><div class="spinner-border spinner-border-sm"></div></div>';
    const params = {action:'get_members_for_assignment', access_id:_activeLicId};
    if(q) params.q = q;
    get(params).then(r=>{
        _memberCache = r.members || [];
        renderMemberList(_memberCache);
    });
}

function renderMemberList(members){
    const wrap = document.getElementById('lmMemberList');
    if(!members||!members.length){
        wrap.innerHTML='<div class="text-center text-muted py-3 small">No available members found</div>';
        return;
    }
    wrap.innerHTML = members.map(m=>`
        <div class="lm-member-item ${_selectedUsr.has(m.usr_code)?'selected':''}" onclick="lmToggleMember('${m.usr_code}',this)" id="mi_${m.usr_code}">
            <input type="checkbox" ${_selectedUsr.has(m.usr_code)?'checked':''} style="pointer-events:none">
            <div class="lm-avatar-sm" style="background:${avatarColor(m.usr_code)};width:30px;height:30px;font-size:.65rem">${initials(m.full_name)}</div>
            <div style="flex:1;min-width:0">
                <div style="font-size:.82rem;font-weight:600">${esc(m.full_name)}</div>
                <div style="font-size:.72rem;color:#64748b">${esc(m.email)}${m.dept_name?' · '+esc(m.dept_name):''}</div>
            </div>
        </div>`).join('');
    document.getElementById('lmSelectedCount').textContent = _selectedUsr.size;
}

window.lmToggleMember = function(usrCode, el){
    if(_selectedUsr.has(usrCode)){_selectedUsr.delete(usrCode);el.classList.remove('selected');el.querySelector('input').checked=false;}
    else{_selectedUsr.add(usrCode);el.classList.add('selected');el.querySelector('input').checked=true;}
    document.getElementById('lmSelectedCount').textContent = _selectedUsr.size;
};

window.lmSearchMembers = function(){
    clearTimeout(_memberSearchTimer);
    _memberSearchTimer = setTimeout(()=>{
        loadMembersForAssign(document.getElementById('lmMemberSearch').value.trim());
    }, 350);
};

/* ── Do assign ──────────────────────────────────────────── */
window.lmDoAssign = function(){
    if(!_selectedUsr.size){ Swal.fire({toast:true,position:'bottom-end',icon:'info',title:'No members selected',showConfirmButton:false,timer:2000}); return; }
    Swal.fire({title:'Assigning seats…',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
    post({action:'assign_seats', access_id:_activeLicId, usr_codes:[..._selectedUsr]}).then(r=>{
        Swal.close();
        if(r.status==='success'){
            _selectedUsr.clear();
            loadLicenses();
            loadAssignees(_activeLicId, {});
            loadMembersForAssign();
            lmTab('assigned');
            Swal.fire({toast:true,position:'bottom-end',icon:'success',title:r.message,showConfirmButton:false,timer:3000,timerProgressBar:true});
        } else {
            Swal.fire('Error', r.message, 'error');
        }
    });
};

/* ── Load departments for bulk assign ───────────────────── */
function loadDepts(){
    fetch(ORGAPI+'?action=list_departments').then(r=>r.json()).then(r=>{
        const sel = document.getElementById('lmBulkDept');
        sel.innerHTML = '<option value="">-- Select department --</option>';
        (r.departments||[]).forEach(d=>{
            sel.insertAdjacentHTML('beforeend',`<option value="${d.id}">${esc(d.dept_name)} (${d.member_count||0} members)</option>`);
        });
    });
}

/* ── Do bulk assign ─────────────────────────────────────── */
window.lmDoBulkAssign = function(){
    const deptId = document.getElementById('lmBulkDept').value;
    if(!deptId){ Swal.fire({toast:true,position:'bottom-end',icon:'info',title:'Please select a department',showConfirmButton:false,timer:2000}); return; }
    Swal.fire({title:'Assigning all department members…',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
    post({action:'bulk_assign', access_id:_activeLicId, dept_id:parseInt(deptId)}).then(r=>{
        Swal.close();
        if(r.status==='success'){
            loadLicenses();
            loadAssignees(_activeLicId, {});
            lmTab('assigned');
            Swal.fire({toast:true,position:'bottom-end',icon:'success',title:r.message,showConfirmButton:false,timer:3000,timerProgressBar:true});
        } else {
            Swal.fire('Error', r.message, 'error');
        }
    });
};

/* ── Init ────────────────────────────────────────────────── */
loadLicenses();
})();
</script>
<!-- Fallback thumbnail template -->
<template id="_lmThumbTpl">
    <div class="lm-thumb-placeholder"><i class="bi bi-play-circle-fill"></i></div>
</template>
