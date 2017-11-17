<?php

namespace justimageoptimizer\controllers;

use justimageoptimizer\models\Settings;
use justimageoptimizer\models\Media;
use justimageoptimizer\models\Connect;

/**
 * Adds option settings page
 */
class SettingsController extends \justimageoptimizer\core\Component {

	/**
	 * Class constructor.
	 * initialize WordPress hooks
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'admin_menu', array( $this, 'init_settings_menu' ) );
		add_action( 'admin_print_scripts-media_page_just-img-opt-settings', array( $this, 'registerAssets' ) );
		add_action( 'joi_settings_admin_notice', array( $this, 'notice' ) );

	}

	/**
	 * Notice message.
	 */
	public function notice() {
		if ( empty( maybe_unserialize( self::$settings->image_sizes ) ) ) {
			echo __( '<div class="update-nag">
                <strong>Please confirm the settings below and Save them.</strong>
                </div><br>', \JustImageOptimizer::TEXTDOMAIN
			);
		}
		if ( isset( $_POST['submit-settings'] ) ) {
			echo __( '<div class="update-nag" style="border-left-color: green !important">
                <strong>Settings options updated!</strong>
                <strong>Go to <a href=" ' . admin_url() . 'upload.php?page=just-img-opt-dashboard">Dashboard page</a>
                to view the general statistics.</strong>
                </div>', \JustImageOptimizer::TEXTDOMAIN
			);
		}
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
	 * Redirect to Connect page if connection false.
	 *
	 * @return string Redirect to Connect Page
	 */
	public function redirect() {
		if ( ! Connect::connected() ) {
			return admin_url() . 'upload.php?page=just-img-opt-connection';
		}

		return null;
	}

	/**
	 * Render Settings page
	 */
	public function actionIndex() {
		$model = self::$settings;
		$model->load( $_POST ) && $model->save();
		if ( ! empty( $this->redirect() ) ) {
			$this->render( 'redirect', array(
				'redirect_url' => $this->redirect(),
			) );
		}
		$this->render( 'settings/index', array(
			'tab'         => 'settings',
			'model'       => $model,
			'sizes'       => Media::image_dimensions(),
			'image_sizes' => maybe_unserialize( $model->image_sizes ),
		) );
	}
}