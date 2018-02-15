<?php
namespace JustCoded\WP\ImageOptimizer\services;

use JustCoded\WP\ImageOptimizer\models\Log;

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
	 * @param Log    $log Log object.
	 *
	 * @return mixed
	 */
	public function upload_optimize_images( $attach_ids, $dst, $log );
}
