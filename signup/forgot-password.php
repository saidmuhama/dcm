<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Digital Class - Forget Password</title>
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
        <form action="" id="resetPassword">
            <div class="container-fluid">
                <div class="auth-wrapper">
                    <div class="row justify-content-center minheight-dynamic" style="--mih-dynamic: calc(100vh - 120px)">
                        <div class="col-12 col-md-6 col-xl-4 d-flex flex-column px-0">
                            <div class="h-100 py-4 px-3">
                                <div class="row h-100 align-items-center justify-content-center mt-md-3">
                                    <div class="col-11 col-sm-8 col-md-11 col-xl-11 col-xxl-10 login-box">
                                        <div class="text-center mb-4">
                                            <h1 class="mb-0">Sorry!</h1>
                                            <h4 class="mb-3">You have to be here</h4>
                                            <p class="text-secondary">Provide your registered email address, we will send
                                                you an email with change password link with steps.</p>
                                        </div>
                                        <div class="form-floating mb-3"><input type="email" class="form-control"
                                                id="emailadd" placeholder="Enter email address"
                                                autofocus=""> <label for="emailadd">Email Address</label>
                                        </div>
                                        <button type="submit" class="btn btn-lg btn-theme w-100 mb-4" id="resetBtn">
                                            <span id="resetText">Reset Now</span>
                                            <span id="resetLoader" style="display:none;">
                                                <i class="spinner-border spinner-border-sm"></i> Sending...
                                            </span>
                                        </button>
                                        <div class="text-center mb-3">Already have password? <a href="../"
                                                class="">Login</a> here.</div><br><br>
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
    $('#resetPassword').on('submit', function(e){
        e.preventDefault();
    
        $('#resetText').hide();
        $('#resetLoader').show();
        $('#resetBtn').prop('disabled', true);
    
        $.ajax({
            url: '../data_files/ajax/ajax_forgot_password.php',
            method: 'POST',
            data: {
                email: $('#emailadd').val()
            },
            success: function(response){
    
                $('#resetText').show();
                $('#resetLoader').hide();
                $('#resetBtn').prop('disabled', false);
    
                if(response.trim() === 'success'){
                    Swal.fire({
                        icon: 'success',
                        title: 'Email Sent!',
                        text: 'Check your email for reset link',
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response
                    });
                }
            }
        });
    });
    </script>
</body>

</html>