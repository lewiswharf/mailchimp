<?php

	if(!defined('__IN_SYMPHONY__')) die('<h2>Error</h2><p>You cannot directly access this file</p>');

	include_once(EXTENSIONS . '/mailchimp/lib/class.mcapi.php');

	Class eventMailchimp extends Event
	{
		protected $_driver = null;

		public function __construct(&$parent, $env = null) {
			parent::__construct($parent, $env);

			$this->_driver = Symphony::ExtensionManager()->create('mailchimp');
		}

		public static function about()
		{
			return array(
						 'name' => 'MailChimp',
						 'author' => array('name' => 'Mark Lewis',
										   'website' => 'http://www.casadelewis.com',
										   'email' => 'mark@casadelewis.com'),
						 'version' => '1.15',
						 'release-date' => '2011-12-11',
						 'trigger-condition' => 'action[subscribe]'
						 );
		}

		public function load()
		{
			if(isset($_POST['action']['signup']))
				return $this->__trigger();
		}

		public static function documentation()
		{
			return new XMLElement('p', 'Subscribes user to a MailChimp list.');
		}

		protected function __trigger()
		{
			$result = new XMLElement("mailchimp");
			$cookies = new XMLElement("cookies");

			$email = $_POST['email'];

			$cookie = new XMLElement('cookie', $email);
			$cookie->setAttribute("handle", 'email');

			$cookies->appendChild($cookie);

			$result->appendChild($cookies);

			$api = new MCAPI($this->_driver->getKey());

			$mergeVars = $api->listMergeVars($this->_driver->getList());

			if(count($mergeVars) > 1) {
				$merge = $_POST['merge'];
				foreach($merge as $key => $val)
				{
					if(!empty($val))
					{
					$cookie = new XMLElement('cookie', $val);
					$cookie->setAttribute("handle", $key);

					$cookies->appendChild($cookie);
					}
				}
			}

			if(!ereg('^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.(([0-9]{1,3})|([a-zA-Z]{2,3})|(aero|coop|info|museum|name))$', $email))
			{
				$error = new XMLElement('error', 'E-mail is invalid.');
				$error->setAttribute("handle", 'email');

				$result->appendChild($error);
				$result->setAttribute("result", "error");

				return $result;
			}

			if(count($mergeVars) == 1) {
				$merge ='';
			}

			if(!$api->listSubscribe($this->_driver->getList(), $email, $merge)) {
				$result->setAttribute("result", "error");

				// try to match mergeVars with error
				if(count($mergeVars) > 1){
					// replace
					foreach($mergeVars as $var) {
						$errorMessage = str_replace($var['tag'], $var['name'], $api->errorMessage, $count);
						if($count == 1) {
							$error = new XMLElement("error", $errorMessage);
							break;
						}
					}
				}

				// no error message found with merge vars in it
				if ($error == null) {
					$msg = $api->errorMessage;

					// replace ampersands and replace to make XML happy
					$msg = str_replace('&', '&amp;', $msg);

					$error = new XMLElement("error", strlen($msg) > 0 ? $msg : 'Unknow error');
				}

				$result->appendChild($error);

			}
			else {
				$result->setAttribute("result", "success");
			}

			return $result;
		}
	}

?>