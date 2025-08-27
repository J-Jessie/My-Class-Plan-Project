<?php
// === DB & Roles ===
define('DB_HOST','localhost');
define('DB_NAME','MyClassPlan');
define('DB_USER','root');
define('DB_PASS','');

// Roles
define('ROLE_ADMIN',1);
define('ROLE_STUDENT',2);
define('ROLE_LECTURER',3);
define('ROLE_TIMETABLER',4);

// === Security Headers (applies globally) ===
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

function db_connect(){
  $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  if($conn->connect_error){ die('DB connection failed: '.$conn->connect_error); }
  $conn->set_charset('utf8mb4');
  return $conn;
}

function format_day($d){ return [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'][$d] ?? $d; }
function slots_def(){ return [1=>['08:00:00','11:00:00'],2=>['11:00:00','14:00:00'],3=>['14:00:00','17:00:00']]; }