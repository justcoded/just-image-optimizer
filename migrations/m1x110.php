<?php

namespace JustCoded\WP\ImageOptimizer\migrations;

use JustCoded\WP\ImageOptimizer\models;

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

/**
 * Class m1x000
 */
class m1X110 extends \JustCoded\WP\ImageOptimizer\core\Migration {
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
		$table_conversion   = $wpdb->prefix . models\Log::TABLE_IMAGE_CONVERSION;
		$table_image_log    = $wpdb->prefix . models\Log::TABLE_IMAGE_LOG;
		$table_log_detailed = $wpdb->prefix . models\Log::TABLE_IMAGE_LOG_DETAILS;
		$table_stats        = $wpdb->prefix . models\Media::TABLE_IMAGE_STATS;

		$wpdb->query( "ALTER TABLE `{$table_log_detailed}` ADD COLUMN `image_format` VARCHAR(20) NOT NULL AFTER `image_size`;" );
		$wpdb->query( "ALTER TABLE `{$table_stats}` ADD COLUMN `image_format` VARCHAR(20) NOT NULL AFTER `image_size`;" );
		$wpdb->query( "ALTER TABLE `{$table_image_log}` ADD COLUMN `end_time` DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL AFTER `time`;" );

		$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset} COLLATE {$wpdb->collate}";
		$sql             = "
		CREATE TABLE IF NOT EXISTS {$table_conversion} (
			record_id bigint(20) NOT NULL AUTO_INCREMENT,
			attach_id bigint(20) NOT NULL,
			image_size varchar(255),
			file_size bigint(20) NOT NULL ,
			image_format varchar(20),
			status varchar(20),
			conversion_message varchar(255), 
			upload_path varchar(512),
			converted_path varchar(512),
			creation_time varchar(30),
			update_time varchar(30),
			PRIMARY KEY (record_id),
			KEY (upload_path) 
		)
		{$charset_collate};";

		dbDelta( $sql );

		return true;
	}
}
