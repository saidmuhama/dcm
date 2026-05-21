<?php
$course_id   = (int)($_GET['course_id'] ?? 0);
if (!$course_id) { echo '<p class="text-center p-4">Invalid course.</p>'; return; }

$courseTitle = App::getWhatFromWHere('title',         'tbl_courses', 'id', $course_id);
$courseOwner = App::getWhatFromWHere('instructor_id', 'tbl_courses', 'id', $course_id);
$library_id  = App::getWhatFromWHere('library_id',   'tbl_courses', 'id', $course_id);
$library_key = App::getBunnyLibraryKey($library_id, App::getBunnyNetApiKey());

if ($courseOwner != ($_SESSION['usr_code'] ?? '')) { ?>
    <script>window.location.href='../data_files/?view=3002';</script>
<?php exit; }
?>

<style>
/* ── Hero ── */
.ccm-hero {
    background: linear-gradient(135deg,#1a1a2e 0%,#16213e 45%,#0f3460 100%);
    padding: 1.75rem 0 3.5rem; position: relative; overflow: hidden;
}
.ccm-hero::before {
    content:''; position:absolute; inset:0; pointer-events:none;
    background: radial-gradient(circle at 10% 60%,rgba(99,102,241,.18) 0%,transparent 55%),
                radial-gradient(circle at 85% 20%,rgba(168,85,247,.13) 0%,transparent 50%);
}
.ccm-hero::after {
    content:''; position:absolute; inset:0; pointer-events:none;
    background-image:url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='20' cy='20' r='1.5' fill='%23fff' fill-opacity='.03'/%3E%3C/svg%3E");
}

/* ── Canvas ── */
.ccm-canvas {
    max-width: 1400px; margin: -2rem auto 2rem;
    padding: 0 1rem; position: relative; z-index: 10;
}
.ccm-grid {
    display: grid;
    grid-template-columns: 340px 1fr;
    gap: 1.25rem; align-items: start;
}
@media(max-width:991px){ .ccm-grid { grid-template-columns: 1fr; } }

/* ── Panel card ── */
.panel-card {
    background: #fff; border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,.08);
    border: 1px solid rgba(0,0,0,.05);
    overflow: hidden;
}
.panel-header {
    padding: .85rem 1rem; border-bottom: 1px solid #f1f5f9;
    display: flex; align-items: center; gap: .6rem;
    background: linear-gradient(135deg,#fafbff,#f5f7ff);
}
.panel-title { font-size: .88rem; font-weight: 700; color: #1e293b; }

/* ── Chapter accordion ── */
.ch-item { border-bottom: 1px solid #f1f5f9; }
.ch-item:last-child { border-bottom: none; }
.ch-toggle {
    display: flex; align-items: center; gap: .6rem;
    padding: .75rem 1rem; cursor: pointer;
    background: none; border: none; width: 100%; text-align: left;
    font-size: .84rem; font-weight: 600; color: #1e293b;
    transition: background .15s;
}
.ch-toggle:hover { background: #f8f9ff; }
.ch-toggle .ch-arrow { margin-left: auto; color: #94a3b8; transition: transform .2s; font-size: .75rem; }
.ch-toggle.collapsed .ch-arrow { transform: rotate(-90deg); }
.ch-badge { background: #eef2ff; color: #6366f1; border-radius: 20px;
    font-size: .68rem; padding: .18rem .5rem; font-weight: 600; flex-shrink: 0; }
.ch-drag-handle { color: #cbd5e1; cursor: grab; font-size: .85rem; flex-shrink: 0; }

.lesson-list { padding: .25rem .5rem .5rem 2rem; margin: 0; list-style: none; }
.lesson-item {
    display: flex; align-items: center; gap: .5rem;
    padding: .55rem .65rem; border-radius: 9px; cursor: pointer;
    font-size: .82rem; color: #475569; transition: all .15s; margin-bottom: .2rem;
}
.lesson-item:hover { background: #f8f9ff; color: #6366f1; }
.lesson-item.active { background: #eef2ff; color: #6366f1; font-weight: 600; }
.lesson-item .l-drag { color: #cbd5e1; cursor: grab; font-size: .78rem; flex-shrink: 0; }
.lesson-item .l-type { font-size: .75rem; color: #94a3b8; flex-shrink: 0; }
.lesson-item .l-free { background: #dcfce7; color: #16a34a; border-radius: 4px;
    font-size: .62rem; padding: .1rem .3rem; font-weight: 700; flex-shrink: 0; }

.add-lesson-row {
    padding: .4rem .75rem .75rem 2.25rem;
}
.btn-add-lesson {
    font-size: .75rem; font-weight: 600; color: #6366f1;
    background: none; border: 1px dashed #c7d2fe; border-radius: 8px;
    padding: .35rem .75rem; cursor: pointer; transition: all .15s;
    display: flex; align-items: center; gap: .3rem;
}
.btn-add-lesson:hover { background: #eef2ff; border-color: #6366f1; }

/* ── Right panel ── */
.right-panel { position: sticky; top: 1rem; }
.rp-welcome {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 3rem 2rem; text-align: center; color: #94a3b8;
}
.rp-welcome .wi { font-size: 3rem; margin-bottom: 1rem; opacity: .4; }

/* ── Lesson detail ── */
.ld-section-title {
    font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .7px;
    color: #94a3b8; margin: 1.25rem 0 .75rem;
    display: flex; align-items: center; gap: .5rem;
}
.ld-section-title::after { content:''; flex:1; height:1px; background:#f1f5f9; }
.form-label-sm { font-size: .78rem; font-weight: 600; color: #475569; margin-bottom: .3rem; }
.form-control-sm2 {
    border-radius: 10px; border-color: #e2e8f0; font-size: .85rem;
    padding: .6rem .85rem;
}
.form-control-sm2:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
.switch-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: .7rem 0; border-bottom: 1px solid #f1f5f9;
}
.switch-row:last-child { border-bottom: none; }
.switch-lbl { font-size: .84rem; color: #334155; }
.switch-sub { font-size: .72rem; color: #94a3b8; }

/* ── Media preview frame ── */
.media-frame {
    width: 100%; height: 240px; border: none; border-radius: 12px;
    background: #000; display: block;
}
.media-thumb { width: 100%; height: 180px; object-fit: cover; border-radius: 12px; }

/* ── Status badge ── */
.status-chip { border-radius: 20px; padding: .28rem .75rem;
    font-size: .72rem; font-weight: 700; display: inline-flex; align-items: center; gap: .3rem; }
.status-chip.active   { background: #dcfce7; color: #15803d; }
.status-chip.inactive { background: #fee2e2; color: #dc2626; }
.status-chip.draft    { background: #fef9c3; color: #854d0e; }

/* ── Drag mirror ── */
.gu-mirror { opacity:.85; background:#fff; border:1px solid #6366f1;
    border-radius:8px; box-shadow:0 4px 16px rgba(99,102,241,.2);
    list-style:none; padding:8px 12px; cursor:grabbing; }
.lesson-list.gu-over { background:rgba(99,102,241,.05); border-radius:8px;
    outline:2px dashed #6366f1; outline-offset:-2px; }
.gu-transit { opacity:.3; }

/* dark mode */
@media(prefers-color-scheme:dark){
    .panel-card { background:#1e293b; border-color:rgba(255,255,255,.06); }
    .panel-header { background:linear-gradient(135deg,#1e293b,#1a2440); border-color:rgba(255,255,255,.06); }
    .panel-title { color:#e2e8f0; }
    .ch-toggle { color:#e2e8f0; }
    .ch-toggle:hover { background:rgba(99,102,241,.08); }
    .ch-item { border-color:rgba(255,255,255,.06); }
    .lesson-item { color:#94a3b8; }
    .lesson-item:hover,.lesson-item.active { background:rgba(99,102,241,.12); color:#a5b4fc; }
    .ld-section-title::after { background:rgba(255,255,255,.06); }
    .switch-row { border-color:rgba(255,255,255,.06); }
    .switch-lbl { color:#e2e8f0; }
    .form-control,.form-select { background:#0f172a; border-color:#334155; color:#e2e8f0; }
}
</style>

<!-- ══════════════════════ HERO ══════════════════════ -->
<div class="ccm-hero">
    <div class="container-xl position-relative" style="z-index:2">
        <nav class="mb-2">
            <ol class="breadcrumb mb-0" style="font-size:.76rem">
                <li class="breadcrumb-item"><a href="../data_files/?view=3002" class="text-white-50 text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" style="color:rgba(255,255,255,.5)">Course Management</li>
            </ol>
        </nav>
        <div class="d-flex flex-wrap align-items-center gap-3 justify-content-between">
            <div>
                <h4 class="text-white fw-bold mb-1" style="max-width:640px"><?= htmlspecialchars($courseTitle ?? '') ?></h4>
                <div class="d-flex align-items-center gap-2">
                    <span id="heroStatusChip" class="status-chip draft"><i class="bi bi-circle-fill" style="font-size:.45rem"></i> —</span>
                    <span class="text-white-50 small" id="heroLessonCount"></span>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="../data_files/?view=view_course_details&course_id=<?= $course_id ?>" target="_blank"
                   class="btn btn-sm btn-outline-light" style="border-radius:9px;font-size:.8rem">
                    <i class="bi bi-eye me-1"></i>Preview
                </a>
                <button onclick="showCourseSettings()" class="btn btn-sm btn-outline-light" style="border-radius:9px;font-size:.8rem">
                    <i class="bi bi-gear me-1"></i>Settings
                </button>
                <button id="statusToggleBtn" onclick="handlePublishClick()"
                    class="btn btn-sm" style="border-radius:9px;font-size:.8rem;background:#fff;color:#1e293b;font-weight:600">
                    <i class="bi bi-send me-1" id="statusToggleIcon"></i><span id="statusText">Submit for Review</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════ CANVAS ══════════════════════ -->
<div class="ccm-canvas">
    <div class="ccm-grid">

        <!-- ── LEFT: Chapter list ── -->
        <div class="panel-card">
            <div class="panel-header">
                <i class="bi bi-collection-play" style="color:#6366f1;font-size:1rem"></i>
                <span class="panel-title">Course Chapters</span>
                <div class="ms-auto d-flex gap-1">
                    <button onclick="toggleAllChapters()" id="toggleAllBtn"
                        class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;border-radius:7px;padding:.25rem .6rem">
                        <i class="bi bi-chevron-bar-contract" id="toggleAllIcon"></i>
                    </button>
                    <button data-bs-toggle="modal" data-bs-target="#createChapterModal"
                        class="btn btn-sm" style="font-size:.72rem;border-radius:7px;padding:.25rem .65rem;background:#6366f1;color:#fff;border:none">
                        <i class="bi bi-plus-lg me-1"></i>Chapter
                    </button>
                </div>
            </div>
            <div id="chapterAccordion" style="max-height:calc(100vh - 200px);overflow-y:auto">
                <!-- skeleton -->
                <div class="p-3" id="chapterSkeleton">
                    <?php for($i=0;$i<3;$i++): ?>
                    <div style="height:44px;border-radius:8px;background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:sk 1.5s infinite;margin-bottom:.5rem"></div>
                    <?php endfor; ?>
                </div>
            </div>
            <style>@keyframes sk{0%{background-position:200% 0}100%{background-position:-200% 0}}</style>
        </div>

        <!-- ── RIGHT: Lesson detail ── -->
        <div class="panel-card right-panel" id="rightPanelCard">
            <div id="chapterContents">
                <div class="rp-welcome">
                    <div class="wi"><i class="bi bi-play-circle"></i></div>
                    <h6 class="fw-semibold" style="color:#334155">Select a lesson to edit</h6>
                    <p class="small">Click any lesson from the left panel to view or edit it, or add a new lesson to a chapter.</p>
                    <button onclick="showCourseSettings()" class="btn btn-sm mt-1"
                        style="background:#eef2ff;color:#6366f1;border-radius:9px;font-size:.8rem;font-weight:600;border:none">
                        <i class="bi bi-gear me-1"></i>Open Course Settings
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ══════════════════════ EDIT LESSON TITLE MODAL ══════════════════════ -->
<div class="modal fade" id="editLessonTitleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h6 class="fw-bold mb-0">Edit Lesson Title</h6>
                    <small class="text-muted">Update the lesson name</small>
                </div>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label" style="font-size:.78rem;font-weight:600;color:#475569">New Title</label>
                <input type="text" id="new_lesson_title" class="form-control" placeholder="Lesson title" style="border-radius:10px">
                <input type="hidden" id="edit_lesson_id">
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-light" data-bs-dismiss="modal" style="border-radius:9px">Cancel</button>
                <button id="saveLessonTitleBtn" class="btn" style="background:#6366f1;color:#fff;border-radius:9px">
                    <i class="bi bi-check-lg me-1"></i>Update
                </button>
            </div>
        </div>
    </div>
</div>

<script>
/* ════════════════════════════════════════════════════════════
   GLOBALS
═══════════════════════════════════════════════════════════════ */
const COURSE_ID = <?= (int)$course_id ?>;
let _activeLessonId = null;
let _lessonDragging = false;

/* ════════════════════════════════════════════════════════════
   CHAPTER LOADING  (global so save/delete can refresh it)
═══════════════════════════════════════════════════════════════ */
window.loadChapters = function(cid){
    cid = cid || COURSE_ID;
    fetch('ajax/ajax_get_chapters_lessons.php?course_id='+cid)
    .then(r=>r.json())
    .then(chapters=>{
        const el = document.getElementById('chapterAccordion');
        document.getElementById('chapterSkeleton')?.remove();

        if(!chapters.length){
            el.innerHTML = `
            <div class="p-4 text-center text-muted small">
                <i class="bi bi-folder-x d-block mb-2" style="font-size:1.8rem;opacity:.35"></i>
                No chapters yet. Click <strong>+ Chapter</strong> to get started.
            </div>`;
            document.getElementById('heroLessonCount').textContent = '';
            return;
        }

        let totalLessons = 0;
        let html = '';
        chapters.forEach((ch,i)=>{
            const colId = 'ch-col-'+i;
            const lessonCount = (ch.lessons||[]).length;
            totalLessons += lessonCount;
            html += `
            <div class="ch-item" data-chapter-id="${ch.id}">
                <button class="ch-toggle" data-target="${colId}" onclick="toggleChapter(this,'${colId}')">
                    <i class="bi bi-grip-vertical ch-drag-handle"></i>
                    <i class="bi bi-folder2-open" style="color:#6366f1;font-size:.85rem"></i>
                    <span style="flex:1;text-align:left;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escHtml(ch.chapter_title)}</span>
                    <span class="ch-badge">${lessonCount}</span>
                    <i class="bi bi-chevron-down ch-arrow"></i>
                </button>
                <div id="${colId}" class="ch-collapse">
                    <ul class="lesson-list lesson-sortable-list" id="lessonList-${ch.id}" data-chapter-id="${ch.id}">`;
            if(lessonCount){
                ch.lessons.forEach(l=>{
                    const typeIcon = l.content_type==='audio' ? 'bi-music-note' : l.content_type==='pdf' ? 'bi-file-pdf' : 'bi-play-circle';
                    html += `
                        <li class="lesson-item" data-lesson-id="${l.id}" onclick="handleLessonClick(${l.id},this)">
                            <i class="bi bi-grip-vertical l-drag"></i>
                            <i class="bi ${typeIcon} l-type"></i>
                            <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escHtml(l.lesson_title)}</span>
                            ${l.isFreePreviewLesson==1?'<span class="l-free">FREE</span>':''}
                        </li>`;
                });
            } else {
                html += `<li class="list-group-item text-muted small empty-chapter-placeholder" style="list-style:none;padding:.4rem .65rem;font-size:.78rem">No lessons yet</li>`;
            }
            html += `</ul>
                    <div class="add-lesson-row">
                        <button class="btn-add-lesson" onclick="showLessonInputForm(${ch.id})">
                            <i class="bi bi-plus-lg"></i> Add Lesson
                        </button>
                    </div>
                </div>
            </div>`;
        });

        el.innerHTML = html;
        document.getElementById('heroLessonCount').textContent = totalLessons+' lesson'+(totalLessons!==1?'s':'');

        // Restore active lesson highlight
        if(_activeLessonId){
            const li = el.querySelector(`[data-lesson-id="${_activeLessonId}"]`);
            if(li) li.classList.add('active');
        }
        initLessonDragDrop();
    })
    .catch(console.error);
};

/* ════════════════════════════════════════════════════════════
   CHAPTER TOGGLE
═══════════════════════════════════════════════════════════════ */
function toggleChapter(btn, colId){
    const col = document.getElementById(colId);
    const open = col.style.display !== 'none';
    col.style.display = open ? 'none' : '';
    btn.classList.toggle('collapsed', open);
}

function toggleAllChapters(){
    const cols   = document.querySelectorAll('.ch-collapse');
    const btns   = document.querySelectorAll('.ch-toggle');
    const icon   = document.getElementById('toggleAllIcon');
    const allOpen = Array.from(cols).every(c=>c.style.display!=='none');
    cols.forEach(c=>{ c.style.display = allOpen ? 'none' : ''; });
    btns.forEach(b=>{ b.classList.toggle('collapsed', allOpen); });
    icon.className = allOpen ? 'bi bi-chevron-bar-expand' : 'bi bi-chevron-bar-contract';
}

/* ════════════════════════════════════════════════════════════
   LESSON CLICK  (guard against drag-end firing click)
═══════════════════════════════════════════════════════════════ */
function handleLessonClick(lessonId, el){
    if(_lessonDragging) return;
    document.querySelectorAll('.lesson-item').forEach(x=>x.classList.remove('active'));
    el.classList.add('active');
    _activeLessonId = lessonId;
    showLessonContents(lessonId);
}

/* ════════════════════════════════════════════════════════════
   DRAG & DROP
═══════════════════════════════════════════════════════════════ */
function initLessonDragDrop(){
    const lists = Array.from(document.querySelectorAll('.lesson-sortable-list'));
    if(!lists.length || typeof dragula === 'undefined') return;

    const drake = dragula(lists,{
        moves: el=>el.classList.contains('lesson-item'),
        accepts: (el,target)=>target.classList.contains('lesson-sortable-list')
    });
    drake.on('drag',     ()=>{ _lessonDragging=true; });
    drake.on('dragend',  ()=>{ setTimeout(()=>{ _lessonDragging=false; },150); });
    drake.on('drop',(el,target,source)=>{
        const newCh = target.dataset.chapterId;
        const oldCh = source.dataset.chapterId;
        if(newCh===oldCh) return;
        const lid = el.dataset.lessonId;
        // Remove placeholder
        target.querySelector('.empty-chapter-placeholder')?.remove();
        fetch('ajax/ajax_move_lesson.php',{
            method:'POST', headers:{'Content-Type':'application/json'},
            body:JSON.stringify({lesson_id:lid,chapter_id:newCh})
        }).then(r=>r.json()).then(r=>{
            if(r.status!=='success'){ Swal.fire('Error',r.message,'error'); drake.cancel(true); }
            if(!source.querySelectorAll('.lesson-item').length){
                const li = document.createElement('li');
                li.className='list-group-item text-muted small empty-chapter-placeholder';
                li.style='list-style:none;padding:.4rem .65rem;font-size:.78rem';
                li.textContent='No lessons yet';
                source.appendChild(li);
            }
            // update chapter badges
            source.closest('.ch-item')?.querySelector('.ch-badge')
                && (source.closest('.ch-item').querySelector('.ch-badge').textContent = source.querySelectorAll('.lesson-item').length);
            target.closest('.ch-item')?.querySelector('.ch-badge')
                && (target.closest('.ch-item').querySelector('.ch-badge').textContent = target.querySelectorAll('.lesson-item').length);
        }).catch(()=>{ Swal.fire('Error','Could not move lesson','error'); drake.cancel(true); });
    });
}

/* ════════════════════════════════════════════════════════════
   COURSE STATUS + REVIEW WORKFLOW
═══════════════════════════════════════════════════════════════ */
let _reviewStatus = null; // cached from last fetch

function loadCourseStatus(){
    fetch('ajax/ajax_course_review.php?action=get_status&course_id='+COURSE_ID)
    .then(r=>r.json()).then(r=>{
        if(r.status!=='success') return;
        const d = r.data || {};
        _reviewStatus = d;
        const approval = d.is_approved || 'pending';
        const status   = d.course_status || 'is_draft';

        const chip = document.getElementById('heroStatusChip');
        const txt  = document.getElementById('statusText');
        const ico  = document.getElementById('statusToggleIcon');
        const btn  = document.getElementById('statusToggleBtn');

        // Status chip reflects course live status
        if (status === 'active') {
            chip.className = 'status-chip active';
            chip.innerHTML = '<i class="bi bi-circle-fill" style="font-size:.45rem"></i> Live';
        } else {
            chip.className = 'status-chip draft';
            chip.innerHTML = '<i class="bi bi-circle-fill" style="font-size:.45rem"></i> Draft';
        }

        // Button state based on review/approval status
        if (status === 'active' && approval === 'approved') {
            ico.className = 'bi bi-toggle-on me-1 text-success';
            txt.textContent = 'Published';
            btn.style.background = '#dcfce7';
            btn.style.color = '#15803d';
            btn.disabled = false;
        } else if (approval === 'pending') {
            ico.className = 'bi bi-hourglass-split me-1 text-warning';
            txt.textContent = 'Pending Review';
            btn.style.background = '#fef9c3';
            btn.style.color = '#92400e';
            btn.disabled = false;
        } else if (approval === 'rejected') {
            ico.className = 'bi bi-x-circle me-1 text-danger';
            txt.textContent = 'Rejected — Resubmit';
            btn.style.background = '#fee2e2';
            btn.style.color = '#b91c1c';
            btn.disabled = false;
        } else {
            ico.className = 'bi bi-send me-1';
            txt.textContent = 'Submit for Review';
            btn.style.background = '#fff';
            btn.style.color = '#1e293b';
            btn.disabled = false;
        }
    }).catch(console.error);
}

function handlePublishClick(){
    const d = _reviewStatus || {};
    const approval = d.is_approved || '';
    const status   = d.course_status || '';

    if (status === 'active' && approval === 'approved') {
        // Already live — offer to unpublish
        Swal.fire({
            title: 'Unpublish this course?',
            text: 'It will be hidden from students until you resubmit for review.',
            icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Unpublish', confirmButtonColor: '#dc2626', reverseButtons: true
        }).then(res => {
            if (!res.isConfirmed) return;
            fetch('ajax/ajax_update_course_status.php', {
                method:'POST', headers:{'Content-Type':'application/json'},
                body: JSON.stringify({course_id:COURSE_ID, status:'inactive'})
            }).then(r=>r.json()).then(r => {
                if (r.status==='success') { Swal.fire({icon:'success',title:'Unpublished',timer:1200,showConfirmButton:false}); loadCourseStatus(); }
                else Swal.fire('Error', r.message, 'error');
            });
        });
        return;
    }

    if (approval === 'pending') {
        Swal.fire({
            title: 'Under Review',
            html: '<p class="text-muted small mb-0">Your course has been submitted and is awaiting admin review. You will be notified once a decision is made.</p>',
            icon: 'info', confirmButtonText: 'OK'
        });
        return;
    }

    // Show submit modal (draft or rejected resubmit)
    const isResubmit = approval === 'rejected';
    const adminComment = d.admin_comment || '';
    Swal.fire({
        title: isResubmit ? '<i class="bi bi-arrow-repeat me-2" style="color:#d97706"></i>Resubmit for Review' : '<i class="bi bi-send me-2" style="color:#6366f1"></i>Submit for Review',
        html: `
            ${isResubmit && adminComment ? `
            <div class="text-start p-3 mb-3 rounded-3" style="background:#fff1f2;border:1px solid #fecaca">
                <div class="fw-semibold small mb-1" style="color:#b91c1c"><i class="bi bi-chat-dots me-1"></i>Admin Feedback</div>
                <p class="mb-0 small" style="color:#7f1d1d;white-space:pre-wrap">${esc(adminComment)}</p>
            </div>` : ''}
            <div class="text-start">
                <label class="form-label small fw-semibold">Message to Admin <span class="text-muted fw-normal">(optional)</span></label>
                <textarea id="submitNoteInput" class="form-control form-control-sm" rows="3"
                    placeholder="Describe changes made or any notes for the reviewer…" style="border-radius:10px;resize:none"></textarea>
            </div>`,
        showCancelButton: true,
        confirmButtonText: isResubmit ? 'Resubmit' : 'Submit for Review',
        confirmButtonColor: '#6366f1',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        didOpen: () => { document.getElementById('submitNoteInput').focus(); }
    }).then(res => {
        if (!res.isConfirmed) return;
        const note = document.getElementById('submitNoteInput')?.value?.trim() || '';
        const btn  = document.getElementById('statusToggleBtn');
        btn.disabled = true;

        fetch('ajax/ajax_course_review.php', {
            method: 'POST', headers: {'Content-Type':'application/json'},
            body: JSON.stringify({action:'submit', course_id:COURSE_ID, note})
        }).then(r=>r.json()).then(r => {
            btn.disabled = false;
            if (r.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Submitted!',
                    text: 'Your course is now in the review queue. The admin will review and respond shortly.',
                    confirmButtonColor: '#6366f1'
                });
                loadCourseStatus();
            } else {
                Swal.fire('Error', r.message, 'error');
            }
        });
    });
}

function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

/* ════════════════════════════════════════════════════════════
   COURSE SETTINGS
═══════════════════════════════════════════════════════════════ */
function showCourseSettings(){
    document.getElementById('chapterContents').innerHTML=`
        <div class="text-center p-4"><div class="spinner-border text-primary spinner-border-sm"></div><p class="small text-muted mt-2">Loading settings…</p></div>`;
    fetch('pages/course_settings.php?course_id='+COURSE_ID)
    .then(r=>r.text()).then(html=>{
        document.getElementById('chapterContents').innerHTML=html;
        setTimeout(()=>{
            if(typeof FroalaEditor!=='undefined') new FroalaEditor('#course_description',{height:160});
        },200);
    }).catch(console.error);
}

/* ════════════════════════════════════════════════════════════
   ADD CHAPTER FORM SUBMIT
═══════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded',()=>{
    const form = document.querySelector('#addChapterForm');
    if(!form) return;
    let submitting = false;
    form.addEventListener('submit',e=>{
        e.preventDefault();
        if(submitting) return; submitting=true;
        if(!form.checkValidity()){ form.classList.add('was-validated'); submitting=false; return; }
        const title = document.getElementById('validationTooltip02').value;
        Swal.fire({title:'Saving…',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
        fetch('ajax/ajax_save_chapter.php',{
            method:'POST', headers:{'Content-Type':'application/json'},
            body:JSON.stringify({title, course_id:COURSE_ID})
        }).then(r=>r.json()).then(r=>{
            Swal.close(); submitting=false;
            if(r.status==='success'){
                bootstrap.Modal.getInstance(document.getElementById('createChapterModal'))?.hide();
                form.reset(); form.classList.remove('was-validated');
                Swal.fire({icon:'success',title:'Chapter Added',timer:1300,showConfirmButton:false});
                loadChapters(COURSE_ID);
            } else Swal.fire('Error',r.message,'error');
        }).catch(()=>{ Swal.fire('Error','Something went wrong','error'); submitting=false; });
    });
});

/* ════════════════════════════════════════════════════════════
   ADD LESSON (step 1: create blank lesson)
═══════════════════════════════════════════════════════════════ */
document.addEventListener('click',e=>{
    if(!e.target || e.target.id!=='saveLessonBtnNew') return;
    const title       = document.getElementById('lesson_title').value.trim();
    const content_type= document.getElementById('content_type').value.trim();
    const chapter_id  = document.getElementById('chapter_id').value.trim();
    if(!title){ Swal.fire('Error','Lesson title is required','error'); return; }
    if(!content_type){ Swal.fire('Error','Content type is required','error'); return; }
    const fd = new FormData();
    fd.append('lesson_title',title); fd.append('course_id',COURSE_ID);
    fd.append('chapter_id',chapter_id); fd.append('content_type',content_type);
    Swal.fire({title:'Creating lesson…',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
    fetch('ajax/ajax_save_lesson_new.php',{method:'POST',body:fd})
    .then(r=>r.text()).then(t=>{
        let res; try{ res=JSON.parse(t); }catch(e){ throw new Error('Invalid response'); }
        Swal.close();
        if(res.status==='success'){
            loadChapters(COURSE_ID);
            document.getElementById('chapterContents').innerHTML=`
                <div class="rp-welcome">
                    <div style="font-size:2.5rem;color:#22c55e;margin-bottom:.75rem"><i class="bi bi-check-circle-fill"></i></div>
                    <h6 class="fw-semibold" style="color:#334155">Lesson Created!</h6>
                    <p class="small">Click the lesson from the left panel to upload content.</p>
                    <button class="btn btn-sm mt-1" style="background:#eef2ff;color:#6366f1;border-radius:9px;font-size:.8rem;font-weight:600;border:none"
                        onclick="showLessonInputForm(${chapter_id})">
                        <i class="bi bi-plus-lg me-1"></i>Add Another
                    </button>
                </div>`;
            Swal.fire({icon:'success',title:'Lesson Created',timer:1300,showConfirmButton:false});
        } else Swal.fire('Error',res.message,'error');
    }).catch(err=>Swal.fire('Error',err.message||'Something went wrong','error'));
});

/* ════════════════════════════════════════════════════════════
   SHOW LESSON INPUT FORM
═══════════════════════════════════════════════════════════════ */
window.showLessonInputForm = function(chapterId){
    document.getElementById('chapterContents').innerHTML=`
        <div class="text-center p-4"><div class="spinner-border text-primary spinner-border-sm"></div></div>`;
    fetch('pages/lesson_input_form_new.php?chapter_id='+chapterId)
    .then(r=>r.text()).then(html=>{
        document.getElementById('chapterContents').innerHTML=html;
        setTimeout(()=>{
            if(typeof FroalaEditor!=='undefined') new FroalaEditor('#lesson_description',{height:160});
        },200);
    }).catch(console.error);
};

/* ════════════════════════════════════════════════════════════
   AUDIO HELPERS  (window-level so onclick= works)
═══════════════════════════════════════════════════════════════ */
window._removeAudioThumb = false;
window.previewAudioThumb = function(input){
    if(!input.files||!input.files[0]) return;
    const reader=new FileReader();
    reader.onload=e=>{
        document.getElementById('audioThumbImg').src=e.target.result;
        document.getElementById('audioThumbPreview').classList.remove('d-none');
    };
    reader.readAsDataURL(input.files[0]);
    window._removeAudioThumb=false;
};
window.removeAudioThumb = function(){
    document.getElementById('audioThumbPreview').classList.add('d-none');
    document.getElementById('audio_thumbnail_input').value='';
    window._removeAudioThumb=true;
};

function initAudioPlayer(){
    const doInit=()=>{
        const el=document.getElementById('lesson-audio-player');
        if(!el) return;
        new Plyr(el,{controls:['play','progress','current-time','duration','mute','volume'],resetOnEnd:false});
    };
    if(window.Plyr){ doInit(); return; }
    if(!document.getElementById('plyr-css')){
        const l=document.createElement('link');
        l.id='plyr-css'; l.rel='stylesheet';
        l.href='https://cdn.plyr.io/3.7.8/plyr.css';
        document.head.appendChild(l);
    }
    const s=document.createElement('script');
    s.src='https://cdn.plyr.io/3.7.8/plyr.polyfilled.js';
    s.onload=doInit;
    document.head.appendChild(s);
}

/* ════════════════════════════════════════════════════════════
   SHOW LESSON CONTENTS
═══════════════════════════════════════════════════════════════ */
/* ── Build embeddable URL from lesson fields (mirrors PHP buildEmbedUrl) ── */
function buildEmbedUrl(l){
    const vid  = l.video_id   || '';
    const lib  = l.library_id || '';
    const path = l.file_path  || '';

    // BunnyCDN stream via video_id + library_id
    if(vid && lib)
        return `https://iframe.mediadelivery.net/embed/${lib}/${vid}?autoplay=false&preload=true`;

    // BunnyCDN player URL → embed URL
    const m1 = path.match(/player\.mediadelivery\.net\/play\/(\d+)\/([a-f0-9\-]+)/i);
    if(m1) return `https://iframe.mediadelivery.net/embed/${m1[1]}/${m1[2]}?autoplay=false`;

    // Already an embed URL
    if(path.includes('iframe.mediadelivery.net/embed')) return path;

    // YouTube watch or short
    const m2 = path.match(/youtube\.com\/watch\?v=([^&]+)/i);
    if(m2) return `https://www.youtube.com/embed/${m2[1]}`;
    const m3 = path.match(/youtu\.be\/([^?]+)/i);
    if(m3) return `https://www.youtube.com/embed/${m3[1]}`;
    if(path.includes('youtube.com/embed')) return path;

    return path || null;
}

window.showLessonContents = function(lessonId){
    if(_lessonDragging) return;
    _activeLessonId = lessonId;
    document.getElementById('chapterContents').innerHTML=`
        <div class="text-center p-4">
            <div class="spinner-border text-primary spinner-border-sm"></div>
            <p class="small text-muted mt-2">Loading lesson…</p>
        </div>`;
    fetch('ajax/ajax_get_lesson.php?id='+lessonId)
    .then(r=>r.json()).then(res=>{
        if(res.status!=='success'){
            document.getElementById('chapterContents').innerHTML='<p class="p-3 text-danger">Error loading lesson.</p>';
            return;
        }
        const l = res.data;
        const ct = (l.content_type||'video').toLowerCase();

        /* ── Build media preview block ── */
        let mediaBlock = '';

        if(ct === 'audio'){
            const path  = l.file_path || '';
            const mime  = path.toLowerCase().endsWith('.mp4') ? 'audio/mp4' : 'audio/mpeg';
            const thumb = l.lesson_thumbnail || '';
            if(path){
                mediaBlock = `
                <div class="ld-section-title">Preview</div>
                <div class="mb-3">
                    ${thumb?`<img src="${escHtml(thumb)}" class="media-thumb mb-2" alt="Cover">`:''}
                    <audio id="lesson-audio-player" controls crossorigin playsinline style="width:100%;border-radius:10px">
                        <source src="${escHtml(path)}" type="${mime}">
                    </audio>
                </div>
                <div class="mb-3 p-3 rounded" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px">
                    <label class="form-label-sm"><i class="bi bi-image me-1 text-primary"></i>Audio Cover Image <span class="text-muted fw-normal">(optional)</span></label>
                    <input type="file" id="audio_thumbnail_input" class="form-control form-control-sm mt-1" accept="image/*" onchange="previewAudioThumb(this)" style="border-radius:8px">
                    <div id="audioThumbPreview" class="mt-2 ${thumb?'':'d-none'}">
                        <img id="audioThumbImg" src="${escHtml(thumb)}" class="rounded" style="max-height:90px;max-width:180px;object-fit:cover">
                        <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeAudioThumb()"><i class="bi bi-trash"></i></button>
                    </div>
                </div>`;
            } else {
                mediaBlock = `<div class="ld-section-title">Preview</div>
                <div class="mb-3 p-3 rounded text-center text-muted" style="background:#f8fafc;border:1px dashed #e2e8f0;border-radius:10px">
                    <i class="bi bi-music-note-beamed d-block mb-1" style="font-size:1.5rem;opacity:.4"></i>
                    <small>No audio uploaded yet. Upload a file below.</small>
                </div>`;
            }
        } else if(ct === 'pdf' || ct === 'presentation'){
            const path = l.file_path || '';
            if(path){
                mediaBlock = `
                <div class="ld-section-title">Preview</div>
                <div class="mb-3" style="position:relative">
                    <iframe src="${escHtml(path)}" class="media-frame mb-1"
                        style="height:320px" allowfullscreen></iframe>
                    <a href="${escHtml(path)}" target="_blank" class="btn btn-sm btn-outline-secondary"
                       style="font-size:.75rem;border-radius:8px;position:absolute;top:.5rem;right:.5rem;background:rgba(255,255,255,.9)">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Open
                    </a>
                </div>`;
            } else {
                const icon = ct==='pdf' ? 'bi-file-pdf' : 'bi-file-slides';
                mediaBlock = `<div class="ld-section-title">Preview</div>
                <div class="mb-3 p-3 rounded text-center text-muted" style="background:#f8fafc;border:1px dashed #e2e8f0;border-radius:10px">
                    <i class="bi ${icon} d-block mb-1" style="font-size:1.5rem;opacity:.4"></i>
                    <small>No ${ct} uploaded yet. Upload a file below.</small>
                </div>`;
            }
        } else {
            /* Video — try BunnyCDN embed or YouTube */
            const embedUrl = buildEmbedUrl(l);
            if(embedUrl){
                mediaBlock = `
                <div class="ld-section-title">Preview</div>
                <div class="mb-3" style="position:relative;padding-top:56.25%;border-radius:12px;overflow:hidden;background:#000">
                    <iframe src="${escHtml(embedUrl)}"
                        style="position:absolute;top:0;left:0;width:100%;height:100%;border:none"
                        allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture;fullscreen"
                        allowfullscreen></iframe>
                </div>`;
            } else {
                mediaBlock = `<div class="ld-section-title">Preview</div>
                <div class="mb-3 p-3 rounded text-center text-muted" style="background:#f8fafc;border:1px dashed #e2e8f0;border-radius:10px">
                    <i class="bi bi-play-circle d-block mb-1" style="font-size:1.5rem;opacity:.4"></i>
                    <small>No video uploaded yet. Upload a file below.</small>
                </div>`;
            }
        }

        const checked = v => v==1 ? 'checked' : '';

        document.getElementById('chapterContents').innerHTML = `
        <div style="padding:1.25rem">

            <!-- Header row -->
            <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                <div style="flex:1">
                    <div class="fw-bold" style="font-size:.95rem;color:#1e293b">${escHtml(l.lesson_title)}</div>
                    <div class="text-muted small">${escHtml(l.content_type||'Video')} lesson</div>
                </div>
                <button class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:.76rem"
                    onclick="openEditTitleModal(${l.id},'${escHtml(l.lesson_title).replace(/'/g,"\\'")}')">
                    <i class="bi bi-pencil me-1"></i>Rename
                </button>
            </div>

            <!-- Media block -->
            ${mediaBlock}

            <!-- Upload new file -->
            <div class="ld-section-title">Replace Media File</div>
            <div class="mb-3">
                <label class="form-label-sm">Upload new file <span class="text-muted fw-normal">(leave empty to keep current)</span></label>
                <input type="file" id="upload_file" class="form-control form-control-sm" style="border-radius:10px">
            </div>

            <!-- Description -->
            <div class="ld-section-title">Description</div>
            <div class="mb-3">
                <textarea id="lesson_description" style="width:100%;min-height:80px;border-radius:10px;border:1px solid #e2e8f0;padding:.65rem .85rem;font-size:.84rem;resize:vertical">${escHtml(l.description||'')}</textarea>
            </div>

            <!-- Settings toggles -->
            <div class="ld-section-title">Settings</div>
            <div class="mb-3 px-1">
                <div class="switch-row">
                    <div><div class="switch-lbl">Free Preview</div><div class="switch-sub">Students can watch without enrolling</div></div>
                    <div class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" id="isFreePreviewLesson" ${checked(l.isFreePreviewLesson)}></div>
                </div>
                <div class="switch-row">
                    <div><div class="switch-lbl">Enable Discussions</div><div class="switch-sub">Allow Q&amp;A on this lesson</div></div>
                    <div class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" id="enableDiscussions" ${checked(l.enableDiscussions)}></div>
                </div>
                <div class="switch-row">
                    <div><div class="switch-lbl">Downloadable</div><div class="switch-sub">Students can download the file</div></div>
                    <div class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" id="isDownloadable" ${checked(l.isDownloadable)}></div>
                </div>
            </div>

            <!-- Hidden -->
            <input type="hidden" id="lesson_id"   value="${l.id}">
            <input type="hidden" id="chapter_id"  value="${l.chapter_id}">
            <input type="hidden" id="lesson_title_hidden" value="${escHtml(l.lesson_title)}">
            <input type="hidden" id="content_type" value="${escHtml(l.content_type||'Video')}">

            <!-- Actions -->
            <div class="d-flex flex-wrap gap-2 pt-2 border-top mt-3">
                <button id="updateLessonBtn" class="btn btn-sm" style="background:#6366f1;color:#fff;border-radius:9px;font-size:.82rem;font-weight:600">
                    <i class="bi bi-cloud-upload me-1"></i>Save Changes
                </button>
                <button id="deleteLessonBtn" class="btn btn-sm btn-outline-danger" style="border-radius:9px;font-size:.82rem">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
                <a href="../data_files/?view=study_notes_manager&lesson_id=${l.id}&course_id=${l.course_id}&chapter_id=${l.chapter_id}"
                   class="btn btn-sm btn-outline-secondary ms-auto" style="border-radius:9px;font-size:.82rem">
                    <i class="bi bi-journal-bookmark me-1"></i>Q&amp;A Notes
                </a>
            </div>
        </div>`;

        setTimeout(()=>{
            if(l.content_type==='audio'&&l.file_path) initAudioPlayer();
        },200);

    }).catch(err=>{
        console.error(err);
        document.getElementById('chapterContents').innerHTML='<p class="p-3 text-danger">Error loading lesson.</p>';
    });
};

/* ════════════════════════════════════════════════════════════
   UPDATE LESSON  (file optional — just updates settings/desc if no file)
═══════════════════════════════════════════════════════════════ */
document.addEventListener('click',e=>{
    if(!e.target||e.target.id!=='updateLessonBtn') return;
    const fileInput= document.getElementById('upload_file');
    const file     = fileInput?.files?.[0];
    const lesson_id    = document.getElementById('lesson_id').value;
    const chapter_id   = document.getElementById('chapter_id').value;
    const title        = document.getElementById('lesson_title_hidden').value;
    const content_type = document.getElementById('content_type').value;
    const description  = document.getElementById('lesson_description').value;
    const isFree       = document.getElementById('isFreePreviewLesson').checked ? 1 : 0;
    const discuss      = document.getElementById('enableDiscussions').checked ? 1 : 0;
    const download     = document.getElementById('isDownloadable').checked ? 1 : 0;

    const fd = new FormData();
    fd.append('lesson_id',lesson_id); fd.append('chapter_id',chapter_id);
    fd.append('lesson_title',title);  fd.append('description',description);
    fd.append('content_type',content_type);
    fd.append('isFreePreviewLesson',isFree);
    fd.append('enableDiscussions',discuss);
    fd.append('isDownloadable',download);
    if(file) fd.append('file',file);

    const thumbInput = document.getElementById('audio_thumbnail_input');
    if(thumbInput?.files?.[0]) fd.append('lesson_thumbnail',thumbInput.files[0]);
    if(window._removeAudioThumb) fd.append('remove_thumbnail','1');

    Swal.fire({title:file?'Uploading…':'Saving…',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
    fetch('ajax/ajax_update_lesson.php',{method:'POST',body:fd})
    .then(r=>r.json()).then(r=>{
        Swal.close();
        if(r.status==='success'){
            Swal.fire({icon:'success',title:'Saved!',timer:1200,showConfirmButton:false});
            showLessonContents(lesson_id);
            loadChapters(COURSE_ID);
        } else Swal.fire('Error',r.message,'error');
    }).catch(()=>Swal.fire('Error','Upload failed','error'));
});

/* ════════════════════════════════════════════════════════════
   DELETE LESSON
═══════════════════════════════════════════════════════════════ */
document.addEventListener('click',e=>{
    if(!e.target?.closest('#deleteLessonBtn')) return;
    const lid = document.getElementById('lesson_id')?.value;
    if(!lid){ Swal.fire('Error','Lesson ID missing','error'); return; }
    Swal.fire({
        title:'Delete this lesson?',
        text:'This will also remove the associated media file. This cannot be undone.',
        icon:'warning', showCancelButton:true,
        confirmButtonColor:'#dc2626', confirmButtonText:'Yes, delete'
    }).then(r=>{
        if(!r.isConfirmed) return;
        Swal.fire({title:'Deleting…',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
        const fd=new FormData(); fd.append('lesson_id',lid);
        fetch('ajax/ajax_delete_lesson.php',{method:'POST',body:fd})
        .then(r=>r.json()).then(r=>{
            Swal.close();
            if(r.status==='success'){
                _activeLessonId=null;
                Swal.fire({icon:'success',title:'Deleted!',timer:1200,showConfirmButton:false});
                loadChapters(COURSE_ID);
                document.getElementById('chapterContents').innerHTML=`
                    <div class="rp-welcome">
                        <div class="wi"><i class="bi bi-play-circle"></i></div>
                        <h6 class="fw-semibold" style="color:#334155">Lesson deleted</h6>
                        <p class="small">Select another lesson or add a new one.</p>
                    </div>`;
            } else Swal.fire('Error',r.message,'error');
        }).catch(()=>Swal.fire('Error','Something went wrong','error'));
    });
});

/* ════════════════════════════════════════════════════════════
   RENAME LESSON TITLE
═══════════════════════════════════════════════════════════════ */
window.openEditTitleModal = function(id, title){
    document.getElementById('new_lesson_title').value = title;
    document.getElementById('edit_lesson_id').value   = id;
    new bootstrap.Modal(document.getElementById('editLessonTitleModal')).show();
};

document.addEventListener('click',e=>{
    if(!e.target||e.target.id!=='saveLessonTitleBtn') return;
    const newTitle  = document.getElementById('new_lesson_title').value.trim();
    const lesson_id = document.getElementById('edit_lesson_id').value;
    if(!newTitle){ Swal.fire('Error','Title cannot be empty','error'); return; }
    Swal.fire({title:'Updating…',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
    fetch('ajax/ajax_rename_lesson.php',{
        method:'POST', headers:{'Content-Type':'application/json'},
        body:JSON.stringify({lesson_id, lesson_title:newTitle})
    }).then(r=>r.json()).then(r=>{
        Swal.close();
        if(r.status==='success'){
            bootstrap.Modal.getInstance(document.getElementById('editLessonTitleModal'))?.hide();
            showLessonContents(lesson_id);
            loadChapters(COURSE_ID);
            Swal.fire({icon:'success',title:'Renamed!',timer:1100,showConfirmButton:false});
        } else Swal.fire('Error',r.message,'error');
    }).catch(()=>Swal.fire('Error','Something went wrong','error'));
});

/* ════════════════════════════════════════════════════════════
   COURSE SETTINGS — SAVE & DELETE
═══════════════════════════════════════════════════════════════ */
document.addEventListener('click',e=>{
    if(!e.target||e.target.id!=='saveCourseSettingsBtn') return;
    const title     = document.getElementById('course_name')?.value.trim();
    const price     = document.getElementById('course_price')?.value.trim();
    const discount  = document.getElementById('course_discount')?.value.trim();
    const desc      = document.getElementById('course_description')?.value;
    const cid       = document.getElementById('course_id')?.value;
    const lid       = document.getElementById('library_id')?.value;
    const lkey      = document.getElementById('library_key')?.value;
    const oldName   = document.getElementById('old_course_name')?.value;
    const cert      = document.getElementById('isCertificateOffered')?.checked ? 1 : 0;
    const qna       = document.getElementById('isQandAEnabled')?.checked ? 1 : 0;
    const fileInput = document.getElementById('course_thumbnail');
    const file      = fileInput?.files?.[0];
    if(!title||!price){ Swal.fire('Error','Course name and price are required','error'); return; }
    const fd=new FormData();
    fd.append('course_name',title); fd.append('course_price',price);
    fd.append('course_discount',discount||0); fd.append('course_description',desc||'');
    fd.append('course_id',cid); fd.append('isCertificateOffered',cert);
    fd.append('isQandAEnabled',qna); fd.append('old_course_name',oldName);
    fd.append('library_id',lid); fd.append('library_key',lkey);
    if(file) fd.append('thumbnail',file);
    Swal.fire({title:'Updating…',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
    fetch('ajax/ajax_update_course_settings.php',{method:'POST',body:fd})
    .then(r=>r.json()).then(r=>{
        Swal.close();
        r.status==='success' ? Swal.fire({icon:'success',title:'Saved!',timer:1200,showConfirmButton:false})
                             : Swal.fire('Error',r.message,'error');
    }).catch(()=>Swal.fire('Error','Something went wrong','error'));
});

document.addEventListener('click',e=>{
    if(!e.target?.closest('#deleteCourseBtn')) return;
    const title = document.getElementById('course_name')?.value.trim();
    const cid   = document.getElementById('course_id')?.value;
    Swal.fire({
        title:'Delete course?', icon:'warning',
        html:`<b>${escHtml(title)}</b><br><small>All chapters and lessons will be permanently deleted.</small>`,
        showCancelButton:true, confirmButtonColor:'#dc2626', confirmButtonText:'Yes, delete it!'
    }).then(r=>{
        if(!r.isConfirmed) return;
        Swal.fire({title:'Deleting…',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
        const fd=new FormData(); fd.append('course_name',title); fd.append('course_id',cid);
        fetch('ajax/ajax_delete_course.php',{method:'POST',body:fd})
        .then(r=>r.json()).then(r=>{
            Swal.close();
            if(r.status==='success') Swal.fire({icon:'success',title:'Deleted!',timer:1200,showConfirmButton:false})
                .then(()=>{ window.location.href='../data_files/?view=3002'; });
            else Swal.fire('Error',r.message,'error');
        }).catch(()=>Swal.fire('Error','Something went wrong','error'));
    });
});

/* ════════════════════════════════════════════════════════════
   CONTENT TYPE TOGGLE (in add-lesson form)
═══════════════════════════════════════════════════════════════ */
document.addEventListener('change',e=>{
    if(!e.target||e.target.id!=='content_type') return;
    const type     = e.target.value;
    const videoF   = document.getElementById('video_url');
    const fileF    = document.getElementById('upload_file');
    if(videoF&&fileF){
        videoF.parentElement.style.display = type==='Video' ? 'block' : 'none';
        fileF.parentElement.style.display  = type==='Video' ? 'none'  : 'block';
    }
});

/* ════════════════════════════════════════════════════════════
   UTILS
═══════════════════════════════════════════════════════════════ */
function escHtml(s){
    if(!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ════════════════════════════════════════════════════════════
   BOOT
═══════════════════════════════════════════════════════════════ */
(function boot(){
    if(document.readyState==='loading'){
        document.addEventListener('DOMContentLoaded',()=>{ loadChapters(COURSE_ID); loadCourseStatus(); });
    } else {
        loadChapters(COURSE_ID); loadCourseStatus();
    }
})();
</script>
