<?php

namespace justimageoptimizer\components;
use justimageoptimizer\models\Settings;
/**
 * Adds option connect page
 */
class ConnectController extends \justimageoptimizer\core\Component {
	const DB_OPT_API_KEY = '_just_img_opt_api_key';
	const DB_OPT_SERVICE = '_just_img_opt_service';

	/**
	 * Class constructor.
	 * initialize WordPress hooks
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'init_admin_menu' ) );
		add_action( 'admin_print_scripts', array( $this, 'registerAssets' ) );
	}

	/**
	 * Add new page to the Wordpress Menu
	 */
	public function init_admin_menu() {
		add_media_page(
			__( 'Image Optimization', \justImageOptimizer::TEXTDOMAIN ),
			__( 'Image Optimization', \justImageOptimizer::TEXTDOMAIN ),
			'manage_options',
			'just-img-opt-connection',
			array( $this, 'actionIndex' )
		);
	}

	/**
	 * Render Connect page
	 */
	public function actionIndex() {
		$model = new Settings();
		$model->save( $_POST );
		$this->render( 'connect/connect-page', array(
			'api_key_opt' => self::DB_OPT_API_KEY,
			'api_key'     => get_option( self::DB_OPT_API_KEY ),
			'service_opt' => self::DB_OPT_SERVICE,
			'service'     => get_option( self::DB_OPT_SERVICE ),
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
	public function checkPageSpeed($url, $key ){
		$url_req = 'https://www.googleapis.com/pagespeedonline/v1/runPagespeed?url='.$url.'&key='.$key.'';
		if (function_exists('file_get_contents')) {
			$result = @file_get_contents($url_req);
		}
		if ($result == '') {
			$ch = curl_init();
			$timeout = 60;
			curl_setopt($ch, CURLOPT_URL, $url_req);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$result = curl_exec($ch);
			curl_close($ch);
		}

		return $result;
	}
//
//	public function downloadOptimizeImage( $url, $strategy, $key ) {
//		$url_req = 'https://www.googleapis.com/pagespeedonline/v3beta1/optimizeContents?url='.$url.'&strategy='.$strategy.'&key='.$key.'';
//		if (function_exists('file_get_contents')) {
//			$result = @file_get_contents($url_req);
//		}
//		if ($result == '') {
//			$ch = curl_init();
//			$timeout = 60;
//			curl_setopt($ch, CURLOPT_URL, $url_req);
//			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
//			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
//			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
//			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
//			$result = curl_exec($ch);
//			curl_close($ch);
//		}
//
//		return $result;
//	}
}
