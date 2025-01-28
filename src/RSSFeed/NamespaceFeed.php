<?php

namespace BlueSpice\RSSFeeder\RSSFeed;

use MediaWiki\Title\Title;

class NamespaceFeed extends RecentChanges {

	/**
	 * @inheritDoc
	 */
	public function getId() {
		return 'namespace';
	}

	/**
	 * @inheritDoc
	 */
	public function getDisplayName() {
		return $this->context->msg( 'bs-ns' );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription() {
		return $this->context->msg( 'bs-rssstandards-desc-ns' );
	}

	/**
	 * @inheritDoc
	 */
	public function getRss() {
		$request = $this->context->getRequest();
		$nsId = $request->getInt( 'ns', NS_MAIN );
		$conditions = $this->getConditions();
		$rc = $this->getRecentChanges( $conditions );

		$channel = $this->getChannel( $this->context->getLanguage()->getNsText( $nsId ) );
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
	protected function getFeedConditions() {
		$request = $this->context->getRequest();
		$nsId = $request->getInt( 'ns', NS_MAIN );
		$titleObject = Title::makeTitle( $nsId, 'Dummy' );

		return [ 'rc_namespace' => $titleObject->getNamespace() ];
	}
}
