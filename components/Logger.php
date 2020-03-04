<?php

namespace JustCoded\WP\ImageOptimizer\components;

use JustCoded\WP\ImageOptimizer\core\Singleton;

/**
 * Class Logger
 * For debugging, will be removed in the future.
 *
 * @package JustCoded\WP\Imagizer\
 *
 * @method Logger instance() static
 */
class Logger {
	use Singleton;

	/**
	 * Folder dir
	 *
	 * @var string
	 */
	protected $folder_dir;

	/**
	 * File dir.
	 *
	 * @var string
	 */
	protected $file_dir;

	/**
	 * Logger constructor.
	 */
	public function __construct() {
		$uploads          = wp_get_upload_dir();
		$this->folder_dir = $uploads['basedir'] . '/imagizer-logs';
		$this->file_dir   = $this->folder_dir . '/logs.log';

		$this->check_dir();
	}

	/**
	 * Check_dir
	 */
	protected function check_dir() {
		if ( ! is_dir( $this->folder_dir ) ) {
			mkdir( $this->folder_dir, 0755 );
		}

		if ( ! is_file( $this->file_dir ) ) {
			file_put_contents( $this->file_dir, '' );
		}
	}

	/**
	 * Logged
	 *
	 * @param array $message Message array.
	 *
	 * @return bool
	 */
	public function logged( $message ) {

		if ( ! is_array( $message ) ) {
			return false;
		}

		$ar   = debug_backtrace();
		$key  = pathinfo( $ar[0]['file'] );
		$key  = $key['dirname'] . ':' . $ar[0]['line'];
		$time = date( 'd:m:Y H:i:s' );
		$log  = '[' . $time . '] : ' . $key . ' - ' . wp_json_encode( $message );


		file_put_contents( $this->file_dir, $log . PHP_EOL, FILE_APPEND );
	}
}
