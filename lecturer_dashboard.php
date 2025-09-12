<?php
/**
 * Minimal role-based dashboard page.
 * Requirements covered:
 * - Role-based access control stubs
 * - Timetable grid rendering
 * - Feature-specific UIs per role
 *
 * NOTE: Set your DB credentials below. Pages gracefully degrade to demo data if DB is unreachable.
 * Recommended schema is shown near the bottom of each file (SQL comment).
 */
session_start();

// ---------------- CONFIG ----------------
$CONFIG = [
  "db" => [
    "dsn"  => getenv("TT_DSN") ?: "mysql:host=localhost;dbname=timetabler;charset=utf8mb4",
    "user" => getenv("TT_DBUSER") ?: "root",
    "pass" => getenv("TT_DBPASS") ?: "",
  ],
  // set to true to see SQL errors in the UI (dev only)
  "debug" => true,
  // Default timetable window
  "days"  => ["Mon","Tue","Wed","Thu","Fri"],
  "slots" => ["08:00","09:00","10:00","11:00","12:00","13:00","14:00","15:00","16:00","17:00"],
];

function getPDO($CONFIG) {
  try {
    $pdo = new PDO($CONFIG["db"]["dsn"], $CONFIG["db"]["user"], $CONFIG["db"]["pass"], [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    return $pdo;
  } catch (Throwable $e) {
    return null; // fall back to demo data
  }
}

function fetchCourses($pdo) {
  if (!$pdo) return [
    ["id"=>1,"code"=>"CSC101","name"=>"Intro to CS"],
    ["id"=>2,"code"=>"MAT102","name"=>"Calculus I"],
    ["id"=>3,"code"=>"PHY103","name"=>"Physics I"],
  ];
  return $pdo->query("SELECT id, code, name FROM courses ORDER BY code")->fetchAll();
}

function fetchLecturers($pdo) {
  if (!$pdo) return [
    ["id"=>10,"name"=>"Dr. Achieng"],
    ["id"=>11,"name"=>"Mr. Otieno"],
    ["id"=>12,"name"=>"Prof. Wanjiru"],
  ];
  return $pdo->query("SELECT id, name FROM users WHERE role='lecturer' ORDER BY name")->fetchAll();
}

function fetchRooms($pdo) {
  if (!$pdo) return [
    ["id"=>100,"code"=>"B1-01","capacity"=>80],
    ["id"=>101,"code"=>"Eng-201","capacity"=>40],
    ["id"=>102,"code"=>"Sci-14","capacity"=>60],
  ];
  return $pdo->query("SELECT id, code, capacity FROM rooms ORDER BY code")->fetchAll();
}

function fetchAllocations($pdo, $filter = []) {
  // Returns array of events: day, time, course_code, room_code, lecturer_name, group_label (optional), owner ids
  if (!$pdo) {
    // Demo data
    return [
      ["day"=>"Mon","time"=>"10:00","course_code"=>"CSC101","course_id"=>1,"room_code"=>"B1-01","room_id"=>100,"lecturer_name"=>"Dr. Achieng","lecturer_id"=>10,"group_label"=>"Y1"],
      ["day"=>"Tue","time"=>"09:00","course_code"=>"MAT102","course_id"=>2,"room_code"=>"Eng-201","room_id"=>101,"lecturer_name"=>"Mr. Otieno","lecturer_id"=>11,"group_label"=>"Y1"],
      ["day"=>"Wed","time"=>"14:00","course_code"=>"PHY103","course_id"=>3,"room_code"=>"Sci-14","room_id"=>102,"lecturer_name"=>"Prof. Wanjiru","lecturer_id"=>12,"group_label"=>"Y1"],
      ["day"=>"Thu","time"=>"11:00","course_code"=>"CSC101","course_id"=>1,"room_code"=>"B1-01","room_id"=>100,"lecturer_name"=>"Dr. Achieng","lecturer_id"=>10,"group_label"=>"Y1"],
    ];
  }
  $where = [];
  $params = [];
  if (!empty($filter["lecturer_id"])) { $where[] = "a.lecturer_id = :lid"; $params[":lid"] = $filter["lecturer_id"]; }
  if (!empty($filter["student_group"])) { $where[] = "a.group_label = :g"; $params[":g"] = $filter["student_group"]; }
  $sql = "SELECT a.day, a.time, c.code AS course_code, c.id AS course_id, r.code AS room_code, r.id AS room_id,
                 u.name AS lecturer_name, u.id AS lecturer_id, a.group_label
          FROM allocations a
          JOIN courses c ON c.id=a.course_id
          JOIN rooms r ON r.id=a.room_id
          JOIN users u ON u.id=a.lecturer_id" . (count($where) ? (" WHERE " . implode(" AND ", $where)) : "") . "
          ORDER BY FIELD(a.day,'Mon','Tue','Wed','Thu','Fri','Sat'), a.time";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  return $stmt->fetchAll();
}

// Simple conflict detection: same day+time with either same room or same lecturer
function detectConflicts($events) {
  $seen = [];
  $conflicts = [];
  foreach ($events as $idx => $e) {
    $k_room = $e["day"]."|".$e["time"]."|room|".$e["room_code"];
    $k_lec  = $e["day"]."|".$e["time"]."|lec|".$e["lecturer_name"];
    foreach ([$k_room, $k_lec] as $k) {
      if (isset($seen[$k])) {
        $conflicts[] = [$seen[$k], $idx];
      } else {
        $seen[$k] = $idx;
      }
    }
  }
  return $conflicts;
}

function renderTimetableGrid($events, $CONFIG, $highlight = []) {
  $grid = [];
  foreach ($CONFIG["days"] as $d) foreach ($CONFIG["slots"] as $t) $grid[$d][$t] = [];
  foreach ($events as $e) {
    if (isset($grid[$e["day"]][$e["time"]])) $grid[$e["day"]][$e["time"]][] = $e;
  }
  ob_start();
  ?>
  <div class="table-responsive border rounded shadow-sm">
    <table class="table table-bordered align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th style="width:8rem">Time</th>
          <?php foreach ($CONFIG["days"] as $d): ?>
            <th><?= htmlspecialchars($d) ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($CONFIG["slots"] as $slot): ?>
          <tr>
            <th class="table-light"><?= htmlspecialchars($slot) ?></th>
            <?php foreach ($CONFIG["days"] as $d): ?>
              <td style="min-width:14rem">
                <?php if (empty($grid[$d][$slot])): ?>
                  <div class="text-muted small">—</div>
                <?php else: foreach ($grid[$d][$slot] as $e): 
                  $isMy = false;
                  if (!empty($highlight)) {
                    foreach ($highlight as $k=>$v) {
                      if (isset($e[$k]) && $e[$k]===$v) { $isMy = true; break; }
                    }
                  }
                ?>
                  <div class="p-2 mb-2 rounded <?= $isMy ? 'bg-info-subtle border border-info' : 'bg-body-secondary' ?>">
                    <div class="fw-semibold"><?= htmlspecialchars($e["course_code"]) ?> <span class="text-muted small"><?= htmlspecialchars($e["group_label"] ?? "") ?></span></div>
                    <div class="small"><?= htmlspecialchars($e["room_code"]) ?> · <?= htmlspecialchars($e["lecturer_name"]) ?></div>
                  </div>
                <?php endforeach; endif; ?>
              </td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php
  return ob_get_clean();
}

function flash($type,$msg) {
  $_SESSION["flash"][] = ["type"=>$type,"msg"=>$msg];
}
function renderFlash() {
  if (empty($_SESSION["flash"])) return;
  foreach ($_SESSION["flash"] as $f) {
    $cls = $f["type"] === "error" ? "danger" : ($f["type"]==="warn" ? "warning" : "success");
    echo "<div class='alert alert-$cls alert-dismissible fade show' role='alert'>".htmlspecialchars($f["msg"])."<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
  }
  $_SESSION["flash"] = [];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Timetable System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif }
    .nav-pills .nav-link.active { background: #0d6efd; }
    .card { border-radius: 1rem; }
  </style>
</head>
<body class="bg-light">
<div class="container py-4">
<?php
require_role([ROLE_LECTURER, ROLE_ADMIN]);
$pdo = getPDO($CONFIG);

// Save availability (demo stores in session; in production save to DB 'availability')
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["availability"])) {
  $_SESSION["availability"] = $_POST["availability"];
  flash("ok","Availability updated.");
  header("Location: ".$_SERVER["PHP_SELF"]);
  exit;
}
$availability = $_SESSION["availability"] ?? [];

$events = fetchAllocations($pdo, ["lecturer_id"=>($_SESSION["user_id"] ?? null)]);

echo "<h1 class='mb-3'>Lecturer Dashboard</h1>";
renderFlash();
?>
<div class="row g-4">
  <div class="col-lg-5">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h5 class="card-title">Set Availability</h5>
        <form method="post" class="">
          <input type="hidden" name="availability[sentinel]" value="1"/>
          <div class="table-responsive border rounded">
            <table class="table table-sm table-bordered mb-2">
              <thead class="table-light">
                <tr>
                  <th>Time \ Day</th>
                  <?php foreach ($CONFIG["days"] as $d): ?><th><?= htmlspecialchars($d) ?></th><?php endforeach; ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($CONFIG["slots"] as $t): ?>
                  <tr>
                    <th class="table-light"><?= htmlspecialchars($t) ?></th>
                    <?php foreach ($CONFIG["days"] as $d): 
                      $checked = isset($availability[$d][$t]) ? "checked" : "";
                    ?>
                      <td class="text-center"><input type="checkbox" class="form-check-input" name="availability[<?= htmlspecialchars($d) ?>][<?= htmlspecialchars($t) ?>]" <?= $checked ?> /></td>
                    <?php endforeach; ?>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <button class="btn btn-primary mt-2">Save Availability</button>
        </form>
        <p class="text-muted small mt-2 mb-0">Checked = available.</p>
      </div>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h5 class="card-title">My Assigned Schedule</h5>
        <?php
          echo renderTimetableGrid($events, $CONFIG, ["lecturer_name"=>$_SESSION["name"]]);
        ?>
      </div>
    </div>
  </div>
</div>

<!--
Suggested tables:
CREATE TABLE availability (id INT PK AI, lecturer_id INT, day ENUM('Mon','Tue','Wed','Thu','Fri'), time TIME, available BOOLEAN, UNIQUE KEY (lecturer_id, day, time));
-->

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
