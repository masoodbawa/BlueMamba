<?php
/**
 * Display Folders
 */

include_once("inc/BlueMamba/super2global.php");
include_once("inc/BlueMamba/nocache.php");
include_once("conf/conf.php");


function getFolderStates() {
  global $loginID, $host;

  $data = cache_read($loginID, $host, "folder_states");

  if(!$data) {
    return array("INBOX");
  }
  else {
    return $data;
  }
}



function saveFolderStates($folders) {
  global $loginID, $host;

  $result = cache_write($loginID, $host, "folder_states", $folders, false);
  return $result;
}



function removeFolders($array) {
  if((!is_array($array)) || (count($array) == 0)) {
    return true;
  }

  $current = getFolderStates();
  if(is_array($current)) {
    $save = array();
    while(list($k, $folder) = each($current)) {
      if(!in_array($folder, $array)) {
        $save[] = $folder;
      }
    }
    saveFolderStates($save);
  }
}



function addFolders($array) {
  if((!is_array($array)) || (count($array) == 0)) {
    return true;
  }

  $current = getFolderStates();
  if(is_array($current)) {
    $save = array_merge($current, $array);
    sort($save);
    saveFolderStates($save);
  }
}



function InArray($array, $item) {
  if(!is_array($array)) {
    return false;
  }
  elseif(strcasecmp($item, "inbox") == 0) {
    return false;
  }
  else {
    return in_array($item, $array);
  }
}



function ChildInArray($array, $item) {
  if(!is_array($array)) {
    return false;
  }
  reset($array);
  while(list($k, $v) = each($array)) {
    $pos = strpos($v, $item);
    if(($pos !== false) && ($pos == 0)) {
      return true;
    }
  }
  return false;
}



function IndentPath($path, $containers, $delim) {
  $containers->reset();
  $pos = strrpos($path, $delim);
  if($pos > 0) {
    $folder = substr($path, $pos);
    $path = substr($path, 0, $pos);
  }

  do {
    $container = $containers->next();
    if($container) {
      $path = str_replace($container, "&nbsp;&nbsp;&nbsp;", $path);
    }
  }
  while($container);

  return $path . $folder;
}

