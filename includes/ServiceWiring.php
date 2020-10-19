<?php

use BlueSpice\RSSFeeder\RSSFeedManagerFactory;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;

return [
	'BSRSSFeederFeedManagerFactory' => function ( MediaWikiServices $services ) {
		return new RSSFeedManagerFactory(
			LoggerFactory::getInstance( 'rssfeeder' ),
			$services
		);
	}
];
