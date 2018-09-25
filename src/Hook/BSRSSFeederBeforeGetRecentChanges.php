<?php

namespace BlueSpice\RSSFeeder\Hook;

abstract class BSRSSFeederBeforeGetRecentChanges extends \BlueSpice\Hook {
	/**
	 *
	 * @var array
	 */
	protected $conditions;

	/**
	 *
	 * @var string
	 */
	protected $feedType;

	/**
	 *
	 * @param array $conditions
	 * @param string $feedType
	 * @return boolean
	 */
	public static function callback( &$conditions, $feedType ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$conditions,
			$feedType
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param array $conditions
	 * @param string $feedType
	 */
	public function __construct( $context, $config, &$conditions, $feedType ) {
		parent::__construct( $context, $config );

		$this->conditions = &$conditions;
		$this->feedType = $feedType;
	}

}