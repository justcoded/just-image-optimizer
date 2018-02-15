<?php

namespace JustCoded\WP\ImageOptimizer\controllers;

use JustCoded\WP\ImageOptimizer\models\Connect;
use JustCoded\WP\ImageOptimizer\models\Settings;
use JustCoded\WP\ImageOptimizer\models\Media;

/**
 * Adds option dashboard page
 */
class DashboardController extends \JustCoded\WP\ImageOptimizer\core\Component {

	/**
	 * Class constructor.
	 * initialize WordPress hooks
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'init_dashboard_menu' ) );
		add_action( 'admin_print_scripts-media_page_just-img-opt-dashboard', array( $this, 'registerAssets' ) );
	}

	/**
	 * Add new page to the WordPress Menu
	 */
	public function init_dashboard_menu() {
		$page_title = \JustImageOptimizer::$plugin_name;

		add_media_page(
			$page_title,
			$page_title,
			'manage_options',
			'just-img-opt-dashboard',
			array( $this, 'actionIndex' )
		);
	}

	/**
	 * Register Assets
	 */
	public function registerAssets() {
		wp_enqueue_script(
			'just_img_opt_js',
			plugins_url( 'assets/js/main.js', dirname( __FILE__ ) ),
			array( 'jquery' )
		);
		wp_enqueue_script(
			'google_charts',
			'https://www.gstatic.com/charts/loader.js',
			array(),
			'',
			false
		);
		wp_enqueue_style( 'just_img_opt_css', plugins_url( 'assets/css/styles.css', dirname( __FILE__ ) ) );
	}

	/**
	 * Render Dashboard page
	 */
	public function actionIndex() {
		// check page access.
		if ( ! Connect::connected() ) {
			$this->render( 'redirect', array(
				'redirect_url' => admin_url() . 'upload.php?page=just-img-opt-connection',
			) );
		}

		$model = new Media();
		$this->render( 'dashboard/index', array(
			'tab'   => 'dashboard',
			'model' => $model,
		) );
	}
}
