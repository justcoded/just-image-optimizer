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
		$table_stats     = $wpdb->prefix . models\Media::TABLE_IMAGE_STATS;
		$table_posts     = $wpdb->posts;
		$sql             = "CREATE TABLE $table_stats (
			id           BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			attach_id    BIGINT(20) UNSIGNED NOT NULL,
			attach_name	 VARCHAR(255) NOT NULL,
			image_size   VARCHAR(255) NOT NULL,
			bytes_before VARCHAR(255) NOT NULL,
			bytes_after  VARCHAR(255) NOT NULL,
			PRIMARY KEY (id),
			INDEX ix_attach_id(attach_id),
			INDEX ix_attach_name(attach_name),
			INDEX ix_image_size(image_size),
			FOREIGN KEY (attach_id) 
				REFERENCES $table_posts(ID) 
				ON DELETE CASCADE
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		return true;
	}
}

