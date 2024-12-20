<?php
namespace SG_Security\Editors_Service;

/**
 * Editor_Service class which disables in-built theme and plugin editors.
 */
class Editors_Service {

	/**
	 * Disable the in-built theme and plugin editors for all users.
	 *
	 * @since  1.0.0
	 *
	 * @param string[] $caps    Primitive capabilities required of the user.
	 * @param string   $cap     Capability being checked.
	 */
	public function disable_file_edit( $caps, $cap ) {
		if ( in_array( $cap, array( 'edit_themes', 'edit_plugins', 'edit_files' ), true ) ) {
			return array( 'sg-security' );
		}

		return $caps;
	}
}
