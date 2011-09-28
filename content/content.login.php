<?php

	if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

	require_once(TOOLKIT . '/class.event.php');
	require_once(TOOLKIT . '/class.page.php');
	require_once(CORE . '/class.frontend.php');
	require_once(EXTENSIONS . '/mailchimp/events/event.mailchimp.php');

	/**
	 * This class wraps around the event.mailchimp.php class for
	 * extending the event into an Ajax request
	 * @author nicolasbrassard - http://www.nitriques.com/open-source/
	 * N.B. Naming the page 'login' makes symphony treat this page as
	 * the login page: It is accessible without authentification
	 */
	Class contentExtensionMailchimpLogin extends JSONPage {

		/**
		 * Method that build the result send to the client
		 */
		public function view() {
			// creates a mail chimp event
			$_event = new eventMailchimp($parent, array());

			// gets the output
			$output = $_event->load();

			// converts object to array
			if ($output instanceof XMLElement) {
				try {
					$output = $output->generate();
					$output = (array) simplexml_load_string($output);
				} catch (Exception $e) {
					// do nothing
					$output = null;

					//var_dump($e);
				}
			}

			if (empty($output)) {
				// no output, must manage error
				$output = array('error' => __('Error, could not process the request'));
			}

			// set body of the response
			$this->_Result = json_encode($output);
		}

	}

	/**
	 * Abstract class that sets basic params for a JSON page
	 * @author nicolasbrassard
	 *
	 */
	Abstract Class JSONPage extends Page {

		/**
		 * Method that builds the result send to the client
		 */
		public abstract function view();

		/**
		 * Generate the output
		 */
		public function generate(){
			header('Content-Type: application/json');

			// tryed this, not working
			//$this->addHeaderToPage('Content-Type','application/json');

			// creates the output
			$this->view();

			echo $this->_Result;
			exit;
		}

		/**
		 * Dummy method to be compatible with normal Administration pages
		 */
		public function build() {
			return $this->generate();
		}

	}