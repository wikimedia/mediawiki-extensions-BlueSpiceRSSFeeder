<?php

namespace BlueSpice\RSSFeeder\RSSFeed;

class FollowOwn extends RecentChanges {

	/**
	 * @inheritDoc
	 */
	public function getId() {
		return 'followOwn';
	}

	/**
	 * @inheritDoc
	 */
	public function getDisplayName() {
		return $this->context->msg( 'bs-rssstandards-title-own' );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription() {
		return $this->context->msg( 'bs-rssstandards-desc-own' );
	}

	/**
	 * @inheritDoc
	 */
	protected function getItemTitle( $title, $row ) {
		return $this->context->msg(
			'bs-rssstandards-changes-from'
		)->params( $row->rc_user_text )->text();
	}

	/**
	 * @inheritDoc
	 */
	protected function getFeedConditions() {
		return [ 'recentchanges_actor.actor_user' => $this->user->getId() ];
	}
}
