<?php
// =======================
// CONFIGURATION & SETUP
// =======================
define('DB_HOST', 'localhost');
define('DB_NAME', 'myclassplan_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// User roles
define('ROLE_ADMIN', 1);
define('ROLE_STUDENT', 2);
define('ROLE_LECTURER', 3);
define('ROLE_TIMETABLER', 4);

session_start();

// Database connection function
function db_connect() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// =======================
// AUTHENTICATION HELPERS
// =======================
function login($username, $password) {
    $conn = db_connect();
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
    }
    return false;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function logout() {
    session_destroy();
    header("Location: index.php");
    exit();
}

function has_permission($roles) {
    return in_array($_SESSION['role'] ?? null, (array)$roles);
}

// =======================
// TIMETABLE AUTO-SCHEDULER
// =======================
function generate_timetable() {
    $conn = db_connect();
    $courses = $conn->query("SELECT * FROM courses");

    // Slots: 8–11, 11–14, 14–17 (Mon–Fri)
    $slots = [
        ['08:00:00','11:00:00'],
        ['11:00:00','14:00:00'],
        ['14:00:00','17:00:00']
    ];
    $days = [1,2,3,4,5]; // Mon–Fri

    $schedule = [];
    $rooms = ['Room A','Room B','Room C'];
    $used = [];

    while ($course = $courses->fetch_assoc()) {
        $placed = false;
        foreach ($days as $d) {
            foreach ($slots as $slot) {
                foreach ($rooms as $room) {
                    $key = $d.'-'.$slot[0].'-'.$room;
                    if (!isset($used[$key])) {
                        $conn->query("INSERT INTO timetable(course_id, day_of_week, start_time, end_time, room) VALUES (".$course['id'].",$d,'$slot[0]','$slot[1]','$room')");
                        $used[$key] = true;
                        $schedule[] = [$course['course_code'],$d,$slot[0],$slot[1],$room];
                        $placed = true;
                        break 3;
                    }
                }
            }
        }
        if(!$placed) {
            $schedule[] = [$course['course_code'],'No slot','-','-',''];
        }
    }
    return $schedule;
}

function suggest_alternative($conflictCourse) {
    $conn = db_connect();
    $slots = [
        ['08:00:00','11:00:00'],
        ['11:00:00','14:00:00'],
        ['14:00:00','17:00:00']
    ];
    $days = [1,2,3,4,5];
    $rooms = ['Room A','Room B','Room C'];
    foreach ($days as $d) {
        foreach ($slots as $slot) {
            foreach ($rooms as $room) {
                $exists = $conn->query("SELECT * FROM timetable WHERE day_of_week=$d AND start_time='$slot[0]' AND room='$room'");
                if($exists->num_rows==0) {
                    return "Suggested alternative: Day $d, $slot[0]-$slot[1], $room";
                }
            }
        }
    }
    return "No alternative slot available.";
}

// =======================
// PAGE ROUTING
// =======================
$page = $_GET['page'] ?? 'home';
if (isset($_POST['login'])) {
    if (login($_POST['username'], $_POST['password'])) {
        header("Location: index.php?page=dashboard");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
if (isset($_GET['logout'])) logout();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MyClassPlan - Timetable Management</title>
<style>
body { font-family: Arial, sans-serif; background:#f5f7fb; margin:0; }
header { background:#3a0ca3; color:#fff; padding:1rem; }
nav { background:#4361ee; padding:.5rem; }
nav a { color:#fff; margin:0 1rem; text-decoration:none; }
.sidebar { width:200px; background:#eee; height:100vh; float:left; padding-top:2rem; }
.sidebar a { display:block; padding:1rem; color:#333; text-decoration:none; }
.sidebar a:hover { background:#ddd; }
.main { margin-left:200px; padding:2rem; }
.card { background:#fff; padding:1rem; border-radius:8px; margin-bottom:1rem; }
.btn { padding:.5rem 1rem; background:#4361ee; color:#fff; border:none; border-radius:4px; cursor:pointer; }
.btn:hover { background:#3a0ca3; }
.error { background:#ffeaea; color:#c00; padding:.5rem; margin:.5rem 0; border-radius:4px; }
table { width:100%; border-collapse: collapse; margin-top:1rem; }
th, td { border:1px solid #ddd; padding:.5rem; text-align:center; }
th { background:#eee; }
</style>
</head>
<body>
<header><h1>📚 MyClassPlan</h1></header>

<?php
// =======================
// DB INITIALIZATION (safe)
// =======================
function init_database_soft(){
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($conn->connect_error) die("Connection failed: ".$conn->connect_error);
    $conn->query("CREATE DATABASE IF NOT EXISTS ".DB_NAME);
    $conn->select_db(DB_NAME);

    // Core tables
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        role INT NOT NULL,
        first_name VARCHAR(50),
        last_name VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $conn->query("CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_code VARCHAR(20) UNIQUE,
        course_name VARCHAR(100) NOT NULL,
        lecturer_id INT,
        credits INT DEFAULT 3,
        FOREIGN KEY (lecturer_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB");

    $conn->query("CREATE TABLE IF NOT EXISTS rooms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        room_code VARCHAR(50) UNIQUE,
        capacity INT DEFAULT 60
    ) ENGINE=InnoDB");

    $conn->query("CREATE TABLE IF NOT EXISTS enrollments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        student_id INT NOT NULL,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE(course_id, student_id)
    ) ENGINE=InnoDB");

    // Availability by slot (1=08-11, 2=11-14, 3=14-17)
    $conn->query("CREATE TABLE IF NOT EXISTS availability (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        day_of_week TINYINT NOT NULL, -- 1=Mon..6=Sat, 7=Sun
        slot TINYINT NOT NULL,        -- 1..3
        available TINYINT(1) DEFAULT 1,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE(user_id, day_of_week, slot)
    ) ENGINE=InnoDB");

    // Published timetable
    $conn->query("CREATE TABLE IF NOT EXISTS timetable (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        day_of_week TINYINT NOT NULL,
        slot TINYINT NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        room_id INT NOT NULL,
        published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
        FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE RESTRICT,
        UNIQUE(day_of_week, slot, room_id)
    ) ENGINE=InnoDB");

    // Draft schedule for Timetabler to review
    $conn->query("CREATE TABLE IF NOT EXISTS timetable_proposed (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        day_of_week TINYINT NOT NULL,
        slot TINYINT NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        room_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
        FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE RESTRICT,
        UNIQUE(day_of_week, slot, room_id)
    ) ENGINE=InnoDB");

    // Notifications
    $conn->query("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    // Seed minimal data
    $exists = $conn->query("SELECT id FROM users WHERE username='admin'")->num_rows;
    if(!$exists){
        $pwd = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO users(username,password,email,role,first_name,last_name) VALUES
            ('admin','$pwd','admin@myclassplan.com',".ROLE_ADMIN.", 'System','Administrator')");
    }
    // At least one room
    $r = $conn->query("SELECT id FROM rooms LIMIT 1")->num_rows;
    if(!$r){
        $conn->query("INSERT INTO rooms(room_code,capacity) VALUES ('Room A1',80),('Room B2',60),('Lab C3',40)");
    }
}
init_database_soft();

// =======================
// SCHEDULER (3-hour slots 08-11, 11-14, 14-17)
// =======================
function slots_def(){
    return [1=>['08:00:00','11:00:00'], 2=>['11:00:00','14:00:00'], 3=>['14:00:00','17:00:00']];
}

function lecturer_busy($conn,$lecturer_id,$day,$slot,$table){
    $sql = "SELECT t.id FROM $table t JOIN courses c ON c.id=t.course_id WHERE c.lecturer_id=? AND t.day_of_week=? AND t.slot=?";
    $st=$conn->prepare($sql); $st->bind_param('iii',$lecturer_id,$day,$slot); $st->execute(); $res=$st->get_result();
    return $res->num_rows>0;
}

function room_busy($conn,$room_id,$day,$slot,$table){
    $st=$conn->prepare("SELECT id FROM $table WHERE room_id=? AND day_of_week=? AND slot=?");
    $st->bind_param('iii',$room_id,$day,$slot); $st->execute();
    return $st->get_result()->num_rows>0;
}

function student_conflict_count($conn,$course_id,$day,$slot,$table){
    // Count how many enrolled students would have clashes in the same slot with other courses
    $sql = "SELECT COUNT(*) cnt FROM enrollments e
            JOIN enrollments e2 ON e.student_id=e2.student_id AND e2.course_id<>e.course_id
            JOIN $table t ON t.course_id=e2.course_id AND t.day_of_week=? AND t.slot=?
            WHERE e.course_id=?";
    $st=$conn->prepare($sql); $st->bind_param('iii',$day,$slot,$course_id); $st->execute();
    $cnt=$st->get_result()->fetch_assoc()['cnt'] ?? 0; return (int)$cnt;
}

function user_available($conn,$user_id,$day,$slot){
    $st=$conn->prepare("SELECT available FROM availability WHERE user_id=? AND day_of_week=? AND slot=?");
    $st->bind_param('iii',$user_id,$day,$slot); $st->execute(); $res=$st->get_result();
    if($res->num_rows==0) return true; // default available if not set
    return (bool)$res->fetch_assoc()['available'];
}

function best_room($conn,$day,$slot){
    // Pick first free room
    $rooms=$conn->query("SELECT id FROM rooms ORDER BY capacity DESC");
    while($r=$rooms->fetch_assoc()){
        if(!room_busy($conn,$r['id'],$day,$slot,'timetable_proposed')) return (int)$r['id'];
    }
    return null;
}

function propose_schedule($conn){
    // Clear previous draft
    $conn->query("DELETE FROM timetable_proposed");

    $slots=slots_def();
    $days=[1,2,3,4,5]; // Mon..Fri

    // Order courses by #enrollments desc to place hardest first
    $courses=$conn->query("SELECT c.*, COALESCE(en.cnt,0) as scnt FROM courses c
        LEFT JOIN (SELECT course_id, COUNT(*) cnt FROM enrollments GROUP BY course_id) en ON en.course_id=c.id
        ORDER BY scnt DESC, c.id ASC");

    $suggestions=[];
    while($course=$courses->fetch_assoc()){
        $placed=false; $bestAlt=null; $bestAltScore=PHP_INT_MAX;
        foreach($days as $d){
            foreach([1,2,3] as $s){
                // Check lecturer availability and busy status
                if(!user_available($conn,$course['lecturer_id'],$d,$s)) continue;
                if(lecturer_busy($conn,$course['lecturer_id'],$d,$s,'timetable_proposed')) continue;
                $room=best_room($conn,$d,$s);
                if(!$room) { // compute conflict score for alternative even if no room now
                    $score= 100000 + student_conflict_count($conn,$course['id'],$d,$s,'timetable_proposed');
                } else {
                    $score= student_conflict_count($conn,$course['id'],$d,$s,'timetable_proposed');
                }
                // Prefer zero-conflict slots
                if($room && $score==0){
                    list($st,$et)=$slots[$s];
                    $ins=$conn->prepare("INSERT INTO timetable_proposed(course_id, day_of_week, slot, start_time, end_time, room_id) VALUES (?,?,?,?,?,?)");
                    $ins->bind_param('iiissi',$course['id'],$d,$s,$st,$et,$room); $ins->execute();
                    $placed=true; break 2;
                }
                // Keep best alternative
                if($score < $bestAltScore){
                    $bestAltScore=$score; $bestAlt=['day'=>$d,'slot'=>$s,'room'=>$room];
                }
            }
        }
        if(!$placed){
            // Couldn't place perfectly — store suggestion
            $suggestions[$course['id']]=$bestAlt; // may have null room; Timetabler will decide
        }
    }
    return $suggestions;
}

function publish_draft($conn){
    // Copy draft to published timetable and notify
    $slots=slots_def();
    $res=$conn->query("SELECT * FROM timetable_proposed");
    while($row=$res->fetch_assoc()){
        $ins=$conn->prepare("INSERT IGNORE INTO timetable(course_id, day_of_week, slot, start_time, end_time, room_id) VALUES (?,?,?,?,?,?)");
        $ins->bind_param('iiissi',$row['course_id'],$row['day_of_week'],$row['slot'],$row['start_time'],$row['end_time'],$row['room_id']);
        $ins->execute();
    }
    // Simple broadcast notifications to students/lecturers
    $conn->query("INSERT INTO notifications(user_id, message)
                 SELECT id, 'New timetable has been published.' FROM users WHERE role IN (".ROLE_STUDENT.",".ROLE_LECTURER.")");
    $conn->query("DELETE FROM timetable_proposed");
}

function clear_draft($conn){ $conn->query("DELETE FROM timetable_proposed"); }

function format_day($d){ return [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'][$d] ?? $d; }

function render_grid($conn,$table){
    $slots=slots_def();
    echo '<div class="card"><h3>'.($table=='timetable'?'Published Timetable':'Draft Timetable')."</h3>";
    echo '<div class="timetable-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px">';
    echo '<div></div>'; foreach([1,2,3] as $s){ echo '<div class="timetable-header">'.substr($slots[$s][0],0,5).'–'.substr($slots[$s][1],0,5).'</div>'; }
    for($d=1;$d<=5;$d++){
        echo '<div class="timetable-header">'.format_day($d).'</div>';
        foreach([1,2,3] as $s){
            $st=$conn->prepare("SELECT tp.*, c.course_code, c.course_name, r.room_code FROM $table tp JOIN courses c ON c.id=tp.course_id JOIN rooms r ON r.id=tp.room_id WHERE day_of_week=? AND slot=?");
            $st->bind_param('ii',$d,$s); $st->execute(); $res=$st->get_result();
            echo '<div class="timetable-cell">';
            if($res->num_rows==0){ echo '<em>Free</em>'; }
            else while($row=$res->fetch_assoc()){
                echo '<div class="timetable-slot"><h4>'.$row['course_code'].'</h4>'.htmlspecialchars($row['course_name']).'<br><small>'.$row['room_code'].'</small></div>';
            }
            echo '</div>';
        }
    }
    echo '</div></div>';
}
?>

<?php if(is_logged_in()): ?>
<nav>
  <a href="index.php?page=dashboard">Dashboard</a>
  <a href="index.php?page=timetable">Timetable</a>
  <a href="index.php?page=exams">Exams</a>
  <?php if(has_permission([ROLE_ADMIN, ROLE_TIMETABLER])): ?><a href="index.php?page=management">Management</a><?php endif; ?>
  <a href="?logout=true">Logout</a>
</nav>
<?php endif; ?>

<div class="main">
<?php
$page = $_GET['page'] ?? 'home';
$conn = db_connect();

// =======================
// ACTION HANDLERS (POST)
// =======================
if(isset($_POST['add_course']) && has_permission([ROLE_ADMIN, ROLE_TIMETABLER])){
    $st=$conn->prepare("INSERT INTO courses(course_code,course_name,lecturer_id,credits) VALUES (?,?,?,?)");
    $st->bind_param('ssii',$_POST['course_code'],$_POST['course_name'],$_POST['lecturer_id'],$_POST['credits']);
    $st->execute(); echo '<div class="card">✅ Course added.</div>';
}
if(isset($_POST['add_room']) && has_permission([ROLE_ADMIN, ROLE_TIMETABLER])){
    $st=$conn->prepare("INSERT INTO rooms(room_code,capacity) VALUES (?,?)");
    $st->bind_param('si',$_POST['room_code'],$_POST['capacity']); $st->execute(); echo '<div class="card">✅ Room added.</div>';
}
if(isset($_POST['set_availability']) && has_permission([ROLE_LECTURER, ROLE_TIMETABLER, ROLE_ADMIN])){
    $uid = $_SESSION['user_id'];
    foreach($_POST['avail'] ?? [] as $key=>$val){ list($d,$s)=explode('-',$key); $av = (int)$val; 
        $st=$conn->prepare("INSERT INTO availability(user_id,day_of_week,slot,available) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE available=VALUES(available)");
        $st->bind_param('iiii',$uid,$d,$s,$av); $st->execute();
    }
    echo '<div class="card">✅ Availability saved.</div>';
}
if(isset($_POST['enroll']) && has_permission([ROLE_ADMIN, ROLE_TIMETABLER])){
    $st=$conn->prepare("INSERT IGNORE INTO enrollments(course_id,student_id) VALUES (?,?)");
    $st->bind_param('ii',$_POST['course_id'],$_POST['student_id']); $st->execute(); echo '<div class="card">✅ Student enrolled.</div>';
}
if(isset($_POST['run_scheduler']) && has_permission([ROLE_ADMIN, ROLE_TIMETABLER])){
    $sug = propose_schedule($conn);
    echo '<div class="card"><b>Scheduler run complete.</b> Suggestions for unresolved courses are listed below.</div>';
}
if(isset($_POST['publish']) && has_permission([ROLE_ADMIN, ROLE_TIMETABLER])){ publish_draft($conn); echo '<div class="card">🚀 Draft published to students & lecturers.</div>'; }
if(isset($_POST['clear_draft']) && has_permission([ROLE_ADMIN, ROLE_TIMETABLER])){ clear_draft($conn); echo '<div class="card">🧹 Draft cleared.</div>'; }
?>

<?php if(!is_logged_in() && $page=='home'): ?>
  <div class="card">
    <h2>Welcome to MyClassPlan</h2>
    <p>Efficient 3-hour block timetables (08:00–17:00) with role-aware workflows and smart suggestions.</p>
    <a class="btn" href="index.php?page=login">Login</a>
    <a class="btn" href="index.php?page=signup">Sign Up</a>
  </div>
<?php elseif($page=='login' && !is_logged_in()): ?>
  <div class="card" style="max-width:400px; margin:auto;">
    <h2>Login</h2>
    <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
    <form method="POST">
      <input type="text" name="username" placeholder="Username" required style="width:100%;margin:.5rem 0;">
      <input type="password" name="password" placeholder="Password" required style="width:100%;margin:.5rem 0;">
      <button type="submit" name="login" class="btn">Login</button>
    </form>
  </div>
<?php elseif($page=='signup' && !is_logged_in()): ?>
  <div class="card" style="max-width:480px; margin:auto;">
    <h2>Sign Up</h2>
    <form method="POST" action="signup.php">
      <input type="text" name="username" placeholder="Username" required style="width:100%;margin:.5rem 0;">
      <input type="email" name="email" placeholder="Email" required style="width:100%;margin:.5rem 0;">
      <input type="password" name="password" placeholder="Password" required style="width:100%;margin:.5rem 0;">
      <select name="role" required style="width:100%;margin:.5rem 0;">
        <option value="2">Student</option>
        <option value="3">Lecturer</option>
        <option value="4">Timetabler</option>
      </select>
      <input type="text" name="first_name" placeholder="First name" style="width:100%;margin:.5rem 0;">
      <input type="text" name="last_name" placeholder="Last name" style="width:100%;margin:.5rem 0;">
      <button type="submit" class="btn">Create Account</button>
    </form>
  </div>
<?php elseif(is_logged_in() && $page=='dashboard'): ?>
  <h2>Dashboard</h2>
  <div class="card">Welcome back, <?= htmlspecialchars($_SESSION['username']) ?>!</div>
  <?php if(has_permission([ROLE_LECTURER, ROLE_TIMETABLER, ROLE_ADMIN])): ?>
  <div class="card">
    <h3>Your Availability (Mon–Fri / 3 slots)</h3>
    <form method="POST">
      <table>
        <tr><th>Day</th><th>08–11</th><th>11–14</th><th>14–17</th></tr>
        <?php for($d=1;$d<=5;$d++): ?>
        <tr>
          <td><?= format_day($d) ?></td>
          <?php for($s=1;$s<=3;$s++): ?>
            <td><select name="avail[<?= $d.'-'.$s ?>]"><option value="1">Available</option><option value="0">Busy</option></select></td>
          <?php endfor; ?>
        </tr>
        <?php endfor; ?>
      </table>
      <button class="btn" name="set_availability">Save Availability</button>
    </form>
  </div>
  <?php endif; ?>
<?php elseif(is_logged_in() && $page=='timetable'): ?>
  <?php render_grid($conn,'timetable'); ?>
<?php elseif(is_logged_in() && $page=='exams'): ?>
  <div class="card"><h3>Exams</h3><p>Exam scheduling UI can be added similarly with slots/days.</p></div>
<?php elseif(is_logged_in() && $page=='management' && has_permission([ROLE_ADMIN, ROLE_TIMETABLER])): ?>
  <h2>Management</h2>
  <div class="card">
    <h3>Courses</h3>
    <form method="POST" style="display:grid;grid-template-columns:repeat(5,1fr);gap:8px;align-items:end;">
      <div><label>Code</label><input name="course_code" required></div>
      <div><label>Name</label><input name="course_name" required></div>
      <div><label>Lecturer</label>
        <select name="lecturer_id" required>
          <?php $ls=$conn->query("SELECT id,username FROM users WHERE role=".ROLE_LECTURER." ORDER BY username"); while($l=$ls->fetch_assoc()) echo '<option value="'.$l['id'].'">'.htmlspecialchars($l['username']).'</option>'; ?>
        </select>
      </div>
      <div><label>Credits</label><input type="number" name="credits" value="3"></div>
      <div><button class="btn" name="add_course">Add Course</button></div>
    </form>
  </div>

  <div class="card">
    <h3>Rooms</h3>
    <form method="POST" style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;align-items:end;">
      <div><label>Room Code</label><input name="room_code" required></div>
      <div><label>Capacity</label><input type="number" name="capacity" value="60" required></div>
      <div><button class="btn" name="add_room">Add Room</button></div>
    </form>
  </div>

  <div class="card">
    <h3>Enroll Students</h3>
    <form method="POST" style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;align-items:end;">
      <div><label>Course</label><select name="course_id"><?php $cs=$conn->query("SELECT id,course_code FROM courses ORDER BY course_code"); while($c=$cs->fetch_assoc()) echo '<option value="'.$c['id'].'">'.htmlspecialchars($c['course_code']).'</option>'; ?></select></div>
      <div><label>Student</label><select name="student_id"><?php $ss=$conn->query("SELECT id,username FROM users WHERE role=".ROLE_STUDENT." ORDER BY username"); while($s=$ss->fetch_assoc()) echo '<option value="'.$s['id'].'">'.htmlspecialchars($s['username']).'</option>'; ?></select></div>
      <div><button class="btn" name="enroll">Enroll</button></div>
    </form>
  </div>

  <div class="card">
    <h3>Auto-Scheduler</h3>
    <form method="POST" style="display:flex; gap:8px;">
      <button class="btn" name="run_scheduler">Run Draft Scheduler</button>
      <button class="btn" name="publish" onclick="return confirm('Publish draft to all users?')">Publish Draft</button>
      <button class="btn" name="clear_draft" onclick="return confirm('Clear current draft?')">Clear Draft</button>
    </form>
  </div>

  <?php render_grid($conn,'timetable_proposed'); ?>

  <div class="card">
    <h3>Suggestions for Unplaced Courses</h3>
    <table>
      <tr><th>Course</th><th>Suggested Day</th><th>Suggested Slot</th><th>Suggested Room</th></tr>
      <?php
      // Build a live suggestions table based on current draft
      $courses=$conn->query("SELECT id, course_code FROM courses ORDER BY course_code");
      while($c=$courses->fetch_assoc()){
        // If course not in draft, compute suggestion
        $check=$conn->prepare("SELECT 1 FROM timetable_proposed WHERE course_id=?"); $check->bind_param('i',$c['id']); $check->execute(); $in=$check->get_result()->num_rows;
        if($in==0){
          // brute compute best suggestion
          $best=null; $bestScore=PHP_INT_MAX; foreach([1,2,3,4,5] as $d) foreach([1,2,3] as $s){ $roomId=best_room($conn,$d,$s); $score= student_conflict_count($conn,$c['id'],$d,$s,'timetable_proposed'); if($score<$bestScore){ $bestScore=$score; $best=['day'=>$d,'slot'=>$s,'room'=>$roomId]; }}
          echo '<tr><td>'.htmlspecialchars($c['course_code']).'</td><td>'.format_day($best['day']??'-').'</td><td>'.(($best['slot']??'-')).'</td><td>'.($best['room']? $conn->query("SELECT room_code FROM rooms WHERE id=".(int)$best['room'])->fetch_assoc()['room_code'] : '—').'</td></tr>';
        }
      }
      ?>
    </table>
  </div>
<?php endif; ?>
</div>

<footer style="background:#212529;color:#fff;padding:1rem;text-align:center;">
  &copy; <?= date('Y') ?> MyClassPlan
</footer>
</body>
</html>