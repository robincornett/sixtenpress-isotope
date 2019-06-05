<?php
/**
 * Class SixTenPressFieldsEnqueue
 * @copyright 2019 Robin Cornett
 */
class SixTenPressFieldsEnqueue {

	/**
	 * All fields (from $this->fields or _get()).
	 * @var $fields array
	 */
	protected $fields;

	/**
	 * SixTenPressCustomFieldsFieldEnqueue constructor.
	 *
	 * @param $fields
	 */
	public function __construct( $fields ) {
		$this->fields = $fields;
	}

	/**
	 * Enqueue our admin scripts.
	 */
	public function enqueue() {
		$scripts = array(
			array(
				'key'    => 'type',
				'value'  => 'file',
				'filter' => 'uploader',
			),
			array(
				'key'    => 'type',
				'value'  => 'image',
				'filter' => 'uploader',
			),
			array(
				'key'    => 'format',
				'value'  => 'date',
				'filter' => 'date_picker',
			),
			array(
				'key'    => 'type',
				'value'  => 'color',
				'filter' => 'color_picker',
			),
			array(
				'key'    => 'repeatable',
				'value'  => true,
				'filter' => 'post_meta',
			),
		);
		foreach ( $scripts as $script ) {
			if ( $this->check_field_keys( $script['key'], $script['value'] ) ) {
				add_filter( "sixtenpress_admin_{$script['filter']}", '__return_true' );
				if ( 'post_meta' === $script['filter'] ) {
					add_filter( 'sixtenpress_admin_style', '__return_true' );
				}
			}
		}
	}

	/**
	 * Helper function to check custom fields and see if various scripts should be loaded.
	 *
	 * @since 2.0.0
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return bool
	 */
	protected function check_field_keys( $key, $value ) {
		foreach ( $this->fields as $field ) {
			if ( isset( $field[ $key ] ) && $field[ $key ] === $value ) {
				return true;
			}

			if ( empty( $field['type'] ) || 'group' !== $field['type'] ) {
				continue;
			}
			foreach ( $field['group'] as $group_field ) {
				if ( isset( $group_field[ $key ] ) && $group_field[ $key ] === $value ) {
					return true;
				}
			}
		}

		return false;
	}
}
