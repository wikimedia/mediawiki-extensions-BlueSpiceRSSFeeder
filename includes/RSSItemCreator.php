<?php
/**
 * RSSItemCreator
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
 *
 * @package    Bluespice_Extensions
 * @subpackage RSSFeeder
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

/**
 * the RSSItemCreator class
 *
 * RSSItemCreator builds RSS feed items following the RSS 2.0 specification.
 *
 * @package BlueSpice_Extensions
 * @subpackage RSSFeeder
 */
class RSSItemCreator {
	protected $title       = false;
	protected $link        = false;
	protected $description = false;

	protected $source      = false;
	protected $enclosure   = false;
	protected $category    = false;
	protected $pubDate     = false;
	protected $guid        = false;
	protected $comments    = false;
	protected $author      = false;

	/**
	 * magic getter
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		if ( isset( $this->$name ) ) {
			return $this->$name;
		}
		return false;
	}

	/**
	 * Create a new RSS item from the given data and return a RSSItemCreator instance,
	 * which hold this item.
	 *
	 * @param string $title the title of the item
	 * @param string $link the link to the item
	 * @param string $description the description of the item
	 * @return RSSItemCreator returns false, when the given link don't pass the test for valid URLs
	 */
	public static function createItem( $title, $link, $description ) {
		$title       = htmlentities( $title, ENT_QUOTES, 'UTF-8', false );
		$description = htmlentities( $description, ENT_QUOTES, 'UTF-8', false );
		$link = RSSCreator::ensureLinkProtocol( $link );
		if ( RSSCreator::testURL( $link ) ) {
			return new RSSItemCreator( $title, $link, $description );
		}
		return false;
	}

	/**
	 * constructor of RSSItemCreator
	 * @param string $title the title of the item
	 * @param string $link the link to the item
	 * @param string $description the description of the item
	 */
	protected function __construct( $title, $link, $description ) {
		$this->title       = $title;
		$this->link        = $link;
		$this->description = $description;
	}

	/**
	 * set the source of the item
	 * @param string $url
	 */
	public function setSource( $url ) {
		$this->source = $url;
	}

	/**
	 *
	 * @param string $url
	 * @param int $size
	 * @param string $type
	 */
	public function setEnclosure( $url, $size, $type ) {
		$this->enclosure = [ 'url'  => $url,
								 'size' => $size,
								 'type' => $type ];
	}

	/**
	 * set the category of the item
	 * @param string $category
	 * @param domain $domain
	 */
	public function setCategory( $category, $domain = false ) {
		$this->category = [ 'categorie' => $category,
								'domain'    => $domain ];
	}

	/**
	 * set the timestamp for the publication date
	 * @param int $timestamp
	 */
	public function setPubDate( $timestamp ) {
		$this->pubDate = date( 'r', $timestamp );
	}

	/**
	 * set the GUID
	 * @param string $guid
	 * @param bool $isPermaLink
	 */
	public function setGUID( $guid, $isPermaLink = 'true' ) {
		$this->guid = [ 'guid'        => $guid,
							'isPermaLink' => $isPermaLink ];
	}

	/**
	 * set the comment URL
	 * @param string $url
	 */
	public function setComments( $url ) {
		$this->comments = RSSCreator::ensureLinkProtocol( $url );
	}

	/**
	 * set the informations of the author of this item
	 * @param string $mail
	 * @param string $name
	 */
	public function setAuthor( $mail, $name = false ) {
		$this->author = [
			'mail' => $mail,
			'name' => $name
		];
	}
}
