<?php
namespace justimageoptimizer\services;

use justimageoptimizer\models\Connect;

class ImageOptimizerFactory {

	public static function create( $service = '', $api_key = '' ) {
		$connect = new Connect();
		$service = ( $service ? $service : $connect->service );
		if ( $service ) {
			switch ( $service ) {
				case 'google_insights' :
					$google_insights          = new GooglePagespeed();
					$google_insights->api_key = ( $api_key ? $api_key : $connect->api_key );

					return $google_insights;
				default:
					throw new \Exception( "Service does not found" );
			}
		}
	}
}

?>