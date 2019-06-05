<?php

/**
 * Build out the text field.
 *
 * Class SixTenPressFieldText
 * @copyright 2018-2019 Robin Cornett
 */
class SixTenPressFieldText extends SixTenPressFieldBase {

	/**
	 * Build a text field.
	 */
	public function do_field() {
		$defaults = array(
			'class' => 'regular-text',
		);
		$field    = wp_parse_args( $this->field, $defaults );
		if ( 'custom_date' === $field['class'] ) {
			$this->value = $this->value ? date( 'n/j/Y', $this->value ) : '';
		}

		printf(
			'<input type="%7$s" id="%3$s" aria-label="%1$s" name="%1$s" value="%2$s" class="%4$s"%5$s%6$s/>',
			esc_attr( $this->name ),
			esc_attr( $this->value ),
			esc_attr( $this->id ),
			esc_attr( $field['class'] ),
			esc_attr( $field['required'] ),
			$this->get_data_attribute( $field ),
			esc_attr( $this->get_field_type( $field ) )
		);
	}

	/**
	 * Get the correct field type (HTML5).
	 *
	 * @param $field
	 *
	 * @return string
	 */
	private function get_field_type( $field ) {
		if ( empty( $field['format'] ) ) {
			return 'text';
		}
		switch ( $field['format'] ) {
			case 'email':
				return 'email';

			case 'url':
				return 'url';

			case 'password':
				return 'password';

			default:
				return 'text';
		}
	}

	/**
	 * Allow plugins to add custom data attributes to an input field.
	 * @since 2.0.0
	 *
	 * @param $field
	 *
	 * @return string
	 */
	protected function get_data_attribute( $field ) {
		if ( ! isset( $field['data'] ) || ! $field['data'] ) {
			return '';
		}
		if ( is_string( $field['data'] ) ) {
			return ' ' . $field['data'];
		}
		$output = '';
		foreach ( $field['data'] as $key => $value ) {
			$output .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
		}

		return $output;
	}
}
