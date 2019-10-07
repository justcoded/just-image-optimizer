<?php


namespace JustCoded\WP\ImageOptimizer\controllers;

use JustCoded\WP\ImageOptimizer\components;
use JustCoded\WP\ImageOptimizer\core;
use JustCoded\WP\ImageOptimizer\models;
use JustCoded\WP\ImageOptimizer\includes\Singleton;

require_once JUSTIMAGEOPTIMIZER_ROOT . '/vendor/autoload.php';

/**
 * Class Loader
 *
 * @package JustCoded\WP\Imagizer\controllers
 *
 * @method ServiceController instance() static
 */
class ServiceController {
	use Singleton;

	/**
	 * Imagizer options page path
	 *
	 * @var string $page_path .
	 */
	public static $page_path;

	/**
	 * Loader constructor.
	 */
	public function __construct() {
		if ( ! class_exists( 'WebPConvert\WebPConvert' ) ) {
			$this->notice( 'You can not use conversion option, because WebPConvert not installed.' );

			return;
		}

		if ( ! function_exists( 'exec' ) ) {
			$this->notice( 'The "exec" function is not available on your server. Plugin will not be able to convert images and will have no effect.' );

			return;
		}

		self::$page_path = '/just-image-optimizer/views/imagizer';

		add_action( 'admin_menu', array( $this, 'imagizer_init' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'imagizer_dashboard_widget' ) );

		components\ImagizerRest::instance();
		components\Replacement::instance();
	}

	/**
	 * Notice
	 *
	 * @param string $message .
	 * @param string $classes .
	 */
	public static function notice( $message, $classes = 'notice notice-warning is-dismissible' ) {
		if ( ! empty( $message ) ) {
			printf( '<div class="%2$s"><p>%1$s</p></div>', $message, $classes );
		}
	}

	/**
	 * Imagizer_activate
	 */
	public static function imagizer_activate() {
		$sys = strtoupper( substr( PHP_OS, 0, 3 ) );

		$options_default = (object) array(
			'sys'          => $sys,
			'pathes'       => array(
				'root' => UPLOADS_ROOT,
				'webp' => WEBP_DIR,
				'jp2'  => JP2_DIR,
			),
			'quality'      => 80,
			'amount'       => 300,
			'replacement'  => false,
			'lazy'         => false,
			'images_total' => 0,
			'converted'    => array(
				'webp' => 0,
				'jp2'  => 0,
			),
		);

		update_option( 'imagizer_options', $options_default );
	}

	/**
	 * Imagizer_init
	 *
	 * @return void
	 */
	public function imagizer_init() {
		$this->regiser_assets();

		add_submenu_page(
			'upload.php',
			'Just Imagizer',
			'Just Imagizer',
			'manage_options',
			JUSTIMAGEOPTIMIZER_ROOT . '/views/imagizer/imagizer-option-page.php',
			''
		);
	}

	/**
	 * Regiser_assets
	 */
	public function regiser_assets() {

		// Styles.
		wp_enqueue_style(
			'imagizer-admin-style',
			plugins_url( 'assets/css/imagizer-admin-style.css', dirname( __FILE__ ) ) );

		// Jquery UI.
		wp_enqueue_style(
			'jquery-ui',
			'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css' );

		// Scripts.
		wp_enqueue_script(
			'jquery-ui',
			'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js',
			array( 'jquery' )
		);

		// Scripts.
		wp_enqueue_script(
			'imagizer-helper',
			plugins_url( 'assets/js/imagizer-worker.js', dirname( __FILE__ ) ),
			array( 'jquery' )
		);

		wp_enqueue_script(
			'wp-deactivation-message',
			plugins_url( 'assets/js/message.js', dirname( __FILE__ ) ),
			array( 'jquery' )
		);

	}

	/**
	 * Load_lazy_script
	 */
	public function load_lazy_script() {
		wp_enqueue_script(
			'lazyload',
			plugins_url( 'assets/js/lazyload.min.js', dirname( __FILE__ ) ),
			array( 'jquery' )
		);

		wp_enqueue_script(
			'lazyload-activation',
			plugins_url( 'assets/js/lazyload-activation.js', dirname( __FILE__ ) ),
			array( 'jquery' )
		);
	}

	/**
	 * Imagizer_deactivate
	 */
	public function imagizer_deactivate() {
		$query_model = new models\QueryModel();
		delete_option( 'imgzr-quality' );
		delete_option( 'imagizer_options' );

		$query = $query_model->im_query_vars()['get'];

		if ( ! empty( $query['keep'] ) && 'false' === $query['keep'] ) {
			core\Imagizer::instance()->fs->clean_space();
		}
	}

	public function imagizer_dashboard_widget() {
		wp_add_dashboard_widget( 'imagizer-dashboard', 'Imagizer', array( $this, 'render_dashboard_widget' ) );
	}

	public function render_dashboard_widget() {
		include JUSTIMAGEOPTIMIZER_ROOT . '/views/imagizer/_dash-widget.php';
	}
}
