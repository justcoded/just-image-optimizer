<?php


namespace JustCoded\WP\ImageOptimizer\controllers;

use JustCoded\WP\ImageOptimizer\core\Component;
use JustCoded\WP\ImageOptimizer\models;

/**
 * Class DebugController
 *
 * @package JustCoded\WP\ImageOptimizer\controllers
 */
class DebugController extends Component {

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
			'just-img-opt-debug',
			array( $this, 'actionIndex' )
		);
	}

	/**
	 * Image Optimization Log view
	 */
	public function actionIndex() {
		$model = new models\Debug();
		$this->render( 'debug/index', array(
			'model' => $model,
			'tab'   => 'debug',
		) );
	}

}
