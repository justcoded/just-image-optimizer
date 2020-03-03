<?php


namespace JustCoded\WP\ImageOptimizer\components;

/**
 * Class QueryVars
 *
 * @package JustCoded\WP\ImageOptimizer\components
 */
class QueryVars {

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
	protected $query_vars = array();

	/**
	 * Jio_get_query_vars
	 *
	 * @param string $method .
	 * @param string $var .
	 *
	 * @return mixed
	 */
	public function get_query_vars( $method = 'post', $var = '' ) {
		if ( ! empty( $var ) && isset( $this->query_data[ $method ][ $var ] ) ) {
			return $this->query_data[ $method ][ $var ];
		}

		return $this->query_data[ $method ];
	}

	/**
	 * Jio_add_dynamic_query_vars
	 *
	 * @param string $var .
	 *
	 * @return array
	 */
	public function add_dynamic_query_var( $var ) {
		if ( ! empty( $var ) && ! in_array( $var, $this->query_vars, true ) ) {
			$this->query_vars[] = $var;
		}

		return $this->verify_query_vars( $this->query_vars, $this->query_data );
	}

	/**
	 * Query_vars_post
	 *
	 * @param array $query_vars .
	 * @param array $query_data .
	 *
	 * @return mixed
	 */
	protected function verify_query_vars( $query_vars, &$query_data ) {
		$methods = array(
			'post' => $_POST,
			'get'  => $_GET,
		);

		foreach ( $methods as $method => $_vars ) {
			if ( isset( $_vars['options_nonce'] ) ) {
				if ( ! wp_verify_nonce( $_vars['options_nonce'], '_options' ) ) {
					return $query_data;
				}
			}

			if ( empty( $_vars ) ) {
				return $query_data;
			}

			foreach ( $query_vars as $query_var ) {
				if ( empty( $_vars[ $query_var ] ) ) {
					continue;
				}

				$query_data[ $method ][ $query_var ] = $_vars[ $query_var ];
			}
		}

		return $query_data;
	}

}
