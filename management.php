<?php
require_once __DIR__.'/../includes/auth.php';
require_once __DIR__.'/../includes/ui.php';
require_once __DIR__.'/../includes/scheduler.php';
require_login(); if(!has_role([ROLE_ADMIN,ROLE_TIMETABLER])){ header('Location:/'); exit; }
$conn=db_connect();

if(isset($_POST['add_course'])){
  $st=$conn->prepare("INSERT INTO courses(course_code,course_name,lecturer_id,credits) VALUES (?,?,?,?)");
  $st->bind_param('ssii',$_POST['course_code'],$_POST['course_name'],$_POST['lecturer_id'],$_POST['credits']); $st->execute();
}
if(isset($_POST['add_room'])){ $st=$conn->prepare("INSERT INTO rooms(room_code,capacity) VALUES (?,?)"); $st->bind_param('si',$_POST['room_code'],$_POST['capacity']); $st->execute(); }
if(isset($_POST['enroll'])){ $st=$conn->prepare("INSERT IGNORE INTO enrollments(course_id,student_id) VALUES (?,?)"); $st->bind_param('ii',$_POST['course_id'],$_POST['student_id']); $st->execute(); }
if(isset($_POST['run_scheduler'])) propose_schedule($conn);
if(isset($_POST['publish'])) publish_draft($conn);
if(isset($_POST['clear_draft'])) clear_draft($conn);

page_header('Management');
?>
<div class="card"><h2>Management</h2></div>
<div class="card">
  <h3>Add Course</h3>
  <form method="POST" class="grid" style="grid-template-columns:repeat(5,1fr)">
    <input name="course_code" placeholder="Code" required>
    <input name="course_name" placeholder="Name" required>
    <select name="lecturer_id" required><?php $ls=$conn->query("SELECT id,username FROM users WHERE role=".ROLE_LECTURER." ORDER BY username"); while($l=$ls->fetch_assoc()) echo '<option value="'.$l['id'].'">'.htmlspecialchars($l['username']).'</option>'; ?></select>
    <input type="number" name="credits" value="3">
    <button class="btn" name="add_course">Add</button>
  </form>
</div>
<div class="card">
  <h3>Add Room</h3>
  <form method="POST" class="grid" style="grid-template-columns:repeat(3,1fr)">
    <input name="room_code" placeholder="Room Code" required>
    <input type="number" name="capacity" value="60" required>
    <button class="btn" name="add_room">Add</button>
  </form>
</div>
<div class="card">
  <h3>Enroll Student</h3>
  <form method="POST" class="grid" style="grid-template-columns:repeat(3,1fr)">
    <select name="course_id"><?php $cs=$conn->query("SELECT id,course_code FROM courses ORDER BY course_code"); while($c=$cs->fetch_assoc()) echo '<option value="'.$c['id'].'">'.htmlspecialchars($c['course_code']).'</option>'; ?></select>
    <select name="student_id"><?php $ss=$conn->query("SELECT id,username FROM users WHERE role=".ROLE_STUDENT." ORDER BY username"); while($s=$ss->fetch_assoc()) echo '<option value="'.$s['id'].'">'.htmlspecialchars($s['username']).'</option>'; ?></select>
    <button class="btn" name="enroll">Enroll</button>
  </form>
</div>
<div class="card">
  <h3>Auto-Scheduler</h3>
  <form method="POST" style="display:flex;gap:8px">
    <button class="btn" name="run_scheduler">Run Draft Scheduler</button>
    <button class="btn" name="publish" onclick="return confirm('Publish draft to all users?')">Publish Draft</button>
    <button class="btn" name="clear_draft" onclick="return confirm('Clear current draft?')">Clear Draft</button>
  </form>
</div>
<?php render_timetable_grid($conn,'timetable_proposed'); ?>
<?php page_footer();