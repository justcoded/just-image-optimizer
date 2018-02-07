<?php

namespace JustCoded\WP\ImageOptimizer\controllers;

use JustCoded\WP\ImageOptimizer\models;

/**
 * Class OptimizationLogController
 * Image Optimization Log
 */
class LogController extends \JustCoded\WP\ImageOptimizer\core\Component {
	/**
	 * OptimizationLogController constructor.
	 * Init WP hooks
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'initRoutes' ) );
	}

	/**
	 * Image Optimization Log page
	 */
	public function initRoutes() {
		$page_title = \JustImageOptimizer::$plugin_name;

		add_submenu_page(
			null,
			$page_title,
			$page_title,
			'manage_options',
			'just-img-opt-log',
			array( $this, 'actionIndex' )
		);
	}

	/**
	 * Image Optimization Log view
	 */
	public function actionIndex() {
		$model    = new models\Log();
		$store_id = ( isset( $_GET['store_id'] ) ? $_GET['store_id'] : 0 );
		if ( isset( $store_id ) && ! empty( $store_id ) ) {
			$this->render( 'log/single-log', array(
				'model'    => $model,
				'store_id' => $store_id,
				'tab'      => 'log',
			) );
		} else {
			$this->render( 'log/index', array(
				'model' => $model,
				'tab'   => 'log',
			) );
		}
	}
}

