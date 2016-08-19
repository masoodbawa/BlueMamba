<?php
/*********************************************************************

  DB Element is a software package created by Travis Schanafelt
  Copyright 2013-2016 Travis Schanafelt, All Rights Reserved

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  Author:   Travis Schanafelt

  Modified: 03/31/2016

 *********************************************************************/

class DB {

  // Connection Handler
  static protected $conn;

  /**
   * Start Connection
   * @param str $name  Database name
   * @param str $user Username
   * @param str $pass Password
   * @param str $host Hostname
   * @param int $port
   */
  public static function connect($name = NULL, $user = NULL, $pass = NULL, $host = 'localhost', $port = NULL) {
    
    self::$conn = mysqli_init();
    
    // Connect with SSL
    if(DB_SSL == true) {
      //mysqli_ssl_set(self::$conn, DB_SSL_CLIENT_KEY, DB_SSL_CLIENT_CERT, DB_SSL_CA_CERT, NULL, NULL);
    }
    
    // Connect to Server
    mysqli_real_connect(self::$conn, $host, $user, $pass, $name, $port);
    
    // Check Connection 
    if (!self::connected()) {
      throw new Exception("Database Not Connected");
    }
    
    // Set Characterset
    mysqli_set_charset(self::$conn, "utf8");
  }
  
  /**
   * Check if Connected
   */
  public static function connected() {
    return mysqli_ping(self::$conn);
  }

  /**
   * Close Connection
   */
  public static function close() {
    mysqli_close(self::$conn);
  }
  
  /**
   * Send Query
   * @param str $query
   */
  public static function query($query) {
    // Check if connected
    if (!self::connected()) {
      throw new Exception("Database Not Connected");
    }
    // Send SQL Query
    if (($result = mysqli_query(self::$conn, $query))) {
      return $result;
    } else {
      $error = array( 
        'Message' => mysqli_error(self::$conn),
        'Query' => $query
      );
      throw new Exception("DB Error: " . print_r($error, true));
    }
  }
  
  /**
   * Send Milti Query
   * @param str $query
   */
  public static function multi_query($query) {
    // Check if connected
    if (!self::connected()) {
      throw new Exception("Database Not Connected");
    }
    // Send SQL Query
    if (($result = mysqli_multi_query(self::$conn, $query))) {
      return $result;
    } else {
      $error = array( 
        'Message' => mysqli_error(self::$conn),
        'Query' => $query
      );
      throw new Exception("DB Error: " . print_r($error, true));
    }
  }
  
  /**
   * Dispense Table
   * @param str $table
   */
  public static function dispense($table) {
    $result = self::query('DESCRIBE `' . $table . '`');
    $info = array();
    while ($row = mysqli_fetch_assoc($result)) {
      $data = '';
      if(empty($data)) {
        if($row['Default'] == null && $row['Null'] == 'YES') {
          $data = null;
        }
        elseif($row['Default'] != '' && $row['Default'] !== null) {
          $data = $default;
        }
      }
      $info->$row['Field'] = $data;
    }
    $dbe = new DBElement($table, $info, null);
    return $dbe;
  }
  
  /**
   * Load Row from Table
   * @param str $table
   */
  public static function load($table, $id, $where = null, $include_related = false) {
    $where = ($where === null ? "" : " AND $where");
    $result = self::query('SELECT * FROM `' . $table . '` WHERE id = ' . (int) $id . $where);
    $row = mysqli_fetch_assoc($result);
    $info = new stdClass();
    $related = new stdClass();
    foreach($row as $key => $value) {
      $info->$key = self::escape($value, true);
      if($include_related === true) {
        // If there is a related table load it, Avoid loading same child id as parent id
        if(!strstr($key, '_id') === false && $value > 0 && $info->id !== $value) {
          $subtable = strstr($key, '_id', true);
          $related->$subtable = self::load($subtable, $value, $include_related);
        }
      }
    }
    $dbe = new DBElement($table, $info, $related);
    return $dbe;
  }
  
