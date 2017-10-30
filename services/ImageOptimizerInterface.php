<?php
namespace justimageoptimizer\services;

interface ImageOptimizerInterface {

	public function check_api_key( $api_key );

	public function upload_optimize_images( $api_key, $optimize_contents_url );
}
?>