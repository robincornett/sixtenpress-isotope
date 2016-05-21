<?php
/**
 * Isotope handler for SixTen Press
 *
 * @package   SixTenPressIsotope
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 * @copyright 2016 Robin Cornett Creative, LLC
 *
 * Plugin Name:       SixTen Press Isotope
 * Plugin URI:        http://robincornett.com
 * Description:       SixTen Press Isotope makes building an isotope layout archive super easy.
 * Author:            Robin Cornett
 * Author URI:        http://robincornett.com
 * Text Domain:       sixtenpress
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Version:           1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'SIXTENPRESSISOTOPE_BASENAME' ) ) {
	define( 'SIXTENPRESSISOTOPE_BASENAME', plugin_basename( __FILE__ ) );
}

// Include classes
function sixtenpressisotope_require() {
	$files = array(
		'class-sixtenpressisotope',
		'class-sixtenpressisotope-help',
		'class-sixtenpressisotope-output',
		'class-sixtenpressisotope-validation',
		'class-sixtenpressisotope-settings',
	);

	foreach ( $files as $file ) {
		require plugin_dir_path( __FILE__ ) . 'includes/' . $file . '.php';
	}
}
sixtenpressisotope_require();

// Instantiate dependent classes
$sixtenpressisotope_output   = new SixTenPressIsotopeOutput();
$sixtenpressisotope_settings = new SixTenPressIsotopeSettings();

// Instantiate main class and pass in dependencies
$sixtenpressisotope = new SixTenPressIsotope(
	$sixtenpressisotope_output,
	$sixtenpressisotope_settings
);

// Run the plugin
$sixtenpressisotope->run();

/**
 * Helper function to retrieve the plugin setting, with defaults.
 * @return mixed|void
 */
function sixtenpressisotope_get_settings() {
	return apply_filters( 'sixtenpressisotope_get_plugin_setting', false );
}
