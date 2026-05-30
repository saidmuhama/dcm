<?php
/* Load categories for the edit modal */
$_iclCats = [];
if (isset($db)) {
    $__r = $db->query("SELECT id, category_title, icon, category_code FROM tbl_course_categories WHERE status=1 ORDER BY sort_order,id");
    if ($__r) $_iclCats = $__r->fetch_all(MYSQLI_ASSOC);
}
$_iclColors = ['#6366f1','#10b981','#f59e0b','#3b82f6','#8b5cf6','#ec4899','#14b8a6','#f97316','#84cc16','#06b6d4','#a855f7','#ef4444'];
$_instructorName = $_SESSION['name'] ?? 'Instructor';
?>
<style>
/* ═══════════════════════════════════════════════════════════
   My Courses — Hero Design
═══════════════════════════════════════════════════════════ */
@keyframes mcl-fade{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}
@keyframes mcl-pop{0%{transform:scale(.8);opacity:0}60%{transform:scale(1.07)}100%{transform:scale(1);opacity:1}}
@keyframes mcl-orb1{from{transform:translate(0,0) scale(1)}to{transform:translate(-18px,14px) scale(1.18)}}
@keyframes mcl-orb2{from{transform:translate(0,0) scale(1)}to{transform:translate(16px,-20px) scale(1.12)}}
@keyframes mcl-orb3{from{transform:translate(0,0) scale(1)}to{transform:translate(-10px,-12px) scale(.88)}}
@keyframes mcl-kpi-in{from{opacity:0;transform:translateY(22px) scale(.94)}to{opacity:1;transform:none}}
@keyframes mcl-card-in{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}
@keyframes mcl-bar{from{width:0}to{width:var(--bw,60%)}}
@keyframes mcl-skel{0%{background-position:200% 0}100%{background-position:-200% 0}}

