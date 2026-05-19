<?php
$preselect_exam = (int)($_GET['exam_id'] ?? 0);
?>
<style>
/* ═══════════════════════════════════════════════════════════
   QB PRINT EXAMS  (qbp-*)
═══════════════════════════════════════════════════════════ */
.qbp-wrap { font-family:'Open Sans',sans-serif; }

/* ── Control panel ── */
.qbp-panel { background:#fff; border-radius:18px; border:1px solid #f0f4f8; box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 16px rgba(0,0,0,.05); overflow:hidden; }
.qbp-panel-head { padding:.9rem 1.2rem; background:linear-gradient(135deg,#0b1120,#0f1e3d); display:flex; align-items:center; gap:.55rem; }
.qbp-panel-title { font-size:.88rem; font-weight:800; color:#fff; font-family:'SUSE',sans-serif; }
.qbp-panel-body { padding:1.1rem 1.2rem; }
.qbp-ctrl-lbl { font-size:.74rem; font-weight:700; color:#475569; margin-bottom:.3rem; display:block; }
.qbp-ctrl { width:100%; padding:.52rem .85rem; border-radius:11px; border:1.5px solid #e2e8f0; font-size:.81rem; font-family:inherit; outline:none; background:#f8fafc; color:#1e293b; transition:border-color .18s; }
.qbp-ctrl:focus { border-color:#1a4fc4; box-shadow:0 0 0 3px rgba(26,79,196,.1); background:#fff; }
.qbp-check-row { display:flex; align-items:center; gap:.5rem; padding:.5rem .7rem; background:#f8fafc; border-radius:10px; border:1px solid #f0f4f8; margin-bottom:.4rem; cursor:pointer; }
.qbp-check-row input { accent-color:#1a4fc4; width:14px; height:14px; cursor:pointer; }
.qbp-check-row label { font-size:.79rem; font-weight:600; color:#334155; cursor:pointer; margin:0; }
.qbp-print-btn { display:flex; align-items:center; justify-content:center; gap:.5rem; background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff; border:none; border-radius:12px; padding:.65rem 1rem; font-size:.85rem; font-weight:700; cursor:pointer; font-family:inherit; width:100%; margin-top:.85rem; box-shadow:0 4px 16px rgba(26,79,196,.35); transition:filter .18s; }
.qbp-print-btn:hover { filter:brightness(1.1); }
.qbp-refresh-btn { display:flex; align-items:center; justify-content:center; gap:.5rem; background:#f1f5f9; color:#475569; border:1.5px solid #e2e8f0; border-radius:11px; padding:.55rem 1rem; font-size:.8rem; font-weight:700; cursor:pointer; font-family:inherit; width:100%; margin-top:.55rem; transition:background .15s; }
.qbp-refresh-btn:hover { background:#e2e8f0; }

/* ── Skel ── */
.qbp-skel { background:linear-gradient(90deg,#f0f4f8 25%,#e2e8f0 50%,#f0f4f8 75%); background-size:200% 100%; animation:qbp-shim 1.5s infinite; border-radius:8px; }
@keyframes qbp-shim { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ═══════════════════════════════════════════════════════════
   PRINT PREVIEW AREA
═══════════════════════════════════════════════════════════ */
.qbp-preview-wrap { background:#fff; border-radius:18px; border:1px solid #f0f4f8; box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 16px rgba(0,0,0,.05); min-height:500px; }
.qbp-preview-toolbar { padding:.75rem 1.2rem; background:#f8fafc; border-bottom:1px solid #f0f4f8; border-radius:18px 18px 0 0; display:flex; align-items:center; justify-content:space-between; }
.qbp-preview-label { font-size:.75rem; font-weight:700; color:#64748b; }
.qbp-preview-content { padding:2.2rem; font-family:'Times New Roman',Times,serif; }

/* ── Exam header ── */
.qbp-exam-header { text-align:center; margin-bottom:1.5rem; padding-bottom:1.2rem; border-bottom:2px solid #0f172a; }
.qbp-school-name { font-size:1rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#334155; }
.qbp-exam-title { font-size:1.4rem; font-weight:900; color:#0f172a; margin:.3rem 0; }
.qbp-exam-meta-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:.5rem; margin-top:.85rem; }
.qbp-meta-item { font-size:.78rem; color:#475569; }
.qbp-meta-item strong { display:block; font-size:.7rem; color:#94a3b8; font-weight:700; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.15rem; }

/* ── Instructions ── */
.qbp-instructions { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:.85rem 1rem; margin-bottom:1.5rem; font-size:.82rem; color:#334155; line-height:1.65; }
.qbp-instructions-title { font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.07em; color:#64748b; margin-bottom:.45rem; }

/* ── Question block ── */
.qbp-question-block { margin-bottom:1.4rem; page-break-inside:avoid; }
.qbp-q-num { font-size:.82rem; font-weight:800; color:#0f172a; margin-bottom:.35rem; }
.qbp-q-stem { font-size:.85rem; color:#0f172a; line-height:1.6; margin-bottom:.65rem; }
.qbp-options { margin-left:1.2rem; }
.qbp-option { display:flex; align-items:flex-start; gap:.65rem; margin-bottom:.4rem; font-size:.82rem; color:#334155; }
.qbp-option-lbl { font-weight:700; min-width:22px; flex-shrink:0; }
.qbp-option.correct-ans { font-weight:700; color:#059669; }
.qbp-option.correct-ans .qbp-option-lbl { color:#059669; }
.qbp-answer-space { margin-top:.6rem; margin-left:1.2rem; }
.qbp-answer-line { border-bottom:1px solid #94a3b8; height:20px; margin-bottom:.3rem; }
.qbp-marks-tag { font-size:.7rem; color:#64748b; font-weight:700; float:right; }
.qbp-section-break { border:none; border-top:1.5px dashed #e2e8f0; margin:1.4rem 0; }

/* ── Empty state ── */
.qbp-empty { text-align:center; padding:4rem 2rem; color:#94a3b8; }

/* ═══════════════════════════════════════════════════════════
   PRINT MEDIA — hide controls, show only preview
═══════════════════════════════════════════════════════════ */
@media print {
  body { margin:0; padding:0; }
  .qbp-wrap > .container-fluid { padding:0 !important; }
  .col-md-3,
  .qbp-preview-toolbar,
  .qbp-panel,
  nav, header, footer,
  [class*="qbt-"], [class*="qbq-"],
  .container-fluid > *:not(.row),
  .row > *:not(.col-md-9):not(.col-lg-9) { display:none !important; }
  .col-md-9, .col-lg-9 { flex:0 0 100%; max-width:100%; width:100%; padding:0; }
  .qbp-preview-wrap { border:none; box-shadow:none; border-radius:0; }
  .qbp-preview-content { padding:1.5cm 2cm; font-size:11pt; }
  .qbp-exam-title { font-size:16pt; }
  .qbp-q-stem { font-size:11pt; }
  .qbp-option { font-size:10.5pt; }
}
</style>

<div class="container-fluid px-3 py-3 qbp-wrap">
<div class="row g-3">

  <!-- ── Left: Controls ── -->
  <div class="col-md-3">
    <div class="qbp-panel">
      <div class="qbp-panel-head">
        <i class="bi bi-printer-fill" style="color:#60a5fa;font-size:1rem"></i>
        <div class="qbp-panel-title">Print Options</div>
      </div>
      <div class="qbp-panel-body">

        <div class="mb-3">
          <label class="qbp-ctrl-lbl">Select Exam</label>
          <select id="pExam" class="qbp-ctrl" onchange="loadPreview()">
            <option value="">— Select —</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="qbp-ctrl-lbl">School / Institution Name</label>
          <input type="text" id="pSchool" class="qbp-ctrl" placeholder="e.g. Dar es Salaam Academy" oninput="loadPreview()">
        </div>

        <div class="mb-3">
          <label class="qbp-ctrl-lbl">Print Options</label>
          <label class="qbp-check-row">
            <input type="checkbox" id="pShowAnswers" onchange="loadPreview()">
            <label for="pShowAnswers"><i class="bi bi-eye-fill text-success me-1" style="font-size:.8rem"></i>Show Correct Answers</label>
          </label>
          <label class="qbp-check-row">
            <input type="checkbox" id="pShowInstructions" checked onchange="loadPreview()">
            <label for="pShowInstructions"><i class="bi bi-info-circle-fill text-primary me-1" style="font-size:.8rem"></i>Include Instructions</label>
          </label>
          <label class="qbp-check-row">
            <input type="checkbox" id="pAnswerSpace" checked onchange="loadPreview()">
            <label for="pAnswerSpace"><i class="bi bi-pencil-square text-warning me-1" style="font-size:.8rem"></i>Show Answer Space</label>
          </label>
          <label class="qbp-check-row">
            <input type="checkbox" id="pShowMarks" checked onchange="loadPreview()">
            <label for="pShowMarks"><i class="bi bi-star-fill text-warning me-1" style="font-size:.8rem"></i>Show Marks per Question</label>
          </label>
        </div>

        <div class="mb-3">
          <label class="qbp-ctrl-lbl">Font Size</label>
          <select id="pFontSize" class="qbp-ctrl" onchange="applyFontSize()">
            <option value="13">Normal (13px)</option>
            <option value="14">Large (14px)</option>
            <option value="12">Small (12px)</option>
            <option value="11">Very Small (11px)</option>
          </select>
        </div>

        <div class="mb-2">
          <label class="qbp-ctrl-lbl">Exam Date</label>
          <input type="date" id="pDate" class="qbp-ctrl" oninput="loadPreview()">
        </div>

        <button class="qbp-print-btn" onclick="window.print()">
          <i class="bi bi-printer-fill"></i>Print / Save PDF
        </button>
        <button class="qbp-refresh-btn" onclick="loadPreview()">
          <i class="bi bi-arrow-clockwise"></i>Refresh Preview
        </button>

        <div style="margin-top:1.1rem;padding:.75rem;background:#f0f9ff;border-radius:10px;border:1px solid #bae6fd">
          <div style="font-size:.7rem;font-weight:700;color:#0369a1;margin-bottom:.3rem;text-transform:uppercase;letter-spacing:.05em">Print tip</div>
          <div style="font-size:.75rem;color:#0369a1;line-height:1.5">Use "Save as PDF" in the browser print dialog for best results. Set margins to "Minimum".</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Right: Preview ── -->
  <div class="col-md-9">
    <div class="qbp-preview-wrap">
      <div class="qbp-preview-toolbar">
        <span class="qbp-preview-label"><i class="bi bi-eye me-1"></i>Print Preview</span>
        <span style="font-size:.72rem;color:#94a3b8" id="previewMeta">Select an exam to preview</span>
      </div>
      <div class="qbp-preview-content" id="previewContent">
        <div class="qbp-empty">
          <i class="bi bi-printer" style="font-size:3rem;display:block;margin-bottom:1rem;opacity:.2"></i>
          <div style="font-size:1rem;font-weight:700;margin-bottom:.4rem">No exam selected</div>
          <div style="font-size:.82rem">Choose an exam from the panel on the left to preview the printable paper.</div>
        </div>
      </div>
    </div>
  </div>

</div><!-- /.row -->
</div><!-- /.container-fluid -->

<script>
let currentExamData = null;

function _qbpeInit() {
  loadExamList();
  document.getElementById('pDate').value = new Date().toISOString().slice(0,10);
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', _qbpeInit);
} else {
  _qbpeInit();
}

function loadExamList() {
  fetch('ajax/ajax_qb_exams.php?action=list')
    .then(r=>r.json()).then(res=>{
      const sel = document.getElementById('pExam');
      if (res.status !== 'success') return;
      res.data.forEach(e => {
        sel.add(new Option(`${e.exam_code ? '['+e.exam_code+'] ' : ''}${e.exam_title}`, e.exam_id));
      });
      // Pre-select if exam_id in URL
      const pre = <?= $preselect_exam ?>;
      if (pre) {
        sel.value = pre;
        loadPreview();
      }
    });
}

function loadPreview() {
  const exam_id = document.getElementById('pExam').value;
  if (!exam_id) {
    document.getElementById('previewContent').innerHTML = `
      <div class="qbp-empty">
        <i class="bi bi-printer" style="font-size:3rem;display:block;margin-bottom:1rem;opacity:.2"></i>
        <div style="font-size:1rem;font-weight:700;margin-bottom:.4rem">No exam selected</div>
        <div style="font-size:.82rem">Choose an exam from the panel on the left.</div>
      </div>`;
    document.getElementById('previewMeta').textContent = 'Select an exam to preview';
    return;
  }

  document.getElementById('previewContent').innerHTML = '<div style="text-align:center;padding:4rem"><div class="spinner-border text-primary spinner-border-sm"></div> Loading preview…</div>';

  fetch(`ajax/ajax_qb_exams.php?action=get&id=${exam_id}`)
    .then(r=>r.json()).then(res=>{
      if (res.status !== 'success') {
        document.getElementById('previewContent').innerHTML = '<div class="qbp-empty" style="color:#dc2626">Could not load exam data.</div>';
        return;
      }
      currentExamData = res.data;
      renderPreview();
    });
}

function renderPreview() {
  if (!currentExamData) return;
  const e             = currentExamData;
  const showAnswers   = document.getElementById('pShowAnswers').checked;
  const showInstr     = document.getElementById('pShowInstructions').checked;
  const showSpace     = document.getElementById('pAnswerSpace').checked;
  const showMarks     = document.getElementById('pShowMarks').checked;
  const school        = document.getElementById('pSchool').value || 'DigitalClass Institution';
  const dateVal       = document.getElementById('pDate').value;
  const dateDisplay   = dateVal ? new Date(dateVal).toLocaleDateString('en-GB',{day:'2-digit',month:'long',year:'numeric'}) : new Date().toLocaleDateString('en-GB',{day:'2-digit',month:'long',year:'numeric'});

  document.getElementById('previewMeta').textContent = `${(e.questions||[]).length} questions · ${parseFloat(e.total_marks||0).toFixed(0)} marks`;

  let html = `
    <div class="qbp-exam-header">
      <div class="qbp-school-name">${escHtml(school)}</div>
      <div class="qbp-exam-title">${escHtml(e.exam_title)}</div>
      <div class="qbp-exam-meta-grid">
        <div class="qbp-meta-item"><strong>Exam Code</strong>${e.exam_code || '—'}</div>
        <div class="qbp-meta-item"><strong>Subject</strong>${escHtml(e.subject_name||'—')}</div>
        <div class="qbp-meta-item"><strong>Level / Class</strong>${escHtml(e.level_name||'—')}</div>
        <div class="qbp-meta-item"><strong>Date</strong>${dateDisplay}</div>
        <div class="qbp-meta-item"><strong>Duration</strong>${e.duration_minutes || 60} minutes</div>
        <div class="qbp-meta-item"><strong>Total Marks</strong>${parseFloat(e.total_marks||0).toFixed(0)} marks</div>
      </div>
    </div>`;

  // Student info fields
  html += `
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:1rem;margin-bottom:1.2rem;font-size:.82rem">
      <div>Name: <span style="display:inline-block;border-bottom:1px solid #94a3b8;min-width:200px">&nbsp;</span></div>
      <div>Admission No: <span style="display:inline-block;border-bottom:1px solid #94a3b8;min-width:140px">&nbsp;</span></div>
    </div>`;

  // Instructions
  if (showInstr && e.instructions) {
    html += `
      <div class="qbp-instructions">
        <div class="qbp-instructions-title"><i class="bi bi-info-circle me-1"></i>Instructions</div>
        ${escHtml(e.instructions).replace(/\n/g,'<br>')}
      </div>`;
  }

  // Questions
  const questions = e.questions || [];
  if (!questions.length) {
    html += '<div class="qbp-empty" style="padding:2rem">No questions have been added to this exam.</div>';
  } else {
    html += questions.map((q, i) => {
      const marksEff = parseFloat(q.marks_override ?? q.marks ?? 0);
      const marksTag = showMarks ? `<span class="qbp-marks-tag">[${marksEff} mark${marksEff!==1?'s':''}]</span>` : '';
      const stem     = q.question_stem || '';

      let optionsHtml = '';
      const isEssay   = q.question_type === 'essay';
      const isTF      = q.question_type === 'true_false';
      const isMCQ     = q.question_type === 'mcq' || q.question_type === 'fill_blank';

      if (isTF) {
        const opts = [
          {label:'A', text:'True',  is_correct: q.correct_answer?.toLowerCase()==='true'},
          {label:'B', text:'False', is_correct: q.correct_answer?.toLowerCase()==='false'},
        ];
        optionsHtml = `<div class="qbp-options">${opts.map(o=>`
          <div class="qbp-option${(showAnswers&&o.is_correct)?' correct-ans':''}">
            <span class="qbp-option-lbl">${o.label}.</span>
            <span>${o.text}${(showAnswers&&o.is_correct)?' ✓':''}</span>
          </div>`).join('')}</div>`;
      } else if (isMCQ && q.options_data) {
        // Options not yet loaded with this endpoint — show placeholder
        optionsHtml = '';
      } else if (isEssay || !isMCQ) {
        if (showSpace) {
          optionsHtml = `<div class="qbp-answer-space">${[1,2,3].map(()=>'<div class="qbp-answer-line"></div>').join('')}</div>`;
        }
        if (showAnswers && q.correct_answer) {
          optionsHtml += `<div style="margin-top:.4rem;margin-left:1.2rem;font-size:.78rem;color:#059669;font-weight:700">Model Answer: ${escHtml(q.correct_answer)}</div>`;
        }
      }

      return `
      <div class="qbp-question-block">
        <div class="qbp-q-num">Question ${i+1} ${marksTag}</div>
        <div class="qbp-q-stem">${stem}</div>
        ${optionsHtml}
      </div>
      ${i < questions.length-1 ? '<hr class="qbp-section-break">' : ''}`;
    }).join('');
  }

  html += `<div style="text-align:center;margin-top:2rem;font-size:.75rem;color:#94a3b8;border-top:1px solid #e2e8f0;padding-top:1rem">— End of Exam Paper — · Generated by DigitalClass · ${new Date().toLocaleDateString()}</div>`;

  document.getElementById('previewContent').innerHTML = html;
  applyFontSize();
}

function applyFontSize() {
  const sz = document.getElementById('pFontSize').value;
  const pc = document.getElementById('previewContent');
  if (pc) pc.style.fontSize = sz + 'px';
}

Object.assign(window, { loadPreview, applyFontSize, loadExamList });

function escHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;');
}
</script>
