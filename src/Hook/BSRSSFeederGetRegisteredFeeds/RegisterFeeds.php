<?php

namespace BlueSpice\RSSFeeder\Hook\BSRSSFeederGetRegisteredFeeds;

use BlueSpice\RSSFeeder\Hook\BSRSSFeederGetRegisteredFeeds;

class RegisterFeeds extends BSRSSFeederGetRegisteredFeeds {

	protected function doProcess() {
		$rssFeeder = $this->getServices()->getService( 'BSExtensionFactory' )->getExtension(
			'BlueSpiceRSSFeeder'
		);
		$this->registerFeed( 'recentchanges',
			$this->msg( 'bs-rssfeeder-recent-changes' )->plain(),
			$this->msg( 'bs-rssstandards-desc-rc' )->plain(),
			$rssFeeder,
			'buildRssRc',
			null,
			'buildLinksRc'
		);

		$this->registerFeed( 'followOwn',
			$this->msg( 'bs-rssstandards-title-own' )->plain(),
			$this->msg( 'bs-rssstandards-desc-own' )->plain(),
			$rssFeeder,
			'buildRssOwn',
			[],
			'buildLinksOwn'
		);

		$this->registerFeed( 'followPage',
			$this->msg( 'bs-rssstandards-title-page' )->plain(),
			$this->msg( 'bs-rssstandards-desc-page' )->plain(),
			$rssFeeder,
			'buildRssPage',
			[ 'p', 'ns' ],
			'buildLinksPage'
		);

		$this->registerFeed( 'namespace',
			$this->msg( 'bs-ns' )->plain(),
			$this->msg( 'bs-rssstandards-desc-ns' )->plain(),
			$rssFeeder,
			'buildRssNs',
			[ 'ns' ],
			'buildLinksNs'
		);

		$this->registerFeed( 'category',
			$this->msg( 'bs-rssstandards-title-cat' )->plain(),
			$this->msg( 'bs-rssstandards-desc-cat' )->plain(),
			$rssFeeder,
			'buildRssCat',
			[ 'cat' ],
			'buildLinksCat'
		);

		$this->registerFeed( 'watchlist',
			$this->msg( 'bs-rssstandards-title-watch' )->plain(),
			$this->msg( 'bs-rssstandards-desc-watch' )->plain(),
			$rssFeeder,
			'buildRssWatch',
			[ 'days' ],
			'buildLinksWatch'
		);
	}

}
