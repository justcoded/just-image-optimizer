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
use justimageoptimizer\services;
use justimageoptimizer\models;
use justimageoptimizer\controllers;

/**
 * Class JustImageOptimizer
 * Main plugin entry point. Includes components in constructor
 */
class JustImageOptimizer extends core\Singleton {
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
	 * Current Optimize service
	 *
	 * @var object
	 */
	public static $service;

	/**
	 * Settings model
	 *
	 * @var object
	 */
	public static $settings;

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
		self::$plugin_name = __( 'Just Image Optimizer', JustImageOptimizer::TEXTDOMAIN );
		self::$version     = 0.100;
		self::$settings = new models\Settings();
		// init features, which this plugin is created for.
		self::$service = services\ImageOptimizerFactory::create();
		new components\MediaInfo();
		new components\Optimizer();
		if ( is_admin() ) {
			new controllers\ConnectController();
			new controllers\SettingsController();
			new controllers\DashboardController();
		}
	}

}

JustImageOptimizer::run();
