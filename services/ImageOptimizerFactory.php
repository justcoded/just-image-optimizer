<?php
namespace justimageoptimizer\services;

class ImageOptimizerFactory {

	public static function create( $service ) {
		switch ( $service ) {
			case 'google_insights' :
				return new GooglePagespeed();
			default:
				return new GooglePagespeed();
		}

	}
}

?>