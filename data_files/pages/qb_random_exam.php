<?php
// QB Random Exam Generator
?>
<style>
/* ═══════════════════════════════════════════════════════════
   QB RANDOM EXAM  (qbre-*)
═══════════════════════════════════════════════════════════ */
.qbre-wrap { font-family:'Open Sans',sans-serif; }
.qbre-hero { position:relative; overflow:hidden; isolation:isolate; border-radius:20px; padding:2rem 2.2rem; margin-bottom:1.4rem; background:linear-gradient(135deg,#1c0a00 0%,#431407 40%,#7c2d12 100%); }
.qbre-hero-grid { position:absolute; inset:0; z-index:0; background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px); background-size:44px 44px; }
.qbre-hero-inner { position:relative; z-index:1; }
.qbre-hero-badge { display:inline-flex; align-items:center; gap:.4rem; background:rgba(255,255,255,.09); border:1px solid rgba(255,255,255,.15); border-radius:100px; padding:.28rem .85rem; font-size:.7rem; font-weight:700; color:rgba(255,255,255,.7); letter-spacing:.06em; text-transform:uppercase; margin-bottom:.75rem; backdrop-filter:blur(6px); }
.qbre-hero-title { font-size:1.7rem; font-weight:900; color:#fff; font-family:'SUSE',sans-serif; letter-spacing:-.04em; line-height:1.15; margin-bottom:.3rem; }
.qbre-hero-title em { font-style:normal; background:linear-gradient(90deg,#fcd34d,#fb923c); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent; }
.qbre-hero-sub { font-size:.81rem; color:rgba(255,255,255,.45); margin-bottom:1.4rem; max-width:520px; line-height:1.6; }
.qbre-kpis { display:flex; gap:.7rem; flex-wrap:wrap; }
.qbre-kpi { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12); border-radius:14px; padding:.6rem 1rem; backdrop-filter:blur(8px); min-width:90px; }
.qbre-kpi-val { font-size:1.2rem; font-weight:900; color:#fff; font-family:'SUSE',sans-serif; line-height:1; }
.qbre-kpi-lbl { font-size:.63rem; color:rgba(255,255,255,.45); margin-top:.15rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; }

/* ── Step indicator ── */
.qbre-steps { display:flex; gap:0; margin-bottom:1.4rem; background:#fff; border-radius:16px; padding:.6rem; box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 14px rgba(0,0,0,.05); border:1px solid #f0f4f8; }
.qbre-step { flex:1; display:flex; align-items:center; gap:.55rem; padding:.55rem .9rem; border-radius:12px; cursor:pointer; transition:background .15s; }
.qbre-step.active { background:linear-gradient(135deg,#1a4fc4,#6d28d9); }
.qbre-step.done { background:#f0fdf4; }
.qbre-step-num { width:28px; height:28px; border-radius:50%; border:2px solid #e2e8f0; display:flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:800; color:#94a3b8; flex-shrink:0; font-family:'SUSE',sans-serif; }
.qbre-step.active .qbre-step-num { background:rgba(255,255,255,.2); border-color:transparent; color:#fff; }
.qbre-step.done .qbre-step-num { background:#059669; border-color:#059669; color:#fff; }
.qbre-step-lbl { font-size:.79rem; font-weight:700; color:#475569; }
.qbre-step.active .qbre-step-lbl { color:#fff; }
.qbre-step.done .qbre-step-lbl { color:#059669; }

/* ── Panels ── */
.qbre-panel { background:#fff; border-radius:18px; border:1px solid #f0f4f8; box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 16px rgba(0,0,0,.05); overflow:hidden; }
.qbre-panel-head { padding:.9rem 1.2rem; border-bottom:1px solid #f0f4f8; display:flex; align-items:center; gap:.55rem; }
.qbre-panel-title { font-size:.88rem; font-weight:800; color:#0f172a; font-family:'SUSE',sans-serif; }
.qbre-panel-body { padding:1.2rem; }

/* ── Form controls ── */
.qbre-ctrl-lbl { font-size:.74rem; font-weight:700; color:#475569; margin-bottom:.3rem; display:block; }
.qbre-ctrl { width:100%; padding:.52rem .85rem; border-radius:11px; border:1.5px solid #e2e8f0; font-size:.81rem; font-family:inherit; outline:none; background:#f8fafc; color:#1e293b; transition:border-color .18s,box-shadow .18s; }
.qbre-ctrl:focus { border-color:#c2410c; box-shadow:0 0 0 3px rgba(194,65,12,.1); background:#fff; }

/* ── Difficulty table ── */
.qbre-diff-table { width:100%; border-collapse:separate; border-spacing:0 .35rem; }
.qbre-diff-row { }
.qbre-diff-row td { padding:.4rem .6rem; background:#f8fafc; border:1px solid #f0f4f8; }
.qbre-diff-row td:first-child { border-radius:10px 0 0 10px; font-size:.79rem; font-weight:700; color:#334155; }
.qbre-diff-row td:last-child { border-radius:0 10px 10px 0; width:80px; }
.qbre-diff-input { width:100%; padding:.35rem .5rem; border-radius:8px; border:1.5px solid #e2e8f0; font-size:.8rem; text-align:center; font-family:inherit; outline:none; }
.qbre-diff-input:focus { border-color:#c2410c; }

/* ── Bloom checkboxes ── */
.qbre-bloom-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(130px,1fr)); gap:.4rem; }
.qbre-bloom-chk { display:flex; align-items:center; gap:.45rem; padding:.45rem .65rem; background:#f8fafc; border:1px solid #f0f4f8; border-radius:9px; cursor:pointer; transition:all .15s; }
.qbre-bloom-chk:hover { border-color:#bfdbfe; background:#f0f9ff; }
.qbre-bloom-chk input { accent-color:#c2410c; width:14px; height:14px; cursor:pointer; }
.qbre-bloom-chk label { font-size:.77rem; font-weight:600; color:#334155; cursor:pointer; margin:0; }

/* ── Generate button ── */
.qbre-generate-btn { display:inline-flex; align-items:center; gap:.5rem; background:linear-gradient(135deg,#c2410c,#ea580c); color:#fff; border:none; border-radius:13px; padding:.7rem 2rem; font-size:.88rem; font-weight:700; cursor:pointer; font-family:inherit; box-shadow:0 4px 18px rgba(194,65,12,.4); transition:filter .18s,transform .12s; }
.qbre-generate-btn:hover { filter:brightness(1.1); transform:translateY(-1px); }
.qbre-generate-btn:active { transform:scale(.96); }

/* ── Preview cards ── */
.qbre-prev-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:.85rem; margin-top:1rem; }
.qbre-prev-card { background:#fff; border-radius:14px; border:1px solid #f0f4f8; box-shadow:0 1px 3px rgba(0,0,0,.04); padding:.9rem 1rem; animation:qbre-up .35s cubic-bezier(.16,1,.3,1) both; }
@keyframes qbre-up { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
.qbre-prev-uid { font-family:monospace; font-size:.66rem; font-weight:800; background:#0f172a; color:#e2e8f0; padding:.12rem .45rem; border-radius:6px; display:inline-block; margin-bottom:.4rem; }
.qbre-prev-stem { font-size:.8rem; color:#334155; line-height:1.5; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.qbre-prev-meta { display:flex; gap:.25rem; flex-wrap:wrap; margin-top:.45rem; }
.qbre-prev-badge { font-size:.62rem; font-weight:700; padding:.1rem .42rem; border-radius:100px; }

/* ── Action buttons ── */
.qbre-action-row { display:flex; gap:.65rem; flex-wrap:wrap; margin-top:1.1rem; align-items:center; }
.qbre-btn { display:inline-flex; align-items:center; gap:.4rem; border-radius:12px; padding:.58rem 1.3rem; font-size:.82rem; font-weight:700; cursor:pointer; font-family:inherit; border:none; transition:filter .18s; }
.qbre-btn-primary { background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff; box-shadow:0 4px 14px rgba(26,79,196,.3); }
.qbre-btn-primary:hover { filter:brightness(1.1); color:#fff; }
.qbre-btn-success { background:linear-gradient(135deg,#059669,#0d9488); color:#fff; box-shadow:0 4px 14px rgba(5,150,105,.3); }
.qbre-btn-success:hover { filter:brightness(1.08); color:#fff; }
.qbre-btn-outline { background:#f1f5f9; color:#475569; border:1.5px solid #e2e8f0; }
.qbre-btn-outline:hover { background:#e2e8f0; }
.qbre-btn-orange { background:linear-gradient(135deg,#c2410c,#ea580c); color:#fff; }
.qbre-btn-orange:hover { filter:brightness(1.1); }

/* ── Skel ── */
.qbre-skel { background:linear-gradient(90deg,#f0f4f8 25%,#e2e8f0 50%,#f0f4f8 75%); background-size:200% 100%; animation:qbre-shim 1.5s infinite; border-radius:8px; }
@keyframes qbre-shim { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
</style>

<div class="container-fluid px-3 py-3 qbre-wrap">

<!-- ── Hero ──────────────────────────────────────────────── -->
<div class="qbre-hero">
  <div class="qbre-hero-grid"></div>
  <div style="position:absolute;right:3rem;top:50%;transform:translateY(-50%);width:220px;height:220px;border-radius:50%;background:conic-gradient(from 0deg,rgba(217,119,6,.45),rgba(234,88,12,.35),rgba(217,119,6,.45));filter:blur(42px);opacity:.55;animation:db-orb-spin 16s linear infinite;z-index:0"></div>
  <div style="position:absolute;left:30%;bottom:-40px;width:140px;height:140px;border-radius:50%;background:rgba(234,88,12,.35);filter:blur(36px);opacity:.35;z-index:0"></div>
  <div class="qbre-hero-inner">
    <div class="qbre-hero-badge"><i class="bi bi-shuffle"></i>Exam Builder — Random Mode</div>
    <div class="qbre-hero-title"><em>Random</em> Exam Generator</div>
    <div class="qbre-hero-sub">Auto-generate exam papers using distribution rules — specify how many questions per difficulty, filter by Bloom's levels, chapters, and question type. Regenerate until satisfied.</div>
    <div class="qbre-kpis">
      <div class="qbre-kpi"><div class="qbre-kpi-val" id="kpiQ">0</div><div class="qbre-kpi-lbl">Generated Qs</div></div>
      <div class="qbre-kpi"><div class="qbre-kpi-val" id="kpiM">0</div><div class="qbre-kpi-lbl">Total Marks</div></div>
    </div>
  </div>
</div>

<!-- ── Step indicator ── -->
<div class="qbre-steps" id="stepIndicator">
  <div class="qbre-step active" id="step1Ind" onclick="goStep(1)">
    <div class="qbre-step-num">1</div>
    <div class="qbre-step-lbl">Set Criteria</div>
  </div>
  <div class="qbre-step" id="step2Ind" onclick="goStep(2)">
    <div class="qbre-step-num">2</div>
    <div class="qbre-step-lbl">Preview &amp; Save</div>
  </div>
</div>

<!-- ══ STEP 1: Criteria ═══════════════════════════════════ -->
<div id="step1Panel">
  <div class="row g-3">
    <!-- Left: Basic Info -->
    <div class="col-lg-6">
      <div class="qbre-panel">
        <div class="qbre-panel-head" style="background:linear-gradient(135deg,#1c0a00,#431407)">
          <i class="bi bi-file-earmark-text" style="color:#fb923c;font-size:1rem"></i>
          <div class="qbre-panel-title" style="color:#fff">Exam Details</div>
        </div>
        <div class="qbre-panel-body">
          <div class="mb-3">
            <label class="qbre-ctrl-lbl">Exam Title <span class="text-danger">*</span></label>
            <input type="text" id="rTitle" class="qbre-ctrl" placeholder="e.g. Random Mathematics Test">
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="qbre-ctrl-lbl">Subject</label>
              <select id="rSubject" class="qbre-ctrl" onchange="loadRChapters()">
                <option value="">— Select —</option>
              </select>
            </div>
            <div class="col-6">
              <label class="qbre-ctrl-lbl">Level</label>
              <select id="rLevel" class="qbre-ctrl" onchange="loadRChapters()">
                <option value="">— Select —</option>
              </select>
            </div>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="qbre-ctrl-lbl">Duration (minutes)</label>
              <input type="number" id="rDuration" class="qbre-ctrl" value="60" min="1">
            </div>
            <div class="col-6">
              <label class="qbre-ctrl-lbl">Passing Marks</label>
              <input type="number" id="rPassMarks" class="qbre-ctrl" value="0" min="0" step="0.5">
            </div>
          </div>

          <label class="qbre-ctrl-lbl">Question Type Filter</label>
          <select id="rType" class="qbre-ctrl mb-3">
            <option value="">All Types (Mixed)</option>
            <option value="mcq">MCQ Only</option>
            <option value="true_false">True / False Only</option>
            <option value="essay">Essay Only</option>
            <option value="fill_blank">Fill in the Blank Only</option>
          </select>

          <label class="qbre-ctrl-lbl mb-2">Chapters (leave empty for all)</label>
          <div id="chapterCheckboxes" style="display:grid;grid-template-columns:repeat(2,1fr);gap:.35rem;max-height:200px;overflow-y:auto;">
            <div style="font-size:.77rem;color:#94a3b8;padding:.5rem">Select a subject and level first</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: Distribution & Bloom -->
    <div class="col-lg-6">
      <div class="qbre-panel mb-3">
        <div class="qbre-panel-head" style="background:linear-gradient(135deg,#431407,#7c2d12)">
          <i class="bi bi-speedometer2" style="color:#fb923c;font-size:1rem"></i>
          <div class="qbre-panel-title" style="color:#fff">Difficulty Distribution</div>
        </div>
        <div class="qbre-panel-body">
          <p style="font-size:.77rem;color:#64748b;margin-bottom:.85rem">Specify how many questions to pick for each difficulty level. Leave 0 to skip.</p>
          <table class="qbre-diff-table" id="diffTable">
            <tr><td colspan="2"><div class="qbre-skel" style="height:38px"></div></td></tr>
          </table>
        </div>
      </div>

      <div class="qbre-panel">
        <div class="qbre-panel-head" style="background:linear-gradient(135deg,#7c2d12,#9a3412)">
          <i class="bi bi-bar-chart-steps" style="color:#fb923c;font-size:1rem"></i>
          <div class="qbre-panel-title" style="color:#fff">Bloom's Taxonomy Filter</div>
        </div>
        <div class="qbre-panel-body">
          <p style="font-size:.77rem;color:#64748b;margin-bottom:.85rem">Check the cognitive levels to include. Leave all unchecked to include all levels.</p>
          <div class="qbre-bloom-grid" id="bloomCheckboxes">
            <div class="qbre-skel" style="height:36px"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div style="margin-top:1.2rem;text-align:center">
    <button class="qbre-generate-btn" onclick="generateRandom()">
      <i class="bi bi-lightning-charge-fill"></i>Generate Random Exam
    </button>
  </div>
</div>

<!-- ══ STEP 2: Preview & Save ════════════════════════════ -->
<div id="step2Panel" style="display:none">
  <div class="qbre-panel">
    <div class="qbre-panel-head" style="background:linear-gradient(135deg,#1c0a00,#431407)">
      <i class="bi bi-eye" style="color:#fb923c;font-size:1rem"></i>
      <div class="qbre-panel-title" style="color:#fff">Preview — Generated Questions</div>
      <div class="ms-auto d-flex align-items-center gap-2">
        <span style="font-size:.77rem;color:rgba(255,255,255,.6)" id="prevSummary"></span>
      </div>
    </div>
    <div class="qbre-panel-body">
      <div id="previewGrid" class="qbre-prev-grid"></div>
      <div class="qbre-action-row">
        <button class="qbre-btn qbre-btn-orange" onclick="generateRandom()"><i class="bi bi-arrow-clockwise"></i>Regenerate</button>
        <button class="qbre-btn qbre-btn-outline" onclick="goStep(1)"><i class="bi bi-arrow-left"></i>Back to Criteria</button>
        <button class="qbre-btn qbre-btn-primary" onclick="saveRandomExam('draft')"><i class="bi bi-floppy"></i>Save as Draft</button>
        <button class="qbre-btn qbre-btn-success" onclick="saveRandomExam('published')"><i class="bi bi-send-fill"></i>Publish Exam</button>
      </div>
    </div>
  </div>
</div>

</div><!-- /.container-fluid -->

<script>
const dcmAlert = {
  _css:`
    .ds-pop{border-radius:20px!important;font-family:'Open Sans',sans-serif!important;padding:1.6rem!important}
    .ds-ttl{font-size:1.1rem!important;font-weight:800!important;color:#0f172a!important;margin-top:.3rem!important}
    .ds-btn{border-radius:11px!important;font-weight:700!important;font-size:.82rem!important;padding:.55rem 1.4rem!important}
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
};

const TYPE_CFG = {
  mcq:        {light:'#eff6ff',  text:'#1a4fc4', label:'MCQ'},
  true_false: {light:'#f0fdf4',  text:'#059669', label:'T/F'},
  essay:      {light:'#fffbeb',  text:'#d97706', label:'Essay'},
  fill_blank: {light:'#f0f9ff',  text:'#0891b2', label:'Fill'},
};

let generatedQuestions = [];
let currentStep = 1;

function _qbreInit() {
  loadTaxonomy();
  loadDifficulties();
  loadBlooms();
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', _qbreInit);
} else {
  _qbreInit();
}

function goStep(n) {
  currentStep = n;
  document.getElementById('step1Panel').style.display = n === 1 ? '' : 'none';
  document.getElementById('step2Panel').style.display = n === 2 ? '' : 'none';
  document.getElementById('step1Ind').classList.toggle('active', n===1);
  document.getElementById('step2Ind').classList.toggle('active', n===2);
  document.getElementById('step1Ind').classList.toggle('done', n===2);
}

function loadTaxonomy() {
  [
    {entity:'subjects',el:'rSubject',v:'subject_id',l:'subject_name'},
    {entity:'levels',  el:'rLevel',  v:'level_id',  l:'level_name'},
  ].forEach(({entity,el,v,l}) => {
    fetch(`ajax/ajax_qb_taxonomy.php?entity=${entity}&action=list`)
      .then(r=>r.json()).then(res=>{
        if (res.status!=='success') return;
        const sel = document.getElementById(el);
        res.data.forEach(row => sel.add(new Option(row[l], row[v])));
      });
  });
}

function loadDifficulties() {
  fetch('ajax/ajax_qb_taxonomy.php?entity=difficulty_levels&action=list')
    .then(r=>r.json()).then(res=>{
      if (res.status!=='success') return;
      const tbl = document.getElementById('diffTable');
      tbl.innerHTML = res.data.map(d => `
        <tr class="qbre-diff-row">
          <td><i class="bi bi-speedometer2 me-2" style="opacity:.5"></i>${d.difficulty_name}</td>
          <td><input type="number" class="qbre-diff-input" id="diff_${d.difficulty_id}" value="0" min="0" placeholder="0"></td>
        </tr>`).join('');
    });
}

function loadBlooms() {
  fetch('ajax/ajax_qb_taxonomy.php?entity=bloom_levels&action=list')
    .then(r=>r.json()).then(res=>{
      if (res.status!=='success') return;
      const div = document.getElementById('bloomCheckboxes');
      div.innerHTML = res.data.map(b => `
        <label class="qbre-bloom-chk">
          <input type="checkbox" id="bloom_${b.bloom_id}" value="${b.bloom_id}">
          <label for="bloom_${b.bloom_id}">${b.bloom_name}</label>
        </label>`).join('');
    });
}

function loadRChapters() {
  const subj  = document.getElementById('rSubject').value;
  const level = document.getElementById('rLevel').value;
  const div   = document.getElementById('chapterCheckboxes');
  div.innerHTML = '<div style="font-size:.77rem;color:#94a3b8;padding:.5rem">Loading…</div>';

  if (!subj && !level) {
    div.innerHTML = '<div style="font-size:.77rem;color:#94a3b8;padding:.5rem">Select a subject and level first</div>';
    return;
  }

  fetch('ajax/ajax_qb_taxonomy.php?entity=chapters&action=list')
    .then(r=>r.json()).then(res=>{
      if (res.status!=='success') return;
      const chapters = res.data.filter(r=>(!subj||r.subject_id==subj)&&(!level||r.level_id==level));
      if (!chapters.length) {
        div.innerHTML = '<div style="font-size:.77rem;color:#94a3b8;padding:.5rem">No chapters found</div>';
        return;
      }
      div.innerHTML = chapters.map(c => `
        <label style="display:flex;align-items:center;gap:.45rem;padding:.42rem .6rem;background:#f8fafc;border:1px solid #f0f4f8;border-radius:9px;cursor:pointer;font-size:.76rem;font-weight:600;color:#334155">
          <input type="checkbox" id="chap_${c.chapter_id}" value="${c.chapter_id}" style="accent-color:#c2410c;width:13px;height:13px">
          ${(c.chapter_number?`Ch.${c.chapter_number} – `:'')+c.chapter_name}
        </label>`).join('');
    });
}

function generateRandom() {
  const title = document.getElementById('rTitle').value.trim();
  if (!title) { dcmAlert.error('Exam title required', 'Please enter a title before generating.'); return; }

  // Collect difficulty counts
  const difficulty_counts = {};
  document.querySelectorAll('[id^="diff_"]').forEach(inp => {
    const cnt = parseInt(inp.value) || 0;
    if (cnt > 0) difficulty_counts[inp.id.replace('diff_','')] = cnt;
  });
  if (Object.keys(difficulty_counts).length === 0) {
    dcmAlert.error('No questions specified', 'Set at least one difficulty count > 0.'); return;
  }

  // Bloom IDs
  const bloom_ids = [...document.querySelectorAll('[id^="bloom_"]:checked')].map(el => +el.value);

  // Chapter IDs
  const chapter_ids = [...document.querySelectorAll('[id^="chap_"]:checked')].map(el => +el.value);

  const payload = {
    action:            'random_generate',
    subject_id:        parseInt(document.getElementById('rSubject').value) || 0,
    level_id:          parseInt(document.getElementById('rLevel').value) || 0,
    chapter_ids,
    difficulty_counts,
    bloom_ids,
    type_filter:       document.getElementById('rType').value,
  };

  dcmAlert.loading('Generating questions…');
  fetch('ajax/ajax_qb_exams.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify(payload)
  }).then(r=>r.json()).then(res=>{
    Swal.close();
    if (res.status !== 'success') { dcmAlert.error('Generation failed', res.message); return; }
    generatedQuestions = res.data || [];
    if (!generatedQuestions.length) {
      dcmAlert.error('No questions found', 'No published questions match your criteria. Adjust filters and try again.');
      return;
    }
    renderPreview();
    goStep(2);
  }).catch(() => dcmAlert.error('Request failed', 'Unable to reach server.'));
}

function renderPreview() {
  const total_marks = generatedQuestions.reduce((s,q)=>s+parseFloat(q.marks||0),0);
  document.getElementById('kpiQ').textContent = generatedQuestions.length;
  document.getElementById('kpiM').textContent = total_marks.toFixed(0);
  document.getElementById('prevSummary').textContent = `${generatedQuestions.length} questions · ${total_marks.toFixed(0)} marks`;

  document.getElementById('previewGrid').innerHTML = generatedQuestions.map((q,i) => {
    const tc = TYPE_CFG[q.question_type] || {light:'#f1f5f9',text:'#475569',label:q.question_type};
    const stem = stripHtml(q.question_stem||'').trim();
    return `
    <div class="qbre-prev-card" style="animation-delay:${Math.min(i*0.03,.25)}s">
      <span class="qbre-prev-uid">${q.q_uid}</span>
      <div class="qbre-prev-stem">${stem||'<em style="color:#94a3b8">No text</em>'}</div>
      <div class="qbre-prev-meta">
        <span class="qbre-prev-badge" style="background:${tc.light};color:${tc.text}">${tc.label}</span>
        ${q.difficulty_name?`<span class="qbre-prev-badge" style="background:#fff7ed;color:#c2410c">${q.difficulty_name}</span>`:''}
        <span class="qbre-prev-badge" style="background:#f1f5f9;color:#475569"><i class="bi bi-star-fill" style="font-size:.55rem"></i>${q.marks}</span>
      </div>
    </div>`;
  }).join('');
}

async function saveRandomExam(status) {
  const title = document.getElementById('rTitle').value.trim();
  if (!title) { dcmAlert.error('Exam title required'); return; }

  dcmAlert.loading('Creating exam…');

  // Step 1: create exam
  const examPayload = {
    action:           'save',
    exam_id:          0,
    exam_title:       title,
    subject_id:       parseInt(document.getElementById('rSubject').value) || 0,
    level_id:         parseInt(document.getElementById('rLevel').value) || 0,
    duration_minutes: parseInt(document.getElementById('rDuration').value) || 60,
    passing_marks:    parseFloat(document.getElementById('rPassMarks').value) || 0,
    exam_type:        'random',
    status:           status,
  };

  const r1 = await fetch('ajax/ajax_qb_exams.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify(examPayload)
  }).then(r=>r.json());

  if (r1.status !== 'success') { Swal.close(); dcmAlert.error('Could not create exam', r1.message); return; }
  const exam_id = r1.exam_id;

  // Step 2: add all questions
  let added = 0;
  for (const q of generatedQuestions) {
    const r2 = await fetch('ajax/ajax_qb_exams.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({action:'add_question', exam_id, question_id: q.question_id})
    }).then(r=>r.json());
    if (r2.status === 'success') added++;
  }

  Swal.close();
  dcmAlert.success('Exam created!', `${added} questions added. Redirecting…`);
  setTimeout(() => { window.location.href = `?view=qb_exam_templates`; }, 1800);
}

function stripHtml(html) {
  const tmp = document.createElement('div');
  tmp.innerHTML = html;
  return tmp.textContent || tmp.innerText || '';
}

Object.assign(window, { generateRandom, goStep, saveRandomExam, loadRChapters });
</script>
