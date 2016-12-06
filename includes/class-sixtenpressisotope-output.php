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

	/**
	 * Decide whether or not we can do the isotope output.
	 */
	public function maybe_do_isotope() {
		if ( is_singular() || is_admin() ) {
			return;
		}
		if ( ! $this->post_type_supports() ) {
			return;
		}
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_isotope' ) );
		add_action( 'wp_head', array( $this, 'inline_style' ) );
		add_action( 'wp_head', array( $this, 'pick_filter' ) );
		$post_type = $this->get_current_post_type();
		if ( $this->setting[ $post_type ]['search'] ) {
			add_action( 'sixtenpress_before_isotope', array( $this, 'add_search_input' ), 20 );
		}
		if ( function_exists( 'genesis' ) ) {
			$this->do_genesis_things();
		} else {
			add_action( 'loop_start', array( $this, 'open_div' ), 25 );
			add_action( 'loop_end', array( $this, 'close_div' ), 5 );
		}
	}

	/**
	 * Do genesis specific functions.
	 */
	protected function do_genesis_things() {
		if ( $this->setting['layout'] ) {
			add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
		}
		add_filter( 'genesis_options', array( $this, 'modify_genesis_options' ), 15 );
		remove_action( 'genesis_entry_content', 'genesis_do_post_image', 8 );
		if ( $this->setting['remove']['content'] ) {
			remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
		}
		if ( $this->setting['remove']['before'] ) {
			remove_post_type_support( $this->get_current_post_type(), 'genesis-entry-meta-before-content' );
		}
		if ( $this->setting['remove']['after'] ) {
			remove_post_type_support( $this->get_current_post_type(), 'genesis-entry-meta-after-content' );
		}
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
		$version = '1.1.0';
		wp_register_script( 'sixtenpress-isotope', plugin_dir_url( __FILE__ ) . 'js/isotope.min.js', array( 'jquery' ), '3.0.0', true );
		if ( ! wp_script_is( 'imagesloaded', 'registered' ) ) {
			wp_register_script( 'imagesloaded', plugin_dir_url( __FILE__ ) . 'js/imagesloaded.min.js', array(), '4.1.0', true );
		}
		$dependent_scripts = array( 'sixtenpress-isotope', 'imagesloaded' );
		wp_register_script( 'infinite-scroll', plugin_dir_url( __FILE__ ) . 'js/jquery.infinitescroll.min.js', array(), '2.1.0', true );
		if ( $this->setting['infinite'] ) {
			$dependent_scripts[] = 'infinite-scroll';
		}
		wp_enqueue_script( 'sixtenpress-isotope-set', plugin_dir_url( __FILE__ ) . 'js/isotope-set.js', $dependent_scripts, $version, true );

		add_action( 'wp_print_scripts', array( $this, 'localize' ) );
	}

	/**
	 * Localize the script for isotope output.
	 */
	public function localize() {
		$options = $this->get_isotope_options();
		wp_localize_script( 'sixtenpress-isotope-set', 'SixTenPressIsotope', $options );
	}

	/**
	 * Get the isotope options for localization, inline scripts
	 * @return array
	 */
	protected function get_isotope_options() {
		$post_type_name = $this->get_current_post_type();
		$gutter         = isset( $this->setting[ $post_type_name ]['gutter'] ) ? $this->setting[ $post_type_name ]['gutter'] : 0;
		$options        = apply_filters( 'sixtenpress_isotope_options', array(
			'container' => 'isotope',
			'selector'  => '.entry',
			'gutter'    => $gutter,
		) );
		$isotope = apply_filters( 'sixtenpress_isotope_options', array(
			'isotopeRules' => array(
				'itemSelector'    => $options['selector'],
				'percentPosition' => true,
				'masonry'         => [
					'isAnimated' => true,
					'gutter'     => (int) $options['gutter'],
				]
			),
		) );
		$array = array(
			'loading'  => plugin_dir_url( __FILE__ ) . 'images/loading.svg',
			'msg'      => __( 'Loading...', 'sixtenpress-featured-content-masonry' ),
			'infinite' => (bool) $this->setting['infinite'],
			'finished' => __( 'No more items to load.', 'sixtenpress-isotope' ),
		);

		return array_merge( $options, $isotope, $array );
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
	 * Get the post type and see if post type support should be added.
	 *
	 * @param $query WP_Query
	 */
	public function maybe_add_post_type_support( $query ) {
		$this->setting = sixtenpressisotope_get_settings();
		if ( ! $query->is_main_query() || $query->is_search() || $query->is_feed() || is_admin() ) {
			return;
		}
		$post_types = $query->get( 'post_type' );
		if ( empty( $post_types ) ) {
			$post_types = 'post';
		}

		if ( is_array( $post_types ) ) {
			foreach ( $post_types as $post_type ) {
				$this->add_post_type_support( $query, $post_type );
			}
		} else {
			$this->add_post_type_support( $query, $post_types );
		}
	}

	/**
	 * Actually add the post type support.
	 * @param $query
	 * @param $post_type
	 */
	protected function add_post_type_support( $query, $post_type ) {
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

	/**
	 * @param $args
	 *
	 * @return mixed
	 */
	public function modify_genesis_options( $args ) {
		if ( 'default' !== $this->setting['image_size'] ) {
			$args['content_archive_thumbnail'] = 1;
			$args['image_size']                = $this->setting['image_size'];
		}
		if ( 'default' !== $this->setting['alignment'] ) {
			$args['image_alignment'] = $this->setting['alignment'];
		}

		return $args;
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
		$css        = sprintf(
			'.js .%4$s { opacity: 0; }
			.%3$s { clear: both; margin: 0 auto 40px; overflow: visible; }
			.%3$s %4$s { float: left; margin: 0 0 %2$spx; %1$s }
			.main-filter { margin-bottom: 40px; overflow: auto; }
			.main-filter ul { text-align: center; }
			.main-filter li { display: inline-block; margin: 1px; }
			.isotope-search-form { margin-bottom: 40px; }
			div#infscr-loading { position: absolute; right: 0; bottom: 0; left: 0; text-align: center; }
			div#infscr-loading img { display: block; margin: 0 auto; }',
			$one_half,
			$options['gutter'],
			$options['container'],
			$options['selector']
		);
		if ( $this->setting['columns'] > 2 ) {
			$one_third = 'width: -webkit-calc(33.33333% - ' . 2 * $options['gutter'] / 3 . 'px); width: calc(33.33333% - ' . 2 * $options['gutter'] / 3 . 'px);';
			$css      .= sprintf(
				'@media only screen and (min-width: 600px) { .%1$s %2$s { %3$s } }',
				$options['container'],
				$options['selector'],
				$one_third
			);
		}
		if ( $this->setting['columns'] > 3 ) {
			$one_fourth = 'width: -webkit-calc(25% - ' . 3 * $options['gutter'] / 4 . 'px); width: calc(25% - ' . 3 * $options['gutter'] / 4 . 'px);';
			$css       .= sprintf(
				'@media only screen and (min-width: 1023px) { .%1$s %2$s { %3$s } }',
				$options['container'],
				$options['selector'],
				$one_fourth
			);
		}

		$css = apply_filters( 'sixtenpress_isotope_inline_style', $css, $post_type, $this->setting, $options );
		// Minify a bit
		$css = str_replace( "\t", '', $css );
		$css = str_replace( array( "\n", "\r" ), ' ', $css );

		// Echo the CSS
		echo '<style type="text/css" media="screen">' . strip_tags( $css ) . '</style>';
	}

	/**
	 * Wraps articles/posts in a div. Required for isotope.
	 */
	function open_div() {
		if ( ! is_main_query() || ! in_the_loop() ) {
			return;
		}
		do_action( 'sixtenpress_before_isotope' );
		$options = $this->get_isotope_options();
		echo '<div class="' . $options['container'] . '" id="' . $options['container'] . '">';
	}

	/**
	 * Closes the div added above. Required for isotope.
	 *
	 */
	function close_div() {
		if ( ! is_main_query() || ! in_the_loop() ) {
			return;
		}
		echo '</div>';
		echo '<div class="clearfix"></div>';
		do_action( 'sixtenpress_after_isotope' );
	}

	/**
	 * Add a text based search input.
	 */
	public function add_search_input() {
		$post_type = $this->get_current_post_type();
		$object    = get_post_type_object( $post_type );

		printf( '<div class="isotope-search-form"><input type="text" class="isotope-search" name="isotope-search" placeholder="%s %s"></div>', __( 'Search', 'sixtenpress-isotope' ), $object->label );
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
		if ( 1 === $count ) {
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
		$output .= '</div><div class="clearfix"></div>';
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
