<?php
$_usr = $_SESSION['usr_code'] ?? '';
?>
<style>
/* ═══════════════════════════════════════════════════════
   CHECKOUT PAGE  (cp-*)
═══════════════════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; }

.cp-root {
  font-family: 'Inter', 'Open Sans', sans-serif;
  background: #f1f5f9;
  min-height: 100vh;
  padding-bottom: 3rem;
}

/* ── Hero bar ─────────────────────────────────────────── */
.cp-hero {
  background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
  padding: 1.75rem 2rem 1.75rem;
  position: relative;
  overflow: hidden;
}
.cp-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.cp-hero-inner {
  position: relative;
  max-width: 1100px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}
.cp-back {
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  font-size: .78rem;
  font-weight: 600;
  color: rgba(255,255,255,.75);
  text-decoration: none;
  padding: .35rem .85rem;
  border-radius: 8px;
  border: 1px solid rgba(255,255,255,.2);
  backdrop-filter: blur(6px);
  transition: all .15s;
}
.cp-back:hover { background: rgba(255,255,255,.15); color: #fff; }
.cp-hero-title {
  color: #fff;
  font-size: 1.35rem;
  font-weight: 800;
  font-family: 'SUSE', 'Inter', sans-serif;
  letter-spacing: -.02em;
}
.cp-hero-sub { color: rgba(255,255,255,.6); font-size: .78rem; margin-top: .15rem; }
.cp-hero-steps {
  display: flex;
  align-items: center;
  gap: .5rem;
  font-size: .72rem;
}
.cp-step {
  display: flex;
  align-items: center;
  gap: .35rem;
  color: rgba(255,255,255,.5);
}
.cp-step.done { color: rgba(255,255,255,.9); }
.cp-step-dot {
  width: 22px; height: 22px;
  border-radius: 50%;
  background: rgba(255,255,255,.15);
  border: 1.5px solid rgba(255,255,255,.3);
  display: flex; align-items: center; justify-content: center;
  font-size: .65rem; font-weight: 700; color: rgba(255,255,255,.6);
}
.cp-step.done .cp-step-dot {
  background: #818cf8;
  border-color: #818cf8;
  color: #fff;
}
.cp-step-sep { color: rgba(255,255,255,.25); }

/* ── Main canvas ──────────────────────────────────────── */
.cp-canvas {
  max-width: 1100px;
  margin: 1.5rem auto 0;
  padding: 0 1.25rem;
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 1.5rem;
  align-items: start;
}
@media (max-width: 900px) { .cp-canvas { grid-template-columns: 1fr; margin-top: -1.5rem; } }

/* ── Card shell ───────────────────────────────────────── */
.cp-card {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 4px 24px rgba(0,0,0,.07), 0 1px 4px rgba(0,0,0,.04);
  overflow: hidden;
}
.cp-card-head {
  padding: 1rem 1.4rem;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.cp-card-title {
  font-size: .82rem;
  font-weight: 800;
  color: #1e293b;
  text-transform: uppercase;
  letter-spacing: .05em;
  display: flex;
  align-items: center;
  gap: .5rem;
}
.cp-card-title i { font-size: 1rem; }

/* ── Cart item ────────────────────────────────────────── */
.cp-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.4rem;
  border-bottom: 1px solid #f8fafc;
  transition: background .12s;
}
.cp-item:last-child { border-bottom: none; }
.cp-item:hover { background: #fafbff; }
.cp-thumb-wrap {
  position: relative;
  flex-shrink: 0;
}
.cp-item-thumb {
  width: 80px; height: 58px;
  border-radius: 10px;
  object-fit: cover;
  background: #f1f5f9;
  display: block;
}
.cp-thumb-play {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,.35);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity .15s;
}
.cp-item:hover .cp-thumb-play { opacity: 1; }
.cp-thumb-play i { color: #fff; font-size: .9rem; }
.cp-item-body { flex: 1; min-width: 0; }
.cp-item-title {
  font-size: .84rem;
  font-weight: 700;
  color: #0f172a;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  line-clamp: 2;
  line-height: 1.35;
  margin-bottom: .25rem;
}
.cp-item-meta { font-size: .7rem; color: #94a3b8; display: flex; align-items: center; gap: .65rem; }
.cp-item-meta i { font-size: .72rem; }
.cp-item-right { flex-shrink: 0; text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: .3rem; }
.cp-item-final { font-size: .92rem; font-weight: 800; color: #1e293b; }
.cp-item-orig  { font-size: .72rem; color: #94a3b8; text-decoration: line-through; }
.cp-disc-pill {
  font-size: .62rem; font-weight: 800;
  background: #ecfdf5; color: #059669;
  border: 1px solid #a7f3d0;
  border-radius: 100px;
  padding: .1rem .5rem;
}
.cp-remove-btn {
  width: 28px; height: 28px;
  border-radius: 8px;
  border: 1px solid #fee2e2;
  background: #fff5f5;
  color: #dc2626;
  font-size: .75rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .12s;
  margin-top: .15rem;
}
.cp-remove-btn:hover { background: #dc2626; color: #fff; border-color: #dc2626; }

/* ── Empty state ──────────────────────────────────────── */
.cp-empty {
  padding: 3.5rem 2rem;
  text-align: center;
}
.cp-empty-icon {
  width: 80px; height: 80px;
  border-radius: 50%;
  background: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.25rem;
  font-size: 2rem;
  color: #cbd5e1;
}
.cp-empty h4 { font-size: 1rem; font-weight: 800; color: #475569; margin-bottom: .4rem; }
.cp-empty p  { font-size: .8rem; color: #94a3b8; margin-bottom: 1.5rem; }
.cp-empty-btn {
  display: inline-flex;
  align-items: center;
  gap: .45rem;
  padding: .55rem 1.25rem;
  border-radius: 10px;
  background: #4f46e5;
  color: #fff;
  font-size: .82rem;
  font-weight: 700;
  text-decoration: none;
  transition: all .15s;
}
.cp-empty-btn:hover { background: #4338ca; color: #fff; }

/* ── Skeleton ─────────────────────────────────────────── */
.cp-skel {
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: cpShim 1.5s infinite;
  border-radius: 8px;
}
@keyframes cpShim { 0%{background-position:200%}100%{background-position:-200%} }

/* ── Right panel ──────────────────────────────────────── */
.cp-right { position: sticky; top: 1.25rem; display: flex; flex-direction: column; gap: 1rem; }

/* Order summary */
.cp-summary-rows { padding: 1.1rem 1.4rem; border-bottom: 1px solid #f1f5f9; }
.cp-summ-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: .82rem;
  color: #64748b;
  padding: .3rem 0;
}
.cp-summ-row.disc { color: #059669; font-weight: 600; }
.cp-summ-row.total {
  font-size: 1.05rem;
  font-weight: 900;
  color: #0f172a;
  border-top: 2px solid #f1f5f9;
  margin-top: .4rem;
  padding-top: .75rem;
}
.cp-summ-row.total .val { color: #4f46e5; }

/* Payment methods */
.cp-pay-section { padding: 1.1rem 1.4rem; }
.cp-section-label {
  font-size: .68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .08em;
  color: #94a3b8;
  margin-bottom: .75rem;
}
.cp-methods { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; margin-bottom: 1.1rem; }
.cp-method {
  border: 2px solid #e2e8f0;
  border-radius: 14px;
  padding: .9rem .75rem;
  cursor: pointer;
  text-align: center;
  background: #fafafa;
  transition: all .15s;
  position: relative;
}
.cp-method:hover { border-color: #818cf8; background: #f5f3ff; }
.cp-method.sel {
  border-color: #4f46e5;
  background: #ede9fe;
  box-shadow: 0 0 0 3px rgba(79,70,229,.12);
}
.cp-method-check {
  position: absolute;
  top: 7px; right: 8px;
  width: 17px; height: 17px;
  border-radius: 50%;
  background: #4f46e5;
  color: #fff;
  font-size: .6rem;
  display: none;
  align-items: center;
  justify-content: center;
}
.cp-method.sel .cp-method-check { display: flex; }
.cp-method-ico { font-size: 1.5rem; display: block; margin-bottom: .35rem; line-height: 1; }
.cp-method-name { font-size: .75rem; font-weight: 800; color: #1e293b; }
.cp-method-sub  { font-size: .62rem; color: #94a3b8; margin-top: .1rem; }

/* Card logos row */
.cp-card-logos {
  display: flex;
  gap: .4rem;
  align-items: center;
  margin-bottom: .9rem;
  flex-wrap: wrap;
}
.cp-logo-pill {
  border: 1px solid #e2e8f0;
  border-radius: 7px;
  padding: .22rem .55rem;
  font-size: .62rem;
  font-weight: 800;
  color: #475569;
  background: #f8fafc;
  letter-spacing: .02em;
}
.cp-logo-pill.visa { color: #1434CB; border-color: #1434CB; background: #eff6ff; }
.cp-logo-pill.mc   { color: #eb001b; border-color: #f9a31b; background: #fff7ed; }

/* MNO wallets */
.cp-wallets {
  display: flex;
  gap: .35rem;
  flex-wrap: wrap;
  margin-bottom: .9rem;
}
.cp-wallet-pill {
  display: flex;
  align-items: center;
  gap: .3rem;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: .22rem .7rem;
  font-size: .65rem;
  font-weight: 700;
  color: #475569;
  background: #f8fafc;
}
.cp-wallet-pill .dot {
  width: 7px; height: 7px;
  border-radius: 50%;
  flex-shrink: 0;
}

/* Phone panel */
.cp-phone-panel { display: none; margin-bottom: .9rem; }
.cp-phone-panel.show { display: block; }
.cp-phone-wrap {
  display: flex;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
  transition: border-color .15s;
  background: #fff;
}
.cp-phone-wrap:focus-within { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.1); }
.cp-phone-prefix {
  padding: 0 .9rem;
  background: #f8fafc;
  border-right: 2px solid #e2e8f0;
  font-size: .8rem;
  font-weight: 700;
  color: #334155;
  display: flex;
  align-items: center;
  gap: .35rem;
  white-space: nowrap;
  flex-shrink: 0;
}
.cp-phone-inp {
  flex: 1;
  border: none;
  outline: none;
  padding: .7rem .85rem;
  font-size: .88rem;
  font-family: 'Inter', monospace;
  letter-spacing: .04em;
  color: #0f172a;
  background: transparent;
  min-width: 0;
}
.cp-phone-inp::placeholder { color: #cbd5e1; letter-spacing: 0; }
.cp-network-hint {
  font-size: .7rem;
  color: #64748b;
  margin-top: .5rem;
  display: flex;
  align-items: center;
  gap: .35rem;
}
.cp-network-detected {
  display: inline-flex;
  align-items: center;
  gap: .3rem;
  font-weight: 700;
  color: #059669;
}

/* Pay button */
.cp-pay-btn {
  width: 100%;
  padding: .9rem 1rem;
  border-radius: 13px;
  border: none;
  cursor: pointer;
  font-size: .92rem;
  font-weight: 800;
  font-family: inherit;
  letter-spacing: -.01em;
  background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
  color: #fff;
  box-shadow: 0 4px 20px rgba(79,70,229,.35), 0 1px 0 rgba(255,255,255,.15) inset;
  transition: all .18s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .55rem;
  margin-bottom: .9rem;
}
.cp-pay-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 8px 28px rgba(79,70,229,.45), 0 1px 0 rgba(255,255,255,.15) inset;
}
.cp-pay-btn:active:not(:disabled) { transform: translateY(0); }
.cp-pay-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; box-shadow: none; }
.cp-pay-btn.mno {
  background: linear-gradient(135deg, #059669 0%, #10b981 100%);
  box-shadow: 0 4px 20px rgba(5,150,105,.35), 0 1px 0 rgba(255,255,255,.15) inset;
}
.cp-pay-btn.mno:hover:not(:disabled) {
  box-shadow: 0 8px 28px rgba(5,150,105,.45), 0 1px 0 rgba(255,255,255,.15) inset;
}

/* Trust row */
.cp-trust {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .4rem;
  font-size: .68rem;
  color: #94a3b8;
  margin-bottom: .7rem;
}
.cp-trust i { color: #059669; }

.cp-powered {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .4rem;
  font-size: .68rem;
  color: #cbd5e1;
  padding: .6rem 0 .1rem;
  border-top: 1px solid #f1f5f9;
}
.cp-powered strong { color: #7c3aed; }

/* ── MNO overlay ──────────────────────────────────────── */
.cp-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, .7);
  z-index: 9999;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
}
.cp-overlay.show { display: flex; }
.cp-overlay-box {
  background: #fff;
  border-radius: 24px;
  padding: 2.5rem 2rem;
  max-width: 420px;
  width: 92%;
  text-align: center;
  box-shadow: 0 32px 80px rgba(0,0,0,.25);
  animation: cpSlideUp .3s cubic-bezier(.34,1.56,.64,1);
}
@keyframes cpSlideUp { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }
.cp-pulse-ring {
  width: 90px; height: 90px;
  border-radius: 50%;
  background: #ecfdf5;
  border: 3px solid #10b981;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.25rem;
  animation: cpPulse 1.6s ease-in-out infinite;
  font-size: 2.2rem;
}
@keyframes cpPulse {
  0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16,185,129,.4); }
  50% { transform: scale(1.05); box-shadow: 0 0 0 12px rgba(16,185,129,0); }
}
.cp-overlay-box h4 { font-size: 1.05rem; font-weight: 900; color: #0f172a; margin-bottom: .5rem; }
.cp-overlay-box p  { font-size: .82rem; color: #64748b; margin-bottom: .35rem; line-height: 1.55; }
.cp-overlay-warn   { font-size: .72rem; color: #f59e0b; font-weight: 700; margin-bottom: 1.5rem;
                      background: #fffbeb; border-radius: 8px; padding: .5rem .85rem; display: inline-block; }
.cp-cancel-btn {
  width: 100%;
  padding: .7rem;
  border-radius: 12px;
  border: 2px solid #e2e8f0;
  background: #f8fafc;
  color: #64748b;
  font-size: .8rem;
  font-weight: 700;
  font-family: inherit;
  cursor: pointer;
  transition: all .15s;
}
.cp-cancel-btn:hover { border-color: #dc2626; color: #dc2626; background: #fff5f5; }

/* ── Clear all btn ────────────────────────────────────── */
.cp-clear-btn {
  display: none;
  align-items: center;
  gap: .35rem;
  font-size: .72rem;
  font-weight: 700;
  color: #dc2626;
  padding: .3rem .7rem;
  border-radius: 8px;
  border: 1px solid #fee2e2;
  background: #fff5f5;
  cursor: pointer;
  transition: all .14s;
}
.cp-clear-btn:hover { background: #dc2626; color: #fff; border-color: #dc2626; }
</style>

<div class="cp-root">

  <!-- ── Hero ───────────────────────────────────────────── -->
  <div class="cp-hero">
    <div class="cp-hero-inner">
      <div>
        <a href="?view=3002" class="cp-back"><i class="bi bi-arrow-left"></i>Back to Dashboard</a>
        <div class="cp-hero-title" style="margin-top:.75rem">Checkout</div>
        <div class="cp-hero-sub">Review your courses and complete payment securely</div>
      </div>
      <div class="cp-hero-steps">
        <div class="cp-step done">
          <div class="cp-step-dot"><i class="bi bi-check2" style="font-size:.7rem"></i></div>
          <span>Cart</span>
        </div>
        <div class="cp-step-sep">›</div>
        <div class="cp-step done">
          <div class="cp-step-dot">2</div>
          <span>Payment</span>
        </div>
        <div class="cp-step-sep">›</div>
        <div class="cp-step">
          <div class="cp-step-dot">3</div>
          <span>Enrolment</span>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Canvas ─────────────────────────────────────────── -->
  <div class="cp-canvas">

    <!-- Left: Course Items -->
    <div>
      <div class="cp-card">
        <div class="cp-card-head">
          <div class="cp-card-title">
            <i class="bi bi-bag-check" style="color:#4f46e5"></i>
            Order Items <span id="cpCountLbl" style="color:#94a3b8;font-weight:600;text-transform:none;letter-spacing:0;font-size:.75rem"></span>
          </div>
          <button class="cp-clear-btn" id="cpClearBtn" onclick="cpClearAll()">
            <i class="bi bi-trash3"></i>Clear all
          </button>
        </div>
        <div id="cpItemsList">
          <?php for ($i = 0; $i < 3; $i++): ?>
          <div class="cp-item">
            <div class="cp-skel" style="width:80px;height:58px;border-radius:10px;flex-shrink:0"></div>
            <div style="flex:1">
              <div class="cp-skel" style="height:13px;width:70%;margin-bottom:7px"></div>
              <div class="cp-skel" style="height:10px;width:42%"></div>
            </div>
            <div class="cp-skel" style="width:70px;height:22px;border-radius:100px"></div>
          </div>
          <?php endfor; ?>
        </div>
      </div>

      <!-- Guarantee strip -->
      <div style="display:flex;gap:1.5rem;padding:1rem .25rem;flex-wrap:wrap">
        <?php foreach ([
          ['bi-arrow-counterclockwise','30-day money-back guarantee'],
          ['bi-infinity','Lifetime course access'],
          ['bi-patch-check','Verified instructors'],
        ] as [$ic, $tx]): ?>
        <div style="display:flex;align-items:center;gap:.4rem;font-size:.72rem;color:#64748b">
          <i class="bi <?= $ic ?>" style="color:#4f46e5;font-size:.88rem"></i><?= $tx ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Right: Summary + Payment -->
    <div class="cp-right">

      <!-- Order summary card -->
      <div class="cp-card">
        <div class="cp-card-head">
          <div class="cp-card-title">
            <i class="bi bi-receipt" style="color:#059669"></i>Order Summary
          </div>
        </div>
        <div class="cp-summary-rows">
          <div class="cp-summ-row"><span>Subtotal</span><span id="cpSubtotal">—</span></div>
          <div class="cp-summ-row disc" id="cpDiscRow" style="display:none">
            <span><i class="bi bi-tag-fill me-1" style="font-size:.7rem"></i>Discount</span>
            <span id="cpDiscount">—</span>
          </div>
          <div class="cp-summ-row total"><span>Total due</span><span class="val" id="cpTotal">—</span></div>
        </div>

        <!-- Payment section -->
        <div class="cp-pay-section">

          <div class="cp-section-label">Payment Method</div>

          <div class="cp-methods">
            <div class="cp-method sel" id="cpMethodCard" onclick="cpSelectMethod('CARD')" role="button">
              <div class="cp-method-check"><i class="bi bi-check2" style="font-size:.65rem"></i></div>
              <span class="cp-method-ico">💳</span>
              <div class="cp-method-name">Card</div>
              <div class="cp-method-sub">Visa / Mastercard</div>
            </div>
            <div class="cp-method" id="cpMethodMNO" onclick="cpSelectMethod('MNO')" role="button">
              <div class="cp-method-check"><i class="bi bi-check2" style="font-size:.65rem"></i></div>
              <span class="cp-method-ico">📱</span>
              <div class="cp-method-name">Mobile Money</div>
              <div class="cp-method-sub">M-Pesa · Airtel · Tigo</div>
            </div>
          </div>

          <!-- Card logos (shown for CARD) -->
          <div id="cpCardLogos" class="cp-card-logos">
            <span class="cp-logo-pill visa">VISA</span>
            <span class="cp-logo-pill mc">MC</span>
            <span class="cp-logo-pill">Maestro</span>
            <span class="cp-logo-pill">UnionPay</span>
          </div>

          <!-- Wallet pills (shown for MNO) -->
          <div id="cpWallets" class="cp-wallets" style="display:none">
            <span class="cp-wallet-pill"><span class="dot" style="background:#e11d48"></span>M-Pesa</span>
            <span class="cp-wallet-pill"><span class="dot" style="background:#e6a817"></span>Airtel</span>
            <span class="cp-wallet-pill"><span class="dot" style="background:#0059a1"></span>Tigo</span>
            <span class="cp-wallet-pill"><span class="dot" style="background:#6d28d9"></span>Halopesa</span>
            <span class="cp-wallet-pill"><span class="dot" style="background:#0891b2"></span>Azampesa</span>
          </div>

          <!-- Phone input (MNO) -->
          <div class="cp-phone-panel" id="cpPhonePanel">
            <div class="cp-phone-wrap">
              <div class="cp-phone-prefix">🇹🇿 +255</div>
              <input type="tel" class="cp-phone-inp" id="cpPhone"
                     placeholder="7XX XXX XXX" maxlength="9" inputmode="numeric"
                     autocomplete="tel">
            </div>
            <div class="cp-network-hint">
              <i class="bi bi-info-circle" style="font-size:.75rem"></i>
              <span id="cpNetworkHint">Enter the number linked to your mobile wallet</span>
            </div>
          </div>

          <!-- Pay button -->
          <button class="cp-pay-btn" id="cpPayBtn" onclick="cpCheckout()" disabled>
            <i class="bi bi-lock-fill" style="font-size:.85rem"></i>
            <span id="cpPayLabel">Pay Now</span>
          </button>

          <div class="cp-trust">
            <i class="bi bi-shield-check-fill"></i>
            <span>256-bit SSL encrypted · PCI DSS compliant</span>
          </div>

          <div class="cp-powered">
            <i class="bi bi-lightning-charge-fill" style="color:#f59e0b;font-size:.75rem"></i>
            Powered by <strong>Selcom Mobile</strong>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- MNO waiting overlay -->
<div class="cp-overlay" id="cpMnoOverlay">
  <div class="cp-overlay-box">
    <div class="cp-pulse-ring">📲</div>
    <h4>Check Your Phone</h4>
    <p id="cpMnoMsg">A payment prompt has been sent to your mobile number. Please approve it to complete your enrolment.</p>
    <div class="cp-overlay-warn">
      <i class="bi bi-exclamation-triangle-fill me-1"></i>Do not close this window
    </div>
    <button class="cp-cancel-btn" onclick="cpCloseMnoOverlay()">
      <i class="bi bi-x-circle me-1"></i>Cancel — Use a different method
    </button>
  </div>
</div>

<script>
(function () {

let cpCart      = [];
let cpMethod    = 'CARD';
let cpSubV      = 0;
let cpDiscV     = 0;
let cpTotalV    = 0;

/* ── Init ──────────────────────────────────────────────── */
function _cpInit() { cpLoadCart(); }
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', _cpInit);
} else { _cpInit(); }

/* ── Load cart ─────────────────────────────────────────── */
function cpLoadCart() {
  fetch('ajax/ajax_fetch_cart.php')
    .then(r => r.json())
    .then(res => { cpCart = res.data || []; cpRender(); cpSummary(); })
    .catch(() => {
      document.getElementById('cpItemsList').innerHTML =
        '<div style="padding:2.5rem;text-align:center;color:#94a3b8;font-size:.85rem"><i class="bi bi-wifi-off" style="font-size:2rem;display:block;margin-bottom:.75rem"></i>Could not load cart items</div>';
    });
}

/* ── Render items ──────────────────────────────────────── */
function cpRender() {
  const list    = document.getElementById('cpItemsList');
  const payBtn  = document.getElementById('cpPayBtn');
  const clearBtn = document.getElementById('cpClearBtn');
  const countLbl = document.getElementById('cpCountLbl');

  countLbl.textContent = cpCart.length ? `(${cpCart.length} item${cpCart.length > 1 ? 's' : ''})` : '';

  if (!cpCart.length) {
    clearBtn.style.display = 'none';
    list.innerHTML = `<div class="cp-empty">
      <div class="cp-empty-icon"><i class="bi bi-cart-x"></i></div>
      <h4>Your cart is empty</h4>
      <p>Add courses from the dashboard to get started on your learning journey.</p>
      <a href="?view=3002" class="cp-empty-btn"><i class="bi bi-compass"></i>Browse Courses</a>
    </div>`;
    payBtn.disabled = true;
    return;
  }

  clearBtn.style.display = 'inline-flex';

  list.innerHTML = cpCart.map(c => {
    const price = parseFloat(c.price  || 0);
    const disc  = parseFloat(c.discount || 0);
    const final = Math.round(price - (price * disc / 100));
    const thumb = cpEsc(c.thumbnail || 'uploads/course_default.png');
    const discBadge = disc > 0 ? `<span class="cp-disc-pill">${disc}% off</span>` : '';
    const origPrice = disc > 0 ? `<div class="cp-item-orig">TZS ${cpFmt(Math.round(price))}</div>` : '';
    return `<div class="cp-item" id="cpItem_${c.id}">
      <div class="cp-thumb-wrap">
        <img class="cp-item-thumb" src="${thumb}" alt="" onerror="this.src='uploads/course_default.png'">
        <div class="cp-thumb-play"><i class="bi bi-play-circle-fill"></i></div>
      </div>
      <div class="cp-item-body">
        <div class="cp-item-title">${cpEsc(c.title || 'Course')}</div>
        <div class="cp-item-meta">
          <span><i class="bi bi-play-circle"></i> Online Course</span>
          <span><i class="bi bi-infinity"></i> Lifetime access</span>
        </div>
      </div>
      <div class="cp-item-right">
        <div class="cp-item-final">TZS ${cpFmt(final)}</div>
        ${origPrice}
        ${discBadge}
        <button class="cp-remove-btn" onclick="cpRemove(${c.id})" title="Remove from cart">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
    </div>`;
  }).join('');
}

/* ── Summary ───────────────────────────────────────────── */
function cpSummary() {
  cpSubV = cpCart.reduce((s, c) => s + parseFloat(c.price || 0), 0);
  const finalTotal = cpCart.reduce((s, c) => {
    const p = parseFloat(c.price || 0);
    const d = parseFloat(c.discount || 0);
    return s + (p - (p * d / 100));
  }, 0);
  cpDiscV  = cpSubV - finalTotal;
  cpTotalV = Math.ceil(finalTotal);

  document.getElementById('cpSubtotal').textContent = 'TZS ' + cpFmt(Math.round(cpSubV));
  document.getElementById('cpTotal').textContent    = 'TZS ' + cpFmt(cpTotalV);

  const discRow = document.getElementById('cpDiscRow');
  if (cpDiscV > 0.5) {
    discRow.style.display = 'flex';
    document.getElementById('cpDiscount').textContent = '− TZS ' + cpFmt(Math.round(cpDiscV));
  } else {
    discRow.style.display = 'none';
  }

  const payBtn = document.getElementById('cpPayBtn');
  if (cpCart.length) {
    document.getElementById('cpPayLabel').textContent = `Pay TZS ${cpFmt(cpTotalV)}`;
  }
  cpUpdatePayBtn();
}

/* ── Remove ────────────────────────────────────────────── */
function cpRemove(id) {
  const el = document.getElementById('cpItem_' + id);
  if (el) { el.style.opacity = '.35'; el.style.pointerEvents = 'none'; }
  fetch('ajax/ajax_remove_cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'course_id=' + id
  }).then(() => {
    cpCart = cpCart.filter(c => c.id != id);
    cpRender(); cpSummary();
  }).catch(() => {
    if (el) { el.style.opacity = '1'; el.style.pointerEvents = ''; }
  });
}

function cpClearAll() {
  if (!cpCart.length) return;
  const ids = cpCart.map(c => c.id);
  Promise.all(ids.map(id =>
    fetch('ajax/ajax_remove_cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'course_id=' + id
    })
  )).then(() => { cpCart = []; cpRender(); cpSummary(); });
}

/* ── Method select ─────────────────────────────────────── */
function cpSelectMethod(m) {
  cpMethod = m;
  document.getElementById('cpMethodCard').classList.toggle('sel', m === 'CARD');
  document.getElementById('cpMethodMNO').classList.toggle('sel',  m === 'MNO');
  document.getElementById('cpCardLogos').style.display  = m === 'CARD' ? 'flex' : 'none';
  document.getElementById('cpWallets').style.display    = m === 'MNO'  ? 'flex' : 'none';
  document.getElementById('cpPhonePanel').classList.toggle('show', m === 'MNO');
  const btn = document.getElementById('cpPayBtn');
  btn.className = 'cp-pay-btn' + (m === 'MNO' ? ' mno' : '');
  cpUpdatePayBtn();
}

/* ── Phone network detection ───────────────────────────── */
const cpNetworks = [
  { prefix: ['74','75','76'], name: 'M-Pesa (Vodacom)', color: '#e11d48' },
  { prefix: ['68','69','78'], name: 'Airtel Money',     color: '#e6a817' },
  { prefix: ['71','65','67'], name: 'Tigo Pesa',        color: '#0059a1' },
  { prefix: ['62','61'],      name: 'Halopesa',          color: '#6d28d9' },
  { prefix: ['77','79'],      name: 'Azampesa',          color: '#0891b2' },
];
function cpDetectNetwork(num) {
  const s = num.replace(/\D/g, '').slice(0, 2);
  return cpNetworks.find(n => n.prefix.some(p => s.startsWith(p))) || null;
}

document.addEventListener('input', e => {
  if (e.target.id !== 'cpPhone') return;
  e.target.value = e.target.value.replace(/\D/g, '').slice(0, 9);
  const hint    = document.getElementById('cpNetworkHint');
  const network = cpDetectNetwork(e.target.value);
  if (network && e.target.value.length >= 2) {
    hint.innerHTML = `Detected: <span class="cp-network-detected">
      <span style="width:8px;height:8px;border-radius:50%;background:${network.color};display:inline-block"></span>
      ${network.name}
    </span>`;
  } else {
    hint.textContent = 'Enter the number linked to your mobile wallet';
  }
  cpUpdatePayBtn();
});

function cpUpdatePayBtn() {
  const btn = document.getElementById('cpPayBtn');
  if (!cpCart.length) { btn.disabled = true; return; }
  if (cpMethod === 'MNO') {
    const ph = (document.getElementById('cpPhone')?.value || '').replace(/\D/g, '');
    btn.disabled = ph.length < 9;
  } else {
    btn.disabled = false;
  }
}

/* ── Checkout ──────────────────────────────────────────── */
function cpCheckout() {
  if (!cpCart.length) return;
  const phone = cpMethod === 'MNO'
    ? (document.getElementById('cpPhone')?.value || '').replace(/\D/g, '')
    : '';
  if (cpMethod === 'MNO' && phone.length < 9) {
    cpToast('Please enter a valid 9-digit phone number', 'warning'); return;
  }

  const btn = document.getElementById('cpPayBtn');
  btn.disabled = true;
  btn.innerHTML = `<span class="spinner-border spinner-border-sm" style="width:16px;height:16px;border-width:2px"></span><span>Processing…</span>`;

  fetch('ajax/ajax_checkout.php', {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({ method: cpMethod, phone })
  })
  .then(r => r.json())
  .then(res => {
    if (res.status === 'success' && res.method === 'CARD' && res.redirect_url) {
      btn.innerHTML = `<i class="bi bi-arrow-right-circle-fill"></i><span>Redirecting to payment…</span>`;
      window.location.href = res.redirect_url;
      return;
    }
    if (res.status === 'pending' && res.method === 'MNO') {
      document.getElementById('cpMnoMsg').textContent = res.message || 'Approve the payment prompt on your phone.';
      document.getElementById('cpMnoOverlay').classList.add('show');
      return;
    }
    cpToast(res.message || 'Payment failed. Please try again.', 'error');
    btn.disabled = false;
    document.getElementById('cpPayLabel').textContent = `Pay TZS ${cpFmt(cpTotalV)}`;
    btn.innerHTML = `<i class="bi bi-lock-fill" style="font-size:.85rem"></i><span id="cpPayLabel">Pay TZS ${cpFmt(cpTotalV)}</span>`;
  })
  .catch(() => {
    cpToast('Network error — please check your connection and try again.', 'error');
    btn.disabled = false;
    btn.innerHTML = `<i class="bi bi-lock-fill" style="font-size:.85rem"></i><span>Pay TZS ${cpFmt(cpTotalV)}</span>`;
  });
}

function cpCloseMnoOverlay() {
  document.getElementById('cpMnoOverlay').classList.remove('show');
  const btn = document.getElementById('cpPayBtn');
  btn.disabled = false;
  btn.innerHTML = `<i class="bi bi-lock-fill" style="font-size:.85rem"></i><span>Pay TZS ${cpFmt(cpTotalV)}</span>`;
}

/* ── Helpers ───────────────────────────────────────────── */
function cpFmt(n) { return Number(n).toLocaleString('en'); }
function cpEsc(s) {
  return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function cpToast(msg, type) {
  if (typeof Swal !== 'undefined') {
    Swal.fire({ icon: type || 'info', title: msg, timer: 3500, showConfirmButton: false, toast: true, position: 'top-end' });
  } else { alert(msg); }
}

Object.assign(window, { cpSelectMethod, cpRemove, cpClearAll, cpCheckout, cpCloseMnoOverlay });

})();
</script>
