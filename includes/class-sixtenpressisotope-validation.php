<?php

/**
 * Class for validating SixTenPress Isotope settings.
 * @package   SixTenPressIsotope
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 * @copyright 2016 Robin Cornett Creative, LLC
 */
class SixTenPressIsotopeValidation {

	/**
	 * @var $page
	 */
	protected $page;

	/**
	 * @var $setting
	 */
	protected $setting;

	/**
	 * @var $fields
	 */
	protected $fields;

	/**
	 * @var $post_types
	 */
	protected $post_types;

	/**
	 * SixTenPressIsotopeValidation constructor.
	 *
	 * @param $page
	 * @param $setting
	 * @param $fields
	 * @param $post_types
	 */
	public function __construct( $page, $setting, $fields, $post_types ) {
		$this->page       = $page;
		$this->setting    = $setting;
		$this->fields     = $fields;
		$this->post_types = $post_types;
	}

	/**
	 * Validate all settings.
	 *
	 * @param  array $new_value new values from settings page
	 *
	 * @return array            validated values
	 *
	 * @since 1.0.0
	 */
	public function do_validation_things( $new_value ) {

		if ( empty( $_POST[$this->page . '_nonce'] ) ) {
			wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'sixtenpress-isotope' ) );
		}

		check_admin_referer( "{$this->page}_save-settings", "{$this->page}_nonce" );
		$diff = array_diff_key( $this->setting, $new_value );
		foreach ( $diff as $key => $value ) {
			if ( empty( $new_value[ $key ] ) ) {
				unset( $this->setting[ $key ] );
			}
		}
		$new_value = array_merge( $this->setting, $new_value );

		foreach ( $this->fields as $field ) {
			switch ( $field['callback'] ) {
				case 'do_checkbox':
					$new_value[ $field['id'] ] = $this->one_zero( $new_value[ $field['id'] ] );
					break;

				case 'do_select':
					$new_value[ $field['id'] ] = esc_attr( $new_value[ $field['id'] ] );
					break;

				case 'do_number':
					$new_value[ $field['id'] ] = (int) $new_value[ $field['id'] ];
					break;

				case 'do_checkbox_array':
					foreach ( $field['args']['options'] as $option ) {
						$new_value[ $field['id'] ][ $option['choice'] ] = $this->one_zero( $new_value[ $field['id'] ][ $option['choice'] ] );
					}
					break;
			}
		}
		foreach ( $this->post_types as $post_type ) {
			$new_value[ $post_type ]['support'] = $this->one_zero( $new_value[ $post_type ]['support'] );
			$new_value[ $post_type ]['gutter']  = (int) $new_value[ $post_type ]['gutter'];
			$taxonomies = $this->get_taxonomies( $post_type );
			foreach ( $taxonomies as $taxonomy ) {
				$new_value[ $post_type ][ $taxonomy ] = $this->one_zero( $new_value[ $post_type ][ $taxonomy ] );
			}
		}

		return $new_value;
	}

	/**
	 * Get the taxonomies registered to a post type.
	 * @param $post_type
	 *
	 * @return array
	 */
	protected function get_taxonomies( $post_type ) {
		$taxonomies = get_object_taxonomies( $post_type, 'names' );
		return 'post' === $post_type ? array( 'category' ) : $taxonomies;
	}

	/**
	 * Returns a 1 or 0, for all truthy / falsy values.
	 *
	 * Uses double casting. First, we cast to bool, then to integer.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $new_value Should ideally be a 1 or 0 integer passed in
	 *
	 * @return integer 1 or 0.
	 */
	protected function one_zero( $new_value ) {
		return (int) (bool) $new_value;
	}
}
