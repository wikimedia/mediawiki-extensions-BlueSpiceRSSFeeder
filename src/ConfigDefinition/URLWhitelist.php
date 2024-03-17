<?php

namespace BlueSpice\RSSFeeder\ConfigDefinition;

use BlueSpice\ConfigDefinition\ArraySetting;
use BlueSpice\ConfigDefinition\IOverwriteGlobal;
use HTMLMultiSelectPlusAdd;

class URLWhitelist extends ArraySetting implements IOverwriteGlobal {

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
		return 'bs-rss-feeder-pref-url-whitelist';
	}

	/**
	 *
	 * @return HTMLMultiSelectPlusAdd
	 */
	public function getHtmlFormField() {
		return new HTMLMultiSelectPlusAdd( $this->makeFormFieldParams() );
	}

	/**
	 * @return string
	 */
	public function getGlobalName() {
		return 'wgRSSUrlWhitelist';
	}

	/**
	 *
	 * @return string
	 */
	public function getHelpMessageKey() {
		return 'bs-rss-feeder-pref-url-allow-help';
	}
}
