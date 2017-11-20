<?php

namespace justimageoptimizer\models;

use justimageoptimizer\core;
use justimageoptimizer\services;

/**
 * Class Media
 *
 * Work with connect settings
 */
class Connect extends core\Model {

	const DB_OPT_API_KEY = 'joi_api_key';
	const DB_OPT_SERVICE = 'joi_service';
	const DB_OPT_STATUS = 'joi_status';

	public $api_key;
	public $service;
	public $status;

	public function __construct() {
		$this->reset();
	}

	public function reset() {
		$this->api_key = get_option( self::DB_OPT_API_KEY );
		$this->service = get_option( self::DB_OPT_SERVICE );
		$this->status  = get_option( self::DB_OPT_STATUS );
	}


	/**
	 * Update Settings
	 */
	public function save() {
		$service = services\ImageOptimizerFactory::create( $this->service, $this->api_key );
		if ( $service && $service->check_api_key() ) {
			update_option( self::DB_OPT_API_KEY, $this->api_key );
			update_option( self::DB_OPT_SERVICE, $this->service );
			update_option( self::DB_OPT_STATUS, '1' );
			flush_rewrite_rules();
		} else {
			update_option( self::DB_OPT_STATUS, '2' );
		}
		$this->reset();
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