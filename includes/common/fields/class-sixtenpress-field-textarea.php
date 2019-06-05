<?php

/**
 * Build out a textarea field.
 *
 * Class SixTenPressFieldTextarea
 * @copyright 2018-2019 Robin Cornett
 */
class SixTenPressFieldTextarea extends SixTenPressFieldBase {

	/**
	 * Build a textarea field.
	 */
	public function do_field() {
		$rows = isset( $this->field['rows'] ) ? $this->field['rows'] : 3;
		printf(
			'<textarea class="large-text" rows="%4$s" id="%1$s" name="%2$s" aria-label="%5$s"%6$s>%3$s</textarea>',
			esc_attr( $this->id ),
			esc_attr( $this->name ),
			sanitize_textarea_field( $this->value ),
			(int) $rows,
			esc_attr( $this->field['label'] ),
			esc_attr( $this->field['required'] )
		);
	}
}
