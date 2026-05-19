<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
  <title>DigitalClass — Reset Password</title>
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=SUSE:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <script defer src="../assets/js/appd9fa.js?6b22e6ee1626676f5950"></script>
  <link href="../assets/css/appd9fa.css?6b22e6ee1626676f5950" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --brand-from: #1a4fc4;
      --brand-to:   #6d28d9;
      --accent:     #0ea5e9;
      --text:       #0f172a;
      --muted:      #64748b;
      --border:     #e2e8f0;
      --font:       'Inter', sans-serif;
    }
    html, body { height: 100%; font-family: var(--font); }

    .pageloader { background: #1a4fc4; }
    .pageloader .text-secondary { color: rgba(255,255,255,.6) !important; }

    body.auth-page { background: #f1f5f9; display: flex; flex-direction: column; }
    .auth-wrap { flex: 1; display: flex; min-height: 100vh; }

    /* ── LEFT PANEL ─────────────────────────────────────── */
    .auth-brand {
      flex: 0 0 46%;
      background: linear-gradient(145deg, var(--brand-from) 0%, var(--brand-to) 100%);
      position: relative; overflow: hidden;
      display: flex; flex-direction: column; justify-content: space-between;
      padding: 3rem 3.5rem; color: #fff;
    }
    .auth-brand::before {
      content: '';
      position: absolute; inset: 0;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .auth-orb {
      position: absolute; border-radius: 50%;
      background: rgba(255,255,255,.07);
      animation: float 8s ease-in-out infinite;
    }
    .auth-orb-1 { width: 340px; height: 340px; top: -100px; right: -100px; animation-delay: 0s; }
    .auth-orb-2 { width: 220px; height: 220px; bottom: 60px; left: -80px; animation-delay: 3s; }
    .auth-orb-3 { width: 120px; height: 120px; bottom: 200px; right: 60px; animation-delay: 6s; }
    @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-18px)} }

    .auth-brand-logo { display: flex; align-items: center; gap: .75rem; position: relative; }
    .auth-brand-logo img { height: 38px; filter: brightness(0) invert(1); }
    .auth-brand-name { font-family: 'SUSE', sans-serif; font-size: 1.4rem; font-weight: 700; letter-spacing: -.5px; }

    .auth-brand-body { position: relative; }
    .auth-brand-body h1 {
      font-family: 'SUSE', sans-serif;
      font-size: 2.6rem; font-weight: 800;
      line-height: 1.15; letter-spacing: -.5px;
      margin-bottom: 1rem;
    }
    .auth-brand-body h1 span { color: #93c5fd; }
    .auth-brand-body p { opacity: .75; font-size: .95rem; line-height: 1.65; max-width: 340px; }

    .auth-features { list-style: none; position: relative; margin-top: 2.2rem; }
    .auth-features li {
      display: flex; align-items: center; gap: .8rem;
      padding: .65rem 0; border-bottom: 1px solid rgba(255,255,255,.1);
      font-size: .875rem; opacity: .85;
    }
    .auth-features li:last-child { border-bottom: none; }
    .auth-features li .feat-icon {
      width: 36px; height: 36px; border-radius: 10px;
      background: rgba(255,255,255,.15);
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
      font-size: 1rem;
    }
    .auth-brand-footer { position: relative; font-size: .78rem; opacity: .5; }

    /* ── RIGHT PANEL ─────────────────────────────────────── */
    .auth-form {
      flex: 1; display: flex; align-items: center; justify-content: center;
      padding: 2.5rem 1.5rem; background: #f1f5f9;
    }
    .auth-card {
      width: 100%; max-width: 420px;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,.07), 0 20px 60px -10px rgba(0,0,0,.12);
      padding: 2.5rem;
      animation: slideUp .4s cubic-bezier(.16,1,.3,1) both;
    }
    @keyframes slideUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }

    /* Icon badge */
    .auth-icon-badge {
      width: 64px; height: 64px; border-radius: 18px; margin: 0 auto 1.5rem;
      background: linear-gradient(135deg, var(--brand-from), var(--brand-to));
      display: flex; align-items: center; justify-content: center;
      font-size: 1.75rem; color: #fff;
      box-shadow: 0 8px 24px rgba(26,79,196,.35);
    }

    .auth-card-header { margin-bottom: 2rem; text-align: center; }
    .auth-card-header h2 { font-family:'SUSE',sans-serif; font-size:1.65rem; font-weight:800; color:var(--text); margin-bottom:.4rem; }
    .auth-card-header p { font-size:.875rem; color:var(--muted); line-height:1.55; }

    .auth-field { position: relative; margin-bottom: 1rem; }
    .auth-field label {
      display: block; font-size: .78rem; font-weight: 600;
      color: var(--text); margin-bottom: .45rem; letter-spacing: .02em;
    }
    .field-wrap { position: relative; }
    .field-icon {
      position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
      color: var(--muted); font-size: 1rem; pointer-events: none;
    }
    .auth-input {
      width: 100%; height: 48px;
      padding: 0 42px 0 42px;
      border: 1.5px solid var(--border); border-radius: 11px;
      font-family: var(--font); font-size: .9rem; color: var(--text);
      background: #fafafa;
      transition: border-color .2s, box-shadow .2s, background .2s;
      outline: none;
    }
    .auth-input:focus {
      border-color: var(--brand-from); background: #fff;
      box-shadow: 0 0 0 4px rgba(26,79,196,.1);
    }
    .auth-input::placeholder { color: #adb5bd; }

    .auth-btn {
      width: 100%; height: 50px;
      background: linear-gradient(135deg, var(--brand-from), var(--brand-to));
      color: #fff; font-weight: 700; font-size: .95rem; letter-spacing: .02em;
      border: none; border-radius: 12px; cursor: pointer;
      display: flex; align-items: center; justify-content: center; gap: .5rem;
      transition: opacity .2s, transform .2s, box-shadow .2s;
      box-shadow: 0 4px 16px rgba(26,79,196,.35);
      font-family: var(--font); margin-bottom: 1.25rem;
    }
    .auth-btn:hover { opacity: .92; transform: translateY(-1px); box-shadow: 0 8px 24px rgba(26,79,196,.4); }
    .auth-btn:active { transform: translateY(0); }
    .auth-btn:disabled { opacity: .65; transform: none; cursor: not-allowed; }

    .auth-link-btn {
      display: flex; align-items: center; justify-content: center; gap:.35rem;
      width: 100%; height: 50px;
      border: 1.5px solid var(--border); border-radius: 12px; background: #fff;
      color: var(--text); font-weight: 600; font-size: .9rem;
      text-decoration: none; cursor: pointer;
      transition: border-color .2s, background .2s;
      font-family: var(--font);
    }
    .auth-link-btn:hover { border-color: var(--brand-from); background: #f8faff; color: var(--brand-from); }

    /* Info notice */
    .auth-notice {
      display: flex; align-items: flex-start; gap: .65rem;
      background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px;
      padding: .85rem 1rem; margin-bottom: 1.25rem;
    }
    .auth-notice i { color: var(--brand-from); font-size: 1rem; margin-top: .1rem; flex-shrink: 0; }
    .auth-notice p { font-size: .8rem; color: #1e40af; line-height: 1.5; margin: 0; }

    @media (max-width: 900px) { .auth-brand { display: none; } }
    @media (max-width: 480px) { .auth-card { padding: 1.75rem 1.25rem; } }
  </style>
</head>

<body class="auth-page main-bg main-bg-opac sharpcornerui theme-blue scrollup" data-theme="theme-blue">

  <!-- Page loader -->
  <div class="pageloader">
    <div class="container h-100">
      <div class="row justify-content-center align-items-center text-center h-100">
        <div class="col-12 mb-auto pt-4"></div>
        <div class="col-auto">
          <img src="../assets/img/logo.svg" alt="" class="height-60 mb-3" style="filter:brightness(0)invert(1)">
          <p class="h6 mb-0 text-white">DigitalClass</p>
          <p class="h3 mb-4 text-white">Learning</p>
          <div class="loader11 mb-2 mx-auto"></div>
        </div>
        <div class="col-12 mt-auto pb-4"><p class="text-secondary">Please wait...</p></div>
      </div>
    </div>
  </div>

  <div class="auth-wrap">

    <!-- ── LEFT BRAND PANEL ───────────────────────────── -->
    <div class="auth-brand">
      <div class="auth-orb auth-orb-1"></div>
      <div class="auth-orb auth-orb-2"></div>
      <div class="auth-orb auth-orb-3"></div>

      <div class="auth-brand-logo">
        <img src="../assets/img/logo-light.svg" alt="DigitalClass">
        <span class="auth-brand-name">DigitalClass</span>
      </div>

      <div class="auth-brand-body">
        <h1>Locked Out?<br><span>We've got you.</span></h1>
        <p>It happens to everyone. Just provide your registered email and we'll send you a secure link to reset your password in seconds.</p>

        <ul class="auth-features">
          <li>
            <div class="feat-icon"><i class="bi bi-envelope-check-fill"></i></div>
            <span>Reset link sent instantly to your inbox</span>
          </li>
          <li>
            <div class="feat-icon"><i class="bi bi-clock-fill"></i></div>
            <span>Link expires in 1 hour for your security</span>
          </li>
          <li>
            <div class="feat-icon"><i class="bi bi-shield-lock-fill"></i></div>
            <span>Your account stays fully protected</span>
          </li>
          <li>
            <div class="feat-icon"><i class="bi bi-arrow-repeat"></i></div>
            <span>Change your password anytime, anywhere</span>
          </li>
        </ul>
      </div>

      <div class="auth-brand-footer">
        &copy; <?= date('Y') ?> Digital Class Media &middot; All rights reserved
      </div>
    </div>

    <!-- ── RIGHT FORM PANEL ───────────────────────────── -->
    <div class="auth-form">
      <div class="auth-card">

        <div class="auth-icon-badge">
          <i class="bi bi-key-fill"></i>
        </div>

        <div class="auth-card-header">
          <h2>Forgot your password?</h2>
          <p>No worries. Enter your registered email address and we'll send you a link to reset it.</p>
        </div>

        <form id="resetPassword" novalidate>

          <div class="auth-notice">
            <i class="bi bi-info-circle-fill"></i>
            <p>The reset link will expire in <strong>1 hour</strong>. Check your spam folder if you don't see it.</p>
          </div>

          <div class="auth-field">
            <label for="emailadd">Email Address</label>
            <div class="field-wrap">
              <i class="bi bi-envelope field-icon"></i>
              <input type="email" id="emailadd" class="auth-input"
                     placeholder="myemail@gmail.com" autofocus>
            </div>
          </div>

          <button type="submit" class="auth-btn" id="resetBtn">
            <span id="resetText"><i class="bi bi-send me-1"></i>Send Reset Link</span>
            <span id="resetLoader" style="display:none">
              <i class="bi bi-arrow-repeat" style="animation:spin .8s linear infinite"></i>
              Sending&hellip;
            </span>
          </button>

          <a href="../" class="auth-link-btn">
            <i class="bi bi-arrow-left"></i> Back to Sign In
          </a>

        </form>
      </div>
    </div>

  </div><!-- .auth-wrap -->

  <style>
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <script>
    $('#resetPassword').on('submit', function (e) {
      e.preventDefault();

      $('#resetText').hide();
      $('#resetLoader').show();
      $('#resetBtn').prop('disabled', true);

      $.ajax({
        url: '../data_files/ajax/ajax_forgot_password.php',
        method: 'POST',
        data: { email: $('#emailadd').val() },
        success: function (response) {
          $('#resetText').show();
          $('#resetLoader').hide();
          $('#resetBtn').prop('disabled', false);

          if (response.trim() === 'success') {
            Swal.fire({
              icon: 'success',
              title: 'Email Sent!',
              text: 'Check your inbox for the password reset link.',
              confirmButtonColor: '#1a4fc4'
            });
          } else {
            Swal.fire({ icon: 'error', title: 'Error', text: response });
          }
        }
      });
    });
  </script>

</body>
</html>
