<?php
if ($user_role != 5) { include('403.php'); return; }

$stats = $db->query("
    SELECT
        COUNT(*)                                                AS total,
        SUM(user_role = 1)                                     AS students,
        SUM(user_role = 2)                                     AS parents,
        SUM(user_role = 3)                                     AS instructors,
        SUM(user_role = 4)                                     AS schools,
        SUM(user_status = 'Active')                            AS active,
        SUM(user_status = 'Inactive')                          AS inactive
    FROM tbl_all_users
")->fetch_assoc();

$total_courses  = $db->query("SELECT COUNT(*) AS c FROM tbl_courses WHERE deleted_at IS NULL")->fetch_assoc()['c'];
$total_questions = $db->query("SELECT COUNT(*) AS c FROM qb_questions")->fetch_assoc()['c'];

$recent = $db->query("
    SELECT u.first_name, u.last_name, u.email_address, u.user_role, u.user_status, u.created_at,
           r.role_title
    FROM tbl_all_users u
    LEFT JOIN tbl_user_roles r ON r.id = u.user_role
    ORDER BY u.created_at DESC LIMIT 10
")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-fluid px-3 py-3">

  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <h5 class="fw-semibold mb-1"><i class="bi bi-shield-lock-fill text-primary me-2"></i>Super Admin</h5>
      <small class="text-muted">System-wide overview and management console</small>
    </div>
    <a href="?view=admin_users" class="btn btn-primary btn-sm">
      <i class="bi bi-person-plus me-1"></i>Add User
    </a>
  </div>

  <!-- Stat cards -->
  <div class="row g-3 mb-4">
    <?php
    $cards = [
      ['icon'=>'bi-people-fill',       'color'=>'primary', 'val'=>$stats['total'],       'lbl'=>'Total Users'],
      ['icon'=>'bi-mortarboard-fill',   'color'=>'info',    'val'=>$stats['students'],    'lbl'=>'Students'],
      ['icon'=>'bi-person-workspace',   'color'=>'success', 'val'=>$stats['instructors'], 'lbl'=>'Instructors'],
      ['icon'=>'bi-bank',               'color'=>'warning', 'val'=>$stats['schools'],     'lbl'=>'Schools'],
      ['icon'=>'bi-person-vcard',       'color'=>'secondary','val'=>$stats['parents'],    'lbl'=>'Parents'],
      ['icon'=>'bi-check-circle-fill',  'color'=>'success', 'val'=>$stats['active'],      'lbl'=>'Active Users'],
      ['icon'=>'bi-collection-play-fill','color'=>'primary', 'val'=>$total_courses,       'lbl'=>'Courses'],
      ['icon'=>'bi-patch-question-fill', 'color'=>'info',   'val'=>$total_questions,      'lbl'=>'QB Questions'],
    ];
    foreach ($cards as $c): ?>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body d-flex align-items-center gap-3 py-3">
          <div class="rounded-circle bg-<?= $c['color'] ?> bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px">
            <i class="bi <?= $c['icon'] ?> text-<?= $c['color'] ?> fs-5"></i>
          </div>
          <div>
            <div class="h4 fw-bold mb-0"><?= number_format((int)$c['val']) ?></div>
            <small class="text-muted"><?= $c['lbl'] ?></small>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Quick links + Recent users -->
  <div class="row g-3">

    <!-- Quick links -->
    <div class="col-12 col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-transparent fw-semibold py-2 small text-uppercase text-muted">Quick Actions</div>
        <div class="list-group list-group-flush">
          <?php $links = [
            ['?view=admin_users',       'bi-people',           'Manage Users',       'Roles, status, passwords'],
            ['?view=admin_roles',        'bi-layers',           'Manage Roles',       'Create or rename roles'],
            ['?view=admin_permissions',  'bi-toggle-on',        'Module Permissions', 'Control which roles see what'],
            ['?view=qb_all_questions',   'bi-patch-question',   'Question Bank',      'View all QB questions'],
            ['?view=my_courses_online_contents_list_view','bi-collection-play','Courses','All instructor courses'],
          ];
          foreach ($links as [$href,$icon,$title,$sub]): ?>
          <a href="<?= $href ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2 px-3">
            <i class="bi <?= $icon ?> text-primary fs-5 flex-shrink-0"></i>
            <div>
              <div class="fw-medium small"><?= $title ?></div>
              <div class="text-muted" style="font-size:.74rem"><?= $sub ?></div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Recent registrations -->
    <div class="col-12 col-md-8">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-2">
          <span class="fw-semibold small text-uppercase text-muted">Recent Registrations</span>
          <a href="?view=admin_users" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
              <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th></tr>
            </thead>
            <tbody>
              <?php foreach ($recent as $u):
                $sc = match($u['user_role']) {'1'=>'info','3'=>'success','4'=>'warning','5'=>'danger',default=>'secondary'};
              ?>
              <tr>
                <td class="fw-medium"><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></td>
                <td class="text-muted"><?= htmlspecialchars($u['email_address']) ?></td>
                <td><span class="badge bg-<?= $sc ?> bg-opacity-85 text-white"><?= htmlspecialchars($u['role_title'] ?? '—') ?></span></td>
                <td>
                  <span class="badge bg-<?= $u['user_status']==='Active'?'success':'danger' ?> bg-opacity-10 text-<?= $u['user_status']==='Active'?'success':'danger' ?> border border-<?= $u['user_status']==='Active'?'success':'danger' ?> border-opacity-25">
                    <?= $u['user_status'] ?>
                  </span>
                </td>
                <td class="text-muted"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if (!$recent): ?>
              <tr><td colspan="5" class="text-center text-muted py-4">No users yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>
