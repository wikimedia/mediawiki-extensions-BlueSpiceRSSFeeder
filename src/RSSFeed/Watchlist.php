<?php

namespace BlueSpice\RSSFeeder\RSSFeed;

use MediaWiki\Config\ConfigException;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;
use RSSCreator;

class Watchlist extends RecentChanges {

	/**
	 * @inheritDoc
	 */
	public function getId() {
		return 'watchlist';
	}

	/**
	 * @inheritDoc
	 */
	public function getDisplayName() {
		return $this->context->msg( 'bs-rssstandards-title-watch' );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription() {
		return $this->context->msg( 'bs-rssstandards-desc-watch' );
	}

	/**
	 * @inheritDoc
	 */
	public function getRss() {
		$channel = $this->getChannel();
		if ( $this->user->isAnon() ) {
			return $channel->buildOutput();
		}
		$dbr = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$prefix = $this->context->getConfig()->get( 'DBprefix' );
		$conditions = $this->getConditions();

		$rc = $dbr->newSelectQueryBuilder()
			->select( [
				'r.*',
				'rc_comment_text' => 'c.comment_text',
				'rc_comment_data' => 'c.comment_data'
			] )
			->from(
				$prefix . 'watchlist',
				'w'
			)
			->join(
				$prefix . 'recentchanges',
				'r',
				[
					'w.wl_namespace = r.rc_namespace',
					'w.wl_title = r.rc_title'
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

		foreach ( $rc as $row ) {
			$title = Title::makeTitle( $row->rc_namespace, $row->rc_title );
			if ( !$this->verifyTitle( $title ) ) {
				continue;
			}
			$entry = $this->getEntry( $title, $row );
			$talkPageTarget = $this->services->getNamespaceInfo()
				->getTalkPage( $title );
			$talkPage = Title::newFromLinkTarget( $talkPageTarget );
			$entry->setComments( $talkPage->getFullURL() );
			$channel->addItem( $entry );
		}

		return $channel->buildOutput();
	}

	/**
	 * @return array
	 */
	protected function getConditions() {
		$period = $this->context->getRequest()->getVal( 'days', 1 );
		$dbr = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$rcTimestamp = $dbr->timestamp( time() - intval( $period * 86400 ) );

		$rcUnique = $this->context->getRequest()->getVal( 'rc_unique', false );
		if ( $rcUnique ) {
			$rcUniqueIds = $this->getUniqueRecentChangesIds( [ 'rc_timestamp > ' . $rcTimestamp ] );
			$conditions = [ 'w.wl_user = ' . $this->user->getId() ];
			if ( !empty( $rcUniqueIds ) ) {
				$conditions[] = 'rc_id IN (' . implode( ',', $rcUniqueIds ) . ')';
			}
		} else {
			$conditions = [
				'w.wl_user = ' . $this->user->getId(),
				'r.rc_timestamp > ' . $rcTimestamp
			];
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
	 * @param string|null $displayName
	 * @return false|RSSCreator
	 * @throws ConfigException
	 */
	protected function getChannel( $displayName = null ) {
		return RSSCreator::createChannel(
			SpecialPage::getTitleFor( 'Watchlist' ) . ' (' . $this->user->getName() . ')',
			'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
			$this->getDescription()->text()
		);
	}

	/**
	 * @inheritDoc
	 */
	protected function getItemTitle( $title, $row ) {
		return $title->getPrefixedText();
	}
}
