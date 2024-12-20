<?php
namespace SG_Security\Activity_Log;

/**
 * Activity Log Posts main class
 */
class Activity_Log_Posts extends Activity_Log_Helper {

	/**
	 * Log post status transition.
	 *
	 * @param string  $new New post status.
	 * @param string  $old Old post status.
	 * @param WP_Post $post Post object.
	 *
	 * @since  1.0.0
	 */
	public function log_post_status_transition( $new, $old, $post ) {
		// Bail if it's a revision.
		if ( wp_is_post_revision( $post->ID ) ) {
			return;
		}

		// Bail for menu items.
		if ( 'nav_menu_item' === $post->post_type ) {
			return;
		}

		$post_type = ucwords( $post->post_type );

		if (
			'auto-draft' === $old &&
			( 'auto-draft' !== $new && 'inherit' !== $new )
		) {
			$activity = __( 'Created', 'sg-security' ) . ' ' . $post_type;
			$this->log_event( array(
				'activity'    => $activity,
				'description' => $this->get_post_description( $post, $activity ),
				'object_id'   => $post->ID,
				'type'        => 'post',
				'action'      => 'create',
			) );
		} elseif (
			'auto-draft' === $new ||
			( 'new' === $old && 'inherit' === $new )
		) {
			return;
		} elseif ( 'trash' === $new ) {
			// page was deleted.
			$activity = __( 'Trashed', 'sg-security' ) . ' ' . $post_type;
			$this->log_event( array(
				'activity'    => $activity,
				'description' => $this->get_post_description( $post, $activity ),
				'object_id'   => $post->ID,
				'type'        => 'post',
				'action'      => 'trash',
			) );
		} elseif ( 'trash' === $old ) {
			$activity = __( 'Restored', 'sg-security' ) . ' ' . $post_type;
			$this->log_event( array(
				'activity'    => $activity,
				'description' => $this->get_post_description( $post, $activity ),
				'object_id'   => $post->ID,
				'type'        => 'post',
				'action'      => 'restore',
			) );
		} else {
			$activity = __( 'Updated', 'sg-security' ) . ' ' . $post_type;
			$this->log_event( array(
				'activity'    => $activity,
				'description' => $this->get_post_description( $post, $activity ),
				'object_id'   => $post->ID,
				'type'        => 'post',
				'action'      => 'update',
			) );
		}
	}

	/**
	 * Log post deleted from database.
	 *
	 * @since  1.0.0
	 *
	 * @param  int    $id   The post ID.
	 * @param  object $post WP_Post object.
	 */
	public function log_post_delete( $id, $post ) {

		if (
			'autodraft' === $post->post_status ||
			'inherit' === $post->post_status ||
			'nav_menu_item' === $post->post_type
		) {
			return;
		}

		$activity = __( 'Deleted', 'sg-security' ) . ' ' . ucwords( $post->post_type );
		$this->log_event( array(
			'activity'    => $activity,
			'description' => $this->get_post_description( $post, $activity ),
			'object_id'   => $id,
			'type'        => 'post',
			'action'      => 'delete',
		) );
	}

	/**
	 * Get post log description.
	 *
	 * @since  1.0.0
	 *
	 * @param  int    $post_obj The post object.
	 * @param  string $activity The activity type.
	 *
	 * @return string           The description.
	 */
	public function get_post_description( $post_obj, $activity ) {
		return $activity . ' - ' . $post_obj->post_title;
	}
}
