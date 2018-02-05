<?php

namespace JustCoded\WP\ImageOptimizer\components;

use JustCoded\WP\ImageOptimizer\models\Settings;
use JustCoded\WP\ImageOptimizer\models\Media;
use JustCoded\WP\ImageOptimizer\models\OptimizationLog;

/**
 * Class Optimizer
 */
class Optimizer extends \JustCoded\WP\ImageOptimizer\core\Component {

	// TODO: refactor method names (@AP)

	/**
	 * Class constructor.
	 * initialize WordPress hooks
	 */
	public function __construct() {
		$this->setup_cron();
		add_action( 'wp_ajax_manual_optimize', array( $this, 'manual_optimize' ) );
		add_action( 'add_attachment', array( $this, 'set_attachment_in_queue' ) );
	}

	/**
	 * Run cron job by Settings param
	 */
	protected function setup_cron() {
		if ( \JustImageOptimizer::$settings->auto_optimize ) {
			add_filter( 'cron_schedules', array( $this, 'add_schedule' ) );
			add_action( 'init', array( $this, 'add_cron_event' ) );
			add_action( 'optimizer_image_cron', array( $this, 'auto_optimizer' ) );
		}
	}

	/**
	 * Add Optimizer Image cron interval function.
	 *
	 * @param array $schedules An array of non-default cron schedules. Default empty.
	 *
	 * @return array
	 */
	public function add_schedule( $schedules ) {
		$schedules['just_image_optimizer'] = array(
			'interval' => 60 * 5,
			'display'  => 'Optimizer Image Cron Work',
		);

		return $schedules;
	}

	/**
	 * Set uploaded attachment in queue
	 *
	 * @param int $post_id Attachment id.
	 */
	public function set_attachment_in_queue( $post_id ) {
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
			'posts_per_page' => \JustImageOptimizer::$settings->image_limit,
			'orderby'        => 'id',
			'order'          => 'ASC',
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					// TODO: let's rename to _just_img_opt_status.
					'key'   => '_just_img_opt_queue',
					'value' => '1',
				),
				array(
					'key'     => '_just_img_opt_queue',
					'compare' => 'NOT EXISTS',
					'value'   => '',
				),
			),
		);
		$set_queue  = new \WP_Query( $queue_args );
		while ( $set_queue->have_posts() ) {
			$set_queue->the_post();
			$attach_ids[] = get_the_ID();
			// TODO: move status keys to constants
			update_post_meta( get_the_ID(), '_just_img_opt_queue', 1 );
		}
		require ABSPATH . 'wp-admin/includes/file.php';
		$this->optimize_images( $attach_ids );
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
		$attach_id = $_POST['attach_id'];

		$this->optimize_images( [ $attach_id ] );
		$model           = new Media();
		$attach_stats    = $model->get_total_attachment_stats( $attach_id );
		$data_statistics = array(
			'saving_percent' => ( ! empty( $attach_stats[0]->percent ) ? $attach_stats[0]->percent : 0 ),
			'saving_size'    => ( ! empty( $attach_stats[0]->saving_size ) ? size_format( $attach_stats[0]->saving_size ) : 0 ),
			'total_size'     => ( ! empty( $attach_stats[0]->disk_usage ) ? size_format( $attach_stats[0]->disk_usage ) : 0 ),
			'count_images'   => $model->get_count_images( $attach_id ),
		);
		header( 'Content-Type: application/json; charset=' . get_bloginfo( 'charset' ) );
		echo wp_json_encode( $data_statistics );
		wp_die();
	}

	/**
	 * Function for optimize images
	 *
	 * @param array $attach_ids Attachment ids.
	 */
	protected function optimize_images( array $attach_ids ) {
		global $wp_filesystem;
		$media      = new Media();
		$log        = new OptimizationLog();
		$attach_ids = $media->size_limit( $attach_ids );
		// add filter for WP_FIlesystem permission.
		add_filter( 'filesystem_method', array( $this, 'filesystem_direct' ) );
		WP_Filesystem();
		// set statistics and status before replace images.
		foreach ( $attach_ids as $attach_id ) {
			$media->save_stats( $attach_id, $media->get_file_sizes( $attach_id, 'single' ) );
			$log_id = $log->save_log( $attach_id, $media->get_file_sizes( $attach_id, 'single' ) );
			update_post_meta( $attach_id, '_just_img_opt_queue', 2 );
		}
		// upload images from service.
		$dir = WP_CONTENT_DIR . '/tmp/';
		\JustImageOptimizer::$service->upload_optimize_images( $attach_ids, $dir );

		$get_image = scandir( $dir );
		$get_path  = $media->get_uploads_path();
		// process image replacement.
		if ( ! empty( $get_image ) ) {
			foreach ( $get_image as $key => $file ) {
				if ( $wp_filesystem->is_file( $dir . $file ) ) {
					foreach ( $get_path as $path ) {
						if ( $wp_filesystem->exists( $path . '/' . $file ) ) {
							$optimize_image_size = getimagesize( $dir . $file );
							if ( 25 < $optimize_image_size[0] && 25 < $optimize_image_size[1] ) {
								$wp_filesystem->copy( $dir . $file, $path . '/' . $file, true );
							} else {
								$log->save_fail( $log_id, $file );
							}
						}
					}
				}
			}
			self::delete_dir( $dir );
		}

		// set statistics and status after replace images.
		foreach ( $attach_ids as $attach_id ) {
			$media->update_stats( $attach_id, $media->get_file_sizes( $attach_id, 'single' ) );
			$log->update_log( $log_id, $attach_id, $media->get_file_sizes( $attach_id, 'single' ) );
			update_post_meta( $attach_id, '_just_img_opt_queue', 3 );
		}
	}
}
