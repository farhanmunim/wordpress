<?php
namespace SG_Security\Install_Service;

use SG_Security\Install_Service\Install;
use SiteGround_Helper\Helper_Service;
use SG_Security\Encryption_Service\Encryption_Service;
use SG_Security\SG_2fa\SG_2fa;
/**
 * The instalation package version class.
 */
class Install_1_3_7 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 1.3.7
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '1.3.7';

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
	 * @since 1.3.7
	 */
	public function __construct() {
		$this->sg2fa      = new SG_2fa();
		$this->encryption = new Encryption_Service( $this->sg2fa->encryption_key_file );
	}

	/**
	 * Run the install procedure.
	 *
	 * @since 1.3.7
	 */
	public function install() {
		// Setup the WP Filesystem.
		$wp_filesystem = Helper_Service::setup_wp_filesystem();

		// Check if file exists.
		if ( ! $wp_filesystem->is_file( $this->sg2fa->encryption_key_file ) ) {
			return true;
		}

		// Get the file content.
		$key = $wp_filesystem->get_contents( $this->sg2fa->encryption_key_file );

		// Update the file.
		$this->encryption->save_encryption_key( $key );

		// Decrypt all users secret codes.
		$this->decrypt_all_users_secrets();

		// Update the file with new key.
		$this->encryption->save_encryption_key( base64_encode( openssl_random_pseudo_bytes( 32 ) ) );

		// Encrypt the secrets.
		$this->encryption_all_users_secrets();
	}

	/**
	 * Decrypt all secret codes in the DB.
	 *
	 * @since 1.3.7
	 */
	public function decrypt_all_users_secrets() {
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

		// Decrypt their secret codes.
		foreach ( $users_with_secret_codes as $user ) {
			// Get the user secret code.
			$secret = get_user_meta( $user->ID, 'sg_security_2fa_secret', true ); // phpcs:ignore

			// Sаve it.
			update_user_meta( $user->ID, 'sg_security_2fa_secret', $this->encryption->sgs_decrypt( $secret ) );
		}
	}

	/**
	 * Encrypt all secret codes in the DB.
	 *
	 * @since 1.3.7
	 */
	public function encryption_all_users_secrets() {
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

			// Sаve it.
			update_user_meta( $user->ID, 'sg_security_2fa_secret', $this->encryption->sgs_encrypt( $secret ) );
		}
	}
}
