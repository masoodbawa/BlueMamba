<?php
/**
 * Video Interface
 */

class DocumentInterface_Video {
  
  protected $type = 'video';
  
  /**
   * Construct
   */
	public function __construct() {
	}

  /**
   * Process Images
   * @param type $info
   */
  public function process($info) {
    
    DebugLog::create("Process Video - Underconstruction: " . $info->objectname);
    
  }
}

