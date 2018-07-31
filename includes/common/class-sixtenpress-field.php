<?php

/**
 * Class SixTenPressField
 */
class SixTenPressField {

	/**
	 * @var
	 */
	protected $prefix;

	/**
	 * Build a checkbox field.
	 *
	 * @param $name
	 * @param $id
	 * @param $value
	 * @param $args
	 */
	protected function do_field_checkbox( $name, $id, $value, $args ) {
		printf( '<input type="hidden" name="%s" value="0" />',
			esc_attr( $name )
		);

		printf( '<label for="%2$s"><input type="checkbox" name="%1$s" id="%2$s" value="1" %3$s class="code"%5$s/>%4$s</label>',
			esc_attr( $name ),
			esc_attr( $id ),
			checked( 1, $value, false ),
			esc_attr( $args['label'] ),
			esc_attr( $this->required( $args ) )
		);
	}

	/**
	 * Build a checkbox array field.
	 *
	 * @param $name
	 * @param $id
	 * @param $value
	 * @param $args
	 */
	protected function do_field_checkbox_array( $name, $id, $value, $args ) {
		echo '<fieldset>';
		foreach ( $this->get_options( $args ) as $choice => $field_label ) {
			$check = isset( $value[ $choice ] ) ? $value[ $choice ] : 0;
			printf( '<input type="hidden" name="%s[%s]" value="0" />',
				esc_attr( $name ),
				esc_attr( $choice )
			);
			printf( '<label for="%5$s-%1$s"><input type="checkbox" name="%4$s[%1$s]" id="%5$s-%1$s" value="1"%2$s class="code" aria-labelledby="%5$s"/>%3$s</label>',
				esc_attr( $choice ),
				checked( 1, $check, false ),
				esc_html( $field_label ),
				esc_attr( $name ),
				esc_attr( $id )
			);
		}
		echo '</fieldset>';
	}

	/**
	 * Build a color field.
	 * @param $name
	 * @param $id
	 * @param $value
	 * @param $args
	 */
	protected function do_field_color( $name, $id, $value, $args ) {
		$default = isset( $args['default'] ) && $args['default'] ? $args['default'] : '';
		printf( '<input type="text" name="%1$s" id="%3$s" value="%2$s" class="color-field" data-default-color="%4$s">',
			esc_attr( $name ),
			esc_attr( $value ),
			esc_attr( $id ),
			esc_attr( $default )
		);
	}

	/**
	 * Build a file field.
	 *
	 * @param $name
	 * @param $id
	 * @param $value
	 * @param $args
	 * @param $getter \SixTenPressCustomFieldsFieldGetter
	 * @param $is_group bool
	 */
	protected function do_field_file( $name, $id, $value, $args, $getter, $is_group = false ) {
		add_filter( 'sixtenpress_uploader_localization', function ( $data ) use ( $args ) {
			$data[ $this->prefix . '-' . $args['setting'] ] = $this->get_localization_data( $args );

			return $data;
		} );

		if ( in_array( 'image', $args['library'], true ) ) {
			$image_id = $is_group ? $value : $getter->get_field_value( $args['setting'] . '_id' );
			if ( $image_id ) {
				printf( '<div class="upload-file-preview">%s</div>', wp_get_attachment_image(
					(int) $image_id,
					'medium',
					false
				) );
			}
		}

		if ( ! $is_group ) {
			printf( '<input type="hidden" class="upload-file-id" id="%1$s" name="%2$s" value="%3$s" />',
				esc_attr( $getter->get_field_id( $args['setting'] . '_id' ) ),
				esc_attr( $getter->get_field_name( $args['setting'] . '_id' ) ),
				esc_attr( $getter->get_field_value( $args['setting'] . '_id' ) )
			);
		}

		printf( '<input type="%4$s" class="upload-file-%5$s regular-text" id="%3$s" name="%1$s" value="%2$s" />',
			esc_attr( $name ),
			esc_attr( $value ),
			esc_attr( $id ),
			esc_attr( in_array( 'image', $args['library'], true ) ? 'hidden' : 'text' ),
			esc_attr( $is_group ? 'id' : 'url' )
		);

		printf( '<button id="%s-button" class="upload-file button-secondary %s-%s">%s</button>',
			esc_attr( $id ),
			esc_attr( $this->prefix ),
			esc_attr( $args['setting'] ),
			esc_attr__( 'Select File', 'sixtenpress' )
		);

		printf( ' <button class="delete-file button-secondary" style="%s">%s</button>',
			esc_attr( $value ? '' : 'display:none;' ),
			esc_attr__( 'Delete File', 'sixtenpress' )
		);
	}

