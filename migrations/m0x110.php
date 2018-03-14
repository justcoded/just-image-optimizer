<?php

namespace JustCoded\WP\ImageOptimizer\migrations;

use JustCoded\WP\ImageOptimizer\models;

/**
 * Class m0x110
 */
class m0x110 extends \JustCoded\WP\ImageOptimizer\core\Migration {
	/**
	 * There are no changes in components structure
	 *
	 * @return bool
	 */
	public function test() {
		// no compatibility issues
		return false;
	}

	/**
	 * Update DB table
	 *
	 * @return boolean
	 */
	public function update() {
		global $wpdb;
		$charset_collate   = $wpdb->get_charset_collate();
		$table_log_details = $wpdb->prefix . models\Log::TABLE_IMAGE_LOG_DETAILS;
		$table_log         = $wpdb->prefix . models\Log::TABLE_IMAGE_LOG;
		$table_posts       = $wpdb->posts;

		$sql_log = "CREATE TABLE $table_log (
			request_id     BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			service        VARCHAR(255) NOT NULL,
			image_limit    VARCHAR(255) NOT NULL,
			size_limit     VARCHAR(255) NOT NULL,
			time           datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			info           LONGTEXT,
			PRIMARY KEY (request_id)
		) $charset_collate;";

		$sql_details = "CREATE TABLE $table_log_details (
			id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			request_id	  BIGINT(20) UNSIGNED NOT NULL,
			attach_id     BIGINT(20) UNSIGNED NOT NULL,
			image_size    VARCHAR(255) NOT NULL,
			bytes_before  VARCHAR(255) NOT NULL,
			bytes_after   VARCHAR(255) NOT NULL,
			attach_name	  VARCHAR(255) NOT NULL,
			status        VARCHAR(255) NOT NULL,
			PRIMARY KEY (id),
			INDEX ix_request_id(request_id),
			INDEX ix_attach_id(attach_id),
			FOREIGN KEY (request_id) 
				REFERENCES $table_log(request_id) 
				ON DELETE CASCADE,
			FOREIGN KEY (attach_id) 
				REFERENCES $table_posts(ID) 
				ON DELETE CASCADE
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql_log );
		dbDelta( $sql_details );

		return true;
	}
}

