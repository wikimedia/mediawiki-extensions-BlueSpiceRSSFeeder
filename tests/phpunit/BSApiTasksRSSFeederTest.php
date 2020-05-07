<?php

use BlueSpice\Tests\BSApiTasksTestBase;

/**
 * @group medium
 * @group Database
 * @group API
 * @group BlueSpice
 * @group BluespiceExtensions
 * @group BlueSpiceRSSFeeder
 */
class BSApiTasksRSSFeederTest extends BSApiTasksTestBase {

	/**
	 *
	 * @return string
	 */
	protected function getModuleName() {
		return 'bs-rssfeeder-tasks';
	}

	/**
	 *
	 * @return array
	 */
	public function getTokens() {
		return $this->getTokenList( self::$users[ 'sysop' ] );
	}

	/**
	 * @covers BSApiTasksRSSFeeder::task_getRSS
	 */
	public function testInvalidUrl() {
		$oData = $this->executeTask(
			'getRSS',
			[
				'url' => 'I want some rss'
			]
		);

		$this->assertFalse( $oData->success, "The API responded success instead of failure" );
		$this->assertNotEmpty( $oData->message, "There was no error message" );
	}

	/**
	 * @covers BSApiTasksRSSFeeder::task_getRSS
	 */
	public function testValidNonWhitelistedUrl() {
		$oData = $this->executeTask(
			'getRSS',
			[
				'url' => 'http://some.rss.de',
				'count' => 12
			]
		);

		$this->assertTrue( $oData->success );
		$this->assertContains(
			'class="error"',
			$oData->payload['html'],
			"There is no error message for the user"
		);
	}

	/**
	 * @covers BSApiTasksRSSFeeder::task_getRSS
	 */
	public function testValidWhitelistedUnreachableUrl() {
		$this->mergeMwGlobalArrayValue( 'wgRSSUrlWhitelist', [ "http://some.rss.de" ] );
		$oData = $this->executeTask(
			'getRSS',
			[
				'url' => 'http://some.rss.de',
				'count' => 12
			]
		);

		$this->assertTrue( $oData->success );
		$this->assertContains(
			'Error',
			$oData->payload['html'],
			"There is no error message for the user"
		);
	}

	/**
	 * @covers BSApiTasksRSSFeeder::task_getRSS
	 */
	public function testActualUrl() {
		$oData = $this->executeTask(
			'getRSS',
			[
				'url' => 'https://blog.bluespice.com/feed/',
				'count' => 12
			]
		);

		$this->assertTrue( $oData->success );
		$this->assertContains(
			'class="plainlinks"',
			$oData->payload['html'],
			"There is rendered RSS"
		);
	}
}
