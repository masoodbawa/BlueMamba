<?php
/*********************************************************************

  BlueMamba is a software package created by Travis Schanafelt
  Copyright 2006-2016 Travis Schanafelt, All Rights Reserved

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  Author:   Travis Schanafelt

  Modified: 09/18/2008

  Document: source/index.php

  Function: Login, User validation.

*********************************************************************/

include("inc/BlueMamba/super2global.php");
include("inc/BlueMamba/nocache.php");
include_once("inc/BlueMamba/encryption.php");
include_once("inc/BlueMamba/version.php");
include_once("conf/conf.php");

$authenticated = false;

// Session not started yet
if(!isset($session) || (empty($session))) {

  include_once("conf/defaults.php");

  // Attempt to initiate session
  if((isset($user)) && (isset($password)) && (isset($host))) {
    include("inc/BlueMamba/icl.php");
    $user_name = $user;

    // Domain Authintication
    include('images/themes/' . $DOMAIN_THEME . '/domain_auth.php');

    // First, authenticate with server
    $iil_conn = iil_Connect($host, $user_name, $password, $AUTH_MODE);
    if($iil_conn) {
      // Start session
      if(!$error) {
        include("inc/BlueMamba/write_sinc.php");
        include("conf/login_actions.php");

        if($new_user) {
          include("conf/new_user.php");
          $show_page = "options";
        }

        setcookie("MAIL_SESSION", $session, time() + (3600 * 24)); // Save cookie set expire in 24 hours

        $authenticated = true;
      }

      iil_Close($iil_conn);
    }
    else {
      $error = $iil_error;
    }

    // Make log entry
    $log_action = "log in";
    include("inc/BlueMamba/log.php");
  }
}


// Valid Session
$login_success = false;
if((isset($session)) && ($session != "")) {

  $user = $session;

  // Load session data
  include("inc/BlueMamba/session_auth.php");
  include("conf/defaults.php");

  // Authenticate
  if(!$authenticated) {
    include_once("inc/BlueMamba/icl.php");
    $conn = iil_Connect($host, $loginID, $password, $AUTH_MODE);
    if($conn) {
      iil_Close($conn);
    }
    else {
      $error = "Authentication Failed!";
    }
  }

  // Save Options (posted from "options" pane so that changes apply to all frames)
  if($do_options) {
    // Apply changes...
    if(isset($apply)) {
      $update = true;
    }
    if((isset($update)) || (isset($revert))) {
      $my_prefs = $default_prefs;

      // Over-write values if updating
      if(isset($update)) {
        reset($my_prefs);
        while(list($key, $value) = each($my_prefs)) {
          $my_prefs[$key] = $$key;
        }
      }

      // Save prefs to backend
      include("inc/BlueMamba/save_prefs.php");

      // Display options page again
      $show_page = "options";


      if(!empty($error)) {
        echo "<body>ERROR: $error</body></html>";
        exit;
      }
    }
  }



  $login_success = true;


  // Select Start up Pages
  $main_page = "main.php?folder=INBOX&user=" . $session;
  $preview_page = "images/themes/" . $DOMAIN_THEME . "/welcome/welcome.php?user=" . $session . "&theme=" . $my_prefs['theme'];

  if($show_page) {
    $main_page = $show_page . ".php?user=" . $session . "&to=" . $mailclient_to;
  }
  ?>
  <html>
    <FRAMESET COLS="200,*" frameborder=no border=0 framespacing=0 MARGINWIDTH="0" MARGINHEIGHT="0">
      <FRAME SRC="folders.php?user=<?php echo $session; ?>" NAME="list1" MARGINWIDTH=5 MARGINHEIGHT=5 NORESIZE frameborder=no border=0 framespacing=0>
      <FRAME SRC="<?php echo $main_page; ?>" NAME="list2" MARGINWIDTH=10 MARGINHEIGHT=10 FRAMEBORDER=no border=0 framespacing=0>
    </FRAMESET>
    <noframes>
      You need to download a newer web browser to use Schana Mail.
    </noframes>
  </html>
  <?php
  
}

// Show login form
if(!$login_success) {
  
  if($_COOKIE["MAIL_SESSION"]) {
    setcookie("MAIL_SESSION", "");
  }

  include("$SPLASH_THEME/splash.php");
}

