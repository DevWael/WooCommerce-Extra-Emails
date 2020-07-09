<?php
/**
 * Plugin Name: WooCommerce Extra Emails
 * Plugin URI: https://github.com/DevWael/WooCommerce-Extra-Emails
 * Description: add extra emails to woocommerce order statuses.
 * Version: 1.0
 * Author: Ahmad Wael
 * Author URI: https://github.com/DevWael/WooCommerce-Extra-Emails
 * License: GPL2
 */

defined( 'ABSPATH' ) || exit; //prevent direct file access.

/**
 * Classes autoloader
 */
spl_autoload_register( 'wcea_autoloader' );
function wcea_autoloader( $class_name ) {
	$classes_dir = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR;
	$class_file  = $classes_dir . $class_name . '.php';
	if ( file_exists( $class_file ) ) {
		require_once $class_file;
	}

	return false;
}

$runner = new EA_Runner();
$runner->run_hooks();