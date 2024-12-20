<?php
namespace SG_Security\Activity_Log;

use SG_Security\Activity_Log\Activity_Log_Posts;
use SG_Security\Activity_Log\Activity_Log_Options;
use SG_Security\Activity_Log\Activity_Log_Attachments;
use SG_Security\Activity_Log\Activity_Log_Comments;
use SG_Security\Activity_Log\Activity_Log_Core;
use SG_Security\Activity_Log\Activity_Log_Menu;
use SG_Security\Activity_Log\Activity_Log_Export;
use SG_Security\Activity_Log\Activity_Log_Plugins;
use SG_Security\Activity_Log\Activity_Log_Themes;
use SG_Security\Activity_Log\Activity_Log_Users;
use SG_Security\Activity_Log\Activity_Log_Widgets;
use SG_Security\Activity_Log\Activity_Log_Unknown;
use SG_Security\Activity_Log\Activity_Log_Taxonomies;
use SG_Security\Activity_Log\Activity_Log_Weekly_Emails;
use SG_Security\Helper\Helper;
use SG_Security\Activity_Log\Activity_Log_Helper;
use SiteGround_Helper\Helper_Service;

/**
 * Activity log main class
 */
class Activity_Log {

	/**
	 * The singleton instance.
	 *
	 * @since 1.0.0
	 *
	 * @var \Activity_Log The singleton instance.
	 */
	private static $instance;

	/**
	 * Our custom log table name
	 *
	 * @var string
	 */
	public $log_table = 'sgs_log_events';

	/**
	 * Our custom log visitors tabl
	 *
	 * @var string
	 */
	public $visitors_table = 'sgs_log_visitors';

	/**
	 * Local variables
	 *
	 * @var mixed
	 */
	public $type;
	public $posts;
	public $options;
	public $attachments;
	public $comments;
	public $core;
	public $export;
	public $plugins;
	public $themes;
	public $users;
	public $widgets;
	public $unknown;
	public $taxonomies;
	public $weekly_emails;

	/**
	 * Child classes that have to be initialized.
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	public static $children = array(
		'posts',
		'options',
		'attachments',
		'comments',
		'core',
		'export',
		'plugins',
		'themes',
		'users',
		'widgets',
		'unknown',
		'taxonomies',
		'weekly_emails',
	);

	/**
	 * The constructor.
	 */
	public function __construct() {
		self::$instance = $this;
		$this->run();

		global $wpdb;

		$wpdb->sgs_log      = $wpdb->prefix . $this->log_table;
		$wpdb->sgs_visitors = $wpdb->prefix . $this->visitors_table;
	}

	/**
	 * Get the singleton instance.
	 *
	 * @since 1.0.0
	 *
	 * @return \Minifier The singleton instance.
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			static::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Init the sub loggers
	 *
	 * @since  1.0.0
	 */
	public function run() {
		foreach ( self::$children as $child ) {
			$this->factory( $child );
		}
	}

	/**
	 * Create a new log of type $type
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The type of the log class.
	 *
	 * @throws \Exception if the type is not supported.
	 */
	private function factory( $type ) {

		$class = __NAMESPACE__ . '\\Activity_Log_' . str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $type ) ) );

		if ( ! class_exists( $class ) ) {
			throw new \Exception( 'Unknown activity log type "' . esc_html( $type ) . '".' );
		}

		$this->$type = new $class();
	}


	/**
	 * Set the CRON job for deleting old logs.
	 *
	 * @since  1.0.0
	 */
	public function set_sgs_logs_cron() {
		// Bail if CRON is disabled.
		if ( 1 === Helper_Service::is_cron_disabled() ) {
			return;
		}

		if ( ! wp_next_scheduled( 'siteground_security_clear_logs_cron' ) ) {
			wp_schedule_event( time(), 'daily', 'siteground_security_clear_logs_cron' );
		}
	}

	/**
	 * Delete logs on plugin page if CRON is disabled.
	 *
	 * @since  1.0.0
	 */
	public function delete_logs_on_admin_page() {
		// Delete if we are on plugin page and CRON is disabled.
		if (
			isset( $_GET['page'] ) &&
			'sg-security' === $_GET['page'] &&
			1 === Helper_Service::is_cron_disabled()
		) {
			$this->delete_old_activity_logs();
		}
	}

	/**
	 * Delete the old log records from the database.
	 *
	 * @since  1.0.0
	 */
	public function delete_old_activity_logs() {
		$this->delete_old_events_logs();
		$this->delete_old_visitors_logs();
	}

	/**
	 * Create log tables upon new site creation.
	 *
	 * @since  1.2.0
	 *
	 * @param  WP_Site $new_site New site object.
	 */
	public function create_subsite_log_tables( $new_site ) {
		// Check if the method exists.
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}

		if ( ! \is_plugin_active_for_network( 'sg-security/sg-security.php' ) ) {
			return;
		}

		// Switch to the newly created blog.
		switch_to_blog( $new_site->blog_id );

		// Add the new tables.
		Activity_Log_Helper::create_log_tables();

		// Restore to the current blog.
		restore_current_blog();
	}

	/**
	 * Get the activity log lifetime.
	 *
	 * @since 1.3.3
	 *
	 * @return int $log_lifetime How many days the log is preserved, 12 by default.
	 */
	public static function get_activity_log_lifetime() {
		// Set custom log lifetime interval in days. The intval covers the cases for string, array and SQL injections.
		$log_lifetime = intval( apply_filters( 'sgs_set_activity_log_lifetime', get_option( 'sgs_activity_log_lifetime', 12 ) ) );

		// If the custom value is less than 1 day or more than 12, fallback to the default lifetime.
		if ( ( 1 > $log_lifetime ) || ( $log_lifetime > 12 ) ) {
			$log_lifetime = 12;
		}

		return $log_lifetime;
	}

	/**
	 * Delete old logs from events table.
	 *
	 * @since 1.4.4
	 *
	 * @return  int|bool False if tables do not exists, number of rows deleted.
	 */
	public function delete_old_events_logs() {
		global $wpdb;

		// Bail if table doesn't exist.
		if ( ! Helper::table_exists( $wpdb->sgs_log ) ) {
			return false;
		}

		// Get the activity log lifetime.
		$log_lifetime = self::get_activity_log_lifetime();

		$wpdb->query(
			$wpdb->prepare(
				'DELETE FROM `' . $wpdb->sgs_log . '`
					WHERE `ts` < %s
				;',
				time() - $log_lifetime * DAY_IN_SECONDS
			)
		);
	}

	/**
	 * Delete old logs from visitors table.
	 *
	 * @since 1.4.4
	 *
	 * @return  int|bool False if tables do not exists, number of rows deleted.
	 */
	public function delete_old_visitors_logs() {
		global $wpdb;

		// Bail if table doesn't exist.
		if (
			! Helper::table_exists( $wpdb->sgs_log ) ||
			! Helper::table_exists( $wpdb->sgs_visitors )
		) {
			return false;
		}

		$wpdb->query(
			'DELETE `' . $wpdb->sgs_visitors . '`
				FROM `' . $wpdb->sgs_visitors . '`
				LEFT JOIN `' . $wpdb->sgs_log . '` ON `' . $wpdb->sgs_visitors . '`.id = `' . $wpdb->sgs_log . '`.visitor_id
				WHERE `' . $wpdb->sgs_visitors . '`.user_id = 0
				AND `' . $wpdb->sgs_visitors . '`.block = 0
				AND `' . $wpdb->sgs_log . '`.visitor_id IS NULL
			;'
		);
	}
}
