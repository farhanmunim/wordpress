<?php
namespace SG_Security\Cli;

use SG_Security\Options_Service\Options_Service;
use SG_Security\Rest\Rest_Helper_Activity;
use SG_Security\Helper\Helper;

/**
 * WP-CLI: wp sg list {setting} value.
 *
 * Run the `wp sg list value` command to list specific logs.
 *
 * @since 1.1.0
 * @package Cli
 * @subpackage Cli/Cli_List
 */

/**
 * Define the {@link Cli_List} class.
 *
 * @since 1.1.0
 */
class Cli_List {

	/**
	 * The default number of days to show if no period specified.
	 *
	 * @var integer
	 */
	public $period = 1;

	/**
	 * The default interval on which the data should be gathered.
	 *
	 * @var null
	 */
	public $interval = 0;

	/**
	 * Property for the wp database.
	 *
	 * @var null
	 */
	public $wpdb = null;

	/**
	 * The property to hold the db query.
	 *
	 * @var String.
	 */
	public $query;

	/**
	 * Rest_Helper_Activity class.
	 *
	 * @var Object.
	 */
	public $rest_helper_activity;

	/**
	 * Enable specific setting for SiteGround Optimizer plugin.
	 *
	 * ## OPTIONS
	 *
	 * <setting>
	 * : Setting name.
	 * ---
	 * options:
	 *  - log-unknown
	 *  - log-registered
	 *  - log-blocked
	 * ---
	 *
	 * [--days=<days>]
	 * : Days interval.
	 */
	public function __invoke( $args, $assoc_args ) {
		// Build the method name.
		$method = 'list_' . str_replace( '-', '_', $args[0] );

		// Check if method exist.
		if ( true !== method_exists( $this, $method ) ) {
			return \WP_CLI::error( 'Non-existing method.' );
		}

		// Set the custom time period if user has specified it.
		if ( ! empty( $assoc_args ) ) {
			$this->period = intval( $assoc_args['days'] );
		}

		// Set the interval property.
		$this->interval = time() - 86400 * $this->period;

		// Set the db property.
		global $wpdb;
		$this->wpdb = $wpdb;

		// Build the query.
		$this->query = $this->get_query( $args[0] );

		// Call the Rest Helper Activity to get user-data.
		$this->rest_helper_activity = new Rest_Helper_Activity();

		// Call the method and send arguments.
		call_user_func( array( $this, $method ) );
	}

	/**
	 * Prepare the query for unknown and registered logs.
	 *
	 * @since  1.1.0
	 *
	 * @param  string $type The type of log we want.
	 *
	 * @return string       The SQL  query.
	 */
	public function get_query( $type ) {
		// Bail if table doesn't exist.
		if ( ! Helper::table_exists( $this->wpdb->sgs_log ) ) {
			return false;
		}

		// Prepare the clauses.
		$select = 'SELECT * FROM ' . $this->wpdb->sgs_log;
		$where  = ' WHERE `visitor_type` != "user"';
		$order  = ' ORDER BY `ts` DESC;';

		switch ( $type ) {
			// Set the period for unknown log.
			case 'log-unknown':
				$where .= ' AND `ts` <= ' . time() . ' AND `ts` >= ' . $this->interval;
				break;
			// Change the where clause for registered and add search interval.
			case 'log-registered':
				$where = ' WHERE `visitor_type` = "user"  AND `ts` <= ' . time() . ' AND `ts` >= ' . $this->interval;
				break;
			case 'log-blocked':
				$select = 'SELECT * FROM ' . $this->wpdb->sgs_visitors;
				$where  = ' WHERE `block` = 1';
				$order  = ' ORDER BY `blocked_on` DESC;';
				break;
		}

		// Return the query.
		return $select . $where . $order;
	}

