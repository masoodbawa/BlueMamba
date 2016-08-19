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
              
    Document: source/compose.php
              
    Function: Provide interface for creating messages
              Provide interface for uploading attachments

*********************************************************************/

include_once("inc/BlueMamba/super2global.php");
include_once("inc/BlueMamba/header_main.php");
include_once("inc/BlueMamba/icl.php");
include_once("inc/BlueMamba/version.php");
include_once("inc/BlueMamba/mod_base64.php");
include_once("inc/BlueMamba/compose.php");
include_once("conf/defaults.php");


function showForm(){}

?>
<script language="JavaScript" type="text/javascript" src="wysiwyg/wysiwyg.js"></script>
<form name="messageform" enctype="multipart/form-data" action="compose.php?user=<?=$user?>" method="POST" onSubmit='DeselectAdresses(); close_popup(); return true;' style="display:inline">
	<input type="hidden" name="user" value="<?=$user?>">
	<input type="hidden" name="show_contacts" value="<?=$show_contacts?>">
	<input type="hidden" name="show_cc" value="<?=$show_cc?>">
<?php
if($no_subject)
{
	?><input type="hidden" name="confirm_no_subject" value="1"><?php
}

if(($replyto) || ($in_reply_to))
{
	if(empty($in_reply_to)) 
	{
		$in_reply_to = $folder.":".$uid;
	}
	?>
	<input type="hidden" name="in_reply_to" value="<?php echo $in_reply_to; ?>">
	<input type="hidden" name="replyto_messageID" value="$<?php echo replyto_messageID; ?>">
	<?php
}
elseif(($forward) || ($forward_of))
{
	if(empty($forward_of))
	{
		$forward_of = $folder.":".$uid;
	}
	?><input type="hidden" name="forward_of" value="<?php echo $forward_of; ?>"><?php
}

if(is_array($fwd_att_list))
{
	reset($fwd_att_list);
	while(list($file, $v) = each($fwd_att_list))
	{
		?><input type="hidden" name="<?php echo $fwd_att_list[$file]; ?>" value="1"><?php
	}
}

if(!empty($folder))
{
	?><input type="hidden" name="folder" value="<?=$folder?>"><?php
}

?>

<table border="0" cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td>
      &nbsp;  <span class="bigTitle">Compose Message</span>
    </td>
    <td align="right">
      <input type="submit" class="button" name="savedraft" value="Save as Draft">
      <input type="submit" class="button" name="send" value="Send Message">
    </td>
  </tr>
</table>


<br>

<?php

if(!empty($error))
{
	echo '<font color="red">'.$error.'</font><br>';
}
$to  = encodeUTFSafeHTML($to);
$cc  = encodeUTFSafeHTML($cc);
$bcc = encodeUTFSafeHTML($bcc);

$email_address = htmlspecialchars($original_from);

