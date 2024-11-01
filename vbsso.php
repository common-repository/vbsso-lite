<?php
/*
Plugin Name: vBSSO Connect
Plugin URI: http://www.vbsso.com/platforms/wordpress
Description: Provides universal Secure Single Sign-On between vBulletin and different popular platforms like WordPress.
Author: www.vbsso.com
Version: 1.4.3
Author URI: http://www.vbsso.com
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
Network: true
Compatibility: 3.x
*/

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

require_once(dirname(__FILE__) . '/vendor/autoload.php');
require_once(dirname(__FILE__) . '/config.php');


$vbssoPlugins = \Foolz\Plugin\Loader::forge()->addDir(dirname(__FILE__) . '/plugins')->getAll();

if (!defined('ABSPATH') && !isset($vbssoPlugins['vbsso/OldEndpoint'])) {
    exit();
}

if (!defined('ABSPATH') && isset($vbssoPlugins['vbsso/OldEndpoint'])) {
    $oldEndpoint = $vbssoPlugins['vbsso/OldEndpoint'];
    $oldEndpoint->execute();
}

define('VBSSO_WORDPRESS_PLUGIN_URL',
    WP_PLUGIN_URL . '/' . str_replace(basename(__FILE__), "", plugin_basename(__FILE__)));

/* Load languages */
add_action('plugins_loaded', 'vbsso_plugin_init');

/**
 * Initialisation function
 * Connect languages to plugin
 *
 * @return void
 */
function vbsso_plugin_init() {
    load_plugin_textdomain('vbsso', FALSE, dirname(plugin_basename(__FILE__)) . '/languages/');
}


/**
 * Register Activation Hook
 */
register_activation_hook(__FILE__, 'vbsso_register_activation_hook');

/**
 * Plugin activation hook
 *
 * @return void
 */
function vbsso_register_activation_hook() {
    $nloaded_extensions = vbsso_verify_loaded_extensions();

    if (count($nloaded_extensions)) {
        wp_die('Please install these PHP extensions `' . join(', ', $nloaded_extensions)
            . '` before installing or upgrading this product!');
    }

    vbsso_add_endpoint();
    flush_rewrite_rules();
}

register_uninstall_hook(__FILE__, 'vbsso_register_uninstall_hook');
/**
 * Uninstall hook
 *
 * @return void
 */
function vbsso_register_uninstall_hook() {
    flush_rewrite_rules();
}

/**
 * Admin Menu Filter
 */
if (is_admin()) {
    add_action('admin_menu', 'vbsso_admin_menu_hook');
}
/**
 * Add admin settings page
 *
 * @return void
 */
function vbsso_admin_menu_hook() {
    global $wpdb;

    if (in_array($wpdb->blogid, array(0, 1))) {
        add_options_page(VBSSO_PRODUCT_NAME . ' Options', VBSSO_PRODUCT_NAME, 'manage_options', 'vbsso_options',
            'vbsso_options');
    }
}

/**
 * Profile Personal Options Filter
 */
if (defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE) {
    add_action('profile_personal_options', 'vbsso_profile_personal_options_hook');
}

/**
 * Add css to profile page
 *
 * @return void
 */
function vbsso_profile_personal_options_hook() {
    global $current_user;
    if (!in_array('administrator', $current_user->roles)) {
        echo '<link rel="stylesheet" type="text/css" href="' . VBSSO_WORDPRESS_PLUGIN_URL
            . 'assets/css/profile-overrides.css">';
    }
}

/**
 * Get data from master platform
 *
 * @param string $link   url
 * @param mixed  $fields field from master
 *
 * @return array|mixed
 */
function vbsso_get_vb_post_data($link, $fields = FALSE) {
    $baa_username = sharedapi_decode_data(get_site_option(VBSSO_NAMED_EVENT_FIELD_API_KEY, SHAREDAPI_DEFAULT_API_KEY),
        get_site_option(VBSSO_NAMED_EVENT_FIELD_BAA_USERNAME, NULL));

    $baa_password = sharedapi_decode_data(get_site_option(VBSSO_NAMED_EVENT_FIELD_API_KEY, SHAREDAPI_DEFAULT_API_KEY),
        get_site_option(VBSSO_NAMED_EVENT_FIELD_BAA_PASSWORD, NULL));

    $data = sharedapi_post($link, $fields, $baa_username, $baa_password);

    return ($data['error_string']) ? $data : json_decode($data['response']);
}

