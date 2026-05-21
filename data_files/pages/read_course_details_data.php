<?php
/* ── Course data ──────────────────────────────────────────────── */
require_once __DIR__ . '/../config/url_crypt_config.php';
$_cid = decryptURLId($_GET['course_id'] ?? '', ctx: 'course');
if (!$_cid) { echo '<div class="p-4 text-center text-danger">Invalid course.</div>'; return; }

$_cs = $db->prepare("SELECT * FROM tbl_courses WHERE id = ? AND deleted_at IS NULL LIMIT 1");
$_cs->bind_param('i', $_cid);
$_cs->execute();
$_course = $_cs->get_result()->fetch_assoc();
if (!$_course) { echo '<div class="p-4 text-center text-danger">Course not found.</div>'; return; }

/* ── Instructor ───────────────────────────────────────────────── */
$_ti = $db->prepare("SELECT first_name, last_name, image, course FROM tbl_tutors WHERE usr_code = ? LIMIT 1");
$_ti->bind_param('s', $_course['instructor_id']);
$_ti->execute();
$_instructor = $_ti->get_result()->fetch_assoc() ?: [];

/* ── Stats ────────────────────────────────────────────────────── */
$_stats = $db->query("
    SELECT COUNT(DISTINCT ch.id) chapters, COUNT(DISTINCT l.id) lessons,
           ROUND(AVG(r.rating),1) avg_rating, COUNT(DISTINCT r.id) reviews,
           COUNT(DISTINCT CASE WHEN e.has_access=1 THEN e.id END) enrolled
    FROM tbl_courses c
    LEFT JOIN tbl_course_chapters ch ON ch.course_id=c.id
    LEFT JOIN tbl_course_chapter_lessons l ON l.chapter_id=ch.id
    LEFT JOIN tbl_course_ratings r ON r.course_id=c.id
    LEFT JOIN tbl_course_enrollments e ON e.course_id=c.id
    WHERE c.id={$_cid}")->fetch_assoc();

$_chapters  = (int)($_stats['chapters'] ?? 0);
$_lessons   = (int)($_stats['lessons']  ?? 0);
$_rating    = $_stats['avg_rating'] ? number_format((float)$_stats['avg_rating'], 1) : '—';
$_reviews   = (int)($_stats['reviews']  ?? 0);
$_enrolled  = (int)($_stats['enrolled'] ?? 0);
$_iname     = htmlspecialchars(trim(($_instructor['first_name'] ?? '') . ' ' . ($_instructor['last_name'] ?? '')));
$_iimg      = !empty($_instructor['image']) ? $_instructor['image'] : '';
$_thumb     = !empty($_course['thumbnail']) && $_course['thumbnail'] !== 'uploads/course_default.png'
              ? htmlspecialchars($_course['thumbnail']) : '';
$_price     = (float)($_course['price'] ?? 0);
$_disc      = (float)($_course['discount'] ?? 0);
$_final     = $_price > 0 ? $_price - ($_price * $_disc / 100) : 0;
?>
<style>
/* ═══════════════════════════════════════════════════════════
   COURSE READER  (cr-*)
═══════════════════════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; }
.cr-wrap   { font-family:'Open Sans',sans-serif; background:#f8fafc; min-height:100vh; padding:0 0 2rem; }

/* ── Top bar ── */
.cr-topbar { background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 60%,#312e81 100%);
             padding:1rem 1.5rem; display:flex; align-items:center; gap:1rem;
             flex-wrap:wrap; border-bottom:1px solid rgba(255,255,255,.07); }
.cr-back   { display:inline-flex;align-items:center;gap:.4rem;color:rgba(255,255,255,.7);
             font-size:.78rem;font-weight:700;text-decoration:none;padding:.35rem .75rem;
             border-radius:8px;border:1px solid rgba(255,255,255,.15);transition:all .15s; }
.cr-back:hover { background:rgba(255,255,255,.12);color:#fff; }
.cr-topbar-title { flex:1;min-width:0; }
.cr-topbar-title h1 { font-size:1rem;font-weight:800;color:#fff;font-family:'SUSE',sans-serif;
                      margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.cr-topbar-meta  { display:flex;align-items:center;gap:.65rem;flex-wrap:wrap;margin-top:.25rem; }
.cr-chip { display:inline-flex;align-items:center;gap:.3rem;font-size:.68rem;font-weight:700;
           color:rgba(255,255,255,.6);padding:.2rem .6rem;border-radius:100px;
           background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.1); }
.cr-chip .stars { color:#f59e0b; }

/* ── Layout ── */
.cr-layout { display:grid;grid-template-columns:1fr 360px;gap:0;
             min-height:calc(100vh - 60px);margin:1.25rem;border-radius:18px;
             overflow:hidden;box-shadow:0 4px 32px rgba(0,0,0,.08);border:1.5px solid #f0f4f8; }
@media(max-width:900px){ .cr-layout { grid-template-columns:1fr;margin:.75rem; } }

/* ── Main column ── */
.cr-main   { min-width:0; }

/* ── Viewer ── */
.cr-viewer { background:#000;position:relative;width:100%; }
.cr-viewer-inner { width:100%;aspect-ratio:16/9;max-height:500px;background:#0a0a0a;
                   display:flex;align-items:center;justify-content:center;overflow:hidden; }
.cr-viewer-inner iframe,
.cr-viewer-inner embed  { width:100%;height:100%;border:0; }
/* ── Custom Audio Player ── */
#crAP { position:relative;width:100%;height:100%;background:#0d0d1a;overflow:hidden;display:flex;flex-direction:column; }
#crAP-bg { position:absolute;inset:-20px;background-size:cover;background-position:center;filter:blur(32px) brightness(.3);z-index:0; }
#crAP-art { flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;position:relative;z-index:2;padding:1.25rem 1.5rem .5rem;gap:.75rem;min-height:0; }
#crAP-cover { width:130px;height:130px;border-radius:16px;overflow:hidden;background:linear-gradient(135deg,#4f46e5,#7c3aed);display:flex;align-items:center;justify-content:center;box-shadow:0 16px 48px rgba(0,0,0,.6);flex-shrink:0; }
#crAP-cover img { width:100%;height:100%;object-fit:cover; }
#crAP-title { font-size:.86rem;font-weight:700;color:#fff;text-align:center;max-width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
#crAP-sub { font-size:.68rem;color:rgba(255,255,255,.4);letter-spacing:.03em; }
#crAP-ctrl { position:relative;z-index:2;padding:.7rem 1.25rem 1rem;background:rgba(0,0,0,.35);backdrop-filter:blur(8px); }
.crAP-seek-wrap { position:relative;height:4px;background:rgba(255,255,255,.15);border-radius:99px;cursor:pointer;margin-bottom:.4rem; }
.crAP-seek-fill { height:100%;border-radius:99px;background:linear-gradient(90deg,#6366f1,#8b5cf6);pointer-events:none;width:0%; }
.crAP-seek-wrap input[type=range] { position:absolute;inset:-8px 0;width:100%;height:20px;opacity:0;cursor:pointer;margin:0; }
.crAP-time-row { display:flex;justify-content:space-between;font-size:.63rem;color:rgba(255,255,255,.38);margin-bottom:.65rem; }
.crAP-btn-row { display:flex;align-items:center;justify-content:center;gap:.85rem; }
.crAP-btn { background:none;border:none;color:rgba(255,255,255,.65);cursor:pointer;font-size:1.1rem;padding:.3rem;border-radius:50%;transition:all .15s;display:flex;align-items:center;justify-content:center;line-height:1; }
.crAP-btn:hover { color:#fff;background:rgba(255,255,255,.1); }
.crAP-btn.crAP-play { width:50px;height:50px;font-size:1.35rem;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;box-shadow:0 4px 16px rgba(99,102,241,.5); }
.crAP-btn.crAP-play:hover { transform:scale(1.07);box-shadow:0 6px 22px rgba(99,102,241,.65); }
.crAP-vol-row { display:flex;align-items:center;gap:.4rem;position:absolute;right:1.25rem;bottom:1rem; }
.crAP-vol-row input[type=range] { width:64px;accent-color:#6366f1;cursor:pointer;height:3px; }
.crAP-fs-btn { position:absolute;top:.6rem;right:.75rem;z-index:10;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.14);color:rgba(255,255,255,.7);border-radius:8px;padding:.35rem .45rem;cursor:pointer;font-size:.9rem;line-height:1;transition:background .15s; }
.crAP-fs-btn:hover { background:rgba(255,255,255,.2);color:#fff; }
/* Fullscreen overrides */
#crAP:fullscreen,#crAP:-webkit-full-screen { background:#0d0d1a; }
#crAP:fullscreen #crAP-cover,#crAP:-webkit-full-screen #crAP-cover { width:220px;height:220px; }
#crAP:fullscreen #crAP-title,#crAP:-webkit-full-screen #crAP-title { font-size:1.1rem;max-width:500px; }
#crAP:fullscreen #crAP-ctrl,#crAP:-webkit-full-screen #crAP-ctrl { padding:1rem 3rem 1.75rem; }
#crAP:fullscreen .crAP-btn.crAP-play,#crAP:-webkit-full-screen .crAP-btn.crAP-play { width:66px;height:66px;font-size:1.7rem; }
#crAP:fullscreen .crAP-btn,#crAP:-webkit-full-screen .crAP-btn { font-size:1.3rem; }
#crAP:fullscreen .crAP-vol-row input[type=range],#crAP:-webkit-full-screen .crAP-vol-row input[type=range] { width:110px; }
.cr-no-content { display:flex;flex-direction:column;align-items:center;justify-content:center;
                 color:rgba(255,255,255,.35);gap:.75rem;height:100%; }
.cr-no-content i { font-size:3rem; }
.cr-no-content p { font-size:.85rem;font-weight:600;margin:0; }

/* ── Content tabs ── */
.cr-tabs-wrap { position:sticky;top:0;z-index:20;background:#fff;
                border-bottom:2px solid #f0f4f8;overflow-x:auto; }
.cr-tabs { display:flex;gap:0;padding:0 1.5rem; }
.cr-tab  { display:inline-flex;align-items:center;gap:.4rem;padding:.85rem 1.1rem;
           font-size:.8rem;font-weight:700;color:#64748b;cursor:pointer;border:none;
           background:none;border-bottom:2.5px solid transparent;margin-bottom:-2px;
           white-space:nowrap;transition:all .18s;font-family:inherit; }
.cr-tab.active  { color:#4f46e5;border-bottom-color:#4f46e5; }
.cr-tab:hover:not(.active) { color:#0f172a;background:#f8fafc; }
.cr-tab-badge { background:#ede9fe;color:#4f46e5;border-radius:100px;
                padding:.05rem .45rem;font-size:.65rem;font-weight:800; }

/* ── Tab panels ── */
.cr-panels { padding:1.5rem; }
.cr-panel  { display:none; }
.cr-panel.active { display:block; animation:crFade .25s ease; }
@keyframes crFade { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:none} }

/* ── About section ── */
.cr-about-header { display:flex;align-items:center;gap:1rem;padding:1rem;
                   background:linear-gradient(135deg,#f8faff,#f0f4ff);
                   border-radius:14px;border:1px solid #e0e7ff;margin-bottom:1.25rem; }
.cr-instr-avatar { width:52px;height:52px;border-radius:50%;object-fit:cover;
                   border:2px solid #c7d2fe;flex-shrink:0;background:#ede9fe;
                   display:flex;align-items:center;justify-content:center;
                   font-size:1.3rem;color:#818cf8; }
.cr-instr-name  { font-size:.85rem;font-weight:800;color:#0f172a; }
.cr-instr-sub   { font-size:.72rem;color:#64748b;margin-top:.1rem; }
.cr-desc-box    { background:#f8fafc;border-radius:12px;padding:1.1rem 1.25rem;
                  font-size:.83rem;color:#475569;line-height:1.8;
                  border:1px solid #f0f4f8; }
.cr-stat-pills  { display:flex;gap:.5rem;flex-wrap:wrap;margin-top:1rem; }
.cr-stat-pill   { display:inline-flex;align-items:center;gap:.35rem;font-size:.72rem;
                  font-weight:700;padding:.35rem .85rem;border-radius:100px;
                  background:#fff;border:1.5px solid #e2e8f0;color:#334155; }

/* ── Discussion ── */
.cr-discuss-form { background:#fff;border:1.5px solid #e2e8f0;border-radius:14px;
                   padding:1.1rem 1.25rem;margin-bottom:1.25rem; }
.cr-discuss-form h6 { font-size:.82rem;font-weight:800;color:#0f172a;margin-bottom:.75rem; }
.cr-inp { width:100%;border:1.5px solid #e2e8f0;border-radius:10px;
          padding:.55rem .85rem;font-size:.82rem;font-family:inherit;
          color:#0f172a;background:#f8fafc;outline:none;transition:border-color .15s;
          margin-bottom:.6rem;resize:vertical; }
.cr-inp:focus { border-color:#4f46e5;background:#fff; }
.cr-btn { display:inline-flex;align-items:center;gap:.35rem;border-radius:10px;
          padding:.5rem 1.1rem;font-size:.78rem;font-weight:700;cursor:pointer;
          font-family:inherit;border:none;transition:all .15s; }
.cr-btn-primary { background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;box-shadow:0 3px 10px rgba(79,70,229,.25); }
.cr-btn-primary:hover { filter:brightness(1.08); }
.cr-btn-sm { padding:.38rem .8rem;font-size:.73rem; }
.cr-btn-ghost { background:#f1f5f9;color:#475569;border:1.5px solid #e2e8f0; }
.cr-btn-ghost:hover { background:#e2e8f0;color:#0f172a; }
.cr-d-card { background:#fff;border:1.5px solid #f0f4f8;border-radius:14px;
             margin-bottom:.75rem;overflow:hidden; }
.cr-d-head { display:flex;align-items:flex-start;justify-content:space-between;
             gap:.75rem;padding:.9rem 1.1rem;cursor:pointer; }
.cr-d-num  { width:28px;height:28px;border-radius:8px;background:#ede9fe;color:#4f46e5;
             font-size:.72rem;font-weight:800;display:flex;align-items:center;
             justify-content:center;flex-shrink:0;margin-top:.1rem; }
.cr-d-title { font-size:.83rem;font-weight:700;color:#0f172a;flex:1; }
.cr-d-meta  { font-size:.68rem;color:#94a3b8;margin-top:.2rem; }
.cr-d-body  { border-top:1.5px solid #f0f4f8;padding:1rem 1.1rem;display:none; }
.cr-d-body.open { display:block; }
.cr-d-answer-item { background:#f8fafc;border-radius:10px;padding:.7rem .9rem;
                    margin-bottom:.5rem;font-size:.8rem;color:#334155; }
.cr-d-answer-item.best { background:#f0fdf4;border:1.5px solid #86efac; }
.cr-d-answer-name { font-weight:700;font-size:.72rem;color:#4f46e5;margin-bottom:.2rem; }
.cr-d-ans-form { margin-top:.8rem;border-top:1.5px solid #f0f4f8;padding-top:.8rem; }
.cr-d-like { display:inline-flex;align-items:center;gap:.3rem;font-size:.73rem;
             font-weight:700;color:#dc2626;background:#fee2e2;border:none;
             border-radius:8px;padding:.3rem .75rem;cursor:pointer;transition:all .15s; }
.cr-d-like:hover { background:#fecaca; }

/* ── Study Notes ── */
.sn-card2  { background:#fff;border:1.5px solid #f0f4f8;border-radius:14px;
             margin-bottom:.65rem;overflow:hidden;transition:box-shadow .15s; }
.sn-card2:hover { box-shadow:0 4px 16px rgba(0,0,0,.07); }
.sn-card2.important { border-left:3px solid #f59e0b; }
.sn-head2  { display:flex;align-items:flex-start;gap:.75rem;padding:.85rem 1rem;cursor:pointer; }
.sn-num2   { width:26px;height:26px;border-radius:7px;background:#ede9fe;color:#4f46e5;
             font-size:.7rem;font-weight:800;display:flex;align-items:center;
             justify-content:center;flex-shrink:0;margin-top:.1rem; }
.sn-q2     { font-size:.82rem;font-weight:700;color:#0f172a;flex:1;line-height:1.4; }
.sn-body2  { border-top:1.5px solid #f0f4f8;padding:.85rem 1rem;display:none;
             background:#fafbff;font-size:.8rem;color:#475569;line-height:1.8; }
.sn-body2.open { display:block; }
.sn-bm2   { padding:0;border:none;background:none;font-size:1rem;
            cursor:pointer;color:#cbd5e1;transition:color .15s;flex-shrink:0; }
.sn-bm2.saved { color:#f59e0b; }

/* ── Sidebar ── */
.cr-sidebar { background:#fff;border-left:1.5px solid #f0f4f8;
              height:calc(100vh - 60px);overflow-y:auto;position:sticky;top:0;
              display:flex;flex-direction:column; }
@media(max-width:900px){ .cr-sidebar { height:auto;position:static;border-left:none;border-top:2px solid #f0f4f8; } }

.cr-sb-head { padding:1.1rem 1.25rem;border-bottom:1.5px solid #f0f4f8;flex-shrink:0; }
.cr-sb-title{ font-size:.82rem;font-weight:800;color:#0f172a;margin-bottom:.7rem;font-family:'SUSE',sans-serif; }
.cr-prog-bar{ height:6px;background:#f0f4f8;border-radius:100px;overflow:hidden;margin-bottom:.35rem; }
.cr-prog-fill{ height:100%;background:linear-gradient(90deg,#4f46e5,#7c3aed);
               border-radius:100px;transition:width 1s cubic-bezier(.16,1,.3,1); }
.cr-prog-lbl{ display:flex;justify-content:space-between;font-size:.67rem;
              font-weight:700;color:#94a3b8; }

.cr-sb-list { flex:1;overflow-y:auto;padding:.75rem; }

/* Chapter accordion */
.cr-ch     { border:1.5px solid #f0f4f8;border-radius:12px;margin-bottom:.6rem;overflow:hidden; }
.cr-ch-head{ display:flex;align-items:center;justify-content:space-between;gap:.5rem;
             padding:.7rem .9rem;cursor:pointer;background:#f8fafc;
             transition:background .15s; }
.cr-ch-head:hover { background:#f1f5f9; }
.cr-ch-name{ font-size:.78rem;font-weight:800;color:#0f172a;flex:1; }
.cr-ch-meta{ font-size:.65rem;color:#94a3b8;font-weight:600;white-space:nowrap; }
.cr-ch-ico { font-size:.7rem;color:#94a3b8;transition:transform .2s; }
.cr-ch.open .cr-ch-ico { transform:rotate(180deg); }
.cr-ch-body{ display:none;background:#fff; }
.cr-ch.open .cr-ch-body { display:block; }

/* Lesson row */
.cr-lesson { display:flex;align-items:center;gap:.7rem;padding:.6rem .9rem;
             cursor:pointer;transition:background .15s;border-bottom:1px solid #f8fafc; }
.cr-lesson:last-child { border-bottom:none; }
.cr-lesson:hover { background:#f8fafc; }
.cr-lesson.active { background:#ede9fe; }
.cr-lesson.locked { cursor:default;opacity:.5; }
.cr-l-icon { width:30px;height:30px;border-radius:8px;display:flex;align-items:center;
             justify-content:center;font-size:.75rem;flex-shrink:0; }
.cr-l-icon.done   { background:#dcfce7;color:#059669; }
.cr-l-icon.active { background:#4f46e5;color:#fff; }
.cr-l-icon.locked { background:#f1f5f9;color:#94a3b8; }
.cr-l-icon.free   { background:#f0fdf4;color:#059669; }
.cr-l-icon.idle   { background:#f1f5f9;color:#475569; }
.cr-l-info { flex:1;min-width:0; }
.cr-l-title{ font-size:.75rem;font-weight:700;color:#0f172a;
             white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.cr-l-meta { font-size:.63rem;color:#94a3b8;margin-top:.1rem; }
.cr-l-badge{ font-size:.6rem;font-weight:800;padding:.1rem .4rem;border-radius:100px;
             background:#dcfce7;color:#166534;white-space:nowrap;flex-shrink:0; }

/* Mark complete btn — inline in sidebar lesson */
.cr-mark-btn { display:inline-flex;align-items:center;gap:.3rem;padding:.32rem .7rem;
               border-radius:8px;border:none;cursor:pointer;font-size:.68rem;font-weight:700;
               font-family:inherit;background:linear-gradient(135deg,#059669,#10b981);
               color:#fff;box-shadow:0 2px 6px rgba(5,150,105,.25);transition:all .15s; }
.cr-mark-btn:hover { filter:brightness(1.07); }
.cr-mark-btn:disabled { opacity:.5;cursor:not-allowed; }
.cr-l-actions { padding:.3rem .9rem .65rem 3.15rem;display:flex;gap:.4rem;flex-wrap:wrap; }

/* Viewer label */
.cr-viewer-label { background:#1e1b4b;padding:.5rem 1.25rem;
                   display:flex;align-items:center;gap:.75rem;flex-wrap:wrap; }
.cr-vl-title { font-size:.8rem;font-weight:700;color:#fff;flex:1;
               white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.cr-vl-type  { font-size:.65rem;font-weight:800;padding:.2rem .6rem;border-radius:100px;
               background:rgba(255,255,255,.12);color:rgba(255,255,255,.7); }

/* Skeleton */
.cr-skel { background:linear-gradient(90deg,#f0f4f8 25%,#e2e8f0 50%,#f0f4f8 75%);
           background-size:200% 100%;animation:crShim 1.4s infinite;border-radius:8px; }
@keyframes crShim { 0%{background-position:200%}100%{background-position:-200%} }

/* Empty state */
.cr-empty { text-align:center;padding:2.5rem 1rem;color:#94a3b8; }
.cr-empty i { font-size:2.5rem;display:block;margin-bottom:.65rem; }
.cr-empty-title { font-weight:700;color:#475569;font-size:.85rem;margin-bottom:.25rem; }

/* Locked overlay */
.cr-locked-overlay { position:absolute;inset:0;display:flex;flex-direction:column;
                     align-items:center;justify-content:center;gap:.85rem;
                     background:rgba(10,10,20,.85);backdrop-filter:blur(4px);color:#fff; }
.cr-locked-overlay i { font-size:3rem;color:rgba(255,255,255,.5); }
.cr-locked-overlay p { font-size:.85rem;font-weight:700;color:rgba(255,255,255,.8);margin:0; }
</style>

<div class="cr-wrap">

<!-- ── Top bar ── -->
<div class="cr-topbar">
  <a href="?view=3002" class="cr-back"><i class="bi bi-arrow-left"></i>Dashboard</a>
  <div class="cr-topbar-title">
    <h1><?= htmlspecialchars($_course['title']) ?></h1>
    <div class="cr-topbar-meta">
      <?php if ($_iname): ?>
      <span class="cr-chip"><i class="bi bi-person-fill"></i><?= $_iname ?></span>
      <?php endif; ?>
      <span class="cr-chip"><i class="bi bi-collection"></i><?= $_chapters ?> chapters</span>
      <span class="cr-chip"><i class="bi bi-play-circle"></i><?= $_lessons ?> lessons</span>
      <?php if ($_rating !== '—'): ?>
      <span class="cr-chip"><span class="stars" style="color:#f59e0b">★</span><?= $_rating ?> (<?= $_reviews ?>)</span>
      <?php endif; ?>
      <?php if ($_price > 0): ?>
      <span class="cr-chip" style="color:#86efac;border-color:rgba(134,239,172,.3)">
        TZS <?= number_format($_final) ?>
        <?php if ($_disc > 0): ?><s style="opacity:.5;font-size:.6rem">TZS <?= number_format($_price) ?></s><?php endif; ?>
      </span>
      <?php else: ?>
      <span class="cr-chip" style="color:#86efac;border-color:rgba(134,239,172,.3)"><i class="bi bi-gift"></i>Free</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ── Layout ── -->
<div class="cr-layout">

  <!-- ══ MAIN ══ -->
  <div class="cr-main">

    <!-- Content viewer -->
    <div class="cr-viewer">
      <div class="cr-viewer-inner" id="crViewerInner">
        <div class="cr-no-content">
          <i class="bi bi-collection-play"></i>
          <p>Select a lesson to begin</p>
        </div>
      </div>
      <div class="cr-viewer-label" id="crViewerLabel" style="display:none">
        <span class="cr-vl-title" id="crViewerTitle"></span>
        <span class="cr-vl-type"  id="crViewerType"></span>
      </div>
    </div>

    <!-- Tabs -->
    <div class="cr-tabs-wrap">
      <div class="cr-tabs" role="tablist">
        <button class="cr-tab active" id="crTabAbout"   onclick="crTab('about')"><i class="bi bi-journal-text"></i>About</button>
        <button class="cr-tab" id="crTabNotes"   onclick="crTab('notes')">
          <i class="bi bi-journal-bookmark-fill"></i>Study Notes
          <span class="cr-tab-badge" id="crNotesBadge" style="display:none">0</span>
        </button>
        <button class="cr-tab" id="crTabDiscuss" onclick="crTab('discuss')">
          <i class="bi bi-chat-right-text"></i>Discussion
          <span class="cr-tab-badge" id="crDiscussBadge">0</span>
        </button>
      </div>
    </div>

    <!-- Panels -->
    <div class="cr-panels">

      <!-- About -->
      <div class="cr-panel active" id="crPanelAbout">
        <?php if ($_iname || $_iimg): ?>
        <div class="cr-about-header">
          <?php if ($_iimg): ?>
          <img src="<?= htmlspecialchars($_iimg) ?>" class="cr-instr-avatar" alt="" onerror="this.outerHTML='<div class=\'cr-instr-avatar\'><i class=\'bi bi-person-fill\'></i></div>'">
          <?php else: ?>
          <div class="cr-instr-avatar"><i class="bi bi-person-fill"></i></div>
          <?php endif; ?>
          <div>
            <div class="cr-instr-name"><?= $_iname ?></div>
            <div class="cr-instr-sub"><?= htmlspecialchars($_instructor['course'] ?? 'Instructor') ?></div>
          </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($_course['description'])): ?>
        <div class="cr-desc-box"><?= nl2br(htmlspecialchars($_course['description'])) ?></div>
        <?php else: ?>
        <div class="cr-desc-box" style="color:#94a3b8;font-style:italic">No description provided for this course.</div>
        <?php endif; ?>

        <div class="cr-stat-pills">
          <span class="cr-stat-pill"><i class="bi bi-collection" style="color:#4f46e5"></i><?= $_chapters ?> Chapters</span>
          <span class="cr-stat-pill"><i class="bi bi-play-circle" style="color:#059669"></i><?= $_lessons ?> Lessons</span>
          <span class="cr-stat-pill"><i class="bi bi-people" style="color:#0ea5e9"></i><?= $_enrolled ?> Enrolled</span>
          <?php if ($_rating !== '—'): ?>
          <span class="cr-stat-pill"><span style="color:#f59e0b">★</span><?= $_rating ?> Rating</span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Study Notes -->
      <div class="cr-panel" id="crPanelNotes">
        <div id="crNotesContent">
          <div class="cr-empty">
            <i class="bi bi-journal-x"></i>
            <div class="cr-empty-title">No notes yet</div>
            <div style="font-size:.78rem">Select a lesson to view its study notes</div>
          </div>
        </div>
      </div>

      <!-- Discussion -->
      <div class="cr-panel" id="crPanelDiscuss">
        <div class="cr-discuss-form">
          <h6><i class="bi bi-chat-right-text me-2" style="color:#4f46e5"></i>Ask a Question</h6>
          <input  type="text" id="crQTitle" class="cr-inp" placeholder="Question title…" autocomplete="off">
          <textarea id="crQDesc" class="cr-inp" rows="3" placeholder="Describe your question (optional)…"></textarea>
          <button class="cr-btn cr-btn-primary cr-btn-sm" onclick="crPostQuestion()">
            <i class="bi bi-send-fill"></i>Post Question
          </button>
        </div>
        <div id="crDiscussList">
          <div class="cr-skel" style="height:60px;margin-bottom:.5rem"></div>
          <div class="cr-skel" style="height:60px;margin-bottom:.5rem"></div>
        </div>
      </div>

    </div><!-- /.cr-panels -->
  </div><!-- /.cr-main -->

  <!-- ══ SIDEBAR ══ -->
  <div class="cr-sidebar">
    <div class="cr-sb-head">
      <div class="cr-sb-title">Course Content</div>
      <div class="cr-prog-bar"><div class="cr-prog-fill" id="crProgFill" style="width:0%"></div></div>
      <div class="cr-prog-lbl">
        <span id="crProgTxt">0% complete</span>
        <span id="crProgFrac">0 / <?= $_lessons ?></span>
      </div>
    </div>
    <div class="cr-sb-list" id="crSbList">
      <?php for ($i=0;$i<3;$i++): ?>
      <div class="cr-skel" style="height:44px;margin-bottom:.5rem;border-radius:12px"></div>
      <?php endfor; ?>
    </div>
  </div>

</div><!-- /.cr-layout -->
</div><!-- /.cr-wrap -->

<script>
/* ════════════════════════════════════════════════════════════
   STATE
════════════════════════════════════════════════════════════ */
const CR_COURSE_ID = <?= $_cid ?>;
let crCurrentLesson = null;   // {id, title, file_path, content_type, storage, video_id}
let crCompleted     = new Set();
let crIsPaid        = false;
let crAllLessons    = [];
let crSnNotes       = [];
let crSnTab         = 'all';
let crSnSearch      = '';
let crDiscussions   = [];

/* ════════════════════════════════════════════════════════════
   INIT
════════════════════════════════════════════════════════════ */
function _crInit() {
  crLoadSidebar();
  crLoadDiscussions();
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', _crInit);
} else { _crInit(); }

/* ════════════════════════════════════════════════════════════
   TABS
════════════════════════════════════════════════════════════ */
function crTab(name) {
  ['about','notes','discuss'].forEach(t => {
    document.getElementById('crTab'    + cap(t) + (t==='discuss'?'uss':''))?.classList.toggle('active', t === name);
    document.getElementById('crPanel' + cap(t) + (t==='discuss'?'uss':''))?.classList.toggle('active', t === name);
  });
  // simpler approach:
  document.querySelectorAll('.cr-tab').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.cr-panel').forEach(p => p.classList.remove('active'));
  const tabMap = {about:'crTabAbout',notes:'crTabNotes',discuss:'crTabDiscuss'};
  const panMap = {about:'crPanelAbout',notes:'crPanelNotes',discuss:'crPanelDiscuss'};
  document.getElementById(tabMap[name])?.classList.add('active');
  document.getElementById(panMap[name])?.classList.add('active');
}
function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

/* ════════════════════════════════════════════════════════════
   SIDEBAR LESSON LIST
════════════════════════════════════════════════════════════ */
function crLoadSidebar() {
  fetch(`ajax/ajax_fetch_lessons_paid.php?course_id=${CR_COURSE_ID}`)
    .then(r => r.json())
    .then(res => {
      crIsPaid = !!res.is_paid;
      crCompleted = new Set((res.completed_lessons || []).map(Number));
      const chapters = res.data || [];

      crAllLessons = [];
      chapters.forEach(ch => ch.lessons.forEach(l => crAllLessons.push(l)));

      crUpdateProgress();
      crRenderSidebar(chapters);

      // Auto-play first available lesson
      let first = null;
      for (const ch of chapters) {
        for (const l of ch.lessons) {
          if (crIsPaid || parseInt(l.isFreePreviewLesson) === 1) {
            first = l; break;
          }
        }
        if (first) break;
      }
      if (first) crPlayLesson(first);
    })
    .catch(() => {
      document.getElementById('crSbList').innerHTML =
        '<div class="cr-empty"><i class="bi bi-exclamation-circle"></i><div class="cr-empty-title">Could not load lessons</div></div>';
    });
}

function crUpdateProgress() {
  const total = crAllLessons.length;
  const done  = crCompleted.size;
  const pct   = total > 0 ? Math.round(done / total * 100) : 0;
  document.getElementById('crProgFill').style.width = pct + '%';
  document.getElementById('crProgTxt').textContent  = pct + '% complete';
  document.getElementById('crProgFrac').textContent = done + ' / ' + total;
}

function crRenderSidebar(chapters) {
  const list = document.getElementById('crSbList');
  if (!chapters.length) {
    list.innerHTML = '<div class="cr-empty"><i class="bi bi-collection"></i><div class="cr-empty-title">No lessons yet</div></div>';
    return;
  }

  let num = 0;
  list.innerHTML = chapters.map((ch, ci) => {
    const isOpen = ci === 0 || ch.lessons.some(l => crCurrentLesson && l.id == crCurrentLesson.id);
    const chDone = ch.lessons.filter(l => crCompleted.has(parseInt(l.id))).length;
    const lessons = ch.lessons.map(l => {
      num++;
      const isFree   = parseInt(l.isFreePreviewLesson) === 1;
      const canPlay  = crIsPaid || isFree;
      const isDone   = crCompleted.has(parseInt(l.id));
      const isActive = crCurrentLesson && l.id == crCurrentLesson.id;
      const ct       = (l.content_type || 'video').toLowerCase();
      const ctIcon   = ct === 'pdf' ? 'bi-file-earmark-pdf' : ct === 'audio' ? 'bi-music-note-beamed' : 'bi-play-circle-fill';
      let iconCls = 'idle', iconI = ctIcon;
      if (!canPlay)    { iconCls = 'locked'; iconI = 'bi-lock-fill'; }
      else if (isDone) { iconCls = 'done';   iconI = 'bi-check2'; }
      else if (isActive){ iconCls = 'active'; }
      else if (isFree && !crIsPaid) { iconCls = 'free'; }

      const dur = l.video_duration ? `<span>${l.video_duration}</span>` : '';
      const freeTag = isFree && !crIsPaid ? '<span class="cr-l-badge">Free</span>' : '';
      const lJson   = JSON.stringify(l).replace(/"/g, '&quot;');
      const markBtn = canPlay && !isDone
        ? `<div class="cr-l-actions" id="crMkWrap_${l.id}">
             <button class="cr-mark-btn" id="crMkBtn_${l.id}" data-lid="${l.id}" onclick="crMarkComplete(${l.id})">
               <i class="bi bi-check2-circle"></i> Mark Complete
             </button>
           </div>` : '';

      return `<div class="cr-lesson${isActive?' active':''}${!canPlay?' locked':''}" id="crL_${l.id}"
                   onclick="${canPlay ? `crPlayLesson('${lJson}')` : ''}">
        <div class="cr-l-icon ${iconCls}"><i class="bi ${iconI}"></i></div>
        <div class="cr-l-info">
          <div class="cr-l-title">${crEsc(l.lesson_title)}</div>
          <div class="cr-l-meta">${ct.toUpperCase()} ${dur}</div>
        </div>
        ${freeTag}
      </div>${markBtn}`;
    }).join('');

    return `<div class="cr-ch${isOpen?' open':''}" id="crCh_${ci}">
      <div class="cr-ch-head" onclick="crToggleCh(${ci})">
        <span class="cr-ch-name">${crEsc(ch.chapter_title)}</span>
        <span class="cr-ch-meta">${chDone}/${ch.lessons.length}</span>
        <i class="bi bi-chevron-down cr-ch-ico"></i>
      </div>
      <div class="cr-ch-body">${lessons}</div>
    </div>`;
  }).join('');
}

function crToggleCh(ci) {
  const el = document.getElementById('crCh_' + ci);
  if (el) el.classList.toggle('open');
}

/* ════════════════════════════════════════════════════════════
   VIDEO URL RESOLVER
════════════════════════════════════════════════════════════ */
function crResolveEmbed(lesson) {
  const ct      = (lesson.content_type || 'video').toLowerCase();
  const storage = (lesson.storage || 'upload').toLowerCase();
  const fp      = (lesson.file_path || '').trim();

  // PDF
  if (ct === 'pdf') {
    return { type:'pdf', src: fp };
  }

  // Audio
  if (ct === 'audio') {
    return { type:'audio', src: encodeURI(fp), thumb: lesson.lesson_thumbnail || '' };
  }

  // BunnyCDN stream: video_id + library_id (from DB)
  if (lesson.video_id && lesson.library_id) {
    return { type:'iframe', src:`https://iframe.mediadelivery.net/embed/${lesson.library_id}/${lesson.video_id}?autoplay=true&preload=true` };
  }

  // file_path contains a BunnyCDN player URL → convert to embed
  // e.g. https://player.mediadelivery.net/play/637820/8ad51273-...
  if (fp.includes('player.mediadelivery.net/play/')) {
    const m = fp.match(/player\.mediadelivery\.net\/play\/(\d+)\/([a-f0-9-]+)/i);
    if (m) return { type:'iframe', src:`https://iframe.mediadelivery.net/embed/${m[1]}/${m[2]}?autoplay=true&preload=true` };
  }

  // Already a BunnyCDN iframe embed URL
  if (fp.includes('iframe.mediadelivery.net/embed/')) {
    return { type:'iframe', src: fp.includes('autoplay') ? fp : fp + (fp.includes('?') ? '&' : '?') + 'autoplay=true' };
  }

  // YouTube embed URL (already /embed/)
  if (fp.includes('youtube.com/embed/') || fp.includes('youtu.be/')) {
    const src = fp.includes('youtube.com/embed/')
      ? (fp.includes('autoplay') ? fp : fp + (fp.includes('?') ? '&' : '?') + 'autoplay=1')
      : `https://www.youtube.com/embed/${fp.split('youtu.be/')[1]}?autoplay=1`;
    return { type:'iframe', src };
  }

  // YouTube watch URL → convert to embed
  if (fp.includes('youtube.com/watch')) {
    try {
      const u = new URL(fp);
      const v = u.searchParams.get('v');
      if (v) return { type:'iframe', src:`https://www.youtube.com/embed/${v}?autoplay=1` };
    } catch(e) {}
  }

  // Vimeo
  if (fp.includes('vimeo.com/')) {
    const vid = fp.split('vimeo.com/').pop().split('?')[0].split('/')[0];
    return { type:'iframe', src:`https://player.vimeo.com/video/${vid}?autoplay=1` };
  }

  // storage flag overrides
  if (storage === 'youtube') {
    const v = lesson.video_id || fp;
    return { type:'iframe', src:`https://www.youtube.com/embed/${v}?autoplay=1` };
  }
  if (storage === 'vimeo') {
    return { type:'iframe', src:`https://player.vimeo.com/video/${lesson.video_id}?autoplay=1` };
  }

  // Direct file (mp4, webm, etc.)
  if (fp) return { type:'video', src: encodeURI(fp) };

  return null;
}

/* ════════════════════════════════════════════════════════════
   LESSON PLAYER
════════════════════════════════════════════════════════════ */
function crPlayLesson(lesson) {
  if (typeof lesson === 'string') lesson = JSON.parse(lesson);
  crCurrentLesson = lesson;

  const inner = document.getElementById('crViewerInner');
  const label = document.getElementById('crViewerLabel');
  const ct    = (lesson.content_type || 'video').toLowerCase();

  const embed = crResolveEmbed(lesson);
  let viewerHtml = '';

  if (!embed) {
    viewerHtml = `<div class="cr-no-content"><i class="bi bi-exclamation-circle"></i><p>No content available for this lesson</p></div>`;
  } else if (embed.type === 'pdf') {
    viewerHtml = `<iframe src="${crEsc(embed.src)}" style="width:100%;height:100%;border:0;background:#fff" title="${crEsc(lesson.lesson_title)}"></iframe>`;
  } else if (embed.type === 'audio') {
    const thumb = embed.thumb ? `uploads/lessons/${crEsc(embed.thumb.split('/').pop())}` : '';
    const bgStyle = thumb ? `style="background-image:url(${thumb})"` : '';
    const coverInner = thumb
      ? `<img src="${thumb}" alt="" onerror="this.style.display='none'">`
      : `<i class="bi bi-music-note-beamed" style="font-size:2.5rem;color:rgba(255,255,255,.6)"></i>`;
    viewerHtml = `
      <div id="crAP">
        <div id="crAP-bg" ${bgStyle}></div>
        <button class="crAP-fs-btn" id="crAP-fsBtn" title="Fullscreen">
          <i class="bi bi-fullscreen" id="crAP-fsIcon"></i>
        </button>
        <div id="crAP-art">
          <div id="crAP-cover">${coverInner}</div>
          <div id="crAP-title">${crEsc(lesson.lesson_title||'')}</div>
          <div id="crAP-sub">Audio Lesson</div>
        </div>
        <div id="crAP-ctrl">
          <div class="crAP-seek-wrap">
            <div class="crAP-seek-fill" id="crAP-fill"></div>
            <input type="range" id="crAP-seek" min="0" max="1000" value="0" step="1">
          </div>
          <div class="crAP-time-row">
            <span id="crAP-cur">0:00</span><span id="crAP-dur">0:00</span>
          </div>
          <div class="crAP-btn-row" style="position:relative">
            <button class="crAP-btn" id="crAP-back" title="Back 10s"><i class="bi bi-skip-start-fill"></i></button>
            <button class="crAP-btn crAP-play" id="crAP-playBtn"><i class="bi bi-play-fill" id="crAP-playIcon"></i></button>
            <button class="crAP-btn" id="crAP-fwd" title="Forward 10s"><i class="bi bi-skip-end-fill"></i></button>
            <div class="crAP-vol-row">
              <button class="crAP-btn" id="crAP-volBtn" style="font-size:.9rem"><i class="bi bi-volume-up-fill" id="crAP-volIcon"></i></button>
              <input type="range" id="crAP-vol" min="0" max="1" step=".01" value="1">
            </div>
          </div>
        </div>
        <audio id="crAP-audio" src="${crEsc(embed.src)}" preload="metadata"></audio>
      </div>`;
  } else if (embed.type === 'iframe') {
    viewerHtml = `<iframe src="${crEsc(embed.src)}" allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture;fullscreen" allowfullscreen style="width:100%;height:100%;border:0"></iframe>`;
  } else if (embed.type === 'video') {
    viewerHtml = `<video controls autoplay src="${crEsc(embed.src)}" style="width:100%;height:100%;background:#000"></video>`;
  }

  inner.innerHTML = viewerHtml;
  if (embed && embed.type === 'audio') crInitAudioPlayer();

  // Label bar
  const ctLabel = { pdf:'PDF Document', audio:'Audio', video:'Video' }[ct] || 'Video';
  document.getElementById('crViewerTitle').textContent = lesson.lesson_title || '';
  document.getElementById('crViewerType').textContent  = ctLabel;
  label.style.display = 'flex';

  // Highlight active lesson + refresh mark-complete buttons in sidebar
  document.querySelectorAll('.cr-lesson').forEach(el => el.classList.remove('active'));
  const lessonEl = document.getElementById('crL_' + lesson.id);
  if (lessonEl) {
    lessonEl.classList.add('active');
    lessonEl.scrollIntoView({block:'nearest'});
  }
  crRefreshMarkBtns();

  // Load study notes
  crLoadStudyNotes(lesson.id);
}

/* ════════════════════════════════════════════════════════════
   CUSTOM AUDIO PLAYER
════════════════════════════════════════════════════════════ */
function crInitAudioPlayer() {
  const audio    = document.getElementById('crAP-audio');
  if (!audio) return;
  const playBtn  = document.getElementById('crAP-playBtn');
  const playIcon = document.getElementById('crAP-playIcon');
  const seek     = document.getElementById('crAP-seek');
  const fill     = document.getElementById('crAP-fill');
  const curEl    = document.getElementById('crAP-cur');
  const durEl    = document.getElementById('crAP-dur');
  const volBar   = document.getElementById('crAP-vol');
  const volBtn   = document.getElementById('crAP-volBtn');
  const volIcon  = document.getElementById('crAP-volIcon');
  const fsBtn    = document.getElementById('crAP-fsBtn');
  const fsIcon   = document.getElementById('crAP-fsIcon');
  const container= document.getElementById('crAP');

  function fmtT(s) {
    s = Math.floor(s || 0);
    return Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0');
  }

  playBtn.addEventListener('click', () => { audio.paused ? audio.play() : audio.pause(); });
  audio.addEventListener('play',  () => { playIcon.className = 'bi bi-pause-fill'; });
  audio.addEventListener('pause', () => { playIcon.className = 'bi bi-play-fill'; });
  audio.addEventListener('ended', () => { playIcon.className = 'bi bi-play-fill'; seek.value = 0; fill.style.width = '0%'; });

  audio.addEventListener('loadedmetadata', () => { durEl.textContent = fmtT(audio.duration); });
  audio.addEventListener('timeupdate', () => {
    if (!audio.duration) return;
    const pct = (audio.currentTime / audio.duration) * 100;
    fill.style.width = pct + '%';
    seek.value = Math.round(pct * 10);
    curEl.textContent = fmtT(audio.currentTime);
  });

  seek.addEventListener('input', () => {
    if (audio.duration) audio.currentTime = (seek.value / 1000) * audio.duration;
  });

  document.getElementById('crAP-back').addEventListener('click', () => { audio.currentTime = Math.max(0, audio.currentTime - 10); });
  document.getElementById('crAP-fwd').addEventListener('click',  () => { audio.currentTime = Math.min(audio.duration || 0, audio.currentTime + 10); });

  volBar.addEventListener('input', () => {
    audio.volume = parseFloat(volBar.value);
    audio.muted  = audio.volume === 0;
    volIcon.className = audio.muted ? 'bi bi-volume-mute-fill' : audio.volume < 0.5 ? 'bi bi-volume-down-fill' : 'bi bi-volume-up-fill';
  });
  volBtn.addEventListener('click', () => {
    audio.muted = !audio.muted;
    volIcon.className = audio.muted ? 'bi bi-volume-mute-fill' : 'bi bi-volume-up-fill';
  });

  function toggleFS() {
    const inFS = document.fullscreenElement === container || document.webkitFullscreenElement === container;
    if (inFS) {
      (document.exitFullscreen || document.webkitExitFullscreen).call(document);
    } else {
      (container.requestFullscreen || container.webkitRequestFullscreen).call(container);
    }
  }
  fsBtn.addEventListener('click', toggleFS);

  function onFSChange() {
    const inFS = document.fullscreenElement === container || document.webkitFullscreenElement === container;
    fsIcon.className = inFS ? 'bi bi-fullscreen-exit' : 'bi bi-fullscreen';
  }
  document.addEventListener('fullscreenchange', onFSChange);
  document.addEventListener('webkitfullscreenchange', onFSChange);

  audio.play().catch(() => {});
}

/* ════════════════════════════════════════════════════════════
   MARK COMPLETE
════════════════════════════════════════════════════════════ */
function crMarkComplete(lessonId) {
  const id  = lessonId || (crCurrentLesson && crCurrentLesson.id);
  if (!id) return;
  const btn = document.getElementById('crMkBtn_' + id);
  if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving…'; }

  fetch('ajax/ajax_mark_complete.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({lesson_id: id})
  }).then(r => r.json()).then(res => {
    if (res.status === 'success') {
      crCompleted.add(parseInt(id));
      crUpdateProgress();
      // Update sidebar icon
      const el = document.getElementById('crL_' + id);
      if (el) {
        const ico = el.querySelector('.cr-l-icon');
        if (ico) { ico.className = 'cr-l-icon done'; ico.innerHTML = '<i class="bi bi-check2"></i>'; }
      }
      crRefreshMarkBtns();
      // Auto advance to next lesson if it was the current one
      if (crCurrentLesson && crCurrentLesson.id == id) {
        const idx = crAllLessons.findIndex(l => l.id == id);
        if (idx !== -1 && idx + 1 < crAllLessons.length) {
          const next = crAllLessons[idx + 1];
          if (crIsPaid || parseInt(next.isFreePreviewLesson) === 1) {
            setTimeout(() => crPlayLesson(next), 700);
          }
        }
      }
    } else {
      if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check2-circle"></i> Mark Complete'; }
    }
  }).catch(() => {
    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check2-circle"></i> Mark Complete'; }
  });
}

function crRefreshMarkBtns() {
  document.querySelectorAll('[id^="crMkBtn_"]').forEach(btn => {
    const lid = parseInt(btn.dataset.lid);
    if (crCompleted.has(lid)) {
      btn.closest('.cr-l-actions')?.remove();
    }
  });
}

/* ════════════════════════════════════════════════════════════
   STUDY NOTES
════════════════════════════════════════════════════════════ */
function crLoadStudyNotes(lessonId) {
  fetch(`ajax/ajax_study_notes.php?action=list&lesson_id=${lessonId}`)
    .then(r => r.json())
    .then(res => {
      crSnNotes = res.data || [];
      const badge = document.getElementById('crNotesBadge');
      if (crSnNotes.length) {
        badge.textContent = crSnNotes.length;
        badge.style.display = '';
      } else {
        badge.style.display = 'none';
      }
      crRenderNotes();
    })
    .catch(() => { crSnNotes = []; crRenderNotes(); });
}

function crRenderNotes() {
  const cont = document.getElementById('crNotesContent');
  let filtered = crSnNotes.filter(n => {
    if (crSnTab === 'bookmarked' && !n.bookmarked) return false;
    if (crSnTab === 'important'  && !n.is_important) return false;
    if (crSnSearch) return (n.question + ' ' + n.answer).toLowerCase().includes(crSnSearch);
    return true;
  });

  if (!crSnNotes.length) {
    cont.innerHTML = '<div class="cr-empty"><i class="bi bi-journal-x"></i><div class="cr-empty-title">No study notes for this lesson</div></div>';
    return;
  }
  if (!filtered.length) {
    cont.innerHTML = '<div class="cr-empty"><i class="bi bi-search"></i><div class="cr-empty-title">No notes match your filter</div></div>';
    return;
  }

  const bkCnt = crSnNotes.filter(n=>n.bookmarked).length;
  const imCnt = crSnNotes.filter(n=>n.is_important).length;

  cont.innerHTML = `
    <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;margin-bottom:1rem">
      <input type="text" class="cr-inp" style="flex:1;min-width:180px;margin:0;padding:.45rem .75rem"
             placeholder="Search notes…" value="${crEsc(crSnSearch)}" oninput="crSnSearchInput(this.value)">
      <div style="display:flex;gap:.3rem;flex-shrink:0">
        <button class="cr-btn cr-btn-sm ${crSnTab==='all'?'cr-btn-primary':'cr-btn-ghost'}" onclick="crSnSetTab('all')">All (${crSnNotes.length})</button>
        <button class="cr-btn cr-btn-sm ${crSnTab==='bookmarked'?'cr-btn-primary':'cr-btn-ghost'}" onclick="crSnSetTab('bookmarked')">
          <i class="bi bi-bookmark-fill"></i>${bkCnt}
        </button>
        <button class="cr-btn cr-btn-sm ${crSnTab==='important'?'cr-btn-primary':'cr-btn-ghost'}" onclick="crSnSetTab('important')" style="${crSnTab==='important'?'':'background:#fef3c7;color:#92400e;border-color:#fde68a'}">
          <i class="bi bi-star-fill"></i>${imCnt}
        </button>
      </div>
    </div>
    ${filtered.map((n,i) => `
    <div class="sn-card2${n.is_important?' important':''}" id="snc_${n.id}">
      <div class="sn-head2" onclick="crSnToggle(${n.id})">
        <span class="sn-num2">${i+1}</span>
        <div class="sn-q2">
          ${n.is_important ? '<span style="font-size:.6rem;font-weight:800;background:#fef3c7;color:#92400e;border-radius:100px;padding:.1rem .4rem;margin-right:.4rem">KEY</span>' : ''}
          ${crHl(n.question)}
        </div>
        <button class="sn-bm2 ${n.bookmarked?'saved':''}" title="Bookmark" onclick="crSnBookmark(event,${n.id},this)">
          <i class="bi ${n.bookmarked?'bi-bookmark-fill':'bi-bookmark'}"></i>
        </button>
        <i class="bi bi-chevron-down" style="font-size:.7rem;color:#94a3b8;margin-left:.3rem;flex-shrink:0;transition:transform .2s" id="snChev_${n.id}"></i>
      </div>
      <div class="sn-body2" id="snb_${n.id}">
        <i class="bi bi-lightbulb-fill" style="color:#4f46e5;margin-right:.5rem;flex-shrink:0"></i>${crHl(n.answer)}
      </div>
    </div>`).join('')}
  `;
}

function crSnSearchInput(v) { crSnSearch = v.toLowerCase().trim(); crRenderNotes(); }
function crSnSetTab(t)      { crSnTab = t; crRenderNotes(); }

function crSnToggle(id) {
  const body = document.getElementById('snb_' + id);
  const chev = document.getElementById('snChev_' + id);
  if (!body) return;
  const open = body.classList.toggle('open');
  if (chev) chev.style.transform = open ? 'rotate(180deg)' : '';
}

async function crSnBookmark(e, noteId, btn) {
  e.stopPropagation();
  const res = await fetch('ajax/ajax_study_notes.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({action:'toggle_bookmark', note_id: noteId})
  }).then(r => r.json());
  if (res.status === 'success') {
    const n = crSnNotes.find(x => x.id == noteId);
    if (n) n.bookmarked = res.bookmarked ? 1 : 0;
    btn.classList.toggle('saved', !!res.bookmarked);
    btn.innerHTML = `<i class="bi ${res.bookmarked?'bi-bookmark-fill':'bi-bookmark'}"></i>`;
  }
}

function crHl(text) {
  const safe = String(text??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  if (!crSnSearch) return safe;
  const re = new RegExp(`(${crSnSearch.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi');
  return safe.replace(re, '<mark style="background:#fef08a;border-radius:2px;padding:0 2px">$1</mark>');
}

/* ════════════════════════════════════════════════════════════
   DISCUSSION
════════════════════════════════════════════════════════════ */
function crLoadDiscussions() {
  fetch(`ajax/ajax_fetch_discussions.php?course_id=${CR_COURSE_ID}`)
    .then(r => r.json())
    .then(res => {
      crDiscussions = res.data || [];
      document.getElementById('crDiscussBadge').textContent = crDiscussions.length;
      crRenderDiscussions();
    })
    .catch(() => {
      document.getElementById('crDiscussList').innerHTML =
        '<div class="cr-empty"><i class="bi bi-exclamation-circle"></i><div class="cr-empty-title">Could not load discussions</div></div>';
    });
}

function crRenderDiscussions() {
  const list = document.getElementById('crDiscussList');
  if (!crDiscussions.length) {
    list.innerHTML = '<div class="cr-empty"><i class="bi bi-chat-dots"></i><div class="cr-empty-title">No discussions yet</div><div style="font-size:.78rem">Be the first to ask a question</div></div>';
    return;
  }
  list.innerHTML = crDiscussions.map((d, i) => {
    const answers = (d.answers || []).map(a => `
      <div class="cr-d-answer-item${a.is_correct==1?' best':''}">
        <div class="cr-d-answer-name">${crEsc(a.first_name||'User')}
          ${a.is_correct==1?'<span style="font-size:.62rem;background:#059669;color:#fff;border-radius:100px;padding:.1rem .5rem;margin-left:.4rem">Best Answer</span>':''}
        </div>
        ${crEsc(a.answer||'')}
      </div>`).join('');

    return `<div class="cr-d-card" id="crd_${d.id}">
      <div class="cr-d-head" onclick="crDToggle(${d.id})">
        <span class="cr-d-num">${i+1}</span>
        <div style="flex:1;min-width:0">
          <div class="cr-d-title">${crEsc(d.title||'Question')}</div>
          <div class="cr-d-meta"><i class="bi bi-person me-1"></i>${crEsc(d.first_name||'')} · ${crEsc(d.created_at||'')} · ${d.total_answers||0} answers</div>
        </div>
        <i class="bi bi-chevron-down" id="crdChev_${d.id}" style="font-size:.75rem;color:#94a3b8;transition:transform .2s;flex-shrink:0"></i>
      </div>
      <div class="cr-d-body" id="crdb_${d.id}">
        ${d.description ? `<p style="font-size:.8rem;color:#475569;margin-bottom:.75rem">${crEsc(d.description)}</p>` : ''}
        <div style="display:flex;gap:.5rem;align-items:center;margin-bottom:.85rem">
          <button class="cr-d-like" onclick="crLike(${d.id})">❤ ${d.total_likes||0}</button>
        </div>
        ${answers || '<div style="font-size:.78rem;color:#94a3b8;margin-bottom:.75rem">No answers yet — be the first!</div>'}
        <div class="cr-d-ans-form">
          <textarea id="crAns_${d.id}" class="cr-inp" rows="2" placeholder="Write your answer…"></textarea>
          <button class="cr-btn cr-btn-primary cr-btn-sm" onclick="crSubmitAnswer(${d.id})">
            <i class="bi bi-send-fill"></i>Submit
          </button>
        </div>
      </div>
    </div>`;
  }).join('');
}

function crDToggle(id) {
  const body = document.getElementById('crdb_' + id);
  const chev = document.getElementById('crdChev_' + id);
  if (!body) return;
  const open = body.classList.toggle('open');
  if (chev) chev.style.transform = open ? 'rotate(180deg)' : '';
}

function crPostQuestion() {
  const title = (document.getElementById('crQTitle')?.value || '').trim();
  const desc  = (document.getElementById('crQDesc')?.value  || '').trim();
  if (!title) { crToast('Enter a question title', 'error'); return; }

  fetch('ajax/ajax_post_question.php', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: `course_id=${CR_COURSE_ID}&title=${encodeURIComponent(title)}&description=${encodeURIComponent(desc)}`
  }).then(r => r.json()).then(res => {
    if (res.status === 'success') {
      document.getElementById('crQTitle').value = '';
      document.getElementById('crQDesc').value  = '';
      crToast('Question posted!', 'success');
      crLoadDiscussions();
    } else { crToast(res.message || 'Failed', 'error'); }
  }).catch(() => crToast('Server error', 'error'));
}

function crSubmitAnswer(id) {
  const ans = (document.getElementById('crAns_' + id)?.value || '').trim();
  if (!ans) return;
  fetch('ajax/ajax_post_answer.php', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: `discussion_id=${id}&answer=${encodeURIComponent(ans)}`
  }).then(r => r.json()).then(() => { crLoadDiscussions(); });
}

function crLike(id) {
  fetch('ajax/ajax_like_discussion.php', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: `discussion_id=${id}`
  }).then(() => crLoadDiscussions());
}

/* ════════════════════════════════════════════════════════════
   HELPERS
════════════════════════════════════════════════════════════ */
function crEsc(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function crToast(msg, type) {
  if (typeof Swal !== 'undefined') {
    Swal.fire({icon: type==='error'?'error':'success', title: msg, timer:2000, showConfirmButton:false});
  } else { alert(msg); }
}

Object.assign(window, {
  crTab, crPlayLesson, crMarkComplete, crRefreshMarkBtns, crToggleCh,
  crSnToggle, crSnBookmark, crSnSearchInput, crSnSetTab,
  crDToggle, crPostQuestion, crSubmitAnswer, crLike
});
</script>
