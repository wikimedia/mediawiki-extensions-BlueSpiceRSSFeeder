<?php

namespace BlueSpice\RSSFeeder\RSSFeed;

use FeedUtils;
use MediaWiki\Title\Title;
use RecentChange;
use RSSItemCreator;

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
		$this->services->getHookContainer()->run(
			'BSRSSFeederBeforeGetRecentChanges',
			[
				&$conditions,
				$this->getId()
			]
		);
		$rcUnique = $this->context->getRequest()->getVal( 'rc_unique', false );
		if ( $rcUnique ) {
			$rcUniqueIds = $this->getUniqueRecentChangesIds( $conditions, 10 );
			if ( !empty( $rcUniqueIds ) ) {
				$conditions[] = 'rc_id IN (' . implode( ',', $rcUniqueIds ) . ')';
			}
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
		$rcQuery = RecentChange::getQueryInfo();
		$res = $dbr->select(
			$rcQuery['tables'],
			$rcQuery['fields'],
			$conditions,
			__METHOD__,
			[
				'ORDER BY' => 'rc_timestamp DESC',
				'LIMIT' => '10'
			],
			$rcQuery['joins']
		);

		return $res ?: (object)null;
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
		$uniqueRecordsIdsResult = $dbr->newSelectQueryBuilder()
			->select( [ 'MAX(rc_id) as id' ] )
			->from( $prefix . 'recentchanges' )
			->join(
				$prefix . 'actor',
				'recentchanges_actor',
				[ $prefix . 'recentchanges.rc_actor = recentchanges_actor.actor_id' ]
			)
			->where( $conditions )
			->groupBy( [ 'rc_title', 'rc_namespace' ] )
			->orderBy( 'id', 'DESC' )
			->caller( __METHOD__ )
			->fetchResultSet();
		$rcUniqueIds = [];
		foreach ( $uniqueRecordsIdsResult as $rc ) {
			$rcUniqueIds[] = $rc->id;
		}
		return $rcUniqueIds;
	}
}
