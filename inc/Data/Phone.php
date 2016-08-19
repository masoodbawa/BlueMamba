<?php
/**
 * Phone Class
 */

class Phone extends BaseTable {

	protected $table = 'phone';

  /**
   * Create new phone record
   * @param type $nubmer
   * @return type
   */
	public function create($nubmer) {
		// Check that this instance isn't a loaded address
		if (empty($this->info->id)) {
			$info = DB::dispense('phone');
			$info->number = preg_replace("/[^0-9]/", "", trim($phone));
			$id = $this->save($info);

			// Make this instance this address
			$this->load($id);
			return $id;
		}
  }


	/**
	 * Check if an phone is tied to an order or transaction
	 * @param type $phone_id
	 * @param type $customer_id
	 * @return boolean
	 */
	public function inUse($phone_id, $customer_id = null) {
    /*
		$sql = "SELECT
						(SELECT count(*) FROM `order` WHERE address_id=ac.address_id) AS orders,
						(SELECT count(*) FROM `transaction` WHERE address_id=ac.address_id) AS transactions
						FROM address_customer ac WHERE address_id = " . (int) $address_id;

		if ($customer_id > 0)
			$sql .= " AND customer_id = " . (int) $customer_id;
		
		$uses = DB::getRow($sql);

		if ($uses['orders'] > 0 || $uses['transactions'] > 0)
			return true;
		else
			return false;
     */
    return false;
	}





}

