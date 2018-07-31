<?php

/**
 * Class SixTenPressSettingsGetter
 */
class SixTenPressSettingsGetter {

	/**
	 * @var string
	 */
	protected $prefix;

	/**
	 * @var array
	 */
	protected $setting;

	/**
	 * SixTenPressCustomFieldsFormGetter constructor.
	 *
	 * @param $prefix
	 * @param $setting
	 *
	 */
	public function __construct( $prefix, $setting ) {
		$this->prefix  = $prefix;
		$this->setting = $setting;
	}

	/**
	 * Get the current field name.
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function get_field_name( $args ) {
		return isset( $args['key'] ) && $args['key'] ? $this->prefix . '[' . $args['key'] . '][' . $args['setting'] . ']' : $this->prefix . '[' . $args['id'] . ']';
	}

	/**
	 * Get the current field id.
	 *
	 * @param $args
	 *
	 * @return string
	 *
	 */
	public function get_field_id( $args ) {
		return isset( $args['key'] ) && $args['key'] ? $this->prefix . '-' . $args['key'] . '-' . $args['setting'] : $this->prefix . '-' . $args['id'];
	}

	/**
	 * Get the current field value.
	 *
	 * @param $args
	 *
	 * @return mixed
	 * @internal param $setting
	 *
	 */
	public function get_field_value( $args ) {
		if ( isset( $args['key'] ) && $args['key'] ) {
			$value = isset( $this->setting[ $args['key'] ][ $args['setting'] ] ) ? $this->setting[ $args['key'] ][ $args['setting'] ] : 0;
		} else {
			$value = isset( $this->setting[ $args['id'] ] ) ? $this->setting[ $args['id'] ] : '';
		}

		return $value;
	}
}
