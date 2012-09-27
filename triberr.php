<?php
/* 
Plugin Name: Triberr
Plugin URI: http://triberr.com/subdomains/plugins/wordpress/
Description: Instantly send posts from your blog from Triberr.
Version: 3.0.1
Author: Triberr
Author URI: http://Triberr.com/
License: GPL2
*/
$GLOBALS['version_number'] = "3.0.0";
require_once('triberr_includes/class-admin-functionality.php');

// Include calls for xml-rpc
require_once('triberr_includes/class-triberr-xmlrpc-server.php');
require_once('triberr_includes/class-content-system.php');
require_once('triberr_includes/class-comment-system.php');
require_once('triberr_includes/class-api.php');

// Include stylesheets and javascripts  
add_action('admin_head', 'triberr_admin_register_head');

// Listen for RPC's
add_action('admin_footer', 'triberr_enable_remote'); 
add_action ('publish_post', 'triberr_submit_post');
add_action ('publish_future_post', 'triberr_submit_post');
add_action ('Triberr.getSourceID', 'triberr_update_meta');
add_action ('admin_footer', 'triberr_display_message');

// Check of the plugin has been configured, if not show message
add_action ('admin_notices','triberr_admin_setup_notices');

// Create the sidebar link
add_action('admin_menu', 'triberr_menu'); 

// Add endorsement capabilties
add_filter( 'the_content', array('triberr_endorse_post', 'triberr_endorse_post_function'));

// print out the commenting system
add_filter('comments_template', 'triberr_comment_template',9);

// Hook into the 'wp_dashboard_setup' action to register our other functions
add_action('wp_dashboard_setup', 'triberr_add_dashboard_widgets' ); // Hint: For Multisite Network Admin Dashboard use wp_network_dashboard_setup instead of wp_dashboard_setup.
?>
