<?php
/**
 * instructor_announcements.php
 * Send & manage course announcements — role 3 (instructor) + role 5 (super admin)
 */
if (!in_array(($user_role ?? 0), [3, 5])) { include('403.php'); return; }

$me = $_SESSION['usr_code'];

/* ── Fetch instructor's courses ──────────────────────────────────────── */
$courses = [];
if ($user_role == 3) {
    $cq = $db->prepare("SELECT id, title FROM tbl_courses WHERE instructor_id = ? AND deleted_at IS NULL AND status='active' ORDER BY title");
    $cq->bind_param('s', $me);
    $cq->execute();
    $courses = $cq->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $cq = $db->query("SELECT id, title FROM tbl_courses WHERE deleted_at IS NULL AND status='active' ORDER BY title LIMIT 300");
    $courses = $cq->fetch_all(MYSQLI_ASSOC);
}

/* ── Fetch instructor's orgs (for org_only audience) ─────────────────── */
$orgs = [];
$oq = $db->prepare("
    SELECT o.org_code, o.org_name
    FROM tbl_organizations o
    JOIN tbl_org_members om ON om.org_code = o.org_code AND om.usr_code = ? AND om.status='active'
    WHERE o.deleted_at IS NULL
    ORDER BY o.org_name
");
$oq->bind_param('s', $me);
$oq->execute();
$orgs = $oq->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<style>
/* ── Instructor Announcements  ia-* ───────────────────────────────────── */
.ia-hero{background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 55%,#312e81 100%);padding:2rem 1.5rem 3.5rem;position:relative;overflow:hidden}
.ia-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")}
.ia-breadcrumb{display:flex;align-items:center;gap:.4rem;font-size:.78rem;color:rgba(255,255,255,.55);margin-bottom:.9rem}
.ia-breadcrumb a{color:rgba(255,255,255,.55);text-decoration:none}
.ia-breadcrumb a:hover{color:#fff}
.ia-breadcrumb .sep{opacity:.4}
.ia-hero-title{font-size:1.5rem;font-weight:700;color:#fff;margin-bottom:.25rem}
.ia-hero-sub{font-size:.85rem;color:rgba(255,255,255,.6);margin-bottom:1.2rem}
.ia-kpi-row{display:flex;flex-wrap:wrap;gap:.7rem}
.ia-kpi{background:rgba(255,255,255,.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.15);border-radius:.85rem;padding:.7rem 1.1rem;min-width:130px;flex:1}
.ia-kpi-val{font-size:1.5rem;font-weight:800;color:#fff;line-height:1.1}
.ia-kpi-lbl{font-size:.72rem;color:rgba(255,255,255,.6);margin-top:.15rem}
/* body */
.ia-body{background:#f8fafc;margin-top:-1.8rem;border-radius:1.2rem 1.2rem 0 0;padding:1.5rem;min-height:60vh;position:relative;z-index:1}
/* compose card */
.ia-card{background:#fff;border-radius:1rem;border:1px solid #e8ecf3;padding:1.4rem;margin-bottom:1.5rem}
.ia-card-title{font-size:.95rem;font-weight:700;color:#1e293b;margin-bottom:1.1rem;display:flex;align-items:center;gap:.5rem}
/* form fields */
.ia-label{font-size:.8rem;font-weight:600;color:#374151;margin-bottom:.3rem;display:block}
.ia-input{width:100%;border:1px solid #e2e8f0;border-radius:.55rem;padding:.45rem .75rem;font-size:.84rem;outline:none;transition:border-color .15s,box-shadow .15s;background:#fff}
.ia-input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
.ia-select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%2394a3b8'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right .7rem center;padding-right:2rem}
.ia-textarea{min-height:130px;resize:vertical;font-family:inherit;line-height:1.55}
.ia-row{display:grid;gap:.9rem}
.ia-row-2{grid-template-columns:1fr 1fr}
@media(max-width:640px){.ia-row-2{grid-template-columns:1fr}}
/* audience block */
.ia-audience-opts{display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:.7rem}
.ia-aud-btn{border:2px solid #e2e8f0;border-radius:.6rem;padding:.4rem .9rem;font-size:.8rem;font-weight:600;color:#64748b;background:#fff;cursor:pointer;transition:all .15s}
.ia-aud-btn:hover,.ia-aud-btn.active{border-color:#6366f1;color:#6366f1;background:#f5f3ff}
/* student multi-select */
.ia-student-list{max-height:200px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:.55rem;padding:.4rem;background:#fff}
.ia-student-item{display:flex;align-items:center;gap:.5rem;padding:.3rem .4rem;border-radius:.4rem;font-size:.8rem;cursor:pointer;transition:background .1s}
.ia-student-item:hover{background:#f5f3ff}
.ia-student-item input{accent-color:#6366f1;flex-shrink:0}
/* send button */
.ia-send-btn{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:.6rem;padding:.55rem 1.4rem;font-size:.875rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:.45rem;transition:opacity .15s;min-width:130px;justify-content:center}
.ia-send-btn:hover{opacity:.88}
.ia-send-btn:disabled{opacity:.55;cursor:not-allowed}
/* history table */
.ia-table-wrap{background:#fff;border-radius:.9rem;border:1px solid #e8ecf3;overflow:hidden}
.ia-table{width:100%;border-collapse:collapse;font-size:.82rem}
.ia-table thead th{background:#f8fafc;font-weight:700;color:#374151;padding:.7rem .9rem;border-bottom:2px solid #e2e8f0;white-space:nowrap}
.ia-table tbody td{padding:.65rem .9rem;border-bottom:1px solid #f1f5f9;color:#374151;vertical-align:middle}
.ia-table tbody tr:last-child td{border-bottom:none}
.ia-table tbody tr:hover td{background:#fafaff}
/* type badge */
.ia-type{font-size:.68rem;font-weight:700;padding:.2rem .55rem;border-radius:2rem;white-space:nowrap}
.ia-type.announcement{background:#ede9fe;color:#7c3aed}
.ia-type.reminder{background:#fef3c7;color:#d97706}
.ia-type.assignment_notice{background:#dbeafe;color:#2563eb}
.ia-type.assessment_notice{background:#fee2e2;color:#dc2626}
.ia-type.discussion{background:#d1fae5;color:#059669}
/* read rate bar */
.ia-rate{height:7px;border-radius:4px;background:#f1f5f9;overflow:hidden;min-width:70px;display:inline-block;vertical-align:middle}
.ia-rate-bar{height:100%;border-radius:4px;background:linear-gradient(90deg,#6366f1,#8b5cf6);transition:width .4s}
/* skeleton */
.ia-skel{border-radius:.4rem;background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:iaSkel 1.4s infinite;display:inline-block;height:.9em;vertical-align:middle}
@keyframes iaSkel{0%{background-position:200% 0}100%{background-position:-200% 0}}
/* empty */
.ia-empty{text-align:center;padding:3rem 1rem;color:#94a3b8}
.ia-empty i{font-size:2.8rem;display:block;margin-bottom:.7rem;opacity:.3}
/* file attachment preview */
.ia-attach-preview{font-size:.78rem;color:#64748b;margin-top:.4rem;display:flex;align-items:center;gap:.4rem}
/* course filter pill */
.ia-filter-row{display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;margin-bottom:1rem}
.ia-filter-row select{font-size:.82rem;border-radius:.5rem;border:1px solid #e2e8f0;padding:.35rem .7rem;background:#fff}
</style>

<!-- HERO -->
<div class="ia-hero">
    <div class="container-fluid px-3">
        <div class="ia-breadcrumb">
            <a href="?view=3002">Dashboard</a>
            <span class="sep">/</span>
            <span>Announcements</span>
        </div>
        <div class="ia-hero-title"><i class="bi bi-megaphone-fill me-2" style="color:#a78bfa"></i>Course Announcements</div>
        <div class="ia-hero-sub">Send targeted announcements to your enrolled students</div>
        <div class="ia-kpi-row">
            <div class="ia-kpi">
                <div class="ia-kpi-val" id="kpiTotalSent"><span class="ia-skel" style="width:30px"></span></div>
                <div class="ia-kpi-lbl">Total Sent</div>
            </div>
            <div class="ia-kpi">
                <div class="ia-kpi-val" id="kpiAvgRate"><span class="ia-skel" style="width:40px"></span></div>
                <div class="ia-kpi-lbl">Avg Read Rate</div>
            </div>
            <div class="ia-kpi">
                <div class="ia-kpi-val" id="kpiCourses"><span class="ia-skel" style="width:24px"></span></div>
                <div class="ia-kpi-lbl">Courses with Announcements</div>
            </div>
        </div>
    </div>
</div>

<!-- BODY -->
<div class="ia-body">
    <div class="container-fluid px-1">
        <div class="row g-3">

            <!-- COMPOSE FORM -->
            <div class="col-12 col-xl-5">
                <div class="ia-card">
                    <div class="ia-card-title"><i class="bi bi-pencil-square" style="color:#6366f1"></i> Compose Announcement</div>
                    <form id="iaForm" enctype="multipart/form-data">

                        <!-- Course -->
                        <div class="mb-3">
                            <label class="ia-label" for="iaCourse">Course <span style="color:#ef4444">*</span></label>
                            <select id="iaCourse" class="ia-input ia-select" required>
                                <option value="">Select a course...</option>
                                <?php foreach ($courses as $c): ?>
                                <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="ia-row ia-row-2 mb-3">
                            <!-- Subject -->
                            <div>
                                <label class="ia-label" for="iaSubject">Subject <span style="color:#ef4444">*</span></label>
                                <input type="text" id="iaSubject" class="ia-input" placeholder="Announcement subject..." maxlength="200" required>
                            </div>
                            <!-- Type -->
                            <div>
                                <label class="ia-label" for="iaType">Type</label>
                                <select id="iaType" class="ia-input ia-select">
                                    <option value="announcement">Announcement</option>
                                    <option value="reminder">Reminder</option>
                                    <option value="assignment_notice">Assignment Notice</option>
                                    <option value="assessment_notice">Assessment Notice</option>
                                    <option value="discussion">Discussion</option>
                                </select>
                            </div>
                        </div>

                        <!-- Audience -->
                        <div class="mb-3">
                            <label class="ia-label">Audience</label>
                            <div class="ia-audience-opts">
                                <button type="button" class="ia-aud-btn active" data-aud="all"><i class="bi bi-people-fill me-1"></i>All Enrolled</button>
                                <?php if (!empty($orgs)): ?>
                                <button type="button" class="ia-aud-btn" data-aud="org_only"><i class="bi bi-building me-1"></i>Org Only</button>
                                <?php endif; ?>
                                <button type="button" class="ia-aud-btn" data-aud="selected"><i class="bi bi-person-check me-1"></i>Selected</button>
                            </div>
                            <input type="hidden" id="iaAudience" value="all">

                            <!-- Org dropdown (hidden unless org_only) -->
                            <div id="iaOrgWrap" style="display:none">
                                <select id="iaOrgCode" class="ia-input ia-select">
                                    <option value="">Select organization...</option>
                                    <?php foreach ($orgs as $o): ?>
                                    <option value="<?= htmlspecialchars($o['org_code']) ?>"><?= htmlspecialchars($o['org_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Student picker (hidden unless selected) -->
                            <div id="iaStudentWrap" style="display:none;margin-top:.5rem">
                                <div id="iaStudentSearch" style="margin-bottom:.4rem">
                                    <input type="text" id="iaStudentFilter" class="ia-input" placeholder="Filter students..." style="font-size:.8rem;padding:.35rem .65rem">
                                </div>
                                <div class="ia-student-list" id="iaStudentList">
                                    <div style="text-align:center;color:#94a3b8;font-size:.8rem;padding:.8rem">Select a course first</div>
                                </div>
                                <div style="font-size:.74rem;color:#94a3b8;margin-top:.3rem"><span id="iaSelCount">0</span> students selected</div>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="mb-3">
                            <label class="ia-label" for="iaBody">Message <span style="color:#ef4444">*</span></label>
                            <textarea id="iaBody" class="ia-input ia-textarea" placeholder="Write your announcement here..." required></textarea>
                        </div>

                        <!-- Attachment -->
                        <div class="mb-3">
                            <label class="ia-label" for="iaAttachment">Attachment <span style="color:#94a3b8;font-weight:400">(optional)</span></label>
                            <input type="file" id="iaAttachment" class="ia-input" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif,.zip">
                            <div class="ia-attach-preview" id="iaAttachPreview" style="display:none">
                                <i class="bi bi-paperclip"></i><span id="iaAttachName"></span>
                                <button type="button" onclick="iaClearAttach()" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:.75rem;padding:0 .2rem">Remove</button>
                            </div>
                        </div>

                        <button type="submit" class="ia-send-btn" id="iaSendBtn">
                            <i class="bi bi-send-fill"></i> Send Announcement
                        </button>
                    </form>
                </div>
            </div>

            <!-- HISTORY -->
            <div class="col-12 col-xl-7">
                <div class="ia-card" style="padding:0;overflow:hidden">
                    <div style="padding:1.1rem 1.2rem .8rem;display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
                        <div class="ia-card-title" style="margin:0;flex:1"><i class="bi bi-clock-history" style="color:#6366f1"></i> Sent Announcements</div>
                        <div class="ia-filter-row" style="margin:0">
                            <select id="iaHistoryCourse" class="ia-input ia-select" style="font-size:.8rem;padding:.32rem .65rem">
                                <option value="">All Courses</option>
                                <?php foreach ($courses as $c): ?>
                                <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button onclick="iaLoadHistory()" class="btn btn-sm btn-outline-secondary" style="font-size:.78rem;border-radius:.45rem"><i class="bi bi-arrow-clockwise"></i></button>
                        </div>
                    </div>
                    <div class="ia-table-wrap" style="border-radius:0;border:none;border-top:1px solid #f1f5f9">
                        <table class="ia-table">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Course</th>
                                    <th>Type</th>
                                    <th>Sent</th>
                                    <th>Read Rate</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="iaHistoryBody">
                                <tr><td colspan="6" style="text-align:center;padding:2rem;color:#94a3b8"><div class="ia-skel" style="width:80%;height:1em;display:block;margin:.5rem auto"></div><div class="ia-skel" style="width:60%;height:1em;display:block;margin:.5rem auto"></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div><!-- row -->
    </div>
</div>

<!-- Stats Modal -->
<div id="iaStatsModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.45);padding:1rem;overflow-y:auto">
    <div style="max-width:540px;margin:2rem auto;background:#fff;border-radius:1rem;box-shadow:0 20px 60px rgba(0,0,0,.2);overflow:hidden">
        <div style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:1.1rem 1.3rem;display:flex;align-items:center;gap:.5rem">
            <span style="font-size:.95rem;font-weight:700;color:#fff;flex:1">Announcement Details</span>
            <button onclick="document.getElementById('iaStatsModal').style.display='none'" style="background:rgba(255,255,255,.2);border:none;color:#fff;border-radius:50%;width:28px;height:28px;cursor:pointer;font-size:1rem;line-height:1;display:flex;align-items:center;justify-content:center">&times;</button>
        </div>
        <div id="iaStatsBody" style="padding:1.3rem;font-size:.84rem">Loading...</div>
    </div>
</div>

<script>
(function(){
    const _ANN = '../data_files/ajax/ajax_announcements.php';

    /* ── KPI load ──────────────────────────────────────────────── */
    function loadKpi(){
        fetch(_ANN+'?action=get_announcement_stats').then(r=>r.json()).then(d=>{
            if(d.status!=='success') return;
            const s = d.data;
            document.getElementById('kpiTotalSent').textContent  = s.total_sent  || 0;
            document.getElementById('kpiAvgRate').textContent    = (s.avg_read_rate||0)+'%';
            document.getElementById('kpiCourses').textContent    = s.courses_count || 0;
        }).catch(()=>{});
    }
    loadKpi();

    /* ── History load ──────────────────────────────────────────── */
    window.iaLoadHistory = function(){
        const cid = document.getElementById('iaHistoryCourse').value;
        const url = _ANN+'?action=list_announcements_sent'+(cid?'&course_id='+cid:'');
        const tbody = document.getElementById('iaHistoryBody');
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem;color:#94a3b8"><i class="bi bi-hourglass-split" style="font-size:1.2rem;display:block;margin-bottom:.4rem;opacity:.4"></i>Loading...</td></tr>';
        fetch(url).then(r=>r.json()).then(d=>{
            if(d.status!=='success'||!d.data.length){
                tbody.innerHTML='<tr><td colspan="6"><div class="ia-empty"><i class="bi bi-megaphone"></i>No announcements sent yet</div></td></tr>';
                return;
            }
            const typeLabel = {announcement:'Announcement',reminder:'Reminder',assignment_notice:'Assignment',assessment_notice:'Assessment',discussion:'Discussion'};
            tbody.innerHTML = d.data.map(r=>{
                const rate = r.read_rate || 0;
                const dt   = r.sent_at ? new Date(r.sent_at).toLocaleDateString(undefined,{day:'2-digit',month:'short',year:'numeric'}) : '-';
                return `<tr>
                    <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${_iae(r.subject)}">${_iae(r.subject)}</td>
                    <td style="font-size:.78rem;color:#64748b;max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${_iae(r.course_title||'-')}</td>
                    <td><span class="ia-type ${r.type}">${typeLabel[r.type]||r.type}</span></td>
                    <td style="font-size:.78rem;color:#64748b;white-space:nowrap">${dt}</td>
                    <td style="white-space:nowrap">
                        <span class="ia-rate" style="width:60px"><span class="ia-rate-bar" style="width:${rate}%"></span></span>
                        <span style="font-size:.74rem;color:#64748b;margin-left:.4rem">${rate}%</span>
                        <span style="font-size:.72rem;color:#94a3b8;margin-left:.2rem">(${r.read_count}/${r.total_count})</span>
                    </td>
                    <td><button onclick="iaShowStats(${r.id})" class="btn btn-xs btn-outline-primary" style="font-size:.72rem;border-radius:.35rem;padding:.15rem .5rem"><i class="bi bi-bar-chart-line"></i></button></td>
                </tr>`;
            }).join('');
        }).catch(()=>{ tbody.innerHTML='<tr><td colspan="6" style="text-align:center;padding:1.5rem;color:#94a3b8">Failed to load</td></tr>'; });
    };
    iaLoadHistory();

    /* ── Stats modal ───────────────────────────────────────────── */
    window.iaShowStats = function(ann_id){
        document.getElementById('iaStatsModal').style.display = 'block';
        document.getElementById('iaStatsBody').innerHTML = '<div style="text-align:center;padding:2rem;color:#94a3b8"><i class="bi bi-hourglass-split" style="font-size:1.5rem;display:block;margin-bottom:.5rem;opacity:.4"></i>Loading...</div>';
        fetch(_ANN+'?action=get_announcement_stats&ann_id='+ann_id).then(r=>r.json()).then(d=>{
            if(d.status!=='success'){document.getElementById('iaStatsBody').innerHTML='<p style="color:#ef4444">Error loading stats</p>';return;}
            const s=d.data, recips=d.recipients||[];
            const dt = s.sent_at ? new Date(s.sent_at).toLocaleString() : '-';
            let rHtml = recips.length ? recips.map(r=>`
                <div style="display:flex;align-items:center;gap:.5rem;padding:.35rem 0;border-bottom:1px solid #f8fafc;font-size:.8rem">
                    <span style="flex:1;color:#374151">${_iae((r.first_name||'')+' '+(r.last_name||''))}</span>
                    <span style="color:#94a3b8;font-size:.75rem">${_iae(r.usr_code)}</span>
                    ${r.is_read==1
                        ? `<span style="color:#10b981;font-size:.75rem"><i class="bi bi-check2-circle"></i> Read</span>`
                        : `<span style="color:#94a3b8;font-size:.75rem"><i class="bi bi-circle"></i> Unread</span>`}
                </div>`).join('')
                : '<p style="color:#94a3b8;font-size:.8rem">No recipients found</p>';
            document.getElementById('iaStatsBody').innerHTML=`
                <div style="margin-bottom:1rem">
                    <div style="font-size:1rem;font-weight:700;color:#1e293b;margin-bottom:.25rem">${_iae(s.subject||'')}</div>
                    <div style="font-size:.78rem;color:#64748b">Sent: ${dt}</div>
                </div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.6rem;margin-bottom:1.1rem">
                    <div style="text-align:center;background:#f8fafc;border-radius:.6rem;padding:.7rem .5rem">
                        <div style="font-size:1.4rem;font-weight:800;color:#6366f1">${s.total_count||0}</div>
                        <div style="font-size:.7rem;color:#94a3b8">Recipients</div>
                    </div>
                    <div style="text-align:center;background:#f8fafc;border-radius:.6rem;padding:.7rem .5rem">
                        <div style="font-size:1.4rem;font-weight:800;color:#10b981">${s.read_count||0}</div>
                        <div style="font-size:.7rem;color:#94a3b8">Read</div>
                    </div>
                    <div style="text-align:center;background:#f8fafc;border-radius:.6rem;padding:.7rem .5rem">
                        <div style="font-size:1.4rem;font-weight:800;color:#f59e0b">${s.read_rate||0}%</div>
                        <div style="font-size:.7rem;color:#94a3b8">Read Rate</div>
                    </div>
                </div>
                <div style="max-height:280px;overflow-y:auto">${rHtml}</div>`;
        }).catch(()=>{ document.getElementById('iaStatsBody').innerHTML='<p style="color:#ef4444">Failed to load</p>'; });
    };

    /* ── Audience toggle ───────────────────────────────────────── */
    document.querySelectorAll('.ia-aud-btn').forEach(btn=>{
        btn.addEventListener('click',()=>{
            document.querySelectorAll('.ia-aud-btn').forEach(b=>b.classList.remove('active'));
            btn.classList.add('active');
            const aud = btn.dataset.aud;
            document.getElementById('iaAudience').value = aud;
            document.getElementById('iaOrgWrap').style.display     = aud==='org_only'  ? '' : 'none';
            document.getElementById('iaStudentWrap').style.display  = aud==='selected' ? '' : 'none';
            if(aud==='selected') iaLoadStudents();
        });
    });

    /* ── Course change: reload students if selected audience ───── */
    document.getElementById('iaCourse').addEventListener('change', function(){
        if(document.getElementById('iaAudience').value==='selected') iaLoadStudents();
    });

    /* ── Load students for selected audience ───────────────────── */
    let _allStudents = [];
    function iaLoadStudents(){
        const cid = document.getElementById('iaCourse').value;
        const list = document.getElementById('iaStudentList');
        if(!cid){ list.innerHTML='<div style="text-align:center;color:#94a3b8;font-size:.8rem;padding:.8rem">Select a course first</div>'; _allStudents=[]; return; }
        list.innerHTML='<div style="text-align:center;color:#94a3b8;font-size:.8rem;padding:.8rem"><i class="bi bi-hourglass-split"></i> Loading...</div>';
        fetch(_ANN+'?action=get_course_students&course_id='+cid).then(r=>r.json()).then(d=>{
            if(d.status!=='success'||!d.data.length){ list.innerHTML='<div style="text-align:center;color:#94a3b8;font-size:.8rem;padding:.8rem">No students enrolled</div>'; _allStudents=[]; return; }
            _allStudents = d.data;
            renderStudentList(d.data);
        });
    }
    function renderStudentList(students){
        const list = document.getElementById('iaStudentList');
        if(!students.length){ list.innerHTML='<div style="text-align:center;color:#94a3b8;font-size:.8rem;padding:.8rem">No students match</div>'; return; }
        list.innerHTML = students.map(s=>`
            <label class="ia-student-item">
                <input type="checkbox" class="ia-stu-chk" value="${_iae(s.usr_code)}">
                <span>${_iae(s.first_name+' '+s.last_name)}</span>
                <span style="color:#94a3b8;margin-left:auto;font-size:.73rem">${_iae(s.usr_code)}</span>
            </label>`).join('');
        document.querySelectorAll('.ia-stu-chk').forEach(cb=>cb.addEventListener('change',updateSelCount));
        updateSelCount();
    }
    function updateSelCount(){ document.getElementById('iaSelCount').textContent = document.querySelectorAll('.ia-stu-chk:checked').length; }

    document.getElementById('iaStudentFilter').addEventListener('input', function(){
        const q = this.value.toLowerCase();
        const filtered = _allStudents.filter(s=>(s.first_name+' '+s.last_name+s.usr_code).toLowerCase().includes(q));
        renderStudentList(filtered);
    });

    /* ── Attachment preview ────────────────────────────────────── */
    document.getElementById('iaAttachment').addEventListener('change', function(){
        if(this.files.length){
            document.getElementById('iaAttachName').textContent = this.files[0].name;
            document.getElementById('iaAttachPreview').style.display='flex';
        } else {
            document.getElementById('iaAttachPreview').style.display='none';
        }
    });
    window.iaClearAttach = function(){
        document.getElementById('iaAttachment').value='';
        document.getElementById('iaAttachPreview').style.display='none';
    };

    /* ── Form submit ───────────────────────────────────────────── */
    document.getElementById('iaForm').addEventListener('submit', function(e){
        e.preventDefault();
        const btn = document.getElementById('iaSendBtn');
        btn.disabled=true; btn.innerHTML='<i class="bi bi-hourglass-split"></i> Sending...';

        const fd = new FormData();
        fd.append('action','send_announcement');
        fd.append('course_id', document.getElementById('iaCourse').value);
        fd.append('subject',   document.getElementById('iaSubject').value);
        fd.append('type',      document.getElementById('iaType').value);
        fd.append('audience',  document.getElementById('iaAudience').value);
        fd.append('body',      document.getElementById('iaBody').value);
        fd.append('org_code',  document.getElementById('iaOrgCode')?.value||'');

        if(document.getElementById('iaAudience').value==='selected'){
            document.querySelectorAll('.ia-stu-chk:checked').forEach(cb=>fd.append('usr_codes[]', cb.value));
        }
        const file = document.getElementById('iaAttachment').files[0];
        if(file) fd.append('attachment', file);

        fetch(_ANN, {method:'POST', body:fd}).then(r=>r.json()).then(d=>{
            btn.disabled=false; btn.innerHTML='<i class="bi bi-send-fill"></i> Send Announcement';
            if(d.status==='success'){
                if(typeof Swal!=='undefined'){
                    Swal.fire({icon:'success',title:'Sent!',text:'Announcement delivered to '+d.recipients+' student(s)',confirmButtonColor:'#6366f1'});
                } else { alert('Sent to '+d.recipients+' student(s)'); }
                document.getElementById('iaForm').reset();
                document.getElementById('iaAttachPreview').style.display='none';
                document.querySelectorAll('.ia-aud-btn').forEach(b=>b.classList.remove('active'));
                document.querySelector('[data-aud="all"]').classList.add('active');
                document.getElementById('iaAudience').value='all';
                document.getElementById('iaOrgWrap').style.display='none';
                document.getElementById('iaStudentWrap').style.display='none';
                iaLoadHistory();
                loadKpi();
            } else {
                if(typeof Swal!=='undefined'){
                    Swal.fire({icon:'error',title:'Error',text:d.message||'Failed to send',confirmButtonColor:'#6366f1'});
                } else { alert('Error: '+(d.message||'Failed')); }
            }
        }).catch(()=>{
            btn.disabled=false; btn.innerHTML='<i class="bi bi-send-fill"></i> Send Announcement';
            alert('Network error. Please try again.');
        });
    });

    /* ── Utility ───────────────────────────────────────────────── */
    function _iae(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    /* close modal on backdrop click */
    document.getElementById('iaStatsModal').addEventListener('click', function(e){
        if(e.target===this) this.style.display='none';
    });
})();
</script>
