<?php if ($user_role != 5) { include('403.php'); return; } ?>

<div class="container-fluid px-3 py-3">

  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <h5 class="fw-semibold mb-1"><i class="bi bi-collection-play-fill text-primary me-2"></i>All Courses</h5>
      <small class="text-muted">View and manage every instructor course on the platform</small>
    </div>
  </div>

  <!-- Stats bar -->
  <div class="row g-3 mb-4" id="statsRow">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-3 bg-primary bg-opacity-10 p-2"><i class="bi bi-collection-play-fill text-primary fs-5"></i></div>
          <div><div class="fw-bold fs-5" id="statTotal">—</div><div class="text-muted small">Total Courses</div></div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-3 bg-success bg-opacity-10 p-2"><i class="bi bi-check-circle-fill text-success fs-5"></i></div>
          <div><div class="fw-bold fs-5" id="statActive">—</div><div class="text-muted small">Active</div></div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-3 bg-warning bg-opacity-10 p-2"><i class="bi bi-hourglass-split text-warning fs-5"></i></div>
          <div><div class="fw-bold fs-5" id="statPending">—</div><div class="text-muted small">Pending Approval</div></div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-3 bg-info bg-opacity-10 p-2"><i class="bi bi-people-fill text-info fs-5"></i></div>
          <div><div class="fw-bold fs-5" id="statInstructors">—</div><div class="text-muted small">Instructors</div></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters -->
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
      <div class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Search courses or instructor…">
          </div>
        </div>
        <div class="col col-md-auto">
          <select id="filterInstructor" class="form-select form-select-sm">
            <option value="">All Instructors</option>
          </select>
        </div>
        <div class="col col-md-auto">
          <select id="filterStatus" class="form-select form-select-sm">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="is_draft">Draft</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="col col-md-auto">
          <select id="filterApproval" class="form-select form-select-sm">
            <option value="">All Approvals</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
        <div class="col-auto ms-md-auto">
          <button class="btn btn-sm btn-outline-secondary" id="btnReset"><i class="bi bi-x-circle me-1"></i>Reset</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Table card -->
  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="coursesTable">
          <thead class="table-light">
            <tr>
              <th style="width:44px"></th>
              <th>Course</th>
              <th>Instructor</th>
              <th class="text-center">Chapters</th>
              <th class="text-center">Lessons</th>
              <th class="text-center">Q&amp;A</th>
              <th class="text-center">Students</th>
              <th>Status</th>
              <th>Approval</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody id="coursesBody">
            <tr><td colspan="10" class="text-center py-5">
              <div class="spinner-border text-primary" role="status"></div>
            </td></tr>
          </tbody>
        </table>
      </div>
      <!-- Pagination -->
      <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top" id="paginationBar" style="display:none!important">
        <small class="text-muted" id="pageInfo"></small>
        <div class="d-flex gap-1" id="pageBtns"></div>
      </div>
    </div>
  </div>
</div>

<!-- ── Edit Course Modal ───────────────────────────────── -->
<div class="modal fade" id="editCourseModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title fw-semibold"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Course</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="ec_id">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label fw-semibold small">Course Title</label>
            <input type="text" id="ec_title" class="form-control">
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold small">Description</label>
            <textarea id="ec_desc" class="form-control" rows="3"></textarea>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold small">Status</label>
            <select id="ec_status" class="form-select">
              <option value="active">Active</option>
              <option value="is_draft">Draft</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold small">Approval</label>
            <select id="ec_approval" class="form-select">
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold small">Price (TZS)</label>
            <input type="number" id="ec_price" class="form-control" min="0">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary btn-sm" id="saveCourseBtn">
          <i class="bi bi-check2 me-1"></i>Save Changes
        </button>
      </div>
    </div>
  </div>
</div>

<script>
const AJAX = '../data_files/ajax/ajax_admin_courses.php';
let currentPage = 1;

