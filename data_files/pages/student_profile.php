<div class="container mt-4">
    <div class="row align-items-center">
        <div class="col-12 col-md">
            <h5>Student Profile</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-3 mb-md-0">
                    <li class="breadcrumb-item bi"><a href="../data_files/?view=3002">Dashboard</a></li>
                    <li class="breadcrumb-item bi"><a href="../data_files/?view=learning-student-home">Students</a></li>
                    <li class="breadcrumb-item active bi" aria-current="page">Student Profile</li>
                </ol>
            </nav>
        </div>
        <div class="col-12 col-md-auto"><button class="btn btn-theme" data-bs-toggle="modal"
                data-bs-target="#add-attendance-modal"><i data-feather="plus"></i> Add Attendance</button>
        </div>
    </div>
</div>
<div class="container mt-4" id="main-content">
    <div class="row">
        <div class="col-12 col-md-6 col-lg-3 order-1 mb-4">
            <figure class="avatar avatar-150 rounded coverimg align-middle shadow-sm position-relative mb-3">
                <img src="<?php echo $userProfileImage; ?>" alt="">
            </figure>
            <h4><?php echo $fullname; ?> <i class="bi bi-star-fill text-theme-1 theme-yellow small"></i></h4>
            <p><span class="badge badge-light bg-theme-1-subtle text-theme-1 theme-teal">Present</span>
                <span class="badge badge-light bg-theme-1-subtle text-theme-1 theme-orange">In-Campus</span>
            </p>
            <p class="text-secondary mb-1"><i class="bi bi-mortarboard me-2 align-middle"></i>
                11<sup>th</sup> Standard</p>
            <p class="text-secondary mb-1"><i class="bi bi-cake me-2 align-middle"></i> <span
                    class="align-middle">15/09/2000</span></p>
            <p class="text-secondary"><i class="bi bi-geo-alt me-2 align-middle"></i> <span class="align-middle">13th
                    Street. 47 W 13th St, New York, NY 10011, USA.</span></p>
        </div>
        <div class="col-12 col-md-12 col-lg-6 order-3 order-lg-2 mb-4">
            <div class="row">
                <div class="col mb-3">
                    <h6 class="mb-0">Attendance Statistics</h6>
                    <p class="small">Student's average in/Out time</p>
                </div>
                <div class="col-auto"><select class="form-select bg-theme-1-subtle border-0">
                        <option>2022</option>
                        <option>2023</option>
                        <option>2024</option>
                        <option>2025</option>
                    </select></div>
            </div>
            <div class="height-250 mb-3"><canvas id="summarychart"></canvas></div>
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">11.56 AM</h5>
                    <p class="text-secondary small">Average In-Time</p>
                </div>
                <div class="col">
                    <h5 class="mb-0">5.15 PM</h5>
                    <p class="text-secondary small">Average Out-Time</p>
                </div>
                <div class="col">
                    <h5 class="mb-0">0.80 Sec</h5>
                    <p class="text-secondary small">Authentication Time</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3 order-2 order-lg-3 mb-4">
            <h6 class="mb-0">Grades Achived</h6>
            <p class="small mb-3"><span class="text-secondary">Upadted on:</span> 17 January 2025</p>
            <h1>A<sup>+</sup></h1>
            <h3 class="mb-0">89.78<small>%</small></h3>
            <p class="small text-secondary">in 10<sup>th</sup> Standard</p><br>
            <h6 class="mb-0">Parent Contacts</h6>
            <p class="small mb-3"><span class="text-secondary">You can connect with student parent</span>
            </p>
            <div class="row mb-3">
                <div class="col-auto">
                    <div class="avatar avatar-40 coverimg rounded d-block align-top"><img
                            src="assets/img/modern-ai-image/user-5.jpg" alt=""></div>
                </div>
                <div class="col">
                    <p class="mb-0">Alice Johnson</p>
                    <p class="small text-secondary">Mother</p>
                </div>
                <div class="col-auto"><a href="tel:+44859@5555525" class="btn btn-sm btn-square btn-link"><i
                            class="bi bi-telephone"></i></a> <a href="learning-chat-call.html"
                        class="btn btn-sm btn-square btn-link"><i class="bi bi-chat-dots"></i></a></div>
            </div>
            <div class="row">
                <div class="col-auto">
                    <div class="avatar avatar-40 coverimg rounded d-block align-top"><img
                            src="assets/img/modern-ai-image/user-4.jpg" alt=""></div>
                </div>
                <div class="col">
                    <p class="mb-0">Macros Johnson</p>
                    <p class="small text-secondary">Father</p>
                </div>
                <div class="col-auto"><a href="tel:+44859@5555525" class="btn btn-sm btn-square btn-link"><i
                            class="bi bi-telephone"></i></a> <a href="learning-chat-call.html"
                        class="btn btn-sm btn-square btn-link"><i class="bi bi-chat-dots"></i></a></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-center mb-3">
                        <div class="col-auto">
                            <div class="avatar avatar-50">
                                <div id="circleprogressblue1"></div>
                            </div>
                        </div>
                        <div class="col">
                            <h6 class="mb-0">IT Data Modal</h6>
                            <p class="small">Prof. John Jackson</p>
                        </div>
                    </div>
                    <div class="row gx-3 align-items-center">
                        <div class="col">
                            <p class="text-secondary small mb-0">Completed 2/9 chapters</p>
                            <p class="text-secondary small mb-0">Passed 2/10 assignment</p>
                        </div>
                        <div class="col-auto"><button class="btn btn-link btn-sm btn-square"><i
                                    class="bi bi-play"></i></button></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-center mb-3">
                        <div class="col-auto">
                            <div class="avatar avatar-50">
                                <div id="circleprogressgreen1"></div>
                            </div>
                        </div>
                        <div class="col">
                            <h6 class="mb-0">Bio Management</h6>
                            <p class="small">Prof. Jinjo Min</p>
                        </div>
                    </div>
                    <div class="row gx-3 align-items-center">
                        <div class="col">
                            <p class="text-secondary small mb-0">2/9 chapters completed</p>
                            <p class="text-secondary small mb-0">8 assignments left</p>
                        </div>
                        <div class="col-auto"><button class="btn btn-link btn-sm btn-square"><i
                                    class="bi bi-play"></i></button></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card adminuiux-card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar avatar-50 rounded bg-theme-1-subtle text-theme-1 h4 mb-3"><i
                                    class="bi bi-journal-check"></i></div>
                        </div>
                        <div class="col mb-3">
                            <h6 class="mb-0">Assignment 3</h6>
                            <p class="small">IT Data Modal</p>
                        </div>
                    </div>
                    <div class="row gx-3 align-items-center">
                        <div class="col">
                            <p class="text-secondary small mb-0">30 MCQs 1 Project</p>
                            <p class="text-secondary small">25 hours 15min</p>
                        </div>
                        <div class="col-auto"><button class="btn btn-theme btn-sm">Start <i
                                    class="bi bi-play"></i></button></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <div class="card adminuiux-card bg-theme-1 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center mb-3">
                        <div class="col-auto">
                            <div class="avatar avatar-50 rounded bg-white-opacity text-white">
                                <h6>11<sup>th</sup></h6>
                            </div>
                        </div>
                        <div class="col">
                            <h6 class="mb-0">Upcoming Exam</h6>
                            <p class="opacity-75 small">Chemistry MCQ: Weekly Test</p>
                        </div>
                    </div>
                    <div class="row gx-3 align-items-center">
                        <div class="col">
                            <p class="mb-0">11/12/2025</p>
                            <p class="opacity-75 small">11:30am-12:30pm, 1hr</p>
                        </div>
                        <div class="col-auto">
                            <div class="dropdown d-inline-block"><a
                                    class="btn btn-sm btn-link text-white btn-square no-caret"
                                    data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="javascript:void(0)">Add Result</a>
                                    </li>
                                    <li><a class="dropdown-item" href="javascript:void(0)">View Details</a>
                                    </li>
                                    <li><a class="dropdown-item theme-red" href="javascript:void(0)">Delete</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card adminuiux-card shadow-sm mb-4">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col order-1 mb-2 mb-md-0">
                    <h6 class="text-truncated">Assignment and Exams</h6>
                </div>
                <div class="col-6 col-md-auto order-3 order-md-2">
                    <div class="input-group input-group-sm"><span class="input-group-text"><i class="bi bi-search"></i>
                        </span><input class="form-control" type="search" placeholder="Search here..."
                            id="searchglobal2"></div>
                </div>
                <div class="col-6 col-md-auto order-4 order-md-3"><select class="form-select form-select-sm">
                        <option>2021</option>
                        <option>2022</option>
                        <option>2023</option>
                        <option>2024</option>
                        <option selected="selected">2025</option>
                    </select></div>
                <div class="col-6 col-md-auto order-4 order-md-3"><select class="form-select form-select-sm">
                        <option>All</option>
                        <option>January</option>
                        <option>February</option>
                        <option>March</option>
                        <option>April</option>
                        <option>June</option>
                        <option>July</option>
                        <option>August</option>
                        <option>September</option>
                        <option>October</option>
                        <option selected="selected">November</option>
                    </select></div>
                <div class="col-auto order-2 order-md-4 mb-2 mb-md-0"><button class="btn btn-sm btn-square btn-link"><i
                            class="bi bi-arrow-clockwise"></i></button></div>
            </div>
        </div>
        <div class="card-body pt-0">
            <table class="table mb-0" data-show-toggle="true" id="dataTable">
                <thead>
                    <tr>
                        <th class="all">Exam</th>
                        <th class="all">Date & Time</th>
                        <th class="desktop">Duration</th>
                        <th class="tablet desktop">Marks</th>
                        <th class="desktop">Result</th>
                        <th class="desktop">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="theme-orange">
                        <td>
                            <div class="row gx-3 align-items-center">
                                <div class="col-auto">
                                    <div class="avatar avatar-40 rounded bg-theme-1-subtle text-theme-1">
                                        <h6>11<sup>th</sup></h6>
                                    </div>
                                </div>
                                <div class="col">
                                    <h6 class="mb-0">Chemistry MCQ: Weekly Test</h6>
                                    <p class="text-secondary small">Regular Syllabus</p>
                                </div>
                            </div>
                        </td>
                        <td data-sort="YYYYMMDD">
                            <div>
                                <p class="mb-0">11/12/2025</p>
                                <p class="text-secondary small">11:30am-12:30pm</p>
                            </div>
                        </td>
                        <td>
                            <p class="mb-0">1 hr</p>
                            <p class="small text-secondary">Regular</p>
                        </td>
                        <td>
                            <p class="mb-0">10</p>
                            <p class="small text-secondary">From 30</p>
                        </td>
                        <td>
                            <div class="badge badge-light bg-theme-1-subtle text-theme-1 theme-orange">
                                Upcoming</div>
                        </td>
                        <td>
                            <div class="dropdown d-inline-block"><a class="btn btn-sm btn-link btn-square no-caret"
                                    data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="javascript:void(0)">Add Result</a>
                                    </li>
                                    <li><a class="dropdown-item" href="javascript:void(0)">View Details</a>
                                    </li>
                                    <li><a class="dropdown-item theme-red" href="javascript:void(0)">Delete</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <tr class="theme-red">
                        <td>
                            <div class="row gx-3 align-items-center">
                                <div class="col-auto">
                                    <div class="avatar avatar-40 rounded bg-theme-1-subtle text-theme-1">
                                        <h6>11<sup>th</sup></h6>
                                    </div>
                                </div>
                                <div class="col">
                                    <h6 class="mb-0">Weekly Test</h6>
                                    <p class="text-secondary small">Regular Syllabus</p>
                                </div>
                            </div>
                        </td>
                        <td data-sort="YYYYMMDD">
                            <p class="mb-0">25/11/2025</p>
                            <p class="text-secondary small">11:30am-12:30pm</p>
                        </td>
                        <td>
                            <p class="mb-0">1 hr</p>
                            <p class="small text-secondary">Regular</p>
                        </td>
                        <td>
                            <p class="mb-0">10</p>
                            <p class="small text-secondary">From 30</p>
                        </td>
                        <td>
                            <div class="badge badge-light bg-theme-1-subtle text-theme-1 theme-red">Failed
                            </div>
                        </td>
                        <td>
                            <div class="dropdown d-inline-block"><a class="btn btn-sm btn-link btn-square no-caret"
                                    data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="javascript:void(0)">Add Result</a>
                                    </li>
                                    <li><a class="dropdown-item" href="javascript:void(0)">View Details</a>
                                    </li>
                                    <li><a class="dropdown-item theme-red" href="javascript:void(0)">Delete</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="row gx-3 align-items-center">
                                <div class="col-auto">
                                    <div class="avatar avatar-40 rounded bg-theme-1-subtle text-theme-1">
                                        <h6>11<sup>th</sup></h6>
                                    </div>
                                </div>
                                <div class="col">
                                    <h6 class="mb-0">Physics Weekly Test</h6>
                                    <p class="text-secondary small">Regular Syllabus</p>
                                </div>
                            </div>
                        </td>
                        <td data-sort="YYYYMMDD">
                            <p class="mb-0">26/11/2025</p>
                            <p class="text-secondary small">11:30am-12:30pm</p>
                        </td>
                        <td>
                            <p class="mb-0">1 hr</p>
                            <p class="small text-secondary">Regular</p>
                        </td>
                        <td>
                            <p class="mb-0">27</p>
                            <p class="small text-secondary">From 30</p>
                        </td>
                        <td>
                            <div class="badge badge-light bg-theme-1-subtle text-theme-1 theme-green">Pass
                            </div>
                        </td>
                        <td>
                            <div class="dropdown d-inline-block"><a class="btn btn-sm btn-link btn-square no-caret"
                                    data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="javascript:void(0)">Add Result</a>
                                    </li>
                                    <li><a class="dropdown-item" href="javascript:void(0)">View Details</a>
                                    </li>
                                    <li><a class="dropdown-item theme-red" href="javascript:void(0)">Delete</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="row gx-3 align-items-center">
                                <div class="col-auto">
                                    <div class="avatar avatar-40 rounded bg-theme-1-subtle text-theme-1">
                                        <h6>11<sup>th</sup></h6>
                                    </div>
                                </div>
                                <div class="col">
                                    <h6 class="mb-0">Maths Weekly Test</h6>
                                    <p class="text-secondary small">Regular Syllabus</p>
                                </div>
                            </div>
                        </td>
                        <td data-sort="YYYYMMDD">
                            <p class="mb-0">28/11/2025</p>
                            <p class="text-secondary small">11:30am-12:30pm</p>
                        </td>
                        <td>
                            <p class="mb-0">1 hr</p>
                            <p class="small text-secondary">Regular</p>
                        </td>
                        <td>
                            <p class="mb-0">25</p>
                            <p class="small text-secondary">From 30</p>
                        </td>
                        <td>
                            <div class="badge badge-light bg-theme-1-subtle text-theme-1 theme-green">Pass
                            </div>
                        </td>
                        <td>
                            <div class="dropdown d-inline-block"><a class="btn btn-sm btn-link btn-square no-caret"
                                    data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="javascript:void(0)">Add Result</a>
                                    </li>
                                    <li><a class="dropdown-item" href="javascript:void(0)">View Details</a>
                                    </li>
                                    <li><a class="dropdown-item theme-red" href="javascript:void(0)">Delete</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="row gx-3 align-items-center">
                                <div class="col-auto">
                                    <div class="avatar avatar-40 rounded bg-theme-1-subtle text-theme-1">
                                        <h6>11<sup>th</sup></h6>
                                    </div>
                                </div>
                                <div class="col">
                                    <h6 class="mb-0">Chemistry: Mid Term Exam</h6>
                                    <p class="text-secondary small">Regular Syllabus</p>
                                </div>
                            </div>
                        </td>
                        <td data-sort="YYYYMMDD">
                            <p class="mb-0">12/10/2025</p>
                            <p class="text-secondary small">9:00am-12:00pm</p>
                        </td>
                        <td>
                            <p class="mb-0">3 hr</p>
                            <p class="small text-secondary">Strict</p>
                        </td>
                        <td>
                            <p class="mb-0">45</p>
                            <p class="small text-secondary">From 50</p>
                        </td>
                        <td>
                            <div class="badge badge-light bg-theme-1-subtle text-theme-1 theme-green">Pass
                            </div>
                        </td>
                        <td>
                            <div class="dropdown d-inline-block"><a class="btn btn-sm btn-link btn-square no-caret"
                                    data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="javascript:void(0)">Add Result</a>
                                    </li>
                                    <li><a class="dropdown-item" href="javascript:void(0)">View Details</a>
                                    </li>
                                    <li><a class="dropdown-item theme-red" href="javascript:void(0)">Delete</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="row gx-3 align-items-center">
                                <div class="col-auto">
                                    <div class="avatar avatar-40 rounded bg-theme-1-subtle text-theme-1">
                                        <h6>11<sup>th</sup></h6>
                                    </div>
                                </div>
                                <div class="col">
                                    <h6 class="mb-0">Maths: Mid Term Exam</h6>
                                    <p class="text-secondary small">Regular Syllabus</p>
                                </div>
                            </div>
                        </td>
                        <td data-sort="YYYYMMDD">
                            <p class="mb-0">11/10/2025</p>
                            <p class="text-secondary small">9:00am-12:00pm</p>
                        </td>
                        <td>
                            <p class="mb-0">3 hr</p>
                            <p class="small text-secondary">Strict</p>
                        </td>
                        <td>
                            <p class="mb-0">42</p>
                            <p class="small text-secondary">From 50</p>
                        </td>
                        <td>
                            <div class="badge badge-light bg-theme-1-subtle text-theme-1 theme-green">Pass
                            </div>
                        </td>
                        <td>
                            <div class="dropdown d-inline-block"><a class="btn btn-sm btn-link btn-square no-caret"
                                    data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="javascript:void(0)">Add Result</a>
                                    </li>
                                    <li><a class="dropdown-item" href="javascript:void(0)">View Details</a>
                                    </li>
                                    <li><a class="dropdown-item theme-red" href="javascript:void(0)">Delete</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>