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
		if ( ! empty( get_option( Connect::DB_OPT_SERVICE ) ) ) {
			add_action( 'admin_menu', array( $this, 'init_settings_menu' ) );
		}
		add_action( 'admin_print_scripts-media_page_just-img-opt-settings', array( $this, 'registerAssets' ) );
		if ( empty( get_option( Settings::DB_OPT_IMAGE_SIZES ) ) ) {
			add_action( 'joi_settings_admin_notice', array( $this, 'notice' ) );
		}
	}

	/**
	 * Notice message.
	 */
	public function notice() {
		echo __( '<div class="update-nag">
                <strong>Please confirm the settings below and Save them.</strong>
                </div>', \JustImageOptimizer::TEXTDOMAIN
		);
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
	 * Is first save add redirect
	 *
	 * @return string Redirect to Dashboard Page
	 */
	public function redirect() {
		if ( isset( $_POST['submit-settings'] ) && get_option( Settings::DB_OPT_IS_SECOND ) !== '1' ) {
			return "<script>window.location = '" . admin_url() . "upload.php?page=just-img-opt-dashboard'</script>";
		}

		return null;
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
			'redirect' => $this->redirect(),
		) );
	}
}