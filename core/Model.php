<?php

namespace JustCoded\WP\ImageOptimizer\core;

class Model {

	/**
	 * Ability to sanituze the input
	 *
	 * @var array
	 */
	protected $sanitize = array();

	/**
	 * Set request params
	 *
	 * @param array $params Load input data into model properties.
	 *
	 * @return boolean
	 */
	public function load( $params ) {
		if ( ! empty( $params ) ) {
			$this->set_attributes( $params );

			return true;
		}

		return false;
	}

	/**
	 * Set attributes to model
	 *
	 * @param array $params Input data.
	 */
	public function set_attributes( $params ) {
		$self = get_class( $this );
		foreach ( $params as $key => $value ) {
			if ( property_exists( $self, $key ) ) {
				$this->$key = $this->sanitize_attribute( $key, $value );
			}
		}
	}

	/**
	 * Sanitize input to be sure it's safe
	 *
	 * @param string $attr  Attribute.
	 * @param mixed  $value Input data.
	 *
	 * @return array|mixed
	 */
	public function sanitize_attribute( $attr, $value ) {
		$attr_key = is_array( $value ) ? "$attr.*" : $attr;

		$sanitize_func = 'sanitize_text_field';
		if ( ! empty( $this->sanitize[ $attr_key ] ) ) {
			$sanitize_func = 'sanitize_' . $this->sanitize[ $attr_key ];
		}

		if ( is_array( $value ) ) {
			$safe_value = array();
			foreach ( $value as $key => $val ) {
				$safe_value[ $key ] = call_user_func_array( $sanitize_func, array( $val ) );
			}
		} else {
			$safe_value = call_user_func_array( $sanitize_func, array( $value ) );
		}

		return $safe_value;
	}

}
