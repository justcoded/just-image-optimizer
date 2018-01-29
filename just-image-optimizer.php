<?php

/*
Plugin Name: Just Image Optimizer
Description: Optimize your site images (reduce size). Based on Google Page Speed.
Version: 0.1
Author: JustCoded
License: GPL3
*/

define( 'JUSTIMAGEOPTIMIZER_ROOT', dirname( __FILE__ ) );
require_once JUSTIMAGEOPTIMIZER_ROOT . '/core/Autoload.php';

use JustCoded\WP\ImageOptimizer\core;
use JustCoded\WP\ImageOptimizer\components;
use JustCoded\WP\ImageOptimizer\services;
use JustCoded\WP\ImageOptimizer\models;
use JustCoded\WP\ImageOptimizer\controllers;

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
	 * @var services\ImageOptimizerInterface
	 */
	public static $service;

	/**
	 * Settings model
	 *
	 * @var models\Settings
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
		self::$plugin_name = __( 'Just Image Optimizer', self::TEXTDOMAIN );
		self::$version     = 0.100;

		register_activation_hook( __FILE__, array( $this, 'initDB' ) );

		// init global static objects.
		self::$settings = new models\Settings();
		self::$service  = services\ImageOptimizerFactory::create();

		// init components for media and optimizer.
		new components\MediaInfo();
		new components\Optimizer();

		// admin panel option pages.
		if ( is_admin() ) {
			new controllers\ConnectController();
			new controllers\SettingsController();
			new controllers\DashboardController();
			new controllers\MigrateController();
		}
	}

	// init joi plugin Media DB
	public function initDB() {
		$migrate    = new models\Migrate;
		$migrations = $migrate->findMigrations();
		$migrate->migrate( $migrations );
	}

}

JustImageOptimizer::run();
