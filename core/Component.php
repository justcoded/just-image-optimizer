<?php

namespace JustCoded\WP\ImageOptimizer\core;
use JustCoded\WP\ImageOptimizer\models\Settings;

/**
 * Base class for plugin features and components.
 *
 * Contains global methods which can help you inside your features.
 */
class Component {

	/**
	 * Render view file with extracted params
	 *
	 * @param string $path view name to be rendered.
	 * @param array  $data view data.
	 *
	 * @throws \Exception Exception if view file is not found.
	 */
	public function render( $path, $data = null ) {
		$__view = JUSTIMAGEOPTIMIZER_ROOT . '/views/' . $path . '.php';
		if ( ! is_file( $__view ) ) {
			$ml_message = __( '{class}::render() : Unable to load {file}', \JustImageOptimizer::TEXTDOMAIN );
			$ml_message = strtr( $ml_message, array(
				'{class}' => get_class( $this ),
				'{file}'  => $__view,
			) );
			throw new \Exception( $ml_message );
		}

		if ( ! empty( $data ) && is_array( $data ) ) {
			extract( $data );
		}

		include( $__view );
	}

}
