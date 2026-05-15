<?php
$cv = $_GET['view'] ?? '';

$groups = [
    'menuTaxonomy'    => ['qb_subjects','qb_levels','qb_chapters','qb_subtopics','qb_bloom_levels','qb_difficulty_levels','qb_sections'],
    'menuQuestions'   => ['qb_all_questions','qb_add_question','qb_bulk_upload','qb_draft_questions','qb_review_queue','qb_approved_questions','qb_published_questions','qb_archived_questions','qb_question_media'],
    'menuExamBuilder' => ['qb_create_exam','qb_exam_templates','qb_random_exam','qb_cbt_exams','qb_print_exams'],
    'menuCurriculum'  => ['qb_competencies','qb_learning_outcomes','qb_curriculum_references'],
    'menuAnalytics'   => ['qb_question_usage','qb_difficulty_analytics','qb_student_performance','qb_most_failed','qb_bloom_distribution'],
    'menuImportExport'=> ['qb_excel_import','qb_csv_import','qb_export_questions','qb_import_logs'],
    'menuAiTools'     => ['qb_ai_generate','qb_ai_explanations','qb_ai_difficulty','qb_duplicate_detector'],
    'menuSettings'    => ['qb_id_rules','qb_approval_workflow','qb_media_storage','qb_general_settings'],
];

$open = function(string $id) use ($groups, $cv): bool {
    return in_array($cv, $groups[$id] ?? []);
};

// Returns parent link classes: adds 'collapsed' when section is closed
$pc = function(string $id) use ($open): string {
    return 'nav-link qb-parent' . ($open($id) ? '' : ' collapsed');
};

// Returns collapse div classes: adds 'show' when section is open
$cc = function(string $id) use ($open): string {
    return 'collapse' . ($open($id) ? ' show' : '');
};

// Returns 'true'/'false' for aria-expanded
$ae = function(string $id) use ($open): string {
    return $open($id) ? 'true' : 'false';
};

