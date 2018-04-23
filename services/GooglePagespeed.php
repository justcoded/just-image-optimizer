<?php

namespace JustCoded\WP\ImageOptimizer\services;

use JustCoded\WP\ImageOptimizer\components\Optimizer;
use JustCoded\WP\ImageOptimizer\core\Component;
use JustCoded\WP\ImageOptimizer\models;

class GooglePagespeed implements ImageOptimizerInterface {

	const API_URL = 'https://www.googleapis.com/pagespeedonline/v1/runPagespeed?';
	const OPTIMIZE_CONTENTS = 'https://www.googleapis.com/pagespeedonline/v3beta1/optimizeContents?';

	/**
	 * Service API key
	 *
	 * @var string
	 */
	public $api_key;

	/**
	 * Cache for storing attach filenames, which were printed on page.
	 *
	 * @var array
	 */
	private $attach_filenames = array();

	/**
	 * Class constructor.
	 * initialize WordPress hooks
	 */
	public function __construct() {
		add_filter( 'query_vars', array( $this, 'query_vars' ), 0 );
		add_action( 'parse_request', array( $this, 'parse_request' ), 0 );
		add_action( 'init', array( $this, 'add_rewrite_rules' ), 0 );
	}

	/**
	 * User friendly service name.
	 *
	 * @return string
	 */
	public function name() {
		return 'Google Page Speed';
	}

	/**
	 * Check Service credentials to be valid
	 *
	 * @return bool
	 */
	public function check_api_key() {
		$check_url = 'http://code.google.com/speed/page-speed/';
		$url_req   = self::API_URL . 'url=' . $check_url . '&key=' . $this->api_key . '';
		$response  = wp_remote_get( $url_req, array( 'timeout' => 60 ) );
		$code      = wp_remote_retrieve_response_code( $response );
		return ( 200 === $code );
	}


	/**
	 * Check Service availability (that it has correct mode or site access)
	 *
	 * @param bool $force_check Ignore caches within requriements check.
	 *
	 * @return bool
	 */
	public function check_availability( $force_check = false ) {
		// generate unique transient key based on domain, this will minimize requests on same domain.
		$transient_key = 'jio_service_availability.google_page_speed.' . home_url();

		$status = get_transient( $transient_key );
		if ( false === $status || $force_check ) {
			$url_req  = self::API_URL . 'url=' . home_url() . '&key=' . $this->api_key . '';
			$response = wp_remote_get( $url_req, array( 'timeout' => 60 ) );
			$code     = wp_remote_retrieve_response_code( $response );

			$status = (int) ( 200 === $code );
			set_transient( $transient_key, $status, 3600 * 60 * 24 );
		}
		return $status;
	}

	/**
	 * Optimize images and save to destination directory
	 *
	 * @param int[]      $attach_ids Attachment ids to optimize.
	 * @param string     $dst Directory to save image to.
	 * @param models\Log $log Log object.
	 *
	 * @return mixed
	 */
	public function upload_optimize_images( $attach_ids, $dst, $log ) {
		/* @var $wp_filesystem \WP_Filesystem_Direct */
		global $wp_filesystem;

		$base_attach_ids = base64_encode( implode( ',', $attach_ids ) );
		$upload_dir      = WP_CONTENT_DIR;
		$google_img_path = $dst . '/image/';
		$wp_filesystem->is_dir( $google_img_path ) || $wp_filesystem->mkdir( $google_img_path );

		$images_url = home_url( '/just-image-optimize/google/' . $base_attach_ids ) . '?' . rand( 0, 10000 );
		$log->update_info( 'Optimize request: ' . $images_url );

		// download archive file with optimized images.
		$archive_file = $upload_dir . '/optimize_contents.zip';
		$source       = self::OPTIMIZE_CONTENTS . 'key=' . $this->api_key . '&url=' . rawurlencode( $images_url ) . '&strategy=desktop';

		$response = wp_remote_get( $source, array(
			'httpversion' => '1.1',
			'sslverify'   => false,
			'timeout'     => 120,
			'stream'      => true,
			'filename'    => $archive_file,
		) );
		if ( is_wp_error( $response ) ) {
			$log->update_info( 'WP Error: ' . $response->get_error_message() );
			return false;
		}

		$log->update_info( 'Downloaded: ' . $archive_file . ', ' . ( (int) @filesize( $archive_file ) ) . 'B' );

		// optimized images are placed under /image folder inside the archive, so $google_img_path = $dst . '/image'.
		$unzipfile = unzip_file( $archive_file, $dst );
		if ( ! is_wp_error( $unzipfile ) ) {
			// Get array of all source files.
			$files   = scandir( $google_img_path );
			$counter = 0;
			foreach ( $files as $file ) {
				if ( in_array( $file, array( '.', '..' ), true ) ||
					! preg_match( '/([\d]+)\.(.+)/', $file, $match )
				) {
					continue;
				}
				// find media stats row corresponding to this image.
				if ( ! $stats_row = models\Media::find_stats_by_id( $match[1] ) ) {
					continue;
				}

				// copy optimized image under real filename.
				if ( ! $wp_filesystem->is_file( $dst . $stats_row->attach_name ) ) {
					copy( $google_img_path . $file, $dst . $stats_row->attach_name );
				}
				$counter ++;
			}
			if ( is_dir( $google_img_path ) ) {
				$wp_filesystem->rmdir( $google_img_path, true );
			}
			unlink( $archive_file );

			$log->update_info( 'Extracted: ' . $counter . ' files' );
			return $counter;
		} else {
			$log->update_info( 'WP Error: ' . $unzipfile->get_error_message() );
			return false;
		}
	}

