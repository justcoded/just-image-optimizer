<?php

namespace justimageoptimizer\controllers;

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
		$model->load( $_POST ) && $model->save();
		$this->render( 'settings/index', array(
			'tab'         => 'settings',
			'model'       => $model,
			'sizes'       => $this->image_dimensions(),
			'image_sizes' => maybe_unserialize( get_option( $model::DB_OPT_IMAGE_SIZES ) ),
		) );
	}

	public function image_dimensions() {
		global $_wp_additional_image_sizes;
		$additional_sizes = get_intermediate_image_sizes();
		$sizes            = array();

		// Create the full array with sizes and crop info
		foreach ( $additional_sizes as $_size ) {
			if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {
				$sizes[ $_size ]['width']  = get_option( $_size . '_size_w' );
				$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
				$sizes[ $_size ]['crop']   = (bool) get_option( $_size . '_crop' );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$sizes[ $_size ] = array(
					'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop'   => $_wp_additional_image_sizes[ $_size ]['crop']
				);
			}
		}
		//Medium Large
		if ( ! isset( $sizes['medium_large'] ) || empty( $sizes['medium_large'] ) ) {
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