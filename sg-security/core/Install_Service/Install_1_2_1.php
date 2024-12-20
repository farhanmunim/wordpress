<?php
namespace SG_Security\Install_Service;

use SG_Security\Install_Service\Install;
use SG_Security\Options_Service\Options_Service;
use SG_Security\Htaccess_Service\Headers_Service;
/**
 * The instalation package version class.
 */
class Install_1_2_1 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 1.2.1
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '1.2.1';

	/**
	 * Run the install procedure.
	 *
	 * @since 1.2.1
	 */
	public function install() {
		$this->maybe_remove_xss_htaccess();
	}

	/**
	 * Method wich determines if we need to make changes to the htaccess file.
	 *
	 * @since  1.2.1
	 *
	 * @param  integer $counter Flag for making changes to the file.
	 */
	public function maybe_remove_xss_htaccess( $counter = 0 ) {
		// Skip if the option is disabled
		if ( false === Options_Service::is_enabled( 'xss_protection' ) ) {
			return;
		}

		// Initialize XSS instance.
		$xss_service = new Headers_Service();

		// Remove the rules using the old Class.
		// We are not chanigng the options, since we will serve the header in a new way.
		$xss_service->toggle_rules( 0 );
	}
}
