<?php
require_once __DIR__.'/../includes/auth.php';
require_once __DIR__.'/../includes/ui.php';
require_login(); $conn=db_connect();
page_header('Timetable');
$filter=[]; if(has_role([ROLE_STUDENT])) $filter=['student_id'=>current_user_id()]; if(has_role([ROLE_LECTURER])) $filter=['lecturer_id'=>current_user_id()];
render_timetable_grid($conn,'timetable',$filter);
page_footer();