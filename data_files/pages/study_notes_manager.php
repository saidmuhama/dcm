<?php
$lesson_id  = intval($_GET['lesson_id']  ?? 0);
$course_id  = intval($_GET['course_id']  ?? 0);
$chapter_id = intval($_GET['chapter_id'] ?? 0);

if (!$lesson_id || !$course_id) {
    echo '<div class="container py-5 text-center text-muted">Invalid lesson.</div>'; exit;
}

$lesson  = $db->query("SELECT * FROM tbl_course_chapter_lessons WHERE id=$lesson_id")->fetch_assoc();
$course  = $db->query("SELECT title FROM tbl_courses WHERE id=$course_id")->fetch_assoc();
$chapter = $db->query("SELECT chapter_title FROM tbl_course_chapters WHERE id=$chapter_id")->fetch_assoc();

if (!$lesson || $lesson['instructor_id'] != $_SESSION['usr_code']) {
    echo '<div class="container py-5 text-center text-danger">Access denied.</div>'; exit;
}
?>

<div class="container-fluid px-3 pt-3 pb-5">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="?view=3002">Home</a></li>
            <li class="breadcrumb-item"><a href="?view=course_contents_management&course_id=<?= $course_id ?>">
                <?= htmlspecialchars($course['title'] ?? 'Course') ?>
            </a></li>
            <li class="breadcrumb-item active">Q&amp;A Notes</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h5 class="mb-1 fw-semibold"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Q&amp;A Study Notes</h5>
            <p class="text-muted small mb-0">
                <span class="me-2"><i class="bi bi-collection me-1"></i><?= htmlspecialchars($chapter['chapter_title'] ?? '') ?></span>
                <span><i class="bi bi-play-circle me-1"></i><?= htmlspecialchars($lesson['lesson_title']) ?></span>
            </p>
        </div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#noteModal" onclick="openAddModal()">
            <i class="bi bi-plus-lg me-1"></i> Add Q&amp;A Note
        </button>
    </div>

    <!-- Notes list -->
    <div id="notesList"></div>

    <!-- Empty state -->
    <div id="emptyState" class="text-center py-5 d-none">
        <i class="bi bi-journal-x fs-1 text-muted opacity-50"></i>
        <p class="text-muted mt-3">No Q&amp;A notes yet. Add the first one!</p>
    </div>

</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content custom-modal">
            <div class="modal-header">
                <h6 class="modal-title fw-semibold" id="noteModalTitle"><i class="bi bi-patch-question-fill text-primary me-2"></i>Add Q&amp;A Note</h6>
                <button type="button" class="btn-close custom-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="noteId" value="">
                <div class="mb-3">
                    <label class="form-label fw-medium">Question <span class="text-danger">*</span></label>
                    <textarea id="noteQuestion" class="form-control" rows="2" placeholder="e.g. What is Hajj?"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Answer <span class="text-danger">*</span></label>
                    <textarea id="noteAnswer" class="form-control" rows="5" placeholder="Write the full answer here..."></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label fw-medium">Language</label>
                        <select id="noteLanguage" class="form-select">
                            <option value="EN">English (EN)</option>
                            <option value="SW">Swahili (SW)</option>
                            <option value="AR">Arabic (AR)</option>
                        </select>
                    </div>
                    <div class="col-sm-6 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="noteImportant">
                            <label class="form-check-label" for="noteImportant">
                                <i class="bi bi-star-fill text-warning me-1"></i> Mark as Important
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="saveNoteBtn" onclick="saveNote()">
                    <i class="bi bi-check-lg me-1"></i> Save Note
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const LESSON_ID  = <?= $lesson_id ?>;
const CHAPTER_ID = <?= $chapter_id ?>;
const COURSE_ID  = <?= $course_id ?>;
const AJAX_URL   = 'ajax/ajax_study_notes.php';

let notes = [];

