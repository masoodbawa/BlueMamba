<?php
/**
 * Customers Class
 */

class Customer extends BaseTableRecord {

	protected $table = 'customer';

  /**
   * Create new customer record
   * @param type $lastname
   * @param type $firstname
   * @param type $company
   * @param type $email
   * @param type $phone
   * @param type $notes
   * @param type $referral
   * @param type $identity_id
   * @param type $active
   * @return type
   */
	public function create($lastname, $firstname, $company, $email, $phone, $notes, $referral, $identity_id = null, $active = 1) {
    $info = DB::dispense('customer');
    $info->lastname = trim($lastname);
    $info->firstname = trim($firstname);
    $info->company = trim($company);
    $info->email = trim($email);
    $info->phone = preg_replace("/[^0-9]/", "", trim($phone));
    $info->notes = trim($notes);
    $info->referral = trim($referral);
    $info->datecreated = date("Y-m-d");
    $info->identity_id = ($identity_id > 0 ? (int) $identity_id : null);
    $info->active = ($active == 1 ? 1 : 0);
    $id = $this->save($info);

    // Make this instance this address
    $this->load($id);
    return $id;
  }

  /**
   * Search for customers
   * @param type $terms
   * @param type $orderby
   */
	public function search($terms, $orderby = "lastname, firstname") {

    $where = "lastname LIKE '%" . addslashes($terms) . "%' ";
    $where .= " OR firstname LIKE '%" . addslashes($terms) . "%' ";
    $where .= " OR company LIKE '%" . addslashes($terms) . "%' ";
    $where .= " OR email LIKE '%" . addslashes($terms) . "%' ";
    $where .= " OR phone LIKE '%" . addslashes($terms) . "%' ";

    return DB::loadAll('customer', $where . " ORDER BY " . addslashes($orderby) . " DESC");
  }


}

