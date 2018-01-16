<?php
namespace JustCoded\WP\ImageOptimizer\services;

interface ImageOptimizerInterface {

	/**
	 * Check Service credentials to be valid
	 *
	 * @return bool
	 */
	public function check_api_key();

	/**
	 * Optimize images and save to destination directory
	 *
	 * @param int[]  $attach_ids Attachment ids to optimize.
	 * @param string $dst Directory to save image to.
	 *
	 * @return mixed
	 */
	public function upload_optimize_images( $attach_ids, $dst );
}
