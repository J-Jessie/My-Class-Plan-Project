<?php
// --- SESSION START SAFELY ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// === DB CONFIG ===
define('DB_HOST','localhost');
define('DB_NAME','MyClassPlan');
define('DB_USER','root');
define('DB_PASS','');

// === ROLES ===
define('ROLE_ADMIN',1);
define('ROLE_STUDENT',2);
define('ROLE_LECTURER',3);
define('ROLE_TIMETABLER',4);

// === DB CONNECT ===
function db_connect(){
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