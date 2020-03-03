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
	 * Services list.
	 *
	 * @var array $scope
	 */
	public static $scope = array();

	/**
	 * ImageOptimizerFactory constructor.
	 */
	public function __construct() {
		self::$scope = array(
			/*'google_insights' => new GooglePagespeed(),*/
			'localconverter' => new LocalConverter(),
		);
	}

	/**
	 * Factory create method
	 *
	 * @param string $service Service to be created.
	 * @param string $api_key Credentials.
	 *
	 * @return GooglePagespeed|LocalConverter
	 * @throws \Exception Wrong service passed.
	 */
	public static function create( $service = '', $api_key = '' ) {
		$connect = new Connect();
		$service = ( $service ? $service : $connect->service );
		if ( $service ) {
			if ( empty( self::$scope[ $service ] ) ) {
				throw new \Exception( "Service \"{$service}\" does not exists." );
			}

			$item = self::$scope[ $service ];
			if ( true === $item->has_api_key() ) {
				$item->api_key = ( $api_key ? : $connect->api_key );
			}

			return $item;
		}
	}
}
