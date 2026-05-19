<?php
$user_role = $user_role ?? 0;
if ($user_role != 5) { include('403.php'); return; }

$roles   = $db->query("SELECT id, role_title FROM tbl_user_roles WHERE id != 5 ORDER BY id")->fetch_all(MYSQLI_ASSOC);
$modules = APP_MODULES;

// Auto-seed missing permissions as enabled for all non-student roles so new modules are accessible by default
foreach (array_keys($modules) as $mk) {
    foreach ($roles as $r) {
        if ($r['id'] == 1) continue; // students managed separately
        $db->query("INSERT IGNORE INTO tbl_module_permissions (module_key, role_id, is_enabled) VALUES ('" . $db->escape_string($mk) . "', {$r['id']}, 1)");
    }
}
// Seed student_exams for students (role 1) — they need access to their own exam portal
$db->query("INSERT IGNORE INTO tbl_module_permissions (module_key, role_id, is_enabled) VALUES ('student_exams', 1, 1)");

// Module groupings
$groups = [
    'Platform'                  => ['course_management', 'admin_courses'],
    'Question Bank — Core'      => ['qb_taxonomy', 'qb_questions', 'qb_exams', 'qb_curriculum'],
    'Question Bank — Analytics' => ['qb_analytics', 'qb_import_export'],
    'Question Bank — AI & Tools'=> ['qb_ai_tools', 'qb_settings'],
    'Student Centre'            => ['student_exams'],
];

// Role UI metadata
$role_meta = [
    1 => ['color' => '#3b82f6', 'bg' => '#eff6ff', 'icon' => 'bi-mortarboard-fill'],
    2 => ['color' => '#8b5cf6', 'bg' => '#f5f3ff', 'icon' => 'bi-people-fill'],
    3 => ['color' => '#f97316', 'bg' => '#fff7ed', 'icon' => 'bi-person-video3'],
    4 => ['color' => '#10b981', 'bg' => '#f0fdf4', 'icon' => 'bi-building'],
];

// Build permission matrix
$matrix = [];
foreach ($modules as $mk => $_) {
    foreach ($roles as $r) $matrix[$mk][$r['id']] = 0;
}
$res = $db->query("SELECT module_key, role_id, is_enabled FROM tbl_module_permissions");
while ($row = $res->fetch_assoc()) {
    if (isset($matrix[$row['module_key']][$row['role_id']])) {
        $matrix[$row['module_key']][$row['role_id']] = (int)$row['is_enabled'];
    }
}

// Count enabled per role
$role_counts = [];
foreach ($roles as $r) {
    $role_counts[$r['id']] = array_sum(array_column(array_map(fn($mk) => ['v' => $matrix[$mk][$r['id']] ?? 0], array_keys($modules)), 'v'));
}
$total_modules = count($modules);
?>

<style>
:root {
  --pm-radius: 14px;
  --pm-border: #e8edf3;
  --pm-text: #0f172a;
  --pm-muted: #64748b;
  --pm-font: 'Inter', sans-serif;
}

/* ── Layout ── */
#permPage { font-family: var(--pm-font); }

