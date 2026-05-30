<?php
// Org Admin Reports — role 4 only
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
/* ── Org Reports ─────────────────────────────────────────── or-* ── */
.or-hero{background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 55%,#312e81 100%);padding:2rem 1.5rem 3.5rem;position:relative;overflow:hidden}
.or-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")}
.or-breadcrumb{display:flex;align-items:center;gap:.4rem;font-size:.78rem;color:rgba(255,255,255,.55);margin-bottom:.9rem}
.or-breadcrumb a{color:rgba(255,255,255,.55);text-decoration:none}.or-breadcrumb a:hover{color:#fff}
.or-breadcrumb .sep{opacity:.4}
.or-hero-title{font-size:1.5rem;font-weight:700;color:#fff;margin-bottom:.25rem}
.or-hero-sub{font-size:.85rem;color:rgba(255,255,255,.6);margin-bottom:1.2rem}
.or-stat-pills{display:flex;flex-wrap:wrap;gap:.6rem}
.or-stat-pill{background:rgba(255,255,255,.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.15);border-radius:2rem;padding:.35rem .85rem;color:#fff;font-size:.8rem;display:flex;align-items:center;gap:.45rem}
.or-stat-pill i{opacity:.7}
.or-body{background:#f8fafc;margin-top:-1.8rem;border-radius:1.2rem 1.2rem 0 0;padding:1.5rem;min-height:60vh;position:relative;z-index:1}
/* filter bar */
.or-filter-bar{display:flex;flex-wrap:wrap;gap:.6rem;align-items:center;margin-bottom:1.4rem}
.or-filter-bar select{font-size:.82rem;border-radius:.5rem;border:1px solid #e2e8f0;padding:.38rem .7rem;background:#fff;outline:none;min-width:150px}
.or-filter-bar select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12)}
.or-export-btn{margin-left:auto;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:.5rem;padding:.38rem .9rem;font-size:.82rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;transition:opacity .15s}
.or-export-btn:hover{opacity:.85}
/* tabs */
.or-tabs{display:flex;gap:0;border-bottom:2px solid #e2e8f0;margin-bottom:1.3rem}
.or-tab{border:none;background:none;padding:.6rem 1.1rem;font-size:.84rem;font-weight:600;color:#64748b;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;transition:color .15s,border-color .15s}
.or-tab.active{color:#6366f1;border-bottom-color:#6366f1}
.or-tab:hover:not(.active){color:#374151}
.or-tab-pane{display:none}.or-tab-pane.active{display:block}
/* tables */
.or-table-wrap{background:#fff;border-radius:.9rem;border:1px solid #e8ecf3;overflow:hidden}
.or-table{width:100%;border-collapse:collapse;font-size:.82rem}
.or-table thead th{background:#f8fafc;font-weight:700;color:#374151;padding:.7rem .9rem;border-bottom:2px solid #e2e8f0;white-space:nowrap}
.or-table tbody td{padding:.65rem .9rem;border-bottom:1px solid #f1f5f9;color:#374151;vertical-align:middle}
.or-table tbody tr:last-child td{border-bottom:none}
.or-table tbody tr:hover td{background:#fafaff}
/* progress bar */
.or-prog{height:7px;border-radius:4px;background:#f1f5f9;overflow:hidden;min-width:80px}
.or-prog-bar{height:100%;border-radius:4px;background:linear-gradient(90deg,#6366f1,#8b5cf6);transition:width .4s ease}
.or-prog-bar.green{background:linear-gradient(90deg,#10b981,#34d399)}
/* dept cards */
.or-dept-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:1rem}
.or-dept-card{background:#fff;border:1px solid #e8ecf3;border-radius:.9rem;padding:1.1rem;transition:box-shadow .15s}
.or-dept-card:hover{box-shadow:0 6px 20px rgba(99,102,241,.1)}
.or-dept-kpi{display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;margin:.8rem 0 .7rem}
.or-dept-kpi-item{text-align:center;background:#f8fafc;border-radius:.5rem;padding:.5rem .3rem}
.or-dept-kpi-val{font-size:1.1rem;font-weight:800;line-height:1}
.or-dept-kpi-lbl{font-size:.68rem;color:#94a3b8;margin-top:.18rem}
/* badges */
.or-role-badge{font-size:.68rem;font-weight:700;padding:.2rem .55rem;border-radius:2rem}
.or-role-badge.admin{background:#fee2e2;color:#dc2626}
.or-role-badge.instructor{background:#dbeafe;color:#2563eb}
.or-role-badge.student{background:#dcfce7;color:#16a34a}
.or-role-badge.staff{background:#f1f5f9;color:#475569}
.or-role-badge.coordinator{background:#fef9c3;color:#a16207}
/* skeleton */
.or-skel{border-radius:.4rem;background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:orSkel 1.4s infinite;display:inline-block}
@keyframes orSkel{0%{background-position:200% 0}100%{background-position:-200% 0}}
/* empty */
.or-empty{text-align:center;padding:3.5rem 1rem;color:#94a3b8}
.or-empty i{font-size:3rem;display:block;margin-bottom:.8rem;opacity:.35}
</style>

<!-- Hero -->
<div class="or-hero">
    <div class="or-breadcrumb">
        <a href="?view=org_dashboard"><i class="bi bi-house-fill"></i></a>
        <span class="sep">/</span>
        <span><?= htmlspecialchars($orgRow['org_name']) ?></span>
        <span class="sep">/</span>
        <span style="color:#fff">Reports</span>
    </div>
    <div class="or-hero-title"><i class="bi bi-bar-chart-line-fill me-2" style="color:#a5b4fc"></i>Learning Reports</div>
    <div class="or-hero-sub">Progress and engagement analytics across your organization</div>
    <div class="or-stat-pills">
        <div class="or-stat-pill"><i class="bi bi-person-fill-check"></i><span id="orPillLearners">—</span> Active Learners</div>
        <div class="or-stat-pill"><i class="bi bi-book-fill"></i><span id="orPillEnrolled">—</span> Enrollments</div>
        <div class="or-stat-pill"><i class="bi bi-trophy-fill"></i><span id="orPillCompleted">—</span> Completed</div>
        <div class="or-stat-pill"><i class="bi bi-graph-up-arrow"></i><span id="orPillRate">—</span>% Avg Rate</div>
    </div>
</div>

<!-- Body -->
<div class="or-body">

    <!-- Filter bar -->
    <div class="or-filter-bar">
        <select id="orDeptFilter" onchange="orLoad()">
            <option value="">All Departments</option>
        </select>
        <select id="orPeriod" onchange="orLoad()">
            <option value="30">Last 30 days</option>
            <option value="90">Last 90 days</option>
            <option value="180">Last 6 months</option>
            <option value="365">Last year</option>
            <option value="0">All time</option>
        </select>
        <button class="or-export-btn" onclick="orExport()">
            <i class="bi bi-download"></i>Export CSV
        </button>
    </div>

    <!-- Tabs -->
    <div class="or-tabs">
        <button class="or-tab active" onclick="orSwitchTab('member', this)">
            <i class="bi bi-people me-1"></i>Member Progress
        </button>
        <button class="or-tab" onclick="orSwitchTab('course', this)">
            <i class="bi bi-journal-bookmark me-1"></i>Course Engagement
        </button>
        <button class="or-tab" onclick="orSwitchTab('dept', this)">
            <i class="bi bi-diagram-3 me-1"></i>Department Summary
        </button>
    </div>

    <!-- Tab panes -->
    <div class="or-tab-pane active" id="orPane-member">
        <div class="or-table-wrap">
            <div class="table-responsive">
                <table class="or-table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Enrolled</th>
                            <th>Completed</th>
                            <th style="min-width:140px">Avg Progress</th>
                            <th>Last Active</th>
                        </tr>
                    </thead>
                    <tbody id="orMemberTbody">
                        <tr><td colspan="7" class="text-center py-5">
                            <div class="spinner-border text-primary spinner-border-sm me-2"></div>Loading…
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="or-tab-pane" id="orPane-course">
        <div class="or-table-wrap">
            <div class="table-responsive">
                <table class="or-table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Enrolled</th>
                            <th>Completions</th>
                            <th style="min-width:140px">Completion Rate</th>
                            <th>Avg Score</th>
                        </tr>
                    </thead>
                    <tbody id="orCourseTbody">
                        <tr><td colspan="5" class="text-center py-5 text-muted">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="or-tab-pane" id="orPane-dept">
        <div class="or-dept-grid" id="orDeptGrid">
            <div class="text-center py-5 text-muted" style="grid-column:1/-1">
                <div class="spinner-border text-primary spinner-border-sm me-2"></div>Loading…
            </div>
        </div>
    </div>

</div>

<script>
const OR_AJAX = '../data_files/ajax/ajax_org_admin.php';
let orData = {};
let orActiveTab = 'member';

async function orInit() {
    const dr = await fetch(`${OR_AJAX}?action=list_departments`).then(x=>x.json()).catch(()=>({}));
    const depts = dr.departments ?? [];
    const sel = document.getElementById('orDeptFilter');
    depts.forEach(d => sel.appendChild(new Option(d.dept_name, d.id)));
    orLoad();
}

window.orLoad = async function() {
    orShowLoading();

    const params = new URLSearchParams({
        action: 'get_reports',
        dept:   document.getElementById('orDeptFilter').value,
        period: document.getElementById('orPeriod').value,
    });

    const r = await fetch(`${OR_AJAX}?${params}`).then(x=>x.json()).catch(()=>({}));

    if (r.status !== 'success') {
        orShowError(r.message || 'Failed to load report data');
        return;
    }
    orData = r;

    // Pills
    document.getElementById('orPillLearners').textContent  = r.active_learners  ?? 0;
    document.getElementById('orPillEnrolled').textContent  = r.total_enrolled   ?? 0;
    document.getElementById('orPillCompleted').textContent = r.total_completed  ?? 0;
    document.getElementById('orPillRate').textContent      = r.avg_completion   ?? 0;

    orRenderMembers(r.members  ?? []);
    orRenderCourses(r.courses  ?? []);
    orRenderDepts(r.departments ?? []);
}

function orShowLoading() {
    document.getElementById('orMemberTbody').innerHTML  =
        '<tr><td colspan="7" class="text-center py-5"><div class="spinner-border text-primary spinner-border-sm me-2"></div>Loading…</td></tr>';
    document.getElementById('orCourseTbody').innerHTML  =
        '<tr><td colspan="5" class="text-center py-5 text-muted">Loading…</td></tr>';
    document.getElementById('orDeptGrid').innerHTML =
        '<div class="text-center py-5 text-muted" style="grid-column:1/-1"><div class="spinner-border text-primary spinner-border-sm me-2"></div>Loading…</div>';
}

function orShowError(msg) {
    const errHtml = `<tr><td colspan="7" class="text-center py-5 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>${orEsc(msg)}</td></tr>`;
    document.getElementById('orMemberTbody').innerHTML  = errHtml;
    document.getElementById('orCourseTbody').innerHTML  = errHtml.replace('colspan="7"','colspan="5"');
    document.getElementById('orDeptGrid').innerHTML     = `<div class="or-empty" style="grid-column:1/-1"><i class="bi bi-exclamation-triangle"></i><div>${orEsc(msg)}</div></div>`;
}

function orRenderMembers(members) {
    const tb = document.getElementById('orMemberTbody');
    if (!members.length) {
        tb.innerHTML = '<tr><td colspan="7" class="text-center py-5"><div class="or-empty" style="padding:1rem"><i class="bi bi-people"></i><div>No member data for this period.</div></div></td></tr>';
        return;
    }
    tb.innerHTML = members.map(m => {
        const pct     = Math.min(100, m.avg_progress || 0);
        const initials = ((m.first_name||'').charAt(0) + (m.last_name||'').charAt(0)).toUpperCase();
        const roleCls  = { admin:'admin', instructor:'instructor', student:'student', staff:'staff', coordinator:'coordinator' };
        return `<tr>
            <td>
                <div style="display:flex;align-items:center;gap:.6rem">
                    <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-size:.72rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0">${initials||'?'}</div>
                    <div>
                        <div style="font-weight:600">${orEsc(m.first_name)} ${orEsc(m.last_name)}</div>
                        <div style="font-size:.72rem;color:#94a3b8">${orEsc(m.email)}</div>
                    </div>
                </div>
            </td>
            <td><span class="or-role-badge ${roleCls[m.org_role]||'staff'}">${orEsc(m.org_role)}</span></td>
            <td>${orEsc(m.dept_name||'—')}</td>
            <td style="font-weight:600">${m.enrolled_courses||0}</td>
            <td><span style="color:#16a34a;font-weight:600">${m.completed_courses||0}</span></td>
            <td>
                <div style="display:flex;align-items:center;gap:.5rem">
                    <div class="or-prog flex-grow-1"><div class="or-prog-bar" style="width:${pct}%"></div></div>
                    <span style="font-size:.75rem;min-width:34px;color:#6366f1;font-weight:600">${pct}%</span>
                </div>
            </td>
            <td style="color:#94a3b8;font-size:.78rem">${m.last_active ? orFmtDate(m.last_active) : '—'}</td>
        </tr>`;
    }).join('');
}

function orRenderCourses(courses) {
    const tb = document.getElementById('orCourseTbody');
    if (!courses.length) {
        tb.innerHTML = '<tr><td colspan="5" class="text-center py-5"><div class="or-empty" style="padding:1rem"><i class="bi bi-journal-bookmark"></i><div>No course data for this period.</div></div></td></tr>';
        return;
    }
    tb.innerHTML = courses.map(c => {
        const rate = c.enrolled > 0 ? Math.round((c.completed / c.enrolled) * 100) : 0;
        return `<tr>
            <td style="font-weight:600">${orEsc(c.title)}</td>
            <td>${c.enrolled||0}</td>
            <td><span style="color:#16a34a;font-weight:600">${c.completed||0}</span></td>
            <td>
                <div style="display:flex;align-items:center;gap:.5rem">
                    <div class="or-prog flex-grow-1"><div class="or-prog-bar green" style="width:${rate}%"></div></div>
                    <span style="font-size:.75rem;min-width:34px;color:#10b981;font-weight:600">${rate}%</span>
                </div>
            </td>
            <td>${c.avg_score != null ? `<strong>${c.avg_score}%</strong>` : '<span style="color:#94a3b8">—</span>'}</td>
        </tr>`;
    }).join('');
}

function orRenderDepts(depts) {
    const grid = document.getElementById('orDeptGrid');
    if (!depts.length) {
        grid.innerHTML = `<div class="or-empty" style="grid-column:1/-1"><i class="bi bi-diagram-3"></i><div>No department data available.</div></div>`;
        return;
    }
    grid.innerHTML = depts.map(d => {
        const rate = Math.min(100, d.avg_completion || 0);
        return `
        <div class="or-dept-card">
            <div style="display:flex;align-items:center;gap:.7rem;margin-bottom:.3rem">
                <div style="width:36px;height:36px;border-radius:.6rem;background:linear-gradient(135deg,rgba(99,102,241,.12),rgba(139,92,246,.12));display:flex;align-items:center;justify-content:center;font-size:1rem;color:#6366f1;flex-shrink:0">
                    <i class="bi bi-building-fill"></i>
                </div>
                <div>
                    <div style="font-weight:700;font-size:.9rem;color:#1e293b">${orEsc(d.dept_name)}</div>
                    <div style="font-size:.72rem;color:#94a3b8">${d.member_count||0} member${d.member_count!=1?'s':''}</div>
                </div>
            </div>
            <div class="or-dept-kpi">
                <div class="or-dept-kpi-item">
                    <div class="or-dept-kpi-val" style="color:#6366f1">${d.enrolled||0}</div>
                    <div class="or-dept-kpi-lbl">Enrolled</div>
                </div>
                <div class="or-dept-kpi-item">
                    <div class="or-dept-kpi-val" style="color:#16a34a">${d.completed||0}</div>
                    <div class="or-dept-kpi-lbl">Completed</div>
                </div>
                <div class="or-dept-kpi-item">
                    <div class="or-dept-kpi-val" style="color:#8b5cf6">${rate}%</div>
                    <div class="or-dept-kpi-lbl">Avg Rate</div>
                </div>
            </div>
            <div class="or-prog" style="height:8px">
                <div class="or-prog-bar" style="width:${rate}%"></div>
            </div>
        </div>`;
    }).join('');
}

window.orSwitchTab = function(name, el) {
    orActiveTab = name;
    document.querySelectorAll('.or-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.or-tab-pane').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    document.getElementById(`orPane-${name}`).classList.add('active');
}

window.orExport = function() {
    const members = orData.members ?? [];
    if (!members.length) {
        orToast('No member data to export', 'warning');
        return;
    }
    const rows = [
        ['Name','Email','Role','Department','Enrolled','Completed','Avg Progress','Last Active'],
        ...members.map(m => [
            `${m.first_name} ${m.last_name}`, m.email, m.org_role, m.dept_name||'',
            m.enrolled_courses||0, m.completed_courses||0, (m.avg_progress||0)+'%', m.last_active||''
        ])
    ];
    const csv  = rows.map(r => r.map(c => `"${String(c).replace(/"/g,'""')}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const a    = document.createElement('a');
    a.href     = URL.createObjectURL(blob);
    a.download = `org_report_${new Date().toISOString().slice(0,10)}.csv`;
    a.click();
    orToast('CSV exported');
}

/* ── helpers ── */
const orEsc    = s => (s+'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
const orFmtDate = s => s ? new Date(s).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : '—';

function orToast(msg, type = 'success') {
    const colors = { success:'#16a34a', danger:'#dc2626', warning:'#d97706', info:'#0891b2' };
    const icons  = { success:'bi-check-circle-fill', danger:'bi-x-circle-fill', warning:'bi-exclamation-triangle-fill', info:'bi-info-circle-fill' };
    let c = document.getElementById('orToastWrap');
    if (!c) {
        c = Object.assign(document.createElement('div'), { id: 'orToastWrap' });
        c.style.cssText = 'position:fixed;bottom:1.2rem;right:1.2rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem';
        document.body.appendChild(c);
    }
    const t = document.createElement('div');
    t.style.cssText = `background:${colors[type]||colors.success};color:#fff;padding:.65rem 1rem;border-radius:.65rem;font-size:.84rem;box-shadow:0 4px 16px rgba(0,0,0,.18);display:flex;align-items:center;gap:.5rem;max-width:320px;animation:orFadeUp .2s ease`;
    t.innerHTML = `<i class="bi ${icons[type]||icons.success}" style="flex-shrink:0"></i><span>${msg}</span>`;
    c.appendChild(t);
    setTimeout(() => t.remove(), 3800);
}

if (!document.getElementById('orFadeKf')) {
    const s = document.createElement('style');
    s.id = 'orFadeKf';
    s.textContent = '@keyframes orFadeUp{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}';
    document.head.appendChild(s);
}

orInit();
</script>