	/**
	 * Build an image field.
	 * @param $name
	 * @param $id
	 * @param $value
	 * @param $args
	 */
	protected function do_field_image( $name, $id, $value, $args ) {
		if ( ! empty( $value ) ) {
			printf( '<div class="upload-file-preview">%s</div>', wp_get_attachment_image(
				(int) $value,
				'medium',
				false
			) );
		}
		add_filter( 'sixtenpress_uploader_localization', function ( $data ) use ( $args ) {
			return array_merge( $data, array(
				$args['id'] => $this->get_localization_data( $args ),
			) );
		} );
		printf( '<input type="hidden" class="upload-file-id" id="%1$s" name="%3$s" value="%2$s" />',
			esc_attr( $id ),
			esc_attr( $value ),
			esc_attr( $name )
		);
		printf( '<button id="%1$s-button" class="upload-file button-secondary %2$s">%3$s</button>',
			esc_attr( $id ),
			esc_attr( $args['id'] ),
			esc_attr__( 'Select Image', 'sixtenpress' )
		);
		printf( ' <button class="delete-file button-secondary" style="%s">%s</button>',
			esc_attr( $value ? '' : 'display:none;' ),
			esc_attr__( 'Delete Image', 'sixtenpress' )
		);
	}

	/**
	 * Build a multiselect field.
	 *
	 * @param $name
	 * @param $id
	 * @param $value
	 * @param $args
	 */
	protected function do_field_multiselect( $name, $id, $value, $args ) {
		printf( '<input type="hidden" name="%s[]" value="" />',
			esc_attr( $name )
		);

		echo '<fieldset>';
		foreach ( $this->get_options( $args ) as $choice => $field_label ) {
			$check = isset( $value ) && $value && in_array( $choice, $value, true ) ? $choice : '';
			printf( '<label for="%5$s[%1$s]"><input type="checkbox" name="%4$s[]" id="%5$s[%1$s]" value="%1$s"%2$s class="code" aria-labelledby="%5$s"/>%3$s</label>',
				esc_attr( $choice ),
				checked( $choice, $check, false ),
				esc_html( $field_label ),
				esc_attr( $name ),
				esc_attr( $id )
			);
		}
		echo '</fieldset>';
	}

	/**
	 * Build a number field.
	 *
	 * @param $name
	 * @param $id
	 * @param $value
	 * @param $args
	 */
	protected function do_field_number( $name, $id, $value, $args ) {
		$screen = get_current_screen();
		$args   = wp_parse_args( $args, array(
			'class' => 'post' !== $screen->base ? 'small-text' : 'regular-text',
			'min'   => '',
			'max'   => '',
			'step'  => 1,
			'value' => '',
		) );

		$input = sprintf( '<input type="number" id="%7$s" aria-label="%1$s" name="%1$s" value="%2$s" class="%3$s" step="%8$s"%4$s%5$s%6$s/>',
			esc_attr( $name ),
			esc_attr( $value ),
			esc_attr( $args['class'] ),
			esc_attr( $this->required( $args ) ),
			esc_attr( $args['min'] ? sprintf( ' min="%s"', esc_attr( $args['min'] ) ) : '' ),
			esc_attr( $args['max'] ? sprintf( ' max="%s"', esc_attr( $args['max'] ) ) : '' ),
			esc_attr( $id ),
			esc_attr( $args['step'] )
		);

		if ( 'post' !== $screen->base ) {
			$input = sprintf( '<label for="%s">%s%s</label>',
				esc_attr( $name ),
				$input,
				esc_attr( $args['label'] )
			);
		}

		echo $input;
	}

	/**
	 * Build a select field.
	 *
	 * @param $name
	 * @param $id
	 * @param $value
	 * @param $args
	 */
	protected function do_field_select( $name, $id, $value, $args ) {
		$options = $this->get_options( $args );
		$class   = isset( $args['class'] ) ? sprintf( ' class=%s', $args['class'] ) : '';
		$aria    = $name;
		if ( isset( $args['label'] ) ) {
			$aria = $args['label'];
		} elseif ( isset( $args['title'] ) ) {
			$aria = $args['title'];
		}
		printf( '<select id="%1$s" name="%2$s" aria-label="%5$s"%3$s%4$s>',
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( $class ),
			esc_attr( $this->required( $args ) ),
			esc_attr( $aria )
		);
		foreach ( (array) $options as $option => $field_label ) {
			printf( '<option value="%s" %s>%s</option>',
				esc_attr( $option ),
				selected( $option, $value, false ),
				esc_attr( $field_label )
			);
		}
		echo '</select>';
	}

