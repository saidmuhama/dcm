<?php
/* ═══════════════════════════════════════════════════════════
   ADMIN MAIN DASHBOARD — DATA LAYER
═══════════════════════════════════════════════════════════ */

/* ── Core KPIs ──────────────────────────────────────────── */
$s = [];
$s['total_users']        = (int)$db->query("SELECT COUNT(*) AS c FROM tbl_all_users")->fetch_assoc()['c'];
$s['students']           = (int)$db->query("SELECT COUNT(*) AS c FROM tbl_all_users WHERE user_role='1'")->fetch_assoc()['c'];
$s['instructors']        = (int)$db->query("SELECT COUNT(*) AS c FROM tbl_all_users WHERE user_role IN ('3','4')")->fetch_assoc()['c'];
$s['new_users_month']    = (int)$db->query("SELECT COUNT(*) AS c FROM tbl_all_users WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetch_assoc()['c'];
$s['prev_month_users']   = (int)$db->query("SELECT COUNT(*) AS c FROM tbl_all_users WHERE MONTH(created_at)=MONTH(DATE_SUB(NOW(),INTERVAL 1 MONTH)) AND YEAR(created_at)=YEAR(DATE_SUB(NOW(),INTERVAL 1 MONTH))")->fetch_assoc()['c'];
$s['total_courses']      = (int)$db->query("SELECT COUNT(*) AS c FROM tbl_courses WHERE deleted_at IS NULL")->fetch_assoc()['c'];
$s['active_courses']     = (int)$db->query("SELECT COUNT(*) AS c FROM tbl_courses WHERE deleted_at IS NULL AND status='active'")->fetch_assoc()['c'];
$s['approved_courses']   = (int)$db->query("SELECT COUNT(*) AS c FROM tbl_courses WHERE deleted_at IS NULL AND is_approved='approved'")->fetch_assoc()['c'];
$s['pending_approval']   = (int)$db->query("SELECT COUNT(*) AS c FROM tbl_courses WHERE deleted_at IS NULL AND is_approved='pending'")->fetch_assoc()['c'];
$s['rejected_courses']   = (int)$db->query("SELECT COUNT(*) AS c FROM tbl_courses WHERE deleted_at IS NULL AND is_approved='rejected'")->fetch_assoc()['c'];
$s['total_lessons']      = (int)$db->query("SELECT COUNT(*) AS c FROM tbl_course_chapter_lessons")->fetch_assoc()['c'];
$s['total_chapters']     = (int)$db->query("SELECT COUNT(*) AS c FROM tbl_course_chapters")->fetch_assoc()['c'];
$s['study_notes']        = (int)$db->query("SELECT COUNT(*) AS c FROM study_notes")->fetch_assoc()['c'];
$s['discussions']        = (int)$db->query("SELECT COUNT(*) AS c FROM tbl_course_discussions")->fetch_assoc()['c'];
$s['total_revenue']      = (float)$db->query("SELECT COALESCE(SUM(paid_amount),0) AS c FROM tbl_orders WHERE payment_status='PAID'")->fetch_assoc()['c'];
$s['revenue_month']      = (float)$db->query("SELECT COALESCE(SUM(paid_amount),0) AS c FROM tbl_orders WHERE payment_status='PAID' AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetch_assoc()['c'];
$s['revenue_prev_month'] = (float)$db->query("SELECT COALESCE(SUM(paid_amount),0) AS c FROM tbl_orders WHERE payment_status='PAID' AND MONTH(created_at)=MONTH(DATE_SUB(NOW(),INTERVAL 1 MONTH)) AND YEAR(created_at)=YEAR(DATE_SUB(NOW(),INTERVAL 1 MONTH))")->fetch_assoc()['c'];
$s['total_payments']     = (int)$db->query("SELECT COUNT(*) AS c FROM tbl_orders WHERE payment_status='PAID'")->fetch_assoc()['c'];
$s['bookmarks']          = (int)$db->query("SELECT COUNT(*) AS c FROM study_note_bookmarks")->fetch_assoc()['c'];

/* ── Payment method breakdown ───────────────────────────── */
$pm_rows = $db->query("
    SELECT payment_method, COUNT(*) AS cnt
    FROM tbl_orders WHERE payment_status='PAID'
    GROUP BY payment_method ORDER BY cnt DESC
")->fetch_all(MYSQLI_ASSOC);

/* ── Enrollment stats (paid orders = enrollments) ────────── */
$s['total_enrollments']  = (int)$db->query("SELECT COUNT(*) AS c FROM tbl_order_items oi JOIN tbl_orders o ON o.id=oi.order_id WHERE o.payment_status='PAID'")->fetch_assoc()['c'];
$s['active_enrollments'] = $s['total_enrollments']; // all paid items are active enrollments

/* ── QB stats ────────────────────────────────────────────── */
$s['total_questions']    = (int)($db->query("SELECT COUNT(*) AS c FROM qb_questions")->fetch_assoc()['c'] ?? 0);

/* ── Trend helpers ──────────────────────────────────────── */
function trendPct($now, $prev) {
    if ($prev == 0) return $now > 0 ? 100 : 0;
    return round((($now - $prev) / $prev) * 100, 1);
}
$user_trend    = trendPct($s['new_users_month'], $s['prev_month_users']);
$rev_trend     = trendPct($s['revenue_month'],   $s['revenue_prev_month']);

/* ── Content type breakdown ─────────────────────────────── */
$lesson_types_raw = $db->query("SELECT content_type, COUNT(*) AS cnt FROM tbl_course_chapter_lessons GROUP BY content_type ORDER BY cnt DESC")->fetch_all(MYSQLI_ASSOC);
$lesson_types = [];
foreach ($lesson_types_raw as $lt) $lesson_types[$lt['content_type']] = (int)$lt['cnt'];

/* ── Chart: Revenue last 12 months ─────────────────────── */
$rev_rows = $db->query("
    SELECT DATE_FORMAT(created_at,'%b') AS label,
           DATE_FORMAT(created_at,'%Y-%m') AS sort_key,
           SUM(paid_amount) AS amount, COUNT(*) AS txns
    FROM tbl_orders WHERE payment_status='PAID'
      AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY sort_key ORDER BY sort_key ASC
")->fetch_all(MYSQLI_ASSOC);

/* ── Chart: User registrations last 12 months ───────────── */
$user_growth = $db->query("
    SELECT DATE_FORMAT(created_at,'%b') AS label,
           DATE_FORMAT(created_at,'%Y-%m') AS sort_key,
           COUNT(*) AS cnt
    FROM tbl_all_users
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY sort_key ORDER BY sort_key ASC
")->fetch_all(MYSQLI_ASSOC);

/* ── Chart: Role distribution ───────────────────────────── */
$role_map  = ['1'=>'Students','2'=>'Parents','3'=>'Instructors','4'=>'Schools','5'=>'Admins'];
$role_rows = $db->query("SELECT user_role, COUNT(*) AS cnt FROM tbl_all_users GROUP BY user_role ORDER BY cnt DESC")->fetch_all(MYSQLI_ASSOC);

/* ── Chart: Course approval breakdown ──────────────────── */
$approval_data = [
    'Approved' => $s['approved_courses'],
    'Pending'  => $s['pending_approval'],
    'Rejected' => $s['rejected_courses'],
];

/* ── Recent users ───────────────────────────────────────── */
$recent_users = $db->query("
    SELECT first_name, last_name, email_address, user_role, created_at
    FROM tbl_all_users ORDER BY created_at DESC LIMIT 8
")->fetch_all(MYSQLI_ASSOC);

/* ── Recent courses ─────────────────────────────────────── */
$recent_courses = $db->query("
    SELECT c.id, c.title, c.status, c.is_approved, c.price, c.created_at,
           u.first_name, u.last_name,
           (SELECT COUNT(*) FROM tbl_course_chapters ch WHERE ch.course_id=c.id) AS ch_cnt,
           (SELECT COUNT(*) FROM tbl_course_chapter_lessons cl
            LEFT JOIN tbl_course_chapters cc ON cc.id=cl.chapter_id WHERE cc.course_id=c.id) AS ls_cnt
    FROM tbl_courses c
    LEFT JOIN tbl_all_users u ON u.usr_code = c.instructor_id
    WHERE c.deleted_at IS NULL
    ORDER BY c.created_at DESC LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

/* ── Recent payments ────────────────────────────────────── */
$recent_payments = $db->query("
    SELECT o.id, o.paid_amount AS amount, o.payment_method, o.created_at AS payment_date,
           o.payment_status AS status,
           c.title AS course_title,
           u.first_name, u.last_name
    FROM tbl_orders o
    LEFT JOIN tbl_order_items oi ON oi.order_id = o.id
    LEFT JOIN tbl_courses c ON c.id = oi.course_id
    LEFT JOIN tbl_all_users u ON u.usr_code = o.user_id
    WHERE o.payment_status = 'PAID'
    ORDER BY o.created_at DESC LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

/* ── Top courses leaderboard ────────────────────────────── */
$top_courses = $db->query("
    SELECT c.id, c.title, c.status,
           COUNT(DISTINCT sn.id) AS note_count,
           COUNT(DISTINCT d.id)  AS disc_count,
           u.first_name, u.last_name
    FROM tbl_courses c
    LEFT JOIN study_notes sn          ON sn.course_id = c.id
    LEFT JOIN tbl_course_discussions d ON d.course_id = c.id
    LEFT JOIN tbl_all_users u         ON u.usr_code = c.instructor_id
    WHERE c.deleted_at IS NULL
    GROUP BY c.id ORDER BY note_count DESC, disc_count DESC LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

/* ── JS data ─────────────────────────────────────────────── */
$rev_labels  = json_encode(array_column($rev_rows,  'label'));
$rev_amounts = json_encode(array_map(fn($r)=>(float)$r['amount'], $rev_rows));
$rev_txns    = json_encode(array_map(fn($r)=>(int)$r['txns'],     $rev_rows));

$ug_labels = json_encode(array_column($user_growth, 'label'));
$ug_counts = json_encode(array_map(fn($r)=>(int)$r['cnt'], $user_growth));

$role_labels = json_encode(array_map(fn($r)=>$role_map[$r['user_role']]??'Role '.$r['user_role'], $role_rows));
$role_counts = json_encode(array_map(fn($r)=>(int)$r['cnt'], $role_rows));

$ap_labels = json_encode(array_keys($approval_data));
$ap_counts = json_encode(array_values($approval_data));

$pm_labels = json_encode(array_map(fn($r)=>ucfirst($r['payment_method']), $pm_rows));
$pm_counts = json_encode(array_map(fn($r)=>(int)$r['cnt'], $pm_rows));

$hour = (int)date('H');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$today = date('l, d F Y');

/* ── Role pill config ───────────────────────────────────── */
$role_pills = [
    '1'=>['bg'=>'#e0e7ff','color'=>'#3730a3','label'=>'Student'],
    '2'=>['bg'=>'#fce7f3','color'=>'#be185d','label'=>'Parent'],
    '3'=>['bg'=>'#d1fae5','color'=>'#065f46','label'=>'Instructor'],
    '4'=>['bg'=>'#fef3c7','color'=>'#92400e','label'=>'School'],
    '5'=>['bg'=>'#fee2e2','color'=>'#b91c1c','label'=>'Admin'],
];
$avatar_colors = ['#1a4fc4','#7c3aed','#059669','#d97706','#0891b2','#be185d','#dc2626','#0d9488'];
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

<style>
/* ═══════════════════════════════════════════════════════════
   ADMIN DASHBOARD STYLES
═══════════════════════════════════════════════════════════ */
.db-wrap { font-family:'Open Sans',sans-serif; }

/* ── Hero Banner ── */
.db-hero {
  position: relative; overflow: hidden;
  background: linear-gradient(135deg, #0b1120 0%, #0f1e3d 35%, #1a1040 65%, #0d1628 100%);
  border-radius: 20px;
  padding: 2rem 2.2rem;
  margin-bottom: 1.5rem;
  isolation: isolate;
}
.db-hero::before {
  content: '';
  position: absolute; inset: 0; z-index: 0;
  background:
    radial-gradient(circle at 15% 50%, rgba(26,79,196,.35) 0%, transparent 55%),
    radial-gradient(circle at 85% 20%, rgba(109,40,217,.3) 0%, transparent 50%),
    radial-gradient(circle at 60% 80%, rgba(14,165,233,.18) 0%, transparent 45%);
}
.db-hero::after {
  content: '';
  position: absolute; top: -60px; right: -60px; z-index: 0;
  width: 340px; height: 340px; border-radius: 50%;
  border: 1px solid rgba(255,255,255,.04);
  box-shadow: inset 0 0 80px rgba(109,40,217,.12);
}
.db-hero-inner { position: relative; z-index: 1; }
.db-hero-grid { position: absolute; inset: 0; z-index: 0;
  background-image: linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
  background-size: 40px 40px;
}
.db-greeting { font-size: .75rem; color: rgba(255,255,255,.5); font-weight: 500; letter-spacing: .06em; text-transform: uppercase; margin-bottom: .3rem; }
.db-name { font-size: 1.65rem; font-weight: 800; color: #fff; letter-spacing: -.03em; line-height: 1.2; margin-bottom: .25rem; font-family:'SUSE',sans-serif; }
.db-name span { background: linear-gradient(90deg,#60a5fa,#a78bfa); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent; }
.db-date { font-size: .78rem; color: rgba(255,255,255,.45); }
.db-hero-stats { display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1.5rem; }
.db-hero-stat { background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.1); border-radius: 12px;
  padding: .6rem 1rem; backdrop-filter: blur(8px); }
.db-hero-stat-val { font-size: 1.1rem; font-weight: 800; color: #fff; line-height: 1.1; font-family:'SUSE',sans-serif; }
.db-hero-stat-lbl { font-size: .65rem; color: rgba(255,255,255,.5); margin-top: .1rem; text-transform: uppercase; letter-spacing: .05em; }
.db-hero-actions { display: flex; gap: .6rem; flex-wrap: wrap; margin-top: 1.25rem; }
.db-hero-btn {
  display: inline-flex; align-items: center; gap: .4rem;
  padding: .5rem 1.1rem; border-radius: 10px; font-size: .78rem; font-weight: 700;
  text-decoration: none; border: none; cursor: pointer; transition: all .18s;
  font-family: inherit;
}
.db-hero-btn-solid { background: linear-gradient(135deg,#1a4fc4,#6d28d9); color: #fff; box-shadow: 0 4px 16px rgba(26,79,196,.4); }
.db-hero-btn-solid:hover { filter: brightness(1.12); color: #fff; transform: translateY(-1px); }
.db-hero-btn-ghost { background: rgba(255,255,255,.1); color: rgba(255,255,255,.85); border: 1px solid rgba(255,255,255,.15); }
.db-hero-btn-ghost:hover { background: rgba(255,255,255,.18); color: #fff; }
.db-hero-right { text-align: right; }
.db-hero-orb { position: absolute; right: 2rem; top: 50%; transform: translateY(-50%);
  width: 120px; height: 120px; border-radius: 50%;
  background: conic-gradient(from 0deg, #1a4fc4, #6d28d9, #0ea5e9, #1a4fc4);
  opacity: .18; filter: blur(2px); animation: db-orb-spin 12s linear infinite; z-index: 0; }
@keyframes db-orb-spin { to { transform: translateY(-50%) rotate(360deg); } }

/* ── Alert strip ── */
.db-alert {
  display: flex; align-items: center; gap: .85rem; padding: .85rem 1.2rem;
  border-radius: 14px; margin-bottom: 1.2rem;
  border: 1.5px solid;
  animation: db-alert-pulse 3s ease infinite;
}
.db-alert.warn { background: #fffbeb; border-color: #fde68a; }
.db-alert.danger { background: #fff1f2; border-color: #fecaca; }
@keyframes db-alert-pulse { 0%,100%{box-shadow:none} 50%{box-shadow:0 0 0 4px rgba(245,158,11,.1)} }
.db-alert-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
.db-alert.warn .db-alert-icon { background: #fef3c7; color: #d97706; }
.db-alert.danger .db-alert-icon { background: #fee2e2; color: #dc2626; }

/* ── KPI Cards ── */
.kpi-grid { display: grid; grid-template-columns: repeat(6,1fr); gap: .85rem; margin-bottom: 1.5rem; }
@media(max-width:1399px){ .kpi-grid { grid-template-columns: repeat(3,1fr); } }
@media(max-width:767px)  { .kpi-grid { grid-template-columns: repeat(2,1fr); } }

.kpi-card {
  background: #fff; border-radius: 16px; padding: 1.15rem 1.1rem 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.06);
  border: 1px solid #f0f4f8;
  position: relative; overflow: hidden;
  transition: transform .22s ease, box-shadow .22s ease;
  cursor: default;
}
.kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 32px rgba(0,0,0,.11); }
.kpi-card::after {
  content: ''; position: absolute; bottom: 0; left: 0; right: 0;
  height: 3px; border-radius: 0 0 16px 16px;
  background: var(--kpi-accent, linear-gradient(90deg,#1a4fc4,#6d28d9));
  opacity: 0; transition: opacity .22s;
}
.kpi-card:hover::after { opacity: 1; }

.kpi-top { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: .75rem; }
.kpi-icon {
  width: 42px; height: 42px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.15rem; flex-shrink: 0;
  box-shadow: 0 4px 12px var(--kpi-glow, rgba(26,79,196,.2));
}
.kpi-trend {
  display: inline-flex; align-items: center; gap: .2rem;
  font-size: .68rem; font-weight: 700; border-radius: 100px;
  padding: .15rem .5rem;
}
.kpi-trend.up   { background: #dcfce7; color: #15803d; }
.kpi-trend.down { background: #fee2e2; color: #b91c1c; }
.kpi-trend.flat { background: #f1f5f9; color: #64748b; }

.kpi-val { font-size: 1.55rem; font-weight: 800; color: #0f172a; line-height: 1.1; letter-spacing: -.03em; font-family:'SUSE',sans-serif; }
.kpi-label { font-size: .74rem; font-weight: 700; color: #475569; margin-top: .18rem; }
.kpi-sub { font-size: .68rem; color: #94a3b8; margin-top: .15rem; }
.kpi-bar { height: 4px; background: #f0f4f8; border-radius: 100px; margin-top: .85rem; overflow: hidden; }
.kpi-bar-fill { height: 100%; border-radius: 100px; background: var(--kpi-accent, linear-gradient(90deg,#1a4fc4,#6d28d9)); transition: width 1.2s cubic-bezier(.16,1,.3,1); width: 0%; }

/* ── Section card ── */
.db-card {
  background: #fff; border-radius: 16px; padding: 1.4rem 1.4rem 1.2rem;
  box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 4px 16px rgba(0,0,0,.05);
  border: 1px solid #f0f4f8; height: 100%;
}
.db-card-hdr { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.1rem; }
.db-card-title { font-size: .88rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: .5rem; }
.db-card-title i { font-size: 1rem; }
.db-card-sub { font-size: .72rem; color: #94a3b8; margin-top: .1rem; }

/* ── Content type strip ── */
.content-strip { display: flex; gap: .7rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.content-pill {
  display: flex; align-items: center; gap: .55rem;
  padding: .6rem 1rem; border-radius: 12px; border: 1.5px solid;
  font-size: .78rem; font-weight: 700; flex: 1; min-width: 110px;
}
.content-pill-count { font-size: 1.1rem; font-weight: 800; font-family:'SUSE',sans-serif; display: block; line-height: 1; }
.content-pill-label { font-size: .65rem; font-weight: 600; opacity: .75; display: block; margin-top: .05rem; }

/* ── Chart containers ── */
.chart-wrap { position: relative; }
.chart-legend { display: flex; flex-wrap: wrap; gap: .5rem 1rem; margin-top: .85rem; }
.chart-legend-item { display: flex; align-items: center; gap: .35rem; font-size: .72rem; font-weight: 600; color: #475569; }
.chart-legend-dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }

/* ── Stat summary row (under revenue chart) ── */
.rev-stats { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; }
.rev-stat { background: #f8fafc; border-radius: 10px; padding: .7rem .85rem; border: 1px solid #f0f4f8; }
.rev-stat-val { font-size: 1.05rem; font-weight: 800; color: #0f172a; font-family:'SUSE',sans-serif; }
.rev-stat-lbl { font-size: .67rem; color: #94a3b8; margin-top: .1rem; font-weight: 600; }

/* ── Donut chart with center label ── */
.donut-wrap { position: relative; }
.donut-center { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); text-align: center; pointer-events: none; }
.donut-center-val { font-size: 1.4rem; font-weight: 800; color: #0f172a; font-family:'SUSE',sans-serif; line-height: 1; }
.donut-center-lbl { font-size: .63rem; color: #94a3b8; font-weight: 600; margin-top: .1rem; }

/* ── User list ── */
.user-list-item {
  display: flex; align-items: center; gap: .85rem; padding: .7rem 0;
  border-bottom: 1px solid #f4f7fb; transition: background .15s;
}
.user-list-item:last-child { border-bottom: none; padding-bottom: 0; }
.user-avatar {
  width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: .72rem; font-weight: 800; color: #fff; letter-spacing: -.02em;
}
.user-name { font-size: .82rem; font-weight: 700; color: #1e293b; }
.user-email { font-size: .68rem; color: #94a3b8; }

/* ── Course table rows ── */
.course-row {
  display: flex; align-items: center; gap: .85rem; padding: .75rem 0;
  border-bottom: 1px solid #f4f7fb;
}
.course-row:last-child { border-bottom: none; padding-bottom: 0; }
.course-thumb {
  width: 44px; height: 44px; border-radius: 10px; flex-shrink: 0;
  background: linear-gradient(135deg,#e0e7ff,#ede9fe);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem; color: #6d28d9;
}
.course-title { font-size: .82rem; font-weight: 700; color: #1e293b; line-height: 1.3; }
.course-meta { font-size: .68rem; color: #94a3b8; }

/* ── Payment feed ── */
.pay-item {
  display: flex; align-items: center; gap: .85rem; padding: .7rem 0;
  border-bottom: 1px solid #f4f7fb;
}
.pay-item:last-child { border-bottom: none; padding-bottom: 0; }
.pay-icon { width: 38px; height: 38px; border-radius: 11px; display: flex; align-items: center; justify-content: center; font-size: .95rem; flex-shrink: 0; }
.pay-course { font-size: .8rem; font-weight: 700; color: #1e293b; }
.pay-meta { font-size: .67rem; color: #94a3b8; }
.pay-amount { font-size: .88rem; font-weight: 800; white-space: nowrap; font-family:'SUSE',sans-serif; }

/* ── Leaderboard ── */
.lb-item {
  display: flex; align-items: center; gap: .9rem; padding: .8rem 0;
  border-bottom: 1px solid #f4f7fb;
}
.lb-item:last-child { border-bottom: none; padding-bottom: 0; }
.lb-rank {
  width: 28px; height: 28px; border-radius: 8px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: .72rem; font-weight: 800; font-family:'SUSE',sans-serif;
}
.lb-rank.r1 { background: linear-gradient(135deg,#f59e0b,#d97706); color: #fff; }
.lb-rank.r2 { background: linear-gradient(135deg,#94a3b8,#64748b); color: #fff; }
.lb-rank.r3 { background: linear-gradient(135deg,#b45309,#92400e); color: #fff; }
.lb-rank.rn { background: #f0f4f8; color: #64748b; }
.lb-bar-wrap { flex: 1; }
.lb-bar-track { height: 5px; background: #f0f4f8; border-radius: 100px; margin-top: .35rem; overflow: hidden; }
.lb-bar-fill  { height: 100%; border-radius: 100px; transition: width 1.2s cubic-bezier(.16,1,.3,1); width: 0%; }

/* ── Status pills ── */
.st-pill { display: inline-block; border-radius: 100px; padding: .15rem .6rem; font-size: .67rem; font-weight: 700; }

/* ── View-all btn ── */
.db-view-all {
  font-size: .72rem; font-weight: 700; color: #1a4fc4; text-decoration: none;
  display: inline-flex; align-items: center; gap: .25rem;
  padding: .3rem .7rem; border-radius: 8px; background: #eff6ff;
  transition: background .15s, color .15s;
}
.db-view-all:hover { background: #1a4fc4; color: #fff; }

/* ── Count-up ── */
.count-up { display: inline-block; }

/* ── Animations ── */
@keyframes db-fade-up {
  from { opacity: 0; transform: translateY(18px); }
  to   { opacity: 1; transform: translateY(0); }
}
.db-anim { opacity: 0; }
.db-anim.visible { animation: db-fade-up .5s cubic-bezier(.16,1,.3,1) both; }
.db-anim-d1 { animation-delay: .05s; }
.db-anim-d2 { animation-delay: .1s; }
.db-anim-d3 { animation-delay: .15s; }
.db-anim-d4 { animation-delay: .2s; }
.db-anim-d5 { animation-delay: .25s; }
.db-anim-d6 { animation-delay: .3s; }
</style>

<div class="container-fluid px-3 py-3 db-wrap">

<!-- ═══════════════════════════════════════════════════════════
     HERO BANNER
═══════════════════════════════════════════════════════════ -->
<div class="db-hero db-anim visible">
  <div class="db-hero-grid"></div>
  <div class="db-orb" style="position:absolute;right:3rem;top:50%;transform:translateY(-50%);width:160px;height:160px;border-radius:50%;background:conic-gradient(from 0deg,rgba(26,79,196,.4),rgba(109,40,217,.4),rgba(14,165,233,.3),rgba(26,79,196,.4));filter:blur(30px);opacity:.6;animation:db-orb-spin 14s linear infinite;z-index:0"></div>
  <div class="db-hero-inner">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <div class="db-greeting"><i class="bi bi-shield-fill-check me-1" style="color:#60a5fa"></i>Super Administrator</div>
        <div class="db-name"><?= $greeting ?>, <span>Admin!</span></div>
        <div class="db-date"><i class="bi bi-calendar3 me-1 opacity-60"></i><?= $today ?></div>
        <div class="db-hero-stats">
          <div class="db-hero-stat">
            <div class="db-hero-stat-val"><?= number_format($s['total_users']) ?></div>
            <div class="db-hero-stat-lbl">Total Users</div>
          </div>
          <div class="db-hero-stat">
            <div class="db-hero-stat-val"><?= number_format($s['total_courses']) ?></div>
            <div class="db-hero-stat-lbl">Courses</div>
          </div>
          <div class="db-hero-stat">
            <div class="db-hero-stat-val">TZS <?= $s['total_revenue'] >= 1000000 ? number_format($s['total_revenue']/1000000,1).'M' : number_format($s['total_revenue']) ?></div>
            <div class="db-hero-stat-lbl">Total Revenue</div>
          </div>
          <?php if ($s['total_enrollments'] > 0): ?>
          <div class="db-hero-stat">
            <div class="db-hero-stat-val"><?= number_format($s['total_enrollments']) ?></div>
            <div class="db-hero-stat-lbl">Enrollments</div>
          </div>
          <?php endif; ?>
          <?php if ($s['total_questions'] > 0): ?>
          <div class="db-hero-stat">
            <div class="db-hero-stat-val"><?= number_format($s['total_questions']) ?></div>
            <div class="db-hero-stat-lbl">QB Questions</div>
          </div>
          <?php endif; ?>
          <?php if ($s['pending_approval'] > 0): ?>
          <div class="db-hero-stat" style="border-color:rgba(245,158,11,.4);background:rgba(245,158,11,.12)">
            <div class="db-hero-stat-val" style="color:#fbbf24"><?= $s['pending_approval'] ?></div>
            <div class="db-hero-stat-lbl" style="color:rgba(251,191,36,.7)">Pending Review</div>
          </div>
          <?php endif; ?>
        </div>
        <div class="db-hero-actions">
          <a href="?view=admin_users" class="db-hero-btn db-hero-btn-solid"><i class="bi bi-people-fill"></i>Manage Users</a>
          <a href="?view=admin_courses" class="db-hero-btn db-hero-btn-ghost"><i class="bi bi-collection-play-fill"></i>All Courses</a>
          <a href="?view=admin_permissions" class="db-hero-btn db-hero-btn-ghost"><i class="bi bi-shield-check"></i>Permissions</a>
        </div>
      </div>
      <div class="col-lg-5 d-none d-lg-flex justify-content-end align-items-center" style="gap:1rem">
        <!-- Mini platform vitals panel -->
        <div style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:1.1rem 1.3rem;min-width:200px;backdrop-filter:blur(10px)">
          <div style="font-size:.67rem;color:rgba(255,255,255,.4);font-weight:700;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.85rem">Platform Health</div>
          <?php
          $vitals = [
            ['label'=>'Content Coverage',    'pct'=>$s['total_lessons']>0?min(100,round(($s['study_notes']/$s['total_lessons'])*100)):0,    'color'=>'#60a5fa'],
            ['label'=>'Course Approval Rate','pct'=>$s['total_courses']>0?round(($s['approved_courses']/$s['total_courses'])*100):0,         'color'=>'#34d399'],
            ['label'=>'Active Enrollments',  'pct'=>$s['total_enrollments']>0?round(($s['active_enrollments']/$s['total_enrollments'])*100):0,'color'=>'#a78bfa'],
            ['label'=>'Active Courses',      'pct'=>$s['total_courses']>0?round(($s['active_courses']/$s['total_courses'])*100):0,           'color'=>'#fb923c'],
          ];
          foreach ($vitals as $v): ?>
          <div style="margin-bottom:.7rem">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.3rem">
              <span style="font-size:.7rem;color:rgba(255,255,255,.55);font-weight:600"><?= $v['label'] ?></span>
              <span style="font-size:.7rem;font-weight:800;color:<?= $v['color'] ?>"><?= $v['pct'] ?>%</span>
            </div>
            <div style="height:4px;background:rgba(255,255,255,.08);border-radius:100px;overflow:hidden">
              <div style="width:<?= $v['pct'] ?>%;height:100%;background:<?= $v['color'] ?>;border-radius:100px;transition:width 1.4s cubic-bezier(.16,1,.3,1)"></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     ALERT STRIP
═══════════════════════════════════════════════════════════ -->
<?php if ($s['pending_approval'] > 0): ?>
<div class="db-alert warn db-anim visible">
  <div class="db-alert-icon"><i class="bi bi-hourglass-split"></i></div>
  <div class="flex-grow-1">
    <div style="font-size:.84rem;font-weight:700;color:#92400e">
      <?= $s['pending_approval'] ?> course<?= $s['pending_approval']>1?'s':'' ?> awaiting your approval
    </div>
    <div style="font-size:.72rem;color:#b45309;margin-top:.1rem">Review and approve or reject pending submissions to keep your platform running smoothly.</div>
  </div>
  <a href="?view=admin_courses" class="db-hero-btn db-hero-btn-solid" style="font-size:.74rem;padding:.4rem .9rem;background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 4px 12px rgba(245,158,11,.35)">
    <i class="bi bi-arrow-right-circle-fill"></i>Review Now
  </a>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════
     KPI CARDS
═══════════════════════════════════════════════════════════ -->
<?php
$kpis = [
  [
    'icon'=>'bi-people-fill', 'grad'=>'linear-gradient(135deg,#1a4fc4,#6d28d9)', 'glow'=>'rgba(26,79,196,.25)',
    'accent'=>'linear-gradient(90deg,#1a4fc4,#6d28d9)',
    'val'=>$s['total_users'], 'fmt'=>'num', 'label'=>'Total Users',
    'sub'=>$s['new_users_month'].' new this month',
    'trend'=>$user_trend, 'bar_pct'=>min(100,round($s['students']/$s['total_users']*100))
  ],
  [
    'icon'=>'bi-mortarboard-fill', 'grad'=>'linear-gradient(135deg,#7c3aed,#a855f7)', 'glow'=>'rgba(124,58,237,.25)',
    'accent'=>'linear-gradient(90deg,#7c3aed,#a855f7)',
    'val'=>$s['students'], 'fmt'=>'num', 'label'=>'Students',
    'sub'=>$s['instructors'].' instructors & schools',
    'trend'=>null, 'bar_pct'=>$s['total_users']>0?round($s['students']/$s['total_users']*100):0
  ],
  [
    'icon'=>'bi-collection-play-fill', 'grad'=>'linear-gradient(135deg,#059669,#0d9488)', 'glow'=>'rgba(5,150,105,.25)',
    'accent'=>'linear-gradient(90deg,#059669,#0d9488)',
    'val'=>$s['total_courses'], 'fmt'=>'num', 'label'=>'Total Courses',
    'sub'=>$s['active_courses'].' active · '.$s['approved_courses'].' approved',
    'trend'=>null, 'bar_pct'=>$s['total_courses']>0?round($s['active_courses']/$s['total_courses']*100):0
  ],
  [
    'icon'=>'bi-play-circle-fill', 'grad'=>'linear-gradient(135deg,#0891b2,#0ea5e9)', 'glow'=>'rgba(8,145,178,.25)',
    'accent'=>'linear-gradient(90deg,#0891b2,#0ea5e9)',
    'val'=>$s['total_lessons'], 'fmt'=>'num', 'label'=>'Total Lessons',
    'sub'=>$s['total_chapters'].' chapters · '.$s['study_notes'].' notes',
    'trend'=>null, 'bar_pct'=>min(100, $s['total_lessons']>0?60:0)
  ],
  [
    'icon'=>'bi-cash-coin', 'grad'=>'linear-gradient(135deg,#d97706,#f59e0b)', 'glow'=>'rgba(217,119,6,.25)',
    'accent'=>'linear-gradient(90deg,#d97706,#f59e0b)',
    'val'=>$s['total_revenue'], 'fmt'=>'currency', 'label'=>'Total Revenue',
    'sub'=>$s['total_payments'].' transactions · TZS '.number_format($s['revenue_month']).' this month',
    'trend'=>$rev_trend, 'bar_pct'=>75
  ],
  [
    'icon'=>'bi-clock-history', 'grad'=>'linear-gradient(135deg,#be185d,#ec4899)', 'glow'=>'rgba(190,24,93,.25)',
    'accent'=>'linear-gradient(90deg,#be185d,#ec4899)',
    'val'=>$s['pending_approval'], 'fmt'=>'num', 'label'=>'Pending Approval',
    'sub'=>$s['rejected_courses'].' rejected · needs action',
    'trend'=>null, 'bar_pct'=>$s['total_courses']>0?round($s['pending_approval']/$s['total_courses']*100):0
  ],
];
?>
<div class="kpi-grid">
<?php foreach ($kpis as $i => $k):
  $trend_html = '';
  if ($k['trend'] !== null) {
    if ($k['trend'] > 0) $trend_html = '<span class="kpi-trend up"><i class="bi bi-arrow-up-short"></i>'.abs($k['trend']).'%</span>';
    elseif ($k['trend'] < 0) $trend_html = '<span class="kpi-trend down"><i class="bi bi-arrow-down-short"></i>'.abs($k['trend']).'%</span>';
    else $trend_html = '<span class="kpi-trend flat">—</span>';
  }
  $display_val = $k['fmt']==='currency' ? 'TZS '.($k['val']>=1000000?number_format($k['val']/1000000,1).'M':number_format($k['val'])) : number_format($k['val']);
?>
<div class="kpi-card db-anim db-anim-d<?= $i+1 ?>" style="--kpi-accent:<?= $k['accent'] ?>;--kpi-glow:<?= $k['glow'] ?>">
  <div class="kpi-top">
    <div class="kpi-icon" style="background:<?= $k['grad'] ?>;box-shadow:0 4px 14px <?= $k['glow'] ?>">
      <i class="bi <?= $k['icon'] ?>" style="color:#fff;font-size:1.1rem"></i>
    </div>
    <?= $trend_html ?>
  </div>
  <div class="kpi-val count-up" data-target="<?= $k['val'] ?>" data-prefix="<?= $k['fmt']==='currency'?'TZS ':'' ?>" data-suffix="" data-currency="<?= $k['fmt']==='currency'?'1':'' ?>"><?= $display_val ?></div>
  <div class="kpi-label"><?= $k['label'] ?></div>
  <div class="kpi-sub"><?= $k['sub'] ?></div>
  <div class="kpi-bar"><div class="kpi-bar-fill" data-pct="<?= $k['bar_pct'] ?>"></div></div>
</div>
<?php endforeach; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════
     CONTENT TYPE STRIP
═══════════════════════════════════════════════════════════ -->
<?php
$ct_config = [
  'video' => ['icon'=>'bi-play-circle-fill','grad'=>'linear-gradient(135deg,#dc2626,#ef4444)','border'=>'#fecaca','bg'=>'#fff5f5','color'=>'#dc2626','label'=>'Video Lessons'],
  'audio' => ['icon'=>'bi-music-note-beamed','grad'=>'linear-gradient(135deg,#d97706,#f59e0b)','border'=>'#fde68a','bg'=>'#fffbeb','color'=>'#d97706','label'=>'Audio Lessons'],
  'pdf'   => ['icon'=>'bi-file-pdf-fill',    'grad'=>'linear-gradient(135deg,#b91c1c,#dc2626)','border'=>'#fecaca','bg'=>'#fff5f5','color'=>'#b91c1c','label'=>'PDF Documents'],
  'live'  => ['icon'=>'bi-broadcast-fill',   'grad'=>'linear-gradient(135deg,#059669,#16a34a)','border'=>'#bbf7d0','bg'=>'#f0fdf4','color'=>'#059669','label'=>'Live Sessions'],
  'ppt'   => ['icon'=>'bi-file-earmark-slides-fill','grad'=>'linear-gradient(135deg,#ea580c,#f97316)','border'=>'#fdba74','bg'=>'#fff7ed','color'=>'#ea580c','label'=>'Presentations'],
];
$total_typed = array_sum($lesson_types);
?>
<div class="content-strip db-anim db-anim-d1">
  <?php foreach ($ct_config as $type => $cfg):
    $count = $lesson_types[$type] ?? 0;
    $pct   = $total_typed > 0 ? round($count/$total_typed*100) : 0;
  ?>
  <div class="content-pill" style="background:<?= $cfg['bg'] ?>;border-color:<?= $cfg['border'] ?>;color:<?= $cfg['color'] ?>">
    <div style="width:36px;height:36px;border-radius:10px;background:<?= $cfg['grad'] ?>;display:flex;align-items:center;justify-content:center;font-size:.95rem;color:#fff;flex-shrink:0;box-shadow:0 3px 10px rgba(0,0,0,.15)">
      <i class="bi <?= $cfg['icon'] ?>"></i>
    </div>
    <div>
      <span class="content-pill-count"><?= number_format($count) ?></span>
      <span class="content-pill-label"><?= $cfg['label'] ?> <?= $pct>0?"($pct%)":''; ?></span>
    </div>
  </div>
  <?php endforeach; ?>
  <!-- Discussions pill -->
  <div class="content-pill" style="background:#f5f3ff;border-color:#ddd6fe;color:#7c3aed">
    <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#7c3aed,#a855f7);display:flex;align-items:center;justify-content:center;font-size:.95rem;color:#fff;flex-shrink:0;box-shadow:0 3px 10px rgba(124,58,237,.2)">
      <i class="bi bi-chat-left-dots-fill"></i>
    </div>
    <div>
      <span class="content-pill-count"><?= number_format($s['discussions']) ?></span>
      <span class="content-pill-label">Discussions</span>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     ROW 1: Revenue chart + Course Approval donut
═══════════════════════════════════════════════════════════ -->
<div class="row g-3 mb-3">

  <div class="col-xl-8 db-anim db-anim-d1">
    <div class="db-card">
      <div class="db-card-hdr">
        <div>
          <div class="db-card-title"><i class="bi bi-bar-chart-line-fill" style="color:#1a4fc4"></i>Revenue Overview</div>
          <div class="db-card-sub">Last 12 months · collected payments (TZS)</div>
        </div>
        <div class="d-flex align-items-center gap-2">
          <div style="text-align:right">
            <div style="font-size:1.1rem;font-weight:800;color:#0f172a;font-family:'SUSE',sans-serif">TZS <?= number_format($s['total_revenue']) ?></div>
            <div style="font-size:.68rem;color:#94a3b8">lifetime revenue</div>
          </div>
          <?php if ($rev_trend > 0): ?>
          <span class="kpi-trend up"><i class="bi bi-arrow-up-short"></i><?= $rev_trend ?>%</span>
          <?php elseif ($rev_trend < 0): ?>
          <span class="kpi-trend down"><i class="bi bi-arrow-down-short"></i><?= abs($rev_trend) ?>%</span>
          <?php endif; ?>
        </div>
      </div>
      <div class="chart-wrap" style="height:230px">
        <canvas id="chartRevenue"></canvas>
      </div>
      <div class="rev-stats mt-3">
        <div class="rev-stat">
          <div class="rev-stat-val">TZS <?= number_format($s['revenue_month']) ?></div>
          <div class="rev-stat-lbl">This Month</div>
        </div>
        <div class="rev-stat">
          <div class="rev-stat-val"><?= $s['total_payments'] ?></div>
          <div class="rev-stat-lbl">Total Transactions</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-4 db-anim db-anim-d2">
    <div class="db-card">
      <div class="db-card-hdr">
        <div>
          <div class="db-card-title"><i class="bi bi-patch-check-fill" style="color:#059669"></i>Course Approvals</div>
          <div class="db-card-sub">Approval status breakdown</div>
        </div>
      </div>
      <div class="donut-wrap" style="height:200px">
        <canvas id="chartApproval"></canvas>
        <div class="donut-center">
          <div class="donut-center-val"><?= $s['total_courses'] ?></div>
          <div class="donut-center-lbl">Total</div>
        </div>
      </div>
      <div style="margin-top:1rem;display:flex;flex-direction:column;gap:.5rem">
        <?php
        $ap_cfg = [
          ['label'=>'Approved','val'=>$s['approved_courses'],'color'=>'#059669','bg'=>'#d1fae5'],
          ['label'=>'Pending', 'val'=>$s['pending_approval'],'color'=>'#d97706','bg'=>'#fef3c7'],
          ['label'=>'Rejected','val'=>$s['rejected_courses'],'color'=>'#dc2626','bg'=>'#fee2e2'],
        ];
        foreach ($ap_cfg as $ap): $pct_ap = $s['total_courses']>0?round($ap['val']/$s['total_courses']*100):0; ?>
        <div style="display:flex;align-items:center;gap:.65rem">
          <div style="width:10px;height:10px;border-radius:3px;background:<?= $ap['color'] ?>;flex-shrink:0"></div>
          <div style="flex:1;font-size:.76rem;font-weight:600;color:#475569"><?= $ap['label'] ?></div>
          <div style="font-size:.76rem;font-weight:800;color:#0f172a"><?= $ap['val'] ?></div>
          <div style="font-size:.68rem;color:#94a3b8;width:32px;text-align:right"><?= $pct_ap ?>%</div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

</div>

<!-- ═══════════════════════════════════════════════════════════
     ROW 2: User Growth + Role Distribution + Pay Methods
═══════════════════════════════════════════════════════════ -->
<div class="row g-3 mb-3">

  <div class="col-lg-6 db-anim db-anim-d1">
    <div class="db-card">
      <div class="db-card-hdr">
        <div>
          <div class="db-card-title"><i class="bi bi-person-lines-fill" style="color:#7c3aed"></i>User Growth</div>
          <div class="db-card-sub">New registrations · last 12 months</div>
        </div>
        <span style="background:#f5f3ff;color:#7c3aed;border-radius:100px;padding:.2rem .7rem;font-size:.72rem;font-weight:700">
          +<?= $s['new_users_month'] ?> this month
        </span>
      </div>
      <div class="chart-wrap" style="height:200px">
        <canvas id="chartUserGrowth"></canvas>
      </div>
    </div>
  </div>

  <div class="col-lg-3 db-anim db-anim-d2">
    <div class="db-card">
      <div class="db-card-hdr">
        <div>
          <div class="db-card-title"><i class="bi bi-people-fill" style="color:#0891b2"></i>User Roles</div>
          <div class="db-card-sub">Distribution</div>
        </div>
      </div>
      <div class="donut-wrap" style="height:160px">
        <canvas id="chartRoles"></canvas>
        <div class="donut-center">
          <div class="donut-center-val"><?= $s['total_users'] ?></div>
          <div class="donut-center-lbl">Users</div>
        </div>
      </div>
      <div id="roleLegend" class="chart-legend mt-3"></div>
    </div>
  </div>

  <div class="col-lg-3 db-anim db-anim-d3">
    <div class="db-card">
      <div class="db-card-hdr">
        <div>
          <div class="db-card-title"><i class="bi bi-wallet2" style="color:#be185d"></i>Pay Methods</div>
          <div class="db-card-sub">By transactions</div>
        </div>
      </div>
      <div class="donut-wrap" style="height:160px">
        <canvas id="chartPayMethods"></canvas>
        <div class="donut-center">
          <div class="donut-center-val"><?= $s['total_payments'] ?></div>
          <div class="donut-center-lbl">Txns</div>
        </div>
      </div>
      <div id="pmLegend" class="chart-legend mt-3"></div>
    </div>
  </div>

</div>

<!-- ═══════════════════════════════════════════════════════════
     ROW 3: Recent Users + Recent Courses
═══════════════════════════════════════════════════════════ -->
<div class="row g-3 mb-3">

  <div class="col-lg-6 db-anim db-anim-d1">
    <div class="db-card">
      <div class="db-card-hdr">
        <div>
          <div class="db-card-title"><i class="bi bi-person-plus-fill" style="color:#1a4fc4"></i>Recent Users</div>
          <div class="db-card-sub">Latest registrations</div>
        </div>
        <a href="?view=admin_users" class="db-view-all"><i class="bi bi-arrow-right"></i>View All</a>
      </div>
      <?php
      $av_colors = ['#1a4fc4','#7c3aed','#059669','#d97706','#0891b2','#be185d','#dc2626','#0d9488'];
      foreach ($recent_users as $ui => $u):
        $rp = $role_pills[$u['user_role']] ?? ['bg'=>'#f1f5f9','color'=>'#475569','label'=>'User'];
        $initials = strtoupper(substr($u['first_name'],0,1).substr($u['last_name'],0,1));
        $avc = $av_colors[$ui % count($av_colors)];
      ?>
      <div class="user-list-item">
        <div class="user-avatar" style="background:<?= $avc ?>"><?= $initials ?></div>
        <div class="flex-grow-1 min-w-0">
          <div class="user-name"><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></div>
          <div class="user-email"><?= htmlspecialchars($u['email_address']) ?></div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.3rem;flex-shrink:0">
          <span class="st-pill" style="background:<?= $rp['bg'] ?>;color:<?= $rp['color'] ?>"><?= $rp['label'] ?></span>
          <span style="font-size:.65rem;color:#94a3b8"><?= date('d M', strtotime($u['created_at'])) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="col-lg-6 db-anim db-anim-d2">
    <div class="db-card">
      <div class="db-card-hdr">
        <div>
          <div class="db-card-title"><i class="bi bi-collection-play-fill" style="color:#059669"></i>Recent Courses</div>
          <div class="db-card-sub">Newest additions</div>
        </div>
        <a href="?view=admin_courses" class="db-view-all"><i class="bi bi-arrow-right"></i>View All</a>
      </div>
      <?php
      $cs_styles = [
        'active'   => ['bg'=>'#dcfce7','color'=>'#15803d','label'=>'Active'],
        'is_draft' => ['bg'=>'#fef9c3','color'=>'#92400e','label'=>'Draft'],
        'inactive' => ['bg'=>'#f1f5f9','color'=>'#475569','label'=>'Inactive'],
      ];
      $ap_styles = [
        'approved' => ['bg'=>'#dcfce7','color'=>'#15803d'],
        'pending'  => ['bg'=>'#fef9c3','color'=>'#92400e'],
        'rejected' => ['bg'=>'#fee2e2','color'=>'#b91c1c'],
      ];
      $ct_icons = ['video'=>'bi-play-circle-fill','audio'=>'bi-music-note-beamed','pdf'=>'bi-file-pdf-fill','live'=>'bi-broadcast-fill','ppt'=>'bi-file-earmark-slides-fill'];
      foreach ($recent_courses as $c):
        $css = $cs_styles[$c['status']] ?? ['bg'=>'#f1f5f9','color'=>'#475569','label'=>ucfirst($c['status'])];
        $aps = $ap_styles[$c['is_approved']] ?? ['bg'=>'#f1f5f9','color'=>'#475569'];
      ?>
      <div class="course-row">
        <div class="course-thumb">
          <i class="bi bi-collection-play"></i>
        </div>
        <div class="flex-grow-1 min-w-0">
          <div class="course-title text-truncate" style="max-width:220px"><?= htmlspecialchars($c['title']) ?></div>
          <div class="course-meta"><?= htmlspecialchars($c['first_name'].' '.$c['last_name']) ?> · <?= $c['ch_cnt'] ?> chapters · <?= $c['ls_cnt'] ?> lessons</div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.3rem;flex-shrink:0">
          <span class="st-pill" style="background:<?= $css['bg'] ?>;color:<?= $css['color'] ?>"><?= $css['label'] ?></span>
          <span class="st-pill" style="background:<?= $aps['bg'] ?>;color:<?= $aps['color'] ?>"><?= ucfirst($c['is_approved']) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (!$recent_courses): ?>
      <div style="text-align:center;padding:2.5rem 1rem;color:#94a3b8">
        <i class="bi bi-collection" style="font-size:2rem;display:block;margin-bottom:.5rem;color:#e2e8f0"></i>
        No courses yet
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- ═══════════════════════════════════════════════════════════
     ROW 4: Payment Feed + Study Notes Leaderboard
═══════════════════════════════════════════════════════════ -->
<div class="row g-3 mb-3">

  <div class="col-lg-5 db-anim db-anim-d1">
    <div class="db-card">
      <div class="db-card-hdr">
        <div>
          <div class="db-card-title"><i class="bi bi-lightning-charge-fill" style="color:#d97706"></i>Recent Payments</div>
          <div class="db-card-sub">Latest successful transactions</div>
        </div>
        <div style="background:#d1fae5;color:#065f46;border-radius:8px;padding:.3rem .7rem;font-size:.7rem;font-weight:800">
          TZS <?= number_format($s['revenue_month']) ?> / month
        </div>
      </div>
      <?php if (!$recent_payments): ?>
      <div style="text-align:center;padding:3rem 1rem;color:#94a3b8">
        <i class="bi bi-credit-card" style="font-size:2.5rem;display:block;margin-bottom:.6rem;color:#e2e8f0"></i>
        <div style="font-weight:600;font-size:.85rem;color:#64748b">No payments yet</div>
        <div style="font-size:.74rem;margin-top:.25rem">Payments will appear here once students enrol</div>
      </div>
      <?php else: ?>
      <?php
      $pm_cfg = [
        'mobile' => ['icon'=>'bi-phone-fill',    'bg'=>'#ede9fe','color'=>'#7c3aed','grad'=>'linear-gradient(135deg,#7c3aed,#a855f7)'],
        'card'   => ['icon'=>'bi-credit-card-fill','bg'=>'#e0f2fe','color'=>'#0891b2','grad'=>'linear-gradient(135deg,#0891b2,#0ea5e9)'],
        'bank'   => ['icon'=>'bi-bank',           'bg'=>'#d1fae5','color'=>'#059669','grad'=>'linear-gradient(135deg,#059669,#16a34a)'],
      ];
      foreach ($recent_payments as $p):
        $pmc = $pm_cfg[$p['payment_method']] ?? ['icon'=>'bi-cash','bg'=>'#f1f5f9','color'=>'#64748b','grad'=>'linear-gradient(135deg,#64748b,#475569)'];
        $payer = trim(($p['first_name']??'').' '.($p['last_name']??'')) ?: 'Anonymous';
      ?>
      <div class="pay-item">
        <div class="pay-icon" style="background:<?= $pmc['bg'] ?>;color:<?= $pmc['color'] ?>">
          <i class="bi <?= $pmc['icon'] ?>"></i>
        </div>
        <div class="flex-grow-1 min-w-0">
          <div class="pay-course text-truncate" style="max-width:180px"><?= htmlspecialchars($p['course_title'] ?? 'General Payment') ?></div>
          <div class="pay-meta"><?= htmlspecialchars($payer) ?> · <?= ucfirst($p['payment_method']) ?> · <?= date('d M Y', strtotime($p['payment_date'])) ?></div>
        </div>
        <div class="pay-amount" style="color:#059669">TZS <?= number_format($p['amount']) ?></div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-7 db-anim db-anim-d2">
    <div class="db-card">
      <div class="db-card-hdr">
        <div>
          <div class="db-card-title"><i class="bi bi-trophy-fill" style="color:#f59e0b"></i>Course Leaderboard</div>
          <div class="db-card-sub">Ranked by study notes &amp; discussions</div>
        </div>
      </div>
      <?php if (!$top_courses): ?>
      <div style="text-align:center;padding:2.5rem 1rem;color:#94a3b8">
        <i class="bi bi-journal" style="font-size:2rem;display:block;margin-bottom:.5rem;color:#e2e8f0"></i>
        No data yet
      </div>
      <?php else: ?>
      <?php
      $max_notes = max(array_column($top_courses,'note_count')) ?: 1;
      $rank_cls = ['r1','r2','r3','rn','rn','rn'];
      $rank_gradients = [
        'linear-gradient(135deg,#1a4fc4,#6d28d9)',
        'linear-gradient(135deg,#7c3aed,#a855f7)',
        'linear-gradient(135deg,#059669,#0d9488)',
        'linear-gradient(135deg,#0891b2,#0ea5e9)',
        'linear-gradient(135deg,#d97706,#f59e0b)',
        'linear-gradient(135deg,#be185d,#ec4899)',
      ];
      foreach ($top_courses as $ri => $tc):
        $pct_lb = $max_notes > 0 ? round(($tc['note_count']/$max_notes)*100) : 0;
      ?>
      <div class="lb-item">
        <div class="lb-rank <?= $rank_cls[$ri] ?? 'rn' ?>"><?= $ri+1 ?></div>
        <div class="lb-bar-wrap" style="flex:1;min-width:0">
          <div style="display:flex;align-items:center;justify-content:space-between">
            <div>
              <div style="font-size:.81rem;font-weight:700;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:250px"><?= htmlspecialchars($tc['title']) ?></div>
              <div style="font-size:.67rem;color:#94a3b8"><?= htmlspecialchars($tc['first_name'].' '.$tc['last_name']) ?></div>
            </div>
            <div style="text-align:right;flex-shrink:0;margin-left:.5rem">
              <div style="font-size:.78rem;font-weight:800;color:#d97706"><?= $tc['note_count'] ?> note<?= $tc['note_count']!=1?'s':'' ?></div>
              <div style="font-size:.65rem;color:#94a3b8"><?= $tc['disc_count'] ?> discussion<?= $tc['disc_count']!=1?'s':'' ?></div>
            </div>
          </div>
          <div class="lb-bar-track">
            <div class="lb-bar-fill" data-pct="<?= $pct_lb ?>" style="background:<?= $rank_gradients[$ri] ?>"></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- ═══════════════════════════════════════════════════════════
     ROW 5: Quick Actions + Summary Stats
═══════════════════════════════════════════════════════════ -->
<div class="row g-3 mb-3">

  <!-- Quick Actions -->
  <div class="col-lg-4 db-anim db-anim-d1">
    <div class="db-card">
      <div class="db-card-hdr">
        <div>
          <div class="db-card-title"><i class="bi bi-lightning-fill" style="color:#f59e0b"></i>Quick Actions</div>
          <div class="db-card-sub">Common admin tasks</div>
        </div>
      </div>
      <div style="display:flex;flex-direction:column;gap:.55rem">
        <?php
        $qa = [
          ['href'=>'?view=admin_courses&filter=pending',   'icon'=>'bi-patch-check-fill',        'color'=>'#d97706','bg'=>'#fffbeb','border'=>'#fde68a','label'=>'Review Pending Courses',    'sub'=>$s['pending_approval'].' awaiting approval'],
          ['href'=>'?view=admin_users&action=add',         'icon'=>'bi-person-plus-fill',         'color'=>'#1a4fc4','bg'=>'#eff6ff','border'=>'#bfdbfe','label'=>'Add New User',               'sub'=>$s['total_users'].' users total'],
          ['href'=>'?view=admin_courses',                  'icon'=>'bi-collection-play-fill',     'color'=>'#059669','bg'=>'#f0fdf4','border'=>'#bbf7d0','label'=>'Manage All Courses',         'sub'=>$s['total_courses'].' courses · '.$s['active_courses'].' active'],
          ['href'=>'?view=admin_question_bank',            'icon'=>'bi-journal-richtext',         'color'=>'#7c3aed','bg'=>'#f5f3ff','border'=>'#ddd6fe','label'=>'Question Bank',              'sub'=>$s['total_questions'].' questions'],
          ['href'=>'?view=admin_payments',                 'icon'=>'bi-cash-coin',                'color'=>'#be185d','bg'=>'#fdf2f8','border'=>'#fbcfe8','label'=>'Payment Reports',            'sub'=>'TZS '.number_format($s['total_revenue']).' total'],
          ['href'=>'?view=admin_permissions',              'icon'=>'bi-shield-lock-fill',         'color'=>'#0891b2','bg'=>'#f0f9ff','border'=>'#bae6fd','label'=>'Role Permissions',           'sub'=>'Access control'],
        ];
        foreach ($qa as $qa_item): ?>
        <a href="<?= $qa_item['href'] ?>" style="display:flex;align-items:center;gap:.75rem;padding:.65rem .9rem;border-radius:12px;border:1.5px solid <?= $qa_item['border'] ?>;background:<?= $qa_item['bg'] ?>;text-decoration:none;transition:all .18s" onmouseover="this.style.filter='brightness(.96)'" onmouseout="this.style.filter=''">
          <div style="width:36px;height:36px;border-radius:10px;background:<?= $qa_item['bg'] ?>;border:1.5px solid <?= $qa_item['border'] ?>;display:flex;align-items:center;justify-content:center;font-size:.95rem;color:<?= $qa_item['color'] ?>;flex-shrink:0">
            <i class="bi <?= $qa_item['icon'] ?>"></i>
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-size:.8rem;font-weight:700;color:#1e293b"><?= $qa_item['label'] ?></div>
            <div style="font-size:.67rem;color:#94a3b8"><?= $qa_item['sub'] ?></div>
          </div>
          <i class="bi bi-chevron-right" style="font-size:.7rem;color:<?= $qa_item['color'] ?>;opacity:.6;flex-shrink:0"></i>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Platform Summary Metrics -->
  <div class="col-lg-8 db-anim db-anim-d2">
    <div class="db-card">
      <div class="db-card-hdr">
        <div>
          <div class="db-card-title"><i class="bi bi-graph-up-arrow" style="color:#1a4fc4"></i>Platform Summary</div>
          <div class="db-card-sub">Comprehensive statistics overview</div>
        </div>
        <span style="font-size:.7rem;color:#94a3b8;font-weight:600"><i class="bi bi-calendar3 me-1"></i><?= $today ?></span>
      </div>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.7rem">
        <?php
        $summary_stats = [
          ['label'=>'Total Users',        'val'=>number_format($s['total_users']),         'icon'=>'bi-people-fill',               'color'=>'#1a4fc4','bg'=>'#eff6ff'],
          ['label'=>'Students',           'val'=>number_format($s['students']),             'icon'=>'bi-mortarboard-fill',           'color'=>'#7c3aed','bg'=>'#f5f3ff'],
          ['label'=>'Instructors',        'val'=>number_format($s['instructors']),           'icon'=>'bi-person-video3',              'color'=>'#059669','bg'=>'#f0fdf4'],
          ['label'=>'Total Courses',      'val'=>number_format($s['total_courses']),         'icon'=>'bi-collection-play-fill',      'color'=>'#0891b2','bg'=>'#f0f9ff'],
          ['label'=>'Active Courses',     'val'=>number_format($s['active_courses']),        'icon'=>'bi-broadcast-fill',             'color'=>'#059669','bg'=>'#f0fdf4'],
          ['label'=>'Total Lessons',      'val'=>number_format($s['total_lessons']),         'icon'=>'bi-play-circle-fill',           'color'=>'#d97706','bg'=>'#fffbeb'],
          ['label'=>'Chapters',           'val'=>number_format($s['total_chapters']),        'icon'=>'bi-bookmark-fill',              'color'=>'#be185d','bg'=>'#fdf2f8'],
          ['label'=>'Study Notes',        'val'=>number_format($s['study_notes']),           'icon'=>'bi-journal-text',               'color'=>'#7c3aed','bg'=>'#f5f3ff'],
          ['label'=>'Discussions',        'val'=>number_format($s['discussions']),           'icon'=>'bi-chat-dots-fill',             'color'=>'#0891b2','bg'=>'#f0f9ff'],
          ['label'=>'QB Questions',       'val'=>number_format($s['total_questions']),       'icon'=>'bi-journal-richtext',           'color'=>'#6d28d9','bg'=>'#ede9fe'],
          ['label'=>'Enrollments',        'val'=>number_format($s['total_enrollments']),     'icon'=>'bi-person-check-fill',          'color'=>'#059669','bg'=>'#ecfdf5'],
          ['label'=>'Transactions',       'val'=>number_format($s['total_payments']),        'icon'=>'bi-receipt',                   'color'=>'#d97706','bg'=>'#fef9c3'],
        ];
        foreach ($summary_stats as $ss): ?>
        <div style="background:<?= $ss['bg'] ?>;border-radius:12px;padding:.75rem .85rem;border:1px solid rgba(0,0,0,.04)">
          <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.35rem">
            <i class="bi <?= $ss['icon'] ?>" style="color:<?= $ss['color'] ?>;font-size:.85rem"></i>
            <span style="font-size:.65rem;color:#64748b;font-weight:600"><?= $ss['label'] ?></span>
          </div>
          <div style="font-size:1.15rem;font-weight:800;color:#0f172a;font-family:'SUSE',sans-serif;line-height:1"><?= $ss['val'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

</div>

</div><!-- /.container-fluid -->

<script>
(function(){
'use strict';

/* ── Shared palette ────────────────────────────────────── */
const P = ['#1a4fc4','#7c3aed','#059669','#d97706','#0891b2','#be185d','#dc2626','#0d9488'];

/* ── Animated entrance (IntersectionObserver) ─────────── */
const obs = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); }
  });
}, { threshold: 0.12 });
document.querySelectorAll('.db-anim').forEach(el => obs.observe(el));

/* ── Count-up animations ──────────────────────────────── */
function countUp(el) {
  const target = parseFloat(el.dataset.target) || 0;
  const isCurrency = el.dataset.currency === '1';
  const dur = 1400;
  const start = performance.now();
  const fmt = v => {
    if (isCurrency) {
      const n = v >= 1000000 ? (v/1000000).toFixed(1)+'M' : Math.round(v).toLocaleString();
      return 'TZS ' + n;
    }
    return Math.round(v).toLocaleString();
  };
  const tick = now => {
    const p = Math.min((now - start) / dur, 1);
    const ease = 1 - Math.pow(1 - p, 4);
    el.textContent = fmt(target * ease);
    if (p < 1) requestAnimationFrame(tick);
  };
  requestAnimationFrame(tick);
}
const cuObs = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) { countUp(e.target); cuObs.unobserve(e.target); } });
}, { threshold: 0.5 });
document.querySelectorAll('.count-up').forEach(el => cuObs.observe(el));

/* ── Progress bar animations ──────────────────────────── */
const barObs = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      const pct = e.target.dataset.pct || 0;
      e.target.style.width = pct + '%';
      barObs.unobserve(e.target);
    }
  });
}, { threshold: 0.3 });
document.querySelectorAll('.kpi-bar-fill, .lb-bar-fill').forEach(el => barObs.observe(el));

/* ── Gradient factory for charts ──────────────────────── */
function makeGrad(ctx, from, to) {
  const g = ctx.createLinearGradient(0, 0, 0, ctx.canvas.offsetHeight || 240);
  g.addColorStop(0, from);
  g.addColorStop(1, to);
  return g;
}

/* ── Legend builder ────────────────────────────────────── */
function buildLegend(id, labels, colors) {
  const el = document.getElementById(id);
  if (!el) return;
  el.innerHTML = labels.map((l,i) =>
    `<div class="chart-legend-item">
       <div class="chart-legend-dot" style="background:${colors[i%colors.length]}"></div>${l}
     </div>`
  ).join('');
}

/* ── Default chart options ─────────────────────────────── */
const baseFont = { family:"'Open Sans',sans-serif", size: 11 };
const baseGrid = { color:'rgba(241,245,249,.9)', lineWidth: 1 };

/* ── REVENUE ─────────────────────────────────────────────── */
(function(){
  const ctx = document.getElementById('chartRevenue');
  if (!ctx) return;
  const revGrad = makeGrad(ctx.getContext('2d'), 'rgba(26,79,196,.22)', 'rgba(26,79,196,.01)');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= $rev_labels ?>,
      datasets: [{
        label: 'Revenue (TZS)',
        data: <?= $rev_amounts ?>,
        backgroundColor: 'rgba(26,79,196,.15)',
        borderColor: '#1a4fc4',
        borderWidth: 2,
        borderRadius: { topLeft:8, topRight:8 },
        borderSkipped: false,
        order: 2
      },{
        label: 'Transactions',
        data: <?= $rev_txns ?>,
        type: 'line',
        borderColor: '#6d28d9',
        backgroundColor: 'rgba(109,40,217,.08)',
        pointBackgroundColor: '#6d28d9',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 5,
        pointHoverRadius: 7,
        tension: .45,
        yAxisID: 'y2',
        fill: true,
        order: 1
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      interaction: { mode:'index', intersect:false },
      plugins: {
        legend: { display: true, position:'top',
          labels:{ font: baseFont, boxWidth:10, boxHeight:10, padding:14, usePointStyle:true } },
        tooltip: {
          backgroundColor:'rgba(15,23,42,.92)', padding:12,
          titleFont:{...baseFont,weight:'bold'}, bodyFont:baseFont,
          callbacks: { label: c => c.dataset.label===('Revenue (TZS)') ? ' TZS '+c.parsed.y.toLocaleString() : ' '+c.parsed.y+' txns' }
        }
      },
      scales: {
        x: { grid:{display:false}, ticks:{ font:baseFont } },
        y:  { beginAtZero:true, grid:baseGrid, ticks:{ font:baseFont, callback:v=>'TZS '+(v>=1000?Math.round(v/1000)+'K':v) } },
        y2: { position:'right', beginAtZero:true, grid:{display:false}, ticks:{ font:baseFont, stepSize:1 } }
      }
    }
  });
})();

/* ── USER GROWTH ─────────────────────────────────────────── */
(function(){
  const ctx = document.getElementById('chartUserGrowth');
  if (!ctx) return;
  const grad = makeGrad(ctx.getContext('2d'), 'rgba(124,58,237,.2)', 'rgba(124,58,237,.0)');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?= $ug_labels ?>,
      datasets:[{
        label: 'New Users',
        data: <?= $ug_counts ?>,
        borderColor: '#7c3aed',
        backgroundColor: grad,
        pointBackgroundColor: '#7c3aed',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 5,
        pointHoverRadius: 8,
        tension: .45,
        fill: true
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      interaction: { mode:'index', intersect:false },
      plugins: {
        legend: { display:false },
        tooltip: {
          backgroundColor:'rgba(15,23,42,.92)', padding:12,
          titleFont:{...baseFont,weight:'bold'}, bodyFont:baseFont
        }
      },
      scales: {
        x: { grid:{display:false}, ticks:{font:baseFont} },
        y: { beginAtZero:true, grid:baseGrid, ticks:{font:baseFont, stepSize:1} }
      }
    }
  });
})();

/* ── APPROVAL DONUT ─────────────────────────────────────── */
(function(){
  const ctx = document.getElementById('chartApproval');
  if (!ctx) return;
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: <?= $ap_labels ?>,
      datasets:[{ data: <?= $ap_counts ?>,
        backgroundColor: ['#059669','#d97706','#dc2626'],
        borderWidth: 3, borderColor:'#fff', hoverOffset: 8,
        hoverBorderColor:'#fff'
      }]
    },
    options: {
      responsive:true, maintainAspectRatio:false, cutout:'72%',
      plugins:{
        legend:{display:false},
        tooltip:{
          backgroundColor:'rgba(15,23,42,.92)', padding:10,
          titleFont:{...baseFont,weight:'bold'}, bodyFont:baseFont,
          callbacks:{label:c=>' '+c.label+': '+c.parsed+' courses'}
        }
      }
    }
  });
})();

/* ── ROLE DONUT ─────────────────────────────────────────── */
(function(){
  const labels = <?= $role_labels ?>;
  const data   = <?= $role_counts ?>;
  const colors = P.slice(0, labels.length);
  const ctx = document.getElementById('chartRoles');
  if (!ctx) return;
  new Chart(ctx, {
    type:'doughnut',
    data:{ labels, datasets:[{data, backgroundColor:colors, borderWidth:3, borderColor:'#fff', hoverOffset:6}] },
    options:{ responsive:true, maintainAspectRatio:false, cutout:'70%',
      plugins:{ legend:{display:false},
        tooltip:{ backgroundColor:'rgba(15,23,42,.92)', padding:10,
          titleFont:{...baseFont,weight:'bold'}, bodyFont:baseFont,
          callbacks:{label:c=>' '+c.label+': '+c.parsed} }
      }
    }
  });
  buildLegend('roleLegend', labels, colors);
})();

/* ── PAYMENT METHODS DONUT ──────────────────────────────── */
(function(){
  const pm_labels = <?= $pm_labels ?? '[]' ?>;
  const pm_counts = <?= $pm_counts ?? '[]' ?>;
  const colors = ['#7c3aed','#0891b2','#059669','#d97706'].slice(0, pm_labels.length);
  const ctx = document.getElementById('chartPayMethods');
  if (!ctx) return;
  new Chart(ctx, {
    type:'doughnut',
    data:{ labels:pm_labels, datasets:[{data:pm_counts, backgroundColor:colors, borderWidth:3, borderColor:'#fff', hoverOffset:6}] },
    options:{ responsive:true, maintainAspectRatio:false, cutout:'70%',
      plugins:{ legend:{display:false},
        tooltip:{ backgroundColor:'rgba(15,23,42,.92)', padding:10,
          titleFont:{...baseFont,weight:'bold'}, bodyFont:baseFont,
          callbacks:{label:c=>' '+c.label+': '+c.parsed+' txn(s)'}
        }
      }
    }
  });
  buildLegend('pmLegend', pm_labels, colors);
})();

})(); // end IIFE
</script>
