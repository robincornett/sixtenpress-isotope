<?php
/**
 * @copyright 2016 Robin Cornett
 * @package SixTenPress
 */
class SixTenPressSettings {

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
	protected $fields;

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
	protected $version = '1.2.0';

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
		return isset( $_GET['tab'] ) ? $_GET['tab'] : 'main';
	}

	/**
	 * Generic function to output a simple settings form.
	 *
	 * @since 1.0.0
	 */
	public function do_simple_settings_form() {

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
	 * Add sections to the settings page.
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
				array( $this, 'section_description' ),
				$page
			);
		}
	}

	/**
	 * Echo the section description.
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
	 * @param $fields array
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
			$callback = isset( $field['type'] ) && method_exists( $this, "do_{$field['type']}" ) ? "do_{$field['type']}" : $field['callback'];
			$label    = sprintf( '<label for="%s[%s]">%s</label>', $this->tab ? $this->tab : $this->page, $field['id'], $field['title'] );
			add_settings_field(
				$field['id'],
				$label,
				array( $this, $callback ),
				$page,
				$section,
				empty( $field['args'] ) ? array() : $field['args']
			);
		}
	}

	/**
	 * Get the current setting key.
	 * @return null|string
	 */
	protected function get_setting_name() {
		return $this->tab && $this->page !== $this->tab ? $this->tab : $this->page;
	}

	/**
	 * Add a submit button.
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

		$id = $this->setting[ $args['setting'] ];
		if ( ! empty( $id ) ) {
			echo wp_kses_post( $this->render_image_preview( $id ) );
		}
		$this->render_buttons( $id, $args );
	}

	/**
	 * show image select/delete buttons
	 * @param  int $id   image ID
	 * @param  $args array
	 *
	 * @since 2.3.0
	 */
	public function render_buttons( $id, $args ) {
		$id = $id ? (int) $id : '';
		$this->data[ $args['setting'] ] = $this->get_localization_data( $args );
		printf( '<input type="hidden" class="upload-file-id" id="%1$s" name="%1$s" value="%2$s" />', esc_attr( $this->page . '[' . $args['setting'] . ']' ), esc_attr( $id ) );
		printf( '<input id="%1$s" type="button" class="upload-file button-secondary %2$s" value="%3$s" />',
			esc_attr( $this->page . '[' . $args['setting'] . ']' ),
			esc_attr( $args['setting'] ),
			esc_attr__( 'Select Image', 'sixtenpress' )
		);
		if ( ! empty( $id ) ) {
			printf( ' <input type="button" class="delete-file button-secondary" value="%s" />',
				esc_attr__( 'Delete Image', 'sixtenpress' )
			);
		}
	}

	/**
	 * Render image preview
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
	 * Get path for included files.
	 * @return string
	 */
	protected function path() {
		return trailingslashit( plugin_dir_path( __FILE__ ) . 'views' );
	}

	/**
	 * Set color for an element
	 * @param $args array set any color for a setting
	 *
	 * @since 1.3.0
	 */
	public function set_color( $args ) {
		include $this->path() . 'field_color.php';
	}

	/**
	 * Generic callback to create a checkbox setting.
	 *
	 * @since 1.0.0
	 */
	public function do_checkbox( $args ) {
		include $this->path() . 'field_checkbox.php';
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
	 * Generic callback to create a number field setting.
	 *
	 * @since 1.0.1
	 */
	public function do_number( $args ) {
		include $this->path() . 'field_number.php';
	}

	/**
	 * Generic callback to create a select/dropdown setting.
	 *
	 * @since 2.0.0
	 */
	public function do_select( $args ) {
		include $this->path() . 'field_select.php';
	}

	/**
	 * Get the setting and label for a select option. Includes support for a secondary/array select.
	 * @param $args
	 *
	 * @return array
	 */
	protected function get_select_setting( $args ) {
		$setting = isset( $this->setting[ $args['setting'] ] ) ? $this->setting[ $args['setting'] ] : 0;
		$label   = $args['setting'];
		if ( isset( $args['key'] ) ) {
			$setting = isset( $this->setting[ $args['key'] ][ $args['setting'] ] ) ? $this->setting[ $args['key'] ][ $args['setting'] ] : 0;
			$label   = "{$args['key']}][{$args['setting']}";
		}
		return array( 'setting' => $setting, 'label' => $label );
	}

	/**
	 * Set up choices for checkbox array
	 * @param $args array
	 */
	public function do_checkbox_array( $args ) {
		include $this->path() . 'field_checkbox_array.php';
	}

	/**
	 * Output a text field setting.
	 * @param $args
	 */
	public function do_text_field( $args ) {
		include $this->path() . 'field_text.php';
	}

	/**
	 * Output a textarea setting.
	 * @param $args
	 */
	public function do_textarea( $args ) {
		include $this->path() . 'field_textarea.php';
	}

	/**
	 * Default method for getting setting.
	 * @return array
	 */
	protected function get_setting() {
		return get_option( $this->page, array() );
	}

	/**
	 * Generic callback to display a field description.
	 * @param  string $args setting name used to identify description callback
	 *
	 * @since 1.0.0
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
	 * Build up the localization data.
	 * @param $args
	 *
	 * @return array
	 */
	protected function get_localization_data( $args ) {
		return array(
			'text' => sprintf( __( 'Select %s', 'sixtenpress' ), $args['label'] ),
			'type' => isset( $args['library'] ) ? $args['library'] : array( 'audio', 'video', 'image', 'application' ),
		);
	}

	/**
	 * Localize data for our javascript.
	 */
	public function localize() {
		if ( empty( $this->data ) ) {
			return;
		}
		$args = array(
			'class' => 'file',
		);
		wp_localize_script( 'sixtenpress-upload', 'SixTenUpload', array_merge( $this->data, $args ) );
	}

	/**
	 * Enqueue the color picker script.
	 */
	public function enqueue_color_picker() {
		wp_enqueue_style( 'wp-color-picker' );
		if ( function_exists( 'wp_add_inline_script' ) ) {
			wp_enqueue_script( 'wp-color-picker' );
			$code = '( function( $ ) { \'use strict\'; $( function() { $( \'.color-field\' ).wpColorPicker(); }); })( jQuery );';
			wp_add_inline_script( 'wp-color-picker', $code );
		} else {
			wp_enqueue_script( 'sixtenpress-color-picker', plugins_url( '/js/color-picker.js', __FILE__ ), array( 'wp-color-picker' ), $this->version, true );
		}
	}

	/**
	 * Default settings validation method.
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

		include_once plugin_dir_path( __FILE__ ) . 'class-sixtenpress-settings-sanitize.php';
		$sanitize = new SixTenPressSettingsSanitize( $this->fields, $this->get_setting(), $this->page );
		return $sanitize->sanitize( $new_value );
	}

	/**
	 * Determines if the user has permission to save the information from the submenu
	 * page.
	 *
	 * @since    1.0.0
	 * @access   protected
	 *
	 * @param    string    $action   The name of the action specified on the submenu page
	 * @param    string    $nonce    The nonce specified on the submenu page
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
}
