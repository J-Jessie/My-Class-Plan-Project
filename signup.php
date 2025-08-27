<?php
require_once __DIR__.'/helper.php';

// Errors visible in dev
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Security headers
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

$conn = db_connect();
$errors = [];
$token = csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token");
    }

    // Collect inputs
    $username   = clean($_POST['username'] ?? '');
    $email      = clean($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm_pw = $_POST['confirm_password'] ?? '';
    $role       = (int)($_POST['role'] ?? 0);
    $first      = clean($_POST['first_name'] ?? '');
    $last       = clean($_POST['last_name'] ?? '');
    $terms      = isset($_POST['terms']);
    $ip         = $_SERVER['REMOTE_ADDR'] ?? '';
    $agent      = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Validations
    if(!$username || strlen($username) < 3) $errors[]="Username too short";
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]="Invalid email";
    if($password !== $confirm_pw) $errors[]="Passwords do not match";
    if(strlen($password) < 8) $errors[]="Password too weak (min 8 chars)";
    if(!$terms) $errors[]="You must accept Terms & Conditions";
    if(!in_array($role,[ROLE_STUDENT,ROLE_LECTURER,ROLE_TIMETABLER])) $errors[]="Invalid role";

    // Check uniqueness
    $stmt = $conn->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
    $stmt->bind_param("ss",$username,$email);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows>0){ $errors[]="Username or Email already exists"; }
    $stmt->close();

    // Insert if OK
    if(empty($errors)){
        $pwd = password_hash($password, PASSWORD_BCRYPT, ['cost'=>12]);
        $sql = "INSERT INTO users 
                (username, password, email, role, first_name, last_name, created_at, ip_address, user_agent) 
                VALUES (?,?,?,?,?,?,NOW(),?,?)";
        $st = $conn->prepare($sql);
        if(!$st){ die("Prepare failed: ".$conn->error); }

        $st->bind_param("sssissss", $username, $pwd, $email, $role, $first, $last, $ip, $agent);
        if($st->execute()){
            $_SESSION['flash_success'] = "Account created successfully!";
            header("Location: /login.php?registered=1");
            exit;
        } else {
            $errors[] = "Execute failed: ".$st->error;
        }
        $st->close();
    }
}

require_once __DIR__.'/includes/ui.php';
page_header('Sign Up');
?>
<div class="card" style="max-width:520px;margin:auto">
  <h2>Create Account</h2>
  <?php if(!empty($errors)) foreach($errors as $e){ 
    echo "<div style='color:#f72585'>".htmlspecialchars($e)."</div>"; } ?>

  <form method="POST">
    <input name="username" placeholder="Username" required style="width:100%;padding:8px;margin:6px 0">
    <input type="email" name="email" placeholder="Email" required style="width:100%;padding:8px;margin:6px 0">
    <input type="password" name="password" placeholder="Password" required style="width:100%;padding:8px;margin:6px 0">
    <input type="password" name="confirm_password" placeholder="Confirm Password" required style="width:100%;padding:8px;margin:6px 0">
    <div class="grid" style="grid-template-columns:1fr 1fr">
      <input name="first_name" placeholder="First name">
      <input name="last_name" placeholder="Last name">
    </div>
    <select name="role" required style="width:100%;padding:8px;margin:6px 0">
      <option value="2">Student</option>
      <option value="3">Lecturer</option>
      <option value="4">Timetabler</option>
    </select>
    <label><input type="checkbox" name="terms"> I accept the Terms & Conditions</label><br>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
    <button class="btn" type="submit">Sign Up</button>
  </form>
</div>
<?php page_footer(); ?>