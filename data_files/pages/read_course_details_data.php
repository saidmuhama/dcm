<?php 
$course_id = $_GET['course_id'];
$sql = mysqli_query($db,"SELECT * FROM tbl_courses WHERE id='$course_id'");
$course = mysqli_fetch_assoc($sql);
$instructor_id= $course['instructor_id'];

$qry = mysqli_query($db,"SELECT * FROM tbl_tutors WHERE usr_code='$instructor_id'");
$instructor = mysqli_fetch_assoc($qry);

//lessons and statictics
$lessons_count = mysqli_num_rows(mysqli_query($db,"SELECT * FROM tbl_course_chapter_lessons WHERE course_id='$course_id'"));
$chapters_count = mysqli_num_rows(mysqli_query($db,"SELECT * FROM tbl_course_chapters WHERE course_id='$course_id'"));
?>

<style>
button[aria-expanded="true"] i { transform: rotate(180deg); transition: 0.3s; }

/* ── Study Notes ──────────────────────────────────────────────────────── */
.sn-card { border-radius: 12px; overflow: hidden; transition: box-shadow .15s ease; }
.sn-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.09) !important; }
.sn-card.important { border-left: 4px solid #f59e0b !important; }
.sn-header { cursor: pointer; user-select: none; }
.sn-chevron { transition: transform .25s ease; font-size: .75rem; flex-shrink: 0; }
.sn-card.open .sn-chevron { transform: rotate(180deg); }
.sn-answer {
    display: none;
    border-top: 1px solid rgba(0,0,0,.06);
    background: rgba(var(--bs-primary-rgb), .02);
    white-space: pre-wrap;
    line-height: 1.75;
}
.sn-answer.open { display: block; }
.sn-bm-btn { color: #cbd5e1; transition: color .15s ease; padding: 0; border: none; background: none; }
.sn-bm-btn.saved { color: #f59e0b; }
.sn-search .input-group-text { border-right: none; background: transparent; }
.sn-search .form-control { border-left: none; }
.sn-search .form-control:focus { box-shadow: none; border-color: #dee2e6; }
.sn-highlight { background: #fef08a; border-radius: 2px; padding: 0 2px; }
#studyNotesSection { animation: snFadeIn .25s ease; }
@keyframes snFadeIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:none; } }
</style>
<div class="container mt-4">
    <div class="row align-items-center">
        <div class="col-12 col-md">
            <h5>Courses Details</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-3 mb-md-0">
                    <li class="breadcrumb-item bi"><a href="../data_files/?view=3002">Dashboard</a></li>
                    <li class="breadcrumb-item bi"><a href="../data_files/?view=3002">Courses</a></li>
                    <li class="breadcrumb-item active bi" aria-current="page">Courses Details</li>
                </ol>
            </nav>
        </div>
        <div class="col-12 col-md-auto"></div>
    </div>
</div>
<div class="container mt-4" id="main-content">
    <div class="row">
        <div class="col-12 col-md-8">
            <div class="card adminuiux-card bg-theme-1-subtle mb-4">
                <div class="card-body">
                    <h4 class="mb-3 text-truncated"><?php echo $course['title']; ?></h4>
                    <div class="row align-items-center">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="avatar avatar-50 coverimg rounded"><img
                                    src="<?php echo $instructor['image']; ?>" alt=""></div>
                        </div>
                        <div class="col-auto mb-3 mb-sm-0">
                            <h6 class="mb-0">Instructor/Teacher: <?php echo $instructor['first_name'].' '.$instructor['last_name']; ?></h6>
                            <p class="small text-secondary"><?php echo $instructor['course']; ?></p>
                        </div>
                        <!-- <div class="col-auto mb-3 mb-sm-0">
                            <button class="btn btn-sm btn-outline-theme">View More</button>
                        </div> -->
                        <div class="col-12 col-sm-auto ms-auto mb-sm-0">
                            <span class="badge badge-light text-bg-theme-1 theme-red ms-1 my-1"><i
                                    class="bi bi-person me-1"></i> <?php echo $chapters_count; ?> Chapters </span><span
                                class="badge badge-light text-bg-theme-1 theme-orange ms-1 my-1"><i
                                    class="bi bi-eye me-1"></i> <?php echo $lessons_count; ?> Lessons </span>
                        </div>
                    </div>
                </div>
            </div>

            <iframe id="videoPlayer" class="height-400 w-100 rounded mb-2 border-0" title="Digital Class video player"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>

            <div class="position-sticky z-index-1" style="top:5.0rem">
                <div class="card adminuiux-card shadow-sm mb-4">
                    <div class="card-body p-2 overflow-x-auto">
                        <ul class="nav nav-pills adminuiux-nav-pills flex-nowrap" id="list-example">
                            <li class="nav-item"><a class="nav-link active" aria-current="page" href="#description">
                                    <div class="avatar avatar-28 icon"><i class="bi bi-journal-text fs-4"></i></div>
                                    <div class="col text-truncated">
                                        <p class="h6 mb-0">About Course</p>
                                    </div>
                                </a></li>
                            <li class="nav-item d-none" id="snNavTab">
                                <a class="nav-link" href="#studyNotesSection">
                                    <div class="avatar avatar-28 icon"><i class="bi bi-journal-bookmark-fill fs-4"></i></div>
                                    <div class="col text-truncated">
                                        <p class="h6 mb-0">Study Notes <span class="badge text-bg-theme-1 ms-1" id="snBadgeCount">0</span></p>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="#discussion">
                                    <div class="avatar avatar-28 icon"><i class="bi bi-chat-right-text fs-4"></i></div>
                                    <div class="col text-truncated">
                                        <p class="h6 mb-0">Discussion <span
                                                class="badge text-bg-theme-1 ms-1">115</span></p>
                                    </div>
                                </a></li>
                            <li class="nav-item"><a class="nav-link" href="#projectsnresources">
                                    <div class="avatar avatar-28 icon"><i class="bi bi-folder2 fs-4"></i>
                                    </div>
                                    <div class="col text-truncated">
                                        <p class="h6 mb-0">Resources</p>
                                    </div>
                                </a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card adminuiux-card shadow-sm mb-4" id="description">
                <div class="card-header">
                    <h6>About Course</h6>
                </div>
                <div class="card-body">
                    <p><?php echo $course['description']; ?></p>
                </div>
            </div>

            <!-- ── Study Notes Section ─────────────────────────────────────── -->
            <div id="studyNotesSection" class="d-none mb-4"></div>

            <div class="card adminuiux-card shadow-sm mb-4" id="discussion">

            </div>

        </div>
        <div class="col-12 col-md-4">
            <div class="card adminuiux-card shadow-sm mb-4" id="lesson_list_view">
    
            </div>
            <div class="card adminuiux-card shadow-sm bg-theme-1 overflow-hidden position-relative mb-4">
                <div class="position-absolute start-0 top-0 h-100 w-100 rounded overflow-hidden coverimg z-index-0">
                    <img src="../assets/img/learning/bg-overlay-1.png" alt="">
                </div>
                <div class="card-header">
                    <h4 class="fw-normal">Total <?php echo $lessons_count; ?> Lessons</h4>
                </div>
                <div class="card-body">
                    <div class="row gx-2 align-items-center mb-2">
                        <div class="col-auto">
                            <p class="opacity-75"><i class="bi bi-clock me-1"></i></p>
                        </div>
                        <div class="col">
                            <p>Total <?php echo $chapters_count; ?> Chapters</p>
                        </div>
                    </div>
                    <div class="row gx-2 align-items-center mb-2">
                        <div class="col-auto">
                            <p class="opacity-75"><i class="bi bi-clipboard-check me-1"></i></p>
                        </div>
                        <div class="col">
                            <p>4 Assignments</p>
                        </div>
                    </div>
                    <div class="row gx-2 align-items-center mb-2">
                        <div class="col-auto">
                            <p class="opacity-75"><i class="bi bi-bar-chart me-1"></i></p>
                        </div>
                    </div><br>
                    <div class="row align-items-center">
                        <div class="col">
                            <h4><?php echo number_format($course['price']); ?><small class="opacity-50 fw-normal">/Course</small></h4>
                        </div>
                        <div class="col-auto">
                            <button disabled class="btn btn-light">You Enrolled.</button>
                        </div>
                    </div>
                </div>
            </div>
          
        </div>
    </div>

</div>

<style>
/* Gradient theme */
.bg-gradient-primary {
    background: linear-gradient(135deg, #4e73df, #1cc88a);
}

/* Hover effect */
.card-header button:hover {
    opacity: 0.9;
}

/* Smooth icon rotation */
.transition-icon {
    transition: transform 0.3s ease;
}

/* Rotate icon when open */
button[aria-expanded="true"] .transition-icon {
    transform: rotate(180deg);
}
</style>

<script>

    document.addEventListener("DOMContentLoaded", function(){
    loadLessons();
    loadDiscussions();
});

 let currentLessonId = null;

function playLesson(path, lessonId){
    if(currentLessonId === lessonId){
        currentLessonId = null;
        loadStudyNotes(null);
    } else {
        currentLessonId = lessonId;
        loadStudyNotes(lessonId);
    }

    const player = document.getElementById("videoPlayer");
    if(player && path){
        player.src = path;
    }

    loadLessons();
}


// ✅ GLOBAL STATE
let completedLessons = []; // from backend

function loadLessons(){

    const params = new URLSearchParams(window.location.search);
    const course_id = params.get("course_id");

    if(!course_id){
        document.getElementById("lesson_list_view").innerHTML = "No course selected";
        return;
    }

    fetch("ajax/ajax_fetch_lessons_paid.php?course_id=" + course_id)
    .then(res => res.json())
    .then(res => {

        if(res.status !== "success"){
            document.getElementById("lesson_list_view").innerHTML = "No lessons found";
            return;
        }

        const isPaid = res.is_paid;
        completedLessons = res.completed_lessons || [];

        let html = "";
        let lessonCounter = 1;
        let totalLessons = 0;
        let completedCount = 0;

        let firstPlayableLesson = null;

        html += `<div class="accordion" id="lessonAccordion">`;

        res.data.forEach((chapter, index) => {

            let chapterId = "chapter_" + index;

            // ✅ FIND FIRST PLAYABLE LESSON
            chapter.lessons.forEach(lesson => {

                let isFree = lesson.isFreePreviewLesson == 1;
                let canPlay = isPaid || isFree;

                if(!currentLessonId && canPlay){
                    currentLessonId = lesson.id;
                    firstPlayableLesson = lesson;
                }
            });

            let isChapterActive = chapter.lessons.some(l => l.id == currentLessonId);

            html += `
            <div class="card mb-2">

                <!-- HEADER -->
                <div class="card-header p-2">
                    <button class="btn w-100 text-start d-flex justify-content-between"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse_${chapterId}">
                        <span>Chapter ${index+1}: ${chapter.chapter_title}</span>
                        <i class="bi ${isChapterActive ? 'bi-chevron-up' : 'bi-chevron-down'}"></i>
                    </button>
                </div>

                <!-- BODY -->
                <div id="collapse_${chapterId}" 
                     class="collapse ${isChapterActive ? 'show' : ''}"
                     data-bs-parent="#lessonAccordion">

                    <div class="card-body pb-0">
            `;

            chapter.lessons.forEach(lesson => {

                totalLessons++;

                let isFree = lesson.isFreePreviewLesson == 1;
                let canPlay = isPaid || isFree;
                let isActive = currentLessonId == lesson.id;
                let isCompleted = completedLessons.includes(parseInt(lesson.id));

                if(isCompleted) completedCount++;

                html += `
                <div class="row">
                    <div class="col-auto mb-3">
                        <div class="avatar avatar-40 rounded 
                            ${isCompleted 
                                ? 'bg-success text-white' 
                                : (isActive ? 'bg-theme-1 text-white' : 'border')}">
                            <h6>${String(lessonCounter).padStart(2,'0')}</h6>
                        </div>
                    </div>

                    <div class="col mb-3">
                        <div class="card 
                            ${isActive ? 'bg-theme-1 text-white shadow-lg' : ''}">

                            <div class="card-body">

                                <!-- TITLE -->
                                <div class="row mb-2">
                                    <div class="col">
                                        <h6>${lesson.lesson_title}</h6>
                                        <p class="small ${isActive ? 'opacity-75' : 'text-muted'}">
                                            ${lesson.description || ''}
                                        </p>
                                    </div>

                                    <!-- PLAY BUTTON -->
                                    <div class="col-auto">
                                        ${
                                            canPlay
                                            ? `
                                            <div onclick="playLesson('${lesson.file_path}', ${lesson.id})"
                                                class="btn btn-square rounded-circle 
                                                ${isActive ? 'btn-outline-light' : 'btn-outline-theme'}">
                                                <i class="bi ${isActive ? 'bi-pause' : 'bi-play'}"></i>
                                            </div>`
                                            : `
                                            <div class="btn btn-square rounded-circle btn-link theme-red">
                                                <i class="bi bi-lock"></i>
                                            </div>`
                                        }
                                    </div>
                                </div>

                                <!-- ACTION -->
                                <div class="row mt-2">
                                    <div class="col">
                                        ${
                                            isCompleted
                                            ? `<span class="badge bg-success">Completed</span>`
                                            : (canPlay
                                                ? `<button onclick="markCompleted(${lesson.id})"
                                                    class="btn btn-sm btn-outline-success">
                                                    Mark Complete
                                                   </button>`
                                                : ''
                                              )
                                        }
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                `;

                lessonCounter++;
            });

            html += `</div></div></div>`;
        });

        html += `</div>`;

        // ✅ AUTO LOAD FIRST VIDEO + STUDY NOTES
        if(firstPlayableLesson){
            const player = document.getElementById("videoPlayer");
            if(player && firstPlayableLesson.file_path){
                player.src = firstPlayableLesson.file_path;
            }
            loadStudyNotes(firstPlayableLesson.id);
        }

        // ✅ PROGRESS CALCULATION
        let percent = totalLessons > 0 
            ? Math.round((completedCount / totalLessons) * 100)
            : 0;

        html = `
        <div class="mb-3 px-3">
            <div class="d-flex justify-content-between mb-1">
                <small>Course Progress</small>
                <small class="fw-bold text-success">${percent}%</small>
            </div>

            <div class="progress" style="height:10px;">
                <div class="progress-bar bg-success"
                     style="width:${percent}%"></div>
            </div>
        </div>
        ` + html;

        document.getElementById("lesson_list_view").innerHTML = html;

    })
    .catch(err => {
        console.error(err);
        document.getElementById("lesson_list_view").innerHTML = "Error loading lessons";
    });
}

function markCompleted(lesson_id){

    fetch("ajax/ajax_mark_complete.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ lesson_id: lesson_id })
    })
    .then(res => res.json())
    .then(res => {

        if(res.status === "success"){
            
            Swal.fire("Success", res.message, "success")
            .then(()=>{
                loadLessons(); // refresh UI
            });
        }else{
            alert(res.message);
        }

    })
    .catch(err => {
        console.error(err);
    });
}

// ══════════════════════════════════════════════════════════════════════════════
// STUDY NOTES
// ══════════════════════════════════════════════════════════════════════════════
let snNotes   = [];
let snTab     = 'all';
let snSearch  = '';

async function loadStudyNotes(lessonId) {
    const section = document.getElementById('studyNotesSection');
    const navTab  = document.getElementById('snNavTab');

    if (!lessonId) {
        section.classList.add('d-none');
        section.innerHTML = '';
        navTab.classList.add('d-none');
        return;
    }

    const res = await fetch(`ajax/ajax_study_notes.php?action=list&lesson_id=${lessonId}`).then(r => r.json());
    snNotes = res.data || [];

    if (!snNotes.length) {
        section.classList.add('d-none');
        navTab.classList.add('d-none');
        return;
    }

    // Show tab + badge
    document.getElementById('snBadgeCount').textContent = snNotes.length;
    navTab.classList.remove('d-none');

    section.classList.remove('d-none');
    section.innerHTML = snBuildShell();
    snUpdateCounts();
    snRender();
}

function snBuildShell() {
    return `
    <div class="card adminuiux-card shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="card-header py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Study Notes
                </h6>
                <p class="small text-muted mb-0 mt-1">Click any question to expand the answer</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" onclick="snExpandAll()">
                    <i class="bi bi-arrows-expand me-1"></i>Expand All
                </button>
                <button class="btn btn-sm btn-outline-secondary" onclick="snCollapseAll()">
                    <i class="bi bi-arrows-collapse me-1"></i>Collapse All
                </button>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="card-body py-2 border-bottom bg-light bg-opacity-50">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <div class="input-group sn-search flex-grow-1" style="max-width:300px">
                    <span class="input-group-text"><i class="bi bi-search text-muted small"></i></span>
                    <input id="snSearchInput" type="text" class="form-control form-control-sm"
                           placeholder="Search notes…" oninput="snFilterInput(this.value)">
                </div>
                <div class="btn-group btn-group-sm ms-auto" role="group">
                    <button id="snBtnAll"      class="btn btn-primary"        onclick="snSetTab('all')">
                        All <span class="badge bg-white text-primary ms-1" id="snCntAll">0</span>
                    </button>
                    <button id="snBtnBookmarked" class="btn btn-outline-primary" onclick="snSetTab('bookmarked')">
                        <i class="bi bi-bookmark-fill me-1"></i>Saved
                        <span class="badge ms-1" id="snCntBookmarked">0</span>
                    </button>
                    <button id="snBtnImportant"  class="btn btn-outline-warning" onclick="snSetTab('important')">
                        <i class="bi bi-star-fill me-1"></i>Key
                        <span class="badge ms-1" id="snCntImportant">0</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Notes list -->
        <div class="card-body pt-3" id="snNotesList"></div>

        <!-- Empty state -->
        <div id="snEmpty" class="text-center py-5 d-none px-3">
            <i class="bi bi-journal-x fs-2 text-muted opacity-50 d-block mb-2"></i>
            <p class="text-muted small mb-0" id="snEmptyMsg">No notes match your search.</p>
        </div>
    </div>`;
}

function snUpdateCounts() {
    document.getElementById('snCntAll').textContent       = snNotes.length;
    document.getElementById('snCntBookmarked').textContent = snNotes.filter(n => n.bookmarked == 1).length;
    document.getElementById('snCntImportant').textContent  = snNotes.filter(n => n.is_important == 1).length;
}

function snFilterInput(val) {
    snSearch = val.trim().toLowerCase();
    snRender();
}

function snSetTab(tab) {
    snTab = tab;
    const map = {
        all:        ['snBtnAll',       'btn-primary',        'btn-outline-primary'],
        bookmarked: ['snBtnBookmarked','btn-primary',        'btn-outline-primary'],
        important:  ['snBtnImportant', 'btn-warning text-dark','btn-outline-warning'],
    };
    document.getElementById('snBtnAll').className        = 'btn btn-sm ' + (tab==='all'        ? 'btn-primary'         : 'btn-outline-primary');
    document.getElementById('snBtnBookmarked').className = 'btn btn-sm ' + (tab==='bookmarked' ? 'btn-primary'         : 'btn-outline-primary');
    document.getElementById('snBtnImportant').className  = 'btn btn-sm ' + (tab==='important'  ? 'btn-warning text-dark' : 'btn-outline-warning');
    snRender();
}

function snRender() {
    const list  = document.getElementById('snNotesList');
    const empty = document.getElementById('snEmpty');
    const msg   = document.getElementById('snEmptyMsg');
    if (!list) return;

    let filtered = snNotes.filter(n => {
        if (snTab === 'bookmarked' && !n.bookmarked)   return false;
        if (snTab === 'important'  && !n.is_important) return false;
        if (snSearch) return (n.question + ' ' + n.answer).toLowerCase().includes(snSearch);
        return true;
    });

    if (!filtered.length) {
        list.innerHTML = '';
        empty.classList.remove('d-none');
        msg.textContent = snSearch ? 'No notes match your search.' :
                          snTab === 'bookmarked' ? 'No saved notes yet.' : 'No key notes in this lesson.';
        return;
    }
    empty.classList.add('d-none');

    list.innerHTML = filtered.map((n, i) => {
        const q = snHl(n.question);
        const a = snHl(n.answer);
        return `
        <div class="sn-card mb-3 border shadow-sm ${n.is_important ? 'important' : ''}" data-id="${n.id}">
            <div class="sn-header d-flex align-items-start gap-3 p-3" onclick="snToggle(this.closest('.sn-card'))">
                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1 rounded-2 flex-shrink-0 mt-1">${i+1}</span>
                <div class="flex-grow-1 min-w-0">
                    ${n.is_important
                        ? `<span class="badge bg-warning text-dark me-2 mb-1">
                               <i class="bi bi-star-fill me-1"></i>Key Point
                           </span>`
                        : ''}
                    <span class="badge bg-secondary bg-opacity-10 text-secondary me-1 mb-1 small">${n.language}</span>
                    <p class="fw-semibold mb-0 lh-sm mt-1">${q}</p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0 pt-1">
                    <button class="sn-bm-btn ${n.bookmarked ? 'saved' : ''}"
                            title="${n.bookmarked ? 'Remove bookmark' : 'Save note'}"
                            onclick="snBookmark(event, ${n.id}, this)">
                        <i class="bi ${n.bookmarked ? 'bi-bookmark-fill' : 'bi-bookmark'} fs-5"></i>
                    </button>
                    <i class="bi bi-chevron-down sn-chevron text-muted"></i>
                </div>
            </div>
            <div class="sn-answer px-4 pb-4 pt-3">
                <div class="d-flex gap-2 mb-2">
                    <i class="bi bi-lightbulb-fill text-primary mt-1 flex-shrink-0"></i>
                    <div class="text-secondary small">${a}</div>
                </div>
            </div>
        </div>`;
    }).join('');
}

function snToggle(card) {
    const answer = card.querySelector('.sn-answer');
    const isOpen = card.classList.contains('open');
    card.classList.toggle('open', !isOpen);
    if (!isOpen) {
        answer.style.display = 'block';
        $(answer).hide().slideDown(200);
    } else {
        $(answer).slideUp(180, () => { answer.style.display = 'none'; });
    }
}

function snExpandAll() {
    document.querySelectorAll('.sn-card:not(.open)').forEach(c => {
        c.classList.add('open');
        const a = c.querySelector('.sn-answer');
        a.style.display = 'block';
        $(a).hide().slideDown(200);
    });
}

function snCollapseAll() {
    document.querySelectorAll('.sn-card.open').forEach(c => {
        c.classList.remove('open');
        const a = c.querySelector('.sn-answer');
        $(a).slideUp(180, () => { a.style.display = 'none'; });
    });
}

async function snBookmark(e, noteId, btn) {
    e.stopPropagation();
    const res = await fetch('ajax/ajax_study_notes.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ action: 'toggle_bookmark', note_id: noteId })
    }).then(r => r.json());

    if (res.status === 'success') {
        const note = snNotes.find(n => n.id == noteId);
        if (note) note.bookmarked = res.bookmarked ? 1 : 0;
        btn.classList.toggle('saved', res.bookmarked);
        btn.querySelector('i').className = `bi ${res.bookmarked ? 'bi-bookmark-fill' : 'bi-bookmark'} fs-5`;
        snUpdateCounts();
        if (snTab === 'bookmarked') snRender();
    }
}

function snHl(text) {
    const safe = String(text ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    if (!snSearch) return safe;
    const re = new RegExp(`(${snSearch.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi');
    return safe.replace(re, '<mark class="sn-highlight">$1</mark>');
}

// ══════════════════════════════════════════════════════════════════════════════

function toggleAnswer(id){
    $("#answerBox"+id).collapse('toggle');
}

function submitAnswer(id){

    let answer = document.getElementById("answerInput"+id).value;

    fetch("ajax/ajax_post_answer.php", {
        method: "POST",
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `discussion_id=${id}&answer=${encodeURIComponent(answer)}`
    })
    .then(res => res.json())
    .then(() => loadDiscussions());
}

function likeDiscussion(id){

    fetch("ajax/ajax_like_discussion.php", {
        method: "POST",
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `discussion_id=${id}`
    })
    .then(() => loadDiscussions());
}

function postQuestion(){

    let title = document.getElementById("questionTitle").value;
    let desc  = document.getElementById("questionDesc").value;

    // ✅ GET course_id FROM URL
    const params = new URLSearchParams(window.location.search);
    const course_id = params.get("course_id");

    if(!course_id){
        Swal.fire({
            title: "Course not found",
            icon: "error"
        });
        return;
    }

    if(!title){
        Swal.fire({
            title: "Enter question title",
            icon: "error"
        });
        return;
    }

    fetch("ajax/ajax_post_question.php", {
        method: "POST",
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `course_id=${course_id}&title=${encodeURIComponent(title)}&description=${encodeURIComponent(desc)}`
    })
    .then(res => res.json())
    .then(res => {

        if(res.status === "success"){

            Swal.fire({
                title: "Posted successfully",
                icon: "success",
                timer: 1500,
                showConfirmButton: false
            });

            // ✅ CLEAR INPUTS
            document.getElementById("questionTitle").value = "";
            document.getElementById("questionDesc").value = "";

            // ✅ RELOAD DISCUSSIONS
            loadDiscussions();

        }else{
            Swal.fire({
                title: res.message || "Failed",
                icon: "error"
            });
        }

    })
    .catch(err => {
        console.error(err);
        Swal.fire({
            title: "Server error",
            icon: "error"
        });
    });
}

function loadDiscussions(){

    // ✅ SAFE COURSE ID (fix your PHP echo issue)
    const params = new URLSearchParams(window.location.search);
    const course_id = params.get("course_id");

    if(!course_id){
        document.getElementById("discussion").innerHTML = "No course selected";
        return;
    }

    fetch("ajax/ajax_fetch_discussions.php?course_id=" + course_id)
    .then(res => res.json())
    .then(res => {

        let html = "";

        // ✅ ASK QUESTION (ALWAYS VISIBLE)
        html += `
        <div class="card mb-3">
            <div class="card-body">

                <h6>Ask a Question</h6>

                <input type="text" id="questionTitle" class="form-control mb-2"
                    placeholder="Question title">

                <textarea id="questionDesc" class="form-control mb-2"
                    placeholder="Describe your question"></textarea>

                <button onclick="postQuestion()" class="btn btn-theme btn-sm">
                    Post Question
                </button>

            </div>
        </div>
        `;

        // ✅ EMPTY STATE
        if(res.data.length === 0){
            html += `
            <div class="text-center text-muted py-4">
                <i class="bi bi-chat-dots fs-2"></i>
                <p>No discussions yet</p>
                <small>Be the first to ask a question</small>
            </div>
            `;
        }

        // ✅ ACCORDION START
        html += `<div class="accordion" id="discussionAccordion">`;

        // ✅ LOOP DISCUSSIONS
        res.data.forEach((d, index) => {

            let collapseId = "discussion_" + d.id;

            html += `
            <div class="card mb-2">

                <!-- HEADER (CLICK TO OPEN) -->
                <div class="card-header p-2 border-0 rounded-top 
                    bg-gradient-primary text-white shadow-sm">

                    <button class="btn w-100 text-start d-flex justify-content-between align-items-center 
                        text-white fw-semibold px-2 py-2"
                        data-bs-toggle="collapse"
                        data-bs-target="#${collapseId}"
                        style="background: transparent; border: none;">

                        <!-- LEFT: TITLE -->
                        <span>
                            <i class="bi bi-chat-left-text me-2"></i>
                            ${index + 1}. ${d.title}
                        </span>

                        <!-- RIGHT: ICON -->
                        <i class="bi bi-chevron-down transition-icon"></i>
                    </button>
                </div>

                <!-- BODY -->
                <div id="${collapseId}" class="collapse" data-bs-parent="#discussionAccordion">

                    <div class="card-body">

                        <p class="small text-secondary">
                            Asked by ${d.first_name} • ${d.created_at}
                        </p>

                        <p class="text-secondary">${d.description}</p>

                        <!-- ACTIONS -->
                        <div class="row align-items-center mb-2">

                            <div class="col-auto">
                                <button onclick="likeDiscussion(${d.id})"
                                    class="btn btn-outline-danger btn-sm">
                                    ❤️ ${d.total_likes}
                                </button>
                            </div>

                            <div class="col-auto">
                                <button class="btn btn-outline-theme btn-sm"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#answerBox${d.id}">
                                    Answer (${d.total_answers})
                                </button>
                            </div>

                        </div>

                        <!-- ANSWER BOX -->
                        <div id="answerBox${d.id}" class="collapse show">

                            <textarea id="answerInput${d.id}" 
                                class="form-control mb-2"
                                placeholder="Write answer..."></textarea>

                            <button onclick="submitAnswer(${d.id})"
                                class="btn btn-theme btn-sm mb-3">
                                Submit Answer
                            </button>

                            <hr>
            `;

            // ✅ NO ANSWERS
            if(d.answers.length === 0){
                html += `<p class="text-muted">No answers yet</p>`;
            }

            // ✅ ANSWERS LOOP
            d.answers.forEach(a => {

                html += `
                <div class="mb-2 ${a.is_correct == 1 ? 'bg-success-subtle p-2 rounded' : ''}">
                    <b>${a.first_name}</b> • ${a.created_at}
                    ${
                        a.is_correct == 1 
                        ? `<span class="badge bg-success ms-2">Best Answer</span>` 
                        : ''
                    }
                    <p class="mb-0">${a.answer}</p>
                </div>
                `;
            });

            html += `
                        </div> <!-- END ANSWERS -->
                    </div>
                </div>

            </div>
            `;
        });

        html += `</div>`; // END ACCORDION

        document.getElementById("discussion").innerHTML = html;

    })
    .catch(err => {
        console.error(err);
        document.getElementById("discussion").innerHTML = "Error loading discussions";
    });
}
</script>