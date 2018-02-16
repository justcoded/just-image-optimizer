<?php

namespace JustCoded\WP\ImageOptimizer\models;

use JustCoded\WP\ImageOptimizer\core;
use JustCoded\WP\ImageOptimizer\services;

/**
 * Class Media
 *
 * Work with connect settings
 */
class Connect extends core\Model {

	const DB_OPT_API_KEY = 'joi_api_key';
	const DB_OPT_SERVICE = 'joi_service';
	const DB_OPT_STATUS  = 'joi_status';

	/**
	 * Service identifier.
	 *
	 * @var string
	 */
	public $service;

	/**
	 * Service credentials
	 *
	 * @var string
	 */
	public $api_key;

	/**
	 * Service connection status
	 *
	 * @var bool
	 */
	public $status;

	/**
	 * Sanitize rules
	 *
	 * @var array
	 */
	protected $sanitize = array(
		'service' => 'key',
		'api_key' => 'text_field',
		'status'  => 'int',
	);

	/**
	 * Construct for Connect model
	 */
	public function __construct() {
		$this->reset();
	}

	/**
	 * Set connect options values
	 */
	public function reset() {
		$this->api_key = get_option( self::DB_OPT_API_KEY );
		$this->service = get_option( self::DB_OPT_SERVICE );
		$this->status  = get_option( self::DB_OPT_STATUS );
	}

	/**
	 * Update Settings
	 *
	 * @return bool
	 */
	public function save() {
		$service = services\ImageOptimizerFactory::create( $this->service, $this->api_key );
		if ( $service && $service->check_api_key() ) {
			update_option( self::DB_OPT_API_KEY, $this->api_key );
			update_option( self::DB_OPT_SERVICE, $this->service );
			update_option( self::DB_OPT_STATUS, '1' );
			$this->reset();

			flush_rewrite_rules();

			return true;
		} else {
			update_option( self::DB_OPT_STATUS, '2' );
			$this->reset();

			return false;
		}
	}

	/**
	 * Check API connect
	 *
	 * @return bool Return true or false.
	 */
	public static function connected() {
		return get_option( self::DB_OPT_STATUS ) === '1';
	}
}
