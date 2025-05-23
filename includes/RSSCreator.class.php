<?php
/**
 * RSSCreator
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

use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;

/**
 * the RSSCreator class
 *
 * RSSCreator builds RSS feeds following the RSS 2.0 specification.
 *
 * @package BlueSpice_Extensions
 * @subpackage RSSFeeder
 */
class RSSCreator {
	/**
	 * This array holds all valid "Uniform Resource Identifier (URI) Schemes" following RFC4395.
	 * We need this to check the URLs.
	 * @var array
	 */
	protected static $URISchemes = [
		'aaa', 'aaas', 'acap', 'cap', 'cid', 'crid', 'data', 'dav', 'dict', 'dns', 'fax', 'file', 'ftp', 'go',
		'gopher', 'h323', 'http', 'https', 'iax', 'icap', 'im', 'imap', 'info', 'ipp', 'iris', 'iris\.beep',
		'iris\.xpc', 'iris\.xpcs', 'iris\.lwz', 'ldap', 'mailto', 'mid', 'modem', 'msrp', 'msrps', 'mtqp',
		'mupdate', 'news', 'nfs', 'nntp', 'opaquelocktoken', 'pop', 'pres', 'rtsp', 'service', 'shttp',
		'sieve', 'sip', 'sips', 'snmp', 'soap\.beep', 'soap\.beeps', 'tag', 'tel', 'telnet', 'tftp',
		'thismessage', 'tip', 'tv', 'urn', 'vemmi', 'xmlrpc\.beep', 'xmlrpc\.beeps', 'xmpp', 'z39\.50r', 'z39\.50s'
	];

