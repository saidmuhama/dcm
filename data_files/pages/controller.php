<?php
$view = $_GET['view'];

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

    case 'student_profile':
        include('pages/student_profile.php');
        break;
    
    case 'teacher_profile_completion':
        include('pages/teacher_profile_completion.php');
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

    case 'invitation_COntroller_Page':
        include('pages/invitation_COntroller_Page.php');
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

    case 'qb_question_media':
    case 'qb_bulk_upload':
    case 'qb_create_exam':
    case 'qb_exam_templates':
    case 'qb_random_exam':
    case 'qb_cbt_exams':
    case 'qb_print_exams':
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