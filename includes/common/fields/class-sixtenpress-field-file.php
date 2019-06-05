<?php

require_once plugin_dir_path( __FILE__ ) . 'class-sixtenpress-field-base-files.php';

/**
 * Class SixTenPressFieldFile
 * @copyright 2018-2019 Robin Cornett
 */
class SixTenPressFieldFile extends SixTenPressFieldBaseFiles {

	/**
	 * Build a file field.
	 *
	 * @param $getter   \SixTenPressCustomFieldsFieldGetter
	 * @param $is_group bool
	 */
	public function do_field( $getter, $is_group = false ) {
		$id_only = $this->is_id_only( $this->field, $is_group );
		$this->add_localization_filter( $this->id, $this->field );

		$is_image = $this->is_image_only( $this->field );
		if ( $is_image ) {
			$image_id = $id_only ? $this->value : $getter->get_field_value( $this->field['setting'] . '_id' );
			$this->do_image_preview( $image_id, $this->field );
		}

		if ( ! $id_only ) {
			$this->do_hidden_id_input( $this->field, $getter );
		}

		$this->do_main_file_input( $this->name, $this->value, $this->id, $this->field, $id_only );
		$this->do_upload_button( $this->id, $this->field );

		if ( ! $is_image ) {
			$this->do_delete_button( $this->value );
		}
	}

	/**
	 * Build out the hidden ID input (custom fields only).
	 *
	 * @param $field
	 * @param $getter \SixTenPressCustomFieldsFieldGetter
	 */
	private function do_hidden_id_input( $field, $getter ) {
		printf(
			'<input type="hidden" class="upload-file-id" id="%1$s" name="%2$s" value="%3$s" />',
			esc_attr( $getter->get_field_id( $field['setting'] . '_id' ) ),
			esc_attr( $getter->get_field_name( $field['setting'] . '_id' ) ),
			esc_attr( $getter->get_field_value( $field['setting'] . '_id' ) )
		);
	}

	/**
	 * Build out the main text/hidden (for images) input field for files.
	 *
	 * @param $name
	 * @param $value
	 * @param $id
	 * @param $field
	 * @param $save_url
	 */
	private function do_main_file_input( $name, $value, $id, $field, $save_url ) {
		printf(
			'<input type="%4$s" class="upload-file-%5$s regular-text" id="%3$s" name="%1$s" value="%2$s" />',
			esc_attr( $name ),
			esc_attr( $value ),
			esc_attr( $id ),
			esc_attr( $this->is_image_only( $field ) ? 'hidden' : 'text' ),
			esc_attr( $save_url ? 'id' : 'url' )
		);
	}

	/**
	 * Build the delete button.
	 *
	 * @param $value
	 */
	private function do_delete_button( $value ) {
		printf(
			' <button class="delete-file button-secondary" style="%s">%s</button>',
			esc_attr( $value ? '' : 'display:none;' ),
			esc_attr__( 'Delete File', 'sixtenpress' )
		);
	}

	/**
	 * Check if a file field should save only the ID.
	 * Custom fields/images save both the ID and URL unless they're part of a group.
	 *
	 * @param $field
	 * @param $is_group
	 *
	 * @return bool
	 */
	private function is_id_only( $field, $is_group ) {
		if ( $is_group ) {
			return true;
		}
		if ( ! empty( $field['multiple'] ) ) {
			return true;
		}
		$screen = get_current_screen();

		return in_array( $screen->parent_base, array( 'options-general' ), true );
	}

	/**
	 * Determine if the field is restricted to an image file type.
	 * @since 2.2.0
	 *
	 * @param $field
	 * @return bool
	 */
	private function is_image_only( $field ) {
		$library = $this->get_library( $field );

		return (bool) in_array( 'image', $library, true ) && 1 === count( $library );
	}
}
