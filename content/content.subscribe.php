<?php

	if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

	require_once(TOOLKIT . '/class.event.php');
	require_once(TOOLKIT . '/class.ajaxpage.php');
	require_once(CORE . '/class.frontend.php');
	require_once(EXTENSIONS . '/mailchimp/events/event.mailchimp.php');
	
	/**
	 * This class wraps around the event.mailchimp.php class for
	 * extending the event into an Ajax request
	 * @author nicolasbrassard
	 *
	 */
	Class contentExtensionMailchimpSubscribe extends AjaxPage {
		
		// pointer to the "fake" event
		protected $_event = null;
		
		/**
		 * Object ctor
		 * @param $parent Administration class
		 */
		public function __construct($parent) {
			// call the parent ctor
			parent::__construct($parent);
			
			// creates a mail chimp event 
			$this->_event = new eventMailchimp($parent, array());
		}
		
		/**
		 * Method that build the result send to the client
		 */
		public function view() {
			// gets the output
			$output = $this->_event.load();
			
			if (strlen($output) < 1) {
				// no output, must manage error
				$output = __('Error');
			}
			
			$this->_Result = $output;
			
			var_dump($this->_Result);
		}
		
		/**
		 * Generate the output
		 */
		public function generate(){
			header('Content-Type: application/json');
			echo $this->_Result;
			exit;
		}
		
		/**
		 * Overrides the default autorisation failed mechanisism
		 */
		public function handleFailedAuthorisation(){
			// do nothing, we do not want any autorisation on this page
			$this->_status = self::STATUS_OK;
			//exit;
		}
		
	}