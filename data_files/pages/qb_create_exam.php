<?php
$exam_id = (int)($_GET['exam_id'] ?? 0);
$is_edit = $exam_id > 0;

$hero_bg = $is_edit
  ? 'linear-gradient(135deg,#1a0845 0%,#2d1065 40%,#4c1d95 100%)'
  : 'linear-gradient(135deg,#052e16 0%,#064e3b 40%,#065f46 100%)';
$hero_orb1 = $is_edit ? 'rgba(109,40,217,.45)' : 'rgba(5,150,105,.45)';
$hero_orb2 = $is_edit ? 'rgba(124,58,237,.32)' : 'rgba(13,148,136,.32)';
?>
<style>
/* ═══════════════════════════════════════════════════════════
   QB CREATE EXAM  (qbe-*)
═══════════════════════════════════════════════════════════ */
.qbe-wrap { font-family:'Open Sans',sans-serif; }
.qbe-hero { position:relative; overflow:hidden; isolation:isolate; border-radius:20px; padding:1.6rem 2.2rem; margin-bottom:1.4rem; }
.qbe-hero-grid { position:absolute; inset:0; z-index:0; background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px); background-size:44px 44px; }
.qbe-hero-inner { position:relative; z-index:1; }
.qbe-hero-badge { display:inline-flex; align-items:center; gap:.4rem; background:rgba(255,255,255,.09); border:1px solid rgba(255,255,255,.15); border-radius:100px; padding:.28rem .85rem; font-size:.7rem; font-weight:700; color:rgba(255,255,255,.7); letter-spacing:.06em; text-transform:uppercase; margin-bottom:.65rem; backdrop-filter:blur(6px); }
.qbe-hero-title { font-size:1.5rem; font-weight:900; color:#fff; font-family:'SUSE',sans-serif; letter-spacing:-.04em; line-height:1.15; margin-bottom:.25rem; }
.qbe-hero-title em { font-style:normal; background:linear-gradient(90deg,#6ee7b7,#60a5fa); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent; }
.qbe-hero-sub { font-size:.79rem; color:rgba(255,255,255,.45); line-height:1.6; }
.qbe-hero-chips { display:flex; gap:.5rem; flex-wrap:wrap; margin-top:.75rem; }
.qbe-hero-chip { display:inline-flex; align-items:center; gap:.35rem; background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.18); border-radius:100px; padding:.25rem .75rem; font-size:.72rem; font-weight:700; color:rgba(255,255,255,.85); }

/* ── Panels ── */
.qbe-panel { background:#fff; border-radius:18px; border:1px solid #f0f4f8; box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 16px rgba(0,0,0,.05); overflow:hidden; height:100%; }
.qbe-panel-head { padding:.9rem 1.2rem; border-bottom:1px solid #f0f4f8; display:flex; align-items:center; gap:.55rem; }
.qbe-panel-icon { width:30px; height:30px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:.9rem; color:#fff; flex-shrink:0; }
.qbe-panel-title { font-size:.85rem; font-weight:800; color:#0f172a; font-family:'SUSE',sans-serif; }
.qbe-panel-body { padding:1.1rem 1.2rem; overflow-y:auto; }
.qbe-panel-scroll { max-height:calc(100vh - 260px); overflow-y:auto; padding:1.1rem 1.2rem; }

/* ── Form controls ── */
.qbe-ctrl-lbl { font-size:.74rem; font-weight:700; color:#475569; margin-bottom:.3rem; display:block; }
.qbe-ctrl { width:100%; padding:.52rem .85rem; border-radius:11px; border:1.5px solid #e2e8f0; font-size:.81rem; font-family:inherit; outline:none; background:#f8fafc; color:#1e293b; transition:border-color .18s,box-shadow .18s; }
.qbe-ctrl:focus { border-color:#1a4fc4; box-shadow:0 0 0 3px rgba(26,79,196,.1); background:#fff; }
.qbe-ctrl.is-invalid { border-color:#e11d48; }
.qbe-check-row { display:flex; align-items:center; gap:.55rem; padding:.55rem .8rem; background:#f8fafc; border-radius:10px; border:1px solid #f0f4f8; cursor:pointer; transition:background .15s; }
.qbe-check-row:hover { background:#f1f5f9; }
.qbe-check-row input[type=checkbox] { width:15px; height:15px; accent-color:#1a4fc4; flex-shrink:0; cursor:pointer; }
.qbe-check-row label { font-size:.79rem; font-weight:600; color:#334155; cursor:pointer; margin:0; flex:1; }

/* ── Save buttons ── */
.qbe-save-row { display:flex; gap:.55rem; padding:1rem 1.2rem; border-top:1px solid #f0f4f8; flex-wrap:wrap; }
.qbe-save-btn { flex:1; display:inline-flex; align-items:center; justify-content:center; gap:.4rem; border-radius:11px; padding:.6rem 1rem; font-size:.82rem; font-weight:700; cursor:pointer; font-family:inherit; border:none; transition:filter .18s; min-width:100px; }
.qbe-save-btn-draft { background:#f1f5f9; color:#475569; border:1.5px solid #e2e8f0; }
.qbe-save-btn-draft:hover { background:#e2e8f0; }
.qbe-save-btn-publish { background:linear-gradient(135deg,#059669,#0d9488); color:#fff; box-shadow:0 4px 14px rgba(5,150,105,.3); }
.qbe-save-btn-publish:hover { filter:brightness(1.08); }
.qbe-save-btn-primary { background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff; box-shadow:0 4px 14px rgba(26,79,196,.3); }
.qbe-save-btn-primary:hover { filter:brightness(1.08); }

/* ── Bank search ── */
.qbe-bank-search-wrap { position:relative; margin-bottom:.7rem; }
.qbe-bank-search-wrap i { position:absolute; left:.75rem; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:.82rem; pointer-events:none; }
.qbe-bank-search { width:100%; padding:.48rem .85rem .48rem 2.15rem; border-radius:10px; border:1.5px solid #e2e8f0; font-size:.8rem; font-family:inherit; outline:none; background:#f8fafc; color:#1e293b; transition:border-color .18s; }
.qbe-bank-search:focus { border-color:#1a4fc4; box-shadow:0 0 0 3px rgba(26,79,196,.1); background:#fff; }
.qbe-filter-row { display:flex; gap:.4rem; flex-wrap:wrap; margin-bottom:.75rem; }
.qbe-filter-sel { flex:1; min-width:80px; padding:.42rem .6rem; border-radius:9px; border:1.5px solid #e2e8f0; font-size:.75rem; font-family:inherit; outline:none; background:#f8fafc; color:#475569; }
.qbe-filter-sel:focus { border-color:#1a4fc4; }

/* ── Bank question row ── */
.qbe-qrow { display:flex; align-items:flex-start; gap:.65rem; padding:.65rem .75rem; border-radius:12px; border:1px solid #f0f4f8; margin-bottom:.45rem; background:#fff; transition:border-color .15s,background .15s; }
.qbe-qrow:hover { border-color:#bfdbfe; background:#f8fbff; }
.qbe-qrow.added { background:#f0fdf4; border-color:#bbf7d0; }
.qbe-qrow-uid { font-family:monospace; font-size:.65rem; font-weight:800; background:#0f172a; color:#e2e8f0; padding:.12rem .45rem; border-radius:6px; flex-shrink:0; white-space:nowrap; }
.qbe-qrow-stem { font-size:.79rem; color:#334155; line-height:1.5; flex:1; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.qbe-qrow-meta { display:flex; flex-direction:column; align-items:flex-end; gap:.25rem; flex-shrink:0; }
.qbe-qrow-type { font-size:.63rem; font-weight:700; padding:.1rem .42rem; border-radius:100px; white-space:nowrap; }
.qbe-qrow-marks { font-size:.65rem; color:#64748b; font-weight:700; white-space:nowrap; }
.qbe-add-btn { width:28px; height:28px; border-radius:8px; border:none; background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:.78rem; flex-shrink:0; transition:filter .15s,transform .12s; }
.qbe-add-btn:hover { filter:brightness(1.1); transform:scale(1.08); }
.qbe-add-btn:disabled { background:#d1d5db; cursor:not-allowed; transform:none; }

/* ── Selected questions ── */
.qbe-sel-q { display:flex; align-items:flex-start; gap:.55rem; padding:.65rem .8rem; border-radius:12px; border:1px solid #f0f4f8; margin-bottom:.45rem; background:#fff; transition:background .15s; }
.qbe-sel-q:hover { background:#f8fafc; }
.qbe-sel-num { width:24px; height:24px; border-radius:7px; background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff; font-size:.7rem; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-family:'SUSE',sans-serif; }
.qbe-sel-info { flex:1; min-width:0; }
.qbe-sel-stem { font-size:.77rem; color:#334155; line-height:1.45; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; margin-bottom:.3rem; }
.qbe-sel-badges { display:flex; gap:.25rem; flex-wrap:wrap; }
.qbe-sel-acts { display:flex; flex-direction:column; align-items:flex-end; gap:.3rem; flex-shrink:0; }
.qbe-mk-input { width:58px; padding:.28rem .4rem; border-radius:8px; border:1.5px solid #e2e8f0; font-size:.75rem; text-align:center; font-family:inherit; outline:none; }
.qbe-mk-input:focus { border-color:#1a4fc4; }
.qbe-ord-btns { display:flex; gap:.2rem; }
.qbe-ord-btn { width:22px; height:22px; border-radius:6px; border:1px solid #e2e8f0; background:#f8fafc; color:#64748b; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:.62rem; transition:all .12s; }
.qbe-ord-btn:hover { background:#e2e8f0; }
.qbe-rm-btn { width:26px; height:26px; border-radius:7px; border:1.5px solid #fecaca; background:#fff1f2; color:#dc2626; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:.72rem; transition:all .12s; }
.qbe-rm-btn:hover { background:#dc2626; color:#fff; }

/* ── Totals bar ── */
.qbe-totals-bar { display:flex; gap:.55rem; padding:.85rem 1.2rem; background:#f8fafc; border-top:1px solid #f0f4f8; }
.qbe-total-chip { flex:1; text-align:center; }
.qbe-total-val { font-size:1.1rem; font-weight:900; color:#0f172a; font-family:'SUSE',sans-serif; }
.qbe-total-lbl { font-size:.62rem; color:#94a3b8; font-weight:600; text-transform:uppercase; }

/* ── Bank pager ── */
.qbe-bank-pager { display:flex; justify-content:space-between; align-items:center; padding:.65rem .75rem; border-top:1px solid #f0f4f8; margin-top:.4rem; }
.qbe-pager-info { font-size:.72rem; color:#94a3b8; font-weight:600; }
.qbe-pager-btns { display:flex; gap:.3rem; }
.qbe-pager-btn { padding:.32rem .7rem; border-radius:8px; border:1.5px solid #e2e8f0; background:#f8fafc; color:#475569; font-size:.74rem; font-weight:700; cursor:pointer; font-family:inherit; transition:all .15s; }
.qbe-pager-btn:hover { border-color:#1a4fc4; color:#1a4fc4; }
.qbe-pager-btn:disabled { opacity:.4; cursor:not-allowed; }

/* ── Skeleton ── */
.qbe-skel { background:linear-gradient(90deg,#f0f4f8 25%,#e2e8f0 50%,#f0f4f8 75%); background-size:200% 100%; animation:qbe-shim 1.5s infinite; border-radius:8px; }
@keyframes qbe-shim { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ── Empty state ── */
.qbe-empty-small { text-align:center; padding:2.5rem 1rem; color:#94a3b8; font-size:.8rem; }
</style>

<div class="container-fluid px-3 py-3 qbe-wrap">

<!-- ── Hero ──────────────────────────────────────────────── -->
<div class="qbe-hero" style="background:<?= $hero_bg ?>">
  <div class="qbe-hero-grid"></div>
  <div style="position:absolute;right:3rem;top:50%;transform:translateY(-50%);width:180px;height:180px;border-radius:50%;background:conic-gradient(from 0deg,<?= $hero_orb1 ?>,<?= $hero_orb2 ?>,<?= $hero_orb1 ?>);filter:blur(38px);opacity:.55;animation:db-orb-spin 16s linear infinite;z-index:0"></div>

  <div class="qbe-hero-inner">
    <div class="qbe-hero-badge"><i class="bi bi-file-earmark-plus"></i><?= $is_edit ? 'Editing Exam' : 'New Exam' ?></div>
    <div class="qbe-hero-title"><em><?= $is_edit ? 'Edit' : 'Create' ?></em> Exam Builder</div>
    <div class="qbe-hero-sub"><?= $is_edit ? 'Modify exam settings, add or remove questions, and adjust marks.' : 'Build a new exam by configuring settings and selecting questions from the question bank.' ?></div>
    <div class="qbe-hero-chips">
      <span class="qbe-hero-chip" id="chipQCount"><i class="bi bi-patch-question"></i><span id="chipQVal">0</span> Questions</span>
      <span class="qbe-hero-chip" id="chipMarks"><i class="bi bi-star"></i><span id="chipMarkVal">0</span> Total Marks</span>
      <span class="qbe-hero-chip" id="chipCode" style="display:none"><i class="bi bi-tag"></i><span id="chipCodeVal"></span></span>
    </div>
  </div>
</div>

<!-- ── 3-col layout ──────────────────────────────────────── -->
<div class="row g-3">

  <!-- LEFT: Exam Settings -->
  <div class="col-lg-4">
    <div class="qbe-panel" style="display:flex;flex-direction:column;">
      <div class="qbe-panel-head" style="background:<?= $hero_bg ?>">
        <div class="qbe-panel-icon" style="background:rgba(255,255,255,.15)"><i class="bi bi-sliders" style="color:#fff"></i></div>
        <div class="qbe-panel-title" style="color:#fff">Exam Settings</div>
      </div>
      <div class="qbe-panel-scroll" style="flex:1">
        <input type="hidden" id="fExamId" value="<?= $exam_id ?>">

        <div class="mb-3">
          <label class="qbe-ctrl-lbl">Exam Title <span class="text-danger">*</span></label>
          <input type="text" id="fTitle" class="qbe-ctrl" placeholder="e.g. Mathematics Mid-Term Exam">
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="qbe-ctrl-lbl">Subject <span class="text-danger">*</span></label>
            <select id="fSubject" class="qbe-ctrl" onchange="loadChapters()">
              <option value="">— Select —</option>
            </select>
          </div>
          <div class="col-6">
            <label class="qbe-ctrl-lbl">Level <span class="text-danger">*</span></label>
            <select id="fLevel" class="qbe-ctrl" onchange="loadChapters()">
              <option value="">— Select —</option>
            </select>
          </div>
        </div>
        <div class="mb-3">
          <label class="qbe-ctrl-lbl">Description</label>
          <textarea id="fDesc" class="qbe-ctrl" rows="2" placeholder="Brief description of this exam…"></textarea>
        </div>
        <div class="mb-3">
          <label class="qbe-ctrl-lbl">Instructions</label>
          <textarea id="fInstructions" class="qbe-ctrl" rows="3" placeholder="Instructions shown to students before the exam…"></textarea>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="qbe-ctrl-lbl">Duration (minutes)</label>
            <input type="number" id="fDuration" class="qbe-ctrl" value="60" min="1">
          </div>
          <div class="col-6">
            <label class="qbe-ctrl-lbl">Passing Marks</label>
            <input type="number" id="fPassMarks" class="qbe-ctrl" value="0" min="0" step="0.5">
          </div>
        </div>

        <div class="mb-2">
          <label class="qbe-ctrl-lbl">Options</label>
          <div class="d-flex flex-column gap-2">
            <label class="qbe-check-row">
              <input type="checkbox" id="fShuffle">
              <label for="fShuffle"><i class="bi bi-shuffle text-primary me-1" style="font-size:.8rem"></i>Shuffle Questions</label>
            </label>
            <label class="qbe-check-row">
              <input type="checkbox" id="fShuffleOpts">
              <label for="fShuffleOpts"><i class="bi bi-arrow-left-right text-warning me-1" style="font-size:.8rem"></i>Shuffle Options</label>
            </label>
            <label class="qbe-check-row">
              <input type="checkbox" id="fShowAnswers">
              <label for="fShowAnswers"><i class="bi bi-eye-fill text-success me-1" style="font-size:.8rem"></i>Show Answers After Submission</label>
            </label>
          </div>
        </div>
      </div>
      <div class="qbe-save-row">
        <button class="qbe-save-btn qbe-save-btn-draft" onclick="saveExam('draft')"><i class="bi bi-floppy"></i>Save Draft</button>
        <button class="qbe-save-btn qbe-save-btn-publish" onclick="saveExam('published')"><i class="bi bi-send-fill"></i>Publish</button>
      </div>
    </div>
  </div>

  <!-- MIDDLE: Question Bank -->
  <div class="col-lg-4">
    <div class="qbe-panel" style="display:flex;flex-direction:column;">
      <div class="qbe-panel-head" style="background:linear-gradient(135deg,#0b1120,#0f1e3d)">
        <div class="qbe-panel-icon" style="background:rgba(255,255,255,.15)"><i class="bi bi-database" style="color:#fff"></i></div>
        <div class="qbe-panel-title" style="color:#fff">Question Bank</div>
        <div class="ms-auto">
          <span class="qbe-hero-chip" style="font-size:.65rem;padding:.18rem .6rem" id="bankCount">…</span>
        </div>
      </div>
      <div style="padding:.85rem .85rem .4rem;border-bottom:1px solid #f0f4f8;">
        <div class="qbe-bank-search-wrap">
          <i class="bi bi-search"></i>
          <input type="text" id="bSearch" class="qbe-bank-search" placeholder="Search UID or question text…" oninput="debouncedBankSearch()">
        </div>
        <div class="qbe-filter-row">
          <select id="bSubject" class="qbe-filter-sel" onchange="loadBankChapters();bankSearch()">
            <option value="">All Subjects</option>
          </select>
          <select id="bLevel" class="qbe-filter-sel" onchange="loadBankChapters();bankSearch()">
            <option value="">All Levels</option>
          </select>
        </div>
        <div class="qbe-filter-row">
          <select id="bChapter" class="qbe-filter-sel" onchange="bankSearch()">
            <option value="">All Chapters</option>
          </select>
          <select id="bDifficulty" class="qbe-filter-sel" onchange="bankSearch()">
            <option value="">All Difficulties</option>
          </select>
          <select id="bType" class="qbe-filter-sel" onchange="bankSearch()">
            <option value="">All Types</option>
            <option value="mcq">MCQ</option>
            <option value="true_false">T/F</option>
            <option value="essay">Essay</option>
            <option value="fill_blank">Fill</option>
          </select>
        </div>
      </div>
      <div id="bankList" style="flex:1;overflow-y:auto;padding:.75rem .85rem">
        <div class="qbe-empty-small"><i class="bi bi-search" style="font-size:1.8rem;display:block;margin-bottom:.5rem;opacity:.3"></i>Start typing or pick filters</div>
      </div>
      <div id="bankPager" class="qbe-bank-pager" style="display:none">
        <span class="qbe-pager-info" id="bankPagerInfo"></span>
        <div class="qbe-pager-btns">
          <button class="qbe-pager-btn" id="bankPrevBtn" onclick="bankPageChange(-1)">‹ Prev</button>
          <button class="qbe-pager-btn" id="bankNextBtn" onclick="bankPageChange(1)">Next ›</button>
        </div>
      </div>
    </div>
  </div>

  <!-- RIGHT: Selected Questions -->
  <div class="col-lg-4">
    <div class="qbe-panel" style="display:flex;flex-direction:column;">
      <div class="qbe-panel-head" style="background:linear-gradient(135deg,#1c1200,#431407)">
        <div class="qbe-panel-icon" style="background:rgba(255,255,255,.15)"><i class="bi bi-list-check" style="color:#fff"></i></div>
        <div class="qbe-panel-title" style="color:#fff">Exam Questions</div>
        <div class="ms-auto d-flex gap-2">
          <span class="qbe-hero-chip" style="font-size:.65rem;padding:.18rem .6rem" id="selQCount">0 Qs</span>
        </div>
      </div>
      <div id="selList" style="flex:1;overflow-y:auto;padding:.75rem .85rem">
        <div class="qbe-empty-small" id="selEmpty">
          <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3"></i>No questions yet — add from the bank
        </div>
      </div>
      <div class="qbe-totals-bar">
        <div class="qbe-total-chip">
          <div class="qbe-total-val" id="totQs">0</div>
          <div class="qbe-total-lbl">Questions</div>
        </div>
        <div class="qbe-total-chip">
          <div class="qbe-total-val" id="totMarks">0</div>
          <div class="qbe-total-lbl">Total Marks</div>
        </div>
        <div class="qbe-total-chip">
          <div class="qbe-total-val" id="totPass">0</div>
          <div class="qbe-total-lbl">Pass Mark</div>
        </div>
      </div>
    </div>
  </div>

</div><!-- /.row -->
</div><!-- /.container-fluid -->

<script>
/* ── DCM Alert ─────────────────────────────────────────────── */
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
  validation(){this._inject();Swal.fire({icon:'warning',title:'Required fields missing',html:'<div style="font-size:.85rem;color:#64748b">Please fill Exam Title, Subject and Level.</div>',customClass:{popup:'ds-pop',title:'ds-ttl',confirmButton:'ds-btn'},confirmButtonColor:'#d97706',confirmButtonText:'Fix it',showClass:{popup:'animate__animated animate__shakeX animate__faster'}});},
  loading(t='Saving…'){this._inject();Swal.fire({title:t,allowOutsideClick:false,customClass:{popup:'ds-pop',title:'ds-ttl'},didOpen:()=>Swal.showLoading()});},
};

const TYPE_CFG = {
  mcq:        {light:'#eff6ff',  text:'#1a4fc4', label:'MCQ'},
  true_false: {light:'#f0fdf4',  text:'#059669', label:'T/F'},
  essay:      {light:'#fffbeb',  text:'#d97706', label:'Essay'},
  matching:   {light:'#fff1f2',  text:'#dc2626', label:'Matching'},
  fill_blank: {light:'#f0f9ff',  text:'#0891b2', label:'Fill'},
};

let examId    = <?= $exam_id ?>;
let selQuestions = [];  // [{eq_id, question_id, sort_order, marks_override, q_uid, question_stem, question_type, marks, ...}]
let bankPage  = 1, bankTotal = 0, bankPerPage = 15;
let bankTimer = null;

/* ── Init ─────────────────────────────────────────────────── */
function _qbceInit() {
  loadTaxonomyOpts();
  if (examId) { loadExamData(); }
  bankSearch();
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', _qbceInit);
} else {
  _qbceInit();
}

function loadTaxonomyOpts() {
  [
    {entity:'subjects',          el:'fSubject',   v:'subject_id',    l:'subject_name'},
    {entity:'levels',            el:'fLevel',     v:'level_id',      l:'level_name'},
    {entity:'subjects',          el:'bSubject',   v:'subject_id',    l:'subject_name'},
    {entity:'levels',            el:'bLevel',     v:'level_id',      l:'level_name'},
    {entity:'difficulty_levels', el:'bDifficulty',v:'difficulty_id', l:'difficulty_name'},
  ].forEach(({entity,el,v,l}) => {
    fetch(`ajax/ajax_qb_taxonomy.php?entity=${entity}&action=list`)
      .then(r=>r.json()).then(res=>{
        if (res.status!=='success') return;
        const sel = document.getElementById(el);
        if (!sel) return;
        res.data.forEach(row => {
          const o = new Option(row[l], row[v]);
          sel.add(o);
        });
      });
  });
}

function loadChapters() {
  // Not needed for exam form, but available for bank
}

function loadBankChapters() {
  const subj  = document.getElementById('bSubject').value;
  const level = document.getElementById('bLevel').value;
  const sel   = document.getElementById('bChapter');
  sel.innerHTML = '<option value="">All Chapters</option>';
  if (!subj && !level) return;
  fetch('ajax/ajax_qb_taxonomy.php?entity=chapters&action=list')
    .then(r=>r.json()).then(res=>{
      if (res.status!=='success') return;
      res.data.filter(r=>(!subj||r.subject_id==subj)&&(!level||r.level_id==level))
        .forEach(r=>{
          const o = new Option((r.chapter_number?`Ch.${r.chapter_number} – `:'')+r.chapter_name, r.chapter_id);
          sel.add(o);
        });
    });
}

/* ── Load existing exam ──────────────────────────────────── */
function loadExamData() {
  fetch(`ajax/ajax_qb_exams.php?action=get&id=${examId}`)
    .then(r=>r.json()).then(res=>{
      if (res.status !== 'success') { dcmAlert.error('Could not load exam'); return; }
      const e = res.data;

      document.getElementById('fTitle').value          = e.exam_title || '';
      document.getElementById('fDesc').value           = e.description || '';
      document.getElementById('fInstructions').value   = e.instructions || '';
      document.getElementById('fDuration').value       = e.duration_minutes || 60;
      document.getElementById('fPassMarks').value      = e.passing_marks || 0;
      document.getElementById('fShuffle').checked      = !!parseInt(e.shuffle_questions);
      document.getElementById('fShuffleOpts').checked  = !!parseInt(e.shuffle_options);
      document.getElementById('fShowAnswers').checked  = !!parseInt(e.show_answers_after);

      // Subjects/levels may not be loaded yet — wait a tick
      setTimeout(() => {
        if (e.subject_id) document.getElementById('fSubject').value = e.subject_id;
        if (e.level_id)   document.getElementById('fLevel').value   = e.level_id;
      }, 600);

      if (e.exam_code) {
        document.getElementById('chipCode').style.display = 'inline-flex';
        document.getElementById('chipCodeVal').textContent = e.exam_code;
      }

      selQuestions = (e.questions || []).map(q => ({
        eq_id:          q.eq_id,
        question_id:    q.question_id,
        sort_order:     q.sort_order,
        marks_override: q.marks_override,
        q_uid:          q.q_uid,
        question_stem:  q.question_stem,
        question_type:  q.question_type,
        marks:          q.marks,
        difficulty_name:q.difficulty_name,
        subject_name:   q.subject_name,
      }));
      renderSelList();
      bankSearch();
    });
}

/* ── Save exam ───────────────────────────────────────────── */
function saveExam(status) {
  const title   = document.getElementById('fTitle').value.trim();
  const subject = document.getElementById('fSubject').value;
  const level   = document.getElementById('fLevel').value;
  if (!title) {
    document.getElementById('fTitle').classList.add('is-invalid');
    dcmAlert.validation(); return;
  }
  document.getElementById('fTitle').classList.remove('is-invalid');

  const payload = {
    action:             'save',
    exam_id:            examId || 0,
    exam_title:         title,
    subject_id:         parseInt(subject) || 0,
    level_id:           parseInt(level) || 0,
    description:        document.getElementById('fDesc').value,
    instructions:       document.getElementById('fInstructions').value,
    duration_minutes:   parseInt(document.getElementById('fDuration').value) || 60,
    passing_marks:      parseFloat(document.getElementById('fPassMarks').value) || 0,
    exam_type:          'manual',
    status:             status,
    shuffle_questions:  document.getElementById('fShuffle').checked ? 1 : 0,
    shuffle_options:    document.getElementById('fShuffleOpts').checked ? 1 : 0,
    show_answers_after: document.getElementById('fShowAnswers').checked ? 1 : 0,
  };

  dcmAlert.loading(status === 'published' ? 'Publishing…' : 'Saving…');
  fetch('ajax/ajax_qb_exams.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify(payload)
  }).then(r=>r.json()).then(res=>{
    Swal.close();
    if (res.status === 'success') {
      if (!examId) {
        examId = res.exam_id;
        document.getElementById('fExamId').value = examId;
        if (res.exam_code) {
          document.getElementById('chipCode').style.display = 'inline-flex';
          document.getElementById('chipCodeVal').textContent = res.exam_code;
        }
        history.replaceState(null,'','?view=qb_create_exam&exam_id='+examId);
      }
      dcmAlert.success(status==='published'?'Exam published!':'Saved!', res.message||'Exam saved successfully.');
      bankSearch(); // refresh bank (exclude list updates)
    } else {
      dcmAlert.error('Could not save', res.message);
    }
  }).catch(() => dcmAlert.error('Request failed', 'Unable to reach the server.'));
}

/* ── Bank search ─────────────────────────────────────────── */
function debouncedBankSearch() {
  clearTimeout(bankTimer);
  bankTimer = setTimeout(() => { bankPage = 1; bankSearch(); }, 350);
}

function bankSearch() {
  const params = new URLSearchParams({
    action:       'question_search',
    q:            document.getElementById('bSearch').value,
    subject_id:   document.getElementById('bSubject').value,
    level_id:     document.getElementById('bLevel').value,
    chapter_id:   document.getElementById('bChapter').value,
    difficulty_id:document.getElementById('bDifficulty').value,
    type:         document.getElementById('bType').value,
    exam_id:      examId || 0,
    page:         bankPage,
    per_page:     bankPerPage,
  });

  document.getElementById('bankList').innerHTML =
    [1,2,3,4,5].map(()=>`<div class="qbe-skel" style="height:56px;margin-bottom:.45rem"></div>`).join('');

  fetch('ajax/ajax_qb_exams.php?' + params)
    .then(r=>r.json()).then(res=>{
      if (res.status !== 'success') { renderBankError(); return; }
      bankTotal = res.total;
      document.getElementById('bankCount').textContent = `${bankTotal} results`;
      renderBankList(res.data);
      renderBankPager();
    }).catch(() => renderBankError());
}

function bankPageChange(dir) {
  const pages = Math.ceil(bankTotal / bankPerPage);
  bankPage = Math.max(1, Math.min(bankPage + dir, pages));
  bankSearch();
}

function renderBankList(rows) {
  const addedIds = new Set(selQuestions.map(q => q.question_id));
  const list = document.getElementById('bankList');

  if (!rows.length) {
    list.innerHTML = `<div class="qbe-empty-small"><i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3"></i>No questions found</div>`;
    return;
  }

  list.innerHTML = rows.map(q => {
    const tc     = TYPE_CFG[q.question_type] || {light:'#f1f5f9',text:'#475569',label:q.question_type};
    const isAdded= addedIds.has(+q.question_id);
    const stem   = stripHtml(q.question_stem||'');
    return `
    <div class="qbe-qrow${isAdded?' added':''}">
      <div style="display:flex;flex-direction:column;align-items:center;gap:.25rem;flex-shrink:0">
        <span class="qbe-qrow-uid">${q.q_uid}</span>
      </div>
      <div style="flex:1;min-width:0">
        <div class="qbe-qrow-stem">${stem||'<em style="color:#94a3b8">No text</em>'}</div>
        <div style="display:flex;gap:.25rem;margin-top:.25rem;flex-wrap:wrap">
          <span class="qbe-qrow-type" style="background:${tc.light};color:${tc.text}">${tc.label}</span>
          ${q.difficulty_name?`<span style="font-size:.63rem;font-weight:700;padding:.1rem .42rem;border-radius:100px;background:#fff7ed;color:#c2410c">${q.difficulty_name}</span>`:''}
        </div>
      </div>
      <div class="qbe-qrow-meta">
        <span class="qbe-qrow-marks"><i class="bi bi-star-fill" style="font-size:.55rem"></i>${q.marks}</span>
        <button class="qbe-add-btn" ${isAdded?'disabled title="Already added"':''} onclick="addQuestion(${q.question_id})">
          ${isAdded?'<i class="bi bi-check2" style="color:#059669"></i>':'<i class="bi bi-plus-lg"></i>'}
        </button>
      </div>
    </div>`;
  }).join('');
}

function renderBankPager() {
  const pages = Math.ceil(bankTotal / bankPerPage);
  const pager = document.getElementById('bankPager');
  pager.style.display = pages > 1 ? 'flex' : 'none';
  if (pages <= 1) return;
  document.getElementById('bankPagerInfo').textContent = `Page ${bankPage} of ${pages}`;
  document.getElementById('bankPrevBtn').disabled = bankPage <= 1;
  document.getElementById('bankNextBtn').disabled = bankPage >= pages;
}

function renderBankError() {
  document.getElementById('bankList').innerHTML = `<div class="qbe-empty-small" style="color:#dc2626"><i class="bi bi-exclamation-triangle" style="font-size:1.5rem;display:block;margin-bottom:.4rem"></i>Failed to load</div>`;
}

/* ── Add question ─────────────────────────────────────────── */
function addQuestion(question_id) {
  if (!examId) {
    // Must save first
    dcmAlert.error('Save exam first', 'Please save the exam settings before adding questions.');
    return;
  }
  fetch('ajax/ajax_qb_exams.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({action:'add_question', exam_id:examId, question_id})
  }).then(r=>r.json()).then(res=>{
    if (res.status === 'success') {
      // Reload full exam to get eq_id etc
      reloadExamQuestions();
    } else dcmAlert.error('Could not add', res.message);
  }).catch(() => dcmAlert.error('Request failed','Unable to reach the server.'));
}

function reloadExamQuestions() {
  fetch(`ajax/ajax_qb_exams.php?action=get&id=${examId}`)
    .then(r=>r.json()).then(res=>{
      if (res.status !== 'success') return;
      selQuestions = (res.data.questions || []).map(q => ({
        eq_id:          q.eq_id,
        question_id:    q.question_id,
        sort_order:     q.sort_order,
        marks_override: q.marks_override,
        q_uid:          q.q_uid,
        question_stem:  q.question_stem,
        question_type:  q.question_type,
        marks:          q.marks,
        difficulty_name:q.difficulty_name,
      }));
      renderSelList();
      bankSearch(); // refresh bank to update "added" states
    });
}

/* ── Remove question ──────────────────────────────────────── */
function removeQuestion(eq_id) {
  fetch('ajax/ajax_qb_exams.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({action:'remove_question', eq_id, exam_id:examId})
  }).then(r=>r.json()).then(res=>{
    if (res.status === 'success') {
      reloadExamQuestions();
    } else dcmAlert.error('Could not remove', res.message);
  }).catch(() => dcmAlert.error('Request failed','Unable to reach the server.'));
}

/* ── Move up/down ─────────────────────────────────────────── */
function moveQuestion(eq_id, dir) {
  const idx = selQuestions.findIndex(q => q.eq_id === eq_id);
  if (idx < 0) return;
  const target = idx + dir;
  if (target < 0 || target >= selQuestions.length) return;

  [selQuestions[idx], selQuestions[target]] = [selQuestions[target], selQuestions[idx]];
  selQuestions.forEach((q, i) => q.sort_order = i + 1);

  renderSelList();

  const items = selQuestions.map(q => ({eq_id: q.eq_id, sort_order: q.sort_order}));
  fetch('ajax/ajax_qb_exams.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({action:'reorder', items})
  });
}

/* ── Marks override ───────────────────────────────────────── */
function updateMarksOverride(eq_id, val) {
  const marks_override = val === '' ? '' : parseFloat(val);
  fetch('ajax/ajax_qb_exams.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({action:'update_marks_override', eq_id, exam_id:examId, marks_override})
  }).then(r=>r.json()).then(res=>{
    if (res.status === 'success') {
      const q = selQuestions.find(q => q.eq_id === eq_id);
      if (q) q.marks_override = marks_override === '' ? null : marks_override;
      updateTotals(res.total_marks);
    }
  });
}

/* ── Render selected list ─────────────────────────────────── */
function renderSelList() {
  const list  = document.getElementById('selList');
  const empty = document.getElementById('selEmpty');

  if (!selQuestions.length) {
    list.innerHTML = '';
    if (empty) empty.style.display = '';
    updateTotals(0);
    return;
  }
  if (empty) empty.style.display = 'none';

  const total_marks = selQuestions.reduce((s, q) => s + parseFloat(q.marks_override ?? q.marks ?? 0), 0);

  list.innerHTML = selQuestions.map((q, i) => {
    const tc    = TYPE_CFG[q.question_type] || {light:'#f1f5f9',text:'#475569',label:q.question_type};
    const stem  = stripHtml(q.question_stem||'');
    const effMk = q.marks_override != null ? parseFloat(q.marks_override) : parseFloat(q.marks||0);
    return `
    <div class="qbe-sel-q" id="selq_${q.eq_id}">
      <div class="qbe-sel-num">${i+1}</div>
      <div class="qbe-sel-info">
        <div class="qbe-sel-stem">${stem||'No text'}</div>
        <div class="qbe-sel-badges">
          <span style="font-family:monospace;font-size:.63rem;font-weight:800;background:#0f172a;color:#e2e8f0;padding:.1rem .4rem;border-radius:6px">${q.q_uid}</span>
          <span class="qbe-qrow-type" style="background:${tc.light};color:${tc.text}">${tc.label}</span>
          ${q.difficulty_name?`<span style="font-size:.63rem;font-weight:700;padding:.1rem .38rem;border-radius:100px;background:#fff7ed;color:#c2410c">${q.difficulty_name}</span>`:''}
        </div>
      </div>
      <div class="qbe-sel-acts">
        <input type="number" class="qbe-mk-input" value="${effMk}" placeholder="${q.marks}"
               onchange="updateMarksOverride(${q.eq_id},this.value)"
               title="Marks (default: ${q.marks})" min="0" step="0.5">
        <div class="qbe-ord-btns">
          <button class="qbe-ord-btn" onclick="moveQuestion(${q.eq_id},-1)" title="Move up" ${i===0?'disabled':''}><i class="bi bi-chevron-up"></i></button>
          <button class="qbe-ord-btn" onclick="moveQuestion(${q.eq_id},1)"  title="Move down" ${i===selQuestions.length-1?'disabled':''}><i class="bi bi-chevron-down"></i></button>
        </div>
        <button class="qbe-rm-btn" onclick="removeQuestion(${q.eq_id})" title="Remove"><i class="bi bi-x-lg"></i></button>
      </div>
    </div>`;
  }).join('');

  updateTotals(total_marks);

  document.getElementById('selQCount').textContent = selQuestions.length + ' Qs';
  document.getElementById('chipQVal').textContent  = selQuestions.length;
}

function updateTotals(tm) {
  const n = selQuestions.length;
  document.getElementById('totQs').textContent    = n;
  document.getElementById('totMarks').textContent  = parseFloat(tm||0).toFixed(1);
  document.getElementById('totPass').textContent   = parseFloat(document.getElementById('fPassMarks').value||0).toFixed(1);
  document.getElementById('chipMarkVal').textContent = parseFloat(tm||0).toFixed(0);
}

document.getElementById('fPassMarks').addEventListener('input', () => {
  document.getElementById('totPass').textContent = parseFloat(document.getElementById('fPassMarks').value||0).toFixed(1);
});

function stripHtml(html) {
  const tmp = document.createElement('div');
  tmp.innerHTML = html;
  return (tmp.textContent || tmp.innerText || '').trim();
}

Object.assign(window, {
  saveExam, debouncedBankSearch, bankPageChange, bankSearch,
  addQuestion, removeQuestion, moveQuestion, updateMarksOverride, loadBankChapters
});
</script>
