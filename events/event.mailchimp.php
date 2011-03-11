<?php

	if(!defined('__IN_SYMPHONY__')) die('<h2>Error</h2><p>You cannot directly access this file</p>');

	include_once(EXTENSIONS . '/mailchimp/lib/class.mcapi.php');

	Class eventMailchimp extends Event
	{		
		protected $_driver = null;
		
		public function __construct(&$parent, $env = null) {
			parent::__construct($parent, $env);
			
			$this->_driver = Frontend::instance()->ExtensionManager->create('mailchimp');
		}
		
		public static function about()
		{								
			return array(
						 'name' => 'MailChimp',
						 'author' => array('name' => 'Mark Lewis',
										   'website' => 'http://www.casadelewis.com',
										   'email' => 'mark@casadelewis.com'),
						 'version' => '1.0',
						 'release-date' => '2009-05-8',
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
			return new XMLElement('p', 'Subscribes e-mail address to Mailchimp list.');
		}
		
		protected function __trigger()
		{			
			$email = $_POST['email'];
			
			$merge = $_POST['merge'];

			$result = new XMLElement("mailchimp");			
			
			$api = new MCAPI($this->_driver->getUser(), $this->_driver->getPass());

			$cookies = new XMLElement("cookies");	
			
			foreach($merge as $key => $val)
			{
				if(!empty($val))
				{
				$cookie = new XMLElement('cookie', $val);
				$cookie->setAttribute("handle", $key);		

				$cookies->appendChild($cookie);
				}
			}
			
			$cookie = new XMLElement('cookie', $email);
			$cookie->setAttribute("handle", 'email');		

			$cookies->appendChild($cookie);
							
			$result->appendChild($cookies);
			
			if($merge['fname'] == '')
			{
				$error = new XMLElement('error', 'First name is required.');
				$error->setAttribute("handle", 'fname');		

				$result->appendChild($error);
				$result->setAttribute("result", "error");		
				
				return $result;
			}
				
			if($merge['lname'] == '')
			{
				$error = new XMLElement('error', 'Last name is required.');
				$error->setAttribute("handle", 'lname');		

				$result->appendChild($error);
				$result->setAttribute("result", "error");		
				
				return $result;
			}	
			
			if($email == '')
			{
				$error = new XMLElement('error', 'E-mail is required.');
				$error->setAttribute("handle", 'email');		

				$result->appendChild($error);
				$result->setAttribute("result", "error");		
				
				return $result;
			}
				
			if(!ereg('^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.(([0-9]{1,3})|([a-zA-Z]{2,3})|(aero|coop|info|museum|name))$', $email))
			{
				$error = new XMLElement('error', 'E-mail is invalid.');
				$error->setAttribute("handle", 'email');		

				$result->appendChild($error);
				$result->setAttribute("result", "error");		
				
				return $result;
			}	
																		
			if(!$api->listSubscribe($this->_driver->getList(), $email, array_change_key_case($merge, CASE_UPPER)))
			{
				$result->setAttribute("result", "error");		
				$error = new XMLElement("error", $api->errorMessage);									
				$result->appendChild($error);
			}
			else
			{
				$result->setAttribute("result", "success");	
			}
			
			return $result;
		}		
	}

?>