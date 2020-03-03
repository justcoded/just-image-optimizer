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
	 * Service ID.
	 *
	 * @return string
	 */
	public function service_id();

	/**
	 * Checks does service use api key.
	 *
	 * @return bool
	 */
	public function has_api_key();

	/**
	 * Check Service credentials to be valid
	 *
	 * @return bool
	 */
	public function check_api_key();

	/**
	 * Check Service connection (that it has correct mode or site access)
	 *
	 * @param bool $force_check Ignore caches within requirements check.
	 *
	 * @return bool
	 */
	public function check_connect( $force_check = false );

	/**
	 * Has options
	 *
	 * @return bool
	 */
	public function has_options();

	/**
	 * Get service options
	 *
	 * @return array
	 */
	public function get_service_options();

	/**
	 * Optimize images and save to destination directory
	 *
	 * @param int[]  $attach_ids Attachment ids to optimize.
	 * @param Log    $log Log object.
	 * @param string $dst Directory to save image to.
	 *
	 * @return integer
	 */
	public function optimize_images( $attach_ids, $log, $dst = null );
}
