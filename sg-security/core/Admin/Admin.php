<?php
namespace SG_Security\Admin;

use SG_Security;
use SiteGround_Helper\Helper_Service;
use SiteGround_i18n\i18n_Service;
use SG_Security\SG_2fa\Sg_2fa;

/**
 * Handle all hooks for our custom admin page.
 */
class Admin {

	/**
	 * Subpages array.
	 *
	 * @var array
	 */
	public $subpages = array(
		'site-security'     => 'Site Security',
		'login-settings'    => 'Login Security',
		'activity-log'      => 'Activity Log',
		'post-hack-actions' => 'Post-hack Actions',
	);

	/**
	 * Styles to be dequeued.
	 *
	 * @var array
	 */
	public $dequeued_styles = array(
		'auxin-front-icon', // Phlox Theme.
		'mks_shortcodes_simple_line_icons', // Meks Flexible Shortcodes.
		'onthego-admin-styles', // Toolset Types
		'foogra-icons', // Foogra Theme
	);

	/**
	 * Get the subpages id.
	 *
	 * @since  1.0.0
	 *
	 * @return array The subpages id's array.
	 */
	public function get_plugin_page_ids() {
		$subpage_ids = array(
			'toplevel_page_sg-security',
			'toplevel_page_sg-security-network',
		);

		foreach ( $this->subpages as $id => $title ) {
			$subpage_ids[] = 'security-optimizer_page_' . $id . '';
			$subpage_ids[] = 'security-optimizer_page_' . $id . '-network';
		}

		return $subpage_ids;
	}

