<?php
/* Central definition of all permission-gated modules.
   module_key => label
   Used by: side_menu.php, admin_permissions.php, controller.php
*/

define('APP_MODULES', [
    'course_management' => ['label' => 'Course Management',  'icon' => 'bi-collection-play'],
    'admin_courses'     => ['label' => 'All Courses (Admin)', 'icon' => 'bi-collection-play-fill'],
    'qb_taxonomy'       => ['label' => 'QB: Taxonomy',        'icon' => 'bi-diagram-3'],
    'qb_questions'      => ['label' => 'QB: Questions',       'icon' => 'bi-patch-question'],
    'qb_exams'          => ['label' => 'QB: Exam Builder',    'icon' => 'bi-clipboard-check'],
    'qb_curriculum'     => ['label' => 'QB: Curriculum',      'icon' => 'bi-book'],
    'qb_analytics'      => ['label' => 'QB: Analytics',       'icon' => 'bi-bar-chart-line'],
    'qb_import_export'  => ['label' => 'QB: Import & Export', 'icon' => 'bi-upload'],
    'qb_ai_tools'       => ['label' => 'QB: AI Tools',        'icon' => 'bi-stars'],
    'qb_settings'       => ['label' => 'QB: Settings',        'icon' => 'bi-gear'],
    'student_exams'     => ['label' => 'Student Exams',        'icon' => 'bi-mortarboard-fill'],
]);

// Views that belong to each module — used for access-gating in controller.php
define('MODULE_VIEWS', [
    'course_management' => [
        'my_courses_online_contents_list_view','course_contents_management',
        'teacher_study_notes','study_notes_manager',
    ],
    'admin_courses' => ['admin_courses','admin_course_detail'],
    'qb_taxonomy' => [
        'qb_subjects','qb_levels','qb_chapters','qb_subtopics',
        'qb_bloom_levels','qb_difficulty_levels','qb_sections',
    ],
    'qb_questions' => [
        'qb_all_questions','qb_add_question','qb_bulk_upload',
        'qb_draft_questions','qb_review_queue','qb_approved_questions',
        'qb_published_questions','qb_archived_questions','qb_question_media',
    ],
    'qb_exams' => [
        'qb_create_exam','qb_exam_templates','qb_random_exam','qb_cbt_exams','qb_print_exams',
    ],
    'qb_curriculum' => ['qb_competencies','qb_learning_outcomes','qb_curriculum_references'],
    'qb_analytics'  => [
        'qb_question_usage','qb_difficulty_analytics','qb_student_performance',
        'qb_most_failed','qb_bloom_distribution',
    ],
    'qb_import_export' => ['qb_excel_import','qb_csv_import','qb_export_questions','qb_import_logs'],
    'qb_ai_tools'  => ['qb_ai_generate','qb_ai_explanations','qb_ai_difficulty','qb_duplicate_detector'],
    'qb_settings'  => ['qb_id_rules','qb_approval_workflow','qb_media_storage','qb_general_settings'],
    'student_exams'=> ['student_exams','student_take_exam','student_exam_results'],
]);

function canAccessModule(array $perms, string $module): bool {
    return in_array('*', $perms) || in_array($module, $perms);
}

function viewModule(string $view): ?string {
    foreach (MODULE_VIEWS as $mod => $views) {
        if (in_array($view, $views)) return $mod;
    }
    return null;
}
