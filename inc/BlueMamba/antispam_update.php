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

    Author:   Travis Schanafelt  >>  travis@bluemamba.org

    Modified: 09/18/2008
              
    Document: antispam_update.php
              
    Function: Update session table with latest send operation

*********************************************************************/

include_once(DOCUMENT_ROOT . "/conf/conf.php");
include_once(DOCUMENT_ROOT . "/conf/db_conf.php");
include_once(dirname(__FILE__) . "/idba.php");

// Connect to db
$db = new idba_obj;
if ($db->connect())
{
	$numSent = $numSent + $num_recepients;
	$sql  = "UPDATE $DB_SESSIONS_TABLE";
	$sql .= " SET lastSend=".time().", numSent=$numSent";
	$sql .= " WHERE sid='$sid'";
	if ($db->query($sql))
	{
		echo "<p>Anti-Spam update complete.\n";
	}
	else
	{
		echo "<p>Anti-Spam update failed!\n";
	}
}
else
{
	echo "<p>DB connection failed.\n";
}

?>