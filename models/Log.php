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
	const TABLE_IMAGE_LOG = 'image_optimize_log';
	//Log main Table
	const COL_REQUEST_ID = 'request_id';
	const COL_SERVICE = 'service';
	const COL_IMAGE_LIMIT = 'image_limit';
	const COL_SIZE_LIMIT = 'size_limit';
	const COL_TIME = 'time';
	const COL_INFO = 'info';
	//Log Details Table
	const COL_TRY_ID = 'request_id';
	const COL_ATTACH_ID = 'attach_id';
	const COL_IMAGE_SIZE = 'image_size';
	const COL_BYTES_BEFORE = 'bytes_before';
	const COL_BYTES_AFTER = 'bytes_after';
	const COL_ATTACH_NAME = 'attach_name';
	const COL_STATUS = 'status';

	const STATUS_PENDING = 'pending';
	const STATUS_ABORTED = 'aborted';
	const STATUS_OPTIMIZED = 'optimized';
	const STATUS_REMOVED = 'removed';

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
	 * @param string $status
	 *
	 * @return string
	 */
	public function get_status_message( $status ) {
		$statuses = array(
			'pending'   => 'Request sent',
			'aborted'   => 'Optimization aborted. Image was 25x25',
			'optimized' => 'Optimized',
			'removed'   => 'Removed from service request',
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
	public function update_info( $line ) {
		global $wpdb;
		$table   = $wpdb->prefix . self::TABLE_IMAGE_LOG;
		$request = $this->find( $this->request_id );
		if ( ! $request ) {
			return false;
		}
		return $wpdb->update(
			$table,
			array(
				self::COL_INFO => $request->info . "\n" . $line,
			),
			array(
				self::COL_REQUEST_ID => $this->request_id,
			)
		);
	}

	/**
	 * Find log record
	 *
	 * @param int $request_id Request ID
	 *
	 * @return object
	 */
	public function find( $request_id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_IMAGE_LOG;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM $table WHERE " . self::COL_REQUEST_ID . " = %d",
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

			$image_data = image_get_intermediate_size( $attach_id, $size );

			$wpdb->insert(
				$table_name,
				array(
					self::COL_ATTACH_ID    => $attach_id,
					self::COL_TRY_ID       => $request_id,
					self::COL_IMAGE_SIZE   => $size,
					self::COL_BYTES_BEFORE => $file_size,
					self::COL_BYTES_AFTER  => $file_size,
					self::COL_ATTACH_NAME  => $image_data['file'],
					self::COL_STATUS       => static::STATUS_PENDING,
				)
			);
		}
	}

	/**
	 * Save specific file status
	 *
	 * @param int    $request_id Request ID.
	 * @param string $attach_name Attach name.
	 * @param string $status Log status
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
	 * @param string $status Log status
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
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_LOG_DETAILS;
		foreach ( $stats as $size => $file_size ) {
			$wpdb->update(
				$table_name,
				array(
					self::COL_BYTES_AFTER => $file_size,
				),
				array(
					self::COL_TRY_ID     => $request_id,
					self::COL_ATTACH_ID  => $attach_id,
					self::COL_IMAGE_SIZE => $size,
				)
			);
		}
	}

	/**
	 * Get log store data
	 *
	 * @return array
	 */
	public function get_requests() {
		global $wpdb;
		$table_name     = $wpdb->prefix . self::TABLE_IMAGE_LOG;
		$table_name2    = $wpdb->prefix . self::TABLE_IMAGE_LOG_DETAILS;
		$result         = array();
		$items_per_page = self::ITEMS_PER_PAGE;
		$page           = isset( $_GET['offset'] ) ? abs( (int) $_GET['offset'] ) : 1;
		$offset         = ( $page * $items_per_page ) - $items_per_page;
		$query          = 'SELECT store.*, sum(log.' . self::COL_BYTES_BEFORE . ' - log.' . self::COL_BYTES_AFTER . ') as total_save,
								COUNT(log.id) as total_count
							FROM ' . $table_name . ' AS store
							LEFT JOIN ' . $table_name2 . ' AS log
							ON log.' . self::COL_TRY_ID . ' = store.' . self::COL_REQUEST_ID . '
							GROUP BY store.' . self::COL_REQUEST_ID . '
							';
		$total_query    = "SELECT COUNT(1) FROM (${query}) AS total_log";
		$total          = $wpdb->get_var( $total_query );

		$log_store = $wpdb->get_results( $query . ' ORDER BY ' . self::COL_REQUEST_ID . ' DESC LIMIT ' . $offset . ', ' . $items_per_page, ARRAY_A );

		$pagination = array(
			'base'      => add_query_arg( 'offset', '%#%' ),
			'format'    => '',
			'prev_text' => __( '&laquo;' ),
			'next_text' => __( '&raquo;' ),
			'total'     => ceil( $total / $items_per_page ),
			'current'   => $page,
		);
		$result     = array(
			'rows'       => $log_store,
			'pagination' => $pagination,
		);

		return $result;
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
	 * @return array
	 */
	public function attach_count( $try_id ) {
		global $wpdb;
		$table_name  = $wpdb->prefix . self::TABLE_IMAGE_LOG_DETAILS;
		$attach_stat = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT DISTINCT(" . self::COL_ATTACH_ID . ")
			FROM $table_name as log
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
	 * @return array
	 */
	public function files_count_stat( $try_id, $status ) {
		global $wpdb;
		$table_name  = $wpdb->prefix . self::TABLE_IMAGE_LOG_DETAILS;
		$attach_stat = $wpdb->get_var( $wpdb->prepare(
			"
			SELECT COUNT(log.id)
			FROM $table_name as log
			WHERE " . self::COL_TRY_ID . " = %d
			AND " . self::COL_STATUS . " = %s
			",
			$try_id,
			$status
		) );

		return $attach_stat;
	}
}