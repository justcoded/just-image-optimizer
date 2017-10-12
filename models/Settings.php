<?php

namespace justimageoptimizer\models;

class Settings {

	const DB_OPT_API_KEY = '_just_img_opt_api_key';
	const DB_OPT_SERVICE = '_just_img_opt_service';
	const DB_OPT_STATUS = '_just_img_opt_connect_status';

	/**
	 * Update option
	 *
	 * @param array $data Data Array.
	 */
	public function save( $data ) {
		if ( is_array( $data ) && isset( $data['submit'] ) ) {
			$this->update_google_api_key( $data );
			$this->update_service( $data );
		}
	}

	/**
	 * Update Google API Key option
	 *
	 * @param array $data Data Array.
	 *
	 * @return mixed
	 */
	protected function update_google_api_key( $data ) {
		if ( isset( $data[ self::DB_OPT_API_KEY ] ) ) {
			if ( get_option( self::DB_OPT_API_KEY ) !== $data[ self::DB_OPT_API_KEY ] ) {
				update_option( self::DB_OPT_STATUS, '' );
			}

			return update_option( self::DB_OPT_API_KEY, $data[ self::DB_OPT_API_KEY ] );
		}

		return false;
	}

	/**
	 * Update Google API Key option
	 *
	 * @param array $data Data Array.
	 *
	 * @return mixed
	 */
	protected function update_service( $data ) {
		if ( isset( $data[ self::DB_OPT_SERVICE ] ) ) {
			return update_option( self::DB_OPT_SERVICE, $data[ self::DB_OPT_SERVICE ] );
		} else {
			return update_option( self::DB_OPT_SERVICE, '' );
		}
	}
}