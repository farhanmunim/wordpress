<?php
namespace SG_Security\Install_Service;

/**
 * SG Security Install main plugin class.
 */
abstract class Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 1.0.1
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '0.0.0';

	/**
	 * Return the current version of the installation.
	 *
	 * @since 1.0.1
	 */
	public function get_version() {
		return static::$version;
	}

	/**
	 * Run the install procedure. This function must be implemented by superclasses.
	 *
	 * @since 1.0.1
	 *
	 * @return mixed The result.
	 */
	abstract public function install();

}
