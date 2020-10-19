<?php

namespace BlueSpice\RSSFeeder\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;
use SpecialPage;

class AddDashboardResources extends BeforePageDisplay {

	/**
	 *
	 * @var array
	 */
	protected $pages = [
		"AdminDashboard",
		"UserDashboard",
	];

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		$title = $this->out->getTitle();

		foreach ( $this->pages as $spPage ) {
			if ( !$title->equals( SpecialPage::getTitleFor( $spPage ) ) ) {
				continue;
			}
			return false;
		}
		return true;
	}

	protected function doProcess() {
		$this->out->addModules( 'ext.bluespice.rssFeeder' );
	}

}
