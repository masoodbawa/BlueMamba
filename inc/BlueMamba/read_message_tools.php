<?php
/**
 * Function: Read Message Tools
 */

$read_message_tools_counter++;

$folder_url = urlencode($folder);


$target = "list2";
if($my_prefs["preview_window"] == 1)		// Build Targets
{
	$target = "preview";
}
else
{
	if($my_prefs["compose_inside"] != 1)
	{
		$target = "scr" . $user . $folder_url . $id;
	}
}

?>
<table border="0" width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>

			<table border="0" cellpadding="0" cellspacing="0" class="mainToolBar">
				<tr>
					<td>&nbsp;</td>
			
						<?php
					// Previouse Link
					if(!empty($prev_link))
					{
						echo "<td>". $prev_link ."</td>";
					}
					
					// Folder Name
					if($my_prefs["view_inside"])
					{
						echo "<td> &nbsp; <a href=\"main.php?user=$user&folder=$folder&start=$start&sort_field=$sort_field&sort_order=$sort_order\" target=\"list2\" class=bigTitle>";
						$folder_name = $defaults[$folder];
						if(empty($folder_name))
						{
							$delim = iil_C_GetHierarchyDelimiter($conn);
							$pos = strrpos($folder, $delim);
							if($pos!==false) $pos++;
							$folder_name = substr($folder, $pos);
						}
				
						// Lowercase inbox
						$folder_name = str_replace("INBOX", "Inbox", $folder_name);
						echo $folder_name . "</a> &nbsp; </td>";
					}
					
					// Next Link
					if(!empty($next_link))
					{
						echo "<td>". $next_link ."</td>";
					}
					
					echo "<td>&nbsp; &nbsp; &nbsp;</td>";
					
					if($folder_name == "Drafts")
					{
						// Compose from Draft
						$href = "<a href=\"compose.php?user=$user&draft=1&folder=$folder_url&id=$id&uid=$uid&part=$part\" target=\"$target\" class=\"\">";
						echo "<td><img src=\"" . $THEME . "compose_draft.gif\" border=\"0\" height=14>&nbsp;" . $href . "Compose from Draft</a></td>";
					}
					elseif($folder_name == "Unsent")
					{
						// Resend
						$href  = "<a href=\"compose.php?user=$user&unsent=1&folder=$folder_url&id=$id&uid=$uid&part=$part\" target=\"$target\" class=\"\">";
						echo "<td>" . $href . "<img src=\"" . $THEME . "resend.gif\" border=\"0\" height=14>&nbsp;Resend Message</a></td>";
					}
					else
					{
					
						// Reply
						$href = "<a href=\"compose.php?user=$user&replyto=1&folder=$folder_url&id=$id&uid=$uid&part=$part\" target=\"$target\" class=\"\">";
						echo "<td>" . $href . "<img src=\"" . $THEME . "reply.gif\" border=\"0\" height=14>&nbsp;Reply</a></td>";
						
						echo "<td>&nbsp;|&nbsp;</td>";
						
						// Reply All
						if($multiple_recipients == true)
						{
							$href = "<a href=\"compose.php?user=$user&replyto=1&replyto_all=1&folder=$folder_url&id=$id&uid=$uid&part=$part\" target=\"$target\" class=\"\">";
							echo "<td>" . $href . "<img src=\"" . $THEME . "replyall.gif\" border=\"0\" height=14>&nbsp;Re. To All</a></td>";
					
							echo "<td>&nbsp;|&nbsp;</td>";
						}
						
						// Forward
						$href = "<a href=\"compose.php?user=$user&forward=1&folder=$folder_url&id=$id&uid=$uid&part=$part\" target=\"$target\" class=\"\">";
						echo "<td>" . $href . "<img src=\"" . $THEME . "forward.gif\" border=\"0\" height=14>&nbsp;Forward</a></td>";

					}
					echo "<td>&nbsp;|&nbsp;</td>";
					
					// Delete
					if(!$header->deleted) 
					{
						$href = "<a href=\"main.php?user=$user&folder=$folder_url&checkboxes[]=$id&uids[]=$uid&submit=Delete&start=$start\" target=\"list2\" class=\"\">";
						echo "<td>" . $href . "<img src=\"" . $THEME . "delete.gif\" border=\"0\" height=14>&nbsp;Delete</a></td>";
					}
					
					if( ($folder_name != "Drafts") && ($folder_name != "Unsent") )
					{
						echo "</td><td>&nbsp;|&nbsp;</td><td>";
					
						// Unread
						$href = "<a href=\"main.php?user=$user&folder=$folder_url&checkboxes[]=$id&uids[]=$uid&submit=Unread&start=$start\" target=\"list2\" class=\"\">";
						echo "<td>" . $href . "<img src=\"" . $THEME . "unread.gif\" border=\"0\" height=14>&nbsp;Unread</a></td>";
					}
					
					?>	
				</tr>
			</table>
	
		</td>
		<td align="right" valign="top">
	
		<form method="POST" action="main.php" style="display:inline"<?php if($my_prefs["preview_window"] == 1) {echo " target=\"list2\"";} ?>>
			<input type="hidden" name="user" value="<?=$user?>">
			<input type="hidden" name="folder" value="<?=$folder?>">
			<input type="hidden" name="checkboxes[]" value="<?=$id?>">
			<input type="hidden" name="uids[]" value="<?php echo $uid; ?>">
			<input type="hidden" name="start" value="<?php echo $start; ?>">
			<input type="hidden" name="max_messages" value="<?php echo ($id+1); ?>">
			<?php

			if(!is_array($folderlist))
			{
				$cached_folders = cache_read($loginID, $host, "folders");
				if(is_array($cached_folders))
				{
					$folderlist = $cached_folders;
				}
				else
				{
					if($my_prefs["hideUnsubscribed"]) $folderlist = iil_C_ListSubscribed($conn, $ROOTDIR, "*");
					else $folderlist = iil_C_ListMailboxes($conn, $ROOTDIR, "*");
					$cache_result = cache_write($loginID, $host, "folders", $folderlist);
				}
			}

			?>
			<select name="moveto<?php echo $read_message_tools_counter; ?>">
				<option value=""></option>
				<?php 
				sort($folderlist);
				reset($folderlist);
				while(list($k, $folder2) = each($folderlist))
				{
					echo '<option value="' . $folder2 . '">' . cleanfolder($folder2) . "</option>\n";
				}
				?>
			</select>
			<input type="submit" class="button" name="submit" value="Move">
		</form>

		</td>
	</tr>
</table>