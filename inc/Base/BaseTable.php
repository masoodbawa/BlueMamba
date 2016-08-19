<?php
/**
 * Base Table
 */

class BaseTable {

	protected $id;
	protected $table;
	public $info;

	public function __construct($id = 0) {
		if ($this->table === null) {
			throw new Exception("The protected field \$table must be defined in the overriding class.");
		}
		if ($id > 0) {
			$this->id = $id;
			$this->load($id);
		}
	}

	/**
	 * Loads a simple product record from the DB.
	 * @param integer $id
	 */
	public function load($id) {
		$this->info = DB::load($this->table, $id);
		if ($this->info->id != $id) {
			//Not an existing record
			$this->id = null;
			return false;
		}
		else {
			$this->id = $this->info->id;
			return $this->info;
		}
	}

	/**
	 * Method to save (insert or update)
	 */
	public function save($info) {

		// Attempt to save.
    $saved_id = DB::store($info);

		// Check if it was new/update rollback on error
		if ($this->id !== NULL && $this->id != $saved_id && $saved_id !== false) {
			return false;	// Should have been an update, did an insert instead, don't keep changes.
		}
		else {
			$this->info = $info;
			$this->id = $saved_id;
			return $saved_id;
		}
	}

	/**
	 * Deletes a single record.
	 * @return boolean|mixed affected should be the number of rows deleted.
	 */

	public function delete() {
		if ($this->id === null) {
			return false;
		}
		if ($this->info === null) {
			$this->load($this->id);
		}

		//Attempt to delete.
		try {
			$affected = DB::trash($this->info);
		} catch (Exception $e) {
			return false;
		}

		return $affected;
	}


	/**
	 * Return the current records ID.
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}



}

