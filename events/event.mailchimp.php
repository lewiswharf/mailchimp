<?php

	if(!defined('__IN_SYMPHONY__')) die('<h2>Error</h2><p>You cannot directly access this file</p>');

	include_once(EXTENSIONS . '/mailchimp/lib/mailchimp-api/MailChimp.class.php');

	Class eventMailchimp extends Event
	{
		protected $_driver = null;

		public function __construct() {
			parent::__construct();
			$this->_driver = Symphony::ExtensionManager()->create('mailchimp');
		}

		public static function about()
		{
			return array(
				'name' => 'MailChimp',
				'author' => array(
					'name' => 'Mark Lewis',
					'website' => 'http://www.casadelewis.com',
					'email' => 'mark@casadelewis.com'),
				'version' => '2.0',
				'release-date' => '2013-11-16',
				'trigger-condition' => 'action[subscribe]',
				'source' => 'MailChimp extension'
			);
		}

		public function load()
		{
			if(isset($_POST['action']['signup']) || isset($_POST['action']['subscribe']))
				return $this->__trigger();
		}

		public static function documentation()
		{
			$docs = '<h3>Example Front-end Form Markup</h3>
        <p>This is an example of the form markup you can use on your frontend to subscribe to a list:</p>
        <pre class="XML"><code>&lt;form method="post" enctype="multipart/form-data">
  &lt;label>Email
    &lt;input name="email" type="email" />
  &lt;/label>
  &lt;input name="action[subscribe]" type="submit" value="Submit" />
&lt;/form></code></pre>';

			$docs .= '<p>By default, this will subscribe the user to the List entered in Symphony\'s preferences. If you
				wish to override this, you can by passing a new list ID in your form:</p>
			    <pre class="XML"><code>&lt;input name="list" type="hidden" value="your-list-id" /></code></pre>';

			$docs .= '<p>If you have additional information about your subscriber that you wish add, use merge fields:</p>
			    <pre class="XML"><code>&lt;input name="merge[FNAME]" type="input" value="Mark" />
&lt;input name="merge[LNAME]" type="input" value="Lewis" /></code></pre>';

			$docs .= '<p>Additionally, you can set any of the following options with hidden fields. If these fields are omitted the following are defaults:</p>
			    <pre class="XML"><code>&lt;input name="email_type" type="input" value="html" />
&lt;input name="double_optin" type="input" value="yes" />
&lt;input name="update_existing" type="input" value="no" />
&lt;input name="replace_interests" type="input" value="yes" />
&lt;input name="send_welcome" type="input" value="no" /></code></pre>';

			$docs .= '<p>Here\'s an example of some of the above options. A form that will update if the subscriber exists and sets their mail preference to mobile:</p>
		        <pre class="XML"><code>&lt;form method="post" enctype="multipart/form-data">
		  &lt;label>Email
		    &lt;input name="email" type="email" />
		  &lt;/label>
		  &lt;label>First Name
		    &lt;input name="merge[FNAME]" type="text" />
		  &lt;/label>
		  &lt;label>Last Name
		    &lt;input name="merge[LNAME]" type="email" />
		  &lt;/label>
		  &lt;input name="update_existing" type="input" value="yes" />
		  &lt;input name="email_type" type="input" value="mobile" />
		  &lt;input name="action[subscribe]" type="submit" value="Submit" />
&lt;/form></code></pre>';

			return $docs;
		}

		protected function __trigger()
		{
			$api = new MailChimp($this->_driver->getKey());
			$result = new XMLElement("mailchimp");

			$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
			$list = (isset($_POST['list'])) ? $_POST['list'] : $this->_driver->getList();

			// For post values
			$fields = $_POST;
			unset($fields['action']);

			// Valid email?
			if(!$email)
			{
				$error = new XMLElement('error', 'E-mail is invalid.');
				$error->setAttribute("handle", 'email');

				$result->appendChild($error);
				$result->setAttribute("result", "error");

				return $result;
			}

			// Default subscribe parameters
			$params = array(
				'email' => array(
					'email' => $email
				),
				'id' => $list,
				'merge_vars' => array(),
				'email_type' => ($fields['email_type']) ? $fields['email_type'] : 'html',
				'double_optin' => ($fields['double_optin']) ? $fields['double_optin'] == 'yes' : true,
				'update_existing' => ($fields['update_existing']) ? $fields['update_existing'] == 'yes' : false,
				'replace_interests' => ($fields['replace_interests']) ? $fields['replace_interests'] == 'yes' : true,
				'send_welcome' => ($fields['send_welcome']) ? $fields['send_welcome'] == 'yes' : false
			);

			// Are we merging?
			$mergeVars = $api->call('lists/merge-vars', array(
				'id' => array(
					$list
				)
			));
			$mergeVars = ($mergeVars['success_count']) 
				? $mergeVars['data'][0]['merge_vars']
				: array();

			if(count($mergeVars) > 1 && isset($fields['merge'])) {
				$merge = $fields['merge'];
				foreach($merge as $key => $val)
				{
					if(!empty($val)) {
						$params['merge_vars'][$key] = $val;
					}
					else {
						unset($fields['merge'][$key]);
					}
				}
			}

			// Subscribe the user
			$api_result = $api->call('lists/subscribe', $params);
			if($api_result['status'] == 'error') {
				$result->setAttribute("result", "error");

				// try to match mergeVars with error
				if(count($mergeVars) > 1) {
					// replace
					foreach($mergeVars as $var) {
						$errorMessage = str_replace($var['tag'], $var['name'], $api_result['error'], $count);
						if($count == 1) {
							$error = new XMLElement("message", $errorMessage);
							break;
						}
					}
				}

				// no error message found with merge vars in it
				if ($error == null) {
					$msg = General::sanitize($api_result['error']);
					$error = new XMLElement("message", strlen($msg) > 0 ? $msg : 'Unknown error', array(
						'code' => $api_result['code'],
						'name' => $api_result['name']
					));
				}

				$result->appendChild($error);
			}
			else if(isset($_REQUEST['redirect'])) {
				redirect($_REQUEST['redirect']);
			}
			else {
				$result->setAttribute("result", "success");
				$result->appendChild(
					new XMLElement('message', __('Subscriber added to list successfully'))
				);
			}

			// Set the post values
			$post_values = new XMLElement("post-values");
			General::array_to_xml($post_values, $fields);
			$result->appendChild($post_values);

			return $result;
		}
	}

?>