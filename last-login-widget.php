<?php
/*
Plugin Name: Last Login Widget
Description: Adds a dashboard widget showing the current user's name and last login time
Version: 1.2.0
Author: Alexis Roques
*/

function llw_update_last_login($login, $user) {
    update_user_meta($user->ID, 'last_login', current_time('mysql'));
}
add_action('wp_login', 'llw_update_last_login', 10, 2);

function llw_dashboard_widget_content() {
    echo '<img src="https://media.tenor.com/w1Ua1frNOE0AAAAC/jake-the-dog-jake.gif" alt="Animated GIF" style="width: 100%;">';

    $current_user = wp_get_current_user();
    $last_login = get_user_meta($current_user->ID, 'last_login', true);
    
    echo "<p>Welcome, {$current_user->display_name}!</p>";
    
    if ($last_login) {
        echo "<p>Your last login was at: {$last_login}</p>";
    } else {
        echo "<p>This is your first login!</p>";
    }
}

function llw_add_dashboard_widgets() {
    wp_add_dashboard_widget('last_login_widget', 'Last Login', 'llw_dashboard_widget_content');
}
add_action('wp_dashboard_setup', 'llw_add_dashboard_widgets');

function my_plugin_check_for_updates($transient) {
    if (empty($transient->checked)) {
        return $transient;
    }

    $response = wp_remote_get('https://api.github.com/repos/AlexisRqs/last-login-widget/releases');

    error_log(print_r($response, true));

    if (is_wp_error($response)) {
        return $transient;
    }

    $releases = json_decode(wp_remote_retrieve_body($response));
    $latest_release = $releases[0];

    if (version_compare('v1.1.0', $latest_release->tag_name, '<')) {
        $obj = new stdClass();
        $obj->slug = 'last-login-widget';
        $obj->new_version = $latest_release->tag_name;
        $obj->url = $latest_release->html_url;
        $obj->package = $latest_release->zipball_url;
        $transient->response['last-login-widget/last-login-widget.php'] = $obj;
    }

    error_log(print_r($transient, true));

    return $transient;
}
add_filter('pre_set_site_transient_update_plugins', 'my_plugin_check_for_updates');

function enable_auto_update_for_my_plugin ( $plugins ) {
    $plugins[] = 'last-login-widget/last-login-widget.php';
    return $plugins;
}
add_filter( 'auto_update_plugin', 'enable_auto_update_for_my_plugin' );
