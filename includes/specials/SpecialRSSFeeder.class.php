<?php
// Last review MRG (01.07.11 14:22)
class SpecialRSSFeeder extends \BlueSpice\SpecialPage {

	public function __construct() {
		parent::__construct( 'RSSFeeder', 'rssfeeder-viewspecialpage' );
	}

	/**
	 *
	 * @param string $sParameter
	 * @return null
	 */
	public function execute( $sParameter ) {
		if ( $this->getUser()->isAnon() ) {
			// Try to log in user from request
			$authenticator = new RSSAuthenticator( $this->getRequest(), \RequestContext::getMain() );
			$authenticator->logInUser();
		}

		parent::execute( $sParameter );
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
		$rssFeeds = RSSFeeder::getRegisteredFeeds();
		if ( $extension && is_array( $rssFeeds[$extension] ) ) {
			$this->getOutput()->disable();
			$runner = $rssFeeds[$extension]['method'];
			header( 'Content-Type: application/xml; charset=UTF-8' );
			echo $rssFeeds[$extension]['object']->$runner( $sParameter );
			return;
		}

		$this->getOutput()->addModuleStyles( 'ext.bluespice.rssFeeder' );

		$form = new ViewBaseForm();
		$form->setId( 'RSSFeederForm' );
		# $form->setValidationUrl( 'index.php?&action=remote&mod=RSSFeeder&rf=validate' );

		$label = new ViewFormElementLabel();
		$label->useAutoWidth();
		$label->setText( '<h3>' . wfMessage( 'bs-rssfeeder-pagetext' )->plain() . '</h3>' );

		$form->addItem( $label );

		foreach ( $rssFeeds as $name => $feed ) {
			$func = $feed['buildLinks'];
			$form->addItem( $feed['object']->$func() );
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

}
