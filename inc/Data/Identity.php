<?php
/**
 * Identities Class
 */

class Identity extends BaseTableRecord {

	protected $table = 'identity';

  /**
   * Create new identity record
   * @param type $description
   * @param type $name
   * @param type $company
   * @param type $address
   * @param type $address2
   * @param type $city
   * @param type $state
   * @param type $zip
   * @param type $country
   * @param type $email
   * @param type $phone
   * @param type $logo
   * @param type $invoiceterms
   * @param type $active
   * @return type
   */
	public function create($description, $name, $company, $address, $address2, $city, $state, $zip, $country, $email, $phone, $logo = null, $invoiceterms = null, $active = 1) {
    $info = DB::dispense('identity');
    $info->description = trim($description);
    $info->name = trim($name);
    $info->company = trim($company);
    $info->address = trim($address);
    $info->address2 = trim($address2);
    $info->city = trim($city);
    $info->state = trim($state);
    $info->zip = trim($zip);
    $info->country = trim($country);
    $info->email = trim($email);
    $info->phone = preg_replace("/[^0-9]/", "", trim($phone));
    $info->logo = trim($logo);
    $info->invoiceterms = trim($invoiceterms);
    $info->active = ($active == 1 ? 1 : 0);
    $id = $this->save($info);

    // Make this instance this address
    $this->load($id);
    return $id;
  }

  /**
   * Search for identities
   * @param type $terms
   * @param type $orderby
   */
	public function search($terms, $orderby = "`description`, `name`") {

    $where = "`description` LIKE '%" . addslashes($terms) . "%' ";
    $where .= " OR `name` LIKE '%" . addslashes($terms) . "%' ";
    $where .= " OR `company` LIKE '%" . addslashes($terms) . "%' ";
    $where .= " OR `email` LIKE '%" . addslashes($terms) . "%' ";
    $where .= " OR `phone` LIKE '%" . addslashes($terms) . "%' ";

    return DB::loadAll('identity', $where . " ORDER BY " . addslashes($orderby) . " DESC");
  }


}

