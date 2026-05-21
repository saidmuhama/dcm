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

<!-- ── Create Course Name ───────────────────────────────── -->
<div class="modal fade" id="createCourseModal" tabindex="-1"
     aria-labelledby="createCourseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:460px">
    <div class="modal-content" data-dcm-accent="brand">

      <div class="modal-header dcm-hdr-brand">
        <div class="d-flex align-items-center gap-2">
          <div class="dcm-modal-icon"><i class="bi bi-collection-play-fill"></i></div>
          <div>
            <div class="dcm-modal-title">New Course</div>
            <div class="dcm-modal-sub">Give your course a clear, descriptive name</div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="createCourseForm" novalidate>
        <div class="modal-body">
          <label class="form-label" for="newCourseTitle">Course Title <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="newCourseTitle"
                 placeholder="e.g. Modern Agriculture Fundamentals" required autocomplete="off">
          <div class="invalid-feedback">Please enter a course title.</div>
          <div class="mt-3 p-3 rounded-3" style="background:#f0f6ff;border:1.5px solid #c7d9fc">
            <div class="d-flex align-items-center gap-2 mb-1">
              <i class="bi bi-lightbulb-fill text-primary" style="font-size:.85rem"></i>
              <span class="fw-semibold text-primary" style="font-size:.78rem">Pro tip</span>
            </div>
            <p class="mb-0 text-muted" style="font-size:.76rem;line-height:1.5">
              A great course title is specific, benefit-driven, and under 70 characters.
              You can always change it later from the course settings.
            </p>
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
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('createCourseForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const titleInput = document.getElementById('newCourseTitle');
        const title = titleInput.value.trim();
        const btn = document.getElementById('saveCourseBtn');

        if (!title) {
            titleInput.classList.add('is-invalid');
            return;
        }
        titleInput.classList.remove('is-invalid');

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Creating…';

        fetch('ajax/ajax_save_course.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title })
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success' && res.course_id) {
                window.location.href = '?view=course_contents_management&course_id=' + res.course_id;
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
