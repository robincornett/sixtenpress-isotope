<?php

/**
 * Class for adding a new settings page to the WordPress admin, under Settings.
 * @package   SixTenPressIsotope
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 * @copyright 2016 Robin Cornett Creative, LLC
 */
class SixTenPressIsotopeSettings extends SixTenPressIsotopeValidation {

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
		add_action( "load-settings_page_{$this->page}", array( $help, 'help' ) );

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
	 * Add the sections to the settings page.
	 * @param $sections
	 */
	protected function add_sections( $sections ) {
		foreach ( $sections as $section ) {
			$register = $section['id'];
			$page     = $this->page;
			if ( class_exists( 'SixTenPress' ) ) {
				$register = $this->page . '_' . $section['id'];
				$page     = $this->page . '_' . $section['tab'];
			}
			add_settings_section(
				$register,
				$section['title'],
				array( $this, $section['id'] . '_section_description' ),
				$page
			);
		}
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
					'options' => array(
						array(
							'choice' => 'content',
							'label'  => __( 'Remove Entry Content', 'sixtenpress-isotope' ),
						),
						array(
							'choice' => 'before',
							'label'  => __( 'Remove Entry Info', 'sixtenpress-isotope' ),
						),
						array(
							'choice' => 'after',
							'label'  => __( 'Remove Entry Meta', 'sixtenpress-isotope' ),
						),
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
	 * Add the fields to the settings page.
	 * @param $fields
	 * @param $sections
	 */
	protected function add_fields( $fields, $sections ) {
		foreach ( $fields as $field ) {
			$page    = $this->page;
			$section = $sections[ $field['section'] ]['id'];
			if ( class_exists( 'SixTenPress' ) ) {
				$page    = $this->page . '_' . $sections[ $field['section'] ]['tab']; // page
				$section = $this->page . '_' . $sections[ $field['section'] ]['id']; // section
			}
			add_settings_field(
				'[' . $field['id'] . ']',
				sprintf( '<label for="%s">%s</label>', $field['id'], $field['title'] ),
				array( $this, $field['callback'] ),
				$page,
				$section,
				empty( $field['args'] ) ? array() : $field['args']
			);
		}
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
	 * Generic callback to create a checkbox setting.
	 *
	 * @since 1.0.0
	 */
	public function do_checkbox( $args ) {
		$get_things = $this->get_checkbox_setting( $args );
		$label   = $get_things['label'];
		$setting = $get_things['setting'];
		$style   = isset( $args['style'] ) ? sprintf( 'style=%s', $args['style'] ) : '';
		printf( '<input type="hidden" name="%s[%s]" value="0" />', esc_attr( $this->tab ), esc_attr( $label ) );
		printf( '<label for="%1$s[%2$s]" %5$s><input type="checkbox" name="%1$s[%2$s]" id="%1$s[%2$s]" value="1" %3$s class="code" />%4$s</label>',
			esc_attr( $this->tab ),
			esc_attr( $label ),
			checked( 1, esc_attr( $setting ), false ),
			esc_attr( $args['label'] ),
			$style
		);
		$this->do_description( $args['setting'] );
	}

	/**
	 * Get the setting/label for the checkbox.
	 * @param $args array
	 *
	 * @return array {
	 *               $setting int the current setting
	 *               $label string label for the checkbox
	 * }
	 */
	protected function get_checkbox_setting( $args ) {
		$setting = isset( $this->setting[ $args['setting'] ] ) ? $this->setting[ $args['setting'] ] : 0;
		$label   = $args['setting'];
		if ( isset( $args['key'] ) ) {
			$setting = isset( $this->setting[ $args['key'] ][ $args['setting'] ] ) ? $this->setting[ $args['key'] ][ $args['setting'] ] : 0;
			$label   = "{$args['key']}][{$args['setting']}";
		}

		return array(
			'setting' => $setting,
			'label'   => $label,
		);
	}

	/**
	 * Output an array of checkboxes.
	 * @param $args array
	 */
	public function do_checkbox_array( $args ) {
		foreach ( $args['options'] as $option ) {
			$checkbox_args = array(
				'setting' => $option['choice'],
				'label'   => $option['label'],
				'style'   => 'margin-right:12px;',
				'key'     => $args['setting'],
			);
			$this->do_checkbox( $checkbox_args );
		}
		$this->do_description( $args['setting'] );
	}

	/**
	 * Generic callback to create a number field setting.
	 *
	 * @since 1.0.0
	 */
	public function do_number( $args ) {
		$setting = isset( $this->setting[ $args['setting'] ] ) ? $this->setting[ $args['setting'] ] : 0;
		if ( isset( $args['setting_name'] ) ) {
			if ( ! isset( $this->setting[ $args['post_type'] ] ) ) {
				$this->setting[ $args['post_type'] ] = array();
			}
			$setting = isset( $this->setting[ $args['post_type'] ][ $args['setting_name'] ] ) ? $this->setting[ $args['post_type'] ][ $args['setting_name'] ] : 0;
		}
		if ( ! isset( $setting ) ) {
			$setting = 0;
		}
		printf( '<label for="%s[%s]">%s</label>', esc_attr( $this->tab ), esc_attr( $args['setting'] ), esc_attr( $args['label'] ) );
		printf( '<input type="number" step="1" min="%1$s" max="%2$s" id="%5$s[%3$s]" name="%5$s[%3$s]" value="%4$s" class="small-text" />',
			(int) $args['min'],
			(int) $args['max'],
			esc_attr( $args['setting'] ),
			esc_attr( $setting ),
			esc_attr( $this->tab )
		);
		$this->do_description( $args['setting'] );

	}

	/**
	 * Generic callback to create a select/dropdown setting.
	 *
	 * @since 1.0.0
	 */
	public function do_select( $args ) {
		$function = 'pick_' . $args['options'];
		$options  = $this->$function();
		printf( '<label for="%s[%s]">', esc_attr( $this->tab ), esc_attr( $args['setting'] ) );
		printf( '<select id="%1$s[%2$s]" name="%1$s[%2$s]">', esc_attr( $this->tab ), esc_attr( $args['setting'] ) );
			foreach ( (array) $options as $name => $key ) {
				printf( '<option value="%s" %s>%s</option>', esc_attr( $name ), selected( $name, $this->setting[$args['setting']], false ), esc_attr( $key ) );
			}
		echo '</select></label>';
		$this->do_description( $args['setting'] );
	}

	/**
	 * Generic callback to create a text field.
	 *
	 * @since 1.0.0
	 */
	public function do_text_field( $args ) {
		printf( '<input type="text" id="%3$s[%1$s]" name="%3$s[%1$s]" value="%2$s" class="regular-text" />', esc_attr( $args['setting'] ), esc_attr( $this->setting[$args['setting']] ), esc_attr( $this->tab ) );
		$this->do_description( $args['setting'] );
	}

	/**
	 * Generic callback to display a field description.
	 *
	 * @param  string $args setting name used to identify description callback
	 *
	 * @return string       Description to explain a field.
	 */
	protected function do_description( $args ) {
		$function = $args . '_description';
		if ( ! method_exists( $this, $function ) ) {
			return;
		}
		$description = $this->$function();
		printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
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
		$setting_name = 'gutter';
		$gutter_args = array(
			'setting'      => "{$post_type}][{$setting_name}",
			'min'          => 0,
			'max'          => 24,
			'label'        => __( 'Gutter Width', 'sixtenpress-isotope' ),
			'post_type'    => $post_type,
			'setting_name' => $setting_name,
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
}
