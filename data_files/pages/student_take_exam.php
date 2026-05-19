<?php
$session_id = (int)($_GET['session_id'] ?? 0);
if (!$session_id) {
    echo '<div style="padding:3rem;text-align:center;color:#dc2626;font-weight:700">No session specified. <a href="?view=student_exams">Back to Exams</a></div>';
    return;
}
?>
<style>
/* ══ Student Take Exam (ste-*) ════════════════════════════════ */
* { box-sizing:border-box; }
.ste-wrap { font-family:'Open Sans',sans-serif;min-height:100vh;background:#f8fafc;padding:0; }

/* ── Top bar ── */
.ste-topbar { position:sticky;top:0;z-index:100;background:#fff;border-bottom:1px solid #e2e8f0;
              display:flex;align-items:center;gap:1rem;padding:.75rem 1.5rem;
              box-shadow:0 2px 12px rgba(0,0,0,.06); }
.ste-exam-title { font-weight:800;color:#0f172a;font-size:.88rem;font-family:'SUSE',sans-serif;flex:1;min-width:0;
                  white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.ste-progress-wrap { display:flex;align-items:center;gap:.6rem;flex-shrink:0; }
.ste-progress-ring { width:44px;height:44px;flex-shrink:0; }
.ste-progress-txt { font-size:.72rem;font-weight:700;color:#64748b;white-space:nowrap; }
.ste-timer { display:flex;align-items:center;gap:.4rem;font-size:.95rem;font-weight:800;
             font-family:'SUSE',sans-serif;color:#0f172a;padding:.4rem .9rem;border-radius:10px;
             border:1.5px solid #e2e8f0;background:#f8fafc;transition:all .4s;white-space:nowrap; }
.ste-timer.warn { color:#d97706;border-color:#fde68a;background:#fef3c7; }
.ste-timer.danger { color:#dc2626;border-color:#fecaca;background:#fee2e2;animation:ste-pulse .8s infinite; }
@keyframes ste-pulse { 0%,100%{opacity:1}50%{opacity:.7} }
.ste-submit-topbar { display:inline-flex;align-items:center;gap:.4rem;border-radius:10px;
                     padding:.45rem 1.1rem;font-size:.8rem;font-weight:700;cursor:pointer;border:none;
                     font-family:inherit;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;
                     box-shadow:0 4px 14px rgba(79,70,229,.3);transition:filter .18s;flex-shrink:0; }
.ste-submit-topbar:hover { filter:brightness(1.08); }

/* ── Main layout ── */
.ste-layout { display:grid;grid-template-columns:220px 1fr;gap:1rem;padding:1rem 1.5rem;min-height:calc(100vh - 64px); }
@media(max-width:768px){.ste-layout{grid-template-columns:1fr;}.ste-nav-panel{order:2;}}

/* ── Nav panel ── */
.ste-nav-panel { background:#fff;border-radius:18px;border:1px solid #f0f4f8;
                 box-shadow:0 1px 3px rgba(0,0,0,.05);padding:1rem;align-self:start;
                 position:sticky;top:74px; }
.ste-nav-head { font-size:.7rem;font-weight:800;color:#94a3b8;text-transform:uppercase;
                letter-spacing:.06em;margin-bottom:.65rem; }
.ste-nav-grid { display:grid;grid-template-columns:repeat(5,1fr);gap:.3rem; }
.ste-nav-btn { aspect-ratio:1;border-radius:8px;border:1.5px solid #e2e8f0;background:#f8fafc;
               color:#64748b;font-size:.75rem;font-weight:700;cursor:pointer;transition:all .15s;
               display:flex;align-items:center;justify-content:center; }
.ste-nav-btn:hover { border-color:#4f46e5;color:#4f46e5;background:#ede9fe; }
.ste-nav-btn.answered { background:#dcfce7;border-color:#86efac;color:#166534; }
.ste-nav-btn.flagged  { background:#fef3c7;border-color:#fcd34d;color:#92400e; }
.ste-nav-btn.answered.flagged { background:#fff7ed;border-color:#fbbf24;color:#92400e; }
.ste-nav-btn.current  { background:#4f46e5;border-color:#4f46e5;color:#fff;
                         box-shadow:0 4px 12px rgba(79,70,229,.35); }
.ste-nav-legend { margin-top:.75rem;display:grid;grid-template-columns:1fr 1fr;gap:.3rem; }
.ste-legend-item { display:flex;align-items:center;gap:.35rem;font-size:.65rem;color:#64748b; }
.ste-legend-dot { width:10px;height:10px;border-radius:3px;flex-shrink:0; }

.ste-nav-info { margin-top:.8rem;border-top:1px solid #f0f4f8;padding-top:.75rem; }
.ste-nav-info-row { display:flex;justify-content:space-between;font-size:.72rem;color:#64748b;margin-bottom:.25rem; }
.ste-nav-info-val { font-weight:700;color:#0f172a; }

/* ── Question area ── */
.ste-qarea { background:#fff;border-radius:18px;border:1px solid #f0f4f8;
             box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden; }
.ste-qhead { padding:1rem 1.4rem;border-bottom:1px solid #f0f4f8;display:flex;align-items:center;gap:1rem;flex-wrap:wrap; }
.ste-qnum { font-size:.75rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em; }
.ste-quid { font-family:monospace;font-size:.68rem;font-weight:800;background:#0f172a;color:#e2e8f0;
            padding:.14rem .5rem;border-radius:6px;letter-spacing:.04em; }
.ste-qtype-badge { display:inline-flex;align-items:center;gap:.25rem;border-radius:100px;
                   padding:.14rem .55rem;font-size:.65rem;font-weight:800;letter-spacing:.03em; }
.ste-flag-btn { margin-left:auto;display:inline-flex;align-items:center;gap:.35rem;border-radius:9px;
                padding:.35rem .85rem;font-size:.75rem;font-weight:700;cursor:pointer;border:1.5px solid #e2e8f0;
                background:#f8fafc;color:#64748b;transition:all .15s; }
.ste-flag-btn:hover,.ste-flag-btn.flagged { border-color:#fcd34d;background:#fef3c7;color:#92400e; }

.ste-qbody { padding:1.4rem 1.6rem; }
.ste-stem { font-size:1rem;font-weight:600;color:#0f172a;line-height:1.65;margin-bottom:1.4rem;
            word-break:break-word; }

/* MCQ Options */
.ste-options { display:flex;flex-direction:column;gap:.65rem; }
.ste-opt { display:flex;align-items:flex-start;gap:.75rem;padding:.85rem 1rem;border-radius:12px;
           border:1.5px solid #e2e8f0;cursor:pointer;transition:all .2s;background:#fff; }
.ste-opt:hover { border-color:#4f46e5;background:#f5f3ff; }
.ste-opt.selected { border-color:#4f46e5;background:#ede9fe;box-shadow:0 0 0 3px rgba(79,70,229,.12); }
.ste-opt input[type=radio] { display:none; }
.ste-opt-circle { width:28px;height:28px;border-radius:50%;border:2px solid #cbd5e1;display:flex;
                  align-items:center;justify-content:center;font-size:.75rem;font-weight:800;
                  flex-shrink:0;transition:all .2s;color:#94a3b8;margin-top:.05rem; }
.ste-opt.selected .ste-opt-circle { border-color:#4f46e5;background:#4f46e5;color:#fff; }
.ste-opt-text { font-size:.88rem;color:#334155;line-height:1.5;flex:1; }
.ste-opt.selected .ste-opt-text { color:#1e1b4b;font-weight:600; }

/* True/False */
.ste-tf-wrap { display:flex;gap:.75rem;margin-top:.5rem; }
.ste-tf-btn { flex:1;padding:1.1rem;border-radius:14px;border:2px solid #e2e8f0;cursor:pointer;
              font-size:.95rem;font-weight:800;font-family:'SUSE',sans-serif;background:#fff;
              display:flex;align-items:center;justify-content:center;gap:.5rem;transition:all .2s;color:#475569; }
.ste-tf-btn:hover { border-color:#4f46e5;background:#f5f3ff;color:#4f46e5; }
.ste-tf-btn.selected { border-color:#4f46e5;background:#ede9fe;color:#3730a3;box-shadow:0 0 0 3px rgba(79,70,229,.12); }
.ste-tf-btn[data-val=true].selected  { border-color:#059669;background:#dcfce7;color:#166534; }
.ste-tf-btn[data-val=false].selected { border-color:#dc2626;background:#fee2e2;color:#991b1b; }

/* Fill blank */
.ste-fill-input { width:100%;padding:.8rem 1rem;border-radius:11px;border:1.5px solid #e2e8f0;
                  font-size:.9rem;font-family:inherit;outline:none;color:#0f172a;background:#f8fafc;
                  transition:border-color .18s,box-shadow .18s; }
.ste-fill-input:focus { border-color:#4f46e5;box-shadow:0 0 0 3px rgba(79,70,229,.1);background:#fff; }

/* Short answer / essay */
.ste-ta { width:100%;min-height:120px;padding:.8rem 1rem;border-radius:11px;border:1.5px solid #e2e8f0;
          font-size:.88rem;font-family:inherit;outline:none;color:#0f172a;background:#f8fafc;
          resize:vertical;transition:border-color .18s,box-shadow .18s; }
.ste-ta:focus { border-color:#4f46e5;box-shadow:0 0 0 3px rgba(79,70,229,.1);background:#fff; }

/* Save indicator */
.ste-save-ind { font-size:.72rem;font-weight:600;color:#94a3b8;display:flex;align-items:center;gap:.3rem; }
.ste-save-ind.saved  { color:#059669; }
.ste-save-ind.saving { color:#d97706; }
.ste-save-ind.error  { color:#dc2626; }

/* Footer controls */
.ste-qfoot { padding:1rem 1.4rem;border-top:1px solid #f0f4f8;display:flex;align-items:center;
             justify-content:space-between;gap:.65rem;flex-wrap:wrap;background:#fafbfd; }
.ste-nav-action { display:inline-flex;align-items:center;gap:.4rem;border-radius:10px;padding:.55rem 1.2rem;
                  font-size:.8rem;font-weight:700;cursor:pointer;border:1.5px solid #e2e8f0;
                  background:#fff;color:#475569;font-family:inherit;transition:all .15s; }
.ste-nav-action:hover:not(:disabled) { border-color:#4f46e5;color:#4f46e5;background:#ede9fe; }
.ste-nav-action:disabled { opacity:.4;cursor:not-allowed; }
.ste-submit-action { background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;border:none;
                     box-shadow:0 4px 14px rgba(79,70,229,.3);border-radius:10px;padding:.55rem 1.4rem;
                     font-size:.8rem;font-weight:700;cursor:pointer;font-family:inherit;transition:filter .18s; }
.ste-submit-action:hover { filter:brightness(1.08); }

/* Loading overlay */
.ste-loading { display:flex;align-items:center;justify-content:center;min-height:60vh;flex-direction:column;gap:1rem; }
.ste-spinner { width:48px;height:48px;border:4px solid #e2e8f0;border-top-color:#4f46e5;
               border-radius:50%;animation:ste-spin .8s linear infinite; }
@keyframes ste-spin { to{transform:rotate(360deg)} }

/* Keyboard hint */
.ste-kbd-hint { font-size:.65rem;color:#94a3b8;margin-top:1rem;text-align:center; }
.ste-kbd { background:#f1f5f9;border:1px solid #e2e8f0;border-radius:4px;padding:.1rem .35rem;
           font-family:monospace;font-size:.65rem;color:#475569; }
</style>

<div class="ste-wrap">

<!-- Top bar -->
<div class="ste-topbar">
  <div class="ste-exam-title" id="examTitle">Loading exam…</div>
  <div class="ste-progress-wrap">
    <svg class="ste-progress-ring" viewBox="0 0 44 44">
      <circle cx="22" cy="22" r="18" fill="none" stroke="#e2e8f0" stroke-width="4"/>
      <circle cx="22" cy="22" r="18" fill="none" stroke="#4f46e5" stroke-width="4"
              stroke-dasharray="113" stroke-dashoffset="113" stroke-linecap="round"
              style="transform:rotate(-90deg);transform-origin:center;transition:stroke-dashoffset 0.6s"
              id="progressRing"/>
    </svg>
    <div class="ste-progress-txt" id="progressTxt">0 / 0</div>
  </div>
  <div class="ste-timer" id="timerEl"><i class="bi bi-clock"></i><span id="timerTxt">—:——</span></div>
  <button class="ste-submit-topbar" onclick="confirmSubmit()"><i class="bi bi-check2-square"></i>Submit Exam</button>
</div>

<!-- Layout -->
<div class="ste-layout">

  <!-- Question nav panel -->
  <div class="ste-nav-panel">
    <div class="ste-nav-head">Questions</div>
    <div class="ste-nav-grid" id="navGrid"></div>
    <div class="ste-nav-legend">
      <div class="ste-legend-item"><div class="ste-legend-dot" style="background:#f8fafc;border:1.5px solid #e2e8f0"></div>Not answered</div>
      <div class="ste-legend-item"><div class="ste-legend-dot" style="background:#dcfce7;border:1.5px solid #86efac"></div>Answered</div>
      <div class="ste-legend-item"><div class="ste-legend-dot" style="background:#fef3c7;border:1.5px solid #fcd34d"></div>Flagged</div>
      <div class="ste-legend-item"><div class="ste-legend-dot" style="background:#4f46e5;border:1.5px solid #4f46e5"></div>Current</div>
    </div>
    <div class="ste-nav-info">
      <div class="ste-nav-info-row"><span>Answered</span><span class="ste-nav-info-val" id="infoAnswered">0</span></div>
      <div class="ste-nav-info-row"><span>Flagged</span><span class="ste-nav-info-val" id="infoFlagged">0</span></div>
      <div class="ste-nav-info-row"><span>Remaining</span><span class="ste-nav-info-val" id="infoRemaining">—</span></div>
    </div>
  </div>

  <!-- Question display area -->
  <div class="ste-qarea" id="qArea">
    <div class="ste-loading"><div class="ste-spinner"></div><div style="color:#64748b;font-size:.85rem">Loading exam…</div></div>
  </div>

</div>
</div>

<script>
const SESSION_ID = <?= $session_id ?>;
let questions    = [];
let currentIdx   = 0;
let session      = null;
let answers      = {};    // question_id → answer_given string
let flagged      = new Set();
let saveTimer    = null;
let timerInterval= null;
let remaining    = 0;
let autoSubmitting = false;

const TYPE_LABELS = {
  mcq:'Multiple Choice', true_false:'True / False',
  fill_blank:'Fill in the Blank', short_answer:'Short Answer', essay:'Essay'
};
const TYPE_COLORS = {
  mcq:         {bg:'#ede9fe',text:'#5b21b6'},
  true_false:  {bg:'#dbeafe',text:'#1e40af'},
  fill_blank:  {bg:'#d1fae5',text:'#065f46'},
  short_answer:{bg:'#fef3c7',text:'#92400e'},
  essay:       {bg:'#fce7f3',text:'#831843'},
};

/* ════════════════════════════════════════════════════════════
   INIT
════════════════════════════════════════════════════════════ */
function _steInit() {
  fetch(`ajax/ajax_student_exam.php?action=questions&session_id=${SESSION_ID}`)
    .then(r=>r.json()).then(res => {
      if (res.status !== 'success') {
        document.getElementById('qArea').innerHTML = `
          <div class="ste-loading">
            <i class="bi bi-exclamation-triangle" style="font-size:3rem;color:#dc2626"></i>
            <div style="font-weight:700;color:#dc2626">${res.message||'Could not load exam'}</div>
            ${res.session_status === 'submitted' ? `<a href="?view=student_exam_results&session_id=${SESSION_ID}" style="margin-top:1rem;padding:.6rem 1.4rem;background:#4f46e5;color:#fff;border-radius:10px;text-decoration:none;font-weight:700">View Results</a>` : `<a href="?view=student_exams" style="margin-top:1rem;padding:.6rem 1.4rem;background:#4f46e5;color:#fff;border-radius:10px;text-decoration:none;font-weight:700">Back to Exams</a>`}
          </div>`;
        return;
      }
      session   = res.session;
      questions = res.questions;
      answers   = {};
      for (const [qid, ans] of Object.entries(res.saved_answers || {})) {
        answers[parseInt(qid)] = ans;
      }
      flagged = new Set((session.flagged || []).map(Number));

      // Merge localStorage backup
      const lsKey = `exam_${SESSION_ID}`;
      try {
        const cached = JSON.parse(localStorage.getItem(lsKey) || '{}');
        for (const [qid, ans] of Object.entries(cached)) {
          if (!(parseInt(qid) in answers)) answers[parseInt(qid)] = ans;
        }
      } catch(e) {}

      document.getElementById('examTitle').textContent = session.exam_title;
      buildNavGrid();
      renderQuestion(0);
      startTimer(session.remaining_seconds);
      updateProgress();

      // Warn before leaving
      window.onbeforeunload = e => { if (!autoSubmitting) { e.preventDefault(); return ''; } };
    }).catch(err => {
      document.getElementById('qArea').innerHTML = `<div class="ste-loading"><i class="bi bi-wifi-off" style="font-size:3rem;color:#dc2626"></i><div style="color:#dc2626;font-weight:700">Network error. Please refresh.</div></div>`;
    });
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', _steInit);
} else { _steInit(); }

/* ════════════════════════════════════════════════════════════
   TIMER
════════════════════════════════════════════════════════════ */
function startTimer(seconds) {
  remaining = seconds;
  updateTimerDisplay();
  timerInterval = setInterval(() => {
    remaining--;
    updateTimerDisplay();
    if (remaining <= 0) {
      clearInterval(timerInterval);
      autoSubmit();
    }
  }, 1000);
}

function updateTimerDisplay() {
  const el  = document.getElementById('timerEl');
  const txt = document.getElementById('timerTxt');
  const m   = Math.floor(Math.abs(remaining) / 60);
  const s   = Math.abs(remaining) % 60;
  txt.textContent = (remaining < 0 ? '-':'') + m + ':' + String(s).padStart(2,'0');
  el.classList.toggle('warn',   remaining > 0 && remaining <= 300);
  el.classList.toggle('danger', remaining > 0 && remaining <= 60);
  if (remaining === 300) playBeep(440, 0.3);
  if (remaining === 60)  playBeep(660, 0.5);
  if (remaining === 10)  playBeep(880, 0.2);
}

function playBeep(freq, dur) {
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain); gain.connect(ctx.destination);
    osc.frequency.value = freq;
    gain.gain.setValueAtTime(0.15, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + dur);
    osc.start(); osc.stop(ctx.currentTime + dur);
  } catch(e) {}
}

function autoSubmit() {
  if (autoSubmitting) return;
  autoSubmitting = true;
  Swal.fire({ title:'Time is up!', text:'Submitting your exam now…', icon:'warning',
              allowOutsideClick:false, showConfirmButton:false,
              didOpen:()=>Swal.showLoading() });
  submitExam();
}

/* ════════════════════════════════════════════════════════════
   NAVIGATION GRID
════════════════════════════════════════════════════════════ */
function buildNavGrid() {
  const grid = document.getElementById('navGrid');
  grid.innerHTML = questions.map((q, i) => {
    const qid = parseInt(q.question_id);
    return `<button class="ste-nav-btn" id="navbtn_${i}" onclick="goToQuestion(${i})" title="Q${i+1}">${i+1}</button>`;
  }).join('');
  refreshNavGrid();
}

function refreshNavGrid() {
  questions.forEach((q, i) => {
    const btn = document.getElementById(`navbtn_${i}`);
    if (!btn) return;
    const qid = parseInt(q.question_id);
    const ans = answers[qid];
    const isAnswered = ans !== undefined && ans !== '';
    const isFlagged  = flagged.has(qid);
    btn.className = 'ste-nav-btn'
      + (i === currentIdx ? ' current' : '')
      + (isAnswered ? ' answered' : '')
      + (isFlagged  ? ' flagged'  : '');
  });

  // Update sidebar info
  const answered  = questions.filter(q => { const a = answers[parseInt(q.question_id)]; return a !== undefined && a !== ''; }).length;
  const flaggedCt = flagged.size;
  document.getElementById('infoAnswered').textContent  = `${answered} / ${questions.length}`;
  document.getElementById('infoFlagged').textContent   = flaggedCt;
  document.getElementById('infoRemaining').textContent = questions.length - answered;
}

function updateProgress() {
  const answered = questions.filter(q => { const a = answers[parseInt(q.question_id)]; return a !== undefined && a !== ''; }).length;
  const total    = questions.length;
  const pct      = total > 0 ? answered / total : 0;
  const circum   = 2 * Math.PI * 18;
  document.getElementById('progressRing').setAttribute('stroke-dashoffset', circum * (1 - pct));
  document.getElementById('progressTxt').textContent = `${answered} / ${total}`;
}

/* ════════════════════════════════════════════════════════════
   QUESTION RENDERING
════════════════════════════════════════════════════════════ */
function renderQuestion(idx) {
  currentIdx = idx;
  const q    = questions[idx];
  if (!q) return;

  const qid      = parseInt(q.question_id);
  const typeInfo = TYPE_COLORS[q.question_type] || {bg:'#f1f5f9',text:'#475569'};
  const isFlagged= flagged.has(qid);
  const savedAns = answers[qid] ?? '';

  let inputHtml = '';
  switch (q.question_type) {
    case 'mcq':
      inputHtml = `<div class="ste-options">` +
        (q.options || []).map(opt => `
          <label class="ste-opt${savedAns === opt.option_label ? ' selected' : ''}" data-label="${opt.option_label}">
            <input type="radio" name="q_${qid}" value="${opt.option_label}" ${savedAns === opt.option_label?'checked':''}>
            <div class="ste-opt-circle">${opt.option_label}</div>
            <div class="ste-opt-text">${opt.option_text}</div>
          </label>`).join('') + `</div>`;
      break;
    case 'true_false':
      inputHtml = `<div class="ste-tf-wrap">
        <button class="ste-tf-btn${savedAns==='true'?' selected':''}" data-val="true" onclick="selectAnswer(${qid},'true')"><i class="bi bi-check-circle-fill"></i>True</button>
        <button class="ste-tf-btn${savedAns==='false'?' selected':''}" data-val="false" onclick="selectAnswer(${qid},'false')"><i class="bi bi-x-circle-fill"></i>False</button>
      </div>`;
      break;
    case 'fill_blank':
      inputHtml = `<input type="text" class="ste-fill-input" id="fill_${qid}" placeholder="Type your answer…" value="${escHtml(savedAns)}" oninput="debounceSave(${qid},this.value)">`;
      break;
    default:
      inputHtml = `<textarea class="ste-ta" id="ta_${qid}" placeholder="Write your answer…" oninput="debounceSave(${qid},this.value)">${escHtml(savedAns)}</textarea>
        <div style="font-size:.72rem;color:#94a3b8;margin-top:.35rem"><i class="bi bi-info-circle"></i> This question type is manually graded by your teacher.</div>`;
  }

  document.getElementById('qArea').innerHTML = `
    <div class="ste-qhead">
      <div class="ste-qnum">Question ${idx + 1} of ${questions.length}</div>
      <span class="ste-quid">${q.q_uid || ''}</span>
      <span class="ste-qtype-badge" style="background:${typeInfo.bg};color:${typeInfo.text}">${TYPE_LABELS[q.question_type]||q.question_type}</span>
      ${q.effective_marks ? `<span style="font-size:.72rem;font-weight:700;color:#64748b;margin-left:.25rem">${parseFloat(q.effective_marks).toFixed(1)} mark${q.effective_marks!=1?'s':''}</span>` : ''}
      <button class="ste-flag-btn${isFlagged?' flagged':''}" id="flagBtn" onclick="toggleFlag(${qid})">
        <i class="bi bi-flag${isFlagged?'-fill':''}"></i>${isFlagged?'Flagged':'Flag'}
      </button>
    </div>
    <div class="ste-qbody">
      <div class="ste-stem">${q.question_stem || '<em style="color:#94a3b8">No question text</em>'}</div>
      ${inputHtml}
      <div class="ste-save-ind" id="saveInd" style="margin-top:.75rem"><i class="bi bi-check2-circle"></i><span>Saved</span></div>
    </div>
    <div class="ste-kbd-hint">Keyboard: <span class="ste-kbd">←</span><span class="ste-kbd">→</span> navigate · <span class="ste-kbd">F</span> flag · <span class="ste-kbd">1-4</span> select option · <span class="ste-kbd">Enter</span> next</div>
    <div class="ste-qfoot">
      <button class="ste-nav-action" onclick="goToQuestion(${idx-1})" ${idx===0?'disabled':''}><i class="bi bi-chevron-left"></i>Previous</button>
      <div style="display:flex;gap:.5rem;align-items:center">
        ${idx === questions.length-1
          ? `<button class="ste-submit-action" onclick="confirmSubmit()"><i class="bi bi-check2-square"></i>Submit Exam</button>`
          : `<button class="ste-nav-action" onclick="goToQuestion(${idx+1})">Next<i class="bi bi-chevron-right"></i></button>`
        }
      </div>
    </div>`;

  // Attach MCQ listeners
  if (q.question_type === 'mcq') {
    document.querySelectorAll('.ste-opt').forEach(lbl => {
      lbl.addEventListener('click', () => {
        const val = lbl.dataset.label;
        document.querySelectorAll('.ste-opt').forEach(l => l.classList.remove('selected'));
        lbl.classList.add('selected');
        lbl.querySelector('input').checked = true;
        selectAnswer(qid, val);
      });
    });
  }

  refreshNavGrid();
}

/* ════════════════════════════════════════════════════════════
   ANSWER HANDLING
════════════════════════════════════════════════════════════ */
function selectAnswer(qid, val) {
  answers[qid] = val;
  updateProgress();
  refreshNavGrid();
  setSaveState('saving');
  saveAnswer(qid, val);
}

function debounceSave(qid, val) {
  answers[qid] = val;
  updateProgress();
  refreshNavGrid();
  setSaveState('saving');
  clearTimeout(saveTimer);
  saveTimer = setTimeout(() => saveAnswer(qid, val), 600);
}

function saveAnswer(qid, val) {
  // LocalStorage backup
  try {
    const lsKey = `exam_${SESSION_ID}`;
    const cache = JSON.parse(localStorage.getItem(lsKey) || '{}');
    cache[qid] = val;
    localStorage.setItem(lsKey, JSON.stringify(cache));
  } catch(e) {}

  fetch('ajax/ajax_student_exam.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ action:'save_answer', session_id:SESSION_ID, question_id:qid, answer_given:val })
  }).then(r=>r.json()).then(res => {
    setSaveState(res.status === 'success' ? 'saved' : 'error');
  }).catch(() => setSaveState('error'));
}

function setSaveState(state) {
  const el = document.getElementById('saveInd');
  if (!el) return;
  const map = {
    saving: ['saving','<i class="bi bi-arrow-repeat" style="animation:ste-spin .6s linear infinite"></i><span>Saving…</span>'],
    saved:  ['saved',  '<i class="bi bi-check2-circle"></i><span>Saved</span>'],
    error:  ['error',  '<i class="bi bi-exclamation-circle"></i><span>Save failed — try again</span>'],
  };
  const [cls, html] = map[state] || map.saved;
  el.className = 'ste-save-ind ' + cls;
  el.innerHTML = html;
}

/* ════════════════════════════════════════════════════════════
   FLAG
════════════════════════════════════════════════════════════ */
function toggleFlag(qid) {
  fetch('ajax/ajax_student_exam.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ action:'flag', session_id:SESSION_ID, question_id:qid })
  }).then(r=>r.json()).then(res => {
    if (res.status === 'success') {
      flagged = new Set(res.flagged.map(Number));
      const btn = document.getElementById('flagBtn');
      const isFlagged = flagged.has(qid);
      if (btn) {
        btn.className = 'ste-flag-btn' + (isFlagged?' flagged':'');
        btn.innerHTML = `<i class="bi bi-flag${isFlagged?'-fill':''}"></i>${isFlagged?'Flagged':'Flag'}`;
      }
      refreshNavGrid();
    }
  });
}

/* ════════════════════════════════════════════════════════════
   NAVIGATION
════════════════════════════════════════════════════════════ */
function goToQuestion(idx) {
  if (idx < 0 || idx >= questions.length) return;
  renderQuestion(idx);
  // Scroll nav button into view
  const btn = document.getElementById(`navbtn_${idx}`);
  if (btn) btn.scrollIntoView({ block:'nearest' });
}

/* ════════════════════════════════════════════════════════════
   KEYBOARD SHORTCUTS
════════════════════════════════════════════════════════════ */
document.addEventListener('keydown', e => {
  if (!questions.length) return;
  if (['INPUT','TEXTAREA'].includes(document.activeElement.tagName)) return;
  const q = questions[currentIdx];
  switch (e.key) {
    case 'ArrowRight': case 'Enter':
      if (currentIdx < questions.length - 1) goToQuestion(currentIdx + 1);
      break;
    case 'ArrowLeft':
      if (currentIdx > 0) goToQuestion(currentIdx - 1);
      break;
    case 'f': case 'F':
      if (q) toggleFlag(parseInt(q.question_id));
      break;
    case '1': case '2': case '3': case '4':
      if (q && q.question_type === 'mcq') {
        const opts = q.options || [];
        const idx  = parseInt(e.key) - 1;
        if (opts[idx]) {
          const lbl = document.querySelector(`[data-label="${opts[idx].option_label}"]`);
          if (lbl) lbl.click();
        }
      }
      break;
  }
});

/* ════════════════════════════════════════════════════════════
   SUBMIT
════════════════════════════════════════════════════════════ */
function confirmSubmit() {
  const answered   = questions.filter(q => { const a = answers[parseInt(q.question_id)]; return a !== undefined && a !== ''; }).length;
  const unanswered = questions.length - answered;
  const flaggedCt  = flagged.size;

  const warningLines = [];
  if (unanswered > 0) warningLines.push(`<li><b>${unanswered}</b> question${unanswered!==1?'s':''} not answered</li>`);
  if (flaggedCt  > 0) warningLines.push(`<li><b>${flaggedCt}</b> question${flaggedCt!==1?'s':''} flagged for review</li>`);

  Swal.fire({
    title: 'Submit Exam?',
    html: `<div style="text-align:left">
      <div style="margin-bottom:.75rem;color:#475569;font-size:.88rem">You are about to submit your exam. This cannot be undone.</div>
      ${warningLines.length ? `<ul style="color:#92400e;background:#fef3c7;border-radius:10px;padding:.75rem 1rem .75rem 1.8rem;margin:0;font-size:.83rem">${warningLines.join('')}</ul>` : '<div style="color:#059669;font-size:.85rem"><i class="bi bi-check-circle-fill"></i> All <b>'+answered+'</b> questions answered. Great job!</div>'}
    </div>`,
    icon: unanswered > 0 ? 'warning' : 'question',
    showCancelButton: true,
    confirmButtonText: '<i class="bi bi-check2-square"></i> Yes, Submit',
    cancelButtonText:  'Go Back',
    confirmButtonColor: '#4f46e5',
    reverseButtons: true,
    customClass:{popup:'ds-pop',title:'ds-ttl',confirmButton:'ds-btn',cancelButton:'ds-can'}
  }).then(r => { if (r.isConfirmed) submitExam(); });
}

function submitExam() {
  const time_taken = session.duration_minutes * 60 - remaining;
  clearInterval(timerInterval);
  window.onbeforeunload = null;

  Swal.fire({ title:'Submitting…',allowOutsideClick:false,customClass:{popup:'ds-pop'},didOpen:()=>Swal.showLoading() });

  fetch('ajax/ajax_student_exam.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ action:'submit', session_id:SESSION_ID, time_taken_seconds: Math.max(0, time_taken) })
  }).then(r=>r.json()).then(res => {
    Swal.close();
    if (res.status === 'success') {
      try { localStorage.removeItem(`exam_${SESSION_ID}`); } catch(e) {}
      Swal.fire({
        title: res.passed ? '🎉 Exam Submitted!' : 'Exam Submitted',
        html:  `<div style="text-align:center"><div style="font-size:2.5rem;font-weight:900;color:${res.passed?'#059669':'#dc2626'};font-family:'SUSE',sans-serif">${res.total_marks>0?Math.round(res.score/res.total_marks*100):0}%</div><div style="color:#475569;margin-top:.5rem">Score: ${parseFloat(res.score).toFixed(1)} / ${parseFloat(res.total_marks).toFixed(1)} marks</div></div>`,
        icon: res.passed ? 'success' : 'info',
        confirmButtonText:'View Detailed Results',
        confirmButtonColor:'#4f46e5',
        allowOutsideClick:false,
        customClass:{popup:'ds-pop',confirmButton:'ds-btn'}
      }).then(() => {
        window.location.href = `?view=student_exam_results&session_id=${SESSION_ID}`;
      });
    } else {
      Swal.fire({icon:'error',title:'Submission failed',text:res.message});
    }
  }).catch(() => Swal.fire({icon:'error',title:'Error','text':'Network error. Please try again.'}));
}

function escHtml(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

Object.assign(window, { goToQuestion, toggleFlag, selectAnswer, debounceSave, confirmSubmit });
</script>
