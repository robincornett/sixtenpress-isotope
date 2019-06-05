<?php
/**
 * @package SixTenPress
 * @copyright 2016-2019 Robin Cornett
 */
class SixTenPressSettingsSanitize {

	/**
	 * The plugin settings page.
	 * @var string $page
	 */
	protected $page;

	/**
	 * The plugin setting.
	 * @var $setting
	 */
	protected $setting;

	/**
	 * The settings fields.
	 * @var $fields
	 */
	protected $fields;

	/**
	 * The registered setting/option name.
	 * @var string
	 */
	protected $option;

	/**
	 * @var \SixTenPressSanitizeSwitcher
	 */
	protected $switcher;

	/**
	 * SixTenPressSettingsSanitize constructor.
	 *
	 * @param $fields
	 * @param $setting
	 * @param $page
	 * @param $option
	 */
	public function __construct( $fields, $setting, $page, $option ) {
		$this->fields  = $fields;
		$this->setting = $setting;
		$this->page    = $page;
		$this->option  = $option;
	}

	/**
	 * Default settings validation method.
	 * @param $new_value
	 *
	 * @return array
	 */
	public function sanitize( $new_value ) {

		$new_value = array_merge( $this->setting, $new_value );
		$old_value = $this->setting;
		if ( ! $this->fields ) {
			return $old_value;
		}

		foreach ( $this->fields as $field ) {
			if ( ! isset( $field['type'] ) ) {
				$field = $this->sanitize_callback_switcher( $field );
			}
			$switcher                  = $this->get_switcher();
			$old_field_value           = isset( $old_value[ $field['id'] ] ) ? $old_value[ $field['id'] ] : '';
			$new_value[ $field['id'] ] = $switcher->sanitize_switcher( $new_value[ $field['id'] ], $field, $old_field_value );
			$new_value[ $field['id'] ] = apply_filters( "sixtenpress_sanitize_{$this->page}_{$field['id']}", $new_value[ $field['id'] ], $field, $new_value, $old_field_value );
			$new_value[ $field['id'] ] = apply_filters( "sixtenpress_sanitize_{$this->option}_{$field['id']}", $new_value[ $field['id'] ], $field, $new_value, $old_field_value );
		}

		do_action( "sixtenpress_after_sanitize_{$this->option}", $this->option, $new_value, $old_value );

		return $new_value;
	}

	/**
	 * Switch through field callbacks and add types for updated sanitization.
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	protected function sanitize_callback_switcher( $field ) {
		switch ( $field['callback'] ) {
			case 'set_image':
				$field['type'] = 'image';
				break;

			case 'set_color':
				$field['type'] = 'color';
				break;

			case 'do_checkbox':
				$field['type'] = 'checkbox';
				break;

			case 'do_number':
				$field['type'] = 'number';
				break;

			case 'do_select':
				$field['type'] = 'select';
				break;

			case 'do_checkbox_array':
				$field['type'] = 'checkbox_array';
				break;

			case 'do_text_field':
				$field['type'] = 'text_field';
				break;

			case 'do_wysiwyg':
				$field['type'] = 'wysiwyg';
				break;

			default:
				$field['type'] = '';
				break;
		} // End switch(). phpcs:ignore Squiz.PHP.CommentedOutCode.Found
		return $field;
	}

	/**
	 * @return \SixTenPressSanitizeSwitcher
	 *
	 * @since 2.0.0
	 */
	protected function get_switcher() {
		if ( isset( $this->switcher ) ) {
			return $this->switcher;
		}
		if ( ! class_exists( 'SixTenPressAutoloader' ) ) {
			include_once trailingslashit( plugin_dir_path( __FILE__ ) ) . 'class-sixtenpress-sanitize-switcher.php';
		}
		$this->switcher = new SixTenPressSanitizeSwitcher();

		return $this->switcher;
	}
}
