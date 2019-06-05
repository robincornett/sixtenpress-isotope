<?php
/**
 * @package SixTenpress
 * @copyright 2019 Robin Cornett
 */

if ( ! class_exists( 'SixTenPressLicensing' ) ) {
	include_once 'class-sixtenpress-licensing.php';
}

/**
 * Class to initialize the Six/Ten Press Licensing class more easily.
 *
 * Class SixTenPressLicensingInit
 */
class SixTenPressLicensingInit extends SixTenPressLicensing {

	/**
	 * SixTenPressLicensingInit constructor.
	 *
	 * @param $args
	 */
	public function __construct( $args ) {
		$args = wp_parse_args( $args, $this->defaults() );
		if ( ! $args['basename'] ) {
			return;
		}
		$this->version  = $args['version'];
		$this->name     = $args['name'];
		$this->slug     = $args['slug'];
		$this->page     = $args['page'];
		$this->key      = $args['key'];
		$this->basename = $args['basename'];
		$this->url      = $args['url'];
		$this->author   = $args['author'];

		add_action( 'admin_init', array( $this, 'set_up_licensing' ) );
	}

	/**
	 * @return array
	 */
	private function defaults() {
		return array(
			'version'  => false,
			'name'     => 'sixtenpress',
			'slug'     => false,
			'page'     => 'sixtenpress',
			'key'      => 'sixtenpress',
			'basename' => false,
			'url'      => 'https://robincornett.com/',
			'author'   => 'Robin Cornett',
		);
	}

	/**
	 * Function to set up licensing fields and call the updater.
	 */
	public function set_up_licensing() {
		if ( ! $this->is_sixten_active() ) {
			$this->page   = $this->key;
			$this->action = $this->key . '_save-settings';
			$this->nonce  = $this->key . '_nonce';
		}
		$this->license = get_option( $this->key . '_key', '' );
		$this->status  = get_option( $this->key . '_status', false );
		$this->data    = get_option( $this->key . '_data', false );
		$this->fields  = $this->register_fields();
		$this->register_settings();
		$this->updater();
		add_action( "load-settings_page_{$this->page}", array( $this, 'build_settings_page' ), 20 );
		if ( $this->is_sixten_active() ) {
			add_action( 'sixtenpress_weekly_events', array( $this, 'weekly_license_check' ) );
			add_filter( 'sixtenpress_licensing_error_message', array( $this, 'sixtenpress_error_message' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'select_error_message' ) );
		}
	}

	/**
	 * Build the licensing settings page.
	 */
	public function build_settings_page() {
		$sections = $this->is_sixten_active() ? $this->register_section() : array(
			$this->key => array(
				'id' => $this->key,
			),
		);
		$this->add_fields( $this->fields, $sections );
	}

	/**
	 * Register plugin license settings and fields
	 * @since 1.4.0
	 */
	public function register_settings() {
		$options_group = $this->is_sixten_active() ? $this->page . 'licensing' : $this->key;
		register_setting( $options_group, $this->key . '_key', array( $this, 'sanitize_license' ) );
	}

	/**
	 * Register the licensing section.
	 * @return array
	 */
	protected function register_section() {
		return array(
			'licensing' => array(
				'id'    => 'licensing',
				'tab'   => 'licensing',
				'title' => __( 'License', 'sixtenpress' ),
			),
		);
	}
}
