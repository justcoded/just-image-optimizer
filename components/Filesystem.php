<?php


namespace JustCoded\WP\ImageOptimizer\components;

use JustCoded\WP\ImageOptimizer\core\Singleton;

require ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
require ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

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
	public $fs_user;

	/**
	 * Filesystem group
	 *
	 * @var string $fs_group .
	 */
	public $fs_group;

	/**
	 * Filesystem constructor.
	 */
	public function __construct() {
		add_filter( 'filesystem_method', array( $this, 'filesystem_direct' ) );

		$this->fs_credentials();
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
	 *
	 * @param string $path .
	 *
	 * @return bool|\WP_Error
	 */
	public function setup_directories( $path ) {
		if ( ! $this->is_writable( UPLOADS_ROOT ) ) {
			return new \WP_Error( 'error_exec', 'Exec() function not allowed on the server.' );
		}

		return $this->is_dir( $path ) || $this->mkdir( $path, 0755, $this->fs_user, $this->fs_group );
	}

	/**
	 * Get_uploads_path
	 *
	 * @param string $way .
	 *
	 * @return array
	 */
	public static function get_uploads_path( $way = UPLOADS_ROOT ) {
		$path = array();
		foreach ( glob( $way . '/*', GLOB_ONLYDIR ) as $upload ) {
			foreach ( glob( $upload . '/*', GLOB_ONLYDIR ) as $upload_dir ) {
				$path[] = $upload_dir;
			}
		}

		return $path;
	}

	/**
	 * Prepare_path
	 *
	 * @param string $in_file .
	 * @param string $format .
	 *
	 * @return bool|\WP_Error
	 */
	public function prepare_path( $in_file, $format ) {
		if ( empty( $format ) ) {
			return new \WP_Error( 'empty_format', 'File format should not be empty.' );
		}

		$in_file = str_replace( UPLOADS_ROOT, '', $in_file );
		$parts   = explode( '/', $in_file );
		$_path   = UPLOADS_ROOT . DIRECTORY_SEPARATOR . $format . DIRECTORY_SEPARATOR;

		foreach ( $parts as $folder ) {
			if ( basename( $in_file ) === $folder ) {
				continue;
			}

			$_path .= $folder . DIRECTORY_SEPARATOR;

			if ( ! $this->is_dir( $_path ) ) {
				if ( ! $this->create_space( $_path ) ) {
					return new \WP_Error( 'uploads_permission', 'Uploads directory must be writable!' );
				}
			}
		}

		return true;
	}

	/**
	 * Clean_space
	 *
	 * @param string $path .
	 *
	 * @return bool
	 */
	public function clean_space( $path ) {
		return $this->rmdir( $path, true );
	}

	/**
	 * Create_space
	 *
	 * @param string $path .
	 *
	 * @return mixed
	 */
	public function create_space( $path ) {
		return $this->is_dir( $path ) || $this->mkdir( $path, 0755, $this->fs_user, $this->fs_group );
	}

	/**
	 * Function for init filesystem accesses
	 *
	 * @return string
	 */
	public function filesystem_direct() {
		return 'direct';
	}

}
