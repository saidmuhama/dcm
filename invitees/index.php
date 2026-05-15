<?php
 error_reporting(E_ALL);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Digital Class - Invites Signup</title>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    <main class="flex-shrink-0 pt-0">
        <form action="" id="createUser">
        <div class="container-fluid">
            <div class="auth-wrapper">
                <div class="row justify-content-center minheight-dynamic" style="--mih-dynamic: calc(100vh - 120px)">
                    <div class="col-12 col-md-8 col-xl-6">
                        <div class="h-100 py-4 px-md-3">
                            <div class="row h-100 align-items-center justify-content-center mt-md-3">
                                
                                <div class="col-12 col-sm-8 col-md-11 col-xl-11 col-xxl-10 login-box">
                                    <div class="text-center mb-4">
                                        <h1 class="mb-2">Let's get started&#128077;</h1>
                                        <p class="text-secondary">Provide your few details</p>
                                    </div>
                                    <div class="row">
                                        <div class="col">
                                            <div class="form-floating mb-3">
                                                <input class="form-control" id="invitation_code" placeholder="Enter invitation code"  autofocus=""> 
                                                <label for="invitation_code">Invitation Code</label></div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col">
                                            <div class="form-floating mb-3">
                                                <input class="form-control" id="namef" placeholder="Enter first name"  autofocus=""> 
                                                <label for="namef">First Name</label></div>
                                        </div>
                                        <div class="col">
                                            <div class="form-floating mb-3"><input class="form-control" id="namel" placeholder="Enter last name" > 
                                            <label for="namel">Last Name</label></div>
                                        </div>
                                    </div>

                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control"  id="emailadd" placeholder="Enter email address">
                                        <label for="emailadd">Email Address</label>
                                    </div>
                                    <div class="input-group mb-3">
                                        <div class="form-floating"><input class="form-control" id="phonen"placeholder="Enter your phone number"> 
                                        <label for="phonen">Phone Number</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col">
                                            <div class="form-floating mb-3">
                                                <select class="form-select" id="user_role"
                                                    aria-label="User Role">
                                                    <option value="1" selected>Student</option>
                                                </select> 
                                                <label for="namef">User Type</label>
                                            </div>
                                    </div>
                                        
                                    <div class="position-relative">
                                        <div class="form-floating mb-3"><input type="password" class="form-control"
                                                id="checkstrength" placeholder="Enter your password"> <label
                                                for="checkstrength">Password</label></div>
                                                
                                        <button type="button" id="togglePassword" class="btn btn-square btn-link text-theme-1 position-absolute end-0 top-0 mt-2 me-2"><i id="eyeIcon" class="bi bi-eye"></i></button>
                                    </div>
                                    <div class="feedback mb-3">
                                        <div class="row">
                                            <div class="col">
                                                <div class="check-strength" id="checksterngthdisplay">
                                                    <div></div>
                                                    <div></div>
                                                    <div></div>
                                                    <div></div>
                                                    <div></div>
                                                    <div></div>
                                                </div>
                                            </div>
                                            <div class="col-auto"><span class="small" id="textpassword"></span> <i
                                                    class="bi bi-info-circle text-theme ms-1" data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="Password should contain atleast 1 capital, 1 alphanumeric & min. 8 characters"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="position-relative">
                                        <div class="form-floating mb-3">
                                            <input type="password" class="form-control"
                                                id="passwd" placeholder="Confirm your password"> <label
                                                for="passwd">Confirm Password</label>
                                        </div>
                                        
                                        <button type="button" id="toggleConfirmPassword" class="btn btn-square btn-link position-absolute end-0 top-0 mt-2 me-2">
                                            <i class="bi bi-eye" id="eyeIcon2"></i>
                                        </button>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-lg btn-theme w-100 mb-4" id="signupBtn">
                                        <span id="btnText">Sign up</span>
                                        <span id="btnLoader" style="display:none;">
                                            <i class="spinner-border spinner-border-sm"></i> Processing...
                                        </span>
                                    </button>
                                    
                                    <div class="text-center mb-3">Already have account? <a href="../" class="">Login</a> here.</div>
                                    <div class="row align-items-center mb-4">
                                        <div class="col">
                                            <hr class="">
                                        </div>
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
    
    <script>
    
             $('#toggleConfirmPassword').on('click', function(){
    
                let field = $('#passwd');
                let icon = $('#eyeIcon2');
            
                if(field.attr('type') === 'password'){
                    field.attr('type', 'text');
                    icon.removeClass('bi-eye').addClass('bi-eye-slash');
                } else {
                    field.attr('type', 'password');
                    icon.removeClass('bi-eye-slash').addClass('bi-eye');
                }
            
            });
            
            $('#togglePassword').on('click', function(){
    
                let field = $('#checkstrength');
                let icon = $('#eyeIcon');
            
                if(field.attr('type') === 'password'){
                    field.attr('type', 'text');
                    icon.removeClass('bi-eye').addClass('bi-eye-slash');
                } else {
                    field.attr('type', 'password');
                    icon.removeClass('bi-eye-slash').addClass('bi-eye');
                }
            
            });
            

            $('#createUser').on('submit', function(e){
                e.preventDefault();
                // Show loader
                $('#btnText').hide();
                $('#btnLoader').show();
                $('#signupBtn').prop('disabled', true);
            
                let data = {
                    invitation_code: $('#invitation_code').val(),
                    first_name: $('#namef').val(),
                    last_name: $('#namel').val(),
                    email: $('#emailadd').val(),
                    phone: $('#phonen').val(),
                    user_role: $('#user_role').val(),
                    password: $('#checkstrength').val(),
                    confirm_password: $('#passwd').val()
                };
            
                $.ajax({
                    url: '../data_files/ajax/ajax_create_user_invite.php',
                    method: 'POST',
                    data: data,
                    success: function(response){
            
                        // Reset button
                        $('#btnText').show();
                        $('#btnLoader').hide();
                        $('#signupBtn').prop('disabled', false);
            
                        if(response.trim() === 'success'){
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'User created successfully',
                                confirmButtonColor: '#3085d6'
                            }).then(() => {
                                window.location.href = "../";
                            });
            
                        } else {
            
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: response,
                                confirmButtonColor: '#d33'
                            });
            
                        }
                    },
                    error: function(xhr, status, error){

                        $('#btnText').show();
                        $('#btnLoader').hide();
                        $('#signupBtn').prop('disabled', false);
                    
                        console.log(xhr.responseText); // 🔍 check in browser console
                    
                        Swal.fire({
                            icon: 'error',
                            title: 'Request Failed',
                            text: xhr.status + ' - ' + error,
                        });
                    }
                });
            });

            let inviteTimer;

            $('#invitation_code').on('input', function () {

                clearTimeout(inviteTimer);

                let code = $(this).val().trim();

                // reset UI
                $('#signupBtn').prop('disabled', true);

                if (code.length < 4) return;

                inviteTimer = setTimeout(function () {

                    $.ajax({
                        url: '../data_files/ajax/ajax_validate_invite.php',
                        method: 'POST',
                        dataType: 'json',
                        data: { invitation_code: code },

                        success: function (res) {

                            if (res.status === "success") {

                                // AUTO FILL NAMES
                                $('#namef').val(res.data.first_name);
                                $('#namel').val(res.data.last_name);
                                $('#phonen').val(res.data.phone);

                                // DISABLE NAME EDIT (optional)
                                $('#namef, #namel, #phonen').prop('readonly', true);

                                // ENABLE SIGNUP
                                $('#signupBtn').prop('disabled', false);

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Valid Code',
                                    text: 'Invitation verified successfully',
                                    timer: 1200,
                                    showConfirmButton: false
                                });

                            } else {

                                // CLEAR FIELDS
                                $('#namef').val('').prop('readonly', false);
                                $('#namel').val('').prop('readonly', false);
                                $('#phonen').val('').prop('readonly', false);

                                $('#signupBtn').prop('disabled', true);

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Invalid Code',
                                    text: res.message
                                });
                            }
                        }
                    });

                }, 600); // debounce
            });
    </script>
    
</body>
</html>