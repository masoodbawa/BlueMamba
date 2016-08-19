<?php

if(!$port)
  $port = 143;


if($port == 143) {  // IMAP
  $protocol = "IMAP";
  $ICL_SSL = false;
  include(dirname(__FILE__) . "/imap.php");
}

if($port == 993) {  // IMAPS
  $protocol = "IMAP";
  $ICL_SSL = true;
  include(dirname(__FILE__) . "/imap.php");
}

$ICL_PORT = $port;
?>