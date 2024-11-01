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

/**
 * Get plugin version
 *
 * @return mixed
 */
function vbsso_get_plugin_version() {

    if (!function_exists('get_plugins')) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    $plugin_folder = get_plugins('/' . plugin_basename(dirname(__FILE__) . '/../'));
    $plugin_file = VBSSO_PRODUCT_ID . '.php';

    return $plugin_folder[$plugin_file]['Version'];
}

if (strcmp($wp_version, '3.0.6') <= 0) {
    include_once(ABSPATH . 'wp-includes/registration.php');
}

/**
 * Report error
 *
 * @param Exception $error error message
 *
 * @return array
 */
function vbsso_listener_report_error($error) {
    $code = !is_string($error) ? $error->get_error_code() : '';
    $message = !is_string($error) ? $error->get_error_message($code) : $error;
    $data = !is_string($error) ? $error->get_error_data($code) : '';

    return array(SHAREDAPI_EVENT_FIELD_ERROR_CODE => $code, SHAREDAPI_EVENT_FIELD_ERROR_MESSAGE => $message,
                 SHAREDAPI_EVENT_FIELD_ERROR_DATA => $data);
}

/**
 * Load user from json data
 *
 * @param string $json        json data
 * @param bool   $create_user flag to create user
 *
 * @return mixed
 */
function vbsso_listener_user_load($json, $create_user = FALSE) {
    $vbsso_username = html_entity_decode($json[SHAREDAPI_EVENT_FIELD_USERNAME], NULL, get_option('blog_charset'));

    $user_by_email = get_user_by('email', $json[SHAREDAPI_EVENT_FIELD_EMAIL]);
    $user_by_login = get_user_by('login', $vbsso_username);

    // WP 3.0.x support
    $user_by_email = ($user_by_email instanceof WP_User)
        ? $user_by_email
        : new WP_User((is_object($user_by_email)
        && isset($user_by_email->ID) ? $user_by_email->ID : 0));
    $user_by_login = ($user_by_login instanceof WP_User)
        ? $user_by_login
        : new WP_User((is_object($user_by_login)
        && isset($user_by_login->ID) ? $user_by_login->ID : 0));

    if (!$user_by_email->ID && !$user_by_login->ID && $create_user
        && (get_site_option('users_can_register') || get_site_option('vbsso_ignore_membership', 1))) {
        $user_id = wp_create_user($vbsso_username, '', $json[SHAREDAPI_EVENT_FIELD_EMAIL]);

        if (!is_wp_error($user_id)) {
            $user = new WP_User($user_id);

            //Roles managing
            $new_roles = explode(',', $json[SHAREDAPI_EVENT_FIELD_USERGROUPS]);
            if ($vbsso_usergroups_assoc =
                json_decode(get_site_option(VBSSO_NAMED_EVENT_FIELD_USERGROUPS_ASSOC, NULL))) {
                foreach ($user->roles as $role) {
                    $user->remove_role($role);
                }
                foreach ($new_roles as $new_role) {
                    $user->add_role($vbsso_usergroups_assoc->$new_role);
                }
            }

            $data = array('user_login' => $vbsso_username, 'user_nicename' => $vbsso_username,);
            $data = stripslashes_deep($data);

            global $wpdb;
            $res = $wpdb->update($wpdb->users, $data, array('ID' => $user->ID));
            if ($res === FALSE) {
                $user =
                    vbsso_listener_report_error('Unable to update user login' . $json[SHAREDAPI_EVENT_FIELD_USERNAME]);
            }

            return $user;
        } else {
            return vbsso_listener_report_error($user_id);
        }
    }

    return ($user_by_email) ? $user_by_email
        : vbsso_listener_report_error('Unable to load user: ' . $json[SHAREDAPI_EVENT_FIELD_EMAIL]);
}

/**
 * Verify listener
 *
 * @param  string $json json params
 *
 * @return array
 */
function vbsso_listener_verify($json) {
    $supported = vbsso_get_supported_api_properties();
    foreach ($supported as $key => $item) {
        if (get_site_option($key) != $json[$item['field']]) {
            update_site_option($key, $json[$item['field']]);
        }
    }

    return array('data' => array(SHAREDAPI_EVENT_FIELD_VERIFY => TRUE));
}

/**
 * Auth user
 *
 * @param string $json settings
 *
 * @return array
 */
