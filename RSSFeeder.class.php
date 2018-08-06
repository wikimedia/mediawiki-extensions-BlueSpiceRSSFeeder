<?php
/**
 * This is the RSSFeeder class.
 *
 * The RSSFeeder offers different Feeds.
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
 * @author     Leonid Verhovskij <verhovskij@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage RSSFeeder
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

/**
 * the Preferences class
 * @package BlueSpice_Extensions
 * @subpackage RSSFeeder
 */
class RSSFeeder extends BsExtensionMW {

	/**
	 * initialise the extension
	 */
	protected function initExt() {
		$this->setHook( 'BSDashboardsAdminDashboardPortalPortlets' );
		$this->setHook( 'BSDashboardsAdminDashboardPortalConfig' );
		$this->setHook( 'BSDashboardsUserDashboardPortalPortlets' );
		$this->setHook( 'BSDashboardsUserDashboardPortalConfig' );
		$this->setHook( 'BSDashboardsUserDashboardPortalPortlets' );
		$this->setHook( 'BSDashboardsUserDashboardPortalConfig' );
		$this->setHook( 'BSRSSFeederGetRegisteredFeeds' );
		$this->setHook( 'BeforePageDisplay' );
	}

	public static function getRSS( $iCount, $sUrl ) {
		global $wgParser;
		$oParserOpts = new ParserOptions;
		$iCount = intval( $iCount );

		$sTag = '<rss max="' . $iCount . '">' . $sUrl . '</rss>';

		return $wgParser->parse( $sTag, RequestContext::getMain()->getTitle(), $oParserOpts )->getText();
	}

	/**
	 * Hook Handler for BSDashboardsAdminDashboardPortalPortlets
	 *
	 * @param array &$aPortlets reference to array portlets
	 * @return boolean always true to keep hook alive
	 */
	public function onBSDashboardsAdminDashboardPortalPortlets( &$aPortlets ) {
		$aPortlets[] = [
			'type' => 'BS.RSSFeeder.RSSPortlet',
			'config' => [
				'title' => wfMessage( 'bs-rssfeeder-rss' )->plain(),
				'height' => 660,
				'rssurl' => 'http://blog.bluespice.com/feed/'
			],
			'title' => wfMessage( 'bs-rssfeeder-rss' )->plain(),
			'description' => wfMessage( 'bs-rssfeeder-rss-desc' )->plain()
		];

		return true;
	}

	/**
	 * Hook Handler for BSDashboardsAdminDashboardPortalConfig
	 *
	 * @param object $oCaller caller instance
	 * @param array &$aPortalConfig reference to array portlet configs
	 * @param boolean $bIsDefault default
	 * @return boolean always true to keep hook alive
	 */
	public function onBSDashboardsAdminDashboardPortalConfig( $oCaller, &$aPortalConfig, $bIsDefault ) {
		$aPortalConfig[0][] = [
			'type' => 'BS.RSSFeeder.RSSPortlet',
			'config' => [
				'title' => wfMessage( 'bs-rssfeeder-rss' )->plain(),
				'height' => 610,
				'rssurl' => 'http://blog.bluespice.com/feed/'
			]
		];

		return true;
	}

		/**
	 * Hook Handler for BSDashboardsAdminDashboardPortalPortlets
	 *
	 * @param array &$aPortlets reference to array portlets
	 * @return boolean always true to keep hook alive
	 */
	public function onBSDashboardsUserDashboardPortalPortlets( &$aPortlets ) {
		$aPortlets[] = [
			'type' => 'BS.RSSFeeder.RSSPortlet',
			'config' => [
				'title' => wfMessage( 'bs-rssfeeder-rss' )->plain(),
				'height' => 610,
				'rssurl' => 'http://blog.bluespice.com/feed/'
			],
			'title' => wfMessage( 'bs-rssfeeder-rss' )->plain(),
			'description' => wfMessage( 'bs-rssfeeder-rss-desc' )->plain()
		];

		return true;
	}

