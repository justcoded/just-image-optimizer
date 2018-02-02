<?php

namespace JustCoded\WP\ImageOptimizer\controllers;

use JustCoded\WP\ImageOptimizer\models;

/**
 * Class OptimizationLogController
 * Image Optimization Log
 */
class OptimizationLogController extends \JustCoded\WP\ImageOptimizer\core\Component {
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
		add_media_page(
			'Optimization Log',
			'Optimization Log',
			'manage_options',
			'just-img-opt-log',
			array( $this, 'actionIndex' )
		);
	}

	/**
	 * Image Optimization Log view
	 */
	public function actionIndex() {
		$model = new models\OptimizationLog();
		$this->render( 'log/index', array(
			'model' => $model,
		) );
	}
}

