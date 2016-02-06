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
		add_action( 'pre_get_posts', array( $this, 'posts_per_page' ), 9999 );
		add_action( 'template_redirect', array( $this, 'do_isotope' ) );
		add_action( 'wp_print_scripts', array( $this, 'localize' ) );

		add_action( 'genesis_after_header', array( $this, 'pick_filter' ) );
		add_action( 'sixtenpress_before_isotope', array( $this, 'do_isotope_buttons' ) );
		add_action( 'sixtenpress_before_isotope', array( $this, 'do_isotope_select' ) );
	}

	/**
	 * Fire up isotope work if the post type supports it.
	 */
	public function do_isotope() {
		if ( is_singular() ) {
			return;
		}
		if ( $this->post_type_supports() ) {
			add_action( 'wp_enqueue_scripts', 'sixtenpress_enqueue_isotope' );
		}
	}

	/**
	 * Check whether the current post type supports isotope.
	 * @return bool
	 */
	protected function post_type_supports() {
		$post_type = $this->check_post_type();
		if ( isset( $this->setting[ $post_type ]['support'] ) && $this->setting[ $post_type ]['support'] ) {
			add_post_type_support( $post_type, 'sixtenpress-isotope' );
		}
		if ( post_type_supports( $post_type, 'sixtenpress-isotope' ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Localize the script for isotope output.
	 */
	public function localize() {
		$post_type_name = $this->check_post_type();
		$gutter         = 0;
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
	protected function check_post_type() {
		$post_type_name = get_post_type();
		if ( false === get_post_type() ) {
			$post_type_name = get_query_var( 'post_type' );
		}
		return $post_type_name;
	}

	/**
	 * Build the array/string of taxonomies to use as a filter.
	 * @param array $filters
	 *
	 * @return array|string
	 */
	public function build_filter_array( $filters = array() ) {
		$post_type  = $this->check_post_type();
		$taxonomies = get_object_taxonomies( $post_type, 'names' );
		if ( ! $taxonomies ) {
			return $filters;
		}
		foreach ( $taxonomies as $taxonomy ) {
			if ( key_exists( $taxonomy, $this->setting[ $post_type ] ) && $this->setting[ $post_type ][ $taxonomy ] ) {
				$filters[] = $taxonomy;
			};
		}
		$count = count( $filters );
		if ( $count === 1 ) {
			$filters = implode( $filters );
		}
		return $filters;
	}

	/**
	 * Determine which filter to use.
	 */
	public function pick_filter() {
		$filters = $this->build_filter_array();
		if ( empty( $filters ) ) {
			return;
		}
		$hook = 'sixtenpress_isotope_select_terms';
		if ( is_string( $filters ) ) {
			$hook = 'sixtenpress_isotope_buttons';
		}
		if ( ! has_action( $hook ) ) {
			add_filter( $hook, array( $this, 'build_filter_array' ) );
		}
	}

	/**
	 * Build the filter(s) for the isotope.
	 * @param $select_options array containing terms, name, singular name, and optional class for the select.
	 * @param string $filter_name string What to name the filter heading (optional)
	 */
	public function do_isotope_select() {
		$select_options = apply_filters( 'sixtenpress_isotope_select_terms', array() );
		if ( ! $select_options ) {
			return;
		}
		$count        = count( $select_options );
		$column_class = $this->select_class( $count );
		$output       = '<div class="main-filter">';
		$object       = get_post_type_object( $this->check_post_type() );
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
		$taxonomy = apply_filters( 'sixtenpress_isotope_buttons', array() );
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
