<?php
/**
 * Audio Interface
 * Uses SOX toolkit
 *
 * Supported Audio Formats:
 * mp3, wav, wma
 *
 * Capable Audio Formats:
 * 8svx aif aifc aiff aiffc al amb amr-nb amr-wb anb au avr awb caf
 * cdda cdr cvs cvsd cvu dat dvms f32 f4 f64 f8 fap flac fssd gsm
 * gsrt hcom htk ima ircam la lpc lpc10 lu mat mat4 mat5 maud nist
 * ogg paf prc pvf raw s1 s16 s2 s24 s3 s32 s4 s8 sb sd2 sds sf sl
 * sln smp snd sndfile sndr sndt sou sox sph sw txw u1 u16 u2 u24
 * u3 u32 u4 u8 ub ul uw vms voc vorbis vox w64 wav wavpcm wv wve xa xi
*/

class DocumentInterface_Audio {

  protected $type = 'audio';
  
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
    $objectname = $info->member_id . "_" . md5(random_string());

    // Establish filenames
    $raw_name   = DOCUMENT_ROOT . "/docs/uploads/" . $info->objectname;
    $audio_name = DOCUMENT_ROOT . "/docs/bucket/" . $objectname . ".mp3";
    
    
    // Figure out what type of file this is
    $parts = pathinfo($raw_name);
    $extension = strtolower($parts['extension']);

    // Move or convert
    if($extension == "mp3") {
      // Don't convert, just move
      rename($raw_name, $audio_name);
    }
    else {
      // Convert audio with SOX and remove raw file
      $return = shell_exec("/usr/bin/sox $raw_name $audio_name");
      unlink($raw_name);
    }

    // Update main document entry
    $sql = "UPDATE `document`
                SET `objectname` = '" . $objectname . ".mp3',
                    `contenttype` = 'mp3',
                    `active` = 1
                WHERE id = " . (int) $info->id;
    DB::query($sql);
  }

}