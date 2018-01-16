<?php

namespace JustCoded\WP\ImageOptimizer\services;

use JustCoded\WP\ImageOptimizer\components\Optimizer;
use JustCoded\WP\ImageOptimizer\models;

class GooglePagespeed implements ImageOptimizerInterface {

	const API_URL           = 'https://www.googleapis.com/pagespeedonline/v1/runPagespeed?';
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
		if ( function_exists( 'file_get_contents' ) ) {
			$result = @file_get_contents( $url_req );
		}
		if ( $result == '' ) {
			$ch      = curl_init();
			$timeout = 60;
			curl_setopt( $ch, CURLOPT_URL, $url_req );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
			$result = curl_exec( $ch );
			curl_close( $ch );
		}
		$decode_result = json_decode( $result );
		if ( isset( $decode_result->responseCode ) && $decode_result->responseCode === 200 ) {
			return true;
		}

		return false;
	}

	/**
	 * Optimize images and save to destination directory
	 *
	 * @param int[]  $attach_ids Attachment ids to optimize.
	 * @param string $dst Directory to save image to.
	 *
	 * @return mixed
	 */
	public function upload_optimize_images( $attach_ids, $dst ) {
		// TODO: use wp_remote_get() instead of direct cURL.
		$base_attach_ids = base64_encode( implode( ',', $attach_ids ) );
		$upload_dir      = WP_CONTENT_DIR;
		$google_img_path = $upload_dir . '/tmp/image/';
		$ch              = curl_init();
		$file            = fopen( $upload_dir . '/optimize_contents.zip', 'w+' );
		$source          = self::OPTIMIZE_CONTENTS . 'key=' . $this->api_key . '&url=' . home_url( '/just-image-optimize/' . $base_attach_ids . '' ) . '&strategy=desktop';
		curl_setopt( $ch, CURLOPT_URL, $source );
		curl_setopt( $ch, CURLOPT_FILE, $file );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_exec( $ch );
		curl_close( $ch );
		fclose( $file );

		$unzipfile = unzip_file( $upload_dir . '/optimize_contents.zip', $dst );
		if ( $unzipfile ) {
			// Get array of all source files.
			$files = scandir( $google_img_path );
			foreach ( $files as $file ) {
				if ( in_array( $file, array( '.', '..' ), true ) ) {
					continue;
				}
				copy( $google_img_path . $file, $dst . $file );
			}
			if ( is_dir( $google_img_path ) ) {
				Optimizer::delete_dir( $google_img_path );
			}
			unlink( $upload_dir . '/optimize_contents.zip' );
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
