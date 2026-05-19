<?php error_reporting(E_ALL); ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
  <title>DigitalClass — Invitation Signup</title>
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
      flex: 0 0 42%;
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
    .auth-orb-2 { width: 220px; height: 220px; bottom: 60px;  left: -80px;  animation-delay: 3s; }
    .auth-orb-3 { width: 120px; height: 120px; bottom: 200px; right: 60px;  animation-delay: 6s; }
    @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-18px)} }

    .auth-brand-logo { display: flex; align-items: center; gap: .75rem; position: relative; }
    .auth-brand-logo img { height: 38px; filter: brightness(0) invert(1); }
    .auth-brand-name { font-family: 'SUSE', sans-serif; font-size: 1.4rem; font-weight: 700; letter-spacing: -.5px; }

    .auth-brand-body { position: relative; }
    .auth-brand-body h1 {
      font-family: 'SUSE', sans-serif;
      font-size: 2.5rem; font-weight: 800;
      line-height: 1.15; letter-spacing: -.5px; margin-bottom: 1rem;
    }
    .auth-brand-body h1 span { color: #93c5fd; }
    .auth-brand-body p { opacity: .75; font-size: .925rem; line-height: 1.65; max-width: 340px; }

    /* Exclusive badge */
    .invite-badge {
      display: inline-flex; align-items: center; gap: .5rem;
      background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25);
      border-radius: 100px; padding: .35rem .9rem;
      font-size: .75rem; font-weight: 600; letter-spacing: .04em;
      margin-bottom: 1.5rem; position: relative;
    }
    .invite-badge i { font-size: .85rem; color: #fbbf24; }

    .auth-features { list-style: none; position: relative; margin-top: 2rem; }
    .auth-features li {
      display: flex; align-items: center; gap: .8rem;
      padding: .6rem 0; border-bottom: 1px solid rgba(255,255,255,.1);
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
      flex: 1; display: flex; align-items: flex-start; justify-content: center;
      padding: 2rem 1.5rem; background: #f1f5f9; overflow-y: auto;
    }
    .auth-card {
      width: 100%; max-width: 480px;
      background: #fff; border-radius: 20px;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,.07), 0 20px 60px -10px rgba(0,0,0,.12);
      padding: 2.25rem 2.5rem;
      animation: slideUp .4s cubic-bezier(.16,1,.3,1) both;
      margin: auto 0;
    }
    @keyframes slideUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }

    .auth-card-header { margin-bottom: 1.75rem; }
    .auth-card-header h2 { font-family:'SUSE',sans-serif; font-size:1.55rem; font-weight:800; color:var(--text); margin-bottom:.3rem; }
    .auth-card-header p { font-size:.875rem; color:var(--muted); }

    /* Steps indicator */
    .steps-bar {
      display: flex; align-items: center; gap: 0;
      margin-bottom: 1.75rem;
    }
    .step {
      display: flex; flex-direction: column; align-items: center; gap: .3rem;
      flex: 1; position: relative;
    }
    .step-dot {
      width: 28px; height: 28px; border-radius: 50%;
      border: 2px solid var(--border);
      display: flex; align-items: center; justify-content: center;
      font-size: .7rem; font-weight: 700; color: var(--muted);
      background: #fff; z-index: 1; position: relative;
      transition: all .3s;
    }
    .step.active .step-dot {
      border-color: var(--brand-from);
      background: var(--brand-from); color: #fff;
    }
    .step.done .step-dot {
      border-color: #22c55e; background: #22c55e; color: #fff;
    }
    .step-label { font-size: .68rem; font-weight: 600; color: var(--muted); white-space: nowrap; }
    .step.active .step-label { color: var(--brand-from); }
    .step.done .step-label { color: #22c55e; }
    .step-line {
      flex: 1; height: 2px; background: var(--border);
      margin-bottom: 1rem; transition: background .3s;
    }
    .step-line.done { background: #22c55e; }

    /* ── Invitation Code field (special) ── */
    .invite-code-wrap {
      background: linear-gradient(135deg, #eff6ff, #f5f3ff);
      border: 2px dashed #bfdbfe;
      border-radius: 14px; padding: 1.1rem 1.25rem;
      margin-bottom: 1.25rem;
      transition: border-color .3s, background .3s;
    }
    .invite-code-wrap.is-valid   { border-color: #86efac; background: linear-gradient(135deg, #f0fdf4, #f0fdf4); border-style: solid; }
    .invite-code-wrap.is-invalid { border-color: #fca5a5; background: linear-gradient(135deg, #fef2f2, #fef2f2); border-style: solid; }

    .invite-code-label {
      display: flex; align-items: center; gap: .5rem;
      font-size: .75rem; font-weight: 700; color: #1e40af;
      letter-spacing: .04em; margin-bottom: .6rem;
    }
    .invite-code-label i { font-size: .9rem; }
    .invite-code-wrap.is-valid   .invite-code-label { color: #15803d; }
    .invite-code-wrap.is-invalid .invite-code-label { color: #b91c1c; }

    .invite-code-field-wrap { position: relative; }
    .invite-code-input {
      width: 100%; height: 50px;
      padding: 0 46px 0 46px;
      border: 1.5px solid #bfdbfe; border-radius: 10px;
      font-family: var(--font); font-size: 1rem; font-weight: 700;
      color: #1e3a8a; background: #fff; letter-spacing: .15em;
      text-transform: uppercase; outline: none;
      transition: border-color .2s, box-shadow .2s;
    }
    .invite-code-input:focus { border-color: var(--brand-from); box-shadow: 0 0 0 4px rgba(26,79,196,.1); }
    .invite-code-wrap.is-valid .invite-code-input   { border-color: #86efac; color: #166534; }
    .invite-code-wrap.is-invalid .invite-code-input { border-color: #fca5a5; color: #991b1b; }
    .invite-code-input::placeholder { color: #93c5fd; font-weight: 500; letter-spacing: .08em; text-transform: none; font-size: .875rem; }

    .invite-code-icon {
      position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
      font-size: 1.1rem; color: #3b82f6; pointer-events: none;
    }
    .invite-code-wrap.is-valid   .invite-code-icon { color: #22c55e; }
    .invite-code-wrap.is-invalid .invite-code-icon { color: #ef4444; }

    .invite-code-status {
      position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
      font-size: 1.1rem; display: none;
    }
    .invite-code-status.spinning { display: block; color: var(--muted); animation: spin .8s linear infinite; }
    .invite-code-status.valid    { display: block; color: #22c55e; }
    .invite-code-status.invalid  { display: block; color: #ef4444; }

    .invite-code-msg {
      margin-top: .5rem; font-size: .75rem; font-weight: 600;
      display: none;
    }
    .invite-code-msg.valid   { display: block; color: #15803d; }
    .invite-code-msg.invalid { display: block; color: #b91c1c; }

    /* ── Form fields ── */
    .name-row { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; margin-bottom: 1rem; }
    .name-row .auth-field { margin-bottom: 0; }

    .auth-field { position: relative; margin-bottom: 1rem; }
    .auth-field label {
      display: flex; align-items: center; gap: .4rem;
      font-size: .78rem; font-weight: 600;
      color: var(--text); margin-bottom: .4rem; letter-spacing: .02em;
    }
    .field-locked-badge {
      display: inline-flex; align-items: center; gap: .25rem;
      background: #dcfce7; color: #15803d;
      border-radius: 100px; padding: .1rem .45rem;
      font-size: .65rem; font-weight: 700; letter-spacing: .02em;
    }

    .field-wrap { position: relative; }
    .field-icon {
      position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
      color: var(--muted); font-size: 1rem; pointer-events: none;
      transition: color .2s;
    }
    .auth-input {
      width: 100%; height: 46px;
      padding: 0 42px 0 42px;
      border: 1.5px solid var(--border); border-radius: 11px;
      font-family: var(--font); font-size: .875rem; color: var(--text);
      background: #fafafa;
      transition: border-color .2s, box-shadow .2s, background .2s;
      outline: none;
    }
    .auth-input:focus { border-color: var(--brand-from); background: #fff; box-shadow: 0 0 0 4px rgba(26,79,196,.1); }
    .auth-input::placeholder { color: #adb5bd; }
    .auth-input[readonly] {
      background: #f0fdf4; border-color: #bbf7d0; color: #166534;
      cursor: default;
    }
    .auth-input[readonly]:focus { box-shadow: none; border-color: #86efac; }

    .eye-btn {
      position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
      background: none; border: none; cursor: pointer;
      color: var(--muted); font-size: 1rem; padding: 4px; transition: color .2s;
    }
    .eye-btn:hover { color: var(--brand-from); }

    /* Strength */
    .strength-wrap { margin: -.5rem 0 .75rem; }
    .check-strength { display:flex; gap:3px; height:4px; margin-bottom: .4rem; }
    .check-strength > div { flex:1; border-radius:3px; background:var(--border); transition:background .3s; }
    .strength-meta { display:flex; justify-content:space-between; align-items:center; }
    .strength-meta #textpassword { font-size:.73rem; color:var(--muted); }
    .strength-meta i { font-size:.8rem; color:var(--muted); }

    /* Submit */
    .auth-btn {
      width: 100%; height: 48px;
      background: linear-gradient(135deg, var(--brand-from), var(--brand-to));
      color: #fff; font-weight: 700; font-size: .92rem; letter-spacing: .02em;
      border: none; border-radius: 12px; cursor: pointer;
      display: flex; align-items: center; justify-content: center; gap: .5rem;
      transition: opacity .2s, transform .2s, box-shadow .2s;
      box-shadow: 0 4px 16px rgba(26,79,196,.35);
      font-family: var(--font); margin-bottom: 1.25rem;
    }
    .auth-btn:hover:not(:disabled) { opacity: .92; transform: translateY(-1px); box-shadow: 0 8px 24px rgba(26,79,196,.4); }
    .auth-btn:active:not(:disabled) { transform: translateY(0); }
    .auth-btn:disabled { opacity: .45; cursor: not-allowed; box-shadow: none; }

    .auth-link-btn {
      display: flex; align-items: center; justify-content: center; gap:.35rem;
      width: 100%; height: 46px;
      border: 1.5px solid var(--border); border-radius: 12px; background: #fff;
      color: var(--text); font-weight: 600; font-size: .875rem;
      text-decoration: none;
      transition: border-color .2s, background .2s; font-family: var(--font);
    }
    .auth-link-btn:hover { border-color: var(--brand-from); background: #f8faff; color: var(--brand-from); }

    .auth-divider { display:flex; align-items:center; gap:1rem; margin: 1rem 0; }
    .auth-divider::before, .auth-divider::after { content:''; flex:1; height:1px; background:var(--border); }
    .auth-divider span { font-size:.73rem; color:var(--muted); white-space:nowrap; }

    @keyframes spin { to { transform: rotate(360deg); } }
    @media (max-width: 900px) { .auth-brand { display: none; } }
    @media (max-width: 560px) {
      .auth-card { padding: 1.75rem 1.25rem; }
      .name-row { grid-template-columns: 1fr; }
    }
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
        <div class="invite-badge">
          <i class="bi bi-star-fill"></i> Invitation Only Access
        </div>
        <h1>Your Seat<br><span>Is Reserved.</span></h1>
        <p>You've been personally invited to join DigitalClass. Complete your registration using your exclusive invitation code to get started.</p>

        <ul class="auth-features">
          <li>
            <div class="feat-icon"><i class="bi bi-ticket-perforated-fill"></i></div>
            <span>Exclusive invitation-only access</span>
          </li>
          <li>
            <div class="feat-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <span>Curated courses from your institution</span>
          </li>
          <li>
            <div class="feat-icon"><i class="bi bi-people-fill"></i></div>
            <span>Direct connection with your instructor</span>
          </li>
          <li>
            <div class="feat-icon"><i class="bi bi-shield-check-fill"></i></div>
            <span>Private, secure &amp; always accessible</span>
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

        <div class="auth-card-header">
          <h2>Create your account</h2>
          <p>Enter your invitation code to get started</p>
        </div>

        <!-- Steps indicator -->
        <div class="steps-bar" id="stepsBar">
          <div class="step active" id="step1">
            <div class="step-dot" id="dot1">1</div>
            <span class="step-label">Verify Code</span>
          </div>
          <div class="step-line" id="line1"></div>
          <div class="step" id="step2">
            <div class="step-dot" id="dot2">2</div>
            <span class="step-label">Your Details</span>
          </div>
          <div class="step-line" id="line2"></div>
          <div class="step" id="step3">
            <div class="step-dot" id="dot3">3</div>
            <span class="step-label">Set Password</span>
          </div>
        </div>

        <form id="createUser" novalidate>

          <!-- ── Invitation Code ── -->
          <div class="invite-code-wrap" id="inviteWrap">
            <div class="invite-code-label">
              <i class="bi bi-ticket-perforated-fill"></i>
              <span id="inviteLabelText">ENTER YOUR INVITATION CODE</span>
            </div>
            <div class="invite-code-field-wrap">
              <i class="bi bi-qr-code invite-code-icon" id="inviteFieldIcon"></i>
              <input type="text" id="invitation_code" class="invite-code-input"
                     placeholder="e.g. DCM-2024-XXXX" maxlength="30" autofocus>
              <i class="bi bi-arrow-repeat invite-code-status" id="codeStatus"></i>
            </div>
            <div class="invite-code-msg" id="codeMsg"></div>
          </div>

          <!-- ── Name row ── -->
          <div class="name-row">
            <div class="auth-field">
              <label for="namef">
                First Name
                <span class="field-locked-badge" id="namefBadge" style="display:none"><i class="bi bi-check2"></i> Verified</span>
              </label>
              <div class="field-wrap">
                <i class="bi bi-person field-icon"></i>
                <input type="text" id="namef" class="auth-input" placeholder="First name">
              </div>
            </div>
            <div class="auth-field">
              <label for="namel">
                Last Name
                <span class="field-locked-badge" id="namelBadge" style="display:none"><i class="bi bi-check2"></i> Verified</span>
              </label>
              <div class="field-wrap">
                <i class="bi bi-person field-icon"></i>
                <input type="text" id="namel" class="auth-input" placeholder="Last name">
              </div>
            </div>
          </div>

          <!-- ── Email ── -->
          <div class="auth-field">
            <label for="emailadd">Email Address</label>
            <div class="field-wrap">
              <i class="bi bi-envelope field-icon"></i>
              <input type="email" id="emailadd" class="auth-input" placeholder="myemail@gmail.com">
            </div>
          </div>

          <!-- ── Phone ── -->
          <div class="auth-field">
            <label for="phonen">
              Phone Number
              <span class="field-locked-badge" id="phoneBadge" style="display:none"><i class="bi bi-check2"></i> Verified</span>
            </label>
            <div class="field-wrap">
              <i class="bi bi-phone field-icon"></i>
              <input type="tel" id="phonen" class="auth-input" placeholder="e.g. 712 345 678">
            </div>
          </div>

          <!-- Hidden role (always Student for invitees) -->
          <select id="user_role" style="display:none">
            <option value="1" selected>Student</option>
          </select>

          <!-- ── Password ── -->
          <div class="auth-field">
            <label for="checkstrength">Password</label>
            <div class="field-wrap">
              <i class="bi bi-lock field-icon"></i>
              <input type="password" id="checkstrength" class="auth-input" placeholder="Create a strong password">
              <button type="button" class="eye-btn" id="togglePassword" tabindex="-1">
                <i class="bi bi-eye" id="eyeIcon"></i>
              </button>
            </div>
          </div>

          <!-- Strength indicator -->
          <div class="strength-wrap">
            <div class="check-strength" id="checksterngthdisplay">
              <div></div><div></div><div></div><div></div><div></div><div></div>
            </div>
            <div class="strength-meta">
              <span id="textpassword"></span>
              <i class="bi bi-info-circle" title="Min. 8 chars with uppercase &amp; numbers"></i>
            </div>
          </div>

          <!-- ── Confirm Password ── -->
          <div class="auth-field">
            <label for="passwd">Confirm Password</label>
            <div class="field-wrap">
              <i class="bi bi-shield-lock field-icon"></i>
              <input type="password" id="passwd" class="auth-input" placeholder="Repeat your password">
              <button type="button" class="eye-btn" id="toggleConfirmPassword" tabindex="-1">
                <i class="bi bi-eye" id="eyeIcon2"></i>
              </button>
            </div>
          </div>

          <!-- ── Submit ── -->
          <button type="submit" class="auth-btn" id="signupBtn" disabled>
            <span id="btnText"><i class="bi bi-person-check me-1"></i>Complete Registration</span>
            <span id="btnLoader" style="display:none">
              <i class="bi bi-arrow-repeat" style="animation:spin .8s linear infinite"></i>
              Creating account&hellip;
            </span>
          </button>

          <div class="auth-divider"><span>already have an account?</span></div>

          <a href="../" class="auth-link-btn">
            <i class="bi bi-box-arrow-in-right"></i> Sign in instead
          </a>

        </form>
      </div>
    </div>

  </div><!-- .auth-wrap -->

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="../assets/js/learning/learning-auth.js"></script>

  <script>

    /* ── Toggle password visibility ── */
    $('#togglePassword').on('click', function () {
      let f = $('#checkstrength'), icon = $('#eyeIcon');
      if (f.attr('type') === 'password') { f.attr('type','text'); icon.removeClass('bi-eye').addClass('bi-eye-slash'); }
      else { f.attr('type','password'); icon.removeClass('bi-eye-slash').addClass('bi-eye'); }
    });
    $('#toggleConfirmPassword').on('click', function () {
      let f = $('#passwd'), icon = $('#eyeIcon2');
      if (f.attr('type') === 'password') { f.attr('type','text'); icon.removeClass('bi-eye').addClass('bi-eye-slash'); }
      else { f.attr('type','password'); icon.removeClass('bi-eye-slash').addClass('bi-eye'); }
    });

    /* ── Steps helper ── */
    function setStep(n) {
      // Step 1 always done once we move forward
      for (let i = 1; i <= 3; i++) {
        let $step = $('#step' + i), $dot = $('#dot' + i);
        $step.removeClass('active done');
        if (i < n)       { $step.addClass('done');   $dot.html('<i class="bi bi-check2"></i>'); }
        else if (i === n){ $step.addClass('active');  $dot.text(i); }
        else             { $dot.text(i); }
      }
      $('#line1').toggleClass('done', n > 1);
      $('#line2').toggleClass('done', n > 2);
    }

    /* ── Invitation code validation ── */
    let inviteTimer;
    let codeVerified = false;

    function setCodeState(state, msg) {
      let $wrap    = $('#inviteWrap');
      let $status  = $('#codeStatus');
      let $codeMsg = $('#codeMsg');
      let $icon    = $('#inviteFieldIcon');
      let $label   = $('#inviteLabelText');

      $wrap.removeClass('is-valid is-invalid');
      $status.removeClass('spinning valid invalid');
      $codeMsg.removeClass('valid invalid').hide();

      if (state === 'validating') {
        $status.addClass('spinning').removeClass('bi-check-circle-fill bi-x-circle-fill').addClass('bi-arrow-repeat');
        $label.text('VERIFYING CODE…');
      } else if (state === 'valid') {
        $wrap.addClass('is-valid');
        $status.addClass('valid').removeClass('bi-arrow-repeat bi-x-circle-fill').addClass('bi-check-circle-fill');
        $icon.removeClass('bi-qr-code').addClass('bi-patch-check-fill');
        $label.text('INVITATION VERIFIED');
        $codeMsg.addClass('valid').text('✓ ' + msg).show();
        setStep(2);
      } else if (state === 'invalid') {
        $wrap.addClass('is-invalid');
        $status.addClass('invalid').removeClass('bi-arrow-repeat bi-check-circle-fill').addClass('bi-x-circle-fill');
        $icon.removeClass('bi-patch-check-fill').addClass('bi-qr-code');
        $label.text('INVALID CODE');
        $codeMsg.addClass('invalid').text('✗ ' + msg).show();
        setStep(1);
      } else {
        // reset
        $icon.removeClass('bi-patch-check-fill').addClass('bi-qr-code');
        $label.text('ENTER YOUR INVITATION CODE');
        setStep(1);
      }
    }

    function lockField(id, badgeId) {
      $('#' + id).prop('readonly', true);
      $('#' + badgeId).show();
    }
    function unlockField(id, badgeId) {
      $('#' + id).val('').prop('readonly', false);
      $('#' + badgeId).hide();
    }

    $('#invitation_code').on('input', function () {
      clearTimeout(inviteTimer);

      let code = $(this).val().trim();
      codeVerified = false;
      $('#signupBtn').prop('disabled', true);

      unlockField('namef',  'namefBadge');
      unlockField('namel',  'namelBadge');
      unlockField('phonen', 'phoneBadge');
      setCodeState('reset');

      if (code.length < 4) return;

      setCodeState('validating');

      inviteTimer = setTimeout(function () {
        $.ajax({
          url: '../data_files/ajax/ajax_validate_invite.php',
          method: 'POST',
          dataType: 'json',
          data: { invitation_code: code },
          success: function (res) {
            if (res.status === 'success') {
              codeVerified = true;

              $('#namef').val(res.data.first_name);
              $('#namel').val(res.data.last_name);
              $('#phonen').val(res.data.phone);

              lockField('namef',  'namefBadge');
              lockField('namel',  'namelBadge');
              lockField('phonen', 'phoneBadge');

              setCodeState('valid', 'Welcome, ' + res.data.first_name + '! Fill in the details below.');
              $('#signupBtn').prop('disabled', false);
              setStep(3);

              Swal.fire({
                icon: 'success', title: 'Code Verified!',
                text: 'Invitation accepted. Complete your registration below.',
                timer: 1800, showConfirmButton: false,
                position: 'top-end', toast: true
              });
            } else {
              codeVerified = false;
              setCodeState('invalid', res.message || 'This invitation code is not valid or has already been used.');
              $('#signupBtn').prop('disabled', true);
            }
          },
          error: function () {
            setCodeState('invalid', 'Could not verify code. Check your connection and try again.');
          }
        });
      }, 600);
    });

    /* ── Form submit ── */
    $('#createUser').on('submit', function (e) {
      e.preventDefault();

      if (!codeVerified) {
        Swal.fire({ icon: 'warning', title: 'Verify Your Code', text: 'Please enter and verify your invitation code first.' });
        return;
      }

      $('#btnText').hide();
      $('#btnLoader').show();
      $('#signupBtn').prop('disabled', true);

      let data = {
        invitation_code:  $('#invitation_code').val(),
        first_name:       $('#namef').val(),
        last_name:        $('#namel').val(),
        email:            $('#emailadd').val(),
        phone:            $('#phonen').val(),
        user_role:        $('#user_role').val(),
        password:         $('#checkstrength').val(),
        confirm_password: $('#passwd').val()
      };

      $.ajax({
        url: '../data_files/ajax/ajax_create_user_invite.php',
        method: 'POST',
        data: data,
        success: function (response) {
          $('#btnText').show();
          $('#btnLoader').hide();
          $('#signupBtn').prop('disabled', false);

          if (response.trim() === 'success') {
            Swal.fire({
              icon: 'success', title: 'Welcome to DigitalClass!',
              text: 'Your account has been created successfully.',
              confirmButtonColor: '#1a4fc4'
            }).then(() => window.location.href = '../');
          } else {
            Swal.fire({ icon: 'error', title: 'Registration Failed', text: response, confirmButtonColor: '#d33' });
          }
        },
        error: function (xhr, status, error) {
          $('#btnText').show();
          $('#btnLoader').hide();
          $('#signupBtn').prop('disabled', false);
          Swal.fire({ icon: 'error', title: 'Request Failed', text: xhr.status + ' — ' + error });
        }
      });
    });

  </script>

</body>
</html>
