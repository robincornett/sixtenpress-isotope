<?php

/**
 * Generic licensing class to work with EDD Software Licensing.
 * @copyright 2016-2017 Robin Cornett
 */
class SixTenPressLicensing extends SixTenPressSettings {

	/**
	 * Current plugin version
	 * @var string $version
	 */
	public $version;

	/**
	 * Licensing page/setting
	 * @var string $page
	 */
	protected $page;

	/**
	 * Key for plugin setting base
	 * @var string
	 */
	protected $key;

	/**
	 * Array of fields for licensing
	 * @var $fields
	 */
	protected $fields;

	/**
	 * License key
	 * @var $license
	 */
	protected $license;

	/** License status
	 * @var $status
	 */
	protected $status;

	/**
	 * License data for this site (expiration date, latest version)
	 * @var $data
	 */
	protected $data = false;

	/**
	 * Store URL for Easy Digital Downloads.
	 * @var string
	 */
	protected $url;

	/**
	 * Plugin name for EDD.
	 * @var string
	 */
	protected $name;

	/**
	 * Plugin slug for license check.
	 * @var string
	 */
	protected $slug;

	/**
	 * The current plugin's basename.
	 * @var $basename
	 */
	protected $basename;

	/**
	 * The plugin author.
	 * @var $author
	 */
	protected $author;

	/**
	 * Action for our custom nonce.
	 * @var string $action
	 */
	protected $action = 'sixtenpress_save-settings';

	/**
	 * Custom nonce.
	 * @var string $nonce
	 */
	protected $nonce = 'sixtenpress_nonce';

	/**
	 * Set up EDD licensing updates
	 * @since 1.4.0
	 */
	public function updater() {

		if ( is_multisite() && ! is_main_site() ) {
			return;
		}

		if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
			// load our custom updater if it doesn't already exist
			include plugin_dir_path( __FILE__ ) . 'class-eddpluginupdater.php';
		}

		$edd_updater = new EDD_SL_Plugin_Updater( $this->url, $this->basename, array(
			'version'   => $this->version,
			'license'   => trim( $this->license ),
			'item_name' => $this->name,
			'author'    => $this->author,
			'url'       => home_url(),
		) );

