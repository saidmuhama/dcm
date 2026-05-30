<?php
require_once __DIR__ . '/../config/url_crypt_config.php';
$me = $_SESSION['usr_code'] ?? '';

$sql = "
    SELECT
        c.id AS cid, c.title AS ctitle, c.thumbnail, c.status AS cstatus,
        ch.id AS chid, ch.chapter_title, ch.`order` AS chorder,
        l.id AS lid, l.lesson_title, l.content_type, l.file_type,
        (SELECT COUNT(*) FROM study_notes sn WHERE sn.lesson_id = l.id) AS notes_count
    FROM tbl_courses c
    LEFT JOIN tbl_course_chapters ch ON ch.course_id = c.id
    LEFT JOIN tbl_course_chapter_lessons l ON l.chapter_id = ch.id AND l.status = 'active'
    WHERE c.instructor_id = '$me' AND c.deleted_at IS NULL
    ORDER BY c.id DESC, ch.`order` ASC, l.id ASC
";
$rows = $db->query($sql)->fetch_all(MYSQLI_ASSOC);

$courses = [];
foreach ($rows as $row) {
    $cid = $row['cid']; $chid = $row['chid']; $lid = $row['lid'];
    if (!isset($courses[$cid])) {
        $courses[$cid] = ['id'=>$cid,'title'=>$row['ctitle'],'thumbnail'=>$row['thumbnail'],'status'=>$row['cstatus'],'chapters'=>[]];
    }
    if ($chid && !isset($courses[$cid]['chapters'][$chid])) {
        $courses[$cid]['chapters'][$chid] = ['id'=>$chid,'title'=>$row['chapter_title'],'lessons'=>[]];
    }
    if ($chid && $lid) {
        $courses[$cid]['chapters'][$chid]['lessons'][] = ['id'=>$lid,'title'=>$row['lesson_title'],'content_type'=>$row['content_type']??'Video','file_type'=>$row['file_type']??'video','notes_count'=>(int)$row['notes_count']];
    }
}

$typeIcon = ['video'=>'bi-play-circle-fill','audio'=>'bi-music-note-beamed','pdf'=>'bi-file-earmark-pdf-fill','presentation'=>'bi-easel-fill','image'=>'bi-image-fill','live'=>'bi-broadcast-pin'];
$typeColor = ['video'=>'#6366f1','audio'=>'#8b5cf6','pdf'=>'#ef4444','presentation'=>'#f59e0b','image'=>'#10b981','live'=>'#3b82f6'];

