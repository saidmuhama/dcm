<?php 
$course_id   = $_GET["course_id"];
$courseTitle = App::getWhatFromWHere("title", "tbl_courses", "id", $course_id);
$courseOwner = App::getWhatFromWHere("instructor_id","tbl_courses","id",$course_id);
$library_id  = App::getWhatFromWHere("library_id", "tbl_courses", "id", $course_id);
$library_key = App::getBunnyLibraryKey($library_id, App::getBunnyNetApiKey());

if($courseOwner != $_SESSION['usr_code']){
    ?>
    <script>
         window.location.href = "../data_files/?view=3002";
    </script>
    <?php 
    exit;
}
?>
<div class="container mt-4">
    <div class="row gx-3 align-items-center">
        <div class="col col-sm">
            <h5>Course Name: <?php echo $courseTitle; ?></h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item bi"><a href="../data_files/?view=3002">Home</a></li>
                    <li class="breadcrumb-item active bi" aria-current="page">Course Management</li>
                </ol>
            </nav>
        </div>


    </div>
</div>
<div class="container mb-3 mt-3" id="main-content">
    <div class="inner-sidebar-wrap">
        
        <div class="inner-sidebar-content h-100">
            <div class="row gx-3 h-100 mx-0">

                <div class="col-12 col-md-6 col-lg-12 col-xl-5 col-xxl-4">
                        <div class="card adminuiux-card shadow-sm mb-3 mt-3 mt-md-0">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Chapter Contents</h6>
                            <div class="d-flex gap-2">
                                <button id="toggleAllChaptersBtn" onclick="toggleAllChapters()" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-chevron-bar-contract" id="toggleAllIcon"></i> Collapse All
                                </button>
                                <button data-bs-toggle="modal" data-bs-target="#createChapterModal" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus"></i> Add Chapter
                                </button>
                            </div>
                        </div>
                        <div class="card-body py-0 px-2">
                           
                            <div class="accordion" id="accordionExample">
                               
                            </div>
                           
                            <hr/>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-12 col-xl-7 col-xxl-8 height-dynamic mb-4 mb-xl-0"
                    style="--h-dynamic: calc(100vh - 160px)">
                    <div class="card adminuiux-card shadow-sm h-100 mb-3">
                        <div class="card-header border-bottom">
                            <div class="row gx-2">
                                <div class="col-auto">
                                    <div class="row gx-2">
                                       
                                        <div class="col">
                                            <div class="row align-items-center">
                                                
                                                <div class="col-lg-12">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        
                                                        <button onclick="showCourseSettings()" type="button" class="btn btn-sm btn-outline-accent">
                                                            <i data-feather="settings" class="me-1 align-middle"></i> Course Settings
                                                        </button>
                                                        <button onclick="showCourseSettings()" type="button" class="btn btn-sm btn-primary">
                                                            <i data-feather="eye" class="me-1 align-middle"></i> Course Preview
                                                        </button>
                                                        <button id="statusBtn"
                                                            onclick="changeCourseStatus()" 
                                                            type="button" 
                                                            class="btn btn-sm">
                                                            <i data-feather="toggle-right" class="me-1 align-middle"></i>
                                                            <span id="statusText">Change Status</span>
                                                        </button>
                                                        <!-- <button onclick="showCourseSettings()" type="button" class="btn btn-sm btn-danger">
                                                            <i data-feather="trash" class="me-1 align-middle"></i> Delete Course
                                                        </button> -->

                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        <div id="chapterContents" class="card-body overflow-y-auto">
                            
                           

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="editLessonTitleModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">

      <!-- HEADER -->
      <div class="modal-header border-0 pb-0">
        <div>
          <h5 class="modal-title fw-bold mb-0">Edit Lesson Title</h5>
          <small class="text-muted">Update your lesson name below</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- BODY -->
      <div class="modal-body pt-3">

        <div class="form-floating">
          <input type="text" id="new_lesson_title" class="form-control rounded-3" placeholder="Lesson title">
          <label>Lesson Title</label>
        </div>

        <input type="hidden" id="edit_lesson_id">

      </div>

      <!-- FOOTER -->
      <div class="modal-footer border-0 pt-0 d-flex justify-content-between">

        <button class="btn btn-light px-4" data-bs-dismiss="modal">
          Cancel
        </button>

        <button id="saveLessonTitleBtn" class="btn btn-primary px-4 d-flex align-items-center gap-2">
          <i class="bi bi-check-lg"></i>
          Update
        </button>

      </div>

    </div>
  </div>
