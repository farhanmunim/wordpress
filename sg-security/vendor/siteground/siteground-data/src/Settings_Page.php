<?php
namespace SiteGround_Data;

use SiteGround_Data\Settings;

if ( ! class_exists( 'SiteGround_Data/Settings_Page' ) ) {
	/**
	 * Plugin Settings main class
	 */
	class Settings_Page {

		const REST_NAMESPACE = 'siteground-settings/v1';

		/**
		 * The data collector cron.
		 *
		 * @var string
		 */
		public $data_cron = 'siteground_data_collector_cron';

		/**
		 * The settings classes and their hooks and options.
		 *
		 * @var array
		 */
		public $settings = array(
			'siteground_data_consent',
			'siteground_email_consent',
			'siteground_settings_optimizer',
			'siteground_settings_security',
		);

		/**
		 * The options that could be changed in the settings page.
		 *
		 * @var array
		 */
		public $allowed_options = array(
			'siteground_data_consent',
			'siteground_email_consent',
		);

		/**
		 * The constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			foreach ( $this->settings as $option ) {
				// Record timestamp of when the option was created.
				add_action( "add_option_$option", array( $this, 'add_option_change_timestamp' ), 10, 3 );
				// Record timestamp of when the option was updated.
				add_action( "update_option_$option", array( $this, 'update_option_change_timestamp' ), 10, 3 );
			}

			$settings = ! method_exists( 'Siteground_Data\\Settings', 'get_instance' ) ? new Settings() : Settings::get_instance();
			// Add the data interval, since the hook from the plugin is not available here.
			add_action( 'cron_schedules', array( $settings, 'add_siteground_data_interval' ) );
		}

		/**
		 * Set a flag when the option has been created.
		 *
		 * @since 1.0.0
		 *
		 * @param string $option The option name.
		 * @param string $value  The option value.
		*/
		public function add_option_change_timestamp( $option, $value ) {
			update_option( $option . '_timestamp', time() );
		}

		/**
		 * Set a flag when the option has been updated.
		 *
		 * @since  1.0.0
		 *
		 * @param  mixed  $old    The old value of the option.
		 * @param  mixed  $new    The new value of the option.
		 * @param  string $option The option name.
		 */
		public function update_option_change_timestamp( $old, $new, $option ) {
			update_option( $option . '_timestamp', time() );

			// Setup cron if data consent is enabled.
			if ( 'siteground_data_consent' === $option  ) {
				$this->manage_cronjobs( intval( $new ) );
			}
		}

		/**
		 * Modify the cronjob based on user consent selection
		 *
		 * @param  int $value The user selected option.
		 */
		public function manage_cronjobs( $value ) {
			if ( 0 === $value ) {
				wp_unschedule_event( wp_next_scheduled( $this->data_cron ), $this->data_cron );
				return;
			}

			if ( ! wp_next_scheduled( $this->data_cron ) ) {
				wp_schedule_event( time(), 'siteground_monthly', $this->data_cron );
			}
		}

		/**
		 * Add the settings to allowed options.
		 *
		 * @since  1.0.0
		 *
		 * @param  array $options Array of allowed options.
		 *
		 * @return array          Array for allowed options.
		 */
		public function change_allowed_options( $options ) {
			$options[ self::get_page_name() ] = $this->allowed_options;
			return $options;
		}

		/**
		 * Add the setting fields.
		 *
		 * @since 1.0.0
		 */
		public function add_setting_fields() {

			$settings = array(
				'siteground_data_consent' => array(
					'field' => 'siteground_data_consent',
					'title' => __( 'Manage consent', 'siteground_settings' ),
					'description' => __( 'Collect technical data about my installation. The data will be used for technical analysis, plugin compatibility improvements and rarely for contacting the site admin in case of critical security releases or other issues related with the plugin proper functioning. We do not recommend opting out, as it may negatively impact the plugin performance. (A full list of the data to be collected can be found <a href="https://www.siteground.com/kb/what-information-wp-plugins-collect">here</a>)', 'siteground_settings' ),
				),
			);

			if (
				! empty( ini_get( 'open_basedir' ) ) ||
				! ( @file_exists( '/etc/yum.repos.d/baseos.repo' ) && @file_exists( '/Z' ) )
			) {
				$settings['siteground_email_consent'] = array(
					'field'       => 'siteground_email_consent',
					'title'       => '',
					'description' => __( 'Send occasional emails with tips on improving my site performance, information about new plugin features and exclusive offers by SiteGround. You can always opt-out from these.', 'siteground_settings' ),
				);
			}

			foreach ( $settings as $key => $args ) {
				add_settings_field(
					$key,
					$args['title'],
					array( $this, 'render_field' ),
					self::get_page_name(),
					self::get_page_name(),
					$args
				);
			}
		}



		/**
		 * Register the settings page.
		 *
		 * @since  1.0.0
		 */
		public function register_settings_page() {
			global $submenu;

			if (
				! isset( $submenu['options-general.php'] ) ||
				! is_array( $submenu['options-general.php'] )
			) {
				return;
			}

			$page_exists = array_search(
				'siteground_settings',
				array_column( $submenu['options-general.php'], '2' )
			);

			if ( $page_exists ) {
				return;
			}

			add_options_page(
				__( 'SiteGround Plugins Settings', 'siteground_settings' ),
				__( 'SG Plugins', 'siteground_settings' ),
				'manage_options',
				self::get_page_name(),
				array( $this, 'render_settings_page' )
			);

			add_settings_section(
				self::get_page_name(),
				'',
				'',
				self::get_page_name()
			);
		}

		/**
		 * Render the settings page.
		 *
		 * @since  1.0.0
		 */
		public function render_settings_page() {
			?>
			<div class="wrap">
				<h2><?php esc_html_e( 'SiteGround Plugins Settings', 'siteground_settings' ); ?></h2>
				<form method="POST" action="options.php">
					<?php
					settings_fields( self::get_page_name() );
					do_settings_sections( self::get_page_name() );
					submit_button();
					?>
				</form>
			</div>
			<?php
		}

		/**
		 * Render the setting field.
		 *
		 * @since  1.0.0
		 *
		 * @param  array $args Fiedl args.
		 */
		public function render_field( $args ) {
			$checked = checked( 1, get_option( $args['field'], 0 ), false );
			?>
			<input
				name="<?php echo esc_attr( $args['field'] ); ?>"
				id="<?php echo esc_attr( $args['field'] ); ?>"
				type="checkbox"
				value="1"
				<?php echo esc_html( $checked ); ?>
			/>
			<label for="<?php echo esc_attr( $args['field'] ); ?>"><?php echo $args['description']; ?></label>
			<?php
		}

		/**
		 * Name of the settings page.
		 *
		 * @since  1.0.0
		 *
		 * @return string $name The name of the options page.
		 */
		public static function get_page_name() {
			return 'siteground_settings';
		}

		/**
		 * Register the rest route for the settings endpoint.
		 *
		 * @since  1.0.0
		 */
		public function register_rest_routes() {
			register_rest_route(
				self::REST_NAMESPACE, '/update-settings/', array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => function() {
						return current_user_can( 'manage_options' );
					},
				)
			);
		}

		/**
		 * Update the settings
		 *
		 * @since  1.0.0
		 *
		 * @param  object $request Request data.
		 */
		public function update_settings( $request ) {
			$options = json_decode( $request->get_body(), true );

			foreach ( $options as $option => $value ) {
				if ( ! in_array( $option, $this->settings ) ) {
					continue;
				}

				update_option( $option, $value );

				$response[ $option ] = $value;
			}

			wp_schedule_single_event( time(), $this->data_cron );

			if ( ! headers_sent() ) {
				header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

				status_header( 200 );
			}

			echo wp_json_encode( array(
				'status' => 200,
				'data'   => $response,
			) );

			exit;
		}
	}
}