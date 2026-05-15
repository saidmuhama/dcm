<?php
$view = $_GET['view'] ?? 'qb_all_questions';

$statusMap = [
    'qb_all_questions'      => ['label' => 'All Questions',      'status' => '',          'badge' => 'secondary'],
    'qb_draft_questions'    => ['label' => 'Draft',              'status' => 'draft',     'badge' => 'warning'],
    'qb_review_queue'       => ['label' => 'Review Queue',       'status' => 'review',    'badge' => 'info'],
    'qb_approved_questions' => ['label' => 'Approved',           'status' => 'approved',  'badge' => 'success'],
    'qb_published_questions'=> ['label' => 'Published',          'status' => 'published', 'badge' => 'primary'],
    'qb_archived_questions' => ['label' => 'Archived',           'status' => 'archived',  'badge' => 'dark'],
];

$current = $statusMap[$view] ?? $statusMap['qb_all_questions'];
$filterStatus = $current['status'];
?>

<div class="container-fluid px-3 py-3">

  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h5 class="mb-0 fw-semibold">
        <i class="bi bi-patch-question me-2 text-primary"></i><?= $current['label'] ?>
      </h5>
      <small class="text-muted">Question Bank — manage and filter questions</small>
    </div>
    <a href="?view=qb_add_question" class="btn btn-primary btn-sm">
      <i class="bi bi-plus-lg me-1"></i> Add Question
    </a>
  </div>

  <!-- Status Nav Tabs -->
  <ul class="nav nav-tabs mb-3">
    <?php foreach ($statusMap as $v => $s): ?>
    <li class="nav-item">
      <a class="nav-link <?= $v === $view ? 'active' : '' ?> py-1 px-3"
         href="?view=<?= $v ?>">
        <?= $s['label'] ?>
        <span class="badge bg-<?= $s['badge'] ?> ms-1" id="badge_<?= $v ?>"></span>
      </a>
    </li>
    <?php endforeach; ?>
  </ul>

  <!-- Filters -->
  <div class="card adminuiux-card shadow-sm mb-3">
    <div class="card-body py-2">
      <div class="row gx-2 align-items-end">
        <div class="col-12 col-md-auto">
          <label class="form-label small mb-1">Subject</label>
          <select id="fSubject" class="form-select form-select-sm" onchange="loadChapters(); loadQuestions()">
            <option value="">All Subjects</option>
          </select>
        </div>
        <div class="col-12 col-md-auto">
          <label class="form-label small mb-1">Level</label>
          <select id="fLevel" class="form-select form-select-sm" onchange="loadChapters(); loadQuestions()">
            <option value="">All Levels</option>
          </select>
        </div>
        <div class="col-12 col-md-auto">
          <label class="form-label small mb-1">Chapter</label>
          <select id="fChapter" class="form-select form-select-sm" onchange="loadQuestions()">
            <option value="">All Chapters</option>
          </select>
        </div>
        <div class="col-12 col-md-auto">
          <label class="form-label small mb-1">Difficulty</label>
          <select id="fDifficulty" class="form-select form-select-sm" onchange="loadQuestions()">
            <option value="">All Difficulties</option>
          </select>
        </div>
        <div class="col-12 col-md-auto">
          <label class="form-label small mb-1">Type</label>
          <select id="fType" class="form-select form-select-sm" onchange="loadQuestions()">
            <option value="">All Types</option>
            <option value="mcq">MCQ</option>
            <option value="true_false">True/False</option>
            <option value="essay">Essay</option>
            <option value="matching">Matching</option>
            <option value="fill_blank">Fill Blank</option>
          </select>
        </div>
        <div class="col-12 col-md">
          <label class="form-label small mb-1">Search</label>
          <input type="text" id="fSearch" class="form-control form-control-sm"
                 placeholder="Search q_uid or stem..." oninput="debounceSearch()">
        </div>
        <div class="col-auto">
          <button class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
            <i class="bi bi-x-circle"></i> Reset
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Table -->
  <div class="card adminuiux-card shadow-sm">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-2">
      <span class="small text-muted" id="qCount">Loading...</span>
      <div class="d-flex gap-2" id="bulkActions" style="display:none!important">
        <select id="bulkStatus" class="form-select form-select-sm w-auto">
          <option value="">Change Status To...</option>
          <option value="draft">Draft</option>
          <option value="review">Review</option>
          <option value="approved">Approved</option>
          <option value="published">Published</option>
          <option value="archived">Archived</option>
        </select>
        <button class="btn btn-sm btn-primary" onclick="bulkChangeStatus()">Apply</button>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th width="36"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th>
              <th>Question ID</th>
              <th>Subject</th>
              <th>Level</th>
              <th>Chapter</th>
              <th>Type</th>
              <th>Difficulty</th>
              <th>Status</th>
              <th>Marks</th>
              <th width="90" class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody id="qTbody">
            <tr><td colspan="10" class="text-center py-4">
              <div class="spinner-border spinner-border-sm text-primary"></div>
            </td></tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-transparent">
      <div id="qPagination" class="d-flex justify-content-end align-items-center gap-2"></div>
    </div>
  </div>

