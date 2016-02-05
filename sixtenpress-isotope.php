<?php
/**
 * Isotope handler for SixTen Press
 *
 * @package   SixTenPressIsotope
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 * @copyright 2015 Robin Cornett Creative, LLC
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
		'class-sixtenpressisotope-settings',
	);

	foreach ( $files as $file ) {
		require plugin_dir_path( __FILE__ ) . 'includes/' . $file . '.php';
	}
}
sixtenpressisotope_require();

// Instantiate dependent classes
$sixtenpressisotope_settings = new SixTenPressIsotopeSettings();

// Instantiate main class and pass in dependencies
$sixtenpressisotope = new SixTenPressIsotope(
	$sixtenpressisotope_settings
);

// Run the plugin
$sixtenpressisotope->run();

/**
 * Function to enqueue isotope scripts and do the isotope things.
 */
function sixtenpress_enqueue_isotope() {
	wp_register_script( 'sixtenpess-isotope', plugin_dir_url( __FILE__ ) . '/js/isotope.min.js', array( 'jquery' ), '2.2.2', true );
	wp_register_script( 'sixtenpess-isotope-images', plugin_dir_url( __FILE__ ) . '/js/imagesloaded.min.js', array(), '4.1.0', true );
	wp_enqueue_script( 'sixtenpress-isotope-set', plugin_dir_url( __FILE__ ) . '/js/isotope-set.js', array( 'sixtenpess-isotope', 'sixtenpess-isotope-images' ), '1.0.0', true );

	add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
	add_action( 'genesis_before_loop', 'sixtenpress_open_div', 25 );
	remove_action( 'genesis_entry_content', 'genesis_do_post_image', 8 );
	remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
	add_action( 'genesis_entry_header', 'genesis_do_post_image', 5 );
	add_action( 'genesis_after_endwhile', 'sixtenpress_close_div', 5 );

}

/**
 * Wraps articles/posts in a div. Required for isotope.
 */
function sixtenpress_open_div() {
	do_action( 'sixtenpress_before_isotope' );
	echo '<div class="isotope">';
}

/**
 * Closes the div added above. Required for isotope.
 *
 */
function sixtenpress_close_div() {
	echo '</div>';
	echo '<br clear="all">';
	do_action( 'sixtenpress_after_isotope' );
}
