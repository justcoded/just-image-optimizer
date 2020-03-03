<?php

/*
Plugin Name: Just Image Optimizer
Description: Compress image files, improve performance and boost your SEO rank using Google Page Speed Insights compression and optimization.
Tags: image, resize, optimize, optimise, compress, performance, optimisation, optimise JPG, pictures, optimizer, Google Page Speed
Version: 1.110.3
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
class JustImageOptimizer {
	use core\Singleton;
	/**
	 * Plugin text domain for translations
	 */
	const TEXTDOMAIN = 'justimageoptimizer';
	/**
	 * Plugin version option name
	 */
	const OPT_VERSION = 'jio_version';
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
	 * Plugin main entry point
	 *
	 * Protected constructor prevents creating another plugin instance with "new" operator.
	 */
	public function __construct() {
		$loader = new core\PluginLoader();
		// init plugin name and version.
		self::$plugin_name = __( 'Just Image Optimizer', self::TEXTDOMAIN );
		self::$version     = '1.110.3';
		self::$opt_version = get_option( self::OPT_VERSION );

		new services\ImageOptimizerFactory();

		self::$service  = services\ImageOptimizerFactory::create();
		self::$settings = new models\Settings();

		register_activation_hook( __FILE__, array( $loader, 'init_db' ) );

		// init components for media and optimizer.
		new components\MediaInfo();
		new components\Optimizer();
		new components\Replacement();
		new components\CacheControl();

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
				new controllers\DebugController();
			}
		}

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'jio_settings_link' ) );
	}

	/**
	 * Plugin_settings_link
	 *
	 * @param array $links .
	 *
	 * @return array
	 */
	public function jio_settings_link( $links ) {
		if ( ! empty( self::$service ) ) {
			$links[] = '<a href="' .
				admin_url( 'upload.php?page=just-img-opt-dashboard' ) .
				'">' . __( 'Dashboard' ) . '</a>';

			$links[] = '<a href="' .
				admin_url( 'upload.php?page=just-img-opt-settings' ) .
				'">' . __( 'Settings' ) . '</a>';
		} else {
			$links[] = '<a href="' .
				admin_url( 'upload.php?page=just-img-opt-dashboard' ) .
				'">' . __( 'Connect' ) . '</a>';
		}

		return $links;
	}

}

JustImageOptimizer::instance();
