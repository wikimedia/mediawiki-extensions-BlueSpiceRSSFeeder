<?php
/**
 * This is the RSSStandards class.
 *
 * The RSSStandards provides various feeds
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-design.hk>
 * @package    Bluespice_Extensions
 * @subpackage RSSStandards
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

// TODO SU (04.07.11 10:37): Userhash für alle Links
// Last review MRG (01.07.11 14:37)
// TODO: make RSSStandards methods more generic

use MediaWiki\MediaWikiServices;

class RSSStandards extends BsExtensionMW {

	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->setHook( 'BSRSSFeederGetRegisteredFeeds' );
		$this->setHook( 'BeforePageDisplay' );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 *
	 * @param OutputPage $oOutputPage
	 * @param SkinTemplate $oSkinTemplate
	 * @return boolean
	 */
	public function onBeforePageDisplay( $oOutputPage, $oSkinTemplate ) {
		if( !SpecialPage::getTitleFor('RSSFeeder')->equals( $this->getTitle() )) return true;
		$oOutputPage->addModules('ext.bluespice.rssStandards');
		return true;
	}

	/**
	 * Hook-Handler for BlueSpice hook BSRSSFeederGetRegisteredFeeds
	 * @param Array $aFeed Feed array.
	 * @return bool Always true.
	 */
	public function onBSRSSFeederGetRegisteredFeeds( $aFeeds ) {
		RSSFeeder::registerFeed('recentchanges',
			wfMessage( 'bs-rssfeeder-recent-changes' )->plain(),
			wfMessage( 'bs-rssstandards-desc-rc' )->plain(),
			$this,
			NULL,
			NULL,
			'buildLinksRc'
		);

		RSSFeeder::registerFeed('followOwn',
			wfMessage( 'bs-rssstandards-title-own' )->plain(),
			wfMessage( 'bs-rssstandards-desc-own' )->plain(),
			$this,
			'buildRssOwn',
			array('u'),
			'buildLinksOwn'
		);

		RSSFeeder::registerFeed('followPage',
			wfMessage( 'bs-rssstandards-title-page' )->plain(),
			wfMessage( 'bs-rssstandards-desc-page' )->plain(),
			$this,
			'buildRssPage',
			array('p', 'ns'),
			'buildLinksPage'
		);

		RSSFeeder::registerFeed('namespace',
			wfMessage( 'bs-ns' )->plain(),
			wfMessage( 'bs-rssstandards-desc-ns' )->plain(),
			$this,
			'buildRssNs',
			array('ns'),
			'buildLinksNs'
		);

		RSSFeeder::registerFeed( 'category',
			wfMessage( 'bs-rssstandards-title-cat' )->plain(),
			wfMessage( 'bs-rssstandards-desc-cat' )->plain(),
			$this,
			'buildRssCat',
			array('cat'),
			'buildLinksCat'
		);

		RSSFeeder::registerFeed('watchlist',
			wfMessage( 'bs-rssstandards-title-watch' )->plain(),
			wfMessage( 'bs-rssstandards-desc-watch' )->plain(),
			$this,
			'buildRssWatch',
			array('days'),
			'buildLinksWatch'
		);

		return true;
	}

	public function buildRssPage() {
		global $wgSitename, $wgRequest, $wgContLang;
		$sTitle = $wgRequest->getVal( 'p', '' );
		$iNSid = $wgRequest->getInt( 'ns', 0 );

		$aNamespaces = $wgContLang->getNamespaces();
		if ( $iNSid != 0 ) {
			$sPageName = $aNamespaces[$iNSid].':'.$sTitle;
		} else {
			$sPageName = $sTitle;
		}

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			array( 'page', 'recentchanges' ),
			'*',
			array(
				'page_title'     => $sTitle,
				'page_namespace' => $iNSid,
				'rc_timestamp > '. $dbr->timestamp( time() - intval( 7 * 86400 ) )
			),
			__METHOD__,
			array( 'ORDER BY' => 'rc_timestamp DESC' ),
			array(
				'page'=> array( 'LEFT JOIN', 'rc_cur_id = page_id' )
			)
		);

		$oChannel = RSSCreator::createChannel(
			RSSCreator::xmlEncode( $wgSitename . ' - ' . $sPageName ),
			'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
			wfMessage( 'bs-rssstandards-desc-page' )->plain()
		);
		foreach ( $res as $row ) {
			$title = Title::makeTitle( $row->rc_namespace, $row->rc_title );
			$entry = RSSItemCreator::createItem(
				wfMessage( 'bs-rssstandards-changes-from', $row->rc_user_text )->text(),
				$title->getFullURL( 'diff=' . $row->rc_this_oldid . '&oldid=prev' ),
				FeedUtils::formatDiff( $row )
			);
			$entry->setPubDate( wfTimestamp( TS_UNIX,$row->rc_timestamp ) );
			$oChannel->addItem( $entry );
		}
		$dbr->freeResult( $res );

		return $oChannel->buildOutput();
	}

	public function buildRssOwn() {
		global $wgSitename, $wgRequest;
		$user = $wgRequest->getInt( 'u', 0 );

		$dbr = wfGetDB( DB_REPLICA );

		$res = $dbr->select(
			array( 'recentchanges' ),
			array( 'rc_id' ),
			array(
				'rc_user'     => $user,
				'rc_timestamp > '. $dbr->timestamp( time() - intval( 7 * 86400 ) )
			)
		);

		$ids = array();
		foreach ( $res as $row ) {
			$ids[] = $row->rc_id;
		}

		if ( count( $ids ) ) {
			$res = $dbr->select(
				array( 'recentchanges' ),
				array( 'rc_id' ),
				array(
					'rc_timestamp > '. $dbr->timestamp( time() - intval( 7 * 86400 ) )
				),
				__METHOD__,
				array( 'ORDER BY' => 'rc_timestamp DESC' )
			);
		} else {
			$res = false;
		}

		$channel = RSSCreator::createChannel(
			RSSCreator::xmlEncode( $wgSitename . ' - ' . wfMessage( 'bs-rssstandards-title-own' )->plain() ),
			'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
			wfMessage( 'bs-rssstandards-desc-own' )->plain()
		);
		if ( $res ) {
			foreach ( $res as $obj ) {
				$title = Title::makeTitle( $obj->rc_namespace, $obj->rc_title );
				$entry = RSSItemCreator::createItem(
					wfMessage( 'bs-rssstandards-changes-from', $obj->rc_user_text )->text(),
					$title->getFullURL( 'diff=' . $obj->rc_this_oldid . '&oldid=prev' ),
					FeedUtils::formatDiff($obj)
				);
				$entry->setPubDate( wfTimestamp( TS_UNIX,$obj->rc_timestamp ) );
				$channel->addItem( $entry );
			}
			$dbr->freeResult( $res );
		}

		return $channel->buildOutput();
	}

	public function buildRssCat() {
		global $wgRequest, $wgSitename;

		$dbr = wfGetDB( DB_REPLICA );

		$_showLimit = 10;

		$cat = $wgRequest->getVal( 'cat', '' );

		$channel = RSSCreator::createChannel(
			$wgSitename . ' - ' . wfMessage( 'bs-rssstandards-title-cat' )->plain() . ' ' . addslashes( $cat ),
			'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
			wfMessage( 'bs-rssstandards-desc-cat' )->plain()
		);

		$res = $dbr->select(
			"categorylinks",
			"cl_from",
			array( "cl_to" => $cat )
		);

		$entryIds = Array();

		foreach ( $res as $row ) {
			$entryIds[] = $row->cl_from;
		}

		if ( count( $entryIds ) ) {
			$aTable = array( 'r' => 'revision' );
			$aFields = array(
				'rid' => "MIN(r.rev_id)",
				"r.rev_page",
				"r.rev_timestamp",
				"r.rev_user_text"
			);

			$aConditions = array( "r.rev_page" => $entryIds );
			$aOptions = array(
				"group by" => array(
					"r.rev_page",
					"r.rev_timestamp",
					"r.rev_user_text"
				),
				"order by" => "rid DESC"
			);

			$res = $dbr->select( $aTable, $aFields, $aConditions, __METHOD__, $aOptions );
			$numberOfEntries = $dbr->numRows( $res );
			$paramShowAll = $wgRequest->getFuzzyBool( 'showall', false ); // Sole importance is the existence of param 'showall'

			if ( !$paramShowAll ) {
				$aOptions['LIMIT'] = $_showLimit;
			}

			$res = $dbr->select( $aTable, $aFields, $aConditions, __METHOD__, $aOptions );

			foreach ( $res as $row ) {
				$title = Title::newFromID( $row->rev_page );
				$page = WikiPage::factory( $title );
				if ( !$title->userCan( 'read' ) ) {
					$numberOfEntries--;
					continue;
				}

				$_title = str_replace( "_", " ", $title->getText() );
				$_link  = $title->getFullURL();

				$_description = preg_replace(
					"#\[<a\ href\=\"(.*)action\=edit(.*)\"\ title\=\"(.*)\">(.*)<\/a>\]#",
					"",
					$this->mCore->parseWikiText( $page->getContent()->getNativeData(), $this->getTitle() )
				);

				$item = RSSItemCreator::createItem( $_title, $_link, $_description );
				if ( $item ) {
					$item->setPubDate( wfTimestamp( TS_UNIX,$row->rev_timestamp) );
					$item->setComments( $title->getTalkPage()->getFullURL() );
					$item->setGUID( $title->getFullURL( "oldid=".$page->getRevision()->getId() ), 'true' );
					$channel->addItem( $item );
				}
			}
		}

		$dbr->freeResult( $res );
		return $channel->buildOutput();
	}

	public function buildRssNs( $aParams ) {
		global $wgRequest, $wgSitename, $wgLang, $wgDBprefix;

		$dbr =  wfGetDB( DB_REPLICA );

		$_showLimit = 10;

		if ( isset( $aParams['ns'] ) ) {
			$ns = $aParams['ns']+0;
		} else {
			$ns = $wgRequest->getInt( 'ns', 0 );
		}

		$aNamespaces = $wgLang->getNamespaces();

		$channel = RSSCreator::createChannel(
			$wgSitename . ' - ' . wfMessage( 'bs-ns' )->plain() . ' ' . $aNamespaces[$ns],
			'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
			wfMessage( 'bs-rssstandards-desc-ns' )->plain()
		);


		$res = $dbr->select(
			"page",
			"page_id",
			array( "page_namespace" => $ns )
		);

		$entryIds = Array();
		foreach ( $res as $row ) {
			$entryIds[] = $row->page_id;
		}

		if ( count( $entryIds ) ) {
			$aTable = array( 'r' => 'revision' );
			$aFields = array(
				'rid' => "MIN(r.rev_id)",
				"r.rev_page",
				"r.rev_timestamp",
				"r.rev_user_text"
			);
			$aConditions = array( "r.rev_page" => $entryIds );
			$aOptions = array(
				"group by" => array(
					"r.rev_page",
					"r.rev_timestamp",
					"r.rev_user_text"
				),
				"order by" => "rid DESC"
			);

			$res = $dbr->select( $aTable, $aFields, $aConditions, __METHOD__, $aOptions );

			$numberOfEntries = $dbr->numRows( $res );

			$aOptions['LIMIT'] = $_showLimit;

			$res = $dbr->select( $aTable, $aFields, $aConditions, __METHOD__, $aOptions );

			foreach ( $res as $row ) {
				$title = Title::newFromID( $row->rev_page );
				$page = WikiPage::factory( $title );
				if ( !$title->userCan( 'read' ) ) {
					$numberOfEntries--;
					continue;
				}

				$_title = str_replace( "_", " ", $title->getText() );
				$_link  = $title->getFullURL();
				$_description = preg_replace(
					"#\[<a\ href\=\"(.*)action\=edit(.*)\"\ title\=\"(.*)\">(.*)<\/a>\]#",
					"",
					$this->mCore->parseWikiText( $page->getContent()->getNativeData(), $this->getTitle() )
				);

				$item = RSSItemCreator::createItem( $_title, $_link, $_description );
				if ( $item ) {
					$item->setPubDate( wfTimestamp( TS_UNIX, $row->rev_timestamp ) );
					$item->setComments( $title->getTalkPage()->getFullURL() );
					$item->setGUID( $title->getFullURL( "oldid=".$page->getRevision()->getId() ), 'true' );
					$channel->addItem( $item );
				}
			}
		}

		$dbr->freeResult( $res );
		return $channel->buildOutput();
	}

	public function buildRssWatch( $par ) {
		// TODO SU (04.07.11 10:35): Globals
		global $wgUser, $wgOut, $wgRequest, $wgRCShowWatchingUsers, $wgShowUpdatedMarker, $wgEnotifWatchlist;

		$skin = RequestContext::getMain()->getSkin();
		$specialTitle = SpecialPage::getTitleFor( 'Watchlist' );
		$wgOut->setRobotPolicy( 'noindex,nofollow' );

		# Anons don't get a watchlist
		if ( $wgUser->isAnon() ) {
			$_user = $wgRequest->getVal( 'u', '' );
			$user = User::newFromName( $_user );
			$_hash = $wgRequest->getVal( 'h', '' );
			if ( !( $user && $_hash == md5( $_user.$user->getToken().$user->getId() ) ) || $user->isAnon() ) {
				$oTitle = SpecialPage::getTitleFor( 'Userlogin' );
				$sLink = MediaWikiServices::getInstance()->getLinkRenderer()->makeLink(
					$oTitle,
					wfMessage( 'loginreqlink' )->plain(),
					array(),
					array(
						'returnto' => $specialTitle->getLocalUrl()
					)
				);

				throw new ErrorPageError(
					'bs-rssstandards-watchnologin', 'watchlistanontext', array( $sLink )
				);
			}
		} else {
			$user = $wgUser;
		}

		$wgOut->setPageTitle( wfMessage( 'bs-rssstandards-watchlist' )->plain() );

		$sub  = wfMessage( 'watchlistfor', $user->getName() )->parse();
		$sub .= '<br />' . SpecialEditWatchlist::buildTools( $this->getSkin() );
		$wgOut->setSubtitle( $sub );

		if( ( $mode = SpecialEditWatchlist::getMode( $wgRequest, "" ) ) !== false ) {
			$editor = new SpecialEditWatchlist();
			$editor->execute( $user, $wgOut, $wgRequest, $mode );
			return;
		}

		$uid = $user->getId();
		if( ($wgEnotifWatchlist || $wgShowUpdatedMarker) && $wgRequest->getVal( 'reset' ) && $wgRequest->wasPosted() ) {
			$user->clearAllNotifications( $uid );
			$wgOut->redirect( $specialTitle->getFullUrl() );
			return;
		}

		$aConditions = array(
			"w.wl_user" => $uid,
			"wl_title" => "rc_title"
		);

		$defaults = array(
			/* float */ 'days' => floatval( $user->getOption( 'watchlistdays' ) ), /* 3.0 or 0.5, watch further below */
			/* bool  */ 'hideOwn' => (int)$user->getBoolOption( 'watchlisthideown' ),
			/* bool  */ 'hideBots' => (int)$user->getBoolOption( 'watchlisthidebots' ),
			/* bool */ 'hideMinor' => (int)$user->getBoolOption( 'watchlisthideminor' ),
			/* ?     */ 'namespace' => 'all',
		);

		extract( $defaults );

		# Extract variables from the request, falling back to user preferences or
		# other default values if these don't exist
		$prefs['days'    ] = floatval( $user->getOption( 'watchlistdays' ) );
		$prefs['hideown' ] = $user->getBoolOption( 'watchlisthideown' );
		$prefs['hidebots'] = $user->getBoolOption( 'watchlisthidebots' );
		$prefs['hideminor'] = $user->getBoolOption( 'watchlisthideminor' );

		# Get query variables
		$days     = $wgRequest->getVal( 'days', $prefs['days'] );
		$hideOwn  = $wgRequest->getBool( 'hideOwn', $prefs['hideown'] );
		$hideBots = $wgRequest->getBool( 'hideBots', $prefs['hidebots'] );
		$hideMinor = $wgRequest->getBool( 'hideMinor', $prefs['hideminor'] );

		# Get namespace value, if supplied, and prepare a WHERE fragment
		$nameSpace = $wgRequest->getIntOrNull( 'namespace' );

		if ( !is_null( $nameSpace ) ) {
			$nameSpace = intval( $nameSpace );
			$aConditions['rc_namespace'] = $nameSpace;
		}

		$dbr = wfGetDB( DB_REPLICA, 'watchlist' );
		list( $page, $watchlist, $recentchanges ) = $dbr->tableNamesN( 'page', 'watchlist', 'recentchanges' );

		$watchlistCount = $dbr->selectField( 'watchlist', 'COUNT(*)',
			array( 'wl_user' => $uid ), __METHOD__ );
		// Adjust for page X, talk:page X, which are both stored separately,
		// but treated together
		$nitems = floor( $watchlistCount / 2 );

		if( is_null( $days ) || !is_numeric( $days ) ) {
			$big = 1000; /* The magical big */
			if ( $nitems > $big ) {
				# Set default cutoff shorter
				$days = $defaults['days'] = (12.0 / 24.0); # 12 hours...
			} else {
				$days = $defaults['days']; # default cutoff for shortlisters
			}
		} else {
			$days = floatval( $days );
		}

		// Dump everything here
		$nondefaults = array();

		wfAppendToArrayIfNotDefault( 'days'     , $days         , $defaults, $nondefaults );
		wfAppendToArrayIfNotDefault( 'hideOwn'  , (int)$hideOwn , $defaults, $nondefaults );
		wfAppendToArrayIfNotDefault( 'hideBots' , (int)$hideBots, $defaults, $nondefaults );
		wfAppendToArrayIfNotDefault( 'hideMinor', (int)$hideMinor, $defaults, $nondefaults );
		wfAppendToArrayIfNotDefault( 'namespace', $nameSpace     , $defaults, $nondefaults );

		$aTable = array(
			'w' => $watchlist,
			'r' => $recentchanges
		);
		$aFields = array(
			"r.*"
		);
		$aOptions = array(
			"order by" => "r.rc_timestamp DESC"
		);

		if( $days > 0 ) {
			$aConditions[] = "rc_timestamp > '" . $dbr->timestamp( time() - intval( $days * 86400 ) ) . "'";
		}

		# If the watchlist is relatively short, it's simplest to zip
		# down its entirety and then sort the results.

		# If it's relatively long, it may be worth our while to zip
		# through the time-sorted page list checking for watched items.

		# Up estimate of watched items by 15% to compensate for talk pages...

		# Toggles
		if( $hideOwn ) {
			$aConditions[] = "rc_user <> " . $uid;
		}

		if( $hideBots ) {
			$aConditions['rc_bot'] = "0";
		}

		if( $hideMinor ) {
			$aConditions['rc_minor'] = "0";
		}

		# Toggle watchlist content (all recent edits or just the latest)
		if( $user->getOption( 'extendwatchlist' )) {
			$aOptions['limit'] = intval( $user->getOption( 'wllimit' ) );
		} else {
			# Top log Ids for a page are not stored
			$aConditions[] = "(rc_this_oldid=page_latest OR rc_type=' . RC_LOG . ')";
		}

		if( $wgShowUpdatedMarker ) {
			$aFields[] = "w.wl_notificationtimestamp";
		}

		$res = $dbr->select( $aTable, $aFields, $aConditions, __METHOD__, $aOptions );

		$numRows = $dbr->numRows( $res );

		if($numRows > 0) {
			/* Do link batch query */
			$linkBatch = new LinkBatch;

			foreach ( $res as $row ) {
				$userNameUnderscored = str_replace( ' ', '_', $row->rc_user_text );
				if ( $row->rc_user != 0 ) {
					$linkBatch->add( NS_USER, $userNameUnderscored );
				}
				$linkBatch->add( NS_USER_TALK, $userNameUnderscored );
			}
			$linkBatch->execute();
			$dbr->dataSeek( $res, 0 );
		}

		$list = ChangesList::newFromContext( $skin->getContext() ); //Thanks to Bartosz Dziewoński (https://gerrit.wikimedia.org/r/#/c/94082/)

		$channel = RSSCreator::createChannel(
			SpecialPage::getTitleFor( 'Watchlist' ) . ' (' . $user->getName(). ')',
			'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
			wfMessage( 'bs-rssstandards-desc-watch' )->plain()
		);

		$html = $list->beginRecentChangesList();
		$counter = 1;
		$items = array();
		foreach ( $res as $obj ) {
			$title = Title::newFromText($obj->rc_title, $obj->rc_namespace);
			$items[] = array(
				'title'    => $title->getPrefixedText(),
				'link'     => $title->getFullURL(),
				'date'     => wfTimestamp(TS_UNIX,$obj->rc_timestamp),
				'comments' => $title->getTalkPage()->getFullURL()
			);
			# Make RC entry
			$rc = RecentChange::newFromRow( $obj );
			$rc->counter = $counter++;

			if ( $wgShowUpdatedMarker ) {
				$updated = $obj->wl_notificationtimestamp;
			} else {
				$updated = false;
			}

			if ( $wgRCShowWatchingUsers && $user->getOption( 'shownumberswatching' ) ) {
				$rc->numberofWatchingusers = $dbr->selectField(
					'watchlist',
					'COUNT(*)',
					array(
						'wl_namespace' => $obj->rc_namespace,
						'wl_title'     => $obj->rc_title,
					),
					__METHOD__
				);
			} else {
				$rc->numberofWatchingusers = 0;
			}
			$rc->mAttribs['rc_timestamp'] = 0;

			$html .= $list->recentChangesLine( $rc, false );
		}
		$html .= $list->endRecentChangesList();
		$lines = array();
		preg_match_all('%<li.*?>(.*?)</li>%', $html, $lines, PREG_SET_ORDER);
		foreach ( $lines as $key => $line ) {
			$item = $items[$key];
			$entry = RSSItemCreator::createItem(
				$item['title'],
				$item['link'],
				RSSCreator::xmlEncode($line[1])
			);
			if ( $entry == false ){
				wfDebugLog('BS::RSSStandards::buildRssWatch', 'Invalid item: '.var_export($item,true) );
				continue;
			}
			$entry->setPubDate($item['date']);
			$entry->setComments($item['comments']);
			$channel->addItem($entry);
		}
		$dbr->freeResult( $res );

		return $channel->buildOutput();
	}

	public function buildLinksRc() {
		global $wgUser;
		$set = new ViewFormElementFieldset();
		$set->setLabel( wfMessage( 'bs-rssfeeder-recent-changes' )->plain() );

		$label = new ViewFormElementLabel();
		$label->useAutoWidth();
		$label->setFor( 'btnFeedRc' );
		$label->setText( wfMessage( 'bs-rssstandards-desc-rc' )->plain() );

		$btn = new ViewFormElementButton();
		$btn->setId('btnFeedRc');
		$btn->setName('btnFeedRc');
		$btn->setType('button');
		$btn->setValue(
			SpecialPage::getTitleFor( 'Recentchanges' )->getLocalUrl(
				array(
					'feed' => 'rss',
					'u'    => $wgUser->getName(),
					'h'    => $wgUser->getToken()
				)
			)
		);
		$btn->setLabel( wfMessage( 'bs-rssfeeder-submit' )->plain() );

		$set->addItem( $label );
		$set->addItem( $btn );
		return $set;
	}

	public function buildLinksPage() {
		$set = new ViewFormElementFieldset();
		$set->setLabel( wfMessage( 'bs-rssstandards-title-page' )->plain() );

		$div = new ViewTagElement();
		$div->setAutoElement( 'div' );
		$div->setId( 'divFeedPage' );

		$btn = new ViewFormElementButton();
		$btn->setId( 'btnFeedPage' );
		$btn->setName( 'btnFeedPage' );
		$btn->setType( 'button' );
		$btn->setLabel( wfMessage( 'bs-rssfeeder-submit' )->plain() );

		$set->addItem( $div );
		$set->addItem( $btn );
		return $set;
	}

	public function buildLinksOwn() {
		global $wgUser;
		$set = new ViewFormElementFieldset();
		$set->setLabel( wfMessage( 'bs-rssstandards-title-own' )->plain() );

		$label = new ViewFormElementLabel();
		$label->useAutoWidth();
		$label->setFor( 'btnFeedOwn' );
		$label->setText( wfMessage( 'bs-rssstandards-desc-own' )->plain() );

		$oSpecialRSS = SpecialPage::getTitleFor( 'RSSFeeder' );
		$sUserName   = $wgUser->getName();
		$sUserToken  = $wgUser->getToken();

		$btn = new ViewFormElementButton();
		$btn->setId( 'btnFeedOwn' );
		$btn->setName( 'btnFeedOwn' );
		$btn->setType( 'button' );
		$btn->setValue(
			$oSpecialRSS->getLinkUrl(
				array(
					'Page' => 'followOwn',
					'u'    => $sUserName,
					'h'    => $sUserToken
				)
			)
		);
		$btn->setLabel( wfMessage( 'bs-rssfeeder-submit' )->plain() );

		$set->addItem( $label );
		$set->addItem( $btn );
		return $set;
	}

	public function buildLinksNs() {
		global $wgUser;
		$set = new ViewFormElementFieldset();
		$set->setLabel( wfMessage( 'bs-ns' )->plain() );

		$select = new ViewFormElementSelectbox();
		$select->setId( 'selFeedNs' );
		$select->setName( 'selFeedNs' );
		$select->setLabel( wfMessage( 'bs-ns' )->plain() );

		$aNamespaces = BsNamespaceHelper::getNamespacesForSelectOptions( array( NS_SPECIAL, NS_MEDIA ) );
		$oSpecialRSS = SpecialPage::getTitleFor( 'RSSFeeder' );
		$sUserName   = $wgUser->getName();
		$sUserToken  = $wgUser->getToken();
		foreach( $aNamespaces as $key => $name ) {
			$select->addData(
				array(
					'value' => $oSpecialRSS->getLinkUrl(
						array(
							'Page' => 'namespace',
							'ns'   => $key,
							'u'    => $sUserName,
							'h'    => $sUserToken
						)
					),
					'label' => $name
				)
			);
		}

		$btn = new ViewFormElementButton();
		$btn->setId( 'btnFeedNs' );
		$btn->setName( 'btnFeedNs' );
		$btn->setType( 'button' );
		$btn->setLabel( wfMessage( 'bs-rssfeeder-submit' )->plain() );

		$set->addItem( $select );
		$set->addItem( $btn );
		return $set;
	}

	public function buildLinksCat() {
		global $wgUser;

		$set = new ViewFormElementFieldset();
		$set->setLabel( wfMessage( 'bs-rssstandards-title-cat' )->plain() );

		$select = new ViewFormElementSelectbox();
		$select->setId( 'selFeedCat' );
		$select->setName( 'selFeedCat' );
		$select->setLabel( wfMessage( 'bs-rssstandards-title-cat' )->plain() );

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'categorylinks',
			'cl_to',
			array(),
			__METHOD__,
			array(
				'GROUP BY' => 'cl_to',
				'ORDER BY' => 'cl_to',
			)
		);

		$oSpecialRSS = SpecialPage::getTitleFor( 'RSSFeeder' );
		$sUserName   = $wgUser->getName();
		$sUserToken  = $wgUser->getToken();
		foreach ( $res as $row ) {
			$select->addData(
				array(
					'value' => $oSpecialRSS->getLinkUrl(
						array(
							'Page' => 'category',
							'cat'  => $row->cl_to,
							'u'    => $sUserName,
							'h'    => $sUserToken
						)
					),
					'label' => $row->cl_to
				)
			);
		}

		$btn = new ViewFormElementButton();
		$btn->setId( 'btnFeedCat' );
		$btn->setName( 'btnFeedCat' );
		$btn->setType( 'button' );
		$btn->setLabel( wfMessage( 'bs-rssfeeder-submit' )->plain() );

		$set->addItem( $select );
		$set->addItem( $btn );

		return $set;
	}

	public function buildLinksWatch() {
		global $wgUser;
		$aRssWatchlistDays = array(1, 3, 5, 7, 14, 30, 60, 90, 180, 365);

		$set = new ViewFormElementFieldset();
		$set->setLabel( wfMessage( 'bs-rssstandards-title-watch' )->plain() );

		$select = new ViewFormElementSelectbox();
		$select->setId( 'selFeedWatch' );
		$select->setName( 'selFeedWatch' );
		$select->setLabel( wfMessage( 'bs-rssstandards-title-watch' )->plain() );

		$oSpecialRSS = SpecialPage::getTitleFor( 'RSSFeeder' );
		$sUserName   = $wgUser->getName();
		$sUserToken  = $wgUser->getToken();
		foreach ( $aRssWatchlistDays as $day ) {
			$select->addData(
				array(
					'value' => $oSpecialRSS->getLinkUrl(
						array(
							'Page' => 'watchlist',
							'days' => $day,
							'u'    => $sUserName,
							'h'    => $sUserToken
						)
					),
					'label' => wfMessage( 'bs-rssstandards-link-text-watch', $day )->text(),
				)
			);
		}

		$btn = new ViewFormElementButton();
		$btn->setId( 'btnFeedWatch' );
		$btn->setName( 'btnFeedWatch' );
		$btn->setType( 'button' );
		$btn->setLabel( wfMessage( 'bs-rssfeeder-submit' )->plain() );

		$set->addItem( $select );
		$set->addItem( $btn );

		return $set;
	}

	/**
	 * UnitTestsList allows registration of additional test suites to execute
	 * under PHPUnit. Extensions can append paths to files to the $paths array,
	 * and since MediaWiki 1.24, can specify paths to directories, which will
	 * be scanned recursively for any test case files with the suffix "Test.php".
	 * @param array $paths
	 */
	public static function onUnitTestsList( array &$paths ) {
		$paths[] = __DIR__ . '/tests/phpunit/';
		return true;
	}
}
