<?php

namespace JustCoded\WP\ImageOptimizer\models;

use JustCoded\WP\ImageOptimizer\core;
use JustCoded\WP\ImageOptimizer\components\Filesystem;
use WP_Error;

/**
 * Class Media
 *
 * Work with attachment images statistics
 */
class Media extends core\Model {

	const TABLE_IMAGE_STATS = 'image_optimize';

	const FORMATS            = [ 'webp', 'jp2' ];
	const MONTH_YEAR_PATTERN = '/(\d{4}\/\d{2})/';

	const COL_ATTACH_ID    = 'attach_id';
	const COL_ATTACH_NAME  = 'attach_name';
	const COL_IMAGE_SIZE   = 'image_size';
	const COL_IMAGE_FORMAT = 'image_format';
	const COL_BYTES_BEFORE = 'bytes_before';
	const COL_BYTES_AFTER  = 'bytes_after';

	const STATUS_IN_QUEUE           = 1;
	const STATUS_IN_PROCESS         = 2;
	const STATUS_PROCESSED          = 3;
	const STATUS_PARTIALY_PROCESSED = 4;
	const STATUS_INAPPROPRIATE      = 5;

	/**
	 * Arguments query array to use.
	 *
	 * @var array $query_args
	 */
	protected $query_args = array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'post_mime_type' => array( 'image/jpg', 'image/jpeg', 'image/gif', 'image/png' ),
		'posts_per_page' => - 1,
		'orderby'        => 'id',
		'order'          => 'ASC',
	);

	/**
	 * Save attachment stats before optimize
	 *
	 * @param int   $attach_id Attach ID.
	 * @param array $stats Array with stats attachments.
	 */
	public function save_stats( $attach_id, $stats ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_STATS;
		$table_conv = $wpdb->prefix . Log::TABLE_IMAGE_CONVERSION;

		$exists = $wpdb->get_col( $wpdb->prepare(
			"SELECT " . self::COL_IMAGE_SIZE . " FROM $table_name WHERE " . self::COL_ATTACH_ID . " = %d",
			$attach_id
		) );

		foreach ( $stats as $size => $file_size ) {
			$image_data = image_get_intermediate_size( $attach_id, $size );

			// check row exist first.
			if ( in_array( $size, $exists ) ) {
				continue;
			}

			foreach ( self::FORMATS as $format ) {
				$file_size_converted = $wpdb->get_var(
					"SELECT " . Log::COL_FILE_SIZE . "
						FROM {$table_conv}
						WHERE " . Log::COL_ATTACH_ID . " = {$attach_id}
						AND " . Log::COL_IMAGE_SIZE . " = '{$size}'
						AND " . Log::COL_IMAGE_FORMAT . " = '{$format}'"
				);

				$attach_name = ! empty( $image_data['file'] ) ? $image_data['file'] : basename( wp_get_attachment_metadata( $attach_id )['file'] );

				$wpdb->insert(
					$table_name,
					array(
						self::COL_ATTACH_ID    => $attach_id,
						self::COL_ATTACH_NAME  => $attach_name,
						self::COL_IMAGE_SIZE   => $size,
						self::COL_IMAGE_FORMAT => $format,
						self::COL_BYTES_BEFORE => $file_size,
						self::COL_BYTES_AFTER  => ! empty( $file_size_converted ) ? $file_size_converted : 0,
					)
				);
			}
		}
	}

	/**
	 * Update attachment stats after optimize
	 *
	 * @param int   $attach_id Attach ID.
	 * @param array $stats Array with stats attachments.
	 */
	public function update_stats( $attach_id, $stats ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_STATS;
		$table_conv = $wpdb->prefix . Log::TABLE_IMAGE_CONVERSION;

		foreach ( $stats as $size => $file_size ) {

			foreach ( self::FORMATS as $format ) {
				$file_size = $wpdb->get_var(
					"SELECT " . Log::COL_FILE_SIZE . "
						FROM {$table_conv}
						WHERE " . Log::COL_ATTACH_ID . " = {$attach_id}
						AND " . Log::COL_IMAGE_SIZE . " = '{$size}'
						AND " . Log::COL_IMAGE_FORMAT . " = '{$format}'"
				);

				$wpdb->update(
					$table_name,
					array(
						self::COL_BYTES_AFTER => $file_size,
					),
					array(
						self::COL_ATTACH_ID    => $attach_id,
						self::COL_IMAGE_SIZE   => $size,
						self::COL_IMAGE_FORMAT => $format,
					)
				);
			}
		}
	}

	/**
	 * Find image stats row (attach_id - image_size) by ID
	 *
	 * @param int $id Stats table ID.
	 *
	 * @return array|null|object|void
	 */
	public static function find_stats_by_id( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_IMAGE_STATS;

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE `id` = %d", $id ) );
	}

	/**
	 * Find image stats row (attach_id - image_size) by ID
	 *
	 * @param int    $attach_id Stats table ID.
	 * @param string $image_size .
	 *
	 * @return array|null|object|void
	 */
	public static function find_stats( $attach_id, $image_size ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_IMAGE_STATS;

		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$table} WHERE `attach_id` = %d AND `image_size` = %s",
			$attach_id,
			$image_size
		) );
	}

	/**
	 * Get total attachment stats
	 *
	 * @param int $attach_id Attach ID.
	 *
	 * @return object
	 */
	public function get_total_attachment_stats( $attach_id ) {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE_IMAGE_STATS;
		$sql   = "SELECT
				" . self::COL_ATTACH_ID . " AS attach_id,
				sum( " . self::COL_BYTES_BEFORE . " ) as original,
				( SELECT sum( " . self::COL_BYTES_AFTER . " ) 
					FROM {$table} 
					WHERE " . self::COL_ATTACH_ID . " = {$attach_id} 
					AND " . self::COL_IMAGE_FORMAT . " = 'webp' ) AS webp,
				( SELECT sum( " . self::COL_BYTES_AFTER . " ) 
					FROM {$table} 
					WHERE " . self::COL_ATTACH_ID . " = {$attach_id} 
					AND " . self::COL_IMAGE_FORMAT . " = 'jp2' ) AS jp2,
				count( DISTINCT " . self::COL_IMAGE_SIZE . " ) AS count_images
			FROM {$table}
			WHERE " . self::COL_ATTACH_ID . " = {$attach_id}";

		$stats = $wpdb->get_row( $sql );

		return $this->calculate_stats( $stats );
	}

	/**
	 * Get single attachment stats
	 *
	 * @param int    $attach_id Attach ID.
	 * @param string $image_size Attachment size.
	 *
	 * @return object
	 */
	public function get_attachment_stats( $attach_id, $image_size ) {
		global $wpdb;

		$table_name = $wpdb->prefix . self::TABLE_IMAGE_STATS;
		$table_conv = $wpdb->prefix . Log::TABLE_IMAGE_CONVERSION;

		$sql = "
			SELECT
				sum(" . self::COL_BYTES_BEFORE . ") AS bytes_before,
				sum(" . self::COL_BYTES_AFTER . ") AS saving_size,
				round( ( sum(" . self::COL_BYTES_BEFORE . ") 
					- sum(" . self::COL_BYTES_AFTER . ") ) 
					/ sum(" . self::COL_BYTES_BEFORE . ") * 100, 2 ) AS percent,
				base." . self::COL_IMAGE_FORMAT . " AS format,
				conv.conversion_message AS message
			FROM {$table_name} AS base
			
			LEFT JOIN {$table_conv} AS conv
			ON conv." . self::COL_ATTACH_ID . " = {$attach_id}
			AND conv." . self::COL_IMAGE_SIZE . " = '{$image_size}'
			
			WHERE base." . self::COL_ATTACH_ID . " = {$attach_id}
			AND base." . self::COL_IMAGE_SIZE . " = '{$image_size}'
			GROUP BY base." . self::COL_IMAGE_FORMAT;

		return $wpdb->get_results( $sql, OBJECT );
	}

	/**
	 * Calculate stats
	 *
	 * @param object $stats .
	 *
	 * @return object
	 */
	protected function calculate_stats( $stats ) {
		$attach_stats = (object) [];
		$webp         = $stats->webp;
		$jp2          = $stats->jp2;

		if ( $webp > $stats->original ) {
			$webp = $stats->original;
		}

		if ( $jp2 > $stats->jp2 ) {
			$jp2 = $stats->original;
		}

		$average_size = ( $webp + $jp2 ) / 2;

		if ( is_numeric( $stats->original ) &&
			0 !== $stats->original &&
			0 !== $average_size &&
			$average_size < $stats->original
		) {
			$attach_stats->percent = round( ( ( $stats->original - $average_size ) / $stats->original ) * 100, 2 );
		} else {
			$attach_stats->percent = 0;
		}

		$attach_stats->saving_size  = $average_size;
		$attach_stats->count_images = $stats->count_images;
		$attach_stats->disk_usage   = $stats->original;

		return $attach_stats;
	}

	/**
	 * Get dashboard attachment stats
	 *
	 * @return object
	 */
	public function get_dashboard_attachment_stats() {
		global $wpdb;
		$table_name       = $wpdb->prefix . self::TABLE_IMAGE_STATS;
		$media_disk_usage = $this->get_images_disk_usage();

		if ( empty( $media_disk_usage ) ) {
			return new WP_Error( 'Check your hdd. It`s seems like it absent.' );
		}

		$dashboard_stats = (object) [
			'saving_size' => 0,
			'percent'     => 0,
		];

		$sql = "
			SELECT
				" . self::COL_BYTES_BEFORE . " AS bytes_before,
				" . self::COL_BYTES_AFTER . " AS bytes_after			
			FROM {$table_name}
			WHERE " . self::COL_BYTES_AFTER . " <> 0
			";

		$stats = $wpdb->get_results( $sql, OBJECT );

		if ( ! empty( $stats ) ) {
			$original  = 0;
			$pre_saved = 0;

			foreach ( $stats as $item ) {
				if ( $item->bytes_after > $item->bytes_before ) {
					$pre_saved += intval( $item->bytes_before );
					continue;
				}

				$pre_saved += intval( $item->bytes_after );
				$original  += intval( $item->bytes_before );
			}

			$dashboard_stats->saving_size = $pre_saved / 2;
			$dashboard_stats->percent     = round( ( $dashboard_stats->saving_size / $media_disk_usage ) * 100, 2 );
		}

		return $dashboard_stats;
	}

	/**
	 * Clear statistics after Regenerate Thumbnails
	 *
	 * @param int $attach_id Attachment ID.
	 */
	public function clean_statistics( $attach_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_STATS;
		$wpdb->delete(
			$table_name,
			array( self::COL_ATTACH_ID => $attach_id )
		);
		update_post_meta( $attach_id, '_just_img_opt_status', self::STATUS_IN_QUEUE );
	}

	/**
	 * Get filesize attachment in Kb
	 *
	 * @param string $attach_file Attachment file.
	 *
	 * @return float|int
	 */
	public function get_filesize( $attach_file ) {
		return filesize( $attach_file );
	}

	/**
	 * Get total|single filesizes attachments in bytes
	 *
	 * @param int  $id Attachment ID.
	 * @param bool $detailed For get total size = 'total' or sizes array = 'detailed'.
	 *
	 * @return int|array
	 */
	public function get_file_sizes( $id, $detailed = true ) {
		$wp_filesystem = Filesystem::instance();
		$total_size    = 0;
		$sizes_array   = array();
		$meta          = wp_get_attachment_metadata( $id );

		if ( ! $meta ) {
			return 0;
		}

		$image_dirs = pathinfo( $meta['file'] )['dirname'];
		$image_path = UPLOADS_ROOT . '/' . $image_dirs . '/';

		$sizes_array['full'] = filesize( $image_path . basename( $meta['file'] ) );

		foreach ( $meta['sizes'] as $size_key => $attachment ) {
			$image_size_file = $image_path . $attachment['file'];
			if ( $wp_filesystem->exists( $image_size_file ) ) {
				$sizes_array[ $size_key ] = filesize( $image_size_file );
			}
		}

		if ( $detailed ) {
			return $sizes_array;
		} else {
			foreach ( $sizes_array as $size ) {
				$total_size = $total_size + $size;
			}

			return $total_size;
		}
	}

	/**
	 * Get count additional sizes images
	 *
	 * @param int $id Attachment ID.
	 *
	 * @return float|null
	 */
	public function get_count_images( $id ) {
		$count        = 0;
		$files        = array();
		$get_metadata = wp_get_attachment_metadata( $id );
		if ( $get_metadata ) {
			foreach ( $get_metadata['sizes'] as $size ) {
				if ( ! isset( $size['file'] ) ) {
					continue;
				}
				$files[ $size['file'] ] = $size;
			}
			$count = count( $files );

			return $count;
		}

		return $count;
	}

	/**
	 * Get additional sizes images
	 *
	 * @return array
	 */
	public static function image_dimensions() {
		global $_wp_additional_image_sizes;
		$additional_sizes = get_intermediate_image_sizes();
		$sizes            = array();
		// Create the full array with sizes and crop info.
		foreach ( $additional_sizes as $_size ) {
			if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {
				$sizes[ $_size ]['width']  = get_option( $_size . '_size_w' );
				$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
				$sizes[ $_size ]['crop']   = (bool) get_option( $_size . '_crop' );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$sizes[ $_size ] = array(
					'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
				);
			}
		}
		// Medium Large.
		if ( ! isset( $sizes['medium_large'] ) || empty( $sizes['medium_large'] ) ) {
			$width  = intval( get_option( 'medium_large_size_w' ) );
			$height = intval( get_option( 'medium_large_size_h' ) );

			$sizes['medium_large'] = array(
				'width'  => $width,
				'height' => $height,
			);
		}

		return apply_filters( 'jio_settings_image_sizes', $sizes );

	}

	/**
	 * Get Count Images with status in_queue or count all images
	 *
	 * @param bool $all Check all images or in queue.
	 *
	 * @return int
	 */
	public function get_images_stat( $all = false ) {
		$args = $this->query_args;
		if ( false === $all ) {
			$args['meta_query'] = array(
				array(
					'key'   => '_just_img_opt_status',
					'value' => self::STATUS_IN_QUEUE,
				),
			);
		}
		$query = new \WP_Query( $args );

		return $query->post_count;
	}

	/**
	 * Get Total image size
	 *
	 * @return int
	 */
	public function get_images_disk_usage() {
		$disk_usage = 0;
		// Init ms-functions for get_dirsize.
		require_once ABSPATH . WPINC . '/ms-functions.php';
		$get_path = Filesystem::get_uploads_path();
		foreach ( $get_path as $path ) {
			$disk_usage = $disk_usage + get_dirsize( $path );
		}

		return $disk_usage;
	}

	/**
	 * Get count images with status processed
	 *
	 * @return int
	 */
	public function get_count_images_processed() {
		$args               = $args = $this->query_args;
		$args['meta_query'] = array(
			'relation' => 'OR',
			array(
				'key'   => '_just_img_opt_status',
				'value' => self::STATUS_PROCESSED,
			),
			array(
				'key'   => '_just_img_opt_status',
				'value' => self::STATUS_PARTIALY_PROCESSED,
			),
			array(
				'key'   => '_just_img_opt_status',
				'value' => self::STATUS_INAPPROPRIATE,
			),
		);

		$query = new \WP_Query( $args );

		return $query->post_count;
	}

	/**
	 * Get count images in queue from total count
	 *
	 * @return int|float
	 */
	public function get_in_queue_image_count() {
		return $this->get_images_stat( true ) - $this->get_count_images_processed();
	}

	/**
	 * Get saving sizes from space size
	 *
	 * @return int|float
	 */
	public function get_disk_space_size() {
		$total_size  = $this->get_images_disk_usage();
		$saving_size = $this->get_dashboard_attachment_stats();
		if ( ! empty( $saving_size->saving_size ) ) {
			$space_size = $total_size - $saving_size->saving_size;
		} else {
			$space_size = $total_size;
		}

		return $space_size;
	}

	/**
	 * Get size format without units
	 *
	 * @param int $bytes Size in bytes.
	 *
	 * @return array
	 */
	public function size_format_explode( $bytes ) {
		$size = array(
			'bytes' => $bytes,
			'unit'  => jio_size_format( $bytes ),
		);

		return $size;
	}

	/**
	 * Check size limit images optimization
	 *
	 * @param array $attach_ids Array attach ids.
	 *
	 * @return array Array attach ids.
	 */
	public function size_limit( array $attach_ids ) {
		$size_limit = 0;
		$size_array = array();
		$array_ids  = array();
		if ( 0 < \JustImageOptimizer::$settings->size_limit ) {
			foreach ( $attach_ids as $attach_id ) {
				$size_array[ $attach_id ] = $this->get_file_sizes( $attach_id, false );
			}
			foreach ( $attach_ids as $attach_id ) {
				if ( (int) number_format_i18n( $size_limit / 1048576 ) >= (int) \JustImageOptimizer::$settings->size_limit ) {
					return $array_ids;
				}
				$size_limit              = $size_limit + $size_array[ $attach_id ];
				$array_ids[ $attach_id ] = $attach_id;
			}
		}

		return $attach_ids;
	}

	/**
	 * Check optimization table and define what is the real optimization status.
	 *
	 * @param int $attach_id Attachment ID to check.
	 *
	 * @return int  real image optimization status
	 */
	public static function check_optimization_status( int $attach_id ) {
		global $wpdb;
		$table_stats       = $wpdb->prefix . self::TABLE_IMAGE_STATS;
		$table_log_details = $wpdb->prefix . Log::TABLE_IMAGE_LOG_DETAILS;

		$tried_images = $wpdb->get_var( $wpdb->prepare(
			"
			SELECT COUNT(`id`)
			FROM {$table_stats}
			WHERE `attach_id` = %d
			",
			$attach_id
		) );

		$failed_images = $wpdb->get_var( $wpdb->prepare(
			"
			SELECT COUNT(`id`)
			FROM {$table_stats}
			WHERE `attach_id` = %d
			    AND `bytes_after` = 0
			",
			$attach_id
		) );

		$inappropriate_images = $wpdb->get_var( $wpdb->prepare(
			"
			SELECT COUNT( DISTINCT `attach_id`)
			FROM {$table_log_details}
			WHERE `attach_id` = %d
				AND `status` = %s
			GROUP BY `attach_id`
			",
			$attach_id,
			'inappropriate'
		) );

		$status = static::STATUS_IN_QUEUE;
		if ( $tried_images ) {
			$status = static::STATUS_PROCESSED;
			if ( $failed_images ) {
				$status = static::STATUS_PARTIALY_PROCESSED;
			}

			if ( $inappropriate_images ) {
				$status = static::STATUS_INAPPROPRIATE;
			}
		}

		return $status;
	}

	/**
	 * Get queued image sizes, which are not optimized yet.
	 *
	 * @param int $attach_id Attachment ID to get image sizes in queue.
	 *
	 * @return string[] image sizes names
	 */
	public static function get_queued_image_sizes( int $attach_id ) {
		global $wpdb;
		$table_base_log = $wpdb->prefix . self::TABLE_IMAGE_STATS;

		$sql = "
			SELECT DISTINCT `image_size`
				FROM {$table_base_log}
				WHERE `attach_id` = %d
		";

		$queued_images = $wpdb->get_col( $wpdb->prepare( $sql, $attach_id ) );

		return $queued_images;
	}

	/**
	 * Reconvert_all
	 *
	 * @return bool|WP_Error
	 */
	public function reset_queue() {
		global $wpdb;
		$table_stats = $wpdb->prefix . self::TABLE_IMAGE_STATS;

		$updates = $wpdb->delete( $wpdb->postmeta,
			array( 'meta_key' => '_just_img_opt_status' )
		);

		if ( false === $updates ) {
			return new WP_Error( 'Error with reset optimizer queue.' );
		} elseif ( 0 !== $updates ) {
			$query = $wpdb->query( "TRUNCATE {$table_stats}" );

			if ( false === $query ) {
				return new WP_Error( 'Error with stats table clearing.' );
			}
		}

		return true;
	}
}
