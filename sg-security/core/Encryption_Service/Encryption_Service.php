<?php
namespace SG_Security\Encryption_Service;

use SiteGround_Helper\Helper_Service;

/**
 * Encryption_Service class used to encrypt and decrypt data.
 */
class Encryption_Service {
	/**
	 * Encryption key file name.
	 *
	 * @var string
	 */
	public $encryption_key_file;

	/**
	 * Encryption/Decryption cipher.
	 *
	 * @var string
	 */
	public $cipher = 'AES-256-CBC';

	/**
	 * WP_Filesystem class instance holder.
	 *
	 * @since 1.3.6
	 *
	 * @var object
	 */
	public $wp_filesystem;

	/**
	 * Construct of the class.
	 *
	 * @since 1.3.6
	 *
	 * @param string $encryption_key_file_name    The encryption key file name.
	 */
	public function __construct( $encryption_key_file_name ) {
		$this->encryption_key_file = $encryption_key_file_name;
		$this->wp_filesystem       = Helper_Service::setup_wp_filesystem();
	}

	/**
	 * Generate encryption/decryption key file.
	 *
	 * @since 1.3.6
	 */
	public function generate_encryption_file() {
		// Check if the file already exists.
		if ( $this->wp_filesystem->is_file( $this->encryption_key_file ) ) {
			// Validate the file content.
			if ( false === $this->get_encryption_key() ) {
				return false;
			}

			return true;
		}

		// Try to create the file and bail if for some reason it's not created.
		if ( false === $this->wp_filesystem->touch( $this->encryption_key_file ) ) {
			return false;
		}

		// Bail if encryption file is not writable.
		if ( ! $this->wp_filesystem->is_writable( $this->encryption_key_file ) ) {
			return false;
		}

		// Generate unique encryption key for the website.
		$encryption_key = base64_encode( openssl_random_pseudo_bytes( 32 ) );

		// Add the encryption key to the file.
		return $this->save_encryption_key( $encryption_key );
	}

	/**
	 * Get encryption/decryption key and validate it.
	 *
	 * @since 1.3.6
	 *
	 * @return mixed False if the key is not valid/existing, encryption key string on successfull validation.
	 */
	public function get_encryption_key() {
		// Bail if file does not exist.
		if ( ! $this->wp_filesystem->is_file( $this->encryption_key_file ) ) {
			return false;
		}

		// Get the encryption key file content.
		$enc_file_content = $this->wp_filesystem->get_contents( $this->encryption_key_file );

		// Bail if the file content is not correct.
		if ( ! preg_match( '~(?<=\$sg_encryption_key = ")(.*)(?="; \?>)~', $enc_file_content, $matches ) ) {
			return false;
		}

		// Validate key content.
		if ( ! $this->validate_encryption_key( $matches[0] ) ) {
			return false;
		}

		// Return the decoded key.
		return base64_decode( $matches[0], true );
	}

	/**
	 * Delete encryption/decryption key file.
	 *
	 * @since 1.3.6
	 */
	public function delete_encryption_file() {
		return $this->wp_filesystem->delete( $this->encryption_key_file, true );
	}

	/**
	 * Encrypt data method.
	 *
	 * @since  1.3.6
	 *
	 * @param  string $data The string we will encrypt.
	 *
	 * @return string       The encrypted data.
	 */
	public function sgs_encrypt( $data ) {
		$encryption_key = $this->get_encryption_key();

		// Bail if encryption key doesn't exist.
		if ( false === $encryption_key ) {
			return;
		}

		// Generate an initialization vector.
		$iv = openssl_random_pseudo_bytes( openssl_cipher_iv_length( $this->cipher ) );

		$raw_value = openssl_encrypt( $data, $this->cipher, $encryption_key, OPENSSL_RAW_DATA, $iv );

		// Return the encrypted data.
		return base64_encode( $iv . $raw_value );
	}

	/**
	 * Decrypt data method.
	 *
	 * @since  1.3.6
	 *
	 * @param  string $data The string we will decrypt.
	 *
	 * @return string       The decrypted data.
	 */
	public function sgs_decrypt( $data ) {
		$encryption_key = $this->get_encryption_key();

		// Bail if encryption key doesn't exist.
		if ( false === $encryption_key ) {
			return;
		}

		// Remove the base64 encoding from our data.
		$raw_value = base64_decode( $data, true );

		// Get the cipher iv length.
		$ivlen = openssl_cipher_iv_length( $this->cipher );
		// Get the initialization vector.
		$iv = substr( $raw_value, 0, $ivlen );

		$raw_value = substr( $raw_value, $ivlen );

		// Return the decrypted data.
		return openssl_decrypt( $raw_value, 'AES-256-CBC', $encryption_key, OPENSSL_RAW_DATA, $iv );
	}

	/**
	 * Saves an encryption key into the encryption key file.
	 *
	 * @since  1.3.7
	 *
	 * @param  string $key The key.
	 *
	 * @return bool        True if key is saved, False if key is not valid or not saved.
	 */
	public function save_encryption_key( $key ) {
		// Check if file exists.
		if ( ! $this->wp_filesystem->is_file( $this->encryption_key_file ) ) {
			return false;
		}

		// Validate key content.
		if ( ! $this->validate_encryption_key( $key ) ) {
			return false;
		}

		return $this->wp_filesystem->put_contents( $this->encryption_key_file, '<?php $sg_encryption_key = "' . $key . '"; ?>' );
	}

	/**
	 * Validate encryption key content.
	 *
	 * @since 1.3.7
	 *
	 * @param string $key The key.
	 *
	 * @return bool       True/False if key is valid or not.
	 */
	public function validate_encryption_key( $key ) {
		// Bail if there is no encryption key.
		if ( empty( $key ) ) {
			return false;
		}

		$decoded_key = base64_decode( $key, true );

		// Bail if key contains character from outside the base64 alphabet.
		if ( false === $decoded_key ) {
			return false;
		}

		// Bail if key is not 32 characters long.
		if ( strlen( $decoded_key ) !== 32 ) {
			return false;
		}

		// Key is valid.
		return true;
	}
}
