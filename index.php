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
      --brand:      #1a4fc4;
      --brand-to:   #6d28d9;
      --accent:     #0ea5e9;
      --text:       #0f172a;
      --muted:      #64748b;
      --border:     #e2e8f0;
      --radius:     14px;
      --font:       'Inter', sans-serif;
    }

    html, body { height: 100%; font-family: var(--font); }

    .pageloader { background: #1a4fc4; }
    .pageloader .text-secondary { color: rgba(255,255,255,.6) !important; }

    /* ── Layout ── */
    body.auth-page { background: #f1f5f9; display: flex; flex-direction: column; }
    .auth-wrap { flex: 1; display: flex; min-height: 100vh; }

    /* ── Left brand panel ── */
    .auth-brand {
      flex: 0 0 46%;
      background: linear-gradient(145deg, #1a4fc4 0%, #6d28d9 100%);
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
    .auth-orb { position: absolute; border-radius: 50%; background: rgba(255,255,255,.07); animation: float 8s ease-in-out infinite; }
    .auth-orb-1 { width:340px;height:340px;top:-100px;right:-100px;animation-delay:0s; }
    .auth-orb-2 { width:220px;height:220px;bottom:60px;left:-80px;animation-delay:3s; }
    .auth-orb-3 { width:120px;height:120px;bottom:200px;right:60px;animation-delay:6s; }
    @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-18px)} }

    .auth-brand-logo { display:flex;align-items:center;gap:.75rem;position:relative; }
    .auth-brand-logo img { height:38px;filter:brightness(0)invert(1); }
    .auth-brand-name { font-family:'SUSE',sans-serif;font-size:1.4rem;font-weight:700;letter-spacing:-.5px; }

    .auth-brand-body { position: relative; }
    .auth-brand-body h1 {
      font-family: 'SUSE',sans-serif; font-size:2.6rem; font-weight:800;
      line-height:1.15; letter-spacing:-.5px; margin-bottom:1rem;
    }
    .auth-brand-body h1 span { color:#93c5fd; }
    .auth-brand-body p { opacity:.75;font-size:.95rem;line-height:1.65;max-width:340px; }

    .auth-features { list-style:none;position:relative;margin-top:2.2rem; }
    .auth-features li {
      display:flex;align-items:center;gap:.8rem;padding:.65rem 0;
      border-bottom:1px solid rgba(255,255,255,.1);font-size:.875rem;opacity:.85;
    }
    .auth-features li:last-child { border-bottom:none; }
    .auth-features li .feat-icon {
      width:36px;height:36px;border-radius:10px;
      background:rgba(255,255,255,.15);
      display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1rem;
    }
    .auth-brand-footer { position:relative;font-size:.78rem;opacity:.5; }

    /* ── Right form panel ── */
    .auth-form {
      flex:1;display:flex;align-items:center;justify-content:center;
      padding:2.5rem 1.5rem;background:#f1f5f9;
    }
    .auth-card {
      width:100%;max-width:440px;
      background:#fff;
      border-radius:24px;
      box-shadow:0 4px 6px -1px rgba(0,0,0,.07),0 24px 64px -12px rgba(0,0,0,.14);
      overflow:hidden;
      animation:slideUp .4s cubic-bezier(.16,1,.3,1) both;
    }
    /* Gradient accent bar at top of card */
    .auth-card::before {
      content:'';
      display:block;
      height:4px;
      background:linear-gradient(90deg,#1a4fc4,#6d28d9,#0ea5e9);
    }
    .auth-card-inner { padding:2.25rem 2.25rem 2rem; }
    @keyframes slideUp { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:translateY(0)} }

    /* Mobile brand logo (shows when left panel hidden) */
    .auth-mobile-logo {
      display:none;
      align-items:center;gap:.6rem;
      margin-bottom:1.5rem;
    }
    .auth-mobile-logo img { height:30px; }
    .auth-mobile-logo span { font-family:'SUSE',sans-serif;font-size:1.1rem;font-weight:800;color:#1a4fc4; }

    .auth-card-title { font-family:'SUSE',sans-serif;font-size:1.6rem;font-weight:800;color:var(--text);margin-bottom:.25rem; }
    .auth-card-sub { font-size:.875rem;color:var(--muted);margin-bottom:1.75rem; }

    /* ── Inputs ── */
    .auth-field { margin-bottom:1rem; }
    .auth-field-label {
      display:flex;align-items:center;justify-content:space-between;
      font-size:.78rem;font-weight:600;color:var(--text);margin-bottom:.45rem;letter-spacing:.02em;
    }
    .auth-field-label a { font-size:.78rem;color:var(--muted);text-decoration:none;font-weight:500;transition:color .2s; }
    .auth-field-label a:hover { color:var(--brand); }
    .auth-input-wrap { position:relative; }
    .auth-input-icon {
      position:absolute;left:14px;top:50%;transform:translateY(-50%);
      color:#94a3b8;font-size:.95rem;pointer-events:none;transition:color .2s;
    }
    .auth-input {
      width:100%;height:50px;
      padding:0 44px;
      border:1.5px solid var(--border);
      border-radius:12px;
      font-family:var(--font);font-size:.9rem;color:var(--text);
      background:#f8fafc;
      transition:border-color .2s,box-shadow .2s,background .2s;
      outline:none;
    }
    .auth-input:focus {
      border-color:var(--brand);
      background:#fff;
      box-shadow:0 0 0 4px rgba(26,79,196,.1);
    }
    .auth-input:focus + .auth-input-icon-right,
    .auth-input-wrap:focus-within .auth-input-icon { color:var(--brand); }
    .auth-input::placeholder { color:#c1cbd9; }
    .auth-input.is-error  { border-color:#ef4444;background:#fff; box-shadow:0 0 0 4px rgba(239,68,68,.09); }
    .auth-input.is-success{ border-color:#22c55e;background:#fff; }

    .auth-input-icon-right {
      position:absolute;right:12px;top:50%;transform:translateY(-50%);
      background:none;border:none;cursor:pointer;
      color:#94a3b8;font-size:.95rem;padding:4px;
      transition:color .2s;line-height:1;
    }
    .auth-input-icon-right:hover { color:var(--brand); }

    /* field-level error hint */
    .auth-field-hint { font-size:.73rem;margin-top:.3rem;display:none;align-items:center;gap:.3rem; }
    .auth-field-hint.error  { color:#ef4444;display:flex; }
    .auth-field-hint.ok     { color:#22c55e;display:flex; }

    /* ── Inline login alert ── */
    .auth-alert {
      display:none;
      align-items:flex-start;gap:.75rem;
      padding:.8rem 1rem;
      border-radius:12px;
      font-size:.83rem;
      margin-bottom:1rem;
      animation:fadeIn .25s ease;
    }
    @keyframes fadeIn { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }
    .auth-alert.error  { background:#fef2f2;border:1px solid #fecaca;color:#b91c1c; }
    .auth-alert.warning{ background:#fffbeb;border:1px solid #fde68a;color:#92400e; }
    .auth-alert.success{ background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d; }
    .auth-alert-icon   { font-size:1rem;flex-shrink:0;margin-top:.05rem; }
    .auth-alert-close  {
      margin-left:auto;background:none;border:none;cursor:pointer;
      color:inherit;opacity:.5;padding:0 2px;font-size:.95rem;flex-shrink:0;
      transition:opacity .15s;line-height:1;
    }
    .auth-alert-close:hover { opacity:1; }

    /* ── Remember me ── */
    .auth-remember {
      display:flex;align-items:center;justify-content:space-between;
      margin-bottom:1.25rem;
    }
    .auth-checkbox-wrap { display:flex;align-items:center;gap:.5rem;cursor:pointer;user-select:none; }
    .auth-checkbox-wrap input[type=checkbox] { display:none; }
    .auth-checkbox-box {
      width:18px;height:18px;border-radius:5px;
      border:1.5px solid var(--border);background:#f8fafc;
      display:flex;align-items:center;justify-content:center;
      transition:all .15s;flex-shrink:0;
    }
    .auth-checkbox-wrap input:checked ~ .auth-checkbox-box {
      background:var(--brand);border-color:var(--brand);
    }
    .auth-checkbox-wrap input:checked ~ .auth-checkbox-box::after {
      content:'';width:9px;height:5px;
      border-left:2px solid #fff;border-bottom:2px solid #fff;
      transform:rotate(-45deg) translate(1px,-1px);display:block;
    }
    .auth-checkbox-label { font-size:.82rem;color:var(--muted); }

    /* ── Submit button ── */
    .auth-btn {
      width:100%;height:52px;
      background:linear-gradient(135deg,var(--brand),var(--brand-to));
      color:#fff;font-weight:700;font-size:.95rem;letter-spacing:.02em;
      border:none;border-radius:12px;cursor:pointer;
      display:flex;align-items:center;justify-content:center;gap:.5rem;
      transition:opacity .2s,transform .15s,box-shadow .2s;
      box-shadow:0 4px 20px rgba(26,79,196,.35);
      font-family:var(--font);position:relative;overflow:hidden;
    }
    .auth-btn::after {
      content:'';position:absolute;inset:0;
      background:linear-gradient(135deg,rgba(255,255,255,.12),transparent);
      opacity:0;transition:opacity .2s;
    }
    .auth-btn:hover::after { opacity:1; }
    .auth-btn:hover { transform:translateY(-1px);box-shadow:0 8px 28px rgba(26,79,196,.42); }
    .auth-btn:active { transform:translateY(0);box-shadow:0 4px 12px rgba(26,79,196,.3); }
    .auth-btn:disabled { opacity:.6;transform:none;cursor:not-allowed;box-shadow:none; }

    /* spinner inside button */
    .auth-btn-spinner {
      width:18px;height:18px;border-radius:50%;
      border:2.5px solid rgba(255,255,255,.35);
      border-top-color:#fff;
      animation:spin .7s linear infinite;display:none;
    }

    /* ── Secondary link button ── */
    .auth-link-btn {
      display:flex;align-items:center;justify-content:center;gap:.4rem;
      width:100%;height:50px;
      border:1.5px solid var(--border);border-radius:12px;background:#fff;
      color:var(--text);font-weight:600;font-size:.88rem;
      text-decoration:none;cursor:pointer;
      transition:border-color .2s,background .2s,color .2s,box-shadow .2s;
      font-family:var(--font);
    }
    .auth-link-btn:hover { border-color:var(--brand);background:#f5f8ff;color:var(--brand);box-shadow:0 2px 12px rgba(26,79,196,.1); }

    .auth-divider { display:flex;align-items:center;gap:1rem;margin:1.1rem 0; }
    .auth-divider::before,.auth-divider::after { content:'';flex:1;height:1px;background:var(--border); }
    .auth-divider span { font-size:.72rem;color:var(--muted);white-space:nowrap; }

    /* ── Footer ── */
    .auth-footer {
      display:flex;align-items:center;justify-content:center;gap:1.25rem;
      padding:.9rem 1rem;
      border-top:1px solid #f1f5f9;
      background:#fafbfd;
    }
    .auth-footer a {
      font-size:.8rem;color:var(--muted);text-decoration:none;
      display:flex;align-items:center;gap:.3rem;transition:color .2s;
    }
    .auth-footer a:hover { color:var(--brand); }
    .auth-footer-sep { width:4px;height:4px;border-radius:50%;background:#d1d5db;flex-shrink:0; }

    /* ── Shake animation for failed login ── */
    @keyframes shake {
      0%,100%{transform:translateX(0)}
      15%{transform:translateX(-7px)}
      30%{transform:translateX(7px)}
      45%{transform:translateX(-5px)}
      60%{transform:translateX(5px)}
      75%{transform:translateX(-3px)}
      90%{transform:translateX(3px)}
    }
    .auth-card.shaking { animation:shake .45s cubic-bezier(.36,.07,.19,.97) both; }

    /* ── 2FA digits ── */
    .tfa-digit-box { display:flex;gap:.4rem;justify-content:center;margin-bottom:1.25rem; }
    .otp-digit {
      width:48px;height:58px;
      border:1.5px solid var(--border);border-radius:13px;
      font-size:1.5rem;font-weight:800;text-align:center;
      color:var(--text);background:#f8fafc;outline:none;
      transition:border-color .2s,box-shadow .2s,background .2s;
      font-family:'SUSE',sans-serif;
    }
    .otp-digit:focus { border-color:var(--brand);background:#fff;box-shadow:0 0 0 4px rgba(26,79,196,.12); }
    .otp-digit.filled { background:#f0f5ff;border-color:#93c5fd; }

    /* ── SweetAlert2 brand theme ── */
    .dc-swal {
      font-family:var(--font) !important;
      border-radius:20px !important;
      padding:1.75rem 2rem !important;
      box-shadow:0 24px 64px rgba(0,0,0,.16) !important;
    }
    .dc-swal .swal2-title { font-family:'SUSE',sans-serif !important;font-size:1.3rem !important;font-weight:800 !important;color:#0f172a !important; }
    .dc-swal .swal2-html-container { font-size:.88rem !important;color:#475569 !important;margin-top:.25rem !important; }
    .dc-swal .swal2-icon { margin-bottom:1rem !important;transform:scale(.88); }
    .dc-btn-confirm {
      background:linear-gradient(135deg,#1a4fc4,#6d28d9) !important;
      color:#fff !important;font-weight:700 !important;font-size:.88rem !important;
      border:none !important;border-radius:10px !important;padding:.6rem 1.5rem !important;
      box-shadow:0 4px 14px rgba(26,79,196,.35) !important;cursor:pointer !important;
      font-family:var(--font) !important;
    }
    .dc-btn-confirm:hover { filter:brightness(1.08) !important; }
    .dc-btn-cancel {
      background:#f1f5f9 !important;color:#475569 !important;
      font-weight:600 !important;font-size:.88rem !important;
      border:1px solid #e2e8f0 !important;border-radius:10px !important;
      padding:.6rem 1.5rem !important;cursor:pointer !important;
      font-family:var(--font) !important;
    }
    .dc-swal-toast {
      font-family:var(--font) !important;
      border-radius:14px !important;
      box-shadow:0 8px 32px rgba(0,0,0,.14) !important;
    }

    /* ── Misc ── */
    @keyframes spin { to { transform:rotate(360deg); } }
    @media (max-width:900px) { .auth-brand{display:none;} .auth-mobile-logo{display:flex;} }
    @media (max-width:480px)  { .auth-card-inner{padding:1.75rem 1.25rem 1.5rem;} }
  </style>
</head>

<body class="auth-page main-bg main-bg-opac sharpcornerui theme-blue scrollup" data-theme="theme-blue">

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

    <!-- ── LEFT BRAND PANEL ── -->
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
          <li><div class="feat-icon"><i class="bi bi-play-circle-fill"></i></div><span>10,000+ video &amp; audio courses</span></li>
          <li><div class="feat-icon"><i class="bi bi-patch-question-fill"></i></div><span>Smart Question Bank with AI tools</span></li>
          <li><div class="feat-icon"><i class="bi bi-journal-bookmark-fill"></i></div><span>Study notes &amp; Q&amp;A for every lesson</span></li>
          <li><div class="feat-icon"><i class="bi bi-bar-chart-fill"></i></div><span>Real-time analytics &amp; progress reports</span></li>
        </ul>
      </div>

      <div class="auth-brand-footer">&copy; <?= date('Y') ?> Digital Class Media · All rights reserved</div>
    </div>

    <!-- ── RIGHT FORM PANEL ── -->
    <div class="auth-form">
      <div class="auth-card" id="authCard">

        <div class="auth-card-inner">

          <!-- Mobile logo (hidden on desktop where brand panel shows) -->
          <div class="auth-mobile-logo">
            <img src="assets/img/logo.svg" alt="DigitalClass">
            <span>DigitalClass</span>
          </div>

          <!-- ═══ LOGIN FORM ═══ -->
          <div id="loginSection">
            <div class="auth-card-title">Welcome back</div>
            <div class="auth-card-sub">Sign in to your account to continue learning</div>

            <form id="loginForm" novalidate autocomplete="on">

              <!-- Inline alert -->
              <div class="auth-alert" id="loginAlert" role="alert">
                <i class="auth-alert-icon bi bi-x-circle-fill"></i>
                <span id="loginAlertMsg"></span>
                <button type="button" class="auth-alert-close" onclick="closeLoginAlert()" aria-label="Close">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>

              <!-- Email -->
              <div class="auth-field">
                <div class="auth-field-label">
                  <span>Email Address</span>
                </div>
                <div class="auth-input-wrap">
                  <i class="bi bi-envelope auth-input-icon"></i>
                  <input type="email" id="emailaddress" class="auth-input"
                         placeholder="you@example.com" autofocus autocomplete="email">
                  <i class="bi bi-check-circle-fill auth-input-icon-right" id="emailOkIcon"
                     style="display:none;color:#22c55e;pointer-events:none"></i>
                </div>
                <div class="auth-field-hint" id="emailHint">
                  <i class="bi bi-exclamation-circle-fill"></i><span></span>
                </div>
              </div>

              <!-- Password -->
              <div class="auth-field">
                <div class="auth-field-label">
                  <span>Password</span>
                  <a href="signup/forgot-password.php">Forgot password?</a>
                </div>
                <div class="auth-input-wrap">
                  <i class="bi bi-lock auth-input-icon"></i>
                  <input type="password" id="password" class="auth-input"
                         placeholder="Enter your password" autocomplete="current-password">
                  <button type="button" class="auth-input-icon-right" id="togglePassword" tabindex="-1" aria-label="Toggle password">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                  </button>
                </div>
                <div class="auth-field-hint" id="passwordHint">
                  <i class="bi bi-exclamation-circle-fill"></i><span></span>
                </div>
              </div>

              <!-- Remember me -->
              <div class="auth-remember">
                <label class="auth-checkbox-wrap">
                  <input type="checkbox" name="rememberme" id="rememberme">
                  <div class="auth-checkbox-box"></div>
                  <span class="auth-checkbox-label">Remember me for 30 days</span>
                </label>
              </div>

              <!-- Submit -->
              <button type="submit" class="auth-btn" id="loginBtn">
                <div class="auth-btn-spinner" id="loginSpinner"></div>
                <span id="loginText"><i class="bi bi-box-arrow-in-right me-1"></i>Sign In</span>
              </button>

              <div class="auth-divider"><span>New to DigitalClass?</span></div>

              <a href="signup/" class="auth-link-btn">
                <i class="bi bi-person-plus"></i> Create a free account
              </a>

            </form>
          </div><!-- /#loginSection -->

          <!-- ═══ FORCED 2FA SETUP ═══ -->
          <div id="tfaSetupStep" style="display:none">

            <div id="setupNotice">
              <div style="text-align:center;margin-bottom:1.5rem">
                <div style="width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg,#f59e0b,#d97706);display:flex;align-items:center;justify-content:center;margin:0 auto .9rem;box-shadow:0 8px 24px rgba(245,158,11,.3)">
                  <i class="bi bi-shield-exclamation" style="font-size:1.7rem;color:#fff"></i>
                </div>
                <div class="auth-card-title" style="font-size:1.35rem">2FA Required</div>
                <p style="font-size:.875rem;color:#64748b;margin:.35rem 0 1.25rem">Your role requires two-factor authentication. Set it up now to continue.</p>
                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:.75rem 1rem;font-size:.8rem;color:#92400e;text-align:left;margin-bottom:1.25rem">
                  <i class="bi bi-info-circle-fill me-1"></i>
                  Install <strong>Google Authenticator</strong> or <strong>Authy</strong> on your phone first.
                </div>
                <button type="button" id="setupStartBtn" class="auth-btn">
                  <i class="bi bi-shield-plus me-1"></i>Set Up Authenticator
                </button>
              </div>
            </div>

            <div id="setupQrPanel" style="display:none">
              <div style="text-align:center;margin-bottom:1rem">
                <div class="auth-card-title" style="font-size:1.2rem">Scan QR Code</div>
                <p style="font-size:.82rem;color:#64748b;margin-top:.25rem">Open your authenticator app and scan below</p>
              </div>
              <div id="setupQrLoader" style="text-align:center;padding:1.5rem;color:#94a3b8">
                <div class="auth-btn-spinner" style="display:inline-block;width:28px;height:28px;border-color:rgba(26,79,196,.2);border-top-color:#1a4fc4"></div>
                <p style="margin-top:.5rem;font-size:.79rem">Generating…</p>
              </div>
              <div id="setupQrWrap" style="display:none">
                <div style="display:flex;justify-content:center;margin-bottom:1rem">
                  <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:1rem;display:inline-block">
                    <div id="setupQrCanvas"></div>
                  </div>
                </div>
                <div style="margin-bottom:1.1rem">
                  <div style="font-size:.68rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.4rem">Manual entry key</div>
                  <div id="setupSecretDisplay" style="font-family:monospace;font-size:.85rem;font-weight:700;color:#1a4fc4;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:.5rem .85rem;letter-spacing:.08em;word-break:break-all;cursor:pointer;display:flex;align-items:center;gap:.5rem" title="Click to copy">
                    <i class="bi bi-key"></i><span id="setupSecretText"></span>
                    <i class="bi bi-clipboard ms-auto" style="font-size:.75rem;opacity:.5"></i>
                  </div>
                </div>
                <button type="button" id="setupQrDoneBtn" class="auth-btn">
                  <i class="bi bi-check-circle me-1"></i>I've scanned it — Next
                </button>
              </div>
            </div>

            <div id="setupVerifyPanel" style="display:none">
              <div style="text-align:center;margin-bottom:1.1rem">
                <div class="auth-card-title" style="font-size:1.2rem">Verify Your Code</div>
                <p style="font-size:.82rem;color:#64748b;margin-top:.25rem">Enter the 6-digit code from your authenticator app</p>
              </div>
              <div class="tfa-digit-box" id="setupDigits">
                <?php for($d=0;$d<6;$d++): ?><input type="text" inputmode="numeric" maxlength="1" class="otp-digit setup-digit"><?php endfor; ?>
              </div>
              <div class="auth-alert error" id="setupError" style="display:none">
                <i class="auth-alert-icon bi bi-x-circle-fill"></i>
                <span id="setupErrorMsg"></span>
              </div>
              <button type="button" id="setupVerifyBtn" class="auth-btn">
                <div class="auth-btn-spinner" id="setupVerifySpinner"></div>
                <span id="setupVerifyText"><i class="bi bi-shield-check me-1"></i>Activate &amp; Sign In</span>
              </button>
              <button type="button" id="setupBackBtn" class="auth-link-btn" style="margin-top:.6rem;height:40px;font-size:.82rem">
                <i class="bi bi-arrow-left me-1"></i>Back
              </button>
            </div>
          </div><!-- /#tfaSetupStep -->

          <!-- ═══ 2FA VERIFY STEP ═══ -->
          <div id="tfaStep" style="display:none">
            <div style="text-align:center;margin-bottom:1.5rem">
              <div style="width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg,#1a4fc4,#6d28d9);display:flex;align-items:center;justify-content:center;margin:0 auto .9rem;box-shadow:0 8px 24px rgba(26,79,196,.3)">
                <i class="bi bi-shield-lock-fill" style="font-size:1.7rem;color:#fff"></i>
              </div>
              <div class="auth-card-title" style="font-size:1.4rem">Two-Factor Verification</div>
              <p style="font-size:.875rem;color:#64748b;margin-top:.3rem">Enter the 6-digit code from your authenticator app</p>
            </div>

            <div class="tfa-digit-box" id="tfaDigits">
              <?php for($d=0;$d<6;$d++): ?><input type="text" inputmode="numeric" maxlength="1" class="otp-digit tfa-login-digit"><?php endfor; ?>
            </div>

            <div class="auth-alert error" id="tfaError" style="display:none">
              <i class="auth-alert-icon bi bi-x-circle-fill"></i>
              <span id="tfaErrorMsg"></span>
            </div>

            <button type="button" id="tfaBtn" class="auth-btn">
              <div class="auth-btn-spinner" id="tfaSpinner"></div>
              <span id="tfaText"><i class="bi bi-check-circle me-1"></i>Verify Code</span>
            </button>

            <button type="button" id="tfaBackBtn" class="auth-link-btn" style="margin-top:.6rem;height:40px;font-size:.82rem">
              <i class="bi bi-arrow-left me-1"></i>Back to login
            </button>
          </div><!-- /#tfaStep -->

        </div><!-- /.auth-card-inner -->

        <!-- Card footer links -->
        <div class="auth-footer" id="loginFooter">
          <a href="invitees/"><i class="bi bi-ticket-perforated"></i>Invitation code</a>
          <div class="auth-footer-sep"></div>
          <a href="signup/forgot-password.php"><i class="bi bi-shield-lock"></i>Reset password</a>
          <div class="auth-footer-sep"></div>
          <a href="#" style="cursor:default;pointer-events:none;opacity:.5">
            <i class="bi bi-lock-fill" style="color:#22c55e"></i>Secured
          </a>
        </div>

      </div><!-- /.auth-card -->
    </div><!-- /.auth-form -->

  </div><!-- /.auth-wrap -->

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script src="assets/js/learning/learning-auth.js"></script>

  <script>
  /* ── SweetAlert2 branded preset ── */
  const DC_SWAL = {
    customClass: { popup:'dc-swal', confirmButton:'dc-btn-confirm', cancelButton:'dc-btn-cancel' },
    buttonsStyling: false,
  };
  const DC_TOAST = (icon, title) => Swal.fire({
    toast: true, position: 'top-end', icon, title,
    showConfirmButton: false, timer: 2800, timerProgressBar: true,
    customClass: { popup:'dc-swal-toast' },
  });

  /* ── Inline alert helpers ── */
  function showLoginAlert(type, msg) {
    const el = document.getElementById('loginAlert');
    const msgEl = document.getElementById('loginAlertMsg');
    const icon = el.querySelector('.auth-alert-icon');
    el.className = 'auth-alert ' + type;
    msgEl.textContent = msg;
    icon.className = 'auth-alert-icon bi bi-' +
      (type === 'error' ? 'x-circle-fill' : type === 'warning' ? 'exclamation-triangle-fill' : 'check-circle-fill');
    el.style.display = 'flex';
  }
  function closeLoginAlert() {
    document.getElementById('loginAlert').style.display = 'none';
  }

  function shakeCard() {
    const card = document.getElementById('authCard');
    card.classList.remove('shaking');
    void card.offsetWidth;
    card.classList.add('shaking');
    card.addEventListener('animationend', () => card.classList.remove('shaking'), { once: true });
  }

  /* ── Inline field validation ── */
  function clearFieldState(inputId) {
    const inp = document.getElementById(inputId);
    inp.classList.remove('is-error','is-success');
    const hint = document.getElementById(inputId === 'emailaddress' ? 'emailHint' : 'passwordHint');
    hint.className = 'auth-field-hint';
  }
  function setFieldError(inputId, msg) {
    const inp = document.getElementById(inputId);
    inp.classList.add('is-error'); inp.classList.remove('is-success');
    const hintId = inputId === 'emailaddress' ? 'emailHint' : 'passwordHint';
    const hint = document.getElementById(hintId);
    hint.querySelector('span').textContent = msg;
    hint.className = 'auth-field-hint error';
    if (inputId === 'emailaddress') document.getElementById('emailOkIcon').style.display = 'none';
  }

  /* ── Password toggle ── */
  document.getElementById('togglePassword').addEventListener('click', function () {
    const f = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (f.type === 'password') { f.type = 'text'; icon.className = 'bi bi-eye-slash'; }
    else { f.type = 'password'; icon.className = 'bi bi-eye'; }
  });

  /* Email live check tick */
  document.getElementById('emailaddress').addEventListener('blur', function () {
    const ok = document.getElementById('emailOkIcon');
    if (this.value && /\S+@\S+\.\S+/.test(this.value)) {
      this.classList.remove('is-error'); this.classList.add('is-success');
      ok.style.display = '';
      document.getElementById('emailHint').className = 'auth-field-hint';
    }
  });
  document.getElementById('emailaddress').addEventListener('focus', function () {
    clearFieldState('emailaddress');
    closeLoginAlert();
  });
  document.getElementById('password').addEventListener('focus', function () {
    clearFieldState('password');
    closeLoginAlert();
  });

  /* ── Login form submit ── */
  document.getElementById('loginForm').addEventListener('submit', function (e) {
    e.preventDefault();
    closeLoginAlert();

    const email = document.getElementById('emailaddress').value.trim();
    const pass  = document.getElementById('password').value;
    let valid = true;

    if (!email) { setFieldError('emailaddress', 'Email address is required.'); valid = false; }
    else if (!/\S+@\S+\.\S+/.test(email)) { setFieldError('emailaddress', 'Please enter a valid email.'); valid = false; }
    if (!pass) { setFieldError('password', 'Password is required.'); valid = false; }

    if (!valid) { shakeCard(); return; }

    /* Loading state */
    const btn = document.getElementById('loginBtn');
    const text = document.getElementById('loginText');
    const spinner = document.getElementById('loginSpinner');
    btn.disabled = true; text.style.display = 'none'; spinner.style.display = 'block';

    $.ajax({
      url: 'data_files/ajax/ajax_login.php', method: 'POST', dataType: 'json',
      data: { email, password: pass },
      success: function (res) {
        btn.disabled = false; text.style.display = ''; spinner.style.display = 'none';

        if (res.status === 'success') {
          btn.disabled = true;
          btn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Signed in!';
          btn.style.background = 'linear-gradient(135deg,#059669,#0d9488)';
          DC_TOAST('success', 'Welcome back! Redirecting…');
          setTimeout(() => window.location.href = 'data_files/?view=3002', 1200);

        } else if (res.status === '2fa_required') {
          document.getElementById('loginSection').style.display = 'none';
          document.getElementById('loginFooter').style.display = 'none';
          document.getElementById('tfaStep').style.display = '';
          setTimeout(() => document.querySelector('.tfa-login-digit').focus(), 60);

        } else if (res.status === '2fa_setup_required') {
          document.getElementById('loginSection').style.display = 'none';
          document.getElementById('loginFooter').style.display = 'none';
          document.getElementById('tfaSetupStep').style.display = '';
          document.getElementById('setupNotice').style.display = '';
          document.getElementById('setupQrPanel').style.display = 'none';
          document.getElementById('setupVerifyPanel').style.display = 'none';

        } else {
          shakeCard();
          showLoginAlert('error', res.message || 'Incorrect email or password. Please try again.');
          document.getElementById('password').value = '';
          document.getElementById('password').focus();
          clearFieldState('password');
        }
      },
      error: function (xhr) {
        btn.disabled = false; text.style.display = ''; spinner.style.display = 'none';
        shakeCard();
        showLoginAlert('error', 'Server error (' + xhr.status + '). Please try again.');
      }
    });
  });

  /* ── OTP digit behaviour (shared) ── */
  function bindOtpDigits(sel) {
    $(document).on('input', sel, function () {
      this.value = this.value.replace(/\D/g, '').slice(-1);
      $(this).toggleClass('filled', !!this.value);
      if (this.value) $(this).next(sel).focus();
    });
    $(document).on('keydown', sel, function (e) {
      if (e.key === 'Backspace' && !this.value) {
        $(this).prev(sel).focus().val('').trigger('input');
      }
    });
    $(document).on('paste', sel, function (e) {
      e.preventDefault();
      const digits = (e.originalEvent.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
      $(sel).each(function (i) { $(this).val(digits[i] || '').toggleClass('filled', !!digits[i]); });
      $(sel).eq(Math.min(digits.length, 5)).focus();
    });
  }
  bindOtpDigits('.tfa-login-digit');
  bindOtpDigits('.setup-digit');

  /* ── 2FA verify ── */
  $('#tfaBtn').on('click', function () {
    const code = $('.tfa-login-digit').map(function () { return this.value; }).get().join('');
    const errEl = document.getElementById('tfaError');
    const errMsg = document.getElementById('tfaErrorMsg');
    if (code.length < 6) {
      errMsg.textContent = 'Please enter all 6 digits.';
      errEl.style.display = 'flex'; return;
    }
    errEl.style.display = 'none';

    $('#tfaText').hide(); $('#tfaSpinner').css('display','block'); $(this).prop('disabled', true);

    $.ajax({
      url: 'data_files/ajax/ajax_2fa.php?action=verify_login',
      method: 'POST', dataType: 'json', data: { code },
      success: function (res) {
        $('#tfaText').show(); $('#tfaSpinner').css('display','none'); $('#tfaBtn').prop('disabled', false);
        if (res.status === 'success') {
          DC_TOAST('success', 'Verified! Redirecting…');
          setTimeout(() => window.location.href = 'data_files/?view=3002', 1200);
        } else {
          errMsg.textContent = res.message || 'Invalid code. Try again.';
          errEl.style.display = 'flex';
          $('.tfa-login-digit').val('').removeClass('filled');
          $('.tfa-login-digit').first().focus();
          shakeCard();
        }
      },
      error: function () {
        $('#tfaText').show(); $('#tfaSpinner').css('display','none'); $('#tfaBtn').prop('disabled', false);
        errMsg.textContent = 'Server error. Please try again.';
        errEl.style.display = 'flex';
      }
    });
  });

  $('#tfaBackBtn').on('click', function () {
    document.getElementById('tfaStep').style.display = 'none';
    document.getElementById('tfaError').style.display = 'none';
    $('.tfa-login-digit').val('').removeClass('filled');
    document.getElementById('loginSection').style.display = '';
    document.getElementById('loginFooter').style.display = '';
    document.getElementById('emailaddress').focus();
  });

  /* ── 2FA setup wizard ── */
  var _setupSecret = '';

  $('#setupStartBtn').on('click', function () {
    document.getElementById('setupNotice').style.display = 'none';
    document.getElementById('setupQrPanel').style.display = '';
    document.getElementById('setupQrLoader').style.display = 'block';
    document.getElementById('setupQrWrap').style.display = 'none';

    $.ajax({
      url: 'data_files/ajax/ajax_2fa.php?action=generate_login_secret', dataType: 'json',
      success: function (res) {
        if (res.status !== 'success') {
          Swal.fire({ ...DC_SWAL, icon:'error', title:'Error', text: res.message || 'Could not generate code.' });
          return;
        }
        _setupSecret = res.secret;
        document.getElementById('setupQrLoader').style.display = 'none';
        document.getElementById('setupQrWrap').style.display = '';
        document.getElementById('setupQrCanvas').innerHTML = '';
        new QRCode(document.getElementById('setupQrCanvas'), {
          text: res.uri, width: 160, height: 160,
          colorDark: '#0f172a', colorLight: '#f8fafc',
          correctLevel: QRCode.CorrectLevel.M
        });
        document.getElementById('setupSecretText').textContent = res.secret;
        document.getElementById('setupSecretDisplay').onclick = function () {
          navigator.clipboard.writeText(res.secret)
            .then(() => DC_TOAST('success', 'Key copied!'));
        };
      },
      error: function () {
        Swal.fire({ ...DC_SWAL, icon:'error', title:'Error', text:'Server unreachable.' });
      }
    });
  });

  $('#setupQrDoneBtn').on('click', function () {
    document.getElementById('setupQrPanel').style.display = 'none';
    document.getElementById('setupVerifyPanel').style.display = '';
    setTimeout(() => document.querySelector('.setup-digit').focus(), 60);
  });

  $('#setupVerifyBtn').on('click', function () {
    const code = $('.setup-digit').map(function () { return this.value; }).get().join('');
    const errEl = document.getElementById('setupError');
    const errMsg = document.getElementById('setupErrorMsg');
    if (code.length < 6) { errMsg.textContent = 'Please enter all 6 digits.'; errEl.style.display = 'flex'; return; }
    errEl.style.display = 'none';

    $('#setupVerifyText').hide(); $('#setupVerifySpinner').css('display','block'); $(this).prop('disabled', true);

    $.ajax({
      url: 'data_files/ajax/ajax_2fa.php?action=setup_and_login',
      method: 'POST', dataType: 'json', data: { code },
      success: function (res) {
        $('#setupVerifyText').show(); $('#setupVerifySpinner').css('display','none'); $('#setupVerifyBtn').prop('disabled', false);
        if (res.status === 'success') {
          Swal.fire({ ...DC_SWAL, icon:'success', title:'2FA Activated!', text:'Your account is now protected.', timer:2000, showConfirmButton:false })
            .then(() => window.location.href = 'data_files/?view=3002');
        } else {
          errMsg.textContent = res.message || 'Invalid code.';
          errEl.style.display = 'flex';
          $('.setup-digit').val('').removeClass('filled');
          $('.setup-digit').first().focus();
          shakeCard();
        }
      },
      error: function () {
        $('#setupVerifyText').show(); $('#setupVerifySpinner').css('display','none'); $('#setupVerifyBtn').prop('disabled', false);
        errMsg.textContent = 'Server error. Please try again.';
        errEl.style.display = 'flex';
      }
    });
  });

  $('#setupBackBtn').on('click', function () {
    document.getElementById('setupVerifyPanel').style.display = 'none';
    $('.setup-digit').val('').removeClass('filled');
    document.getElementById('setupError').style.display = 'none';
    document.getElementById('setupQrPanel').style.display = '';
  });
  </script>

</body>
</html>
