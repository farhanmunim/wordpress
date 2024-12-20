<?php
namespace SG_Security\Activity_Log;

use SiteGround_Helper\Helper_Service;
use SiteGround_Emails\Email_Service;
use SG_Security\Activity_Log\Activity_Log;

/**
 * Activity Log Weekly Emails class
 */
class Activity_Log_Weekly_Emails extends Activity_Log_Helper {

	/**
	 * Weekly report email.
	 *
	 * @var Email_Service
	 */
	public $weekly_report_email;

	/**
	 * Database placeholder.
	 */
	public $wpdb;

	/**
	 * The constructor.
	 *
	 * @since 1.2.0
	 */
	public function __construct() {
		// Assign the Database.
		global $wpdb;
		$this->wpdb = $wpdb;

		// Initiate the Email Service Class.
		$this->weekly_report_email = new Email_Service(
			'sgs_email_cron',
			'weekly',
			strtotime( 'next monday + ' . rand( 1, 360 ) . ' minutes' ),
			array(
				'recipients_option' => 'sg_security_notification_emails',
				'subject'           => 'Weekly Activity for ' . Helper_Service::get_site_url(),
				'body_method'       => array( '\SG_Security\Activity_Log\Activity_Log_Weekly_Emails', 'generate_message_body' ),
				'from_name'         => 'Security Optimizer by SiteGround',
			)
		);
	}

	/**
	 * Generate the message body and return it to the constructor.
	 *
	 * @since  1.2.0
	 *
	 * @return string $message_body HTML of the message body.
	 */
	static function generate_message_body() {
		// Do not sent the message if activity log lifetime is set to less than 8 days.
		if ( 8 > Activity_Log::get_activity_log_lifetime() ) {
			return false;
		}

		$weekly_emails = new Activity_Log_Weekly_Emails();

		// Activity Log page URL.
		$activity_log_url = admin_url( '/admin.php?page=activity-log' );

		// Generate the start date we should collect the data from.
		$start_date = $weekly_emails->get_last_cron_run()->modify( 'last monday' );
		// Generate the end date we should collect the data to.
		$end_date = $weekly_emails->get_last_cron_run();

		// Get the count of total human visits for the period.
		$total_human = (int) $weekly_emails->get_total_human_stats( $start_date->getTimestamp(), $end_date->getTimestamp() );
		// Get the count of total bots visit for the period.
		$total_bots = (int) $weekly_emails->get_total_bots_stats( $start_date->getTimestamp(), $end_date->getTimestamp() );
		// Get the count of total blocked login attempts.
		$total_blocked_login = (int) get_option( 'sg_security_total_blocked_logins', 0 );
		// Get the count of total blocked visits.
		$total_blocked_visits = (int) get_option( 'sg_security_total_blocked_visits', 0 );

		// Bail if all stats are 0.
		if (
			0 === $total_human &&
			0 === $total_bots &&
			0 === $total_blocked_login &&
			0 === $total_blocked_visits
		) {
			return false;
		}

		// Get assets from remote server.
		$assets = $weekly_emails->get_remote_assets();

		// Bail if we do not get the templates.
		if ( false === $assets ) {
			return false;
		}

		// Sanitize paths.
		$assets = $weekly_emails->prepare_paths( $assets );

		// Get the locale.
		$locale = get_locale();

		// Locales that need translation.
		$maybe_translate = array(
			'es_ES',
			'it_IT',
			'de_DE',
		);

		// If the locale is not in the array, set the default locale.
		if ( ! in_array( $locale, $maybe_translate, true ) ) {
			$locale = 'en_US';
		}

		// Mail template arguments.
		$args = array(
			'domain'               => Helper_Service::get_site_url(),
			'activity_log_link'    => $activity_log_url,
			'unsubscribe_link'     => $activity_log_url,
			'start_time'           => ( 'en_US' === $locale ) ? $start_date->format( 'F d' ) : $weekly_emails->translate_dates( $start_date, $locale, 'start' ),
			'end_time'             => ( 'en_US' === $locale ) ? $end_date->format( 'F d, Y' ) : $weekly_emails->translate_dates( $end_date, $locale, 'end' ),
			'is_siteground'        => Helper_Service::is_siteground(),
			'agreed_email_consent' => (int) get_option( 'siteground_email_consent', 0 ),
			'total_human'          => $total_human,
			'total_bots'           => $total_bots,
			'total_blocked_login'  => $total_blocked_login,
			'total_blocked_visits' => $total_blocked_visits,
			'email_image'          => $assets['image'],
			'email_body'           => $assets['email_body'],
			'intro_path'           => $assets['intro_path'],
			'learn_more_path'      => $assets['learn_more_path'],
			'non_sg'               => $assets['non_sg'],
			'unsubscribe'          => $assets['unsubscribe'],
		);

		// Turn on output buffering.
		ob_start();

		// Include the template file.
		include \SG_Security\DIR . '/templates/weekly_report.php';

		// Pass the contents of the output buffer to the variable.
		$message_body = ob_get_contents();

		// Clean the output buffer and turn off output buffering.
		ob_end_clean();

		// Return the message body content as a string.
		return $message_body;
	}

