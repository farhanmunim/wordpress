<?php
namespace SG_Security\Activity_Log;

use SG_Security\Helper\Helper;
/**
 * Class that manages the Activity log for unknown visits.
 */
class Activity_Log_Unknown extends Activity_Log_Helper {

	/**
	 * Known crawlers array.
	 *
	 * @var array
	 */
	public $crawlers = array(
		'googlebot',
		'adsbot',
		'bingbot',
		'slurp',
		'duckduckbot',
		'baiduspider',
		'yandexbot',
		'facebot',
		'ia_archiver',
		'petalbot',
		'jetmon',
		'semrushbot'
	);

	/**
	 * Known services array.
	 *
	 * @var array
	 */
	public $ping_services = array(
		'UptimeRobot' => array(
			'216.144.250.150',
			'69.162.124.226',
			'69.162.124.227',
			'69.162.124.228',
			'69.162.124.229',
			'69.162.124.230',
			'69.162.124.231',
			'69.162.124.232',
			'69.162.124.233',
			'69.162.124.234',
			'69.162.124.235',
			'69.162.124.236',
			'69.162.124.237',
			'63.143.42.242',
			'63.143.42.243',
			'63.143.42.244',
			'63.143.42.245',
			'63.143.42.246',
			'63.143.42.247',
			'63.143.42.248',
			'63.143.42.249',
			'63.143.42.250',
			'63.143.42.251',
			'63.143.42.252',
			'63.143.42.253',
			'216.245.221.82',
			'216.245.221.83',
			'216.245.221.84',
			'216.245.221.85',
			'216.245.221.86',
			'216.245.221.87',
			'216.245.221.88',
			'216.245.221.89',
			'216.245.221.90',
			'216.245.221.91',
			'216.245.221.92',
			'216.245.221.93',
			'208.115.199.18',
			'208.115.199.19',
			'208.115.199.20',
			'208.115.199.21',
			'208.115.199.22',
			'208.115.199.23',
			'208.115.199.24',
			'208.115.199.25',
			'208.115.199.26',
			'208.115.199.27',
			'208.115.199.28',
			'208.115.199.29',
			'208.115.199.30',
			'46.137.190.132',
			'122.248.234.23',
			'167.99.209.234',
			'178.62.52.237',
			'54.79.28.129',
			'54.94.142.218',
			'104.131.107.63',
			'54.67.10.127',
			'54.64.67.106',
			'159.203.30.41',
			'46.101.250.135',
			'18.221.56.27',
			'52.60.129.180',
			'159.89.8.111',
			'146.185.143.14',
			'139.59.173.249',
			'165.227.83.148',
			'128.199.195.156',
			'138.197.150.151',
			'34.233.66.117',
			'2607:ff68:107::3',
			'2607:ff68:107::4',
			'2607:ff68:107::5',
			'2607:ff68:107::6',
			'2607:ff68:107::7',
			'2607:ff68:107::8',
			'2607:ff68:107::9',
			'2607:ff68:107::10',
			'2607:ff68:107::11',
			'2607:ff68:107::12',
			'2607:ff68:107::13',
			'2607:ff68:107::14',
			'2607:ff68:107::15',
			'2607:ff68:107::16',
			'2607:ff68:107::17',
			'2607:ff68:107::18',
			'2607:ff68:107::19',
			'2607:ff68:107::20',
			'2607:ff68:107::21',
			'2607:ff68:107::22',
			'2607:ff68:107::23',
			'2607:ff68:107::24',
			'2607:ff68:107::25',
			'2607:ff68:107::26',
			'2607:ff68:107::27',
			'2607:ff68:107::28',
			'2607:ff68:107::29',
			'2607:ff68:107::30',
			'2607:ff68:107::31',
			'2607:ff68:107::32',
			'2607:ff68:107::33',
			'2607:ff68:107::34',
			'2607:ff68:107::35',
			'2607:ff68:107::36',
			'2607:ff68:107::37',
			'2607:ff68:107::38',
			'2607:ff68:107::39',
			'2607:ff68:107::40',
			'2607:ff68:107::41',
			'2607:ff68:107::42',
			'2607:ff68:107::43',
			'2607:ff68:107::44',
			'2607:ff68:107::45',
			'2607:ff68:107::46',
			'2607:ff68:107::47',
			'2607:ff68:107::48',
			'2607:ff68:107::49',
			'2607:ff68:107::50',
			'2607:ff68:107::51',
			'2607:ff68:107::52',
			'2607:ff68:107::53',
			'2607:ff68:107::54',
			'2607:ff68:107::55',
			'2a03:b0c0:2:d0::fa3:e001',
			'2a03:b0c0:1:d0::e54:a001',
			'2604:a880:800:10::4e6:f001',
			'2604:a880:cad:d0::122:7001',
			'2a03:b0c0:3:d0::33e:4001',
			'2600:1f16:775:3a01:70d6:601a:1eb5:dbb9',
			'2600:1f11:56a:9000:23:651b:dac0:9be4',
			'2a03:b0c0:3:d0::44:f001',
			'2a03:b0c0:0:1010::2b:b001',
			'2a03:b0c0:1:d0::22:5001',
			'2604:a880:400:d0::4f:3001',
			'2400:6180:0:d0::16:d001',
			'2604:a880:cad:d0::18:f001',
			'2600:1f18:179:f900:88b2:b3d:e487:e2f4',
		),
	);

