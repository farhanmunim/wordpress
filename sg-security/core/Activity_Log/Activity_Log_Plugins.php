<?php
namespace SG_Security\Activity_Log;

/**
 * Activity Log Plugins main class
 */
class Activity_Log_Plugins extends Activity_Log_Helper {

	/**
	 * Log plugin activate.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $plugin Path to the plugin file relative to the plugins directory.
	 */
	public function log_plugin_activate( $plugin ) {
		$activity = __( 'Activated Plugin', 'sg-security' );
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_plugin_log_description( $this->get_plugin_name( $plugin ), $activity ),
			'object_id'   => 0,
			'type'        => 'plugin',
			'action'      => 'activate',
		) );
	}

	/**
	 * Log plugin deactivate.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $plugin Path to the plugin file relative to the plugins directory.
	 */
	public function log_plugin_deactivate( $plugin ) {
		$activity = __( 'Deactivated Plugin', 'sg-security' );
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_plugin_log_description( $this->get_plugin_name( $plugin ), $activity ),
			'object_id'   => 0,
			'type'        => 'plugin',
			'action'      => 'deactivate',
		) );
	}

	/**
	 * Log plugin file edit.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $old_value Array of last edited files.
	 * @param  array $new_value Array of last edited files including the last file.
	 */
	public function log_plugin_edit( $old_value, $new_value ) {
		// Bail if the updated file is not a plugin file.
		if ( false === strpos( $new_value[0], WP_PLUGIN_DIR ) ) {
			return;
		}

		$activity = __( 'Edited Plugin', 'sg-security' );
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_plugin_log_description( $this->get_plugin_name( $new_value[0] ), $activity ),
			'object_id'   => 0,
			'type'        => 'plugin',
			'action'      => 'edit',
		) );
	}

	/**
	 * Log plugin install
	 *
	 * @since  1.0.0
	 *
	 * @param  obejct $upgrader WP_Upgrader object.
	 * @param  array  $options  Array of extra options.
	 */
	public function log_plugin_install( $upgrader, $options ) {
		// Bail if the type of the installtion is not a plugin.
		if (
			! isset( $options['type'] ) ||
			'plugin' !== $options['type']
		) {
			return;
		}

		// Bail if the action is not an install.
		if ( 'install' !== $options['action'] ) {
			return;
		}

		$activity = __( 'Installed Plugin', 'sg-security' );
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_plugin_log_description( $upgrader->new_plugin_data['Name'], $activity ),
			'object_id'   => 0,
			'type'        => 'plugin',
			'action'      => 'install',
		) );
	}

	/**
	 * Log plugin update
	 *
	 * @since  1.0.0
	 *
	 * @param  obejct $upgrader WP_Upgrader object.
	 * @param  array  $options  Array of extra options.
	 */
	public function log_plugin_update( $upgrader, $options ) {
		// Bail if the type of the update is not a plugin.
		if (
			! isset( $options['type'] ) ||
			'plugin' !== $options['type']
		) {
			return;
		}

		// Bail if the action is not an update.
		if ( 'update' !== $options['action'] ) {
			return;
		}

		$activity = __( 'Updated Plugin', 'sg-security' );

		if ( ! empty( $options['plugin'] ) ) {
			$this->log_event( array(
				'activity'    => $activity,
				'description' => $this->get_plugin_log_description( $this->get_plugin_name( $options['plugin'] ), $activity ),
				'object_id'   => 0,
				'type'        => 'plugin',
				'action'      => 'update',
			) );

			return;
		}

		foreach ( $options['plugins'] as $path ) {
			$this->log_event( array(
				'activity'    => $activity,
				'description' => $this->get_plugin_log_description( $this->get_plugin_name( $path ), $activity ),
				'object_id'   => 0,
				'type'        => 'plugin',
				'action'      => 'update',
			) );
		}
	}

	/**
	 * Get the plugin name from the plugin path.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $plugin The plugin path.
	 *
	 * @return string         The plugin name.
	 */
	public function get_plugin_name( $plugin ) {
		// Add the plugin path.
		if ( false === strpos( $plugin, WP_PLUGIN_DIR ) ) {
			$plugin = WP_PLUGIN_DIR . '/' . $plugin;
		}

		$plugin_data = \get_plugin_data( $plugin );

		return $plugin_data['Name'];
	}

	/**
	 * Get plugin log description
	 *
	 * @since  1.0.0
	 *
	 * @param  string $plugin   The plugin name.
	 * @param  string $activity The activity.
	 *
	 * @return string           The description.
	 */
	public function get_plugin_log_description( $plugin, $activity ) {
		return $activity . ' - ' . $plugin;
	}
}
