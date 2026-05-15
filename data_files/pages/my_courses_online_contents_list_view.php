<div class="container mt-4">
    <div class="row align-items-center">
        <div class="col-12 col-md">
            <h5>E Courses</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-3 mb-md-0">
                    <li class="breadcrumb-item bi"><a href="../data_files/?view=3002">Dashboard</a></li>
                    <li class="breadcrumb-item active bi" aria-current="page">Courses</li>
                </ol>
            </nav>
        </div>
        <div class="col-12 col-md-auto"></div>
    </div>
</div>
<div class="container mt-4" id="main-content">
    <div class="swiper swipernav">
        <div class="swiper-wrapper">
            <div class="swiper-slide">
                <div class="card adminuiux-card bg-theme-1-subtle mb-4">
                    <div class="card-body py-0 position-relative">
                        <div class="row">
                            <div class="col-12 col-sm py-3 py-lg-4 px-lg-4 align-self-center">
                                <h2 class="mb-0 text-theme-1">Best Online Courses</h2>
                                <h5>Learn unstoppable from anywhere</h5>
                                <p class="text-secondary">With Personal Subscription, you get access to
                                    10,000+ of our top courses in industry, business, and more...</p><a
                                    href="#" class="btn btn-theme">Learn More</a>
                            </div>
                            <div class="col-12 col-sm align-self-end"><img
                                    src="../assets/img/learning/banner.png" alt=""
                                    class="mw-100 rounded mt-0 mt-sm-4"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="swiper-slide">
                <div class="card adminuiux-card bg-theme-1-subtle mb-4">
                    <div class="card-body py-0 position-relative">
                        <div class="row">
                            <div class="col-12 col-sm py-3 py-lg-4 px-lg-4 align-self-center">
                                <h2 class="mb-0 text-theme-1">Design that inspires</h2>
                                <h5>You are at the best place</h5>
                                <p class="text-secondary">With Personal Subscription, you get access to
                                    10,000+ of our top courses in industry, business, and more...</p><a
                                    href="#" class="btn btn-theme">Learn More</a>
                            </div>
                            <div class="col-12 col-sm align-self-end"><img
                                    src="../assets/img/learning/banner.png" alt=""
                                    class="mw-100 rounded mt-0 mt-sm-4"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
   
    <h5>My Course Progress</h5>
    <p class="text-secondary">Start from where you have left</p>
    <div class="row">
        <div class="row" id="coursesContainer"></div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function(){

    loadCourses();

    function loadCourses(){

        fetch("ajax/ajax_get_courses.php")
        .then(res => res.json())
        .then(res => {

            let container = document.getElementById("coursesContainer");
            container.innerHTML = "";

            if(res.status === "success"){

                if(res.data.length === 0){
                    container.innerHTML = `<p class="text-center">No courses found</p>`;
                    return;
                }

                res.data.forEach(course => {

                    let card = `
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card adminuiux-card shadow-sm mb-4">
                            <div class="card-body">
                                <div class="row align-items-center mb-3">
                                    <div class="col-auto">
                                        <div class="avatar avatar-50 coverimg rounded">
                                            <img src="./${course.thumbnail}">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h6 class="mb-0">${course.title}</h6>
                                        <p class="small">Instructor ID: ${course.instructor_id}</p>
                                    </div>
                                </div>

                                <div class="row gx-4 align-items-center">
                                    <div class="col">
                                        <p class="text-secondary small mb-0">0 chapters</p>
                                        <p class="text-secondary small mb-0">0 assignments</p>
                                    </div>
                                    <div class="col-auto">
                                        <a href="./?view=course_contents_management&course_id=${course.id}&courseTitle=${course.title}" class="btn btn-theme btn-sm">
                                            View Course <i class="bi bi-play"></i>
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    `;

                    container.innerHTML += card;

                });

            } else {
                container.innerHTML = `<p class="text-danger">${res.message}</p>`;
            }

        })
        .catch(() => {
            document.getElementById("coursesContainer").innerHTML =
                `<p class="text-danger">Failed to load courses</p>`;
        });

    }

});
</script>