/**
 * Get user groups from vbulletin
 *
 * @return mixed
 */
function vbsso_get_vb_usergroups() {
    return vbsso_get_vb_post_data(get_site_option(VBSSO_NAMED_EVENT_FIELD_USERGROUPS_URL));
}

/**
 * Get vb user unread stats
 *
 * @return mixed
 */
function vbsso_get_vb_user_unread_stats() {
    global $current_user;
    $data = vbsso_get_vb_post_data(get_site_option(VBSSO_NAMED_EVENT_FIELD_USER_UNREAD_STATS_URL, '')
        . get_site_option(VBSSO_NAMED_EVENT_FIELD_LID, '') . '&id=' . md5(strtolower($current_user->data->user_email)));

    if (is_object($data) && isset($data->data)) {
        $data = sharedapi_accept_data(get_site_option(VBSSO_NAMED_EVENT_FIELD_API_KEY, SHAREDAPI_DEFAULT_API_KEY),
            $data->data);
    }

    return $data;
}

/**
 * Settings page
 *
 * @return void
 */
function vbsso_options() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    vbsso_options_submit();

    $sharedkey_name = VBSSO_NAMED_EVENT_FIELD_API_KEY;
    $sharedkey_value = get_site_option(VBSSO_NAMED_EVENT_FIELD_API_KEY, SHAREDAPI_DEFAULT_API_KEY);
    $sharedkey_title = __('Platform Shared Key', 'vbsso');
    $sharedkey_desc = __('Please unconnect this platform to change shared key', 'vbsso');

    $ignore_membership = get_site_option('vbsso_ignore_membership', 1) ? 'checked' : '';
    $ignore_membership_name = 'vbsso_ignore_membership';
    $ignore_membership_title = __('Ignore Membership (Anyone can register through vBSSO)', 'vbsso');

    $url = home_url() . '/vbsso/1.0';
    $url_title = __('Platform Address', 'vbsso');

    $disabled_field = (get_site_option(VBSSO_NAMED_EVENT_FIELD_LOGIN_URL, '')) ? 'disabled' : '';
    //**************************----------------------------*******************************************************************
    /* Languages */
    $lPlatform = __('Platform', 'vbsso');
    $lSettings = __('Settings', 'vbsso');
    $lGroups = __('User Groups Association (Default role is subscriber)', 'vbsso');
    $lSaveBtn = __('Save Changes', 'vbsso');

    $footer_link_name = VBSSO_PLATFORM_FOOTER_LINK_PROPERTY;
    $footer_link = get_site_option(VBSSO_PLATFORM_FOOTER_LINK_PROPERTY, VBSSO_PLATFORM_FOOTER_LINK_SHOW_EVERYWHERE);
    if ($footer_link == VBSSO_PLATFORM_FOOTER_LINK_SHOW_ADMIN) {
        $footer_link = update_option(VBSSO_PLATFORM_FOOTER_LINK_PROPERTY, VBSSO_PLATFORM_FOOTER_LINK_SHOW_EVERYWHERE);
    }
    $footerOptions = vbsso_get_platform_footer_link_options();
    $footerOptions[VBSSO_PLATFORM_FOOTER_LINK_SHOW_EVERYWHERE] = __('Show', 'vbsso');
    $footerOptions[VBSSO_PLATFORM_FOOTER_LINK_SHOW_NONE] =
        __('Don\'t show (I have already purchased branding Free license)', 'vbsso');
    $lFooter = __('Footer Link', 'vbsso');
    $footer_link_description = __(VBSSO_PLATFORM_FOOTER_LINK_DESCRIPTION_HTML, 'vbsso');

    $footerOpt = "";
    foreach ($footerOptions as $key => $title) {
        $checked = $key == $footer_link ? 'checked' : '';
        $footerOpt .= '<label for="footer_link_option_' . $key . '"><input type="radio" id="footer_link_option_' . $key
            . '" name="' . $footer_link_name . '" value="' . $key . '" ' . $checked . '> ' . $title . '</label><br/>';
    }

    $nonce = wp_nonce_field("vbsso_save_settings");

    $activeEndpoint = get_site_option('vbsso_active_endpoint');
    if ($activeEndpoint && $activeEndpoint != '1.0') {
        vbsso_show_wp_message('error', 'Please, reconnect this platform to a new url!',
            'Since plugin version 1.4.0 we changed connection mechanism for better plugin security and compatibility.');
    }

    if (!get_option('permalink_structure')) {
        vbsso_show_wp_message('error',
            'vBSSO warning: for proper vBSSO activity be sure that pretty permalinks URL structure was enabled:' .
            ' navigate to Settings > Permalink Settings > Common Settings > choose any URL structure except plain' .
            ' or default > Save.');
    }

    echo "
