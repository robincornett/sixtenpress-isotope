<?php
/**
 * @copyright 2016 Robin Cornett
 * @package SixTenPress
 */
class SixTenPressSettingsSanitize {

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
	 * The registered setting/option name.
	 * @var string
	 */
	protected $option;

	/**
	 * SixTenPressSettingsSanitize constructor.
	 *
	 * @param $fields
	 * @param $setting
	 * @param $page
	 * @param $option
	 */
	public function __construct( $fields, $setting, $page, $option ) {
		$this->fields  = $fields;
		$this->setting = $setting;
		$this->page    = $page;
		$this->option  = $option;
	}

	/**
	 * Default settings validation method.
	 * @param $new_value
	 *
	 * @return array
	 */
	public function sanitize( $new_value ) {

		$new_value = array_merge( $this->setting, $new_value );
		$old_value = $this->setting;
		if ( ! $this->fields ) {
			return $old_value;
		}

		foreach ( $this->fields as $field ) {
			$method                    = isset( $field['type'] ) ? 'sanitize_switcher' : 'sanitize_callback_switcher';
			$new_value[ $field['id'] ] = $this->$method( $new_value[ $field['id'] ], $field, $old_value[ $field['id'] ] );
			$new_value[ $field['id'] ] = apply_filters( "sixtenpress_sanitize_{$this->page}_{$field['id']}", $new_value[ $field['id'] ], $field, $new_value, $old_value[ $field['id'] ] );
			$new_value[ $field['id'] ] = apply_filters( "sixtenpress_sanitize_{$this->option}_{$field['id']}", $new_value[ $field['id'] ], $field, $new_value, $old_value[ $field['id'] ] );
		}

		do_action( "sixtenpress_after_sanitize_{$this->option}", $this->option, $new_value, $old_value );

		return $new_value;
	}

	/**
	 * Switch through field callbacks and sanitize accordingly.
	 * @param $new_value
	 * @param $field
	 * @param $old_value
	 *
	 * @return int|string
	 */
	protected function sanitize_switcher( $new_value, $field, $old_value ) {
		switch ( $field['type'] ) {
			case 'image':
				$new_value = $this->validate_image( $new_value, $old_value, $field['title'] );
				break;

			case 'color':
				$new_value = $this->is_color( $new_value, $old_value, $field['title'] );
				break;

			case 'checkbox':
				$new_value = $this->one_zero( $new_value );
				break;

			case 'number':
				$new_value = $this->check_value( $new_value, $old_value, $field['args']['min'], $field['args']['max'] );
				break;

			case 'select':
				$new_value = is_numeric( $new_value ) ? (int) $new_value : esc_attr( $new_value );
				break;

			case 'checkbox_array':
				$choices = isset( $field['choices'] ) ? $field['choices'] : $field['args']['choices'];
				foreach ( $choices as $key => $label ) {
					$new_value[ $key ] = $this->one_zero( $new_value[ $key ] );
				}
				break;

			case 'text':
				$new_value = sanitize_text_field( $new_value );
				break;

			case 'email':
				$new_value = $this->validate_email( $new_value, $old_value );
				break;

			case 'url':
				$new_value = esc_url( $new_value );
				break;

			case 'wysiwyg':
				$new_value = wp_kses_post( $new_value );
				break;

			case 'textarea':
				$new_value = sanitize_textarea_field( $new_value );
				break;

			case 'multidimensional':
				array_walk_recursive( $new_value, array( $this, 'validate_multidimensional' ) );
				break;

			default:
				$new_value = is_array( $new_value ) ? array_map( 'sanitize_text_field', $new_value ) : sanitize_text_field( $new_value );
				break;
		} // End switch().
		return $this->format_fields( $new_value, $field );
	}

	/**
	 * Validate multidimensional arrays.
	 * @param $new_value
	 * @param $key
	 */
	protected function validate_multidimensional( &$new_value, $key ) {
		$new_value = is_numeric( $new_value ) ? (int) $new_value : sanitize_text_field( $new_value );
	}

	/**
	 * Switch through field callbacks and sanitize accordingly.
	 *
	 * @param $new_value
	 * @param $field
	 * @param $old_value
	 *
	 * @return int|string
	 */
	protected function sanitize_callback_switcher( $new_value, $field, $old_value ) {
		switch ( $field['callback'] ) {
			case 'set_image':
				$new_value = $this->validate_image( $new_value, $old_value, $field['title'] );
				break;

			case 'set_color':
				$new_value = $this->is_color( $new_value, $old_value, $field['title'] );
				break;

			case 'do_checkbox':
				$new_value = $this->one_zero( $new_value );
				break;

			case 'do_number':
				$new_value = $this->check_value( $new_value, $old_value, $field['args']['min'], $field['args']['max'] );
				break;

			case 'do_select':
				$new_value = esc_attr( $new_value );
				break;

			case 'do_checkbox_array':
				$choices = $field['args']['choices'];
				foreach ( $choices as $key => $label ) {
					$new_value[ $key ] = $this->one_zero( $new_value[ $key ] );
				}
				break;

			case 'do_text_field':
				$new_value = sanitize_text_field( $new_value );
				break;

			case 'do_wysiwyg':
				$new_value = wp_kses_post( $new_value );
				break;

			default:
				$new_value = is_array( $new_value ) ? array_map( 'sanitize_text_field', $new_value ) : sanitize_text_field( $new_value );
				break;
		} // End switch().
		return $this->format_fields( $new_value, $field );
	}

	/**
	 * Format values for specific kinds of data.
	 * Currently supported: url, email, image
	 * @param $value
	 * @param $field
	 *
	 * @return mixed
	 */
	protected function format_fields( $value, $field ) {
		if ( ! isset( $field['format'] ) ) {
			return $value;
		}
		$method = "validate_{$field['format']}";
		if ( method_exists( $this, $method ) ) {
			$value = $this->$method( $value, $field );
		}

		return $value;
	}

	protected function validate_url( $new_value, $field ) {
		return esc_url( $new_value );
	}

	/**
	 * Validate the email address.
	 *
	 * @param $new_value
	 *
	 * @param $field
	 *
	 * @return string
	 */
	protected function validate_email( $new_value, $field ) {
		return is_email( $new_value ) ? esc_attr( $new_value ) : '';
	}

	/**
	 * Format a standard US phone number.
	 *
	 * @param $new_value
	 *
	 * @param string $field
	 *
	 * @return string
	 */
	protected function validate_phone( $new_value, $field = '' ) {
		if ( preg_match( '/^\D?(\d{3})\D?\D?(\d{3})\D?(\d{4})$/', $new_value, $matches ) ) {
			$new_value = sprintf( '(%s) %s-%s', $matches[1], $matches[2], $matches[3] );
		}

		return $new_value;
	}

	/**
	 * Returns previous value for image if not correct file type/size
	 *
	 * @param  string $new_value New value
	 * @param $old_value
	 * @param $label
	 *
	 * @return string New or previous value, depending on allowed image size.
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
	 * @return array
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
