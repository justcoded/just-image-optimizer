<?php

namespace JustCoded\WP\ImageOptimizer\models;

use JustCoded\WP\ImageOptimizer\core;
use JustCoded\WP\ImageOptimizer\models\Connect;

/**
 * Class Log
 *
 * Images Optimization Log
 */
class Log extends core\Model {

	const TABLE_IMAGE_LOG_DETAILS = 'image_optimize_log_details';
	const TABLE_IMAGE_LOG         = 'image_optimize_log';
	const TABLE_IMAGE_CONVERSION  = 'image_conversion_log';

	const FORMATS = [ 'webp', 'jp2' ];

	// Log main Table.
	const COL_REQUEST_ID  = 'request_id';
	const COL_SERVICE     = 'service';
	const COL_IMAGE_LIMIT = 'image_limit';
	const COL_SIZE_LIMIT  = 'size_limit';
	const COL_TIME        = 'time';
	const COL_END_TIME    = 'end_time';
	const COL_INFO        = 'info';

	// Log Details Table.
	const COL_TRY_ID       = 'request_id';
	const COL_ATTACH_ID    = 'attach_id';
	const COL_IMAGE_SIZE   = 'image_size';
	const COL_BYTES_BEFORE = 'bytes_before';
	const COL_BYTES_AFTER  = 'bytes_after';
	const COL_ATTACH_NAME  = 'attach_name';
	const COL_STATUS       = 'status';

	// Attachment statuses .
	const STATUS_PENDING       = 'pending';
	const STATUS_ABORTED       = 'aborted';
	const STATUS_OPTIMIZED     = 'optimized';
	const STATUS_REMOVED       = 'removed';
	const STATUS_PARTIALLY     = 'partially';
	const STATUS_CMYK          = 'cmyk';
	const STATUS_INAPPROPRIATE = 'inappropriate';
	const STATUS_NOT_EXISTS    = 'not_exists';

	// Log image conversion.
	const COL_IMAGE_FORMAT   = 'image_format';
	const COL_UPLOAD_PATH    = 'upload_path';
	const COL_CONVERTED_PATH = 'converted_path';
	const COL_CREATION_TIME  = 'creation_time';
	const COL_UPDATE_TIME    = 'update_time';
	const COL_FILE_SIZE      = 'file_size';

	const ITEMS_PER_PAGE = 20;

	/**
	 * Current running request ID.
	 *
	 * @var int
	 */
	public $request_id;

	/**
	 * Return status message based on status.
	 *
	 * @param string $status .
	 *
	 * @return string
	 */
	public static function get_status_message( $status ) {
		$statuses = array(
			'pending'       => 'Request sent',
			'aborted'       => 'Optimization aborted',
			'optimized'     => 'Optimized',
			'partially'     => 'Partially converted',
			'removed'       => 'Removed from service request',
			'cmyk'          => 'Inappropriate color profile',
			'inappropriate' => 'Inappropriate image type',
		);
		if ( isset( $statuses[ $status ] ) ) {
			return $statuses[ $status ];
		} else {
			return (string) $status;
		}
	}

	/**
	 * Save optimization request start
	 *
	 * @return int Request log ID.
	 */
	public function start_request() {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_LOG;
		$connect    = new Connect();
		$wpdb->insert(
			$table_name,
			array(
				self::COL_SERVICE     => $connect->service,
				self::COL_IMAGE_LIMIT => \JustImageOptimizer::$settings->image_limit,
				self::COL_SIZE_LIMIT  => \JustImageOptimizer::$settings->size_limit,
				self::COL_TIME        => current_time( 'mysql' ),
			)
		);

		$this->request_id = $wpdb->insert_id;

		return $this->request_id;
	}

	/**
	 * Store log info as multiline log
	 *
	 * @param string $line Line to add to request log info field.
	 *
	 * @return bool
	 */
	public function end_request( $line ) {
		global $wpdb;
		$table   = $wpdb->prefix . self::TABLE_IMAGE_LOG;
		$request = $this->find( $this->request_id );
		if ( ! $request ) {
			return false;
		}

		return $wpdb->update(
			$table,
			array(
				self::COL_INFO     => $request->info . "\n" . $line,
				self::COL_END_TIME => date( 'Y-m-d H:i:s', time() ),
			),
			array(
				self::COL_REQUEST_ID => $this->request_id,
			)
		);
	}

