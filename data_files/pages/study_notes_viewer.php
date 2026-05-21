<?php
require_once __DIR__ . '/../config/url_crypt_config.php';
$lesson_id = intval($_GET['lesson_id'] ?? 0);
$course_id = decryptURLId($_GET['course_id'] ?? '', ctx: 'course') ?? 0;

if (!$lesson_id) {
    echo '<div class="container py-5 text-center text-muted">Invalid lesson.</div>'; exit;
}

$lesson  = $db->query("SELECT * FROM tbl_course_chapter_lessons WHERE id=$lesson_id")->fetch_assoc();
$chapter = $lesson ? $db->query("SELECT chapter_title FROM tbl_course_chapters WHERE id={$lesson['chapter_id']}")->fetch_assoc() : null;
$course  = $course_id ? $db->query("SELECT title FROM tbl_courses WHERE id=$course_id")->fetch_assoc() : null;
?>

<style>
.snote-card { cursor: pointer; transition: box-shadow .15s ease, border-color .15s ease; }
.snote-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08) !important; }
.snote-card .snote-answer { display: none; white-space: pre-wrap; line-height: 1.7; }
.snote-card.open .snote-answer { display: block; }
.snote-card .snote-chevron { transition: transform .2s ease; }
.snote-card.open .snote-chevron { transform: rotate(180deg); }
.snote-card.important { border-left: 3px solid #f59e0b !important; }
.bookmark-btn.active { color: #f59e0b !important; }
.snote-highlight { background: #fef9c3; border-radius: 2px; padding: 0 2px; }
</style>

<div class="container-fluid px-3 pt-3 pb-5">

    <!-- Header -->
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <nav aria-label="breadcrumb" class="mb-1">
                <ol class="breadcrumb small mb-0">
                    <li class="breadcrumb-item"><a href="?view=learning-student-home">Home</a></li>
                    <?php if ($course): ?>
                    <li class="breadcrumb-item"><a href="?view=view_course_details&course_id=<?= $course_id ? encryptURLId($course_id, ctx: 'course') : '' ?>"><?= htmlspecialchars($course['title']) ?></a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active">Study Notes</li>
                </ol>
            </nav>
            <h5 class="mb-1 fw-semibold"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Study Notes</h5>
            <p class="text-muted small mb-0">
                <?php if ($chapter): ?><span class="me-2"><i class="bi bi-collection me-1"></i><?= htmlspecialchars($chapter['chapter_title']) ?></span><?php endif; ?>
                <span><i class="bi bi-play-circle me-1"></i><?= htmlspecialchars($lesson['lesson_title'] ?? '') ?></span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="expandAll()"><i class="bi bi-arrows-expand me-1"></i>Expand All</button>
            <button class="btn btn-sm btn-outline-secondary" onclick="collapseAll()"><i class="bi bi-arrows-collapse me-1"></i>Collapse All</button>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="row g-2 mb-4 align-items-center">
        <div class="col-12 col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input id="searchInput" type="text" class="form-control border-start-0 ps-0" placeholder="Search questions or answers…" oninput="applyFilters()">
                <button class="btn btn-outline-secondary" onclick="document.getElementById('searchInput').value='';applyFilters()" title="Clear">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                <button id="tabAll" class="btn btn-primary btn-sm" onclick="setTab('all')">
                    <i class="bi bi-grid-3x3-gap me-1"></i>All <span id="countAll" class="badge bg-white text-primary ms-1">0</span>
                </button>
                <button id="tabBookmarked" class="btn btn-outline-primary btn-sm" onclick="setTab('bookmarked')">
                    <i class="bi bi-bookmark-fill me-1"></i>Saved <span id="countBookmarked" class="badge bg-primary ms-1">0</span>
                </button>
                <button id="tabImportant" class="btn btn-outline-warning btn-sm" onclick="setTab('important')">
                    <i class="bi bi-star-fill me-1"></i>Important <span id="countImportant" class="badge bg-warning text-dark ms-1">0</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Notes grid -->
    <div id="notesList" class="row g-3"></div>

    <!-- Empty state -->
    <div id="emptyState" class="text-center py-5 d-none">
        <i class="bi bi-journal-x fs-1 text-muted opacity-40 d-block mb-3"></i>
        <p id="emptyMsg" class="text-muted">No study notes available for this lesson yet.</p>
    </div>

</div>

<script>
const LESSON_ID = <?= $lesson_id ?>;
const AJAX_URL  = 'ajax/ajax_study_notes.php';

let allNotes   = [];
let activeTab  = 'all';
let searchTerm = '';

// ── Load ───────────────────────────────────────────────────────────────────
async function loadNotes() {
    const res = await fetch(`${AJAX_URL}?action=list&lesson_id=${LESSON_ID}`).then(r => r.json());
    allNotes = res.data || [];
    updateCounts();
    applyFilters();
}

function updateCounts() {
    document.getElementById('countAll').textContent       = allNotes.length;
    document.getElementById('countBookmarked').textContent = allNotes.filter(n => n.bookmarked == 1).length;
    document.getElementById('countImportant').textContent  = allNotes.filter(n => n.is_important == 1).length;
}

// ── Filter & render ────────────────────────────────────────────────────────
function applyFilters() {
    searchTerm = document.getElementById('searchInput').value.trim().toLowerCase();
    let filtered = allNotes.filter(n => {
        if (activeTab === 'bookmarked' && !n.bookmarked) return false;
        if (activeTab === 'important'  && !n.is_important) return false;
        if (searchTerm) {
            return n.question.toLowerCase().includes(searchTerm) || n.answer.toLowerCase().includes(searchTerm);
        }
        return true;
    });
    renderNotes(filtered);
}

function setTab(tab) {
    activeTab = tab;
    ['all','bookmarked','important'].forEach(t => {
        const btn = document.getElementById('tab' + t.charAt(0).toUpperCase() + t.slice(1));
        btn.className = btn.className.replace(/btn-outline-(\w+)|btn-(\w+)(?=\s|$)/g, '').trim();
    });
    document.getElementById('tabAll').className        = 'btn btn-sm ' + (tab==='all'        ? 'btn-primary'         : 'btn-outline-primary');
    document.getElementById('tabBookmarked').className = 'btn btn-sm ' + (tab==='bookmarked' ? 'btn-primary'         : 'btn-outline-primary');
    document.getElementById('tabImportant').className  = 'btn btn-sm ' + (tab==='important'  ? 'btn-warning text-dark': 'btn-outline-warning');
    applyFilters();
}

function highlight(text, term) {
    if (!term) return escHtml(text);
    const safe = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    return escHtml(text).replace(new RegExp(`(${safe})`, 'gi'), '<mark class="snote-highlight">$1</mark>');
}

function renderNotes(notes) {
    const list  = document.getElementById('notesList');
    const empty = document.getElementById('emptyState');
    const msg   = document.getElementById('emptyMsg');

    if (!notes.length) {
        list.innerHTML = '';
        empty.classList.remove('d-none');
        msg.textContent = searchTerm ? 'No notes match your search.' :
                          activeTab === 'bookmarked' ? 'No saved notes yet. Bookmark notes to find them here.' :
                          activeTab === 'important'  ? 'No important notes in this lesson.' :
                          'No study notes available for this lesson yet.';
        return;
    }
    empty.classList.add('d-none');

    list.innerHTML = notes.map((n, i) => `
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm snote-card ${n.is_important ? 'important' : ''}" data-id="${n.id}" onclick="toggleCard(this, event)">
                <div class="card-body py-3 px-4">
                    <div class="d-flex align-items-start gap-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1 rounded-2 flex-shrink-0 mt-1">${i + 1}</span>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center gap-1 mb-1 flex-wrap">
                                ${n.is_important ? '<span class="badge bg-warning text-dark small"><i class="bi bi-star-fill me-1"></i>Important</span>' : ''}
                                <span class="badge bg-secondary bg-opacity-10 text-secondary small">${n.language}</span>
                            </div>
                            <p class="fw-semibold mb-0 lh-sm">${highlight(n.question, searchTerm)}</p>
                            <div class="snote-answer mt-3 text-muted small">${highlight(n.answer, searchTerm)}</div>
                        </div>
                        <div class="d-flex flex-column align-items-center gap-2 flex-shrink-0 ms-1">
                            <button class="btn btn-sm btn-link p-0 bookmark-btn ${n.bookmarked ? 'active' : 'text-muted'}"
                                    onclick="toggleBookmark(event, ${n.id})" title="${n.bookmarked ? 'Remove bookmark' : 'Bookmark'}">
                                <i class="bi ${n.bookmarked ? 'bi-bookmark-fill' : 'bi-bookmark'}"></i>
                            </button>
                            <i class="bi bi-chevron-down snote-chevron text-muted" style="font-size:.75rem"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

// ── Card expand / collapse ─────────────────────────────────────────────────
function toggleCard(card, e) {
    if (e.target.closest('.bookmark-btn')) return;
    card.classList.toggle('open');
}

function expandAll()  { document.querySelectorAll('.snote-card').forEach(c => c.classList.add('open')); }
function collapseAll(){ document.querySelectorAll('.snote-card').forEach(c => c.classList.remove('open')); }

// ── Bookmark ───────────────────────────────────────────────────────────────
async function toggleBookmark(e, noteId) {
    e.stopPropagation();
    const btn = e.currentTarget;
    const res = await fetch(AJAX_URL, {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ action:'toggle_bookmark', note_id: noteId })
    }).then(r => r.json());

    if (res.status === 'success') {
        const note = allNotes.find(n => n.id == noteId);
        if (note) note.bookmarked = res.bookmarked ? 1 : 0;
        const icon = btn.querySelector('i');
        if (res.bookmarked) {
            btn.classList.add('active');
            icon.className = 'bi bi-bookmark-fill';
        } else {
            btn.classList.remove('active');
            icon.className = 'bi bi-bookmark';
        }
        updateCounts();
        if (activeTab === 'bookmarked') applyFilters();
    }
}

function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

loadNotes();
</script>
