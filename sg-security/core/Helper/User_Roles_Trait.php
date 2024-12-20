<?php
namespace SG_Security\Helper;

use SG_Security\Options_Service\Options_Service;
/**
 * Trait used for factory pattern in the plugin.
 */
trait User_Roles_Trait {

	/**
	 * Roles that should be forced to use 2FA.
	 *
	 * @var array
	 */
	public static function get_admin_user_roles() {
		$roles = array(
			'editor',
			'administrator',
		);

		if ( Options_Service::is_enabled( 'sg2fa' ) ) {
			$roles = apply_filters( 'sg_security_2fa_roles', $roles );
		}

		return $roles;
	}
}
