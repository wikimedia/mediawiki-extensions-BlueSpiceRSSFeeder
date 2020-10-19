<?php

namespace BlueSpice\RSSFeeder\RSSFeed;

use Title;
use ViewTagElement;

class FollowPage extends RecentChanges {

	/**
	 * @inheritDoc
	 */
	public function getId() {
		return 'followPage';
	}

	/**
	 * @inheritDoc
	 */
	public function getDisplayName() {
		return $this->context->msg( 'bs-rssstandards-title-page' );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription() {
		return $this->context->msg( 'bs-rssstandards-desc-page' );
	}

	/**
	 * @inheritDoc
	 */
	public function getViewElement() {
		$set = $this->getViewElementFieldset();

		$div = new ViewTagElement();
		$div->setAutoElement( 'div' );
		$div->setId( 'divFeedPage' );

		$btn = $this->getSubmitButton();

		$set->addItem( $div );
		$set->addItem( $btn );
		return $set;
	}

	/**
	 * @inheritDoc
	 */
	public function getRss() {
		$request = $this->context->getRequest();
		$page = $request->getVal( 'p', '' );
		$nsId = $request->getInt( 'ns', 0 );
		$titleObject = Title::makeTitle( $nsId, $page );

		$conditions = [
			'rc_namespace' => $titleObject->getNamespace(),
			'rc_title' => $titleObject->getDBkey(),
		];
		\Hooks::run( 'BSRSSFeederBeforeGetRecentChanges', [ &$conditions, 'followPage' ] );
		$rc = $this->getRecentChanges( $conditions );

		$channel = $this->getChannel( $titleObject->getPrefixedText() );
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
	protected function getItemTitle( $title, $row ) {
		return $this->context->msg(
			'bs-rssstandards-changes-from'
		)->params( $row->rc_user_text )->text();
	}

	/**
	 * @inheritDoc
	 */
	public function getJSHandler() {
		return 'bs.rssfeeder.handler.page';
	}
}
