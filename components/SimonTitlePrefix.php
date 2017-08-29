<?php

namespace justimageoptimizer\components;

/**
 * Adds option page to configure text, which will be added to all titles on the frontend
 *
 * Just some dummy feature, isn't it? :)
 */
class SimonTitlePrefix extends \justimageoptimizer\core\Component {
	const DB_OPT_NAME = 'simon_says_prefix';

	/**
	 * Class constructor.
	 * initialize WordPress hooks
	 */
	public function __construct() {
		add_filter( 'the_title', array( $this, 'the_title' ) );
		add_action( 'admin_menu', array( $this, 'init_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_setting_api' ) );
	}

	/**
	 * Filter callback for 'the_title'
	 * adds some text from admin setting
	 *
	 * @param string $title post title.
	 *
	 * @return string
	 */
	public function the_title( $title ) {
		$prefix = get_option( self::DB_OPT_NAME, __( 'Simon says: ', \justImageOptimizer::TEXTDOMAIN ) );
		$title  = "$prefix $title";

		return $title;
	}

	/**
	 * Add new page to the Wordpress Menu
	 */
	public function init_admin_menu() {
		add_menu_page(
			__( 'Simon Says', \justImageOptimizer::TEXTDOMAIN ),
			__( 'Simon Says', \justImageOptimizer::TEXTDOMAIN ),
			'manage_options',
			'justimageoptimizer-simon-page',
			array( $this, 'display_admin_page' ),
			''
		);
	}

	/**
	 * Wordpress settings page render
	 */
	public function display_admin_page() {
		$this->render( 'simon/admin-page' );
	}

	/**
	 * Add section For Plugin Settings Page
	 */
	public function register_setting_api() {
		// set settings section.
		add_settings_section( 'justimageoptimizer_simon_settings', __( 'Post Settings', \justImageOptimizer::TEXTDOMAIN ), null, 'justimageoptimizer-simon-page' );

		// set section fields.
		add_settings_field( self::DB_OPT_NAME, __( 'Title Prefix', \justImageOptimizer::TEXTDOMAIN ), array(
			$this,
			'form_input_title',
		), 'justimageoptimizer-simon-page', 'justimageoptimizer_simon_settings' );

		// register settings with settings api.
		register_setting( 'justimageoptimizer-simon-page', self::DB_OPT_NAME );
	}

	/**
	 * Add view _form.php For Plugin Settings Page "Example Form"
	 */
	public function form_input_title() {
		$this->render( 'simon/_form-input-text', array(
			'name'  => self::DB_OPT_NAME,
			'value' => get_option( self::DB_OPT_NAME, __( 'Simon says: ', \justImageOptimizer::TEXTDOMAIN ) ),
		) );
	}

}
