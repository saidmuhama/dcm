<?php
$edit_id    = (int)($_GET['id'] ?? 0);
$is_edit    = $edit_id > 0;
$page_title = $is_edit ? 'Edit Question' : 'Add New Question';
$hero_grad  = $is_edit
    ? 'linear-gradient(135deg,#1a0845 0%,#2d1065 45%,#4c1d95 100%)'
    : 'linear-gradient(135deg,#0a0f1e 0%,#0d1b3e 45%,#111827 100%)';
$hero_orb1  = $is_edit ? 'rgba(124,58,237,.5)' : 'rgba(26,79,196,.45)';
$hero_orb2  = $is_edit ? 'rgba(168,85,247,.38)' : 'rgba(109,40,217,.35)';
?>

<style>
/* ═══════════════════════════════════════════════════════════
   QB ADD / EDIT QUESTION  (qba-*)
═══════════════════════════════════════════════════════════ */
.qba-wrap { font-family:'Open Sans',sans-serif; }

/* ── Hero ── */
.qba-hero { position:relative; overflow:hidden; isolation:isolate; border-radius:20px; padding:1.8rem 2.2rem; margin-bottom:1.4rem; }
.qba-hero-grid { position:absolute; inset:0; z-index:0; background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px); background-size:44px 44px; }
.qba-hero-inner { position:relative; z-index:1; }
.qba-hero-badge { display:inline-flex; align-items:center; gap:.4rem; background:rgba(255,255,255,.09); border:1px solid rgba(255,255,255,.15); border-radius:100px; padding:.26rem .8rem; font-size:.68rem; font-weight:700; color:rgba(255,255,255,.7); letter-spacing:.06em; text-transform:uppercase; margin-bottom:.6rem; backdrop-filter:blur(6px); }
.qba-hero-title { font-size:1.55rem; font-weight:900; color:#fff; font-family:'SUSE',sans-serif; letter-spacing:-.04em; line-height:1.15; margin-bottom:.25rem; }
.qba-hero-title em { font-style:normal; background:linear-gradient(90deg,#60a5fa,#c084fc); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent; }
.qba-hero-sub { font-size:.79rem; color:rgba(255,255,255,.42); max-width:480px; line-height:1.6; }
.qba-hero-actions { display:flex; gap:.7rem; margin-top:1.1rem; flex-wrap:wrap; }
.qba-back-btn { display:inline-flex; align-items:center; gap:.4rem; background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.18); color:rgba(255,255,255,.8); border-radius:10px; padding:.5rem 1rem; font-size:.79rem; font-weight:700; text-decoration:none; transition:all .17s; }
.qba-back-btn:hover { background:rgba(255,255,255,.17); color:#fff; }
.qba-submit-btn { display:inline-flex; align-items:center; gap:.45rem; background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff; border:none; border-radius:10px; padding:.52rem 1.25rem; font-size:.8rem; font-weight:700; cursor:pointer; font-family:inherit; box-shadow:0 4px 14px rgba(26,79,196,.4); transition:filter .17s,transform .12s; }
.qba-submit-btn:hover { filter:brightness(1.1); transform:translateY(-1px); color:#fff; }
.qba-draft-btn { display:inline-flex; align-items:center; gap:.45rem; background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2); color:#fff; border-radius:10px; padding:.5rem 1.1rem; font-size:.79rem; font-weight:700; cursor:pointer; font-family:inherit; transition:all .17s; }
.qba-draft-btn:hover { background:rgba(255,255,255,.2); }

/* ── Hero info strip ── */
.qba-info-strip { display:flex; gap:.65rem; flex-wrap:wrap; margin-top:1rem; }
.qba-info-chip { display:inline-flex; align-items:center; gap:.4rem; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.13); border-radius:100px; padding:.28rem .75rem; font-size:.7rem; color:rgba(255,255,255,.65); font-weight:600; backdrop-filter:blur(6px); }
.qba-info-chip i { opacity:.7; font-size:.7rem; }

/* ── Panel (form card) ── */
.qba-panel { background:#fff; border-radius:16px; border:1px solid #f0f4f8; box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 16px rgba(0,0,0,.04); margin-bottom:1.1rem; overflow:hidden; }
.qba-panel-hdr { display:flex; align-items:center; justify-content:space-between; padding:.7rem 1.15rem; border-bottom:1px solid #f0f4f8; }
.qba-panel-title { display:flex; align-items:center; gap:.55rem; font-size:.72rem; font-weight:800; color:#475569; text-transform:uppercase; letter-spacing:.07em; }
.qba-panel-title i { font-size:.8rem; color:#94a3b8; }
.qba-panel-body { padding:1.1rem 1.15rem; }

/* ── Form controls ── */
.qba-label { font-size:.73rem; font-weight:700; color:#475569; margin-bottom:.28rem; display:block; }
.qba-req { color:#e11d48; }
.qba-ctrl { width:100%; padding:.5rem .8rem; border-radius:10px; border:1.5px solid #e2e8f0; font-size:.81rem; font-family:inherit; outline:none; background:#f8fafc; color:#1e293b; transition:border-color .18s,box-shadow .18s; }
.qba-ctrl:focus { border-color:#1a4fc4; box-shadow:0 0 0 3px rgba(26,79,196,.1); background:#fff; }
.qba-ctrl.is-err { border-color:#e11d48; box-shadow:0 0 0 3px rgba(225,29,72,.08); }
.qba-ctrl-sm { padding:.42rem .75rem; font-size:.79rem; }
.qba-help { font-size:.69rem; color:#94a3b8; margin-top:.25rem; }

/* ── Stem textarea ── */
.qba-stem { width:100%; padding:.7rem .9rem; border-radius:12px; border:1.5px solid #e2e8f0; font-size:.88rem; font-family:'Open Sans',inherit; outline:none; background:#f8fafc; color:#0f172a; line-height:1.65; resize:vertical; min-height:130px; transition:border-color .18s,box-shadow .18s; }
.qba-stem:focus { border-color:#1a4fc4; box-shadow:0 0 0 3px rgba(26,79,196,.1); background:#fff; }

/* ── MCQ option rows ── */
.qba-mcq-opt { border-radius:12px; border:1.5px solid #e2e8f0; margin-bottom:.55rem; transition:all .18s; overflow:hidden; cursor:pointer; }
.qba-mcq-opt:hover { border-color:#bfdbfe; background:#f8fafc; }
.qba-mcq-opt.selected { border-color:#1a4fc4; background:#eff6ff; box-shadow:0 0 0 3px rgba(26,79,196,.09); }
.qba-opt-label { display:flex; align-items:center; gap:.7rem; padding:.55rem .75rem; margin:0; cursor:pointer; }
.qba-opt-radio { display:none; }
.qba-opt-badge { width:30px; height:30px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:.76rem; font-weight:900; background:#f1f5f9; color:#64748b; flex-shrink:0; transition:all .17s; font-family:'SUSE',sans-serif; }
.qba-mcq-opt.selected .qba-opt-badge { background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff; }
.qba-opt-text { flex:1; border:none; background:transparent; font-size:.83rem; font-family:inherit; outline:none; color:#1e293b; padding:0; min-width:0; }
.qba-opt-check { width:22px; height:22px; border-radius:50%; border:1.5px solid #e2e8f0; display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:all .17s; }
.qba-mcq-opt.selected .qba-opt-check { background:#1a4fc4; border-color:#1a4fc4; }
.qba-mcq-opt.selected .qba-opt-check i { color:#fff; font-size:.72rem; }
.qba-opt-check i { display:none; }
.qba-mcq-opt.selected .qba-opt-check i { display:block; }

/* ── T/F options ── */
.qba-tf-row { display:flex; gap:.75rem; }
.qba-tf-btn { flex:1; padding:.75rem; border-radius:14px; border:1.5px solid #e2e8f0; background:#f8fafc; display:flex; align-items:center; justify-content:center; gap:.6rem; cursor:pointer; font-size:.86rem; font-weight:700; color:#64748b; transition:all .18s; }
.qba-tf-btn:hover { border-color:#bfdbfe; background:#f0f9ff; }
.qba-tf-btn.selected-true { border-color:#059669; background:#f0fdf4; color:#059669; }
.qba-tf-btn.selected-false { border-color:#dc2626; background:#fff1f2; color:#dc2626; }

/* ── Matching pairs ── */
.qba-match-row { display:flex; align-items:center; gap:.55rem; margin-bottom:.6rem; }
.qba-match-num { width:24px; height:24px; border-radius:7px; background:#f1f5f9; color:#94a3b8; font-size:.7rem; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-family:'SUSE',sans-serif; }
.qba-match-arrow { color:#cbd5e1; font-size:.9rem; flex-shrink:0; }
.qba-match-del { width:28px; height:28px; border-radius:8px; border:1.5px solid #f0f4f8; background:#fff; color:#cbd5e1; display:flex; align-items:center; justify-content:center; cursor:pointer; flex-shrink:0; transition:all .15s; font-size:.78rem; }
.qba-match-del:hover { border-color:#fecaca; color:#dc2626; background:#fff1f2; }
.qba-add-pair-btn { display:inline-flex; align-items:center; gap:.4rem; padding:.45rem .9rem; border-radius:10px; border:1.5px dashed #bfdbfe; background:#f0f9ff; color:#1a4fc4; font-size:.77rem; font-weight:700; cursor:pointer; font-family:inherit; transition:all .15s; }
.qba-add-pair-btn:hover { background:#dbeafe; }

/* ── Section divider ── */
.qba-section-sep { border:none; border-top:1px solid #f0f4f8; margin:1rem 0; }

/* ── Submit strip ── */
.qba-submit-strip { background:#fff; border-radius:16px; border:1px solid #f0f4f8; padding:.9rem 1.15rem; display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; box-shadow:0 1px 3px rgba(0,0,0,.04); }
.qba-strip-info { font-size:.76rem; color:#94a3b8; }
.qba-strip-btns { display:flex; gap:.65rem; flex-wrap:wrap; }
.qba-strip-draft { display:inline-flex; align-items:center; gap:.4rem; padding:.52rem 1.1rem; border-radius:10px; border:1.5px solid #e2e8f0; background:#f8fafc; color:#475569; font-size:.79rem; font-weight:700; cursor:pointer; font-family:inherit; transition:all .15s; }
.qba-strip-draft:hover { border-color:#1a4fc4; color:#1a4fc4; background:#eff6ff; }
.qba-strip-submit { display:inline-flex; align-items:center; gap:.45rem; background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff; border:none; border-radius:10px; padding:.52rem 1.3rem; font-size:.8rem; font-weight:700; cursor:pointer; font-family:inherit; box-shadow:0 4px 14px rgba(26,79,196,.35); transition:filter .17s; }
.qba-strip-submit:hover { filter:brightness(1.1); }
</style>

<div class="container-fluid px-3 py-3 qba-wrap">

<!-- ── Hero ──────────────────────────────────────────────── -->
<div class="qba-hero" style="background:<?= $hero_grad ?>">
  <div class="qba-hero-grid"></div>
  <div style="position:absolute;right:2rem;top:50%;transform:translateY(-50%);width:200px;height:200px;border-radius:50%;background:conic-gradient(from 0deg,<?= $hero_orb1 ?>,<?= $hero_orb2 ?>,<?= $hero_orb1 ?>);filter:blur(40px);opacity:.5;animation:db-orb-spin 16s linear infinite;z-index:0"></div>
  <div class="qba-hero-inner">
    <div class="qba-hero-badge">
      <i class="bi bi-<?= $is_edit ? 'pencil-square' : 'plus-circle-fill' ?>"></i>
      Question Bank <?= $is_edit ? 'Editor' : 'Authoring' ?>
    </div>
    <div class="qba-hero-title"><em><?= $page_title ?></em></div>
    <div class="qba-hero-sub"><?= $is_edit ? 'Modify the question details below. All changes are saved immediately on submit.' : 'Author a new question. Fill taxonomy, select type, write the stem and options, then save.' ?></div>
    <div class="qba-info-strip">
      <span class="qba-info-chip"><i class="bi bi-tag-fill"></i>UID auto-generated</span>
      <span class="qba-info-chip"><i class="bi bi-shield-check"></i>Required fields marked <span style="color:#f87171;margin-left:.2rem">*</span></span>
      <?php if ($is_edit): ?><span class="qba-info-chip" id="editIdChip"><i class="bi bi-pencil"></i>ID #<?= $edit_id ?></span><?php endif; ?>
    </div>
    <div class="qba-hero-actions">
      <a href="?view=qb_all_questions" class="qba-back-btn"><i class="bi bi-arrow-left"></i>All Questions</a>
      <button class="qba-draft-btn" onclick="submitQuestion('draft')"><i class="bi bi-floppy"></i>Save Draft</button>
      <button class="qba-submit-btn" onclick="submitQuestion('review')"><i class="bi bi-send"></i>Submit for Review</button>
    </div>
  </div>
</div>

<form id="qbAddForm" novalidate>
  <input type="hidden" id="qbEditId" value="<?= $edit_id ?>">
  <div class="row g-3">

    <!-- ── LEFT: Taxonomy + Metadata ─────────────────────── -->
    <div class="col-12 col-lg-4">

      <!-- Taxonomy -->
      <div class="qba-panel">
        <div class="qba-panel-hdr">
          <div class="qba-panel-title"><i class="bi bi-diagram-3"></i>Taxonomy</div>
          <span style="font-size:.65rem;color:#94a3b8;font-weight:600">Required</span>
        </div>
        <div class="qba-panel-body">
          <div class="mb-3">
            <label class="qba-label">Subject <span class="qba-req">*</span></label>
            <select id="f_subject" class="qba-ctrl" onchange="cascadeLevel(); cascadeChapter()">
              <option value="">— Select Subject —</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="qba-label">Level <span class="qba-req">*</span></label>
            <select id="f_level" class="qba-ctrl" onchange="cascadeChapter()">
              <option value="">— Select Level —</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="qba-label">Chapter <span class="qba-req">*</span></label>
            <select id="f_chapter" class="qba-ctrl" onchange="cascadeSubtopic()">
              <option value="">— Select Chapter —</option>
            </select>
          </div>
          <div>
            <label class="qba-label">Subtopic <span class="qba-req">*</span></label>
            <select id="f_subtopic" class="qba-ctrl">
              <option value="">— Select Subtopic —</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Metadata -->
      <div class="qba-panel">
        <div class="qba-panel-hdr">
          <div class="qba-panel-title"><i class="bi bi-sliders"></i>Metadata</div>
        </div>
        <div class="qba-panel-body">
          <div class="row gx-2 mb-3">
            <div class="col-6">
              <label class="qba-label">Type <span class="qba-req">*</span></label>
              <select id="f_question_type" class="qba-ctrl qba-ctrl-sm" onchange="onTypeChange()">
                <option value="mcq">MCQ</option>
                <option value="true_false">True / False</option>
                <option value="essay">Essay</option>
                <option value="fill_blank">Fill Blank</option>
                <option value="matching">Matching</option>
              </select>
            </div>
            <div class="col-6">
              <label class="qba-label">Section</label>
              <select id="f_section" class="qba-ctrl qba-ctrl-sm">
                <option value="">None</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="qba-label">Difficulty <span class="qba-req">*</span></label>
            <select id="f_difficulty" class="qba-ctrl">
              <option value="">— Select —</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="qba-label">Bloom's Level</label>
            <select id="f_bloom" class="qba-ctrl">
              <option value="">— Select —</option>
            </select>
          </div>
          <hr class="qba-section-sep">
          <div class="row gx-2 mb-3">
            <div class="col-6">
              <label class="qba-label">Marks</label>
              <input type="number" id="f_marks" class="qba-ctrl qba-ctrl-sm" value="1" min="0.5" step="0.5">
            </div>
            <div class="col-6">
              <label class="qba-label">Time (sec)</label>
              <input type="number" id="f_est_time" class="qba-ctrl qba-ctrl-sm" value="60" min="10" step="10">
            </div>
          </div>
          <div class="row gx-2">
            <div class="col-6">
              <label class="qba-label">Exam Year</label>
              <input type="number" id="f_year" class="qba-ctrl qba-ctrl-sm" placeholder="2024" min="1990" max="2099">
            </div>
            <div class="col-6">
              <label class="qba-label">Question No.</label>
              <input type="text" id="f_question_number" class="qba-ctrl qba-ctrl-sm" placeholder="e.g. Q3(b)">
            </div>
          </div>
        </div>
      </div>

    </div><!-- /col-lg-4 -->

    <!-- ── RIGHT: Stem + Options + Hints ─────────────────── -->
    <div class="col-12 col-lg-8">

      <!-- Stem -->
      <div class="qba-panel">
        <div class="qba-panel-hdr">
          <div class="qba-panel-title"><i class="bi bi-chat-square-text"></i>Question Stem</div>
          <span style="font-size:.65rem;color:#e11d48;font-weight:700">Required</span>
        </div>
        <div class="qba-panel-body">
          <textarea id="f_question_stem" class="qba-stem" placeholder="Type the question stem here — supports plain text and basic HTML…"></textarea>
        </div>
      </div>

      <!-- MCQ Options -->
      <div class="qba-panel" id="mcqBlock">
        <div class="qba-panel-hdr">
          <div class="qba-panel-title"><i class="bi bi-list-ul"></i>Answer Options</div>
          <span style="font-size:.65rem;color:#94a3b8">Click the row to mark correct</span>
        </div>
        <div class="qba-panel-body">
          <?php foreach (['A','B','C','D'] as $lbl): ?>
          <div class="qba-mcq-opt" id="optRow_<?= $lbl ?>" onclick="selectOpt('<?= $lbl ?>')">
            <label class="qba-opt-label">
              <input type="radio" name="correct_answer_radio" value="<?= $lbl ?>" id="radio<?= $lbl ?>" class="qba-opt-radio">
              <div class="qba-opt-badge"><?= $lbl ?></div>
              <input type="text" id="opt<?= $lbl ?>" class="qba-opt-text" placeholder="Option <?= $lbl ?> …" onclick="event.stopPropagation()">
              <div class="qba-opt-check"><i class="bi bi-check"></i></div>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- True/False -->
      <div class="qba-panel d-none" id="tfBlock">
        <div class="qba-panel-hdr">
          <div class="qba-panel-title"><i class="bi bi-toggle-on"></i>Correct Answer</div>
        </div>
        <div class="qba-panel-body">
          <div class="qba-tf-row">
            <button type="button" class="qba-tf-btn" id="tfBtnTrue"  onclick="selectTF('True')">
              <i class="bi bi-check-circle-fill" style="font-size:1.1rem"></i> True
            </button>
            <button type="button" class="qba-tf-btn" id="tfBtnFalse" onclick="selectTF('False')">
              <i class="bi bi-x-circle-fill" style="font-size:1.1rem"></i> False
            </button>
          </div>
          <input type="hidden" id="tf_value" name="tf_answer">
        </div>
      </div>

      <!-- Fill Blank -->
      <div class="qba-panel d-none" id="fillBlock">
        <div class="qba-panel-hdr">
          <div class="qba-panel-title"><i class="bi bi-input-cursor-text"></i>Expected Answer</div>
        </div>
        <div class="qba-panel-body">
          <input type="text" id="f_fill_answer" class="qba-ctrl" placeholder="The expected answer text…">
        </div>
      </div>

      <!-- Essay -->
      <div class="qba-panel d-none" id="essayBlock">
        <div class="qba-panel-hdr">
          <div class="qba-panel-title"><i class="bi bi-file-text"></i>Model Answer <span style="font-weight:500;color:#94a3b8;font-size:.7rem;text-transform:none">(optional)</span></div>
        </div>
        <div class="qba-panel-body">
          <textarea id="f_essay_answer" class="qba-ctrl" style="height:100px;resize:vertical" placeholder="Reference answer for graders — not shown to students…"></textarea>
          <div class="qba-help">This is for grader reference only and is never shown during the exam.</div>
        </div>
      </div>

      <!-- Matching -->
      <div class="qba-panel d-none" id="matchingBlock">
        <div class="qba-panel-hdr">
          <div class="qba-panel-title"><i class="bi bi-arrow-left-right"></i>Matching Pairs</div>
          <button type="button" class="qba-add-pair-btn" onclick="addMatchingPair()"><i class="bi bi-plus-lg"></i>Add Pair</button>
        </div>
        <div class="qba-panel-body">
          <div style="display:grid;grid-template-columns:1fr auto 1fr auto auto;gap:.5rem;align-items:center;margin-bottom:.5rem">
            <span style="font-size:.67rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em">Premise (Left)</span>
            <span></span>
            <span style="font-size:.67rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em">Response (Right)</span>
            <span></span><span></span>
          </div>
          <div id="matchingPairsContainer"></div>
        </div>
      </div>

      <!-- Solution + Hints -->
      <div class="qba-panel">
        <div class="qba-panel-hdr">
          <div class="qba-panel-title"><i class="bi bi-lightbulb"></i>Solution & Hints</div>
        </div>
        <div class="qba-panel-body">
          <div class="mb-3">
            <label class="qba-label">Solution Explanation</label>
            <textarea id="f_solution" class="qba-ctrl" style="height:90px;resize:vertical" placeholder="Step-by-step explanation of the correct answer…"></textarea>
          </div>
          <div>
            <label class="qba-label">Swahili Hint <span style="color:#94a3b8;font-weight:500">(optional)</span></label>
            <textarea id="f_swahili_hint" class="qba-ctrl" style="height:70px;resize:vertical" placeholder="Kidokezo kwa Kiswahili…"></textarea>
          </div>
        </div>
      </div>

      <!-- Submit strip -->
      <div class="qba-submit-strip">
        <div class="qba-strip-info"><i class="bi bi-info-circle me-1"></i>Question UID is auto-generated from taxonomy selection.</div>
        <div class="qba-strip-btns">
          <a href="?view=qb_all_questions" style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;border-radius:10px;border:1.5px solid #e2e8f0;background:#f8fafc;color:#475569;font-size:.79rem;font-weight:700;text-decoration:none;transition:all .15s">Cancel</a>
          <button type="button" class="qba-strip-draft" onclick="submitQuestion('draft')"><i class="bi bi-floppy"></i>Save Draft</button>
          <button type="button" class="qba-strip-submit" onclick="submitQuestion('review')"><i class="bi bi-send"></i>Submit for Review</button>
        </div>
      </div>

    </div><!-- /col-lg-8 -->
  </div><!-- /row -->
</form>
</div>

<script>
const QB_EDIT_ID = <?= $edit_id ?>;

/* ── DCM Alerts ──────────────────────────────────────────── */
const dcmAlert = {
  _css:`.ds-pop{border-radius:20px!important;font-family:'Open Sans',sans-serif!important;padding:1.6rem!important}.ds-ttl{font-size:1.1rem!important;font-weight:800!important;color:#0f172a!important;margin-top:.3rem!important}.ds-btn{border-radius:11px!important;font-weight:700!important;font-size:.82rem!important;padding:.55rem 1.4rem!important}.ds-can{border-radius:11px!important;font-weight:700!important;font-size:.82rem!important;padding:.55rem 1.4rem!important;background:#f1f5f9!important;color:#475569!important;border:1.5px solid #e2e8f0!important}.ds-ico{border:none!important;margin-bottom:.4rem!important}.ds-tst{border-radius:14px!important;font-family:'Open Sans',sans-serif!important;box-shadow:0 8px 32px rgba(0,0,0,.14)!important;padding:.75rem 1.1rem!important;border-left:4px solid}.dst-ok{border-color:#059669!important}.dst-er{border-color:#dc2626!important}.dst-wn{border-color:#d97706!important}`,
  _done:false,
  _inject(){if(!this._done){const s=document.createElement('style');s.textContent=this._css;document.head.appendChild(s);this._done=true;}},
  toast(icon,title,text=''){this._inject();const cls={success:'dst-ok',error:'dst-er',warning:'dst-wn'}[icon]||'';Swal.fire({toast:true,position:'top-end',showConfirmButton:false,timer:3400,timerProgressBar:true,icon,title,text,customClass:{popup:`ds-tst ${cls}`}});},
  success(t,x=''){this.toast('success',t,x);},
  error(t,x=''){this._inject();Swal.fire({icon:'error',title:t,text:x||'Something went wrong.',customClass:{popup:'ds-pop',title:'ds-ttl',confirmButton:'ds-btn'},confirmButtonColor:'#dc2626',confirmButtonText:'Got it'});},
  validation(){this._inject();Swal.fire({icon:'warning',title:'Required fields missing',html:'<div style="font-size:.85rem;color:#64748b" id="missFields"></div>',customClass:{popup:'ds-pop',title:'ds-ttl',icon:'ds-ico',confirmButton:'ds-btn'},confirmButtonColor:'#d97706',confirmButtonText:'Fix it',showClass:{popup:'animate__animated animate__shakeX animate__faster'}});},
  loading(t='Saving…'){this._inject();Swal.fire({title:t,allowOutsideClick:false,customClass:{popup:'ds-pop',title:'ds-ttl'},didOpen:()=>Swal.showLoading()});},
};

let allChapters = [], allSubtopics = [];

document.addEventListener('DOMContentLoaded', () => {
  loadDropdowns();
  if (QB_EDIT_ID) loadExistingQuestion();
});

/* ── Dropdowns ───────────────────────────────────────────── */
function loadDropdowns() {
  [
    {entity:'subjects',          el:'f_subject',    v:'subject_id',    l:'subject_name'},
    {entity:'levels',            el:'f_level',      v:'level_id',      l:'level_name'},
    {entity:'difficulty_levels', el:'f_difficulty', v:'difficulty_id', l:'difficulty_name'},
    {entity:'bloom_levels',      el:'f_bloom',      v:'bloom_id',      l:'bloom_name'},
    {entity:'sections',          el:'f_section',    v:'section_id',    l:'section_name'},
  ].forEach(t => {
    fetch(`ajax/ajax_qb_taxonomy.php?entity=${t.entity}&action=list`)
      .then(r=>r.json()).then(res=>{
        if (res.status!=='success') return;
        const sel = document.getElementById(t.el);
        res.data.forEach(row => {
          const o = document.createElement('option');
          o.value = row[t.v]; o.textContent = row[t.l];
          sel.appendChild(o);
        });
      });
  });
  fetch('ajax/ajax_qb_taxonomy.php?entity=chapters&action=list').then(r=>r.json()).then(res=>{ if(res.status==='success') allChapters=res.data; });
  fetch('ajax/ajax_qb_taxonomy.php?entity=subtopics&action=list').then(r=>r.json()).then(res=>{ if(res.status==='success') allSubtopics=res.data; });
}

function cascadeLevel() {}

function cascadeChapter() {
  const subj  = document.getElementById('f_subject').value;
  const level = document.getElementById('f_level').value;
  const sel   = document.getElementById('f_chapter');
  sel.innerHTML = '<option value="">— Select Chapter —</option>';
  document.getElementById('f_subtopic').innerHTML = '<option value="">— Select Subtopic —</option>';
  if (!subj && !level) return;
  allChapters.filter(c=>(!subj||c.subject_id==subj)&&(!level||c.level_id==level)).forEach(c=>{
    const o=document.createElement('option');
    o.value=c.chapter_id;
    o.textContent=(c.chapter_number?`Ch.${c.chapter_number} – `:'')+c.chapter_name;
    sel.appendChild(o);
  });
}

function cascadeSubtopic() {
  const chapter_id = document.getElementById('f_chapter').value;
  const sel = document.getElementById('f_subtopic');
  sel.innerHTML = '<option value="">— Select Subtopic —</option>';
  if (!chapter_id) return;
  allSubtopics.filter(st=>st.chapter_id==chapter_id).forEach(st=>{
    const o=document.createElement('option');
    o.value=st.subtopic_id; o.textContent=st.subtopic_name;
    sel.appendChild(o);
  });
}

/* ── Type switch ─────────────────────────────────────────── */
function onTypeChange() {
  const type = document.getElementById('f_question_type').value;
  document.getElementById('mcqBlock').classList.toggle('d-none', type!=='mcq');
  document.getElementById('tfBlock').classList.toggle('d-none',  type!=='true_false');
  document.getElementById('fillBlock').classList.toggle('d-none', type!=='fill_blank');
  document.getElementById('essayBlock').classList.toggle('d-none', type!=='essay');
  document.getElementById('matchingBlock').classList.toggle('d-none', type!=='matching');
  if (type==='matching' && document.getElementById('matchingPairsContainer').children.length===0) {
    addMatchingPair(); addMatchingPair(); addMatchingPair();
  }
}

/* ── MCQ option select ───────────────────────────────────── */
function selectOpt(lbl) {
  document.querySelectorAll('.qba-mcq-opt').forEach(el=>el.classList.remove('selected'));
  const row = document.getElementById(`optRow_${lbl}`);
  row.classList.add('selected');
  document.getElementById(`radio${lbl}`).checked = true;
}

/* ── T/F select ──────────────────────────────────────────── */
function selectTF(val) {
  document.getElementById('tf_value').value = val;
  document.getElementById('tfBtnTrue').className  = 'qba-tf-btn' + (val==='True'  ? ' selected-true'  : '');
  document.getElementById('tfBtnFalse').className = 'qba-tf-btn' + (val==='False' ? ' selected-false' : '');
}

/* ── Matching pairs ──────────────────────────────────────── */
let matchPairCount = 0;
function addMatchingPair() {
  matchPairCount++;
  const n = matchPairCount;
  const row = document.createElement('div');
  row.className = 'qba-match-row match-pair-row';
  row.innerHTML = `
    <div class="qba-match-num">${n}</div>
    <input type="text" class="qba-ctrl qba-ctrl-sm match-left" style="flex:1" placeholder="Premise ${n}…">
    <span class="qba-match-arrow"><i class="bi bi-arrow-left-right"></i></span>
    <input type="text" class="qba-ctrl qba-ctrl-sm match-right" style="flex:1" placeholder="Response ${n}…">
    <button type="button" class="qba-match-del" onclick="removeMatchPair(this)"><i class="bi bi-x"></i></button>`;
  document.getElementById('matchingPairsContainer').appendChild(row);
}

function removeMatchPair(btn) {
  const c = document.getElementById('matchingPairsContainer');
  if (c.children.length > 2) btn.closest('.match-pair-row').remove();
}

function getMatchingPairs() {
  const pairs = [];
  document.querySelectorAll('.match-pair-row').forEach(row=>{
    const left  = row.querySelector('.match-left').value.trim();
    const right = row.querySelector('.match-right').value.trim();
    if (left||right) pairs.push({left,right});
  });
  return pairs;
}

function setMatchingPairs(pairs) {
  document.getElementById('matchingPairsContainer').innerHTML='';
  matchPairCount=0;
  pairs.forEach(p=>{
    addMatchingPair();
    const rows=document.querySelectorAll('.match-pair-row');
    const last=rows[rows.length-1];
    last.querySelector('.match-left').value=p.left||'';
    last.querySelector('.match-right').value=p.right||'';
  });
}

/* ── Collect answer ──────────────────────────────────────── */
function getCorrectAnswer() {
  const type = document.getElementById('f_question_type').value;
  if (type==='mcq')        { const r=document.querySelector('input[name="correct_answer_radio"]:checked'); return r?r.value:''; }
  if (type==='true_false') { return document.getElementById('tf_value').value; }
  if (type==='fill_blank') { return document.getElementById('f_fill_answer').value.trim(); }
  if (type==='essay')      { return document.getElementById('f_essay_answer').value.trim(); }
  return '';
}

function getMCQOptions() {
  return ['A','B','C','D'].map(l=>document.getElementById(`opt${l}`).value.trim());
}

/* ── Submit ──────────────────────────────────────────────── */
function submitQuestion(submitStatus) {
  const subject_id    = document.getElementById('f_subject').value;
  const level_id      = document.getElementById('f_level').value;
  const chapter_id    = document.getElementById('f_chapter').value;
  const subtopic_id   = document.getElementById('f_subtopic').value;
  const difficulty_id = document.getElementById('f_difficulty').value;
  const question_stem = document.getElementById('f_question_stem').value.trim();
  const question_type = document.getElementById('f_question_type').value;
  const correct_answer = getCorrectAnswer();

  const missing = [];
  if (!subject_id)    missing.push('Subject');
  if (!level_id)      missing.push('Level');
  if (!chapter_id)    missing.push('Chapter');
  if (!subtopic_id)   missing.push('Subtopic');
  if (!difficulty_id) missing.push('Difficulty');
  if (!question_stem) missing.push('Question Stem');
  if (question_type==='mcq') {
    if (getMCQOptions().filter(v=>v).length<2) missing.push('At least 2 MCQ options');
    if (!correct_answer) missing.push('Correct answer (MCQ)');
  }
  if (question_type==='true_false'&&!correct_answer) missing.push('True / False answer');
  if (question_type==='fill_blank'&&!correct_answer) missing.push('Expected answer');
  if (question_type==='matching'&&getMatchingPairs().filter(p=>p.left&&p.right).length<2) missing.push('At least 2 matching pairs');

  if (missing.length) {
    dcmAlert._inject();
    Swal.fire({
      icon:'warning',title:'Required fields missing',
      html:`<div style="font-size:.84rem;color:#64748b;line-height:1.8">${missing.map(m=>`<span style="display:inline-flex;align-items:center;gap:.3rem;background:#fff1f2;color:#e11d48;border-radius:8px;padding:.15rem .55rem;margin:.2rem;font-weight:700;font-size:.77rem"><i class="bi bi-x-circle-fill" style="font-size:.7rem"></i>${m}</span>`).join('')}</div>`,
      customClass:{popup:'ds-pop',title:'ds-ttl',confirmButton:'ds-btn',icon:'ds-ico'},
      confirmButtonColor:'#d97706',confirmButtonText:'Fix it',
      showClass:{popup:'animate__animated animate__shakeX animate__faster'}
    });
    return;
  }

  dcmAlert.loading('Saving question…');

  const fd = new FormData();
  fd.append('subject_id', subject_id); fd.append('level_id', level_id);
  fd.append('chapter_id', chapter_id); fd.append('subtopic_id', subtopic_id);
  fd.append('difficulty_id', difficulty_id);
  fd.append('bloom_id',      document.getElementById('f_bloom').value);
  fd.append('section_id',    document.getElementById('f_section').value);
  fd.append('question_type', question_type);
  fd.append('question_stem', question_stem);
  fd.append('correct_answer', correct_answer);
  fd.append('solution_explanation', document.getElementById('f_solution').value.trim());
  fd.append('swahili_hint',  document.getElementById('f_swahili_hint').value.trim());
  fd.append('marks',         document.getElementById('f_marks').value);
  fd.append('estimated_time_seconds', document.getElementById('f_est_time').value);
  fd.append('year_year',     document.getElementById('f_year').value);
  fd.append('question_number', document.getElementById('f_question_number').value.trim());
  fd.append('save_status',   submitStatus);
  if (question_type==='mcq') getMCQOptions().forEach(opt=>fd.append('options[]',opt));
  if (question_type==='matching') fd.append('matching_pairs', JSON.stringify(getMatchingPairs().filter(p=>p.left&&p.right)));
  if (QB_EDIT_ID) fd.append('question_id', QB_EDIT_ID);

  const url = QB_EDIT_ID ? 'ajax/ajax_qb_update_question.php' : 'ajax/ajax_qb_save_question.php';

  fetch(url, {method:'POST',body:fd})
    .then(r=>r.json())
    .then(res=>{
      Swal.close();
      if (res.status==='success') {
        const isReview = res.save_status==='review';
        dcmAlert._inject();
        Swal.fire({
          icon:'success',
          title: QB_EDIT_ID ? 'Question Updated!' : 'Question Saved!',
          html:`<div style="text-align:center"><div style="font-family:monospace;font-size:1.1rem;font-weight:800;background:#0f172a;color:#e2e8f0;display:inline-block;padding:.35rem .9rem;border-radius:9px;margin:.5rem 0">${res.q_uid}</div><br><span style="display:inline-flex;align-items:center;gap:.35rem;background:${isReview?'#e0f2fe':'#f0fdf4'};color:${isReview?'#0369a1':'#059669'};border-radius:100px;padding:.3rem .9rem;font-size:.78rem;font-weight:800;margin-top:.4rem">${isReview?'Submitted for Review':'Saved as Draft'}</span></div>`,
          showDenyButton:true,
          confirmButtonText:'<i class="bi bi-collection me-1"></i>View All',
          denyButtonText:'<i class="bi bi-plus-lg me-1"></i>Add Another',
          denyButtonColor:'#6c757d',
          customClass:{popup:'ds-pop',title:'ds-ttl',confirmButton:'ds-btn'}
        }).then(r=>{ window.location.href = r.isDenied ? '?view=qb_add_question' : '?view=qb_all_questions'; });
      } else {
        dcmAlert.error('Save failed', res.message);
      }
    })
    .catch(()=>dcmAlert.error('Request failed','Unable to reach the server.'));
}

/* ── Load for edit ───────────────────────────────────────── */
function loadExistingQuestion() {
  fetch(`ajax/ajax_qb_questions.php?action=get&id=${QB_EDIT_ID}`)
    .then(r=>r.json()).then(res=>{
      if (res.status!=='success') return;
      const q = res.data;
      setTimeout(() => {
        setVal('f_subject',q.subject_id); setVal('f_level',q.level_id);
        setVal('f_difficulty',q.difficulty_id); setVal('f_bloom',q.bloom_id);
        setVal('f_section',q.section_id); setVal('f_question_type',q.question_type);
        setVal('f_marks',q.marks); setVal('f_est_time',q.estimated_time_seconds);
        setVal('f_year',q.year_year); setVal('f_question_number',q.question_number);
        setVal('f_question_stem',q.question_stem);
        setVal('f_solution',q.solution_explanation); setVal('f_swahili_hint',q.swahili_hint);
        onTypeChange();
        cascadeChapter();
        setTimeout(()=>{
          setVal('f_chapter',q.chapter_id); cascadeSubtopic();
          setTimeout(()=>{
            setVal('f_subtopic',q.subtopic_id);
            if (q.question_type==='mcq'&&q.options) {
              q.options.forEach(opt=>{
                const el=document.getElementById(`opt${opt.option_label}`); if(el) el.value=opt.option_text;
                if(opt.is_correct) selectOpt(opt.option_label);
              });
            }
            if (q.question_type==='true_false'&&q.correct_answer) selectTF(q.correct_answer);
            if (q.question_type==='fill_blank') setVal('f_fill_answer',q.correct_answer);
            if (q.question_type==='essay') setVal('f_essay_answer',q.correct_answer);
            if (q.question_type==='matching'&&q.options&&q.options.length) {
              setMatchingPairs(q.options.map(o=>{try{return JSON.parse(o.option_text);}catch(e){return{left:o.option_text,right:''};}}));
            }
          },200);
        },200);
      },500);
    });
}

function setVal(id, val) { const el=document.getElementById(id); if(el&&val!=null) el.value=val; }
</script>
