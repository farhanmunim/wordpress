<?php
namespace SG_Security\Install_Service;

/**
 * The installation package version class.
 */
class Install_1_2_0 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 1.2.0
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '1.2.0';

	/**
	 * The Database placeholder.
	 */
	public $wpdb;

	/**
	 * Run the install procedure.
	 *
	 * @since 1.2.0
	 */
	public function install() {
		global $wpdb;
		$this->wpdb = $wpdb;

		// Change the the events and visitors tables charset.
		$this->wpdb->query( 'ALTER TABLE `' . esc_sql( $this->wpdb->prefix . 'sgs_log_events' ) . '` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;' );
		$this->wpdb->query( 'ALTER TABLE `' . esc_sql( $this->wpdb->prefix . 'sgs_log_visitors' ) . '` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;' );

		// Setting the notification email option for Weekly emails.
		add_option( 'sg_security_notification_emails', array( get_bloginfo( 'admin_email' ) ) );

		// Update the last run timestamp, so when the event run - it will be used as a start date.
		update_option( 'sg_security_weekly_email_timestamp', time() );
	}
}
