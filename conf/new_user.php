<?php

include_once(DOCUMENT_ROOT . "/inc/BlueMamba/icl.php");
include_once(DOCUMENT_ROOT . "/inc/BlueMamba/cache.php");
include_once(DOCUMENT_ROOT . "/inc/BlueMamba/data_manager.php");


// Perform New User Setup
if(!strstr($user, "@")) {
  $user = $user . "@" . $DOMAIN_THEME;
  $user_name = $user;
}



// Initialize Contacts 
$dm = new DataManager_obj;
if($dm->initialize($loginID, $host, $DB_CONTACTS_TABLE, $DB_TYPE)) {
  
  $new_contact_array = array (
    "owner" => $session_dataID,
    "name" => "Schanaco Support",
    "email" => "support@schanaco.com",
    "phone" => "",
    "work" => "",
    "url" => "http://www.schanaco.com"
  );

  !$dm->insert($new_contact_array);
}
unset($dm);
?>