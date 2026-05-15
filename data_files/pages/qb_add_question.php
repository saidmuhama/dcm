<?php
$edit_id = (int)($_GET['id'] ?? 0);
$page_title = $edit_id ? 'Edit Question' : 'Add Question';
?>

<div class="container-fluid px-3 py-3">

  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h5 class="mb-0 fw-semibold">
        <i class="bi bi-pencil-square me-2 text-primary"></i><?= $page_title ?>
      </h5>
      <small class="text-muted">Question Bank — <?= $edit_id ? 'update an existing' : 'author a new' ?> question</small>
    </div>
    <a href="?view=qb_all_questions" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i> Back to Questions
    </a>
  </div>

  <form id="qbAddForm" novalidate>
    <input type="hidden" id="qbEditId" value="<?= $edit_id ?>">

    <div class="row g-3">

      <!-- LEFT: Taxonomy + Metadata -->
      <div class="col-12 col-lg-4">

        <!-- Taxonomy Card -->
        <div class="card adminuiux-card shadow-sm mb-3">
          <div class="card-header bg-transparent py-2">
            <small class="fw-semibold text-muted text-uppercase letter-spacing-1">Taxonomy</small>
          </div>
          <div class="card-body">

            <div class="mb-3">
              <label class="form-label small fw-medium">Subject <span class="text-danger">*</span></label>
              <select id="f_subject" class="form-select form-select-sm" onchange="cascadeLevel(); cascadeChapter()">
                <option value="">— Select Subject —</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-medium">Level <span class="text-danger">*</span></label>
              <select id="f_level" class="form-select form-select-sm" onchange="cascadeChapter()">
                <option value="">— Select Level —</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-medium">Chapter <span class="text-danger">*</span></label>
              <select id="f_chapter" class="form-select form-select-sm" onchange="cascadeSubtopic()">
                <option value="">— Select Chapter —</option>
              </select>
            </div>

            <div class="mb-0">
              <label class="form-label small fw-medium">Subtopic <span class="text-danger">*</span></label>
              <select id="f_subtopic" class="form-select form-select-sm">
                <option value="">— Select Subtopic —</option>
              </select>
            </div>

          </div>
        </div>

        <!-- Metadata Card -->
        <div class="card adminuiux-card shadow-sm mb-3">
          <div class="card-header bg-transparent py-2">
            <small class="fw-semibold text-muted text-uppercase letter-spacing-1">Metadata</small>
          </div>
          <div class="card-body">

            <div class="row gx-2">
              <div class="col-6 mb-3">
                <label class="form-label small fw-medium">Question Type <span class="text-danger">*</span></label>
                <select id="f_question_type" class="form-select form-select-sm" onchange="onTypeChange()">
                  <option value="mcq">MCQ</option>
                  <option value="true_false">True / False</option>
                  <option value="essay">Essay</option>
                  <option value="fill_blank">Fill in Blank</option>
                  <option value="matching">Matching</option>
                </select>
              </div>
              <div class="col-6 mb-3">
                <label class="form-label small fw-medium">Section</label>
                <select id="f_section" class="form-select form-select-sm">
                  <option value="">None</option>
                </select>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-medium">Difficulty <span class="text-danger">*</span></label>
              <select id="f_difficulty" class="form-select form-select-sm">
                <option value="">— Select —</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-medium">Bloom's Level</label>
              <select id="f_bloom" class="form-select form-select-sm">
                <option value="">— Select —</option>
              </select>
            </div>

            <div class="row gx-2">
              <div class="col-6 mb-3">
                <label class="form-label small fw-medium">Marks</label>
                <input type="number" id="f_marks" class="form-control form-control-sm" value="1" min="0.5" step="0.5">
              </div>
              <div class="col-6 mb-3">
                <label class="form-label small fw-medium">Time (sec)</label>
                <input type="number" id="f_est_time" class="form-control form-control-sm" value="60" min="10" step="10">
              </div>
            </div>

            <div class="row gx-2">
              <div class="col-6 mb-0">
                <label class="form-label small fw-medium">Year</label>
                <input type="number" id="f_year" class="form-control form-control-sm" placeholder="e.g. 2024" min="1990" max="2099">
              </div>
              <div class="col-6 mb-0">
                <label class="form-label small fw-medium">Question No.</label>
                <input type="text" id="f_question_number" class="form-control form-control-sm" placeholder="e.g. Q3(b)">
              </div>
            </div>

          </div>
        </div>

      </div><!-- /col-lg-4 -->

      <!-- RIGHT: Stem + Options + Hints -->
      <div class="col-12 col-lg-8">

        <!-- Stem Card -->
        <div class="card adminuiux-card shadow-sm mb-3">
          <div class="card-header bg-transparent py-2">
            <small class="fw-semibold text-muted text-uppercase letter-spacing-1">Question Stem</small>
          </div>
          <div class="card-body">
            <textarea id="f_question_stem" class="form-control" rows="5"
              placeholder="Type the question here..."></textarea>
          </div>
        </div>

        <!-- MCQ Options Card -->
        <div class="card adminuiux-card shadow-sm mb-3" id="mcqBlock">
          <div class="card-header bg-transparent py-2">
            <small class="fw-semibold text-muted text-uppercase letter-spacing-1">Answer Options</small>
          </div>
          <div class="card-body">
            <div class="mb-2 text-muted small">Mark the correct answer with the radio button.</div>

            <?php foreach (['A','B','C','D'] as $lbl): ?>
            <div class="input-group input-group-sm mb-2">
              <div class="input-group-text px-2">
                <input type="radio" name="correct_answer_radio" value="<?= $lbl ?>"
                       id="radio<?= $lbl ?>" class="form-check-input mt-0">
              </div>
              <label class="input-group-text fw-bold" for="radio<?= $lbl ?>"><?= $lbl ?></label>
              <input type="text" id="opt<?= $lbl ?>" class="form-control"
                     placeholder="Option <?= $lbl ?>...">
            </div>
            <?php endforeach; ?>

          </div>
        </div>

        <!-- True/False Block -->
        <div class="card adminuiux-card shadow-sm mb-3 d-none" id="tfBlock">
          <div class="card-header bg-transparent py-2">
            <small class="fw-semibold text-muted text-uppercase letter-spacing-1">Correct Answer</small>
          </div>
          <div class="card-body d-flex gap-4">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="tf_answer" id="tfTrue" value="True">
              <label class="form-check-label fw-medium" for="tfTrue">True</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="tf_answer" id="tfFalse" value="False">
              <label class="form-check-label fw-medium" for="tfFalse">False</label>
            </div>
          </div>
        </div>

        <!-- Fill Blank Answer -->
        <div class="card adminuiux-card shadow-sm mb-3 d-none" id="fillBlock">
          <div class="card-header bg-transparent py-2">
            <small class="fw-semibold text-muted text-uppercase letter-spacing-1">Expected Answer</small>
          </div>
          <div class="card-body">
            <input type="text" id="f_fill_answer" class="form-control form-control-sm"
                   placeholder="The expected answer text...">
          </div>
        </div>

        <!-- Solution + Hint -->
        <div class="card adminuiux-card shadow-sm mb-3">
          <div class="card-header bg-transparent py-2">
            <small class="fw-semibold text-muted text-uppercase letter-spacing-1">Solution & Hints</small>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label small fw-medium">Solution Explanation</label>
              <textarea id="f_solution" class="form-control form-control-sm" rows="3"
                placeholder="Step-by-step explanation of the correct answer..."></textarea>
            </div>
            <div class="mb-0">
              <label class="form-label small fw-medium">Swahili Hint <span class="text-muted">(optional)</span></label>
              <textarea id="f_swahili_hint" class="form-control form-control-sm" rows="2"
                placeholder="Kidokezo kwa Kiswahili..."></textarea>
            </div>
          </div>
        </div>

        <!-- Submit -->
        <div class="d-flex justify-content-end gap-2">
          <a href="?view=qb_all_questions" class="btn btn-light">Cancel</a>
          <button type="button" class="btn btn-outline-primary" onclick="submitQuestion('draft')">
            <i class="bi bi-floppy me-1"></i> Save as Draft
          </button>
          <button type="button" class="btn btn-primary" onclick="submitQuestion('review')" id="btnSubmitReview">
            <i class="bi bi-send me-1"></i> Submit for Review
          </button>
        </div>

      </div><!-- /col-lg-8 -->
    </div><!-- /row -->
  </form>

