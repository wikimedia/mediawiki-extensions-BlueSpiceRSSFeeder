<?php

namespace BlueSpice\RSSFeeder\RSSFeed;

use FatalError;
use FeedUtils;
use MediaWiki\MediaWikiServices;
use MWException;
use RSSItemCreator;
use Title;

class RecentChanges extends TitleBasedFeed {

	/**
	 * @inheritDoc
	 */
	public function getId() {
		return 'recentchanges';
	}

	/**
	 * @inheritDoc
	 */
	public function getDisplayName() {
		return $this->context->msg( 'bs-rssfeeder-recent-changes' );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription() {
		return $this->context->msg( 'bs-rssstandards-desc-rc' );
	}

	/**
	 * @inheritDoc
	 */
	public function getRss() {
		$rc = $this->getRecentChanges( $this->getConditions() );
		$channel = $this->getChannel();

		foreach ( $rc as $row ) {
			$title = Title::makeTitle( $row->rc_namespace, $row->rc_title );
			if ( !$this->verifyTitle( $title ) ) {
				continue;
			}
			$channel->addItem( $this->getEntry( $title, $row ) );
		}

		return $channel->buildOutput();
	}

	/**
	 * @param Title $title
	 * @param object $row
	 * @return string
	 */
	protected function getItemTitle( $title, $row ) {
		return $title->getPrefixedText();
	}

	/**
	 * @return array
	 * @throws FatalError
	 * @throws MWException
	 */
	protected function getConditions() {
		$conditions = [];
		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSRSSFeederBeforeGetRecentChanges',
			[
				&$conditions,
				'recentchanges'
			]
		);

		return $conditions;
	}

	/**
	 * @param Title $title
	 * @param object $row
	 * @return RSSItemCreator|false
	 */
	protected function getEntry( $title, $row ) {
		// fake old fields for FeedUtils::formatDiff, because its currently
		// broken for new fields
		$row->rc_comment_text = $row->comment_text;
		$row->rc_comment_data = $row->comment_data;
		$entry = RSSItemCreator::createItem(
			$this->getItemTitle( $title, $row ),
			$title->getFullURL( 'diff=' . $row->rc_this_oldid . '&oldid=prev' ),
			FeedUtils::formatDiff( $row )
		);
		if ( $entry ) {
			$entry->setPubDate( wfTimestamp( TS_UNIX, $row->rc_timestamp ) );
		}

		return $entry;
	}

	/**
	 * @param array $conditions
	 * @return object
	 */
	protected function getRecentChanges( $conditions = [] ) {
		$dbr = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA );

		$conditions[] = 'rc_comment_id = comment_id';
		$res = $dbr->select(
			[ 'recentchanges', 'comment' ],
			[ '*' ],
			$conditions,
			__METHOD__,
			[
				'ORDER BY' => 'rc_timestamp DESC',
				'LIMIT' => '10'
			]
		);

		return $res ?: (object)null;
	}
}
