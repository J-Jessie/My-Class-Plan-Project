<?php
require_once __DIR__.'/config.php';

function page_header($title='MyClassPlan'){
  $user = $_SESSION['username'] ?? null; $role = $_SESSION['role'] ?? null;
  ?>
  <!DOCTYPE html><html lang="en"><head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?></title>
  <style>
    :root{--primary:#4361ee;--secondary:#3a0ca3;--danger:#f72585;--light:#f5f7fb;--dark:#212529}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;background:var(--light)}
    header{background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;padding:12px 16px}
    nav a{color:#fff;text-decoration:none;margin-right:16px}
    .container{max-width:1100px;margin:16px auto;padding:0 12px}
    .card{background:#fff;border-radius:10px;box-shadow:0 4px 10px rgba(0,0,0,.05);padding:16px;margin-bottom:16px}
    .btn{display:inline-block;background:var(--primary);color:#fff;border:none;padding:8px 12px;border-radius:6px;cursor:pointer;text-decoration:none}
    table{width:100%;border-collapse:collapse}
    th,td{padding:8px;border:1px solid #eee;text-align:left}
    .grid{display:grid;gap:8px}
    input, select {
  border:1px solid #ccc;
  border-radius:6px;
  padding:10px;
  font-size:15px;
}
input:focus, select:focus {
  border-color: var(--primary);
  outline:none;
  box-shadow:0 0 5px rgba(67,97,238,.3);
}
  </style></head><body>
  <header><div class="container">
    <strong>MyClassPlan</strong>
    <nav style="float:right">
      <?php if($user): ?>
        <a href="/dashboards/<?= role_slug($role) ?>_dashboard.php">Dashboard</a>
        <a href="/pages/timetable.php">Timetable</a>
        <a href="/pages/exams.php">Exams</a>
        <?php if(in_array($role,[ROLE_ADMIN,ROLE_TIMETABLER])): ?><a href="/pages/management.php">Management</a><?php endif; ?>
        <a href="/pages/notifications.php">Notifications</a>
        <a href="/logout.php">Logout</a>
      <?php else: ?>
        <a href="/login.php">Login</a>
        <a href="/signup.php">Sign up</a>
      <?php endif; ?>
    </nav>
  </div></header>
  <div class="container">
  <?php
}

function page_footer(){ echo "</div><footer style='text-align:center;color:#999;padding:24px'>&copy; ".date('Y')." MyClassPlan</footer></body></html>"; }
function role_slug($r){ return $r==ROLE_ADMIN?'admin':($r==ROLE_STUDENT?'student':($r==ROLE_LECTURER?'lecturer':'timetabler')); }

function render_timetable_grid($conn,$table='timetable',$filter=[]){
  $slots=slots_def();
  echo "<div class='card'><h3>".($table==='timetable'?'Published Timetable':'Draft Timetable')."</h3>";
  echo "<div class='grid' style='grid-template-columns:120px repeat(3,1fr)'>";
  echo "<div></div>"; foreach([1,2,3] as $s){ echo "<div style='background:#e6ecff;padding:8px;border-radius:6px;text-align:center'>".substr($slots[$s][0],0,5)."–".substr($slots[$s][1],0,5)."</div>"; }
  for($d=1;$d<=5;$d++){
    echo "<div style='background:#f0f0f0;padding:8px;border-radius:6px;font-weight:600'>".format_day($d)."</div>";
    foreach([1,2,3] as $s){
      $sql="SELECT t.*, c.course_code, c.course_name, r.room_code FROM $table t JOIN courses c ON c.id=t.course_id JOIN rooms r ON r.id=t.room_id WHERE day_of_week=? AND slot=?";
      $params=[$d,$s];
      if(isset($filter['lecturer_id'])){ $sql.=" AND c.lecturer_id=?"; $params[]=$filter['lecturer_id']; }
      if(isset($filter['student_id'])){
        $sql.=" AND EXISTS(SELECT 1 FROM enrollments e WHERE e.course_id=c.id AND e.student_id=?)"; $params[]=$filter['student_id'];
      }
      $st=$conn->prepare($sql);
      $types=str_repeat('i',count($params)); $st->bind_param($types, ...$params); $st->execute(); $res=$st->get_result();
      echo "<div style='background:#fff;border:1px solid #eee;border-radius:6px;padding:8px;min-height:84px'>";
      if($res->num_rows==0) echo "<em>Free</em>";
      while($row=$res->fetch_assoc()){
        echo "<div style='background:#fafcff;border:1px dashed #dbe0ff;border-radius:6px;padding:6px;margin-bottom:6px'>";
        echo "<strong>{$row['course_code']}</strong> — ".htmlspecialchars($row['course_name'])."<br><small>Room: ".htmlspecialchars($row['room_code'])."</small>";
        echo "</div>";
      }
      echo "</div>";
    }
  }
  echo "</div></div>";
}