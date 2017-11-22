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
		add_action( 'wp_ajax_connect_api', array( $this, 'connect_api' ) );

	}

	 /**
	 * Add new page to the Wordpress Menu
	 */
	public function init_admin_menu() {
		add_submenu_page(
			null,
			__( 'Image Optimization', \JustImageOptimizer::TEXTDOMAIN ),
			__( 'Image Optimization', \JustImageOptimizer::TEXTDOMAIN ),
			'manage_options',
			'just-img-opt-connection',
			array( $this, 'actionIndex' )
		);
	}

	/**
	 * Render Connect page
	 */
	public function actionIndex() {
		$model = new Connect();
		if ( $model->load( $_POST ) && $saved = $model->save() ) {
			if ( ! \JustImageOptimizer::$settings->saved() ) {
				$this->render( 'redirect', array(
					'redirect_url' => admin_url() . 'upload.php?page=just-img-opt-settings',
				) );
			}
		}
		$this->render( 'connect/connect-page', array(
			'model' => $model,
			'tab'   => 'connect',
			'saved' => ( isset( $saved ) ? $saved : '' ),
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
	public function connect_api() {
		$service        = services\ImageOptimizerFactory::create( $_POST['service'], $_POST['api_key'] );
		$connection_api = $service->check_api_key();
		if ( $connection_api === '1' ) {
			flush_rewrite_rules();
		}
		echo $connection_api;
		exit();
	}
}