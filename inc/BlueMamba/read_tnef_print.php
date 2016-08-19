<?php
/*********************************************************************

    Author:   Henry Fong
              
    Modified: 09/18/2008
              
    Document: read_tnef_print.php
              
    Function: Actual code that displays tnef message body part in 
              "source/read_message.php"

*********************************************************************/

	// Figure out the body part's type

	$typestring = iml_GetPartTypeString($structure, $part);
	list($type, $subtype) = explode("/", $typestring);

	// Fetch body part
	$body = iil_C_FetchPartBody($conn, $folder, $id, $part);

	// Decode body part
	$encoding = iml_GetPartEncodingCode($structure, $part);
	if($encoding == 3)
	{
		$body = base64_decode($body);
	}
	elseif($encoding == 4)
	{
		$body = quoted_printable_decode($body);
	}

	// Check if UTF-8
	$charset = iml_GetPartCharset($structure, $part);
	if(strcasecmp($charset, "utf-8") == 0)
	{
		include_once(dirname(__FILE__) . "/utf8.php");
		$is_unicode = true;
		$body = utf8ToUnicodeEntities($body);
	}
	else
	{
		$is_unicode = false;
	}
		
	// Run through character encoding engine
	$body = LangConvert($body, $my_charset, $charset);
	$tnef_files = tnef_decode($body);
	echo "<table border=0 size=90%><tr>";
	for($i = 0; $i < sizeof($tnef_files); $i++)
	{
		$tmptype = $tnef_files[$i]['type0'];
		//format href
		$href = "view.php?user=$user&folder=$folder_url&id=$id&part=" . $part . "&tneffid=" . $i;
		//show icon, file name, size
		echo "<td align=center><a href=\"" . $href . "\">";
		echo "<img src=\"images/" . $tmptype . ".gif\" border=0><br>";
		echo "<font size=\"-1\">" . LangConvert($tnef_files[$i]['name'], $my_charset) . "<br>[" . ShowBytes($tnef_files[$i]['size']) . "]";
		echo "<br>" . $tnef_files[$i]['type0'] . "/" . $tnef_files[$i]['type1'] . "</font>";
		echo "</a></td>";
	}
	echo "</tr></table>";
	flush();

?>