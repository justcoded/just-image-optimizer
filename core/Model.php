<?php

namespace JustCoded\WP\ImageOptimizer\core;

class Model {

	/**
	 * Set request params
	 *
	 * @param array $params
	 *
	 * @return boolean
	 */
	public function load( $params ) {
		if ( ! empty( $params ) ) {
			$this->setAttributes( $params );

			return true;
		}

		return false;
	}

	/**
	 * Set attributes to model
	 *
	 * @param array $params
	 */
	public function setAttributes( $params ) {
		$self = get_class( $this );
		foreach ( $params as $key => $value ) {
			if ( property_exists( $self, $key ) ) {
				$this->$key = is_array( $value ) ? $value : strip_tags( trim( $value ) );
			}
		}
	}
}