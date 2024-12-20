<?php
namespace SG_Security\Activity_Log;

/**
 * Activity Log Attachments main class
 */
class Activity_Log_Attachments extends Activity_Log_Helper {

	/**
	 * Log add attachment event.
	 *
	 * @param int $id The attachment ID.
	 *
	 * @since  1.0.0
	 */
	public function log_add_attachment( $id ) {
		$activity = __( 'Added attachment', 'sg-security' );
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_attachment_activity_description( $id, $activity ),
			'object_id'   => $id,
			'type'        => 'attachment',
			'action'      => 'add',
		) );
	}

	/**
	 * Log edit attachment event.
	 *
	 * @param int $id The attachment ID.
	 *
	 * @since  1.0.0
	 */
	public function log_edit_attachment( $id ) {
		$activity = __( 'Edited attachment', 'sg-security' );
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_attachment_activity_description( $id, $activity ),
			'object_id'   => $id,
			'type'        => 'attachment',
			'action'      => 'edit',
		) );
	}

	/**
	 * Log delete attachment event.
	 *
	 * @param int $id The attachment ID.
	 *
	 * @since  1.0.0
	 */
	public function log_delete_attachment( $id ) {
		$activity = __( 'Deleted attachment', 'sg-security' );
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_attachment_activity_description( $id, $activity ),
			'object_id'   => $id,
			'type'        => 'attachment',
			'action'      => 'delete',
		) );
	}


	/**
	 * Get the activity description for posts.
	 *
	 * @since  1.0.0
	 *
	 * @param  int    $id       Post ID.
	 * @param  string $activity The activity.
	 *
	 * @return string  The description.
	 */
	public function get_attachment_activity_description( $id, $activity ) {
		if ( empty( $id ) ) {
			return __( 'Unknown', 'sg-security' );
		}

		$post = get_post( $id );

		if ( empty( $post ) ) {
			return __( 'Unknown', 'sg-security' );
		}

		return $activity . ' - ' . $post->post_title;
	}
}
