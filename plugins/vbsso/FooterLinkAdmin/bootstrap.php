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


Event::forge('Foolz\Plugin\Plugin::execute.vbsso/FooterLinkAdmin')->setCall(function ($result) {
    /**
     * Admin Footer Filter
     */
    add_filter('admin_footer', 'vbsso_admin_footer_hook');

    /**
     * Hook for footer link(admin)
     *
     * @return void
     */
    function vbsso_admin_footer_hook() {
        if (in_array(get_site_option(VBSSO_PLATFORM_FOOTER_LINK_PROPERTY, VBSSO_PLATFORM_FOOTER_LINK_SHOW_ADMIN),
            array(VBSSO_PLATFORM_FOOTER_LINK_SHOW_EVERYWHERE, VBSSO_PLATFORM_FOOTER_LINK_SHOW_ADMIN))) {
            _e(VBSSO_PLATFORM_FOOTER_LINK_HTML, 'vbsso');
        }
    }
});
