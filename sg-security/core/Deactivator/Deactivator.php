<?php
namespace SG_Security\Deactivator;

use SG_Security\Htaccess_Service\Directory_Service;
use SG_Security\Htaccess_Service\Xmlrpc_Service;
use SG_Security\Activity_Log\Activity_Log_Weekly_Emails;

/**
 * Class that manages plugin deactivation.
 */
class Deactivator {

	/**
	 * Run on plugin deactivation.
	 *
	 * @since 1.0.0
	 */
	public function deactivate() {
		// Disable any existing rules for directory hardening.
		$directory_service = new Directory_Service();
		$directory_service->toggle_rules( 0 );

		// Disable the XML-RPC rules.
		$xml_rpc_service = new Xmlrpc_Service();
		$xml_rpc_service->toggle_rules( 0 );

		// Delete the Weekly Emails Cron Job.
		$weekly_emails = new Activity_Log_Weekly_Emails();

		if ( wp_next_scheduled( 'sgs_email_cron' ) ) {
			$weekly_emails->weekly_report_email->unschedule_event();
		}
	}
}
