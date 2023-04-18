<?php

/**
 * Copy of CentralAuth's CentralAuthServiceWiringTest.php
 * used to test the ServiceWiring.php file.
 */

namespace BlueSpice\RSSFeeder\Tests;

use MediaWiki\MediaWikiServices;
use MediaWikiIntegrationTestCase;

/**
 * Tests ServiceWiring.php
 *
 * @coversNothing PHPUnit does not support covering annotations for files
 * @group BlueSpiceRSSFeeder
 * @group BlueSpiceExtensions
 * @group BlueSpice
 */
class ServiceWiringTest extends MediaWikiIntegrationTestCase {
	/**
	 * @dataProvider provideService
	 */
	public function testService( string $name ) {
		MediaWikiServices::getInstance()->get( $name );
		$this->addToAssertionCount( 1 );
	}

	public function provideService() {
		$wiring = require __DIR__ . '/../../includes/ServiceWiring.php';
		foreach ( $wiring as $name => $_ ) {
			yield $name => [ $name ];
		}
	}
}
