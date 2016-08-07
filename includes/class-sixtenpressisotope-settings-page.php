<?php

/**
 * Class for adding a new settings page to the WordPress admin, under Settings.
 * @package   SixTenPressIsotope
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 * @copyright 2016 Robin Cornett Creative, LLC
 */
class SixTenPressIsotopeSettings extends SixTenPressSettings {

	/**
	 * Option registered by plugin.
	 * @var array $setting
	 */
	protected $setting;

	/**
	 * Public registered post types.
	 * @var array $post_types
	 */
	protected $post_types;

	/**
	 * Slug for settings page.
	 * @var string $page
	 */
	protected $page;

	/**
	 * Settings fields registered by plugin.
	 * @var array
	 */
	protected $fields;

	/**
	 * Tab/page for settings.
	 * @var string $tab
	 */
	protected $tab = 'sixtenpressisotope';

	/**
	 * Maybe add the submenu page under Settings.
	 */
	public function do_submenu_page() {

		$this->page    = 'sixtenpress';
		$this->setting = $this->get_setting();
		$sections      = $this->register_sections();
		$this->fields  = $this->register_fields();
		if ( ! class_exists( 'SixTenPress' ) ) {
			$this->page = $this->tab;
			add_options_page(
				__( '6/10 Press Isotope Settings', 'sixtenpress-isotope' ),
				__( '6/10 Press Isotope', 'sixtenpress-isotope' ),
				'manage_options',
				$this->page,
				array( $this, 'do_settings_form' )
			);
		}

		add_filter( 'sixtenpress_settings_tabs', array( $this, 'add_tab' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		$help = new SixTenPressIsotopeHelp();
		if ( class_exists( 'SixTenPress' ) ) {
			add_filter( 'sixtenpress_help_tabs', array( $help, 'tabs' ), 10, 2 );
		} else {
			add_action( "load-settings_page_{$this->page}", array( $help, 'help' ) );
		}

		$this->add_sections( $sections );
		$this->add_fields( $this->fields, $sections );
	}

	/**
	 * Output the plugin settings form.
	 *
	 * @since 1.0.0
	 */
	public function do_settings_form() {

		echo '<div class="wrap">';
		echo '<h1>' . esc_attr( get_admin_page_title() ) . '</h1>';
		echo '<form action="options.php" method="post">';
		settings_fields( $this->page );
		do_settings_sections( $this->page );
		wp_nonce_field( "{$this->page}_save-settings", "{$this->page}_nonce", false );
		submit_button();
		echo '</form>';
		echo '</div>';

	}

	/**
	 * Add isotope settings to 6/10 Press as a new tab, rather than creating a unique page.
	 * @param $tabs
	 *
	 * @return array
	 */
	public function add_tab( $tabs ) {
		$tabs[] = array( 'id' => 'isotope', 'tab' => __( 'Isotope', 'sixtenpress-isotope' ) );

		return $tabs;
	}

	/**
	 * Add new fields to wp-admin/options-general.php?page=sixtenpressisotope
	 *
	 * @since 1.0.0
	 */
	public function register_settings() {
		register_setting( 'sixtenpressisotope', 'sixtenpressisotope', array( $this, 'do_validation_things' ) );
	}

	/**
	 * @return array $setting for plugin, or defaults.
	 */
	public function get_setting() {

		$defaults = array(
			'posts_per_page' => (int) get_option( 'posts_per_page', 10 ),
			'style'          => 1,
			'image_size'     => 'default',
			'alignment'      => 'default',
			'remove'         => array(
				'content' => 1,
				'before'  => 0,
				'after'   => 0,
			),
			'infinite'       => 0,
		);

		$setting = get_option( 'sixtenpressisotope', $defaults );

		return wp_parse_args( $setting, $defaults );
	}

	/**
	 * Define the array of post types for the plugin to show/use.
	 * @return array
	 */
	protected function post_types() {
		$args         = array(
			'public'      => true,
			'_builtin'    => false,
			'has_archive' => true,
		);
		$output       = 'names';
		$post_types   = get_post_types( $args, $output );
		$post_types[] = 'post';

		return $post_types;
	}

	/**
	 * Register sections for settings page.
	 *
	 * @since 3.0.0
	 */
	protected function register_sections() {

		$sections = array(
			'general' => array(
				'id'    => 'general',
				'tab'   => 'isotope',
				'title' => __( 'General Settings', 'sixtenpress-isotope' ),
			),
		);

		$this->post_types = $this->post_types();
		if ( $this->post_types ) {

			$sections['cpt'] = array(
				'id'    => 'cpt',
				'tab'   => 'isotope',
				'title' => __( 'Isotope Settings for Content Types', 'sixtenpress-isotope' ),
			);
		}

		return $sections;
	}

	/**
	 * Register settings fields
	 *
	 * @param  settings array $sections
	 *
	 * @return array $fields settings fields
	 *
	 * @since 1.0.0
	 */
	protected function register_fields() {

		$fields = array(
			array(
				'id'       => 'posts_per_page',
				'title'    => __( 'Number of Posts to Show on Isotope Archives', 'sixtenpress-isotope' ),
				'callback' => 'do_number',
				'section'  => 'general',
				'args'     => array(
					'setting' => 'posts_per_page',
					'min'     => 1,
					'max'     => 200,
					'label'   => __( 'Posts per Page', 'sixtenpress-isotope' ),
				),
			),
			array(
				'id'       => 'infinite',
				'title'    => __( 'Infinite Scroll', 'sixtenpress-isotope' ),
				'callback' => 'do_checkbox',
				'section'  => 'general',
				'args'     => array(
					'setting' => 'infinite',
					'label'   => __( 'Enable infinite scroll?', 'sixtenpress-isotope' ),
				),
			),
			array(
				'id'       => 'style',
				'title'    => __( 'Plugin Stylesheet', 'sixtenpress-isotope' ),
				'callback' => 'do_checkbox',
				'section'  => 'general',
				'args'     => array(
					'setting' => 'style',
					'label'   => __( 'Use the plugin styles?', 'sixtenpress-isotope' ),
				),
			),
			array(
				'id'       => 'image_size',
				'title'    => __( 'Featured Image Size', 'sixtenpress-isotope' ),
				'callback' => 'do_select',
				'section'  => 'general',
				'args'     => array(
					'setting' => 'image_size',
					'options' => 'sizes',
				),
			),
			array(
				'id'       => 'alignment',
				'title'    => __( 'Featured Image Alignment', 'sixtenpress-isotope' ),
				'callback' => 'do_select',
				'section'  => 'general',
				'args'     => array(
					'setting' => 'alignment',
					'options' => 'alignment',
				),
			),
			array(
				'id'       => 'remove',
				'title'    => __( 'Remove Entry Elements', 'sixtenpress-isotope' ),
				'callback' => 'do_checkbox_array',
				'section'  => 'general',
				'args'     => array(
					'setting' => 'remove',
					'choices' => array(
						'content' => __( 'Remove Entry Content', 'sixtenpress-isotope' ),
						'before' => __( 'Remove Entry Info', 'sixtenpress-isotope' ),
						'after' => __( 'Remove Entry Meta', 'sixtenpress-isotope' ),
					),
				),
			),
		);
		if ( $this->post_types ) {
			foreach ( $this->post_types as $post_type ) {
				$object   = get_post_type_object( $post_type );
				$label    = $object->labels->name;
				$fields[] = array(
					'id'       => '[post_types]' . esc_attr( $post_type ),
					'title'    => esc_attr( $label ),
					'callback' => 'set_post_type_options',
					'section'  => 'cpt',
					'args'     => array( 'post_type' => $post_type ),
				);
			}
		}

		return $fields;
	}

	/**
	 * Callback for general plugin settings section.
	 */
	public function general_section_description() {
		$description = __( 'You can set the default isotope settings here.', 'sixtenpress-isotope' );
		printf( '<p>%s</p>', wp_kses_post( $description ) );
	}

	/**
	 * Callback for the content types section description.
	 */
	public function cpt_section_description() {
		$description = __( 'Set the isotope settings for each content type.', 'sixtenpress-isotope' );
		printf( '<p>%s</p>', wp_kses_post( $description ) );
	}

	/**
	 * Callback to populate the thumbnail size dropdown with available image sizes.
	 * @return array selected sizes with names and dimensions
	 *
	 */
	protected function pick_sizes() {
		$options['default'] = __( 'Theme Default', 'sixtenpress-isotope' );
		$intermediate_sizes = get_intermediate_image_sizes();
		foreach ( $intermediate_sizes as $_size ) {
			$default_sizes = apply_filters( 'sixtenpressisotope_thumbnail_size_list', array( 'thumbnail', 'medium' ) );
			if ( in_array( $_size, $default_sizes, true ) ) {
				$width           = get_option( $_size . '_size_w' );
				$height          = get_option( $_size . '_size_h' );
				$options[$_size] = sprintf( '%s ( %sx%s )', $_size, $width, $height );
			}
		}

		return $options;
	}

	/**
	 * Callback to create a dropdown list for featured image alignment.
	 * @return array list of alignment choices.
	 *
	 */
	protected function pick_alignment() {
		$options = array(
			'default'      => __( 'Theme Default', 'sixtenpress-isotope' ),
			'alignleft'    => __( 'Left', 'sixtenpress-isotope' ),
			'alignright'   => __( 'Right', 'sixtenpress-isotope' ),
			'aligncenter'  => __( 'Center', 'sixtenpress-isotope' ),
			'alignnone'    => __( 'None', 'sixtenpress-isotope' ),
		);

		return $options;
	}

	/**
	 * Set the field for each post type.
	 * @param $args
	 */
	public function set_post_type_options( $args ) {
		$post_type = $args['post_type'];
		$checkbox_args = array(
			'setting' => 'support',
			'label'   => __( 'Enable Isotope for this post type?', 'sixtenpress-isotope' ),
			'key'     => $post_type,
		);
		$this->do_checkbox( $checkbox_args );
		echo '<br />';
		$gutter_args = array(
			'setting' => 'gutter',
			'min'     => 0,
			'max'     => 24,
			'value'   => __( 'Gutter Width', 'sixtenpress-isotope' ),
			'key'     => $post_type,
		);
		$this->do_number( $gutter_args );
		echo '<br />';
		$taxonomies = $this->get_taxonomies( $post_type );
		if ( ! $taxonomies ) {
			return;
		}
		foreach ( $taxonomies as $taxonomy ) {
			$tax_object = get_taxonomy( $taxonomy );
			$tax_args   = array(
				'setting' => $taxonomy,
				'label'   => sprintf( __( 'Add a filter for %s', 'sixtenpress-isotope' ), $tax_object->labels->name ),
				'key'     => $post_type,
			);
			$this->do_checkbox( $tax_args );
			echo '<br />';
		}
	}

	/**
	 * Get the taxonomies registered to a post type.
	 * @param $post_type
	 *
	 * @return array
	 */
	protected function get_taxonomies( $post_type ) {
		$taxonomies = get_object_taxonomies( $post_type, 'names' );
		return 'post' === $post_type ? array( 'category' ) : $taxonomies;
	}

	public function infinite_description() {
		return __( 'Combine infinite scroll with filtering with great caution.', 'sixtenpress-isotope' );
	}

	/**
	 * Validate all settings.
	 *
	 * @param  array $new_value new values from settings page
	 *
	 * @return array            validated values
	 *
	 * @since 1.0.0
	 */
	public function do_validation_things( $new_value ) {

		$action = $this->page . '_save-settings';
		$nonce  = $this->page . '_nonce';
		// If the user doesn't have permission to save, then display an error message
		if ( ! $this->user_can_save( $action, $nonce ) ) {
			wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'sixtenpress' ) );
		}

		check_admin_referer( "{$this->page}_save-settings", "{$this->page}_nonce" );
		$diff = array_diff_key( $this->setting, $new_value );
		foreach ( $diff as $key => $value ) {
			if ( empty( $new_value[ $key ] ) ) {
				unset( $this->setting[ $key ] );
			}
		}
		$new_value = array_merge( $this->setting, $new_value );

		foreach ( $this->fields as $field ) {
			switch ( $field['callback'] ) {
				case 'do_checkbox':
					$new_value[ $field['id'] ] = $this->one_zero( $new_value[ $field['id'] ] );
					break;

				case 'do_select':
					$new_value[ $field['id'] ] = esc_attr( $new_value[ $field['id'] ] );
					break;

				case 'do_number':
					$new_value[ $field['id'] ] = (int) $new_value[ $field['id'] ];
					break;

				case 'do_checkbox_array':
					foreach ( $field['args']['options'] as $option ) {
						$new_value[ $field['id'] ][ $option['choice'] ] = $this->one_zero( $new_value[ $field['id'] ][ $option['choice'] ] );
					}
					break;
			}
		}
		foreach ( $this->post_types as $post_type ) {
			$new_value[ $post_type ]['support'] = $this->one_zero( $new_value[ $post_type ]['support'] );
			$new_value[ $post_type ]['gutter']  = (int) $new_value[ $post_type ]['gutter'];
			$taxonomies = $this->get_taxonomies( $post_type );
			foreach ( $taxonomies as $taxonomy ) {
				$new_value[ $post_type ][ $taxonomy ] = $this->one_zero( $new_value[ $post_type ][ $taxonomy ] );
			}
		}

		return $new_value;
	}
}
