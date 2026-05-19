<?php /* admin_2fa.php — Two-Factor Authentication Policy & Settings */ ?>
<style>
.tfa-wrap { font-family:'Open Sans',sans-serif; }

/* Two-column body row */
.tfa-body-row { display:grid; grid-template-columns:1fr; gap:1.1rem; align-items:start; }
.tfa-body-row.tfa-two-col { grid-template-columns:1fr 1fr; }
@media (max-width:920px) { .tfa-body-row.tfa-two-col { grid-template-columns:1fr; } }

/* Hero */
.tfa-hero { position:relative; overflow:hidden; border-radius:20px; padding:2rem 2.2rem; margin-bottom:1.5rem;
  background:linear-gradient(135deg,#0a0f1e 0%,#0f1e3d 55%,#1a0845 100%); }
.tfa-hero-grid { position:absolute; inset:0; background-image:
  linear-gradient(rgba(255,255,255,.022) 1px,transparent 1px),
  linear-gradient(90deg,rgba(255,255,255,.022) 1px,transparent 1px);
  background-size:40px 40px; }
.tfa-hero-orb { position:absolute; right:2rem; top:50%; transform:translateY(-50%);
  width:180px; height:180px; border-radius:50%;
  background:conic-gradient(from 0deg,rgba(99,102,241,.5),rgba(139,92,246,.35),rgba(99,102,241,.5));
  filter:blur(38px); opacity:.55; animation:db-orb-spin 16s linear infinite; }
@keyframes db-orb-spin { to { transform:translateY(-50%) rotate(360deg); } }
.tfa-hero-inner { position:relative; z-index:1; }
.tfa-hero-badge { display:inline-flex; align-items:center; gap:.4rem;
  background:rgba(255,255,255,.09); border:1px solid rgba(255,255,255,.15);
  border-radius:100px; padding:.25rem .8rem; font-size:.68rem; font-weight:700;
  color:rgba(255,255,255,.7); letter-spacing:.06em; text-transform:uppercase;
  margin-bottom:.55rem; backdrop-filter:blur(6px); }
.tfa-hero-title { font-size:1.55rem; font-weight:900; color:#fff; font-family:'SUSE',sans-serif;
  letter-spacing:-.03em; margin-bottom:.2rem; }
.tfa-hero-title em { font-style:normal;
  background:linear-gradient(90deg,#818cf8,#c084fc); -webkit-background-clip:text;
  background-clip:text; -webkit-text-fill-color:transparent; }
.tfa-hero-sub { font-size:.8rem; color:rgba(255,255,255,.42); line-height:1.6; max-width:520px; }

/* Section label */
.tfa-section-label { font-size:.72rem; font-weight:800; color:#475569; text-transform:uppercase;
  letter-spacing:.1em; margin:1.5rem 0 .7rem; padding-left:.1rem; display:flex;
  align-items:center; gap:.55rem; }
.tfa-section-label::after { content:''; flex:1; height:1px; background:#e2e8f0; }

/* Card */
.tfa-card { background:#fff; border-radius:18px; border:1px solid #f0f4f8;
  box-shadow:0 1px 3px rgba(0,0,0,.05),0 6px 20px rgba(0,0,0,.05); overflow:hidden; margin-bottom:1rem; }
.tfa-card-hdr { display:flex; align-items:center; gap:.6rem; padding:.9rem 1.3rem;
  border-bottom:1px solid #f0f4f8; }
.tfa-card-hdr-icon { width:2rem; height:2rem; border-radius:8px; display:flex;
  align-items:center; justify-content:center; font-size:.9rem; }
.tfa-card-hdr-title { font-size:.72rem; font-weight:800; color:#475569;
  text-transform:uppercase; letter-spacing:.07em; }
.tfa-card-hdr-sub { font-size:.75rem; color:#94a3b8; margin-left:auto; }
.tfa-card-body { padding:1.3rem; }

/* Role Grid */
.tfa-role-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(148px,1fr)); gap:.75rem; }
.tfa-role-card { border-radius:14px; border:1.5px solid #e2e8f0; padding:.95rem 1rem;
  background:#fff; transition:border-color .2s,box-shadow .2s; }
.tfa-role-card.required { border-color:#6366f1; background:#fafaff;
  box-shadow:0 4px 16px rgba(99,102,241,.08); }
.tfa-role-card-top { display:flex; align-items:center; gap:.65rem; margin-bottom:.75rem; }
.tfa-role-icon { width:2.2rem; height:2.2rem; border-radius:10px; display:flex;
  align-items:center; justify-content:center; font-size:.95rem; flex-shrink:0; }
.tfa-role-name { font-size:.82rem; font-weight:700; color:#0f172a; line-height:1.3; }
.tfa-role-count { font-size:.7rem; color:#94a3b8; margin-top:.15rem; }
.tfa-role-card-footer { display:flex; align-items:center; justify-content:space-between; }
.tfa-role-badge { font-size:.65rem; font-weight:700; border-radius:100px; padding:.18rem .6rem; }
.tfa-role-badge.req { background:#ede9fe; color:#6d28d9; }
.tfa-role-badge.opt { background:#f1f5f9; color:#64748b; }

/* Toggle switch */
.tfa-toggle { position:relative; display:inline-block; width:42px; height:24px; flex-shrink:0; }
.tfa-toggle input { opacity:0; width:0; height:0; position:absolute; }
.tfa-toggle-track { position:absolute; inset:0; border-radius:100px; background:#e2e8f0;
  cursor:pointer; transition:background .25s; }
.tfa-toggle input:checked + .tfa-toggle-track { background:linear-gradient(135deg,#6366f1,#8b5cf6); }
.tfa-toggle-track::after { content:''; position:absolute; width:18px; height:18px;
  left:3px; top:3px; border-radius:50%; background:#fff; transition:transform .25s;
  box-shadow:0 1px 3px rgba(0,0,0,.2); }
.tfa-toggle input:checked + .tfa-toggle-track::after { transform:translateX(18px); }
.tfa-toggle input:disabled + .tfa-toggle-track { opacity:.45; cursor:not-allowed; }

/* Status badge */
.tfa-status-pill { display:inline-flex; align-items:center; gap:.4rem;
  border-radius:100px; padding:.3rem .9rem; font-size:.75rem; font-weight:700; margin-top:.9rem; }
.tfa-status-pill.on  { background:rgba(5,150,105,.18); color:#34d399; border:1px solid rgba(52,211,153,.25); }
.tfa-status-pill.off { background:rgba(239,68,68,.13);  color:#f87171; border:1px solid rgba(248,113,113,.22); }

/* Steps */
.tfa-steps { display:flex; gap:0; margin-bottom:1.4rem; }
.tfa-step { flex:1; display:flex; flex-direction:column; align-items:center; position:relative; }
.tfa-step:not(:last-child)::after { content:''; position:absolute; top:14px; left:calc(50% + 14px);
  width:calc(100% - 28px); height:2px; background:#e2e8f0; }
.tfa-step-dot { width:28px; height:28px; border-radius:50%; display:flex; align-items:center;
  justify-content:center; font-size:.72rem; font-weight:800; z-index:1;
  background:#f1f5f9; color:#94a3b8; border:2px solid #e2e8f0; margin-bottom:.4rem; transition:.2s; }
.tfa-step.active .tfa-step-dot { background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; border-color:transparent; box-shadow:0 4px 12px rgba(99,102,241,.35); }
.tfa-step.done   .tfa-step-dot { background:#059669; color:#fff; border-color:transparent; }
.tfa-step-lbl { font-size:.65rem; font-weight:600; color:#94a3b8; text-align:center; text-transform:uppercase; letter-spacing:.04em; }
.tfa-step.active .tfa-step-lbl, .tfa-step.done .tfa-step-lbl { color:#475569; }

/* QR */
.tfa-qr-wrap { display:flex; gap:1.4rem; align-items:flex-start; flex-wrap:wrap; }
.tfa-qr-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px;
  padding:1rem; display:flex; align-items:center; justify-content:center;
  width:176px; height:176px; flex-shrink:0; }
.tfa-qr-box canvas { border-radius:6px; }
.tfa-secret-label { font-size:.68rem; font-weight:700; color:#64748b;
  text-transform:uppercase; letter-spacing:.06em; margin-bottom:.35rem; }
.tfa-secret-code { font-family:monospace; font-size:.9rem; font-weight:700;
  color:#1a4fc4; background:#eff6ff; border:1px solid #bfdbfe;
  border-radius:10px; padding:.45rem .75rem; letter-spacing:.08em;
  word-break:break-all; display:flex; align-items:center; gap:.5rem; cursor:pointer; }
.tfa-secret-code:hover { background:#dbeafe; }
.tfa-secret-note { font-size:.75rem; color:#94a3b8; line-height:1.6; margin-top:.75rem; }

/* Code input */
.tfa-code-input { display:flex; gap:.5rem; justify-content:center; margin:1rem 0; }
.tfa-digit { width:48px; height:58px; border:2px solid #e2e8f0; border-radius:12px;
  text-align:center; font-size:1.5rem; font-weight:800; color:#0f172a;
  font-family:'SUSE',sans-serif; outline:none; transition:.15s;
  background:#fafafa; caret-color:#6366f1; }
.tfa-digit:focus { border-color:#6366f1; background:#fff; box-shadow:0 0 0 4px rgba(99,102,241,.12); }
.tfa-digit.filled { border-color:#6366f1; background:#faf5ff; }

/* Buttons */
.tfa-btn { display:inline-flex; align-items:center; gap:.45rem; border:none; cursor:pointer;
  border-radius:12px; padding:.6rem 1.4rem; font-size:.83rem; font-weight:700;
  font-family:inherit; transition:filter .18s,transform .12s,box-shadow .18s; }
.tfa-btn:hover { filter:brightness(1.07); transform:translateY(-1px); }
.tfa-btn:active { transform:translateY(0); }
.tfa-btn:disabled { opacity:.5; cursor:not-allowed; transform:none !important; }
.tfa-btn-primary { background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; box-shadow:0 4px 14px rgba(99,102,241,.35); }
.tfa-btn-danger  { background:linear-gradient(135deg,#dc2626,#ef4444); color:#fff; box-shadow:0 4px 14px rgba(220,38,38,.3); }
.tfa-btn-ghost   { background:#f1f5f9; color:#475569; }
.tfa-btn-ghost:hover { background:#e2e8f0; filter:none; }

/* Active state */
.tfa-active-card { border-radius:14px; padding:1.1rem 1.3rem;
  background:linear-gradient(135deg,#f0fdf4,#dcfce7); border:1px solid #bbf7d0;
  display:flex; align-items:center; gap:1rem; }
.tfa-active-icon { width:3rem; height:3rem; border-radius:12px;
  background:linear-gradient(135deg,#059669,#10b981); display:flex;
  align-items:center; justify-content:center; font-size:1.3rem; color:#fff;
  box-shadow:0 4px 12px rgba(5,150,105,.3); flex-shrink:0; }
.tfa-active-text h6 { font-weight:800; color:#065f46; margin:0 0 .2rem; font-size:.9rem; }
.tfa-active-text p  { font-size:.78rem; color:#047857; margin:0; }

/* Disable section */
.tfa-disable-wrap { margin-top:1.1rem; padding:1rem 1.1rem;
  background:#fff5f5; border:1px solid #fecaca; border-radius:12px; }
.tfa-disable-title { font-size:.75rem; font-weight:700; color:#dc2626;
  text-transform:uppercase; letter-spacing:.06em; margin-bottom:.6rem; }

/* Alert */
.tfa-alert { border-radius:12px; padding:.7rem 1rem; font-size:.79rem; font-weight:500;
  display:flex; align-items:center; gap:.5rem; margin-bottom:.8rem; }
.tfa-alert-info    { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
.tfa-alert-success { background:#f0fdf4; color:#065f46; border:1px solid #bbf7d0; }
.tfa-alert-danger  { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
.tfa-alert-warning { background:#fffbeb; color:#92400e; border:1px solid #fde68a; }

/* Spinner */
.tfa-spin { animation:tfa-rotate .7s linear infinite; display:inline-block; }
@keyframes tfa-rotate { to { transform:rotate(360deg); } }
</style>

<div class="container-fluid px-3 py-3 tfa-wrap">

  <!-- Hero -->
  <div class="tfa-hero">
    <div class="tfa-hero-grid"></div>
    <div class="tfa-hero-orb"></div>
    <div class="tfa-hero-inner">
      <div class="tfa-hero-badge"><i class="bi bi-shield-lock"></i> Security Policy</div>
      <div class="tfa-hero-title">Two-Factor <em>Authentication</em></div>
      <div class="tfa-hero-sub">Control which user roles must use 2FA to access the platform, and configure your own authenticator.</div>
    </div>
  </div>

  <!-- ── BODY ROW: role policy + my 2FA side by side ──── -->
  <div class="tfa-body-row" id="tfaBodyRow">

    <!-- Left col: Role Policy (super admin only) -->
    <div id="rolePolicySection" style="display:none">
      <div class="tfa-section-label"><i class="bi bi-people-fill"></i> Role-Based Policy</div>
      <div class="tfa-card">
        <div class="tfa-card-hdr">
          <div class="tfa-card-hdr-icon" style="background:#ede9fe;color:#6d28d9"><i class="bi bi-toggles"></i></div>
          <div>
            <div class="tfa-card-hdr-title">Enforce 2FA by Role</div>
          </div>
          <div class="tfa-card-hdr-sub" id="rolePolicyMeta"></div>
        </div>
        <div class="tfa-card-body">
          <div class="tfa-alert tfa-alert-info" style="margin-bottom:1rem">
            <i class="bi bi-info-circle-fill"></i>
            When 2FA is required for a role, users must enroll in an authenticator app before they can access the platform.
          </div>
          <div id="roleGrid">
            <div style="text-align:center;padding:2rem;color:#94a3b8">
              <i class="bi bi-arrow-repeat tfa-spin" style="font-size:1.4rem"></i>
              <p style="margin-top:.4rem;font-size:.79rem">Loading roles…</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right col: My 2FA -->
    <div id="myTfaSection">
      <div class="tfa-section-label"><i class="bi bi-person-fill"></i> My Two-Factor Authentication</div>
      <div style="text-align:center;padding:1.5rem;color:#94a3b8" id="myTfaLoader">
        <i class="bi bi-arrow-repeat tfa-spin" style="font-size:1.5rem"></i>
        <p style="margin-top:.5rem;font-size:.82rem">Loading…</p>
      </div>
      <div id="myTfaStatus" style="margin-bottom:.5rem"></div>
      <div id="tfaContent"></div>
    </div>

  </div>

</div>

<!-- qrcode.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
(function () {

/* ── dcmAlert ─────────────────────────────────────────── */
const dcmAlert = {
  _css:`.ds-pop{border-radius:20px!important;font-family:'Open Sans',sans-serif!important;padding:1.6rem!important}.ds-ttl{font-size:1.1rem!important;font-weight:800!important;color:#0f172a!important;margin-top:.3rem}.ds-btn{border-radius:11px!important;font-weight:700!important;font-size:.82rem!important;padding:.55rem 1.4rem!important}.ds-tst{border-radius:14px!important;font-family:'Open Sans',sans-serif!important;box-shadow:0 8px 32px rgba(0,0,0,.14)!important;padding:.75rem 1.1rem!important;border-left:4px solid}.dst-ok{border-color:#059669!important}.dst-er{border-color:#dc2626!important}`,
  _done:false, _inject(){if(!this._done){const s=document.createElement('style');s.textContent=this._css;document.head.appendChild(s);this._done=true;}},
  toast(icon,title,text=''){this._inject();const cls={success:'dst-ok',error:'dst-er'}[icon]||'';Swal.fire({toast:true,position:'top-end',showConfirmButton:false,timer:3400,timerProgressBar:true,icon,title,text,customClass:{popup:`ds-tst ${cls}`}});},
  success(t,x=''){this.toast('success',t,x);},
  error(t,x=''){this._inject();Swal.fire({icon:'error',title:t,text:x,customClass:{popup:'ds-pop',title:'ds-ttl',confirmButton:'ds-btn'},confirmButtonColor:'#dc2626',confirmButtonText:'OK'});},
  confirm(t,x,cb){this._inject();Swal.fire({icon:'warning',title:t,text:x,customClass:{popup:'ds-pop',title:'ds-ttl',confirmButton:'ds-btn',cancelButton:'ds-btn'},showCancelButton:true,confirmButtonColor:'#dc2626',cancelButtonColor:'#64748b',confirmButtonText:'Yes, proceed',cancelButtonText:'Cancel'}).then(r=>{if(r.isConfirmed)cb();});}
};

/* ── Role icon config ─────────────────────────────────── */
const roleConfig = {
  1: { icon:'bi-mortarboard-fill', bg:'#eff6ff', color:'#1d4ed8' },
  2: { icon:'bi-people-fill',      bg:'#f0fdfa', color:'#0d9488' },
  3: { icon:'bi-person-workspace', bg:'#faf5ff', color:'#7c3aed' },
  4: { icon:'bi-building-fill',    bg:'#fff7ed', color:'#c2410c' },
  5: { icon:'bi-shield-fill',      bg:'#fef2f2', color:'#dc2626' },
};

/* ── Boot ─────────────────────────────────────────────── */
loadRolePolicy();
loadMyTfaStatus();

/* ═══════════════════════════════════════════════════════
   ROLE POLICY
   ═══════════════════════════════════════════════════════ */
function loadRolePolicy() {
  fetch('ajax/ajax_2fa.php?action=get_role_policy')
    .then(r => r.json())
    .then(res => {
      if (res.status !== 'success') return; // not super admin — keep single column
      document.getElementById('rolePolicySection').style.display = '';
      document.getElementById('tfaBodyRow').classList.add('tfa-two-col');
      renderRoleGrid(res.roles);
    })
    .catch(() => {}); // silently hide for non-admins
}

function renderRoleGrid(roles) {
  const required = roles.filter(r => r.require_2fa).length;
  document.getElementById('rolePolicyMeta').textContent =
    required ? `${required} of ${roles.length} roles enforced` : 'No roles enforced';

  const grid = document.getElementById('roleGrid');
  grid.innerHTML = '';
  const wrap = document.createElement('div');
  wrap.className = 'tfa-role-grid';

  roles.forEach(role => {
    const cfg = roleConfig[role.id] || { icon:'bi-person-fill', bg:'#f1f5f9', color:'#64748b' };
    const card = document.createElement('div');
    card.className = 'tfa-role-card' + (role.require_2fa ? ' required' : '');
    card.dataset.roleId = role.id;

    card.innerHTML = `
      <div class="tfa-role-card-top">
        <div class="tfa-role-icon" style="background:${cfg.bg};color:${cfg.color}">
          <i class="bi ${cfg.icon}"></i>
        </div>
        <div>
          <div class="tfa-role-name">${role.title}</div>
          <div class="tfa-role-count">${role.user_count} user${role.user_count !== 1 ? 's' : ''}</div>
        </div>
      </div>
      <div class="tfa-role-card-footer">
        <span class="tfa-role-badge ${role.require_2fa ? 'req' : 'opt'}" id="badge-${role.id}">
          ${role.require_2fa ? 'Required' : 'Optional'}
        </span>
        <label class="tfa-toggle" title="${role.require_2fa ? 'Disable enforcement' : 'Enable enforcement'}">
          <input type="checkbox" id="toggle-${role.id}" ${role.require_2fa ? 'checked' : ''}>
          <span class="tfa-toggle-track"></span>
        </label>
      </div>`;

    wrap.appendChild(card);

    // Toggle handler (after appended so DOM exists)
    setTimeout(() => {
      const chk = document.getElementById('toggle-' + role.id);
      if (chk) chk.addEventListener('change', () => handleRoleToggle(role.id, chk));
    }, 0);
  });

  grid.appendChild(wrap);
}

function handleRoleToggle(roleId, checkbox) {
  const val     = checkbox.checked ? '1' : '0';
  const card    = checkbox.closest('.tfa-role-card');
  const badge   = document.getElementById('badge-' + roleId);
  checkbox.disabled = true;

  // Optimistic UI
  card.classList.toggle('required', checkbox.checked);
  if (badge) { badge.className = 'tfa-role-badge ' + (checkbox.checked ? 'req' : 'opt'); badge.textContent = checkbox.checked ? 'Required' : 'Optional'; }

  const fd = new FormData();
  fd.append('role_id',     roleId);
  fd.append('require_2fa', val);

  fetch('ajax/ajax_2fa.php?action=set_role_policy', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
      checkbox.disabled = false;
      if (res.status === 'success') {
        dcmAlert.success(checkbox.checked ? '2FA enforced' : '2FA optional', '');
        // Update meta count
        const cards    = document.querySelectorAll('.tfa-role-card.required');
        const total    = document.querySelectorAll('.tfa-role-card').length;
        const metaEl   = document.getElementById('rolePolicyMeta');
        if (metaEl) metaEl.textContent = cards.length ? `${cards.length} of ${total} roles enforced` : 'No roles enforced';
      } else {
        // Revert
        checkbox.checked = !checkbox.checked;
        card.classList.toggle('required', checkbox.checked);
        if (badge) { badge.className = 'tfa-role-badge ' + (checkbox.checked ? 'req' : 'opt'); badge.textContent = checkbox.checked ? 'Required' : 'Optional'; }
        dcmAlert.error('Failed to update', res.message || 'Please try again.');
      }
    })
    .catch(() => {
      checkbox.disabled = false;
      checkbox.checked  = !checkbox.checked;
      dcmAlert.error('Network error', 'Could not reach the server.');
    });
}

/* ═══════════════════════════════════════════════════════
   MY 2FA (personal)
   ═══════════════════════════════════════════════════════ */
let currentSecret = '';
let currentUri    = '';

function loadMyTfaStatus() {
  fetch('ajax/ajax_2fa.php?action=status')
    .then(r => r.json())
    .then(res => {
      document.getElementById('myTfaLoader').style.display = 'none';
      if (res.status !== 'success') { showError('Failed to load 2FA status.'); return; }
      res.enabled ? renderEnabled() : renderSetup();
    })
    .catch(() => showError('Could not reach the server.'));
}

/* ── ENABLED ──────────────────────────────────────────── */
function renderEnabled() {
  document.getElementById('myTfaStatus').innerHTML =
    '<div class="tfa-status-pill on"><i class="bi bi-shield-fill-check"></i> 2FA is Active</div>';

  document.getElementById('tfaContent').innerHTML = `
    <div class="tfa-card">
      <div class="tfa-card-hdr">
        <div class="tfa-card-hdr-icon" style="background:#f0fdf4;color:#059669"><i class="bi bi-shield-check"></i></div>
        <div class="tfa-card-hdr-title">Two-Factor Authentication</div>
      </div>
      <div class="tfa-card-body">
        <div class="tfa-active-card">
          <div class="tfa-active-icon"><i class="bi bi-shield-fill-check"></i></div>
          <div class="tfa-active-text">
            <h6>Your account is protected</h6>
            <p>Every login requires a 6-digit code from your authenticator app.</p>
          </div>
        </div>
        <div class="tfa-disable-wrap" style="margin-top:1.2rem">
          <div class="tfa-disable-title"><i class="bi bi-exclamation-triangle me-1"></i>Disable 2FA</div>
          <p style="font-size:.78rem;color:#64748b;margin-bottom:.8rem">Enter your current authenticator code to confirm and disable 2FA.</p>
          <div class="tfa-code-input" id="disableDigits"></div>
          <div id="disableMsg"></div>
          <div style="display:flex;gap:.6rem;flex-wrap:wrap;margin-top:.5rem">
            <button class="tfa-btn tfa-btn-danger" id="disableBtn"><i class="bi bi-shield-x"></i>Disable 2FA</button>
          </div>
        </div>
      </div>
    </div>`;

  buildDigits('disableDigits');
  document.getElementById('disableBtn').addEventListener('click', handleDisable);
}

/* ── SETUP ────────────────────────────────────────────── */
function renderSetup(step) {
  document.getElementById('myTfaStatus').innerHTML =
    '<div class="tfa-status-pill off"><i class="bi bi-shield-x"></i> 2FA is Off</div>';
  (!step || step === 1) ? renderStep1() : renderStep2();
}

function renderStep1() {
  document.getElementById('tfaContent').innerHTML = `
    <div class="tfa-card">
      <div class="tfa-card-hdr">
        <div class="tfa-card-hdr-icon" style="background:#eff6ff;color:#6366f1"><i class="bi bi-shield-plus"></i></div>
        <div class="tfa-card-hdr-title">Enable Two-Factor Authentication</div>
      </div>
      <div class="tfa-card-body">
        <div class="tfa-steps">
          <div class="tfa-step active"><div class="tfa-step-dot">1</div><div class="tfa-step-lbl">Scan QR</div></div>
          <div class="tfa-step"><div class="tfa-step-dot">2</div><div class="tfa-step-lbl">Verify</div></div>
          <div class="tfa-step"><div class="tfa-step-dot">3</div><div class="tfa-step-lbl">Done</div></div>
        </div>
        <div class="tfa-alert tfa-alert-info">
          <i class="bi bi-info-circle-fill"></i>
          Install <strong>Google Authenticator</strong>, <strong>Authy</strong>, or any TOTP app, then scan the QR code below.
        </div>
        <div id="qrLoadWrap" style="text-align:center;padding:2rem;color:#94a3b8">
          <i class="bi bi-arrow-repeat tfa-spin" style="font-size:1.4rem"></i>
          <p style="margin-top:.4rem;font-size:.79rem">Generating your secret…</p>
        </div>
        <div id="qrSetupWrap" style="display:none">
          <div class="tfa-qr-wrap">
            <div class="tfa-qr-box"><div id="qrCanvas"></div></div>
            <div style="flex:1;min-width:200px">
              <div class="tfa-secret-label">Manual entry key</div>
              <div class="tfa-secret-code" id="secretDisplay" title="Click to copy">
                <i class="bi bi-key"></i><span id="secretText"></span>
                <i class="bi bi-clipboard ms-auto" style="font-size:.7rem;opacity:.5"></i>
              </div>
              <div class="tfa-secret-note">Can't scan? Open your authenticator, choose <em>Enter a setup key</em>, and type the code above manually.</div>
            </div>
          </div>
          <div style="display:flex;gap:.6rem;margin-top:1.2rem;flex-wrap:wrap">
            <button class="tfa-btn tfa-btn-primary" id="nextBtn"><i class="bi bi-arrow-right-circle"></i>I've scanned it — Next</button>
            <button class="tfa-btn tfa-btn-ghost"   id="refreshBtn"><i class="bi bi-arrow-clockwise"></i>Regenerate</button>
          </div>
        </div>
      </div>
    </div>`;

  generateSecret();
  document.getElementById('nextBtn').addEventListener('click', () => renderStep2());
  document.getElementById('refreshBtn').addEventListener('click', generateSecret);
}

function renderStep2() {
  document.getElementById('tfaContent').innerHTML = `
    <div class="tfa-card">
      <div class="tfa-card-hdr">
        <div class="tfa-card-hdr-icon" style="background:#eff6ff;color:#6366f1"><i class="bi bi-phone"></i></div>
        <div class="tfa-card-hdr-title">Verify Your Authenticator</div>
      </div>
      <div class="tfa-card-body">
        <div class="tfa-steps">
          <div class="tfa-step done"><div class="tfa-step-dot"><i class="bi bi-check"></i></div><div class="tfa-step-lbl">Scan QR</div></div>
          <div class="tfa-step active"><div class="tfa-step-dot">2</div><div class="tfa-step-lbl">Verify</div></div>
          <div class="tfa-step"><div class="tfa-step-dot">3</div><div class="tfa-step-lbl">Done</div></div>
        </div>
        <p style="font-size:.82rem;color:#64748b;margin-bottom:1rem">Open your authenticator app and enter the 6-digit code shown for <strong>DigitalClass</strong>.</p>
        <div class="tfa-code-input" id="verifyDigits"></div>
        <div id="verifyMsg"></div>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap;margin-top:.5rem">
          <button class="tfa-btn tfa-btn-primary" id="verifyBtn"><i class="bi bi-shield-check"></i>Activate 2FA</button>
          <button class="tfa-btn tfa-btn-ghost"   id="backBtn"><i class="bi bi-arrow-left"></i>Back</button>
        </div>
      </div>
    </div>`;

  buildDigits('verifyDigits');
  document.getElementById('verifyBtn').addEventListener('click', handleVerifySetup);
  document.getElementById('backBtn').addEventListener('click', () => renderStep1());
}

/* ── Generate QR ──────────────────────────────────────── */
function generateSecret() {
  const lw = document.getElementById('qrLoadWrap');
  const qw = document.getElementById('qrSetupWrap');
  if (lw) lw.style.display = '';
  if (qw) qw.style.display = 'none';

  fetch('ajax/ajax_2fa.php?action=generate_secret')
    .then(r => r.json())
    .then(res => {
      if (res.status !== 'success') { showError('Failed to generate secret.'); return; }
      currentSecret = res.secret;
      currentUri    = res.uri;
      if (lw) lw.style.display = 'none';
      if (qw) qw.style.display = '';

      const canvas = document.getElementById('qrCanvas');
      canvas.innerHTML = '';
      new QRCode(canvas, { text:currentUri, width:148, height:148, colorDark:'#0f172a', colorLight:'#f8fafc', correctLevel:QRCode.CorrectLevel.M });

      document.getElementById('secretText').textContent = currentSecret;
      const disp = document.getElementById('secretDisplay');
      if (disp) disp.onclick = () => navigator.clipboard.writeText(currentSecret).then(() => dcmAlert.success('Copied!', 'Secret key copied to clipboard.'));
    })
    .catch(() => showError('Could not generate secret.'));
}

/* ── Digit helpers ────────────────────────────────────── */
function buildDigits(cid) {
  const wrap = document.getElementById(cid);
  if (!wrap) return;
  wrap.innerHTML = '';
  for (let i = 0; i < 6; i++) {
    const inp = document.createElement('input');
    inp.type = 'text'; inp.inputMode = 'numeric'; inp.maxLength = 1;
    inp.className = 'tfa-digit'; inp.autocomplete = 'one-time-code'; inp.dataset.idx = i;
    inp.addEventListener('input',   e => onDigitInput(e, cid));
    inp.addEventListener('keydown', e => onDigitKey(e, cid));
    inp.addEventListener('paste',   e => onDigitPaste(e, cid));
    wrap.appendChild(inp);
  }
}
function getDigits(cid) { return [...document.getElementById(cid).querySelectorAll('.tfa-digit')]; }
function getCode(cid)   { return getDigits(cid).map(d => d.value).join(''); }
function onDigitInput(e, cid) {
  const inp = e.target;
  inp.value = inp.value.replace(/\D/g, '').slice(0, 1);
  inp.classList.toggle('filled', inp.value !== '');
  if (inp.value) { const next = getDigits(cid)[+inp.dataset.idx + 1]; if (next) next.focus(); }
}
function onDigitKey(e, cid) {
  if (e.key === 'Backspace' && !e.target.value) {
    const prev = getDigits(cid)[+e.target.dataset.idx - 1];
    if (prev) { prev.value = ''; prev.classList.remove('filled'); prev.focus(); }
  }
  if (e.key === 'Enter') { const btn = document.getElementById(cid === 'verifyDigits' ? 'verifyBtn' : 'disableBtn'); if (btn) btn.click(); }
}
function onDigitPaste(e, cid) {
  e.preventDefault();
  const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
  getDigits(cid).forEach((inp, i) => { inp.value = text[i] || ''; inp.classList.toggle('filled', !!inp.value); });
  const last = getDigits(cid)[Math.min(text.length, 5)];
  if (last) last.focus();
}

/* ── Verify setup ─────────────────────────────────────── */
function handleVerifySetup() {
  const code = getCode('verifyDigits');
  const msg  = document.getElementById('verifyMsg');
  const btn  = document.getElementById('verifyBtn');
  if (code.length < 6) { msg.innerHTML = '<div class="tfa-alert tfa-alert-danger mt-2"><i class="bi bi-x-circle"></i>Please enter all 6 digits.</div>'; return; }
  msg.innerHTML = ''; btn.disabled = true; btn.innerHTML = '<i class="bi bi-arrow-repeat tfa-spin"></i>Verifying…';

  const fd = new FormData(); fd.append('code', code);
  fetch('ajax/ajax_2fa.php?action=verify_setup', { method:'POST', body:fd })
    .then(r => r.json())
    .then(res => {
      btn.disabled = false; btn.innerHTML = '<i class="bi bi-shield-check"></i>Activate 2FA';
      if (res.status === 'success') { renderDone(); }
      else {
        msg.innerHTML = `<div class="tfa-alert tfa-alert-danger mt-2"><i class="bi bi-x-circle"></i>${res.message}</div>`;
        getDigits('verifyDigits').forEach(d => { d.value = ''; d.classList.remove('filled'); });
        getDigits('verifyDigits')[0].focus();
      }
    })
    .catch(() => { btn.disabled = false; btn.innerHTML = '<i class="bi bi-shield-check"></i>Activate 2FA'; dcmAlert.error('Network error', 'Could not reach the server.'); });
}

/* ── Done ─────────────────────────────────────────────── */
function renderDone() {
  document.getElementById('myTfaStatus').innerHTML =
    '<div class="tfa-status-pill on"><i class="bi bi-shield-fill-check"></i> 2FA is Active</div>';

  document.getElementById('tfaContent').innerHTML = `
    <div class="tfa-card">
      <div class="tfa-card-hdr">
        <div class="tfa-card-hdr-icon" style="background:#f0fdf4;color:#059669"><i class="bi bi-shield-check"></i></div>
        <div class="tfa-card-hdr-title">Setup Complete</div>
      </div>
      <div class="tfa-card-body">
        <div class="tfa-steps">
          <div class="tfa-step done"><div class="tfa-step-dot"><i class="bi bi-check"></i></div><div class="tfa-step-lbl">Scan QR</div></div>
          <div class="tfa-step done"><div class="tfa-step-dot"><i class="bi bi-check"></i></div><div class="tfa-step-lbl">Verify</div></div>
          <div class="tfa-step done active"><div class="tfa-step-dot"><i class="bi bi-check"></i></div><div class="tfa-step-lbl">Done</div></div>
        </div>
        <div class="tfa-alert tfa-alert-success">
          <i class="bi bi-check-circle-fill"></i>
          <strong>2FA is now active!</strong> Your account requires an authenticator code on every login.
        </div>
        <div class="tfa-active-card">
          <div class="tfa-active-icon"><i class="bi bi-shield-fill-check"></i></div>
          <div class="tfa-active-text">
            <h6>Your account is protected</h6>
            <p>Every login requires a 6-digit code from your authenticator app. Keep your device safe.</p>
          </div>
        </div>
        <button class="tfa-btn tfa-btn-ghost mt-3" id="viewSettingsBtn"><i class="bi bi-arrow-repeat"></i>View 2FA Settings</button>
      </div>
    </div>`;

  dcmAlert.success('2FA Enabled!', 'Your account is now protected.');
  document.getElementById('viewSettingsBtn').addEventListener('click', () => renderEnabled());
}

/* ── Disable ──────────────────────────────────────────── */
function handleDisable() {
  const code = getCode('disableDigits');
  const msg  = document.getElementById('disableMsg');
  const btn  = document.getElementById('disableBtn');
  if (code.length < 6) { msg.innerHTML = '<div class="tfa-alert tfa-alert-danger mt-2"><i class="bi bi-x-circle"></i>Enter your current 6-digit code first.</div>'; return; }

  dcmAlert.confirm('Disable 2FA?', 'This will remove the extra security layer from your account.', () => {
    msg.innerHTML = ''; btn.disabled = true; btn.innerHTML = '<i class="bi bi-arrow-repeat tfa-spin"></i>Disabling…';
    const fd = new FormData(); fd.append('code', code);
    fetch('ajax/ajax_2fa.php?action=disable', { method:'POST', body:fd })
      .then(r => r.json())
      .then(res => {
        btn.disabled = false; btn.innerHTML = '<i class="bi bi-shield-x"></i>Disable 2FA';
        if (res.status === 'success') { dcmAlert.success('2FA Disabled', ''); renderSetup(); }
        else {
          msg.innerHTML = `<div class="tfa-alert tfa-alert-danger mt-2"><i class="bi bi-x-circle"></i>${res.message}</div>`;
          getDigits('disableDigits').forEach(d => { d.value = ''; d.classList.remove('filled'); });
          getDigits('disableDigits')[0].focus();
        }
      })
      .catch(() => { btn.disabled = false; btn.innerHTML = '<i class="bi bi-shield-x"></i>Disable 2FA'; dcmAlert.error('Network error', 'Could not reach the server.'); });
  });
}

function showError(msg) {
  document.getElementById('tfaContent').innerHTML =
    `<div class="tfa-card"><div class="tfa-card-body"><div class="tfa-alert tfa-alert-danger"><i class="bi bi-exclamation-triangle"></i>${msg}</div></div></div>`;
}

})();
</script>
