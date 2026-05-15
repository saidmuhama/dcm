<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Digital Class - Change Password</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300..800&amp;family=SUSE:wght@100..800&amp;display=swap"
        rel="stylesheet">
    <style>
        :root {
            --adminuiux-content-font: "Open Sans", sans-serif;
            --adminuiux-content-font-weight: 400;
            --adminuiux-title-font: "SUSE", sans-serif;
            --adminuiux-title-font-weight: 600
        }
    </style>
    <script defer="defer" src="../assets/js/appd9fa.js?6b22e6ee1626676f5950"></script>
    <link href="../assets/css/appd9fa.css?6b22e6ee1626676f5950" rel="stylesheet">
</head>

<body
    class="main-bg main-bg-opac sharpcornerui adminuiux-header-standard adminuiux-sidebar-iconic theme-blue adminuiux-header-transparent adminuiux-sidebar-fill-white bg-gradient-1 scrollup"
    data-theme="theme-blue" data-sidebarfill="adminuiux-sidebar-fill-white" data-bs-spy="scroll"
    data-bs-target="#list-example" data-bs-smooth-scroll="true" tabindex="0"
    data-sidebarlayout="adminuiux-sidebar-iconic" data-headerlayout="adminuiux-header-standard"
    data-bggradient="bg-gradient-1" data-headerfill="adminuiux-header-transparent">
    <div class="pageloader">
        <div class="container h-100">
            <div class="row justify-content-center align-items-center text-center h-100">
                <div class="col-12 mb-auto pt-4"></div>
                <div class="col-auto"><img src="../assets/img/logo.svg" alt="" class="height-60 mb-3">
                    <p class="h6 mb-0">DigitalClass</p>
                    <p class="h3 mb-4">E-Learning Platform</p>
                    <div class="loader11 mb-2 mx-auto"></div>
                </div>
                <div class="col-12 mt-auto pb-4">
                    <p class="text-secondary">Please wait...</p>
                </div>
            </div>
        </div>
    </div>
    <header class="adminuiux-header">
        <nav class="navbar">
            <div class="container-fluid"><a class="navbar-brand" href="learning-dashboard.html"><img data-bs-img="light"
                        src="../assets/img/logo-light.svg" alt=""> <img data-bs-img="dark" src="../assets/img/logo.svg"
                        alt="">
                    <div class=""><span class="h4">Digital<span class="fw-bold">Class</span></span>
                        <p class="company-tagline">E-Learning Platform</p>
                    </div>
                </a>
                <div class="ms-auto"></div>
                <div class="ms-auto"></div>
            </div>
        </nav>
    </header>
    <main class="flex-shrink-0 pt-0 h-100">
        
        <?php
        include('../data_files/config/db.php');
        
        $token = $_GET['token'];
        
        $query = mysqli_query($db, "SELECT * FROM tbl_all_users 
        WHERE reset_token='$token' AND token_expiry > NOW()");
        
        if(mysqli_num_rows($query) == 0){
            die("Invalid or expired token!");
        }
        ?>


        <form action="" id="resetPassword">

            <input type="hidden" id="token" value="<?php echo $token; ?>">
        
            <div class="container-fluid">
                <div class="auth-wrapper">
                    <div class="row justify-content-center minheight-dynamic" style="--mih-dynamic: calc(100vh - 120px)">
                        <div class="col-12 col-md-6 col-xl-4 d-flex flex-column px-0">
                            <div class="h-100 py-4 px-3">
        
                                <div class="row h-100 align-items-center justify-content-center mt-md-3">
                                    <div class="col-11 col-sm-8 col-md-11 col-xl-11 col-xxl-10 login-box">
        
                                        <div class="text-center mb-4">
                                            <h1 class="mb-0">Thats great step!</h1>
                                            <h4 class="mb-3">You are now one step away</h4>
                                            <p class="text-secondary">Provide your new password below.</p>
                                        </div>
        
                                        <!-- New Password -->
                                        <div class="position-relative">
                                            <div class="form-floating mb-3">
                                                <input type="password" class="form-control" id="new_password"
                                                    placeholder="New Password">
                                                <label>New Password</label>
                                            </div>
                                            <button type="button" id="toggleNew"
                                                class="btn btn-square btn-link position-absolute end-0 top-0 mt-2 me-2">
                                                <i class="bi bi-eye" id="eye1"></i>
                                            </button>
                                        </div>
        
                                        <!-- Confirm Password -->
                                        <div class="position-relative">
                                            <div class="form-floating mb-4">
                                                <input type="password" class="form-control" id="confirm_password"
                                                    placeholder="Confirm Password">
                                                <label>Confirm Password</label>
                                            </div>
                                            <button type="button" id="toggleConfirm"
                                                class="btn btn-square btn-link position-absolute end-0 top-0 mt-2 me-2">
                                                <i class="bi bi-eye" id="eye2"></i>
                                            </button>
                                        </div>
        
                                        <!-- Submit Button -->
                                        <button type="submit" class="btn btn-lg btn-theme w-100 mb-4" id="changeBtn">
                                            <span id="changeText">Change Now</span>
                                            <span id="changeLoader" style="display:none;">
                                                <i class="spinner-border spinner-border-sm"></i> Updating...
                                            </span>
                                        </button>
        
                                        <div class="text-center mb-3">
                                            Already have password?
                                            <a href="../" class="text-theme-1">Login</a>
                                        </div>
        
                                    </div>
                                </div>
        
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
        </form>
    </main>
   <footer class="adminuiux-footer mt-auto">
        <div class="container-fluid text-center"><span class="small">Copyright @<?php echo date('Y'); ?>, <a href="https://digitalclassmedia.com/"
                    target="_blank">Digital Class </a></span></div>
    </footer>
    <?php include('../data_files/pages/theme.php'); ?>
    <script src="../assets/js/learning/learning-auth.js"></script>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

// Toggle Password
$('#toggleNew').click(function(){
    let field = $('#new_password');
    let icon = $('#eye1');

    if(field.attr('type') === 'password'){
        field.attr('type','text');
        icon.removeClass('bi-eye').addClass('bi-eye-slash');
    } else {
        field.attr('type','password');
        icon.removeClass('bi-eye-slash').addClass('bi-eye');
    }
});

$('#toggleConfirm').click(function(){
    let field = $('#confirm_password');
    let icon = $('#eye2');

    if(field.attr('type') === 'password'){
        field.attr('type','text');
        icon.removeClass('bi-eye').addClass('bi-eye-slash');
    } else {
        field.attr('type','password');
        icon.removeClass('bi-eye-slash').addClass('bi-eye');
    }
});


// Submit Reset
$('#resetPassword').on('submit', function(e){
    e.preventDefault();

    let password = $('#new_password').val();
    let confirm = $('#confirm_password').val();

    if(password !== confirm){
        Swal.fire('Error','Passwords do not match','error');
        return;
    }

    $('#changeText').hide();
    $('#changeLoader').show();
    $('#changeBtn').prop('disabled', true);

    $.ajax({
        url: '../data_files/ajax/ajax_update_password.php',
        method: 'POST',
        data: {
            token: $('#token').val(),
            password: password
        },
        success: function(res){

            $('#changeText').show();
            $('#changeLoader').hide();
            $('#changeBtn').prop('disabled', false);

            if(res.trim() === 'success'){
                Swal.fire({
                    icon:'success',
                    title:'Success!',
                    text:'Password updated successfully',
                    timer:2000,
                    showConfirmButton:false
                }).then(()=>{
                    window.location.href = "../";
                });
            } else {
                Swal.fire('Error', res, 'error');
            }
        },
        error:function(){
            Swal.fire('Error','Server error','error');
        }
    });

});
</script>

</body>

</html>