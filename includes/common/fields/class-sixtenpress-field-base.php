<?php

/**
 * This is the base class for building Six/Ten Press fields.
 *
 * Class SixTenPressFieldBase
 * @copyright 2018-2019 Robin Cornett
 */
class SixTenPressFieldBase {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var string|bool|int|array|mixed
	 */
	protected $value;

	/**
	 * @var array
	 */
	protected $field;

	/**
	 * SixTenPressFieldBase constructor.
	 *
	 * @param $name
	 * @param $id
	 * @param $value
	 * @param $field
	 */
	public function __construct( $name, $id, $value, $field ) {
		$this->name  = $name;
		$this->id    = $id;
		$this->value = $value;
		$this->field = $field;
	}
}
