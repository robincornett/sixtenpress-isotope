<?php

/**
 * Class SixTenPressSanitizeCustom
 * @copyright 2018-2019 Robin Cornett
 */
class SixTenPressSanitizeCustom {

	/**
	 * Allow for custom sanitizing/formatting methods.
	 * Current allowed formats: date, url, phone, email
	 *
	 * @param $post_value
	 * @param $field
	 *
	 * @param $old_value
	 *
	 * @return mixed
	 */
	public function format_fields( $post_value, $field, $old_value ) {
		if ( ! isset( $field['format'] ) ) {
			return $post_value;
		}
		$method = "sanitize_{$field['format']}";
		if ( method_exists( $this, $method ) ) {
			$post_value = $this->$method( $post_value, $field, $old_value );
		}

		return $post_value;
	}

	/**
	 * Make sure URL is properly escaped.
	 *
	 * @param $post_value
	 *
	 * @param $field
	 * @param $old_value
	 *
	 * @return string
	 */
	public function sanitize_url( $post_value, $field, $old_value ) {
		$protocols = isset( $field['protocols'] ) ? $field['protocols'] : null;
		return esc_url_raw( $post_value, $protocols );
	}

	/**
	 * Since dates are a custom kind of field, we need to handle it uniquely.
	 *
	 * @param $post_value
	 * @param $field
	 *
	 * @param $old_value
	 *
	 * @return false|int
	 */
	public function sanitize_date( $post_value, $field, $old_value ) {
		if ( isset( $field['class'] ) && 'custom_date' === $field['class'] ) {
			return strtotime( $post_value );
		}

		return $post_value;
	}

	/**
	 * Format a standard US phone number.
	 *
	 * @param $post_value
	 *
	 * @param $field
	 * @param $old_value
	 *
	 * @return string
	 */
	public function sanitize_phone( $post_value, $field, $old_value ) {
		if ( preg_match( '/^\D?(\d{3})\D?\D?(\d{3})\D?(\d{4})$/', $post_value, $matches ) ) {
			$post_value = sprintf( '(%s) %s-%s', $matches[1], $matches[2], $matches[3] );
		}

		return $post_value;
	}

	/**
	 * Sanitize an email address.
	 *
	 * @param $post_value
	 *
	 * @param $field
	 * @param $old_value
	 *
	 * @return string
	 */
	public function sanitize_email( $post_value, $field, $old_value ) {
		return is_email( $post_value ) || empty( $post_value ) ? $post_value : $old_value;
	}
}
