<?php

namespace BlueSpice\RSSFeeder\RSSFeed;

use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use ViewFormElementSelectbox;

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
	public function getViewElement() {
		$set = $this->getViewElementFieldset();

		$select = new ViewFormElementSelectbox();
		$select->setId( 'selFeedCat' );
		$select->setName( 'selFeedCat' );
		$select->setLabel( $this->getDisplayName()->plain() );

		$categories = $this->getCategories();
		foreach ( $categories as $category ) {
			$select->addData( [
				'value' => $this->getFeedURL( [ 'cat' => $category ] ),
				'label' => $category
			] );
		}

		$set->addItem( $select );
		$set->addItem( $this->getSubmitButton() );
		$set->addItem( $this->getRCUniqueCheckbox() );

		return $set;
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
		// phpcs:ignore MediaWiki.Usage.DbrQueryUsage.DbrQueryFound
		$rc = $dbr->query(
			"SELECT r.* from {$prefix}categorylinks AS c "
			. "INNER JOIN {$prefix}page AS p ON c.cl_from = p.page_id "
			. "INNER JOIN {$prefix}recentchanges AS r "
			. 'ON r.rc_namespace = p.page_namespace AND r.rc_title = p.page_title '
			. 'WHERE ' . implode( ' AND ', $conditions )
			. ' ORDER BY r.rc_timestamp DESC;'
		);

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
			$conditions = [
				'r.rc_id IN (' . implode( ',', $rcUniqueIds ) . ')'
			];
		}

		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSRSSFeederBeforeGetRecentChanges',
			[
				&$conditions,
				$this->getId()
			]
		);

		return $conditions;
	}

	/**
	 * @return array
	 */
	private function getCategories() {
		$categories = [];
		$dbr = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$res = $dbr->select(
			'categorylinks',
			'cl_to',
			[],
			__METHOD__,
			[
				'GROUP BY' => 'cl_to',
				'ORDER BY' => 'cl_to',
			]
		);

		foreach ( $res as $row ) {
			$categories[] = $row->cl_to;
		}

		return $categories;
	}

	/**
	 * @inheritDoc
	 */
	protected function getItemTitle( $title, $row ) {
		return $title->getPrefixedText();
	}

	/**
	 * @inheritDoc
	 */
	public function getJSHandler() {
		return 'bs.rssfeeder.handler.category';
	}
}
