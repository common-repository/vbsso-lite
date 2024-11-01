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

require_once(dirname(__FILE__) . '/vendor/com.extremeidea.vbsso/vbsso-connect-shared/vbsso_shared.php');
if (file_exists(dirname(__FILE__) . '/config.custom.php')) {
    include_once(dirname(__FILE__) . '/config.custom.php');
}
