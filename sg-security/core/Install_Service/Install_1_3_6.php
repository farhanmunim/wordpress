<?php
namespace SG_Security\Install_Service;

use SG_Security\Install_Service\Install;
use SG_Security\Encryption_Service\Encryption_Service;
use SG_Security\SG_2fa\SG_2fa;

/**
 * The instalation package version class.
 */
class Install_1_3_6 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 1.3.6
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '1.3.6';

	/**
	 * Local variables
	 *
	 * @var mixed
	 */
	public $sg2fa;
	public $encryption;

	/**
	 * Constructs a new instance.
	 *
	 * @since 1.3.6
	 */
	public function __construct() {
		$this->sg2fa      = new SG_2fa();
		$this->encryption = new Encryption_Service( $this->sg2fa->encryption_key_file );
	}

	/**
	 * Run the install procedure.
	 *
	 * @since 1.3.6
	 */
	public function install() {
		// Create the encryption key file.
		if ( ! $this->encryption->generate_encryption_file() ) {
			// Disable the 2FA and show admin notice.
			$this->sg2fa->disable_2fa_show_notice();
			// Update install service option.
			return true;
		}

		// Encrypt all users secret codes.
		$this->encrypt_all_users_secrets();
		// Delete all stored QR codes.
		$this->delete_all_qr_codes();
	}

	/**
	 * Encrypt all secret codes in the DB.
	 *
	 * @since 1.3.6
	 */
	public function encrypt_all_users_secrets() {
		// Get all users with existing 2FA secret codes.
		$users_with_secret_codes = get_users(
			array(
				'fields'     => array( 'ID' ),
				'meta_query' => array(
					array(
						'key'      => 'sg_security_2fa_secret',
						'compare'  => 'EXISTS',
					),
				),
			)
		);

		// Encrypt their secret codes.
		foreach ( $users_with_secret_codes as $user ) {
			// Get the user secret code.
			$secret = get_user_meta( $user->ID, 'sg_security_2fa_secret', true ); // phpcs:ignore

			// Store the secret code encrypted.
			update_user_meta( $user->ID, 'sg_security_2fa_secret', $this->encryption->sgs_encrypt( $secret ) );
		}
	}

	/**
	 * Delete all stored QR codes.
	 *
	 * @since 1.3.6
	 */
	public function delete_all_qr_codes() {
		delete_metadata( 'user', 0, 'sg_security_2fa_qr', '', true );
	}
}
