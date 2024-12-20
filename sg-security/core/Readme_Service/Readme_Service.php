<?php
namespace SG_Security\Readme_Service;

/**
 * Class that manages the Readme.html services.
 */
class Readme_Service {

	/**
	 * Check if the file exist in the root directory of the WP Installation
	 *
	 * @since  1.0.0
	 *
	 * @return bool true if the file exists, false otherwise.
	 */
	public function readme_exist() {
		// Check if the readme.html file exists in the root of the application.
		if ( file_exists( ABSPATH . 'readme.html' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Remove the readme.html file from the root directory of WP Installation.
	 *
	 * @since  1.0.0
	 *
	 * @return bool true if the file was removed, false otherwise.
	 */
	public function delete_readme() {
		// Check if the readme.html file exists in the root of the application.
		if ( ! $this->readme_exist() ) {
			return true;
		}

		// Check if file permissions are set accordingly.
		if ( 600 >= intval( substr( sprintf( '%o', fileperms( ABSPATH . 'readme.html' ) ), -3 ) ) ) {
			return false;
		}

		// Try to remove the file.
		if ( wp_delete_file( ABSPATH . 'readme.html' ) === false ) {
			return false;
		}

		return true;
	}
}
