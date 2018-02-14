<?php

namespace JustCoded\WP\ImageOptimizer\migrations;

use JustCoded\WP\ImageOptimizer\models;

/**
 * Class m0x100
 */
class m0x100 extends \JustCoded\WP\ImageOptimizer\core\Migration {
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
		$table_name      = $wpdb->prefix . models\Media::TABLE_IMAGE_STATS;
		$sql             = "CREATE TABLE $table_name (
			id           mediumint(9) NOT NULL AUTO_INCREMENT,
			attach_id    mediumint(9) NOT NULL,
			image_size   VARCHAR(255) NOT NULL,
			bytes_before VARCHAR(255) NOT NULL,
			bytes_after  VARCHAR(255) NOT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		return true;
	}
}

