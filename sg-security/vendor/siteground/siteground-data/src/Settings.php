<?php
namespace SiteGround_Data;

if ( ! class_exists( 'SiteGround_Data/Settings' ) ) {
	/**
	 * The data tracking class.
	 */
	class Settings {

		/**
		 * The unique instance of the data setting.
		 *
		 * @var Settings
		 */
		private static $instance;

		/**
		 * Gets an instance of our data setting.
		 *
		 * @return Settings
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * The Tracking API url.
		 *
		 * @var string
		 */
		const API_URL = 'https://wpreports.sgvps.net';

		/**
		 * The default tracking period in days.
		 *
		 * @var integer
		 */
		public $tracking_period = 30;

		/**
		 * The last tracking date in epoch time.
		 *
		 * @var string
		 */
		public $tracking_option = 'siteground_last_data_tracking';

		/**
		 * The date user has given their consent.
		 *
		 * @var string
		 */
		public $siteground_data_consent = 'siteground_data_consent';

		/**
		 * The options we want to track.
		 *
		 * @var array
		 */
		public $plugin_options = array(
			// SiteGround Optimizer.
			'siteground_optimizer_enable_cache',
			'siteground_optimizer_file_caching',
			'siteground_optimizer_enable_memcached',
			'siteground_optimizer_autoflush_cache',
			'siteground_optimizer_ssl_enabled',
			'siteground_optimizer_fix_insecure_content',
			'siteground_optimizer_enable_browser_caching',
			'siteground_optimizer_resize_images',
			'siteground_optimizer_lazyload_images',
			'siteground_optimizer_database_optimization',
			'siteground_optimizer_purge_rest_cache',
			'siteground_optimizer_backup_media',
			'siteground_optimizer_image_compression_level',
			'siteground_optimizer_webp_support',
			'siteground_optimizer_heartbeat_post_interval',
			'siteground_optimizer_heartbeat_dashboard_interval',
			'siteground_optimizer_heartbeat_frontend_interval',
			'siteground_optimizer_purge_rest_cache',
			'siteground_optimizer_backup_media',
			// SiteGround Security.
			'sg_security_lock_system_folders',
			'sg_security_wp_remove_version',
			'sg_security_xss_protection',
			'sg_security_disable_xml_rpc',
			'sg_security_disable_file_edit',
			'sg_security_disable_feed',
			'sg_security_xss_protection',
			'sg_security_hsts_protection',
			'sg_security_login_type',
			'sg_security_login_attempts',
			'sg_security_sg2fa',
			'sg_security_disable_usernames',
		);

		/**
		 * Handle settings page update
		 *
		 * @since  1.0.0
		 */
		public function handle_settings_update() {
			if (
				isset( $_POST['option_page'] ) && //phpcs:ignore
				'siteground_settings' === $_POST['option_page'] //phpcs:ignore
			) {
				$this->send_data();
			}
		}

		/**
		 * Send a request to the api to not send emails.
		 *
		 * @since  1.0.0
		 */
		public function stop_collecting_data() {
			delete_option( 'siteground_data_consent' );
			delete_option( 'siteground_email_consent' );
			delete_option( 'siteground_settings_optimizer' );
			delete_option( 'siteground_settings_security' );
			delete_option( 'siteground_data_store' );
			wp_clear_scheduled_hook( 'siteground_data_collector_cron' );

			$this->send_data();
		}

		/**
		 * Prepare the tracking data.
		 *
		 * @param int   $retry    Whether to retry if the first try fails.
		 * @param array $old_data Locally stored data to sent to the API when ready.
		 *
		 * @since  1.0.0
		 */
		public function send_data( $retry = 0, $old_data = array() ) {
			// Prepare the data.
			$data = ! empty( $old_data ) ? $old_data : $this->prepare_data();

			// Refresh the auth token if we retry to send the statistics.
			if ( 1 === $retry ) {
				$this->refresh_auth_token();
			}

			// Prepare the resposne.
			$response = wp_remote_post(
				self::API_URL . '/collect_plugin_data',
				array(
					'timeout' => 10,
					'headers' => array(
						'Content-Type' => 'application/json',
						'X-auth-token' => $this->get_auth_token(),
					),
					'sslverify' => false,
					'body'      => json_encode(
						array( 'data' => $data )
					),
				)
			);

			// Retry if the request fails.
			if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				return wp_schedule_single_event( strtotime( '+' . rand( 12, 24 ) . ' hours' ), 'siteground_data_collector_cron', array( 1 ) );
			}

			update_option( $this->tracking_option, time() );
			return true;

		}

		/**
		 * Check if old data have to be send to the api.
		 *
		 * @since  1.0.0
		 */
		public function check_for_old_data() {
			// Get the data from the database.
			$data = get_option( 'siteground_data_store', array() );

			// Bail if no data is found.
			if ( empty( $data ) ) {
				return;
			}

			// Send the data entry to the api.
			$this->send_data( 0, $data );

			delete_option( 'siteground_data_store' );
		}

		/**
		 * Send the data on wp login.
		 *
		 * @since  1.0.0
		 */
		public function send_data_on_login() {
			if ( false === $this->should_send_data() ) {
				return;
			}

			$this->send_data();
		}

		/**
		 * Prepare the data that will be send.
		 *
		 * @since  1.0.0
		 *
		 * @return array Array of site data.
		 */
		public function prepare_data() {
			$data = $this->get_main_data();

			// Get the plugin data.
			foreach ( $this->plugin_options as $option ) {
				$data[ $option ] = intval( get_option( $option, 0 ) );
			}

			return $data;
		}

		/**
		 * Get the last time we've sent data to the API
		 *
		 * @since  1.0.0
		 *
		 * @return intval The time stamp when we last sent data.
		 */
		public function should_send_data() {
			// Get the last track and asume it may be the first track.
			$last_track = intval( get_option( $this->tracking_option, 0 ) );

			// Send the data if there is no record for the last track.
			if ( empty( $last_track ) ) {
				return true;
			}

			// Send the data if the current timestamp is greater than the tracking period.
			if ( time() >= ( $last_track + $this->tracking_period * 86400 ) ) {
				return true;
			}

			// Bail if the next tracking is in the future.
			return false;
		}

		/**
		 * Get WP Core and server related data.
		 *
		 * @since  1.0.0
		 *
		 * @return array $data The server and core related information we need.
		 */
		public function get_main_data() {
			global $wpdb;

			// Get the theme data.
			$theme_data = wp_get_theme();
			// Get the user data.
			$users_data = count_users();

			// Prepare the server and core data.
			$data = array(
				'url'                     => home_url(),
				'email_consent'           => intval( get_option( 'siteground_email_consent', 0 ) ),
				'data_consent'            => intval( get_option( 'siteground_email_consent', 0 ) ),
				'email_consent_timestamp' => get_option( 'siteground_email_consent_timestamp', 0 ),
				'data_consent_timestamp'  => get_option( 'siteground_data_consent_timestamp', 0 ),
				'admin_email'             => get_option( 'admin_email' ),
				'php_version'             => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
				'php_max_exec_time'       => ini_get( 'max_execution_time' ),
				'admin_url'               => get_admin_url(),
				'php_upload_max_filesize' => ini_get( 'upload_max_filesize' ),
				'php_memory_limit'        => ini_get( 'memory_limit' ),
				'server'                  => strtolower( PHP_OS ),
				'image_library'           => $this->check_image_libraries(),
				'wp_version'              => get_bloginfo( 'version' ),
				'wp_memory_limit'         => WP_MEMORY_LIMIT,
				'wp_max_upload'           => round( wp_max_upload_size() / 1024 / 1024, 4 ),
				'wp_user_count'           => $users_data['total_users'],
				'timezone'                => get_option( 'timezone_string', '' ),
				'mysql_version'           => $wpdb->db_version(),
				'server_version'          => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '',
				'is_ssl'                  => is_ssl(),
				'is_multisite'            => is_multisite(),
				'site_count'              => function_exists( 'get_blog_count' ) ? (int) get_blog_count() : 1,
				'theme_name'              => $theme_data->name,
				'theme_version'           => $theme_data->version,
				'locale'                  => get_locale(),
				'has_woocommerce'         => in_array( trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php', wp_get_active_and_valid_plugins() ),
				'hosting_provider'        => $this->get_hosting_provider(),
			);

			// Add the plugins data and return everything.
			return array_merge( $data, $this->get_plugins() );
		}

		/**
		 * Get the current hosting provider.
		 *
		 * @since  1.0.0
		 *
		 * @return string The hosting provider.
		 */
		public function get_hosting_provider() {

			if ( self::is_siteground() ) {
				return 'SiteGround';
			}

			if ( class_exists( 'WPaaS\Plugin' ) ) {
				return 'GoDaddy';
			}

			if (
				class_exists( '\Presslabs\Cache\CacheHandler' ) &&
				defined( 'PL_INSTANCE_REF' )
			) {
				return 'Presslabs';
			}

			$provider = 'Unknown';
			// A list of hosting provider headers.
			// See more: https://github.com/rviscomi/ismyhostfastyet/blob/main/ttfb.sql
			$host_headers = array(
				'zoneos'                           => 'Zone.eu',
				'seravo'                           => 'Seravo',
				'wordpress.com'                    => 'Automattic',
				'x-ah-environment'                 => 'Acquia',
				'x-pantheon-styx-hostname'         => 'Pantheon',
				'wpe-backend'                      => 'WP Engine',
				'wp engine'                        => 'WP Engine',
				'x-kinsta-cache'                   => 'Kinsta',
				'x-github-request'                 => 'GitHub',
				'alproxy'                          => 'AlwaysData',
				'flywheel'                         => 'Flywheel',
				'c2hhcmVkLmJsdWVob3N0LmNvbQ=='     => 'Bluehost',
			);

			// Make a request to the homepage.
			$response = wp_remote_get( get_home_url() );

			// Get the host header from the response.
			$host_header = wp_remote_retrieve_header( $response, 'X-Powered-By' );

			// Bail if the X-Powered-By header doesn't exist.
			if ( empty( $host_header ) ) {
				return $provider;
			}

			return array_key_exists( $host_header, $host_headers );
		}

		/**
		 * Check for image libraries.
		 *
		 * @since  1.0.0
		 *
		 * @return array $libraries The loaded image libraries.
		 */
		public function check_image_libraries() {
			// Images Libraries we are checking.
			$libraries = array(
				'gd',
				'imagick',
			);

			// Loop trough the libraries list.
			foreach ( $libraries as $key => $library ) {

				// Check if the extension is loaded.
				if ( ! extension_loaded( $library ) ) {
					unset( $libraries[ $key ] );
				}
			}

			return $libraries;
		}

		/**
		 * Get the name and version of all installed plugins
		 *
		 * @since  1.0.0
		 *
		 * @return array $plugins Array containing all installed plugins
		 */
		public function get_plugins() {
			// Check if we need to require the Class.
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}

			$all_plugins = \get_plugins();

			$active_plugins = get_option( 'active_plugins', array() );

			// Assemble array of name, version, and whether plugin is active (boolean).
			foreach ( $all_plugins as $key => $value ) {
				$plugins['installed_plugins'][] = $value['Name'] . ' ' . $value['Version'];

				if ( in_array( $key, $active_plugins ) ) {
					$plugins['active_plugins'][] = $value['Name'] . ' ' . $value['Version'];
				}
			}

			foreach ( $plugins as $type => $plugin_data ) {
				$plugins[ $type ] = implode( ', ', $plugin_data );
			}

			return $plugins;
		}

		/**
		 * Get the auth token.
		 *
		 * @since  1.0.0
		 *
		 * @return string The auth token.
		 */
		public function get_auth_token() {
			// Check for token in database.
			$token = get_option( 'siteground_data_token', false );

			// Return the token if exists in database.
			if ( ! empty( $token ) ) {
				return $token;
			}

			// Get and return a new token from the api.
			return $this->refresh_auth_token();
		}

		/**
		 * Make a call to the statistic api to get a new auth token.
		 *
		 * @since  1.0.0
		 *
		 * @return mixed False on failure, the auth token on success.
		 */
		public function refresh_auth_token() {
			// Get a new token from the api.
			$response = wp_remote_post(
				self::API_URL . '/auth/get_authenticated_response',
				array(
					'timeout' => 10,
					'body' => json_encode(
						array( 'url' => home_url() )
					),
					'sslverify' => false,
				)
			);

			// Bail if the request fails.
			if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				return false;
			}

			// Get the body of the response.
			$body = wp_remote_retrieve_body( $response );

			// Decode the body of the response.
			$decoded_body = json_decode( $body, true );

			// Bail if no token is provided.
			if ( empty( $decoded_body['token'] ) ) {
				return false;
			}

			// Store the token in the database.
			update_option( 'siteground_data_token', $decoded_body['token'] );

			return $decoded_body['token'];
		}

		/**
		 * Schedule a cron job to collect the data.
		 *
		 * @since  1.0.0
		 */
		public function schedule_cron_job() {
			if ( ! wp_next_scheduled( 'siteground_data_collector_cron' ) ) {
				wp_schedule_event( time(), 'siteground_monthly', 'siteground_data_collector_cron' );
			}
		}

		/**
		 * Add a custom cron interval.
		 *
		 * @since 1.0.0
		 *
		 * @param array $schedules The cron schedules with our custom interval.
		 */
		public function add_siteground_data_interval( $schedules ) {
			$schedules['siteground_monthly'] = array(
				'interval' => 2635200,
				'display' => __( 'Once a month' ),
			);

			return $schedules;
		}

		/**
		 * Checks if the plugin run on the new SiteGround interface.
		 *
		 * @since  1.0.0
		 *
		 * @return boolean True/False.
		 */
		public static function is_siteground() {
			// Bail if open_basedir restrictions are set, and we are not able to check certain directories.
			if ( ! empty( ini_get( 'open_basedir' ) ) ) {
				return 0;
			}

			return (int) ( @file_exists( '/etc/yum.repos.d/baseos.repo' ) && @file_exists( '/Z' ) );
		}
	}
}
