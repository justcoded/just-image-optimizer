<?php
namespace JustCoded\WP\ImageOptimizer\services;

interface ImageOptimizerInterface {

	public function check_api_key();

	public function upload_optimize_images( $attach_id, $tmp_images );
}
?>