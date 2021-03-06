<?
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
              
    Document: include/array2sql.php
              
    Function: Convert associative array into sql request
              
*********************************************************************/

function Array2SQL($table, $array, $action)
{
    $fields	= "";
    $vals	= "";
    $sql	= "";
    
    if(strcasecmp($action,"INSERT") == 0)
	{
        reset($array);
        while(list($field, $val) = each($array))
		{
            $fields .= (!empty($fields)?",":"").$field;
            $vals   .= (!empty($vals)?",":"")."'".$val."'";
        }
        
        $sql = "INSERT INTO $table ($fields) VALUES ($vals)";
   
     }
	 elseif(strcasecmp($action, "UPDATE") == 0)
	 {
        reset($array);
        while(list($field, $val) = each($array))
		{
            $sql .= (!empty($sql)?",":"")."$field='$val'";
		}
        
        $sql = "UPDATE $table SET " . $sql;
    }
    
    return $sql;
}


?>