<?php

/**
 * Class SixTenPressFieldCheckboxArray
 * @copyright 2018-2019 Robin Cornett
 */
class SixTenPressFieldCheckboxArray extends SixTenPressFieldBase {

	/**
	 * Build a checkbox array field.
	 */
	public function do_field() {
		echo '<fieldset>';
		foreach ( $this->field['options'] as $choice => $field_label ) {
			$check = isset( $this->value[ $choice ] ) ? $this->value[ $choice ] : 0;
			printf(
				'<input type="hidden" name="%s[%s]" value="0" />',
				esc_attr( $this->name ),
				esc_attr( $choice )
			);
			printf(
				'<label for="%5$s-%1$s"><input type="checkbox" name="%4$s[%1$s]" id="%5$s-%1$s" value="1"%2$s class="code" aria-labelledby="%5$s"/>%3$s</label>',
				esc_attr( $choice ),
				checked( 1, $check, false ),
				esc_html( $field_label ),
				esc_attr( $this->name ),
				esc_attr( $this->id )
			);
		}
		echo '</fieldset>';
	}
}
