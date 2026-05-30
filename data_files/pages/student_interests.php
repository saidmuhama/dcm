<?php
$me   = $_SESSION['usr_code'] ?? '';
$role = (int)($_SESSION['user_role'] ?? 0);
if (!$me || $role != 1) { echo "<script>location.replace('?view=3002')</script>"; return; }

$cats    = $db->query("SELECT id, category_title, icon, category_code FROM tbl_course_categories WHERE status=1 ORDER BY sort_order,id")->fetch_all(MYSQLI_ASSOC);

/* Fetch level priorities if the student already has a level set */
$_levelPriorities = [];
if (!empty($profile['education_level'])) {
    $__lv = $db->real_escape_string($profile['education_level']);
    $__lpr = $db->query("SELECT category_code, priority FROM tbl_level_category_map WHERE education_level='$__lv'");
    if ($__lpr) foreach ($__lpr->fetch_all(MYSQLI_ASSOC) as $__r) $_levelPriorities[$__r['category_code']] = $__r['priority'];
}
$myInts  = array_column($db->query("SELECT category_id FROM tbl_student_interests WHERE student_id='".$db->real_escape_string($me)."'")->fetch_all(MYSQLI_ASSOC),'category_id');
$profile = $db->query("SELECT * FROM tbl_student_profiles WHERE student_id='".$db->real_escape_string($me)."' LIMIT 1")->fetch_assoc();
$combos  = $db->query("SELECT * FROM tbl_combinations WHERE status='active' ORDER BY stream_type,combination_code")->fetch_all(MYSQLI_ASSOC);
$comboByStream = [];
foreach ($combos as $c) $comboByStream[$c['stream_type']][] = $c;
$studentName = $_SESSION['name'] ?? 'Student';
$colors = ['#6366f1','#10b981','#f59e0b','#3b82f6','#8b5cf6','#ec4899','#14b8a6','#f97316','#84cc16','#06b6d4','#a855f7','#ef4444'];
?>
<style>
/* ═══════════════════════════════════════════════════════════
   Student Interests — Hero Redesign
═══════════════════════════════════════════════════════════ */
@keyframes si-fade{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}
@keyframes si-pop{0%{transform:scale(.8);opacity:0}60%{transform:scale(1.06)}100%{transform:scale(1);opacity:1}}
@keyframes si-orb1{from{transform:translate(0,0) scale(1)}to{transform:translate(-20px,18px) scale(1.2)}}
@keyframes si-orb2{from{transform:translate(0,0) scale(1)}to{transform:translate(16px,-22px) scale(1.15)}}
@keyframes si-orb3{from{transform:translate(0,0) scale(1)}to{transform:translate(-10px,-12px) scale(.9)}}
@keyframes si-card-in{from{opacity:0;transform:translateY(18px) scale(.95)}to{opacity:1;transform:none}}
@keyframes si-check-pop{0%{transform:scale(0)}60%{transform:scale(1.3)}100%{transform:scale(1)}}
@keyframes si-step-slide{from{opacity:0;transform:translateX(18px)}to{opacity:1;transform:none}}
@keyframes si-pulse{0%,100%{box-shadow:0 0 0 0 rgba(99,102,241,.4)}70%{box-shadow:0 0 0 8px transparent}}

/* ── Layout ── */
.si-page{max-width:900px;margin:0 auto;padding:0 1rem 4rem;animation:si-fade .4s ease both}

