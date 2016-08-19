<?php
/**
 * Address Class
 */

class Address extends BaseTable {

	protected $table = 'address';

  /**
   * Create new address record
   * @param type $addresstype_id
   * @param type $address
   * @param type $address2
   * @param type $city
   * @param type $state
   * @param type $zip
   * @param type $country
   */
	public function create($address, $address2, $city, $state, $zip, $country, $name = null) {
		// Check that this instance isn't a loaded address
		if (empty($this->info->id)) {
			$info = DB::dispense('address');
			$info->name = trim($name);
			$info->address = trim($address);
			$info->address2 = (!empty($address2) ? trim($address2) : null);
			$info->city = trim($city);
			$info->state = trim($state);
			$info->zip = trim($zip);
			$info->country = trim($country);
			$id = $this->save($info);

			// Make this instance this address
			$this->load($id);
			return $id;
		}
  }

	/**
	 * Load list of countries with display selection first
	 * @param string $country
	 */
	public function getCountries($active = false) {
    $sql = "SELECT code, name FROM country";
    if($active === true) {
      $sql .= " WHERE active = 1";
    }
    $sql .= " ORDER BY name ";
		return DB::getAll($sql);
	}

	/**
	 * Get the name of the country
	 * @param string $country
	 */
	public function getCountryName($country = 'US') {
		return DB::getCell("SELECT name FROM country WHERE code ='" . $country . "'");
	}


}
