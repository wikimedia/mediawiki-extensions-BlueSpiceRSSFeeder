<?php

namespace BlueSpice\RSSFeeder\HookHandler;

use BlueSpice\RSSFeeder\GlobalActionsTool;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class Main implements MWStakeCommonUIRegisterSkinSlotComponents {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$registry->register(
			'GlobalActionsTools',
			[
				'special-bluespice-rssfeeder' => [
					'factory' => static function () {
						return new GlobalActionsTool();
					}
				]
			]
		);
	}
}
