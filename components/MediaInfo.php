<?php

namespace justimageoptimizer\components;

use justimageoptimizer\models\Settings;

/**
 * Adds option settings page
 */
class MediaInfo extends \justimageoptimizer\core\Component {

	/**
	 * Class constructor.
	 * initialize WordPress hooks
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'hook_new_media_columns' ) );
	}

	public function hook_new_media_columns() {
		add_filter( 'manage_media_columns', array( $this, 'optimize_column' ) );
		add_action( 'manage_media_custom_column', array( $this, 'optimize_column_display' ), 10, 2 );
		add_filter( 'manage_upload_sortable_columns', array( $this, 'optimize_column_sortable' ) );
	}

	/**
	 * Add the column.
	 *
	 * @param array $cols Cols Array.
	 *
	 * @return mixed
	 */
	public function optimize_column( $cols ) {
		$cols["optimize"] = "Optimize";

		return $cols;
	}

	/**
	 * Display column content.
	 *
	 * @param string $column_name Column Name.
	 * @param integer $id Attachment id.
	 *
	 * @return mixed
	 */
	public function optimize_column_display( $column_name, $id ) {
		echo 'Image Optimize';
	}

	/**
	 * Register the column as sortable & sort by name.
	 *
	 * @param array $cols Cols Array.
	 *
	 * @return mixed
	 */
	public function optimize_column_sortable( $cols ) {
		$cols["optimize"] = "name";

		return $cols;
	}
}