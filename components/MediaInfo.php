<?php

namespace JustCoded\WP\ImageOptimizer\components;

use JustCoded\WP\ImageOptimizer\models\Settings;
use JustCoded\WP\ImageOptimizer\models\Media;

/**
 * Adds option media info boxes
 */
class MediaInfo extends \JustCoded\WP\ImageOptimizer\core\Component {

	/**
	 * Allowed images mime types
	 */
	public $allowed_images = array( 'image/jpeg', 'image/jpg', 'image/png', 'image/gif' );

	/**
	 * Class constructor.
	 * initialize WordPress hooks
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'hook_new_media_columns' ) );
		add_action( 'admin_print_scripts-upload.php', array( $this, 'registerAssets' ) );
		add_action( 'admin_print_scripts-post.php', array( $this, 'registerAssets' ) );
		add_action( 'add_meta_boxes_attachment', array( $this, 'add_optimize_meta_boxes' ), 99 );

		// clean stats on regenerate thumbnails regen.
		add_filter( 'wp_generate_attachment_metadata', array( $this, 'clean_stats' ), 99, 2 );
	}

	/**
	 * Clean attachment statistics with Regenerate Thumbnails
	 *
	 * @param array $metadata Attachment new metadata.
	 * @param int   $attachment_id Attachment ID.
	 *
	 * @return array
	 */
	public function clean_stats( $metadata, $attachment_id ) {
		// Regenerate thumbnails has no any hooks, we can define this only by REST_REQUEST.
		if ( defined( 'REST_REQUEST' )
			&& ! empty( $attachment_id )
			&& (
				// In different cases it send POST or GET request, so we check some keys it submit.
				isset( $_POST['regeneration_args'] )
				|| isset( $_GET['only_regenerate_missing_thumbnails'] )
				|| ( ! empty( $_SERVER['REQUEST_URI'] ) && false !== strpos( $_SERVER['REQUEST_URI'], 'regenerate-thumbnails' ) )
		     )
		) {
			$media = new Media();
			$media->clean_statistics( $attachment_id );
		}
		return $metadata;
	}

	/**
	 * Initialize WordPress media hooks
	 */
	public function hook_new_media_columns() {
		add_filter( 'manage_media_columns', array( $this, 'optimize_column' ) );
		add_action( 'manage_media_custom_column', array( $this, 'optimize_column_display' ), 10, 2 );
		add_filter( 'manage_upload_sortable_columns', array( $this, 'optimize_column_sortable' ) );
	}

	/**
	 * Register Assets
	 */
	public function registerAssets() {
		wp_enqueue_script(
			'just_img_manual_js',
			plugins_url( 'assets/js/optimize.js', dirname( __FILE__ ) ),
			array( 'jquery' )
		);
		wp_enqueue_style( 'just_img_opt_css', plugins_url( 'assets/css/styles.css', dirname( __FILE__ ) ) );
	}

	/**
	 * Add the column.
	 *
	 * @param array $cols Cols Array.
	 *
	 * @return mixed
	 */
	public function optimize_column( $cols ) {
		$cols["optimize"] = "Optimize";

		return $cols;
	}

	/**
	 * Display column content.
	 *
	 * @param string $column_name Column Name.
	 * @param integer $id Attachment id.
	 *
	 * @return mixed
	 */
	public function optimize_column_display( $column_name, $id ) {
		$model = new Media();
		$this->render( 'media/column', array(
			'id'             => $id,
			'column_name'    => $column_name,
			'model'          => $model,
			'allowed_images' => $this->allowed_images,
		) );
	}

	/**
	 * Register the column as sortable & sort by name.
	 *
	 * @param array $cols Cols Array.
	 *
	 * @return mixed
	 */
	public function optimize_column_sortable( $cols ) {
		$cols["optimize"] = "name";

		return $cols;
	}

	/**
	 * Register metabox for media type image.
	 *
	 * @param \WP_Post $post Post object.
	 */
	public function add_optimize_meta_boxes( $post ) {
		if ( ! in_array( get_post_mime_type( $post->ID ), $this->allowed_images ) ) {
			return;
		}
		add_meta_box( 'jri-attachement-meta-info', __( 'Image Sizes' ), array( $this, 'render_meta_info' ) );
	}

	/**
	 * Calculate common divider.
	 *
	 * @param int $a First integer.
	 * @param int $b Second integer.
	 *
	 * @return int
	 */
	public function common_divisor( $a, $b ) {
		$a = (int) $a;
		$b = (int) $b;

		return ( 0 === $b ) ? $a : $this->common_divisor( $b, $a % $b );
	}

	/**
	 * Get meta information and render.
	 *
	 * @param \WP_Post $post Post object.
	 */
	public function render_meta_info( $post ) {
		$meta  = wp_get_attachment_metadata( $post->ID );
		$model = new Media();

		if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
			$meta['gcd']     = $this->common_divisor( $meta['width'], $meta['height'] );
			$meta['x_ratio'] = (int) $meta['width'] / $meta['gcd'];
			$meta['y_ratio'] = (int) $meta['height'] / $meta['gcd'];
			if ( 20 < $meta['x_ratio'] || 20 < $meta['y_ratio'] ) {
				$meta['x_ratio']   = round( $meta['x_ratio'] / 10 );
				$meta['y_ratio']   = round( $meta['y_ratio'] / 10 );
				$meta['avr_ratio'] = true;
			}
		}

		$this->render( 'media/meta-box', array(
				'meta'  => $meta,
				'model' => $model,
				'id'    => $post->ID,
				'allowed_images' => $this->allowed_images,
			)
		);
	}
}