<form name='form1' method='post' action=''>
    <input type='hidden' name='submit_options' value='1'>
    
    <h4>{$lFooter}</h4>
    {$footer_link_description}
    <p>$footerOpt</p>
    <h4>$lPlatform</h4>
    <p>
        <input size='80' name='{$sharedkey_name}' value='{$sharedkey_value}' {$disabled_field} />
        <br/><span class='description'>{$sharedkey_title}</span>
        <br/><span class='description'>{$sharedkey_desc}</span><br/><br/>
                
        <input size='80' value='{$url}' readonly />
        <br/><span class='description'>{$url_title}</span><br>
    </p>
    <h4>$lSettings</h4>  
        <p>
            <input type='checkbox' id='{$ignore_membership_name}' name='{$ignore_membership_name}'" .
        " value='1' {$ignore_membership} />
            <label for='{$ignore_membership_name}'>{$ignore_membership_title}</label> <br/>
        </p>       
";
    try {
        $settings =
            \Foolz\Plugin\Loader::forge()->addDir(dirname(__FILE__) . '/plugins')->get('vbsso/Settings')->execute();
    } catch (OutOfBoundsException $error) {
        //Plugin settings not found, Nothing to do here
        $settings = NULL;
    }

    echo "<h4>$lGroups</h4><p>";
    (get_site_option(VBSSO_NAMED_EVENT_FIELD_USERGROUPS_URL, '')) ? vbsso_display_groups()
        : _e("This platform is not connected.", 'vbsso');
    echo "
</p>
    <p>
        $nonce
    </p>        
    <p class='submit'>
        <input type='submit' name='Submit' class='button-primary' value='$lSaveBtn' />
    </p>
</form>
";
}

/**
 * Form submit action
 *
 * @return void
 */
function vbsso_options_submit() {
    if (isset($_POST['submit_options']) && $_POST['submit_options'] == 1
        && check_admin_referer('vbsso_save_settings')) {
        if (isset($_POST[VBSSO_NAMED_EVENT_FIELD_API_KEY])) {
            if (!empty($_POST[VBSSO_NAMED_EVENT_FIELD_API_KEY])) {
                update_site_option(VBSSO_NAMED_EVENT_FIELD_API_KEY,
                    sanitize_text_field($_POST[VBSSO_NAMED_EVENT_FIELD_API_KEY]));
            } else {
                echo '<div class="error"><p>' . __('Platform Shared Key can not be empty', 'vbsso') . '</p></div>';
            }
        }
        update_site_option(VBSSO_NAMED_EVENT_FIELD_FETCH_AVATARS,
            isset($_POST[VBSSO_NAMED_EVENT_FIELD_FETCH_AVATARS]) ? '1' : '0');
        update_site_option(VBSSO_NAMED_EVENT_FIELD_EDIT_PROFILE_IN_VBULLETIN,
            isset($_POST[VBSSO_NAMED_EVENT_FIELD_EDIT_PROFILE_IN_VBULLETIN]) ? '1' : '0');
        update_site_option(VBSSO_NAMED_EVENT_FIELD_SHOW_VBULLETIN_AUTHOR_PROFILE,
            isset($_POST[VBSSO_NAMED_EVENT_FIELD_SHOW_VBULLETIN_AUTHOR_PROFILE]) ? '1' : '0');
        update_site_option('vbsso_ignore_membership', isset($_POST['vbsso_ignore_membership']) ? '1' : '0');
        update_site_option(VBSSO_PLATFORM_FOOTER_LINK_PROPERTY,
            sanitize_text_field($_POST[VBSSO_PLATFORM_FOOTER_LINK_PROPERTY]));

        if (get_site_option(VBSSO_NAMED_EVENT_FIELD_USERGROUPS_URL, '')) {
            $vb_usergroups = vbsso_get_vb_usergroups();
            $vbsso_usergroups_assoc = array();

            foreach ($vb_usergroups as $vb_usergroup) {
                $vbsso_usergroups_assoc[$vb_usergroup->usergroupid] =
                    $_POST[VBSSO_NAMED_EVENT_FIELD_USERGROUPS_ASSOC . '_' . $vb_usergroup->usergroupid];
            }

            update_site_option(VBSSO_NAMED_EVENT_FIELD_USERGROUPS_ASSOC, json_encode($vbsso_usergroups_assoc));
        }
    }
}

