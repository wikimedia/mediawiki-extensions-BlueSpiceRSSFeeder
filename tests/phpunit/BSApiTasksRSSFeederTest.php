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
	protected function getModuleName () {
		return 'bs-rssfeeder-tasks';
	}

	function getTokens () {
		return $this->getTokenList ( self::$users[ 'sysop' ] );
	}

	public function testInvalidUrl() {
		$oData = $this->executeTask(
			'getRSS',
			array(
				'url' => 'I want some rss'
			)
		);

		$this->assertFalse( $oData->success, "The API responded success instead of failure" );
		$this->assertNotEmpty( $oData->message, "There was no error message" );
	}

	public function testValidNonWhitelistedUrl() {
		$oData = $this->executeTask(
			'getRSS',
			array(
				'url' => 'http://some.rss.de',
				'count' => 12
			)
		);

		$this->assertTrue( $oData->success );
		$this->assertContains( 'class="error"', $oData->payload['html'], "There is no error message for the user" );
	}

	public function testValidWhitelistedUnreachableUrl() {
		$this->mergeMwGlobalArrayValue( 'wgRSSUrlWhitelist', [ "http://some.rss.de" ] );
		$oData = $this->executeTask(
			'getRSS',
			array(
				'url' => 'http://some.rss.de',
				'count' => 12
			)
		);

		$this->assertTrue( $oData->success );
		$this->assertContains( 'Error', $oData->payload['html'], "There is no error message for the user" );
	}


	public function testActualUrl() {
		$oData = $this->executeTask(
			'getRSS',
			array(
				'url' => 'http://blog.bluespice.com/feed/',
				'count' => 12
			)
		);

		$this->assertTrue( $oData->success );
		$this->assertContains( 'class="plainlinks"', $oData->payload['html'], "There is rendered RSS" );
	}
}
