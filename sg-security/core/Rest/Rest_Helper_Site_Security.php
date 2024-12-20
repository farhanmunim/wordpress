<?php
namespace SG_Security\Rest;

use SG_Security\Rest\Rest_Helper_Options;
use SG_Security\Readme_Service\Readme_Service;
use SG_Security\Htaccess_Service\Directory_Service;
use SG_Security\Htaccess_Service\Headers_Service;
use SG_Security\Htaccess_Service\Xmlrpc_Service;
use SG_Security\Message_Service\Message_Service;
use SG_Security\Options_Service\Options_Service;

/**
 * Rest Helper class that manages the site security.
 */
class Rest_Helper_Site_Security extends Rest_Helper {

	/**
	 * Local variables
	 *
	 * @var mixed
	 */
	public $readme_service;
	public $rest_helper_options;
	public $directory_service;
	public $xmlrpc_service;

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->readme_service      = new Readme_Service();
		$this->rest_helper_options = new Rest_Helper_Options();
		$this->directory_service   = new Directory_Service();
		$this->xmlrpc_service      = new Xmlrpc_Service();
	}

	/**
	 * Locks system folders.
	 *
	 * @since  1.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function lock_system_folders( $request ) {
		$value = $this->validate_and_get_option_value( $request, 'lock_system_folders' );
		$this->directory_service->toggle_rules( $value );

		return $this->rest_helper_options->change_option_from_rest( $request, 'lock_system_folders' );
	}

	/**
	 * Disable the theme/plugins editor.
	 *
	 * @since  1.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function disable_editors( $request ) {
		return $this->rest_helper_options->change_option_from_rest( $request, 'disable_file_edit' );
	}

	/**
	 * WP Version Removal
	 *
	 * @since  1.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function hide_wp_version( $request ) {
		return $this->rest_helper_options->change_option_from_rest( $request, 'wp_remove_version' );
	}

	/**
	 * Disable XML-RPC
	 *
	 * @since  1.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function disable_xml_rpc( $request ) {
		$value = $this->validate_and_get_option_value( $request, 'disable_xml_rpc' );
		$result = $this->xmlrpc_service->toggle_rules( $value );

		if ( false === $result ) {
			return self::send_response(
				Message_Service::get_response_message( $result, 'disable_xml_rpc', $value ),
				$result
			);
		}

		return $this->rest_helper_options->change_option_from_rest( $request, 'disable_xml_rpc' );
	}

	/**
	 * Disable RSS and ATOM Feeds
	 *
	 * @since  1.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function disable_feeds( $request ) {
		return $this->rest_helper_options->change_option_from_rest( $request, 'disable_feed' );
	}

	/**
	 * Enable advanced XSS protection.
	 *
	 * @since  1.0.0
	 *
	 * @param  object $request Request data.
	 */
	public function xss_protection( $request ) {
		return $this->rest_helper_options->change_option_from_rest( $request, 'xss_protection' );
	}

	/**
	 * Deletes the WP readme.
	 *
	 * @since  1.0.0
	 */
	public function delete_readme( $request ) {

		// Get and validate value.
		$value = $this->validate_and_get_option_value( $request, 'delete_readme' );

		// If enabling, delete readme on request, continue if not.
		if ( 1 === intval( $value ) ) {
			$this->readme_service->delete_readme();
		}

		// Change the option in the DB, so that on the next update the hook for deleting the readme is called.
		return $this->rest_helper_options->change_option_from_rest( $request, 'delete_readme' );
	}
}
