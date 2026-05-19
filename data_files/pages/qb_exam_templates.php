<?php
$view = $_GET['view'] ?? 'qb_exam_templates';
?>
<style>
/* ═══════════════════════════════════════════════════════════
   QB EXAM TEMPLATES  (qbe-*)
═══════════════════════════════════════════════════════════ */
.qbe-wrap { font-family:'Open Sans',sans-serif; }

/* ── Hero ── */
.qbe-hero { position:relative; overflow:hidden; isolation:isolate; border-radius:20px; padding:2rem 2.2rem; margin-bottom:1.4rem; background:linear-gradient(135deg,#0b1120 0%,#0f1e3d 40%,#1a1040 100%); }
.qbe-hero-grid { position:absolute; inset:0; z-index:0; background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px); background-size:44px 44px; }
.qbe-hero-inner { position:relative; z-index:1; }
.qbe-hero-badge { display:inline-flex; align-items:center; gap:.4rem; background:rgba(255,255,255,.09); border:1px solid rgba(255,255,255,.15); border-radius:100px; padding:.28rem .85rem; font-size:.7rem; font-weight:700; color:rgba(255,255,255,.7); letter-spacing:.06em; text-transform:uppercase; margin-bottom:.75rem; backdrop-filter:blur(6px); }
.qbe-hero-title { font-size:1.7rem; font-weight:900; color:#fff; font-family:'SUSE',sans-serif; letter-spacing:-.04em; line-height:1.15; margin-bottom:.3rem; }
.qbe-hero-title em { font-style:normal; background:linear-gradient(90deg,#60a5fa,#c084fc); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent; }
.qbe-hero-sub { font-size:.81rem; color:rgba(255,255,255,.45); margin-bottom:1.4rem; max-width:520px; line-height:1.6; }
.qbe-kpis { display:flex; gap:.7rem; flex-wrap:wrap; }
.qbe-kpi { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12); border-radius:14px; padding:.6rem 1rem; backdrop-filter:blur(8px); min-width:90px; transition:background .2s; }
.qbe-kpi:hover { background:rgba(255,255,255,.13); }
.qbe-kpi-val { font-size:1.2rem; font-weight:900; color:#fff; font-family:'SUSE',sans-serif; line-height:1; }
.qbe-kpi-lbl { font-size:.63rem; color:rgba(255,255,255,.45); margin-top:.15rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; }

/* ── Hero actions ── */
.qbe-hero-acts { display:flex; gap:.65rem; flex-wrap:wrap; margin-top:1.1rem; }
.qbe-hero-btn { display:inline-flex; align-items:center; gap:.42rem; border-radius:12px; padding:.58rem 1.3rem; font-size:.82rem; font-weight:700; cursor:pointer; font-family:inherit; transition:filter .18s,transform .12s; text-decoration:none; border:none; white-space:nowrap; }
.qbe-hero-btn-primary { background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff; box-shadow:0 4px 18px rgba(26,79,196,.4); }
.qbe-hero-btn-primary:hover { filter:brightness(1.1); transform:translateY(-1px); color:#fff; }
.qbe-hero-btn-secondary { background:rgba(255,255,255,.1); color:#fff; border:1px solid rgba(255,255,255,.2); }
.qbe-hero-btn-secondary:hover { background:rgba(255,255,255,.18); color:#fff; }

/* ── Status nav ── */
.qbe-status-nav { display:flex; gap:.4rem; flex-wrap:wrap; margin-bottom:1.2rem; }
.qbe-spill { display:inline-flex; align-items:center; gap:.45rem; padding:.38rem .95rem; border-radius:100px; font-size:.77rem; font-weight:700; text-decoration:none; border:1.5px solid #e2e8f0; background:#fff; color:#475569; transition:all .17s; cursor:pointer; box-shadow:0 1px 3px rgba(0,0,0,.04); }
.qbe-spill:hover:not(.active) { border-color:#cbd5e1; color:#0f172a; }
.qbe-spill.active { background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff; border-color:transparent; box-shadow:0 4px 14px rgba(26,79,196,.35); }
.qbe-spill-cnt { font-size:.66rem; font-weight:900; padding:.05rem .42rem; border-radius:100px; background:rgba(0,0,0,.08); min-width:20px; text-align:center; }
.qbe-spill.active .qbe-spill-cnt { background:rgba(255,255,255,.22); }

/* ── Toolbar ── */
.qbe-toolbar { display:flex; align-items:center; gap:.65rem; flex-wrap:wrap; background:#fff; border-radius:16px; padding:.85rem 1.1rem; box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 14px rgba(0,0,0,.05); border:1px solid #f0f4f8; margin-bottom:1.1rem; }
.qbe-search-wrap { position:relative; flex:1; min-width:180px; }
.qbe-search-wrap i { position:absolute; left:.75rem; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:.84rem; pointer-events:none; }
.qbe-search { width:100%; padding:.5rem .85rem .5rem 2.2rem; border-radius:10px; border:1.5px solid #e2e8f0; font-size:.82rem; font-family:inherit; outline:none; background:#f8fafc; color:#1e293b; transition:border-color .18s,box-shadow .18s; }
.qbe-search:focus { border-color:#1a4fc4; box-shadow:0 0 0 3px rgba(26,79,196,.1); background:#fff; }
.qbe-sel { padding:.5rem .75rem; border-radius:10px; border:1.5px solid #e2e8f0; font-size:.79rem; font-family:inherit; outline:none; background:#f8fafc; color:#475569; cursor:pointer; }
.qbe-sel:focus { border-color:#1a4fc4; }
.qbe-view-btns { display:flex; gap:.3rem; }
.qbe-view-btn { width:34px; height:34px; border-radius:9px; border:1.5px solid #e2e8f0; background:#f8fafc; color:#94a3b8; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all .15s; font-size:.88rem; }
.qbe-view-btn.active { background:#eff6ff; border-color:#bfdbfe; color:#1a4fc4; }
.qbe-badge { background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff; border-radius:100px; padding:.25rem .8rem; font-size:.72rem; font-weight:800; white-space:nowrap; font-family:'SUSE',sans-serif; }

/* ── Card grid ── */
.qbe-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:1rem; }
.qbe-grid.list-mode { grid-template-columns:1fr; gap:.55rem; }

/* ── Exam card ── */
.qbe-card { background:#fff; border-radius:18px; border:1px solid #f0f4f8; box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 16px rgba(0,0,0,.05); overflow:hidden; transition:transform .22s,box-shadow .22s; animation:qbe-up .4s cubic-bezier(.16,1,.3,1) both; }
.qbe-card:hover { transform:translateY(-4px); box-shadow:0 14px 44px rgba(0,0,0,.11); }
@keyframes qbe-up { from{opacity:0;transform:translateY(18px)} to{opacity:1;transform:translateY(0)} }
.qbe-card-accent { height:4px; }
.qbe-card-body { padding:1.1rem 1.2rem .8rem; }
.qbe-card-top { display:flex; align-items:flex-start; justify-content:space-between; gap:.75rem; margin-bottom:.7rem; }
.qbe-code-badge { font-family:monospace; font-size:.68rem; font-weight:800; background:#0f172a; color:#e2e8f0; padding:.16rem .55rem; border-radius:7px; letter-spacing:.04em; }
.qbe-type-badge { display:inline-flex; align-items:center; gap:.25rem; border-radius:100px; padding:.13rem .55rem; font-size:.65rem; font-weight:800; letter-spacing:.04em; }
.qbe-card-title { font-size:.97rem; font-weight:800; color:#0f172a; line-height:1.25; font-family:'SUSE',sans-serif; margin-bottom:.55rem; }
.qbe-card-meta { display:flex; flex-wrap:wrap; gap:.3rem; margin-bottom:.75rem; }
.qbe-meta-pill { display:inline-flex; align-items:center; gap:.25rem; border-radius:100px; padding:.15rem .55rem; font-size:.65rem; font-weight:700; background:#f1f5f9; color:#475569; }

/* ── 3-stat strip ── */
.qbe-stats { display:flex; gap:.4rem; padding:0 1.2rem .8rem; }
.qbe-stat { flex:1; background:#f8fafc; border-radius:10px; padding:.5rem .6rem; border:1px solid #f0f4f8; text-align:center; }
.qbe-stat-val { font-size:.95rem; font-weight:800; color:#0f172a; font-family:'SUSE',sans-serif; line-height:1; }
.qbe-stat-lbl { font-size:.58rem; color:#94a3b8; font-weight:600; margin-top:.12rem; text-transform:uppercase; letter-spacing:.04em; }

/* ── Progress bar ── */
.qbe-prog-wrap { padding:0 1.2rem .8rem; }
.qbe-prog-labels { display:flex; justify-content:space-between; font-size:.66rem; font-weight:600; color:#94a3b8; margin-bottom:.28rem; }
.qbe-prog-track { height:5px; background:#f0f4f8; border-radius:100px; overflow:hidden; }
.qbe-prog-fill { height:100%; border-radius:100px; width:0%; transition:width 1.3s cubic-bezier(.16,1,.3,1); }

/* ── Card footer ── */
.qbe-card-foot { display:flex; align-items:center; justify-content:space-between; padding:.65rem 1.2rem; background:#fafbfd; border-top:1px solid #f0f4f8; gap:.5rem; flex-wrap:wrap; }
.qbe-status-pill { display:inline-flex; align-items:center; gap:.3rem; border-radius:100px; padding:.2rem .65rem; font-size:.67rem; font-weight:800; letter-spacing:.04em; text-transform:capitalize; }
.qbe-card-acts { display:flex; gap:.32rem; align-items:center; }
.qbe-icn { width:30px; height:30px; border-radius:8px; border:1.5px solid #e2e8f0; background:#f8fafc; color:#64748b; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all .15s; font-size:.78rem; text-decoration:none; }
.qbe-icn:hover { background:#eff6ff; border-color:#bfdbfe; color:#1a4fc4; }
.qbe-icn-del:hover { background:#fff1f2; border-color:#fecaca; color:#dc2626; }
.qbe-pub-btn { display:inline-flex; align-items:center; gap:.3rem; border-radius:8px; border:none; padding:.3rem .75rem; font-size:.72rem; font-weight:700; cursor:pointer; font-family:inherit; transition:all .15s; }
.qbe-pub-btn-publish { background:#ede9fe; color:#6d28d9; }
.qbe-pub-btn-publish:hover { background:#6d28d9; color:#fff; }
.qbe-pub-btn-archive { background:#f1f5f9; color:#64748b; }
.qbe-pub-btn-archive:hover { background:#64748b; color:#fff; }
.qbe-pub-btn-draft { background:#fef9c3; color:#92400e; }
.qbe-pub-btn-draft:hover { background:#92400e; color:#fff; }

/* ── Skeleton ── */
.qbe-skel { background:linear-gradient(90deg,#f0f4f8 25%,#e2e8f0 50%,#f0f4f8 75%); background-size:200% 100%; animation:qbe-shim 1.5s infinite; border-radius:8px; }
@keyframes qbe-shim { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ── Empty ── */
.qbe-empty { grid-column:1/-1; text-align:center; padding:4rem 2rem; background:#fff; border-radius:18px; border:1.5px dashed #e2e8f0; }
.qbe-empty-icon { font-size:3.5rem; color:#e2e8f0; display:block; margin-bottom:1rem; }

/* ── List mode ── */
.qbe-grid.list-mode .qbe-card { border-radius:14px; }
.qbe-grid.list-mode .qbe-card-accent { display:none; }
.qbe-grid.list-mode .qbe-card-body { padding:.75rem 1rem; }
.qbe-grid.list-mode .qbe-prog-wrap { display:none; }
</style>

<div class="container-fluid px-3 py-3 qbe-wrap">

<!-- ── Hero ──────────────────────────────────────────────── -->
<div class="qbe-hero">
  <div class="qbe-hero-grid"></div>
  <div style="position:absolute;right:3rem;top:50%;transform:translateY(-50%);width:220px;height:220px;border-radius:50%;background:conic-gradient(from 0deg,rgba(26,79,196,.45),rgba(109,40,217,.35),rgba(26,79,196,.45));filter:blur(42px);opacity:.55;animation:db-orb-spin 16s linear infinite;z-index:0"></div>
  <div style="position:absolute;left:30%;bottom:-40px;width:140px;height:140px;border-radius:50%;background:rgba(109,40,217,.35);filter:blur(36px);opacity:.35;z-index:0"></div>

  <div class="qbe-hero-inner">
    <div class="qbe-hero-badge"><i class="bi bi-layout-text-window"></i>Question Bank — Exam Module</div>
    <div class="qbe-hero-title"><em>Exam</em> Templates</div>
    <div class="qbe-hero-sub">Create, manage, and publish exam papers. Build manual or auto-generated exams from your question bank with full control over marks, duration, and delivery options.</div>
    <div class="qbe-kpis" id="heroKpis">
      <?php for ($ki=0;$ki<4;$ki++): ?>
      <div class="qbe-kpi"><div class="qbe-skel" style="width:44px;height:22px;margin-bottom:5px"></div><div class="qbe-kpi-lbl">Loading</div></div>
      <?php endfor; ?>
    </div>
    <div class="qbe-hero-acts">
      <a href="?view=qb_create_exam" class="qbe-hero-btn qbe-hero-btn-primary"><i class="bi bi-plus-lg"></i>Create Exam</a>
      <a href="?view=qb_random_exam" class="qbe-hero-btn qbe-hero-btn-secondary"><i class="bi bi-shuffle"></i>Random Exam</a>
      <a href="?view=qb_cbt_exams"   class="qbe-hero-btn qbe-hero-btn-secondary"><i class="bi bi-display"></i>CBT Exams</a>
    </div>
  </div>
</div>

<!-- ── Status nav ─────────────────────────────────────────── -->
<div class="qbe-status-nav">
  <button class="qbe-spill active" id="pill_all"       onclick="filterStatus('')">
    <i class="bi bi-collection-fill" style="font-size:.74rem"></i>All
    <span class="qbe-spill-cnt" id="cnt_all">…</span>
  </button>
  <button class="qbe-spill" id="pill_draft"     onclick="filterStatus('draft')">
    <i class="bi bi-pencil-square" style="font-size:.74rem"></i>Draft
    <span class="qbe-spill-cnt" id="cnt_draft">…</span>
  </button>
  <button class="qbe-spill" id="pill_published" onclick="filterStatus('published')">
    <i class="bi bi-check-circle-fill" style="font-size:.74rem"></i>Published
    <span class="qbe-spill-cnt" id="cnt_published">…</span>
  </button>
  <button class="qbe-spill" id="pill_archived"  onclick="filterStatus('archived')">
    <i class="bi bi-archive-fill" style="font-size:.74rem"></i>Archived
    <span class="qbe-spill-cnt" id="cnt_archived">…</span>
  </button>
</div>

<!-- ── Toolbar ────────────────────────────────────────────── -->
<div class="qbe-toolbar">
  <div class="qbe-search-wrap">
    <i class="bi bi-search"></i>
    <input type="text" id="eSearch" class="qbe-search" placeholder="Search exams…" oninput="filterExams()">
  </div>
  <select id="eType" class="qbe-sel" onchange="filterExams()">
    <option value="">All Types</option>
    <option value="manual">Manual</option>
    <option value="random">Random</option>
  </select>
  <div class="qbe-view-btns">
    <button class="qbe-view-btn active" id="btnGrid" onclick="setView('grid')" title="Grid"><i class="bi bi-grid-fill"></i></button>
    <button class="qbe-view-btn"        id="btnList" onclick="setView('list')" title="List"><i class="bi bi-list-ul"></i></button>
  </div>
  <div class="qbe-badge" id="eCount">…</div>
</div>

<!-- ── Card grid ──────────────────────────────────────────── -->
<div class="qbe-grid" id="eGrid">
  <?php for ($si=0;$si<6;$si++): ?>
  <div class="qbe-card">
    <div class="qbe-card-accent qbe-skel" style="height:4px"></div>
    <div class="qbe-card-body">
      <div class="qbe-skel" style="width:80%;height:18px;border-radius:6px;margin-bottom:8px"></div>
      <div class="qbe-skel" style="width:60%;height:14px;border-radius:5px;margin-bottom:10px"></div>
      <div style="display:flex;gap:.3rem"><div class="qbe-skel" style="width:70px;height:22px;border-radius:100px"></div><div class="qbe-skel" style="width:60px;height:22px;border-radius:100px"></div></div>
    </div>
    <div class="qbe-stats"><div class="qbe-stat"><div class="qbe-skel" style="width:100%;height:36px;border-radius:8px"></div></div><div class="qbe-stat"><div class="qbe-skel" style="width:100%;height:36px;border-radius:8px"></div></div><div class="qbe-stat"><div class="qbe-skel" style="width:100%;height:36px;border-radius:8px"></div></div></div>
    <div class="qbe-card-foot" style="border-top:1px solid #f0f4f8"><div class="qbe-skel" style="width:72px;height:22px;border-radius:100px"></div><div style="display:flex;gap:.3rem"><div class="qbe-skel" style="width:30px;height:30px;border-radius:8px"></div><div class="qbe-skel" style="width:30px;height:30px;border-radius:8px"></div><div class="qbe-skel" style="width:30px;height:30px;border-radius:8px"></div></div></div>
  </div>
  <?php endfor; ?>
</div>

</div><!-- /.container-fluid -->

<script>
/* ── DCM Alert ─────────────────────────────────────────────── */
const dcmAlert = {
  _css:`
    .ds-pop{border-radius:20px!important;font-family:'Open Sans',sans-serif!important;padding:1.6rem!important}
    .ds-ttl{font-size:1.1rem!important;font-weight:800!important;color:#0f172a!important;margin-top:.3rem!important}
    .ds-btn{border-radius:11px!important;font-weight:700!important;font-size:.82rem!important;padding:.55rem 1.4rem!important}
    .ds-can{border-radius:11px!important;font-weight:700!important;font-size:.82rem!important;padding:.55rem 1.4rem!important;background:#f1f5f9!important;color:#475569!important;border:1.5px solid #e2e8f0!important}
    .ds-ico{border:none!important;margin-bottom:.4rem!important}
    .ds-tst{border-radius:14px!important;font-family:'Open Sans',sans-serif!important;box-shadow:0 8px 32px rgba(0,0,0,.14)!important;padding:.75rem 1.1rem!important;border-left:4px solid}
    .dst-ok{border-color:#059669!important}.dst-er{border-color:#dc2626!important}
  `,
  _done:false,
  _inject(){if(!this._done){const s=document.createElement('style');s.textContent=this._css;document.head.appendChild(s);this._done=true;}},
  toast(icon,title,text=''){this._inject();const cls={success:'dst-ok',error:'dst-er'}[icon]||'';Swal.fire({toast:true,position:'top-end',showConfirmButton:false,timer:3400,timerProgressBar:true,icon,title,text,customClass:{popup:`ds-tst ${cls}`}});},
  success(t,x=''){this.toast('success',t,x);},
  error(t,x=''){this._inject();Swal.fire({icon:'error',title:t,text:x||'Something went wrong.',customClass:{popup:'ds-pop',title:'ds-ttl',confirmButton:'ds-btn'},confirmButtonColor:'#dc2626',confirmButtonText:'Got it'});},
  loading(t='Processing…'){this._inject();Swal.fire({title:t,allowOutsideClick:false,customClass:{popup:'ds-pop',title:'ds-ttl'},didOpen:()=>Swal.showLoading()});},
  confirm({title,text,confirmText='Confirm',confirmColor='#dc2626',onConfirm}){
    this._inject();
    Swal.fire({title,text,icon:'warning',showCancelButton:true,confirmButtonText:confirmText,cancelButtonText:'Cancel',confirmButtonColor:confirmColor,reverseButtons:true,
      customClass:{popup:'ds-pop',title:'ds-ttl',confirmButton:'ds-btn',cancelButton:'ds-can',icon:'ds-ico'},
      showClass:{popup:'animate__animated animate__zoomIn animate__faster'},
      hideClass:{popup:'animate__animated animate__zoomOut animate__faster'}
    }).then(r=>{if(r.isConfirmed&&onConfirm)onConfirm();});
  }
};

const STATUS_CFG = {
  draft:     { accent:'#f59e0b', light:'#fef9c3', text:'#92400e', icon:'bi-pencil-square' },
  published: { accent:'#6366f1', light:'#ede9fe', text:'#4c1d95', icon:'bi-check-circle-fill' },
  archived:  { accent:'#9ca3af', light:'#f3f4f6', text:'#374151', icon:'bi-archive-fill' },
};
const TYPE_CFG = {
  manual: { label:'Manual', light:'#eff6ff', text:'#1a4fc4', icon:'bi-hand-index' },
  random: { label:'Random', light:'#fff7ed', text:'#c2410c', icon:'bi-shuffle' },
};

let allExams = [], currentStatus = '', currentView = 'grid';

function _qbetInit() {
  loadCounts();
  loadExams();
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', _qbetInit);
} else {
  _qbetInit();
}

function loadCounts() {
  fetch('ajax/ajax_qb_exams.php?action=counts')
    .then(r=>r.json()).then(res=>{
      if (res.status !== 'success') return;
      const d = res.data;
      document.getElementById('cnt_all').textContent       = d.total_exams;
      document.getElementById('cnt_draft').textContent     = d.draft;
      document.getElementById('cnt_published').textContent = d.published;
      document.getElementById('cnt_archived').textContent  = d.archived;

      document.getElementById('heroKpis').innerHTML = [
        {label:'Total Exams',    val: d.total_exams},
        {label:'Published',      val: d.published},
        {label:'Questions Used', val: d.total_questions_in_exams},
        {label:'Sessions',       val: d.sessions_count},
      ].map(k => `
        <div class="qbe-kpi">
          <div class="qbe-kpi-val">${(+k.val||0).toLocaleString()}</div>
          <div class="qbe-kpi-lbl">${k.label}</div>
        </div>`).join('');
    });
}

function loadExams() {
  const params = new URLSearchParams({ action:'list' });
  if (currentStatus) params.set('status', currentStatus);
  fetch('ajax/ajax_qb_exams.php?' + params)
    .then(r=>r.json()).then(res=>{
      allExams = res.status === 'success' ? res.data : [];
      renderExams();
    }).catch(() => renderError());
}

function filterStatus(st) {
  currentStatus = st;
  ['all','draft','published','archived'].forEach(s => {
    document.getElementById('pill_'+s).classList.toggle('active', (s==='all'&&st==='')||(s===st));
  });
  loadExams();
}

function filterExams() {
  const q    = document.getElementById('eSearch').value.toLowerCase();
  const type = document.getElementById('eType').value;
  const rows = allExams.filter(e =>
    (!q   || e.exam_title.toLowerCase().includes(q) || (e.exam_code||'').toLowerCase().includes(q) || (e.subject_name||'').toLowerCase().includes(q)) &&
    (!type || e.exam_type === type)
  );
  renderExamCards(rows);
}

function renderExams() { filterExams(); }

function renderError() {
  document.getElementById('eGrid').innerHTML = `
    <div class="qbe-empty">
      <i class="bi bi-exclamation-triangle qbe-empty-icon"></i>
      <div style="font-size:1rem;font-weight:800;color:#475569;margin-bottom:.35rem">Failed to load</div>
      <a class="qbe-hero-btn qbe-hero-btn-primary" onclick="loadExams()" style="display:inline-flex;margin-top:.75rem"><i class="bi bi-arrow-clockwise"></i>Retry</a>
    </div>`;
}

function setView(v) {
  currentView = v;
  document.getElementById('btnGrid').classList.toggle('active', v==='grid');
  document.getElementById('btnList').classList.toggle('active', v==='list');
  document.getElementById('eGrid').classList.toggle('list-mode', v==='list');
}

function renderExamCards(rows) {
  const grid = document.getElementById('eGrid');
  document.getElementById('eCount').textContent = `${rows.length} Exam${rows.length!==1?'s':''}`;

  if (!rows.length) {
    const q = document.getElementById('eSearch').value;
    grid.innerHTML = `
      <div class="qbe-empty">
        <i class="bi bi-layout-text-window qbe-empty-icon"></i>
        <div style="font-size:1rem;font-weight:800;color:#475569;margin-bottom:.35rem">${q ? 'No matches found' : 'No exams yet'}</div>
        <div style="font-size:.8rem;color:#94a3b8;margin-bottom:1.2rem">${q ? 'Try a different search term.' : 'Create your first exam to get started.'}</div>
        ${!q ? '<a href="?view=qb_create_exam" class="qbe-hero-btn qbe-hero-btn-primary" style="display:inline-flex;text-decoration:none"><i class="bi bi-plus-lg"></i>Create Exam</a>' : ''}
      </div>`;
    return;
  }

  grid.innerHTML = rows.map((e, i) => {
    const sc = STATUS_CFG[e.status] || STATUS_CFG.draft;
    const tc = TYPE_CFG[e.exam_type] || TYPE_CFG.manual;
    const passPct = e.total_marks > 0 ? Math.round((e.passing_marks / e.total_marks) * 100) : 0;

    // Publish/Archive action button
    let pubBtn = '';
    if (e.status === 'draft') {
      pubBtn = `<button class="qbe-pub-btn qbe-pub-btn-publish" onclick="changeStatus(${e.exam_id},'published')"><i class="bi bi-send-fill" style="font-size:.65rem"></i>Publish</button>`;
    } else if (e.status === 'published') {
      pubBtn = `<button class="qbe-pub-btn qbe-pub-btn-archive" onclick="changeStatus(${e.exam_id},'archived')"><i class="bi bi-archive" style="font-size:.65rem"></i>Archive</button>`;
    } else {
      pubBtn = `<button class="qbe-pub-btn qbe-pub-btn-draft" onclick="changeStatus(${e.exam_id},'draft')"><i class="bi bi-arrow-counterclockwise" style="font-size:.65rem"></i>Restore</button>`;
    }

    const dateStr = e.created_at ? new Date(e.created_at).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}) : '';

    return `
    <div class="qbe-card" style="animation-delay:${Math.min(i*0.04,.3)}s">
      <div class="qbe-card-accent" style="background:${sc.accent}"></div>
      <div class="qbe-card-body">
        <div class="qbe-card-top">
          <div>
            <span class="qbe-code-badge">${e.exam_code || '—'}</span>
            <span class="qbe-type-badge ms-1" style="background:${tc.light};color:${tc.text}"><i class="bi ${tc.icon}" style="font-size:.6rem"></i>${tc.label}</span>
          </div>
        </div>
        <div class="qbe-card-title">${e.exam_title}</div>
        <div class="qbe-card-meta">
          ${e.subject_name ? `<span class="qbe-meta-pill"><i class="bi bi-book" style="font-size:.6rem"></i>${e.subject_name}</span>` : ''}
          ${e.level_name   ? `<span class="qbe-meta-pill"><i class="bi bi-layers" style="font-size:.6rem"></i>${e.level_name}</span>` : ''}
        </div>
      </div>
      <div class="qbe-stats">
        <div class="qbe-stat">
          <div class="qbe-stat-val" style="color:#1a4fc4">${e.question_count || 0}</div>
          <div class="qbe-stat-lbl">Questions</div>
        </div>
        <div class="qbe-stat">
          <div class="qbe-stat-val" style="color:#059669">${parseFloat(e.total_marks||0).toFixed(0)}</div>
          <div class="qbe-stat-lbl">Total Marks</div>
        </div>
        <div class="qbe-stat">
          <div class="qbe-stat-val" style="color:#d97706">${e.duration_minutes || 0}</div>
          <div class="qbe-stat-lbl">Minutes</div>
        </div>
      </div>
      <div class="qbe-prog-wrap">
        <div class="qbe-prog-labels">
          <span style="color:${sc.text};font-weight:700">Pass Threshold</span>
          <span>${passPct}% (${parseFloat(e.passing_marks||0).toFixed(0)} marks)</span>
        </div>
        <div class="qbe-prog-track">
          <div class="qbe-prog-fill" data-pct="${passPct}" style="background:${sc.accent}"></div>
        </div>
      </div>
      <div class="qbe-card-foot">
        <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
          <span class="qbe-status-pill" style="background:${sc.light};color:${sc.text}">
            <i class="bi ${sc.icon}" style="font-size:.62rem"></i>${e.status}
          </span>
          ${pubBtn}
        </div>
        <div class="qbe-card-acts">
          <a href="?view=qb_create_exam&exam_id=${e.exam_id}" class="qbe-icn" title="Edit"><i class="bi bi-pencil"></i></a>
          <a href="?view=qb_print_exams&exam_id=${e.exam_id}" class="qbe-icn" title="Print"><i class="bi bi-printer"></i></a>
          <button class="qbe-icn qbe-icn-del" onclick="deleteExam(${e.exam_id},'${e.exam_title.replace(/'/g,"&#39;")}')" title="Delete"><i class="bi bi-trash"></i></button>
        </div>
      </div>
    </div>`;
  }).join('');

  requestAnimationFrame(() => {
    document.querySelectorAll('.qbe-prog-fill').forEach(el => { el.style.width = (el.dataset.pct || 0) + '%'; });
  });
}

function changeStatus(exam_id, status) {
  dcmAlert.loading('Updating…');
  fetch('ajax/ajax_qb_exams.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({action:'publish', exam_id, status})
  }).then(r=>r.json()).then(res=>{
    Swal.close();
    if (res.status === 'success') {
      dcmAlert.success('Status updated!', `Exam is now ${status}.`);
      loadCounts();
      loadExams();
    } else dcmAlert.error('Failed', res.message);
  }).catch(() => dcmAlert.error('Request failed', 'Unable to reach the server.'));
}

function deleteExam(id, title) {
  dcmAlert.confirm({
    title: 'Delete this exam?',
    text: `"${title}" and all its question assignments will be permanently removed.`,
    confirmText: '<i class="bi bi-trash me-1"></i>Yes, delete it',
    confirmColor: '#dc2626',
    onConfirm() {
      dcmAlert.loading('Deleting…');
      fetch('ajax/ajax_qb_exams.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({action:'delete', exam_id: id})
      }).then(r=>r.json()).then(res=>{
        Swal.close();
        if (res.status === 'success') {
          dcmAlert.success('Deleted!', 'Exam removed.');
          loadCounts();
          loadExams();
        } else dcmAlert.error('Delete failed', res.message);
      }).catch(() => dcmAlert.error('Request failed', 'Unable to reach the server.'));
    }
  });
}

Object.assign(window, { filterStatus, filterExams, setView, changeStatus, deleteExam, loadCounts, loadExams });
</script>
