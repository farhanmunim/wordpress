<?php
namespace SG_Security\Install_Service;

use SG_Security\Sg_2fa\Sg_2fa;

/**
 * The instalation package version class.
 */
class Install_1_1_0 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 1.1.0
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '1.1.0';

	/**
	 * Run the install procedure.
	 *
	 * @since 1.1.0
	 */
	public function install() {
		// Setting the Server Address.
		add_option( 'sg_security_server_address', \gethostbyname( \gethostname() ), '', 'no' );

		// Setting the login type option to default upon installation.
		update_option( 'sg_security_login_type', 'default' );

		$sg_2fa = new Sg_2fa();

		$users = get_users( array(
			'role__in'   => $sg_2fa->get_admin_user_roles(),
			'fields'     => array( 'ID' ),
			'meta_query' => array(
				array(
					'key'     => 'sg_security_2fa_configured',
					'value'   => 1,
					'compare' => '=',
				),
			),
		) );

		foreach ( $users as $user ) {
			$sg_2fa->generate_user_backup_codes( $user->ID );
			update_user_meta( $user->ID, 'sgs_additional_codes_added', 1 );
		}
	}
}
