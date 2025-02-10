<?php

namespace BlueSpice\RSSFeeder\RSSFeed;

use BlueSpice\RSSFeeder\IRSSFeed;
use MediaWiki\Config\ConfigException;
use MediaWiki\Context\IContextSource;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\User;
use RSSCreator;

abstract class FeedBase implements IRSSFeed {
	/** @var IContextSource */
	protected $context;
	/** @var User */
	protected $user;
	/** @var MediaWikiServices */
	protected $services;

	/**
	 * @param IContextSource $context
	 * @param User $user
	 * @param MediaWikiServices $services
	 */
	public function __construct(
		IContextSource $context, User $user, MediaWikiServices $services
	) {
		$this->context = $context;
		$this->user = $user;
		$this->services = $services;
	}

	/**
	 * @inheritDoc
	 */
	public static function factory(
		IContextSource $context, User $user, MediaWikiServices $services
	) {
		return new static( $context, $user, $services );
	}

	/**
	 * @param string|null $displayName
	 * @return false|RSSCreator
	 * @throws ConfigException
	 */
	protected function getChannel( $displayName = null ) {
		if ( !$displayName ) {
			$displayName = $this->getDisplayName()->plain();
		}
		$sitename = $this->services->getMainConfig()->get( 'Sitename' );
		return RSSCreator::createChannel(
			RSSCreator::xmlEncode( $sitename . ' - ' . $displayName ),
			'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
			$this->getDescription()->plain()
		);
	}
}
