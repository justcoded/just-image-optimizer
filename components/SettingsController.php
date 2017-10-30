<?php

namespace justimageoptimizer\components;

use justimageoptimizer\models\Settings;

/**
 * Adds option settings page
 */
class SettingsController extends \justimageoptimizer\core\Component {

	/**
	 * Class constructor.
	 * initialize WordPress hooks
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'init_settings_menu' ) );
	}

	/**
	 * Add new page to the Wordpress Menu
	 */
	public function init_settings_menu() {
		add_submenu_page(
			null,
			__( 'Settings', \justImageOptimizer::TEXTDOMAIN ),
			__( 'Settings', \justImageOptimizer::TEXTDOMAIN ),
			'manage_options',
			'just-img-opt-settings',
			array( $this, 'actionIndex' )
		);
	}

	/**
	 * Render Settings page
	 */
	public function actionIndex() {
		$model = new Settings();
		$model->save( $_POST );
		$this->render( 'settings/index', array(
			'tab'               => 'settings',
			'sizes'             => $this->image_dimensions(),
			'image_sizes'       => maybe_unserialize( get_option( $model::DB_OPT_IMAGE_SIZES ) ),
			'auto_optimize'     => get_option( $model::DB_OPT_AUTO_OPTIMIZE ),
			'image_limit'       => get_option( $model::DB_OPT_IMAGE_LIMIT ),
			'size_limit'        => get_option( $model::DB_OPT_SIZE_LIMIT ),
			'before_regen'      => get_option( $model::DB_OPT_BEFORE_REGEN ),
			'image_sizes_opt'   => $model::DB_OPT_IMAGE_SIZES . '[]',
			'auto_optimize_opt' => $model::DB_OPT_AUTO_OPTIMIZE,
			'image_limit_opt'   => $model::DB_OPT_IMAGE_LIMIT,
			'size_limit_opt'    => $model::DB_OPT_SIZE_LIMIT,
			'before_regen_opt'  => $model::DB_OPT_BEFORE_REGEN,
		) );
	}

	public function image_dimensions() {
		global $_wp_additional_image_sizes;
		$additional_sizes = get_intermediate_image_sizes();
		$sizes = array();

		// Create the full array with sizes and crop info
		foreach( $additional_sizes as $_size ) {
			if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {
				$sizes[ $_size ]['width'] = get_option( $_size . '_size_w' );
				$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
				$sizes[ $_size ]['crop'] = (bool) get_option( $_size . '_crop' );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$sizes[ $_size ] = array(
					'width' => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop' =>  $_wp_additional_image_sizes[ $_size ]['crop']
				);
			}
		}
		//Medium Large
		if ( !isset( $sizes['medium_large'] ) || empty( $sizes['medium_large'] ) ) {
			$width  = intval( get_option( 'medium_large_size_w' ) );
			$height = intval( get_option( 'medium_large_size_h' ) );

			$sizes['medium_large'] = array(
				'width'  => $width,
				'height' => $height
			);
		}

		return $sizes;

	}
}