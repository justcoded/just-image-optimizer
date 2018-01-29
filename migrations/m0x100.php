<?php

namespace JustCoded\WP\ImageOptimizer\migrations;

/**
 * Class m0x100
 */
class m0x100 extends \JustCoded\WP\ImageOptimizer\core\Migration {
	/**
	 * Read data from storage
	 */
	public function readData() {

	}

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
		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'joi_media';
		$sql             = "CREATE TABLE $table_name (
			media_id     mediumint(9) NOT NULL AUTO_INCREMENT,
			attach_id    smallint(5)  NOT NULL,
			media_key    VARCHAR(50)  NOT NULL,
			media_value  VARCHAR(255) NOT NULL,
			UNIQUE KEY media_id (media_id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		return true;
	}

	/**
	 * Run clean up after update
	 */
	public function cleanup() {

	}
}

