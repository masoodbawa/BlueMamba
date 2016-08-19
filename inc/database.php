<?php

class database {

  // Connection Handler
  private $PEAK_CONN;

  // Persistent Connection Flag - DB open and close functions must be called manually
  private $PEAK_PERSISTENT = false;
  
  // Default Database Config - Use $DB->change() to select a different database
  private $PEAK_HOST;
  private $PEAK_NAME;
  private $PEAK_USER;
  private $PEAK_PASS;

  // Start Connection
  function connect() {
    // Connect to Server
    $this->PEAK_CONN = mysqli_connect($this->PEAK_HOST, $this->PEAK_USER, $this->PEAK_PASS);
    if(!$this->PEAK_CONN) {
      $this->error("Error: Database Connect - " . mysqli_error($this->PEAK_CONN));
    }
    // Select Database
    if(!mysqli_select_db($this->PEAK_CONN, $this->PEAK_NAME)) {
      $this->error("Error: Database Select - " . mysqli_error($this->PEAK_CONN));
    }
  }

  // Close Connection
  function close() {
    mysqli_close($this->PEAK_CONN);
  }

  // Set Persistent Connection Flag - True/False
  function persistent($boolean) {
    $this->PEAK_PERSISTENT = $boolean;
  }

  // Change Database
  function change($dbname = NULL, $dbuser = NULL, $dbpass = NULL, $dbhost = NULL){
    if($dbname != NULL){$this->PEAK_NAME = $dbname;}
    if($dbuser != NULL){$this->PEAK_USER = $dbuser;}
    if($dbpass != NULL){$this->PEAK_PASS = $dbpass;}
    if($dbhost != NULL){$this->PEAK_HOST = $dbhost;}
  }
  
  /**
   * Load System Settings
   * @param str $query
   */
  public function getSettings() {
    // Load default settings
    $settings = $this->getAll("SELECT name, value FROM setting WHERE ISNULL(application_env)", true);
    foreach($settings as $setting) {
      $SETTINGS[$setting['name']] = $setting['value'];
    }
    // Load application environment settings over defaults
    $app_env = getenv("APPLICATION_ENV");
    if (!empty($app_env)) {
      $settings = $this->getAll("SELECT name, value FROM setting WHERE application_env='" . $app_env . "'", true);
      foreach($settings as $setting) {
        $SETTINGS[$setting['name']] = $setting['value'];
      }
    }
    return $SETTINGS;
  }
  
  // Send Query
  function query($query) {
    if($this->PEAK_PERSISTENT == false) {    // Open connection if not persitent
      $this->connect();
    }
  
    // Send SQL Query
    if($result = mysqli_query($this->PEAK_CONN, $query)) {
      if($this->PEAK_PERSISTENT == false){  // Close connection if not persitent
        $this->close();
      }
      return $result;
    } else {
      $this->close();
      $this->error("Error: Database Query Error - " . $query . " - " . mysqli_error($this->PEAK_CONN));
    }
  }

  /**
   * Get All Lines
   * @param str $query
   * @param boolean $stripescape
   */
  public function getAll($query, $stripescape = true) {
    $result = $this->query($query);
    while ($row = mysqli_fetch_assoc($result)) {
      if ($stripescape) {
        $return[] = $this->stripescape($row);
      } else {
        $return[] = $row;
      }
    }
    return $return;
  }

  /**
   * Get One Row
   * @param str $query
   * @param boolean $stripescape
   */
  public function getRow($query, $stripescape = true) {
    $result = $this->query($query . ' LIMIT 1');
    if (($row = mysqli_fetch_assoc($result))) {
      if ($stripescape) {
        return $this->stripescape($row);
      } else {
        return $row;
      }
    }
  }

  /**
   * Get One Cell
   * @param str $query
   */
  public function getCell($query) {
    $result = $this->query($query . ' LIMIT 1');
    if (($row = mysqli_fetch_assoc($result))) {
      $key = key($row);
      return $row[$key];
    }
  }
  
