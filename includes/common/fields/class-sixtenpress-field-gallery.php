<?php

require_once plugin_dir_path( __FILE__ ) . 'class-sixtenpress-field-base-files.php';

/**
 * Class SixTenPressFieldGallery
 * @copyright 2018-2019 Robin Cornett
 */
class SixTenPressFieldGallery extends SixTenPressFieldBaseFiles {

	/**
	 * Build a gallery field.
	 */
	public function do_field() {
		$field = $this->update_field( $this->field );
		$this->do_image_preview( $this->value, $field );
		$this->add_localization_filter( $this->id, $field );
		$this->do_input_field( $this->id, $this->value, $this->name );
		$this->do_upload_button( $this->id, $field );
	}

	/**
	 * Gallery fields are always restricted to image only uploads.
	 * @since 2.2.0
	 *
	 * @param $field
	 * @return array
	 */
	protected function get_library( $field ) {
		return array( 'image' );
	}

	/**
	 * Update the field to always allow multiple files and to only include images.
	 * @since 2.2.0
	 *
	 * @param $field
	 * @return array
	 */
	private function update_field( $field ) {
		return array_merge(
			$field,
			array(
				'multiple' => true,
			)
		);
	}
}
