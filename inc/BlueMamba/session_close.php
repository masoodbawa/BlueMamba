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
              
    Document: session_close.php
              
    Function: Close Session
	
*********************************************************************/

	if($MAIL_SESSION)
	{
		$user = $MAIL_SESSION;
	}

	// Delete this session and any old unclosed ones
	include_once(DOCUMENT_ROOT . "/conf/db_conf.php");
	include_once(DOCUMENT_ROOT . "/conf/conf.php");
	
	// Connect to db
	include_once(dirname(__FILE__) . "/idba.php");
	$db = new idba_obj;
	if($db->connect())
	{
			$expTime = time() - $MAX_SESSION_TIME; //close all session that are over 24 hours old
			$sql = "delete from $DB_SESSIONS_TABLE where (sid = '$user') or (inTime < $expTime)";
			if(!$db->query($sql)) echo "DB query failed: $sql <br>\n";
	}
	else
	{
		echo "DB connection failed.<br>\n";
	}
?>