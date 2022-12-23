<?php

use BlueSpice\RSSFeeder\RSSFeedManagerFactory;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;

// PHP unit does not understand code coverage for this file
// as the @covers annotation cannot cover a specific file
// This is fully tested in ServiceWiringTest.php
// @codeCoverageIgnoreStart

return [
	'BSRSSFeederFeedManagerFactory' => static function ( MediaWikiServices $services ) {
		return new RSSFeedManagerFactory(
			LoggerFactory::getInstance( 'rssfeeder' ),
			$services
		);
	}
];

// @codeCoverageIgnoreEnd
