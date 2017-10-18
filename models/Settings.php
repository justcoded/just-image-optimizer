<?php

namespace justimageoptimizer\models;

class Settings {

	const DB_OPT_API_KEY = '_just_img_opt_api_key';
	const DB_OPT_SERVICE = '_just_img_opt_service';
	const DB_OPT_STATUS = '_just_img_opt_connect_status';

	const DB_OPT_IMAGE_SIZES = '_just_img_opt_image_sizes';
	const DB_OPT_AUTO_OPTIMIZE = '_just_img_opt_auto_optimize';
	const DB_OPT_IMAGE_LIMIT = '_just_img_opt_image_limit';
	const DB_OPT_SIZE_LIMIT = '_just_img_opt_size_limit';
	const DB_OPT_BEFORE_REGEN = '_just_img_opt_before_regen';

	/**
	 * Update option
	 *
	 * @param array $data Data Array.
	 */
	public function save( $data ) {
		if ( is_array( $data ) ) {
			if ( isset( $data['submit-connect'] ) ) {
				$this->update_google_api_key( $data );
				$this->update_service( $data );
			}
			if ( isset( $data['submit-settings'] ) ) {
				$this->update_auto_optimize( $data );
				$this->update_before_regen( $data );
				$this->update_image_limit( $data );
				$this->update_image_sizes( $data );
				$this->update_size_limit( $data );
			}
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

	/**
	 * Update Image Sizes
	 *
	 * @param array $data Data Array.
	 *
	 * @return mixed
	 */
	protected function update_image_sizes( $data ) {
		if ( isset( $data[ self::DB_OPT_IMAGE_SIZES ] ) ) {
			return update_option( self::DB_OPT_IMAGE_SIZES, maybe_serialize( $data[ self::DB_OPT_IMAGE_SIZES ] ) );
		}

		return update_option( self::DB_OPT_IMAGE_SIZES, '' );
	}

	/**
	 * Update Auto optimize option
	 *
	 * @param array $data Data Array.
	 *
	 * @return mixed
	 */
	protected function update_auto_optimize( $data ) {
		if ( isset( $data[ self::DB_OPT_AUTO_OPTIMIZE ] ) ) {
			return update_option( self::DB_OPT_AUTO_OPTIMIZE, $data[ self::DB_OPT_AUTO_OPTIMIZE ] );
		}

		return update_option( self::DB_OPT_AUTO_OPTIMIZE, '' );
	}

	/**
	 * Update Image Limit option
	 *
	 * @param array $data Data Array.
	 *
	 * @return mixed
	 */
	protected function update_image_limit( $data ) {
		if ( isset( $data[ self::DB_OPT_IMAGE_LIMIT ] ) ) {
			return update_option( self::DB_OPT_IMAGE_LIMIT, $data[ self::DB_OPT_IMAGE_LIMIT ] );
		}

		return false;
	}

	/**
	 * Update Size Limit option
	 *
	 * @param array $data Data Array.
	 *
	 * @return mixed
	 */
	protected function update_size_limit( $data ) {
		if ( isset( $data[ self::DB_OPT_SIZE_LIMIT ] ) ) {
			return update_option( self::DB_OPT_SIZE_LIMIT, $data[ self::DB_OPT_SIZE_LIMIT ] );
		}

		return false;
	}

	/**
	 * Update Before Regen option
	 *
	 * @param array $data Data Array.
	 *
	 * @return mixed
	 */
	protected function update_before_regen( $data ) {
		if ( isset( $data[ self::DB_OPT_BEFORE_REGEN ] ) ) {
			return update_option( self::DB_OPT_BEFORE_REGEN, $data[ self::DB_OPT_BEFORE_REGEN ] );
		}

		return update_option( self::DB_OPT_BEFORE_REGEN, '' );
	}
}