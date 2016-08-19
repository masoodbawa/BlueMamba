<?php
/**
 * Config
 */

session_start();
date_default_timezone_set('America/Los_Angeles');
header('Content-Type: text/html; charset=utf-8');

error_reporting(E_ALL & ~E_NOTICE);
$APP_SERVER = getenv("APP_SERVER");
if($APP_SERVER == "development") {
  define("SITE_BRAND", "schanaco");
  define("SITE_ROOT", "http://localhost/");
  define("SECURE_ROOT", "http://localhost/");
  define("DOMAIN_ROOT", "/cust/schanaco.com");
  define("DOCUMENT_ROOT", "/cust/schanaco.com/mail");
  define("DB_NAME", "apps");
  define("DB_USER", "root");
  define("DB_PASS", "password");
  define("DB_HOST", "localhost");
  define("DB_PORT", null);
  define("DB_SSL", false);
  define("DEBUG", true);
}
else {
  define("SITE_BRAND", "schanaco");
  define("SITE_ROOT", "http://mail.schanaco.com/");
  define("SECURE_ROOT", "https://mail.schanaco.com/");
  define("DOMAIN_ROOT", "/cust/schanaco.com");
  define("DOCUMENT_ROOT", "/cust/schanaco.com/mail");
  define("DB_NAME", "apps");
  define("DB_USER", "root");
  define("DB_PASS", "password");
  define("DB_HOST", "localhost");
  define("DB_PORT", null);
  define("DB_SSL", false);
  define("DB_SSL_CLIENT_KEY", DOMAIN_ROOT . "/ssl/mysqlclient-key.pem");
  define("DB_SSL_CLIENT_CERT", DOMAIN_ROOT . "/ssl/mysqlclient-cert.pem");
  define("DB_SSL_CA_CERT", DOMAIN_ROOT . "/ssl/mysqlca-cert.pem");
  define("DEBUG", false);
}


/**
 * Universal Site Constants
 */
define("ADMIN_EMAIL", "schanaco@protonmail.ch");
define("DEV_EMAIL", "schanaco@protonmail.ch");
define("SESSION_TIME", "30");


/**
 * Brand Site Constants
 */
if(SITE_BRAND == "schanaco") {
  define("COMPANY_NAME", "Schanaco Ltd");
  define("COMPANY_ADDRESS", "Rm 701-2, 7/F, Fu Fai Commercial Centre, 27 Hillier Street, Sheung Wan, Hong Kong");

  define("SITE_TITLE", "Schanaco Apps");
  define("SITE_EMAIL", "support@schanaco.com");
  define("SITE_LINK", "http://www.schanaco.com");
  define("SITE_TERMS", "http://www.schanaco.com/legal");
  define("SITE_COPYRIGHT", "Copyright &copy; " . date("Y") . " <a href=\"" . SITE_LINK . "\" target=\"_blank\">" . COMPANY_NAME . "</a>, All Rights Reserved.");

  define("LOGO_LOGIN", "images/brand/schanaco/login-logo.png");
  define("LOGO_SIGNUP", "images/brand/schanaco/email-logo.png");
  define("LOGO_HEADER", "images/brand/schanaco/logo.png");
  define("LOGO_EMAIL", "images/brand/schanaco/email-logo.png");
  define("LOGO_POWEREDBY", "images/brand/schanaco/powered-by-logo.png");

  define("DEFAULT_LANG", "en-us");
}


/**
 * Mail SMTP
 */
define("SMTP_USER", "postmaster@mg.schanaco.com");
define("SMTP_PASS", "be26b9fa763dfc44b13ca24215cb591d");
define("SMTP_HOST", "smtp.mailgun.org");
define("SMTP_PORT", "587");
define("SMTP_SSL", true);
define("SMTP_DEBUG", 0); // 1 = errors and messages / 2 = messages only


/**
 * Init Database
 */
