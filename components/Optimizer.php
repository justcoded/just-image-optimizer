<?php

namespace JustCoded\WP\ImageOptimizer\components;

use JustCoded\WP\ImageOptimizer\models\Settings;
use JustCoded\WP\ImageOptimizer\models\Media;
use JustCoded\WP\ImageOptimizer\models\Log;

/**
 * Class Optimizer
 */
class Optimizer extends \JustCoded\WP\ImageOptimizer\core\Component {

	/**
	 * Class constructor.
	 * initialize WordPress hooks
	 */
	public function __construct() {
		add_action( 'wp_ajax_manual_optimize', array( $this, 'manual_optimize' ) );
		add_action( 'add_attachment', array( $this, 'set_attachment_in_queue' ) );

		$this->setup_cron();
	}

	/**
	 * Run cron job by Settings param
	 */
	protected function setup_cron() {
		if ( \JustImageOptimizer::$settings->auto_optimize ) {
			add_action( 'init', array( $this, 'check_cron_scheduled' ) );
			add_filter( 'cron_schedules', array( $this, 'init_cron_schedule' ) );
			add_action( 'just_image_optimizer_autorun', array( $this, 'auto_optimize' ) );
		}
	}

	/**
	 * Add Optimizer Image cron interval function.
	 *
	 * @param array $schedules An array of non-default cron schedules. Default empty.
	 *
	 * @return array
	 */
	public function init_cron_schedule( $schedules ) {
		$schedules['just_image_optimizer'] = array(
			'interval' => 60 * 5, // 5 minutes
			'display'  => 'Image optimizer background optimization',
		);

		return $schedules;
	}

	/**
	 * Re-schedule our auto optimizer background job if needed.
	 */
	public function check_cron_scheduled() {
		if ( ! wp_next_scheduled( 'just_image_optimizer_autorun' ) ) {
			wp_schedule_event( time(), 'just_image_optimizer', 'just_image_optimizer_autorun' );
		}
	}

	/**
	 * Set uploaded attachment in queue
	 *
	 * @param int $post_id Attachment id.
	 */
	public function set_attachment_in_queue( $post_id ) {
		update_post_meta( $post_id, '_just_img_opt_status', Media::STATUS_IN_QUEUE );
	}


	/**
	 * Auto optimizer cron job.
	 */
	public function auto_optimize() {
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
					'key'   => '_just_img_opt_status',
					'value' => Media::STATUS_IN_QUEUE,
				),
				array(
					'key'     => '_just_img_opt_status',
					'compare' => 'NOT EXISTS',
					'value'   => '',
				),
			),
		);
		$set_queue  = new \WP_Query( $queue_args );
		while ( $set_queue->have_posts() ) {
			$set_queue->the_post();
			$attach_ids[] = get_the_ID();
			update_post_meta( get_the_ID(), '_just_img_opt_status', Media::STATUS_IN_QUEUE );
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
		$attach_id = (int)$_POST['attach_id'];

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
		$log        = new Log();
		$before_attach_found = $attach_ids;
		$attach_ids = $media->size_limit( $attach_ids );
		// add filter for WP_FIlesystem permission.
		add_filter( 'filesystem_method', array( $this, 'filesystem_direct' ) );
		WP_Filesystem();
		// set statistics and status before replace images.
		$request_id = $log->start_request();

		foreach ( $attach_ids as $attach_id ) {
			$file_sizes = $media->get_file_sizes( $attach_id, 'detailed' );
			$media->save_stats( $attach_id, $file_sizes );
			$log->save_details( $request_id, $attach_id, $file_sizes );
			update_post_meta( $attach_id, '_just_img_opt_status', Media::STATUS_IN_PROCESS );
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
								$log->save_status( $request_id, $file, Log::STATUS_OPTIMIZED );
							} else {
								$log->save_status( $request_id, $file, Log::STATUS_ABORTED );
							}
						}
					}
				}
			}
			self::delete_dir( $dir );
		}

		// TODO: if images processing failed - clean status to IN QUEUE again, otherwise stats is wrong (bytes_after = 0 and it calculate wrong stats)

		// set statistics and status after replace images.
		foreach ( $attach_ids as $attach_id ) {
			$file_sizes = $media->get_file_sizes( $attach_id, 'detailed' );
			$media->update_stats( $attach_id, $file_sizes );
			$log->update_details( $request_id, $attach_id, $file_sizes );
			update_post_meta( $attach_id, '_just_img_opt_status', Media::STATUS_PROCESSED );
		}
	}
}
