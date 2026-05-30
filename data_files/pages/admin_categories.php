<?php
if (($user_role ?? 0) != 5) { include('403.php'); return; }
$_me = $_SESSION['usr_code'] ?? '';
?>
<style>
/* ═══ HERO ═══════════════════════════════════════════════════════ */
.acat-hero{position:relative;overflow:hidden;border-radius:20px;background:linear-gradient(135deg,#0f0c29 0%,#302b63 45%,#24243e 100%);padding:2rem 2rem 1.75rem;margin:0 1rem 1.5rem;color:#fff}
.acat-hero-orb{position:absolute;border-radius:50%;filter:blur(50px);pointer-events:none}
.acat-hero-orb-1{width:220px;height:220px;background:rgba(99,102,241,.35);top:-60px;right:-40px;animation:acOrb1 6s ease-in-out infinite alternate}
.acat-hero-orb-2{width:140px;height:140px;background:rgba(139,92,246,.3);bottom:-40px;right:160px;animation:acOrb2 8s ease-in-out infinite alternate}
.acat-hero-orb-3{width:100px;height:100px;background:rgba(236,72,153,.25);top:20px;left:55%;animation:acOrb3 7s ease-in-out infinite alternate}
@keyframes acOrb1{from{transform:translate(0,0) scale(1)} to{transform:translate(20px,-15px) scale(1.15)}}
@keyframes acOrb2{from{transform:translate(0,0) scale(1)} to{transform:translate(-15px,20px) scale(1.2)}}
@keyframes acOrb3{from{transform:translate(0,0) scale(1)} to{transform:translate(15px,-10px) scale(.9)}}
.acat-hero-content{position:relative;z-index:2;display:flex;align-items:center;gap:1.25rem}
.acat-hero-icon{width:64px;height:64px;border-radius:18px;background:rgba(255,255,255,.12);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;font-size:1.8rem;flex-shrink:0;border:1px solid rgba(255,255,255,.18);box-shadow:0 8px 32px rgba(99,102,241,.4);animation:acIconPop .6s cubic-bezier(.34,1.56,.64,1) both}
@keyframes acIconPop{from{transform:scale(0) rotate(-15deg);opacity:0} to{transform:scale(1) rotate(0);opacity:1}}
.acat-hero-title{font-size:1.45rem;font-weight:800;line-height:1.1;letter-spacing:-.02em}
.acat-hero-title span{background:linear-gradient(90deg,#a78bfa,#f9a8d4,#93c5fd);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.acat-hero-sub{font-size:.82rem;opacity:.6;margin-top:.2rem}
.acat-hero-pills{display:flex;gap:.5rem;margin-top:.9rem;flex-wrap:wrap}
.acat-hero-pill{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);backdrop-filter:blur(6px);color:#fff;font-size:.72rem;font-weight:700;padding:.25rem .75rem;border-radius:20px;display:flex;align-items:center;gap:.35rem}
.acat-hero-pill i{font-size:.8rem}
.acat-hero-actions{margin-left:auto;display:flex;gap:.6rem;flex-shrink:0}
.acat-hero-btn{padding:.55rem 1.2rem;border-radius:12px;font-size:.82rem;font-weight:700;cursor:pointer;transition:all .2s;border:none;display:flex;align-items:center;gap:.4rem}
.acat-hero-btn-primary{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;box-shadow:0 4px 18px rgba(99,102,241,.5)}
.acat-hero-btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(99,102,241,.65)}
.acat-hero-btn-secondary{background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.2)}
.acat-hero-btn-secondary:hover{background:rgba(255,255,255,.2)}

/* ═══ KPI CARDS ═══════════════════════════════════════════════════ */
.acat-kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin:0 1rem 1.5rem}
@media(max-width:991px){.acat-kpi-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:575px){.acat-kpi-grid{grid-template-columns:repeat(2,1fr)}}
.acat-kpi{background:#fff;border-radius:18px;padding:1.25rem 1.35rem;position:relative;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,.06);transition:transform .25s,box-shadow .25s;animation:acKpiIn .5s cubic-bezier(.34,1.56,.64,1) both}
.acat-kpi:hover{transform:translateY(-5px);box-shadow:0 12px 32px rgba(0,0,0,.1)}
.acat-kpi:nth-child(1){animation-delay:.05s}.acat-kpi:nth-child(2){animation-delay:.1s}.acat-kpi:nth-child(3){animation-delay:.15s}.acat-kpi:nth-child(4){animation-delay:.2s}
@keyframes acKpiIn{from{opacity:0;transform:translateY(24px) scale(.95)} to{opacity:1;transform:none}}
.acat-kpi-bg{position:absolute;right:-20px;bottom:-20px;font-size:5rem;opacity:.07;line-height:1}
.acat-kpi-icon{width:46px;height:46px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;margin-bottom:.85rem;position:relative;z-index:1}
.acat-kpi-label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:.2rem;position:relative;z-index:1}
.acat-kpi-value{font-size:2rem;font-weight:900;line-height:1;position:relative;z-index:1;font-variant-numeric:tabular-nums}
.acat-kpi-sub{font-size:.72rem;color:#94a3b8;margin-top:.3rem;position:relative;z-index:1}
.acat-kpi-bar{height:3px;border-radius:99px;margin-top:.9rem;background:#f1f5f9;overflow:hidden;position:relative;z-index:1}
.acat-kpi-bar-fill{height:100%;border-radius:99px;transition:width 1s cubic-bezier(.4,0,.2,1);width:0}

/* KPI colour themes */
.acat-kpi-1 .acat-kpi-icon{background:linear-gradient(135deg,#ede9fe,#ddd6fe);color:#6366f1}
.acat-kpi-1 .acat-kpi-value{color:#4f46e5}
.acat-kpi-1 .acat-kpi-bar-fill{background:linear-gradient(90deg,#6366f1,#8b5cf6)}
.acat-kpi-2 .acat-kpi-icon{background:linear-gradient(135deg,#d1fae5,#a7f3d0);color:#059669}
.acat-kpi-2 .acat-kpi-value{color:#059669}
.acat-kpi-2 .acat-kpi-bar-fill{background:linear-gradient(90deg,#10b981,#34d399)}
.acat-kpi-3 .acat-kpi-icon{background:linear-gradient(135deg,#fef3c7,#fde68a);color:#d97706}
.acat-kpi-3 .acat-kpi-value{color:#d97706}
.acat-kpi-3 .acat-kpi-bar-fill{background:linear-gradient(90deg,#f59e0b,#fbbf24)}
.acat-kpi-4 .acat-kpi-icon{background:linear-gradient(135deg,#fce7f3,#fbcfe8);color:#db2777}
.acat-kpi-4 .acat-kpi-value{color:#db2777}
.acat-kpi-4 .acat-kpi-bar-fill{background:linear-gradient(90deg,#ec4899,#f9a8d4)}

/* ═══ TOOLBAR ══════════════════════════════════════════════════════ */
.acat-toolbar{background:#fff;border-radius:16px;padding:.85rem 1.2rem;margin:0 1rem 1rem;box-shadow:0 2px 12px rgba(0,0,0,.05);display:flex;align-items:center;gap:.75rem;flex-wrap:wrap}
.acat-search{flex:1;min-width:200px;max-width:300px;position:relative}
.acat-search input{width:100%;border:1.5px solid #e0e7ff;border-radius:12px;padding:.5rem .85rem .5rem 2.2rem;font-size:.84rem;transition:all .2s;background:#f8f7ff}
.acat-search input:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1);background:#fff}
.acat-search-icon{position:absolute;left:.7rem;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:.9rem}
.acat-filter-tabs{display:flex;gap:.3rem;background:#f1f5f9;border-radius:10px;padding:.2rem}
.acat-filter-tab{padding:.3rem .85rem;border-radius:8px;font-size:.78rem;font-weight:600;cursor:pointer;border:none;background:transparent;color:#64748b;transition:all .18s;white-space:nowrap}
.acat-filter-tab.active{background:#fff;color:#6366f1;box-shadow:0 1px 6px rgba(0,0,0,.08)}
.acat-view-toggle{display:flex;gap:.3rem;background:#f1f5f9;border-radius:10px;padding:.2rem;margin-left:auto}
.acat-view-btn{width:32px;height:32px;border-radius:7px;display:flex;align-items:center;justify-content:center;cursor:pointer;border:none;background:transparent;color:#94a3b8;transition:all .18s;font-size:.9rem}
.acat-view-btn.active{background:#fff;color:#6366f1;box-shadow:0 1px 6px rgba(0,0,0,.08)}
.acat-add-btn{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:12px;padding:.5rem 1.1rem;font-size:.83rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.4rem;transition:all .2s;box-shadow:0 4px 14px rgba(99,102,241,.35);white-space:nowrap}
.acat-add-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(99,102,241,.5)}

/* ═══ TABLE ════════════════════════════════════════════════════════ */
.acat-table-wrap{background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,.06);margin:0 1rem 1.5rem}
.acat-table{width:100%;border-collapse:collapse}
.acat-table thead th{background:linear-gradient(135deg,#f8f7ff,#f1f5f9);padding:.75rem 1rem;font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#64748b;border-bottom:2px solid #e0e7ff;white-space:nowrap}
.acat-table thead th:first-child{padding-left:1.5rem}
.acat-table thead th:last-child{padding-right:1.5rem;text-align:right}
.acat-table tbody tr{border-bottom:1px solid #f1f5f9;transition:background .15s,transform .15s;animation:acRowIn .35s ease both}
.acat-table tbody tr:last-child{border-bottom:none}
.acat-table tbody tr:hover{background:#f8f7ff}
@keyframes acRowIn{from{opacity:0;transform:translateX(-8px)} to{opacity:1;transform:none}}
.acat-table td{padding:.8rem 1rem;vertical-align:middle}
.acat-table td:first-child{padding-left:1.5rem}
.acat-table td:last-child{padding-right:1.5rem}
.acat-cat-icon-cell{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;transition:transform .2s}
tr:hover .acat-cat-icon-cell{transform:scale(1.12) rotate(-5deg)}
.acat-cat-name{font-weight:700;font-size:.87rem;color:#1e1b4b;line-height:1.2}
.acat-cat-desc{font-size:.72rem;color:#94a3b8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;margin-top:1px}
.acat-code-chip{display:inline-flex;align-items:center;background:linear-gradient(135deg,#ede9fe,#e0e7ff);color:#4338ca;font-size:.68rem;font-weight:800;padding:3px 10px;border-radius:20px;letter-spacing:.04em}
.acat-count-pill{display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:24px;background:#f1f5f9;color:#475569;font-size:.72rem;font-weight:700;border-radius:20px;padding:0 8px}
.acat-count-pill.has-courses{background:linear-gradient(135deg,#ede9fe,#e0e7ff);color:#6366f1}
.acat-status-btn{border:none;border-radius:20px;padding:3px 12px;font-size:.71rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:.3rem;transition:all .2s}
.acat-status-btn.active{background:#d1fae5;color:#065f46}
.acat-status-btn.active:hover{background:#059669;color:#fff}
.acat-status-btn.inactive{background:#fee2e2;color:#991b1b}
.acat-status-btn.inactive:hover{background:#dc2626;color:#fff}
.acat-action-btn{width:32px;height:32px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;border:1.5px solid;cursor:pointer;font-size:.82rem;transition:all .2s}
.acat-action-btn:hover{transform:scale(1.15)}
.acat-action-edit{color:#6366f1;border-color:#e0e7ff;background:#f8f7ff}
.acat-action-edit:hover{background:#6366f1;color:#fff;border-color:#6366f1}
.acat-action-del{color:#ef4444;border-color:#fee2e2;background:#fff5f5}
.acat-action-del:hover{background:#ef4444;color:#fff;border-color:#ef4444}

/* ═══ GRID VIEW ════════════════════════════════════════════════════ */
.acat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;margin:0 1rem 1.5rem}
.acat-grid-card{background:#fff;border-radius:16px;padding:1.25rem;box-shadow:0 2px 12px rgba(0,0,0,.06);border:2px solid transparent;transition:all .25s;cursor:pointer;animation:acKpiIn .4s ease both;position:relative;overflow:hidden}
.acat-grid-card::before{content:'';position:absolute;inset:0;border-radius:14px;background:linear-gradient(135deg,transparent,rgba(99,102,241,.04));opacity:0;transition:opacity .2s;pointer-events:none}
.acat-grid-card:hover{transform:translateY(-4px);box-shadow:0 12px 28px rgba(0,0,0,.1);border-color:rgba(99,102,241,.2)}
.acat-grid-card:hover::before{opacity:1}
.acat-grid-icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;margin-bottom:.85rem;transition:transform .2s}
.acat-grid-card:hover .acat-grid-icon{transform:scale(1.1) rotate(-8deg)}
.acat-grid-name{font-weight:700;font-size:.85rem;color:#1e1b4b;margin-bottom:.2rem}
.acat-grid-code{font-size:.67rem;font-weight:800;color:#a5b4fc;letter-spacing:.06em;text-transform:uppercase}
.acat-grid-count{font-size:.7rem;color:#94a3b8;margin-top:.5rem}
.acat-grid-count strong{color:#6366f1}
.acat-grid-actions{display:flex;gap:.4rem;margin-top:.85rem;position:relative;z-index:2}
.acat-grid-inactive{opacity:.55;filter:grayscale(.3)}
.acat-status-dot{width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:.3rem}
.acat-status-dot.active{background:#22c55e;box-shadow:0 0 0 2px rgba(34,197,94,.25)}
.acat-status-dot.inactive{background:#ef4444}

/* ═══ EMPTY / LOADING ═════════════════════════════════════════════ */
.acat-empty{padding:4rem 2rem;text-align:center;color:#94a3b8}
.acat-empty-icon{width:72px;height:72px;border-radius:20px;background:linear-gradient(135deg,#ede9fe,#e0e7ff);display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:#6366f1;margin:0 auto 1rem}
.acat-skeleton{background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:acSkel 1.4s infinite;border-radius:8px}
@keyframes acSkel{0%{background-position:200% 0} 100%{background-position:-200% 0}}

/* ═══ MODAL ════════════════════════════════════════════════════════ */
#catModal .modal-content{border:none;border-radius:20px;box-shadow:0 24px 80px rgba(0,0,0,.18);overflow:hidden}
#catModal .modal-header{background:linear-gradient(135deg,#0f0c29,#302b63);padding:1.25rem 1.5rem;border:none}
#catModal .modal-title{color:#fff;font-weight:800;font-size:.95rem}
#catModal .btn-close{filter:invert(1);opacity:.7}
#catModal .modal-body{padding:1.5rem;background:#fafbff}
#catModal .modal-footer{background:#f8f7ff;border:none;padding:1rem 1.5rem}
#catModal .form-control,#catModal .form-select{border-radius:10px;border:1.5px solid #e0e7ff;font-size:.85rem;transition:all .2s;background:#fff}
#catModal .form-control:focus,#catModal .form-select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
#catModal label{font-size:.75rem;font-weight:700;color:#475569;letter-spacing:.02em;text-transform:uppercase;margin-bottom:.3rem}
.acat-icon-picker{display:flex;flex-wrap:wrap;gap:.35rem;max-height:130px;overflow-y:auto;background:#f8f7ff;border-radius:10px;padding:.6rem;border:1.5px solid #e0e7ff}
.acat-icon-opt{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;border:1.5px solid transparent;font-size:1rem;transition:all .15s;color:#64748b;background:#fff}
.acat-icon-opt:hover{border-color:#a5b4fc;color:#6366f1;transform:scale(1.12)}
.acat-icon-opt.selected{border-color:#6366f1;background:#ede9fe;color:#6366f1}

/* Number count-up */
.acat-kpi-value{transition:all .3s}
</style>

<?php
/* ── Hero ── */
?>
<div class="container-fluid px-0">

<!-- HERO BANNER -->
<div class="acat-hero mx-3 mt-3 mb-0">
    <div class="acat-hero-orb acat-hero-orb-1"></div>
    <div class="acat-hero-orb acat-hero-orb-2"></div>
    <div class="acat-hero-orb acat-hero-orb-3"></div>
    <div class="acat-hero-content">
        <div class="acat-hero-icon"><i class="bi bi-grid-3x3-gap-fill"></i></div>
        <div>
            <div class="acat-hero-title">Course <span>Categories</span></div>
            <div class="acat-hero-sub">Organise every course into the right academic or professional category</div>
            <div class="acat-hero-pills">
                <span class="acat-hero-pill"><i class="bi bi-grid"></i><span id="hp-total">— categories</span></span>
                <span class="acat-hero-pill"><i class="bi bi-check-circle-fill" style="color:#4ade80"></i><span id="hp-active">— active</span></span>
                <span class="acat-hero-pill"><i class="bi bi-collection-play-fill" style="color:#fbbf24"></i><span id="hp-courses">— courses assigned</span></span>
            </div>
        </div>
        <div class="acat-hero-actions d-none d-md-flex">
            <button class="acat-hero-btn acat-hero-btn-secondary" onclick="catMgr.load()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
            <button class="acat-hero-btn acat-hero-btn-primary" onclick="catMgr.openCreate()"><i class="bi bi-plus-lg"></i> New Category</button>
        </div>
    </div>
</div>

<!-- KPI CARDS -->
<div class="acat-kpi-grid mt-3 px-3">
    <div class="acat-kpi acat-kpi-1">
        <div class="acat-kpi-bg"><i class="bi bi-grid-3x3-gap"></i></div>
        <div class="acat-kpi-icon"><i class="bi bi-grid-3x3-gap-fill"></i></div>
        <div class="acat-kpi-label">Total Categories</div>
        <div class="acat-kpi-value" id="statTotal">—</div>
        <div class="acat-kpi-sub">All academic & professional</div>
        <div class="acat-kpi-bar"><div class="acat-kpi-bar-fill" id="barTotal" style="width:100%"></div></div>
    </div>
    <div class="acat-kpi acat-kpi-2">
        <div class="acat-kpi-bg"><i class="bi bi-check-circle"></i></div>
        <div class="acat-kpi-icon"><i class="bi bi-check-circle-fill"></i></div>
        <div class="acat-kpi-label">Active</div>
        <div class="acat-kpi-value" id="statActive">—</div>
        <div class="acat-kpi-sub">Visible to students</div>
        <div class="acat-kpi-bar"><div class="acat-kpi-bar-fill" id="barActive"></div></div>
    </div>
    <div class="acat-kpi acat-kpi-3">
        <div class="acat-kpi-bg"><i class="bi bi-collection-play"></i></div>
        <div class="acat-kpi-icon"><i class="bi bi-collection-play-fill"></i></div>
        <div class="acat-kpi-label">Courses Categorised</div>
        <div class="acat-kpi-value" id="statCourses">—</div>
        <div class="acat-kpi-sub">Courses with a category set</div>
        <div class="acat-kpi-bar"><div class="acat-kpi-bar-fill" id="barCourses" style="width:72%"></div></div>
    </div>
    <div class="acat-kpi acat-kpi-4">
        <div class="acat-kpi-bg"><i class="bi bi-trophy"></i></div>
        <div class="acat-kpi-icon"><i class="bi bi-trophy-fill"></i></div>
        <div class="acat-kpi-label">Top Category</div>
        <div class="acat-kpi-value" style="font-size:1rem;padding-top:.2rem" id="statTop">—</div>
        <div class="acat-kpi-sub">Most courses assigned</div>
        <div class="acat-kpi-bar"><div class="acat-kpi-bar-fill" style="width:100%"></div></div>
    </div>
</div>

<!-- TOOLBAR -->
<div class="acat-toolbar">
    <div class="acat-search">
        <i class="bi bi-search acat-search-icon"></i>
        <input id="catSearch" placeholder="Search categories…" oninput="catMgr.search(this.value)" autocomplete="off">
    </div>
    <div class="acat-filter-tabs" id="catFilterTabs">
        <button class="acat-filter-tab active" data-f="all"      onclick="catMgr.filterTab('all',this)">All</button>
        <button class="acat-filter-tab"         data-f="active"   onclick="catMgr.filterTab('active',this)">Active</button>
        <button class="acat-filter-tab"         data-f="inactive" onclick="catMgr.filterTab('inactive',this)">Inactive</button>
    </div>
    <div class="acat-view-toggle ms-auto" id="viewToggle">
        <button class="acat-view-btn active" id="vbTable" onclick="catMgr.setView('table',this)" title="Table view"><i class="bi bi-table"></i></button>
        <button class="acat-view-btn"        id="vbGrid"  onclick="catMgr.setView('grid',this)"  title="Grid view"><i class="bi bi-grid-3x3-gap"></i></button>
    </div>
    <button class="acat-add-btn d-md-none" onclick="catMgr.openCreate()"><i class="bi bi-plus-lg"></i> New</button>
</div>

<!-- TABLE VIEW -->
<div id="catTableView" class="acat-table-wrap">
    <div class="table-responsive">
    <table class="acat-table" id="catTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Category</th>
                <th>Code</th>
                <th class="text-center">Courses</th>
                <th class="text-center">Status</th>
                <th class="text-center">Order</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="catBody">
            <tr><td colspan="7" class="acat-empty">
                <div style="display:flex;gap:.5rem;flex-direction:column;align-items:center;padding:2rem 0">
                    <div class="acat-skeleton" style="width:40px;height:40px;border-radius:10px"></div>
                    <div class="acat-skeleton" style="width:160px;height:12px;margin-top:.5rem"></div>
                    <div class="acat-skeleton" style="width:120px;height:10px"></div>
                </div>
            </td></tr>
        </tbody>
    </table>
    </div>
</div>

<!-- GRID VIEW (hidden by default) -->
<div id="catGridView" class="acat-grid d-none" style="padding-bottom:1.5rem"></div>

</div><!-- /.container-fluid -->

<!-- ══════ MODAL ══════ -->
<div class="modal fade" id="catModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-lg">
<div class="modal-content">
    <div class="modal-header">
        <h6 class="modal-title" id="catModalTitle"><i class="bi bi-grid-3x3-gap-fill me-2"></i>New Category</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <input type="hidden" id="catId">
        <div class="row g-3">
            <div class="col-12">
                <label>Category Name *</label>
                <input class="form-control" id="catName" placeholder="e.g. Web Development" maxlength="200">
            </div>
            <div class="col-md-4">
                <label>Category Code *</label>
                <input class="form-control text-uppercase fw-bold" id="catCode" placeholder="e.g. WEB" maxlength="20" oninput="this.value=this.value.toUpperCase()">
                <div class="form-text" style="font-size:.7rem">Unique short identifier</div>
            </div>
            <div class="col-md-8">
                <label>Icon</label>
                <div class="input-group mb-2">
                    <span class="input-group-text" style="width:46px;justify-content:center;font-size:1.15rem"><i id="iconPreviewI" class="bi bi-grid"></i></span>
                    <input class="form-control" id="catIcon" placeholder="bi-grid" value="bi-grid" oninput="catMgr.previewIcon(this.value)">
                </div>
                <div class="acat-icon-picker" id="iconPicker"></div>
            </div>
            <div class="col-12">
                <label>Description</label>
                <textarea class="form-control" id="catDesc" rows="2" placeholder="Brief description of this category…" maxlength="500"></textarea>
            </div>
            <div class="col-md-4">
                <label>Sort Order</label>
                <input type="number" class="form-control" id="catOrder" value="0" min="0">
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="acat-add-btn" onclick="catMgr.save()" id="catSaveBtn"><i class="bi bi-check-lg"></i> Save Category</button>
    </div>
</div>
</div>
</div>

<script>
/* ── Suggested icons ── */
window.ICON_SUGGESTIONS = [
    'bi-laptop','bi-cpu','bi-code-slash','bi-globe2','bi-phone','bi-robot','bi-bar-chart-line',
    'bi-shield-lock','bi-diagram-3','bi-calculator','bi-lightning','bi-droplet','bi-flower1',
    'bi-briefcase','bi-rocket-takeoff','bi-receipt','bi-graph-up','bi-palette','bi-book',
    'bi-translate','bi-moon-stars','bi-award','bi-tools','bi-star','bi-mortarboard','bi-people',
    'bi-camera','bi-music-note-beamed','bi-brush','bi-building','bi-gear','bi-cloud',
];

window.COLORS = ['#6366f1','#10b981','#f59e0b','#3b82f6','#8b5cf6','#ec4899','#14b8a6',
                 '#f97316','#84cc16','#06b6d4','#a855f7','#ef4444','#6366f1','#10b981'];

/* ── Count-up animation ── */
window.countUp = function countUp(el, to, duration=900) {
    if (isNaN(to)) { el.textContent = to; return; }
    const start = performance.now();
    const from  = 0;
    function step(now) {
        const p = Math.min((now-start)/duration, 1);
        const ease = 1-Math.pow(1-p, 3);
        el.textContent = Math.round(from + (to-from)*ease);
        if (p < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
}

window.catMgr = (() => {
    const AJAX = 'ajax/ajax_categories.php';
    let _modal, _allRows = [], _filter = 'all', _view = 'table';

    async function api(action, body={}) {
        const isGet = ['list','stats','get'].includes(action);
        const url   = isGet ? `${AJAX}?action=${action}&${new URLSearchParams(body)}` : AJAX;
        const opts  = isGet ? {} : {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action,...body})};
        return (await fetch(url, opts)).json();
    }

    async function load(q='') {
        const res = await api('list', {q});
        _allRows  = res.data || [];
        applyFilter();
        loadStats();
    }

    async function loadStats() {
        const r = await api('stats');
        if (r.status !== 'success') return;
        const d = r.data;
        const total  = parseInt(d.total)  || 0;
        const active = parseInt(d.active) || 0;
        countUp(document.getElementById('statTotal'),   total);
        countUp(document.getElementById('statActive'),  active);
        countUp(document.getElementById('statCourses'), parseInt(d.categorised_courses)||0);
        document.getElementById('statTop').textContent = d.top_category || '—';
        // progress bars
        document.getElementById('barActive').style.width  = total ? Math.round(active/total*100)+'%' : '0%';
        // hero pills
        document.getElementById('hp-total').textContent   = total + ' categories';
        document.getElementById('hp-active').textContent  = active + ' active';
        document.getElementById('hp-courses').textContent = (d.categorised_courses||0) + ' courses assigned';
    }

    function applyFilter() {
        let rows = _allRows;
        if (_filter === 'active')   rows = rows.filter(r => r.status == 1);
        if (_filter === 'inactive') rows = rows.filter(r => r.status != 1);
        render(rows);
    }

    function render(rows) {
        if (_view === 'grid') renderGrid(rows);
        else renderTable(rows);
    }

    function renderTable(rows) {
        const tb = document.getElementById('catBody');
        if (!rows.length) {
            tb.innerHTML = `<tr><td colspan="7"><div class="acat-empty">
                <div class="acat-empty-icon"><i class="bi bi-grid"></i></div>
                <div class="fw-bold">No categories found</div>
                <div style="font-size:.8rem;margin-top:.3rem">Try a different filter or create your first category</div>
            </div></td></tr>`;
            return;
        }
        tb.innerHTML = rows.map((r, i) => {
            const active  = r.status == 1;
            const color   = COLORS[i % COLORS.length];
            const iconCls = r.icon || 'bi-grid';
            return `<tr style="animation-delay:${i*.04}s">
                <td class="text-muted" style="font-size:.75rem;font-weight:700;color:#c4c9d4">${String(i+1).padStart(2,'0')}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:.85rem">
                        <div class="acat-cat-icon-cell" style="background:${color}18;color:${color}"><i class="bi ${iconCls}"></i></div>
                        <div>
                            <div class="acat-cat-name">${esc(r.category_title)}</div>
                            <div class="acat-cat-desc">${esc(r.category_description||'No description')}</div>
                        </div>
                    </div>
                </td>
                <td><span class="acat-code-chip">${esc(r.category_code||'—')}</span></td>
                <td class="text-center"><span class="acat-count-pill ${r.course_count>0?'has-courses':''}">${r.course_count||0}</span></td>
                <td class="text-center">
                    <button class="acat-status-btn ${active?'active':'inactive'}" onclick="catMgr.toggleStatus(${r.id},this)">
                        <span class="acat-status-dot ${active?'active':'inactive'}"></span>
                        ${active ? 'Active' : 'Inactive'}
                    </button>
                </td>
                <td class="text-center" style="font-size:.8rem;color:#94a3b8;font-weight:600">${r.sort_order||0}</td>
                <td>
                    <div style="display:flex;gap:.4rem">
                        <button class="acat-action-btn acat-action-edit" onclick="catMgr.openEdit(${r.id})" title="Edit"><i class="bi bi-pencil-fill"></i></button>
                        <button class="acat-action-btn acat-action-del"  onclick="catMgr.del(${r.id},'${esc(r.category_title)}')" title="Delete"><i class="bi bi-trash-fill"></i></button>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    function renderGrid(rows) {
        const gv = document.getElementById('catGridView');
        if (!rows.length) {
            gv.innerHTML = `<div style="grid-column:1/-1"><div class="acat-empty"><div class="acat-empty-icon"><i class="bi bi-grid"></i></div><div class="fw-bold">No categories</div></div></div>`;
            return;
        }
        gv.innerHTML = rows.map((r, i) => {
            const active  = r.status == 1;
            const color   = COLORS[i % COLORS.length];
            const iconCls = r.icon || 'bi-grid';
            return `<div class="acat-grid-card ${active?'':'acat-grid-inactive'}" style="animation-delay:${i*.04}s">
                <div class="acat-grid-icon" style="background:${color}18;color:${color}"><i class="bi ${iconCls}"></i></div>
                <div class="acat-grid-name">${esc(r.category_title)}</div>
                <div class="acat-grid-code">${esc(r.category_code||'—')}</div>
                <div class="acat-grid-count"><strong>${r.course_count||0}</strong> course${r.course_count==1?'':'s'}</div>
                <div class="acat-grid-actions">
                    <button class="acat-action-btn acat-action-edit" onclick="catMgr.openEdit(${r.id})" title="Edit"><i class="bi bi-pencil-fill"></i></button>
                    <button class="acat-action-btn acat-action-del"  onclick="catMgr.del(${r.id},'${esc(r.category_title)}')" title="Delete"><i class="bi bi-trash-fill"></i></button>
                    <button class="acat-status-btn ${active?'active':'inactive'}" style="margin-left:auto;justify-content:center" onclick="catMgr.toggleStatus(${r.id})">
                        <span class="acat-status-dot ${active?'active':'inactive'}"></span>${active?'On':'Off'}
                    </button>
                </div>
            </div>`;
        }).join('');
    }

    function setView(v, btn) {
        _view = v;
        document.querySelectorAll('.acat-view-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const tv = document.getElementById('catTableView');
        const gv = document.getElementById('catGridView');
        if (v === 'grid') { tv.classList.add('d-none'); gv.classList.remove('d-none'); }
        else              { gv.classList.add('d-none'); tv.classList.remove('d-none'); }
        applyFilter();
    }

    function filterTab(f, btn) {
        _filter = f;
        document.querySelectorAll('.acat-filter-tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        applyFilter();
    }

    function search(q) {
        const lq = q.toLowerCase();
        const filtered = _allRows.filter(r =>
            (r.category_title||'').toLowerCase().includes(lq) ||
            (r.category_code||'').toLowerCase().includes(lq) ||
            (r.category_description||'').toLowerCase().includes(lq)
        );
        render(filtered);
    }

    function esc(s) { return String(s||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

    function buildIconPicker() {
        const p = document.getElementById('iconPicker');
        if (p.innerHTML) return;
        p.innerHTML = ICON_SUGGESTIONS.map(ic =>
            `<div class="acat-icon-opt" title="${ic}" data-icon="${ic}" onclick="catMgr.pickIcon('${ic}')"><i class="bi ${ic}"></i></div>`
        ).join('');
    }

    function pickIcon(ic) {
        document.getElementById('catIcon').value = ic;
        previewIcon(ic);
        document.querySelectorAll('.acat-icon-opt').forEach(o => o.classList.toggle('selected', o.dataset.icon===ic));
    }

    function openCreate() {
        document.getElementById('catId').value   = '';
        document.getElementById('catName').value  = '';
        document.getElementById('catCode').value  = '';
        document.getElementById('catDesc').value  = '';
        document.getElementById('catIcon').value  = 'bi-grid';
        document.getElementById('catOrder').value = '0';
        document.getElementById('catModalTitle').innerHTML = '<i class="bi bi-plus-circle-fill me-2"></i>New Category';
        previewIcon('bi-grid');
        buildIconPicker();
        getModal().show();
    }

    async function openEdit(id) {
        const r = await api('get', {id});
        if (r.status !== 'success') return;
        const d = r.data;
        document.getElementById('catId').value    = d.id;
        document.getElementById('catName').value  = d.category_title   || '';
        document.getElementById('catCode').value  = d.category_code    || '';
        document.getElementById('catDesc').value  = d.category_description || '';
        document.getElementById('catIcon').value  = d.icon             || 'bi-grid';
        document.getElementById('catOrder').value = d.sort_order       || 0;
        document.getElementById('catModalTitle').innerHTML = '<i class="bi bi-pencil-fill me-2"></i>Edit Category';
        previewIcon(d.icon || 'bi-grid');
        buildIconPicker();
        document.querySelectorAll('.acat-icon-opt').forEach(o => o.classList.toggle('selected', o.dataset.icon===(d.icon||'')));
        getModal().show();
    }

    async function save() {
        const id   = document.getElementById('catId').value;
        const name = document.getElementById('catName').value.trim();
        const code = document.getElementById('catCode').value.trim();
        if (!name || !code) { Swal.fire({icon:'warning',title:'Required',text:'Name and code are required',timer:2200,showConfirmButton:false}); return; }
        const btn = document.getElementById('catSaveBtn');
        btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';
        const body = {category_title:name, category_code:code, category_description:document.getElementById('catDesc').value, icon:document.getElementById('catIcon').value, sort_order:document.getElementById('catOrder').value};
        const res  = await api(id ? 'update' : 'create', id ? {...body, id} : body);
        btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg"></i> Save Category';
        if (res.status === 'success') {
            Swal.fire({icon:'success',title:'Saved!',text:res.message,timer:1800,showConfirmButton:false});
            getModal().hide(); load();
        } else {
            Swal.fire({icon:'error',title:'Error',text:res.message});
        }
    }

    async function toggleStatus(id) {
        const res = await api('toggle_status', {id});
        if (res.status === 'success') load();
    }

    async function del(id, name) {
        const c = await Swal.fire({icon:'warning',title:'Delete Category?',html:`<p>Delete <strong>"${name}"</strong>?<br>This cannot be undone.</p>`,showCancelButton:true,confirmButtonColor:'#ef4444',confirmButtonText:'Yes, Delete',cancelButtonText:'Cancel'});
        if (!c.isConfirmed) return;
        const res = await api('delete', {id});
        if (res.status === 'success') { Swal.fire({icon:'success',title:'Deleted',timer:1500,showConfirmButton:false}); load(); }
        else Swal.fire({icon:'error',title:'Cannot Delete',text:res.message});
    }

    function previewIcon(v) {
        const el = document.getElementById('iconPreviewI');
        if (el) el.className = 'bi ' + (v || 'bi-grid');
    }

    function getModal() {
        if (!_modal) _modal = new bootstrap.Modal(document.getElementById('catModal'));
        return _modal;
    }

    load();
    return {load, search, filterTab, setView, openCreate, openEdit, save, toggleStatus, del, previewIcon, pickIcon};
})();
</script>