	/**
	 * Get visitor type.
	 *
	 * @since 1.0.0
	 *
	 * @return string The type of visitor.
	 */
	public function get_visitor_type() {
		// Check for ping services.
		$maybe_pingbot = $this->check_for_pingbots();

		// Return the service name if it exists.
		if ( false !== $maybe_pingbot ) {
			return $maybe_pingbot;
		}

		// If no user-agent set as unknown.
		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return __( 'Unknown', 'sg-security' );
		}

		// Check for a bot/crawler.
		$maybe_crawler = $this->check_for_crawlers();

		// Return the bot name.
		if ( false !== $maybe_crawler ) {
			return $maybe_crawler;
		}

		return __( 'Human', 'sg-security' );
	}

	/**
	 * Check if the visitor is a known service
	 *
	 * @since  1.1.0
	 *
	 * @return string|bool The service name, or false if no match found.
	 */
	public function check_for_pingbots() {
		$services = apply_filters(
			'sg_security_custom_ping_services',
			array_merge(
				$this->ping_services, // Our custom services.
				get_option( 'sg_security_user_ping_services', array() ) // User defined services.
			)
		);

		// Get the current user ip.
		$ip = Helper::get_current_user_ip();

		// Loop all services.
		foreach ( $services as $key => $service_ip ) {
			// If we have a match, return the service name.
			if ( in_array( $ip, $service_ip ) ) {
				return $key;
			}
		}

		return false;
	}
	/**
	 * Check if the visitor is a bot by user agent.
	 *
	 * @since  1.1.0
	 *
	 * @return string|bool $match The bot name. False otherwise.
	 */
	public function check_for_crawlers() {
		$crawlers = apply_filters(
			'sg_security_custom_crawlers',
			array_merge(
				$this->crawlers, // Our custom crawlers.
				get_option( 'sg_security_user_crawlers', array() ) // User defined crawlers.
			)
		);

		// Build the regex for locating the bot user-agents.
		$regex = sprintf(
			'/(%s)/i',
			implode( '|', $crawlers )
		);

		// Check if we ahve a match and return the name of the bot.
		if ( preg_match( $regex, $_SERVER['HTTP_USER_AGENT'], $match ) ) { //phpcs:ignore
			return $match[0];
		}

		return false;
	}
	/**
	 * Log page visit.
	 *
	 * @since  1.0.0
	 */
	public function log_visit() {
		if ( defined( 'WP_CLI' ) ) {
			exit;
		}

		// Bail if request is made with jquery.
		if ( ! empty( $_SERVER['X-Requested-With'] ) ) {
			return;
		}

		// Bail if it is a request for something else than html page.
		if ( isset( $_SERVER['HTTP_ACCEPT'] ) && false === strpos( $_SERVER['HTTP_ACCEPT'], 'text/html' ) ) { // phpcs:ignore
			return;
		}

		// List of URLs which hits are excluded from the log.
		$excluded_urls = array(
			'admin-ajax.php',
			'wp-comments-post.php',
			'/?unapproved=',
		);
		// Bail if request is made trough excluded URIs.
		foreach ( $excluded_urls as $excluded ) {
			if ( isset( $_SERVER['REQUEST_URI'] ) && false !== strpos( $_SERVER['REQUEST_URI'], $excluded ) ) { // phpcs:ignore
				return;
			}
		}

		// Do not log the visit if a user is logged in.
		if ( isset( $_SERVER['HTTP_COOKIE'] ) && false !== strpos( $_SERVER['HTTP_COOKIE'], 'wordpress_logged_in_' ) ) { // phpcs:ignore
			return;
		}

		// Get the current user IP.
		$ip = Helper::get_current_user_ip();

		// Prepare the arguments for writing to db.
		$args = array(
			'ts'           => time(),
			'visitor_id'   => $this->get_visitor_by_ip( $ip ),
			'activity'     => $_SERVER['REQUEST_URI'], // phpcs:ignore
			'description'  => $_SERVER['REQUEST_URI'], // phpcs:ignore
			'ip'           => $ip,
			'code'         => http_response_code(),
			'object_id'    => 0,
			'type'         => 'unknown',
			'hostname'     => $ip,
			'action'       => 'visit',
			'visitor_type' => $this->get_visitor_type(),
		);

		// Log the visit in the db.
		$this->insert( $args );

		exit;
	}
}
