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
 * For further information visit https://bluespice.com
 *
 * @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-design.hk>
 * @author     Leonid Verhovskij <verhovskij@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage RSSFeeder
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

use BlueSpice\RSSFeeder\RSSFeedManager;
use MediaWiki\MediaWikiServices;

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
		$this->setHook( 'BSRSSFeederGetRegisteredFeeds' );
	}

	/**
	 *
	 * @param int $iCount
	 * @param string $sUrl
	 * @return string
	 */
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
	 * @return bool always true to keep hook alive
	 */
	public function onBSDashboardsAdminDashboardPortalPortlets( &$aPortlets ) {
		$aPortlets[] = [
			'type' => 'BS.RSSFeeder.RSSPortlet',
			'config' => [
				'title' => wfMessage( 'bs-rssfeeder-rss' )->plain(),
				'height' => 660,
				'rssurl' => 'https://blog.bluespice.com/feed/'
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
	 * @param bool $bIsDefault default
	 * @return bool always true to keep hook alive
	 */
	public function onBSDashboardsAdminDashboardPortalConfig( $oCaller, &$aPortalConfig,
		$bIsDefault ) {
		$aPortalConfig[0][] = [
			'type' => 'BS.RSSFeeder.RSSPortlet',
			'config' => [
				'title' => wfMessage( 'bs-rssfeeder-rss' )->plain(),
				'height' => 610,
				'rssurl' => 'https://blog.bluespice.com/feed/'
			]
		];

		return true;
	}

	/**
	 * Hook Handler for BSDashboardsAdminDashboardPortalPortlets
	 *
	 * @param array &$aPortlets reference to array portlets
	 * @return bool always true to keep hook alive
	 */
	public function onBSDashboardsUserDashboardPortalPortlets( &$aPortlets ) {
		$aPortlets[] = [
			'type' => 'BS.RSSFeeder.RSSPortlet',
			'config' => [
				'title' => wfMessage( 'bs-rssfeeder-rss' )->plain(),
				'height' => 610,
				'rssurl' => 'https://blog.bluespice.com/feed/'
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
	 * @param bool $bIsDefault default
	 * @return bool always true to keep hook alive
	 */
	public function onBSDashboardsUserDashboardPortalConfig( $oCaller, &$aPortalConfig, $bIsDefault ) {
		$aPortalConfig[0][] = [
			'type' => 'BS.RSSFeeder.RSSPortlet',
			'config' => [
				'title' => wfMessage( 'bs-rssfeeder-rss' )->plain(),
				'height' => 610,
				'rssurl' => 'https://blog.bluespice.com/feed/'
			]
		];

		return true;
	}

	/**
	 * returns an array of all registered feed plugings
	 * @deprecated Since 3.1.12, use RSSFeedManager
	 * @return array
	 */
	public static function getRegisteredFeeds() {
		$feedDummy = [];
		Hooks::run( 'BSRSSFeederGetRegisteredFeeds', [ &$feedDummy ], '3.1.12' );

		/** @var RSSFeedManager $feedsManager */
		$feedsManager = MediaWikiServices::getInstance()->getService(
			'BSRSSFeederFeedManagerFactory'
		)->makeManager( RequestContext::getMain() );

		return $feedsManager->getFeeds();
	}

	/**
	 * @param Title $title
	 * @param User $user
	 * @param bool &$whitelisted
	 * @return bool
	 */
	public static function onTitleReadWhitelist( Title $title, User $user, &$whitelisted ) {
		if ( $title->isSpecial( 'RSSFeeder' ) ) {
			$whitelisted = true;
		}

		return true;
	}

}