/**
 * Display groups on settings page
 *
 * @return void print groups
 */
function vbsso_display_groups() {

    $vb_usergroups = (array)vbsso_get_vb_usergroups();

    if (isset($vb_usergroups['error_string'])) {
        _e($vb_usergroups['error_string']);

        return;
    }

    $vbsso_usergroups_assoc = json_decode(get_site_option(VBSSO_NAMED_EVENT_FIELD_USERGROUPS_ASSOC, NULL));
    $lUserGroups = __('vBulletin Usergroups', 'vbsso');
    $lWordpressRoles = __('Wordpress Roles', 'vbsso');

    echo '<table id="vbsso_usergroups">';
    echo "<tr style='text-align: center'><td>$lUserGroups</td><td>$lWordpressRoles</td></tr>";
    foreach ($vb_usergroups as $vb_usergroup) {
        $ugid = $vb_usergroup->usergroupid;
        echo '<tr><td>' . $vb_usergroup->title . '</td><td><select name="' . VBSSO_NAMED_EVENT_FIELD_USERGROUPS_ASSOC
            . '_' . $vb_usergroup->usergroupid . '">';
        wp_dropdown_roles(($vbsso_usergroups_assoc and $vbsso_usergroups_assoc->$ugid) ? $vbsso_usergroups_assoc->$ugid
            : 'subscriber');
        echo '</select></td></tr>';
    }
    echo '</table>';
}

/**
 * Adds vBSSO Login Form Widget.
 */
class VbssoLoginForm extends WP_Widget {

    /**
     * VbssoLoginForm constructor.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct('vbsso_login_form', __('vBSSO Login Form', 'vbsso'),
            array('description' => __('vBSSO Login Form Widget', 'vbsso'),));
        add_action('wp_enqueue_scripts', array($this, 'vbssoFrontendScripts'));
    }

    /**
     * Widget function
     *
     * @param mixed $args     arguments
     * @param mixed $instance Wordpress instance
     *
     * @return void
     */
    public function widget($args, $instance) {
        global $user_ID;

        $title = isset($instance['title']) ? $instance['title'] : __('vBSSO Login Form', 'vbsso');
        extract($args);

        $title = apply_filters('widget_title', $title);

        if (vbsso_get_platform_config_property(SHAREDAPI_PLATFORM_WORDPRESS, VBSSO_CONFIG_PROPERTY_OVERRIDE_LINKS, TRUE)
            && get_site_option(VBSSO_NAMED_EVENT_FIELD_LOGIN_VBULLETIN_URL, '') != '') {
            echo $args['before_widget'];

            if (!empty($title)) {
                echo $args['before_title'] . $title . $args['after_title'];
            }

            $metalinks = !is_user_logged_in() ? $this->userNotLogged() : $this->userLogged($user_ID);

            echo '<div id="vbsso_menu"><ul>' . $metalinks . '</ul></div>';

            echo $args['after_widget'];
        }
    }

    /**
     * Update title widget function
     *
     * @param mixed $new_instance new instance(updated)
     * @param mixed $old_instance old instance
     *
     * @return array
     *
     * @inheritDoc
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = strip_tags($new_instance['title']);

        return $instance;
    }

    /**
     * Widget Form initialise
     *
     * @param mixed $instance Wordpress instance
     *
     * @return void
     */
    public function form($instance) {

        $title = isset($instance['title']) ? $instance['title'] : __('vBSSO Login Form', 'vbsso');
        echo "
        <p>
            <label for='" . $this->get_field_id('title') . "'>" . _e('Title:') . "</label>
            <input class=\"widefat\" id='" . $this->get_field_id('title') . "'
                   name='" . $this->get_field_name('title') . "' type=\"text\"
                   value='" . esc_attr($title) . "'/>
        </p>
        ";
    }

