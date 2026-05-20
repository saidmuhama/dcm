<?php
/* ── Student data (server-side) ───────────────────────────── */
$_usr  = $_SESSION['usr_code'] ?? '';
$_name = $_SESSION['name']     ?? 'Student';
$_role = $_SESSION['user_role']?? 1;
$_pct  = 0;
try { $_pct = min(100, max(0, (int)App::getProfileCompletionStatus($_usr, $_role))); } catch(Throwable $e) {}

$_st = null;
if ($_usr) {
    $sq = $db->prepare("SELECT first_name, image, main_academic_level FROM tbl_students WHERE usr_code = ?");
    $sq->bind_param('s', $_usr);
    $sq->execute();
    $_st = $sq->get_result()->fetch_assoc();
}
$_img      = !empty($_st['image']) ? 'uploads/' . basename($_st['image']) : '';
$_fname    = htmlspecialchars($_st['first_name'] ?? explode(' ', $_name)[0]);
$_greet    = (int)date('H') < 12 ? 'Good morning' : ((int)date('H') < 17 ? 'Good afternoon' : 'Good evening');
$_ring_off = round(100 - $_pct, 1);

/* ── Categories ───────────────────────────────────────────── */
$_cats = $db->query("SELECT id, category_title FROM tbl_course_categories WHERE status=1 ORDER BY `order`, id")->fetch_all(MYSQLI_ASSOC) ?: [];
?>
<style>
/* ══ Student Dashboard (sd-*) ════════════════════════════════ */
.sd-wrap  { font-family:'Open Sans',sans-serif;padding:1.25rem 1rem 3rem; }

/* ── Hero ── */
.sd-hero  { background:linear-gradient(135deg,#1e1b4b 0%,#312e81 50%,#4c1d95 100%);
            border-radius:22px;padding:1.75rem 2rem;margin-bottom:1.4rem;
            position:relative;overflow:hidden;isolation:isolate; }
.sd-hero-grid { position:absolute;inset:0;z-index:0;
                background-image:linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),
                                 linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);
                background-size:40px 40px; }
.sd-hero-inner { position:relative;z-index:1;display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap; }
.sd-hero-avatar { width:64px;height:64px;border-radius:50%;object-fit:cover;
                  border:3px solid rgba(255,255,255,.3);flex-shrink:0;background:#312e81;
                  display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:rgba(255,255,255,.5); }
.sd-hero-text   { flex:1;min-width:150px; }
.sd-hero-greet  { font-size:.78rem;color:rgba(255,255,255,.55);font-weight:600;text-transform:uppercase;letter-spacing:.05em; }
.sd-hero-name   { font-size:1.5rem;font-weight:900;color:#fff;font-family:'SUSE',sans-serif;line-height:1.15; }
.sd-hero-sub    { font-size:.8rem;color:rgba(255,255,255,.5);margin-top:.2rem; }
.sd-hero-actions{ display:flex;gap:.5rem;margin-top:.85rem;flex-wrap:wrap; }
.sd-hero-btn    { display:inline-flex;align-items:center;gap:.35rem;border-radius:10px;
                  padding:.45rem 1.05rem;font-size:.78rem;font-weight:700;cursor:pointer;
                  font-family:inherit;border:none;text-decoration:none;transition:all .15s; }
.sd-hero-btn-p  { background:#fff;color:#312e81; }
.sd-hero-btn-p:hover { background:#e0e7ff;color:#312e81; }
.sd-hero-btn-s  { background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.2); }
.sd-hero-btn-s:hover { background:rgba(255,255,255,.22); }
.sd-ring-wrap   { flex-shrink:0;text-align:center; }
.sd-ring        { width:76px;height:76px; }
.sd-ring-lbl    { font-size:.62rem;color:rgba(255,255,255,.5);font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-top:.2rem; }

/* ── KPI pills ── */
.sd-kpis  { display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.8rem;margin-bottom:1.4rem; }
.sd-kpi   { background:#fff;border:1px solid #f0f4f8;border-radius:16px;padding:1rem 1.1rem;
            box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 14px rgba(0,0,0,.04);
            animation:sd-up .4s cubic-bezier(.16,1,.3,1) both; }
.sd-kpi:nth-child(2){animation-delay:.05s} .sd-kpi:nth-child(3){animation-delay:.1s} .sd-kpi:nth-child(4){animation-delay:.15s}
@keyframes sd-up { from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)} }
.sd-kpi-val { font-size:1.65rem;font-weight:900;font-family:'SUSE',sans-serif;line-height:1; }
.sd-kpi-lbl { font-size:.67rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-top:.25rem; }
.sd-kpi-icon{ width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;
              font-size:1rem;margin-bottom:.55rem; }

/* ── Tabs ── */
.sd-tabs  { display:flex;gap:.35rem;background:#fff;border:1px solid #e8edf3;border-radius:14px;
            padding:.4rem;margin-bottom:1.2rem;overflow-x:auto; }
