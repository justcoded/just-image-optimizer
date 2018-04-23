<?php

namespace JustCoded\WP\ImageOptimizer\models;

use JustCoded\WP\ImageOptimizer\core;

/**
 * Class Media
 *
 * Work with attachment images statistics
 */
class Media extends core\Model {

	const TABLE_IMAGE_STATS = 'image_optimize';

	const COL_ATTACH_ID    = 'attach_id';
	const COL_ATTACH_NAME  = 'attach_name';
	const COL_IMAGE_SIZE   = 'image_size';
	const COL_BYTES_BEFORE = 'bytes_before';
	const COL_BYTES_AFTER  = 'bytes_after';

	const STATUS_IN_QUEUE           = 1;
	const STATUS_IN_PROCESS         = 2;
	const STATUS_PROCESSED          = 3;
	const STATUS_PARTIALY_PROCESSED = 4;

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

		$exists = $wpdb->get_col( $wpdb->prepare(
			"SELECT " . self::COL_IMAGE_SIZE . " FROM $table_name WHERE " . self::COL_ATTACH_ID . " = %d",
			$attach_id
		));

		foreach ( $stats as $size => $file_size ) {
			$image_data = image_get_intermediate_size( $attach_id, $size );
			// check row exist first.
			if ( in_array( $size, $exists ) ) {
				continue;
			}
			$wpdb->insert(
				$table_name,
				array(
					self::COL_ATTACH_ID    => $attach_id,
					self::COL_ATTACH_NAME  => $image_data['file'],
					self::COL_IMAGE_SIZE   => $size,
					self::COL_BYTES_BEFORE => $file_size,
					self::COL_BYTES_AFTER  => $file_size,
				)
			);
		}
	}

	/**
	 * Update attachment stats after optimize
	 *
	 * @param int $attach_id Attach ID.
	 * @param array $stats Array with stats attachments.
	 */
	public function update_stats( $attach_id, $stats ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_STATS;
		foreach ( $stats as $size => $file_size ) {
			$wpdb->update(
				$table_name,
				array(
					self::COL_BYTES_AFTER => $file_size,
				),
				array(
					self::COL_ATTACH_ID  => $attach_id,
					self::COL_IMAGE_SIZE => $size,
				)
			);
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
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE `id` = %d", $id ) );
	}

	/**
	 * Find image stats row (attach_id - image_size) by ID
	 *
	 * @param int $id Stats table ID.
	 *
	 * @return array|null|object|void
	 */
	public static function find_stats( $attach_id, $image_size ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_IMAGE_STATS;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM $table WHERE `attach_id` = %d AND `image_size` = %s",
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
		$table_name  = $wpdb->prefix . self::TABLE_IMAGE_STATS;
		$total_stats = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT ( sum( " . self::COL_BYTES_BEFORE . " )
				   - sum( " . self::COL_BYTES_AFTER . " ) ) AS saving_size,
				   round( ( ( sum( " . self::COL_BYTES_BEFORE . " )
				   - sum( " . self::COL_BYTES_AFTER . " ) )
				   / sum( " . self::COL_BYTES_BEFORE . " ) * 100 ), 2 ) as percent,
				   sum( " . self::COL_BYTES_AFTER . " ) as disk_usage
			FROM (
				SELECT * FROM $table_name
				WHERE " . self::COL_ATTACH_ID . " = %s
				GROUP BY " . self::COL_ATTACH_NAME . "
			) io
			",
			$attach_id
		), OBJECT );

		return $total_stats;
	}

	/**
	 * Get single attachment stats
	 *
	 * @param int $attach_id Attach ID.
	 * @param string $size Attachment size.
	 *
	 * @return object
	 */
	public function get_attachment_stats( $attach_id, $size ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_STATS;
		$stats      = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT ( " . self::COL_BYTES_BEFORE . " - " . self::COL_BYTES_AFTER . " ) AS saving_size,
				   round( ( ( " . self::COL_BYTES_BEFORE . "
				   - " . self::COL_BYTES_AFTER . " )
				   / " . self::COL_BYTES_BEFORE . " * 100 ), 2 ) as percent
			FROM (
				SELECT * FROM $table_name
				WHERE " . self::COL_ATTACH_ID . " = %s
				AND " . self::COL_IMAGE_SIZE . " = %s
				GROUP BY " . self::COL_ATTACH_NAME . "
			) io",
			$attach_id,
			$size
		), OBJECT );

		return $stats;
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
		$dashboard_stats  = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT ( sum( " . self::COL_BYTES_BEFORE . " )
				   - sum( " . self::COL_BYTES_AFTER . " ) ) AS saving_size,
				   round( ( sum( " . self::COL_BYTES_BEFORE . " )
				   - sum( " . self::COL_BYTES_AFTER . " ) )
				   / %s * 100, 2 ) AS percent
			FROM (
				SELECT * FROM $table_name
				GROUP BY " . self::COL_ATTACH_NAME . "
			) io",
			$media_disk_usage
		), OBJECT );

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
	 * Get all date upload dir
	 *
	 * @return array
	 */
	public static function get_uploads_path() {
		$path = array();
		// TODO: (after launch) check this with multisite.
		foreach ( glob( wp_upload_dir()['basedir'] . '/*', GLOB_ONLYDIR ) as $upload ) {
			foreach ( glob( $upload . '/*', GLOB_ONLYDIR ) as $upload_dir ) {
				$path[] = $upload_dir;
			}
		}

		return $path;
	}

	/**
	 * Get filesize attachment in Kb
	 *
	 * @param string $attach_file Attachment file.
	 *
	 * @return float|int
	 */
	public function get_filesize( $attach_file ) {
		$attach_filesize = filesize( $attach_file );

		return $attach_filesize;
	}

	/**
	 * Get total|single filesizes attachments in bytes
	 *
	 * @param int $id Attachment ID.
	 * @param string $type For get total size = 'total' or sizes array = 'detailed'.
	 *
	 * @return int|float|boolean|array
	 */
	public function get_file_sizes( $id, $type = 'detailed' ) {
		global $wp_filesystem;
		WP_Filesystem();
		$total_size  = 0;
		$sizes_array = array();
		$meta = wp_get_attachment_metadata( $id );
		$get_path    = $this->get_uploads_path();
		if ( ! $meta ) {
			return 0;
		}

		foreach ( $meta['sizes'] as $size_key => $attachment ) {
			if( basename( $meta['file'] ) !== $attachment['file'] ) {
				foreach ( $get_path as $path ) {
					if ( $wp_filesystem->exists( $path . '/' . $attachment['file'] ) ) {
						$sizes_array[ $size_key ] = $this->get_filesize( $path . '/' . $attachment['file'] );
					}
				}
			}
		}
		foreach ( $sizes_array as $size ) {
			$total_size = $total_size + $size;
		}
		if ( 'detailed' === $type ) {
			return $sizes_array;
		} else {
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
					'value' => Media::STATUS_IN_QUEUE,
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
		require_once ABSPATH . WPINC .'/ms-functions.php';
		$get_path = self::get_uploads_path();
		foreach( $get_path as $path ) {
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
			array(
				'key'   => '_just_img_opt_status',
				'value' => Media::STATUS_PROCESSED,
			),
		);
		$query              = new \WP_Query( $args );

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
		if ( ! empty( $saving_size[0]->saving_size ) ) {
			$space_size = $total_size - $saving_size[0]->saving_size;
		} else {
			$space_size = $total_size;
		}

		return $space_size;
	}

	/**
	 * Get size format without units
	 *
	 * @param  int $bytes Size in bytes.
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
				$size_array[ $attach_id ] = $this->get_file_sizes( $attach_id, 'total' );
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
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_STATS;

		$tried_images = $wpdb->get_var( $wpdb->prepare(
			"
			SELECT COUNT(`id`)
			FROM $table_name
			WHERE `attach_id` = %d
			",
			$attach_id
		) );
		
		$failed_images = $wpdb->get_var( $wpdb->prepare(
			"
			SELECT COUNT(`id`)
			FROM $table_name
			WHERE `attach_id` = %d
				AND `bytes_before` <= `bytes_after`
			",
			$attach_id
		) );
		
		$status = static::STATUS_IN_QUEUE;
		if ( $tried_images ) {
			$status = static::STATUS_PROCESSED;
			if ( $failed_images ) {
				$status = static::STATUS_PARTIALY_PROCESSED;
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
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_STATS;
		$queued_images = $wpdb->get_col( $wpdb->prepare(
			"
			SELECT image_size
			FROM $table_name
			WHERE `attach_id` = %d
				AND `bytes_before` <= `bytes_after`
			",
			$attach_id
		) );
		return $queued_images;
	}

}
