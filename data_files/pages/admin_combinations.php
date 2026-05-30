<?php
if (($user_role ?? 0) != 5) { include('403.php'); return; }
?>
<style>
/* ═══ HERO ════════════════════════════════════════════════════════ */
.acb-hero{position:relative;overflow:hidden;border-radius:20px;background:linear-gradient(135deg,#0a0f1e 0%,#1a1040 40%,#0e1628 100%);padding:2rem 2rem 1.75rem;margin:0 1rem 0;color:#fff}
.acb-hero-orb{position:absolute;border-radius:50%;filter:blur(55px);pointer-events:none}
.acb-orb-sci{width:200px;height:200px;background:rgba(59,130,246,.32);top:-60px;right:30px;animation:cbOrb1 7s ease-in-out infinite alternate}
.acb-orb-art{width:150px;height:150px;background:rgba(236,72,153,.28);bottom:-40px;right:220px;animation:cbOrb2 9s ease-in-out infinite alternate}
.acb-orb-bus{width:120px;height:120px;background:rgba(16,185,129,.25);top:30px;left:45%;animation:cbOrb3 6s ease-in-out infinite alternate}
@keyframes cbOrb1{from{transform:translate(0,0) scale(1)} to{transform:translate(-20px,15px) scale(1.2)}}
@keyframes cbOrb2{from{transform:translate(0,0) scale(1)} to{transform:translate(15px,-20px) scale(1.15)}}
@keyframes cbOrb3{from{transform:translate(0,0) scale(1)} to{transform:translate(-10px,12px) scale(.9)}}
.acb-hero-inner{position:relative;z-index:2;display:flex;align-items:center;gap:1.25rem}
.acb-hero-icon{width:64px;height:64px;border-radius:18px;background:rgba(255,255,255,.1);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;font-size:1.8rem;flex-shrink:0;border:1px solid rgba(255,255,255,.15);box-shadow:0 8px 32px rgba(59,130,246,.4);animation:cbIconPop .6s cubic-bezier(.34,1.56,.64,1) both}
@keyframes cbIconPop{from{transform:scale(0) rotate(15deg);opacity:0} to{transform:scale(1) rotate(0);opacity:1}}
.acb-hero-title{font-size:1.4rem;font-weight:800;letter-spacing:-.02em;line-height:1.1}
.acb-hero-title span{background:linear-gradient(90deg,#93c5fd,#d8b4fe,#fbcfe8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.acb-hero-sub{font-size:.82rem;opacity:.55;margin-top:.2rem}
.acb-hero-stream-pills{display:flex;gap:.5rem;margin-top:.9rem;flex-wrap:wrap}
.acb-stream-pill{padding:.25rem .85rem;border-radius:20px;font-size:.72rem;font-weight:700;display:flex;align-items:center;gap:.35rem;border:1px solid transparent}
.acb-pill-sci{background:rgba(59,130,246,.2);border-color:rgba(59,130,246,.3);color:#93c5fd}
.acb-pill-art{background:rgba(236,72,153,.2);border-color:rgba(236,72,153,.3);color:#f9a8d4}
.acb-pill-bus{background:rgba(16,185,129,.2);border-color:rgba(16,185,129,.3);color:#6ee7b7}
.acb-pill-gen{background:rgba(245,158,11,.2);border-color:rgba(245,158,11,.3);color:#fde68a}
.acb-hero-btns{margin-left:auto;display:flex;gap:.6rem;flex-shrink:0}
.acb-btn{padding:.55rem 1.2rem;border-radius:12px;font-size:.82rem;font-weight:700;cursor:pointer;border:none;display:flex;align-items:center;gap:.4rem;transition:all .2s;white-space:nowrap}
.acb-btn-primary{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;box-shadow:0 4px 16px rgba(99,102,241,.45)}
.acb-btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(99,102,241,.6)}
.acb-btn-ghost{background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.18)}
.acb-btn-ghost:hover{background:rgba(255,255,255,.2)}

/* ═══ STREAM KPI CARDS ════════════════════════════════════════════ */
.acb-kpi-row{display:grid;grid-template-columns:repeat(5,1fr);gap:1rem;margin:1.25rem 1rem .25rem}
@media(max-width:991px){.acb-kpi-row{grid-template-columns:repeat(3,1fr)}}
@media(max-width:575px){.acb-kpi-row{grid-template-columns:repeat(2,1fr)}}
.acb-kpi{border-radius:18px;padding:1.15rem 1.25rem;position:relative;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,.07);transition:transform .25s,box-shadow .25s;animation:cbKpiIn .5s cubic-bezier(.34,1.56,.64,1) both;cursor:pointer}
.acb-kpi:hover{transform:translateY(-5px)}
.acb-kpi:nth-child(1){animation-delay:.06s}.acb-kpi:nth-child(2){animation-delay:.12s}.acb-kpi:nth-child(3){animation-delay:.18s}.acb-kpi:nth-child(4){animation-delay:.24s}.acb-kpi:nth-child(5){animation-delay:.3s}
@keyframes cbKpiIn{from{opacity:0;transform:translateY(22px) scale(.94)} to{opacity:1;transform:none}}
.acb-kpi-bg-icon{position:absolute;right:-15px;bottom:-15px;font-size:4.5rem;opacity:.1;line-height:1}
.acb-kpi-tag{display:inline-flex;align-items:center;gap:.3rem;font-size:.67rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;padding:.2rem .65rem;border-radius:20px;margin-bottom:.6rem}
.acb-kpi-num{font-size:2.2rem;font-weight:900;line-height:1;font-variant-numeric:tabular-nums}
.acb-kpi-label{font-size:.72rem;margin-top:.15rem;font-weight:500}
.acb-kpi-seg{height:3px;border-radius:99px;margin-top:.8rem;overflow:hidden;background:rgba(0,0,0,.1)}
.acb-kpi-seg-fill{height:100%;border-radius:99px;transition:width 1s cubic-bezier(.4,0,.2,1);width:0}

/* KPI themes */
.acb-kpi-total{background:linear-gradient(135deg,#1e1b4b,#312e81);color:#fff}
.acb-kpi-total .acb-kpi-tag{background:rgba(255,255,255,.15);color:#c4b5fd}
.acb-kpi-total .acb-kpi-seg-fill{background:linear-gradient(90deg,#a5b4fc,#c4b5fd)}
.acb-kpi-sci{background:linear-gradient(135deg,#1e3a5f,#1d4ed8);color:#fff}
.acb-kpi-sci .acb-kpi-tag{background:rgba(255,255,255,.15);color:#93c5fd}
.acb-kpi-sci .acb-kpi-seg-fill{background:linear-gradient(90deg,#60a5fa,#93c5fd)}
.acb-kpi-sci:hover{box-shadow:0 12px 32px rgba(29,78,216,.3)}
.acb-kpi-art{background:linear-gradient(135deg,#500724,#9d174d);color:#fff}
.acb-kpi-art .acb-kpi-tag{background:rgba(255,255,255,.15);color:#fbcfe8}
.acb-kpi-art .acb-kpi-seg-fill{background:linear-gradient(90deg,#f472b6,#fbcfe8)}
.acb-kpi-art:hover{box-shadow:0 12px 32px rgba(157,23,77,.3)}
.acb-kpi-bus{background:linear-gradient(135deg,#064e3b,#065f46);color:#fff}
.acb-kpi-bus .acb-kpi-tag{background:rgba(255,255,255,.15);color:#6ee7b7}
.acb-kpi-bus .acb-kpi-seg-fill{background:linear-gradient(90deg,#34d399,#6ee7b7)}
.acb-kpi-bus:hover{box-shadow:0 12px 32px rgba(6,79,60,.3)}
.acb-kpi-act{background:linear-gradient(135deg,#1c1917,#292524);color:#fff}
.acb-kpi-act .acb-kpi-tag{background:rgba(255,255,255,.12);color:#d1d5db}
.acb-kpi-act .acb-kpi-seg-fill{background:linear-gradient(90deg,#4ade80,#86efac)}

/* ═══ STREAM TABS ══════════════════════════════════════════════════ */
.acb-tabs-wrap{background:#fff;border-radius:16px;margin:1rem 1rem .75rem;padding:.8rem 1.1rem;box-shadow:0 2px 12px rgba(0,0,0,.05);display:flex;align-items:center;gap:.6rem;flex-wrap:wrap}
.acb-stream-tabs{display:flex;gap:.3rem;background:#f1f5f9;border-radius:12px;padding:.25rem;flex-wrap:wrap}
.acb-stab{padding:.35rem 1.1rem;border-radius:9px;font-size:.78rem;font-weight:700;cursor:pointer;border:none;background:transparent;color:#64748b;transition:all .2s;display:flex;align-items:center;gap:.35rem;white-space:nowrap}
.acb-stab.active{color:#fff;box-shadow:0 2px 8px rgba(0,0,0,.15)}
.acb-stab-all.active   {background:linear-gradient(135deg,#6366f1,#8b5cf6)}
.acb-stab-sci.active   {background:linear-gradient(135deg,#1d4ed8,#3b82f6)}
.acb-stab-art.active   {background:linear-gradient(135deg,#9d174d,#ec4899)}
.acb-stab-bus.active   {background:linear-gradient(135deg,#065f46,#10b981)}
.acb-stab-gen.active   {background:linear-gradient(135deg,#92400e,#f59e0b)}
.acb-stab-dot{width:8px;height:8px;border-radius:50%}
.acb-stab-sci .acb-stab-dot{background:#3b82f6}
.acb-stab-art .acb-stab-dot{background:#ec4899}
.acb-stab-bus .acb-stab-dot{background:#10b981}
.acb-stab-gen .acb-stab-dot{background:#f59e0b}
.acb-stab.active .acb-stab-dot{background:rgba(255,255,255,.8)}
.acb-search{flex:1;min-width:180px;max-width:260px;position:relative}
.acb-search input{width:100%;border:1.5px solid #e0e7ff;border-radius:11px;padding:.45rem .85rem .45rem 2.1rem;font-size:.83rem;transition:all .2s;background:#f8f7ff}
.acb-search input:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1);background:#fff}
.acb-search-ico{position:absolute;left:.65rem;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:.85rem}
.acb-add-btn{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:11px;padding:.48rem 1rem;font-size:.82rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.35rem;transition:all .2s;box-shadow:0 4px 14px rgba(99,102,241,.3);margin-left:auto;white-space:nowrap}
.acb-add-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(99,102,241,.5)}

/* ═══ CARDS GRID ══════════════════════════════════════════════════ */
.acb-cards-wrap{padding:0 1rem 1.5rem}
.acb-stream-section{margin-bottom:1.75rem}
.acb-section-hdr{display:flex;align-items:center;gap:.75rem;margin-bottom:.9rem;padding:.6rem 1rem;border-radius:12px}
.acb-section-hdr-sci{background:linear-gradient(135deg,#dbeafe,#eff6ff)}
.acb-section-hdr-art{background:linear-gradient(135deg,#fce7f3,#fdf4ff)}
.acb-section-hdr-bus{background:linear-gradient(135deg,#d1fae5,#f0fdf4)}
.acb-section-hdr-gen{background:linear-gradient(135deg,#fef3c7,#fffbeb)}
.acb-section-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem}
.acb-section-icon-sci{background:#1d4ed8;color:#fff}
.acb-section-icon-art{background:#9d174d;color:#fff}
.acb-section-icon-bus{background:#065f46;color:#fff}
.acb-section-icon-gen{background:#92400e;color:#fff}
.acb-section-title{font-weight:800;font-size:.88rem;color:#1e1b4b}
.acb-section-count{margin-left:auto;font-size:.72rem;font-weight:700;padding:2px 10px;border-radius:20px}
.acb-section-count-sci{background:#dbeafe;color:#1e40af}
.acb-section-count-art{background:#fce7f3;color:#831843}
.acb-section-count-bus{background:#d1fae5;color:#065f46}
.acb-section-count-gen{background:#fef3c7;color:#92400e}
.acb-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:.85rem}
.acb-card{background:#fff;border-radius:16px;border:2px solid #f1f5f9;padding:1.1rem 1.15rem;transition:all .25s;animation:cbCardIn .4s cubic-bezier(.34,1.56,.64,1) both;position:relative;overflow:hidden}
.acb-card::before{content:'';position:absolute;top:0;left:0;bottom:0;width:4px;border-radius:4px 0 0 4px}
.acb-card-sci::before{background:linear-gradient(180deg,#1d4ed8,#60a5fa)}
.acb-card-art::before{background:linear-gradient(180deg,#9d174d,#f472b6)}
.acb-card-bus::before{background:linear-gradient(180deg,#065f46,#34d399)}
.acb-card-gen::before{background:linear-gradient(180deg,#92400e,#fbbf24)}
@keyframes cbCardIn{from{opacity:0;transform:scale(.93)} to{opacity:1;transform:none}}
.acb-card:hover{border-color:rgba(99,102,241,.2);box-shadow:0 8px 24px rgba(0,0,0,.08);transform:translateY(-3px)}
.acb-card-top{display:flex;align-items:flex-start;gap:.85rem;padding-left:.3rem}
.acb-code-badge{min-width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:.78rem;letter-spacing:.02em;flex-shrink:0;transition:transform .2s}
.acb-card:hover .acb-code-badge{transform:scale(1.08) rotate(-3deg)}
.acb-code-sci{background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:#1d4ed8}
.acb-code-art{background:linear-gradient(135deg,#fce7f3,#fbcfe8);color:#9d174d}
.acb-code-bus{background:linear-gradient(135deg,#d1fae5,#a7f3d0);color:#065f46}
.acb-code-gen{background:linear-gradient(135deg,#fef3c7,#fde68a);color:#92400e}
.acb-card-name{font-weight:700;font-size:.87rem;color:#1e1b4b;line-height:1.3}
.acb-card-desc{font-size:.73rem;color:#94a3b8;margin-top:.2rem;line-height:1.4}
.acb-subjects-wrap{display:flex;flex-wrap:wrap;gap:.3rem;margin-top:.75rem;padding-left:.3rem}
.acb-subj-chip{background:#f1f5f9;color:#475569;font-size:.67rem;font-weight:700;padding:2px 8px;border-radius:20px}
.acb-card-footer{display:flex;align-items:center;justify-content:space-between;margin-top:.85rem;padding-top:.75rem;border-top:1px solid #f1f5f9;padding-left:.3rem}
.acb-card-status{display:flex;align-items:center;gap:.4rem;font-size:.72rem;font-weight:700}
.acb-sdot{width:7px;height:7px;border-radius:50%}
.acb-sdot-active{background:#22c55e;box-shadow:0 0 0 2px rgba(34,197,94,.2)}
.acb-sdot-inactive{background:#ef4444}
.acb-card-actions{display:flex;gap:.35rem}
.acb-card-btn{width:30px;height:30px;border-radius:8px;border:1.5px solid;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.78rem;transition:all .18s}
.acb-card-btn:hover{transform:scale(1.15)}
.acb-btn-edit{color:#6366f1;border-color:#e0e7ff;background:#f8f7ff}
.acb-btn-edit:hover{background:#6366f1;color:#fff;border-color:#6366f1}
.acb-btn-del{color:#ef4444;border-color:#fee2e2;background:#fff5f5}
.acb-btn-del:hover{background:#ef4444;color:#fff;border-color:#ef4444}
.acb-btn-toggle{font-size:.65rem;padding:2px 8px;border-radius:20px;border:none;cursor:pointer;font-weight:700;transition:all .18s;white-space:nowrap}
.acb-btn-toggle-on{background:#d1fae5;color:#065f46}
.acb-btn-toggle-on:hover{background:#10b981;color:#fff}
.acb-btn-toggle-off{background:#fee2e2;color:#991b1b}
.acb-btn-toggle-off:hover{background:#ef4444;color:#fff}
.acb-card-inactive{opacity:.6;filter:grayscale(.2)}

/* ═══ EMPTY ════════════════════════════════════════════════════════ */
.acb-empty{padding:3.5rem 2rem;text-align:center;color:#94a3b8}
.acb-empty-icon{width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg,#ede9fe,#e0e7ff);display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:#6366f1;margin:0 auto .85rem}
.acb-skeleton{background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:cbSkel 1.4s infinite;border-radius:8px}
@keyframes cbSkel{0%{background-position:200% 0} 100%{background-position:-200% 0}}

/* ═══ MODAL ════════════════════════════════════════════════════════ */
#comboModal .modal-content{border:none;border-radius:20px;box-shadow:0 24px 80px rgba(0,0,0,.18);overflow:hidden}
#comboModal .modal-header{background:linear-gradient(135deg,#0a0f1e,#1a1040);padding:1.25rem 1.5rem;border:none}
#comboModal .modal-title{color:#fff;font-weight:800;font-size:.95rem}
#comboModal .btn-close{filter:invert(1);opacity:.7}
#comboModal .modal-body{padding:1.5rem;background:#fafbff}
#comboModal .modal-footer{background:#f8f7ff;border:none;padding:1rem 1.5rem}
#comboModal .form-control,#comboModal .form-select{border-radius:10px;border:1.5px solid #e0e7ff;font-size:.85rem;transition:all .2s;background:#fff}
#comboModal .form-control:focus,#comboModal .form-select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
#comboModal label{font-size:.74rem;font-weight:700;color:#475569;letter-spacing:.02em;text-transform:uppercase;margin-bottom:.3rem}
</style>

<div class="container-fluid px-0">

<!-- HERO -->
<div class="acb-hero mx-3 mt-3">
    <div class="acb-hero-orb acb-orb-sci"></div>
    <div class="acb-hero-orb acb-orb-art"></div>
    <div class="acb-hero-orb acb-orb-bus"></div>
    <div class="acb-hero-inner">
        <div class="acb-hero-icon"><i class="bi bi-diagram-3-fill"></i></div>
        <div>
            <div class="acb-hero-title">Subject <span>Combinations</span></div>
            <div class="acb-hero-sub">Manage Science, Arts and Business academic combinations for stream-based content targeting</div>
            <div class="acb-hero-stream-pills">
                <span class="acb-stream-pill acb-pill-sci"><i class="bi bi-lightning-fill"></i><span id="hp-sci">— Science</span></span>
                <span class="acb-stream-pill acb-pill-art"><i class="bi bi-palette-fill"></i><span id="hp-art">— Arts</span></span>
                <span class="acb-stream-pill acb-pill-bus"><i class="bi bi-briefcase-fill"></i><span id="hp-bus">— Business</span></span>
                <span class="acb-stream-pill acb-pill-gen"><i class="bi bi-book-fill"></i><span id="hp-gen">— General</span></span>
            </div>
        </div>
        <div class="acb-hero-btns d-none d-md-flex">
            <button class="acb-btn acb-btn-ghost" onclick="comboMgr.load()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
            <button class="acb-btn acb-btn-primary" onclick="comboMgr.openCreate()"><i class="bi bi-plus-lg"></i> New Combination</button>
        </div>
    </div>
</div>

<!-- KPI ROW -->
<div class="acb-kpi-row">
    <div class="acb-kpi acb-kpi-total" onclick="comboMgr.setStream('',this)">
        <div class="acb-kpi-bg-icon"><i class="bi bi-diagram-3"></i></div>
        <div class="acb-kpi-tag"><i class="bi bi-collection-fill me-1"></i>Total</div>
        <div class="acb-kpi-num" id="cStatTotal">—</div>
        <div class="acb-kpi-label">All combinations</div>
        <div class="acb-kpi-seg"><div class="acb-kpi-seg-fill" id="segTotal" style="width:100%"></div></div>
    </div>
    <div class="acb-kpi acb-kpi-sci" onclick="comboMgr.setStream('science',this)">
        <div class="acb-kpi-bg-icon"><i class="bi bi-lightning"></i></div>
        <div class="acb-kpi-tag"><i class="bi bi-lightning-fill me-1"></i>Science</div>
        <div class="acb-kpi-num" id="cStatSci">—</div>
        <div class="acb-kpi-label">PCM · PCB · PGM…</div>
        <div class="acb-kpi-seg"><div class="acb-kpi-seg-fill" id="segSci"></div></div>
    </div>
    <div class="acb-kpi acb-kpi-art" onclick="comboMgr.setStream('arts',this)">
        <div class="acb-kpi-bg-icon"><i class="bi bi-palette"></i></div>
        <div class="acb-kpi-tag"><i class="bi bi-palette-fill me-1"></i>Arts</div>
        <div class="acb-kpi-num" id="cStatArts">—</div>
        <div class="acb-kpi-label">HGL · HKL · ECA…</div>
        <div class="acb-kpi-seg"><div class="acb-kpi-seg-fill" id="segArts"></div></div>
    </div>
    <div class="acb-kpi acb-kpi-bus" onclick="comboMgr.setStream('business',this)">
        <div class="acb-kpi-bg-icon"><i class="bi bi-briefcase"></i></div>
        <div class="acb-kpi-tag"><i class="bi bi-briefcase-fill me-1"></i>Business</div>
        <div class="acb-kpi-num" id="cStatBus">—</div>
        <div class="acb-kpi-label">CBA · ECG · BAM…</div>
        <div class="acb-kpi-seg"><div class="acb-kpi-seg-fill" id="segBus"></div></div>
    </div>
    <div class="acb-kpi acb-kpi-act">
        <div class="acb-kpi-bg-icon"><i class="bi bi-check-circle"></i></div>
        <div class="acb-kpi-tag"><i class="bi bi-check-circle-fill me-1"></i>Active</div>
        <div class="acb-kpi-num" id="cStatActive">—</div>
        <div class="acb-kpi-label">Available to students</div>
        <div class="acb-kpi-seg"><div class="acb-kpi-seg-fill" id="segActive"></div></div>
    </div>
</div>

<!-- TOOLBAR WITH STREAM TABS -->
<div class="acb-tabs-wrap">
    <div class="acb-stream-tabs" id="streamTabs">
        <button class="acb-stab acb-stab-all active" data-stream="" onclick="comboMgr.setStream('',this)">All</button>
        <button class="acb-stab acb-stab-sci" data-stream="science"  onclick="comboMgr.setStream('science',this)"><span class="acb-stab-dot"></span>Science</button>
        <button class="acb-stab acb-stab-art" data-stream="arts"     onclick="comboMgr.setStream('arts',this)"><span class="acb-stab-dot"></span>Arts</button>
        <button class="acb-stab acb-stab-bus" data-stream="business" onclick="comboMgr.setStream('business',this)"><span class="acb-stab-dot"></span>Business</button>
        <button class="acb-stab acb-stab-gen" data-stream="general"  onclick="comboMgr.setStream('general',this)"><span class="acb-stab-dot"></span>General</button>
    </div>
    <div class="acb-search">
        <i class="bi bi-search acb-search-ico"></i>
        <input id="comboSearch" placeholder="Search combinations…" oninput="comboMgr.search(this.value)" autocomplete="off">
    </div>
    <button class="acb-add-btn" onclick="comboMgr.openCreate()"><i class="bi bi-plus-lg"></i> New Combination</button>
</div>

<!-- CARDS -->
<div class="acb-cards-wrap" id="combosContainer">
    <!-- Skeleton loaders -->
    <div class="acb-grid">
        <?php for($i=0;$i<4;$i++): ?>
        <div class="acb-card"><div style="display:flex;gap:.75rem;padding-left:.3rem">
            <div class="acb-skeleton" style="width:52px;height:52px;border-radius:14px;flex-shrink:0"></div>
            <div style="flex:1"><div class="acb-skeleton" style="width:70%;height:13px;margin-bottom:.5rem"></div><div class="acb-skeleton" style="width:90%;height:10px"></div></div>
        </div></div>
        <?php endfor; ?>
    </div>
</div>

</div>

<!-- ══════ MODAL ══════ -->
<div class="modal fade" id="comboModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
    <div class="modal-header">
        <h6 class="modal-title" id="comboModalTitle"><i class="bi bi-plus-circle-fill me-2"></i>New Combination</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <input type="hidden" id="comboId">
        <div class="row g-3">
            <div class="col-md-4">
                <label>Code *</label>
                <input class="form-control text-uppercase fw-bold" id="comboCo" placeholder="PCM" maxlength="10" oninput="this.value=this.value.toUpperCase()">
            </div>
            <div class="col-md-8">
                <label>Combination Name *</label>
                <input class="form-control" id="comboNm" placeholder="Physics, Chemistry & Mathematics" maxlength="200">
            </div>
            <div class="col-md-6">
                <label>Stream *</label>
                <select class="form-select" id="comboSt">
                    <option value="science">⚡ Science</option>
                    <option value="arts">🎨 Arts</option>
                    <option value="business">💼 Business</option>
                    <option value="general">📚 General</option>
                </select>
            </div>
            <div class="col-md-6">
                <label>Subjects (comma-separated)</label>
                <input class="form-control" id="comboSub" placeholder="Physics,Chemistry,Mathematics">
            </div>
            <div class="col-12">
                <label>Description</label>
                <textarea class="form-control" id="comboDe" rows="2" placeholder="Brief description of this combination…"></textarea>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
        <button class="acb-add-btn" onclick="comboMgr.save()" id="comboSaveBtn"><i class="bi bi-check-lg"></i> Save Combination</button>
    </div>
</div>
</div>
</div>

<script>
window.cbCountUp = function(el, to, duration=900) {
    if (isNaN(to)) { el.textContent = to; return; }
    const start = performance.now();
    function step(now) {
        const p = Math.min((now-start)/duration, 1);
        const e = 1-Math.pow(1-p,3);
        el.textContent = Math.round(to*e);
        if (p < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
}

window.comboMgr = (() => {
    const AJAX = 'ajax/ajax_combinations.php';
    let _modal, _all = [], _stream = '', _q = '';

    async function api(action, body={}) {
        const isGet = ['list','get'].includes(action);
        const url   = isGet ? `${AJAX}?action=${action}&${new URLSearchParams(body)}` : AJAX;
        return (await fetch(url, isGet ? {} : {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action,...body})})).json();
    }

    async function load() {
        const r = await api('list', {q:_q, stream:_stream});
        _all = r.data || [];
        updateStats(_all);
        render(_all);
    }

    function updateStats(rows) {
        const total = rows.length;
        const sci   = rows.filter(r=>r.stream_type==='science').length;
        const arts  = rows.filter(r=>r.stream_type==='arts').length;
        const bus   = rows.filter(r=>r.stream_type==='business').length;
        const gen   = rows.filter(r=>r.stream_type==='general').length;
        const act   = rows.filter(r=>r.status==='active').length;
        cbCountUp(document.getElementById('cStatTotal'),  total);
        cbCountUp(document.getElementById('cStatSci'),    sci);
        cbCountUp(document.getElementById('cStatArts'),   arts);
        cbCountUp(document.getElementById('cStatBus'),    bus);
        cbCountUp(document.getElementById('cStatActive'), act);
        // segment bars
        const pct = n => total ? Math.round(n/total*100)+'%' : '0%';
        setTimeout(()=>{
            document.getElementById('segSci').style.width    = pct(sci);
            document.getElementById('segArts').style.width   = pct(arts);
            document.getElementById('segBus').style.width    = pct(bus);
            document.getElementById('segActive').style.width = total ? Math.round(act/total*100)+'%' : '0%';
        }, 200);
        // hero pills
        document.getElementById('hp-sci').textContent = sci  + ' Science';
        document.getElementById('hp-art').textContent = arts + ' Arts';
        document.getElementById('hp-bus').textContent = bus  + ' Business';
        document.getElementById('hp-gen').textContent = gen  + ' General';
    }

    const STREAM_META = {
        science:  {label:'Science',   icon:'bi-lightning-fill',  cls:'sci'},
        arts:     {label:'Arts',      icon:'bi-palette-fill',    cls:'art'},
        business: {label:'Business',  icon:'bi-briefcase-fill',  cls:'bus'},
        general:  {label:'General',   icon:'bi-book-fill',       cls:'gen'},
    };

    function render(rows) {
        const wrap = document.getElementById('combosContainer');
        if (!rows.length) {
            wrap.innerHTML = `<div class="acb-empty"><div class="acb-empty-icon"><i class="bi bi-diagram-3"></i></div><div class="fw-bold">No combinations found</div><div style="font-size:.8rem;margin-top:.3rem">Adjust your filter or create a new combination</div></div>`;
            return;
        }
        // Group by stream
        const grouped = {};
        const ORDER   = ['science','arts','business','general'];
        rows.forEach(r => {
            const s = r.stream_type || 'general';
            if (!grouped[s]) grouped[s] = [];
            grouped[s].push(r);
        });
        let html = '';
        ORDER.forEach(stream => {
            if (!grouped[stream]) return;
            const m = STREAM_META[stream] || STREAM_META.general;
            let delay = 0;
            html += `<div class="acb-stream-section">
                <div class="acb-section-hdr acb-section-hdr-${m.cls}">
                    <div class="acb-section-icon acb-section-icon-${m.cls}"><i class="bi ${m.icon}"></i></div>
                    <div class="acb-section-title">${m.label} Stream</div>
                    <span class="acb-section-count acb-section-count-${m.cls}">${grouped[stream].length} combination${grouped[stream].length===1?'':'s'}</span>
                </div>
                <div class="acb-grid">
                ${grouped[stream].map((r,i) => {
                    const active   = r.status === 'active';
                    const subjects = (r.subjects||'').split(',').map(s=>s.trim()).filter(Boolean);
                    return `<div class="acb-card acb-card-${m.cls} ${active?'':'acb-card-inactive'}" style="animation-delay:${(delay++)*0.06}s">
                        <div class="acb-card-top">
                            <div class="acb-code-badge acb-code-${m.cls}">${esc(r.combination_code)}</div>
                            <div>
                                <div class="acb-card-name">${esc(r.combination_name)}</div>
                                <div class="acb-card-desc">${esc(r.description||'')}</div>
                            </div>
                        </div>
                        ${subjects.length ? `<div class="acb-subjects-wrap">${subjects.map(s=>`<span class="acb-subj-chip">${esc(s)}</span>`).join('')}</div>` : ''}
                        <div class="acb-card-footer">
                            <div class="acb-card-status">
                                <span class="acb-sdot acb-sdot-${active?'active':'inactive'}"></span>
                                ${active ? 'Active' : 'Inactive'}
                            </div>
                            <div class="acb-card-actions">
                                <button class="acb-btn-toggle ${active?'acb-btn-toggle-on':'acb-btn-toggle-off'}" onclick="comboMgr.toggleStatus(${r.combination_id})">
                                    ${active ? 'Deactivate' : 'Activate'}
                                </button>
                                <button class="acb-card-btn acb-btn-edit" onclick="comboMgr.openEdit(${r.combination_id})" title="Edit"><i class="bi bi-pencil-fill"></i></button>
                                <button class="acb-card-btn acb-btn-del"  onclick="comboMgr.del(${r.combination_id},'${esc(r.combination_name)}')" title="Delete"><i class="bi bi-trash-fill"></i></button>
                            </div>
                        </div>
                    </div>`;
                }).join('')}
                </div>
            </div>`;
        });
        wrap.innerHTML = html;
    }

    function esc(s) { return String(s||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

    function setStream(s, btn) {
        _stream = s;
        document.querySelectorAll('.acb-stab').forEach(b => b.classList.remove('active'));
        // Match both toolbar tabs and KPI card clicks
        if (btn && btn.classList.contains('acb-stab')) btn.classList.add('active');
        else {
            const tab = document.querySelector(`.acb-stab[data-stream="${s}"]`);
            if (tab) tab.classList.add('active');
        }
        load();
    }

    function search(q) {
        _q = q;
        const lq = q.toLowerCase();
        const filtered = _all.filter(r =>
            (r.combination_name||'').toLowerCase().includes(lq) ||
            (r.combination_code||'').toLowerCase().includes(lq) ||
            (r.subjects||'').toLowerCase().includes(lq)
        );
        render(filtered);
    }

    function filterStream() { load(); }

    function openCreate() {
        ['comboId','comboCo','comboNm','comboSub','comboDe'].forEach(id=>document.getElementById(id).value='');
        document.getElementById('comboSt').value='science';
        document.getElementById('comboModalTitle').innerHTML='<i class="bi bi-plus-circle-fill me-2"></i>New Combination';
        getModal().show();
    }

    async function openEdit(id) {
        const r = await api('get', {id});
        if (r.status !== 'success') return;
        const d = r.data;
        document.getElementById('comboId').value  = d.combination_id;
        document.getElementById('comboCo').value  = d.combination_code;
        document.getElementById('comboNm').value  = d.combination_name;
        document.getElementById('comboSt').value  = d.stream_type;
        document.getElementById('comboSub').value = d.subjects||'';
        document.getElementById('comboDe').value  = d.description||'';
        document.getElementById('comboModalTitle').innerHTML='<i class="bi bi-pencil-fill me-2"></i>Edit Combination';
        getModal().show();
    }

    async function save() {
        const id   = document.getElementById('comboId').value;
        const code = document.getElementById('comboCo').value.trim();
        const name = document.getElementById('comboNm').value.trim();
        if (!code||!name) { Swal.fire({icon:'warning',title:'Required',text:'Code and name are required',timer:2200,showConfirmButton:false}); return; }
        const btn = document.getElementById('comboSaveBtn');
        btn.disabled=true; btn.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>Saving…';
        const body = {combination_code:code,combination_name:name,stream_type:document.getElementById('comboSt').value,subjects:document.getElementById('comboSub').value,description:document.getElementById('comboDe').value};
        const res  = await api(id?'update':'create', id?{...body,id}:body);
        btn.disabled=false; btn.innerHTML='<i class="bi bi-check-lg"></i> Save Combination';
        if (res.status==='success') { Swal.fire({icon:'success',title:'Saved!',timer:1500,showConfirmButton:false}); getModal().hide(); load(); }
        else Swal.fire({icon:'error',title:'Error',text:res.message});
    }

    async function toggleStatus(id) { await api('toggle_status',{id}); load(); }

    async function del(id,name) {
        const c = await Swal.fire({icon:'warning',title:'Delete Combination?',html:`<p>Delete <strong>"${name}"</strong>?<br>This cannot be undone.</p>`,showCancelButton:true,confirmButtonColor:'#ef4444',confirmButtonText:'Yes, Delete'});
        if (!c.isConfirmed) return;
        const r = await api('delete',{id});
        if (r.status==='success') { Swal.fire({icon:'success',title:'Deleted',timer:1500,showConfirmButton:false}); load(); }
        else Swal.fire({icon:'error',title:'Cannot Delete',text:r.message});
    }

    function getModal() { if(!_modal)_modal=new bootstrap.Modal(document.getElementById('comboModal')); return _modal; }
    load();
    return {load,search,filterStream,setStream,openCreate,openEdit,save,toggleStatus,del};
})();
</script>
