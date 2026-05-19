<?php
$user_role = $user_role ?? 0;
if ($user_role != 5) { include('403.php'); return; }

$cid = (int)($_GET['cid'] ?? 0);
if (!$cid) { include('404.php'); return; }

$course = $db->query("
    SELECT c.*, u.first_name, u.last_name, u.email_address,
           cat.category_title AS category_name
    FROM tbl_courses c
    LEFT JOIN tbl_all_users u ON u.usr_code = c.instructor_id
    LEFT JOIN tbl_course_categories cat ON cat.id = c.category_id
    WHERE c.id = $cid AND c.deleted_at IS NULL
    LIMIT 1
")->fetch_assoc();

if (!$course) { include('404.php'); return; }
?>

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<div class="container-fluid px-3 py-3">

  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0 small">
      <li class="breadcrumb-item"><a href="?view=admin_dashboard">Admin</a></li>
      <li class="breadcrumb-item"><a href="?view=admin_courses">All Courses</a></li>
      <li class="breadcrumb-item active text-truncate" style="max-width:260px"><?= htmlspecialchars($course['title']) ?></li>
    </ol>
  </nav>

  <!-- Course header card -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <div class="d-flex align-items-start gap-3 flex-wrap">
        <img src="../<?= htmlspecialchars($course['thumbnail'] ?? 'assets/img/logo.svg') ?>"
             class="rounded-3 object-fit-cover flex-shrink-0" width="80" height="80"
             onerror="this.src='../assets/img/logo.svg'">
        <div class="flex-grow-1">
          <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
            <div>
              <h5 class="fw-bold mb-1"><?= htmlspecialchars($course['title']) ?></h5>
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <small class="text-muted"><i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($course['first_name'].' '.$course['last_name']) ?></small>
                <small class="text-muted">·</small>
                <small class="text-muted"><i class="bi bi-tag-fill me-1"></i><?= htmlspecialchars($course['category_name'] ?? 'Uncategorised') ?></small>
                <?php if ($course['price'] > 0): ?>
                <small class="text-muted">·</small>
                <small class="text-muted"><i class="bi bi-cash me-1"></i>TZS <?= number_format($course['price']) ?></small>
                <?php endif; ?>
              </div>
            </div>
            <div class="d-flex gap-2 align-items-center">
              <!-- Quick approval toggle -->
              <?php
              $approvalStyles = [
                  'pending'  => ['bg'=>'#fef9c3','color'=>'#92400e','border'=>'#fde68a'],
                  'approved' => ['bg'=>'#dcfce7','color'=>'#15803d','border'=>'#bbf7d0'],
                  'rejected' => ['bg'=>'#fee2e2','color'=>'#b91c1c','border'=>'#fecaca'],
              ];
              $statusStyles = [
                  'active'   => ['bg'=>'#dcfce7','color'=>'#15803d','border'=>'#bbf7d0','label'=>'Active'],
                  'is_draft' => ['bg'=>'#fef9c3','color'=>'#92400e','border'=>'#fde68a','label'=>'Draft'],
                  'inactive' => ['bg'=>'#f1f5f9','color'=>'#475569','border'=>'#e2e8f0','label'=>'Inactive'],
              ];
              $as = $approvalStyles[$course['is_approved']] ?? ['bg'=>'#f1f5f9','color'=>'#475569','border'=>'#e2e8f0'];
              $ss = $statusStyles[$course['status']]       ?? ['bg'=>'#f1f5f9','color'=>'#475569','border'=>'#e2e8f0','label'=>$course['status']];
              ?>
              <span style="display:inline-block;padding:.28rem .75rem;border-radius:100px;font-size:.75rem;font-weight:700;background:<?= $as['bg'] ?>;color:<?= $as['color'] ?>;border:1.5px solid <?= $as['border'] ?>;text-transform:capitalize"><?= htmlspecialchars($course['is_approved']) ?></span>
              <span style="display:inline-block;padding:.28rem .75rem;border-radius:100px;font-size:.75rem;font-weight:700;background:<?= $ss['bg'] ?>;color:<?= $ss['color'] ?>;border:1.5px solid <?= $ss['border'] ?>"><?= htmlspecialchars($ss['label']) ?></span>
              <button class="dcm-btn dcm-btn-primary dcm-btn-sm" id="btnEditCourse"><i class="bi bi-pencil-fill me-1"></i>Edit Course</button>
            </div>
          </div>
          <?php if ($course['description']): ?>
          <p class="text-muted small mt-2 mb-0" style="max-width:700px"><?= nl2br(htmlspecialchars(substr($course['description'], 0, 200))) ?><?= strlen($course['description']) > 200 ? '…' : '' ?></p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Custom Tab Bar ── -->
  <div class="detail-tab-bar" id="detailTabs">
    <button class="detail-tab active" data-tab="content">
      <i class="bi bi-layout-text-sidebar-reverse"></i>
      <span>Chapters &amp; Lessons</span>
    </button>
    <button class="detail-tab" data-tab="instructor_qa">
      <span class="dtb-icon-wrap amber"><i class="bi bi-patch-question-fill"></i></span>
      <span>Instructor Q&amp;A</span>
      <span class="dtb-count amber" id="iqaBadgeCount" style="display:none"></span>
    </button>
    <button class="detail-tab" data-tab="student_qa">
      <span class="dtb-icon-wrap blue"><i class="bi bi-chat-left-dots-fill"></i></span>
      <span>Student Discussions</span>
      <span class="dtb-count blue" id="sqaBadgeCount" style="display:none"></span>
    </button>
  </div>

  <!-- ── TAB: Chapters & Lessons ───────────────────────── -->
  <div id="tabContent" class="tab-pane-content">
    <div class="card border-0 shadow-sm border-top-0 rounded-top-0">
      <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="text-muted small">Drag chapters or lessons to reorder. Changes save automatically.</span>
          <button class="dcm-btn dcm-btn-primary dcm-btn-sm" id="btnAddChapter">
            <i class="bi bi-plus-circle-fill me-1"></i>Add Chapter
          </button>
        </div>

        <div id="chaptersSpinner" class="text-center py-5">
          <div class="spinner-border text-primary"></div>
        </div>
        <div id="chaptersList" style="display:none"></div>

      </div>
    </div>
  </div>

  <!-- ── TAB: Instructor Q&A ── -->
  <div id="tabInstructorQa" class="tab-pane-content" style="display:none">
    <div class="qa-panel-wrapper">
      <div class="qa-panel-header amber-theme">
        <div class="qph-left">
          <div class="qph-icon"><i class="bi bi-patch-question-fill"></i></div>
          <div>
            <div class="qph-title">Instructor Study Notes &amp; Q&amp;A</div>
            <div class="qph-sub">Q&amp;A pairs prepared per lesson. Drag handles let you reorder within each lesson.</div>
          </div>
        </div>
      </div>
      <div class="qa-panel-body">
        <div id="snSpinner" class="qa-loading"><div class="spinner-border text-warning"></div><span>Loading study notes…</span></div>
        <div id="snTree" style="display:none"></div>
      </div>
    </div>
  </div>

  <!-- ── TAB: Student Discussions ── -->
  <div id="tabStudentQa" class="tab-pane-content" style="display:none">
    <div class="qa-panel-wrapper">
      <div class="qa-panel-header blue-theme">
        <div class="qph-left">
          <div class="qph-icon"><i class="bi bi-chat-left-dots-fill"></i></div>
          <div>
            <div class="qph-title">Student Discussions</div>
            <div class="qph-sub">Questions posted by enrolled students. Moderate, answer, or remove as needed.</div>
          </div>
        </div>
        <button class="qph-btn" id="btnAddStudentQ">
          <i class="bi bi-plus-circle-fill me-1"></i>New Question
        </button>
      </div>
      <div class="qa-panel-body">
        <div id="sqaSpinner" class="qa-loading"><div class="spinner-border text-primary"></div><span>Loading discussions…</span></div>
        <div id="sqaList" style="display:none"></div>
      </div>
    </div>
  </div>

</div><!-- .container-fluid -->

<!-- ── Lesson 360 Viewer Modal ── -->
<div class="modal fade" id="lessonViewerModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width:900px">
    <div class="modal-content" data-dcm-accent="dark">

      <!-- Dark header -->
      <div class="lv-header">
        <div class="lv-type-icon" id="lv_type_icon"></div>
        <div style="flex:1;min-width:0">
          <div class="text-white fw-bold" id="lv_title" style="font-size:.96rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">Loading…</div>
          <div id="lv_meta" style="font-size:.72rem;color:#94a3b8;margin-top:.15rem"></div>
        </div>
        <button type="button" class="btn-close btn-close-white flex-shrink-0" data-bs-dismiss="modal"></button>
      </div>

      <!-- Content area -->
      <div class="lv-content-wrap">
        <div id="lv_content" style="width:100%">
          <div class="qa-loading" style="min-height:380px">
            <div class="spinner-border text-white opacity-50"></div>
          </div>
        </div>
      </div>

      <!-- Info strip -->
      <div class="lv-info-strip" id="lv_info"></div>

    </div>
  </div>
</div>

<!-- ── Edit Course Modal ── -->
<div class="modal fade" id="editCourseModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" data-dcm-accent="brand">
      <div class="modal-header dcm-hdr-brand">
        <div class="d-flex align-items-center gap-2">
          <div class="dcm-modal-icon"><i class="bi bi-pencil-square"></i></div>
          <div>
            <div class="dcm-modal-title">Edit Course</div>
            <div class="dcm-modal-sub">Update course details, status, and pricing</div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Course Title</label>
            <input type="text" id="ec_title" class="form-control" value="<?= htmlspecialchars($course['title']) ?>">
          </div>
          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea id="ec_desc" class="form-control" rows="3"><?= htmlspecialchars($course['description'] ?? '') ?></textarea>
          </div>
          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select id="ec_status" class="form-select">
              <option value="active"   <?= $course['status']==='active'?'selected':'' ?>>Active</option>
              <option value="is_draft" <?= $course['status']==='is_draft'?'selected':'' ?>>Draft</option>
              <option value="inactive" <?= $course['status']==='inactive'?'selected':'' ?>>Inactive</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Approval</label>
            <select id="ec_approval" class="form-select">
              <option value="pending"  <?= $course['is_approved']==='pending'?'selected':'' ?>>Pending</option>
              <option value="approved" <?= $course['is_approved']==='approved'?'selected':'' ?>>Approved</option>
              <option value="rejected" <?= $course['is_approved']==='rejected'?'selected':'' ?>>Rejected</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Price (TZS)</label>
            <input type="number" id="ec_price" class="form-control" value="<?= $course['price'] ?>" min="0">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="dcm-btn dcm-btn-ghost dcm-btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="dcm-btn dcm-btn-primary dcm-btn-sm" id="saveCourseBtn"><i class="bi bi-check2-circle me-1"></i>Save Changes</button>
      </div>
    </div>
  </div>
</div>

<!-- ── Edit Lesson Modal ── -->
<div class="modal fade" id="editLessonModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" data-dcm-accent="brand">
      <div class="modal-header" style="background:linear-gradient(135deg,#f0f6ff,#eaedff);border-bottom:1px solid #c7d9fc">
        <div class="d-flex align-items-center gap-2">
          <div class="dcm-modal-icon icon-light"><i class="bi bi-play-circle-fill"></i></div>
          <div>
            <div class="dcm-modal-title" style="color:#1e293b">Edit Lesson</div>
            <div class="dcm-modal-sub" style="color:#64748b">Update lesson details and visibility</div>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="el_id">
        <div class="mb-3">
          <label class="form-label">Lesson Title</label>
          <input type="text" id="el_title" class="form-control" placeholder="Enter lesson title…">
        </div>
        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea id="el_desc" class="form-control" rows="3" placeholder="Brief description of what this lesson covers…"></textarea>
        </div>
        <div class="row g-3">
          <div class="col-6">
            <label class="form-label">Visibility</label>
            <select id="el_status" class="form-select">
              <option value="active">Active</option>
              <option value="inactive">Hidden</option>
            </select>
          </div>
          <div class="col-6 d-flex align-items-end" style="padding-bottom:.45rem">
            <div class="form-check" style="padding-left:1.6rem">
              <input class="form-check-input" type="checkbox" id="el_free">
              <label class="form-check-label" style="font-size:.8rem;font-weight:600;color:#374151" for="el_free">
                <i class="bi bi-unlock-fill text-success me-1" style="font-size:.8rem"></i>Free preview
              </label>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="dcm-btn dcm-btn-ghost dcm-btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="dcm-btn dcm-btn-primary dcm-btn-sm" id="saveLessonBtn"><i class="bi bi-check2-circle me-1"></i>Save Changes</button>
      </div>
    </div>
  </div>
</div>

<!-- ── Question Modal ── -->
<div class="modal fade" id="questionModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" data-dcm-accent="brand">
      <div class="modal-header dcm-hdr-brand">
        <div class="d-flex align-items-center gap-2">
          <div class="dcm-modal-icon" id="qModalIcon"><i class="bi bi-chat-left-dots-fill"></i></div>
          <div>
            <div class="dcm-modal-title" id="questionModalTitle">Student Question</div>
            <div class="dcm-modal-sub">Add or edit a question for this course</div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="q_id">
        <input type="hidden" id="q_type" value="student">
        <div class="mb-3">
          <label class="form-label">Question Title <span class="text-danger">*</span></label>
          <input type="text" id="q_title" class="form-control" placeholder="Short, descriptive title…">
        </div>
        <div class="mb-0">
          <label class="form-label">Details</label>
          <textarea id="q_desc" class="form-control" rows="4" placeholder="Provide the full context or details of the question…"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="dcm-btn dcm-btn-ghost dcm-btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="dcm-btn dcm-btn-primary dcm-btn-sm" id="saveQuestionBtn"><i class="bi bi-check2-circle me-1"></i>Save Question</button>
      </div>
    </div>
  </div>
</div>

<!-- ── Study Note Modal ── -->
<div class="modal fade" id="studyNoteModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content" data-dcm-accent="amber">
      <div class="modal-header dcm-hdr-amber">
        <div class="d-flex align-items-center gap-2">
          <div class="dcm-modal-icon"><i class="bi bi-patch-question-fill"></i></div>
          <div>
            <div class="dcm-modal-title" id="snModalTitle">Study Note</div>
            <div class="dcm-modal-sub">Instructor-prepared Q&amp;A paired to a specific lesson</div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="sn_id">
        <input type="hidden" id="sn_chapter_id">
        <input type="hidden" id="sn_lesson_id">
        <div class="mb-3">
          <label class="form-label">Lesson</label>
          <div id="sn_lesson_label" class="form-control bg-light text-muted" style="cursor:default;font-size:.83rem"></div>
        </div>
        <div class="mb-3">
          <label class="form-label">Question <span class="text-danger">*</span></label>
          <textarea id="sn_question" class="form-control" rows="2" placeholder="Enter the question…"></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Answer <span class="text-danger">*</span></label>
          <textarea id="sn_answer" class="form-control" rows="4" placeholder="Write the detailed answer…"></textarea>
        </div>
        <div class="dcm-field-section">
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label">Language</label>
              <select id="sn_language" class="form-select">
                <option value="EN">🇬🇧 English</option>
                <option value="SW">🇹🇿 Swahili</option>
                <option value="FR">🇫🇷 French</option>
                <option value="AR">🇸🇦 Arabic</option>
              </select>
            </div>
            <div class="col-6 d-flex align-items-end" style="padding-bottom:.45rem">
              <div class="form-check" style="padding-left:1.6rem">
                <input class="form-check-input" type="checkbox" id="sn_important">
                <label class="form-check-label" style="font-size:.8rem;font-weight:600;color:#374151" for="sn_important">
                  <i class="bi bi-star-fill text-warning me-1"></i>Mark as Important
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="dcm-btn dcm-btn-ghost dcm-btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="dcm-btn dcm-btn-amber dcm-btn-sm" id="saveStudyNoteBtn"><i class="bi bi-check2-circle me-1"></i>Save Note</button>
      </div>
    </div>
  </div>
</div>

<!-- ── Answer Modal ── -->
<div class="modal fade" id="answerModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" data-dcm-accent="success">
      <div class="modal-header dcm-hdr-success">
        <div class="d-flex align-items-center gap-2">
          <div class="dcm-modal-icon"><i class="bi bi-patch-check-fill"></i></div>
          <div>
            <div class="dcm-modal-title" id="answerModalTitle">Add Answer</div>
            <div class="dcm-modal-sub">Write a clear, helpful answer to the student's question</div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="a_id">
        <input type="hidden" id="a_qid">
        <label class="form-label">Answer <span class="text-danger">*</span></label>
        <textarea id="a_text" class="form-control" rows="5" placeholder="Write a clear, complete answer…"></textarea>
        <div class="mt-2 d-flex align-items-center gap-1" style="font-size:.74rem;color:#64748b">
          <i class="bi bi-info-circle"></i>
          <span>Markdown is supported. You can mark the best answer after saving.</span>
        </div>
      </div>
      <div class="modal-footer">
        <button class="dcm-btn dcm-btn-ghost dcm-btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="dcm-btn dcm-btn-success dcm-btn-sm" id="saveAnswerBtn"><i class="bi bi-check2-circle me-1"></i>Save Answer</button>
      </div>
    </div>
  </div>
</div>

<style>
/* ══════════════════════════════════════════════════════
   CUSTOM TAB BAR
══════════════════════════════════════════════════════ */
.detail-tab-bar {
  display: flex; background: #fff;
  border-bottom: 2px solid #e2e8f0;
  overflow-x: auto; -webkit-overflow-scrolling: touch;
}
.detail-tab {
  display: inline-flex; align-items: center; gap: .4rem;
  padding: .85rem 1.4rem; border: none; background: none;
  font-size: .82rem; font-weight: 600; color: #64748b;
  border-bottom: 2.5px solid transparent; margin-bottom: -2px;
  cursor: pointer; transition: color .18s, border-color .18s, background .18s;
  white-space: nowrap; flex-shrink: 0;
}
.detail-tab:hover { color: #1e293b; background: #f8fafc; }
.detail-tab.active { color: #1a4fc4; border-bottom-color: #1a4fc4; }
.detail-tab.active[data-tab="instructor_qa"] { color: #b45309; border-bottom-color: #f59e0b; }
.dtb-icon-wrap { display:inline-flex; align-items:center; justify-content:center;
  width:22px; height:22px; border-radius:6px; font-size:.72rem; }
.dtb-icon-wrap.amber { background:#fef3c7; color:#d97706; }
.dtb-icon-wrap.blue  { background:#e0e7ff; color:#1a4fc4; }
.dtb-count { display:inline-flex; align-items:center; justify-content:center;
  min-width:18px; height:18px; border-radius:100px; font-size:.62rem; font-weight:700; padding:0 5px; }
.dtb-count.amber { background:#fef3c7; color:#92400e; }
.dtb-count.blue  { background:#e0e7ff; color:#3730a3; }

/* ── Chapter & Lesson (content tab) ── */
.chapter-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; margin-bottom:.8rem; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.04); }
.chapter-header { display:flex; align-items:center; gap:.5rem; padding:.8rem 1.1rem; background:linear-gradient(135deg,#f8fafc,#f1f5f9); cursor:pointer; user-select:none; transition:background .15s; }
.chapter-header:hover { background:#eef2ff; }
.chapter-drag-handle { cursor:grab; color:#94a3b8; font-size:1.1rem; flex-shrink:0; }
.chapter-drag-handle:active { cursor:grabbing; }
.chapter-toggle-icon { font-size:.85rem; transition:transform .22s; color:#94a3b8; }
.chapter-header.collapsed .chapter-toggle-icon { transform:rotate(-90deg); }
.chapter-title-text { font-weight:700; font-size:.88rem; flex:1; color:#1e293b; }
.chapter-actions { display:flex; gap:.3rem; flex-shrink:0; }
.lessons-list { padding:.6rem .9rem .8rem; }
.lesson-item { display:flex; align-items:center; gap:.6rem; padding:.55rem .85rem; border:1px solid #f0f4f8; border-radius:10px; margin-bottom:.4rem; background:#fafcff; transition:background .15s,box-shadow .15s; }
.lesson-item:hover { background:#f0f6ff; box-shadow:0 2px 8px rgba(26,79,196,.07); }
.lesson-drag-handle { cursor:grab; color:#cbd5e1; font-size:1rem; flex-shrink:0; }
.lesson-drag-handle:active { cursor:grabbing; }
.lesson-type-icon { font-size:.95rem; flex-shrink:0; }
.lesson-title { font-size:.82rem; font-weight:600; flex:1; color:#334155; }
.lesson-actions { display:flex; gap:.25rem; flex-shrink:0; }
.sortable-ghost { opacity:.3; background:#e0e7ff !important; border:2px dashed #6366f1 !important; }
.sortable-chosen { box-shadow:0 8px 24px rgba(26,79,196,.18) !important; }

/* ══════════════════════════════════════════════════════
   QA PANEL SHELL
══════════════════════════════════════════════════════ */
.qa-panel-wrapper { border:1px solid #e2e8f0; border-top:none; border-radius:0 0 16px 16px; background:#fff; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.05); }
.qa-panel-header { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:1.1rem 1.4rem; flex-wrap:wrap; }
.qa-panel-header.amber-theme { background:linear-gradient(135deg,#fffbeb,#fef9c3); border-bottom:1px solid #fde68a; }
.qa-panel-header.blue-theme  { background:linear-gradient(135deg,#eff6ff,#e0e7ff); border-bottom:1px solid #c7d2fe; }
.qph-left { display:flex; align-items:center; gap:.85rem; }
.qph-icon { width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; }
.amber-theme .qph-icon { background:#fef3c7; color:#d97706; }
.blue-theme  .qph-icon { background:#dbeafe; color:#1d4ed8; }
.qph-title { font-weight:700; font-size:.92rem; color:#1e293b; margin-bottom:.15rem; }
.qph-sub { font-size:.75rem; color:#64748b; }
.qph-btn { display:inline-flex; align-items:center; gap:.3rem; padding:.55rem 1.1rem; border:none; border-radius:10px; font-size:.8rem; font-weight:700; cursor:pointer; transition:filter .15s,box-shadow .15s; }
.blue-theme .qph-btn { background:linear-gradient(135deg,#1a4fc4,#6d28d9); color:#fff; box-shadow:0 4px 14px rgba(26,79,196,.25); }
.blue-theme .qph-btn:hover { filter:brightness(1.08); }
.qa-panel-body { padding:1.25rem 1.4rem; }
.qa-loading { display:flex; align-items:center; justify-content:center; gap:.85rem; padding:3rem 1rem; color:#94a3b8; font-size:.85rem; }

/* ══════════════════════════════════════════════════════
   STUDY NOTES (Instructor Q&A)
══════════════════════════════════════════════════════ */
.sn-chapter-card { border:1px solid #e2e8f0; border-radius:14px; margin-bottom:1.1rem; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.04); }
.sn-chapter-hdr { display:flex; align-items:center; gap:.7rem; padding:.85rem 1.1rem; background:linear-gradient(135deg,#fffbeb,#fef3c7); border-bottom:1px solid #fde68a; cursor:pointer; user-select:none; }
.sn-chapter-hdr:hover { background:linear-gradient(135deg,#fef3c7,#fde68a); }
.sn-chapter-num { width:28px; height:28px; border-radius:8px; background:#f59e0b; color:#fff; display:flex; align-items:center; justify-content:center; font-size:.72rem; font-weight:800; flex-shrink:0; }
.sn-chapter-name { font-weight:700; font-size:.88rem; color:#92400e; flex:1; }
.sn-chapter-meta { font-size:.7rem; color:#b45309; background:#fef9c3; border-radius:100px; padding:.15rem .6rem; font-weight:600; }
.sn-chapter-toggle { color:#b45309; font-size:.8rem; transition:transform .22s; }
.sn-chapter-hdr.collapsed .sn-chapter-toggle { transform:rotate(-90deg); }

.sn-chapter-body { padding:.75rem 1rem 1rem; }

.sn-lesson-wrap { margin-bottom:.9rem; }
.sn-lesson-hdr { display:flex; align-items:center; gap:.6rem; padding:.55rem .85rem; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; margin-bottom:.5rem; }
.sn-lesson-icon { width:24px; height:24px; border-radius:6px; background:#dbeafe; color:#1d4ed8; display:flex; align-items:center; justify-content:center; font-size:.7rem; flex-shrink:0; }
.sn-lesson-name { font-size:.8rem; font-weight:700; color:#334155; flex:1; }
.sn-lesson-count { font-size:.65rem; background:#e0e7ff; color:#3730a3; border-radius:100px; padding:.12rem .5rem; font-weight:700; }
.sn-add-btn { display:inline-flex; align-items:center; gap:.25rem; padding:.3rem .7rem; background:#f59e0b; color:#fff; border:none; border-radius:7px; font-size:.72rem; font-weight:700; cursor:pointer; transition:background .15s; }
.sn-add-btn:hover { background:#d97706; }

.sn-notes-container { padding-left:.5rem; }
.sn-note-card { display:flex; align-items:stretch; gap:0; border:1px solid #e2e8f0; border-radius:12px; margin-bottom:.55rem; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.04); transition:box-shadow .18s,transform .18s; }
.sn-note-card:hover { box-shadow:0 4px 16px rgba(245,158,11,.15); transform:translateY(-1px); }
.sn-note-drag-col { display:flex; align-items:center; justify-content:center; width:28px; background:#fffbeb; border-right:1px solid #fde68a; cursor:grab; color:#d97706; font-size:1rem; flex-shrink:0; }
.sn-note-drag-col:active { cursor:grabbing; }
.sn-note-content { flex:1; display:flex; flex-direction:column; }
.sn-note-q { display:flex; align-items:flex-start; gap:.6rem; padding:.65rem .9rem .5rem; background:#fffbeb; border-bottom:1px solid #fde68a; }
.sn-note-q-icon { width:20px; height:20px; border-radius:50%; background:#f59e0b; color:#fff; display:flex; align-items:center; justify-content:center; font-size:.65rem; font-weight:800; flex-shrink:0; margin-top:.1rem; }
.sn-note-q-text { font-size:.82rem; font-weight:700; color:#1e293b; line-height:1.45; flex:1; }
.sn-note-a { display:flex; align-items:flex-start; gap:.6rem; padding:.55rem .9rem .65rem; background:#fff; }
.sn-note-a-icon { width:20px; height:20px; border-radius:50%; background:#d1fae5; color:#059669; display:flex; align-items:center; justify-content:center; font-size:.65rem; font-weight:800; flex-shrink:0; margin-top:.1rem; }
.sn-note-a-text { font-size:.78rem; color:#475569; line-height:1.55; flex:1; }
.sn-note-footer { display:flex; align-items:center; gap:.4rem; padding:.4rem .9rem .5rem; background:#f8fafc; border-top:1px solid #f0f4f8; }
.sn-badge { font-size:.62rem; border-radius:100px; padding:.12rem .5rem; font-weight:700; }
.sn-badge.lang { background:#e0e7ff; color:#3730a3; }
.sn-badge.imp  { background:#fef3c7; color:#92400e; }
.sn-note-actions-col { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:.25rem; padding:.5rem .6rem; background:#f8fafc; border-left:1px solid #f0f4f8; flex-shrink:0; }
.sn-empty-lesson { color:#94a3b8; font-size:.77rem; padding:.4rem .5rem .6rem; font-style:italic; display:flex; align-items:center; gap:.35rem; }

/* ══════════════════════════════════════════════════════
   STUDENT DISCUSSIONS
══════════════════════════════════════════════════════ */
.disc-card { border:1px solid #e2e8f0; border-radius:14px; margin-bottom:.85rem; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.04); transition:box-shadow .18s,transform .18s; }
.disc-card:hover { box-shadow:0 6px 22px rgba(26,79,196,.1); transform:translateY(-1px); }
.disc-card.has-answers { border-left:3px solid #1a4fc4; }
.disc-card.no-answers  { border-left:3px solid #e2e8f0; }
.disc-header { display:flex; align-items:flex-start; gap:.85rem; padding:1rem 1.1rem .85rem; cursor:pointer; background:#fff; transition:background .15s; }
.disc-header:hover { background:#f8fafc; }
.disc-avatar { width:38px; height:38px; border-radius:50%; color:#fff; display:flex; align-items:center; justify-content:center; font-size:.8rem; font-weight:800; flex-shrink:0; }
.disc-body { flex:1; }
.disc-title { font-size:.88rem; font-weight:700; color:#1e293b; margin-bottom:.25rem; line-height:1.4; }
.disc-desc  { font-size:.78rem; color:#64748b; line-height:1.5; margin-bottom:.4rem; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.disc-meta  { display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; font-size:.71rem; color:#94a3b8; }
.disc-meta-author { font-weight:600; color:#475569; }
.disc-answer-count { display:inline-flex; align-items:center; gap:.25rem; background:#eff6ff; color:#1d4ed8; border-radius:100px; padding:.2rem .65rem; font-size:.7rem; font-weight:700; }
.disc-answer-count.zero { background:#f1f5f9; color:#94a3b8; }
.disc-drag { color:#cbd5e1; font-size:1.1rem; flex-shrink:0; cursor:grab; margin-top:.2rem; }
.disc-drag:active { cursor:grabbing; }
.disc-actions { display:flex; gap:.3rem; flex-shrink:0; align-items:flex-start; margin-top:.1rem; }
.disc-chevron { color:#94a3b8; font-size:.85rem; flex-shrink:0; transition:transform .22s; margin-top:.3rem; }
.disc-chevron.open { transform:rotate(180deg); }

.disc-replies { border-top:1px solid #f0f4f8; background:#f8fafc; }
.disc-replies-inner { padding:.85rem 1.1rem; }
.disc-reply { display:flex; align-items:flex-start; gap:.75rem; padding:.7rem 0; border-bottom:1px solid #f0f4f8; }
.disc-reply:last-child { border-bottom:none; }
.reply-avatar { width:32px; height:32px; border-radius:50%; color:#fff; display:flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:800; flex-shrink:0; }
.reply-body { flex:1; }
.reply-text { font-size:.8rem; color:#334155; line-height:1.55; margin-bottom:.3rem; }
.reply-meta { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; font-size:.7rem; color:#94a3b8; }
.reply-author { font-weight:600; color:#475569; }
.correct-banner { display:inline-flex; align-items:center; gap:.3rem; background:#d1fae5; color:#065f46; border-radius:6px; padding:.2rem .6rem; font-size:.7rem; font-weight:700; margin-bottom:.4rem; }
.reply-actions { display:flex; gap:.2rem; flex-shrink:0; align-items:flex-start; }
.disc-add-reply-bar { padding:.65rem 1.1rem; border-top:1px solid #e2e8f0; background:#fff; display:flex; gap:.5rem; align-items:center; }
.disc-reply-input-hint { flex:1; padding:.45rem .85rem; border:1.5px solid #e2e8f0; border-radius:9px; font-size:.79rem; color:#94a3b8; background:#f8fafc; cursor:pointer; transition:border-color .15s; }
.disc-reply-input-hint:hover { border-color:#1a4fc4; color:#1a4fc4; }
.disc-empty { text-align:center; padding:3rem 1rem; color:#94a3b8; }
.disc-empty i { font-size:2.5rem; display:block; margin-bottom:.5rem; color:#e2e8f0; }
.disc-empty-title { font-size:.88rem; font-weight:600; color:#64748b; margin-bottom:.25rem; }
.disc-empty-sub { font-size:.76rem; }

/* ══════════════════════════════════════════════════════
   UNIFIED BUTTON SYSTEM
══════════════════════════════════════════════════════ */
.dcm-btn {
  display:inline-flex; align-items:center; gap:.35rem;
  padding:.58rem 1.2rem; border:none; border-radius:11px;
  font-size:.8rem; font-weight:700; cursor:pointer; text-decoration:none;
  white-space:nowrap; transition:filter .15s,box-shadow .15s,transform .1s;
  font-family:inherit;
}
.dcm-btn:active { transform:scale(.96); }

/* Primary — brand gradient (blue → purple) */
.dcm-btn-primary {
  background:linear-gradient(135deg,#1a4fc4 0%,#6d28d9 100%);
  color:#fff; box-shadow:0 4px 14px rgba(26,79,196,.28);
}
.dcm-btn-primary:hover { filter:brightness(1.09); box-shadow:0 6px 22px rgba(26,79,196,.38); color:#fff; }

/* Amber — instructor / notes */
.dcm-btn-amber {
  background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);
  color:#fff; box-shadow:0 4px 14px rgba(245,158,11,.28);
}
.dcm-btn-amber:hover { filter:brightness(1.08); color:#fff; }

/* Success */
.dcm-btn-success {
  background:linear-gradient(135deg,#059669 0%,#0d9488 100%);
  color:#fff; box-shadow:0 4px 12px rgba(5,150,105,.25);
}
.dcm-btn-success:hover { filter:brightness(1.09); color:#fff; }

/* Danger */
.dcm-btn-danger {
  background:linear-gradient(135deg,#dc2626 0%,#9f1239 100%);
  color:#fff; box-shadow:0 4px 12px rgba(220,38,38,.22);
}
.dcm-btn-danger:hover { filter:brightness(1.09); color:#fff; }

/* Ghost — secondary / cancel */
.dcm-btn-ghost {
  background:#f1f5f9; color:#475569;
  border:1.5px solid #e2e8f0; box-shadow:none;
}
.dcm-btn-ghost:hover { background:#e2e8f0; color:#1e293b; border-color:#cbd5e1; }

/* Size modifiers */
.dcm-btn-sm { padding:.4rem .9rem; font-size:.76rem; border-radius:9px; }
.dcm-btn-xs { padding:.25rem .6rem; font-size:.7rem; border-radius:7px; }

/* Icon-only circular action buttons */
.icn-btn {
  display:inline-flex; align-items:center; justify-content:center;
  width:30px; height:30px; border-radius:8px; border:none; cursor:pointer;
  font-size:.78rem; transition:background .15s,color .15s,transform .1s;
  flex-shrink:0;
}
.icn-btn:active { transform:scale(.88); }
.icn-btn-view    { background:#eff6ff; color:#1d4ed8; }
.icn-btn-view:hover    { background:#1d4ed8; color:#fff; }
.icn-btn-edit    { background:#f0f9ff; color:#0369a1; }
.icn-btn-edit:hover    { background:#0369a1; color:#fff; }
.icn-btn-rename  { background:#f5f3ff; color:#7c3aed; }
.icn-btn-rename:hover  { background:#7c3aed; color:#fff; }
.icn-btn-del     { background:#fff1f2; color:#e11d48; }
.icn-btn-del:hover     { background:#e11d48; color:#fff; }
.icn-btn-ok      { background:#f0fdf4; color:#16a34a; }
.icn-btn-ok:hover      { background:#16a34a; color:#fff; }
.icn-btn-ok.active { background:#16a34a; color:#fff; }

/* Lesson badges */
.lsn-badge { font-size:.63rem; font-weight:700; border-radius:100px; padding:.12rem .5rem; }
.lsn-badge.hidden { background:#fee2e2; color:#b91c1c; }
.lsn-badge.free   { background:#d1fae5; color:#065f46; }
.lsn-badge.count  { background:#e0e7ff; color:#3730a3; }

/* ── Lesson Viewer Modal ── */
.lv-header { background:linear-gradient(135deg,#0f172a 0%,#1a2744 100%); padding:1.1rem 1.4rem; display:flex; align-items:center; gap:.9rem; }
.lv-type-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.25rem; flex-shrink:0; }
.lv-content-wrap { background:#0f172a; min-height:420px; display:flex; align-items:center; justify-content:center; position:relative; overflow:hidden; }
.lv-info-strip { display:flex; align-items:center; gap:.75rem; flex-wrap:wrap; padding:.85rem 1.4rem; background:#f8fafc; border-top:1px solid #e2e8f0; }
.lv-info-badge { display:inline-flex; align-items:center; gap:.3rem; border-radius:8px; padding:.28rem .75rem; font-size:.72rem; font-weight:700; }
</style>

<script>
const AJAX = '../data_files/ajax/ajax_admin_courses.php';
const COURSE_ID = <?= $cid ?>;
const INSTRUCTOR_ID = '<?= htmlspecialchars($course['instructor_id'], ENT_QUOTES) ?>';

/* ── Tab switching ── */
const TAB_PANELS = {
  content:       '#tabContent',
  instructor_qa: '#tabInstructorQa',
  student_qa:    '#tabStudentQa'
};
$('[data-tab]').on('click', function(){
  $('[data-tab]').removeClass('active');
  $(this).addClass('active');
  let tab = $(this).data('tab');
  Object.values(TAB_PANELS).forEach(p => $(p).hide());
  $(TAB_PANELS[tab]).show();
  if (tab === 'instructor_qa' && !snLoaded)  loadStudyNotes();
  if (tab === 'student_qa'   && !sqaLoaded) loadQaTab();
});

/* ══════════════════════════════════════════════════════
   CHAPTERS & LESSONS
══════════════════════════════════════════════════════ */
let chapterSortable;
let lessonSortables = {};

function contentTypeIcon(t) {
  const map = {video:'bi-play-circle-fill text-danger', audio:'bi-music-note-beamed text-warning',
    pdf:'bi-file-pdf-fill text-danger', ppt:'bi-file-earmark-slides-fill text-warning',
    live:'bi-broadcast-fill text-success'};
  return `<i class="bi ${map[t]||'bi-file-earmark text-muted'} lesson-type-icon"></i>`;
}

function renderChapters(chapters) {
  let html = '';
  chapters.forEach((ch, ci) => {
    let lessonsHtml = '';
    (ch.lessons || []).forEach(l => {
      lessonsHtml += `
        <div class="lesson-item" data-id="${l.id}" data-chapter="${ch.id}">
          <i class="bi bi-grip-vertical lesson-drag-handle"></i>
          ${contentTypeIcon(l.content_type)}
          <span class="lesson-title">${escHtml(l.lesson_title)}</span>
          ${l.status==='inactive' ? '<span class="lsn-badge hidden ms-1">Hidden</span>' : ''}
          ${parseInt(l.isFreePreviewLesson)?'<span class="lsn-badge free ms-1">Free</span>':''}
          <div class="lesson-actions">
            <button class="icn-btn icn-btn-view btn-view-lesson" data-id="${l.id}" data-title="${escHtml(l.lesson_title)}" data-type="${escHtml(l.content_type||'')}" data-path="${escHtml(l.file_path||'')}" data-desc="${escHtml(l.description||'')}" title="Preview"><i class="bi bi-eye-fill"></i></button>
            <button class="icn-btn icn-btn-edit btn-edit-lesson" data-id="${l.id}" title="Edit"><i class="bi bi-pencil-fill"></i></button>
            <button class="icn-btn icn-btn-del btn-del-lesson" data-id="${l.id}" data-title="${escHtml(l.lesson_title)}" title="Delete"><i class="bi bi-trash-fill"></i></button>
          </div>
        </div>`;
    });

    html += `
      <div class="chapter-card" data-id="${ch.id}">
        <div class="chapter-header" data-bs-toggle="chapter-collapse" data-target="#chLessons_${ch.id}">
          <i class="bi bi-grip-vertical chapter-drag-handle"></i>
          <i class="bi bi-chevron-down chapter-toggle-icon"></i>
          <span class="chapter-title-text">${escHtml(ch.chapter_title)}</span>
          <span class="badge bg-primary bg-opacity-10 text-primary ms-auto me-2">${(ch.lessons||[]).length} lessons</span>
          <div class="chapter-actions" onclick="event.stopPropagation()">
            <button class="icn-btn icn-btn-rename btn-rename-ch" data-id="${ch.id}" data-title="${escHtml(ch.chapter_title)}" title="Rename"><i class="bi bi-pencil-fill"></i></button>
            <button class="icn-btn icn-btn-del btn-del-ch" data-id="${ch.id}" data-title="${escHtml(ch.chapter_title)}" title="Delete"><i class="bi bi-trash-fill"></i></button>
          </div>
        </div>
        <div class="lessons-list" id="chLessons_${ch.id}">
          <div class="lessons-sortable" id="lessonsSortable_${ch.id}" data-chapter="${ch.id}">
            ${lessonsHtml || '<div class="text-muted small ps-1 py-1">No lessons yet</div>'}
          </div>
        </div>
      </div>`;
  });

  $('#chaptersList').html(html || '<div class="text-center text-muted py-5"><i class="bi bi-inbox fs-2 d-block mb-2"></i>No chapters yet. Add one!</div>');
  $('#chaptersSpinner').hide();
  $('#chaptersList').show();

  // Init chapter Sortable
  chapterSortable = Sortable.create(document.getElementById('chaptersList'), {
    handle: '.chapter-drag-handle',
    animation: 150,
    ghostClass: 'sortable-ghost',
    chosenClass: 'sortable-chosen',
    onEnd: saveChapterOrder
  });

  // Init lesson Sortables per chapter
  chapters.forEach(ch => {
    let el = document.getElementById('lessonsSortable_' + ch.id);
    if (!el) return;
    lessonSortables[ch.id] = Sortable.create(el, {
      group: 'lessons',           // same group = cross-chapter drop
      handle: '.lesson-drag-handle',
      animation: 150,
      ghostClass: 'sortable-ghost',
      chosenClass: 'sortable-chosen',
      onEnd: saveLessonOrder
    });
  });
}

function loadChapters() {
  $('#chaptersSpinner').show(); $('#chaptersList').hide();
  $.post(AJAX, {action:'get_chapters', course_id:COURSE_ID}, function(r){
    if (r.status === 'success') renderChapters(r.data);
  }, 'json');
}

function saveChapterOrder() {
  let ids = [];
  $('#chaptersList .chapter-card').each(function(){ ids.push($(this).data('id')); });
  $.ajax({url:AJAX, method:'POST', contentType:'application/json',
    data: JSON.stringify({action:'reorder_chapters', ids}),
    success: () => showToast('Chapter order saved', 'success')
  });
}

function saveLessonOrder() {
  let items = [];
  $('#chaptersList .lessons-sortable').each(function(){
    let chapterId = $(this).data('chapter');
    $(this).children('.lesson-item').each(function(){
      items.push({id: $(this).data('id'), chapter_id: chapterId});
    });
  });
  $.ajax({url:AJAX, method:'POST', contentType:'application/json',
    data: JSON.stringify({action:'reorder_lessons', items}),
    success: () => showToast('Lesson order saved', 'success')
  });
}

/* ── Chapter collapse toggle ── */
$(document).on('click', '.chapter-header', function(e){
  if ($(e.target).closest('.chapter-actions').length || $(e.target).hasClass('chapter-drag-handle')) return;
  let target = $(this).data('target');
  $(target).toggle();
  $(this).toggleClass('collapsed');
});

/* ── Add chapter ── */
$('#btnAddChapter').on('click', function(){
  Swal.fire({
    title: 'New Chapter', input: 'text', inputPlaceholder: 'Chapter title…',
    showCancelButton: true, confirmButtonText: 'Add',
    inputValidator: v => !v.trim() && 'Please enter a title'
  }).then(r => {
    if (!r.isConfirmed) return;
    $.post(AJAX, {action:'add_chapter', course_id:COURSE_ID, instructor_id:INSTRUCTOR_ID, title:r.value}, function(res){
      if (res.status === 'success') { loadChapters(); showToast('Chapter added', 'success'); }
    }, 'json');
  });
});

/* ── Rename chapter ── */
$(document).on('click', '.btn-rename-ch', function(){
  let id = $(this).data('id'), old = $(this).data('title');
  Swal.fire({
    title:'Rename Chapter', input:'text', inputValue: old,
    showCancelButton:true, confirmButtonText:'Save',
    inputValidator: v => !v.trim() && 'Title required'
  }).then(r => {
    if (!r.isConfirmed) return;
    $.post(AJAX, {action:'update_chapter', id, title:r.value}, function(res){
      if (res.status === 'success') { loadChapters(); showToast('Renamed', 'success'); }
    }, 'json');
  });
});

/* ── Delete chapter ── */
$(document).on('click', '.btn-del-ch', function(){
  let id = $(this).data('id'), title = $(this).data('title');
  Swal.fire({
    icon:'warning', title:'Delete Chapter?',
    html:`<b>${title}</b><br><small class="text-danger">All lessons inside will also be deleted.</small>`,
    showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Delete'
  }).then(r => {
    if (!r.isConfirmed) return;
    $.post(AJAX, {action:'delete_chapter', id}, function(res){
      if (res.status === 'success') { loadChapters(); showToast('Chapter deleted', 'success'); }
    }, 'json');
  });
});

/* ── Edit lesson ── */
$(document).on('click', '.btn-edit-lesson', function(){
  let id = $(this).data('id');
  $.post(AJAX, {action:'get_lesson', id}, function(r){
    if (r.status !== 'success') return;
    let l = r.data;
    $('#el_id').val(l.id);
    $('#el_title').val(l.lesson_title);
    $('#el_desc').val(l.description);
    $('#el_status').val(l.status);
    $('#el_free').prop('checked', parseInt(l.isFreePreviewLesson) === 1);
    new bootstrap.Modal('#editLessonModal').show();
  }, 'json');
});

$('#saveLessonBtn').on('click', function(){
  $(this).prop('disabled',true).html('<span class="spinner-border spinner-border-sm"></span>');
  $.post(AJAX, {
    action:'update_lesson',
    id: $('#el_id').val(),
    lesson_title: $('#el_title').val(),
    description: $('#el_desc').val(),
    status: $('#el_status').val(),
    isFreePreviewLesson: $('#el_free').is(':checked') ? 1 : 0
  }, function(r){
    $('#saveLessonBtn').prop('disabled',false).html('<i class="bi bi-check2-circle me-1"></i>Save Changes');
    if (r.status === 'success') {
      bootstrap.Modal.getInstance('#editLessonModal').hide();
      loadChapters();
      showToast('Lesson updated', 'success');
    }
  }, 'json');
});

/* ── Delete lesson ── */
$(document).on('click', '.btn-del-lesson', function(){
  let id = $(this).data('id'), title = $(this).data('title');
  Swal.fire({
    icon:'warning', title:'Delete Lesson?', text:title,
    showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Delete'
  }).then(r => {
    if (!r.isConfirmed) return;
    $.post(AJAX, {action:'delete_lesson', id}, function(res){
      if (res.status === 'success') { loadChapters(); showToast('Lesson deleted', 'success'); }
    }, 'json');
  });
});

/* ── Edit course ── */
$('#btnEditCourse').on('click', () => new bootstrap.Modal('#editCourseModal').show());
$('#saveCourseBtn').on('click', function(){
  $(this).prop('disabled',true).html('<span class="spinner-border spinner-border-sm"></span>');
  $.post(AJAX, {
    action:'update_course', id:COURSE_ID,
    title:$('#ec_title').val(), description:$('#ec_desc').val(),
    status:$('#ec_status').val(), is_approved:$('#ec_approval').val(), price:$('#ec_price').val()
  }, function(r){
    $('#saveCourseBtn').prop('disabled',false).html('<i class="bi bi-check2-circle me-1"></i>Save Changes');
    if (r.status === 'success') {
      bootstrap.Modal.getInstance('#editCourseModal').hide();
      showToast('Course updated', 'success');
    }
  }, 'json');
});

/* ══════════════════════════════════════════════════════
   INSTRUCTOR STUDY NOTES  (study_notes table)
══════════════════════════════════════════════════════ */
let snLoaded = false;
let snSortables = {};   // keyed by lesson_id

function loadStudyNotes() {
  snLoaded = true;
  $('#snSpinner').show(); $('#snTree').hide();
  $.post(AJAX, {action:'list_study_notes', course_id:COURSE_ID}, function(r){
    $('#snSpinner').hide(); $('#snTree').show();
    if (r.status !== 'success') return;
    let total = r.data.reduce((s,ch) => s + ch.lessons.reduce((s2,l) => s2 + l.notes.length, 0), 0);
    $('#iqaBadgeCount').text(total||'').toggle(total > 0);
    renderStudyNotes(r.data);
  }, 'json');
}

function renderStudyNotes(chapters) {
  if (!chapters.length) {
    $('#snTree').html(`<div class="disc-empty"><i class="bi bi-collection"></i><div class="disc-empty-title">No chapters found</div><div class="disc-empty-sub">Add chapters and lessons to this course first.</div></div>`);
    return;
  }
  let html = '';
  chapters.forEach((ch, ci) => {
    let totalNotes = ch.lessons.reduce((s, l) => s + l.notes.length, 0);
    html += `
      <div class="sn-chapter-card">
        <div class="sn-chapter-hdr" onclick="toggleSnChapter(this, 'snChBody_${ch.id}')">
          <div class="sn-chapter-num">${ci + 1}</div>
          <span class="sn-chapter-name">${escHtml(ch.chapter_title)}</span>
          <span class="sn-chapter-meta">${ch.lessons.length} lesson${ch.lessons.length!=1?'s':''} · ${totalNotes} note${totalNotes!=1?'s':''}</span>
          <i class="bi bi-chevron-down sn-chapter-toggle"></i>
        </div>
        <div class="sn-chapter-body" id="snChBody_${ch.id}">`;

    if (!ch.lessons.length) {
      html += `<div class="sn-empty-lesson"><i class="bi bi-info-circle-fill"></i>No lessons in this chapter yet.</div>`;
    }

    ch.lessons.forEach((lesson, li) => {
      let noteCount = lesson.notes.length;
      html += `
          <div class="sn-lesson-wrap">
            <div class="sn-lesson-hdr">
              <div class="sn-lesson-icon"><i class="bi bi-play-fill"></i></div>
              <span class="sn-lesson-name">${escHtml(lesson.lesson_title)}</span>
              <span class="sn-lesson-count">${noteCount} note${noteCount!=1?'s':''}</span>
              <button class="sn-add-btn btn-add-sn" data-chapter="${ch.id}" data-lesson="${lesson.id}" data-lesson-title="${escHtml(lesson.lesson_title)}">
                <i class="bi bi-plus-circle-fill"></i> Add Q&amp;A
              </button>
            </div>
            <div class="sn-notes-container" id="snList_${lesson.id}">`;

      if (!noteCount) {
        html += `<div class="sn-empty-lesson"><i class="bi bi-lightbulb"></i>No study notes for this lesson yet. Click "Add Q&amp;A" to create the first one.</div>`;
      }

      lesson.notes.forEach((n, ni) => {
        html += `
              <div class="sn-note-card" data-snid="${n.id}">
                <div class="sn-note-drag-col"><i class="bi bi-grip-vertical"></i></div>
                <div class="sn-note-content">
                  <div class="sn-note-q">
                    <div class="sn-note-q-icon">Q</div>
                    <div class="sn-note-q-text">${escHtml(n.question)}</div>
                  </div>
                  <div class="sn-note-a">
                    <div class="sn-note-a-icon">A</div>
                    <div class="sn-note-a-text">${escHtml(n.answer)}</div>
                  </div>
                  <div class="sn-note-footer">
                    <span class="sn-badge lang"><i class="bi bi-translate me-1"></i>${escHtml(n.language||'EN')}</span>
                    ${parseInt(n.is_important) ? '<span class="sn-badge imp"><i class="bi bi-star-fill me-1"></i>Important</span>' : ''}
                    <span class="ms-auto" style="font-size:.65rem;color:#94a3b8">#${ni+1}</span>
                  </div>
                </div>
                <div class="sn-note-actions-col">
                  <button class="icn-btn icn-btn-edit btn-edit-sn"
                    data-id="${n.id}" data-chapter="${ch.id}" data-lesson="${lesson.id}"
                    data-lesson-title="${escHtml(lesson.lesson_title)}"
                    data-question="${escHtml(n.question)}" data-answer="${escHtml(n.answer)}"
                    data-language="${n.language||'EN'}" data-important="${n.is_important||0}"
                    title="Edit"><i class="bi bi-pencil-fill"></i></button>
                  <button class="icn-btn icn-btn-del btn-del-sn" data-id="${n.id}" title="Delete"><i class="bi bi-trash-fill"></i></button>
                </div>
              </div>`;
      });

      html += `</div></div>`;  // sn-notes-container + sn-lesson-wrap
    });

    html += `</div></div>`;  // sn-chapter-body + sn-chapter-card
  });

  $('#snTree').html(html);

  // Init SortableJS per lesson after render
  chapters.forEach(ch => {
    ch.lessons.forEach(lesson => {
      setTimeout(() => {
        let el = document.getElementById('snList_' + lesson.id);
        if (!el || !el.querySelector('.sn-note-card')) return;
        snSortables[lesson.id] = Sortable.create(el, {
          handle: '.sn-note-drag-col',
          animation: 160,
          ghostClass: 'sortable-ghost',
          chosenClass: 'sortable-chosen',
          onEnd: () => {
            let ids = [];
            $(`#snList_${lesson.id} .sn-note-card`).each(function(){ ids.push($(this).data('snid')); });
            $.ajax({url:AJAX, method:'POST', contentType:'application/json',
              data: JSON.stringify({action:'reorder_study_notes', ids}),
              success: () => showToast('Order saved', 'success')
            });
          }
        });
      }, 60);
    });
  });
}

function toggleSnChapter(hdr, bodyId) {
  let $hdr = $(hdr), $body = $('#' + bodyId);
  $hdr.toggleClass('collapsed');
  $body.slideToggle(200);
}

/* ── Open study note modal ── */
function openSnModal(chapterId, lessonId, lessonTitle, id, question, answer, language, isImportant) {
  $('#sn_id').val(id||'');
  $('#sn_chapter_id').val(chapterId);
  $('#sn_lesson_id').val(lessonId);
  $('#sn_lesson_label').text(lessonTitle);
  $('#sn_question').val(question||'');
  $('#sn_answer').val(answer||'');
  $('#sn_language').val(language||'EN');
  $('#sn_important').prop('checked', parseInt(isImportant||0) === 1);
  $('#snModalTitle').text((id ? 'Edit' : 'Add') + ' Study Note');
  new bootstrap.Modal('#studyNoteModal').show();
}

$(document).on('click', '.btn-add-sn', function(){
  openSnModal($(this).data('chapter'), $(this).data('lesson'), $(this).data('lesson-title'));
});

$(document).on('click', '.btn-edit-sn', function(){
  let d = $(this).data();
  openSnModal(d.chapter, d.lesson, d['lesson-title'] || d.lessonTitle, d.id, d.question, d.answer, d.language, d.important);
});

$('#saveStudyNoteBtn').on('click', function(){
  let id = $('#sn_id').val();
  let payload = {
    action: id ? 'update_study_note' : 'add_study_note',
    course_id:    COURSE_ID,
    chapter_id:   $('#sn_chapter_id').val(),
    lesson_id:    $('#sn_lesson_id').val(),
    question:     $('#sn_question').val().trim(),
    answer:       $('#sn_answer').val().trim(),
    language:     $('#sn_language').val(),
    is_important: $('#sn_important').is(':checked') ? 1 : 0
  };
  if (id) payload.id = id;
  if (!payload.question || !payload.answer) {
    showToast('Question and answer are required', 'error'); return;
  }
  $(this).prop('disabled',true).html('<span class="spinner-border spinner-border-sm"></span>');
  $.post(AJAX, payload, function(r){
    $('#saveStudyNoteBtn').prop('disabled',false).html('<i class="bi bi-check2 me-1"></i>Save Note');
    if (r.status === 'success') {
      bootstrap.Modal.getInstance('#studyNoteModal').hide();
      snLoaded = false; loadStudyNotes();
      showToast('Study note saved', 'success');
    }
  }, 'json');
});

$(document).on('click', '.btn-del-sn', function(){
  let id = $(this).data('id');
  Swal.fire({
    icon:'warning', title:'Delete Study Note?', text:'This Q&A pair will be permanently removed.',
    showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Delete'
  }).then(r => {
    if (!r.isConfirmed) return;
    $.post(AJAX, {action:'delete_study_note', id}, function(res){
      if (res.status === 'success') { snLoaded = false; loadStudyNotes(); showToast('Deleted', 'success'); }
    }, 'json');
  });
});

/* ══════════════════════════════════════════════════════
   STUDENT DISCUSSIONS  (tbl_course_discussions)
══════════════════════════════════════════════════════ */
let sqaLoaded = false;
let sqaSortable;

function loadQaTab() {
  sqaLoaded = true;
  $('#sqaSpinner').show(); $('#sqaList').hide();
  $.post(AJAX, {action:'list_questions', course_id:COURSE_ID, type:'student'}, function(r){
    $('#sqaSpinner').hide(); $('#sqaList').show();
    if (r.status !== 'success') return;
    let cnt = r.data.length;
    $('#sqaBadgeCount').text(cnt||'').toggle(cnt > 0);
    renderSqa(r.data);
  }, 'json');
}

const AVATAR_COLORS = ['#1a4fc4','#7c3aed','#059669','#d97706','#0891b2','#be185d','#dc2626','#0d9488'];
function avatarColor(name) {
  let h = 0;
  for (let i = 0; i < (name||'').length; i++) h = ((h << 5) - h) + name.charCodeAt(i);
  return AVATAR_COLORS[Math.abs(h) % AVATAR_COLORS.length];
}

function renderSqa(questions) {
  if (!questions.length) {
    $('#sqaList').html(`
      <div class="disc-empty">
        <i class="bi bi-chat-left-dots"></i>
        <div class="disc-empty-title">No discussions yet</div>
        <div class="disc-empty-sub">Student questions will appear here once enrolled students start asking.</div>
      </div>`);
    return;
  }

  let html = '<div id="sqaQuestionsSortable">';
  questions.forEach(q => {
    let name  = ((q.first_name||'') + ' ' + (q.last_name||'')).trim() || 'Unknown';
    let init  = (name.split(' ').map(w=>w[0]).join('').toUpperCase()).slice(0,2) || '?';
    let aclr  = avatarColor(name);
    let cnt   = parseInt(q.answer_count) || 0;
    let hasAns = cnt > 0;

    html += `
      <div class="disc-card ${hasAns ? 'has-answers' : 'no-answers'}" data-qid="${q.id}">
        <div class="disc-header" onclick="toggleAnswers(${q.id}, this)">
          <i class="bi bi-grip-vertical disc-drag" onclick="event.stopPropagation()"></i>
          <div class="disc-avatar" style="background:${aclr}">${init}</div>
          <div class="disc-body">
            <div class="disc-title">${escHtml(q.title)}</div>
            ${q.description ? `<div class="disc-desc">${escHtml(q.description)}</div>` : ''}
            <div class="disc-meta">
              <span class="disc-meta-author"><i class="bi bi-person-fill me-1"></i>${escHtml(name)}</span>
              <span>·</span>
              <span>${timeAgo(q.created_at)}</span>
              <span>·</span>
              <span class="disc-answer-count ${hasAns?'':'zero'}">
                <i class="bi bi-chat-fill"></i> ${cnt} answer${cnt!==1?'s':''}
              </span>
            </div>
          </div>
          <div class="disc-actions" onclick="event.stopPropagation()">
            <button class="icn-btn icn-btn-edit btn-edit-q"
              data-id="${q.id}" data-title="${escHtml(q.title)}" data-desc="${escHtml(q.description||'')}"
              title="Edit"><i class="bi bi-pencil-fill"></i></button>
            <button class="icn-btn icn-btn-del btn-del-q" data-id="${q.id}" title="Delete"><i class="bi bi-trash-fill"></i></button>
          </div>
          <i class="bi bi-chevron-down disc-chevron" id="discChevron_${q.id}"></i>
        </div>
        <div class="disc-replies" id="discReplies_${q.id}" style="display:none">
          <div class="disc-replies-inner" id="answersInner_${q.id}">
            <div class="qa-loading"><div class="spinner-border spinner-border-sm text-primary"></div><span>Loading answers…</span></div>
          </div>
          <div class="disc-add-reply-bar">
            <div class="reply-avatar" style="background:linear-gradient(135deg,#1a4fc4,#6d28d9)">
              <i class="bi bi-person-fill" style="font-size:.75rem;color:#fff"></i>
            </div>
            <div class="disc-reply-input-hint btn-add-answer" data-qid="${q.id}">
              <i class="bi bi-pencil me-1"></i>Write an answer…
            </div>
          </div>
        </div>
      </div>`;
  });
  html += '</div>';
  $('#sqaList').html(html);

  sqaSortable = Sortable.create(document.getElementById('sqaQuestionsSortable'), {
    handle: '.disc-drag', animation: 160,
    ghostClass: 'sortable-ghost', chosenClass: 'sortable-chosen',
    onEnd: () => {
      let ids = [];
      $('#sqaQuestionsSortable .disc-card').each(function(){ ids.push($(this).data('qid')); });
      $.ajax({ url:AJAX, method:'POST', contentType:'application/json',
        data: JSON.stringify({action:'reorder_questions', ids}),
        success: () => showToast('Order saved', 'success')
      });
    }
  });
}

function toggleAnswers(qid, header) {
  let $replies = $('#discReplies_' + qid);
  let $chevron = $('#discChevron_' + qid);
  if ($replies.is(':visible')) {
    $replies.slideUp(180);
    $chevron.removeClass('open');
  } else {
    $replies.slideDown(180);
    $chevron.addClass('open');
    loadAnswers(qid);
  }
}

function loadAnswers(qid) {
  $('#answersInner_' + qid).html('<div class="qa-loading"><div class="spinner-border spinner-border-sm text-primary"></div><span>Loading answers…</span></div>');
  $.post(AJAX, {action:'list_answers', question_id:qid}, function(r){
    if (r.status !== 'success') return;
    if (!r.data.length) {
      $('#answersInner_' + qid).html('<div class="disc-empty" style="padding:1.5rem"><i class="bi bi-chat-square-dots" style="font-size:1.5rem;color:#e2e8f0;display:block;margin-bottom:.4rem"></i><div class="disc-empty-sub">No answers yet — be the first to respond.</div></div>');
      return;
    }
    let html = '';
    r.data.forEach(a => {
      let name  = ((a.first_name||'') + ' ' + (a.last_name||'')).trim() || 'Unknown';
      let init  = (name.split(' ').map(w=>w[0]).join('').toUpperCase()).slice(0,2) || '?';
      let aclr  = avatarColor(name);
      html += `
        <div class="disc-reply" data-aid="${a.id}">
          <div class="reply-avatar" style="background:${aclr}">${init}</div>
          <div class="reply-body">
            ${a.is_correct ? '<div class="correct-banner"><i class="bi bi-patch-check-fill"></i>Accepted Answer</div>' : ''}
            <div class="reply-text">${escHtml(a.answer)}</div>
            <div class="reply-meta">
              <span class="reply-author">${escHtml(name)}</span>
              <span>·</span><span>${timeAgo(a.created_at)}</span>
            </div>
          </div>
          <div class="reply-actions">
            <button class="icn-btn icn-btn-ok btn-mark-correct ${a.is_correct?'active':''}"
              data-aid="${a.id}" data-qid="${qid}" data-is="${a.is_correct?0:1}"
              title="${a.is_correct?'Unmark':'Mark as accepted'}">
              <i class="bi bi-patch-check-fill"></i>
            </button>
            <button class="icn-btn icn-btn-edit btn-edit-a" data-id="${a.id}" data-qid="${qid}" data-text="${escHtml(a.answer)}" title="Edit"><i class="bi bi-pencil-fill"></i></button>
            <button class="icn-btn icn-btn-del btn-del-a" data-id="${a.id}" data-qid="${qid}" title="Delete"><i class="bi bi-trash-fill"></i></button>
          </div>
        </div>`;
    });
    $('#answersInner_' + qid).html(html);
  }, 'json');
}

/* ── Student Q&A modal ── */
$('#btnAddStudentQ').on('click', function(){
  $('#q_id').val(''); $('#q_title').val(''); $('#q_desc').val(''); $('#q_type').val('student');
  $('#questionModalTitle').text('Add Student Question');
  new bootstrap.Modal('#questionModal').show();
});

$(document).on('click', '.btn-edit-q', function(){
  $('#q_id').val($(this).data('id'));
  $('#q_title').val($(this).data('title'));
  $('#q_desc').val($(this).data('desc'));
  $('#q_type').val('student');
  $('#questionModalTitle').text('Edit Question');
  new bootstrap.Modal('#questionModal').show();
});

$('#saveQuestionBtn').on('click', function(){
  let id = $('#q_id').val();
  let payload = {action: id ? 'update_question' : 'add_question',
    title: $('#q_title').val(), description: $('#q_desc').val(), type:'student'};
  if (id) payload.id = id; else payload.course_id = COURSE_ID;
  $(this).prop('disabled',true).html('<span class="spinner-border spinner-border-sm"></span>');
  $.post(AJAX, payload, function(r){
    $('#saveQuestionBtn').prop('disabled',false).html('<i class="bi bi-check2-circle me-1"></i>Save Question');
    if (r.status === 'success') {
      bootstrap.Modal.getInstance('#questionModal').hide();
      sqaLoaded = false; loadQaTab();
      showToast('Saved', 'success');
    }
  }, 'json');
});

$(document).on('click', '.btn-del-q', function(){
  let id = $(this).data('id');
  Swal.fire({
    icon:'warning', title:'Delete Question?', text:'All answers will also be deleted.',
    showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Delete'
  }).then(r => {
    if (!r.isConfirmed) return;
    $.post(AJAX, {action:'delete_question', id}, function(res){
      if (res.status === 'success') { sqaLoaded = false; loadQaTab(); showToast('Deleted', 'success'); }
    }, 'json');
  });
});

/* ── Add / edit answer ── */
$(document).on('click', '.btn-add-answer', function(){
  $('#a_id').val(''); $('#a_qid').val($(this).data('qid')); $('#a_text').val('');
  $('#answerModalTitle').text('Add Answer');
  new bootstrap.Modal('#answerModal').show();
});

$(document).on('click', '.btn-edit-a', function(){
  $('#a_id').val($(this).data('id'));
  $('#a_qid').val($(this).data('qid'));
  $('#a_text').val($(this).data('text'));
  $('#answerModalTitle').text('Edit Answer');
  new bootstrap.Modal('#answerModal').show();
});

$('#saveAnswerBtn').on('click', function(){
  let id = $('#a_id').val(), qid = $('#a_qid').val();
  let action = id ? 'update_answer' : 'add_answer';
  let payload = {action, answer:$('#a_text').val()};
  if (id) payload.id = id; else payload.question_id = qid;

  $(this).prop('disabled',true).html('<span class="spinner-border spinner-border-sm"></span>');
  $.post(AJAX, payload, function(r){
    $('#saveAnswerBtn').prop('disabled',false).html('<i class="bi bi-check2-circle me-1"></i>Save Answer');
    if (r.status === 'success') {
      bootstrap.Modal.getInstance('#answerModal').hide();
      loadAnswers(qid);
      if (!id) {
        // update answer count in question header
        let $badge = $(`.question-card[data-qid="${qid}"] .text-primary.fw-semibold`);
        let cur = parseInt($badge.text()) || 0;
        $badge.text((cur+1) + ' answer' + (cur+1!=1?'s':''));
      }
      showToast('Answer saved', 'success');
    }
  }, 'json');
});

$(document).on('click', '.btn-del-a', function(){
  let id = $(this).data('id'), qid = $(this).data('qid');
  Swal.fire({
    icon:'warning', title:'Delete Answer?', showCancelButton:true,
    confirmButtonColor:'#dc3545', confirmButtonText:'Delete'
  }).then(r => {
    if (!r.isConfirmed) return;
    $.post(AJAX, {action:'delete_answer', id}, function(res){
      if (res.status === 'success') { loadAnswers(qid); showToast('Answer deleted', 'success'); }
    }, 'json');
  });
});

/* ── Mark correct ── */
$(document).on('click', '.btn-mark-correct', function(){
  let aid = $(this).data('aid'), qid = $(this).data('qid'), is = $(this).data('is');
  $.post(AJAX, {action:'mark_correct', answer_id:aid, question_id:qid, is_correct:is}, function(r){
    if (r.status === 'success') { loadAnswers(qid); showToast(is ? 'Marked as correct' : 'Unmarked', 'success'); }
  }, 'json');
});

/* ── Utils ── */
function escHtml(str) {
  return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function timeAgo(dt) {
  if (!dt) return '';
  let diff = Math.floor((Date.now() - new Date(dt)) / 1000);
  if (diff < 60) return diff + 's ago';
  if (diff < 3600) return Math.floor(diff/60) + 'm ago';
  if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
  return Math.floor(diff/86400) + 'd ago';
}
function showToast(msg, icon) {
  Swal.fire({toast:true, position:'top-end', icon:icon||'success', title:msg, timer:1800, showConfirmButton:false});
}

/* ══════════════════════════════════════════════════════
   LESSON 360 VIEWER
══════════════════════════════════════════════════════ */
function getEmbedUrl(path) {
  if (!path) return null;
  let yt = path.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s?#]+)/);
  if (yt) return 'https://www.youtube.com/embed/' + yt[1] + '?autoplay=1&rel=0';
  if (path.startsWith('http')) return path;
  return '../../' + path.replace(/^\.\.\//, '');
}

function ucFirst(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }

const TYPE_META = {
  video : { icon:'bi-play-circle-fill', color:'#ef4444', bg:'rgba(239,68,68,.15)',  label:'Video'   },
  audio : { icon:'bi-music-note-beamed', color:'#f59e0b', bg:'rgba(245,158,11,.15)', label:'Audio'   },
  pdf   : { icon:'bi-file-pdf-fill',     color:'#dc2626', bg:'rgba(220,38,38,.15)',  label:'PDF'     },
  ppt   : { icon:'bi-file-earmark-slides-fill', color:'#ea580c', bg:'rgba(234,88,12,.15)', label:'Slides' },
  live  : { icon:'bi-broadcast-fill',    color:'#16a34a', bg:'rgba(22,163,74,.15)',  label:'Live'    },
};

$(document).on('click', '.btn-view-lesson', function() {
  let id    = $(this).data('id');
  let title = $(this).data('title') || 'Lesson';
  let type  = $(this).data('type')  || 'video';
  let path  = $(this).data('path')  || '';
  let desc  = $(this).data('desc')  || '';

  let tm = TYPE_META[type] || { icon:'bi-file-earmark', color:'#64748b', bg:'rgba(100,116,139,.15)', label:ucFirst(type) };

  // Populate header
  $('#lv_type_icon').css({background: tm.bg, color: tm.color})
    .html(`<i class="bi ${tm.icon}"></i>`);
  $('#lv_title').text(title);
  $('#lv_meta').text(tm.label + (desc ? ' · ' + desc.slice(0, 80) + (desc.length > 80 ? '…' : '') : ''));

  // Info strip
  let infoBadges = `
    <span class="lv-info-badge" style="background:#e0e7ff;color:#3730a3"><i class="bi bi-collection-play me-1"></i>${tm.label}</span>
    ${desc ? `<span class="lv-info-badge" style="background:#f0fdf4;color:#15803d"><i class="bi bi-info-circle me-1"></i>${escHtml(desc.slice(0,100))}</span>` : ''}
  `;
  $('#lv_info').html(infoBadges);

  // Build content
  let url = getEmbedUrl(path);
  let content = '';
  if (type === 'video' || type === 'ppt') {
    content = url
      ? `<iframe src="${url}" style="width:100%;height:480px;border:none;display:block" allowfullscreen allow="autoplay; encrypted-media"></iframe>`
      : `<div class="qa-loading" style="min-height:380px;color:#94a3b8"><i class="bi bi-exclamation-circle fs-3 d-block mb-2"></i>No video URL set</div>`;
  } else if (type === 'audio') {
    content = url
      ? `<div style="display:flex;align-items:center;justify-content:center;min-height:380px;padding:2rem"><div style="width:100%;max-width:560px"><div style="width:64px;height:64px;border-radius:50%;background:rgba(245,158,11,.15);color:#f59e0b;display:flex;align-items:center;justify-content:center;font-size:2rem;margin:0 auto 1.5rem"><i class="bi bi-music-note-beamed"></i></div><audio controls style="width:100%;border-radius:10px" autoplay><source src="${url}">Your browser does not support audio.</audio></div></div>`
      : `<div class="qa-loading" style="min-height:380px;color:#94a3b8">No audio file set</div>`;
  } else if (type === 'pdf') {
    content = url
      ? `<iframe src="${url}" style="width:100%;height:540px;border:none;display:block"></iframe>`
      : `<div class="qa-loading" style="min-height:380px;color:#94a3b8">No PDF set</div>`;
  } else if (type === 'live') {
    content = url
      ? `<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:380px;gap:1.5rem;padding:2rem"><div style="width:70px;height:70px;border-radius:50%;background:rgba(22,163,74,.15);color:#16a34a;display:flex;align-items:center;justify-content:center;font-size:2.2rem"><i class="bi bi-broadcast-fill"></i></div><div style="color:#94a3b8;font-size:.9rem">Live session link</div><a href="${url}" target="_blank" class="dcm-btn dcm-btn-success"><i class="bi bi-box-arrow-up-right me-1"></i>Join Session</a></div>`
      : `<div class="qa-loading" style="min-height:380px;color:#94a3b8">No live link set</div>`;
  } else {
    content = url
      ? `<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:380px;gap:1.25rem"><i class="bi bi-file-earmark-arrow-down" style="font-size:3rem;color:#94a3b8"></i><div style="color:#94a3b8;font-size:.88rem">File content</div><a href="${url}" download class="dcm-btn dcm-btn-primary"><i class="bi bi-download me-1"></i>Download File</a></div>`
      : `<div class="qa-loading" style="min-height:380px;color:#94a3b8">No file attached</div>`;
  }
  $('#lv_content').html(content);

  new bootstrap.Modal('#lessonViewerModal').show();
});

$('#lessonViewerModal').on('hide.bs.modal', function() {
  $('#lv_content').html('<div class="qa-loading" style="min-height:380px"><div class="spinner-border text-white opacity-50"></div></div>');
});

/* ── Init ── */
loadChapters();
</script>
