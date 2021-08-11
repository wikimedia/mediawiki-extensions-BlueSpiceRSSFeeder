<?php

namespace BlueSpice\RSSFeeder;

use Message;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\RestrictedTextLink;
use SpecialPage;

class GlobalActionsTool extends RestrictedTextLink {

	public function __construct() {
		parent::__construct( [] );
	}

	/**
	 *
	 * @return string
	 */
	public function getId(): string {
		return 'ga-bs-rssfeeder';
	}

	/**
	 *
	 * @return string
	 */
	public function getHref(): string {
		$tool = SpecialPage::getTitleFor( 'RSSFeeder' );
		return $tool->getLocalURL();
	}

	/**
	 *
	 * @return array
	 */
	public function getPermissions(): array {
		return [ 'read' ];
	}

	/**
	 *
	 * @return Message
	 */
	public function getTitle(): Message {
		return Message::newFromKey( 'bs-rssfeeder-desc' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getText(): Message {
		return Message::newFromKey( 'rssfeeder' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getAriaLabel(): Message {
		return Message::newFromKey( 'rssfeeder' );
	}

}
