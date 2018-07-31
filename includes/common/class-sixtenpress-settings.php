<?php

if ( ! class_exists( 'SixTenPressField' ) ) {
	include dirname( __FILE__ ) . '/class-sixtenpress-field.php';
}
/**
 * @copyright 2016-2017 Robin Cornett
 * @package   SixTenPress
 */
class SixTenPressSettings extends SixTenPressField {

	/**
	 * The plugin settings page.
	 * @var string $page
	 */
	protected $page;

	/**
	 * The plugin setting.
	 * @var $setting
	 */
	protected $setting;

	/**
	 * The settings fields.
	 * @var $fields
	 */
	protected $fields = array();

	/**
	 * The settings page tab, if it exists.
	 * @var $tab
	 */
	protected $tab = null;

	/**
	 * @var
	 */
	protected $action;

	/**
	 * @var
	 */
	protected $nonce;

	/**
	 * @var array
	 */
	protected $data = array();

	/**
	 * @var string
	 */
	protected $version = '';

	/**
	 * @var \SixTenPressSettingsGetter
	 */
	protected $get;

	/**
	 * Check if 6/10 Press is active.
	 * @return bool
	 */
	protected function is_sixten_active() {
		return (bool) function_exists( 'sixtenpress_get_setting' );
	}

	/**
	 * Set which tab is considered active.
	 * @return string
	 * @since 1.0.0
	 */
	protected function get_active_tab() {
		$tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );

