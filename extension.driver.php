<?php

	if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

	Class extension_mailchimp extends Extension{
	
		public function about(){
			return array('name' => 'MailChimp',
						 'version' => '1.0',
						 'release-date' => '2009-05-8',
						 'author' => array('name' => 'Mark Lewis',
										   'website' => 'http://www.casadelewis.com',
										   'email' => 'mark@casadelewis.com'),
						 'description' => 'Supports subscribing a new address, first name, last name to your MailChimp account.'
				 		);
		}
				
		public function uninstall() {
			Symphony::Configuration()->remove('mailchimp');
			Symphony::Configuration()->saveConfig();
		}
		
		public function getSubscribedDelegates() {
			return array(
				array(
					'page'		=> '/system/preferences/',
					'delegate'	=> 'AddCustomPreferenceFieldsets',
					'callback'	=> 'addCustomPreferenceFieldsets'
				)
			);
		}
		
	/*-------------------------------------------------------------------------
		Utilities:
	-------------------------------------------------------------------------*/
		
		public function getUser() {
			return Symphony::Configuration()->get('user', 'mailchimp');
		}
		
		public function getPass() {
			return Symphony::Configuration()->get('pass', 'mailchimp');
		}
		
		public function getList() {
			return Symphony::Configuration()->get('list', 'mailchimp');
		}
		
	/*-------------------------------------------------------------------------
		Delegates:
	-------------------------------------------------------------------------*/
		
		public function addCustomPreferenceFieldsets($context) {
			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->appendChild(
				new XMLElement('legend', 'Mailchimp')
			);
			
			$group = new XMLElement('div');
			$group->setAttribute('class', 'group');

			$user = Widget::Label('Username');
			$user->appendChild(Widget::Input(
				'settings[mailchimp][user]', General::Sanitize($this->getUser())
			));
			$group->appendChild($user);
			
			$pass = Widget::Label('Password');
			$pass->appendChild(Widget::Input(
				'settings[mailchimp][pass]', General::Sanitize($this->getPass()), 'password'
			));
			$group->appendChild($pass);

			$fieldset->appendChild($group);

			$list = Widget::Label('List ID');
			$list->appendChild(Widget::Input(
				'settings[mailchimp][list]', General::Sanitize($this->getList())
			));
			$fieldset->appendChild($list);

			$context['wrapper']->appendChild($fieldset);			
		}
	}

?>