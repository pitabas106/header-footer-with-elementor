<?php
/**
 * Plugin Name: Header & Footer with Elementor
 * Plugin URI:  https://github.com/pitabas/header-footer-with-elementor
 * Description: Create custom Header and Footer for your site using the Elementor Page Builder.
 * Version:     1.0.0
 * Author:      Pitabas Behera
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: header-footer-with-elementor
 * Domain Path: /languages/
 *
 * @package header-footer-with-elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define plugin paths & version
define( 'HFWE_FILE', __FILE__ );

define( 'HFWE_DIR', trailingslashit( plugin_dir_path( HFWE_FILE ) ) );

define( 'HFWE_URL', plugins_url( '/', HFWE_FILE ) );

define( 'HFWE_PATH', plugin_basename( HFWE_FILE ) );

define( 'HFWE_INC_DIR', trailingslashit ( HFWE_DIR . 'inc' ) );

define( 'HFWE_ASSETS_DIR', trailingslashit ( HFWE_DIR . 'assets' ) );

define( 'HFWE_VERSION', '1.0.0' );

/**
 * Load the Plugin Class
 */
function hfwe_init() {
	require_once HFWE_DIR . 'inc/class-header-footer-with-elementor.php';
}

add_action( 'plugins_loaded', 'hfwe_init' );
