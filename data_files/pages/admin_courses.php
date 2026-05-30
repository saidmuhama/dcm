<?php if ($user_role != 5) { include('403.php'); return; } ?>
<style>
/* ═══ KEYFRAMES ═══════════════════════════════════════════════════ */
@keyframes acrs-orb1{from{transform:translate(0,0) scale(1)} to{transform:translate(-22px,16px) scale(1.18)}}
@keyframes acrs-orb2{from{transform:translate(0,0) scale(1)} to{transform:translate(18px,-20px) scale(1.12)}}
@keyframes acrs-orb3{from{transform:translate(0,0) scale(1)} to{transform:translate(-12px,-14px) scale(.88)}}
@keyframes acrs-kpi-in{from{opacity:0;transform:translateY(26px) scale(.93)} to{opacity:1;transform:none}}
@keyframes acrs-row-in{from{opacity:0;transform:translateX(-10px)} to{opacity:1;transform:none}}
@keyframes acrs-fade-up{from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:none}}
@keyframes acrs-skel{0%{background-position:200% 0} 100%{background-position:-200% 0}}
@keyframes acrs-pulse-ring{0%{box-shadow:0 0 0 0 rgba(245,158,11,.4)} 70%{box-shadow:0 0 0 8px transparent} 100%{box-shadow:0 0 0 0 transparent}}
@keyframes acrs-bar-grow{from{width:0} to{width:var(--bar-w,60%)}}