</div>

<script>
const QB_EDIT_ID = <?= $edit_id ?>;

/* ── Taxonomy cache ─────────────────────────────────────────── */
let allChapters  = [];
let allSubtopics = [];

document.addEventListener('DOMContentLoaded', () => {
  loadDropdowns();
  if (QB_EDIT_ID) loadExistingQuestion();
});

/* ── Load all dropdowns on init ─────────────────────────────── */
function loadDropdowns() {
  const tasks = [
    { entity: 'subjects',          el: 'f_subject',    v: 'subject_id',    l: 'subject_name' },
    { entity: 'levels',            el: 'f_level',      v: 'level_id',      l: 'level_name' },
    { entity: 'difficulty_levels', el: 'f_difficulty', v: 'difficulty_id', l: 'difficulty_name' },
    { entity: 'bloom_levels',      el: 'f_bloom',      v: 'bloom_id',      l: 'bloom_name' },
    { entity: 'sections',          el: 'f_section',    v: 'section_id',    l: 'section_name' },
  ];

  tasks.forEach(t => {
    fetch(`ajax/ajax_qb_taxonomy.php?entity=${t.entity}&action=list`)
      .then(r => r.json()).then(res => {
        if (res.status !== 'success') return;
        const sel = document.getElementById(t.el);
        res.data.forEach(row => {
          const o = document.createElement('option');
          o.value = row[t.v]; o.textContent = row[t.l];
          sel.appendChild(o);
        });
      });
  });

  // Pre-load all chapters and subtopics into memory for cascade
  fetch('ajax/ajax_qb_taxonomy.php?entity=chapters&action=list')
    .then(r => r.json()).then(res => {
      if (res.status === 'success') allChapters = res.data;
    });

  fetch('ajax/ajax_qb_taxonomy.php?entity=subtopics&action=list')
    .then(r => r.json()).then(res => {
      if (res.status === 'success') allSubtopics = res.data;
    });
}

