<?php
namespace SG_Security\Config;

use SiteGround_Helper\Helper_Service;

/**
 * Config functions and main initialization class.
 */
class Config {
	/**
	 * The config filename.
	 *
	 * @since 1.4.0
	 */
	const SGS_CONFIG = \SG_Security\DIR . '/sg-config.json';

	/**
	 * List of all optimization that we want to keep in the config.
	 *
	 * @access public
	 *
	 * @since 1.4.0
	 * 
	 * @var array $config_options List of all options.
	 */
	public $config_options = array(
		'version'              => 'sg_security_current_version',
		'lock_system_folders'  => 'sg_security_lock_system_folders',
		'wp_remove_version'    => 'sg_security_wp_remove_version',
		'disable_file_edit'    => 'sg_security_disable_file_edit',
		'disable_xml_rpc'      => 'sg_security_disable_xml_rpc',
		'disable_feed'         => 'sg_security_disable_feed',
		'xss_protection'       => 'sg_security_xss_protection',
		'delete_readme'        => 'sg_security_delete_readme',
		'sg2fa'                => 'sg_security_sg2fa',
		'disable_usernames'    => 'sg_security_disable_usernames',
		'disable_activity_log' => 'sg_security_disable_activity_log',
	);

	/**
	 * Check if the config file needs to be updated.
	 *
	 * @since 1.4.1
	 *
	 * @param string $option Name of the option to add/update.
	 */
	public function update_config_check( $option ) {
		// Check if the option matches the once we are setting in the config.
		if ( ! in_array( $option, $this->config_options, true ) ) {
			return;
		}

		// Update the config file.
		$this->update_config();
	}

	/**
	 * Update the config.
	 *
	 * @since 1.4.0
	 */
	public function update_config() {
		// Check for the helper service method.
		if (
			! method_exists( 'SiteGround_Helper\\Helper_Service', 'update_file' ) ||
			! method_exists( 'SiteGround_Helper\\Helper_Service', 'build_config_content' )
		) {
			return;
		}

		// Update the config file.
		Helper_Service::update_file( self::SGS_CONFIG, Helper_Service::build_config_content( $this->config_options ) );
	}
}
