<?php

namespace justimageoptimizer\components;

use justimageoptimizer\models\Settings;
use justimageoptimizer\services\GooglePagespeed;

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
			__( 'Settings', \justImageOptimizer::TEXTDOMAIN ),
			__( 'Settings', \justImageOptimizer::TEXTDOMAIN ),
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
		$model->save( $_POST );
		$this->render( 'settings/index', array(
			'tab' => 'settings',
		) );
	}
}