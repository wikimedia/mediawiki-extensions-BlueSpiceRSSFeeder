<?php

namespace BlueSpice\RSSFeeder;

use IContextSource;
use MediaWiki\MediaWikiServices;
use Message;
use User;
use ViewFormElementFieldset;

interface IRSSFeed {
	/**
	 * @param IContextSource $context
	 * @param User $user
	 * @param MediaWikiServices $services
	 * @return static
	 */
	public static function factory(
		IContextSource $context, User $user, MediaWikiServices $services
	);

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
	 * Get Configuration/URL generator form
	 * @return ViewFormElementFieldset
	 */
	public function getViewElement();

	/**
	 * Get RSS feed output
	 * @return string
	 */
	public function getRss();

	/**
	 * Get a JS function to be called after element is loaded
	 *
	 * @return string
	 */
	public function getJSHandler();
}