	/**
	 * List the activity log for unknown visitors.
	 *
	 * @since  1.1.0
	 */
	public function list_log_unknown() {
		// Get the visitors log.
		$visitors = $this->wpdb->get_results( $this->query, ARRAY_A ); // phpcs:ignore

		$table_data = array();

		// Format the data and prepare it for printing in the terminal.
		foreach ( $visitors as $visit ) {
			$table_data[] = array(
				'Timestamp'    => get_date_from_gmt( gmdate( 'Y-m-d h:i:s', $visit['ts'] ), 'Y-m-d H:i' ),
				'Visitor Type' => $visit['visitor_type'],
				'IP Address'   => $visit['ip'],
				'Page Visited' => $visit['activity'],
				'Response'     => $visit['code'],
			);
		}

		// Show the logs.
		\WP_CLI\Utils\format_items( 'table', $table_data, array( 'Timestamp', 'Visitor Type', 'IP Address', 'Page Visited', 'Response' ) );
	}

	/**
	 * List the blocked users list.
	 *
	 * @since  1.1.0
	 */
	public function list_log_blocked() {
		// Get the blocked visitors result.
		$results = $this->wpdb->get_results( $this->query, ARRAY_A ); // phpcs:ignore

		// Get all user visitors from the database.
		$query = $this->wpdb->prepare(
			'SELECT * FROM `' . esc_sql( $this->wpdb->sgs_visitors ) . '`
				WHERE `user_id` != %d
			;',
			0
		);

		$visitors = $this->wpdb->get_results( $query, OBJECT_K ); // phpcs:ignore

		// Loop results and get necessary data.
		$data = array();
		foreach ( $results as $entry ) {
			$log = array(
				'ts'         => get_date_from_gmt( gmdate( 'Y-m-d H:i', $entry['blocked_on'] ), 'Y-m-d H:i' ),
				'user'       => $entry['ip'],
				'visitor_id' => $entry['id'],
				'object_id'  => $entry['user_id'],
				'type'       => 0 == $entry['user_id'] ? 'ip' : 'user',
			);

			// Check for username.
			if ( ! empty( $entry['user_id'] ) ) {
				$user_data = $this->rest_helper_activity->get_user_data( $log, $visitors );

				$log['user'] = $user_data['nicename'];
			}

			// Prepare the data to represent it in the table.
			$data[] = array(
				'Timestamp' => $log['ts'],
				'User/IP'   => $log['user'],
			);
		}

		// Show the logs.
		\WP_CLI\Utils\format_items( 'table', $data, array( 'Timestamp', 'User/IP' ) );
	}

	/**
	 * List registered users activity.
	 *
	 * @since  1.1.0
	 */
	public function list_log_registered() {
		// Get user entries.
		$entries = $this->wpdb->get_results( $this->query, ARRAY_A ); // phpcs:ignore

		// Get visitors data.
		$query = $this->wpdb->prepare(
			'SELECT * FROM `' . esc_sql( $this->wpdb->sgs_visitors ) . '`
				WHERE `user_id` != %d
			;',
			0
		);

		$visitors = $this->wpdb->get_results( $query, OBJECT_K ); // phpcs:ignore

		// Populate the data for the table.
		$table_data = array();
		foreach ( $entries as $entry ) {
			// Get the user data.
			$user_data = $this->rest_helper_activity->get_user_data( $entry, $visitors );

			// Add the data to the table array.
			$table_data[] = array(
				'Timestamp'  => get_date_from_gmt( gmdate( 'Y-m-d H:i', $entry['ts'] ), 'Y-m-d H:i' ),
				'IP Address' => $entry['ip'],
				'Activity'   => $entry['description'],
				'Hostname'   => $entry['hostname'],
				'Username'   => $user_data['nicename'],
				'Response'   => $entry['code'],
			);
		}

		// Show the logs.
		\WP_CLI\Utils\format_items( 'table', $table_data, array( 'Timestamp', 'IP Address', 'Activity', 'Hostname', 'Username', 'Response' ) );
	}
}
