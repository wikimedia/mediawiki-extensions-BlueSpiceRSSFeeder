<?php

use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;

class ApiRSSStandardsPagesStore extends BSApiWikiPageStore {

	/**
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
