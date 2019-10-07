<?php

namespace JustCoded\WP\ImageOptimizer\core;

use JustCoded\WP\ImageOptimizer\includes\Singleton;
use JustCoded\WP\ImageOptimizer\models;
use JustCoded\WP\ImageOptimizer\components;

use JustCoded\WP\ImageOptimizer\models\QueryModel;
use WebPConvert\WebPConvert;
use WebPConvert\Loggers\EchoLogger;

/**
 * Class Imagizer
 *
 * @package JustCoded\WP\ImageOptimizer
 *
 * @method Imagizer instance() static
 */
class Imagizer {
	use Singleton;

	/**
	 * Settings
	 *
	 * @var models\ImagizerSettings $settings .
	 */
	public $settings;

	/**
	 * Filesystem
	 *
	 * @var \stdClass $fs
	 */
	public $fs;

	/**
	 * Amount of image to convert by iteration
	 *
	 * @var int $amount
	 */
	public $amount;

	/**
	 * Imagizer constructor.
	 */
	protected function __construct() {
		$this->settings = models\ImagizerSettings::instance();
		$this->fs       = components\Filesystem::instance();

		add_action( 'wp_ajax_imagizer', array( $this, 'work' ) );
		add_action( 'wp_ajax_imagizer_renew_images_count', array( $this, 'renew_images_count' ) );
		add_action( 'wp_ajax_imagizer_remove_images', array( $this, 'remove_images' ) );
	}

	/**
	 * Progress
	 *
	 * @return int|null
	 */
	public static function progress() {
		$progress = get_option( 'progress' );

		if ( empty( $progress ) ) {
			return null;
		}

		return $progress;
	}

	/**
	 * Renew_images_count
	 */
	public function renew_images_count() {
		$webps = $this->fs->get_files( $this->fs->get_uploads_path( WEBP_DIR ), [ 'webp' ] );
		$jp2s  = $this->fs->get_files( $this->fs->get_uploads_path( JP2_DIR ), [ 'jp2' ] );

		$images = (object) array(
			'webps' => count( $webps ),
			'jp2s'  => count( $jp2s ),
		);

		echo wp_json_encode( $images );

		$this->settings->update_options( $images );

		delete_option( 'progress' );
		exit;
	}

	/**
	 * Remove_images
	 */
	public function remove_images() {
		$query_model = new QueryModel();
		$type        = $query_model->im_query_vars()['post']['type'];

		if ( empty( $type ) ) {
			echo 'Empty type.';
			exit;
		}

		switch ( $type ) {
			case 'webp':
				$images = $this->fs->get_files( $this->fs->get_uploads_path( WEBP_DIR ), [ $type ] );
				break;
			case 'jp2':
				$images = $this->fs->get_files( $this->fs->get_uploads_path( JP2_DIR ), [ $type ] );
				break;
		}

		if ( empty( $images ) ) {
			exit;
		}

		$progress = count( $images );
		update_option( 'progress', $progress );

		foreach ( $images as $image ) {
			unlink( $image );
			$progress --;
			update_option( 'progress', $progress );
		}

		echo 'Done';
		exit;
	}

	/**
	 * Work
	 * The main conversion process function
	 *
	 * @throws \WebPConvert\Convert\Exceptions\ConversionFailedException
	 */
	public function work() {
		$query_model = new QueryModel();
		$new_quality = $query_model->im_query_vars()['post']['quality'];

		if ( ! wp_verify_nonce( $_POST['nonce'], 'wp_rest' ) ) {
			exit;
		}

		if ( ! empty( $new_quality ) ) {
			$this->settings->options->quality = $new_quality;
			update_option( 'imgzr-quality', $new_quality );
		}

		if ( $this->settings->options->images_total <= $this->settings->options->converted['webp']
			&& $this->settings->options->images_total <= $this->settings->options->converted['jp2'] ) {

			update_option( 'progress', 'All images is already converted!' );

			exit;
		}

		$progress = 0;
		update_option( 'progress', $progress );

		$dataset = $this->fs->dataset();

		foreach ( $dataset->attachments as $file ) {
			if ( ! $this->fs->exists( $file ) ) {
				continue;
			}

			$create_image = $this->create_images( $file );

			if ( empty( $create_image ) ) {
				continue;
			}

			$progress ++;
			update_option( 'progress', $progress );

			if ( 0 === ( $progress % $this->settings->options->amount ) ) {
				sleep( 4 );
			}
		}

		echo 'Done!';
		exit;
	}

