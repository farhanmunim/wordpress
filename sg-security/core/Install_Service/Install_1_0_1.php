<?php
namespace SG_Security\Install_Service;

use SG_Security\Install_Service\Install;
use SG_Security\Options_Service\Options_Service;
use SG_Security\Htaccess_Service\Directory_Service;
use SG_Security\Htaccess_Service\Headers_Service;
use SG_Security\Htaccess_Service\Xmlrpc_Service;

/**
 * The instalation package version class.
 */
class Install_1_0_1 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 1.0.1
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '1.0.1';

	/**
	 * Run the install procedure.
	 *
	 * @since 1.0.1
	 */
	public function install() {
		$default_options = array(
			'lock_system_folders',
			'disable_file_edit',
			'wp_remove_version',
			'disable_xml_rpc',
			'xss_protection',
			'disable_usernames',
		);

		// Enable the main options on install.
		foreach ( $default_options as $option ) {
			Options_Service::enable_option( $option );
		}

		$deps = array(
			'Directory_Service',
			'Xmlrpc_Service',
		);

		// Do the additional actions.
		foreach ( $deps as $dep ) {
			$class_name = 'SG_Security\Htaccess_Service\\' . $dep;
			$class = new $class_name();
			$class->toggle_rules( 1 );
		}

		update_option( 'sg_security_login_attempts', 5 );
	}
}
