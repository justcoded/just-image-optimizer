<?php

if ( ! function_exists( 'sanitize_int' ) ) {
	/**
	 * Sanitize input as integer
	 *
	 * @param int $number Raw input.
	 *
	 * @return int Validated int.
	 */
	function sanitize_int( $number ) {
		return (int) $number;
	}
}

if ( ! function_exists( 'sanitize_float' ) ) {
	/**
	 * Sanitize input as integer
	 *
	 * @param int $number Raw input.
	 *
	 * @return int Validated int.
	 */
	function sanitize_float( $number ) {
		return (float) $number;
	}
}
