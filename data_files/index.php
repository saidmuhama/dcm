<?php
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('display_errors', 1);
ini_set('error_log', 'error_log.txt');

session_start();
include('config/db.php');
include('config/header-config.php');
include('config/dump.php');
include('config/modules.php');

// App::sendSMS('255765131788', 'Testing if message get Sent');
$username       = $_SESSION['usr_code'];
$fullname       = $_SESSION['name'];

$user_role      = $_SESSION['user_role'];
$usr_code       = $_SESSION['usr_code'];
$signup_success = App::signupStatus($usr_code);
$roleTitle      = App::getWhatFromWHere('role_title','tbl_user_roles', 'id',$user_role);
$userProfileImage = @App::getUserProfileImage($usr_code,$user_role);

// Load module permissions for this role
if ($user_role == 5) {
    $user_perms = ['*']; // super admin: unrestricted
} else {
    $pstmt = $db->prepare("SELECT module_key FROM tbl_module_permissions WHERE role_id = ? AND is_enabled = 1");
    $pstmt->bind_param("i", $user_role);
    $pstmt->execute();
    $user_perms = array_column($pstmt->get_result()->fetch_all(MYSQLI_ASSOC), 'module_key');
}      
if(!isset($_SESSION['usr_code'])){
    header('Location: ../');
    exit;
}

// ── AJAX fragment mode (lazy navigation) ──────────────────────
if (!empty($_GET['_dcm_ajax'])) {
    include('pages/controller.php');
    exit;
}

if ($signup_success !== 'Completed' && ($user_role=='1')): ?>
<script>
    const params = new URLSearchParams(window.location.search);
    if (params.get("view") !== "student-profile-completion-8872") {
        window.location.href = "../data_files/?view=student-profile-completion-8872";
    }
</script>
<?php endif; 

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Digital Class - Dashboard</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300..800&amp;family=SUSE:wght@100..800&amp;display=swap" rel="stylesheet">
    <style>
        :root 
        {
            --adminuiux-content-font: "Open Sans", sans-serif;
            --adminuiux-content-font-weight: 400;
            --adminuiux-title-font: "SUSE", sans-serif;
            --adminuiux-title-font-weight: 600
        }
    </style>
    <script defer="defer" src="../assets/js/appd9fa.js?6b22e6ee1626676f5950"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">
    <link href="../assets/css/appd9fa.css?6b22e6ee1626676f5950" rel="stylesheet">
    <link href="../assets/css/custom.css" rel="stylesheet">
    <link href="../assets/css/dcm-system.css" rel="stylesheet">
</head>

<body
    class="main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-standard theme-blue adminuiux-header-fill-theme adminuiux-sidebar-fill-white bg-gradient-1 scrollup"
    data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-bs-spy="scroll"
    data-bs-target="#list-example" data-bs-smooth-scroll="true" tabindex="0"
    data-sidebarlayout="adminuiux-sidebar-standard" data-headerlayout="adminuiux-header-standard"
    data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-fill-theme">
    <div class="pageloader">
        <div class="container h-100">
            <div class="row justify-content-center align-items-center text-center h-100">
                <div class="col-12 mb-auto pt-4"></div>
                <div class="col-auto"><img src="../assets/img/logo.svg" alt="" class="height-60 mb-3">
                    <p class="h6 mb-0">Digital Class</p>
                    <p class="h3 mb-4">Learning</p>
                    <div class="loader11 mb-2 mx-auto"></div>
                </div>
                <div class="col-12 mt-auto pb-4">
                    <p class="text-secondary">Please wait...</p>
                </div>
            </div>
        </div>
    </div>
    <header class="adminuiux-header">
        <nav class="navbar navbar-expand-lg fixed-top">
            <?php include('pages/nav_page.php'); ?>
        </nav>
        <div class="adminuiux-search-full">
            <div class="row gx-2 align-items-center">
                <?php include('pages/app_view.php'); ?>
            </div>
        </div>
    </header>
    <div class="adminuiux-wrap">
        <div class="adminuiux-sidebar shadow-sm">
            <div class="adminuiux-sidebar-inner">
                <?php include('pages/side_menu.php'); ?>
            </div>
        </div>
        <main class="adminuiux-content has-sidebar" onclick="contentClick()">
            <?php include('pages/controller.php'); ?>
        </main>
    </div>
    <div class="offcanvas offcanvas-end shadow border-0" tabindex="-1" id="theming" data-bs-scroll="true"
        data-bs-backdrop="false" aria-labelledby="theminglabel">
        <?php include('pages/personalize.php'); ?>
    </div>
    <footer class="adminuiux-footer has-adminuiux-sidebar mt-auto">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-md col-lg py-2"><span class="small">Copyright @<?php echo date('Y'); ?>, <a
                            href="https://digitalclassmedia.com/" target="_blank">Digital Class</a></span></div>
                <div class="col-12 col-md-auto col-lg-auto align-self-center">
                    <ul class="nav small">
                        <li class="nav-item"><a class="nav-link" href="../data_files/?view=help_instrauctions">Help</a></li>
                        <li class="nav-item">|</li>
                        <li class="nav-item"><a class="nav-link" href="../data_files/?view=terms_of_use">Terms of Use</a></li>
                        <li class="nav-item">|</li>
                        <li class="nav-item"><a class="nav-link" href="../data_files/?view=privacy_policy">Privacy Policy</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    <div class="position-fixed bottom-0 end-0 m-3 z-index-5"><button
            class="btn btn-square btn-theme shadow rounded-circle" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#theming" aria-controls="theming"><i class="bi bi-palette"></i></button><br><button
            class="btn btn-theme btn-square shadow mt-2 d-none rounded-circle" id="backtotop"><i
                class="bi bi-arrow-up"></i></button>
    </div>
    
    
    <?php include('pages/modal_lunch.php'); ?>
    <script src="../assets/js/learning/learning-dashboard.js"></script>
    <script src="../assets/js/learning/learning-student-add.js"></script>
    <script src="../assets/js/learning/learning-student-progress.js"></script>
    <script src="../assets/js/learning/learning-teacher-profile.js"></script>
    <script src="../assets/js/learning/dcm-nav.js"></script>
</body>

</html>