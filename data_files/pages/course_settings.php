<?php
include('../config/db.php');

$course_id = intval($_GET['course_id'] ?? 0);

$q    = mysqli_query($db,"SELECT * FROM tbl_courses WHERE id='$course_id'");
$data = mysqli_fetch_assoc($q);

$_cats = $db->query("SELECT id, category_title, icon FROM tbl_course_categories WHERE status=1 ORDER BY sort_order,id")->fetch_all(MYSQLI_ASSOC);

/* Currently assigned categories */
$_assignedIds = [];
if ($course_id) {
    $__ar = $db->query("SELECT category_id FROM tbl_course_category_map WHERE course_id=$course_id");
    if ($__ar) $_assignedIds = array_column($__ar->fetch_all(MYSQLI_ASSOC), 'category_id');
    if (empty($_assignedIds) && !empty($data['category_id']))
        $_assignedIds = [(int)$data['category_id']];
}

/* Enrollment + chapter + lesson counts */
$_stats = $db->query("
    SELECT
        (SELECT COUNT(*) FROM tbl_course_chapters WHERE course_id=$course_id) AS chapters,
        (SELECT COUNT(*) FROM tbl_course_chapter_lessons l JOIN tbl_course_chapters ch ON ch.id=l.chapter_id WHERE ch.course_id=$course_id) AS lessons,
        (SELECT COUNT(*) FROM tbl_course_enrollments WHERE course_id=$course_id AND has_access=1) AS enrolled
")->fetch_assoc();

$_catColors = ['#6366f1','#10b981','#f59e0b','#3b82f6','#8b5cf6','#ec4899','#14b8a6','#f97316','#84cc16','#06b6d4','#a855f7','#ef4444'];
$_thumbUrl  = !empty($data['thumbnail']) ? '../uploads/'.basename($data['thumbnail']) : '../assets/img/logo.svg';
$_statusMap = ['active'=>['Published','#16a34a','#dcfce7'],'is_draft'=>['Draft','#92400e','#fef3c7'],'inactive'=>['Inactive','#991b1b','#fee2e2']];
$_st = $_statusMap[$data['status']??'is_draft'] ?? ['Unknown','#475569','#f1f5f9'];
?>
<style>
/* ══ Course Settings — Hero Design ══════════════════════════════ */
@keyframes cset-fade{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
@keyframes cset-skel{0%{background-position:200% 0}100%{background-position:-200% 0}}

/* ── Hero ── */
.cset-hero{position:relative;overflow:hidden;border-radius:20px;background:linear-gradient(135deg,#05050f 0%,#111827 40%,#1e1b4b 100%);padding:0;margin-bottom:1.5rem;animation:cset-fade .4s ease both}
.cset-hero-bg{position:absolute;inset:0;background-size:cover;background-position:center;filter:blur(28px) brightness(.18);transform:scale(1.1);transition:background-image .4s}
.cset-hero-inner{position:relative;z-index:2;display:flex;align-items:flex-end;gap:1.5rem;padding:1.75rem 1.75rem 1.5rem;flex-wrap:wrap}
.cset-hero-thumb-wrap{position:relative;flex-shrink:0}
.cset-hero-thumb{width:110px;height:80px;border-radius:14px;object-fit:cover;border:3px solid rgba(255,255,255,.15);box-shadow:0 8px 32px rgba(0,0,0,.5);transition:all .3s;cursor:pointer}
.cset-hero-thumb-overlay{position:absolute;inset:0;border-radius:12px;background:rgba(0,0,0,.55);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .2s;color:#fff;font-size:.78rem;font-weight:700;gap:.3rem;cursor:pointer}
.cset-hero-thumb-wrap:hover .cset-hero-thumb-overlay{opacity:1}
.cset-hero-title{font-size:1.35rem;font-weight:900;color:#fff;letter-spacing:-.02em;line-height:1.2;text-shadow:0 2px 12px rgba(0,0,0,.5)}
.cset-hero-status{display:inline-flex;align-items:center;gap:.35rem;font-size:.72rem;font-weight:700;padding:.22rem .75rem;border-radius:20px;margin-top:.4rem}
.cset-hero-pills{display:flex;flex-wrap:wrap;gap:.45rem;margin-top:.65rem}
.cset-hero-pill{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);color:#fff;font-size:.71rem;font-weight:600;padding:.22rem .7rem;border-radius:20px;display:inline-flex;align-items:center;gap:.3rem}
.cset-hero-actions{margin-left:auto;align-self:flex-start;padding-top:.15rem;display:flex;gap:.6rem;flex-wrap:wrap}
.cset-hbtn{padding:.5rem 1.1rem;border-radius:11px;font-size:.8rem;font-weight:700;cursor:pointer;border:none;display:flex;align-items:center;gap:.4rem;transition:all .2s;white-space:nowrap}
.cset-hbtn-save{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;box-shadow:0 4px 16px rgba(99,102,241,.4)}
.cset-hbtn-save:hover{transform:translateY(-2px);box-shadow:0 8px 26px rgba(99,102,241,.55)}
.cset-hbtn-del{background:rgba(239,68,68,.15);color:#fca5a5;border:1px solid rgba(239,68,68,.25)}
.cset-hbtn-del:hover{background:#ef4444;color:#fff}

/* ── Section cards ── */
.cset-section{background:#fff;border-radius:18px;padding:1.4rem 1.5rem;margin-bottom:1.1rem;box-shadow:0 2px 14px rgba(0,0,0,.055);animation:cset-fade .4s ease both;transition:box-shadow .2s}
.cset-section:hover{box-shadow:0 4px 24px rgba(0,0,0,.09)}
.cset-section-hdr{display:flex;align-items:center;gap:.85rem;margin-bottom:1.25rem;padding-bottom:.85rem;border-bottom:1px solid #f1f5f9}
.cset-section-ico{width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.05rem;flex-shrink:0}
.cset-section-title{font-weight:800;font-size:.9rem;color:#1e1b4b}
.cset-section-sub{font-size:.73rem;color:#94a3b8;margin-top:.1rem}
.cset-section:nth-child(1){animation-delay:.05s}
.cset-section:nth-child(2){animation-delay:.09s}
.cset-section:nth-child(3){animation-delay:.13s}
.cset-section:nth-child(4){animation-delay:.17s}
.cset-section:nth-child(5){animation-delay:.21s}

/* ── Form inputs ── */
.cset-label{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#475569;margin-bottom:.35rem;display:flex;align-items:center;gap:.4rem}
.cset-input{width:100%;border:1.5px solid #e0e7ff;border-radius:11px;padding:.65rem .9rem;font-size:.88rem;color:#1e1b4b;background:#fff;transition:all .2s;font-family:inherit}
.cset-input:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1);background:#fff}
.cset-input::placeholder{color:#c4cad4}
textarea.cset-input{resize:vertical;min-height:110px;line-height:1.55}

/* ── Thumbnail upload ── */
.cset-thumb-upload{border:2px dashed #e0e7ff;border-radius:14px;padding:1.25rem;text-align:center;cursor:pointer;transition:all .2s;background:#f8f7ff;position:relative}
.cset-thumb-upload:hover{border-color:#a5b4fc;background:#f5f3ff}
.cset-thumb-upload input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.cset-thumb-preview{display:flex;align-items:center;gap:1rem}
.cset-thumb-preview img{width:80px;height:56px;border-radius:10px;object-fit:cover;border:2px solid #e0e7ff;flex-shrink:0}

/* ── Category grid ── */
.cset-cat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:.45rem;max-height:220px;overflow-y:auto;padding:.05rem}
.cset-cat-grid::-webkit-scrollbar{width:4px}
.cset-cat-grid::-webkit-scrollbar-thumb{background:rgba(99,102,241,.2);border-radius:4px}
.cset-cat-tile{border:2px solid #e0e7ff;border-radius:11px;padding:.55rem .4rem;cursor:pointer;text-align:center;transition:all .18s;user-select:none;background:#fff;position:relative}
.cset-cat-tile:hover{border-color:#a5b4fc;box-shadow:0 3px 10px rgba(99,102,241,.12);transform:translateY(-2px)}
.cset-cat-tile.selected{border-color:#6366f1;background:linear-gradient(135deg,#ede9fe,#eff6ff)}
.cset-cat-tile.selected .cset-tile-name{color:#4f46e5}
.cset-tile-ico{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.95rem;margin:0 auto .35rem;transition:transform .18s;pointer-events:none}
.cset-cat-tile:hover .cset-tile-ico,.cset-cat-tile.selected .cset-tile-ico{transform:scale(1.1) rotate(-6deg)}
.cset-tile-name{font-size:.65rem;font-weight:700;color:#334155;line-height:1.25;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;pointer-events:none}
.cset-cat-tile.selected::after{content:'\F26E';font-family:'bootstrap-icons';position:absolute;top:3px;right:5px;font-size:.62rem;color:#6366f1;pointer-events:none}
.cset-cat-search{width:100%;border:1.5px solid #e0e7ff;border-radius:10px;padding:.42rem .8rem .42rem 2rem;font-size:.82rem;background:#f8f7ff;transition:all .2s;margin-bottom:.6rem}
.cset-cat-search:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1);background:#fff}
.cset-cat-pill{display:inline-flex;align-items:center;gap:.3rem;background:linear-gradient(135deg,#ede9fe,#e0e7ff);color:#4f46e5;font-size:.71rem;font-weight:700;padding:.22rem .7rem;border-radius:20px}

/* ── Price section ── */
.cset-price-toggle{display:flex;gap:.6rem;margin-bottom:.9rem}
.cset-price-opt{flex:1;border:2px solid #e0e7ff;border-radius:12px;padding:.75rem .9rem;cursor:pointer;transition:all .18s;text-align:center;user-select:none}
.cset-price-opt:hover{border-color:#a5b4fc}
.cset-price-opt.selected.free{border-color:#10b981;background:linear-gradient(135deg,#f0fdf4,#dcfce7)}
.cset-price-opt.selected.paid{border-color:#f59e0b;background:linear-gradient(135deg,#fffbeb,#fef3c7)}
.cset-price-opt-icon{font-size:1.2rem;margin-bottom:.2rem}
.cset-price-opt-label{font-size:.75rem;font-weight:700;color:#334155}

/* ── Toggle option cards ── */
.cset-toggle-card{display:flex;align-items:center;justify-content:space-between;border:1.5px solid #e0e7ff;border-radius:13px;padding:.9rem 1.1rem;cursor:pointer;transition:all .2s;background:#fff;user-select:none}
.cset-toggle-card:hover{border-color:#a5b4fc;background:#f8f7ff}
.cset-toggle-card.on{border-color:#6366f1;background:linear-gradient(135deg,#f5f3ff,#eff6ff)}
.cset-toggle-left{display:flex;align-items:center;gap:.85rem}
.cset-toggle-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.cset-toggle-title{font-weight:700;font-size:.85rem;color:#1e1b4b}
.cset-toggle-desc{font-size:.72rem;color:#94a3b8;margin-top:.08rem}
.cset-toggle-switch{width:44px;height:24px;border-radius:99px;border:none;cursor:pointer;transition:all .25s;position:relative;flex-shrink:0}
.cset-toggle-switch::after{content:'';position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;transition:transform .25s;box-shadow:0 1px 4px rgba(0,0,0,.2)}
.cset-toggle-switch.on{background:linear-gradient(135deg,#6366f1,#8b5cf6)}
.cset-toggle-switch.on::after{transform:translateX(20px)}
.cset-toggle-switch.off{background:#e2e8f0}

/* ── Danger zone ── */
.cset-danger{border:2px solid #fee2e2;border-radius:18px;background:#fff5f5;padding:1.3rem 1.5rem;margin-bottom:1.5rem}
.cset-danger-title{font-weight:800;font-size:.88rem;color:#dc2626;display:flex;align-items:center;gap:.5rem;margin-bottom:.35rem}
.cset-danger-del-btn{background:linear-gradient(135deg,#dc2626,#9f1239);color:#fff;border:none;border-radius:11px;padding:.55rem 1.2rem;font-size:.82rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.4rem;transition:all .2s;box-shadow:0 3px 12px rgba(220,38,38,.3)}
.cset-danger-del-btn:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(220,38,38,.45)}

/* ── Bottom save bar (non-sticky — avoids overflow:hidden parent issues) ── */
.cset-save-bar{background:#fff;border:1.5px solid #e0e7ff;border-radius:16px;padding:.85rem 1.5rem;display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;box-shadow:0 2px 14px rgba(0,0,0,.055)}
.cset-save-btn{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:12px;padding:.6rem 1.6rem;font-size:.86rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.45rem;transition:all .2s;box-shadow:0 4px 16px rgba(99,102,241,.4)}
.cset-save-btn:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(99,102,241,.55)}
.cset-unsaved-dot{width:8px;height:8px;border-radius:50%;background:#f59e0b;display:none;margin-right:.25rem}
</style>

<!-- ═══ HERO ══════════════════════════════════════════════════════ -->
<div class="container-fluid px-3 px-md-4 mt-3">
<div class="cset-hero">
    <div class="cset-hero-bg" id="csetHeroBg" style="background-image:url('<?= htmlspecialchars($_thumbUrl) ?>')"></div>
    <div class="cset-hero-inner">
        <!-- Thumbnail preview (click to upload) -->
        <div class="cset-hero-thumb-wrap" id="csetHeroThumbWrap" title="Click to change thumbnail">
            <img class="cset-hero-thumb" id="csetHeroThumb" src="<?= htmlspecialchars($_thumbUrl) ?>" alt="thumbnail">
            <div class="cset-hero-thumb-overlay" style="pointer-events:none"><i class="bi bi-camera-fill"></i>Change</div>
        </div>
        <!-- Meta -->
        <div style="min-width:0">
            <div class="cset-hero-title" id="csetHeroTitle"><?= htmlspecialchars($data['title'] ?? 'Untitled Course') ?></div>
            <div>
                <span class="cset-hero-status" style="background:<?= $_st[2] ?>;color:<?= $_st[1] ?>">
                    <span style="width:6px;height:6px;border-radius:50%;background:<?= $_st[1] ?>;display:inline-block"></span>
                    <?= $_st[0] ?>
                </span>
            </div>
            <div class="cset-hero-pills">
                <span class="cset-hero-pill"><i class="bi bi-collection"></i><?= (int)($_stats['chapters']??0) ?> Chapters</span>
                <span class="cset-hero-pill"><i class="bi bi-play-circle"></i><?= (int)($_stats['lessons']??0) ?> Lessons</span>
                <span class="cset-hero-pill"><i class="bi bi-people"></i><?= (int)($_stats['enrolled']??0) ?> Enrolled</span>
                <span class="cset-hero-pill"><i class="bi bi-tag"></i><?= count($_assignedIds) ?> Categor<?= count($_assignedIds)==1?'y':'ies' ?></span>
            </div>
        </div>
        <!-- Actions -->
        <div class="cset-hero-actions d-none d-md-flex">
            <button class="cset-hbtn cset-hbtn-save" id="heroSaveBtn">
                <i class="bi bi-check-lg"></i> Save Changes
            </button>
            <button class="cset-hbtn cset-hbtn-del" id="heroDeleteBtn">
                <i class="bi bi-trash3"></i> Delete
            </button>
        </div>
    </div>
</div>

<!-- Hidden inputs -->
<input type="hidden" id="course_id"   value="<?= $course_id ?>">
<input type="hidden" id="library_id"  value="<?= htmlspecialchars($data['library_id']??'') ?>">
<input type="hidden" id="library_key" value="<?= htmlspecialchars($data['library_key']??'') ?>">
<input type="hidden" id="old_course_name" value="<?= htmlspecialchars($data['title']??'') ?>">

<div class="row g-3 pb-4" style="position:relative">
<!-- ══ LEFT COLUMN ══ -->
<div class="col-12 col-xl-8">

    <!-- ① Basic Info -->
    <div class="cset-section">
        <div class="cset-section-hdr">
            <div class="cset-section-ico" style="background:#ede9fe;color:#6366f1"><i class="bi bi-info-circle-fill"></i></div>
            <div>
                <div class="cset-section-title">Basic Information</div>
                <div class="cset-section-sub">Course title and cover thumbnail</div>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-12">
                <label class="cset-label" for="course_name"><i class="bi bi-fonts"></i> Course Title</label>
                <input class="cset-input" id="course_name" value="<?= htmlspecialchars($data['title']??'') ?>"
                       placeholder="e.g. Modern PHP Development Masterclass" required>
            </div>
            <div class="col-12">
                <label class="cset-label"><i class="bi bi-image"></i> Course Thumbnail</label>
                <div class="cset-thumb-upload" id="csetThumbDrop">
                    <input type="file" id="course_thumbnail" accept="image/*">
                    <div class="cset-thumb-preview" id="csetThumbPreview">
                        <img id="csetThumbImg" src="<?= htmlspecialchars($_thumbUrl) ?>" alt="thumbnail">
                        <div>
                            <div class="fw-semibold" style="font-size:.84rem;color:#334155">Current Thumbnail</div>
                            <div class="text-muted" style="font-size:.74rem;margin-top:.2rem">Click or drag to replace</div>
                            <div style="font-size:.7rem;color:#a5b4fc;margin-top:.15rem">JPG, PNG, WebP — max 5 MB</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ② Description -->
    <div class="cset-section">
        <div class="cset-section-hdr">
            <div class="cset-section-ico" style="background:#dbeafe;color:#2563eb"><i class="bi bi-file-text-fill"></i></div>
            <div>
                <div class="cset-section-title">Course Description</div>
                <div class="cset-section-sub">What will students learn? Keep it compelling</div>
            </div>
        </div>
        <label class="cset-label" for="course_description"><i class="bi bi-pencil-fill"></i> Description</label>
        <textarea class="cset-input" id="course_description" rows="6"
                  placeholder="Describe what your course covers, who it's for, and what students will achieve…"><?= htmlspecialchars($data['description']??'', ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <!-- ③ Pricing -->
    <div class="cset-section">
        <div class="cset-section-hdr">
            <div class="cset-section-ico" style="background:#fef3c7;color:#d97706"><i class="bi bi-tag-fill"></i></div>
            <div>
                <div class="cset-section-title">Pricing</div>
                <div class="cset-section-sub">Set the price or offer this course for free</div>
            </div>
        </div>
        <!-- Free / Paid toggle -->
        <?php $isFree = (float)($data['price']??0) == 0; ?>
        <div class="cset-price-toggle" id="csetPriceToggle">
            <div class="cset-price-opt free <?= $isFree?'selected':'' ?>" data-type="free">
                <div class="cset-price-opt-icon">🆓</div>
                <div class="cset-price-opt-label">Free</div>
                <div style="font-size:.68rem;color:#64748b;margin-top:.1rem">Open to all students</div>
            </div>
            <div class="cset-price-opt paid <?= !$isFree?'selected':'' ?>" data-type="paid">
                <div class="cset-price-opt-icon">💰</div>
                <div class="cset-price-opt-label">Paid</div>
                <div style="font-size:.68rem;color:#64748b;margin-top:.1rem">Requires purchase</div>
            </div>
        </div>
        <div class="row g-3" id="csetPriceFields" style="<?= $isFree?'display:none':'' ?>">
            <div class="col-md-6">
                <label class="cset-label" for="course_price"><i class="bi bi-currency-exchange"></i> Price (TZS)</label>
                <input class="cset-input" id="course_price" type="number" min="0"
                       value="<?= htmlspecialchars($data['price']??'0') ?>"
                       placeholder="e.g. 25000">
            </div>
            <div class="col-md-6">
                <label class="cset-label" for="course_discount"><i class="bi bi-percent"></i> Discount (%)</label>
                <input class="cset-input" id="course_discount" type="number" min="0" max="100"
                       value="<?= htmlspecialchars($data['discount']??'') ?>"
                       placeholder="e.g. 20">
            </div>
        </div>
        <!-- Hidden price field for when free is selected -->
        <input type="hidden" id="course_price_free" value="0">
    </div>

    <!-- ④ Course Options -->
    <div class="cset-section">
        <div class="cset-section-hdr">
            <div class="cset-section-ico" style="background:#d1fae5;color:#059669"><i class="bi bi-gear-fill"></i></div>
            <div>
                <div class="cset-section-title">Course Options</div>
                <div class="cset-section-sub">Features and capabilities for enrolled students</div>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="cset-toggle-card <?= $data['certificate']==1?'on':'' ?>" id="tcCert" data-toggle="cert">
                    <div class="cset-toggle-left">
                        <div class="cset-toggle-icon" style="background:<?= $data['certificate']==1?'linear-gradient(135deg,#6366f1,#8b5cf6)':'#f1f5f9' ?>;color:<?= $data['certificate']==1?'#fff':'#94a3b8' ?>"><i class="bi bi-award-fill"></i></div>
                        <div>
                            <div class="cset-toggle-title">Certificate</div>
                            <div class="cset-toggle-desc">Issue completion certificate</div>
                        </div>
                    </div>
                    <div class="cset-toggle-switch <?= $data['certificate']==1?'on':'off' ?>" id="swCert"></div>
                </div>
                <input type="hidden" id="isCertificateOffered" value="<?= $data['certificate']==1?'1':'0' ?>">
            </div>
            <div class="col-md-6">
                <div class="cset-toggle-card <?= $data['qna']==1?'on':'' ?>" id="tcQna" data-toggle="qna">
                    <div class="cset-toggle-left">
                        <div class="cset-toggle-icon" style="background:<?= $data['qna']==1?'linear-gradient(135deg,#6366f1,#8b5cf6)':'#f1f5f9' ?>;color:<?= $data['qna']==1?'#fff':'#94a3b8' ?>"><i class="bi bi-chat-dots-fill"></i></div>
                        <div>
                            <div class="cset-toggle-title">Q&amp;A</div>
                            <div class="cset-toggle-desc">Allow student questions</div>
                        </div>
                    </div>
                    <div class="cset-toggle-switch <?= $data['qna']==1?'on':'off' ?>" id="swQna"></div>
                </div>
                <input type="hidden" id="isQandAEnabled" value="<?= $data['qna']==1?'1':'0' ?>">
            </div>
        </div>
    </div>

</div><!-- /.col left -->

<!-- ══ RIGHT COLUMN ══ -->
<div class="col-12 col-xl-4">

    <!-- ⑤ Categories -->
    <div class="cset-section" style="position:sticky;top:16px">
        <div class="cset-section-hdr">
            <div class="cset-section-ico" style="background:#ede9fe;color:#6366f1"><i class="bi bi-grid-3x3-gap-fill"></i></div>
            <div>
                <div class="cset-section-title">Categories</div>
                <div class="cset-section-sub">Boosts student recommendations</div>
            </div>
        </div>
        <!-- Filter -->
        <div class="position-relative mb-2">
            <i class="bi bi-search position-absolute" style="left:.65rem;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:.78rem;pointer-events:none"></i>
            <input class="cset-cat-search" id="csCatSearch" style="padding-left:2rem"
                   placeholder="Filter categories…" autocomplete="off">
        </div>
        <!-- Grid -->
        <div class="cset-cat-grid" id="csCatGrid">
            <?php foreach ($_cats as $ci => $cat):
                $cc  = $_catColors[$ci % count($_catColors)];
                $sel = in_array($cat['id'], $_assignedIds);
            ?>
            <div class="cset-cat-tile <?= $sel?'selected':'' ?>"
                 data-id="<?= $cat['id'] ?>"
                 data-name="<?= htmlspecialchars($cat['category_title']) ?>"
                 data-search="<?= strtolower(htmlspecialchars($cat['category_title'])) ?>">
                <div class="cset-tile-ico" style="background:<?= $cc ?>18;color:<?= $cc ?>">
                    <i class="bi <?= htmlspecialchars($cat['icon']??'bi-grid') ?>"></i>
                </div>
                <div class="cset-tile-name"><?= htmlspecialchars($cat['category_title']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <!-- Selected pills -->
        <div id="csCatPills" class="d-flex flex-wrap gap-1 mt-2" style="min-height:1.4rem">
            <?php foreach ($_assignedIds as $aid):
                $found = array_filter($_cats, fn($c)=>$c['id']==$aid);
                $cat   = reset($found); if (!$cat) continue;
            ?>
            <span class="cset-cat-pill"><?= htmlspecialchars($cat['category_title']) ?></span>
            <?php endforeach; ?>
        </div>
    </div>

</div><!-- /.col right -->
</div><!-- /.row -->

<!-- ══ DANGER ZONE ══ -->
<div class="cset-danger">
    <div class="cset-danger-title"><i class="bi bi-exclamation-triangle-fill"></i> Danger Zone</div>
    <p class="text-muted small mb-3">Deleting the course will soft-delete it and remove it from the platform. All enrolled students will lose access. This cannot be easily undone.</p>
    <button class="cset-danger-del-btn" id="deleteCourseBtn"><i class="bi bi-trash3-fill"></i> Delete This Course</button>
</div>

<!-- ══ STICKY SAVE BAR ══ -->
<div class="cset-save-bar">
    <div style="display:flex;align-items:center;gap:.5rem">
        <span class="cset-unsaved-dot" id="csetUnsavedDot"></span>
        <span class="small text-muted" id="csetSaveStatus">All changes saved</span>
    </div>
    <button class="cset-save-btn" id="saveCourseSettingsBtn">
        <i class="bi bi-check-lg"></i> Save Changes
    </button>
</div>

</div><!-- /.container-fluid -->

<script>
/* ─────────────────────────────────────────────────────────────
   Course Settings JS
   All wiring is done directly — no DOMContentLoaded because
   in the SPA that event already fired before the fragment loads.
───────────────────────────────────────────────────────────── */
(function () {

/* ── Shared state ── */
window._csCatSelected = new Set(<?= json_encode(array_map('intval', $_assignedIds)) ?>);
var _dirty = false;

/* ── Helpers ── */
function escH(s) {
    return String(s||'').replace(/[&<>"']/g, function(c){
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
    });
}
function markDirty() {
    _dirty = true;
    var dot = document.getElementById('csetUnsavedDot');
    var st  = document.getElementById('csetSaveStatus');
    if (dot) dot.style.display = 'inline-block';
    if (st)  st.textContent    = 'Unsaved changes';
}
function markSaved() {
    _dirty = false;
    var dot = document.getElementById('csetUnsavedDot');
    var st  = document.getElementById('csetSaveStatus');
    if (dot) dot.style.display = 'none';
    if (st)  st.textContent    = 'All changes saved';
}

/* ── Category tiles — delegated ── */
var grid = document.getElementById('csCatGrid');
if (grid) {
    grid.addEventListener('click', function(e) {
        var tile = e.target.closest('.cset-cat-tile');
        if (!tile) return;
        var id = parseInt(tile.dataset.id);
        if (_csCatSelected.has(id)) {
            _csCatSelected.delete(id);
            tile.classList.remove('selected');
        } else {
            _csCatSelected.add(id);
            tile.classList.add('selected');
        }
        renderCatPills();
        markDirty();
    });
}

/* Category search filter */
var catSearch = document.getElementById('csCatSearch');
if (catSearch) {
    catSearch.addEventListener('input', function() {
        var lq = this.value.toLowerCase();
        document.querySelectorAll('.cset-cat-tile').forEach(function(el) {
            el.style.display = (el.dataset.search||'').includes(lq) ? '' : 'none';
        });
    });
}

function renderCatPills() {
    var wrap  = document.getElementById('csCatPills');
    if (!wrap) return;
    var tiles = document.querySelectorAll('.cset-cat-tile');
    if (!_csCatSelected.size) { wrap.innerHTML = ''; return; }
    wrap.innerHTML = Array.from(_csCatSelected).map(function(id) {
        var tile = Array.from(tiles).find(function(t){ return parseInt(t.dataset.id) === id; });
        var name = tile ? tile.dataset.name : id;
        return '<span class="cset-cat-pill">'
             + '<i class="bi bi-check-circle-fill" style="font-size:.62rem;pointer-events:none"></i>'
             + escH(name) + '</span>';
    }).join('');
}

/* ── Thumbnail preview ── */
function previewThumb(file) {
    if (!file) return;
    var fr = new FileReader();
    fr.onload = function(e) {
        var src = e.target.result;
        var img    = document.getElementById('csetThumbImg');
        var hImg   = document.getElementById('csetHeroThumb');
        var hBg    = document.getElementById('csetHeroBg');
        if (img)  img.src = src;
        if (hImg) hImg.src = src;
        if (hBg)  hBg.style.backgroundImage = "url('" + src + "')";
    };
    fr.readAsDataURL(file);
    markDirty();
}

/* File input — change */
var fileInput = document.getElementById('course_thumbnail');
if (fileInput) {
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) previewThumb(this.files[0]);
    });
}

/* Hero thumbnail wrapper — click opens file picker */
var heroWrap = document.getElementById('csetHeroThumbWrap');
if (heroWrap) {
    heroWrap.addEventListener('click', function() {
        if (fileInput) fileInput.click();
    });
}

/* Upload drop zone — click also opens file picker */
var dropZone = document.getElementById('csetThumbDrop');
if (dropZone) {
    dropZone.addEventListener('click', function(e) {
        /* Don't double-trigger if the click came from the file input itself */
        if (e.target === fileInput) return;
        if (fileInput) fileInput.click();
    });
    /* Drag-and-drop */
    dropZone.addEventListener('dragover',  function(e){ e.preventDefault(); dropZone.style.borderColor='#6366f1'; });
    dropZone.addEventListener('dragleave', function(e){ dropZone.style.borderColor=''; });
    dropZone.addEventListener('drop',      function(e){
        e.preventDefault();
        dropZone.style.borderColor = '';
        var f = e.dataTransfer.files[0];
        if (f && f.type.startsWith('image/')) {
            if (fileInput) {
                /* Assign to file input via DataTransfer */
                var dt = new DataTransfer(); dt.items.add(f);
                fileInput.files = dt.files;
            }
            previewThumb(f);
        }
    });
}

/* ── Price toggle — delegated ── */
var priceToggle = document.getElementById('csetPriceToggle');
if (priceToggle) {
    priceToggle.addEventListener('click', function(e) {
        var opt = e.target.closest('.cset-price-opt');
        if (!opt) return;
        var type = opt.dataset.type;
        document.querySelectorAll('.cset-price-opt').forEach(function(o){ o.classList.remove('selected'); });
        opt.classList.add('selected');
        var fields = document.getElementById('csetPriceFields');
        if (fields) fields.style.display = type === 'free' ? 'none' : '';
        if (type === 'free') {
            var pr = document.getElementById('course_price');
            if (pr) pr.value = '0';
        }
        markDirty();
    });
}

/* ── Toggle option cards (cert / Q&A) — delegated ── */
var optCards = document.querySelectorAll('.cset-toggle-card[data-toggle]');
optCards.forEach(function(card) {
    card.addEventListener('click', function() {
        var type  = card.dataset.toggle;
        var isOn  = card.classList.contains('on');
        var sw    = card.querySelector('.cset-toggle-switch');
        var ico   = card.querySelector('.cset-toggle-icon');
        var hidId = type === 'cert' ? 'isCertificateOffered' : 'isQandAEnabled';
        if (isOn) {
            card.classList.remove('on');
            if (sw)  { sw.classList.remove('on');  sw.classList.add('off'); }
            if (ico) { ico.style.background = '#f1f5f9'; ico.style.color = '#94a3b8'; }
            document.getElementById(hidId).value = '0';
        } else {
            card.classList.add('on');
            if (sw)  { sw.classList.remove('off'); sw.classList.add('on'); }
            if (ico) { ico.style.background = 'linear-gradient(135deg,#6366f1,#8b5cf6)'; ico.style.color = '#fff'; }
            document.getElementById(hidId).value = '1';
        }
        markDirty();
    });
});

/* ── Inputs — mark dirty on change ── */
['course_name','course_price','course_discount','course_description'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('input', markDirty);
});

/* Sync title into hero */
var nameInput = document.getElementById('course_name');
if (nameInput) {
    nameInput.addEventListener('input', function() {
        var t = document.getElementById('csetHeroTitle');
        if (t) t.textContent = this.value || 'Untitled Course';
    });
}

/* ── Save / Delete are owned by course_contents_management.php.
   The hero buttons delegate to the main buttons so CCM's handler fires. ── */
var heroSave = document.getElementById('heroSaveBtn');
if (heroSave) {
    heroSave.addEventListener('click', function() {
        var b = document.getElementById('saveCourseSettingsBtn');
        if (b) b.click();
    });
}
var heroDelete = document.getElementById('heroDeleteBtn');
if (heroDelete) {
    heroDelete.addEventListener('click', function() {
        var b = document.getElementById('deleteCourseBtn');
        if (b) b.click();
    });
}

}());
</script>
