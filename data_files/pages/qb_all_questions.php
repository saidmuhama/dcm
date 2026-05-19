<?php
$view = $_GET['view'] ?? 'qb_all_questions';

$statusMap = [
    'qb_all_questions'       => ['label'=>'All Questions', 'status'=>'',          'icon'=>'bi-collection-fill'],
    'qb_draft_questions'     => ['label'=>'Draft',         'status'=>'draft',     'icon'=>'bi-pencil-square'],
    'qb_review_queue'        => ['label'=>'Review Queue',  'status'=>'review',    'icon'=>'bi-eye-fill'],
    'qb_approved_questions'  => ['label'=>'Approved',      'status'=>'approved',  'icon'=>'bi-patch-check-fill'],
    'qb_published_questions' => ['label'=>'Published',     'status'=>'published', 'icon'=>'bi-check-circle-fill'],
    'qb_archived_questions'  => ['label'=>'Archived',      'status'=>'archived',  'icon'=>'bi-archive-fill'],
];

$current      = $statusMap[$view] ?? $statusMap['qb_all_questions'];
$filterStatus = $current['status'];

$heroConfigs = [
    ''          => ['grad'=>'linear-gradient(135deg,#0a0f1e 0%,#0d1b3e 45%,#111827 100%)', 'orb1'=>'rgba(26,79,196,.45)',   'orb2'=>'rgba(109,40,217,.35)',  'sub'=>'Browse, filter and manage your entire question bank across all subjects, levels and chapters.'],
    'draft'     => ['grad'=>'linear-gradient(135deg,#1c1200 0%,#2d1c00 45%,#44260a 100%)', 'orb1'=>'rgba(217,119,6,.48)',   'orb2'=>'rgba(245,158,11,.32)',  'sub'=>'Draft questions are works-in-progress. Refine and submit for review when ready.'],
    'review'    => ['grad'=>'linear-gradient(135deg,#001226 0%,#001e40 45%,#002855 100%)', 'orb1'=>'rgba(8,145,178,.48)',   'orb2'=>'rgba(14,165,233,.32)',  'sub'=>'Questions awaiting expert review before they can be approved and published.'],
    'approved'  => ['grad'=>'linear-gradient(135deg,#052e16 0%,#064e3b 45%,#065f46 100%)', 'orb1'=>'rgba(5,150,105,.48)',   'orb2'=>'rgba(13,148,136,.32)',  'sub'=>'Approved questions are quality-checked and ready to be published live.'],
    'published' => ['grad'=>'linear-gradient(135deg,#0b1120 0%,#0f1e3d 45%,#1a1040 100%)', 'orb1'=>'rgba(26,79,196,.48)',   'orb2'=>'rgba(99,102,241,.35)',  'sub'=>'Live questions available in student exams and practice sessions.'],
    'archived'  => ['grad'=>'linear-gradient(135deg,#111827 0%,#1f2937 45%,#374151 100%)', 'orb1'=>'rgba(107,114,128,.45)', 'orb2'=>'rgba(75,85,99,.30)',    'sub'=>'Archived questions are retired and no longer active in any exam.'],
];
$hero = $heroConfigs[$filterStatus] ?? $heroConfigs[''];

$allNav = [
    'qb_subjects'          => ['icon'=>'bi-book-fill',        'label'=>'Subjects'],
    'qb_levels'            => ['icon'=>'bi-layers-fill',      'label'=>'Levels'],
    'qb_chapters'          => ['icon'=>'bi-bookmark-fill',    'label'=>'Chapters'],
    'qb_subtopics'         => ['icon'=>'bi-bookmarks-fill',   'label'=>'Subtopics'],
    'qb_bloom_levels'      => ['icon'=>'bi-bar-chart-steps',  'label'=>"Bloom's"],
    'qb_difficulty_levels' => ['icon'=>'bi-speedometer2',     'label'=>'Difficulty'],
    'qb_sections'          => ['icon'=>'bi-grid-fill',        'label'=>'Sections'],
];
?>

<style>
/* ═══════════════════════════════════════════════════════════
   QB QUESTIONS — PREMIUM UI  (qbq-*)
═══════════════════════════════════════════════════════════ */
.qbq-wrap { font-family:'Open Sans',sans-serif; }

