<?php

namespace BlueSpice\RSSFeeder;

use BlueSpice\ExtensionAttributeBasedRegistry;
use MediaWiki\Context\IContextSource;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\User;
use Psr\Log\LoggerInterface;

class RSSFeedManager {
	/** @var array */
	private $feeds = [];
	/** @var IContextSource */
	private $context;
	/** @var User */
	private $user;
	/** @var bool */
	private $loaded = false;
	/** @var LoggerInterface */
	private $logger;
	/** @var MediaWikiServices */
	private $services;

	/**
	 * @param IContextSource $context
	 * @param User $user
	 * @param LoggerInterface $logger
	 * @param MediaWikiServices $services
	 */
	public function __construct(
		IContextSource $context, User $user, LoggerInterface $logger, MediaWikiServices $services
	) {
		$this->context = $context;
		$this->user = $user;
		$this->logger = $logger;
		$this->services = $services;
	}

	/**
	 * @return IRSSFeed[]
	 */
	public function getFeeds() {
		$this->load();
		return $this->feeds;
	}

	/**
	 * @param string $key
	 * @return IRSSFeed|null
	 */
	public function getFeed( $key ) {
		$this->load();
		return isset( $this->feeds[$key] ) ? $this->feeds[$key] : null;
	}

	/**
	 * @param string $key
	 * @param IRSSFeed $feed
	 */
	public function registerFeed( $key, IRSSFeed $feed ) {
		$this->load();
		$this->feeds[$key] = $feed;
	}

	/**
	 * @param string $key
	 * @return bool false if feed does not exist
	 */
	public function unregisterFeed( $key ) {
		if ( isset( $this->feeds[$key] ) ) {
			unset( $this->feeds[$key] );
			return true;
		}

		return false;
	}

	/**
	 * Load and initialize feeds
	 *
	 * @param bool|null $reload
	 */
	protected function load( $reload = false ) {
		if ( $this->loaded && !$reload ) {
			return;
		}

		$registry = new ExtensionAttributeBasedRegistry( 'BlueSpiceRSSFeederFeeds' );
		foreach ( $registry->getAllKeys() as $key ) {
			$factory = $registry->getValue( $key );
			if ( !is_callable( $factory ) ) {
				$this->logger->error( 'Factory method for {key} is not callable!', [
					'key' => $key
				] );
				continue;
			}
			$feed = call_user_func_array( $factory, [
				$this->context, $this->user, $this->services
			] );
			if ( !$feed instanceof IRSSFeed ) {
				$this->logger->error( 'Expected instance of {expected}, got {actual}', [
					'expected' => IRSSFeed::class,
					'actual' => get_class( $feed )
				] );
				continue;
			}
			$this->feeds[$key] = $feed;
		}
	}
}
