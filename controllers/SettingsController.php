<?php

namespace justimageoptimizer\controllers;

use justimageoptimizer\models\Settings;
use justimageoptimizer\models\Media;
/**
 * Adds option settings page
 */
class SettingsController extends \justimageoptimizer\core\Component {

	/**
	 * Class constructor.
	 * initialize WordPress hooks
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'init_settings_menu' ) );
	}

	/**
	 * Add new page to the Wordpress Menu
	 */
	public function init_settings_menu() {
		add_submenu_page(
			null,
			__( 'Settings', \JustImageOptimizer::TEXTDOMAIN ),
			__( 'Settings', \JustImageOptimizer::TEXTDOMAIN ),
			'manage_options',
			'just-img-opt-settings',
			array( $this, 'actionIndex' )
		);
	}

	/**
	 * Render Settings page
	 */
	public function actionIndex() {
		$model = new Settings();
		$media = new Media();
		$model->load( $_POST ) && $model->save();
		$this->render( 'settings/index', array(
			'tab'         => 'settings',
			'model'       => $model,
			'sizes'       => $media->image_dimensions(),
			'image_sizes' => maybe_unserialize( get_option( $model::DB_OPT_IMAGE_SIZES ) ),
		) );
	}
}