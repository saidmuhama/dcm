<?php
$session_id = (int)($_GET['session_id'] ?? 0);
if (!$session_id) {
    echo '<div style="padding:3rem;text-align:center;color:#dc2626;font-weight:700">No session specified. <a href="?view=student_exams">Back to Exams</a></div>';
    return;
}
?>
<style>
/* ══ Exam Results (ser-*) ═════════════════════════════════════ */
.ser-wrap { font-family:'Open Sans',sans-serif; }

/* Hero */
.ser-hero { position:relative;overflow:hidden;isolation:isolate;border-radius:20px;
            padding:2rem 2.4rem;margin-bottom:1.4rem; }
.ser-hero-grid { position:absolute;inset:0;z-index:0;
                 background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),
                                  linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px);
                 background-size:44px 44px; }
.ser-hero-inner { position:relative;z-index:1;display:flex;align-items:center;gap:2rem;flex-wrap:wrap; }

/* Score ring */
.ser-ring-wrap { flex-shrink:0;position:relative; }
.ser-ring { width:130px;height:130px; }
.ser-ring-pct { position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;
                justify-content:center;text-align:center; }
.ser-ring-num { font-size:1.8rem;font-weight:900;color:#fff;font-family:'SUSE',sans-serif;line-height:1; }
.ser-ring-sub { font-size:.65rem;color:rgba(255,255,255,.6);margin-top:.15rem;font-weight:600;text-transform:uppercase; }

/* Hero info */
.ser-hero-info { flex:1;min-width:200px; }
.ser-pass-badge { display:inline-flex;align-items:center;gap:.5rem;border-radius:12px;
                  padding:.45rem 1.1rem;font-size:.82rem;font-weight:800;margin-bottom:.75rem; }
.ser-hero-title { font-size:1.4rem;font-weight:900;color:#fff;font-family:'SUSE',sans-serif;
                  letter-spacing:-.03em;line-height:1.2;margin-bottom:.3rem; }
.ser-hero-sub { font-size:.8rem;color:rgba(255,255,255,.5);line-height:1.6; }
.ser-hero-acts { display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.9rem; }
.ser-act-btn { display:inline-flex;align-items:center;gap:.4rem;border-radius:11px;padding:.5rem 1.1rem;
               font-size:.8rem;font-weight:700;cursor:pointer;font-family:inherit;border:none;
               text-decoration:none;transition:filter .18s,transform .12s; }
.ser-act-btn:hover { filter:brightness(1.08);transform:translateY(-1px); }
.ser-btn-primary   { background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.25); }
.ser-btn-secondary { background:rgba(255,255,255,.1);color:rgba(255,255,255,.85);border:1px solid rgba(255,255,255,.15); }

/* Stats grid */
.ser-stats { display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.8rem;margin-bottom:1.2rem; }
.ser-stat-card { background:#fff;border-radius:16px;border:1px solid #f0f4f8;padding:1rem 1.2rem;
                 box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 14px rgba(0,0,0,.04);
                 animation:ser-up .4s cubic-bezier(.16,1,.3,1) both; }
@keyframes ser-up { from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)} }
.ser-stat-val { font-size:1.6rem;font-weight:900;font-family:'SUSE',sans-serif;line-height:1;margin-bottom:.2rem; }
.ser-stat-lbl { font-size:.68rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em; }

