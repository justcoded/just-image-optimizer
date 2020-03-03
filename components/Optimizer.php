<?php

namespace JustCoded\WP\ImageOptimizer\components;

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
		if ( \JustImageOptimizer::$settings->saved()
			&& \JustImageOptimizer::$settings->auto_optimize
			&& \JustImageOptimizer::$settings->check_requirements()
		) {
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
	 *
	 * @throws \Exception
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

		// do not run optimization and any logs if we don't have images to optimize.
		if ( empty( $attach_ids ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		$tries = 0;
		do {
			$this->optimize_images( $attach_ids );

			// remove processed attachments from list.
			foreach ( $attach_ids as $key => $attach_id ) {
				$status = (int) get_post_meta( $attach_id, '_just_img_opt_status' );
				if ( Media::STATUS_PROCESSED === $status ) {
					unset( $attach_ids[ $key ] );
				}
			}
		} while ( ! empty( $attach_ids ) && \JustImageOptimizer::$settings->tries_count > ++ $tries );
	}

	/**
	 * Ajax function for manual image optimize
	 *
	 * @throws \Exception
	 */
	public function manual_optimize() {
		$attach_id = (int) $_POST['attach_id'];
		$model     = new Media();

		$tries = 1;
		do {
			$this->optimize_images( [ $attach_id ] );
			$optimize_status = $model->check_optimization_status( $attach_id );
		} while ( Media::STATUS_PROCESSED !== $optimize_status && \JustImageOptimizer::$settings->tries_count > $tries ++ );

		$attach_stats    = $model->get_total_attachment_stats( $attach_id );
		$data_statistics = array(
			'saving_percent' => ( ! empty( $attach_stats->percent ) ? $attach_stats->percent : 0 ),
			'saving_size'    => ( ! empty( $attach_stats->saving_size ) ? jio_size_format( $attach_stats->saving_size ) : 0 ),
			'total_size'     => ( ! empty( $attach_stats->disk_usage ) ? jio_size_format( $attach_stats->disk_usage ) : 0 ),
			'count_images'   => $attach_stats->count_images,
		);
		header( 'Content-Type: application/json; charset=' . get_bloginfo( 'charset' ) );
		echo wp_json_encode( $data_statistics );
		wp_die();
	}

	/**
	 * Optimize_images
	 *
	 * @param array $attach_ids .
	 *
	 * @return bool
	 * @throws \Exception
	 */
	protected function optimize_images( array $attach_ids ) {
		global $wpdb;
		$table      = $wpdb->prefix . Log::TABLE_IMAGE_CONVERSION;
		$media      = new Media();
		$log        = new Log();
		$attach_ids = $media->size_limit( $attach_ids );
		$request_id = $log->start_request();

		// Create new stats record to DB.
		foreach ( $attach_ids as $key => $attach_id ) {
			$optimize_status = (int) get_post_meta( $attach_id, '_just_img_opt_status' );
			if ( Media::STATUS_PROCESSED === $optimize_status ) {
				unset( $attach_ids[ $key ] );
				continue;
			}

			$file_sizes = $media->get_file_sizes( $attach_id );
			$media->save_stats( $attach_id, $file_sizes );
			$log->save_details( $request_id, $attach_id, $file_sizes );
			update_post_meta( $attach_id, '_just_img_opt_status', Media::STATUS_IN_PROCESS );
		}

		$counter = \JustImageOptimizer::$service->optimize_images( $attach_ids, $log );

		if ( 0 !== $counter ) {
			foreach ( $attach_ids as $attach_id ) {
				$current_status   = 0;
				$check_conversion = $wpdb->get_results( "SELECT `status` FROM {$table} WHERE attach_id = {$attach_id}", ARRAY_A );

				if ( ! empty( $check_conversion ) ) {
					foreach ( $check_conversion as $conv_status ) {
						if ( $current_status <= intval( $conv_status['status'] ) ) {
							$current_status = $conv_status['status'];
						}
					}

					switch ( intval( $current_status ) ) {
						case 0:
						case 5:
							$log->update_status( $attach_id, $request_id, Log::STATUS_REMOVED );
							break;
						case 1:
							$log->update_status( $attach_id, $request_id, Log::STATUS_OPTIMIZED );
							break;
						case 2:
							$log->update_status( $attach_id, $request_id, Log::STATUS_CMYK );
							break;
						case 3:
							$log->update_status( $attach_id, $request_id, Log::STATUS_PARTIALLY );
							break;
						case 4:
							$log->update_status( $attach_id, $request_id, Log::STATUS_INAPPROPRIATE );
							break;
						default:
							continue 2;
					}
				}
			}
		}

		// Update statistics and status after replace images.
		foreach ( $attach_ids as $attach_id ) {
			$file_sizes = $media->get_file_sizes( $attach_id );
			$media->update_stats( $attach_id, $file_sizes );
			$log->update_details( $request_id, $attach_id, $file_sizes );
			$optimize_status = $media->check_optimization_status( $attach_id );
			update_post_meta( $attach_id, '_just_img_opt_status', $optimize_status );
		}

		$log->end_request( 'Processed: ' . $counter . ' ' . _n( 'file', 'files', $counter ) );

		return true;
	}
}
