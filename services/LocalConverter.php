<?php


namespace JustCoded\WP\ImageOptimizer\services;

use JustCoded\WP\ImageOptimizer\components\Filesystem;
use JustCoded\WP\ImageOptimizer\models\Log;
use JustCoded\WP\ImageOptimizer\models\Media;
use Exception;

define( 'UPLOADS_ROOT', wp_upload_dir()['basedir'] );
define( 'UPLOADS_URL', wp_upload_dir()['baseurl'] );

/**
 * Class LocalConverter
 *
 * @package JustCoded\WP\ImageOptimizer\services
 */
class LocalConverter implements ImageOptimizerInterface {

	/**
	 * Service extra options.
	 *
	 * @var array $options
	 */
	public static $options = array(
		'webp_image_quality' => 75,
		'jp2_image_quality'  => 55,
		'lossless'           => 1,
	);

	/**
	 * Conversion statuses
	 *
	 * @var array $conversion_statuses
	 */
	protected static $conversion_statuses = array(
		0 => 'Fail',
		1 => 'Success',
		2 => 'Inappropriate color profile: CMYK',
		3 => 'Input file does not exists',
		4 => 'Inappropriate image type: ',
		5 => 'Cannot create folders',
	);

	/**
	 * Converter directories.
	 *
	 * @var array $directories .
	 */
	protected static $directories = array(
		'webp',
		'jp2',
		'tmp',
	);

	/**
	 * Service errors
	 *
	 * @var \WP_Error $errors .
	 */
	protected $errors;

	/**
	 * Filesystem
	 *
	 * @var Filesystem $fs .
	 */
	protected $fs;

	/**
	 * LocalConverter constructor.
	 */
	public function __construct() {
		$this->fs     = Filesystem::instance();
		$this->errors = new \WP_Error();

		foreach ( self::$directories as $path ) {
			$this->fs->setup_directories( UPLOADS_ROOT . DIRECTORY_SEPARATOR . $path );
		}
	}

	/**
	 * Service name.
	 *
	 * @return string
	 */
	public function name() {
		return 'Local Image Converter';
	}

	/**
	 * Service ID
	 *
	 * @return string
	 */
	public function service_id() {
		return 'localconverter';
	}

	/**
	 * Has_option
	 *
	 * @return bool
	 */
	public function has_options() {
		return true;
	}

	/**
	 * Has_api_key
	 *
	 * @return bool
	 */
	public function has_api_key() {
		return false;
	}

	/**
	 * Get service options
	 *
	 * @return array
	 */
	public function get_service_options() {
		if ( true === $this->has_options() ) {
			return self::$options;
		}

		return array();
	}

	/**
	 * Check_api_key
	 *
	 * @return bool
	 */
	public function check_api_key() {
		return true;
	}

	/**
	 * Check_connect
	 *
	 * @param bool $force_check .
	 *
	 * @return bool|array
	 */
	public function check_connect( $force_check = false ) {
		$this->check_exec();
		$this->check_writable();
		$this->check_cwebp();
		$this->check_imagick();

		return $this->errors->has_errors() ? $this->errors->get_error_messages() : true;
	}

	/**
	 * Check_exec
	 *
	 * @return bool
	 */
	protected function check_exec() {
		if ( function_exists( 'exec' ) ) {
			if ( 'EXEC' === exec( 'echo EXEC' ) ) {
				return true;
			}
			$this->errors->add( 'error_exec', 'Exec() function allowed, but website has no permission to execute.' );

			return false;
		}

		$this->errors->add( 'error_exec', 'Exec() function not allowed on the server.' );

		return false;
	}

	/**
	 * Check_writable
	 *
	 * @return bool
	 */
	protected function check_writable() {
		if ( is_writable( UPLOADS_ROOT ) ) {

			return true;
		}

		$this->errors->add( 'uploads_permission', 'Uploads directory must be writable!' );

		return false;
	}

	/**
	 * Check_cwebp
	 *
	 * @return bool
	 */
	private function check_cwebp() {
		$test_image     = JUSTIMAGEOPTIMIZER_ROOT . '/assets/images/test_image.jpg';
		$test_out_image = UPLOADS_ROOT . '/test_output_image.webp';

		exec( 'cwebp -version', $output, $code );

		if ( ( 0 === $code ) && isset( $output[0] ) ) {
			exec( "cwebp -q 75 $test_image -o $test_out_image", $test_conversion, $code );

			if ( ( 0 === $code ) && $this->fs->exists( $test_out_image ) ) {
				unlink( $test_out_image );

				return true;
			}

			$this->errors->add( 'error_webp', 'Cannot convert test image to WEBP.' );

			return false;
		}

		$this->errors->add( 'error_webp', 'Webp library not installed.' );

		return false;
	}