  /**
   * Load All Rows from Table
   * @param str $table
   * @param str $where
   * @param arr $ids Array of ids to load
   * @param bln $include_related Include related table data
   */
  public static function loadAll($table, $where = null, $ids = null, $include_related = false) {
    $set = new stdClass();
    $info = new stdClass();
    $related = new stdClass();
    $where = ($where === null ? "" : " AND $where");
    if(is_array($ids)) {
      $where .= ' AND id IN(' . self::escape(implode(',', $ids)) . ')';
    }
    $result = self::query('SELECT * FROM `' . $table . '` WHERE 1=1 ' . $where);
    while($row = mysqli_fetch_assoc($result)) {
      foreach($row as $key => $value) {
        $info->$key = self::escape($value, true);
        if($include_related === true) {
          // If there is a related table load it
          if(!strstr($key, '_id') === false && $value > 0 && $info->id !== $value) {
            $subtable = strstr($key, '_id', true);
            $apikeyssubtable = self::load($subtable, $value, $include_related);
          }
        }
      }
      $id = $info->id;
      $set->$id = new DBElement($table, $info, $related);
      unset($related);
    }
    return $set;
  }
  
  /**
   * Store Element in Table
   * @param obj $object
   */
  public static function store($object) {
    
    if(get_class($object) !== 'DBElement') {
      return false;
    }
    
    $original_id = $object->getID();
    $table = $object->getTable();
    
    if(empty($table)) {
      return false;
    }
    
    // Convert DBElement to array
    $array = get_object_vars($object);
    
    // Get field types and format data types correctly
    $result = self::query('DESCRIBE `' . $table . '`');
    while ($row = mysqli_fetch_assoc($result)) {
      $data = $array[$row['Field']];
      $type = $row['Type'];
      $null = $row['Null'];
      $default = $row['Default'];
      
      if (strstr($type, 'int')) {
        $data = (int) $data;
      } elseif (strstr($type, 'double')) {
        $data = (float) $data;
      } elseif (strstr($type, 'decimal')) {
        $data = (float) $data;
      } elseif (strstr($type, 'float')) {
        $data = (float) $data;
      } elseif (strstr($type, 'char')) {
        $data = self::escape($data);
      } elseif (strstr($type, 'enum')) {
        $data = self::escape($data);
      } elseif (strstr($type, 'text')) {
        $data = self::escape($data);
      } elseif (strstr($type, 'blob')) {
        $data = self::escape($data);
      } elseif (strstr($type, 'year')) {
        $data = (int) $data;
      } elseif (strstr($type, 'date')) {
        if(!empty($data)) {
          $data = date("Y-m-d", strtotime($data));
        } else {
          $data = null;
        }
      } elseif (strstr($type, 'time')) {
        if(!empty($data)) {
          $data = date("H:i:s", strtotime($data));
        } else {
          $data = null;
        }
      } elseif (strstr($type, 'datetime')) {
        if(!empty($data)) {
          $data = date("Y-m-d H:i:s", strtotime($data));
        } else {
          $data = null;
        }
      } elseif (strstr($type, 'timestamp')) {
        if(!empty($data)) {
          $data = date("Y-m-d H:i:s", strtotime($data));
        } else {
          $data = null;
        }
      }
      
      // Set blank data to default values
      if(empty($data)) {
        if($default == null && $null == 'YES') {
          $data = null;
        }
        elseif($default != '' && $default !== null) {
          $data = $default;
        }
      }
      
      $array[$row['Field']] = $data;
    }
    
    if($original_id > 0) {
      $type = 'UPDATE';
      $where = 'id = ' . $original_id;
      if($original_id == $array['id']) {
        // If ID is the same then remove from update
        unset($array['id']);
      }
    } else {
      $type = 'INSERT';
      $where = NULL;
    }
    return self::put($type, $table, $array, $where);
  }
  
  /**
   * Trash Element - Delete from database
   * @param obj $object
   */
  public static function trash($object) {
    
    if(get_class($object) !== 'DBElement') {
      return false;
    }
    
    $original_id = $object->getID();
    $table = $object->getTable();
    
    if($result = self::query('DELETE FROM `' . $table . '` WHERE id = ' . $original_id)) {
      return $original_id;
    }
    else {
      return false;
    }
  }

