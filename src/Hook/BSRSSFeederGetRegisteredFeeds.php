<?php

namespace BlueSpice\RSSFeeder\Hook;

use BlueSpice\Hook;
use Config;
use IContextSource;

abstract class BSRSSFeederGetRegisteredFeeds extends Hook {
	/**
	 *
	 * @var array
	 */
	protected $feeds;

	/**
	 *
	 * @param array &$feeds
	 * @return bool
	 */
	public static function callback( &$feeds ) {
		$clasname = static::class;
		$hookHandler = new $clasname(
			null,
			null,
			$feeds,
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param IContextSource $context
	 * @param Config $config
	 * @param array &$feeds
	 */
	public function __construct( $context, $config, &$feeds ) {
		parent::__construct( $context, $config );

		$this->feeds = &$feeds;
	}

	/**
	 * register a feed plugin to the RSSFeeder
	 * @param string $name the unique name of the plugin
	 * @param string $title the nationalized title of the plugin
	 * @param string $desc the nationalized description of the plugin
	 * @param object $obj the object instance of the plugin class
	 * @param string $method the plugin method
	 * @param array $params the params to put to the method
	 * @param string $linkBuilder the method to build the link to the feed
	 */
	protected function registerFeed( $name, $title, $desc, $obj, $method,
		$params, $linkBuilder = false ) {
		$this->feeds[$name] = [
			'title' => $title,
			'description' => $desc,
			'object' => $obj,
			'method' => $method,
			'params' => $params,
			'buildLinks' => $linkBuilder
		];
	}

	/**
	 * unregister a feed plugin from the RSSFeeder
	 * @param string $name the unique name of the plugin
	 */
	protected function unregisterFeed( $name ) {
		if ( !isset( $this->feeds[$name] ) ) {
			return;
		}
		unset( $this->feeds[$name] );
	}
}
