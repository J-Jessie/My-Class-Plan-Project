<?php
// Define roles directly in this file to ensure they are always available.
define('ROLE_ADMIN', 1);
define('ROLE_STUDENT', 2);
define('ROLE_LECTURER', 3);
define('ROLE_TIMETABLER', 4);

// The helper file is still needed for database connection and other functions.
require_once __DIR__.'/helper.php';

// Fix the session_start() error by checking if a session is already active.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function login_user($email, $password){
  $conn = db_connect();
  $st = $conn->prepare("SELECT id, email, password, role, first_name, last_name FROM users WHERE email = ?");
  $st->bind_param('s', $email);
  $st->execute();
  $res = $st->get_result();

  if ($res->num_rows === 1) {
    $u = $res->fetch_assoc();
    if (password_verify($password, $u['password'])) {
      $_SESSION['user_id'] = $u['id'];
      $_SESSION['email'] = $u['email'];
      $_SESSION['role'] = (int)$u['role'];
      $_SESSION['name'] = trim(($u['first_name']??'').' '.($u['last_name']??''));
      return true;
    }
  }
  return false;
}

// Check if a user is logged in
function is_logged_in(){ return isset($_SESSION['user_id']); }

// Require a login for a page
function require_login(){ if(!is_logged_in()){ header('Location: login.php'); exit; } }

// Redirect to the correct dashboard based on the user's role
function redirect_by_role() {
    $role = current_role();
    switch ($role) {
        case ROLE_ADMIN:
            header('Location: admin_dashboard.php');
            break;
        case ROLE_STUDENT:
            header('Location: student_dashboard.php');
            break;
        case ROLE_LECTURER:
            header('Location: lecturer_dashboard.php');
            break;
        case ROLE_TIMETABLER:
            header('Location: timetabler_dashboard.php');
            break;
        default:
            // Fallback for unknown role
            logout_user();
            break;
    }
    exit;
}

// Log a user out
function logout_user(){
  session_destroy();
  header('Location: login.php');
  exit;
}

// Get the current user's ID
function current_user_id(){ return $_SESSION['user_id'] ?? null; }

// Get the current user's role
function current_role(){ return $_SESSION['role'] ?? null; }

// Check if a user has one of the required roles
function has_role($roles){
    if(!is_array($roles)) $roles=[$roles];
    return in_array(current_role(), $roles, true);
}

// Enforce a minimum required role for a page
function require_role($required_roles) {
    require_login(); // First, check if they are logged in
    if (!has_role($required_roles)) {
        // Log them out for security if they try to access a page they shouldn't
        logout_user();
    }
}