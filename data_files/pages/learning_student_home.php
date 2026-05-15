<div class="container mt-4" id="main-content">
                <div class="row align-items-center">
                    <div class="col-12 col-md-6 col-lg-9 mb-4">
                        <h1>Hi, <span class="text-theme-1"><?php echo $fullname; ?></span></h1>
                        <h2 class="mb-4">Start your learning today</h2>
                        <p>"The only way to do great work is to love what you do."<br><span class="text-secondary">-
                                Steve Jobs</span></p>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="swiper swipernav">
                            <div class="swiper-wrapper" id="popularCourses"></div>
                        </div>
                    </div>
                </div>
                <!-- <div class="row">
                    <div id="progressContainer"></div>
                </div> -->

                 <div class="card adminuiux-card shadow-sm mb-4">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6>Subscribed Courses</h6>
                            </div>
                            <div class="col-auto px-0">
                                <select class="form-select form-select-sm" onchange="filterCourses(this.value)">
                                    <option value="all">All Course</option>
                                    <option value="completed">Completed</option>
                                    <option value="progress">In-Progress</option>
                                    <option value="not_started">Not Started</option>
                                </select>
                            </div>
                            <div class="col-auto"><button class="btn btn-sm btn-square btn-link"><i
                                        class="bi bi-arrow-clockwise"></i></button></div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                    <div class="table-responsive" style="overflow: visible;">
                        <table class="table table-bordered table-hover align-middle mb-0" id="dataTable">

                            <!-- HEADER -->
                            <thead class="table-light text-uppercase small">
                                <tr>
                                    <th class="fw-semibold">Course</th>
                                    <th class="fw-semibold">Contents</th>
                                    <th class="fw-semibold">Status</th>
                                    <th class="fw-semibold text-center">Chat</th>
                                    <th class="fw-semibold text-left" style="padding-left: 80px;">Action</th>
                                </tr>
                            </thead>
                            <!-- BODY -->
                            <tbody id="courseTableBody">
                                <!-- dynamic data -->
                            </tbody>

                        </table>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-12 col-md-6 col-lg-12">
                        <div class="card adminuiux-card shadow-sm mb-4">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <h5 class="mb-2">Top courses list
                                        <span class="badge badge-sm badge-light text-bg-success">New</span>
                                    </h5>
                                </div>
                            </div>
                            <div class="card-body">

                               <div class="row mb-3">
                                    <div class="col-md-4">
                                        <input type="text" id="searchCourse" class="form-control" placeholder="Search courses...">
                                    </div>

                                    <div class="col-md-3">
                                        <select id="filterPrice" class="form-control">
                                            <option value="">All Prices</option>
                                            <option value="free">Free</option>
                                            <option value="paid">Paid</option>
                                        </select>
                                    </div>
                               </div>

                               <div class="swiper swipernavpagination mb-3">
                                    <div class="swiper-wrapper mb-3" id="coursesListView">
                                        <!-- Dynamic courses will load here -->
                                    </div>
                                    <div class="swiper-pagination"></div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- <div  id="studentDashboard" class="col-12 col-md-6 col-lg-3 mb-4"></div> -->
              
                </div>

               
                
            </div>

<style>

/* Smooth borders */
.table {
    border-color: #e9ecef;
}

/* Row hover effect */
.table-hover tbody tr:hover {
    background-color: #f8f9fa;
    transition: 0.2s;
}

/* Header styling */
thead th {
    font-size: 12px;
    letter-spacing: 0.5px;
}

/* Cell padding */
.table td, .table th {
    padding: 14px 12px;
    vertical-align: middle;
}

/* Rounded table edges */
.card {
    border-radius: 12px;
    overflow: hidden;
}

/* Action buttons spacing */
.table .btn {
    padding: 4px 10px;
    font-size: 13px;
}
</style>

<script>
$(document).ready(function()
{
    loadPopularCourses();
    loadCourseProgress();
    loadSubscribedCourses();
    loadStudentDashboard();
});


