<?php

namespace justimageoptimizer\components;

use justimageoptimizer\models\Settings;
use justimageoptimizer\models\Media;

/**
 * Class Optimizer
 */
class Optimizer extends \justimageoptimizer\core\Component {

	/**
	 * Class constructor.
	 * initialize WordPress hooks
	 */
	public function __construct() {
		parent::__construct();
		$this->run_cron();
		add_action( 'wp_ajax_manual_optimize', array( $this, 'manual_optimize' ) );
		add_action( 'add_attachment', array( $this, 'set_attachment_in_queue' ) );
	}

	/**
	 * Run cron job by Settings param
	 */
	protected function run_cron() {
		if ( self::$settings->auto_optimize === '1' ) {
			add_filter( 'cron_schedules', array( $this, 'add_schedule' ) );
			add_action( 'init', array( $this, 'add_cron_event' ) );
			add_action( 'optimizer_image_cron', array( $this, 'auto_optimizer' ) );
		}
	}

	/**
	 * Add Optimizer Image cron interval function.
	 */
	public function add_schedule( $schedules ) {
		$schedules['just_image_optimizer'] = array( 'interval' => 60 * 5, 'display' => 'Optimizer Image Cron Work' );

		return $schedules;
	}

	/**
	 * Set uploaded attachment in queue
	 *
	 * @param int $post_id Attachment id.
	 */
	function set_attachment_in_queue( $post_id ) {
		update_post_meta( $post_id, '_just_img_opt_queue', 1 );
	}

	/**
	 * Add Optimizer Image cron function.
	 */
	public function add_cron_event() {
		if ( ! wp_next_scheduled( 'optimizer_image_cron' ) ) {
			wp_schedule_event( time(), 'just_image_optimizer', 'optimizer_image_cron' );
		}
	}

	/**
	 * Auto optimizer cron job.
	 */
	public function auto_optimizer() {
		$attach_ids = array();
		$queue_args = array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => array( 'image/jpg', 'image/jpeg', 'image/gif', 'image/png' ),
			'posts_per_page' => self::$settings->image_limit,
			'orderby'        => 'id',
			'order'          => 'ASC',
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'   => '_just_img_opt_queue',
					'value' => '1',
				),
				array(
					'key'     => '_just_img_opt_queue',
					'compare' => 'NOT EXISTS',
					'value'   => '',
				),
			)
		);
		$set_queue  = new \WP_Query( $queue_args );
		while ( $set_queue->have_posts() ) {
			$set_queue->the_post();
			$attach_ids[] = get_the_ID();
			update_post_meta( get_the_ID(), '_just_img_opt_queue', 1 );
		}
		require ABSPATH . 'wp-admin/includes/file.php';
		$this->optimize_images( $attach_ids );
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
	 * Function for init filesystem accesses
	 *
	 * @return string
	 */
	public function filesystem_direct() {
		return 'direct';
	}

	/**
	 * Delete upload dir
	 *
	 * @param string $dir_path Path url.
	 *
	 * @throws \InvalidArgumentException Check directory.
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
	public function manual_optimize() {
		$this->optimize_images( $_POST );
		$attach_id       = $_POST['attach_id'];
		$model           = new Media();
		$data_statistics = array(
			'saving_percent' => get_post_meta( $attach_id, $model::DB_META_IMAGE_SAVING_PERCENT, true ),
			'saving_size'    => get_post_meta( $attach_id, $model::DB_META_IMAGE_SAVING, true ),
			'total_size'     => get_post_meta( $attach_id, $model::DB_META_IMAGE_DU, true ),
			'count_images'   => $model->get_count_images( $attach_id ),
		);
		header( "Content-Type: application/json; charset=" . get_bloginfo( 'charset' ) );
		echo wp_json_encode( $data_statistics );
		wp_die();
	}

	/**
	 * Function for optimize images
	 *
	 * @param array $attach_ids Attachment ids.
	 */
	protected function optimize_images( $attach_ids ) {
		global $wp_filesystem;
		$media = new Media();
		//add filter for WP_FIlesystem permission
		add_filter( 'filesystem_method', array( $this, 'filesystem_direct' ) );
		//encode to base64 attach ids
		$base_attach_ids = base64_encode( implode( ',', $attach_ids ) );
		//upload images from service
		\JustImageOptimizer::$service->upload_optimize_images( $base_attach_ids, WP_CONTENT_DIR . '/tmp' );
		$dir       = WP_CONTENT_DIR . '/tmp/image/';
		$get_image = scandir( $dir );
		$get_path  = $this->get_uploads_path();
		//set statistics and status before replace images
		foreach ( $attach_ids as $attach_id ) {
			$media->before_main_attach_stats[ $attach_id ] = $media->get_total_filesizes( $attach_id, false );
			$media->before_optimize_stats[ $attach_id ]    = array(
				'b_stats' => $media->get_total_filesizes( $attach_id, true ),
			);
			update_post_meta( $attach_id, '_just_img_opt_queue', 2 );
		}
		$media->set_before_sizes();
		//process for replace images
		if ( ! empty( $get_image ) ) {
			foreach ( $get_image as $key => $file ) {
				if ( $wp_filesystem->is_file( $dir . $file ) ) {
					foreach ( $get_path as $path ) {
						if ( $wp_filesystem->exists( $path . '/' . $file ) ) {
							$wp_filesystem->copy( $dir . $file, $path . '/' . $file, true );
						}
					}
				}
			}
			self::delete_dir( WP_CONTENT_DIR . '/tmp' );
		}
		//set statistics and status after replace images
		foreach ( $attach_ids as $attach_id ) {
			$media->after_main_attach_stats[ $attach_id ] = $media->get_total_filesizes( $attach_id, false );
			$media->after_optimize_stats[ $attach_id ]    = array(
				'a_stats' => $media->get_total_filesizes( $attach_id, true ),
			);
			$media->save( $attach_id );
			update_post_meta( $attach_id, '_just_img_opt_queue', 3 );
		}
		$media->set_saving_size();
	}
}