<?php


namespace JustCoded\WP\ImageOptimizer\components;

use JustCoded\WP\ImageOptimizer\models\Log;
use JustCoded\WP\ImageOptimizer\models\Media;

/**
 * Class Replacement
 *
 * @package JustCoded\WP\ImageOptimizer\components
 */
class Replacement {

	/**
	 * Replacement constructor.
	 */
	public function __construct() {
		add_filter( 'wp_head', array( $this, 'start_buffer' ) );
		add_filter( 'wp_footer', array( $this, 'end_buffer' ) );
	}

	/**
	 * Start_buffer
	 */
	public function start_buffer() {
		ob_start( array( $this, 'alternate_urls' ) );
	}

	/**
	 * End_buffer
	 */
	public function end_buffer() {
		ob_end_flush();
	}

	/**
	 * Alternate_urls
	 *
	 * @param string $buffer .
	 *
	 * @return string
	 */
	public function alternate_urls( &$buffer ) {
		global $wpdb, $is_chrome, $is_safari;

		$table = $wpdb->prefix . Log::TABLE_IMAGE_CONVERSION;

		if ( false === $is_chrome && false === $is_safari ) {
			return $buffer;
		}

		if ( true === $is_safari ) {
			$extension = 'jp2';
		} elseif ( true === $is_chrome ) {
			$extension = 'webp';
		}

		preg_match_all( '/' . preg_quote( UPLOADS_URL, '/' ) . '([^\s\"]*)/', $buffer, $srcs );

		foreach ( $srcs[1] as $src ) {
			$src = trim( $src, '/' );

			$match = $wpdb->get_var( "
				SELECT " . Log::COL_CONVERTED_PATH . "
				FROM {$table}
				WHERE " . Log::COL_UPLOAD_PATH . " = '{$src}'
				AND " . Log::COL_IMAGE_FORMAT . " = '{$extension}'
				AND " . Log::COL_STATUS . " = 1
			" );

			if ( empty( $match ) ) {
				continue;
			}

			$pattern = '/' . preg_quote( $src, '/' ) . '/';
			$buffer  = preg_replace( $pattern, $match . ' ', $buffer );
		}

		return $buffer;
	}
}
