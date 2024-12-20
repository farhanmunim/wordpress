<?php
namespace SG_Security\Cli;

use SG_Security\Options_Service\Options_Service;
use SG_Security\Htaccess_Service\Directory_Service;
use SG_Security\Htaccess_Service\Xmlrpc_Service;
use SG_Security\Feed_Service\Feed_Service;
use SG_Security\Loader\Loader;

/**
 * WP-CLI: wp sg secure {option} enable/disable.
 *
 * Run the `wp sg secure {option} enable/disable` command to enable/disable specific plugin functionality.
 *
 * @since 1.0.2
 * @package Cli
 * @subpackage Cli/Cli_Secure
 */

/**
 * Define the {@link Cli_Secure} class.
 *
 * @since 1.0.2
 */
class Cli_Secure {

	/**
	 * The array that maps features with their option.
	 *
	 * @var array
	 */
	public $mapping = array(
		'protect-system-folders' => 'lock_system_folders',
		'hide-wordpress-version' => 'wp_remove_version',
		'plugins-themes-editor'  => 'disable_file_edit',
		'xml-rpc'                => 'disable_xml_rpc',
		'rss-atom-feed'          => 'disable_feed',
		'xss-protection'         => 'xss_protection',
		'2fa'                    => 'sg2fa',
		'disable-admin-user'     => 'disable_usernames',
	);

	/**
	 * Enable specific optimization for SG Security plugin.
	 *
	 * ## OPTIONS
	 *
	 * <optimization>
	 * : Optimization name.
	 * ---
	 * options:
	 *  - protect-system-folders
	 *  - hide-wordpress-version
	 *  - plugins-themes-editor
	 *  - xml-rpc
	 *  - rss-atom-feed
	 *  - xss-protection
	 *  - 2fa
	 *  - disable-admin-user
	 * ---
	 * [<action>]
	 * : The action: enable\disable.
	 * Whether to enable or disable the optimization.
	 */
	public function __invoke( $args ) {
		// Get the status of the feature if no action is set.
		if ( ! isset( $args[1] ) ) {
			return $this->get_feature_status( $args[0] );
		}

		switch ( $args[0] ) {
			case 'protect-system-folders':
			case 'xml-rpc':
				return $this->htaccess_secure( $args );
			case 'xss-protection':
			case 'rss-atom-feed':
			case 'hide-wordpress-version':
			case 'plugins-themes-editor':
			case '2fa':
			case 'disable-admin-user':
				return $this->optimize( $args );
		}
	}

	/**
	 * Enables/Disables the option in the database.
	 *
	 * @since 1.0.2
	 *
	 * @param string $args A string that contains the option and the action which should be taken.
	 */
	public function optimize( $args ) {

		// Check the input of the user and proceed depending on the option.
		switch ( $args[1] ) {
			case 'enable':
				$result = Options_Service::enable_option( $this->mapping[ $args[0] ] );
				break;

			case 'disable':
				$result = Options_Service::change_option( $this->mapping[ $args[0] ], 0 );
				break;

			default:
				\WP_CLI::error( 'There is an issue with changing the status of ' . $args[0] . '. Please check your command.' );
				break;
		}

		// Bail if there is an error.
		if ( 0 === $result ) {
			return \WP_CLI::error( 'There is an issue with changing the status of ' . $args[0] );
		}

		// Confirm the successful change.
		\WP_CLI::success( $args[0] . ' was ' . $args[1] . 'd succesfully!' );
	}

	public function htaccess_secure( $args ) {
		$classes = array(
			'protect-system-folders' => 'Directory_Service',
			'xml-rpc'                => 'Xmlrpc_Service',
		);

		$class_name = 'SG_Security\Htaccess_Service\\' . $classes[ $args[0] ];

		$class = new $class_name();

		// Disables the protection.
		if ( 'disable' === $args[1] ) {
			$class->toggle_rules( 0 );
		} else {
			// Enables the protection.
			$class->toggle_rules( 1 );
		}

		// Update option in database.
		$this->optimize( $args );
	}

	/**
	 * Get the status of a specific feature.
	 *
	 * @since 1.3.7
	 *
	 * @param  string $feature The feature we want to check.
	 */
	public function get_feature_status( $feature ) {
		// Check if option is enabled.
		$maybe_enabled = Options_Service::is_enabled( $this->mapping[ $feature ] );

		// Set the proper status based on the options status.
		$status = $maybe_enabled ? \WP_CLI::colorize( '%gEnabled%n' ) : \WP_CLI::colorize( '%rDisabled%n' );

		// Return the status.
		\WP_CLI::success( $feature . ' Status: ' . $status );
	}
}
