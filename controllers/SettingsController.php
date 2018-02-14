<?php

namespace JustCoded\WP\ImageOptimizer\controllers;

use JustCoded\WP\ImageOptimizer\models\Settings;
use JustCoded\WP\ImageOptimizer\models\Media;
use JustCoded\WP\ImageOptimizer\models\Connect;

/**
 * Adds option settings page
 */
class SettingsController extends \JustCoded\WP\ImageOptimizer\core\Component {

	/**
	 * Class constructor.
	 * initialize WordPress hooks
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'init_settings_menu' ) );
		add_action( 'admin_print_scripts-media_page_just-img-opt-settings', array( $this, 'registerAssets' ) );
	}

	/**
	 * Register Assets
	 */
	public function registerAssets() {
		wp_enqueue_script(
			'just_img_opt_js',
			plugins_url( 'assets/js/settings.js', dirname( __FILE__ ) ),
			array( 'jquery' )
		);
		wp_enqueue_style( 'just_img_opt_css', plugins_url( 'assets/css/styles.css', dirname( __FILE__ ) ) );
	}

	/**
	 * Add new page to the WordPress Menu
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
		// check page access. we can edit settings only if connection is valid.
		if ( ! Connect::connected() ) {
			$this->render( 'redirect', array(
				'redirect_url' => admin_url() . 'upload.php?page=just-img-opt-connection',
			) );
		}

		$model = \JustImageOptimizer::$settings;
		$model->load( $_POST ) && $saved = $model->save();
		$this->render( 'dashboard/settings', array(
			'tab'   => 'settings',
			'model' => $model,
			'sizes' => Media::image_dimensions(),
			'saved' => ( isset( $saved ) ? $saved : false ),
		) );
	}
}
