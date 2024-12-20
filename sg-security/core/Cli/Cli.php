<?php
namespace SG_Security\Cli;

/**
 * SG Security Cli main plugin class
 */
class Cli {
	/**
	 * Init SG Security .
	 *
	 * @version
	 */
	public function register_commands() {
		// Optimize commands.
		\WP_CLI::add_command( 'sg secure', 'SG_Security\Cli\Cli_Secure' );

		// Limits login attempts.
		\WP_CLI::add_command( 'sg limit-login-attempts', 'SG_Security\Cli\Cli_Limit_Login_Attempts' );

		// Login access configuration.
		\WP_CLI::add_command( 'sg login-access', 'SG_Security\Cli\Cli_Login_Access' );

		// List activity logs.
		\WP_CLI::add_command( 'sg list', 'SG_Security\Cli\Cli_List' );

		// Add ua and ip to the activity log.
		\WP_CLI::add_command( 'sg log', 'SG_Security\Cli\Cli_Log' );

		// Reset the 2FA setup per user.
		\WP_CLI::add_command( 'sg 2fa', 'SG_Security\Cli\Cli_2fa' );

		// Get Custom Login URL status or disable it.
		\WP_CLI::add_command( 'sg custom-login', 'SG_Security\Cli\Cli_Custom_Login_Url' );
		// Config.
		\WP_CLI::add_command( 'sg security-config', 'SG_Security\Cli\Cli_Config' );
	}
}