	/**
	 * Hide all errors and notices on our custom dashboard.
	 *
	 * @since  1.0.0
	 */
	public function hide_errors_and_notices() {
		$sg_2fa = new SG_2fa();

		// Hide all error in our page.
		if (
			isset( $_GET['page'] ) && // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'sg-security' === $_GET['page'] // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		) {
			remove_all_actions( 'network_admin_notices' );
			remove_all_actions( 'user_admin_notices' );
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );

			error_reporting( 0 );

			// Add 2FA notice action.
			add_action( 'admin_notices', array( $sg_2fa, 'show_notices' ) );
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		// Bail if we are on different page.
		if ( false === $this->is_plugin_page() ) {
			return;
		}

		wp_enqueue_style(
			'sg-security-admin',
			\SG_Security\URL . '/assets/css/main.min.css',
			array(),
			\SG_Security\VERSION,
			'all'
		);

		// Dequeue conflicting styles.
		foreach ( $this->dequeued_styles as $style ) {
			wp_dequeue_style( $style );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		// Enqueue the sg-security script.
		wp_enqueue_script(
			'sg-security',
			\SG_Security\URL . '/assets/js/admin.js',
			array( 'jquery' ), // Dependencies.
			\SG_Security\VERSION,
			true
		);

		// Bail if we are on different page.
		if ( false === $this->is_plugin_page() ) {
			return;
		}

		// Enqueue the sg-security script.
		wp_enqueue_script(
			'sg-security-admin',
			\SG_Security\URL . '/assets/js/main.min.js',
			array( 'jquery' ), // Dependencies.
			\SG_Security\VERSION,
			true
		);
	}

	/**
	 * Check if this is the SG Security page.
	 *
	 * @since  1.0.0
	 *
	 * @return bool True/False
	 */
	public function is_plugin_page() {
		// Bail if the page is not an admin screen.
		if ( ! is_admin() ) {
			return false;
		}

		$current_screen = \get_current_screen();

		if ( in_array( $current_screen->id, $this->get_plugin_page_ids() ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Register the top level page into the WordPress admin menu.
	 *
	 * @since 1.0.0
	 */
	public function add_plugin_pages() {
		$page = \add_menu_page(
			__( 'Security Optimizer by SiteGround', 'sg-security' ), // Page title.
			__( 'Security Optimizer', 'sg-security' ), // Menu item title.
			'manage_options',
			\SG_Security\PLUGIN_SLUG,   // Page slug.
			array( $this, 'render' ),
			\SG_Security\URL . '/assets/images/icon.svg'
		);

		foreach ( $this->subpages as $id => $title ) {
			add_submenu_page(
				\SG_Security\PLUGIN_SLUG,   // Parent slug.
				'Security Optimizer - ' . $title,
				$title,
				'manage_options',
				$id,
				array( $this, 'render' )
			);
		}
	}

	/**
	 * Add styles to WordPress admin head.
	 *
	 * @since  1.0.0
	 */
	public function admin_print_styles() {
		echo '<style>.toplevel_page_sg-security.menu-top .wp-menu-image img { width:20px; margin: auto; } </style>';

		// Bail if we are on different page.
		if ( ! $this->is_plugin_page() ) {
			return;
		}

		$current_screen = \get_current_screen();
		echo '<style>.notice { display:none!important; } </style>';

		$id = strtoupper( str_replace(
			array(
				'sg-security_page_',
				'security-optimizer_page_',
				'_network',
				'-',
			),
			array(
				'',
				'',
				'',
				'_',
			),
			$current_screen->id
		));

		if ( 'TOPLEVEL_PAGE_SG_SECURITY' === $id ) {
			$id = 'SECURITY_DASHBOARD';
		}

		$i18n_service = new i18n_Service( 'sg-security' );

		$data = array(
			'rest_base'          => untrailingslashit( get_rest_url( null, '/' ) ),
			'home_url'           => Helper_Service::get_site_url(),
			'update_timestamp'   => get_option( 'sg_security_update_timestamp', 0 ),
			'localeSlug'         => join( '-', explode( '_', \get_user_locale() ) ),
			'locale'             => $i18n_service->get_i18n_data_json(),
			'wp_nonce'           => wp_create_nonce( 'wp_rest' ),
			'log_page_url'       => admin_url( 'admin.php?page=activity-log' ),
			'data_consent_popup' => $this->get_popup_settings(),
			'assets_path'         => SG_Security\URL . '/assets',
		);

		echo '<script>window.addEventListener("load", function(){ SGSecurity.init({page: SGSecurity.PAGE.' . esc_html( $id ) . ',config:' . wp_json_encode( $data ) . '})});</script>';
		echo '<style>.toplevel_page_sg-security.menu-top .wp-menu-image img { width:20px; } </style>';
	}

	/**
	 * Display the admin page.
	 *
	 * @since 1.0.0
	 */
	public function render() {
		echo '<div id="sg-security-container"></div>';
	}

	/**
	 * Reorder the submenu pages.
	 *
	 * @since  1.0.0
	 *
	 * @param   array $menu_order The WP menu order.
	 */
	public function reorder_submenu_pages( $menu_order ) {
		// Load the global submenu.
		global $submenu;
		if ( empty( $submenu['sg-security'] ) ) {
			return $menu_order;
		}

		$submenu['sg-security'][0][0] = __( 'Dashboard', 'sg-security' );
		$submenu['sg-security'][1][0] = __( 'Site Security', 'sg-security' );
		$submenu['sg-security'][2][0] = __( 'Login Security', 'sg-security' );
		$submenu['sg-security'][3][0] = __( 'Activity Log', 'sg-security' );
		$submenu['sg-security'][4][0] = __( 'Post-hack Actions', 'sg-security' );

		return $menu_order;
	}

	/**
	 * Get the popup configuration.
	 *
	 * @since  1.2.0
	 *
	 * @return array The popup settings.
	 */
	public function get_popup_settings() {
		$settings = array();

		$data_consent       = intval( get_option( 'siteground_data_consent', 0 ) );
		$email_consent      = intval( get_option( 'siteground_email_consent', 0 ) );
		$settings_security  = intval( get_option( 'siteground_settings_security', 0 ) );


		if ( ! empty( $settings_security ) ) {
			return array(
				'show_data_field'  => 0,
				'show_email_field' => 0,
			);
		}

		if ( Helper_Service::is_siteground() ) {
			if ( 1 === $data_consent ) {
				return array(
					'show_data_field'  => 0,
					'show_email_field' => 0,
				);
			}

			return array(
				'show_data_field'  => 1,
				'show_email_field' => 0,
			);
		}

		$settings = array();

		$settings['show_data_field'] = 0 === $data_consent ? 1 : 0;
		$settings['show_email_field'] = 0 === $email_consent ? 1 : 0;

		return $settings;
	}

	public function show_privacy_policy ( $text ) {
		if ( false === $this->is_plugin_page() ) {
			return $text;
		}
		return __( 'By installing and using this plugin you acknowledge that you have read and understood <a href="//siteground.com/viewtos/siteground_plugins_privacy_notice"> SiteGround Plugins Privacy Notice </a>.', 'sg-security' );
	}

}