if(empty($user)) {
  echo "User unspecified.";
  exit;
}
else {
  include_once("inc/BlueMamba/session_auth.php");
	include_once("inc/BlueMamba/global_func.php");
	include_once("inc/BlueMamba/icl.php");
	include_once("inc/BlueMamba/stack.php");
	include_once("inc/BlueMamba/cache.php");

	?>
	<html>
	<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <link type="text/css" rel="stylesheet" href="/css/main.css">
	</head>
	<body style="margin:0px !important;">

	<div style="background:#222; color:#fff; font-size:14px; text-align:left; padding:13px 15px 10px 15px;">
    <img src="<?php echo $THEME; ?>folder_logo.png" style="padding-bottom:3px;" align="absmiddle" border="0">
    Mail
  </div>


	<table width="100%" border="0" cellspacing="0" cellpadding="10" class="folderList">
		<tr>
			<td>
				<?php

				$conn = iil_Connect($host, $loginID, $password, $AUTH_MODE);
          if($conn) {

            // Handle emptry_trash request
            if($empty_trash) {
              iil_C_ClearFolder($conn, "Trash");
            }

            // Get list of mailboxes
            cache_clear($loginID, $host, "folders");
            $folders = iil_C_ListMailboxes($conn, $ROOTDIR, "*");
            cache_write($loginID, $host, "folders", $folders);


            if(!is_array($folders)) {
              echo "<b>Failed:</b> " . $conn->error . "<br>\n";
            }
            else {

              // Get hierarchy delimiter, usually '/' or '.'
              $delim = iil_C_GetHierarchyDelimiter($conn);

              // Get list of container folders, because some IMAP server won't return them
              // e.g.  container of "folder/sub" is "folder"
              $folder_container = array();
              $containers = array();
              reset($folders);
              while(list($k, $path) = each($folders)) {
                while(false !== ($pos = strrpos($path, $delim))) {
                  $container = substr($path, 0, $pos);
                  if($containers[$container] != 1)
                    $containers[$container] = 1;
                  $folder_container[$path] = $container;
                  $path = substr($path, 0, $pos);
                }
              }

              // Make sure containers are in folder list
              reset($containers);
              while(list($container, $v) = each($containers)) {
                if(!InArray($folders, $container)) {
                  array_push($folders, $container);
                }
              }
              asort($folders);

              // Handle subscribe (expand) command
              if($subscribe) {
                // Subscribe folder...
                $add_list = array();
                $v_sub[] = $folder;
                $add_list[] = $folder;

                // And immediate sub-folders
                $folder .= $delim;
                reset($folders);
                while(list($k, $v) = each($folders)) {
                  $pos = strpos($v, $folder);
                  if(($pos !== false) && ($pos == 0)) {
                    $pos = strrpos($v, $delim);
                    if($pos <= strlen($folder)) {
                      $v_sub[] = $v;
                      $add_list[] = $v;
                    }
                  }
                }
                if(count($add_list) > 0) {
                  addFolders($add_list);
                }
              }

              // Get list of subscribed (expanded) folders
              $subscribed = getFolderStates();

              // Make sure they exist (might've been deleted)
              $temp_subs = array();
              reset($subscribed);
              while(list($k, $path) = each($subscribed)) {
                if(in_array($path, $folders)) {
                  $temp_subs[] = $path;
                }
              }
              $subscribed = $temp_subs;

              // With some servers, only container folders are ignored, so we need to
              // Do it the inefficient way...
              if(is_array($subscribed)) {
                // make sure the container of every subscribed folder is also in list
                reset($subscribed);
                while(list($k, $path) = each($subscribed)) {
                  // Make sure every folder in path to subscribed folder is also subscribed.
                  $original_path = $path;
                  while(false !== ($pos = strrpos($path, $delim))) {
                    $container = substr($path, 0, $pos);
                    if(!in_array($container, $subscribed)) {
                      $v_sub[] = $container;
                    }
                    $path = substr($path, 0, $pos);
                  }

                  // Make sure all folder at same level as subscribed folders are subscribed
                  $path = $original_path;
                  if(false !== ($pos = strrpos($path, $delim))) {
                    $container = substr($path, 0, $pos);
                    if(!$checked_container[$container]) {
                      reset($folders);
                      while(list($k2, $folder) = each($folders)) {
                        // Is "folder" inside "container"?
                        $pos = strpos($folder, $container);
                        if(($pos !== false) && ($pos == 0)) {
                          // Is $folder immediately inside $container, or further down?
                          $pos = strrpos($folder, $delim);
                          if($pos <= strlen($container . $delim)) {
                            if(!InArray($subscribed, $folder)) {
                              //*gasp*!  $folder is not subscribed!
                              $v_sub[] = $folder;
                            }
                          }
                        }
                      }
                      $checked_container[$container] = 1;
                    }
                  }
                }
              }


              if(is_array($v_sub)) {
                while(list($k, $v) = each($v_sub)) {
                  if(!in_array($v, $subscribed)) {
                    $subscribed[] = $v;
                  }
                }
              }

              if(is_array($subscribed)) {
                sort($subscribed);
                reset($subscribed);
              }

              natcasesort($folders);
              $c = sizeof($folders);
              echo "<NOBR>";

              // Show default folders (i.e. Inbox, Sent, Trash)
              $unseen_str = "";

              $defaults["INBOX"] = "Inbox";
              $defaults["Drafts"] = "Drafts";
              $defaults["Sent"] = "Sent";
              $defaults["Spam"] = "Spam";
              $defaults["Trash"] = "Trash";

              reset($defaults);
              ?>
            <a href="compose.php?user=<?php echo $user; ?>" target="list2" style="width:125px;text-align:center;padding:7px;margin:7px;display:table;background-color:#0174DF;color:#fff;border-radius:5px;">Compose</a>

						<table border="0" cellpadding="0" cellspacing="5" class="folderList">
							<?php
              while(list($key, $value) = each($defaults)) {
                if(($value != ".") && (!empty($key))) {
                  if($my_prefs["showNumUnread"]) {
                    $num_unseen = iil_C_CountUnseen($conn, $key);
                    $unseen_str = "";
                    if($num_unseen > 0) {
                      $unseen_str = "&nbsp;(" . $num_unseen . ")";
                    }
                  }
                  ?>
                  <tr>
                    <td valign="bottom">
                      <a href="main.php?folder=<?php echo $key . "&user=" . $user; ?>" target="list2">
                        <img src="<?php echo $THEME . strtolower($value); ?>.gif" width="16" height="14" border="0"></a>
                    </td>
                    <td valign="middle">
                      <a href="main.php?folder=<?php echo $key . "&user=" . $user; ?>" target="list2"><?php echo $value; ?></a>
                        <?php echo $unseen_str; ?>
                    </td>
                  </tr>
                  <?php
                }
              }
              ?>
						</table>
						<br>
						<?php

						// Indent according to depth
            $result = array();
            reset($folders);
            while(list($k, $path) = each($folders)) {
              // We're only going to display folders that are in...
              // Root level, subscribed, or in "INBOX"
              if(($folder_container[$path] == $ROOTDIR) || (InArray($subscribed, $path))) {

                $a = explode($delim, $path);
                $c = count($a);
                $folder = $a[$c - 1];
                if(strcmp($a[0], $ROOTDIR) == 0) {
                  $c--;
                }
                if(($path[0] != ".") && ($folder[0] != ".")) {
                  for($i = 0; $i < ($c - 1); $i++) {
                    $indent[$path] .= "&nbsp;&nbsp;";
                  }
                  $result[$path] = $folder;
                }
              }
            }

            flush();

            $blank_img = '<img src="' . $THEME . 'blank.gif" width="16" height="14" border="0">';
            $open_img = '<img src="' . $THEME . 'folder_open.gif" width="16" height="14" border="0">';
            $close_img = '<img src="' . $THEME . 'folder_close.gif" width="16" height="14" border="0">';

            // Display folders
            reset($result);
            ?>
						<table border="0" cellpadding="0" cellspacing="7" class="folderList">
							<tr>
								<td>
									<?php
									while(list($path, $display) = each($result)) {
										if( (!empty($display)) && (($containers[$path]) || (empty($defaults[$path]))) ) {
											$key = $path;
											if($containers[$path]) {
												$is_sub  = ChildInArray($subscribed, $path.$delim);
												$button  = "<a href=\"folders.php?user=$user&".($is_sub?"unsubscribe":"subscribe")."=1&folder=".urlencode($path)."\" target=\"list1\">";
												$button .= ($is_sub?"$open_img":"$close_img") . "</a>";
											}
											else {
												$button = $close_img;
											}
											echo "<span style=\"font-size: ".$my_colors["font_size"]."; color: ".$my_colors["folder_bg"]."\"><tt>".$indent[$key]."</tt></span>";
											echo $button;

											$unseen_str="";
											if($my_prefs["showNumUnread"]) {
												$num_unseen = iil_C_CountUnseen($conn, $path);
												if($num_unseen > 0) $unseen_str = "&nbsp;(".$num_unseen.")";
											}

											$path    = stripslashes($path);
											$display = stripslashes($display);
											$path    = urlencode($path);
											echo " <a href=\"main.php?folder=$path&user=".$user."\" target=\"list2\">".$display.$unseen_str."</a><BR>\n";
											flush();
										}
									}
									?>
								</td>
							</tr>
						</table>
					<?php

					}
					iil_Close($conn);
				}
				?>
        <br>
            
        <div style="padding:5px;">
          <a href="search_form.php?user=<?php echo $user; ?>" target="list2">Search</a><br>
          <a href="options.php?user=<?php echo $user; ?>" target="list2">Settings</a><br>
          <br>
          <a href="logout.php?logout=1&user=<?=$user?>" target="_parent">Logout</a>
        </div>

			</td>
		</tr>
	</table>
	</body>
	</html>
<?php
}
?>