<?php


namespace JustCoded\WP\ImageOptimizer\models;

use JustCoded\WP\ImageOptimizer\components\DefaultTables;
use JustImageOptimizer;

/**
 * Class DetailedLogTable
 *
 * @package JustCoded\WP\ImageOptimizer\models
 */
class DetailedLogTable extends DefaultTables {

	/**
	 * Get_columns
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'attachment_id' => __( 'Attachment ID', JustImageOptimizer::TEXTDOMAIN ),
			'size'          => __( 'Size', JustImageOptimizer::TEXTDOMAIN ),
			'filename'      => __( 'File Name', JustImageOptimizer::TEXTDOMAIN ),
			'image_format'  => __( 'Image Format', JustImageOptimizer::TEXTDOMAIN ),
			'bytes_before'  => __( 'Size Before', JustImageOptimizer::TEXTDOMAIN ),
			'bytes_after'   => __( 'Size After', JustImageOptimizer::TEXTDOMAIN ),
			'status'        => __( 'Status', JustImageOptimizer::TEXTDOMAIN ),
		);
	}

	/**
	 * Column_default
	 *
	 * @param object $item .
	 * @param string $column_name .
	 *
	 * @return string|true|void
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'attachment_id':
			case 'size':
			case 'filename':
			case 'image_format':
			case 'bytes_before':
			case 'bytes_after':
			case 'status':
				return $item[ $column_name ];
			default:
				return '';
		}
	}

	/**
	 * Table_data
	 *
	 * @return array
	 */
	public function table_data() {
		$data = array();

		foreach ( $this->data as $row ) {
			$data[] = array(
				'attachment_id' => $row[ Log::COL_ATTACH_ID ],
				'size'          => $row[ Log::COL_IMAGE_SIZE ],
				'filename'      => $row[ Log::COL_ATTACH_NAME ],
				'image_format'  => $row[ Log::COL_IMAGE_FORMAT ],
				'bytes_before'  => jio_size_format( $row[ Log::COL_BYTES_BEFORE ] ),
				'bytes_after'   => jio_size_format( $row[ Log::COL_BYTES_AFTER ] ),
				'status'        => Log::get_status_message( $row[ Log::COL_STATUS ] ),
			);
		}

		return $data;
	}

}