    /**
     * Added plugin js and css to WP page header
     *
     * @return void
     */
    public function vbssoFrontendScripts() {
        wp_enqueue_script('vbsso_notifications', plugins_url('assets/js/notifications.js', __FILE__), array('jquery'));
        $path = str_replace(site_url(), '', plugins_url('/assets/images', __FILE__));
        wp_localize_script('vbsso_notifications', 'vbsso_vars',
            array('arrow_up_url' => "$path/arrow_up.png", 'arrow_down_url' => "$path/arrow_down.png",));
        wp_enqueue_style('vbsso_notifications', plugins_url('assets/css/notifications.css', __FILE__));
    }

    /**
     * Notification function
     *
     * @return string notification messages
     */
    private function buildNotifications() {
        global $current_user;

        $output = '';
        $vb_user_unread_stats = vbsso_get_vb_user_unread_stats();
        if (is_array($vb_user_unread_stats) and array_sum($vb_user_unread_stats) > 0) {
            $vb_stats_url = get_site_option(VBSSO_NAMED_EVENT_FIELD_STATS_URL, '');
            $output .= '<li id="vbsso_stats_notifications">' . __('Notifications: ')
                . array_sum((array)$vb_user_unread_stats) . '</li>';
            $output .= '<div id="vbsso_stats_holder"><table>';
            $output .= ($vb_user_unread_stats[SHAREDAPI_EVENT_FIELD_STAT_PM]) ? '<tr><td>'
                . $vb_user_unread_stats[SHAREDAPI_EVENT_FIELD_STAT_PM] . '</td><td class="vbsso_stat_message">
            <a href="' . $vb_stats_url . SHAREDAPI_EVENT_FIELD_STAT_PM
                . '" rel="nofollow">Unread Private Messages</a></td></tr>' : '';
            $output .= ($vb_user_unread_stats[SHAREDAPI_EVENT_FIELD_STAT_VM]) ? '<tr><td>'
                . $vb_user_unread_stats[SHAREDAPI_EVENT_FIELD_STAT_VM] . '</td><td class="vbsso_stat_message">
            <a href="' . $vb_stats_url . SHAREDAPI_EVENT_FIELD_STAT_VM . '&id='
                . md5(strtolower($current_user->data->user_email))
                . '" rel="nofollow">Unread Visitor Messages</a></td></tr>' : '';
            $output .= ($vb_user_unread_stats[SHAREDAPI_EVENT_FIELD_STAT_FR]) ? '<tr><td>'
                . $vb_user_unread_stats[SHAREDAPI_EVENT_FIELD_STAT_FR] . '</td><td class="vbsso_stat_message">
            <a href="' . $vb_stats_url . SHAREDAPI_EVENT_FIELD_STAT_FR
                . '" rel="nofollow">Incoming Friend Requests</a></td></tr>' : '';
            $output .= ($vb_user_unread_stats[SHAREDAPI_EVENT_FIELD_STAT_PC]) ? '<tr><td>'
                . $vb_user_unread_stats[SHAREDAPI_EVENT_FIELD_STAT_PC] . '</td><td class="vbsso_stat_message">
            <a href="' . $vb_stats_url . SHAREDAPI_EVENT_FIELD_STAT_PC
                . '" rel="nofollow">Unread Picture Comments</a></td></tr>' : '';
            $output .= '</table></div>';
        }

        return $output;
    }

    /**
     * Registration Form
     *
     * @param string $before html code before link
     * @param string $after  html code after link
     *
     * @return mixed
     */
    private function vbssoWpRegister($before, $after) {
        $str1 = $before . '<a href="' . esc_url(apply_filters('register_url',
                site_url('wp-login.php?action=register', 'login'))) . '">' . __('Register', 'vbsso') . '</a>' . $after;
        $str2 = $before . '<a href="' . admin_url() . '">' . __('Site Admin', 'vbsso') . '</a>' . $after;

        $link = !is_user_logged_in() ? $str1 : $str2;

        return apply_filters('register', $link);
    }

