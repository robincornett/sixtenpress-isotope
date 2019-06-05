<?php

/**
 * Class SixTenPressFieldRadio
 * @copyright 2019 Robin Cornett
 */
class SixTenPressFieldRadio extends SixTenPressFieldBase {

	/**
	 * Build a radio input field.
	 */
	public function do_field() {
		echo '<fieldset class="radio">';
		foreach ( $this->field['options'] as $choice => $field_label ) {
			printf(
				'<label for="%5$s-%1$s" style="margin-right:12px !important;"><input type="radio" name="%4$s" id="%5$s-%1$s" value="%1$s"%2$s aria-labelledby="%5$s"/>%3$s</label>',
				esc_attr( $choice ),
				checked( $choice, $this->value, false ),
				esc_html( $field_label ),
				esc_attr( $this->name ),
				esc_attr( $this->id )
			);
		}
		echo '</fieldset>';
	}
}
