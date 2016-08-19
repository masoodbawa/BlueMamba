<?php
/**
 * User Class
 */

class User extends BaseTable {
  
	protected $table = 'user';
  
  /**
   * Create User or update existing record
   * @param type $username
   * @param type $password
   * @param type $name
   * @param type $defaultpage
   * @param type $userpermission_id
   * @return type
   */
	public function record($username, $password, $name, $defaultpage = null, $userpermission_id = null) {
    if($this->id > 0) {
      $this->load($this->id);
    } else {
      $this->info = DB::dispense($this->table);
    }
    
    $this->info->username = strtolower(trim($username));
    $this->info->password = md5(trim($password));
    $this->info->name = trim($name);
    if($defaultpage != null) {
      $this->info->defaultpage = trim($defaultpage);
    }
    $this->info->lastlogin = null;
    $this->info->datecreated = date('Y-m-d h:i:s');
    if($userpermission_id != null) {
      $this->info->userpermission_id = (int) $userpermission_id;
    }
    
    $id = $this->save($this->info);
    if(empty($id)) {
      throw new Exception("Error recording new user!");
    }

    // Make this instance this address
    $this->load($id);
    return $id;
  }
  
  
  /**
   * Check if Password is Usable
   */
  public function checkPassword($password) {
    $password_clean = addslashes(trim($password));
    
    if(empty($password_clean)) {
      return "password_blank";
    }
    if(strlen($password_clean) < 8) {
      return "password_short";
    }
    return true;
  }
  
  
  /**
   * Check if Username is Available
   */
  public function isAvailable($username) {
    $username_clean = addslashes(strtolower(trim($username)));
    
    // Make suer username is longer than 4 characters
    if(strlen($username) < 4) {
      return false;
    }
    
    // Check for censored words - DISABLED - ERRORS
    /*
    $censor = new Censor();
    if(!$censor->check($username_clean)) {
      return false;
    }
    */
    /*
    // Check for reserved words
    $reserved_username = array(
      "admin",
      "support",
      "service",
      "system",
      "customer",
      "account",
      "webmaster",
      "archive",
      "root",
      "http",
      "www",
      "ssl",
      "apps",
      "info",
      "mail",
      "sale"
    );
    foreach($reserved_username as $w) {
      if(strpos($username_clean, $w) !== false) {
        return false;
      }
    }
    
    // Schanaco Check
    if(strpos($username_clean, "schanaco") !== false) {
      return false;
    }
     */

    // Check if username has been used
    $sql = "SELECT id FROM user WHERE username='" . $username_clean . "'";
		$user_id = DB::getCell($sql);
    if($user_id > 0) {
      return false;
    } else {
      return true;
    }
  }
  
  
  /**
   * Load this user class by username
   * @param type $username
   * @return boolean
   */
  function loadByUsername($username) {
    $username_clean = addslashes(strtolower(trim($username)));
    
    $sql = "SELECT id FROM user WHERE username='" . $username_clean . "'";
		$user_id = DB::getCell($sql);
    if($user_id > 0) {
      return $this->load($user_id);
    } else {
      return false;
    }
  }
  
  
  /**
   * Check if user permission level is acceptable
   * @param type $userpermission_id
   */
  function checkPermission($userpermission_id) {
    if($this->info->userpermission_id > $userpermission_id || empty($this->info->userpermission_id)) {
      header("location: /dashboard.php");
      exit;
    }
  }
  
  

}