	/**
	 * Create_images
	 * Checking requirements of input file.
	 *
	 * @param string $file .
	 *
	 * @return bool|null
	 *
	 * @throws \WebPConvert\Convert\Exceptions\ConversionFailedException
	 */
	public function create_images( $file ) {
		if ( empty( $file ) ) {
			return null;
		}

		$types        = [ 2, 3 ];
		$mime         = exif_imagetype( $file );
		$color_format = $this->color_format( $file );

		if ( ! in_array( $mime, $types, true ) || 'cmyk' === $color_format ) {
			return null;
		}

		$this->do_jp2( $file );
		$this->do_webp( $file );

		return true;
	}

	/**
	 * Color_format
	 * Detect image color format.
	 *
	 * @param string $path .
	 *
	 * @return string
	 */
	protected function color_format( $path ) {
		$image_opts = getimagesize( $path );
		if ( array_key_exists( 'mime', $image_opts ) && 'image/jpeg' === $image_opts['mime'] ) {
			if ( array_key_exists( 'channels', $image_opts ) && 4 === $image_opts['channels'] ) {
				return 'cmyk';
			}
		}

		return 'rgb';
	}

	/**
	 * Do JP2
	 * Convert image to JPEG 2000
	 *
	 * @param string $file .
	 *
	 * @return void
	 */
	protected function do_jp2( $file ) {
		$log     = components\Logger::instance();
		$quality = $this->settings->options->quality;

		$subdir  = $this->fs->prepare_path( $file );
		$way_jp2 = JP2_DIR . '/' . $subdir . '/';
		$this->fs->create_space( $way_jp2 );
		$out_file_jp2 = $way_jp2 . pathinfo( $file )['filename'] . '.jp2';

		$jp2cmd  = 'WIN' === $this->settings->options->sys ? 'convert.exe' : 'convert';
		$command = "$jp2cmd $file -define jp2:quality=$quality $out_file_jp2";

		if ( ! file_exists( $out_file_jp2 ) && file_exists( $file ) ) {
			try {
				exec( $command, $output );
				$log->logged( array(
					'format'  => 'JP2',
					'command' => $command,
					'output'  => $output,
				) );
			} catch ( \Exception $exception ) {
				$log->logged( array(
					'format'    => 'JP2',
					'command'   => $command,
					'output'    => $output,
					'exception' => $exception->getMessage(),
				) );
			}
		}
	}

	/**
	 * Do_webp
	 * Convert image to WEBP
	 *
	 * @param string $file .
	 *
	 * @return void
	 * @throws \WebPConvert\Convert\Exceptions\ConversionFailedException
	 */
	protected function do_webp( $file ) {
		$webp       = new WebPConvert();
		$converters = $this->settings->get_converters();

		( WP_ENV === 'development' ) ? $logger = new EchoLogger() : $logger = null; // For debugging information.

		$subdir   = $this->fs->prepare_path( $file );
		$way_webp = WEBP_DIR . '/' . $subdir . '/';
		$this->fs->create_space( $way_webp );
		$out_file_webp = $way_webp . pathinfo( $file )['filename'] . '.webp';

		if ( ! file_exists( $out_file_webp ) && file_exists( $file ) ) {
			$webp::convert(
				$file,
				$out_file_webp,
				array(
					'quality'          => (int) $this->settings->options->quality,
					'reconvert'        => true,
					'max-quality'      => 100,
					'encoding'         => 'lossless',
					'converters'       => $converters,
					'stack-converters' => $converters,
					'png'              => array(
						'converters' => array( 'cwebp' ),
						'gd-skip'    => true,
					),
				),
				$logger
			);
		}
	}
}
