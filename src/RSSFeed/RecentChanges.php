<?php

namespace BlueSpice\RSSFeeder\RSSFeed;

use FatalError;
use FeedUtils;
use MWException;
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
	 * @inheritDoc
	 */
	public function getJSHandler() {
		return 'bs.rssfeeder.handler.recentchanges';
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
		\Hooks::run( 'BSRSSFeederBeforeGetRecentChanges', [ &$conditions, 'recentchanges' ] );
		$request = $this->context->getRequest();
		$rcUnique = $request->getInt( 'rc_unique', false );
		if ( $rcUnique ) {
			$conditions[] = $this->getRCUniqueConditions( $conditions );
		}

		return $conditions;
	}

	/**
	 * @param Title $title
	 * @param object $row
	 * @return RSSItemCreator|false
	 */
	protected function getEntry( $title, $row ) {
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

		$res = $dbr->select(
			[ 'recentchanges' ],
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
		$checkbox->setId( 'rcUnique' );
		$checkbox->setName( 'rc_unique' );
		$checkbox->setType( 'checkbox' );
		$checkbox->setValue( $this->getFeedURL( [ 'rc_unique' => true ] ) );
		$checkbox->setLabel(
			$this->context->msg( 'bs-rssfeeder-rcunique-checkbox' )->plain()
		);

		return $checkbox;
	}

	/**
	 * @param array $conditions
	 * @return string
	 */
	protected function getRCUniqueConditions( array $conditions = [] ) {
		$dbr = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$uniqueRecordsIdsResult = $dbr->select(
			[ 'recentchanges' ],
			[ 'MAX(rc_id) as id' ],
			$conditions,
			__METHOD__,
			[
				'GROUP BY' => 'rc_title, rc_namespace',
				'ORDER BY' => 'id DESC',
				'LIMIT' => '10'
			]
		);
		$rcUniqueIds = [];
		foreach ( $uniqueRecordsIdsResult as $rc ) {
			$rcUniqueIds[] = $rc->id;
		}
		$cond = 'rc_id IN (' . implode( ',', $rcUniqueIds ) . ')';
		return $cond;
	}

}
