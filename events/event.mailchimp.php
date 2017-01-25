<?php

if(!defined('__IN_SYMPHONY__')) die('<h2>Error</h2><p>You cannot directly access this file</p>');

include_once(EXTENSIONS . '/mailchimp/vendor/autoload.php');

use \DrewM\MailChimp\MailChimp;

class eventMailchimp extends Event
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
            'version' => '3.0.0',
            'release-date' => '2017-01-18',
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
            <pre class="XML"><code>&lt;input name="status" type="input" value="pending" /></code></pre>';
        
        $docs .= '<p>For possible values of status, check mailchimp doc here: <a href="https://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/">https://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/</a></p>';
        
        $docs .= '<p>Here\'s an example of some of the above options. A form that will add or update the subscriber without double opt in:</p>
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
  &lt;input name="status" type="input" value="subscribed" />
  &lt;input name="action[subscribe]" type="submit" value="Submit" />
&lt;/form></code></pre>';

        return $docs;
    }

    protected function __trigger()
    {
        $api = new MailChimp($this->_driver->getKey());
        $result = new XMLElement("mailchimp");

        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $lists = (isset($_POST['list'])) ? $_POST['list'] : $this->_driver->getList();

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

        $explodedLists = explode(',', $lists);

        foreach ($explodedLists as $list) {

            // Default subscribe parameters
            $custom_status = $fields['status'];
            $params = array(
                'email_address' => $email,
                //status = pending enables double opt in. Set to subscribed for no double opt in
                'status' => ($custom_status) ? $custom_status : 'pending'
            );
            
            try {
                if (is_array($fields['merge'])) {
                    $params['merge_fields'] = $fields['merge'];
                }
                
                // check if user already exists
                $hash_email = $api->subscriberHash($email);
                $check_result = $api->get("lists/$list/members/$hash_email");
                $is_already_subscribed = isset($check_result['id']);
                
                //if status is default value and subscriber already in list, status must not be changed
                if ($is_already_subscribed && !$custom_status && isset($check_result['status'])) {
                    $params['status'] = $check_result['status'];
                }
                
                // Subscribe or update the user
                $api_result = $api->put("lists/$list/members/$hash_email", $params);
                
                if ($is_already_subscribed) {
                    $result->setAttribute("result", "error");

                    $error = new XMLElement("message", "Email address already in list.");
                    $result->appendChild($error);
                    $error = new XMLElement("code", "409");
                    $result->appendChild($error);
                } else if(General::intval($api_result['status']) > -1) {
                    $result->setAttribute("result", "error");

                    // no error message found with merge vars in it
                    if ($error == null) {
                        $msg = General::sanitize($api_result['detail']);
                        $error = new XMLElement("message", strlen($msg) > 0 ? $msg : 'Unknown error', array(
                            'code' => $api_result['code'],
                            'name' => $api_result['name']
                        ));
                    }

                    $result->appendChild($error);
                } else if(isset($_REQUEST['redirect'])) {
                    redirect($_REQUEST['redirect']);
                } else {
                    $result->setAttribute("result", "success");
                    $result->appendChild(
                        new XMLElement('message', __('Subscriber added to list successfully'))
                    );
                }

                // Set the post values
                $post_values = new XMLElement("post-values");
                General::array_to_xml($post_values, $fields);
                $result->appendChild($post_values);
            }
            catch (Exception $ex) {
                $error = new XMLElement('error', General::wrapInCDATA($ex->getMessage()));
                $result->appendChild($error);
            }
        }
        return $result;
    }
}
