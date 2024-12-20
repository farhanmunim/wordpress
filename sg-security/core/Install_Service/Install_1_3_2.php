<?php
namespace SG_Security\Install_Service;

use SG_Security\Install_Service\Install;
use SG_Security\Sg_2fa\Sg_2fa;

/**
 * The instalation package version class.
 */
class Install_1_3_2 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 1.3.2
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '1.3.2';

	/**
	 * 2FA class instance.
	 *
	 * @var SG_2fa
	 */
	public $sg_2fa;

	/**
	 * Constructs a new instance.
	 *
	 * @since 1.3.2
	 */
	public function __construct() {
		// Init 2FA.
		$this->sg_2fa = new Sg_2fa();
	}

	/**
	 * Run the install procedure.
	 *
	 * @since 1.3.2
	 */
	public function install() {
		$this->hash_existing_backup_codes();
		$this->delete_unnecessary_backup_codes();
	}

	/**
	 * Hash 2FA users backup codes.
	 *
	 * @since 1.3.2
	 */
	public function hash_existing_backup_codes() {
		// Get all users with 2FA configured in order to hash the backup codes.
		$users_2fa_configured = get_users(
			array(
				'role__in'   => $this->sg_2fa->get_admin_user_roles(),
				'fields'     => array( 'ID' ),
				'meta_query' => array(
					array(
						'key'     => 'sg_security_2fa_configured',
						'value'   => '1',
						'compare' => '=',
					),
					array(
						'key'      => 'sg_security_2fa_backup_codes',
						'compare'  => 'EXISTS',
					),
				),
			)
		);

		// Hash their backup codes.
		foreach ( $users_2fa_configured as $user ) {
			// Get the user backup codes.
			$backup_codes = get_user_meta( $user->ID, 'sg_security_2fa_backup_codes', true ); // phpcs:ignore

			// Store the backup codes hashed.
			$this->sg_2fa->store_hashed_user_meta( $user->ID, 'sg_security_2fa_backup_codes', $backup_codes );
		}
	}

	/**
	 * Delete unnecessary backup codes.
	 *
	 * @since 1.3.2
	 */
	public function delete_unnecessary_backup_codes() {
		// Get all users with backup codes which have not yet configured 2FA.
		$users_with_backup_codes = get_users(
			array(
				'fields'     => array( 'ID' ),
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key'     => 'sg_security_2fa_configured',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'      => 'sg_security_2fa_backup_codes',
						'compare'  => 'EXISTS',
					),
				),
			)
		);

		// Delete their backup codes.
		foreach ( $users_with_backup_codes as $user ) {
			// Get the user backup codes.
			delete_user_meta( $user->ID, 'sg_security_2fa_backup_codes' ); // phpcs:ignore
		}
	}
}
