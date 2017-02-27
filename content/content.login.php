<?php

if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

require_once(EXTENSIONS . '/mailchimp/events/event.mailchimp.php');

/**
 * This class wraps around the event.mailchimp.php class for
 * extending the event into an Ajax request
 * @author nicolasbrassard - http://www.nitriques.com/open-source/
 * N.B. Naming the page 'login' makes symphony treat this page as
 * the login page: It is accessible without authentication
 */
class contentExtensionMailchimpLogin extends JSONPage {

    /**
     * Method that build the result send to the client
     */
    public function view() {
        // creates a mail chimp event
        $_event = new eventMailchimp();

        // gets the output
        $output = $_event->load();

        // converts object to array
        if ($output instanceof XMLElement) {
            try {
                $output = $output->generate();
                $output = (array) simplexml_load_string($output);
            } catch (Exception $e) {
                // do nothing
                $output = array(
                    'error' => __('Error, could not process the request')
                );
            }
        }

        if (empty($output)) {
            // no output, must manage error
            $output = array('error' => __('Error, could not process the request'));
        }

        // set body of the response
        $this->_Result = $output;
    }

}