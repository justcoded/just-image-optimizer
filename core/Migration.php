<?php

namespace JustCoded\WP\ImageOptimizer\core;

/**
 * Class Migration
 * Used as base class for all specific-version migrations
 * Contains generic functions to speed-up migrations development
 *
 * @package JustCoded\WP\ImageOptimizer\core
 */
abstract class Migration {
	const MODE_TEST = 'test';
	const MODE_UPDATE = 'update';

	/**
	 * @var string  test|update
	 */
	protected $mode;

	/**
	 * @var boolean
	 */
	protected $updated;


	/**
	 * Migration constructor.
	 * Init main settings, which were similar to all versions
	 */
	public function __construct() {
	}

	/**
	 * Test data compatibility with newer version
	 *
	 * @return array
	 */
	abstract protected function test();

	/**
	 * Update to the latest version
	 *
	 * @return void
	 */
	abstract protected function update();

	/**
	 * Check if migration run in test mode (no need to cleanup old settings)
	 *
	 * @return bool
	 */
	public function isTestMode() {
		return $this->mode !== self::MODE_UPDATE;
	}

	/**
	 * Run compatibility data test
	 *
	 * @return array
	 */
	public function runTest() {

		return $this->test();
	}

	/**
	 * Update data to match new format
	 *
	 * @param string $mode
	 *
	 * @return \bool
	 */
	public function runUpdate( $mode = 'test' ) {
		$this->mode = ( $mode == self::MODE_UPDATE ) ? self::MODE_UPDATE : self::MODE_TEST;

		$this->updated = $this->update();

		return true;
	}
}

