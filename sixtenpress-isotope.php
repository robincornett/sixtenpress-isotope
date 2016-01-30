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
/**
 * Fire up isotope work if the post type supports it.
 */
function sixtenpress_do_isotope() {
	if ( is_singular() ) {
		return;
	}
	$post_type_name = get_post_type();
	if ( false === get_post_type() ) {
		$post_type_name = get_query_var( 'post_type' );
	}
	if ( post_type_supports( $post_type_name, 'sixtenpress-isotope' ) ) {
		add_action( 'wp_enqueue_scripts', 'sixtenpress_enqueue_isotope' );
	}
}

/**
 * Function to enqueue isotope scripts and do the isotope things.
 */
function sixtenpress_enqueue_isotope() {
	wp_register_script( 'sixtenpress-isotope', plugin_dir_url( __FILE__ ) . '/js/isotope.min.js', array( 'jquery' ), '2.2.2', true );
	wp_enqueue_script( 'sixtenpress-isotope-set', plugin_dir_url( __FILE__ ) . '/js/isotope-set.js', array( 'sixtenpress-isotope' ), '1.0.0', true );

	$options = apply_filters( 'sixtenpress_isotope_options', array(
		'container' => 'isotope',
		'selector'  => 'article',
		'gutter'    => 0,
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
	echo '<div class="isotope">';
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
	$output = '<div class="main-filter">';
	$filter_text = sprintf( __( 'Filter %s By:', 'sixtenpress' ), $filter_name );
	$output .= sprintf( '<h4>%s</h4>', esc_html( $filter_text ) );
	foreach ( $select_options as $option ) {
		$output .= sixtenpress_do_select( $option );
	}
	$output .= '<br clear="all" />';
	$output .= '</div>';
	return $output;
}

/**
 * Build a select/dropdown for isotope filtering.
 * @param $option array
 */
function sixtenpress_do_select( $option ) {
	$output = sprintf( '<select name="%1$s" id="%1$s-filters" class="filter %2$s" data-filter-group="%1$s">',
		esc_attr( strtolower( $option['name'] ) ),
		esc_attr( $option['class'] )
	);
	$all_things = sprintf( __( 'All', 'sixtenpress' ), ' ' . $option['name'] );
	$output .= sprintf( '<option value="*">%s</option>', esc_html( $all_things ) );
	foreach ( $option['terms'] as $term ) {
		$output .= sprintf( '<option value=".%s-%s">%s</option>',
			esc_attr( $option['singular'] ),
			esc_attr( $term->slug ),
			esc_attr( $term->name )
		);
	}
	$output .= '</select>';
	return $output;
}
