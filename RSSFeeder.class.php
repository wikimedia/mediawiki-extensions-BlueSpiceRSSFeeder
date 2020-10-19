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

use MediaWiki\MediaWikiServices;

/**
 * the Preferences class
 * @package BlueSpice_Extensions
 * @subpackage RSSFeeder
 */
class RSSFeeder extends BsExtensionMW {
	/**
	 *
	 * @param int $iCount
	 * @param string $sUrl
	 * @return string
	 */
	public static function getRSS( $iCount, $sUrl ) {
		$oParserOpts = ParserOptions::newFromAnon();
		$iCount = intval( $iCount );

		$sTag = '<rss max="' . $iCount . '">' . $sUrl . '</rss>';

		$parserOutput = MediaWikiServices::getInstance()->getParser()->parse(
			$sTag,
			RequestContext::getMain()->getTitle(),
			$oParserOpts
		);
		return $parserOutput->getText();
	}
}
