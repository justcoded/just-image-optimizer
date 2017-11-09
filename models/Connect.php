<?php

namespace justimageoptimizer\models;

use justimageoptimizer\core;
/**
 * Class Media
 *
 * Work with connect settings
 */
class Connect extends core\Model {

	const DB_OPT_API_KEY = 'api_key';
	const DB_OPT_SERVICE = 'service';
	const DB_OPT_STATUS = 'connect_status';

	public $api_key;
	public $service;
	public $connect_status;

	/**
	 * Update Settings
	 */
	public function save() {
		if ( get_option( self::DB_OPT_API_KEY ) !== $this->api_key ) {
			update_option( self::DB_OPT_STATUS, $this->connect_status );
		}

		update_option( self::DB_OPT_API_KEY, $this->api_key );
		update_option( self::DB_OPT_SERVICE, $this->service );
		flush_rewrite_rules();
	}
}