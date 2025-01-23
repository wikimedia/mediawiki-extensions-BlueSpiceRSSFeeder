<?php

namespace BlueSpice\RSSFeeder\RSSFeed;

use BlueSpice\RSSFeeder\IRSSFeed;
use ConfigException;
use MediaWiki\Context\IContextSource;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\User;
use MWException;
use RSSCreator;
use SpecialPage;
use ViewFormElementButton;
use ViewFormElementFieldset;
use ViewFormElementLabel;

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
	 * @inheritDoc
	 */
	public function getViewElement() {
		$set = $this->getViewElementFieldset();
		$set->addItem( $this->getLabelElement() );
		$set->addItem( $this->getSubmitButton() );

		return $set;
	}

	/**
	 * @return ViewFormElementFieldset
	 */
	protected function getViewElementFieldset() {
		$set = new ViewFormElementFieldset();
		$set->setLabel( $this->getDisplayName()->plain() );

		return $set;
	}

	/**
	 * @return ViewFormElementButton
	 * @throws MWException
	 */
	protected function getSubmitButton() {
		$btn = new ViewFormElementButton();
		$btn->setId( $this->getButtonId() );
		$btn->setName( $this->getButtonId() );
		$btn->setType( 'button' );
		$btn->setValue( $this->getFeedURL() );
		$btn->setLabel( $this->context->msg( 'bs-rssfeeder-submit' )->plain() );

		return $btn;
	}

	/**
	 * @inheritDoc
	 */
	protected function getFeedURL( $params = [] ) {
		return SpecialPage::getTitleFor( 'RSSFeeder' )->getLocalUrl(
			array_merge( [
				'Page' => $this->getId(),
			], $this->getUserAuthInfo(), $params )
		);
	}

	/**
	 * @return ViewFormElementLabel
	 */
	protected function getLabelElement() {
		$label = new ViewFormElementLabel();
		$label->useAutoWidth();
		$label->setFor( $this->getButtonId() );
		$label->setText( $this->getDescription()->plain() );

		return $label;
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

	/**
	 * @return string
	 */
	protected function getButtonId() {
		return $this->getId();
	}

	/**
	 * @return array
	 */
	protected function getUserAuthInfo() {
		return [
			'u' => $this->user->getName(),
			'h' => $this->user->getToken(),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getJSHandler() {
		return '';
	}
}
