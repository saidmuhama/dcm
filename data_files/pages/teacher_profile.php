<?php
$emailAddress = @App::getWhatFromWHere('email_address','tbl_all_users', 'usr_code',$usr_code);
$phoneNumber  = @App::getWhatFromWHere('phone_number','tbl_all_users', 'usr_code',$usr_code);
$user_status  = @App::getWhatFromWHere('user_status','tbl_all_users', 'usr_code',$usr_code);
$city         = @App::getWhatFromWHere('city','tbl_tutors', 'usr_code',$usr_code);
$country      = @App::getWhatFromWHere('country','tbl_tutors','usr_code',$usr_code);
$description  = @App::getWhatFromWHere('description','tbl_tutors','usr_code',$usr_code);
$course       = @App::getWhatFromWHere('course','tbl_tutors','usr_code',$usr_code);
$salutation   = @App::getWhatFromWHere('main_academic_level','tbl_tutors','usr_code',$usr_code);
$university   = @App::getWhatFromWHere('sub_academic_level','tbl_tutors','usr_code',$usr_code);
$start_year   = @App::getWhatFromWHere('start_year','tbl_tutors','usr_code',$usr_code);
?>

<style>
/* ── Hero ── */
.inst-hero {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 40%, #0f3460 100%);
    padding: 2.5rem 0 4rem;
    position: relative;
    overflow: hidden;
}
.inst-hero::before {
    content: '';
    position: absolute; inset: 0;
    background-image: radial-gradient(circle at 20% 50%, rgba(99,102,241,.18) 0%, transparent 60%),
                      radial-gradient(circle at 80% 20%, rgba(168,85,247,.12) 0%, transparent 50%);
    pointer-events: none;
}
.inst-hero::after {
    content: '';
    position: absolute; inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='20' cy='20' r='1.5'/%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}
