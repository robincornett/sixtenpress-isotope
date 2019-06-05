<?php

/**
 * Class SixTenPressFieldColor
 * @copyright 2018-2019 Robin Cornett
 */
class SixTenPressFieldColor extends SixTenPressFieldBase {

	/**
	 * Build a color field.
	 */
	public function do_field() {
		printf(
			'<input type="text" name="%1$s" id="%3$s" value="%2$s" class="color-field" data-default-color="%4$s">',
			esc_attr( $this->name ),
			esc_attr( $this->value ),
			esc_attr( $this->id ),
			esc_attr( $this->get_default() )
		);
	}

	/**
	 * Get the default color value if it exists.
	 * @return string
	 */
	protected function get_default() {
		return ! empty( $this->field['default'] ) ? $this->field['default'] : '';
	}
}
