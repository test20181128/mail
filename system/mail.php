<?php
include_once('class.phpmailer.php');
class Mail {
	protected $to;
	protected $from;
	protected $sender;
	protected $subject;
	protected $text;
	protected $html;
	protected $attachments = array();
	public $protocol = 'mail';
	public $separator = ',';
	public $hostname;
	public $username;
	public $password;
	public $port = 25;
	public $timeout = 3;
	public $newline = "\n";
	public $crlf = "\r\n";
	public $verp = FALSE;
	public $parameter = '';
	public function isValidEmail($email){
		$pattern = '/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])(([a-z0-9-])*([a-z0-9]))+(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$/i';
		if (!preg_match($pattern,$email)) {
			return false;
		}
		return true;
	}
	public function setTo($to) {
		if (is_array($to)) {
			$this->to = $to;
		} else {
			$this->to = explode($this->separator,$to);
		}
		foreach ($this->to as $to){
			if (!$this->isValidEmail($to)) {
				exit('Error: E-Mail Address does not appear to be valid!');
			}
		}
	}
   
	public function setFrom($from) {
		$fromTmp = explode($this->separator,$from);
		$from = array_shift($fromTmp);
		if (!$this->isValidEmail($from)) {
			exit('Error: E-Mail Address does not appear to be valid!');
		}
		$this->from = $from;
	}
	
	public function addheader($header, $value) {
		$this->headers[$header] = $value;
	}
	
	public function setSender($sender) {
		$sender="=?UTF-8?B?".base64_encode($sender)."?=";
		$this->sender = $sender;
	}
	
	public function setSubject($subject) {
		$subject = preg_replace('/(,|:|\.|\"|\'|!|&|-)/s',' ',$subject);
		$subject ="=?UTF-8?B?".base64_encode($subject)."?=";
		$this->subject = $subject;
	}
	
	public function setText($text) {
		$text = nl2br($text);
		$this->text = $text;
	}
	
	public function setHtml($html) {
		$this->html = $html;
	}
	
	public function addAttachment($file, $filename = '') {
		if (!$filename) {
			$filename = basename($file);
		}
	  
		$this->attachments[] = array(
			'filename' => $filename,
			'file'     => $file
		);
	}
	
	public function send() {   
		if (!$this->to) {
			exit('Error: E-Mail to required!');
		}
	
		if (!$this->from) {
			exit('Error: E-Mail from required!');
		}
	
		if (!$this->sender) {
			exit('Error: E-Mail sender required!');
		}
	
		if (!$this->subject) {
			exit('Error: E-Mail subject required!');
		}
	
		if ((!$this->text) && (!$this->html)) {
			exit('Error: E-Mail message required!');
		}
		if ($this->protocol == 'smtp'&&$this->hostname&&$this->username) {
			if (!$this->html) {
				$message =$this->text;
			} else {
				$message = $this->html;
			}

			$mail  = new PHPMailer();

			$mail->IsSMTP();
			$mail->CharSet       = "utf-8";
			$mail->Host          = $this->hostname;
			$mail->Port          = $this->port;
			$mail->Timeout       = $this->timeout;
			$mail->SMTPAuth      = true;
			$mail->Username      = $this->username;
			$mail->Sender        = $this->username;
			$mail->Password      = $this->password;
			$mail->AddReplyTo($this->from,$this->port);
			$mail->From          = $this->from;
			$mail->FromName      = $this->sender;
			$mail->Subject       = $this->subject;
			$mail->MsgHTML($message);

			foreach ($this->attachments as $attachment) {
				$mail->AddAttachment($attachment['file'],$attachment['filename']);
			}
			foreach ($this->to as $toTmp){
				$mail->AddAddress($toTmp);
			}
			$mail->Send();
		}else{
			$boundary = '----=_NextPart_' . md5(rand());

			if (strpos(PHP_OS, 'WIN') === false) {
				$eol = $this->newline;
			} else {
				$eol = $this->crlf;
			}

			$headers  = 'From: ' . $this->sender . '<' . $this->from . '>' . $eol;
			$headers .= 'Reply-To: ' . $this->sender . '<' . $this->from . '>' . $eol;
			$headers .= 'Return-Path: ' . $this->from . $eol;
			$headers .= 'X-Mailer: PHP/' . phpversion() . $eol;
			$headers .= 'MIME-Version: 1.0' . $eol;
			$headers .= 'Content-Type: multipart/mixed; boundary="' . $boundary . '"' . $eol;

			if (!$this->html) {
				$message  = '--' . $boundary . $eol;
				$message .= 'Content-Type: text/plain; charset="utf-8"' . $eol;
				$message .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
				$message .= chunk_split(base64_encode($this->text));
			} else {
				$message  = '--' . $boundary . $eol;
				$message .= 'Content-Type: multipart/alternative; boundary="' . $boundary . '_alt"' . $eol . $eol;
				$message .= '--' . $boundary . '_alt' . $eol;
				$message .= 'Content-Type: text/plain; charset="utf-8"' . $eol;
				$message .= 'Content-Transfer-Encoding: base64' . $eol;

				if ($this->text) {
					$message .= chunk_split(base64_encode($this->text));
				} else {
					$message .= chunk_split(base64_encode('This is a HTML email and your email client software does not support HTML email!'));
				}

				$message .= '--' . $boundary . '_alt' . $eol;
				$message .= 'Content-Type: text/html; charset="utf-8"' . $eol;
				$message .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
				$message .= chunk_split(base64_encode($this->html));
				$message .= '--' . $boundary . '_alt--' . $eol;
			}

			foreach ($this->attachments as $attachment) {
				if (file_exists($attachment['file'])) {
					$handle = fopen($attachment['file'], 'r');
					$content = fread($handle, filesize($attachment['file']));
			
					fclose($handle); 
			
					$message .= '--' . $boundary . $eol;
					$message .= 'Content-Type: application/octetstream' . $eol;   
					$message .= 'Content-Transfer-Encoding: base64' . $eol;
					$message .= 'Content-Disposition: attachment; filename="' . basename($attachment['filename']) . '"' . $eol;
					$message .= 'Content-ID: <' . basename($attachment['filename']) . '>' . $eol . $eol;
					$message .= chunk_split(base64_encode($content));
				}
			}
			$to = implode($this->separator, $this->to);
			if ($this->parameter) {
				mail($to, $this->subject, $message, $headers, $this->parameter);
			} else {
				mail($to, $this->subject, $message, $headers);
			}
		}
	}
}
?>