<?php

namespace BlueSpice\RSSFeeder;

use MediaWiki\Message\Message;

interface IRSSFeed {

	/**
	 * Get ID of the feed
	 * @return string
	 */
	public function getId();

	/**
	 * Get human readable name of the feed
	 * @return Message
	 */
	public function getDisplayName();

	/**
	 * Get description of the feed
	 * @return Message
	 */
	public function getDescription();

	/**
	 * Get RSS feed output
	 * @return string
	 */
	public function getRss();
}
