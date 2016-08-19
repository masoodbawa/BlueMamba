<?php
/**
 * Language Functions
 */

class Lang {

  static protected $code;
  static public $info;

  /**
   * Set Language - English by default
   * @param type $code
   * @throws Exception
   */
  static public function set($code = 'en-US') {
    if(empty($code)) {
      throw new Exception("Language not specified.");
    }
    

    self::$code = strtolower(addslashes($code));
    self::$info = new stdClass();
    
    global $DEFAULT_LANG;
    $DEFAULT_LANG = self::$code;
  }


  /**
   * Load Language Section
   * @global type $smarty
   * @param type $section
   * @param type $code
   * @param type $assign_smarty
   * @return type
   * @throws Exception
   */
  static function load($section, $code = null, $assign_smarty = true) {
    if(empty($code)) {
      $code = self::$code;
    }
   
    self::add($section, $code);
    
    // Assign to Smarty
    global $smarty;
    if($assign_smarty == true && $smarty) {
      $smarty->assign("lang", self::$info->$section);
    }

    return self::$info->$section;
  }


  /**
   * Add Language Section
   * @global type $smarty
   * @param type $section
   * @param type $code
   * @return type
   * @throws Exception
   */
  static function add($section, $code = null) {
    if(empty($code)) {
      $code = self::$code;
    }
    $code = strtolower($code);
    
    $file = DOCUMENT_ROOT . '/lang/' . addslashes($code) . '/json/' . addslashes($section) . '.json';
    
    // If file doesn't exist default to English.
    if(!is_file($file)) {
      self::set('en-US');
      $code = self::$code;
    }

    $file = DOCUMENT_ROOT . '/lang/' . addslashes($code) . '/json/' . addslashes($section) . '.json';
    if(!$json = file_get_contents($file, false)) {
      throw new Exception("Error Loading Language: $file");
    }
    
    self::$info->$section = json_decode($json);
    

    return self::$info->$section;
  }


  /**
   * Get Language Section Stored
   * @param type $section
   * @return type
   */
  static function get($section) {
    if(empty($section)) {
      return false;
    }
    return self::$info->$section;
  }

}

