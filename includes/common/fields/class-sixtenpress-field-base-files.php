<?php

/**
 * Base class with helper functions for file/upload fields.
 *
 * Class SixTenPressFieldFiles
 * @copyright 2018-2019 Robin Cornett
 */
class SixTenPressFieldBaseFiles extends SixTenPressFieldBase {

	/**
	 * Build the input field.
	 *
	 * @param $id
	 * @param $value
	 * @param $name
	 */
	protected function do_input_field( $id, $value, $name ) {
		printf(
			'<input type="hidden" class="upload-file-id" id="%1$s" name="%3$s" value="%2$s" />',
			esc_attr( $id ),
			esc_attr( $value ),
			esc_attr( $name )
		);
	}

	/**
	 * Build the upload button.
	 *
	 * @param $id
	 * @param $field
	 */
	protected function do_upload_button( $id, $field ) {
		$label = $this->get_label( $field );
		printf(
			'<button id="%1$s-button" class="upload-file button-secondary %1$s">%2$s</button>',
			esc_attr( $id ),
			esc_attr( $label )
		);
	}

	/**
	 * Get the correct upload button label.
	 * @since 2.2.0
	 *
	 * @param $field
	 * @return string
	 */
	protected function get_label( $field ) {
		switch ( $field['type'] ) {
			case 'image':
				$label = __( 'Select Image', 'sixtenpress' );
				break;

			case 'gallery':
				$label = __( 'Select Images', 'sixtenpress' );
				break;

			default:
				$label = __( 'Select File', 'sixtenpress' );
		}

		return $label;
	}

	/**
	 * Build a delete button.
	 *
	 * @since 2.2.0
	 *
	 * @param  string $value
	 *
	 * @return string
	 */
	protected function get_image_delete_button( $value = '' ) {
		return sprintf(
			' <button class="delete-file button-secondary"><span class="dashicons dashicons-no"></span> <span class="screen-reader-text">%s</span></button>',
			esc_attr__( 'Delete File', 'sixtenpress' )
		);
	}

	/**
	 * Render the image preview(s).
	 *
	 * @since 2.2.0
	 *
	 * @param   string $value
	 * @param   array  $field
	 */
	protected function do_image_preview( $value = '', $field = array() ) {
		add_filter( 'wp_kses_allowed_html', array( $this, 'filter_allowed_html' ), 10, 2 );
		$preview = $this->get_preview( $value );
		printf( '<div class="%s">%s</div>', esc_attr( $this->get_preview_class( $field ) ), wp_kses_post( $preview ) );
		remove_filter( 'wp_kses_allowed_html', array( $this, 'filter_allowed_html' ) );
	}

	/**
	 * Get all preview image elements with markup.
	 *
	 * @param $value
	 *
	 * @return string
	 */
	private function get_preview( $value ) {
		$preview = '';
		$images  = explode( ',', $value );
		if ( ! $images ) {
			return $preview;
		}
		foreach ( $images as $image ) {
			if ( ! $image ) {
				continue;
			}
			$preview .= '<div class="preview-image">';
			$preview .= wp_get_attachment_image(
				(int) $image,
				'medium',
				false,
				array(
					'data-id' => $image,
				)
			);
			$preview .= $this->get_image_delete_button( $value );
			$preview .= '</div>';
		}

		return $preview;
	}

	/**
	 * Get the class for the image preview div.
	 *
	 * @param $field
	 *
	 * @return string
	 */
	private function get_preview_class( $field ) {
		$class = 'upload-file-preview';
		if ( ! empty( $field['multiple'] ) ) {
			$class .= ' images-multiple';
		}

		return $class;
	}

	/**
	 * Allow the data-id attribute on images in the admin.
	 *
	 * @param $allowed
	 * @param $context
	 *
	 * @return mixed
	 */
	public function filter_allowed_html( $allowed, $context ) {
		if ( 'post' === $context ) {
			$allowed['img']['data-id'] = true;
		}

		return $allowed;
	}

	/**
	 * Build up the localization data.
	 *
	 * @param $field
	 *
	 * @return array
	 */
	protected function get_localization_data( $field ) {
		return array(
			'text'     => __( 'Select ', 'sixtenpress' ) . $field['label'],
			'type'     => $this->get_library( $field ),
			'multiple' => ! empty( $field['multiple'] ) ? $field['multiple'] : false,
			'delete'   => $this->get_image_delete_button(),
		);
	}

	/**
	 * Get the allowed file types for a field.
	 * @since 2.2.0
	 *
	 * @param $field
	 * @return array
	 */
	protected function get_library( $field ) {
		return ! empty( $field['library'] ) ? $field['library'] : array(
			'audio',
			'video',
			'image',
			'application',
		);
	}

	/**
	 * Update the localization data for the file upload script.
	 * @since 2.2.0
	 *
	 * @param $id
	 * @param $field
	 */
	protected function add_localization_filter( $id, $field ) {
		add_filter(
			'sixtenpress_uploader_localization',
			function ( $data ) use ( $id, $field ) {
				return array_merge(
					$data,
					array(
						$id => $this->get_localization_data( $field ),
					)
				);
			}
		);
	}
}
