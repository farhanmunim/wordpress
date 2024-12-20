<?php
namespace SG_Security\Activity_Log;

/**
 * Activity Log Export main class
 */
class Activity_Log_Export extends Activity_Log_Helper {

	/**
	 * Log update core.
	 *
	 * @since  1.0.0
	 */
	public function log_export() {
		$this->log_event( array(
			'activity'    => __( 'Exported WordPress', 'sg-security' ),
			'description' => __( 'Exported WordPress', 'sg-security' ),
			'object_id'   => 0,
			'type'        => 'wp',
			'action'      => 'export',
		) );
	}
}
