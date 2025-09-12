<?php
require_once __DIR__.'/../includes/auth.php';
require_once __DIR__.'/../includes/ui.php';
require_login(); $conn=db_connect(); $uid=current_user_id();
if(isset($_POST['mark_read'])){ $conn->query("UPDATE notifications SET is_read=1 WHERE user_id=".(int)$uid); }
page_header('Notifications');
$res=$conn->query("SELECT message, is_read, created_at FROM notifications WHERE user_id=$uid ORDER BY created_at DESC");
?>
<div class="card"><h2>Your Notifications</h2>
  <form method="POST" style="margin-bottom:8px"><button class="btn" name="mark_read">Mark all as read</button></form>
  <table><tr><th>Date</th><th>Status</th><th>Message</th></tr>
  <?php while($n=$res->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($n['created_at']) ?></td>
      <td><?= $n['is_read']? 'Read':'New' ?></td>
      <td><?= htmlspecialchars($n['message']) ?></td>
    </tr>
  <?php endwhile; ?>
  </table>
</div>
<?php page_footer();