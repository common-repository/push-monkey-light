<?php
/**
* Push Monkey Lite Uninstall
*
* Uninstalling Push Monkey Lite options.
*/

// If uninstall not called from Wordpress exit 
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {

	exit();
}
// required core files
require_once( plugin_dir_path( __FILE__ ) . 'push-monkey-light.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/class_push_monkey_light_client.php' );

// Delete plugin options
delete_option( Push_Monkey_Light::ACCOUNT_KEY_KEY );
delete_option( Push_Monkey_Light::WEBSITE_NAME_KEY );
delete_option( Push_Monkey_Light::SUBDOMAIN_FORCED );
delete_option( Push_Monkey_Light::WEBSITE_PUSH_ID_KEY );
delete_option( Push_Monkey_Light::FLUSH_REWRITE_RULES_FLAG_KEY );
delete_option( Push_Monkey_Light_Client::PLAN_NAME_KEY );
