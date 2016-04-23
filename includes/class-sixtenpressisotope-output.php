<?php
/**
 *
 * Class to handle isotope output.
 *
 * @package   SixTenPressIsotope
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 * @copyright 2016 Robin Cornett Creative, LLC
 */

class SixTenPressIsotopeOutput {

	/**
	 * @var array The plugin setting.
	 */
	protected $setting;

	public function maybe_do_isotope() {
		if ( ! $this->post_type_supports() ) {
			return;
		}
		if ( is_singular() || is_admin() ) {
			return;
		}
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_isotope' ) );
		add_action( 'wp_head', array( $this, 'inline_style' ) );
		if ( function_exists( 'genesis' ) ) {
			$this->do_genesis_things();
		}
	}

	/**
	 * Do genesis specific functions.
	 */
	protected function do_genesis_things() {
		add_action( 'genesis_after_header', array( $this, 'pick_filter' ) );
		add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
		remove_action( 'genesis_entry_content', 'genesis_do_post_image', 8 );
		remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
		add_action( 'genesis_entry_header', 'genesis_do_post_image', 5 );
		add_action( 'genesis_before_loop', array( $this, 'open_div' ), 25 );
		add_action( 'genesis_after_endwhile', array( $this, 'close_div' ), 5 );
	}

	/**
	 * Check whether the current post type supports isotope.
	 * Can be modified via filter (eg on taxonomies).
	 * @return bool
	 */
	protected function post_type_supports( $post_type = '' ) {
		$support   = false;
		$post_type = empty( $post_type ) ? $this->get_current_post_type() : $post_type;
		if ( is_array( $post_type ) ) {
			foreach( $post_type as $type ) {
				$support = post_type_supports( $type, 'sixtenpress-isotope' );
				if ( ! $support ) {
					break;
				}
			}
		} else {
			$support = post_type_supports( $post_type, 'sixtenpress-isotope' );
		}
		return (bool) apply_filters( 'sixtenpress_isotope_support', $support );
	}

	/**
	 * Function to enqueue isotope scripts and do the isotope things.
	 */
	public function enqueue_isotope() {
		wp_register_script( 'sixtenpress-isotope', plugin_dir_url( __FILE__ ) . 'js/isotope.min.js', array( 'jquery' ), '2.2.2', true );
		wp_register_script( 'sixtenpress-isotope-images', plugin_dir_url( __FILE__ ) . 'js/imagesloaded.min.js', array(), '4.1.0', true );
		wp_enqueue_script( 'sixtenpress-isotope-set', plugin_dir_url( __FILE__ ) . 'js/isotope-set.js', array( 'sixtenpress-isotope', 'sixtenpress-isotope-images' ), '1.0.0', true );

		add_action( 'wp_print_scripts', array( $this, 'localize' ) );
	}

	/**
	 * Localize the script for isotope output.
	 */
	public function localize() {
		$options = $this->get_isotope_options();
		wp_localize_script( 'sixtenpress-isotope-set', 'SixTenPress', $options );
	}

	/**
	 * Get the isotope options for localization, inline scripts
	 * @return mixed|void
	 */
	protected function get_isotope_options() {
		$post_type_name = $this->get_current_post_type();
		$gutter         = isset( $this->setting[ $post_type_name ]['gutter'] ) ? $this->setting[ $post_type_name ]['gutter'] : 0;
		$options        = apply_filters( 'sixtenpress_isotope_options', array(
			'container' => 'isotope',
			'selector'  => '.entry',
			'gutter'    => $gutter,
		) );

		return $options;
	}

	/**
	 * Check the current post type.
	 * @return false|mixed|string
	 */
	protected function get_current_post_type() {
		$post_type_name = get_post_type();
		if ( false === get_post_type() ) {
			$post_type_name = get_query_var( 'post_type' );
		}
		return $post_type_name;
	}

	/**
	 * Add isotope support to the relevant post types.
	 */
	public function add_post_type_support( $query ) {
		$this->setting = get_option( 'sixtenpressisotope', false );
		if ( ! $query->is_main_query() ) {
			return;
		}
		$post_type = empty( $query->get( 'post_type' ) ) ? 'post' : $query->get( 'post_type' );
		if ( isset( $this->setting[ $post_type ]['support'] ) && $this->setting[ $post_type ]['support'] ) {
			add_post_type_support( $post_type, 'sixtenpress-isotope' );
			$this->posts_per_page( $query, $post_type );
		}
	}