/* ── Cascade dropdowns ──────────────────────────────────────── */
function cascadeLevel() {
  // Levels are global (not filtered by subject) — nothing extra needed
}

function cascadeChapter() {
  const subj  = document.getElementById('f_subject').value;
  const level = document.getElementById('f_level').value;
  const sel   = document.getElementById('f_chapter');
  sel.innerHTML = '<option value="">— Select Chapter —</option>';
  document.getElementById('f_subtopic').innerHTML = '<option value="">— Select Subtopic —</option>';

  if (!subj && !level) return;

  allChapters
    .filter(c => (!subj || c.subject_id == subj) && (!level || c.level_id == level))
    .forEach(c => {
      const o = document.createElement('option');
      o.value = c.chapter_id;
      o.textContent = (c.chapter_number ? `Ch.${c.chapter_number} – ` : '') + c.chapter_name;
      sel.appendChild(o);
    });
}

function cascadeSubtopic() {
  const chapter_id = document.getElementById('f_chapter').value;
  const sel        = document.getElementById('f_subtopic');
  sel.innerHTML = '<option value="">— Select Subtopic —</option>';
  if (!chapter_id) return;

  allSubtopics
    .filter(st => st.chapter_id == chapter_id)
    .forEach(st => {
      const o = document.createElement('option');
      o.value = st.subtopic_id;
      o.textContent = st.subtopic_name;
      sel.appendChild(o);
    });
}

/* ── Question type switch ───────────────────────────────────── */
function onTypeChange() {
  const type = document.getElementById('f_question_type').value;
  document.getElementById('mcqBlock').classList.toggle('d-none', type !== 'mcq');
  document.getElementById('tfBlock').classList.toggle('d-none', type !== 'true_false');
  document.getElementById('fillBlock').classList.toggle('d-none', type !== 'fill_blank');
}

/* ── Collect correct answer based on type ───────────────────── */
function getCorrectAnswer() {
  const type = document.getElementById('f_question_type').value;
  if (type === 'mcq') {
    const r = document.querySelector('input[name="correct_answer_radio"]:checked');
    return r ? r.value : '';
  }
  if (type === 'true_false') {
    const r = document.querySelector('input[name="tf_answer"]:checked');
    return r ? r.value : '';
  }
  if (type === 'fill_blank') {
    return document.getElementById('f_fill_answer').value.trim();
  }
  return '';
}

/* ── Collect MCQ options array ──────────────────────────────── */
function getMCQOptions() {
  return ['A','B','C','D'].map(l => document.getElementById(`opt${l}`).value.trim());
}