	/**
	 * Find log record
	 *
	 * @param int $request_id Request ID.
	 *
	 * @return object
	 */
	public function find( $request_id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_IMAGE_LOG;

		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$table} WHERE " . self::COL_REQUEST_ID . " = %d",
			$request_id
		) );
	}

	/**
	 * Save attachment stats before optimize
	 *
	 * @param int   $request_id Request log ID.
	 * @param int   $attach_id Attach ID.
	 * @param array $stats Array with stats attachments.
	 */
	public function save_details( $request_id, $attach_id, $stats ) {
		global $wpdb;
		$table_conv = $wpdb->prefix . self::TABLE_IMAGE_CONVERSION;
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_LOG_DETAILS;

		$settings      = \JustImageOptimizer::$settings;
		$not_optimized = Media::get_queued_image_sizes( $attach_id );

		foreach ( $stats as $size => $file_size ) {
			// skip image sizes which we do not optimize by settings or they are optimized already.
			if ( ! in_array( $size, $not_optimized, true )
				|| ( ! $settings->image_sizes_all && ! in_array( $size, $settings->image_sizes, true ) )
			) {
				continue;
			}

			$image_data  = image_get_intermediate_size( $attach_id, $size );
			$attach_name = ! empty( $image_data['file'] ) ? $image_data['file'] : basename( wp_get_attachment_metadata( $attach_id )['file'] );

			foreach ( self::FORMATS as $format ) {
				$file_size_converted = $wpdb->get_var(
					"SELECT " . Log::COL_FILE_SIZE . "
						FROM {$table_conv}
						WHERE " . Log::COL_ATTACH_ID . " = {$attach_id}
						AND " . Log::COL_IMAGE_SIZE . " = '{$size}'
						AND " . Log::COL_IMAGE_FORMAT . " = '{$format}'"
				);

				$wpdb->insert(
					$table_name,
					array(
						self::COL_ATTACH_ID    => $attach_id,
						self::COL_TRY_ID       => $request_id,
						self::COL_IMAGE_SIZE   => $size,
						self::COL_IMAGE_FORMAT => $format,
						self::COL_BYTES_BEFORE => $file_size,
						self::COL_BYTES_AFTER  => ! empty( $file_size_converted ) ? $file_size_converted : 0,
						self::COL_ATTACH_NAME  => $attach_name,
						self::COL_STATUS       => static::STATUS_PENDING,
					)
				);
			}
		}
	}

	/**
	 * Save specific file status
	 *
	 * @param int    $request_id Request ID.
	 * @param string $attach_name Attach name.
	 * @param string $status Log status.
	 */
	public function save_status( $request_id, $attach_name, $status ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_LOG_DETAILS;
		$wpdb->update(
			$table_name,
			array(
				self::COL_STATUS => $status,
			),
			array(
				self::COL_TRY_ID      => $request_id,
				self::COL_ATTACH_NAME => $attach_name,
			)
		);
	}

	/**
	 * Update specific file status
	 *
	 * @param int    $attach_id Attach ID.
	 * @param int    $request_id Request ID.
	 * @param string $status Log status .
	 */
	public function update_status( $attach_id, $request_id, $status ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_LOG_DETAILS;
		$wpdb->update(
			$table_name,
			array(
				self::COL_STATUS => $status,
			),
			array(
				self::COL_ATTACH_ID => $attach_id,
				self::COL_TRY_ID    => $request_id,
			)
		);
	}

	/**
	 * Update attachment stats after optimization
	 *
	 * @param int   $request_id Request ID.
	 * @param int   $attach_id Attach ID.
	 * @param array $stats Array with stats attachments.
	 */
	public function update_details( $request_id, $attach_id, $stats ) {
		global $wpdb;
		$table_conv = $wpdb->prefix . self::TABLE_IMAGE_CONVERSION;
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_LOG_DETAILS;

		foreach ( $stats as $size => $file_size ) {
			foreach ( self::FORMATS as $format ) {
				$file_size_converted = $wpdb->get_var(
					"SELECT " . Log::COL_FILE_SIZE . "
						FROM {$table_conv}
						WHERE " . Log::COL_ATTACH_ID . " = {$attach_id}
						AND " . Log::COL_IMAGE_SIZE . " = '{$size}'
						AND " . Log::COL_IMAGE_FORMAT . " = '{$format}'"
				);

				$wpdb->update(
					$table_name,
					array(
						self::COL_BYTES_AFTER => $file_size_converted,
					),
					array(
						self::COL_TRY_ID       => $request_id,
						self::COL_ATTACH_ID    => $attach_id,
						self::COL_IMAGE_SIZE   => $size,
						self::COL_IMAGE_FORMAT => $format,
					)
				);
			}
		}
	}

	/**
	 * Get log store data
	 *
	 * @return array
	 */
	public function get_requests() {
		global $wpdb;
		$log_store     = array();
		$table_log     = $wpdb->prefix . self::TABLE_IMAGE_LOG;
		$table_details = $wpdb->prefix . self::TABLE_IMAGE_LOG_DETAILS;

		$query = "SELECT
						store.*,
						COUNT(log.id) as total_count
					FROM " . $table_log . " AS store
					
					LEFT JOIN " . $table_details . " AS log
					ON log." . self::COL_TRY_ID . " = store." . self::COL_REQUEST_ID . "
					
					GROUP BY store." . self::COL_REQUEST_ID;

		$requests = $wpdb->get_results( $query . ' ORDER BY ' . self::COL_REQUEST_ID, ARRAY_A );

		foreach ( $requests as $request ) {
			$total = $request['total_count'];

			$converted = $wpdb->get_results(
				"SELECT COUNT( image_size ) as converted FROM {$table_details} WHERE `request_id` = '{$request['request_id']}' AND `bytes_after` <> 0"
			);

			$failed = $wpdb->get_results(
				"SELECT COUNT( image_size ) as failed FROM {$table_details} WHERE `request_id` = '{$request['request_id']}' AND `bytes_after` = 0"
			);

			$log_store[] = array(
				'request_id'      => $request['request_id'],
				'time'            => $request['time'],
				'end_time'        => $request['end_time'],
				'service'         => $request['service'],
				'image_limit'     => $request['image_limit'],
				'size_limit'      => $request['size_limit'],
				'total_count'     => $total,
				'converted_count' => $converted[0]->converted,
				'failed_count'    => $failed[0]->failed,
			);
		}

		return $log_store;
	}

	/**
	 * Get dashboard attachment stats
	 *
	 * @param int $request_id Request Log ID.
	 *
	 * @return array
	 */
	public function get_request_details( $request_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_LOG_DETAILS;
		$query      = 'SELECT * FROM ' . $table_name . '
							WHERE  ' . self::COL_TRY_ID . ' = ' . $request_id;

		$result = $wpdb->get_results( $query . ' ORDER BY id ASC ', ARRAY_A );

		return $result;
	}

	/**
	 * Get Attachments count by request
	 *
	 * @param int $try_id Store log ID.
	 *
	 * @return int
	 */
	public function attach_count( $try_id ) {
		global $wpdb;
		$table_name  = $wpdb->prefix . self::TABLE_IMAGE_LOG_DETAILS;
		$attach_stat = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT DISTINCT(" . self::COL_ATTACH_ID . ")
			FROM {$table_name} as log
			WHERE " . self::COL_TRY_ID . " = %d
			",
			$try_id
		) );

		return count( $attach_stat );
	}

	/**
	 * Get Attachment file count stats
	 *
	 * @param int    $try_id Store log ID.
	 * @param string $status Attachment optimize status.
	 *
	 * @return string
	 */
	public function files_count_stat( $try_id, $status ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_LOG_DETAILS;

		return $wpdb->get_var( $wpdb->prepare(
			"
			SELECT COUNT(log.id)
			FROM {$table_name} as log
			WHERE " . self::COL_TRY_ID . " = %d
			AND " . self::COL_STATUS . " = %s
			",
			$try_id,
			$status
		) );
	}
}
