<?php

/**
 * Build out the number field.
 *
 * Class SixTenPressFieldNumber
 * @copyright 2018-2019 Robin Cornett
 */
class SixTenPressFieldNumber extends SixTenPressFieldBase {

	/**
	 * Build a number field.
	 */
	public function do_field() {
		$screen = get_current_screen();
		$field  = wp_parse_args(
			$this->field,
			array(
				'class' => 'post' !== $screen->base ? 'small-text' : 'regular-text',
				'min'   => '',
				'max'   => '',
				'step'  => 1,
				'value' => '',
			)
		);

		$input = sprintf(
			'<input type="number" id="%7$s" aria-label="%1$s" name="%1$s" value="%2$s" class="%3$s" step="%8$s"%4$s%5$s%6$s/>',
			esc_attr( $this->name ),
			esc_attr( $this->value ),
			esc_attr( $field['class'] ),
			esc_attr( $field['required'] ),
			esc_attr( $field['min'] ? sprintf( ' min="%s"', esc_attr( $field['min'] ) ) : '' ),
			esc_attr( $field['max'] ? sprintf( ' max="%s"', esc_attr( $field['max'] ) ) : '' ),
			esc_attr( $this->id ),
			esc_attr( $field['step'] )
		);

		if ( 'post' !== $screen->base ) {
			$input = sprintf(
				'<label for="%s">%s%s</label>',
				esc_attr( $this->name ),
				$input,
				esc_attr( $field['label'] )
			);
		}

		echo $input;
	}
}
