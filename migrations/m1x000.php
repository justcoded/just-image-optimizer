<?php

namespace JustCoded\WP\ImageOptimizer\migrations;

use JustCoded\WP\ImageOptimizer\models;

/**
 * Class m1x000
 */
class m1x000 extends \JustCoded\WP\ImageOptimizer\core\Migration {
	/**
	 * There are no changes in components structure
	 *
	 * @return bool
	 */
	public function test() {
		// no compatibility issues.
		return false;
	}

	/**
	 * Update DB table
	 * update bytes count columns from varchar to bigint.
	 *
	 * @return boolean
	 */
	public function update() {
		global $wpdb;
		$table_stats       = $wpdb->prefix . models\Media::TABLE_IMAGE_STATS;
		$table_log_details = $wpdb->prefix . models\Log::TABLE_IMAGE_LOG_DETAILS;
		$table_log         = $wpdb->prefix . models\Log::TABLE_IMAGE_LOG;

		$wpdb->query( "ALTER TABLE `{$table_stats}` CHANGE `bytes_before` `bytes_before` BIGINT(20)  NOT NULL;" );
		$wpdb->query( "ALTER TABLE `{$table_stats}` CHANGE `bytes_after` `bytes_after` BIGINT(20)  NOT NULL;" );

		$wpdb->query( "ALTER TABLE `{$table_log_details}` CHANGE `bytes_before` `bytes_before` BIGINT(20)  NOT NULL;" );
		$wpdb->query( "ALTER TABLE `{$table_log_details}` CHANGE `bytes_after` `bytes_after` BIGINT(20)  NOT NULL;" );

		return true;
	}
}