/* ── Hero ── */
.mcl-hero{position:relative;border-radius:22px;overflow:hidden;background:linear-gradient(135deg,#050510 0%,#0d0929 40%,#1a0f3d 70%,#0e1420 100%);padding:2.1rem 2rem 1.9rem;margin:0 1rem;color:#fff;animation:mcl-fade .4s ease both}
.mcl-orb{position:absolute;border-radius:50%;filter:blur(55px);pointer-events:none}
.mcl-orb-1{width:260px;height:260px;background:rgba(99,102,241,.28);top:-80px;right:-20px;animation:mcl-orb1 8s ease-in-out infinite alternate}
.mcl-orb-2{width:180px;height:180px;background:rgba(16,185,129,.22);bottom:-50px;right:200px;animation:mcl-orb2 10s ease-in-out infinite alternate}
.mcl-orb-3{width:130px;height:130px;background:rgba(236,72,153,.18);top:25px;left:40%;animation:mcl-orb3 7s ease-in-out infinite alternate}
.mcl-hero-inner{position:relative;z-index:2;display:flex;align-items:center;gap:1.4rem;flex-wrap:wrap}
.mcl-hero-icon{width:68px;height:68px;border-radius:20px;background:rgba(255,255,255,.09);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.14);display:flex;align-items:center;justify-content:center;font-size:1.85rem;flex-shrink:0;box-shadow:0 8px 32px rgba(99,102,241,.4);animation:mcl-pop .7s cubic-bezier(.34,1.56,.64,1) both}
.mcl-hero-title{font-size:1.45rem;font-weight:900;letter-spacing:-.025em;line-height:1.1}
.mcl-hero-title span{background:linear-gradient(90deg,#a5b4fc 0%,#f9a8d4 55%,#6ee7b7 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.mcl-hero-sub{font-size:.83rem;opacity:.5;margin-top:.3rem}
.mcl-hero-pills{display:flex;flex-wrap:wrap;gap:.45rem;margin-top:.85rem}
.mcl-hero-pill{background:rgba(255,255,255,.09);border:1px solid rgba(255,255,255,.14);color:#fff;font-size:.7rem;font-weight:700;padding:.22rem .75rem;border-radius:20px;display:inline-flex;align-items:center;gap:.3rem}
.mcl-hero-pill-g{background:rgba(16,185,129,.2);border-color:rgba(16,185,129,.3);color:#6ee7b7}
.mcl-hero-pill-y{background:rgba(245,158,11,.18);border-color:rgba(245,158,11,.3);color:#fde68a}
.mcl-hero-actions{margin-left:auto;display:flex;gap:.65rem;flex-shrink:0}
.mcl-hero-btn{padding:.52rem 1.2rem;border-radius:12px;font-size:.81rem;font-weight:700;cursor:pointer;border:none;display:flex;align-items:center;gap:.45rem;transition:all .2s;white-space:nowrap}
.mcl-hero-btn-primary{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;box-shadow:0 4px 16px rgba(99,102,241,.4)}
.mcl-hero-btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(99,102,241,.55)}
.mcl-hero-btn-ghost{background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.18)}
.mcl-hero-btn-ghost:hover{background:rgba(255,255,255,.2)}

/* ── KPI Grid ── */
.mcl-kpi-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:.9rem;margin:1.25rem 1rem 0}
@media(max-width:1100px){.mcl-kpi-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:575px){.mcl-kpi-grid{grid-template-columns:repeat(2,1fr)}}
.mcl-kpi{border-radius:18px;padding:1.2rem 1.25rem;position:relative;overflow:hidden;box-shadow:0 2px 18px rgba(0,0,0,.07);transition:transform .25s,box-shadow .25s;animation:mcl-kpi-in .5s cubic-bezier(.34,1.56,.64,1) both}
.mcl-kpi:hover{transform:translateY(-5px)}
.mcl-kpi:nth-child(1){animation-delay:.05s}.mcl-kpi:nth-child(2){animation-delay:.1s}
.mcl-kpi:nth-child(3){animation-delay:.15s}.mcl-kpi:nth-child(4){animation-delay:.2s}.mcl-kpi:nth-child(5){animation-delay:.25s}
.mcl-kpi-ghost{position:absolute;right:-14px;bottom:-14px;font-size:4.8rem;opacity:.08;line-height:1;pointer-events:none}
.mcl-kpi-icon{width:44px;height:44px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;margin-bottom:.8rem;position:relative;z-index:1}
.mcl-kpi-lbl{font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;opacity:.6;margin-bottom:.15rem;position:relative;z-index:1}
.mcl-kpi-val{font-size:2rem;font-weight:900;line-height:1;position:relative;z-index:1;font-variant-numeric:tabular-nums}
.mcl-kpi-sub{font-size:.7rem;margin-top:.25rem;opacity:.5;position:relative;z-index:1}
.mcl-kpi-bar{height:3px;border-radius:99px;margin-top:.85rem;overflow:hidden;position:relative;z-index:1}
.mcl-kpi-fill{height:100%;border-radius:99px;width:0;transition:width 1.1s cubic-bezier(.4,0,.2,1)}

/* KPI themes */
.mcl-kpi-total{background:linear-gradient(145deg,#1e1b4b,#2e2a6e);color:#fff}
.mcl-kpi-total .mcl-kpi-icon{background:rgba(255,255,255,.12);color:#c4b5fd}
.mcl-kpi-total .mcl-kpi-bar{background:rgba(255,255,255,.12)}
.mcl-kpi-total .mcl-kpi-fill{background:linear-gradient(90deg,#a5b4fc,#c4b5fd)}
.mcl-kpi-total:hover{box-shadow:0 14px 36px rgba(30,27,75,.35)}
.mcl-kpi-pub{background:linear-gradient(145deg,#052e16,#065f46);color:#fff}
.mcl-kpi-pub .mcl-kpi-icon{background:rgba(255,255,255,.1);color:#6ee7b7}
.mcl-kpi-pub .mcl-kpi-bar{background:rgba(255,255,255,.12)}
.mcl-kpi-pub .mcl-kpi-fill{background:linear-gradient(90deg,#34d399,#6ee7b7)}
.mcl-kpi-pub:hover{box-shadow:0 14px 36px rgba(5,46,22,.35)}
.mcl-kpi-draft{background:linear-gradient(145deg,#1c1917,#292524);color:#fff}
.mcl-kpi-draft .mcl-kpi-icon{background:rgba(255,255,255,.1);color:#d1d5db}
.mcl-kpi-draft .mcl-kpi-bar{background:rgba(255,255,255,.12)}
.mcl-kpi-draft .mcl-kpi-fill{background:linear-gradient(90deg,#9ca3af,#e5e7eb)}
.mcl-kpi-draft:hover{box-shadow:0 14px 36px rgba(28,25,23,.35)}
.mcl-kpi-students{background:linear-gradient(145deg,#1e3a5f,#1e40af);color:#fff}
.mcl-kpi-students .mcl-kpi-icon{background:rgba(255,255,255,.1);color:#bfdbfe}
.mcl-kpi-students .mcl-kpi-bar{background:rgba(255,255,255,.12)}
.mcl-kpi-students .mcl-kpi-fill{background:linear-gradient(90deg,#60a5fa,#bfdbfe)}
.mcl-kpi-students:hover{box-shadow:0 14px 36px rgba(30,58,95,.35)}
.mcl-kpi-rating{background:linear-gradient(145deg,#431407,#7c2d12);color:#fff}
.mcl-kpi-rating .mcl-kpi-icon{background:rgba(255,255,255,.1);color:#fed7aa}
.mcl-kpi-rating .mcl-kpi-bar{background:rgba(255,255,255,.12)}
.mcl-kpi-rating .mcl-kpi-fill{background:linear-gradient(90deg,#fb923c,#fed7aa)}
.mcl-kpi-rating:hover{box-shadow:0 14px 36px rgba(67,20,7,.35)}

/* ── Toolbar ── */
.mcl-toolbar{background:#fff;border-radius:16px;padding:.85rem 1.2rem;margin:1.1rem 1rem .85rem;box-shadow:0 2px 14px rgba(0,0,0,.05);display:flex;flex-wrap:wrap;gap:.75rem;align-items:center;animation:mcl-fade .4s .15s ease both}
.mcl-tabs{display:flex;gap:.25rem;background:#f1f5f9;border-radius:12px;padding:.2rem;flex-shrink:0}
.mcl-tab{padding:.3rem .9rem;border-radius:9px;font-size:.77rem;font-weight:700;cursor:pointer;border:none;background:transparent;color:#64748b;transition:all .2s;white-space:nowrap;display:flex;align-items:center;gap:.3rem}
.mcl-tab.active{background:#fff;color:#6366f1;box-shadow:0 2px 8px rgba(0,0,0,.1)}
.mcl-tab-dot{width:7px;height:7px;border-radius:50%}
.mcl-search{flex:1;min-width:180px;max-width:280px;position:relative}
.mcl-search input{width:100%;border:1.5px solid #e0e7ff;border-radius:12px;padding:.48rem .9rem .48rem 2.2rem;font-size:.84rem;background:#f8f7ff;transition:all .2s}
.mcl-search input:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1);background:#fff}
.mcl-search-ico{position:absolute;left:.72rem;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:.88rem}
.mcl-new-btn{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:12px;padding:.5rem 1.1rem;font-size:.82rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.4rem;transition:all .2s;box-shadow:0 4px 14px rgba(99,102,241,.35);margin-left:auto;white-space:nowrap}
.mcl-new-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(99,102,241,.5)}

/* ── Course cards ── */
.mcl-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(310px,1fr));gap:1.1rem;margin:0 1rem 1.5rem;animation:mcl-fade .4s .2s ease both}
.mcl-card{background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,.06);transition:transform .25s,box-shadow .25s;animation:mcl-card-in .4s ease both;position:relative;border:1.5px solid rgba(0,0,0,.04)}
.mcl-card:hover{transform:translateY(-5px);box-shadow:0 16px 40px rgba(0,0,0,.11)}
.mcl-card:nth-child(1){animation-delay:.05s}.mcl-card:nth-child(2){animation-delay:.09s}
.mcl-card:nth-child(3){animation-delay:.13s}.mcl-card:nth-child(4){animation-delay:.17s}
.mcl-card:nth-child(5){animation-delay:.21s}.mcl-card:nth-child(6){animation-delay:.25s}

/* Thumbnail */
.mcl-thumb{height:180px;position:relative;overflow:hidden;background:#e9ecef}
.mcl-thumb-img{width:100%;height:100%;object-fit:cover;transition:transform .4s ease}
.mcl-card:hover .mcl-thumb-img{transform:scale(1.05)}
.mcl-thumb-fallback{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:3rem;color:rgba(255,255,255,.6)}
.mcl-thumb-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.6) 0%,transparent 50%);opacity:0;transition:opacity .25s}
.mcl-card:hover .mcl-thumb-overlay{opacity:1}
.mcl-status-chip{position:absolute;top:10px;left:10px;padding:3px 10px;border-radius:20px;font-size:.68rem;font-weight:800;backdrop-filter:blur(6px)}
.mcl-price-chip{position:absolute;top:10px;right:10px;padding:3px 10px;border-radius:20px;font-size:.68rem;font-weight:800}
.mcl-manage-hint{position:absolute;bottom:10px;right:10px;background:rgba(255,255,255,.95);color:#6366f1;border:none;border-radius:20px;padding:4px 12px;font-size:.71rem;font-weight:700;cursor:pointer;opacity:0;transform:translateY(4px);transition:all .25s;display:flex;align-items:center;gap:.3rem}
.mcl-card:hover .mcl-manage-hint{opacity:1;transform:none}

/* Card body */
.mcl-card-body{padding:1.1rem 1.2rem .9rem}
.mcl-card-title{font-weight:800;font-size:.92rem;color:#1e1b4b;line-height:1.3;margin-bottom:.4rem;display:-webkit-box;-webkit-line-clamp:2;line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.mcl-rating{display:flex;align-items:center;gap:.35rem;font-size:.78rem;margin-bottom:.75rem}
.mcl-stars{color:#f59e0b;font-size:.8rem;letter-spacing:-.5px}
.mcl-rating-val{font-weight:700;color:#1e1b4b;font-size:.78rem}
.mcl-rating-cnt{color:#94a3b8;font-size:.73rem}
.mcl-cats{display:flex;flex-wrap:wrap;gap:.3rem;margin-bottom:.8rem;min-height:1.5rem}
.mcl-stat-row{display:grid;grid-template-columns:repeat(4,1fr);gap:.4rem;padding-top:.75rem;border-top:1px solid #f1f5f9}
.mcl-stat{display:flex;flex-direction:column;align-items:center;gap:.15rem}
.mcl-stat-val{font-weight:800;font-size:.88rem;color:#1e1b4b}
.mcl-stat-lbl{font-size:.62rem;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap}
.mcl-stat i{font-size:.8rem;margin-bottom:.1rem}

/* Card footer */
.mcl-card-foot{display:flex;align-items:center;justify-content:space-between;padding:.7rem 1.2rem .85rem;border-top:1px solid #f1f5f9}
.mcl-date{font-size:.72rem;color:#94a3b8;display:flex;align-items:center;gap:.3rem}
.mcl-manage-btn{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:10px;padding:.42rem 1rem;font-size:.78rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.35rem;transition:all .2s;box-shadow:0 3px 10px rgba(99,102,241,.3);text-decoration:none}
.mcl-manage-btn:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(99,102,241,.45);color:#fff}

/* Edit cat button */
.icl-edit-cat-btn{border:1.5px dashed #c4b5fd;background:#faf5ff;color:#6366f1;font-size:.68rem;font-weight:700;border-radius:20px;padding:2px 7px;cursor:pointer;transition:all .18s;white-space:nowrap;display:inline-flex;align-items:center;gap:.28rem}
.icl-edit-cat-btn:hover{background:#6366f1;color:#fff;border-color:#6366f1;border-style:solid}
.icl-cat-chip{display:inline-flex;align-items:center;gap:.28rem;font-size:.64rem;font-weight:700;padding:2px 7px;border-radius:20px;white-space:nowrap;max-width:100px;overflow:hidden;text-overflow:ellipsis}

/* Skeleton */
.mcl-skel{background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:mcl-skel 1.4s infinite;border-radius:8px}

/* Empty state */
.mcl-empty{padding:4rem 2rem;text-align:center;color:#94a3b8;grid-column:1/-1}
.mcl-empty-icon{width:80px;height:80px;border-radius:22px;background:linear-gradient(135deg,#ede9fe,#e0e7ff);display:flex;align-items:center;justify-content:center;font-size:2rem;color:#6366f1;margin:0 auto 1.1rem}

/* ── Category Edit Modal ── */
#iclCatModal .modal-content{border:none;border-radius:20px;box-shadow:0 24px 80px rgba(0,0,0,.18);overflow:hidden}
#iclCatModal .modal-header{background:linear-gradient(135deg,#1e1b4b,#312e81);padding:1.2rem 1.5rem;border:none}
#iclCatModal .modal-title{color:#fff;font-weight:800;font-size:.92rem}
#iclCatModal .btn-close{filter:invert(1);opacity:.65}
#iclCatModal .modal-body{padding:1.4rem;background:#fafbff}
#iclCatModal .modal-footer{background:#f8f7ff;border:none;padding:.9rem 1.5rem}
.icl-cat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:.45rem;max-height:230px;overflow-y:auto;padding:.1rem .05rem}
.icl-cat-grid::-webkit-scrollbar{width:4px}
.icl-cat-grid::-webkit-scrollbar-thumb{background:rgba(99,102,241,.2);border-radius:4px}
.icl-tile{border:2px solid #e0e7ff;border-radius:11px;padding:.5rem .4rem;cursor:pointer;text-align:center;transition:all .18s;user-select:none;background:#fff;position:relative}
.icl-tile:hover{border-color:#a5b4fc;box-shadow:0 3px 10px rgba(99,102,241,.12);transform:translateY(-2px)}
.icl-tile.selected{border-color:#6366f1;background:linear-gradient(135deg,#ede9fe,#eff6ff)}
.icl-tile.selected .icl-tile-name{color:#4f46e5}
.icl-tile-icon{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.95rem;margin:0 auto .35rem;transition:transform .18s;pointer-events:none}
.icl-tile:hover .icl-tile-icon,.icl-tile.selected .icl-tile-icon{transform:scale(1.1) rotate(-5deg)}
.icl-tile-name{font-size:.65rem;font-weight:700;color:#334155;line-height:1.25;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;line-clamp:2;-webkit-box-orient:vertical;pointer-events:none}
.icl-tile.selected::after{content:'\F26E';font-family:'bootstrap-icons';position:absolute;top:3px;right:5px;font-size:.62rem;color:#6366f1;pointer-events:none}
.icl-pill{display:inline-flex;align-items:center;gap:.3rem;background:linear-gradient(135deg,#ede9fe,#e0e7ff);color:#4f46e5;font-size:.72rem;font-weight:700;padding:.22rem .7rem;border-radius:20px}
.icl-pill button{background:none;border:none;padding:0;margin-left:.2rem;color:#4f46e5;font-size:.82rem;cursor:pointer;line-height:1;font-weight:700}
.icl-save-btn{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:11px;padding:.5rem 1.25rem;font-size:.82rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.4rem;transition:all .2s;box-shadow:0 4px 14px rgba(99,102,241,.35)}
.icl-save-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(99,102,241,.5)}
</style>

<!-- ══ HERO ══ -->
<div class="mcl-hero mt-3">
    <div class="mcl-orb mcl-orb-1"></div>
    <div class="mcl-orb mcl-orb-2"></div>
    <div class="mcl-orb mcl-orb-3"></div>
    <div class="mcl-hero-inner">
        <div class="mcl-hero-icon"><i class="bi bi-collection-play-fill"></i></div>
        <div class="flex-grow-1">
            <div class="mcl-hero-title">My <span>Courses</span></div>
            <div class="mcl-hero-sub">Manage, track, and grow your course portfolio</div>
            <div class="mcl-hero-pills">
                <span class="mcl-hero-pill" id="hpTotal"><i class="bi bi-collection-fill"></i>— courses</span>
                <span class="mcl-hero-pill mcl-hero-pill-g" id="hpPub"><i class="bi bi-check-circle-fill"></i>— published</span>
                <span class="mcl-hero-pill mcl-hero-pill-y" id="hpStudents"><i class="bi bi-people-fill"></i>— students</span>
            </div>
        </div>
        <div class="mcl-hero-actions d-none d-md-flex">
            <button class="mcl-hero-btn mcl-hero-btn-ghost" onclick="window.loadCourses()"><i class="bi bi-arrow-clockwise"></i>Refresh</button>
            <button class="mcl-hero-btn mcl-hero-btn-primary" data-bs-toggle="modal" data-bs-target="#createCourseModal"><i class="bi bi-plus-lg"></i>New Course</button>
        </div>
    </div>
</div>

<!-- ══ KPI CARDS ══ -->
<div class="mcl-kpi-grid">
    <div class="mcl-kpi mcl-kpi-total">
        <div class="mcl-kpi-ghost"><i class="bi bi-collection-play"></i></div>
        <div class="mcl-kpi-icon"><i class="bi bi-collection-play-fill"></i></div>
        <div class="mcl-kpi-lbl">Total Courses</div>
        <div class="mcl-kpi-val" id="kpiTotal">—</div>
        <div class="mcl-kpi-sub">All your courses</div>
        <div class="mcl-kpi-bar"><div class="mcl-kpi-fill" id="fillTotal" style="width:100%"></div></div>
    </div>
    <div class="mcl-kpi mcl-kpi-pub">
        <div class="mcl-kpi-ghost"><i class="bi bi-check-circle"></i></div>
        <div class="mcl-kpi-icon"><i class="bi bi-check-circle-fill"></i></div>
        <div class="mcl-kpi-lbl">Published</div>
        <div class="mcl-kpi-val" id="kpiPub">—</div>
        <div class="mcl-kpi-sub">Live &amp; active</div>
        <div class="mcl-kpi-bar"><div class="mcl-kpi-fill" id="fillPub"></div></div>
    </div>
    <div class="mcl-kpi mcl-kpi-draft">
        <div class="mcl-kpi-ghost"><i class="bi bi-pencil-square"></i></div>
        <div class="mcl-kpi-icon"><i class="bi bi-pencil-square"></i></div>
        <div class="mcl-kpi-lbl">Drafts</div>
        <div class="mcl-kpi-val" id="kpiDraft">—</div>
        <div class="mcl-kpi-sub">In progress</div>
        <div class="mcl-kpi-bar"><div class="mcl-kpi-fill" id="fillDraft"></div></div>
    </div>
    <div class="mcl-kpi mcl-kpi-students">
        <div class="mcl-kpi-ghost"><i class="bi bi-people"></i></div>
        <div class="mcl-kpi-icon"><i class="bi bi-people-fill"></i></div>
        <div class="mcl-kpi-lbl">Total Students</div>
        <div class="mcl-kpi-val" id="kpiStudents">—</div>
        <div class="mcl-kpi-sub">Enrolled across all</div>
        <div class="mcl-kpi-bar"><div class="mcl-kpi-fill" id="fillStudents" style="width:100%"></div></div>
    </div>
    <div class="mcl-kpi mcl-kpi-rating">
        <div class="mcl-kpi-ghost"><i class="bi bi-star"></i></div>
        <div class="mcl-kpi-icon"><i class="bi bi-star-fill"></i></div>
        <div class="mcl-kpi-lbl">Avg Rating</div>
        <div class="mcl-kpi-val" id="kpiRating">—</div>
        <div class="mcl-kpi-sub">Across rated courses</div>
        <div class="mcl-kpi-bar"><div class="mcl-kpi-fill" id="fillRating"></div></div>
    </div>
</div>

<!-- ══ TOOLBAR ══ -->
<div class="mcl-toolbar">
    <div class="mcl-tabs" id="mclTabs">
        <button class="mcl-tab active" data-filter="">All</button>
        <button class="mcl-tab" data-filter="active"><span class="mcl-tab-dot" style="background:#22c55e"></span>Published</button>
        <button class="mcl-tab" data-filter="is_draft"><span class="mcl-tab-dot" style="background:#94a3b8"></span>Draft</button>
        <button class="mcl-tab" data-filter="inactive"><span class="mcl-tab-dot" style="background:#ef4444"></span>Inactive</button>
    </div>
    <div class="mcl-search">
        <i class="bi bi-search mcl-search-ico"></i>
        <input id="mclSearch" placeholder="Search courses…" autocomplete="off">
    </div>
    <button class="mcl-new-btn d-md-none" data-bs-toggle="modal" data-bs-target="#createCourseModal"><i class="bi bi-plus-lg"></i>New</button>
</div>

<!-- ══ COURSES GRID ══ -->
<div class="mcl-grid" id="coursesContainer">
    <!-- Skeleton loaders -->
    <?php for($i=0;$i<3;$i++): ?>
    <div class="mcl-card">
        <div class="mcl-thumb mcl-skel" style="border-radius:0"></div>
        <div class="mcl-card-body">
            <div class="mcl-skel" style="height:14px;width:80%;margin-bottom:.5rem"></div>
            <div class="mcl-skel" style="height:11px;width:55%"></div>
            <div style="display:flex;gap:.4rem;margin:1rem 0 .5rem">
                <div class="mcl-skel" style="height:22px;width:70px;border-radius:20px"></div>
                <div class="mcl-skel" style="height:22px;width:90px;border-radius:20px"></div>
            </div>
        </div>
    </div>
    <?php endfor; ?>
</div>

<!-- ══ CATEGORY EDIT MODAL ══ -->
<div class="modal fade" id="iclCatModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-lg">
<div class="modal-content">
    <div class="modal-header">
        <h6 class="modal-title"><i class="bi bi-grid-3x3-gap-fill me-2"></i>Edit Course Categories</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <input type="hidden" id="iclCourseId">
        <p class="small text-muted mb-3">Select all categories that apply — students discover your course in every matching feed.</p>
        <div class="position-relative mb-2">
            <i class="bi bi-search position-absolute" style="left:.65rem;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:.8rem;pointer-events:none"></i>
            <input type="text" class="form-control form-control-sm" style="padding-left:2rem;border-radius:9px;border:1.5px solid #e0e7ff;background:#f8f7ff" id="iclFilterInput" placeholder="Filter categories…" autocomplete="off">
        </div>
        <div class="icl-cat-grid" id="iclGrid">
            <?php foreach ($_iclCats as $ci => $cat):
                $cc = $_iclColors[$ci % count($_iclColors)]; ?>
            <div class="icl-tile" data-id="<?= $cat['id'] ?>" data-name="<?= htmlspecialchars($cat['category_title']) ?>" data-search="<?= strtolower(htmlspecialchars($cat['category_title'].' '.$cat['category_code'])) ?>">
                <div class="icl-tile-icon" style="background:<?= $cc ?>18;color:<?= $cc ?>"><i class="bi <?= htmlspecialchars($cat['icon'] ?? 'bi-grid') ?>"></i></div>
                <div class="icl-tile-name"><?= htmlspecialchars($cat['category_title']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <div id="iclPills" class="d-flex flex-wrap gap-1 mt-3" style="min-height:1.5rem"></div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
        <button class="icl-save-btn" id="iclSaveBtn"><i class="bi bi-check-lg"></i> Save Categories</button>
    </div>
</div>
</div>
</div>

<script>
/* ─────────────────────────────────────────────────────────────
   Category modal — delegation-based, zero inline onclick
───────────────────────────────────────────────────────────── */
(function () {
    var _sel   = new Set();
    var _modal = null;

    function toggleTile(tile) {
        var id = tile.dataset.id;
        if (_sel.has(id)) { _sel.delete(id); tile.classList.remove('selected'); }
        else              { _sel.add(id);    tile.classList.add('selected'); }
        renderPills();
    }
    function removeCat(id) {
        _sel.delete(id);
        var tile = document.querySelector('#iclGrid .icl-tile[data-id="' + id + '"]');
        if (tile) tile.classList.remove('selected');
        renderPills();
    }
    function renderPills() {
        var wrap = document.getElementById('iclPills');
        if (!wrap) return;
        if (!_sel.size) { wrap.innerHTML = ''; return; }
        wrap.innerHTML = Array.from(_sel).map(function(id) {
            var tile = document.querySelector('#iclGrid .icl-tile[data-id="' + id + '"]');
            var name = tile ? tile.dataset.name : id;
            return '<span class="icl-pill"><i class="bi bi-check-circle-fill" style="font-size:.7rem;pointer-events:none"></i>'
                 + escHtml(name) + '<button data-remove="' + id + '">&times;</button></span>';
        }).join('');
    }
    function filterTiles(q) {
        var lq = q.toLowerCase();
        document.querySelectorAll('#iclGrid .icl-tile').forEach(function(el) {
            el.style.display = (el.dataset.search||'').includes(lq) ? '' : 'none';
        });
    }

    window.iclOpen = function(courseId, assignedIds) {
        _sel.clear();
        document.querySelectorAll('#iclGrid .icl-tile').forEach(function(t) { t.classList.remove('selected'); });
        document.getElementById('iclCourseId').value = courseId;
        (assignedIds||[]).forEach(function(id) {
            var sid = String(id); _sel.add(sid);
            var tile = document.querySelector('#iclGrid .icl-tile[data-id="' + sid + '"]');
            if (tile) tile.classList.add('selected');
        });
        renderPills();
        var fi = document.getElementById('iclFilterInput');
        if (fi) { fi.value = ''; filterTiles(''); }
        if (!_modal) _modal = new bootstrap.Modal(document.getElementById('iclCatModal'));
        _modal.show();
    };

    async function doSave() {
        var courseId = document.getElementById('iclCourseId').value;
        var ids      = Array.from(_sel).map(Number).filter(Boolean);
        var btn      = document.getElementById('iclSaveBtn');
        btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';
        try {
            var res = await fetch('ajax/ajax_save_course_categories.php', {
                method:'POST', headers:{'Content-Type':'application/json'},
                body: JSON.stringify({course_id:courseId, category_ids:ids})
            }).then(function(r){ return r.json(); });
            if (res.status === 'success') {
                _modal.hide();
                Swal.fire({icon:'success',title:'Categories Updated!',timer:1600,showConfirmButton:false,toast:true,position:'top-end'});
                window.loadCourses();
            } else Swal.fire({icon:'error',title:'Error',text:res.message});
        } catch(e) { Swal.fire({icon:'error',title:'Network Error',text:'Please try again.'}); }
        btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg"></i> Save Categories';
    }

    /* Wire all listeners directly */
    var grid = document.getElementById('iclGrid');
    if (grid) grid.addEventListener('click', function(e) {
        var tile = e.target.closest('.icl-tile'); if (tile) toggleTile(tile);
    });
    var pillsWrap = document.getElementById('iclPills');
    if (pillsWrap) pillsWrap.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-remove]'); if (btn) removeCat(btn.dataset.remove);
    });
    var fi = document.getElementById('iclFilterInput');
    if (fi) fi.addEventListener('input', function() { filterTiles(this.value); });
    var saveBtn = document.getElementById('iclSaveBtn');
    if (saveBtn) saveBtn.addEventListener('click', doSave);
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.js-icl-open');
        if (!btn) return;
        window.iclOpen(btn.dataset.courseId, JSON.parse(btn.dataset.assigned || '[]'));
    });
}());

/* ─────────────────────────────────────────────────────────────
   Count-up animation
───────────────────────────────────────────────────────────── */
function mclCountUp(el, to, dur, decimals) {
    if (!el) return;
    dur = dur||900; decimals = decimals||0;
    if (isNaN(to)) { el.textContent = to; return; }
    var s = performance.now();
    var fn = function(now) {
        var p = Math.min((now-s)/dur, 1), e = 1-Math.pow(1-p,3);
        el.textContent = (to*e).toFixed(decimals);
        if (p < 1) requestAnimationFrame(fn);
    };
    requestAnimationFrame(fn);
}

/* ─────────────────────────────────────────────────────────────
   Course card helpers
───────────────────────────────────────────────────────────── */
var ICL_CHIP_COLORS = ['#6366f1','#10b981','#f59e0b','#3b82f6','#8b5cf6','#ec4899','#14b8a6','#f97316'];
var THUMB_COLORS    = ['#6366f1','#10b981','#f59e0b','#3b82f6','#ec4899','#8b5cf6','#14b8a6'];

function buildCategoryHtml(cats) {
    if (!cats || !cats.length) {
        return '<button class="icl-edit-cat-btn js-icl-open" style="pointer-events:none"><i class="bi bi-tag-fill" style="pointer-events:none"></i>Add category</button>';
    }
    var chips = cats.slice(0,3).map(function(cat, i) {
        var col = ICL_CHIP_COLORS[i % ICL_CHIP_COLORS.length];
        return '<span class="icl-cat-chip" style="background:'+col+'18;color:'+col+'">'+escHtml(cat.category_title)+'</span>';
    }).join('');
    var more = cats.length > 3 ? '<span class="icl-cat-chip" style="background:#f1f5f9;color:#64748b">+' + (cats.length-3) + '</span>' : '';
    return chips + more;
}

function escHtml(s) {
    return String(s||'').replace(/[&<>"']/g, function(c) {
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
    });
}

/* ─────────────────────────────────────────────────────────────
   State
───────────────────────────────────────────────────────────── */
var _allCourses = [];
var _activeFilter = '';
var _searchQ = '';

function applyFilter() {
    var filtered = _allCourses.filter(function(c) {
        var statusOk = !_activeFilter || c.status === _activeFilter;
        var searchOk = !_searchQ || (c.title||'').toLowerCase().includes(_searchQ);
        return statusOk && searchOk;
    });
    renderCards(filtered);
}

/* ─────────────────────────────────────────────────────────────
   Render
───────────────────────────────────────────────────────────── */
function renderCards(courses) {
    var container = document.getElementById('coursesContainer');
    if (!courses.length) {
        container.innerHTML = '<div class="mcl-empty"><div class="mcl-empty-icon"><i class="bi bi-collection-play"></i></div>'
            + '<div class="fw-bold mb-1">No courses found</div>'
            + '<div style="font-size:.82rem">Try a different filter or create your first course</div></div>';
        return;
    }
    container.innerHTML = courses.map(function(c, idx) {
        var thumb      = c.thumbnail ? 'uploads/' + c.thumbnail.split('/').pop() : '';
        var thumbCol   = THUMB_COLORS[idx % THUMB_COLORS.length];
        var thumbHtml  = thumb
            ? '<img class="mcl-thumb-img" src="' + escHtml(thumb) + '" alt="" onerror="_mclThumbErr(this,' + idx + ')">'
            : '<div class="mcl-thumb-fallback" style="background:' + thumbCol + '22"><i class="bi bi-collection-play-fill" style="color:' + thumbCol + '"></i></div>';
        var statusMap  = {active:['#dcfce7','#065f46','Published'], is_draft:['#f1f5f9','#475569','Draft'], inactive:['#fee2e2','#991b1b','Inactive']};
        var sm         = statusMap[c.status] || statusMap.is_draft;
        var price      = parseFloat(c.price) > 0 ? 'TZS ' + Number(c.price).toLocaleString() : 'Free';
        var priceStyle = parseFloat(c.price) > 0 ? 'background:#1e1b4b;color:#fff' : 'background:#dcfce7;color:#065f46';
        var starsHtml  = c.avg_rating
            ? '<span class="mcl-stars">' + '★'.repeat(Math.round(c.avg_rating)) + '☆'.repeat(5-Math.round(c.avg_rating)) + '</span>'
              + '<span class="mcl-rating-val">' + c.avg_rating + '</span>'
              + '<span class="mcl-rating-cnt">(' + c.rating_count + ')</span>'
            : '<span style="font-size:.72rem;color:#94a3b8">No ratings yet</span>';
        var created    = c.created_at ? new Date(c.created_at).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}) : '—';
        var assignedIds = JSON.stringify((c.categories||[]).map(function(cat){ return cat.category_id; }));
        return '<div class="mcl-card" style="animation-delay:' + (idx * 0.05) + 's">'
            + '<div class="mcl-thumb" style="background:' + thumbCol + '15">'
            + thumbHtml
            + '<div class="mcl-thumb-overlay"></div>'
            + '<span class="mcl-status-chip" style="background:' + sm[0] + ';color:' + sm[1] + '">' + sm[2] + '</span>'
            + '<span class="mcl-price-chip" style="' + priceStyle + '">' + price + '</span>'
            + '<a href="./?view=course_contents_management&course_id=' + encodeURIComponent(c.course_token) + '" class="mcl-manage-hint"><i class="bi bi-arrow-right-circle-fill" style="pointer-events:none"></i>Manage</a>'
            + '</div>'
            + '<div class="mcl-card-body">'
            + '<div class="mcl-card-title" title="' + escHtml(c.title) + '">' + escHtml(c.title) + '</div>'
            + '<div class="mcl-rating">' + starsHtml + '</div>'
            + '<div class="mcl-cats">'
            + buildCategoryHtml(c.categories)
            + ' <button class="icl-edit-cat-btn js-icl-open" data-course-id="' + c.id + '" data-assigned="' + escHtml(assignedIds) + '"><i class="bi bi-pencil-fill" style="pointer-events:none"></i>Edit</button>'
            + '</div>'
            + '<div class="mcl-stat-row">'
            + '<div class="mcl-stat"><i class="bi bi-collection text-primary"></i><span class="mcl-stat-val">' + (c.chapters||0) + '</span><span class="mcl-stat-lbl">Chapters</span></div>'
            + '<div class="mcl-stat"><i class="bi bi-play-circle text-info"></i><span class="mcl-stat-val">' + (c.lessons||0) + '</span><span class="mcl-stat-lbl">Lessons</span></div>'
            + '<div class="mcl-stat"><i class="bi bi-people text-success"></i><span class="mcl-stat-val">' + (c.enrolled||0) + '</span><span class="mcl-stat-lbl">Students</span></div>'
            + '<div class="mcl-stat"><i class="bi bi-journal-bookmark text-warning"></i><span class="mcl-stat-val">' + (c.study_notes||0) + '</span><span class="mcl-stat-lbl">Notes</span></div>'
            + '</div></div>'
            + '<div class="mcl-card-foot">'
            + '<span class="mcl-date"><i class="bi bi-calendar3"></i>' + created + '</span>'
            + '<a href="./?view=course_contents_management&course_id=' + encodeURIComponent(c.course_token) + '" class="mcl-manage-btn"><i class="bi bi-folder2-open" style="pointer-events:none"></i>Manage</a>'
            + '</div></div>';
    }).join('');
}

window._mclThumbErr = function(img, idx) {
    var col = THUMB_COLORS[idx % THUMB_COLORS.length];
    var div = document.createElement('div');
    div.className = 'mcl-thumb-fallback';
    div.style.cssText = 'background:' + col + '22';
    div.innerHTML = '<i class="bi bi-collection-play-fill" style="color:' + col + '"></i>';
    img.parentNode.replaceChild(div, img);
};

/* ─────────────────────────────────────────────────────────────
   Update KPIs
───────────────────────────────────────────────────────────── */
function updateKpis(courses) {
    var total    = courses.length;
    var pub      = courses.filter(function(c){ return c.status==='active'; }).length;
    var draft    = courses.filter(function(c){ return c.status==='is_draft'; }).length;
    var students = courses.reduce(function(s,c){ return s + (parseInt(c.enrolled)||0); }, 0);
    var rated    = courses.filter(function(c){ return c.avg_rating; });
    var avgRating= rated.length ? (rated.reduce(function(s,c){ return s+parseFloat(c.avg_rating); },0)/rated.length) : 0;

    mclCountUp(document.getElementById('kpiTotal'),    total,   900, 0);
    mclCountUp(document.getElementById('kpiPub'),      pub,     900, 0);
    mclCountUp(document.getElementById('kpiDraft'),    draft,   900, 0);
    mclCountUp(document.getElementById('kpiStudents'), students,1000,0);
    mclCountUp(document.getElementById('kpiRating'),   avgRating,900,1);

    var pct = function(n){ return total ? Math.round(n/total*100)+'%' : '0%'; };
    setTimeout(function() {
        document.getElementById('fillPub').style.width    = pct(pub);
        document.getElementById('fillDraft').style.width  = pct(draft);
        document.getElementById('fillRating').style.width = avgRating ? Math.round(avgRating/5*100)+'%' : '0%';
    }, 200);

    /* hero pills */
    document.getElementById('hpTotal').innerHTML    = '<i class="bi bi-collection-fill"></i>' + total + ' course' + (total!==1?'s':'');
    document.getElementById('hpPub').innerHTML      = '<i class="bi bi-check-circle-fill"></i>' + pub + ' published';
    document.getElementById('hpStudents').innerHTML = '<i class="bi bi-people-fill"></i>' + students + ' student' + (students!==1?'s':'');
}

/* ─────────────────────────────────────────────────────────────
   Load courses
───────────────────────────────────────────────────────────── */
window.loadCourses = function() {
    fetch('ajax/ajax_get_courses.php')
        .then(function(r){ return r.json(); })
        .then(function(res) {
            if (res.status !== 'success') {
                document.getElementById('coursesContainer').innerHTML =
                    '<div class="mcl-empty"><div class="mcl-empty-icon"><i class="bi bi-collection-play"></i></div>'
                    + '<div class="fw-bold">Could not load courses</div></div>';
                return;
            }
            _allCourses = res.data || [];
            updateKpis(_allCourses);
            applyFilter();
        })
        .catch(function() {
            document.getElementById('coursesContainer').innerHTML =
                '<p class="text-danger text-center py-4 col-12">Failed to load courses. Please refresh.</p>';
        });
};

/* ─────────────────────────────────────────────────────────────
   Filter tabs + search
───────────────────────────────────────────────────────────── */
document.getElementById('mclTabs').addEventListener('click', function(e) {
    var tab = e.target.closest('.mcl-tab');
    if (!tab) return;
    document.querySelectorAll('.mcl-tab').forEach(function(t){ t.classList.remove('active'); });
    tab.classList.add('active');
    _activeFilter = tab.dataset.filter;
    applyFilter();
});

var _searchTimer;
document.getElementById('mclSearch').addEventListener('input', function() {
    clearTimeout(_searchTimer);
    var q = this.value.toLowerCase();
    _searchTimer = setTimeout(function() { _searchQ = q; applyFilter(); }, 280);
});

/* Call directly — DOMContentLoaded already fired in the SPA */
loadCourses();
</script>
