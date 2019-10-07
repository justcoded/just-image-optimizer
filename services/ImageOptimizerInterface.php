<?php
namespace JustCoded\WP\ImageOptimizer\services;

use JustCoded\WP\ImageOptimizer\models\Log;

interface ImageOptimizerInterface {

	/**
	 * User friendly service name.
	 *
	 * @return string
	 */
	public function name();

	/**
	 * Check Service credentials to be valid
	 *
	 * @return bool
	 */
	public function check_api_key();

	/**
	 * Check Service availability (that it has correct mode or site access)
	 *
	 * @param bool $force_check Ignore caches within requriements check.
	 *
	 * @return bool
	 */
	public function check_availability( $force_check = false );

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
