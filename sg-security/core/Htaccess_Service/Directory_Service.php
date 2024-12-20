<?php
namespace SG_Security\Htaccess_Service;

use SG_Security;

/**
 * Class managing the directory hardening related htaccess rules.
 */
class Directory_Service extends Abstract_Htaccess_Service {

	/**
	 * The path to the htaccess template.
	 *
	 * @var string
	 */
	public $template = 'directory-hardening.tpl';

	/**
	 * The type of directory.
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Array with files to the whitelisted.
	 *
	 * @var array
	 */
	public $whitelist = array();

	/**
	 * Array with files to the whitelisted.
	 *
	 * @var array
	 */
	public $types = array(
		'content'  => array(
			'whitelist' => array(),
		),
		'includes' => array(
			'whitelist' => array(
				'wp-tinymce.php',
				'ms-files.php',
			),
		),
		'uploads'  => array(
			'whitelist' => array(),
		),
	);

	/**
	 * Regular expressions to check if the rules are enabled.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @var array Regular expressions to check if the rules are enabled.
	 */
	public $rules = array(
		'enabled'     => '/\#\s+SGS Directory Hardening/si',
		'disabled'    => '/\#\s+SGS\s+Directory\s+Hardening(.+?)\#\s+SGS\s+Directory\s+Hardening\s+END(\n)?/ims',
		'disable_all' => '/\#\s+SGS\s+Directory\s+Hardening(.+?)\#\s+SGS\s+Directory\s+Hardening\s+END(\n)?/ims',
	);

	/**
	 * Get the filepath to the htaccess file.
	 *
	 * @since 1.0.0
	 */
	public function get_filepath() {
		switch ( $this->type ) {
			case 'includes':
				return $this->wp_filesystem->abspath() . WPINC . '/.htaccess';
				break;

			case 'uploads':
				$upload_dir = wp_upload_dir();
				return $upload_dir['basedir'] . '/.htaccess';
				break;

			case 'content':
				return $this->wp_filesystem->wp_content_dir() . '.htaccess';
				break;
		}
	}

	/**
	 * Add whitelist rule for specifc or user files.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $content The generated custom rule for a directory.
	 *
	 * @return string $content The modified rule, containing the whitelist.
	 */
	public function do_replacement( $content ) {
		// Add custom whitelist.
		$this->types[ $this->type ]['whitelist'] = apply_filters( 'sgs_whitelist_wp_' . $this->type, $this->types[ $this->type ]['whitelist'] );

		// Bail the there is nothing to whitelist.
		if ( empty( $this->types[ $this->type ]['whitelist'] ) ) {
			return str_replace( '{REPLACEMENT}', '', $content );
		}

		$whitelisted_files = '';
		// Get the whitelist template.
		$whitelist_template = $this->wp_filesystem->get_contents( SG_Security\DIR . '/templates/whitelist-file.tpl' );

		// Loop through the files and create whitelist rules.
		foreach ( $this->types[ $this->type ]['whitelist'] as $file ) {
			$whitelisted_files .= str_replace( '{FILENAME}', $file, $whitelist_template ) . PHP_EOL;
		}

		// Add the whitelisted files.
		return str_replace( '{REPLACEMENT}', $whitelisted_files, $content );
	}

	/**
	 * Enable all hardening rules.
	 *
	 * @since  1.0.0
	 *
	 * @param  boolean $enable Whether to enable or disable the rules.
	 */
	public function toggle_rules( $enable = 1 ) {
		foreach ( $this->types as $type => $data ) {
			$this->type = $type;
			$this->set_htaccess_path();

			// Enable the rules.
			if ( 1 === intval( $enable ) ) {
				$this->enable();
				continue;
			}

			// Disable and remove htaccess files otherwise.
			$this->disable();
			$this->maybe_remove_htaccess();

		}
	}

	/**
	 * Check if we need to remove the htaccess files after disable if they are empty.
	 *
	 * @since  1.0.1
	 *
	 * @return bool True/False if we deleted the files.
	 */
	public function maybe_remove_htaccess() {
		// Get the filepath of the file.
		$path = $this->get_filepath();

		// Bail if it isn't writable.
		if ( ! $this->wp_filesystem->is_writable( $path ) ) {
			return;
		}

		// Bail if the file is not empty.
		if ( ! empty( trim( $this->wp_filesystem->get_contents( $path ) ) ) ) {
			return;
		}

		return $this->wp_filesystem->delete( $path );
	}
}
