<?php
/* qb_bulk_upload.php – Bulk Question Import */
// $db is already available — loaded by data_files/index.php before any page is included
$subjects = $db->query("SELECT subject_id, subject_name FROM qb_subjects ORDER BY subject_name")->fetch_all(MYSQLI_ASSOC);
$levels   = $db->query("SELECT level_id, level_name FROM qb_levels ORDER BY level_name")->fetch_all(MYSQLI_ASSOC);
?>
<style>
/* ── QB Bulk Upload (qbu-*) ── */
.qbu-wrap { font-family:'Open Sans',sans-serif; }
.qbu-hero { position:relative; overflow:hidden; isolation:isolate; border-radius:20px; padding:2rem 2.2rem; margin-bottom:1.4rem; background:linear-gradient(135deg,#1c0a00 0%,#431407 45%,#7c2d12 100%); }
.qbu-hero-grid { position:absolute; inset:0; z-index:0; background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px); background-size:44px 44px; }
.qbu-hero-inner { position:relative; z-index:1; }
.qbu-hero-badge { display:inline-flex; align-items:center; gap:.4rem; background:rgba(255,255,255,.09); border:1px solid rgba(255,255,255,.15); border-radius:100px; padding:.26rem .8rem; font-size:.68rem; font-weight:700; color:rgba(255,255,255,.7); letter-spacing:.06em; text-transform:uppercase; margin-bottom:.6rem; backdrop-filter:blur(6px); }
.qbu-hero-title { font-size:1.55rem; font-weight:900; color:#fff; font-family:'SUSE',sans-serif; letter-spacing:-.04em; margin-bottom:.25rem; }
.qbu-hero-title em { font-style:normal; background:linear-gradient(90deg,#fbbf24,#f97316); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent; }
.qbu-hero-sub { font-size:.79rem; color:rgba(255,255,255,.42); max-width:520px; line-height:1.6; }
.qbu-kpis { display:flex; gap:.65rem; flex-wrap:wrap; margin-top:1.1rem; }
.qbu-kpi { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12); border-radius:14px; padding:.55rem .95rem; backdrop-filter:blur(8px); }
.qbu-kpi-val { font-size:1.15rem; font-weight:900; color:#fff; font-family:'SUSE',sans-serif; line-height:1; }
.qbu-kpi-lbl { font-size:.62rem; color:rgba(255,255,255,.45); margin-top:.12rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; }

/* ── Panel ── */
.qbu-panel { background:#fff; border-radius:16px; border:1px solid #f0f4f8; box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 14px rgba(0,0,0,.04); margin-bottom:1.1rem; overflow:hidden; }
.qbu-panel-hdr { display:flex; align-items:center; justify-content:space-between; padding:.7rem 1.15rem; border-bottom:1px solid #f0f4f8; }
.qbu-panel-title { display:flex; align-items:center; gap:.55rem; font-size:.72rem; font-weight:800; color:#475569; text-transform:uppercase; letter-spacing:.07em; }
.qbu-panel-title i { font-size:.82rem; color:#94a3b8; }
.qbu-panel-body { padding:1.1rem 1.15rem; }

/* ── Template download ── */
.qbu-tpl-btn { display:inline-flex; align-items:center; gap:.5rem; background:linear-gradient(135deg,#d97706,#f59e0b); color:#fff; border:none; border-radius:11px; padding:.55rem 1.2rem; font-size:.8rem; font-weight:700; cursor:pointer; font-family:inherit; box-shadow:0 4px 14px rgba(217,119,6,.35); transition:filter .18s; text-decoration:none; }
.qbu-tpl-btn:hover { filter:brightness(1.1); color:#fff; }

/* ── Column map table ── */
.qbu-col-table { width:100%; border-collapse:collapse; font-size:.8rem; }
.qbu-col-table th { background:#f8fafc; color:#64748b; font-size:.67rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; padding:.5rem .85rem; border-bottom:1px solid #e2e8f0; text-align:left; }
.qbu-col-table td { padding:.5rem .85rem; border-bottom:1px solid #f0f4f8; color:#334155; vertical-align:top; }
.qbu-col-table tr:last-child td { border-bottom:none; }
.qbu-col-req { display:inline-block; background:#fff1f2; color:#e11d48; border-radius:100px; padding:.1rem .5rem; font-size:.66rem; font-weight:800; }
.qbu-col-opt { display:inline-block; background:#f1f5f9; color:#64748b; border-radius:100px; padding:.1rem .5rem; font-size:.66rem; font-weight:700; }
.qbu-col-name { font-family:monospace; font-size:.76rem; font-weight:700; color:#0f172a; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:.1rem .4rem; }

/* ── Drop zone ── */
.qbu-drop-zone { border:2px dashed #d97706; border-radius:18px; padding:3rem 2rem; text-align:center; background:linear-gradient(135deg,#fffbeb,#fff7ed); transition:all .2s; cursor:pointer; }
.qbu-drop-zone.drag-over { border-color:#f59e0b; background:#fef3c7; transform:scale(1.01); }
.qbu-drop-icon { font-size:3rem; color:#d97706; margin-bottom:.75rem; display:block; }
.qbu-drop-title { font-size:1.05rem; font-weight:800; color:#92400e; margin-bottom:.35rem; font-family:'SUSE',sans-serif; }
.qbu-drop-sub { font-size:.8rem; color:#b45309; margin-bottom:1.2rem; }
.qbu-file-btn { display:inline-flex; align-items:center; gap:.5rem; background:linear-gradient(135deg,#d97706,#f59e0b); color:#fff; border:none; border-radius:11px; padding:.6rem 1.4rem; font-size:.82rem; font-weight:700; cursor:pointer; font-family:inherit; box-shadow:0 4px 16px rgba(217,119,6,.3); }
.qbu-file-input { display:none; }
.qbu-file-chosen { display:none; margin-top:.8rem; font-size:.8rem; color:#92400e; font-weight:600; align-items:center; gap:.4rem; }
.qbu-file-chosen.show { display:inline-flex; }

/* ── Upload button ── */
.qbu-upload-btn { display:inline-flex; align-items:center; gap:.5rem; background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff; border:none; border-radius:12px; padding:.65rem 1.6rem; font-size:.85rem; font-weight:700; cursor:pointer; font-family:inherit; box-shadow:0 4px 18px rgba(26,79,196,.35); transition:filter .18s,transform .12s; }
.qbu-upload-btn:hover { filter:brightness(1.1); transform:translateY(-1px); }
.qbu-upload-btn:disabled { opacity:.5; cursor:not-allowed; transform:none; }

/* ── Progress bar ── */
.qbu-prog-wrap { background:#f1f5f9; border-radius:100px; height:8px; overflow:hidden; margin:1rem 0; }
.qbu-prog-fill { height:100%; background:linear-gradient(90deg,#d97706,#f59e0b); border-radius:100px; transition:width .5s ease; }

/* ── Results ── */
.qbu-results { display:none; }
.qbu-result-stats { display:flex; gap:.65rem; flex-wrap:wrap; margin-bottom:1.1rem; }
.qbu-res-stat { flex:1; min-width:100px; background:#fff; border-radius:14px; border:1px solid #f0f4f8; padding:.8rem 1rem; text-align:center; box-shadow:0 1px 3px rgba(0,0,0,.04); }
.qbu-res-stat-val { font-size:1.5rem; font-weight:900; font-family:'SUSE',sans-serif; line-height:1; }
.qbu-res-stat-lbl { font-size:.67rem; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:.05em; margin-top:.25rem; }
.qbu-err-table { width:100%; border-collapse:collapse; font-size:.79rem; }
.qbu-err-table th { background:#fff1f2; color:#dc2626; font-size:.68rem; font-weight:700; text-transform:uppercase; padding:.5rem .85rem; border-bottom:1px solid #fecaca; text-align:left; }
.qbu-err-table td { padding:.5rem .85rem; border-bottom:1px solid #f0f4f8; color:#334155; vertical-align:top; }
.qbu-err-row td:first-child { color:#dc2626; font-weight:700; font-family:monospace; }
.qbu-err-msg { color:#dc2626; font-size:.76rem; }
</style>

<div class="container-fluid px-3 py-3 qbu-wrap">

<!-- ── Hero ──────────────────────────────────────────────── -->
<div class="qbu-hero">
  <div class="qbu-hero-grid"></div>
  <div style="position:absolute;right:2.5rem;top:50%;transform:translateY(-50%);width:200px;height:200px;border-radius:50%;background:conic-gradient(from 0deg,rgba(217,119,6,.48),rgba(234,88,12,.35),rgba(217,119,6,.48));filter:blur(40px);opacity:.5;animation:db-orb-spin 16s linear infinite;z-index:0"></div>
  <div class="qbu-hero-inner">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <div class="qbu-hero-badge"><i class="bi bi-upload"></i>Question Bank</div>
        <div class="qbu-hero-title"><em>Bulk Upload</em> Questions</div>
        <div class="qbu-hero-sub">Import questions at once from an Excel (.xlsx) or CSV file. Use the standard column format, fill in your data, and upload — invalid rows are skipped with a full error report.</div>
        <div class="qbu-kpis">
          <div class="qbu-kpi"><div class="qbu-kpi-val" id="kpiTotal">—</div><div class="qbu-kpi-lbl">Total in Bank</div></div>
          <div class="qbu-kpi"><div class="qbu-kpi-val" id="kpiLastBatch">—</div><div class="qbu-kpi-lbl">Last Batch</div></div>
          <div class="qbu-kpi"><div class="qbu-kpi-val" id="kpiErrors">—</div><div class="qbu-kpi-lbl">Errors</div></div>
        </div>
      </div>
      <div class="col-lg-5 d-none d-lg-flex justify-content-end mt-3 mt-lg-0">
        <div style="text-align:center">
          <div style="font-size:4rem;color:rgba(255,255,255,.15)"><i class="bi bi-file-earmark-spreadsheet"></i></div>
          <a href="ajax/ajax_qb_bulk_upload.php?action=template" class="qbu-tpl-btn" download>
            <i class="bi bi-download"></i>Download CSV Template
          </a>
          <div style="font-size:.7rem;color:rgba(255,255,255,.35);margin-top:.5rem;text-align:center">Works with .xlsx &amp; .csv</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">

  <!-- LEFT: Instructions + Column Map ─────────────────── -->
  <div class="col-12 col-lg-4">

    <div class="qbu-panel">
      <div class="qbu-panel-hdr">
        <div class="qbu-panel-title"><i class="bi bi-info-circle"></i>Instructions</div>
      </div>
      <div class="qbu-panel-body">
        <ol style="font-size:.8rem;color:#475569;line-height:2;padding-left:1.2rem;margin:0">
          <li>Download the <strong>CSV template</strong> above or use the provided <code>.xlsx</code> file directly.</li>
          <li>Keep the <strong>header row exactly as-is</strong> — columns must be in order.</li>
          <li>One question per row. Section A rows are imported as <strong>MCQ</strong>, Section B as <strong>Short Answer</strong>.</li>
          <li>For MCQ fill <code>Option A–E</code> and set <code>Correct Ans</code> to the correct letter (<code>A</code>–<code>E</code>).</li>
          <li>Chapters and subtopics that don't exist are <strong>created automatically</strong> under the subject &amp; level you select.</li>
          <li>Choose how to handle <strong>duplicate Q_IDs</strong> — skip or overwrite.</li>
          <li>Invalid rows are skipped with a full error report after upload.</li>
        </ol>
        <a href="ajax/ajax_qb_bulk_upload.php?action=template" class="qbu-tpl-btn d-lg-none mt-3" download style="width:100%;justify-content:center">
          <i class="bi bi-download"></i>Download Template
        </a>
      </div>
    </div>

    <div class="qbu-panel">
      <div class="qbu-panel-hdr">
        <div class="qbu-panel-title"><i class="bi bi-table"></i>Column Reference</div>
      </div>
      <div style="overflow-x:auto">
        <table class="qbu-col-table">
          <thead><tr><th>#</th><th>Column Header</th><th>Req</th><th>Notes</th></tr></thead>
          <tbody>
            <?php
            $cols = [
              [1,  'Q_ID',                 true,  'Unique ID e.g. PP_2019_A01'],
              [2,  'Year',                 false, 'Exam year e.g. 2024'],
              [3,  'Section',              false, 'Section A (MCQ) or Section B (Short Answer)'],
              [4,  'Q#',                   false, 'Question number in exam'],
              [5,  'Chapter#',             false, 'Chapter number'],
              [6,  'Chapter Name',         true,  'Auto-created if not found'],
              [7,  'Sub-topic',            false, 'Auto-created if not found'],
              [8,  'Difficulty',           false, 'Easy · Medium · Hard'],
              [9,  "Bloom's Level",        false, 'Remembering · Understanding · Applying…'],
              [10, 'Question Stem',        true,  'The full question text'],
              [11, 'Option A',             false, 'MCQ only'],
              [12, 'Option B',             false, 'MCQ only'],
              [13, 'Option C',             false, 'MCQ only'],
              [14, 'Option D',             false, 'MCQ only'],
              [15, 'Option E',             false, 'MCQ only (5-choice)'],
              [16, 'Correct Ans',          false, 'Letter A–E for MCQ · Full text for Section B'],
              [17, 'Solution/Explanation', false, 'Step-by-step solution'],
              [18, 'Swahili Hint',         false, 'Optional Swahili language hint'],
              [19, 'Est.Time(s)',           false, 'Seconds, default 60'],
              [20, 'Marks',                false, 'Default 1'],
              [21, 'CIRA Flag',            false, 'Yes or No'],
            ];
            foreach ($cols as [$num, $col, $req, $notes]):
            ?>
            <tr>
              <td style="color:#94a3b8;font-size:.7rem;font-weight:700"><?= $num ?></td>
              <td><span class="qbu-col-name"><?= htmlspecialchars($col) ?></span></td>
              <td><?= $req ? '<span class="qbu-col-req">Req</span>' : '<span class="qbu-col-opt">Opt</span>' ?></td>
              <td style="font-size:.72rem;color:#64748b"><?= htmlspecialchars($notes) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- RIGHT: Upload + Results ────────────────────────── -->
  <div class="col-12 col-lg-8">

    <!-- Upload zone -->
    <div class="qbu-panel">
      <div class="qbu-panel-hdr">
        <div class="qbu-panel-title"><i class="bi bi-cloud-upload"></i>Upload File</div>
      </div>
      <div class="qbu-panel-body">

        <!-- Drop zone -->
        <div class="qbu-drop-zone" id="dropZone">
          <i class="bi bi-file-earmark-spreadsheet qbu-drop-icon"></i>
          <div class="qbu-drop-title">Drag &amp; drop your file here</div>
          <div class="qbu-drop-sub">or click to browse — .xlsx or .csv, max 10 MB</div>
          <button type="button" class="qbu-file-btn" id="browseBtn">
            <i class="bi bi-folder2-open"></i>Choose File
          </button>
          <div class="qbu-file-chosen" id="fileChosen"><i class="bi bi-file-check"></i><span id="fileName">No file</span></div>
        </div>
        <input type="file" id="csvFileInput" class="qbu-file-input" accept=".xlsx,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv">

        <!-- Settings -->
        <div style="margin-top:1.2rem;padding:1rem 1.1rem;background:#f8fafc;border-radius:14px;border:1px solid #e2e8f0">
          <div style="font-size:.7rem;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.85rem">
            <i class="bi bi-sliders me-1"></i>Import Settings
          </div>
          <div class="row g-3">
            <div class="col-12 col-sm-6">
              <label style="font-size:.75rem;font-weight:700;color:#334155;display:block;margin-bottom:.35rem">Subject <span style="color:#dc2626">*</span></label>
              <select id="subjectId" style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:.45rem .75rem;font-size:.83rem;color:#0f172a;background:#fff;outline:none">
                <option value="">— Select subject —</option>
                <?php foreach ($subjects as $s): ?>
                <option value="<?= $s['subject_id'] ?>"><?= htmlspecialchars($s['subject_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-sm-6">
              <label style="font-size:.75rem;font-weight:700;color:#334155;display:block;margin-bottom:.35rem">Level / Grade <span style="color:#dc2626">*</span></label>
              <div style="display:flex;gap:.4rem">
                <select id="levelSelect" style="flex:1;border:1.5px solid #e2e8f0;border-radius:10px;padding:.45rem .75rem;font-size:.83rem;color:#0f172a;background:#fff;outline:none">
                  <option value="">— Select or type new —</option>
                  <?php foreach ($levels as $l): ?>
                  <option value="<?= htmlspecialchars($l['level_name']) ?>"><?= htmlspecialchars($l['level_name']) ?></option>
                  <?php endforeach; ?>
                  <option value="__new__">+ New level…</option>
                </select>
                <input type="text" id="levelNew" placeholder="Level name" style="display:none;width:130px;border:1.5px solid #6366f1;border-radius:10px;padding:.45rem .75rem;font-size:.83rem;outline:none">
              </div>
            </div>
            <div class="col-12 col-sm-6">
              <label style="font-size:.75rem;font-weight:700;color:#334155;display:block;margin-bottom:.35rem">Import as Status</label>
              <div style="display:flex;gap:.85rem;align-items:center;padding-top:.2rem">
                <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;cursor:pointer">
                  <input type="radio" name="importStatus" value="draft" checked style="accent-color:#6366f1"> Draft
                </label>
                <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;cursor:pointer">
                  <input type="radio" name="importStatus" value="review" style="accent-color:#6366f1"> Submit for Review
                </label>
              </div>
            </div>
            <div class="col-12 col-sm-6">
              <label style="font-size:.75rem;font-weight:700;color:#334155;display:block;margin-bottom:.35rem">Duplicate Q_ID</label>
              <div style="display:flex;gap:.85rem;align-items:center;padding-top:.2rem">
                <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;cursor:pointer">
                  <input type="radio" name="dupAction" value="skip" checked style="accent-color:#6366f1"> Skip
                </label>
                <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;cursor:pointer">
                  <input type="radio" name="dupAction" value="overwrite" style="accent-color:#6366f1"> Overwrite
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Progress -->
        <div id="progressWrap" style="display:none;margin-top:1rem">
          <div class="qbu-prog-wrap"><div class="qbu-prog-fill" id="progFill" style="width:0%"></div></div>
          <div style="font-size:.76rem;color:#94a3b8;text-align:center;margin-top:.35rem" id="progLabel">Processing…</div>
        </div>

        <div style="display:flex;justify-content:flex-end;margin-top:1.2rem">
          <button class="qbu-upload-btn" id="uploadBtn" disabled>
            <i class="bi bi-upload"></i>Upload &amp; Import
          </button>
        </div>
      </div>
    </div>

    <!-- Results -->
    <div id="resultsWrap" class="qbu-results">
      <div class="qbu-result-stats" id="resultStats"></div>

      <div class="qbu-panel" id="errPanel" style="display:none">
        <div class="qbu-panel-hdr">
          <div class="qbu-panel-title"><i class="bi bi-exclamation-triangle" style="color:#dc2626"></i>Row Errors</div>
          <span id="errCount" style="font-size:.72rem;font-weight:700;color:#dc2626"></span>
        </div>
        <div style="overflow-x:auto">
          <table class="qbu-err-table" id="errTable">
            <thead><tr><th>Row</th><th>UID / Data</th><th>Error</th></tr></thead>
            <tbody id="errTbody"></tbody>
          </table>
        </div>
      </div>

      <div class="qbu-panel" id="successPanel" style="display:none">
        <div class="qbu-panel-hdr">
          <div class="qbu-panel-title"><i class="bi bi-check-circle-fill" style="color:#059669"></i>Imported Questions</div>
          <span id="successCount" style="font-size:.72rem;font-weight:700;color:#059669"></span>
        </div>
        <div class="qbu-panel-body">
          <div id="successList" style="display:flex;flex-wrap:wrap;gap:.4rem"></div>
        </div>
      </div>
    </div>

  </div>
</div>
</div>

<script>
(function () {
/* ── DCM Alerts ──────────────────────────────────────────── */
const dcmAlert = {
  _css:`.ds-pop{border-radius:20px!important;font-family:'Open Sans',sans-serif!important;padding:1.6rem!important}.ds-ttl{font-size:1.1rem!important;font-weight:800!important;color:#0f172a!important;margin-top:.3rem!important}.ds-btn{border-radius:11px!important;font-weight:700!important;font-size:.82rem!important;padding:.55rem 1.4rem!important}.ds-tst{border-radius:14px!important;font-family:'Open Sans',sans-serif!important;box-shadow:0 8px 32px rgba(0,0,0,.14)!important;padding:.75rem 1.1rem!important;border-left:4px solid}.dst-ok{border-color:#059669!important}.dst-er{border-color:#dc2626!important}.dst-wn{border-color:#d97706!important}`,
  _done:false,
  _inject(){if(!this._done){const s=document.createElement('style');s.textContent=this._css;document.head.appendChild(s);this._done=true;}},
  toast(icon,title,text=''){this._inject();const cls={success:'dst-ok',error:'dst-er',warning:'dst-wn'}[icon]||'';Swal.fire({toast:true,position:'top-end',showConfirmButton:false,timer:3400,timerProgressBar:true,icon,title,text,customClass:{popup:`ds-tst ${cls}`}});},
  success(t,x=''){this.toast('success',t,x);},
  error(t,x=''){this._inject();Swal.fire({icon:'error',title:t,text:x||'Something went wrong.',customClass:{popup:'ds-pop',title:'ds-ttl',confirmButton:'ds-btn'},confirmButtonColor:'#dc2626',confirmButtonText:'Got it'});},
};

let selectedFile = null;

/* ── DOM refs ────────────────────────────────────────────── */
const dropZone   = document.getElementById('dropZone');
const fileInput  = document.getElementById('csvFileInput');
const browseBtn  = document.getElementById('browseBtn');
const uploadBtn  = document.getElementById('uploadBtn');
const fileChosen = document.getElementById('fileChosen');
const fileNameEl = document.getElementById('fileName');
const progWrap   = document.getElementById('progressWrap');
const progFill   = document.getElementById('progFill');
const progLabel  = document.getElementById('progLabel');
const resultsWrap= document.getElementById('resultsWrap');
const levelSelect= document.getElementById('levelSelect');
const levelNew   = document.getElementById('levelNew');

/* ── New level field toggle ──────────────────────────────── */
levelSelect.addEventListener('change', function () {
  if (this.value === '__new__') {
    levelNew.style.display = '';
    levelNew.focus();
  } else {
    levelNew.style.display = 'none';
  }
});

/* ── Load KPIs ───────────────────────────────────────────── */
fetch('ajax/ajax_qb_questions.php?action=counts')
  .then(r => r.json()).then(res => {
    if (res.status === 'success')
      document.getElementById('kpiTotal').textContent = res.data.total || '0';
  }).catch(() => {});

/* ── File selection helpers ──────────────────────────────── */
function setFile(file) {
  selectedFile = file;
  fileChosen.classList.add('show');
  fileNameEl.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
  uploadBtn.disabled = false;
}

/* ── Event listeners ─────────────────────────────────────── */
// Click drop zone → open file picker
dropZone.addEventListener('click', function (e) {
  if (e.target === browseBtn || browseBtn.contains(e.target)) return;
  fileInput.click();
});

// Browse button
browseBtn.addEventListener('click', function (e) {
  e.stopPropagation();
  fileInput.click();
});

// File input change
fileInput.addEventListener('change', function () {
  if (this.files.length) setFile(this.files[0]);
});

// Drag events
dropZone.addEventListener('dragover', function (e) {
  e.preventDefault();
  dropZone.classList.add('drag-over');
});
dropZone.addEventListener('dragleave', function () {
  dropZone.classList.remove('drag-over');
});
dropZone.addEventListener('drop', function (e) {
  e.preventDefault();
  dropZone.classList.remove('drag-over');
  if (e.dataTransfer.files.length) setFile(e.dataTransfer.files[0]);
});

// Upload button
uploadBtn.addEventListener('click', startUpload);

/* ── Upload ──────────────────────────────────────────────── */
function startUpload() {
  if (!selectedFile) return;

  const ext = selectedFile.name.split('.').pop().toLowerCase();
  if (!['xlsx','csv'].includes(ext)) {
    dcmAlert.error('Invalid file', 'Please upload a .xlsx or .csv file.');
    return;
  }
  if (selectedFile.size > 10 * 1024 * 1024) {
    dcmAlert.error('File too large', 'Maximum file size is 10 MB.');
    return;
  }

  const subjectId = document.getElementById('subjectId').value;
  if (!subjectId) { dcmAlert.error('No subject selected', 'Please choose a subject before importing.'); return; }

  const levelName = levelSelect.value === '__new__'
    ? levelNew.value.trim()
    : levelSelect.value;
  if (!levelName) { dcmAlert.error('No level selected', 'Please choose or type a level / grade.'); return; }

  const importStatus = document.querySelector('input[name="importStatus"]:checked')?.value || 'draft';
  const dupAction    = document.querySelector('input[name="dupAction"]:checked')?.value    || 'skip';

  uploadBtn.disabled = true;
  progWrap.style.display = '';
  resultsWrap.style.display = 'none';
  setProgress(20, 'Uploading file…');

  const fd = new FormData();
  fd.append('action',           'import');
  fd.append('file',             selectedFile);
  fd.append('subject_id',       subjectId);
  fd.append('level_name',       levelName);
  fd.append('status',           importStatus);
  fd.append('duplicate_action', dupAction);

  setProgress(50, 'Processing rows…');

  fetch('ajax/ajax_qb_bulk_upload.php', { method: 'POST', body: fd })
    .then(r => r.text().then(text => {
      try { return JSON.parse(text); }
      catch (e) { throw new Error(text.slice(0, 300) || 'Server returned non-JSON response'); }
    }))
    .then(res => {
      if (res.status === 'error') {
        progWrap.style.display = 'none';
        uploadBtn.disabled = false;
        dcmAlert.error('Import error', res.message || 'Server returned an error.');
        return;
      }
      setProgress(100, 'Done!');
      setTimeout(() => {
        progWrap.style.display = 'none';
        uploadBtn.disabled = false;
        showResults(res);
      }, 400);
    })
    .catch(err => {
      progWrap.style.display = 'none';
      uploadBtn.disabled = false;
      dcmAlert.error('Upload failed', err.message || 'Could not reach the server.');
    });
}

function setProgress(pct, label) {
  progFill.style.width = pct + '%';
  progLabel.textContent = label;
}

/* ── Results ─────────────────────────────────────────────── */
function showResults(res) {
  resultsWrap.style.display = '';
  const imported = res.imported  || 0;
  const errors   = (res.errors  || []).length;
  const total    = res.total_rows || 0;
  const skipped  = Math.max(0, total - imported - errors);

  document.getElementById('kpiLastBatch').textContent = imported;
  document.getElementById('kpiErrors').textContent    = errors;
  document.getElementById('kpiTotal').textContent     = res.total_in_bank || '—';

  document.getElementById('resultStats').innerHTML = `
    <div class="qbu-res-stat"><div class="qbu-res-stat-val" style="color:#334155">${total}</div><div class="qbu-res-stat-lbl">Rows Parsed</div></div>
    <div class="qbu-res-stat"><div class="qbu-res-stat-val" style="color:#059669">${imported}</div><div class="qbu-res-stat-lbl">Imported</div></div>
    <div class="qbu-res-stat"><div class="qbu-res-stat-val" style="color:${errors > 0 ? '#dc2626' : '#059669'}">${errors}</div><div class="qbu-res-stat-lbl">Errors</div></div>
    <div class="qbu-res-stat"><div class="qbu-res-stat-val" style="color:#d97706">${skipped}</div><div class="qbu-res-stat-lbl">Skipped</div></div>`;

  const errPanel     = document.getElementById('errPanel');
  const successPanel = document.getElementById('successPanel');

  if (errors) {
    errPanel.style.display = '';
    document.getElementById('errCount').textContent = `${errors} row${errors !== 1 ? 's' : ''} failed`;
    document.getElementById('errTbody').innerHTML = res.errors.map(e => `
      <tr class="qbu-err-row">
        <td>Row ${e.row}</td>
        <td style="font-size:.74rem;color:#64748b;max-width:220px;overflow:hidden;text-overflow:ellipsis">${(e.stem || '').slice(0, 60) || '—'}</td>
        <td class="qbu-err-msg">${e.error}</td>
      </tr>`).join('');
  } else {
    errPanel.style.display = 'none';
  }

  if (imported) {
    successPanel.style.display = '';
    document.getElementById('successCount').textContent = `${imported} question${imported !== 1 ? 's' : ''} imported`;
    document.getElementById('successList').innerHTML = (res.uids || []).map(uid =>
      `<span style="font-family:monospace;font-size:.71rem;background:#f0fdf4;color:#059669;border:1px solid #bbf7d0;border-radius:8px;padding:.15rem .55rem;font-weight:700">${uid}</span>`
    ).join('');
    dcmAlert.success(`${imported} questions imported!`, errors ? `${errors} rows had errors.` : 'All rows processed successfully.');
  } else {
    successPanel.style.display = 'none';
    if (!errors) dcmAlert.error('Nothing imported', 'The file may be empty or all rows had errors.');
  }
}

})(); // end IIFE — keeps scope clean whether executed via new Function() or direct script tag
</script>