    /**
     * Widget form when user not logged
     *
     * @return string
     */
    private function userNotLogged() {
        echo '<form action="' . get_site_option(VBSSO_NAMED_EVENT_FIELD_LOGIN_VBULLETIN_URL, '') . '" method="post">
                        <input class="input" type="text" name="vb_login_username" id="vb_username"' .
            ' style="padding:3px; margin-bottom: 3px; display: block;" accesskey="u" placeholder="'
            . __('Username or email address', 'vbsso') . '" title="' . __('Username or email address') . '" />
                        <input class="input" type="password" name="vb_login_password" id="vb_password"' .
            ' style="padding:3px; margin-bottom: 3px; display: block;" placeholder="'
            . __('Password', 'vbsso') . '" title="' . __('Password') . '" />
                <input class="input" type="checkbox" name="cookieuser" value="1" id="vb_cookieuser" accesskey="c" />
                <label for="vb_cookieuser" style="display: inline-block;">' . __('Remember me', 'vbsso') . '</label>
                <input class="button-primary" type="submit" value="' . __('Login', 'vbsso') . '" accesskey="s" />

                <input type="hidden" name="do" value="login" />
                    </form>';

        $metalinks = $this->vbssoWpRegister('', '');
        $metalinks .= '<li><a href="' . wp_lostpassword_url() . '" rel="nofollow">' . __('Forgot your password?',
                'vbsso') . '</a></li>';

        return $metalinks;
    }

    /**
     * Widget form when user is logged
     *
     * @param integer $user_ID user id
     *
     * @return string
     */
    private function userLogged($user_ID) {
        echo '<ul><li style="list-style-type: none;">' . sprintf(__('Howdy, %1$s', 'vbsso'),
                wp_get_current_user()->display_name) . '!</li></ul><div id="vbsso_avatar">' . get_avatar($user_ID, '38')
            . '</div>';

        $metalinks = $this->buildNotifications();

        $metalinks .= '<li><a href="' . admin_url() . '" rel="nofollow">' . __('Site Admin', 'vbsso') . '</a></li>';
        $metalinks .= '<li><a href="' . site_url('wp-admin/profile.php') . '" rel="nofollow">' . __('Profile', 'vbsso')
            . '</a></li>';
        $metalinks .= '<li><a href=" ' . wp_logout_url() . '" rel="nofollow">' . __('Logout', 'vbsso') . '</a></li>';

        return $metalinks;
    }
}

add_action('widgets_init', function () {
    register_widget( "VbssoLoginForm" );
});

unset($vbssoPlugins['vbsso/Settings']);
unset($vbssoPlugins['vbsso/OldEndpoint']);

foreach ($vbssoPlugins as $name => $plugin) {
    $plugin->execute();
}

/**
 * Add the endpoint
 *
 * @return void
 */
function vbsso_add_endpoint() {
    add_rewrite_endpoint('vbsso', EP_ROOT);
}

add_action('init', 'vbsso_add_endpoint');

/**
 * Add endpoint
 *
 * @param object $query request
 *
 * @return void
 */
function vbsso_init_endpoint($query) {
    if ($query->is_main_query()) {
        $action = $query->get('vbsso');
        if ($action == '1.0') {
            vbsso_active_endpoint('1.0');
            vbsso_endpoint_controller();
        }
    }
}

add_action('pre_get_posts', 'vbsso_init_endpoint');

/**
 * Endpoint main controller
 *
 * @return void
 */
function vbsso_endpoint_controller() {
    global $wp_version;

    if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
        $previous_error_reporting = error_reporting(E_ALL);
        error_reporting($previous_error_reporting & ~E_DEPRECATED);
    }
    include_once(dirname(__FILE__) . '/includes/api.php');
}

/**
 * Show Wordpress Message
 *
 * @param string $type        div class
 * @param string $message     message
 * @param string $subMesssage sub message
 *
 * @return void
 */
function vbsso_show_wp_message($type, $message, $subMesssage = '') {
    echo "
            <div class='$type'>
                <p>
                    <strong>$message</strong>
                </p>
                <p>
                    $subMesssage                
                </p>
            </div>
            ";
}

/**
 * Active Endpoint Update option
 *
 * @param string $value value to save
 *
 * @return void
 */
function vbsso_active_endpoint($value) {
    if (!empty($_POST)) {
        update_site_option('vbsso_active_endpoint', $value);
    }
}
