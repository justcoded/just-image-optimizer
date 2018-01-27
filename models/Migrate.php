<?php
namespace JustCoded\WP\ImageOptimizer\models;

use JustCoded\WP\ImageOptimizer\core;

class Migrate extends core\Model
{
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
	public static function adminUpgradeNotice()
	{
		$link_text =  __('Update settings', \JustImageOptimizer::TEXTDOMAIN);
		$link = '<a href="'.admin_url('options-general.php?page=jcf_upgrade').'" class="jcf-btn-save button-primary">'.$link_text.'</a>';

		$warning = __('Thank you for upgrading Just Custom Field plugin. You need to update your settings to continue using the plugin. {link}', \JustImageOptimizer::TEXTDOMAIN);
		$warning = str_replace('{link}', $link, $warning);

		printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', $warning );
	}

	/**
	 * Search available migrations
	 * set protected $_version, $_migrations properties
	 *
	 * @return Migration[]
	 */
	public function findMigrations()
	{
		// $version = $this->_dL->getStorageVersion();
		// if ( ! $version ) {
		// 	$version = self::guessVersion();
		// }

		$migrations = array();
		if ( $migration_files = $this->_getMigrationFiles($version) ) {
			foreach ( $migration_files as $ver => $file ) {
				$class_name = '\\jcf\\migrations\\' . preg_replace('/\.php$/', '', basename($file));

				require_once $file;
				$migrations[$ver] = new $class_name();
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
	protected function _getMigrationFiles($version)
	{
		$folder = JUSTIMAGEOPTIMIZER_ROOT . '/migrations';
		$files = scandir($folder);

		$migrations = array();

		foreach ( $files as $key => $file ) {
			if ( $file == '.' || $file == '..' || !is_file($folder . '/' . $file)
			     || ! preg_match('/^m([\dx]+)/', $file, $match)
			) {
				continue;
			}

			$mig_version = str_replace('x', '.', $match[1]);
			if ( version_compare($mig_version, $version, '<=') ) {
				continue;
			}

			$migrations[$mig_version] = $folder . '/' . $file;
		}
		ksort($migrations);

		return $migrations;
	}

	/**
	 * Do test run to check that we can migrate or need to show warnings
	 *
	 * @param Migration[] $migrations
	 * @return array
	 */
	public function testMigrate($migrations)
	{
		$data = null;
		$warnings = array();

		foreach ($migrations as $ver => $m) {
			if ( $warning = $m->runTest( $data ) ) {
				$warnings[$ver] = $warning;
			}
			$data = $m->runUpdate($data);
		}

		return $warnings;
	}

	/**
	 * Run migrations
	 *
	 * @param Migration[] $migrations
	 * @return boolean
	 */
	public function migrate($migrations)
	{
		if ( !empty($migrations) ) {
			set_time_limit(0);

			$data = null;
			foreach ($migrations as $ver => $m) {
				$data = $m->runUpdate($data, \Core\Migration::MODE_UPDATE);
			}
		}
		else {
			$migrations = array();
			$updated = true;
		}

		// do cleanup
		if ( $updated ) {
			foreach ($migrations as $ver => $m) {
				$m->runCleanup();
			}
			return true;
		}
		else {
			$this->addError('Error! Upgrade failed. Please contact us through github to help you and update migration scripts.');
		}
	}
}