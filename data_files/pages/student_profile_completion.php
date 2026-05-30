<?php
/* ── Load student record ───────────────────────────────────────── */
$_usr = $_SESSION['usr_code'] ?? '';
$_st  = null;
if ($_usr) {
    $_sq = $db->prepare("SELECT * FROM tbl_students WHERE usr_code = ?");
    $_sq->bind_param('s', $_usr);
    $_sq->execute();
    $_st = $_sq->get_result()->fetch_assoc();
}

/* ── Academic levels ────────────────────────────────────────────── */
$_main_levels = $db->query("SELECT id, level_title FROM tbl_main_academic_levels ORDER BY id")->fetch_all(MYSQLI_ASSOC) ?: [];
$_sub_levels  = [];
if (!empty($_st['main_academic_level'])) {
    $_mal = (int)$_st['main_academic_level'];
    $_slr = $db->query("SELECT id, sub_level_title FROM tbl_sub_academic_levels WHERE main_level = $_mal ORDER BY id");
    $_sub_levels = $_slr ? $_slr->fetch_all(MYSQLI_ASSOC) : [];
}

/* ── Profile completion % ───────────────────────────────────────── */
$_pct = 0;
try { $_pct = min(100, max(0, (int)App::getProfileCompletionStatus($_usr, $_SESSION['user_role'] ?? 0))); } catch(Throwable $e) {}

/* ── Escaped field values ───────────────────────────────────────── */
$_v = fn($k) => htmlspecialchars($_st[$k] ?? '', ENT_QUOTES);
$_img_src  = !empty($_st['image']) ? 'uploads/' . ltrim(basename($_st['image']), '/') : '';
$_start_yr = (int)($_st['start_year'] ?? date('Y'));
$_mal_sel  = (int)($_st['main_academic_level'] ?? 0);
$_sal_sel  = (int)($_st['sub_academic_level']  ?? 0);
$_end_yr   = htmlspecialchars($_st['end_year'] ?? 'Continuing', ENT_QUOTES);
$_ring_off = round(100 - $_pct, 1);

