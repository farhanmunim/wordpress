<?php
namespace SG_Security\Cli;;

use SG_Security\Config\Config;

/**
 * WP-CLI: wp sg security-config.
 *
 * Run the `wp sg security-config` command to generate config file.
 *
 * @since 1.5.2
 * @package Cli
 * @subpackage Cli/Config
 */

/**
 * Define the {@link Cli_Config} class.
 *
 * @since 1.5.2
 */
class Cli_Config {

	/**
	 * Create Config file.
	 */
	public function __invoke() {
		$config = new Config();

		if ( ! version_compare( get_option( 'sg_security_current_version', false ), \SG_Security\VERSION, '==' ) ) {
			// Update the option in the db.
			update_option( 'sg_security_current_version', \SG_Security\VERSION );
		}

		$config->update_config();
	}

}