function loadStudentDashboard(){

    fetch("ajax/ajax_fetch_student_dashboard.php")
    .then(res => res.json())
    .then(res => {

        if(res.status !== "success"){
            document.getElementById("studentDashboard").innerHTML = "Error loading";
            return;
        }

        let s = res.student;
        let stats = res.stats;
        let skills = res.skills;

        let image = s.image ? s.image : "assets/img/user.png";

        // 🎯 SKILLS UI
        let skillHTML = "";
        let colors = ["green","yellow","purple","secondary"];

        skills.forEach((skill, i) => {
            let percent = Math.floor(Math.random()*40)+20; // demo %
            skillHTML += `
            <div class="col-6 mb-3">
                <p class="small">
                    <span class="me-1 avatar avatar-10 rounded-circle bg-${colors[i%colors.length]}"></span>
                    ${skill}
                    <span class="text-success ms-1">${percent}%</span>
                </p>
            </div>`;
        });

        // 🎯 MAIN HTML
        let html = `
        

            <div class="card shadow-sm">

                <div class="card-header bg-gradient text-black"
                     style="background: linear-gradient(45deg,#4facfe,#00f2fe);">
                    <div class="d-flex justify-content-between">
                        <h6>${s.first_name} ${s.last_name}</h6>
                        <small>Level ${s.main_academic_level || 'N/A'}</small>
                    </div>
                </div>

                <div class="card-body text-center">

                    <img src="${image}" 
                         class="rounded-circle mb-3"
                         width="80" height="80">

                    <p class="small text-muted">${s.school || ''}</p>

                    <div class="row text-start">
                        ${skillHTML}
                    </div>

                </div>
            </div>

            <!-- STATS -->
            <div class="row gx-2 text-center mt-2">

                <div class="col-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <i class="bi bi-mortarboard text-primary"></i>
                            <h5>${stats.courses}</h5>
                            <small>Paid Courses</small>
                        </div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <i class="bi bi-patch-check text-success"></i>
                            <h5>${stats.completed}</h5>
                            <small>Completed</small>
                        </div>
                    </div>
                </div>

            </div>

        `;

        document.getElementById("studentDashboard").innerHTML = html;

    });
}

function filterCourses(type){

    const rows = document.querySelectorAll("#dataTable tbody tr");

    rows.forEach(row => {

        const text = row.innerText.toLowerCase();

        if(type === "completed" && !text.includes("completed")){
            row.style.display = "none";
        }
        else if(type === "progress" && !text.includes("%")){
            row.style.display = "none";
        }
        else if(type === "not_started" && text.includes("%")){
            row.style.display = "none";
        }
        else{
            row.style.display = "";
        }
    });
}


function startCourse(course_id){
    window.location.href = "../data_files/?view=read_course_details_data&course_id=" + course_id;
}

