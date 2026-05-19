<?php
$user_role = isset($user_role) ? (int)$user_role : 0;
if ($user_role != 5) { include('403.php'); return; }

$roles = $db->query("SELECT id, role_title FROM tbl_user_roles ORDER BY id")->fetch_all(MYSQLI_ASSOC);

$stats = $db->query("
    SELECT
        COUNT(*)                          AS total,
        SUM(user_role = '1')              AS students,
        SUM(user_role IN ('3','4'))       AS instructors,
        SUM(user_status = 'Active')       AS active,
        SUM(user_status = 'Inactive')     AS inactive
    FROM tbl_all_users
")->fetch_assoc();
?>

<style>
/* ── Stat cards ── */
.um-stat {
  background:#fff; border:1.5px solid #e8edf3; border-radius:14px;
  padding:1.1rem 1.25rem; position:relative; overflow:hidden;
  transition:box-shadow .2s;
}
.um-stat:hover { box-shadow:0 6px 24px rgba(0,0,0,.08); }
.um-stat::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:var(--sc); }
.um-stat-icon { width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;background:var(--sb);color:var(--sc);flex-shrink:0; }
.um-stat-val  { font-size:1.6rem;font-weight:800;color:#0f172a;line-height:1; }
.um-stat-lbl  { font-size:.75rem;color:#64748b;margin-top:.2rem; }

/* ── Filter bar ── */
.um-filters { background:#fff;border:1.5px solid #e8edf3;border-radius:14px;padding:.75rem 1rem; }
.um-filters .form-control,
.um-filters .form-select {
  border:1.5px solid #e8edf3;border-radius:9px;font-size:.83rem;
  background:#fafcff;transition:border-color .2s,box-shadow .2s;
}
.um-filters .form-control:focus,
.um-filters .form-select:focus { border-color:#1a4fc4;box-shadow:0 0 0 3px rgba(26,79,196,.09); }
.um-search-wrap { position:relative; }
.um-search-wrap .bi-search { position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:.85rem;pointer-events:none; }
.um-search-wrap input { padding-left:32px; }

/* ── Table card ── */
.um-card { background:#fff;border:1.5px solid #e8edf3;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04),0 8px 24px rgba(0,0,0,.05); }
.um-table { width:100%;border-collapse:collapse; }
.um-table th {
  padding:.65rem 1rem;background:#f8fafc;
  font-size:.72rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#64748b;
  border-bottom:1px solid #e8edf3;white-space:nowrap;
}
.um-table td { padding:.7rem 1rem;border-bottom:1px solid #f1f5f9;vertical-align:middle; }
.um-table tr:last-child td { border-bottom:none; }
.um-table tbody tr { transition:background .12s; }
.um-table tbody tr:hover { background:#f8fbff; }

/* ── Avatar ── */
.u-avatar {
  width:38px;height:38px;border-radius:11px;
  display:flex;align-items:center;justify-content:center;
  font-size:.8rem;font-weight:800;color:#fff;flex-shrink:0;
  letter-spacing:.02em;
}

/* ── Role & status badges ── */
.role-pill {
  display:inline-flex;align-items:center;gap:.35rem;
  padding:.28rem .7rem;border-radius:100px;font-size:.72rem;font-weight:700;
  border:1.5px solid transparent;white-space:nowrap;
}
.status-dot { width:7px;height:7px;border-radius:50%;display:inline-block;flex-shrink:0; }

/* ── Actions dropdown ── */
.um-action-btn {
  width:32px;height:32px;border-radius:8px;
  border:1.5px solid #e8edf3;background:#fff;
  display:flex;align-items:center;justify-content:center;
  color:#64748b;cursor:pointer;transition:all .15s;
  font-size:1rem;
}
.um-action-btn:hover { border-color:#1a4fc4;color:#1a4fc4;background:#eff6ff; }
.um-dropdown {
  position:absolute;right:0;top:calc(100% + 6px);
  background:#fff;border:1.5px solid #e8edf3;border-radius:12px;
  box-shadow:0 8px 32px rgba(0,0,0,.12);
  min-width:180px;z-index:999;overflow:hidden;
  animation:dropIn .15s cubic-bezier(.16,1,.3,1);
}
@keyframes dropIn { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }
.um-dropdown-item {
  display:flex;align-items:center;gap:.65rem;
  padding:.65rem 1rem;font-size:.82rem;font-weight:600;
  color:#0f172a;cursor:pointer;transition:background .12s;border:none;background:none;width:100%;text-align:left;
}
.um-dropdown-item:hover { background:#f8fafc; }
.um-dropdown-item.danger { color:#dc2626; }
.um-dropdown-item.danger:hover { background:#fef2f2; }
.um-dropdown-item i { font-size:.95rem;width:18px;text-align:center; }
.um-dropdown-sep { height:1px;background:#f1f5f9;margin:.25rem 0; }
.um-action-wrap { position:relative; }

/* ── Pagination ── */
.um-page-btn {
  width:34px;height:34px;border-radius:9px;border:1.5px solid #e8edf3;
  background:#fff;color:#64748b;font-size:.83rem;font-weight:600;
  display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .15s;
}
.um-page-btn:hover { border-color:#1a4fc4;color:#1a4fc4;background:#eff6ff; }
.um-page-btn.active { background:#1a4fc4;border-color:#1a4fc4;color:#fff; }
.um-page-btn:disabled { opacity:.4;cursor:not-allowed; }

/* ── Modal ── */
.um-modal .modal-content { border:none;border-radius:18px;box-shadow:0 20px 60px rgba(0,0,0,.18);overflow:hidden; }
.um-modal .modal-header { background:linear-gradient(135deg,#1a4fc4,#6d28d9);padding:1.25rem 1.5rem;border:none; }
.um-modal .modal-title { color:#fff;font-weight:700;font-size:1rem; }
.um-modal .btn-close { filter:brightness(0) invert(1);opacity:.8; }
.um-modal .modal-body { padding:1.5rem; }
.um-modal .modal-footer { border-top:1px solid #f1f5f9;padding:.85rem 1.5rem; }

.um-field label { font-size:.78rem;font-weight:700;color:#0f172a;margin-bottom:.4rem;letter-spacing:.02em; }
.um-field .form-control,
.um-field .form-select {
  border:1.5px solid #e8edf3;border-radius:10px;font-size:.875rem;
  background:#fafcff;height:44px;transition:border-color .2s,box-shadow .2s;
}
.um-field .form-control:focus,
.um-field .form-select:focus { border-color:#1a4fc4;box-shadow:0 0 0 4px rgba(26,79,196,.1);background:#fff; }
.um-field .field-icon-wrap { position:relative; }
.um-field .field-icon { position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:.95rem;pointer-events:none; }
.um-field .form-control.has-icon { padding-left:38px; }
.um-save-btn {
  background:linear-gradient(135deg,#1a4fc4,#6d28d9);color:#fff;
  border:none;border-radius:10px;padding:.6rem 1.5rem;
  font-weight:700;font-size:.88rem;transition:opacity .2s,transform .2s;
  box-shadow:0 4px 14px rgba(26,79,196,.35);
}
.um-save-btn:hover { opacity:.92;transform:translateY(-1px); }
.um-save-btn:disabled { opacity:.55;transform:none; }

/* ── Fade in ── */
@keyframes fadeUp { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
.um-stat { animation:fadeUp .3s cubic-bezier(.16,1,.3,1) both; }
.um-stat:nth-child(2){animation-delay:.05s}
.um-stat:nth-child(3){animation-delay:.1s}
.um-stat:nth-child(4){animation-delay:.15s}
</style>

<div class="container-fluid px-3 py-3">

  <!-- ── Header ── -->
  <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <div style="width:38px;height:38px;border-radius:11px;background:linear-gradient(135deg,#1a4fc4,#6d28d9);display:flex;align-items:center;justify-content:center">
          <i class="bi bi-people-fill text-white fs-6"></i>
        </div>
        <h5 class="fw-bold mb-0">User Management</h5>
      </div>
      <p class="text-muted small mb-0 ms-1">Create, edit and control access for all platform users</p>
    </div>
    <button class="btn btn-primary btn-sm px-3" style="border-radius:10px;font-weight:600" onclick="openCreateModal()">
      <i class="bi bi-person-plus me-1"></i>Add User
    </button>
  </div>

  <!-- ── Stat cards ── -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
      <div class="um-stat" style="--sc:#1a4fc4;--sb:#eff6ff">
        <div class="d-flex align-items-center gap-3">
          <div class="um-stat-icon"><i class="bi bi-people-fill"></i></div>
          <div><div class="um-stat-val"><?= number_format($stats['total']) ?></div><div class="um-stat-lbl">Total Users</div></div>
        </div>
      </div>
    </div>
    <div class="col-6 col-xl-3">
      <div class="um-stat" style="--sc:#22c55e;--sb:#f0fdf4">
        <div class="d-flex align-items-center gap-3">
          <div class="um-stat-icon"><i class="bi bi-circle-fill" style="font-size:.5rem;margin:0"></i><i class="bi bi-check-circle-fill ms-1"></i></div>
          <div><div class="um-stat-val"><?= number_format($stats['active']) ?></div><div class="um-stat-lbl">Active</div></div>
        </div>
      </div>
    </div>
    <div class="col-6 col-xl-3">
      <div class="um-stat" style="--sc:#f97316;--sb:#fff7ed">
        <div class="d-flex align-items-center gap-3">
          <div class="um-stat-icon"><i class="bi bi-mortarboard-fill"></i></div>
          <div><div class="um-stat-val"><?= number_format($stats['students']) ?></div><div class="um-stat-lbl">Students</div></div>
        </div>
      </div>
    </div>
    <div class="col-6 col-xl-3">
      <div class="um-stat" style="--sc:#8b5cf6;--sb:#f5f3ff">
        <div class="d-flex align-items-center gap-3">
          <div class="um-stat-icon"><i class="bi bi-person-video3"></i></div>
          <div><div class="um-stat-val"><?= number_format($stats['instructors']) ?></div><div class="um-stat-lbl">Instructors</div></div>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Filters ── -->
  <div class="um-filters d-flex align-items-center gap-2 flex-wrap mb-3">
    <div class="um-search-wrap flex-grow-1" style="min-width:200px">
      <i class="bi bi-search"></i>
      <input type="text" id="fSearch" class="form-control form-control-sm"
             placeholder="Search name, email, phone…" oninput="debounceSearch()">
    </div>
    <select id="fRole" class="form-select form-select-sm" style="width:auto;min-width:140px" onchange="loadUsers()">
      <option value="">All Roles</option>
      <?php foreach ($roles as $r): ?>
      <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['role_title']) ?></option>
      <?php endforeach; ?>
    </select>
    <select id="fStatus" class="form-select form-select-sm" style="width:auto;min-width:120px" onchange="loadUsers()">
      <option value="">All Status</option>
      <option value="Active">Active</option>
      <option value="Inactive">Inactive</option>
    </select>
    <button class="btn btn-sm btn-outline-secondary" style="border-radius:9px;border-color:#e8edf3" onclick="resetFilters()" title="Clear filters">
      <i class="bi bi-x-circle me-1"></i>Reset
    </button>
  </div>

  <!-- ── Table card ── -->
  <div class="um-card">
    <!-- Table meta -->
    <div style="padding:.7rem 1.1rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between">
      <span class="small text-muted" id="uCount">Loading…</span>
      <div class="d-flex align-items-center gap-2">
        <select id="fPerPage" class="form-select form-select-sm" style="width:auto;font-size:.75rem;border-radius:8px;border-color:#e8edf3" onchange="loadUsers()">
          <option value="15">15 / page</option>
          <option value="25" selected>25 / page</option>
          <option value="50">50 / page</option>
        </select>
      </div>
    </div>

    <div class="table-responsive">
      <table class="um-table">
        <thead>
          <tr>
            <th>User</th>
            <th>Contact</th>
            <th>Role</th>
            <th>Status</th>
            <th>Joined</th>
            <th style="text-align:right;padding-right:1.25rem">Actions</th>
          </tr>
        </thead>
        <tbody id="uTbody">
          <tr><td colspan="6" class="text-center py-5">
            <div class="spinner-border text-primary" style="width:1.5rem;height:1.5rem;border-width:2px"></div>
          </td></tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div style="padding:.75rem 1.1rem;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem" id="uPagination"></div>
  </div>

</div>

<!-- ── Dropdown click-outside close ── -->
<div id="dropOverlay" onclick="closeAllDropdowns()" style="position:fixed;inset:0;z-index:998;display:none"></div>

<!-- ═══ Create / Edit User Modal ══════════════════════════════════════════════ -->
<div class="modal fade um-modal" id="userModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-person-circle text-white fs-5"></i>
          <h6 class="modal-title mb-0" id="userModalTitle">Add New User</h6>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="editUserId">

        <!-- Name row -->
        <div class="row g-3 mb-0">
          <div class="col-6">
            <div class="um-field">
              <label>First Name <span class="text-danger">*</span></label>
              <div class="field-icon-wrap">
                <i class="bi bi-person field-icon"></i>
                <input type="text" id="uFirstName" class="form-control has-icon" placeholder="First name">
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="um-field">
              <label>Last Name <span class="text-danger">*</span></label>
              <div class="field-icon-wrap">
                <i class="bi bi-person field-icon"></i>
                <input type="text" id="uLastName" class="form-control has-icon" placeholder="Last name">
              </div>
            </div>
          </div>

          <div class="col-12">
            <div class="um-field">
              <label>Email Address <span class="text-danger">*</span></label>
              <div class="field-icon-wrap">
                <i class="bi bi-envelope field-icon"></i>
                <input type="email" id="uEmail" class="form-control has-icon" placeholder="myemail@gmail.com">
              </div>
            </div>
          </div>

          <div class="col-6">
            <div class="um-field">
              <label>Phone</label>
              <div class="field-icon-wrap">
                <i class="bi bi-phone field-icon"></i>
                <input type="text" id="uPhone" class="form-control has-icon" placeholder="+255…">
              </div>
            </div>
          </div>

          <div class="col-6">
            <div class="um-field">
              <label>Role <span class="text-danger">*</span></label>
              <select id="uRole" class="form-select">
                <?php foreach ($roles as $r): ?>
                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['role_title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Password (create only) -->
          <div class="col-12" id="passwordGroup">
            <div class="um-field">
              <label>Password <span class="text-danger">*</span></label>
              <div class="field-icon-wrap" style="position:relative">
                <i class="bi bi-lock field-icon"></i>
                <input type="password" id="uPassword" class="form-control has-icon" placeholder="Min. 8 characters" style="padding-right:44px">
                <button type="button" onclick="togglePwd()" tabindex="-1"
                  style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:#94a3b8;cursor:pointer;padding:4px">
                  <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Status (edit only) -->
          <div class="col-12" id="statusGroup" style="display:none">
            <div class="um-field">
              <label>Account Status</label>
              <select id="uStatus" class="form-select">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-sm btn-light" style="border-radius:9px" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="um-save-btn" id="saveUserBtn" onclick="saveUser()">
          <i class="bi bi-check2 me-1"></i><span id="saveBtnText">Save User</span>
        </button>
      </div>

    </div>
  </div>
</div>

<script>
const ROLES = <?= json_encode(array_column($roles, 'role_title', 'id')) ?>;

const ROLE_META = {
  '1': { label:'Student',     color:'#3b82f6', bg:'#eff6ff',  icon:'bi-mortarboard-fill' },
  '2': { label:'Parent',      color:'#8b5cf6', bg:'#f5f3ff',  icon:'bi-people-fill'      },
  '3': { label:'Instructor',  color:'#f97316', bg:'#fff7ed',  icon:'bi-person-video3'    },
  '4': { label:'School',      color:'#10b981', bg:'#f0fdf4',  icon:'bi-building'         },
  '5': { label:'Super Admin', color:'#dc2626', bg:'#fef2f2',  icon:'bi-shield-fill'      },
};

const AVATAR_COLORS = ['#1a4fc4','#6d28d9','#059669','#d97706','#dc2626','#0891b2','#7c3aed','#be185d'];

let userModal, page = 1, perPage = 25, totalUsers = 0, searchTimer;

document.addEventListener('DOMContentLoaded', () => {
  userModal = new bootstrap.Modal(document.getElementById('userModal'));
  loadUsers();
});

/* ── Filters ── */
function debounceSearch() { clearTimeout(searchTimer); searchTimer = setTimeout(loadUsers, 350); }
function resetFilters() {
  document.getElementById('fSearch').value = '';
  document.getElementById('fRole').value   = '';
  document.getElementById('fStatus').value = '';
  loadUsers();
}

/* ── Load ── */
function loadUsers() {
  page    = 1;
  perPage = parseInt(document.getElementById('fPerPage').value) || 25;
  fetchUsers();
}
function fetchUsers() {
  const params = new URLSearchParams({
    action:'list',
    role:   document.getElementById('fRole').value,
    status: document.getElementById('fStatus').value,
    q:      document.getElementById('fSearch').value,
    page, per_page: perPage
  });
  document.getElementById('uTbody').innerHTML =
    `<tr><td colspan="6" class="text-center py-5"><div class="spinner-border text-primary" style="width:1.5rem;height:1.5rem;border-width:2px"></div></td></tr>`;

  fetch(`ajax/ajax_admin_users.php?${params}`)
    .then(r => r.json())
    .then(res => {
      if (res.status !== 'success') return;
      totalUsers = res.total;
      document.getElementById('uCount').textContent =
        `${res.total} user${res.total !== 1 ? 's' : ''}`;
      renderUsers(res.data);
      renderPagination();
    });
}

/* ── Render rows ── */
function avatarColor(name) {
  let hash = 0;
  for (let c of (name || 'U')) hash = c.charCodeAt(0) + ((hash << 5) - hash);
  return AVATAR_COLORS[Math.abs(hash) % AVATAR_COLORS.length];
}
function initials(f, l) {
  return ((f||'?')[0] + (l||'')[0]).toUpperCase() || '?';
}

function renderUsers(rows) {
  const tbody = document.getElementById('uTbody');
  if (!rows.length) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-muted">
      <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>
      <div class="fw-semibold">No users found</div>
      <div class="small">Try adjusting your filters</div>
    </td></tr>`;
    return;
  }

  tbody.innerHTML = rows.map(u => {
    const rm     = ROLE_META[u.user_role] || ROLE_META['1'];
    const isActive = u.user_status === 'Active';
    const color  = avatarColor(u.first_name + u.last_name);
    const ini    = initials(u.first_name, u.last_name);
    const joined = formatDate(u.created_at);

    return `
    <tr>
      <!-- User -->
      <td>
        <div class="d-flex align-items-center gap-2">
          <div class="u-avatar" style="background:${color}">${ini}</div>
          <div>
            <div style="font-size:.85rem;font-weight:700;color:#0f172a;line-height:1.3">${esc(u.first_name)} ${esc(u.last_name)}</div>
            <div style="font-size:.7rem;color:#94a3b8;font-family:monospace">${esc(u.usr_code)}</div>
          </div>
        </div>
      </td>

      <!-- Contact -->
      <td>
        <div style="font-size:.82rem;color:#334155">${esc(u.email_address)}</div>
        <div style="font-size:.73rem;color:#94a3b8">${esc(u.phone_number || '—')}</div>
      </td>

      <!-- Role -->
      <td>
        <span class="role-pill" style="color:${rm.color};background:${rm.bg};border-color:${rm.color}22">
          <i class="bi ${rm.icon}" style="font-size:.7rem"></i>
          ${esc(rm.label || ROLES[u.user_role] || '?')}
        </span>
      </td>

      <!-- Status -->
      <td>
        <div class="d-flex align-items-center gap-2">
          <span class="status-dot" style="background:${isActive ? '#22c55e' : '#94a3b8'}"></span>
          <span style="font-size:.8rem;font-weight:600;color:${isActive ? '#15803d' : '#64748b'}">${u.user_status}</span>
        </div>
      </td>

      <!-- Joined -->
      <td style="font-size:.8rem;color:#64748b;white-space:nowrap">${joined}</td>

      <!-- Actions -->
      <td style="text-align:right;padding-right:1rem">
        <div class="um-action-wrap">
          <button class="um-action-btn" onclick="toggleDropdown(event, 'dd_${u.id}')" title="Actions">
            <i class="bi bi-three-dots-vertical"></i>
          </button>
          <div class="um-dropdown" id="dd_${u.id}" style="display:none">
            <button class="um-dropdown-item" onclick="closeAllDropdowns(); openEditModal(${JSON.stringify(u).replace(/"/g,'&quot;')})">
              <i class="bi bi-pencil-square" style="color:#1a4fc4"></i> Edit User
            </button>
            <button class="um-dropdown-item" onclick="closeAllDropdowns(); resetPassword(${u.id}, '${esc(u.first_name)}')">
              <i class="bi bi-key-fill" style="color:#f97316"></i> Reset Password
            </button>
            <div class="um-dropdown-sep"></div>
            <button class="um-dropdown-item danger" onclick="closeAllDropdowns(); deleteUser(${u.id}, '${esc(u.first_name+' '+u.last_name)}')">
              <i class="bi bi-trash3-fill"></i> Delete User
            </button>
          </div>
        </div>
      </td>
    </tr>`;
  }).join('');
}

/* ── Dropdown ── */
function toggleDropdown(e, id) {
  e.stopPropagation();
  const dd  = document.getElementById(id);
  const open = dd.style.display !== 'none';
  closeAllDropdowns();
  if (!open) {
    dd.style.display = '';
    document.getElementById('dropOverlay').style.display = '';
  }
}
function closeAllDropdowns() {
  document.querySelectorAll('.um-dropdown').forEach(d => d.style.display = 'none');
  document.getElementById('dropOverlay').style.display = 'none';
}

/* ── Pagination ── */
function renderPagination() {
  const pages = Math.ceil(totalUsers / perPage);
  const el = document.getElementById('uPagination');
  if (!totalUsers) { el.innerHTML = ''; return; }

  const from = Math.min((page-1)*perPage + 1, totalUsers);
  const to   = Math.min(page*perPage, totalUsers);

  let btns = '';
  btns += `<button class="um-page-btn" onclick="goPage(${page-1})" ${page<=1?'disabled':''}>‹</button>`;
  let start = Math.max(1, page-2), end = Math.min(pages, page+2);
  if (start > 1) btns += `<button class="um-page-btn" onclick="goPage(1)">1</button>${start>2?'<span style="padding:0 .25rem;color:#94a3b8">…</span>':''}`;
  for (let p = start; p <= end; p++) btns += `<button class="um-page-btn${p===page?' active':''}" onclick="goPage(${p})">${p}</button>`;
  if (end < pages) btns += `${end<pages-1?'<span style="padding:0 .25rem;color:#94a3b8">…</span>':''}<button class="um-page-btn" onclick="goPage(${pages})">${pages}</button>`;
  btns += `<button class="um-page-btn" onclick="goPage(${page+1})" ${page>=pages?'disabled':''}>›</button>`;

  el.innerHTML = `
    <span style="font-size:.78rem;color:#64748b">Showing ${from}–${to} of ${totalUsers}</span>
    <div class="d-flex gap-1">${btns}</div>`;
}
function goPage(p) { page = p; fetchUsers(); }

/* ── Modals ── */
function openCreateModal() {
  document.getElementById('userModalTitle').textContent = 'Add New User';
  document.getElementById('editUserId').value = '';
  ['uFirstName','uLastName','uEmail','uPhone','uPassword'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('uRole').value = '1';
  document.getElementById('passwordGroup').style.display = '';
  document.getElementById('statusGroup').style.display   = 'none';
  document.getElementById('saveBtnText').textContent = 'Create Account';
  userModal.show();
}

function openEditModal(u) {
  if (typeof u === 'string') { try { u = JSON.parse(u); } catch(e) { return; } }
  document.getElementById('userModalTitle').textContent = 'Edit User';
  document.getElementById('editUserId').value      = u.id;
  document.getElementById('uFirstName').value      = u.first_name   || '';
  document.getElementById('uLastName').value       = u.last_name    || '';
  document.getElementById('uEmail').value          = u.email_address|| '';
  document.getElementById('uPhone').value          = u.phone_number || '';
  document.getElementById('uRole').value           = u.user_role;
  document.getElementById('uStatus').value         = u.user_status;
  document.getElementById('uPassword').value       = '';
  document.getElementById('passwordGroup').style.display = 'none';
  document.getElementById('statusGroup').style.display   = '';
  document.getElementById('saveBtnText').textContent = 'Save Changes';
  userModal.show();
}

/* ── Save ── */
function saveUser() {
  const id         = document.getElementById('editUserId').value;
  const first_name = document.getElementById('uFirstName').value.trim();
  const last_name  = document.getElementById('uLastName').value.trim();
  const email      = document.getElementById('uEmail').value.trim();
  const phone      = document.getElementById('uPhone').value.trim();
  const role       = document.getElementById('uRole').value;
  const password   = document.getElementById('uPassword').value;
  const status     = document.getElementById('uStatus').value;

  if (!first_name || !last_name || !email || !role) {
    Swal.fire({ icon:'warning', title:'Incomplete', text:'First name, last name, email and role are required.' }); return;
  }
  if (!id && password.length < 8) {
    Swal.fire({ icon:'warning', title:'Weak Password', text:'Password must be at least 8 characters.' }); return;
  }

  const btn = document.getElementById('saveUserBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

  const fd = new FormData();
  fd.append('action',     id ? 'update' : 'create');
  fd.append('id',         id);
  fd.append('first_name', first_name);
  fd.append('last_name',  last_name);
  fd.append('email',      email);
  fd.append('phone',      phone);
  fd.append('role',       role);
  fd.append('status',     status);
  if (!id) fd.append('password', password);

  fetch('ajax/ajax_admin_users.php', { method:'POST', body:fd })
    .then(r => r.json())
    .then(res => {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-check2 me-1"></i><span id="saveBtnText">Save</span>';
      if (res.status === 'success') {
        userModal.hide();
        Swal.fire({ icon:'success', title: id ? 'User Updated' : 'Account Created!', timer:1400, showConfirmButton:false, toast:true, position:'top-end' });
        loadUsers();
      } else {
        Swal.fire({ icon:'error', title:'Error', text:res.message });
      }
    });
}

/* ── Delete ── */
function deleteUser(id, name) {
  Swal.fire({
    icon:'warning', title:'Delete User?',
    html:`<b>${esc(name)}</b><br><small class="text-muted">This action cannot be undone.</small>`,
    showCancelButton:true, confirmButtonColor:'#dc2626', confirmButtonText:'Yes, delete'
  }).then(r => {
    if (!r.isConfirmed) return;
    const fd = new FormData();
    fd.append('action','delete'); fd.append('id', id);
    fetch('ajax/ajax_admin_users.php', { method:'POST', body:fd })
      .then(r => r.json())
      .then(res => {
        if (res.status === 'success') {
          Swal.fire({ icon:'success', title:'Deleted', timer:1200, showConfirmButton:false, toast:true, position:'top-end' });
          loadUsers();
        } else Swal.fire({ icon:'error', title:'Error', text:res.message });
      });
  });
}

/* ── Reset password ── */
function resetPassword(id, name) {
  Swal.fire({
    title: `Reset password`,
    html: `<div style="font-size:.875rem;color:#64748b;margin-bottom:.75rem">Setting new password for <b>${esc(name)}</b></div>`,
    input: 'password',
    inputLabel: 'New password (min. 8 characters)',
    inputAttributes: { minlength:8, autocomplete:'new-password', placeholder:'Enter new password' },
    showCancelButton:true,
    confirmButtonText:'Reset Password',
    confirmButtonColor:'#1a4fc4',
    preConfirm: pw => {
      if (!pw || pw.length < 8) { Swal.showValidationMessage('Minimum 8 characters required'); return false; }
      return pw;
    }
  }).then(r => {
    if (!r.isConfirmed) return;
    const fd = new FormData();
    fd.append('action','reset_password'); fd.append('id', id); fd.append('password', r.value);
    fetch('ajax/ajax_admin_users.php', { method:'POST', body:fd })
      .then(r => r.json())
      .then(res => {
        if (res.status === 'success')
          Swal.fire({ icon:'success', title:'Password Reset!', timer:1400, showConfirmButton:false, toast:true, position:'top-end' });
        else
          Swal.fire({ icon:'error', title:'Error', text:res.message });
      });
  });
}

/* ── Helpers ── */
function togglePwd() {
  const inp = document.getElementById('uPassword');
  const ico = document.getElementById('eyeIcon');
  if (inp.type === 'password') { inp.type='text';     ico.className='bi bi-eye-slash'; }
  else                         { inp.type='password'; ico.className='bi bi-eye';       }
}

function esc(s) {
  return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]);
}
function formatDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
}
</script>