/* ── View-mode resolved labels ──────────────────────────────────── */
$_full_name  = trim(($_st['first_name'] ?? '') . ' ' . ($_st['middle_name'] ?? '') . ' ' . ($_st['last_name'] ?? ''));
$_mal_title  = '';
foreach ($_main_levels as $_ml) { if ($_ml['id'] == $_mal_sel) { $_mal_title = $_ml['level_title']; break; } }
$_sal_title  = '';
foreach ($_sub_levels as $_sl) { if ($_sl['id'] == $_sal_sel) { $_sal_title = $_sl['sub_level_title']; break; } }
$_skills_arr = array_filter(array_map('trim', explode(',', $_st['skill'] ?? '')));
?>
<style>
/* ════════════════════════════════════════════════════════════════
   Student Profile Completion — Hero Animated Design
════════════════════════════════════════════════════════════════ */
@keyframes spc-fade-up   {from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}
@keyframes spc-pop       {0%{transform:scale(.75);opacity:0}60%{transform:scale(1.06)}100%{transform:scale(1);opacity:1}}
@keyframes spc-orb1      {from{transform:translate(0,0) scale(1)}to{transform:translate(-22px,15px) scale(1.18)}}
@keyframes spc-orb2      {from{transform:translate(0,0) scale(1)}to{transform:translate(18px,-20px) scale(1.12)}}
@keyframes spc-orb3      {from{transform:translate(0,0) scale(1)}to{transform:translate(-12px,-10px) scale(.88)}}
@keyframes spc-bar-grow  {from{width:0}to{width:var(--bw,0%)}}
@keyframes spc-ring-in   {from{stroke-dashoffset:100}to{stroke-dashoffset:var(--off,25)}}
@keyframes spc-slide-in  {from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:none}}
@keyframes spc-step-done {0%{transform:scale(0) rotate(-180deg);opacity:0}60%{transform:scale(1.2)}100%{transform:scale(1);opacity:1}}
@keyframes spc-label-up  {from{top:.6rem;font-size:.88rem;color:#94a3b8}to{top:-.55rem;font-size:.72rem}}
@keyframes spc-field-in  {from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
@keyframes spc-photo-pulse{0%,100%{box-shadow:0 0 0 0 rgba(79,70,229,.3)}50%{box-shadow:0 0 0 10px rgba(79,70,229,0)}}

/* ── Wrap ── */
.spc-wrap { font-family:'Open Sans',sans-serif; max-width:980px; margin:0 auto; padding:1.25rem 1rem 3rem; }

/* ════════════════════════════════════════════════════════════
   HERO HEADER
════════════════════════════════════════════════════════════ */
.spc-hero {
    position: relative; overflow: hidden; border-radius: 24px;
    background: linear-gradient(135deg,#05050f 0%,#0f0c29 35%,#1e1040 65%,#2d1b69 100%);
    padding: 2rem 2rem 1.75rem; margin-bottom: 1.5rem; color: #fff;
    animation: spc-fade-up .45s ease both;
}
.spc-orb { position:absolute; border-radius:50%; filter:blur(55px); pointer-events:none; }
.spc-orb-1 { width:240px;height:240px;background:rgba(79,70,229,.32);top:-70px;right:-20px;animation:spc-orb1 8s ease-in-out infinite alternate; }
.spc-orb-2 { width:160px;height:160px;background:rgba(124,58,237,.25);bottom:-50px;right:200px;animation:spc-orb2 10s ease-in-out infinite alternate; }
.spc-orb-3 { width:120px;height:120px;background:rgba(99,102,241,.2);top:20px;left:42%;animation:spc-orb3 7s ease-in-out infinite alternate; }
.spc-hero-inner { position:relative;z-index:2;display:flex;align-items:center;gap:1.5rem;flex-wrap:nowrap; }

/* Avatar */
.spc-avatar-wrap  { position:relative;flex-shrink:0; }
.spc-avatar       { width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,.3);
                    background:rgba(255,255,255,.12);display:block;animation:spc-pop .6s cubic-bezier(.34,1.56,.64,1) both; }
.spc-avatar-empty { width:80px;height:80px;border-radius:50%;border:3px solid rgba(255,255,255,.2);
                    background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;
                    font-size:2rem;color:rgba(255,255,255,.55);animation:spc-pop .6s cubic-bezier(.34,1.56,.64,1) both; }
.spc-avatar-upload { position:absolute;bottom:0;right:0;width:26px;height:26px;border-radius:50%;
                     background:#fff;color:#4f46e5;border:2px solid rgba(255,255,255,.5);
                     display:flex;align-items:center;justify-content:center;cursor:pointer;
                     font-size:.65rem;box-shadow:0 2px 8px rgba(0,0,0,.25);transition:transform .15s; }
.spc-avatar-upload:hover { transform:scale(1.14); }

/* Hero info */
.spc-hero-info    { flex:1;min-width:0; }
.spc-hero-name    { font-size:1.2rem;font-weight:900;letter-spacing:-.02em;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.spc-hero-sub     { font-size:.8rem;opacity:.55;margin-top:.1rem; }
.spc-hero-bar-row { display:flex;align-items:center;gap:.75rem;margin-top:.65rem; }
.spc-hero-bar     { flex:1;max-width:200px;height:5px;background:rgba(255,255,255,.18);border-radius:99px;overflow:hidden; }
.spc-hero-bar-fill{ height:100%;background:linear-gradient(90deg,#a5b4fc,#c4b5fd);border-radius:99px;
                    animation:spc-bar-grow 1.2s cubic-bezier(.16,1,.3,1) both .4s; }
.spc-hero-pct     { font-size:.74rem;font-weight:700;color:rgba(255,255,255,.75); }
.spc-hero-pills   { display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.7rem; }
.spc-hero-pill    { background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.8);
                    font-size:.69rem;font-weight:600;padding:.2rem .65rem;border-radius:20px;
                    display:inline-flex;align-items:center;gap:.28rem; }

/* Ring + actions */
.spc-hero-right { display:flex;align-items:center;gap:1rem;flex-shrink:0; }
.spc-ring-wrap  { flex-shrink:0;text-align:center; }
.spc-ring       { width:70px;height:70px; }
.spc-ring-lbl   { font-size:.6rem;font-weight:700;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.05em;margin-top:.2rem; }
.spc-pct-text   { font-size:.55rem;font-weight:800;fill:#fff; }
.spc-hero-actions { display:flex;flex-direction:column;gap:.5rem; }
.spc-btn { display:inline-flex;align-items:center;gap:.4rem;border-radius:11px;padding:.52rem 1.1rem;
           font-size:.8rem;font-weight:700;cursor:pointer;border:none;font-family:inherit;transition:all .2s; }
.spc-btn-edit   { background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.28);backdrop-filter:blur(4px); }
.spc-btn-edit:hover { background:rgba(255,255,255,.25); }
.spc-btn-ghost  { background:rgba(255,255,255,.1);color:rgba(255,255,255,.75);border:1px solid rgba(255,255,255,.18); }
.spc-btn-ghost:hover { background:rgba(255,255,255,.18);color:#fff; }
@media(max-width:576px){
    .spc-hero-right{ gap:.6rem; }
    .spc-ring-wrap { display:none; }
    .spc-hero-inner{ flex-wrap:wrap; }
}

/* ════════════════════════════════════════════════════════════
   VIEW MODE — INFO CARDS
════════════════════════════════════════════════════════════ */
.spc-incomplete-banner {
    display:flex;align-items:center;gap:.9rem;flex-wrap:wrap;
    background:linear-gradient(135deg,#fffbeb,#fef9c3);border:1px solid #fde68a;border-radius:14px;
    padding:.9rem 1.25rem;margin-bottom:1.25rem;font-size:.83rem;color:#92400e;
    animation:spc-fade-up .35s ease both .1s;
}
.spc-incomplete-banner i { font-size:1.15rem;color:#f59e0b;flex-shrink:0; }
.spc-btn-inline { margin-left:auto;font-size:.78rem;font-weight:700;background:linear-gradient(135deg,#f59e0b,#d97706);
                  color:#fff;border:none;border-radius:9px;padding:.42rem .95rem;cursor:pointer;transition:all .2s;white-space:nowrap; }
.spc-btn-inline:hover { transform:translateY(-1px);box-shadow:0 4px 12px rgba(245,158,11,.4); }
.spc-card { background:#fff;border:1px solid #e8edf3;border-radius:20px;
            box-shadow:0 2px 16px rgba(0,0,0,.055);overflow:hidden;
            animation:spc-fade-up .35s ease both; }
/* Only stretch to equal height inside a Bootstrap row — not standalone full-width cards */
.row .spc-card { height:100%; }
.spc-card:nth-child(2){ animation-delay:.06s; }
.spc-card-header { display:flex;align-items:center;gap:.7rem;padding:1rem 1.5rem;border-bottom:1px solid #f1f5f9; }
.spc-card-icon   { width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0; }
.spc-card-title  { font-size:.9rem;font-weight:700;color:#1e293b;margin:0; }
.spc-card-body   { padding:1.1rem 1.5rem; }
.spc-info-row    { display:flex;align-items:flex-start;gap:.75rem;padding:.55rem 0;border-bottom:1px solid #f8fafc; }
.spc-info-row:last-child{ border-bottom:none;padding-bottom:0; }
.spc-info-key   { font-size:.71rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em;min-width:105px;padding-top:.18rem;flex-shrink:0; }
.spc-info-val   { font-size:.84rem;font-weight:600;color:#1e293b;flex:1;line-height:1.45; }
.spc-info-val.empty { color:#cbd5e1;font-style:italic;font-weight:400;font-size:.8rem; }
.spc-skill-tag  { display:inline-flex;align-items:center;background:#ede9fe;color:#4f46e5;border-radius:100px;padding:.18rem .65rem;font-size:.72rem;font-weight:700;margin:.12rem .12rem 0 0; }
.spc-status-badge { display:inline-flex;align-items:center;gap:.35rem;border-radius:100px;padding:.25rem .75rem;font-size:.75rem;font-weight:700; }
.spc-status-badge.continuing{background:#dcfce7;color:#15803d}
.spc-status-badge.completed{background:#dbeafe;color:#1d4ed8}
.spc-status-badge.passed{background:#e0f2fe;color:#0369a1}
.spc-status-badge.deferred{background:#fef3c7;color:#b45309}
.spc-status-badge.dropped{background:#fee2e2;color:#dc2626}

/* ════════════════════════════════════════════════════════════
   EDIT WIZARD
════════════════════════════════════════════════════════════ */
.spc-wizard { background:#fff;border-radius:22px;overflow:hidden;box-shadow:0 4px 32px rgba(0,0,0,.08);
              animation:spc-fade-up .35s ease both; }

/* Step progress bar */
.spc-steps-wrap { background:linear-gradient(135deg,#f8f7ff,#f1f5f9);padding:1.25rem 1.75rem 0;border-bottom:1px solid #e0e7ff; }
.spc-steps  { display:flex;align-items:flex-end;gap:0;overflow-x:auto;scrollbar-width:none; }
.spc-steps::-webkit-scrollbar{ display:none; }
.spc-step-item { display:flex;align-items:center;flex:1;min-width:0; }
.spc-step-btn  { display:flex;flex-direction:column;align-items:center;gap:.35rem;cursor:pointer;
                 padding:.2rem .4rem .85rem;border:none;background:transparent;position:relative;flex:1; }
.spc-step-pill { width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;
                 font-size:.8rem;font-weight:800;flex-shrink:0;border:2px solid #e0e7ff;
                 background:#fff;color:#94a3b8;transition:all .3s; }
.spc-step-lbl  { font-size:.72rem;font-weight:700;color:#94a3b8;white-space:nowrap;transition:color .3s; }
.spc-step-sub  { font-size:.62rem;color:#c4c9d4;white-space:nowrap; }
.spc-step-item.active .spc-step-pill { background:linear-gradient(135deg,#4f46e5,#7c3aed);border-color:transparent;color:#fff;box-shadow:0 4px 14px rgba(79,70,229,.4); }
.spc-step-item.active .spc-step-lbl  { color:#4f46e5; }
.spc-step-item.done   .spc-step-pill { background:linear-gradient(135deg,#059669,#0d9488);border-color:transparent;color:#fff;box-shadow:0 3px 10px rgba(5,150,105,.3);animation:spc-step-done .4s cubic-bezier(.34,1.56,.64,1) both; }
.spc-step-item.done   .spc-step-lbl  { color:#059669; }
/* Active underline indicator */
/* Underline indicator is a lighter tint so it contrasts with both the pill above and the amber bar below */
.spc-step-btn::after { content:'';position:absolute;bottom:0;left:50%;right:50%;height:2px;
                       background:linear-gradient(90deg,#818cf8,#a5b4fc);border-radius:3px 3px 0 0;transition:all .3s;opacity:0; }
.spc-step-item.active .spc-step-btn::after { left:15%;right:15%;opacity:1; }
.spc-connector { flex-shrink:1;height:2px;background:#e0e7ff;margin-bottom:1rem;transition:background .4s;min-width:16px; }
.spc-connector.done { background:linear-gradient(90deg,#059669,#0d9488); }
@media(max-width:576px){ .spc-step-lbl,.spc-step-sub{ display:none; } .spc-step-btn{ padding:.2rem .2rem .7rem; } }

/* Progress bar uses AMBER so it never merges visually with the indigo active-step pill above it */
.spc-progress { height:4px;background:#f0f4f8; }
.spc-progress-bar { height:100%;background:linear-gradient(90deg,#f59e0b,#fb923c);transition:width .4s cubic-bezier(.16,1,.3,1);box-shadow:0 1px 4px rgba(245,158,11,.4); }

/* Panel */
.spc-body   { padding:1.75rem 1.75rem; }
.spc-panel  { display:none !important; }
.spc-panel.active { display:block !important; animation:spc-slide-in .3s ease both; }

.spc-section-title { font-size:.7rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;
                     margin-bottom:1rem;margin-top:.25rem;display:flex;align-items:center;gap:.5rem; }
.spc-section-title::after { content:'';flex:1;height:1px;background:linear-gradient(90deg,#e0e7ff,transparent); }

/* ── Label-above inputs (no floating labels — clean, reliable for all input types) ── */
.spc-field {
    position: relative;
    padding-top: 1.55rem;   /* reserved space for the label that sits above the box */
    animation: spc-field-in .3s ease both;
}
/* Label floats above the input box */
.spc-field > label {
    position: absolute;
    top: 0;
    left: 0;
    font-size: .7rem;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .05em;
    pointer-events: none;
    white-space: nowrap;
    line-height: 1;
}
/* Icon centers on the INPUT BOX (offset = padding-top + half box height) */
.spc-field-icon {
    position: absolute;
    left: .8rem;
    top: calc(1.55rem + 22px);   /* padding-top + ½ × 44px box */
    transform: translateY(-50%);
    color: #a5b4fc;
    font-size: .9rem;
    pointer-events: none;
    transition: color .2s;
}
/* Textarea icon stays near top of box */
.spc-field textarea ~ .spc-field-icon {
    top: calc(1.55rem + .75rem);
    transform: none;
}
/* All inputs / selects / textareas */
.spc-field input,
.spc-field select,
.spc-field textarea {
    width: 100%;
    border: 1.5px solid #e0e7ff;
    border-radius: 12px;
    padding: .65rem .9rem .65rem 2.5rem;
    font-size: .875rem;
    font-family: inherit;
    color: #1e293b;
    background: #fafbff;
    transition: border-color .2s, box-shadow .2s, background .2s;
    outline: none;
    -webkit-appearance: none;
    appearance: none;
    height: 44px;
}
.spc-field textarea {
    height: auto;
    min-height: 80px;
    resize: vertical;
    padding-top: .65rem;
}
.spc-field input:focus,
.spc-field select:focus,
.spc-field textarea:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79,70,229,.1);
    background: #fff;
}
.spc-field input:focus ~ .spc-field-icon,
.spc-field select:focus ~ .spc-field-icon,
.spc-field textarea:focus ~ .spc-field-icon { color: #4f46e5; }
.spc-field input.is-invalid,
.spc-field select.is-invalid { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.1); }

/* ── Phone field ── */
.spc-phone-wrap { position: relative; }
.spc-phone-prefix {
    position: absolute; left: 2.5rem;
    top: 50%; transform: translateY(-50%);
    font-size: .875rem; font-weight: 700; color: #4f46e5;
    pointer-events: none; z-index: 1; line-height: 1;
}
.spc-phone-input  { padding-left: 3.25rem !important; }
.spc-phone-len {
    position: absolute; right: .8rem;
    top: 50%; transform: translateY(-50%);
    font-size: .68rem; font-weight: 700; color: #94a3b8; pointer-events: none;
}
.spc-phone-len.valid   { color: #059669; }
.spc-phone-len.invalid { color: #ef4444; }
/* Phone label left-aligns from wrapper edge */
.spc-field .spc-phone-wrap ~ label { left: 0 !important; }

/* ── Photo upload ── */
.spc-photo-zone { display:flex;flex-direction:column;align-items:center;gap:.75rem;
                  padding:1.4rem;border:2px dashed #e0e7ff;border-radius:18px;
                  cursor:pointer;transition:all .22s;background:#f8f7ff;text-align:center;
                  position:relative;overflow:hidden; }
.spc-photo-zone:hover { border-color:#4f46e5;background:#f5f3ff;box-shadow:0 4px 20px rgba(79,70,229,.1); }
.spc-photo-zone.has-img { border-style:solid;border-color:#e0e7ff;background:#fff; }
.spc-photo-zone input[type=file] { position:absolute;inset:0;opacity:0;cursor:pointer; }
.spc-photo-large { width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid #e0e7ff;
                   box-shadow:0 4px 16px rgba(0,0,0,.1); }
.spc-photo-empty { width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,#ede9fe,#e0e7ff);
                   display:flex;align-items:center;justify-content:center;font-size:2.5rem;color:#a5b4fc;
                   animation:spc-photo-pulse 3s ease-in-out infinite; }
.spc-photo-hint  { font-size:.75rem;color:#64748b;font-weight:600; }
.spc-photo-hint span { color:#94a3b8;font-weight:400; }

/* ── Skills tags ── */
.spc-tags-wrap { display:flex;flex-wrap:wrap;gap:.4rem;align-items:center;
                 border:1.5px solid #e0e7ff;border-radius:12px;padding:.5rem .75rem;
                 background:#fafbff;min-height:48px;cursor:text;transition:all .2s; }
.spc-tags-wrap:focus-within { border-color:#4f46e5;box-shadow:0 0 0 3px rgba(79,70,229,.1);background:#fff; }
.spc-tag { display:inline-flex;align-items:center;gap:.3rem;background:linear-gradient(135deg,#ede9fe,#e0e7ff);
           color:#4f46e5;border-radius:100px;padding:.18rem .65rem;font-size:.75rem;font-weight:700;
           animation:spc-pop .2s cubic-bezier(.34,1.56,.64,1) both; }
.spc-tag-del { cursor:pointer;font-size:.9rem;line-height:1;opacity:.6;transition:opacity .15s; }
.spc-tag-del:hover { opacity:1; }
.spc-tag-input { border:none;outline:none;font-size:.85rem;min-width:80px;flex:1;background:transparent;font-family:inherit;color:#1e293b; }

/* ── Summary card ── */
.spc-summary-card { background:linear-gradient(135deg,#f8f7ff,#f0f4ff);border:1px solid #e0e7ff;border-radius:16px;padding:1.1rem 1.25rem;margin-top:1.25rem; }
.spc-summary-lbl  { font-size:.65rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.2rem; }
.spc-summary-val  { font-size:.82rem;font-weight:700;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }

/* ── Footer nav ── */
.spc-footer { display:flex;align-items:center;justify-content:space-between;
              padding:1.25rem 1.75rem;border-top:1px solid #f0f4f8;
              background:linear-gradient(135deg,#fafbff,#f8f7ff);flex-wrap:wrap;gap:.75rem; }
.spc-step-counter { font-size:.74rem;font-weight:700;color:#94a3b8;background:#f1f5f9;padding:.3rem .8rem;border-radius:20px; }
.spc-btn-cancel { background:#f1f5f9;color:#475569;border:1px solid #e0e7ff;border-radius:11px;padding:.5rem 1rem;font-size:.8rem;font-weight:700;cursor:pointer; }
.spc-btn-cancel:hover { background:#e0e7ff; }
.spc-btn-prev   { background:#fff;color:#475569;border:1.5px solid #e0e7ff;border-radius:11px;padding:.5rem 1rem;font-size:.8rem;font-weight:700;cursor:pointer;transition:all .2s; }
.spc-btn-prev:hover:not(:disabled) { border-color:#4f46e5;color:#4f46e5; }
.spc-btn-prev:disabled { opacity:.35;cursor:not-allowed; }
.spc-btn-next   { background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;border:none;border-radius:11px;padding:.55rem 1.4rem;font-size:.83rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.4rem;transition:all .2s;box-shadow:0 4px 14px rgba(79,70,229,.35); }
.spc-btn-next:hover { transform:translateY(-2px);box-shadow:0 8px 24px rgba(79,70,229,.45); }
.spc-btn-save   { background:linear-gradient(135deg,#059669,#0d9488);color:#fff;border:none;border-radius:11px;padding:.55rem 1.4rem;font-size:.83rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:.4rem;transition:all .2s;box-shadow:0 4px 14px rgba(5,150,105,.35); }
.spc-btn-save:hover { transform:translateY(-2px);box-shadow:0 8px 24px rgba(5,150,105,.45); }

/* Transition between view/edit */
#spcViewMode,#spcEditMode{ transition:opacity .25s; }
</style>

<div class="spc-wrap">

<!-- ════════════════════ HERO HEADER ════════════════════ -->
<div class="spc-hero">
    <div class="spc-orb spc-orb-1"></div>
    <div class="spc-orb spc-orb-2"></div>
    <div class="spc-orb spc-orb-3"></div>
    <div class="spc-hero-inner">

        <!-- Avatar -->
        <div class="spc-avatar-wrap" id="spcAvatarClickArea" title="Change photo">
            <?php if ($_img_src): ?>
                <img class="spc-avatar" id="spcAvatarSmall" src="<?= $_img_src ?>" alt="Profile">
            <?php else: ?>
                <div class="spc-avatar-empty" id="spcAvatarSmall"><i class="bi bi-person"></i></div>
            <?php endif; ?>
            <div class="spc-avatar-upload" id="spcAvatarEditBtn" style="display:none"><i class="bi bi-camera-fill"></i></div>
        </div>

        <!-- Info -->
        <div class="spc-hero-info">
            <div class="spc-hero-name"><?= $_full_name ? htmlspecialchars($_full_name) : htmlspecialchars($_SESSION['name'] ?? 'Student') ?></div>
            <div class="spc-hero-sub"><?= $_mal_title ? htmlspecialchars($_mal_title).($_sal_title?' · '.htmlspecialchars($_sal_title):'') : 'Student Profile' ?></div>
            <div class="spc-hero-bar-row">
                <div class="spc-hero-bar">
                    <div class="spc-hero-bar-fill" style="--bw:<?= $_pct ?>%;width:<?= $_pct ?>%"></div>
                </div>
                <span class="spc-hero-pct"><?= $_pct ?>% complete</span>
            </div>
            <div class="spc-hero-pills">
                <?php if ($_full_name): ?><span class="spc-hero-pill"><i class="bi bi-person-check-fill" style="color:#6ee7b7"></i>Name set</span><?php endif; ?>
                <?php if (!empty($_st['phone'])): ?><span class="spc-hero-pill"><i class="bi bi-phone-fill" style="color:#93c5fd"></i>Phone set</span><?php endif; ?>
                <?php if ($_mal_title): ?><span class="spc-hero-pill"><i class="bi bi-mortarboard-fill" style="color:#fde68a"></i><?= htmlspecialchars($_mal_title) ?></span><?php endif; ?>
            </div>
        </div>

        <!-- Ring + CTA -->
        <div class="spc-hero-right">
            <div class="spc-ring-wrap">
                <svg class="spc-ring" viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="rgba(255,255,255,.18)" stroke-width="3.2"/>
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#fff" stroke-width="3.2"
                            stroke-dasharray="100 100" stroke-dashoffset="<?= $_ring_off ?>"
                            stroke-linecap="round" style="transform:rotate(-90deg);transform-origin:center;transition:stroke-dashoffset 1.4s cubic-bezier(.16,1,.3,1)"/>
                    <text x="18" y="20.5" text-anchor="middle" dominant-baseline="middle" class="spc-pct-text"><?= $_pct ?>%</text>
                </svg>
                <div class="spc-ring-lbl">Complete</div>
            </div>
            <div class="spc-hero-actions" id="spcHeaderActions">
                <button class="spc-btn spc-btn-edit" id="spcBtnEditToggle" onclick="spcEnterEdit()">
                    <i class="bi bi-pencil-square"></i> Edit Profile
                </button>
                <a href="?view=3002" class="spc-btn spc-btn-ghost text-decoration-none text-center" id="spcBtnSkip">
                    <i class="bi bi-columns-gap"></i> Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ════════════════════ VIEW MODE ════════════════════ -->
<div id="spcViewMode">

    <?php if ($_pct < 100): ?>
    <div class="spc-incomplete-banner">
        <i class="bi bi-exclamation-circle-fill"></i>
        <div><strong>Profile <?= $_pct ?>% complete.</strong> Complete your profile to unlock the best learning experience and personalised recommendations.</div>
        <button class="spc-btn-inline" onclick="spcEnterEdit()"><i class="bi bi-pencil me-1"></i>Complete Now</button>
    </div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <!-- Personal -->
        <div class="col-12 col-md-6">
            <div class="spc-card">
                <div class="spc-card-header">
                    <div class="spc-card-icon" style="background:#ede9fe;color:#4f46e5"><i class="bi bi-person-fill"></i></div>
                    <h6 class="spc-card-title">Personal</h6>
                </div>
                <div class="spc-card-body">
                    <div class="spc-info-row"><span class="spc-info-key"><i class="bi bi-person me-1"></i>Full Name</span>
                        <?php if ($_full_name): ?><span class="spc-info-val"><?= htmlspecialchars($_full_name) ?></span>
                        <?php else: ?><span class="spc-info-val empty">Not set</span><?php endif; ?>
                    </div>
                    <div class="spc-info-row"><span class="spc-info-key"><i class="bi bi-calendar3 me-1"></i>Date of Birth</span>
                        <?php if (!empty($_st['dob'])): ?><span class="spc-info-val"><?= htmlspecialchars($_st['dob']) ?></span>
                        <?php else: ?><span class="spc-info-val empty">Not set</span><?php endif; ?>
                    </div>
                    <div class="spc-info-row"><span class="spc-info-key"><i class="bi bi-file-text me-1"></i>Bio</span>
                        <?php if (!empty($_st['description'])): ?><span class="spc-info-val" style="font-weight:500;color:#475569"><?= nl2br(htmlspecialchars($_st['description'])) ?></span>
                        <?php else: ?><span class="spc-info-val empty">Not set</span><?php endif; ?>
                    </div>
                    <div class="spc-info-row"><span class="spc-info-key"><i class="bi bi-stars me-1"></i>Skills</span>
                        <?php if ($_skills_arr): ?><span class="spc-info-val"><?php foreach ($_skills_arr as $_sk): ?><span class="spc-skill-tag"><?= htmlspecialchars($_sk) ?></span><?php endforeach; ?></span>
                        <?php else: ?><span class="spc-info-val empty">None added</span><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Contact -->
        <div class="col-12 col-md-6">
            <div class="spc-card">
                <div class="spc-card-header">
                    <div class="spc-card-icon" style="background:#e0f2fe;color:#0284c7"><i class="bi bi-telephone-fill"></i></div>
                    <h6 class="spc-card-title">Contact</h6>
                </div>
                <div class="spc-card-body">
                    <div class="spc-info-row"><span class="spc-info-key"><i class="bi bi-people me-1"></i>Guardian</span>
                        <?php if (!empty($_st['parent_name'])): ?><span class="spc-info-val"><?= htmlspecialchars($_st['parent_name']) ?></span>
                        <?php else: ?><span class="spc-info-val empty">Not set</span><?php endif; ?>
                    </div>
                    <div class="spc-info-row"><span class="spc-info-key"><i class="bi bi-phone me-1"></i>Phone</span>
                        <?php if (!empty($_st['phone'])): ?><span class="spc-info-val">+<?= htmlspecialchars($_st['phone']) ?></span>
                        <?php else: ?><span class="spc-info-val empty">Not set</span><?php endif; ?>
                    </div>
                    <div class="spc-info-row"><span class="spc-info-key"><i class="bi bi-envelope me-1"></i>Email</span>
                        <?php if (!empty($_st['email'])): ?><span class="spc-info-val"><?= htmlspecialchars($_st['email']) ?></span>
                        <?php else: ?><span class="spc-info-val empty">Not set</span><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Academic — responsive 4-stat grid, handles empty gracefully -->
    <?php
    /* Only count as "has data" when the student explicitly set a level or class.
       Start year always defaults to the current year so it cannot determine intent. */
    $_hasAcademic = !empty($_mal_title) || !empty($_sal_title);
    $_sc  = strtolower($_st['end_year'] ?? 'continuing');
    $_sc2 = in_array($_sc, ['continuing','completed','passed','deferred','dropped']) ? $_sc : 'continuing';
    ?>
    <div class="spc-card mb-3" style="animation-delay:.12s">
        <div class="spc-card-header">
            <div class="spc-card-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-mortarboard-fill"></i></div>
            <h6 class="spc-card-title">Academic Information</h6>
            <?php if (!$_hasAcademic): ?>
            <span style="margin-left:auto;font-size:.72rem;font-weight:600;color:#94a3b8;background:#f8fafc;border-radius:20px;padding:.2rem .75rem;border:1px solid #e2e8f0">
                <i class="bi bi-pencil me-1"></i>Not set yet
            </span>
            <?php endif; ?>
        </div>
        <?php if (!$_hasAcademic): ?>
        <!-- Empty state — compact placeholder, no expanding layout -->
        <div style="padding:.85rem 1.5rem;display:flex;align-items:center;gap:.75rem;background:linear-gradient(135deg,#fffbeb,#fef9c3);border-top:1px solid #fde68a">
            <i class="bi bi-exclamation-circle" style="color:#f59e0b;font-size:1.1rem;flex-shrink:0"></i>
            <div style="flex:1;min-width:0">
                <div style="font-size:.8rem;font-weight:700;color:#92400e">Academic details not completed</div>
                <div style="font-size:.72rem;color:#b45309;margin-top:.1rem">Click <strong>Edit Profile</strong> to set your education level, class, and status</div>
            </div>
        </div>
        <?php else: ?>
        <!-- Filled state — 4-column grid, no overflow issues -->
        <div class="spc-card-body" style="padding:.85rem 1.5rem">
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0">
                <div style="padding-right:1rem;border-right:1px solid #f1f5f9">
                    <div style="font-size:.63rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem"><i class="bi bi-layers me-1"></i>Level</div>
                    <div style="font-size:.82rem;font-weight:700;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($_mal_title) ?>">
                        <?= $_mal_title ? htmlspecialchars($_mal_title) : '<span style="color:#cbd5e1;font-style:italic;font-size:.75rem">—</span>' ?>
                    </div>
                </div>
                <div style="padding:0 1rem;border-right:1px solid #f1f5f9">
                    <div style="font-size:.63rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem"><i class="bi bi-book me-1"></i>Class</div>
                    <div style="font-size:.82rem;font-weight:700;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($_sal_title) ?>">
                        <?= $_sal_title ? htmlspecialchars($_sal_title) : '<span style="color:#cbd5e1;font-style:italic;font-size:.75rem">—</span>' ?>
                    </div>
                </div>
                <div style="padding:0 1rem;border-right:1px solid #f1f5f9">
                    <div style="font-size:.63rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem"><i class="bi bi-calendar-check me-1"></i>Start Year</div>
                    <div style="font-size:.82rem;font-weight:700;color:#1e293b">
                        <?= $_start_yr ?: '<span style="color:#cbd5e1;font-style:italic;font-size:.75rem">—</span>' ?>
                    </div>
                </div>
                <div style="padding-left:1rem">
                    <div style="font-size:.63rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem"><i class="bi bi-patch-check me-1"></i>Status</div>
                    <span class="spc-status-badge <?= $_sc2 ?>" style="font-size:.71rem;padding:.2rem .6rem">
                        <i class="bi bi-circle-fill" style="font-size:.38rem"></i><?= htmlspecialchars($_st['end_year'] ?? 'Continuing') ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div><!-- /#spcViewMode -->

<!-- ════════════════════ EDIT MODE ════════════════════ -->
<div id="spcEditMode" style="display:none">
<div class="spc-wizard">

    <!-- Step indicator -->
    <div class="spc-steps-wrap">
        <div class="spc-steps" id="spcStepsTabs">
            <div class="spc-step-item active" id="spcTab_0">
                <button class="spc-step-btn" onclick="spcGoStep(0)">
                    <div class="spc-step-pill" id="spcNum_0"><i class="bi bi-person-fill"></i></div>
                    <div class="spc-step-lbl">Personal</div>
                    <div class="spc-step-sub">Name &amp; photo</div>
                </button>
            </div>
            <div class="spc-connector" id="spcConn_0"></div>
            <div class="spc-step-item" id="spcTab_1">
                <button class="spc-step-btn" onclick="spcGoStep(1)">
                    <div class="spc-step-pill" id="spcNum_1"><i class="bi bi-telephone-fill"></i></div>
                    <div class="spc-step-lbl">Contact</div>
                    <div class="spc-step-sub">Phone &amp; guardian</div>
                </button>
            </div>
            <div class="spc-connector" id="spcConn_1"></div>
            <div class="spc-step-item" id="spcTab_2">
                <button class="spc-step-btn" onclick="spcGoStep(2)">
                    <div class="spc-step-pill" id="spcNum_2"><i class="bi bi-mortarboard-fill"></i></div>
                    <div class="spc-step-lbl">Academics</div>
                    <div class="spc-step-sub">Level &amp; class</div>
                </button>
            </div>
        </div>
    </div>
    <div class="spc-progress"><div class="spc-progress-bar" id="spcProgressBar" style="width:33.3%"></div></div>

    <!-- ── STEP 1: Personal ── -->
    <div class="spc-body spc-panel active" id="spcPanel_0">
        <div class="row g-3">
            <!-- Photo upload -->
            <div class="col-12 col-sm-4 col-md-3">
                <div class="spc-section-title">Profile Photo</div>
                <div class="spc-photo-zone <?= $_img_src?'has-img':'' ?>" id="spcPhotoZone">
                    <input type="file" id="spcImgInput" accept="image/*">
                    <?php if ($_img_src): ?>
                        <img class="spc-photo-large" id="spcPhotoPreview" src="<?= $_img_src ?>" alt="">
                    <?php else: ?>
                        <div class="spc-photo-empty" id="spcPhotoEmpty"><i class="bi bi-person"></i></div>
                        <img class="spc-photo-large" id="spcPhotoPreview" src="" alt="" style="display:none">
                    <?php endif; ?>
                    <div class="spc-photo-hint"><i class="bi bi-cloud-upload me-1"></i>Click to upload<br><span>JPG, PNG — max 2 MB</span></div>
                </div>
            </div>
            <!-- Name + bio + skills -->
            <div class="col-12 col-sm-8 col-md-9">
                <div class="spc-section-title">Basic Details</div>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <div class="spc-field">
                            <i class="bi bi-person spc-field-icon"></i>
                            <input id="spc_fn" placeholder=" " value="<?= $_v('first_name') ?>">
                            <label>First Name *</label>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="spc-field">
                            <i class="bi bi-person spc-field-icon"></i>
                            <input id="spc_mn" placeholder=" " value="<?= $_v('middle_name') ?>">
                            <label>Middle Name</label>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="spc-field">
                            <i class="bi bi-person spc-field-icon"></i>
                            <input id="spc_ln" placeholder=" " value="<?= $_v('last_name') ?>">
                            <label>Last Name *</label>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="spc-field">
                            <i class="bi bi-calendar3 spc-field-icon"></i>
                            <input type="date" id="spc_dob" placeholder=" " value="<?= $_v('dob') ?>">
                            <label>Date of Birth</label>
                        </div>
                    </div>
                    <div class="col-12 col-md-8">
                        <div class="spc-field">
                            <i class="bi bi-chat-left-text spc-field-icon"></i>
                            <textarea id="spc_desc" placeholder=" " style="height:56px"><?= $_v('description') ?></textarea>
                            <label>Short Bio</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="spc-section-title" style="margin-top:.1rem">Skills &amp; Interests</div>
                        <div class="spc-tags-wrap" id="spcTagsWrap" onclick="document.getElementById('spcTagInput').focus()">
                            <input class="spc-tag-input" id="spcTagInput" placeholder="Type a skill, press Enter…" autocomplete="off">
                        </div>
                        <div style="font-size:.68rem;color:#94a3b8;margin-top:.3rem"><i class="bi bi-lightbulb me-1"></i>Press Enter or comma to add</div>
                        <input type="hidden" id="spc_skill" value="<?= $_v('skill') ?>">
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /.spcPanel_0 -->

    <!-- ── STEP 2: Contact ── -->
    <div class="spc-body spc-panel" id="spcPanel_1">
        <div class="spc-section-title">Parent / Guardian</div>
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="spc-field">
                    <i class="bi bi-people spc-field-icon"></i>
                    <input id="spc_pname" placeholder=" " value="<?= $_v('parent_name') ?>">
                    <label>Parent / Guardian Name</label>
                </div>
            </div>
            <!-- Phone with auto-format -->
            <div class="col-12 col-md-4">
                <div class="spc-field">
                    <div class="spc-phone-wrap">
                        <i class="bi bi-phone spc-field-icon"></i>
                        <span class="spc-phone-prefix">+</span>
                        <input id="spc_phone" class="spc-phone-input" placeholder=" "
                               value="<?= $_v('phone') ?>" inputmode="numeric" maxlength="13"
                               autocomplete="tel">
                        <span class="spc-phone-len" id="spcPhoneLen">0/12</span>
                    </div>
                    <label>Phone Number</label>
                </div>
                <div style="font-size:.68rem;color:#94a3b8;margin-top:.3rem" id="spcPhoneHint">
                    <i class="bi bi-info-circle me-1"></i>Format: 255XXXXXXXXX (12 digits, e.g. 255712345678)
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="spc-field">
                    <i class="bi bi-envelope spc-field-icon"></i>
                    <input type="email" id="spc_email" placeholder=" " value="<?= $_v('email') ?>">
                    <label>Email Address</label>
                </div>
            </div>
        </div>
        <div class="spc-section-title">Home Address</div>
        <div class="row g-3">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="spc-field">
                    <i class="bi bi-geo spc-field-icon"></i>
                    <input id="spc_street" placeholder=" " value="<?= $_v('school') ?>">
                    <label>Street / Area</label>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="spc-field">
                    <i class="bi bi-map spc-field-icon"></i>
                    <input id="spc_city" placeholder=" " value="<?= $_v('course') ?>">
                    <label>City / Region</label>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="spc-field">
                    <i class="bi bi-globe2 spc-field-icon"></i>
                    <input id="spc_country" placeholder=" " value="Tanzania">
                    <label>Country</label>
                </div>
            </div>
        </div>
    </div><!-- /.spcPanel_1 -->

    <!-- ── STEP 3: Academics ── -->
    <div class="spc-body spc-panel" id="spcPanel_2">
        <div class="spc-section-title">Academic Level</div>
        <div class="row g-3">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="spc-field">
                    <i class="bi bi-layers spc-field-icon"></i>
                    <select id="spc_main_level" onchange="spcLoadSubLevels(this.value)">
                        <option value="">— Select level —</option>
                        <?php foreach ($_main_levels as $_ml): ?>
                        <option value="<?= $_ml['id'] ?>" <?= ($_mal_sel==$_ml['id'])?'selected':'' ?>><?= htmlspecialchars($_ml['level_title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Education Level *</label>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="spc-field">
                    <i class="bi bi-bookmark spc-field-icon"></i>
                    <select id="spc_sub_level">
                        <option value="">— Select class/course —</option>
                        <?php foreach ($_sub_levels as $_sl): ?>
                        <option value="<?= $_sl['id'] ?>" <?= ($_sal_sel==$_sl['id'])?'selected':'' ?>><?= htmlspecialchars($_sl['sub_level_title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Class / Course</label>
                </div>
            </div>
            <div class="col-12 col-md-4 col-lg-2">
                <div class="spc-field">
                    <i class="bi bi-calendar-check spc-field-icon"></i>
                    <select id="spc_start_year">
                        <?php for ($y=date('Y');$y>=2015;$y--): ?>
                        <option value="<?= $y ?>" <?= ($_start_yr==$y)?'selected':'' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                    <label>Start Year</label>
                </div>
            </div>
            <div class="col-12 col-md-4 col-lg-2">
                <div class="spc-field">
                    <i class="bi bi-patch-check spc-field-icon"></i>
                    <select id="spc_status">
                        <?php foreach (['Continuing','Passed','Completed','Deferred','Dropped'] as $_s): ?>
                        <option value="<?= $_s ?>" <?= ($_end_yr===$_s)?'selected':'' ?>><?= $_s ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Status</label>
                </div>
            </div>
        </div>
        <!-- Summary -->
        <div class="spc-summary-card" style="display:none" id="spcSummaryWrap">
            <div style="font-size:.72rem;font-weight:800;color:#4f46e5;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.85rem;display:flex;align-items:center;gap:.4rem">
                <i class="bi bi-check2-circle"></i> Profile Summary
            </div>
            <div class="row g-2" id="spcSummary"></div>
        </div>
    </div><!-- /.spcPanel_2 -->

    <!-- Footer nav -->
    <div class="spc-footer">
        <div class="d-flex gap-2">
            <button class="spc-btn-cancel" onclick="spcExitEdit()"><i class="bi bi-x-lg me-1"></i>Cancel</button>
            <button class="spc-btn-prev" id="spcBtnPrev" onclick="spcPrev()" disabled><i class="bi bi-chevron-left"></i> Back</button>
        </div>
        <span class="spc-step-counter" id="spcStepCounter">Step 1 of 3</span>
        <div style="display:flex;gap:.6rem">
            <button class="spc-btn-next" id="spcBtnNext" onclick="spcNext()">Next <i class="bi bi-chevron-right"></i></button>
            <button class="spc-btn-save" id="spcBtnSave" style="display:none" onclick="spcSave()"><i class="bi bi-check2-circle"></i> Save Profile</button>
        </div>
    </div>

</div><!-- /.spc-wizard -->
</div><!-- /#spcEditMode -->

</div><!-- /.spc-wrap -->

<script>
/* ── State ── */
var spcStep     = 0;
var SPC_TOTAL   = 3;
var spcBase64   = '';
var spcTags     = [];
var SPC_USR     = <?= json_encode($_usr) ?>;

/* ── Mode toggle ── */
function spcEnterEdit() {
    document.getElementById('spcViewMode').style.display = 'none';
    document.getElementById('spcEditMode').style.display = '';
    document.getElementById('spcBtnEditToggle').style.display = 'none';
    document.getElementById('spcBtnSkip').style.display = 'none';
    document.getElementById('spcAvatarEditBtn').style.display = '';
    document.getElementById('spcAvatarClickArea').style.cursor = 'pointer';
    document.getElementById('spcAvatarClickArea').onclick = function(){ document.getElementById('spcImgInput').click(); };
    spcUpdateStep(0);
}
function spcExitEdit() {
    document.getElementById('spcEditMode').style.display = 'none';
    document.getElementById('spcViewMode').style.display = '';
    document.getElementById('spcBtnEditToggle').style.display = '';
    document.getElementById('spcBtnSkip').style.display = '';
    document.getElementById('spcAvatarEditBtn').style.display = 'none';
    document.getElementById('spcAvatarClickArea').style.cursor = 'default';
    document.getElementById('spcAvatarClickArea').onclick = null;
}

/* ── Init ── */
(function() {
    spcRenderTags(<?= json_encode(array_values($_skills_arr)) ?>);
    spcTagInput();
    spcPhoneInit();
})();

/* ── Step navigation ── */
function spcUpdateStep(i) {
    spcStep = i;
    document.querySelectorAll('.spc-panel').forEach(function(p,idx){ p.classList.toggle('active', idx===i); });
    var icons = ['bi-person-fill','bi-telephone-fill','bi-mortarboard-fill'];
    for (var t = 0; t < SPC_TOTAL; t++) {
        var tab = document.getElementById('spcTab_' + t);
        var num = document.getElementById('spcNum_' + t);
        tab.classList.remove('active','done');
        if (t < i)  { tab.classList.add('done');   num.innerHTML = '<i class="bi bi-check2"></i>'; }
        if (t === i) tab.classList.add('active');
        if (t > i)  num.innerHTML = '<i class="bi ' + icons[t] + '"></i>';
    }
    for (var c = 0; c < SPC_TOTAL-1; c++) {
        var conn = document.getElementById('spcConn_' + c);
        conn.classList.toggle('done', c < i);
    }
    document.getElementById('spcProgressBar').style.width = (((i+1)/SPC_TOTAL)*100) + '%';
    document.getElementById('spcStepCounter').textContent = 'Step ' + (i+1) + ' of ' + SPC_TOTAL;
    document.getElementById('spcBtnPrev').disabled = (i===0);
    var isLast = (i === SPC_TOTAL-1);
    document.getElementById('spcBtnNext').style.display = isLast ? 'none' : '';
    document.getElementById('spcBtnSave').style.display = isLast ? '' : 'none';
    if (isLast) { spcBuildSummary(); document.getElementById('spcSummaryWrap').style.display = ''; }
}
function spcGoStep(i){ spcUpdateStep(i); }
function spcNext(){ if (spcStep < SPC_TOTAL-1 && spcValidate()) spcUpdateStep(spcStep+1); }
function spcPrev(){ if (spcStep > 0) spcUpdateStep(spcStep-1); }

function spcValidate() {
    if (spcStep === 0) {
        var fn = document.getElementById('spc_fn').value.trim();
        var ln = document.getElementById('spc_ln').value.trim();
        if (!fn || !ln) {
            Swal.fire({icon:'warning',title:'Required',text:'Please enter your first and last name.'});
            if (!fn) document.getElementById('spc_fn').classList.add('is-invalid');
            if (!ln) document.getElementById('spc_ln').classList.add('is-invalid');
            return false;
        }
        document.getElementById('spc_fn').classList.remove('is-invalid');
        document.getElementById('spc_ln').classList.remove('is-invalid');
    }
    if (spcStep === 1) {
        var ph = document.getElementById('spc_phone').value.replace(/\D/g,'');
        if (ph && ph.length !== 12) {
            Swal.fire({icon:'warning',title:'Invalid Phone',text:'Phone number must be exactly 12 digits (e.g. 255712345678).'});
            document.getElementById('spc_phone').classList.add('is-invalid');
            return false;
        }
        document.getElementById('spc_phone').classList.remove('is-invalid');
    }
    if (spcStep === 2) {
        var ml = document.getElementById('spc_main_level').value;
        if (!ml) {
            Swal.fire({icon:'warning',title:'Required',text:'Please select your education level.'});
            document.getElementById('spc_main_level').classList.add('is-invalid');
            return false;
        }
        document.getElementById('spc_main_level').classList.remove('is-invalid');
    }
    return true;
}

/* ── Phone auto-format (12 digits: 255XXXXXXXXX) ── */
function spcPhoneInit() {
    var inp = document.getElementById('spc_phone');
    var lenEl = document.getElementById('spcPhoneLen');
    var hintEl = document.getElementById('spcPhoneHint');
    if (!inp) return;

    function formatPhone(raw) {
        /* Strip everything except digits */
        var digits = raw.replace(/\D/g,'');
        /* Normalise: strip leading + or 0 then prepend 255 if not starting with 255 */
        if (digits.startsWith('0')) digits = '255' + digits.slice(1);
        if (!digits.startsWith('255') && digits.length > 0) digits = '255' + digits;
        /* Cap at 12 digits */
        return digits.slice(0, 12);
    }

    function update() {
        var formatted = formatPhone(inp.value);
        inp.value = formatted;
        var len = formatted.length;
        if (lenEl) {
            lenEl.textContent = len + '/12';
            lenEl.className = 'spc-phone-len ' + (len===12?'valid':(len>0?'invalid':''));
        }
        if (hintEl) {
            if (len === 12) {
                hintEl.innerHTML = '<i class="bi bi-check-circle-fill me-1" style="color:#059669"></i>Valid — +' + formatted;
                hintEl.style.color = '#059669';
            } else if (len > 0) {
                hintEl.innerHTML = '<i class="bi bi-exclamation-circle me-1" style="color:#ef4444"></i>Must be 12 digits (255XXXXXXXXX)';
                hintEl.style.color = '#ef4444';
            } else {
                hintEl.innerHTML = '<i class="bi bi-info-circle me-1"></i>Format: 255XXXXXXXXX (e.g. 255712345678)';
                hintEl.style.color = '#94a3b8';
            }
        }
    }

    inp.addEventListener('input', update);
    inp.addEventListener('blur',  update);
    inp.addEventListener('paste', function(e) {
        e.preventDefault();
        var pasted = (e.clipboardData||window.clipboardData).getData('text');
        inp.value = pasted;
        update();
    });
    /* Run on load to format existing value */
    if (inp.value) update();
}

/* ── Photo upload ── */
document.getElementById('spcImgInput').addEventListener('change', function() {
    var file = this.files[0];
    if (!file) return;
    if (file.size > 2*1024*1024) { Swal.fire({icon:'warning',title:'Too large',text:'Choose an image under 2 MB.'}); return; }
    var reader = new FileReader();
    reader.onload = function(e) {
        spcBase64 = e.target.result;
        var prev = document.getElementById('spcPhotoPreview');
        var empt = document.getElementById('spcPhotoEmpty');
        if (prev) { prev.src = spcBase64; prev.style.display = ''; }
        if (empt) empt.style.display = 'none';
        document.getElementById('spcPhotoZone').classList.add('has-img');
        var mini = document.getElementById('spcAvatarSmall');
        if (mini) {
            if (mini.tagName === 'IMG') { mini.src = spcBase64; }
            else { var img = document.createElement('img'); img.className='spc-avatar'; img.id='spcAvatarSmall'; img.src=spcBase64; mini.replaceWith(img); }
        }
    };
    reader.readAsDataURL(file);
});

/* ── Skills tags ── */
function spcTagInput() {
    var inp = document.getElementById('spcTagInput');
    inp.addEventListener('keydown', function(e) {
        if (e.key==='Enter'||e.key===',') {
            e.preventDefault();
            var val = this.value.trim().replace(/,/g,'');
            if (val && !spcTags.includes(val)) { spcTags.push(val); spcRenderTags(spcTags); }
            this.value = '';
        }
        if (e.key==='Backspace' && !this.value && spcTags.length) { spcTags.pop(); spcRenderTags(spcTags); }
    });
}
function spcRenderTags(tags) {
    spcTags = tags.filter(Boolean);
    var wrap = document.getElementById('spcTagsWrap');
    var inp  = document.getElementById('spcTagInput');
    var chips = spcTags.map(function(t,i){ return '<span class="spc-tag">'+spcEsc(t)+'<span class="spc-tag-del" onclick="spcRemoveTag('+i+')">×</span></span>'; }).join('');
    wrap.innerHTML = chips;
    wrap.appendChild(inp);
    document.getElementById('spc_skill').value = spcTags.join(', ');
}
function spcRemoveTag(i){ spcTags.splice(i,1); spcRenderTags(spcTags); }

/* ── Sub-levels ── */
function spcLoadSubLevels(mainId) {
    var sel = document.getElementById('spc_sub_level');
    sel.innerHTML = '<option>Loading…</option>';
    if (!mainId) { sel.innerHTML = '<option value="">— Select class/course —</option>'; return; }
    var fd = new FormData(); fd.append('main_id', mainId);
    fetch('ajax/ajax_get_sub_levels.php', {method:'POST', body:fd})
        .then(function(r){return r.text();})
        .then(function(html){ sel.innerHTML = '<option value="">— Select class/course —</option>' + html; })
        .catch(function(){ sel.innerHTML = '<option value="">Error loading</option>'; });
}

/* ── Summary ── */
function spcBuildSummary() {
    /* Strips placeholder options like "— Select level —" — returns '—' instead */
    function selText(id) {
        var t = document.getElementById(id)?.selectedOptions[0]?.text || '';
        return (t.trim().startsWith('—') || !t) ? '—' : t;
    }
    var ph = document.getElementById('spc_phone').value;
    var items = [
        ['Name',   [document.getElementById('spc_fn').value,document.getElementById('spc_mn').value,document.getElementById('spc_ln').value].filter(Boolean).join(' ') || '—'],
        ['DOB',    document.getElementById('spc_dob').value || '—'],
        ['Phone',  ph ? '+' + ph : '—'],
        ['Parent', document.getElementById('spc_pname').value || '—'],
        ['Skills', document.getElementById('spc_skill').value || '—'],
        ['Level',  selText('spc_main_level')],
        ['Class',  selText('spc_sub_level')],
        ['Year',   document.getElementById('spc_start_year').value],
        ['Status', document.getElementById('spc_status').value],
    ];
    document.getElementById('spcSummary').innerHTML = items.map(function(pair){
        return '<div class="col-6 col-md-4"><div class="spc-summary-lbl">'+pair[0]+'</div><div class="spc-summary-val">'+spcEsc(String(pair[1]))+'</div></div>';
    }).join('');
}

/* ── Save ── */
function spcSave() {
    var payload = {
        id:                  SPC_USR,
        first_name:          document.getElementById('spc_fn').value.trim(),
        middle_name:         document.getElementById('spc_mn').value.trim(),
        last_name:           document.getElementById('spc_ln').value.trim(),
        dob:                 document.getElementById('spc_dob').value,
        description:         document.getElementById('spc_desc').value.trim(),
        skill:               document.getElementById('spc_skill').value,
        parent_name:         document.getElementById('spc_pname').value.trim(),
        phone:               document.getElementById('spc_phone').value.replace(/\D/g,''),
        email:               document.getElementById('spc_email').value.trim(),
        school:              document.getElementById('spc_street').value.trim(),
        course:              document.getElementById('spc_city').value.trim(),
        main_academic_level: document.getElementById('spc_main_level').value,
        sub_academic_level:  document.getElementById('spc_sub_level').value,
        start_year:          document.getElementById('spc_start_year').value,
        end_year:            document.getElementById('spc_status').value,
        image:               spcBase64,
    };
    if (!payload.first_name || !payload.last_name) {
        Swal.fire({icon:'warning',title:'Required',text:'First and last name are required.'}); spcUpdateStep(0); return;
    }
    if (payload.phone && payload.phone.length !== 12) {
        Swal.fire({icon:'warning',title:'Invalid Phone',text:'Phone number must be exactly 12 digits.'}); spcUpdateStep(1); return;
    }
    Swal.fire({title:'Saving…',allowOutsideClick:false,didOpen:function(){Swal.showLoading();}});
    fetch('ajax/ajax_save_student.php', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)})
        .then(function(r){return r.json();})
        .then(function(res){
            Swal.close();
            if (res.status==='success') {
                Swal.fire({icon:'success',title:'Profile saved!',text:res.message||'Your profile has been updated.',confirmButtonText:'Done',confirmButtonColor:'#4f46e5'})
                    .then(function(){ window.location.reload(); });
            } else {
                Swal.fire({icon:'error',title:'Save failed',text:res.message||'Something went wrong.'});
            }
        })
        .catch(function(){ Swal.fire({icon:'error',title:'Network error',text:'Could not reach the server.'}); });
}

function spcEsc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

Object.assign(window, { spcGoStep,spcNext,spcPrev,spcSave,spcExitEdit,spcEnterEdit,spcLoadSubLevels,spcRemoveTag });
</script>
