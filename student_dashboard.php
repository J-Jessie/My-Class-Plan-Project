<?php
require_once __DIR__.'/../includes/auth.php';
require_once __DIR__.'/../includes/ui.php';
require_login(); if(!has_role([ROLE_STUDENT,ROLE_ADMIN])){ header('Location:/'); exit; }
$conn=db_connect(); $uid=current_user_id();
page_header('Student Dashboard');
?>
<div class="card"><h2>Student Dashboard</h2></div>
<div class="card">
  <h3>Your Timetable</h3>
  <?php render_timetable_grid($conn,'timetable',[ 'student_id'=>$uid ]); ?>
</div>
<div class="card">
  <h3>Your Enrollments</h3>
  <table><tr><th>Course</th><th>Lecturer</th></tr>
  <?php
  $res=$conn->query("SELECT c.course_code,c.course_name,u.username AS lect FROM enrollments e JOIN courses c ON c.id=e.course_id LEFT JOIN users u ON u.id=c.lecturer_id WHERE e.student_id=$uid ORDER BY c.course_code");
  while($r=$res->fetch_assoc()){ echo '<tr><td>'.htmlspecialchars($r['course_code'].' — '.$r['course_name']).'</td><td>'.htmlspecialchars($r['lect']??'-').'</td></tr>'; }
  ?>
  </table>
</div>
<?php page_footer();