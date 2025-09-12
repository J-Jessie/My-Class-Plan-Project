<?php
require_once __DIR__.'/config.php';

function lecturer_busy($conn,$lecturer_id,$day,$slot,$table){
  $sql="SELECT t.id FROM $table t JOIN courses c ON c.id=t.course_id WHERE c.lecturer_id=? AND t.day_of_week=? AND t.slot=?";
  $st=$conn->prepare($sql); $st->bind_param('iii',$lecturer_id,$day,$slot); $st->execute();
  return $st->get_result()->num_rows>0;
}
function room_busy($conn,$room_id,$day,$slot,$table){
  $st=$conn->prepare("SELECT id FROM $table WHERE room_id=? AND day_of_week=? AND slot=?");
  $st->bind_param('iii',$room_id,$day,$slot); $st->execute();
  return $st->get_result()->num_rows>0;
}
function student_conflict_count($conn,$course_id,$day,$slot,$table){
  $sql="SELECT COUNT(*) cnt FROM enrollments e
        JOIN enrollments e2 ON e.student_id=e2.student_id AND e2.course_id<>e.course_id
        JOIN $table t ON t.course_id=e2.course_id AND t.day_of_week=? AND t.slot=?
        WHERE e.course_id=?";
  $st=$conn->prepare($sql); $st->bind_param('iii',$day,$slot,$course_id); $st->execute();
  $cnt=$st->get_result()->fetch_assoc()['cnt'] ?? 0; return (int)$cnt;
}
function user_available($conn,$user_id,$day,$slot){
  $st=$conn->prepare("SELECT available FROM availability WHERE user_id=? AND day_of_week=? AND slot=?");
  $st->bind_param('iii',$user_id,$day,$slot); $st->execute(); $res=$st->get_result();
  if($res->num_rows==0) return true; return (bool)$res->fetch_assoc()['available'];
}
function best_room($conn,$day,$slot){
  $rooms=$conn->query("SELECT id FROM rooms ORDER BY capacity DESC");
  while($r=$rooms->fetch_assoc()) if(!room_busy($conn,$r['id'],$day,$slot,'timetable_proposed')) return (int)$r['id'];
  return null;
}
function propose_schedule($conn){
  $conn->query("DELETE FROM timetable_proposed");
  $days=[1,2,3,4,5]; $slots=slots_def();
  $courses=$conn->query("SELECT c.*, COALESCE(en.cnt,0) as scnt FROM courses c
    LEFT JOIN (SELECT course_id, COUNT(*) cnt FROM enrollments GROUP BY course_id) en ON en.course_id=c.id
    ORDER BY scnt DESC, c.id ASC");
  while($course=$courses->fetch_assoc()){
    $placed=false; $bestAlt=null; $bestScore=PHP_INT_MAX;
    foreach($days as $d){ foreach([1,2,3] as $s){
      if(!user_available($conn,$course['lecturer_id'],$d,$s)) continue;
      if(lecturer_busy($conn,$course['lecturer_id'],$d,$s,'timetable_proposed')) continue;
      $room=best_room($conn,$d,$s); $score=$room?student_conflict_count($conn,$course['id'],$d,$s,'timetable_proposed'):100000;
      if($room && $score==0){ list($st,$et)=$slots[$s]; $ins=$conn->prepare("INSERT INTO timetable_proposed(course_id,day_of_week,slot,start_time,end_time,room_id) VALUES (?,?,?,?,?,?)");
        $ins->bind_param('iiissi',$course['id'],$d,$s,$st,$et,$room); $ins->execute(); $placed=true; break 2; }
      if($score<$bestScore){ $bestScore=$score; $bestAlt=['day'=>$d,'slot'=>$s,'room'=>$room]; }
    }}
    if(!$placed){ /* store suggestion row with room 0 meaning none */ }
  }
}
function publish_draft($conn){
  $res=$conn->query("SELECT * FROM timetable_proposed");
  while($row=$res->fetch_assoc()){
    $ins=$conn->prepare("INSERT IGNORE INTO timetable(course_id,day_of_week,slot,start_time,end_time,room_id) VALUES (?,?,?,?,?,?)");
    $ins->bind_param('iiissi',$row['course_id'],$row['day_of_week'],$row['slot'],$row['start_time'],$row['end_time'],$row['room_id']); $ins->execute();
  }
  $conn->query("INSERT INTO notifications(user_id,message) SELECT id,'New timetable has been published.' FROM users WHERE role IN (".ROLE_STUDENT.",".ROLE_LECTURER.")");
  $conn->query("DELETE FROM timetable_proposed");
}
function clear_draft($conn){ $conn->query("DELETE FROM timetable_proposed"); }