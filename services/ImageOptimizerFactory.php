<?php
namespace justimageoptimizer\services;
use justimageoptimizer\models;
class ImageOptimizerFactory {

	public static function create() {
		$service = get_option( models\Connect::DB_OPT_SERVICE );
		if ( $service ) {
			switch ( $service ) {
				case 'google_insights' :
					$google_insights = new GooglePagespeed();
					$google_insights->api_key = get_option( models\Connect::DB_OPT_API_KEY );
					return $google_insights;
				default:
					throw new \Exception( "Service does not found" );
			}
		}
	}
}

?>