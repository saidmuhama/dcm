<?php
// QB CBT Exams — Computer Based Testing Sessions
?>
<style>
/* ═══════════════════════════════════════════════════════════
   QB CBT EXAMS  (qbc-*)
═══════════════════════════════════════════════════════════ */
.qbc-wrap { font-family:'Open Sans',sans-serif; }
.qbc-hero { position:relative; overflow:hidden; isolation:isolate; border-radius:20px; padding:2rem 2.2rem; margin-bottom:1.4rem; background:linear-gradient(135deg,#052e16 0%,#064e3b 40%,#065f46 100%); }
.qbc-hero-grid { position:absolute; inset:0; z-index:0; background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px); background-size:44px 44px; }
.qbc-hero-inner { position:relative; z-index:1; }
.qbc-hero-badge { display:inline-flex; align-items:center; gap:.4rem; background:rgba(255,255,255,.09); border:1px solid rgba(255,255,255,.15); border-radius:100px; padding:.28rem .85rem; font-size:.7rem; font-weight:700; color:rgba(255,255,255,.7); letter-spacing:.06em; text-transform:uppercase; margin-bottom:.75rem; backdrop-filter:blur(6px); }
.qbc-hero-title { font-size:1.7rem; font-weight:900; color:#fff; font-family:'SUSE',sans-serif; letter-spacing:-.04em; line-height:1.15; margin-bottom:.3rem; }
.qbc-hero-title em { font-style:normal; background:linear-gradient(90deg,#6ee7b7,#34d399); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent; }
.qbc-hero-sub { font-size:.81rem; color:rgba(255,255,255,.45); margin-bottom:1.4rem; max-width:520px; line-height:1.6; }
.qbc-kpis { display:flex; gap:.7rem; flex-wrap:wrap; }
.qbc-kpi { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12); border-radius:14px; padding:.6rem 1rem; backdrop-filter:blur(8px); min-width:90px; transition:background .2s; }
.qbc-kpi:hover { background:rgba(255,255,255,.13); }
.qbc-kpi-val { font-size:1.2rem; font-weight:900; color:#fff; font-family:'SUSE',sans-serif; line-height:1; }
.qbc-kpi-lbl { font-size:.63rem; color:rgba(255,255,255,.45); margin-top:.15rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; }

/* ── Panels ── */
.qbc-panel { background:#fff; border-radius:18px; border:1px solid #f0f4f8; box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 16px rgba(0,0,0,.05); overflow:hidden; }
.qbc-panel-head { padding:.9rem 1.2rem; border-bottom:1px solid #f0f4f8; display:flex; align-items:center; gap:.55rem; }
.qbc-panel-title { font-size:.88rem; font-weight:800; color:#0f172a; font-family:'SUSE',sans-serif; }
.qbc-panel-body { padding:1.2rem; }

/* ── Form ── */
.qbc-ctrl-lbl { font-size:.74rem; font-weight:700; color:#475569; margin-bottom:.3rem; display:block; }
.qbc-ctrl { width:100%; padding:.52rem .85rem; border-radius:11px; border:1.5px solid #e2e8f0; font-size:.81rem; font-family:inherit; outline:none; background:#f8fafc; color:#1e293b; transition:border-color .18s,box-shadow .18s; }
.qbc-ctrl:focus { border-color:#059669; box-shadow:0 0 0 3px rgba(5,150,105,.1); background:#fff; }

.qbc-start-btn { display:inline-flex; align-items:center; gap:.45rem; background:linear-gradient(135deg,#059669,#0d9488); color:#fff; border:none; border-radius:12px; padding:.65rem 1.6rem; font-size:.85rem; font-weight:700; cursor:pointer; font-family:inherit; box-shadow:0 4px 16px rgba(5,150,105,.35); transition:filter .18s,transform .12s; width:100%; justify-content:center; margin-top:.75rem; }
.qbc-start-btn:hover { filter:brightness(1.08); transform:translateY(-1px); }

/* ── Student search results ── */
.qbc-stu-results { border:1.5px solid #e2e8f0; border-radius:11px; max-height:180px; overflow-y:auto; display:none; }
.qbc-stu-row { display:flex; align-items:center; gap:.6rem; padding:.55rem .8rem; cursor:pointer; transition:background .13s; border-bottom:1px solid #f0f4f8; }
.qbc-stu-row:last-child { border-bottom:none; }
.qbc-stu-row:hover { background:#f0fdf4; }
.qbc-stu-avatar { width:30px; height:30px; border-radius:50%; background:linear-gradient(135deg,#059669,#0d9488); color:#fff; font-size:.75rem; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.qbc-stu-name { font-size:.8rem; font-weight:700; color:#0f172a; }
.qbc-stu-code { font-size:.7rem; color:#64748b; }
.qbc-selected-stu { display:none; background:#f0fdf4; border:1.5px solid #bbf7d0; border-radius:11px; padding:.6rem .85rem; align-items:center; gap:.6rem; margin-top:.4rem; }
.qbc-selected-stu.show { display:flex; }

/* ── Status nav ── */
.qbc-status-nav { display:flex; gap:.4rem; flex-wrap:wrap; margin-bottom:1rem; }
.qbc-spill { display:inline-flex; align-items:center; gap:.4rem; padding:.35rem .85rem; border-radius:100px; font-size:.77rem; font-weight:700; cursor:pointer; border:1.5px solid #e2e8f0; background:#fff; color:#475569; transition:all .17s; }
.qbc-spill:hover:not(.active) { border-color:#cbd5e1; }
.qbc-spill.active { background:linear-gradient(135deg,#059669,#0d9488); color:#fff; border-color:transparent; box-shadow:0 4px 14px rgba(5,150,105,.3); }

/* ── Sessions table ── */
.qbc-table-wrap { background:#fff; border-radius:16px; border:1px solid #f0f4f8; box-shadow:0 1px 3px rgba(0,0,0,.04); overflow:hidden; }
.qbc-table { width:100%; border-collapse:collapse; font-size:.8rem; }
.qbc-table thead th { background:#f8fafc; color:#64748b; font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; padding:.6rem 1rem; border-bottom:1px solid #e2e8f0; white-space:nowrap; }
.qbc-table tbody tr { border-bottom:1px solid #f0f4f8; transition:background .13s; }
.qbc-table tbody tr:hover { background:#f8fafc; }
.qbc-table tbody tr:last-child { border-bottom:none; }
.qbc-table td { padding:.6rem 1rem; color:#334155; vertical-align:middle; }
.qbc-status-pill { display:inline-flex; align-items:center; gap:.28rem; border-radius:100px; padding:.18rem .6rem; font-size:.66rem; font-weight:800; text-transform:capitalize; }
.qbc-icn { width:28px; height:28px; border-radius:8px; border:1.5px solid #e2e8f0; background:#f8fafc; color:#64748b; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all .13s; font-size:.76rem; }
.qbc-icn:hover { background:#eff6ff; border-color:#bfdbfe; color:#1a4fc4; }
.qbc-icn-del:hover { background:#fff1f2; border-color:#fecaca; color:#dc2626; }

/* ── Skel ── */
.qbc-skel { background:linear-gradient(90deg,#f0f4f8 25%,#e2e8f0 50%,#f0f4f8 75%); background-size:200% 100%; animation:qbc-shim 1.5s infinite; border-radius:8px; }
@keyframes qbc-shim { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ── Results modal ── */
.qbc-modal .modal-content { border-radius:20px; border:none; box-shadow:0 24px 80px rgba(0,0,0,.18); overflow:hidden; font-family:'Open Sans',sans-serif; }
.qbc-modal .modal-header { border-bottom:none; padding:1.3rem 1.5rem; background:linear-gradient(135deg,#052e16,#064e3b); }
.qbc-modal .modal-title { font-size:.92rem; font-weight:800; color:#fff; font-family:'SUSE',sans-serif; display:flex; align-items:center; gap:.6rem; }
.qbc-modal .modal-body { padding:1.3rem 1.5rem; max-height:65vh; overflow-y:auto; }
.qbc-ans-row { border:1px solid #f0f4f8; border-radius:12px; padding:.85rem 1rem; margin-bottom:.65rem; }
.qbc-ans-row.correct { border-color:#bbf7d0; background:#f0fdf4; }
.qbc-ans-row.wrong { border-color:#fecaca; background:#fff1f2; }
</style>

<div class="container-fluid px-3 py-3 qbc-wrap">

<!-- ── Hero ──────────────────────────────────────────────── -->
<div class="qbc-hero">
  <div class="qbc-hero-grid"></div>
  <div style="position:absolute;right:3rem;top:50%;transform:translateY(-50%);width:220px;height:220px;border-radius:50%;background:conic-gradient(from 0deg,rgba(5,150,105,.45),rgba(13,148,136,.32),rgba(5,150,105,.45));filter:blur(42px);opacity:.55;animation:db-orb-spin 16s linear infinite;z-index:0"></div>
  <div style="position:absolute;left:30%;bottom:-40px;width:140px;height:140px;border-radius:50%;background:rgba(13,148,136,.32);filter:blur(36px);opacity:.35;z-index:0"></div>
  <div class="qbc-hero-inner">
    <div class="qbc-hero-badge"><i class="bi bi-display"></i>Computer Based Testing</div>
    <div class="qbc-hero-title"><em>CBT</em> Exam Sessions</div>
    <div class="qbc-hero-sub">Start, monitor, and review computer-based exam sessions. Track student performance, view results, and manage submission records.</div>
    <div class="qbc-kpis" id="heroKpis">
      <?php for ($ki=0;$ki<4;$ki++): ?>
      <div class="qbc-kpi"><div class="qbc-skel" style="width:44px;height:22px;margin-bottom:5px"></div><div class="qbc-kpi-lbl">Loading</div></div>
      <?php endfor; ?>
    </div>
  </div>
</div>

<div class="row g-3">

  <!-- LEFT: Start session -->
  <div class="col-lg-4">
    <div class="qbc-panel">
      <div class="qbc-panel-head" style="background:linear-gradient(135deg,#052e16,#064e3b)">
        <i class="bi bi-play-circle-fill" style="color:#34d399;font-size:1rem"></i>
        <div class="qbc-panel-title" style="color:#fff">Start New Session</div>
      </div>
      <div class="qbc-panel-body">
        <div class="mb-3">
          <label class="qbc-ctrl-lbl">Select Exam (Published)</label>
          <select id="sExam" class="qbc-ctrl">
            <option value="">Loading exams…</option>
          </select>
        </div>
        <div class="mb-2">
          <label class="qbc-ctrl-lbl">Search Student</label>
          <div style="position:relative">
            <input type="text" id="sStudentSearch" class="qbc-ctrl" placeholder="Type name or student code…" oninput="searchStudent()" autocomplete="off">
          </div>
          <div id="stuSearchResults" class="qbc-stu-results mt-1"></div>
          <div class="qbc-selected-stu" id="selectedStu">
            <div class="qbc-stu-avatar" id="stuInitials">?</div>
            <div>
              <div class="qbc-stu-name" id="stuName">—</div>
              <div class="qbc-stu-code" id="stuCode">—</div>
            </div>
            <button style="margin-left:auto;background:none;border:none;color:#64748b;cursor:pointer;font-size:.8rem" onclick="clearStudent()"><i class="bi bi-x-lg"></i></button>
          </div>
          <input type="hidden" id="sStudentId">
        </div>
        <button class="qbc-start-btn" onclick="startSession()">
          <i class="bi bi-play-fill"></i>Start Session
        </button>
      </div>
    </div>
  </div>

  <!-- RIGHT: Sessions list -->
  <div class="col-lg-8">
    <div class="qbc-status-nav">
      <button class="qbc-spill active" id="spill_all"         onclick="filterSessions('')">All</button>
      <button class="qbc-spill" id="spill_in_progress"        onclick="filterSessions('in_progress')"><i class="bi bi-hourglass-split" style="font-size:.7rem"></i>In Progress</button>
      <button class="qbc-spill" id="spill_submitted"          onclick="filterSessions('submitted')"><i class="bi bi-check-circle" style="font-size:.7rem"></i>Submitted</button>
      <button class="qbc-spill" id="spill_graded"             onclick="filterSessions('graded')"><i class="bi bi-patch-check" style="font-size:.7rem"></i>Graded</button>
    </div>
    <div class="qbc-table-wrap">
      <div style="padding:.6rem 1rem;border-bottom:1px solid #f0f4f8;display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:.77rem;color:#94a3b8;font-weight:600" id="sessCount">Loading…</span>
        <button style="font-size:.75rem;color:#1a4fc4;background:none;border:none;cursor:pointer;font-weight:700" onclick="loadSessions()"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</button>
      </div>
      <div class="table-responsive">
        <table class="qbc-table">
          <thead>
            <tr>
              <th>Exam</th>
              <th>Student</th>
              <th>Started</th>
              <th>Submitted</th>
              <th>Score</th>
              <th>Status</th>
              <th class="text-end" style="padding-right:1rem">Actions</th>
            </tr>
          </thead>
          <tbody id="sessBody">
            <tr><td colspan="7" style="text-align:center;padding:2rem">
              <div class="qbc-skel" style="height:40px;margin-bottom:.5rem"></div>
              <div class="qbc-skel" style="height:40px;margin-bottom:.5rem"></div>
              <div class="qbc-skel" style="height:40px"></div>
            </td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
</div><!-- /.container-fluid -->

<!-- ── Results Modal ──────────────────────────────────────── -->
<div class="modal fade qbc-modal" id="resultsModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">
          <div style="width:30px;height:30px;border-radius:9px;background:rgba(255,255,255,.13);display:flex;align-items:center;justify-content:center"><i class="bi bi-bar-chart-fill"></i></div>
          <span id="modalTitle">Session Results</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1) brightness(2);opacity:.7"></button>
      </div>
      <div class="modal-body" id="modalBody">
        <div class="text-center py-4"><div class="spinner-border spinner-border-sm text-success"></div></div>
      </div>
    </div>
  </div>
</div>

<script>
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
  in_progress: {bg:'#fef9c3', color:'#92400e', icon:'bi-hourglass-split'},
  submitted:   {bg:'#ede9fe', color:'#4c1d95', icon:'bi-check-circle-fill'},
  graded:      {bg:'#dcfce7', color:'#15803d', icon:'bi-patch-check-fill'},
};

let sessionStatus = '';
let resultsModal;
let stuTimer = null;

function _qbcbtInit() {
  resultsModal = new bootstrap.Modal(document.getElementById('resultsModal'));
  loadPublishedExams();
  loadCounts();
  loadSessions();
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', _qbcbtInit);
} else {
  _qbcbtInit();
}

function loadPublishedExams() {
  fetch('ajax/ajax_qb_exams.php?action=list&status=published')
    .then(r=>r.json()).then(res=>{
      const sel = document.getElementById('sExam');
      if (res.status !== 'success') { sel.innerHTML = '<option value="">No published exams found</option>'; return; }
      sel.innerHTML = '<option value="">— Select Exam —</option>';
      res.data.forEach(e => sel.add(new Option(`${e.exam_code || ''} ${e.exam_title}`.trim(), e.exam_id)));
    });
}

function loadCounts() {
  fetch('ajax/ajax_qb_exams.php?action=counts')
    .then(r=>r.json()).then(res=>{
      if (res.status !== 'success') return;
      const d = res.data;
      document.getElementById('heroKpis').innerHTML = [
        {label:'Total Sessions',  val: d.sessions_count},
        {label:'Published Exams', val: d.published},
        {label:'Total Exams',     val: d.total_exams},
        {label:'Qs in Exams',     val: d.total_questions_in_exams},
      ].map(k=>`<div class="qbc-kpi"><div class="qbc-kpi-val">${(+k.val||0).toLocaleString()}</div><div class="qbc-kpi-lbl">${k.label}</div></div>`).join('');
    });
}

function filterSessions(st) {
  sessionStatus = st;
  ['all','in_progress','submitted','graded'].forEach(s => {
    document.getElementById('spill_'+s).classList.toggle('active', (s==='all'&&st==='')||(s===st));
  });
  loadSessions();
}

function loadSessions() {
  const params = new URLSearchParams({action:'session_list'});
  if (sessionStatus) params.set('status', sessionStatus);
  fetch('ajax/ajax_qb_exams.php?' + params)
    .then(r=>r.json()).then(res=>{
      if (res.status !== 'success') { renderSessError(); return; }
      renderSessions(res.data);
    }).catch(() => renderSessError());
}

function renderSessions(rows) {
  const tbody = document.getElementById('sessBody');
  document.getElementById('sessCount').textContent = `${rows.length} session${rows.length!==1?'s':''}`;

  if (!rows.length) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:3rem">
      <i class="bi bi-inbox" style="font-size:2.5rem;color:#cbd5e1;display:block;margin-bottom:.75rem"></i>
      <div style="font-weight:800;color:#475569">No sessions yet</div>
      <div style="font-size:.79rem;color:#94a3b8;margin-top:.3rem">Start a new session using the panel on the left.</div>
    </td></tr>`;
    return;
  }

  tbody.innerHTML = rows.map(s => {
    const sc = STATUS_CFG[s.status] || {bg:'#f1f5f9',color:'#475569',icon:'bi-question-circle'};
    const started   = s.started_at   ? new Date(s.started_at).toLocaleString('en-GB',{day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit'}) : '—';
    const submitted = s.submitted_at ? new Date(s.submitted_at).toLocaleString('en-GB',{day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit'}) : '—';
    const scoreStr  = s.total_marks > 0 ? `${parseFloat(s.score).toFixed(1)} / ${parseFloat(s.total_marks).toFixed(1)}` : '—';
    const stuDisplay = (s.student_name||'').trim() || s.student_id || '—';
    return `
    <tr>
      <td style="font-size:.78rem;font-weight:700;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${s.exam_title || '—'}</td>
      <td style="font-size:.79rem">${stuDisplay}</td>
      <td style="font-size:.75rem;color:#64748b">${started}</td>
      <td style="font-size:.75rem;color:#64748b">${submitted}</td>
      <td style="font-size:.8rem;font-weight:700">${scoreStr}</td>
      <td><span class="qbc-status-pill" style="background:${sc.bg};color:${sc.color}"><i class="bi ${sc.icon}" style="font-size:.6rem"></i>${s.status.replace('_',' ')}</span></td>
      <td style="text-align:right;padding-right:.85rem">
        <div style="display:inline-flex;gap:.3rem">
          <button class="qbc-icn" onclick="viewResults(${s.session_id})" title="View Results"><i class="bi bi-bar-chart"></i></button>
          <button class="qbc-icn qbc-icn-del" onclick="deleteSession(${s.session_id})" title="Delete"><i class="bi bi-trash"></i></button>
        </div>
      </td>
    </tr>`;
  }).join('');
}

function renderSessError() {
  document.getElementById('sessBody').innerHTML = `<tr><td colspan="7" style="text-align:center;padding:2rem;color:#dc2626;font-size:.8rem">Failed to load sessions</td></tr>`;
}

/* ── Student search ───────────────────────────────────────── */
function searchStudent() {
  clearTimeout(stuTimer);
  const q = document.getElementById('sStudentSearch').value.trim();
  const res = document.getElementById('stuSearchResults');
  if (q.length < 2) { res.style.display = 'none'; return; }
  stuTimer = setTimeout(() => {
    fetch(`ajax/ajax_qb_exams.php?action=student_search&q=${encodeURIComponent(q)}`)
      .then(r=>r.json()).then(data=>{
        if (!data.data?.length) { res.style.display = 'none'; return; }
        res.innerHTML = data.data.map(u => {
          const name = `${u.first_name||''} ${u.last_name||''}`.trim() || u.usr_code;
          const init = name.split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase();
          return `<div class="qbc-stu-row" onclick="selectStudent('${u.usr_code}','${name.replace(/'/g,"\\'")}','${init}')">
            <div class="qbc-stu-avatar">${init}</div>
            <div><div class="qbc-stu-name">${name}</div><div class="qbc-stu-code">${u.usr_code}</div></div>
          </div>`;
        }).join('');
        res.style.display = 'block';
      });
  }, 300);
}

function selectStudent(code, name, init) {
  document.getElementById('sStudentId').value         = code;
  document.getElementById('sStudentSearch').value     = '';
  document.getElementById('stuSearchResults').style.display = 'none';
  document.getElementById('stuInitials').textContent  = init;
  document.getElementById('stuName').textContent      = name;
  document.getElementById('stuCode').textContent      = code;
  document.getElementById('selectedStu').classList.add('show');
}

function clearStudent() {
  document.getElementById('sStudentId').value = '';
  document.getElementById('selectedStu').classList.remove('show');
}

/* ── Start session ────────────────────────────────────────── */
function startSession() {
  const exam_id    = document.getElementById('sExam').value;
  const student_id = document.getElementById('sStudentId').value;
  if (!exam_id)    { dcmAlert.error('Select an exam first'); return; }
  if (!student_id) { dcmAlert.error('Select a student first'); return; }

  dcmAlert.loading('Starting session…');
  fetch('ajax/ajax_qb_exams.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({action:'start_session', exam_id:+exam_id, student_id})
  }).then(r=>r.json()).then(res=>{
    Swal.close();
    if (res.status === 'success') {
      dcmAlert.success('Session started!', 'A new session has been created.');
      clearStudent();
      document.getElementById('sExam').value = '';
      loadCounts();
      loadSessions();
    } else dcmAlert.error('Could not start', res.message);
  }).catch(() => dcmAlert.error('Request failed','Unable to reach server.'));
}

/* ── View results ─────────────────────────────────────────── */
function viewResults(session_id) {
  document.getElementById('modalBody').innerHTML = '<div style="text-align:center;padding:2.5rem"><div class="spinner-border spinner-border-sm text-success"></div></div>';
  resultsModal.show();

  fetch(`ajax/ajax_qb_exams.php?action=session_results&session_id=${session_id}`)
    .then(r=>r.json()).then(res=>{
      if (res.status !== 'success') {
        document.getElementById('modalBody').innerHTML = '<p class="text-danger">Could not load results.</p>'; return;
      }
      const s = res.data;
      document.getElementById('modalTitle').textContent = `Results — ${s.student_name || s.student_id}`;
      const scoreColor = s.score >= s.total_marks * 0.5 ? '#059669' : '#dc2626';

      let html = `
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-bottom:1.2rem">
          <div style="background:#f8fafc;border-radius:12px;padding:.75rem;text-align:center">
            <div style="font-size:1.3rem;font-weight:900;color:${scoreColor};font-family:'SUSE',sans-serif">${parseFloat(s.score).toFixed(1)}</div>
            <div style="font-size:.65rem;color:#94a3b8;font-weight:700;text-transform:uppercase">Score</div>
          </div>
          <div style="background:#f8fafc;border-radius:12px;padding:.75rem;text-align:center">
            <div style="font-size:1.3rem;font-weight:900;color:#1a4fc4;font-family:'SUSE',sans-serif">${parseFloat(s.total_marks).toFixed(1)}</div>
            <div style="font-size:.65rem;color:#94a3b8;font-weight:700;text-transform:uppercase">Total Marks</div>
          </div>
          <div style="background:#f8fafc;border-radius:12px;padding:.75rem;text-align:center">
            <div style="font-size:1.3rem;font-weight:900;color:#d97706;font-family:'SUSE',sans-serif">${s.answers?.length || 0}</div>
            <div style="font-size:.65rem;color:#94a3b8;font-weight:700;text-transform:uppercase">Answered</div>
          </div>
        </div>
        <hr style="border-color:#f0f4f8;margin-bottom:1rem">`;

      if (!s.answers?.length) {
        html += '<p style="color:#94a3b8;text-align:center;font-size:.82rem">No answers recorded for this session.</p>';
      } else {
        html += s.answers.map((a, i) => {
          const cls = a.is_correct ? 'correct' : 'wrong';
          const ico = a.is_correct ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle-fill text-danger"></i>';
          return `
          <div class="qbc-ans-row ${cls}">
            <div style="display:flex;align-items:flex-start;gap:.55rem;margin-bottom:.45rem">
              ${ico}
              <span style="font-family:monospace;font-size:.65rem;font-weight:800;background:#0f172a;color:#e2e8f0;padding:.1rem .4rem;border-radius:6px">${a.q_uid||'Q'+(i+1)}</span>
              <span style="font-size:.65rem;font-weight:700;color:#64748b;margin-left:auto">${parseFloat(a.marks_awarded||0).toFixed(1)} / ${parseFloat(a.marks||0).toFixed(1)} mk</span>
            </div>
            <div style="font-size:.8rem;color:#334155;margin-bottom:.4rem">${a.question_stem||''}</div>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;font-size:.77rem">
              <span style="color:#64748b">Answered: <strong>${a.answer_given||'—'}</strong></span>
              <span style="color:#059669">Correct: <strong>${a.correct_answer||'—'}</strong></span>
            </div>
          </div>`;
        }).join('');
      }

      document.getElementById('modalBody').innerHTML = html;
    });
}

/* ── Delete session ───────────────────────────────────────── */
function deleteSession(session_id) {
  dcmAlert.confirm({
    title: 'Delete this session?',
    text: 'All answers and results for this session will be permanently removed.',
    confirmText: '<i class="bi bi-trash me-1"></i>Yes, delete',
    confirmColor: '#dc2626',
    onConfirm() {
      dcmAlert.loading('Deleting…');
      fetch('ajax/ajax_qb_exams.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({action:'delete_session', session_id})
      }).then(r=>r.json()).then(res=>{
        Swal.close();
        if (res.status === 'success') { dcmAlert.success('Deleted!'); loadCounts(); loadSessions(); }
        else dcmAlert.error('Delete failed', res.message);
      }).catch(() => dcmAlert.error('Request failed','Unable to reach server.'));
    }
  });
}

Object.assign(window, {
  filterSessions, loadSessions, searchStudent, selectStudent, clearStudent,
  startSession, viewResults, deleteSession, loadCounts, loadPublishedExams
});
</script>
