<?php


namespace JustCoded\WP\ImageOptimizer\components;

use JustCoded\WP\ImageOptimizer\includes\Singleton;
use JustCoded\WP\ImageOptimizer\models\ImagizerSettings;

require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

define( 'WEBP_DIR', UPLOADS_ROOT . '/webp' );
define( 'JP2_DIR', UPLOADS_ROOT . '/jp2' );

define( 'WEBP_URL', UPLOADS_URL . '/webp' );
define( 'JP2_URL', UPLOADS_URL . '/jp2' );

/**
 * Class Filesystem
 *
 * @package JustCoded\WP\Imagizer
 *
 * @method Filesystem instance() static
 */
class Filesystem extends \WP_Filesystem_Direct {
	use Singleton;

	/**
	 * Filesystem user
	 *
	 * @var string $fs_user .
	 */
	protected $fs_user;

	/**
	 * Filesystem group
	 *
	 * @var string $fs_group .
	 */
	protected $fs_group;

	/**
	 * Filesystem constructor.
	 */
	public function __construct() {
		$this->fs_credentials();
		$this->setup_directories();
		$this->check_years();
		$this->dataset();
	}

	/**
	 * Fs_credentials
	 */
	protected function fs_credentials() {
		$this->fs_user  = $this->owner( UPLOADS_ROOT );
		$this->fs_group = $this->group( UPLOADS_ROOT );
	}

	/**
	 * Directories
	 */
	protected function setup_directories() {
		if ( ! $this->is_writable( UPLOADS_ROOT ) ) {
			return;
		}

		$this->is_dir( WEBP_DIR ) || $this->mkdir( WEBP_DIR, 0777, $this->fs_user, $this->fs_group );
		$this->is_dir( JP2_DIR ) || $this->mkdir( JP2_DIR, 0777, $this->fs_user, $this->fs_group );
	}

	/**
	 * Check_years
	 */
	private function check_years() {
		$udirs = $this->get_uploads_path();

		foreach ( $udirs as $udir ) {
			$exploded = explode( '/', pathinfo( $udir )['dirname'] );
			$year     = end( $exploded );

			if ( preg_match( '/(?!0000)\d{4}/', $year ) ) {
				$this->is_dir( WEBP_DIR . '/' . $year )
				|| $this->mkdir( WEBP_DIR . '/' . $year, 0777, $this->fs_user, $this->fs_group );
				$this->is_dir( JP2_DIR . '/' . $year )
				|| $this->mkdir( JP2_DIR . '/' . $year, 0777, $this->fs_user, $this->fs_group );
			}
		}

	}

	/**
	 * Get_uploads_path
	 *
	 * @param string $way .
	 *
	 * @return array
	 */
	public function get_uploads_path( $way = UPLOADS_ROOT ) {
		$path = array();
		foreach ( glob( $way . '/*', GLOB_ONLYDIR ) as $upload ) {
			foreach ( glob( $upload . '/*', GLOB_ONLYDIR ) as $upload_dir ) {
				$path[] = $upload_dir;
			}
		}

		return $path;
	}

	/**
	 * Dataset
	 *
	 * @return object
	 */
	public function dataset() {
		$settings  = ImagizerSettings::instance();
		$webps     = $this->get_files( $this->get_uploads_path( WEBP_DIR ), [ 'webp' ] );
		$jp2s      = $this->get_files( $this->get_uploads_path( JP2_DIR ), [ 'jp2' ] );
		$all_files = $this->get_files( $this->get_uploads_path(), array( 'jpg', 'jpeg', 'png' ) );

		$output = (object) array(
			'attachments' => $all_files,
			'total'       => count( $all_files ),
			'webps'       => count( $webps ),
			'jp2s'        => count( $jp2s ),
		);

		$settings->update_options( $output );

		return $output;
	}

	/**
	 * Get_files
	 *
	 * @param array $dirs .
	 * @param array $ext .
	 * @param array $results .
	 *
	 * @return array
	 */
	public function get_files( $dirs, $ext, &$results = array() ) {

		foreach ( (array) $dirs as $dir ) {
			$files = scandir( $dir );

			foreach ( $files as $value ) {

				$path = realpath( $dir . '/' . $value );

				if ( ! $this->is_dir( $path ) ) {
					if ( in_array( pathinfo( $value )['extension'], $ext, true ) ) {
						$results[] = $path;
					}
				} elseif ( '.' !== $value && '..' !== $value ) {
					continue;
				}
			}
		}

		return $results;
	}

	/**
	 * Get_real_path
	 *
	 * @param string $file .
	 *
	 * @return mixed
	 */
	public function get_real_path( $file ) {
		preg_match( '(\/(\d+)\/(\d+)\/[^"\s]*)', $file, $filename );
		if ( empty( $filename ) ) {
			return null;
		}

		return $filename[0];
	}

	/**
	 * Prepare_path
	 *
	 * @param string $in_file .
	 *
	 * @return string
	 */
	public function prepare_path( $in_file ) {
		$path   = explode( '/', pathinfo( $in_file )['dirname'] );
		$up_key = array_search( 'uploads', $path, true );

		$year_key  = $up_key + 1;
		$month_key = $up_key + 2;

		$subdir = $path[ $year_key ] . '/' . $path[ $month_key ];

		return $subdir;
	}

	/**
	 * Check_file
	 *
	 * @param array  $files .
	 * @param string $extension .
	 *
	 * @return bool
	 */
	public function check_file( $files, $extension ) {
		foreach ( (array) $files as $file ) {
			$filename = pathinfo( $file )['filename'];
			$subdir   = pathinfo( $file )['dirname'];

			switch ( $extension ) {
				case 'webp':
					$path = WEBP_DIR;
					break;
				case 'jp2':
					$path = JP2_DIR;
					break;
				default:
					$path = UPLOADS_ROOT;
					break;
			}

			if ( $this->is_file( $path . $subdir . '/' . $filename . '.' . $extension ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Clean_space
	 */
	public function clean_space() {
		$this->rmdir( WEBP_DIR, true );
		$this->rmdir( JP2_DIR, true );
	}

	/**
	 * Create_space
	 *
	 * @param string $path .
	 *
	 * @return mixed
	 */
	public function create_space( $path ) {
		$this->is_dir( $path ) || $this->mkdir( $path, 0755, $this->fs_user, $this->fs_group );
	}
}
