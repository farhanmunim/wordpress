<?php
namespace SG_Security\Plugins_Service;

if ( ! class_exists( '\Plugin_Upgrader' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
}

/**
 * Plugins_Service class that handles plugins update and install.
 */
class Plugins_Service {

	/**
	 * Plugins that shouldn't be reinstalled.
	 *
	 * @var array
	 */
	public $do_not_reinstall = array(
		'sg-security',
	);

	/**
	 * Enable maintenance mode.
	 *
	 * @since  1.0.0
	 */
	public function maintanance_mode() {
		wp_die(
			esc_html__( 'Website under planned maintenance. Please check back later.', 'sg-security' ),
			esc_html__( 'Under Maintenance', 'sg-security' ),
			array(
				'sgs_error' => true,
				'response'  => 403,
			)
		);
	}

	/**
	 * Reinstall the free plugins.
	 *
	 * @since  1.0.0
	 */
	public function reinstall_plugins() {
		$plugins = $this->get_plugins_data();

		foreach ( $plugins as $plugin ) {
			$this->resintall_plugin( $plugin );
		}

		return 1;
	}

	/**
	 * Get the plugins basic info.
	 *
	 * @since  1.0.0
	 *
	 * @return array The installed plugins info.
	 */
	public function get_plugins_data() {
		// Check if we need to require the Class.
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		$plugins = array();
		// Get the plugins data.
		$installed_plugins = \get_plugins();

		// Get only what we need as information.
		foreach ( $installed_plugins as $plugin => $key ) {
			// Default values for the plugin extra attributes.
			$delimiter = strpos( $plugin, '/' ) ? '/' : '.';
			$parts     = explode( $delimiter, $plugin, 2 );
			$slug      = $parts[0];

			// Bail if the plugin should not be reinstalled.
			if ( in_array( $slug, $this->do_not_reinstall ) ) {
				continue;
			}

			// Add extra attributes so we make things less complicated.
			$plugins[] = array(
				'path'      => $plugin,
				'name'      => $key['Name'],
				'version'   => $key['Version'],
				'is_active' => (int) is_plugin_active( $plugin ),
				'slug'      => $slug,
			);
		}

		return $plugins;
	}

	/**
	 * Start the plugin installation process.
	 *
	 * @since  1.0.0.
	 *
	 * @param  array $plugin The array containing the data needed for a successful reinstall.
	 *
	 * @return bool/array true/false or array containing the error data.
	 */
	public function resintall_plugin( $plugin ) {
		// Bail if plugin data is empty.
		if ( empty( $plugin ) ) {
			return false;
		}

		// Build the download URL.
		$package = sprintf(
			'https://downloads.wordpress.org/plugin/%s.%s.zip',
			$plugin['slug'],
			$plugin['version']
		);

		$headers = get_headers( $package, true );

		// Bail if the fetch fails.
		if ( empty( $headers ) ) {
			return;
		}

		// Bail if response is not 200.
		if (
			! empty( $headers ) &&
			'HTTP/1.1 200 OK' !== $headers[0]
		) {
			return;
		}

		// Deactivate the plugin if is active.
		if ( $plugin['is_active'] ) {
			deactivate_plugins( $plugin['path'] );
		}

		try {
			if ( true !== delete_plugins( array( $plugin['path'] ) ) ) {
				return false;
			};
		} catch ( \Error $e ) {
				return;
		}

		// Prepare the necessary dependencies.
		$skin     = new \WP_Ajax_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $package );

		// Activate the plugin if it was previously activated.
		if ( 1 === $plugin['is_active'] ) {
			activate_plugin( $plugin['path'], '', false, true );
		}

		// Refresh plugin update information.
		wp_clean_plugins_cache();

		return $result;

	}
}
