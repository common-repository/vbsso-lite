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


Event::forge('Foolz\Plugin\Plugin::execute.vbsso/Login')->setCall(function ($result) {

    /**
     * Login Url Filter
     */
    if (vbsso_get_platform_config_property(SHAREDAPI_PLATFORM_WORDPRESS, VBSSO_CONFIG_PROPERTY_OVERRIDE_LINKS, TRUE)
        && get_site_option(VBSSO_NAMED_EVENT_FIELD_LOGIN_URL, '') != '') {
        add_filter('login_url', 'vbsso_login_url_hook');
    }

    /**
     * Login action
     *
     * @return string
     */
    function vbsso_login_url_hook() {
        return sharedapi_url_add_destination(get_site_option(VBSSO_NAMED_EVENT_FIELD_LOGIN_URL, ''), 'server', '',
            get_site_option(VBSSO_NAMED_EVENT_FIELD_LID, ''));
    }

    /**
     * Auth Cookie Expiration Filter.
     */
    add_filter('auth_cookie_expiration', 'vbsso_auth_cookie_expiration_hook', 10, 3);

    /**
     * User auth action
     *
     * @param integer $timeout  timeout
     * @param integer $user_id  user id
     * @param boolean $remember remember
     *
     * @return mixed
     */
    function vbsso_auth_cookie_expiration_hook($timeout, $user_id, $remember) {
        $vbsso_timeout = sharedapi_gpc_variable(VBSSO_NAMED_EVENT_FIELD_TIMEOUT, '', 'c');

        return !empty($vbsso_timeout) && $vbsso_timeout > 0 ? $vbsso_timeout : $timeout;
    }
});
