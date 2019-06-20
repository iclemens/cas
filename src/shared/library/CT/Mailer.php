<?php
	/**
 	 * Citrus-IT Mailer module
 	 *
 	 * @author     Ivar Clemens <ivar@citrus-it.nl>
 	 * @package 	 boekhouding
	 */

	require_once 'mail/htmlMimeMail.php';

	/**
	 * Mailer class
	 *
	 * @package		boekhouding
	 */
	class CT_Mailer extends htmlMimeMail
	{

		function __construct() 
		{
			$config = Zend_Registry::get('config');

			parent::__construct();

			$this->setHeader('Date', date('D, d M y H:i:s O'));
			$this->setReturnPath($config->mailer->return_path);
			$this->setFrom($config->mailer->from_address);

			if(strlen($config->mailer->copy_to) > 0)
				$this->setCc($config->mailer->copy_to);

			$server 	= $config->mailer->server;
			$port 		= $config->mailer->port;
			$username 	= $config->mailer->username;
			$password 	= $config->mailer->password;

			if(strlen($server) == 0)	$server = null;
			if(strlen($port) == 0) 		$port = null;
			if(strlen($username) == 0) 	$username = null;
			if(strlen($username) == 0) 	$password = null;

			if($username != null)				$auth = true;	else $auth = false;

			$this->setSMTPParams($server, $port, null, $auth, $username, $password);
		}

		public function send($recipients, $type = null)
		{
			$config = Zend_Registry::get('config');
			$logger = Zend_Registry::get("logger");

			if(is_null($type))
				$type = $config->mailer->method;

			if(!is_array($recipients))
				$recipients = array($recipients);
			
			$status = parent::send($recipients, $type);

			if($status == false) {
				if(is_array($this->errors)) {
					$msg = '';
					
					foreach($this->errors as $error) {
						if(strlen($msg) == 0)
							$msg = $error;
						else
							$msg = $msg . ',' . $error;
					}
				} else {
					$msg = $this->errors;
				}

				$logger->log("[Mailer] E-mail aan " . $recipients . " is niet verzonden: " . $msg, Zend_Log::NOTICE);

				throw new Exception("De email is niet verzonden.");
			} else {
				$logger->log("[Mailer] E-mail aan " . $recipients . " is verzonden", Zend_Log::NOTICE);
			}
		}
	}
?>