/* ── Submit ─────────────────────────────────────────────────── */
function submitQuestion(submitStatus) {
  const subject_id    = document.getElementById('f_subject').value;
  const level_id      = document.getElementById('f_level').value;
  const chapter_id    = document.getElementById('f_chapter').value;
  const subtopic_id   = document.getElementById('f_subtopic').value;
  const difficulty_id = document.getElementById('f_difficulty').value;
  const question_stem = document.getElementById('f_question_stem').value.trim();
  const question_type = document.getElementById('f_question_type').value;
  const correct_answer = getCorrectAnswer();

  // Validation
  const missing = [];
  if (!subject_id)    missing.push('Subject');
  if (!level_id)      missing.push('Level');
  if (!chapter_id)    missing.push('Chapter');
  if (!subtopic_id)   missing.push('Subtopic');
  if (!difficulty_id) missing.push('Difficulty');
  if (!question_stem) missing.push('Question Stem');
  if (question_type === 'mcq') {
    const opts = getMCQOptions().filter(v => v);
    if (opts.length < 2) missing.push('At least 2 MCQ options');
    if (!correct_answer) missing.push('Correct Answer (MCQ)');
  }
  if (question_type === 'true_false' && !correct_answer) missing.push('Correct Answer (True/False)');

  if (missing.length) {
    Swal.fire('Validation', 'Please fill in: ' + missing.join(', '), 'warning');
    return;
  }

  Swal.fire({ title: 'Saving...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

  const fd = new FormData();
  fd.append('subject_id',    subject_id);
  fd.append('level_id',      level_id);
  fd.append('chapter_id',    chapter_id);
  fd.append('subtopic_id',   subtopic_id);
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

  if (question_type === 'mcq') {
    getMCQOptions().forEach(opt => fd.append('options[]', opt));
  }

  // For edit mode, append the ID and save status
  if (QB_EDIT_ID) {
    fd.append('question_id', QB_EDIT_ID);
    fd.append('save_status', submitStatus);
  }

  const url = QB_EDIT_ID
    ? 'ajax/ajax_qb_update_question.php'
    : 'ajax/ajax_qb_save_question.php';

  fetch(url, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
      Swal.close();
      if (res.status === 'success') {
        Swal.fire({
          icon: 'success',
          title: QB_EDIT_ID ? 'Updated!' : 'Saved!',
          html: `<code>${res.q_uid}</code><br><small class="text-muted">Saved as draft</small>`,
          confirmButtonText: 'View Questions'
        }).then(() => {
          window.location.href = '?view=qb_all_questions';
        });
      } else {
        Swal.fire('Error', res.message, 'error');
      }
    })
    .catch(() => Swal.fire('Error', 'Request failed', 'error'));
}

/* ── Load existing question for edit mode ───────────────────── */
function loadExistingQuestion() {
  fetch(`ajax/ajax_qb_questions.php?action=get&id=${QB_EDIT_ID}`)
    .then(r => r.json())
    .then(res => {
      if (res.status !== 'success') return;
      const q = res.data;

      // Wait for dropdowns to load then populate
      setTimeout(() => {
        setVal('f_subject',       q.subject_id);
        setVal('f_level',         q.level_id);
        setVal('f_difficulty',    q.difficulty_id);
        setVal('f_bloom',         q.bloom_id);
        setVal('f_section',       q.section_id);
        setVal('f_question_type', q.question_type);
        setVal('f_marks',         q.marks);
        setVal('f_est_time',      q.estimated_time_seconds);
        setVal('f_year',          q.year_year);
        setVal('f_question_number', q.question_number);
        setVal('f_question_stem', q.question_stem);
        setVal('f_solution',      q.solution_explanation);
        setVal('f_swahili_hint',  q.swahili_hint);

        onTypeChange();

        // Trigger cascades after data is set
        cascadeChapter();
        setTimeout(() => {
          setVal('f_chapter', q.chapter_id);
          cascadeSubtopic();
          setTimeout(() => {
            setVal('f_subtopic', q.subtopic_id);

            // Populate MCQ options and correct answer
            if (q.question_type === 'mcq' && q.options) {
              q.options.forEach(opt => {
                const el = document.getElementById(`opt${opt.option_label}`);
                if (el) el.value = opt.option_text;
                if (opt.is_correct) {
                  const r = document.getElementById(`radio${opt.option_label}`);
                  if (r) r.checked = true;
                }
              });
            }
            if (q.question_type === 'true_false' && q.correct_answer) {
              const r = document.querySelector(`input[name="tf_answer"][value="${q.correct_answer}"]`);
              if (r) r.checked = true;
            }
            if (q.question_type === 'fill_blank') {
              setVal('f_fill_answer', q.correct_answer);
            }
          }, 200);
        }, 200);
      }, 500);
    });
}

function setVal(id, val) {
  const el = document.getElementById(id);
  if (el && val != null) el.value = val;
}
</script>
