<?php
namespace justimageoptimizer\services;

interface ImageOptimizerInterface {

	public function check_api_key();

	public function upload_optimize_images( $optimize_contents_url );
}
?>