// ── Load notes ─────────────────────────────────────────────────────────────
async function loadNotes() {
    const res = await fetch(`${AJAX_URL}?action=list&lesson_id=${LESSON_ID}`).then(r => r.json());
    notes = res.data || [];
    renderNotes();
}

function renderNotes() {
    const list = document.getElementById('notesList');
    const empty = document.getElementById('emptyState');

    if (!notes.length) {
        list.innerHTML = '';
        empty.classList.remove('d-none');
        return;
    }
    empty.classList.add('d-none');

    list.innerHTML = notes.map((n, i) => `
        <div class="card mb-3 border-0 shadow-sm ${n.is_important ? 'border-start border-warning border-3' : ''}" data-id="${n.id}">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold fs-6 px-2 py-1 rounded-2 flex-shrink-0">${i + 1}</span>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            ${n.is_important ? '<span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>Important</span>' : ''}
                            <span class="badge bg-secondary bg-opacity-15 text-secondary">${n.language}</span>
                        </div>
                        <p class="fw-semibold mb-1">${escHtml(n.question)}</p>
                        <p class="text-muted small mb-0" style="white-space:pre-wrap">${escHtml(n.answer)}</p>
                    </div>
                    <div class="d-flex gap-1 flex-shrink-0">
                        <button class="btn btn-sm btn-outline-secondary" onclick="openEditModal(${n.id})" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteNote(${n.id})" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

// ── Modal helpers ──────────────────────────────────────────────────────────
function openAddModal() {
    document.getElementById('noteModalTitle').innerHTML = '<i class="bi bi-patch-question-fill text-primary me-2"></i>Add Q&amp;A Note';
    document.getElementById('noteId').value = '';
    document.getElementById('noteQuestion').value = '';
    document.getElementById('noteAnswer').value = '';
    document.getElementById('noteLanguage').value = 'EN';
    document.getElementById('noteImportant').checked = false;
}

function openEditModal(id) {
    const n = notes.find(x => x.id == id);
    if (!n) return;
    document.getElementById('noteModalTitle').innerHTML = '<i class="bi bi-pencil-fill text-primary me-2"></i>Edit Q&amp;A Note';
    document.getElementById('noteId').value = n.id;
    document.getElementById('noteQuestion').value = n.question;
    document.getElementById('noteAnswer').value = n.answer;
    document.getElementById('noteLanguage').value = n.language;
    document.getElementById('noteImportant').checked = n.is_important == 1;
    new bootstrap.Modal(document.getElementById('noteModal')).show();
}

// ── Save ───────────────────────────────────────────────────────────────────
async function saveNote() {
    const question  = document.getElementById('noteQuestion').value.trim();
    const answer    = document.getElementById('noteAnswer').value.trim();
    const language  = document.getElementById('noteLanguage').value;
    const important = document.getElementById('noteImportant').checked ? 1 : 0;
    const id        = document.getElementById('noteId').value;

    if (!question || !answer) {
        Swal.fire('Required', 'Please fill in both question and answer.', 'warning'); return;
    }

    const btn = document.getElementById('saveNoteBtn');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

    const res = await fetch(AJAX_URL, {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ action:'save', id, lesson_id:LESSON_ID, chapter_id:CHAPTER_ID, course_id:COURSE_ID, question, answer, language, is_important:important })
    }).then(r => r.json());

    btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Save Note';

    if (res.status === 'success') {
        bootstrap.Modal.getInstance(document.getElementById('noteModal'))?.hide();
        loadNotes();
    } else {
        Swal.fire('Error', res.message, 'error');
    }
}

// ── Delete ─────────────────────────────────────────────────────────────────
async function deleteNote(id) {
    const result = await Swal.fire({
        title: 'Delete this note?',
        text: 'This cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-secondary' },
        buttonsStyling: false
    });
    if (!result.isConfirmed) return;

    const res = await fetch(AJAX_URL, {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ action:'delete', id })
    }).then(r => r.json());

    if (res.status === 'success') {
        loadNotes();
    } else {
        Swal.fire('Error', res.message, 'error');
    }
}

function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

loadNotes();
</script>
