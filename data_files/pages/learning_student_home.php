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

/* ── Interest check ────────────────────────────────────────── */
$_hasInterests  = false;
$_interestCats  = [];
$_interestColors= ['#6366f1','#10b981','#f59e0b','#3b82f6','#8b5cf6','#ec4899','#14b8a6','#f97316','#84cc16','#06b6d4','#a855f7','#ef4444'];
if ($_usr) {
    $__ic = $db->query("SELECT COUNT(*) FROM tbl_student_interests WHERE student_id='".$db->real_escape_string($_usr)."'");
    $_hasInterests = (int)$__ic->fetch_row()[0] > 0;
    if (!$_hasInterests) {
        $__cr = $db->query("SELECT id, category_title, icon, category_code FROM tbl_course_categories WHERE status=1 ORDER BY sort_order, id");
        if ($__cr) $_interestCats = $__cr->fetch_all(MYSQLI_ASSOC);
    }
}
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
.sd-brow-wish  { position:absolute;top:.55rem;right:.55rem;width:32px;height:32px;border-radius:50%;
                 background:rgba(255,255,255,.92);border:none;cursor:pointer;display:flex;
                 align-items:center;justify-content:center;font-size:.88rem;
                 color:#94a3b8;   /* grey when NOT wishlisted */
                 transition:color .2s,background .2s,transform .2s,box-shadow .2s;
                 box-shadow:0 2px 8px rgba(0,0,0,.15); }
