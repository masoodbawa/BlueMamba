<?php
/**
 *  General Functions
 */

/**
 * Handle any uncaught exceptions
 * @param type $e
 */
set_exception_handler('default_exception_handler');
function default_exception_handler($e) {
  
  $APP_SERVER = getenv("APP_SERVER");

  $details = 'Details: '; 
  $details .= print_r(array( 
    'Server' => $APP_SERVER,
    'Request' => $_REQUEST,
    'Session' => $_SESSION,
    'Cookie' => $_COOKIE,
    'Server' => $_SERVER
  ), true);
  
  $message = "<pre>" . $e->getMessage() . "\n" . $details . "</pre>";
  
  // Email error to developer
  if(DEBUG) {
    print_r($message);
  } 
  else {
    Email::send(
      DEV_EMAIL, null, 
      SITE_EMAIL, SITE_TITLE, 
      SITE_TITLE . " Exception Error", 
      $message
    );

    // Display notice
    global $base_lang;
    display_error($base_lang->error->exception);
  }
  exit;
}

/**
 * Display Errors
 * @global type $smarty
 * @param type $message
 */
function display_error($message, $error_template = 'main.tpl') {
  global $smarty;
  
  // Display notice
  $smarty->assign("content", '<div style="text-align:center;">' . $message . '</div>');
  $smarty->display($error_template);
  exit;
}


/**
 * JSON Responce
 */
function json_responce($data_array, $exit = true) {
  print_r(json_encode($data_array)); 
  if($exit == true) {
    exit;
  }
}


/**
 * Generate random string
 * @return type
 */
function random_string($length = 32) {

  $xk = "";
  for ($xi = 0; $xi < $length; $xi++) {
    $switch = rand(0, 1);
    if ($switch == 1) {
      $xk .= chr(rand(97, 122));
    } else {
      $xk .= rand(0, 9);
    }
  }

  return $xk;
}

/**
 * Generate random key
 * @return type
 */
function random_key($length = 32) {
  $xk = "";
  for($xi = 0; $xi < $length; $xi++) {
    $switch = mt_rand(0, 2);
    if($switch == 1) {
      $xk .= chr(mt_rand(65, 90));
    }
    elseif($switch == 2) {
      $xk .= chr(mt_rand(97, 122));
    }
    else {
      $xk .= mt_rand(0, 9);
    }
  }
  return $xk;
}


/**
 * Remove any unwanted characters in a username
 * @param type $string
 * @return type
 */
function clean_username($string) {
  // Items to remove from string
  $remove_char = array(
    " ", "'", "~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "=", "¯", "_",
    "-", "+", "/", "\\", "|", "{", "}", "[", "]", ":", ";", "\"", "?", "<", ">", chr(13)
  );

  // Remove each specified item
  foreach ($remove_char as $value) {
    $string = str_replace($value, "", $string);
  }

  return $string;
}


/**
 * Converts normal ascii characters into html
 * @param type $string
 * @return type
 */
function htmlencode($string) {
  $string = stripslashes($string);

  $string = str_replace(chr(13), " <br> ", $string);
  $string = str_replace("&", "&amp;", $string);

  return $string;
}


/**
 * Format Currency
 * @param type $number
 * @return type
 */
function smarty_modifier_format_currency($number) {
  if(CURRENCY_CODE == "GBP") {
    $number = number_format((float) $number, 2, ",", ".");
  } else {
    $number = number_format((float) $number, 2, ".", ",");
  }
  return CURRENCY_SYMBOL . $number; 
}
function format_currency($number) {
  return smarty_modifier_format_currency($number);
}


/**
 * Format Phone Number
 * @param type $number
 * @return type
 */
function smarty_modifier_format_phone($number) { 
  $number = preg_replace("/\D/","",$number); 
  global $member;
  
  if($member->info->country == "US") {
    $number = format_phone_us($number);
  }
  elseif($member->info->country == "HK") {
    $number = format_phone_hk($number);
  }
  elseif($member->info->country == "CL") {
    $number = format_phone_cl($number);
  }
  else {
    $number = format_phone_hk($number);
  }

  return $number; 
}
function format_phone($number) {
  return smarty_modifier_format_phone($number);
}

/**
 * USA Number Format 
 * +1 (111) 111-1111
 */
function format_phone_us($number) {
  $length = strlen($number);
  if($length == 10) {
    $number = sprintf(
      "(%s) %s-%s", 
      substr($number,-10,3), 
      substr($number,-7,3), 
      substr($number,-4,4) 
    );
  }
  elseif($length > 10) {
    $number = sprintf(
      "+%s (%s) %s-%s", 
      substr($number, ($length * -1),($length - 10)), 
      substr($number,-10,3), 
      substr($number,-7,3), 
      substr($number,-4,4) 
    );
  } 
  elseif ($length < 10 && $length >= 8) {
    $number = sprintf(
      "%s %s-%s", 
      substr($number,($length * -1),($length - 7)), 
      substr($number,-7,3), 
      substr($number,-4,4) 
    );
  }
  elseif ($length == 7) {
    $number = sprintf(
      "%s-%s",
      substr($number,-7,3), 
      substr($number,-4,4) 
    );
  }
  return $number; 
}

/**
 * Hong Kong Number Format 
 * +852 1111 1111
 */
function format_phone_hk($number) { 
  $length = strlen($number);
  if($length > 8) {
    $number = sprintf(
      "+%s %s %s",
      substr($number, 0,($length - 8)), 
      substr($number,-8,4), 
      substr($number,-4,4) 
    );
  } 
  elseif ($length == 8) {
    $number = sprintf(
      "%s %s",
      substr($number,-8,4), 
      substr($number,-4,4) 
    );
  }
  return $number; 
}

/**
 * Chile Number Format 
 * +56 1 11111111
 */
function format_phone_cl($number) { 
  $length = strlen($number);
  if($length > 9) {
    $number = sprintf(
      "+%s %s %s",
      substr($number, 0,($length - 9)), 
      substr($number,-9,1), 
      substr($number,-8,8) 
    );
  } 
  elseif ($length == 9) {
    $number = sprintf(
      "%s %s",
      substr($number,-9,1), 
      substr($number,-8,8) 
    );
  }
  elseif ($length == 8) {
    $number = sprintf(
      "%s",
      substr($number,-8,8) 
    );
  }
  return $number; 
}



