<?php
require_once __DIR__.'/config.php';
session_start();

function login_user($username,$password){
  $conn=db_connect();
  $st=$conn->prepare("SELECT id,username,password,role,first_name,last_name FROM users WHERE username=?");
  $st->bind_param('s',$username); $st->execute(); $res=$st->get_result();
  if($res->num_rows===1){ $u=$res->fetch_assoc(); if(password_verify($password,$u['password'])){
      $_SESSION['user_id']=$u['id']; $_SESSION['username']=$u['username']; $_SESSION['role']=(int)$u['role'];
      $_SESSION['name']=trim(($u['first_name']??'').' '.($u['last_name']??'')); return true; }}
  return false;
}
function is_logged_in(){ return isset($_SESSION['user_id']); }
function require_login(){ if(!is_logged_in()){ header('Location: /login.php'); exit; } }
function logout_user(){ session_destroy(); header('Location: /login.php'); exit; }
function current_user_id(){ return $_SESSION['user_id'] ?? null; }
function current_role(){ return $_SESSION['role'] ?? null; }
function has_role($roles){ if(!is_array($roles)) $roles=[$roles]; return in_array(current_role(), $roles, true); }