function loadSubscribedCourses(){

    fetch("ajax/ajax_fetch_subscribed_courses.php")
    .then(res => res.json())
    .then(res => {

        if(res.status !== "success"){
            document.querySelector("#dataTable tbody").innerHTML =
                `<tr><td colspan="6">No courses found</td></tr>`;
            return;
        }

        let html = "";

        res.data.forEach(course => {

            let progressColor = course.progress == 100
                ? "bg-success"
                : (course.progress > 0 ? "bg-warning" : "bg-secondary");

            let thumbnail = course.thumbnail 
                ? course.thumbnail 
                : "uploads/course_default.png";

            html += `
            <tr>

                <!-- COURSE -->
                <td>
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar avatar-40 coverimg rounded">
                                <img src="${thumbnail}">
                            </div>
                        </div>
                        <div class="col">
                            <h6 class="mb-0">${course.title}</h6>
                            <p class="text-secondary small">Course</p>
                        </div>
                    </div>
                </td>

                <!-- DURATION -->
                <td>
                    <p class="mb-0">${course.total_lessons} Lessons</p>
                    <p class="small text-secondary">${course.total_chapters} Chapters</p>
                </td>

                <!-- STATUS -->
                <td>
                    ${
                        course.progress == 100
                        ? `<span class="badge bg-success">Completed</span>`
                        : `
                        <p class="mb-2">${course.progress}%</p>
                        <div class="progress" style="height:5px;">
                            <div class="progress-bar ${progressColor}" 
                                 style="width:${course.progress}%">
                            </div>
                        </div>`
                    }
                </td>

                <!-- CHAT -->
                <td>
                    <button class="btn btn-sm btn-link">
                        ${course.discussions} 
                        <i class="bi bi-chat-text"></i>
                    </button>
                </td>

                <!-- ACTION -->
                <td>
                    <button onclick="startCourse(${course.course_id})"
                        class="btn btn-sm btn-outline-success">
                        ${course.progress > 0 ? "Continue" : "Start"}
                    </button>

                    <button onclick="startCourse(${course.course_id})"
                        class="btn btn-sm btn-outline-primary">
                        Invite Others
                    </button>

                    <div class="dropdown d-inline-block">
                        <a class="btn btn-sm btn-link btn-square no-caret"
                           data-bs-toggle="dropdown">
                           <i class="bi bi-three-dots"></i>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" 
                                   href="../data_files/?view=read_course_details_data&course_id=${course.course_id}">
                                   View Details
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" 
                                   href="../data_files/?view=invitation_COntroller_Page&course_id=${course.course_id}">
                                   Invite Others
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>

            </tr>
            `;
        });

        document.querySelector("#dataTable tbody").innerHTML = html;

    })
    .catch(err => {
        console.error(err);
    });
}

function initCircleProgress(){
    document.querySelectorAll('.circleProgress').forEach(el => {

        let percent = el.getAttribute("data-progress");

        el.innerHTML = `
            <div style="
                width:50px;
                height:50px;
                border-radius:50%;
                border:4px solid #eee;
                position:relative;
                text-align:center;
                line-height:42px;
                font-size:12px;
                font-weight:bold;
            ">
                ${percent}%
            </div>
        `;
    });
}
//load lesson and course progress 
function loadCourseProgress(){

    fetch("ajax/ajax_course_progress.php")
    .then(res => res.json())
    .then(res => {

        if(res.status !== "success"){
            document.getElementById("progressContainer").innerHTML = "No courses found";
            return;
        }

        let html = `<div class="row">`;

        res.data.forEach(course => {

            html += `
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card adminuiux-card shadow-sm mb-4">

                    <div class="card-body">

                        <!-- HEADER -->
                        <div class="row align-items-center mb-3">

                            <div class="col-auto">
                                <div class="avatar avatar-50 rounded bg-theme-1-subtle text-theme-1">
                                    <b>${course.progress}%</b>
                                </div>
                            </div>

                            <div class="col">
                                <h6 class="mb-0">${course.title}</h6>
                                <p class="small text-secondary">Course</p>
                            </div>

                        </div>

                        <!-- CHAPTER PROGRESS -->
                        <p class="text-secondary small mb-0">
                            You have completed 
                            ${course.completed_chapters}/${course.total_chapters} chapters
                        </p>

                        <!-- LESSON PROGRESS -->
                        <p class="text-secondary small mb-0">
                            ${course.watched_lessons}/${course.total_lessons} lessons completed
                        </p>

                        <!-- PROGRESS BAR -->
                        <div class="progress mt-2" style="height:5px;">
                            <div class="progress-bar bg-theme-1"
                                 style="width:${course.progress}%">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            `;
        });

        html += `</div>`;

        document.getElementById("progressContainer").innerHTML = html;

    })
    .catch(err => {
        console.error(err);
        document.getElementById("progressContainer").innerHTML = "Error loading progress";
    });
}
//load popular courses
function loadPopularCourses(){

    fetch("ajax/ajax_fetch_popular_courses.php")
    .then(res => res.json())
    .then(res => {

        if(res.status !== "success"){
            document.getElementById("popularCourses").innerHTML = "No courses";
            return;
        }

        let html = "";

        res.data.forEach(course => {

            let thumbnail = course.thumbnail 
                ? course.thumbnail 
                : "uploads/course_default.png";

            html += `
            <div class="swiper-slide pb-4">
                <div class="card adminuiux-card shadow-sm overflow-hidden">

                    <div class="card-body">

                        <!-- IMAGE -->
                        <figure class="height-120 w-100 coverimg rounded mb-3">
                            <img src="${thumbnail}" style="width:100%;height:100%;object-fit:cover;">
                        </figure>

                        <!-- TITLE -->
                        <h6 class="mb-0 text-truncated">
                            ${course.title}
                        </h6>

                        <p class="small text-secondary">
                            Course
                        </p>

                        <!-- LIKES -->
                        <div class="row gx-3 align-items-center">

                            <div class="col">
                                <p class="text-secondary small mb-0">
                                    ❤️ ${course.total_likes} Likes
                                </p>
                            </div>

                            <div class="col-auto">
                                <a href="course-details.php?id=${course.id}" 
                                   class="btn btn-sm btn-success">
                                    View <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>

                        </div>

                    </div>

                    <!-- PROGRESS BAR STYLE -->
                    <div class="progress position-absolute w-100 bottom-0 start-0 height-dynamic"
                        style="--h-dynamic: 5px">
                        <div class="progress-bar bg-success" 
                             style="width: ${Math.min(course.total_likes * 5, 100)}%">
                        </div>
                    </div>

                </div>
            </div>
            `;
        });

        document.getElementById("popularCourses").innerHTML = html;

        // ✅ INIT SWIPER
        setTimeout(() => {
            new Swiper('.swipernav', {
                slidesPerView: 'auto',
                spaceBetween: 15
            });
        }, 200);

    })
    .catch(err => {
        console.error(err);
    });
}
//search filter 
document.getElementById("searchCourse").addEventListener("keyup", loadCourses);
document.getElementById("filterPrice").addEventListener("change", loadCourses);

