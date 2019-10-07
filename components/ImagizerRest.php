<?php


namespace JustCoded\WP\ImageOptimizer\components;

use JustCoded\WP\ImageOptimizer\includes\Singleton;
use JustCoded\WP\ImageOptimizer\core\Imagizer;

/**
 * Class Imagizer_Rest
 *
 * @package JustCoded\WP\Imagizer
 *
 * @method ImagizerRest instance() static
 */
class ImagizerRest extends \WP_REST_Controller {
	use Singleton;

	/**
	 * Request parameters
	 *
	 * @var array $params
	 */
	protected $params;

	/**
	 * Imagizer_Rest constructor.
	 */
	public function __construct() {
		$this->namespace = 'imagizer/v1';
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register_routes
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace, '/progress',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_progress' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);
	}

	/**
	 * Get_progress
	 *
	 * @param \WP_REST_Request $request .
	 *
	 * @return \WP_REST_Response
	 */
	public function get_progress( \WP_REST_Request $request ) {
		$progress = Imagizer::progress();

		if ( empty( $progress ) || 0 === $progress ) {
			$progress = 'Conversion stopped';
		}

		$response = array( 'progress' => $progress );

		return new \WP_REST_Response( $response, 200 );
	}

	/**
	 * Check_permission
	 *
	 * @param \WP_REST_Request $request .
	 *
	 * @return bool
	 */
	public function check_permission( \WP_REST_Request $request ) {
		if ( empty( $request->get_headers()['x_wp_nonce'] ) ) {
			return false;
		} else {
			return true;
		}
	}
}
