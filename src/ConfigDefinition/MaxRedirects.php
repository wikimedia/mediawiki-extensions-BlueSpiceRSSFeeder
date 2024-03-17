<?php

namespace BlueSpice\RSSFeeder\ConfigDefinition;

use BlueSpice\ConfigDefinition\IntSetting;
use BlueSpice\ConfigDefinition\IOverwriteGlobal;

class MaxRedirects extends IntSetting implements IOverwriteGlobal {

	/**
	 *
	 * @return string[]
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_ADMINISTRATION . '/BlueSpiceRSSFeeder',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceRSSFeeder/' . static::FEATURE_ADMINISTRATION,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/BlueSpiceRSSFeeder',
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-rss-feeder-pref-max-redirects';
	}

	/**
	 * @return string
	 */
	public function getGlobalName() {
		return 'wgRSSUrlNumberOfAllowedRedirects';
	}

	/**
	 *
	 * @return string
	 */
	public function getHelpMessageKey() {
		return 'bs-rss-feeder-pref-max-redirects-help';
	}
}