	/**
	 * Check_imagick
	 *
	 * @return bool
	 */
	private function check_imagick() {
		$test_image     = JUSTIMAGEOPTIMIZER_ROOT . '/assets/images/test_image.jpg';
		$test_out_image = UPLOADS_ROOT . '/test_output_image.jp2';

		exec( 'identify -list format', $formats, $code );

		if ( isset( $formats ) && ! empty( $formats ) ) {
			foreach ( $formats as $format ) {
				if ( preg_match( '(JP2)', $format ) ) {
					exec( "convert $test_image -define jp2:quality=60 $test_out_image", $test_conversion, $code );

					if ( $this->fs->exists( $test_out_image ) ) {
						unlink( $test_out_image );

						return true;
					}

					$this->errors->add( 'error_jp2', 'Cannot convert test image to JP2.' );

					return false;
				}
			}
			$this->errors->add( 'error_jp2', 'JP2 delegates not configured.' );

			return false;
		}

		$this->errors->add( 'error_jp2', 'ImageMagick package not installed.' );

		return false;
	}

	/**
	 * Optimize_images
	 *
	 * @param int[]                                   $attach_ids .
	 * @param \JustCoded\WP\ImageOptimizer\models\Log $log .
	 * @param string                                  $dst .
	 *
	 * @return integer
	 * @throws Exception
	 */
	public function optimize_images( $attach_ids, $log, $dst = null ) {
		$counter = 0;

		foreach ( $attach_ids as $attach_id ) {
			$this->convert_image( $attach_id );
			++ $counter;
		}

		return $counter;
	}

	/**
	 * Convert_image
	 *
	 * @param integer $attachment_id .
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function convert_image( $attachment_id ) {
		$queued_sizes = Media::get_queued_image_sizes( $attachment_id );

		if ( ! empty( $queued_sizes ) ) {
			foreach ( $queued_sizes as $size ) {
				$size_url    = wp_get_attachment_image_url( $attachment_id, $size );
				$size_path   = $this->attachment_base_path( $size_url );
				$upload_path = trim( str_replace( UPLOADS_URL, '', $size_url ), '/' );
				$image       = $this->create_images( $size_path );
				$this->save_conversion_stats( $attachment_id, $image, $size, $upload_path );

				if ( strpos( $size_path, 'conversion_tmp' ) ) {
					unlink( $size_path );
				}
			}
		}

		return true;
	}

	/**
	 * Create_images
	 *
	 * @param string $file .
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function create_images( $file ) {
		$stat = array(
			'jp2'  => false,
			'webp' => false,
		);

		foreach ( self::$directories as $converter ) {
			if ( array_key_exists( $converter, $stat ) ) {
				$stat[ $converter ] = $this->do_conversion( $converter, $file );
			}
		}

		return $stat;
	}

	/**
	 * Do_conversion
	 *
	 * @param string $format .
	 * @param string $file .
	 *
	 * @return bool|array
	 * @throws Exception
	 */
	protected function do_conversion( $format, $file ) {
		if ( ! $this->fs->exists( $file ) ) {
			return $this->set_conversion_status( 3 );
		}

		$types = [ 2, 3 ];
		$mime  = exif_imagetype( $file );

		if ( ! in_array( $mime, $types, true ) ) {
			return $this->set_conversion_status( 4, '', image_type_to_mime_type( $mime ) );
		}

		if ( true === $this->is_cmyk( $file ) ) {
			return $this->set_conversion_status( 2 );
		}

		$quality = intval( \JustImageOptimizer::$settings->service_options[ $format . '_image_quality' ] );

		if ( is_wp_error( $this->fs->prepare_path( $file, $format ) ) ) {
			return $this->set_conversion_status( 5 );
		}

		$converted_path = $this->attachment_conversion_path( $file, $format );

		if ( 'webp' === $format ) {
			$lossless = '';

			if ( '1' === \JustImageOptimizer::$settings->service_options['lossless'] ) {
				$lossless = '-lossless -exact';
			}

			$command = "cwebp -q $quality $lossless $file -o $converted_path";
		} elseif ( 'jp2' === $format ) {
			$command = "convert $file -define jp2:quality=$quality $converted_path";
		}

		if ( empty( $command ) ) {
			return $this->set_conversion_status( 0 );
		}

		exec( $command, $output, $code );

		if ( $this->fs->exists( $converted_path ) ) {
			return $this->set_conversion_status( 1, $converted_path );
		}

		return $this->set_conversion_status( 0 );
	}

	/**
	 * Conversion_status
	 *
	 * @param int    $status .
	 * @param string $converted_path .
	 * @param string $image_type .
	 *
	 * @return array
	 */
	protected function set_conversion_status( int $status, string $converted_path = '', string $image_type = '' ) {
		return array(
			'status'             => $status,
			'conversion_message' => self::$conversion_statuses[ $status ] . $image_type,
			'converted_path'     => $converted_path,
		);
	}