function loadCourses(){

    let search = document.getElementById("searchCourse").value;
    let priceFilter = document.getElementById("filterPrice").value;

    fetch(`ajax/ajax_get_courses.php?search=${search}&price=${priceFilter}`)
    .then(res => res.json())
    .then(renderCourses);
}

//add course to wish list 
function toggleWishlist(courseId){

    fetch("ajax/ajax_toggle_wishlist.php",{
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body: JSON.stringify({course_id: courseId})
    })
    .then(res=>res.json())
    .then(res=>{
        Swal.fire("Success", res.message, "success").then(()=>{
           loadCourses();
        });
    });
}

//add course to cart 
function addToCart(courseId){

    fetch("ajax/ajax_add_to_cart.php",{
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body: JSON.stringify({course_id: courseId})
    })
    .then(res=>res.json())
    .then(res=>{
        Swal.fire("Success", res.message, "success").then(()=>{
            window.location.href = "../data_files/?view=view_my_cart_to_pay";
        });
    });
}
//rate course 

function submitRating(courseId){

    let rating = document.getElementById("ratingValue").value;

    fetch("ajax/ajax_rate_course.php",{
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body: JSON.stringify({
            course_id: courseId,
            rating: rating
        })
    })
    .then(res=>res.json())
    .then(res=>{
        Swal.fire("Success", res.message, "success").then(()=>{
            loadCourses();
        });
    });
}

//load all courses list
document.addEventListener("DOMContentLoaded", function(){
    loadCourses();
});

