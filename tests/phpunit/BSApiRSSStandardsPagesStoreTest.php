<?php

namespace BlueSpice\RSSFeeder\Tests;

use BlueSpice\Tests\BSApiExtJSStoreTestBase;

/**
 * @group medium
 * @group API
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpiceRSSStandards
 * @group Database
 * @covers \ApiRSSStandardsPagesStore
 */
class BSApiRSSStandardsPagesStoreTest extends BSApiExtJSStoreTestBase {

	protected $iFixtureTotal = 2;

	/**
	 *
	 * @return array
	 */
	protected function getStoreSchema() {
		return [
			'page_id' => [
				'type' => 'string'
			],
			'page_namespace' => [
				'type' => 'string'
			],
			'page_title' => [
				'type' => 'string'
			],
			'type' => [
				'type' => 'string'
			],
			'prefixedText' => [
				'type' => 'string'
			],
			'displayText' => [
				'type' => 'string'
			],
			'feedUrl' => [
				'type' => 'string'
			]
		];
	}

	protected function createStoreFixtureData() {
		$this->insertPage( "RSSStandards Test Page" );
		$this->insertPage( "Help:Just A Test Page" );
	}

	/**
	 *
	 * @return string
	 */
	protected function getModuleName() {
		return 'bs-rss-standards-pages-store';
	}

	/**
	 *
	 * @return array
	 */
	public function provideSingleFilterData() {
		return [
			'Filter by name' => [ 'string', 'ct', 'page_title', 'RSSStandards', 1 ]
		];
	}

	/**
	 *
	 * @return array
	 */
	public function provideMultipleFilterData() {
		return [
			'Filter by name and type' => [
				[
					[
						'type' => 'string',
						'comparison' => 'eq',
						'field' => 'type',
						'value' => 'wikitext'
					],
					[
						'type' => 'string',
						'comparison' => 'ct',
						'field' => 'page_title',
						'value' => 'RSSStandards'
					]
				],
				1
			]
		];
	}

	/**
	 * @covers \ApiRSSStandardsPagesStore::makeData
	 * @dataProvider provideQueryData
	 * @param string $sQuery
	 * @param string $prefixedText
	 * @param string $displayText
	 * @param int $iPageNamespace
	 * @param int $iTotal
	 */
	public function testQuery( $sQuery, $prefixedText, $displayText, $iPageNamespace, $iTotal ) {
		$aParams = [
			'action' => $this->getModuleName(),
			'query' => $sQuery
		];

		$results = $this->doApiRequest( $aParams );
		$response = $results[0];

		$this->assertSame(
			$iTotal,
			$response['total'],
			'Field "total" contains wrong value'
		);

		$this->assertEquals(
			$prefixedText,
			$response['results'][0]['prefixedText'],
			'Prefixed text does not match'
		);

		$this->assertEquals(
			$displayText,
			$response['results'][0]['displayText'],
			'Display text does not match'
		);

		$this->assertEquals(
			$iPageNamespace,
			$response['results'][0]['page_namespace'],
			'Namespace number does not match'
		);
	}

	/**
	 *
	 * @return array
	 */
	public function provideQueryData() {
		return [
			'page title prefix search' => [
				'RSS',
				'RSSStandards Test Page',
				'RSSStandards Test Page',
				0,
				1
			],
			'page title infix search' => [
				'Standards',
				'RSSStandards Test Page',
				'RSSStandards Test Page',
				0,
				1
			],
			'page title case insensitiveness' => [
				'rss',
				'RSSStandards Test Page',
				'RSSStandards Test Page',
				0,
				1
			],
			'namespace and title prefix' => [
				'Help:Just',
				'Help:Just A Test Page',
				'Just A Test Page',
				NS_HELP,
				1
			],
			'namespace and title infix' => [
				'Help:Test',
				'Help:Just A Test Page',
				'Just A Test Page',
				NS_HELP,
				1
			],
			'namespace case insensitive' => [
				'help:just',
				'Help:Just A Test Page',
				'Just A Test Page',
				NS_HELP,
				1
			]
		];
	}

	/**
	 * @covers \ApiRSSStandardsPagesStore::makeData
	 */
	public function testFeedUrl() {
		$aParams = [
			'action' => $this->getModuleName(),
			'query' => 'RSSStandards'
		];

		$results = $this->doApiRequest( $aParams );
		$response = $results[0];

		$this->assertSame(
			1,
			$response['total'],
			'Field "total" contains wrong value'
		);

		$this->assertStringContainsString(
			"Special:RSSFeeder",
			$response['results'][0]['feedUrl'],
			'Link to special page is missing'
		);
		$this->assertStringContainsString(
			"&Page=",
			$response['results'][0]['feedUrl'],
			'Type info (attribute Page) is missing'
		);
		$this->assertStringContainsString(
			"&p=",
			$response['results'][0]['feedUrl'],
			'Page info (attribute p) is missing'
		);
		$this->assertStringContainsString(
			"&ns=",
			$response['results'][0]['feedUrl'],
			'Namespace info (attribute ns) is missing'
		);
		$this->assertStringContainsString(
			"&u=",
			$response['results'][0]['feedUrl'],
			'User info (attribute u) is missing'
		);
		$this->assertStringContainsString(
			"&h=",
			$response['results'][0]['feedUrl'],
			'Token info (attribute h) is missing'
		);
	}
}
