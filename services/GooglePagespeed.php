<?php

namespace justimageoptimizer\services;

use justimageoptimizer\components\Optimizer;

class GooglePagespeed implements ImageOptimizerInterface {

	const API_URL = 'https://www.googleapis.com/pagespeedonline/v1/runPagespeed?';
	const OPTIMIZE_CONTENTS = 'https://www.googleapis.com/pagespeedonline/v3beta1/optimizeContents?';

	/**
	 * Class constructor.
	 * initialize WordPress hooks
	 */
	public function __construct() {
		add_filter( 'query_vars', array( $this, 'optimize_query_vars' ), 0 );
		add_action( 'parse_request', array( $this, 'optimize_view' ), 0 );
		add_action( 'init', array( $this, 'optimize_rewrite' ), 0 );
	}

	public function check_api_key( $api_key ) {
		$check_url = 'http://code.google.com/speed/page-speed/';
		$url_req   = self::API_URL . 'url=' . $check_url . '&key=' . $api_key . '';
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
			return 1;
		}

		return 2;
	}

	public function upload_optimize_images( $api_key, $optimize_contents_url ) {
		$upload_dir = WP_CONTENT_DIR;
		$ch         = curl_init();
		$file       = fopen( $upload_dir . '/optimize_contents.zip', 'w+' );
		$source     = self::OPTIMIZE_CONTENTS . 'key=' . $api_key . '&url=' . $optimize_contents_url . '&strategy=desktop';
		curl_setopt( $ch, CURLOPT_URL, $source );
		curl_setopt( $ch, CURLOPT_FILE, $file );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_exec( $ch );
		curl_close( $ch );
		fclose( $file );

		WP_Filesystem();
		unzip_file( $upload_dir . '/optimize_contents.zip', $upload_dir . '/tmp' );
		unlink( $upload_dir . '/optimize_contents.zip' );
	}

	public function optimize_rewrite() {
		add_rewrite_rule( '^just-image-optimize/?', 'index.php?just-image-optimize=true', 'top' );
		flush_rewrite_rules();
	}

	public function optimize_query_vars( $query_vars ) {
		$query_vars[] = 'just-image-optimize';

		return $query_vars;
	}

	public function optimize_view() {
		global $wp;
		$optimizer = new Optimizer();
		if ( isset( $wp->query_vars['just-image-optimize'] ) ) {
			$optimizer->render( 'optimize/index' );
			exit;
		}
	}
}

?>