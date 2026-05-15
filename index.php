<!doctype html>
<html lang="en">

<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
   <meta http-equiv="x-ua-compatible" content="ie=edge">
   <title>Digital Class - Login</title>
   <link rel="icon" type="image/png" href="assets/img/favicon.png">
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
         --adminuiux-title-font-weight: 600;
      }

      /* ================= LOGIN UI UPGRADE ================= */

      body {
         background: linear-gradient(135deg, #eef4ff, #f8fbff);
      }

      /* Card enhancement */
      .login-box .card {
         border-radius: 16px !important;
         border: none;
         backdrop-filter: blur(10px);
         box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
         transition: all 0.3s ease;
      }

      .login-box .card:hover {
         transform: translateY(-3px);
         box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
      }

      /* Title styling */
      .login-box h2 {
         font-weight: 700;
         letter-spacing: 0.5px;
      }

      .login-box p {
         font-size: 14px;
      }

      /* Inputs */
      .form-control {
         border-radius: 10px !important;
         border: 1px solid #e3e8f0;
         transition: all 0.25s ease;
      }

      .form-control:focus {
         border-color: #015EC2;
         box-shadow: 0 0 0 3px rgba(1, 94, 194, 0.1);
      }

      /* Floating label smoother */
      .form-floating label {
         color: #6c757d;
      }

      /* Button */
      .btn-theme {
         border-radius: 12px !important;
         font-weight: 600;
         letter-spacing: 0.4px;
         transition: all 0.3s ease;
      }

      .btn-theme:hover {
         transform: translateY(-2px);
         box-shadow: 0 8px 20px rgba(1, 94, 194, 0.3);
      }

      /* Signup button */
      .btn-link {
         font-weight: 500;
      }

      /* Eye button */
      #togglePassword {
         opacity: 0.6;
         transition: 0.2s;
      }

      #togglePassword:hover {
         opacity: 1;
         transform: scale(1.1);
      }

      /* Remember me */
      .form-check-label {
         font-size: 14px;
      }

      /* Forgot password */
      .login-box a.small {
         color: #015EC2;
         text-decoration: none;
         transition: 0.2s;
      }

      .login-box a.small:hover {
         text-decoration: underline;
      }

      /* Footer */
      .adminuiux-footer {
         opacity: 0.7;
      }

      /* Loader animation improvement */
      .spinner-border {
         vertical-align: middle;
      }

      /* Subtle divider */
      hr {
         opacity: 0.1;
      }

      /* Mobile spacing */
      @media (max-width: 768px) {
         .login-box {
            padding: 10px;
         }
      }

      /* Optional glow effect */
      .btn-theme:focus {
         box-shadow: 0 0 0 4px rgba(1, 94, 194, 0.2);
      }
   </style>
   <script defer="defer" src="assets/js/appd9fa.js?6b22e6ee1626676f5950"></script>
   <link href="assets/css/appd9fa.css?6b22e6ee1626676f5950" rel="stylesheet">
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
            <div class="col-auto">
               <img src="assets/img/logo.svg" alt="" class="height-60 mb-3">
               <p class="h6 mb-0">DigitalClass</p>
               <p class="h3 mb-4">Learning</p>
               <div class="loader11 mb-2 mx-auto"></div>
            </div>
            <div class="col-12 mt-auto pb-4">
               <p class="text-secondary">Please Wait...</p>
            </div>
         </div>
      </div>
   </div>
   <header class="adminuiux-header">
      <nav class="navbar">
         <div class="container-fluid">
            <a class="navbar-brand" href="">
               <img data-bs-img="light" src="assets/img/logo-light.svg" alt=""> <img data-bs-img="dark"
                  src="assets/img/logo.svg" alt="">
               <div class="">
                  <span class="h4">Digital<span class="fw-bold">Class</span></span>
                  <p class="company-tagline">E-Learning Platform</p>
               </div>
            </a>
            <div class="ms-auto"></div>
            <div class="ms-auto"></div>
         </div>
      </nav>
   </header>

   <main class="flex-shrink-0 pt-0 z-index-1">
      <form action="" id="loginForm">
         <div class="container">
            <div class="auth-wrapper">
               <div class="row justify-content-center minheight-dynamic" style="--mih-dynamic: calc(100vh - 120px)">
                  <div class="col-12 col-md-8 col-xl-6">
                     <div class="h-100 py-4 px-md-3">
                        <div class="row h-100 align-items-center justify-content-center mt-md-3">
                           <div class="col-12 col-sm-8 col-md-11 col-xl-11 col-xxl-10 login-box">
                              <div class="card adminuiux-card shadow-sm mb-2" style="border-radius:10px;">
                                 <div class="card-body">
                                    <div class="text-center mb-4">
                                       <h2 class="mb-1">Login</h2>
                                       <p class="text-secondary">Enter your credential to login</p>
                                    </div>
                                    <div class="form-floating mb-3">
                                       <input type="text" class="form-control" id="emailaddress"
                                          placeholder="Enter Username" autofocus=""> <label
                                          for="emailadd">Username</label>
                                    </div>
                                    <div class="position-relative">
                                       <div class="form-floating mb-3">
                                          <input type="password" class="form-control" id="password"
                                             placeholder="Enter your password"> <label for="passwd">Password</label>
                                       </div>
                                       <button type="button" id="togglePassword"
                                          class="btn btn-square btn-link text-theme-1 position-absolute end-0 top-0 mt-2 me-2">
                                          <i class="bi bi-eye" id="eyeIcon"></i>
                                       </button>

                                    </div>
                                    <div class="row align-items-center mb-3">
                                       <div class="col">
                                          <div class="form-check"><input class="form-check-input" type="checkbox"
                                                name="rememberme" id="rememberme"> <label class="form-check-label"
                                                for="rememberme">Remember me</label></div>
                                       </div>
                                    </div>
                                    <div class="row align-items-center mb-4">
                                       <div class="col">
                                          <button type="submit" class="btn btn-lg btn-theme w-100" id="loginBtn">
                                             <span id="loginText">Login</span>
                                             <span id="loginLoader" style="display:none;">
                                                <i class="spinner-border spinner-border-sm"></i> Checking...
                                             </span>
                                          </button>
                                       </div>
                                       <div class="col"><a href="signup/" class="btn btn-lg btn-link w-100">Signup <i
                                                class="bi bi-chevron-right"></i></a></div>
                                    </div>
                                    <div class="row align-items-center mb-4">
                                       <div class="col">
                                          <hr class="">
                                       </div>
                                       <div class="col">
                                          <hr class="">
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="mt-3 d-flex justify-content-between mb-4">

                                 <a href="signup/forgot-password.php" class="small text-decoration-none">
                                    Forget Password?
                                 </a>

                                 <a href="invitees/" class="small text-decoration-none">
                                    I have Invitation Code
                                 </a>

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
      <div class="container-fluid text-center"><span class="small">Copyright @2026, <a
               href="https://digitalclassmedia.com/" target="_blank">Digital Class - Media</a></span></div>
   </footer>
   <?php include('data_files/pages/theme.php'); ?>
   <script src="assets/js/learning/learning-auth.js"></script>
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


   <script>
      $('#togglePassword').on('click', function () {

         let passwordField = $('#password');
         let icon = $('#eyeIcon');

         if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
         } else {
            passwordField.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
         }

      });

      $('#loginForm').on('submit', function (e) {
         e.preventDefault();

         // Show loader
         $('#loginText').hide();
         $('#loginLoader').show();
         $('#loginBtn').prop('disabled', true);

         let data = {
            email: $('#emailaddress').val(),
            password: $('#password').val()
         };

         $.ajax({
            url: 'data_files/ajax/ajax_login.php', // post and data validation
            method: 'POST',
            data: data,
            success: function (response) {

               // Reset button
               $('#loginText').show();
               $('#loginLoader').hide();
               $('#loginBtn').prop('disabled', false);

               if (response.trim() === 'success') {

                  Swal.fire({
                     icon: 'success',
                     title: 'Welcome!',
                     text: 'Login successful',
                     timer: 1500,
                     showConfirmButton: false
                  }).then(() => {
                     window.location.href = "data_files/?view=3002";
                  });

               } else {

                  Swal.fire({
                     icon: 'error',
                     title: 'Login Failed',
                     text: response
                  });

               }
            },
            error: function (xhr) {

               $('#loginText').show();
               $('#loginLoader').hide();
               $('#loginBtn').prop('disabled', false);

               Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: xhr.status + ' - Server not reachable'
               });
            }
         });
      });
   </script>

</body>

</html>