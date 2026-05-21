<?php
$cv = $_GET['view'] ?? '';

$groups = [
    'menuAdmin'      => ['admin_dashboard','admin_users','admin_roles','admin_permissions','admin_courses','admin_course_detail','admin_payment_settings','admin_course_reviews','admin_organizations','admin_org_detail'],
    'menuOrgAdmin'   => ['org_members','org_departments','org_reports','org_courses'],
    'menuCourseMgmt' => ['my_courses_online_contents_list_view','course_contents_management','view_course_details','teacher_study_notes','study_notes_manager'],
    'menuTaxonomy'   => ['qb_subjects','qb_levels','qb_chapters','qb_subtopics','qb_bloom_levels','qb_difficulty_levels','qb_sections'],
    'menuQuestions'  => ['qb_all_questions','qb_add_question','qb_bulk_upload','qb_draft_questions','qb_review_queue','qb_approved_questions','qb_published_questions','qb_archived_questions','qb_question_media'],
    'menuExamBuilder'=> ['qb_create_exam','qb_exam_templates','qb_random_exam','qb_cbt_exams','qb_print_exams'],
    'menuStudentExam'=> ['student_exams','student_take_exam','student_exam_results'],
    'menuCurriculum' => ['qb_competencies','qb_learning_outcomes','qb_curriculum_references'],
    'menuAnalytics'  => ['qb_question_usage','qb_difficulty_analytics','qb_student_performance','qb_most_failed','qb_bloom_distribution'],
    'menuImportExport'=> ['qb_excel_import','qb_csv_import','qb_export_questions','qb_import_logs'],
    'menuAiTools'    => ['qb_ai_generate','qb_ai_explanations','qb_ai_difficulty','qb_duplicate_detector'],
    'menuSettings'   => ['qb_id_rules','qb_approval_workflow','qb_media_storage','qb_general_settings'],
];

