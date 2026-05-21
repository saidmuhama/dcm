<?php
$user_role = isset($user_role) ? (int)$user_role : 0;
if ($user_role == 1) {
    include('learning_student_home.php');
} elseif ($user_role == 3) {
    include('teacher_profile.php');
} elseif ($user_role == 4) {
    include('org_dashboard.php');
} elseif ($user_role == 5) {
    include('admin_main_dashboard.php');
} else {
    include('404.php');
}
?>