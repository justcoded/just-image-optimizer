<?php

namespace justimageoptimizer\models;

use justimageoptimizer\core;

/**
 * Class Media
 *
 * Work with settings plugin
 */
class Settings extends core\Model {

	const DB_OPT_IMAGE_SIZES = 'joi_image_sizes';
	const DB_OPT_AUTO_OPTIMIZE = 'joi_auto_optimize';
	const DB_OPT_IMAGE_LIMIT = 'joi_image_limit';
	const DB_OPT_SIZE_LIMIT = 'joi_size_limit';
	const DB_OPT_BEFORE_REGEN = 'joi_before_regen';
	const DB_OPT_SIZE_CHECKED = 'joi_image_sizes_all';

	public $image_sizes;
	public $auto_optimize;
	public $image_limit;
	public $size_limit;
	public $before_regen;
	public $image_sizes_all;


	public function __construct() {
		$this->image_sizes     = get_option( self::DB_OPT_IMAGE_SIZES );
		$this->auto_optimize   = get_option( self::DB_OPT_AUTO_OPTIMIZE );
		$this->image_limit     = get_option( self::DB_OPT_IMAGE_LIMIT );
		$this->size_limit      = get_option( self::DB_OPT_SIZE_LIMIT );
		$this->before_regen    = get_option( self::DB_OPT_BEFORE_REGEN );
		$this->image_sizes_all = get_option( self::DB_OPT_SIZE_CHECKED );
	}

	/**
	 * Update options
	 */
	public function save() {
		update_option( self::DB_OPT_IMAGE_SIZES, $this->image_sizes );
		update_option( self::DB_OPT_IMAGE_LIMIT, $this->image_limit );
		update_option( self::DB_OPT_SIZE_LIMIT, $this->size_limit );
		update_option( self::DB_OPT_BEFORE_REGEN, $this->before_regen );
		update_option( self::DB_OPT_AUTO_OPTIMIZE, $this->auto_optimize );
		update_option( self::DB_OPT_SIZE_CHECKED, $this->image_sizes_all );
	}

}