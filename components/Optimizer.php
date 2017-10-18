<?php

namespace justimageoptimizer\components;
use justimageoptimizer\models\Settings;
/**
 * Class Optimizer
 */
class Optimizer extends \justimageoptimizer\core\Component {

	/**
	 * Class constructor.
	 * initialize WordPress hooks
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'optimizer_image_add_cron' ) );
		add_filter( 'cron_schedules', array( $this, 'optimizer_image_add_schedule' ) );
	}

	/**
	 * Add Optimizer Image cron function.
	 */
	public function optimizer_image_add_cron() {
		if ( ! wp_next_scheduled( 'optimizer_image_cron_start' ) ) {
			wp_schedule_event( time(), 'optimizer_image', 'optimizer_image_cron_start' );
		}
	}

	/**
	 * Add Optimizer Image cron interval function.
	 */
	public function optimizer_image_add_schedule() {
		$schedules['optimizer_image'] = array( 'interval' => 5 * 60, 'display' => 'Optimizer Image Cron Work' );

		return $schedules;
	}
}