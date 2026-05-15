<?php 
//fixed tutor information
$emailAddress = @App::getWhatFromWHere('email_address','tbl_all_users', 'usr_code',$usr_code); 
$phoneNumber  = @App::getWhatFromWHere('phone_number','tbl_all_users', 'usr_code',$usr_code); 
$user_status  = @App::getWhatFromWHere('user_status','tbl_all_users', 'usr_code',$usr_code); 

$city = @App::getWhatFromWHere('city','tbl_tutors', 'usr_code',$usr_code); 
$country = @App::getWhatFromWHere('country','tbl_tutors','usr_code',$usr_code);
$description = @App::getWhatFromWHere('description','tbl_tutors','usr_code',$usr_code);
$course = @App::getWhatFromWHere('course','tbl_tutors','usr_code',$usr_code);
$salutation = @App::getWhatFromWHere('main_academic_level','tbl_tutors','usr_code',$usr_code);
$university = @App::getWhatFromWHere('sub_academic_level','tbl_tutors','usr_code',$usr_code);
$start_year = @App::getWhatFromWHere('start_year','tbl_tutors','usr_code',$usr_code);
?>
<div class="container mt-4">
    <div class="row align-items-center">
        <div class="col-12 col-md">
            <h5>Teacher Profile</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-3 mb-md-0">
                    <li class="breadcrumb-item bi"><a href="../data_files/?view=3002">Dashboard</a></li>
                    <li class="breadcrumb-item bi"><a href="">Teacher</a></li>
                    <li class="breadcrumb-item active bi" aria-current="page">Teacher Profile</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto col-md-auto">
            <a href="../data_files/?view=my_courses_online_contents_list_view" class="btn btn-success"><i
                    data-feather="eye" class="me-1"></i> View my Courses</a>
        
            <a data-bs-toggle="modal" data-bs-target="#addCourseModal" href="../data_files/?view=create_new_course" class="btn btn-theme"><i
                    data-feather="plus" class="me-1"></i> Create New Course</a>
        </div>
    </div>