.inst-avatar-wrap {
    width: 100px; height: 100px;
    border-radius: 50%;
    border: 3px solid rgba(255,255,255,.25);
    box-shadow: 0 8px 32px rgba(0,0,0,.4);
    overflow: hidden;
    flex-shrink: 0;
}
.inst-avatar-wrap img { width: 100%; height: 100%; object-fit: cover; }
.inst-hero .badge-role {
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.2);
    color: #c7d2fe;
    font-size: .75rem;
    padding: .35rem .75rem;
    border-radius: 20px;
    backdrop-filter: blur(4px);
}
.inst-hero .stat-pill {
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 12px;
    padding: .5rem 1rem;
    text-align: center;
    backdrop-filter: blur(4px);
    min-width: 90px;
}
.inst-hero .stat-pill .val { font-size: 1.1rem; font-weight: 700; color: #fff; }
.inst-hero .stat-pill .lbl { font-size: .68rem; color: rgba(255,255,255,.55); text-transform: uppercase; letter-spacing: .5px; }

/* ── Canvas ── */
.inst-canvas {
    max-width: 1280px;
    margin: -2.5rem auto 0;
    padding: 0 1rem 2rem;
    position: relative;
    z-index: 10;
}

/* ── Metric cards ── */
.metric-card {
    background: #fff;
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
    border: 1px solid rgba(0,0,0,.05);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform .2s, box-shadow .2s;
}
.metric-card:hover { transform: translateY(-3px); box-shadow: 0 6px 24px rgba(0,0,0,.1); }
.metric-icon {
    width: 52px; height: 52px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; flex-shrink: 0;
}
.metric-val { font-size: 1.5rem; font-weight: 700; line-height: 1; color: #1e293b; }
.metric-lbl { font-size: .78rem; color: #64748b; margin-top: .2rem; }
.metric-trend { font-size: .72rem; font-weight: 600; }
.metric-trend.up { color: #16a34a; }
.metric-trend.neutral { color: #64748b; }

/* ── Profile card ── */
.profile-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
    border: 1px solid rgba(0,0,0,.05);
    overflow: hidden;
}
.profile-card .prof-banner {
    height: 70px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
}
.profile-card .prof-body { padding: 0 1.25rem 1.25rem; }
.profile-card .prof-avatar {
    width: 72px; height: 72px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,.15);
    margin-top: -36px;
    overflow: hidden;
}
.profile-card .prof-avatar img { width: 100%; height: 100%; object-fit: cover; }
.prof-info-row { display: flex; align-items: center; gap: .5rem; font-size: .82rem; color: #64748b; padding: .3rem 0; border-bottom: 1px solid #f1f5f9; }
.prof-info-row:last-child { border-bottom: none; }
.prof-info-row i { width: 16px; color: #94a3b8; }

/* ── Section card ── */
.sect-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
    border: 1px solid rgba(0,0,0,.05);
    overflow: hidden;
    margin-bottom: 1.25rem;
}
.sect-card .sect-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex; align-items: center; gap: .75rem;
}
.sect-card .sect-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
}
.sect-card .sect-body { padding: 1.25rem; }
.sect-card .sect-title { font-size: .92rem; font-weight: 600; color: #1e293b; margin: 0; }
.sect-card .sect-sub { font-size: .75rem; color: #94a3b8; }

/* ── Course analytics cards ── */
.course-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid rgba(0,0,0,.07);
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
    overflow: hidden;
    transition: transform .2s, box-shadow .2s;
    height: 100%;
}
.course-card:hover { transform: translateY(-4px); box-shadow: 0 10px 30px rgba(0,0,0,.1); }
.course-card .cc-thumb {
    height: 160px; overflow: hidden; position: relative;
}
.course-card .cc-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s; }
.course-card:hover .cc-thumb img { transform: scale(1.05); }
.course-card .cc-status {
    position: absolute; top: .6rem; right: .6rem;
    padding: .3rem .65rem; border-radius: 20px; font-size: .7rem; font-weight: 600;
}
.course-card .cc-body { padding: 1rem; }
.course-card .cc-title { font-size: .88rem; font-weight: 600; color: #1e293b; line-height: 1.35;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.course-card .cc-meta { font-size: .75rem; color: #64748b; margin-top: .35rem; }
.course-card .cc-earn { font-size: 1.1rem; font-weight: 700; color: #16a34a; }
.course-card .cc-bar { height: 5px; border-radius: 99px; background: #f1f5f9; margin: .75rem 0 .85rem; overflow: hidden; }
.course-card .cc-bar-fill { height: 100%; border-radius: 99px; background: linear-gradient(90deg,#6366f1,#8b5cf6); }
.course-card .cc-actions { display: flex; gap: .5rem; }
.course-card .cc-btn {
    flex: 1; font-size: .75rem; font-weight: 600;
    padding: .4rem .5rem; border-radius: 8px; text-align: center;
    border: none; cursor: pointer; transition: all .15s;
}

/* ── My Courses table ── */
#instCoursesTable thead th {
    font-size: .72rem; text-transform: uppercase; letter-spacing: .6px;
    color: #94a3b8; font-weight: 600; border-bottom: 2px solid #f1f5f9;
    padding: .75rem 1rem; white-space: nowrap;
}
#instCoursesTable tbody td { padding: .85rem 1rem; vertical-align: middle; font-size: .84rem; }
#instCoursesTable tbody tr { border-bottom: 1px solid #f8fafc; transition: background .15s; }
#instCoursesTable tbody tr:hover { background: #f8f9ff; }
.cthumbnail { width: 42px; height: 42px; border-radius: 8px; object-fit: cover; }
.status-chip { padding: .3rem .7rem; border-radius: 20px; font-size: .72rem; font-weight: 600; display: inline-flex; align-items: center; gap: .3rem; }
.status-chip.active  { background: #dcfce7; color: #15803d; }
.status-chip.inactive{ background: #fee2e2; color: #dc2626; }
.status-chip.draft   { background: #fef9c3; color: #854d0e; }
.tbl-action-btn { font-size: .75rem; padding: .3rem .65rem; border-radius: 7px; font-weight: 500; }

/* ── Education card ── */
.edu-row {
    display: flex; align-items: flex-start; gap: 1rem;
    padding: .85rem 0; border-bottom: 1px solid #f1f5f9;
}
.edu-row:last-child { border-bottom: none; padding-bottom: 0; }
.edu-logo { width: 44px; height: 44px; border-radius: 10px; overflow: hidden; flex-shrink: 0;
    background: linear-gradient(135deg,#6366f1,#8b5cf6); display: flex; align-items: center; justify-content: center; }
.edu-logo img { width: 100%; height: 100%; object-fit: contain; }

/* ── Quick actions ── */
.qa-btn { border-radius: 12px; font-weight: 600; font-size: .85rem; padding: .6rem 1.2rem; }

/* ── Skeleton ── */
.skel { background: linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);
    background-size: 200% 100%; animation: skel-anim 1.5s infinite; border-radius: 8px; }
@keyframes skel-anim { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* dark mode compat */
@media (prefers-color-scheme: dark) {
    .metric-card,.profile-card,.sect-card,.course-card { background: #1e293b; border-color: rgba(255,255,255,.06); }
    .metric-val { color: #f1f5f9; }
    .metric-lbl,.sect-sub { color: #94a3b8; }
    .sect-title,.cc-title { color: #e2e8f0; }
    #instCoursesTable thead th { border-color: rgba(255,255,255,.06); }
    #instCoursesTable tbody tr { border-color: rgba(255,255,255,.04); }
    #instCoursesTable tbody tr:hover { background: rgba(99,102,241,.05); }
    .prof-info-row { border-color: rgba(255,255,255,.06); color: #94a3b8; }
    .cc-bar { background: rgba(255,255,255,.08); }
    .edu-row { border-color: rgba(255,255,255,.06); }
    .skel { background: linear-gradient(90deg,#1e293b 25%,#334155 50%,#1e293b 75%);
        background-size: 200% 100%; }
}
</style>

<!-- ═══════════════════════════════ HERO ═══════════════════════════════ -->
<div class="inst-hero">
    <div class="container-xl position-relative" style="z-index:2">

        <!-- breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0" style="font-size:.78rem">
                <li class="breadcrumb-item"><a href="../data_files/?view=3002" class="text-white-50 text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" style="color:rgba(255,255,255,.55)">Instructor Overview</li>
            </ol>
        </nav>

        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3">

            <!-- avatar -->
            <div class="inst-avatar-wrap">
                <img src="<?= htmlspecialchars($userProfileImage ?? 'assets/img/logo-512.png') ?>" alt="Profile">
            </div>

            <!-- name + meta -->
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <h4 class="text-white mb-0 fw-bold"><?= htmlspecialchars($fullname ?? '') ?></h4>
                    <span class="badge-role"><?= htmlspecialchars($roleTitle ?? 'Instructor') ?></span>
                </div>
                <div class="d-flex flex-wrap gap-3 mb-2" style="font-size:.82rem; color:rgba(255,255,255,.6)">
                    <?php if($course): ?><span><i class="bi bi-mortarboard me-1"></i><?= htmlspecialchars($course) ?></span><?php endif; ?>
                    <?php if($city || $country): ?><span><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars(trim($city.', '.$country, ', ')) ?></span><?php endif; ?>
                    <?php if($emailAddress): ?><span><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($emailAddress) ?></span><?php endif; ?>
                </div>
                <!-- hero stat pills -->
                <div class="d-flex flex-wrap gap-2 mt-1" id="heroStatPills">
                    <div class="stat-pill"><div class="val" id="hsCourses">—</div><div class="lbl">Courses</div></div>
                    <div class="stat-pill"><div class="val" id="hsStudents">—</div><div class="lbl">Students</div></div>
                    <div class="stat-pill"><div class="val" id="hsEarnings">—</div><div class="lbl">Earnings</div></div>
                    <div class="stat-pill"><div class="val" id="hsSales">—</div><div class="lbl">Sales</div></div>
                </div>
            </div>

            <!-- CTAs -->
            <div class="d-flex gap-2 flex-shrink-0 flex-wrap">
                <a href="../data_files/?view=teacher_profile_completion" class="btn btn-light qa-btn">
                    <i class="bi bi-pencil me-1"></i> Edit Profile
                </a>
                <a href="../data_files/?view=my_courses_online_contents_list_view" class="btn btn-outline-light qa-btn">
                    <i class="bi bi-collection-play me-1"></i> My Courses
                </a>
                <a data-bs-toggle="modal" data-bs-target="#addCourseModal"
                   href="../data_files/?view=create_new_course"
                   class="btn qa-btn" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none">
                    <i class="bi bi-plus-lg me-1"></i> New Course
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════ CANVAS ════════════════════════════ -->
<div class="inst-canvas">

    <!-- ── Metric cards row ── -->
    <div class="row g-3 mb-4" id="metricCards">
        <?php
        $metrics = [
            ['id'=>'mc0','icon'=>'bi-collection-play','color'=>'#6366f1','bg'=>'#eef2ff','label'=>'Total Courses','val'=>'—','trend'=>''],
            ['id'=>'mc1','icon'=>'bi-people','color'=>'#0ea5e9','bg'=>'#e0f2fe','label'=>'Total Students','val'=>'—','trend'=>''],
            ['id'=>'mc2','icon'=>'bi-currency-exchange','color'=>'#16a34a','bg'=>'#dcfce7','label'=>'Net Earnings (TZS)','val'=>'—','trend'=>''],
            ['id'=>'mc3','icon'=>'bi-cart-check','color'=>'#f59e0b','bg'=>'#fef3c7','label'=>'Total Sales','val'=>'—','trend'=>''],
        ];
        foreach($metrics as $m): ?>
        <div class="col-6 col-lg-3">
            <div class="metric-card">
                <div class="metric-icon" style="background:<?= $m['bg'] ?>;color:<?= $m['color'] ?>">
                    <i class="bi <?= $m['icon'] ?>"></i>
                </div>
                <div>
                    <div class="metric-val" id="<?= $m['id'] ?>">
                        <span class="skel d-inline-block" style="width:60px;height:22px"></span>
                    </div>
                    <div class="metric-lbl"><?= $m['label'] ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Main two-column ── -->
    <div class="row g-4">

        <!-- LEFT column -->
        <div class="col-12 col-lg-4 col-xl-3">

            <!-- Profile card -->
            <div class="profile-card mb-4">
                <div class="prof-banner"></div>
                <div class="prof-body">
                    <div class="prof-avatar mb-2">
                        <img src="<?= htmlspecialchars($userProfileImage ?? 'assets/img/logo-512.png') ?>" alt="">
                    </div>
                    <h6 class="fw-bold mb-0"><?= htmlspecialchars($fullname ?? '') ?></h6>
                    <div class="text-muted small mb-2"><?= htmlspecialchars($roleTitle ?? 'Instructor') ?></div>
                    <?php if($course): ?>
                    <span class="badge text-bg-primary mb-3" style="border-radius:20px;font-size:.72rem">
                        <i class="bi bi-mortarboard me-1"></i><?= htmlspecialchars($course) ?>
                    </span>
                    <?php endif; ?>
                    <div class="mt-1">
                        <?php if($emailAddress): ?>
                        <div class="prof-info-row"><i class="bi bi-envelope-fill"></i><?= htmlspecialchars($emailAddress) ?></div>
                        <?php endif; ?>
                        <?php if($phoneNumber): ?>
                        <div class="prof-info-row"><i class="bi bi-telephone-fill"></i><?= htmlspecialchars($phoneNumber) ?></div>
                        <?php endif; ?>
                        <?php if($city || $country): ?>
                        <div class="prof-info-row"><i class="bi bi-geo-alt-fill"></i><?= htmlspecialchars(trim($city.', '.$country, ', ')) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-sm btn-outline-secondary rounded-circle"
                                style="width:36px;height:36px" data-bs-toggle="modal" data-bs-target="#callmodal">
                            <i class="bi bi-telephone"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary rounded-circle"
                                style="width:36px;height:36px" data-bs-toggle="modal" data-bs-target="#callmodal">
                            <i class="bi bi-camera-video"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary rounded-circle"
                                style="width:36px;height:36px" data-bs-toggle="modal" data-bs-target="#chatmodal">
                            <i class="bi bi-chat-left-text"></i>
                        </button>
                        <a href="../data_files/?view=teacher_profile_completion"
                           class="btn btn-sm ms-auto" style="background:#6366f1;color:#fff;border-radius:10px;font-size:.78rem">
                            Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- About card -->
            <?php if($description): ?>
            <div class="sect-card mb-4">
                <div class="sect-header">
                    <div class="sect-icon" style="background:#f0fdf4;color:#16a34a"><i class="bi bi-person-lines-fill"></i></div>
                    <div>
                        <div class="sect-title">About</div>
                    </div>
                </div>
                <div class="sect-body">
                    <p class="text-muted small mb-2" style="line-height:1.6"><?= nl2br(htmlspecialchars($description)) ?></p>
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        <span class="badge text-bg-primary" style="border-radius:20px;font-size:.7rem">Teaching</span>
                        <span class="badge text-bg-info" style="border-radius:20px;font-size:.7rem">Online Education</span>
                        <span class="badge text-bg-success" style="border-radius:20px;font-size:.7rem">E-Learning</span>
                        <span class="badge text-bg-secondary" style="border-radius:20px;font-size:.7rem">Language</span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Education card -->
            <?php if($university || $salutation): ?>
            <div class="sect-card mb-4">
                <div class="sect-header">
                    <div class="sect-icon" style="background:#eff6ff;color:#2563eb"><i class="bi bi-mortarboard-fill"></i></div>
                    <div>
                        <div class="sect-title">Education</div>
                    </div>
                </div>
                <div class="sect-body" style="padding-top:.75rem">
                    <div class="edu-row">
                        <div class="edu-logo">
                            <img src="assets/img/logo-512.png" alt="">
                        </div>
                        <div>
                            <div class="fw-semibold" style="font-size:.85rem;color:#1e293b"><?= htmlspecialchars($university ?? '') ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($course ?? '') ?></div>
                            <div class="text-muted" style="font-size:.72rem"><?= htmlspecialchars($salutation ?? '') ?><?= $start_year ? ' · '.$start_year : '' ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- /LEFT -->

        <!-- RIGHT column -->
        <div class="col-12 col-lg-8 col-xl-9">

            <!-- Course Analytics grid -->
            <div class="sect-card mb-4">
                <div class="sect-header justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="sect-icon" style="background:#eef2ff;color:#6366f1"><i class="bi bi-grid-1x2-fill"></i></div>
                        <div>
                            <div class="sect-title">Course Performance</div>
                            <div class="sect-sub">Sales &amp; enrollment per course</div>
                        </div>
                    </div>
                    <a href="../data_files/?view=my_courses_online_contents_list_view"
                       class="btn btn-sm btn-outline-secondary" style="font-size:.78rem;border-radius:8px">
                        View All <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="sect-body">
                    <div class="row g-3" id="courseAnalytics">
                        <!-- skeleton -->
                        <?php for($i=0;$i<3;$i++): ?>
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="course-card">
                                <div class="skel" style="height:160px;border-radius:0"></div>
                                <div class="cc-body">
                                    <div class="skel mb-2" style="height:14px;width:80%"></div>
                                    <div class="skel mb-3" style="height:12px;width:50%"></div>
                                    <div class="skel" style="height:20px;width:60%"></div>
                                </div>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- My Courses table -->
            <div class="sect-card">
                <div class="sect-header justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="sect-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-list-ul"></i></div>
                        <div>
                            <div class="sect-title">All My Courses</div>
                            <div class="sect-sub">Manage status, view details</div>
                        </div>
                    </div>
                </div>
                <div class="sect-body" style="padding:0">
                    <div class="table-responsive">
                        <table class="table mb-0" id="instCoursesTable">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Chapters</th>
                                    <th>Students</th>
                                    <th>Earnings</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="instCoursesTbody">
                                <tr><td colspan="6" class="text-center text-muted py-4">
                                    <div class="skel mx-auto" style="height:14px;width:40%"></div>
                                </td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div><!-- /RIGHT -->
    </div><!-- /row -->
</div><!-- /canvas -->

<script>
(function(){
    // prevent bundle from reiniting the table
    window.dataTables = function(){};

    /* ── helpers ── */
    function fmt(n){ return Number(n||0).toLocaleString(); }
    function fmtShort(n){
        n = Number(n||0);
        if(n >= 1e6) return (n/1e6).toFixed(1)+'M';
        if(n >= 1e3) return (n/1e3).toFixed(1)+'K';
        return n.toLocaleString();
    }

    /* ── Analytics + Metrics ── */
    function loadAnalytics(){
        fetch("ajax/ajax_fetch_instructor_courses_analytics.php")
        .then(r=>r.json()).then(res=>{
            if(res.status !== 'success') return;

            let totalCourses=res.data.length, totalStudents=0, totalEarnings=0, totalSales=0;
            res.data.forEach(c=>{
                totalStudents += parseInt(c.students||0);
                totalEarnings += parseFloat(c.net_earnings||0);
                totalSales    += parseInt(c.total_sales||0);
            });

            /* Hero pills */
            document.getElementById('hsCourses').textContent   = totalCourses;
            document.getElementById('hsStudents').textContent  = fmt(totalStudents);
            document.getElementById('hsEarnings').textContent  = 'TZS '+fmtShort(totalEarnings);
            document.getElementById('hsSales').textContent     = fmt(totalSales);

            /* Metric cards */
            document.getElementById('mc0').textContent = totalCourses;
            document.getElementById('mc1').textContent = fmt(totalStudents);
            document.getElementById('mc2').textContent = 'TZS '+fmtShort(totalEarnings);
            document.getElementById('mc3').textContent = fmt(totalSales);

            /* Course cards */
            let maxSales = Math.max(...res.data.map(c=>parseInt(c.total_sales||0)), 1);
            let html = '';
            res.data.forEach(c=>{
                let thumb  = c.thumbnail || 'assets/img/default-course.png';
                let isLive = c.status === 'active';
                let pct    = Math.round((parseInt(c.total_sales||0)/maxSales)*100);

                let statusChip = isLive
                    ? `<span class="status-chip active"><i class="bi bi-circle-fill" style="font-size:.5rem"></i> Live</span>`
                    : `<span class="status-chip draft"><i class="bi bi-circle-fill" style="font-size:.5rem"></i> Draft</span>`;

                let toggleBtn = isLive
                    ? `<button onclick="toggleStatus(${c.id},'inactive')" class="cc-btn" style="background:#fee2e2;color:#dc2626">Unpublish</button>`
                    : `<button onclick="toggleStatus(${c.id},'active')"   class="cc-btn" style="background:#dcfce7;color:#15803d">Go Live</button>`;

                html += `
                <div class="col-12 col-md-6 col-xl-4">
                  <div class="course-card">
                    <div class="cc-thumb">
                      <img src="${thumb}" alt="">
                      <div class="cc-status">${statusChip}</div>
                    </div>
                    <div class="cc-body">
                      <div class="cc-title">${c.title}</div>
                      <div class="cc-meta">${fmt(c.students)} students &middot; ${fmt(c.total_sales)} sales</div>
                      <div class="cc-earn">TZS ${fmt(c.net_earnings)}</div>
                      <div class="cc-bar"><div class="cc-bar-fill" style="width:${pct}%"></div></div>
                      <div class="cc-actions">
                        ${toggleBtn}
                        <a href="../data_files/?view=course_contents_management&course_id=${c.id}"
                           class="cc-btn" style="background:#eef2ff;color:#6366f1;text-decoration:none">
                           Manage
                        </a>
                      </div>
                    </div>
                  </div>
                </div>`;
            });

            document.getElementById('courseAnalytics').innerHTML = html || '<div class="col-12 text-center text-muted py-3">No courses yet</div>';
        }).catch(console.error);
    }

    /* ── Courses table ── */
    function loadCoursesTable(){
        fetch("ajax/ajax_fetch_instructor_courses.php")
        .then(r=>r.json()).then(res=>{

            if($.fn.DataTable.isDataTable('#instCoursesTable')) $('#instCoursesTable').DataTable().destroy();

            if(res.status !== 'success' || !res.data.length){
                document.getElementById('instCoursesTbody').innerHTML =
                    '<tr><td colspan="6" class="text-center text-muted py-4">No courses found</td></tr>';
                return;
            }

            /* We need per-course student/earnings — we'll get that from analytics cache
               stored in window.analyticsData, or fallback zeros */
            let html = '';
            res.data.forEach(c=>{
                let thumb = c.thumbnail || 'assets/img/default-course.png';
                let aData = (window._analyticsMap||{})[c.id] || {};

                let chip = '';
                if(c.status==='active')   chip = `<span class="status-chip active"><i class="bi bi-circle-fill" style="font-size:.45rem"></i> Active</span>`;
                else if(c.status==='inactive') chip = `<span class="status-chip inactive"><i class="bi bi-circle-fill" style="font-size:.45rem"></i> Inactive</span>`;
                else                      chip = `<span class="status-chip draft"><i class="bi bi-circle-fill" style="font-size:.45rem"></i> Draft</span>`;

                let toggleBtn = '';
                if(c.status==='active')
                    toggleBtn = `<button onclick="toggleStatus(${c.id},'inactive')" class="btn btn-sm tbl-action-btn btn-outline-danger">Unpublish</button>`;
                else
                    toggleBtn = `<button onclick="toggleStatus(${c.id},'active')" class="btn btn-sm tbl-action-btn btn-outline-success">Publish</button>`;

                html += `
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <img src="${thumb}" class="cthumbnail">
                      <div>
                        <div class="fw-semibold" style="font-size:.84rem">${c.title}</div>
                        <div class="text-muted" style="font-size:.72rem">${c.type||'Course'}</div>
                      </div>
                    </div>
                  </td>
                  <td>${c.total_chapters||0}</td>
                  <td>${fmt(aData.students||0)}</td>
                  <td class="fw-semibold text-success" style="font-size:.82rem">TZS ${fmt(aData.net_earnings||0)}</td>
                  <td>${chip}</td>
                  <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center gap-1 flex-wrap">
                      ${toggleBtn}
                      <div class="dropdown">
                        <button class="btn btn-sm btn-light border tbl-action-btn" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size:.82rem;border-radius:12px">
                          <li><a class="dropdown-item" href="../data_files/?view=course_contents_management&course_id=${c.id}"><i class="bi bi-eye me-2"></i>View Details</a></li>
                          <li><a class="dropdown-item" href="../data_files/?view=edit_course&course_id=${c.id}"><i class="bi bi-pencil me-2"></i>Edit Course</a></li>
                          <li><a class="dropdown-item" href="../data_files/?view=view_course_details&course_id=${c.id}"><i class="bi bi-box-arrow-up-right me-2"></i>Preview Page</a></li>
                        </ul>
                      </div>
                    </div>
                  </td>
                </tr>`;
            });

            document.getElementById('instCoursesTbody').innerHTML = html;

            $('#instCoursesTable').DataTable({
                destroy:true, responsive:true, autoWidth:false,
                pageLength:10, lengthMenu:[5,10,25,50],
                order:[[0,'asc']],
                language:{
                    search:'_INPUT_', searchPlaceholder:'Search courses…',
                    lengthMenu:'Show _MENU_ courses',
                    info:'Showing _START_–_END_ of _TOTAL_ courses',
                    paginate:{previous:'‹',next:'›'}
                }
            });

        }).catch(console.error);
    }

    /* ── Toggle status ── */
    window.toggleStatus = function(course_id, status){
        Swal.fire({
            title: status==='active' ? 'Publish Course?' : 'Unpublish Course?',
            text: 'You can change this anytime.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, proceed',
            confirmButtonColor: status==='active' ? '#16a34a' : '#dc2626'
        }).then(r=>{
            if(!r.isConfirmed) return;
            fetch('ajax/ajax_toggle_course_status.php',{
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body:JSON.stringify({course_id, status})
            }).then(r=>r.json()).then(r=>{
                if(r.status==='success'){
                    Swal.fire({icon:'success',title:'Updated!',timer:1200,showConfirmButton:false});
                    setTimeout(()=>{ loadAnalytics(); loadCoursesTable(); }, 1300);
                } else {
                    Swal.fire('Error', r.message, 'error');
                }
            });
        });
    };

    /* ── Bootstrap on DOMContentLoaded ── */
    function boot(){
        /* First load analytics so we have the map ready */
        fetch("ajax/ajax_fetch_instructor_courses_analytics.php")
        .then(r=>r.json()).then(res=>{
            if(res.status==='success'){
                window._analyticsMap = {};
                res.data.forEach(c=>{ window._analyticsMap[c.id] = c; });
            }
            loadAnalytics();
            loadCoursesTable();
        }).catch(()=>{ loadAnalytics(); loadCoursesTable(); });
    }

    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
</script>
