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


Event::forge('Foolz\Plugin\Plugin::execute.vbsso/Logout')->setCall(function ($result) {
    /**
     * Logout Url Filter
     */
    if (vbsso_get_platform_config_property(SHAREDAPI_PLATFORM_WORDPRESS, VBSSO_CONFIG_PROPERTY_OVERRIDE_LINKS, TRUE)
        && get_site_option(VBSSO_NAMED_EVENT_FIELD_LOGOUT_URL, '') != '') {
        add_filter('logout_url', 'vbsso_logout_url_hook');
    }

    /**
     * Logout action
     *
     * @return string
     */
    function vbsso_logout_url_hook() {
        return sharedapi_url_add_destination(get_site_option(VBSSO_NAMED_EVENT_FIELD_LOGOUT_URL, ''), 'server', '',
            get_site_option(VBSSO_NAMED_EVENT_FIELD_LID, ''));
    }

});
