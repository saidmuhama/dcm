<!-- ══════════════════════════════════════════════════════════
     DCM GLOBAL MODALS  (modal_lunch.php)
     All modals use the dcm-system.css design tokens.
══════════════════════════════════════════════════════════ -->

<!-- ── Choose Product / Add Course ─────────────────────── -->
<div class="modal fade" id="addCourseModal" tabindex="-1"
     aria-labelledby="addCourseModalLabel" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content" data-dcm-accent="brand">

      <div class="modal-header dcm-hdr-brand">
        <div class="d-flex align-items-center gap-2">
          <div class="dcm-modal-icon"><i class="bi bi-plus-square-dotted"></i></div>
          <div>
            <div class="dcm-modal-title">Create New Product</div>
            <div class="dcm-modal-sub">Choose a product type to start building your content</div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <?php include('pages/modal_lunch_content.php'); ?>
      </div>

      <div class="modal-footer">
        <button type="button" class="dcm-btn dcm-btn-ghost dcm-btn-sm" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i>Cancel
        </button>
        <button type="button" class="dcm-btn dcm-btn-primary dcm-btn-sm">
          <i class="bi bi-check2-circle me-1"></i>Save Changes
        </button>
      </div>

    </div>
  </div>
</div>

<?php
/* Load active categories for the course-creation picker */
$_mlCats = [];
if (isset($db)) {
    $__r = $db->query("SELECT id, category_title, icon, category_code FROM tbl_course_categories WHERE status=1 ORDER BY sort_order,id");
    if ($__r) $_mlCats = $__r->fetch_all(MYSQLI_ASSOC);
}
$_mlCatColors = ['#6366f1','#10b981','#f59e0b','#3b82f6','#8b5cf6','#ec4899','#14b8a6','#f97316','#84cc16','#06b6d4','#a855f7','#ef4444'];
?>
<style>
.ncc-cat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:.5rem;max-height:220px;overflow-y:auto;padding:.15rem .05rem}
.ncc-cat-grid::-webkit-scrollbar{width:4px}
.ncc-cat-grid::-webkit-scrollbar-thumb{background:rgba(99,102,241,.2);border-radius:4px}
.ncc-cat-item{border:2px solid #e0e7ff;border-radius:11px;padding:.55rem .4rem;cursor:pointer;text-align:center;transition:all .18s;user-select:none;background:#fff;position:relative}
.ncc-cat-item:hover{border-color:#a5b4fc;box-shadow:0 3px 10px rgba(99,102,241,.12);transform:translateY(-2px)}
.ncc-cat-item.selected{border-color:#6366f1;background:linear-gradient(135deg,#ede9fe,#eff6ff)}
.ncc-cat-item.selected::after{content:'\F26E';font-family:'bootstrap-icons';position:absolute;top:3px;right:5px;font-size:.65rem;color:#6366f1;font-weight:900}
.ncc-select-hint{font-size:.72rem;color:#94a3b8;margin-bottom:.5rem}
.ncc-cat-icon{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.95rem;margin:0 auto .35rem;transition:transform .18s}
.ncc-cat-item:hover .ncc-cat-icon,.ncc-cat-item.selected .ncc-cat-icon{transform:scale(1.1) rotate(-5deg)}
.ncc-cat-name{font-size:.66rem;font-weight:700;color:#334155;line-height:1.25;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}
.ncc-cat-item.selected .ncc-cat-name{color:#4f46e5}
.ncc-cat-search{border:1.5px solid #e0e7ff;border-radius:9px;padding:.38rem .7rem .38rem 1.9rem;font-size:.8rem;width:100%;background:#f8f7ff;transition:all .2s}
.ncc-cat-search:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1);background:#fff}
.ncc-selected-pill{display:inline-flex;align-items:center;gap:.35rem;background:linear-gradient(135deg,#ede9fe,#e0e7ff);color:#4f46e5;font-size:.73rem;font-weight:700;padding:.25rem .75rem;border-radius:20px;margin-top:.6rem;animation:nccPillIn .2s cubic-bezier(.34,1.56,.64,1) both}
@keyframes nccPillIn{from{opacity:0;transform:scale(.7)} to{opacity:1;transform:scale(1)}}
.ncc-no-cat{text-align:center;color:#94a3b8;padding:.75rem;font-size:.78rem}
</style>

<!-- ── Create Course Name ───────────────────────────────── -->
<div class="modal fade" id="createCourseModal" tabindex="-1"
     aria-labelledby="createCourseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" data-dcm-accent="brand">

      <div class="modal-header dcm-hdr-brand">
        <div class="d-flex align-items-center gap-2">
          <div class="dcm-modal-icon"><i class="bi bi-collection-play-fill"></i></div>
          <div>
            <div class="dcm-modal-title">Create New Course</div>
            <div class="dcm-modal-sub">Set a title and category to help students discover your course</div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="createCourseForm" novalidate>
        <div class="modal-body">
          <div class="row g-3">

            <!-- Title -->
            <div class="col-12">
              <label class="form-label fw-semibold" style="font-size:.8rem" for="newCourseTitle">
                Course Title <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control" id="newCourseTitle"
                     placeholder="e.g. Modern Agriculture Fundamentals" required autocomplete="off"
                     style="border-radius:10px;border:1.5px solid #e0e7ff;font-size:.88rem">
              <div class="invalid-feedback">Please enter a course title.</div>
            </div>

            <!-- Category picker -->
            <div class="col-12">
              <label class="form-label fw-semibold d-flex align-items-center gap-2" style="font-size:.8rem">
                <span>Course Category</span>
                <span class="text-muted fw-normal" style="font-size:.72rem">(recommended — helps with student recommendations)</span>
              </label>

              <!-- Search -->
              <div class="position-relative mb-2">
                <i class="bi bi-search position-absolute" style="left:.65rem;top:50%;transform:translateY(-50%);color:#a5b4fc;font-size:.8rem"></i>
                <input type="text" class="ncc-cat-search" id="nccCatSearch" placeholder="Filter categories…" oninput="nccFilter(this.value)" autocomplete="off">
              </div>

              <!-- Grid -->
              <div class="ncc-select-hint"><i class="bi bi-check2-square me-1"></i>Select one or more categories — students will see this course in all matching recommendation feeds</div>
              <div class="ncc-cat-grid" id="nccGrid">
                <?php if (empty($_mlCats)): ?>
                  <div class="ncc-no-cat" style="grid-column:1/-1">No categories available yet</div>
                <?php else: foreach ($_mlCats as $ci => $cat): ?>
                  <?php $cc = $_mlCatColors[$ci % count($_mlCatColors)]; ?>
                  <div class="ncc-cat-item"
                       data-id="<?= $cat['id'] ?>"
                       data-name="<?= htmlspecialchars($cat['category_title']) ?>"
                       data-search="<?= strtolower(htmlspecialchars($cat['category_title'].' '.$cat['category_code'])) ?>"
                       onclick="nccToggle(this)">
                    <div class="ncc-cat-icon" style="background:<?= $cc ?>18;color:<?= $cc ?>">
                      <i class="bi <?= htmlspecialchars($cat['icon'] ?? 'bi-grid') ?>"></i>
                    </div>
                    <div class="ncc-cat-name"><?= htmlspecialchars($cat['category_title']) ?></div>
                  </div>
                <?php endforeach; endif; ?>
              </div>

              <!-- Selected pill -->
              <div id="nccSelectedWrap" style="min-height:1.6rem"></div>
            </div>

            <!-- Tip -->
            <div class="col-12">
              <div class="p-3 rounded-3" style="background:#f0f6ff;border:1.5px solid #c7d9fc">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <i class="bi bi-lightbulb-fill text-primary" style="font-size:.85rem"></i>
                  <span class="fw-semibold text-primary" style="font-size:.78rem">Pro tip</span>
                </div>
                <p class="mb-0 text-muted" style="font-size:.76rem;line-height:1.5">
                  A clear title + the right category means your course appears in student recommendation feeds.
                  Both can be changed anytime from Course Settings.
                </p>
              </div>
            </div>

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="dcm-btn dcm-btn-ghost dcm-btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" id="saveCourseBtn" class="dcm-btn dcm-btn-primary dcm-btn-sm">
            <i class="bi bi-arrow-right-circle-fill me-1"></i>Save &amp; Proceed
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- ── Add Chapter ─────────────────────────────────────── -->
<div class="modal fade" id="createChapterModal" tabindex="-1"
     aria-labelledby="createChapterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:460px">
    <div class="modal-content" data-dcm-accent="brand">

      <div class="modal-header" style="background:linear-gradient(135deg,#f0f6ff,#e8eeff);border-bottom:1px solid #c7d9fc">
        <div class="d-flex align-items-center gap-2">
          <div class="dcm-modal-icon icon-light"><i class="bi bi-bookmark-plus-fill"></i></div>
          <div>
            <div class="dcm-modal-title" style="color:#1e293b">Add Chapter</div>
            <div class="dcm-modal-sub" style="color:#64748b">Organise your lessons into logical chapters</div>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="addChapterForm" novalidate>
        <div class="modal-body">
          <label class="form-label" for="newChapterTitle">Chapter Title <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="newChapterTitle"
                 placeholder="e.g. Introduction to Islamic Banking" required autocomplete="off">
          <div class="invalid-feedback">Please enter a chapter title.</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="dcm-btn dcm-btn-ghost dcm-btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" id="saveChapterBtn" class="dcm-btn dcm-btn-primary dcm-btn-sm">
            <i class="bi bi-check2-circle me-1"></i>Save &amp; Proceed
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<script>
/* ── Multi-select category picker ── */
window._nccSelected = new Set();

window.nccToggle = function(el) {
    const id = el.dataset.id;
    if (_nccSelected.has(id)) {
        _nccSelected.delete(id);
        el.classList.remove('selected');
    } else {
        _nccSelected.add(id);
        el.classList.add('selected');
    }
    nccRenderPills();
};
window.nccRemove = function(id) {
    _nccSelected.delete(id);
    const el = document.querySelector(`.ncc-cat-item[data-id="${id}"]`);
    if (el) el.classList.remove('selected');
    nccRenderPills();
};
window.nccRenderPills = function() {
    const wrap = document.getElementById('nccSelectedWrap');
    if (!_nccSelected.size) { wrap.innerHTML = ''; return; }
    wrap.innerHTML = [..._nccSelected].map(id => {
        const el = document.querySelector(`.ncc-cat-item[data-id="${id}"]`);
        const name = el ? el.dataset.name : id;
        return `<span class="ncc-selected-pill">
            <i class="bi bi-check-circle-fill"></i>${name}
            <button type="button" onclick="nccRemove('${id}')" style="background:none;border:none;padding:0;margin-left:.2rem;color:#4f46e5;font-size:.85rem;cursor:pointer;line-height:1;font-weight:700">&times;</button>
        </span>`;
    }).join('');
};
window.nccClear = function() {
    _nccSelected.clear();
    document.querySelectorAll('.ncc-cat-item').forEach(c => c.classList.remove('selected'));
    document.getElementById('nccSelectedWrap').innerHTML = '';
};
window.nccFilter = function(q) {
    const lq = q.toLowerCase();
    document.querySelectorAll('.ncc-cat-item').forEach(el => {
        el.style.display = (el.dataset.search || '').includes(lq) ? '' : 'none';
    });
};
/* Also keep nccSelect as alias for external callers */
window.nccSelect = nccToggle;

/* ── Form submit ── */
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('createCourseForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const titleInput = document.getElementById('newCourseTitle');
        const title       = titleInput.value.trim();
        const categoryIds = [..._nccSelected].map(Number).filter(Boolean);
        const btn         = document.getElementById('saveCourseBtn');

        if (!title) {
            titleInput.classList.add('is-invalid');
            titleInput.focus();
            return;
        }
        titleInput.classList.remove('is-invalid');

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Creating…';

        fetch('ajax/ajax_save_course.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ title, category_ids: categoryIds })
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success' && res.course_token) {
                window.location.href = '?view=course_contents_management&course_id=' + encodeURIComponent(res.course_token);
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-right-circle-fill me-1"></i>Save &amp; Proceed';
                Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Could not create course.' });
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-right-circle-fill me-1"></i>Save &amp; Proceed';
            Swal.fire({ icon: 'error', title: 'Error', text: 'Network error. Please try again.' });
        });
    });

    /* Reset picker when modal closes */
    const modal = document.getElementById('createCourseModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('newCourseTitle').value = '';
            document.getElementById('newCourseTitle').classList.remove('is-invalid');
            nccClear();
            if (document.getElementById('nccCatSearch')) {
                document.getElementById('nccCatSearch').value = '';
                nccFilter('');
            }
        });
    }
});
</script>

<!-- dcm-btn base styles (shared with all pages) -->
<style>
  .dcm-btn {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .58rem 1.2rem; border: none; border-radius: 11px;
    font-size: .8rem; font-weight: 700; cursor: pointer; text-decoration: none;
    white-space: nowrap; transition: filter .15s, box-shadow .15s, transform .1s;
    font-family: inherit; line-height: 1.3;
  }
  .dcm-btn:active { transform: scale(.96); }
  .dcm-btn-primary {
    background: linear-gradient(135deg, #1a4fc4 0%, #6d28d9 100%);
    color: #fff; box-shadow: 0 4px 14px rgba(26,79,196,.28);
  }
  .dcm-btn-primary:hover { filter: brightness(1.09); box-shadow: 0 6px 22px rgba(26,79,196,.38); color: #fff; }
  .dcm-btn-amber {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #fff; box-shadow: 0 4px 14px rgba(245,158,11,.25);
  }
  .dcm-btn-amber:hover { filter: brightness(1.08); color: #fff; }
  .dcm-btn-success {
    background: linear-gradient(135deg, #059669 0%, #0d9488 100%);
    color: #fff; box-shadow: 0 4px 12px rgba(5,150,105,.25);
  }
  .dcm-btn-success:hover { filter: brightness(1.09); color: #fff; }
  .dcm-btn-danger {
    background: linear-gradient(135deg, #dc2626 0%, #9f1239 100%);
    color: #fff; box-shadow: 0 4px 12px rgba(220,38,38,.22);
  }
  .dcm-btn-danger:hover { filter: brightness(1.09); color: #fff; }
  .dcm-btn-ghost {
    background: #f1f5f9; color: #475569;
    border: 1.5px solid #e2e8f0; box-shadow: none;
  }
  .dcm-btn-ghost:hover { background: #e2e8f0; color: #1e293b; border-color: #cbd5e1; }
  .dcm-btn-sm  { padding: .4rem .9rem;  font-size: .76rem; border-radius: 9px; }
  .dcm-btn-xs  { padding: .25rem .6rem; font-size: .7rem;  border-radius: 7px; }
  .icn-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px; border-radius: 8px; border: none; cursor: pointer;
    font-size: .78rem; transition: background .15s, color .15s, transform .1s;
    flex-shrink: 0;
  }
  .icn-btn:active { transform: scale(.88); }
  .icn-btn-view   { background: #eff6ff; color: #1d4ed8; }
  .icn-btn-view:hover   { background: #1d4ed8; color: #fff; }
  .icn-btn-edit   { background: #f0f9ff; color: #0369a1; }
  .icn-btn-edit:hover   { background: #0369a1; color: #fff; }
  .icn-btn-rename { background: #f5f3ff; color: #7c3aed; }
  .icn-btn-rename:hover { background: #7c3aed; color: #fff; }
  .icn-btn-del    { background: #fff1f2; color: #e11d48; }
  .icn-btn-del:hover    { background: #e11d48; color: #fff; }
  .icn-btn-ok     { background: #f0fdf4; color: #16a34a; }
  .icn-btn-ok:hover     { background: #16a34a; color: #fff; }
  .icn-btn-ok.active    { background: #16a34a; color: #fff; }
</style>
