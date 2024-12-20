<?php
namespace SG_Security\Install_Service;

/**
 * The instalation package version class.
 */
class Install_1_4_7 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 1.4.7
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '1.4.7';

	/**
	 * Run the install procedure.
	 *
	 * @since 1.4.7
	 */
	public function install() {
		delete_option( 'sgs_install_1_4_4' );
		// Update install service option.
		update_option( 'sgs_install_1_4_7', 1 );

		if ( empty( get_option( 'sg_security_notification_emails', array() ) ) ) {
			return;
		}

		$this->randomize_sgs_email_cron();
	}

	/**
	 * Randomize the event schedule between 1-6 hours for existing events.
	 *
	 * @since 1.4.7
	 */
	public function randomize_sgs_email_cron() {
		// Retrieve the next timestamp for the cron event.
		$timestamp = wp_next_scheduled( 'sgs_email_cron' );

		//Bail if there is no such event scheduled.
		if ( false === $timestamp ) {
			return;
		}

		// Unschedule the event.
		wp_unschedule_event( $timestamp, 'sgs_email_cron' );
	}
}
