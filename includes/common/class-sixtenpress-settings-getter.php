<?php

/**
 * Class SixTenPressSettingsGetter
 * @copyright 2018-2019 Robin Cornett
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
	 * Get the field name.
	 *
	 * @param $args
	 * @param string $i
	 * @param array $parent
	 *
	 * @return string
	 */
	public function get_name( $args, $i = '', $parent = array() ) {
		$name = $this->get_field_name( $args );
		if ( $parent ) {
			$name = $this->get_group_field_name( $args, $parent['id'], $i, $parent );
		} elseif ( $this->is_repeatable( $args ) ) {
			$name = $this->get_repeater_field_name( $args['id'], $i );
		}

		return $name;
	}

	/**
	 * Get the field ID.
	 *
	 * @param $args
	 * @param string $i
	 * @param array $parent
	 *
	 * @return string
	 */
	public function get_id( $args, $i = '', $parent = array() ) {
		$id = $this->get_field_id( $args );
		if ( $parent ) {
			$id = $this->get_group_field_id( $args, $parent['id'], $i, $parent );
		} elseif ( $this->is_repeatable( $args ) ) {
			$id = $this->get_repeater_field_id( $args['id'], $i );
		}

		return $id;
	}

	/**
	 * Get the field value.
	 * 
	 * @param $args
	 * @param string $i
	 * @param array $parent
	 *
	 * @return mixed|string
	 */
	public function get_value( $args, $i = '', $parent = array() ) {
		$value = $this->get_field_value( $args );
		if ( $parent ) {
			$value = $this->get_group_field_value( $args, $parent['id'], $i, $parent );
		} elseif ( $this->is_repeatable( $args ) ) {
			$value = $this->get_repeater_field_value( $args['id'], $i );
		}

		return $value;
	}

	/**
	 * Get the current field name.
	 *
	 * @param  array $args
	 *
	 * @return string
	 */
	public function get_field_name( $args ) {
		return isset( $args['key'] ) && $args['key'] ? $this->prefix . '[' . $args['key'] . '][' . $args['id'] . ']' : $this->prefix . '[' . $args['id'] . ']';
	}

	/**
	 * Get the repeater field name.
	 *
	 * @param $setting
	 * @param $i
	 *
	 * @return string
	 */
	public function get_repeater_field_name( $setting, $i ) {
		return $this->prefix . '[' . $setting . '][' . $i . ']';
	}

	/**
	 * Get the current group field name.
	 *
	 * @param $setting
	 * @param $parent
	 * @param $i
	 * @param $args
	 *
	 * @return string
	 *
	 * @since 2.0.0
	 */
	public function get_group_field_name( $setting, $parent, $i, $args ) {
		return $this->is_repeatable( $args ) ? $this->prefix . '[' . $parent . '][' . $i . '][' . $setting['id'] . ']' : $this->prefix . '[' . $parent . '][' . $setting['id'] . ']';
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
		return ! empty( $args['key'] ) ? $this->prefix . '-' . $args['key'] . '-' . $args['id'] : $this->prefix . '-' . $args['id'];
	}

	/**
	 * Get the repeater field ID.
	 *
	 * @param $setting
	 * @param $i
	 *
	 * @return string
	 */
	public function get_repeater_field_id( $setting, $i ) {
		return $this->prefix . '-' . $setting . '-' . $i;
	}

	/**
	 * Get the group field id.
	 *
	 * @param $setting
	 * @param $index
	 * @param $i
	 * @param $args
	 *
	 * @return string
	 * @since 2.0.0
	 */
	public function get_group_field_id( $setting, $index, $i, $args ) {
		return $this->is_repeatable( $args ) ? $this->prefix . '-' . $index . '-' . $i . '-' . $setting['id'] : $this->prefix . '-' . $index . '-' . $setting['id'];
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
		if ( ! empty( $args['key'] ) ) {
			$value = isset( $this->setting[ $args['key'] ][ $args['id'] ] ) ? $this->setting[ $args['key'] ][ $args['id'] ] : 0;
		} else {
			$value = isset( $this->setting[ $args['id'] ] ) ? $this->setting[ $args['id'] ] : '';
		}

		return $value;
	}

	/**
	 * Get the value of a repeater field.
	 *
	 * @param $args
	 * @param $i
	 *
	 * @return mixed
	 */
	public function get_repeater_field_value( $args, $i ) {
		return ! empty( $this->setting[ $args ][ $i ] ) ? $this->setting[ $args ][ $i ] : '';
	}

	/**
	 * Get the group field value.
	 *
	 * @param $setting
	 * @param $index
	 * @param $i
	 * @param $args
	 *
	 * @return string
	 */
	public function get_group_field_value( $setting, $index, $i, $args ) {
		$default = isset( $setting['default'] ) ? $setting['default'] : '';

		$value = isset( $this->setting[ $index ][ $i ][ $setting['id'] ] ) ? $this->setting[ $index ][ $i ][ $setting['id'] ] : $default;
		if ( ! $this->is_repeatable( $args ) ) {
			$value = isset( $this->setting[ $index ][ $setting['id'] ] ) ? $this->setting[ $index ][ $setting['id'] ] : $default;
		}

		return $value;
	}

	/**
	 * Check field args to see if a field is repeatable.
	 *
	 * @param $args
	 *
	 * @return bool
	 */
	protected function is_repeatable( $args ) {
		return (bool) ( isset( $args['repeatable'] ) && $args['repeatable'] );
	}
}