</div>
<div class="container mt-4" id="main-content">
    <div class="row">
        <div class="col-12 col-md-5 col-lg-4 col-xl-3">
            <div class="height-300 w-100 rounded coverimg position-relative mb-3"><img
                    src="<?php echo $userProfileImage; ?>" alt=""></div>
            <div class="row gx-3 align-items-center justify-content-center mb-3">
                <div class="col-auto"><button class="btn btn-square rounded-circle btn-outline-theme theme-green"
                        data-bs-toggle="modal" data-bs-target="#callmodal"><i class="bi bi-telephone"></i></button>
                </div>
                <div class="col-auto"><button class="btn btn-square rounded-circle btn-outline-theme"
                        data-bs-toggle="modal" data-bs-target="#callmodal"><i class="bi bi-camera-reels"></i></button>
                </div>
                <div class="col-auto"><button class="btn btn-square rounded-circle btn-outline-theme theme-orange"
                        data-bs-toggle="modal" data-bs-target="#chatmodal"><i class="bi bi-chat-left-text"></i></button>
                </div>
            </div>
            <h5 class="mb-0"><?php echo $fullname; ?></h5>
            <p class="text-secondary mb-2"> <?php echo $roleTitle; ?></p>
            <p><span class="badge badge-light text-bg-theme-1 theme-green"><i
                        class="bi bi-check-all me-1"></i><?php echo $course; ?></span></p>
            <p class="text-secondary"><i class="bi bi-envelope me-1"></i> <?php echo $emailAddress; ?></p>
            <p class="text-secondary"><i class="bi bi-telephone me-1"></i> <?php echo $phoneNumber; ?></p>
            <p class="text-secondary"><i class="bi bi-geo-alt me-1"></i><?php echo $city . ', ' . $country; ?></p>
            <a href="../data_files/?view=teacher_profile_completion"  class="btn btn-theme w-100">Edit Profile <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="col-12 col-md-7 col-lg-8 col-xl-9">

        <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-header">
                    <div class="row">
                        <div class="col-auto mb-3 col-sm-0"><span
                                class="avatar avatar-40 rounded bg-theme-1-subtle text-theme-1"><i
                                    class="bi bi-person"></i></span></div>
                        <div class="col mb-3 col-sm-0">
                            <h6 class="mb-0">Course Statistics</h6>
                            <p class="small text-secondary">Average Sales & Enrollemnt</p>
                        </div>
                    </div>
                </div>
                <div class="card-body py-0">
                    <div class="height-420 mb-3">
                        <div class="row" id="courseAnalytics"></div>
                    </div>
                 
                </div>
        </div>

        <div class="row mb-4" id="dashboardStats"></div>

            <!-- <div class="row mb-3" id="latestCourses">
                
            </div> -->


            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="avatar avatar-30 rounded bg-theme-1-subtle text-theme-1"><i
                                    class="bi bi-play-btn"></i></span></div>
                        <div class="col">
                            <h6>My Courses</h6>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="dataTable">

                            <!-- HEADER -->
                            <thead class="table-light">
                                <tr>
                                    <th class="fw-semibold">Course</th>
                                    <th class="fw-semibold">Duration</th>
                                    <th class="fw-semibold">Schedule</th>
                                    <th class="fw-semibold">Status</th>
                                    <th class="fw-semibold text-center">Chat</th>
                                    <th class="fw-semibold text-center">Action</th>
                                </tr>
                            </thead>

                            <!-- BODY -->
                            <tbody id="instructorCourses">
                                <!-- Dynamic courses -->
                            </tbody>

                        </table>

                    </div>

                </div>
            </div>

            
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-center mb-3">
                        <div class="col-auto"><span class="avatar avatar-30 rounded bg-theme-1-subtle text-theme-1"><i
                                    class="bi bi-person"></i></span></div>
                        <div class="col">
                            <h6>About Instructor/Teacher</h6>
                        </div>
                    </div>
                    <p class="text-secondary"><?php echo $description; ?></p><span
                        class="badge badge-light me-1 mb-2 text-bg-theme-1">Teaching</span> <span
                        class="badge badge-light me-1 mb-2 text-bg-theme-1">Online Education</span> <span
                        class="badge badge-light me-1 mb-1 text-bg-theme-1">School Education</span> <span
                        class="badge badge-light me-1 mb-1 text-bg-theme-1">Language</span>
                </div>
            </div>
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-header z-index-1">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="avatar avatar-30 rounded bg-theme-1-subtle text-theme-1"><i
                                    class="bi bi-book"></i></span></div>
                        <div class="col">
                            <h6>Education</h6>
                        </div>
                    </div>
                </div>
                <div class="card-body pb-0">
                    <div class="row align-items-center mb-3">
                        <div class="col-auto"><span class="avatar avatar-50 rounded coverimg"><img
                                    src="assets/img/logo-512.png" alt=""></span></div>
                        <div class="col col-lg px-0">
                            <div class="row align-items-center">
                                <div class="col-12 col-lg">
                                    <h6 class="mb-0"><?php echo $university; ?></h6>
                                    <p><?php echo $course; ?></p>
                                </div>
                                <div class="col col-lg-auto">
                                    <p class="text-secondary mb-0"><?php echo $salutation; ?>-<?php echo $start_year; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                 
                </div>
            </div>

        </div>
    </div>
</div>


<style>

.custom-table {
        border-radius: 12px;
        overflow: hidden;
    }

    .custom-table thead {
        background: linear-gradient(45deg, #0d6efd, #0a58ca);
        color: #fff;
    }

    .custom-table th {
        font-weight: 600;
        font-size: 14px;
        padding: 14px;
        border: none;
    }

    .custom-table td {
        font-size: 14px;
        padding: 14px;
        vertical-align: middle;
        border-color: #f1f1f1;
    }

    .custom-table tbody tr {
        transition: all 0.2s ease;
    }

    .custom-table tbody tr:hover {
        background: #f8f9ff;
        transform: scale(1.005);
    }

    .course-img {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        object-fit: cover;
    }

    .status-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .status-active {
        background: #e6f4ea;
        color: #198754;
    }

    .status-draft {
        background: #fff3cd;
        color: #856404;
    }

    .status-inactive {
        background: #fdecea;
        color: #dc3545;
    }

    .action-btn {
        border-radius: 8px;
        font-size: 13px;
        padding: 5px 10px;
    }

/* Table spacing */
#dataTable td, #dataTable th {
    padding: 14px 12px;
    vertical-align: middle;
}

/* Header styling */
#dataTable thead th {
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
}

/* Row hover effect */
#dataTable tbody tr {
    transition: all 0.2s ease;
}

#dataTable tbody tr:hover {
    background-color: #f8f9fa;
    transform: scale(1.005);
}

/* Avatar image */
.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Status badges */
.badge {
    padding: 6px 10px;
    font-size: 12px;
    border-radius: 8px;
}

/* Action buttons */
.btn-sm {
    padding: 4px 10px;
    font-size: 12px;
}

/* Rounded card look */
.card {
    border-radius: 12px;
    overflow: hidden;
}

/* Chat icon spacing */
.bi-chat-text {
    font-size: 16px;
}