	/**
	 * Tests the syntactical correctness of a given URL.
	 * @param string $url
	 * @return bool
	 */
	public static function testURL( $url ) {
		// TODO SU (04.07.11 12:10): nicht als foreach testen, sondern als |-Liste in
		// RegEx. Ist effektiver.
		foreach ( self::$URISchemes as $scheme ) {
			$pattern = '/\A(?:\b' . $scheme . ':\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])\Z/i';
			if ( preg_match( $pattern, $url ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Creates a new RSS channel from the given data and returns a new RSSCreator instance
	 * which holds the created channel. If an error occurs, false will be returned.
	 * @param string $title the title of the channel
	 * @param string $link the link to the channel
	 * @param string $description the description of the channel
	 * @return RSSCreator
	 */
	public static function createChannel( $title, $link, $description ) {
		$title       = htmlentities( $title, ENT_QUOTES, 'UTF-8', false );
		$description = htmlentities( $description, ENT_QUOTES, 'UTF-8', false );
		$link = self::ensureLinkProtocol( $link );
		if ( self::testURL( $link ) ) {
			return new RSSCreator( $title, $link, $description );
		}
		return false;
	}

	/**
	 * Escapes the given string for usage in XML.
	 * @param string $string
	 * @return string
	 */
	public static function xmlEncode( $string ) {
		$string = str_replace( "\r\n", "\n", $string );
		$string = preg_replace( '/[\x00-\x08\x0b\x0c\x0e-\x1f]/', '', $string );
		return htmlspecialchars( $string );
	}

	////////////////////////////////////////////////
	// *** Objekt-Methoden und -Eigenschaften *** //
	////////////////////////////////////////////////
	/**
	 * holds all informations of the channel
	 * @var array
	 */
	protected $channel = [
		'title'          => false,
		'link'           => false,
		'description'    => false,
		// enthält array(category => , domain => )
		'categories'     => [],
		'cloud'          => [
			'domain'            => false,
			'path'              => false,
			'port'              => false,
			'protocol'          => false,
			'registerProcedure' => false
		],
		'copyright'      => false,
		'docs'           => 'http://www.rssboard.org/rss-specification',
		'generator'      => 'BlueSpice RSSCreator',
		'image'          => [
			'link'        => false,
			'title'       => false,
			'url'         => false,
			// optional
			'description' => false,
			// optional max 400px
			'height'      => false,
			// optional max 144px
			'width'       => false,
		],
		// TODO MRG (01.07.11 14:29): wieso ist language hardcoded
		// TODO SU (03.07.11 17:03): @MRG Das ist nur eine Standardbelegung.
		// RSS-, W3C- oder ISO639-Language-Code
		'language'       => 'de-de',
		// RFC822-konforme Zeitangabe
		'lastBuildDate'  => false,
		'managingEditor' => [
			'name'       => false,
			'email_addr' => false
		],
		// RFC822-konforme Zeitangabe
		'pubDate'        => false,
		// PICS-rating Label
		'rating'         => false,
		// enthält die englischen Tagesnamen
		'skipDays'       => [],
		// enthält numerische Stundenangaben
		'skipHours'      => [],
		// Cachingdauer
		'ttl'            => 60,
		// eMail-Addresse des Webmasters
		'webMaster'      => false
	];

	/**
	 * holds the items of the channel
	 * @var array
	 */
	protected $items = [];
	/**
	 * holds the dom for the channel
	 * @var DomDocument
	 */
	protected $dom = false;

	/**
	 * constructor of RSSCreator class
	 * @param string $title the title of the channel
	 * @param string $link the link to the channel
	 * @param string $description the description of the channel
	 */
	protected function __construct( $title, $link, $description ) {
		$this->channel['title']       = $title;
		$this->channel['link']        = $link;
		$this->channel['description'] = $description;
		$this->dom = new DOMDocument( '1.0' );
	}

	/**
	 * create a new element in the channels dom
	 * @param DomNode &$target
	 * @param string $tag
	 * @param array $attributes
	 * @param string $content
	 * @param bool $cdata
	 * @return DomNode
	 */
	protected function createElementOn( &$target, $tag, $attributes = false, $content = false,
		$cdata = false ) {
		$_tag = $this->dom->createElement( $tag );
		if ( is_array( $attributes ) ) {
			foreach ( $attributes as $key => $value ) {
				$attr = $this->dom->createAttribute( $key );
				$attr_val = $this->dom->createTextNode( $value );
				$attr->appendChild( $attr_val );
				$_tag->appendChild( $attr );
			}
		}
		if ( $content ) {
			if ( $cdata ) {
				$content = $this->dom->createCDATASection( $content );
			} else {
				$content = $this->dom->createTextNode( $content );
			}
			$_tag->appendChild( $content );
		}
		$target->appendChild( $_tag );
		return $_tag;
	}

	/**
	 * add a new item to the channel
	 * @param DomNode $item
	 */
	public function addItem( $item ) {
		$this->items[] = $item;
	}

	/**
	 * build and return the xml output for the channel
	 * @return string
	 */
	public function buildOutput() {
		$rss = $this->createElementOn(
			$this->dom,
			'rss',
			[ 'version' => '2.0', 'xmlns:dc' => 'http://purl.org/dc/elements/1.1/' ]
		);
		// create Channel
		$channel = $this->createElementOn( $rss, 'channel' );
		$this->createElementOn( $channel, 'title', false, $this->channel['title'] );
		$this->createElementOn( $channel, 'link', false, $this->channel['link'] );
		$this->createElementOn( $channel, 'description', false, $this->channel['description'] );
		if ( count( $this->channel['categories'] ) ) {
			foreach ( $this->channel['categories'] as $category ) {
				if ( $category['domain'] ) {
					$this->createElementOn(
						$channel,
						'category',
						[ 'domain' => $category['domain'] ],
						$category['category']
					);
				} else {
					$this->createElementOn( $channel, 'category', false, $category['category'] );
				}
			}
		}
		if ( $this->channel['cloud']['domain'] ) {
			$this->createElementOn( $channel, 'cloud', $this->channel['cloud'] );
		}
		if ( $this->channel['copyright'] ) {
			$this->createElementOn( $channel, 'copyright', false, $this->channel['copyright'] );
		}
		if ( $this->channel['docs'] ) {
			$this->createElementOn( $channel, 'docs', false, $this->channel['docs'] );
		}
		if ( $this->channel['generator'] ) {
			$this->createElementOn( $channel, 'generator', false, $this->channel['generator'] );
		}
		if ( $this->channel['image']['link'] ) {
			$this->createElementOn( $channel, 'image', $this->channel['image'] );
		}
		if ( $this->channel['language'] ) {
			$this->createElementOn( $channel, 'language', false, $this->channel['language'] );
		}
		if ( $this->channel['lastBuildDate'] ) {
			$this->createElementOn( $channel, 'lastBuildDate', false, $this->channel['lastBuildDate'] );
		}
		if ( $this->channel['managingEditor']['email_addr'] ) {
			if ( !$this->channel['managingEditor']['name'] ) {
				$this->createElementOn(
					$channel,
					'managingEditor',
					false,
					$this->channel['managingEditor']['email_addr']
				);
			} else {
				$this->createElementOn(
					$channel,
					'managingEditor',
					false,
					"{$this->channel['managingEditor']['email_addr']} ({$this->channel['managingEditor']['name']})"
				);
			}
		}
		if ( $this->channel['pubDate'] ) {
			$this->createElementOn( $channel, 'pubDate', false, $this->channel['pubDate'] );
		}
		if ( $this->channel['rating'] ) {
			$this->createElementOn( $channel, 'rating', false, $this->channel['rating'] );
		}
		if ( count( $this->channel['skipDays'] ) ) {
			$this->createElementOn(
				$channel,
				'skipDays',
				false,
				implode( ',', $this->channel['skipDays'] )
			);
		}
		if ( count( $this->channel['skipHours'] ) ) {
			$this->createElementOn(
				$channel,
				'skipHours',
				false,
				implode( ',', $this->channel['skipHours'] )
			);
		}
		if ( $this->channel['ttl'] ) {
			$this->createElementOn( $channel, 'ttl', false, $this->channel['ttl'] );
		}
		if ( $this->channel['webMaster'] ) {
			$this->createElementOn( $channel, 'webMaster', false, $this->channel['webMaster'] );
		}
		// create Items
		if ( count( $this->items ) ) {
			foreach ( $this->items as $item ) {
				$_item = $this->createElementOn( $channel, 'item' );
				$this->createElementOn( $_item, 'title', false, $item->title );
				$this->createElementOn( $_item, 'link', false, $item->link );
				$this->createElementOn( $_item, 'description', false, $item->description, true );
				if ( $item->source ) {
					$this->createElementOn( $_item, 'source', false, $item->source );
				}
				if ( $item->enclosure ) {
					$this->createElementOn( $_item, 'enclosure', $item->enclosure );
				}
				if ( $item->category ) {
					$cat = $item->category;
					if ( $cat['domain'] ) {
						$this->createElementOn(
							$_item,
							'category',
							[ 'domain' => $cat['domain'] ],
							$cat['category']
						);
					} else {
						$this->createElementOn( $_item, 'category', false, $cat['category'] );
					}
				}
				if ( $item->pubDate ) {
					$this->createElementOn( $_item, 'pubDate', false, $item->pubDate );
				}
				if ( $item->guid ) {
					$guid = $item->guid;
					$this->createElementOn(
						$_item,
						'guid',
						[ 'isPermaLink' => $guid['isPermaLink'] ],
						$guid['guid']
					);
				}
				if ( $item->comments ) {
					$this->createElementOn( $_item, 'comments', false, $item->comments );
				}
				if ( $item->author ) {
					$author = $item->author;
					if ( $author['name'] ) {
						$this->createElementOn(
							$_item,
							'author',
							false,
							$author['mail'] . ' (' . $author['name'] . ')'
						);
					} else {
						$this->createElementOn( $_item, 'author', false, $author['mail'] );
					}
				}
			}
		}
		$this->dom->formatOutput = true;
		$out = $this->dom->saveXML();
		$out = html_entity_decode( $out, ENT_COMPAT, 'UTF-8' );
		$out = html_entity_decode( $out, ENT_COMPAT, 'UTF-8' );
		$out = str_replace( '&', '&amp;', $out );
		$out = str_replace( '„', '&quot;', $out );
		$out = str_replace( '“', '&quot;', $out );
		return $out;
	}

	/**
	 * add a category to the channel
	 * @param string $categorie
	 * @param string $domain
	 */
	public function addCategory( $categorie, $domain = false ) {
		$this->channel['categories'][] = [ 'category' => $categorie,
											   'domain'   => $domain ];
	}

	/**
	 * set the cloud of the channel
	 * @param string $domain
	 * @param string $path
	 * @param int $port
	 * @param string $protocol
	 * @param string $registerProcedure
	 */
	public function setCloud( $domain, $path, $port, $protocol, $registerProcedure ) {
		$this->channel['cloud'] = [ 'domain'            => $domain,
										'path'              => $path,
										'port'              => $port,
										'protocol'          => $protocol,
										'registerProcedure' => $registerProcedure ];
	}

	/**
	 * set the copyright informations for the channel
	 * @param string $copyright
	 */
	public function setCopyright( $copyright ) {
		$this->channel['copyright'] = $copyright;
	}

	/**
	 * set an image for this channel
	 * @param string $link
	 * @param string $title
	 * @param string $url
	 * @param string $description
	 * @param int $height
	 * @param int $width
	 */
	public function setImage( $link, $title, $url, $description = false, $height = false,
		$width = false ) {
		$this->channel['image'] = [ 'link'        => $link,
										'title'       => $title,
										'url'         => $url,
										'description' => $description,
										'height'      => $height,
										'width'       => $width ];
	}

	/**
	 * set the language code for the channel
	 * @param string $language
	 */
	public function setLanguage( $language ) {
		$this->channel['language'] = $language;
	}

	/**
	 * set the timestamp of the last build date of the channel
	 * @param int $timestamp
	 */
	public function setLastBuildDate( $timestamp ) {
		$this->channel['lastBuildDate'] = date( 'r', strtotime( $timestamp ) );
	}

	/**
	 * set the details of the managing editor
	 * @param string $email_addr
	 * @param string $name
	 */
	public function setManagingEditor( $email_addr, $name = false ) {
		$this->channel['managingEditor'] = [ 'name'       => $name,
												 'email_addr' => $email_addr ];
	}

	/**
	 * set the puplication date
	 * @param int $timestamp
	 */
	public function setPubDate( $timestamp ) {
		$this->channel['pubDate'] = date( 'r', $timestamp );
	}

	/**
	 * set the PICS label
	 * @param string $rating
	 */
	public function setPICSLabel( $rating ) {
		$this->channel['rating'] = $rating;
	}

	/**
	 * set the weekdays where the channel will not be updated
	 * @param string $days a comma separated list of weekdays (i.e. 'Monday, Saturday, Sunday')
	 */
	public function setSkipDays( $days ) {
		if ( !is_array( $days ) ) {
			$days = explode( ',', $days );
		}
		foreach ( $days as $day ) {
			$this->channel['skipDays'] = trim( $day );
		}
	}

	/**
	 * set the hours where the channel will not be updated
	 * @param string $hours a comma separated list of hours (i.e. '23, 0, 1, 2, 3, 4, 12, 13')
	 */
	public function setSkipHours( $hours ) {
		if ( !is_array( $hours ) ) {
			$hours = explode( ',', $hours );
		}
		foreach ( $hours as $hour ) {
			$this->channel['skipHours'] = $hour;
		}
	}

	/**
	 * set the number of minutes, the channel should be cached
	 * @param int $ttl
	 */
	public function setTTL( $ttl ) {
		$this->channel['ttl'] = $ttl;
	}

	/**
	 * set the email adress of the webmaster of the channel
	 * @param string $email_addr
	 */
	public function setWebmaster( $email_addr ) {
		$this->channel['webMaster'] = $email_addr;
	}

	/**
	 *
	 * @param string $link
	 * @return string
	 */
	public static function ensureLinkProtocol( $link ) {
		$urlUtils = MediaWikiServices::getInstance()->getUrlUtils();
		$parts = $urlUtils->parse( $link );
		// whenever the url comes without a scheme, wich is the default
		if ( !empty( $parts['scheme'] ) ) {
			return $link;
		}
		$protocol = RequestContext::getMain()->getRequest()->getProtocol();
		$delimiter = empty( $parts['delimiter'] ) ? '//' : '';
		return "$protocol:$delimiter$link";
	}
}
