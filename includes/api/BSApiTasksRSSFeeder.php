<?php
/**
 * Provides the RSSFeeder api for BlueSpice.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 */

/**
 * ShoutBox Api class
 * @package BlueSpice_Extensions
 */
class BSApiTasksRSSFeeder extends BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = array(
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
	);

	/**
	 * Methods that can be executed even when the wiki is in read-mode, as
	 * they do not alter the state/content of the wiki
	 * @var array
	 */
	protected $aReadTasks = array(
		'getRSS',
	);

	/**
	 * Returns an array of tasks and their required permissions
	 * array( 'taskname' => array('read', 'edit') )
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return array(
			'getRSS' => array( 'read' )
		);
	}

	/**
	 * Delivers a rendered RSS feed
	 * @param stdClass $oTaskData contains params
	 * @return stdClass Standard task API return
	 */
	protected function task_getRSS( $oTaskData ) {
		global $wgParser;
		$oReturn = $this->makeStandardReturn();

		$iCount = isset( $oTaskData->count )
			? (int) $oTaskData->count
			: 10
		;
		if ( isset( $oTaskData->url ) && filter_var( $oTaskData->url, FILTER_VALIDATE_URL ) ) {
			$sUrl = $oTaskData->url;
		} else {
			$oReturn->message = wfMessage(
				'bs-rssfeeder-invalid-url'
			)->plain();
			return $oReturn;
		}

		$oParserOpts = new ParserOptions;
		$iCount = intval( $iCount );

		$sTag = '<rss max="' . $iCount . '">' . $sUrl . '</rss>';

		$params = new DerivativeRequest(
			$this->getRequest(), // Fallback upon $wgRequest if you can't access context.
			array(
				'action' => 'parse',
				'text' => $sTag,
				'contentmodel' => 'wikitext'
			)
		);
		$api = new ApiMain( $params );
		$api->execute();
		$data = $api->getResult()->getResultData();

		$oReturn->payload['html'] = $data['parse']['text'];
		$oReturn->success = true;
		return $oReturn;
	}

	public function needsToken() {
		return false;
	}
}