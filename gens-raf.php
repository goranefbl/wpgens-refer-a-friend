<?php

/**
 * Refer A Friend by WPGens Plugin
 *
 * @link              http://itsgoran.com
 * @since             1.0.0
 * @package           Gens_RAF
 *
 * @wordpress-plugin
 * Plugin Name:       Refer A Friend for WooCommerce by WPGens
 * Plugin URI:        http://itsgoran.com/gens-raf/
 * Description:       Refer A Friend System for WooCommerce. Go to WooCommerce -> Settings -> Refer a friend tab to set it up.
 * Version:           1.0.0
 * Author:            Goran Jakovljevic
 * Author URI:        http://www.itsgoran.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-gens-raf.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_gens_raf() {

	$plugin = new Gens_RAF();
	$plugin->run();

}

// Need to run after Woo has been loaded
add_action( 'plugins_loaded', 'run_gens_raf' );