</div>

<style>
    /* ── Drag & Drop ── */
    .lesson-item {
        cursor: pointer;
    }
    .drag-handle {
        cursor: grab;
        opacity: 0.5;
    }
    .drag-handle:active {
        cursor: grabbing;
    }
    .lesson-item:hover .drag-handle {
        opacity: 1;
    }
    /* Dragula mirror (floating clone while dragging) */
    .gu-mirror {
        opacity: 0.85;
        background: #fff;
        border: 1px solid #0d6efd;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(13,110,253,.25);
        list-style: none;
        padding: 8px 12px;
        cursor: grabbing;
    }
    /* Drop-target highlight */
    .lesson-sortable-list.gu-over {
        background: rgba(13,110,253,.06);
        border-radius: 6px;
        outline: 2px dashed #0d6efd;
        outline-offset: -2px;
    }
    /* Placeholder slot while dragging */
    .gu-transit {
        opacity: 0.3;
    }

    .modal-content {
    transition: all 0.3s ease;
    }

    .modal-content:hover {
        transform: translateY(-2px);
    }

    #saveLessonTitleBtn {
        transition: 0.2s ease;
    }

    #saveLessonTitleBtn:hover {
        transform: scale(1.05);
    }
</style>

<script>
    $(document).ready(function(){
        loadCourseStatus();
    });

//EDIT LESSON OR VIDEO NAME TO BUNNY NET
document.addEventListener("click", function(e){

    if(e.target && e.target.id === "editLessonTitleBtn"){

        let title = e.target.getAttribute("data-title");
        let lesson_id = document.getElementById("lesson_id").value;

        document.getElementById("new_lesson_title").value = title;
        document.getElementById("edit_lesson_id").value = lesson_id;

        let modal = new bootstrap.Modal(document.getElementById('editLessonTitleModal'));
        modal.show();
    }

});

//Add chepter event
document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector("#addChapterForm");
    let isSubmitting = false;

    form.addEventListener("submit", function (e) {

        e.preventDefault();

        if (isSubmitting) return;
        isSubmitting = true;

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            isSubmitting = false;
            return;
        }

        let chapterName = document.getElementById("validationTooltip02").value;

        Swal.fire({
            title: "Saving...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch("ajax/ajax_save_chapter.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                title: chapterName,
                course_id: <?php echo $course_id; ?>
            })
        })
        .then(res => res.json())
        .then(res => {

            Swal.close();
            isSubmitting = false;

            if (res.status === "success") {

                // Close modal and reset form without page reload
                let modalEl = document.getElementById('createChapterModal');
                let modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                form.reset();
                form.classList.remove('was-validated');

                Swal.fire({
                    icon: "success",
                    title: "Chapter Added",
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                });

                loadChapters(<?php echo $course_id; ?>);

            } else {
                Swal.fire("Error", res.message, "error");
            }

        })
        .catch(() => {
            Swal.fire("Error", "Something went wrong", "error");
            isSubmitting = false;
        });

    });

});


document.addEventListener("click", function(e){

    if(e.target && e.target.id === "saveLessonBtnNew"){

        let title = document.getElementById("lesson_title").value.trim();
        let content_type = document.getElementById("content_type").value.trim();
        let chapter_id = document.getElementById("chapter_id").value.trim();
        let course_id = <?php echo $course_id; ?>;

        // ✅ VALIDATION
        if(title === ""){
            Swal.fire("Error","Lesson title is required","error");
            return;
        }

        if(content_type === ""){
            Swal.fire("Error","Content type is required","error");
            return;
        }

        let formData = new FormData();
        formData.append("lesson_title", title);
        formData.append("course_id", course_id);
        formData.append("chapter_id", chapter_id);
        formData.append("content_type",content_type);

        Swal.fire({
            title: "Saving Lesson...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch("ajax/ajax_save_lesson_new.php", {
            method: "POST",
            body: formData
        })
        .then(async (res) => {
            let text = await res.text(); 
            console.log("RAW RESPONSE:", text);

            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error("Invalid JSON response");
            }
        })
        .then(res => {
            console.log(res);
            Swal.close();

            if(res.status === "success"){

                let chapter_id = document.getElementById("chapter_id").value;

                // Refresh only the chapter list panel
                loadChapters(<?php echo $course_id; ?>);

                // Reset right panel to a clean success state
                document.getElementById("chapterContents").innerHTML = `
                    <div class="text-center py-5 text-success">
                        <i class="bi bi-check-circle-fill fs-1 mb-3 d-block"></i>
                        <h6 class="fw-semibold">Lesson Created Successfully</h6>
                        <p class="text-muted small">Click a lesson from the left panel to view or edit it.</p>
                        <button class="btn btn-sm btn-outline-primary mt-2"
                            onclick="showLessonInputForm(${chapter_id})">
                            <i class="bi bi-plus"></i> Add Another Lesson
                        </button>
                    </div>`;

                Swal.fire({
                    icon: "success",
                    title: "Lesson Created",
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                });

            } else {
                Swal.fire("Error", res.message, "error");
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire("Error", err.message || "Something went wrong", "error");
        });

    }

});