	/**
	 * Add custom rewrite url.
	 */
	public function add_rewrite_rules() {
		add_rewrite_rule( '^just-image-optimize/google/image/([\d]+)', 'index.php?just-image-optimize=google-image&image_size_id=$matches[1]', 'top' );
		add_rewrite_rule( '^just-image-optimize/google/(.+)', 'index.php?just-image-optimize=google-page&attach_ids=$matches[1]', 'top' );
	}

	/**
	 * Add custom query vars.
	 *
	 * @param array $query_vars Array with WordPress query_vars.
	 *
	 * @return array Array with new query_vars.
	 */
	public function query_vars( $query_vars ) {
		$query_vars[] = 'just-image-optimize';
		$query_vars[] = 'attach_ids';
		$query_vars[] = 'image_size_id';

		return $query_vars;
	}

	/**
	 * Render optimize page for upload images
	 */
	public function parse_request() {
		global $wp;
		if ( ! empty( $wp->query_vars['just-image-optimize'] ) ) {
			switch ( $wp->query_vars['just-image-optimize'] ) {
				case 'google-page':
					$this->render_images_page( $wp->query_vars['attach_ids'] );
					break;

				case 'google-image':
					$this->render_image_proxy( $wp->query_vars['image_size_id'] );
			}
		}
	}

	/**
	 * Print a page with all image attachment sizes to scan it with google.
	 *
	 * @param array $attach_ids Attachment IDs for optimization.
	 */
	protected function render_images_page( $attach_ids ) {
		require ABSPATH . 'wp-admin/includes/file.php';

		// extract attach ids.
		$attach_ids    = base64_decode( $attach_ids );
		$attach_ids    = explode( ',', $attach_ids );

		(new Component())->render( 'optimize/google-page-speed', array(
			'attach_ids' => $attach_ids,
			'service'    => $this,
			'media'      => new models\Media(),
			'settings'   => \JustImageOptimizer::$settings,
		) );
		exit;
	}

	/**
	 * Generate image proxy URL to be able identify images after optimization.
	 *
	 * @param int    $attach_id  Attach to optimize.
	 * @param string $image_size Image size to optimize.
	 *
	 * @return string Image proxy URL.
	 */
	public function get_image_proxy_url( $attach_id, $image_size ) {
		if ( $row = models\Media::find_stats( $attach_id, $image_size ) ) {
			// small cache to skip duplicated filenames.
			if ( ! isset( $this->attach_filenames[ $row->attach_name ] ) ) {
				$filename_parts = explode( '.', $row->attach_name );
				$extension      = end( $filename_parts );

				$this->attach_filenames[ $row->attach_name ] = $row->attach_name;
				return home_url( "/just-image-optimize/google/image/{$row->id}.{$extension}" );
			}
		}
	}

	/**
	 * Proxy to print real attachment image under custom URL with ID.
	 *
	 * @param int $image_size_id  Image stats size ID.
	 */
	protected function render_image_proxy( $image_size_id ) {
		if ( $row = models\Media::find_stats_by_id( $image_size_id ) ) {
			$metadata = wp_get_attachment_metadata( $row->attach_id );
			$path     = get_attached_file( $row->attach_id, true );
			if ( ! empty( $metadata['sizes'][ $row->image_size ] ) ) {
				$mime_type = $metadata['sizes'][ $row->image_size ]['mime-type'];
				$file      = dirname( $path ) . '/' . $row->attach_name;

				header( 'Content-type: ' . $mime_type );
				readfile( $file );
			}
		}
		exit;
	}
}