function vbsso_listener_authentication($json) {
    $user = wp_get_current_user(); // object exists for both guest and authenticated user always.

    // If current user is logged in and authentication event came from same user, we don't need to auth him again.
    $is_event_from_current_user = (is_user_logged_in() and $user instanceof WP_User and $user->data->user_email
        == $json[SHAREDAPI_EVENT_FIELD_EMAIL]) ? TRUE : FALSE;

    if (!$is_event_from_current_user) {
        $u = vbsso_listener_user_load($json, TRUE);
        if (!sharedapi_is_error_data_item($u)) {
            if ($user->ID != $u->ID) {
                vbsso_listener_logout($json);

                setcookie(VBSSO_NAMED_EVENT_FIELD_TIMEOUT, $json[SHAREDAPI_EVENT_FIELD_TIMEOUT], 0, SITECOOKIEPATH,
                    SITECOOKIEPATH);
                setcookie(VBSSO_NAMED_EVENT_FIELD_MUID, $json[SHAREDAPI_EVENT_FIELD_USERID], 0, SITECOOKIEPATH,
                    COOKIE_DOMAIN);

                wp_set_current_user($u->ID);
                wp_set_auth_cookie($u->ID,
                    isset($json[SHAREDAPI_EVENT_FIELD_REMEMBERME]) && $json[SHAREDAPI_EVENT_FIELD_REMEMBERME]);
                do_action(VBSSO_NAMED_EVENT_FIELD_MUID, $u->data->user_login);
            }
        } else {
            return array('error' => $u);
        }
    }
}

/**
 * Logout
 *
 * @param string $json json
 *
 * @return void
 */
function vbsso_listener_logout($json) {
    if (is_user_logged_in()) {
        wp_logout();
    }
}

/**
 * Register listener
 *
 * @param string $json json
 *
 * @return array
 */
function vbsso_listener_register($json) {
    $u = vbsso_listener_user_load($json, TRUE);

    if (sharedapi_is_error_data_item($u)) {
        return array('error' => $u);
    }
}

/**
 * User credentials
 *
 * @param string $json json
 *
 * @return mixed
 */
function vbsso_listener_credentials($json) {
    $u = vbsso_listener_user_load($json);

    if (sharedapi_is_error_data_item($u)) {
        return array('error' => $u);
    }

    $update = isset($json[SHAREDAPI_EVENT_FIELD_USERGROUPS2]) ? TRUE : FALSE;

    if (isset($json[SHAREDAPI_EVENT_FIELD_EMAIL2])) {
        $u->data->user_email = $json[SHAREDAPI_EVENT_FIELD_EMAIL2];
        $update = TRUE;
    }

    if (isset($json[SHAREDAPI_EVENT_FIELD_USERNAME2])) {
        $u->data->user_nicename = $u->data->user_login =
            html_entity_decode($json[SHAREDAPI_EVENT_FIELD_USERNAME2], NULL, get_option('blog_charset'));
        $update = TRUE;
    }

    if ($update) {
        if (isset($json[SHAREDAPI_EVENT_FIELD_USERGROUPS2]) and
            $vbsso_usergroups_assoc = json_decode(get_site_option(VBSSO_NAMED_EVENT_FIELD_USERGROUPS_ASSOC, NULL))) {
            //Roles managing
            $new_roles = explode(',', $json[SHAREDAPI_EVENT_FIELD_USERGROUPS2]);
            if ($vbsso_usergroups_assoc =
                json_decode(get_site_option(VBSSO_NAMED_EVENT_FIELD_USERGROUPS_ASSOC, NULL))) {
                vbsso_change_listener_roles($u, $new_roles, $vbsso_usergroups_assoc);
            }
        }

        if (is_wp_error($user_id = wp_insert_user(get_object_vars($u->data)))) {
            return array('error' => vbsso_listener_report_error($user_id));
        }

        $u = new WP_User($user_id);
        if ($u->data->user_login != $u->data->user_nicename) {
            $data = array('user_login' => $u->data->user_nicename);
            $data = stripslashes_deep($data);
            global $wpdb;
            $res = $wpdb->update($wpdb->users, $data, array('ID' => $u->ID));
            if ($res === FALSE) {
                return array('error' => vbsso_listener_report_error('Unable to update user login: '
                    . $u->data->user_login));
            }
        }
    }
}

/**
 * Change user roles
 *
 * @param object $user                 current user
 * @param string $newRoles             new user roles
 * @param object $vbssoUsergroupsAssoc vbsso assoc
 *
 * @return  void
 */
function vbsso_change_listener_roles(&$user, $newRoles, $vbssoUsergroupsAssoc) {
    foreach ($user->roles as $role) {
        $user->remove_role($role);
    }
    foreach ($newRoles as $new_role) {
        $user->add_role($vbssoUsergroupsAssoc->$new_role);
    }
}

sharedapi_data_handler(SHAREDAPI_PLATFORM_WORDPRESS, $wp_version, vbsso_get_plugin_version(),
    get_site_option(VBSSO_NAMED_EVENT_FIELD_API_KEY, SHAREDAPI_DEFAULT_API_KEY),
    array(SHAREDAPI_EVENT_VERIFY => 'vbsso_listener_verify', SHAREDAPI_EVENT_LOGIN => 'vbsso_listener_register',
          SHAREDAPI_EVENT_AUTHENTICATION => 'vbsso_listener_authentication',
          SHAREDAPI_EVENT_LOGOUT => 'vbsso_listener_logout', SHAREDAPI_EVENT_REGISTER => 'vbsso_listener_register',
          SHAREDAPI_EVENT_CREDENTIALS => 'vbsso_listener_credentials',
          SHAREDAPI_EVENT_CONFLICT_USERS => 'vbsso_listener_conflict_users'));
