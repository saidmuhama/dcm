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
$_ring_off = round(100 - $_pct, 1); /* stroke-dashoffset for SVG ring */
?>
<style>
/* ══ Student Profile Completion (spc-*) ══════════════════════════ */
.spc-wrap  { font-family:'Open Sans',sans-serif;max-width:960px;margin:0 auto;padding:1.5rem 1rem 3rem; }

/* ── Top header card ── */
.spc-header { background:#fff;border:1px solid #e8edf3;border-radius:20px;padding:1.5rem 1.75rem;
              display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;margin-bottom:1.5rem;
              box-shadow:0 2px 12px rgba(0,0,0,.05); }
.spc-avatar-wrap { position:relative;flex-shrink:0; }
.spc-avatar { width:84px;height:84px;border-radius:50%;object-fit:cover;border:3px solid #e2e8f0;
              background:#f1f5f9;display:block; }
.spc-avatar-empty { width:84px;height:84px;border-radius:50%;border:3px dashed #cbd5e1;
                    background:#f8fafc;display:flex;align-items:center;justify-content:center;
                    font-size:2rem;color:#cbd5e1; }
.spc-avatar-upload { position:absolute;bottom:0;right:0;width:28px;height:28px;border-radius:50%;
                     background:#4f46e5;color:#fff;border:2px solid #fff;display:flex;
                     align-items:center;justify-content:center;cursor:pointer;font-size:.7rem;
                     box-shadow:0 2px 6px rgba(79,70,229,.4);transition:background .15s; }
.spc-avatar-upload:hover { background:#3730a3; }
.spc-header-info { flex:1;min-width:150px; }
.spc-header-name { font-size:1.1rem;font-weight:800;color:#0f172a;font-family:'SUSE',sans-serif;margin-bottom:.1rem; }
.spc-header-sub  { font-size:.78rem;color:#94a3b8;font-weight:600; }
.spc-ring-wrap   { flex-shrink:0;text-align:center; }
.spc-ring        { width:72px;height:72px; }
.spc-ring-lbl    { font-size:.65rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-top:.25rem; }
.spc-pct-text    { font-size:.9rem;font-weight:900;fill:#0f172a;font-family:'SUSE',sans-serif; }

/* ── Step wizard ── */
.spc-wizard { background:#fff;border:1px solid #e8edf3;border-radius:20px;overflow:hidden;
              box-shadow:0 2px 12px rgba(0,0,0,.05); }
.spc-steps  { display:flex;align-items:center;padding:1.25rem 1.75rem;border-bottom:1px solid #f0f4f8;
              gap:0;overflow-x:auto; }
.spc-step-item { display:flex;align-items:center;flex:1;min-width:0; }
.spc-step-btn  { display:flex;align-items:center;gap:.6rem;cursor:pointer;flex-shrink:0;
                 padding:.3rem .5rem;border-radius:10px;transition:background .15s;border:none;background:transparent; }
.spc-step-btn:hover { background:#f8fafc; }
.spc-step-num  { width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;
                 font-size:.78rem;font-weight:800;flex-shrink:0;border:2px solid #e2e8f0;
                 background:#f8fafc;color:#94a3b8;transition:all .25s; }
.spc-step-item.active .spc-step-num  { background:#4f46e5;border-color:#4f46e5;color:#fff; }
.spc-step-item.done   .spc-step-num  { background:#059669;border-color:#059669;color:#fff; }
.spc-step-label { display:flex;flex-direction:column;text-align:left; }
.spc-step-title { font-size:.78rem;font-weight:700;color:#64748b;white-space:nowrap; }
.spc-step-sub   { font-size:.65rem;color:#94a3b8;white-space:nowrap; }
.spc-step-item.active .spc-step-title { color:#4f46e5; }
.spc-step-item.done   .spc-step-title { color:#059669; }
.spc-connector { flex:1;height:2px;background:#e2e8f0;margin:0 .5rem;min-width:20px;transition:background .3s; }
.spc-step-item.done + .spc-connector,
.spc-connector.done { background:#059669; }
@media(max-width:576px){.spc-step-label{display:none;}.spc-step-btn{padding:.3rem;}}

/* ── Progress bar ── */
.spc-progress { height:3px;background:#f0f4f8; }
.spc-progress-bar { height:100%;background:linear-gradient(90deg,#4f46e5,#7c3aed);transition:width .4s cubic-bezier(.16,1,.3,1); }

/* ── Step panels ── */
.spc-body   { padding:1.75rem; }
.spc-panel  { display:none;animation:spc-fade .3s ease both; }
.spc-panel.active { display:block; }
@keyframes spc-fade { from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)} }

/* ── Section headers ── */
.spc-section-title { font-size:.72rem;font-weight:800;color:#94a3b8;text-transform:uppercase;
                     letter-spacing:.07em;margin-bottom:1rem;margin-top:.25rem;
                     display:flex;align-items:center;gap:.5rem; }
.spc-section-title::after { content:'';flex:1;height:1px;background:#f0f4f8; }

/* ── Photo uploader ── */
.spc-photo-zone { display:flex;flex-direction:column;align-items:center;gap:.75rem;padding:1.25rem;
                  border:2px dashed #e2e8f0;border-radius:16px;cursor:pointer;transition:all .2s;
                  background:#f8fafc;text-align:center; }
.spc-photo-zone:hover { border-color:#4f46e5;background:#f5f3ff; }
.spc-photo-zone.has-img { border-style:solid;border-color:#e2e8f0;background:#fff; }
.spc-photo-large { width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid #e2e8f0; }
.spc-photo-empty { width:100px;height:100px;border-radius:50%;background:#e2e8f0;
                   display:flex;align-items:center;justify-content:center;font-size:2.5rem;color:#94a3b8; }
.spc-photo-hint  { font-size:.75rem;color:#64748b;font-weight:600; }

/* ── Tags input ── */
.spc-tags-wrap { display:flex;flex-wrap:wrap;gap:.4rem;align-items:center;
                 border:1px solid #ced4da;border-radius:.375rem;padding:.5rem .75rem;
                 background:#fff;min-height:46px;cursor:text; }
.spc-tags-wrap:focus-within { border-color:#86b7fe;box-shadow:0 0 0 .25rem rgba(13,110,253,.25); }
.spc-tag { display:inline-flex;align-items:center;gap:.3rem;background:#ede9fe;color:#4f46e5;
           border-radius:100px;padding:.15rem .65rem;font-size:.75rem;font-weight:700; }
.spc-tag-del { cursor:pointer;font-size:.9rem;line-height:1;opacity:.6;transition:opacity .15s; }
.spc-tag-del:hover { opacity:1; }
.spc-tag-input { border:none;outline:none;font-size:.85rem;min-width:80px;flex:1;background:transparent;
                 font-family:inherit;color:#0f172a; }

/* ── Footer actions ── */
.spc-footer { display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.75rem;
              border-top:1px solid #f0f4f8;background:#fafbfd;flex-wrap:wrap;gap:.75rem; }
.spc-btn { display:inline-flex;align-items:center;gap:.4rem;border-radius:10px;padding:.6rem 1.3rem;
           font-size:.82rem;font-weight:700;cursor:pointer;border:none;font-family:inherit;transition:all .18s; }
.spc-btn-ghost  { background:#f1f5f9;color:#475569;border:1px solid #e2e8f0; }
.spc-btn-ghost:hover { background:#e2e8f0; }
.spc-btn-ghost:disabled { opacity:.4;cursor:not-allowed; }
.spc-btn-next   { background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;box-shadow:0 4px 12px rgba(79,70,229,.3); }
.spc-btn-next:hover { filter:brightness(1.08); }
.spc-btn-save   { background:linear-gradient(135deg,#059669,#0d9488);color:#fff;box-shadow:0 4px 12px rgba(5,150,105,.3); }
.spc-btn-save:hover { filter:brightness(1.08); }
.spc-step-counter { font-size:.75rem;font-weight:700;color:#94a3b8; }
</style>

<div class="spc-wrap">

  <!-- ── Header ── -->
  <div class="spc-header">
    <div class="spc-avatar-wrap" onclick="document.getElementById('spcImgInput').click()" title="Change photo">
      <?php if ($_img_src): ?>
        <img class="spc-avatar" id="spcAvatarSmall" src="<?= $_img_src ?>" alt="Profile">
      <?php else: ?>
        <div class="spc-avatar-empty" id="spcAvatarSmall"><i class="bi bi-person"></i></div>
      <?php endif; ?>
      <div class="spc-avatar-upload"><i class="bi bi-camera-fill"></i></div>
    </div>
    <div class="spc-header-info">
      <div class="spc-header-name"><?= htmlspecialchars($_SESSION['name'] ?? 'Student') ?></div>
      <div class="spc-header-sub">Complete your profile to get the best experience</div>
      <div style="margin-top:.5rem">
        <div style="height:6px;background:#f0f4f8;border-radius:100px;width:180px;overflow:hidden">
          <div style="height:100%;width:<?= $_pct ?>%;background:linear-gradient(90deg,#4f46e5,#7c3aed);border-radius:100px;transition:width 1s cubic-bezier(.16,1,.3,1)"></div>
        </div>
      </div>
    </div>
    <div class="spc-ring-wrap">
      <svg class="spc-ring" viewBox="0 0 36 36">
        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e2e8f0" stroke-width="3.2"/>
        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#4f46e5" stroke-width="3.2"
                stroke-dasharray="100 100" stroke-dashoffset="<?= $_ring_off ?>"
                stroke-linecap="round" id="spcRingCircle"
                style="transform:rotate(-90deg);transform-origin:center;transition:stroke-dashoffset 1.4s cubic-bezier(.16,1,.3,1)"/>
        <text x="18" y="22" text-anchor="middle" class="spc-pct-text" id="spcRingText"><?= $_pct ?>%</text>
      </svg>
      <div class="spc-ring-lbl">Complete</div>
    </div>
    <a href="?view=3002" class="spc-btn spc-btn-ghost ms-auto" style="align-self:flex-start">
      <i class="bi bi-skip-forward"></i>Skip for now
    </a>
  </div>

  <!-- ── Wizard ── -->
  <div class="spc-wizard" id="spcWizard">

    <!-- Step tabs -->
    <div class="spc-steps" id="spcStepsTabs">
      <div class="spc-step-item active" id="spcTab_0">
        <button class="spc-step-btn" onclick="spcGoStep(0)">
          <div class="spc-step-num" id="spcNum_0"><i class="bi bi-person-fill"></i></div>
          <div class="spc-step-label">
            <span class="spc-step-title">Personal</span>
            <span class="spc-step-sub">Name &amp; photo</span>
          </div>
        </button>
      </div>
      <div class="spc-connector" id="spcConn_0"></div>
      <div class="spc-step-item" id="spcTab_1">
        <button class="spc-step-btn" onclick="spcGoStep(1)">
          <div class="spc-step-num" id="spcNum_1"><i class="bi bi-telephone-fill"></i></div>
          <div class="spc-step-label">
            <span class="spc-step-title">Contact</span>
            <span class="spc-step-sub">Parent &amp; address</span>
          </div>
        </button>
      </div>
      <div class="spc-connector" id="spcConn_1"></div>
      <div class="spc-step-item" id="spcTab_2">
        <button class="spc-step-btn" onclick="spcGoStep(2)">
          <div class="spc-step-num" id="spcNum_2"><i class="bi bi-mortarboard-fill"></i></div>
          <div class="spc-step-label">
            <span class="spc-step-title">Academics</span>
            <span class="spc-step-sub">Level &amp; course</span>
          </div>
        </button>
      </div>
    </div>
    <div class="spc-progress"><div class="spc-progress-bar" id="spcProgressBar" style="width:33.3%"></div></div>

    <!-- ── Step 1: Personal ── -->
    <div class="spc-body">
      <div class="spc-panel active" id="spcPanel_0">
        <div class="row g-3">
          <div class="col-12 col-sm-4 col-md-3">
            <div class="spc-section-title">Photo</div>
            <div class="spc-photo-zone <?= $_img_src ? 'has-img' : '' ?>" id="spcPhotoZone" onclick="document.getElementById('spcImgInput').click()">
              <?php if ($_img_src): ?>
                <img class="spc-photo-large" id="spcPhotoPreview" src="<?= $_img_src ?>" alt="">
              <?php else: ?>
                <div class="spc-photo-empty" id="spcPhotoEmpty"><i class="bi bi-person"></i></div>
                <img class="spc-photo-large" id="spcPhotoPreview" src="" alt="" style="display:none">
              <?php endif; ?>
              <div class="spc-photo-hint"><i class="bi bi-upload"></i> Click to upload photo<br><span style="font-weight:400;color:#94a3b8">JPG, PNG — max 2 MB</span></div>
            </div>
            <input type="file" id="spcImgInput" accept="image/*" style="display:none">
          </div>
          <div class="col-12 col-sm-8 col-md-9">
            <div class="spc-section-title">Basic Details</div>
            <div class="row g-3">
              <div class="col-12 col-md-4">
                <div class="form-floating">
                  <input class="form-control" id="spc_fn" placeholder="First Name" value="<?= $_v('first_name') ?>">
                  <label>First Name <span class="text-danger">*</span></label>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="form-floating">
                  <input class="form-control" id="spc_mn" placeholder="Middle Name" value="<?= $_v('middle_name') ?>">
                  <label>Middle Name <span style="font-size:.7rem;color:#94a3b8">(optional)</span></label>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="form-floating">
                  <input class="form-control" id="spc_ln" placeholder="Last Name" value="<?= $_v('last_name') ?>">
                  <label>Last Name <span class="text-danger">*</span></label>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="form-floating">
                  <input type="date" class="form-control" id="spc_dob" value="<?= $_v('dob') ?>">
                  <label>Date of Birth</label>
                </div>
              </div>
              <div class="col-12 col-md-8">
                <div class="form-floating">
                  <textarea class="form-control" id="spc_desc" style="height:56px" placeholder="About you"><?= $_v('description') ?></textarea>
                  <label>Short Bio</label>
                </div>
              </div>
              <div class="col-12">
                <div class="spc-section-title" style="margin-top:.25rem">Skills / Interests</div>
                <div class="spc-tags-wrap" id="spcTagsWrap" onclick="document.getElementById('spcTagInput').focus()">
                  <input class="spc-tag-input" id="spcTagInput" placeholder="Type a skill, press Enter…" autocomplete="off">
                </div>
                <div style="font-size:.68rem;color:#94a3b8;margin-top:.3rem"><i class="bi bi-lightbulb"></i> Press Enter or comma to add a skill</div>
                <input type="hidden" id="spc_skill" value="<?= $_v('skill') ?>">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Step 2: Contact ── -->
      <div class="spc-panel" id="spcPanel_1">
        <div class="spc-section-title">Parent / Guardian Contact</div>
        <div class="row g-3 mb-3">
          <div class="col-12 col-md-4">
            <div class="form-floating">
              <input class="form-control" id="spc_pname" placeholder="Parent Full Name" value="<?= $_v('parent_name') ?>">
              <label>Parent / Guardian Name</label>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="form-floating">
              <input class="form-control" id="spc_phone" placeholder="Phone" value="<?= $_v('phone') ?>">
              <label>Phone Number</label>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="form-floating">
              <input type="email" class="form-control" id="spc_email" placeholder="Email" value="<?= $_v('email') ?>">
              <label>Email Address</label>
            </div>
          </div>
        </div>
        <div class="spc-section-title">Home Address</div>
        <div class="row g-3">
          <div class="col-12 col-md-6 col-lg-4">
            <div class="form-floating">
              <input class="form-control" id="spc_street" placeholder="Street" value="<?= $_v('school') ?>">
              <label>Street / Area</label>
            </div>
          </div>
          <div class="col-12 col-md-6 col-lg-4">
            <div class="form-floating">
              <input class="form-control" id="spc_town" placeholder="Town" value="">
              <label>Town / District</label>
            </div>
          </div>
          <div class="col-12 col-md-6 col-lg-4">
            <div class="form-floating">
              <input class="form-control" id="spc_city" placeholder="City" value="<?= $_v('course') ?>">
              <label>City / Region</label>
            </div>
          </div>
          <div class="col-12 col-md-6 col-lg-4">
            <div class="form-floating">
              <input class="form-control" id="spc_country" placeholder="Country" value="Tanzania">
              <label>Country</label>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Step 3: Academics ── -->
      <div class="spc-panel" id="spcPanel_2">
        <div class="spc-section-title">Current Academic Level</div>
        <div class="row g-3">
          <div class="col-12 col-md-6 col-lg-4">
            <div class="form-floating">
              <select class="form-select" id="spc_main_level" onchange="spcLoadSubLevels(this.value)">
                <option value="">— Select level —</option>
                <?php foreach ($_main_levels as $_ml): ?>
                <option value="<?= $_ml['id'] ?>" <?= ($_mal_sel == $_ml['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($_ml['level_title']) ?>
                </option>
                <?php endforeach; ?>
              </select>
              <label>Education Level <span class="text-danger">*</span></label>
            </div>
          </div>
          <div class="col-12 col-md-6 col-lg-4">
            <div class="form-floating">
              <select class="form-select" id="spc_sub_level">
                <option value="">— Select class/course —</option>
                <?php foreach ($_sub_levels as $_sl): ?>
                <option value="<?= $_sl['id'] ?>" <?= ($_sal_sel == $_sl['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($_sl['sub_level_title']) ?>
                </option>
                <?php endforeach; ?>
              </select>
              <label>Class / Course</label>
            </div>
          </div>
          <div class="col-12 col-md-4 col-lg-2">
            <div class="form-floating">
              <select class="form-select" id="spc_start_year">
                <?php for ($y = date('Y'); $y >= 2015; $y--): ?>
                <option value="<?= $y ?>" <?= ($_start_yr == $y) ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
              </select>
              <label>Start Year</label>
            </div>
          </div>
          <div class="col-12 col-md-4 col-lg-2">
            <div class="form-floating">
              <select class="form-select" id="spc_status">
                <?php foreach (['Continuing','Passed','Completed','Deferred','Dropped'] as $_s): ?>
                <option value="<?= $_s ?>" <?= ($_end_yr === $_s) ? 'selected' : '' ?>><?= $_s ?></option>
                <?php endforeach; ?>
              </select>
              <label>Status</label>
            </div>
          </div>
        </div>

        <!-- Summary card -->
        <div style="margin-top:1.5rem;padding:1rem 1.25rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px">
          <div style="font-size:.75rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.75rem">
            <i class="bi bi-check2-circle me-1"></i>Profile Summary
          </div>
          <div class="row g-2" id="spcSummary">
            <!-- filled by JS -->
          </div>
        </div>
      </div>
    </div><!-- /.spc-body -->

    <!-- Footer nav -->
    <div class="spc-footer">
      <button class="spc-btn spc-btn-ghost" id="spcBtnPrev" onclick="spcPrev()" disabled>
        <i class="bi bi-chevron-left"></i>Previous
      </button>
      <span class="spc-step-counter" id="spcStepCounter">Step 1 of 3</span>
      <div style="display:flex;gap:.6rem">
        <button class="spc-btn spc-btn-next" id="spcBtnNext" onclick="spcNext()">
          Next<i class="bi bi-chevron-right"></i>
        </button>
        <button class="spc-btn spc-btn-save" id="spcBtnSave" style="display:none" onclick="spcSave()">
          <i class="bi bi-check2-circle"></i>Save Profile
        </button>
      </div>
    </div>

  </div><!-- /.spc-wizard -->
</div><!-- /.spc-wrap -->

<script>
/* ════════════════════════════════════════════════════════════
   STATE
════════════════════════════════════════════════════════════ */
let spcStep     = 0;
const SPC_TOTAL = 3;
let spcBase64   = '';
let spcTags     = [];
const SPC_USR   = <?= json_encode($_usr) ?>;

/* ════════════════════════════════════════════════════════════
   INIT
════════════════════════════════════════════════════════════ */
function _spcInit() {
  spcRenderTags(<?= json_encode(array_filter(array_map('trim', explode(',', $_st['skill'] ?? '')))) ?>);
  spcTagInput();
  spcUpdateStep(0);
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', _spcInit);
} else { _spcInit(); }

/* ════════════════════════════════════════════════════════════
   STEP NAVIGATION
════════════════════════════════════════════════════════════ */
function spcUpdateStep(i) {
  spcStep = i;

  /* panels */
  document.querySelectorAll('.spc-panel').forEach((p,idx) => p.classList.toggle('active', idx === i));

  /* tabs */
  for (let t = 0; t < SPC_TOTAL; t++) {
    const tab = document.getElementById('spcTab_' + t);
    const num = document.getElementById('spcNum_' + t);
    tab.classList.remove('active','done');
    if (t < i)  { tab.classList.add('done');   num.innerHTML = '<i class="bi bi-check2"></i>'; }
    if (t === i) { tab.classList.add('active'); }
    if (t > i) {
      const icons = ['bi-person-fill','bi-telephone-fill','bi-mortarboard-fill'];
      num.innerHTML = `<i class="bi ${icons[t]}"></i>`;
    }
  }

  /* connectors */
  for (let c = 0; c < SPC_TOTAL - 1; c++) {
    const conn = document.getElementById('spcConn_' + c);
    conn.classList.toggle('done', c < i);
    conn.style.background = c < i ? '#059669' : '#e2e8f0';
  }

  /* progress bar */
  document.getElementById('spcProgressBar').style.width = (((i + 1) / SPC_TOTAL) * 100) + '%';

  /* counter */
  document.getElementById('spcStepCounter').textContent = `Step ${i + 1} of ${SPC_TOTAL}`;

  /* buttons */
  document.getElementById('spcBtnPrev').disabled = (i === 0);
  const isLast = (i === SPC_TOTAL - 1);
  document.getElementById('spcBtnNext').style.display = isLast ? 'none' : '';
  document.getElementById('spcBtnSave').style.display = isLast ? '' : 'none';

  if (isLast) spcBuildSummary();
}

function spcGoStep(i) { spcUpdateStep(i); }
function spcNext() { if (spcStep < SPC_TOTAL - 1 && spcValidate()) spcUpdateStep(spcStep + 1); }
function spcPrev() { if (spcStep > 0) spcUpdateStep(spcStep - 1); }

function spcValidate() {
  if (spcStep === 0) {
    const fn = document.getElementById('spc_fn').value.trim();
    const ln = document.getElementById('spc_ln').value.trim();
    if (!fn || !ln) {
      Swal.fire({ icon:'warning', title:'Required fields', text:'Please enter your first and last name.', customClass:{popup:'ds-pop'} });
      return false;
    }
  }
  if (spcStep === 2) {
    const ml = document.getElementById('spc_main_level').value;
    if (!ml) {
      Swal.fire({ icon:'warning', title:'Required field', text:'Please select your education level.', customClass:{popup:'ds-pop'} });
      return false;
    }
  }
  return true;
}

/* ════════════════════════════════════════════════════════════
   PHOTO UPLOAD
════════════════════════════════════════════════════════════ */
document.getElementById('spcImgInput').addEventListener('change', function () {
  const file = this.files[0];
  if (!file) return;
  if (file.size > 2 * 1024 * 1024) {
    Swal.fire({ icon:'warning', title:'File too large', text:'Please choose an image under 2 MB.' });
    return;
  }
  const reader = new FileReader();
  reader.onload = function (e) {
    spcBase64 = e.target.result;

    /* preview in photo zone (step 1) */
    const prev = document.getElementById('spcPhotoPreview');
    const empt = document.getElementById('spcPhotoEmpty');
    if (prev) { prev.src = spcBase64; prev.style.display = ''; }
    if (empt) empt.style.display = 'none';
    document.getElementById('spcPhotoZone').classList.add('has-img');

    /* mini avatar in header */
    const mini = document.getElementById('spcAvatarSmall');
    if (mini) {
      if (mini.tagName === 'IMG') mini.src = spcBase64;
      else {
        const img = document.createElement('img');
        img.className = 'spc-avatar'; img.id = 'spcAvatarSmall'; img.src = spcBase64;
        mini.replaceWith(img);
      }
    }
  };
  reader.readAsDataURL(file);
});

/* ════════════════════════════════════════════════════════════
   TAGS
════════════════════════════════════════════════════════════ */
function spcTagInput() {
  const inp = document.getElementById('spcTagInput');
  inp.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' || e.key === ',') {
      e.preventDefault();
      const val = this.value.trim().replace(/,/g,'');
      if (val && !spcTags.includes(val)) { spcTags.push(val); spcRenderTags(spcTags); }
      this.value = '';
    }
    if (e.key === 'Backspace' && !this.value && spcTags.length) {
      spcTags.pop(); spcRenderTags(spcTags);
    }
  });
}

function spcRenderTags(tags) {
  spcTags = tags.filter(Boolean);
  const wrap = document.getElementById('spcTagsWrap');
  const inp  = document.getElementById('spcTagInput');
  const chips = spcTags.map((t,i) =>
    `<span class="spc-tag">${spcEsc(t)}<span class="spc-tag-del" onclick="spcRemoveTag(${i})">×</span></span>`
  ).join('');
  wrap.innerHTML = chips;
  wrap.appendChild(inp);
  document.getElementById('spc_skill').value = spcTags.join(', ');
}

function spcRemoveTag(i) { spcTags.splice(i,1); spcRenderTags(spcTags); }

/* ════════════════════════════════════════════════════════════
   SUB-LEVELS AJAX
════════════════════════════════════════════════════════════ */
function spcLoadSubLevels(mainId) {
  const sel = document.getElementById('spc_sub_level');
  sel.innerHTML = '<option>Loading…</option>';
  if (!mainId) { sel.innerHTML = '<option value="">— Select class/course —</option>'; return; }
  const fd = new FormData(); fd.append('main_id', mainId);
  fetch('ajax/ajax_get_sub_levels.php', { method:'POST', body:fd })
    .then(r => r.text()).then(html => { sel.innerHTML = '<option value="">— Select class/course —</option>' + html; })
    .catch(() => { sel.innerHTML = '<option value="">Error loading</option>'; });
}

/* ════════════════════════════════════════════════════════════
   SUMMARY (step 3 review card)
════════════════════════════════════════════════════════════ */
function spcBuildSummary() {
  const items = [
    ['Name', [document.getElementById('spc_fn').value, document.getElementById('spc_mn').value, document.getElementById('spc_ln').value].filter(Boolean).join(' ')],
    ['DOB',  document.getElementById('spc_dob').value || '—'],
    ['Parent', document.getElementById('spc_pname').value || '—'],
    ['Phone', document.getElementById('spc_phone').value || '—'],
    ['Skills', document.getElementById('spc_skill').value || '—'],
    ['Level', document.getElementById('spc_main_level').selectedOptions[0]?.text || '—'],
    ['Class', document.getElementById('spc_sub_level').selectedOptions[0]?.text || '—'],
    ['Year',  document.getElementById('spc_start_year').value],
    ['Status',document.getElementById('spc_status').value],
  ];
  document.getElementById('spcSummary').innerHTML = items.map(([k,v]) => `
    <div class="col-6 col-md-4">
      <div style="font-size:.65rem;font-weight:700;color:#94a3b8;text-transform:uppercase">${k}</div>
      <div style="font-size:.8rem;font-weight:600;color:#0f172a;margin-top:.1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${spcEsc(String(v))}</div>
    </div>`).join('');
}

/* ════════════════════════════════════════════════════════════
   SAVE
════════════════════════════════════════════════════════════ */
function spcSave() {
  const payload = {
    id:                   SPC_USR,
    first_name:           document.getElementById('spc_fn').value.trim(),
    middle_name:          document.getElementById('spc_mn').value.trim(),
    last_name:            document.getElementById('spc_ln').value.trim(),
    dob:                  document.getElementById('spc_dob').value,
    description:          document.getElementById('spc_desc').value.trim(),
    skill:                document.getElementById('spc_skill').value,
    parent_name:          document.getElementById('spc_pname').value.trim(),
    phone:                document.getElementById('spc_phone').value.trim(),
    email:                document.getElementById('spc_email').value.trim(),
    school:               document.getElementById('spc_street').value.trim(),
    course:               document.getElementById('spc_city').value.trim(),
    main_academic_level:  document.getElementById('spc_main_level').value,
    sub_academic_level:   document.getElementById('spc_sub_level').value,
    start_year:           document.getElementById('spc_start_year').value,
    end_year:             document.getElementById('spc_status').value,
    image:                spcBase64,
  };

  if (!payload.first_name || !payload.last_name) {
    Swal.fire({ icon:'warning', title:'Required', text:'First and last name are required.', customClass:{popup:'ds-pop'} });
    spcUpdateStep(0); return;
  }

  Swal.fire({ title:'Saving…', allowOutsideClick:false, customClass:{popup:'ds-pop'}, didOpen:()=>Swal.showLoading() });

  fetch('ajax/ajax_save_student.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify(payload)
  })
  .then(r => r.json())
  .then(res => {
    Swal.close();
    if (res.status === 'success') {
      /* update ring to reflect completion */
      spcAnimateRing(<?= $_pct ?>, Math.min(100, <?= $_pct ?> + 20));
      Swal.fire({
        icon:'success', title:'Profile saved!',
        text: res.message || 'Your profile has been updated.',
        confirmButtonText:'Go to Dashboard',
        confirmButtonColor:'#4f46e5',
        customClass:{popup:'ds-pop',confirmButton:'ds-btn'}
      }).then(() => { window.location.href = '?view=3002'; });
    } else {
      Swal.fire({ icon:'error', title:'Save failed', text: res.message || 'Something went wrong.', customClass:{popup:'ds-pop'} });
    }
  })
  .catch(() => Swal.fire({ icon:'error', title:'Network error', text:'Could not reach the server. Please try again.', customClass:{popup:'ds-pop'} }));
}

/* ════════════════════════════════════════════════════════════
   RING ANIMATION
════════════════════════════════════════════════════════════ */
function spcAnimateRing(from, to) {
  const circle = document.getElementById('spcRingCircle');
  const text   = document.getElementById('spcRingText');
  if (!circle) return;
  circle.style.strokeDashoffset = 100 - to;
  if (text) text.textContent = Math.round(to) + '%';
}

function spcEsc(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

Object.assign(window, { spcGoStep, spcNext, spcPrev, spcSave, spcLoadSubLevels, spcRemoveTag });
</script>
