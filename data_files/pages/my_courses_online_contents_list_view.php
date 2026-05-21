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

<style>
.course-card { transition: transform .18s ease, box-shadow .18s ease; border-radius: 14px !important; overflow: hidden; }
.course-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,.12) !important; }
.course-thumb { height: 170px; background-color: #e9ecef; position: relative; }
.course-thumb .status-badge { position: absolute; top: 10px; left: 10px; }
.course-thumb .price-badge  { position: absolute; top: 10px; right: 10px; }
.stat-pill { display: flex; align-items: center; gap: 5px; font-size: .78rem; color: #64748b; }
.stat-pill i { font-size: .85rem; }
.rating-stars { color: #f59e0b; font-size: .8rem; }
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    loadCourses();
});

function loadCourses() {
    const container = document.getElementById("coursesContainer");
    container.innerHTML = `
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="text-muted small mt-2">Loading courses…</p>
        </div>`;

    fetch("ajax/ajax_get_courses.php")
        .then(r => r.json())
        .then(res => {
            container.innerHTML = "";

            if (res.status !== "success" || !res.data.length) {
                container.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-collection fs-1 text-muted opacity-50 d-block mb-3"></i>
                        <p class="text-muted">No courses yet. Create your first course to get started.</p>
                    </div>`;
                return;
            }

            res.data.forEach(c => {
                const thumb  = c.thumbnail
                    ? `uploads/${c.thumbnail.split('/').pop()}`
                    : 'uploads/course_default.png';

                const status = { active: ['bg-success','Published'], is_draft: ['bg-secondary','Draft'], inactive: ['bg-danger','Inactive'] }[c.status] ?? ['bg-secondary','Unknown'];
                const price  = parseFloat(c.price) > 0
                    ? `TZS ${Number(c.price).toLocaleString()}`
                    : 'Free';
                const priceClass = parseFloat(c.price) > 0 ? 'bg-dark' : 'bg-success';

                const rating = c.avg_rating
                    ? `<span class="rating-stars">${'★'.repeat(Math.round(c.avg_rating))}${'☆'.repeat(5 - Math.round(c.avg_rating))}</span>
                       <span class="ms-1 fw-medium text-dark">${c.avg_rating}</span>
                       <span class="text-muted">(${c.rating_count})</span>`
                    : `<span class="text-muted">No ratings yet</span>`;

                const created = c.created_at
                    ? new Date(c.created_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' })
                    : '—';

                container.innerHTML += `
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-sm course-card mb-4">

                        <!-- Thumbnail -->
                        <div class="course-thumb"
                             style="background:url('${thumb}') center/cover no-repeat;">
                            <span class="badge ${status[0]} status-badge">${status[1]}</span>
                            <span class="badge text-white ${priceClass} price-badge">${price}</span>
                        </div>

                        <!-- Body -->
                        <div class="card-body pb-2 pt-3">
                            <h6 class="fw-semibold mb-1 lh-sm">${c.title}</h6>
                            <div class="d-flex align-items-center gap-1 mb-3 small">
                                ${rating}
                            </div>

                            <!-- Stats row -->
                            <div class="d-flex flex-wrap gap-3 pb-3 border-bottom">
                                <div class="stat-pill">
                                    <i class="bi bi-collection text-primary"></i>
                                    <span><strong>${c.chapters}</strong> Chapter${c.chapters != 1 ? 's' : ''}</span>
                                </div>
                                <div class="stat-pill">
                                    <i class="bi bi-play-circle text-info"></i>
                                    <span><strong>${c.lessons}</strong> Lesson${c.lessons != 1 ? 's' : ''}</span>
                                </div>
                                <div class="stat-pill">
                                    <i class="bi bi-people text-success"></i>
                                    <span><strong>${c.enrolled}</strong> Enrolled</span>
                                </div>
                                <div class="stat-pill">
                                    <i class="bi bi-journal-bookmark text-warning"></i>
                                    <span><strong>${c.study_notes}</strong> Note${c.study_notes != 1 ? 's' : ''}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="card-footer bg-transparent d-flex align-items-center justify-content-between py-2">
                            <span class="small text-muted"><i class="bi bi-calendar3 me-1"></i>${created}</span>
                            <a href="./?view=course_contents_management&course_id=${encodeURIComponent(c.course_token)}"
                               class="btn btn-theme btn-sm px-3">
                                Manage <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>

                    </div>
                </div>`;
            });
        })
        .catch(() => {
            document.getElementById("coursesContainer").innerHTML =
                `<p class="text-danger text-center py-4">Failed to load courses.</p>`;
        });
}
</script>