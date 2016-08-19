<?php
/**
 * Email Class
 */

class Email {
  
	/**
	 * Send Email
   * @param type $recipient_email
   * @param type $recipient_name
   * @param type $from_email
   * @param type $from_name
   * @param type $subject
   * @param type $body
   * @param type $attachments
   * @param type $replyto_email
   * @param type $replyto_name
   * @throws Exception
   */
	static function send($recipient_email, $recipient_name, $from_email, $from_name, $subject, $body, $attachments = array(), $replyto_email = null, $replyto_name = null) {
    require_once(DOCUMENT_ROOT . "/inc/Utilities/PHPMailer/core.php");
    require_once(DOCUMENT_ROOT . "/inc/Utilities/PHPMailer/smtp.php");
    
    $mail = new PHPMailer();
    
    $mail->validateAddress($recipient_email);

    $mail->CharSet = 'UTF-8';

    $mail->IsSMTP();
    $mail->SMTPDebug  = SMTP_DEBUG; // SMTP debug information / 1 = errors and messages / 2 = messages only
    $mail->SMTPAuth   = true;
    $mail->Host       = SMTP_HOST;
    $mail->Port       = SMTP_PORT;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;

    $mail->AddAddress($recipient_email, $recipient_name);
    if(DEBUG) {
      $mail->addBCC(DEV_EMAIL, 'Dev Team');
    } 
    $mail->SetFrom($from_email, $from_name);
    if(!empty($replyto_email)) {
      $mail->addReplyTo($replyto_email, $replyto_name);
    }
    

    $mail->Subject = $subject;
    $mail->MsgHTML($body);


    //$mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
    ///$body = eregi_replace("[\]",'', $body); // Test

    // Attach Files
		if(is_array($attachments) && count($attachments) > 0) {
      foreach($attachments as $attachment) {
        if(is_file($attachment)) {
          $mail->AddAttachment($attachment);
        }
      }
    }
    
    // Send Email
    if(!$mail->Send()) {
      throw new Exception("Mailer Error: " . $mail->ErrorInfo);
    }
    
	}

  
	/**
	 * Verify email format
	 * Input: email address
	 * Return: true/false
	 */
	static function isValid($email) {
    require_once(DOCUMENT_ROOT . "/inc/Utilities/PHPMailer/core.php");
    
    if(empty(trim($email))) {
      return false;
    }

    $mail = new PHPMailer();
    
    if($mail->validateAddress(trim($email))) {
      return true;
    }
    else {
      return false;
    }
  }

  
	/**
	 * Send a test email
	 */
	static function test() {
    $lang = Lang::load('signup', 'en');

    $member = new Member(20);
    $user = new User(20);

    global $smarty;
    $smarty->assign("user", $user->info);
    $smarty->assign("member", $member->info);

    $smarty->assign("content", $smarty->fetch("email/signup-notice.tpl"));

    try {
      self::send(
        'schanaco@gmail.com', 'Travis Schanafelt', 
        SITE_EMAIL, SITE_TITLE, 
        'Test Email', 
        $smarty->fetch("email/main.tpl")
      );
    } catch (phpmailerException $e) {
      print_r($e->errorMessage());
    } catch (Exception $e) {
      print_r($lang->error->email_notice);
    }

  }
  

}

