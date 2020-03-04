<?php


namespace JustCoded\WP\ImageOptimizer\components;

use WP_List_Table;
use JustCoded\WP\ImageOptimizer\models\Log;

/**
 * Class DefaultTables
 *
 * @package JustCoded\WP\ImageOptimizer\components
 */
class DefaultTables extends WP_List_Table {

	/**
	 * Requests data.
	 *
	 * @var array $data
	 */
	protected $data;

	/**
	 * Table model.
	 *
	 * @var object $model .
	 */
	protected $model;

	/**
	 * Setup
	 *
	 * @param object $model .
	 * @param bool   $detailed .
	 * @param string $request_id .
	 */
	public function setup( $model, $detailed = false, $request_id = '' ) {
		$this->set_model( $model );

		if ( $detailed ) {
			$this->set_data( $model->get_request_details( $request_id ) );
		}

		if ( ! $detailed ) {
			$this->set_data( $model->get_requests() );
		}

		$this->prepare_items();
	}

	/**
	 * Set_data
	 *
	 * @param array $data .
	 *
	 * @return mixed
	 */
	public function set_data( $data ) {
		return $this->data = $data;
	}

	/**
	 * Set_model
	 *
	 * @param object $model .
	 *
	 * @return mixed
	 */
	public function set_model( $model ) {
		return $this->model = $model;
	}

	/**
	 * Prepare_items
	 */
	public function prepare_items() {
		$columns = $this->get_columns();

		$data = array_reverse( $this->table_data() );

		$per_page     = Log::ITEMS_PER_PAGE;
		$current_page = $this->get_pagenum();
		$total_items  = count( $data );
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
		) );
		$items = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->_column_headers = array( $columns );
		$this->items           = $items;
	}

	/**
	 * Get_columns
	 *
	 * @return array
	 */
	public function get_columns() {
		return array();
	}

	/**
	 * Table_data
	 *
	 * @return array
	 */
	protected function table_data() {
		return array();
	}

	/**
	 * No_items
	 */
	public function no_items() {
		esc_html_e( 'Log is empty.', \JustImageOptimizer::TEXTDOMAIN );
	}
}
