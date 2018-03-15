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

if ( ! function_exists( 'jio_size_format' ) ) {
	/**
	 * Print file size friendly number with 1 decimal for MB/GB and 0 decimals for KB.
	 *
	 * @param int $bytes Bytes size.
	 *
	 * @return string Friendly file size.
	 */
	function jio_size_format( $bytes ) {
		$size = size_format( $bytes, 1 );
		if ( false !== strpos( $size, 'KB' ) ) {
			$size = size_format( $bytes, 0 );
		}
		return $size;
	}
}
