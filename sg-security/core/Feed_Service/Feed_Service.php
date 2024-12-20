<?php
namespace SG_Security\Feed_Service;

/**
 * Feed_Service class which disable the WordPress feed.
 */
class Feed_Service {

	/**
	 * Disables the WordPress feed.
	 *
	 * @since  1.0.0
	 */
	public function disable_feed() {
		wp_redirect( esc_url( home_url( '/' ) ) );
	}
}
