<?php
/**
 * Plugin Name: Push Monkey Light - Web Push Notifications
 * Plugin URI:
 * Author: Get Push Monkey Ltd.
 * Description: Engage & delight your readers with Desktop Push Notifications - a new subscription channel directly to the mobiles or desktops of your readers. To start, register on <a href="https://www.getpushmonkey.com?source=plugin_desc" target="_blank">getpushmonkey.com</a>. Currently this works for Chrome, Firefox and Safari on MacOS, Windows and Android.
 * Version: 1.0.0
 * Stable Tag: 1.0.0
 * Text Domain: push-monkey-light
 * Domain Path: /languages
 * Author URI: http://www.getpushmonkey.com/?source=plugin
 * License: GPL2
 */
  
  /*
  Push Monkey Light - Web Push Notifications
  Copyright (C) 2017 Get Push Monkey Ltd. (email : tudor@getpushmonkey.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
   */

// WordPress check.
if ( ! defined( 'ABSPATH' ) ) exit;

// Require plugin core file.
require_once( plugin_dir_path( __FILE__ ) . 'includes/class_push_monkey_light_core.php' );

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
add_action( 'plugins_loaded', 'push_monkey_light_load_textdomain' );
function push_monkey_light_load_textdomain() {

  load_plugin_textdomain( 'push-monkey-light', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}

/**
 * Push Monkey Light deactivate.
 */
function push_monkey_light_deactivate() {

  flush_rewrite_rules(true);
}

/**
 * Push Monkey Light activate.
 */
function push_monkey_light_activate() {

  push_monkey_light_rewrite_service_worker_url();
  flush_rewrite_rules(true);
}

/**
 * Push Monkey Light rewrite service worker url.
 */
function push_monkey_light_rewrite_service_worker_url() {

  $account_key = get_option( Push_Monkey_Light::ACCOUNT_KEY_KEY, NULL );
  if ( $account_key ) {
  
    add_rewrite_rule( '^service\-worker\-' . $account_key . '\.php/?',  plugin_dir_path( __DIR__ ) . 'templates/pages/service_worker.php', 
      'top' );
  }
}

/**
 * Push Monkey Light plugin updated.
 *
 * @param      object  $upgrader_object  The upgrader object
 * @param      array  $options          The options
 */
function push_monkey_light_plugin_updated( $upgrader_object, $options ) { 

  $current_plugin_path_name = plugin_basename( __FILE__ );
  if ( $options['action'] == 'update' && $options['type'] == 'plugin' ){
    // if check packages exitst or not
    if ( isset( $options['packages'] ) ) {

      foreach( $options['packages'] as $each_plugin ) {

        if ( $each_plugin == $current_plugin_path_name ) {
          
          push_monkey_light_rewrite_service_worker_url();
          flush_rewrite_rules(true);
        }
      }
    }
  }
}

/**
 * Push Monkey Light initialize.
 */
function push_monkey_light_init() {
  // Register deactivation.
  register_deactivation_hook( __FILE__, 'push_monkey_light_deactivate' );
  // Register activation.
  register_activation_hook( __FILE__, 'push_monkey_light_activate' );
  // Plugin upgrade process hook. 
  add_action( 'upgrader_process_complete', 'push_monkey_light_plugin_updated', 10, 2 );  
  // plugin core class.
  $push_monkey = new Push_Monkey_Light();
  $push_monkey->push_monkey_light_instance();
}

push_monkey_light_init();
