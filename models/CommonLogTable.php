<?php


namespace JustCoded\WP\ImageOptimizer\models;

use JustImageOptimizer;
use JustCoded\WP\ImageOptimizer\components\DefaultTables;

/**
 * Class LogTables
 *
 * @package JustCoded\WP\ImageOptimizer\models
 */
class CommonLogTable extends DefaultTables {

	/**
	 * Get_columns
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'request_id'      => __( 'Log ID', JustImageOptimizer::TEXTDOMAIN ),
			'time'            => __( 'Start time', JustImageOptimizer::TEXTDOMAIN ),
			'end_time'        => __( 'End time', JustImageOptimizer::TEXTDOMAIN ),
			'service'         => __( 'Service', JustImageOptimizer::TEXTDOMAIN ),
			'image_limit'     => __( 'Image/Size Limits', JustImageOptimizer::TEXTDOMAIN ),
			'total_count'     => __( 'Attachments', JustImageOptimizer::TEXTDOMAIN ),
			'size_limit'      => __( 'Img Sizes', JustImageOptimizer::TEXTDOMAIN ),
			'converted_count' => __( 'Converted/Failed', JustImageOptimizer::TEXTDOMAIN ),
		);
	}

	/**
	 * Table_data
	 *
	 * @return array
	 */
	protected function table_data() {
		$data = array();

		foreach ( $this->data as $row ) {
			$data[] = array(
				'request_id'      => '<a href="' . admin_url( 'upload.php?page=just-img-opt-log&request_id=' . $row['request_id'] ) . '">' . esc_html( $row['request_id'] ) . '</a>',
				'time'            => $row[ Log::COL_TIME ],
				'end_time'        => $row[ Log::COL_END_TIME ],
				'service'         => $row[ Log::COL_SERVICE ],
				'image_limit'     => $row[ Log::COL_IMAGE_LIMIT ] . ' attachm. / ' . $row[ Log::COL_SIZE_LIMIT ] . 'MB',
				'total_count'     => $this->model->attach_count( $row[ Log::COL_REQUEST_ID ] ),
				'size_limit'      => ! empty( $row['total_count'] ) ? $row['total_count'] : 0,
				'converted_count' => $row['converted_count'] . ' / <span class="text-danger">' . $row['failed_count'] . '</span>',
			);
		}

		return $data;
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
			case 'request_id':
			case 'time':
			case 'end_time':
			case 'service':
			case 'image_limit':
			case 'total_count':
			case 'size_limit':
			case 'converted_count':
				return $item[ $column_name ];
			default:
				return '';
		}
	}

}
