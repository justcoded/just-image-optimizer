<?php

namespace JustCoded\WP\ImageOptimizer\services;

use JustCoded\WP\ImageOptimizer\components\Optimizer;
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
	 * Class constructor.
	 * initialize WordPress hooks
	 */
	public function __construct() {
		add_filter( 'query_vars', array( $this, 'query_vars' ), 0 );
		add_action( 'parse_request', array( $this, 'view' ), 0 );
		add_action( 'init', array( $this, 'rewrite_url' ), 0 );
	}

	/**
	 * Check Service credentials to be valid
	 *
	 * @return bool
	 */
	public function check_api_key() {
		$check_url = 'http://code.google.com/speed/page-speed/';
		$url_req   = self::API_URL . 'url=' . $check_url . '&key=' . $this->api_key . '';
		$ch        = wp_remote_get( $url_req, array( 'timeout' => 60 ) );
		$result    = wp_remote_retrieve_response_code( $ch );
		if ( $result === 200 ) {
			return true;
		}

		return false;
	}

	/**
	 * Optimize images and save to destination directory
	 *
	 * @param int[] $attach_ids Attachment ids to optimize.
	 * @param string $dst Directory to save image to.
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

		$images_url = home_url( '/just-image-optimize/' . $base_attach_ids );
		$log->update_info( 'Optimize request: ' . $images_url );

		//download archive file with optimized images
		$archive_file = $upload_dir . '/optimize_contents.zip';
		$source       = self::OPTIMIZE_CONTENTS . 'key=' . $this->api_key . '&url=' . $images_url . '&strategy=desktop';
		wp_remote_get( $source,
			array(
				'timeout'  => 120,
				'stream'   => true,
				'filename' => $archive_file,
			)
		);

		$log->update_info( 'Downloaded: ' . $archive_file . ', ' . ( (int) @filesize( $archive_file ) ) . 'B' );

		// optimized images are placed under /image folder inside the archive, so $google_img_path = $dst . '/image'.
		$unzipfile = unzip_file( $archive_file, $dst );
		if ( ! is_wp_error( $unzipfile ) ) {
			// Get array of all source files.
			$files   = scandir( $google_img_path );
			$counter = 0;
			foreach ( $files as $file ) {
				if ( in_array( $file, array( '.', '..' ), true ) ) {
					continue;
				}
				copy( $google_img_path . $file, $dst . $file );
				if ( strpos( $file, '.jpg' ) !== false ) {
					copy( $google_img_path . $file, $dst . str_replace( '.jpg', '.jpeg', $file ) );
				}
				$counter ++;
			}
			if ( is_dir( $google_img_path ) ) {
				$wp_filesystem->rmdir( $google_img_path, true );
			}
			unlink( $archive_file );

			$log->update_info( 'Extracted: ' . $counter . ' files' );
		}
	}

	/**
	 * Add custom rewrite url.
	 */
	public function rewrite_url() {
		add_rewrite_rule( '^just-image-optimize/?', 'index.php?just-image-optimize=true', 'top' );
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

		return $query_vars;
	}

	/**
	 * Render optimize page for upload images
	 */
	public function view() {
		global $wp;
		$optimizer = new Optimizer();
		if ( isset( $wp->query_vars['just-image-optimize'] ) ) {
			require ABSPATH . 'wp-admin/includes/file.php';
			$query_var_url = $_SERVER['REQUEST_URI'];
			$parse_url     = explode( '/', $query_var_url );
			$attach_ids    = base64_decode( end( $parse_url ) );
			$attach_ids    = explode( ',', $attach_ids );
			$optimizer->render( 'optimize/index', array(
				'attach_ids' => $attach_ids,
				'media'      => new models\Media(),
				'settings'   => \JustImageOptimizer::$settings,
			) );
			exit;
		}
	}
}
