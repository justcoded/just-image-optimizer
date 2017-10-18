<?php
namespace justimageoptimizer\services;

class GooglePagespeed implements ImageOptimizerInterface {

	const API_URL = 'https://www.googleapis.com/pagespeedonline/v1/runPagespeed?';

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
}

?>