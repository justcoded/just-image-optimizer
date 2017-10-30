<?php

namespace justimageoptimizer\components;

use justimageoptimizer\models\Settings;

/**
 * Class Optimizer
 */
class Optimizer extends \justimageoptimizer\core\Component {

	/**
	 * Class constructor.
	 * initialize WordPress hooks
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'optimizer_image_add_cron' ) );
		add_filter( 'cron_schedules', array( $this, 'optimizer_image_add_schedule' ) );
		add_action( 'admin_print_scripts-upload.php', array( $this, 'registerAssets' ) );
		add_action( 'wp_ajax_ajax_manual_optimize', array( $this, 'ajax_manual_optimize' ) );
	}

	/**
	 * Add Optimizer Image cron function.
	 */
	public function optimizer_image_add_cron() {
		if ( ! wp_next_scheduled( 'optimizer_image_cron_start' ) ) {
			wp_schedule_event( time(), 'optimizer_image', 'optimizer_image_cron_start' );
		}
	}

	/**
	 * Add Optimizer Image cron interval function.
	 */
	public function optimizer_image_add_schedule() {
		$schedules['optimizer_image'] = array( 'interval' => 5 * 60, 'display' => 'Optimizer Image Cron Work' );

		return $schedules;
	}

	/**
	 * Register Assets
	 */
	public function registerAssets() {
		wp_enqueue_script(
			'just_img_manual_js',
			plugins_url( 'assets/js/optimize.js', dirname( __FILE__ ) ),
			array( 'jquery' )
		);
	}

	/**
	 * Get all date upload dir
	 *
	 * @return array
	 */
	static function get_uploads_path() {
		$path = array();
		foreach ( glob( wp_upload_dir()['basedir'] . '/*', GLOB_ONLYDIR ) as $upload ) {
			foreach ( glob( $upload . '/*', GLOB_ONLYDIR ) as $upload_dir ) {
				$path[] = $upload_dir;
			}
		}

		return $path;
	}

	/**
	 * Delete upload dir
	 *
	 * @param string $dir_path Path url.
	 */
	public static function delete_dir( $dir_path ) {
		if ( ! is_dir( $dir_path ) ) {
			throw new \InvalidArgumentException( "$dir_path must be a directory" );
		}
		if ( substr( $dir_path, strlen( $dir_path ) - 1, 1 ) !== '/' ) {
			$dir_path .= '/';
		}
		$files = glob( $dir_path . '*', GLOB_MARK );
		foreach ( $files as $file ) {
			if ( is_dir( $file ) ) {
				self::delete_dir( $file );
			} else {
				unlink( $file );
			}
		}
		rmdir( $dir_path );
	}

	/**
	 * Ajax function for manual image optimize
	 */
	public function ajax_manual_optimize() {
		$attach_id      = ( isset( $_POST['attach_id'] ) ? $_POST['attach_id'] : '' );
		$base_attach_id = base64_encode( $attach_id );
		\justImageOptimizer::$service->upload_optimize_images( get_option( Settings::DB_OPT_API_KEY ), home_url( '/just-image-optimize/' . $base_attach_id . '' ) );
		$dir       = WP_CONTENT_DIR . '/tmp/image/';
		$get_image = scandir( $dir );
		$get_path  = $this->get_uploads_path();
		if ( ! empty( $get_image ) ) {
			foreach ( $get_image as $key => $file ) {
				if ( is_file( $dir . $file ) ) {
					foreach ( $get_path as $path ) {
						if ( file_exists( $path . '/' . $file ) ) {
							copy( $dir . $file, $path . '/' . $file );
						}
					}
				}
			}
			self::delete_dir( WP_CONTENT_DIR . '/tmp' );
			add_post_meta( $attach_id, '_just_img_opt_queue', 3 );
		}
		wp_die();
	}
}