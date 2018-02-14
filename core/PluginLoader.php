<?php
namespace JustCoded\WP\ImageOptimizer\core;

use JustCoded\WP\ImageOptimizer\models;

/**
 * Class PluginLoader
 * Perform version check and show migration warning if needed
 */
class PluginLoader
{
	/**
	 * Init joi plugin Media DB
	 */
	public function init_db() {
		$migrate    = new models\Migrate;
		$migrate->migrate( $migrate->findMigrations() );
	}

	/**
	 * Check plugin version
	 *
	 * @return bool
	 */
	public function check_migrations_available() {
		if ( version_compare( \JustImageOptimizer::$opt_version, \JustImageOptimizer::$version, '<' ) ) {
			// print notice if we're not on migrate page
			if ( empty( $_GET['page'] ) || $_GET['page'] != 'just-img-opt-migrate' ) {
				add_action( 'admin_notices', array(
					'\JustCoded\WP\ImageOptimizer\models\Migrate',
					'adminUpgradeNotice',
				) );
			}

			return true;
		}

		return false;
	}

}