<?php
/**
 * Plugin Name: ArvanCloud VOD
 * Plugin URI: https://www.arvancloud.com/en/products/video-platform
 * Description: ArvanCloud Video On Demand (VOD) service plugin for WordPress
 * Version: 1.0.2
 * Author: ArvanCloud, Arian
 * Author URI: https://www.arvancloud.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: arvancloud-vod
 * Domain Path: /languages
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

use WP_Arvan\Engine\Setup;
// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

define( 'ACVOD_VERSION', '1.0.1' );
define( 'ACVOD_TEXTDOMAIN', 'arvancloud-vod' );
define( 'ACVOD_NAME', 'ArvanCloud VOD' );
define( 'ACVOD_PLUGIN_ROOT', plugin_dir_path( __FILE__ ) );
define( 'ACVOD_PLUGIN_ROOT_URL', plugin_dir_url( __FILE__ ) );
define( 'ACVOD_PLUGIN_ABSOLUTE', __FILE__ );
define( 'ACVOD_MIN_PHP_VERSION', '7.0' );
define( 'ACVOD_WP_VERSION', '5.3' );

add_action(
	'init',
	static function () {
		load_plugin_textdomain( ACVOD_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
	);

if ( version_compare( PHP_VERSION, ACVOD_MIN_PHP_VERSION, '<=' ) ) {
	add_action(
		'admin_init',
		static function() {
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}
	);
	add_action(
		'admin_notices',
		static function() {
			echo wp_kses_post(
				sprintf(
					'<div class="notice notice-error"><p>%s</p></div>',
					__( '"ArvanCloud VOD" requires PHP 5.6 or newer.', 'arvancloud-vod' )
				)
			);
		}
	);

	// Return early to prevent loading the plugin.
	return;
}

require_once(ACVOD_PLUGIN_ROOT . 'vendor/autoload.php');
require_once(ACVOD_PLUGIN_ROOT . 'inc/API/VOD/video_tags.php');
require_once(ACVOD_PLUGIN_ROOT . 'inc/VOD/Assets/Video_Hooks.php');
require_once(ACVOD_PLUGIN_ROOT . 'inc/VOD/Assets/Sync.php');
require_once(ACVOD_PLUGIN_ROOT . 'inc/ajax.php');
require_once(ACVOD_PLUGIN_ROOT . 'inc/short_stream.php');
require_once(ACVOD_PLUGIN_ROOT . 'inc/short_vid_list.php');
require ACVOD_PLUGIN_ROOT . 'vendor/woocommerce/action-scheduler/action-scheduler.php';

define( 'ACVOD_PLUGIN_STATUS', Setup::is_plugin_has_selected_channel() );
(new Setup())->run();
new Video_Hooks();

// Register AJAX action for manual sync
add_action('wp_ajax_arvancloud_vod_manual_sync', function() {
    (new WP_Arvan\Engine\Setup())->ajax_manual_sync();
});

// Register AJAX action for debug info
add_action('wp_ajax_arvancloud_vod_debug', function() {
    (new WP_Arvan\Engine\Setup())->ajax_debug_sync();
});

// Register AJAX action for repair media
add_action('wp_ajax_arvancloud_vod_repair_media', function() {
    (new WP_Arvan\Engine\Setup())->ajax_repair_media();
});

// Register activation/deactivation hooks
register_activation_hook(__FILE__, function() {
    // Schedule the sync event on plugin activation
    $sync = new WP_Arvan\Engine\VOD\Assets\Sync();
    $sync->schedule_sync();

    // Log activation for debugging
    if (WP_DEBUG) {
        error_log('[ArvanCloud VOD] Plugin activated and sync scheduled');
    }
});

register_deactivation_hook(__FILE__, function() {
    // Unschedule the sync event on plugin deactivation
    $sync = new WP_Arvan\Engine\VOD\Assets\Sync();
    $sync->unschedule_sync();

    // Log deactivation for debugging
    if (WP_DEBUG) {
        error_log('[ArvanCloud VOD] Plugin deactivated and sync unscheduled');
    }
});

// Check if sync is running on schedule
add_action('admin_init', function() {
    if (WP_DEBUG && is_admin() && current_user_can('manage_options')) {
        $next_sync = wp_next_scheduled('arvancloud_vod_sync_event');
        if (!$next_sync) {
            // Sync is not scheduled, try to reschedule
            $sync = new WP_Arvan\Engine\VOD\Assets\Sync();
            $sync->schedule_sync();
            error_log('[ArvanCloud VOD] Auto-rescheduled missing sync event');
        }
    }
});
