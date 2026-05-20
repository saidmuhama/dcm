<?php
$course_id = (int)($_GET['course_id'] ?? 0);
if (!$course_id) { echo '<div class="p-4 text-center text-muted">Invalid course.</div>'; return; }

$usr = $_SESSION['usr_code'] ?? '';

/* ── Course ─────────────────────────────────────────────── */
$course = $db->query("
    SELECT * FROM tbl_courses
    WHERE id = {$course_id} AND status = 'active' AND deleted_at IS NULL
    LIMIT 1
")->fetch_assoc();
if (!$course) { echo '<div class="p-4 text-center text-muted">Course not found.</div>'; return; }

/* ── Instructor ─────────────────────────────────────────── */
$inst = $db->query("
    SELECT * FROM tbl_tutors WHERE usr_code='{$db->escape_string($course['instructor_id'])}' LIMIT 1
")->fetch_assoc();

/* ── Stats ──────────────────────────────────────────────── */
$stats = $db->query("
    SELECT
        (SELECT COUNT(*) FROM tbl_course_chapters WHERE course_id={$course_id}) AS chapters,
        (SELECT COUNT(*) FROM tbl_course_chapter_lessons WHERE course_id={$course_id} AND status='active') AS lessons,
        (SELECT COUNT(*) FROM tbl_course_chapter_lessons WHERE course_id={$course_id} AND isFreePreviewLesson='1') AS free_lessons,
        (SELECT COUNT(*) FROM tbl_orders o JOIN tbl_order_items oi ON oi.order_id=o.id WHERE oi.course_id={$course_id} AND o.payment_status='paid') AS students
")->fetch_assoc();

/* ── Enrollment ─────────────────────────────────────────── */
$enrolled = false;
$inCart   = false;
if ($usr) {
    $s = $db->prepare("
        SELECT COUNT(*) AS cnt FROM tbl_orders o
        JOIN tbl_order_items oi ON oi.order_id = o.id
        WHERE o.user_id = ? AND o.payment_status = 'paid' AND oi.course_id = ?");
    $s->bind_param('si', $usr, $course_id);
    $s->execute();
    $enrolled = (bool)($s->get_result()->fetch_assoc()['cnt'] ?? 0);

    $ic = $db->prepare("SELECT id FROM tbl_course_cart WHERE user_id=? AND course_id=?");
    $ic->bind_param('si', $usr, $course_id);
    $ic->execute();
    $inCart = (bool)$ic->get_result()->num_rows;
}

/* ── Price calculation ──────────────────────────────────── */
$price    = (float)($course['price'] ?? 0);
$disc     = (float)($course['discount'] ?? 0);
$final    = $price > 0 ? round($price - ($price * $disc / 100)) : 0;
$isFree   = $price == 0;

/* ── Demo video embed ───────────────────────────────────── */
$demoSrc  = $course['demo_video_source'] ?? '';
$demoEmbed = '';
if ($demoSrc) {
    if (preg_match('/youtube\.com\/watch\?v=([^&]+)/i', $demoSrc, $m)) {
        $demoEmbed = "https://www.youtube.com/embed/{$m[1]}";
    } elseif (preg_match('/youtu\.be\/([^?]+)/i', $demoSrc, $m)) {
        $demoEmbed = "https://www.youtube.com/embed/{$m[1]}";
    } elseif (preg_match('#iframe\.mediadelivery\.net/embed#', $demoSrc)) {
        $demoEmbed = $demoSrc;
    } elseif (preg_match('#player\.mediadelivery\.net/play/(\d+)/([a-f0-9\-]+)#i', $demoSrc, $m)) {
        $demoEmbed = "https://iframe.mediadelivery.net/embed/{$m[1]}/{$m[2]}";
    } else {
        $demoEmbed = $demoSrc;
    }
}

$thumb = !empty($course['thumbnail']) ? $course['thumbnail'] : 'uploads/course_default.png';
?>
<style>
/* ═══════════════════════════════════════════════════════
   COURSE PREVIEW  (cv-*)
═══════════════════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; }
.cv-root { font-family:'Inter','Open Sans',sans-serif; background:#f1f5f9; min-height:100vh; padding-bottom:3rem; }

/* Hero */
.cv-hero { background:linear-gradient(135deg,#1e1b4b 0%,#312e81 55%,#4338ca 100%); padding:1.75rem 2rem; position:relative; overflow:hidden; }
.cv-hero::before { content:''; position:absolute; inset:0; background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"); }
.cv-hero-inner { position:relative; max-width:1100px; margin:0 auto; }
.cv-breadcrumb { display:flex; align-items:center; gap:.4rem; font-size:.72rem; color:rgba(255,255,255,.5); margin-bottom:.85rem; flex-wrap:wrap; }
.cv-breadcrumb a { color:rgba(255,255,255,.5); text-decoration:none; }
.cv-breadcrumb a:hover { color:rgba(255,255,255,.85); }
.cv-hero-title { font-size:1.5rem; font-weight:900; color:#fff; font-family:'SUSE','Inter',sans-serif; letter-spacing:-.02em; margin-bottom:.6rem; line-height:1.25; }
.cv-hero-meta { display:flex; align-items:center; gap:.85rem; flex-wrap:wrap; margin-bottom:.85rem; }
.cv-badge { display:inline-flex; align-items:center; gap:.3rem; font-size:.72rem; font-weight:700; padding:.22rem .65rem; border-radius:100px; }
.cv-badge.green { background:rgba(16,185,129,.2); color:#6ee7b7; border:1px solid rgba(16,185,129,.25); }
.cv-badge.blue  { background:rgba(99,102,241,.25); color:#a5b4fc; border:1px solid rgba(99,102,241,.3); }
.cv-badge.amber { background:rgba(245,158,11,.2); color:#fcd34d; border:1px solid rgba(245,158,11,.25); }
.cv-inst-row { display:flex; align-items:center; gap:.6rem; }
.cv-inst-avatar { width:34px; height:34px; border-radius:50%; object-fit:cover; border:2px solid rgba(255,255,255,.25); flex-shrink:0; background:#475569; }
.cv-inst-name { font-size:.8rem; color:rgba(255,255,255,.85); font-weight:600; }
.cv-inst-sub  { font-size:.68rem; color:rgba(255,255,255,.5); }

/* Canvas */
.cv-canvas { max-width:1100px; margin:1.5rem auto 0; padding:0 1.25rem; display:grid; grid-template-columns:1fr 360px; gap:1.5rem; align-items:start; }
@media(max-width:900px) { .cv-canvas { grid-template-columns:1fr; } }

/* Card */
.cv-card { background:#fff; border-radius:18px; box-shadow:0 4px 24px rgba(0,0,0,.07),0 1px 4px rgba(0,0,0,.04); overflow:hidden; margin-bottom:1rem; }
.cv-card:last-child { margin-bottom:0; }
.cv-card-head { padding:.9rem 1.4rem; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:.5rem; }
.cv-card-title { font-size:.78rem; font-weight:800; color:#1e293b; text-transform:uppercase; letter-spacing:.06em; }
.cv-card-body { padding:1.4rem; }

/* Demo video */
.cv-video-wrap { position:relative; width:100%; padding-top:56.25%; background:#0f172a; border-radius:18px; overflow:hidden; margin-bottom:1rem; }
.cv-video-wrap iframe { position:absolute; inset:0; width:100%; height:100%; border:none; }
.cv-video-thumb { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; cursor:pointer; background:#0f172a; }
.cv-video-thumb img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:.5; }
.cv-play-btn { position:relative; z-index:1; width:68px; height:68px; border-radius:50%; background:rgba(255,255,255,.15); border:3px solid rgba(255,255,255,.5); display:flex; align-items:center; justify-content:center; backdrop-filter:blur(4px); transition:all .2s; }
.cv-play-btn:hover { background:rgba(255,255,255,.25); border-color:#fff; transform:scale(1.05); }
.cv-play-btn i { color:#fff; font-size:1.6rem; margin-left:4px; }
.cv-video-label { position:relative; z-index:1; color:rgba(255,255,255,.75); font-size:.78rem; font-weight:600; margin-top:.75rem; }

/* Tabs */
.cv-tabs { display:flex; gap:.25rem; border-bottom:2px solid #f1f5f9; margin-bottom:1.25rem; }
.cv-tab { padding:.55rem 1rem; font-size:.8rem; font-weight:700; color:#64748b; cursor:pointer; border:none; background:none; border-bottom:2px solid transparent; margin-bottom:-2px; transition:all .15s; }
.cv-tab.active { color:#4f46e5; border-bottom-color:#4f46e5; }

/* Curriculum */
.cv-chapter { border:1px solid #f1f5f9; border-radius:12px; margin-bottom:.6rem; overflow:hidden; }
.cv-chapter-hd { display:flex; align-items:center; justify-content:space-between; padding:.75rem 1rem; background:#f8fafc; cursor:pointer; gap:.5rem; user-select:none; }
.cv-chapter-hd:hover { background:#f1f5f9; }
.cv-chapter-title { font-size:.82rem; font-weight:800; color:#1e293b; flex:1; }
.cv-chapter-meta  { font-size:.7rem; color:#94a3b8; flex-shrink:0; }
.cv-chapter-chev  { font-size:.75rem; color:#94a3b8; transition:transform .2s; flex-shrink:0; }
.cv-chapter.open .cv-chapter-chev { transform:rotate(180deg); }
.cv-chapter-body { display:none; }
.cv-chapter.open .cv-chapter-body { display:block; }
.cv-lesson { display:flex; align-items:center; gap:.75rem; padding:.65rem 1rem; border-top:1px solid #f8fafc; transition:background .12s; cursor:pointer; }
.cv-lesson:hover.playable { background:#f5f3ff; }
.cv-lesson-num { width:28px; height:28px; border-radius:8px; background:#f1f5f9; display:flex; align-items:center; justify-content:center; font-size:.68rem; font-weight:800; color:#64748b; flex-shrink:0; }
.cv-lesson.playable .cv-lesson-num { background:#ede9fe; color:#6d28d9; }
.cv-lesson-body { flex:1; min-width:0; }
.cv-lesson-title { font-size:.8rem; font-weight:700; color:#1e293b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.cv-lesson-meta  { font-size:.68rem; color:#94a3b8; margin-top:.1rem; display:flex; align-items:center; gap:.5rem; }
.cv-lesson-right { flex-shrink:0; display:flex; align-items:center; gap:.5rem; }
.cv-free-pill { font-size:.62rem; font-weight:800; background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; border-radius:100px; padding:.1rem .5rem; }
.cv-lock-icon { color:#cbd5e1; font-size:.85rem; }
.cv-play-icon { color:#6d28d9; font-size:.95rem; }

/* Skeleton */
.cv-skel { background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%); background-size:200% 100%; animation:cvShim 1.5s infinite; border-radius:8px; }
@keyframes cvShim { 0%{background-position:200%}100%{background-position:-200%} }

/* Price card */
.cv-right { position:sticky; top:1.25rem; }
.cv-price-card { background:#fff; border-radius:18px; box-shadow:0 4px 24px rgba(0,0,0,.09),0 1px 4px rgba(0,0,0,.05); overflow:hidden; }
.cv-price-thumb { width:100%; height:160px; object-fit:cover; display:block; }
.cv-price-body { padding:1.25rem; }
.cv-price-row { display:flex; align-items:baseline; gap:.5rem; margin-bottom:.9rem; }
.cv-price-val { font-size:1.6rem; font-weight:900; color:#0f172a; }
.cv-price-orig { font-size:.88rem; color:#94a3b8; text-decoration:line-through; }
.cv-disc-pill { font-size:.7rem; font-weight:800; background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; border-radius:100px; padding:.15rem .55rem; }
.cv-cta { display:block; width:100%; padding:.85rem; border-radius:13px; border:none; font-size:.92rem; font-weight:800; font-family:inherit; cursor:pointer; text-align:center; text-decoration:none; transition:all .18s; margin-bottom:.6rem; }
.cv-cta.enroll  { background:linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff; box-shadow:0 4px 16px rgba(79,70,229,.35); }
.cv-cta.enroll:hover { filter:brightness(1.07); transform:translateY(-1px); }
.cv-cta.free    { background:linear-gradient(135deg,#059669,#10b981); color:#fff; box-shadow:0 4px 16px rgba(5,150,105,.35); }
.cv-cta.free:hover { filter:brightness(1.07); transform:translateY(-1px); }
.cv-cta.cart    { background:#f1f5f9; color:#475569; border:2px solid #e2e8f0; }
.cv-cta.cart:hover { background:#e2e8f0; }
.cv-cta.learn   { background:linear-gradient(135deg,#059669,#10b981); color:#fff; box-shadow:0 4px 16px rgba(5,150,105,.35); }
.cv-cta.learn:hover { filter:brightness(1.07); transform:translateY(-1px); }
.cv-course-stats { display:grid; grid-template-columns:1fr 1fr; gap:.6rem; margin-top:.9rem; }
.cv-stat-item { background:#f8fafc; border-radius:10px; padding:.6rem .75rem; }
.cv-stat-val { font-size:.95rem; font-weight:900; color:#0f172a; }
.cv-stat-lbl { font-size:.65rem; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
.cv-includes { margin-top:1rem; border-top:1px solid #f1f5f9; padding-top:1rem; }
.cv-include-row { display:flex; align-items:center; gap:.6rem; font-size:.78rem; color:#475569; margin-bottom:.45rem; }
.cv-include-row i { color:#6d28d9; font-size:.85rem; width:16px; }

/* Preview modal */
.cv-modal { display:none; position:fixed; inset:0; z-index:9999; background:rgba(15,23,42,.8); backdrop-filter:blur(6px); align-items:center; justify-content:center; }
.cv-modal.show { display:flex; }
.cv-modal-box { background:#0f172a; border-radius:18px; width:min(760px,95vw); overflow:hidden; box-shadow:0 32px 80px rgba(0,0,0,.5); animation:cvSlide .25s ease-out; }
@keyframes cvSlide { from{opacity:0;transform:scale(.95)} to{opacity:1;transform:scale(1)} }
.cv-modal-head { display:flex; align-items:center; justify-content:space-between; padding:.85rem 1.25rem; border-bottom:1px solid rgba(255,255,255,.08); }
.cv-modal-title { font-size:.88rem; font-weight:700; color:#fff; }
.cv-modal-close { width:30px; height:30px; border-radius:8px; border:none; background:rgba(255,255,255,.1); color:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:.85rem; transition:background .15s; }
.cv-modal-close:hover { background:rgba(255,255,255,.2); }
.cv-modal-player { position:relative; padding-top:56.25%; }
.cv-modal-player iframe { position:absolute; inset:0; width:100%; height:100%; border:none; }
.cv-modal-foot { padding:.75rem 1.25rem; display:flex; align-items:center; justify-content:space-between; }
.cv-modal-foot span { font-size:.75rem; color:rgba(255,255,255,.45); display:flex; align-items:center; gap:.4rem; }
</style>

<div class="cv-root">

  <!-- ── Hero ──────────────────────────────────────────── -->
  <div class="cv-hero">
    <div class="cv-hero-inner">
      <div class="cv-breadcrumb">
        <i class="bi bi-house-fill"></i>
        <a href="?view=3002">Dashboard</a>
        <span>›</span>
        <a href="?view=3002#browse">Courses</a>
        <span>›</span>
        <span style="color:rgba(255,255,255,.75)"><?= htmlspecialchars($course['title']) ?></span>
      </div>
      <div class="cv-hero-title"><?= htmlspecialchars($course['title']) ?></div>
      <div class="cv-hero-meta">
        <span class="cv-badge green"><i class="bi bi-collection-play"></i><?= (int)$stats['lessons'] ?> lessons</span>
        <span class="cv-badge blue"><i class="bi bi-bookmark"></i><?= (int)$stats['chapters'] ?> chapters</span>
        <?php if ($stats['free_lessons'] > 0): ?>
        <span class="cv-badge amber"><i class="bi bi-eye"></i><?= (int)$stats['free_lessons'] ?> free preview</span>
        <?php endif; ?>
        <?php if ($stats['students'] > 0): ?>
        <span class="cv-badge blue"><i class="bi bi-people"></i><?= number_format($stats['students']) ?> enrolled</span>
        <?php endif; ?>
      </div>
      <div class="cv-inst-row">
        <img class="cv-inst-avatar"
             src="<?= htmlspecialchars($inst['image'] ?? 'uploads/default_avatar.png') ?>"
             alt="" onerror="this.src='uploads/default_avatar.png'">
        <div>
          <div class="cv-inst-name"><?= htmlspecialchars(trim(($inst['first_name'] ?? '') . ' ' . ($inst['last_name'] ?? ''))) ?></div>
          <div class="cv-inst-sub"><?= htmlspecialchars($inst['course'] ?? 'Instructor') ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Canvas ─────────────────────────────────────────── -->
  <div class="cv-canvas">

    <!-- Left -->
    <div>

      <!-- Demo video or thumbnail -->
      <div class="cv-video-wrap" id="cvVideoWrap">
        <?php if ($demoEmbed): ?>
        <iframe src="<?= htmlspecialchars($demoEmbed) ?>"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen></iframe>
        <?php else: ?>
        <div class="cv-video-thumb">
          <img src="<?= htmlspecialchars($thumb) ?>" alt="" onerror="this.style.display='none'">
          <div class="cv-play-btn"><i class="bi bi-play-fill"></i></div>
          <div class="cv-video-label">Course Preview</div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Tabs -->
      <div class="cv-card">
        <div class="cv-card-body" style="padding-bottom:0">
          <div class="cv-tabs">
            <button class="cv-tab active" onclick="cvTab('about', this)">About</button>
            <button class="cv-tab" onclick="cvTab('curriculum', this)">Curriculum</button>
          </div>
        </div>

        <!-- About -->
        <div id="cvAbout" class="cv-card-body" style="padding-top:.75rem">
          <?php if ($course['description']): ?>
          <div style="font-size:.85rem;color:#475569;line-height:1.7">
            <?= nl2br(htmlspecialchars($course['description'])) ?>
          </div>
          <?php else: ?>
          <div style="font-size:.84rem;color:#94a3b8;text-align:center;padding:1.5rem 0">No description provided.</div>
          <?php endif; ?>
        </div>

        <!-- Curriculum -->
        <div id="cvCurriculum" class="cv-card-body" style="padding-top:.75rem;display:none">
          <div id="cvCurriculumContent">
            <?php for ($i=0; $i<3; $i++): ?>
            <div class="cv-skel" style="height:46px;margin-bottom:.6rem;border-radius:12px"></div>
            <?php endfor; ?>
          </div>
        </div>
      </div>

    </div>

    <!-- Right: price + CTA -->
    <div class="cv-right">
      <div class="cv-price-card">
        <img class="cv-price-thumb"
             src="<?= htmlspecialchars($thumb) ?>"
             alt="" onerror="this.src='uploads/course_default.png'">
        <div class="cv-price-body">

          <?php if ($enrolled): ?>
            <!-- Enrolled -->
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.9rem">
              <span style="font-size:.82rem;font-weight:800;color:#059669"><i class="bi bi-patch-check-fill me-1"></i>You're enrolled</span>
            </div>
            <a href="?view=read_course_details_data&course_id=<?= $course_id ?>" class="cv-cta learn">
              <i class="bi bi-play-circle-fill me-2"></i>Continue Learning
            </a>

          <?php elseif ($isFree): ?>
            <!-- Free course -->
            <div class="cv-price-row">
              <span class="cv-price-val">Free</span>
            </div>
            <button class="cv-cta free" id="cvEnrollBtn" onclick="cvEnrollFree(<?= $course_id ?>, this)">
              <i class="bi bi-mortarboard-fill me-2"></i>Enrol for Free
            </button>

          <?php else: ?>
            <!-- Paid course -->
            <div class="cv-price-row">
              <span class="cv-price-val">TZS <?= number_format($final) ?></span>
              <?php if ($disc > 0): ?>
              <span class="cv-price-orig">TZS <?= number_format($price) ?></span>
              <span class="cv-disc-pill"><?= $disc ?>% off</span>
              <?php endif; ?>
            </div>
            <?php if ($inCart): ?>
            <a href="?view=view_my_cart_to_pay" class="cv-cta enroll">
              <i class="bi bi-cart-check-fill me-2"></i>Go to Cart
            </a>
            <?php else: ?>
            <button class="cv-cta enroll" id="cvCartBtn" onclick="cvAddToCart(<?= $course_id ?>, this)">
              <i class="bi bi-cart-plus-fill me-2"></i>Add to Cart
            </button>
            <?php endif; ?>
            <a href="?view=view_my_cart_to_pay" class="cv-cta cart" style="display:<?= $inCart ? 'none' : 'block' ?>;margin-top:.25rem" id="cvGoCartLink">View Cart</a>

          <?php endif; ?>

          <!-- Course stats -->
          <div class="cv-course-stats">
            <div class="cv-stat-item">
              <div class="cv-stat-val"><?= (int)$stats['lessons'] ?></div>
              <div class="cv-stat-lbl">Lessons</div>
            </div>
            <div class="cv-stat-item">
              <div class="cv-stat-val"><?= (int)$stats['chapters'] ?></div>
              <div class="cv-stat-lbl">Chapters</div>
            </div>
            <div class="cv-stat-item">
              <div class="cv-stat-val"><?= (int)$stats['free_lessons'] ?></div>
              <div class="cv-stat-lbl">Free Preview</div>
            </div>
            <div class="cv-stat-item">
              <div class="cv-stat-val"><?= number_format($stats['students']) ?></div>
              <div class="cv-stat-lbl">Students</div>
            </div>
          </div>

          <!-- Includes -->
          <div class="cv-includes">
            <?php foreach ([
              ['bi-infinity',          'Lifetime access'],
              ['bi-phone',             'Access on all devices'],
              ['bi-patch-check',       'Verified instructor'],
              ['bi-eye',               (int)$stats['free_lessons'] . ' free preview lessons'],
            ] as [$ic, $txt]): ?>
            <div class="cv-include-row"><i class="bi <?= $ic ?>"></i><?= $txt ?></div>
            <?php endforeach; ?>
          </div>

        </div>
      </div>
    </div>

  </div>
</div>

<!-- Preview modal -->
<div class="cv-modal" id="cvModal">
  <div class="cv-modal-box">
    <div class="cv-modal-head">
      <span class="cv-modal-title" id="cvModalTitle">Lesson Preview</span>
      <button class="cv-modal-close" onclick="cvCloseModal()"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="cv-modal-player">
      <iframe id="cvModalFrame" src="" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
    </div>
    <div class="cv-modal-foot">
      <span><i class="bi bi-eye"></i> Free preview lesson</span>
      <span><i class="bi bi-lock-fill"></i> Full access requires enrolment</span>
    </div>
  </div>
</div>

<script>
(function () {

const COURSE_ID = <?= $course_id ?>;

/* ── Tab switch ─────────────────────────────────────────── */
function cvTab(tab, btn) {
  document.querySelectorAll('.cv-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('cvAbout').style.display      = tab === 'about'      ? '' : 'none';
  document.getElementById('cvCurriculum').style.display = tab === 'curriculum' ? '' : 'none';
  if (tab === 'curriculum' && !cvCurriculumLoaded) cvLoadCurriculum();
}

/* ── Curriculum ─────────────────────────────────────────── */
let cvCurriculumLoaded = false;

function cvLoadCurriculum() {
  cvCurriculumLoaded = true;
  fetch('ajax/ajax_fetch_course_preview.php?course_id=' + COURSE_ID)
    .then(r => r.json())
    .then(res => {
      if (res.status !== 'success') { cvCurriculumErr(); return; }
      cvRenderCurriculum(res.data, res.enrolled);
    })
    .catch(cvCurriculumErr);
}

function cvCurriculumErr() {
  document.getElementById('cvCurriculumContent').innerHTML =
    '<div style="text-align:center;padding:2rem;color:#94a3b8;font-size:.84rem"><i class="bi bi-wifi-off" style="font-size:1.5rem;display:block;margin-bottom:.5rem"></i>Could not load curriculum</div>';
}

function cvRenderCurriculum(chapters, enrolled) {
  const el = document.getElementById('cvCurriculumContent');
  if (!chapters.length) { el.innerHTML = '<p style="color:#94a3b8;font-size:.84rem;text-align:center;padding:1.5rem">No curriculum added yet.</p>'; return; }

  let lessonN = 1;
  el.innerHTML = chapters.map((ch, ci) => {
    const lessonHtml = ch.lessons.map(l => {
      const n     = lessonN++;
      const free  = l.is_free;
      const canPlay = l.can_play;
      const icon  = canPlay
        ? `<i class="bi bi-play-circle-fill cv-play-icon"></i>`
        : `<i class="bi bi-lock cv-lock-icon"></i>`;
      const pill  = free && !enrolled
        ? `<span class="cv-free-pill">Free</span>` : '';
      const dur   = l.duration ? `<span><i class="bi bi-clock me-1"></i>${l.duration}</span>` : '';
      const ct    = l.content_type !== 'Video' ? `<span><i class="bi bi-file-earmark me-1"></i>${l.content_type}</span>` : '<span><i class="bi bi-camera-video me-1"></i>Video</span>';
      const cls   = canPlay ? 'cv-lesson playable' : 'cv-lesson';
      const click = canPlay && l.embed_url
        ? `onclick="cvPlayLesson('${l.embed_url.replace(/'/g,"\\'")}','${(l.title||'').replace(/'/g,"\\'")}')"`
        : canPlay && !enrolled ? `onclick="cvPromptEnroll()"` : '';
      return `<div class="${cls}" ${click}>
        <div class="cv-lesson-num">${String(n).padStart(2,'0')}</div>
        <div class="cv-lesson-body">
          <div class="cv-lesson-title">${cvEsc(l.title)}</div>
          <div class="cv-lesson-meta">${ct}${dur}</div>
        </div>
        <div class="cv-lesson-right">${pill}${icon}</div>
      </div>`;
    }).join('');

    const freeCount = ch.lessons.filter(l => l.is_free).length;
    const meta = `${ch.lessons.length} lesson${ch.lessons.length!==1?'s':''}${freeCount?' · '+freeCount+' free':''}`;

    return `<div class="cv-chapter${ci===0?' open':''}" id="cvCh_${ch.id}">
      <div class="cv-chapter-hd" onclick="cvToggleChapter('cvCh_${ch.id}')">
        <span class="cv-chapter-title">${cvEsc(ch.title)}</span>
        <span class="cv-chapter-meta">${meta}</span>
        <i class="bi bi-chevron-down cv-chapter-chev"></i>
      </div>
      <div class="cv-chapter-body">${lessonHtml || '<div style="padding:.75rem 1rem;font-size:.8rem;color:#94a3b8">No lessons yet.</div>'}</div>
    </div>`;
  }).join('');
}

function cvToggleChapter(id) {
  document.getElementById(id).classList.toggle('open');
}

/* ── Preview modal ──────────────────────────────────────── */
function cvPlayLesson(url, title) {
  document.getElementById('cvModalTitle').textContent = title || 'Lesson Preview';
  document.getElementById('cvModalFrame').src = url;
  document.getElementById('cvModal').classList.add('show');
  document.body.style.overflow = 'hidden';
}

function cvCloseModal() {
  document.getElementById('cvModal').classList.remove('show');
  document.getElementById('cvModalFrame').src = '';
  document.body.style.overflow = '';
}

function cvPromptEnroll() {
  if (typeof Swal !== 'undefined') {
    Swal.fire({ icon:'info', title:'Enrolment required', text:'Please enrol or add this course to your cart to access all lessons.', confirmButtonText:'Enrol Now', confirmButtonColor:'#4f46e5', showCancelButton:true })
      .then(r => { if (r.isConfirmed) { document.getElementById('cvCartBtn')?.click(); } });
  }
}

/* ── Add to cart ────────────────────────────────────────── */
async function cvAddToCart(courseId, btn) {
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:14px;height:14px;border-width:2px"></span> Adding…';
  try {
    const r = await fetch('ajax/ajax_add_to_cart.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ course_id: courseId })
    });
    const d = await r.json();
    if (d.status === 'success' || d.status === 'already') {
      btn.innerHTML = '<i class="bi bi-cart-check-fill me-2"></i>Added to Cart';
      btn.className = 'cv-cta cart';
      const goLink = document.getElementById('cvGoCartLink');
      if (goLink) { goLink.style.display = 'block'; goLink.textContent = 'Go to Cart →'; goLink.classList.add('enroll'); goLink.classList.remove('cart'); }
      if (typeof sdLoadCartCount === 'function') sdLoadCartCount();
    } else if (d.status === 'enrolled') {
      btn.innerHTML = '<i class="bi bi-patch-check-fill me-2"></i>Already Enrolled';
      btn.className = 'cv-cta learn';
    } else {
      cvToast(d.message || 'Could not add to cart', false);
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-cart-plus-fill me-2"></i>Add to Cart';
    }
  } catch {
    cvToast('Network error', false);
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-cart-plus-fill me-2"></i>Add to Cart';
  }
}

/* ── Enrol free ─────────────────────────────────────────── */
async function cvEnrollFree(courseId, btn) {
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:14px;height:14px;border-width:2px"></span> Enrolling…';
  try {
    const r = await fetch('ajax/ajax_enroll_free_course.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ course_id: courseId })
    });
    const d = await r.json();
    if (d.status === 'success' || d.status === 'already') {
      btn.outerHTML = `<a href="?view=read_course_details_data&course_id=${courseId}" class="cv-cta learn"><i class="bi bi-play-circle-fill me-2"></i>Start Learning</a>`;
    } else {
      cvToast(d.message || 'Enrolment failed', false);
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-mortarboard-fill me-2"></i>Enrol for Free';
    }
  } catch {
    cvToast('Network error', false);
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-mortarboard-fill me-2"></i>Enrol for Free';
  }
}

/* ── Close modal on backdrop click ─────────────────────── */
document.getElementById('cvModal').addEventListener('click', function (e) {
  if (e.target === this) cvCloseModal();
});

/* ── Helpers ────────────────────────────────────────────── */
function cvEsc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function cvToast(msg, ok) {
  if (typeof Swal !== 'undefined') {
    Swal.fire({ icon:ok?'success':'error', title:msg, timer:3000, showConfirmButton:false, toast:true, position:'top-end' }); return;
  }
  alert(msg);
}

Object.assign(window, { cvTab, cvToggleChapter, cvPlayLesson, cvCloseModal, cvAddToCart, cvEnrollFree, cvPromptEnroll });
})();
</script>
