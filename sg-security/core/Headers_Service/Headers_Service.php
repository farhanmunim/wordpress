<?php
namespace SG_Security\Headers_Service;

/**
 * Headers_Service class which adds response headers to the WordPress app.
 */
class Headers_Service {

	/**
	 * The security headers array, containing the specific options and headers.
	 *
	 * @var array
	 */
	public $headers = array(
		'xss_protection'  => array(
			'X-Content-Type-Options' => 'nosniff',
			'X-XSS-Protection'       => '1; mode=block',
		),
	);

	/**
	 * The security headers that need to be added.
	 *
	 * @var array
	 */
	public $security_headers;

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->security_headers = $this->prepare_headers();
	}

	/**
	 * Set the necesery security headers.
	 *
	 * @since 1.2.1
	 *
	 * @param array $headers Associative array of headers to be sent.
	 */
	public function set_security_headers( $headers ) {
		// Bail if no headers to add.
		if ( empty( $this->security_headers ) ) {
			return $headers;
		}

		// Loop and modify the headers.
		foreach ( $this->security_headers as $header_key => $header_value ) {
			$headers[ $header_key ] = $header_value;
		}

		// Return the headers array.
		return $headers;
	}

	/**
	 * Set the necesary security headers for the rest api.
	 *
	 * @since 1.2.1
	 *
	 * @param WP_HTTP_Response $result Result to send to the client. Usually a WP_REST_Response.
	 */
	public function set_rest_security_headers( $result ) {
		// Return result if no headers to add.
		if ( empty( $this->security_headers ) ) {
			return $result;
		}

		// Add the specified headers.
		foreach ( $this->security_headers as $header_key => $header_value ) {
				$result->header( $header_key, $header_value );
		}

		// Return the result to the user.
		return $result;
	}

	/**
	 * Prepare the headers.
	 *
	 * @since  1.2.1
	 *
	 * @return array $prepared_headers The security headers we need to add.
	 */
	public function prepare_headers() {
		$headers = array();

		// Loop trough all headers.
		foreach ( $this->headers as $header_option => $security_headers  ) {

			// Check if the security optimization is enabled.
			if ( 1 !== (int) get_option( 'sg_security_' . $header_option, 0 ) ) {
				continue;
			}

			// Add the header to the array if optimization enabled.
			foreach ( $security_headers as $header_key => $header_value ) {
				$headers[ $header_key ] = $header_value;
			}
		}

		return $headers;
	}
}