/* ── Role summary cards ── */
.role-card {
  background: #fff;
  border: 1.5px solid var(--pm-border);
  border-radius: var(--pm-radius);
  padding: 1.1rem 1.25rem;
  position: relative;
  overflow: hidden;
  transition: box-shadow .2s, border-color .2s;
}
.role-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.08); }
.role-card::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0; height: 3px;
  background: var(--rc-color);
}
.role-icon-wrap {
  width: 40px; height: 40px; border-radius: 11px;
  background: var(--rc-bg);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem; color: var(--rc-color);
  flex-shrink: 0;
}
.role-progress { height: 5px; border-radius: 4px; background: #f1f5f9; margin-top: .6rem; }
.role-progress-bar { height: 100%; border-radius: 4px; background: var(--rc-color); transition: width .6s cubic-bezier(.16,1,.3,1); }
.role-bulk-btns { display: flex; gap: .3rem; margin-top: .7rem; }
.role-bulk-btn {
  flex: 1; border: 1.5px solid var(--pm-border); border-radius: 8px;
  background: #fff; color: var(--pm-muted); font-size: .7rem; font-weight: 600;
  padding: .25rem .4rem; cursor: pointer; transition: all .15s;
  display: flex; align-items: center; justify-content: center; gap: .25rem;
}
.role-bulk-btn.grant:hover { border-color: #22c55e; color: #15803d; background: #f0fdf4; }
.role-bulk-btn.revoke:hover { border-color: #ef4444; color: #b91c1c; background: #fef2f2; }

/* ── Filter bar ── */
.filter-bar { background: #fff; border: 1.5px solid var(--pm-border); border-radius: var(--pm-radius); padding: .65rem 1rem; }
.filter-bar input {
  border: none; outline: none; background: transparent;
  font-size: .875rem; color: var(--pm-text); width: 100%;
  font-family: var(--pm-font);
}

/* ── Matrix card ── */
.matrix-card {
  background: #fff; border: 1.5px solid var(--pm-border);
  border-radius: var(--pm-radius);
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 8px 32px rgba(0,0,0,.05);
}

/* ── Table ── */
.pm-table { width: 100%; border-collapse: collapse; }
.pm-table th, .pm-table td { border-bottom: 1px solid var(--pm-border); }
.pm-table tr:last-child td { border-bottom: none; }

/* Column header */
.col-header {
  padding: .85rem 1rem; text-align: center; background: #fff;
  position: sticky; top: 0; z-index: 2;
  border-bottom: 2px solid var(--pm-border) !important;
}
.col-header-inner {
  display: flex; flex-direction: column; align-items: center; gap: .3rem;
}
.col-role-icon {
  width: 34px; height: 34px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: .95rem; font-weight: 700;
  background: var(--rc-bg); color: var(--rc-color);
  margin: 0 auto .2rem;
}
.col-role-name { font-size: .78rem; font-weight: 700; color: var(--pm-text); }
.col-role-sub  { font-size: .65rem; color: var(--pm-muted); }

/* Module column */
.mod-col { min-width: 220px; padding: .8rem 1.25rem; background: #fff; }
.mod-col-inner { display: flex; align-items: center; gap: .75rem; }
.mod-icon-wrap {
  width: 36px; height: 36px; border-radius: 10px;
  background: #f1f5f9;
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem; color: #3b82f6; flex-shrink: 0;
  transition: background .2s;
}
.pm-table tr:hover .mod-icon-wrap { background: #dbeafe; }
.mod-name { font-size: .83rem; font-weight: 600; color: var(--pm-text); }
.mod-key  { font-size: .65rem; color: var(--pm-muted); font-family: 'SF Mono', 'Fira Code', monospace; margin-top: .1rem; }

/* Perm cell */
.perm-cell { text-align: center; padding: .7rem .5rem; min-width: 110px; }

/* Group header row */
.group-row td {
  padding: .45rem 1.25rem;
  background: linear-gradient(90deg, #f8fafc, #fff);
  border-bottom: 1px solid var(--pm-border) !important;
}
.group-label {
  font-size: .68rem; font-weight: 800; letter-spacing: .08em;
  text-transform: uppercase; color: var(--pm-muted);
  display: flex; align-items: center; gap: .5rem;
}
.group-label::after { content: ''; flex: 1; height: 1px; background: var(--pm-border); }

/* Row hover */
.pm-table tbody tr.mod-row { transition: background .12s; }
.pm-table tbody tr.mod-row:hover { background: #f8fbff; }

/* ── Custom toggle switch ── */
.pm-toggle-wrap { display: flex; align-items: center; justify-content: center; }
.pm-toggle {
  position: relative; width: 48px; height: 26px; cursor: pointer;
  display: inline-flex; align-items: center;
}
.pm-toggle input { opacity: 0; width: 0; height: 0; position: absolute; }
.pm-track {
  width: 48px; height: 26px; border-radius: 100px;
  background: #e2e8f0; transition: background .25s;
  display: flex; align-items: center; padding: 0 3px;
  position: relative;
}
.pm-toggle input:checked ~ .pm-track { background: #22c55e; }
.pm-toggle.saving .pm-track { background: #f59e0b; }

.pm-thumb {
  width: 20px; height: 20px; border-radius: 50%;
  background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.18);
  transition: transform .25s cubic-bezier(.16,1,.3,1);
  display: flex; align-items: center; justify-content: center;
  font-size: .6rem; color: transparent;
  flex-shrink: 0;
}
.pm-toggle input:checked ~ .pm-track .pm-thumb { transform: translateX(22px); color: #22c55e; }
.pm-toggle.saving .pm-track .pm-thumb { color: #f59e0b; }

.pm-thumb-icon { transition: opacity .2s; }
.pm-thumb-check { position: absolute; font-size: .55rem; }

/* Saving ring on toggle */
@keyframes spin { to { transform: rotate(360deg); } }
.pm-toggle.saving .pm-track::after {
  content: ''; position: absolute; inset: -2px; border-radius: 100px;
  border: 2px solid #f59e0b; border-top-color: transparent;
  animation: spin .6s linear infinite;
}

/* ── Save notification ── */
#saveNotif {
  position: fixed; bottom: 1.5rem; right: 1.5rem;
  background: #0f172a; color: #fff;
  border-radius: 12px; padding: .75rem 1.25rem;
  display: flex; align-items: center; gap: .6rem;
  font-size: .83rem; font-weight: 600;
  box-shadow: 0 8px 32px rgba(0,0,0,.25);
  transform: translateY(80px); opacity: 0;
  transition: transform .3s cubic-bezier(.16,1,.3,1), opacity .3s;
  pointer-events: none; z-index: 9999;
}
#saveNotif.show { transform: translateY(0); opacity: 1; }
#saveNotif .notif-dot { width: 8px; height: 8px; border-radius: 50%; background: #22c55e; flex-shrink:0; }
#saveNotif.error .notif-dot { background: #ef4444; }

/* Hidden row for filter */
.pm-hidden { display: none; }

/* Fade in */
@keyframes fadeUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
.role-card { animation: fadeUp .35s cubic-bezier(.16,1,.3,1) both; }
.role-card:nth-child(2){animation-delay:.05s}
.role-card:nth-child(3){animation-delay:.1s}
.role-card:nth-child(4){animation-delay:.15s}
</style>

<div id="permPage" class="container-fluid px-3 py-3">

  <!-- ── Header ──────────────────────────────────────────── -->
  <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <div style="width:38px;height:38px;border-radius:11px;background:linear-gradient(135deg,#1a4fc4,#6d28d9);display:flex;align-items:center;justify-content:center">
          <i class="bi bi-toggles text-white fs-5"></i>
        </div>
        <h5 class="fw-bold mb-0">Module Permissions</h5>
      </div>
      <p class="text-muted small mb-0 ms-1">
        Control which roles can access each module. Toggles save instantly — no submit needed.
      </p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="badge rounded-pill" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;font-size:.75rem;padding:.4rem .85rem">
        <i class="bi bi-shield-fill-check me-1"></i>Super Admin — Full Access
      </span>
    </div>
  </div>

  <!-- ── Role summary cards ───────────────────────────────── -->
  <div class="row g-3 mb-4">
    <?php foreach ($roles as $r):
      $rm     = $role_meta[$r['id']] ?? ['color'=>'#64748b','bg'=>'#f8fafc','icon'=>'bi-person-fill'];
      $count  = $role_counts[$r['id']] ?? 0;
      $pct    = $total_modules ? round($count / $total_modules * 100) : 0;
    ?>
    <div class="col-6 col-xl-3">
      <div class="role-card" style="--rc-color:<?= $rm['color'] ?>;--rc-bg:<?= $rm['bg'] ?>">
        <div class="d-flex align-items-center gap-2 mb-2">
          <div class="role-icon-wrap"><i class="bi <?= $rm['icon'] ?>"></i></div>
          <div>
            <div class="fw-bold small" style="color:var(--pm-text)"><?= htmlspecialchars($r['role_title']) ?></div>
            <div style="font-size:.7rem;color:var(--pm-muted)"><?= $count ?>/<?= $total_modules ?> modules</div>
          </div>
          <div class="ms-auto fw-bold" style="font-size:1.15rem;color:<?= $rm['color'] ?>"><?= $pct ?>%</div>
        </div>
        <div class="role-progress">
          <div class="role-progress-bar" style="width:<?= $pct ?>%" data-role="<?= $r['id'] ?>" id="bar_<?= $r['id'] ?>"></div>
        </div>
        <div class="role-bulk-btns">
          <button class="role-bulk-btn grant" onclick="bulkToggle(<?= $r['id'] ?>, 1)">
            <i class="bi bi-check2-all"></i> Grant All
          </button>
          <button class="role-bulk-btn revoke" onclick="bulkToggle(<?= $r['id'] ?>, 0)">
            <i class="bi bi-x-circle"></i> Revoke All
          </button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ── Filter ───────────────────────────────────────────── -->
  <div class="filter-bar d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-search text-muted flex-shrink-0"></i>
    <input type="text" id="moduleFilter" placeholder="Filter modules…" autocomplete="off">
    <span class="text-muted small flex-shrink-0" id="filterCount"><?= $total_modules ?> modules</span>
  </div>

  <!-- ── Permission matrix ────────────────────────────────── -->
  <div class="matrix-card">
    <div class="table-responsive">
      <table class="pm-table" id="permMatrix">
        <thead>
          <tr>
            <th class="col-header text-start" style="min-width:230px">
              <div style="font-size:.72rem;font-weight:700;color:var(--pm-muted);letter-spacing:.04em;text-transform:uppercase;padding:.1rem 0">Module</div>
            </th>
            <?php foreach ($roles as $r):
              $rm = $role_meta[$r['id']] ?? ['color'=>'#64748b','bg'=>'#f8fafc','icon'=>'bi-person-fill'];
            ?>
            <th class="col-header" style="--rc-color:<?= $rm['color'] ?>;--rc-bg:<?= $rm['bg'] ?>">
              <div class="col-header-inner">
                <div class="col-role-icon"><i class="bi <?= $rm['icon'] ?>"></i></div>
                <div class="col-role-name"><?= htmlspecialchars(explode(' ', $r['role_title'])[0]) ?></div>
                <div class="col-role-sub" id="col_count_<?= $r['id'] ?>"><?= $role_counts[$r['id']] ?> on</div>
              </div>
            </th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php
          // Collect any ungrouped modules
          $grouped_keys = array_merge(...array_values($groups));
          $all_keys = array_keys($modules);
          $ungrouped = array_diff($all_keys, $grouped_keys);
          if ($ungrouped) $groups['Other'] = array_values($ungrouped);

          foreach ($groups as $group_label => $group_keys):
            // Check at least one key in this group exists in modules
            $valid_keys = array_filter($group_keys, fn($k) => isset($modules[$k]));
            if (!$valid_keys) continue;
          ?>
          <!-- Group header -->
          <tr class="group-row" data-group="<?= htmlspecialchars($group_label) ?>">
            <td colspan="<?= count($roles) + 1 ?>">
              <div class="group-label"><?= htmlspecialchars($group_label) ?></div>
            </td>
          </tr>

          <?php foreach ($valid_keys as $mk):
            $mod = $modules[$mk];
          ?>
          <tr class="mod-row" data-module="<?= $mk ?>" data-label="<?= strtolower($mod['label']) ?>">
            <td class="mod-col">
              <div class="mod-col-inner">
                <div class="mod-icon-wrap">
                  <i class="bi <?= $mod['icon'] ?>"></i>
                </div>
                <div>
                  <div class="mod-name"><?= htmlspecialchars($mod['label']) ?></div>
                  <div class="mod-key"><?= $mk ?></div>
                </div>
              </div>
            </td>
            <?php foreach ($roles as $r):
              $enabled = $matrix[$mk][$r['id']] ?? 0;
              $rm = $role_meta[$r['id']] ?? ['color'=>'#64748b','bg'=>'#f8fafc','icon'=>'bi-person-fill'];
            ?>
            <td class="perm-cell">
              <div class="pm-toggle-wrap">
                <label class="pm-toggle" id="wrap_<?= $mk ?>_<?= $r['id'] ?>">
                  <input type="checkbox"
                    id="perm_<?= $mk ?>_<?= $r['id'] ?>"
                    data-module="<?= $mk ?>"
                    data-role="<?= $r['id'] ?>"
                    data-color="<?= $rm['color'] ?>"
                    <?= $enabled ? 'checked' : '' ?>
                    onchange="togglePerm(this)">
                  <div class="pm-track">
                    <div class="pm-thumb">
                      <i class="bi bi-check2 pm-thumb-check" style="opacity:<?= $enabled ? 1 : 0 ?>"></i>
                    </div>
                  </div>
                </label>
              </div>
            </td>
            <?php endforeach; ?>
          </tr>
          <?php endforeach; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Footer -->
    <div style="padding:.75rem 1.25rem;background:#f8fafc;border-top:1px solid var(--pm-border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem">
      <div style="font-size:.75rem;color:var(--pm-muted)">
        <i class="bi bi-info-circle me-1"></i>
        Super Admin (Role 5) bypasses all permission checks and always has full access.
      </div>
      <div style="font-size:.72rem;color:var(--pm-muted)">
        <?= $total_modules ?> modules · <?= count($roles) ?> roles
      </div>
    </div>
  </div>

</div><!-- #permPage -->

<!-- ── Save notification ── -->
<div id="saveNotif">
  <div class="notif-dot"></div>
  <span id="saveNotifText">Permission saved</span>
</div>

<script>
const AJAX_URL = 'ajax/ajax_admin_permissions.php';
let notifTimer;

/* ── Toggle one permission ── */
function togglePerm(input) {
  const module  = input.dataset.module;
  const role_id = input.dataset.role;
  const enabled = input.checked ? 1 : 0;
  const $wrap   = document.getElementById(`wrap_${module}_${role_id}`);

  $wrap.classList.add('saving');
  input.disabled = true;

  const fd = new FormData();
  fd.append('action','toggle');
  fd.append('module_key', module);
  fd.append('role_id', role_id);
  fd.append('is_enabled', enabled);

  fetch(AJAX_URL, { method:'POST', body:fd })
    .then(r => r.json())
    .then(res => {
      $wrap.classList.remove('saving');
      input.disabled = false;

      if (res.status === 'success') {
        // update thumb icon
        const thumb = $wrap.querySelector('.pm-thumb-check');
        if (thumb) thumb.style.opacity = enabled ? '1' : '0';
        updateRoleSummary(role_id);
        showNotif('Permission saved', false);
      } else {
        input.checked = !input.checked;
        showNotif('Save failed — ' + (res.message||'error'), true);
      }
    })
    .catch(() => {
      $wrap.classList.remove('saving');
      input.disabled = false;
      input.checked = !input.checked;
      showNotif('Connection error', true);
    });
}

/* ── Bulk toggle entire role ── */
async function bulkToggle(role_id, value) {
  const label = value ? 'Grant ALL modules' : 'Revoke ALL modules';
  const confirm = await Swal.fire({
    icon: value ? 'question' : 'warning',
    title: label,
    html: `This will ${value ? 'enable' : 'disable'} every module for this role.<br>Continue?`,
    showCancelButton: true,
    confirmButtonColor: value ? '#22c55e' : '#ef4444',
    confirmButtonText: value ? 'Yes, grant all' : 'Yes, revoke all'
  });
  if (!confirm.isConfirmed) return;

  // Collect visible checkboxes for this role
  const inputs = [...document.querySelectorAll(`input[data-role="${role_id}"]`)]
    .filter(inp => inp.closest('tr').style.display !== 'none');

  let done = 0;
  for (const inp of inputs) {
    if (parseInt(inp.checked ? 1 : 0) === value) { done++; continue; }
    inp.checked = !!value;
    await new Promise(resolve => {
      const fd = new FormData();
      fd.append('action','toggle');
      fd.append('module_key', inp.dataset.module);
      fd.append('role_id', role_id);
      fd.append('is_enabled', value);
      const wrap = document.getElementById(`wrap_${inp.dataset.module}_${role_id}`);
      if (wrap) wrap.classList.add('saving');
      fetch(AJAX_URL, {method:'POST', body:fd})
        .then(r=>r.json())
        .then(()=>{ if(wrap) wrap.classList.remove('saving'); resolve(); })
        .catch(()=>{ if(wrap) wrap.classList.remove('saving'); resolve(); });
    });
    done++;
  }
  updateRoleSummary(role_id);
  showNotif(`${done} permissions ${value ? 'granted' : 'revoked'}`, false);
}

/* ── Update role summary card ── */
function updateRoleSummary(role_id) {
  const all    = [...document.querySelectorAll(`input[data-role="${role_id}"]`)];
  const on     = all.filter(i => i.checked).length;
  const total  = all.length;
  const pct    = total ? Math.round(on/total*100) : 0;

  // progress bar
  const bar = document.getElementById('bar_' + role_id);
  if (bar) bar.style.width = pct + '%';

  // column count
  const colCount = document.getElementById('col_count_' + role_id);
  if (colCount) colCount.textContent = on + ' on';

  // card count label (find by role id in role-card)
  document.querySelectorAll('.role-card').forEach(card => {
    const btn = card.querySelector(`[onclick="bulkToggle(${role_id}, 1)"]`);
    if (!btn) return;
    const label = card.querySelector('[style*="color:var(--pm-muted)"]');
    if (label) label.textContent = `${on}/${total} modules`;
    const pctEl = card.querySelector('[style*="font-size:1.15rem"]');
    if (pctEl) pctEl.textContent = pct + '%';
  });
}

/* ── Module filter ── */
document.getElementById('moduleFilter').addEventListener('input', function(){
  const q = this.value.trim().toLowerCase();
  let visible = 0;

  document.querySelectorAll('.mod-row').forEach(row => {
    const label = row.dataset.label || '';
    const key   = row.dataset.module || '';
    const match = !q || label.includes(q) || key.includes(q);
    row.style.display = match ? '' : 'none';
    if (match) visible++;
  });

  // Show/hide group headers: hide if all rows in group are hidden
  document.querySelectorAll('.group-row').forEach(groupRow => {
    const groupName = groupRow.dataset.group;
    // find next sibling mod-rows until next group-row
    let next = groupRow.nextElementSibling;
    let anyVisible = false;
    while (next && !next.classList.contains('group-row')) {
      if (next.style.display !== 'none') anyVisible = true;
      next = next.nextElementSibling;
    }
    groupRow.style.display = anyVisible ? '' : 'none';
  });

  document.getElementById('filterCount').textContent = visible + ' module' + (visible !== 1 ? 's' : '');
});

/* ── Notification ── */
function showNotif(msg, isError) {
  const el = document.getElementById('saveNotif');
  const txt = document.getElementById('saveNotifText');
  txt.textContent = msg;
  el.classList.toggle('error', !!isError);
  el.classList.add('show');
  clearTimeout(notifTimer);
  notifTimer = setTimeout(() => el.classList.remove('show'), 2200);
}
</script>
