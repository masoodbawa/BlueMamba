<?php
/**
 * Document Class
 */

class Document extends BaseTable {
  
	protected $table = 'document';
	public $filetypes = array('jpg','jpeg','png','gif','mp3','wav','wma','mp4','mpg','mpeg','mov','wmv','pdf','doc','docx','xls','xlsx','odt','ods'); 
	public $interface;


	public function record($documenttype_id, $member_id, $description = null, $bucket = null, $objectname = null, $contenttype = null, $document_id = null, $active = true, $pending = false) {
    if($this->id > 0) {
      $this->load($this->id);
    } else {
      $this->info = DB::dispense($this->table);
    }

    $this->info->document_id = ($document_id > 0 ? (int) $document_id : null);
    $this->info->documenttype_id = (int) $documenttype_id;
    $this->info->member_id = (int) $member_id;
    
    if(!empty($description)) {
      $profanity = new Profanity();
      $this->info->description = $profanity->clean(trim(addslashes($description)));
    }
    $this->info->bucket = ($bucket === null ? CLOUD_BUCKET : trim(addslashes($bucket)));
    $this->info->objectname = trim(addslashes($objectname));
    $this->info->contenttype = ($contenttype === null ? null : trim(addslashes($contenttype)));    

    $this->info->dateadded = date("Y-m-d h:i:s");
    $this->info->uploadkey = $this->getUploadKey();
    $this->info->pending = $pending;
    $this->info->active = $active;

    $id = $this->save($this->info);

    $this->load($id);
    return $id;
  }
  
  
  /**
   * Initialize Document Interface based on Document Type
   * @param type $documenttype_id
   */
  public function initInterface($documenttype_id = null) {
    if(empty($documenttype_id)) {
      $documenttype_id = $this->info->documenttype_id;
    }
    if(empty($documenttype_id)) {
      throw new Exception('Document initInterface documenttype_id can not be empty');
    }
    
    // Get Interface
    $interface_data = DB::getRow("SELECT * FROM documenttype WHERE id = " . (int) $documenttype_id);
    
    // Init Interface
    $baseinterface = new BaseInterface($interface_data['interface_id']);
    $interface = $baseinterface->init();
    $interface->filetypes = json_decode($interface_data['filetypes']);
    
    unset($baseinterface);
    return $interface;
  }
  
  
  /**
   * Upload Document to Server
   * @param type $documenttype_id
   */
  public function upload($member_id, $fieldname = 'Filedata') {
    if($member_id <= 0) {
      return array('error' => 'Member id not specified.');
    }
    
    $uploadkey = $this->getUploadKey();
    if(empty($uploadkey)) {
      return array('error' => 'Upload key not specified.');
    }
    
    // Validate the file type
    $parts = pathinfo($_FILES[$fieldname]['name']);
    $extension = strtolower($parts['extension']);
    
    $filename = $member_id . '_' . md5(random_string()) . '.' . $extension;
    $tempfile = $_FILES[$fieldname]['tmp_name'];
    $destfile = DOCUMENT_ROOT . '/docs/uploads/' . $filename;

    if (in_array($extension, $this->filetypes)) {
      $document_id = 0;
      $documenttype_id = DB::getCell("SELECT id FROM documenttype WHERE filetypes LIKE '%" . $extension . "%'");
      if($documenttype_id > 0) {
        $document_id = $this->record(
          $documenttype_id,
          $member_id, 
          null, 
          SERVER_NAME, 
          $filename, 
          $extension, 
          null, 
          false, 
          true
        );
      } 
      if($document_id > 0) {
        move_uploaded_file($tempfile, $destfile);
        return array('document_id' => $document_id);
      }
      else {
        return array('error' => 'Error occured while saving document.');
      }
    }
    else {
      return array('error' => 'Invalid file type ' . $extension . ' .');
    }
  }
  
  
  /**
   * Link Member's pending documents to post
   * @param type $member_id
   * @param type $post_id
   */
  public function linkPost($member_id, $post_id) {
    
    // Get pending documents for this member, on this server that have not yet been linked to post
    $sql = "SELECT d.id 
              FROM `document` d
              LEFT JOIN `document_post` dp ON dp.document_id = d.id
              WHERE `pending` = 1 
                AND `member_id` = " . (int) $member_id . "
                AND `uploadkey` = '" . $this->getUploadKey() . "'
                AND `bucket` = '" . SERVER_NAME . "'
                AND ISNULL(dp.post_id)";
    $documents = DB::getAll($sql);
    
    // Get last sortorder to tack on new documents
    $sql = "SELECT max(sortorder) 
              FROM `document_post`
              WHERE `post_id` = " . (int) $post_id;
    $sortorder = DB::getCell($sql);
    $sortorder += 1;
    
    // Add new
    foreach($documents as $doc) {
      $sql = "INSERT INTO `document_post`
                SET `document_id` = " . (int) $doc['id'] . ",
                    `post_id` = " . (int) $post_id . ",
                    `sortorder` = " . (int) $sortorder;
      DB::query($sql);
      $sortorder++;
    }
  }
  
  
  /**
   * Link Member's pending documents to themselves
   * @param type $member_id
   */
  public function linkMember($member_id) {
    
    // Get pending documents for this member, on this server that have not yet been linked to member
    $sql = "SELECT d.id 
              FROM `document` d
              LEFT JOIN `document_member` dm ON dm.document_id = d.id
              WHERE `pending` = 1 
                AND d.member_id = " . (int) $member_id . "
                AND `bucket` = '" . SERVER_NAME . "'
                AND ISNULL(dm.member_id)";
    $documents = DB::getAll($sql);
    
    // Increase sort order for past documents
    if(count($documents) >= 1) {
      $sql = "UPDATE `document_member`
                SET `sortorder` = (sortorder + " . (int) count($documents) . ")
                WHERE `member_id` = " . (int) $member_id;
      DB::query($sql);
    }
    
    // Add new
    $sortorder = 1;
    foreach($documents as $doc) {
      $sql = "INSERT INTO `document_member`
                SET `document_id` = " . (int) $doc['id'] . ",
                    `member_id` = " . (int) $member_id . ",
                    `sortorder` = " . (int) $sortorder;
      DB::query($sql);
      $sortorder++;
    }
  }
  
  
  /**
   * Process Recently Added Documents
   * @param type $uploadkey
   */
  public function process($uploadkey) {
    
    // Get all pending documents
    $where = "pending = 1 
                AND active = 0 
                AND uploadkey = '" . $uploadkey . "'
                AND bucket = '" . SERVER_NAME . "'
              ORDER BY documenttype_id";
    $documents = DB::loadAll("document", $where);
    foreach($documents as $doc) {
      
      // Check if file exists
      $filename = DOCUMENT_ROOT . '/docs/uploads/' . $doc->objectname;
      
      if(is_file($filename)) {
        
        // Load interface
        $this->load($doc->id);
        
        $interface = $this->initInterface();
        try {
          $interface->process($this->info);
        } 
        catch (Exception $e) {
          // Roll exception through
          throw new Exception($e->getMessage());
        }
        //unset($interface);
        
      }
      else {
        throw new Exception("Document Process (ID: " . $doc->id . "). File does not exist: " . $filename);
      }
    }
    
    // Clear out upload key from table
    DB::query("UPDATE document SET uploadkey = NULL WHERE uploadkey = '" . addslashes($uploadkey) . "'");
    
  }
    
    
  /**
   * Transfer file to cloud
   */
  public function transfer() {
    
    // Load Cloud Interface
    $interface = new BaseInterface(CLOUD_INTERFACE_ID);
    $cloud = $interface->init();
    
    // Transfer to Cloud
    $cloud->save();    
  }
  
  
  /**
   * Get Existing Document ID
   * @param type $documenttype_id
   * @param type $tablename
   * @param type $item_id
   * @return type
   */
  public function getExisting($documenttype_id, $tablename = 'post', $item_id) {
    
		$sql = "SELECT d.id AS document_id
							FROM document_" . $tablename . " di
							JOIN document d ON d.id = di.document_id
							WHERE di." . $tablename . "_id='" . (int) $item_id . "' 
              AND d.documenttype_id='" . (int) $documenttype_id . "'";
		$document_id = DB::getCell($sql);
		return $document_id;
  }
  
  
  /**
   * Create Special Key for Tracking Upload Session
   */
  public function createUploadKey() {
    $_SESSION['uploadkey'] = md5(random_string());
    return $_SESSION['uploadkey'];
  }
  
  /**
   * Get Special Key for Tracking Upload Session
   */
  public function getUploadKey() {
    if(empty($_SESSION['uploadkey'])) {
      return null;
    }
    else {
      return $_SESSION['uploadkey'];
    }
  }
  
  /**
   * Get Special Key for Tracking Upload Session
   */
  public function clearUploadKey() {
    unset($_SESSION['uploadkey']);
  }
  

}

