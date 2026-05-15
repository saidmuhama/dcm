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
    button[aria-expanded="true"] i {
    transform: rotate(180deg);
    transition: 0.3s;
}
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
                                </a>
                            </li>
                            <!-- <li class="nav-item"><a class="nav-link" href="#review">
                                    <div class="avatar avatar-28 icon"><i class="bi bi-chat-left-quote fs-4"></i></div>
                                    <div class="col text-truncated">
                                        <p class="h6 mb-0">Reviews <span class="badge text-bg-theme-1 ms-1">256</span>
                                        </p>
                                    </div>
                                </a></li>
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
                                </a>
                            </li> -->
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

            <!-- <div class="card adminuiux-card shadow-sm mb-4" id="discussion">
                
            </div> -->

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
                            <p class="opacity-75"><i class="bi bi-bar-chart me-1"></i></p>
                        </div>
                    </div>
                    <br>
                    <div class="row align-items-center">
                        <div class="col">
                            <h4><?php echo number_format($course['price']); ?><small class="opacity-50 fw-normal">/Course</small></h4>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<script>

    document.addEventListener("DOMContentLoaded", function(){
    loadLessons();
    loadDiscussions();
});

 let currentLessonId = null;

function playLesson(path, lessonId){

    if(currentLessonId === lessonId){
        currentLessonId = null;
    } else {
        currentLessonId = lessonId;
    }

    const player = document.getElementById("videoPlayer");
    if(player && path){
        player.src = path;
    }

    loadLessons();
}


function loadLessons(){

    const params = new URLSearchParams(window.location.search);
    const course_id = params.get("course_id");

    if(!course_id){
        document.getElementById("lesson_list_view").innerHTML = "No course selected";
        return;
    }

    fetch("ajax/ajax_fetch_lessons.php?course_id=" + course_id)
    .then(res => res.json())
    .then(res => {

        if(res.status !== "success"){
            document.getElementById("lesson_list_view").innerHTML = "No lessons found";
            return;
        }

        let html = "";
        let lessonCounter = 1;

        html += `<div class="accordion" id="lessonAccordion">`;

        res.data.forEach((chapter, index) => {

            let chapterId = "chapter_" + index;

            // auto select first lesson
            if(!currentLessonId && chapter.lessons.length > 0){
                currentLessonId = chapter.lessons[0].id;
            }

            // check if this chapter has active lesson
            let isChapterActive = chapter.lessons.some(l => l.id == currentLessonId);

            html += `
            <div class="card mb-2">

                <!-- HEADER -->
                <div class="card-header p-2" id="heading_${chapterId}">
                    <button class="btn w-100 text-start d-flex justify-content-between align-items-center"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse_${chapterId}"
                        aria-expanded="${isChapterActive ? 'true' : 'false'}">

                        <span>
                            Chapter ${index + 1}: ${chapter.chapter_title}
                        </span>

                        <i class="bi ${isChapterActive ? 'bi-chevron-up' : 'bi-chevron-down'}"></i>
                    </button>
                </div>

                <!-- BODY -->
                <div id="collapse_${chapterId}" 
                     class="collapse ${isChapterActive ? 'show' : ''}"
                     data-bs-parent="#lessonAccordion">

                    <div class="card-body pb-0">
            `;

            if(chapter.lessons.length === 0){
                html += `<p class="text-muted">No lessons</p>`;
            }

            chapter.lessons.forEach(lesson => {

                let duration = lesson.video_duration || "00:00";
                let isFree = lesson.isFreePreviewLesson == 1;
                let isActive = currentLessonId == lesson.id;

                html += `
                <div class="row">
                    <div class="col-auto mb-3">
                        <div class="avatar avatar-40 rounded 
                            ${isActive ? 'bg-theme-1 text-white' : (isFree ? 'bg-theme-1 text-white' : 'border')}">
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
                                        <p style="text-align:justify;" class="small ${isActive ? 'opacity-75' : 'text-muted'}">
                                            ${lesson.description || ''}
                                        </p>
                                    </div>

                                    <!-- BUTTON -->
                                    <div class="col-auto">
                                        ${
                                            isFree
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

                                <!-- TIME -->
                                <div class="row mb-2">
                                    <div class="col">
                                        <p class="small ${isActive ? 'opacity-75' : 'opacity-50'}">
                                            ${isActive ? '0:45min' : '00:00min'}
                                        </p>
                                    </div>
                                    <div class="col-auto">
                                        <p class="small ${isActive ? 'opacity-75' : 'opacity-50'}">
                                            ${duration}
                                        </p>
                                    </div>
                                </div>

                                <!-- PROGRESS -->
                                <div class="progress w-100 height-dynamic 
                                    ${isActive ? 'bg-white-opacity' : ''}" 
                                    style="--h-dynamic: 5px">

                                    <div class="progress-bar progress-bar-striped 
                                        ${isActive ? 'bg-white' : 'bg-theme-1'}"
                                         style="width: ${isActive ? '30%' : '5%'}">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                `;

                lessonCounter++;
            });

            html += `
                    </div>
                </div>
            </div>
            `;
        });

        html += `</div>`;

        document.getElementById("lesson_list_view").innerHTML = html;

    })
    .catch(err => {
        console.error(err);
        document.getElementById("lesson_list_view").innerHTML = "Error loading lessons";
    });
}


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

    if(!title){
        swal.fire({
            title: "Enter question title",
            icon: "error"
        });
        return;
    }

    fetch("ajax/ajax_post_question.php", {
        method: "POST",
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `title=${encodeURIComponent(title)}&description=${encodeURIComponent(desc)}`
    })
    .then(res => res.json())
    .then(() => {
        loadDiscussions();
    });
}

