<?php

if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

class extension_mailchimp extends Extension{

    public function uninstall()
    {
        Symphony::Configuration()->remove('mailchimp');
        Symphony::Configuration()->write();
    }

    public function getSubscribedDelegates()
    {
        return array(
            array(
                'page'      => '/system/preferences/',
                'delegate'  => 'AddCustomPreferenceFieldsets',
                'callback'  => 'addCustomPreferenceFieldsets'
            )
        );
    }

    /*-------------------------------------------------------------------------
        Utilities:
    -------------------------------------------------------------------------*/

    public function getKey()
    {
        return Symphony::Configuration()->get('key', 'mailchimp');
    }

    public function getList()
    {
        return Symphony::Configuration()->get('list', 'mailchimp');
    }

    /*-------------------------------------------------------------------------
        Delegates:
    -------------------------------------------------------------------------*/

    public function addCustomPreferenceFieldsets($context)
    {
        $fieldset = new XMLElement('fieldset');
        $fieldset->setAttribute('class', 'settings');
        $fieldset->appendChild(
            new XMLElement('legend', 'Mailchimp')
        );

        $group = new XMLElement('div');
        $group->setAttribute('class', 'group');

        $api = Widget::Label('API Key');
        $api->appendChild(Widget::Input(
            'settings[mailchimp][key]', General::Sanitize($this->getKey())
        ));
        $api->appendChild(
            new XMLElement('p', Widget::Anchor(__('Generate your API Key'), 'http://kb.mailchimp.com/article/where-can-i-find-my-api-key'), array(
                'class' => 'help'
            ))
        );

        $group->appendChild($api);

        $list = Widget::Label('Default List ID');
        $list->appendChild(Widget::Input(
            'settings[mailchimp][list]', General::Sanitize($this->getList())
        ));
        $list->appendChild(
            new XMLElement('p', __('Can be overidden from the frontend'), array(
                'class' => 'help'
            ))
        );
        $group->appendChild($list);

        $fieldset->appendChild($group);

        $context['wrapper']->appendChild($fieldset);
    }
}