<?php
require_once __DIR__.'/../includes/auth.php';
require_once __DIR__.'/../includes/ui.php';
require_once __DIR__.'/../includes/scheduler.php';
require_login(); if(!has_role([ROLE_ADMIN])){ header('Location:/'); exit; }
$conn=db_connect();

// Stats
$totals=[
  'users'=>$conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'],
  'students'=>$conn->query("SELECT COUNT(*) c FROM users WHERE role=".ROLE_STUDENT)->fetch_assoc()['c'],
  'lecturers'=>$conn->query("SELECT COUNT(*) c FROM users WHERE role=".ROLE_LECTURER)->fetch_assoc()['c'],
  'courses'=>$conn->query("SELECT COUNT(*) c FROM courses")->fetch_assoc()['c'],
  'rooms'=>$conn->query("SELECT COUNT(*) c FROM rooms")->fetch_assoc()['c'],
];

page_header('Admin Dashboard');
?>
<div class="card"><h2>Admin Dashboard</h2>
  <div class="grid" style="grid-template-columns:repeat(5,1fr)">
    <?php foreach($totals as $k=>$v): ?><div class="card"><strong><?= ucfirst($k) ?></strong><div style="font-size:22px"><?= (int)$v ?></div></div><?php endforeach; ?>
  </div>
</div>
<div class="card">
  <h3>Published Timetable</h3>
  <?php render_timetable_grid($conn,'timetable'); ?>
</div>
<?php page_footer();