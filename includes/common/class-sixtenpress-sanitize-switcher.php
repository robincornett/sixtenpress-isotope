<?php

/**
 * Class SixTenPressSanitizeSwitcher
 * @copyright 2019 Robin Cornett
 */
class SixTenPressSanitizeSwitcher {

	/**
	 * @var \SixTenPressSanitizeCustom
	 */
	protected $custom;

	/**
	 * Switch through field types and sanitize.
	 *
	 * @param $post_value
	 * @param $field
	 * @param $old_value
	 *
	 * @return string
	 */
	public function sanitize_switcher( $post_value, $field, $old_value = '' ) {
		$switch = isset( $field['type'] ) ? $field['type'] : '';
		switch ( $switch ) {
			case 'file':
				$post_value = is_numeric( $post_value ) ? (int) $post_value : esc_url( $post_value );
				break;

			case 'image':
				$name       = ! empty( $field['title'] ) ? $field['title'] : $field['label'];
				$post_value = $this->validate_image( $post_value, $old_value, $name );
				break;

			case 'gallery':
			case 'text':
				$post_value = esc_attr( $post_value );
				break;

			case 'id':
				$post_value = (int) $post_value;
				break;

			case 'radio':
			case 'select':
				$post_value = is_numeric( $post_value ) ? (int) $post_value : esc_attr( $post_value );
				break;

			case 'textarea':
				$post_value = sanitize_textarea_field( $post_value );
				break;

			case 'wysiwyg':
				$post_value = apply_filters( 'content_save_pre', $post_value );
				break;

			case 'checkbox_array':
				$post_value = $this->validate_checkbox_array( $post_value );
				break;

			case 'checkbox':
				$post_value = $this->one_zero( $post_value );
				break;

			case 'multiselect':
				$post_value = $this->validate_multiselect( $post_value );
				break;

			case 'number':
				$post_value = $this->check_value( $post_value, $old_value, $field );
				break;

			case 'multidimensional':
				array_walk_recursive( $post_value, array( $this, 'validate_multidimensional' ) );
				break;

			case 'group':
				$repeatable = isset( $field['repeatable'] ) && $field['repeatable'];
				$post_value = $this->validate_group( $post_value, $field['group'], $repeatable );
				break;

			default:
				$post_value = is_array( $post_value ) ? array_map( 'sanitize_text_field', $post_value ) : sanitize_text_field( $post_value );
				break;
		}

		$custom = $this->get_custom_formatter();

		return $custom->format_fields( $post_value, $field, $old_value );
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

	/**
	 * Validate multidimensional arrays.
	 *
	 * @param $new_value
	 * @param $key
	 */
	public function validate_multidimensional( &$new_value, $key ) {
		$new_value = is_numeric( $new_value ) && $new_value < PHP_INT_MAX ? (int) $new_value : wp_kses_post( $new_value );
	}

	/**
	 * Validate checkbox arrays.
	 *
	 * @param $post_value
	 *
	 * @return mixed
	 */
	protected function validate_checkbox_array( $post_value ) {
		foreach ( $post_value as $key => $value ) {
			$post_value[ $key ] = $this->one_zero( $value );
		}

		return $post_value;
	}

	/**
	 * Validate multiselect arrays.
	 *
	 * @param $post_value
	 *
	 * @return array
	 */
	protected function validate_multiselect( $post_value ) {
		$new = array();
		foreach ( $post_value as $value ) {
			if ( $value ) {
				$new[] = is_numeric( $value ) ? (int) $value : sanitize_text_field( $value );
			}
		}

		return $new;
	}

	/**
	 * Check the numeric value against the allowed range. If it's within the range, return it; otherwise, return the
	 * old value.
	 *
	 * @param $new_value int new submitted value
	 * @param $old_value int old setting value
	 * @param $field     array
	 *
	 * @return int
	 */
	protected function check_value( $new_value, $old_value, $field ) {
		$min = 0;
		$max = PHP_INT_MAX;
		if ( isset( $field['min'] ) ) {
			$min = $field['min'];
		} elseif ( isset( $field['args']['min'] ) ) {
			$min = $field['args']['min'];
		}
		if ( isset( $field['max'] ) ) {
			$max = $field['max'];
		} elseif ( isset( $field['args']['max'] ) ) {
			$max = $field['args']['max'];
		}
		if ( $new_value >= $min && $new_value <= $max ) {
			return (int) $new_value;
		}

		return $old_value;
	}

	/**
	 * Validate group fields.
	 *
	 * @param $post_value array
	 * @param $group      array
	 *
	 * @param $repeatable
	 *
	 * @return array
	 */
	protected function validate_group( $post_value, $group, $repeatable ) {
		if ( $repeatable ) {
			array_pop( $post_value );
		}
		$i   = 0;
		$new = array();
		foreach ( $post_value as $index => $set ) {
			if ( $repeatable ) {
				foreach ( $set as $key => $value ) {
					$new[ $i ][ $key ] = $this->sanitize_switcher( $value, $this->get_group_field( $group, $key ) );
				}
				$i ++;
			} else {
				$new[ $index ] = $this->sanitize_switcher( $set, $this->get_group_field( $group, $index ) );
			}
		}

		return $new;
	}

	/**
	 * Get the group field that matches the $post_value.
	 *
	 * @param $group array
	 * @param $key   string
	 *
	 * @return array
	 */
	protected function get_group_field( $group, $key ) {
		foreach ( $group as $field ) {
			if ( ( isset( $field['setting'] ) && $key === $field['setting'] ) || ( isset( $field['id'] ) && $key === $field['id'] ) ) {
				return $field;
			}
		}

		return array();
	}

	/**
	 * Returns previous value for image if not correct file type/size
	 *
	 * @param  string $new_value New value
	 * @param         $old_value
	 * @param         $label
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
		/* translators: the placeholder is the label for whatever the image is. */
		$reset = sprintf( __( ' The %s has been reset to the last valid setting.', 'sixtenpress' ), $label );

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
	 *
	 * @param $file
	 *
	 * @return bool
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
	 * Get the custom formatting class.
	 *
	 * @return \SixTenPressSanitizeCustom
	 * @since 2.0.0
	 */
	protected function get_custom_formatter() {
		if ( isset( $this->custom ) ) {
			return $this->custom;
		}
		if ( ! class_exists( 'SixTenPressAutoloader' ) ) {
			include_once trailingslashit( plugin_dir_path( __FILE__ ) ) . 'class-sixtenpress-sanitize-custom.php';
		}
		$this->custom = new SixTenPressSanitizeCustom();

		return $this->custom;
	}
}
