<?php
namespace SG_Security\Activity_Log;

/**
 * Activity Log Users main class
 */
class Activity_Log_Users extends Activity_Log_Helper {
	/**
	 * Log user login.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $user_login Username.
	 * @param  object $user       WP_User object.
	 */
	public function log_login( $user_login, $user ) {
		$activity = __( 'User Login', 'sg-security' );
		$this->log_event(
			array(
				'activity'    => $activity,
				'description' => $this->get_user_description( $user->ID, $activity ),
				'object_id'   => $user->ID,
				'user_id'     => $user->ID,
				'type'        => 'user',
				'action'      => 'login',
			)
		);
	}

	/**
	 * Log user logout.
	 *
	 * @since  1.0.0
	 *
	 * @param  int $id user ID.
	 */
	public function log_logout( $id ) {
		$activity = __( 'User logout', 'sg-security' );
		$this->log_event(
			array(
				'activity'    => $activity,
				'description' => $this->get_user_description( $id, $activity ),
				'object_id'   => $id,
				'user_id'     => $id,
				'type'        => 'user',
				'action'      => 'logout',
			)
		);
	}

	/**
	 * Log user delete.
	 *
	 * @since  1.0.0
	 *
	 * @param  int $id user ID.
	 */
	public function log_user_delete( $id ) {
		$activity = __( 'User Delete', 'sg-security' );
		$this->log_event(
			array(
				'activity'    => $activity,
				'description' => $this->get_user_description( $id, $activity ),
				'object_id'   => $id,
				'type'        => 'user',
				'action'      => 'delete',
			)
		);
	}

	/**
	 * Log user register.
	 *
	 * @since  1.0.0
	 *
	 * @param  int $id user ID.
	 */
	public function log_user_register( $id ) {
		$activity = __( 'User Register', 'sg-security' );
		$this->log_event(
			array(
				'activity'    => $activity,
				'description' => $this->get_user_description( $id, $activity ),
				'object_id'   => $id,
				'type'        => 'user',
				'action'      => 'register',
			)
		);
	}

	/**
	 * Log profile update.
	 *
	 * @since  1.0.0
	 *
	 * @param  int $id user ID.
	 */
	public function log_profile_update( $id ) {
		$activity = __( 'User Update', 'sg-security' );
		$this->log_event(
			array(
				'activity'    => $activity,
				'description' => $this->get_user_description( $id, $activity ),
				'object_id'   => $id,
				'type'        => 'user',
				'action'      => 'update',
			)
		);
	}

	/**
	 * Log user failed login.
	 *
	 * @since  1.0.0
	 *
	 * @param  int $id user ID.
	 */
	public function log_wrong_password( $id ) {
		$this->log_event(
			array(
				'activity'     => __( 'Failed Login Attempt', 'sg-security' ),
				'description'  => __( 'Failed Login Attempt', 'sg-security' ),
				'object_id'    => $id,
				'user_id'      => $id,
				'type'         => 'user',
				'action'       => 'login',
				'visitor_type' => 'Human',
			)
		);
	}

	/**
	 * Get user log description
	 *
	 * @since  1.0.0
	 *
	 * @param  int    $user_id  User ID.
	 * @param  string $activity Activity type.
	 *
	 * @return string           The description.
	 */
	public function get_user_description( $user_id, $activity ) {
		$user = get_user_by( 'id', $user_id );

		//  If user value is null, log just the activity.
		if ( ! isset( $user ) ) {
			return $activity;
		}

		if ( ! isset( $user->data ) ) {
			return $activity;
		}

		if ( ! isset( $user->data->user_login ) ) {
			return $activity;
		}

		return $activity . ' - ' . $user->data->user_login;
	}
}
