<?php
namespace SG_Security\Rest;

use SG_Security\Salt_Shaker\Salt_Shaker;
use SG_Security\Plugins_Service\Plugins_Service;
use SG_Security\Password_Service\Password_Service;
use \WP_Session_Tokens;

/**
 * Rest Helper class that manages all of the post hack actions.
 */
class Rest_Helper_Post_Hack_Actions extends Rest_Helper {

	/**
	 * Local variables
	 *
	 * @var mixed
	 */
	public $salt_shaker;
	public $plugins_service;
	public $password_service;

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->salt_shaker   = new Salt_Shaker();
		$this->plugins_service  = new Plugins_Service();
		$this->password_service = new Password_Service();
	}

	/**
	 * Reinstalls all free plugins.
	 *
	 * @since  1.0.0
	 */
	public function resinstall_plugins() {
		$result = $this->plugins_service->reinstall_plugins();
		// Reinstall plugins.
		return self::send_response(
			$this->get_response_message( $result, 'reinstall_plugins' ),
			$result
		);
	}

	/**
	 * Force passwords reset.
	 *
	 * @since  1.0.0
	 */
	public function force_password_reset() {
		$this->salt_shaker->change_salts();
		// Destroy all sessions.
		WP_Session_Tokens::destroy_all_for_all_users();

		$this->password_service->invalidate_passwords();
		// Force password reset.
		return self::send_response(
			$this->get_response_message( 1, 'force_password_reset' )
		);
	}

	/**
	 * Logs out all users
	 *
	 * @since  1.0.0
	 */
	public function logout_users() {
		$this->salt_shaker->change_salts();
		// Destroy all sessions.
		WP_Session_Tokens::destroy_all_for_all_users();

		// Logout all users.
		return self::send_response(
			$this->get_response_message( 1, 'logout_users' )
		);
	}
}
