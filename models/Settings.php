<?php

namespace justimageoptimizer\models;

use justimageoptimizer\core;
/**
 * Class Media
 *
 * Work with settings plugin
 */
class Settings extends core\Model {

	const DB_OPT_IMAGE_SIZES = 'image_sizes';
	const DB_OPT_AUTO_OPTIMIZE = 'auto_optimize';
	const DB_OPT_IMAGE_LIMIT = 'image_limit';
	const DB_OPT_SIZE_LIMIT = 'size_limit';
	const DB_OPT_BEFORE_REGEN = 'before_regen';

	public $image_sizes;
	public $auto_optimize;
	public $image_limit;
	public $size_limit;
	public $before_regen;

	/**
	 * Update options
	 */
	public function save() {
		update_option( self::DB_OPT_IMAGE_SIZES, $this->image_sizes );
		update_option( self::DB_OPT_AUTO_OPTIMIZE, $this->auto_optimize );
		update_option( self::DB_OPT_IMAGE_LIMIT, $this->image_limit );
		update_option( self::DB_OPT_SIZE_LIMIT, $this->size_limit );
		update_option( self::DB_OPT_BEFORE_REGEN, $this->before_regen );
	}

}