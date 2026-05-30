<?php
$user_role = isset($user_role) ? (int)$user_role : 0;
if ($user_role != 5) { include('403.php'); return; }

$roles = $db->query("SELECT id, role_title FROM tbl_user_roles ORDER BY id")->fetch_all(MYSQLI_ASSOC);

$stats = $db->query("
    SELECT
        COUNT(*)                                                    AS total,
        SUM(user_role = '1')                                        AS students,
        SUM(user_role IN ('3','4'))                                 AS instructors,
        SUM(user_status = 'Active')                                 AS active,
        SUM(user_status = 'Inactive')                               AS inactive,
        SUM(DATE(created_at) >= DATE_FORMAT(NOW(),'%Y-%m-01'))      AS new_month
    FROM tbl_all_users
")->fetch_assoc();
?>
<style>
/* ═══════════════════════════════════════════════════════════
   Admin Users — Super Hero Design
═══════════════════════════════════════════════════════════ */
@keyframes au-fade{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}
@keyframes au-pop{0%{transform:scale(.8);opacity:0}60%{transform:scale(1.07)}100%{transform:scale(1);opacity:1}}
@keyframes au-orb1{from{transform:translate(0,0) scale(1)}to{transform:translate(-20px,15px) scale(1.18)}}
@keyframes au-orb2{from{transform:translate(0,0) scale(1)}to{transform:translate(16px,-20px) scale(1.14)}}
@keyframes au-orb3{from{transform:translate(0,0) scale(1)}to{transform:translate(-12px,-10px) scale(.88)}}
@keyframes au-kpi{from{opacity:0;transform:translateY(24px) scale(.93)}to{opacity:1;transform:none}}
@keyframes au-row{from{opacity:0;transform:translateX(-8px)}to{opacity:1;transform:none}}
@keyframes au-drop{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:none}}
@keyframes au-bar{from{width:0}to{width:var(--bw,60%)}}
@keyframes au-skel{0%{background-position:200% 0}100%{background-position:-200% 0}}
@keyframes au-ping{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.8);opacity:0}}

