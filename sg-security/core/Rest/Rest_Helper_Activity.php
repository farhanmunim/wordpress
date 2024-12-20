<?php
namespace SG_Security\Rest;

use SG_Security\Block_Service\Block_Service;
use SG_Security\Helper\Helper;
use SG_Security\Activity_Log\Activity_Log;
use SG_Security\Activity_Log\Activity_Log_Weekly_Emails;
use SG_Security\Rest\Rest_Helper_Options;

/**
 * Rest Helper class that manages all of the options.
 */
class Rest_Helper_Activity extends Rest_Helper {

	/**
	 * Entries per page.
	 *
	 * @var int
	 */
	public $number_of_entries = 30;

	/**
	 * Local variables
	 *
	 * @var mixed
	 */
	public $block_service;
	public $weekly_emails;
	public $activity_log;
	public $rest_helper_options;

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->block_service       = new Block_Service();
		$this->weekly_emails       = new Activity_Log_Weekly_Emails();
		$this->activity_log        = new Activity_Log();
		$this->rest_helper_options = new Rest_Helper_Options();
	}

	/**
	 * Get the total number of entries.
	 *
	 * @since  1.0.0
	 *
	 * @param  boolean $registered Whether to return total entries for registered or unknown.
	 *
	 * @return int                 Total number of pages to dispaly.
	 */
	public function get_total_pages( $query ) {
		global $wpdb;

		$query = preg_replace( '~LIMIT(.*)$~', ';', $query );

		$total_entries = $wpdb->get_results( // phpcs:ignore
			$query, // phpcs:ignore
			ARRAY_A
		);

		return ceil( count( $total_entries ) / $this->number_of_entries );
	}

	/**
	 * Get unknown activity filters.
	 *
	 * @since  1.0.0
	 *
	 * @param  object $request  The rest request.
	 *
	 * @return array            The unknown activity filters.
	 */
	public function get_unknown_filters( $request ) {
		global $wpdb;

		// Bail if table doesn't exist.
		if ( ! Helper::table_exists( $wpdb->sgs_log ) ) {
			return array();
		}

		$db_fields = $wpdb->get_results( // phpcs:ignore
			'SELECT `ts`, `code`, `visitor_type`, `ip` FROM `' . $wpdb->sgs_log . '`
				WHERE `visitor_type` != "user"
			;',
			ARRAY_A
		);

		$body = json_decode( $request->get_body(), 1 );

		$filters = $this->get_request_filters( $request );

		$dates         = array();
		$visitor_types = array();
		$codes         = array();
		$ips           = array();

		foreach ( $db_fields as $entry ) {
			// Get dates.
			$dates[] = $entry['ts'];

			// Get visitor types.
			$visitor_types[ $entry['visitor_type'] ] = array(
				'label' => $entry['visitor_type'],
				'value' => $entry['visitor_type'],
			);

			// Get status code.
			$codes[ $entry['code'] ] = array(
				'label' => $entry['code'],
				'value' => $entry['code'],
			);

			// Get status code.
			$ips[ $entry['ip'] ] = array(
				'label' => $entry['ip'],
				'value' => $entry['ip'],
			);
		}

		return array(
			array(
				'type'       => 'datepicker',
				'groupTitle' => 'By Date',
				'wp_name'    => 'date',
				'children'   => array(
					array(
						'id'    => 'from',
						'label' => 'From',
						'value' => ! empty( $filters['from'] ) ? intval( $filters['from'] ) : null,
					),
					array(
						'id'    => 'to',
						'label' => 'To',
						'value' => ! empty( $filters['to'] ) ? intval( $filters['to'] ) : null,
					),
				),
			),
			array(
				'type'     => 'dropdown',
				'wp_name'  => 'type',
				'children' => array(
					array(
						'id'            => 1,
						'label'         => 'By Visitor Type',
						'optionLabel'   => 'label',
						'optionValue'   => 'value',
						'searchable'    => true,
						'placeholder'   => 'Select or start typing',
						'options'       => array_values( $visitor_types ),
						'selectedValue' => ! empty( $filters['type'] ) ? $filters['type'] : null,
						'value'         => ! empty( $filters['type'] ) ? $filters['type'] : null,
					),
				),
			),
			array(
				'type'     => 'dropdown',
				'wp_name'  => 'code',
				'children' => array(
					array(
						'id'            => 2,
						'label'         => 'By Response',
						'optionLabel'   => 'label',
						'optionValue'   => 'value',
						'searchable'    => true,
						'placeholder'   => 'Select or start typing',
						'options'       => array_values( $codes ),
						'selectedValue' => ! empty( $filters['code'] ) ? $filters['code'] : null,
						'value'         => ! empty( $filters['code'] ) ? $filters['code'] : null,
					),
				),
			),
			array(
				'type'     => 'dropdown',
				'wp_name'  => 'ip',
				'children' => array(
					array(
						'placeholder'   => 'Select or start typing',
						'label'         => 'By IP',
						'optionLabel'   => 'label',
						'optionValue'   => 'value',
						'searchable'    => true,
						'options'       => array_values( $ips ),
						'selectedValue' => ! empty( $filters['ip'] ) ? $filters['ip'] : null,
						'value'         => ! empty( $filters['ip'] ) ? $filters['ip'] : null,
					),
				),
			),
		);
	}

	/**
	 * Get registered activity filters.
	 *
	 * @since  1.0.0
	 *
	 * @param  object $request  The rest request.
	 *
	 * @return array            The registered activity filters.
	 */
	public function get_registered_activity_filters( $request, $visitors ) {
		global $wpdb;

		// Bail if table doesn't exist.
		if ( ! Helper::table_exists( $wpdb->sgs_log ) ) {
			return array();
		}

		$db_fields = $wpdb->get_results( // phpcs:ignore
			'SELECT `ts`, `activity`, `visitor_id` FROM `' . $wpdb->sgs_log . '`
				WHERE `visitor_type` = "user"
			;',
			ARRAY_A
		);

		$body = json_decode( $request->get_body(), 1 );

		$filters = $this->get_request_filters( $request, true );

		$dates      = array();
		$activities = array();
		$users      = array();

		foreach ( $db_fields as $entry ) {
			$user_data = $this->get_user_data( $entry, $visitors );
			$dates[] = $entry['ts'];

			$activities[ $entry['activity'] ] = array(
				'label' => $entry['activity'],
				'value' => $entry['activity'],
			);

			$users[ $entry['visitor_id'] ] = array(
				'label' => $user_data['nicename'],
				'value' => $entry['visitor_id'],
			);
		}

		return array(
			array(
				'type'       => 'datepicker',
				'groupTitle' => 'By Date',
				'wp_name'    => 'date',
				'children'   => array(
					array(
						'id' => 'from',
						'label' => 'From',
						'value' => ! empty( $filters['from'] ) ? intval( $filters['from'] ) : null,
					),
					array(
						'id' => 'to',
						'label' => 'To',
						'value' => ! empty( $filters['to'] ) ? intval( $filters['to'] ) : null,
					),
				),
			),
			array(
				'type'     => 'dropdown',
				'wp_name'  => 'user',
				'children' => array(
					array(
						'id'            => 1,
						'label'         => 'By User',
						'optionLabel'   => 'label',
						'optionValue'   => 'value',
						'placeholder'   => 'Select or start typing',
						'searchable'    => true,
						'options'       => array_values( $users ),
						'selectedValue' => ! empty( $filters['user'] ) ? $filters['user'] : null,
					),
				),
			),
			array(
				'type'     => 'dropdown',
				'wp_name'  => 'activity',
				'children' => array(
					array(
						'id'            => 2,
						'label'         => 'By Activity',
						'optionLabel'   => 'label',
						'optionValue'   => 'value',
						'searchable'    => true,
						'placeholder'   => 'Select or start typing',
						'options'       => array_values( $activities ),
						'selectedValue' => ! empty( $filters['activity'] ) ? $filters['activity'] : null,
					),
				),
			),
		);
	}

	/**
	 * Get the activity of non logged-in users.
	 *
	 * @since  1.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function unknown_activity( $request ) {
		global $wpdb;

		$paged        = $this->validate_and_get_option_value( $request, 'page', false );
		$limited_view = $this->validate_and_get_option_value( $request, 'limitedView', false ); // phpcs:ignore
		$data         = array();

		// Bail if table doesn't exist.
		if ( ! Helper::table_exists( $wpdb->sgs_visitors ) ) {
			// Send the options to react app.
			return self::send_response(
				'',
				0,
				array(
					'entries' => $data,
					'filters' => $this->get_unknown_filters( $request, array() ),
					'page'    => false === $paged ? 1 : $paged,
					'pages'   => 1,
				)
			);
		}

		$query = $this->get_query( $request );
		$entries = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore

		$users = $wpdb->get_results( // phpcs:ignore
			'SELECT * FROM `' . $wpdb->sgs_visitors . '`
				WHERE `user_id` = 0
			;',
			OBJECT_K
		);

		foreach ( $entries as $entry ) {
			$data[] = array(
				'id'           => $entry['id'],
				'ts'           => get_date_from_gmt( gmdate( 'Y-m-d H:i', $entry['ts'] ), 'Y-m-d H:i' ),
				'ip'           => $entry['ip'],
				'page_visited' => $entry['description'],
				'type'         => $entry['visitor_type'],
				'hostname'     => $entry['hostname'],
				'response'     => $entry['code'],
				'visitor_id'   => $entry['visitor_id'],
				'block'        => $users[ $entry['visitor_id'] ]->block,
			);
		}

		// Send the options to react app.
		return self::send_response(
			'',
			1,
			array(
				'entries' => $data,
				'filters' => $this->get_unknown_filters( $request, $entries ),
				'page'    => false === $paged ? 1 : $paged,
				'pages'   => ! empty( $limited_view ) ? 1 : $this->get_total_pages( $query ),
			)
		);
	}

	/**
	 * Get activity of registered users.
	 *
	 * @since  1.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function registered_activity( $request ) {
		global $wpdb;

		$paged        = $this->validate_and_get_option_value( $request, 'page', false );
		$limited_view = $this->validate_and_get_option_value( $request, 'limitedView', false ); // phpcs:ignore
		$data         = array();
		$query        = $this->get_query( $request, true );

		// Bail if table doesn't exist.
		if ( ! Helper::table_exists( $wpdb->sgs_visitors ) ) {
			// Send the options to react app.
			return self::send_response(
				'',
				0,
				array(
					'entries' => $data,
					'filters' => $this->get_unknown_filters( $request, array() ),
					'page'    => false === $paged ? 1 : $paged,
					'pages'   => 1,
				)
			);
		}

		$entries = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore

		$visitors = $wpdb->get_results( // phpcs:ignore
			'SELECT * FROM `' . $wpdb->sgs_visitors . '`
				WHERE `user_id` != 0
			;',
			OBJECT_K
		);

		foreach ( $entries as $entry ) {
			$user_data = $this->get_user_data( $entry, $visitors );
			$data[] = array(
				'id'           => $entry['id'],
				'ts'           => get_date_from_gmt( gmdate( 'Y-m-d H:i', $entry['ts'] ), 'Y-m-d H:i' ),
				'ip'           => $entry['ip'],
				'activity'     => $entry['description'],
				'hostname'     => $entry['hostname'],
				'user'         => $user_data['nicename'],
				'block'        => $user_data['blocked'],
				'response'     => $entry['code'],
				'visitor_id'   => $entry['visitor_id'],
				'do_not_block' => $user_data['do_not_block'],
			);
		}

		// Send the options to react app.
		return self::send_response(
			'',
			1,
			array(
				'entries' => $data,
				'filters' => $this->get_registered_activity_filters( $request, $visitors ),
				'page'    => false === $paged ? 1 : $paged,
				'pages'   => ! empty( $limited_view ) ? 1 : $this->get_total_pages( $query ),
			)
		);
	}

	/**
	 * Get user data by activity
	 *
	 * @since  1.0.0
	 *
	 * @param  array $log_entry Log data.
	 * @param  array $users     Users data.
	 *
	 * @return array            User data.
	 */
	public function get_user_data( $log_entry, $users ) {
		// Include the template.php if the function doesn't exists.
		if ( ! function_exists( 'get_user_by' ) ) {
			require_once ABSPATH . '/wp-includes/pluggable.php';
		}

		$visitor_data = $users[ $log_entry['visitor_id'] ];

		$user = \get_user_by( 'id', $visitor_data->user_id );

		if ( 'wpcli' === $log_entry['object_id'] ) {
			return array(
				'nicename'     => __( 'WP CLI', 'sg-security' ),
				'blocked'      => 0,
				'do_not_block' => 1,
			);
		}

		if ( 'system' === $log_entry['object_id'] ) {
			return array(
				'nicename'     => __( 'Server Systems', 'sg-security' ),
				'blocked'      => 0,
				'do_not_block' => 1,
			);
		}

		if ( empty( $user ) ) {
			return array(
				'nicename'     => __( 'Unknown user', 'sg-security' ),
				'blocked'      => 0,
				'do_not_block' => 0,
			);
		}

		return array(
			'nicename'     => $user->data->user_login,
			'blocked'      => $visitor_data->block,
			'do_not_block' => 0,
			'ts'           => $visitor_data->blocked_on,
		);
	}

	/**
	 * Get log entries.
	 *
	 * @since  1.0.0
	 *
	 * @param  array   $params     Array of params.
	 * @param  boolean $registered Whether to get unknown or registered logs.
	 *
	 * @return array               Array of all found entries.
	 */
	public function get_query( $request, $registered = false ) {
		global $wpdb;

		$filters     = $this->get_request_filters( $request );
		$paged       = $this->validate_and_get_option_value( $request, 'page', false );
		$limitedView = $this->validate_and_get_option_value( $request, 'limitedView', false ); // phpcs:ignore

		// Clauses.
		$select = 'SELECT * FROM ' . $wpdb->sgs_log;
		$where = ' WHERE `visitor_type` != "user"';
		$order = ' ORDER BY `ts` DESC';
		$limit = ' LIMIT ' . ( ! empty( $limitedView ) ? 5 : $this->number_of_entries ); // phpcs:ignore
		$offset = '';

		// Change the visitor type.
		if ( true === $registered ) {
			$where = ' WHERE `visitor_type` = "user"';
		}

		if ( ! empty( $filters['type'] ) ) {
			$where = ' WHERE `visitor_type` = "' . esc_sql( $filters['type'] ) . '"';
		}

		if ( ! empty( $filters['user'] ) ) {
			$where .= ' AND `visitor_id` = "' . esc_sql( $filters['user'] ) . '"';
		}

		if ( ! empty( $filters['activity'] ) ) {
			$where .= ' AND `activity` = "' . esc_sql( $filters['activity'] ) . '"';
		}

		if ( ! empty( $filters['from'] ) ) {
			$where .= ' AND `ts` >= "' . esc_sql( $filters['from'] ) . '"';
		}

		if ( ! empty( $filters['to'] ) ) {
			$where .= ' AND `ts` <= "' . esc_sql( $filters['to'] ) . '"';
		}

		if ( ! empty( $filters['ip'] ) ) {
			$where .= ' AND `ip` LIKE "%' . esc_sql( $filters['ip'] ) . '%"';
		}

		if ( ! empty( $filters['code'] ) ) {
			$where .= ' AND `code` = "' . esc_sql( $filters['code'] ) . '"';
		}

		if ( ! empty( $paged ) ) {
			$offset .= ' OFFSET ' . intval( ( esc_sql( $paged ) * $this->number_of_entries ) - $this->number_of_entries );
		}

		return $select . $where . $order . $limit . $offset . ';';
	}

	/**
	 * Gets the request filters.
	 *
	 * @param      object $request  The request
	 *
	 * @return     array   The request filters.
	 */
	public function get_request_filters( $request ) {
		$body    = json_decode( $request->get_body(), 1 );
		$filters = array();

		if ( ! empty( $body['filters'] ) ) {
			foreach ( $body['filters'] as $filter ) {
				if ( 'date' === $filter['wp_name'] ) {
					$filters['from'] = ! empty( $filter['children'][0]['selectedValue'] ) ? $filter['children'][0]['selectedValue'] : $filter['children'][0]['value'];
					$filters['to'] = ! empty( $filter['children'][1]['selectedValue'] ) ? $filter['children'][1]['selectedValue'] : $filter['children'][1]['value'];

					continue;
				}
				$filters[ $filter['wp_name'] ] = $filter['children'][0]['value'];
			}
		}

		return $filters;
	}

	/**
	 * Block an IP address.
	 *
	 * @since  1.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function block_ip( $request ) {
		$params = $request->get_params( $request );
		$body   = json_decode( $request->get_body(), true );

		if ( empty( $params['id'] ) ) {
			return self::send_response(
				__( 'Missing ID param!', 'sg-security' ),
				0
			);
		}

		$response = $this->block_service->block_ip( $params['id'], $body['block'] );

		return self::send_response(
			$response['message'],
			$response['result']
		);
	}

	/**
	 * Unblock an IP address, blocked by the Limit Login Attempts functionality.
	 *
	 * @since  1.2.2
	 *
	 * @param  object $request Request data.
	 */
	public function login_unblock( $request ) {
		// Get the request body.
		$body = json_decode( $request->get_body(), true );

		// Bail if IP is not passed.
		if ( empty( $body['ip'] ) ) {
			return self::send_response(
				__( 'Missing IP param!', 'sg-security' ),
				0
			);
		}

		// Get the unsuccessfull login attempts data.
		$login_attempts = get_option( 'sg_security_unsuccessful_login', array() );

		// Remove the IP from the option.
		unset( $login_attempts[ $body['ip'] ] );

		// Update the option with the new value.
		update_option( 'sg_security_unsuccessful_login', $login_attempts );

		// Send the response.
		return self::send_response(
			__( 'IP Unblocked.', 'sg-security' ),
			1
		);
	}

	/**
	 * Limit  user capabilities based on ID.
	 *
	 * @since  1.0.0
	 *
	 * @param  Object $request The request object.
	 */
	public function block_user( $request ) {
		// Get the request params.
		$params = $request->get_params( $request );
		// Get the request body.
		$body = json_decode( $request->get_body(), true );

		if ( empty( $params['id'] ) ) {
			return self::send_response(
				__( 'Missing ID param!', 'sg-security' ),
				0
			);
		}

		switch ( $body['block'] ) {
			// Unblock request.
			case 0:
				$response = $this->block_service->unblock_user( $params['id'] );
				break;
			// Block request.
			case 1:
				$response = $this->block_service->change_user_role( $params['id'] );
				break;
		}

		// Send the response.
		return self::send_response(
			$response['message'],
			$response['result']
		);
	}

	/**
	 * Limit  user capabilities based on ID.
	 *
	 * @since  1.0.0
	 *
	 * @param  Object $request The request object.
	 */
	public function get_visitor_status( $request ) {
		$params = $request->get_params( $request );

		if ( empty( $params['id'] ) ) {
			return self::send_response(
				__( 'Missing ID param!', 'sg-security' ),
				0
			);
		}

		$response = $this->block_service->get_visitor_status( $params['id'] );

		return self::send_response(
			'',
			$response['result'],
			$response['data']
		);
	}

	/**
	 * Ge the blocked users/IPs
	 *
	 * @since  1.0.0
	 *
	 * @param  Object $request The request object.
	 */
	public function get_blocked_user( $request ) {
		global $wpdb;
		$results = $wpdb->get_results( // phpcs:ignore
			'SELECT * FROM `' . $wpdb->sgs_visitors . '`
				WHERE `block` = 1
			;',
			ARRAY_A
		);

		$visitors = $wpdb->get_results( // phpcs:ignore
			'SELECT * FROM `' . $wpdb->sgs_visitors . '`
				WHERE `user_id` != 0
			;',
			OBJECT_K
		);

		$data = array();

		// Get the unsuccessfull login attempts data.
		$limit_login_attempts = get_option( 'sg_security_unsuccessful_login', array() );

		foreach ( $results as $entry ) {
			$log = array(
				'ts'         => get_date_from_gmt( gmdate( 'Y-m-d H:i', $entry['blocked_on'] ), 'Y-m-d H:i' ),
				'user'       => $entry['ip'],
				'visitor_id' => $entry['id'],
				'object_id'  => $entry['user_id'],
				'type'       => 0 == $entry['user_id'] ? 'ip' : 'user',
			);

			if ( ! empty( $entry['user_id'] ) ) {
				$user_data = $this->get_user_data( $log, $visitors );
				$log['user'] = $user_data['nicename'];
			}

			$data[] = $log;
		}

		foreach ( $limit_login_attempts as $ip => $attempt ) {
			// Check if IP is blocked.
			if ( empty( $attempt['timestamp'] ) ) {
				continue;
			}

			$log = array(
				'ts'         => get_date_from_gmt( gmdate( 'Y-m-d H:i', $attempt['timestamp'] ), 'Y-m-d H:i' ),
				'user'       => $ip,
				'visitor_id' => 0,
				'object_id'  => $ip,
				'type'       => 'ip',
			);

			$data[] = $log;
		}

		// Send the options to react app.
		return self::send_response(
			'',
			1,
			array(
				'entries' => $data,
			)
		);
	}

	/**
	 * Get the emails set to receive weekly report email.
	 *
	 * @since 1.2.0
	 *
	 * @param Object $request The request object.
	 */
	public function get_weekly_report_recipients( $request ) {
		$data = $this->weekly_emails->weekly_report_receipients();

		// Send the options to react app.
		return self::send_response(
			'',
			1,
			array(
				'entries'    => $data,
				'max_emails' => 5,
			)
		);
	}

	/**
	 * Manage the weekly report notification email addresses.
	 *
	 * @since 1.2.0
	 *
	 * @param Object $request The request object.
	 */
	public function manage_notification_emails( $request ) {
		$data = json_decode( $request->get_body(), true );

		// Update the option.
		update_option( 'sg_security_notification_emails', array_unique( array_column( $data['entries'], 'email' ) ) );

		return self::send_response(
			__( 'Notification emails updated.', 'sg-security' ),
			1,
			array(
				'weeklyReports' => array(
					$this->weekly_emails->weekly_report_receipients(),
				),
			)
		);
	}

	/**
	 * Enable or disable the activity log.
	 *
	 * @since 1.3.3
	 *
	 * @param object $request Request data.
	 */
	public function manage_activity_log( $request ) {
		return $this->rest_helper_options->change_option_from_rest( $request, 'disable_activity_log' );
	}

	/**
	 * Manage the activity log lifetime.
	 *
	 * @since 1.3.3
	 *
	 * @param object $request Request data.
	 */
	public function activity_log_lifetime( $request ) {
		// Validate the request.
		$log_lifetime = intval( $this->validate_and_get_option_value( $request, 'log_lifetime' ) );

		// Update the activity log lifetime.
		update_option( 'sgs_activity_log_lifetime', $log_lifetime );

		// Delete the old log records from the database.
		$this->activity_log->delete_old_activity_logs();

		return self::send_response(
			'Activity log lifetime updated!',
			1,
			$this->prepare_options_selected_values( array_combine( range( 1, 12 ), range( 1, 12 ) ), intval( get_option( 'sgs_activity_log_lifetime', 12 ) ) )
		);
	}
}
