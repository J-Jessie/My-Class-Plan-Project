<?php
require_once __DIR__.'/../includes/auth.php';
require_once __DIR__.'/../includes/ui.php';
require_once __DIR__.'/../includes/scheduler.php';
require_login(); if(!has_role([ROLE_ADMIN,ROLE_TIMETABLER])){ header('Location:/'); exit; }
$conn=db_connect();

if($_SERVER['REQUEST_METHOD']==='POST'){
  if(isset($_POST['run'])) propose_schedule($conn);
  if(isset($_POST['publish'])) publish_draft($conn);
  if(isset($_POST['clear'])) clear_draft($conn);
}
page_header('Timetabler Dashboard');
?>
<div class="card"><h2>Timetabler Dashboard</h2>
  <form method="POST" style="display:flex;gap:8px">
    <button class="btn" name="run">Run Auto-Scheduler</button>
    <button class="btn" name="publish" onclick="return confirm('Publish draft?')">Publish Draft</button>
    <button class="btn" name="clear" onclick="return confirm('Clear draft?')">Clear Draft</button>
  </form>
</div>
<div class="card">
  <h3>Draft Timetable</h3>
  <?php render_timetable_grid($conn,'timetable_proposed'); ?>
</div>
<div class="card">
  <h3>Suggestions for Unplaced Courses</h3>
  <table><tr><th>Course</th><th>Suggested Day</th><th>Slot</th><th>Room</th></tr>
  <?php
  $courses=$conn->query("SELECT id,course_code FROM courses ORDER BY course_code");
  while($c=$courses->fetch_assoc()){
    $check=$conn->prepare("SELECT 1 FROM timetable_proposed WHERE course_id=?"); $check->bind_param('i',$c['id']); $check->execute();
    if($check->get_result()->num_rows==0){
      $best=null; $bestScore=PHP_INT_MAX; foreach([1,2,3,4,5] as $d) foreach([1,2,3] as $s){
        $room=$conn->query("SELECT id,room_code FROM rooms ORDER BY capacity DESC");
        while($r=$room->fetch_assoc()){
          $busy=$conn->prepare("SELECT 1 FROM timetable_proposed WHERE day_of_week=? AND slot=? AND room_id=?");
          $busy->bind_param('iii',$d,$s,$r['id']); $busy->execute(); if($busy->get_result()->num_rows) continue;
          $score=0; if($score<$bestScore){ $bestScore=$score; $best=['day'=>$d,'slot'=>$s,'room'=>$r]; }
        }
      }
      echo '<tr><td>'.htmlspecialchars($c['course_code']).'</td><td>'.format_day($best['day']??'-').'</td><td>'.(($best['slot']??'-')).'</td><td>'.(($best['room']['room_code']??'—'))."</td></tr>";
    }
  }
  ?>
  </table>
</div>
<?php page_footer();