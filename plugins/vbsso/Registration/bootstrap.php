<?php
/**
 * -----------------------------------------------------------------------
 * vBSSO is a solution which helps you connect to different software platforms
 * via secure Single Sign-On.
 *
 * Copyright (c) 2011-2017 vBSSO. All Rights Reserved.
 * This software is the proprietary information of vBSSO.
 *
 * Author URI: http://www.vbsso.com
 * License: GPL version 2 or later -
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -----------------------------------------------------------------------
 */

if (!defined('ABSPATH')) {
    exit;// Exit if accessed directly
}

use \Foolz\Plugin\Event;


Event::forge('Foolz\Plugin\Plugin::execute.vbsso/Registration')->setCall(function ($result) {
    /**
     * Register Url Filter
     */
    if (vbsso_get_platform_config_property(SHAREDAPI_PLATFORM_WORDPRESS, VBSSO_CONFIG_PROPERTY_OVERRIDE_LINKS, TRUE)
        && get_site_option(VBSSO_NAMED_EVENT_FIELD_REGISTER_URL, '') != '') {
        add_filter('register', 'vbsso_register_url_hook');
    }

    /**
     * Register filter hook for registration block
     *
     * @return string link
     */
    function vbsso_register_url_hook() {
        if (!is_user_logged_in()) {
            $tmplink =
                '<li><a href="' . sharedapi_url_add_destination(get_site_option(VBSSO_NAMED_EVENT_FIELD_REGISTER_URL,
                    ''), 'server', '', get_site_option(VBSSO_NAMED_EVENT_FIELD_LID, '')) . '" rel="nofollow">'
                . __('Create your account', 'vbsso') . '</a></li>';
            $link = get_option('users_can_register') || get_site_option('vbsso_ignore_membership', 1) ? $tmplink : '';

            return $link;
        }
        $link = '<li><a href="' . admin_url() . '" rel="nofollow">' . __('Site Admin') . '</a></li>';

        return $link;
    }

    /**
     * Mail Filter
     */
    add_filter('wp_mail', 'vbsso_disable_registration_email_filter_hook');

    /**
     * Disable registration email
     *
     * @param string $result email
     *
     * @return array|string
     */
    function vbsso_disable_registration_email_filter_hook($result = '') {
        extract($result);
        if (preg_match('/New .+ User/', $subject)) {
            $to = '';
            $subject = '';
            $message = '';
            $headers = '';
            $attachments = array();

            return compact('to', 'subject', 'message', 'headers', 'attachments');
        }

        return $result;
    }
});

