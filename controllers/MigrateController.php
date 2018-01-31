<?php

namespace JustCoded\WP\ImageOptimizer\controllers;

use JustCoded\WP\ImageOptimizer\models;

/**
 * Class MigrateController
 * Perform migrate operations
 */
class MigrateController extends \JustCoded\WP\ImageOptimizer\core\Component {
	/**
	 * MigrateController constructor.
	 * Init WP hooks
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'initRoutes' ) );
	}

	/**
	 * Replace main menu "Just Custom Fields" with migration page
	 */
	public function initRoutes() {
		$page_title = \JustImageOptimizer::$plugin_name;

		add_submenu_page(
			null,
			$page_title,
			$page_title,
			'manage_options',
			'just-img-opt-migrate',
			array( $this, 'actionIndex' )
		);
	}

	/**
	 * Migration information/form/submit
	 *
	 * @return bool
	 */
	public function actionIndex() {
		$model  = new models\Migrate();
		$errors = "";

		// check that we have something to migrate.
		$version = get_option( 'joi_version' );
		if ( ! version_compare( $version, \JustImageOptimizer::$version, '<' ) ) {
			return $this->actionUpgraded();
		}

		$migrations = $model->findMigrations();

		// check form submit and migrate
		if ( $model->load( $_POST ) ) {
			if ( $model->migrate( $migrations ) ) {
				return $this->actionUpgraded();
			} else {
				$errors = $model->migrate( $migrations );
			}
		} // if no submit we test migrate to show possible warnings
		else {
			$warnings = $model->testMigrate( $migrations );
		}

		return $this->render( 'migrate/index', array(
			'tab'        => 'migrate',
			'migrations' => $migrations,
			'warnings'   => $warnings,
			'errors'     => $errors,
		) );
	}

	/**
	 * Success page
	 *
	 * @return bool
	 */
	public function actionUpgraded() {
		return $this->render( 'migrate/upgraded', array(
			'tab' => 'migrate',
		) );
	}

}

