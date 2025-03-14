<?php

use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;

/**
 * Provides rss-standards extjs store api for BlueSpice.
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
 * For further information visit https://bluespice.com
 *
 * @author     Leonid Verhovskij <verhovskij@hallowelt.com>
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

class ApiRSSStandardsPagesStore extends BSApiWikiPageStore {

	/**
	 *
	 * @param \stdClass $oRow
	 * @return \stdClass|bool
	 */
	public function makeDataSet( $oRow ) {
		$oSpecialRSS = SpecialPage::getTitleFor( 'RSSFeeder' );
		$sUserName   = $this->getUser()->getName();
		$sUserToken  = $this->getUser()->getToken();
		$oTitle = Title::newFromID( $oRow->page_id );
		$prefixedText = $oTitle->getPrefixedText();
		$displayText = $oTitle->getText();
		$sFeedLink = $oSpecialRSS->getLinkUrl(
			[
				'Page' => 'followPage',
				'p'    => $oRow->page_title,
				'ns'   => $oRow->page_namespace,
				'u'    => $sUserName,
				'h'    => $sUserToken
			]
		);

		$oRow->type = 'wikipage';
		$oRow->prefixedText = $prefixedText;
		$oRow->displayText = $displayText;
		$oRow->feedUrl = $sFeedLink;

		return parent::makeDataSet( $oRow );
	}
}
