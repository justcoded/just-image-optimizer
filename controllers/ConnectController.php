<?php

namespace justimageoptimizer\controllers;

use justimageoptimizer\models\Connect;
use justimageoptimizer\models\Settings;
use justimageoptimizer\services;

/**
 * Adds option connect page
 */
class ConnectController extends \justimageoptimizer\core\Component {

	/**
	 * Class constructor.
	 * initialize WordPress hooks
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'init_admin_menu' ) );
		add_action( 'admin_print_scripts-media_page_just-img-opt-connection', array( $this, 'registerAssets' ) );
		add_action( 'wp_ajax_ajax_check_api', array( $this, 'ajax_check_api' ) );
	}

	/**
	 * Add new page to the Wordpress Menu
	 */
	public function init_admin_menu() {
		if ( ! get_option( Settings::DB_OPT_IS_SECOND ) ) {
			add_media_page(
				__( 'Image Optimization', \JustImageOptimizer::TEXTDOMAIN ),
				__( 'Image Optimization', \JustImageOptimizer::TEXTDOMAIN ),
				'manage_options',
				'just-img-opt-connection',
				array( $this, 'actionIndex' )
			);
		} else {
			add_submenu_page(
				null,
				__( 'Image Optimization', \JustImageOptimizer::TEXTDOMAIN ),
				__( 'Image Optimization', \JustImageOptimizer::TEXTDOMAIN ),
				'manage_options',
				'just-img-opt-connection',
				array( $this, 'actionIndex' )
			);
		}
	}

	/**
	 * Is first save add redirect
	 *
	 * @return string Redirect to Settings Page
	 */
	public function redirect() {
		if ( isset( $_POST['submit-connect'] ) && get_option( Connect::DB_OPT_IS_FIRST ) !== '1' ) {
			return "<script>window.location = '" . admin_url() . "upload.php?page=just-img-opt-settings'</script>";
		}

		return null;
	}

	/**
	 * Render Connect page
	 */
	public function actionIndex() {
		$model   = new Connect();
		$model->load( $_POST ) && $model->save();
		$this->render( 'connect/connect-page', array(
			'model'             => $model,
			'tab'               => 'connect',
			'wizard' => get_option( $model::DB_OPT_IS_FIRST ),
			'redirect' => $this->redirect(),
		) );
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
		wp_enqueue_style( 'just_img_opt_css', plugins_url( 'assets/css/styles.css', dirname( __FILE__ ) ) );
	}

	/**
	 * Ajax function for check valid API key
	 */
	public function ajax_check_api() {
		$model = new Connect();
		$model->load( $_POST ) && $model->save();
		$service        = services\ImageOptimizerFactory::create();
		$connection_api = $service->check_api_key();
		update_option( $model::DB_OPT_STATUS, $connection_api );
		echo $connection_api;
		exit();
	}
}