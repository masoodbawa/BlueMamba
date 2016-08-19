<?php
/**
 * Image Interface
 */

class DocumentInterface_Image {

  protected $type = 'image';
  
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

    // Generate unique filename
    $image_objectname = $info->member_id . "_" . md5(random_string());
    $thumb_objectname = $image_objectname . "_tn";
    $icon_objectname  = $image_objectname . "_ic";

    // Establish filenames
    $raw_name   = DOCUMENT_ROOT . "/docs/uploads/" . $info->objectname;
    $image_name = DOCUMENT_ROOT . "/docs/bucket/" . $image_objectname . ".jpg";
    $thumb_name = DOCUMENT_ROOT . "/docs/bucket/" . $thumb_objectname . ".jpg";
    $icon_name  = DOCUMENT_ROOT . "/docs/bucket/" . $icon_objectname . ".jpg";

    // Process Images
    $image = new Image();
    $icon_created  = $image->resize(64, 64, $raw_name, $icon_name, 95, true);
    $thumb_created = $image->resize(257, 170, $raw_name, $thumb_name, 95, true);
    $image_created = $image->resize(800, 529, $raw_name, $image_name, 95, false);
    
    // Check if created successfully
    if($icon_created != true) {
      throw new Exception("Error Processing Image Icon: " . $icon_created . ": document_id = " . $info->id);
    }
    if($thumb_created != true) {
      throw new Exception("Error Processing Image Thumnail: " . $thumb_created . ": document_id = " . $info->id);
    }
    if($image_created != true) {
      throw new Exception("Error Processing Image: " . $image_created . ": document_id = " . $info->id);
    }
    
    // Insert icon
    $document = new Document();
    $icon_document_id = $document->record(
      12, // Icon
      $info->member_id,
      null,
      SERVER_NAME,
      $icon_objectname . ".jpg",
      'jpg',
      $info->id,
      true,
      true
    );
    
    // Insert thumbnail
    $document = new Document();
    $thumb_document_id = $document->record(
      11, // Thumbnail
      $info->member_id,
      null,
      SERVER_NAME,
      $thumb_objectname . ".jpg",
      'jpg',
      $info->id,
      true,
      true
    );

    // Update main document entry
    $sql = "UPDATE `document`
                SET `objectname` = '" . $image_objectname . ".jpg',
                    `contenttype` = 'jpg',
                    `active` = 1
                WHERE id = " . (int) $info->id;
    DB::query($sql);

    // Remove raw file
    unlink($raw_name);

  }

}

