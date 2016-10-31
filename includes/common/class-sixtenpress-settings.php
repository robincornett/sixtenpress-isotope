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
	 * Add the settings fields to the page.
	 * @param $fields array
	 * @param $sections array
	 */
	public function add_fields( $fields, $sections ) {
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
	 * show image select/delete buttons
	 * @param  int $id   image ID
	 * @param  string $name name for value/ID/class
	 * @return $buttons       select/delete image buttons
	 *
	 * @since 2.3.0
	 */
	public function render_buttons( $id, $name ) {
		$id = $id ? (int) $id : '';
		printf( '<input type="hidden" class="upload_image_id" id="%1$s" name="%1$s" value="%2$s" />', esc_attr( $name ), esc_attr( $id ) );
		printf( '<input id="%s" type="button" class="upload_image button-secondary" value="%s" />',
			esc_attr( $name ),
			esc_attr__( 'Select Image', 'sixtenpress' )
		);
		if ( ! empty( $id ) ) {
			printf( ' <input type="button" class="delete_image button-secondary" value="%s" />',
				esc_attr__( 'Delete Image', 'sixtenpress' )
			);
		}
	}

	/**
	 * Render image preview
	 * @param $id
	 *
	 * @return string|void
	 */
	public function render_image_preview( $id ) {
		if ( empty( $id ) ) {
			return '';
		}

		$preview = wp_get_attachment_image_src( (int) $id, 'medium' );
		$image   = sprintf( '<div class="upload_image_preview"><img src="%s" style="max-width:320px;" /></div>', $preview[0] );
		return $image;
	}

	/**
	 * Set color for an element
	 * @param $args array set any color for a setting
	 *
	 * @since 1.3.0
	 */
	public function set_color( $args ) {
		printf( '<input type="text" name="%3$s[%1$s]" value="%2$s" class="color-field">',
			esc_attr( $args['setting'] ),
			esc_attr( $this->setting[$args['setting'] ] ),
			esc_attr( $this->get_setting_name() )
		);
		$this->do_description( $args['setting'] );
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
		printf( '<input type="hidden" name="%s[%s]" value="0" />', esc_attr( $this->get_setting_name() ), esc_attr( $label ) );
		printf( '<label for="%1$s[%2$s]" %5$s><input type="checkbox" name="%1$s[%2$s]" id="%1$s[%2$s]" value="1" %3$s class="code" />%4$s</label>',
			esc_attr( $this->get_setting_name() ),
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
	 * Generic callback to create a number field setting.
	 *
	 * @since 1.0.1
	 */
	public function do_number( $args ) {
		$setting = isset( $this->setting[ $args['setting'] ] ) ? $this->setting[ $args['setting'] ] : 0;
		$label   = $args['setting'];
		if ( isset( $args['key'] ) ) {
			$setting = isset( $this->setting[ $args['key'] ][ $args['setting'] ] ) ? $this->setting[ $args['key'] ][ $args['setting'] ] : 0;
			$label   = "{$args['key']}][{$args['setting']}";
		}
		printf( '<label for="%5$s[%3$s]"><input type="number" step="%6$s" min="%1$s" max="%2$s" id="%5$s[%3$s]" name="%5$s[%3$s]" value="%4$s" class="small-text" />%7$s</label>',
			$args['min'],
			(int) $args['max'],
			esc_attr( $label ),
			esc_attr( $setting ),
			esc_attr( $this->get_setting_name() ),
			isset( $args['step'] ) ? esc_attr( $args['step'] ) : (int) 1,
			isset( $args['value'] ) ? esc_attr( $args['value'] ) : ''
		);
		$this->do_description( $args['setting'] );
	}

	/**
	 * Generic callback to create a select/dropdown setting.
	 *
	 * @since 2.0.0
	 */
	public function do_select( $args ) {
		$function = 'pick_' . $args['options'];
		$options  = $this->$function();
		$array    = $this->get_select_setting( $args );
		$setting  = $array['setting'];
		$label    = $array['label'];
		printf( '<label for="%s[%s]">', esc_attr( $this->get_setting_name() ), esc_attr( $label ) );
		printf( '<select id="%1$s[%2$s]" name="%1$s[%2$s]">', esc_attr( $this->get_setting_name() ), esc_attr( $label ) );
		foreach ( (array) $options as $name => $key ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $name ), selected( $name, $setting, false ), esc_attr( $key ) );
		}
		echo '</select></label>';
		$this->do_description( $args['setting'] );
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
		foreach ( $args['choices'] as $key => $label ) {
			$setting = isset( $this->setting[ $args['setting'] ][ $key ] ) ? $this->setting[ $args['setting'] ][ $key ] : 0;
			printf( '<input type="hidden" name="%s[%s][%s]" value="0" />', esc_attr( $this->get_setting_name() ), esc_attr( $args['setting'] ), esc_attr( $key ) );
			printf( '<label for="%4$s[%5$s][%1$s]" style="margin-right:12px;"><input type="checkbox" name="%4$s[%5$s][%1$s]" id="%4$s[%5$s][%1$s]" value="1"%2$s class="code"/>%3$s</label>',
				esc_attr( $key ),
				checked( 1, $setting, false ),
				esc_html( $label ),
				esc_attr( $this->get_setting_name() ),
				esc_attr( $args['setting'] )
			);
			echo isset( $args['clear'] ) && $args['clear'] ? '<br />' : '';
		}
		$this->do_description( $args['setting'] );
	}

	/**
	 * Output a text field setting.
	 * @param $args
	 */
	public function do_text_field( $args ) {
		printf( '<input type="text" id="%3$s[%1$s]" aria-label="%3$s[%1$s]" name="%3$s[%1$s]" value="%2$s" class="regular-text" />',
			esc_attr( $args['setting'] ),
			esc_attr( $this->setting[ $args['setting'] ] ),
			esc_attr( $this->page ) );
		$this->do_description( $args['setting'] );
	}

	/**
	 * Output a textarea setting.
	 * @param $args
	 */
	public function do_textarea( $args ) {
		$rows = isset( $args['rows'] ) ? $args['rows'] : 3;
		printf( '<textarea class="large-text" rows="%3$s" id="%1$s[%2$s]" name="%1$s[%2$s]">%3$s</textarea>', $this->page, $args['setting'], $this->setting[ $args['setting'] ], (int) $rows );
		printf( '<br /><label for="%1$s[%2$s]">%3$s</label>', $this->page, $args['setting'], $args['label'] );
	}

	/**
	 * Generic callback to display a field description.
	 * @param  string $args setting name used to identify description callback
	 * @return string       Description to explain a field.
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

	/**
	 * Returns previous value for image if not correct file type/size
	 * @param  string $new_value New value
	 * @return string            New or previous value, depending on allowed image size.
	 * @since  1.0.0
	 */
	protected function validate_image( $new_value, $old_value, $label ) {

		// ok for field to be empty
		if ( ! $new_value ) {
			return '';
		}

		$source = wp_get_attachment_image_src( $new_value, 'full' );
		$valid  = $this->is_valid_img_ext( $source[0] );
		$reset  = sprintf( __( ' The %s has been reset to the last valid setting.', 'sixtenpress' ), $label );

		if ( $valid ) {
			return (int) $new_value;
		}

		$new_value = $old_value;
		if ( ! $valid ) {
			$message = __( 'Sorry, that is an invalid file type.', 'sixtenpress' );
			$class   = 'invalid';

			add_settings_error(
				$old_value,
				esc_attr( $class ),
				esc_attr( $message . $reset ),
				'error'
			);
		}

		return (int) $new_value;
	}

	/**
	 * Checks if the image is valid.
	 */
	protected function is_valid_img_ext( $file ) {
		$valid = wp_check_filetype( $file );
		return (bool) in_array( $valid['ext'], $this->allowed_file_types(), true );
	}

	/**
	 * Define the array of allowed image/file types.
	 * @return mixed|void array
	 * @since 1.0.0
	 */
	protected function allowed_file_types() {
		$allowed = apply_filters( 'sixtenpress_valid_img_types', array( 'jpg', 'jpeg', 'png', 'gif', 'svg' ) );
		return is_array( $allowed ) ? $allowed : explode( ',', $allowed );
	}

	/**
	 * Function that will check if value is a valid HEX color.
	 *
	 * @since 1.0.0
	 */
	protected function is_color( $new_value, $old_value, $title ) {

		if ( empty( $new_value ) ) {
			return $new_value;
		}

		$new_value = trim( $new_value );
		$new_value = strip_tags( stripslashes( $new_value ) );

		$hex_color = '/^#[a-f0-9]{6}$/i';
		if ( preg_match( $hex_color, $new_value ) ) {
			return $new_value;
		}

		$message = sprintf( __( 'Well, that was unexpected. The %s has been reset to the last valid setting; the value you entered didn\'t work.', 'superside-me' ), $title );

		add_settings_error(
			'color',
			'not-updated',
			$message,
			'error'
		);
		return $old_value;
	}

	/**
	 * Returns a 1 or 0, for all truthy / falsy values.
	 *
	 * Uses double casting. First, we cast to bool, then to integer.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $new_value Should ideally be a 1 or 0 integer passed in
	 * @return integer 1 or 0.
	 */
	protected function one_zero( $new_value ) {
		return (int) (bool) $new_value;
	}

	/**
	 * Check the numeric value against the allowed range. If it's within the range, return it; otherwise, return the old value.
	 * @param $new_value int new submitted value
	 * @param $old_value int old setting value
	 * @param $min int minimum value
	 * @param $max int maximum value
	 *
	 * @return int
	 */
	protected function check_value( $new_value, $old_value, $min, $max ) {
		if ( $new_value >= $min && $new_value <= $max ) {
			return (int) $new_value;
		}
		return $old_value;
	}

	/**
	 * Takes an array of new settings, merges them with the old settings, and pushes them into the database.
	 *
	 * @since 2.0.0
	 *
	 * @param string|array $new     New settings. Can be a string, or an array.
	 * @param string       $setting Optional. Settings field name. Default is sixtenpress.
	 */
	protected function update_settings( $new = '', $setting = 'sixtenpress' ) {
		return update_option( $setting, wp_parse_args( $new, get_option( $setting ) ) );
	}
}
