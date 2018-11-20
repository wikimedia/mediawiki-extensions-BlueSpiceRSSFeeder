<?php

namespace BlueSpice\RSSFeeder\Hook\BeforePageDisplay;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function skipProcessing() {
		if ( !\SpecialPage::getTitleFor( 'RSSFeeder' )->equals( $this->out->getTitle() ) ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$this->out->addModules( 'ext.bluespice.rssStandards' );
		return true;
	}

}
