<?php

namespace JustCoded\WP\ImageOptimizer\models;

use JustCoded\WP\ImageOptimizer\core;

/**
 * Class Media
 *
 * Work with attachment images statistics
 */
class Media extends core\Model {

	const DB_META_IMAGES_STATS = '_just_img_meta_stats';
	const DB_META_IMAGE_DU = '_just_img_meta_du';
	const DB_META_IMAGE_SAVING = '_just_img_meta_saving';
	const DB_META_IMAGE_SAVING_PERCENT = '_just_img_meta_saving_percent';

	const DB_OPT_SIZES_BEFORE = '_just_img_opt_sizes_before';
	const DB_OPT_SAVING_SIZE = '_just_img_opt_saving_size';

	/**
	 * Set before images statistics
	 *
	 * @var array $before_optimize_stats
	 */
	public $before_optimize_stats = array();

	/**
	 * Set after images statistics
	 *
	 * @var array $after_optimize_stats
	 */
	public $after_optimize_stats = array();

	/**
	 * Set before image statistics main attach
	 *
	 * @var array $before_main_attach_stats
	 */
	public $before_main_attach_stats = array();

	/**
	 * Set after image statistics main attach
	 *
	 * @var array $after_main_attach_stats
	 */
	public $after_main_attach_stats = array();

