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
              
    Document: include/array2php.php
              
    Function: Convert associative array to PHP
              
*********************************************************************/

function Array2PHP($a, $varname)
{
	$str .= "\$" . $varname . "= array(\n";
	$i = 0;
	reset($a);
 	while(list($key, $value) = each($a))
	{
		$str .= ($i > 0 ? "," : "") . '"' . $key . '" => "' . $value . '"\n';
		$i++;
	}
	$str .= ");\n";
	
	return $str;
}

?>
