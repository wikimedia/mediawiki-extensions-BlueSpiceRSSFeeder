<?php

namespace BlueSpice\RSSFeeder\RSSFeed;

use MediaWiki\Title\Title;

class Category extends RecentChanges {

	/**
	 * period for querying recent changes from db. Days
	 * @var int
	 */
	private $period = 7;

	/**
	 * @inheritDoc
	 */
	public function getId() {
		return 'category';
	}

	/**
	 * @inheritDoc
	 */
	public function getDisplayName() {
		return $this->context->msg( 'bs-rssstandards-title-cat' );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription() {
		return $this->context->msg( 'bs-rssstandards-desc-cat' );
	}

	/**
	 * @inheritDoc
	 */
	public function getRss() {
		$request = $this->context->getRequest();
		$cat = $request->getVal( 'cat', '' );
		$dbr = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$conditions = $this->getConditions();
		$prefix = $this->context->getConfig()->get( 'DBprefix' );
		$rc = $dbr->newSelectQueryBuilder()
			->select( [
				'r.*',
				'rc_comment_text' => 'c.comment_text',
				'rc_comment_data' => 'c.comment_data'
			] )
			->from(
				$prefix . 'categorylinks',
				'catlinks'
			)
			->join(
				$prefix . 'page',
				'p',
				'catlinks.cl_from = p.page_id'
			)
			->join(
				$prefix . 'recentchanges',
				'r',
				[
					'r.rc_namespace = p.page_namespace',
					'r.rc_title = p.page_title'
				]
			)
			->join(
				$prefix . 'comment',
				'c',
				'r.rc_comment_id = c.comment_id'
			)
			->where( $conditions )
			->orderBy( 'r.rc_timestamp', 'DESC' )
			->caller( __METHOD__ )
			->fetchResultSet();
		$channel = $this->getChannel( addslashes( $cat ) );
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
	 * @return array
	 */
	protected function getConditions() {
		$dbr = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$rcTimestamp = $dbr->timestamp( time() - intval( $this->period * 86400 ) );

		$rcUnique = $this->context->getRequest()->getVal( 'rc_unique', false );
		$conditions = [ 'r.rc_timestamp > ' . $rcTimestamp ];
		if ( $rcUnique ) {
			$rcUniqueIds = $this->getUniqueRecentChangesIds( [ 'rc_timestamp > ' . $rcTimestamp ] );
			if ( !empty( $rcUniqueIds ) ) {
				$conditions = [ 'r.rc_id IN (' . implode( ',', $rcUniqueIds ) . ')' ];
			}
		}

		$category = $this->context->getRequest()->getVal( 'cat', '' );
		if ( $category ) {
			$conditions[] = 'catlinks.cl_to = ' . $dbr->addQuotes( $category );
		}

		$this->services->getHookContainer()->run(
			'BSRSSFeederBeforeGetRecentChanges',
			[
				&$conditions,
				$this->getId()
			]
		);

		return $conditions;
	}

	/**
	 * @inheritDoc
	 */
	protected function getItemTitle( $title, $row ) {
		return $title->getPrefixedText();
	}
}