  // Fetch All Lines
  function fetchAll($query) {
    $result = $this->query($query); 
    while($row = $this->fetch_array($result)) {
      $return[] = $row;
    }
    return $return;
  }
  
  // Fetch One Line
  function fetch($query) {
    $result = $this->query($query); 
    if($return = mysqli_fetch_object($result)) {
      return $return;
    }
  }
  
  // Fetch Array Query
  function fetch_array($result) {
    if($return = mysqli_fetch_array($result)) {
      return $return;
    }
  }
  
  // Fetch Object Query
  function fetch_object($result) {
    if($return = mysqli_fetch_object($result)) {
      return $return;
    }
  }

  // Fetch Column Max 
  function max($column, $table) {
    $result = $this->query("SELECT MAX($column) FROM $table"); 
    if($return = mysqli_fetch_array($result)) {
      return $return["MAX($column)"];
    }
  }

  // Fetch Row Count
  function count($column, $table, $where = NULL) {
    $where = ($where!=NULL?" WHERE $where":"");  // Add where clause if passed
    $result = $this->query("SELECT COUNT($column) FROM $table $where"); 
    if($return = mysqli_fetch_array($result)) {
      return $return["COUNT($column)"];
    }
  }

  // Insert data with an array
  function array_insert($table, $array) {
    reset($array);
    while(list($key, $val) = each($array)) {
      $fields .= (!empty($fields)?",":"") . $key;
      $values .= (!empty($values)?",":"") . "'$val'";
    }
    $result = $this->query("INSERT INTO $table ($fields) VALUES ($values)"); 
    return $result;
  }

  // Update data with an array
  function array_update($table, $where, $array) {
    reset($array);
    while(list($key, $val) = each($array)) {
      $setdata .= (!empty($setdata)?",":"") . "$key='$val'";
    }
    $result = $this->query("UPDATE $table SET $setdata WHERE $where"); 
    return $result;
  }

  // Decrypt Data while fetching
  function decrypt_fetch($result) {
    while($row = mysqli_fetch_object($result)) {
      foreach($row as $key => $value) {
        // Don't Decrypt ID's
        if(substr($key, -3, 3) != "_id") {
          $row->$key = peak_decrypt($value);
        }
      }
      return $row;
    }
  }

  // Add escape character for database entry.
  function addescape($variable) {
    // Escape Array
    if(is_array($variable)) {
      foreach($variable as $key => $value) {
        $variable[$key] = addslashes($value);        
      }
      return $variable;
    }
    // Escape Object
    elseif(is_object($variable)) {
      foreach($variable as $key => $value) { 
        $variable->$key = addslashes($value);
      }
      return $variable;
    }
    // Escape String
    else {
      $variable = addslashes($value);
      return $variable;
    }
  }

  // Strip escape character for database entry.
  function stripescape($variable) {
    // Escape Array
    if(is_array($variable)) {
      foreach($variable as $key => $value) {
        $variable[$key] = stripslashes($value);        
      }
      return $variable;
    }
    // Escape Object
    elseif(is_object($variable)) {
      foreach($variable as $key => $value) { 
        $variable->$key = stripslashes($value);
      }
      return $variable;
    }
    // Escape String
    else {
      $variable = stripslashes($value);
      return $variable;
    }
  }

  // Display Critical Error Messages and Stop
  private function error($string) {
    printf("<div style=\"padding:20px; color:#CC0000; font-weight:bold;\"> %s </div> ", $string);
    exit;
  }

  // Display Minor Error Messages
  private function errmsg($string) {
    printf("<div style=\"padding:20px; color:#CC0000; font-weight:bold;\"> %s </div> ", $string);
  }
  
  // Display General Messages
  private function genmsg($string) {
    printf("<div style=\"padding:20px; color:#00CC00; font-weight:bold;\"> %s </div> ", $string);
  }
  
}
?>