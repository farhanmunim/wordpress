<?php
namespace SG_Security\Rest;

use SG_Security\Options_Service\Options_Service;
use SG_Security\Message_Service\Message_Service;
use SG_Security\Sg_2fa\Sg_2fa;
use SG_Security\Login_Service\Login_Service;
use SG_Security\Usernames_Service\Usernames_Service;

/**
 * Rest Helper class that manages all of the options.
 */
class Rest_Helper_Options extends Rest_Helper {

	/**
	 * Local variables
	 *
	 * @var mixed
	 */
	public $options_service;
	public $sg_2fa;
	public $login_service;
	public $usernames_service;

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->options_service   = new Options_Service();
		$this->sg_2fa            = new Sg_2fa();
		$this->login_service     = new Login_Service();
		$this->usernames_service = new Usernames_Service();
	}

	/**
	 * Checks if the option key exists.
	 *
	 * @since  1.0.0
	 *
	 * @param  object $request Request data.
	 * @param  string $option  The option name.
	 */
	public function change_option_from_rest( $request, $option ) {
		$value  = $this->validate_and_get_option_value( $request, $option );
		$result = $this->change_option( $option, $value );

		// Set the response message.
		return self::send_response(
			Message_Service::get_response_message( $result, $option, $value ),
			$result,
			array(
				$option => $value,
			)
		);
	}

	/**
	 * Provide all plugin options.
	 *
	 * @since  1.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function fetch_options( $request ) {
		// Get the option key.
		$data          = json_decode( $request->get_body(), true );
		$login_type    = get_option( 'sg_security_login_type', 'default' );
		$login_allowed = get_option( 'users_can_register', false );

		$pages = array(
			'security' => array(
				'lock_system_folders' => intval( get_option( 'sg_security_lock_system_folders', 0 ) ),
				'disable_file_edit'   => intval( get_option( 'sg_security_disable_file_edit', 0 ) ),
				'wp_remove_version'   => intval( get_option( 'sg_security_wp_remove_version', 0 ) ),
				'disable_xml_rpc'     => intval( get_option( 'sg_security_disable_xml_rpc', 0 ) ),
				'disable_feed'        => intval( get_option( 'sg_security_disable_feed', 0 ) ),
				'xss_protection'      => intval( get_option( 'sg_security_xss_protection', 0 ) ),
				'xss_protection'      => intval( get_option( 'sg_security_xss_protection', 0 ) ),
				'delete_readme'       => intval( get_option( 'sg_security_delete_readme', 0 ) ),
			),
			'login'    => array(
				'sg2fa'             => intval( get_option( 'sg_security_sg2fa', 0 ) ),
				'reset_2fa'         => $this->sg_2fa->check_for_users_using_2fa(),
				'disable_usernames' => intval( get_option( 'sg_security_disable_usernames', 0 ) ),
				'usernames_data'    => $this->usernames_service->check_for_common_usernames(),
				'login_access'      => get_option( 'sg_login_access', array() ),
				'login_attempts'    => $this->prepare_options_selected_values( $this->login_service->login_attempts_data, intval( get_option( 'sg_security_login_attempts', 0 ) ) ),
				'login_url' => array(
					array(
						'type'     => 'custom',
						'label'    => 'Custom',
						'login'    => get_option( 'sg_security_login_url', '' ),
						'signup'   => 1 === intval( $login_allowed ) ? get_option( 'sg_security_login_register', '' ) : null,
						'selected' => 'custom' === $login_type ? 1 : 0,
					),
					array(
						'type'     => 'default',
						'label'    => 'Default',
						'login'    => wp_login_url(),
						'signup'   => 1 === intval( $login_allowed ) ? site_url( 'wp-signup.php' ) : null,
						'selected' => 'default' === $login_type ? 1 : 0,
					),
				),
			),
			'activity' => array(
				'disable_activity_log' => intval( get_option( 'sg_security_disable_activity_log', 0 ) ),
				'log_lifetime'         => $this->prepare_options_selected_values( array_combine( range( 1, 12 ), range( 1, 12 ) ), intval( get_option( 'sgs_activity_log_lifetime', 12 ) ) ),
			),
		);

		// Send the error message if page does not exist.
		if ( ! array_key_exists( $data['page'], $pages ) ) {
			return self::send_response( '', 0 );
		}

		// Send the response to react app.
		return self::send_response( '', 1, $pages[ $data['page'] ] );
	}
}