/* ── Stat cards ── */
function loadStats() {
  $.post(AJAX, {action:'list', page:1}, function(r){
    if (r.status !== 'success') return;
    let total = r.total, active = 0, pending = 0;
    r.data.forEach(c => {
      if (c.status === 'active') active++;
      if (c.is_approved === 'pending') pending++;
    });
    // fetch full count
    $('#statTotal').text(r.total);
  }, 'json');

  // get active count
  $.post(AJAX, {action:'list', status:'active', page:1}, r => $('#statActive').text(r.total ?? '—'), 'json');
  $.post(AJAX, {action:'list', approval:'pending', page:1}, r => $('#statPending').text(r.total ?? '—'), 'json');
  $.post(AJAX, {action:'instructors'}, function(r){
    if (r.status !== 'success') return;
    $('#statInstructors').text(r.data.length);
    let $sel = $('#filterInstructor');
    r.data.forEach(i => $sel.append(`<option value="${i.usr_code}">${i.first_name} ${i.last_name}</option>`));
  }, 'json');
}

/* ── Status & approval badges ── */
const STATUS_STYLE = {
  active:   {bg:'#dcfce7', color:'#15803d', border:'#bbf7d0', label:'Active'},
  is_draft: {bg:'#fef9c3', color:'#92400e', border:'#fde68a', label:'Draft'},
  inactive: {bg:'#f1f5f9', color:'#475569', border:'#e2e8f0', label:'Inactive'},
};
const APPROVAL_STYLE = {
  approved: {bg:'#dcfce7', color:'#15803d', border:'#bbf7d0'},
  pending:  {bg:'#fef9c3', color:'#92400e', border:'#fde68a'},
  rejected: {bg:'#fee2e2', color:'#b91c1c', border:'#fecaca'},
};
function statusBadge(s) {
  const m = STATUS_STYLE[s] || {bg:'#f1f5f9', color:'#475569', border:'#e2e8f0', label: s};
  return `<span style="display:inline-block;padding:.25rem .65rem;border-radius:100px;font-size:.72rem;font-weight:700;background:${m.bg};color:${m.color};border:1.5px solid ${m.border}">${m.label}</span>`;
}
function approvalBadge(a) {
  const m = APPROVAL_STYLE[a] || {bg:'#f1f5f9', color:'#475569', border:'#e2e8f0'};
  const lbl = (a||'').charAt(0).toUpperCase() + (a||'').slice(1);
  return `<span style="display:inline-block;padding:.25rem .65rem;border-radius:100px;font-size:.72rem;font-weight:700;background:${m.bg};color:${m.color};border:1.5px solid ${m.border}">${lbl}</span>`;
}

/* ── Load table ── */
function loadCourses(page) {
  page = page || 1; currentPage = page;
  $('#coursesBody').html('<tr><td colspan="10" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>');

  $.post(AJAX, {
    action: 'list',
    search:     $('#searchInput').val(),
    instructor: $('#filterInstructor').val(),
    status:     $('#filterStatus').val(),
    approval:   $('#filterApproval').val(),
    page: page
  }, function(r) {
    if (r.status !== 'success') return;
    let html = '';
    if (!r.data.length) {
      html = '<tr><td colspan="10" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-2"></i>No courses found</td></tr>';
    } else {
      r.data.forEach(c => {
        let thumb = c.thumbnail ? `../uploads/${c.thumbnail}` : '../assets/img/logo.svg';
        html += `
          <tr>
            <td><img src="${thumb}" class="rounded-2 object-fit-cover" width="40" height="40" onerror="this.src='../assets/img/logo.svg'"></td>
            <td>
              <div class="fw-semibold small text-truncate" style="max-width:220px" title="${c.title}">${c.title}</div>
              <small class="text-muted">${c.price > 0 ? 'TZS '+Number(c.price).toLocaleString() : 'Free'}</small>
            </td>
            <td><div class="small">${c.first_name||''} ${c.last_name||''}</div><small class="text-muted">${c.email_address||''}</small></td>
            <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary">${c.chapters}</span></td>
            <td class="text-center"><span class="badge bg-info bg-opacity-10 text-info">${c.lessons}</span></td>
            <td class="text-center"><span class="badge bg-secondary bg-opacity-10 text-secondary">${c.questions}</span></td>
            <td class="text-center"><span class="badge bg-success bg-opacity-10 text-success">${c.enrollments}</span></td>
            <td>${statusBadge(c.status)}</td>
            <td>${approvalBadge(c.is_approved)}</td>
            <td class="text-end">
              <div class="d-flex gap-1 justify-content-end">
                <a href="?view=admin_course_detail&cid=${c.id}" class="btn btn-sm btn-primary" title="Manage">
                  <i class="bi bi-folder2-open"></i>
                </a>
                <button class="btn btn-sm btn-outline-secondary btn-edit" data-id="${c.id}" title="Edit"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger btn-del" data-id="${c.id}" data-title="${c.title}" title="Delete"><i class="bi bi-trash"></i></button>
              </div>
            </td>
          </tr>`;
      });
    }
    $('#coursesBody').html(html);

    // Pagination
    let total = r.total, per = r.per, pages = Math.ceil(total/per);
    $('#pageInfo').text(`Showing ${Math.min((page-1)*per+1,total)}–${Math.min(page*per,total)} of ${total}`);
    let btnHtml = '';
    for (let p = 1; p <= pages; p++) {
      btnHtml += `<button class="btn btn-sm ${p===page?'btn-primary':'btn-outline-secondary'} page-btn" data-p="${p}">${p}</button>`;
    }
    $('#pageBtns').html(btnHtml);
    $('#paginationBar').toggle(total > 0);
  }, 'json');
}

