<?php
/**
 * Main plugin class.
 * @package   SixTenPressIsotope
 * @author    Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @link      http://robincornett.com
 * @copyright 2016 Robin Cornett Creative, LLC
 */
class SixTenPressIsotope {

	/**
	 * The output class.
	 * @var $output SixTenPressIsotopeOutput
	 */
	protected $output;

	/**
	 * SixTenPressIsotope constructor.
	 *
	 * @param $settings
	 */
	public function __construct( $output ) {
		$this->output = $output;
	}

	/**
	 * Check for post type support, etc.
	 */
	public function run() {
		add_action( 'plugins_loaded', array( $this, 'load_settings_page' ), 20 );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'pre_get_posts', array( $this->output, 'maybe_add_post_type_support' ), 999 );
		add_action( 'template_redirect', array( $this->output, 'maybe_do_isotope' ) );
	}

	/**
	 * Check for settings/licensing classes and create the settings page.
	 */
	public function load_settings_page() {
		if ( ! class_exists( 'SixTenPressSettings' ) ) {
			include_once plugin_dir_path( __FILE__ ) . 'common/class-sixtenpress-settings.php';
		}
		if ( ! class_exists( 'SixTenPressLicensing' ) ) {
			include_once plugin_dir_path( __FILE__ ) . 'common/class-sixtenpress-licensing.php';
		}
		$files = array( 'page' );
		foreach ( $files as $file ) {
			include_once plugin_dir_path( __FILE__ ) . "class-sixtenpressisotope-settings-{$file}.php";
		}

		$settings = new SixTenPressIsotopeSettings();
		add_action( 'admin_menu', array( $settings, 'do_submenu_page' ) );
		add_filter( 'sixtenpressisotope_get_plugin_setting', array( $settings, 'get_setting' ) );
	}

	/**
	 * Set up text domain for translations
	 *
	 * @since 1.1.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'sixtenpress-isotope', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}
