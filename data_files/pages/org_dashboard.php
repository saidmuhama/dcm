<?php
// Org Admin Dashboard — role 4 only
if (($user_role ?? 0) != 4) { include('403.php'); return; }

$me = $_SESSION['usr_code'];

$orgRow = $db->query("
    SELECT o.org_name, o.org_code, o.status, o.max_users, o.storage_limit_gb,
           o.license_expires_at, o.logo, o.org_type
    FROM tbl_organizations o
    INNER JOIN tbl_org_members m ON m.org_code = o.org_code
    WHERE m.usr_code = '$me' AND m.org_role = 'admin' AND m.status = 'active'
      AND o.deleted_at IS NULL
    LIMIT 1
")->fetch_assoc();

/* ── Auto-provision org on first login ── */
if (!$orgRow) {
    $userRow = $db->query("SELECT first_name, last_name FROM tbl_all_users WHERE usr_code='$me' LIMIT 1")->fetch_assoc();
    $rawName = trim(($userRow['first_name'] ?? '') . ' ' . ($userRow['last_name'] ?? ''));
    $orgName = $db->real_escape_string($rawName . "'s Organization");
    $orgCode = 'ORG' . strtoupper(substr(md5($me . time()), 0, 8));
    $db->query("INSERT INTO tbl_organizations (org_code, org_name, org_type, status, max_users) VALUES ('$orgCode', '$orgName', 'school', 'active', -1)");
    $db->query("INSERT INTO tbl_org_members (org_code, usr_code, org_role, status) VALUES ('$orgCode', '$me', 'admin', 'active')");
    $orgRow = $db->query("SELECT org_name, org_code, status, max_users, storage_limit_gb, license_expires_at, logo, org_type FROM tbl_organizations WHERE org_code='$orgCode' LIMIT 1")->fetch_assoc();
}

/* ── License badge data ── */
$licExp    = $orgRow['license_expires_at'] ?? null;
$daysLeft  = $licExp ? (int)((strtotime($licExp) - time()) / 86400) : null;
$licUrgent = $daysLeft !== null && $daysLeft < 30;
$maxUsers  = $orgRow['max_users'] == -1 ? '∞' : number_format($orgRow['max_users']);
$orgInitials = strtoupper(substr(preg_replace('/[^a-zA-Z ]/', '', $orgRow['org_name']), 0, 2));
$orgTypeLabel = ucfirst(str_replace('_', ' ', $orgRow['org_type'] ?? 'school'));
?>
<style>
/* ═══ Org Dashboard (odb-*) ═══════════════════════════════════════ */

/* ── Hero ── */
.odb-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 45%, #312e81 100%);
    padding: 2.5rem 0 4.5rem;
    position: relative; overflow: hidden;
}
.odb-hero::before {
    content: '';
    position: absolute; inset: 0;
    background-image:
        radial-gradient(circle at 15% 55%, rgba(99,102,241,.22) 0%, transparent 55%),
        radial-gradient(circle at 85% 15%, rgba(139,92,246,.15) 0%, transparent 48%);
    pointer-events: none;
}
.odb-hero::after {
    content: '';
    position: absolute; inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.025'%3E%3Ccircle cx='20' cy='20' r='1.5'/%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.odb-org-avatar {
    width: 64px; height: 64px; border-radius: 16px; flex-shrink: 0;
    border: 2px solid rgba(255,255,255,.2);
    box-shadow: 0 8px 24px rgba(0,0,0,.35);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; font-weight: 800; color: #fff;
    background: linear-gradient(135deg,#6366f1,#8b5cf6);
    overflow: hidden;
}
.odb-org-avatar img { width: 100%; height: 100%; object-fit: contain; }

.odb-hero-badge {
    display: inline-flex; align-items: center; gap: .35rem;
    background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.18);
    color: #c7d2fe; font-size: .72rem; font-weight: 600;
    padding: .28rem .75rem; border-radius: 100px; backdrop-filter: blur(4px);
}
.odb-hero-badge.danger { background: rgba(239,68,68,.15); border-color: rgba(239,68,68,.3); color: #fca5a5; }
.odb-hero-badge.success { background: rgba(34,197,94,.12); border-color: rgba(34,197,94,.25); color: #86efac; }

.odb-hero-stat {
    background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.1);
    border-radius: 14px; padding: .65rem 1.1rem; text-align: center;
    backdrop-filter: blur(4px); min-width: 90px;
}
.odb-hero-stat .val { font-size: 1.15rem; font-weight: 800; color: #fff; line-height: 1; }
.odb-hero-stat .lbl { font-size: .65rem; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: .04em; margin-top: .2rem; }

/* ── Canvas ── */
.odb-canvas {
    max-width: 1280px; margin: -2.5rem auto 0;
    padding: 0 1.25rem 3rem; position: relative; z-index: 10;
}

/* ── Metric cards ── */
.odb-metric {
    background: #fff; border-radius: 18px; padding: 1.35rem 1.5rem;
    box-shadow: 0 2px 14px rgba(0,0,0,.07); border: 1px solid rgba(0,0,0,.05);
    display: flex; align-items: center; gap: 1rem;
    transition: transform .2s, box-shadow .2s;
}
.odb-metric:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(0,0,0,.1); }
.odb-metric-icon {
    width: 54px; height: 54px; border-radius: 15px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; flex-shrink: 0;
}
.odb-metric-val { font-size: 1.6rem; font-weight: 800; line-height: 1; color: #1e293b; }
.odb-metric-lbl { font-size: .75rem; color: #64748b; margin-top: .2rem; }
.odb-metric-sub { font-size: .68rem; color: #94a3b8; margin-top: .12rem; }

/* ── Section cards ── */
.odb-card {
    background: #fff; border-radius: 18px;
    box-shadow: 0 2px 14px rgba(0,0,0,.07); border: 1px solid rgba(0,0,0,.05);
    overflow: hidden; margin-bottom: 1.25rem;
}
.odb-card-header {
    padding: 1rem 1.5rem; border-bottom: 1px solid #f1f5f9;
    display: flex; align-items: center; justify-content: space-between; gap: .75rem;
}
.odb-card-title-wrap { display: flex; align-items: center; gap: .7rem; }
.odb-card-icon {
    width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 1rem;
}
.odb-card-title { font-size: .9rem; font-weight: 700; color: #1e293b; margin: 0; }
.odb-card-sub   { font-size: .72rem; color: #94a3b8; }
.odb-card-body  { padding: 1.25rem 1.5rem; }
.odb-card-link  {
    font-size: .76rem; font-weight: 600; color: #6366f1; text-decoration: none;
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .35rem .8rem; border-radius: 8px; border: 1px solid #e0e7ff;
    background: #f5f3ff; transition: all .15s;
}
.odb-card-link:hover { background: #ede9fe; border-color: #c4b5fd; color: #4f46e5; }

/* ── Role bars ── */
.odb-role-row { display: flex; align-items: center; gap: .75rem; padding: .55rem 0; border-bottom: 1px solid #f8fafc; }
.odb-role-row:last-child { border-bottom: none; }
.odb-role-dot  { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.odb-role-name { font-size: .82rem; font-weight: 600; color: #374151; text-transform: capitalize; min-width: 90px; }
.odb-role-bar-wrap { flex: 1; height: 7px; background: #f1f5f9; border-radius: 100px; overflow: hidden; }
.odb-role-bar  { height: 100%; border-radius: 100px; transition: width 1s cubic-bezier(.16,1,.3,1); }
.odb-role-count { font-size: .8rem; font-weight: 700; color: #1e293b; min-width: 24px; text-align: right; }

/* ── Activity feed ── */
.odb-act-item {
    display: flex; align-items: flex-start; gap: .85rem;
    padding: .75rem 0; border-bottom: 1px solid #f8fafc;
}
.odb-act-item:last-child { border-bottom: none; }
.odb-act-icon {
    width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: .85rem;
}
.odb-act-actor { font-size: .83rem; font-weight: 600; color: #1e293b; }
.odb-act-desc  { font-size: .75rem; color: #64748b; margin-top: .1rem; }
.odb-act-time  { font-size: .7rem; color: #94a3b8; margin-left: auto; flex-shrink: 0; padding-top: .15rem; white-space: nowrap; }

/* ── Learners table ── */
.odb-learner-row { display: flex; align-items: center; gap: .85rem; padding: .7rem 0; border-bottom: 1px solid #f8fafc; }
.odb-learner-row:last-child { border-bottom: none; }
.odb-learner-rank { font-size: .75rem; font-weight: 800; color: #94a3b8; width: 20px; text-align: center; flex-shrink: 0; }
.odb-learner-rank.top { color: #f59e0b; }
.odb-learner-avatar {
    width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg,#6366f1,#8b5cf6);
    display: flex; align-items: center; justify-content: center;
    font-size: .75rem; font-weight: 700; color: #fff;
}
.odb-learner-name { font-size: .83rem; font-weight: 600; color: #1e293b; }
.odb-learner-email { font-size: .7rem; color: #94a3b8; }
.odb-learner-bar-wrap { flex: 1; height: 6px; background: #f1f5f9; border-radius: 100px; overflow: hidden; }
.odb-learner-bar { height: 100%; border-radius: 100px; background: linear-gradient(90deg,#6366f1,#8b5cf6); }
.odb-learner-pct { font-size: .75rem; font-weight: 700; color: #1e293b; min-width: 36px; text-align: right; }

/* ── Quick actions ── */
.odb-qa-btn {
    display: flex; flex-direction: column; align-items: center; gap: .5rem;
    padding: 1.1rem .75rem; border-radius: 14px; text-decoration: none;
    border: 1.5px solid transparent; transition: all .18s; cursor: pointer;
    background: #f8fafc; color: #475569;
}
.odb-qa-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.09); }
.odb-qa-btn .qa-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
    transition: transform .2s;
}
.odb-qa-btn:hover .qa-icon { transform: scale(1.1); }
.odb-qa-btn .qa-label { font-size: .75rem; font-weight: 700; text-align: center; line-height: 1.3; }

/* ── Skeleton ── */
.odb-skel {
    background: linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);
    background-size: 200% 100%; animation: odb-skel 1.5s infinite; border-radius: 8px;
}
@keyframes odb-skel { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ── Empty state ── */
.odb-empty { text-align: center; padding: 2rem 1rem; }
.odb-empty i { font-size: 2rem; color: #d1d5db; display: block; margin-bottom: .5rem; }
.odb-empty p { font-size: .82rem; color: #94a3b8; margin: 0; }

/* Dark mode compat */
@media (prefers-color-scheme: dark) {
    .odb-metric,.odb-card { background: #1e293b; border-color: rgba(255,255,255,.06); }
    .odb-metric-val,.odb-card-title,.odb-learner-name,.odb-act-actor,.odb-role-count { color: #f1f5f9; }
    .odb-role-bar-wrap,.odb-learner-bar-wrap { background: rgba(255,255,255,.08); }
    .odb-qa-btn { background: #1e293b; border-color: rgba(255,255,255,.07); }
    .odb-role-row,.odb-act-item,.odb-learner-row,.odb-card-header { border-color: rgba(255,255,255,.06); }
    .odb-skel { background: linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%); background-size:200% 100%; }
}
</style>

<!-- ═══════════════════════════ HERO ═══════════════════════════ -->
<div class="odb-hero">
    <div class="container-xl position-relative" style="z-index:2">

        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0" style="font-size:.75rem">
                <li class="breadcrumb-item"><span class="text-white-50">Dashboard</span></li>
                <li class="breadcrumb-item active" style="color:rgba(255,255,255,.5)"><?= htmlspecialchars($orgRow['org_name']) ?></li>
            </ol>
        </nav>

        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 mb-4">

            <!-- Org avatar -->
            <div class="odb-org-avatar">
                <?php if ($orgRow['logo']): ?>
                    <img src="uploads/org_logos/<?= htmlspecialchars(basename($orgRow['logo'])) ?>" alt="">
                <?php else: ?>
                    <?= $orgInitials ?>
                <?php endif; ?>
            </div>

            <!-- Name + meta -->
            <div class="flex-grow-1">
                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                    <h4 class="text-white fw-bold mb-0"><?= htmlspecialchars($orgRow['org_name']) ?></h4>
                    <span class="odb-hero-badge"><i class="bi bi-buildings me-1"></i><?= $orgTypeLabel ?></span>
                    <?php if ($licUrgent): ?>
                        <span class="odb-hero-badge danger"><i class="bi bi-exclamation-triangle me-1"></i>License expires in <?= $daysLeft ?> days</span>
                    <?php elseif ($daysLeft !== null): ?>
                        <span class="odb-hero-badge success"><i class="bi bi-shield-check me-1"></i>License active</span>
                    <?php endif; ?>
                </div>
                <div style="font-size:.78rem;color:rgba(255,255,255,.5)">
                    <i class="bi bi-key me-1"></i><?= htmlspecialchars($orgRow['org_code']) ?>
                    <?php if ($licExp): ?>
                        &nbsp;·&nbsp;<i class="bi bi-calendar3 me-1"></i>Expires <?= date('M j, Y', strtotime($licExp)) ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Hero stats (filled by JS) -->
            <div class="d-flex gap-2 flex-wrap flex-shrink-0 mt-2 mt-md-0">
                <div class="odb-hero-stat"><div class="val" id="hStatMembers">—</div><div class="lbl">Members</div></div>
                <div class="odb-hero-stat"><div class="val" id="hStatActive">—</div><div class="lbl">Active</div></div>
                <div class="odb-hero-stat"><div class="val" id="hStatCourses">—</div><div class="lbl">Courses</div></div>
                <div class="odb-hero-stat"><div class="val" id="hStatCompletion">—</div><div class="lbl">Completion</div></div>
            </div>
        </div>

    </div>
</div>

<!-- ═══════════════════════════ CANVAS ═══════════════════════════ -->
<div class="odb-canvas">

    <!-- Metric cards -->
    <div class="row g-3 mb-4">
        <?php
        $metrics = [
            ['id'=>'mc0','icon'=>'bi-people-fill',      'color'=>'#6366f1','bg'=>'#eef2ff','label'=>'Total Members',   'sub'=>'of '.$maxUsers.' seats'],
            ['id'=>'mc1','icon'=>'bi-person-check-fill','color'=>'#0ea5e9','bg'=>'#e0f2fe','label'=>'Active Users',     'sub'=>'currently active'],
            ['id'=>'mc2','icon'=>'bi-collection-play',  'color'=>'#10b981','bg'=>'#d1fae5','label'=>'Courses Available','sub'=>'accessible to org'],
            ['id'=>'mc3','icon'=>'bi-graph-up-arrow',   'color'=>'#f59e0b','bg'=>'#fef3c7','label'=>'Avg Completion',   'sub'=>'across all learners'],
        ];
        foreach ($metrics as $m): ?>
        <div class="col-6 col-lg-3">
            <div class="odb-metric">
                <div class="odb-metric-icon" style="background:<?= $m['bg'] ?>;color:<?= $m['color'] ?>">
                    <i class="bi <?= $m['icon'] ?>"></i>
                </div>
                <div>
                    <div class="odb-metric-val" id="<?= $m['id'] ?>">
                        <span class="odb-skel d-inline-block" style="width:52px;height:24px"></span>
                    </div>
                    <div class="odb-metric-lbl"><?= $m['label'] ?></div>
                    <div class="odb-metric-sub"><?= $m['sub'] ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">

        <!-- ── LEFT COLUMN ── -->
        <div class="col-12 col-lg-4">

            <!-- Members by role -->
            <div class="odb-card mb-4">
                <div class="odb-card-header">
                    <div class="odb-card-title-wrap">
                        <div class="odb-card-icon" style="background:#eef2ff;color:#6366f1"><i class="bi bi-pie-chart-fill"></i></div>
                        <div>
                            <div class="odb-card-title">Members by Role</div>
                            <div class="odb-card-sub">Distribution across roles</div>
                        </div>
                    </div>
                    <a href="../data_files/?view=org_members" class="odb-card-link">
                        <i class="bi bi-people"></i>Manage
                    </a>
                </div>
                <div class="odb-card-body" id="roleBreakdown">
                    <?php for ($i = 0; $i < 4; $i++): ?>
                    <div class="odb-role-row">
                        <span class="odb-skel" style="width:10px;height:10px;border-radius:50%;flex-shrink:0"></span>
                        <span class="odb-skel" style="width:70px;height:12px"></span>
                        <div class="odb-role-bar-wrap"><div class="odb-skel" style="width:60%;height:7px;border-radius:100px"></div></div>
                        <span class="odb-skel" style="width:20px;height:12px"></span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Quick actions -->
            <div class="odb-card">
                <div class="odb-card-header">
                    <div class="odb-card-title-wrap">
                        <div class="odb-card-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-lightning-fill"></i></div>
                        <div><div class="odb-card-title">Quick Actions</div></div>
                    </div>
                </div>
                <div class="odb-card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="../data_files/?view=org_members" class="odb-qa-btn">
                                <div class="qa-icon" style="background:#eef2ff;color:#6366f1"><i class="bi bi-person-plus-fill"></i></div>
                                <span class="qa-label">Add Member</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="../data_files/?view=org_members" class="odb-qa-btn">
                                <div class="qa-icon" style="background:#d1fae5;color:#059669"><i class="bi bi-file-earmark-arrow-up-fill"></i></div>
                                <span class="qa-label">Import CSV</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="../data_files/?view=org_departments" class="odb-qa-btn">
                                <div class="qa-icon" style="background:#e0f2fe;color:#0284c7"><i class="bi bi-building-fill"></i></div>
                                <span class="qa-label">Departments</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="../data_files/?view=org_reports" class="odb-qa-btn">
                                <div class="qa-icon" style="background:#fce7f3;color:#be185d"><i class="bi bi-bar-chart-fill"></i></div>
                                <span class="qa-label">Reports</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /left -->

        <!-- ── RIGHT COLUMN ── -->
        <div class="col-12 col-lg-8">

            <!-- Top Learners -->
            <div class="odb-card mb-4">
                <div class="odb-card-header">
                    <div class="odb-card-title-wrap">
                        <div class="odb-card-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-trophy-fill"></i></div>
                        <div>
                            <div class="odb-card-title">Top Learners</div>
                            <div class="odb-card-sub">Ranked by completion rate</div>
                        </div>
                    </div>
                    <a href="../data_files/?view=org_reports" class="odb-card-link">
                        <i class="bi bi-arrow-right"></i>Full Reports
                    </a>
                </div>
                <div class="odb-card-body" id="topLearners">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                    <div class="odb-learner-row">
                        <span class="odb-learner-rank"><span class="odb-skel d-inline-block" style="width:16px;height:14px"></span></span>
                        <span class="odb-skel" style="width:36px;height:36px;border-radius:50%;flex-shrink:0"></span>
                        <div style="flex:1">
                            <div class="odb-skel mb-1" style="height:13px;width:140px"></div>
                            <div class="odb-skel" style="height:10px;width:100px"></div>
                        </div>
                        <div class="odb-learner-bar-wrap" style="max-width:100px"><div class="odb-skel" style="height:6px;width:70%;border-radius:100px"></div></div>
                        <span class="odb-skel" style="width:32px;height:12px"></span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="odb-card">
                <div class="odb-card-header">
                    <div class="odb-card-title-wrap">
                        <div class="odb-card-icon" style="background:#f0fdf4;color:#16a34a"><i class="bi bi-activity"></i></div>
                        <div>
                            <div class="odb-card-title">Recent Activity</div>
                            <div class="odb-card-sub">Last 10 actions in your org</div>
                        </div>
                    </div>
                </div>
                <div class="odb-card-body" id="recentActivity">
                    <?php for ($i = 0; $i < 4; $i++): ?>
                    <div class="odb-act-item">
                        <span class="odb-skel" style="width:34px;height:34px;border-radius:10px;flex-shrink:0"></span>
                        <div style="flex:1">
                            <div class="odb-skel mb-1" style="height:13px;width:130px"></div>
                            <div class="odb-skel" style="height:10px;width:90px"></div>
                        </div>
                        <span class="odb-skel" style="width:55px;height:10px"></span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

        </div><!-- /right -->
    </div><!-- /row -->
</div><!-- /canvas -->

<script>
(async function odbInit() {
    const r = await fetch('ajax/ajax_org_admin.php?action=dashboard').then(x => x.json()).catch(() => ({}));
    if (r.status !== 'success') {
        ['mc0','mc1','mc2','mc3'].forEach(id => document.getElementById(id).textContent = '—');
        return;
    }
    const d = r.data;

    /* ── Hero pills ── */
    document.getElementById('hStatMembers').textContent   = d.total_members  ?? '—';
    document.getElementById('hStatActive').textContent    = d.active_members  ?? '—';
    document.getElementById('hStatCourses').textContent   = d.total_courses   ?? '—';
    document.getElementById('hStatCompletion').textContent = (d.avg_completion ?? 0) + '%';

    /* ── Metric cards ── */
    document.getElementById('mc0').textContent = d.total_members  ?? '—';
    document.getElementById('mc1').textContent = d.active_members  ?? '—';
    document.getElementById('mc2').textContent = d.total_courses   ?? '—';
    document.getElementById('mc3').textContent = (d.avg_completion ?? 0) + '%';

    /* ── Role breakdown ── */
    const roleColors = {
        admin:'#ef4444', coordinator:'#f59e0b', instructor:'#06b6d4',
        student:'#6366f1', staff:'#64748b'
    };
    const roles = d.role_breakdown ?? [];
    const total = d.total_members || 1;
    document.getElementById('roleBreakdown').innerHTML = roles.length
        ? roles.map(rb => {
            const pct = Math.min(100, Math.round((rb.cnt / total) * 100));
            const col = roleColors[rb.org_role] || '#94a3b8';
            return `
            <div class="odb-role-row">
                <div class="odb-role-dot" style="background:${col}"></div>
                <div class="odb-role-name">${esc(rb.org_role)}</div>
                <div class="odb-role-bar-wrap">
                    <div class="odb-role-bar" style="width:${pct}%;background:${col}"></div>
                </div>
                <div class="odb-role-count">${rb.cnt}</div>
            </div>`;
        }).join('')
        : `<div class="odb-empty"><i class="bi bi-people"></i><p>No members yet</p></div>`;

    /* ── Top learners ── */
    const learners = d.top_learners ?? [];
    document.getElementById('topLearners').innerHTML = learners.length
        ? learners.map((l, i) => {
            const name = esc((l.first_name || '') + ' ' + (l.last_name || '')).trim() || 'Unknown';
            const initials = name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
            const pct = l.avg_completion || 0;
            return `
            <div class="odb-learner-row">
                <div class="odb-learner-rank ${i < 3 ? 'top' : ''}">${i < 3 ? ['🥇','🥈','🥉'][i] : i + 1}</div>
                <div class="odb-learner-avatar">${initials}</div>
                <div style="flex:1;min-width:0">
                    <div class="odb-learner-name">${name}</div>
                    <div class="odb-learner-email">${esc(l.email || '')}</div>
                </div>
                <div class="odb-learner-bar-wrap" style="max-width:90px">
                    <div class="odb-learner-bar" style="width:${pct}%"></div>
                </div>
                <div class="odb-learner-pct">${pct}%</div>
            </div>`;
        }).join('')
        : `<div class="odb-empty"><i class="bi bi-trophy"></i><p>No learner data yet</p></div>`;

    /* ── Recent activity ── */
    const actIcons = {
        member_added:   { icon:'bi-person-plus-fill',    bg:'#eef2ff', color:'#6366f1' },
        member_created: { icon:'bi-person-badge-fill',   bg:'#d1fae5', color:'#059669' },
        member_removed: { icon:'bi-person-dash-fill',    bg:'#fee2e2', color:'#dc2626' },
        member_updated: { icon:'bi-pencil-fill',         bg:'#fef3c7', color:'#d97706' },
        password_reset: { icon:'bi-key-fill',            bg:'#ede9fe', color:'#7c3aed' },
        bulk_import:    { icon:'bi-file-earmark-arrow-up-fill', bg:'#e0f2fe', color:'#0284c7' },
        dept_created:   { icon:'bi-building-fill',       bg:'#f0fdf4', color:'#16a34a' },
        dept_updated:   { icon:'bi-building-gear',       bg:'#fef3c7', color:'#d97706' },
        dept_deleted:   { icon:'bi-building-x',          bg:'#fee2e2', color:'#dc2626' },
    };
    const actLabels = {
        member_added:   'Added a member',   member_created: 'Created a new account',
        member_removed: 'Removed a member', member_updated: 'Updated member details',
        password_reset: 'Reset password',   bulk_import:    'Bulk imported members',
        dept_created:   'Created department', dept_updated: 'Updated department',
        dept_deleted:   'Deleted department',
    };
    const acts = d.recent_activity ?? [];
    document.getElementById('recentActivity').innerHTML = acts.length
        ? acts.map(a => {
            const ic = actIcons[a.action] || { icon:'bi-clock-fill', bg:'#f1f5f9', color:'#94a3b8' };
            const label = actLabels[a.action] || esc(a.action).replace(/_/g, ' ');
            return `
            <div class="odb-act-item">
                <div class="odb-act-icon" style="background:${ic.bg};color:${ic.color}">
                    <i class="bi ${ic.icon}"></i>
                </div>
                <div style="flex:1;min-width:0">
                    <div class="odb-act-actor">${esc(a.actor_name || a.actor_usr_code)}</div>
                    <div class="odb-act-desc">${label}</div>
                </div>
                <div class="odb-act-time">${fmtDate(a.created_at)}</div>
            </div>`;
        }).join('')
        : `<div class="odb-empty"><i class="bi bi-activity"></i><p>No activity recorded yet</p></div>`;

})();

const esc     = s => (s + '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
const fmtDate = s => s ? new Date(s).toLocaleDateString('en-GB', { day:'numeric', month:'short' }) : '—';
</script>
