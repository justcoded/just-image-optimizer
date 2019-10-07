<?php

namespace JustCoded\WP\ImageOptimizer\models;

use JustCoded\WP\ImageOptimizer\includes\Singleton;
use JustCoded\WP\ImageOptimizer\controllers;

/**
 * Class Options
 *
 * @package JustCoded\WP\Imagizer\models
 *
 * @method ImagizerSettings instance() static
 */
class ImagizerSettings {
	use Singleton;

	/**
	 * Imagizer options.
	 *
	 * @var object $options .
	 */
	public $options;

	/**
	 * List of existing converters
	 *
	 * @var array $converters
	 */
	public $converters = array();

	/**
	 * Options constructor.
	 */
	public function __construct() {
		$this->options = (object) get_option( 'imagizer_options' );

		$this->check_system();

		if ( ! empty( $this->options->lazy )
			&& true === $this->options->lazy ) {
			add_action( 'wp_enqueue_scripts', array( controllers\ServiceController::instance(), 'load_lazy_script' ) );
		}

	}

	/**
	 * Check_system
	 * Checks the system for installed converters.
	 *
	 * @return array
	 */
	public function check_system() {
		$converters         = [ 'cwebp', 'imagick', 'gmagick', 'imagickbinary', 'gd' ];
		$this->converters[] = 'cwebp';

		exec( 'php -m', $out );
		$libs = array_intersect( $converters, $out );

		if ( ! empty( $libs ) ) {
			foreach ( $libs as $lib ) {
				$this->converters[] = $lib;
			}

			exec( 'identify -list format', $formats );

			$searchword = 'JP2';
			$matches    = array_filter( $formats, function ( $var ) use ( $searchword ) {
				return preg_match( "/\b$searchword\b/i", $var );
			} );

			if ( empty( $matches ) ) {
				controllers\ServiceController::notice( 'Missing JP2 converter' );
			}
		}

		return $this->converters;
	}

	/**
	 * Get_options
	 *
	 * @return object
	 */
	public static function get_options() {
		return self::instance()->options;
	}

	/**
	 * Page_tabs
	 */
	public static function page_tabs() {
		$current = call_user_func( array( self::instance(), 'current_tab' ) );
		$tabs    = array(
			'processing' => 'Processing',
			'options'    => 'Options',
		);

		$html = '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $tab => $name ) {
			$class = ( $tab === $current ) ? 'nav-tab-active' : '';
			$html  .= '<a class="nav-tab '
				. $class . '" href="?page='
				. controllers\ServiceController::$page_path . '/imagizer-option-page.php&tab='
				. $tab . '&tabs_nonce='
				. wp_create_nonce( '_tabs' ) . '">'
				. $name . '</a>';
		}
		$html .= '</h2>';

		echo $html;
	}

	/**
	 * Current_tab
	 *
	 * @return string|void
	 */
	public function current_tab() {
		$query_model = new QueryModel();
		$current     = $query_model->im_query_vars();

		return ! empty( $current['get']['tab'] ) ? $current['get']['tab'] : 'processing';
	}

	/**
	 * Get_converters
	 *
	 * @return array
	 */
	public function get_converters() {
		return $this->converters;
	}

	/**
	 * Update_options
	 *
	 * @param object $output .
	 */
	public function update_options( $output ) {
		$this->options->images_total      = $output->total;
		$this->options->converted['webp'] = $output->webps;
		$this->options->converted['jp2']  = $output->jp2s;

		update_option( 'imagizer_options', $this->options );
	}

	/**
	 * Uo
	 * Options update indicator.
	 *
	 * @return bool|string
	 */
	public function uo() {
		$query_model = new QueryModel();
		$query_vars  = $query_model->im_query_vars();

		if ( empty( $query_vars['post'] ) ) {
			return '';
		}

		$this->options->amount      = (int) $query_vars['post']['imgzr-amount'];
		$this->options->replacement = ! empty( $query_vars['post']['imgzr-replace'] ) ? true : false;
		$this->options->lazy        = ( ! empty( $query_vars['post']['imgzr-lazy'] )
			&& true === $this->options->replacement ) ? true : false;

		return update_option( 'imagizer_options', $this->options );
	}

}