/* ═══ HERO ════════════════════════════════════════════════════════ */
.acrs-hero{position:relative;overflow:hidden;border-radius:22px;background:linear-gradient(135deg,#05050f 0%,#0d0a2e 35%,#160d3a 60%,#0a0f1e 100%);padding:2.1rem 2rem 1.85rem;margin:0 1rem;color:#fff;animation:acrs-fade-up .5s ease both}
.acrs-orb{position:absolute;border-radius:50%;filter:blur(58px);pointer-events:none}
.acrs-orb-1{width:260px;height:260px;background:rgba(99,102,241,.28);top:-80px;right:-30px;animation:acrs-orb1 8s ease-in-out infinite alternate}
.acrs-orb-2{width:180px;height:180px;background:rgba(16,185,129,.22);bottom:-50px;right:200px;animation:acrs-orb2 10s ease-in-out infinite alternate}
.acrs-orb-3{width:130px;height:130px;background:rgba(236,72,153,.2);top:20px;left:38%;animation:acrs-orb3 7s ease-in-out infinite alternate}
.acrs-hero-inner{position:relative;z-index:2;display:flex;align-items:flex-start;gap:1.4rem;flex-wrap:wrap}
.acrs-hero-icon{width:68px;height:68px;border-radius:20px;background:rgba(255,255,255,.09);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:1.9rem;flex-shrink:0;box-shadow:0 8px 36px rgba(99,102,241,.45);animation:acrs-kpi-in .7s cubic-bezier(.34,1.56,.64,1) both}
.acrs-hero-title{font-size:1.5rem;font-weight:900;letter-spacing:-.025em;line-height:1}
.acrs-hero-title span{background:linear-gradient(90deg,#a5b4fc 0%,#f9a8d4 50%,#6ee7b7 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.acrs-hero-sub{font-size:.82rem;opacity:.5;margin-top:.3rem}
.acrs-hero-pills{display:flex;flex-wrap:wrap;gap:.45rem;margin-top:1rem}
.acrs-hpill{padding:.25rem .8rem;border-radius:20px;font-size:.71rem;font-weight:700;display:inline-flex;align-items:center;gap:.35rem;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.08)}
.acrs-hpill-green{background:rgba(16,185,129,.18);border-color:rgba(16,185,129,.3);color:#6ee7b7}
.acrs-hpill-yellow{background:rgba(245,158,11,.18);border-color:rgba(245,158,11,.3);color:#fde68a;animation:acrs-pulse-ring 2.5s infinite}
.acrs-hpill-violet{background:rgba(99,102,241,.2);border-color:rgba(99,102,241,.35);color:#c4b5fd}
.acrs-hero-actions{margin-left:auto;display:flex;gap:.65rem;align-self:flex-start;padding-top:.15rem}
.acrs-hbtn{padding:.55rem 1.25rem;border-radius:13px;font-size:.82rem;font-weight:700;cursor:pointer;border:none;display:flex;align-items:center;gap:.45rem;transition:all .2s;white-space:nowrap}
.acrs-hbtn-ghost{background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.2)}
.acrs-hbtn-ghost:hover{background:rgba(255,255,255,.2)}

/* ═══ KPI GRID ═══════════════════════════════════════════════════ */
.acrs-kpi-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:.9rem;margin:1.25rem 1rem 0}
@media(max-width:1100px){.acrs-kpi-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:767px){.acrs-kpi-grid{grid-template-columns:repeat(2,1fr)}}
.acrs-kpi{border-radius:18px;padding:1.2rem 1.3rem;position:relative;overflow:hidden;box-shadow:0 2px 18px rgba(0,0,0,.07);transition:transform .25s,box-shadow .25s;animation:acrs-kpi-in .5s cubic-bezier(.34,1.56,.64,1) both}
.acrs-kpi:hover{transform:translateY(-5px)}
.acrs-kpi:nth-child(1){animation-delay:.05s}.acrs-kpi:nth-child(2){animation-delay:.1s}.acrs-kpi:nth-child(3){animation-delay:.15s}.acrs-kpi:nth-child(4){animation-delay:.2s}.acrs-kpi:nth-child(5){animation-delay:.25s}
.acrs-kpi-ghost{position:absolute;right:-14px;bottom:-14px;font-size:4.8rem;opacity:.08;line-height:1;pointer-events:none}
.acrs-kpi-icon{width:44px;height:44px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;margin-bottom:.8rem;position:relative;z-index:1}
.acrs-kpi-lbl{font-size:.69rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;opacity:.65;margin-bottom:.15rem;position:relative;z-index:1}
.acrs-kpi-val{font-size:2rem;font-weight:900;line-height:1;position:relative;z-index:1;font-variant-numeric:tabular-nums}
.acrs-kpi-sub{font-size:.7rem;margin-top:.25rem;opacity:.55;position:relative;z-index:1}
.acrs-kpi-bar{height:3px;border-radius:99px;margin-top:.85rem;overflow:hidden;position:relative;z-index:1}
.acrs-kpi-fill{height:100%;border-radius:99px;width:0;transition:width 1.1s cubic-bezier(.4,0,.2,1)}

/* ── KPI themes ── */
.acrs-kpi-total{background:linear-gradient(145deg,#1e1b4b,#2e2a6b);color:#fff}
.acrs-kpi-total .acrs-kpi-icon{background:rgba(255,255,255,.12);color:#c4b5fd}
.acrs-kpi-total .acrs-kpi-bar{background:rgba(255,255,255,.12)}
.acrs-kpi-total .acrs-kpi-fill{background:linear-gradient(90deg,#a5b4fc,#c4b5fd)}
.acrs-kpi-total:hover{box-shadow:0 14px 36px rgba(30,27,75,.35)}

.acrs-kpi-active{background:linear-gradient(145deg,#052e16,#065f46);color:#fff}
.acrs-kpi-active .acrs-kpi-icon{background:rgba(255,255,255,.1);color:#6ee7b7}
.acrs-kpi-active .acrs-kpi-bar{background:rgba(255,255,255,.12)}
.acrs-kpi-active .acrs-kpi-fill{background:linear-gradient(90deg,#34d399,#6ee7b7)}
.acrs-kpi-active:hover{box-shadow:0 14px 36px rgba(5,46,22,.35)}

.acrs-kpi-draft{background:linear-gradient(145deg,#1c1917,#292524);color:#fff}
.acrs-kpi-draft .acrs-kpi-icon{background:rgba(255,255,255,.1);color:#d1d5db}
.acrs-kpi-draft .acrs-kpi-bar{background:rgba(255,255,255,.12)}
.acrs-kpi-draft .acrs-kpi-fill{background:linear-gradient(90deg,#9ca3af,#d1d5db)}
.acrs-kpi-draft:hover{box-shadow:0 14px 36px rgba(28,25,23,.35)}

.acrs-kpi-pending{background:linear-gradient(145deg,#431407,#7c2d12);color:#fff}
.acrs-kpi-pending .acrs-kpi-icon{background:rgba(255,255,255,.1);color:#fed7aa}
.acrs-kpi-pending .acrs-kpi-bar{background:rgba(255,255,255,.12)}
.acrs-kpi-pending .acrs-kpi-fill{background:linear-gradient(90deg,#fb923c,#fed7aa)}
.acrs-kpi-pending:hover{box-shadow:0 14px 36px rgba(67,20,7,.35)}

.acrs-kpi-instruct{background:linear-gradient(145deg,#1e3a5f,#1e40af);color:#fff}
.acrs-kpi-instruct .acrs-kpi-icon{background:rgba(255,255,255,.1);color:#bfdbfe}
.acrs-kpi-instruct .acrs-kpi-bar{background:rgba(255,255,255,.12)}
.acrs-kpi-instruct .acrs-kpi-fill{background:linear-gradient(90deg,#60a5fa,#bfdbfe)}
.acrs-kpi-instruct:hover{box-shadow:0 14px 36px rgba(30,58,95,.35)}

/* ═══ TOOLBAR ════════════════════════════════════════════════════ */
.acrs-toolbar{background:#fff;border-radius:16px;padding:.85rem 1.2rem;margin:1.1rem 1rem .9rem;box-shadow:0 2px 14px rgba(0,0,0,.05);display:flex;flex-wrap:wrap;gap:.75rem;align-items:center;animation:acrs-fade-up .4s .15s both}
.acrs-filter-tabs{display:flex;gap:.25rem;background:#f1f5f9;border-radius:12px;padding:.2rem;flex-shrink:0}
.acrs-ftab{padding:.3rem .9rem;border-radius:9px;font-size:.77rem;font-weight:700;cursor:pointer;border:none;background:transparent;color:#64748b;transition:all .2s;white-space:nowrap;display:flex;align-items:center;gap:.3rem}
.acrs-ftab.active{background:#fff;color:#6366f1;box-shadow:0 2px 8px rgba(0,0,0,.1)}
.acrs-ftab-dot{width:7px;height:7px;border-radius:50%;display:inline-block}
.acrs-search-wrap{position:relative;flex:1;min-width:180px;max-width:280px}
.acrs-search-wrap input{width:100%;border:1.5px solid #e0e7ff;border-radius:12px;padding:.48rem .9rem .48rem 2.2rem;font-size:.84rem;background:#f8f7ff;transition:all .2s}
.acrs-search-wrap input:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1);background:#fff}
.acrs-search-ico{position:absolute;left:.72rem;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:.88rem}
.acrs-select{border:1.5px solid #e0e7ff;border-radius:11px;padding:.42rem .8rem;font-size:.81rem;background:#f8f7ff;color:#334155;transition:all .2s;min-width:130px}
.acrs-select:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1);background:#fff}
.acrs-reset-btn{padding:.42rem .9rem;border:1.5px solid #e0e7ff;border-radius:11px;font-size:.8rem;font-weight:600;background:#fff;color:#64748b;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:.35rem}
.acrs-reset-btn:hover{border-color:#6366f1;color:#6366f1;background:#f8f7ff}

/* ═══ TABLE ══════════════════════════════════════════════════════ */
.acrs-table-card{background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 2px 18px rgba(0,0,0,.06);margin:0 1rem;animation:acrs-fade-up .4s .2s both}
.acrs-tbl{width:100%;border-collapse:collapse}
.acrs-tbl thead th{background:linear-gradient(135deg,#f8f7ff,#f1f5f9);padding:.75rem 1rem;font-size:.69rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#64748b;border-bottom:2px solid #e0e7ff;white-space:nowrap}
.acrs-tbl thead th:first-child{padding-left:1.5rem}
.acrs-tbl thead th:last-child{padding-right:1.5rem;text-align:right}
.acrs-tbl tbody tr{border-bottom:1px solid #f1f5f9;transition:background .15s;animation:acrs-row-in .32s ease both}
.acrs-tbl tbody tr:last-child{border-bottom:none}
.acrs-tbl tbody tr:hover{background:#f8f7ff}
.acrs-tbl td{padding:.82rem 1rem;vertical-align:middle}
.acrs-tbl td:first-child{padding-left:1.5rem}
.acrs-tbl td:last-child{padding-right:1.5rem}

/* Row stagger */
.acrs-tbl tbody tr:nth-child(1){animation-delay:.04s}
.acrs-tbl tbody tr:nth-child(2){animation-delay:.07s}
.acrs-tbl tbody tr:nth-child(3){animation-delay:.1s}
.acrs-tbl tbody tr:nth-child(4){animation-delay:.13s}
.acrs-tbl tbody tr:nth-child(5){animation-delay:.16s}
.acrs-tbl tbody tr:nth-child(6){animation-delay:.19s}
.acrs-tbl tbody tr:nth-child(7){animation-delay:.22s}
.acrs-tbl tbody tr:nth-child(8){animation-delay:.25s}
.acrs-tbl tbody tr:nth-child(9){animation-delay:.28s}
.acrs-tbl tbody tr:nth-child(10){animation-delay:.31s}

/* Thumbnail */
.acrs-thumb-wrap{position:relative;width:52px;height:40px;border-radius:10px;overflow:hidden;flex-shrink:0}
.acrs-thumb-wrap img{width:100%;height:100%;object-fit:cover;transition:transform .3s}
tr:hover .acrs-thumb-wrap img{transform:scale(1.08)}
.acrs-thumb-fallback{width:52px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;flex-shrink:0}

/* Course cell */
.acrs-course-name{font-weight:700;font-size:.87rem;color:#1e1b4b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:210px;display:block}
.acrs-course-meta{font-size:.71rem;color:#94a3b8;margin-top:2px;display:flex;align-items:center;gap:.4rem}
.acrs-cat-badge{font-size:.62rem;font-weight:700;padding:1px 7px;border-radius:20px;background:#ede9fe;color:#6366f1;white-space:nowrap}
.acrs-price-tag{font-size:.72rem;font-weight:700;padding:1px 7px;border-radius:20px}
.acrs-price-paid{background:#fef3c7;color:#d97706}
.acrs-price-free{background:#d1fae5;color:#059669}

/* Instructor cell */
.acrs-instr-name{font-weight:600;font-size:.84rem;color:#334155}
.acrs-instr-email{font-size:.7rem;color:#94a3b8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px}

/* Stat chips */
.acrs-chip{display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:22px;border-radius:20px;font-size:.71rem;font-weight:700;padding:0 7px}
.acrs-chip-blue{background:#eff6ff;color:#2563eb}
.acrs-chip-teal{background:#f0fdfa;color:#0d9488}
.acrs-chip-slate{background:#f8fafc;color:#475569}
.acrs-chip-green{background:#f0fdf4;color:#16a34a}

/* Status / Approval badges */
.acrs-badge{display:inline-flex;align-items:center;gap:.3rem;padding:3px 10px;border-radius:20px;font-size:.71rem;font-weight:700;white-space:nowrap}
.acrs-badge-dot{width:6px;height:6px;border-radius:50%}
.acrs-badge-active  {background:#d1fae5;color:#065f46}
.acrs-badge-draft   {background:#f1f5f9;color:#475569}
.acrs-badge-inactive{background:#fee2e2;color:#991b1b}
.acrs-badge-approved{background:#d1fae5;color:#065f46}
.acrs-badge-pending {background:#fef3c7;color:#92400e}
.acrs-badge-rejected{background:#fee2e2;color:#b91c1c}
.acrs-badge-active .acrs-badge-dot  {background:#22c55e}
.acrs-badge-draft .acrs-badge-dot   {background:#94a3b8}
.acrs-badge-inactive .acrs-badge-dot{background:#ef4444}
.acrs-badge-approved .acrs-badge-dot{background:#22c55e}
.acrs-badge-pending .acrs-badge-dot {background:#f59e0b}
.acrs-badge-rejected .acrs-badge-dot{background:#ef4444}

/* Action buttons */
.acrs-action-btn{width:32px;height:32px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;font-size:.82rem;border:1.5px solid;transition:all .2s}
.acrs-action-btn:hover{transform:scale(1.14)}
.acrs-ab-view{color:#6366f1;border-color:#e0e7ff;background:#f8f7ff;text-decoration:none}
.acrs-ab-view:hover{background:#6366f1;color:#fff;border-color:#6366f1}
.acrs-ab-edit{color:#0ea5e9;border-color:#bae6fd;background:#f0f9ff}
.acrs-ab-edit:hover{background:#0ea5e9;color:#fff;border-color:#0ea5e9}
.acrs-ab-del{color:#ef4444;border-color:#fee2e2;background:#fff5f5}
.acrs-ab-del:hover{background:#ef4444;color:#fff;border-color:#ef4444}

/* ═══ PAGINATION ═════════════════════════════════════════════════ */
.acrs-pagination{display:flex;align-items:center;justify-content:space-between;padding:.85rem 1.5rem;border-top:1px solid #f1f5f9}
.acrs-page-info{font-size:.78rem;color:#94a3b8;font-weight:500}
.acrs-page-btns{display:flex;gap:.3rem;flex-wrap:wrap}
.acrs-page-btn{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:700;cursor:pointer;border:1.5px solid #e0e7ff;background:#fff;color:#475569;transition:all .18s}
.acrs-page-btn:hover{border-color:#6366f1;color:#6366f1;background:#f8f7ff}
.acrs-page-btn.active{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border-color:transparent;box-shadow:0 3px 10px rgba(99,102,241,.35)}

/* ═══ SKELETON ═══════════════════════════════════════════════════ */
.acrs-skel{background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:acrs-skel 1.4s infinite;border-radius:8px}

/* ═══ EMPTY ══════════════════════════════════════════════════════ */
.acrs-empty{padding:4rem 2rem;text-align:center;color:#94a3b8}
.acrs-empty-icon{width:72px;height:72px;border-radius:22px;background:linear-gradient(135deg,#ede9fe,#e0e7ff);display:flex;align-items:center;justify-content:center;font-size:1.9rem;color:#6366f1;margin:0 auto 1rem}

/* ═══ MODAL ══════════════════════════════════════════════════════ */
#editCourseModal .modal-content{border:none;border-radius:22px;box-shadow:0 28px 80px rgba(0,0,0,.2);overflow:hidden}
#editCourseModal .modal-header{background:linear-gradient(135deg,#05050f,#160d3a);padding:1.3rem 1.6rem;border:none}
#editCourseModal .modal-title{color:#fff;font-weight:800;font-size:.95rem;display:flex;align-items:center;gap:.5rem}
#editCourseModal .btn-close{filter:invert(1);opacity:.65}
#editCourseModal .modal-body{padding:1.6rem;background:#fafbff}
#editCourseModal .modal-footer{background:#f8f7ff;border:none;padding:1rem 1.6rem}
#editCourseModal label{font-size:.73rem;font-weight:800;text-transform:uppercase;letter-spacing:.03em;color:#475569;margin-bottom:.3rem}
#editCourseModal .form-control,#editCourseModal .form-select{border-radius:11px;border:1.5px solid #e0e7ff;font-size:.85rem;background:#fff;transition:all .2s}
#editCourseModal .form-control:focus,#editCourseModal .form-select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
.acrs-save-btn{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:11px;padding:.55rem 1.4rem;font-size:.84rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.4rem;transition:all .2s;box-shadow:0 4px 14px rgba(99,102,241,.4)}
.acrs-save-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(99,102,241,.5)}
</style>

<div class="container-fluid px-0">

<!-- ═══ HERO ════════════════════════════════════════════════════ -->
<div class="acrs-hero mx-3 mt-3">
    <div class="acrs-orb acrs-orb-1"></div>
    <div class="acrs-orb acrs-orb-2"></div>
    <div class="acrs-orb acrs-orb-3"></div>
    <div class="acrs-hero-inner">
        <div class="acrs-hero-icon"><i class="bi bi-collection-play-fill"></i></div>
        <div class="flex-grow-1">
            <div class="acrs-hero-title">All <span>Courses</span></div>
            <div class="acrs-hero-sub">View, approve, and manage every instructor course on the platform</div>
            <div class="acrs-hero-pills">
                <span class="acrs-hpill acrs-hpill-violet"><i class="bi bi-collection-fill"></i><span id="hp-total">— courses</span></span>
                <span class="acrs-hpill acrs-hpill-green"><i class="bi bi-check-circle-fill"></i><span id="hp-active">— active</span></span>
                <span class="acrs-hpill acrs-hpill-yellow"><i class="bi bi-hourglass-split"></i><span id="hp-pending">— pending approval</span></span>
            </div>
        </div>
        <div class="acrs-hero-actions d-none d-lg-flex">
            <button class="acrs-hbtn acrs-hbtn-ghost" onclick="loadCourses(currentPage)"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
        </div>
    </div>
</div>

<!-- ═══ KPI GRID ══════════════════════════════════════════════════ -->
<div class="acrs-kpi-grid">
    <div class="acrs-kpi acrs-kpi-total">
        <div class="acrs-kpi-ghost"><i class="bi bi-collection-play"></i></div>
        <div class="acrs-kpi-icon"><i class="bi bi-collection-play-fill"></i></div>
        <div class="acrs-kpi-lbl">Total Courses</div>
        <div class="acrs-kpi-val" id="statTotal">—</div>
        <div class="acrs-kpi-sub">All platform courses</div>
        <div class="acrs-kpi-bar"><div class="acrs-kpi-fill" id="fillTotal" style="width:100%"></div></div>
    </div>
    <div class="acrs-kpi acrs-kpi-active">
        <div class="acrs-kpi-ghost"><i class="bi bi-check-circle"></i></div>
        <div class="acrs-kpi-icon"><i class="bi bi-check-circle-fill"></i></div>
        <div class="acrs-kpi-lbl">Active</div>
        <div class="acrs-kpi-val" id="statActive">—</div>
        <div class="acrs-kpi-sub">Live & published</div>
        <div class="acrs-kpi-bar"><div class="acrs-kpi-fill" id="fillActive"></div></div>
    </div>
    <div class="acrs-kpi acrs-kpi-draft">
        <div class="acrs-kpi-ghost"><i class="bi bi-pencil-square"></i></div>
        <div class="acrs-kpi-icon"><i class="bi bi-pencil-square"></i></div>
        <div class="acrs-kpi-lbl">Drafts</div>
        <div class="acrs-kpi-val" id="statDraft">—</div>
        <div class="acrs-kpi-sub">In progress by instructors</div>
        <div class="acrs-kpi-bar"><div class="acrs-kpi-fill" id="fillDraft"></div></div>
    </div>
    <div class="acrs-kpi acrs-kpi-pending">
        <div class="acrs-kpi-ghost"><i class="bi bi-hourglass-split"></i></div>
        <div class="acrs-kpi-icon"><i class="bi bi-hourglass-split"></i></div>
        <div class="acrs-kpi-lbl">Pending Approval</div>
        <div class="acrs-kpi-val" id="statPending">—</div>
        <div class="acrs-kpi-sub">Awaiting admin review</div>
        <div class="acrs-kpi-bar"><div class="acrs-kpi-fill" id="fillPending"></div></div>
    </div>
    <div class="acrs-kpi acrs-kpi-instruct">
        <div class="acrs-kpi-ghost"><i class="bi bi-person-video3"></i></div>
        <div class="acrs-kpi-icon"><i class="bi bi-person-video3"></i></div>
        <div class="acrs-kpi-lbl">Instructors</div>
        <div class="acrs-kpi-val" id="statInstructors">—</div>
        <div class="acrs-kpi-sub">Active course creators</div>
        <div class="acrs-kpi-bar"><div class="acrs-kpi-fill" id="fillInstruct" style="width:100%"></div></div>
    </div>
</div>

<!-- ═══ TOOLBAR ════════════════════════════════════════════════ -->
<div class="acrs-toolbar">
    <div class="acrs-filter-tabs" id="acrsTabs">
        <button class="acrs-ftab active" data-status="" data-approval="" onclick="acrsTab(this,'','')">All</button>
        <button class="acrs-ftab" data-status="active" data-approval="" onclick="acrsTab(this,'active','')"><span class="acrs-ftab-dot" style="background:#22c55e"></span>Active</button>
        <button class="acrs-ftab" data-status="is_draft" data-approval="" onclick="acrsTab(this,'is_draft','')"><span class="acrs-ftab-dot" style="background:#94a3b8"></span>Draft</button>
        <button class="acrs-ftab" data-status="" data-approval="pending" onclick="acrsTab(this,'','pending')"><span class="acrs-ftab-dot" style="background:#f59e0b"></span>Pending</button>
        <button class="acrs-ftab" data-status="" data-approval="rejected" onclick="acrsTab(this,'','rejected')"><span class="acrs-ftab-dot" style="background:#ef4444"></span>Rejected</button>
    </div>
    <div class="acrs-search-wrap">
        <i class="bi bi-search acrs-search-ico"></i>
        <input type="text" id="searchInput" placeholder="Search course or instructor…" autocomplete="off">
    </div>
    <select id="filterInstructor" class="acrs-select">
        <option value="">All Instructors</option>
    </select>
    <select id="filterStatus" class="acrs-select" style="display:none">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="is_draft">Draft</option>
        <option value="inactive">Inactive</option>
    </select>
    <select id="filterApproval" class="acrs-select" style="display:none">
        <option value="">All Approvals</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
    </select>
    <button class="acrs-reset-btn" id="btnReset"><i class="bi bi-x-circle"></i> Reset</button>
</div>

<!-- ═══ TABLE ══════════════════════════════════════════════════ -->
<div class="acrs-table-card mb-4">
    <div class="table-responsive">
    <table class="acrs-tbl" id="coursesTable">
        <thead>
            <tr>
                <th style="width:56px">Cover</th>
                <th>Course</th>
                <th>Instructor</th>
                <th class="text-center">Chapters</th>
                <th class="text-center">Lessons</th>
                <th class="text-center">Students</th>
                <th>Status</th>
                <th>Approval</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="coursesBody">
            <tr><td colspan="9" style="padding:2.5rem 0">
                <div style="display:flex;flex-direction:column;align-items:center;gap:.6rem">
                    <?php for($i=0;$i<5;$i++): ?>
                    <div style="width:calc(100% - 3rem);display:flex;gap:1rem;align-items:center;padding:0 1.5rem">
                        <div class="acrs-skel" style="width:52px;height:40px;border-radius:10px;flex-shrink:0"></div>
                        <div style="flex:1"><div class="acrs-skel" style="width:55%;height:12px;margin-bottom:.4rem"></div><div class="acrs-skel" style="width:35%;height:9px"></div></div>
                        <div class="acrs-skel" style="width:120px;height:10px"></div>
                        <div style="display:flex;gap:.4rem"><?php for($j=0;$j<3;$j++): ?><div class="acrs-skel" style="width:32px;height:22px;border-radius:20px"></div><?php endfor; ?></div>
                    </div>
                    <?php endfor; ?>
                </div>
            </td></tr>
        </tbody>
    </table>
    </div>
    <div id="paginationBar" style="display:none">
        <div class="acrs-pagination">
            <div class="acrs-page-info" id="pageInfo"></div>
            <div class="acrs-page-btns" id="pageBtns"></div>
        </div>
    </div>
</div>

</div><!-- /.container-fluid -->

<!-- ═══ EDIT MODAL ════════════════════════════════════════════ -->
<div class="modal fade" id="editCourseModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title"><i class="bi bi-pencil-square"></i>Edit Course</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="ec_id">
        <div class="row g-3">
          <div class="col-12">
            <label>Course Title</label>
            <input type="text" id="ec_title" class="form-control">
          </div>
          <div class="col-12">
            <label>Description</label>
            <textarea id="ec_desc" class="form-control" rows="3"></textarea>
          </div>
          <div class="col-md-4">
            <label>Status</label>
            <select id="ec_status" class="form-select">
              <option value="active">Active</option>
              <option value="is_draft">Draft</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div class="col-md-4">
            <label>Approval</label>
            <select id="ec_approval" class="form-select">
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>
          <div class="col-md-4">
            <label>Price (TZS)</label>
            <input type="number" id="ec_price" class="form-control" min="0">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
        <button class="acrs-save-btn" id="saveCourseBtn"><i class="bi bi-check-lg"></i> Save Changes</button>
      </div>
    </div>
  </div>
</div>

<script>
/* ── Count-up ── */
window._acrsCountUp = function(el, to, dur=900) {
    if (!el || isNaN(to)) { if(el) el.textContent = to; return; }
    const s = performance.now();
    const fn = now => {
        const p = Math.min((now-s)/dur, 1), e = 1-Math.pow(1-p,3);
        el.textContent = Math.round(to*e);
        if (p < 1) requestAnimationFrame(fn);
    };
    requestAnimationFrame(fn);
};

const AJAX = '../data_files/ajax/ajax_admin_courses.php';
window.currentPage = 1;
let _acrsTotal = 1;

/* ── Tab helper ── */
window.acrsTab = function(btn, status, approval) {
    document.querySelectorAll('.acrs-ftab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    $('#filterStatus').val(status);
    $('#filterApproval').val(approval);
    loadCourses(1);
};

/* ── Stats ── */
window.loadStats = function() {
    $.post(AJAX, {action:'list', page:1}, function(r) {
        if (r.status !== 'success') return;
        _acrsTotal = +r.total || 1;
        _acrsCountUp(document.getElementById('statTotal'), +r.total);
        document.getElementById('hp-total').textContent = r.total + ' courses';
    }, 'json');
    $.post(AJAX, {action:'list', status:'active', page:1}, function(r) {
        const n = +r.total || 0;
        _acrsCountUp(document.getElementById('statActive'), n);
        document.getElementById('hp-active').textContent = n + ' active';
        setTimeout(() => document.getElementById('fillActive').style.width = Math.round(n/_acrsTotal*100)+'%', 300);
    }, 'json');
    $.post(AJAX, {action:'list', status:'is_draft', page:1}, function(r) {
        const n = +r.total || 0;
        _acrsCountUp(document.getElementById('statDraft'), n);
        setTimeout(() => document.getElementById('fillDraft').style.width = Math.round(n/_acrsTotal*100)+'%', 300);
    }, 'json');
    $.post(AJAX, {action:'list', approval:'pending', page:1}, function(r) {
        const n = +r.total || 0;
        _acrsCountUp(document.getElementById('statPending'), n);
        document.getElementById('hp-pending').textContent = n + ' pending approval';
        setTimeout(() => document.getElementById('fillPending').style.width = Math.round(n/_acrsTotal*100)+'%', 300);
    }, 'json');
    $.post(AJAX, {action:'instructors'}, function(r) {
        if (r.status !== 'success') return;
        _acrsCountUp(document.getElementById('statInstructors'), r.data.length);
        let $sel = $('#filterInstructor');
        $sel.find('option:not(:first)').remove();
        r.data.forEach(i => $sel.append(`<option value="${i.usr_code}">${i.first_name} ${i.last_name}</option>`));
    }, 'json');
};

/* ── Badge helpers ── */
const STATUS_MAP = {
    active:   ['active',   'Active'],
    is_draft: ['draft',    'Draft'],
    inactive: ['inactive', 'Inactive'],
};
const APPR_MAP = {
    approved: ['approved', 'Approved'],
    pending:  ['pending',  'Pending'],
    rejected: ['rejected', 'Rejected'],
};
function sBadge(s) {
    const [cls, lbl] = STATUS_MAP[s] || ['draft', s];
    return `<span class="acrs-badge acrs-badge-${cls}"><span class="acrs-badge-dot"></span>${lbl}</span>`;
}
function aBadge(a) {
    const [cls, lbl] = APPR_MAP[a] || ['pending', a];
    return `<span class="acrs-badge acrs-badge-${cls}"><span class="acrs-badge-dot"></span>${lbl}</span>`;
}
function esc(s) { return String(s||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

/* ── Thumbnail ── */
window.THUMB_COLORS = ['#6366f1','#10b981','#f59e0b','#3b82f6','#ec4899','#8b5cf6','#14b8a6'];

/* Called from onerror — replaces broken img with a letter tile */
window._acrThumbErr = function(img, title, colorIdx) {
    const col  = THUMB_COLORS[colorIdx % THUMB_COLORS.length];
    const init = (title || 'C').charAt(0).toUpperCase();
    const div  = document.createElement('div');
    div.className = 'acrs-thumb-fallback';
    div.style.cssText = 'background:' + col + '20;color:' + col;
    div.textContent = init;
    img.parentNode.replaceChild(div, img);
};

function thumbHtml(c, i) {
    if (c.thumbnail) {
        const safe = esc(c.title).replace(/\\/g,'\\\\').replace(/'/g,"\\'");
        return `<div class="acrs-thumb-wrap"><img src="../uploads/${esc(c.thumbnail)}" alt="" onerror="_acrThumbErr(this,'${safe}',${i})"></div>`;
    }
    const col  = THUMB_COLORS[i % THUMB_COLORS.length];
    const init = (c.title || 'C').charAt(0).toUpperCase();
    return `<div class="acrs-thumb-fallback" style="background:${col}20;color:${col}">${init}</div>`;
}

/* ── Load table ── */
window.loadCourses = function(page) {
    page = page || 1; currentPage = page;
    $('#coursesBody').html(`<tr><td colspan="9" class="text-center" style="padding:3rem 0">
        <div class="spinner-border" style="width:1.4rem;height:1.4rem;border-width:2px;color:#6366f1" role="status"></div>
    </td></tr>`);

    $.post(AJAX, {
        action:   'list',
        search:   $('#searchInput').val(),
        instructor: $('#filterInstructor').val(),
        status:   $('#filterStatus').val(),
        approval: $('#filterApproval').val(),
        page
    }, function(r) {
        if (r.status !== 'success') return;
        let html = '';
        if (!r.data.length) {
            html = `<tr><td colspan="9"><div class="acrs-empty">
                <div class="acrs-empty-icon"><i class="bi bi-collection-play"></i></div>
                <div class="fw-bold">No courses found</div>
                <div style="font-size:.8rem;margin-top:.3rem">Try adjusting filters or reset to see all</div>
            </div></td></tr>`;
        } else {
            r.data.forEach((c, i) => {
                html += `<tr>
                    <td>${thumbHtml(c, i)}</td>
                    <td>
                        <span class="acrs-course-name" title="${esc(c.title)}">${esc(c.title)}</span>
                        <div class="acrs-course-meta">
                            <span class="acrs-price-tag ${+c.price > 0 ? 'acrs-price-paid' : 'acrs-price-free'}">${+c.price > 0 ? 'TZS '+Number(c.price).toLocaleString() : 'Free'}</span>
                            ${c.category_title ? `<span class="acrs-cat-badge">${esc(c.category_title)}</span>` : ''}
                        </div>
                    </td>
                    <td>
                        <div class="acrs-instr-name">${esc(c.first_name||'')} ${esc(c.last_name||'')}</div>
                        <div class="acrs-instr-email">${esc(c.email_address||'')}</div>
                    </td>
                    <td class="text-center"><span class="acrs-chip acrs-chip-blue">${c.chapters||0}</span></td>
                    <td class="text-center"><span class="acrs-chip acrs-chip-teal">${c.lessons||0}</span></td>
                    <td class="text-center"><span class="acrs-chip acrs-chip-green">${c.enrollments||0}</span></td>
                    <td>${sBadge(c.status)}</td>
                    <td>${aBadge(c.is_approved)}</td>
                    <td>
                        <div style="display:flex;gap:.4rem">
                            <a href="?view=admin_course_detail&cid=${encodeURIComponent(c.cid_token)}" class="acrs-action-btn acrs-ab-view" title="Manage"><i class="bi bi-folder2-open"></i></a>
                            <button class="acrs-action-btn acrs-ab-edit btn-edit" data-id="${c.id}" title="Edit"><i class="bi bi-pencil-fill"></i></button>
                            <button class="acrs-action-btn acrs-ab-del btn-del" data-id="${c.id}" data-title="${esc(c.title)}" title="Delete"><i class="bi bi-trash-fill"></i></button>
                        </div>
                    </td>
                </tr>`;
            });
        }
        $('#coursesBody').html(html);

        /* pagination */
        const total = +r.total, per = +r.per, pages = Math.ceil(total/per);
        $('#pageInfo').text(`Showing ${Math.min((page-1)*per+1,total)}–${Math.min(page*per,total)} of ${total}`);
        let btnHtml = '';
        for (let p = 1; p <= Math.min(pages, 10); p++) {
            btnHtml += `<button class="acrs-page-btn ${p===page?'active':''} page-btn" data-p="${p}">${p}</button>`;
        }
        $('#pageBtns').html(btnHtml);
        $('#paginationBar').toggle(total > per);
    }, 'json');
};

/* ── Edit ── */
$(document).on('click', '.btn-edit', function() {
    const id = $(this).data('id');
    $.post(AJAX, {action:'get', id}, function(r) {
        if (r.status !== 'success') return;
        const c = r.data;
        $('#ec_id').val(c.id);
        $('#ec_title').val(c.title);
        $('#ec_desc').val(c.description);
        $('#ec_status').val(c.status);
        $('#ec_approval').val(c.is_approved);
        $('#ec_price').val(c.price);
        new bootstrap.Modal('#editCourseModal').show();
    }, 'json');
});

$('#saveCourseBtn').on('click', function() {
    $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving…');
    $.post(AJAX, {
        action:'update_course',
        id: $('#ec_id').val(),
        title: $('#ec_title').val(),
        description: $('#ec_desc').val(),
        status: $('#ec_status').val(),
        is_approved: $('#ec_approval').val(),
        price: $('#ec_price').val()
    }, function(r) {
        $('#saveCourseBtn').prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Save Changes');
        if (r.status === 'success') {
            bootstrap.Modal.getInstance('#editCourseModal').hide();
            loadCourses(currentPage);
            Swal.fire({icon:'success', title:'Saved!', timer:1400, showConfirmButton:false, toast:true, position:'top-end'});
        } else {
            Swal.fire({icon:'error', title:'Error', text:r.message});
        }
    }, 'json');
});

/* ── Delete ── */
$(document).on('click', '.btn-del', function() {
    const id = $(this).data('id'), title = $(this).data('title');
    Swal.fire({
        icon:'warning', title:'Delete Course?',
        html:`<p>Delete <strong>"${title}"</strong>?<br><span class="text-muted small">This will soft-delete the course. Chapters and lessons remain in the database.</span></p>`,
        showCancelButton:true, confirmButtonColor:'#ef4444', confirmButtonText:'Yes, Delete', cancelButtonText:'Cancel'
    }).then(res => {
        if (!res.isConfirmed) return;
        $.post(AJAX, {action:'delete_course', id}, function(r) {
            if (r.status === 'success') {
                loadCourses(currentPage);
                loadStats();
                Swal.fire({icon:'success', title:'Deleted', timer:1400, showConfirmButton:false, toast:true, position:'top-end'});
            }
        }, 'json');
    });
});

/* ── Pagination ── */
$(document).on('click', '.page-btn', function() { loadCourses($(this).data('p')); });

/* ── Filters ── */
let _acrsTimer;
$('#searchInput').on('input', function() { clearTimeout(_acrsTimer); _acrsTimer = setTimeout(() => loadCourses(1), 350); });
$('#filterInstructor').on('change', () => loadCourses(1));
$('#btnReset').on('click', function() {
    $('#searchInput').val('');
    $('#filterInstructor').val('');
    $('#filterStatus, #filterApproval').val('');
    document.querySelectorAll('.acrs-ftab').forEach(b => b.classList.remove('active'));
    document.querySelector('.acrs-ftab').classList.add('active');
    loadCourses(1);
});

/* ── Init ── */
loadStats();
loadCourses(1);
</script>
