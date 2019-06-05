<?php

/**
 * Class SixTenPressField
 * @copyright 2019 Robin Cornett
 */
class SixTenPressField {

	/**
	 * The custom field or setting prefix.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * @param array  $field
	 * @param string $name
	 * @param string $id
	 * @param mixed  $value
	 * @param $getter
	 * @param bool   $group
	 */
	protected function pick_field( $field, $name, $id, $value, $getter, $group = false ) {
		include_once plugin_dir_path( __FILE__ ) . 'fields/class-sixtenpress-field-base.php';
		$file = plugin_dir_path( __FILE__ ) . "fields/class-sixtenpress-field-{$field['type']}.php";
		if ( file_exists( $file ) ) {
			$field['required'] = $this->required( $field );
			include_once $file;
			$class = 'SixTenPressField' . ucfirst( $field['type'] );
			if ( 'checkbox_array' === $field['type'] ) {
				$class = 'SixTenPressFieldCheckboxArray';
			}
			$init = false;
			if ( class_exists( $class ) && is_callable( $class, 'do_field' ) ) {
				if ( in_array( $field['type'], array( 'checkbox_array', 'multiselect', 'select' ), true ) ) {
					$field['options'] = $this->get_options( $field );
				}
				$init = new $class( $name, $id, $value, $field );
			}
			if ( $init ) {
				if ( in_array( $field['type'], array( 'file' ), true ) ) {
					call_user_func( array( $init, 'do_field' ), $getter, $group );

					return;
				}
				call_user_func( array( $init, 'do_field' ) );

				return;
			}
		}
		$method = "do_field_{$field['type']}";
		if ( is_callable( array( $this, $method ) ) ) {
			call_user_func( array( $this, $method ), $name, $id, $value, $field );

			return;
		}
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
		$screen = get_current_screen();
		if ( 'post' !== $screen->base && ( is_string( $options ) ) ) {
			$function = "pick_{$options}";
			if ( method_exists( $this, $function ) ) {
				$options = $this->$function();
			}
		}
		if ( is_callable( $options ) ) {
			$options = call_user_func( $options );
		}

		return $options;
	}

	/**
	 * Generic callback to display a field description.
	 *
	 * @param  array $field setting used to identify description callback
	 *
	 * @since 1.0.0
	 */
	protected function do_description( $field ) {
		$id          = empty( $field['id'] ) ? $field['setting'] : $field['id'];
		$description = apply_filters( "sixtenpress_settings_{$this->prefix}_{$id}_description", $this->get_description( $field ), $field );
		if ( ! $description ) {
			return;
		}
		printf( '<p class="description">%s</p>', wp_kses_post( $description ) );
	}

	/**
	 * Get the field description somehow.
	 * @param $field
	 *
	 * @return mixed|string
	 */
	private function get_description( $field ) {
		$description = empty( $field['description'] ) ? false : $field['description'];
		if ( is_callable( $description ) ) {
			$description = call_user_func( $description );
		}
		if ( ! $description && ! empty( $field['id'] ) ) {
			$function = "{$field['id']}_description";
			if ( method_exists( $this, $function ) ) {
				$description = $this->$function();
			}
		}

		return $description;
	}
}