	/**
	 * Hook Handler for BSDashboardsAdminDashboardPortalConfig
	 *
	 * @param object $oCaller caller instance
	 * @param array &$aPortalConfig reference to array portlet configs
	 * @param boolean $bIsDefault default
	 * @return boolean always true to keep hook alive
	 */
	public function onBSDashboardsUserDashboardPortalConfig( $oCaller, &$aPortalConfig, $bIsDefault ) {
		$aPortalConfig[0][] = [
			'type' => 'BS.RSSFeeder.RSSPortlet',
			'config' => [
				'title' => wfMessage( 'bs-rssfeeder-rss' )->plain(),
				'height' => 610,
				'rssurl' => 'http://blog.bluespice.com/feed/'
			]
		];

		return true;
	}

	/**
	 * an array which holds the informations of all registered feed plugins
	 * @var array
	 */
	protected static $aFeeds = [];

	/**
	 * register a feed plugin to the RSSFeeder
	 * @param string $sName the unique name of the plugin
	 * @param string $sTitle the nationalized title of the plugin
	 * @param string $sDescription the nationalized description of the plugin
	 * @param object $oObject the object instance of the plugin class
	 * @param string $sMethod the plugin method
	 * @param array $aParams the params to put to the method
	 * @param string $sLinkBuilder the method to build the link to the feed
	 */
	public static function registerFeed($sName, $sTitle, $sDescription, $oObject, $sMethod, $aParams, $sLinkBuilder = false) {
		self::$aFeeds[$sName] = [
			'title' => $sTitle,
			'description' => $sDescription,
			'object' => $oObject,
			'method' => $sMethod,
			'params' => $aParams,
			'buildLinks' => $sLinkBuilder
		];
	}

	/**
	 * unregister a feed plugin from the RSSFeeder
	 * @param string $sName the unique name of the plugin
	 */
	public static function unregisterFeed($sName) {
		unset(self::$aFeeds[$sName]);
	}

	/**
	 * returns an array of all registered feed plugings
	 * @return array
	 */
	public static function getRegisteredFeeds() {
		Hooks::run( 'BSRSSFeederGetRegisteredFeeds', [ &self::$aFeeds ] );
		return self::$aFeeds;
	}

	/* Source code RSSStandards.class.php */

	/**
	 *
	 * @param OutputPage $oOutputPage
	 * @param SkinTemplate $oSkinTemplate
	 * @return boolean
	 */
	public function onBeforePageDisplay( $oOutputPage, $oSkinTemplate ) {
		if ( !SpecialPage::getTitleFor( 'RSSFeeder' )->equals( $oOutputPage->getTitle() ) ) {
			return true;
		}
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
			[ 'u' ],
			'buildLinksOwn'
		);

		RSSFeeder::registerFeed('followPage',
			wfMessage( 'bs-rssstandards-title-page' )->plain(),
			wfMessage( 'bs-rssstandards-desc-page' )->plain(),
			$this,
			'buildRssPage',
			[ 'p', 'ns' ],
			'buildLinksPage'
		);

		RSSFeeder::registerFeed('namespace',
			wfMessage( 'bs-ns' )->plain(),
			wfMessage( 'bs-rssstandards-desc-ns' )->plain(),
			$this,
			'buildRssNs',
			[ 'ns' ],
			'buildLinksNs'
		);

		RSSFeeder::registerFeed( 'category',
			wfMessage( 'bs-rssstandards-title-cat' )->plain(),
			wfMessage( 'bs-rssstandards-desc-cat' )->plain(),
			$this,
			'buildRssCat',
			[ 'cat' ],
			'buildLinksCat'
		);

		RSSFeeder::registerFeed('watchlist',
			wfMessage( 'bs-rssstandards-title-watch' )->plain(),
			wfMessage( 'bs-rssstandards-desc-watch' )->plain(),
			$this,
			'buildRssWatch',
			[ 'days' ],
			'buildLinksWatch'
		);

