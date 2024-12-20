<?php
namespace SG_Security\Activity_Log;

/**
 * Activity Log Core main class
 */
class Activity_Log_Core extends Activity_Log_Helper {

	/**
	 * Log update core.
	 *
	 * @since  1.0.0
	 */
	public function log_core_update() {
		$this->log_event( array(
			'activity'    => __( 'Updated Core', 'sg-security' ),
			'description' => __( 'Updated Core', 'sg-security' ),
			'object_id'   => 0,
			'type'        => 'core',
			'action'      => 'update',
		) );
	}
}