	/**
	 * Get assets from remote json.
	 *
	 * @since  1.2.4
	 *
	 * @return bool/array false if we fail the request/Array with data.
	 */
	private function get_remote_assets() {
		// Get the banner content.
		$response = wp_remote_get( 'https://sgwpdemo.com/jsons/sg-security-emails.json' );

		// Bail if the request fails.
		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		// Get the locale.
		$locale = get_locale();

		// Get the body of the response.
		$body = wp_remote_retrieve_body( $response );

		// Decode the json response.
		$assets = json_decode( $body, true );

		// Check if we need to return a specific locale assets.
		if ( array_key_exists( $locale, $assets ) ) {
			// Add the locale name so we skip re-use of get_locale in message builder.
			$assets[ $locale ]['lang'] = $locale;

			// Return the locale specific assets.
			return $assets[ $locale ];
		}

		// Set the default locale.
		$assets['default']['lang'] = 'default';

		// Return the correct assets, title and marketing urls.
		return $assets['default'];
	}

	/**
	 * Update the timestamp when the cron event was last ran.
	 *
	 * @since 1.2.0
	 */
	public function update_last_cron_run_timestamp() {
		update_option( 'sg_security_weekly_email_timestamp', time() );
	}

	/**
	 * Get the last time the cron event was ran.
	 *
	 * @since  1.2.0
	 *
	 * @return object $last_run_time DateTime object.
	 */
	public function get_last_cron_run() {
		// DateTime object.
		$last_run_time = new \DateTime();

		// Get the timestamp and convert it to DateTime object.
		$last_run_time->setTimestamp( get_option( 'sg_security_weekly_email_timestamp', time() ) );

		return $last_run_time;
	}

	/**
	 * Get stats for total human visits in the past week.
	 *
	 * @since  1.2.0
	 *
	 * @param  int $start_date Start date timestamp.
	 * @param  int $end_date   End date timestamp.
	 *
	 * @return int             The number of total human visits.
	 */
	private function get_total_human_stats( $start_date, $end_date ) {

		$query = $this->wpdb->prepare(
			'SELECT COUNT(*) FROM `' . esc_sql( $this->wpdb->prefix . 'sgs_log_events' ) . "`
				WHERE `action` = 'visit'
				AND `visitor_type` = 'Human'
				AND `type` = 'unknown'
				AND `ts` BETWEEN %s AND %s",
			$start_date,
			$end_date
		);

		return $this->wpdb->get_var( $query ); //phpcs:ignore
	}

	/**
	 * Get stats for total bots visits in the past week.
	 *
	 * @since  1.2.0
	 *
	 * @param  int $start_date Start date timestamp.
	 * @param  int $end_date   End date timestamp.
	 *
	 * @return int             The number of total bots visits.
	 */
	private function get_total_bots_stats( $start_date, $end_date ) {

		$query = $this->wpdb->prepare(
			'SELECT COUNT(*) FROM `' . esc_sql( $this->wpdb->prefix . 'sgs_log_events' ) . "`
				WHERE `action` = 'visit'
				AND `visitor_type` <> 'Human'
				AND `visitor_type` <> 'unknown'
				AND `type` = 'unknown'
				AND `ts` BETWEEN %s AND %s",
			$start_date,
			$end_date
		);

		return $this->wpdb->get_var( $query ); //phpcs:ignore
	}

	/**
	 * Reset the block stats counters.
	 *
	 * @since 1.2.0
	 */
	public function reset_weekly_stats_counters() {
		// Reset the total blocked visits counter.
		update_option( 'sg_security_total_blocked_visits', 0 );
		// Reset the total blocked logins counter.
		update_option( 'sg_security_total_blocked_logins', 0 );
	}