		return $tab ? $tab : 'main';
	}

	/**
	 * Generic function to register a setting.
	 */
	public function register() {
		$setting = $this->get_setting_name();
		register_setting( $setting, $setting, array( $this, 'sanitize' ) );
	}

	/**
	 * Generic function to output a simple settings form.
	 *
	 * @since 1.0.0
	 */
	public function do_simple_settings_form() {

		echo '<div class="wrap">';
		echo '<h1>' . esc_attr( get_admin_page_title() ) . '</h1>';
		echo '<form action="options.php" method="post" class="sixtenpress-form">';
		settings_fields( $this->page );
		do_settings_sections( $this->page );
		wp_nonce_field( "{$this->page}_save-settings", "{$this->page}_nonce", false );
		submit_button();
		echo '</form>';
		echo '</div>';

	}

	/**
	 * Add sections to the settings page.
	 *
	 * @param $sections
	 */
	public function add_sections( $sections ) {
		$page = $this->page;
		foreach ( $sections as $section ) {
			$register = $section['id'];
			if ( class_exists( 'SixTenPress' ) && isset( $section['tab'] ) ) {
				$register = $this->page . '_' . $section['id'];
				$page     = $this->page . '_' . $section['tab'];
			}
			add_settings_section(
				$register,
				$section['title'],
				isset( $section['description'] ) && is_array( $section['description'] ) ? $section['description'] : array(
					$this,
					'section_description',
				),
				$page
			);
		}
	}

	/**
	 * Echo the section description.
	 *
	 * @param $args
	 */
	public function section_description( $args ) {
		$id     = str_replace( "{$this->page}_", '', $args['id'] );
		$method = "{$id}_section_description";
		if ( method_exists( $this, $method ) ) {
			echo wp_kses_post( wpautop( $this->$method() ) );
		}
	}

	/**
	 * Add the settings fields to the page.
	 *
	 * @param $fields   array
	 * @param $sections array
	 */
	public function add_fields( $fields, $sections ) {
		foreach ( $fields as $field ) {
			$page    = $this->page;
			$section = $sections[ $field['section'] ]['id'];
			if ( class_exists( 'SixTenPress' ) && isset( $sections[ $field['section'] ]['tab'] ) ) {
				$page    = $this->page . '_' . $sections[ $field['section'] ]['tab']; // page
				$section = $this->page . '_' . $sections[ $field['section'] ]['id']; // section
			}
			$defaults = array(
				'callback' => 'do_field',
				'args'     => array(),
				'type'     => '',
			);
			$field    = wp_parse_args( $field, $defaults );
			$callback = is_array( $field['callback'] ) ? $field['callback'] : array( $this, $field['callback'] );
			$label    = 'checkbox' === $field['type'] ? $field['title'] : sprintf( '<label for="%s-%s">%s</label>', $this->tab ? $this->tab : $this->page, $field['id'], $field['title'] );
			add_settings_field(
				$field['id'],
				$label,
				$callback,
				$page,
				$section,
				wp_parse_args( $field, $field['args'] )
			);
		}
	}

	/**
	 * Generic function to pick the view file to output the field.
	 *
	 * @param $args
	 */
	public function do_field( $args ) {
		$defaults = array(
			'key'         => false,
			'type'        => '',
			'callback'    => '',
			'required'    => false,
			'description' => '',
			'clear'       => true,
		);
		$args = wp_parse_args( $args, $defaults );
		if ( isset( $args['value'] ) ) {
			$args['label'] = $args['value'];
		}
		$getter      = $this->get_getter();
		$name        = $getter->get_field_name( $args );
		$id          = $getter->get_field_id( $args );
		$value       = $getter->get_field_value( $args );
		$method      = "do_field_{$args['type']}";
		$needs_class = in_array( $args['type'], array( 'checkbox_array' ), true );
		if ( method_exists( $this, $method ) ) {
			if ( $needs_class ) {
				$class = $args['clear'] ? 'block' : 'inline';
				printf( '<div class="%s">', esc_attr( $class ) );
			}
			$this->$method( $name, $id, $value, $args );
			if ( $needs_class ) {
				echo '</div>';
			}
		} elseif ( method_exists( $this, $args['callback'] ) && 'do_field' !== $args['callback'] ) {
			$this->{$args['callback']}( $args );
		}
		$this->do_description( $args );
	}

	/**
	 * Get the current setting key.
	 * @return null|string
	 */
	protected function get_setting_name() {
		return $this->tab && $this->page !== $this->tab ? $this->tab : $this->page;
	}

	/**
	 * Enqueue our admin scripts.
	 */
	public function enqueue_all() {
		$enqueue = $this->include_file( 'fields-enqueue' );
		if ( ! $enqueue ) {
			return;
		}
		$enqueue_class = new SixTenPressFieldsEnqueue(
			$this->_get()
		);
		$enqueue_class->enqueue();
		add_action( 'admin_footer', array( $this, 'localize' ) );
	}

	/**
	 * Helper function to include a class file. Returns bool so successful load can be checked.
	 *
	 * @param $file
	 *
	 * @return bool
	 */
	protected function include_file( $file ) {
		$path = trailingslashit( plugin_dir_path( __FILE__ ) ) . "class-sixtenpress-{$file}.php";
		if ( ! file_exists( $path ) ) {
			return false;
		}
		if ( ! class_exists( 'SixTenPressAutoloader' ) ) {
			include_once $path;
		}

		return true;
	}

	/**
	 * Add a submit button.
	 *
	 * @param $class
	 * @param $name
	 * @param $value
	 */
	protected function print_button( $class, $name, $value ) {
		printf( '<input type="submit" class="%s" name="%s" value="%s"/>',
			esc_attr( $class ),
			esc_attr( $name ),
			esc_attr( $value )
		);
	}

	/**
	 * Image field.
	 *
	 * @since  1.0.0
	 *
	 * @param $args
	 */
	public function do_image( $args ) {
		_deprecated_function( __FUNCTION__, '2.0.0' );

		$id = $this->setting[ $args['id'] ];
		if ( ! empty( $id ) ) {
			echo wp_kses_post( $this->render_image_preview( $id ) );
		}
		$this->render_buttons( $id, $args );
	}

	/**
	 * show image select/delete buttons
	 *
	 * @param  int $image_id image ID
	 * @param  $args         array
	 *
	 * @since 2.3.0
	 */
	public function render_buttons( $image_id, $args ) {
		$image_id                  = $image_id ? (int) $image_id : '';
		$getter                    = $this->get_getter();
		$id                        = $getter->get_field_id( $args );
		$name                      = $getter->get_field_name( $args );
		add_filter( 'sixtenpress_uploader_localization', function( $data ) use ( $args ) {
			return array_merge( $data, array(
				$args['id'] => $this->get_localization_data( $args ),
			) );
		} );
		printf( '<input type="hidden" class="upload-file-id" id="%1$s" name="%3$s" value="%2$s" />',
			esc_attr( $id ),
			esc_attr( $image_id ),
			esc_attr( $name )
		);
		printf( '<button id="%1$s-button" class="upload-file button-secondary %2$s">%3$s</button>',
			esc_attr( $id ),
			esc_attr( $args['id'] ),
			esc_attr__( 'Select Image', 'sixtenpress' )
		);
		printf( ' <button class="delete-file button-secondary" style="%s">%s</button>',
			esc_attr( $image_id ? '' : 'display:none;' ),
			esc_attr__( 'Delete Image', 'sixtenpress' )
		);
	}

	/**
	 * Render image preview
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public function render_image_preview( $id ) {
		if ( empty( $id ) ) {
			return '';
		}

		$preview = wp_get_attachment_image_src( (int) $id, 'medium' );
		$image   = sprintf( '<div class="upload-file-preview"><img src="%s" style="max-width:320px;" /></div>', $preview[0] );

		return $image;
	}

	/**
	 * Custom callback for a multidimensional image size setting.
	 *
	 * @param $args
	 */
	public function do_image_size( $args ) {
		$settings = array(
			array(
				'key'     => $args['id'],
				'setting' => 'width',
				'min'     => 100,
				'max'     => 2000,
				'value'   => ' ' . __( 'width', 'sixtenpress' ) . ' ',
				'type'    => 'number',
			),
			array(
				'key'     => $args['id'],
				'setting' => 'height',
				'min'     => 100,
				'max'     => 2000,
				'value'   => ' ' . __( 'height', 'sixtenpress' ) . ' ',
				'type'    => 'number',
			),
		);
		foreach ( $settings as $setting ) {
			$this->do_field( $setting );
		}
	}

	/**
	 * @return \SixTenPressSettingsGetter
	 */
	protected function get_getter() {
		if ( isset( $this->get ) ) {
			return $this->get;
		}
		$this->include_file( 'settings-getter' );
		$this->get = new SixTenPressSettingsGetter(
			$this->get_setting_name(),
			$this->setting
		);

		return $this->get;
	}

	/**
	 * Set color for an element
	 *
	 * @param $args array set any color for a setting
	 *
	 * @since 1.3.0
	 */
	public function set_color( $args ) {
		$this->do_field( array_merge( $args, array(
			'type' => 'color',
		) ) );
	}

	/**
	 * Generic callback to create a checkbox setting.
	 *
	 * @since 1.0.0
	 */
	public function do_checkbox( $args ) {
		$this->do_field( array_merge( $args, array(
			'type' => 'checkbox',
		) ) );
	}

	/**
	 * Generic callback to create a number field setting.
	 *
	 * @since 1.0.1
	 */
	public function do_number( $args ) {
		$this->do_field( array_merge( $args, array(
			'type'  => 'number',
			'class' => 'small-text',
		) ) );
	}

	/**
	 * Generic callback to create a select/dropdown setting.
	 *
	 * @since 2.0.0
	 */
	public function do_select( $args ) {
		$this->do_field( array_merge( $args, array(
			'type' => 'select',
		) ) );
	}

	/**
	 * Set up choices for checkbox array
	 *
	 * @param $args array
	 */
	public function do_checkbox_array( $args ) {
		$this->do_field( array_merge( $args, array(
			'type' => 'checkbox_array',
		) ) );
	}

	/**
	 * Output a text field setting.
	 *
	 * @param $args
	 */
	public function do_text_field( $args ) {
		$this->do_field( array_merge( $args, array(
			'type' => 'text',
		) ) );
	}

	/**
	 * Output a textarea setting.
	 *
	 * @param $args
	 */
	public function do_textarea( $args ) {
		$this->do_field( array_merge( $args, array(
			'type' => 'textarea',
		) ) );
	}

	/**
	 * Default method for getting setting.
	 * @return array
	 */
	protected function get_setting() {
		return get_option( $this->tab, array() );
	}

	/**
	 * Default method for getting fields. Should be overridden.
	 * @return array
	 */
	protected function register_fields() {
		return array();
	}

	/**
	 * Generic callback to display a field description.
	 *
	 * @param  array $args setting name used to identify description callback
	 *
	 * @since 1.0.0
	 */
	protected function do_description( $args ) {
		$description = isset( $args['description'] ) && $args['description'] ? $args['description'] : '';
		$function    = isset( $args['id'] ) ? $args['id'] . '_description' : '';
		if ( ! $description && method_exists( $this, $function ) ) {
			$description = $this->$function();
		}
		if ( ! $description ) {
			return;
		}
		printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
	}

	/**
	 * @param $args
	 * @param $id
	 */
	protected function do_field_label( $args, $id ) {
		if ( ! isset( $args['label'] ) ) {
			return;
		}
		printf( '<label for="%s">%s</label>', esc_attr( $id ), esc_html( $args['label'] ) );
	}

	/**
	 * Helper function to get a list of posts.
	 *
	 * @param $post_type
	 * @param string $term
	 * @param string $taxonomy
	 *
	 * @return array
	 */
	protected function get_post_list( $post_type, $term = '', $taxonomy = '' ) {
		$options[''] = '--';
		if ( ! post_type_exists( $post_type ) ) {
			return $options;
		}
		$args = array(
			'post_type'      => $post_type,
			'posts_per_page' => 100,
			'orderby'        => 'title',
			'order'          => 'ASC',
		);
		if ( $term && $taxonomy && term_exists( $term, $taxonomy ) ) {
			$args = array_merge( $args, array(
				'tax_query' => array(
					array(
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $term,
					),
				),
			) );
		}
		$posts = get_posts( $args );
		foreach ( $posts as $post ) {
			$options[ $post->ID ] = get_the_title( $post );
		}

		return apply_filters( 'sixtenpress_related_posts', $options, $args, $post_type, $term, $taxonomy );
	}

	/**
	 * Add necessary js to the settings page (media uploader).
	 * @since 1.0.0
	 */
	public function enqueue_uploader() {
		if ( ! wp_script_is( 'sixtenpress-upload', 'registered' ) ) {
			wp_register_script( 'sixtenpress-upload', plugins_url( '/js/file-upload.js', dirname( __FILE__ ) ), array(
				'jquery',
				'media-upload',
				'thickbox',
			), $this->version, true );
		}
		wp_enqueue_media();
		wp_enqueue_script( 'sixtenpress-upload' );
		add_action( 'admin_footer', array( $this, 'localize' ) );
	}

	/**
	 * Localize data for our javascript.
	 */
	public function localize() {
		$data = apply_filters( 'sixtenpress_uploader_localization', array() );
		if ( empty( $data ) ) {
			return;
		}
		$args = array(
			'class' => 'file',
		);
		wp_localize_script( 'sixtenpress-upload', 'SixTenUpload', wp_parse_args( $data, $args ) );
	}

	/**
	 * Getter method for getting all the fields.
	 * @return array
	 */
	protected function _get() {
		if ( $this->fields ) {
			return $this->fields;
		}
		$this->fields = $this->register_fields();

		return $this->fields;
	}

	/**
	 * Default settings validation method.
	 *
	 * @param $new_value
	 *
	 * @return array
	 */
	public function sanitize( $new_value ) {

		// If the user doesn't have permission to save, then display an error message
		if ( ! $this->user_can_save( $this->action, $this->nonce ) ) {
			wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'sixtenpress' ) );
		}

		check_admin_referer( $this->action, $this->nonce );
		$this->include_file( 'settings-sanitize' );
		$sanitize = new SixTenPressSettingsSanitize( $this->_get(), $this->get_setting(), $this->page, $this->get_setting_name() );

		return $sanitize->sanitize( $new_value );
	}

	/**
	 * Determines if the user has permission to save the information from the submenu
	 * page.
	 *
	 * @since    1.0.0
	 * @access   protected
	 *
	 * @param    string $action The name of the action specified on the submenu page
	 * @param    string $nonce  The nonce specified on the submenu page
	 *
	 * @return   bool                True if the user has permission to save; false, otherwise.
	 * @author   Tom McFarlin (https://tommcfarlin.com/save-wordpress-submenu-page-options/)
	 */
	protected function user_can_save( $action, $nonce ) {
		$is_nonce_set   = isset( $_POST[ $nonce ] );
		$is_valid_nonce = false;

		if ( $is_nonce_set ) {
			$is_valid_nonce = wp_verify_nonce( $_POST[ $nonce ], $action );
		}

		return ( $is_nonce_set && $is_valid_nonce );
	}

	/**
	 * Takes an array of new settings, merges them with the old settings, and pushes them into the database.
	 *
	 * @since 2.0.0
	 *
	 * @param string|array $new New settings. Can be a string, or an array.
	 * @param string $setting   Optional. Settings field name. Default is sixtenpress.
	 */
	protected function update_settings( $new = '', $setting = 'sixtenpress' ) {
		return update_option( $setting, wp_parse_args( $new, get_option( $setting ) ) );
	}

	/**
	 * Returns a 1 or 0, for all truthy / falsy values.
	 *
	 * Uses double casting. First, we cast to bool, then to integer.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $new_value Should ideally be a 1 or 0 integer passed in
	 *
	 * @return integer 1 or 0.
	 */
	protected function one_zero( $new_value ) {
		return (int) (bool) $new_value;
	}
}
