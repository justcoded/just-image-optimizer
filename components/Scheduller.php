<?php


namespace JustCoded\WP\ImageOptimizer\components;

use JustCoded\WP\ImageOptimizer\core\Imagizer;

/**
 * Class Scheduller
 *
 * @package JustCoded\WP\Imagizer
 *
 * @method Scheduller instance() static
 */
class Scheduller extends Imagizer {

	/**
	 * Imagize_file
	 *
	 * @param int $attachment_id .
	 *
	 * @throws \WebPConvert\Convert\Exceptions\ConversionFailedException
	 */
	public function imagize_file( $attachment_id ) {

		$attachment = get_post( $attachment_id, OBJECT );
		$original   = UPLOADS_ROOT . $this->fs->get_real_path( $attachment->guid );

		$siblings = $this->get_siblings( [ $attachment ] );

		$this->create_images( $original );

		if ( 0 !== count( $siblings[0] ) ) {
			foreach ( $siblings[0] as $sibling ) {
				$this->create_images( $sibling );
			}
		}
	}

	/**
	 * Get_siblings
	 *
	 * @param array $attachments .
	 *
	 * @return array
	 */
	protected function get_siblings( $attachments ) {
		$siblings = array();

		foreach ( $attachments as $attachment ) {
			$siblings[] = $this->check_siblings( $attachment->guid );
		}

		return $siblings;
	}

	/**
	 * Check_siblings
	 *
	 * @param string $file .
	 *
	 * @return array|false
	 */
	protected function check_siblings( $file ) {
		$original = $this->fs->get_real_path( $file );
		$path     = pathinfo( $original );

		$image_siblings = glob( UPLOADS_ROOT . $path['dirname'] . '/' . $path['filename'] . '-*.' . $path['extension'] );

		return $image_siblings;
	}
}
