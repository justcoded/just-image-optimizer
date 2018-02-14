<?php

namespace JustCoded\WP\ImageOptimizer\migrations;

use JustCoded\WP\ImageOptimizer\models;

/**
 * Class m0x200
 */
class m0x200 extends \JustCoded\WP\ImageOptimizer\core\Migration {
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
		$charset_collate  = $wpdb->get_charset_collate();
		$table_name       = $wpdb->prefix . models\Log::TABLE_IMAGE_LOG;
		$table_name_store = $wpdb->prefix . models\Log::TABLE_IMAGE_LOG_STORE;

		$sql_store = "CREATE TABLE $table_name_store (
			store_id       mediumint(9) NOT NULL AUTO_INCREMENT,
			service        VARCHAR(255) NOT NULL,
			image_limit    VARCHAR(255) NOT NULL,
			size_limit     VARCHAR(255) NOT NULL,
			time           datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY (store_id)
		) $charset_collate;";

		$sql_log = "CREATE TABLE $table_name (
			id           mediumint(9) NOT NULL AUTO_INCREMENT,
			attach_id    mediumint(9) NOT NULL,
			try_id		 mediumint(9) NOT NULL,
			image_size   VARCHAR(255) NOT NULL,
			bytes_before VARCHAR(255) NOT NULL,
			bytes_after  VARCHAR(255) NOT NULL,
			attach_name	 VARCHAR(255) NOT NULL,
			status         VARCHAR(255) NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (try_id) REFERENCES $table_name_store(store_id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql_store );
		dbDelta( $sql_log );

		return true;
	}
}

