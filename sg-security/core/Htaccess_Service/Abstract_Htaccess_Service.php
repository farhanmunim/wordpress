<?php
namespace SG_Security\Htaccess_Service;

use SG_Security;
use SiteGround_Helper\Helper_Service;

/**
 * SG Security Abstract_Htaccess_Service main plugin class.
 */
abstract class Abstract_Htaccess_Service {

	/**
	 * WordPress filesystem.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 *
	 * @var The WP Filesystem.
	 */
	protected $wp_filesystem = null;

	/**
	 * Path to htaccess file.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 *
	 * @var string The path to htaccess file.
	 */
	public $path = null;

	/**
	 * The singleton instance.
	 *
	 * @since 1.0.0
	 *
	 * @var \Abstract_Htaccess_Service The singleton instance.
	 */
	public static $instance;

	/**
	 * The constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( null === $this->wp_filesystem ) {
			$this->wp_filesystem = Helper_Service::setup_wp_filesystem();
		}

		self::$instance = $this;
	}

	/**
	 * Get the singleton instance.
	 *
	 * @since 1.0.0
	 *
	 * @return \Abstract_Htaccess_Service The singleton instance.
	 */
	public static function get_instance() {
		return self::$instance;
	}

	/**
	 * Set the htaccess path.
	 *
	 * @since 1.0.0
	 */
	public function set_htaccess_path() {
		$filepath = $this->get_filepath();

		// Create the htaccess if it doesn't exists.
		if ( ! is_file( $filepath ) ) {
			$this->wp_filesystem->touch( $filepath );
		}

		// Bail if it isn't writable.
		if ( ! $this->wp_filesystem->is_writable( $filepath ) ) {
			return false;
		}

		// Finally set the path.
		$this->path = $filepath;
	}

	/**
	 * Disable the rule and remove it from the htaccess.
	 *
	 * @since  1.0.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function disable() {
		// Bail if htaccess doesn't exists.
		if ( empty( $this->path ) ) {
			return false;
		}

		// Bail if the rule is already disabled.
		if ( ! $this->is_enabled() ) {
			return true;
		}

		// Get the content of htaccess.
		$content = $this->wp_filesystem->get_contents( $this->path );

		// Remove the rule.
		$new_content = preg_replace( $this->rules['disabled'], '', $content );

		return $this->lock_and_write( $new_content );
	}

	/**
	 * Add rule to htaccess and enable it.
	 *
	 * @since  1.0.0
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function enable() {
		// Bail if htaccess doesn't exists.
		if ( empty( $this->path ) ) {
			return false;
		}

		// Bail if the rule is already enabled.
		if ( $this->is_enabled() ) {
			return true;
		}

		// Disable all other rules first.
		$content = preg_replace(
			$this->rules['disable_all'],
			'',
			$this->wp_filesystem->get_contents( $this->path )
		);

		// Get the new rule.
		$new_rule = $this->wp_filesystem->get_contents( SG_Security\DIR . '/templates/' . $this->template );

		// Add the rule and write the new htaccess.
		$content = $new_rule . PHP_EOL . $content;

		// Whitelist files if the service has any.
		$content = $this->do_replacement( $content );

		// Return the result.
		return $this->lock_and_write( $content );
	}

	/**
	 * Lock file and write something in it.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $content Content to add.
	 *
	 * @return bool            True on success, false otherwise.
	 */
	protected function lock_and_write( $content ) {
		$fp = fopen( $this->path, 'w+' );

		if ( flock( $fp, LOCK_EX ) ) {
			fwrite( $fp, $content );
			flock( $fp, LOCK_UN );
			fclose( $fp );
			return true;
		}

		fclose( $fp );
		return false;
	}

	/**
	 * Do a replacement.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $content The htaccess content.
	 *
	 * @return string          New htaccess content with replacement.
	 */
	public function do_replacement( $content ) {
		return $content;
	}

	/**
	 * Get the filepath to the htaccess.
	 *
	 * @since  1.0.0
	 *
	 * @return string Path to the htaccess.
	 */
	public function get_filepath() {
		return $this->wp_filesystem->abspath() . '.htaccess';
	}

	/**
	 * Toogle specific rule.
	 *
	 * @since  1.0.0
	 *
	 * @param  boolean $type Whether to enable or disable the rules.
	 */
	public function toggle_rules( $type = 1 ) {
		$this->set_htaccess_path();
		( 1 === $type ) ? $this->enable() : $this->disable();
	}

	/**
	 * Check if rule is enabled.
	 *
	 * @since  1.0.0
	 *
	 * @return boolean True if the rule is enabled, false otherwise.
	 */
	public function is_enabled() {
		// Get the content of htaccess.
		$content = $this->wp_filesystem->get_contents( $this->path );

		// Return the result.
		return preg_match( $this->rules['enabled'], $content );
	}
}
