<?php
namespace SG_Security\Wp_Version_Service;

/**
 * Wp_Version_Service class that removes the WordPress version information.
 */
class Wp_Version_Service {

	/**
	 * Remove the WP version from styles and scripts.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $src The source of the script or style.
	 *
	 * @return string $src The source of the script or style with removed WP version.
	 */
	public function remove_script_and_styles_version( $src ) {
		// Check if the script contains version.
		if ( strpos( $src, 'ver=' ) ) {
			$src = remove_query_arg( 'ver', $src );
		}
		// Return the striped source.
		return $src;
	}
}
