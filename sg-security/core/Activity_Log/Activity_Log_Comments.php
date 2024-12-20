<?php
namespace SG_Security\Activity_Log;

/**
 * Activity Log Comments main class
 */
class Activity_Log_Comments extends Activity_Log_Helper {

	/**
	 * Log comment insert.
	 *
	 * @since  1.0.0
	 *
	 * @param  int $id The comment ID.
	 */
	public function log_comment_insert( $id ) {
		$visitor_type = ( true === is_user_logged_in() ) ? 'user' : 'Human';

		$activity = __( 'New comment', 'sg-security' );
		$this->log_event( array(
			'activity'     => $activity,
			'description'  => $this->get_comment_description( $id, $activity ),
			'object_id'    => $id,
			'type'         => 'comment',
			'action'       => 'insert',
			'visitor_type' => $visitor_type,
		) );
	}

	/**
	 * Log comment edit.
	 *
	 * @since  1.0.0
	 *
	 * @param  int $id The comment ID.
	 */
	public function log_comment_edit( $id ) {
		$activity = __( 'Edited comment', 'sg-security' );
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_comment_description( $id, $activity ),
			'object_id'   => $id,
			'type'        => 'comment',
			'action'      => 'edit',
		) );
	}

	/**
	 * Log comment trash.
	 *
	 * @since  1.0.0
	 *
	 * @param  int $id The comment ID.
	 */
	public function log_comment_trash( $id ) {
		$activity = __( 'Trashed comment', 'sg-security' );
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_comment_description( $id, $activity ),
			'object_id'   => $id,
			'type'        => 'comment',
			'action'      => 'trash',
		) );
	}

	/**
	 * Log comment untrash.
	 *
	 * @since  1.0.0
	 *
	 * @param  int $id The comment ID.
	 */
	public function log_comment_untrash( $id ) {
		$activity = __( 'Untrashed comment', 'sg-security' );
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_comment_description( $id, $activity ),
			'object_id'   => $id,
			'type'        => 'comment',
			'action'      => 'untrash',
		) );
	}

	/**
	 * Log comment spam.
	 *
	 * @since  1.0.0
	 *
	 * @param  int $id The comment ID.
	 */
	public function log_comment_spam( $id ) {
		$activity = __( 'Spammed comment', 'sg-security' );
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_comment_description( $id, $activity ),
			'object_id'   => $id,
			'type'        => 'comment',
			'action'      => 'spam',
		) );
	}

	/**
	 * Log comment unspam.
	 *
	 * @since  1.0.0
	 *
	 * @param  int $id The comment ID.
	 */
	public function log_comment_unspam( $id ) {
		$activity = __( 'Unspamed comment', 'sg-security' );
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_comment_description( $id, $activity ),
			'object_id'   => $id,
			'type'        => 'comment',
			'action'      => 'unspam',
		) );
	}

	/**
	 * Log comment delete.
	 *
	 * @since  1.0.0
	 *
	 * @param  int $id The comment ID.
	 */
	public function log_comment_delete( $id ) {
		$activity = __( 'Deleted comment', 'sg-security' );
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_comment_description( $id, $activity ),
			'object_id'   => $id,
			'type'        => 'comment',
			'action'      => 'delete',
		) );
	}

	/**
	 * Log comment status transition.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $new_status The new comment status.
	 * @param  string $old_status The old comment status.
	 * @param  object $comment    Comment object.
	 */
	public function log_comment_status_transition( $new_status, $old_status, $comment ) {
		if ( ! in_array( $new_status, array( 'unapproved', 'approved' ) ) ) {
			return;
		}

		$activity = ucwords( $new_status ) . __( ' comment', 'sg-security' );
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_comment_description( $comment->comment_ID, $activity ),
			'object_id'   => $comment->comment_ID,
			'type'        => 'comment',
			'action'      => 'transition',
		) );
	}

	/**
	 * Get comment description
	 *
	 * @since  1.0.0
	 *
	 * @param  int    $id       Comment ID.
	 * @param  string $activity The activity type.
	 *
	 * @return string           Comment log description.
	 */
	public function get_comment_description( $id, $activity ) {
		$comment = get_comment( $id, ARRAY_A );

		return $activity . ' ' . 'posted by ' . $comment['comment_author'] . ' on ' . get_the_title( $comment['comment_post_ID'] );
	}
}
