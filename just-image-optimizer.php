<?php

/*
Plugin Name: Just Image Optimizer
Description: Compress image files, improve performance and boost your SEO rank using Google Page Speed Insights compression and optimization.
Tags: image, resize, optimize, optimise, compress, performance, optimisation, optimise JPG, pictures, optimizer, Google Page Speed
Version: 1.1.3
Author: JustCoded
License: GPLv2 or later
*/

define( 'JUSTIMAGEOPTIMIZER_ROOT', dirname( __FILE__ ) );
require_once JUSTIMAGEOPTIMIZER_ROOT . '/core/Autoload.php';
require_once JUSTIMAGEOPTIMIZER_ROOT . '/functions.php';

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
	 * Database options plugin version
	 *
	 * @var float
	 */
	public static $opt_version;

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
	 * Plugin version option name
	 */
	const OPT_VERSION = 'joi_version';

	/**
	 * Plugin main entry point
	 *
	 * Protected constructor prevents creating another plugin instance with "new" operator.
	 */
	protected function __construct() {
		$loader = new core\PluginLoader();
		// init plugin name and version.
		self::$plugin_name = __( 'Just Image Optimizer', self::TEXTDOMAIN );
		self::$version     = '1.103';
		self::$opt_version = get_option( self::OPT_VERSION );
		self::$settings    = new models\Settings();
		self::$service     = services\ImageOptimizerFactory::create();

		register_activation_hook( __FILE__, array( $loader, 'init_db' ) );

		// init components for media and optimizer.
		new components\MediaInfo();
		new components\Optimizer();

		// admin panel option pages.
		// we use wp_doing_ajax to prevent version check under ajax.
		if ( ! wp_doing_ajax() && $loader->check_migrations_available() ) {
			new controllers\MigrateController();
		} else {
			// init global static objects.
			if ( is_admin() ) {
				new controllers\ConnectController();
				new controllers\SettingsController();
				new controllers\DashboardController();
				new controllers\LogController();
			}
		}
	}
}

JustImageOptimizer::run();
