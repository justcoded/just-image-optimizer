<?php

namespace justimageoptimizer\controllers;

use justimageoptimizer\models\Connect;
use justimageoptimizer\models\Settings;
use justimageoptimizer\models\Media;

/**
 * Adds option dashboard page
 */
class DashboardController extends \justimageoptimizer\core\Component {

	/**
	 * Class constructor.
	 * initialize WordPress hooks
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'admin_menu', array( $this, 'init_dashboard_menu' ) );
		add_action( 'admin_print_scripts-media_page_just-img-opt-dashboard', array( $this, 'registerAssets' ) );
		add_action( 'joi_dashboard_admin_notice', array( $this, 'notice' ) );

	}

	/**
	 * Notice message.
	 */
	public function notice() {
		if ( empty( self::$settings->auto_optimize ) ) {
			echo __( '<div class="update-nag">
	                <strong>Automatic image optimization is disabled. Please check
					<a href=" ' . admin_url() . 'upload.php?page=just-img-opt-settings">Settings</a>
					 tab to enable it.</strong></div><br>', \JustImageOptimizer::TEXTDOMAIN
			);
		}
		if ( empty( maybe_unserialize( self::$settings->image_sizes ) ) ) {
			echo __( '<div class="update-nag">
	                <strong>Image sizes for optimization are not selected. Please check
					<a href=" ' . admin_url() . 'upload.php?page=just-img-opt-settings">Settings</a>
					 tab to select sizes.</strong></div>', \JustImageOptimizer::TEXTDOMAIN
			);
		}
	}

	/**
	 * Add new page to the Wordpress Menu
	 */
	public function init_dashboard_menu() {
		add_media_page(
			__( 'Image Optimization', \JustImageOptimizer::TEXTDOMAIN ),
			__( 'Image Optimization', \JustImageOptimizer::TEXTDOMAIN ),
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
	 * Redirect to Connect page if connection false.
	 *
	 * @return string Redirect to Connect Page
	 */
	public function redirect() {
		if ( ! Connect::connected() ) {
			return admin_url() . 'upload.php?page=just-img-opt-connection';
		}

		return null;
	}

	/**
	 * Render Dashboard page
	 */
	public function actionIndex() {
		$model = new Media();
		if ( ! empty( $this->redirect() ) ) {
			$this->render( 'redirect', array(
				'redirect_url' => $this->redirect(),
			) );
		}
		$this->render( 'dashboard/index', array(
			'tab'   => 'dashboard',
			'model' => $model,
		) );
	}
}