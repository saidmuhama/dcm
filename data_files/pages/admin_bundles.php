<?php
if (($user_role ?? 0) != 5) { include('403.php'); return; }
?>
<style>
/* ═══ HERO ═══════════════════════════════════════════════════════ */
.abnd-hero{position:relative;overflow:hidden;border-radius:20px;background:linear-gradient(135deg,#0f0c29 0%,#302b63 45%,#24243e 100%);padding:2rem 2rem 1.75rem;margin:0 1rem 1.5rem;color:#fff}
.abnd-hero-orb{position:absolute;border-radius:50%;filter:blur(50px);pointer-events:none}
.abnd-hero-orb-1{width:220px;height:220px;background:rgba(99,102,241,.35);top:-60px;right:-40px;animation:abndOrb1 6s ease-in-out infinite alternate}
.abnd-hero-orb-2{width:140px;height:140px;background:rgba(16,185,129,.3);bottom:-40px;right:160px;animation:abndOrb2 8s ease-in-out infinite alternate}
.abnd-hero-orb-3{width:100px;height:100px;background:rgba(251,191,36,.25);top:20px;left:55%;animation:abndOrb3 7s ease-in-out infinite alternate}
@keyframes abndOrb1{from{transform:translate(0,0) scale(1)} to{transform:translate(20px,-15px) scale(1.15)}}
@keyframes abndOrb2{from{transform:translate(0,0) scale(1)} to{transform:translate(-15px,20px) scale(1.2)}}
@keyframes abndOrb3{from{transform:translate(0,0) scale(1)} to{transform:translate(15px,-10px) scale(.9)}}
.abnd-hero-content{position:relative;z-index:2;display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap}
.abnd-hero-icon{width:64px;height:64px;border-radius:18px;background:rgba(255,255,255,.12);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;font-size:1.8rem;flex-shrink:0;border:1px solid rgba(255,255,255,.18);box-shadow:0 8px 32px rgba(99,102,241,.4)}
.abnd-hero-title{font-size:1.45rem;font-weight:800;line-height:1.1;letter-spacing:-.02em}
.abnd-hero-title span{background:linear-gradient(90deg,#a78bfa,#fbbf24,#93c5fd);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.abnd-hero-sub{font-size:.82rem;opacity:.6;margin-top:.2rem}
.abnd-hero-pills{display:flex;gap:.5rem;margin-top:.9rem;flex-wrap:wrap}
.abnd-hero-pill{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);color:#fff;font-size:.72rem;font-weight:700;padding:.25rem .75rem;border-radius:20px;display:flex;align-items:center;gap:.35rem}
.abnd-hero-actions{margin-left:auto;display:flex;gap:.6rem;flex-shrink:0}
.abnd-hero-btn{padding:.55rem 1.2rem;border-radius:12px;font-size:.82rem;font-weight:700;cursor:pointer;transition:all .2s;border:none;display:flex;align-items:center;gap:.4rem}
.abnd-hero-btn-primary{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;box-shadow:0 4px 18px rgba(99,102,241,.5)}
.abnd-hero-btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(99,102,241,.65)}
.abnd-hero-btn-secondary{background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.2)}
.abnd-hero-btn-secondary:hover{background:rgba(255,255,255,.2)}

/* ═══ TOOLBAR ══════════════════════════════════════════════════════ */
.abnd-toolbar{background:#fff;border-radius:16px;padding:.85rem 1.2rem;margin:0 1rem 1rem;box-shadow:0 2px 12px rgba(0,0,0,.05);display:flex;align-items:center;gap:.75rem;flex-wrap:wrap}
.abnd-search{flex:1;min-width:200px;max-width:320px;position:relative}
.abnd-search input{width:100%;border:1.5px solid #e0e7ff;border-radius:12px;padding:.5rem .85rem .5rem 2.2rem;font-size:.84rem;transition:all .2s;background:#f8f7ff}
.abnd-search input:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1);background:#fff}
.abnd-search-icon{position:absolute;left:.7rem;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:.9rem}
.abnd-filter-tabs{display:flex;gap:.3rem;background:#f1f5f9;border-radius:10px;padding:.2rem}
.abnd-filter-tab{padding:.3rem .85rem;border-radius:8px;font-size:.78rem;font-weight:600;cursor:pointer;border:none;background:transparent;color:#64748b;transition:all .18s;white-space:nowrap}
.abnd-filter-tab.active{background:#fff;color:#6366f1;box-shadow:0 1px 6px rgba(0,0,0,.08)}
.abnd-add-btn{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:12px;padding:.5rem 1.1rem;font-size:.83rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.4rem;transition:all .2s;box-shadow:0 4px 14px rgba(99,102,241,.35);white-space:nowrap;margin-left:auto}
.abnd-add-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(99,102,241,.5)}

/* ═══ BUNDLE CARDS GRID ════════════════════════════════════════════ */
.abnd-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.25rem;margin:0 1rem 1.5rem;padding-bottom:1rem}
@keyframes abndCardIn{from{opacity:0;transform:translateY(20px) scale(.96)} to{opacity:1;transform:none}}
.abnd-bcard{background:#fff;border-radius:18px;padding:1.4rem;box-shadow:0 2px 16px rgba(0,0,0,.07);border:2px solid transparent;transition:all .25s;animation:abndCardIn .4s ease both;position:relative;overflow:hidden}
.abnd-bcard:hover{transform:translateY(-4px);box-shadow:0 14px 32px rgba(0,0,0,.11);border-color:rgba(99,102,241,.2)}
.abnd-bcard-glow{position:absolute;inset:0;border-radius:16px;pointer-events:none;opacity:0;background:radial-gradient(circle at 50% 0%,rgba(99,102,241,.08),transparent 70%);transition:opacity .25s}
.abnd-bcard:hover .abnd-bcard-glow{opacity:1}
.abnd-bcard-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:.9rem}
.abnd-bcard-icon{width:50px;height:50px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0}
.abnd-type-subject{background:#ede9fe;color:#6366f1}
.abnd-type-institutional{background:#d1fae5;color:#059669}
.abnd-type-promotional{background:#fef3c7;color:#d97706}
.abnd-bcard-name{font-size:.95rem;font-weight:800;color:#1e1b4b;margin-bottom:.15rem;line-height:1.25}
.abnd-bcard-desc{font-size:.75rem;color:#94a3b8;line-height:1.45;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.abnd-bcard-meta{display:flex;gap:.55rem;flex-wrap:wrap;margin:.9rem 0}
.abnd-meta-chip{font-size:.68rem;font-weight:700;padding:.22rem .65rem;border-radius:20px;background:#f1f5f9;color:#475569;display:flex;align-items:center;gap:.3rem}
.abnd-meta-chip.courses{background:#ede9fe;color:#6366f1}
.abnd-meta-chip.price{background:#d1fae5;color:#065f46}
.abnd-status-badge{font-size:.65rem;font-weight:700;padding:.2rem .55rem;border-radius:20px;text-transform:uppercase;letter-spacing:.05em}
.abnd-status-active{background:#dcfce7;color:#166534}
.abnd-status-inactive{background:#fee2e2;color:#991b1b}
.abnd-bcard-actions{display:flex;gap:.5rem;padding-top:.9rem;border-top:1px solid #f1f5f9}
.abnd-act-btn{flex:1;border:none;border-radius:10px;padding:.4rem;font-size:.78rem;font-weight:600;cursor:pointer;transition:all .18s;display:flex;align-items:center;justify-content:center;gap:.3rem}
.abnd-act-edit{background:#f8f7ff;color:#6366f1;border:1.5px solid #e0e7ff}
.abnd-act-edit:hover{background:#6366f1;color:#fff;border-color:#6366f1}
.abnd-act-del{background:#fff5f5;color:#ef4444;border:1.5px solid #fee2e2}
.abnd-act-del:hover{background:#ef4444;color:#fff;border-color:#ef4444}

/* ═══ EMPTY STATE ═══════════════════════════════════════════════════ */
.abnd-empty{padding:4rem 2rem;text-align:center;color:#94a3b8}
.abnd-empty-icon{width:80px;height:80px;border-radius:20px;background:linear-gradient(135deg,#ede9fe,#e0e7ff);display:flex;align-items:center;justify-content:center;font-size:2rem;color:#6366f1;margin:0 auto 1rem}

/* ═══ MODAL ════════════════════════════════════════════════════════ */
#abndModal .modal-content{border:none;border-radius:20px;box-shadow:0 24px 80px rgba(0,0,0,.18);overflow:hidden}
#abndModal .modal-header{background:linear-gradient(135deg,#0f0c29,#302b63);padding:1.25rem 1.5rem;border:none}
#abndModal .modal-title{color:#fff;font-weight:800;font-size:.95rem}
#abndModal .btn-close{filter:invert(1);opacity:.7}
#abndModal .modal-body{padding:1.5rem;background:#fafbff}
#abndModal .modal-footer{background:#f8f7ff;border:none;padding:1rem 1.5rem}
#abndModal .form-control,#abndModal .form-select{border-radius:10px;border:1.5px solid #e0e7ff;font-size:.85rem;transition:all .2s}
#abndModal .form-control:focus,#abndModal .form-select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
#abndModal label{font-size:.73rem;font-weight:700;color:#475569;letter-spacing:.03em;text-transform:uppercase;margin-bottom:.3rem}
.abnd-modal-save{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:12px;padding:.55rem 1.4rem;font-size:.84rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;transition:all .2s;box-shadow:0 4px 14px rgba(99,102,241,.35)}
.abnd-modal-save:hover{transform:translateY(-1px);box-shadow:0 8px 24px rgba(99,102,241,.5)}
.abnd-modal-save:disabled{opacity:.6;transform:none}

/* ═══ COURSE MULTI-SELECT ══════════════════════════════════════════ */
.abnd-course-search{border:1.5px solid #e0e7ff;border-radius:10px 10px 0 0;padding:.45rem .75rem;font-size:.83rem;width:100%;background:#f8f7ff;border-bottom:none}
.abnd-course-search:focus{outline:none;border-color:#6366f1;background:#fff}
.abnd-course-list{border:1.5px solid #e0e7ff;border-radius:0 0 10px 10px;max-height:180px;overflow-y:auto;background:#fff}
.abnd-course-item{padding:.45rem .75rem;font-size:.82rem;cursor:pointer;display:flex;align-items:center;gap:.5rem;transition:background .12s}
.abnd-course-item:hover{background:#f8f7ff}
.abnd-course-item input[type="checkbox"]{accent-color:#6366f1}
.abnd-selected-tags{display:flex;flex-wrap:wrap;gap:.3rem;margin-top:.5rem;min-height:24px}
.abnd-course-tag{background:#ede9fe;color:#5b21b6;font-size:.7rem;font-weight:700;padding:.2rem .55rem;border-radius:20px;display:flex;align-items:center;gap:.3rem}
.abnd-course-tag button{background:none;border:none;color:#5b21b6;cursor:pointer;font-size:.7rem;padding:0;line-height:1;opacity:.7}
.abnd-course-tag button:hover{opacity:1}
</style>

<div class="container-fluid px-0">

<!-- HERO -->
<div class="abnd-hero mx-3 mt-3 mb-0">
    <div class="abnd-hero-orb abnd-hero-orb-1"></div>
    <div class="abnd-hero-orb abnd-hero-orb-2"></div>
    <div class="abnd-hero-orb abnd-hero-orb-3"></div>
    <div class="abnd-hero-content">
        <div class="abnd-hero-icon"><i class="bi bi-collection-fill"></i></div>
        <div>
            <div class="abnd-hero-title">Course <span>Bundles</span></div>
            <div class="abnd-hero-sub">Group courses into subject, institutional or promotional bundles</div>
            <div class="abnd-hero-pills">
                <span class="abnd-hero-pill"><i class="bi bi-collection"></i><span id="abndPillTotal">— bundles</span></span>
                <span class="abnd-hero-pill"><i class="bi bi-check-circle-fill" style="color:#4ade80"></i><span id="abndPillActive">— active</span></span>
            </div>
        </div>
        <div class="abnd-hero-actions d-none d-md-flex">
            <button class="abnd-hero-btn abnd-hero-btn-secondary" onclick="abndMgr.load()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
            <button class="abnd-hero-btn abnd-hero-btn-primary"   onclick="abndMgr.openCreate()"><i class="bi bi-plus-lg"></i> New Bundle</button>
        </div>
    </div>
</div>

<!-- TOOLBAR -->
<div class="abnd-toolbar mt-3">
    <div class="abnd-search">
        <i class="bi bi-search abnd-search-icon"></i>
        <input id="abndSearch" placeholder="Search bundles…" oninput="abndMgr.search(this.value)" autocomplete="off">
    </div>
    <div class="abnd-filter-tabs" id="abndFilterTabs">
        <button class="abnd-filter-tab active" onclick="abndMgr.filterTab('all',this)">All</button>
        <button class="abnd-filter-tab"         onclick="abndMgr.filterTab('active',this)">Active</button>
        <button class="abnd-filter-tab"         onclick="abndMgr.filterTab('inactive',this)">Inactive</button>
        <button class="abnd-filter-tab"         onclick="abndMgr.filterTab('subject',this)">Subject</button>
        <button class="abnd-filter-tab"         onclick="abndMgr.filterTab('institutional',this)">Institutional</button>
        <button class="abnd-filter-tab"         onclick="abndMgr.filterTab('promotional',this)">Promo</button>
    </div>
    <button class="abnd-add-btn d-md-none" onclick="abndMgr.openCreate()"><i class="bi bi-plus-lg"></i> New</button>
</div>

<!-- BUNDLE GRID -->
<div class="abnd-grid" id="abndGrid">
    <div style="grid-column:1/-1;text-align:center;padding:3rem;color:#94a3b8">
        <div class="spinner-border spinner-border-sm me-2"></div> Loading bundles…
    </div>
</div>

</div><!-- /.container-fluid -->

<!-- ══════ MODAL ══════ -->
<div class="modal fade" id="abndModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
<div class="modal-content">
    <div class="modal-header">
        <h6 class="modal-title" id="abndModalTitle"><i class="bi bi-collection-fill me-2"></i>New Bundle</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <input type="hidden" id="abndId">
        <div class="row g-3">
            <div class="col-12">
                <label>Bundle Name *</label>
                <input class="form-control" id="abndName" placeholder="e.g. STEM Foundation Pack" maxlength="200">
            </div>
            <div class="col-md-4">
                <label>Bundle Type *</label>
                <select class="form-select" id="abndType">
                    <option value="subject">Subject</option>
                    <option value="institutional">Institutional</option>
                    <option value="promotional">Promotional</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>Individual Price (TZS)</label>
                <input type="number" class="form-control" id="abndIndPrice" min="0" step="0.01" placeholder="0.00">
            </div>
            <div class="col-md-4">
                <label>Org Price (TZS)</label>
                <input type="number" class="form-control" id="abndOrgPrice" min="0" step="0.01" placeholder="0.00">
            </div>
            <div class="col-md-6">
                <label>Target Level</label>
                <input class="form-control" id="abndTargetLevel" placeholder="e.g. Secondary, University…" maxlength="100">
            </div>
            <div class="col-md-6">
                <label>Description</label>
                <input class="form-control" id="abndDesc" placeholder="Short description…" maxlength="500">
            </div>
            <div class="col-12">
                <label>Select Courses</label>
                <input class="abnd-course-search" id="abndCourseSearch" placeholder="Search courses to add…" oninput="abndMgr.searchCourses(this.value)" autocomplete="off">
                <div class="abnd-course-list" id="abndCourseList">
                    <div class="abnd-course-item text-muted"><i class="bi bi-info-circle me-1"></i>Type to search courses…</div>
                </div>
                <div class="abnd-selected-tags" id="abndSelectedTags"></div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="abnd-modal-save" id="abndSaveBtn" onclick="abndMgr.save()"><i class="bi bi-check-lg"></i> Save Bundle</button>
    </div>
</div>
</div>
</div>

<script>
window.abndMgr = (function () {
    var AJAX       = 'ajax/ajax_course_pricing.php';
    var _modal     = null;
    var _allRows   = [];
    var _filter    = 'all';
    var _selCourses= {};  // {id: title}
    var _allCourses= [];  // cache

    function esc(s) {
        return String(s || '').replace(/[&<>"']/g, function(c) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
        });
    }

    function api(action, body, method) {
        if (!method || method === 'GET') {
            var qs = new URLSearchParams(Object.assign({action: action}, body || {}));
            return fetch(AJAX + '?' + qs).then(function(r){ return r.json(); });
        }
        return fetch(AJAX, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(Object.assign({action: action}, body || {}))
        }).then(function(r){ return r.json(); });
    }

    function load() {
        api('list_bundles').then(function(res) {
            _allRows = (res.status === 'success') ? (res.data || []) : [];
            updatePills();
            render(_allRows);
        });
    }

    function updatePills() {
        var total  = _allRows.length;
        var active = _allRows.filter(function(r){ return r.status === 'active'; }).length;
        document.getElementById('abndPillTotal').textContent  = total + ' bundle' + (total !== 1 ? 's' : '');
        document.getElementById('abndPillActive').textContent = active + ' active';
    }

    function filterTab(f, btn) {
        _filter = f;
        document.querySelectorAll('.abnd-filter-tab').forEach(function(b){ b.classList.remove('active'); });
        btn.classList.add('active');
        applyFilter();
    }

    function applyFilter() {
        var rows = _allRows;
        if (_filter === 'active')        rows = rows.filter(function(r){ return r.status === 'active'; });
        else if (_filter === 'inactive') rows = rows.filter(function(r){ return r.status === 'inactive'; });
        else if (['subject','institutional','promotional'].indexOf(_filter) !== -1) {
            rows = rows.filter(function(r){ return r.bundle_type === _filter; });
        }
        var q = (document.getElementById('abndSearch').value || '').toLowerCase();
        if (q) rows = rows.filter(function(r){ return (r.bundle_name||'').toLowerCase().includes(q) || (r.description||'').toLowerCase().includes(q); });
        render(rows);
    }

    function search(q) { applyFilter(); }

    var TYPE_ICONS = {subject:'bi-book',institutional:'bi-building',promotional:'bi-gift'};
    var TYPE_LABELS= {subject:'Subject',institutional:'Institutional',promotional:'Promotional'};

    function render(rows) {
        var grid = document.getElementById('abndGrid');
        if (!rows.length) {
            grid.innerHTML = '<div style="grid-column:1/-1"><div class="abnd-empty"><div class="abnd-empty-icon"><i class="bi bi-collection"></i></div><div class="fw-bold">No bundles found</div><div style="font-size:.8rem;margin-top:.3rem">Create your first bundle to get started</div></div></div>';
            return;
        }
        grid.innerHTML = rows.map(function(b, i) {
            var icon  = TYPE_ICONS[b.bundle_type]  || 'bi-collection';
            var lbl   = TYPE_LABELS[b.bundle_type] || b.bundle_type;
            var act   = b.status === 'active';
            var ip    = parseFloat(b.individual_price) || 0;
            var op    = parseFloat(b.org_price)        || 0;
            var cnt   = parseInt(b.course_count)       || 0;
            return '<div class="abnd-bcard" style="animation-delay:' + (i*0.05) + 's">' +
                '<div class="abnd-bcard-glow"></div>' +
                '<div class="abnd-bcard-top">' +
                    '<div class="abnd-bcard-icon abnd-type-' + esc(b.bundle_type) + '"><i class="bi ' + icon + '"></i></div>' +
                    '<span class="abnd-status-badge ' + (act ? 'abnd-status-active' : 'abnd-status-inactive') + '">' + (act ? 'Active' : 'Inactive') + '</span>' +
                '</div>' +
                '<div class="abnd-bcard-name">' + esc(b.bundle_name) + '</div>' +
                '<div class="abnd-bcard-desc">' + esc(b.description || 'No description') + '</div>' +
                '<div class="abnd-bcard-meta">' +
                    '<span class="abnd-meta-chip"><i class="bi bi-tag-fill"></i>' + lbl + '</span>' +
                    '<span class="abnd-meta-chip courses"><i class="bi bi-collection"></i>' + cnt + ' course' + (cnt !== 1 ? 's' : '') + '</span>' +
                    (ip > 0 ? '<span class="abnd-meta-chip price"><i class="bi bi-person"></i>TZS ' + ip.toLocaleString() + '</span>' : '') +
                    (op > 0 ? '<span class="abnd-meta-chip" style="background:#fef9c3;color:#854d0e"><i class="bi bi-building"></i>Org TZS ' + op.toLocaleString() + '</span>' : '') +
                '</div>' +
                '<div class="abnd-bcard-actions">' +
                    '<button class="abnd-act-btn abnd-act-edit" onclick="abndMgr.openEdit(' + b.id + ')"><i class="bi bi-pencil-fill"></i> Edit</button>' +
                    '<button class="abnd-act-btn abnd-act-del"  onclick="abndMgr.del(' + b.id + ',\'' + esc(b.bundle_name) + '\')"><i class="bi bi-trash-fill"></i> Remove</button>' +
                '</div>' +
                '</div>';
        }).join('');
    }

    function getModal() {
        if (!_modal) _modal = new bootstrap.Modal(document.getElementById('abndModal'));
        return _modal;
    }

    function resetModal() {
        document.getElementById('abndId').value         = '';
        document.getElementById('abndName').value       = '';
        document.getElementById('abndType').value       = 'subject';
        document.getElementById('abndIndPrice').value   = '';
        document.getElementById('abndOrgPrice').value   = '';
        document.getElementById('abndTargetLevel').value= '';
        document.getElementById('abndDesc').value       = '';
        document.getElementById('abndCourseSearch').value = '';
        _selCourses = {};
        renderSelectedTags();
        renderCourseList([]);
    }

    function openCreate() {
        resetModal();
        document.getElementById('abndModalTitle').innerHTML = '<i class="bi bi-plus-circle-fill me-2"></i>New Bundle';
        loadAllCourses();
        getModal().show();
    }

    function openEdit(id) {
        resetModal();
        document.getElementById('abndModalTitle').innerHTML = '<i class="bi bi-pencil-fill me-2"></i>Edit Bundle';
        api('get_bundle', {id: id}).then(function(res) {
            if (res.status !== 'success') { Swal.fire({icon:'error',title:'Error',text:res.message}); return; }
            var b = res.data || {};
            document.getElementById('abndId').value          = b.id || '';
            document.getElementById('abndName').value        = b.bundle_name        || '';
            document.getElementById('abndType').value        = b.bundle_type        || 'subject';
            document.getElementById('abndIndPrice').value    = b.individual_price   || '';
            document.getElementById('abndOrgPrice').value    = b.org_price          || '';
            document.getElementById('abndTargetLevel').value = b.target_level       || '';
            document.getElementById('abndDesc').value        = b.description        || '';
            // Pre-fill selected courses
            _selCourses = {};
            (b.courses || []).forEach(function(c){ _selCourses[c.course_id] = c.course_title; });
            renderSelectedTags();
            loadAllCourses();
        });
        getModal().show();
    }

    function save() {
        var name = document.getElementById('abndName').value.trim();
        if (!name) { Swal.fire({icon:'warning',title:'Required',text:'Bundle name is required',timer:2000,showConfirmButton:false}); return; }
        var courseIds = Object.keys(_selCourses).map(Number);
        var btn = document.getElementById('abndSaveBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';
        api('save_bundle', {
            id:               parseInt(document.getElementById('abndId').value) || 0,
            bundle_name:      name,
            bundle_type:      document.getElementById('abndType').value,
            description:      document.getElementById('abndDesc').value.trim(),
            target_level:     document.getElementById('abndTargetLevel').value.trim(),
            individual_price: parseFloat(document.getElementById('abndIndPrice').value) || 0,
            org_price:        parseFloat(document.getElementById('abndOrgPrice').value) || 0,
            course_ids:       courseIds,
        }, 'POST').then(function(res) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Save Bundle';
            if (res.status === 'success') {
                Swal.fire({icon:'success',title:'Saved!',text:res.message,timer:1800,showConfirmButton:false});
                getModal().hide();
                load();
            } else {
                Swal.fire({icon:'error',title:'Error',text:res.message});
            }
        });
    }

    function del(id, name) {
        Swal.fire({
            icon:'warning', title:'Remove Bundle?',
            html:'<p>Remove <strong>"' + esc(name) + '"</strong>?<br>This will deactivate the bundle.</p>',
            showCancelButton:true, confirmButtonColor:'#ef4444',
            confirmButtonText:'Yes, Remove', cancelButtonText:'Cancel'
        }).then(function(c) {
            if (!c.isConfirmed) return;
            api('delete_bundle', {id: id}, 'POST').then(function(res) {
                if (res.status === 'success') {
                    Swal.fire({icon:'success',title:'Removed',timer:1500,showConfirmButton:false});
                    load();
                } else {
                    Swal.fire({icon:'error',title:'Error',text:res.message});
                }
            });
        });
    }

    /* ── Course multi-select helpers ──────────────────────────────── */
    function loadAllCourses() {
        if (_allCourses.length) { renderCourseList(_allCourses); return; }
        api('list_courses').then(function(res) {
            _allCourses = (res.status === 'success') ? (res.data || []) : [];
            renderCourseList(_allCourses);
        });
    }

    function searchCourses(q) {
        if (!q.trim()) { renderCourseList(_allCourses); return; }
        var lq = q.toLowerCase();
        renderCourseList(_allCourses.filter(function(c){ return (c.course_title||'').toLowerCase().includes(lq); }));
    }

    function renderCourseList(courses) {
        var el = document.getElementById('abndCourseList');
        if (!courses.length) {
            el.innerHTML = '<div class="abnd-course-item text-muted"><i class="bi bi-search me-1"></i>No courses found</div>';
            return;
        }
        el.innerHTML = courses.map(function(c) {
            var checked = _selCourses.hasOwnProperty(c.id) ? 'checked' : '';
            return '<label class="abnd-course-item">' +
                '<input type="checkbox" ' + checked + ' onchange="abndMgr.toggleCourse(' + c.id + ',\'' + esc(c.course_title) + '\',this.checked)">' +
                '<span>' + esc(c.course_title) + '</span>' +
                (parseFloat(c.price) > 0 ? '<span class="ms-auto text-muted" style="font-size:.7rem">TZS ' + parseFloat(c.price).toLocaleString() + '</span>' : '') +
                '</label>';
        }).join('');
    }

    function toggleCourse(id, title, checked) {
        if (checked) {
            _selCourses[id] = title;
        } else {
            delete _selCourses[id];
        }
        renderSelectedTags();
    }

    function removeCourse(id) {
        delete _selCourses[id];
        renderSelectedTags();
        // Uncheck in list
        var el = document.getElementById('abndCourseList');
        var boxes = el.querySelectorAll('input[type="checkbox"]');
        // Re-render to sync
        searchCourses(document.getElementById('abndCourseSearch').value);
    }

    function renderSelectedTags() {
        var ids = Object.keys(_selCourses);
        document.getElementById('abndSelectedTags').innerHTML = ids.length === 0
            ? '<span style="font-size:.75rem;color:#94a3b8">No courses selected</span>'
            : ids.map(function(id) {
                return '<span class="abnd-course-tag">' + esc(_selCourses[id]) +
                    '<button onclick="abndMgr.removeCourse(' + id + ')" title="Remove">&times;</button>' +
                    '</span>';
            }).join('');
    }

    load();

    return {
        load: load,
        search: search,
        filterTab: filterTab,
        openCreate: openCreate,
        openEdit: openEdit,
        save: save,
        del: del,
        searchCourses: searchCourses,
        toggleCourse: toggleCourse,
        removeCourse: removeCourse,
    };
})();
</script>
