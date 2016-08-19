<?php


define("DOCUMENT_ROOT", "/cust/schanaco.com/mail");
  
// Global Conf
$ROOTDIR            = "";
$CHARSET            = "ISO-8859-1";
$STAY_LOGGED_IN     = true;
$TRUST_USER_ADDRESS = true;

// General Conf
//$DATE_FORMAT = "D, M jS Y h:i A";
$DATE_FORMAT = "m/d/Y h:i A";


// Theme Settings, Use Server Name
$DEFAULT_THEME = "schanaco.com";
$DOMAIN_THEME  = "";	// Leave Blank to Auto Select
$SPLASH_THEME  = "login";


// Users Directories
$UPLOAD_DIR     = DOCUMENT_ROOT . "/docs/uploads/";
$CACHE_DIR      = DOCUMENT_ROOT . "/data/cache/";
$USER_DIR       = DOCUMENT_ROOT . "/data/users/";
$SESSION_DIR    = DOCUMENT_ROOT . "/data/sessions/";


// Outgoing Mail
$SMTP_SERVER    = "mail.yourdomain.com";
$SMTP_TYPE      = "sendmail";
$SMTP_USER      = "";
$SMTP_PASSWORD  = "";


// Login Host
$LOGIN_HOST     = "mail.yourdomain.com";
$LOGIN_PORT     = "993";


// Authintication Mode
$AUTH_MODE["imap"] = "plain";
$AUTH_MODE["pop3"] = "plain";
$AUTH_MODE["smtp"] = "";


// Dictionary
$CHECK_SPELLING = false;
$SPELLING_LANG  = "en";
$ASPELL_PATH    = "/usr/bin/aspell";


// Spam Prevention
$max_rcpt_message  = 50;
$max_rcpt_session  = 100;
$min_send_interval = 15;
$report_spam_to    = "";


$MAX_EXEC_TIME          = 60;
$MAX_SESSION_TIME       = (60 * 60 * 24);
$MIN_FOLDERLIST_REFRESH = 10;
$MIN_RADAR_REFRESH      = 10;
$MAX_UPLOAD_SIZE        = 0;
$WORD_WRAP              = 74; // Return line after 74 chars


// Tag added to outgoing mail 'Made with webmail!'
$TAG_LINE = "";



/*********************************************************************
    DO NOT MODIFY BELOW HERE
*********************************************************************/

// Get Users Home Directory
$user_info_array = posix_getpwnam("$loginID");
$USER_BASE_DIR   = $user_info_array['dir'] . "/";

// If Domain Theme isn't set then select it based on server name
if(!$DOMAIN_THEME)
{
	$DOMAIN_PARTS = explode(".", $_SERVER['SERVER_NAME']);
	if(count($DOMAIN_PARTS) == 3)	// Full domain is present
	{
		$DOMAIN_THEME = $DOMAIN_PARTS[1] . "." . $DOMAIN_PARTS[2];
	}
	elseif(count($DOMAIN_PARTS) == 2) // No host on domain name
	{
		$DOMAIN_THEME = $DOMAIN_PARTS[0] . "." . $DOMAIN_PARTS[1];
	}
	
	
	if(!is_file(DOCUMENT_ROOT . "/images/themes/$DOMAIN_THEME/conf.php")) {
		$DOMAIN_THEME = $DEFAULT_THEME;
	}
}

// Load Domain Theme Configs
include_once(DOCUMENT_ROOT . "/images/themes/$DOMAIN_THEME/conf.php");

$SPLASH_THEME = DOCUMENT_ROOT . "/images/themes/$DOMAIN_THEME/$SPLASH_THEME";