</div>

<!-- Quick View Modal -->
<div class="modal fade" id="qViewModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content custom-modal">
      <div class="modal-header">
        <h6 class="modal-title fw-semibold" id="qViewUID">Question Preview</h6>
        <button type="button" class="btn-close custom-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="qViewBody"></div>
      <div class="modal-footer">
        <span id="qViewStatus"></span>
        <div class="ms-auto d-flex gap-2">
          <select id="qStatusChange" class="form-select form-select-sm w-auto">
            <option value="">Change Status</option>
            <option value="draft">Draft</option>
            <option value="review">Review</option>
            <option value="approved">Approved</option>
            <option value="published">Published</option>
            <option value="archived">Archived</option>
          </select>
          <button class="btn btn-sm btn-primary" onclick="applyStatusChange()">Apply</button>
          <button class="btn btn-sm btn-outline-danger" onclick="deleteQuestion()">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const FILTER_STATUS = '<?= $filterStatus ?>';
const STATUS_BADGES = {
  draft:'warning', review:'info', approved:'success', published:'primary', archived:'dark'
};

let viewModal, currentQId = null, searchTimer;
let page = 1, perPage = 20, totalRows = 0;

document.addEventListener('DOMContentLoaded', () => {
  viewModal = new bootstrap.Modal(document.getElementById('qViewModal'));
  loadFilterOptions();
  loadBadgeCounts();
  loadQuestions();
});

/* ── Filter options ─────────────────────────────────────────── */
function loadFilterOptions() {
  ['subjects','levels','difficulty_levels'].forEach(entity => {
    fetch(`ajax/ajax_qb_taxonomy.php?entity=${entity}&action=list`)
      .then(r => r.json()).then(res => {
        if (res.status !== 'success') return;
        const maps = {
          subjects:          { el: 'fSubject',    v: 'subject_id',    l: 'subject_name' },
          levels:            { el: 'fLevel',      v: 'level_id',      l: 'level_name' },
          difficulty_levels: { el: 'fDifficulty', v: 'difficulty_id', l: 'difficulty_name' },
        };
        const m = maps[entity];
        const sel = document.getElementById(m.el);
        res.data.forEach(row => {
          const o = document.createElement('option');
          o.value = row[m.v]; o.textContent = row[m.l];
          sel.appendChild(o);
        });
      });
  });
}

function loadChapters() {
  const subj  = document.getElementById('fSubject').value;
  const level = document.getElementById('fLevel').value;
  const sel   = document.getElementById('fChapter');
  sel.innerHTML = '<option value="">All Chapters</option>';
  if (!subj && !level) return;

  fetch(`ajax/ajax_qb_taxonomy.php?entity=chapters&action=list`)
    .then(r => r.json()).then(res => {
      if (res.status !== 'success') return;
      res.data
        .filter(r => (!subj || r.subject_id == subj) && (!level || r.level_id == level))
        .forEach(r => {
          const o = document.createElement('option');
          o.value = r.chapter_id;
          o.textContent = (r.chapter_number ? `Ch.${r.chapter_number} – ` : '') + r.chapter_name;
          sel.appendChild(o);
        });
    });
}

/* ── Badge counts ───────────────────────────────────────────── */
function loadBadgeCounts() {
  fetch('ajax/ajax_qb_questions.php?action=counts')
    .then(r => r.json()).then(res => {
      if (res.status !== 'success') return;
      const counts = res.data;
      document.getElementById('badge_qb_all_questions').textContent = counts.total || '';
      ['draft','review','approved','published','archived'].forEach(s => {
        const el = document.getElementById(`badge_qb_${s === 'review' ? 'review_queue' : s + '_questions'}`);
        if (el) el.textContent = counts[s] || '';
      });
    });
}

