<?php
namespace SG_Security\Install_Service;

use SG_Security\Activity_Log\Activity_Log;
use SG_Security\Activity_Log\Activity_Log_Helper;

/**
 * The instalation package version class.
 */
class Install_1_4_2 extends Install {
	/**
	 * Local variables
	 *
	 * @var mixed
	 */
	public $activity_log;
	public $activity_log_helper;

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 1.4.2
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '1.4.2';

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->activity_log        = new Activity_Log();
		$this->activity_log_helper = new Activity_Log_Helper();
	}

	/**
	 * Run the install procedure.
	 *
	 * @since 1.4.2
	 */
	public function install() {
		// Clear old logs from the database.
		$this->activity_log->delete_old_activity_logs();

		// Index the tables.
		$this->activity_log_helper->add_log_visitor_indexes();
	}
}
