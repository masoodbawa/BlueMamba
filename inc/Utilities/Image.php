<?php

/**
 * Image Functions
 */
class Image {

  /**
   * Resize Image (JPG, PNG, GIF)
   * @param type $fixed_width
   * @param type $fixed_height
   * @param type $sourcefile
   * @param type $destfile
   * @param type $quality
   * @param int $colors
   * @return boolean
   */
  function resize($fixed_width, $fixed_height, $sourcefile, $destfile, $quality = 90, $thumbnail = false) {
    if(!file_exists($sourcefile)) {
      return "Error file does not exist $sourcefile";
    }
    
    // Figure out what type of image this is, the load as that type
    $parts = pathinfo($sourcefile);
    $extension = strtolower($parts['extension']);

    switch($extension) {
      case 'gif':
        $image_source = imagecreatefromgif($sourcefile);
        break;
      case 'png':
        $image_source = imagecreatefrompng($sourcefile);
        break;
      default: //jpg-jpeg
        $image_source = imagecreatefromjpeg($sourcefile);
        break;
    }
    
    // Check EXIF for rotation, if set then rotate
    $degrees = $this->getOrientation($sourcefile);
    if($degrees <> 0) {
      $image_source = imagerotate($image_source, $degrees, 0);
    }

    // Get Source Dimentions
    $source_width = imagesx($image_source);
    $source_height = imagesy($image_source);
    
    // Landscape (or square)
    if($source_width >= $source_height) {
      $dest_width = $fixed_width;
      $dest_height = $fixed_width * ($source_height / $source_width);
      $dest_x = 0;
      $dest_y = 0;
      
      // Is the calculated height smaller than the output needed for thumbnail
      if($thumbnail == true) {
        $dest_width = $fixed_height * ($source_width / $source_height);
        $dest_height = $fixed_height;
        $dest_x = ($fixed_width - $dest_width) / 2; // Need to be a negative
        $dest_y = 0;
      }
    }
    
    // Portrait
    if($source_width < $source_height) {
      $dest_width = $fixed_width * ($source_width / $source_height);
      $dest_height = $fixed_width;
      $dest_x = 0;
      $dest_y = 0;
      
      // Thumbnail
      if($thumbnail == true) {
        $dest_width = $fixed_width;
        $dest_height = $fixed_width * ($source_height / $source_width);
        $dest_x = 0;
        $dest_y = ($fixed_height - $dest_height) / 2; // Need to be a negative
      }
    }

    if($thumbnail == true) {
      $image_dest = imagecreatetruecolor($fixed_width, $fixed_height);
    }
    else {
      $image_dest = imagecreatetruecolor($dest_width, $dest_height);
    }
    
    imagefill($image_dest, 0, 0, imagecolorallocate($image_dest, 255, 255, 255));

    // Add source image then save
    imagecopyresampled($image_dest, $image_source, $dest_x, $dest_y, 0, 0, $dest_width, $dest_height, $source_width, $source_height);
    imagejpeg($image_dest, $destfile, $quality);

    imagedestroy($image_dest);
    imagedestroy($image_source);

    return true;
  }

  /**
   * Get EXIF orientation
   * @param type $filename
   * @return int
   */
  function getOrientation($filename) {
    // Evaluate exif data from photo
    $exif = exif_read_data($filename);

    // Get the orientation
    $orientation = $exif['Orientation'];

    $rotate = 0;
    // Determine what oreientation the image was taken at
    switch($orientation) {
      case 2: // horizontal flip
        $flip = true;
        break;
      case 3: // 180 rotate left
        $rotate = 180;
        break;
      case 4: // vertical flip
        $flip = true;
        $rotate = 180;
        break;
      case 5: // vertical flip + 90 rotate right
        $flip = true;
        $rotate = 90;
        break;
      case 6: // 90 rotate right
        $rotate = -90;
        break;
      case 7: // horizontal flip + 90 rotate right
        $flip = true;
        $rotate = -90;
        break;
      case 8: // 90 rotate left
        $rotate = 90;
        break;
    }
    return $rotate;
  }


  /**
   * Watermark Image
   * @param type $sourcefile
   * @param type $quality
   * @param type $transparency
   * @return boolean
   */
  function watermark($sourcefile, $quality, $transparency = 75) {
    if(!file_exists($sourcefile)) {
      return "Error file does not exist $sourcefile";
    }
    if(!file_exists(DOCUMENT_ROOT . "/images/watermark.gif")) {
      return "Error watermark file does not exist $sourcefile";
    }
    
    $watermark = imagecreatefromgif(DOCUMENT_ROOT . "/images/watermark.gif");

    $watermark_width = imagesx($watermark);
    $watermark_height = imagesy($watermark);


    $image = imagecreatefromjpeg($sourcefile);

    $size = getimagesize($sourcefile);
    $dest_x = $size[0] - $watermark_width - 10;
    $dest_y = $size[1] - $watermark_height - 10;

    imagealphablending($image, true);
    imagealphablending($watermark, true);
    imagecopymerge($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, $transparency);


    imagejpeg($image, $sourcefile, $quality);

    imagedestroy($watermark);
    imagedestroy($image);

    return true;
  }

}

