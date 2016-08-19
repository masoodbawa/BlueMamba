<?php

if(!$loginID) {
  $loginID = $user;
}
$default_email = $loginID . (strstr($loginID, "@") ? "" : "@" . $DOMAIN_THEME);

$default_prefs = array (
    "colorize_quotes" => "1",
    "detect_links" => "1",
    "view_max" => "15",
    "show_size" => "1",
    "delete_trash" => "0",
    "user_name" => $default_email,
    "email_address" => $default_email,
    "signature1" => "",
    "show_sig1" => "0",
    "sort_field" => "DATE",
    "sort_order" => "DESC",
    "list_folders" => "1",
    "view_inside" => "1",
    "preview_window" => "0",
    "timezone" => "-7",
    "html_in_frame" => "0",
    "show_images_inline" => "1",
    "subject_edit" => "0",
    "advanced_controls" => "0",
    "showContacts" => "2",
    "showCC" => "1",
    "closeAfterSend" => "1",
    "showNav" => "1",
    "compose_inside" => "1",
    "showNumUnread" => "1",
    "refresh_folderlist" => "1",
    "folderlist_interval" => "3",
    "radar_interval" => "3",
    "theme" => "default",
    "notify" => "default",
    "alt_identities" => "",
    "main_cols" => "camsfdz",
    "main_toolbar" => "bt",
    "nav_no_flag" => "0",
    "filters" => "0"
);

