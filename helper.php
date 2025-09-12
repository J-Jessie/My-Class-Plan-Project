<?php
// --- SESSION START SAFELY ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// === DB CONFIG ===
// These should ideally be in a separate config file, but we'll leave them here for now
if (!defined('DB_HOST')) define('DB_HOST','localhost');
if (!defined('DB_NAME')) define('DB_NAME','MyClassPlan');
if (!defined('DB_USER')) define('DB_USER','root');
if (!defined('DB_PASS')) define('DB_PASS','');

// === ROLES ===
// Use `defined()` to prevent re-definition warnings.
if (!defined('ROLE_ADMIN')) define('ROLE_ADMIN',1);
if (!defined('ROLE_STUDENT')) define('ROLE_STUDENT',2);
if (!defined('ROLE_LECTURER')) define('ROLE_LECTURER',3);
if (!defined('ROLE_TIMETABLER')) define('ROLE_TIMETABLER',4);

// === DB CONNECT ===
function db_connect(){
    // Use MySQLi for consistency with other parts of the project
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if($conn->connect_error){ 
        die('DB connection failed: '.$conn->connect_error); 
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// === SANITIZER ===
function clean($x){ 
    return htmlspecialchars(trim($x), ENT_QUOTES, 'UTF-8'); 
}

// === CSRF HELPERS ===
function csrf_token(){
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check($token){
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}