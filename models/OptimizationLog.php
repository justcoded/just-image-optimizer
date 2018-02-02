<?php

namespace JustCoded\WP\ImageOptimizer\models;

use JustCoded\WP\ImageOptimizer\core;

/**
 * Class OptimizationLog
 *
 * Images Optimization Log
 */
class OptimizationLog extends core\Model {

	const TABLE_IMAGE_LOG = 'image_optimization_log';

	const DB_ATTACH_ID = 'attach_id';
	const DB_SIZE = 'size';
	const DB_B_FILE_SIZE = 'b_file_size';
	const DB_A_FILE_SIZE = 'a_file_size';
	const DB_ATTACH_NAME = 'attach_name';
	const DB_FAIL = 'fail';
	const DB_TIME = 'time';

	/**
	 * Save attachment stats before optimize
	 *
	 * @param int   $attach_id Attach ID.
	 * @param array $stats Array with stats attachments.
	 */
	public function save_log( $attach_id, $stats ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_LOG;

		foreach ( $stats as $size => $file_size ) {
			$image_data = image_get_intermediate_size( $attach_id, $size );
			$wpdb->insert(
				$table_name,
				array(
					self::DB_ATTACH_ID   => $attach_id,
					self::DB_SIZE        => $size,
					self::DB_B_FILE_SIZE => $file_size,
					self::DB_ATTACH_NAME => $image_data['file'],
					self::DB_TIME        => current_time( 'mysql' ),
				)
			);
		}
	}

	/**
	 * Check if optimize fail
	 *
	 * @param \string $attach_name Attach name.
	 */
	public function save_fail( $attach_name ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_LOG;
		$wpdb->update(
			$table_name,
			array(
				self::DB_FAIL => 'Optimization aborted. Image was 25x25.',
			),
			array(
				self::DB_ATTACH_NAME => $attach_name,
			)
		);
	}

	/**
	 * Update attachment stats after optimize
	 *
	 * @param int   $attach_id Attach ID.
	 * @param array $stats Array with stats attachments.
	 */
	public function update_log( $attach_id, $stats ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_IMAGE_LOG;
		foreach ( $stats as $size => $file_size ) {
			$wpdb->update(
				$table_name,
				array(
					self::DB_A_FILE_SIZE => $file_size,
				),
				array(
					self::DB_ATTACH_ID => $attach_id,
					self::DB_SIZE      => $size,
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
			array( self::DB_ATTACH_ID => $attach_id )
		);
	}

	/**
	 * Get dashboard attachment stats
	 *
	 * @return object
	 */
	public function get_log() {
		global $wpdb;
		$table_name       = $wpdb->prefix . self::TABLE_IMAGE_LOG;
		$log  = $wpdb->get_results(
			"
			SELECT *
			FROM $table_name
			ORDER BY id DESC
			"
			, OBJECT );

		return $log;
	}
}