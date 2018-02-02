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
		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . models\OptimizationLog::TABLE_IMAGE_LOG;
		$sql             = "CREATE TABLE $table_name (
			id           mediumint(9) NOT NULL AUTO_INCREMENT,
			attach_id    mediumint(9) NOT NULL,
			size         VARCHAR(255) NOT NULL,
			b_file_size  VARCHAR(255) NOT NULL,
			a_file_size  VARCHAR(255) NOT NULL,
			attach_name	 VARCHAR(255) NOT NULL,
			fail         VARCHAR(255) NOT NULL,
			time         datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		return true;
	}
}

