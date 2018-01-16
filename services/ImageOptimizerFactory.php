<?php
namespace JustCoded\WP\ImageOptimizer\services;

use JustCoded\WP\ImageOptimizer\models\Connect;

/**
 * Class ImageOptimizerFactory
 *
 * @package JustCoded\WP\ImageOptimizer\services
 */
class ImageOptimizerFactory {

	/**
	 * Factory create method
	 *
	 * @param string $service Service to be created.
	 * @param string $api_key Credentials.
	 *
	 * @return GooglePagespeed
	 * @throws \Exception Wrong service passed.
	 */
	public static function create( $service = '', $api_key = '' ) {
		$connect = new Connect();
		$service = ( $service ? $service : $connect->service );
		if ( $service ) {
			switch ( $service ) {
				case 'google_insights':
					$google_insights          = new GooglePagespeed();
					$google_insights->api_key = ( $api_key ? $api_key : $connect->api_key );

					return $google_insights;
				default:
					throw new \Exception( "Service \"{$service}\" does not exists." );
			}
		}
	}
}
