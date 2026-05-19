<?php
if ($user_role != 5) { include('403.php'); return; }

$roles = $db->query("
    SELECT r.id, r.role_title,
           COUNT(u.id) AS user_count
    FROM tbl_user_roles r
    LEFT JOIN tbl_all_users u ON u.user_role = r.id
    GROUP BY r.id
    ORDER BY r.id
")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-fluid px-3 py-3">

  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <h5 class="fw-semibold mb-1"><i class="bi bi-layers-fill text-primary me-2"></i>Roles</h5>
      <small class="text-muted">Manage platform roles. Role ID is used across the system.</small>
    </div>
    <button class="btn btn-primary btn-sm" onclick="openAddRole()">
      <i class="bi bi-plus-lg me-1"></i>Add Role
    </button>
  </div>

  <div class="row g-3">
    <?php foreach ($roles as $r):
      $isSys = $r['id'] <= 5;
      $colors = ['','info','secondary','success','warning','danger'];
      $col    = $colors[$r['id']] ?? 'primary';
    ?>
    <div class="col-12 col-md-6 col-xl-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle bg-<?= $col ?> bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px;height:52px">
            <span class="fw-bold text-<?= $col ?> fs-5"><?= $r['id'] ?></span>
          </div>
          <div class="flex-grow-1 min-w-0">
            <div class="fw-semibold"><?= htmlspecialchars($r['role_title']) ?></div>
            <div class="small text-muted"><?= $r['user_count'] ?> user<?= $r['user_count'] != 1 ? 's' : '' ?></div>
            <?php if ($r['id'] <= 5): ?><span class="badge bg-light text-muted border" style="font-size:.65rem">System Role</span><?php endif; ?>
          </div>
          <div class="d-flex gap-1 flex-shrink-0">
            <button class="btn btn-sm btn-outline-primary py-0 px-2" onclick="editRole(<?= $r['id'] ?>, <?= json_encode($r['role_title']) ?>)">
              <i class="bi bi-pencil"></i>
            </button>
            <?php if ($r['id'] > 5 && $r['user_count'] == 0): ?>
            <button class="btn btn-sm btn-outline-danger py-0 px-2" onclick="deleteRole(<?= $r['id'] ?>, <?= json_encode($r['role_title']) ?>)">
              <i class="bi bi-trash"></i>
            </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

</div>

<!-- Role Modal -->
<div class="modal fade" id="roleModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content custom-modal">
      <div class="modal-header">
        <h6 class="modal-title fw-semibold" id="roleModalTitle">Add Role</h6>
        <button type="button" class="btn-close custom-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="roleEditId">
        <label class="form-label small fw-medium">Role Title <span class="text-danger">*</span></label>
        <input type="text" id="roleTitle" class="form-control form-control-sm" placeholder="e.g. Content Creator">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary btn-sm" onclick="saveRole()">Save</button>
      </div>
    </div>
  </div>
</div>

<script>
let roleModal;
document.addEventListener('DOMContentLoaded', () => { roleModal = new bootstrap.Modal(document.getElementById('roleModal')); });

function openAddRole() {
  document.getElementById('roleModalTitle').textContent = 'Add Role';
  document.getElementById('roleEditId').value = '';
  document.getElementById('roleTitle').value  = '';
  roleModal.show();
}

function editRole(id, title) {
  document.getElementById('roleModalTitle').textContent = 'Edit Role';
  document.getElementById('roleEditId').value = id;
  document.getElementById('roleTitle').value  = title;
  roleModal.show();
}

function saveRole() {
  const id    = document.getElementById('roleEditId').value;
  const title = document.getElementById('roleTitle').value.trim();
  if (!title) { Swal.fire('Required', 'Role title cannot be empty.', 'warning'); return; }

  Swal.fire({ title:'Saving...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
  const fd = new FormData();
  fd.append('action', id ? 'update' : 'create');
  fd.append('id',     id);
  fd.append('title',  title);
  fetch('ajax/ajax_admin_roles.php', { method:'POST', body: fd })
    .then(r => r.json()).then(res => {
      Swal.close();
      if (res.status === 'success') {
        roleModal.hide();
        Swal.fire({ icon:'success', timer:1200, showConfirmButton:false })
          .then(() => location.reload());
      } else Swal.fire('Error', res.message, 'error');
    });
}

function deleteRole(id, title) {
  Swal.fire({ title:`Delete "${title}"?`, icon:'warning', showCancelButton:true,
    confirmButtonColor:'#dc3545', confirmButtonText:'Delete' })
  .then(r => {
    if (!r.isConfirmed) return;
    const fd = new FormData();
    fd.append('action','delete'); fd.append('id', id);
    fetch('ajax/ajax_admin_roles.php', { method:'POST', body: fd })
      .then(r => r.json()).then(res => {
        if (res.status === 'success') location.reload();
        else Swal.fire('Error', res.message, 'error');
      });
  });
}
</script>