/* Table borders soft */
.table {
    border-color: #f1f1f1;
}

</style>

<script>
document.addEventListener("DOMContentLoaded", function(){
    // The appd9fa.js bundle calls window.dataTables() on window.load, which would try
    // to re-initialise #dataTable after loadInstructorCourses() already owns it.
    // Override it here (deferred bundle has already defined it; window.load hasn't fired yet).
    window.dataTables = function() {};

    loadInstructorCourses();
    loadLatestCourses();
    loadCourseAnalytics();
    loadDashboardStats();
});

function loadDashboardStats(){

    fetch("ajax/ajax_fetch_instructor_courses_analytics.php")
    .then(res => res.json())
    .then(res => {

        let totalCourses = res.data.length;
        let totalStudents = 0;
        let totalEarnings = 0;

        res.data.forEach(c => {
            totalStudents += parseInt(c.students);
            totalEarnings += parseFloat(c.net_earnings);
        });

        document.getElementById("dashboardStats").innerHTML = `
        <div class="col-md-4">
            <div class="card p-3 text-center shadow-sm">
                <h4>${totalCourses}</h4>
                <small>Total Courses</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 text-center shadow-sm">
                <h4>${totalStudents}</h4>
                <small>Students</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 text-center shadow-sm">
                <h4 class="text-success">
                    TZS ${totalEarnings.toLocaleString()}
                </h4>
                <small>Total Earnings</small>
            </div>
        </div>
        `;
    });
}

function toggleCourseStatus(course_id, status){

    Swal.fire({
        title: status === 'active' ? "Publish Course?" : "Unpublish Course?",
        text: "You can change this anytime",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes, proceed"
    }).then((result) => {

        if(result.isConfirmed){

            fetch("ajax/ajax_toggle_course_status.php", {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({course_id, status})
            })
            .then(res => res.json())
            .then(res => {

                if(res.status === "success"){
                    Swal.fire("Updated!", "", "success");
                    loadCourseAnalytics();
                }else{
                    Swal.fire("Error", res.message, "error");
                }

            });

        }
    });
}

function loadCourseAnalytics(){

    fetch("ajax/ajax_fetch_instructor_courses_analytics.php")
    .then(res => res.json())
    .then(res => {

        let container = document.getElementById("courseAnalytics");

        if(res.status !== "success"){
            container.innerHTML = "No courses found";
            return;
        }

        let html = "";

        res.data.forEach(course => {

            let thumbnail = course.thumbnail || "assets/img/default-course.png";

            let statusBadge = "";
            let toggleBtn = "";

            if(course.status === "active"){
                statusBadge = `<span class="badge bg-success">Live</span>`;
                toggleBtn = `
                    <button onclick="toggleCourseStatus(${course.id}, 'inactive')" 
                        class="btn btn-sm btn-outline-danger">
                        Unpublish
                    </button>`;
            } else {
                statusBadge = `<span class="badge bg-warning text-dark">Draft</span>`;
                toggleBtn = `
                    <button onclick="toggleCourseStatus(${course.id}, 'active')" 
                        class="btn btn-sm btn-outline-success">
                        Go Live
                    </button>`;
            }

            html += `
            <div class="col-12 col-md-6 col-lg-4 mb-4">

                <div class="card shadow-sm h-100">

                    <!-- IMAGE -->
                    <div class="position-relative">
                        <img src="${thumbnail}" class="w-100" style="height:180px; object-fit:cover;">
                        <div class="position-absolute top-0 end-0 m-2">
                            ${statusBadge}
                        </div>
                    </div>

                    <!-- BODY -->
                    <div class="card-body">

                        <h6 class="fw-bold">${course.title}</h6>

                        <p class="small text-muted mb-2">
                            ${course.students} Students • ${course.total_sales} Sales
                        </p>

                        <!-- EARNINGS -->
                        <div class="mb-2">
                            <small class="text-muted">Earnings</small>
                            <h5 class="text-success">
                                TZS ${Number(course.net_earnings).toLocaleString()}
                            </h5>
                        </div>

                        <!-- PROGRESS BAR (SALES PERFORMANCE) -->
                        <div class="progress mb-3" style="height:6px;">
                            <div class="progress-bar bg-success" 
                                style="width:${Math.min(course.total_sales * 10,100)}%">
                            </div>
                        </div>

                        <!-- ACTIONS -->
                        <div class="d-flex justify-content-between">

                            ${toggleBtn}

                            <a href="../data_files/?view=course_contents_management&course_id=${course.id}" 
                                class="btn btn-sm btn-outline-primary">
                                View
                            </a>

                        </div>

                    </div>
                </div>

            </div>
            `;
        });

        container.innerHTML = html;

    });
}

