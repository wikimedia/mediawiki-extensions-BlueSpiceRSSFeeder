<?php

use BlueSpice\Tests\BSApiExtJSStoreTestBase;

/**
 * @group medium
 * @group API
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpiceRSSStandards
 * @group Database
 */
class BSApiRSSStandardsPagesStoreTest extends BSApiExtJSStoreTestBase {

	protected $iFixtureTotal = 3;

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
		return;
	}

	protected function getModuleName() {
		return 'bs-rss-standards-pages-store';
	}

	public function provideSingleFilterData() {
		return [
			'Filter by name' => [ 'string', 'ct', 'page_title', 'RSSStandards', 1 ]
		];
	}

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
	 * @param $limit
	 * @param $offset
	 *
	 * @dataProvider provideQueryData
	 */
	public function testQuery( $sQuery, $sDisplayText, $iPageNamespace, $iTotal ) {
		$aParams = array(
			'action' => $this->getModuleName(),
			'query' => $sQuery
		);

		$results = $this->doApiRequest( $aParams );
		$response = $results[0];

		$this->assertAttributeEquals(
			$iTotal,
			'total',
			(object)$response,
			'Field "total" contains wrong value'
		);

		$this->assertEquals( $response['results'][0]['displayText'], $sDisplayText, 'Display text does not match' );
		$this->assertEquals( $response['results'][0]['page_namespace'], $iPageNamespace, 'Namespace number does not match' );
	}

	public function provideQueryData() {
		return [
			'page title prefix search' => [
				'RSS',
				'RSSStandards Test Page',
				0,
				1
			],
			'page title infix search' => [
				'Standards',
				'RSSStandards Test Page',
				0,
				1
			],
			'page title case insensitiveness' => [
				'rss',
				'RSSStandards Test Page',
				0,
				1
			],
			'namespace and title prefix' => [
				'Help:Just',
				'Help:Just A Test Page',
				NS_HELP,
				1
			],
			'namespace and title infix' => [
				'Help:Test',
				'Help:Just A Test Page',
				NS_HELP,
				1
			],
			'namespace case insensitive' => [
				'help:just',
				'Help:Just A Test Page',
				NS_HELP,
				1
			]
		];
	}

	public function testFeedUrl() {
		$aParams = array(
			'action' => $this->getModuleName(),
			'query' => 'RSSStandards'
		);

		$results = $this->doApiRequest( $aParams );
		$response = $results[0];

		$this->assertAttributeEquals(
			1,
			'total',
			(object)$response,
			'Field "total" contains wrong value'
		);

		$this->assertContains( "Special:RSSFeeder", $response['results'][0]['feedUrl'], 'Link to special page is missing' );
		$this->assertContains( "&Page=", $response['results'][0]['feedUrl'], 'Type info (attribute Page) is missing' );
		$this->assertContains( "&p=", $response['results'][0]['feedUrl'], 'Page info (attribute p) is missing' );
		$this->assertContains( "&ns=", $response['results'][0]['feedUrl'], 'Namespace info (attribute ns) is missing' );
		$this->assertContains( "&u=", $response['results'][0]['feedUrl'], 'User info (attribute u) is missing' );
		$this->assertContains( "&h=", $response['results'][0]['feedUrl'], 'Token info (attribute h) is missing' );
	}
}
