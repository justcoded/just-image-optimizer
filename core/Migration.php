<?php

namespace JustCoded\WP\ImageOptimizer\core;

/**
 * Class Migration
 * Used as base class for all specific-version migrations
 * Contains generic functions to speed-up migrations development
 *
 * @package jcf\core
 */
abstract class Migration
{
	const MODE_TEST = 'test';
	const MODE_UPDATE = 'update';

	/**
	 * @var array
	 */
	protected $data;

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
	public function __construct()
	{
	}

	/**
	 * This method read Data in a unique way for this particular version
	 *
	 * @return void
	 */
	abstract protected function readData();

	/**
	 * Test data compatibility with newer version
	 *
	 * @return array
	 */
	abstract protected function test();

	/**
	 * Update $data property to the latest version
	 *
	 * @return void
	 */
	abstract protected function update();

	/**
	 * Function to be called to remove old settings after update
	 */
	protected function cleanup(){}

	/**
	 * Check if migration run in test mode (no need to cleanup old settings)
	 *
	 * @return bool
	 */
	public function isTestMode()
	{
		return $this->mode !== self::MODE_UPDATE;
	}

	/**
	 * Run compatibility data test
	 *
	 * @param array|null $data
	 *
	 * @return array
	 */
	public function runTest( $data )
	{
		$this->setData($data);

		return $this->test();
	}

	/**
	 * Update data to match new format
	 *
	 * @param array|null $data
	 * @param string     $mode
	 *
	 * @return array
	 */
	public function runUpdate( $data, $mode = 'test' )
	{
		$this->setData($data);
		$this->mode = ($mode == self::MODE_UPDATE) ? self::MODE_UPDATE : self::MODE_TEST;

		$this->updated = $this->update();

		return $this->data;
	}

	/**
	 * Run clean up of old data after update
	 */
	public function runCleanup()
	{
		if ( $this->updated ) {
			$this->cleanup();
		}
	}

	/**
	 * Set data from input or read data from settings if input parameter is empty
	 *
	 * @param array|null $data
	 *
	 * @return array
	 */
	public function setData( $data )
	{
		if ( ! empty($data) ) {
			$this->data = $data;
		} else {
			$this->readData();
		}
	}
}