.sd-brow-wish:hover { background:#fff;color:#dc2626;transform:scale(1.12); }
/* Red filled heart with glow when wishlisted */
.sd-brow-wish.active {
    background:#fff0f0;
    color:#dc2626;
    box-shadow:0 2px 10px rgba(220,38,38,.35);
}
.sd-brow-wish.active:hover { transform:scale(1.12);box-shadow:0 4px 16px rgba(220,38,38,.45); }
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

  <!-- Filter bar -->
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

  <!-- ══ Section 1: Recommended / For You ══ -->
  <div id="sdForYouSection">
    <div class="sd-sec-head" style="margin-bottom:.75rem">
      <div class="sd-sec-title">
        <i class="bi bi-stars me-2" style="color:#f59e0b"></i>
        <span id="sdForYouLabel">Recommended For You</span>
      </div>
      <span id="sdForYouCount" style="font-size:.75rem;font-weight:700;color:#94a3b8;background:#f1f5f9;padding:.2rem .7rem;border-radius:20px"></span>
    </div>
    <div class="sd-browse-grid" id="sdForYouGrid">
      <?php for ($i=0;$i<3;$i++): ?>
      <div class="sd-brow-card"><div class="sd-brow-thumb sd-skel" style="border-radius:0;height:130px"></div>
        <div class="sd-brow-body"><div class="sd-skel" style="height:10px;width:40%;margin-bottom:8px"></div>
          <div class="sd-skel" style="height:14px;margin-bottom:4px"></div>
          <div class="sd-skel" style="height:34px;border-radius:9px;margin-top:12px"></div></div></div>
      <?php endfor; ?>
    </div>
  </div>

  <!-- Divider -->
  <div id="sdExploreDivider" style="display:flex;align-items:center;gap:.75rem;margin:1.5rem 0 .75rem">
    <div style="flex:1;height:1px;background:linear-gradient(90deg,#e0e7ff,transparent)"></div>
    <span style="font-size:.7rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;white-space:nowrap">
      <i class="bi bi-compass me-1"></i>Explore More Courses
    </span>
    <div style="flex:1;height:1px;background:linear-gradient(270deg,#e0e7ff,transparent)"></div>
  </div>

  <!-- ══ Section 2: Explore More ══ -->
  <div id="sdExploreSection">
    <div class="sd-browse-grid" id="sdBrowseGrid">
      <?php for ($i=0;$i<6;$i++): ?>
      <div class="sd-brow-card"><div class="sd-brow-thumb sd-skel" style="border-radius:0;height:130px"></div>
        <div class="sd-brow-body"><div class="sd-skel" style="height:10px;width:40%;margin-bottom:8px"></div>
          <div class="sd-skel" style="height:14px;margin-bottom:4px"></div>
          <div class="sd-skel" style="height:34px;border-radius:9px;margin-top:12px"></div></div></div>
      <?php endfor; ?>
    </div>
    <div id="sdBrowsePager" style="text-align:center;margin-top:1.2rem"></div>
  </div>

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
          ? `<button class="sd-enr-btn sd-enr-btn-done" onclick="sdView('${c.course_token}')"><i class="bi bi-arrow-repeat"></i> Review Course</button>`
          : `<button class="sd-enr-btn sd-enr-btn-go" onclick="sdStart('${c.course_token}')"><i class="bi bi-play-fill"></i> ${pct > 0 ? 'Continue' : 'Start'}</button>`;
        const orgChip = c.via_org ? `<span style="display:inline-flex;align-items:center;gap:.25rem;font-size:.65rem;font-weight:700;background:linear-gradient(135deg,#e0e7ff,#ede9fe);color:#6366f1;border-radius:2rem;padding:.15rem .55rem;margin-bottom:.3rem"><i class="bi bi-building-fill"></i>Organization</span>` : '';
        return `<div class="sd-enr-card">
          <div class="sd-enr-thumb">
            <img src="${sdEsc(thumb)}" alt="" onerror="this.src='uploads/course_default.png'">
            ${badge}
          </div>
          <div class="sd-enr-body">
            ${orgChip}
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

function sdStart(tok) { window.location.href = '?view=read_course_details_data&course_id=' + encodeURIComponent(tok); }
function sdView(tok)  { window.location.href = '?view=view_course_details&course_id=' + encodeURIComponent(tok); }

/* ════════════════════════════════════════════════════════════
   BROWSE COURSES
════════════════════════════════════════════════════════════ */
function sdDebounceBrowse() {
  clearTimeout(sdBrowseTimer);
  sdBrowseTimer = setTimeout(sdDoBrowse, 380);
}

/* ── Skeletons helper ── */
function sdSkel(n, h) {
  return Array(n).fill(`<div class="sd-brow-card">
    <div class="sd-brow-thumb sd-skel" style="border-radius:0;height:${h||130}px"></div>
    <div class="sd-brow-body">
      <div class="sd-skel" style="height:10px;width:40%;margin-bottom:8px"></div>
      <div class="sd-skel" style="height:14px;margin-bottom:4px"></div>
      <div class="sd-skel" style="height:34px;border-radius:9px;margin-top:12px"></div>
    </div></div>`).join('');
}

function sdDoBrowse() {
  const search = (document.getElementById('sdSearch')?.value || '').trim();
  const price  = document.getElementById('sdFilterPrice')?.value || '';
  const cat    = document.getElementById('sdFilterCat')?.value   || '';
  const hasFilter = search || price || cat;

  const fyGrid   = document.getElementById('sdForYouGrid');
  const exGrid   = document.getElementById('sdBrowseGrid');
  const divider  = document.getElementById('sdExploreDivider');
  const fySection= document.getElementById('sdForYouSection');
  const fyLabel  = document.getElementById('sdForYouLabel');
  const fyCount  = document.getElementById('sdForYouCount');

  /* Show skeletons */
  fyGrid.innerHTML = sdSkel(3);
  exGrid.innerHTML = sdSkel(6);

  if (hasFilter) {
    /* ── FILTER MODE: collapse "For You" section, show flat search results below ── */
    fySection.style.display = 'none';
    divider.style.display   = 'none';

    fetch(`ajax/ajax_get_published_courses.php?search=${encodeURIComponent(search)}&price=${encodeURIComponent(price)}&category=${encodeURIComponent(cat)}`)
      .then(r => r.json())
      .then(res => {
        const arr = Array.isArray(res.data || res) ? (res.data || res) : [];
        document.getElementById('sdCntBrw').textContent = arr.length;
        if (!arr.length) {
          exGrid.innerHTML = `<div class="sd-empty" style="grid-column:1/-1">
            <span class="sd-empty-icon"><i class="bi bi-search"></i></span>
            <div class="sd-empty-title">No courses found</div>
            <div class="sd-empty-sub">Try a different search or filter.</div></div>`;
          return;
        }
        exGrid.innerHTML = arr.map(c => sdBrowseCard(c)).join('');
      })
      .catch(() => {
        exGrid.innerHTML = `<div class="sd-empty" style="grid-column:1/-1"><div class="sd-empty-title">Could not load courses</div></div>`;
      });
    return;
  }

  /* ── DISCOVERY MODE: two sections ── */
  fySection.style.display = '';
  divider.style.display   = '';

  /* Fetch recommended (interest + level matched) and explore (everything else) in parallel */
  Promise.all([
    fetch('ajax/ajax_recommendations.php?action=recommended&limit=12').then(r => r.json()).catch(() => ({status:'error',data:[]})),
    fetch('ajax/ajax_recommendations.php?action=explore&limit=18').then(r => r.json()).catch(() => ({status:'error',data:[]})),
  ]).then(function([recRes, expRes]) {

    var recCourses = recRes.data  || [];
    var expCourses = expRes.data  || [];
    var hasInterests = recRes.has_interests !== false;

    /* Update total tab count */
    document.getElementById('sdCntBrw').textContent = recCourses.length + expCourses.length;

    /* ── For You section ── */
    if (!hasInterests || recCourses.length === 0) {
      /* No interests set — show prompt */
      fyLabel.textContent = 'Popular Courses';
      fySection.innerHTML = `
        <div class="sd-sec-head" style="margin-bottom:.75rem">
          <div class="sd-sec-title">
            <i class="bi bi-fire me-2" style="color:#f97316"></i>
            <span>Popular Courses</span>
          </div>
        </div>
        <div class="sd-browse-grid" id="sdForYouGrid">
          ${recCourses.length ? recCourses.map(c => sdRecCard(c)).join('') :
            `<div class="sd-empty" style="grid-column:1/-1">
              <span class="sd-empty-icon"><i class="bi bi-stars"></i></span>
              <div class="sd-empty-title">Set your interests</div>
              <div class="sd-empty-sub">
                <a href="?view=student_interests" style="color:#4f46e5;font-weight:700">
                  <i class="bi bi-pencil-fill me-1"></i>Choose what you love
                </a> and we'll recommend the perfect courses.
              </div>
            </div>`
          }
        </div>`;
    } else {
      fyLabel.textContent = 'Recommended For You';
      fyCount.textContent = recCourses.length + ' courses';
      fyGrid.innerHTML    = recCourses.map(c => sdRecCard(c)).join('');
    }

    /* ── Explore section ── */
    if (!expCourses.length) {
      divider.style.display = 'none';
      document.getElementById('sdExploreSection').style.display = 'none';
    } else {
      divider.style.display = '';
      document.getElementById('sdExploreSection').style.display = '';
      exGrid.innerHTML = expCourses.map(c => sdBrowseCard(c)).join('');
    }
  });
}

/* ── Recommendation card (slightly enhanced style) ── */
function sdRecCard(c) {
  /* Recommendation cards get a coloured interest badge + slight visual differentiation */
  const base = sdBrowseCard(c);
  /* Inject a "★ Matches your interests" ribbon on the card thumb */
  const catCode = c.category_code || '';
  const badge   = c.category_title
    ? `<div style="position:absolute;top:8px;left:8px;background:rgba(79,70,229,.92);color:#fff;font-size:.62rem;font-weight:800;padding:2px 8px;border-radius:20px;backdrop-filter:blur(4px);z-index:2;display:flex;align-items:center;gap:.3rem"><i class="bi bi-stars" style="font-size:.65rem"></i>${sdEsc(c.category_title)}</div>`
    : '';
  /* Inject badge after the first <div class="sd-brow-thumb"> */
  return base.replace('<div class="sd-brow-thumb">', '<div class="sd-brow-thumb" style="position:relative">' + badge);
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
          ? `<button class="sd-brow-btn sd-brow-btn-view" onclick="sdStart('${c.course_token}')" style="flex:2"><i class="bi bi-play-fill"></i> Continue Learning</button>`
          : price === 0
            ? `<button class="sd-brow-btn sd-brow-btn-cart" onclick="sdEnrollFree(${c.id},this)" style="background:#dcfce7;color:#166534"><i class="bi bi-gift"></i> Enrol Free</button>
               <button class="sd-brow-btn sd-brow-btn-view" onclick="sdView('${c.course_token}')"><i class="bi bi-eye"></i> View</button>`
            : `<button class="sd-brow-btn sd-brow-btn-cart" onclick="sdAddCart(${c.id},this)"><i class="bi bi-cart-plus"></i> Enrol</button>
               <button class="sd-brow-btn sd-brow-btn-view" onclick="sdView('${c.course_token}')"><i class="bi bi-eye"></i> View</button>`
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
      if (sdWishlisted.has(id)) {
        sdWishlisted.delete(id);
        btn.classList.remove('active');
        btn.innerHTML = '<i class="bi bi-heart"></i>';
      } else {
        sdWishlisted.add(id);
        btn.classList.add('active');
        btn.innerHTML = '<i class="bi bi-heart-fill"></i>';
        /* pop animation */
        btn.style.transform = 'scale(1.35)';
        setTimeout(function(){ btn.style.transform = ''; }, 220);
      }
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

/* ── Interest onboarding check ── */
<?php if (!$_hasInterests && !empty($_interestCats)): ?>
(function() {
  /* Only show once per browser session */
  if (sessionStorage.getItem('dcm_int_seen')) return;
  sessionStorage.setItem('dcm_int_seen', '1');
  setTimeout(function() {
    var m = document.getElementById('sdInterestModal');
    if (m) new bootstrap.Modal(m, {backdrop:'static', keyboard:false}).show();
  }, 1200);
})();
<?php endif; ?>
</script>

<?php if (!$_hasInterests && !empty($_interestCats)): ?>
<!-- ══ Interest Onboarding Modal ═══════════════════════════════ -->
<style>
@keyframes int-pop{0%{transform:scale(.85) translateY(20px);opacity:0}60%{transform:scale(1.02)}100%{transform:scale(1);opacity:1}}
@keyframes int-orb1{from{transform:translate(0,0) scale(1)}to{transform:translate(-16px,12px) scale(1.2)}}
@keyframes int-orb2{from{transform:translate(0,0) scale(1)}to{transform:translate(14px,-16px) scale(1.15)}}
@keyframes int-card{from{opacity:0;transform:translateY(14px) scale(.94)}to{opacity:1;transform:none}}
@keyframes int-check{0%{transform:scale(0)}60%{transform:scale(1.3)}100%{transform:scale(1)}}

#sdInterestModal .modal-dialog{max-width:680px;animation:int-pop .45s cubic-bezier(.34,1.56,.64,1) both}
#sdInterestModal .modal-content{border:none;border-radius:24px;overflow:hidden;box-shadow:0 32px 100px rgba(0,0,0,.25)}

/* Header */
.int-hdr{position:relative;overflow:hidden;background:linear-gradient(135deg,#050510 0%,#0f0c29 40%,#1e1040 70%,#2d1b69 100%);padding:1.85rem 1.75rem 1.5rem;color:#fff}
.int-orb{position:absolute;border-radius:50%;filter:blur(45px);pointer-events:none}
.int-orb-1{width:180px;height:180px;background:rgba(99,102,241,.32);top:-60px;right:-30px;animation:int-orb1 7s ease-in-out infinite alternate}
.int-orb-2{width:120px;height:120px;background:rgba(139,92,246,.25);bottom:-30px;right:160px;animation:int-orb2 9s ease-in-out infinite alternate}
.int-hdr-inner{position:relative;z-index:2}
.int-hdr-icon{width:56px;height:56px;border-radius:16px;background:rgba(255,255,255,.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:1.55rem;margin-bottom:1rem;box-shadow:0 6px 24px rgba(99,102,241,.4)}
.int-hdr-title{font-size:1.25rem;font-weight:900;letter-spacing:-.02em;line-height:1.1}
.int-hdr-title span{background:linear-gradient(90deg,#a5b4fc,#f9a8d4,#6ee7b7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.int-hdr-sub{font-size:.82rem;opacity:.55;margin-top:.3rem}
.int-hdr-skip{position:absolute;top:.9rem;right:.9rem;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);color:rgba(255,255,255,.7);font-size:.73rem;font-weight:700;border-radius:20px;padding:.25rem .8rem;cursor:pointer;transition:all .2s;z-index:3}
.int-hdr-skip:hover{background:rgba(255,255,255,.2);color:#fff}

/* Count badge */
.int-count{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border-radius:20px;padding:2px 10px;font-size:.72rem;font-weight:700;box-shadow:0 2px 8px rgba(99,102,241,.35);transition:all .2s}

/* Search */
.int-search{border:1.5px solid #e0e7ff;border-radius:12px;padding:.45rem .85rem .45rem 2.2rem;font-size:.83rem;background:#f8f7ff;transition:all .2s;width:100%}
.int-search:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1);background:#fff}

/* Category grid */
.int-cat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(105px,1fr));gap:.55rem;max-height:280px;overflow-y:auto;padding:.1rem .05rem}
.int-cat-grid::-webkit-scrollbar{width:4px}
.int-cat-grid::-webkit-scrollbar-thumb{background:rgba(99,102,241,.2);border-radius:4px}
.int-cat-tile{border:2px solid #e0e7ff;border-radius:13px;padding:.6rem .45rem;cursor:pointer;text-align:center;transition:all .2s;user-select:none;background:#fff;position:relative;animation:int-card .3s ease both}
.int-cat-tile:nth-child(1){animation-delay:.03s}.int-cat-tile:nth-child(2){animation-delay:.06s}
.int-cat-tile:nth-child(3){animation-delay:.09s}.int-cat-tile:nth-child(4){animation-delay:.12s}
.int-cat-tile:nth-child(5){animation-delay:.15s}.int-cat-tile:nth-child(6){animation-delay:.18s}
.int-cat-tile:hover{border-color:#a5b4fc;box-shadow:0 4px 14px rgba(99,102,241,.13);transform:translateY(-3px)}
.int-cat-tile.selected{border-color:#6366f1;background:linear-gradient(135deg,#ede9fe,#eff6ff)}
.int-cat-tile.selected .int-tile-name{color:#4f46e5}
.int-tile-icon{width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1rem;margin:0 auto .4rem;transition:transform .2s;pointer-events:none}
.int-cat-tile:hover .int-tile-icon,.int-cat-tile.selected .int-tile-icon{transform:scale(1.12) rotate(-6deg)}
.int-tile-name{font-size:.66rem;font-weight:700;color:#334155;line-height:1.25;pointer-events:none;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;line-clamp:2;-webkit-box-orient:vertical}
.int-cat-tile.selected::after{content:'\F26E';font-family:'bootstrap-icons';position:absolute;top:3px;right:5px;font-size:.62rem;color:#6366f1;pointer-events:none;animation:int-check .22s cubic-bezier(.34,1.56,.64,1) both}

/* Footer */
.int-footer{background:#f8f7ff;border-top:1px solid #e0e7ff;padding:1rem 1.5rem;display:flex;align-items:center;justify-content:space-between}
.int-save-btn{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:12px;padding:.58rem 1.5rem;font-size:.84rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.45rem;transition:all .2s;box-shadow:0 4px 16px rgba(99,102,241,.4)}
.int-save-btn:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(99,102,241,.55)}
.int-save-btn:disabled{opacity:.55;transform:none}
.int-later-btn{font-size:.8rem;color:#94a3b8;background:none;border:none;cursor:pointer;font-weight:600;transition:color .2s;padding:.4rem}
.int-later-btn:hover{color:#6366f1}
</style>

<div class="modal fade" id="sdInterestModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

    <!-- Header -->
    <div class="int-hdr">
        <div class="int-orb int-orb-1"></div>
        <div class="int-orb int-orb-2"></div>
        <button class="int-hdr-skip" id="intSkipBtn">Skip for now</button>
        <div class="int-hdr-inner">
            <div class="int-hdr-icon"><i class="bi bi-stars"></i></div>
            <div class="int-hdr-title">Welcome, <span><?= $_fname ?></span>! 🎉</div>
            <div class="int-hdr-title" style="-webkit-text-fill-color:#fff;background:none;font-size:.95rem;font-weight:700;margin-top:.25rem">Tell us what you love to learn</div>
            <div class="int-hdr-sub">Pick your subjects — we'll personalise every course recommendation just for you</div>
        </div>
    </div>

    <!-- Body -->
    <div class="modal-body" style="padding:1.4rem;background:#fafbff">
        <!-- Top row -->
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="small fw-semibold text-muted">Choose your areas of interest</span>
            <span class="int-count ms-auto" id="intSelCount">0 selected</span>
        </div>
        <!-- Search -->
        <div class="position-relative mb-3">
            <i class="bi bi-search position-absolute" style="left:.72rem;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:.82rem;pointer-events:none"></i>
            <input class="int-search" id="intSearch" placeholder="Search subjects…" autocomplete="off">
        </div>
        <!-- Category grid -->
        <div class="int-cat-grid" id="intCatGrid">
            <?php foreach ($_interestCats as $ci => $cat):
                $cc = $_interestColors[$ci % count($_interestColors)]; ?>
            <div class="int-cat-tile"
                 data-id="<?= $cat['id'] ?>"
                 data-name="<?= htmlspecialchars($cat['category_title']) ?>"
                 data-search="<?= strtolower(htmlspecialchars($cat['category_title'])) ?>">
                <div class="int-tile-icon" style="background:<?= $cc ?>18;color:<?= $cc ?>">
                    <i class="bi <?= htmlspecialchars($cat['icon'] ?? 'bi-grid') ?>"></i>
                </div>
                <div class="int-tile-name"><?= htmlspecialchars($cat['category_title']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <!-- Tip -->
        <div class="d-flex align-items-start gap-2 mt-3 p-3 rounded-3" style="background:#f0f6ff;border:1.5px solid #c7d9fc">
            <i class="bi bi-lightbulb-fill text-primary mt-1" style="font-size:.85rem;flex-shrink:0"></i>
            <p class="mb-0 text-muted" style="font-size:.75rem;line-height:1.5">
                Select <strong>3 or more</strong> subjects for the best recommendations. You can always update these from <strong>My Interests</strong> in the menu.
            </p>
        </div>
    </div>

    <!-- Footer -->
    <div class="int-footer">
        <button class="int-later-btn" id="intLaterBtn"><i class="bi bi-clock me-1"></i>Remind me later</button>
        <button class="int-save-btn" id="intSaveBtn" disabled>
            <i class="bi bi-check-lg"></i> Save My Interests
        </button>
    </div>

</div>
</div>
</div>

<script>
(function() {
    var _intSel  = new Set();
    var _intModal = null;

    function getModal() {
        if (!_intModal) _intModal = bootstrap.Modal.getInstance(document.getElementById('sdInterestModal'))
                                 || new bootstrap.Modal(document.getElementById('sdInterestModal'), {backdrop:'static', keyboard:false});
        return _intModal;
    }

    /* ── Tile grid delegation ── */
    var grid = document.getElementById('intCatGrid');
    if (grid) {
        grid.addEventListener('click', function(e) {
            var tile = e.target.closest('.int-cat-tile');
            if (!tile) return;
            var id = tile.dataset.id;
            if (_intSel.has(id)) { _intSel.delete(id); tile.classList.remove('selected'); }
            else                  { _intSel.add(id);    tile.classList.add('selected'); }
            updateCount();
        });
    }

    /* ── Search ── */
    var srch = document.getElementById('intSearch');
    if (srch) {
        srch.addEventListener('input', function() {
            var lq = this.value.toLowerCase();
            document.querySelectorAll('.int-cat-tile').forEach(function(t) {
                t.style.display = (t.dataset.search||'').includes(lq) ? '' : 'none';
            });
        });
    }

    function updateCount() {
        var n = _intSel.size;
        var badge = document.getElementById('intSelCount');
        var btn   = document.getElementById('intSaveBtn');
        if (badge) badge.textContent = n + ' selected';
        if (btn) btn.disabled = n === 0;
    }

    /* ── Skip / Later ── */
    function dismiss() {
        sessionStorage.setItem('dcm_int_seen', '1');
        getModal().hide();
    }
    var skipBtn  = document.getElementById('intSkipBtn');
    var laterBtn = document.getElementById('intLaterBtn');
    if (skipBtn)  skipBtn.addEventListener('click',  dismiss);
    if (laterBtn) laterBtn.addEventListener('click', function() {
        /* Remove the session flag so it will show again next visit */
        sessionStorage.removeItem('dcm_int_seen');
        getModal().hide();
    });

    /* ── Save ── */
    var saveBtn = document.getElementById('intSaveBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', async function() {
            var ids = Array.from(_intSel);
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';
            var fd = new FormData();
            fd.append('action', 'save_interests');
            ids.forEach(function(id) { fd.append('category_ids[]', id); });
            try {
                var res = await fetch('ajax/ajax_recommendations.php', {method:'POST', body:fd}).then(function(r){ return r.json(); });
                if (res.status === 'success') {
                    getModal().hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'All set! 🎯',
                        html: '<p>We\'ve saved <strong>' + ids.length + ' interest' + (ids.length!==1?'s':'') + '</strong>.<br>Your dashboard will now show personalised recommendations!</p>',
                        confirmButtonText: 'Let\'s go!',
                        confirmButtonColor: '#6366f1',
                        timer: 4000
                    });
                } else {
                    Swal.fire({icon:'error', title:'Error', text:res.message});
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="bi bi-check-lg"></i> Save My Interests';
                }
            } catch(e) {
                Swal.fire({icon:'error', title:'Network Error', text:'Please try again.'});
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-check-lg"></i> Save My Interests';
            }
        });
    }
})();
</script>
<?php endif; ?>