		return true;
	}

	public function buildRssPage() {
		global $wgSitename, $wgContLang;
		$request = $this->getRequest();
		$sTitle = $request->getVal( 'p', '' );
		$iNSid = $request->getInt( 'ns', 0 );

		$aNamespaces = $wgContLang->getNamespaces();
		if ( $iNSid != 0 ) {
			$sPageName = $aNamespaces[$iNSid].':'.$sTitle;
		} else {
			$sPageName = $sTitle;
		}

		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			[ 'page', 'recentchanges' ],
			'*',
			[
			'page_title' => $sTitle,
			'page_namespace' => $iNSid,
			'rc_timestamp > ' . $dbr->timestamp( time() - intval( 7 * 86400 ) )
			],
			__METHOD__,
			[ 'ORDER BY' => 'rc_timestamp DESC' ],
			[ 'page' => [ 'LEFT JOIN', 'rc_cur_id = page_id' ] ]
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
		global $wgSitename;
		$user = $this->getRequest()->getInt( 'u', 0 );
		$dbr = wfGetDB( DB_SLAVE );

		$res = $dbr->select(
			[ 'recentchanges' ], [ 'rc_id' ],
			[
				'rc_user'     => $user,
				'rc_timestamp > '. $dbr->timestamp( time() - intval( 7 * 86400 ) )
			]
		);

		$ids = [];
		foreach ( $res as $row ) {
			$ids[] = $row->rc_id;
		}

		if ( count( $ids ) ) {
			$res = $dbr->select(
				[ 'recentchanges' ],
				[ 'rc_id' ],
				[
					'rc_timestamp > '. $dbr->timestamp( time() - intval( 7 * 86400 ) )
				],
				__METHOD__,
				[ 'ORDER BY' => 'rc_timestamp DESC' ]
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
		global $wgSitename;

		$dbr = wfGetDB( DB_SLAVE );

		$_showLimit = 10;

		$cat = $this->getRequest()->getVal( 'cat', '' );

		$channel = RSSCreator::createChannel(
			$wgSitename . ' - ' . wfMessage( 'bs-rssstandards-title-cat' )->plain() . ' ' . addslashes( $cat ),
			'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
			wfMessage( 'bs-rssstandards-desc-cat' )->plain()
		);

		$res = $dbr->select(
			"categorylinks",
			"cl_from",
			[ "cl_to" => $cat ]
		);

		$entryIds = [];

		foreach ( $res as $row ) {
			$entryIds[] = $row->cl_from;
		}

		if ( count( $entryIds ) ) {
			$aTable = [ 'r' => 'revision' ];
			$aFields = [
				'rid' => "MIN(r.rev_id)",
				"r.rev_page",
				"r.rev_timestamp",
				"r.rev_user_text"
			];

			$aConditions = [ "r.rev_page" => $entryIds ];
			$aOptions = [
				"group by" => [
					"r.rev_page",
					"r.rev_timestamp",
					"r.rev_user_text"
				],
				"order by" => "rid DESC"
			];

			$res = $dbr->select( $aTable, $aFields, $aConditions, __METHOD__, $aOptions );
			$numberOfEntries = $dbr->numRows( $res );
			$paramShowAll = $this->getRequest()->getFuzzyBool( 'showall', false ); // Sole importance is the existence of param 'showall'

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
					BsCore::getInstance()->parseWikiText( $page->getContent()->getNativeData(), $this->getTitle() )
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
		global $wgSitename, $wgLang;

		$dbr =  wfGetDB( DB_SLAVE );

		$_showLimit = 10;
		$request = $this->getRequest();
		if ( isset( $aParams['ns'] ) ) {
			$ns = $aParams['ns']+0;
		} else {
			$ns = $request->getInt( 'ns', 0 );
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
			[ "page_namespace" => $ns ]
		);

		$entryIds = [];
		foreach ( $res as $row ) {
			$entryIds[] = $row->page_id;
		}

		if ( count( $entryIds ) ) {
			$aTable = [ 'r' => 'revision' ];
			$aFields = [
				'rid' => "MIN(r.rev_id)",
				"r.rev_page",
				"r.rev_timestamp",
				"r.rev_user_text"
			];
			$aConditions = [ "r.rev_page" => $entryIds ];
			$aOptions = [
				"group by" => [
					"r.rev_page",
					"r.rev_timestamp",
					"r.rev_user_text"
				],
				"order by" => "rid DESC"
			];

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
					BsCore::getInstance()->parseWikiText( $page->getContent()->getNativeData(), $this->getTitle() )
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
		global $wgUser, $wgOut, $wgRCShowWatchingUsers, $wgShowUpdatedMarker, $wgEnotifWatchlist;

		$skin = RequestContext::getMain()->getSkin();
		$request = $this->getRequest();
		$specialTitle = SpecialPage::getTitleFor( 'Watchlist' );
		$wgOut->setRobotPolicy( 'noindex,nofollow' );

		# Anons don't get a watchlist
		if ( $wgUser->isAnon() ) {
			$_user = $request->getVal( 'u', '' );
			$user = User::newFromName( $_user );
			$_hash = $request->getVal( 'h', '' );
			if ( !( $user && $_hash == md5( $_user.$user->getToken().$user->getId() ) ) || $user->isAnon() ) {
				$oTitle = SpecialPage::getTitleFor( 'Userlogin' );
				$sLink = Linker::link(
					$oTitle,
					wfMessage( 'loginreqlink' )->plain(),
					[],
					[ 'returnto' => $specialTitle->getLocalUrl() ]
				);

				throw new ErrorPageError(
					'bs-rssstandards-watchnologin', 'watchlistanontext', [ $sLink ]
				);
			}
		} else {
			$user = $wgUser;
		}

		$wgOut->setPageTitle( wfMessage( 'bs-rssstandards-watchlist' )->plain() );

		$sub  = wfMessage( 'watchlistfor', $user->getName() )->parse();
		$sub .= '<br />' . SpecialEditWatchlist::buildTools( $this->getSkin() );
		$wgOut->setSubtitle( $sub );

		if( ( $mode = SpecialEditWatchlist::getMode( $request, "" ) ) !== false ) {
			$editor = new SpecialEditWatchlist();
			$editor->execute( $user, $wgOut, $request, $mode );
			return;
		}

		$uid = $user->getId();
		if( ($wgEnotifWatchlist || $wgShowUpdatedMarker) && $request->getVal( 'reset' ) && $request->wasPosted() ) {
			$user->clearAllNotifications( $uid );
			$wgOut->redirect( $specialTitle->getFullUrl() );
			return;
		}

		$aConditions = [
			"w.wl_user" => $uid,
			"wl_title" => "rc_title"
		];

		$defaults = [
			/* float */ 'days' => floatval( $user->getOption( 'watchlistdays' ) ), /* 3.0 or 0.5, watch further below */
			/* bool  */ 'hideOwn' => (int)$user->getBoolOption( 'watchlisthideown' ),
			/* bool  */ 'hideBots' => (int)$user->getBoolOption( 'watchlisthidebots' ),
			/* bool */ 'hideMinor' => (int)$user->getBoolOption( 'watchlisthideminor' ),
			/* ?     */ 'namespace' => 'all',
		];

		extract( $defaults );

		# Extract variables from the request, falling back to user preferences or
		# other default values if these don't exist
		$prefs['days'] = floatval( $user->getOption( 'watchlistdays' ) );
		$prefs['hideown'] = $user->getBoolOption( 'watchlisthideown' );
		$prefs['hidebots'] = $user->getBoolOption( 'watchlisthidebots' );
		$prefs['hideminor'] = $user->getBoolOption( 'watchlisthideminor' );

		# Get query variables
		$days     = $request->getVal( 'days', $prefs['days'] );
		$hideOwn  = $request->getBool( 'hideOwn', $prefs['hideown'] );
		$hideBots = $request->getBool( 'hideBots', $prefs['hidebots'] );
		$hideMinor = $request->getBool( 'hideMinor', $prefs['hideminor'] );

		# Get namespace value, if supplied, and prepare a WHERE fragment
		$nameSpace = $request->getIntOrNull( 'namespace' );

		if ( !is_null( $nameSpace ) ) {
			$nameSpace = intval( $nameSpace );
			$aConditions['rc_namespace'] = $nameSpace;
		}

		$dbr = wfGetDB( DB_SLAVE, 'watchlist' );
		list( $page, $watchlist, $recentchanges ) = $dbr->tableNamesN( 'page', 'watchlist', 'recentchanges' );

		$watchlistCount = $dbr->selectField( 'watchlist', 'COUNT(*)',
			[ 'wl_user' => $uid ], __METHOD__ );
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
		$nondefaults = [];

		wfAppendToArrayIfNotDefault( 'days'     , $days         , $defaults, $nondefaults );
		wfAppendToArrayIfNotDefault( 'hideOwn'  , (int)$hideOwn , $defaults, $nondefaults );
		wfAppendToArrayIfNotDefault( 'hideBots' , (int)$hideBots, $defaults, $nondefaults );
		wfAppendToArrayIfNotDefault( 'hideMinor', (int)$hideMinor, $defaults, $nondefaults );
		wfAppendToArrayIfNotDefault( 'namespace', $nameSpace     , $defaults, $nondefaults );

		$aTable = [ 'w' => $watchlist, 'r' => $recentchanges ];
		$aFields = [ "r.*" ];
		$aOptions = [ "order by" => "r.rc_timestamp DESC" ];

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
		$items = [];
		foreach ( $res as $obj ) {
			$title = Title::newFromText($obj->rc_title, $obj->rc_namespace);
			$items[] = [
				'title'    => $title->getPrefixedText(),
				'link'     => $title->getFullURL(),
				'date'     => wfTimestamp(TS_UNIX,$obj->rc_timestamp),
				'comments' => $title->getTalkPage()->getFullURL()
			];
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
					[
						'wl_namespace' => $obj->rc_namespace,
						'wl_title'     => $obj->rc_title,
					],
					__METHOD__
				);
			} else {
				$rc->numberofWatchingusers = 0;
			}
			$rc->mAttribs['rc_timestamp'] = 0;

			$html .= $list->recentChangesLine( $rc, false );
		}
		$html .= $list->endRecentChangesList();
		$lines = [];
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
				[
					'feed' => 'rss',
					'u'    => $wgUser->getName(),
					'h'    => $wgUser->getToken()
				]
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
				[
					'Page' => 'followOwn',
					'u'    => $sUserName,
					'h'    => $sUserToken
				]
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

		$aNamespaces = BsNamespaceHelper::getNamespacesForSelectOptions( [ NS_SPECIAL, NS_MEDIA ] );
		$oSpecialRSS = SpecialPage::getTitleFor( 'RSSFeeder' );
		$sUserName   = $wgUser->getName();
		$sUserToken  = $wgUser->getToken();
		foreach( $aNamespaces as $key => $name ) {
			$select->addData(
				[
					'value' => $oSpecialRSS->getLinkUrl(
						[
							'Page' => 'namespace',
							'ns'   => $key,
							'u'    => $sUserName,
							'h'    => $sUserToken
						]
					),
					'label' => $name
				]
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

		$dbr = wfGetDB( DB_SLAVE );
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

		$oSpecialRSS = SpecialPage::getTitleFor( 'RSSFeeder' );
		$sUserName   = $wgUser->getName();
		$sUserToken  = $wgUser->getToken();
		foreach ( $res as $row ) {
			$select->addData(
				[
					'value' => $oSpecialRSS->getLinkUrl(
						[
							'Page' => 'category',
							'cat'  => $row->cl_to,
							'u'    => $sUserName,
							'h'    => $sUserToken
						]
					),
					'label' => $row->cl_to
				]
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
		$aRssWatchlistDays = [ 1, 3, 5, 7, 14, 30, 60, 90, 180, 365 ];

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
				[
					'value' => $oSpecialRSS->getLinkUrl(
						[
							'Page' => 'watchlist',
							'days' => $day,
							'u'    => $sUserName,
							'h'    => $sUserToken
						]
					),
					'label' => wfMessage( 'bs-rssstandards-link-text-watch', $day )->text(),
				]
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
	 * @param Title $title
	 * @param User $user
	 * @param bool $whitelisted
	 * @return bool
	 */
	public static function onTitleReadWhitelist( $title, $user, &$whitelisted ) {
		if( $title->isSpecial( 'RSSFeeder' ) === false ) {
			return true;
		}
		$whitelisted = true;
		return true;
	}
}