/* ── Hero ── */
.au-hero{position:relative;border-radius:22px;overflow:hidden;background:linear-gradient(135deg,#040410 0%,#0b0929 35%,#160d3a 65%,#0a0e1e 100%);padding:2.1rem 2rem 1.9rem;margin:0 1rem;color:#fff;animation:au-fade .4s ease both}
.au-orb{position:absolute;border-radius:50%;filter:blur(58px);pointer-events:none}
.au-orb-1{width:260px;height:260px;background:rgba(99,102,241,.28);top:-80px;right:-20px;animation:au-orb1 8s ease-in-out infinite alternate}
.au-orb-2{width:180px;height:180px;background:rgba(139,92,246,.22);bottom:-50px;right:200px;animation:au-orb2 10s ease-in-out infinite alternate}
.au-orb-3{width:130px;height:130px;background:rgba(59,130,246,.18);top:20px;left:40%;animation:au-orb3 7s ease-in-out infinite alternate}
.au-hero-inner{position:relative;z-index:2;display:flex;align-items:center;gap:1.4rem;flex-wrap:wrap}
.au-hero-icon{width:68px;height:68px;border-radius:20px;background:rgba(255,255,255,.09);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.14);display:flex;align-items:center;justify-content:center;font-size:1.85rem;flex-shrink:0;box-shadow:0 8px 32px rgba(99,102,241,.4);animation:au-pop .7s cubic-bezier(.34,1.56,.64,1) both}
.au-hero-title{font-size:1.45rem;font-weight:900;letter-spacing:-.025em;line-height:1.1}
.au-hero-title span{background:linear-gradient(90deg,#a5b4fc 0%,#f9a8d4 50%,#6ee7b7 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.au-hero-sub{font-size:.83rem;opacity:.5;margin-top:.3rem}
.au-hero-pills{display:flex;flex-wrap:wrap;gap:.45rem;margin-top:.85rem}
.au-pill{background:rgba(255,255,255,.09);border:1px solid rgba(255,255,255,.14);color:#fff;font-size:.7rem;font-weight:700;padding:.22rem .75rem;border-radius:20px;display:inline-flex;align-items:center;gap:.3rem}
.au-pill-g{background:rgba(16,185,129,.2);border-color:rgba(16,185,129,.3);color:#6ee7b7}
.au-pill-b{background:rgba(59,130,246,.2);border-color:rgba(59,130,246,.3);color:#93c5fd}
.au-hero-actions{margin-left:auto;display:flex;gap:.65rem;flex-shrink:0}
.au-hbtn{padding:.52rem 1.2rem;border-radius:12px;font-size:.81rem;font-weight:700;cursor:pointer;border:none;display:flex;align-items:center;gap:.45rem;transition:all .2s;white-space:nowrap}
.au-hbtn-primary{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;box-shadow:0 4px 16px rgba(99,102,241,.4)}
.au-hbtn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(99,102,241,.55)}
.au-hbtn-ghost{background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.18)}
.au-hbtn-ghost:hover{background:rgba(255,255,255,.2)}

/* ── KPI Grid ── */
.au-kpi-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:.9rem;margin:1.25rem 1rem 0}
@media(max-width:1200px){.au-kpi-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:575px){.au-kpi-grid{grid-template-columns:repeat(2,1fr)}}
.au-kpi{border-radius:18px;padding:1.2rem 1.25rem;position:relative;overflow:hidden;box-shadow:0 2px 18px rgba(0,0,0,.07);transition:transform .25s,box-shadow .25s;animation:au-kpi .5s cubic-bezier(.34,1.56,.64,1) both}
.au-kpi:hover{transform:translateY(-5px)}
.au-kpi:nth-child(1){animation-delay:.05s}.au-kpi:nth-child(2){animation-delay:.1s}
.au-kpi:nth-child(3){animation-delay:.15s}.au-kpi:nth-child(4){animation-delay:.2s}.au-kpi:nth-child(5){animation-delay:.25s}
.au-kpi-ghost{position:absolute;right:-14px;bottom:-14px;font-size:5rem;opacity:.08;line-height:1;pointer-events:none}
.au-kpi-icon{width:44px;height:44px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;margin-bottom:.85rem;position:relative;z-index:1}
.au-kpi-lbl{font-size:.69rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;opacity:.6;position:relative;z-index:1}
.au-kpi-val{font-size:2.1rem;font-weight:900;line-height:1.1;position:relative;z-index:1;font-variant-numeric:tabular-nums}
.au-kpi-sub{font-size:.69rem;margin-top:.2rem;opacity:.5;position:relative;z-index:1}
.au-kpi-bar{height:3px;border-radius:99px;margin-top:.9rem;overflow:hidden;position:relative;z-index:1}
.au-kpi-fill{height:100%;border-radius:99px;width:0;transition:width 1.1s cubic-bezier(.4,0,.2,1)}

.au-kpi-total{background:linear-gradient(145deg,#1e1b4b,#2e2a6e);color:#fff}
.au-kpi-total .au-kpi-icon{background:rgba(255,255,255,.12);color:#c4b5fd}
.au-kpi-total .au-kpi-bar{background:rgba(255,255,255,.12)}.au-kpi-total .au-kpi-fill{background:linear-gradient(90deg,#a5b4fc,#c4b5fd)}
.au-kpi-total:hover{box-shadow:0 14px 36px rgba(30,27,75,.35)}

.au-kpi-active{background:linear-gradient(145deg,#052e16,#065f46);color:#fff}
.au-kpi-active .au-kpi-icon{background:rgba(255,255,255,.1);color:#6ee7b7}
.au-kpi-active .au-kpi-bar{background:rgba(255,255,255,.12)}.au-kpi-active .au-kpi-fill{background:linear-gradient(90deg,#34d399,#6ee7b7)}
.au-kpi-active:hover{box-shadow:0 14px 36px rgba(5,46,22,.35)}

.au-kpi-students{background:linear-gradient(145deg,#1e3a5f,#1e40af);color:#fff}
.au-kpi-students .au-kpi-icon{background:rgba(255,255,255,.1);color:#bfdbfe}
.au-kpi-students .au-kpi-bar{background:rgba(255,255,255,.12)}.au-kpi-students .au-kpi-fill{background:linear-gradient(90deg,#60a5fa,#bfdbfe)}
.au-kpi-students:hover{box-shadow:0 14px 36px rgba(30,58,95,.35)}

.au-kpi-instruct{background:linear-gradient(145deg,#431407,#7c2d12);color:#fff}
.au-kpi-instruct .au-kpi-icon{background:rgba(255,255,255,.1);color:#fed7aa}
.au-kpi-instruct .au-kpi-bar{background:rgba(255,255,255,.12)}.au-kpi-instruct .au-kpi-fill{background:linear-gradient(90deg,#fb923c,#fed7aa)}
.au-kpi-instruct:hover{box-shadow:0 14px 36px rgba(67,20,7,.35)}

.au-kpi-new{background:linear-gradient(145deg,#4a044e,#7e22ce);color:#fff}
.au-kpi-new .au-kpi-icon{background:rgba(255,255,255,.1);color:#e9d5ff}
.au-kpi-new .au-kpi-bar{background:rgba(255,255,255,.12)}.au-kpi-new .au-kpi-fill{background:linear-gradient(90deg,#c084fc,#e9d5ff)}
.au-kpi-new:hover{box-shadow:0 14px 36px rgba(74,4,78,.35)}

/* ── Toolbar ── */
.au-toolbar{background:#fff;border-radius:16px;padding:.85rem 1.2rem;margin:1.1rem 1rem .85rem;box-shadow:0 2px 14px rgba(0,0,0,.05);display:flex;flex-wrap:wrap;gap:.75rem;align-items:center;animation:au-fade .4s .15s ease both}
.au-search-wrap{flex:1;min-width:180px;max-width:300px;position:relative}
.au-search-wrap input{width:100%;border:1.5px solid #e0e7ff;border-radius:12px;padding:.48rem .9rem .48rem 2.2rem;font-size:.84rem;background:#f8f7ff;transition:all .2s}
.au-search-wrap input:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1);background:#fff}
.au-search-ico{position:absolute;left:.72rem;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:.88rem;pointer-events:none}
.au-select{border:1.5px solid #e0e7ff;border-radius:11px;padding:.42rem .8rem;font-size:.82rem;background:#f8f7ff;color:#334155;transition:all .2s;min-width:130px}
.au-select:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1);background:#fff}
.au-reset-btn{padding:.42rem .9rem;border:1.5px solid #e0e7ff;border-radius:11px;font-size:.8rem;font-weight:600;background:#fff;color:#64748b;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:.35rem}
.au-reset-btn:hover{border-color:#6366f1;color:#6366f1;background:#f8f7ff}
.au-add-btn{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:12px;padding:.5rem 1.1rem;font-size:.82rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.4rem;transition:all .2s;box-shadow:0 4px 14px rgba(99,102,241,.35);margin-left:auto;white-space:nowrap}
.au-add-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(99,102,241,.5)}

/* ── Table card ── */
.au-card{background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 2px 18px rgba(0,0,0,.06);margin:0 1rem;animation:au-fade .4s .2s ease both}
.au-table-meta{padding:.75rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;background:linear-gradient(135deg,#f8f7ff,#f1f5f9)}
.au-table{width:100%;border-collapse:collapse}
.au-table thead th{padding:.7rem 1rem;background:linear-gradient(135deg,#f8f7ff,#f1f5f9);font-size:.69rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#64748b;border-bottom:2px solid #e0e7ff;white-space:nowrap}
.au-table thead th:first-child{padding-left:1.4rem}
.au-table thead th:last-child{padding-right:1.4rem;text-align:right}
.au-table tbody tr{border-bottom:1px solid #f1f5f9;transition:background .14s;animation:au-row .32s ease both}
.au-table tbody tr:last-child{border-bottom:none}
.au-table tbody tr:hover{background:#f8f7ff}
.au-table td{padding:.82rem 1rem;vertical-align:middle}
.au-table td:first-child{padding-left:1.4rem}
.au-table td:last-child{padding-right:1.4rem;text-align:right}
/* Staggered row animation */
.au-table tbody tr:nth-child(1){animation-delay:.03s}.au-table tbody tr:nth-child(2){animation-delay:.06s}
.au-table tbody tr:nth-child(3){animation-delay:.09s}.au-table tbody tr:nth-child(4){animation-delay:.12s}
.au-table tbody tr:nth-child(5){animation-delay:.15s}.au-table tbody tr:nth-child(6){animation-delay:.18s}
.au-table tbody tr:nth-child(7){animation-delay:.21s}.au-table tbody tr:nth-child(8){animation-delay:.24s}

/* Avatar */
.u-avatar{width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800;color:#fff;flex-shrink:0;letter-spacing:.02em;box-shadow:0 2px 8px rgba(0,0,0,.12);transition:transform .2s}
.au-table tr:hover .u-avatar{transform:scale(1.08)}

/* Role pill */
.role-pill{display:inline-flex;align-items:center;gap:.35rem;padding:.28rem .75rem;border-radius:100px;font-size:.71rem;font-weight:700;border:1.5px solid transparent;white-space:nowrap}

/* Status */
.status-dot{width:7px;height:7px;border-radius:50%;display:inline-block;flex-shrink:0}
.status-dot.active-dot{box-shadow:0 0 0 0 rgba(34,197,94,.4);animation:au-ping 2.5s infinite}

/* Action buttons */
.um-action-wrap{position:relative}
.um-action-btn{width:32px;height:32px;border-radius:9px;border:1.5px solid #e0e7ff;background:#fff;display:flex;align-items:center;justify-content:center;color:#64748b;cursor:pointer;transition:all .18s;font-size:.95rem}
.um-action-btn:hover{border-color:#6366f1;color:#6366f1;background:#f8f7ff;transform:scale(1.1)}
.um-dropdown{position:absolute;right:0;top:calc(100% + 6px);background:#fff;border:1.5px solid #e0e7ff;border-radius:14px;box-shadow:0 12px 40px rgba(0,0,0,.14);min-width:188px;z-index:999;overflow:hidden;animation:au-drop .18s cubic-bezier(.16,1,.3,1)}
.um-dropdown-item{display:flex;align-items:center;gap:.65rem;padding:.65rem 1rem;font-size:.82rem;font-weight:600;color:#0f172a;cursor:pointer;transition:background .12s;border:none;background:none;width:100%;text-align:left}
.um-dropdown-item:hover{background:#f8f7ff}
.um-dropdown-item.danger{color:#dc2626}
.um-dropdown-item.danger:hover{background:#fef2f2}
.um-dropdown-item i{font-size:.9rem;width:18px;text-align:center}
.um-dropdown-sep{height:1px;background:#f1f5f9;margin:.25rem 0}

/* Pagination */
.au-page-btn{width:33px;height:33px;border-radius:9px;border:1.5px solid #e0e7ff;background:#fff;color:#64748b;font-size:.8rem;font-weight:700;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .18s}
.au-page-btn:hover{border-color:#6366f1;color:#6366f1;background:#f8f7ff}
.au-page-btn.active{background:linear-gradient(135deg,#6366f1,#8b5cf6);border-color:transparent;color:#fff;box-shadow:0 3px 10px rgba(99,102,241,.35)}
.au-page-btn:disabled{opacity:.38;cursor:not-allowed}

/* Skeleton */
.au-skel{background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:au-skel 1.4s infinite;border-radius:8px}

/* Modal */
#userModal .modal-content{border:none;border-radius:20px;box-shadow:0 24px 80px rgba(0,0,0,.18);overflow:hidden}
#userModal .modal-header{background:linear-gradient(135deg,#0f0c29,#312e81);padding:1.25rem 1.5rem;border:none}
#userModal .modal-title{color:#fff;font-weight:800;font-size:.95rem}
#userModal .btn-close{filter:invert(1);opacity:.65}
#userModal .modal-body{padding:1.5rem;background:#fafbff}
#userModal .modal-footer{background:#f8f7ff;border:none;padding:1rem 1.5rem}
.um-field label{font-size:.73rem;font-weight:800;text-transform:uppercase;letter-spacing:.03em;color:#475569;margin-bottom:.3rem;display:block}
.um-field .form-control,.um-field .form-select{border:1.5px solid #e0e7ff;border-radius:10px;font-size:.875rem;background:#fff;height:44px;transition:all .2s}
.um-field .form-control:focus,.um-field .form-select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
.um-field .field-icon-wrap{position:relative}
.um-field .field-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:.88rem;pointer-events:none}
.um-field .form-control.has-icon{padding-left:38px}
.um-save-btn{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:11px;padding:.55rem 1.4rem;font-weight:700;font-size:.86rem;cursor:pointer;display:flex;align-items:center;gap:.4rem;transition:all .2s;box-shadow:0 4px 14px rgba(99,102,241,.4)}
.um-save-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(99,102,241,.5)}
.um-save-btn:disabled{opacity:.55;transform:none}
</style>

<div class="container-fluid px-0">

<!-- ══ HERO ══ -->
<div class="au-hero mt-3">
    <div class="au-orb au-orb-1"></div>
    <div class="au-orb au-orb-2"></div>
    <div class="au-orb au-orb-3"></div>
    <div class="au-hero-inner">
        <div class="au-hero-icon"><i class="bi bi-people-fill"></i></div>
        <div class="flex-grow-1">
            <div class="au-hero-title">User <span>Management</span></div>
            <div class="au-hero-sub">Create, edit, and control access for every platform user</div>
            <div class="au-hero-pills">
                <span class="au-pill"><i class="bi bi-people-fill"></i><?= number_format($stats['total']) ?> total users</span>
                <span class="au-pill au-pill-g"><i class="bi bi-check-circle-fill"></i><?= number_format($stats['active']) ?> active</span>
                <span class="au-pill au-pill-b"><i class="bi bi-stars"></i><?= number_format($stats['new_month']) ?> joined this month</span>
            </div>
        </div>
        <div class="au-hero-actions d-none d-lg-flex">
            <button class="au-hbtn au-hbtn-ghost" onclick="window.loadUsers()"><i class="bi bi-arrow-clockwise"></i>Refresh</button>
            <button class="au-hbtn au-hbtn-primary" onclick="window.openCreateModal()"><i class="bi bi-person-plus-fill"></i>Add User</button>
        </div>
    </div>
</div>

<!-- ══ KPI CARDS ══ -->
<div class="au-kpi-grid">
    <div class="au-kpi au-kpi-total">
        <div class="au-kpi-ghost"><i class="bi bi-people"></i></div>
        <div class="au-kpi-icon"><i class="bi bi-people-fill"></i></div>
        <div class="au-kpi-lbl">Total Users</div>
        <div class="au-kpi-val"><?= number_format($stats['total']) ?></div>
        <div class="au-kpi-sub">All platform members</div>
        <div class="au-kpi-bar"><div class="au-kpi-fill" style="--bw:100%;width:100%"></div></div>
    </div>
    <div class="au-kpi au-kpi-active">
        <div class="au-kpi-ghost"><i class="bi bi-check-circle"></i></div>
        <div class="au-kpi-icon"><i class="bi bi-check-circle-fill"></i></div>
        <div class="au-kpi-lbl">Active</div>
        <div class="au-kpi-val"><?= number_format($stats['active']) ?></div>
        <div class="au-kpi-sub">Accounts in good standing</div>
        <div class="au-kpi-bar"><div class="au-kpi-fill" style="--bw:<?= $stats['total'] ? round($stats['active']/$stats['total']*100) : 0 ?>%;"></div></div>
    </div>
    <div class="au-kpi au-kpi-students">
        <div class="au-kpi-ghost"><i class="bi bi-mortarboard"></i></div>
        <div class="au-kpi-icon"><i class="bi bi-mortarboard-fill"></i></div>
        <div class="au-kpi-lbl">Students</div>
        <div class="au-kpi-val"><?= number_format($stats['students']) ?></div>
        <div class="au-kpi-sub">Enrolled learners</div>
        <div class="au-kpi-bar"><div class="au-kpi-fill" style="--bw:<?= $stats['total'] ? round($stats['students']/$stats['total']*100) : 0 ?>%;"></div></div>
    </div>
    <div class="au-kpi au-kpi-instruct">
        <div class="au-kpi-ghost"><i class="bi bi-person-video3"></i></div>
        <div class="au-kpi-icon"><i class="bi bi-person-video3"></i></div>
        <div class="au-kpi-lbl">Instructors</div>
        <div class="au-kpi-val"><?= number_format($stats['instructors']) ?></div>
        <div class="au-kpi-sub">Course creators &amp; admins</div>
        <div class="au-kpi-bar"><div class="au-kpi-fill" style="--bw:<?= $stats['total'] ? round($stats['instructors']/$stats['total']*100) : 0 ?>%;"></div></div>
    </div>
    <div class="au-kpi au-kpi-new">
        <div class="au-kpi-ghost"><i class="bi bi-person-plus"></i></div>
        <div class="au-kpi-icon"><i class="bi bi-person-plus-fill"></i></div>
        <div class="au-kpi-lbl">New This Month</div>
        <div class="au-kpi-val"><?= number_format($stats['new_month']) ?></div>
        <div class="au-kpi-sub">Registrations in <?= date('F') ?></div>
        <div class="au-kpi-bar"><div class="au-kpi-fill" style="--bw:<?= $stats['total'] ? min(100,round($stats['new_month']/$stats['total']*100*5)) : 0 ?>%;"></div></div>
    </div>
</div>

<!-- ══ TOOLBAR ══ -->
<div class="au-toolbar">
    <div class="au-search-wrap">
        <i class="bi bi-search au-search-ico"></i>
        <input type="text" id="fSearch" placeholder="Search name, email, phone…" autocomplete="off">
    </div>
    <select id="fRole" class="au-select">
        <option value="">All Roles</option>
        <?php foreach ($roles as $r): ?>
        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['role_title']) ?></option>
        <?php endforeach; ?>
    </select>
    <select id="fStatus" class="au-select">
        <option value="">All Status</option>
        <option value="Active">Active</option>
        <option value="Inactive">Inactive</option>
    </select>
    <button class="au-reset-btn" id="auResetBtn"><i class="bi bi-x-circle"></i>Reset</button>
    <button class="au-add-btn d-lg-none" onclick="window.openCreateModal()"><i class="bi bi-person-plus-fill"></i>Add</button>
</div>

<!-- ══ TABLE CARD ══ -->
<div class="au-card mb-4">
    <div class="au-table-meta">
        <span style="font-size:.78rem;color:#64748b;font-weight:600" id="uCount">Loading…</span>
        <div class="d-flex align-items-center gap-2">
            <span style="font-size:.75rem;color:#94a3b8">Per page:</span>
            <select id="fPerPage" class="au-select" style="min-width:80px;padding:.3rem .6rem;font-size:.78rem">
                <option value="15">15</option>
                <option value="25" selected>25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>
    <div class="table-responsive">
        <table class="au-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Contact</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="uTbody">
                <tr><td colspan="6" style="padding:3rem 0">
                    <div style="display:flex;flex-direction:column;gap:.6rem;padding:0 1.4rem">
                        <?php for($i=0;$i<6;$i++): ?>
                        <div style="display:flex;gap:1rem;align-items:center">
                            <div class="au-skel" style="width:40px;height:40px;border-radius:12px;flex-shrink:0"></div>
                            <div style="flex:1"><div class="au-skel" style="width:45%;height:11px;margin-bottom:.35rem"></div><div class="au-skel" style="width:65%;height:9px"></div></div>
                            <div class="au-skel" style="width:110px;height:10px"></div>
                            <div class="au-skel" style="width:80px;height:22px;border-radius:20px"></div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </td></tr>
            </tbody>
        </table>
    </div>
    <div id="uPagination" style="padding:.85rem 1.25rem;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem"></div>
</div>

</div><!-- /.container-fluid -->

<!-- Dropdown overlay -->
<div id="dropOverlay" style="position:fixed;inset:0;z-index:998;display:none" onclick="window.closeAllDropdowns()"></div>

<!-- ══ USER MODAL ══ -->
<div class="modal fade" id="userModal" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
    <div class="modal-header">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-person-circle text-white fs-5"></i>
            <h6 class="modal-title mb-0" id="userModalTitle">Add New User</h6>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <input type="hidden" id="editUserId">
        <div class="row g-3">
            <div class="col-6">
                <div class="um-field">
                    <label>First Name <span class="text-danger">*</span></label>
                    <div class="field-icon-wrap"><i class="bi bi-person field-icon"></i>
                    <input type="text" id="uFirstName" class="form-control has-icon" placeholder="First name"></div>
                </div>
            </div>
            <div class="col-6">
                <div class="um-field">
                    <label>Last Name <span class="text-danger">*</span></label>
                    <div class="field-icon-wrap"><i class="bi bi-person field-icon"></i>
                    <input type="text" id="uLastName" class="form-control has-icon" placeholder="Last name"></div>
                </div>
            </div>
            <div class="col-12">
                <div class="um-field">
                    <label>Email Address <span class="text-danger">*</span></label>
                    <div class="field-icon-wrap"><i class="bi bi-envelope field-icon"></i>
                    <input type="email" id="uEmail" class="form-control has-icon" placeholder="email@example.com"></div>
                </div>
            </div>
            <div class="col-6">
                <div class="um-field">
                    <label>Phone</label>
                    <div class="field-icon-wrap"><i class="bi bi-phone field-icon"></i>
                    <input type="text" id="uPhone" class="form-control has-icon" placeholder="+255…"></div>
                </div>
            </div>
            <div class="col-6">
                <div class="um-field">
                    <label>Role <span class="text-danger">*</span></label>
                    <select id="uRole" class="form-select">
                        <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['role_title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-12" id="passwordGroup">
                <div class="um-field">
                    <label>Password <span class="text-danger">*</span></label>
                    <div class="field-icon-wrap" style="position:relative">
                        <i class="bi bi-lock field-icon"></i>
                        <input type="password" id="uPassword" class="form-control has-icon" placeholder="Min. 8 characters" style="padding-right:44px">
                        <button type="button" onclick="window.togglePwd()" tabindex="-1" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:#a5b4fc;cursor:pointer;padding:4px">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-12" id="statusGroup" style="display:none">
                <div class="um-field">
                    <label>Account Status</label>
                    <select id="uStatus" class="form-select">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer justify-content-between">
        <button class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
        <button class="um-save-btn" id="saveUserBtn" onclick="window.saveUser()">
            <i class="bi bi-check-lg"></i><span id="saveBtnText">Save User</span>
        </button>
    </div>
</div>
</div>
</div>

<script>
/* ─────────────────────────────────────────────────────────────
   Admin Users JS — all window.* for SPA scope compatibility
───────────────────────────────────────────────────────────── */
const ROLES = <?= json_encode(array_column($roles, 'role_title', 'id')) ?>;

const ROLE_META = {
    '1':{ label:'Student',     color:'#3b82f6', bg:'#eff6ff',  icon:'bi-mortarboard-fill'},
    '2':{ label:'Parent',      color:'#8b5cf6', bg:'#f5f3ff',  icon:'bi-people-fill'     },
    '3':{ label:'Instructor',  color:'#f97316', bg:'#fff7ed',  icon:'bi-person-video3'   },
    '4':{ label:'Org Admin',   color:'#10b981', bg:'#f0fdf4',  icon:'bi-building'        },
    '5':{ label:'Super Admin', color:'#dc2626', bg:'#fef2f2',  icon:'bi-shield-fill'     },
};
const AVATAR_COLORS = ['#6366f1','#8b5cf6','#059669','#d97706','#dc2626','#0891b2','#7c3aed','#be185d','#0d9488','#1d4ed8'];

var _auModal = null, _auPage = 1, _auPer = 25, _auTotal = 0, _auTimer;

/* ── Count-up for KPI bars ── */
(function() {
    var fills = document.querySelectorAll('.au-kpi-fill');
    setTimeout(function() {
        fills.forEach(function(el) {
            var w = el.style.getPropertyValue('--bw') || el.style.width || '0%';
            el.style.width = '0%';
            setTimeout(function() { el.style.width = w; }, 50);
        });
    }, 300);
})();

/* ── Helpers ── */
function esc(s) {
    return String(s ?? '').replace(/[&<>"']/g, function(m){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]; });
}
function formatDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'});
}
function avatarColor(name) {
    var hash = 0;
    for (var i = 0; i < (name||'U').length; i++) hash = (name||'U').charCodeAt(i) + ((hash << 5) - hash);
    return AVATAR_COLORS[Math.abs(hash) % AVATAR_COLORS.length];
}
function initials(f, l) { return ((f||'?')[0] + (l||'')[0]).toUpperCase(); }

/* ── Filters ── */
window.debounceSearch = function() { clearTimeout(_auTimer); _auTimer = setTimeout(window.loadUsers, 350); };
window.resetFilters   = function() {
    document.getElementById('fSearch').value = '';
    document.getElementById('fRole').value   = '';
    document.getElementById('fStatus').value = '';
    window.loadUsers();
};

/* ── Load ── */
window.loadUsers = function() {
    _auPage = 1;
    _auPer  = parseInt(document.getElementById('fPerPage').value) || 25;
    fetchUsers();
};
function fetchUsers() {
    var params = new URLSearchParams({
        action:'list',
        role:    document.getElementById('fRole').value,
        status:  document.getElementById('fStatus').value,
        q:       document.getElementById('fSearch').value,
        page:    _auPage,
        per_page:_auPer
    });
    var spinner = '<tr><td colspan="6" style="text-align:center;padding:3rem 0">'
        + '<div class="spinner-border" style="width:1.4rem;height:1.4rem;border-width:2px;color:#6366f1"></div></td></tr>';
    document.getElementById('uTbody').innerHTML = spinner;

    fetch('ajax/ajax_admin_users.php?' + params)
        .then(function(r){ return r.json(); })
        .then(function(res) {
            if (res.status !== 'success') return;
            _auTotal = res.total;
            document.getElementById('uCount').textContent = res.total + ' user' + (res.total !== 1 ? 's' : '');
            renderUsers(res.data);
            renderPagination();
        })
        .catch(function(){ document.getElementById('uTbody').innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Failed to load users</td></tr>'; });
}

/* ── Render rows ── */
function renderUsers(rows) {
    var tbody = document.getElementById('uTbody');
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:3.5rem 0;color:#94a3b8">'
            + '<div style="width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg,#ede9fe,#e0e7ff);display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:#6366f1;margin:0 auto .85rem">'
            + '<i class="bi bi-people"></i></div>'
            + '<div style="font-weight:700;margin-bottom:.3rem">No users found</div>'
            + '<div style="font-size:.8rem">Try adjusting your filters</div></td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(function(u, i) {
        var rm      = ROLE_META[u.user_role] || ROLE_META['1'];
        var isActive= u.user_status === 'Active';
        var col     = avatarColor(u.first_name + u.last_name);
        var ini     = initials(u.first_name, u.last_name);
        var uJson   = esc(JSON.stringify(u));
        return '<tr style="animation-delay:' + (i*.04) + 's">'
            + '<td><div style="display:flex;align-items:center;gap:.85rem">'
            + '<div class="u-avatar" style="background:' + col + '">' + ini + '</div>'
            + '<div><div style="font-size:.86rem;font-weight:700;color:#1e1b4b;line-height:1.3">' + esc(u.first_name) + ' ' + esc(u.last_name) + '</div>'
            + '<div style="font-size:.69rem;color:#94a3b8;font-family:monospace">' + esc(u.usr_code) + '</div></div></div></td>'
            + '<td><div style="font-size:.82rem;color:#334155">' + esc(u.email_address) + '</div>'
            + '<div style="font-size:.72rem;color:#94a3b8">' + esc(u.phone_number || '—') + '</div></td>'
            + '<td><span class="role-pill" style="color:' + rm.color + ';background:' + rm.bg + ';border-color:' + rm.color + '22">'
            + '<i class="bi ' + rm.icon + '" style="font-size:.7rem;pointer-events:none"></i>' + esc(rm.label) + '</span></td>'
            + '<td><div style="display:flex;align-items:center;gap:.55rem">'
            + '<span class="status-dot ' + (isActive?'active-dot':'') + '" style="background:' + (isActive?'#22c55e':'#94a3b8') + '"></span>'
            + '<span style="font-size:.8rem;font-weight:700;color:' + (isActive?'#15803d':'#64748b') + '">' + u.user_status + '</span></div></td>'
            + '<td style="font-size:.79rem;color:#64748b;white-space:nowrap">' + formatDate(u.created_at) + '</td>'
            + '<td><div class="um-action-wrap">'
            + '<button class="um-action-btn" onclick="window.toggleDropdown(event,\'dd_' + u.id + '\')" title="Actions"><i class="bi bi-three-dots-vertical"></i></button>'
            + '<div class="um-dropdown" id="dd_' + u.id + '" style="display:none">'
            + '<button class="um-dropdown-item" onclick="window.closeAllDropdowns();window.openEditModal(\'' + uJson.replace(/'/g,'&#39;') + '\')"><i class="bi bi-pencil-square" style="color:#6366f1"></i>Edit User</button>'
            + '<button class="um-dropdown-item" onclick="window.closeAllDropdowns();window.resetPassword(' + u.id + ',\'' + esc(u.first_name) + '\')"><i class="bi bi-key-fill" style="color:#f59e0b"></i>Reset Password</button>'
            + '<div class="um-dropdown-sep"></div>'
            + '<button class="um-dropdown-item danger" onclick="window.closeAllDropdowns();window.deleteUser(' + u.id + ',\'' + esc(u.first_name+' '+u.last_name) + '\')"><i class="bi bi-trash3-fill"></i>Delete User</button>'
            + '</div></div></td></tr>';
    }).join('');
}

/* ── Dropdown ── */
window.toggleDropdown = function(e, id) {
    e.stopPropagation();
    var dd = document.getElementById(id), open = dd.style.display !== 'none';
    window.closeAllDropdowns();
    if (!open) { dd.style.display = ''; document.getElementById('dropOverlay').style.display = ''; }
};
window.closeAllDropdowns = function() {
    document.querySelectorAll('.um-dropdown').forEach(function(d){ d.style.display = 'none'; });
    document.getElementById('dropOverlay').style.display = 'none';
};

/* ── Pagination ── */
function renderPagination() {
    var pages = Math.ceil(_auTotal / _auPer);
    var el    = document.getElementById('uPagination');
    if (!_auTotal) { el.innerHTML = ''; return; }
    var from = Math.min((_auPage-1)*_auPer+1, _auTotal);
    var to   = Math.min(_auPage*_auPer, _auTotal);
    var btns = '<button class="au-page-btn" onclick="window.goPage(' + (_auPage-1) + ')" ' + (_auPage<=1?'disabled':'') + '>‹</button>';
    var start= Math.max(1, _auPage-2), end = Math.min(pages, _auPage+2);
    if (start > 1) { btns += '<button class="au-page-btn" onclick="window.goPage(1)">1</button>'; if (start>2) btns += '<span style="padding:0 .3rem;color:#94a3b8">…</span>'; }
    for (var p = start; p <= end; p++) btns += '<button class="au-page-btn' + (p===_auPage?' active':'') + '" onclick="window.goPage(' + p + ')">' + p + '</button>';
    if (end < pages) { if (end<pages-1) btns += '<span style="padding:0 .3rem;color:#94a3b8">…</span>'; btns += '<button class="au-page-btn" onclick="window.goPage(' + pages + ')">' + pages + '</button>'; }
    btns += '<button class="au-page-btn" onclick="window.goPage(' + (_auPage+1) + ')" ' + (_auPage>=pages?'disabled':'') + '>›</button>';
    el.innerHTML = '<span style="font-size:.78rem;color:#64748b;font-weight:500">Showing ' + from + '–' + to + ' of ' + _auTotal + '</span>'
                 + '<div style="display:flex;gap:.3rem">' + btns + '</div>';
}
window.goPage = function(p) { _auPage = p; fetchUsers(); };

/* ── Create / Edit ── */
window.openCreateModal = function() {
    document.getElementById('userModalTitle').textContent = 'Add New User';
    document.getElementById('editUserId').value = '';
    ['uFirstName','uLastName','uEmail','uPhone','uPassword'].forEach(function(id){ document.getElementById(id).value=''; });
    document.getElementById('uRole').value           = '1';
    document.getElementById('passwordGroup').style.display = '';
    document.getElementById('statusGroup').style.display   = 'none';
    document.getElementById('saveBtnText').textContent     = 'Create Account';
    if (!_auModal) _auModal = new bootstrap.Modal(document.getElementById('userModal'));
    _auModal.show();
};
window.openEditModal = function(u) {
    if (typeof u === 'string') { try { u = JSON.parse(u); } catch(e){ return; } }
    document.getElementById('userModalTitle').textContent  = 'Edit User';
    document.getElementById('editUserId').value            = u.id;
    document.getElementById('uFirstName').value            = u.first_name    || '';
    document.getElementById('uLastName').value             = u.last_name     || '';
    document.getElementById('uEmail').value                = u.email_address || '';
    document.getElementById('uPhone').value                = u.phone_number  || '';
    document.getElementById('uRole').value                 = u.user_role;
    document.getElementById('uStatus').value               = u.user_status;
    document.getElementById('uPassword').value             = '';
    document.getElementById('passwordGroup').style.display = 'none';
    document.getElementById('statusGroup').style.display   = '';
    document.getElementById('saveBtnText').textContent     = 'Save Changes';
    if (!_auModal) _auModal = new bootstrap.Modal(document.getElementById('userModal'));
    _auModal.show();
};

/* ── Save ── */
window.saveUser = function() {
    var id         = document.getElementById('editUserId').value;
    var first_name = document.getElementById('uFirstName').value.trim();
    var last_name  = document.getElementById('uLastName').value.trim();
    var email      = document.getElementById('uEmail').value.trim();
    var phone      = document.getElementById('uPhone').value.trim();
    var role       = document.getElementById('uRole').value;
    var password   = document.getElementById('uPassword').value;
    var status     = document.getElementById('uStatus').value;
    if (!first_name || !last_name || !email || !role) {
        Swal.fire({icon:'warning',title:'Incomplete',text:'First name, last name, email and role are required.'}); return;
    }
    if (!id && password.length < 8) {
        Swal.fire({icon:'warning',title:'Weak Password',text:'Password must be at least 8 characters.'}); return;
    }
    var btn = document.getElementById('saveUserBtn');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';
    var fd = new FormData();
    fd.append('action',     id ? 'update' : 'create');
    fd.append('id',         id);
    fd.append('first_name', first_name);
    fd.append('last_name',  last_name);
    fd.append('email',      email);
    fd.append('phone',      phone);
    fd.append('role',       role);
    fd.append('status',     status);
    if (!id) fd.append('password', password);
    fetch('ajax/ajax_admin_users.php', {method:'POST', body:fd})
        .then(function(r){ return r.json(); })
        .then(function(res) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg"></i><span id="saveBtnText">Save</span>';
            if (res.status === 'success') {
                _auModal.hide();
                Swal.fire({icon:'success',title:id?'User Updated!':'Account Created!',timer:1400,showConfirmButton:false,toast:true,position:'top-end'});
                window.loadUsers();
            } else { Swal.fire({icon:'error',title:'Error',text:res.message}); }
        });
};

/* ── Delete ── */
window.deleteUser = function(id, name) {
    Swal.fire({
        icon:'warning', title:'Delete User?',
        html:'<p>Delete <strong>' + esc(name) + '</strong>?<br><span class="text-muted small">This action cannot be undone.</span></p>',
        showCancelButton:true, confirmButtonColor:'#dc2626', confirmButtonText:'Yes, Delete'
    }).then(function(r) {
        if (!r.isConfirmed) return;
        var fd = new FormData(); fd.append('action','delete'); fd.append('id',id);
        fetch('ajax/ajax_admin_users.php',{method:'POST',body:fd}).then(function(r){ return r.json(); })
            .then(function(res) {
                if (res.status==='success') { Swal.fire({icon:'success',title:'Deleted',timer:1200,showConfirmButton:false,toast:true,position:'top-end'}); window.loadUsers(); }
                else Swal.fire({icon:'error',title:'Error',text:res.message});
            });
    });
};

/* ── Reset password ── */
window.resetPassword = function(id, name) {
    Swal.fire({
        title:'Reset Password',
        html:'<div style="font-size:.875rem;color:#64748b;margin-bottom:.75rem">Setting new password for <b>' + esc(name) + '</b></div>',
        input:'password', inputLabel:'New password (min. 8 characters)',
        inputAttributes:{minlength:8, autocomplete:'new-password', placeholder:'Enter new password'},
        showCancelButton:true, confirmButtonText:'Reset Password', confirmButtonColor:'#6366f1',
        preConfirm:function(pw){ if(!pw||pw.length<8){Swal.showValidationMessage('Minimum 8 characters required');return false;} return pw; }
    }).then(function(r) {
        if (!r.isConfirmed) return;
        var fd = new FormData(); fd.append('action','reset_password'); fd.append('id',id); fd.append('password',r.value);
        fetch('ajax/ajax_admin_users.php',{method:'POST',body:fd}).then(function(r){return r.json();})
            .then(function(res){
                if (res.status==='success') Swal.fire({icon:'success',title:'Password Reset!',timer:1400,showConfirmButton:false,toast:true,position:'top-end'});
                else Swal.fire({icon:'error',title:'Error',text:res.message});
            });
    });
};

/* ── Misc ── */
window.togglePwd = function() {
    var inp = document.getElementById('uPassword'), ico = document.getElementById('eyeIcon');
    if (inp.type === 'password') { inp.type = 'text'; ico.className = 'bi bi-eye-slash'; }
    else { inp.type = 'password'; ico.className = 'bi bi-eye'; }
};

/* ── Wire toolbar listeners directly ── */
document.getElementById('fSearch').addEventListener('input', window.debounceSearch);
document.getElementById('fRole').addEventListener('change',    window.loadUsers);
document.getElementById('fStatus').addEventListener('change',  window.loadUsers);
document.getElementById('fPerPage').addEventListener('change', window.loadUsers);
document.getElementById('auResetBtn').addEventListener('click', window.resetFilters);

/* ── Init — call directly, DOMContentLoaded already fired in SPA ── */
window.loadUsers();
</script>
