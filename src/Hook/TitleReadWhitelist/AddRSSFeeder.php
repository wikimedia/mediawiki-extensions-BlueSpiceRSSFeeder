<?php

namespace BlueSpice\RSSFeeder\Hook\TitleReadWhitelist;

use BlueSpice\Hook\TitleReadWhitelist;

class AddRSSFeeder extends TitleReadWhitelist {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		return !$this->title->isSpecial( 'RSSFeeder' );
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$this->whitelisted = true;
		return true;
	}

}
