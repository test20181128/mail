<?php
include_once('mail/class.phpmailer.php');
class Send {
	private $token = '57744c1def01da4f91cb3153927c6234';
	private $error;
	private $config = [
		// 'mail_type'         => 'SMTP',
		// 'mail_host'         => '',
		// 'mail_port'         => 25,
		// 'mail_user'         => '',
		// 'mail_pass'         => '',
		// 'mail_timeout'      => 10,
		'sender_to_mail'    => '',
		'sender_from_mail'  => '',
		'sender_author'     => '',
		'sender_subject'    => '',
		'sender_content'    => ''
	];
	private $not_required = [
		// 'mail_type',
		// 'mail_port',
		// 'mail_timeout'
	];
	public function index() {
		if (!$this->validate()) {
			echo $this->error;
			exit;
		}
		print_r($this->config);
		$this->sendOut();
	}
	private function validate() {
		if (empty($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
			return $this->error('hello world!');
		}
		if (empty($_POST['token']) || $_POST['token'] != $this->token) {
			return $this->error('hello world!');
		}
		foreach ($this->config as $key => $value) {
			if (empty($_POST[$key]) && !in_array($key, $this->not_required)) {
				return $this->error('param error: '.$key.' empty');
			}
			if (!empty($_POST[$key])) {
				$this->config[$key] = $_POST[$key];
			}
		}
		return true;
	}
	private function error($message) {
		$this->error = $message;
		return false;
	}
	private function sendOut() {
		try {
			$mail = new PHPMailer();
			$mail->IsSMTP();  //telling the class to use SMTP
			$mail->isHTML(true);
			$mail->CharSet      = 'UTF-8'; //设置邮件的字符编码
			$mail->Host         = $_POST['smtp_host']; //also tried "relay-hosting.secureserver.net"
			$mail->SMTPAuth     = true;
			$mail->SMTPDebug    = 3;
			$mail->SMTPSecure   = $_POST['smtp_secure'];
			$mail->Port         = $_POST['smtp_port'];
			$mail->Username     = $_POST['smtp_user'];
			$mail->Password     = $_POST['smtp_pass'];
			$mail->From         = $_POST['sender_from_mail'];
			$mail->FromName     = $_POST['sender_author'];
			$mail->AddReplyTo($_POST['sender_from_mail'], $_POST['sender_author']);//回复地址
			$mail->AddAddress($_POST['sender_to_mail']);//收件人地址
			$mail->Subject      = "phpmailer测试标题";
			$mail->Body         = "<h1>phpmail演示</h1>这是php点点通（<font color=red>www.phpddt.com</font>）对phpmailer的测试内容";
			$mail->AltBody      = "To view the message, please use an HTML compatible email viewer!"; //当邮件不支持html时备用显示，可以省略
			$mail->WordWrap     = 250;
			$mail->Send();
		} catch (phpmailerException $e) {
			echo "邮件发送失败：".$e->errorMessage();
		}
	}
}
$s = new send();
$s->index();
