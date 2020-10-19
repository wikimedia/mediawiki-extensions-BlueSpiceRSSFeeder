<?php

namespace BlueSpice\RSSFeeder;

use IContextSource;
use MediaWiki\MediaWikiServices;
use Psr\Log\LoggerInterface;
use User;

class RSSFeedManagerFactory {
	/** @var LoggerInterface */
	private $logger;
	/** @var MediaWikiServices */
	private $services;

	/**
	 * @param LoggerInterface $logger
	 * @param MediaWikiServices $services
	 */
	public function __construct( LoggerInterface $logger, MediaWikiServices $services ) {
		$this->logger = $logger;
		$this->services = $services;
	}

	/**
	 * @param IContextSource $context
	 * @param User $user
	 * @return RSSFeedManager
	 */
	public function makeManager( IContextSource $context, User $user ) {
		return new RSSFeedManager( $context, $user, $this->logger, $this->services );
	}
}