$totalCourses = count($courses);
$totalLessons = $lessonsWithNotes = $totalNotes = 0;
foreach ($courses as $c) {
    foreach ($c['chapters'] as $ch) {
        foreach ($ch['lessons'] as $l) {
            $totalLessons++;
            $totalNotes += $l['notes_count'];
            if ($l['notes_count'] > 0) $lessonsWithNotes++;
        }
    }
}
$coverage = $totalLessons > 0 ? round($lessonsWithNotes / $totalLessons * 100) : 0;
?>
<style>
/* ═══════════════════════════════════════════════════════════════
   Teacher Study Notes — Hero Design
═══════════════════════════════════════════════════════════════ */
@keyframes tsn-fade   {from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}
@keyframes tsn-pop    {0%{transform:scale(.8);opacity:0}60%{transform:scale(1.06)}100%{transform:scale(1);opacity:1}}
@keyframes tsn-orb1   {from{transform:translate(0,0) scale(1)}to{transform:translate(-18px,14px) scale(1.18)}}
@keyframes tsn-orb2   {from{transform:translate(0,0) scale(1)}to{transform:translate(16px,-20px) scale(1.12)}}
@keyframes tsn-orb3   {from{transform:translate(0,0) scale(1)}to{transform:translate(-10px,-10px) scale(.88)}}
@keyframes tsn-kpi    {from{opacity:0;transform:translateY(22px) scale(.93)}to{opacity:1;transform:none}}
@keyframes tsn-card   {from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
@keyframes tsn-row    {from{opacity:0;transform:translateX(-8px)}to{opacity:1;transform:none}}
@keyframes tsn-bar    {from{width:0}to{width:var(--bw,0%)}}
@keyframes tsn-skel   {0%{background-position:200% 0}100%{background-position:-200% 0}}

/* ── Hero ── */
.tsn-hero{position:relative;overflow:hidden;border-radius:22px;background:linear-gradient(135deg,#050510 0%,#0d0929 35%,#1a0f3d 65%,#0e1420 100%);padding:2.1rem 2rem 1.9rem;margin:0 1rem;color:#fff;animation:tsn-fade .4s ease both}
.tsn-orb{position:absolute;border-radius:50%;filter:blur(55px);pointer-events:none}
.tsn-orb-1{width:240px;height:240px;background:rgba(99,102,241,.28);top:-70px;right:-20px;animation:tsn-orb1 8s ease-in-out infinite alternate}
.tsn-orb-2{width:160px;height:160px;background:rgba(139,92,246,.22);bottom:-50px;right:200px;animation:tsn-orb2 10s ease-in-out infinite alternate}
.tsn-orb-3{width:120px;height:120px;background:rgba(16,185,129,.18);top:20px;left:40%;animation:tsn-orb3 7s ease-in-out infinite alternate}
.tsn-hero-inner{position:relative;z-index:2;display:flex;align-items:center;gap:1.4rem;flex-wrap:wrap}
.tsn-hero-icon{width:68px;height:68px;border-radius:20px;background:rgba(255,255,255,.09);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.14);display:flex;align-items:center;justify-content:center;font-size:1.85rem;flex-shrink:0;box-shadow:0 8px 32px rgba(99,102,241,.4);animation:tsn-pop .7s cubic-bezier(.34,1.56,.64,1) both}
.tsn-hero-title{font-size:1.45rem;font-weight:900;letter-spacing:-.025em;line-height:1.1}
.tsn-hero-title span{background:linear-gradient(90deg,#a5b4fc 0%,#f9a8d4 50%,#6ee7b7 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.tsn-hero-sub{font-size:.83rem;opacity:.5;margin-top:.3rem}
.tsn-hero-pills{display:flex;flex-wrap:wrap;gap:.45rem;margin-top:.85rem}
.tsn-hero-pill{background:rgba(255,255,255,.09);border:1px solid rgba(255,255,255,.14);color:#fff;font-size:.7rem;font-weight:700;padding:.22rem .75rem;border-radius:20px;display:inline-flex;align-items:center;gap:.3rem}
.tsn-hero-pill-g{background:rgba(16,185,129,.2);border-color:rgba(16,185,129,.3);color:#6ee7b7}
.tsn-hero-actions{margin-left:auto;display:flex;gap:.6rem;flex-shrink:0}
.tsn-hbtn{padding:.52rem 1.1rem;border-radius:12px;font-size:.81rem;font-weight:700;cursor:pointer;border:none;display:flex;align-items:center;gap:.4rem;transition:all .2s;white-space:nowrap;text-decoration:none}
.tsn-hbtn-ghost{background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.18)}
.tsn-hbtn-ghost:hover{background:rgba(255,255,255,.2);color:#fff}

/* ── KPI Grid ── */
.tsn-kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:.9rem;margin:1.25rem 1rem 0}
@media(max-width:991px){.tsn-kpi-grid{grid-template-columns:repeat(2,1fr)}}
.tsn-kpi{border-radius:18px;padding:1.2rem 1.25rem;position:relative;overflow:hidden;box-shadow:0 2px 18px rgba(0,0,0,.07);transition:transform .25s,box-shadow .25s;animation:tsn-kpi .5s cubic-bezier(.34,1.56,.64,1) both}
.tsn-kpi:hover{transform:translateY(-5px)}
.tsn-kpi:nth-child(1){animation-delay:.05s}.tsn-kpi:nth-child(2){animation-delay:.1s}
.tsn-kpi:nth-child(3){animation-delay:.15s}.tsn-kpi:nth-child(4){animation-delay:.2s}
.tsn-kpi-ghost{position:absolute;right:-14px;bottom:-14px;font-size:4.8rem;opacity:.08;line-height:1;pointer-events:none}
.tsn-kpi-icon{width:44px;height:44px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;margin-bottom:.85rem;position:relative;z-index:1}
.tsn-kpi-lbl{font-size:.69rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;opacity:.6;position:relative;z-index:1}
.tsn-kpi-val{font-size:2.1rem;font-weight:900;line-height:1.1;position:relative;z-index:1;font-variant-numeric:tabular-nums}
.tsn-kpi-sub{font-size:.69rem;margin-top:.2rem;opacity:.5;position:relative;z-index:1}
.tsn-kpi-bar{height:3px;border-radius:99px;margin-top:.9rem;overflow:hidden;position:relative;z-index:1}
.tsn-kpi-fill{height:100%;border-radius:99px;width:0;transition:width 1.1s cubic-bezier(.4,0,.2,1)}

.tsn-kpi-courses{background:linear-gradient(145deg,#1e1b4b,#2e2a6e);color:#fff}
.tsn-kpi-courses .tsn-kpi-icon{background:rgba(255,255,255,.12);color:#c4b5fd}
.tsn-kpi-courses .tsn-kpi-bar{background:rgba(255,255,255,.12)}.tsn-kpi-courses .tsn-kpi-fill{background:linear-gradient(90deg,#a5b4fc,#c4b5fd)}
.tsn-kpi-courses:hover{box-shadow:0 14px 36px rgba(30,27,75,.35)}

.tsn-kpi-lessons{background:linear-gradient(145deg,#1e3a5f,#1e40af);color:#fff}
.tsn-kpi-lessons .tsn-kpi-icon{background:rgba(255,255,255,.1);color:#bfdbfe}
.tsn-kpi-lessons .tsn-kpi-bar{background:rgba(255,255,255,.12)}.tsn-kpi-lessons .tsn-kpi-fill{background:linear-gradient(90deg,#60a5fa,#bfdbfe)}
.tsn-kpi-lessons:hover{box-shadow:0 14px 36px rgba(30,58,95,.35)}

.tsn-kpi-notes{background:linear-gradient(145deg,#052e16,#065f46);color:#fff}
.tsn-kpi-notes .tsn-kpi-icon{background:rgba(255,255,255,.1);color:#6ee7b7}
.tsn-kpi-notes .tsn-kpi-bar{background:rgba(255,255,255,.12)}.tsn-kpi-notes .tsn-kpi-fill{background:linear-gradient(90deg,#34d399,#6ee7b7)}
.tsn-kpi-notes:hover{box-shadow:0 14px 36px rgba(5,46,22,.35)}

.tsn-kpi-cov{background:linear-gradient(145deg,#431407,#7c2d12);color:#fff}
.tsn-kpi-cov .tsn-kpi-icon{background:rgba(255,255,255,.1);color:#fed7aa}
.tsn-kpi-cov .tsn-kpi-bar{background:rgba(255,255,255,.12)}.tsn-kpi-cov .tsn-kpi-fill{background:linear-gradient(90deg,#fb923c,#fed7aa)}
.tsn-kpi-cov:hover{box-shadow:0 14px 36px rgba(67,20,7,.35)}

/* ── Toolbar ── */
.tsn-toolbar{background:#fff;border-radius:16px;padding:.85rem 1.2rem;margin:1.1rem 1rem .85rem;box-shadow:0 2px 14px rgba(0,0,0,.05);display:flex;flex-wrap:wrap;gap:.75rem;align-items:center;animation:tsn-fade .4s .15s ease both}
.tsn-search{flex:1;min-width:200px;max-width:320px;position:relative}
.tsn-search input{width:100%;border:1.5px solid #e0e7ff;border-radius:12px;padding:.48rem .9rem .48rem 2.2rem;font-size:.84rem;background:#f8f7ff;transition:all .2s}
.tsn-search input:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1);background:#fff}
.tsn-search-ico{position:absolute;left:.72rem;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:.88rem;pointer-events:none}
.tsn-results-count{font-size:.78rem;color:#94a3b8;font-weight:600;background:#f1f5f9;padding:.3rem .8rem;border-radius:20px}

/* ── Course accordion cards ── */
.tsn-courses{padding:0 1rem 2rem;animation:tsn-fade .4s .2s ease both}
.tsn-course-card{background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 2px 18px rgba(0,0,0,.06);margin-bottom:1rem;border:1.5px solid rgba(0,0,0,.04);transition:box-shadow .2s;animation:tsn-card .35s ease both}
.tsn-course-card:nth-child(1){animation-delay:.04s}.tsn-course-card:nth-child(2){animation-delay:.08s}
.tsn-course-card:nth-child(3){animation-delay:.12s}.tsn-course-card:nth-child(4){animation-delay:.16s}
.tsn-course-card:hover{box-shadow:0 6px 28px rgba(0,0,0,.1)}

/* Course header */
.tsn-course-hdr{display:flex;align-items:center;gap:1rem;padding:1rem 1.25rem;cursor:pointer;user-select:none;transition:background .15s;position:relative}
.tsn-course-hdr:hover{background:#f8f7ff}
.tsn-thumb{width:54px;height:42px;border-radius:10px;object-fit:cover;flex-shrink:0;box-shadow:0 2px 8px rgba(0,0,0,.12);transition:transform .2s}
.tsn-course-card:hover .tsn-thumb{transform:scale(1.05)}
.tsn-thumb-fallback{width:54px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;box-shadow:0 2px 8px rgba(0,0,0,.1)}
.tsn-course-info{flex:1;min-width:0}
.tsn-course-name{font-weight:800;font-size:.9rem;color:#1e1b4b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:.25rem}
.tsn-course-meta{display:flex;align-items:center;flex-wrap:wrap;gap:.5rem}
.tsn-status-chip{font-size:.65rem;font-weight:800;padding:2px 8px;border-radius:20px}
.tsn-chip-active{background:#d1fae5;color:#065f46}
.tsn-chip-draft{background:#f1f5f9;color:#475569}
.tsn-chip-inactive{background:#fee2e2;color:#991b1b}
.tsn-meta-pill{font-size:.71rem;font-weight:600;color:#64748b;display:inline-flex;align-items:center;gap:.28rem}
.tsn-notes-progress{flex-shrink:0;text-align:right}
.tsn-notes-big{font-size:1.4rem;font-weight:900;color:#6366f1;line-height:1}
.tsn-notes-lbl{font-size:.62rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.05em}
.tsn-hdr-actions{display:flex;align-items:center;gap:.5rem;flex-shrink:0}
.tsn-manage-btn{background:#f5f3ff;color:#6366f1;border:1.5px solid #e0e7ff;border-radius:10px;padding:.35rem .85rem;font-size:.76rem;font-weight:700;cursor:pointer;transition:all .2s;text-decoration:none;white-space:nowrap;display:inline-flex;align-items:center;gap:.3rem}
.tsn-manage-btn:hover{background:#6366f1;color:#fff;border-color:#6366f1}
.tsn-chevron{width:28px;height:28px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:.8rem;transition:all .3s;flex-shrink:0}
.tsn-course-card.open .tsn-chevron{background:#ede9fe;color:#6366f1;transform:rotate(180deg)}

/* Chapter + lesson rows */
.tsn-chapters{border-top:1px solid #f1f5f9}
.tsn-chapter-hdr{display:flex;align-items:center;gap:.6rem;padding:.6rem 1.25rem;background:linear-gradient(135deg,#f8f7ff,#f1f5f9);border-top:1px solid #e0e7ff}
.tsn-chapter-icon{width:26px;height:26px;border-radius:8px;background:#ede9fe;color:#6366f1;display:flex;align-items:center;justify-content:center;font-size:.72rem;flex-shrink:0}
.tsn-chapter-title{font-size:.75rem;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:.05em}
.tsn-chapter-count{margin-left:auto;font-size:.68rem;font-weight:700;color:#94a3b8;background:#fff;padding:1px 7px;border-radius:20px;border:1px solid #e0e7ff}

.tsn-lesson-row{display:flex;align-items:center;gap:.85rem;padding:.75rem 1.25rem;border-top:1px solid #f8fafc;transition:background .15s;animation:tsn-row .3s ease both}
.tsn-lesson-row:hover{background:#f8f7ff}
.tsn-lesson-type-ico{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;transition:transform .2s}
.tsn-lesson-row:hover .tsn-lesson-type-ico{transform:scale(1.1) rotate(-5deg)}
.tsn-lesson-title{font-weight:600;font-size:.84rem;color:#1e1b4b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:.15rem}
.tsn-lesson-type-tag{font-size:.65rem;font-weight:700;padding:1px 7px;border-radius:20px;background:#f1f5f9;color:#64748b;display:inline-flex;align-items:center;gap:.25rem}
.tsn-notes-badge{display:inline-flex;align-items:center;gap:.3rem;padding:3px 10px;border-radius:20px;font-size:.71rem;font-weight:800;flex-shrink:0;white-space:nowrap}
.tsn-badge-has{background:linear-gradient(135deg,#ede9fe,#e0e7ff);color:#4f46e5;box-shadow:0 2px 8px rgba(99,102,241,.2)}
.tsn-badge-none{background:#f8fafc;color:#cbd5e1;border:1.5px dashed #e0e7ff}
.tsn-action-btn{padding:.38rem .85rem;border-radius:10px;font-size:.76rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:.3rem;transition:all .2s;flex-shrink:0;white-space:nowrap}
.tsn-action-add{background:#f5f3ff;color:#6366f1;border:1.5px dashed #c4b5fd}
.tsn-action-add:hover{background:#6366f1;color:#fff;border-style:solid;transform:translateY(-1px)}
.tsn-action-edit{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;box-shadow:0 3px 10px rgba(99,102,241,.3)}
.tsn-action-edit:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(99,102,241,.45);color:#fff}

/* Empty states */
.tsn-empty-chapter{padding:.85rem 1.25rem;font-size:.78rem;color:#94a3b8;display:flex;align-items:center;gap:.5rem}
.tsn-empty-hero{padding:4rem 2rem;text-align:center;margin:1rem}
.tsn-empty-hero-icon{width:80px;height:80px;border-radius:22px;background:linear-gradient(135deg,#ede9fe,#e0e7ff);display:flex;align-items:center;justify-content:center;font-size:2rem;color:#6366f1;margin:0 auto 1.1rem;animation:tsn-pop .6s cubic-bezier(.34,1.56,.64,1) both}

/* Skeleton */
.tsn-skel{background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:tsn-skel 1.4s infinite;border-radius:8px}

/* Search highlight */
.tsn-highlight{background:linear-gradient(135deg,#fffbeb,#fef3c7);border-radius:3px;padding:0 2px}

/* Coverage ring */
.tsn-cov-ring{position:relative;z-index:1;display:inline-block}
</style>

<div class="container-fluid px-0">

<!-- ══ HERO ══ -->
<div class="tsn-hero mt-3">
    <div class="tsn-orb tsn-orb-1"></div>
    <div class="tsn-orb tsn-orb-2"></div>
    <div class="tsn-orb tsn-orb-3"></div>
    <div class="tsn-hero-inner">
        <div class="tsn-hero-icon"><i class="bi bi-journal-bookmark-fill"></i></div>
        <div class="flex-grow-1">
            <div class="tsn-hero-title">Study <span>Notes Manager</span></div>
            <div class="tsn-hero-sub">Add and manage Q&amp;A study notes for every lesson across all your courses</div>
            <div class="tsn-hero-pills">
                <span class="tsn-hero-pill"><i class="bi bi-collection-fill"></i><?= $totalCourses ?> course<?= $totalCourses!=1?'s':'' ?></span>
                <span class="tsn-hero-pill"><i class="bi bi-play-circle-fill"></i><?= $totalLessons ?> lesson<?= $totalLessons!=1?'s':'' ?></span>
                <span class="tsn-hero-pill tsn-hero-pill-g"><i class="bi bi-journal-bookmark-fill"></i><?= $totalNotes ?> Q&amp;A note<?= $totalNotes!=1?'s':'' ?></span>
                <?php if ($coverage > 0): ?>
                <span class="tsn-hero-pill" style="background:rgba(245,158,11,.2);border-color:rgba(245,158,11,.3);color:#fde68a"><i class="bi bi-bar-chart-fill"></i><?= $coverage ?>% lesson coverage</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="tsn-hero-actions d-none d-md-flex">
            <a href="?view=my_courses_online_contents_list_view" class="tsn-hbtn tsn-hbtn-ghost">
                <i class="bi bi-collection"></i>My Courses
            </a>
        </div>
    </div>
</div>

<!-- ══ KPI CARDS ══ -->
<div class="tsn-kpi-grid">
    <div class="tsn-kpi tsn-kpi-courses">
        <div class="tsn-kpi-ghost"><i class="bi bi-collection-play"></i></div>
        <div class="tsn-kpi-icon"><i class="bi bi-collection-play-fill"></i></div>
        <div class="tsn-kpi-lbl">Courses</div>
        <div class="tsn-kpi-val"><?= $totalCourses ?></div>
        <div class="tsn-kpi-sub">Courses with notes</div>
        <div class="tsn-kpi-bar"><div class="tsn-kpi-fill" style="--bw:100%;width:100%"></div></div>
    </div>
    <div class="tsn-kpi tsn-kpi-lessons">
        <div class="tsn-kpi-ghost"><i class="bi bi-play-circle"></i></div>
        <div class="tsn-kpi-icon"><i class="bi bi-play-circle-fill"></i></div>
        <div class="tsn-kpi-lbl">Total Lessons</div>
        <div class="tsn-kpi-val"><?= $totalLessons ?></div>
        <div class="tsn-kpi-sub">Active lessons total</div>
        <div class="tsn-kpi-bar"><div class="tsn-kpi-fill" style="--bw:100%;width:100%"></div></div>
    </div>
    <div class="tsn-kpi tsn-kpi-notes">
        <div class="tsn-kpi-ghost"><i class="bi bi-journal-bookmark"></i></div>
        <div class="tsn-kpi-icon"><i class="bi bi-journal-bookmark-fill"></i></div>
        <div class="tsn-kpi-lbl">Q&amp;A Notes</div>
        <div class="tsn-kpi-val"><?= $totalNotes ?></div>
        <div class="tsn-kpi-sub"><?= $lessonsWithNotes ?> lessons covered</div>
        <div class="tsn-kpi-bar"><div class="tsn-kpi-fill" style="--bw:<?= $totalLessons>0?round($lessonsWithNotes/$totalLessons*100):0 ?>%;"></div></div>
    </div>
    <div class="tsn-kpi tsn-kpi-cov">
        <div class="tsn-kpi-ghost"><i class="bi bi-bar-chart"></i></div>
        <div class="tsn-kpi-icon"><i class="bi bi-bar-chart-fill"></i></div>
        <div class="tsn-kpi-lbl">Coverage</div>
        <div class="tsn-kpi-val"><?= $coverage ?>%</div>
        <div class="tsn-kpi-sub">Lessons with at least 1 note</div>
        <div class="tsn-kpi-bar"><div class="tsn-kpi-fill" style="--bw:<?= $coverage ?>%;"></div></div>
    </div>
</div>

<?php if (empty($courses)): ?>
<!-- Empty hero -->
<div class="tsn-empty-hero">
    <div class="tsn-empty-hero-icon"><i class="bi bi-collection-play"></i></div>
    <h5 class="fw-bold mb-1" style="color:#1e1b4b">No courses yet</h5>
    <p class="text-muted mb-3" style="font-size:.87rem">Create a course first, then add study notes to each lesson.</p>
    <a href="?view=my_courses_online_contents_list_view" class="btn btn-sm rounded-pill px-4 fw-semibold" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;box-shadow:0 4px 14px rgba(99,102,241,.35)">
        <i class="bi bi-plus-lg me-1"></i>My Courses
    </a>
</div>

<?php else: ?>

<!-- ══ TOOLBAR ══ -->
<div class="tsn-toolbar">
    <div class="tsn-search">
        <i class="bi bi-search tsn-search-ico"></i>
        <input id="tsnSearch" placeholder="Search courses or lessons…" autocomplete="off">
    </div>
    <span class="tsn-results-count" id="tsnCount"><?= $totalCourses ?> course<?= $totalCourses!=1?'s':'' ?></span>
</div>

<!-- ══ COURSES ══ -->
<div class="tsn-courses" id="tsnCourses">
<?php
$thumbColors = ['#6366f1','#10b981','#f59e0b','#3b82f6','#8b5cf6','#ec4899','#14b8a6','#f97316'];
$ci = 0;
foreach ($courses as $course):
    $courseNotes   = array_sum(array_map(fn($ch)=>array_sum(array_column($ch['lessons'],'notes_count')), $course['chapters']));
    $courseLessons = array_sum(array_map(fn($ch)=>count($ch['lessons']), $course['chapters']));
    $ctoken        = encryptURLId((int)$course['id'], ctx:'course');
    $thumb         = $course['thumbnail'] ? 'uploads/'.basename($course['thumbnail']) : '';
    $thumbCol      = $thumbColors[$ci % count($thumbColors)];
    $stClass       = match($course['status']){'active'=>'tsn-chip-active','is_draft'=>'tsn-chip-draft',default=>'tsn-chip-inactive'};
    $stLabel       = match($course['status']){'active'=>'Published','is_draft'=>'Draft',default=>'Inactive'};
    $coveragePct   = $courseLessons > 0 ? round(array_sum(array_map(fn($ch)=>count(array_filter($ch['lessons'],fn($l)=>$l['notes_count']>0)), $course['chapters'])) / $courseLessons * 100) : 0;
    $isFirst       = ($ci === 0);
    $ci++;
?>
<div class="tsn-course-card <?= $isFirst?'open':'' ?>" data-course-id="<?= $course['id'] ?>" data-title="<?= strtolower(htmlspecialchars($course['title'])) ?>">

    <!-- Course header (clickable toggle) -->
    <div class="tsn-course-hdr" onclick="tsnToggleCourse(this.closest('.tsn-course-card'))">
        <!-- Thumbnail -->
        <?php if ($thumb): ?>
        <img class="tsn-thumb" src="<?= htmlspecialchars($thumb) ?>" alt="" onerror="this.outerHTML='<div class=\'tsn-thumb-fallback\' style=\'background:<?= $thumbCol ?>18;color:<?= $thumbCol ?>\'><i class=\'bi bi-collection-play-fill\'></i></div>'">
        <?php else: ?>
        <div class="tsn-thumb-fallback" style="background:<?= $thumbCol ?>18;color:<?= $thumbCol ?>"><i class="bi bi-collection-play-fill"></i></div>
        <?php endif; ?>

        <!-- Info -->
        <div class="tsn-course-info">
            <div class="tsn-course-name"><?= htmlspecialchars($course['title']) ?></div>
            <div class="tsn-course-meta">
                <span class="tsn-status-chip <?= $stClass ?>"><?= $stLabel ?></span>
                <span class="tsn-meta-pill"><i class="bi bi-play-circle"></i><?= $courseLessons ?> lesson<?= $courseLessons!=1?'s':'' ?></span>
                <?php if ($courseLessons > 0): ?>
                <span class="tsn-meta-pill"><i class="bi bi-bar-chart" style="color:#f59e0b"></i><?= $coveragePct ?>% covered</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Notes count -->
        <div class="tsn-notes-progress d-none d-sm-block">
            <div class="tsn-notes-big"><?= $courseNotes ?></div>
            <div class="tsn-notes-lbl">Note<?= $courseNotes!=1?'s':'' ?></div>
        </div>

        <!-- Actions -->
        <div class="tsn-hdr-actions" onclick="event.stopPropagation()">
            <a href="?view=course_contents_management&course_id=<?= $ctoken ?>" class="tsn-manage-btn">
                <i class="bi bi-pencil-square" style="pointer-events:none"></i>
                <span class="d-none d-md-inline">Content</span>
            </a>
        </div>
        <div class="tsn-chevron"><i class="bi bi-chevron-down" style="pointer-events:none"></i></div>
    </div>

    <!-- Chapters & lessons -->
    <div class="tsn-chapters" style="display:<?= $isFirst?'block':'none' ?>">
        <?php if (empty($course['chapters'])): ?>
        <div class="tsn-empty-chapter"><i class="bi bi-collection text-muted"></i>No chapters yet.</div>
        <?php else: foreach ($course['chapters'] as $chIdx => $chapter):
            $chNotes = array_sum(array_column($chapter['lessons'], 'notes_count'));
            $chColor = $thumbColors[$chIdx % count($thumbColors)];
        ?>
        <div>
            <!-- Chapter header -->
            <div class="tsn-chapter-hdr">
                <div class="tsn-chapter-icon" style="background:<?= $chColor ?>18;color:<?= $chColor ?>"><i class="bi bi-bookmark-fill"></i></div>
                <span class="tsn-chapter-title"><?= htmlspecialchars($chapter['title']) ?></span>
                <span class="tsn-chapter-count"><?= count($chapter['lessons']) ?> lesson<?= count($chapter['lessons'])!=1?'s':'' ?> · <?= $chNotes ?> note<?= $chNotes!=1?'s':'' ?></span>
            </div>

            <?php if (empty($chapter['lessons'])): ?>
            <div class="tsn-empty-chapter"><i class="bi bi-inbox text-muted"></i>No lessons in this chapter.</div>
            <?php else: foreach ($chapter['lessons'] as $lIdx => $lesson):
                $icon  = $typeIcon[$lesson['file_type']] ?? 'bi-file-play-fill';
                $iCol  = $typeColor[$lesson['file_type']] ?? '#6366f1';
                $hasN  = $lesson['notes_count'] > 0;
                $noteUrl = '?view=study_notes_manager&lesson_id='.$lesson['id'].'&course_id='.$ctoken.'&chapter_id='.$chapter['id'];
            ?>
            <div class="tsn-lesson-row" style="animation-delay:<?= $lIdx*.04 ?>s" data-lesson-title="<?= strtolower(htmlspecialchars($lesson['title'])) ?>">

                <!-- Type icon -->
                <div class="tsn-lesson-type-ico" style="background:<?= $iCol ?>18;color:<?= $iCol ?>">
                    <i class="bi <?= $icon ?>" style="pointer-events:none"></i>
                </div>

                <!-- Title -->
                <div style="flex:1;min-width:0">
                    <div class="tsn-lesson-title"><?= htmlspecialchars($lesson['title']) ?></div>
                    <span class="tsn-lesson-type-tag">
                        <i class="bi <?= $icon ?>" style="font-size:.55rem;pointer-events:none;color:<?= $iCol ?>"></i>
                        <?= htmlspecialchars(ucfirst($lesson['content_type'] ?? 'Video')) ?>
                    </span>
                </div>

                <!-- Notes badge -->
                <div class="tsn-notes-badge <?= $hasN?'tsn-badge-has':'tsn-badge-none' ?>">
                    <?php if ($hasN): ?>
                    <i class="bi bi-journal-bookmark-fill" style="font-size:.72rem"></i><?= $lesson['notes_count'] ?> note<?= $lesson['notes_count']!=1?'s':'' ?>
                    <?php else: ?>
                    <i class="bi bi-journal" style="font-size:.72rem"></i>No notes
                    <?php endif; ?>
                </div>

                <!-- Action -->
                <a href="<?= $noteUrl ?>" class="tsn-action-btn <?= $hasN?'tsn-action-edit':'tsn-action-add' ?>">
                    <?php if ($hasN): ?>
                    <i class="bi bi-pencil-fill" style="font-size:.72rem;pointer-events:none"></i>Edit Notes
                    <?php else: ?>
                    <i class="bi bi-plus-lg" style="font-size:.82rem;pointer-events:none"></i>Add Notes
                    <?php endif; ?>
                </a>

            </div>
            <?php endforeach; endif; ?>
        </div>
        <?php endforeach; endif; ?>
    </div>

</div>
<?php endforeach; ?>
</div>

<?php endif; ?>

</div><!-- /.container-fluid -->

<script>
/* ── Toggle course accordion ── */
window.tsnToggleCourse = function(card) {
    var chapters = card.querySelector('.tsn-chapters');
    var isOpen   = card.classList.contains('open');
    /* Close all */
    document.querySelectorAll('.tsn-course-card.open').forEach(function(c) {
        c.classList.remove('open');
        var ch = c.querySelector('.tsn-chapters');
        if (ch) ch.style.display = 'none';
    });
    /* Open this one if it was closed */
    if (!isOpen) {
        card.classList.add('open');
        if (chapters) chapters.style.display = 'block';
    }
};

/* ── Search / filter ── */
var _tsnTimer;
var tsnSearchEl = document.getElementById('tsnSearch');
if (tsnSearchEl) {
    tsnSearchEl.addEventListener('input', function() {
        clearTimeout(_tsnTimer);
        var q = this.value.toLowerCase().trim();
        _tsnTimer = setTimeout(function() { tsnFilter(q); }, 250);
    });
}

function tsnFilter(q) {
    var cards   = document.querySelectorAll('.tsn-course-card');
    var visible = 0;
    cards.forEach(function(card) {
        var courseTitle  = card.dataset.title || '';
        var lessonTitles = Array.from(card.querySelectorAll('[data-lesson-title]')).map(function(el){ return el.dataset.lessonTitle; });
        var match = !q || courseTitle.includes(q) || lessonTitles.some(function(t){ return t.includes(q); });
        card.style.display = match ? '' : 'none';
        if (match) visible++;

        /* Highlight matching lesson rows */
        card.querySelectorAll('[data-lesson-title]').forEach(function(row) {
            var lessMatch = !q || row.dataset.lessonTitle.includes(q) || courseTitle.includes(q);
            row.style.opacity = (!q || lessMatch) ? '1' : '.3';
        });

        /* Auto-expand matched course when searching */
        if (q && match && !card.classList.contains('open')) {
            card.classList.add('open');
            var ch = card.querySelector('.tsn-chapters');
            if (ch) ch.style.display = 'block';
        }
    });
    var countEl = document.getElementById('tsnCount');
    if (countEl) countEl.textContent = visible + ' course' + (visible !== 1 ? 's' : '') + (q ? ' matching' : '');
}

/* ── Animate KPI progress bars on load ── */
(function() {
    setTimeout(function() {
        document.querySelectorAll('.tsn-kpi-fill').forEach(function(el) {
            var w = el.style.getPropertyValue('--bw') || el.style.width || '0%';
            el.style.width = '0%';
            setTimeout(function() { el.style.width = w; }, 60);
        });
    }, 300);
})();
</script>
