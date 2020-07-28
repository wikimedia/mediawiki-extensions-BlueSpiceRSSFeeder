<?php

namespace BlueSpice\RSSFeeder\Hook\ChameleonSkinTemplateOutputPageBeforeExec;

use BlueSpice\Calumma\Hook\ChameleonSkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddToGlobalActions extends ChameleonSkinTemplateOutputPageBeforeExec {
	protected function doProcess() {
		$oSpecialRssFeeder = \MediaWiki\MediaWikiServices::getInstance()
			->getSpecialPageFactory()
			->getPage( 'RSSFeeder' );

		if ( !$oSpecialRssFeeder ) {
			return true;
		}

		$this->mergeSkinDataArray(
			SkinData::GLOBAL_ACTIONS,
			[
				'bs-rssfeeder' => [
					'href' => $oSpecialRssFeeder->getPageTitle()->getFullURL(),
					'text' => $oSpecialRssFeeder->getDescription(),
					'title' => $oSpecialRssFeeder->getPageTitle(),
					'iconClass' => ' icon-rss ',
					'position' => 800,
					'data-permissions' => 'read'
				]
			]
		);

		return true;
	}
}