/* ── Load questions ─────────────────────────────────────────── */
function loadQuestions() {
  page = 1; fetchQuestions();
}

function debounceSearch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(loadQuestions, 350);
}

function fetchQuestions() {
  const params = new URLSearchParams({
    action:        'list',
    status_filter: FILTER_STATUS,
    subject_id: document.getElementById('fSubject').value,
    level_id:   document.getElementById('fLevel').value,
    chapter_id: document.getElementById('fChapter').value,
    difficulty_id: document.getElementById('fDifficulty').value,
    type:       document.getElementById('fType').value,
    q:          document.getElementById('fSearch').value,
    page,
    per_page:   perPage,
  });

  document.getElementById('qTbody').innerHTML =
    `<tr><td colspan="10" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>`;

  fetch(`ajax/ajax_qb_questions.php?${params}`)
    .then(r => r.json())
    .then(res => {
      if (res.status !== 'success') return;
      totalRows = res.total;
      renderQuestions(res.data);
      renderPagination();
      document.getElementById('qCount').textContent =
        `${res.total} question${res.total !== 1 ? 's' : ''}`;
    });
}

function renderQuestions(rows) {
  const tbody = document.getElementById('qTbody');
  if (!rows.length) {
    tbody.innerHTML = `<tr><td colspan="10" class="text-center text-muted py-5">
      <i class="bi bi-inbox fs-2 d-block mb-2"></i>No questions found.
      <a href="?view=qb_add_question" class="d-block mt-2">Add your first question →</a>
    </td></tr>`;
    return;
  }
  tbody.innerHTML = rows.map(q => `
    <tr style="cursor:pointer" onclick="viewQuestion(${q.question_id})">
      <td onclick="event.stopPropagation()">
        <input type="checkbox" class="qCheck" value="${q.question_id}" onchange="updateBulkBar()">
      </td>
      <td><code class="small">${q.q_uid}</code></td>
      <td class="small">${q.subject_name ?? '—'}</td>
      <td class="small">${q.level_name ?? '—'}</td>
      <td class="small">${q.chapter_name ?? '—'}</td>
      <td><span class="badge bg-light text-dark border">${q.question_type}</span></td>
      <td class="small">${q.difficulty_name ?? '<span class="text-muted">—</span>'}</td>
      <td><span class="badge bg-${STATUS_BADGES[q.status] ?? 'secondary'}">${q.status}</span></td>
      <td class="small">${q.marks}</td>
      <td class="text-end" onclick="event.stopPropagation()">
        <a href="?view=qb_add_question&id=${q.question_id}" class="btn btn-sm btn-outline-primary py-0 px-2 me-1">
          <i class="bi bi-pencil"></i>
        </a>
        <button class="btn btn-sm btn-outline-danger py-0 px-2" onclick="deleteQ(${q.question_id})">
          <i class="bi bi-trash"></i>
        </button>
      </td>
    </tr>`).join('');
}

function renderPagination() {
  const pages = Math.ceil(totalRows / perPage);
  if (pages <= 1) { document.getElementById('qPagination').innerHTML = ''; return; }
  let html = `<span class="small text-muted me-2">Page ${page} of ${pages}</span>`;
  if (page > 1) html += `<button class="btn btn-sm btn-outline-secondary" onclick="goPage(${page-1})">‹ Prev</button>`;
  if (page < pages) html += `<button class="btn btn-sm btn-outline-secondary" onclick="goPage(${page+1})">Next ›</button>`;
  document.getElementById('qPagination').innerHTML = html;
}

function goPage(p) { page = p; fetchQuestions(); }

