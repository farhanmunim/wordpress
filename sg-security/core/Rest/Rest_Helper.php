<?php
namespace SG_Security\Rest;

use SG_Security\Options_Service\Options_Service;
use SG_Security\Message_Service\Message_Service;

/**
 * Rest Helper class that process all rest requests and provide json output for react app.
 */
abstract class Rest_Helper {

	/**
	 * Checks if the `option_key` paramether exists in rest data.
	 *
	 * @since  1.0.0
	 *
	 * @param  object $request Request data.
	 * @param  string $key     The option key.
	 * @param  bool   $bail    Whether to send json error or to return a response.
	 *
	 * @return string          The option value.
	 */
	public function validate_and_get_option_value( $request, $key, $bail = true ) {
		$data = json_decode( $request->get_body(), true );

		// Bail if the option key is not set.
		if ( ! isset( $data[ $key ] ) ) {
			return true === $bail ? self::send_response( 'Something went wrong', 400 ) : false;
		}

		return $data[ $key ];
	}

	/**
	 * Change the option value.
	 *
	 * @since  1.0.0
	 *
	 * @param  object $key Request data.
	 * @param  string $value   The option value.
	 *
	 * @return bool            True if the option is enabled, false otherwise.
	 */
	public function change_option( $key, $value ) {
		return Options_Service::change_option( $key, $value );
	}

	/**
	 * Custom json response.
	 *
	 * @since  1.0.0
	 *
	 * @param  string  $message     The response message.
	 * @param  integer $result      The result of the optimization.
	 * @param  array   $data        Additional data to be send.
	 */
	public static function send_response( $message, $result = 1, $data = array() ) {
		// Prepare the status code, based on the optimization result.
		$status_code = 1 === $result ? 200 : 400;

		$response = \rest_ensure_response(
			array(
				'data'    => $data,
				'message' => $message,
				'status'  => $status_code,
			)
		);

		$response->set_status( $status_code );

		if ( ! headers_sent() ) {
			$response->header( 'Content-Type', 'application/json; charset=' . get_option( 'blog_charset' ) );
		}

		return $response;
	}

	/**
	 * Prepare the response message for the plugin interface.
	 *
	 * @since  1.0.0
	 *
	 * @param  int    $result The result of the optimization.
	 * @param  string $option The option name.
	 *
	 * @return string         The response message.
	 */
	public function get_response_message( $result, $option ) {
		return Message_Service::get_response_message( $result, $option );
	}

	/**
	 * Prepare dropdown options and selected values to be sent to react.
	 *
	 * @since 1.3.3
	 *
	 * @param      array $options  The options/label array.
	 * @param      bool  $value    The current value.
	 *
	 * @return     array  Data sent to the react.
	 */
	public function prepare_options_selected_values( $options, $value ) {
		// Prepare the data array.
		$data = array();

		// Generate the data array for the react app.
		foreach ( $options as $key => $label ) {
			$data[] = array(
				'value'    => $key,
				'selected' => $key === $value ? 1 : 0,
				'label'    => $label,
			);
		}

		// Return the data.
		return $data;
	}
}
