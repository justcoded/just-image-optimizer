<?php


namespace JustCoded\WP\ImageOptimizer\components;

use JustCoded\WP\ImageOptimizer\includes\Singleton;
use JustCoded\WP\ImageOptimizer\models;
use JustCoded\WP\ImageOptimizer\controllers;

use DOMDocument;

/**
 * Class Replacement
 *
 * @package JustCoded\WP\Imagizer
 *
 * @method Replacement instance() static
 */
class Replacement {
	use Singleton;

	/**
	 * Replacement constructor.
	 */
	public function __construct() {
		add_filter( 'wp_head', array( $this, 'start_buffer' ) );
		add_filter( 'wp_footer', array( $this, 'end_buffer' ) );
	}

	/**
	 * Start_buffer
	 */
	public function start_buffer() {
		if ( true === models\ImagizerSettings::get_options()->replacement
			&& ( 0 !== models\ImagizerSettings::get_options()->converted['webp']
				|| 0 !== models\ImagizerSettings::get_options()->converted['jp2']
			) ) {
			ob_start( array( $this, 'alternate_urls' ) );
		}
	}

	/**
	 * End_buffer
	 */
	public function end_buffer() {
		ob_end_flush();
	}

	/**
	 * Alternate_urls
	 *
	 * @param string $buffer .
	 *
	 * @return string
	 */
	public function alternate_urls( $buffer ) {
		global $is_safari;
		$lazy      = models\ImagizerSettings::get_options()->lazy;
		$extension = 'webp';
		$data_attr = '';

		if ( true === $lazy ) {
			$data_attr = 'data-';
		}

		if ( $is_safari ) {
			$extension = 'jp2';
		}

		libxml_use_internal_errors( true );

		$post = new DOMDocument();

		$post->loadHTML( $buffer );

		$imgs = $post->getElementsByTagName( 'img' );

		foreach ( $imgs as $img ) {
			$class = $img->getAttribute( 'class' );
			$img->setAttribute( 'class', $this->add_lazy_class( $class ) );

			$src    = $img->getAttribute( 'src' );
			$srcset = $img->getAttribute( 'srcset' );

			$matches = Filesystem::instance()->get_real_path( ( empty( $srcset ) ? $src : $srcset ) );

			$check_img = Filesystem::instance()->check_file( $matches, $extension );

			if ( $check_img ) {

				$img->removeAttribute( 'src' );

				// Img src.
				$temp_src = preg_replace( '/(.jpg|.png|.jpeg)/is', '.' . $extension, $src );
				$new_src  = preg_replace( '/uploads/is', 'uploads/' . $extension, $temp_src );

				$img->setAttribute( $data_attr . 'src', $new_src );

				if ( $srcset ) {
					$img->removeAttribute( 'srcset' );

					// Img srcset.
					$temp_srcset = preg_replace( '/(.jpg|.png|.jpeg)/is', '.' . $extension, $srcset );
					$new_srcset  = preg_replace( '/uploads/is', 'uploads/' . $extension, $temp_srcset );

					$img->setAttribute( $data_attr . 'srcset', $new_srcset );
				}
			}
		};

		$pictures = $post->getElementsByTagName( 'picture' );

		foreach ( $pictures as $picture ) {
			$nodes = $picture->getElementsByTagName( 'source' );
			$img   = $picture->lastChild;
			$items = array();

			foreach ( $nodes as $node ) {
				$s = $node->getAttribute( 'srcset' );

				$matches       = Filesystem::instance()->get_real_path( $s );
				$check_picture = Filesystem::instance()->check_file( $matches, $extension );

				$node->removeAttribute( 'srcset' );
				$node->setAttribute( $data_attr . 'srcset', $s );

				if ( $check_picture ) {
					$m = $node->getAttribute( 'media' );

					$el = $post->createElement( 'source' );

					$rp  = preg_replace( '/(.jpg|.png|.jpeg)/is', '.' . $extension, $s );
					$nrp = preg_replace( '/uploads/is', 'uploads/' . $extension, $rp );

					$el->setAttribute( $data_attr . 'srcset', $nrp );
					$el->setAttribute( 'media', $m );
					$el->setAttribute( 'type', 'image/' . $extension );

					$items[] = $el;
				}
			}

			foreach ( $items as $item ) {
				$picture->insertBefore( $item, $img );
			}
		}

		$backs = $post->getElementsByTagName( 'style' );

		foreach ( $backs as $back ) {
			$b = $back->nodeValue;

			preg_match_all( '(\/(\d+)\/(\d+)\/[^"\s\,]*)', $b, $matches );

			$rb  = preg_replace( '/(.jpg|.png|.jpeg)/is', '.' . $extension, $b );
			$nrb = preg_replace( '/uploads/is', 'uploads/' . $extension, $rb );

			$back->nodeValue = $nrb;
		}

		return $post->saveHTML();
	}

	/**
	 * Add_lazy_class
	 *
	 * @param string $classes .
	 *
	 * @return string
	 */
	public function add_lazy_class( $classes ) {
		if ( empty( $classes ) ) {
			return 'lazy';
		}

		$classes = trim( $classes );
		$classes = explode( ' ', $classes );

		if ( true === models\ImagizerSettings::get_options()->lazy ) {
			$classes[] = 'lazy';
		}

		$classes = implode( ' ', $classes );

		return $classes;
	}

}
