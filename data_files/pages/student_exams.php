<?php
$student_name = $_SESSION['name']     ?? 'Student';
$student_code = $_SESSION['usr_code'] ?? '';
?>
<style>
/* ══ Student Exam Portal (sep-*) ══════════════════════════════ */
.sep-wrap { font-family:'Open Sans',sans-serif; }

/* Hero */
.sep-hero { position:relative; overflow:hidden; isolation:isolate; border-radius:20px;
            padding:2rem 2.4rem; margin-bottom:1.4rem;
            background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 45%,#0c1445 100%); }
.sep-hero-grid { position:absolute;inset:0;z-index:0;
                 background-image:linear-gradient(rgba(255,255,255,.02) 1px,transparent 1px),
                                  linear-gradient(90deg,rgba(255,255,255,.02) 1px,transparent 1px);
                 background-size:48px 48px; }
.sep-hero-inner { position:relative;z-index:1; }
.sep-hero-badge { display:inline-flex;align-items:center;gap:.4rem;background:rgba(255,255,255,.1);
                  border:1px solid rgba(255,255,255,.15);border-radius:100px;padding:.28rem .9rem;
                  font-size:.7rem;font-weight:700;color:rgba(255,255,255,.7);letter-spacing:.06em;
                  text-transform:uppercase;margin-bottom:.75rem;backdrop-filter:blur(6px); }
.sep-hero-title { font-size:1.75rem;font-weight:900;color:#fff;font-family:'SUSE',sans-serif;
                  letter-spacing:-.04em;line-height:1.15;margin-bottom:.25rem; }
.sep-hero-title em { font-style:normal;background:linear-gradient(90deg,#818cf8,#c084fc);
                     -webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent; }
.sep-hero-sub { font-size:.82rem;color:rgba(255,255,255,.45);margin-bottom:1.4rem;max-width:520px;line-height:1.6; }

/* KPI cards */
.sep-kpis { display:flex;gap:.7rem;flex-wrap:wrap; }
.sep-kpi { background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);
           border-radius:14px;padding:.65rem 1.1rem;backdrop-filter:blur(8px);min-width:90px;
           transition:background .2s;cursor:default; }
.sep-kpi:hover { background:rgba(255,255,255,.12); }
.sep-kpi-val { font-size:1.3rem;font-weight:900;color:#fff;font-family:'SUSE',sans-serif;line-height:1; }
.sep-kpi-lbl { font-size:.63rem;color:rgba(255,255,255,.45);margin-top:.15rem;font-weight:600;
               text-transform:uppercase;letter-spacing:.05em; }

/* Tabs */
.sep-tabs { display:flex;gap:.35rem;margin-bottom:1.1rem;border-bottom:2px solid #f0f4f8;padding-bottom:0; }
.sep-tab { padding:.55rem 1.2rem;font-size:.82rem;font-weight:700;color:#64748b;border:none;
           background:none;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;
           transition:color .15s,border-color .15s;display:flex;align-items:center;gap:.4rem; }
.sep-tab.active { color:#4f46e5;border-bottom-color:#4f46e5; }
.sep-tab-cnt { font-size:.65rem;font-weight:800;padding:.05rem .42rem;border-radius:100px;
               background:#f1f5f9;color:#64748b;min-width:18px;text-align:center; }
.sep-tab.active .sep-tab-cnt { background:#ede9fe;color:#4f46e5; }

/* Search toolbar */
.sep-toolbar { display:flex;align-items:center;gap:.65rem;flex-wrap:wrap;
               background:#fff;border-radius:16px;padding:.8rem 1.1rem;margin-bottom:1.1rem;
               box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 14px rgba(0,0,0,.04);border:1px solid #f0f4f8; }
.sep-search-wrap { position:relative;flex:1;min-width:180px; }
.sep-search-wrap i { position:absolute;left:.75rem;top:50%;transform:translateY(-50%);
                     color:#94a3b8;font-size:.84rem;pointer-events:none; }
.sep-search { width:100%;padding:.5rem .85rem .5rem 2.2rem;border-radius:10px;
              border:1.5px solid #e2e8f0;font-size:.82rem;font-family:inherit;outline:none;
              background:#f8fafc;color:#1e293b;transition:border-color .18s,box-shadow .18s; }
.sep-search:focus { border-color:#4f46e5;box-shadow:0 0 0 3px rgba(79,70,229,.1);background:#fff; }
.sep-sel { padding:.5rem .75rem;border-radius:10px;border:1.5px solid #e2e8f0;font-size:.79rem;
           font-family:inherit;outline:none;background:#f8fafc;color:#475569;cursor:pointer; }
.sep-badge { background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;border-radius:100px;
             padding:.25rem .85rem;font-size:.72rem;font-weight:800;white-space:nowrap;font-family:'SUSE',sans-serif; }

/* Exam cards */
.sep-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(310px,1fr));gap:1rem; }
.sep-card { background:#fff;border-radius:18px;border:1px solid #f0f4f8;
            box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 16px rgba(0,0,0,.04);
            overflow:hidden;transition:transform .22s,box-shadow .22s;
            animation:sep-up .4s cubic-bezier(.16,1,.3,1) both; }
.sep-card:hover { transform:translateY(-4px);box-shadow:0 14px 44px rgba(0,0,0,.1); }
@keyframes sep-up { from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)} }
.sep-card-stripe { height:4px; }
.sep-card-body { padding:1.1rem 1.2rem .9rem; }
.sep-card-top { display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;margin-bottom:.7rem; }
.sep-code { font-family:monospace;font-size:.68rem;font-weight:800;background:#0f172a;color:#e2e8f0;
            padding:.16rem .55rem;border-radius:7px;letter-spacing:.04em; }
.sep-status-pill { display:inline-flex;align-items:center;gap:.3rem;border-radius:100px;
                   padding:.2rem .65rem;font-size:.66rem;font-weight:800;letter-spacing:.04em; }
.sep-card-title { font-size:1rem;font-weight:800;color:#0f172a;line-height:1.25;
                  font-family:'SUSE',sans-serif;margin-bottom:.55rem; }
.sep-meta { display:flex;flex-wrap:wrap;gap:.3rem;margin-bottom:.85rem; }
.sep-pill { display:inline-flex;align-items:center;gap:.25rem;border-radius:100px;
            padding:.15rem .55rem;font-size:.65rem;font-weight:700;background:#f1f5f9;color:#475569; }

/* Stat strip */
.sep-stats { display:flex;gap:.4rem;padding:0 1.2rem .85rem; }
.sep-stat { flex:1;background:#f8fafc;border-radius:10px;padding:.5rem .55rem;border:1px solid #f0f4f8;text-align:center; }
.sep-stat-val { font-size:.95rem;font-weight:800;color:#0f172a;font-family:'SUSE',sans-serif;line-height:1; }
.sep-stat-lbl { font-size:.58rem;color:#94a3b8;font-weight:600;margin-top:.12rem;text-transform:uppercase;letter-spacing:.04em; }

/* Card footer */
.sep-card-foot { padding:.75rem 1.2rem;background:#fafbfd;border-top:1px solid #f0f4f8;
                 display:flex;align-items:center;justify-content:space-between;gap:.5rem;flex-wrap:wrap; }
.sep-start-btn { display:inline-flex;align-items:center;gap:.4rem;border-radius:11px;padding:.52rem 1.2rem;
                 font-size:.8rem;font-weight:700;cursor:pointer;border:none;font-family:inherit;
                 transition:filter .18s,transform .12s;text-decoration:none; }
.sep-start-btn:hover { filter:brightness(1.08);transform:translateY(-1px); }
.sep-btn-start    { background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;box-shadow:0 4px 14px rgba(79,70,229,.3); }
.sep-btn-resume   { background:linear-gradient(135deg,#d97706,#b45309);color:#fff;box-shadow:0 4px 14px rgba(217,119,6,.3); }
.sep-btn-review   { background:linear-gradient(135deg,#059669,#0d9488);color:#fff;box-shadow:0 4px 14px rgba(5,150,105,.3); }
.sep-score-badge  { font-size:.75rem;font-weight:800;color:#374151;background:#f3f4f6;
                    border-radius:100px;padding:.22rem .75rem; }

/* History table */
.sep-table-wrap { background:#fff;border-radius:18px;border:1px solid #f0f4f8;overflow:hidden;
                  box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 14px rgba(0,0,0,.04); }
.sep-table { width:100%;border-collapse:collapse; }
.sep-table th { padding:.75rem 1rem;font-size:.72rem;font-weight:800;color:#64748b;text-align:left;
                background:#f8fafc;border-bottom:1.5px solid #f0f4f8;letter-spacing:.04em;text-transform:uppercase; }
.sep-table td { padding:.75rem 1rem;font-size:.81rem;color:#334155;border-bottom:1px solid #f8fafc;vertical-align:middle; }
.sep-table tr:last-child td { border-bottom:none; }
.sep-table tr:hover td { background:#f8fafc; }
.sep-score-bar { width:80px;height:6px;background:#e2e8f0;border-radius:100px;overflow:hidden;display:inline-block;vertical-align:middle;margin-left:.4rem; }
.sep-score-fill { height:100%;border-radius:100px;transition:width 1.2s cubic-bezier(.16,1,.3,1); }
.sep-result-btn { display:inline-flex;align-items:center;gap:.3rem;padding:.32rem .8rem;border-radius:8px;
                  font-size:.75rem;font-weight:700;border:none;cursor:pointer;font-family:inherit;
                  background:#ede9fe;color:#5b21b6;transition:background .15s; }
.sep-result-btn:hover { background:#5b21b6;color:#fff; }
.sep-resume-btn2 { background:#fef3c7;color:#92400e; }
.sep-resume-btn2:hover { background:#92400e;color:#fff; }

/* Skeleton */
.sep-skel { background:linear-gradient(90deg,#f0f4f8 25%,#e2e8f0 50%,#f0f4f8 75%);
            background-size:200% 100%;animation:sep-shim 1.5s infinite;border-radius:8px; }
@keyframes sep-shim { 0%{background-position:200% 0}100%{background-position:-200% 0} }

/* Empty */
.sep-empty { text-align:center;padding:4rem 2rem;background:#fff;border-radius:18px;border:1.5px dashed #e2e8f0; }
.sep-empty-icon { font-size:3.5rem;color:#e2e8f0;display:block;margin-bottom:1rem; }

/* Panel hidden/visible */
.sep-panel { display:none; }
.sep-panel.active { display:block; }
</style>

<div class="container-fluid px-3 py-3 sep-wrap">

<!-- Hero -->
<div class="sep-hero">
  <div class="sep-hero-grid"></div>
  <div style="position:absolute;right:3rem;top:50%;transform:translateY(-50%);width:200px;height:200px;border-radius:50%;background:conic-gradient(from 0deg,rgba(79,70,229,.4),rgba(124,58,237,.3),rgba(79,70,229,.4));filter:blur(40px);opacity:.5;animation:db-orb-spin 18s linear infinite;z-index:0"></div>

  <div class="sep-hero-inner">
    <div class="sep-hero-badge"><i class="bi bi-mortarboard-fill"></i>Student Exam Centre</div>
    <div class="sep-hero-title">Your <em>Exams</em></div>
    <div class="sep-hero-sub">Take published exams, track your performance, and review detailed results. Good luck, <?= htmlspecialchars(explode(' ', $student_name)[0]) ?>!</div>
    <div class="sep-kpis" id="heroKpis">
      <?php for ($i=0;$i<4;$i++): ?>
      <div class="sep-kpi"><div class="sep-skel" style="width:44px;height:24px;margin-bottom:5px"></div><div class="sep-kpi-lbl">Loading</div></div>
      <?php endfor; ?>
    </div>
  </div>
</div>

<!-- Tabs -->
<div class="sep-tabs">
  <button class="sep-tab active" id="tab_available" onclick="switchTab('available')">
    <i class="bi bi-collection-fill" style="font-size:.75rem"></i>Available Exams
    <span class="sep-tab-cnt" id="tab_cnt_available">…</span>
  </button>
  <button class="sep-tab" id="tab_history" onclick="switchTab('history')">
    <i class="bi bi-clock-history" style="font-size:.75rem"></i>My History
    <span class="sep-tab-cnt" id="tab_cnt_history">…</span>
  </button>
</div>

<!-- ── AVAILABLE EXAMS PANEL ──────────────────────────────── -->
<div class="sep-panel active" id="panel_available">
  <div class="sep-toolbar">
    <div class="sep-search-wrap">
      <i class="bi bi-search"></i>
      <input type="text" id="availSearch" class="sep-search" placeholder="Search exams…" oninput="filterAvailable()">
    </div>
    <select id="availSubject" class="sep-sel" onchange="filterAvailable()">
      <option value="">All Subjects</option>
    </select>
    <select id="availStatus" class="sep-sel" onchange="filterAvailable()">
      <option value="">All Status</option>
      <option value="new">Not Started</option>
      <option value="in_progress">In Progress</option>
      <option value="submitted">Completed</option>
    </select>
    <div class="sep-badge" id="availCount">…</div>
  </div>
  <div class="sep-grid" id="availGrid">
    <?php for ($i=0;$i<6;$i++): ?>
    <div class="sep-card">
      <div class="sep-card-stripe sep-skel"></div>
      <div class="sep-card-body">
        <div class="sep-skel" style="width:75%;height:16px;margin-bottom:8px"></div>
        <div class="sep-skel" style="width:55%;height:13px;margin-bottom:10px"></div>
        <div style="display:flex;gap:.3rem"><div class="sep-skel" style="width:65px;height:20px;border-radius:100px"></div><div class="sep-skel" style="width:55px;height:20px;border-radius:100px"></div></div>
      </div>
      <div class="sep-stats"><div class="sep-stat"><div class="sep-skel" style="width:100%;height:34px;border-radius:8px"></div></div><div class="sep-stat"><div class="sep-skel" style="width:100%;height:34px;border-radius:8px"></div></div><div class="sep-stat"><div class="sep-skel" style="width:100%;height:34px;border-radius:8px"></div></div></div>
      <div class="sep-card-foot"><div class="sep-skel" style="width:65px;height:22px;border-radius:100px"></div><div class="sep-skel" style="width:90px;height:34px;border-radius:11px"></div></div>
    </div>
    <?php endfor; ?>
  </div>
</div>

<!-- ── HISTORY PANEL ───────────────────────────────────────── -->
<div class="sep-panel" id="panel_history">
  <div class="sep-toolbar">
    <div class="sep-search-wrap">
      <i class="bi bi-search"></i>
      <input type="text" id="histSearch" class="sep-search" placeholder="Search history…" oninput="filterHistory()">
    </div>
    <select id="histStatus" class="sep-sel" onchange="filterHistory()">
      <option value="">All Status</option>
      <option value="submitted">Submitted</option>
      <option value="in_progress">In Progress</option>
    </select>
    <div class="sep-badge" id="histCount">…</div>
  </div>
  <div class="sep-table-wrap">
    <table class="sep-table">
      <thead>
        <tr>
          <th>Exam</th>
          <th>Subject</th>
          <th>Date</th>
          <th>Score</th>
          <th>Time</th>
          <th>Result</th>
          <th></th>
        </tr>
      </thead>
      <tbody id="histBody">
        <tr><td colspan="7" style="text-align:center;padding:3rem;color:#94a3b8"><i class="bi bi-hourglass-split" style="font-size:1.5rem;display:block;margin-bottom:.5rem"></i>Loading history…</td></tr>
      </tbody>
    </table>
  </div>
</div>

</div>

<script>
let allExams = [], allHistory = [], activeTab = 'available';

/* ── Init ─────────────────────────────────────────────────── */
function _sepInit() {
  loadStats();
  loadAvailable();
  loadHistory();
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', _sepInit);
} else { _sepInit(); }

function switchTab(tab) {
  activeTab = tab;
  document.getElementById('panel_available').classList.toggle('active', tab === 'available');
  document.getElementById('panel_history').classList.toggle('active', tab === 'history');
  document.getElementById('tab_available').classList.toggle('active', tab === 'available');
  document.getElementById('tab_history').classList.toggle('active', tab === 'history');
}

/* ── Stats ────────────────────────────────────────────────── */
function loadStats() {
  fetch('ajax/ajax_student_exam.php?action=stats')
    .then(r=>r.json()).then(res => {
      if (res.status !== 'success') return;
      const d = res.data;
      const avgPct = d.avg_pct ? parseFloat(d.avg_pct).toFixed(1) + '%' : '—';
      document.getElementById('heroKpis').innerHTML = [
        {val: d.completed     || 0, lbl:'Completed'},
        {val: d.in_progress   || 0, lbl:'In Progress'},
        {val: d.passed        || 0, lbl:'Passed'},
        {val: avgPct,               lbl:'Avg Score'},
      ].map(k=>`<div class="sep-kpi">
        <div class="sep-kpi-val">${String(k.val).replace(/\B(?=(\d{3})+(?!\d))/g,',')}</div>
        <div class="sep-kpi-lbl">${k.lbl}</div></div>`).join('');
    });
}

/* ── Available Exams ──────────────────────────────────────── */
function loadAvailable() {
  fetch('ajax/ajax_student_exam.php?action=available')
    .then(r=>r.json()).then(res => {
      allExams = res.status === 'success' ? res.data : [];
      document.getElementById('tab_cnt_available').textContent = allExams.length;
      // Populate subject filter
      const subjects = [...new Set(allExams.map(e=>e.subject_name).filter(Boolean))].sort();
      const sel = document.getElementById('availSubject');
      subjects.forEach(s => { const o = new Option(s,s); sel.add(o); });
      renderAvailable(allExams);
    }).catch(() => {
      document.getElementById('availGrid').innerHTML = '<div class="sep-empty" style="grid-column:1/-1"><i class="bi bi-exclamation-triangle sep-empty-icon"></i><div style="font-size:1rem;font-weight:700;color:#475569">Failed to load exams</div></div>';
    });
}

function filterAvailable() {
  const q  = document.getElementById('availSearch').value.toLowerCase();
  const sb = document.getElementById('availSubject').value;
  const st = document.getElementById('availStatus').value;
  const rows = allExams.filter(e => {
    const matchQ  = !q  || e.exam_title.toLowerCase().includes(q) || (e.subject_name||'').toLowerCase().includes(q);
    const matchSb = !sb || e.subject_name === sb;
    const myStatus = e.my_latest_status || 'new';
    const matchSt = !st || (st === 'new' ? !e.my_session_id : myStatus === st);
    return matchQ && matchSb && matchSt;
  });
  renderAvailable(rows);
}

const STRIPE_COLORS = ['#4f46e5','#7c3aed','#0ea5e9','#059669','#d97706','#dc2626','#0891b2'];

function renderAvailable(rows) {
  const grid = document.getElementById('availGrid');
  document.getElementById('availCount').textContent = `${rows.length} Exam${rows.length!==1?'s':''}`;
  if (!rows.length) {
    grid.innerHTML = `<div class="sep-empty" style="grid-column:1/-1">
      <i class="bi bi-inbox sep-empty-icon"></i>
      <div style="font-size:1rem;font-weight:700;color:#475569;margin-bottom:.35rem">No exams found</div>
      <div style="font-size:.8rem;color:#94a3b8">Try adjusting the filters.</div></div>`;
    return;
  }
  grid.innerHTML = rows.map((e, i) => {
    const stripe  = STRIPE_COLORS[i % STRIPE_COLORS.length];
    const myStatus = e.my_latest_status || 'new';
    const passPct  = e.total_marks > 0 ? Math.round((e.passing_marks / e.total_marks) * 100) : 0;

    let statusPill = '', actionBtn = '';
    if (myStatus === 'in_progress') {
      statusPill = `<span class="sep-status-pill" style="background:#fef3c7;color:#92400e"><i class="bi bi-hourglass-split" style="font-size:.6rem"></i>In Progress</span>`;
      actionBtn  = `<button class="sep-start-btn sep-btn-resume" onclick="startExam(${e.exam_id})"><i class="bi bi-play-fill"></i>Resume</button>`;
    } else if (myStatus === 'submitted' || myStatus === 'graded') {
      const pct = e.total_marks > 0 ? Math.round((e.my_best_score / e.total_marks) * 100) : 0;
      statusPill = `<span class="sep-status-pill" style="background:#dcfce7;color:#166534"><i class="bi bi-check-circle-fill" style="font-size:.6rem"></i>Completed</span>`;
      actionBtn  = `<button class="sep-start-btn sep-btn-review" onclick="viewResults(${e.my_session_id})"><i class="bi bi-bar-chart"></i>Results</button>
                   <span class="sep-score-badge">${pct}%</span>`;
    } else {
      statusPill = `<span class="sep-status-pill" style="background:#ede9fe;color:#5b21b6"><i class="bi bi-circle" style="font-size:.6rem"></i>Not Started</span>`;
      actionBtn  = `<button class="sep-start-btn sep-btn-start" onclick="startExam(${e.exam_id})"><i class="bi bi-play-fill"></i>Start Exam</button>`;
    }
    return `
    <div class="sep-card" style="animation-delay:${Math.min(i*.04,.3)}s">
      <div class="sep-card-stripe" style="background:${stripe}"></div>
      <div class="sep-card-body">
        <div class="sep-card-top">
          <div><span class="sep-code">${e.exam_code||'—'}</span></div>
          ${statusPill}
        </div>
        <div class="sep-card-title">${e.exam_title}</div>
        <div class="sep-meta">
          ${e.subject_name?`<span class="sep-pill"><i class="bi bi-book" style="font-size:.6rem"></i>${e.subject_name}</span>`:''}
          ${e.level_name  ?`<span class="sep-pill"><i class="bi bi-layers" style="font-size:.6rem"></i>${e.level_name}</span>`:''}
          <span class="sep-pill"><i class="bi bi-shield-check" style="font-size:.6rem"></i>Pass: ${passPct}%</span>
        </div>
      </div>
      <div class="sep-stats">
        <div class="sep-stat"><div class="sep-stat-val" style="color:#4f46e5">${e.question_count||0}</div><div class="sep-stat-lbl">Questions</div></div>
        <div class="sep-stat"><div class="sep-stat-val" style="color:#0ea5e9">${parseFloat(e.total_marks||0).toFixed(0)}</div><div class="sep-stat-lbl">Marks</div></div>
        <div class="sep-stat"><div class="sep-stat-val" style="color:#d97706">${e.duration_minutes||0}</div><div class="sep-stat-lbl">Minutes</div></div>
      </div>
      <div class="sep-card-foot">${actionBtn}</div>
    </div>`;
  }).join('');
}

/* ── History ──────────────────────────────────────────────── */
function loadHistory() {
  fetch('ajax/ajax_student_exam.php?action=history')
    .then(r=>r.json()).then(res => {
      allHistory = res.status === 'success' ? res.data : [];
      document.getElementById('tab_cnt_history').textContent = allHistory.length;
      renderHistory(allHistory);
    });
}

function filterHistory() {
  const q  = document.getElementById('histSearch').value.toLowerCase();
  const st = document.getElementById('histStatus').value;
  const rows = allHistory.filter(h =>
    (!q  || h.exam_title.toLowerCase().includes(q) || (h.subject_name||'').toLowerCase().includes(q)) &&
    (!st || h.status === st)
  );
  renderHistory(rows);
}

function renderHistory(rows) {
  document.getElementById('histCount').textContent = `${rows.length} Record${rows.length!==1?'s':''}`;
  const tbody = document.getElementById('histBody');
  if (!rows.length) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:3rem;color:#94a3b8">
      <i class="bi bi-journal-x" style="font-size:2rem;display:block;margin-bottom:.5rem"></i>No history yet</td></tr>`;
    return;
  }
  tbody.innerHTML = rows.map(h => {
    const pct    = h.total_marks > 0 ? Math.round((h.score/h.total_marks)*100) : 0;
    const passed = h.score >= parseFloat(h.passing_marks);
    const date   = h.started_at ? new Date(h.started_at).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}) : '—';
    const mins   = h.time_taken_seconds ? Math.floor(h.time_taken_seconds/60)+'m '+Math.round(h.time_taken_seconds%60)+'s' : '—';
    const barColor = pct >= 80 ? '#059669' : pct >= 50 ? '#d97706' : '#dc2626';
    const pill = h.status === 'in_progress'
      ? `<span class="sep-status-pill" style="background:#fef3c7;color:#92400e">In Progress</span>`
      : passed
      ? `<span class="sep-status-pill" style="background:#dcfce7;color:#166534">Passed</span>`
      : `<span class="sep-status-pill" style="background:#fee2e2;color:#991b1b">Failed</span>`;
    const action = h.status === 'in_progress'
      ? `<button class="sep-result-btn sep-resume-btn2" onclick="startExam(${h.exam_id})"><i class="bi bi-play-fill"></i>Resume</button>`
      : `<button class="sep-result-btn" onclick="viewResults(${h.session_id})"><i class="bi bi-bar-chart"></i>Results</button>`;
    return `<tr>
      <td><div style="font-weight:700;color:#0f172a">${h.exam_title}</div><div style="font-size:.7rem;color:#94a3b8;font-family:monospace">${h.exam_code||''}</div></td>
      <td>${h.subject_name||'—'}</td>
      <td>${date}</td>
      <td>
        ${h.status==='in_progress'?'<span style="color:#94a3b8">—</span>':`
          <span style="font-weight:700;color:${barColor}">${pct}%</span>
          <span class="sep-score-bar"><span class="sep-score-fill" style="width:0%;background:${barColor}" data-w="${pct}"></span></span>
          <div style="font-size:.68rem;color:#94a3b8">${parseFloat(h.score).toFixed(1)} / ${parseFloat(h.total_marks).toFixed(1)}</div>`}
      </td>
      <td>${mins}</td>
      <td>${pill}</td>
      <td>${action}</td>
    </tr>`;
  }).join('');
  // Animate score bars
  requestAnimationFrame(() => {
    document.querySelectorAll('.sep-score-fill').forEach(el => { el.style.width = (el.dataset.w||0)+'%'; });
  });
}

/* ── Actions ──────────────────────────────────────────────── */
function startExam(exam_id) {
  Swal.fire({
    title:'Starting exam…',allowOutsideClick:false,
    customClass:{popup:'ds-pop'},
    didOpen:()=>Swal.showLoading()
  });
  fetch('ajax/ajax_student_exam.php',{
    method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({action:'start',exam_id})
  }).then(r=>r.json()).then(res=>{
    Swal.close();
    if (res.status==='success') {
      window.location.href = `?view=student_take_exam&session_id=${res.session_id}`;
    } else {
      Swal.fire({icon:'error',title:'Could not start',text:res.message});
    }
  }).catch(()=>Swal.fire({icon:'error',title:'Error','text':'Network error'}));
}

function viewResults(session_id) {
  window.location.href = `?view=student_exam_results&session_id=${session_id}`;
}

Object.assign(window, { switchTab, filterAvailable, filterHistory, startExam, viewResults });
</script>
