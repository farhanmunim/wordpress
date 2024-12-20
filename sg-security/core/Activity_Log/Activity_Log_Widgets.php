<?php
namespace SG_Security\Activity_Log;

/**
 * Activity Log Widgets main class
 */
class Activity_Log_Widgets extends Activity_Log_Helper {
	/**
	 * Log widget delete
	 *
	 * @param array     $instance The current widget instance's settings.
	 * @param array     $new_instance Array of new widget settings.
	 * @param array     $old_instance Array of old widget settings.
	 * @param WP_Widget $widget The current widget instance.
	 *
	 * @since  1.0.0
	 */
	public function log_widget_update( $instance, $new_instance, $old_instance, $widget ) {
		// Check if we are deleting a widget.
		if ( ! empty( $_POST['sidebar'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$activity = __( 'Updated Widget', 'sg-security' );
			$this->log_event(
				array(
					'activity' => $activity,
					'description' => $this->get_widget_description( $widget->id, $activity ),
					'object_id' => 0,
					'type'      => 'widget',
					'action'    => 'update',
				)
			);
		}

		return $instance;
	}

	/**
	 * Log widget delete
	 *
	 * @since  1.0.0
	 */
	public function log_widget_delete() {
		// Bail.
		if ( ! isset( $_REQUEST['delete_widget'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		// Bail.
		if (
			'post' !== strtolower( $_SERVER['REQUEST_METHOD'] ) || //phpcs:ignore
			empty( $_REQUEST['widget-id'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		) {
			return;
		}

		$activity = __( 'Deleted Widget', 'sg-security' );
		$this->log_event(
			array(
				'activity'    => $activity,
				'description' => $this->get_widget_description( $_REQUEST['widget-id'] , $activity ), // phpcs:ignore
				'object_id'   => sanitize_text_field( wp_unslash( $_REQUEST['widget-id'] ) ), // phpcs:ignore
				'type'        => 'widget',
				'action'      => 'delete',
			)
		);
	}

	/**
	 * Get widget log description
	 *
	 * @since  1.0.0
	 *
	 * @param  int    $id       Widget ID.
	 * @param  string $activity The activity.
	 *
	 * @return string           The description.
	 */
	public function get_widget_description( $id, $activity ) {
		global $wp_registered_widgets;

		if ( ! isset( $wp_registered_widgets[ $id ]['name'] ) ) {
			return $activity;
		}
		return $activity . ' - ' . $wp_registered_widgets[ $id ]['name'];
	}
}