document.addEventListener("click", function(e){

    if(e.target && e.target.id === "saveLessonBtn"){

        let title = document.getElementById("lesson_title").value.trim();
        let video = document.getElementById("video_url").value.trim();
        let chapter_id = document.getElementById("chapter_id").value;
        let content_type = document.getElementById("content_type").value;

        let fileInput = document.getElementById("upload_file");
        let file = fileInput.files[0];

        let description = document.querySelector('#lesson_description').value;

        let isFree = document.getElementById("isFreePreviewLesson").checked ? 1 : 0;
        let discussion = document.getElementById("enableDiscussions").checked ? 1 : 0;
        let downloadable = document.getElementById("isDownloadable").checked ? 1 : 0;

        let course_id = <?php echo $course_id; ?>;

        // ✅ VALIDATION
        if(title === ""){
            Swal.fire("Error","Lesson title is required","error");
            return;
        }

        if(content_type === "video" && video === ""){
            Swal.fire("Error","Video URL is required","error");
            return;
        }

        if((content_type === "pdf" || content_type === "presentation") && !file){
            Swal.fire("Error","Please upload file","error");
            return;
        }

        let formData = new FormData();
        formData.append("lesson_title", title);
        formData.append("video_url", video);
        formData.append("description", description);
        formData.append("isFreePreviewLesson", isFree);
        formData.append("enableDiscussions", discussion);
        formData.append("isDownloadable", downloadable);
        formData.append("course_id", course_id);
        formData.append("chapter_id", chapter_id);
        formData.append("content_type", content_type);

        if(file){
            formData.append("file", file);
        }

        Swal.fire({
            title: "Saving Lesson...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch("ajax/ajax_save_lesson.php",{
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(res => {

            Swal.close();

            if(res.status === "success"){
                Swal.fire("Success", res.message, "success")
                loadChapters(<?php echo $course_id; ?>);
            } else {
                Swal.fire("Error", res.message, "error");
            }

        })
        .catch(()=>{
            Swal.fire("Error","Something went wrong","error");
        });

    }

});
 
//update course status 
function changeCourseStatus(){

    const params = new URLSearchParams(window.location.search);
    const course_id = params.get("course_id");

    if(!course_id){
        Swal.fire("Error", "Course ID not found", "error");
        return;
    }

    // 🔥 Fetch current status first
    fetch("ajax/ajax_get_course_status.php?course_id=" + course_id)
    .then(res => res.json())
    .then(res => {

        if(res.status !== "success"){
            Swal.fire("Error", "Failed to load course status", "error");
            return;
        }

        let currentStatus = res.course_status;

        // ✅ Determine next action
        let actionText = "";
        let newStatus = "";

        if(currentStatus === "active"){
            actionText = "Unpublish this course?";
            newStatus = "inactive";
        }
        else if(currentStatus === "inactive"){
            actionText = "Publish this course?";
            newStatus = "active";
        }
        else{
            actionText = "Publish this course?";
            newStatus = "active";
        }

        // 🎯 SweetAlert
        Swal.fire({
            title: actionText,
            text: "You can change this later anytime",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Yes, continue",
            cancelButtonText: "Cancel"
        }).then((result) => {

            if(result.isConfirmed){

                // 🚀 Update status
                fetch("ajax/ajax_update_course_status.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        course_id: course_id,
                        status: newStatus
                    })
                })
                .then(res => res.json())
                .then(res => {

                    if(res.status === "success"){
                        Swal.fire("Success", "Course status updated", "success");

                        // optional refresh
                        loadCourseStatus(); // 🔥 instead of reload
                    }else{
                        Swal.fire("Error", res.message, "error");
                    }

                });

            }

        });

    });

}
//load course status 
function loadCourseStatus(){

    const params = new URLSearchParams(window.location.search);
    const course_id = params.get("course_id");

    if(!course_id) return;

    fetch("ajax/ajax_get_course_status.php?course_id=" + course_id)
    .then(res => res.json())
    .then(res => {

        if(res.status !== "success") return;

        let status = res.course_status;

        let btn = document.getElementById("statusBtn");
        let text = document.getElementById("statusText");

        // 🔥 RESET CLASSES
        btn.classList.remove("btn-success","btn-danger","btn-info");

        if(status === "active"){
            btn.classList.add("btn-success");
            text.innerText = "Published";
        }
        else if(status === "inactive"){
            btn.classList.add("btn-danger");
            text.innerText = "Unpublished";
        }
        else{
            btn.classList.add("btn-info");
            text.innerText = "Draft";
        }

    });
}

