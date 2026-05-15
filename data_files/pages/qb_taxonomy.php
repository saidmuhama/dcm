<?php
$view = $_GET['view'] ?? 'qb_subjects';

/* ── Entity config ─────────────────────────────────────────── */
$cfg = [
    'qb_subjects' => [
        'title'  => 'Subjects',
        'icon'   => 'bi-book',
        'entity' => 'subjects',
        'cols'   => [
            ['key' => 'subject_code', 'label' => 'Code'],
            ['key' => 'subject_name', 'label' => 'Subject Name'],
            ['key' => 'created_at',   'label' => 'Created'],
        ],
        'form' => [
            ['name'=>'subject_code','label'=>'Subject Code','type'=>'text',  'req'=>false,'help'=>'e.g. MATH, BIO, PHY'],
            ['name'=>'subject_name','label'=>'Subject Name','type'=>'text',  'req'=>true],
        ],
    ],
    'qb_levels' => [
        'title'  => 'Levels / Classes',
        'icon'   => 'bi-layers',
        'entity' => 'levels',
        'cols'   => [
            ['key' => 'sort_order', 'label' => '#'],
            ['key' => 'level_name', 'label' => 'Level Name'],
        ],
        'form' => [
            ['name'=>'level_name', 'label'=>'Level Name',  'type'=>'text',  'req'=>true, 'help'=>'e.g. Standard 7, Form 2, Grade 4'],
            ['name'=>'sort_order', 'label'=>'Sort Order',  'type'=>'number','req'=>false],
        ],
    ],
    'qb_chapters' => [
        'title'  => 'Chapters',
        'icon'   => 'bi-bookmark',
        'entity' => 'chapters',
        'cols'   => [
            ['key' => 'chapter_number', 'label' => 'No.'],
            ['key' => 'chapter_name',   'label' => 'Chapter Name'],
            ['key' => 'subject_name',   'label' => 'Subject'],
            ['key' => 'level_name',     'label' => 'Level'],
        ],
        'form' => [
            ['name'=>'subject_id',     'label'=>'Subject',        'type'=>'select_subjects','req'=>true],
            ['name'=>'level_id',       'label'=>'Level',          'type'=>'select_levels',  'req'=>true],
            ['name'=>'chapter_number', 'label'=>'Chapter Number', 'type'=>'text',           'req'=>false,'help'=>'e.g. 1, 2, 09'],
            ['name'=>'chapter_name',   'label'=>'Chapter Name',   'type'=>'text',           'req'=>true],
        ],
    ],
    'qb_subtopics' => [
        'title'  => 'Subtopics',
        'icon'   => 'bi-bookmarks',
        'entity' => 'subtopics',
        'cols'   => [
            ['key' => 'subtopic_name', 'label' => 'Subtopic Name'],
            ['key' => 'chapter_name',  'label' => 'Chapter'],
        ],
        'form' => [
            ['name'=>'chapter_id',    'label'=>'Chapter',       'type'=>'select_chapters','req'=>true],
            ['name'=>'subtopic_name', 'label'=>'Subtopic Name', 'type'=>'text',           'req'=>true],
        ],
    ],
    'qb_bloom_levels' => [
        'title'  => 'Bloom Levels',
        'icon'   => 'bi-bar-chart-steps',
        'entity' => 'bloom_levels',
        'cols'   => [
            ['key' => 'bloom_name',   'label' => 'Level Name'],
            ['key' => 'description',  'label' => 'Description'],
        ],
        'form' => [
            ['name'=>'bloom_name',  'label'=>'Bloom Level', 'type'=>'text',    'req'=>true, 'help'=>'e.g. Remember, Understand, Apply'],
            ['name'=>'description', 'label'=>'Description', 'type'=>'textarea','req'=>false],
        ],
    ],
    'qb_difficulty_levels' => [
        'title'  => 'Difficulty Levels',
        'icon'   => 'bi-speedometer2',
        'entity' => 'difficulty_levels',
        'cols'   => [
            ['key' => 'difficulty_name', 'label' => 'Difficulty'],
        ],
        'form' => [
            ['name'=>'difficulty_name','label'=>'Difficulty Name','type'=>'text','req'=>true,'help'=>'e.g. Easy, Medium, Hard'],
        ],
    ],
    'qb_sections' => [
        'title'  => 'Sections',
        'icon'   => 'bi-grid',
        'entity' => 'sections',
        'cols'   => [
            ['key' => 'section_name', 'label' => 'Section Name'],
        ],
        'form' => [
            ['name'=>'section_name','label'=>'Section Name','type'=>'text','req'=>true,'help'=>'e.g. Section A, Section B'],
        ],
    ],
];