	/**
	 * @param $query WP_Query
	 */
	public function posts_per_page( $query, $post_type ) {
		// add a filter to optionally override this query
		if ( apply_filters( 'sixtenpress_isotope_override_query', false, $post_type ) ) {
			return;
		}
		$query->set( 'posts_per_page', $this->setting['posts_per_page'] );
	}
	}

	/**
	 * Add the inline stylesheet.
	 */
	public function inline_style() {
		if ( ! $this->setting['style'] || apply_filters( 'sixtenpress_isotope_remove_inline_style', false ) ) {
			return;
		}
		$post_type  = $this->get_current_post_type();
		$options    = $this->get_isotope_options();
		$one_half   = 'width: -webkit-calc(50% - ' . $options['gutter'] / 2 . 'px); width: calc(50% - ' . $options['gutter'] / 2 . 'px);';
		$one_third  = 'width: -webkit-calc(33.33333% - ' . 2 * $options['gutter'] / 3 . 'px); width: calc(33.33333% - ' . 2 * $options['gutter'] / 3 . 'px);';
		$one_fourth = 'width: -webkit-calc(25% - ' . 3 * $options['gutter'] / 4 . 'px); width: calc(25% - ' . 3 * $options['gutter'] / 4 . 'px);';
		$css        = sprintf( '
			.%5$s {
				clear: both;
				margin-bottom: 40px;
			}
			.%5$s %6$s {
				float: left;
				margin-bottom: %2$s;
				%1$s
			}
			.main-filter ul {
				text-align: center;
			}
			.main-filter li {
				display: inline-block;
				margin: 1px;
			}
			@media only screen and (min-width: 600px) {
				.%5$s %6$s {
					%3$s
				}
			}
			@media only screen and (min-width: 1023px) {
				.%5$s %6$s {
					%4$s
				}
			}',
			$one_half,
			$options['gutter'],
			$one_third,
			$one_fourth,
			$options['container'],
			$options['selector']
		);

		$css = apply_filters( 'sixtenpress_isotope_inline_style', $css, $post_type, $this->setting );
		// Minify a bit
		$css = str_replace( "\t", '', $css );
		$css = str_replace( array( "\n", "\r" ), ' ', $css );

		// Echo the CSS
		echo '<style type="text/css" media="screen">' . $css . '</style>';
	}

	/**
	 * Wraps articles/posts in a div. Required for isotope.
	 */
	function open_div() {
		do_action( 'sixtenpress_before_isotope' );
		$options = $this->get_isotope_options();
		echo '<div class="' . $options['container'] . '" id="' . $options['container'] . '">';
	}

	/**
	 * Closes the div added above. Required for isotope.
	 *
	 */
	function close_div() {
		echo '</div>';
		echo '<br clear="all">';
		do_action( 'sixtenpress_after_isotope' );
	}


	/**
	 * Build the array/string of taxonomies to use as a filter.
	 *
	 * @param array $tax_filters
	 *
	 * @return array|string
	 */
	public function build_filter_array( $tax_filters = array() ) {
		$post_type  = $this->get_current_post_type();
		$taxonomies = get_object_taxonomies( $post_type, 'names' );
		$taxonomies = 'post' === $post_type ? array( 'category' ) : $taxonomies;
		if ( $taxonomies ) {
			if ( ! isset( $this->setting[ $post_type ] ) || null === $this->setting[ $post_type ] ) {
				$this->setting[ $post_type ] = array();
			}
			foreach ( $taxonomies as $taxonomy ) {
				if ( key_exists( $taxonomy, $this->setting[ $post_type ] ) && $this->setting[ $post_type ][ $taxonomy ] ) {
					$tax_filters[] = $taxonomy;
				};
			}
		}
		return apply_filters( 'sixtenpress_isotope_filter_terms', $tax_filters, $post_type, $taxonomies, $this->setting );
	}

	/**
	 * Count the taxonomies for the filters--if one, return a string instead of an array.
	 * @return array|string
	 */
	protected function updated_filters() {
		$tax_filters = $this->build_filter_array();
		$count       = count( $tax_filters );
		if ( $count === 1 ) {
			$tax_filters = implode( $tax_filters );
		}
		return $tax_filters;
	}

	/**
	 * Determine which filter to use.
	 */
	public function pick_filter() {
		if ( ! is_post_type_archive() && ! is_home() ) {
			return;
		}
		$filters = $this->updated_filters();
		if ( empty( $filters ) ) {
			return;
		}
		$action = 'do_isotope_select';
		if ( is_string( $filters ) ) {
			$action = 'do_isotope_buttons';
		}
		add_action( 'sixtenpress_before_isotope', array( $this, $action ) );
	}

	/**
	 * Build the filter(s) for the isotope.
	 * @param $select_options array containing terms, name, singular name, and optional class for the select.
	 * @param string $filter_name string What to name the filter heading (optional)
	 */
	public function do_isotope_select() {
		$select_options = $this->updated_filters();
		if ( ! $select_options ) {
			return;
		}
		$count        = count( $select_options );
		$column_class = $this->select_class( $count );
		$output       = '<div class="main-filter">';
		$object       = get_post_type_object( $this->get_current_post_type() );
		$filter_text  = sprintf( __( 'Filter %s By:', 'sixtenpress-isotope' ), esc_attr( $object->labels->name ) );
		$output      .= sprintf( '<h4>%s</h4>', esc_html( $filter_text ) );
		$i            = 0;
		foreach ( $select_options as $option ) {
			$class = $column_class;
			if ( 0 === $i ) {
				$class .= ' first';
			}
			$output .= $this->build_taxonomy_select( $option, $class );
			$i++;
		}
		$output .= '<br clear="all" />';
		$output .= '</div>';
		echo $output;
	}

	/**
	 * Build a select/dropdown for isotope filtering.
	 * @param $option array
	 */
	protected function build_taxonomy_select( $option, $class ) {
		$output = sprintf( '<select name="%1$s" id="%1$s-filters" class="%2$s" data-filter-group="%1$s">',
			esc_attr( strtolower( $option ) ),
			esc_attr( $class )
		);
		$tax_object = get_taxonomy( $option );
		$label      = $tax_object->labels->name;
		$all_things = sprintf( __( 'All %s', 'sixtenpress-isotope' ), $label );
		$output .= sprintf( '<option value="all" data-filter-value="">%s</option>',
			esc_html( $all_things )
		);
		$terms = get_terms( $option );
		$items = '';
		foreach ( $terms as $term ) {
			$class  = sprintf( '%s-%s', esc_attr( $option ), esc_attr( $term->slug ) );
			$items .= sprintf( '<option value="%1$s" data-filter-value=".%1$s">%2$s</option>',
				esc_attr( $class ),
				esc_attr( $term->name )
			);
		}
		$output .= apply_filters( "sixtenpress_isotope_filter_{$option}_items", $items, $option, $class, $terms );
		$output .= '</select>';
		return $output;
	}

	/**
	 * @param $count
	 * @param string $class
	 *
	 * @return string
	 */
	protected function select_class( $count ) {
		$class = 'filter';
		if ( 0 === $count % 3 ) {
			$class .= ' one-third';
		} elseif ( 0 === $count % 4 ) {
			$class .= ' one-fourth';
		} elseif ( 0 === $count % 2 ) {
			$class .= ' one-half';
		}

		return apply_filters( 'sixtenpressisotope_select_class', $class );
	}

	/**
	 * @param $taxonomy string taxonomy for which to generate buttons
	 *
	 * @return string
	 * example:
	 * function soulcarepeople_buttons() {
	 *     sixtenpress_do_isotope_buttons( 'group' );
	 * }
	 */
	public function do_isotope_buttons() {
		$taxonomy = $this->updated_filters();
		if ( ! $taxonomy ) {
			return;
		}

		$terms = get_terms( $taxonomy );
		if ( ! $terms ) {
			return;
		}
		$output  = '<div class="main-filter">';
		$output .= sprintf( '<h4>%s</h4>', __( 'Filter By: ', 'sixtenpress-isotope' ) );
		$output .= sprintf( '<ul id="%s" class="filter">', esc_html( $taxonomy ) );
		$output .= sprintf( '<li><button class="active" data-filter="*">%s</button></li>', __( 'All', 'sixtenpress-isotope' ) );
		$items = '';
		foreach ( $terms as $term ) {
			$items .= sprintf( '<li><button data-filter=".%s-%s">%s</button></li>',
				esc_html( $taxonomy ),
				esc_html( $term->slug ),
				esc_html( $term->name )
			);
		}
		$output .= apply_filters( "sixtenpress_isotope_filter_{$taxonomy}_items", $items, $taxonomy, $terms );
		$output .= '</ul>';
		$output .= '</div>';

		echo $output;
	}
}
