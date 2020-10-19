<?php

namespace BlueSpice\RSSFeeder\RSSFeed;

use BsNamespaceHelper;
use Hooks;
use Title;
use ViewFormElementSelectbox;

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
	public function getViewElement() {
		$set = $this->getViewElementFieldset();

		$select = new ViewFormElementSelectbox();
		$select->setId( 'selFeedNs' );
		$select->setName( 'selFeedNs' );
		$select->setLabel( $this->getDisplayName()->plain() );

		$namespaces = BsNamespaceHelper::getNamespacesForSelectOptions( [ NS_SPECIAL, NS_MEDIA ] );
		foreach ( $namespaces as $key => $name ) {
			$select->addData( [
				'value' => $this->getFeedURL( [ 'ns' => $key ] ),
				'label' => $name
			] );
		}

		$btn = $this->getSubmitButton();

		$set->addItem( $select );
		$set->addItem( $btn );

		return $set;
	}

	/**
	 * @inheritDoc
	 */
	public function getRss() {
		$request = $this->context->getRequest();
		$nsId = $request->getInt( 'ns', NS_MAIN );
		$titleObject = Title::makeTitle( $nsId, 'Dummy' );

		$conditions = [
			'rc_namespace' => $titleObject->getNamespace(),
		];
		Hooks::run( 'BSRSSFeederBeforeGetRecentChanges', [ &$conditions, 'namespace' ] );
		$rc = $this->getRecentChanges( $conditions );

		$channel = $this->getChannel( $this->context->getLanguage()->getNsText( $nsId ) );
		foreach ( $rc as $row ) {
			$title = Title::makeTitle( $row->rc_namespace, $row->rc_title );
			if ( !$this->verifyTitle( $title ) ) {
				continue;
			}
			$entry = $this->getEntry( $title, $row );
			$entry->setComments( $title->getTalkPage()->getFullURL() );
			$channel->addItem( $entry );
		}

		return $channel->buildOutput();
	}

	/**
	 * @inheritDoc
	 */
	public function getJSHandler() {
		return 'bs.rssfeeder.handler.namespace';
	}
}