/* Review section */
.ser-review-head { display:flex;align-items:center;justify-content:space-between;margin-bottom:.9rem;flex-wrap:wrap;gap:.5rem; }
.ser-section-title { font-size:1rem;font-weight:800;color:#0f172a;font-family:'SUSE',sans-serif; }
.ser-filter-bar { display:flex;gap:.3rem;flex-wrap:wrap; }
.ser-filter-btn { padding:.32rem .85rem;border-radius:100px;font-size:.73rem;font-weight:700;
                  border:1.5px solid #e2e8f0;background:#fff;color:#64748b;cursor:pointer;transition:all .15s; }
.ser-filter-btn:hover,.ser-filter-btn.active { background:#4f46e5;color:#fff;border-color:#4f46e5; }

/* Chapter breakdown */
.ser-chapter-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:.7rem;margin-bottom:1.3rem; }
.ser-chapter-card { background:#fff;border-radius:14px;border:1px solid #f0f4f8;padding:.85rem 1rem;
                    box-shadow:0 1px 3px rgba(0,0,0,.04); }
.ser-chapter-name { font-size:.8rem;font-weight:700;color:#0f172a;margin-bottom:.45rem; }
.ser-chapter-bar { height:6px;background:#f0f4f8;border-radius:100px;overflow:hidden;margin-bottom:.35rem; }
.ser-chapter-fill { height:100%;border-radius:100px;transition:width 1.4s cubic-bezier(.16,1,.3,1); }
.ser-chapter-stats { display:flex;justify-content:space-between;font-size:.68rem;color:#94a3b8;font-weight:600; }

/* Q review accordion */
.ser-qa { border:1px solid #f0f4f8;border-radius:14px;overflow:hidden;background:#fff;
          box-shadow:0 1px 3px rgba(0,0,0,.04);margin-bottom:.55rem;
          animation:ser-up .4s cubic-bezier(.16,1,.3,1) both; }
.ser-qa-head { display:flex;align-items:center;gap:.7rem;padding:.8rem 1rem;cursor:pointer;
               transition:background .15s;user-select:none; }
.ser-qa-head:hover { background:#f8fafc; }
.ser-qa-num { width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;
              font-size:.72rem;font-weight:800;flex-shrink:0; }
.ser-qa-uid { font-family:monospace;font-size:.66rem;font-weight:800;background:#0f172a;color:#e2e8f0;
              padding:.12rem .45rem;border-radius:6px; }
.ser-qa-stem { flex:1;font-size:.84rem;font-weight:600;color:#1e293b;line-height:1.3;min-width:0;
               white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.ser-qa-result { font-size:.7rem;font-weight:800;border-radius:100px;padding:.18rem .65rem;flex-shrink:0; }
.ser-qa-chev { font-size:.75rem;color:#94a3b8;flex-shrink:0;transition:transform .2s; }
.ser-qa.open .ser-qa-chev { transform:rotate(180deg); }
.ser-qa-body { display:none;padding:0 1rem 1rem; }
.ser-qa.open .ser-qa-body { display:block; }
.ser-qa-full-stem { font-size:.88rem;color:#1e293b;line-height:1.65;margin-bottom:.85rem; }

/* Option review */
.ser-opt-list { display:flex;flex-direction:column;gap:.45rem;margin-bottom:.75rem; }
.ser-opt-item { display:flex;align-items:flex-start;gap:.6rem;padding:.6rem .8rem;border-radius:10px;border:1.5px solid #e2e8f0; }
.ser-opt-item.correct   { background:#dcfce7;border-color:#86efac; }
.ser-opt-item.wrong     { background:#fee2e2;border-color:#fca5a5; }
.ser-opt-item.chosen    { font-weight:700; }
.ser-opt-label { width:22px;height:22px;border-radius:50%;background:#e2e8f0;display:flex;align-items:center;
                 justify-content:center;font-size:.72rem;font-weight:800;flex-shrink:0; }
.ser-opt-item.correct .ser-opt-label { background:#059669;color:#fff; }
.ser-opt-item.wrong    .ser-opt-label { background:#dc2626;color:#fff; }
.ser-opt-text-r { font-size:.84rem;color:#334155;flex:1; }

/* Explanation */
.ser-explanation { background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:.7rem .9rem;
                   font-size:.82rem;color:#1e40af;line-height:1.55; }
.ser-explanation strong { font-weight:800; }

/* Text answer display */
.ser-text-answer { background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:.65rem .9rem;
                   font-size:.85rem;color:#334155;font-style:italic;margin-bottom:.6rem; }
.ser-manual-note { font-size:.75rem;color:#94a3b8;font-style:italic; }

/* Skeleton */
.ser-skel { background:linear-gradient(90deg,#f0f4f8 25%,#e2e8f0 50%,#f0f4f8 75%);
            background-size:200% 100%;animation:ser-shim 1.5s infinite;border-radius:8px; }
@keyframes ser-shim { 0%{background-position:200% 0}100%{background-position:-200% 0} }

@media print {
  .ser-act-btn, .ser-filter-bar { display:none!important; }
  .ser-qa-body { display:block!important; }
  .ser-qa-head { cursor:default; }
}
</style>

<div class="container-fluid px-3 py-3 ser-wrap">

<!-- Hero (filled by JS) -->
<div class="ser-hero" id="resultsHero">
  <div class="ser-hero-grid"></div>
  <div class="ser-hero-inner">
    <div class="ser-ring-wrap">
      <svg class="ser-ring" viewBox="0 0 130 130">
        <circle cx="65" cy="65" r="54" fill="none" stroke="rgba(255,255,255,.15)" stroke-width="10"/>
        <circle cx="65" cy="65" r="54" fill="none" stroke="rgba(255,255,255,.85)" stroke-width="10"
                stroke-linecap="round" stroke-dasharray="339.3" stroke-dashoffset="339.3"
                style="transform:rotate(-90deg);transform-origin:center;transition:stroke-dashoffset 1.6s cubic-bezier(.16,1,.3,1)"
                id="scoreRing"/>
      </svg>
      <div class="ser-ring-pct">
        <div class="ser-ring-num" id="scorePct">—</div>
        <div class="ser-ring-sub">Score</div>
      </div>
    </div>
    <div class="ser-hero-info">
      <div class="ser-skel" style="width:90px;height:30px;border-radius:100px;margin-bottom:.75rem"></div>
      <div class="ser-skel" style="width:70%;height:28px;border-radius:8px;margin-bottom:.3rem"></div>
      <div class="ser-skel" style="width:50%;height:14px;border-radius:6px"></div>
    </div>
  </div>
</div>

<!-- Stats -->
<div class="ser-stats" id="statsGrid">
  <?php for ($i=0;$i<5;$i++): ?>
  <div class="ser-stat-card"><div class="ser-skel" style="width:60px;height:36px;margin-bottom:8px"></div><div class="ser-skel" style="width:80px;height:12px"></div></div>
  <?php endfor; ?>
</div>

<!-- Chapter breakdown -->
<div id="chapterSection" style="display:none">
  <div class="ser-review-head"><div class="ser-section-title"><i class="bi bi-bar-chart-line me-2"></i>Performance by Chapter</div></div>
  <div class="ser-chapter-grid" id="chapterGrid"></div>
</div>

<!-- Question Review -->
<div id="reviewSection" style="display:none">
  <div class="ser-review-head">
    <div class="ser-section-title"><i class="bi bi-list-check me-2"></i>Question Review</div>
    <div class="ser-filter-bar">
      <button class="ser-filter-btn active" onclick="filterReview('all')">All</button>
      <button class="ser-filter-btn" onclick="filterReview('correct')"><i class="bi bi-check-circle"></i> Correct</button>
      <button class="ser-filter-btn" onclick="filterReview('incorrect')"><i class="bi bi-x-circle"></i> Incorrect</button>
      <button class="ser-filter-btn" onclick="filterReview('skipped')"><i class="bi bi-dash-circle"></i> Skipped</button>
    </div>
  </div>
  <div id="reviewList"></div>
</div>

<!-- No review note -->
<div id="noReviewNote" style="display:none;text-align:center;padding:2.5rem;background:#fff;border-radius:16px;border:1.5px dashed #e2e8f0;color:#94a3b8">
  <i class="bi bi-eye-slash" style="font-size:2.5rem;display:block;margin-bottom:.75rem"></i>
  <div style="font-weight:700;color:#475569;margin-bottom:.3rem">Answer review not available</div>
  <div style="font-size:.82rem">Your teacher has disabled answer review for this exam.</div>
</div>

</div>

<script>
const SESSION_ID_R = <?= $session_id ?>;
let sessionData  = null;
let allAnswers   = [];
let reviewFilter = 'all';

function _serInit() {
  fetch(`ajax/ajax_student_exam.php?action=results&session_id=${SESSION_ID_R}`)
    .then(r=>r.json()).then(res => {
      if (res.status !== 'success') {
        document.getElementById('resultsHero').innerHTML += `<div style="position:relative;z-index:1;color:#fca5a5;font-weight:700;margin-top:1rem">${res.message||'Could not load results'}</div>`;
        return;
      }
      sessionData = res.session;
      allAnswers  = res.answers || [];
      renderHero(sessionData);
      renderStats(sessionData, allAnswers);
      renderChapters(allAnswers);
      if (sessionData.show_answers_after) {
        document.getElementById('reviewSection').style.display = '';
        renderReview('all');
      } else {
        document.getElementById('noReviewNote').style.display = '';
      }
    }).catch(() => {
      const errHtml = `<div style="position:relative;z-index:1;color:#fca5a5;font-weight:700;margin-top:1rem"><i class="bi bi-wifi-off"></i> Failed to load results. Please refresh.</div>`;
      document.getElementById('resultsHero').insertAdjacentHTML('beforeend', errHtml);
      document.getElementById('statsGrid').innerHTML = '';
    });
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', _serInit);
} else { _serInit(); }

/* ── Hero ─────────────────────────────────────────────────── */
function renderHero(s) {
  const pct    = s.total_marks > 0 ? Math.round(s.score / s.total_marks * 100) : 0;
  const passed = parseFloat(s.score) >= parseFloat(s.passing_marks);
  const bgColor= passed
    ? 'linear-gradient(135deg,#052e16 0%,#064e3b 45%,#065f46 100%)'
    : 'linear-gradient(135deg,#450a0a 0%,#7f1d1d 45%,#991b1b 100%)';
  const hero = document.getElementById('resultsHero');
  hero.style.background = bgColor;
  document.getElementById('scorePct').textContent = pct + '%';

  // Animate ring
  setTimeout(() => {
    const circum = 2 * Math.PI * 54;
    document.getElementById('scoreRing').style.strokeDashoffset = circum * (1 - pct/100);
  }, 100);

  const date = s.submitted_at ? new Date(s.submitted_at).toLocaleString('en-GB',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}) : '—';

  document.querySelector('.ser-hero-info').innerHTML = `
    <div class="ser-pass-badge" style="background:${passed?'rgba(5,150,105,.3)':'rgba(220,38,38,.3)'};color:${passed?'#6ee7b7':'#fca5a5'}">
      <i class="bi bi-${passed?'trophy-fill':'x-circle-fill'}"></i>${passed?'PASSED':'NOT PASSED'}
    </div>
    <div class="ser-hero-title">${s.exam_title}</div>
    <div class="ser-hero-sub">
      ${s.subject_name?s.subject_name+' · ':''}${s.level_name||''}<br>
      Score: <strong style="color:#fff">${parseFloat(s.score).toFixed(1)} / ${parseFloat(s.total_marks).toFixed(1)}</strong> marks &nbsp;·&nbsp; Pass mark: ${parseFloat(s.passing_marks).toFixed(1)}<br>
      Submitted: ${date}
    </div>
    <div class="ser-hero-acts">
      <a href="?view=student_exams" class="ser-act-btn ser-btn-primary"><i class="bi bi-arrow-left"></i>Back to Exams</a>
      <button class="ser-act-btn ser-btn-secondary" onclick="window.print()"><i class="bi bi-printer"></i>Print Results</button>
    </div>`;
}

/* ── Stats ────────────────────────────────────────────────── */
function renderStats(s, answers) {
  const total     = answers.length;
  const correct   = answers.filter(a => a.is_correct == 1).length;
  const incorrect = answers.filter(a => a.answer_given && a.answer_given !== '' && a.is_correct == 0).length;
  const skipped   = answers.filter(a => !a.answer_given || a.answer_given === '').length;
  const pct       = s.total_marks > 0 ? Math.round(s.score / s.total_marks * 100) : 0;
  const mins       = s.time_taken_seconds ? Math.floor(s.time_taken_seconds/60)+'m '+Math.round(s.time_taken_seconds%60)+'s' : '—';

  const stats = [
    {val:correct,   lbl:'Correct',   color:'#059669', delay:0},
    {val:incorrect, lbl:'Incorrect', color:'#dc2626', delay:.07},
    {val:skipped,   lbl:'Skipped',   color:'#94a3b8', delay:.14},
    {val:mins,      lbl:'Time Taken',color:'#4f46e5', delay:.21},
    {val:total,     lbl:'Questions', color:'#0ea5e9', delay:.28},
  ];
  document.getElementById('statsGrid').innerHTML = stats.map(st => `
    <div class="ser-stat-card" style="animation-delay:${st.delay}s">
      <div class="ser-stat-val" style="color:${st.color}">${st.val}</div>
      <div class="ser-stat-lbl">${st.lbl}</div>
    </div>`).join('');
}

/* ── Chapter breakdown ────────────────────────────────────── */
function renderChapters(answers) {
  const chapters = {};
  answers.forEach(a => {
    const ch = a.chapter_name || 'General';
    if (!chapters[ch]) chapters[ch] = { total:0, correct:0, marks:0, max:0 };
    chapters[ch].total++;
    if (a.is_correct == 1) chapters[ch].correct++;
    chapters[ch].marks += parseFloat(a.marks_awarded || 0);
    chapters[ch].max   += parseFloat(a.marks_override ?? a.marks ?? 0);
  });
  const keys = Object.keys(chapters);
  if (keys.length < 2) return;

  document.getElementById('chapterSection').style.display = '';
  document.getElementById('chapterGrid').innerHTML = keys.map(ch => {
    const d   = chapters[ch];
    const pct = d.total > 0 ? Math.round(d.correct / d.total * 100) : 0;
    const col = pct >= 70 ? '#059669' : pct >= 40 ? '#d97706' : '#dc2626';
    return `<div class="ser-chapter-card">
      <div class="ser-chapter-name">${ch}</div>
      <div class="ser-chapter-bar"><div class="ser-chapter-fill" style="width:0%;background:${col}" data-w="${pct}"></div></div>
      <div class="ser-chapter-stats"><span>${d.correct}/${d.total} correct</span><span style="color:${col};font-weight:700">${pct}%</span></div>
    </div>`;
  }).join('');
  requestAnimationFrame(() => {
    document.querySelectorAll('.ser-chapter-fill').forEach(el => { el.style.width = (el.dataset.w||0)+'%'; });
  });
}

/* ── Question Review ──────────────────────────────────────── */
function filterReview(f) {
  reviewFilter = f;
  document.querySelectorAll('.ser-filter-btn').forEach((b,i) => {
    b.classList.toggle('active', ['all','correct','incorrect','skipped'][i] === f);
  });
  renderReview(f);
}

function renderReview(filter) {
  const filtered = allAnswers.filter(a => {
    if (filter === 'correct')   return a.is_correct == 1;
    if (filter === 'incorrect') return a.answer_given && a.answer_given !== '' && a.is_correct == 0;
    if (filter === 'skipped')   return !a.answer_given || a.answer_given === '';
    return true;
  });

  const container = document.getElementById('reviewList');
  if (!filtered.length) {
    container.innerHTML = `<div style="text-align:center;padding:2rem;color:#94a3b8">No questions in this category.</div>`;
    return;
  }

  container.innerHTML = filtered.map((a, i) => {
    const qid      = parseInt(a.question_id);
    const isSkipped= !a.answer_given || a.answer_given === '';
    const isCorrect= a.is_correct == 1;
    const numColor = isCorrect ? '#059669' : isSkipped ? '#94a3b8' : '#dc2626';
    const numBg    = isCorrect ? '#dcfce7'  : isSkipped ? '#f1f5f9'  : '#fee2e2';
    const resultPill = isCorrect
      ? `<span class="ser-qa-result" style="background:#dcfce7;color:#166534"><i class="bi bi-check2"></i> Correct</span>`
      : isSkipped
      ? `<span class="ser-qa-result" style="background:#f1f5f9;color:#64748b"><i class="bi bi-dash"></i> Skipped</span>`
      : `<span class="ser-qa-result" style="background:#fee2e2;color:#991b1b"><i class="bi bi-x"></i> Incorrect</span>`;

    const maxMk = parseFloat(a.marks_override ?? a.marks ?? 0);
    const gotMk = parseFloat(a.marks_awarded ?? 0);

    // Options HTML
    let bodyHtml = '';
    if (a.options && a.options.length > 0) {
      bodyHtml += `<div class="ser-opt-list">` + a.options.map(opt => {
        const isTheCorrect = opt.is_correct == 1;
        const isChosen     = (a.answer_given||'').toUpperCase() === opt.option_label.toUpperCase();
        let cls = '';
        if (isTheCorrect && isChosen) cls = 'correct chosen';
        else if (isTheCorrect)        cls = 'correct';
        else if (isChosen)            cls = 'wrong chosen';
        const icon = isTheCorrect ? '<i class="bi bi-check-circle-fill" style="color:#059669;margin-left:auto"></i>' : isChosen ? '<i class="bi bi-x-circle-fill" style="color:#dc2626;margin-left:auto"></i>' : '';
        return `<div class="ser-opt-item ${cls}">
          <div class="ser-opt-label">${opt.option_label}</div>
          <div class="ser-opt-text-r">${opt.option_text}</div>${icon}</div>`;
      }).join('') + `</div>`;
    } else if (a.answer_given && a.answer_given !== '') {
      bodyHtml += `<div class="ser-text-answer">"${escHtmlR(a.answer_given)}"</div>`;
      if (a.correct_answer) {
        bodyHtml += `<div class="ser-text-answer" style="background:#dcfce7;border-color:#86efac;color:#166534"><i class="bi bi-check2-circle"></i> Correct answer: <strong>${escHtmlR(a.correct_answer)}</strong></div>`;
      }
      if (a.question_type === 'short_answer' || a.question_type === 'essay') {
        bodyHtml += `<div class="ser-manual-note"><i class="bi bi-info-circle"></i> This question will be manually graded by your teacher.</div>`;
      }
    } else {
      bodyHtml += `<div style="color:#94a3b8;font-size:.84rem;font-style:italic">Not answered</div>`;
    }

    if (a.explanation) {
      bodyHtml += `<div class="ser-explanation" style="margin-top:.65rem"><strong><i class="bi bi-lightbulb-fill"></i> Explanation:</strong> ${a.explanation}</div>`;
    }

    const diffBadge = a.difficulty_name ? `<span style="font-size:.65rem;padding:.1rem .45rem;border-radius:100px;background:#fff7ed;color:#c2410c;font-weight:700">${a.difficulty_name}</span>` : '';
    const marksBadge = `<span style="font-size:.68rem;color:#94a3b8;margin-left:auto"><i class="bi bi-star-fill" style="font-size:.55rem;color:#d97706"></i> ${gotMk.toFixed(1)} / ${maxMk.toFixed(1)} marks</span>`;

    return `
    <div class="ser-qa" id="qa_${i}" style="animation-delay:${Math.min(i*.025,.5)}s">
      <div class="ser-qa-head" onclick="toggleQA(${i})">
        <div class="ser-qa-num" style="background:${numBg};color:${numColor}">${allAnswers.indexOf(a)+1}</div>
        <span class="ser-quid ser-qa-uid">${a.q_uid||''}</span>
        <div class="ser-qa-stem">${stripHtmlR(a.question_stem||'')}</div>
        ${diffBadge}
        ${resultPill}
        ${marksBadge}
        <i class="bi bi-chevron-down ser-qa-chev"></i>
      </div>
      <div class="ser-qa-body">
        <div class="ser-qa-full-stem">${a.question_stem||''}</div>
        ${bodyHtml}
      </div>
    </div>`;
  }).join('');
}

function toggleQA(i) {
  const el = document.getElementById(`qa_${i}`);
  el.classList.toggle('open');
}

function stripHtmlR(html) {
  const d = document.createElement('div'); d.innerHTML = html;
  return (d.textContent || d.innerText || '').trim();
}
function escHtmlR(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

Object.assign(window, { filterReview, toggleQA });
</script>
