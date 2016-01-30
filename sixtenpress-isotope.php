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

add_action( 'template_redirect', 'sixtenpress_do_isotope' );
function sixtenpress_do_isotope() {
	$can_do = false;
	if ( ! is_singular() ) {
		$can_do = true;
	}
	$can_do = (bool) apply_filters( 'sixtenpress_do_isotope', $can_do );
	if ( $can_do ) {
		add_action( 'wp_enqueue_scripts', 'sixtenpress_enqueue_isotope' );
	}
	return $can_do;
}

/**
 * Function to enqueue isotope scripts and do the isotope things.
 */
function sixtenpress_enqueue_isotope() {
	wp_register_script( 'sixtenpress-isotope', plugin_dir_url( __FILE__ ) . '/js/isotope.min.js', array( 'jquery' ), '2.2.2', true );
	wp_enqueue_script( 'sixtenpress-isotope-set', plugin_dir_url( __FILE__ ) . '/js/isotope-set.js', array( 'sixtenpress-isotope' ), '1.0.0', true );

	$options = apply_filters( 'sixtenpress_isotope_options', array(
		'gutter' => 0,
	) );
	wp_localize_script( 'sixtenpress-isotope-set', 'SixTenPress', $options );

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
	echo '<div class="masonry">';
}

/**
 * Closes the div added above. Required for isotope.
 *
 */
function sixtenpress_close_div() {
	echo '</div>';
}

/**
 * Build the filter(s) for the isotope.
 * @param $select_options array containing terms, name, singular name, and optional class for the select.
 * @param string $filter_name string What to name the filter heading (optional)
 */
function sixtenpress_do_isotope_filter( $select_options, $filter_name = '' ) {
	echo '<div class="main-filter">';
	$filter_text = sprintf( __( 'Filter %s By:', 'sixtenpress' ), $filter_name );
	printf( '<h4>%s</h4>', esc_html( $filter_text ) );
	foreach ( $select_options as $option ) {
		sixtenpress_do_select( $option );
	}
	echo '<br clear="all" />';
	echo '</div>';
}

/**
 * Build a select/dropdown for isotope filtering.
 * @param $option array
 */
function sixtenpress_do_select( $option ) {
	printf( '<select name="%1$s" id="%1$s-filters" class="filter %2$s" data-filter-group="%1$s">',
		esc_attr( strtolower( $option['name'] ) ),
		esc_attr( $option['class'] )
	);
	$all_things = sprintf( __( 'All %s', 'sixtenpress' ), $option['name'] );
	printf( '<option value="*">%s</option>', esc_html( $all_things ) );
	foreach ( $option['terms'] as $term ) {
		printf( '<option value=".%s-%s">%s</option>',
			esc_attr( $option['singular'] ),
			esc_attr( $term->slug ),
			esc_attr( $term->name )
		);
	}
	echo '</select>';
}
