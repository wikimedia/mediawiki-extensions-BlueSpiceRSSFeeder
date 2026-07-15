<?php

class RSSItemCreator {

	/** @var string|false */
	protected $title = false;
	/** @var string|false */
	protected $link = false;
	/** @var string|false */
	protected $description = false;

	/** @var string|false */
	protected $source = false;
	/** @var array|false */
	protected $enclosure = false;
	/** @var array|false */
	protected $category = false;
	/** @var string|false */
	protected $pubDate = false;
	/** @var array|false */
	protected $guid = false;
	/** @var string|false */
	protected $comments = false;
	/** @var array|false */
	protected $author = false;

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
