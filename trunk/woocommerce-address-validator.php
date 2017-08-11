<?php
/**
 * Plugin Name: WooCommerce Address Validator
 * Plugin URI: https://www.address-validator.net/
 * Description: Validate billing and shipping addresses in WooCommerce.
 * Version: 1.2
 * Author: Byteplant
 * Author URI: https://www.address-validator.net/
 * License: GPL2
 * Text-Domain: woo-address-validator
 *
 * @package WCAV
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Load the plugin.
 */
function wcav_load() {
	load_plugin_textdomain(
		'woo-address-validator',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);

	require_once( dirname( __FILE__ ) . '/src/Plugin.php' );
	$plugin = new WCAV_Plugin();
	$plugin->setup();
}
add_action( 'after_setup_theme', 'wcav_load' );
