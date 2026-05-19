<?php
/* qb_question_media.php – QB Question Media Manager */
$preload_id = (int)($_GET['question_id'] ?? 0);
?>
<style>
/* ── QB Media Manager (qbm-*) ── */
.qbm-wrap { font-family:'Open Sans',sans-serif; }
.qbm-hero { position:relative; overflow:hidden; isolation:isolate; border-radius:20px; padding:2rem 2.2rem; margin-bottom:1.4rem; background:linear-gradient(135deg,#001226 0%,#001e40 45%,#002855 100%); }
.qbm-hero-grid { position:absolute; inset:0; z-index:0; background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px); background-size:44px 44px; }
.qbm-hero-inner { position:relative; z-index:1; }
.qbm-hero-badge { display:inline-flex; align-items:center; gap:.4rem; background:rgba(255,255,255,.09); border:1px solid rgba(255,255,255,.15); border-radius:100px; padding:.26rem .8rem; font-size:.68rem; font-weight:700; color:rgba(255,255,255,.7); letter-spacing:.06em; text-transform:uppercase; margin-bottom:.6rem; backdrop-filter:blur(6px); }
.qbm-hero-title { font-size:1.55rem; font-weight:900; color:#fff; font-family:'SUSE',sans-serif; letter-spacing:-.04em; margin-bottom:.25rem; }
.qbm-hero-title em { font-style:normal; background:linear-gradient(90deg,#38bdf8,#818cf8); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; color:transparent; }
.qbm-hero-sub { font-size:.79rem; color:rgba(255,255,255,.42); max-width:520px; line-height:1.6; }
.qbm-kpis { display:flex; gap:.65rem; flex-wrap:wrap; margin-top:1.1rem; }
.qbm-kpi { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12); border-radius:14px; padding:.55rem .95rem; backdrop-filter:blur(8px); }
.qbm-kpi-val { font-size:1.15rem; font-weight:900; color:#fff; font-family:'SUSE',sans-serif; line-height:1; }
.qbm-kpi-lbl { font-size:.62rem; color:rgba(255,255,255,.45); margin-top:.12rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; }

/* ── Panel ── */
.qbm-panel { background:#fff; border-radius:16px; border:1px solid #f0f4f8; box-shadow:0 1px 3px rgba(0,0,0,.05),0 4px 14px rgba(0,0,0,.04); margin-bottom:1.1rem; overflow:hidden; }
.qbm-panel-hdr { display:flex; align-items:center; justify-content:space-between; padding:.7rem 1.15rem; border-bottom:1px solid #f0f4f8; }
.qbm-panel-title { display:flex; align-items:center; gap:.55rem; font-size:.72rem; font-weight:800; color:#475569; text-transform:uppercase; letter-spacing:.07em; }
.qbm-panel-title i { font-size:.82rem; color:#94a3b8; }
.qbm-panel-body { padding:1.1rem 1.15rem; }

/* ── Search ── */
.qbm-search-wrap { position:relative; }
.qbm-search-wrap i { position:absolute; left:.8rem; top:50%; transform:translateY(-50%); color:#94a3b8; pointer-events:none; }
.qbm-search { width:100%; padding:.55rem .85rem .55rem 2.3rem; border-radius:12px; border:1.5px solid #e2e8f0; font-size:.83rem; font-family:inherit; outline:none; background:#f8fafc; color:#1e293b; transition:border-color .18s,box-shadow .18s; }
.qbm-search:focus { border-color:#0ea5e9; box-shadow:0 0 0 3px rgba(14,165,233,.1); background:#fff; }

/* ── Question result list ── */
.qbm-q-item { display:flex; align-items:flex-start; gap:.75rem; padding:.7rem .95rem; border-radius:12px; border:1.5px solid transparent; cursor:pointer; transition:all .18s; }
.qbm-q-item:hover { background:#f0f9ff; border-color:#bae6fd; }
.qbm-q-item.active { background:#eff6ff; border-color:#1a4fc4; }
.qbm-q-uid { font-family:monospace; font-size:.7rem; font-weight:800; background:#0f172a; color:#e2e8f0; padding:.12rem .45rem; border-radius:6px; flex-shrink:0; }
.qbm-q-stem { font-size:.8rem; color:#334155; line-height:1.5; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }

/* ── Selected question header ── */
.qbm-q-selected-card { background:linear-gradient(135deg,#0f172a,#1e293b); border-radius:14px; padding:1rem 1.2rem; display:flex; align-items:flex-start; gap:.85rem; }
.qbm-q-sel-icon { width:42px; height:42px; border-radius:11px; background:rgba(14,165,233,.2); border:1px solid rgba(14,165,233,.3); display:flex; align-items:center; justify-content:center; font-size:1.1rem; color:#38bdf8; flex-shrink:0; }
.qbm-q-sel-uid { font-family:monospace; font-size:.75rem; font-weight:800; color:#94a3b8; margin-bottom:.25rem; }
.qbm-q-sel-stem { font-size:.85rem; color:#e2e8f0; line-height:1.5; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.qbm-q-sel-meta { display:flex; flex-wrap:wrap; gap:.35rem; margin-top:.5rem; }
.qbm-q-sel-chip { display:inline-flex; align-items:center; gap:.25rem; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12); border-radius:100px; padding:.13rem .55rem; font-size:.67rem; color:rgba(255,255,255,.55); font-weight:600; }

/* ── Media grid ── */
.qbm-media-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:.75rem; }
.qbm-media-card { border-radius:14px; border:1.5px solid #f0f4f8; overflow:hidden; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.05); transition:all .2s; position:relative; }
.qbm-media-card:hover { border-color:#bae6fd; box-shadow:0 4px 14px rgba(0,0,0,.1); }
.qbm-media-thumb { width:100%; aspect-ratio:1; object-fit:cover; display:block; background:#f8fafc; }
.qbm-media-icon-thumb { width:100%; aspect-ratio:1; display:flex; align-items:center; justify-content:center; background:#f8fafc; font-size:2.5rem; color:#94a3b8; }
.qbm-media-foot { padding:.45rem .6rem; border-top:1px solid #f0f4f8; display:flex; align-items:center; justify-content:space-between; }
.qbm-media-type { font-size:.64rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; border-radius:100px; padding:.12rem .45rem; }
.qbm-media-del { width:26px; height:26px; border-radius:7px; border:1.5px solid #f0f4f8; background:#fff; color:#cbd5e1; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:.76rem; transition:all .15s; }
.qbm-media-del:hover { border-color:#fecaca; color:#dc2626; background:#fff1f2; }
.qbm-media-empty { text-align:center; padding:3rem 1rem; }

/* ── Drop zone ── */
.qbm-drop-zone { border:2px dashed #bae6fd; border-radius:16px; padding:2rem; text-align:center; background:#f0f9ff; transition:all .2s; cursor:pointer; }
.qbm-drop-zone.drag-over { border-color:#0ea5e9; background:#e0f2fe; transform:scale(1.01); }
.qbm-drop-icon { font-size:2.5rem; color:#0ea5e9; margin-bottom:.6rem; display:block; }
.qbm-drop-title { font-size:.9rem; font-weight:800; color:#0369a1; margin-bottom:.2rem; font-family:'SUSE',sans-serif; }
.qbm-drop-sub { font-size:.76rem; color:#0891b2; margin-bottom:1rem; }
.qbm-file-btn { display:inline-flex; align-items:center; gap:.4rem; background:linear-gradient(135deg,#0891b2,#0ea5e9); color:#fff; border:none; border-radius:10px; padding:.52rem 1.1rem; font-size:.79rem; font-weight:700; cursor:pointer; font-family:inherit; }
.qbm-type-sel { padding:.48rem .7rem; border-radius:10px; border:1.5px solid #bae6fd; font-size:.79rem; font-family:inherit; outline:none; background:#f0f9ff; color:#0369a1; }
.qbm-upload-btn { display:inline-flex; align-items:center; gap:.45rem; background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff; border:none; border-radius:11px; padding:.55rem 1.3rem; font-size:.8rem; font-weight:700; cursor:pointer; font-family:inherit; box-shadow:0 4px 14px rgba(26,79,196,.35); transition:filter .17s; }
.qbm-upload-btn:hover { filter:brightness(1.1); }
.qbm-upload-btn:disabled { opacity:.5; cursor:not-allowed; }

/* ── Skeleton ── */
.qbm-skel { background:linear-gradient(90deg,#f0f4f8 25%,#e2e8f0 50%,#f0f4f8 75%); background-size:200% 100%; animation:qbm-shim 1.5s infinite; border-radius:8px; }
@keyframes qbm-shim { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ── Media type colors ── */
.mt-image    { background:#eff6ff; color:#1a4fc4; }
.mt-audio    { background:#f0fdf4; color:#059669; }
.mt-video    { background:#fdf4ff; color:#7c3aed; }
.mt-document { background:#fff7ed; color:#d97706; }
</style>

<div class="container-fluid px-3 py-3 qbm-wrap">

<!-- ── Hero ──────────────────────────────────────────────── -->
<div class="qbm-hero">
  <div class="qbm-hero-grid"></div>
  <div style="position:absolute;right:2.5rem;top:50%;transform:translateY(-50%);width:200px;height:200px;border-radius:50%;background:conic-gradient(from 0deg,rgba(8,145,178,.48),rgba(14,165,233,.32),rgba(8,145,178,.48));filter:blur(40px);opacity:.5;animation:db-orb-spin 16s linear infinite;z-index:0"></div>
  <div class="qbm-hero-inner">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <div class="qbm-hero-badge"><i class="bi bi-images"></i>Question Bank</div>
        <div class="qbm-hero-title"><em>Question Media</em> Manager</div>
        <div class="qbm-hero-sub">Attach images, audio clips, videos and documents to individual questions. Media is displayed in the question view and during exams.</div>
        <div class="qbm-kpis">
          <div class="qbm-kpi"><div class="qbm-kpi-val" id="kpiTotalMedia">—</div><div class="qbm-kpi-lbl">Total Media Files</div></div>
          <div class="qbm-kpi"><div class="qbm-kpi-val" id="kpiImages">—</div><div class="qbm-kpi-lbl">Images</div></div>
          <div class="qbm-kpi"><div class="qbm-kpi-val" id="kpiAudio">—</div><div class="qbm-kpi-lbl">Audio</div></div>
          <div class="qbm-kpi"><div class="qbm-kpi-val" id="kpiDocs">—</div><div class="qbm-kpi-lbl">Documents</div></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">

  <!-- ── LEFT: Question Search ───────────────────────── -->
  <div class="col-12 col-lg-4">

    <div class="qbm-panel">
      <div class="qbm-panel-hdr">
        <div class="qbm-panel-title"><i class="bi bi-search"></i>Find Question</div>
      </div>
      <div class="qbm-panel-body">
        <div class="qbm-search-wrap mb-2">
          <i class="bi bi-search" style="font-size:.82rem"></i>
          <input type="text" id="qSearch" class="qbm-search" placeholder="Search by UID or question text…" oninput="debounceSearch()">
        </div>
        <div style="font-size:.7rem;color:#94a3b8;margin-bottom:.75rem">Type to search — results appear below</div>
        <div id="qResults" style="max-height:400px;overflow-y:auto">
          <div style="text-align:center;padding:2rem;color:#cbd5e1;font-size:.8rem"><i class="bi bi-search" style="font-size:1.5rem;display:block;margin-bottom:.5rem"></i>Search for a question above</div>
        </div>
      </div>
    </div>

  </div>

  <!-- ── RIGHT: Media Panel ───────────────────────────── -->
  <div class="col-12 col-lg-8">

    <!-- No selection state -->
    <div id="noSelPanel" class="qbm-panel">
      <div class="qbm-panel-body" style="text-align:center;padding:4rem 2rem">
        <i class="bi bi-images" style="font-size:3.5rem;color:#cbd5e1;display:block;margin-bottom:1rem"></i>
        <div style="font-size:1rem;font-weight:800;color:#475569;margin-bottom:.4rem">No question selected</div>
        <div style="font-size:.81rem;color:#94a3b8">Search for a question on the left to manage its media files.</div>
      </div>
    </div>

    <!-- Selected question + media panel -->
    <div id="mediaPanel" style="display:none">

      <!-- Selected Q card -->
      <div class="qbm-panel">
        <div class="qbm-panel-hdr">
          <div class="qbm-panel-title"><i class="bi bi-patch-question-fill"></i>Selected Question</div>
          <button style="font-size:.72rem;font-weight:700;color:#94a3b8;background:none;border:none;cursor:pointer" onclick="clearSelection()"><i class="bi bi-x-circle"></i> Change</button>
        </div>
        <div class="qbm-panel-body">
          <div class="qbm-q-selected-card" id="selectedQCard"></div>
        </div>
      </div>

      <!-- Existing media -->
      <div class="qbm-panel">
        <div class="qbm-panel-hdr">
          <div class="qbm-panel-title"><i class="bi bi-collection"></i>Attached Media</div>
          <span id="mediaCountBadge" style="font-size:.72rem;font-weight:700;color:#94a3b8"></span>
        </div>
        <div class="qbm-panel-body">
          <div class="qbm-media-grid" id="mediaGrid">
            <div class="qbm-media-empty"><div class="qbm-skel" style="width:100%;height:120px;border-radius:12px"></div></div>
          </div>
        </div>
      </div>

      <!-- Upload -->
      <div class="qbm-panel">
        <div class="qbm-panel-hdr">
          <div class="qbm-panel-title"><i class="bi bi-cloud-upload"></i>Upload New Media</div>
        </div>
        <div class="qbm-panel-body">
          <div class="qbm-drop-zone" id="mediaDropZone" onclick="document.getElementById('mediaFileInput').click()" ondragover="mediaDragOver(event)" ondragleave="mediaDragLeave(event)" ondrop="mediaDrop(event)">
            <i class="bi bi-cloud-arrow-up qbm-drop-icon"></i>
            <div class="qbm-drop-title">Drag & drop files here</div>
            <div class="qbm-drop-sub">Images (JPG/PNG/GIF/WebP) · Audio (MP3/WAV) · Video (MP4) · Documents (PDF)</div>
            <button type="button" class="qbm-file-btn" onclick="event.stopPropagation();document.getElementById('mediaFileInput').click()">
              <i class="bi bi-folder2-open"></i>Choose File
            </button>
          </div>
          <input type="file" id="mediaFileInput" style="display:none" accept="image/*,audio/*,video/mp4,.pdf,.doc,.docx" onchange="onMediaFileChosen(this)">

          <div id="mediaUploadInfo" style="display:none;margin-top:.9rem">
            <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap">
              <span style="font-size:.8rem;font-weight:700;color:#334155" id="mediaFileName"></span>
              <div style="display:flex;align-items:center;gap:.5rem">
                <label style="font-size:.73rem;color:#64748b;font-weight:700">Type:</label>
                <select id="mediaType" class="qbm-type-sel">
                  <option value="image">Image</option>
                  <option value="audio">Audio</option>
                  <option value="video">Video</option>
                  <option value="document">Document</option>
                </select>
              </div>
              <button class="qbm-upload-btn" id="doUploadBtn" onclick="uploadMedia()"><i class="bi bi-upload"></i>Upload</button>
              <button style="font-size:.75rem;color:#94a3b8;background:none;border:none;cursor:pointer" onclick="cancelUpload()"><i class="bi bi-x"></i> Cancel</button>
            </div>
            <div id="uploadProgWrap" style="display:none;margin-top:.65rem">
              <div style="background:#f1f5f9;border-radius:100px;height:6px;overflow:hidden">
                <div id="uploadProgFill" style="height:100%;background:linear-gradient(90deg,#0891b2,#0ea5e9);border-radius:100px;transition:width .4s;width:0%"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /#mediaPanel -->
  </div>

</div>
</div><!-- /.container-fluid -->

<!-- ── Lightbox modal ──────────────────────────────────────── -->
<div class="modal fade" id="lightboxModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="background:#0f172a;border:none;border-radius:20px">
      <div class="modal-header" style="border:none;padding:1rem 1.5rem">
        <span id="lightboxTitle" style="color:#94a3b8;font-size:.82rem;font-weight:700"></span>
        <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1) brightness(2);opacity:.7"></button>
      </div>
      <div class="modal-body text-center" style="padding:0 1.5rem 1.5rem" id="lightboxBody"></div>
    </div>
  </div>
</div>

<script>
/* ── DCM Alerts ──────────────────────────────────────────── */
const dcmAlert = {
  _css:`.ds-pop{border-radius:20px!important;font-family:'Open Sans',sans-serif!important;padding:1.6rem!important}.ds-ttl{font-size:1.1rem!important;font-weight:800!important;color:#0f172a!important;margin-top:.3rem!important}.ds-btn{border-radius:11px!important;font-weight:700!important;font-size:.82rem!important;padding:.55rem 1.4rem!important}.ds-can{border-radius:11px!important;font-weight:700!important;font-size:.82rem!important;padding:.55rem 1.4rem!important;background:#f1f5f9!important;color:#475569!important;border:1.5px solid #e2e8f0!important}.ds-ico{border:none!important;margin-bottom:.4rem!important}.ds-tst{border-radius:14px!important;font-family:'Open Sans',sans-serif!important;box-shadow:0 8px 32px rgba(0,0,0,.14)!important;padding:.75rem 1.1rem!important;border-left:4px solid}.dst-ok{border-color:#059669!important}.dst-er{border-color:#dc2626!important}`,
  _done:false,
  _inject(){if(!this._done){const s=document.createElement('style');s.textContent=this._css;document.head.appendChild(s);this._done=true;}},
  toast(icon,title,text=''){this._inject();const cls={success:'dst-ok',error:'dst-er'}[icon]||'';Swal.fire({toast:true,position:'top-end',showConfirmButton:false,timer:3200,timerProgressBar:true,icon,title,text,customClass:{popup:`ds-tst ${cls}`}});},
  success(t,x=''){this.toast('success',t,x);},
  error(t,x=''){this._inject();Swal.fire({icon:'error',title:t,text:x||'Something went wrong.',customClass:{popup:'ds-pop',title:'ds-ttl',confirmButton:'ds-btn'},confirmButtonColor:'#dc2626',confirmButtonText:'Got it'});},
  loading(t='Please wait…'){this._inject();Swal.fire({title:t,allowOutsideClick:false,customClass:{popup:'ds-pop',title:'ds-ttl'},didOpen:()=>Swal.showLoading()});},
  confirm({title,text,confirmText='Confirm',confirmColor='#dc2626',onConfirm}){
    this._inject();
    Swal.fire({title,text,icon:'warning',showCancelButton:true,confirmButtonText:confirmText,cancelButtonText:'Cancel',confirmButtonColor,reverseButtons:true,
      customClass:{popup:'ds-pop',title:'ds-ttl',confirmButton:'ds-btn',cancelButton:'ds-can',icon:'ds-ico'},
      showClass:{popup:'animate__animated animate__zoomIn animate__faster'},hideClass:{popup:'animate__animated animate__zoomOut animate__faster'}
    }).then(r=>{if(r.isConfirmed&&onConfirm)onConfirm();});
  }
};

const TYPE_COLORS = { image:{bg:'#eff6ff',color:'#1a4fc4',icon:'bi-image'}, audio:{bg:'#f0fdf4',color:'#059669',icon:'bi-music-note-beamed'}, video:{bg:'#fdf4ff',color:'#7c3aed',icon:'bi-play-circle-fill'}, document:{bg:'#fff7ed',color:'#d97706',icon:'bi-file-earmark-text'} };

let selectedQId = null, selectedFile = null, lightboxModal, searchTimer;

document.addEventListener('DOMContentLoaded', () => {
  lightboxModal = new bootstrap.Modal(document.getElementById('lightboxModal'));
  loadGlobalStats();
  <?php if ($preload_id): ?>selectQuestion({question_id:<?= $preload_id ?>});<?php endif; ?>
});

function loadGlobalStats() {
  fetch('ajax/ajax_qb_media.php?action=stats')
    .then(r=>r.json()).then(res=>{
      if (res.status!=='success') return;
      document.getElementById('kpiTotalMedia').textContent = res.data.total   || '0';
      document.getElementById('kpiImages').textContent     = res.data.image   || '0';
      document.getElementById('kpiAudio').textContent      = res.data.audio   || '0';
      document.getElementById('kpiDocs').textContent       = res.data.document|| '0';
    });
}

/* ── Question search ─────────────────────────────────────── */
function debounceSearch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(searchQuestions, 300);
}

function searchQuestions() {
  const q = document.getElementById('qSearch').value.trim();
  if (q.length < 2) {
    document.getElementById('qResults').innerHTML = '<div style="text-align:center;padding:1.5rem;color:#cbd5e1;font-size:.8rem"><i class="bi bi-search" style="font-size:1.5rem;display:block;margin-bottom:.5rem"></i>Type at least 2 characters</div>';
    return;
  }
  document.getElementById('qResults').innerHTML = '<div style="text-align:center;padding:1.5rem"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
  fetch(`ajax/ajax_qb_media.php?action=search&q=${encodeURIComponent(q)}`)
    .then(r=>r.json()).then(res=>{
      if (res.status!=='success'||!res.data.length) {
        document.getElementById('qResults').innerHTML = '<div style="text-align:center;padding:1.5rem;color:#94a3b8;font-size:.8rem">No questions found.</div>';
        return;
      }
      document.getElementById('qResults').innerHTML = res.data.map(q=>`
        <div class="qbm-q-item${q.question_id==selectedQId?' active':''}" onclick='selectQuestion(${JSON.stringify(q)})'>
          <span class="qbm-q-uid">${q.q_uid}</span>
          <div class="qbm-q-stem">${q.question_stem_plain||q.q_uid}</div>
        </div>`).join('');
    });
}

function selectQuestion(q) {
  selectedQId = q.question_id;
  document.getElementById('noSelPanel').style.display = 'none';
  document.getElementById('mediaPanel').style.display = '';
  document.getElementById('selectedQCard').innerHTML = `
    <div class="qbm-q-sel-icon"><i class="bi bi-patch-question-fill"></i></div>
    <div style="min-width:0;flex:1">
      <div class="qbm-q-sel-uid">${q.q_uid}</div>
      <div class="qbm-q-sel-stem">${q.question_stem_plain||q.question_stem||q.q_uid}</div>
      <div class="qbm-q-sel-meta">
        ${q.subject_name?`<span class="qbm-q-sel-chip"><i class="bi bi-book" style="font-size:.6rem"></i>${q.subject_name}</span>`:''}
        ${q.level_name?`<span class="qbm-q-sel-chip"><i class="bi bi-layers" style="font-size:.6rem"></i>${q.level_name}</span>`:''}
        ${q.chapter_name?`<span class="qbm-q-sel-chip"><i class="bi bi-bookmark" style="font-size:.6rem"></i>${q.chapter_name}</span>`:''}
      </div>
    </div>`;
  loadMedia();
  document.querySelectorAll('.qbm-q-item').forEach(el=>el.classList.toggle('active', el.onclick&&el.onclick.toString().includes(`"question_id":${q.question_id}`)));
}

function clearSelection() {
  selectedQId = null;
  document.getElementById('noSelPanel').style.display = '';
  document.getElementById('mediaPanel').style.display = 'none';
  document.getElementById('qSearch').value = '';
  document.getElementById('qResults').innerHTML = '<div style="text-align:center;padding:2rem;color:#cbd5e1;font-size:.8rem"><i class="bi bi-search" style="font-size:1.5rem;display:block;margin-bottom:.5rem"></i>Search for a question above</div>';
}

/* ── Media load ──────────────────────────────────────────── */
function loadMedia() {
  const grid = document.getElementById('mediaGrid');
  grid.innerHTML = [1,2,3].map(()=>`<div class="qbm-media-card"><div class="qbm-skel" style="aspect-ratio:1;width:100%"></div></div>`).join('');

  fetch(`ajax/ajax_qb_media.php?action=list&question_id=${selectedQId}`)
    .then(r=>r.json()).then(res=>{
      const items = (res.status==='success') ? res.data : [];
      document.getElementById('mediaCountBadge').textContent = `${items.length} file${items.length!==1?'s':''}`;
      if (!items.length) {
        grid.innerHTML = `<div class="qbm-media-empty" style="grid-column:1/-1"><i class="bi bi-images" style="font-size:3rem;color:#cbd5e1;display:block;margin-bottom:.75rem"></i><div style="font-size:.88rem;font-weight:700;color:#94a3b8">No media attached</div><div style="font-size:.77rem;color:#cbd5e1;margin-top:.3rem">Upload files below to attach them to this question.</div></div>`;
        return;
      }
      grid.innerHTML = items.map(m => buildMediaCard(m)).join('');
    });
}

function buildMediaCard(m) {
  const tc = TYPE_COLORS[m.media_type] || TYPE_COLORS.document;
  const isImg = m.media_type==='image';
  const thumbHtml = isImg
    ? `<img src="${m.media_path}" class="qbm-media-thumb" alt="media" onclick="openLightbox('${m.media_path}','${m.media_type}')" style="cursor:pointer">`
    : `<div class="qbm-media-icon-thumb" onclick="openLightbox('${m.media_path}','${m.media_type}')" style="cursor:pointer"><i class="bi ${tc.icon}" style="color:${tc.color}"></i></div>`;
  return `
  <div class="qbm-media-card" id="mcard_${m.media_id}">
    ${thumbHtml}
    <div class="qbm-media-foot">
      <span class="qbm-media-type mt-${m.media_type}" style="background:${tc.bg};color:${tc.color}">${m.media_type}</span>
      <button class="qbm-media-del" onclick="deleteMedia(${m.media_id})" title="Delete"><i class="bi bi-trash"></i></button>
    </div>
  </div>`;
}

/* ── Lightbox ────────────────────────────────────────────── */
function openLightbox(path, type) {
  document.getElementById('lightboxTitle').textContent = type.toUpperCase() + ' — ' + path.split('/').pop();
  let html = '';
  if (type==='image') html = `<img src="${path}" style="max-width:100%;border-radius:12px">`;
  else if (type==='audio') html = `<audio controls style="width:100%;margin:1rem 0"><source src="${path}">Your browser does not support audio.</audio>`;
  else if (type==='video') html = `<video controls style="max-width:100%;border-radius:12px"><source src="${path}">Your browser does not support video.</video>`;
  else html = `<a href="${path}" target="_blank" class="btn btn-primary mt-3"><i class="bi bi-download me-1"></i>Open / Download</a>`;
  document.getElementById('lightboxBody').innerHTML = html;
  lightboxModal.show();
}

/* ── File choose / drag-drop ─────────────────────────────── */
function onMediaFileChosen(input) {
  if (!input.files.length) return;
  selectedFile = input.files[0];
  showUploadInfo(selectedFile);
}

function showUploadInfo(file) {
  document.getElementById('mediaFileName').textContent = file.name + ' (' + (file.size/1024).toFixed(1) + ' KB)';
  const ext = file.name.split('.').pop().toLowerCase();
  const typeMap = {jpg:'image',jpeg:'image',png:'image',gif:'image',webp:'image',mp3:'audio',wav:'audio',ogg:'audio',mp4:'video',pdf:'document',doc:'document',docx:'document'};
  const detected = typeMap[ext] || 'document';
  document.getElementById('mediaType').value = detected;
  document.getElementById('mediaUploadInfo').style.display = '';
}

function cancelUpload() { selectedFile=null; document.getElementById('mediaUploadInfo').style.display='none'; document.getElementById('mediaFileInput').value=''; }

function mediaDragOver(e) { e.preventDefault(); document.getElementById('mediaDropZone').classList.add('drag-over'); }
function mediaDragLeave()  { document.getElementById('mediaDropZone').classList.remove('drag-over'); }
function mediaDrop(e) {
  e.preventDefault(); document.getElementById('mediaDropZone').classList.remove('drag-over');
  if (e.dataTransfer.files.length) { selectedFile=e.dataTransfer.files[0]; showUploadInfo(selectedFile); }
}

/* ── Upload ──────────────────────────────────────────────── */
function uploadMedia() {
  if (!selectedFile||!selectedQId) return;
  if (selectedFile.size > 20*1024*1024) { dcmAlert.error('File too large','Maximum file size is 20 MB.'); return; }

  document.getElementById('doUploadBtn').disabled = true;
  document.getElementById('uploadProgWrap').style.display = '';
  document.getElementById('uploadProgFill').style.width = '30%';

  const fd = new FormData();
  fd.append('action', 'upload');
  fd.append('question_id', selectedQId);
  fd.append('media_type', document.getElementById('mediaType').value);
  fd.append('media_file', selectedFile);

  fetch('ajax/ajax_qb_media.php', {method:'POST',body:fd})
    .then(r=>r.json())
    .then(res=>{
      document.getElementById('uploadProgFill').style.width = '100%';
      setTimeout(()=>{
        document.getElementById('uploadProgWrap').style.display='none';
        document.getElementById('uploadProgFill').style.width='0%';
        document.getElementById('doUploadBtn').disabled=false;
      },400);
      if (res.status==='success') {
        dcmAlert.success('Media uploaded!', selectedFile.name + ' attached to question.');
        cancelUpload();
        loadMedia();
        loadGlobalStats();
      } else {
        dcmAlert.error('Upload failed', res.message);
      }
    })
    .catch(()=>{ document.getElementById('doUploadBtn').disabled=false; dcmAlert.error('Upload failed','Could not reach the server.'); });
}

/* ── Delete media ────────────────────────────────────────── */
function deleteMedia(id) {
  dcmAlert.confirm({
    title:'Delete this media file?',
    text:'This will permanently remove the file and cannot be undone.',
    confirmText:'<i class="bi bi-trash me-1"></i>Yes, delete it',
    confirmColor:'#dc2626',
    onConfirm() {
      dcmAlert.loading('Deleting…');
      fetch('ajax/ajax_qb_media.php', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'delete',media_id:id})})
        .then(r=>r.json()).then(res=>{
          Swal.close();
          if (res.status==='success') { dcmAlert.success('Deleted!','Media file removed.'); loadMedia(); loadGlobalStats(); }
          else dcmAlert.error('Delete failed', res.message);
        }).catch(()=>dcmAlert.error('Request failed','Unable to reach the server.'));
    }
  });
}
</script>
