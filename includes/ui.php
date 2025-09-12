<?php
// UI functions for a consistent layout across all pages.
// Includes the HTML boilerplate, header, and footer.
function page_header($title) {
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>My Class Plan - <?= htmlspecialchars($title) ?></title>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
      <style>
          body {
              background-color: #f8f9fa;
              font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
          }
          .container {
              margin-top: 20px;
              margin-bottom: 20px;
          }
          .card-header {
              background-color: #007bff;
              color: white;
              font-weight: bold;
          }
          .timetable-grid {
              display: grid;
              grid-template-columns: 50px repeat(<?= count($GLOBALS['CONFIG']['days']) ?>, 1fr);
              gap: 1px;
              border: 1px solid #dee2e6;
              overflow-x: auto;
          }
          .timetable-header, .timetable-cell {
              padding: 10px;
              border: 1px solid #dee2e6;
              text-align: center;
          }
          .timetable-header {
              background-color: #e9ecef;
              font-weight: bold;
              position: sticky;
              top: 0;
              z-index: 10;
          }
          .event-card {
              background-color: #e2f0fb;
              border: 1px solid #b3d9ff;
              border-radius: 5px;
              padding: 5px;
              margin: 2px;
              font-size: 12px;
              overflow: hidden;
              text-overflow: ellipsis;
              white-space: nowrap;
          }
          .form-label {
              font-weight: bold;
          }
      </style>
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container">
        <a class="navbar-brand" href="/">My Class Plan</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav me-auto">
            <li class="nav-item">
              <a class="nav-link" href="/index.php">Home</a>
            </li>
            <?php if (isset($_SESSION['role'])): ?>
            <li class="nav-item">
              <a class="nav-link" href="/timetable.php">Timetable</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/notifications.php">Notifications</a>
            </li>
            <?php if (has_role([ROLE_ADMIN])): ?>
              <li class="nav-item"><a class="nav-link" href="/admin_dashboard.php">Admin</a></li>
            <?php endif; ?>
            <?php if (has_role([ROLE_TIMETABLER])): ?>
              <li class="nav-item"><a class="nav-link" href="/timetabler_dashboard.php">Timetabler</a></li>
            <?php endif; ?>
            <?php if (has_role([ROLE_LECTURER])): ?>
              <li class="nav-item"><a class="nav-link" href="/lecturer_dashboard.php">Lecturer</a></li>
            <?php endif; ?>
            <?php if (has_role([ROLE_STUDENT])): ?>
              <li class="nav-item"><a class="nav-link" href="/student_dashboard.php">Student</a></li>
            <?php endif; ?>
            <?php endif; ?>
          </ul>
          <ul class="navbar-nav">
            <?php if (isset($_SESSION['name'])): ?>
              <li class="nav-item"><span class="nav-link text-white">Hello, <?= htmlspecialchars($_SESSION['name']) ?></span></li>
              <li class="nav-item"><a class="nav-link btn btn-sm btn-outline-danger" href="/login.php?logout=1">Logout</a></li>
            <?php else: ?>
              <li class="nav-item"><a class="nav-link" href="/login.php">Login</a></li>
              <li class="nav-item"><a class="nav-link" href="/signup.php">Sign Up</a></li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </nav>
    <div class="container mt-4">
  <?php
}

function page_footer() {
  ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
  </html>
  <?php
}

// Function to render the timetable grid
function renderTimetableGrid($events, $CONFIG) {
    ?>
    <div class="table-responsive">
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>Time</th>
                    <?php foreach ($CONFIG["days"] as $day): ?>
                        <th><?= htmlspecialchars($day) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $events_by_slot = [];
                foreach ($events as $event) {
                    $events_by_slot[$event['day']][$event['slot']] = $event;
                }
                ?>
                <?php foreach ($CONFIG["slots"] as $slot): ?>
                    <tr>
                        <th class="table-light"><?= htmlspecialchars($slot) ?></th>
                        <?php foreach ($CONFIG["days"] as $day): ?>
                            <td>
                                <?php if (isset($events_by_slot[$day][$slot])):
                                    $event = $events_by_slot[$day][$slot];
                                    ?>
                                    <div class="card bg-info text-white p-2">
                                        <strong><?= htmlspecialchars($event['course_code']) ?></strong><br>
                                        <small><?= htmlspecialchars($event['lecturer_name']) ?></small><br>
                                        <small>Room: <?= htmlspecialchars($event['room_code']) ?></small>
                                    </div>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>