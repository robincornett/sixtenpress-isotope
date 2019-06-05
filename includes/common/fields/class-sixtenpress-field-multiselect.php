<?php

/**
 * Class SixTenPressFieldMultiselect
 * @copyright 2018-2019 Robin Cornett
 */
class SixTenPressFieldMultiselect extends SixTenPressFieldBase {

	/**
	 * Build a multiselect field.
	 */
	public function do_field() {
		printf(
			'<input type="hidden" name="%s[]" value="" />',
			esc_attr( $this->name )
		);

		echo '<fieldset>';
		foreach ( $this->field['options'] as $choice => $field_label ) {
			$check = isset( $this->value ) && $this->value && in_array( $choice, $this->value, true ) ? $choice : '';
			printf(
				'<label for="%5$s[%1$s]"><input type="checkbox" name="%4$s[]" id="%5$s[%1$s]" value="%1$s"%2$s class="code" aria-labelledby="%5$s"/>%3$s</label>',
				esc_attr( $choice ),
				checked( $choice, $check, false ),
				esc_html( $field_label ),
				esc_attr( $this->name ),
				esc_attr( $this->id )
			);
		}
		echo '</fieldset>';
	}
}
