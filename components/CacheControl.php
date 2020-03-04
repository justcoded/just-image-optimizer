<?php


namespace JustCoded\WP\ImageOptimizer\components;

/**
 * Class CacheControl
 *
 * @package JustCoded\WP\ImageOptimizer\components
 */
class CacheControl {

	/**
	 * Filesystem
	 *
	 * @var Filesystem $fs .
	 */
	protected $fs;

	/**
	 * CacheControl constructor.
	 */
	public function __construct() {
		$this->fs = Filesystem::instance();

		add_action( 'init', array( $this, 'cache_init' ) );
		add_action( 'admin_init', array( $this, 'super_cache_setup' ) );

		add_action( 'wp_cache_cleared', array( $this, 'clear_cache' ) );
	}

	/**
	 * Super_cache_options
	 *
	 * @return bool
	 */
	public function super_cache_setup() {
		$config_file = WP_CONTENT_DIR . '/wp-cache-config.php';
		$query_vars  = new QueryVars();
		$query_vars->add_dynamic_query_var( 'action' );
		$scupdate = $query_vars->get_query_vars( 'post', 'action' );

		if ( ! defined( 'WPCACHEHOME' ) ) {
			if ( $this->fs->exists( $config_file ) ) {
				$this->fs->delete( $config_file );
			}

			return false;
		}

		if ( 'scupdates' === $scupdate ) {
			if ( $this->fs->exists( $config_file ) ) {
				$config_contents = $this->fs->get_contents( $config_file );

				preg_match( '/(cache-control-config.php)/', $config_contents, $matches );

				if ( empty( $matches[0] ) ) {
					$config_contents = preg_replace( '/(\$file_prefix)/',
						"if ( file_exists( WP_CONTENT_DIR . '/plugins/just-image-optimizer-master/config/cache-control-config.php' ) ) { include( WP_CONTENT_DIR . '/plugins/just-image-optimizer-master/config/cache-control-config.php' ); }\n$1",
						$config_contents );

					$this->fs->put_contents( $config_file, $config_contents, 0755 );
				}
			}
		}
	}

	/**
	 * Cache_init
	 */
	public function cache_init() {
		global $cache_path, $is_chrome, $is_safari;
		if ( ! is_admin() ) {
			if ( true === $is_safari ) {
				if ( ! strpos( $cache_path, 'safari' ) ) {
					$cache_path = $cache_path . 'safari/';
				}
			} elseif ( true === $is_chrome ) {
				if ( ! strpos( $cache_path, 'chrome' ) ) {
					$cache_path = $cache_path . 'chrome/';
				}
			}
		}
	}

	/**
	 * Jio_cache_clear_cache
	 */
	public function clear_cache() {
		global $cache_path;
		$cache_path = str_replace( 'chrome/', '', $cache_path );
		$cache_path = str_replace( 'safari/', '', $cache_path );
		prune_super_cache( $cache_path . 'chrome', true );
		prune_super_cache( $cache_path . 'safari', true );
		prune_super_cache( $cache_path, true );
	}

}
