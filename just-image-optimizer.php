<?php

/*
Plugin Name: Just Image Optimizer
Description: WordPress Plugin Boilerplate based on latest WordPress OOP practices
Version: 0.1
Author: Private Company
License: GPL3
*/

define( 'JUSTIMAGEOPTIMIZER_ROOT', dirname( __FILE__ ) );
require_once( JUSTIMAGEOPTIMIZER_ROOT . '/core/Autoload.php' );

use justimageoptimizer\core;
use justimageoptimizer\components;

/**
 * Class justImageOptimizer
 * Main plugin entry point. Includes components in constructor
 */
class justImageOptimizer extends core\Singleton {
	/**
	 * Textual plugin name
	 *
	 * @var string
	 */
	public static $plugin_name;

	/**
	 * Current plugin version
	 *
	 * @var float
	 */
	public static $version;

	/**
	 * Plugin text domain for translations
	 */
	const TEXTDOMAIN = 'justimageoptimizer';

	/**
	 * Plugin main entry point
	 *
	 * Protected constructor prevents creating another plugin instance with "new" operator.
	 */
	protected function __construct() {
		// init plugin name and version.
		self::$plugin_name = __( 'Just Image Optimizer', justImageOptimizer::TEXTDOMAIN );
		self::$version     = 0.100;

		// init features, which this plugin is created for.
		new components\SimonTitlePrefix();
	}

}

justImageOptimizer::run();
