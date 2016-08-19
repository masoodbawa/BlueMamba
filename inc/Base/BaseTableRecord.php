<?php
/**
 * Base Table Record
 * Extends base table with contact information functions
 * Such as addresses
 */

class BaseTableRecord extends BaseTable {

	/**
	 * Get customer address
	 * @param integer $address_id
	 */
	public function getAddress($address_id) {
		$address = new Address($address_id);
		return $address->info;
	}

	/**
	 * Get customer address by the type
   * @param type $addresstype_id
   * @param type $primary
   * @return boolean
   * @throws Exception
   */
	public function getAddressByType($addresstype_id, $primary = 1) {

    $address_id = DB::getCell("
			SELECT address_id FROM address_" . $this->table . "
				WHERE " . $this->table . "_id = " . (int) $this->getId() . "
					AND addresstype_id = " . (int) $addresstype_id . "
					AND `primary` = " . (int) $primary);
		$address_info = $this->getAddress($address_id);

		// Check if a valid address entry
		if ($address_info->id > 0) {
			return $address_info;
		}
		else {
			return false;
		}
	}

	/**
	 * Save address
   * @param type $address_id
   * @param type $addresstype_id
   * @param type $address
   * @param type $address2
   * @param type $city
   * @param type $state
   * @param type $zip
   * @param type $country
   * @param type $name
   * @param type $primary
   * @return type
   */
	public function saveAddress($address_id, $addresstype_id, $address, $address2, $city, $state, $zip, $country, $name = null, $primary = 1) {

		// If address type isn't billing check new entry against billing to see if it is unique
		if ($addresstype_id > 10) {
			$bill_info = $this->getAddressByType(10, 1);

			if ($bill_info->id > 0) {
				// Find difference between addresses
				$different = $this->isDifferent(
          $address,
          $address2,
          $city,
          $state,
          $zip,
          $country,
          $bill_info->address,
          $bill_info->address2,
          $bill_info->city,
          $bill_info->state,
          $bill_info->zip,
          $bill_info->country
				);

				// If same as billing grab address_id and create linking address for address type
				if ($different === false) {
					$return = $this->linkAddress($bill_info->id, $addresstype_id, $primary);
					return $bill_info->id;
				}

				// If this has same id as billing and is different, then add new address
				if ($bill_info->id === $address_id && $different === true) {
					$address_id = 0; // Force new address
				}
			}
		}


		// Check difference against itself if an update
		if ($address_id > 0) {
			$address_info = $this->getAddress($address_id);

			$different = $this->isDifferent(
        $address,
        $address2,
        $city,
        $state,
        $zip,
        $country,
        $address_info->address,
        $address_info->address2,
        $address_info->city,
        $address_info->state,
        $address_info->zip,
        $address_info->country
			);

			if ($different === true) {
				$address_id = 0; // Force new address
			}
		}

    // Update or Add New address
		if ($address_id > 0) {

			// Update Address
      $adr = new Address($address_id);

			$adr->info->name = trim($name);
			$adr->info->address = trim($address);
			$adr->info->address2 = (!empty($address2) ? trim($address2) : null);
			$adr->info->city = trim($city);
			$adr->info->state = trim($state);
			$adr->info->zip = trim($zip);
			$adr->info->country = trim($country);

			$address_id = $adr->save($adr->info);
		}
		else {

			// Add Address
      $adr = new Address();
			$address_id = $adr->create($address, $address2, $city, $state, $zip, $country, $name);

			// Link Addess
			if ($address_id > 0) {
				$return = $this->linkAddress($address_id, $addresstype_id, $primary);
			}
		}

		return $address_id;
	}

	/**
	 * Link address to customer
   * @param type $address_id
   * @param type $addresstype_id
   * @param type $primary
   * @return type
   * @throws Exception
   */
	public function linkAddress($address_id, $addresstype_id, $primary = 1) {
    if($this->table == null || $this->getId() == null) {
      throw new Exception("Relation table or id for address not specified");
    }
		if ($primary == 1) {

			// Get primary address id
			$primary_address = $this->getAddressByType($addresstype_id, 1);

			// Check if primary is tied to orders or transaction.
			if ($this->inUse($primary_address->id)) {

				// Currently in use, change to non primary
				DB::query("UPDATE address_" . $this->table . "
						SET `primary` = 0
						WHERE address_id = " . (int) $primary_address->id . "
						AND addresstype_id = " . (int) $addresstype_id . "
						AND " . $this->table . "_id = " . (int) $this->getId());
			}
		}
		return DB::query("INSERT INTO address_" . $this->table . "
				SET address_id = " . (int) $address_id . ",
				" . $this->table . "_id = " . (int) $this->getId() . ",
				addresstype_id = " . (int) $addresstype_id . ",
				`primary` = " . (int) $primary);
	}

	/**
	 * Check two address to see if they are different
	 * @param type $one_address
	 * @param type $one_address2
	 * @param type $one_city
	 * @param type $one_state
	 * @param type $one_zip
	 * @param type $one_country
	 * @param type $two_address
	 * @param type $two_address2
	 * @param type $two_city
	 * @param type $two_state
	 * @param type $two_zip
	 * @param type $two_country
	 * @return boolean
	 */
	public function isDifferent($one_address, $one_address2, $one_city, $one_state, $one_zip, $one_country, $two_address, $two_address2, $two_city, $two_state, $two_zip, $two_country) {

		// Find difference between addresses
		$change = 0;
		$change += levenshtein($one_address,  $two_address);
		$change += levenshtein($one_address2, $two_address2);
		$change += levenshtein($one_city,     $two_city);
		$change += levenshtein($one_state,    $two_state);
		$change += levenshtein($one_zip,      $two_zip);
		$change += levenshtein($one_country,  $two_country);

		if ($change <= 15) {
			return false;
		} else {
			return true;
		}
	}
  
  /**
   * Check if address is in use
   * @param type $address_id
   * @param type $id
   */
	public function inUse($address_id) {
    return false;
  }

}