$open = fn(string $id) => in_array($cv, $groups[$id] ?? []);
$pc   = fn(string $id) => 'nav-link qb-parent' . ($open($id) ? '' : ' collapsed');
$cc   = fn(string $id) => 'collapse' . ($open($id) ? ' show' : '');
$ae   = fn(string $id) => $open($id) ? 'true' : 'false';
$lc   = fn(string $view) => 'nav-link' . ($cv === $view ? ' active' : '');
?>
<ul class="nav flex-column menu-active-line">

    <!-- Dashboard -->
    <div class="dcm-nav-group">Navigation</div>
    <li class="nav-item">
        <a href="../data_files/?view=3002" class="nav-link<?= in_array($cv, ['3002','study_notes_viewer','read_course_details_data','learning-student-home']) ? ' active' : '' ?>">
            <i class="menu-icon bi bi-columns-gap"></i>
            <span class="menu-name">Dashboard</span>
        </a>
    </li>

    <!-- ── SUPER ADMIN ──────────────────────────────────────── -->
    <?php if ($user_role == 5): ?>
    <div class="dcm-nav-group">Administration</div>
    <li class="nav-item">
        <a href="#menuAdmin" class="<?= $pc('menuAdmin') ?>" data-bs-toggle="collapse" aria-expanded="<?= $ae('menuAdmin') ?>">
            <i class="menu-icon bi bi-shield-lock"></i>
            <span class="menu-name">Super Admin</span><i class="bi bi-chevron-down qb-chevron"></i>
        </a>
        <div class="<?= $cc('menuAdmin') ?>" id="menuAdmin">
            <ul class="nav flex-column qb-submenu">
                <li class="nav-item">
                    <a href="../data_files/?view=admin_dashboard" class="<?= $lc('admin_dashboard') ?>">
                        <i class="bi bi-speedometer2"></i><span>Overview</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../data_files/?view=admin_users" class="<?= $lc('admin_users') ?>">
                        <i class="bi bi-people"></i><span>Manage Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../data_files/?view=admin_roles" class="<?= $lc('admin_roles') ?>">
                        <i class="bi bi-layers"></i><span>Roles</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../data_files/?view=admin_permissions" class="<?= $lc('admin_permissions') ?>">
                        <i class="bi bi-toggle-on"></i><span>Module Permissions</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../data_files/?view=admin_courses" class="<?= $lc('admin_courses') === 'nav-link active' || $lc('admin_course_detail') === 'nav-link active' ? 'nav-link active' : 'nav-link' ?>">
                        <i class="bi bi-collection-play-fill"></i><span>All Courses</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../data_files/?view=admin_course_reviews" class="<?= $lc('admin_course_reviews') ?>" id="sideReviewLink">
                        <i class="bi bi-shield-check"></i>
                        <span>Course Reviews</span>
                        <span id="sideReviewBadge" style="display:none;margin-left:auto;background:#f59e0b;color:#fff;border-radius:20px;font-size:.62rem;font-weight:700;padding:.1rem .45rem;line-height:1.6"></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../data_files/?view=admin_payment_settings" class="<?= $lc('admin_payment_settings') ?>">
                        <i class="bi bi-credit-card"></i><span>Payment Settings</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../data_files/?view=admin_organizations" class="<?= in_array($cv, ['admin_organizations','admin_org_detail']) ? 'nav-link active' : 'nav-link' ?>">
                        <i class="bi bi-building"></i><span>Organizations</span>
                    </a>
                </li>
            </ul>
        </div>
    </li>
    <?php endif; ?>

    <!-- ── ORG ADMIN ─────────────────────────────────────────── -->
    <?php if (($user_role ?? 0) == 4): ?>
    <div class="dcm-nav-group">Organization</div>
    <li class="nav-item">
        <a href="#menuOrgAdmin" class="<?= $pc('menuOrgAdmin') ?>" data-bs-toggle="collapse" aria-expanded="<?= $ae('menuOrgAdmin') ?>">
            <i class="menu-icon bi bi-building"></i>
            <span class="menu-name">My Organization</span><i class="bi bi-chevron-down qb-chevron"></i>
        </a>
        <div class="<?= $cc('menuOrgAdmin') ?>" id="menuOrgAdmin">
            <ul class="nav flex-column qb-submenu">
                <li class="nav-item">
                    <a href="../data_files/?view=org_members" class="<?= $lc('org_members') ?>">
                        <i class="bi bi-people"></i><span>Members</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../data_files/?view=org_departments" class="<?= $lc('org_departments') ?>">
                        <i class="bi bi-building"></i><span>Departments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../data_files/?view=org_courses" class="<?= $lc('org_courses') ?>">
                        <i class="bi bi-collection-play"></i><span>Course Catalog</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../data_files/?view=org_reports" class="<?= $lc('org_reports') ?>">
                        <i class="bi bi-bar-chart-line"></i><span>Reports</span>
                    </a>
                </li>
            </ul>
        </div>
    </li>
    <?php endif; ?>
    <script>
    (function(){
        fetch('../data_files/ajax/ajax_course_review.php?action=stats')
        .then(r=>r.json()).then(r=>{
            if(r.status==='success' && r.pending > 0){
                const b = document.getElementById('sideReviewBadge');
                if(b){ b.textContent = r.pending; b.style.display='inline'; }
            }
        }).catch(()=>{});
    })();
    </script>

    <!-- Course Management -->
    <?php if (canAccessModule($user_perms, 'course_management')): ?>
    <div class="dcm-nav-group">Content</div>
    <li class="nav-item">
        <a href="#menuCourseMgmt" class="<?= $pc('menuCourseMgmt') ?>" data-bs-toggle="collapse" aria-expanded="<?= $ae('menuCourseMgmt') ?>">
            <i class="menu-icon bi bi-collection-play"></i>
            <span class="menu-name">Course Management</span><i class="bi bi-chevron-down qb-chevron"></i>
        </a>
        <div class="<?= $cc('menuCourseMgmt') ?>" id="menuCourseMgmt">
            <ul class="nav flex-column qb-submenu">
                <li class="nav-item">
                    <a href="../data_files/?view=my_courses_online_contents_list_view" class="<?= $lc('my_courses_online_contents_list_view') ?>">
                        <i class="bi bi-collection"></i><span>My Courses</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../data_files/?view=teacher_study_notes" class="<?= $lc('teacher_study_notes') ?>">
                        <i class="bi bi-journal-bookmark"></i><span>Manage Study Notes</span>
                    </a>
                </li>
            </ul>
        </div>
    </li>
    <?php endif; ?>

    <!-- Taxonomy Management -->
    <?php if (canAccessModule($user_perms, 'qb_taxonomy')): ?>
    <div class="dcm-nav-group">Question Bank</div>
    <li class="nav-item">
        <a href="#menuTaxonomy" class="<?= $pc('menuTaxonomy') ?>" data-bs-toggle="collapse" aria-expanded="<?= $ae('menuTaxonomy') ?>">
            <i class="menu-icon bi bi-diagram-3"></i>
            <span class="menu-name">Taxonomy</span><i class="bi bi-chevron-down qb-chevron"></i>
        </a>
        <div class="<?= $cc('menuTaxonomy') ?>" id="menuTaxonomy">
            <ul class="nav flex-column qb-submenu">
                <li class="nav-item"><a href="../data_files/?view=qb_subjects"          class="<?= $lc('qb_subjects') ?>"><i class="bi bi-book"></i><span>Subjects</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_levels"            class="<?= $lc('qb_levels') ?>"><i class="bi bi-layers"></i><span>Levels / Classes</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_chapters"          class="<?= $lc('qb_chapters') ?>"><i class="bi bi-bookmark"></i><span>Chapters</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_subtopics"         class="<?= $lc('qb_subtopics') ?>"><i class="bi bi-bookmarks"></i><span>Subtopics</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_bloom_levels"      class="<?= $lc('qb_bloom_levels') ?>"><i class="bi bi-bar-chart-steps"></i><span>Bloom Levels</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_difficulty_levels" class="<?= $lc('qb_difficulty_levels') ?>"><i class="bi bi-speedometer2"></i><span>Difficulty Levels</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_sections"          class="<?= $lc('qb_sections') ?>"><i class="bi bi-grid"></i><span>Sections</span></a></li>
            </ul>
        </div>
    </li>
    <?php endif; ?>

    <!-- Question Management -->
    <?php if (canAccessModule($user_perms, 'qb_questions')): ?>
    <li class="nav-item">
        <a href="#menuQuestions" class="<?= $pc('menuQuestions') ?>" data-bs-toggle="collapse" aria-expanded="<?= $ae('menuQuestions') ?>">
            <i class="menu-icon bi bi-patch-question"></i>
            <span class="menu-name">Questions</span><i class="bi bi-chevron-down qb-chevron"></i>
        </a>
        <div class="<?= $cc('menuQuestions') ?>" id="menuQuestions">
            <ul class="nav flex-column qb-submenu">
                <li class="nav-item"><a href="../data_files/?view=qb_all_questions"      class="<?= $lc('qb_all_questions') ?>"><i class="bi bi-list-ul"></i><span>All Questions</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_add_question"       class="<?= $lc('qb_add_question') ?>"><i class="bi bi-plus-circle"></i><span>Add Question</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_bulk_upload"        class="<?= $lc('qb_bulk_upload') ?>"><i class="bi bi-cloud-upload"></i><span>Bulk Upload</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_draft_questions"    class="<?= $lc('qb_draft_questions') ?>"><i class="bi bi-pencil-square"></i><span>Draft Questions</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_review_queue"       class="<?= $lc('qb_review_queue') ?>"><i class="bi bi-hourglass-split"></i><span>Review Queue</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_approved_questions" class="<?= $lc('qb_approved_questions') ?>"><i class="bi bi-check-circle"></i><span>Approved</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_published_questions"class="<?= $lc('qb_published_questions') ?>"><i class="bi bi-send-check"></i><span>Published</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_archived_questions" class="<?= $lc('qb_archived_questions') ?>"><i class="bi bi-archive"></i><span>Archived</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_question_media"     class="<?= $lc('qb_question_media') ?>"><i class="bi bi-image"></i><span>Question Media</span></a></li>
            </ul>
        </div>
    </li>
    <?php endif; ?>

    <!-- Exam Builder -->
    <?php if (canAccessModule($user_perms, 'qb_exams')): ?>
    <li class="nav-item">
        <a href="#menuExamBuilder" class="<?= $pc('menuExamBuilder') ?>" data-bs-toggle="collapse" aria-expanded="<?= $ae('menuExamBuilder') ?>">
            <i class="menu-icon bi bi-file-earmark-ruled"></i>
            <span class="menu-name">Exam Builder</span><i class="bi bi-chevron-down qb-chevron"></i>
        </a>
        <div class="<?= $cc('menuExamBuilder') ?>" id="menuExamBuilder">
            <ul class="nav flex-column qb-submenu">
                <li class="nav-item"><a href="../data_files/?view=qb_create_exam"    class="<?= $lc('qb_create_exam') ?>"><i class="bi bi-plus-square"></i><span>Create Exam</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_exam_templates" class="<?= $lc('qb_exam_templates') ?>"><i class="bi bi-layout-text-window"></i><span>Exam Templates</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_random_exam"    class="<?= $lc('qb_random_exam') ?>"><i class="bi bi-shuffle"></i><span>Random Exam Generator</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_cbt_exams"      class="<?= $lc('qb_cbt_exams') ?>"><i class="bi bi-laptop"></i><span>CBT Exams</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_print_exams"    class="<?= $lc('qb_print_exams') ?>"><i class="bi bi-printer"></i><span>Print Exams</span></a></li>
            </ul>
        </div>
    </li>
    <?php endif; ?>

    <!-- Curriculum Mapping -->
    <?php if (canAccessModule($user_perms, 'qb_curriculum')): ?>
    <div class="dcm-nav-group">Curriculum & Analytics</div>
    <li class="nav-item">
        <a href="#menuCurriculum" class="<?= $pc('menuCurriculum') ?>" data-bs-toggle="collapse" aria-expanded="<?= $ae('menuCurriculum') ?>">
            <i class="menu-icon bi bi-map"></i>
            <span class="menu-name">Curriculum</span><i class="bi bi-chevron-down qb-chevron"></i>
        </a>
        <div class="<?= $cc('menuCurriculum') ?>" id="menuCurriculum">
            <ul class="nav flex-column qb-submenu">
                <li class="nav-item"><a href="../data_files/?view=qb_competencies"          class="<?= $lc('qb_competencies') ?>"><i class="bi bi-award"></i><span>Competencies</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_learning_outcomes"     class="<?= $lc('qb_learning_outcomes') ?>"><i class="bi bi-bullseye"></i><span>Learning Outcomes</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_curriculum_references" class="<?= $lc('qb_curriculum_references') ?>"><i class="bi bi-journal-bookmark"></i><span>Curriculum References</span></a></li>
            </ul>
        </div>
    </li>
    <?php endif; ?>

    <!-- Analytics -->
    <?php if (canAccessModule($user_perms, 'qb_analytics')): ?>
    <li class="nav-item">
        <a href="#menuAnalytics" class="<?= $pc('menuAnalytics') ?>" data-bs-toggle="collapse" aria-expanded="<?= $ae('menuAnalytics') ?>">
            <i class="menu-icon bi bi-bar-chart-line"></i>
            <span class="menu-name">Analytics</span><i class="bi bi-chevron-down qb-chevron"></i>
        </a>
        <div class="<?= $cc('menuAnalytics') ?>" id="menuAnalytics">
            <ul class="nav flex-column qb-submenu">
                <li class="nav-item"><a href="../data_files/?view=qb_question_usage"       class="<?= $lc('qb_question_usage') ?>"><i class="bi bi-graph-up"></i><span>Question Usage</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_difficulty_analytics" class="<?= $lc('qb_difficulty_analytics') ?>"><i class="bi bi-activity"></i><span>Difficulty Analytics</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_student_performance"  class="<?= $lc('qb_student_performance') ?>"><i class="bi bi-people"></i><span>Student Performance</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_most_failed"          class="<?= $lc('qb_most_failed') ?>"><i class="bi bi-exclamation-triangle"></i><span>Most Failed</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_bloom_distribution"   class="<?= $lc('qb_bloom_distribution') ?>"><i class="bi bi-pie-chart"></i><span>Bloom Distribution</span></a></li>
            </ul>
        </div>
    </li>
    <?php endif; ?>

    <!-- Imports & Exports -->
    <?php if (canAccessModule($user_perms, 'qb_import_export')): ?>
    <div class="dcm-nav-group">Data & Tools</div>
    <li class="nav-item">
        <a href="#menuImportExport" class="<?= $pc('menuImportExport') ?>" data-bs-toggle="collapse" aria-expanded="<?= $ae('menuImportExport') ?>">
            <i class="menu-icon bi bi-arrow-left-right"></i>
            <span class="menu-name">Imports &amp; Exports</span><i class="bi bi-chevron-down qb-chevron"></i>
        </a>
        <div class="<?= $cc('menuImportExport') ?>" id="menuImportExport">
            <ul class="nav flex-column qb-submenu">
                <li class="nav-item"><a href="../data_files/?view=qb_excel_import"     class="<?= $lc('qb_excel_import') ?>"><i class="bi bi-file-earmark-excel"></i><span>Excel Import</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_csv_import"       class="<?= $lc('qb_csv_import') ?>"><i class="bi bi-filetype-csv"></i><span>CSV Import</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_export_questions" class="<?= $lc('qb_export_questions') ?>"><i class="bi bi-download"></i><span>Export Questions</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_import_logs"      class="<?= $lc('qb_import_logs') ?>"><i class="bi bi-journal-text"></i><span>Import Logs</span></a></li>
            </ul>
        </div>
    </li>
    <?php endif; ?>

    <!-- AI Tools -->
    <?php if (canAccessModule($user_perms, 'qb_ai_tools')): ?>
    <li class="nav-item">
        <a href="#menuAiTools" class="<?= $pc('menuAiTools') ?>" data-bs-toggle="collapse" aria-expanded="<?= $ae('menuAiTools') ?>">
            <i class="menu-icon bi bi-stars"></i>
            <span class="menu-name">AI Tools</span><i class="bi bi-chevron-down qb-chevron"></i>
        </a>
        <div class="<?= $cc('menuAiTools') ?>" id="menuAiTools">
            <ul class="nav flex-column qb-submenu">
                <li class="nav-item"><a href="../data_files/?view=qb_ai_generate"       class="<?= $lc('qb_ai_generate') ?>"><i class="bi bi-magic"></i><span>Generate Questions</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_ai_explanations"   class="<?= $lc('qb_ai_explanations') ?>"><i class="bi bi-chat-left-text"></i><span>Generate Explanations</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_ai_difficulty"     class="<?= $lc('qb_ai_difficulty') ?>"><i class="bi bi-cpu"></i><span>Auto Difficulty Detection</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_duplicate_detector"class="<?= $lc('qb_duplicate_detector') ?>"><i class="bi bi-copy"></i><span>Duplicate Detector</span></a></li>
            </ul>
        </div>
    </li>
    <?php endif; ?>

    <!-- Student Exam Centre -->
    <?php if (canAccessModule($user_perms, 'student_exams')): ?>
    <div class="dcm-nav-group">Student Centre</div>
    <li class="nav-item">
        <a href="#menuStudentExam" class="<?= $pc('menuStudentExam') ?>" data-bs-toggle="collapse" aria-expanded="<?= $ae('menuStudentExam') ?>">
            <i class="menu-icon bi bi-mortarboard-fill"></i>
            <span class="menu-name">My Exams</span><i class="bi bi-chevron-down qb-chevron"></i>
        </a>
        <div class="<?= $cc('menuStudentExam') ?>" id="menuStudentExam">
            <ul class="nav flex-column qb-submenu">
                <li class="nav-item"><a href="../data_files/?view=student_exams" class="<?= $lc('student_exams') ?>"><i class="bi bi-collection-fill"></i><span>Exam Portal</span></a></li>
            </ul>
        </div>
    </li>
    <?php endif; ?>

    <!-- Two-Factor Authentication -->
    <div class="dcm-nav-group">Security</div>
    <li class="nav-item">
        <a href="../data_files/?view=admin_2fa" class="<?= $lc('admin_2fa') ?>">
            <i class="menu-icon bi bi-shield-check"></i>
            <span class="menu-name">Two-Factor Auth</span>
        </a>
    </li>

    <!-- Settings -->
    <?php if (canAccessModule($user_perms, 'qb_settings')): ?>
    <div class="dcm-nav-group">System</div>
    <li class="nav-item">
        <a href="#menuSettings" class="<?= $pc('menuSettings') ?>" data-bs-toggle="collapse" aria-expanded="<?= $ae('menuSettings') ?>">
            <i class="menu-icon bi bi-gear"></i>
            <span class="menu-name">Settings</span><i class="bi bi-chevron-down qb-chevron"></i>
        </a>
        <div class="<?= $cc('menuSettings') ?>" id="menuSettings">
            <ul class="nav flex-column qb-submenu">
                <li class="nav-item"><a href="../data_files/?view=qb_id_rules"          class="<?= $lc('qb_id_rules') ?>"><i class="bi bi-hash"></i><span>Question ID Rules</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_approval_workflow" class="<?= $lc('qb_approval_workflow') ?>"><i class="bi bi-diagram-2"></i><span>Approval Workflow</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_media_storage"     class="<?= $lc('qb_media_storage') ?>"><i class="bi bi-hdd"></i><span>Media Storage</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_general_settings"  class="<?= $lc('qb_general_settings') ?>"><i class="bi bi-sliders"></i><span>General Settings</span></a></li>
            </ul>
        </div>
    </li>
    <?php endif; ?>

</ul>
<div class="mt-auto"></div>
<ul class="nav flex-column menu-active-line mb-2">
    <li class="nav-item"><a href="../data_files/?view=learning-chat-call" class="nav-link">
            <div class="col-auto">
                <div class="avatar avatar-30 coverimg rounded d-block align-top"><img
                        src="<?php echo $userProfileImage; ?>" alt=""></div>
            </div>
            <div class="col px-2 menu-name text-start not-iconic">
                <p class="mb-0 fs-14 lh-20"><?php echo $fullname; ?><br><small
                        class="opacity-50"><?php echo $roleTitle; ?></small>
                </p>
            </div>
            <div class="col-auto not-iconic"><i class="bi bi-chat-dots"></i></div>
        </a></li>
</ul>
<div class="px-3 not-iconic">
    <div class="card border-0">
        <div class="card-body p-2">
            <div class="row gx-2">
                <div class="col-12 d-flex justify-content-between">
                    <a href="../data_files/?view=kill-session-user" class="btn btn-square btn-link" title="Logout">
                        <i data-feather="log-out"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