//fetch courses 
function loadCourses(){

    fetch("ajax/ajax_fetch_courses_list.php")
    .then(res => res.json())
    .then(res => {
      
        let container = document.getElementById("coursesListView");

        if(res.status !== "success"){
            container.innerHTML = "<p>Error loading courses</p>";
            return;
        }

        let html = "";

        res.data.forEach(course => {

            let price = parseFloat(course.price || 0);
            let discount = parseFloat(course.discount || 0);
            let finalPrice = price - (price * discount / 100);

            // ✅ FIX THUMBNAIL (MAIN ISSUE)

            thumbnail = course.thumbnail 
                ? course.thumbnail + "?v=" + new Date().getTime()
                : "../data_files/uploads/course_default.png";

            // // 👉 Fix relative path (VERY IMPORTANT)
            // if(!thumbnail.startsWith("http")){
            //     thumbnail = "../data_files/" + thumbnail;
            // }


            // ✅ FIX RATINGS (avoid NaN)
            let rating = parseFloat(course.avg_rating || 0).toFixed(1);
            let reviews = course.total_reviews || 0;

            html += `
            <div class="swiper-slide width-250">
                <div class="card adminuiux-card shadow-sm mb-4">

                    <!-- HEADER -->
                    <div class="card-header">
                        <a href="course-details.php?id=${course.id}" class="style-none">
                            <h6 class="mb-0 text-truncated">${course.title}</h6>
                            <p class="small text-secondary">Course</p>
                        </a>
                    </div>

                    <!-- BODY -->
                    <div class="card-body pt-0 position-relative">

                        <!-- IMAGE -->
                        <div class="height-180 w-100 rounded coverimg position-relative mb-3">
                            <img src="${thumbnail}" 
                                onerror="this.src='uploads/course_default.png';"
                                style="width:100%; height:100%; object-fit:cover;">
                        </div>

                        <!-- PRICE -->
                            <div class="row align-items-center mb-2">
                                <div class="col">
                                    <h5 class="mb-0">
                                        ${new Intl.NumberFormat('en-TZ', {
                                            style: 'currency',
                                            currency: 'TZS',
                                            minimumFractionDigits: 0
                                        }).format(finalPrice)}

                                        ${discount > 0 ? `
                                            <s class="opacity-50 fs-14">
                                                ${new Intl.NumberFormat('en-TZ', {
                                                    style: 'currency',
                                                    currency: 'TZS',
                                                    minimumFractionDigits: 0
                                                }).format(price)}
                                            </s>
                                        ` : ``}
                                    </h5>

                                    <!-- ⭐ RATING -->
                                    <p class="text-secondary mb-1">
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <b>${parseFloat(rating).toFixed(1)}</b>
                                        <small>(${reviews} Reviews)</small>
                                    </p>
                                </div>
                            </div>
                        <!-- ACTIONS -->
                        <div class="row align-items-center">
                            <div class="col">

                                <!-- ❤️ Wishlist -->
                                <button onclick="toggleWishlist(${course.id})" 
                                    class="btn btn-sm btn-square btn-outline-danger">
                                    <i class="bi bi-heart"></i>
                                </button>

                                <!-- 🔄 View More -->
                                <a href="../data_files/?view=view_course_details&course_id=${course.id}" class="btn btn-sm btn-square btn-outline-theme rounded-circle theme-skyblue me-1">
                                    <i class="bi bi-eye"></i>
                                </a>

                            </div>

                            <div class="col-auto">

                                <!-- 🛒 Cart -->
                                <button class="btn btn-sm btn-square btn-outline-primary" onclick="addToCart(${course.id})">
                                    <i class="bi bi-cart"></i>
                                </button>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
            `;
        });

        container.innerHTML = html;

        // ✅ FIX: destroy old swiper before re-init
        if (window.mySwiper) {
            window.mySwiper.destroy(true, true);
        }

        // ✅ Re-init Swiper
        setTimeout(() => {
            window.mySwiper = new Swiper('.swipernavpagination', {
                slidesPerView: 'auto',
                spaceBetween: 15,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true
                }
            });
        }, 200);

    })
    .catch(err => {
        console.error(err);
        document.getElementById("coursesListView").innerHTML = "<p>Error loading courses</p>";
    });
}
</script>