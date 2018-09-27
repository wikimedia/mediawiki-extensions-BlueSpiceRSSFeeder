<?php

namespace BlueSpice\RSSFeeder\Hook\SkinTemplateOutputPageBeforeExec;

use BlueSpice\Hook\SkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddToGlobalActions extends SkinTemplateOutputPageBeforeExec {
	protected function doProcess() {
		$oSpecialRssFeeder = \SpecialPageFactory::getPage( 'RSSFeeder' );

		if( !$oSpecialRssFeeder ) {
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