/* ── Edit modal ── */
$(document).on('click', '.btn-edit', function(){
  let id = $(this).data('id');
  $.post(AJAX, {action:'get', id}, function(r){
    if (r.status !== 'success') return;
    let c = r.data;
    $('#ec_id').val(c.id);
    $('#ec_title').val(c.title);
    $('#ec_desc').val(c.description);
    $('#ec_status').val(c.status);
    $('#ec_approval').val(c.is_approved);
    $('#ec_price').val(c.price);
    new bootstrap.Modal('#editCourseModal').show();
  }, 'json');
});

$('#saveCourseBtn').on('click', function(){
  $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
  $.post(AJAX, {
    action:'update_course',
    id: $('#ec_id').val(),
    title: $('#ec_title').val(),
    description: $('#ec_desc').val(),
    status: $('#ec_status').val(),
    is_approved: $('#ec_approval').val(),
    price: $('#ec_price').val()
  }, function(r){
    $('#saveCourseBtn').prop('disabled',false).html('<i class="bi bi-check2 me-1"></i>Save Changes');
    if (r.status === 'success') {
      bootstrap.Modal.getInstance('#editCourseModal').hide();
      loadCourses(currentPage);
      Swal.fire({icon:'success', title:'Saved!', timer:1200, showConfirmButton:false, toast:true, position:'top-end'});
    } else {
      Swal.fire({icon:'error', title:'Error', text:r.message});
    }
  }, 'json');
});

/* ── Delete ── */
$(document).on('click', '.btn-del', function(){
  let id = $(this).data('id'), title = $(this).data('title');
  Swal.fire({
    icon:'warning', title:'Delete Course?',
    html:`<b>${title}</b><br><small class="text-muted">This will soft-delete the course. Chapters and lessons remain in the DB.</small>`,
    showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Yes, delete'
  }).then(res => {
    if (!res.isConfirmed) return;
    $.post(AJAX, {action:'delete_course', id}, function(r){
      if (r.status === 'success') {
        loadCourses(currentPage);
        loadStats();
        Swal.fire({icon:'success', title:'Deleted', timer:1200, showConfirmButton:false, toast:true, position:'top-end'});
      }
    }, 'json');
  });
});

/* ── Pagination ── */
$(document).on('click', '.page-btn', function(){ loadCourses($(this).data('p')); });

/* ── Filters ── */
let searchTimer;
$('#searchInput').on('input', function(){ clearTimeout(searchTimer); searchTimer = setTimeout(() => loadCourses(1), 350); });
$('#filterInstructor, #filterStatus, #filterApproval').on('change', () => loadCourses(1));
$('#btnReset').on('click', function(){
  $('#searchInput').val(''); $('#filterInstructor, #filterStatus, #filterApproval').val('');
  loadCourses(1);
});

/* ── Init ── */
loadStats();
loadCourses(1);
</script>
