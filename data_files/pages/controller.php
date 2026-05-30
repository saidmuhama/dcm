<?php
$view = $_GET['view'] ?? '';
$user_perms = $user_perms ?? [];   // defined by index.php; default silences static-analysis warnings

// ── Module-level access gate (skip for super admin and non-module views) ──
$_gated_module = viewModule($view);
if ($_gated_module && !canAccessModule($user_perms, $_gated_module)) {
    include('pages/403.php');
    return;
}

switch ($view) {
    case 3002:
        include('pages/dashboard.php');
        break;
    
    case 'personalization':
        include('pages/more_personalizations.php');
        break;    
        
    case 'signup-success':
        include('pages/signup_success.php');
        break; 
    
    case 'student-profile-completion-8872':
        include('pages/student_profile_completion.php');
        break;   
    
    case 'learning-chat-call':
        include('pages/learning_chat_call.php');
        break;
        
    case 'change-password-request':
        include('pages/change_password_request.php');
        break;
        
    case 'kill-session-user':
        session_destroy();
        echo "<script>window.location.href='../';</script>";
        break;

    case 'learning-student-home':
        include('pages/learning_student_home.php');
        break;

    case 'student_interests':
        include('pages/student_interests.php');
        break;

    case 'student_profile':
        include('pages/student_profile.php');
        break;
    
    case 'teacher_profile_completion':
        include('pages/teacher_profile_completion.php');
        break;

    case 'instructor_announcements':
        include('pages/instructor_announcements.php');
        break;

    case 'my_courses_online_contents_list_view':
        include('pages/my_courses_online_contents_list_view.php');
        break;
    
    case 'course_contents_management':
        include('pages/course_contents_management.php');
        break;
    
    case 'view_course_details':
        include('pages/view_course_details.php');
        break;

    case 'view_my_cart_to_pay':
        include('pages/view_my_cart_to_pay.php');
        break;
    
    case 'read_course_details_data':
        include('pages/read_course_details_data.php');
        break;
    
    case 'view_bunny_library_details':
        include('pages/view_bunny_library_details.php');
        break;

    case 'study_notes_manager':
        include('pages/study_notes_manager.php');
        break;

    case 'study_notes_viewer':
        include('pages/study_notes_viewer.php');
        break;

    case 'teacher_study_notes':
        include('pages/teacher_study_notes.php');
        break;

    case 'invitation_COntroller_Page':
        include('pages/invitation_COntroller_Page.php');
        break;

    // ── SUPER ADMIN ────────────────────────────────────────────
    case 'admin_dashboard':
        include('pages/admin_dashboard.php');
        break;

    case 'admin_courses':
        include('pages/admin_courses.php');
        break;

    case 'admin_course_detail':
        include('pages/admin_course_detail.php');
        break;

    case 'admin_course_pricing':
        include('pages/admin_course_pricing.php');
        break;

    case 'admin_bundles':
        include('pages/admin_bundles.php');
        break;

    case 'admin_users':
        include('pages/admin_users.php');
        break;

    case 'admin_roles':
        include('pages/admin_roles.php');
        break;

    case 'admin_permissions':
        include('pages/admin_permissions.php');
        break;

    case 'admin_2fa':
        include('pages/admin_2fa.php');
        break;

    case 'admin_payment_settings':
        include('pages/admin_payment_settings.php');
        break;

    case 'admin_course_reviews':
        include('pages/admin_course_reviews.php');
        break;

    case 'admin_organizations':
        include('pages/admin_organizations.php');
        break;

    case 'admin_org_detail':
        include('pages/admin_org_detail.php');
        break;

    case 'admin_categories':
        include('pages/admin_categories.php');
        break;

    case 'admin_combinations':
        include('pages/admin_combinations.php');
        break;

    case 'admin_reports':
        include('pages/admin_reports.php');
        break;

    case 'org_purchase_requests':
        include('pages/org_purchase_requests.php');
        break;

    case 'admin_purchase_requests':
        include('pages/admin_purchase_requests.php');
        break;

    // ── ORG ADMIN ─────────────────────────────────────────────
    case 'org_dashboard':
        echo "<script>window.location.replace('?view=3002');</script>";
        break;

    case 'org_members':
        include('pages/org_members.php');
        break;

    case 'org_departments':
        include('pages/org_departments.php');
        break;

    case 'org_reports':
        include('pages/org_reports.php');
        break;

    case 'org_courses':
        include('pages/org_courses.php');
        break;

    case 'org_license_management':
        include('pages/org_license_management.php');
        break;

    // ── QUESTION BANK ─────────────────────────────────────────
    // Taxonomy
    case 'qb_subjects':
    case 'qb_levels':
    case 'qb_chapters':
    case 'qb_subtopics':
    case 'qb_bloom_levels':
    case 'qb_difficulty_levels':
    case 'qb_sections':
        include('pages/qb_taxonomy.php');
        break;

    // Question Management
    case 'qb_all_questions':
    case 'qb_draft_questions':
    case 'qb_review_queue':
    case 'qb_approved_questions':
    case 'qb_published_questions':
    case 'qb_archived_questions':
        include('pages/qb_all_questions.php');
        break;

    case 'qb_add_question':
        include('pages/qb_add_question.php');
        break;

    case 'qb_exam_templates':
        include('pages/qb_exam_templates.php');
        break;

    case 'qb_create_exam':
        include('pages/qb_create_exam.php');
        break;

    case 'qb_random_exam':
        include('pages/qb_random_exam.php');
        break;

    case 'qb_cbt_exams':
        include('pages/qb_cbt_exams.php');
        break;

    case 'qb_print_exams':
        include('pages/qb_print_exams.php');
        break;

    case 'qb_bulk_upload':
        include('pages/qb_bulk_upload.php');
        break;

    // ── STUDENT EXAM MODULE ────────────────────────────────────
    // Restricted to Pre-School / Primary / Secondary / High School students
    case 'student_exams':
    case 'student_take_exam':
    case 'student_exam_results':
        /* Gate: only students (role=1) who are NOT at university/professional level */
        $__examOk = true;
        if ((int)($user_role ?? 0) === 1 && isset($db)) {
            $__eUsr  = $usr_code ?? '';
            $__eLvlQ = $db->query("
                SELECT mal.level_title
                FROM tbl_students s
                JOIN tbl_main_academic_levels mal ON mal.id = s.main_academic_level
                WHERE s.usr_code = '" . $db->real_escape_string($__eUsr) . "' LIMIT 1
            ");
            if ($__eLvlQ && $__eLvlRow = $__eLvlQ->fetch_assoc()) {
                $__eLvl = strtolower(trim($__eLvlRow['level_title']));
                if (str_contains($__eLvl, 'undergraduate') || str_contains($__eLvl, 'university') || str_contains($__eLvl, 'degree') || $__eLvl === 'courses') {
                    $__examOk = false;
                }
            }
            if ($__examOk) {
                $__eProfQ = $db->query("SELECT education_level FROM tbl_student_profiles WHERE student_id='" . $db->real_escape_string($__eUsr) . "' LIMIT 1");
                if ($__eProfQ && $__ePRow = $__eProfQ->fetch_assoc()) {
                    if (in_array($__ePRow['education_level'] ?? '', ['university','professional'])) $__examOk = false;
                }
            }
        }
        if (!$__examOk) { include('pages/403.php'); break; }
        if ($view === 'student_exams')        { include('pages/student_exams.php');        break; }
        if ($view === 'student_take_exam')    { include('pages/student_take_exam.php');    break; }
        if ($view === 'student_exam_results') { include('pages/student_exam_results.php'); break; }
        break;

    case 'qb_question_media':
        include('pages/qb_question_media.php');
        break;

    case 'qb_competencies':
    case 'qb_learning_outcomes':
    case 'qb_curriculum_references':
    case 'qb_question_usage':
    case 'qb_difficulty_analytics':
    case 'qb_student_performance':
    case 'qb_most_failed':
    case 'qb_bloom_distribution':
    case 'qb_excel_import':
    case 'qb_csv_import':
    case 'qb_export_questions':
    case 'qb_import_logs':
    case 'qb_ai_generate':
    case 'qb_ai_explanations':
    case 'qb_ai_difficulty':
    case 'qb_duplicate_detector':
    case 'qb_id_rules':
    case 'qb_approval_workflow':
    case 'qb_media_storage':
    case 'qb_general_settings':
        include('pages/qb_coming_soon.php');
        break;

    default:
        include('pages/404.php');
}
?>