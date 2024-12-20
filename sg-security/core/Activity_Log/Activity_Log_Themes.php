<?php
namespace SG_Security\Activity_Log;

/**
 * Activity Log Themes main class
 */
class Activity_Log_Themes extends Activity_Log_Helper {

	/**
	 * Path to the wp-content/themes.
	 *
	 * @var string
	 */
	private $themes_dir = WP_CONTENT_DIR . '/themes/';

	/**
	 * Log theme file edit.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $old_value Array of last edited files.
	 * @param  array $new_value Array of last edited files including the last file.
	 */
	public function log_theme_edit( $old_value, $new_value ) {
		// Bail if the updated file is not a theme file.
		if ( false === strpos( $new_value[0], $this->themes_dir ) ) {
			return;
		}

		$activity   = __( 'Edited Theme', 'sg-security' );
		$theme_name = $this->get_theme_name( dirname( $new_value[0] ) );
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_theme_description( $theme_name, $activity ),
			'object_id'   => $theme_name,
			'type'        => 'theme',
			'action'      => 'edit',
		) );
	}

	/**
	 * Log theme install
	 *
	 * @since  1.0.0
	 *
	 * @param  obejct $upgrader WP_Upgrader object.
	 * @param  array  $options  Array of extra options.
	 */
	public function log_theme_install( $upgrader, $options ) {
		// Bail if the type of the installtion is not a theme.
		if (
			! isset( $options['type'] ) ||
			'theme' !== $options['type']
		) {
			return;
		}

		// Bail if the action is not an install.
		if ( 'install' !== $options['action'] ) {
			return;
		}

		$activity = __( 'Installed Theme', 'sg-security' );
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_theme_description( $upgrader->new_theme_data['Name'], $activity ),
			'object_id'   => $upgrader->new_theme_data['Name'],
			'type'        => 'theme',
			'action'      => 'install',
		) );
	}

	/**
	 * Log theme update
	 *
	 * @since  1.0.0
	 *
	 * @param  obejct $upgrader WP_Upgrader object.
	 * @param  array  $options  Array of extra options.
	 */
	public function log_theme_update( $upgrader, $options ) {
		// Bail if the type of the update is not a theme.
		if (
			! isset( $options['type'] ) ||
			'theme' !== $options['type']
		) {
			return;
		}

		// Bail if the action is not an update.
		if ( 'update' !== $options['action'] ) {
			return;
		}

		$activity = __( 'Updated Theme', 'sg-security' );

		if ( ! empty( $options['theme'] ) ) {
			$this->log_event( array(
				'activity'    => $activity,
				'description' => $this->get_theme_description( $this->get_theme_name( $options['theme'] ), $activity ),
				'object_id'   => 0,
				'type'        => 'theme',
				'action'      => 'update',
			) );

			return;
		}

		foreach ( $options['themes'] as $path ) {
			$theme_name = $this->get_theme_name( $path );
			$this->log_event( array(
				'activity'    => $activity,
				'description' => $this->get_theme_description( $theme_name, $activity ),
				'object_id'   => 0,
				'type'        => 'theme',
				'action'      => 'update',
			) );
		}
	}

	/**
	 * Log switch theme.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $new_theme New theme name.
	 */
	public function log_theme_switch( $new_theme ) {
		$activity = __( 'Switched Theme', 'sg-security' );
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_theme_description( $new_theme, $activity ),
			'object_id'   => $new_theme,
			'type'        => 'theme',
			'action'      => 'switch',
		) );
	}


	/**
	 * Log theme edit via Customizer
	 *
	 * @since  1.0.0
	 *
	 * @param  obejct $customizer WP_Customize_Manager obejct.
	 */
	public function log_theme_customizer_edit( $customizer ) {
		$activity = __( 'Edited Customizer Theme', 'sg-security' );
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_theme_description( $customizer->theme()->display( 'Name' ), $activity ),
			'object_id'   => $customizer->theme()->display( 'Name' ),
			'type'        => 'theme',
			'action'      => 'edit_customizer',
		) );
	}

	/**
	 * Log theme delete
	 *
	 * @since  1.0.0
	 */
	public function log_theme_delete() {
		// Try to get the deleted theme.
		$deleted_theme = $this->get_delete_theme();

		// Bail if we cannot find the deleted theme.
		if ( empty( $deleted_theme ) ) {
			return;
		}

		$activity   = __( 'Deleted Theme', 'sg-security' );
		$theme_name = $this->get_theme_name( $deleted_theme['args'][0] );
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_theme_description( $theme_name, $activity ),
			'object_id'   => $theme_name,
			'type'        => 'theme',
			'action'      => 'delete',
		) );
	}

	/**
	 * Get the theme name from the theme path.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $theme The theme path.
	 *
	 * @return string         The theme name.
	 */
	public function get_theme_name( $theme ) {
		$theme = str_replace( $this->themes_dir, '', $theme );

		$theme_data = \wp_get_theme( $theme );

		return $theme_data['Name'];
	}

	/**
	 * Get deleted theme.
	 *
	 * @since  1.0.0
	 *
	 * @return array Deleted theme data.
	 */
	private function get_delete_theme() {
		$backtrace    = debug_backtrace();
		$delete_theme = null;
		foreach ( $backtrace as $call ) {
			if (
				isset( $call['function'] ) &&
				'delete_theme' === $call['function']
			) {
				$delete_theme = $call;
				break;
			}
		}
		return $delete_theme;
	}

	/**
	 * Get theme log description
	 *
	 * @since  1.0.0
	 *
	 * @param  string $name     Theme name.
	 * @param  string $activity Activity type.
	 *
	 * @return string           The description.
	 */
	public function get_theme_description( $name, $activity ) {
		return $activity . ' - ' . $name;
	}
}
