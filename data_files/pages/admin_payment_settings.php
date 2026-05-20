<?php
if (($user_role ?? 0) != 5) { include('403.php'); return; }

/* ── Check admin's 2FA enrollment ───────────────────────── */
$tfaRow  = $db->query("SELECT totp_enabled FROM tbl_all_users WHERE usr_code='{$db->escape_string($_SESSION['usr_code'])}' LIMIT 1")->fetch_assoc();
$tfaOn   = (bool)($tfaRow['totp_enabled'] ?? false);

/* ── Check page-access stamp (20-min TTL) ───────────────── */
$stampTs   = $_SESSION['reauth_payment_settings'] ?? 0;
$granted   = $stampTs && (time() - $stampTs) < 1200;

/* ── Only expose credentials after auth ─────────────────── */
$row      = $granted ? $db->query("SELECT * FROM tbl_payment_settings WHERE gateway='selcom' LIMIT 1")->fetch_assoc() : null;
$isActive = $row && $row['is_active'];
?>
<style>
/* ═══════════════════════════════════════════════════════
   PAYMENT SETTINGS  (ps-*)
═══════════════════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; }

.ps-root {
  font-family: 'Inter', 'Open Sans', sans-serif;
  background: #f1f5f9;
  min-height: 100vh;
  padding-bottom: 3rem;
}

/* ── Hero ─────────────────────────────────────────────── */
.ps-hero {
  background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
  padding: 1.75rem 2rem 1.75rem;
  position: relative;
  overflow: hidden;
}
.ps-hero::before {
  content: '';
  position: absolute; inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.ps-hero-inner {
  position: relative;
  max-width: 1100px;
  margin: 0 auto;
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}
.ps-hero-title {
  color: #fff;
  font-size: 1.35rem;
  font-weight: 800;
  font-family: 'SUSE', 'Inter', sans-serif;
  letter-spacing: -.02em;
  margin-bottom: .2rem;
}
.ps-hero-sub { color: rgba(255,255,255,.6); font-size: .78rem; }
.ps-hero-actions { display: flex; gap: .6rem; align-items: center; flex-wrap: wrap; }
.ps-hero-btn {
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  padding: .52rem 1.1rem;
  border-radius: 10px;
  font-size: .8rem;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  border: none;
  transition: all .15s;
}
.ps-hero-btn.outline {
  background: rgba(255,255,255,.1);
  color: rgba(255,255,255,.85);
  border: 1.5px solid rgba(255,255,255,.2);
}
.ps-hero-btn.outline:hover { background: rgba(255,255,255,.2); color: #fff; }
.ps-hero-btn.primary { background: #fff; color: #4338ca; box-shadow: 0 2px 12px rgba(0,0,0,.15); }
.ps-hero-btn.primary:hover { background: #f0f0ff; }
.ps-hero-btn:disabled { opacity: .45; cursor: not-allowed; }

/* ── Canvas ───────────────────────────────────────────── */
.ps-canvas {
  max-width: 1100px;
  margin: 1.5rem auto 0;
  padding: 0 1.25rem;
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 1.5rem;
  align-items: start;
}
@media (max-width: 860px) { .ps-canvas { grid-template-columns: 1fr; } }

/* ── Card shell ───────────────────────────────────────── */
.ps-card {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 4px 24px rgba(0,0,0,.07), 0 1px 4px rgba(0,0,0,.04);
  overflow: hidden;
  margin-bottom: 1rem;
}
.ps-card:last-child { margin-bottom: 0; }
.ps-card-head {
  padding: 1rem 1.4rem;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.ps-card-title {
  font-size: .8rem;
  font-weight: 800;
  color: #1e293b;
  text-transform: uppercase;
  letter-spacing: .06em;
  display: flex;
  align-items: center;
  gap: .5rem;
}
.ps-card-body { padding: 1.4rem; }

/* ── Status pill ──────────────────────────────────────── */
.ps-status-pill {
  display: inline-flex;
  align-items: center;
  gap: .35rem;
  font-size: .72rem;
  font-weight: 700;
  padding: .25rem .75rem;
  border-radius: 100px;
}
.ps-status-pill.on  { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
.ps-status-pill.off { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.ps-status-dot { width: 7px; height: 7px; border-radius: 50%; }
.ps-status-pill.on  .ps-status-dot { background: #10b981; animation: psDotPulse 2s infinite; }
.ps-status-pill.off .ps-status-dot { background: #ef4444; }
@keyframes psDotPulse { 0%,100%{opacity:1} 50%{opacity:.35} }

/* ── Form fields ──────────────────────────────────────── */
.ps-field { margin-bottom: 1.1rem; }
.ps-field:last-child { margin-bottom: 0; }
.ps-label { display: block; font-size: .78rem; font-weight: 700; color: #374151; margin-bottom: .45rem; }
.ps-label .req { color: #dc2626; }
.ps-label .opt { color: #9ca3af; font-weight: 500; font-size: .7rem; }
.ps-input {
  display: block; width: 100%;
  border: 2px solid #e5e7eb; border-radius: 11px;
  padding: .62rem .9rem; font-size: .86rem; font-family: inherit;
  color: #0f172a; background: #fafafa; outline: none;
  transition: border-color .15s, box-shadow .15s, background .15s;
}
.ps-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.12); background: #fff; }
.ps-input::placeholder { color: #cbd5e1; }
.ps-input.mono { font-family: 'JetBrains Mono','Fira Code',monospace; font-size: .82rem; letter-spacing: .02em; }
.ps-input-group {
  display: flex; border: 2px solid #e5e7eb; border-radius: 11px;
  overflow: hidden; background: #fafafa;
  transition: border-color .15s, box-shadow .15s;
}
.ps-input-group:focus-within { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.12); background: #fff; }
.ps-input-group .ps-prefix {
  padding: 0 .9rem; background: #f3f4f6; border-right: 2px solid #e5e7eb;
  color: #6b7280; font-size: .78rem; font-weight: 600;
  display: flex; align-items: center; white-space: nowrap; flex-shrink: 0;
}
.ps-input-group input {
  flex: 1; border: none; outline: none; padding: .62rem .9rem;
  font-size: .86rem; font-family: 'JetBrains Mono','Fira Code',monospace;
  color: #0f172a; background: transparent; letter-spacing: .02em; min-width: 0;
}
.ps-hint { font-size: .72rem; color: #94a3b8; margin-top: .38rem; display: flex; align-items: flex-start; gap: .3rem; line-height: 1.5; }
.ps-hint i { flex-shrink: 0; margin-top: .1rem; }
.ps-hint strong { color: #475569; }
.ps-secret-meta {
  display: flex; align-items: center; gap: .5rem; margin-top: .4rem;
  font-size: .72rem; color: #064e3b; background: #f0fdf4;
  border: 1px solid #bbf7d0; border-radius: 8px; padding: .35rem .7rem;
}
.ps-secret-row { display: flex; gap: .5rem; align-items: stretch; }
.ps-secret-row .ps-input { flex: 1; }
.ps-reveal-btn {
  padding: 0 .85rem; border: 2px solid #e5e7eb; border-radius: 11px;
  background: #f8fafc; color: #6366f1; font-size: .75rem; font-weight: 700;
  cursor: pointer; transition: all .14s; white-space: nowrap; flex-shrink: 0;
  font-family: inherit;
}
.ps-reveal-btn:hover { background: #ede9fe; border-color: #6366f1; }

/* ── Test result ──────────────────────────────────────── */
.ps-test-result {
  border-radius: 12px; padding: .85rem 1.1rem; font-size: .82rem; font-weight: 600;
  display: none; margin-top: 1.25rem; align-items: flex-start; gap: .6rem; line-height: 1.5;
}
.ps-test-result.show { display: flex; }
.ps-test-result.ok  { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.ps-test-result.err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

/* ── Right column ─────────────────────────────────────── */
.ps-right { position: sticky; top: 1.25rem; display: flex; flex-direction: column; gap: 1rem; }
.ps-toggle-row { display: flex; align-items: center; justify-content: space-between; padding: 1.1rem 1.4rem; }
.ps-toggle-label { font-size: .84rem; font-weight: 700; color: #1e293b; margin-bottom: .18rem; }
.ps-toggle-sub   { font-size: .72rem; color: #94a3b8; }
.ps-switch { position: relative; width: 46px; height: 26px; flex-shrink: 0; }
.ps-switch input { opacity: 0; width: 0; height: 0; }
.ps-switch-track { position: absolute; inset: 0; background: #e2e8f0; border-radius: 100px; cursor: pointer; transition: background .2s; }
.ps-switch input:checked ~ .ps-switch-track { background: #10b981; }
.ps-switch-track::after { content: ''; position: absolute; width: 20px; height: 20px; border-radius: 50%; background: #fff; top: 3px; left: 3px; box-shadow: 0 1px 4px rgba(0,0,0,.2); transition: transform .2s; }
.ps-switch input:checked ~ .ps-switch-track::after { transform: translateX(20px); }

.ps-brand-strip { padding: 1rem 1.4rem; display: flex; align-items: center; gap: .85rem; border-bottom: 1px solid #f1f5f9; }
.ps-brand-logo { width: 42px; height: 42px; border-radius: 10px; background: linear-gradient(135deg,#7c3aed,#4f46e5); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ps-brand-name { font-size: .88rem; font-weight: 800; color: #1e293b; }
.ps-brand-sub  { font-size: .72rem; color: #94a3b8; }

.ps-audit { padding: 1.1rem 1.4rem; display: flex; flex-direction: column; gap: .55rem; }
.ps-audit-row { display: flex; align-items: center; gap: .6rem; font-size: .78rem; color: #64748b; }
.ps-audit-row i { color: #94a3b8; font-size: .82rem; width: 14px; text-align: center; }
.ps-audit-row strong { color: #334155; }

.ps-methods-list { padding: 1.1rem 1.4rem; display: flex; flex-direction: column; gap: .65rem; }
.ps-method-row { display: flex; align-items: center; gap: .7rem; font-size: .8rem; color: #334155; }
.ps-method-ico { width: 32px; height: 32px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: .95rem; flex-shrink: 0; }
.ps-method-ico.card { background: #eff6ff; }
.ps-method-ico.mno  { background: #ecfdf5; }
.ps-method-name   { font-weight: 700; }
.ps-method-brands { font-size: .68rem; color: #94a3b8; margin-top: .08rem; }

.ps-steps { padding: 1.1rem 1.4rem; display: flex; flex-direction: column; gap: .75rem; }
.ps-step-row { display: flex; align-items: flex-start; gap: .75rem; }
.ps-step-num { width: 22px; height: 22px; border-radius: 50%; background: #ede9fe; color: #4f46e5; font-size: .68rem; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ps-step-txt { font-size: .78rem; color: #475569; line-height: 1.5; }
.ps-step-txt strong { color: #1e293b; }

/* ══════════════════════════════════════════════════════
   AUTH GATE OVERLAY  (ag-*)
══════════════════════════════════════════════════════ */
.ag-overlay {
  display: none;
  position: fixed; inset: 0; z-index: 8000;
  background: rgba(15,23,42,.75);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  align-items: center; justify-content: center;
}
.ag-overlay.show { display: flex; }

.ag-box {
  background: #fff; border-radius: 24px;
  padding: 2.25rem 2rem; max-width: 420px; width: 92%;
  box-shadow: 0 32px 80px rgba(0,0,0,.28);
  animation: agSlide .32s cubic-bezier(.34,1.56,.64,1);
  text-align: center;
}
@keyframes agSlide { from{opacity:0;transform:translateY(28px)} to{opacity:1;transform:translateY(0)} }

.ag-icon-ring {
  width: 72px; height: 72px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 1.25rem;
  font-size: 1.8rem;
}
.ag-icon-ring.lock  { background: linear-gradient(135deg,#1e1b4b,#4338ca); color: #fff; }
.ag-icon-ring.warn  { background: #fff7ed; color: #f59e0b; border: 2px solid #fde68a; }
.ag-icon-ring.setup { background: #fef2f2; color: #dc2626; border: 2px solid #fecaca; }

.ag-title { font-size: 1.05rem; font-weight: 900; color: #0f172a; margin-bottom: .4rem; }
.ag-sub   { font-size: .8rem; color: #64748b; line-height: 1.55; margin-bottom: 1.25rem; }

/* Workflow steps strip */
.ag-steps {
  display: flex; gap: 0; margin-bottom: 1.4rem;
  background: #f8fafc; border-radius: 12px; padding: .75rem 1rem;
}
.ag-step { flex: 1; display: flex; flex-direction: column; align-items: center; position: relative; }
.ag-step:not(:last-child)::after {
  content: ''; position: absolute; top: 12px;
  left: calc(50% + 14px); width: calc(100% - 28px);
  height: 2px; background: #e2e8f0;
}
.ag-step-dot {
  width: 26px; height: 26px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: .68rem; font-weight: 800; z-index: 1;
  background: #e2e8f0; color: #94a3b8; margin-bottom: .35rem;
  transition: all .2s;
}
.ag-step.active .ag-step-dot { background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff; box-shadow: 0 3px 10px rgba(99,102,241,.35); }
.ag-step.done   .ag-step-dot { background: #059669; color: #fff; }
.ag-step-lbl { font-size: .62rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em; }
.ag-step.active .ag-step-lbl, .ag-step.done .ag-step-lbl { color: #475569; }

/* Digit inputs */
.ag-digits { display: flex; gap: .45rem; justify-content: center; margin-bottom: .85rem; }
.ag-digit {
  width: 46px; height: 56px; border: 2px solid #e2e8f0; border-radius: 12px;
  text-align: center; font-size: 1.45rem; font-weight: 800; color: #0f172a;
  font-family: 'SUSE', 'Inter', sans-serif; outline: none; background: #fafafa;
  transition: border-color .15s, box-shadow .15s, background .15s;
  caret-color: #6366f1;
}
.ag-digit:focus  { border-color: #6366f1; background: #fff; box-shadow: 0 0 0 3px rgba(99,102,241,.12); }
.ag-digit.filled { border-color: #6366f1; background: #faf5ff; }
.ag-digit.error  { border-color: #dc2626; background: #fff5f5; animation: agShake .35s; }
@keyframes agShake { 0%,100%{transform:translateX(0)} 25%{transform:translateX(-5px)} 75%{transform:translateX(5px)} }

.ag-err { font-size: .78rem; color: #dc2626; font-weight: 600; min-height: 1.2rem; margin-bottom: .65rem; }

.ag-verify-btn {
  width: 100%; padding: .78rem; border-radius: 13px; border: none;
  font-size: .88rem; font-weight: 800; font-family: inherit; cursor: pointer;
  background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff;
  box-shadow: 0 4px 16px rgba(99,102,241,.35); transition: all .16s;
  display: flex; align-items: center; justify-content: center; gap: .5rem;
  margin-bottom: .7rem;
}
.ag-verify-btn:hover:not(:disabled) { filter: brightness(1.07); transform: translateY(-1px); }
.ag-verify-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }

.ag-secondary-link {
  font-size: .74rem; color: #94a3b8; text-decoration: none;
  display: inline-flex; align-items: center; gap: .3rem;
}
.ag-secondary-link:hover { color: #6366f1; }

/* Session timer */
.ag-timer-bar {
  height: 3px; border-radius: 100px; background: #e2e8f0;
  overflow: hidden; margin-bottom: .5rem;
}
.ag-timer-fill { height: 100%; background: linear-gradient(90deg,#6366f1,#10b981); transition: width 1s linear; }
.ag-timer-lbl { font-size: .68rem; color: #94a3b8; text-align: right; margin-bottom: .9rem; }
</style>

<!-- ════════════════════════════════════════════════════
     PAGE SHELL  (always rendered, gated by overlay)
════════════════════════════════════════════════════ -->
<div class="ps-root" id="psRoot" style="<?= $granted ? '' : 'filter:blur(3px);pointer-events:none;user-select:none' ?>">

  <!-- Hero -->
  <div class="ps-hero">
    <div class="ps-hero-inner">
      <div>
        <div style="font-size:.72rem;color:rgba(255,255,255,.5);margin-bottom:.5rem;display:flex;align-items:center;gap:.4rem">
          <i class="bi bi-speedometer2"></i>
          <a href="?view=admin_dashboard" style="color:rgba(255,255,255,.5);text-decoration:none">Admin</a>
          <span>›</span>
          <span style="color:rgba(255,255,255,.75)">Payment Settings</span>
        </div>
        <div class="ps-hero-title"><i class="bi bi-credit-card-fill me-2" style="color:#a5b4fc"></i>Payment Settings</div>
        <div class="ps-hero-sub">Configure and manage your Selcom payment gateway credentials</div>
      </div>
      <div class="ps-hero-actions" id="psHeroActions">
        <button class="ps-hero-btn outline" id="psTestBtn" onclick="psTest()">
          <i class="bi bi-wifi"></i>Test Connection
        </button>
        <button class="ps-hero-btn primary" id="psSaveBtn" onclick="psSave()">
          <i class="bi bi-floppy2-fill"></i>Save Settings
        </button>
      </div>
    </div>
  </div>

  <!-- Canvas -->
  <div class="ps-canvas">

    <!-- Left -->
    <div>
      <!-- Brand strip -->
      <div class="ps-card">
        <div class="ps-brand-strip">
          <div class="ps-brand-logo"><i class="bi bi-lightning-charge-fill" style="color:#fff;font-size:1.1rem"></i></div>
          <div style="flex:1">
            <div class="ps-brand-name">Selcom Mobile Gateway</div>
            <div class="ps-brand-sub">Tanzania · Card &amp; Mobile Wallet payments</div>
          </div>
          <span id="psStatusBadge" class="ps-status-pill <?= $isActive ? 'on' : 'off' ?>">
            <span class="ps-status-dot"></span><?= $isActive ? 'Active' : 'Inactive' ?>
          </span>
        </div>
      </div>

      <!-- Credentials -->
      <div class="ps-card">
        <div class="ps-card-head">
          <div class="ps-card-title"><i class="bi bi-key-fill" style="color:#6366f1"></i>Authentication</div>
        </div>
        <div class="ps-card-body">
          <div class="row g-3">
            <div class="col-12 col-sm-6">
              <div class="ps-field">
                <label class="ps-label">Vendor / Till Number <span class="req">*</span></label>
                <div class="ps-input-group">
                  <span class="ps-prefix"><i class="bi bi-shop me-1"></i>Vendor</span>
                  <input type="text" id="psVendor" placeholder="TILL61054532"
                         value="<?= htmlspecialchars($row['vendor'] ?? '') ?>">
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6">
              <div class="ps-field">
                <label class="ps-label">API Key <span class="req">*</span></label>
                <input type="text" id="psApiKey" class="ps-input mono"
                       placeholder="TILLXXXXXX-XXXXXXXXXXXXXXXX"
                       value="<?= htmlspecialchars($row['api_key'] ?? '') ?>">
              </div>
            </div>
            <div class="col-12">
              <div class="ps-field">
                <label class="ps-label">API Secret
                  <?php if ($row && $row['api_secret']): ?>
                    <span class="opt">— leave blank to keep existing</span>
                  <?php else: ?><span class="req">*</span><?php endif; ?>
                </label>
                <div class="ps-secret-row">
                  <input type="password" id="psApiSecret" class="ps-input mono" autocomplete="new-password"
                         placeholder="<?= $row && $row['api_secret'] ? '••••••••••••••••••••' : 'Enter API Secret' ?>">
                  <button class="ps-reveal-btn" id="psRevealBtn" onclick="psToggleSecret()">
                    <i class="bi bi-eye me-1"></i>Show
                  </button>
                </div>
                <?php if ($row && $row['api_secret']): ?>
                <div class="ps-secret-meta">
                  <i class="bi bi-shield-check-fill" style="color:#059669"></i>
                  Secret saved — ends in
                  <code style="background:#dcfce7;padding:.05rem .3rem;border-radius:4px;color:#065f46"><?= htmlspecialchars(substr($row['api_secret'], -4)) ?></code>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Endpoints -->
      <div class="ps-card">
        <div class="ps-card-head">
          <div class="ps-card-title"><i class="bi bi-globe2" style="color:#0891b2"></i>Endpoint Configuration</div>
        </div>
        <div class="ps-card-body">
          <div class="ps-field">
            <label class="ps-label">Selcom Base URL <span class="opt">— usually default</span></label>
            <div class="ps-input-group">
              <span class="ps-prefix"><i class="bi bi-link-45deg me-1"></i>https://</span>
              <input type="text" id="psBaseUrl" placeholder="apigw.selcommobile.com"
                     value="<?= htmlspecialchars(preg_replace('#^https?://#', '', $row['base_url'] ?? 'apigw.selcommobile.com')) ?>">
            </div>
            <div class="ps-hint"><i class="bi bi-info-circle"></i>Leave as default unless Selcom provides a custom endpoint.</div>
          </div>
          <div class="ps-field">
            <label class="ps-label">Webhook URL <span class="req">*</span></label>
            <input type="url" id="psWebhookUrl" class="ps-input"
                   placeholder="https://yourdomain.com/data_files/ajax/ajax_selcom_callback.php"
                   value="<?= htmlspecialchars($row['webhook_url'] ?? '') ?>">
            <div class="ps-hint">
              <i class="bi bi-exclamation-triangle-fill" style="color:#f59e0b"></i>
              <span>Must be a <strong>public HTTPS URL</strong> — <strong>localhost will not work</strong>.</span>
            </div>
          </div>
          <div class="ps-test-result" id="psTestResult">
            <i class="bi bi-check-circle-fill"></i>
            <span id="psTestMsg"></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right -->
    <div class="ps-right">

      <!-- Status toggle -->
      <div class="ps-card">
        <div class="ps-card-head">
          <div class="ps-card-title"><i class="bi bi-toggles" style="color:#059669"></i>Gateway Status</div>
        </div>
        <div class="ps-toggle-row">
          <div>
            <div class="ps-toggle-label">Accept Payments</div>
            <div class="ps-toggle-sub">Enable or disable payment processing</div>
          </div>
          <label class="ps-switch">
            <input type="checkbox" id="psIsActive" <?= $isActive ? 'checked' : '' ?>>
            <span class="ps-switch-track"></span>
          </label>
        </div>
      </div>

      <!-- Session timer (shown when granted) -->
      <?php if ($granted): ?>
      <div class="ps-card" id="psSessionCard">
        <div class="ps-card-head">
          <div class="ps-card-title"><i class="bi bi-shield-check" style="color:#059669"></i>Session</div>
        </div>
        <div style="padding:1rem 1.4rem">
          <div class="ag-timer-bar"><div class="ag-timer-fill" id="psTimerFill"></div></div>
          <div class="ag-timer-lbl" id="psTimerLbl">Session expires in —</div>
          <div style="font-size:.75rem;color:#64748b">Re-authentication required every 20 minutes.</div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Audit -->
      <?php if ($row && $row['updated_at']): ?>
      <div class="ps-card">
        <div class="ps-card-head">
          <div class="ps-card-title"><i class="bi bi-clock-history" style="color:#94a3b8"></i>Audit</div>
        </div>
        <div class="ps-audit">
          <div class="ps-audit-row"><i class="bi bi-calendar3"></i><?= date('d M Y, H:i', strtotime($row['updated_at'])) ?></div>
          <?php if ($row['updated_by']): ?>
          <div class="ps-audit-row"><i class="bi bi-person-fill"></i>Updated by <strong><?= htmlspecialchars($row['updated_by']) ?></strong></div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Supported methods -->
      <div class="ps-card">
        <div class="ps-card-head">
          <div class="ps-card-title"><i class="bi bi-wallet2" style="color:#7c3aed"></i>Supported Methods</div>
        </div>
        <div class="ps-methods-list">
          <div class="ps-method-row">
            <div class="ps-method-ico card">💳</div>
            <div><div class="ps-method-name">Card Payments</div><div class="ps-method-brands">Visa · Mastercard · Maestro · UnionPay</div></div>
          </div>
          <div class="ps-method-row">
            <div class="ps-method-ico mno">📱</div>
            <div><div class="ps-method-name">Mobile Wallets</div><div class="ps-method-brands">M-Pesa · Airtel · Tigo · Halopesa · Azampesa</div></div>
          </div>
        </div>
      </div>

      <!-- Help -->
      <div class="ps-card">
        <div class="ps-card-head">
          <div class="ps-card-title"><i class="bi bi-question-circle" style="color:#94a3b8"></i>Where to find credentials</div>
        </div>
        <div class="ps-steps">
          <?php foreach ([
            ['Log in to the','Selcom Business Portal'],
            ['Go to','Settings → API Credentials'],
            ['Copy your','API Key and API Secret'],
            ['Your Till number is on','the portal dashboard header'],
          ] as $i => [$a,$b]): ?>
          <div class="ps-step-row">
            <div class="ps-step-num"><?= $i+1 ?></div>
            <div class="ps-step-txt"><?= $a ?> <strong><?= $b ?></strong></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- ════════════════════════════════════════════════════
     GATE 1 — PAGE ACCESS  (lock screen)
════════════════════════════════════════════════════ -->
<div class="ag-overlay <?= !$granted ? 'show' : '' ?>" id="agLockOverlay">
  <div class="ag-box">

    <?php if (!$tfaOn): ?>
    <!-- 2FA not set up -->
    <div class="ag-icon-ring setup"><i class="bi bi-shield-x"></i></div>
    <div class="ag-title">2FA Setup Required</div>
    <div class="ag-sub">
      Payment settings are protected by Google Authenticator.<br>
      You must enable 2FA on your account before accessing this page.
    </div>
    <a href="?view=admin_2fa" style="display:inline-flex;align-items:center;gap:.45rem;padding:.7rem 1.4rem;border-radius:12px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-size:.85rem;font-weight:800;text-decoration:none;margin-bottom:.75rem">
      <i class="bi bi-shield-plus"></i>Set Up 2FA Now
    </a>
    <br>
    <a href="?view=admin_dashboard" class="ag-secondary-link"><i class="bi bi-arrow-left"></i>Back to Admin</a>

    <?php else: ?>
    <!-- 2FA enabled — show verification -->
    <div class="ag-icon-ring lock"><i class="bi bi-shield-lock-fill"></i></div>
    <div class="ag-title">Verify Your Identity</div>
    <div class="ag-sub">Payment settings are secured. Enter your Google Authenticator code to continue.</div>

    <!-- Workflow steps -->
    <div class="ag-steps">
      <div class="ag-step active" id="agStep1">
        <div class="ag-step-dot"><i class="bi bi-shield-lock" style="font-size:.7rem"></i></div>
        <div class="ag-step-lbl">Authenticate</div>
      </div>
      <div class="ag-step" id="agStep2">
        <div class="ag-step-dot">2</div>
        <div class="ag-step-lbl">Access Settings</div>
      </div>
    </div>

    <div class="ag-digits" id="agLockDigits"></div>
    <div class="ag-err" id="agLockErr"></div>

    <button class="ag-verify-btn" id="agLockBtn" onclick="agVerifyLock()">
      <i class="bi bi-unlock-fill"></i>Verify &amp; Unlock
    </button>
    <a href="?view=admin_dashboard" class="ag-secondary-link">
      <i class="bi bi-arrow-left"></i>Back to Admin
    </a>
    <?php endif; ?>

  </div>
</div>

<!-- ════════════════════════════════════════════════════
     GATE 2 — SAVE CONFIRMATION  (re-auth before commit)
════════════════════════════════════════════════════ -->
<div class="ag-overlay" id="agSaveOverlay">
  <div class="ag-box">
    <div class="ag-icon-ring warn"><i class="bi bi-pencil-square"></i></div>
    <div class="ag-title">Confirm Changes</div>
    <div class="ag-sub">Enter your authenticator code to commit these changes to the payment gateway configuration.</div>

    <div class="ag-steps">
      <div class="ag-step done">
        <div class="ag-step-dot"><i class="bi bi-check2" style="font-size:.7rem"></i></div>
        <div class="ag-step-lbl">Authenticated</div>
      </div>
      <div class="ag-step active">
        <div class="ag-step-dot"><i class="bi bi-shield-lock" style="font-size:.7rem"></i></div>
        <div class="ag-step-lbl">Confirm Save</div>
      </div>
    </div>

    <div class="ag-digits" id="agSaveDigits"></div>
    <div class="ag-err" id="agSaveErr"></div>

    <button class="ag-verify-btn" id="agSaveVerifyBtn" onclick="agVerifySave()">
      <i class="bi bi-floppy2-fill"></i>Verify &amp; Save
    </button>
    <button onclick="agCloseSaveOverlay()" class="ag-secondary-link" style="background:none;border:none;cursor:pointer;margin-top:.1rem">
      <i class="bi bi-x-circle"></i>Cancel
    </button>
  </div>
</div>

<script>
(function () {

const CONTEXT   = 'payment_settings';
const STAMP_TS  = <?= (int)$stampTs ?>;
const TTL       = 1200;
const IS_TFA_ON = <?= $tfaOn ? 'true' : 'false' ?>;

/* ─── Digit helpers (reused for both gates) ───────────── */
function buildDigits(cid) {
  const wrap = document.getElementById(cid);
  if (!wrap) return;
  wrap.innerHTML = '';
  for (let i = 0; i < 6; i++) {
    const inp = document.createElement('input');
    inp.type = 'text'; inp.inputMode = 'numeric'; inp.maxLength = 1;
    inp.className = 'ag-digit'; inp.dataset.idx = i;
    inp.autocomplete = i === 0 ? 'one-time-code' : 'off';
    inp.addEventListener('input',   e => onDInput(e, cid));
    inp.addEventListener('keydown', e => onDKey(e, cid));
    inp.addEventListener('paste',   e => onDPaste(e, cid));
    wrap.appendChild(inp);
  }
}
function digits(cid)  { return [...document.getElementById(cid).querySelectorAll('.ag-digit')]; }
function getCode(cid) { return digits(cid).map(d => d.value).join(''); }
function clearDigits(cid) { digits(cid).forEach(d => { d.value = ''; d.classList.remove('filled','error'); }); }
function shakeDigits(cid) { digits(cid).forEach(d => { d.classList.add('error'); setTimeout(() => d.classList.remove('error'), 400); }); }

function onDInput(e, cid) {
  const inp = e.target;
  inp.value = inp.value.replace(/\D/g,'').slice(0,1);
  inp.classList.toggle('filled', inp.value !== '');
  if (inp.value) { const nx = digits(cid)[+inp.dataset.idx + 1]; if (nx) nx.focus(); }
}
function onDKey(e, cid) {
  if (e.key === 'Backspace' && !e.target.value) {
    const pv = digits(cid)[+e.target.dataset.idx - 1];
    if (pv) { pv.value = ''; pv.classList.remove('filled'); pv.focus(); }
  }
  if (e.key === 'Enter') {
    const btn = cid === 'agLockDigits' ? document.getElementById('agLockBtn') : document.getElementById('agSaveVerifyBtn');
    if (btn) btn.click();
  }
}
function onDPaste(e, cid) {
  e.preventDefault();
  const txt = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
  digits(cid).forEach((inp, i) => { inp.value = txt[i]||''; inp.classList.toggle('filled', !!inp.value); });
  const last = digits(cid)[Math.min(txt.length, 5)]; if (last) last.focus();
}

/* ─── Gate 1: page lock ───────────────────────────────── */
if (IS_TFA_ON) buildDigits('agLockDigits');

async function agVerifyLock() {
  const code = getCode('agLockDigits');
  const err  = document.getElementById('agLockErr');
  const btn  = document.getElementById('agLockBtn');
  if (code.length < 6) { err.textContent = 'Enter all 6 digits'; return; }
  err.textContent = '';
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:14px;height:14px;border-width:2px"></span> Verifying…';

  try {
    const fd = new FormData();
    fd.append('code', code); fd.append('context', CONTEXT);
    const r = await fetch('ajax/ajax_2fa.php?action=verify_reauth', { method:'POST', body:fd });
    const d = await r.json();
    if (d.status === 'success') {
      // Mark step 2 active, then reveal page
      document.getElementById('agStep1').classList.replace('active','done');
      document.getElementById('agStep1').querySelector('.ag-step-dot').innerHTML = '<i class="bi bi-check2" style="font-size:.7rem"></i>';
      document.getElementById('agStep2').classList.add('active');
      btn.innerHTML = '<i class="bi bi-check2-circle-fill"></i> Unlocked — loading…';
      setTimeout(() => {
        document.getElementById('agLockOverlay').classList.remove('show');
        document.getElementById('psRoot').style.filter = '';
        document.getElementById('psRoot').style.pointerEvents = '';
        document.getElementById('psRoot').style.userSelect = '';
        psStartTimer(TTL);
      }, 600);
    } else {
      err.textContent = d.message || 'Invalid code';
      shakeDigits('agLockDigits'); clearDigits('agLockDigits');
      digits('agLockDigits')[0].focus();
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-unlock-fill"></i>Verify &amp; Unlock';
    }
  } catch {
    err.textContent = 'Network error — please try again';
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-unlock-fill"></i>Verify &amp; Unlock';
  }
}

/* ─── Gate 2: save confirm ────────────────────────────── */
let _pendingSavePayload = null;

function agOpenSaveOverlay(payload) {
  _pendingSavePayload = payload;
  buildDigits('agSaveDigits');
  document.getElementById('agSaveErr').textContent = '';
  document.getElementById('agSaveOverlay').classList.add('show');
  setTimeout(() => digits('agSaveDigits')[0].focus(), 80);
}
function agCloseSaveOverlay() {
  document.getElementById('agSaveOverlay').classList.remove('show');
  _pendingSavePayload = null;
  const btn = document.getElementById('psSaveBtn');
  btn.disabled = false;
  btn.innerHTML = '<i class="bi bi-floppy2-fill"></i>Save Settings';
}

async function agVerifySave() {
  const code = getCode('agSaveDigits');
  const err  = document.getElementById('agSaveErr');
  const btn  = document.getElementById('agSaveVerifyBtn');
  if (code.length < 6) { err.textContent = 'Enter all 6 digits'; return; }
  err.textContent = '';
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:14px;height:14px;border-width:2px"></span> Verifying…';

  try {
    const fd = new FormData();
    fd.append('code', code); fd.append('context', CONTEXT);
    const rv = await fetch('ajax/ajax_2fa.php?action=verify_reauth', { method:'POST', body:fd });
    const dv = await rv.json();
    if (dv.status !== 'success') {
      err.textContent = dv.message || 'Invalid code';
      shakeDigits('agSaveDigits'); clearDigits('agSaveDigits');
      digits('agSaveDigits')[0].focus();
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-floppy2-fill"></i>Verify &amp; Save';
      return;
    }

    // Reauth passed — now do the actual save
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:14px;height:14px;border-width:2px"></span> Saving…';
    const rs = await fetch('ajax/ajax_admin_payment_settings.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action:'save', ..._pendingSavePayload })
    });
    const ds = await rs.json();

    agCloseSaveOverlay();
    psToast(ds.message || (ds.status === 'ok' ? 'Saved' : 'Save failed'), ds.status === 'ok');
    if (ds.status === 'ok') {
      document.getElementById('psApiSecret').value = '';
      document.getElementById('psApiSecret').placeholder = '••••••••••••••••••••';
      psStartTimer(TTL);
    }
  } catch {
    err.textContent = 'Network error — please try again';
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-floppy2-fill"></i>Verify &amp; Save';
  }
}

/* ─── Session timer ───────────────────────────────────── */
let _timerInterval = null;

function psStartTimer(remaining) {
  clearInterval(_timerInterval);
  const fill = document.getElementById('psTimerFill');
  const lbl  = document.getElementById('psTimerLbl');
  if (!fill || !lbl) return;

  function tick() {
    if (remaining <= 0) {
      clearInterval(_timerInterval);
      lbl.textContent = 'Session expired — re-authentication required';
      fill.style.width = '0%';
      // Re-show lock overlay
      buildDigits('agLockDigits');
      document.getElementById('agLockErr').textContent = '';
      document.getElementById('agStep1').className = 'ag-step active';
      document.getElementById('agStep1').querySelector('.ag-step-dot').innerHTML = '<i class="bi bi-shield-lock" style="font-size:.7rem"></i>';
      document.getElementById('agStep2').className = 'ag-step';
      document.getElementById('agStep2').querySelector('.ag-step-dot').textContent = '2';
      document.getElementById('agLockBtn').disabled = false;
      document.getElementById('agLockBtn').innerHTML = '<i class="bi bi-unlock-fill"></i>Verify &amp; Unlock';
      document.getElementById('psRoot').style.filter = 'blur(3px)';
      document.getElementById('psRoot').style.pointerEvents = 'none';
      document.getElementById('agLockOverlay').classList.add('show');
      return;
    }
    const pct = (remaining / TTL) * 100;
    fill.style.width = pct + '%';
    const m = Math.floor(remaining / 60);
    const s = remaining % 60;
    lbl.textContent = `Session expires in ${m}:${String(s).padStart(2,'0')}`;
    remaining--;
  }
  tick();
  _timerInterval = setInterval(tick, 1000);
}

// Start timer if already granted
<?php if ($granted && $stampTs): ?>
psStartTimer(Math.max(0, <?= TTL - (time() - $stampTs) ?>));
<?php endif; ?>

/* ─── Settings helpers ────────────────────────────────── */
function psToggleSecret() {
  const inp = document.getElementById('psApiSecret');
  const btn = document.getElementById('psRevealBtn');
  const show = inp.type === 'password';
  inp.type = show ? 'text' : 'password';
  btn.innerHTML = show ? '<i class="bi bi-eye-slash me-1"></i>Hide' : '<i class="bi bi-eye me-1"></i>Show';
}

document.getElementById('psIsActive').addEventListener('change', function () {
  const b = document.getElementById('psStatusBadge');
  b.className = 'ps-status-pill ' + (this.checked ? 'on' : 'off');
  b.innerHTML = `<span class="ps-status-dot"></span>${this.checked ? 'Active' : 'Inactive'}`;
});

function psGetBaseUrl() {
  let v = document.getElementById('psBaseUrl').value.trim();
  if (!v.startsWith('http')) v = 'https://' + v;
  return v.replace(/\/$/, '');
}

function psSave() {
  const vendor  = document.getElementById('psVendor').value.trim();
  const apiKey  = document.getElementById('psApiKey').value.trim();
  const secret  = document.getElementById('psApiSecret').value.trim();
  const baseUrl = psGetBaseUrl();
  const webhook = document.getElementById('psWebhookUrl').value.trim();
  const active  = document.getElementById('psIsActive').checked;

  if (!vendor)  { psToast('Vendor / Till number is required', false); return; }
  if (!apiKey)  { psToast('API Key is required', false); return; }
  if (!webhook) { psToast('Webhook URL is required', false); return; }

  // Gate 2: require re-auth before committing
  agOpenSaveOverlay({ vendor, api_key:apiKey, api_secret:secret,
                      base_url:baseUrl, webhook_url:webhook, is_active:active });
}

async function psTest() {
  const box = document.getElementById('psTestResult');
  const msg = document.getElementById('psTestMsg');
  box.className = 'ps-test-result show';
  box.querySelector('i').className = 'bi bi-hourglass-split';
  msg.textContent = 'Testing connection to Selcom…';

  const btn = document.getElementById('psTestBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:13px;height:13px;border-width:2px"></span> Testing…';

  try {
    const r = await fetch('ajax/ajax_admin_payment_settings.php?action=test');
    const d = await r.json();
    if (d.status === 'reauth_required') {
      box.className = 'ps-test-result show err';
      box.querySelector('i').className = 'bi bi-shield-exclamation';
      msg.textContent = 'Session expired — please re-authenticate first';
    } else {
      const ok = d.status === 'ok';
      box.className = 'ps-test-result show ' + (ok ? 'ok' : 'err');
      box.querySelector('i').className = 'bi bi-' + (ok ? 'check-circle-fill' : 'x-circle-fill');
      msg.textContent = d.message || (ok ? 'Connected' : 'Failed');
    }
  } catch {
    box.className = 'ps-test-result show err';
    box.querySelector('i').className = 'bi bi-x-circle-fill';
    msg.textContent = 'Network error — could not reach server';
  }

  btn.disabled = false;
  btn.innerHTML = '<i class="bi bi-wifi"></i>Test Connection';
}

function psToast(msg, ok) {
  if (typeof Swal !== 'undefined') {
    Swal.fire({ icon:ok?'success':'error', title:msg, timer:3500, showConfirmButton:false, toast:true, position:'top-end' });
    return;
  }
  const t = document.createElement('div');
  t.style.cssText = `position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;padding:.75rem 1.25rem;
    border-radius:12px;font-size:.84rem;font-weight:600;color:#fff;font-family:inherit;
    background:${ok?'#059669':'#dc2626'};box-shadow:0 4px 20px rgba(0,0,0,.2)`;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 3500);
}

Object.assign(window, { psSave, psTest, psToggleSecret, agVerifyLock, agVerifySave, agCloseSaveOverlay });

})();
</script>