// Returns nav-link classes for a submenu item, adding 'active' on match
$lc = function(string $view) use ($cv): string {
    return 'nav-link' . ($cv === $view ? ' active' : '');
};
?>
<ul class="nav flex-column menu-active-line">

    <!-- Dashboard -->
    <li class="nav-item">
        <a href="../data_files/?view=3002" class="nav-link<?= $cv === '3002' ? ' active' : '' ?>">
            <i class="menu-icon bi bi-columns-gap"></i>
            <span class="menu-name">Dashboard</span>
        </a>
    </li>

    <!-- Taxonomy Management -->
    <li class="nav-item">
        <a href="#menuTaxonomy" class="<?= $pc('menuTaxonomy') ?>" data-bs-toggle="collapse" aria-expanded="<?= $ae('menuTaxonomy') ?>">
            <i class="menu-icon bi bi-diagram-3"></i>
            <span class="menu-name">Taxonomy </span><i class="bi bi-chevron-down qb-chevron"></i>
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

    <!-- Question Management -->
    <li class="nav-item">
        <a href="#menuQuestions" class="<?= $pc('menuQuestions') ?>" data-bs-toggle="collapse" aria-expanded="<?= $ae('menuQuestions') ?>">
            <i class="menu-icon bi bi-patch-question"></i>
            <span class="menu-name">Questions </span><i class="bi bi-chevron-down qb-chevron"></i>
        </a>
        <div class="<?= $cc('menuQuestions') ?>" id="menuQuestions">
            <ul class="nav flex-column qb-submenu">
                <li class="nav-item"><a href="../data_files/?view=qb_all_questions"      class="<?= $lc('qb_all_questions') ?>"><i class="bi bi-list-ul"></i><span>All Questions</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_add_question"       class="<?= $lc('qb_add_question') ?>"><i class="bi bi-plus-circle"></i><span>Add Question</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_bulk_upload"        class="<?= $lc('qb_bulk_upload') ?>"><i class="bi bi-cloud-upload"></i><span>Bulk Upload</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_draft_questions"    class="<?= $lc('qb_draft_questions') ?>"><i class="bi bi-pencil-square"></i><span>Draft Questions</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_review_queue"       class="<?= $lc('qb_review_queue') ?>"><i class="bi bi-hourglass-split"></i><span>Review Queue</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_approved_questions" class="<?= $lc('qb_approved_questions') ?>"><i class="bi bi-check-circle"></i><span>Approved Questions</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_published_questions"class="<?= $lc('qb_published_questions') ?>"><i class="bi bi-send-check"></i><span>Published Questions</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_archived_questions" class="<?= $lc('qb_archived_questions') ?>"><i class="bi bi-archive"></i><span>Archived Questions</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_question_media"     class="<?= $lc('qb_question_media') ?>"><i class="bi bi-image"></i><span>Question Media</span></a></li>
            </ul>
        </div>
    </li>

    <!-- Exam Builder -->
    <li class="nav-item">
        <a href="#menuExamBuilder" class="<?= $pc('menuExamBuilder') ?>" data-bs-toggle="collapse" aria-expanded="<?= $ae('menuExamBuilder') ?>">
            <i class="menu-icon bi bi-file-earmark-ruled"></i>
            <span class="menu-name">Exam Builder </span><i class="bi bi-chevron-down qb-chevron"></i>
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

    <!-- Curriculum Mapping -->
    <li class="nav-item">
        <a href="#menuCurriculum" class="<?= $pc('menuCurriculum') ?>" data-bs-toggle="collapse" aria-expanded="<?= $ae('menuCurriculum') ?>">
            <i class="menu-icon bi bi-map"></i>
            <span class="menu-name">Curriculum </span><i class="bi bi-chevron-down qb-chevron"></i>
        </a>
        <div class="<?= $cc('menuCurriculum') ?>" id="menuCurriculum">
            <ul class="nav flex-column qb-submenu">
                <li class="nav-item"><a href="../data_files/?view=qb_competencies"          class="<?= $lc('qb_competencies') ?>"><i class="bi bi-award"></i><span>Competencies</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_learning_outcomes"     class="<?= $lc('qb_learning_outcomes') ?>"><i class="bi bi-bullseye"></i><span>Learning Outcomes</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_curriculum_references" class="<?= $lc('qb_curriculum_references') ?>"><i class="bi bi-journal-bookmark"></i><span>Curriculum References</span></a></li>
            </ul>
        </div>
    </li>

    <!-- Analytics -->
    <li class="nav-item">
        <a href="#menuAnalytics" class="<?= $pc('menuAnalytics') ?>" data-bs-toggle="collapse" aria-expanded="<?= $ae('menuAnalytics') ?>">
            <i class="menu-icon bi bi-bar-chart-line"></i>
            <span class="menu-name">Analytics </span><i class="bi bi-chevron-down qb-chevron"></i>
        </a>
        <div class="<?= $cc('menuAnalytics') ?>" id="menuAnalytics">
            <ul class="nav flex-column qb-submenu">
                <li class="nav-item"><a href="../data_files/?view=qb_question_usage"      class="<?= $lc('qb_question_usage') ?>"><i class="bi bi-graph-up"></i><span>Question Usage</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_difficulty_analytics"class="<?= $lc('qb_difficulty_analytics') ?>"><i class="bi bi-activity"></i><span>Difficulty Analytics</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_student_performance" class="<?= $lc('qb_student_performance') ?>"><i class="bi bi-people"></i><span>Student Performance</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_most_failed"         class="<?= $lc('qb_most_failed') ?>"><i class="bi bi-exclamation-triangle"></i><span>Most Failed Questions</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_bloom_distribution"  class="<?= $lc('qb_bloom_distribution') ?>"><i class="bi bi-pie-chart"></i><span>Bloom Distribution</span></a></li>
            </ul>
        </div>
    </li>

    <!-- Imports & Exports -->
    <li class="nav-item">
        <a href="#menuImportExport" class="<?= $pc('menuImportExport') ?>" data-bs-toggle="collapse" aria-expanded="<?= $ae('menuImportExport') ?>">
            <i class="menu-icon bi bi-arrow-left-right"></i>
            <span class="menu-name">Imports &amp; Exports </span><i class="bi bi-chevron-down qb-chevron"></i>
        </a>
        <div class="<?= $cc('menuImportExport') ?>" id="menuImportExport">
            <ul class="nav flex-column qb-submenu">
                <li class="nav-item"><a href="../data_files/?view=qb_excel_import"    class="<?= $lc('qb_excel_import') ?>"><i class="bi bi-file-earmark-excel"></i><span>Excel Import</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_csv_import"      class="<?= $lc('qb_csv_import') ?>"><i class="bi bi-filetype-csv"></i><span>CSV Import</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_export_questions"class="<?= $lc('qb_export_questions') ?>"><i class="bi bi-download"></i><span>Export Questions</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_import_logs"     class="<?= $lc('qb_import_logs') ?>"><i class="bi bi-journal-text"></i><span>Import Logs</span></a></li>
            </ul>
        </div>
    </li>

    <!-- AI Tools -->
    <li class="nav-item">
        <a href="#menuAiTools" class="<?= $pc('menuAiTools') ?>" data-bs-toggle="collapse" aria-expanded="<?= $ae('menuAiTools') ?>">
            <i class="menu-icon bi bi-stars"></i>
            <span class="menu-name">AI Tools </span><i class="bi bi-chevron-down qb-chevron"></i>
        </a>
        <div class="<?= $cc('menuAiTools') ?>" id="menuAiTools">
            <ul class="nav flex-column qb-submenu">
                <li class="nav-item"><a href="../data_files/?view=qb_ai_generate"      class="<?= $lc('qb_ai_generate') ?>"><i class="bi bi-magic"></i><span>Generate Questions</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_ai_explanations"  class="<?= $lc('qb_ai_explanations') ?>"><i class="bi bi-chat-left-text"></i><span>Generate Explanations</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_ai_difficulty"    class="<?= $lc('qb_ai_difficulty') ?>"><i class="bi bi-cpu"></i><span>Auto Difficulty Detection</span></a></li>
                <li class="nav-item"><a href="../data_files/?view=qb_duplicate_detector"class="<?= $lc('qb_duplicate_detector') ?>"><i class="bi bi-copy"></i><span>Duplicate Detector</span></a></li>
            </ul>
        </div>
    </li>

    <!-- Settings -->
    <li class="nav-item">
        <a href="#menuSettings" class="<?= $pc('menuSettings') ?>" data-bs-toggle="collapse" aria-expanded="<?= $ae('menuSettings') ?>">
            <i class="menu-icon bi bi-gear"></i>
            <span class="menu-name">Settings </span><i class="bi bi-chevron-down qb-chevron"></i>
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
