<?php
if($user_role == 1)
{
    include('learning_student_home.php');
}
elseif($user_role == 3)
{
    include('teacher_profile.php');
}
else
{
    include('404.php');
}
?>