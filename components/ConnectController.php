<?php

namespace justimageoptimizer\components;

use justimageoptimizer\models\Settings;

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
		add_media_page(
			__( 'Image Optimization', \justImageOptimizer::TEXTDOMAIN ),
			__( 'Image Optimization', \justImageOptimizer::TEXTDOMAIN ),
			'manage_options',
			'just-img-opt-connection',
			array( $this, 'actionIndex' )
		);
	}

	/**
	 * Render Connect page
	 */
	public function actionIndex() {
		$model = new Settings();
		$model->save( $_POST );
		$this->render( 'connect/connect-page', array(
			'api_key_opt'       => $model::DB_OPT_API_KEY,
			'api_key'           => get_option( $model::DB_OPT_API_KEY ),
			'service_opt'       => $model::DB_OPT_SERVICE,
			'service'           => get_option( $model::DB_OPT_SERVICE ),
			'connection_status' => get_option( $model::DB_OPT_STATUS ),
			'tab'               => 'connect',
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
		$service        = \justImageOptimizer::$service;
		$model          = new Settings();
		$api_key        = ( isset( $_POST['api_key'] ) ? $_POST['api_key'] : '' );
		$connection_api = $service->check_api_key( $api_key );
		update_option( $model::DB_OPT_STATUS, $connection_api );
		echo $connection_api;
		exit();
	}
}