function loadLatestCourses(){

    fetch("ajax/ajax_fetch_latest_courses.php")
    .then(res => res.json())
    .then(res => {

        let container = document.getElementById("latestCourses");

        if(res.status !== "success" || res.data.length === 0){
            container.innerHTML = `
                <div class="col-12 text-center text-muted">
                    No published courses yet
                </div>
            `;
            return;
        }

        let html = "";

        res.data.forEach((course, index) => {
        
            let thumbnail = course.thumbnail 
                ? course.thumbnail 
                : "assets/img/default-course.png";
    
            let bgClass = index === 0 ? "bg-theme-1 text-white" : "";

            html += `
            <div class="col-12 col-md-12 col-lg-4">
                <div class="card adminuiux-card shadow-sm mb-2 ${bgClass}">
                    
                    <div class="card-body">

                        <div class="row align-items-center">
                            <div class="col-auto mb-3">
                                <div class="avatar avatar-50 coverimg rounded">
                                    <img src="${thumbnail}">
                                </div>
                            </div>

                            <div class="col mb-3">
                                <h6 class="mb-0">${course.title}</h6>
                                <p class="small opacity-75">Your Course</p>
                            </div>
                        </div>

                        <p class="small opacity-75 mb-0">
                            Duration: ${course.duration || 'N/A'}
                        </p>

                        <p class="small opacity-75">
                            Published
                        </p>

                        <div class="row align-items-center">
                            <div class="col-auto">
                                <p class="small">
                                    ${new Date(course.created_at).toLocaleDateString()}
                                </p>
                            </div>

                            <div class="col text-center opacity-75">
                                <p class="small">
                                    <i class="bi bi-clock me-1"></i>Live Course
                                </p>
                            </div>

                            <div class="col-auto">
                                <button class="btn btn-sm btn-outline-light"
                                    onclick="window.location='course-details.php?id=${course.id}'">
                                    View
                                </button>
                            </div>
                        </div>

                    </div>

                    <div class="progress height-dynamic" style="--h-dynamic:5px">
                        <div class="progress-bar bg-success" style="width:100%"></div>
                    </div>

                </div>
            </div>
            `;
        });

        container.innerHTML = html;

    })
    .catch(err => {
        console.error(err);
    });
}

// let statusBadge = `
// <span class="badge 
//     ${course.status === 'active' ? 'bg-success' : 
//       course.status === 'inactive' ? 'bg-danger' : 
//       'bg-warning text-dark'}">
//     ${course.status}
// </span>
// `;


// function getStatusBadge(status){

//     if(status === "active"){
//         return `<span class="badge bg-success">Active</span>`;
//     }

//     if(status === "inactive"){
//         return `<span class="badge bg-danger">Inactive</span>`;
//     }

//     return `<span class="badge bg-warning text-dark">Draft</span>`;
// }
//change or update course status 
function changeCourseStatus(course_id, newStatus){

    Swal.fire({
        title: "Confirm Action",
        text: `Do you want to ${newStatus === 'active' ? 'publish' : 'unpublish'} this course?`,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes"
    }).then(result => {

        if(result.isConfirmed){

            fetch("ajax/ajax_update_my_course_status.php", {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({
                    course_id: course_id,
                    status: newStatus
                })
            })
            .then(res => res.json())
            .then(res => {

                if(res.status === "success"){
                    Swal.fire("Success", "Course updated", "success");
                    loadInstructorCourses();
                    loadLatestCourses();
                }else{
                    Swal.fire("Error", res.message, "error");
                }

            });
        }
    });
}