		$this->activate_license();
		$this->deactivate_license();
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
				'title' => __( 'Six/Ten Press License Key(s)', 'sixtenpress' ),
			),
		);
	}

	/**
	 * License key input field
	 *
	 * @param  array $args parameters to define field
	 *
	 * @since 1.4.0
	 */
	public function do_license_key_field( $args ) {
		if ( 'valid' === $this->status ) {
			$style = 'color:white;background-color:green;border-radius:100%;margin-right:8px;vertical-align:middle;';
			printf( '<span class="dashicons dashicons-yes" style="%s"></span>',
				esc_attr( $style )
			);
		}
		printf( '<input type="password" class="regular-text" id="%1$s" name="%1$s" value="%2$s" />',
			esc_attr( $args['setting'] ),
			esc_attr( $this->license )
		);
		if ( ! empty( $this->license ) && 'valid' === $this->status ) {
			$this->add_deactivation_button();
		}
		if ( 'valid' === $this->status ) {
			return;
		}
		if ( ! $this->is_sixten_active() ) {
			$this->add_activation_button();
		}
		printf( '<p class="description"><label for="%3$s[%1$s]">%2$s</label></p>', esc_attr( $args['setting'] ), esc_html( $args['label'] ), esc_attr( $this->page ) );
	}

	/**
	 * License activation button
	 */
	protected function add_activation_button() {

		if ( 'valid' === $this->status ) {
			return;
		}

		$this->print_button(
			'button-primary',
			'sixtenpress_activate',
			__( 'Activate', 'sixtenpress' )
		);
	}

	/**
	 * License deactivation button
	 */
	protected function add_deactivation_button() {

		if ( 'valid' !== $this->status ) {
			return;
		}

		$this->print_button(
			'button-secondary',
			$this->key . '_deactivate',
			__( 'Deactivate', 'sixtenpress' )
		);
	}

	/**
	 * Sanitize license key
	 *
	 * @param  string $new_value license key
	 *
	 * @return string license key
	 *
	 * @since 1.4.0
	 */
	public function sanitize_license( $new_value ) {
		$license = get_option( $this->key . '_key' );
		if ( ( $license && $license !== $new_value ) || empty( $new_value ) ) {
			delete_option( $this->key . '_status' );
		}
		if ( $license !== $new_value || 'valid' !== $this->status ) {
			$this->activate_license( $new_value );
		}

		return sanitize_text_field( $new_value );
	}

	/**
	 * Activate plugin license
	 *
	 * @param  string $new_value entered license key
	 *
	 * @uses  do_remote_request()
	 *
	 * @since 1.4.0
	 */
	protected function activate_license( $new_value = '' ) {

		if ( 'valid' === $this->status ) {
			return;
		}

		// listen for our activate button to be clicked
		if ( empty( $_POST['sixtenpress_activate'] ) ) {
			return;
		}

		// If the user doesn't have permission to save, then display an error message
		if ( ! $this->user_can_save( $this->action, $this->nonce ) ) {
			wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'sixtenpress' ) );
		}

		// run a quick security check
		if ( ! check_admin_referer( $this->action, $this->nonce ) ) {
			return; // get out if we didn't click the Activate button
		}

		// retrieve the license from the database
		$license = trim( $this->license );
		$license = $new_value !== $license ? trim( $new_value ) : $license;

		if ( empty( $license ) || empty( $new_value ) ) {
			delete_option( $this->key . '_status' );

			return;
		}

		// data to send in our API request
		$api_params   = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => rawurlencode( $this->name ), // the name of our product in EDD
			'url'        => esc_url( home_url() ),
		);
		$license_data = $this->do_remote_request( $api_params );
		$status       = 'invalid';
		if ( $license_data ) {
			$status = $license_data->license;
			if ( false === $license_data->success ) {
				$status = $license_data->error;
			}
		}

		// $license_data->license will be either "valid" or "invalid"
		update_option( $this->key . '_status', $status );
	}

	/**
	 * Deactivate license
	 * @uses  do_remote_request()
	 *
	 * @since 1.4.0
	 */
	protected function deactivate_license() {

		// listen for our activate button to be clicked
		if ( empty( $_POST[ $this->key . '_deactivate' ] ) ) {
			return;
		}

		// If the user doesn't have permission to save, then display an error message
		if ( ! $this->user_can_save( $this->action, $this->nonce ) ) {
			wp_die( esc_attr__( 'Something unexpected happened. Please try again.', 'sixtenpress' ) );
		}

		// run a quick security check
		if ( ! check_admin_referer( $this->action, $this->nonce ) ) {
			return; // get out if we didn't click the Activate button
		}

		// retrieve the license from the database
		$license = trim( $this->license );

		// data to send in our API request
		$api_params   = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_name'  => rawurlencode( $this->name ), // the name of our product in EDD
			'url'        => home_url(),
		);
		$license_data = $this->do_remote_request( $api_params );

		// $license_data->license will be either "deactivated" or "failed"
		if ( is_object( $license_data ) && 'deactivated' === $license_data->license ) {
			delete_option( $this->key . '_status' );
		}
	}

	/**
	 * Weekly cron job to compare activated license with the server.
	 * @uses  check_license()
	 * @since 2.0.0
	 */
	public function weekly_license_check() {
		if ( apply_filters( 'sixtenpress_skip_license_check', false ) ) {
			return;
		}

		if ( ! empty( $_POST[ $this->nonce ] ) ) {
			return;
		}

		$license = get_option( $this->page . '_key', '' );
		if ( empty( $license ) ) {
			delete_option( $this->key . '_status' );

			return;
		}

		$license_data = $this->check_license( $license );
		$status       = 'invalid';
		if ( $license_data ) {
			$status = $license_data->license;
			if ( false === $license_data->success ) {
				$status = $license_data->error;
			}
			$this->update_data_option( $license_data );
		}
		if ( $status !== $this->status ) {
			// Update local plugin status
			update_option( $this->key . '_status', $status );
		}
	}

	/**
	 * Update the plugin data setting.
	 *
	 * @param $license_data
	 */
	protected function update_data_option( $license_data ) {
		$data_setting = $this->key . '_data';
		if ( ! isset( $this->data['expires'] ) || $license_data->expires !== $this->data['expires'] ) {
			$this->update_settings( array(
				'expires' => $license_data->expires,
			), $data_setting );
		}

		if ( 'valid' === $license_data->license ) {
			return;
		}

		$latest_version = $this->get_latest_version();
		if ( ! isset( $this->data['latest_version'] ) || $latest_version !== $this->data['latest_version'] ) {
			$this->update_settings( array(
				'latest_version' => $latest_version,
			), $data_setting );
		}
	}

	/**
	 * Check plugin license status
	 * @uses  do_remote_request()
	 *
	 * @param string $license
	 *
	 * @return mixed data
	 ** @since 1.4.0
	 */
	protected function check_license( $license = '' ) {

		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => empty( $license ) ? $this->license : $license,
			'item_name'  => rawurlencode( $this->name ), // the name of our product in EDD
			'url'        => esc_url( home_url() ),
		);
		if ( empty( $api_params['license'] ) ) {
			return '';
		}

		return $this->do_remote_request( $api_params );
	}

	/**
	 * Get the latest plugin version.
	 * @uses  do_remote_request()
	 * @return mixed
	 *
	 * @since 2.0.0
	 */
	protected function get_latest_version() {
		$api_params = array(
			'edd_action' => 'get_version',
			'item_name'  => $this->name,
			'slug'       => $this->slug,
		);
		$request    = $this->do_remote_request( $api_params );

		if ( $request && isset( $request->sections ) ) {
			$request->sections = maybe_unserialize( $request->sections );
		} else {
			return false;
		}

		return $request->new_version;
	}

	/**
	 * Send the request to the remote server.
	 *
	 * @param $api_params
	 *
	 * @param int $timeout
	 *
	 * @return array|bool|mixed|object
	 * @since 2.0.0
	 */
	private function do_remote_request( $api_params, $timeout = 15 ) {
		$response = wp_remote_post( $this->url, array(
			'timeout'   => $timeout,
			'sslverify' => false,
			'body'      => $api_params,
		) );
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Print error messages.
	 *
	 * @since 1.4.0
	 *
	 * @param $message
	 * @param string $class
	 */
	protected function do_error_message( $message, $class = '' ) {
		if ( empty( $message ) ) {
			return;
		}
		printf( '<div class="notice %s">%s</div>', esc_attr( $class ), wp_kses_post( $message ) );
	}

	/**
	 * Convert a date string to a pretty format.
	 *
	 * @param $args
	 * @param string $before
	 * @param string $after
	 *
	 * @return string
	 */
	protected function pretty_date( $args, $before = '', $after = '' ) {
		$date_format = isset( $args['date_format'] ) ? $args['date_format'] : get_option( 'date_format' );

		return $before . date_i18n( $date_format, $args['field'] ) . $after;
	}

	/**
	 * Boolean to trigger the default 6/10 press error message.
	 *
	 * @param $error
	 *
	 * @return bool
	 * @since 1.1.2
	 */
	public function sixtenpress_error_message( $error ) {
		if ( 'valid' === $this->status ) {
			return $error;
		}

		return true;
	}
}
