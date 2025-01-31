<?php
// Last review MRG (01.07.11 14:22)
use BlueSpice\RSSFeeder\IRSSFeed;
use BlueSpice\RSSFeeder\RSSFeedManager;
use MediaWiki\Context\RequestContext;
use Wikimedia\AtEase\AtEase;

class SpecialRSSFeeder extends \BlueSpice\SpecialPage {

	public function __construct() {
		parent::__construct( 'RSSFeeder' );
	}

	/**
	 *
	 * @param string $sParameter
	 */
	public function execute( $sParameter ) {
		if ( $this->getUser()->isAnon() ) {
			// Try to log in user from request
			$authenticator = new RSSAuthenticator( $this->getRequest(), RequestContext::getMain() );
			$userAuthenticated = $authenticator->logInUser();
		} else {
			$userAuthenticated = true;
		}

		parent::execute( $sParameter );

		$feedsManager = $this->services->getService( 'BSRSSFeederFeedManagerFactory' )
			->makeManager( $this->getContext(), $this->getUser() );

		$this->showFeed( $sParameter, $feedsManager );

		if ( $userAuthenticated ) {
			$this->showConfig( $feedsManager );
		}
	}

	/**
	 * @param string $sParameter
	 * @param RSSFeedManager $feedsManager
	 */
	private function showFeed( $sParameter, RSSFeedManager $feedsManager ) {
		$extension = false;

		if ( $sParameter ) {
			$sParameter = $this->parseParams( $sParameter );
		} else {
			$sParameter = [
				'Page' => $this->getRequest()->getVal( 'Page', '' )
			];
		}
		if ( isset( $sParameter['Page'] ) ) {
			$extension = $sParameter['Page'];
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

	/**
	 * @param RSSFeedManager $feedsManager
	 */
	private function showConfig( RSSFeedManager $feedsManager ) {
		$feeds = $feedsManager->getFeeds();
		$this->addFeedCallbacks( $feeds );
		$this->getOutput()->addModuleStyles( 'ext.bluespice.rssFeeder.styles' );
		$this->getOutput()->addModules( 'ext.bluespice.rssStandards' );

		$form = new ViewBaseForm();
		$form->setId( 'RSSFeederForm' );
		# $form->setValidationUrl( 'index.php?&action=remote&mod=RSSFeeder&rf=validate' );

		$label = new ViewFormElementLabel();
		$label->useAutoWidth();
		$label->setText( '<h3>' . wfMessage( 'bs-rssfeeder-pagetext' )->plain() . '</h3>' );

		$form->addItem( $label );

		foreach ( $feeds as $feed ) {
			$form->addItem( $feed->getViewElement() );
		}

		$this->getOutput()->addHTML(
			$form->execute()
		);
	}

	/**
	 *
	 * @param string $sParameter
	 * @return array
	 */
	protected function parseParams( $sParameter ) {
		$aParameters = [];
		$aTokens = explode( '/', $sParameter );
		foreach ( $aTokens as $vKeyValuePairs ) {
			$vKeyValuePairs = explode( ':', $vKeyValuePairs );
			$aParameters[$vKeyValuePairs[0]] = $vKeyValuePairs[1];
		}
		return $aParameters;
	}

	/**
	 * @param array $feeds
	 */
	private function addFeedCallbacks( array $feeds ) {
		$cbs = [];
		/** @var IRSSFeed $feed */
		foreach ( $feeds as $feed ) {
			if ( $feed->getJSHandler() ) {
				$cbs[$feed->getId()] = $feed->getJSHandler();
			}
		}

		$this->getOutput()->addJsConfigVars( 'bsRSSFeederFeedCallbacks', $cbs );
	}

}
