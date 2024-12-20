<?php
namespace SG_Security\Install_Service;

use SG_Security\Activity_Log\Activity_Log_Helper;

/**
 * The instalation package version class.
 */
class Install_1_4_4 extends Install {
	/**
	 * Activity Log Helper
	 *
	 * @var Activity_Log_Helper
	 */
	public $activity_log_helper;

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 1.4.4
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '1.4.4';

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->activity_log_helper = new Activity_Log_Helper();
	}

	/**
	 * Run the install procedure.
	 *
	 * @since 1.4.4
	 */
	public function install() {
		// Index the tables.
		$this->activity_log_helper->adjust_visitors_indexes();

		// Delete old install service options.
		delete_option( 'sgs_install_1_3_6' );
		delete_option( 'sgs_install_1_3_7' );
		delete_option( 'sgs_install_1_4_2' );

		// Update install service option.
		update_option( 'sgs_install_1_4_4', 1 );
	}
}
