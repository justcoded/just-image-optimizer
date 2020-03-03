<?php

namespace JustCoded\WP\ImageOptimizer\models;

use JustCoded\WP\ImageOptimizer\core;

/**
 * Class Media
 *
 * Work with settings plugin
 */
class Settings extends core\Model {

	const DB_OPT_IMAGE_SIZES     = 'jio_image_sizes';
	const DB_OPT_AUTO_OPTIMIZE   = 'jio_auto_optimize';
	const DB_OPT_IMAGE_LIMIT     = 'jio_image_limit';
	const DB_OPT_SIZE_LIMIT      = 'jio_size_limit';
	const DB_OPT_BEFORE_REGEN    = 'jio_before_regen';
	const DB_OPT_SIZE_CHECKED    = 'jio_image_sizes_all';
	const DB_OPT_TRIES_COUNT     = 'jio_tries_count';
	const DB_OPT_REPLACEMENT     = 'jio_replacement';
	const DB_OPT_LAZYLOAD        = 'jio_lazyload';
	const DB_OPT_SERVICE_OPTIONS = 'jio_service_options';
	const DB_OPT_SAVED           = 'jio_setting_saved';
	const DB_OPT_SUPER_CACHE     = 'jio_super_cache';

	/**
	 * Optimize all image sizes or not.
	 *
	 * @var bool
	 */
	public $image_sizes_all;

	/**
	 * Image size keys to optimize
	 *
	 * @var array
	 */
	public $image_sizes;

	/**
	 * Automatically optimize new images
	 *
	 * @var bool
	 */
	public $auto_optimize;

	/**
	 * Images limit per one optimization request
	 *
	 * @var int
	 */
	public $image_limit;

	/**
	 * Tries count for optimization request
	 *
	 * @var int
	 */
	public $tries_count;
	/**
	 * Files size limit per one optimization request
	 *
	 * @var int
	 */
	public $size_limit;

	/**
	 * Regenerate thumbnails before optimization
	 *
	 * @var bool
	 */
	public $before_regen;

	/**
	 * Image replacement.
	 *
	 * @var bool
	 */
	public $replacement;

	/**
	 * Lazyload.
	 *
	 * @var bool
	 */
	public $lazyload;

	/**
	 * Service extra options.
	 *
	 * @var array $service_options .
	 */
	public $service_options;

	/**
	 * Checks is plugin settings saved.
	 *
	 * @var bool $settings_saved .
	 */
	public $settings_saved;

	/**
	 * Super cache option
	 *
	 * @var bool $super_cache;
	 */
	public $super_cache;

	/**
	 * Sanitize rules
	 *
	 * @var array
	 */
	protected $sanitize = array(
		'image_sizes_all' => 'int',
		'image_sizes.*'   => 'text_field',
		'auto_optimize'   => 'int',
		'image_limit'     => 'int',
		'size_limit'      => 'int',
		'tries_count'     => 'int',
		'before_regen'    => 'int',
		'replacement'     => 'int',
		'lazyload'        => 'int',
		'settings_saved'  => 'int',
		'super_cache'     => 'int',
	);

	/**
	 * Construct for Settings model
	 */
	public function __construct() {
		$this->reset();
	}

	/**
	 * Set setting options values
	 */
	public function reset() {
		$this->image_sizes     = maybe_unserialize( get_option( self::DB_OPT_IMAGE_SIZES, array() ) );
		$this->auto_optimize   = get_option( self::DB_OPT_AUTO_OPTIMIZE, '1' );
		$this->image_limit     = get_option( self::DB_OPT_IMAGE_LIMIT, 5 );
		$this->size_limit      = get_option( self::DB_OPT_SIZE_LIMIT, 10 );
		$this->tries_count     = get_option( self::DB_OPT_TRIES_COUNT, 5 );
		$this->before_regen    = get_option( self::DB_OPT_BEFORE_REGEN );
		$this->image_sizes_all = get_option( self::DB_OPT_SIZE_CHECKED, '1' );
		$this->replacement     = get_option( self::DB_OPT_REPLACEMENT, '0' );
		$this->lazyload        = get_option( self::DB_OPT_LAZYLOAD, '0' );
		$this->service_options = get_option( self::DB_OPT_SERVICE_OPTIONS, '' );
		$this->super_cache     = get_option( self::DB_OPT_SUPER_CACHE );
		$this->settings_saved  = get_option( self::DB_OPT_SAVED );
	}

	/**
	 * Set attributes processor
	 *
	 * @param array $params Input data.
	 */
	public function set_attributes( $params ) {
		parent::set_attributes( $params );
		if ( empty( $this->image_sizes ) ) {
			$this->image_sizes = array();
		}
	}

	/**
	 * Update options
	 */
	public function save() {
		if (
			true === \JustImageOptimizer::$service->has_options() &&
			! isset( $this->service_options )
		) {
			$this->service_options = \JustImageOptimizer::$service->get_service_options();
		}

		if ( false === \JustImageOptimizer::$service->has_options() ) {
			$this->service_options = '';
		}

		update_option( self::DB_OPT_IMAGE_SIZES, $this->image_sizes );
		update_option( self::DB_OPT_IMAGE_LIMIT, $this->image_limit );
		update_option( self::DB_OPT_SIZE_LIMIT, $this->size_limit );
		update_option( self::DB_OPT_BEFORE_REGEN, $this->before_regen );
		update_option( self::DB_OPT_AUTO_OPTIMIZE, $this->auto_optimize );
		update_option( self::DB_OPT_SIZE_CHECKED, $this->image_sizes_all );
		update_option( self::DB_OPT_TRIES_COUNT, $this->tries_count );
		update_option( self::DB_OPT_REPLACEMENT, $this->replacement );
		update_option( self::DB_OPT_LAZYLOAD, $this->lazyload );
		update_option( self::DB_OPT_SERVICE_OPTIONS, $this->service_options );
		update_option( self::DB_OPT_SUPER_CACHE, $this->super_cache );
		update_option( self::DB_OPT_SAVED, $this->settings_saved );
		$this->reset();

		flush_rewrite_rules();

		return true;
	}

	/**
	 * Check first saved setting options
	 *
	 * @return bool true or false.
	 */
	public function saved() {
		return intval( get_option( self::DB_OPT_SAVED ) ) === 1;
	}

	/**
	 * Check requirements for accesses wp-content.
	 *
	 * @param bool $force_check Ignore caches within requriements check.
	 *
	 * @return bool true or false.
	 */
	public function check_requirements( $force_check = false ) {
		$php_vers   = version_compare( phpversion(), '7.0', '>' );
		$wp_content = wp_is_writable( WP_CONTENT_DIR );
		$service    = \JustImageOptimizer::$service && \JustImageOptimizer::$service->check_connect( $force_check );

		return $php_vers && $wp_content && $service;
	}

}
