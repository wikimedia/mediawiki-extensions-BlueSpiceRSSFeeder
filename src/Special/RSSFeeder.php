<?php

namespace BlueSpice\RSSFeeder\Special;

use BlueSpice\RSSFeeder\RSSFeedManager;
use MediaWiki\Context\RequestContext;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\SpecialPage\SpecialPage;
use RSSAuthenticator;
use Wikimedia\AtEase\AtEase;

class RSSFeeder extends SpecialPage {

	public function __construct() {
		parent::__construct( 'RSSFeeder' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $subpage ) {
		if ( $this->getUser()->isAnon() ) {
			// Try to log in user from request
			$authenticator = new RSSAuthenticator( $this->getRequest(), RequestContext::getMain() );
			$userAuthenticated = $authenticator->logInUser();
		} else {
			$userAuthenticated = true;
		}

		parent::execute( $subpage );

		$feedsManager = MediaWikiServices::getInstance()->getService( 'BSRSSFeederFeedManagerFactory' )
			->makeManager( $this->getContext(), $this->getUser() );

		$this->showFeed( $subpage, $feedsManager );

		if ( $userAuthenticated ) {
			$this->showConfig();
		}
	}

	/**
	 * @param string $params
	 * @param RSSFeedManager $feedsManager
	 */
	private function showFeed( $params, RSSFeedManager $feedsManager ) {
		$extension = false;

		if ( $params ) {
			$params = $this->parseParams( $params );
		} else {
			$params = [
				'Page' => $this->getRequest()->getVal( 'Page', '' )
			];
		}
		if ( isset( $params['Page'] ) ) {
			$extension = $params['Page'];
		}

		$requestedFeed = $feedsManager->getFeed( $extension );
		if ( $requestedFeed ) {
			$this->getOutput()->disable();
			AtEase::suppressWarnings();
			header( 'Content-Type: application/xml; charset=UTF-8' );
			echo $requestedFeed->getRss();
			AtEase::restoreWarnings();
			return;
		}
	}

	private function showConfig() {
		$out = $this->getOutput();

		$out->addModuleStyles( 'ext.bluespice.rssfeeder.styles' );
		$out->addModules( [ 'ext.bluespice.rssfeeder.specialRSSFeeder' ] );
		$out->addHTML( Html::element( 'div', [ 'id' => 'bs-rssfeeder-special-rssfeeder-container' ] ) );
	}

	/**
	 * @param string $params
	 * @return array
	 */
	protected function parseParams( $params ) {
		$parsedParams = [];
		$aTokens = explode( '/', $params );
		foreach ( $aTokens as $vKeyValuePairs ) {
			$vKeyValuePairs = explode( ':', $vKeyValuePairs );
			$parsedParams[$vKeyValuePairs[0]] = $vKeyValuePairs[1];
		}
		return $parsedParams;
	}
}
