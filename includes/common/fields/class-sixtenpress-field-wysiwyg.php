<?php

/**
 * Class SixTenPressFieldWysiwyg
 * @copyright 2018-2019 Robin Cornett
 */
class SixTenPressFieldWysiwyg extends SixTenPressFieldBase {

	/**
	 * Build a wysiwyg field.
	 */
	public function do_field() {
		wp_editor( $this->value, $this->id, $this->parse_args() );
	}

	/**
	 * Merge any custom editor parameters with defaults.
	 * @since 2.2.0
	 *
	 * @return array
	 */
	private function parse_args() {
		$custom_args = isset( $this->field['args'] ) ? $this->field['args'] : array();

		return wp_parse_args(
			$custom_args,
			array(
				'textarea_name' => $this->name,
				'media_buttons' => true,
				'textarea_rows' => 10,
			)
		);
	}
}
