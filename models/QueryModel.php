<?php


namespace JustCoded\WP\ImageOptimizer\models;

use JustCoded\WP\ImageOptimizer\controllers\ServiceController;

/**
 * Class QueryModel
 *
 * @package JustCoded\WP\ImageOptimizer\models
 */
class QueryModel {

	/**
	 * Query data
	 *
	 * @var array $query_data
	 */
	public $query_data = array(
		'get'  => array(),
		'post' => array(),
	);

	/**
	 * Query vars
	 *
	 * @var array $query_vars
	 */
	public $query_vars = array(
		'imgzr-amount',
		'imgzr-replace',
		'imgzr-lazy',
		'tab',
		'keep',
		'type',
		'quality',
	);

	/**
	 * Im_query_vars
	 *
	 * @return array
	 */
	public function im_query_vars() {
		$this->query_vars_post( $this->query_vars, $this->query_data );
		$this->query_vars_get( $this->query_vars, $this->query_data );

		return $this->query_data;
	}

	/**
	 * Query_vars_post
	 *
	 * @param array $query_vars .
	 * @param array $query_data .
	 *
	 * @return mixed
	 */
	public function query_vars_post( $query_vars, &$query_data ) {
		if ( isset( $_POST['options_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_POST['options_nonce'], '_options' ) ) {
				return $query_data;
			}
		}

		if ( empty( $_POST ) ) {
			return $query_data;
		}

		foreach ( $query_vars as $query_var ) {
			if ( empty( $_POST[ $query_var ] ) ) {
				continue;
			}

			$query_data['post'][ $query_var ] = $_POST[ $query_var ];
		}

		return $query_data;
	}

	/**
	 * Query_vars_get
	 *
	 * @param array $query_vars .
	 * @param array $query_data .
	 *
	 * @return array
	 */
	public function query_vars_get( $query_vars, &$query_data ) {
		if ( isset( $_GET['tabs_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_GET['tabs_nonce'], '_tabs' ) ) {
				return $query_data;
			}
		}

		if ( empty( $_GET ) ) {
			return $query_data;
		}

		foreach ( $query_vars as $query_var ) {
			if ( empty( $_GET[ $query_var ] ) ) {
				continue;
			}

			$query_data['get'][ $query_var ] = $_GET[ $query_var ];
		}

		return $query_data;
	}

	/**
	 * Class destructor
	 */
	public function __destruct() {}
}
