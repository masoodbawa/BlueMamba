<?php
/**
 * Autoload Includes 
 */

function autoload($directories = array()) {
  foreach ($directories as $dir) {
    $includes = scandir($dir);
    foreach($includes as $include) {
      if(is_file($dir . '/' . $include)) {
        include_once($dir . '/' . $include);
      }
    }
  }
}

