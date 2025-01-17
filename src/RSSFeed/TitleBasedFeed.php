<?php

namespace BlueSpice\RSSFeeder\RSSFeed;

use MediaWiki\Title\Title;

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
		$pm = $this->services->getPermissionManager();
		return $pm->userCan( 'read', $this->user, $title );
	}
}
