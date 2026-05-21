<?php error_reporting(E_ALL); ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
  <title>DigitalClass — Create Account</title>
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
    .auth-orb-2 { width: 220px; height: 220px; bottom: 60px; left: -80px; animation-delay: 3s; }
    .auth-orb-3 { width: 120px; height: 120px; bottom: 200px; right: 60px; animation-delay: 6s; }
    @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-18px)} }

    .auth-brand-logo { display: flex; align-items: center; gap: .75rem; position: relative; }
    .auth-brand-logo img { height: 38px; filter: brightness(0) invert(1); }
    .auth-brand-name { font-family: 'SUSE', sans-serif; font-size: 1.4rem; font-weight: 700; letter-spacing: -.5px; }

    .auth-brand-body { position: relative; }
    .auth-brand-body h1 {
      font-family: 'SUSE', sans-serif;
      font-size: 2.5rem; font-weight: 800;
      line-height: 1.15; letter-spacing: -.5px;
      margin-bottom: 1rem;
    }
    .auth-brand-body h1 span { color: #93c5fd; }
    .auth-brand-body p { opacity: .75; font-size: .925rem; line-height: 1.65; max-width: 340px; }

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
      padding: 2rem 1.5rem; background: #f1f5f9;
      overflow-y: auto;
    }
    .auth-card {
      width: 100%; max-width: 500px;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,.07), 0 20px 60px -10px rgba(0,0,0,.12);
      padding: 2.25rem 2.5rem;
      animation: slideUp .4s cubic-bezier(.16,1,.3,1) both;
      margin: auto 0;
    }
    @keyframes slideUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }

    .auth-card-header { margin-bottom: 1.5rem; }
    .auth-card-header h2 { font-family:'SUSE',sans-serif; font-size:1.6rem; font-weight:800; color:var(--text); margin-bottom:.3rem; }
    .auth-card-header p { font-size:.875rem; color:var(--muted); }

    /* Role cards */
    .role-section-label { font-size:.78rem; font-weight:600; color:var(--text); letter-spacing:.02em; margin-bottom:.6rem; }
    .role-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; margin-bottom: 1.25rem; }
    .role-card {
      border: 1.5px solid var(--border); border-radius: 12px;
      padding: .85rem .7rem; cursor: pointer; text-align: center;
      transition: border-color .18s, background .18s, box-shadow .18s;
      user-select: none;
    }
    .role-card:hover { border-color: var(--brand-from); background: #f8faff; }
    .role-card.active {
      border-color: var(--brand-from);
      background: linear-gradient(135deg, rgba(26,79,196,.05), rgba(109,40,217,.05));
      box-shadow: 0 0 0 3px rgba(26,79,196,.12);
    }
    .role-icon {
      width: 38px; height: 38px; border-radius: 10px; margin: 0 auto .5rem;
      background: var(--border);
      display: flex; align-items: center; justify-content: center; font-size: 1.05rem; color: var(--muted);
      transition: background .18s, color .18s;
    }
    .role-card.active .role-icon {
      background: linear-gradient(135deg, var(--brand-from), var(--brand-to));
      color: #fff;
    }
    .role-card p { font-size: .78rem; font-weight: 700; color: var(--text); margin: 0 0 .15rem; }
    .role-card small { font-size: .68rem; color: var(--muted); }

    /* Form fields */
    .name-row { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; margin-bottom: 1rem; }
    .name-row .auth-field { margin-bottom: 0; }

    .auth-field { position: relative; margin-bottom: 1rem; }
    .auth-field label {
      display: block; font-size: .78rem; font-weight: 600;
      color: var(--text); margin-bottom: .4rem; letter-spacing: .02em;
    }
    .field-wrap { position: relative; }
    .field-icon {
      position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
      color: var(--muted); font-size: 1rem; pointer-events: none;
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
    .auth-input:focus {
      border-color: var(--brand-from); background: #fff;
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

    /* Phone */
    .phone-row { display: flex; gap: .55rem; margin-bottom: 1rem; }
    .phone-row .auth-field { margin-bottom: 0; flex: 1; }
    .code-select {
      height: 46px; width: 100px; flex-shrink: 0;
      border: 1.5px solid var(--border); border-radius: 11px;
      background: #fafafa; font-family: var(--font); font-size: .875rem;
      color: var(--text); padding: 0 8px 0 12px;
      outline: none; cursor: pointer; align-self: flex-end;
      transition: border-color .2s, box-shadow .2s;
    }
    .code-select:focus { border-color: var(--brand-from); box-shadow: 0 0 0 4px rgba(26,79,196,.1); }

    /* Strength bar */
    .strength-wrap { margin: -.5rem 0 .75rem; }
    .check-strength { display:flex; gap:3px; height:4px; margin-bottom: .4rem; }
    .check-strength > div { flex:1; border-radius:3px; background:var(--border); transition:background .3s; }
    .strength-meta { display:flex; justify-content:space-between; align-items:center; }
    .strength-meta #textpassword { font-size:.73rem; color:var(--muted); }
    .strength-meta i { font-size:.8rem; color:var(--muted); cursor:default; }

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
    .auth-btn:hover { opacity: .92; transform: translateY(-1px); box-shadow: 0 8px 24px rgba(26,79,196,.4); }
    .auth-btn:active { transform: translateY(0); }
    .auth-btn:disabled { opacity: .65; transform: none; cursor: not-allowed; }

    .auth-link-btn {
      display: flex; align-items: center; justify-content: center; gap:.35rem;
      width: 100%; height: 48px;
      border: 1.5px solid var(--border); border-radius: 12px; background: #fff;
      color: var(--text); font-weight: 600; font-size: .875rem;
      text-decoration: none; cursor: pointer;
      transition: border-color .2s, background .2s;
      font-family: var(--font);
    }
    .auth-link-btn:hover { border-color: var(--brand-from); background: #f8faff; color: var(--brand-from); }

    .auth-divider { display:flex; align-items:center; gap:1rem; margin: 1rem 0; }
    .auth-divider::before, .auth-divider::after { content:''; flex:1; height:1px; background:var(--border); }
    .auth-divider span { font-size:.73rem; color:var(--muted); white-space:nowrap; }

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
        <h1>Learn. Grow.<br><span>Succeed.</span></h1>
        <p>Join thousands of students, instructors, schools, and companies already transforming the way they learn and teach — all in one powerful platform.</p>

        <ul class="auth-features">
          <li>
            <div class="feat-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <span>Courses for every level &amp; subject</span>
          </li>
          <li>
            <div class="feat-icon"><i class="bi bi-people-fill"></i></div>
            <span>Connect with expert instructors</span>
          </li>
          <li>
            <div class="feat-icon"><i class="bi bi-shield-check-fill"></i></div>
            <span>Private, secure &amp; always accessible</span>
          </li>
          <li>
            <div class="feat-icon"><i class="bi bi-trophy-fill"></i></div>
            <span>Track progress &amp; earn certificates</span>
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
          <p>Join DigitalClass — it takes less than 2 minutes</p>
        </div>

        <form id="createUser" novalidate>

          <!-- Role selection -->
          <p class="role-section-label">I am joining as</p>
          <div class="role-grid">
            <div class="role-card" data-role="1">
              <div class="role-icon"><i class="bi bi-mortarboard-fill"></i></div>
              <p>Student</p>
              <small>I want to learn</small>
            </div>
            <div class="role-card" data-role="2">
              <div class="role-icon"><i class="bi bi-people-fill"></i></div>
              <p>Parent</p>
              <small>Monitor my child</small>
            </div>
            <div class="role-card active" data-role="3">
              <div class="role-icon"><i class="bi bi-person-video3"></i></div>
              <p>Instructor</p>
              <small>I want to teach</small>
            </div>
            <div class="role-card" data-role="4">
              <div class="role-icon"><i class="bi bi-buildings-fill"></i></div>
              <p>School / Company</p>
              <small>Org or business use</small>
            </div>
          </div>

          <!-- Hidden role select (read by existing JS) -->
          <select id="user_role" style="display:none">
            <option value="1">Student</option>
            <option value="2">Parent / Guardian</option>
            <option value="3" selected>Instructor / Teacher</option>
            <option value="4">School / Company</option>
          </select>

          <!-- Name row -->
          <div class="name-row">
            <div class="auth-field">
              <label for="namef">First Name</label>
              <div class="field-wrap">
                <i class="bi bi-person field-icon"></i>
                <input type="text" id="namef" class="auth-input" placeholder="First name" autofocus>
              </div>
            </div>
            <div class="auth-field">
              <label for="namel">Last Name</label>
              <div class="field-wrap">
                <i class="bi bi-person field-icon"></i>
                <input type="text" id="namel" class="auth-input" placeholder="Last name">
              </div>
            </div>
          </div>

          <!-- Email -->
          <div class="auth-field">
            <label for="emailadd">Email Address</label>
            <div class="field-wrap">
              <i class="bi bi-envelope field-icon"></i>
              <input type="email" id="emailadd" class="auth-input" placeholder="myemail@gmail.com">
            </div>
          </div>

          <!-- Phone -->
          <div class="auth-field">
            <label for="phonen">Phone Number</label>
            <div class="phone-row">
              <select id="country_code" class="code-select">
                <option value="255" selected>+255</option>
                <option value="254">+254</option>
                <option value="256">+256</option>
              </select>
              <div class="auth-field" style="margin:0;flex:1">
                <div class="field-wrap">
                  <i class="bi bi-phone field-icon"></i>
                  <input type="tel" id="phonen" class="auth-input" placeholder="712 345 678">
                </div>
              </div>
            </div>
          </div>

          <!-- Password -->
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

          <!-- Confirm Password -->
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

          <!-- Submit -->
          <button type="submit" class="auth-btn" id="signupBtn">
            <span id="btnText">Create Account</span>
            <span id="btnLoader" style="display:none">
              <i class="bi bi-arrow-repeat" style="animation:spin .8s linear infinite"></i>
              Creating account&hellip;
            </span>
          </button>

          <div class="auth-divider"><span>already a member?</span></div>

          <a href="../" class="auth-link-btn">
            <i class="bi bi-box-arrow-in-right"></i> Sign in to your account
          </a>

        </form>
      </div>
    </div>

  </div><!-- .auth-wrap -->

  <style>
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Branded SweetAlert2 ── */
    .dc-swal {
      font-family: var(--font) !important;
      border-radius: 20px !important;
      padding: 1.75rem 2rem !important;
      box-shadow: 0 24px 64px rgba(0,0,0,.16) !important;
    }
    .dc-swal .swal2-title { font-family:'SUSE',sans-serif !important; font-size:1.3rem !important; font-weight:800 !important; color:#0f172a !important; }
    .dc-swal .swal2-html-container { font-size:.88rem !important; color:#475569 !important; margin-top:.25rem !important; }
    .dc-swal .swal2-icon { margin-bottom:1rem !important; transform:scale(.88); }
    .dc-btn-confirm {
      background: linear-gradient(135deg,#1a4fc4,#6d28d9) !important;
      color: #fff !important; font-weight: 700 !important; font-size: .88rem !important;
      border: none !important; border-radius: 10px !important; padding: .6rem 1.5rem !important;
      box-shadow: 0 4px 14px rgba(26,79,196,.35) !important; cursor: pointer !important;
      font-family: var(--font) !important;
    }
    .dc-btn-confirm:hover { filter: brightness(1.08) !important; }
    .dc-btn-cancel {
      background: #f1f5f9 !important; color: #475569 !important;
      font-weight: 600 !important; font-size: .88rem !important;
      border: 1px solid #e2e8f0 !important; border-radius: 10px !important;
      padding: .6rem 1.5rem !important; cursor: pointer !important;
      font-family: var(--font) !important;
    }
    .dc-swal-toast {
      font-family: var(--font) !important;
      border-radius: 14px !important;
      box-shadow: 0 8px 32px rgba(0,0,0,.14) !important;
    }
  </style>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="../assets/js/learning/learning-auth.js"></script>

  <script>
    const DC_SWAL = {
      customClass: { popup:'dc-swal', confirmButton:'dc-btn-confirm', cancelButton:'dc-btn-cancel' },
      buttonsStyling: false,
    };
    const DC_TOAST = (icon, title) => Swal.fire({
      toast: true, position: 'top-end', icon, title,
      showConfirmButton: false, timer: 2800, timerProgressBar: true,
      customClass: { popup:'dc-swal-toast' },
    });

    // Role card selection
    $('.role-card').on('click', function () {
      $('.role-card').removeClass('active');
      $(this).addClass('active');
      $('#user_role').val($(this).data('role'));
    });

    // Toggle password visibility
    $('#togglePassword').on('click', function () {
      let f = $('#checkstrength'), icon = $('#eyeIcon');
      if (f.attr('type') === 'password') { f.attr('type', 'text'); icon.removeClass('bi-eye').addClass('bi-eye-slash'); }
      else { f.attr('type', 'password'); icon.removeClass('bi-eye-slash').addClass('bi-eye'); }
    });

    $('#toggleConfirmPassword').on('click', function () {
      let f = $('#passwd'), icon = $('#eyeIcon2');
      if (f.attr('type') === 'password') { f.attr('type', 'text'); icon.removeClass('bi-eye').addClass('bi-eye-slash'); }
      else { f.attr('type', 'password'); icon.removeClass('bi-eye-slash').addClass('bi-eye'); }
    });

    // Form submit
    $('#createUser').on('submit', function (e) {
      e.preventDefault();
      $('#btnText').hide();
      $('#btnLoader').show();
      $('#signupBtn').prop('disabled', true);

      let data = {
        first_name: $('#namef').val(),
        last_name:  $('#namel').val(),
        email:      $('#emailadd').val(),
        phone:      $('#country_code').val() + $('#phonen').val(),
        user_role:  $('#user_role').val(),
        password:   $('#checkstrength').val(),
        confirm_password: $('#passwd').val()
      };

      $.ajax({
        url: '../data_files/ajax/ajax_create_user.php',
        method: 'POST',
        data: data,
        success: function (response) {
          $('#btnText').show();
          $('#btnLoader').hide();
          $('#signupBtn').prop('disabled', false);

          if (response.trim() === 'success') {
            DC_TOAST('success', 'Account created! Welcome to DigitalClass');
            setTimeout(() => window.location.href = '../', 1400);
          } else {
            Swal.fire({ ...DC_SWAL, icon: 'error', title: 'Sign Up Failed', text: response });
          }
        },
        error: function (xhr, status, error) {
          $('#btnText').show();
          $('#btnLoader').hide();
          $('#signupBtn').prop('disabled', false);
          Swal.fire({ ...DC_SWAL, icon: 'error', title: 'Request Failed', text: xhr.status + ' — ' + error });
        }
      });
    });
  </script>

</body>
</html>