$e = $cfg[$view] ?? $cfg['qb_subjects'];
$entity  = $e['entity'];
$title   = $e['title'];
$icon    = $e['icon'];
$cols    = $e['cols'];
$fields  = $e['form'];
?>

<div class="container-fluid px-3 py-3">

  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h5 class="mb-0 fw-semibold"><i class="bi <?= $icon ?> me-2 text-primary"></i><?= $title ?></h5>
      <small class="text-muted">Manage <?= strtolower($title) ?> in the question bank</small>
    </div>
    <button class="btn btn-primary btn-sm" onclick="openAddModal()">
      <i class="bi bi-plus-lg me-1"></i> Add <?= rtrim($title, 's') ?>
    </button>
  </div>

  <!-- Table Card -->
  <div class="card adminuiux-card shadow-sm">
    <div class="card-header bg-transparent d-flex align-items-center justify-content-between py-2">
      <input type="text" id="qbSearch" class="form-control form-control-sm w-auto"
             placeholder="Search..." oninput="filterTable(this.value)" style="min-width:220px">
      <span class="text-muted small" id="qbCount"></span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="qbTable">
          <thead class="table-light">
            <tr>
              <th width="40">#</th>
              <?php foreach ($cols as $col): ?>
              <th><?= $col['label'] ?></th>
              <?php endforeach; ?>
              <th width="100" class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody id="qbTbody">
            <tr><td colspan="<?= count($cols) + 2 ?>" class="text-center py-4">
              <div class="spinner-border spinner-border-sm text-primary"></div> Loading...
            </td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="qbModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content custom-modal">
      <div class="modal-header">
        <h6 class="modal-title fw-semibold" id="qbModalTitle">Add <?= rtrim($title,'s') ?></h6>
        <button type="button" class="btn-close custom-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="qbEditId">
        <?php foreach ($fields as $f): ?>
          <div class="mb-3">
            <label class="form-label fw-medium"><?= $f['label'] ?><?= $f['req'] ? ' <span class="text-danger">*</span>' : '' ?></label>
            <?php if ($f['type'] === 'textarea'): ?>
              <textarea id="field_<?= $f['name'] ?>" class="form-control" rows="3"
                placeholder="<?= $f['label'] ?>"></textarea>
            <?php elseif ($f['type'] === 'select_subjects'): ?>
              <select id="field_<?= $f['name'] ?>" class="form-select">
                <option value="">-- Select Subject --</option>
              </select>
            <?php elseif ($f['type'] === 'select_levels'): ?>
              <select id="field_<?= $f['name'] ?>" class="form-select">
                <option value="">-- Select Level --</option>
              </select>
            <?php elseif ($f['type'] === 'select_chapters'): ?>
              <select id="field_<?= $f['name'] ?>" class="form-select">
                <option value="">-- Select Chapter --</option>
              </select>
            <?php else: ?>
              <input id="field_<?= $f['name'] ?>" type="<?= $f['type'] ?>"
                class="form-control" placeholder="<?= $f['label'] ?>">
            <?php endif; ?>
            <?php if (!empty($f['help'])): ?>
              <small class="text-muted"><?= $f['help'] ?></small>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="saveRecord()">
          <i class="bi bi-check2 me-1"></i> Save
        </button>
      </div>
    </div>
  </div>
</div>

<script>
const QB_ENTITY  = '<?= $entity ?>';
const QB_COLS    = <?= json_encode($cols) ?>;
const QB_FIELDS  = <?= json_encode($fields) ?>;
const QB_TITLE   = '<?= addslashes(rtrim($title, 's')) ?>';

let qbModal, allRows = [];

document.addEventListener('DOMContentLoaded', () => {
  qbModal = new bootstrap.Modal(document.getElementById('qbModal'));
  loadData();
  <?php if (in_array('select_subjects', array_column($fields,'type'))): ?>
  loadSelectOptions('subjects', 'field_subject_id', 'subject_id', 'subject_name');
  <?php endif; ?>
  <?php if (in_array('select_levels', array_column($fields,'type'))): ?>
  loadSelectOptions('levels', 'field_level_id', 'level_id', 'level_name');
  <?php endif; ?>
  <?php if (in_array('select_chapters', array_column($fields,'type'))): ?>
  loadSelectOptions('chapters', 'field_chapter_id', 'chapter_id', 'chapter_name');
  <?php endif; ?>
});

function loadSelectOptions(entity, elId, valKey, labelKey) {
  fetch(`ajax/ajax_qb_taxonomy.php?entity=${entity}&action=list`)
    .then(r => r.json()).then(res => {
      const sel = document.getElementById(elId);
      if (!sel || res.status !== 'success') return;
      res.data.forEach(row => {
        const opt = document.createElement('option');
        opt.value = row[valKey];
        opt.textContent = row[labelKey];
        sel.appendChild(opt);
      });
    });
}