	/**
	 * Build a text field.
	 *
	 * @param $name
	 * @param $id
	 * @param $value
	 * @param $args
	 */
	protected function do_field_text( $name, $id, $value, $args ) {
		$defaults = array(
			'class' => 'regular-text',
		);
		$args = wp_parse_args( $args, $defaults );
		if ( 'custom_date' === $args['class'] ) {
			$value = $value ? date( 'n/j/Y', $value ) : '';
		}

		printf( '<input type="text" id="%3$s" aria-label="%1$s" name="%1$s" value="%2$s" class="%4$s"%5$s%6$s/>',
			esc_attr( $name ),
			esc_attr( $value ),
			esc_attr( $id ),
			esc_attr( $args['class'] ),
			esc_attr( $this->required( $args ) ),
			$this->get_data_attribute( $args )
		);
	}

	/**
	 * Build a textarea field.
	 *
	 * @param $name
	 * @param $id
	 * @param $value
	 * @param $args
	 */
	protected function do_field_textarea( $name, $id, $value, $args ) {
		$rows = isset( $args['rows'] ) ? $args['rows'] : 3;
		printf( '<textarea class="large-text" rows="%4$s" id="%1$s" name="%2$s" aria-label="%5$s"%6$s>%3$s</textarea>',
			esc_attr( $id ),
			esc_attr( $name ),
			sanitize_textarea_field( $value ),
			(int) $rows,
			esc_attr( $args['label'] ),
			esc_attr( $this->required( $args ) )
		);
	}

	/**
	 * Build a wysiwyg field.
	 *
	 * @param $name
	 * @param $id
	 * @param $value
	 * @param $args
	 */
	protected function do_field_wysiwyg( $name, $id, $value, $args ) {
		$custom_args = isset( $args['args'] ) ? $args['args'] : array();
		$editor_args = wp_parse_args( $custom_args, array(
			'textarea_name' => $name,
			'media_buttons' => true,
			'textarea_rows' => 10,
		) );
		wp_editor( $value, $id, $editor_args );
	}

	/**
	 * If a field is required, this outputs the markup for it.
	 *
	 * @param $args
	 *
	 * @return string
	 */
	protected function required( $args ) {
		return isset( $args['required'] ) && $args['required'] ? ' required' : '';
	}

	/**
	 * Helper function to get the array of options/choices
	 * for multiselect, checkbox array, and select fields.
	 *
	 * @since 2.0.0
	 *
	 * @param $args
	 *
	 * @return array
	 */
	protected function get_options( $args ) {
		$options = isset( $args['options'] ) ? $args['options'] : array();
		if ( isset( $args['choices'] ) ) {
			$options = $args['choices'];
		}
		$screen  = get_current_screen();
		if ( 'post' !== $screen->base && ( isset( $args['options'] ) && is_string( $args['options'] ) ) ) {
			$function = 'pick_' . $args['options'];
			if ( method_exists( $this, $function ) ) {
				$options = $this->$function();
			}
		}

		return $options;
	}

	/**
	 * Build up the localization data.
	 *
	 * @param $args
	 *
	 * @return array
	 */
	protected function get_localization_data( $args ) {
		return array(
			'text' => __( 'Select ', 'sixtenpress' ) . $args['label'],
			'type' => isset( $args['library'] ) ? $args['library'] : array( 'audio', 'video', 'image', 'application' ),
		);
	}

	/**
	 * Allow plugins to add custom data attributes to an input field.
	 * @since 2.0.0
	 *
	 * @param $args
	 *
	 * @return string
	 */
	protected function get_data_attribute( $args ) {
		if ( ! isset( $args['data'] ) || ! $args['data'] ) {
			return '';
		}
		if ( is_string( $args['data'] ) ) {
			return ' ' . $args['data'];
		}
		$output = '';
		foreach ( $args['data'] as $key => $value ) {
			$output .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
		}

		return $output;
	}
}
