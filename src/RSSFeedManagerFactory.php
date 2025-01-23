<?php

namespace BlueSpice\RSSFeeder;

use MediaWiki\Context\IContextSource;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\User;
use Psr\Log\LoggerInterface;

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