	/**
	 * Attachment base path
	 * Return attachment base path if file is local.
	 * Otherwise, uploads remote file to temp directory and return path to the temp file.
	 *
	 * @param string $attachment_url .
	 *
	 * @return mixed
	 */
	public function attachment_base_path( $attachment_url ) {
		global $wp_version;
		$url_parts       = wp_parse_url( $attachment_url );
		$home_url        = wp_parse_url( home_url( '/' ) );
		$attachment_path = str_replace( UPLOADS_URL, UPLOADS_ROOT, $attachment_url );

		if ( $home_url['host'] !== $url_parts['host'] ) {
			$args = array(
				'httpversion' => '1.1',
				'user-agent'  => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
				'sslverify'   => false,
				'stream'      => false,
				'filename'    => basename( $attachment_path ),
			);

			$request = wp_remote_get( $attachment_url, $args );

			if ( ! empty( $request ) && 200 === $request['response']['code'] ) {

				$body = wp_remote_retrieve_body( $request );

				if ( is_wp_error( $this->fs->prepare_path( $attachment_path, 'tmp' ) ) ) {
					return new \WP_Error( 'copy_error', 'Can not copy image.' );
				}

				$tmp_path = UPLOADS_ROOT . DIRECTORY_SEPARATOR . self::$directories[2] . DIRECTORY_SEPARATOR . basename( $attachment_path );
				$this->fs->put_contents( $tmp_path, $body, 0644 );

				return $tmp_path;
			}
		}

		return $attachment_path;
	}

	/**
	 * Attachment_conversion_path
	 *
	 * @param string $attachment_path .
	 * @param string $format .
	 *
	 * @return string
	 */
	public function attachment_conversion_path( $attachment_path, $format ) {
		$attachment_path = str_replace( UPLOADS_ROOT, '', $attachment_path );
		$dirs            = '';
		$check_dirs      = trim( pathinfo( $attachment_path )['dirname'], '/' );
		$filename        = trim( pathinfo( $attachment_path )['filename'], '/' );

		if ( '.' !== $check_dirs ) {
			$dirs = $check_dirs . DIRECTORY_SEPARATOR;
		}

		return UPLOADS_ROOT
			. DIRECTORY_SEPARATOR
			. $format
			. DIRECTORY_SEPARATOR
			. $dirs
			. $filename . '.' . $format;
	}

	/**
	 * Save_conversion_stats
	 *
	 * @param int    $attach_id .
	 * @param array  $stats .
	 * @param string $size .
	 * @param string $upload_path .
	 */
	public function save_conversion_stats( $attach_id, $stats, $size, $upload_path ) {
		global $wpdb;
		$media        = new Media();
		$table        = $wpdb->prefix . Log::TABLE_IMAGE_CONVERSION;
		$data_formats = array( '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );

		foreach ( $stats as $key => $value ) {
			$time           = date( 'd.m.Y H:i:s', time() );
			$filesize       = $media->get_filesize( $value['converted_path'] );
			$converted_path = trim( str_replace(
				UPLOADS_ROOT,
				'',
				$this->attachment_conversion_path( $upload_path, $key ) ), '/' );
			$data           = array(
				'attach_id'          => $attach_id,
				'image_size'         => $size,
				'file_size'          => $filesize,
				'image_format'       => $key,
				'status'             => $value['status'],
				'conversion_message' => $value['conversion_message'],
				'upload_path'        => $upload_path,
				'converted_path'     => $converted_path,
				'creation_time'      => $time,
				'update_time'        => $time,
			);

			$record = $wpdb->get_row(
				"SELECT * 
						FROM {$table}
						WHERE `upload_path` = '{$upload_path}'
						AND `image_format` = '{$key}'",
				ARRAY_A
			);

			if ( empty( $record ) ) {
				$wpdb->insert( $table, $data, $data_formats );

				continue;
			}

			$data['creation_time'] = $record['creation_time'];
			$wpdb->update( $table, $data, array( 'record_id' => $record['record_id'] ), $data_formats );
		}
	}

	/**
	 * Color_format
	 * Detect image color format.
	 *
	 * @param string $path .
	 *
	 * @return string
	 */
	protected function is_cmyk( $path ) {
		$image_opts = getimagesize( $path );
		if ( array_key_exists( 'mime', $image_opts ) && 'image/jpeg' === $image_opts['mime'] ) {
			if ( array_key_exists( 'channels', $image_opts ) && 4 === $image_opts['channels'] ) {
				return true;
			}
		}

		return false;
	}

}
