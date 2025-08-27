<?php
require_once __DIR__.'/includes/auth.php';
require_once __DIR__.'/includes/ui.php';

if($_SERVER['REQUEST_METHOD']==='POST'){
  if(login_user($_POST['username'], $_POST['password'])){
    header('Location: /'); exit;
  } else { $error='Invalid username or password.'; }
}
page_header('Login');
?>
<div class="card" style="max-width:420px;margin:auto">
  <h2>Login</h2>
  <?php if(isset($error)) echo "<div style='color:#f72585;margin:6px 0'>".htmlspecialchars($error)."</div>"; ?>
  <form method="POST">
    <input name="username" placeholder="Username" required style="width:100%;padding:8px;margin:6px 0">
    <input type="password" name="password" placeholder="Password" required style="width:100%;padding:8px;margin:6px 0">
    <button class="btn" type="submit">Login</button>
  </form>
</div>
<?php page_footer();