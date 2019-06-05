<?php

/**
 * Build out the select field.
 *
 * Class SixTenPressFieldSelect
 * @copyright 2018-2019 Robin Cornett
 */
class SixTenPressFieldSelect extends SixTenPressFieldBase {

	/**
	 * Build a select field.
	 */
	public function do_field() {
		printf(
			'<select id="%1$s" name="%2$s" aria-label="%5$s"%3$s%4$s>',
			esc_attr( $this->id ),
			esc_attr( $this->name ),
			esc_attr( $this->get_class( $this->field ) ),
			esc_attr( $this->field['required'] ),
			esc_attr( $this->get_aria( $this->field ) )
		);
		foreach ( (array) $this->field['options'] as $option => $field_label ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $option ),
				selected( $option, $this->value, false ),
				esc_attr( $field_label )
			);
		}
		echo '</select>';
	}

	/**
	 * Define the optional class for the select field.
	 *
	 * @return string
	 */
	private function get_class() {
		return empty( $this->field['class'] ) ? '' : sprintf( ' class=%s', $this->field['class'] );
	}

	/**
	 * Define the aria-label value for the select field.
	 *
	 * @return string
	 */
	private function get_aria() {
		$aria = $this->name;
		if ( isset( $this->field['label'] ) ) {
			$aria = $this->field['label'];
		} elseif ( isset( $this->field['title'] ) ) {
			$aria = $this->field['title'];
		}

		return $aria;
	}
}