function loadData() {
  fetch(`ajax/ajax_qb_taxonomy.php?entity=${QB_ENTITY}&action=list`)
    .then(r => r.json())
    .then(res => {
      allRows = res.status === 'success' ? res.data : [];
      renderTable(allRows);
    })
    .catch(() => {
      document.getElementById('qbTbody').innerHTML =
        `<tr><td colspan="${QB_COLS.length+2}" class="text-center text-danger py-3">Failed to load data</td></tr>`;
    });
}

function renderTable(rows) {
  const tbody = document.getElementById('qbTbody');
  document.getElementById('qbCount').textContent = `${rows.length} record${rows.length !== 1 ? 's' : ''}`;

  if (!rows.length) {
    tbody.innerHTML = `<tr><td colspan="${QB_COLS.length+2}" class="text-center text-muted py-4">
      <i class="bi bi-inbox fs-3 d-block mb-2"></i>No records yet. Click "Add" to get started.
    </td></tr>`;
    return;
  }

  tbody.innerHTML = rows.map((row, i) => `
    <tr>
      <td class="text-muted small">${i + 1}</td>
      ${QB_COLS.map(c => `<td>${row[c.key] ?? '<span class="text-muted">—</span>'}</td>`).join('')}
      <td class="text-end">
        <button class="btn btn-sm btn-outline-primary me-1 py-0 px-2" onclick='editRecord(${JSON.stringify(row)})'>
          <i class="bi bi-pencil"></i>
        </button>
        <button class="btn btn-sm btn-outline-danger py-0 px-2" onclick="deleteRecord(${row[Object.keys(row)[0]]})">
          <i class="bi bi-trash"></i>
        </button>
      </td>
    </tr>`).join('');
}

function filterTable(q) {
  const filtered = q
    ? allRows.filter(r => Object.values(r).some(v => String(v).toLowerCase().includes(q.toLowerCase())))
    : allRows;
  renderTable(filtered);
}

function openAddModal() {
  document.getElementById('qbModalTitle').textContent = `Add ${QB_TITLE}`;
  document.getElementById('qbEditId').value = '';
  QB_FIELDS.forEach(f => {
    const el = document.getElementById(`field_${f.name}`);
    if (el) el.value = '';
  });
  qbModal.show();
}

function editRecord(row) {
  document.getElementById('qbModalTitle').textContent = `Edit ${QB_TITLE}`;
  document.getElementById('qbEditId').value = row[Object.keys(row)[0]];
  QB_FIELDS.forEach(f => {
    const el = document.getElementById(`field_${f.name}`);
    if (el) el.value = row[f.name] ?? '';
  });
  qbModal.show();
}

function saveRecord() {
  const id = document.getElementById('qbEditId').value;
  const data = { entity: QB_ENTITY, action: id ? 'update' : 'create', id };
  let valid = true;

  QB_FIELDS.forEach(f => {
    const el = document.getElementById(`field_${f.name}`);
    if (!el) return;
    data[f.name] = el.value.trim();
    if (f.req && !data[f.name]) {
      el.classList.add('is-invalid'); valid = false;
    } else {
      el.classList.remove('is-invalid');
    }
  });

  if (!valid) return Swal.fire('Validation', 'Please fill in all required fields.', 'warning');

  Swal.fire({ title: 'Saving...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

  fetch('ajax/ajax_qb_taxonomy.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  })
  .then(r => r.json())
  .then(res => {
    Swal.close();
    if (res.status === 'success') {
      qbModal.hide();
      Swal.fire({ icon: 'success', title: 'Saved', text: res.message, timer: 1400, showConfirmButton: false });
      loadData();
    } else {
      Swal.fire('Error', res.message, 'error');
    }
  })
  .catch(() => Swal.fire('Error', 'Request failed', 'error'));
}

function deleteRecord(id) {
  Swal.fire({
    title: 'Delete this record?',
    text: 'This cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    confirmButtonText: 'Yes, delete'
  }).then(r => {
    if (!r.isConfirmed) return;
    fetch('ajax/ajax_qb_taxonomy.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ entity: QB_ENTITY, action: 'delete', id })
    })
    .then(r => r.json())
    .then(res => {
      if (res.status === 'success') {
        Swal.fire({ icon: 'success', title: 'Deleted', timer: 1200, showConfirmButton: false });
        loadData();
      } else {
        Swal.fire('Error', res.message, 'error');
      }
    });
  });
}
</script>