function loadInstructorCourses(){

    fetch("ajax/ajax_fetch_instructor_courses.php")

    .then(res => res.json())

    .then(res => {

        // =========================
        // DESTROY OLD DATATABLE
        // =========================
        if ($.fn.DataTable.isDataTable('#dataTable')) {

            $('#dataTable').DataTable().destroy();

        }

        // OPTIONAL
        // clears old table completely
        $('#dataTable tbody').empty();

        let html = "";

        // =========================
        // EMPTY STATE
        // =========================
        if(res.status !== "success"){

            html = `
            <tr>
                <td colspan="6" class="text-center text-muted">
                    No courses found
                </td>
            </tr>
            `;
        }

        // =========================
        // LOOP COURSES
        // =========================
        else{

            res.data.forEach(course => {

                let thumbnail = course.thumbnail
                    ? course.thumbnail
                    : "assets/img/default-course.png";

                let statusBadge = "";
                let actionBtn = "";

                if(course.status === "active"){

                    statusBadge = `
                    <span class="badge bg-success">
                        Active
                    </span>`;

                    actionBtn = `
                    <button onclick="changeCourseStatus(${course.id}, 'inactive')"
                        class="btn btn-sm btn-outline-danger">
                        Unpublish
                    </button>`;
                }

                else if(course.status === "inactive"){

                    statusBadge = `
                    <span class="badge bg-danger">
                        Inactive
                    </span>`;

                    actionBtn = `
                    <button onclick="changeCourseStatus(${course.id}, 'active')"
                        class="btn btn-sm btn-outline-success">
                        Publish
                    </button>`;
                }

                else{

                    statusBadge = `
                    <span class="badge bg-warning text-dark">
                        Draft
                    </span>`;

                    actionBtn = `
                    <button onclick="changeCourseStatus(${course.id}, 'active')"
                        class="btn btn-sm btn-outline-success">
                        Publish
                    </button>`;
                }

                html += `
                <tr>

                    <!-- COURSE -->
                    <td>
                        <div class="d-flex align-items-center">

                            <img src="${thumbnail}"
                                class="rounded shadow-sm me-2"
                                width="45"
                                height="45"
                                style="object-fit:cover;">

                            <div>
                                <h6 class="mb-0 fw-semibold">
                                    ${course.title}
                                </h6>

                                <small class="text-muted">
                                    ${course.type || 'Course'}
                                </small>
                            </div>

                        </div>
                    </td>

                    <!-- DURATION -->
                    <td>
                        <div class="fw-semibold">
                            ${course.duration || 'N/A'}
                        </div>

                        <small class="text-muted">
                            ${course.total_chapters} Chapters
                        </small>
                    </td>

                    <!-- SCHEDULE -->
                    <td>
                        <div>
                            ${course.duration || 'N/A'}
                        </div>

                        <small class="text-muted">
                            ${course.start_date || 'No date'}
                        </small>
                    </td>

                    <!-- STATUS -->
                    <td>
                        ${statusBadge}
                    </td>

                    <!-- CHAT -->
                    <td class="text-center">

                        <button class="btn btn-sm btn-light border">

                            <i class="bi bi-chat"></i> 0

                        </button>

                    </td>

                    <!-- ACTION -->
                    <td class="text-center">

                        ${actionBtn}

                        <div class="dropdown d-inline-block">

                            <a class="btn btn-sm btn-light border"
                                data-bs-toggle="dropdown">

                                <i class="bi bi-three-dots"></i>

                            </a>

                            <ul class="dropdown-menu dropdown-menu-end">

                                <li>
                                    <a class="dropdown-item"
                                        href="../data_files/?view=course_contents_management&course_id=${course.id}">

                                        View Details
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item"
                                        href="../data_files/?view=edit_course&course_id=${course.id}">

                                        Edit Course
                                    </a>
                                </li>

                            </ul>

                        </div>

                    </td>

                </tr>
                `;
            });
        }

        // =========================
        // LOAD HTML
        // =========================
        $('#dataTable tbody').html(html);

        // =========================
        // INIT DATATABLE
        // =========================
        $('#dataTable').DataTable({

            destroy: true,

            responsive: true,

            autoWidth: false,

            pageLength: 5,

            lengthMenu: [5, 10, 25, 50],

            order: [[0, "asc"]],

            language: {

                search: "_INPUT_",

                searchPlaceholder: "Search courses...",

                lengthMenu: "Show _MENU_ courses",

                info: "Showing _START_ to _END_ of _TOTAL_ courses",

                paginate: {
                    previous: "‹",
                    next: "›"
                }
            }
        });

    })

    .catch(err => {

        console.error(err);

    });
}


document.addEventListener("DOMContentLoaded", function(){
  
    const form = document.querySelector("form.needs-validation");
 
    form.addEventListener("submit", function(e){

        e.preventDefault();

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        let courseName = document.getElementById("validationTooltip01").value;

        Swal.fire({
            title: "Saving...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch("ajax/ajax_save_course.php", { 
            method: "POST",
            headers: {
                "Content-Type": "application/json" 
            },
            body: JSON.stringify({
                title: courseName 
            })
        })
        .then(res => res.json()) 
        .then(res => {

            Swal.close();

            if (res.status === "success") {

                Swal.fire("Success", res.message, "success")
                .then(() => {
                    window.location.href = "?view=my_courses_online_contents_list_view";
                });

            } else {
                Swal.fire("Error", res.message, "error");
            }

        })
        .catch(() => {
            Swal.fire("Error","Something went wrong","error");
        });

    });

});

</script>