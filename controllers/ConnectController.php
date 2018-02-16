<?php

namespace JustCoded\WP\ImageOptimizer\controllers;

use JustCoded\WP\ImageOptimizer\models\Connect;
use JustCoded\WP\ImageOptimizer\models\Settings;
use JustCoded\WP\ImageOptimizer\services;

/**
 * Adds option connect page
 */
class ConnectController extends \JustCoded\WP\ImageOptimizer\core\Component {

	/**
	 * Class constructor.
	 * initialize WordPress hooks
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'init_admin_menu' ) );
		add_action( 'admin_print_scripts-media_page_just-img-opt-connection', array( $this, 'registerAssets' ) );
		add_action( 'wp_ajax_joi_check_api_connect', array( $this, 'check_api_connect' ) );

	}

	/**
	 * Add new page to the WordPress Menu
	 */
	public function init_admin_menu() {
		$page_title = \JustImageOptimizer::$plugin_name;

		add_submenu_page(
			null,
			$page_title,
			$page_title,
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
		$this->render( 'dashboard/connect', array(
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
	public function check_api_connect() {
		try {
			$service = services\ImageOptimizerFactory::create(
				sanitize_key( $_POST['service'] ),
				sanitize_text_field( $_POST['api_key'] )
			);
			$connection_status = $service->check_api_key();
			echo esc_attr( $connection_status );
		} catch ( \Exception $e ) {
			echo '0';
		}
		exit();
	}
}
