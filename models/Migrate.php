<?php
namespace JustCoded\WP\ImageOptimizer\models;

use JustCoded\WP\ImageOptimizer\core;
use JustCoded\WP\ImageOptimizer\core\Migration;

class Migrate extends core\Model {
	/**
	 * Form button name property
	 * Used for $model->load()
	 *
	 * @var string
	 */
	public $upgrade_storage;

	/**
	 * HTML error message with link to admin upgrade page
	 */
	public static function adminUpgradeNotice() {
		$link_text = __( 'Update migrations', \JustImageOptimizer::TEXTDOMAIN );
		$link      = '<a href="' . admin_url( 'upload.php?page=just-img-opt-migrate' ) . '" class="button-primary">' . $link_text . '</a>';

		$warning = __( 'You need to update your migrations to continue using the plugin. {link}', \JustImageOptimizer::TEXTDOMAIN );
		$warning = str_replace( '{link}', $link, $warning );

		printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', $warning );
	}

	/**
	 * Search available migrations
	 * set protected $_version, $_migrations properties
	 *
	 * @return Migration[]
	 */
	public function findMigrations() {
		$version = get_option( 'joi_version' );

		$migrations = array();
		if ( $migration_files = $this->_getMigrationFiles( $version ) ) {
			foreach ( $migration_files as $ver => $file ) {
				$class_name = '\\JustCoded\\WP\\ImageOptimizer\\migrations\\' . preg_replace( '/\.php$/', '', basename( $file ) );

				require_once $file;
				$migrations[ $ver ] = new $class_name();
			}
		}

		return $migrations;
	}

	/**
	 * Scan migrations directory and filter outdated migration based on current version
	 *
	 * @param float $version
	 *
	 * @return array
	 */
	protected function _getMigrationFiles( $version ) {
		$folder = JUSTIMAGEOPTIMIZER_ROOT . '/migrations';
		$files  = scandir( $folder );

		$migrations = array();

		foreach ( $files as $key => $file ) {
			if ( $file == '.' || $file == '..' || ! is_file( $folder . '/' . $file )
			     || ! preg_match( '/^m([\dx]+)/', $file, $match )
			) {
				continue;
			}

			$mig_version = str_replace( 'x', '.', $match[1] );
			if ( version_compare( $mig_version, $version, '<=' ) ) {
				continue;
			}

			$migrations[ $mig_version ] = $folder . '/' . $file;
		}
		ksort( $migrations );

		return $migrations;
	}

	/**
	 * Do test run to check that we can migrate or need to show warnings
	 *
	 * @param Migration[] $migrations
	 *
	 * @return array
	 */
	public function testMigrate( $migrations ) {
		// TODO: remove data.
		$data     = null;
		$warnings = array();

		foreach ( $migrations as $ver => $m ) {
			if ( $warning = $m->runTest( $data ) ) {
				$warnings[ $ver ] = $warning;
			}
			// TODO: we can't run update in test.
			$data = $m->runUpdate( $data );
		}

		return $warnings;
	}

	/**
	 * Run migrations
	 *
	 * @param Migration[] $migrations
	 *
	 * @return boolean
	 */
	public function migrate( $migrations ) {
		if ( ! empty( $migrations ) ) {
			set_time_limit( 0 );

			foreach ( $migrations as $ver => $m ) {
				$m->runUpdate( Migration::MODE_UPDATE );
			}
			update_option( 'joi_version', \JustImageOptimizer::$version );
		}

		return true;
	}
}