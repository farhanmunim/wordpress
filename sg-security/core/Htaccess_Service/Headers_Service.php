<?php
namespace SG_Security\Htaccess_Service;

/**
 * Class managing the header related htaccess rules.
 */
class Headers_Service extends Abstract_Htaccess_Service {

	/**
	 * The path to the htaccess template.
	 *
	 * @var string
	 */
	public $template = 'xss-headers.tpl';

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
		'enabled'     => '/\#\s+SGS XSS Header Service/si',
		'disabled'    => '/\#\s+SGS\s+XSS\s+Header\s+Service(.+?)\#\s+SGS\s+XSS\s+Header\s+Service\s+END(\n)?/ims',
		'disable_all' => '/\#\s+SGS\s+XSS\s+Header\s+Service(.+?)\#\s+SGS\s+XSS\s+Header\s+Service\s+END(\n)?/ims',
	);
}