/* ── View question ──────────────────────────────────────────── */
function viewQuestion(id) {
  currentQId = id;
  fetch(`ajax/ajax_qb_questions.php?action=get&id=${id}`)
    .then(r => r.json()).then(res => {
      if (res.status !== 'success') return;
      const q = res.data;
      document.getElementById('qViewUID').textContent = q.q_uid;
      document.getElementById('qViewStatus').innerHTML =
        `<span class="badge bg-${STATUS_BADGES[q.status] ?? 'secondary'} me-2">${q.status}</span>`;
      document.getElementById('qStatusChange').value = q.status;

      const opts = (q.options || []).map((o, i) =>
        `<div class="d-flex align-items-start gap-2 mb-2 p-2 rounded ${o.is_correct ? 'bg-success bg-opacity-10 border border-success' : 'bg-light'}">
          <span class="fw-bold">${o.option_label}.</span>
          <span>${o.option_text}</span>
          ${o.is_correct ? '<i class="bi bi-check-circle-fill text-success ms-auto"></i>' : ''}
        </div>`).join('');

      document.getElementById('qViewBody').innerHTML = `
        <div class="row gx-3 mb-3">
          <div class="col-4"><small class="text-muted d-block">Subject</small>${q.subject_name ?? '—'}</div>
          <div class="col-4"><small class="text-muted d-block">Level</small>${q.level_name ?? '—'}</div>
          <div class="col-4"><small class="text-muted d-block">Chapter</small>${q.chapter_name ?? '—'}</div>
        </div>
        <div class="row gx-3 mb-3">
          <div class="col-4"><small class="text-muted d-block">Subtopic</small>${q.subtopic_name ?? '—'}</div>
          <div class="col-4"><small class="text-muted d-block">Difficulty</small>${q.difficulty_name ?? '—'}</div>
          <div class="col-4"><small class="text-muted d-block">Bloom</small>${q.bloom_name ?? '—'}</div>
        </div>
        <hr class="my-2">
        <p class="fw-semibold mb-3">${q.question_stem}</p>
        ${opts}
        ${q.solution_explanation ? `<div class="mt-3 p-3 bg-info bg-opacity-10 rounded">
          <small class="text-muted d-block mb-1">Solution</small>${q.solution_explanation}</div>` : ''}
        ${q.swahili_hint ? `<div class="mt-2 p-3 bg-warning bg-opacity-10 rounded">
          <small class="text-muted d-block mb-1">Swahili Hint</small>${q.swahili_hint}</div>` : ''}
      `;
      viewModal.show();
    });
}

function applyStatusChange() {
  const status = document.getElementById('qStatusChange').value;
  if (!status || !currentQId) return;
  changeStatus([currentQId], status, () => {
    viewModal.hide();
    loadQuestions();
    loadBadgeCounts();
  });
}

/* ── Bulk actions ───────────────────────────────────────────── */
function toggleSelectAll(cb) {
  document.querySelectorAll('.qCheck').forEach(c => c.checked = cb.checked);
  updateBulkBar();
}
function updateBulkBar() {
  const checked = document.querySelectorAll('.qCheck:checked').length;
  document.getElementById('bulkActions').style.display = checked ? 'flex' : 'none';
}
function bulkChangeStatus() {
  const ids = [...document.querySelectorAll('.qCheck:checked')].map(c => +c.value);
  const status = document.getElementById('bulkStatus').value;
  if (!ids.length || !status) return;
  changeStatus(ids, status, () => { loadQuestions(); loadBadgeCounts(); });
}

function changeStatus(ids, status, cb) {
  Swal.fire({ title: 'Updating...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
  fetch('ajax/ajax_qb_questions.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'change_status', ids, status })
  }).then(r => r.json()).then(res => {
    Swal.close();
    if (res.status === 'success') {
      Swal.fire({ icon:'success', title:'Updated', timer:1200, showConfirmButton:false });
      if (cb) cb();
    } else Swal.fire('Error', res.message, 'error');
  });
}

function deleteQ(id) {
  Swal.fire({ title:'Delete question?', icon:'warning', showCancelButton:true,
    confirmButtonColor:'#dc3545', confirmButtonText:'Yes, delete' })
  .then(r => {
    if (!r.isConfirmed) return;
    fetch('ajax/ajax_qb_questions.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'delete', id })
    }).then(r => r.json()).then(res => {
      if (res.status === 'success') { loadQuestions(); loadBadgeCounts(); }
      else Swal.fire('Error', res.message, 'error');
    });
  });
}
function deleteQuestion() { if (currentQId) deleteQ(currentQId); viewModal.hide(); }

function resetFilters() {
  ['fSubject','fLevel','fChapter','fDifficulty','fType'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('fSearch').value = '';
  document.getElementById('fChapter').innerHTML = '<option value="">All Chapters</option>';
  loadQuestions();
}
</script>
