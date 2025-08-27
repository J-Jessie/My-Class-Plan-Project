<?php
require_once __DIR__.'/../includes/auth.php';
require_once __DIR__.'/../includes/ui.php';
require_login(); if(!has_role([ROLE_LECTURER,ROLE_ADMIN,ROLE_TIMETABLER])){ header('Location:/'); exit; }
$conn=db_connect(); $uid=current_user_id();

if(isset($_POST['set_availability'])){
  for($d=1;$d<=5;$d++) for($s=1;$s<=3;$s++){
    $key=$d.'-'.$s; $av=isset($_POST['avail'][$key])?(int)$_POST['avail'][$key]:1;
    $st=$conn->prepare("INSERT INTO availability(user_id,day_of_week,slot,available) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE available=VALUES(available)");
    $st->bind_param('iiii',$uid,$d,$s,$av); $st->execute();
  }
}
page_header('Lecturer Dashboard');
?>
<div class="card"><h2>Lecturer Dashboard</h2></div>
<div class="card">
  <h3>Your Weekly Availability</h3>
  <form method="POST">
    <table>
      <tr><th>Day</th><th>08–11</th><th>11–14</th><th>14–17</th></tr>
      <?php for($d=1;$d<=5;$d++): ?><tr>
        <td><?= format_day($d) ?></td>
        <?php for($s=1;$s<=3;$s++): $sel=$conn->query("SELECT available FROM availability WHERE user_id=$uid AND day_of_week=$d AND slot=$s")->fetch_assoc()['available']??1; ?>
          <td>
            <select name="avail[<?= $d.'-'.$s ?>]">
              <option value="1" <?= $sel? 'selected':'' ?>>Available</option>
              <option value="0" <?= !$sel? 'selected':'' ?>>Busy</option>
            </select>
          </td>
        <?php endfor; ?>
      </tr><?php endfor; ?>
    </table>
    <button class="btn" name="set_availability">Save Availability</button>
  </form>
</div>
<div class="card">
  <h3>Your Timetable</h3>
  <?php render_timetable_grid($conn,'timetable',[ 'lecturer_id'=>$uid ]); ?>
</div>
<?php page_footer();