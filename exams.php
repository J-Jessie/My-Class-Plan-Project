<?php
require_once __DIR__.'/../includes/auth.php';
require_once __DIR__.'/../includes/ui.php';
require_login(); $conn=db_connect(); $uid=current_user_id();
page_header('Exams');
$sql="SELECT e.*, c.course_code, c.course_name, r.room_code FROM exams e JOIN courses c ON c.id=e.course_id JOIN rooms r ON r.id=e.room_id";
if(has_role([ROLE_STUDENT])) $sql.=" WHERE EXISTS(SELECT 1 FROM enrollments en WHERE en.course_id=c.id AND en.student_id=$uid)";
if(has_role([ROLE_LECTURER])) $sql.=" WHERE c.lecturer_id=$uid";
$sql.=" ORDER BY exam_date,start_time";
$res=$conn->query($sql);
?>
<div class="card"><h2>Exam Schedule</h2>
  <table><tr><th>Date</th><th>Time</th><th>Course</th><th>Room</th></tr>
  <?php while($row=$res->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($row['exam_date']) ?></td>
      <td><?= substr($row['start_time'],0,5) ?>–<?= substr($row['end_time'],0,5) ?></td>
      <td><?= htmlspecialchars($row['course_code'].' — '.$row['course_name']) ?></td>
      <td><?= htmlspecialchars($row['room_code']) ?></td>
    </tr>
  <?php endwhile; ?>
  </table>
</div>
<?php page_footer();