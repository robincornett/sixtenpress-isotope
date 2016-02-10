<?php

/**
 * @copyright $year Robin Cornett
 */
class SixTenPressIsotope {

	/**
	 * The settings class.
	 * @var $settings SixTenPressIsotopeSettings
	 */
	protected $settings;

	/**
	 * The plugin setting.
	 * @var $setting
	 */
	protected $setting;

	/**
	 * SixTenPressIsotope constructor.
	 *
	 * @param $settings
	 */
	public function __construct( $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Check for post type support, etc.
	 */
	public function run() {

		$this->setting = get_option( 'sixtenpressisotope', false );
		add_action( 'admin_menu', array( $this->settings, 'do_submenu_page' ) );
		add_action( 'init', array( $this->settings, 'add_post_type_support' ), 9999 );
		add_action( 'pre_get_posts', array( $this, 'posts_per_page' ), 9999 );
		add_action( 'template_redirect', array( $this, 'do_isotope' ) );
		add_action( 'wp_print_scripts', array( $this, 'localize' ) );
	}

	/**
	 * Fire up isotope work if the post type supports it.
	 */
	public function do_isotope() {
		if ( is_singular() || is_admin() ) {
			return;
		}
		if ( $this->post_type_supports() ) {
			add_action( 'wp_enqueue_scripts', 'sixtenpress_enqueue_isotope' );
			add_action( 'wp_head', array( $this, 'inline_style' ) );
			add_action( 'genesis_after_header', array( $this, 'pick_filter' ) );
		}
	}

	/**
	 * Check whether the current post type supports isotope.
	 * Can be modified via filter (eg on taxonomies).
	 * @return bool
	 */
	protected function post_type_supports() {
		$support   = false;
		$post_type = $this->get_current_post_type();
		if ( post_type_supports( $post_type, 'sixtenpress-isotope' ) ) {
			$support = true;
		}
		return (bool) apply_filters( 'sixtenpress_isotope_support', $support );
	}

	/**
	 * Localize the script for isotope output.
	 */
	public function localize() {
		if ( ! $this->post_type_supports() ) {
			return;
		}
		$post_type_name = $this->get_current_post_type();
		$gutter = 0;
		if ( isset( $this->setting[ $post_type_name ]['gutter'] ) ) {
			$gutter = $this->setting[ $post_type_name]['gutter'];
		}
		$options = apply_filters( 'sixtenpress_isotope_options', array(
			'container' => 'isotope',
			'selector'  => '.entry',
			'gutter'    => $gutter,
		) );
		wp_localize_script( 'sixtenpress-isotope-set', 'SixTenPress', $options );
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
	 * @param $query WP_Query
	 */
	public function posts_per_page( $query ) {
		if ( ! $query->is_main_query() ) {
			return;
		}
		$args     = array(
			'public'      => true,
			'_builtin'    => false,
			'has_archive' => true,
		);
		$output     = 'names';
		$post_types = get_post_types( $args, $output );
		$supported  = array();
		foreach( $post_types as $post_type ) {
			if ( post_type_supports( $post_type, 'sixtenpress-isotope' ) ) {
				$supported[] = $post_type;
			}
		}
		if ( in_array( true, $supported, false ) ) {
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
		$margin     = $this->setting[ $post_type ]['gutter'];
		$one_half   = 'width: -webkit-calc(50% - ' . $margin / 2 . 'px); width: calc(50% - ' . $margin / 2 . 'px);';
		$one_third  = 'width: -webkit-calc(33.33333% - ' . 2 * $margin / 3 . 'px); width: calc(33.33333% - ' . 2 * $margin / 3 . 'px);';
		$one_fourth = 'width: -webkit-calc(25% - ' . 3 * $margin / 4 . 'px); width: calc(25% - ' . 3 * $margin / 4 . 'px);';
		$css        = sprintf( '
			.isotope {
				clear: both;
				margin-bottom: 40px;
			}
			.isotope .entry {
				float: left;
				margin-bottom: %2$s;
				%1$s
			}
			.main-filter li {
				display: inline-block;
				margin: 1px;
			}
			@media only screen and (min-width: 600px) {
				.isotope .entry {
					%3$s
				}
			}
			@media only screen and (min-width: 1023px) {
				.isotope .entry {
					%4$s
				}
			}',
			$one_half,
			$margin . 'px',
			$one_third,
			$one_fourth
		);

		$css = apply_filters( 'sixtenpress_isotope_inline_style', $css, $post_type, $this->setting );
		// Minify a bit
		$css = str_replace( "\t", '', $css );
		$css = str_replace( array( "\n", "\r" ), ' ', $css );

		// Echo the CSS
		echo '<style type="text/css" media="screen">' . $css . '</style>';
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
		if ( ! $taxonomies ) {
			return $tax_filters;
		}
		if ( null === $this->setting[ $post_type ] ) {
			$this->setting[ $post_type ] = array();
		}
		foreach ( $taxonomies as $taxonomy ) {
			if ( key_exists( $taxonomy, $this->setting[ $post_type ] ) && $this->setting[ $post_type ][ $taxonomy ] ) {
				$tax_filters[] = $taxonomy;
			};
		}
		return apply_filters( 'sixtenpress_isotope_filter_terms', $tax_filters );
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
		foreach ( $terms as $term ) {
			$class   = sprintf( '%s-%s', esc_attr( $option ), esc_attr( $term->slug ) );
			$output .= sprintf( '<option value="%1$s" data-filter-value=".%1$s">%2$s</option>',
				esc_attr( $class ),
				esc_attr( $term->name )
			);
		}
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
		foreach ( $terms as $term ) {
			$output .= sprintf( '<li><button data-filter=".%s-%s">%s</button></li>',
				esc_html( $taxonomy ),
				esc_html( $term->slug ),
				esc_html( $term->name )
			);
		}
		$output .= '</ul>';
		$output .= '</div>';

		echo $output;
	}
}
