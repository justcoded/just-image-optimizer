<?php
namespace JustCoded\WP\ImageOptimizer\models;

use JustCoded\WP\ImageOptimizer\core;

class Migrate extends core\Model {
	/**
	 * Form button name property
	 * Used for $model->load()
	 *
	 * @var string
	 */
	public $upgrade_storage;

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
		$data     = null;
		$warnings = array();

		foreach ( $migrations as $ver => $m ) {
			if ( $warning = $m->runTest( $data ) ) {
				$warnings[ $ver ] = $warning;
			}
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
		$updated = '';
		if ( ! empty( $migrations ) ) {
			set_time_limit( 0 );

			$data = null;
			foreach ( $migrations as $ver => $m ) {
				$data = $m->runUpdate( $data, \JustCoded\WP\ImageOptimizer\core\Migration::MODE_UPDATE );
			}
			update_option( 'joi_version', \JustImageOptimizer::$version );
		} else {
			$migrations = array();
			$updated    = true;
		}

		// do cleanup
		if ( $updated ) {
			foreach ( $migrations as $ver => $m ) {
				$m->runCleanup();
			}

			return true;
		} else {
			return 'Error! Upgrade failed. Please contact us through github to help you and update migration scripts.';
		}
	}
}