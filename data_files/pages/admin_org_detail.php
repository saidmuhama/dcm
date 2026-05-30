<?php
require_once __DIR__ . '/../config/url_crypt_config.php';

$oid = decryptURLId($_GET['oid'] ?? '', ctx: 'org');
if (!$oid) { include('404.php'); return; }

$oidSafe = (int)$oid;
$orgRow  = $db->query("
    SELECT o.*, p.plan_name, p.plan_code,
           CONCAT(u.first_name,' ',u.last_name) AS admin_name,
           u.email_address AS admin_email,
           u.phone_number  AS admin_phone
    FROM tbl_organizations o
    LEFT JOIN tbl_org_plans p ON p.id = o.plan_id
    LEFT JOIN tbl_all_users u ON u.usr_code = o.admin_usr_code
    WHERE o.id = $oidSafe AND o.deleted_at IS NULL
    LIMIT 1
")->fetch_assoc();
if (!$orgRow) { include('404.php'); return; }

$oidToken  = encryptURLId($oidSafe, ctx: 'org');
$initials  = strtoupper(substr($orgRow['org_name'], 0, 2));
$isExpired = $orgRow['license_expires_at'] && strtotime($orgRow['license_expires_at']) < time();
$expiringSoon = !$isExpired && $orgRow['license_expires_at'] && strtotime($orgRow['license_expires_at']) < strtotime('+30 days');

$statusGrad = match($orgRow['status']) {
    'active'    => 'linear-gradient(135deg,#059669,#047857)',
    'suspended' => 'linear-gradient(135deg,#f59e0b,#d97706)',
    'expired'   => 'linear-gradient(135deg,#ef4444,#dc2626)',
    default     => 'linear-gradient(135deg,#6366f1,#4f46e5)',
};
$typeLabel = ['school'=>'School','college'=>'College','company'=>'Company','institution'=>'Institution','training_center'=>'Training Center'][$orgRow['org_type']] ?? ucfirst($orgRow['org_type']);
?>
<style>
/* ── Layout ─────────────────────────────────────────────────── */
#aodRoot { --aod-radius: 16px; --aod-shadow: 0 2px 12px rgba(0,0,0,.07); }

/* ── Hero header ─────────────────────────────────────────────── */
.aod-hero {
    background: linear-gradient(135deg,#1e1b4b 0%,#312e81 50%,#4c1d95 100%);
    border-radius: var(--aod-radius);
    overflow: hidden;
    position: relative;
    padding: 2rem 2rem 1.5rem;
    margin-bottom: 1.5rem;
    color: #fff;
}
.aod-hero::before {
    content:'';
    position:absolute;inset:0;
    background: radial-gradient(circle at 80% -10%, rgba(139,92,246,.45) 0%, transparent 60%);
    pointer-events:none;
}
.aod-hero-logo {
    width:72px;height:72px;border-radius:16px;
    background:rgba(255,255,255,.18);backdrop-filter:blur(8px);
    border:2px solid rgba(255,255,255,.25);
    display:flex;align-items:center;justify-content:center;
    font-size:1.6rem;font-weight:800;color:#fff;flex-shrink:0;
    letter-spacing:-.5px;
}
.aod-hero-logo img { width:100%;height:100%;object-fit:contain;border-radius:14px; }
.aod-hero-name { font-size:1.4rem;font-weight:800;line-height:1.2;margin-bottom:.3rem; }
.aod-hero-code { font-size:.78rem;opacity:.65;font-family:monospace;letter-spacing:.04em; }
.aod-pill {
    display:inline-flex;align-items:center;gap:.3rem;
    padding:.22rem .7rem;border-radius:100px;font-size:.72rem;font-weight:700;
    backdrop-filter:blur(4px);
}
.aod-pill-status-active    { background:rgba(16,185,129,.25);color:#a7f3d0;border:1px solid rgba(16,185,129,.35); }
.aod-pill-status-suspended { background:rgba(245,158,11,.25);color:#fde68a;border:1px solid rgba(245,158,11,.35); }
.aod-pill-status-expired   { background:rgba(239,68,68,.25);color:#fecaca;border:1px solid rgba(239,68,68,.35); }
.aod-pill-status-pending   { background:rgba(139,92,246,.25);color:#ddd6fe;border:1px solid rgba(139,92,246,.35); }
.aod-pill-type { background:rgba(255,255,255,.15);color:rgba(255,255,255,.9);border:1px solid rgba(255,255,255,.2); }
.aod-hero-meta { display:flex;flex-wrap:wrap;gap:.5rem .75rem;font-size:.8rem;opacity:.8;margin-top:.6rem; }
.aod-hero-meta span { display:flex;align-items:center;gap:.3rem; }
.aod-hero-divider { border-top:1px solid rgba(255,255,255,.12);margin:.5rem 0; }
.aod-hero-actions { display:flex;flex-wrap:wrap;gap:.5rem;padding-top:.75rem; }
.aod-hero-btn {
    display:inline-flex;align-items:center;gap:.35rem;
    padding:.42rem .9rem;border-radius:10px;font-size:.82rem;font-weight:600;
    border:1.5px solid rgba(255,255,255,.3);color:#fff;background:rgba(255,255,255,.12);
    cursor:pointer;transition:background .15s,border-color .15s;
}
.aod-hero-btn:hover { background:rgba(255,255,255,.22);border-color:rgba(255,255,255,.5); }
.aod-hero-btn.danger { border-color:rgba(239,68,68,.5);color:#fca5a5;background:rgba(239,68,68,.12); }
.aod-hero-btn.danger:hover { background:rgba(239,68,68,.22); }
.aod-hero-btn.success { border-color:rgba(16,185,129,.5);color:#6ee7b7;background:rgba(16,185,129,.12); }
.aod-hero-btn.success:hover { background:rgba(16,185,129,.22); }
.aod-hero-btn.warn { border-color:rgba(245,158,11,.5);color:#fde68a;background:rgba(245,158,11,.12); }
.aod-hero-btn.warn:hover { background:rgba(245,158,11,.22); }

/* ── Stat cards ───────────────────────────────────────────────── */
.aod-stat {
    border-radius: var(--aod-radius);
    padding: 1.1rem 1.3rem;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.aod-stat .sv { font-size: 1.9rem; font-weight: 800; line-height: 1; margin-bottom: .2rem; }
.aod-stat .sl { font-size: .72rem; opacity: .88; font-weight: 500; text-transform:uppercase; letter-spacing:.05em; }
.aod-stat .si { position:absolute;right:.9rem;top:50%;transform:translateY(-50%);font-size:2.4rem;opacity:.2; }

/* ── Tabs ─────────────────────────────────────────────────────── */
.aod-tabs { border-bottom: 2px solid #e5e7eb; margin-bottom: 1.5rem; gap:.25rem; display:flex;flex-wrap:wrap; }
.aod-tab-btn {
    padding:.55rem 1rem;border:none;background:none;color:#64748b;
    font-size:.84rem;font-weight:600;cursor:pointer;border-radius:8px 8px 0 0;
    display:flex;align-items:center;gap:.35rem;
    border-bottom: 2px solid transparent;margin-bottom:-2px;
    transition:color .15s,border-color .15s,background .15s;
}
.aod-tab-btn:hover { color:#6366f1;background:#f0f0ff; }
.aod-tab-btn.active { color:#6366f1;border-bottom-color:#6366f1;background:#f5f3ff; }
.aod-tab-btn .tab-count {
    background:#e0e7ff;color:#6366f1;border-radius:100px;
    font-size:.68rem;font-weight:700;padding:.1rem .4rem;
}
.aod-tab-btn.active .tab-count { background:#6366f1;color:#fff; }
.aod-pane { display:none; }
.aod-pane.active { display:block; }

/* ── Section cards ────────────────────────────────────────────── */
.aod-card {
    background:#fff;border-radius:var(--aod-radius);
    box-shadow:var(--aod-shadow);border:1px solid #f1f5f9;
    overflow:hidden;
}
.aod-card-hd {
    padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;
    display:flex;align-items:center;justify-content:space-between;
    background:#fafbff;
}
.aod-card-hd h6 { margin:0;font-size:.84rem;font-weight:700;color:#1e293b; }
.aod-card-body { padding:1.25rem; }

/* ── Overview detail rows ─────────────────────────────────────── */
.aod-detail-row {
    display:flex;align-items:flex-start;gap:.5rem;
    padding:.6rem 0;border-bottom:1px solid #f8fafc;font-size:.84rem;
}
.aod-detail-row:last-child { border-bottom:none;padding-bottom:0; }
.aod-detail-label { color:#94a3b8;font-size:.76rem;font-weight:600;text-transform:uppercase;
    letter-spacing:.04em;min-width:130px;flex-shrink:0;padding-top:.1rem; }
.aod-detail-val { color:#1e293b;font-weight:500;flex:1;word-break:break-word; }

/* ── Admin info card ──────────────────────────────────────────── */
.aod-admin-card {
    background: linear-gradient(135deg,#f8f7ff,#ede9fe);
    border:1px solid #ddd6fe;border-radius:14px;padding:1.25rem;
}
.aod-admin-avatar {
    width:52px;height:52px;border-radius:14px;
    background:linear-gradient(135deg,#6366f1,#8b5cf6);
    display:flex;align-items:center;justify-content:center;
    font-size:1.2rem;font-weight:800;color:#fff;flex-shrink:0;
}
.aod-admin-badge { background:#ede9fe;color:#5b21b6;font-size:.68rem;font-weight:700;padding:.15rem .5rem;border-radius:100px; }

/* ── Members table ────────────────────────────────────────────── */
.aod-member-avatar {
    width:34px;height:34px;border-radius:10px;
    display:inline-flex;align-items:center;justify-content:center;
    font-size:.78rem;font-weight:700;color:#fff;flex-shrink:0;
}
.aod-role-pill {
    font-size:.68rem;font-weight:700;padding:.15rem .55rem;border-radius:100px;
    text-transform:capitalize;
}

/* ── Course access ────────────────────────────────────────────── */
.aod-course-icon {
    width:38px;height:38px;border-radius:10px;background:#f0f4ff;
    display:flex;align-items:center;justify-content:center;
    font-size:1.1rem;color:#6366f1;flex-shrink:0;
}

/* ── Activity timeline ────────────────────────────────────────── */
.aod-timeline { padding:0 .5rem; }
.aod-tl-item { display:flex;gap:1rem;padding:.75rem 0;border-bottom:1px solid #f8fafc;align-items:flex-start; }
.aod-tl-item:last-child { border-bottom:none; }
.aod-tl-dot {
    width:34px;height:34px;border-radius:10px;
    display:flex;align-items:center;justify-content:center;
    font-size:.9rem;flex-shrink:0;
}
.aod-tl-action { font-size:.76rem;font-weight:700;padding:.15rem .55rem;border-radius:6px;display:inline-block; }

/* ── Dept cards ───────────────────────────────────────────────── */
.aod-dept-chip {
    background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;
    padding:.85rem 1rem;display:flex;align-items:center;gap:.75rem;
    transition:box-shadow .15s;
}
.aod-dept-chip:hover { box-shadow:0 4px 12px rgba(0,0,0,.08); }
.aod-dept-icon {
    width:38px;height:38px;border-radius:10px;
    background:linear-gradient(135deg,#e0e7ff,#c7d2fe);
    display:flex;align-items:center;justify-content:center;
    color:#6366f1;font-size:1rem;flex-shrink:0;
}

/* ── Empty states ─────────────────────────────────────────────── */
.aod-empty { padding:3rem 1rem;text-align:center;color:#94a3b8; }
.aod-empty i { font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem; }
.aod-empty p { font-size:.85rem;margin:0; }

/* ── Responsive ───────────────────────────────────────────────── */
@media (max-width:576px) {
    .aod-hero { padding:1.25rem; }
    .aod-hero-name { font-size:1.1rem; }
    .aod-detail-label { min-width:100px; }
    .aod-hero-btn span { display:none; }
}
</style>

<div class="container-fluid px-3 pt-3 pb-5" id="aodRoot">

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small mb-0">
        <li class="breadcrumb-item"><a href="../data_files/?view=admin_organizations" class="text-decoration-none">Organizations</a></li>
        <li class="breadcrumb-item active text-truncate" style="max-width:220px"><?= htmlspecialchars($orgRow['org_name']) ?></li>
    </ol>
</nav>

<!-- ── Hero Header ───────────────────────────────────────────── -->
<div class="aod-hero mb-4">
    <div class="d-flex align-items-start gap-3 flex-wrap">
        <div class="aod-hero-logo">
            <?php if ($orgRow['logo']): ?>
            <img src="uploads/org_logos/<?= htmlspecialchars(basename($orgRow['logo'])) ?>" alt="Logo">
            <?php else: ?>
            <?= $initials ?>
            <?php endif; ?>
        </div>
        <div class="flex-grow-1 min-w-0">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <div class="aod-hero-name"><?= htmlspecialchars($orgRow['org_name']) ?></div>
                <span class="aod-pill aod-pill-status-<?= $orgRow['status'] ?>">
                    <i class="bi bi-<?= $orgRow['status']==='active'?'check-circle':'pause-circle' ?>"></i>
                    <?= ucfirst($orgRow['status']) ?>
                </span>
                <span class="aod-pill aod-pill-type"><?= $typeLabel ?></span>
                <?php if ($isExpired): ?>
                <span class="aod-pill" style="background:rgba(239,68,68,.25);color:#fca5a5;border:1px solid rgba(239,68,68,.35)"><i class="bi bi-exclamation-triangle-fill"></i> License Expired</span>
                <?php elseif ($expiringSoon): ?>
                <span class="aod-pill" style="background:rgba(245,158,11,.25);color:#fde68a;border:1px solid rgba(245,158,11,.35)"><i class="bi bi-clock-history"></i> Expiring Soon</span>
                <?php endif; ?>
            </div>
            <div class="aod-hero-code"><?= htmlspecialchars($orgRow['org_code']) ?></div>
            <div class="aod-hero-meta">
                <?php if ($orgRow['email']): ?><span><i class="bi bi-envelope"></i><?= htmlspecialchars($orgRow['email']) ?></span><?php endif; ?>
                <?php if ($orgRow['phone']): ?><span><i class="bi bi-telephone"></i><?= htmlspecialchars($orgRow['phone']) ?></span><?php endif; ?>
                <?php if ($orgRow['country']): ?><span><i class="bi bi-globe2"></i><?= htmlspecialchars($orgRow['country']) ?></span><?php endif; ?>
                <?php if ($orgRow['plan_name']): ?><span><i class="bi bi-box"></i><?= htmlspecialchars($orgRow['plan_name']) ?> Plan</span><?php endif; ?>
                <span><i class="bi bi-calendar3"></i>Since <?= date('M Y', strtotime($orgRow['created_at'])) ?></span>
            </div>
        </div>
    </div>

    <div class="aod-hero-divider"></div>

    <div class="aod-hero-actions">
        <button class="aod-hero-btn" onclick="aodOpenEdit()">
            <i class="bi bi-pencil-fill"></i><span>Edit Details</span>
        </button>
        <button class="aod-hero-btn warn" onclick="aodResetAdminPass()">
            <i class="bi bi-key-fill"></i><span>Reset Admin Password</span>
        </button>
        <?php if ($orgRow['status'] === 'active'): ?>
        <button class="aod-hero-btn warn" onclick="aodToggle('suspended')">
            <i class="bi bi-pause-circle-fill"></i><span>Suspend</span>
        </button>
        <?php else: ?>
        <button class="aod-hero-btn success" onclick="aodToggle('active')">
            <i class="bi bi-play-circle-fill"></i><span>Activate</span>
        </button>
        <?php endif; ?>
        <a class="aod-hero-btn" href="../data_files/?view=admin_organizations" style="text-decoration:none">
            <i class="bi bi-arrow-left"></i><span>Back to List</span>
        </a>
    </div>
</div>

<!-- ── Stats Row ─────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="aod-stat" style="background:linear-gradient(135deg,#6366f1,#4f46e5)">
            <div class="sv" id="statMembers">—</div>
            <div class="sl">Total Members</div>
            <i class="bi bi-people-fill si"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="aod-stat" style="background:linear-gradient(135deg,#059669,#047857)">
            <div class="sv" id="statActive">—</div>
            <div class="sl">Active Users</div>
            <i class="bi bi-person-check-fill si"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="aod-stat" style="background:linear-gradient(135deg,#0891b2,#0e7490)">
            <div class="sv" id="statCourses">—</div>
            <div class="sl">Courses Granted</div>
            <i class="bi bi-collection-fill si"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="aod-stat" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
            <div class="sv" id="statDepts">—</div>
            <div class="sl">Departments</div>
            <i class="bi bi-diagram-3-fill si"></i>
        </div>
    </div>
</div>

<!-- ── Custom Tabs ───────────────────────────────────────────── -->
<div class="aod-tabs">
    <button class="aod-tab-btn active" onclick="aodTab('overview',this)"><i class="bi bi-grid-1x2"></i> Overview</button>
    <button class="aod-tab-btn" onclick="aodTab('members',this)"><i class="bi bi-people"></i> Members <span class="tab-count" id="memberCount">—</span></button>
    <button class="aod-tab-btn" onclick="aodTab('depts',this)"><i class="bi bi-diagram-3"></i> Departments</button>
    <button class="aod-tab-btn" onclick="aodTab('courses',this)"><i class="bi bi-collection"></i> Course Access</button>
    <button class="aod-tab-btn" onclick="aodTab('activity',this)"><i class="bi bi-clock-history"></i> Activity</button>
</div>

<!-- ── Overview ─────────────────────────────────────────────── -->
<div class="aod-pane active" id="pane-overview">
    <div class="row g-3">

        <!-- Organization details -->
        <div class="col-lg-6">
            <div class="aod-card">
                <div class="aod-card-hd">
                    <h6><i class="bi bi-building me-2 text-primary"></i>Organization Details</h6>
                </div>
                <div class="aod-card-body">
                    <div class="aod-detail-row"><div class="aod-detail-label">Org Code</div><div class="aod-detail-val font-monospace"><?= htmlspecialchars($orgRow['org_code']) ?></div></div>
                    <div class="aod-detail-row"><div class="aod-detail-label">Type</div><div class="aod-detail-val"><?= $typeLabel ?></div></div>
                    <div class="aod-detail-row">
                        <div class="aod-detail-label">Status</div>
                        <div class="aod-detail-val">
                            <span style="background:<?= ['active'=>'#dcfce7','suspended'=>'#fef3c7','expired'=>'#fee2e2','pending'=>'#ede9fe'][$orgRow['status']] ?? '#f1f5f9' ?>;color:<?= ['active'=>'#166534','suspended'=>'#92400e','expired'=>'#991b1b','pending'=>'#5b21b6'][$orgRow['status']] ?? '#475569' ?>;padding:.15rem .65rem;border-radius:100px;font-size:.76rem;font-weight:700;"><?= ucfirst($orgRow['status']) ?></span>
                        </div>
                    </div>
                    <div class="aod-detail-row"><div class="aod-detail-label">Plan</div><div class="aod-detail-val"><?= htmlspecialchars($orgRow['plan_name'] ?? '—') ?></div></div>
                    <div class="aod-detail-row">
                        <div class="aod-detail-label">Max Users</div>
                        <div class="aod-detail-val"><?= $orgRow['max_users'] == -1 ? '<span class="text-success fw-semibold">Unlimited</span>' : number_format($orgRow['max_users']) ?></div>
                    </div>
                    <div class="aod-detail-row">
                        <div class="aod-detail-label">Storage</div>
                        <div class="aod-detail-val"><?= $orgRow['storage_limit_gb'] == -1 ? '<span class="text-success fw-semibold">Unlimited</span>' : $orgRow['storage_limit_gb'].' GB' ?></div>
                    </div>
                    <div class="aod-detail-row">
                        <div class="aod-detail-label">License Expires</div>
                        <div class="aod-detail-val">
                            <?php if ($orgRow['license_expires_at']): ?>
                            <span class="<?= $isExpired ? 'text-danger fw-semibold' : ($expiringSoon ? 'text-warning fw-semibold' : '') ?>">
                                <?= date('M j, Y', strtotime($orgRow['license_expires_at'])) ?>
                                <?= $isExpired ? ' <span class="badge bg-danger ms-1">Expired</span>' : ($expiringSoon ? ' <span class="badge bg-warning text-dark ms-1">Soon</span>' : '') ?>
                            </span>
                            <?php else: ?>—<?php endif; ?>
                        </div>
                    </div>
                    <?php if ($orgRow['domain']): ?>
                    <div class="aod-detail-row"><div class="aod-detail-label">Domain</div><div class="aod-detail-val"><a href="https://<?= htmlspecialchars($orgRow['domain']) ?>" target="_blank" rel="noopener" class="text-decoration-none"><?= htmlspecialchars($orgRow['domain']) ?> <i class="bi bi-box-arrow-up-right" style="font-size:.7rem"></i></a></div></div>
                    <?php endif; ?>
                    <div class="aod-detail-row"><div class="aod-detail-label">Created</div><div class="aod-detail-val"><?= date('M j, Y', strtotime($orgRow['created_at'])) ?></div></div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 d-flex flex-column gap-3">

            <!-- Contact info -->
            <div class="aod-card">
                <div class="aod-card-hd">
                    <h6><i class="bi bi-geo-alt me-2 text-info"></i>Contact &amp; Location</h6>
                </div>
                <div class="aod-card-body">
                    <div class="aod-detail-row"><div class="aod-detail-label">Email</div><div class="aod-detail-val"><?= $orgRow['email'] ? '<a href="mailto:'.htmlspecialchars($orgRow['email']).'" class="text-decoration-none">'.htmlspecialchars($orgRow['email']).'</a>' : '<span class="text-muted">—</span>' ?></div></div>
                    <div class="aod-detail-row"><div class="aod-detail-label">Phone</div><div class="aod-detail-val"><?= $orgRow['phone'] ? htmlspecialchars($orgRow['phone']) : '<span class="text-muted">—</span>' ?></div></div>
                    <div class="aod-detail-row"><div class="aod-detail-label">Country</div><div class="aod-detail-val"><?= $orgRow['country'] ? htmlspecialchars($orgRow['country']) : '<span class="text-muted">—</span>' ?></div></div>
                    <div class="aod-detail-row"><div class="aod-detail-label">Address</div><div class="aod-detail-val"><?= $orgRow['address'] ? nl2br(htmlspecialchars($orgRow['address'])) : '<span class="text-muted">—</span>' ?></div></div>
                </div>
            </div>

            <!-- Admin info -->
            <div class="aod-admin-card">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="aod-admin-avatar"><?= strtoupper(substr($orgRow['admin_name'] ?: 'A', 0, 2)) ?></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-bold" style="font-size:.92rem;color:#1e293b"><?= htmlspecialchars($orgRow['admin_name'] ?: 'No Admin Assigned') ?></div>
                        <span class="aod-admin-badge">Organization Admin</span>
                    </div>
                    <button class="btn btn-sm fw-semibold px-3" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:10px;font-size:.78rem;white-space:nowrap" onclick="aodResetAdminPass()">
                        <i class="bi bi-key me-1"></i>Reset Password
                    </button>
                </div>
                <div class="aod-detail-row" style="border-color:#ddd6fe"><div class="aod-detail-label" style="color:#7c3aed">Email</div><div class="aod-detail-val" style="color:#312e81"><?= $orgRow['admin_email'] ? '<a href="mailto:'.htmlspecialchars($orgRow['admin_email']).'" class="text-decoration-none" style="color:#5b21b6">'.htmlspecialchars($orgRow['admin_email']).'</a>' : '<span class="text-muted">—</span>' ?></div></div>
                <div class="aod-detail-row" style="border-color:#ddd6fe;border-bottom:none;padding-bottom:0"><div class="aod-detail-label" style="color:#7c3aed">Phone</div><div class="aod-detail-val" style="color:#312e81"><?= $orgRow['admin_phone'] ? htmlspecialchars($orgRow['admin_phone']) : '<span class="text-muted">—</span>' ?></div></div>
            </div>

            <!-- Notes -->
            <?php if ($orgRow['notes']): ?>
            <div class="aod-card">
                <div class="aod-card-hd">
                    <h6><i class="bi bi-sticky me-2 text-warning"></i>Admin Notes</h6>
                </div>
                <div class="aod-card-body">
                    <p class="text-muted small mb-0" style="line-height:1.6"><?= nl2br(htmlspecialchars($orgRow['notes'])) ?></p>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- ── Members ───────────────────────────────────────────────── -->
<div class="aod-pane" id="pane-members">
    <div class="aod-card">
        <div class="aod-card-hd">
            <h6><i class="bi bi-people me-2 text-primary"></i>Members <span id="memberCountHd" class="text-muted fw-normal ms-1"></span></h6>
            <div class="d-flex gap-2 flex-wrap">
                <input type="search" id="memberSearch" class="form-control form-control-sm" style="width:190px" placeholder="Search…" oninput="aodFilterMembers()">
                <select id="memberRoleFilter" class="form-select form-select-sm" style="width:auto" onchange="aodFilterMembers()">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="coordinator">Coordinator</option>
                    <option value="instructor">Instructor</option>
                    <option value="student">Student</option>
                    <option value="staff">Staff</option>
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size:.84rem">
                <thead style="background:#f8f9ff;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:#64748b">
                    <tr>
                        <th class="ps-4 py-3 fw-semibold">Member</th>
                        <th class="py-3 fw-semibold">Role</th>
                        <th class="py-3 fw-semibold">Department</th>
                        <th class="py-3 fw-semibold">Status</th>
                        <th class="py-3 fw-semibold">Joined</th>
                        <th class="py-3 pe-4 text-end fw-semibold">Action</th>
                    </tr>
                </thead>
                <tbody id="membersTbody">
                    <tr><td colspan="6" class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2 text-primary"></div>Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── Departments ───────────────────────────────────────────── -->
<div class="aod-pane" id="pane-depts">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="fw-semibold text-muted small"><i class="bi bi-diagram-3 me-1"></i>Departments managed by the org admin</div>
    </div>
    <div class="row g-3" id="deptsGrid">
        <div class="col-12"><div class="aod-empty"><i class="bi bi-hourglass-split"></i><p>Loading…</p></div></div>
    </div>
</div>

<!-- ── Course Access ─────────────────────────────────────────── -->
<div class="aod-pane" id="pane-courses">
    <div class="aod-card">
        <div class="aod-card-hd">
            <h6><i class="bi bi-collection me-2 text-info"></i>Course Access</h6>
            <button class="btn btn-sm fw-semibold px-3" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:8px;font-size:.78rem" onclick="aodGrantCourse()">
                <i class="bi bi-plus-lg me-1"></i>Grant Course
            </button>
        </div>
        <div class="px-3 py-2 border-bottom" style="background:#fafbff">
            <input type="search" id="courseSearch" class="form-control form-control-sm" style="max-width:240px" placeholder="Search courses…" oninput="aodFilterCourses()">
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size:.84rem">
                <thead style="background:#f8f9ff;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:#64748b">
                    <tr>
                        <th class="ps-4 py-3 fw-semibold">Course</th>
                        <th class="py-3 fw-semibold">Granted By</th>
                        <th class="py-3 fw-semibold">Date</th>
                        <th class="py-3 fw-semibold">Expires</th>
                        <th class="py-3 fw-semibold">Status</th>
                        <th class="py-3 pe-4 text-end fw-semibold">Action</th>
                    </tr>
                </thead>
                <tbody id="coursesTbody">
                    <tr><td colspan="6" class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm me-2 text-info"></div>Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── Activity Log ──────────────────────────────────────────── -->
<div class="aod-pane" id="pane-activity">
    <div class="aod-card">
        <div class="aod-card-hd">
            <h6><i class="bi bi-clock-history me-2 text-secondary"></i>Activity Log</h6>
            <button class="btn btn-sm btn-outline-secondary" style="font-size:.76rem" onclick="activityLoaded=false;aodLoadActivity()"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</button>
        </div>
        <div class="aod-card-body" style="padding:.5rem 1rem">
            <div class="aod-timeline" id="activityTimeline">
                <div class="aod-empty"><i class="bi bi-hourglass-split"></i><p>Loading…</p></div>
            </div>
        </div>
    </div>
</div>

</div><!-- /aodRoot -->

<!-- ── Grant Course Modal ────────────────────────────────────── -->
<div class="modal fade" id="grantCourseModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px;border:none">
            <div class="modal-header" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border-radius:16px 16px 0 0;border:none">
                <h6 class="modal-title fw-bold"><i class="bi bi-collection-fill me-2"></i>Grant Course Access</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="input-group input-group-sm mb-3">
                    <span class="input-group-text bg-transparent"><i class="bi bi-search text-muted"></i></span>
                    <input type="search" id="gcSearch" class="form-control border-start-0" placeholder="Search courses…" oninput="gcFilter()">
                </div>
                <div id="gcList" style="max-height:380px;overflow-y:auto"></div>
            </div>
            <div class="modal-footer border-0 bg-light gap-2" style="border-radius:0 0 16px 16px">
                <span id="gcSelectedCount" class="text-muted small me-auto"></span>
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary btn-sm px-4 fw-semibold" onclick="gcSave()"><i class="bi bi-check2 me-1"></i>Grant Selected</button>
            </div>
        </div>
    </div>
</div>

<!-- ── Edit Org Modal ────────────────────────────────────────── -->
<div class="modal fade" id="editOrgModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:16px;border:none">
            <div class="modal-header" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border-radius:16px 16px 0 0;border:none">
                <h6 class="modal-title fw-bold"><i class="bi bi-pencil-fill me-2"></i>Edit Organization</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Organization Name</label>
                        <input type="text" class="form-control" id="eoOrgName" value="<?= htmlspecialchars($orgRow['org_name']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Type</label>
                        <select class="form-select" id="eoOrgType">
                            <?php foreach(['school','college','company','institution','training_center'] as $t): ?>
                            <option value="<?= $t ?>" <?= $orgRow['org_type']===$t?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$t)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Email</label>
                        <input type="email" class="form-control" id="eoEmail" value="<?= htmlspecialchars($orgRow['email']??'') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Phone</label>
                        <input type="text" class="form-control" id="eoPhone" value="<?= htmlspecialchars($orgRow['phone']??'') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Country</label>
                        <input type="text" class="form-control" id="eoCountry" value="<?= htmlspecialchars($orgRow['country']??'') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Domain</label>
                        <input type="text" class="form-control" id="eoDomain" placeholder="org.example.com" value="<?= htmlspecialchars($orgRow['domain']??'') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Status</label>
                        <select class="form-select" id="eoStatus">
                            <?php foreach(['active','suspended','expired','pending'] as $s): ?>
                            <option value="<?= $s ?>" <?= $orgRow['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">License Expires</label>
                        <input type="date" class="form-control" id="eoLicense" value="<?= $orgRow['license_expires_at']??'' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Max Users <small class="text-muted">(-1 = unlimited)</small></label>
                        <input type="number" class="form-control" id="eoMaxUsers" value="<?= $orgRow['max_users'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Storage Limit (GB) <small class="text-muted">(-1 = unlimited)</small></label>
                        <input type="number" class="form-control" id="eoStorage" value="<?= $orgRow['storage_limit_gb'] ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Address</label>
                        <textarea class="form-control" id="eoAddress" rows="2"><?= htmlspecialchars($orgRow['address']??'') ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Admin Notes</label>
                        <textarea class="form-control" id="eoNotes" rows="2" placeholder="Internal notes…"><?= htmlspecialchars($orgRow['notes']??'') ?></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light" style="border-radius:0 0 16px 16px">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary px-4 fw-semibold" id="eoSaveBtn" onclick="aodSubmitEdit()"><i class="bi bi-check2 me-1"></i>Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
const AOD_OID  = '<?= $oidToken ?>';
const AOD_AJAX = '../data_files/ajax/ajax_organizations.php';
const AOD_ADMIN_NAME  = <?= json_encode($orgRow['admin_name'] ?: '') ?>;
const AOD_ADMIN_EMAIL = <?= json_encode($orgRow['admin_email'] ?: '') ?>;
const AOD_ADMIN_PHONE = <?= json_encode($orgRow['admin_phone'] ?: '') ?>;

let allCoursesData = [], allMembersData = [], grantedCoursesList = [];
let membersLoaded = false, deptsLoaded = false, coursesLoaded = false, activityLoaded = false;

/* ── Tab switching ─────────────────────────────────────────── */
window.aodTab = function(name, btn) {
    document.querySelectorAll('.aod-tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.aod-pane').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('pane-' + name).classList.add('active');
    if (name === 'members'  && !membersLoaded)  aodLoadMembers();
    if (name === 'depts'    && !deptsLoaded)    aodLoadDepts();
    if (name === 'courses'  && !coursesLoaded)  aodLoadCourses();
    if (name === 'activity' && !activityLoaded) aodLoadActivity();
}

/* ── Stats ─────────────────────────────────────────────────── */
async function aodLoadStats() {
    const r = await fetch(`${AOD_AJAX}?action=stats&oid=${encodeURIComponent(AOD_OID)}`).then(x=>x.json()).catch(()=>({}));
    if (r.status === 'success') {
        document.getElementById('statMembers').textContent = r.total_members ?? '—';
        document.getElementById('statActive').textContent  = r.active_members ?? '—';
        document.getElementById('statCourses').textContent = r.total_courses ?? '—';
        document.getElementById('statDepts').textContent   = r.total_depts ?? '—';
        const mc = document.getElementById('memberCount');
        if (mc) mc.textContent = r.total_members ?? '';
        const mchd = document.getElementById('memberCountHd');
        if (mchd) mchd.textContent = r.total_members ? `(${r.total_members})` : '';
    }
}

/* ── Members ────────────────────────────────────────────────── */
const roleColors = {admin:'#ef4444',coordinator:'#f59e0b',instructor:'#06b6d4',student:'#6366f1',staff:'#64748b'};
const roleBg     = {admin:'#fee2e2',coordinator:'#fef3c7',instructor:'#cffafe',student:'#e0e7ff',staff:'#f1f5f9'};

async function aodLoadMembers() {
    membersLoaded = true;
    document.getElementById('membersTbody').innerHTML = '<tr><td colspan="6" class="text-center py-5"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Loading…</td></tr>';
    const r = await fetch(`${AOD_AJAX}?action=list_members&oid=${encodeURIComponent(AOD_OID)}`).then(x=>x.json()).catch(()=>({}));
    allMembersData = r.members ?? [];
    renderMembers(allMembersData);
}

function renderMembers(list) {
    const tb = document.getElementById('membersTbody');
    if (!list.length) {
        tb.innerHTML = `<tr><td colspan="6"><div class="aod-empty"><i class="bi bi-people"></i><p>No members found.</p></div></td></tr>`;
        return;
    }
    tb.innerHTML = list.map(m => {
        const ini = (m.full_name||'?').trim().split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase();
        const rc  = roleColors[m.org_role] || '#64748b';
        const rb  = roleBg[m.org_role]    || '#f1f5f9';
        return `<tr>
            <td class="ps-4 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="aod-member-avatar" style="background:${rc}">${esc(ini)}</div>
                    <div>
                        <div class="fw-semibold">${esc(m.full_name)}</div>
                        <div class="text-muted" style="font-size:.75rem">${esc(m.email)}</div>
                    </div>
                </div>
            </td>
            <td><span class="aod-role-pill" style="background:${rb};color:${rc}">${esc(m.org_role)}</span></td>
            <td class="text-muted">${esc(m.dept_name||'—')}</td>
            <td>
                <span style="background:${m.status==='active'?'#dcfce7':'#f1f5f9'};color:${m.status==='active'?'#166534':'#475569'};padding:.15rem .55rem;border-radius:100px;font-size:.72rem;font-weight:700">
                    ${esc(m.status)}
                </span>
            </td>
            <td class="text-muted">${fmtDate(m.joined_at)}</td>
            <td class="pe-4 text-end">
                <button class="btn btn-sm" style="background:#fee2e2;color:#dc2626;border:none;border-radius:8px;font-size:.76rem;padding:.25rem .6rem" onclick="aodRemoveMember('${m.usr_code}')" title="Remove member">
                    <i class="bi bi-person-dash me-1"></i>Remove
                </button>
            </td>
        </tr>`;
    }).join('');
}

window.aodFilterMembers = function() {
    const q    = document.getElementById('memberSearch').value.toLowerCase();
    const role = document.getElementById('memberRoleFilter').value;
    renderMembers(allMembersData.filter(m =>
        (!q    || m.full_name.toLowerCase().includes(q) || m.email.toLowerCase().includes(q)) &&
        (!role || m.org_role === role)
    ));
}

window.aodRemoveMember = async function(usrCode) {
    const m = allMembersData.find(x => x.usr_code === usrCode);
    const name = m ? m.full_name : 'this member';
    const conf = await Swal.fire({
        title: 'Remove Member?',
        html: `<strong>${esc(name)}</strong> will be removed from this organization.<br>Their account will remain active.`,
        icon: 'warning', showCancelButton: true,
        confirmButtonText: 'Yes, Remove', confirmButtonColor: '#dc2626',
    });
    if (!conf.isConfirmed) return;
    const r = await post({action:'remove_member', oid: AOD_OID, usr_code: usrCode});
    if (r.status === 'success') { toast('Member removed'); membersLoaded=false; aodLoadMembers(); aodLoadStats(); }
    else toast(r.message||'Error','danger');
}

/* ── Departments ─────────────────────────────────────────────── */
async function aodLoadDepts() {
    deptsLoaded = true;
    renderDepts(null);
    const r = await fetch(`${AOD_AJAX}?action=list_depts&oid=${encodeURIComponent(AOD_OID)}`).then(x=>x.json()).catch(()=>({}));
    renderDepts(r.departments ?? []);
}

function renderDepts(list) {
    const g = document.getElementById('deptsGrid');
    if (!list) { g.innerHTML = '<div class="col-12"><div class="aod-empty"><i class="bi bi-hourglass-split"></i><p>Loading…</p></div></div>'; return; }
    if (!list.length) { g.innerHTML = '<div class="col-12"><div class="aod-empty"><i class="bi bi-diagram-3"></i><p>No departments have been created yet.</p></div></div>'; return; }
    g.innerHTML = list.map(d => `
        <div class="col-sm-6 col-lg-4">
            <div class="aod-dept-chip">
                <div class="aod-dept-icon"><i class="bi bi-diagram-3"></i></div>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold" style="font-size:.88rem">${esc(d.dept_name)}</div>
                    ${d.dept_code ? `<div class="text-muted" style="font-size:.74rem;font-family:monospace">${esc(d.dept_code)}</div>` : ''}
                    <div class="text-muted" style="font-size:.74rem">${d.member_count||0} member${d.member_count!=1?'s':''}</div>
                </div>
                <span style="background:${d.status==='active'?'#dcfce7':'#f1f5f9'};color:${d.status==='active'?'#166534':'#475569'};padding:.15rem .55rem;border-radius:100px;font-size:.68rem;font-weight:700;flex-shrink:0">${esc(d.status)}</span>
            </div>
        </div>`).join('');
}

/* ── Courses ─────────────────────────────────────────────────── */
async function aodLoadCourses() {
    coursesLoaded = true;
    document.getElementById('coursesTbody').innerHTML = '<tr><td colspan="6" class="text-center py-5"><div class="spinner-border spinner-border-sm text-info me-2"></div>Loading…</td></tr>';
    const r = await fetch(`${AOD_AJAX}?action=list_courses&oid=${encodeURIComponent(AOD_OID)}`).then(x=>x.json()).catch(()=>({}));
    renderCourses(r.courses ?? []);
}

function renderCourses(list) {
    grantedCoursesList = list;
    const tb = document.getElementById('coursesTbody');
    if (!list.length) {
        tb.innerHTML = `<tr><td colspan="6"><div class="aod-empty"><i class="bi bi-collection"></i><p>No course access granted yet.</p></div></td></tr>`;
        return;
    }
    tb.innerHTML = list.map((c, idx) => `
        <tr>
            <td class="ps-4 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="aod-course-icon"><i class="bi bi-play-circle-fill"></i></div>
                    <div class="fw-semibold">${esc(c.title)}</div>
                </div>
            </td>
            <td class="text-muted">${esc(c.granted_by_name||'—')}</td>
            <td class="text-muted">${fmtDate(c.granted_at)}</td>
            <td class="text-muted">${c.expires_at ? fmtDate(c.expires_at) : '<span style="color:#16a34a;font-weight:600">Never</span>'}</td>
            <td>
                <span style="background:${c.is_active?'#dcfce7':'#fee2e2'};color:${c.is_active?'#166534':'#991b1b'};padding:.15rem .55rem;border-radius:100px;font-size:.72rem;font-weight:700">
                    ${c.is_active?'Active':'Revoked'}
                </span>
            </td>
            <td class="pe-4 text-end">
                ${c.is_active ? `<button class="btn btn-sm" style="background:#fee2e2;color:#dc2626;border:none;border-radius:8px;font-size:.76rem;padding:.25rem .6rem" onclick="aodRevokeCourse(${idx})" title="Revoke">
                    <i class="bi bi-x-circle me-1"></i>Revoke
                </button>` : '<span class="text-muted small">—</span>'}
            </td>
        </tr>`).join('');
}

window.aodFilterCourses = function() {
    const q = document.getElementById('courseSearch').value.toLowerCase();
    document.querySelectorAll('#coursesTbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

window.aodRevokeCourse = async function(idx) {
    const c = grantedCoursesList[idx];
    const courseId = c.course_id, title = c.title;
    const conf = await Swal.fire({
        title: 'Revoke Course Access?',
        html: `Members will lose access to <strong>${esc(title)}</strong>.`,
        icon: 'warning', showCancelButton: true,
        confirmButtonText: 'Yes, Revoke', confirmButtonColor: '#dc2626',
    });
    if (!conf.isConfirmed) return;
    const r = await post({action:'revoke_course', oid: AOD_OID, course_id: courseId});
    if (r.status === 'success') { toast('Course access revoked'); coursesLoaded=false; aodLoadCourses(); aodLoadStats(); }
    else toast(r.message||'Error','danger');
}

/* ── Grant Course Modal ──────────────────────────────────────── */
window.aodGrantCourse = async function() {
    if (!allCoursesData.length) {
        const r = await fetch(`${AOD_AJAX}?action=all_courses`).then(x=>x.json()).catch(()=>({}));
        allCoursesData = r.courses ?? [];
    }
    gcFilter();
    bootstrap.Modal.getOrCreateInstance(document.getElementById('grantCourseModal')).show();
}

window.gcFilter = function() {
    const q = document.getElementById('gcSearch')?.value.toLowerCase() || '';
    const filtered = allCoursesData.filter(c => c.title.toLowerCase().includes(q));
    const el = document.getElementById('gcList');
    if (!filtered.length) { el.innerHTML = '<div class="aod-empty" style="padding:1.5rem"><i class="bi bi-search d-block mb-2" style="font-size:1.5rem;opacity:.3"></i><p>No courses found.</p></div>'; return; }
    el.innerHTML = filtered.map(c => `
        <div class="d-flex align-items-center gap-3 p-3 border-bottom" style="cursor:pointer" onclick="document.getElementById('gc_${c.id}').click()">
            <input class="form-check-input flex-shrink-0 mt-0" type="checkbox" value="${c.id}" id="gc_${c.id}" onclick="e=>e.stopPropagation()" onchange="gcCountSelected()">
            <div class="aod-course-icon" style="width:32px;height:32px;font-size:.9rem"><i class="bi bi-play-circle-fill"></i></div>
            <div class="flex-grow-1">
                <div class="fw-semibold" style="font-size:.85rem">${esc(c.title)}</div>
                <div class="text-muted" style="font-size:.74rem">${esc(c.instructor_name||'')} &middot; <span style="text-transform:capitalize">${esc(c.status)}</span></div>
            </div>
        </div>`).join('');
    gcCountSelected();
}

window.gcCountSelected = function() {
    const n = document.querySelectorAll('#gcList input:checked').length;
    const el = document.getElementById('gcSelectedCount');
    if (el) el.textContent = n ? `${n} course${n>1?'s':''} selected` : '';
}

window.gcSave = async function() {
    const ids = [...document.querySelectorAll('#gcList input:checked')].map(i => +i.value);
    if (!ids.length) { toast('Select at least one course','warning'); return; }
    const r = await post({action:'grant_course', oid: AOD_OID, course_ids: ids});
    if (r.status === 'success') {
        toast('Course access granted');
        bootstrap.Modal.getInstance(document.getElementById('grantCourseModal'))?.hide();
        coursesLoaded = false;
        aodLoadCourses();
        aodLoadStats();
    } else toast(r.message||'Error','danger');
}

/* ── Activity Log ────────────────────────────────────────────── */
const actColors = {
    org_created:'#6366f1',org_updated:'#0891b2',member_created:'#059669',member_removed:'#ef4444',
    status_changed:'#f59e0b',course_granted:'#06b6d4',course_revoked:'#f43f5e',
    admin_password_reset:'#8b5cf6',org_admin_reset:'#8b5cf6',
};
const actIcons = {
    org_created:'bi-building-add',org_updated:'bi-pencil-fill',member_created:'bi-person-plus-fill',
    member_removed:'bi-person-dash-fill',status_changed:'bi-toggle-on',course_granted:'bi-collection-fill',
    course_revoked:'bi-x-circle-fill',admin_password_reset:'bi-key-fill',
};

window.aodLoadActivity = async function() {
    activityLoaded = true;
    document.getElementById('activityTimeline').innerHTML = '<div class="aod-empty"><i class="bi bi-hourglass-split"></i><p>Loading…</p></div>';
    const r = await fetch(`${AOD_AJAX}?action=activity_log&oid=${encodeURIComponent(AOD_OID)}`).then(x=>x.json()).catch(()=>({}));
    const logs = r.logs ?? [];
    const tl = document.getElementById('activityTimeline');
    if (!logs.length) { tl.innerHTML = '<div class="aod-empty"><i class="bi bi-clock-history"></i><p>No activity recorded yet.</p></div>'; return; }
    tl.innerHTML = logs.map(l => {
        const col = actColors[l.action] || '#94a3b8';
        const ico = actIcons[l.action]  || 'bi-activity';
        const label = l.action.replace(/_/g,' ');
        return `<div class="aod-tl-item">
            <div class="aod-tl-dot" style="background:${col}15;color:${col}"><i class="bi ${ico}"></i></div>
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="aod-tl-action" style="background:${col}15;color:${col}">${esc(label)}</span>
                    ${l.target_type ? `<span class="text-muted small">${esc(l.target_type)}: <span class="font-monospace">${esc(l.target_id||'')}</span></span>` : ''}
                </div>
                <div class="text-muted mt-1" style="font-size:.76rem">
                    <strong>${esc(l.actor_name||l.actor_usr_code)}</strong>
                    ${l.ip_address ? ` &middot; <span class="font-monospace">${esc(l.ip_address)}</span>` : ''}
                    &middot; ${fmtDateTime(l.created_at)}
                </div>
            </div>
        </div>`;
    }).join('');
}

/* ── Edit Org ────────────────────────────────────────────────── */
window.aodOpenEdit = function() {
    bootstrap.Modal.getOrCreateInstance(document.getElementById('editOrgModal')).show();
}

window.aodSubmitEdit = async function() {
    const btn = document.getElementById('eoSaveBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';
    const r = await post({
        action: 'update', oid: AOD_OID,
        org_name: v('eoOrgName'), org_type: v('eoOrgType'), email: v('eoEmail'),
        phone: v('eoPhone'), country: v('eoCountry'), domain: v('eoDomain'),
        status: v('eoStatus'), license_expires_at: v('eoLicense'),
        max_users: v('eoMaxUsers'), storage_limit_gb: v('eoStorage'),
        address: v('eoAddress'), notes: v('eoNotes'),
    });
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Save Changes';
    if (r.status === 'success') {
        toast('Organization updated');
        bootstrap.Modal.getInstance(document.getElementById('editOrgModal'))?.hide();
        setTimeout(() => location.reload(), 900);
    } else {
        toast(r.message||'Failed to save','danger');
    }
}

/* ── Toggle Status ───────────────────────────────────────────── */
window.aodToggle = async function(newStatus) {
    const conf = await Swal.fire({
        title: newStatus === 'suspended' ? 'Suspend Organization?' : 'Activate Organization?',
        text: newStatus === 'suspended' ? 'Members will lose access immediately.' : 'The organization will be reactivated.',
        icon: newStatus === 'suspended' ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonText: newStatus === 'suspended' ? 'Yes, Suspend' : 'Yes, Activate',
        confirmButtonColor: newStatus === 'suspended' ? '#f59e0b' : '#059669',
    });
    if (!conf.isConfirmed) return;
    const r = await post({action:'toggle_status', oid: AOD_OID, status: newStatus});
    if (r.status === 'success') { toast('Status updated'); setTimeout(() => location.reload(), 900); }
    else toast(r.message||'Error','danger');
}

/* ── Reset Admin Password ────────────────────────────────────── */
window.aodResetAdminPass = async function() {
    if (!AOD_ADMIN_NAME) { toast('No admin assigned to this organization','warning'); return; }
    const conf = await Swal.fire({
        title: 'Reset Admin Password?',
        html: `A new temporary password will be generated for <strong>${esc(AOD_ADMIN_NAME)}</strong>.<br><br>They will be required to change it on first login.${AOD_ADMIN_PHONE ? `<br><small class="text-muted">SMS will be sent to ${esc(AOD_ADMIN_PHONE)}</small>` : ''}`,
        icon: 'warning', showCancelButton: true,
        confirmButtonText: 'Yes, Reset Password', confirmButtonColor: '#8b5cf6',
    });
    if (!conf.isConfirmed) return;
    Swal.fire({ title: 'Resetting…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    const r = await post({action:'reset_admin_password', oid: AOD_OID});
    Swal.close();
    if (r.status !== 'success') { Swal.fire('Error', r.message||'Failed', 'error'); return; }
    const smsLine = r.sms_sent
        ? `<div class="alert alert-success border-0 py-2 small mt-2 text-start"><i class="bi bi-check-circle-fill me-1"></i>Credentials sent via SMS to <strong>${esc(r.phone)}</strong></div>`
        : (r.phone ? `<div class="alert alert-warning border-0 py-2 small mt-2 text-start"><i class="bi bi-exclamation-circle me-1"></i>SMS failed — please share manually</div>` : '');
    Swal.fire({
        icon: 'success', title: 'Password Reset',
        html: `<p class="mb-2">New temporary password for <strong>${esc(r.admin_name)}</strong>:</p>
               <div class="input-group input-group-sm mb-2">
                   <input type="text" class="form-control font-monospace fw-bold text-center fs-5" value="${esc(r.temp_password)}" readonly>
                   <button class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText('${esc(r.temp_password)}').then(()=>this.innerHTML='<i class=\\'bi bi-check\\'></i>')"><i class="bi bi-clipboard"></i></button>
               </div>
               <small class="text-muted d-block">Login: <strong>${esc(r.email)}</strong></small>
               ${smsLine}`,
        confirmButtonText: 'Done', confirmButtonColor: '#6366f1',
    });
}

/* ── Helpers ─────────────────────────────────────────────────── */
const v   = id => document.getElementById(id)?.value ?? '';
const esc = s  => (s+'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
const fmtDate     = s => s ? new Date(s).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : '—';
const fmtDateTime = s => s ? new Date(s).toLocaleString('en-GB',{day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}) : '—';

async function post(data) {
    const fd = new FormData();
    Object.entries(data).forEach(([k,val]) => {
        if (Array.isArray(val)) val.forEach(i => fd.append(k+'[]', i));
        else fd.append(k, val ?? '');
    });
    return fetch(AOD_AJAX, {method:'POST', body:fd}).then(x=>x.json()).catch(()=>({status:'error',message:'Network error'}));
}

function toast(msg, type='success') {
    const colors  = {success:'#059669',danger:'#dc2626',warning:'#d97706',info:'#0891b2'};
    const bgLight = {success:'#f0fdf4',danger:'#fef2f2',warning:'#fffbeb',info:'#f0f9ff'};
    const icons   = {success:'bi-check-circle-fill',danger:'bi-x-circle-fill',warning:'bi-exclamation-triangle-fill',info:'bi-info-circle-fill'};
    const c = document.getElementById('aodToasts') || (() => {
        const el = document.createElement('div');
        el.id = 'aodToasts';
        el.style.cssText = 'position:fixed;bottom:1.25rem;right:1.25rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;pointer-events:none';
        document.body.appendChild(el); return el;
    })();
    const t = document.createElement('div');
    t.style.cssText = `background:${bgLight[type]||bgLight.success};color:${colors[type]||colors.success};padding:.7rem 1rem;border-radius:12px;font-size:.84rem;box-shadow:0 4px 20px rgba(0,0,0,.12);max-width:320px;display:flex;align-items:center;gap:.5rem;border:1px solid ${colors[type]}30;pointer-events:auto`;
    t.innerHTML = `<i class="bi ${icons[type]||icons.success}"></i><span>${esc(msg)}</span>`;
    c.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

aodLoadStats();
</script>
