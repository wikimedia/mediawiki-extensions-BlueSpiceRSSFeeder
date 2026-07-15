<?php

use MediaWiki\Api\ApiMain;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Request\DerivativeRequest;

class BSApiTasksRSSFeeder extends BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = [
		'getRSS' => [
			'examples' => [
				[
					'url' => 'http://some.rss.url',
					'count' => 12
				],
				[
					'url' => 'http://some.rss.url'
				]
			],
			'params' => [
				'url' => [
					'desc' => 'Valid URL to retrieve RSS from',
					'type' => 'string',
					'required' => true
				],
				'count' => [
					'desc' => 'Number of RSS entities to retrieve',
					'type' => 'integer',
					'required' => false,
					'default' => 10
				]
			]
		]
	];

	/**
	 * Methods that can be executed even when the wiki is in read-mode, as
	 * they do not alter the state/content of the wiki
	 * @var array
	 */
	protected $aReadTasks = [
		'getRSS',
	];

	/**
	 * Returns an array of tasks and their required permissions
	 * array( 'taskname' => array('read', 'edit') )
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'getRSS' => [ 'read' ]
		];
	}

	/**
	 * Delivers a rendered RSS feed
	 * @param stdClass $oTaskData contains params
	 * @return stdClass Standard task API return
	 */
	protected function task_getRSS( $oTaskData ) { // phpcs:ignore MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName, Generic.Files.LineLength.TooLong
		$oReturn = $this->makeStandardReturn();

		$iCount = isset( $oTaskData->count )
			? (int)$oTaskData->count
			: 10;
		if ( isset( $oTaskData->url ) && filter_var( $oTaskData->url, FILTER_VALIDATE_URL ) ) {
			$sUrl = $oTaskData->url;
		} else {
			$oReturn->message = wfMessage(
				'bs-rssfeeder-invalid-url'
			)->text();
			return $oReturn;
		}

		$oParserOpts = new ParserOptions( $this->getUser() );
		$iCount = intval( $iCount );

		$sTag = '<rss max="' . $iCount . '">' . $sUrl . '</rss>';

		$params = new DerivativeRequest(
			// Fallback upon $wgRequest if you can't access context.
			$this->getRequest(),
			[
				'action' => 'parse',
				'text' => $sTag,
				'contentmodel' => 'wikitext'
			]
		);
		$api = new ApiMain( $params );
		$api->execute();
		$data = $api->getResult()->getResultData();

		$oReturn->payload['html'] = $data['parse']['text'];
		$oReturn->success = true;
		return $oReturn;
	}

	/**
	 * @inheritDoc
	 */
	public function needsToken() {
		return false;
	}
}