/* ── Hero banner ── */
.si-hero{position:relative;border-radius:24px;overflow:hidden;background:linear-gradient(135deg,#050510 0%,#0f0c29 35%,#1e1040 65%,#2d1b69 100%);padding:2.25rem 2rem 2rem;margin-bottom:1.75rem;color:#fff}
.si-orb{position:absolute;border-radius:50%;filter:blur(55px);pointer-events:none}
.si-orb-1{width:240px;height:240px;background:rgba(99,102,241,.3);top:-70px;right:-30px;animation:si-orb1 8s ease-in-out infinite alternate}
.si-orb-2{width:160px;height:160px;background:rgba(139,92,246,.25);bottom:-50px;right:180px;animation:si-orb2 10s ease-in-out infinite alternate}
.si-orb-3{width:120px;height:120px;background:rgba(236,72,153,.2);top:30px;left:42%;animation:si-orb3 7s ease-in-out infinite alternate}
.si-hero-inner{position:relative;z-index:2;display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap}
.si-hero-icon{width:68px;height:68px;border-radius:20px;background:rgba(255,255,255,.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:1.9rem;flex-shrink:0;box-shadow:0 8px 32px rgba(99,102,241,.4);animation:si-pop .7s cubic-bezier(.34,1.56,.64,1) both}
.si-hero-title{font-size:1.4rem;font-weight:900;letter-spacing:-.02em;line-height:1.1}
.si-hero-title span{background:linear-gradient(90deg,#a5b4fc,#f9a8d4,#6ee7b7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.si-hero-sub{font-size:.83rem;opacity:.55;margin-top:.3rem}
.si-hero-pills{display:flex;flex-wrap:wrap;gap:.45rem;margin-top:.85rem}
.si-hero-pill{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);color:#fff;font-size:.7rem;font-weight:700;padding:.22rem .75rem;border-radius:20px;display:inline-flex;align-items:center;gap:.3rem}
.si-hero-pill-green{background:rgba(16,185,129,.2);border-color:rgba(16,185,129,.3);color:#6ee7b7}

/* ── Progress stepper ── */
.si-stepper{display:flex;align-items:center;gap:0;margin-bottom:1.75rem;overflow-x:auto;padding:.25rem .1rem;scrollbar-width:none}
.si-stepper::-webkit-scrollbar{display:none}
.si-step-item{display:flex;align-items:center;gap:0;flex-shrink:0}
.si-step-btn{display:flex;align-items:center;gap:.5rem;padding:.5rem 1rem;border-radius:12px;font-size:.78rem;font-weight:700;cursor:pointer;border:2px solid #e0e7ff;color:#94a3b8;background:#fff;transition:all .22s;white-space:nowrap}
.si-step-btn:hover{border-color:#a5b4fc;color:#6366f1}
.si-step-btn.active{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border-color:transparent;box-shadow:0 4px 16px rgba(99,102,241,.4);animation:si-pulse 2.5s infinite}
.si-step-btn.done{background:#f0fdf4;border-color:#6ee7b7;color:#065f46}
.si-step-num{width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:800;background:rgba(0,0,0,.08);flex-shrink:0}
.si-step-btn.active .si-step-num{background:rgba(255,255,255,.25);color:#fff}
.si-step-btn.done .si-step-num{background:#10b981;color:#fff}
.si-step-connector{width:32px;height:2px;background:#e0e7ff;margin:0 .2rem;flex-shrink:0;transition:background .3s}
.si-step-item.done + .si-step-item .si-step-connector{background:#6ee7b7}

/* ── Step panels ── */
.si-panel{display:none}
.si-panel.active{display:block;animation:si-step-slide .28s ease both}

/* ── Section card ── */
.si-card{background:#fff;border-radius:18px;padding:1.5rem;margin-bottom:0;box-shadow:0 2px 16px rgba(0,0,0,.06);border:1.5px solid transparent;transition:box-shadow .2s}
.si-card-hdr{display:flex;align-items:center;gap:.85rem;margin-bottom:1.25rem;padding-bottom:.85rem;border-bottom:1px solid #f1f5f9}
.si-card-ico{width:42px;height:42px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.05rem;flex-shrink:0}
.si-card-title{font-weight:800;font-size:.9rem;color:#1e1b4b}
.si-card-sub{font-size:.73rem;color:#94a3b8;margin-top:.1rem}

/* ── Category interest grid ── */
.si-cat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:.6rem;max-height:340px;overflow-y:auto;padding:.1rem .05rem}
.si-cat-grid::-webkit-scrollbar{width:4px}
.si-cat-grid::-webkit-scrollbar-thumb{background:rgba(99,102,241,.2);border-radius:4px}
.si-cat-card{border:2px solid #e0e7ff;border-radius:13px;padding:.65rem .5rem;cursor:pointer;text-align:center;transition:all .2s;user-select:none;background:#fff;position:relative;animation:si-card-in .3s ease both}
.si-cat-card:hover{border-color:#a5b4fc;box-shadow:0 4px 14px rgba(99,102,241,.12);transform:translateY(-3px)}
.si-cat-card.selected{border-color:#6366f1;background:linear-gradient(135deg,#ede9fe,#eff6ff)}
.si-cat-card.selected .si-cat-name{color:#4f46e5}
.si-cat-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;margin:0 auto .45rem;transition:transform .2s;pointer-events:none}
.si-cat-card:hover .si-cat-icon,.si-cat-card.selected .si-cat-icon{transform:scale(1.12) rotate(-6deg)}
.si-cat-name{font-size:.68rem;font-weight:700;color:#334155;line-height:1.25;pointer-events:none}
.si-cat-check{position:absolute;top:4px;right:5px;width:18px;height:18px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:50%;display:flex;align-items:center;justify-content:center;opacity:0;transform:scale(.4);transition:all .22s;pointer-events:none}
.si-cat-card.selected .si-cat-check{opacity:1;transform:scale(1);animation:si-check-pop .25s ease both}
.si-cat-check i{color:#fff;font-size:.55rem;pointer-events:none}
.si-cat-card.level-excluded{opacity:.35;filter:grayscale(.6)}
.si-cat-card.level-excluded:hover{opacity:.55;transform:none;box-shadow:none}
.si-level-badge{font-size:.62rem;font-weight:700;padding:1px 6px;border-radius:20px;white-space:nowrap}
.si-level-badge-high{background:#d1fae5;color:#065f46}
.si-level-badge-med{background:#fef3c7;color:#92400e}
.si-count-badge{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border-radius:20px;padding:2px 10px;font-size:.71rem;font-weight:700;box-shadow:0 2px 8px rgba(99,102,241,.35)}
.si-search{border:1.5px solid #e0e7ff;border-radius:11px;padding:.42rem .8rem .42rem 2.1rem;font-size:.82rem;background:#f8f7ff;transition:all .2s;width:100%}
.si-search:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1);background:#fff}

/* ── Stream selector ── */
.si-stream-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:.75rem}
@media(min-width:576px){.si-stream-grid{grid-template-columns:repeat(4,1fr)}}
.si-stream-opt{border:2px solid #e0e7ff;border-radius:14px;padding:1rem .75rem;cursor:pointer;text-align:center;transition:all .22s;user-select:none;background:#fff}
.si-stream-opt:hover{border-color:#a5b4fc;transform:translateY(-3px)}
.si-stream-opt.selected{border-color:var(--sc,#6366f1);background:var(--sb,#ede9fe)}
.si-stream-ico{width:44px;height:44px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;margin:0 auto .5rem;transition:all .2s;background:#f1f5f9;color:#94a3b8}
.si-stream-opt.selected .si-stream-ico{color:#fff}
.si-stream-lbl{font-size:.78rem;font-weight:700;color:#334155}
.si-stream-opt.selected .si-stream-lbl{color:#1e1b4b}

/* ── Combination cards ── */
.si-combo-card{border:2px solid #e0e7ff;border-radius:13px;padding:.9rem 1rem;cursor:pointer;display:flex;align-items:flex-start;gap:.75rem;transition:all .2s;user-select:none;background:#fff;position:relative}
.si-combo-card:hover{border-color:#a5b4fc;box-shadow:0 4px 14px rgba(99,102,241,.1);transform:translateY(-2px)}
.si-combo-card.selected{border-color:#6366f1;background:linear-gradient(135deg,#f5f3ff,#eff6ff)}
.si-combo-code{min-width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:900;flex-shrink:0;background:#ede9fe;color:#6366f1;transition:all .2s}
.si-combo-card.selected .si-combo-code{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff}
.si-combo-name{font-size:.8rem;font-weight:700;color:#1e1b4b;line-height:1.3}
.si-combo-subj{font-size:.7rem;color:#94a3b8;margin-top:.2rem}

/* ── Education select ── */
.si-edu-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.6rem}
.si-edu-opt{border:2px solid #e0e7ff;border-radius:13px;padding:.85rem .75rem;cursor:pointer;text-align:center;transition:all .2s;user-select:none;background:#fff}
.si-edu-opt:hover{border-color:#a5b4fc;transform:translateY(-2px)}
.si-edu-opt.selected{border-color:#6366f1;background:linear-gradient(135deg,#ede9fe,#eff6ff)}
.si-edu-ico{font-size:1.5rem;margin-bottom:.3rem}
.si-edu-lbl{font-size:.75rem;font-weight:700;color:#334155}
.si-edu-opt.selected .si-edu-lbl{color:#4f46e5}

/* ── Nav buttons ── */
.si-nav{display:flex;align-items:center;justify-content:space-between;margin-top:1.5rem;gap:.75rem}
.si-btn-back{border:1.5px solid #e0e7ff;background:#fff;color:#64748b;border-radius:12px;padding:.55rem 1.25rem;font-size:.82rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.4rem;transition:all .2s}
.si-btn-back:hover{border-color:#6366f1;color:#6366f1}
.si-btn-next{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:12px;padding:.55rem 1.6rem;font-size:.82rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.45rem;transition:all .2s;box-shadow:0 4px 16px rgba(99,102,241,.35)}
.si-btn-next:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(99,102,241,.5)}
.si-btn-save{background:linear-gradient(135deg,#059669,#10b981);color:#fff;border:none;border-radius:12px;padding:.55rem 1.6rem;font-size:.82rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.45rem;transition:all .2s;box-shadow:0 4px 14px rgba(5,150,105,.35)}
.si-btn-save:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(5,150,105,.5)}

/* Stagger category card animations */
.si-cat-card:nth-child(1){animation-delay:.02s}.si-cat-card:nth-child(2){animation-delay:.04s}
.si-cat-card:nth-child(3){animation-delay:.06s}.si-cat-card:nth-child(4){animation-delay:.08s}
.si-cat-card:nth-child(5){animation-delay:.10s}.si-cat-card:nth-child(6){animation-delay:.12s}
.si-cat-card:nth-child(7){animation-delay:.14s}.si-cat-card:nth-child(8){animation-delay:.16s}
</style>

<div class="si-page mt-3">

<!-- ══ HERO ══ -->
<div class="si-hero">
    <div class="si-orb si-orb-1"></div>
    <div class="si-orb si-orb-2"></div>
    <div class="si-orb si-orb-3"></div>
    <div class="si-hero-inner">
        <div class="si-hero-icon"><i class="bi bi-stars"></i></div>
        <div>
            <div class="si-hero-title">Welcome, <span><?= htmlspecialchars(explode(' ',$studentName)[0]) ?></span> 👋</div>
            <div class="si-hero-title" style="-webkit-text-fill-color:#fff;background:none;font-size:1rem;font-weight:700;margin-top:.2rem">Personalise Your Learning Journey</div>
            <div class="si-hero-sub">Tell us what you love and your academic level — we'll surface the courses that matter most to you</div>
            <div class="si-hero-pills">
                <span class="si-hero-pill <?= count($myInts)?'si-hero-pill-green':'' ?>">
                    <i class="bi bi-<?= count($myInts)?'check-circle-fill':'circle' ?>"></i>
                    <?= count($myInts) ?> interest<?= count($myInts)!=1?'s':'' ?> selected
                </span>
                <?php if ($profile && $profile['education_level']): ?>
                <span class="si-hero-pill si-hero-pill-green"><i class="bi bi-mortarboard-fill"></i><?= ucfirst(str_replace('_',' ',$profile['education_level'])) ?></span>
                <?php endif; ?>
                <?php if ($profile && $profile['stream']): ?>
                <span class="si-hero-pill si-hero-pill-green"><i class="bi bi-diagram-3-fill"></i><?= ucfirst($profile['stream']) ?> Stream</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ══ STEPPER ══ -->
<div class="si-stepper" id="siStepper">
    <div class="si-step-item">
        <button class="si-step-btn active" data-step="1" id="siStepBtn1">
            <span class="si-step-num">1</span> Interests
        </button>
    </div>
    <div class="si-step-connector"></div>
    <div class="si-step-item">
        <button class="si-step-btn" data-step="2" id="siStepBtn2">
            <span class="si-step-num">2</span> Education
        </button>
    </div>
    <div class="si-step-connector"></div>
    <div class="si-step-item">
        <button class="si-step-btn" data-step="3" id="siStepBtn3">
            <span class="si-step-num">3</span> Stream
        </button>
    </div>
    <div class="si-step-connector"></div>
    <div class="si-step-item">
        <button class="si-step-btn" data-step="4" id="siStepBtn4">
            <span class="si-step-num">4</span> Combination
        </button>
    </div>
</div>

<!-- ══ STEP 1 — Interests ══ -->
<div class="si-panel active" id="siPanel1">
<div class="si-card">
    <div class="si-card-hdr">
        <div class="si-card-ico" style="background:#ede9fe;color:#6366f1"><i class="bi bi-hearts"></i></div>
        <div>
            <div class="si-card-title">Areas of Interest</div>
            <div class="si-card-sub">Pick every subject that excites you — tap to select, tap again to remove</div>
        </div>
        <span class="si-count-badge ms-auto" id="siSelCount"><?= count($myInts) ?> selected</span>
    </div>
    <!-- Search -->
    <div class="position-relative mb-3">
        <i class="bi bi-search position-absolute" style="left:.7rem;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:.82rem;pointer-events:none"></i>
        <input class="si-search" id="siCatSearch" style="padding-left:2.1rem" placeholder="Search subjects…" autocomplete="off">
    </div>
    <!-- Grid -->
    <div class="si-cat-grid" id="siCatGrid">
        <?php foreach ($cats as $i => $cat):
            $cc  = $colors[$i % count($colors)];
            $sel = in_array($cat['id'], $myInts);
        ?>
        <?php
        $__prio  = $_levelPriorities[$cat['category_code'] ?? ''] ?? '';
        $__xCls  = $__prio === 'excluded' ? ' level-excluded' : '';
        $__badge = '';
        if ($__prio === 'high')   $__badge = '<span class="si-level-badge si-level-badge-high">✓ Matches level</span>';
        if ($__prio === 'medium') $__badge = '<span class="si-level-badge si-level-badge-med">~ Optional</span>';
        ?>
        <div class="si-cat-card <?= $sel?'selected':'' ?><?= $__xCls ?>"
             data-id="<?= $cat['id'] ?>"
             data-name="<?= htmlspecialchars($cat['category_title']) ?>"
             data-search="<?= strtolower(htmlspecialchars($cat['category_title'])) ?>"
             <?= $__prio === 'excluded' ? 'title="Not typically recommended for your academic level"' : '' ?>>
            <div class="si-cat-icon" style="background:<?= $cc ?>18;color:<?= $cc ?>">
                <i class="bi <?= htmlspecialchars($cat['icon']??'bi-grid') ?>"></i>
            </div>
            <div class="si-cat-name"><?= htmlspecialchars($cat['category_title']) ?></div>
            <?php if ($__badge): ?><div class="mt-1"><?= $__badge ?></div><?php endif; ?>
            <div class="si-cat-check"><i class="bi bi-check-lg"></i></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<div class="si-nav">
    <div></div>
    <button class="si-btn-next" id="siBtnSaveInterests"><i class="bi bi-check-lg"></i> Save &amp; Continue</button>
</div>
</div>

<!-- ══ STEP 2 — Education ══ -->
<div class="si-panel" id="siPanel2">
<div class="si-card">
    <div class="si-card-hdr">
        <div class="si-card-ico" style="background:#dbeafe;color:#2563eb"><i class="bi bi-mortarboard-fill"></i></div>
        <div>
            <div class="si-card-title">Education Level</div>
            <div class="si-card-sub">Tell us where you are in your academic journey</div>
        </div>
    </div>
    <div class="si-edu-grid" id="siEduGrid">
        <?php
        $eduOpts = [
            'primary'      => ['🏫','Primary School'],
            'o_level'      => ['📚','O-Level / Secondary'],
            'a_level'      => ['🎓','A-Level / Advanced'],
            'university'   => ['🏛️','University'],
            'professional' => ['💼','Professional'],
            'other'        => ['📖','Other'],
        ];
        foreach ($eduOpts as $val => $opt):
            $sel = ($profile['education_level']??'') === $val;
        ?>
        <div class="si-edu-opt <?= $sel?'selected':'' ?>" data-edu="<?= $val ?>">
            <div class="si-edu-ico"><?= $opt[0] ?></div>
            <div class="si-edu-lbl"><?= $opt[1] ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <input type="hidden" id="siEduLevel" value="<?= htmlspecialchars($profile['education_level']??'') ?>">
</div>
<div class="si-nav">
    <button class="si-btn-back" id="siBtnBack2"><i class="bi bi-arrow-left"></i> Back</button>
    <button class="si-btn-next" id="siBtnNext2">Next <i class="bi bi-arrow-right"></i></button>
</div>
</div>

<!-- ══ STEP 3 — Stream ══ -->
<div class="si-panel" id="siPanel3">
<div class="si-card">
    <div class="si-card-hdr">
        <div class="si-card-ico" style="background:#d1fae5;color:#059669"><i class="bi bi-diagram-3-fill"></i></div>
        <div>
            <div class="si-card-title">Academic Stream</div>
            <div class="si-card-sub">Choose your academic pathway for tailored content</div>
        </div>
    </div>
    <?php
    $streamMeta = [
        'science'  => ['icon'=>'bi-lightning-fill',  'label'=>'Science',  'bg'=>'#eff6ff', 'col'=>'#1d4ed8', 'ico_bg'=>'#1d4ed8'],
        'arts'     => ['icon'=>'bi-palette-fill',     'label'=>'Arts',     'bg'=>'#fdf4ff', 'col'=>'#9d174d', 'ico_bg'=>'#9d174d'],
        'business' => ['icon'=>'bi-briefcase-fill',   'label'=>'Business', 'bg'=>'#f0fdf4', 'col'=>'#065f46', 'ico_bg'=>'#065f46'],
        'general'  => ['icon'=>'bi-book-fill',        'label'=>'General',  'bg'=>'#fffbeb', 'col'=>'#92400e', 'ico_bg'=>'#92400e'],
    ];
    ?>
    <div class="si-stream-grid" id="siStreamGrid">
        <?php foreach ($streamMeta as $key => $s):
            $sel = ($profile['stream']??'') === $key;
        ?>
        <div class="si-stream-opt <?= $sel?'selected':'' ?>"
             data-stream="<?= $key ?>"
             style="--sc:<?= $s['col'] ?>;--sb:<?= $s['bg'] ?>">
            <div class="si-stream-ico" style="<?= $sel?"background:{$s['ico_bg']};color:#fff":'' ?>">
                <i class="bi <?= $s['icon'] ?>"></i>
            </div>
            <div class="si-stream-lbl"><?= $s['label'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <input type="hidden" id="siStream" value="<?= htmlspecialchars($profile['stream']??'') ?>">
</div>
<div class="si-nav">
    <button class="si-btn-back" id="siBtnBack3"><i class="bi bi-arrow-left"></i> Back</button>
    <button class="si-btn-next" id="siBtnNext3">Next <i class="bi bi-arrow-right"></i></button>
</div>
</div>

<!-- ══ STEP 4 — Combination ══ -->
<div class="si-panel" id="siPanel4">
<div class="si-card">
    <div class="si-card-hdr">
        <div class="si-card-ico" style="background:#fef3c7;color:#d97706"><i class="bi bi-grid-3x3-gap-fill"></i></div>
        <div>
            <div class="si-card-title">Subject Combination</div>
            <div class="si-card-sub">Select your specific subject combination (optional)</div>
        </div>
    </div>
    <div id="siComboWrap">
        <p class="text-muted small text-center py-3" id="siNoCombo" style="<?= ($profile['stream']??'')?'display:none':'' ?>">Select your stream in Step 3 to see combinations.</p>
        <?php foreach ($comboByStream as $stream => $streamCombos):
            $sm = $streamMeta[$stream] ?? ['label'=>ucfirst($stream),'col'=>'#6366f1','ico_bg'=>'#6366f1'];
        ?>
        <div class="si-combo-stream <?= ($profile['stream']??'')===$stream?'':'d-none' ?>" data-stream="<?= $stream ?>">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="fw-bold" style="font-size:.78rem;color:<?= $sm['col'] ?>;text-transform:uppercase;letter-spacing:.05em"><?= $sm['label'] ?> Stream</span>
                <span class="text-muted" style="font-size:.72rem">— <?= count($streamCombos) ?> combinations</span>
            </div>
            <div class="row g-2 mb-3">
                <?php foreach ($streamCombos as $c):
                    $sel = ($profile['combination_id']??0) == $c['combination_id'];
                ?>
                <div class="col-md-6">
                    <div class="si-combo-card <?= $sel?'selected':'' ?>" data-combo-id="<?= $c['combination_id'] ?>" style="--cc:<?= $sm['ico_bg'] ?>">
                        <div class="si-combo-code" style="<?= $sel?"background:linear-gradient(135deg,{$sm['ico_bg']},#8b5cf6);color:#fff":'' ?>">
                            <?= htmlspecialchars($c['combination_code']) ?>
                        </div>
                        <div>
                            <div class="si-combo-name"><?= htmlspecialchars($c['combination_name']) ?></div>
                            <div class="si-combo-subj"><?= htmlspecialchars($c['subjects']??'') ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <input type="hidden" id="siComboId" value="<?= (int)($profile['combination_id']??0) ?>">
</div>
<div class="si-nav">
    <button class="si-btn-back" id="siBtnBack4"><i class="bi bi-arrow-left"></i> Back</button>
    <button class="si-btn-save" id="siBtnSaveProfile"><i class="bi bi-check-lg"></i> Save My Profile</button>
</div>
</div>

</div><!-- /.si-page -->

<script>
(function() {
    var _currentStep   = 1;
    var _selectedStream = <?= json_encode($profile['stream'] ?? '') ?>;
    var _selectedCombo  = <?= (int)($profile['combination_id'] ?? 0) ?>;

    /* ── Step navigation ── */
    function goStep(step) {
        for (var i = 1; i <= 4; i++) {
            var panel = document.getElementById('siPanel' + i);
            var btn   = document.getElementById('siStepBtn' + i);
            if (panel) panel.classList.toggle('active', i === step);
            if (btn) {
                btn.classList.toggle('active', i === step);
                btn.classList.toggle('done',   i < step);
            }
        }
        _currentStep = step;
        if (step === 3 || step === 4) showStreamCombos(_selectedStream);
        window.scrollTo({top: 0, behavior: 'smooth'});
    }

    /* Stepper button clicks — null-checked so a missing element can't crash the IIFE */
    var siStepperEl = document.getElementById('siStepper');
    if (siStepperEl) {
        siStepperEl.addEventListener('click', function(e) {
            var btn = e.target.closest('.si-step-btn');
            if (!btn) return;
            goStep(parseInt(btn.dataset.step));
        });
    }

    /* ── Step nav buttons ── */
    function btn(id, fn) { var el = document.getElementById(id); if (el) el.addEventListener('click', fn); }
    btn('siBtnBack2', function(){ goStep(1); });
    btn('siBtnNext2', function(){ goStep(3); });
    btn('siBtnBack3', function(){ goStep(2); });
    btn('siBtnNext3', function(){ goStep(4); });
    btn('siBtnBack4', function(){ goStep(3); });

    /* ── Category grid — delegated ── */
    var catGrid = document.getElementById('siCatGrid');
    if (catGrid) {
        catGrid.addEventListener('click', function(e) {
            var card = e.target.closest('.si-cat-card');
            if (!card) return;
            card.classList.toggle('selected');
            var count = catGrid.querySelectorAll('.si-cat-card.selected').length;
            var badge = document.getElementById('siSelCount');
            if (badge) badge.textContent = count + ' selected';
        });
    }

    /* Category search */
    var catSearch = document.getElementById('siCatSearch');
    if (catSearch) {
        catSearch.addEventListener('input', function() {
            var lq = this.value.toLowerCase();
            document.querySelectorAll('.si-cat-card').forEach(function(c) {
                c.style.display = (c.dataset.search||'').includes(lq) ? '' : 'none';
            });
        });
    }

    /* ── Education options — delegated ── */
    var eduGrid = document.getElementById('siEduGrid');
    if (eduGrid) {
        eduGrid.addEventListener('click', function(e) {
            var opt = e.target.closest('.si-edu-opt');
            if (!opt) return;
            document.querySelectorAll('.si-edu-opt').forEach(function(o){ o.classList.remove('selected'); });
            opt.classList.add('selected');
            var hid = document.getElementById('siEduLevel');
            if (hid) hid.value = opt.dataset.edu;
            /* Auto-suggest stream for primary */
            if (opt.dataset.edu === 'primary') {
                _selectedStream = 'general';
                document.getElementById('siStream').value = 'general';
                document.querySelectorAll('.si-stream-opt').forEach(function(s) {
                    s.classList.toggle('selected', s.dataset.stream === 'general');
                });
            }
        });
    }

    /* ── Stream selector — delegated ── */
    var streamGrid = document.getElementById('siStreamGrid');
    if (streamGrid) {
        streamGrid.addEventListener('click', function(e) {
            var opt = e.target.closest('.si-stream-opt');
            if (!opt) return;
            var stream = opt.dataset.stream;
            var streamColors = {science:'#1d4ed8',arts:'#9d174d',business:'#065f46',general:'#92400e'};

            document.querySelectorAll('.si-stream-opt').forEach(function(o) {
                o.classList.remove('selected');
                var ico = o.querySelector('.si-stream-ico');
                if (ico) ico.removeAttribute('style');
            });
            opt.classList.add('selected');
            var ico = opt.querySelector('.si-stream-ico');
            if (ico) ico.style.cssText = 'background:' + (streamColors[stream]||'#6366f1') + ';color:#fff';

            _selectedStream = stream;
            var hid = document.getElementById('siStream');
            if (hid) hid.value = stream;
            showStreamCombos(stream);
        });
    }

    function showStreamCombos(stream) {
        document.querySelectorAll('.si-combo-stream').forEach(function(el) {
            el.classList.toggle('d-none', el.dataset.stream !== stream);
        });
        var noCombo = document.getElementById('siNoCombo');
        if (noCombo) noCombo.style.display = stream ? 'none' : '';
    }

    /* ── Combo cards — delegated ── */
    var comboWrap = document.getElementById('siComboWrap');
    if (comboWrap) {
        comboWrap.addEventListener('click', function(e) {
            var card = e.target.closest('.si-combo-card');
            if (!card) return;
            var comboId  = card.dataset.comboId;
            var streamEl = card.closest('.si-combo-stream');
            var stream   = streamEl ? streamEl.dataset.stream : '';
            var colors   = {science:'#1d4ed8',arts:'#9d174d',business:'#065f46',general:'#92400e'};
            var col      = colors[stream] || '#6366f1';

            document.querySelectorAll('.si-combo-card').forEach(function(c) {
                c.classList.remove('selected');
                var code = c.querySelector('.si-combo-code');
                if (code) code.removeAttribute('style');
            });
            card.classList.add('selected');
            var code = card.querySelector('.si-combo-code');
            if (code) code.style.cssText = 'background:linear-gradient(135deg,' + col + ',#8b5cf6);color:#fff';

            _selectedCombo = comboId;
            var hid = document.getElementById('siComboId');
            if (hid) hid.value = comboId;
        });
    }

    /* ── Save interests ── */
    btn('siBtnSaveInterests', async function() {
        var ids = Array.from(catGrid ? catGrid.querySelectorAll('.si-cat-card.selected') : []).map(function(c){ return c.dataset.id; });
        var saveBtn = document.getElementById('siBtnSaveInterests');
        if (saveBtn) { saveBtn.disabled = true; saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…'; }
        var fd = new FormData();
        fd.append('action','save_interests');
        ids.forEach(function(id){ fd.append('category_ids[]', id); });
        try {
            var j = await fetch('ajax/ajax_recommendations.php', {method:'POST', body:fd}).then(function(r){ return r.json(); });
            if (j.status === 'success') {
                Swal.fire({icon:'success',title:'Interests saved!',text:ids.length + ' areas selected',timer:1400,showConfirmButton:false,toast:true,position:'top-end'});
                goStep(2);
            } else Swal.fire({icon:'error',title:'Error',text:j.message});
        } catch(e) { Swal.fire({icon:'error',title:'Network Error',text:'Please try again.'}); }
        if (saveBtn) { saveBtn.disabled = false; saveBtn.innerHTML = '<i class="bi bi-check-lg"></i> Save &amp; Continue'; }
    });

    /* ── Save profile ── */
    btn('siBtnSaveProfile', async function() {
        var saveBtn = document.getElementById('siBtnSaveProfile');
        if (saveBtn) { saveBtn.disabled = true; saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…'; }
        var fd = new FormData();
        fd.append('action',         'save_profile');
        fd.append('education_level', (document.getElementById('siEduLevel')||{}).value||'');
        fd.append('stream',          (document.getElementById('siStream')||{}).value||'');
        fd.append('combination_id',  (document.getElementById('siComboId')||{}).value||'');
        try {
            var j = await fetch('ajax/ajax_recommendations.php', {method:'POST', body:fd}).then(function(r){ return r.json(); });
            if (j.status === 'success') {
                Swal.fire({icon:'success',title:'Profile Complete! 🎉',
                    html:'<p>Your learning profile has been saved.<br>Head to your dashboard to see personalised recommendations.</p>',
                    confirmButtonText:'Go to Dashboard',confirmButtonColor:'#6366f1'})
                .then(function(){ window.location.replace('?view=learning-student-home'); });
            } else Swal.fire({icon:'error',title:'Error',text:j.message});
        } catch(e) { Swal.fire({icon:'error',title:'Network Error',text:'Please try again.'}); }
        if (saveBtn) { saveBtn.disabled = false; saveBtn.innerHTML = '<i class="bi bi-check-lg"></i> Save My Profile'; }
    });

    /* Init */
    showStreamCombos(_selectedStream);

}());
</script>
