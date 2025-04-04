<?php
/**
 * Plugin Name: Arvan VOD Tool
 * Plugin URI: https://github.com/arian24b/arvan-vod-tool
 * Description: ArvanCloud Video On Demand (VOD) service plugin for WordPress
 * Version: 1.0.0
 * Author: Arian Omrani
 * Author URI: https://github.com/arian24b
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: arvan-vod-tool
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

define( 'ACVOD_VERSION', '1.0.0' );
define( 'ACVOD_TEXTDOMAIN', 'arvan-vod-tool' );
define( 'ACVOD_NAME', 'Arvan VOD Tool' );
define( 'ACVOD_PLUGIN_ROOT', plugin_dir_path( __FILE__ ) );
define( 'ACVOD_PLUGIN_ROOT_URL', plugin_dir_url( __FILE__ ) );
define( 'ACVOD_PLUGIN_ABSOLUTE', __FILE__ );
define( 'ACVOD_MIN_PHP_VERSION', '7.0' );
define( 'ACVOD_WP_VERSION', '5.3' );

// ... existing code ...
