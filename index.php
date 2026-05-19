<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
  <title>DigitalClass — Sign In</title>
  <link rel="icon" type="image/png" href="assets/img/favicon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=SUSE:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <script defer src="assets/js/appd9fa.js?6b22e6ee1626676f5950"></script>
  <link href="assets/css/appd9fa.css?6b22e6ee1626676f5950" rel="stylesheet">

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --brand-from: #1a4fc4;
      --brand-to:   #6d28d9;
      --accent:     #0ea5e9;
      --text:       #0f172a;
      --muted:      #64748b;
      --border:     #e2e8f0;
      --radius:     14px;
      --font:       'Inter', sans-serif;
    }

    html, body { height: 100%; font-family: var(--font); }

    /* ── Page loader (kept for AdminUIUX JS) ─────────────── */
    .pageloader { background: #1a4fc4; }
    .pageloader .text-secondary { color: rgba(255,255,255,.6) !important; }

    /* ── Auth layout ─────────────────────────────────────── */
    body.auth-page { background: #f1f5f9; display: flex; flex-direction: column; }
    .auth-wrap { flex: 1; display: flex; min-height: 100vh; }

    /* LEFT PANEL */
    .auth-brand {
      flex: 0 0 46%;
      background: linear-gradient(145deg, var(--brand-from) 0%, var(--brand-to) 100%);
      position: relative; overflow: hidden;
      display: flex; flex-direction: column; justify-content: space-between;
      padding: 3rem 3.5rem;
      color: #fff;
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

    /* RIGHT PANEL */
    .auth-form {
      flex: 1; display: flex; align-items: center; justify-content: center;
      padding: 2.5rem 1.5rem;
      background: #f1f5f9;
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

    .auth-card-header { margin-bottom: 2rem; }
    .auth-card-header h2 { font-family:'SUSE',sans-serif; font-size:1.65rem; font-weight:800; color:var(--text); margin-bottom:.3rem; }
    .auth-card-header p { font-size:.875rem; color:var(--muted); }

    /* Inputs */
    .auth-field { position: relative; margin-bottom: 1rem; }
    .auth-field label {
      display: block; font-size: .78rem; font-weight: 600;
      color: var(--text); margin-bottom: .45rem; letter-spacing: .02em;
    }
    .auth-field .field-wrap { position: relative; }
    .auth-field .field-icon {
      position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
      color: var(--muted); font-size: 1rem; pointer-events: none;
    }
    .auth-input {
      width: 100%; height: 48px;
      padding: 0 42px 0 42px;
      border: 1.5px solid var(--border);
      border-radius: 11px;
      font-family: var(--font); font-size: .9rem; color: var(--text);
      background: #fafafa;
      transition: border-color .2s, box-shadow .2s, background .2s;
      outline: none;
    }
    .auth-input:focus {
      border-color: var(--brand-from);
      background: #fff;
      box-shadow: 0 0 0 4px rgba(26,79,196,.1);
    }
    .auth-input::placeholder { color: #adb5bd; }
    .eye-btn {
      position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
      background: none; border: none; cursor: pointer;
      color: var(--muted); font-size: 1rem; padding: 4px;
      transition: color .2s;
    }
    .eye-btn:hover { color: var(--brand-from); }

    /* Submit btn */
    .auth-btn {
      width: 100%; height: 50px;
      background: linear-gradient(135deg, var(--brand-from), var(--brand-to));
      color: #fff; font-weight: 700; font-size: .95rem; letter-spacing: .02em;
      border: none; border-radius: 12px; cursor: pointer;
      display: flex; align-items: center; justify-content: center; gap: .5rem;
      transition: opacity .2s, transform .2s, box-shadow .2s;
      box-shadow: 0 4px 16px rgba(26,79,196,.35);
      font-family: var(--font);
    }
    .auth-btn:hover { opacity: .92; transform: translateY(-1px); box-shadow: 0 8px 24px rgba(26,79,196,.4); }
    .auth-btn:active { transform: translateY(0); }
    .auth-btn:disabled { opacity: .65; transform: none; cursor: not-allowed; }

    /* Secondary link-btn */
    .auth-link-btn {
      display: flex; align-items: center; justify-content: center; gap:.3rem;
      width: 100%; height: 50px;
      border: 1.5px solid var(--border);
      border-radius: 12px; background: #fff;
      color: var(--text); font-weight: 600; font-size: .9rem;
      text-decoration: none; cursor: pointer;
      transition: border-color .2s, background .2s;
      font-family: var(--font);
    }
    .auth-link-btn:hover { border-color: var(--brand-from); background: #f8faff; color: var(--brand-from); }

    .auth-divider { display: flex; align-items: center; gap: 1rem; margin: 1.25rem 0; }
    .auth-divider::before, .auth-divider::after { content:''; flex:1; height:1px; background:var(--border); }
    .auth-divider span { font-size:.75rem; color:var(--muted); white-space:nowrap; }

    .auth-footer-links { display:flex; justify-content:space-between; margin-top: 1.5rem; }
    .auth-footer-links a { font-size:.825rem; color:var(--muted); text-decoration:none; transition:color .2s; }
    .auth-footer-links a:hover { color:var(--brand-from); }

    .auth-check { display:flex; align-items:center; gap:.5rem; margin-bottom:1.25rem; }
    .auth-check input { width:16px; height:16px; accent-color:var(--brand-from); cursor:pointer; }
    .auth-check label { font-size:.83rem; color:var(--muted); cursor:pointer; user-select:none; }

    /* Mobile: hide left panel */
    @media (max-width: 900px) { .auth-brand { display: none; } }
    @media (max-width: 480px) { .auth-card { padding: 1.75rem 1.25rem; } }
  </style>
</head>

<body class="auth-page main-bg main-bg-opac sharpcornerui theme-blue scrollup"
      data-theme="theme-blue">

  <!-- Page loader -->
  <div class="pageloader">
    <div class="container h-100">
      <div class="row justify-content-center align-items-center text-center h-100">
        <div class="col-12 mb-auto pt-4"></div>
        <div class="col-auto">
          <img src="assets/img/logo.svg" alt="" class="height-60 mb-3" style="filter:brightness(0)invert(1)">
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
        <img src="assets/img/logo-light.svg" alt="DigitalClass">
        <span class="auth-brand-name">DigitalClass</span>
      </div>

      <div class="auth-brand-body">
        <h1>Learn Without<br><span>Limits.</span></h1>
        <p>Access thousands of courses, interactive question banks, and real-time progress tracking — all in one platform built for modern learners.</p>

        <ul class="auth-features">
          <li>
            <div class="feat-icon"><i class="bi bi-play-circle-fill"></i></div>
            <span>10,000+ video & audio courses</span>
          </li>
          <li>
            <div class="feat-icon"><i class="bi bi-patch-question-fill"></i></div>
            <span>Smart Question Bank with AI tools</span>
          </li>
          <li>
            <div class="feat-icon"><i class="bi bi-journal-bookmark-fill"></i></div>
            <span>Study notes & Q&amp;A for every lesson</span>
          </li>
          <li>
            <div class="feat-icon"><i class="bi bi-bar-chart-fill"></i></div>
            <span>Real-time analytics &amp; progress reports</span>
          </li>
        </ul>
      </div>

      <div class="auth-brand-footer">
        &copy; <?= date('Y') ?> Digital Class Media · All rights reserved
      </div>
    </div>

    <!-- ── RIGHT FORM PANEL ───────────────────────────── -->
    <div class="auth-form">
      <div class="auth-card">

        <div class="auth-card-header">
          <h2>Welcome back</h2>
          <p>Sign in to your account to continue learning</p>
        </div>

        <form id="loginForm" novalidate>

          <div class="auth-field">
            <label for="emailaddress">Email Address</label>
            <div class="field-wrap">
              <i class="bi bi-envelope field-icon"></i>
              <input type="email" id="emailaddress" class="auth-input"
                     placeholder="myemail@gmail.com" autofocus>
            </div>
          </div>

          <div class="auth-field">
            <label for="password">Password</label>
            <div class="field-wrap">
              <i class="bi bi-lock field-icon"></i>
              <input type="password" id="password" class="auth-input"
                     placeholder="Enter your password">
              <button type="button" class="eye-btn" id="togglePassword" tabindex="-1">
                <i class="bi bi-eye" id="eyeIcon"></i>
              </button>
            </div>
          </div>

          <div class="auth-check">
            <input type="checkbox" name="rememberme" id="rememberme">
            <label for="rememberme">Remember me for 30 days</label>
          </div>

          <button type="submit" class="auth-btn" id="loginBtn">
            <span id="loginText">Sign In</span>
            <span id="loginLoader" style="display:none">
              <i class="bi bi-arrow-repeat" style="animation:spin .8s linear infinite"></i>
              Signing in…
            </span>
          </button>

          <div class="auth-divider"><span>or</span></div>

          <a href="signup/" class="auth-link-btn">
            <i class="bi bi-person-plus"></i> Create new account
          </a>

        </form>

        <div class="auth-footer-links" id="loginFooter">
          <a href="signup/forgot-password.php"><i class="bi bi-shield-lock me-1"></i>Forgot password?</a>
          <a href="invitees/"><i class="bi bi-ticket-perforated me-1"></i>Invitation code</a>
        </div>

        <!-- ── FORCED SETUP STEP (role requires 2FA, user not enrolled) ── -->
        <div id="tfaSetupStep" style="display:none">

          <!-- Notice -->
          <div id="setupNotice">
            <div style="text-align:center;margin-bottom:1.5rem">
              <div style="width:60px;height:60px;border-radius:16px;background:linear-gradient(135deg,#f59e0b,#d97706);display:flex;align-items:center;justify-content:center;margin:0 auto .85rem;box-shadow:0 8px 24px rgba(245,158,11,.3)">
                <i class="bi bi-shield-exclamation" style="font-size:1.6rem;color:#fff"></i>
              </div>
              <h2 style="font-family:'SUSE',sans-serif;font-size:1.35rem;font-weight:800;color:#0f172a;margin-bottom:.4rem">2FA Required</h2>
              <p style="font-size:.875rem;color:#64748b;margin-bottom:1.25rem">Your account role requires two-factor authentication. Set it up now to continue.</p>
              <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:.75rem 1rem;font-size:.8rem;color:#92400e;text-align:left;margin-bottom:1.25rem">
                <i class="bi bi-info-circle-fill me-1"></i>
                Install <strong>Google Authenticator</strong> or <strong>Authy</strong> on your phone before clicking Set Up.
              </div>
              <button type="button" id="setupStartBtn" class="auth-btn">
                <i class="bi bi-shield-plus me-1"></i>Set Up Authenticator
              </button>
            </div>
          </div>

          <!-- QR Scan -->
          <div id="setupQrPanel" style="display:none">
            <div style="text-align:center;margin-bottom:1rem">
              <h2 style="font-family:'SUSE',sans-serif;font-size:1.2rem;font-weight:800;color:#0f172a;margin-bottom:.25rem">Scan QR Code</h2>
              <p style="font-size:.825rem;color:#64748b">Open your authenticator app and scan the code below</p>
            </div>
            <div id="setupQrLoader" style="text-align:center;padding:1.5rem;color:#94a3b8">
              <i class="bi bi-arrow-repeat" style="font-size:1.4rem;animation:spin .8s linear infinite"></i>
              <p style="margin-top:.4rem;font-size:.79rem">Generating…</p>
            </div>
            <div id="setupQrWrap" style="display:none">
              <div style="display:flex;justify-content:center;margin-bottom:1rem">
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:.9rem;display:inline-block">
                  <div id="setupQrCanvas"></div>
                </div>
              </div>
              <div style="margin-bottom:1rem">
                <div style="font-size:.68rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.35rem">Manual entry key</div>
                <div id="setupSecretDisplay" style="font-family:monospace;font-size:.85rem;font-weight:700;color:#1a4fc4;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:.45rem .75rem;letter-spacing:.08em;word-break:break-all;cursor:pointer;display:flex;align-items:center;gap:.5rem" title="Click to copy">
                  <i class="bi bi-key"></i><span id="setupSecretText"></span>
                  <i class="bi bi-clipboard ms-auto" style="font-size:.7rem;opacity:.5"></i>
                </div>
              </div>
              <button type="button" id="setupQrDoneBtn" class="auth-btn">
                <i class="bi bi-check-circle me-1"></i>I've scanned it — Next
              </button>
            </div>
          </div>

          <!-- Verify -->
          <div id="setupVerifyPanel" style="display:none">
            <div style="text-align:center;margin-bottom:1rem">
              <h2 style="font-family:'SUSE',sans-serif;font-size:1.2rem;font-weight:800;color:#0f172a;margin-bottom:.25rem">Verify Your Code</h2>
              <p style="font-size:.825rem;color:#64748b">Enter the 6-digit code from your authenticator app</p>
            </div>
            <div id="setupDigits" style="display:flex;gap:.45rem;justify-content:center;margin-bottom:1rem">
              <?php for($d=0;$d<6;$d++): ?>
              <input type="text" inputmode="numeric" maxlength="1" class="setup-digit"
                     style="width:46px;height:56px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:1.4rem;font-weight:700;text-align:center;color:#0f172a;background:#fafafa;outline:none;transition:border-color .2s,box-shadow .2s">
              <?php endfor; ?>
            </div>
            <div id="setupError" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:.65rem 1rem;font-size:.83rem;color:#dc2626;margin-bottom:1rem;text-align:center"></div>
            <button type="button" id="setupVerifyBtn" class="auth-btn">
              <span id="setupVerifyText"><i class="bi bi-shield-check me-1"></i>Activate & Sign In</span>
              <span id="setupVerifyLoader" style="display:none">
                <i class="bi bi-arrow-repeat" style="animation:spin .8s linear infinite"></i> Activating…
              </span>
            </button>
            <button type="button" id="setupBackBtn"
                    style="display:block;width:100%;margin-top:.6rem;padding:.65rem;background:none;border:none;color:#64748b;font-size:.83rem;cursor:pointer;border-radius:10px">
              <i class="bi bi-arrow-left me-1"></i>Back
            </button>
          </div>

        </div>

        <!-- ── 2FA STEP ──────────────────────────────────────── -->
        <div id="tfaStep" style="display:none">
          <div style="text-align:center;margin-bottom:1.5rem">
            <div style="width:60px;height:60px;border-radius:16px;background:linear-gradient(135deg,#1a4fc4,#6d28d9);display:flex;align-items:center;justify-content:center;margin:0 auto .85rem;box-shadow:0 8px 24px rgba(26,79,196,.35)">
              <i class="bi bi-shield-lock-fill" style="font-size:1.6rem;color:#fff"></i>
            </div>
            <h2 style="font-family:'SUSE',sans-serif;font-size:1.4rem;font-weight:800;color:#0f172a;margin-bottom:.3rem">Verification Required</h2>
            <p style="font-size:.875rem;color:#64748b">Enter the 6-digit code from your authenticator app</p>
          </div>

          <div id="tfaDigits" style="display:flex;gap:.45rem;justify-content:center;margin-bottom:1.5rem">
            <?php for($d=0;$d<6;$d++): ?>
            <input type="text" inputmode="numeric" maxlength="1"
                   class="tfa-login-digit"
                   style="width:46px;height:56px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:1.4rem;font-weight:700;text-align:center;color:#0f172a;background:#fafafa;outline:none;transition:border-color .2s,box-shadow .2s">
            <?php endfor; ?>
          </div>

          <div id="tfaError" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:.65rem 1rem;font-size:.83rem;color:#dc2626;margin-bottom:1rem;text-align:center"></div>

          <button type="button" id="tfaBtn" class="auth-btn">
            <span id="tfaText"><i class="bi bi-check-circle me-1"></i>Verify Code</span>
            <span id="tfaLoader" style="display:none">
              <i class="bi bi-arrow-repeat" style="animation:spin .8s linear infinite"></i>
              Verifying…
            </span>
          </button>

          <button type="button" id="tfaBackBtn"
                  style="display:block;width:100%;margin-top:.75rem;padding:.7rem;background:none;border:none;color:#64748b;font-size:.83rem;cursor:pointer;border-radius:10px;transition:background .2s">
            <i class="bi bi-arrow-left me-1"></i>Back to login
          </button>
        </div>

      </div>
    </div>

  </div><!-- .auth-wrap -->

  <style>
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script src="assets/js/learning/learning-auth.js"></script>

  <script>
    $('#togglePassword').on('click', function () {
      let f = $('#password'), icon = $('#eyeIcon');
      if (f.attr('type') === 'password') { f.attr('type','text'); icon.removeClass('bi-eye').addClass('bi-eye-slash'); }
      else { f.attr('type','password'); icon.removeClass('bi-eye-slash').addClass('bi-eye'); }
    });

    /* ── Login form ──────────────────────────────────────── */
    $('#loginForm').on('submit', function (e) {
      e.preventDefault();
      $('#loginText').hide(); $('#loginLoader').show(); $('#loginBtn').prop('disabled', true);

      $.ajax({
        url: 'data_files/ajax/ajax_login.php', method: 'POST',
        dataType: 'json',
        data: { email: $('#emailaddress').val(), password: $('#password').val() },
        success: function (res) {
          $('#loginText').show(); $('#loginLoader').hide(); $('#loginBtn').prop('disabled', false);
          if (res.status === 'success') {
            Swal.fire({ icon:'success', title:'Welcome back!', text:'Login successful', timer:1500, showConfirmButton:false })
              .then(() => window.location.href = 'data_files/?view=3002');
          } else if (res.status === '2fa_required') {
            $('#loginForm').hide();
            $('#loginFooter').hide();
            $('#tfaStep').show();
            setTimeout(function () { $('.tfa-login-digit').first().focus(); }, 50);
          } else if (res.status === '2fa_setup_required') {
            $('#loginForm').hide();
            $('#loginFooter').hide();
            $('#tfaSetupStep').show();
            $('#setupNotice').show();
            $('#setupQrPanel').hide();
            $('#setupVerifyPanel').hide();
          } else {
            Swal.fire({ icon:'error', title:'Login Failed', text: res.message || 'An error occurred.' });
          }
        },
        error: function (xhr) {
          $('#loginText').show(); $('#loginLoader').hide(); $('#loginBtn').prop('disabled', false);
          Swal.fire({ icon:'error', title:'Error', text: xhr.status + ' — Server unreachable' });
        }
      });
    });

    /* ── 2FA digit box behaviour ─────────────────────────── */
    $(document).on('input', '.tfa-login-digit', function () {
      this.value = this.value.replace(/\D/g, '').slice(-1);
      if (this.value) $(this).next('.tfa-login-digit').focus();
    });
    $(document).on('keydown', '.tfa-login-digit', function (e) {
      if (e.key === 'Backspace' && !this.value) $(this).prev('.tfa-login-digit').focus();
    });
    $(document).on('paste', '.tfa-login-digit', function (e) {
      e.preventDefault();
      var digits = (e.originalEvent.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
      $('.tfa-login-digit').each(function (i) { $(this).val(digits[i] || ''); });
      $('.tfa-login-digit').last().focus();
    });
    $(document).on('focus', '.tfa-login-digit', function () {
      $(this).css({ borderColor: '#1a4fc4', boxShadow: '0 0 0 4px rgba(26,79,196,.12)', background: '#fff' });
    });
    $(document).on('blur', '.tfa-login-digit', function () {
      $(this).css({ borderColor: '#e2e8f0', boxShadow: 'none', background: '#fafafa' });
    });

    /* ── 2FA verify ──────────────────────────────────────── */
    $('#tfaBtn').on('click', function () {
      var code = $('.tfa-login-digit').map(function () { return this.value; }).get().join('');
      if (code.length < 6) {
        $('#tfaError').text('Please enter all 6 digits.').show();
        return;
      }
      $('#tfaError').hide();
      $('#tfaText').hide(); $('#tfaLoader').show(); $('#tfaBtn').prop('disabled', true);

      $.ajax({
        url: 'data_files/ajax/ajax_2fa.php?action=verify_login',
        method: 'POST', dataType: 'json',
        data: { code: code },
        success: function (res) {
          $('#tfaText').show(); $('#tfaLoader').hide(); $('#tfaBtn').prop('disabled', false);
          if (res.status === 'success') {
            Swal.fire({ icon:'success', title:'Welcome back!', text:'Verified!', timer:1200, showConfirmButton:false })
              .then(() => window.location.href = 'data_files/?view=3002');
          } else {
            $('#tfaError').text(res.message || 'Invalid code. Try again.').show();
            $('.tfa-login-digit').val('');
            $('.tfa-login-digit').first().focus();
          }
        },
        error: function () {
          $('#tfaText').show(); $('#tfaLoader').hide(); $('#tfaBtn').prop('disabled', false);
          $('#tfaError').text('Server error. Please try again.').show();
        }
      });
    });

    /* ── 2FA back button ─────────────────────────────────── */
    $('#tfaBackBtn').on('click', function () {
      $('#tfaStep').hide();
      $('#tfaError').hide();
      $('.tfa-login-digit').val('');
      $('#loginForm').show();
      $('#loginFooter').show();
    });
    $('#tfaBackBtn').on('mouseenter', function () { $(this).css('background', '#f1f5f9'); });
    $('#tfaBackBtn').on('mouseleave', function () { $(this).css('background', 'none'); });

    /* ── Setup wizard (forced enrollment) ────────────────── */
    var _setupSecret = '';

    $('#setupStartBtn').on('click', function () {
      $('#setupNotice').hide();
      $('#setupQrPanel').show();
      $('#setupQrLoader').show();
      $('#setupQrWrap').hide();

      $.ajax({
        url: 'data_files/ajax/ajax_2fa.php?action=generate_login_secret',
        dataType: 'json',
        success: function (res) {
          if (res.status !== 'success') {
            Swal.fire({ icon:'error', title:'Error', text: res.message || 'Could not generate code.' });
            return;
          }
          _setupSecret = res.secret;
          $('#setupQrLoader').hide();
          $('#setupQrWrap').show();
          $('#setupQrCanvas').html('');
          new QRCode(document.getElementById('setupQrCanvas'), {
            text: res.uri, width: 160, height: 160,
            colorDark: '#0f172a', colorLight: '#f8fafc',
            correctLevel: QRCode.CorrectLevel.M
          });
          $('#setupSecretText').text(res.secret);
          $('#setupSecretDisplay').on('click', function () {
            navigator.clipboard.writeText(res.secret)
              .then(function () { Swal.fire({ toast:true, position:'top-end', icon:'success', title:'Copied!', showConfirmButton:false, timer:1800 }); });
          });
        },
        error: function () {
          Swal.fire({ icon:'error', title:'Error', text: 'Server unreachable.' });
        }
      });
    });

    $('#setupQrDoneBtn').on('click', function () {
      $('#setupQrPanel').hide();
      $('#setupVerifyPanel').show();
      setTimeout(function () { $('.setup-digit').first().focus(); }, 50);
    });

    /* Digit box behaviour for setup wizard */
    $(document).on('input', '.setup-digit', function () {
      this.value = this.value.replace(/\D/g, '').slice(-1);
      if (this.value) $(this).next('.setup-digit').focus();
    });
    $(document).on('keydown', '.setup-digit', function (e) {
      if (e.key === 'Backspace' && !this.value) $(this).prev('.setup-digit').focus();
    });
    $(document).on('paste', '.setup-digit', function (e) {
      e.preventDefault();
      var digits = (e.originalEvent.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
      $('.setup-digit').each(function (i) { $(this).val(digits[i] || ''); });
      $('.setup-digit').last().focus();
    });

    $('#setupVerifyBtn').on('click', function () {
      var code = $('.setup-digit').map(function () { return this.value; }).get().join('');
      if (code.length < 6) { $('#setupError').text('Please enter all 6 digits.').show(); return; }
      $('#setupError').hide();
      $('#setupVerifyText').hide(); $('#setupVerifyLoader').show(); $('#setupVerifyBtn').prop('disabled', true);

      $.ajax({
        url: 'data_files/ajax/ajax_2fa.php?action=setup_and_login',
        method: 'POST', dataType: 'json',
        data: { code: code },
        success: function (res) {
          $('#setupVerifyText').show(); $('#setupVerifyLoader').hide(); $('#setupVerifyBtn').prop('disabled', false);
          if (res.status === 'success') {
            Swal.fire({ icon:'success', title:'2FA Activated!', text:'Your account is now protected.', timer:1800, showConfirmButton:false })
              .then(function () { window.location.href = 'data_files/?view=3002'; });
          } else {
            $('#setupError').text(res.message || 'Invalid code.').show();
            $('.setup-digit').val('');
            $('.setup-digit').first().focus();
          }
        },
        error: function () {
          $('#setupVerifyText').show(); $('#setupVerifyLoader').hide(); $('#setupVerifyBtn').prop('disabled', false);
          $('#setupError').text('Server error. Please try again.').show();
        }
      });
    });

    $('#setupBackBtn').on('click', function () {
      $('#setupVerifyPanel').hide();
      $('.setup-digit').val('');
      $('#setupError').hide();
      $('#setupQrPanel').show();
    });
    $('#setupBackBtn').on('mouseenter', function () { $(this).css('background', '#f1f5f9'); });
    $('#setupBackBtn').on('mouseleave', function () { $(this).css('background', 'none'); });
  </script>

</body>
</html>
