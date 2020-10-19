<?php

namespace BlueSpice\RSSFeeder\RSSFeed;

use Title;

abstract class TitleBasedFeed extends FeedBase {
	/**
	 * Make sure title can be read by user
	 *
	 * @param Title|mixed $title
	 * @return bool
	 */
	protected function verifyTitle( $title ) {
		if ( !$title instanceof Title ) {
			return false;
		}
		return $title->userCan( 'read', $this->user );
	}
}