/* ── Hero ── */
.qbq-hero { position:relative; overflow:hidden; isolation:isolate; border-radius:20px; padding:2rem 2.2rem; margin-bottom:1.4rem; }
.qbq-hero-grid { position:absolute; inset:0; z-index:0; background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px); background-size:44px 44px; }
.qbq-hero-inner { position:relative; z-index:1; }
.qbq-hero-badge { display:inline-flex; align-items:center; gap:.4rem; background:rgba(255,255,255,.09); border:1px solid rgba(255,255,255,.15); border-radius:100px; padding:.28rem .85rem; font-size:.7rem; font-weight:700; color:rgba(255,255,255,.7); letter-spacing:.06em; text-transform:uppercase; margin-bottom:.75rem; backdrop-filter:blur(6px); }
.qbq-hero-title { font-size:1.7rem; font-weight:900; color:#fff; font-family:'SUSE',sans-serif; letter-spacing:-.04em; line-height:1.15; margin-bottom:.3rem; }
.qbq-hero-title em { font-style:normal; background:linear-gradient(90deg,#60a5fa,#c084fc); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent; }
.qbq-hero-sub { font-size:.81rem; color:rgba(255,255,255,.45); margin-bottom:1.4rem; max-width:540px; line-height:1.6; }
.qbq-kpis { display:flex; gap:.7rem; flex-wrap:wrap; }
.qbq-kpi { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12); border-radius:14px; padding:.6rem 1rem; backdrop-filter:blur(8px); min-width:90px; transition:background .2s; cursor:default; }
.qbq-kpi:hover { background:rgba(255,255,255,.13); }
.qbq-kpi-val { font-size:1.2rem; font-weight:900; color:#fff; font-family:'SUSE',sans-serif; line-height:1; }
.qbq-kpi-lbl { font-size:.63rem; color:rgba(255,255,255,.45); margin-top:.15rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; }

/* ── Hero side panel ── */
.qbq-side-panel { background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.11); border-radius:16px; padding:.9rem 1.1rem; backdrop-filter:blur(10px); min-width:180px; }
.qbq-side-title { font-size:.63rem; color:rgba(255,255,255,.38); font-weight:700; text-transform:uppercase; letter-spacing:.07em; margin-bottom:.65rem; }
.qbq-nav-link { display:flex; align-items:center; gap:.55rem; padding:.32rem .55rem; border-radius:9px; text-decoration:none; font-size:.75rem; font-weight:600; color:rgba(255,255,255,.6); transition:all .15s; white-space:nowrap; }
.qbq-nav-link:hover { background:rgba(255,255,255,.1); color:#fff; }
.qbq-add-btn { display:inline-flex; align-items:center; gap:.45rem; background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff; border:none; border-radius:12px; padding:.6rem 1.3rem; font-size:.82rem; font-weight:700; cursor:pointer; font-family:inherit; box-shadow:0 4px 18px rgba(26,79,196,.4); transition:filter .18s,transform .12s; white-space:nowrap; text-decoration:none; }
.qbq-add-btn:hover { filter:brightness(1.1); transform:translateY(-1px); color:#fff; }

/* ── Status nav ── */
.qbq-status-nav { display:flex; gap:.4rem; flex-wrap:wrap; margin-bottom:1.2rem; }
.qbq-spill { display:inline-flex; align-items:center; gap:.45rem; padding:.38rem .95rem; border-radius:100px; font-size:.77rem; font-weight:700; text-decoration:none; border:1.5px solid #e2e8f0; background:#fff; color:#475569; transition:all .17s; white-space:nowrap; box-shadow:0 1px 3px rgba(0,0,0,.04); }
.qbq-spill:hover:not(.active) { border-color:#cbd5e1; color:#0f172a; }
.qbq-spill.active { background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff !important; border-color:transparent; box-shadow:0 4px 14px rgba(26,79,196,.35); }
.qbq-spill-cnt { font-size:.66rem; font-weight:900; padding:.05rem .42rem; border-radius:100px; background:rgba(0,0,0,.08); min-width:20px; text-align:center; }
.qbq-spill.active .qbq-spill-cnt { background:rgba(255,255,255,.22); }

/* ── Toolbar ── */
.qbq-toolbar { display:flex; align-items:center; gap:.65rem; flex-wrap:wrap; background:#fff; border-radius:16px; padding:.85rem 1.1rem; box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 14px rgba(0,0,0,.05); border:1px solid #f0f4f8; margin-bottom:.9rem; }
.qbq-search-wrap { position:relative; flex:1; min-width:180px; }
.qbq-search-wrap i { position:absolute; left:.75rem; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:.84rem; pointer-events:none; }
.qbq-search { width:100%; padding:.5rem .85rem .5rem 2.2rem; border-radius:10px; border:1.5px solid #e2e8f0; font-size:.82rem; font-family:inherit; outline:none; background:#f8fafc; color:#1e293b; transition:border-color .18s,box-shadow .18s; }
.qbq-search:focus { border-color:#1a4fc4; box-shadow:0 0 0 3px rgba(26,79,196,.1); background:#fff; }
.qbq-sel { padding:.5rem .75rem; border-radius:10px; border:1.5px solid #e2e8f0; font-size:.79rem; font-family:inherit; outline:none; background:#f8fafc; color:#475569; cursor:pointer; transition:border-color .18s; }
.qbq-sel:focus { border-color:#1a4fc4; }
.qbq-view-btns { display:flex; gap:.3rem; }
.qbq-view-btn { width:34px; height:34px; border-radius:9px; border:1.5px solid #e2e8f0; background:#f8fafc; color:#94a3b8; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all .15s; font-size:.88rem; }
.qbq-view-btn.active { background:#eff6ff; border-color:#bfdbfe; color:#1a4fc4; }
.qbq-view-btn:hover:not(.active) { background:#f1f5f9; color:#475569; }
.qbq-badge { background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff; border-radius:100px; padding:.25rem .8rem; font-size:.72rem; font-weight:800; white-space:nowrap; font-family:'SUSE',sans-serif; }
.qbq-reset-btn { padding:.5rem .85rem; border-radius:10px; border:1.5px solid #e2e8f0; background:#f8fafc; color:#64748b; font-size:.79rem; font-family:inherit; cursor:pointer; transition:all .15s; display:flex; align-items:center; gap:.35rem; }
.qbq-reset-btn:hover { border-color:#dc2626; color:#dc2626; background:#fff1f2; }

/* ── Filter row ── */
.qbq-filter-row { display:flex; gap:.55rem; flex-wrap:wrap; background:#fff; border-radius:14px; padding:.7rem 1rem; border:1px solid #f0f4f8; margin-bottom:1.1rem; box-shadow:0 1px 3px rgba(0,0,0,.04); }
.qbq-filter-grp { display:flex; flex-direction:column; gap:.22rem; }
.qbq-filter-lbl { font-size:.65rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.06em; }

/* ── Card grid ── */
.qbq-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:1rem; }
.qbq-grid.list-mode { grid-template-columns:1fr; gap:.55rem; }

/* ── Question card ── */
.qbq-card { background:#fff; border-radius:18px; border:1px solid #f0f4f8; box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 16px rgba(0,0,0,.05); overflow:hidden; transition:transform .22s,box-shadow .22s; animation:qbq-up .4s cubic-bezier(.16,1,.3,1) both; }
.qbq-card:hover { transform:translateY(-4px); box-shadow:0 14px 44px rgba(0,0,0,.11); }
.qbq-card.selected { outline:2px solid #1a4fc4; outline-offset:-2px; }
@keyframes qbq-up { from{opacity:0;transform:translateY(18px)} to{opacity:1;transform:translateY(0)} }
.qbq-card-accent { height:4px; }
.qbq-card-body { padding:1.1rem 1.15rem .75rem; }
.qbq-card-head { display:flex; align-items:flex-start; gap:.8rem; margin-bottom:.8rem; }
.qbq-card-icon { width:44px; height:44px; border-radius:12px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:1.18rem; color:#fff; box-shadow:0 6px 16px rgba(0,0,0,.18); }
.qbq-uid-badge { font-family:monospace; font-size:.68rem; font-weight:800; background:#0f172a; color:#e2e8f0; padding:.15rem .55rem; border-radius:7px; letter-spacing:.04em; }
.qbq-type-badge { display:inline-block; border-radius:100px; padding:.13rem .55rem; font-size:.65rem; font-weight:800; letter-spacing:.05em; }
.qbq-marks-badge { display:inline-flex; align-items:center; gap:.22rem; font-size:.67rem; font-weight:700; color:#64748b; background:#f1f5f9; border-radius:100px; padding:.13rem .5rem; }
.qbq-stem { font-size:.83rem; color:#334155; line-height:1.55; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; margin-top:.35rem; font-weight:500; }
.qbq-meta { display:flex; flex-wrap:wrap; gap:.3rem; margin-top:.65rem; }
.qbq-meta-pill { display:inline-flex; align-items:center; gap:.25rem; border-radius:100px; padding:.15rem .55rem; font-size:.65rem; font-weight:700; background:#f1f5f9; color:#475569; }
.qbq-card-foot { display:flex; align-items:center; justify-content:space-between; padding:.65rem 1.15rem; border-top:1px solid #f0f4f8; }
.qbq-status-pill { display:inline-flex; align-items:center; gap:.3rem; border-radius:100px; padding:.2rem .65rem; font-size:.67rem; font-weight:800; letter-spacing:.04em; text-transform:capitalize; }
.qbq-card-acts { display:flex; gap:.3rem; align-items:center; }
.qbq-icn { width:30px; height:30px; border-radius:8px; border:1.5px solid #e2e8f0; background:#f8fafc; color:#64748b; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all .15s; font-size:.78rem; text-decoration:none; }
.qbq-icn:hover { background:#eff6ff; border-color:#bfdbfe; color:#1a4fc4; }
.qbq-icn-del:hover { background:#fff1f2; border-color:#fecaca; color:#dc2626; }
.qbq-icn-preview:hover { background:#f0fdf4; border-color:#bbf7d0; color:#059669; }
.qbq-card-chk { width:16px; height:16px; border-radius:4px; cursor:pointer; accent-color:#1a4fc4; flex-shrink:0; }

/* ── List (table) mode ── */
.qbq-table-wrap { background:#fff; border-radius:16px; border:1px solid #f0f4f8; box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 14px rgba(0,0,0,.04); overflow:hidden; }
.qbq-table { width:100%; border-collapse:collapse; font-size:.8rem; }
.qbq-table thead th { background:#f8fafc; color:#64748b; font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; padding:.65rem 1rem; border-bottom:1px solid #e2e8f0; white-space:nowrap; }
.qbq-table tbody tr { border-bottom:1px solid #f0f4f8; cursor:pointer; transition:background .14s; }
.qbq-table tbody tr:hover { background:#f8fafc; }
.qbq-table tbody tr:last-child { border-bottom:none; }
.qbq-table td { padding:.6rem 1rem; color:#334155; vertical-align:middle; }
.qbq-table td:first-child { padding-left:.85rem; }
.qbq-tbl-status { display:inline-flex; align-items:center; gap:.3rem; border-radius:100px; padding:.17rem .6rem; font-size:.66rem; font-weight:800; letter-spacing:.04em; text-transform:capitalize; }
.qbq-tbl-type { display:inline-block; border-radius:8px; padding:.13rem .5rem; font-size:.65rem; font-weight:700; background:#f1f5f9; color:#475569; }
.qbq-tbl-uid { font-family:monospace; font-size:.72rem; font-weight:700; color:#475569; }

/* ── Skeleton ── */
.qbq-skel { background:linear-gradient(90deg,#f0f4f8 25%,#e2e8f0 50%,#f0f4f8 75%); background-size:200% 100%; animation:qbq-shim 1.5s infinite; border-radius:8px; }
@keyframes qbq-shim { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ── Empty state ── */
.qbq-empty { grid-column:1/-1; text-align:center; padding:4rem 2rem; }
.qbq-empty-icon { font-size:3rem; color:#cbd5e1; display:block; margin-bottom:1rem; }

/* ── Bulk bar ── */
.qbq-bulk-bar { position:fixed; bottom:1.5rem; left:50%; transform:translateX(-50%) translateY(100px); background:#0f172a; color:#fff; border-radius:16px; padding:.75rem 1.2rem; display:flex; align-items:center; gap:.8rem; box-shadow:0 12px 40px rgba(0,0,0,.35); transition:transform .25s cubic-bezier(.16,1,.3,1); z-index:1050; white-space:nowrap; }
.qbq-bulk-bar.visible { transform:translateX(-50%) translateY(0); }
.qbq-bulk-info { font-size:.8rem; font-weight:700; color:rgba(255,255,255,.7); }
.qbq-bulk-sel { padding:.42rem .75rem; border-radius:10px; border:none; font-size:.78rem; font-family:inherit; background:rgba(255,255,255,.12); color:#fff; outline:none; }
.qbq-bulk-sel option { background:#0f172a; }
.qbq-bulk-apply { padding:.42rem 1rem; border-radius:10px; border:none; background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff; font-size:.78rem; font-weight:700; cursor:pointer; font-family:inherit; }
.qbq-bulk-clear { padding:.42rem .8rem; border-radius:10px; border:1px solid rgba(255,255,255,.2); background:transparent; color:rgba(255,255,255,.65); font-size:.78rem; cursor:pointer; font-family:inherit; }
.qbq-bulk-clear:hover { background:rgba(255,255,255,.1); }

/* ── Pagination ── */
.qbq-pager { display:flex; justify-content:flex-end; align-items:center; gap:.55rem; padding:1rem 1.15rem; background:#fff; border-radius:0 0 16px 16px; }
.qbq-pager-info { font-size:.77rem; color:#94a3b8; font-weight:600; }
.qbq-pager-btn { padding:.38rem .85rem; border-radius:9px; border:1.5px solid #e2e8f0; background:#f8fafc; color:#475569; font-size:.77rem; font-weight:700; cursor:pointer; font-family:inherit; transition:all .15s; }
.qbq-pager-btn:hover { border-color:#1a4fc4; color:#1a4fc4; background:#eff6ff; }
.qbq-pager-btn:disabled { opacity:.4; cursor:not-allowed; }

/* ── Preview modal ── */
.qbq-modal .modal-content { border-radius:20px; border:none; box-shadow:0 24px 80px rgba(0,0,0,.18); overflow:hidden; font-family:'Open Sans',sans-serif; }
.qbq-modal .modal-header { border-bottom:none; padding:1.3rem 1.5rem; }
.qbq-modal .modal-title { font-size:.92rem; font-weight:800; color:#fff; font-family:'SUSE',sans-serif; display:flex; align-items:center; gap:.6rem; }
.qbq-modal .modal-body { padding:1.3rem 1.5rem; }
.qbq-modal .modal-footer { border-top:1px solid #f0f4f8; padding:.85rem 1.5rem; gap:.5rem; }
.qbq-preview-meta { display:grid; grid-template-columns:repeat(3,1fr); gap:.75rem; margin-bottom:1rem; }
.qbq-preview-meta-item small { font-size:.67rem; color:#94a3b8; font-weight:700; text-transform:uppercase; letter-spacing:.05em; display:block; margin-bottom:.2rem; }
.qbq-preview-meta-item span { font-size:.81rem; color:#334155; font-weight:600; }
.qbq-preview-stem { font-size:.9rem; font-weight:600; color:#0f172a; line-height:1.6; margin-bottom:1.1rem; padding-bottom:1rem; border-bottom:1px solid #f0f4f8; }
.qbq-opt { display:flex; align-items:flex-start; gap:.6rem; margin-bottom:.6rem; padding:.6rem .75rem; border-radius:10px; background:#f8fafc; border:1px solid #f0f4f8; font-size:.83rem; }
.qbq-opt.correct { background:#f0fdf4; border-color:#bbf7d0; }
.qbq-opt-lbl { font-weight:800; color:#64748b; min-width:18px; }
.qbq-opt.correct .qbq-opt-lbl { color:#059669; }
.qbq-status-change-btn { display:inline-flex; align-items:center; gap:.4rem; background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff; border:none; border-radius:10px; padding:.5rem 1.1rem; font-size:.79rem; font-weight:700; cursor:pointer; font-family:inherit; }
.qbq-modal-del { display:inline-flex; align-items:center; gap:.4rem; background:#fff1f2; color:#dc2626; border:1.5px solid #fecaca; border-radius:10px; padding:.5rem 1.1rem; font-size:.79rem; font-weight:700; cursor:pointer; font-family:inherit; }
.qbq-modal-sel { padding:.5rem .75rem; border-radius:10px; border:1.5px solid #e2e8f0; font-size:.79rem; font-family:inherit; outline:none; background:#f8fafc; }
.qbq-modal-sel:focus { border-color:#1a4fc4; }
</style>

<div class="container-fluid px-3 py-3 qbq-wrap">

<!-- ── Hero ──────────────────────────────────────────────── -->
<div class="qbq-hero" style="background:<?= $hero['grad'] ?>">
  <div class="qbq-hero-grid"></div>
  <div style="position:absolute;right:3rem;top:50%;transform:translateY(-50%);width:220px;height:220px;border-radius:50%;background:conic-gradient(from 0deg,<?= $hero['orb1'] ?>,<?= $hero['orb2'] ?>,<?= $hero['orb1'] ?>);filter:blur(42px);opacity:.55;animation:db-orb-spin 16s linear infinite;z-index:0"></div>
  <div style="position:absolute;left:32%;bottom:-40px;width:140px;height:140px;border-radius:50%;background:<?= $hero['orb2'] ?>;filter:blur(36px);opacity:.35;z-index:0"></div>

  <div class="qbq-hero-inner">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <div class="qbq-hero-badge"><i class="bi bi-patch-question-fill"></i>Question Bank</div>
        <div class="qbq-hero-title"><em><?= $current['label'] ?></em></div>
        <div class="qbq-hero-sub"><?= $hero['sub'] ?></div>
        <div class="qbq-kpis" id="heroKpis">
          <?php for ($ki=0;$ki<4;$ki++): ?>
          <div class="qbq-kpi"><div class="qbq-skel" style="width:44px;height:22px;margin-bottom:5px"></div><div class="qbq-kpi-lbl">Loading</div></div>
          <?php endfor; ?>
        </div>
      </div>
      <div class="col-lg-5 d-none d-lg-flex justify-content-end align-items-center gap-3 mt-3 mt-lg-0">
        <div class="qbq-side-panel">
          <div class="qbq-side-title">Taxonomy Setup</div>
          <?php foreach ($allNav as $vk => $vn): ?>
          <a href="?view=<?= $vk ?>" class="qbq-nav-link">
            <i class="bi <?= $vn['icon'] ?>" style="font-size:.78rem;width:16px;opacity:.75"></i><?= $vn['label'] ?>
          </a>
          <?php endforeach; ?>
        </div>
        <a href="?view=qb_add_question" class="qbq-add-btn">
          <i class="bi bi-plus-lg"></i>Add Question
        </a>
      </div>
    </div>
  </div>
</div>

<!-- ── Status nav ─────────────────────────────────────────── -->
<div class="qbq-status-nav">
  <?php foreach ($statusMap as $vk => $sm): ?>
  <a href="?view=<?= $vk ?>" class="qbq-spill<?= $vk===$view?' active':'' ?>">
    <i class="bi <?= $sm['icon'] ?>" style="font-size:.74rem"></i>
    <?= $sm['label'] ?>
    <span class="qbq-spill-cnt" id="badge_<?= $vk ?>">…</span>
  </a>
  <?php endforeach; ?>
  <a href="?view=qb_add_question" class="qbq-add-btn d-lg-none ms-auto" style="padding:.38rem .9rem;font-size:.77rem"><i class="bi bi-plus-lg"></i>Add</a>
</div>

<!-- ── Toolbar ────────────────────────────────────────────── -->
<div class="qbq-toolbar">
  <div class="qbq-search-wrap">
    <i class="bi bi-search"></i>
    <input type="text" id="fSearch" class="qbq-search" placeholder="Search UID or question text…" oninput="debounceSearch()">
  </div>
  <select id="fType" class="qbq-sel" onchange="loadQuestions()">
    <option value="">All Types</option>
    <option value="mcq">MCQ</option>
    <option value="true_false">True / False</option>
    <option value="essay">Essay</option>
    <option value="matching">Matching</option>
    <option value="fill_blank">Fill Blank</option>
  </select>
  <div class="qbq-view-btns">
    <button class="qbq-view-btn active" id="btnGrid" onclick="setView('grid')" title="Card view"><i class="bi bi-grid-fill"></i></button>
    <button class="qbq-view-btn"        id="btnList" onclick="setView('list')" title="Table view"><i class="bi bi-table"></i></button>
  </div>
  <div class="qbq-badge" id="qbqCount">…</div>
  <button class="qbq-reset-btn" onclick="resetFilters()"><i class="bi bi-x-circle"></i>Reset</button>
</div>

<!-- ── Filter row ─────────────────────────────────────────── -->
<div class="qbq-filter-row">
  <div class="qbq-filter-grp">
    <span class="qbq-filter-lbl">Subject</span>
    <select id="fSubject" class="qbq-sel" onchange="loadChapters(); loadQuestions()">
      <option value="">All Subjects</option>
    </select>
  </div>
  <div class="qbq-filter-grp">
    <span class="qbq-filter-lbl">Level</span>
    <select id="fLevel" class="qbq-sel" onchange="loadChapters(); loadQuestions()">
      <option value="">All Levels</option>
    </select>
  </div>
  <div class="qbq-filter-grp">
    <span class="qbq-filter-lbl">Chapter</span>
    <select id="fChapter" class="qbq-sel" onchange="loadQuestions()">
      <option value="">All Chapters</option>
    </select>
  </div>
  <div class="qbq-filter-grp">
    <span class="qbq-filter-lbl">Difficulty</span>
    <select id="fDifficulty" class="qbq-sel" onchange="loadQuestions()">
      <option value="">All Difficulties</option>
    </select>
  </div>
</div>

<!-- ── Card grid ──────────────────────────────────────────── -->
<div class="qbq-grid" id="qbqGrid">
  <?php for ($si=0;$si<6;$si++): ?>
  <div class="qbq-card">
    <div class="qbq-card-accent qbq-skel" style="height:4px"></div>
    <div class="qbq-card-body">
      <div class="qbq-card-head">
        <div class="qbq-skel" style="width:44px;height:44px;border-radius:12px;flex-shrink:0"></div>
        <div style="flex:1">
          <div class="qbq-skel" style="width:80px;height:16px;border-radius:6px;margin-bottom:7px"></div>
          <div class="qbq-skel" style="width:90%;height:13px;border-radius:5px;margin-bottom:5px"></div>
          <div class="qbq-skel" style="width:75%;height:13px;border-radius:5px"></div>
        </div>
      </div>
      <div style="display:flex;gap:.3rem;flex-wrap:wrap">
        <div class="qbq-skel" style="width:70px;height:22px;border-radius:100px"></div>
        <div class="qbq-skel" style="width:55px;height:22px;border-radius:100px"></div>
        <div class="qbq-skel" style="width:80px;height:22px;border-radius:100px"></div>
      </div>
    </div>
    <div class="qbq-card-foot" style="border-top:1px solid #f0f4f8">
      <div class="qbq-skel" style="width:72px;height:22px;border-radius:100px"></div>
      <div style="display:flex;gap:.3rem">
        <div class="qbq-skel" style="width:30px;height:30px;border-radius:8px"></div>
        <div class="qbq-skel" style="width:30px;height:30px;border-radius:8px"></div>
        <div class="qbq-skel" style="width:30px;height:30px;border-radius:8px"></div>
      </div>
    </div>
  </div>
  <?php endfor; ?>
</div>

<!-- ── Table (list mode) ──────────────────────────────────── -->
<div class="qbq-table-wrap" id="qbqTableWrap" style="display:none">
  <div style="padding:.6rem 1rem;border-bottom:1px solid #f0f4f8;display:flex;align-items:center;justify-content:space-between">
    <span style="font-size:.77rem;color:#94a3b8;font-weight:600" id="qbqTableCount">Loading…</span>
    <label style="font-size:.75rem;color:#64748b;display:flex;align-items:center;gap:.4rem;cursor:pointer">
      <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"> Select all visible
    </label>
  </div>
  <div class="table-responsive">
    <table class="qbq-table">
      <thead>
        <tr>
          <th width="36"></th>
          <th>UID</th>
          <th>Subject</th>
          <th>Level</th>
          <th>Chapter</th>
          <th>Type</th>
          <th>Difficulty</th>
          <th>Status</th>
          <th>Marks</th>
          <th class="text-end" style="padding-right:1.1rem">Actions</th>
        </tr>
      </thead>
      <tbody id="qTbody"></tbody>
    </table>
  </div>
  <div class="qbq-pager" id="qPagination"></div>
</div>

<!-- ── Card pagination ────────────────────────────────────── -->
<div class="qbq-pager" id="qCardPager" style="display:none;background:#fff;border-radius:16px;border:1px solid #f0f4f8;margin-top:1rem;box-shadow:0 1px 3px rgba(0,0,0,.04)"></div>

</div><!-- /.container-fluid -->

<!-- ── Bulk action bar ────────────────────────────────────── -->
<div class="qbq-bulk-bar" id="bulkBar">
  <span class="qbq-bulk-info" id="bulkInfo">0 selected</span>
  <select id="bulkStatus" class="qbq-bulk-sel">
    <option value="">Change status to…</option>
    <option value="draft">Draft</option>
    <option value="review">Review</option>
    <option value="approved">Approved</option>
    <option value="published">Published</option>
    <option value="archived">Archived</option>
  </select>
  <button class="qbq-bulk-apply" onclick="bulkChangeStatus()">Apply</button>
  <button class="qbq-bulk-clear" onclick="clearSelection()">Clear</button>
</div>

<!-- ── Preview Modal ─────────────────────────────────────── -->
<div class="modal fade qbq-modal" id="qViewModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background:<?= $hero['grad'] ?>">
        <div class="modal-title">
          <div style="width:30px;height:30px;border-radius:9px;background:rgba(255,255,255,.13);display:flex;align-items:center;justify-content:center"><i class="bi bi-patch-question-fill"></i></div>
          <span id="qViewUID">Question Preview</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1) brightness(2);opacity:.7"></button>
      </div>
      <div class="modal-body" id="qViewBody">
        <div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>
      </div>
      <div class="modal-footer">
        <span id="qViewStatus"></span>
        <div class="ms-auto d-flex align-items-center gap-2">
          <select id="qStatusChange" class="qbq-modal-sel">
            <option value="">Change Status</option>
            <option value="draft">Draft</option>
            <option value="review">Review</option>
            <option value="approved">Approved</option>
            <option value="published">Published</option>
            <option value="archived">Archived</option>
          </select>
          <button class="qbq-status-change-btn" onclick="applyStatusChange()"><i class="bi bi-arrow-repeat"></i>Apply</button>
          <button class="qbq-modal-del" onclick="deleteQuestion()"><i class="bi bi-trash"></i>Delete</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const FILTER_STATUS = '<?= $filterStatus ?>';

const STATUS_CFG = {
  draft:     { accent:'#f59e0b', bg:'#fef9c3', color:'#92400e', icon:'bi-pencil-square' },
  review:    { accent:'#0ea5e9', bg:'#e0f2fe', color:'#0369a1', icon:'bi-eye-fill' },
  approved:  { accent:'#10b981', bg:'#d1fae5', color:'#065f46', icon:'bi-patch-check-fill' },
  published: { accent:'#6366f1', bg:'#ede9fe', color:'#4c1d95', icon:'bi-check-circle-fill' },
  archived:  { accent:'#6b7280', bg:'#f3f4f6', color:'#374151', icon:'bi-archive-fill' },
};

const TYPE_CFG = {
  mcq:        { icon:'bi-list-ul',           grad:'linear-gradient(135deg,#1a4fc4,#6d28d9)', glow:'rgba(26,79,196,.25)',  light:'#eff6ff',  text:'#1a4fc4', label:'MCQ' },
  true_false: { icon:'bi-toggle-on',         grad:'linear-gradient(135deg,#059669,#0d9488)', glow:'rgba(5,150,105,.25)',  light:'#f0fdf4',  text:'#059669', label:'True/False' },
  essay:      { icon:'bi-file-text',         grad:'linear-gradient(135deg,#d97706,#f59e0b)', glow:'rgba(217,119,6,.25)',  light:'#fffbeb',  text:'#d97706', label:'Essay' },
  matching:   { icon:'bi-arrow-left-right',  grad:'linear-gradient(135deg,#dc2626,#e11d48)', glow:'rgba(220,38,38,.25)',  light:'#fff1f2',  text:'#dc2626', label:'Matching' },
  fill_blank: { icon:'bi-input-cursor-text', grad:'linear-gradient(135deg,#0891b2,#0ea5e9)', glow:'rgba(8,145,178,.25)',  light:'#f0f9ff',  text:'#0891b2', label:'Fill Blank' },
};

/* ── DCM Alerts ──────────────────────────────────────────── */
const dcmAlert = {
  _css:`
    .ds-pop{border-radius:20px!important;font-family:'Open Sans',sans-serif!important;padding:1.6rem!important}
    .ds-ttl{font-size:1.1rem!important;font-weight:800!important;color:#0f172a!important;margin-top:.3rem!important}
    .ds-btn{border-radius:11px!important;font-weight:700!important;font-size:.82rem!important;padding:.55rem 1.4rem!important}
    .ds-can{border-radius:11px!important;font-weight:700!important;font-size:.82rem!important;padding:.55rem 1.4rem!important;background:#f1f5f9!important;color:#475569!important;border:1.5px solid #e2e8f0!important}
    .ds-ico{border:none!important;margin-bottom:.4rem!important}
    .ds-tst{border-radius:14px!important;font-family:'Open Sans',sans-serif!important;box-shadow:0 8px 32px rgba(0,0,0,.14)!important;padding:.75rem 1.1rem!important;border-left:4px solid}
    .dst-ok{border-color:#059669!important}.dst-er{border-color:#dc2626!important}.dst-wn{border-color:#d97706!important}
  `,
  _done:false,
  _inject(){if(!this._done){const s=document.createElement('style');s.textContent=this._css;document.head.appendChild(s);this._done=true;}},
  toast(icon,title,text=''){this._inject();const cls={success:'dst-ok',error:'dst-er',warning:'dst-wn'}[icon]||'';Swal.fire({toast:true,position:'top-end',showConfirmButton:false,timer:3400,timerProgressBar:true,icon,title,text,customClass:{popup:`ds-tst ${cls}`}});},
  success(t,x=''){this.toast('success',t,x);},
  error(t,x=''){this._inject();Swal.fire({icon:'error',title:t,text:x||'Something went wrong.',customClass:{popup:'ds-pop',title:'ds-ttl',confirmButton:'ds-btn'},confirmButtonColor:'#dc2626',confirmButtonText:'Got it'});},
  loading(t='Processing…'){this._inject();Swal.fire({title:t,allowOutsideClick:false,customClass:{popup:'ds-pop',title:'ds-ttl'},didOpen:()=>Swal.showLoading()});},
  confirm({title,text,confirmText='Confirm',confirmColor='#dc2626',onConfirm}){
    this._inject();
    Swal.fire({title,text,icon:'warning',showCancelButton:true,confirmButtonText:confirmText,cancelButtonText:'Cancel',confirmButtonColor,reverseButtons:true,
      customClass:{popup:'ds-pop',title:'ds-ttl',confirmButton:'ds-btn',cancelButton:'ds-can',icon:'ds-ico'},
      showClass:{popup:'animate__animated animate__zoomIn animate__faster'},
      hideClass:{popup:'animate__animated animate__zoomOut animate__faster'}
    }).then(r=>{if(r.isConfirmed&&onConfirm)onConfirm();});
  }
};

let viewModal, currentQId = null, searchTimer;
let page = 1, perPage = 20, totalRows = 0;
let currentView = 'grid';
let allCounts = {};

function _qbqInit() {
  viewModal = new bootstrap.Modal(document.getElementById('qViewModal'));
  loadFilterOptions();
  loadBadgeCounts();
  loadQuestions();
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', _qbqInit);
} else {
  _qbqInit();
}

/* ── View toggle ─────────────────────────────────────────── */
function setView(v) {
  currentView = v;
  document.getElementById('btnGrid').classList.toggle('active', v==='grid');
  document.getElementById('btnList').classList.toggle('active', v==='list');
  document.getElementById('qbqGrid').style.display      = v==='grid' ? '' : 'none';
  document.getElementById('qbqTableWrap').style.display = v==='list' ? '' : 'none';
  document.getElementById('qCardPager').style.display   = v==='grid' ? '' : 'none';
  fetchQuestions();
}

/* ── Filter options ─────────────────────────────────────── */
function loadFilterOptions() {
  ['subjects','levels','difficulty_levels'].forEach(entity => {
    fetch(`ajax/ajax_qb_taxonomy.php?entity=${entity}&action=list`)
      .then(r=>r.json()).then(res=>{
        if (res.status!=='success') return;
        const maps = {
          subjects:          {el:'fSubject',    v:'subject_id',    l:'subject_name'},
          levels:            {el:'fLevel',      v:'level_id',      l:'level_name'},
          difficulty_levels: {el:'fDifficulty', v:'difficulty_id', l:'difficulty_name'},
        };
        const m = maps[entity];
        const sel = document.getElementById(m.el);
        res.data.forEach(row => {
          const o = document.createElement('option');
          o.value = row[m.v]; o.textContent = row[m.l];
          sel.appendChild(o);
        });
      });
  });
}

function loadChapters() {
  const subj  = document.getElementById('fSubject').value;
  const level = document.getElementById('fLevel').value;
  const sel   = document.getElementById('fChapter');
  sel.innerHTML = '<option value="">All Chapters</option>';
  if (!subj && !level) return;
  fetch('ajax/ajax_qb_taxonomy.php?entity=chapters&action=list')
    .then(r=>r.json()).then(res=>{
      if (res.status!=='success') return;
      res.data
        .filter(r=>(!subj||r.subject_id==subj)&&(!level||r.level_id==level))
        .forEach(r=>{
          const o = document.createElement('option');
          o.value = r.chapter_id;
          o.textContent = (r.chapter_number?`Ch.${r.chapter_number} – `:'')+r.chapter_name;
          sel.appendChild(o);
        });
    });
}

/* ── Badge counts + hero KPIs ───────────────────────────── */
function loadBadgeCounts() {
  fetch('ajax/ajax_qb_questions.php?action=counts')
    .then(r=>r.json()).then(res=>{
      if (res.status!=='success') return;
      allCounts = res.data;

      const el = (id) => document.getElementById(id);
      el('badge_qb_all_questions').textContent      = allCounts.total     || '0';
      el('badge_qb_draft_questions').textContent    = allCounts.draft     || '0';
      el('badge_qb_review_queue').textContent       = allCounts.review    || '0';
      el('badge_qb_approved_questions').textContent = allCounts.approved  || '0';
      el('badge_qb_published_questions').textContent= allCounts.published || '0';
      el('badge_qb_archived_questions').textContent = allCounts.archived  || '0';

      document.getElementById('heroKpis').innerHTML = [
        {label:'Total Questions', val: allCounts.total},
        {label:'Published',       val: allCounts.published},
        {label:'Draft',           val: allCounts.draft},
        {label:'In Review',       val: allCounts.review},
      ].map((k,i)=>`
        <div class="qbq-kpi">
          <div class="qbq-kpi-val">${(+k.val||0).toLocaleString()}</div>
          <div class="qbq-kpi-lbl">${k.label}</div>
        </div>`).join('');
    });
}

/* ── Load / fetch questions ─────────────────────────────── */
function loadQuestions() { page = 1; fetchQuestions(); }

function debounceSearch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(loadQuestions, 350);
}

function fetchQuestions() {
  const params = new URLSearchParams({
    action:        'list',
    status_filter: FILTER_STATUS,
    subject_id:    document.getElementById('fSubject').value,
    level_id:      document.getElementById('fLevel').value,
    chapter_id:    document.getElementById('fChapter').value,
    difficulty_id: document.getElementById('fDifficulty').value,
    type:          document.getElementById('fType').value,
    q:             document.getElementById('fSearch').value,
    page, per_page: perPage,
  });

  showSkeleton();

  fetch(`ajax/ajax_qb_questions.php?${params}`)
    .then(r=>r.json())
    .then(res=>{
      if (res.status!=='success') { showError(); return; }
      totalRows = res.total;
      const label = `${res.total} question${res.total!==1?'s':''}`;
      document.getElementById('qbqCount').textContent = label;
      if (document.getElementById('qbqTableCount')) document.getElementById('qbqTableCount').textContent = label;
      renderQuestions(res.data);
      renderPagination();
    })
    .catch(()=>showError());
}

function showSkeleton() {
  if (currentView==='grid') {
    document.getElementById('qbqGrid').innerHTML = [0,1,2,3,4,5].map(()=>`
      <div class="qbq-card">
        <div class="qbq-card-accent qbq-skel" style="height:4px"></div>
        <div class="qbq-card-body">
          <div class="qbq-card-head">
            <div class="qbq-skel" style="width:44px;height:44px;border-radius:12px;flex-shrink:0"></div>
            <div style="flex:1">
              <div class="qbq-skel" style="width:80px;height:16px;border-radius:6px;margin-bottom:7px"></div>
              <div class="qbq-skel" style="width:90%;height:13px;border-radius:5px;margin-bottom:5px"></div>
              <div class="qbq-skel" style="width:75%;height:13px;border-radius:5px"></div>
            </div>
          </div>
          <div style="display:flex;gap:.3rem;flex-wrap:wrap;margin-top:.65rem">
            <div class="qbq-skel" style="width:70px;height:22px;border-radius:100px"></div>
            <div class="qbq-skel" style="width:55px;height:22px;border-radius:100px"></div>
            <div class="qbq-skel" style="width:80px;height:22px;border-radius:100px"></div>
          </div>
        </div>
        <div class="qbq-card-foot" style="border-top:1px solid #f0f4f8">
          <div class="qbq-skel" style="width:72px;height:22px;border-radius:100px"></div>
          <div style="display:flex;gap:.3rem">
            <div class="qbq-skel" style="width:30px;height:30px;border-radius:8px"></div>
            <div class="qbq-skel" style="width:30px;height:30px;border-radius:8px"></div>
            <div class="qbq-skel" style="width:30px;height:30px;border-radius:8px"></div>
          </div>
        </div>
      </div>`).join('');
  } else {
    document.getElementById('qTbody').innerHTML =
      `<tr><td colspan="10" style="text-align:center;padding:2rem"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>`;
  }
}

function showError() {
  const errHtml = `<div class="qbq-empty"><i class="bi bi-exclamation-triangle qbq-empty-icon"></i><div style="font-size:1rem;font-weight:800;color:#475569;margin-bottom:.35rem">Failed to load</div><div style="font-size:.8rem;color:#94a3b8;margin-bottom:1.2rem">Could not reach the server.</div><button class="qbq-add-btn" onclick="fetchQuestions()"><i class="bi bi-arrow-clockwise"></i>Retry</button></div>`;
  if (currentView==='grid') document.getElementById('qbqGrid').innerHTML = errHtml;
  else document.getElementById('qTbody').innerHTML = `<tr><td colspan="10">${errHtml}</td></tr>`;
}

/* ── Render questions ────────────────────────────────────── */
function renderQuestions(rows) {
  if (currentView==='grid') renderCards(rows);
  else renderTable(rows);
}

function stripHtml(html) {
  const tmp = document.createElement('div');
  tmp.innerHTML = html; return tmp.textContent || tmp.innerText || '';
}

function renderCards(rows) {
  const grid = document.getElementById('qbqGrid');
  if (!rows.length) {
    const q = document.getElementById('fSearch').value;
    grid.innerHTML = `<div class="qbq-empty"><i class="bi bi-inbox qbq-empty-icon"></i><div style="font-size:1rem;font-weight:800;color:#475569;margin-bottom:.35rem">${q?'No matches found':'Nothing here yet'}</div><div style="font-size:.8rem;color:#94a3b8;margin-bottom:1.2rem">${q?'Try a different search term.':'Click Add Question to get started.'}</div>${!q?`<a href="?view=qb_add_question" class="qbq-add-btn"><i class="bi bi-plus-lg"></i>Add Question</a>`:''}</div>`;
    return;
  }
  grid.innerHTML = rows.map((q,i) => buildCard(q,i)).join('');
}

function buildCard(q, i) {
  const tc = TYPE_CFG[q.question_type] || TYPE_CFG.mcq;
  const sc = STATUS_CFG[q.status] || {accent:'#94a3b8',bg:'#f1f5f9',color:'#475569',icon:'bi-question-circle'};
  const stem = stripHtml(q.question_stem||'').trim();
  const stemShort = stem.length>150 ? stem.slice(0,150)+'…' : stem;

  const metaPills = [
    q.subject_name    ? `<span class="qbq-meta-pill"><i class="bi bi-book" style="font-size:.6rem"></i>${q.subject_name}</span>` : '',
    q.level_name      ? `<span class="qbq-meta-pill"><i class="bi bi-layers" style="font-size:.6rem"></i>${q.level_name}</span>` : '',
    q.chapter_name    ? `<span class="qbq-meta-pill"><i class="bi bi-bookmark" style="font-size:.6rem"></i>${q.chapter_name}</span>` : '',
    q.difficulty_name ? `<span class="qbq-meta-pill" style="background:#fff7ed;color:#c2410c"><i class="bi bi-speedometer2" style="font-size:.6rem"></i>${q.difficulty_name}</span>` : '',
  ].join('');

  return `
  <div class="qbq-card" id="card_${q.question_id}" style="animation-delay:${Math.min(i*0.04,.32)}s">
    <div class="qbq-card-accent" style="background:${sc.accent}"></div>
    <div class="qbq-card-body">
      <div class="qbq-card-head">
        <div class="qbq-card-icon" style="background:${tc.grad};box-shadow:0 6px 18px ${tc.glow}">
          <i class="bi ${tc.icon}"></i>
        </div>
        <div style="min-width:0;flex:1">
          <div style="display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;margin-bottom:.3rem">
            <span class="qbq-uid-badge">${q.q_uid}</span>
            <span class="qbq-type-badge" style="background:${tc.light};color:${tc.text}">${tc.label}</span>
            <span class="qbq-marks-badge"><i class="bi bi-star-fill" style="font-size:.56rem"></i>${q.marks} mk</span>
          </div>
          <div class="qbq-stem">${stemShort || '<em style="color:#94a3b8">No question text</em>'}</div>
        </div>
      </div>
      <div class="qbq-meta">${metaPills}</div>
    </div>
    <div class="qbq-card-foot">
      <div style="display:flex;align-items:center;gap:.5rem">
        <input type="checkbox" class="qbq-card-chk qCheck" value="${q.question_id}" onchange="updateBulkBar()" title="Select">
        <span class="qbq-status-pill" style="background:${sc.bg};color:${sc.color}">
          <i class="bi ${sc.icon}" style="font-size:.62rem"></i>${q.status}
        </span>
      </div>
      <div class="qbq-card-acts">
        <button class="qbq-icn qbq-icn-preview" onclick="viewQuestion(${q.question_id})" title="Preview"><i class="bi bi-eye"></i></button>
        <a href="?view=qb_add_question&id=${q.question_id}" class="qbq-icn" title="Edit"><i class="bi bi-pencil"></i></a>
        <button class="qbq-icn qbq-icn-del" onclick="deleteQ(${q.question_id})" title="Delete"><i class="bi bi-trash"></i></button>
      </div>
    </div>
  </div>`;
}

function renderTable(rows) {
  const tbody = document.getElementById('qTbody');
  if (!rows.length) {
    const q = document.getElementById('fSearch').value;
    tbody.innerHTML = `<tr><td colspan="10" style="text-align:center;padding:3rem">
      <i class="bi bi-inbox" style="font-size:2.5rem;color:#cbd5e1;display:block;margin-bottom:.75rem"></i>
      <div style="font-weight:800;color:#475569">${q?'No matches':'No questions found'}</div>
      ${!q?`<a href="?view=qb_add_question" style="color:#1a4fc4;font-size:.82rem;display:block;margin-top:.5rem">Add your first question →</a>`:''}
    </td></tr>`;
    return;
  }
  tbody.innerHTML = rows.map(q => {
    const tc = TYPE_CFG[q.question_type] || TYPE_CFG.mcq;
    const sc = STATUS_CFG[q.status] || {accent:'#94a3b8',bg:'#f1f5f9',color:'#475569',icon:'bi-question-circle'};
    return `
    <tr onclick="viewQuestion(${q.question_id})" style="border-left:3px solid ${sc.accent}">
      <td onclick="event.stopPropagation()">
        <input type="checkbox" class="qCheck" value="${q.question_id}" onchange="updateBulkBar()">
      </td>
      <td><span class="qbq-tbl-uid">${q.q_uid}</span></td>
      <td style="font-size:.79rem">${q.subject_name??'—'}</td>
      <td style="font-size:.79rem">${q.level_name??'—'}</td>
      <td style="font-size:.79rem;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${q.chapter_name??'—'}</td>
      <td><span class="qbq-tbl-type" style="background:${tc.light};color:${tc.text}">${tc.label}</span></td>
      <td style="font-size:.79rem">${q.difficulty_name??'<span style="color:#94a3b8">—</span>'}</td>
      <td><span class="qbq-tbl-status" style="background:${sc.bg};color:${sc.color}"><i class="bi ${sc.icon}" style="font-size:.6rem"></i>${q.status}</span></td>
      <td style="font-size:.79rem;font-weight:600">${q.marks}</td>
      <td style="text-align:right;padding-right:.85rem" onclick="event.stopPropagation()">
        <div style="display:inline-flex;gap:.3rem">
          <a href="?view=qb_add_question&id=${q.question_id}" class="qbq-icn" title="Edit"><i class="bi bi-pencil"></i></a>
          <button class="qbq-icn qbq-icn-del" onclick="deleteQ(${q.question_id})" title="Delete"><i class="bi bi-trash"></i></button>
        </div>
      </td>
    </tr>`;
  }).join('');
}

/* ── Pagination ──────────────────────────────────────────── */
function renderPagination() {
  const pages = Math.ceil(totalRows / perPage);
  const pagerEl = currentView==='grid'
    ? document.getElementById('qCardPager')
    : document.getElementById('qPagination');

  document.getElementById('qCardPager').style.display = (currentView==='grid' && pages>1) ? 'flex' : 'none';

  if (pages<=1) { pagerEl.innerHTML=''; return; }
  pagerEl.innerHTML = `
    <span class="qbq-pager-info">Page ${page} of ${pages} &nbsp;(${totalRows} total)</span>
    <button class="qbq-pager-btn" onclick="goPage(${page-1})" ${page<=1?'disabled':''}>‹ Prev</button>
    <button class="qbq-pager-btn" onclick="goPage(${page+1})" ${page>=pages?'disabled':''}>Next ›</button>`;
}

function goPage(p) { page=p; fetchQuestions(); window.scrollTo({top:0,behavior:'smooth'}); }

/* ── View question (preview modal) ──────────────────────── */
function viewQuestion(id) {
  currentQId = id;
  document.getElementById('qViewBody').innerHTML = '<div style="text-align:center;padding:2.5rem"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
  viewModal.show();

  fetch(`ajax/ajax_qb_questions.php?action=get&id=${id}`)
    .then(r=>r.json()).then(res=>{
      if (res.status!=='success') { document.getElementById('qViewBody').innerHTML='<p class="text-danger">Could not load question.</p>'; return; }
      const q = res.data;
      document.getElementById('qViewUID').textContent = q.q_uid;
      const sc = STATUS_CFG[q.status]||{bg:'#f1f5f9',color:'#475569',icon:'bi-question-circle'};
      document.getElementById('qViewStatus').innerHTML =
        `<span class="qbq-status-pill" style="background:${sc.bg};color:${sc.color}"><i class="bi ${sc.icon}" style="font-size:.62rem"></i>${q.status}</span>`;
      document.getElementById('qStatusChange').value = q.status;

      let optsHtml = '';
      if (q.question_type==='matching') {
        const pairs=(q.options||[]).map(o=>{try{return JSON.parse(o.option_text);}catch(e){return{left:o.option_text,right:''}}});
        optsHtml=`<div class="table-responsive"><table style="width:100%;border-collapse:collapse;font-size:.82rem"><thead><tr><th style="padding:.5rem .75rem;background:#f8fafc;border:1px solid #e2e8f0;font-weight:700;color:#475569">Premise</th><th style="padding:.5rem .75rem;background:#f8fafc;border:1px solid #e2e8f0;font-weight:700;color:#475569">Response</th></tr></thead><tbody>${pairs.map(p=>`<tr><td style="padding:.5rem .75rem;border:1px solid #f0f4f8">${p.left}</td><td style="padding:.5rem .75rem;border:1px solid #f0f4f8;font-weight:700;color:#059669">${p.right}</td></tr>`).join('')}</tbody></table></div>`;
      } else if (q.question_type==='essay') {
        optsHtml=q.correct_answer?`<div style="padding:.85rem 1rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;font-size:.83rem"><div style="font-size:.67rem;font-weight:700;color:#059669;text-transform:uppercase;margin-bottom:.4rem">Model Answer</div>${q.correct_answer}</div>`:'<span style="color:#94a3b8;font-size:.82rem">No model answer provided.</span>';
      } else {
        optsHtml=(q.options||[]).map(o=>`<div class="qbq-opt${o.is_correct?' correct':''}"><span class="qbq-opt-lbl">${o.option_label}.</span><span style="font-size:.83rem">${o.option_text}</span>${o.is_correct?'<i class="bi bi-check-circle-fill text-success ms-auto"></i>':''}</div>`).join('');
      }

      document.getElementById('qViewBody').innerHTML=`
        <div class="qbq-preview-meta">
          <div class="qbq-preview-meta-item"><small>Subject</small><span>${q.subject_name??'—'}</span></div>
          <div class="qbq-preview-meta-item"><small>Level</small><span>${q.level_name??'—'}</span></div>
          <div class="qbq-preview-meta-item"><small>Chapter</small><span>${q.chapter_name??'—'}</span></div>
          <div class="qbq-preview-meta-item"><small>Subtopic</small><span>${q.subtopic_name??'—'}</span></div>
          <div class="qbq-preview-meta-item"><small>Difficulty</small><span>${q.difficulty_name??'—'}</span></div>
          <div class="qbq-preview-meta-item"><small>Bloom Level</small><span>${q.bloom_name??'—'}</span></div>
        </div>
        <div class="qbq-preview-stem">${q.question_stem||''}</div>
        ${optsHtml}
        ${q.solution_explanation?`<div style="margin-top:1rem;padding:.85rem 1rem;background:#f0f9ff;border:1px solid #bae6fd;border-radius:12px;font-size:.82rem"><div style="font-size:.67rem;font-weight:700;color:#0369a1;text-transform:uppercase;margin-bottom:.4rem">Solution</div>${q.solution_explanation}</div>`:''}
        ${q.swahili_hint?`<div style="margin-top:.75rem;padding:.85rem 1rem;background:#fffbeb;border:1px solid #fde68a;border-radius:12px;font-size:.82rem"><div style="font-size:.67rem;font-weight:700;color:#92400e;text-transform:uppercase;margin-bottom:.4rem">Swahili Hint</div>${q.swahili_hint}</div>`:''}`;
    });
}

function applyStatusChange() {
  const status = document.getElementById('qStatusChange').value;
  if (!status||!currentQId) return;
  changeStatus([currentQId], status, ()=>{ viewModal.hide(); fetchQuestions(); loadBadgeCounts(); });
}

/* ── Bulk / select ───────────────────────────────────────── */
function getChecked() { return [...document.querySelectorAll('.qCheck:checked')].map(c=>+c.value); }

function toggleSelectAll(cb) {
  document.querySelectorAll('.qCheck').forEach(c=>c.checked=cb.checked);
  updateBulkBar();
}

function updateBulkBar() {
  const n = getChecked().length;
  const bar = document.getElementById('bulkBar');
  document.getElementById('bulkInfo').textContent = `${n} selected`;
  bar.classList.toggle('visible', n>0);
}

function clearSelection() {
  document.querySelectorAll('.qCheck').forEach(c=>c.checked=false);
  const sa = document.getElementById('selectAll');
  if (sa) sa.checked=false;
  updateBulkBar();
}

function bulkChangeStatus() {
  const ids = getChecked();
  const status = document.getElementById('bulkStatus').value;
  if (!ids.length||!status) { dcmAlert.error('Select questions and a status first.'); return; }
  changeStatus(ids, status, ()=>{ clearSelection(); fetchQuestions(); loadBadgeCounts(); });
}

/* ── changeStatus / delete ───────────────────────────────── */
function changeStatus(ids, status, cb) {
  dcmAlert.loading('Updating status…');
  fetch('ajax/ajax_qb_questions.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({action:'change_status',ids,status})
  }).then(r=>r.json()).then(res=>{
    Swal.close();
    if (res.status==='success') {
      dcmAlert.success('Status updated!', `${ids.length} question${ids.length>1?'s':''} moved to "${status}".`);
      if (cb) cb();
    } else dcmAlert.error('Update failed', res.message);
  }).catch(()=>dcmAlert.error('Request failed','Unable to reach the server.'));
}

function deleteQ(id) {
  dcmAlert.confirm({
    title: 'Delete this question?',
    text: 'This will permanently remove the question and all its options. This cannot be undone.',
    confirmText: '<i class="bi bi-trash me-1"></i>Yes, delete it',
    confirmColor: '#dc2626',
    onConfirm() {
      dcmAlert.loading('Deleting…');
      fetch('ajax/ajax_qb_questions.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({action:'delete',id})
      }).then(r=>r.json()).then(res=>{
        Swal.close();
        if (res.status==='success') {
          dcmAlert.success('Deleted!','Question removed from the bank.');
          fetchQuestions(); loadBadgeCounts();
        } else dcmAlert.error('Delete failed', res.message);
      }).catch(()=>dcmAlert.error('Request failed','Unable to reach the server.'));
    }
  });
}

function deleteQuestion() { if (currentQId) { viewModal.hide(); deleteQ(currentQId); } }

function resetFilters() {
  ['fSubject','fLevel','fChapter','fDifficulty','fType'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('fSearch').value='';
  document.getElementById('fChapter').innerHTML='<option value="">All Chapters</option>';
  loadQuestions();
}

// Expose handlers to global scope so onclick attributes work after SPA navigation
Object.assign(window, {
  setView, loadQuestions, loadChapters, debounceSearch, resetFilters,
  toggleSelectAll, bulkChangeStatus, clearSelection, applyStatusChange,
  deleteQuestion, fetchQuestions, updateBulkBar, viewQuestion, deleteQ, goPage
});
</script>
