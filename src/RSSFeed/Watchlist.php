<?php

namespace BlueSpice\RSSFeeder\RSSFeed;

use ConfigException;
use RSSCreator;
use SpecialPage;
use Title;
use ViewFormElementSelectbox;

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
	public function getViewElement() {
		$watchlistDays = [ 1, 3, 5, 7, 14, 30, 60, 90, 180, 365 ];

		$set = $this->getViewElementFieldset();

		$select = new ViewFormElementSelectbox();
		$select->setId( 'selFeedWatch' );
		$select->setName( 'selFeedWatch' );
		$select->setLabel( $this->getDisplayName()->plain() );

		foreach ( $watchlistDays as $day ) {
			$select->addData( [
				'value' => $this->getFeedURL( [ 'days' => $day ] ),
				'label' => $this->context->msg( 'bs-rssstandards-link-text-watch' )
					->params( $day )->text()
			] );
		}

		$set->addItem( $select );
		$set->addItem( $this->getSubmitButton() );

		return $set;
	}

	/**
	 * @inheritDoc
	 */
	public function getRss() {
		$channel = $this->getChannel();
		if ( $this->user->isAnon() ) {
			return $channel->buildOutput();
		}

		$request = $this->context->getRequest();
		$period = $request->getVal( 'days', 1 );
		$dbr = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$prefix = $this->context->getConfig()->get( 'DBprefix' );
		$conditions = [
			'r.rc_timestamp > ' . $dbr->timestamp( time() - intval( $period * 86400 ) ),
			'w.wl_user = ' . $this->user->getId()
		];
		\Hooks::run( 'BSRSSFeederBeforeGetRecentChanges', [ &$conditions, 'watchlist' ] );

		// phpcs:ignore MediaWiki.Usage.DbrQueryUsage.DbrQueryFound
		$rc = $dbr->query(
			"SELECT r.* FROM {$prefix}watchlist AS w "
			. "INNER JOIN {$prefix}recentchanges AS r "
			. "ON w.wl_namespace = r.rc_namespace AND w.wl_title = r.rc_title "
			. 'WHERE ' . implode( ' AND ', $conditions )
			. ' ORDER BY r.rc_timestamp DESC;'
		);

		foreach ( $rc as $row ) {
			$title = Title::makeTitle( $row->rc_namespace, $row->rc_title );
			if ( !$this->verifyTitle( $title ) ) {
				continue;
			}
			$entry = $this->getEntry( $title, $row );
			$entry->setComments( $title->getTalkPage()->getFullURL() );
			$channel->addItem( $entry );
		}
		$dbr->freeResult( $rc );

		return $channel->buildOutput();
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
			$this->getDescription()->plain()
		);
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
		return 'bs.rssfeeder.handler.watchlist';
	}
}
