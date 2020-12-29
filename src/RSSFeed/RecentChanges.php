<?php

namespace BlueSpice\RSSFeeder\RSSFeed;

use FeedUtils;
use MediaWiki\MediaWikiServices;
use RSSItemCreator;
use Title;
use ViewFormElementInput;

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
	public function getViewElement() {
		$set = parent::getViewElement();
		$set->addItem( $this->getRCUniqueCheckbox() );
		return $set;
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
	 * @param \stdClass $row
	 * @return string
	 */
	protected function getItemTitle( $title, $row ) {
		return $title->getPrefixedText();
	}

	/**
	 * @return array
	 */
	protected function getConditions() {
		$conditions = $this->getFeedConditions();
		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSRSSFeederBeforeGetRecentChanges',
			[
				&$conditions,
				$this->getId()
			]
		);
		$rcUnique = $this->context->getRequest()->getVal( 'rc_unique', false );
		if ( $rcUnique ) {
			$rcUniqueIds = $this->getUniqueRecentChangesIds( $conditions, 10 );
			$conditions[] = 'rc_id IN (' . implode( ',', $rcUniqueIds ) . ')';
		}
		return $conditions;
	}

	/**
	 * @return array
	 */
	protected function getFeedConditions() {
		return [];
	}

	/**
	 * @param Title $title
	 * @param \stdClass $row
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
	 * @return \stdClass
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

	/**
	 * @return ViewFormElementInput
	 */
	protected function getRCUniqueCheckbox() {
		$checkbox = new ViewFormElementInput();
		$checkbox->setId( 'RcUnique_' . $this->getId() );
		$checkbox->setType( 'checkbox' );
		$checkbox->setLabel(
			$this->context->msg( 'bs-rssfeeder-rcunique-checkbox' )->plain()
		);

		return $checkbox;
	}

	/**
	 * @param array $conditions
	 * @param null $limit
	 * @return array
	 */
	protected function getUniqueRecentChangesIds( $conditions, $limit = null ) {
		$dbr = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$options = [
			'GROUP BY' => 'rc_title, rc_namespace',
			'ORDER BY' => 'id DESC'
		];
		if ( is_numeric( $limit ) ) {
			$options['LIMIT'] = $limit;
		}
		$prefix = $this->context->getConfig()->get( 'DBprefix' );
		$uniqueRecordsIdsResult = $dbr->select(
			[ $prefix . 'recentchanges' ],
			[ 'MAX(rc_id) as id' ],
			$conditions,
			__METHOD__,
			$options
		);
		$rcUniqueIds = [];
		foreach ( $uniqueRecordsIdsResult as $rc ) {
			$rcUniqueIds[] = $rc->id;
		}
		return $rcUniqueIds;
	}
}