	/**
	 * Arguments query array to use.
	 *
	 * @var array $query_args
	 */
	protected $query_args = array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'post_mime_type' => array( 'image/jpg', 'image/jpeg', 'image/gif', 'image/png' ),
		'posts_per_page' => - 1,
		'orderby'        => 'id',
		'order'          => 'ASC',
	);

	public $saving_size;

	/**
	 * Media constructor.
	 */
	public function __construct() {
		$this->saving_size = ( get_option( self::DB_OPT_SAVING_SIZE ) ? get_option( self::DB_OPT_SAVING_SIZE ) : 0 );
	}

	/**
	 * Update options
	 *
	 * @param int $id Attach ID.
	 */
	public function save( $id ) {
		$this->set_sizes_stats( $id );
		$this->set_stats_main_attach( $id );
	}

	/**
	 * Update option for saving size after optimization
	 */
	public function set_saving_size() {
		update_option( self::DB_OPT_SAVING_SIZE, $this->get_saving_size() + $this->saving_size );
	}

	/**
	 * Update option for before optimize sizes
	 */
	public function set_before_sizes() {
		update_option( self::DB_OPT_SIZES_BEFORE, $this->get_images_disk_usage() );
	}

	/**
	 * Set array statistics
	 *
	 * @param int $attach_id Attachment ID.
	 */
	public function set_sizes_stats( $attach_id ) {
		$stats = array(
			'percent_stats' => $this->get_saving_size_stats( $attach_id ),
			'size_stats'    => $this->get_percent_saving_stats( $attach_id ),
		);
		update_post_meta( $attach_id, self::DB_META_IMAGES_STATS, maybe_serialize( $stats ) );
	}

	/**
	 * Set stats for main attach image
	 *
	 * @param int $attach_id Attachment ID.
	 */
	public function set_stats_main_attach( $attach_id ) {
		$saving_size    = $this->before_main_attach_stats[ $attach_id ] - $this->after_main_attach_stats[ $attach_id ];
		$saving_percent = round( ( $saving_size / $this->before_main_attach_stats[ $attach_id ] ) * 100, 2 );
		update_post_meta( $attach_id, self::DB_META_IMAGE_DU, size_format( $this->after_main_attach_stats[ $attach_id ] ) );
		update_post_meta( $attach_id, self::DB_META_IMAGE_SAVING, size_format( $saving_size ) );
		update_post_meta( $attach_id, self::DB_META_IMAGE_SAVING_PERCENT, $saving_percent . '%' );
	}

	/**
	 * Clear statistics after Regenerate Thumbnails
	 *
	 * @param int $attach_id Attachment ID.
	 */
	public function clean_statistics( $attach_id ) {
		$stats = array(
			'percent_stats' => '',
			'size_stats'    => '',
		);
		update_post_meta( $attach_id, self::DB_META_IMAGES_STATS, maybe_serialize( $stats ) );
		update_post_meta( $attach_id, self::DB_META_IMAGE_DU, '' );
		update_post_meta( $attach_id, self::DB_META_IMAGE_SAVING, '' );
		update_post_meta( $attach_id, self::DB_META_IMAGE_SAVING_PERCENT, '' );
		update_post_meta( $attach_id, '_just_img_opt_queue', 1 );
	}

	/**
	 * Get all date upload dir
	 *
	 * @return array
	 */
	public static function get_uploads_path() {
		$path = array();
		// TODO: check this with multisite.
		foreach ( glob( wp_upload_dir()['basedir'] . '/*', GLOB_ONLYDIR ) as $upload ) {
			foreach ( glob( $upload . '/*', GLOB_ONLYDIR ) as $upload_dir ) {
				$path[] = $upload_dir;
			}
		}

		return $path;
	}

	/**
	 * Get filesize attachment in Kb
	 *
	 * @param string $attach_file Attachment file.
	 *
	 * @return float|int
	 */
	public function get_filesize( $attach_file ) {
		$attach_filesize = filesize( $attach_file );

		return $attach_filesize;
	}


	/**
	 * Array map callback method
	 *
	 * @param array $key Stats size key.
	 * @param array $before_stats Stats before optimize.
	 * @param array $after_stats Stats after optimize.
	 *
	 * @return float|int|array
	 */
	public function size_map_callback( $key, $before_stats, $after_stats ) {
		return [ $key => ( $before_stats - $after_stats ) ];
	}

	/**
	 * Get size stats after optimize for each size in KB
	 *
	 * @param int $attach_id Attachment ID.
	 *
	 * @return float|null|array
	 */
	public function get_saving_size_stats( $attach_id ) {
		$size_stats = array();
		if ( ! empty( $this->before_optimize_stats[ $attach_id ]['b_stats'] ) ) {
			$size_stats = array_map(
				array(
					$this,
					'size_map_callback',
				),
				array_keys( $this->before_optimize_stats[ $attach_id ]['b_stats'] ),
				$this->before_optimize_stats[ $attach_id ]['b_stats'],
				$this->after_optimize_stats[ $attach_id ]['a_stats']
			);

			return $size_stats;
		}

		return $size_stats;
	}

	/**
	 * Array map callback method
	 *
	 * @param array $key Stats size key.
	 * @param array $size_stats Stats optimize in KB.
	 * @param array $before_stats Stats before optimize.
	 *
	 * @return array
	 */
	public function percent_map_callback( $key, $size_stats, $before_stats ) {
		return [ $key => round( ( $size_stats[ $key ] / $before_stats ) * 100, 2 ) ];
	}

	/**
	 * Get saving percent after optimize for each size
	 *
	 * @param int $attach_id Attachment ID.
	 *
	 * @return float|int|array
	 */
	public function get_percent_saving_stats( $attach_id ) {
		$percent_stats = array();
		$size_stats    = $this->get_saving_size_stats( $attach_id );
		$before_stats  = $this->before_optimize_stats[ $attach_id ]['b_stats'];
		if ( ! empty( $this->before_optimize_stats[ $attach_id ]['b_stats'] ) ) {
			$percent_stats = array_map(
				array(
					$this,
					'percent_map_callback',
				),
				array_keys( $before_stats ),
				$size_stats,
				$before_stats
			);

			return $percent_stats;
		}

		return $percent_stats;
	}

	/**
	 * Get total filesizes attachments in bytes
	 *
	 * @param int $id Attachment ID.
	 * @param bool $stats For get total size = false or sizes array = true.
	 *
	 * @return int|float|boolean|array
	 */
	public function get_total_filesizes( $id, $stats = false ) {
		global $wp_filesystem;
		WP_Filesystem();
		$total_size  = 0;
		$sizes_array = array();
		$attachments = wp_get_attachment_metadata( $id );
		$get_path    = $this->get_uploads_path();
		if ( ! $attachments ) {
			return 0;
		}
		foreach ( $attachments['sizes'] as $size_key => $attachment ) {
			foreach ( $get_path as $path ) {
				if ( $wp_filesystem->exists( $path . '/' . $attachment['file'] ) ) {
					$sizes_array[ $size_key ] = $this->get_filesize( $path . '/' . $attachment['file'] );
				}
			}
		}
		foreach ( $sizes_array as $size ) {
			$total_size = $total_size + $size;
		}
		if ( true === $stats ) {
			return $sizes_array;
		} else {
			return $total_size;
		}
	}

	/**
	 * Get count additional sizes images
	 *
	 * @param int $id Attachment ID.
	 *
	 * @return float|null
	 */
	public function get_count_images( $id ) {
		$count        = 0;
		$sizes        = array();
		$get_metadata = wp_get_attachment_metadata( $id );
		if ( $get_metadata ) {
			foreach ( $get_metadata['sizes'] as $size ) {
				$sizes[] = $size;
			}
			$count = count( $sizes );

			return $count;
		}

		return $count;
	}

	/**
	 * Get additional sizes images
	 *
	 * @return array
	 */
	public static function image_dimensions() {
		global $_wp_additional_image_sizes;
		$additional_sizes = get_intermediate_image_sizes();
		$sizes            = array();

		// Create the full array with sizes and crop info.
		foreach ( $additional_sizes as $_size ) {
			if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {
				$sizes[ $_size ]['width']  = get_option( $_size . '_size_w' );
				$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
				$sizes[ $_size ]['crop']   = (bool) get_option( $_size . '_crop' );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$sizes[ $_size ] = array(
					'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
				);
			}
		}
		// Medium Large.
		if ( ! isset( $sizes['medium_large'] ) || empty( $sizes['medium_large'] ) ) {
			$width  = intval( get_option( 'medium_large_size_w' ) );
			$height = intval( get_option( 'medium_large_size_h' ) );

			$sizes['medium_large'] = array(
				'width'  => $width,
				'height' => $height,
			);
		}

		return $sizes;

	}

	/**
	 * Get statistics for image sizes
	 *
	 * @param int $id Attachment id.
	 * @param string $key Image size key.
	 *
	 * @return array
	 */
	public function get_stats( $id, $key ) {
		$stats_array = array();
		$stats       = maybe_unserialize( get_post_meta( $id, self::DB_META_IMAGES_STATS, true ) );
		if ( isset( $stats['size_stats'] ) ) {
			foreach ( $stats as $stat_key => $stat ) {
				if ( is_array( $stat ) ) {
					foreach ( $stat as $sizes ) {
						foreach ( $sizes as $size_key => $size_stat ) {
							if ( $size_key === $key ) {
								if ( 'percent_stats' === $stat_key ) {
									$stats_array[ $size_key ]['percent_stats'] = $size_stat;
								}
								if ( 'size_stats' === $stat_key ) {
									$stats_array[ $size_key ]['size_stats'] = $size_stat;
								}
							}
						}
					}
				}
			}

			return $stats_array;
		}

		return $stats_array;
	}

	/**
	 * Get Count Images with status in_queue or count all images
	 *
	 * @param bool $all Check all images or in queue.
	 *
	 * @return int
	 */
	public function get_images_stat( $all = false ) {
		$args = $this->query_args;
		if ( false === $all ) {
			$args['meta_query'] = array(
				array(
					'key'   => '_just_img_opt_queue',
					'value' => '1',
				),
			);
		}
		$query = new \WP_Query( $args );

		return $query->post_count;
	}

	/**
	 * Get Total image size
	 *
	 * @return int
	 */
	public function get_images_disk_usage() {
		$disk_usage = 0;
		$args       = $args = $this->query_args;
		$query      = new \WP_Query( $args );
		while ( $query->have_posts() ) {
			$query->the_post();
			$disk_usage = $disk_usage + $this->get_total_filesizes( get_the_ID(), false );
		}

		return round( $disk_usage / 1048576, 2 );
	}

	/**
	 * Get count images with status processed
	 *
	 * @return int
	 */
	public function get_count_images_processed() {
		$args               = $args = $this->query_args;
		$args['meta_query'] = array(
			array(
				'key'   => '_just_img_opt_queue',
				'value' => '3',
			),
		);
		$query              = new \WP_Query( $args );

		return $query->post_count;
	}

	/**
	 * Get sizes images with status processed
	 *
	 * @return int
	 */
	public function get_size_images_processed() {
		$disk_usage         = 0;
		$args               = $args = $this->query_args;
		$args['meta_query'] = array(
			array(
				'key'   => '_just_img_opt_queue',
				'value' => '3',
			),
		);
		$query              = new \WP_Query( $args );
		while ( $query->have_posts() ) {
			$query->the_post();
			$disk_usage = $disk_usage + $this->get_total_filesizes( get_the_ID(), false );
		}

		return (int) number_format_i18n( $disk_usage / 1048576 );
	}

	/**
	 * Get count images in queue from total count
	 *
	 * @return int|float
	 */
	public function get_in_queue_image_count() {
		return $this->get_images_stat( true ) - $this->get_count_images_processed();
	}

	/**
	 * Get disk space sizes from total size
	 *
	 * @return int|float
	 */
	public function get_saving_size() {
		$before_sizes = get_option( self::DB_OPT_SIZES_BEFORE );
		$total_size   = $this->get_images_disk_usage();
		if ( $before_sizes ) {
			return $before_sizes - $total_size;
		} else {
			return 0;
		}
	}

	/**
	 * Get saving sizes from space size
	 *
	 * @return int|float
	 */
	public function get_disk_space_size() {
		$total_size = $this->get_images_disk_usage();
		if ( 0 !== $this->saving_size ) {
			return $total_size - $this->saving_size;
		} else {
			return $total_size;
		}

	}

	/**
	 * Get Saving image percent
	 *
	 * @return int|float
	 */
	public function get_saving_percent_dashboard() {
		if ( $this->get_images_disk_usage() ) {
			return round( ( $this->saving_size / $this->get_images_disk_usage() ) * 100, 2 );
		} else {
			return 0;
		}
	}

	/**
	 * Check size limit images optimization
	 *
	 * @param array $attach_ids Array attach ids.
	 *
	 * @return array Array attach ids.
	 */
	public function size_limit( array $attach_ids ) {
		$size_limit = 0;
		$size_array = array();
		$array_ids  = array();
		// TODO: wrong condition for image sizes. you should limit always, even if "all" is selected.
		if ( is_array( \JustImageOptimizer::$settings->image_sizes ) && '0' !== \JustImageOptimizer::$settings->size_limit ) {
			foreach ( $attach_ids as $attach_id ) {
				$size_array[ $attach_id ] = $this->get_total_filesizes( $attach_id, true );
			}
			foreach ( \JustImageOptimizer::$settings->image_sizes as $value_size ) {
				foreach ( $attach_ids as $attach_id ) {
					if ( ! empty( $size_array[ $attach_id ][ $value_size ] ) ) {
						$size_limit              = $size_limit + $size_array[ $attach_id ][ $value_size ];
						$array_ids[ $attach_id ] = $attach_id;
					}
					if ( number_format_i18n( $size_limit / 1048576 ) >= \JustImageOptimizer::$settings->size_limit ) {
						break;
					}
				}
			}

			return $array_ids;
		}

		return $attach_ids;
	}

}