//show course Settings
function showCourseSettings() {

    fetch("pages/course_settings.php?course_id=" + <?php echo $_GET['course_id']; ?>)
    .then(res => res.text())
    .then(html => {

        document.getElementById("chapterContents").innerHTML = html;

        // ✅ Wait a bit for DOM to render
        setTimeout(() => {

            if (typeof FroalaEditor !== "undefined") {

                new FroalaEditor('#course_description', {
                    height: 160
                });

            } else {
                console.error("Froala not loaded!");
            }

        }, 200);

    })
    .catch(err => {
        console.error("Error loading form:", err);
    });
}

//delete nunny video library
document.addEventListener("click", function(e){

    if(e.target && e.target.id === "deleteCourseBtn"){

        let title = document.getElementById("course_name").value.trim();
        let course_id = document.getElementById("course_id").value;

        // 🔥 CONFIRMATION FIRST
        Swal.fire({
                title: "⚠️ Are you sure?",
                html: `
                    <b>Delete course "${title}"?</b><br>
                    <small>All associated lessons will also be permanently deleted.</small>
                `,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel",

                // 🔥 Colors
                confirmButtonColor: "#dc3545", // bootstrap danger
                cancelButtonColor: "#6c757d",

                // 🔥 Background styling
                background: "#fff3f3",
                color: "#842029",

                // 🔥 Optional custom class
                customClass: {
                    popup: "border border-danger shadow-lg",
                    title: "text-danger",
                    confirmButton: "btn btn-danger",
                    cancelButton: "btn btn-secondary"
                }

            }).then((result) => {

            if(result.isConfirmed){

                let formData = new FormData();
                formData.append("course_name", title);
                formData.append("course_id", course_id);

                // 🔄 LOADING
                Swal.fire({
                    title: "Deleting...",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch("ajax/ajax_delete_course.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(res => {

                    Swal.close();

                    if(res.status === "success"){
                        Swal.fire({
                            title: "Deleted!",
                            text: res.message,
                            icon: "success"
                        }).then(() => {
                            location.reload();
                        });

                    } else {
                        Swal.fire("Error", res.message, "error");
                    }

                })
                .catch(() => {
                    Swal.fire("Error","Something went wrong","error");
                });

            }

        });

    }

});

//submit course settings.
document.addEventListener("click", function(e){

    if(e.target && e.target.id === "saveCourseSettingsBtn"){

        let title           = document.getElementById("course_name").value.trim();
        let old_course_name = document.getElementById("old_course_name").value;
        let library_id      = document.getElementById("library_id").value;
        let price           = document.getElementById("course_price").value.trim();
        let discount        = document.getElementById("course_discount").value.trim();
        let description     = document.getElementById("course_description").value;
        let course_id       = document.getElementById("course_id").value;
        let library_key     = document.getElementById("library_key").value;

        let certificate     = document.getElementById("isCertificateOffered").checked ? 1 : 0;
        let qna             = document.getElementById("isQandAEnabled").checked ? 1 : 0;

        let fileInput       = document.getElementById("course_thumbnail");
        let file            = fileInput.files[0];

        if(title === "" || price === ""){
            Swal.fire("Error","Course name and price required","error");
            return;
        }

        let formData = new FormData();
        formData.append("course_name", title);
        formData.append("course_price", price);
        formData.append("course_discount", discount);
        formData.append("course_description", description);
        formData.append("course_id", course_id);
        formData.append("isCertificateOffered", certificate);
        formData.append("isQandAEnabled", qna);
        formData.append("old_course_name", old_course_name);
        formData.append("library_id", library_id);
        formData.append("library_key", library_key);

        if(file){
            formData.append("thumbnail", file);
        }

        Swal.fire({
            title: "Updating...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch("ajax/ajax_update_course_settings.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(res => {

            Swal.close();

            if(res.status === "success"){
                Swal.fire("Success", res.message, "success");
            } else {
                Swal.fire("Error", res.message, "error");
            }

        })
        .catch(() => {
            Swal.fire("Error","Something went wrong","error");
        });

    }

});

document.addEventListener("DOMContentLoaded", function(){

    let course_id = <?php echo $course_id; ?>; 

    loadChapters(course_id);

    function loadChapters(course_id){

        fetch(`ajax/ajax_get_chapters_lessons.php?course_id=${course_id}`)
        .then(res => res.json())
        .then(data => {

            let html = "";
            let index = 1;

            data.forEach((chapter, i) => {

                let collapseId = "collapse" + i;

                html += `
                <div class="accordion-item">
                    <div class="accordion-header">
                        <button class="accordion-button"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#${collapseId}">
                            
                            <i class="bi bi-grid-3x3-gap"></i>&nbsp; ${chapter.chapter_title}
                        </button>
                    </div>

                    <div id="${collapseId}" class="accordion-collapse collapse show">
                        <div class="accordion-body">

                            <div class="row">
                                <div class="col-12">

                                    <ol class="list-group adminuiux-list-group lesson-sortable-list"
                    id="lessonList-${chapter.id}"
                    data-chapter-id="${chapter.id}">
                `;

                // LESSONS LOOP
                if(chapter.lessons.length > 0){

                    chapter.lessons.forEach(lesson => {
                        html += `
                        <li class="list-group-item lesson-item"
                            data-lesson-id="${lesson.id}"
                            onclick="showLessonContents(${lesson.id})">
                            <i class="bi bi-grip-vertical text-muted drag-handle me-1"></i>
                            <i class="bi bi-play-circle"></i>&nbsp; ${lesson.lesson_title}
                        </li>
                        `;
                    });

                } else {
                    html += `<li class="list-group-item text-muted empty-chapter-placeholder">No lessons yet</li>`;
                }

                html += `
                                    </ol>

                                    <center class="mt-3">
                                        <button class="btn btn-default addLessonBtn"
                                            data-chapter="${chapter.id}" onclick="showLessonInputForm(${chapter.id})">
                                            <i class="bi bi-plus"></i> Add Lesson
                                        </button>
                                    </center>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                `;
            });

            document.getElementById("accordionExample").innerHTML = html;

            initLessonDragDrop();

        });
    }

});

//Rename Video/Lesson Title
document.addEventListener("click", function(e){

    if(e.target && e.target.id === "saveLessonTitleBtn"){

        let newTitle = document.getElementById("new_lesson_title").value.trim();
        let lesson_id = document.getElementById("edit_lesson_id").value;

        if(newTitle === ""){
            Swal.fire("Error","Title is required","error");
            return;
        }

        // ✅ CONFIRMATION
        Swal.fire({
            title: "Confirm Update",
            text: `Rename this lesson to "${newTitle}"?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#0d6efd",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, update",
            cancelButtonText: "Cancel"
        }).then((result) => {

            if(!result.isConfirmed) return;

            // 🔄 LOADING
            Swal.fire({
                title: "Updating...",
                text: "Please wait",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch("ajax/ajax_rename_lesson.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    lesson_id: lesson_id,
                    lesson_title: newTitle
                })
            })
            .then(res => res.json())
            .then(res => {

                Swal.close();

                if(res.status === "success"){

                    Swal.fire("Success", res.message, "success");

                    // close modal safely
                    let modalEl = document.getElementById('editLessonTitleModal');
                    let modal = bootstrap.Modal.getInstance(modalEl);
                    if(modal) modal.hide();

                    // refresh UI
                    showLessonContents(lesson_id);

                } else {
                    Swal.fire("Error", res.message, "error");
                }

            })
            .catch((err)=>{
                console.error(err);
                Swal.fire("Error","Something went wrong","error");
            });

        });

    }

});
// ─── EXPAND / COLLAPSE ALL CHAPTERS ─────────────────────────────────────────
function toggleAllChapters() {
    let panels  = document.querySelectorAll('#accordionExample .accordion-collapse');
    let btn     = document.getElementById('toggleAllChaptersBtn');
    let icon    = document.getElementById('toggleAllIcon');
    let allOpen = Array.from(panels).every(p => p.classList.contains('show'));

    panels.forEach(function(panel) {
        let instance = bootstrap.Collapse.getOrCreateInstance(panel, { toggle: false });
        allOpen ? instance.hide() : instance.show();
    });

    if (allOpen) {
        icon.className = 'bi bi-chevron-bar-expand';
        btn.innerHTML  = '<i class="bi bi-chevron-bar-expand" id="toggleAllIcon"></i> Expand All';
    } else {
        icon.className = 'bi bi-chevron-bar-contract';
        btn.innerHTML  = '<i class="bi bi-chevron-bar-contract" id="toggleAllIcon"></i> Collapse All';
    }
}

// ─── DRAG & DROP LESSONS BETWEEN CHAPTERS ───────────────────────────────────
function initLessonDragDrop() {
    let lists = Array.from(document.querySelectorAll('.lesson-sortable-list'));
    if (!lists.length) return;

    window._lessonDragging = false;

    let drake = dragula(lists, {
        moves: function(el) {
            return el.classList.contains('lesson-item');
        },
        accepts: function(el, target) {
            return target.classList.contains('lesson-sortable-list');
        }
    });

    drake.on('drag', function() {
        window._lessonDragging = true;
    });

    drake.on('dragend', function() {
        setTimeout(function() { window._lessonDragging = false; }, 100);
    });

    drake.on('drop', function(el, target, source) {
        let newChapterId = target.dataset.chapterId;
        let oldChapterId = source.dataset.chapterId;

        if (newChapterId === oldChapterId) return;

        let lessonId = el.dataset.lessonId;

        // Remove empty placeholder from target if present
        let placeholder = target.querySelector('.empty-chapter-placeholder');
        if (placeholder) placeholder.remove();

        fetch('ajax/ajax_move_lesson.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ lesson_id: lessonId, chapter_id: newChapterId })
        })
        .then(function(r) { return r.json(); })
        .then(function(r) {
            if (r.status !== 'success') {
                Swal.fire('Error', r.message, 'error');
                drake.cancel(true);
            }
            // If source chapter is now empty, add placeholder
            if (source.querySelectorAll('.lesson-item').length === 0) {
                let li = document.createElement('li');
                li.className = 'list-group-item text-muted empty-chapter-placeholder';
                li.textContent = 'No lessons yet';
                source.appendChild(li);
            }
        })
        .catch(function() {
            Swal.fire('Error', 'Could not move lesson', 'error');
            drake.cancel(true);
        });
    });
}

// ─── PLYR AUDIO PLAYER ───────────────────────────────────────────────────────
function initAudioPlayer() {
    function doInit() {
        const el = document.getElementById('lesson-audio-player');
        if (!el) return;
        new Plyr(el, {
            controls: ['play', 'progress', 'current-time', 'duration', 'mute', 'volume'],
            resetOnEnd: false
        });
    }

    if (window.Plyr) { doInit(); return; }

    if (!document.getElementById('plyr-css')) {
        const link = document.createElement('link');
        link.id   = 'plyr-css';
        link.rel  = 'stylesheet';
        link.href = 'https://cdn.plyr.io/3.7.8/plyr.css';
        document.head.appendChild(link);
    }

    const script  = document.createElement('script');
    script.src    = 'https://cdn.plyr.io/3.7.8/plyr.polyfilled.js';
    script.onload = doInit;
    document.head.appendChild(script);
}

//show lesson contents
function showLessonContents(lessonId){
    if (window._lessonDragging) return;

    // Loader (optional UX)
    document.getElementById("chapterContents").innerHTML = `
        <div class="text-center p-3">
            <div class="spinner-border"></div>
            <p>Loading lesson...</p>
        </div>
    `;

    fetch("ajax/ajax_get_lesson.php?id=" + lessonId)
    .then(res => res.json())
    .then(res => {

        if(res.status !== "success"){
            document.getElementById("chapterContents").innerHTML = "<p>Error loading lesson</p>";
            return;
        }

        let lesson = res.data;

        // Build media block based on content_type
        function buildMediaBlock(lesson) {
            if (!lesson.file_path) return '';
            if (lesson.content_type === 'audio') {
                const mime = lesson.file_path.toLowerCase().endsWith('.mp4') ? 'audio/mp4' : 'audio/mpeg';
                return `
                <div class="mb-3">
                    <audio id="lesson-audio-player" controls crossorigin playsinline style="width:100%">
                        <source src="${lesson.file_path}" type="${mime}">
                    </audio>
                </div>`;
            }
            return `
            <div class="mb-3">
                <iframe
                    style="width:100%; height:400px; border:0; display:block;"
                    src="${lesson.file_path}"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin"
                    allowfullscreen>
                </iframe>
            </div>`;
        }

        // Inject HTML
       document.getElementById("chapterContents").innerHTML = `
            <div class="card adminuiux-card shadow-sm overflow-hidden z-index-0 mb-4">


                <div class="card-body pb-0">
                    <h6 class="mb-3">Lesson Basic Details</h6>

                    <!-- MEDIA PLAYER -->
                    ${buildMediaBlock(lesson)}

                    <!-- TITLE -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="fw-bold mb-0">Lesson Title</label>
                            <i class="bi bi-pencil-square text-primary" 
                            style="cursor:pointer;" 
                            id="editLessonTitleBtn"
                            data-title="${lesson.lesson_title}">
                            </i>
                        </div>

                        <div class="form-floating">
                            <input id="lesson_title" disabled readonly class="form-control" value="${lesson.lesson_title}">
                            <label>Lesson Title</label>
                        </div>
                    </div>


                    <!-- CONTENT TYPE -->
                    <div class="mb-3">
                        <div class="form-floating">
                            <input id="content_type" disabled readonly class="form-control" value="${lesson.content_type}">
                            <label>Content Type</label>
                        </div>
                    </div>
<!-- 
                    <div class="col-12 col-sm-12 col-lg-12 col-xl-12 mb-3">
                        <div class="form-floating">
                            <select class="form-select" id="content_type">
                                <option value="${lesson.content_type}">${lesson.content_type}</option>
                                <option value="">Select Content Type</option>
                                <option value="video">Video</option>
                                <option value="pdf">PDF</option>
                                <option value="presentation">Presentation</option>
                            </select> 
                            <label for="content_type">Content-Type</label>
                        </div>
                    </div>
-->
                    <div class="col-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="form-floating mb-3">
                            <input id="upload_file" type="file" required="" class="form-control"> 
                            <label>Upload File</label>
                        </div>
                        <div class="invalid-feedback">Please Attach File</div>
                    </div>

                    <!-- HIDDEN FIELDS -->
                    <input type="hidden" id="lesson_id" value="${lesson.id}">
                    <input type="hidden" id="chapter_id" value="${lesson.chapter_id}">

                    <!-- DESCRIPTION -->
                    <h6 class="mb-3">Lesson Description</h6>
                    <div class="mb-4">
                        <textarea id="lesson_description">${lesson.description ?? ''}</textarea>
                    </div>

                
                    <!-- SETTINGS -->
                    <h6 class="mb-3">Lesson Settings</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="isFreePreviewLesson" ${lesson.isFreePreviewLesson == 1 ? "checked" : ""}>
                                <label class="form-check-label">Free Preview</label>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="enableDiscussions" ${lesson.enableDiscussions == 1 ? "checked" : ""}>
                                <label class="form-check-label">Discussions</label>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="isDownloadable" ${lesson.isDownloadable == 1 ? "checked" : ""}>
                                <label class="form-check-label">Downloadable</label>
                            </div>
                            
                        </div>
                    </div>

                </div>

                <!-- FOOTER -->
                <div class="card-footer">
                    <button id="updateLessonBtn" class="btn btn-theme">Update Lesson</button>
                    <button id="deleteLessonBtn" class="btn btn-danger">Delete Lesson</button>
                </div>

            </div>
            `;

            // ✅ Initialize Froala + Plyr AFTER render
            setTimeout(() => {
                if (typeof FroalaEditor !== "undefined") {
                    new FroalaEditor('#lesson_description');
                }
                if (lesson.content_type === 'audio' && lesson.file_path) {
                    initAudioPlayer();
                }
            }, 200);

    })
    .catch(err => {
        console.error(err);
        document.getElementById("chapterContents").innerHTML = "<p>Error loading lesson</p>";
    });

}


//update lesson
document.addEventListener("click", function(e){

    if(e.target && e.target.id === "updateLessonBtn"){

        let fileInput = document.getElementById("upload_file");
        let file = fileInput.files[0];

        let lesson_id    = document.getElementById("lesson_id").value;
        let chapter_id   = document.getElementById("chapter_id").value;
        let title        = document.getElementById("lesson_title").value;
        let content_type = document.getElementById("content_type").value;
        let description  = document.getElementById("lesson_description").value;


        let isDownloadable = document.getElementById("isDownloadable").checked ? 1 : 0;
        let enableDiscussions = document.getElementById("enableDiscussions").checked ? 1 : 0;
        let isFreePreviewLesson = document.getElementById("isFreePreviewLesson").checked ? 1 : 0;
        
        if(!file){
            Swal.fire("Error","Please select a Media file","error");
            return;
        }

        let formData = new FormData();
        formData.append("file", file);
        formData.append("lesson_id", lesson_id);
        formData.append("chapter_id", chapter_id);
        formData.append("lesson_title", title);
        formData.append("description", description);
        formData.append("content_type", content_type);
        formData.append("isDownloadable", isDownloadable);
        formData.append("enableDiscussions", enableDiscussions);
        formData.append("isFreePreviewLesson", isFreePreviewLesson);

        Swal.fire({
            title: "Uploading Media File...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });         

        fetch("ajax/ajax_update_lesson.php",{
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            Swal.close();

            if(res.status === "success"){
                Swal.fire("Success", res.message, "success");
                showLessonContents(lesson_id);
            } else {
                Swal.fire("Error", res.message, "error");
            }
        })
        .catch(err=>{
            console.error(err);
            Swal.fire("Error","Upload failed "+err,"error");
        });

    }

});


//delete lesson
document.addEventListener("click", function(e){

    let btn = e.target.closest("#deleteLessonBtn");

    if(!btn) return;

    // GET FROM BUTTON DATA ATTRIBUTE
    let lesson_id = document.getElementById("lesson_id").value;

    if(!lesson_id){

        Swal.fire("Error", "Lesson ID missing", "error");
        return;
    }

    Swal.fire({
        title: "⚠️ Are you sure?",
        text: "This will delete the lesson AND all associated video/file. This cannot be undone!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, delete it",
        cancelButtonText: "Cancel"
    }).then((result) => {

        if (!result.isConfirmed) return;

        Swal.fire({
            title: "Deleting...",
            text: "Removing lesson and resources",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        let formData = new FormData();

        formData.append("lesson_id", lesson_id);

        fetch("ajax/ajax_delete_lesson.php", {
            method: "POST",
            body: formData
        })

        .then(res => res.json())

        .then(res => {

            Swal.close();

            if (res.status === "success") {

                Swal.fire("Deleted!", res.message, "success");

                // reload contents
                showLessonContents();

            } else {

                Swal.fire("Error", res.message, "error");
            }

        })

        .catch(err => {

            console.error(err);

            Swal.fire("Error", "Something went wrong", "error");
        });

    });

});
//show lesson input form
function showLessonInputForm(chapterId) {

    fetch("pages/lesson_input_form_new.php?chapter_id=" + chapterId)
    .then(res => res.text())
    .then(html => {

        document.getElementById("chapterContents").innerHTML = html;

        // ✅ Wait a bit for DOM to render
        setTimeout(() => {

            if (typeof FroalaEditor !== "undefined") {

                new FroalaEditor('#lesson_description', {
                    height: 160
                });

            } else {
                console.error("Froala not loaded!");
            }

        }, 200);

    })
    .catch(err => {
        console.error("Error loading form:", err);
    });
}



// AJAX FOR SAVING LESSONS 

document.addEventListener("change", function(e){

    if(e.target && e.target.id === "content_type"){

        let type = e.target.value;

        let videoField = document.getElementById("video_url");
        let fileField = document.getElementById("upload_file");

        if(videoField && fileField){
            videoField.parentElement.style.display =
                (type === "Video") ? "block" : "none";

            fileField.parentElement.style.display =
                (type === "Video") ? "none" : "block";
        }
    }

});




</script>