include_once(DOCUMENT_ROOT . '/inc/db.php');
DB::connect(DB_NAME, DB_USER, DB_PASS, DB_HOST);

/**
 * Language
 */
include_once(DOCUMENT_ROOT . '/inc/language.php');
if(!empty($_REQUEST['default_lang'])) {
  $DEFAULT_LANG = addslashes($_REQUEST['default_lang']);
}
elseif(!empty($_REQUEST['lang'])) {
  $DEFAULT_LANG = addslashes($_REQUEST['lang']);
}
else {
  $DEFAULT_LANG = DEFAULT_LANG;
}
Lang::set($DEFAULT_LANG);

/**
 * Autoload Classes
 */
include_once(DOCUMENT_ROOT . '/inc/autoload.php');
$loaddir[] = DOCUMENT_ROOT . '/inc/Base';
$loaddir[] = DOCUMENT_ROOT . '/inc/Master';
$loaddir[] = DOCUMENT_ROOT . '/inc/Utilities';
$loaddir[] = DOCUMENT_ROOT . '/inc/Reports';
$loaddir[] = DOCUMENT_ROOT . '/inc/Data';
$loaddir[] = DOCUMENT_ROOT . '/inc/Interfaces';
autoload($loaddir);

/**
 * Includes
 */
include_once(DOCUMENT_ROOT . '/inc/functions.php');
if($SKIP_LOGIN !== true && $CRON_JOB !== true && $CGI !== true) {
  include_once(DOCUMENT_ROOT . '/inc/session.php');
}

/**
 * Smarty
 */
require(DOCUMENT_ROOT . '/inc/smarty/SmartyBC.class.php');
$smarty = new SmartyBC;
$smarty->template_dir = DOCUMENT_ROOT . '/templates';
$smarty->compile_dir = DOCUMENT_ROOT . '/templates_c';
if($APP_SERVER == "development") {
  $smarty->compile_dir = DOMAIN_ROOT . '/templates_c';
}

/**
 * CGI
 */
if($CGI === true) {
  include_once(DOCUMENT_ROOT . '/inc/cgi.php');
}

/**
 * Logged In
 */
if(($CRON_JOB !== true && $SKIP_LOGIN !== true) || $CGI == true) {

  /**
   * Set Language and Load Base Language
   */
  if($_SESSION['loggedin'] == true || $CGI == true) {
    Lang::set($member->info->language);
    $base_lang = Lang::load('base', null, false);
    $smarty->assign("base_lang", $base_lang);
  }

  /**
   * Load Menu
   */
  if($_SESSION['loggedin'] == true) {
    $smarty->assign("menu_lang", (array) Lang::load('menu'));
    
    $menu_sql = ' (userpermission_id >= ' . (int) $user->info->userpermission_id . ' OR ISNULL(userpermission_id)) 
                  AND active = 1 ORDER BY sortorder ';
    $smarty->assign("menu", DB::loadAll('menu', $menu_sql));
    
  }

  /**
   * Load Member and User data to smarty
   */
  $smarty->assign("member", $member->info);
  $smarty->assign("user", $user->info);
  $smarty->assign("app_server", $APP_SERVER);

  /**
   * Set Currency format on selected payment currency
   */
  if(!empty($member->info->paymentcurrency)) {
    $sql = "SELECT code, symbol 
              FROM `currency` 
              WHERE code = '" . addslashes($member->info->paymentcurrency) . "'";
    $currency = DB::getRow($sql);
    define("CURRENCY_CODE", $currency['code']);
    define("CURRENCY_SYMBOL", $currency['symbol']);
  }
  else {
    define("CURRENCY_CODE", "USD");
    define("CURRENCY_SYMBOL", "$");
  }
  
  
  }
else {
  
  /**
   * Assign Default Language
   */
  $smarty->assign("default_lang", $DEFAULT_LANG);
  $base_lang = Lang::load('base', null, false);
  $smarty->assign("base_lang", $base_lang);
}