?>
<table cellspacing="0" cellpadding="3" class="mainLight" width="100%" align="center">
  <tr>
    <td align="right" width="5%">From:</td>
    <td>
      <select name="sender_identity_id" class="textbox" style="width:100%;">
      <option value="-1">
        <?php
        echo LangDecodeSubject($email_address, $CHARSET);

        while(list($key, $ident_a) = each($alt_identities))
        {
          if( $ident_a["name"] != $my_prefs["user_name"] || $ident_a["email"] != $my_prefs["email_address"] )
          {
            echo "<option value=\"$key\" ".($key==$sender_identity_id?"SELECTED":"").">";
            echo "\"".$ident_a["name"]."\"&nbsp;&nbsp;&lt;".$ident_a["email"]."&gt;\n";
          }
        }
        ?>
      </select>
    </td>
  </tr>
  <?php

  $contacts_shown = false;


  // Display to field
  ?>
  <tr>
    <td align="right">To:</td>
    <td>
      <input type="text" class="textbox" style="width:100%;" name="to" value="<?php echo stripslashes($to); ?>" size="<?php echo $WORD_WRAP; ?>">
    </td>
  </tr>
  <tr>
    <td align="right">Subject:</td>
    <td><input type="text" class="textbox" style="width:100%;" name="subject" value="<?php echo encodeUTFSafeHTML(stripslashes($subject)); ?>" size="<?php echo $WORD_WRAP; ?>" onKeyUp="fixtitle('Compose Message');"></td>
  </tr>
  <?php


  // Display cc box
  $cc_field_shown = false;
  if((!empty($cc)) || ($my_prefs["showCC"] == 1) || ($show_cc)) {
    $cc_field_shown = true;
    ?>
    <tr>
      <td align="right">CC:</td>
      <td>
        <input type="text" class="textbox" style="width:100%;" name="cc" size="<?php echo $WORD_WRAP; ?>" value="<?php echo stripslashes($cc); ?>">
      </td>
    </tr>
    <?php
  }


  // Display bcc box
  $bcc_field_shown = false;
  if((!empty($bcc)) || ($my_prefs["showCC"] == 1) || ($show_cc)) {
    $bcc_field_shown = true;
    ?>
    <tr>
      <td align="right">BCC:</td>
      <td>
        <input type="text" name="bcc" class="textbox" style="width:100%;" size="<?php echo $WORD_WRAP; ?>" value="<?php echo stripslashes($bcc); ?>">
      </td>
    </tr>
    <?php
  }

  ?>
  <tr style="display:none;">
    <td align="right"></td>
    <td valign="top">
      Attach:
      <?php
      if((is_array($uploaded_files)) && (count($uploaded_files)>0)) {
        ?>
        <table border="0" cellspacing="0" cellpadding="5" bgcolor="<?=$my_colors["main_hilite"]?>">
        <?php
        reset($uploaded_files);
        while(list($k, $file) = each($uploaded_files)) {
          $file_parts = explode(".", $file);
          ?>
          <tr bgcolor="<?=$my_colors["main_bg"]?>">
            <td valign="middle"><input type="checkbox" name="attach[<?php echo $file; ?>]" value="1" <?php echo ($attach[$file]==1?"CHECKED":""); ?>></td>
            <td valign="middle"><?php echo mod_base64_decode($file_parts[1]); ?>&nbsp;</td>
            <td valign="middle" class="small"><?php echo mod_base64_decode($file_parts[3]); ?>bytes&nbsp;</td>
            <td valign="middle" class="small">(<?php echo mod_base64_decode($file_parts[2]); ?>)</td>
          </tr>
          <?php
        }
        ?>
        </table>
        <?php
      }

      if($MAX_UPLOAD_SIZE) {
        $max_file_size = $MAX_UPLOAD_SIZE;
      }
      else {
        $max_file_size = ini_get('upload_max_filesize');
      }

      if(eregi("M$", $max_file_size)) {
        $max_file_size = (int) $max_file_size * 1000000;
      }
      elseif(eregi("K$", $max_file_size)) {
        $max_file_size = (int) $max_file_size * 1000;
      }
      ?>
      <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_file_size; ?>">
      <input type="file"   name="userfile" class="textbox">
      <input type="submit" class="button" name="upload" value="Upload">
    </td>
  </tr>
  <tr>
    <td align="center" colspan="2">
      <script src="/js/tinymce/tinymce.min.js" type="text/javascript"></script>
      <script src="/js/tinymce/init.en-us.js" type="text/javascript"></script>
      <textarea name="message" id="message" class="textbox" style="width:100%;" rows="20" cols="<?php echo $WORD_WRAP + 10; ?>" wrap="virtual"><?php echo "\n".encodeUTFSafeHTML($message); ?></TEXTAREA>
    </td>
  </tr>
</table>

</form>
<script type="text/javascript">
  generate_wysiwyg('messageNONE');
</script>

<br>
<script type="text/javascript">
	var _p = this.parent;
	if(_p==this)
	{
		_p.document.title = "Compose Message";
	}
</script>

</body>
</html>