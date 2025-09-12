<?php
// Minimal configuration file for the MyClassPlan application.

// ---------------- CONFIG ----------------
$CONFIG = [
  "db" => [
    "dsn"  => getenv("TT_DSN") ?: "mysql:host=localhost;dbname=timetabler;charset=utf8mb4",
    "user" => getenv("TT_DBUSER") ?: "root",
    "pass" => getenv("TT_DBPASS") ?: "",
  ],
  // Set to true to see SQL errors in the UI (dev only)
  "debug" => true,
  // Default timetable window
  "days"  => ["Mon","Tue","Wed","Thu","Fri"],
  "slots" => ["08:00","09:00","10:00","11:00","12:00","13:00","14:00","15:00","16:00","17:00"],
];
// --- SESSION START SAFELY ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>