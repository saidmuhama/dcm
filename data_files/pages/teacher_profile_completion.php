<?php
$profile_completion = App::getProfileCompletionStatus($usr_code ?? '', $user_role ?? 0);
$pct = (int)$profile_completion;
?>

<style>
/* ── Hero ── */
.tpc-hero {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 40%, #0f3460 100%);
    padding: 2rem 0 4.5rem;
    position: relative; overflow: hidden;
}
.tpc-hero::before {
    content:''; position:absolute; inset:0; pointer-events:none;
    background: radial-gradient(circle at 15% 60%, rgba(99,102,241,.2) 0%,transparent 55%),
                radial-gradient(circle at 85% 25%, rgba(168,85,247,.15) 0%,transparent 50%);
}
.tpc-hero::after {
    content:''; position:absolute; inset:0; pointer-events:none;
    background-image: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='20' cy='20' r='1.5' fill='%23fff' fill-opacity='.03'/%3E%3C/svg%3E");
}

/* ── Canvas ── */
.tpc-canvas {
    max-width: 960px;
    margin: -2.5rem auto 2rem;
    padding: 0 1rem;
    position: relative; z-index: 10;
}

/* ── Step nav ── */
.tpc-steps {
    display: flex; gap: 0;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 14px;
    padding: .35rem;
    backdrop-filter: blur(6px);
}
.tpc-step-btn {
    flex: 1; background: transparent; border: none; color: rgba(255,255,255,.55);
    border-radius: 10px; padding: .55rem .75rem;
    font-size: .78rem; font-weight: 600; cursor: pointer;
    transition: all .2s; display: flex; align-items: center; justify-content: center; gap: .4rem;
    text-align: center;
}
.tpc-step-btn .sn {
    width: 22px; height: 22px; border-radius: 50%; border: 2px solid rgba(255,255,255,.3);
    display: flex; align-items: center; justify-content: center;
    font-size: .72rem; flex-shrink: 0; transition: all .2s;
}
.tpc-step-btn.active {
    background: rgba(255,255,255,.15); color: #fff;
}
.tpc-step-btn.active .sn { background: #6366f1; border-color: #6366f1; color: #fff; }
.tpc-step-btn.done .sn { background: #22c55e; border-color: #22c55e; color: #fff; }
.tpc-step-btn.done { color: rgba(255,255,255,.7); }

/* ── Avatar upload ── */
.av-upload-wrap {
    position: relative; width: 90px; height: 90px; flex-shrink: 0;
}
.av-upload-wrap img {
    width: 90px; height: 90px; border-radius: 50%; object-fit: cover;
    border: 3px solid rgba(255,255,255,.25); box-shadow: 0 4px 20px rgba(0,0,0,.35);
}
.av-upload-btn {
    position: absolute; bottom: 2px; right: 2px;
    width: 28px; height: 28px; border-radius: 50%;
    background: #6366f1; border: 2px solid #fff;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: .7rem; color: #fff; transition: background .15s;
}
.av-upload-btn:hover { background: #4f46e5; }

/* ── Completion ring (inline SVG) ── */
.tpc-ring { position: relative; width: 64px; height: 64px; flex-shrink: 0; }
.tpc-ring svg { transform: rotate(-90deg); }
.tpc-ring .ring-track { fill: none; stroke: rgba(255,255,255,.12); stroke-width: 5; }
.tpc-ring .ring-fill  { fill: none; stroke: #6366f1; stroke-width: 5;
    stroke-linecap: round; transition: stroke-dashoffset 1s ease; }
.tpc-ring .ring-label {
    position: absolute; inset: 0; display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    font-size: .78rem; font-weight: 700; color: #fff; line-height: 1;
}
.tpc-ring .ring-label small { font-size: .5rem; color: rgba(255,255,255,.55); }

/* ── Section card ── */
.sect-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 4px 24px rgba(0,0,0,.08);
    border: 1px solid rgba(0,0,0,.05);
    overflow: hidden;
    display: none;
}
.sect-card.active { display: block; }
.sect-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex; align-items: center; gap: .85rem;
    background: linear-gradient(135deg, #fafbff, #f5f7ff);
}
.sect-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;
}
.sect-body { padding: 1.5rem; }

/* ── Avatar in step-1 ── */
.avatar-card {
    background: linear-gradient(135deg, #f8f9ff, #eef2ff);
    border: 2px dashed #c7d2fe;
    border-radius: 16px;
    padding: 1.5rem 1rem;
    text-align: center;
    cursor: pointer; transition: border-color .2s, background .2s;
    position: relative; overflow: hidden;
}
.avatar-card:hover { border-color: #6366f1; background: #eef2ff; }
.avatar-card img { width: 90px; height: 90px; border-radius: 50%; object-fit: cover;
    border: 3px solid #fff; box-shadow: 0 4px 14px rgba(99,102,241,.25); margin-bottom: .6rem; }
.avatar-card .av-hint { font-size: .75rem; color: #6366f1; font-weight: 500; }

/* ── Form styling ── */
.form-label { font-size: .8rem; font-weight: 600; color: #475569; margin-bottom: .35rem; }
.form-control, .form-select {
    border-radius: 10px; border-color: #e2e8f0; font-size: .875rem;
    padding: .65rem .9rem; transition: border-color .2s, box-shadow .2s;
}
.form-control:focus, .form-select:focus {
    border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.12);
}
.form-control[disabled], .form-control:disabled {
    background: #f8fafc; color: #94a3b8; cursor: not-allowed;
}
.field-group { margin-bottom: 1.1rem; }
.section-divider {
    font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px;
    color: #94a3b8; margin: 1.25rem 0 .85rem; display: flex; align-items: center; gap: .6rem;
}
.section-divider::before, .section-divider::after {
    content:''; flex: 1; height: 1px; background: #f1f5f9;
}

/* ── Skills tags ── */
.skills-wrap { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: .5rem; }
.skill-tag {
    background: #eef2ff; color: #6366f1; border-radius: 20px;
    padding: .28rem .65rem; font-size: .75rem; font-weight: 600;
    display: flex; align-items: center; gap: .3rem;
}
.skill-tag .rm { cursor: pointer; opacity: .6; font-size: .8rem; line-height: 1; }
.skill-tag .rm:hover { opacity: 1; }

/* ── Nav buttons row ── */
.tpc-nav {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-top: 1px solid #f1f5f9;
    background: #fafbff;
}
.btn-prev {
    background: #f1f5f9; color: #475569; border: none; border-radius: 10px;
    padding: .6rem 1.25rem; font-size: .85rem; font-weight: 600; cursor: pointer;
    transition: background .15s;
}
.btn-prev:hover { background: #e2e8f0; }
.btn-next, .btn-save {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff; border: none; border-radius: 10px;
    padding: .6rem 1.5rem; font-size: .85rem; font-weight: 600; cursor: pointer;
    transition: opacity .15s; display: flex; align-items: center; gap: .4rem;
}
.btn-next:hover, .btn-save:hover { opacity: .88; }

/* ── Progress bar under card ── */
.tpc-progress { height: 4px; background: #f1f5f9; border-radius: 0 0 18px 18px; overflow: hidden; }
.tpc-progress-fill { height: 100%; background: linear-gradient(90deg,#6366f1,#8b5cf6); transition: width .4s ease; border-radius: 0 0 18px 18px; }

@media (prefers-color-scheme: dark){
    .sect-card { background: #1e293b; border-color: rgba(255,255,255,.06); }
    .sect-header { background: linear-gradient(135deg,#1e293b,#1a2440); border-color: rgba(255,255,255,.06); }
    .form-control, .form-select { background: #0f172a; border-color: #334155; color: #e2e8f0; }
    .form-label { color: #94a3b8; }
    .section-divider::before,.section-divider::after { background: rgba(255,255,255,.06); }
    .section-divider { color: #64748b; }
    .tpc-nav { background: #1e293b; border-color: rgba(255,255,255,.06); }
    .btn-prev { background: #334155; color: #94a3b8; }
    .btn-prev:hover { background: #475569; }
    .avatar-card { background: linear-gradient(135deg,#1e293b,#1a2440); border-color: #4f46e5; }
    .tpc-progress { background: rgba(255,255,255,.06); }
    .skill-tag { background: rgba(99,102,241,.2); }
}
</style>

<!-- ═══════════════════════════════ HERO ════════════════════════════ -->
<div class="tpc-hero">
    <div class="container position-relative" style="z-index:2; max-width:960px">

        <!-- breadcrumb -->
        <nav class="mb-3">
            <ol class="breadcrumb mb-0" style="font-size:.78rem">
                <li class="breadcrumb-item"><a href="../data_files/?view=3002" class="text-white-50 text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" style="color:rgba(255,255,255,.55)">Edit Profile</li>
            </ol>
        </nav>

        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3 mb-3">

            <!-- avatar (hero) -->
            <div class="av-upload-wrap" onclick="document.getElementById('imageInput').click()" style="cursor:pointer" title="Click to change photo">
                <img id="heroAvatar" src="<?= htmlspecialchars($userProfileImage ?? 'assets/img/logo-512.png') ?>" alt="">
                <div class="av-upload-btn"><i class="bi bi-camera-fill"></i></div>
            </div>
            <input type="file" id="imageInput" class="d-none" accept="image/*">

            <!-- name + role -->
            <div class="flex-grow-1">
                <h4 class="text-white fw-bold mb-0" id="heroName"><?= htmlspecialchars($fullname ?? '') ?></h4>
                <div class="text-white-50 small mb-2"><?= htmlspecialchars($roleTitle ?? 'Instructor') ?></div>
                <a href="../data_files/?view=3002" class="btn btn-sm btn-outline-light" style="border-radius:8px;font-size:.78rem">
                    <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
                </a>
            </div>

            <!-- Completion ring -->
            <div class="text-center">
                <div class="tpc-ring mx-auto">
                    <?php
                    $r = 28; $circ = round(2 * M_PI * $r, 1);
                    $offset = round($circ - ($pct / 100) * $circ, 1);
                    ?>
                    <svg width="64" height="64" viewBox="0 0 64 64">
                        <circle class="ring-track" cx="32" cy="32" r="<?= $r ?>"/>
                        <circle class="ring-fill" cx="32" cy="32" r="<?= $r ?>"
                            stroke-dasharray="<?= $circ ?>"
                            stroke-dashoffset="<?= $offset ?>"/>
                    </svg>
                    <div class="ring-label"><?= $pct ?><small>%</small></div>
                </div>
                <div style="font-size:.7rem;color:rgba(255,255,255,.5);margin-top:.3rem">Profile Complete</div>
            </div>

        </div>

        <!-- Step navigator -->
        <div class="tpc-steps">
            <button class="tpc-step-btn active" data-step="0">
                <span class="sn">1</span>
                <span class="d-none d-sm-inline">Personal</span>
            </button>
            <button class="tpc-step-btn" data-step="1">
                <span class="sn">2</span>
                <span class="d-none d-sm-inline">Contact</span>
            </button>
            <button class="tpc-step-btn" data-step="2">
                <span class="sn">3</span>
                <span class="d-none d-sm-inline">Education</span>
            </button>
        </div>

    </div>
</div>

<!-- ═══════════════════════════════ CANVAS ══════════════════════════ -->
<div class="tpc-canvas">

    <!-- ── Step 0: Personal Details ── -->
    <div class="sect-card active" id="step-0">
        <div class="sect-header">
            <div class="sect-icon" style="background:#eef2ff;color:#6366f1"><i class="bi bi-person-fill"></i></div>
            <div>
                <div style="font-weight:700;font-size:.95rem;color:#1e293b">Instructor Details</div>
                <div style="font-size:.75rem;color:#94a3b8">Name, bio and specialities</div>
            </div>
        </div>
        <div class="sect-body">
            <div class="row g-3 align-items-start">

                <!-- Avatar column -->
                <div class="col-12 col-md-3">
                    <div class="avatar-card" onclick="document.getElementById('imageInput').click()">
                        <img id="previewImage" src="<?= htmlspecialchars($userProfileImage ?? 'assets/img/logo-512.png') ?>" alt="">
                        <div class="av-hint"><i class="bi bi-camera me-1"></i>Click to change photo</div>
                        <div style="font-size:.68rem;color:#94a3b8;margin-top:.2rem">JPG, PNG — max 5MB</div>
                    </div>
                    <input type="hidden" id="profile_image_base64">
                </div>

                <!-- Fields column -->
                <div class="col-12 col-md-9">
                    <div class="section-divider">Basic Information</div>
                    <div class="row g-3">
                        <div class="col-12 col-sm-4">
                            <div class="field-group">
                                <label class="form-label" for="namef">First Name <span class="text-danger">*</span></label>
                                <input class="form-control" id="namef" placeholder="e.g. John">
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="field-group">
                                <label class="form-label" for="namem">Middle Name <span class="text-muted fw-normal">(optional)</span></label>
                                <input class="form-control" id="namem" placeholder="e.g. K.">
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="field-group">
                                <label class="form-label" for="namel">Last Name <span class="text-danger">*</span></label>
                                <input class="form-control" id="namel" placeholder="e.g. Doe">
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="field-group">
                                <label class="form-label" for="datepicker">Date of Birth</label>
                                <input class="form-control" type="date" id="datepicker">
                            </div>
                        </div>
                    </div>

                    <div class="section-divider">About You</div>
                    <div class="field-group">
                        <label class="form-label" for="describe3">Short Bio / Description</label>
                        <textarea class="form-control" id="describe3" rows="3"
                            placeholder="Tell students about yourself, your teaching style and experience…"
                            style="resize:vertical"></textarea>
                        <div style="font-size:.72rem;color:#94a3b8;margin-top:.25rem">
                            <span id="bioCount">0</span> / 500 characters
                        </div>
                    </div>

                    <div class="section-divider">Skills &amp; Specialities</div>
                    <div class="field-group">
                        <label class="form-label">Add Skills</label>
                        <div class="skills-wrap" id="skillTags"></div>
                        <div class="input-group" style="max-width:340px">
                            <input class="form-control" id="skillInput" placeholder="e.g. Data Science, PHP…">
                            <button class="btn" id="addSkillBtn"
                                style="background:#6366f1;color:#fff;border-radius:0 10px 10px 0;font-size:.82rem">
                                <i class="bi bi-plus-lg"></i> Add
                            </button>
                        </div>
                        <input type="hidden" id="tags1">
                    </div>
                </div>
            </div>
        </div>
        <div class="tpc-progress"><div class="tpc-progress-fill" style="width:33%"></div></div>
        <div class="tpc-nav">
            <a href="../data_files/?view=3002" class="btn-prev">
                <i class="bi bi-x-lg me-1"></i>Cancel
            </a>
            <button class="btn-next" onclick="goStep(1)">
                Contact Info <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </div>

    <!-- ── Step 1: Contact & Location ── -->
    <div class="sect-card" id="step-1">
        <div class="sect-header">
            <div class="sect-icon" style="background:#f0fdf4;color:#16a34a"><i class="bi bi-telephone-fill"></i></div>
            <div>
                <div style="font-weight:700;font-size:.95rem;color:#1e293b">Contact &amp; Location</div>
                <div style="font-size:.75rem;color:#94a3b8">How students can reach you</div>
            </div>
        </div>
        <div class="sect-body">
            <div class="section-divider">Contact Information</div>
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="field-group">
                        <label class="form-label" for="namefull">Full Name</label>
                        <input class="form-control" id="namefull" placeholder="As on official record">
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="field-group">
                        <label class="form-label" for="phoneon2">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text" style="border-radius:10px 0 0 10px;border-color:#e2e8f0;font-size:.82rem;color:#64748b"><i class="bi bi-telephone"></i></span>
                            <input class="form-control" id="phoneon2" placeholder="+255 7xx xxx xxx" style="border-radius:0 10px 10px 0">
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="field-group">
                        <label class="form-label" for="emailaddresson1">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text" style="border-radius:10px 0 0 10px;border-color:#e2e8f0;font-size:.82rem;color:#64748b"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="emailaddresson1"
                                placeholder="your@email.com" disabled
                                style="border-radius:0 10px 10px 0;background:#f8fafc;color:#94a3b8">
                        </div>
                        <div style="font-size:.7rem;color:#94a3b8;margin-top:.2rem"><i class="bi bi-lock me-1"></i>Email cannot be changed here</div>
                    </div>
                </div>
            </div>

            <div class="section-divider">Address</div>
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="field-group">
                        <label class="form-label" for="street">Street / Road</label>
                        <input class="form-control" id="street" placeholder="e.g. Muhama Street">
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="field-group">
                        <label class="form-label" for="town">Town / Ward</label>
                        <input class="form-control" id="town" placeholder="e.g. Kinondoni">
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="field-group">
                        <label class="form-label" for="city">City</label>
                        <input class="form-control" id="city" placeholder="e.g. Dar es Salaam">
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="field-group">
                        <label class="form-label" for="country">Country</label>
                        <input class="form-control" id="country" placeholder="e.g. Tanzania">
                    </div>
                </div>
            </div>
        </div>
        <div class="tpc-progress"><div class="tpc-progress-fill" style="width:66%"></div></div>
        <div class="tpc-nav">
            <button class="btn-prev" onclick="goStep(0)"><i class="bi bi-arrow-left me-1"></i>Personal</button>
            <button class="btn-next" onclick="goStep(2)">Education <i class="bi bi-arrow-right"></i></button>
        </div>
    </div>

    <!-- ── Step 2: Education ── -->
    <div class="sect-card" id="step-2">
        <div class="sect-header">
            <div class="sect-icon" style="background:#eff6ff;color:#2563eb"><i class="bi bi-mortarboard-fill"></i></div>
            <div>
                <div style="font-weight:700;font-size:.95rem;color:#1e293b">Education</div>
                <div style="font-size:.75rem;color:#94a3b8">Academic background and qualifications</div>
            </div>
        </div>
        <div class="sect-body">
            <div class="section-divider">Academic Details</div>
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="field-group">
                        <label class="form-label" for="main_academic_level">Degree Level</label>
                        <select class="form-select" id="main_academic_level">
                            <option value="">Select…</option>
                            <option value="Certificate">Certificate</option>
                            <option value="Diploma">Diploma</option>
                            <option value="Bachelor">Bachelor</option>
                            <option value="Masters">Masters</option>
                            <option value="Phd">PhD</option>
                            <option value="Postdoctoral">Postdoctoral</option>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-5">
                    <div class="field-group">
                        <label class="form-label" for="sub_academic_level">University / College</label>
                        <input class="form-control" id="sub_academic_level" placeholder="e.g. University of Dar es Salaam">
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="field-group">
                        <label class="form-label" for="degree_title">Degree / Course Pursued</label>
                        <input class="form-control" id="degree_title" placeholder="e.g. BSc Computer Science">
                    </div>
                </div>
                <div class="col-6 col-sm-3 col-lg-2">
                    <div class="field-group">
                        <label class="form-label" for="start-year-2a">Start Year</label>
                        <select class="form-select" id="start-year-2a">
                            <?php for($y=date('Y');$y>=1980;$y--): ?>
                            <option value="<?= $y ?>"><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="col-6 col-sm-3 col-lg-2">
                    <div class="field-group">
                        <label class="form-label" for="end-year-2a">Status</label>
                        <select class="form-select" id="end-year-2a">
                            <option value="Continuing">Continuing</option>
                            <option value="Completed">Completed</option>
                            <option value="Passed">Passed</option>
                            <option value="Deferred">Deferred</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Preview card -->
            <div class="section-divider">Preview</div>
            <div id="eduPreviewCard" style="
                background:linear-gradient(135deg,#f8f9ff,#eef2ff);
                border:1px solid #c7d2fe; border-radius:14px; padding:1rem 1.25rem;
                display:flex; align-items:center; gap:1rem">
                <div style="width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,#6366f1,#8b5cf6);
                    display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.2rem;color:#fff">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div>
                    <div id="epTitle" style="font-weight:700;color:#1e293b;font-size:.88rem">—</div>
                    <div id="epUni"   style="font-size:.78rem;color:#64748b">—</div>
                    <div id="epYear"  style="font-size:.72rem;color:#94a3b8">—</div>
                </div>
            </div>
        </div>
        <div class="tpc-progress"><div class="tpc-progress-fill" style="width:100%"></div></div>
        <div class="tpc-nav">
            <button class="btn-prev" onclick="goStep(1)"><i class="bi bi-arrow-left me-1"></i>Contact</button>
            <button class="btn-save" id="saveBtn" onclick="saveProfile()">
                <i class="bi bi-check-lg"></i> Save Profile
            </button>
        </div>
    </div>

</div><!-- /canvas -->

<script>
(function(){
    /* ── State ── */
    let currentStep = 0;
    let base64Image = '';
    let skills = [];

    /* ── Step navigation ── */
    window.goStep = function(i){
        document.getElementById('step-'+currentStep).classList.remove('active');
        document.getElementById('step-'+i).classList.add('active');

        document.querySelectorAll('.tpc-step-btn').forEach((btn,idx)=>{
            btn.classList.remove('active','done');
            if(idx < i)    btn.classList.add('done');
            if(idx === i)  btn.classList.add('active');
        });

        currentStep = i;
        updateEduPreview();
        window.scrollTo({top:0,behavior:'smooth'});
    };

    document.querySelectorAll('.tpc-step-btn').forEach(btn=>{
        btn.addEventListener('click', ()=> goStep(+btn.dataset.step));
    });

    /* ── Avatar upload ── */
    document.getElementById('imageInput').addEventListener('change', function(){
        const file = this.files[0];
        if(!file) return;
        if(file.size > 5*1024*1024){
            Swal.fire('File too large','Please choose an image under 5 MB','warning');
            return;
        }
        const reader = new FileReader();
        reader.onload = e=>{
            base64Image = e.target.result;
            document.getElementById('previewImage').src   = base64Image;
            document.getElementById('heroAvatar').src     = base64Image;
            document.getElementById('profile_image_base64').value = base64Image;
        };
        reader.readAsDataURL(file);
    });

    /* ── Bio character counter ── */
    document.getElementById('describe3').addEventListener('input', function(){
        document.getElementById('bioCount').textContent = this.value.length;
    });

    /* ── Skills tags ── */
    function renderSkills(){
        const wrap = document.getElementById('skillTags');
        wrap.innerHTML = skills.map((s,i)=>
            `<span class="skill-tag">${escHtml(s)}<span class="rm" data-i="${i}" title="Remove">×</span></span>`
        ).join('');
        wrap.querySelectorAll('.rm').forEach(el=>{
            el.addEventListener('click',()=>{ skills.splice(+el.dataset.i,1); renderSkills(); syncSkills(); });
        });
        syncSkills();
    }
    function syncSkills(){ document.getElementById('tags1').value = skills.join(','); }

    function addSkill(){
        const inp = document.getElementById('skillInput');
        const val = inp.value.trim();
        if(!val) return;
        val.split(',').forEach(s=>{ s=s.trim(); if(s && !skills.includes(s)) skills.push(s); });
        inp.value = '';
        renderSkills();
    }
    document.getElementById('addSkillBtn').addEventListener('click', addSkill);
    document.getElementById('skillInput').addEventListener('keydown', e=>{ if(e.key==='Enter'){ e.preventDefault(); addSkill(); } });

    /* ── Hero name live-update ── */
    ['namef','namem','namel'].forEach(id=>{
        document.getElementById(id).addEventListener('input', ()=>{
            const f = document.getElementById('namef').value.trim();
            const m = document.getElementById('namem').value.trim();
            const l = document.getElementById('namel').value.trim();
            document.getElementById('heroName').textContent = [f,m,l].filter(Boolean).join(' ') || '<?= addslashes($fullname ?? '') ?>';
        });
    });

    /* ── Education preview ── */
    function updateEduPreview(){
        const deg  = document.getElementById('main_academic_level').value;
        const uni  = document.getElementById('sub_academic_level').value.trim();
        const crs  = document.getElementById('degree_title').value.trim();
        const sy   = document.getElementById('start-year-2a').value;
        const st   = document.getElementById('end-year-2a').value;
        document.getElementById('epTitle').textContent = crs  || '—';
        document.getElementById('epUni').textContent   = uni  ? (deg ? deg+' · '+uni : uni) : (deg || '—');
        document.getElementById('epYear').textContent  = sy   ? (sy+' · '+st) : '—';
    }
    ['main_academic_level','sub_academic_level','degree_title','start-year-2a','end-year-2a'].forEach(id=>{
        document.getElementById(id).addEventListener('change', updateEduPreview);
        document.getElementById(id).addEventListener('input',  updateEduPreview);
    });

    /* ── Load existing data ── */
    function escHtml(s){ const d=document.createElement('div'); d.textContent=s; return d.innerHTML; }

    function loadExisting(){
        const id = '<?= addslashes($_SESSION['usr_code'] ?? '') ?>';
        if(!id) return;
        fetch('ajax/ajax_get_teacher.php?id='+encodeURIComponent(id))
        .then(r=>r.json()).then(res=>{
            if(!res) return;
            const set = (id,v)=>{ const el=document.getElementById(id); if(el && v!=null) el.value=v; };
            set('namef',  res.first_name);
            set('namem',  res.middle_name);
            set('namel',  res.last_name);
            set('datepicker', res.dob);
            set('describe3',  res.description);
            document.getElementById('bioCount').textContent = (res.description||'').length;
            // skills
            if(res.skill){ skills = res.skill.split(',').map(s=>s.trim()).filter(Boolean); renderSkills(); }
            set('namefull',      res.parent_name);
            set('phoneon2',      res.phone);
            set('emailaddresson1', res.email);
            set('street',        res.street);
            set('locality',      res.locality);
            set('town',          res.town);
            set('city',          res.city);
            set('country',       res.country);
            set('sub_academic_level',  res.sub_academic_level);
            set('main_academic_level', res.main_academic_level);
            set('degree_title',        res.course);
            set('start-year-2a',       res.start_year);
            set('end-year-2a',         res.end_year);

            if(res.image){
                document.getElementById('previewImage').src = res.image;
                document.getElementById('heroAvatar').src   = res.image;
            }
            updateEduPreview();
        }).catch(console.error);
    }

    /* ── Save ── */
    window.saveProfile = function(){
        const btn = document.getElementById('saveBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

        const data = {
            id:               '<?= addslashes($_SESSION['usr_code'] ?? '') ?>',
            first_name:       document.getElementById('namef').value,
            middle_name:      document.getElementById('namem').value,
            last_name:        document.getElementById('namel').value,
            dob:              document.getElementById('datepicker').value,
            description:      document.getElementById('describe3').value,
            skill:            document.getElementById('tags1').value,
            parent_name:      document.getElementById('namefull').value,
            phone:            document.getElementById('phoneon2').value,
            email:            document.getElementById('emailaddresson1').value,
            street:           document.getElementById('street').value,
            town:             document.getElementById('town').value,
            city:             document.getElementById('city').value,
            country:          document.getElementById('country').value,
            main_academic_level: document.getElementById('main_academic_level').value,
            sub_academic_level:  document.getElementById('sub_academic_level').value,
            course:              document.getElementById('degree_title').value,
            start_year:          document.getElementById('start-year-2a').value,
            end_year:            document.getElementById('end-year-2a').value,
            image:               base64Image
        };

        fetch('ajax/ajax_save_teacher.php',{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify(data)
        })
        .then(r=>r.json())
        .then(res=>{
            if(res.status==='success'){
                Swal.fire({
                    icon:'success', title:'Profile Saved!',
                    text: res.message,
                    confirmButtonText:'Go to Dashboard',
                    confirmButtonColor:'#6366f1'
                }).then(()=>{ window.location.href='?view=3002'; });
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        })
        .catch(()=> Swal.fire('Error','Something went wrong. Please try again.','error'))
        .finally(()=>{
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Save Profile';
        });
    };

    /* ── Boot ── */
    if(document.readyState==='loading'){
        document.addEventListener('DOMContentLoaded', loadExisting);
    } else {
        loadExisting();
    }
})();
</script>
