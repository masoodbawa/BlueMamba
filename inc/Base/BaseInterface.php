<?php
/**
 * Base Interface
 */

class BaseInterface {

  protected $id;
  protected $table = 'interface';
  
  public $filetypes;
  public $info;
  public $interface;


  /**
   * Create Interface
   * @param type $id
   * @param type $currency
   * @param type $sandbox
   * @param type $apikeys
   * @throws Exception
   */
  public function __construct($id, $currency, $sandbox, $apikeys) {
    if($this->table === null) {
      throw new Exception("The protected field \$table must be defined.");
    }
    if($id > 0) {
      $this->id = $id;
      $this->info = DB::load($this->table, $id, null, true);
    
      // Only include if not already included
      if(!class_exists($this->info->class)) {
        // Load Interface Script
        if(!file_exists(DOCUMENT_ROOT . "/inc/" . $this->info->interface)) {
          throw new Exception("Unable to load interface " . DOCUMENT_ROOT . "/inc/" . $this->info->interface);
        }

        require_once(DOCUMENT_ROOT . "/inc/" . $this->info->interface);

        // Check if class exists
        if(!class_exists($this->info->class)) {
          throw new Exception("Class " . $this->info->class . " does not exist.");
        }
      }

      // Create Interface Object
      $this->interface = new $this->info->class($currency, $sandbox, json_decode($apikeys));
    }
  }


  
  
  /**
   * Get Interface for Country
   * @param type $country_code
   * @param type $interfacetype_id
   * @return type
   */
  function getByCountry($country_code, $interfacetype_id = 1) {
    if(empty($country_code)) {
      return false;
    }
    $sql = "SELECT i.id, i.name
              FROM interface i
              LEFT JOIN interface_country ic on ic.interface_id = i.id
              LEFT JOIN country c on ic.country_id = c.id
            WHERE c.code = '" . addslashes($country_code) . "'
              AND i.active = 1
              AND i.interfacetype_id = " . (int) $interfacetype_id . "
            GROUP BY i.id
            ORDER BY i.name";
    return DB::getAll($sql);
  }
  
  
  
  /**
   * Get Currency for Selected Interface
   * @param type $interface_id
   * @return type
   */
  function getCurrency($interface_id) {
    if(empty($interface_id)) {
      return false;
    }

    $sql = "SELECT c.code, c.name 
              FROM interface i
              LEFT JOIN interface_currency ic on ic.interface_id = i.id
              LEFT JOIN currency c on ic.currency_id = c.id
            WHERE interface_id IN(" . (int) $interface_id . ")
              AND c.active = 1
            ORDER BY name";
    return DB::getAll($sql);
  }

}


