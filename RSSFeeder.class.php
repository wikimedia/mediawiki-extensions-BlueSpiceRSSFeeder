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
	 * Hook-Handler for BlueSpice hook BSRSSFeederGetRegisteredFeeds
	 * @param Array $aFeed Feed array.
	 * @return bool Always true.
	 */
	public function onBSRSSFeederGetRegisteredFeeds( $aFeeds ) {
		RSSFeeder::registerFeed( 'recentchanges',
			wfMessage( 'bs-rssfeeder-recent-changes' )->plain(),
			wfMessage( 'bs-rssstandards-desc-rc' )->plain(),
			$this,
			'buildRssRc',
			NULL,
			'buildLinksRc'
		);

		RSSFeeder::registerFeed( 'followOwn',
			wfMessage( 'bs-rssstandards-title-own' )->plain(),
			wfMessage( 'bs-rssstandards-desc-own' )->plain(),
			$this,
			'buildRssOwn',
			[],
			'buildLinksOwn'
		);

		RSSFeeder::registerFeed( 'followPage',
			wfMessage( 'bs-rssstandards-title-page' )->plain(),
			wfMessage( 'bs-rssstandards-desc-page' )->plain(),
			$this,
			'buildRssPage',
			[ 'p', 'ns' ],
			'buildLinksPage'
		);

		RSSFeeder::registerFeed( 'namespace',
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

		RSSFeeder::registerFeed( 'watchlist',
			wfMessage( 'bs-rssstandards-title-watch' )->plain(),
			wfMessage( 'bs-rssstandards-desc-watch' )->plain(),
			$this,
			'buildRssWatch',
			[ 'days' ],
			'buildLinksWatch'
		);

		return true;
	}

	protected function getRecentChanges( $conditions = [] ) {
		$dbr = wfGetDB( DB_SLAVE );

		$res = $dbr->select(
			[ 'recentchanges' ],
			[ '*' ],
			$conditions,
			__METHOD__,
			[
				'ORDER BY' => 'rc_timestamp DESC',
				'LIMIT' => '10'
			]
		);

		if ( $res ) {
			return $res;
		}
		return new \stdClass();
	}

	public function buildRssRc() {
		global $wgSitename;

		$conditions = [];
		\Hooks::run( 'BSRSSFeederBeforeGetRecentChanges', [ &$conditions, 'recentchanges' ] );

		$rc = $this->getRecentChanges( $conditions );

		$oChannel = RSSCreator::createChannel(
			RSSCreator::xmlEncode( $wgSitename . ' - ' . wfMessage( 'bs-rssstandards-title-rc' )->plain() ),
			'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
			wfMessage( 'bs-rssstandards-desc-rc' )->plain()
		);

		foreach ( $rc as $row ) {
			$title = Title::makeTitle( $row->rc_namespace, $row->rc_title );
			$entry = RSSItemCreator::createItem(
				$title->getPrefixedText(),
				$title->getFullURL( 'diff=' . $row->rc_this_oldid . '&oldid=prev' ),
				FeedUtils::formatDiff( $row )
			);
			$entry->setPubDate( wfTimestamp( TS_UNIX,$row->rc_timestamp ) );
			$oChannel->addItem( $entry );
		}

		return $oChannel->buildOutput();
	}

	public function buildRssPage() {
		global $wgSitename;
		$request = $this->getRequest();
		$sTitle = $request->getVal( 'p', '' );
		$iNSid = $request->getInt( 'ns', 0 );

		if ( $iNSid != 0 ) {
			$sPageName = $aNamespaces[$iNSid].':'.$sTitle;
		} else {
			$sPageName = $sTitle;
		}

		$conditions = [
			'rc_namespace' => $iNSid,
			'rc_title' => $sTitle
		];
		\Hooks::run( 'BSRSSFeederBeforeGetRecentChanges', [ &$conditions, 'followPage' ] );
		$rc = $this->getRecentChanges( $conditions );

		$oChannel = RSSCreator::createChannel(
			RSSCreator::xmlEncode( $wgSitename . ' - ' . $sPageName ),
			'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
			wfMessage( 'bs-rssstandards-desc-page' )->plain()
		);
		foreach ( $rc as $row ) {
			$title = Title::makeTitle( $row->rc_namespace, $row->rc_title );
			$entry = RSSItemCreator::createItem(
				wfMessage( 'bs-rssstandards-changes-from', $row->rc_user_text )->text(),
				$title->getFullURL( 'diff=' . $row->rc_this_oldid . '&oldid=prev' ),
				FeedUtils::formatDiff( $row )
			);
			$entry->setPubDate( wfTimestamp( TS_UNIX,$row->rc_timestamp ) );
			$oChannel->addItem( $entry );
		}

		return $oChannel->buildOutput();
	}

	public function buildRssOwn() {
		global $wgSitename;
		$channel = RSSCreator::createChannel(
			RSSCreator::xmlEncode( $wgSitename . ' - ' . wfMessage( 'bs-rssstandards-title-own' )->plain() ),
			'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
			wfMessage( 'bs-rssstandards-desc-own' )->plain()
		);

		$conditions = [
			'rc_user' => $this->getUser()->getId()
		];
		\Hooks::run( 'BSRSSFeederBeforeGetRecentChanges', [ &$conditions, 'followOwn' ] );
		$rc = $this->getRecentChanges( $conditions );

		if ( $rc ) {
			foreach ( $rc as $obj ) {
				$title = Title::makeTitle( $obj->rc_namespace, $obj->rc_title );
				$entry = RSSItemCreator::createItem(
					wfMessage( 'bs-rssstandards-changes-from', $obj->rc_user_text )->text(),
					$title->getFullURL( 'diff=' . $obj->rc_this_oldid . '&oldid=prev' ),
					FeedUtils::formatDiff($obj)
				);
				$entry->setPubDate( wfTimestamp( TS_UNIX,$obj->rc_timestamp ) );
				$channel->addItem( $entry );
			}
		}

		return $channel->buildOutput();
	}

	public function buildRssCat() {
		global $wgSitename;

		$dbr = wfGetDB( DB_SLAVE );

		$cat = $this->getRequest()->getVal( 'cat', '' );

		$channel = RSSCreator::createChannel(
			$wgSitename . ' - ' . wfMessage( 'bs-rssstandards-title-cat' )->plain() . ' ' . addslashes( $cat ),
			'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
			wfMessage( 'bs-rssstandards-desc-cat' )->plain()
		);

		$conditions = [
			'r.rc_timestamp > '. $dbr->timestamp( time() - intval( 7 * 86400 ) )
		];
		\Hooks::run( 'BSRSSFeederBeforeGetRecentChanges', [ &$conditions, 'category' ] );

		$rc = $dbr->query( 'SELECT r.* from categorylinks AS c INNER JOIN page AS p ON c.cl_from = p.page_id INNER JOIN recentchanges AS r '
				. 'ON r.rc_namespace = p.page_namespace AND r.rc_title = p.page_title '
				. 'WHERE ' . implode( ' AND ', $conditions )
				. ' ORDER BY r.rc_timestamp DESC;' );

		if ( $rc ) {
			foreach ( $rc as $obj ) {
				$title = Title::makeTitle( $obj->rc_namespace, $obj->rc_title );
				$entry = RSSItemCreator::createItem(
					$title->getPrefixedText(),
					$title->getFullURL( 'diff=' . $obj->rc_this_oldid . '&oldid=prev' ),
					FeedUtils::formatDiff($obj)
				);

				$entry->setPubDate( wfTimestamp( TS_UNIX, $obj->rc_timestamp ) );
				$channel->addItem( $entry );
			}
			$dbr->freeResult( $rc );
		}

		return $channel->buildOutput();
	}

	public function buildRssNs( $aParams ) {
		global $wgSitename, $wgLang;


		$request = $this->getRequest();
		if ( isset( $aParams['ns'] ) ) {
			$ns = (int) $aParams['ns'];
		} else {
			$ns = $request->getInt( 'ns', 0 );
		}

		$aNamespaces = $wgLang->getNamespaces();

		$channel = RSSCreator::createChannel(
			$wgSitename . ' - ' . wfMessage( 'bs-ns' )->plain() . ' ' . $aNamespaces[$ns],
			'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
			wfMessage( 'bs-rssstandards-desc-ns' )->plain()
		);

		$conditions = [
			'rc_namespace' => $ns
		];
		\Hooks::run( 'BSRSSFeederBeforeGetRecentChanges', [ &$conditions, 'namespace' ] );
		$rc = $this->getRecentChanges( $conditions );

		foreach( $rc as $obj ) {
			$title = Title::makeTitle( $obj->rc_namespace, $obj->rc_title );
			$entry = RSSItemCreator::createItem(
				$title->getPrefixedText(),
				$title->getFullURL( 'diff=' . $obj->rc_this_oldid . '&oldid=prev' ),
				FeedUtils::formatDiff($obj)
			);
			$entry->setComments( $title->getTalkPage()->getFullURL() );
			$entry->setPubDate( wfTimestamp( TS_UNIX, $obj->rc_timestamp ) );
			$channel->addItem( $entry );
		}

		return $channel->buildOutput();
	}

	public function buildRssWatch( $par ) {
		$user = $this->getUser();

		$channel = RSSCreator::createChannel(
			SpecialPage::getTitleFor( 'Watchlist' ) . ' (' . $user->getName(). ')',
			'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
			wfMessage( 'bs-rssstandards-desc-watch' )->plain()
		);

		$request = $this->getRequest();
		$period = $request->getInt( 'days', 1 );
		$dbr = wfGetDB( DB_SLAVE );

		if( $user->isAnon() ) {
			return $channel->buildOutput();
		}

		$conditions = [
			'r.rc_timestamp > '. $dbr->timestamp( time() - intval( 7 * 86400 ) ),
			'w.wl_user = ' . $user->getId()
		];
		\Hooks::run( 'BSRSSFeederBeforeGetRecentChanges', [ &$conditions, 'watchlist' ] );

		$rc = $dbr->query( 'SELECT r.* FROM watchlist AS w INNER JOIN recentchanges AS r ON w.wl_namespace = r.rc_namespace AND w.wl_title = r.rc_title '
				. 'WHERE ' . implode( ' AND ', $conditions )
				. ' ORDER BY r.rc_timestamp DESC;' );

		foreach( $rc as $obj ) {
			$title = Title::makeTitle( $obj->rc_namespace, $obj->rc_title );
			$entry = RSSItemCreator::createItem(
				$title->getPrefixedText(),
				$title->getFullURL( 'diff=' . $obj->rc_this_oldid . '&oldid=prev' ),
				FeedUtils::formatDiff($obj)
			);
			$entry->setComments( $title->getTalkPage()->getFullURL() );
			$entry->setPubDate( wfTimestamp( TS_UNIX, $obj->rc_timestamp ) );
			$channel->addItem( $entry );
		}
		$dbr->freeResult( $rc );

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
			SpecialPage::getTitleFor( 'RSSFeeder' )->getLocalUrl(
				[
					'Page' => 'recentchanges',
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

		$oSpecialRSS = SpecialPage::getTitleFor( 'RSSFeeder' );
		$btn = new ViewFormElementButton();
		$btn->setId( 'btnFeedPage' );
		$btn->setName( 'btnFeedPage' );
		$btn->setType( 'button' );
		$btn->setValue(
			$oSpecialRSS->getLocalUrl(
				[
					'Page' => 'followPage',
					'u'    => $this->getUser()->getName(),
					'h'    => $this->getUser()->getToken()
				]
			)
		);
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
			$oSpecialRSS->getLocalUrl(
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