.sd-tab   { display:inline-flex;align-items:center;gap:.45rem;border-radius:10px;padding:.55rem 1.2rem;
            font-size:.8rem;font-weight:700;cursor:pointer;border:none;background:transparent;
            color:#64748b;font-family:inherit;white-space:nowrap;transition:all .18s; }
.sd-tab.active { background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;box-shadow:0 4px 12px rgba(79,70,229,.3); }
.sd-tab:hover:not(.active) { background:#f1f5f9;color:#0f172a; }
.sd-tab-count { background:rgba(255,255,255,.25);border-radius:100px;padding:.05rem .45rem;font-size:.65rem; }
.sd-tab:not(.active) .sd-tab-count { background:#e2e8f0;color:#64748b; }
.sd-panel { display:none; }
.sd-panel.active { display:block; animation:sd-up .3s ease both; }

/* ── Section header ── */
.sd-sec-head { display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem; }
.sd-sec-title{ font-size:.95rem;font-weight:800;color:#0f172a;font-family:'SUSE',sans-serif; }
.sd-sec-link { font-size:.75rem;font-weight:700;color:#4f46e5;text-decoration:none; }
.sd-sec-link:hover { color:#3730a3; }

/* ── Course cards (enrolled) ── */
.sd-enr-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem; }
.sd-enr-card { background:#fff;border:1px solid #f0f4f8;border-radius:16px;overflow:hidden;
               box-shadow:0 1px 3px rgba(0,0,0,.05);transition:box-shadow .2s,transform .2s; }
.sd-enr-card:hover { box-shadow:0 8px 30px rgba(0,0,0,.1);transform:translateY(-2px); }
.sd-enr-thumb{ height:140px;background:#f1f5f9;position:relative;overflow:hidden; }
.sd-enr-thumb img { width:100%;height:100%;object-fit:cover; }
.sd-enr-badge{ position:absolute;top:.6rem;right:.6rem;background:rgba(0,0,0,.55);color:#fff;
               font-size:.65rem;font-weight:700;border-radius:100px;padding:.2rem .6rem;backdrop-filter:blur(4px); }
.sd-enr-body { padding:.9rem 1rem 1rem; }
.sd-enr-title{ font-size:.85rem;font-weight:700;color:#0f172a;margin-bottom:.35rem;
               display:-webkit-box;-webkit-line-clamp:2;line-clamp:2;-webkit-box-orient:vertical;overflow:hidden; }
.sd-enr-meta { font-size:.72rem;color:#94a3b8;margin-bottom:.65rem; }
.sd-enr-prog { height:5px;background:#f0f4f8;border-radius:100px;margin-bottom:.4rem;overflow:hidden; }
.sd-enr-prog-bar { height:100%;border-radius:100px;transition:width 1.2s cubic-bezier(.16,1,.3,1); }
.sd-enr-prog-txt{ display:flex;justify-content:space-between;font-size:.67rem;color:#94a3b8;font-weight:600;margin-bottom:.75rem; }
.sd-enr-btn  { width:100%;border-radius:10px;padding:.5rem;font-size:.78rem;font-weight:700;
               cursor:pointer;border:none;font-family:inherit;transition:all .15s; }
.sd-enr-btn-go   { background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;box-shadow:0 3px 10px rgba(79,70,229,.25); }
.sd-enr-btn-go:hover { filter:brightness(1.08); }
.sd-enr-btn-done { background:#dcfce7;color:#166534;border:1px solid #86efac; }

/* ── Browse courses ── */
.sd-filter-bar  { display:flex;gap:.6rem;margin-bottom:1.1rem;flex-wrap:wrap; }
.sd-filter-bar input, .sd-filter-bar select {
  border:1.5px solid #e2e8f0;border-radius:10px;padding:.5rem .85rem;font-size:.82rem;
  font-family:inherit;color:#0f172a;background:#fff;outline:none;transition:border-color .15s; }
.sd-filter-bar input  { flex:1;min-width:180px; }
.sd-filter-bar input:focus, .sd-filter-bar select:focus { border-color:#4f46e5; }
.sd-browse-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:1rem; }
.sd-brow-card  { background:#fff;border:1px solid #f0f4f8;border-radius:16px;overflow:hidden;
                 box-shadow:0 1px 3px rgba(0,0,0,.05);transition:all .2s;display:flex;flex-direction:column; }
.sd-brow-card:hover { box-shadow:0 8px 30px rgba(0,0,0,.1);transform:translateY(-2px); }
.sd-brow-thumb { height:130px;background:#f1f5f9;overflow:hidden;position:relative; }
.sd-brow-thumb img { width:100%;height:100%;object-fit:cover;transition:transform .3s; }
.sd-brow-card:hover .sd-brow-thumb img { transform:scale(1.04); }
.sd-brow-price-tag { position:absolute;bottom:.55rem;left:.55rem;
                     background:rgba(15,23,42,.8);color:#fff;font-size:.7rem;font-weight:800;
                     border-radius:8px;padding:.2rem .55rem;backdrop-filter:blur(4px); }
.sd-brow-wish  { position:absolute;top:.55rem;right:.55rem;width:30px;height:30px;border-radius:50%;
                 background:rgba(255,255,255,.9);border:none;cursor:pointer;display:flex;
                 align-items:center;justify-content:center;font-size:.85rem;color:#dc2626;
                 transition:all .15s;box-shadow:0 2px 6px rgba(0,0,0,.15); }
.sd-brow-wish:hover { background:#fee2e2; }
.sd-brow-wish.active { background:#fee2e2;color:#dc2626; }
.sd-brow-body  { padding:.85rem .95rem 1rem;flex:1;display:flex;flex-direction:column; }
.sd-brow-cat   { font-size:.65rem;font-weight:800;color:#4f46e5;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem; }
.sd-brow-title { font-size:.83rem;font-weight:700;color:#0f172a;margin-bottom:.3rem;
                 display:-webkit-box;-webkit-line-clamp:2;line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;flex:1; }
.sd-brow-rating{ display:flex;align-items:center;gap:.3rem;font-size:.72rem;color:#94a3b8;margin-bottom:.7rem; }
.sd-brow-rating .stars { color:#f59e0b; }
.sd-brow-foot  { display:flex;gap:.4rem; }
.sd-brow-btn   { flex:1;border-radius:9px;padding:.42rem .5rem;font-size:.73rem;font-weight:700;
                 cursor:pointer;border:none;font-family:inherit;transition:all .15s;
                 display:flex;align-items:center;justify-content:center;gap:.3rem; }
.sd-brow-btn-cart{ background:#ede9fe;color:#4f46e5; }
.sd-brow-btn-cart:hover { background:#4f46e5;color:#fff; }
.sd-brow-btn-view{ background:#f0fdf4;color:#059669;border:1px solid #bbf7d0; }
.sd-brow-btn-view:hover { background:#059669;color:#fff;border-color:#059669; }

/* ── Exams ── */
.sd-exam-kpis { display:grid;grid-template-columns:repeat(3,1fr);gap:.7rem;margin-bottom:1.2rem; }
.sd-exam-kpi  { background:#fff;border:1px solid #f0f4f8;border-radius:14px;padding:.85rem 1rem;text-align:center;
                box-shadow:0 1px 3px rgba(0,0,0,.04); }
.sd-exam-kpi-val { font-size:1.4rem;font-weight:900;font-family:'SUSE',sans-serif; }
.sd-exam-kpi-lbl { font-size:.67rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em; }
.sd-exam-list { display:flex;flex-direction:column;gap:.6rem; }
.sd-exam-row  { background:#fff;border:1px solid #f0f4f8;border-radius:14px;padding:.85rem 1.1rem;
                display:flex;align-items:center;gap:.85rem;box-shadow:0 1px 3px rgba(0,0,0,.04); }
.sd-exam-icon { width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;
                font-size:1rem;flex-shrink:0; }
.sd-exam-info { flex:1;min-width:0; }
.sd-exam-title{ font-size:.83rem;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.sd-exam-meta { font-size:.68rem;color:#94a3b8;margin-top:.1rem; }
.sd-exam-score{ font-size:1.05rem;font-weight:900;font-family:'SUSE',sans-serif;flex-shrink:0; }
.sd-exam-btn  { flex-shrink:0;border-radius:9px;padding:.38rem .85rem;font-size:.73rem;font-weight:700;
                cursor:pointer;border:none;font-family:inherit;transition:all .15s; }

/* ── Empty states ── */
.sd-empty { text-align:center;padding:2.5rem 1rem;color:#94a3b8; }
.sd-empty-icon { font-size:3rem;display:block;margin-bottom:.75rem; }
.sd-empty-title{ font-weight:700;color:#475569;margin-bottom:.3rem;font-size:.9rem; }
.sd-empty-sub  { font-size:.8rem; }

/* ── Skeleton ── */
.sd-skel { background:linear-gradient(90deg,#f0f4f8 25%,#e2e8f0 50%,#f0f4f8 75%);
           background-size:200% 100%;animation:sd-shim 1.5s infinite;border-radius:8px; }
@keyframes sd-shim { 0%{background-position:200%}100%{background-position:-200%} }

/* ── Profile completion banner ── */
.sd-complete-banner { background:linear-gradient(135deg,#fff7ed,#fef3c7);border:1px solid #fde68a;
                      border-radius:14px;padding:.85rem 1.1rem;display:flex;align-items:center;
                      gap:.85rem;margin-bottom:1.2rem; }
.sd-complete-banner .icon { font-size:1.5rem;flex-shrink:0; }
.sd-complete-banner p { margin:0;font-size:.8rem;color:#92400e;flex:1; }
.sd-complete-banner strong { font-weight:800; }
</style>

<div class="sd-wrap">

<!-- ── Hero ── -->
<div class="sd-hero">
  <div class="sd-hero-grid"></div>
  <div class="sd-hero-inner">
    <?php if ($_img): ?>
      <img class="sd-hero-avatar" src="<?= $_img ?>" alt="">
    <?php else: ?>
      <div class="sd-hero-avatar"><i class="bi bi-person-fill"></i></div>
    <?php endif; ?>
    <div class="sd-hero-text">
      <div class="sd-hero-greet"><?= $_greet ?></div>
      <div class="sd-hero-name"><?= $_fname ?>!</div>
      <div class="sd-hero-sub">Keep learning, keep growing. Your progress awaits.</div>
      <div class="sd-hero-actions">
        <a href="?view=student_exams" class="sd-hero-btn sd-hero-btn-p"><i class="bi bi-play-circle-fill"></i>Take an Exam</a>
        <a href="?view=student-profile-completion-8872" class="sd-hero-btn sd-hero-btn-s"><i class="bi bi-person-badge"></i>My Profile</a>
        <a href="?view=view_my_cart_to_pay" class="sd-hero-btn sd-hero-btn-s" id="sdCartBtn" style="position:relative">
          <i class="bi bi-cart3"></i>My Cart
          <span id="sdCartBadge" style="display:none;position:absolute;top:-6px;right:-6px;
            background:#f59e0b;color:#fff;font-size:.55rem;font-weight:900;border-radius:100px;
            min-width:16px;height:16px;line-height:16px;text-align:center;padding:0 4px">0</span>
        </a>
      </div>
    </div>
    <div class="sd-ring-wrap">
      <svg class="sd-ring" viewBox="0 0 36 36">
        <circle cx="18" cy="18" r="15.9" fill="none" stroke="rgba(255,255,255,.15)" stroke-width="3"/>
        <circle cx="18" cy="18" r="15.9" fill="none" stroke="rgba(255,255,255,.85)" stroke-width="3"
                stroke-dasharray="100 100" stroke-dashoffset="<?= $_ring_off ?>"
                stroke-linecap="round" style="transform:rotate(-90deg);transform-origin:center"/>
        <text x="18" y="21.5" text-anchor="middle" style="font-size:7px;font-weight:900;fill:#fff;font-family:'SUSE',sans-serif"><?= $_pct ?>%</text>
      </svg>
      <div class="sd-ring-lbl">Profile</div>
    </div>
  </div>
</div>

<!-- ── Profile completion banner (if incomplete) ── -->
<?php if ($_pct < 60): ?>
<div class="sd-complete-banner">
  <span class="icon">⚡</span>
  <p><strong>Your profile is <?= $_pct ?>% complete.</strong> Complete it to unlock personalised course recommendations and exam access.</p>
  <a href="?view=student-profile-completion-8872" class="sd-hero-btn sd-hero-btn-p" style="background:#f59e0b;color:#fff;white-space:nowrap">Complete Now</a>
</div>
<?php endif; ?>

<!-- ── KPI cards ── -->
<div class="sd-kpis" id="sdKpis">
  <?php foreach ([
    ['—','Enrolled Courses','#4f46e5','#ede9fe','bi-collection-play-fill','courses'],
    ['—','Completed','#059669','#dcfce7','bi-patch-check-fill','completed'],
    ['—','Exams Taken','#0ea5e9','#e0f2fe','bi-clipboard-check-fill','exams'],
    ['—','Avg Score','#f59e0b','#fef3c7','bi-star-fill','avg'],
  ] as [$v,$l,$c,$bg,$ic,$key]): ?>
  <div class="sd-kpi" id="sdKpi_<?= $key ?>">
    <div class="sd-kpi-icon" style="background:<?= $bg ?>;color:<?= $c ?>"><i class="bi <?= $ic ?>"></i></div>
    <div class="sd-kpi-val" style="color:<?= $c ?>"><?= $v ?></div>
    <div class="sd-kpi-lbl"><?= $l ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ── Tabs ── -->
<div class="sd-tabs" role="tablist">
  <button class="sd-tab active" id="sdTabBrowse" onclick="sdSwitch('browse')">
    <i class="bi bi-compass"></i>Discover Courses<span class="sd-tab-count" id="sdCntBrw">—</span>
  </button>
  <button class="sd-tab" id="sdTabLearning" onclick="sdSwitch('learning')">
    <i class="bi bi-play-circle"></i>My Learning<span class="sd-tab-count" id="sdCntLrn">—</span>
  </button>
  <button class="sd-tab" id="sdTabExams" onclick="sdSwitch('exams')">
    <i class="bi bi-clipboard-check"></i>My Exams<span class="sd-tab-count" id="sdCntExm">—</span>
  </button>
</div>

<!-- ── Panel: My Learning ── -->
<div class="sd-panel" id="sdPanelLearning">
  <div class="sd-sec-head">
    <div class="sd-sec-title"><i class="bi bi-play-circle-fill me-2" style="color:#4f46e5"></i>Enrolled Courses</div>
  </div>
  <div class="sd-enr-grid" id="sdEnrGrid">
    <!-- skeleton -->
    <?php for ($i=0;$i<3;$i++): ?>
    <div class="sd-enr-card"><div class="sd-enr-thumb sd-skel" style="border-radius:0"></div>
      <div class="sd-enr-body"><div class="sd-skel" style="height:14px;width:80%;margin-bottom:8px"></div>
        <div class="sd-skel" style="height:10px;width:50%;margin-bottom:16px"></div>
        <div class="sd-skel" style="height:5px;margin-bottom:8px"></div>
        <div class="sd-skel" style="height:34px;border-radius:10px"></div></div></div>
    <?php endfor; ?>
  </div>
</div>

<!-- ── Panel: Browse Courses ── -->
<div class="sd-panel active" id="sdPanelBrowse">
  <div class="sd-filter-bar">
    <input type="text" id="sdSearch" placeholder="Search courses…" autocomplete="off" oninput="sdDebounceBrowse()">
    <select id="sdFilterCat" onchange="sdDoBrowse()">
      <option value="">All Categories</option>
      <?php foreach ($_cats as $c): ?>
      <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['category_title']) ?></option>
      <?php endforeach; ?>
    </select>
    <select id="sdFilterPrice" onchange="sdDoBrowse()">
      <option value="">All Prices</option>
      <option value="free">Free</option>
      <option value="paid">Paid</option>
    </select>
  </div>
  <div class="sd-browse-grid" id="sdBrowseGrid">
    <?php for ($i=0;$i<6;$i++): ?>
    <div class="sd-brow-card"><div class="sd-brow-thumb sd-skel" style="border-radius:0;height:130px"></div>
      <div class="sd-brow-body"><div class="sd-skel" style="height:10px;width:40%;margin-bottom:8px"></div>
        <div class="sd-skel" style="height:14px;width:90%;margin-bottom:4px"></div>
        <div class="sd-skel" style="height:14px;width:60%;margin-bottom:16px"></div>
        <div class="sd-skel" style="height:34px;border-radius:9px"></div></div></div>
    <?php endfor; ?>
  </div>
  <div id="sdBrowsePager" style="text-align:center;margin-top:1.2rem"></div>
</div>

<!-- ── Panel: My Exams ── -->
<div class="sd-panel" id="sdPanelExams">
  <div class="sd-exam-kpis">
    <div class="sd-exam-kpi"><div class="sd-exam-kpi-val" id="sdExmTotal" style="color:#0ea5e9">—</div><div class="sd-exam-kpi-lbl">Taken</div></div>
    <div class="sd-exam-kpi"><div class="sd-exam-kpi-val" id="sdExmPassed" style="color:#059669">—</div><div class="sd-exam-kpi-lbl">Passed</div></div>
    <div class="sd-exam-kpi"><div class="sd-exam-kpi-val" id="sdExmAvg" style="color:#f59e0b">—</div><div class="sd-exam-kpi-lbl">Avg Score</div></div>
  </div>
  <div class="sd-sec-head">
    <div class="sd-sec-title"><i class="bi bi-clock-history me-2" style="color:#0ea5e9"></i>Exam History</div>
    <a href="?view=student_exams" class="sd-hero-btn sd-hero-btn-p" style="background:#4f46e5;color:#fff">
      <i class="bi bi-plus-circle"></i>Take an Exam
    </a>
  </div>
  <div class="sd-exam-list" id="sdExamList">
    <div class="sd-empty"><span class="sd-empty-icon"><i class="bi bi-clipboard"></i></span>
      <div class="sd-skel" style="height:60px;border-radius:14px;margin-bottom:.6rem"></div>
      <div class="sd-skel" style="height:60px;border-radius:14px"></div></div>
  </div>
</div>

</div><!-- /.sd-wrap -->

<script>
/* ════════════════════════════════════════════════════════════
   STATE
════════════════════════════════════════════════════════════ */
const SD_USR = <?= json_encode($_usr) ?>;
let sdBrowseTimer = null;
let sdWishlisted  = new Set();
let sdEnrolledIds = new Set();

/* ════════════════════════════════════════════════════════════
   INIT
════════════════════════════════════════════════════════════ */
function _sdInit() {
  sdLoadEnrolled();
  sdLoadStats();
  sdDoBrowse();
  sdLoadExams();
  sdLoadCartCount();
}

function sdLoadCartCount() {
  fetch('ajax/ajax_fetch_cart.php')
    .then(r => r.json())
    .then(res => {
      const n = (res.data || []).length;
      const badge = document.getElementById('sdCartBadge');
      if (badge) {
        badge.textContent = n;
        badge.style.display = n > 0 ? 'inline-block' : 'none';
      }
    })
    .catch(() => {});
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', _sdInit);
} else { _sdInit(); }

/* ════════════════════════════════════════════════════════════
   TABS
════════════════════════════════════════════════════════════ */
function sdSwitch(tab) {
  ['learning','browse','exams'].forEach(t => {
    document.getElementById('sdPanel' + cap(t)).classList.toggle('active', t === tab);
    document.getElementById('sdTab'   + cap(t)).classList.toggle('active', t === tab);
  });
}
function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

/* ════════════════════════════════════════════════════════════
   KPI STATS
════════════════════════════════════════════════════════════ */
function sdLoadStats() {
  Promise.all([
    fetch('ajax/ajax_fetch_subscribed_courses.php').then(r => r.json()).catch(() => ({data:[]})),
    fetch('ajax/ajax_student_exam.php?action=stats').then(r => r.json()).catch(() => ({data:{}})),
  ]).then(([enrRes, exmRes]) => {
    const courses  = enrRes.data || [];
    const completed= courses.filter(c => parseInt(c.progress||0) === 100).length;
    const exm      = exmRes.data || {};

    sdKpi('courses',   courses.length,  '');
    sdKpi('completed', completed,        '');
    sdKpi('exams',     exm.completed||0, '');
    sdKpi('avg',       exm.avg_pct ? Math.round(exm.avg_pct)+'%' : '—', '');

    document.getElementById('sdCntLrn').textContent = courses.length;

    // exam kpis
    const sdExmTotal  = document.getElementById('sdExmTotal');
    const sdExmPassed = document.getElementById('sdExmPassed');
    const sdExmAvg    = document.getElementById('sdExmAvg');
    if (sdExmTotal)  sdExmTotal.textContent  = exm.total_sessions||0;
    if (sdExmPassed) sdExmPassed.textContent = exm.passed||0;
    if (sdExmAvg)    sdExmAvg.textContent    = exm.avg_pct ? Math.round(exm.avg_pct)+'%' : '—';
  });
}

function sdKpi(key, val, suffix) {
  const el = document.getElementById('sdKpi_' + key);
  if (!el) return;
  el.querySelector('.sd-kpi-val').textContent = val + (suffix||'');
}

/* ════════════════════════════════════════════════════════════
   MY LEARNING (ENROLLED COURSES)
════════════════════════════════════════════════════════════ */
function sdLoadEnrolled() {
  fetch('ajax/ajax_fetch_subscribed_courses.php')
    .then(r => r.json())
    .then(res => {
      const courses = res.data || [];
      sdEnrolledIds = new Set(courses.map(c => String(c.course_id)));
      document.getElementById('sdCntLrn').textContent = courses.length;

      const grid = document.getElementById('sdEnrGrid');
      if (!courses.length) {
        grid.innerHTML = `<div class="sd-empty" style="grid-column:1/-1">
          <span class="sd-empty-icon"><i class="bi bi-collection-play"></i></span>
          <div class="sd-empty-title">No enrolled courses yet</div>
          <div class="sd-empty-sub">Browse and subscribe to courses to start learning.</div>
          <button class="sd-hero-btn sd-hero-btn-p" style="background:#4f46e5;color:#fff;margin-top:.9rem" onclick="sdSwitch('browse')">
            <i class="bi bi-compass"></i>Discover Courses
          </button></div>`;
        return;
      }

      grid.innerHTML = courses.map(c => {
        const pct   = parseInt(c.progress || 0);
        const thumb = c.thumbnail || 'uploads/course_default.png';
        const pCol  = pct === 100 ? '#059669' : pct > 0 ? '#f59e0b' : '#94a3b8';
        const badge = pct === 100 ? '<span style="background:#059669" class="sd-enr-badge"><i class="bi bi-check2"></i> Done</span>'
                    : pct > 0    ? `<span style="background:#f59e0b" class="sd-enr-badge">${pct}%</span>` : '';
        const btnHtml = pct === 100
          ? `<button class="sd-enr-btn sd-enr-btn-done" onclick="sdView(${c.course_id})"><i class="bi bi-arrow-repeat"></i> Review Course</button>`
          : `<button class="sd-enr-btn sd-enr-btn-go" onclick="sdStart(${c.course_id})"><i class="bi bi-play-fill"></i> ${pct > 0 ? 'Continue' : 'Start'}</button>`;
        return `<div class="sd-enr-card">
          <div class="sd-enr-thumb">
            <img src="${sdEsc(thumb)}" alt="" onerror="this.src='uploads/course_default.png'">
            ${badge}
          </div>
          <div class="sd-enr-body">
            <div class="sd-enr-title">${sdEsc(c.title||'Untitled')}</div>
            <div class="sd-enr-meta"><i class="bi bi-collection me-1"></i>${c.total_lessons||0} lessons · ${c.total_chapters||0} chapters</div>
            <div class="sd-enr-prog"><div class="sd-enr-prog-bar" style="width:${pct}%;background:${pCol}"></div></div>
            <div class="sd-enr-prog-txt"><span>${pct}% complete</span><span>${pct === 100 ? '🎉 Completed' : ''}</span></div>
            ${btnHtml}
          </div>
        </div>`;
      }).join('');
    })
    .catch(() => {
      document.getElementById('sdEnrGrid').innerHTML = `<div class="sd-empty" style="grid-column:1/-1"><div class="sd-empty-title">Could not load courses</div></div>`;
    });
}

function sdStart(id) { window.location.href = '?view=read_course_details_data&course_id=' + id; }
function sdView(id)  { window.location.href = '?view=view_course_details&course_id=' + id; }

/* ════════════════════════════════════════════════════════════
   BROWSE COURSES
════════════════════════════════════════════════════════════ */
function sdDebounceBrowse() {
  clearTimeout(sdBrowseTimer);
  sdBrowseTimer = setTimeout(sdDoBrowse, 380);
}

function sdDoBrowse() {
  const search = (document.getElementById('sdSearch')?.value || '').trim();
  const price  = document.getElementById('sdFilterPrice')?.value || '';
  const cat    = document.getElementById('sdFilterCat')?.value   || '';

  const grid = document.getElementById('sdBrowseGrid');
  grid.innerHTML = Array(6).fill(`<div class="sd-brow-card">
    <div class="sd-brow-thumb sd-skel" style="border-radius:0;height:130px"></div>
    <div class="sd-brow-body"><div class="sd-skel" style="height:10px;width:40%;margin-bottom:8px"></div>
    <div class="sd-skel" style="height:14px;margin-bottom:4px"></div><div class="sd-skel" style="height:34px;border-radius:9px;margin-top:12px"></div></div></div>`).join('');

  fetch(`ajax/ajax_get_published_courses.php?search=${encodeURIComponent(search)}&price=${encodeURIComponent(price)}&category=${encodeURIComponent(cat)}`)
    .then(r => r.json())
    .then(res => {
      const courses = res.data || res || [];
      const arr = Array.isArray(courses) ? courses : [];
      document.getElementById('sdCntBrw').textContent = arr.length;
      if (!arr.length) {
        grid.innerHTML = `<div class="sd-empty" style="grid-column:1/-1">
          <span class="sd-empty-icon"><i class="bi bi-search"></i></span>
          <div class="sd-empty-title">No courses found</div>
          <div class="sd-empty-sub">Try a different search or filter.</div></div>`;
        return;
      }
      grid.innerHTML = arr.map(c => sdBrowseCard(c)).join('');
    })
    .catch(() => {
      grid.innerHTML = `<div class="sd-empty" style="grid-column:1/-1"><div class="sd-empty-title">Could not load courses</div></div>`;
    });
}

function sdBrowseCard(c) {
  const price    = parseFloat(c.price || 0);
  const discount = parseFloat(c.discount || 0);
  const final    = price - (price * discount / 100);
  const priceTag = price === 0 ? 'FREE'
    : 'TZS ' + new Intl.NumberFormat('en').format(Math.round(final));
  const oldPrice = discount > 0 ? `<s style="color:rgba(255,255,255,.55);font-size:.6rem">TZS ${new Intl.NumberFormat('en').format(Math.round(price))}</s>` : '';
  const thumb  = c.thumbnail || 'uploads/course_default.png';
  const rating = parseFloat(c.avg_rating || 0).toFixed(1);
  const reviews= c.total_reviews || 0;
  const stars  = '★'.repeat(Math.round(rating)) + '☆'.repeat(5 - Math.round(rating));
  const enrolled = parseInt(c.is_enrolled || 0) === 1 || sdEnrolledIds.has(String(c.id));
  const catName  = c.category_title || '';
  const enrCount = parseInt(c.enrolled_count || 0);

  return `<div class="sd-brow-card" id="sdBrowCard_${c.id}">
    <div class="sd-brow-thumb">
      <img src="${sdEsc(thumb)}" alt="" onerror="this.src='uploads/course_default.png'">
      <div class="sd-brow-price-tag">${priceTag} ${oldPrice}</div>
      <button class="sd-brow-wish ${sdWishlisted.has(c.id)?'active':''}" onclick="sdToggleWish(${c.id},this)" title="Wishlist">
        <i class="bi bi-heart${sdWishlisted.has(c.id)?'-fill':''}"></i>
      </button>
    </div>
    <div class="sd-brow-body">
      ${catName ? `<div class="sd-brow-cat">${sdEsc(catName)}</div>` : ''}
      <div class="sd-brow-title">${sdEsc(c.title || 'Untitled')}</div>
      <div class="sd-brow-rating">
        <span class="stars">${stars}</span>
        <span>${rating} <span style="color:#cbd5e1">(${reviews})</span></span>
        ${enrCount > 0 ? `<span style="color:#cbd5e1">· ${enrCount} enrolled</span>` : ''}
      </div>
      <div class="sd-brow-foot">
        ${enrolled
          ? `<button class="sd-brow-btn sd-brow-btn-view" onclick="sdStart(${c.id})" style="flex:2"><i class="bi bi-play-fill"></i> Continue Learning</button>`
          : price === 0
            ? `<button class="sd-brow-btn sd-brow-btn-cart" onclick="sdEnrollFree(${c.id},this)" style="background:#dcfce7;color:#166534"><i class="bi bi-gift"></i> Enrol Free</button>
               <button class="sd-brow-btn sd-brow-btn-view" onclick="sdView(${c.id})"><i class="bi bi-eye"></i> View</button>`
            : `<button class="sd-brow-btn sd-brow-btn-cart" onclick="sdAddCart(${c.id},this)"><i class="bi bi-cart-plus"></i> Enrol</button>
               <button class="sd-brow-btn sd-brow-btn-view" onclick="sdView(${c.id})"><i class="bi bi-eye"></i> View</button>`
        }
      </div>
    </div>
  </div>`;
}

function sdToggleWish(id, btn) {
  fetch('ajax/ajax_toggle_wishlist.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({course_id: id})
  }).then(r => r.json()).then(res => {
    if (res.status === 'success') {
      if (sdWishlisted.has(id)) { sdWishlisted.delete(id); btn.classList.remove('active'); btn.innerHTML = '<i class="bi bi-heart"></i>'; }
      else { sdWishlisted.add(id); btn.classList.add('active'); btn.innerHTML = '<i class="bi bi-heart-fill"></i>'; }
    } else { Swal.fire({ icon:'info', title: res.message||'Login to save wishlist', timer:2000, showConfirmButton:false }); }
  }).catch(() => {});
}

function sdEnrollFree(id, btn) {
  btn.disabled = true;
  btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
  fetch('ajax/ajax_enroll_free_course.php', {
    method: 'POST', headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({course_id: id})
  }).then(r => r.json()).then(res => {
    if (res.status === 'success' || res.status === 'already') {
      sdEnrolledIds.add(String(id));
      const card = document.getElementById('sdBrowCard_' + id);
      if (card) {
        const foot = card.querySelector('.sd-brow-foot');
        if (foot) foot.innerHTML = `<button class="sd-brow-btn sd-brow-btn-view" onclick="sdStart(${id})" style="flex:2"><i class="bi bi-play-fill"></i> Start Learning</button>`;
      }
      Swal.fire({icon:'success', title:'Enrolled!', text: res.message || 'You can start learning now.', timer:2500, showConfirmButton:false});
      sdLoadEnrolled();
    } else {
      Swal.fire({icon:'error', title:'Failed', text: res.message || 'Could not enrol.', timer:2500, showConfirmButton:false});
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-gift"></i> Enrol Free';
    }
  }).catch(() => {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-gift"></i> Enrol Free';
  });
}

function sdAddCart(id, btn) {
  btn.disabled = true;
  btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
  fetch('ajax/ajax_add_to_cart.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({course_id: id})
  }).then(r => r.json()).then(res => {
    if (res.status === 'success') {
      sdLoadCartCount();
      Swal.fire({
        icon:'success', title:'Added to cart!', text: res.message || '',
        showCancelButton:true, confirmButtonText:'<i class="bi bi-cart3"></i> View Cart',
        cancelButtonText:'Continue Browsing', confirmButtonColor:'#4f46e5',
        customClass:{popup:'ds-pop',confirmButton:'ds-btn'}
      }).then(r => { if (r.isConfirmed) window.location.href = '?view=view_my_cart_to_pay'; });
    } else {
      Swal.fire({ icon:'info', title: res.message || 'Could not add', timer:2500, showConfirmButton:false });
      btn.disabled = false; btn.innerHTML = '<i class="bi bi-cart-plus"></i> Enrol';
    }
  }).catch(() => { btn.disabled = false; btn.innerHTML = '<i class="bi bi-cart-plus"></i> Enrol'; });
}

/* ════════════════════════════════════════════════════════════
   EXAMS
════════════════════════════════════════════════════════════ */
function sdLoadExams() {
  fetch('ajax/ajax_student_exam.php?action=history')
    .then(r => r.json())
    .then(res => {
      const exams = res.data || [];
      document.getElementById('sdCntExm').textContent = exams.length;

      const list = document.getElementById('sdExamList');
      if (!exams.length) {
        list.innerHTML = `<div class="sd-empty">
          <span class="sd-empty-icon"><i class="bi bi-clipboard"></i></span>
          <div class="sd-empty-title">No exams taken yet</div>
          <div class="sd-empty-sub">Head to the Exam Portal to start your first exam.</div>
          <a href="?view=student_exams" class="sd-hero-btn sd-hero-btn-p" style="background:#4f46e5;color:#fff;margin-top:.85rem;display:inline-flex">
            <i class="bi bi-mortarboard-fill"></i>Go to Exams
          </a></div>`;
        return;
      }

      list.innerHTML = exams.slice(0, 10).map(e => {
        const pct     = e.total_marks > 0 ? Math.round(e.score / e.total_marks * 100) : 0;
        const passed  = parseFloat(e.score) >= parseFloat(e.passing_marks||0);
        const col     = e.status === 'submitted' || e.status === 'graded'
                        ? (passed ? '#059669' : '#dc2626') : '#f59e0b';
        const icon    = e.status === 'in_progress' ? 'bi-hourglass-split' : (passed ? 'bi-trophy-fill' : 'bi-x-circle-fill');
        const bgIcon  = e.status === 'in_progress' ? '#fef3c7' : (passed ? '#dcfce7' : '#fee2e2');
        const date    = e.submitted_at ? new Date(e.submitted_at).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}) : 'In progress';
        const scoreDisplay = e.status === 'in_progress'
          ? `<span style="font-size:.72rem;color:#f59e0b;font-weight:700">In Progress</span>`
          : `<div class="sd-exam-score" style="color:${col}">${pct}%</div>`;
        const actionBtn = e.status === 'in_progress'
          ? `<button class="sd-exam-btn" style="background:#fef3c7;color:#92400e" onclick="sdResumeExam(${e.session_id},${e.exam_id})"><i class="bi bi-play-fill"></i> Resume</button>`
          : `<button class="sd-exam-btn" style="background:#ede9fe;color:#4f46e5" onclick="window.location.href='?view=student_exam_results&session_id=${e.session_id}'"><i class="bi bi-bar-chart-line-fill"></i> Results</button>`;
        return `<div class="sd-exam-row">
          <div class="sd-exam-icon" style="background:${bgIcon};color:${col}"><i class="bi ${icon}"></i></div>
          <div class="sd-exam-info">
            <div class="sd-exam-title">${sdEsc(e.exam_title||'Exam')}</div>
            <div class="sd-exam-meta"><i class="bi bi-calendar2 me-1"></i>${date} · ${e.subject_name||''}</div>
          </div>
          ${scoreDisplay}
          ${actionBtn}
        </div>`;
      }).join('');
    })
    .catch(() => {
      document.getElementById('sdExamList').innerHTML = `<div class="sd-empty"><div class="sd-empty-title">Could not load exams</div></div>`;
    });
}

function sdResumeExam(sessionId, examId) {
  window.location.href = `?view=student_take_exam&session_id=${sessionId}`;
}

/* ════════════════════════════════════════════════════════════
   HELPERS
════════════════════════════════════════════════════════════ */
function sdEsc(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

Object.assign(window, {
  sdSwitch, sdStart, sdView, sdDoBrowse, sdDebounceBrowse,
  sdToggleWish, sdAddCart, sdEnrollFree, sdResumeExam, sdLoadCartCount
});
</script>