	/**
	 * Get notification recipient emails.
	 *
	 * @since  1.2.0
	 *
	 * @return Object $data Array Object with the list of emails set to receive notifications.
	 */
	public function weekly_report_receipients() {
		$data = array();

		// Get the currently set recipients.
		$receipients = get_option( 'sg_security_notification_emails', array() );

		// Return empty array if no recipients are set.
		if ( empty( $receipients ) ) {
			return $data;
		}

		// Convert the data to an email key array.
		foreach ( $receipients as $entry ) {
			$data[] = array( 'email' => $entry );
		}

		// Return the data.
		return $data;
	}

	/**
	 * Prepare safe paths for templates.
	 *
	 * @since  1.3.0
	 *
	 * @param  array $assets The assets array from sgwpdemo.
	 *
	 * @return array $assets The assets array from sgwpdemo.
	 */
	private function prepare_paths( $assets ) {
		// Set the default paths.
		$default_paths = array(
			'intro_path'      => \SG_Security\DIR . '/templates/partials/weekly-report/intro/default.php',
			'learn_more_path' => \SG_Security\DIR . '/templates/partials/weekly-report/learn-more/default.php',
		);

		// Skip path traversal if any.
		$file = str_replace(
			'../',
			'',
			$assets['lang']
		);

		// Merge the default ones with the assets and send them if we have default locale.
		if ( 'default' === $file ) {
			return array_merge( $assets, $default_paths );
		}

		// Prepare path based on type and language.
		$paths = preg_replace(
			'~{LANG}~',
			$file,
			array(
				'intro_path' => \SG_Security\DIR . '/templates/partials/weekly-report/intro/{LANG}.php',
				'learn_more_path' => \SG_Security\DIR . '/templates/partials/weekly-report/learn-more/{LANG}.php',
			)
		);

		// Loop the new paths.
		foreach ( $paths as $type => $path ) {

			// Check if the file exists.
			if ( file_exists( $path ) ) {
				// Add the new path based on language.
				$assets[ $type ] = $path;
				continue;
			}

			// Set the default one if the file is trying to traverse.
			$assets[ $type ] = $default_paths[ $type ];
		}

		// Return the assets array.
		return $assets;
	}

	/**
	 * Translate dates based on locale.
	 *
	 * @since 1.4.8
	 *
	 * @param  DateTime $date   DateTime Object containing the specific start/end date.
	 * @param  string   $locale The application locale.
	 * @param  string   $type   The type of date - start/end.
	 *
	 * @return string           The translated date.
	 */
	public function translate_dates( $date, $locale, $type ) {
		// Months translations.
		$translations = array(
			'es_ES' => array(
				'January'   => 'enero',
				'February'  => 'febrero',
				'March'     => 'marzo',
				'April'     => 'abril',
				'May'       => 'mayo',
				'June'      => 'junio',
				'July'      => 'julio',
				'August'    => 'agosto',
				'September' => 'septiembre',
				'October'   => 'octubre',
				'November'  => 'noviembre',
				'December'  => 'diciembre',
			),
			'it_IT' => array(
				'January'   => 'gennaio',
				'February'  => 'febbraio',
				'March'     => 'marzo',
				'April'     => 'aprile',
				'May'       => 'maggio',
				'June'      => 'giugno',
				'July'      => 'luglio',
				'August'    => 'agosto',
				'September' => 'settembre',
				'October'   => 'ottobre',
				'November'  => 'novembre',
				'December'  => 'dicembre',
			),
			'de_DE' => array(
				'January'   => 'Januar',
				'February'  => 'Februar',
				'March'     => 'März',
				'April'     => 'April',
				'May'       => 'Mai',
				'June'      => 'Juni',
				'July'      => 'Juli',
				'August'    => 'August',
				'September' => 'September',
				'October'   => 'Oktober',
				'November'  => 'November',
				'December'  => 'Dezember',
			),
		);

		// Translate the dates based on types.
		if ( 'start' === $type ) {
			return $translations[ $locale ][ $date->format( 'F' ) ] . ' ' . $date->format( 'd' );
		}

		return $translations[ $locale ][ $date->format( 'F' ) ] . ' ' . $date->format( 'd, Y' );
	}
}
