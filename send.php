<?php
use google\appengine\api\mail\Message;

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
		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			$this->error = 'hello world!';
			return false;
		}

		if (empty($_POST['token']) || $_POST['token'] != $this->token) {
			$this->error = 'hello world!';
			return false;
		}

		foreach ($this->config as $key => $value) {
			if (empty($_POST[$key]) && !in_array($key, $this->not_required)) {
				$this->error = 'param error: '.$key.' empty';
				return false;
			}

			if (!empty($_POST[$key])) {
				$this->config[$key] = $_POST[$key];
			}
		}

		return true;
	}

	private function sendOut() {
		try {
		    $message = new Message();
		    $message->setSender(strtolower($this->config['sender_from_mail']));
		    $message->addTo(strtolower($this->config['sender_to_mail']));
		    $message->setSubject(html_entity_decode($this->config['sender_subject'], ENT_QUOTES, 'UTF-8'));
		    $message->setHtmlBody($this->config['sender_content']);
		    // $message->addAttachment('image.jpg', $image_data, $image_content_id);
		    $message->send();
		    echo '[ok]';
		} catch (InvalidArgumentException $e) {
		    echo '[error]';
		}
	}
}

$s = new send();
$s->index();
