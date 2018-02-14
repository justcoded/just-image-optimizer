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

	const TABLE_IMAGE_LOG = 'image_optimization_log';
	const TABLE_IMAGE_LOG_STORE = 'image_optimization_log_store';
	//Log Table
	const COL_ATTACH_ID = 'attach_id';
	const COL_TRY_ID = 'try_id';
	const COL_IMAGE_SIZE = 'image_size';
	const COL_BYTES_BEFORE = 'bytes_before';
	const COL_BYTES_AFTER = 'bytes_after';
	const COL_ATTACH_NAME = 'attach_name';
	const COL_STATUS = 'status';
	//Log Store Table
	const COL_STORE_ID = 'store_id';
	const COL_SERVICE = 'service';
	const COL_IMAGE_LIMIT = 'image_limit';
	const COL_SIZE_LIMIT = 'size_limit';
	const COL_TIME = 'time';

	//Optimized status with message
	public $status = array(
		'aborted'   => 'Optimization aborted. Image was 25x25.',
		'optimized' => 'Optimized',
		'removed'   => 'Removed from service request.',
	);

	// TODO: refactor names (@AP).
	/**
	 * Save attachment stats before optimize
	 *
	 * @param int $attach_id Attach ID.
	 * @param array $stats Array with stats attachments.
	 */
	public function save_log( $attach_id, $stats, $store_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_LOG;

		foreach ( $stats as $size => $file_size ) {
			$image_data = image_get_intermediate_size( $attach_id, $size );
			$wpdb->insert(
				$table_name,
				array(
					self::COL_ATTACH_ID    => $attach_id,
					self::COL_TRY_ID       => $store_id,
					self::COL_IMAGE_SIZE   => $size,
					self::COL_BYTES_BEFORE => $file_size,
					self::COL_ATTACH_NAME  => $image_data['file'],
					self::COL_STATUS       => $this->status['removed'],
				)
			);
		}
	}

	/**
	 * Save Log Store
	 */
	public function save_log_store() {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_LOG_STORE;
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

		return $wpdb->insert_id;
	}

	/**
	 * Save optimized status
	 *
	 * @param \string $attach_name Attach name.
	 */
	public function save_status( $attach_name, $status ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_LOG;
		$wpdb->update(
			$table_name,
			array(
				self::COL_STATUS => $status,
			),
			array(
				self::COL_ATTACH_NAME => $attach_name,
			)
		);
	}

	/**
	 * Update attachment stats after optimize
	 *
	 * @param int $attach_id Attach ID.
	 * @param array $stats Array with stats attachments.
	 */
	public function update_log( $attach_id, $stats ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_LOG;
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
	 * Clear log after Regenerate Thumbnails
	 *
	 * @param int $attach_id Attachment ID.
	 */
	public function clean_log( $attach_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_LOG;
		$wpdb->delete(
			$table_name,
			array( self::COL_ATTACH_ID => $attach_id )
		);
	}

	/**
	 * Get log store data
	 *
	 * @return array
	 */
	public function get_log_store() {
		global $wpdb;
		$table_name     = $wpdb->prefix . self::TABLE_IMAGE_LOG_STORE;
		$table_name2    = $wpdb->prefix . self::TABLE_IMAGE_LOG;
		$result         = array();
		$items_per_page = 15;
		$page           = isset( $_GET['store'] ) ? abs( (int) $_GET['store'] ) : 1;
		$offset         = ( $page * $items_per_page ) - $items_per_page;
		$query          = 'SELECT store.*, sum(log.' . self::COL_BYTES_BEFORE . ' - log.' . self::COL_BYTES_AFTER . ') as total_save,
							COUNT(log.id) as total_count
							FROM ' . $table_name . ' AS store
							INNER JOIN ' . $table_name2 . ' AS log
							ON log.' . self::COL_TRY_ID . ' = store.' . self::COL_STORE_ID . '
							GROUP BY store.' . self::COL_STORE_ID . '
							';
		$total_query    = "SELECT COUNT(1) FROM (${query}) AS total_log";
		$total          = $wpdb->get_var( $total_query );

		$log_store = $wpdb->get_results( $query . ' ORDER BY ' . self::COL_STORE_ID . ' DESC LIMIT ' . $offset . ', ' . $items_per_page, ARRAY_A );

		$pagination = paginate_links( array(
			'base'      => add_query_arg( 'store', '%#%' ),
			'format'    => '',
			'prev_text' => __( '&laquo;' ),
			'next_text' => __( '&raquo;' ),
			'total'     => ceil( $total / $items_per_page ),
			'current'   => $page,
		) );
		$result     = array(
			'log_store'  => $log_store,
			'pagination' => $pagination,
		);

		return $result;
	}

	/**
	 * Get Attachment count stats
	 *
	 * @param int $try_id Store log ID.
	 * @param string $status Attachment optimize status.
	 *
	 * @return array
	 */
	public function attach_count_stat( $try_id, $status ) {
		global $wpdb;
		$table_name  = $wpdb->prefix . self::TABLE_IMAGE_LOG;
		$attach_stat = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT COUNT(log.id) as stat
			FROM $table_name as log
			WHERE " . self::COL_TRY_ID . " = %s
			AND " . self::COL_STATUS . " = %s
			",
			$try_id,
			$this->status[ $status ]
		), ARRAY_A );

		return $attach_stat;
	}

	/**
	 * Get dashboard attachment stats
	 *
	 * @param int $store_id Store Log ID.
	 *
	 * @return array
	 */
	public function get_log( $store_id ) {
		global $wpdb;
		$table_name     = $wpdb->prefix . self::TABLE_IMAGE_LOG;
		$result         = array();
		$items_per_page = 15;
		$page           = isset( $_GET['log'] ) ? abs( (int) $_GET['log'] ) : 1;
		$offset         = ( $page * $items_per_page ) - $items_per_page;
		$query          = 'SELECT * FROM ' . $table_name . '
							WHERE  ' . self::COL_TRY_ID . ' = ' . $store_id;
		$total_query    = "SELECT COUNT(1) FROM (${query}) AS total_log";
		$total          = $wpdb->get_var( $total_query );

		$log = $wpdb->get_results( $query . ' ORDER BY id DESC LIMIT ' . $offset . ', ' . $items_per_page, ARRAY_A );

		$pagination = paginate_links( array(
			'base'      => add_query_arg( 'log', '%#%' ),
			'format'    => '',
			'prev_text' => __( '&laquo;' ),
			'next_text' => __( '&raquo;' ),
			'total'     => ceil( $total / $items_per_page ),
			'current'   => $page,
		) );
		$result     = array(
			'log'        => $log,
			'pagination' => $pagination,
		);

		return $result;
	}
}