function loadDiscussions(){

    const course_id = <?php echo $_GET['course_id']; ?>;

    fetch("ajax/ajax_fetch_discussions.php?course_id=" + course_id)
    .then(res => res.json())
    .then(res => {
        console.log(res);
        let html = "";

        // ✅ ALWAYS SHOW ASK QUESTION FORM
        html += `
        <div class="card mb-4">
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

        // ✅ LOOP DISCUSSIONS
        res.data.forEach(d => {

            html += `
            <div class="card mb-4">
                <div class="card-body">

                    <h5 class="text-theme-1">${d.title}</h5>

                    <p class="small text-secondary">
                        Asked by ${d.name} • ${d.created_at}
                    </p>

                    <p class="text-secondary">${d.description}</p>

                    <div class="row align-items-center">

                        <div class="col-auto">
                            <button onclick="likeDiscussion(${d.id})"
                                class="btn btn-outline-danger btn-sm">
                                ❤️ ${d.total_likes}
                            </button>
                        </div>

                        <div class="col-auto">
                            <button onclick="toggleAnswer(${d.id})"
                                class="btn btn-outline-theme btn-sm">
                                Answer (${d.total_answers})
                            </button>
                        </div>

                    </div>

                </div>

                <!-- ANSWER SECTION ALWAYS EXISTS -->
                <div id="answerBox${d.id}" class="collapse show">

                    <div class="card-body border-top">

                        <!-- INPUT -->
                        <textarea id="answerInput${d.id}" class="form-control mb-2"
                            placeholder="Write answer..."></textarea>

                        <button onclick="submitAnswer(${d.id})"
                            class="btn btn-theme btn-sm mb-3">
                            Submit Answer
                        </button>

                        <hr>
            `;

            // ✅ IF NO ANSWERS
            if(d.answers.length === 0){
                html += `
                <p class="text-muted">No answers yet</p>
                `;
            }

            // ✅ SHOW ANSWERS
            d.answers.forEach(a => {

                html += `
                <div class="mb-2 ${a.is_correct == 1 ? 'bg-success-subtle p-2 rounded' : ''}">
                    <b>${a.name}</b> • ${a.created_at}
                    <p>${a.answer}</p>
                </div>
                `;
            });

            html += `</div></div></div>`;
        });

        document.getElementById("discussion").innerHTML = html;

    });
}
</script>