  /**
   * Get All Lines
   * @param str $query
   * @param boolean $stripescape
   */
  public static function getAll($query, $stripescape = true) {
    $result = self::query($query);
    while ($row = mysqli_fetch_assoc($result)) {
      if ($stripescape) {
        $return[] = self::escape($row);
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
  public static function getRow($query, $stripescape = true) {
    $result = self::query($query . ' LIMIT 1');
    if (($row = mysqli_fetch_assoc($result))) {
      if ($stripescape) {
        return self::escape($row);
      } else {
        return $row;
      }
    }
  }

  /**
   * Get One Cell
   * @param str $query
   */
  public static function getCell($query) {
    $result = self::query($query . ' LIMIT 1');
    if (($row = mysqli_fetch_assoc($result))) {
      $key = key($row);
      return $row[$key];
    }
  }

  /**
   * Fetch Object
   * @param str $query
   */
  public static function fetch($query) {
    $result = self::query($query);
    if (($return = mysqli_fetch_object($result))) {
      return $return;
    }
  }

  /**
   * Fetch Column Max
   * @param str $table
   * @param str $column
   */
  public static function max($table, $column = '*') {
    $result = self::query('SELECT MAX(' . $column . ') AS value FROM `' . $table . '`');
    if (($return = mysqli_fetch_assoc($result))) {
      return $return['value'];
    }
  }

  /**
   * Fetch Column Min
   * @param str $table
   * @param str $column
   */
  public static function min($table, $column = '*') {
    $result = self::query('SELECT MIN(' . $column . ') AS value FROM `' . $table . '`');
    if (($return = mysqli_fetch_assoc($result))) {
      return $return['value'];
    }
  }

  /**
   * Fetch Row Count
   * @param str $table
   * @param str $column
   * @param str $where
   */
  public static function count($table, $column = '*', $where = NULL) {
    $where = ($where === NULL ? '' : ' WHERE ' . $where); // Add where clause if passed
    $result = self::query('SELECT COUNT(' . $column . ') AS value FROM `' . $table . '`' . $where);
    if (($return = mysqli_fetch_assoc($result))) {
      return $return['value'];
    }
  }

  /**
   * Insert data with an array
   * @param str $table
   * @param arr $array
   */
  public static function insert($table, $array) {
    return self::put('INSERT', $table, $array);
  }

  /**
   * Update data with an array
   * @param str $table
   * @param str $where
   * @param arr $array
   */
  public static function update($table, $array, $where) {
    return self::put('UPDATE', $table, $array, $where);
  }

  /**
   * Put data with an array
   * @param str $table
   * @param arr $array
   * @param str $where
   */
  protected static function put($type, $table, $array, $where = NULL) {
    $result = null;
    if($type == 'INSERT' || $type == 'UPDATE') {
      reset($array);
      foreach($array as $key => $val) {
        $setdata[] = "`$key`=" . ($val === NULL ? 'NULL' : "'$val'");
      }
      $setdata = join(', ', $setdata);
      $where = ($where === NULL ? "" : "WHERE $where");
      $type = ($type == 'INSERT' ? 'INSERT INTO' : $type);
      $result = self::query("$type `$table` SET $setdata $where");
      return mysqli_insert_id(self::$conn);
    } else {
      return false;
    }
  }

  /**
   * Add escape character for database entry
   * @param str|arr|obj $variable Can accept string, array or object
   * @param bln $strip Strip slashes. Default is to add slashes
   */
  public static function escape($variable, $strip = false) {
    // Escape Array
    if(is_array($variable)) {
      foreach($variable as $key => $value) {
        $variable[$key] = ($strip ? stripslashes($value) : addslashes($value));        
      }
      return $variable;
    }
    // Escape Object
    elseif(is_object($variable)) {
      foreach($variable as $key => $value) { 
        $variable->$key = ($strip ? stripslashes($value) : addslashes($value));
      }
      return $variable;
    }
    // Escape String
    else {
      $variable = ($strip ? stripslashes($variable) : addslashes($variable));
      return $variable;
    }
  }

}

/**
 * Database Element Class
 */
class DBElement {
  protected $__id;
  protected $__table;
  
  /**
   * Class Constructor
   * @param str $table
   * @param obj $info
   */
  public function __construct($table, $info, $related) {
    $this->__id = $info->id;
    $this->__table = $table;
    
    // Set info
    foreach($info as $key => $value) {
      $this->$key = $value;
    }
    
    // Tack on related elements
    if(is_object($related)) {
      foreach($related as $key => $value) {
        $relkey = 'own' . $key;
        $this->$relkey = $value;
      }
    }
  }
  
  /**
   * Get Original ID from Element
   */
  public function getID() {
    return $this->__id;
  }
  
  /**
   * Get Table from Element
   */
  public function getTable() {
    return $this->__table;
  }
  
  /**
   * Save Element
   */
  public function save() {
    return DB::store($this);
  }
}

