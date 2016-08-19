<?php
/**
 *  Get Session and Login Information
 */

// Get Session Information
$session = DB::getRow("SELECT * FROM session WHERE sessionkey='" . addslashes($_COOKIE["SESSION_KEY"]) . "'");

// Validate Session
if(empty($session) || $session['expires'] <= strtotime("now")) {
  $_SESSION['loggedin'] = false;
  header('Location: login?error=expire');
  exit;
}

// Pull User's Information
$user = new User();
$user_id = $user->loadByUsername($session['username']);

// Validate user
if(empty($user_id) || empty($user->info->password) || $session['password'] !== $user->info->password) {
  $_SESSION['loggedin'] = false;
  header('Location: login?error=incorrect_login');
  exit;
}

// Reset Experation Date
$new_expire = strtotime("+" . SESSION_TIME . " Minutes");
DB::query("UPDATE session SET expires='" . $new_expire . "' WHERE id='" . $session['id'] . "'");
DB::query("UPDATE user SET lastlogin=NOW() WHERE id='" . $user->info->id . "'");

$_SESSION['loggedin'] = true;

// Grab Member information
$member_id = DB::getCell("SELECT member_id FROM member_user WHERE user_id='" . $user->info->id . "'");
$member = new Member($member_id);

if($member->info->active != 1) {
  $login_error = "inactive";
}


