<?php
namespace SG_Security\Usernames_Service;

/**
 * Handle usernames customization.
 */
class Usernames_Service {

	/**
	 * Array containing common usernames prone to attacks.
	 *
	 * @var array
	 */
	public $common_usernames = array(
		'administrator',
		'user1',
		'admin',
		'user',
	);

	/**
	 * Add illegal usernames
	 *
	 * @since  1.0.0
	 *
	 * @param  array $usernames Default illegal usernames.
	 *
	 * @return array            Default + custom illegal usernames.
	 */
	public function get_illegal_usernames( $usernames = array() ) {
		$illegal_usernames = apply_filters(
			'sg_security_illegal_usernames',
			$usernames
		);

		return array_map(
			'strtolower',
			array_merge(
				$illegal_usernames,
				$this->common_usernames
			)
		);
	}

	/**
	 * Chnage the default admin username.
	 *
	 * @param array $new_username The new username provided by the user.
	 *
	 * @since  1.0.0
	 *
	 * @return int|false The number of rows updated, or false on error.
	 */
	public function change_common_username( $new_username ) {
		global $wpdb;

		$status = $wpdb->update( // phpcs:ignore
			$wpdb->users, // phpcs:ignore
			array( 'user_login' => $new_username['user_login'] ),
			array( 'ID' => $new_username['ID'] )
		);

		return $status;
	}

	/**
	 * Check if common usernames exist in the database.
	 *
	 * @since  1.1.0
	 *
	 * @return array The array containining the common usernames.
	 */
	public function check_for_common_usernames() {
		// Get all users for validating usernames in the React App.
		$all_users = get_users(
			array(
				'orderby' => 'user_login',
				'order'   => 'ASC',
				'fields'  => array(
					'ID',
					'user_login',
				),
			)
		);

		// Get all admins.
		$admins = get_users(
			array(
				'role'    => 'administrator',
				'orderby' => 'user_login',
				'order'   => 'ASC',
				'fields'  => array(
					'ID',
					'user_login',
				),
			)
		);

		// Check for illegal usernames.
		foreach ( $admins as $key => $admin ) {
			// Remove the user if its username is not in the illegal list.
			if ( ! in_array( strtolower( $admin->user_login ), $this->get_illegal_usernames() ) ) {
				unset( $admins[ $key ] );
			}
		}

		// Build the response array.
		$user_data = array(
			'all_users'     => $all_users,
			'admin_matches' => array_values( $admins ),
		);

		return $user_data;
	}

	/**
	 * Start the name change for common usernames.
	 *
	 * @since  1.1.0
	 *
	 * @param  array $usernames The array containing the changed usernames.
	 *
	 * @return bool|array $result Array containing the result for each username update.
	 */
	public function update_common_usernames( $usernames ) {
		// Bail if usernames array is empty.
		if ( empty( $usernames ) ) {
			return array();
		}

		// Loop the specified usernames.
		foreach ( $usernames as $key => $username ) {
			// Remove the successfull changes and return the failed only if any.
			if ( 1 === $this->change_common_username( $username ) ) {
				unset( $usernames[ $key ] );
			}
		}

		return $usernames;
	}
}
