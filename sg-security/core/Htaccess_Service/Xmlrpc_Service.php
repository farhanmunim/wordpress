<?php
namespace SG_Security\Htaccess_Service;

/**
 * Class managing the xml-rpc related htaccess rules.
 */
class Xmlrpc_Service extends Abstract_Htaccess_Service {

	/**
	 * Array containing all plugins using XML-RPC.
	 *
	 * @since 1.0.0
	 *
	 * @var array All known plugins using XML-RPC.
	 */
	private $xml_rpc_plugin_list = array(
		'jetpack/jetpack.php',
	);

	/**
	 * The path to the htaccess template.
	 *
	 * @var string
	 */
	public $template = 'xml-rpc.tpl';

	/**
	 * Regular expressions to check if the rules are enabled.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @var array Regular expressions to check if the rules are enabled.
	 */
	public $rules = array(
		'enabled'     => '/\#\s+SGS XMLRPC Disable Service/si',
		'disabled'    => '/\#\s+SGS\s+XMLRPC\s+Disable\s+Service(.+?)\#\s+SGS\s+XMLRPC\s+Disable\s+Service\s+END(\n)?/ims',
		'disable_all' => '/\#\s+SGS\s+XMLRPC\s+Disable\s+Service(.+?)\#\s+SGS\s+XMLRPC\s+Disable\s+Service\s+END(\n)?/ims',
	);

	/**
	 * Check if we have active plugins that are using XML-RPC.
	 *
	 * @since  1.0.0
	 *
	 * @return array The array containing known active plugins using XML-RPC or empty array if none are active.
	 */
	public function plugins_using_xml_rpc() {
		// Get the list of active plugins.
		$active_plugins = get_option( 'active_plugins', array() );

		// The array that will contain conflicting plugins if there are any.
		$maybe_conflict = array();

		// Check if the function exists, since we are connecting a bit early.
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		// Loop trough active plugins and check if any is present in the known plugins that use XML-RPC.
		foreach ( $active_plugins as $key => $plugin ) {
			// Continue if the plugin is not in the list.
			if ( ! in_array( $plugin, $this->xml_rpc_plugin_list ) ) {
				continue;
			}

			// Get the plugin data and push it to an array.
			$plugin_data      = get_plugin_data( ABSPATH . 'wp-content/plugins/' . $plugin );
			$maybe_conflict[] = $plugin_data['Name'];
		}

		// Return the names of all active plugins that use XML-RPC or empty array to be consistent for the FE management.
		return $maybe_conflict;
	}
}
