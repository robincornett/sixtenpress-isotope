<?php

/**
 * Build out the checkbox field.
 *
 * Class SixTenPressFieldCheckbox
 * @copyright 2018-2019 Robin Cornett
 */
class SixTenPressFieldCheckbox extends SixTenPressFieldBase {

	/**
	 * Build a checkbox field.
	 */
	public function do_field() {
		printf(
			'<input type="hidden" name="%s" value="0" />',
			esc_attr( $this->name )
		);

		printf(
			'<label for="%2$s"><input type="checkbox" name="%1$s" id="%2$s" value="1" %3$s class="code"%5$s/>%4$s</label>',
			esc_attr( $this->name ),
			esc_attr( $this->id ),
			checked( 1, $this->value, false ),
			esc_attr( $this->field['label'] ),
			esc_attr( $this->field['required'] )
		);
	}
}
